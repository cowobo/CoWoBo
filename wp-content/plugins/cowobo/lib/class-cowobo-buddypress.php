<?php
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

require_once ( COWOBO_PLUGIN_LIB . 'buddypress-ajax.php' );

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

        cowobo()->buddypress = &$this;

        $this->filter_querystring();
        $this->content_filters();

		add_action( 'bp_enqueue_scripts', array( $this, 'enqueue_scripts'  ) ); // Enqueue theme JS
        add_action ( 'wp_head', array ( &$this, 'do_notifications' ) );

        $this->add_ajax();

    }

    public function do_notifications() {
        if ( cowobo()->users->is_current_user_profile() )
            // Remove at_mention notifications
            bp_activity_remove_screen_notifications();
        
        $this->add_notifications();
    }


    private function add_notifications() {
        if ( ! $notifications = bp_core_get_notifications_for_user( bp_loggedin_user_id() ) )
            return;

        foreach ( $notifications as $notification ) {
            cowobo()->add_notice( $notification, 'message' );
        }

    }

    private function add_ajax() {
		/** Ajax **************************************************************/

		$actions = array(

			// Activity
			'activity_get_older_updates'  => 'bp_legacy_theme_activity_template_loader',
//			'activity_mark_fav'           => 'bp_legacy_theme_mark_activity_favorite',
//			'activity_mark_unfav'         => 'bp_legacy_theme_unmark_activity_favorite',
			'activity_widget_filter'      => 'bp_legacy_theme_activity_template_loader',
			'delete_activity'             => 'bp_legacy_theme_delete_activity',
			'delete_activity_comment'     => 'bp_legacy_theme_delete_activity_comment',
			'get_single_activity_content' => 'bp_legacy_theme_get_single_activity_content',
			'new_activity_comment'        => 'bp_legacy_theme_new_activity_comment',
			'post_update'                 => 'bp_legacy_theme_post_update',
			'bp_spam_activity'            => 'bp_legacy_theme_spam_activity',
			'bp_spam_activity_comment'    => 'bp_legacy_theme_spam_activity',

		);

		/**
		 * Register all of these AJAX handlers
		 *
		 * The "wp_ajax_" action is used for logged in users, and "wp_ajax_nopriv_"
		 * executes for users that aren't logged in. This is for backpat with BP <1.6.
		 */
		foreach( $actions as $name => $function ) {
			add_action( 'wp_ajax_'        . $name, $function );
			add_action( 'wp_ajax_nopriv_' . $name, $function );
		}
    }

	public function head_scripts() {
	?>

		<script type="text/javascript" charset="utf-8">
			/* <![CDATA[ */
			var ajaxurl = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
			/* ]]> */
		</script>

	<?php
	}

    public function enqueue_scripts() {
        wp_enqueue_script( 'cowobo-buddypress', get_template_directory_uri() . '/templates/buddypress/bp.js', array ( 'jquery' ), COWOBO_PLUGIN_VERSION, true );
        wp_enqueue_style( 'cowobo-buddypress', get_template_directory_uri() . '/templates/buddypress/bp.css', null, COWOBO_PLUGIN_VERSION );
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

        // Make sure mentions have the right link
        add_filter ( 'bp_activity_multiple_at_mentions_notification', array ( &$this, 'at_mention_notification_replace_link' ), 10, 2 );
        add_filter ( 'bp_activity_single_at_mentions_notification', array ( &$this, 'at_mention_notification_replace_link' ), 10, 2 );


    }

    public function at_mention_notification_replace_link( $notification, $link ) {
        $newlink = cowobo()->users->get_current_user_profile_link() . "#mentions";
        return str_replace( $link, $newlink,  $notification );
    }

    public function cowobo_user_domain( $domain, $user_id ) {

        return cowobo()->users->get_user_domain ( $user_id );
    }

    private function filter_querystring() {
        // @todo Is remove filter working?
        remove_filter( 'bp_ajax_querystring', 'bp_dtheme_ajax_querystring');
        add_filter ( 'bp_ajax_querystring', array ( &$this, 'ajax_querystring' ), 11, 2 );
    }

    public function ajax_querystring( $query_string, $object ) {

        if ( cowobo()->query->scope ) $this->query_filter = cowobo()->query->scope;
        $qf = &$this->query_filter;

        $qs = array();

        // Defaults
        $qs[] = "per_page=3";

        if ( ! empty( $_POST['page'] ) && '-1' != $_POST['page'] )
                $qs[] = 'page=' . $_POST['page'];

        switch ( $object ) {
            case 'activity' :
                if ( cowobo()->users->is_profile() || cowobo()->query->user_id ) {
                    if ( cowobo()->query->user_id )
                        $current_profile = get_userdata ( cowobo()->query->user_id );
                    else
                        $current_profile = cowobo()->users->displayed_user;

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
        $displayed_user = cowobo()->users->displayed_user;

        if ( cowobo()->users->is_profile() ) {
            $target = ( cowobo()->users->is_current_user_profile() ) ? 'user' : 'mentions';
            echo "<input type='hidden' name='target' id='cowobo-form-target' value='$target'>";

            if ( ! cowobo()->users->is_current_user_profile() ) {
                echo "<input type='hidden' name='object' id='whats-new-post-object' value='user_profile'>";
                echo "<input type='hidden' name='item_id' id='whats-new-post-in' value='{$displayed_user->ID}'>";
                echo "<input type='hidden' name='user_nicename' id='whats-new-post-in' value='{$displayed_user->user_nicename}'>";
            }
        }
    }

    public function update_filter( $object = '', $item_id = '', $content = '' ) {


        // Sanity check
        if ( empty ( $item_id ) || empty ( $content ) ) return $object;

        if ( $object != "user_profile" ) return $object;

        $user = get_userdata( $item_id );
        $nicename = $user->user_nicename;
        // Are we already mentioning?
        if ( strpos ( $content, "@$nicename" ) !== false )
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