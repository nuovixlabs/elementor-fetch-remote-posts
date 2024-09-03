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

    // public function get_script_depends()
    // {
    //     return ['jquery', 'efrp-script'];
    // }

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
            'title_length',
            [
                'label' => __('Title Length (words)', 'elementor-fetch-remote-posts'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'min' => 1,
                'max' => 20,
                'step' => 1,
                'default' => 10,
            ]
        );

        $this->add_control(
            'excerpt_length',
            [
                'label' => __('Excerpt Length (words)', 'elementor-fetch-remote-posts'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'min' => 10,
                'max' => 200,
                'step' => 1,
                'default' => 55,
            ]
        );

        // Filter Excerpt with Specific String
        // $this->add_control(
        //     'filter_content',
        //     [
        //         'label' => __('Filter "Article Originally Published Here"', 'elementor-fetch-remote-posts'),
        //         'type' => \Elementor\Controls_Manager::SWITCHER,
        //         'label_on' => __('Yes', 'elementor-fetch-remote-posts'),
        //         'label_off' => __('No', 'elementor-fetch-remote-posts'),
        //         'return_value' => 'yes',
        //         'default' => 'yes',
        //     ]
        // );

        $this->end_controls_section();

        $this->start_controls_section(
            'layout_section',
            [
                'label' => __('Layout', 'elementor-fetch-remote-posts'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'layout',
            [
                'label' => __('Layout', 'elementor-fetch-remote-posts'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'list',
                'options' => [
                    'list' => __('List', 'elementor-fetch-remote-posts'),
                    'grid' => __('Grid', 'elementor-fetch-remote-posts'),
                ],
            ]
        );

        $this->add_control(
            'grid_layout_style',
            [
                'label' => __('Grid Layout', 'elementor-fetch-remote-posts'),
                'type' => \Elementor\Controls_Manager::HIDDEN,
                'default' => 'yes',
                'selectors' => [
                    '{{WRAPPER}} .efrp-grid' => 'display: grid;',
                ],
                'condition' => [
                    'layout' => 'grid',
                ],
            ]
        );


        $this->add_responsive_control(
            'columns',
            [
                'label' => __('Columns', 'elementor-fetch-remote-posts'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '3',
                'options' => [
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                ],
                'selectors' => [
                    '{{WRAPPER}} .efrp-grid' => 'grid-template-columns: repeat({{VALUE}}, 1fr);',
                ],
                'condition' => [
                    'layout' => 'grid',
                ],
            ]
        );

 

        $this->add_responsive_control(
            'column_gap',
            [
                'label' => __('Column Gap', 'elementor-fetch-remote-posts'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', '%'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .efrp-grid' => 'column-gap: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'layout' => 'grid',
                ],
            ]
        );

        $this->add_responsive_control(
            'row_gap',
            [
                'label' => __('Row Gap', 'elementor-fetch-remote-posts'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', '%'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .efrp-grid' => 'row-gap: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'layout' => 'grid',
                ],
            ]
        );

        $this->end_controls_section();


        $this->start_controls_section(
            'cache_optimizations_section',
            [
                'label' => __('Optimizations', 'elementor-fetch-remote-posts'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'cache_time',
            [
                'label' => __('Cache Time (seconds)', 'elementor-fetch-remote-posts'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'min' => 0,
                'max' => 86400, // 24 hours
                'step' => 60,
                'default' => 300,
                'description' => __('Set to 0 for real-time fetching (may impact performance)', 'elementor-fetch-remote-posts'),
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

        
        // BOX STYLE
        $this->add_control(
            'box_heading',
            [
                'label' => __('Box Style', 'elementor-fetch-remote-posts'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'post_background',
                'label' => __('Post Background', 'elementor-fetch-remote-posts'),
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .efrp-post',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'post_border',
                'label' => __('Border', 'elementor-fetch-remote-posts'),
                'selector' => '{{WRAPPER}} .efrp-post',
            ]
        );

        $this->add_control(
            'post_border_radius',
            [
                'label' => __('Border Radius', 'elementor-fetch-remote-posts'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .efrp-post' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );


        $this->add_responsive_control(
            'post_padding',
            [
                'label' => __('Padding', 'elementor-fetch-remote-posts'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .efrp-post' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'post_margin',
            [
                'label' => __('Margin', 'elementor-fetch-remote-posts'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .efrp-post' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );





        // IMAGE STYLE
        $this->add_control(
            'image_heading',
            [
                'label' => __('Image Settings', 'elementor-fetch-remote-posts'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'image_width',
            [
                'label' => __('Image Width', 'elementor-fetch-remote-posts'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 1000,
                        'step' => 1,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => '%',
                    'size' => 100,
                ],
                'selectors' => [
                    '{{WRAPPER}} .efrp-post-image img' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'image_height',
            [
                'label' => __('Image Height', 'elementor-fetch-remote-posts'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'em'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 1000,
                        'step' => 1,
                    ],
                    'em' => [
                        'min' => 0,
                        'max' => 50,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 200,
                ],
                'selectors' => [
                    '{{WRAPPER}} .efrp-post-image img' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'image_object_fit',
            [
                'label' => __('Object Fit', 'elementor-fetch-remote-posts'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'cover',
                'options' => [
                    'cover' => __('Cover', 'elementor-fetch-remote-posts'),
                    'contain' => __('Contain', 'elementor-fetch-remote-posts'),
                    'fill' => __('Fill', 'elementor-fetch-remote-posts'),
                    'none' => __('None', 'elementor-fetch-remote-posts'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .efrp-post-image img' => 'object-fit: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'image_padding',
            [
                'label' => __('Image Padding', 'elementor-fetch-remote-posts'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .efrp-post-image' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'image_margin',
            [
                'label' => __('Image Margin', 'elementor-fetch-remote-posts'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .efrp-post-image' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'image_border',
                'label' => __('Image Border', 'elementor-fetch-remote-posts'),
                'selector' => '{{WRAPPER}} .efrp-post-image img',
            ]
        );

        $this->add_control(
            'image_border_radius',
            [
                'label' => __('Image Border Radius', 'elementor-fetch-remote-posts'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .efrp-post-image img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // CONTENT BOX
        $this->add_control(
            'contentbox_heading',
            [
                'label' => __('Content Box', 'elementor-fetch-remote-posts'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'content_padding',
            [
                'label' => __('Content Padding', 'elementor-fetch-remote-posts'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .efrp-post-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'content_margin',
            [
                'label' => __('Content Margin', 'elementor-fetch-remote-posts'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .efrp-post-content' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );


        // TITLE STYLE
        $this->add_control(
            'title_heading',
            [
                'label' => __('Title', 'elementor-fetch-remote-posts'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
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
                    '{{WRAPPER}} .efrp-post-title a' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'title_hover_color',
            [
                'label' => __('Title Hover Color', 'elementor-fetch-remote-posts'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .efrp-post-title a:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'excerpt_heading',
            [
                'label' => __('Excerpt', 'elementor-fetch-remote-posts'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
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
        $widget_id = $this->get_id();

        // Ensure the settings are properly JSON encoded
        $settings_json = wp_json_encode($settings);

        echo '<div id="efrp-container-' . esc_attr($widget_id) . '" class="efrp-container" data-settings="' . esc_attr($settings_json) . '"></div>';
    }


    // protected function render()
    // {
    //     $settings = $this->get_settings_for_display();

    //     $site_url = $settings['site_url']['url'];
    //     $post_count = $settings['post_count'];
    //     $category = $settings['category'];
    //     $excerpt_length = $settings['excerpt_length'];
    //     $layout = $settings['layout'];
    //     $cache_time = $settings['cache_time'];

    //     if (empty($site_url)) {
    //         echo __('Please enter a valid remote site URL.', 'elementor-fetch-remote-posts');
    //         return;
    //     }

    //     // $posts = $this->fetch_remote_posts($site_url, $post_count, $category);
    //     $posts = $this->fetch_remote_posts($site_url, $post_count, $category, $cache_time);

    //     if (is_wp_error($posts)) {
    //         echo $posts->get_error_message();
    //         return;
    //     }

    //     if (empty($posts)) {
    //         echo __('No posts found.', 'elementor-fetch-remote-posts');
    //         return;
    //     }

    //     $wrapper_class = 'efrp-posts-list';
    //     if ($layout === 'grid') {
    //         $wrapper_class .= ' efrp-grid';
    //     }


    //     echo '<div class="' . esc_attr($wrapper_class) . '">';
    //     foreach ($posts as $post) {
    //         $title = wp_trim_words(wp_strip_all_tags($post['title']['rendered']), $settings['title_length']);
    //         // $excerpt = wp_trim_words(wp_strip_all_tags($post['excerpt']['rendered']), $excerpt_length);



    //         // Determine the excerpt source
    //         if (!empty($post['excerpt']['rendered'])) {
    //             $excerpt_text = wp_strip_all_tags($post['excerpt']['rendered']);
    //         } else {
    //             $excerpt_text = wp_strip_all_tags($post['content']['rendered']);
    //         }

    //         // Apply content filtering if enabled
    //         // if ($settings['filter_content'] === 'yes') {
    //         //     $excerpt_text = preg_replace('/Article Originally Published Here.*$/is', '', $excerpt_text);
    //         //     $excerpt_text = trim($excerpt_text);
    //         // }

    //         // Trim the excerpt to the specified length
    //         $excerpt = wp_trim_words($excerpt_text, $settings['excerpt_length']);

    //         $link = $post['link'];
    //         $image_url = isset($post['_embedded']['wp:featuredmedia'][0]['source_url'])
    //             ? $post['_embedded']['wp:featuredmedia'][0]['source_url']
    //             : '';

    //         echo '<div class="efrp-post">';
    //         if ($image_url) {
    //             echo '<div class="efrp-post-image"><a href="' . esc_url($link) . '"><img decoding="async" src="' . esc_url($image_url) . '" alt="' . esc_attr($title) . '"></a></div>';
    //         }
    //         echo '<div class="efrp-post-content">';
    //         echo '<h3 class="efrp-post-title"><a href="' . esc_url($link) . '">' . esc_html($title) . '</a></h3>';
    //         echo '<div class="efrp-post-excerpt">' . esc_html($excerpt) . '</div>';
    //         echo '</div>'; // Close efrp-post-content
    //         echo '</div>'; // Close efrp-post
    //     }
    //     echo '</div>';
    // }

    // OLDER METHOD OF CALLING THE API
    // private function fetch_remote_posts($site_url, $post_count, $category)
    // {
    //     $api_url = trailingslashit($site_url) . 'wp-json/wp/v2/posts?_embed&per_page=' . intval($post_count);

    //     if (!empty($category)) {
    //         $category_api_url = trailingslashit($site_url) . 'wp-json/wp/v2/categories?slug=' . urlencode($category);
    //         $category_response = wp_remote_get($category_api_url);

    //         if (is_wp_error($category_response)) {
    //             return new WP_Error('fetch_error', __('Error fetching category information.', 'elementor-fetch-remote-posts'));
    //         }

    //         $categories = json_decode(wp_remote_retrieve_body($category_response), true);
    //         if (!empty($categories) && isset($categories[0]['id'])) {
    //             $category_id = $categories[0]['id'];
    //             $api_url .= '&categories=' . $category_id;
    //         }
    //     }

    //     $response = wp_remote_get($api_url);

    //     if (is_wp_error($response)) {
    //         return new WP_Error('fetch_error', __('Error fetching posts from remote site.', 'elementor-fetch-remote-posts'));
    //     }

    //     $posts = json_decode(wp_remote_retrieve_body($response), true);

    //     if (empty($posts)) {
    //         return new WP_Error('no_posts', __('No posts found on the remote site.', 'elementor-fetch-remote-posts'));
    //     }

    //     return $posts;
    // }



    private function fetch_remote_posts($site_url, $post_count, $category, $cache_time = 300)
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
}