<?php
/**
 * Plugin Name: Elementor Fetch Remote Posts
 * Plugin URI: https://github.com/yourusername/elementor-fetch-remote-posts
 * Description: Fetches and displays posts from external WordPress sites using Elementor templates
 * Version: 1.0.2
 * Author: Rakesh Mandal
 * Author URI: https://rakeshmandal.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: elementor-fetch-remote-posts
 * Domain Path: /languages
 */


/*
    Elementor Fetch Remote Posts is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 2 of the License, or
    any later version.

    Elementor Fetch Remote Posts is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Elementor Fetch Remote Posts. If not, see http://www.gnu.org/licenses/gpl-2.0.txt.
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

define('EFRP_VERSION', '1.0.2');
define('EFRP_FILE', __FILE__);
define('EFRP_PATH', plugin_dir_path(EFRP_FILE));
define('EFRP_URL', plugin_dir_url(EFRP_FILE));

// Register custom post type for remote posts
function efrp_register_remote_post_type()
{
    $args = array(
        'public' => false,
        'label' => __('Remote Posts', 'elementor-fetch-remote-posts'),
        'supports' => array('title', 'editor', 'thumbnail'),
    );
    register_post_type('efrp_remote_post', $args);
}
add_action('init', 'efrp_register_remote_post_type');

// Function to clear existing remote posts
function efrp_clear_remote_posts()
{
    $args = array(
        'post_type' => 'efrp_remote_post',
        'posts_per_page' => -1,
        'fields' => 'ids',
    );
    $posts = get_posts($args);
    foreach ($posts as $post_id) {
        wp_delete_post($post_id, true);
    }
}

// Schedule cleanup of remote posts
function efrp_schedule_cleanup()
{
    if (!wp_next_scheduled('efrp_clear_remote_posts_hook')) {
        wp_schedule_event(time(), 'daily', 'efrp_clear_remote_posts_hook');
    }
}
add_action('wp', 'efrp_schedule_cleanup');

// Hook for scheduled cleanup
add_action('efrp_clear_remote_posts_hook', 'efrp_clear_remote_posts');

// Cleanup on plugin deactivation
register_deactivation_hook(EFRP_FILE, 'efrp_clear_remote_posts');

// Load plugin text domain
function efrp_load_textdomain()
{
    load_plugin_textdomain('elementor-fetch-remote-posts', false, dirname(plugin_basename(EFRP_FILE)) . '/languages/');
}
add_action('plugins_loaded', 'efrp_load_textdomain');

// Check if Elementor is installed and activated
function efrp_check_elementor()
{
    if (!did_action('elementor/loaded')) {
        add_action('admin_notices', 'efrp_fail_load');
        return false;
    }
    return true;
}


function efrp_enqueue_scripts()
{
    wp_enqueue_script('jquery');
    wp_enqueue_script('efrp-script', EFRP_URL . 'assets/js/efrp-script.js', ['jquery'], EFRP_VERSION, true);
    wp_localize_script('efrp-script', 'efrp_ajax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('efrp_nonce')
    ]);
}
add_action('wp_enqueue_scripts', 'efrp_enqueue_scripts');


// FETCH
function fetch_remote_posts($site_url, $post_count, $category, $cache_time = 300)
{
    $transient_key = 'efrp_remote_posts_' . md5($site_url . $post_count . $category);

    // Always check the transient, even if cache_time is 0
    $cached_data = get_transient($transient_key);

    // If we have cached data and the cache hasn't expired, use it
    if (false !== $cached_data && $cache_time > 0) {
        $cached_time = get_option('_transient_timeout_' . $transient_key) - time();
        if ($cached_time > 0) {
            return $cached_data;
        }
    }

    // Cache is expired, doesn't exist, or real-time fetching is enabled
    $api_url = trailingslashit($site_url) . 'wp-json/wp/v2/posts?_embed&per_page=' . intval($post_count);

    if (!empty($category)) {
        $category_api_url = trailingslashit($site_url) . 'wp-json/wp/v2/categories?slug=' . urlencode($category);
        $category_response = wp_remote_get($category_api_url);

        if (is_wp_error($category_response)) {
            return new WP_Error('fetch_error', __('Error fetching category information.', 'elementor-fetch-remote-posts'));
        }

        $categories = json_decode(wp_remote_retrieve_body($category_response), true);
        if (!empty($categories) && isset($categories[0]['id'])) {
            $category_id = $categories[0]['id'];
            $api_url .= '&categories=' . $category_id;
        }
    }

    $response = wp_remote_get($api_url);

    if (is_wp_error($response)) {
        return new WP_Error('fetch_error', __('Error fetching posts from remote site.', 'elementor-fetch-remote-posts'));
    }

    $posts = json_decode(wp_remote_retrieve_body($response), true);

    if (empty($posts)) {
        return new WP_Error('no_posts', __('No posts found on the remote site.', 'elementor-fetch-remote-posts'));
    }

    // Always cache the fetched data, even if cache_time is 0
    // This allows us to check if the data has changed on subsequent requests
    set_transient($transient_key, $posts, max(1, $cache_time)); // Minimum 1 second cache

    return $posts;
}

function efrp_ajax_fetch_posts()
{
    try {
        check_ajax_referer('efrp_nonce', 'nonce');

        if (!isset($_POST['settings'])) {
            throw new Exception('Invalid request: settings not provided');
        }

        $settings = json_decode(stripslashes($_POST['settings']), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON in settings: ' . json_last_error_msg());
        }

        if (!isset($settings['site_url']) || !isset($settings['site_url']['url'])) {
            throw new Exception('Invalid settings: site_url not provided');
        }

        $site_url = $settings['site_url']['url'];
        $post_count = isset($settings['post_count']) ? intval($settings['post_count']) : 4;
        $category = isset($settings['category']) ? $settings['category'] : '';
        $cache_time = isset($settings['cache_time']) ? intval($settings['cache_time']) : 300;

        // Use the fetch_remote_posts function
        $posts = fetch_remote_posts($site_url, $post_count, $category, $cache_time);

        if (is_wp_error($posts)) {
            throw new Exception($posts->get_error_message());
        }

        wp_send_json_success($posts);
    } catch (Exception $e) {
        error_log('EFRP Error: ' . $e->getMessage());
        wp_send_json_error('An error occurred: ' . $e->getMessage());
    }
}
add_action('wp_ajax_efrp_fetch_posts', 'efrp_ajax_fetch_posts');
add_action('wp_ajax_nopriv_efrp_fetch_posts', 'efrp_ajax_fetch_posts');


// Admin notice if Elementor is not active
function efrp_fail_load()
{
    $screen = get_current_screen();
    if (isset($screen->parent_file) && 'plugins.php' === $screen->parent_file && 'update' === $screen->id) {
        return;
    }

    $plugin = 'elementor/elementor.php';
    $installed_plugins = get_plugins();

    if (isset($installed_plugins[$plugin])) {
        $activation_url = wp_nonce_url('plugins.php?action=activate&plugin=' . $plugin . '&plugin_status=all&paged=1', 'activate-plugin_' . $plugin);
        $message = __('<p>Elementor Fetch Remote Posts requires Elementor to be activated.</p>', 'elementor-fetch-remote-posts');
        $button_text = __('Activate Elementor', 'elementor-fetch-remote-posts');
    } else {
        $activation_url = wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=elementor'), 'install-plugin_elementor');
        $message = __('<p>Elementor Fetch Remote Posts requires Elementor to be installed and activated.</p>', 'elementor-fetch-remote-posts');
        $button_text = __('Install Elementor', 'elementor-fetch-remote-posts');
    }

    $button = '<p><a href="' . $activation_url . '" class="button-primary">' . $button_text . '</a></p>';

    printf('<div class="error"><p>%1$s</p>%2$s</div>', __($message), $button);
}

// Load the widget class and register it with Elementor
function efrp_register_widget($widgets_manager)
{
    require_once EFRP_PATH . 'includes/class-efrp-widget.php';
    $widgets_manager->register(new \EFRP_Widget());
}

// Initialize the plugin
function efrp_init()
{
    if (efrp_check_elementor()) {
        add_action('elementor/widgets/register', 'efrp_register_widget');
    }
}
add_action('plugins_loaded', 'efrp_init');

function efrp_clear_transients()
{
    global $wpdb;
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_efrp_remote_posts_%'");
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_efrp_remote_posts_%'");
}

register_activation_hook(__FILE__, 'efrp_clear_transients');