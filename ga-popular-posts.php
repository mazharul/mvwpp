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

            #var_dump(get_option('gappmv-setting'));


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
            

            if( isset($_POST['SubmitLogin']) && isset($_POST['gappmv_login_type']) && $_POST['gappmv_login_type'] == 'oauth' )
            {
              $this->gappmv_admin_oauth_login_options();
            }
            elseif( isset($_REQUEST['oauth_return']) )
            {
              $this->gappmv_admin_oauth_complete();
            }
        }



        public function gappmv_admin_oauth_login_options()
        {
            delete_option('gappmv_oa_anon_token');
            delete_option('gappmv_oa_anon_secret');

            $signature_method = new GADOAuthSignatureMethod_HMAC_SHA1();
            $params = array();

            $params['oauth_callback'] = $this->gappmv_get_admin_url('/options-general.php') . '?page=mvwpp/gpp-popular-posts.php&oauth_return=true';
            $params['scope'] = 'https://www.googleapis.com/auth/analytics.readonly'; // This is a space seperated list of applications we want access to
            $params['xoauth_displayname'] = 'Analytics Dashboard';

            $consumer = new GADOAuthConsumer('anonymous', 'anonymous', NULL);
            $req_req = GADOAuthRequest::from_consumer_and_token($consumer, NULL, 'GET', 'https://www.google.com/accounts/OAuthGetRequestToken', $params);
            $req_req->sign_request($signature_method, $consumer, NULL);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $req_req->to_url());
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            $oa_response = curl_exec($ch);

            if(curl_errno($ch))
            {
              $error_message = curl_error($ch);
              $info_redirect = $this->gappmv_get_admin_url('/options-general.php') . '?page=mvwpp/gpp-popular-posts.php&error_message=' . urlencode($error_message);
              header("Location: " . $info_redirect);
              die("");
            }

            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if($http_code == 200)
            {
              $access_params = $this->split_params($oa_response);

              add_option('gappmv_oa_anon_token', $access_params['oauth_token']);
              add_option('gappmv_oa_anon_secret', $access_params['oauth_token_secret']);

              header("Location: https://www.google.com/accounts/OAuthAuthorizeToken?oauth_token=" . urlencode($access_params['oauth_token']));
            }
            else
            {
              $info_redirect = gappmv_get_admin_url('/options-general.php') . '?page=google-analytics-dashboard/gad-admin-options.php&error_message=' . urlencode($oa_response);
              header("Location: " . $info_redirect);
            }

            die("");
        }


        public function gappmv_admin_oauth_complete()
        {
            // step two in oauth login process

            if( function_exists('current_user_can') && !current_user_can('manage_options') )
            {
              die(__('Cheatin&#8217; uh?'));
            }

            $signature_method = new GADOAuthSignatureMethod_HMAC_SHA1();
            $params = array();

            $params['oauth_verifier'] = $_REQUEST['oauth_verifier'];

            $consumer = new GADOAuthConsumer('anonymous', 'anonymous', NULL);

            $upgrade_token = new GADOAuthConsumer(get_option('gappmv_oa_anon_token'), get_option('gappmv_oa_anon_secret'));

            $acc_req = GADOAuthRequest::from_consumer_and_token($consumer, $upgrade_token, 'GET', 'https://www.google.com/accounts/OAuthGetAccessToken', $params);

            $acc_req->sign_request($signature_method, $consumer, $upgrade_token);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $acc_req->to_url());
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            $oa_response = curl_exec($ch);

            if(curl_errno($ch))
            {
              $error_message = curl_error($ch);
              $info_redirect = gad_get_admin_url('/options-general.php') . '?page=mvwpp/gpp-popular-posts.php&error_message=' . urlencode($error_message);
              header("Location: " . $info_redirect);
              die("");
            }

            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            delete_option('gappmv_oa_anon_token');
            delete_option('gappmv_oa_anon_secret');

            if($http_code == 200)
            {
              $access_params = $this->split_params($oa_response);

              update_option('gappmv_oauth_token', $access_params['oauth_token']);
              update_option('gappmv_oauth_secret', $access_params['oauth_token_secret']);
              update_option('gappmv_auth_token', 'gappmv_see_oauth');

              $info_redirect = gad_get_admin_url('/options-general.php') . '?page=mvwpp/gpp-popular-posts.php&info_message=' . urlencode('Authenticated!');
              header("Location: " . $info_redirect);
            }
            else
            {
              $info_redirect = gappmv_get_admin_url('/options-general.php') . '?page=mvwpp/gpp-popular-posts.php&error_message=' . urlencode($oa_response);
              header("Location: " . $info_redirect);
            }

            die("");
        }


        public function gappmv_get_admin_url($path = '')
        {
          global $wp_version;
          
          if (version_compare($wp_version, '3.0', '>='))
          {
            return get_admin_url(null, $path);
          }
          else
          {
            return get_bloginfo( 'wpurl' ) . '/wp-admin' . $path;
          }

        }

        public function split_params($response)
        {
            $params = array();
            $param_pairs = explode('&', $response);
            foreach($param_pairs as $param_pair)
            {
              if (trim($param_pair) == '') { continue; }
              list($key, $value) = explode('=', $param_pair);
              $params[$key] = urldecode($value);
            }
            return $params;
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


        public function gappmv_profileId_callback() {

            $s = array ( get_option('gappmv-setting'));
            echo "<input type='text' name='gappmv-setting[profileId]' value='$s[profileId]' required> ";
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
            

            $gappmv_auth_token = get_option('gappmv_auth_token');

            if(isset($gappmv_auth_token) && $gappmv_auth_token != '')
            {
                $this->admin_handle_other_options($info_message);
            }
            else
            {
                $this->gappmv_admin_handle_login();
            }
        }


        public function gappmv_admin_handle_login()
        {

            if( isset($_POST['SubmitLogin']) ) 
            {
              if( function_exists('current_user_can') && !current_user_can('manage_options') )
              {
                die(__('Cheatin&#8217; uh?'));
              }
            }

            include 'templates/gappmv-admin-menu-output.php';
        } 


        public function gappmv_handle_options()
        {
            if( isset($_POST['SubmitOptions']) ) 
            {
              if( function_exists('current_user_can') && !current_user_can('manage_options') )
              {
                die("You do not have permission");
              }


              add_option('gappmv_account_id', $_POST['gappmv_account_id']);

            }

            $ga = new GALib('oauth', NULL, get_option('gappmv_oauth_token'), get_option('gappmv_oauth_secret'), '', get_option('gappmv_cache_timeout') !== false ? get_option('gappmv_cache_timeout') : 60);

            $accounts = $ga->account_query();

            if($ga->isError())
            {
              if($ga->isAuthError())
              {
                delete_option('gappmv_auth_token'); // this is removed so login will happen again
                $this->gappmv_admin_handle_login();
                return;
              }
              else
              {
                ?>
                <div class="wrap" style="padding-top: 50px;">
                  Authenticatin Error.

                  Please try again later.
                </div>
                <?php
              }
            }

            include 'templates/gappmv_admin_options.php';

        }

    }    
}

if (class_exists(WPGAMVPopularPosts)) {
   
    $WPGAMVPopularPosts = new WPGAMVPopularPosts;
}

