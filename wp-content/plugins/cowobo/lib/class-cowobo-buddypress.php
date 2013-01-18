<?php
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

class CoWoBo_BuddyPress
{
    public function __construct() {
        // @todo
        remove_filter( 'bp_ajax_querystring', 'bp_dtheme_ajax_querystring');
        add_filter ( 'bp_ajax_querystring', array ( &$this, 'ajax_querystring' ) );
    }

    public function ajax_querystring( $query_string, $object ) {
        global $cowobo;

        $qs = array();
        switch ( $object ) {
            case 'activity' :
                if ( $cowobo->posts->is_profile() ) {
                    $current_profile = $cowobo->users->displayed_user_id;
                    $new_qs[] = "user_id=$current_profile";
                }
                break;
        }

        if ( ! empty( $qs ) )
            $query_string = join( '&', (array) $qs );

        return $query_string;
    }

}