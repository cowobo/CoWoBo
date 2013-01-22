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
     * @todo Add specific CoWoBo point actions
     * @todo Add strings for point actions
     */
    class CoWoBo_CubePoints    {

        public $ranks = array();

        public $current_user_points     = 0;
        public $current_user_rank       = array( "rank" => "", "points" => "");
        public $current_user_next_rank  = array( "rank" => "", "points" => "");

        public $displayed_user_points   = 0;
        public $displayed_user_rank     = array( "rank" => "", "points" => "");
        public $displayed_user_next_rank
                                        = array( "rank" => "", "points" => "");

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

            if ( is_user_logged_in() ) {
                $this->setup_current_user();
                add_action ( 'cowobo_after_content', array ( &$this, 'do_awesome_box' ) );
                add_action ( 'cp_log', array ( &$this, 'add_notification' ), 10, 4);

                add_action ( 'cowobo_after_post', array ( &$this, 'do_points_log_box'), 20, 3 );
                add_action ( 'cowobo_after_post', array ( &$this, 'do_post_kudos_box'), 30, 3 );

                add_action ( 'wp', array ( &$this, '_maybe_give_kudos' ) );
            }

            add_action ( 'cowobo_after_layouts', array ( &$this, 'do_user_profile_points'), 10, 3 );
            add_action('cowobo_logs_description', array ( &$this, 'cp_logs_desc' ), 10, 4);

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
            $current_user_ranks = $this->get_user_ranks_by_points( $current_user_points );

            $this->current_user_rank        = $current_user_ranks['current_rank'];
            $this->current_user_next_rank   = $current_user_ranks['next_rank'];

        }

        private function setup_displayed_user() {
            $display_user_id = cowobo()->users->displayed_user->ID;
            $displayed_user_points = $this->displayed_user_points = $this->get_user_points( $display_user_id );
            $displayed_user_ranks = $this->get_user_ranks_by_points( $displayed_user_points );

            $this->displayed_user_rank        = $displayed_user_ranks['current_rank'];
            $this->displayed_user_next_rank   = $displayed_user_ranks['next_rank'];
        }

            private function get_user_ranks_by_points ( $points ) {
                $previous_rank = '';
                foreach( $this->ranks as $p => $r ) {
                    if( $points >= $p ) {
                        $current_rank = array ( "rank" => $r, "points" => $p );
                        $next_rank = $previous_rank;
                        break;
                    }
                    $previous_rank = array ( "rank" => $r, "points" => $p );
                }

                if ( empty ( $next_rank ) ) $next_rank = $current_rank;

                return array (
                    "current_rank" => $current_rank,
                    "next_rank" => $next_rank,
                );
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
            echo "<p>You are a <strong>" . $this->get_current_user_rank() . "</strong> with " . $this->get_current_user_points() . " awesomeness.</p>";
            echo "<p>Next rank: <strong>" . $this->current_user_next_rank['rank'] . "</strong>";
            $this->do_progression( $this->current_user_points, $this->current_user_rank, $this->current_user_next_rank );
            echo "</p>";
            echo "</div>";
        }

        /**
         * @todo make this work with different UIDs
         * @param type $uid
         */
        public function do_progression( $points, $current_rank, $next_rank ) {

            $current_points = (int) $points - (int) $current_rank['points'];
            $goal = (int) $next_rank['points'] - (int) $current_rank['points'];
            if ( $goal == 0 ) return false;
            $percentage = round ( ($current_points / $goal) * 100 );

            $image_url = COWOBO_CP_INC_URL . 'progress_bar.png';

            ?>
            <div class="points-progression-container" style="width:100px;border:1px solid #ccc;display: inline-block">
                <div class="stat-bar" style="width:<?php echo $percentage;?>px; overflow: hidden;">
                    <img title="You need another <?php echo $goal - $current_points; ?> points to become a <?php echo $next_rank['rank']; ?>!" src="<?php echo $image_url;?>"/>
                </div>
            </div>
            <?php
            return $goal - $current_points;

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

                        if ( $this->kudos_already_given() ) {
                            cowobo()->add_notice("Sorry, you have already shown your appreciation for this post before!");
                            return;
                        }

                        foreach ( $authors as $author_profile_id ) {
                            $this->give_kudos( 'profile', $author_profile_id, $amount, $origin );
                        }
                        break;
                    case 'profile' :
                        if ( $origin == $object && $this->kudos_already_given() ) {
                            cowobo()->add_notice("You must really like this angel! Sorry, but you can't give props to the same person twice.");
                            return;
                        }
                        if ( ! $amount ) $amount = COWOBO_CP_PROFILE_KUDOS;
                        $users = cowobo()->users->get_users_by_profile_id( $object_id );
                        foreach ( $users as $user ) {
                            $this->give_kudos ( 'user', $user->ID, $amount, $origin );
                        }
                        break;
                    case 'user' :
                        if ( ! $amount ) $amount = 1; // Unidentified kudos. Let's just give one anyway.

                        $data = "userid=" . get_current_user_id() . "&postid=" . get_the_ID();
                        cp_points("cowobo_kudos_$origin", $object_id, $amount, $data );
                        break;

                }
            }

            private function kudos_already_given ( $data = '' ) {
                global $wpdb;

                if ( empty ( $data) ) $data = "userid=" . get_current_user_id() . "&postid=" . get_the_ID();

                $query = $wpdb->prepare ( 'SELECT * FROM `' . CP_DB . '` WHERE data = %s', $data );
                $results = $wpdb->get_results( $query, 'ARRAY_A' );
                return ( ! empty ( $results ) );
            }

        public function cp_logs_desc( $type, $uid, $points, $data ){
            if ( substr ( $type, 0, 7 ) == 'cowobo_' ) {
                $type = substr ( $type, 7 );

                $data_arr = array();
                parse_str ( $data, $data_arr );
                if ( empty ( $data_arr ) ) {
                    echo "Corrupted data";
                    return;
                }

                if ( ! isset ( $data_arr['userid'] ) ) return false;
                $user_profile = get_post( cowobo()->users->get_user_profile_id( $data_arr['userid'] ) );

                switch ( $type ) {
                    case 'kudos_profile' :
                        echo '<a href="'.get_permalink( $user_profile ).'">' . $user_profile->post_title . '</a> gave props and respect';
                        break;
                    case 'kudos_post' :
                        if ( ! isset ( $data_arr['postid'] ) ) return false;
                        $post = get_post( $data_arr['postid'] );
                        echo 'Kudos on <a href="'.get_permalink( $post ).'">' . $post->post_title . '</a> from <a href="'.get_permalink( $user_profile ).'">' . $user_profile->post_title . '</a>';
                        break;
                }
            } else {
                switch ( $type ) {
                    case 'dailypoints' :
                        echo "Thanks for checking in on CoWoBo today!";
                        break;
                    case 'post' :
                        $post = get_post($data);
                        echo 'Added a new post, <a href="'.get_permalink( $post ).'">' . $post->post_title . '</a>';
                        break;


                    default:
                        do_action('cp_logs_description', $type, $uid, $points, $data );
                        break;
                }
            }




            return;
        }

        public function do_user_profile_points ( $postid, $postcat, $author ) {
            if ( ! cowobo()->users->is_profile() ) return;

            $this->setup_displayed_user();

            echo '<span class="field"><h3>Awesomeness:</h3><span class="hint">' . $this->displayed_user_points . '</span></span>';
            echo '<span class="field"><h3>Rank:</h3><span class="hint">' . $this->displayed_user_rank['rank'] . '</span></span>';
            $this->do_progression( $this->displayed_user_points, $this->displayed_user_rank, $this->displayed_user_next_rank );
        }

        public function do_points_log_box( $postid, $postcat, $author ) {
            if ( ! cowobo()->users->is_profile() ) return;

            $GLOBALS['wpdb']->show_errors();
            $log = $this->get_log ( cowobo()->users->displayed_user->ID, 15 );

            require ( COWOBO_CP_DIR . 'templates/log.php' );

        }

            private function get_log( $type = 'all', $limit = 10 ) {
                global $wpdb;

                $q      = '';
                $limitq = '';

                $uid = (int) cowobo()->users->displayed_user->ID;

                if ( 'all' != $type )
                    $q = $wpdb->prepare ( " WHERE `uid` = %d", $uid );

                if( $limit > 0 )
                    $limitq = 'LIMIT '.(int) $limit;

                $query = 'SELECT * FROM `' . CP_DB . '` ' . $q . ' ORDER BY timestamp DESC ' . $limitq;
                return $wpdb->get_results( $query );
            }

    }

    add_action('init', array('CoWoBo_CubePoints', 'init'));
endif;