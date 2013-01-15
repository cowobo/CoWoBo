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
//define('COWOBO_PLUGIN_TEMPLATES_DIR', COWOBO_PLUGIN_DIR . 'templates/');
//define('COWOBO_PLUGIN_TEMPLATES_URL', COWOBO_PLUGIN_URL . 'templates/');
//define('COWOBO_PLUGIN_INC_URL', COWOBO_PLUGIN_TEMPLATES_URL . '_inc/');

if (!class_exists('CoWoBo')) :

    class CoWoBo    {

        /**
         * Creates an instance of the CoWoBo class
         *
         * @return CoWoBo object
         * @since 0.1
         * @static
        */
        public static function &init() {
            static $instance = false;

            if (!$instance) {
                load_plugin_textdomain('cowobo', false, basename(COWOBO_PLUGIN_DIR) . '/languages/');
                $instance = new CoWoBo;
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
        public function CoWoBo() {
            $this->__construct();
        }


    }

    add_action('init', array('CoWoBo', 'init'));
endif;