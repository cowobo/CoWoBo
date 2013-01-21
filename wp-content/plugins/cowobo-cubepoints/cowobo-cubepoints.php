<?php
/*
  Plugin Name: CoWoBo CubePoints
  Plugin URI: http://cowobo.org
  Description: Another CoWoBo Module! Allows for easy enabling and disabling of CubePoints integration for Cowobo
  Version: 0.1
  Author: Cowobo
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
define('COWOBO_CP_VERSION', '0.1');

/**
 * PATHs and URLs
 *
 * @since 0.1
 */
define('COWOBO_CP_DIR', plugin_dir_path(__FILE__));
define('COWOBO_CP_URL', plugin_dir_url(__FILE__));
define('COWOBO_CP_TEMPLATES_DIR', COWOBO_CP_DIR . 'templates/');
define('COWOBO_CP_TEMPLATES_URL', COWOBO_CP_URL . 'templates/');
define('COWOBO_CP_INC_URL', COWOBO_CP_TEMPLATES_URL . '_inc/');

if (!class_exists('CoWoBo_CubePoints')) :

    class CoWoBo_CubePoints    {

        /**
         * Creates an instance of the CoWoBo_CubePoints class
         *
         * @return CoWoBo_CubePoints object
         * @since 0.1
         * @static
        */
        public static function &init() {
            static $instance = false;

            if (!$instance) {
                $instance = new CoWoBo_CubePoints;
            }

            return $instance;
        }

        /**
         * Constructor
         *
         * @since 0.1
         */
        public function __construct() {
            if ( !defined ( 'CP_VER' ) ) {
                cowobo()->admin_notice = "CubePoints plugin not active. To use CoWoBo CubePoints integration, enable CubePoints (as would seem quite logical to do..)";
                add_action ('admin_notices', array ( cowobo(), 'admin_notice' ) );
                return;
            }

            cowobo()->points = &$this;

            if ( is_user_logged_in() ) {
                add_action ( 'cowobo_after_content', array ( &$this, 'do_awesome_box' ) );
            }

        }

            /**
             * PHP4
             *
             * @since 0.1
             */
            public function CoWoBo_CubePoints() {
                $this->__construct();
            }

        public function the_points( $uid = 0, $format = 1 ) {
            echo $this->get_user_points( $uid, $format );
        }

            /**
             * Alias for cp_displayPoints
             */
            public function get_user_points( $uid = 0, $format = 1 ) {
                return cp_displayPoints( $uid, true, $format );
            }

        public function the_rank( $uid = 0 ) {
            echo $this->get_user_rank( $uid );
        }

            public function get_user_rank ( $uid = 0 ) {
                if ( !function_exists( 'cp_module_ranks_getRank' ) ) return;

                if ($uid == 0) {
                    if (!is_user_logged_in()) {
                      return false;
                    }
                    $uid = cp_currentUser();
                }

                return cp_module_ranks_getRank( $uid );
            }

        public function do_awesome_box() {
            echo "<div class='tab'>";
            echo "<p>" . $this->get_user_points() . " and rank: " . $this->get_user_rank() . "</p>";
            echo "</div>";
        }

    }

    add_action('init', array('CoWoBo_CubePoints', 'init'));
endif;