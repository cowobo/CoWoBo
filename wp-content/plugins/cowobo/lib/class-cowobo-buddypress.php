<?php
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

class CoWoBo_BuddyPress
{

    public static function &init() {
        static $instance = false;

        if ( !$instance ) {
            $instance = new CoWoBo_BuddyPress;
        }

        return $instance;
    }

    public function __construct() {
        // @todo
        //remove_filter( 'bp_ajax_querystring', 'bp_dtheme_ajax_querystring');
        add_filter ( 'bp_ajax_querystring', array ( &$this, 'ajax_querystring' ), 11, 2 );
    }

    public function ajax_querystring( $query_string, $object ) {
        global $cowobo;

        $qs = array();
        switch ( $object ) {
            case 'activity' :
                if ( $cowobo->users->is_profile() ) {
                    $current_profile = $cowobo->users->displayed_user;
                    $qs[] = "user_id={$current_profile->ID}";
                }
                break;
        }

        if ( ! empty( $qs ) )
            $query_string = join( '&', (array) $qs );

        return $query_string;
    }

}
add_action( 'bp_include', array( 'CoWoBo_BuddyPress', 'init' ) );