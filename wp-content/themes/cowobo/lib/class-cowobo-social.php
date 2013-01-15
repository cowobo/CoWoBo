<?php
/*
 *      class-cowobo-social.php
 *
 *      Copyright 2012 Coders Without Borders
 *
 *      This program is free software; you can redistribute it and/or modify
 *      it under the terms of the GNU General Public License as published by
 *      the Free Software Foundation; either version 2 of the License, or
 *      (at your option) any later version.
 *
 *      This program is distributed in the hope that it will be useful,
 *      but WITHOUT ANY WARRANTY; without even the implied warranty of
 *      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *      GNU General Public License for more details.
 *
 *      You should have received a copy of the GNU General Public License
 *      along with this program; if not, write to the Free Software
 *      Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 *      MA 02110-1301, USA.
 *
 *
 */

/**
 * This class regulates all front end user interaction
 *
 * @package cowobo-social
 */

class Cowobo_Social {
    /**
     * Gives the profile id of the logged in user
     *
     * @var int
     */
    public $profile_id;
    /**
     * Constructor for cowobo social
     *
     * @param boolean $skip_construct true skips constructor
     */
	public function __construct( $skip_construct = false ) {
		if ( $skip_construct ) return;

		// Profile nag
		$userid = wp_get_current_user()->ID;

		$this->profile_id = get_user_meta($userid, 'cowobo_profile', true);
		
		// RSS
		add_filter('pre_get_posts',array( &$this, 'feed_filter' ));

		// Share
		add_action('init', array ( &$this, 'share_scripts') );
		//add_action ( 'wp_footer',array ( &$this, 'total_sharing' ) );

		// Schedule share cron job
		add_action('daily_events', array ( &$this, 'update_total_count' ) );

        // Change redirect social connect
        add_filter( 'social_connect_redirect_to', array ( &$this, 'redirect_after_social_login' ) );

        // Restrict dashboard access
        add_action( 'admin_head', array ( &$this, 'restrict_dashboard'), 0 );

        // Rewrite for the personal feed
        add_action ( 'init', array ( &$this, 'personal_feed_url' ) );

        // Template for personal feed rss
        remove_all_actions( 'do_feed_rss2' );
        add_action( 'do_feed_rss2', array ( &$this, 'personal_feed_rss_template' ), 10, 1 );
	}

	/* === User Profiles === */
	/**
     * Creates the new user profile
     *
     * @param int $userid
     * @return int profile id
     */
	public function new_user_profile ( $userid ) {
		$user = get_userdata($userid);
		if (!empty($user->cowobo_profile)) return false;
		if (!wp_update_user (array ('ID' => $userid, 'role' => 'author' ))) return false;
		$post = array(
			  'post_author' => $userid,
			  'post_category' => array(get_cat_ID('Coders')),
			  'post_content' => " ",
			  'post_name' => $user->user_login,
			  'post_status' => 'publish',
			  'post_title' => $user->display_name,
			  'post_type' => 'post'
		);

		if ( !$profileid = wp_insert_post($post) ) return false;
		if ( !update_user_meta( $userid, 'cowobo_profile', $profileid) ) return false;
		return $profileid;
	}

	/* === RSS === */
    /**
     * General filter calling all the RSS mods
     *
     * @param type $query
     * @return type
     */
	public function feed_filter ( $query ) {
		if ($query->is_feed) {
			add_filter('the_content', array( &$this, 'feed_content_filter' ) );
			if ( ! $query->is_category )
            add_filter('the_title', array( &$this, 'feed_title_filter' ), 10, 2 );
		}
		return $query;
	}

    public function feed_title_filter ( $title, $post_id ) {
        $type = cwob_get_category( $post_id );
        $title = "{$type->name}: $title";
        return $title;
    }

    /**
     * Add query to user feed
     *
     * @param int $userid
     * @param arr $feed_query
     * @return boolean true on success
     */
	public function add_to_feed($userid,$feed_query) {
		$currentfeed = get_user_meta($userid,'cowobo_feed',true);
		if (empty($currentfeed)) $currentfeed = array();
		$currentfeed[] = $feed_query;
		update_user_meta($userid,'cowobo_feed',$currentfeed);
		return true;
	}

    /**
     * Full reset on user's personal feed
     *
     * @param int $userid
     * @return boolean true on success
     */
	public function reset_feed($userid) {
		update_user_meta($userid,'cowobo_feed', '');
		return true;
	}

	/**
     * Add thumbs to feed
     *
     * @param object $content
     * @return object content with image
     */
	public function feed_content_filter($content) {
		$img = cwob_get_first_image($post->ID);
		if($img) {
			$image = '<img align="left" src="'. $img .'" alt="" />';
			echo $image;
		}
		return $content;
	}

    /**
     * Returns user object from profile post id
     *
     * Use with care - searches through the metafields of all posts.
     *
     * @global obj $wpdb
     * @param int $profile_id
     * @return int $userid
     */
    public function get_user_from_profile_id ( $profile_id ) {
        global $wpdb;
        $usermeta = $wpdb->prefix . 'usermeta';

        $select_user = "SELECT user_id FROM $usermeta WHERE meta_key = 'cowobo_profile' AND meta_value = '$profile_id'";
        $user_id = $wpdb->get_var( $select_user );

        return $user_id;
    }

    public function get_profile_url ( $userid ) {
        $ret = get_permalink($this->profile_id);
        return $ret;
    }

    public function redirect_after_social_login( $redirect_to ){
        return home_url();
    }

    public function get_profile_shares ( $post_id ) {
        if ( ! $profile_shares = get_post_meta ( $post_id, 'cowobo_profile_shares', true ) )
            $profile_shares = 0;
        return $profile_shares;
    }

    /**
     * Echos the share buttons
     *
     * @param int $post_id
     */
	public function show_share ( $post_id ) {
		$option = $this->share_options();
		$post_link = get_permalink( $post_id );
		$post_title = get_the_title ( $post_id );
        $userid = wp_get_current_user()->ID;
        $profileshares = $this->get_profile_shares( $post_id );

		$output = '';

		if ($option['active_buttons']['google_plus']==true) {
			$data_count = ($option['google_count']) ? '' : 'count="false"';
			$output .= '
				<div class="sharebutton ongoogle">
				<g:plusone size="medium" href="' . $post_link . '"'.$data_count.'></g:plusone>
				</div>';
		}

		if ($option['active_buttons']['twitter']==true) {
			$data_count = ($option['twitter_count']) ? 'horizontal' : 'none';
			if ($option['twitter_id'] != ''){
				$output .= '
				<div class="sharebutton ontwitter">
					<a href="http://twitter.com/share" class="twitter-share-button" data-url="'. $post_link .'"  data-text="'. $post_title . '" data-count="'.$data_count.'" data-via="'. $option['twitter_id'] . '">Tweet</a>
					</div>';
			} else {
				$output .= '
					<div class="sharebutton ontwitter">
					<a href="http://twitter.com/share" class="twitter-share-button" data-url="'. $post_link .'"  data-text="'. $post_title . '" data-count="'.$data_count.'">Tweet</a>
					</div>';
			}
		}

		if ($option['active_buttons']['stumbleupon']==true) {
			$output .= '
				<div class="sharebutton onstumble">
				<iframe src="http://www.stumbleupon.com/badge/embed/1/?url='.urlencode($post_link).'" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:74px; height: 18px;" allowtransparency="true"></iframe></div>';
		}

		if ($option['active_buttons']['facebook_like']==true) {
			$output .= '
				<div class="sharebutton onfacebook">
				<iframe src="http://www.facebook.com/plugins/like.php?href=' . urlencode($post_link) . '&amp;layout=button_count&amp;show_faces=false&amp;width='.$option['facebook_like_width'].'&amp;action=like&amp;font=verdana&amp;colorscheme=light&amp;height=21" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width='.$option['facebook_like_width'].'px; height:21px;" allowTransparency="true"></iframe></div>';
		}

		//if ($option['active_buttons']['linkedin']==true) {
			//$counter = ($option['linkedin_count']) ? 'right' : '';
			//$output .= '
				//<div class="sharebutton" style="width:' .$option['linkedin_width']. 'px;">
				//<script type="in/share" data-url="' . $post_link . '" data-counter="' .$counter. '"></script>
				//</div>';
		//}
		echo $output;
	}

    /**
     * Gets the options for social share from db
     * @return array options
     */
	public function share_options () {
		$option = get_option('cowobo_share');

		if ($option===false) {
			$option = $this->share_default();
			add_option('cowobo_share', $option);
		}
		return $option;
	}

    /**
     * Enqueues share scripts
     */
	public function share_scripts() {
		wp_enqueue_script('cowobo_share_gplus', 'https://apis.google.com/js/plusone.js','','',true);
		wp_enqueue_script('cowobo_share_twitter', 'http://platform.twitter.com/widgets.js','','',true);
	}

    /**
     * Sets the defaults for sharing
     * @return array defaults
     */
	private function share_default() {
		$option = array(
			'auto' => true,
			'active_buttons' => array('facebook_like'=>true, 'twitter'=>true, 'stumbleupon'=>true, 'google_plus'=>true, 'linkedin'=>true),
			'border' => 'none',
			'bkcolor' => false,
			'bkcolor_value' => '#ffffff',
			'facebook_like_width' => '85',
			'twitter_width' => '95',
			'linkedin_width' => '105',
			'stumbleupon_width' => '85',
			'google_width' => '80',
			'google_count' => true,
			'twitter_count' => true,
			'linkedin_count' => true
		);
		return $option;
	}

    /**
     * Updates the total-like count from ShareThis into the database
     * @global object $wpdb
     * @param array $postids
     */
	public function update_total_count( $postids = false ) {
		$pubkey = "4a45176a-73d4-4e42-8be9-c015a589c031";
		$accesskey = "b7766bcf68b38dc47009f4e8eec78957";
		global $wpdb;
		if ( ! $postids ) $postids = get_published_ids();
		foreach ( $postids as $postid ) {
			$url = get_permalink ( $postid );
            $profile_shares = $this->get_profile_shares ( $postid );
			$share_count = $this->get_total_count_sharethis ( $url ) + $profile_shares;
			update_post_meta ( $postid, 'cowobo_share_count', $share_count );
			update_post_meta ( $postid, 'cowobo_popularity', $this->get_popularity_count( $postid, true ) );
		}
		// return $share_count;
	}

    /**
     * Gets the total share / like count from ShareThis
     * @param str $url
     * @return var $total_count
     */
	public function get_total_count_sharethis ( $url ) {
		$total_share_url = "http://rest.sharethis.com/reach/getUrlInfo.php?"
							."url=".urlencode($url)
							."&provider=total"
							."&pub_key=$pubkey"
							."&access_key=$accesskey";
		$total_count = json_decode ( file_get_contents( $total_share_url ) )->total;
		$total_count = $total_count->inbound + $total_count->outbound;
		return $total_count;
	}

	/* Get the current count from the database.
	 *
	 * name: get_total_count
	 * @param $postids (optional) - array of post ids
	 * @return array ( postid => count )
	 *
	 */
	public function get_total_shares ( $postids = false, $is_single = false ) {
		if ( $postids && ! is_array ( $postids ) ) $postids = array ( $postids );
		elseif ( ! $postids ) $postids = get_published_ids();
		$total_count = array();
		foreach ( $postids as $postid ) {
			$total_count [ $postid ] = get_post_meta ( $postid, 'cowobo_share_count', true );
		}
		if(empty($total_count)) $total_count = 0;
		return ($is_single) ? $total_count[$postids[0]] : $total_count;
	}

    /**
     * Compute the full popularity count, based on shares and comments
     *
     * @param array $postids
     * @param boolean $is_single
     * @param boolean (optional) $only_comments
     * @return var or array with the total count
     */
	public function get_popularity_count ( $postids = false, $is_single = false, $only_comments = false  ) {
		if ( $postids && ! is_array ( $postids ) ) $postids = array ( $postids );
		elseif ( ! $postids ) $postids = get_published_ids();
		$total_shares = ( $only_comments ) ? array() : $this->get_total_shares ( $postids );
		$total_count = array();
		foreach ( $postids as $postid ) {
			$total_count [ $postid ] = wp_count_comments( $postid )->total_comments + $total_shares [ $postid ];
		}
		return ($is_single) ? $total_count[$postids[0]] : $total_count;
	}


	/**
     * List all feeds a user is subscribed to
     *
     * @param int $userid
     * @param str $type (optional) default is categories, alternative is posts
     * @return array posts or categories
     */
	public function list_user_feeds ( $userid, $type = 'categories' ) {
		$feed_query = get_user_meta($userid,'cowobo_feed',true);
		if (!is_array($feed_query)) return null;

		// Stored as type => id  ('p' or 'c')
		$categories = array();
		$posts = array();
		foreach ($feed_query as $query) {
			if (key($query) == 'p') $posts[] = current($query);
			elseif (key($query) == 'c') $cats[] = current($query);
		}
		if ($type == 'posts' ) return $posts;
		else return $cats;
	}

    /**
     * Restricts dashboard access to admins (user_can edit_dashboard)
     */
    public function restrict_dashboard() {
        $userid = get_current_user_id( );
        $redirect_url = $this->get_profile_url($userid);

        if ( ! current_user_can( 'edit_dashboard' ) ) {
            if ( headers_sent() ) {
                echo "<meta http-equiv='refresh' content='0;url=$redirect_url'>";
                echo "<script type='text/javascript'>document.location.href='$redirect_url'</script>";
                die;
            } else {
                wp_redirect( $redirect_url );
                exit();
            }
        }
    }

	/**
     * Converts the url to the right one
      *
      * @param str $url for the rss service with either %enc_feed% or %feed%
      * @param str $feed_url url for the feed to be added
      * @return str Url for the service with feed url
    */
   	public function get_feed_url($url, $feed_url) {
        $url = str_replace(
			array('%enc_feed%', '%feed%'),
			array(urlencode($feed_url), esc_url($feed_url),
		),$url);
		return $url;
    }


    /**
     * Prints RSS links for current feed
     *
     * @param str (optional) feedlink
     * @param str (optional) what to print before the link
     * @param str (optional) what to print after the link
     * @return boolean
     */
    public function print_rss_links( $feed_link = false, $before = '', $after = '' ) {
        $rss_services = array(
            'yahoo' => array(
                'name' => 'myYahoo',
                'url' => 'http://add.my.yahoo.com/rss?url=%enc_feed%',
                ),
			'facebook' => array(
                'name' => 'Facebook',
                'url' => 'http://www.facebook.com/cowobo',
                ),
            'google' => array(
                'name' => 'iGoogle',
                'url' => 'http://fusion.google.com/add?feedurl=%enc_feed%',
                ),
			'bloglines' => array(
                'name' => 'Bloglines',
                'url' => 'http://www.bloglines.com/sub/%feed%',
                ),
			'netvibes' => array(
                'name' => 'netvibes',
                'url' => 'http://www.netvibes.com/subscribe.php?url=%enc_feed%',
                ),
            'newsgator' => array(
                'name' => 'newsgator',
                'url' => 'http://www.newsgator.com/ngs/subscriber/subext.aspx?url=%enc_feed%',
                ),
			'rss_feed' => array(
                'name' => 'Other RSS Feed Readers',
                'url' => '%feed%',
             	),
        );

        if ( ! $feed_link ) $feed_link = $this->current_feed_url();
        $output = "";
        foreach ( $rss_services as $rss ) {
            $output .= "$before<a href ='" .$this->get_feed_url ( $rss['url'], $feed_link ) . "'>{$rss['name']}</a>$after";
        }

        echo $output;
        return true;
    }

    /**
     * Returns the RSS URL for the current feed in the feederbar
     *
     * @return str RSS URL for the current feed in the feederbar
     */
    public function current_feed_url() {
        $url = 'http';
        if ($_SERVER["HTTPS"] == "on") {$url .= "s";}
        $url .= "://";
        if ($_SERVER["SERVER_PORT"] != "80") {
            $url .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
        } else {
            $url .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
        }

        if ( substr ( $url, -1 ) != '/' ) $url .= '/';

        $url .= "feed";
        return $url;
    }

    /**
     * Makes rewrite rules for the personal feed
     *
     * @global obj $wp
     * @global obj $wp_rewrite
     */
    public function personal_feed_url() {
        global $wp,$wp_rewrite;
        $wp->add_query_var('userfeed');
        $wp_rewrite->add_rewrite_tag('%userfeed%','([^/]+)','userfeed=');
        $wp_rewrite->add_permastruct('personal-feed', PERSONALFEEDSLUG . '/%userfeed%');
    }

    /**
     * Makes sure we use our custom RSS template for the personal feed
     *
     * @param bool $for_comments
     */
    public function personal_feed_rss_template( $for_comments ) {
        $rss_template = get_template_directory() . '/feeds.php';
        if( get_query_var( 'userfeed' ) and file_exists( $rss_template ) )
            load_template( $rss_template );
        else
            do_feed_rss2( $for_comments ); // Call default function
    }

}
