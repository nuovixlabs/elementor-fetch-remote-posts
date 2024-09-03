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