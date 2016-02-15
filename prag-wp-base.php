<?php
/*
Plugin Name: WordPress Base Changes
Plugin URI: https://www.pragmatticode.com
Description: Intended to be used as a base plugin installed on sites in order to set various default behaviors of WordPress.
Version: 1.0.0
Author: Matt Walters
Author URI: https://www.pragmatticode.com
License: GPL2
*/

/*
Inspired by and some borrowed from Sage cleanup -- https://github.com/roots/sage
*/

if ( ! class_exists( 'PragWPBaseChanges' ) ) {
    class PragWPBaseChanges {

        public function __construct() {
            // Hook in where necessary
            add_action( 'after_setup_theme', array( &$this, 'move_scripts_to_footer' ) );
            add_action( 'after_setup_theme', array( &$this, 'remove_header_links' ) );
            add_action( 'after_setup_theme', array( &$this, 'remove_header_script_styles' ) );
            add_action( 'after_setup_theme', array( &$this, 'remove_extra_feeds' ) ); // Feed adjustment 1 of 2
            add_action( 'wp_head', array( &$this, 'add_main_feed_back' ) ); // Feed adjustment 2 of 2
            add_action( 'admin_init', array( &$this, 'remove_dashboard_widgets' ) );

            add_filter( 'widgets_init', array( &$this, 'remove_wp_widget_recent_comments_style' ) );
            add_filter( 'the_generator', '__return_false' );
            add_filter( 'body_class', array( &$this, 'filter_body_class' ) );
            add_filter( 'post_class', array( &$this, 'filter_post_class' ) );
            add_filter( 'get_bloginfo_rss', array( &$this, 'filter_bloginfo_rss' ) );
            add_filter( 'embed_oembed_html', array( &$this, 'embed_wrap' ), 10, 4 );
            add_filter( 'embed_googlevideo', array( &$this, 'embed_wrap' ), 10, 2 );
            add_filter( 'wp_get_attachment_link', array( &$this, 'attachment_link_class' ), 10, 1 );
            add_filter( 'dynamic_sidebar_params', array( &$this, 'widget_first_last_classes' ) );
        }

        public function move_scripts_to_footer() {
            // remove_action('wp_head', 'wp_print_scripts');
            // remove_action('wp_head', 'wp_enqueue_scripts', 1);
            remove_action('wp_head', 'wp_print_head_scripts', 9);

            // add_action('wp_footer', 'wp_print_scripts', 5);
            // add_action('wp_footer', 'wp_enqueue_scripts', 5);
            add_action('wp_footer', 'wp_print_head_scripts', 5);
        }

        public function remove_header_links() {
            remove_action( 'wp_head', 'rsd_link' );
            remove_action( 'wp_head', 'wlwmanifest_link' );
            remove_action( 'wp_head', 'wp_shortlink_wp_head' );
            remove_action( 'wp_head', 'wp_generator' );
        }

        public function remove_header_script_styles() {
            remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
            remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
            remove_action( 'wp_print_styles', 'print_emoji_styles' );
            remove_action( 'admin_print_styles', 'print_emoji_styles' );
        }

        public function remove_extra_feeds() {
            remove_action('wp_head', 'feed_links_extra', 3);
            remove_action('wp_head', 'feed_links', 2);
        }
        public function add_main_feed_back() {
            $output = "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"" . get_bloginfo() . " Feed\" href=\"" . get_bloginfo('rss_url') . "\" />";
            echo $output;
        }

        public function remove_dashboard_widgets() {
            remove_meta_box( 'dashboard_primary', 'dashboard', 'normal' ); // WordPress News
            remove_meta_box( 'dashboard_secondary', 'dashboard', 'normal' ); // Other news
            remove_meta_box( 'dashboard_plugins', 'dashboard', 'normal' );
            remove_meta_box('dashboard_incoming_links', 'dashboard', 'normal');
        }

        public function remove_wp_widget_recent_comments_style() {
            global $wp_widget_factory;
            remove_action('wp_head', array($wp_widget_factory->widgets['WP_Widget_Recent_Comments'], 'recent_comments_style'));
        }

        public function filter_body_class( $classes ) {
            foreach ( $classes as $k => $class ) {
                if ( preg_match( '/.*id-\d+/', $class ) === 1 ) {
                    unset( $classes[$k] );
                }
                if ( preg_match( '/author-\d+/', $class ) === 1 ) {
                    unset( $classes[$k] );
                }
                if ( preg_match( '/category-\d+/', $class ) === 1 ) {
                    unset( $classes[$k] );
                }
                if ( preg_match( '/tag-\d+/', $class ) === 1 ) {
                    unset( $classes[$k] );
                }
                if ( preg_match( '/term-\d+/', $class ) === 1 ) {
                    unset( $classes[$k] );
                }
                if ( preg_match( '/.*-paged-\d+/', $class ) === 1 ) {
                    unset( $classes[$k] );
                }
            }

            if ( is_multisite() ) {
                $classes[] = strtolower( str_replace( ' ', '-', trim( get_bloginfo( 'name' ) ) ) );
            }

            if ( is_single() || is_page() && ! is_front_page() ) {
                $classes[] = basename( get_permalink() );
            }

            $removeClasses = array(
                'page-template-default'
            );
            $classes = array_diff($classes, $removeClasses);

            return $classes;
        }

        public function filter_post_class( $classes ) {
            foreach ( $classes as $k => $class ) {
                if ( preg_match( '/.*-\d+/', $class ) === 1 ) {
                    unset( $classes[$k] );
                }
            }

            return $classes;
        }

        public function filter_bloginfo_rss( $blogDescription ) {
            $defaultTagline = 'Just another WordPress site';
            if ( $blogDescription == $defaultTagline ) {
                $blogDescription = get_bloginfo();
            }

            return $blogDescription;
        }

        public function embed_wrap( $cache, $url, $attr = '', $postId = '' ) {
            return '<div class="entry-content-asset">' . $cache . '</div>';
        }

        public function attachment_link_class($html) {
            $html = str_replace('<a', '<a class="thumbnail"', $html);
            return $html;
        }

        public function widget_first_last_classes($params) {
            global $my_widget_num;

            $this_id = $params[0]['id'];
            $arr_registered_widgets = wp_get_sidebars_widgets();

            if ( ! $my_widget_num ) {
                $my_widget_num = array();
            }
            if ( ! isset( $arr_registered_widgets[$this_id] ) || ! is_array( $arr_registered_widgets[$this_id] ) ) {
                return $params;
            }
            if ( isset( $my_widget_num[$this_id] ) ) {
                $my_widget_num[$this_id]++;
            } else {
                $my_widget_num[$this_id] = 1;
            }

            $class = 'class="widget-' . $my_widget_num[$this_id] . ' ';

            if ( $my_widget_num[$this_id] == 1 ) {
                $class .= 'widget-first ';
            } elseif ( $my_widget_num[$this_id] == count( $arr_registered_widgets[$this_id] ) ) {
                $class .= 'widget-last ';
            }

            $params[0]['before_widget'] = preg_replace( '/class=\"/', "$class", $params[0]['before_widget'], 1 );

            return $params;
        }

        /**
         * Outputs a more readable format for things
         * @return void or string
         */
        private function _d( $value = '', $html = true, $echo = true ) {
            $output = '';
            if ( $html ) { $output .= '<pre>'; }
            if ( is_array( $value ) || is_object( $value ) ) {
                $output .= print_r( $value, true );
            } else {
                $output .= $value . "\n";
            }
            if ( $html ) { $output .= '</pre>'; }

            if ( $echo ) {
                echo $output;
            } else {
                return $output;
            }
        }

    }
}

// Create object if needed
if ( ! @$PragWPBaseChanges && function_exists( 'add_action' ) ) { $PragWPBaseChanges = new PragWPBaseChanges(); }
