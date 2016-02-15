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

if ( ! class_exists( 'PragWPBaseChanges' ) ) {
    class PragWPBaseChanges {

        private $settings;

        public function __construct() {
            // Load settings
            $this->get_settings();

            // Set location values
            $this->path = untrailingslashit( plugin_dir_path( __FILE__ ) );
            $this->url  = untrailingslashit( plugin_dir_url( __FILE__ ) );

            // Hook in where necessary
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

        /**
         * Retrieves settings from _options table
         * @return void
         */
        private function get_settings() {
            $this->settings = maybe_unserialize( get_option( 'prag-wp_base_changes-settings' ) );
            return;
        }

        /**
         * Save settings to _options table
         * @return void
         */
        private function save_settings() {
            update_option( 'prag-wp_base_changes-settings', maybe_serialize( $this->settings ) );
            return;
        }

    }
}

// Create object if needed
if ( ! @$PragWPBaseChanges && function_exists( 'add_action' ) ) { $PragWPBaseChanges = new PragWPBaseChanges(); }
