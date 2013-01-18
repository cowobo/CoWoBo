<?php
/*
  Plugin Name: CoWoBo Loader
  Plugin URI: http://cowobo.org
  Description: Coding for a better world
  Version: 0.1
  Author: Coding Angels
  Author URI: http://cowobo.org
 */

// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

/**
 * Version number
 *
 * @since 0.1
 */
define('COWOBO_PLUGIN_VERSION', '0.1');

/**
 * PATHs and URLs
 *
 * @since 0.1
 */
define('COWOBO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('COWOBO_PLUGIN_URL', plugin_dir_url(__FILE__));
define('COWOBO_PLUGIN_LIB', COWOBO_PLUGIN_DIR . 'lib/' );

/**
 * Requires and includes
 *
 * @since 0.1
 */
require_once ( COWOBO_PLUGIN_LIB . 'class-cowobo-query.php' );
require_once ( COWOBO_PLUGIN_LIB . 'class-cowobo-users.php' );
require_once ( COWOBO_PLUGIN_LIB . 'class-cowobo-feed.php' );
require_once ( COWOBO_PLUGIN_LIB . 'class-cowobo-posts.php' );
require_once ( COWOBO_PLUGIN_LIB . 'class-cowobo-related.php' );
require_once ( COWOBO_PLUGIN_LIB . 'class-cowobo-l10n.php' );
require_once ( COWOBO_PLUGIN_LIB . 'class-cowobo-layouts.php' );
require_once ( COWOBO_PLUGIN_LIB . 'class-cowobo-map.php' );
require_once ( COWOBO_PLUGIN_LIB . 'notices.php' );

if (!class_exists('CoWoBo')) :

    /**
     * CoWoBo Master Class
     *
     * @package CoWoBo
     * @subpackage Plugin
     */
    class CoWoBo    {

        /**
         * Var to contain the query class, for easy access to query vars
         *
         * @var CoWoBo_Query
         */
        public $query;

        /**
         * Var to contain the query class with nonce check
         *
         * @var CoWoBo_Query
         */
        public $verified_query;

        /**
         * Var to contain the users class with nonce check
         *
         * @var CoWoBo_Users
         */
        public $users;

        /**
         * Var to contain the feed class
         *
         * @var CoWoBo_Feed
         */
        public $feed;

        /**
         * Var to contain the posts class
         *
         * @var CoWoBo_Posts
         */
        public $posts;

        /**
         * Var to contain the related posts class
         *
         * @var CoWoBo_Related_Posts
         */
        public $relations;

        /**
         * Var to contain the localization class
         *
         * @var CoWoBo_Localization
         */
        public $L10n;

        /**
         * Var to contain the layouts class
         *
         * @var CoWoBo_Localization
         */
        public $layouts;


        /**
         * Notices
         *
         * Errors, messages, success, or other notifications to the user about what's going on here.
         */
        public $notices = array();

        /**
         *
         */
        public $notices_loop;
        private $notice_types;
        public $default_notices = array (
                "editrequest_sent"      =>  "Thank you, your request has been sent.",
                "editrequest_accepted"  =>  "Thank you, your request has been accepted.",
                "editrequest_denied"    =>  "Thank you, your request has been denied.",
                "editrequest_cancelled" =>  "Thank you, your request has been cancelled.",
            );


        /**
         * Creates an instance of the CoWoBo class
         *
         * @return CoWoBo object
         * @since 0.1
         * @static
        */
        public static function &init() {
            global $cowobo;
            //static $instance = false;

            if (!$cowobo) {
                load_plugin_textdomain('cowobo', false, basename(COWOBO_PLUGIN_DIR) . '/languages/');
                $cowobo = new CoWoBo;
            }

            return $cowobo;
        }

        /**
         * Constructor
         *
         * @since 0.1
         */
        public function __construct() {

            $this->query = new CoWoBo_Query;
            $this->verified_query = new CoWoBo_Query ( true );

            $this->setup_notices_loop();

            $this->actions_and_filters();
        }

            /**
             * PHP4
             *
             * @since 0.1
             */
            public function CoWoBo() {
                $this->__construct();
            }

        private function setup_notices_loop() {
            $this->notices_loop = new stdClass;
            $this->notices_loop->count = 0;
            $this->notices_loop->index = 0;

            if ( $this->query->message ) {
                $this->add_notice_by_key ( $this->query->message );
            }
        }

        private function actions_and_filters() {
            add_action('init', array ( &$this, 'setup' ) );
            add_action('template_redirect', array ( &$this, 'controller' ) );

        }

        public function setup() {
            $this->users = new CoWoBo_Users;
            $this->feed = new CoWoBo_Feed;
            $this->posts = new CoWoBo_Posts;
            $this->relations = new Cowobo_Related_Posts;
            $this->L10n = new CoWoBo_Localization;
            $this->layouts = new Cowobo_Layouts;
        }

        public function controller() {
            if ( is_404() ) return;

            $query = &$this->query;
            $verify = &$this->verified_query;
            $users = &$this->users;
            $feed = &$this->feed;
            $posts = &$this->posts;
            $relations = &$this->relations;
            $L10n = &$this->L10n;

            // User actions
            if( $verify->confirm ) $notices = $users->create_user();
            elseif( $verify->login && ! $query->user ) $notices = $users->login_user();

            // Feed actions
            elseif( $query->sort ) $notices = $feed->filter_feed();
            elseif( $query->showall ) $notices = $feed->related_feed();

            // Post actions
            elseif( $verify->delete ) $notices = $posts->delete_post();
            elseif( $verify->save ) $GLOBALS['postmsg'] = $posts->save_post();
            elseif( $verify->linkposts ) $notices = $relations->link_post();

//            elseif( $query->commentid ) wp_delete_comment($_POST['commentid']);

            elseif( $verify->sendemail && ! $query->user ) $notices = $this->send_email();
            elseif( $verify->request ) $notices = $posts->edit_request();
            elseif( $query->correctlang ) $notices = $L10n->correct_translation();
        }

        /**
         * Redirect users based on $_REQUEST['redirect']
         *
         * @param mixed $param1 Either newkey or an associative_array
         * @param string $param2 (optional) Newvalue
         *
         */
        public function redirect( $query = false ) {
            $redirect_url = '';
            if ( $redirect = $this->query->redirect ) {
                switch ( $redirect ) {
                    case 'profile' :
                        $profile_id = $this->users->get_current_user_profile_id();
                        $redirect_url = get_permalink( $profile_id );
                        break;
                    case 'contact' :
                        $redirect_url = '?action=contact';
                        break;
                    case 'edit' :
                        $redirect_url = '?action=editpost';
                        break;
                }
            }
            if ( empty ( $redirect_url ) ) {
                if ( $this->query->action == 'login' ) {
                    $profile_id = $this->users->get_current_user_profile_id();
                    $redirect_url = get_permalink( $profile_id );;
                } else {
                    $redirect_url = $_SERVER["REQUEST_URI"];
                }
            }

            if ( func_num_args() > 1 ) {
                $newkey = urlencode( func_get_arg(0) );
                $newvalue = urlencode( func_get_arg(1) );
                $redirect_url = add_query_arg( $newkey, $newvalue, $redirect_url );
            } elseif ( is_array ( $query ) ) {
                $redirect_url = add_query_arg( $query, $redirect_url );
            }

            wp_safe_redirect( $redirect_url );
            exit;
        }

        /**
         * Returns an array with the current category (obj) and the category id (str)
         *
         * @return arr  current category (obj) and category id (str)
         */
        public function get_current_category() {
            global $post, $currentcat;

            $catid = 0;
            $currentcat = false;
            if ( ! is_a ( $post, 'WP_Post' ) ) return array();
            if ($catid = get_query_var('cat')) {
                $currentcat = get_category($catid);
            } else {
                $cat = get_the_category();
                if ( is_array ( $cat ) && isset ( $cat[0] ) ) {
                    $currentcat = $cat[0];
                    $catid = $currentcat->term_id;
                }
            }
            return array ('currentcat' => $currentcat, 'catid' => $catid );
        }

        /**
         * Send contact email
         *
         * @todo Email localization
         * @global type $profile_id
         * @return string
         */
        private function send_email() {
            global $profile_id;
            $profile = get_post( $profile_id );
            $firstname = $_POST['user_firstname'];
            $header  = 'MIME-Version: 1.0'."\r\n";
            $header .= 'Content-type: text/html; charset=utf8'."\r\n";
            $header .= 'From: Coders Without Borders <'.get_bloginfo('admin_email').'>' . "\r\n";

            if($from = $_POST['user_email']):
                $subject = 'New message from a visitor';
                $message = $_POST['emailtext'].'<br/><br/>'.$firstname.'<br/><br/>';
                $message .= '<a href="mailto:'.$from.'">Click here to reply</a>';
                mail('balthazar@cowobo.org', $subject, $message, $header);
            elseif($to = $_POST['user_friends']):
                $subject = $firstname.' sent you this message via our site';
                $message = $_POST['emailtext'].'<br/><br/>'.$firstname.'<br/><br/>';
                $message .= '<a href="'.$_SERVER['REQUEST_URI'].'">'. $this->feed->feed_title() .'</a><br/>';
                if(is_single()) $message .= get_the_excerpt(); else $message .= get_bloginfo('description');
                mail('balthazar@cowobo.org,'.$to, $subject, $message, $header);
            else:
                $emailnotice = 'Please enter at least one email address';
            endif;
            $emailnotice = 'Your email has been sent successfully';

            return $emailnotice;
            // @todo: handle and return errors
        }

        public function has_notices() {
            return ( !empty ( $this->notices ) );
        }

        public function has_notice( $notice_type ) {
            if ( ! $this->has_notices() ) return false;
            $notice_types = &$this->notice_types;

            // Refresh notice_types array?
            if ( empty ( $notice_types ) || ( count ( $notice_types ) < count( $this->notices ) ) ) {
                $notice_types = array();
                foreach ( $this->notices as $notice ) {
                    if ( ! is_array ( $notice ) ) continue;
                    $notice_types[] = key ( $notice );
                }
            }

            if ( is_array ( $notice_type ) ) {
                foreach ( $notice_type as $type ) {
                    if ( in_array ( $type, $notice_types ) ) return true;
                }
                return false;
            }

            return in_array ( $notice_type, $notice_types );
        }

        public function add_notice ( $message, $key = 'message' ) {
            $this->notices[] = array ( $key => $message );
            $this->notice_types[] = $key;
        }

        public function print_notices( $notice_types, $class = '' ) {
            if ( ! is_array ( $notice_types ) ) $notice_types = array ( $notice_types );
            if ( $this->has_notice( $notice_types ) ) {
                while ( have_notices() ) : the_notice();
                    if ( ! in_array ( get_the_notice_type(), $notice_types ) ) continue;

                    echo "<div class='tab notice " . strtolower ( get_the_notice_type() ) . " $class'>";
                    the_notice_content();
                    echo "<span class='close hide-if-no-js'>dismiss</span>";
                    echo "</div>";
                endwhile;
            }
        }

        public function add_notice_by_key( $key ) {

            if ( array_key_exists ( $key, $this->default_notices ) ) {
                $this->add_notice( $this->default_notices[$key], $key );
            }
        }

    }

    add_action('plugins_loaded', array('CoWoBo', 'init'));
endif;