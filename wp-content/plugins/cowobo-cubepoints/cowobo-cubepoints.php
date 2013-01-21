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

        }

        /**
         * PHP4
         *
         * @since 0.1
             */
        public function CoWoBo_CubePoints() {
            $this->__construct();
        }


    }

    add_action('init', array('CoWoBo_CubePoints', 'init'));
endif;