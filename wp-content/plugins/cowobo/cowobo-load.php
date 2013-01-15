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
         * Notices
         *
         * Errors, messages, success, or other notifications to the user about what's going on here.
         */
        public $notices = array();


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
            $this->users = new CoWoBo_Users;
            $this->feed = new CoWoBo_Feed;
            $this->posts = new CoWoBo_Posts;
            $this->relations = new Cowobo_Related_Posts;
            $this->L10n = new CoWoBo_Localization;
            $this->layouts = new Cowobo_Layouts;

            $this->old_includes();

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

        private function old_includes() {
            include_once( TEMPLATEPATH . '/lib/class-cowobo-map.php');
            global $social, $layouts;
        }

        private function actions_and_filters() {
            add_action('template_redirect', array ( &$this, 'controller' ) );

        }

        /**
         * @todo Add nonces
         */
        public function controller() {
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
            elseif( $verify->new ) $GLOBALS['postid'] = $posts->create_post();
            elseif( $verify->save ) $GLOBALS['postmsg'] = $posts->save_post();
            elseif( $verify->linkposts ) $notices = $relations->link_post();

//            elseif( $query->commentid ) wp_delete_comment($_POST['commentid']);

            elseif( $verify->sendemail && ! $query->user ) $notices = $this->send_email();
            elseif( $verify->request ) $notices = $posts->edit_request();
            elseif( $query->correctlang ) $notices = $L10n->correct_translation();
        }

        /**
         * Redirect users based on $_REQUEST['redirect']
         */
        public function redirect() {
            if ( $redirect = $this->query->redirect ) {
                switch ( $redirect ) {
                    case 'profile' :
                        $profile_id = $this->users->get_current_user_profile_id();
                        wp_safe_redirect(get_permalink( $profile_id ) );
                        break;
                    case 'contact' :
                        wp_safe_redirect('?action=contact');
                        break;
                    case 'edit' :
                        wp_safe_redirect('?action=editpost');
                        break;
                    default :
                        wp_safe_redirect($_SERVER["REQUEST_URI"]);
                        break;
                }
            } else wp_safe_redirect($_SERVER["REQUEST_URI"]);
        }

        /**
         * Returns an array with the current category (obj) and the category id (str)
         *
         * @return arr  current category (obj) and category id (str)
         */
        public function get_current_category() {
            if (is_home()) {
                $catid = 0;
                $currentcat = false;
            } elseif ($catid = get_query_var('cat')) {
                $currentcat = get_category($catid);
            } else {
                $cat = get_the_category($post->ID);
                $currentcat = $cat[0];
                $catid = $currentcat->term_id;
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

    }

    add_action('init', array('CoWoBo', 'init'));
endif;