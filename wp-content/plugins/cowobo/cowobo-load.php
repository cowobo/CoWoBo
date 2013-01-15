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
require_once ( COWOBO_PLUGIN_LIB . 'class-cowobo-query.php');
require_once ( COWOBO_PLUGIN_LIB . 'class-cowobo-users.php');

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
         * @var type
         */
        public $query;

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

        private function actions_and_filters() {
            add_action('template_redirect', array ( &$this, 'controller' ) );

        }

        /**
         * @todo Add nonces
         */
        public function controller() {
            $query = &$this->query;
            $verified = &$this->verified_query;
            $users = &$this->users;

            if( $verified->confirm ) $users->create_user();
            elseif( $query->userpw && !$_POST['user']) $users->login_user();


            elseif( $query->sort ) cwb_filter_feed();
            elseif( $query->showall ) cwb_related_feed();
            elseif( $query->delete ) cwb_delete_post();
            elseif( $query->new ) $GLOBALS['postid'] = cwb_create_post();
            elseif( $query->requesttype ) $notices = cwb_edit_request();
            elseif( $query->correctlang ) $notices = cwb_correct_translation();
            elseif( $query->emailtext && ! $query->user ) $notices = cwb_send_email();
            elseif( $query->linkto ) $notices = cwb_link_post();
            elseif( $query->commentid ) wp_delete_comment($_POST['commentid']);
            elseif( $query->post_ID ) $GLOBALS['postmsg'] = cwb_save_post();
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


    }

    add_action('init', array('CoWoBo', 'init'));
endif;