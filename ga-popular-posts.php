<?php
/**
 * Plugin Name: GA popular posts
 * Plugin URI: http://mazharulanwar.com/
 * Description: Get the popular posts of your blog according to GA
 * Version:  0.1
 * Author: Mazharul Anwar
 * Author URI: http://mazharulanwar.com/
 * License: GPL3
 */

if (!class_exists(WPGAMVPopularPosts)) {
  
    class WPGAMVPopularPosts{


        /**
         * page slug
         * @var string
         */
        private $_pageSlug = "wp-ga-popular-posts";

        /**
         * Constructing the class, adding actions and hooks in construct
         */
        public function __construct() {

            add_action('admin_menu', array($this, 'gappmv_admin_menu'));
            add_action('admin_init', array($this, 'gappmv_admin_init'));

            register_activation_hook(__FILE__, array($this, 'gappmv_activate'));
            register_deactivation_hook(__FILE__, array($this, 'gappmv_deactivate'));


        }

        /**
         * Activate function for the plugin
         * @return void
         */
        public static function  gappmv_activate() {

        }

        /**
         * Deactivating function of the plugin
         * @return void
         */
        public static function gappmv_deactivate() {

        } 


        /**
         * Initializing admin options fields
         * @access public
         * @return void
         */
        public function gappmv_admin_init() {
            
            register_setting('gappmv_setting_group', 'gappmv-setting');
            
            add_settings_section('gappmv-first-section', 'Section One', array($this, 'gappmv_first_section_callback'), $this->_pageSlug);

            add_settings_field('gappmv-gmail', 'Your Analytics email', array($this, 'gappmv_gmail_callback'), $this->_pageSlug, 'gappmv-first-section');

            add_settings_field('gappmv-password', 'Provide us your password please', array($this, 'gappmv_password_callback'), $this->_pageSlug, 'gappmv-first-section');
        }

        /**
         * This function works as callback for the first section of the settings
         * @return string
         */
        
        public function gappmv_first_section_callback() {
            echo "We need you to give your email that you used while setting up the analytics for this blog";
        }

        /**
         * Callback for the first field of the options
         * @return [type] [description]
         */
        public function gappmv_gmail_callback() {

            $s = array ( get_option('gappmv-setting'));
            echo "<input type='email' name='gappmv-setting[email]' value='$s[email]' required> ";
        }


        /**
         * Callback for the second (password) field of the options
         * @return [type] [description]
         */
        public function gappmv_password_callback() {

            $s = array ( get_option('gappmv-setting'));
            echo "<input type='password' name='gappmv-setting[password]' value='$s[password]' required> ";
        }

        
        /**
         * Initializing the admin menu
         * wordpress API used `add_options_page()`
         * @return [type] [description]
         */
        public function gappmv_admin_menu () {
            add_options_page("GA popular posts plugin", "GA popular posts", "manage_options", $this->_pageSlug, array($this, "gappmv_admin_menu_output"));
        }


        public function gappmv_admin_menu_output () {
            //include 'templates/gpp-admin-menu-output.php';
            include 'templates/gappmv-admin-menu-output.php';
        } 

    }    
}

if (class_exists(WPGAMVPopularPosts)) {
   
    $WPGAMVPopularPosts = new WPGAMVPopularPosts;
}

