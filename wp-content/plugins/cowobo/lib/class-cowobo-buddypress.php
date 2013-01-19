<?php
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

/**
 * @todo make sure user nicename is more appropriate
 */
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
        add_filter ( 'bp_core_get_user_domain', array ( &$this, 'cowobo_user_domain' ), 10, 2 );

        // Add context to BP Activity form
        add_action ( 'bp_activity_post_form_options', array ( &$this, 'cowobo_activity_context' ) );
        // Make sure activities posted on a user's profile actually end up there
        add_filter ( 'bp_activity_custom_update', array ( &$this, 'update_filter' ), 10, 3 );

        // Disable favorites
        add_filter ( 'bp_activity_can_favorite' ,'__return_false' );
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

        // Defaults
        $qs[] = "per_page=3";

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

    public function cowobo_activity_context() {
        global $cowobo;
        if ( $cowobo->users->is_profile() )
            //echo "<input type='hidden' name='context' value='user_id'>";
            echo "<input type='hidden' name='object' id='whats-new-post-object' value='user_profile'>";
            echo "<input type='hidden' name='item_id' id='whats-new-post-in' value='{$cowobo->users->displayed_user->ID}'>";
            echo "<input type='hidden' name='user_nicename' id='whats-new-post-in' value='{$cowobo->users->displayed_user->user_nicename}'>";
    }

    public function update_filter( $object = '', $item_id = '', $content = '' ) {
        global $cowobo;

        // Sanity check
        if ( empty ( $item_id ) || empty ( $content ) ) return $object;

        if ( $object != "user_profile" ) return $object;

        $user = get_userdata( $item_id );
        $nicename = $user->user_nicename;
        // Are we already mentioning?
        if ( strpos ( $content, "@$nicename" ) )
            $post = $content;

        // Name mentioned, no mention yet
        elseif ( strpos ( $content, $nicename ) )
            $post = $this->str_replace_once ( $nicename, "@$nicename", $content );

        // Bad user!
        else
            $post = "@$nicename: $content";

        return bp_activity_post_update( array( 'content' => $post ) );
    }

    function str_replace_once($search, $replace, $subject) {
        $firstChar = strpos($subject, $search);
        if($firstChar !== false) {
            $beforeStr = substr($subject,0,$firstChar);
            $afterStr = substr($subject, $firstChar + strlen($search));
            return $beforeStr.$replace.$afterStr;
        } else {
            return $subject;
        }
    }

}
add_action( 'bp_init', array( 'CoWoBo_BuddyPress', 'init' ) );