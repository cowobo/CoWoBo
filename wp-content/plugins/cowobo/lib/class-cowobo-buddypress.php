<?php
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

class CoWoBo_BuddyPress
{

    public $query_filter;

    public static function &init() {
        static $instance = false;

        if ( !$instance ) {
            $instance = new CoWoBo_BuddyPress;
        }

        return $instance;
    }

    public function __construct() {
        global $cowobo;
        $cowobo->buddypress = &$this;

        $this->filter_querystring();
        $this->content_filters();

    }

    private function content_filters() {
        add_filter ( 'bp_core_get_user_domain', array ( &$this, 'cowobo_user_domain' ), 12, 2 );
    }

    public function cowobo_user_domain( $domain, $user_id ) {
        global $cowobo;
        return $cowobo->users->get_user_domain ( $user_id );
    }

    private function filter_querystring() {
        // @todo Is remove filter working?
        remove_filter( 'bp_ajax_querystring', 'bp_dtheme_ajax_querystring');
        add_filter ( 'bp_ajax_querystring', array ( &$this, 'ajax_querystring' ), 11, 2 );
    }

    public function ajax_querystring( $query_string, $object ) {
        global $cowobo;
        $qf = &$this->query_filter;

        $qs = array();
        switch ( $object ) {
            case 'activity' :
                if ( $cowobo->users->is_profile() ) {
                    $current_profile = $cowobo->users->displayed_user;
                    switch ( $qf ) {
                        case 'mentions' :
                            $qs[] = "search_terms=@{$current_profile->user_nicename}<";
                            $qs[] = "user_id=0";
                            break;
                        case 'user' :
                            $qs[] = "user_id={$current_profile->ID}";

                    }
                }
                break;
        }

        if ( ! empty( $qs ) )
            $query_string = join( '&', (array) $qs );

        return $query_string;
    }

}
add_action( 'bp_init', array( 'CoWoBo_BuddyPress', 'init' ) );