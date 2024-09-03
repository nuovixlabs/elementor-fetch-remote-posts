<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('\Elementor\Widget_Base')) {
    return;
}

class EFRP_Widget extends \Elementor\Widget_Base
{

    public function get_name()
    {
        return 'efrp_widget';
    }

    public function get_title()
    {
        return __('Remote Posts List', 'elementor-fetch-remote-posts');
    }

    public function get_icon()
    {
        return 'eicon-post-list';
    }

    public function get_categories()
    {
        return ['general'];
    }

    protected function register_controls()
    {
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content', 'elementor-fetch-remote-posts'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'site_url',
            [
                'label' => __('Remote Site URL', 'elementor-fetch-remote-posts'),
                'type' => \Elementor\Controls_Manager::URL,
                'placeholder' => 'https://example.com',
                'default' => [
                    'url' => '',
                ],
                'label_block' => true,
            ]
        );

        $this->add_control(
            'post_count',
            [
                'label' => __('Number of Posts', 'elementor-fetch-remote-posts'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'min' => 1,
                'max' => 20,
                'step' => 1,
                'default' => 4,
            ]
        );

        $this->add_control(
            'category',
            [
                'label' => __('Category', 'elementor-fetch-remote-posts'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'placeholder' => __('Enter category slug', 'elementor-fetch-remote-posts'),
            ]
        );

        $this->add_control(
            'excerpt_length',
            [
                'label' => __('Excerpt Length', 'elementor-fetch-remote-posts'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'min' => 10,
                'max' => 200,
                'step' => 1,
                'default' => 55,
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'style_section',
            [
                'label' => __('Style', 'elementor-fetch-remote-posts'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'label' => __('Title Typography', 'elementor-fetch-remote-posts'),
                'selector' => '{{WRAPPER}} .efrp-post-title',
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label' => __('Title Color', 'elementor-fetch-remote-posts'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .efrp-post-title' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'excerpt_typography',
                'label' => __('Excerpt Typography', 'elementor-fetch-remote-posts'),
                'selector' => '{{WRAPPER}} .efrp-post-excerpt',
            ]
        );

        $this->add_control(
            'excerpt_color',
            [
                'label' => __('Excerpt Color', 'elementor-fetch-remote-posts'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .efrp-post-excerpt' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();

        $site_url = $settings['site_url']['url'];
        $post_count = $settings['post_count'];
        $category = $settings['category'];
        $excerpt_length = $settings['excerpt_length'];

        if (empty($site_url)) {
            echo __('Please enter a valid remote site URL.', 'elementor-fetch-remote-posts');
            return;
        }

        $posts = $this->fetch_remote_posts($site_url, $post_count, $category);

        if (is_wp_error($posts)) {
            echo $posts->get_error_message();
            return;
        }

        if (empty($posts)) {
            echo __('No posts found.', 'elementor-fetch-remote-posts');
            return;
        }

        echo '<div class="efrp-posts-list">';
        foreach ($posts as $post) {
            $title = $post['title']['rendered'];
            $excerpt = wp_trim_words(wp_strip_all_tags($post['excerpt']['rendered']), $excerpt_length);
            $link = $post['link'];
            $image_url = isset($post['_embedded']['wp:featuredmedia'][0]['source_url'])
                ? $post['_embedded']['wp:featuredmedia'][0]['source_url']
                : '';

            echo '<div class="efrp-post">';
            if ($image_url) {
                echo '<div class="efrp-post-image"><img src="' . esc_url($image_url) . '" alt="' . esc_attr($title) . '"></div>';
            }
            echo '<h3 class="efrp-post-title"><a href="' . esc_url($link) . '">' . esc_html($title) . '</a></h3>';
            echo '<div class="efrp-post-excerpt">' . esc_html($excerpt) . '</div>';
            echo '</div>';
        }
        echo '</div>';
    }

    private function fetch_remote_posts($site_url, $post_count, $category)
    {
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

        return $posts;
    }
}