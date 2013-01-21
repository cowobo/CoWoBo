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


define ( 'COWOBO_CP_POST_KUDOS', 10 );
define ( 'COWOBO_CP_PROFILE_KUDOS', 40 );


/**
 * PATHs and URLs
 *
 * @since 0.1
 */
define('COWOBO_CP_DIR', plugin_dir_path(__FILE__));
define('COWOBO_CP_URL', plugin_dir_url(__FILE__));
define('COWOBO_CP_INC_URL', COWOBO_CP_URL . '_inc/');

if (!class_exists('CoWoBo_CubePoints')) :

    /**
     * @todo Give points to user / author(s) of post
     * @todo Add specific CoWoBo point actions
     * @todo Show point log @ own profile
     * @todo Show point log @ all profiles?
     * @display points and rank on user profile
     */
    class CoWoBo_CubePoints    {

        public $current_user_points = 0;
        public $current_user_rank = array( "rank" => "", "points" => "");
        public $next_rank = array( "rank" => "", "points" => "");
        public $ranks = array();

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
                cowobo()->do_admin_notice( "CubePoints plugin not active. To use CoWoBo CubePoints integration, enable CubePoints (as would seem quite logical to do..)" );
                return;
            }

            cowobo()->points = &$this;

            $this->setup_context();

            if (is_user_logged_in() )
                $this->setup_current_user();

            if ( is_user_logged_in() ) {
                add_action ( 'cowobo_after_content', array ( &$this, 'do_awesome_box' ) );
                add_action ( 'cp_log', array ( &$this, 'add_notification' ), 10, 4);
                add_action ( 'cowobo_after_post', array ( &$this, 'do_post_kudos_box'), 10, 3 );

                add_action ( 'wp', array ( &$this, '_maybe_give_kudos' ) );
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

        public function add_notification($type, $uid, $points, $data) {
            if (get_current_user_id() != $uid )
                return;

            if($points>0){
                $m= "+$points";
                cowobo()->add_notice( "Wow! That just got you $points extra points!" );
            } else {
                cowobo()->add_notice( "Careful, that action cost you $points!" );
            }

        }

        private function setup_context() {
            if( ! cp_module_activated('ranks') ) {
                cowobo()->do_admin_notice ( "Please activate CubePoints ranks module" );
                return;
            }

            $ranks = get_option('cp_module_ranks_data');
            krsort($ranks);
            $this->ranks = $ranks;
        }

        /**
         * Setup current user rank and next rank
         *
         * @return type
         */
        private function setup_current_user() {

            $current_user_points = $this->get_current_user_points();

            foreach( $this->ranks as $p => $r ) {
                if( $current_user_points >= $p ) {
                    $this->current_user_rank = array ( "rank" => $r, "points" => $p );
                    $this->next_rank = $previous_rank;
                    break;
                }
                $previous_rank = array ( "rank" => $r, "points" => $p );
            }
        }

        public function get_current_user_points() {
            if ( !is_user_logged_in() ) return null;

            if ( $this->current_user_points ) return $this->current_user_points;
            $this->current_user_points = $this->get_user_points( get_current_user_id() );
            return $this->current_user_points;
        }

        public function the_points( $uid = 0, $format = 1 ) {
            echo $this->get_user_points(get_current_user_id(), $format );
        }

            /**
             * Alias for cp_displayPoints
             */
            public function get_user_points( $uid = 0, $format = 0 ) {
                // First, check our cache for the current user (method will come back with UID if necessary)
                if ( ! $uid ) return $this->get_current_user_points();

                return cp_displayPoints( $uid, true, $format );
            }

        public function get_current_user_rank() {
            if ( !is_user_logged_in() ) return null;

            if ( $this->current_user_rank ) return $this->current_user_rank['rank'];
            $this->current_user_rank = $this->get_user_rank ( get_current_user_id() );
        }

        public function the_rank( $uid = 0 ) {
            echo $this->get_user_rank( $uid );
        }

            public function get_user_rank ( $uid = 0 ) {
                if ( !function_exists( 'cp_module_ranks_getRank' ) ) return;

                if ( ! $uid ) return $this->get_current_user_rank();

                return cp_module_ranks_getRank( $uid );
            }

        public function do_awesome_box() {
            echo "<div class='tab'>";
            echo "<p>" . $this->get_current_user_points() . "</p>";
            echo "<p>" . $this->get_current_user_rank() . "</p>";
            $this->do_progression();
            echo "</div>";
        }

        /**
         * @todo make this work with different UIDs
         * @param type $uid
         */
        public function do_progression( $uid = 0 ) {
            $current_points = (int) $this->current_user_points - (int) $this->current_user_rank['points'];
            $goal = (int) $this->next_rank['points'] - (int) $this->current_user_rank['points'];
            if ( $goal == 0 ) return false;
            $percentage = round ( ($current_points / $goal) * 100 );

            $image_url = COWOBO_CP_INC_URL . 'progress_bar.png';

            ?>
            <div class="points-progression-container" style="width:100px;border:1px solid #ccc;">
                <div class="stat-bar" style="width:<?php echo $percentage;?>px; overflow: hidden;">
                    <img alt="" src="<?php echo $image_url;?>"/>
                </div>
            </div>
            You need another <?php echo $goal - $current_points; ?> points to become a <?php echo $this->next_rank['rank']; ?>!
            <?php
        }

        /**
         * Box to gives kudos to post authors for logged in users
         *
         * @param type $postid
         * @param type $postcat
         * @param type $author
         * @return type
         * @todo make it look nice
         */
        public function do_post_kudos_box ( $postid, $postcat, $author ) {
            // Authors don't need to see this
            if ( $author ) return;


            echo "<div class='tab'>";

                // Different box for profiles
                if ( $postcat->slug == 'coder' ) {
                    $kudos_link = add_query_arg( array ( 'profile_kudos' => wp_create_nonce( "profile_kudos_$postid" ) ) );
                    echo "<p>Do you like " . get_the_title() . "? Or do you endorse the work of " . get_the_title() . "? Show it by giving props and rescept!</p>";

                } else {
                    $kudos_link = add_query_arg( array ( 'post_kudos' => wp_create_nonce( "post_kudos_$postid" ) ) );
                    echo "<p>Liked this post? Give the authors some kudos! It's free!*</p>";
                }
                echo "<p><a href='$kudos_link' class='button'>Give kudos</a></p>";

            echo "</div>";
        }

        public function _maybe_give_kudos() {
            if ( ! is_single() ) return;

                $postid = get_the_ID();
            if ( cowobo()->query->post_kudos ) {
                $object = 'post';
                if ( ! wp_verify_nonce ( cowobo()->query->post_kudos, "post_kudos_$postid" ) )
                    return;
            } elseif ( cowobo()->query->profile_kudos ) {
                $object = 'profile';
                if ( ! wp_verify_nonce ( cowobo()->query->profile_kudos, "profile_kudos_$postid" ) )
                    return;
            } else {
                return;
            }

            // Don't allow authors to kudo themselves
            if ( cowobo()->posts->is_user_post_author( $postid ) )
                return;

            // Give kudos!
            $this->give_kudos( $object, $postid );
        }

            private function give_kudos ( $object = '', $object_id = 0, $amount = 0, $origin= '' ) {
                if ( empty ( $origin ) ) $origin = $object;

                switch ( $object ) {
                    case 'post' :
                        if ( ! $amount ) $amount = COWOBO_CP_POST_KUDOS;
                        $authors = cowobo()->posts->get_post_authors( $object_id );
                        foreach ( $authors as $author_profile_id ) {
                            $this->give_kudos( 'profile', $author_profile_id, $amount, $origin );
                        }
                        break;
                    case 'profile' :
                        if ( ! $amount ) $amount = COWOBO_CP_PROFILE_KUDOS;
                        $users = cowobo()->users->get_users_by_profile_id( $object_id );
                        foreach ( $users as $user ) {
                            $this->give_kudos ( 'user', $user->ID, $amount, $origin );
                        }
                        break;
                    case 'user' :
                        if ( ! $amount ) $amount = 1; // Unidentified kudos. Let's just give one anyway.

                        $data = $this->get_kudos_description ( $origin );

                        cp_points("cowobo_kudos_$origin", $object_id, $amount, $data );
                        break;

                }
            }

                private function get_kudos_description( $origin ) {
                    $current_user = get_current_user();
                    $userlink = "<a href='" . cowobo()->users->get_current_user_profile_link() ."'>" . $current_user . "</a>";
                    switch ( $origin ) {
                        case 'post' :
                            $postlink = "<a href='" . get_post_permalink() . "'>" . get_the_title() . "</a>";
                            return "Received kudos from $userlink on $postlink";
                            break;
                        default:
                            return "Received kudus from $userlink";
                    }
                }

    }

    add_action('init', array('CoWoBo_CubePoints', 'init'));
endif;