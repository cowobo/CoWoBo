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

        // Add updated post activity
        add_filter ( 'cowobo_post_updated', array ( &$this, 'record_post_edited' ), 10, 3 );

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

    function record_post_edited( $post_id, $post_title ) {
        global $bp, $wpdb;

        $post_id = (int) $post_id;
        $blog_id = (int) $wpdb->blogid;
        $user_id = get_current_user_id();

        // If blog is not trackable, do not record the activity.
        if ( ! bp_blogs_is_blog_trackable( $blog_id, $user_id ) )
            return false;

        if ( !$user_id )
            return false;

        $is_blog_public = apply_filters( 'bp_is_blog_public', (int)get_blog_option( $blog_id, 'blog_public' ) );

        if ( $is_blog_public || !is_multisite() ) {

            // Record this in activity streams
            $post_permalink   = get_permalink( $post_id );

            if ( is_multisite() )
                $activity_action  = sprintf( __( '%1$s updated the post %2$s, on the site %3$s', 'cowobo' ), bp_core_get_userlink( (int) $user_id ), '<a href="' . $post_permalink . '">' . $post_title . '</a>', '<a href="' . get_blog_option( $blog_id, 'home' ) . '">' . get_blog_option( $blog_id, 'blogname' ) . '</a>' );
            else
                $activity_action  = sprintf( __( '%1$s updated the post %2$s', 'cowobo' ), bp_core_get_userlink( (int) $user_id ), '<a href="' . $post_permalink . '">' . $post_title . '</a>' );

            // Make sure there's not an existing entry for this post (prevent bumping)
            if ( bp_is_active( 'activity' ) ) {
                $existing = bp_activity_get( array(
                    'filter' => array(
                        'user_id'      => (int) $user_id,
                        'action'       => 'updated_blog_post',
                        'primary_id'   => $blog_id,
                        'secondary_id' => $post_id,
                    )
                ) );

                if ( !empty( $existing['activities'] ) ) {
                    return;
                }
            }

            $activity_content = "";

            bp_blogs_record_activity( array(
                'user_id'           => (int) $user_id,
                'action'            => apply_filters( 'bp_blogs_activity_updated_post_action',       $activity_action,  $post_title, $post_permalink ),
                'content'           => apply_filters( 'bp_blogs_activity_updated_post_content',      $activity_content, $post_title, $post_permalink ),
                'primary_link'      => apply_filters( 'bp_blogs_activity_updated_post_primary_link', $post_permalink,   $post_id               ),
                'type'              => 'updated_blog_post',
                'item_id'           => $blog_id,
                'secondary_item_id' => $post_id,
                'recorded_time'     => current_time ( 'mysql', 1 )
            ));
        }

        // Update the blogs last activity
        bp_blogs_update_blogmeta( $blog_id, 'last_activity', bp_core_current_time() );

        do_action( 'bp_blogs_updated_blog_post', $post_id, $post_title, $user_id );
    }

}
add_action( 'bp_init', array( 'CoWoBo_BuddyPress', 'init' ) );