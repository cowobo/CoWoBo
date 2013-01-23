<?php
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

class CoWoBo_Posts
{

    public function __construct() {
        $this->has_requests();
    }

    /**
     * Delete post and all links associated with it
     */
    public function delete_post() {


        $deleteid = cowobo()->query->id;
        cowobo()->relations->delete_relations($deleteid);
        if ( wp_delete_post($deleteid) ) {
            cowobo()->notifications[] = array (
                "error" => "An error occurred deleting your post."
            );
        } else {
            cowobo()->notifications[] = array (
                "success" => "Post succesfully deleted."
            );
        }
    }

    /**
     * Save post with new data
     * @todo This is one beast of a method - can we make some subroutines?
     */
    public function save_post(){
        global $post, $cowobo, $profile_id;
        $linkedid = 0;

        //store all data
        $postid = cowobo()->query->post_ID;

        $post_title  = ( cowobo()->query->post_title ) ? trim(strip_tags( cowobo()->query->post_title ) ) : null;
        $post_content = ( cowobo()->query->post_content ) ? trim( cowobo()->query->post_content ) : null;
        $tags  = ( cowobo()->query->tags ) ? trim(strip_tags( cowobo()->query->tags ) ) : null;
        $oldcity = get_post_meta( $postid, 'cityid', true );
        //$oldslug = $post->post_name;
        $involvement = cowobo()->query->involvement;
        $newslug = sanitize_title($post_title);

        $postcat = ( ! cowobo()->query->new )  ?$this->get_category($postid) : get_category ( get_cat_ID( cowobo()->query->new ) );
        $tagarray = array( $postcat->term_id );

        if ( ! $postid ) {
            $postid = $GLOBALS['newpostid'] = wp_insert_post( array('post_name' =>$newslug, 'post_category' => array ( get_cat_ID( cowobo()->query->new ) ), 'post_content' => " " ) );
            add_post_meta( $postid, 'author', $profile_id);
        }

        //check if post is created from within another post
        //if($postid != $post->ID) $linkedid = $post->ID;

        if ( empty ( $post_content ) ) {
            $postmsg['largetext'] = "Please add some content to your post!";
            $post_content = ' ';
        }

        //if the user is not involved don't link it to their profile
        if($involvement == 'none') {
            cowobo()->relations->delete_relations($postid, $profile_id); //existing posts
            $linkedid = false;
        } else {
            $linkedid = $profile_id;
        }

        //check if title filled correctly
        if ($post_title == '') $postmsg['title'] = 'You forgot to add one.';

        //check if the user entered all text in english
        if(!cowobo()->query->confirmenglish)  $postmsg['confirmenglish'] = 'Please check if all text is in English and check the checbox below';

        /**
         * update all the custom fields
         * @todo !! Iterate over POST more conciously
         */
        foreach ($_POST as $key => $value) {
            if( empty ( $value ) ) continue;

            delete_post_meta($postid, $key);
            if(strpos($key,'-checked')== true) {
                foreach ($value as $newval) {
                    add_post_meta($postid, $key, $newval);
                }
            }else {
                add_post_meta($postid, $key, $value);
            }
        }

        //if its a new location post geocode its location
        if( $postcat->slug == 'location' ) {
            if( $countryid = cowobo()->query->country ) {
                $tagarray = array( $countryid );
                if($latlng = cwb_geocode( $post_title.', '.$countryid ) ) {
                    $coordinates = $latlng['lat'].','.$latlng['lng'];
                    $citypost = get_posts('meta_key=coordinates&meta_value='.$coordinates);
                    //check if coordinates have already been added (avoids international spelling differences)
                    if($citypost && $citypost[0]->ID != $postid) {
                        $postmsg['title'] = 'The location you are trying to add already exists';
                    } else {
                        add_post_meta($postid, 'coordinates', $coordinates);
                    }
                    if( ! empty( $linkedid ) ) cowobo()->relations->create_relations($postid, array($linkedid));
                } else {
                    $postmsg['title'] = 'We could not find that city. Check your spelling or internet connection.';
                }
            } else {
                $postmsg['country'] = 'Please select a country';
            }
        }

        //if post contains a location create or link to that location post
        if( $city = cowobo()->query->city ) {
            if($city != $oldcity ) {
                if($countryid = $_POST['country']) {
                    $countrycat = get_category($countryid);
                    if($latlng = cwb_geocode($city.', '.$countrycat->name)):
                        $coordinates = $latlng['lat'].','.$latlng['lng'];
                        $citypost = get_posts('meta_key=coordinates&meta_value='.$coordinates);
                        //check if coordinates have already been added (avoids international spelling differences)
                        if($citypost):
                            $cityid = $citypost[0]->ID;
                        else:
                            // @todo use returned geocoding city name
                            $cityid = wp_insert_post(array('post_title'=>$city, 'post_category'=>array($countryid), 'post_status'=>'Publish'));
                            add_post_meta($cityid, 'coordinates', $coordinates);
                        endif;
                        cowobo()->relations->delete_relations($postid, $oldcity);
                        cowobo()->relations->create_relations($postid, array($cityid));
                        add_post_meta($postid, 'cityid', $cityid);  //save ID to check city next time
                        update_post_meta($postid, 'coordinates', $coordinates);
                    else:
                        $postmsg['location'] = 'We could not find that city. Check your spelling or internet connection.';
                    endif;
                } else {
                    $postmsg['location'] = 'Please select a country';
                }
            }
        }

        //get ids for each tag and create them if they dont already exist
        if ( ! empty ( $tags ) ) {
            foreach(explode(',', $tags) as $tag) {
                $tagid = term_exists(trim($tag), 'category', $postcat->term_id);
                if(!$tagid) $tagid = wp_insert_term(trim($tag), 'category', array('parent'=> $postcat->term_id));
                if ( is_a ( $tagid, 'WP_Error' ) ) continue;
                $tagarray[] = $tagid['term_id'];
            }
            $tagarray = array_map('intval', $tagarray);
            $tagarray = array_unique($tagarray);
        } elseif($postcat->slug != 'location') {
             //$postmsg['tags'] = 'You must add atleast one.';
        }

        //handle images
        /**
         * @todo check for malicious code in jpg?
         *
         */
        for ($x=0; $x<5; $x++):
            $imgid = $_POST['imgid'.$x];
            $file = $_FILES['file'.$x]['name'];
            $caption_id = "caption$x";
            $caption = cowobo()->query->$caption_id;
            $videocheck = explode("?v=", $caption );
            $imagecheck = $this->is_image_url ( $caption );

            //delete image if selected or being replaced by something else
            $deletex = "delete$x";
            if(cowobo()->query->$deletex || !empty($file) || !empty($videocheck[1]) ):
            //if($_POST['delete'.$x] or !empty($file) or !empty($videocheck[1])):
                wp_delete_attachment($imgid, true);
                delete_post_meta($postid, 'imgid'.$x);
            endif;
            //add new image
            if(!empty($file)):
                $imgid = $this->insert_attachment('file'.$x, $postid);
                update_post_meta($postid, 'imgid'.$x, $imgid);
            endif;
        endfor;

        //update draft post
        // @todo does this work well with new posts?
        //$postdata = array('ID' => $postid, 'post_title' => $post_title, 'post_content' => $post_content, 'post_status' => 'auto-draft', 'post_category' => $tagarray);
        //wp_update_post($postdata);

        // if there are no errors publish post, add links, and show thanks for saving message
        if(empty($postmsg)) {
            wp_update_post( array('ID' => $postid,'post_status' => 'publish', 'post_title' => $post_title, 'post_content' => $post_content, 'post_category' => $tagarray ) );

            if ( ! isset ( $GLOBALS['newpostid'] ) || empty ( $GLOBALS['newpostid'] ) ) {
                do_action( 'cowobo_post_updated', $postid, $post_title );
            }

            if ( cowobo()->query->link_to ) cowobo()->relations->create_relations($postid, cowobo()->query->link_to );
            if(!empty($linkedid)) cowobo()->relations->create_relations($postid, $linkedid );
            cowobo()->add_notice ( 'Thank you, your post was saved successfully. <a href="'.get_permalink($postid).'">Click here to view the result</a> or add another', "saved" );
            $GLOBALS['newpostid'] = null;
        } else {
            cowobo()->add_notice ( "There has been an error saving your post. Please check all the fields below.", "savepost" );
            foreach ( $postmsg as $key => $msg ) {
                cowobo()->add_notice ( $msg, $key );
            }
        }

        //return $postmsg;
    }

    /**
     * Get primal category of post
     */
    public function get_category( $postid = 0 ) {
        if ( ! $postid ) {
            $post = get_post();
            if ( ! $post ) return false;
            $postid = $post->ID;
        }
        if ( ! $postid ) return false;

        $cat = get_the_category($postid);
        $ancestors = get_ancestors($cat[0]->term_id,'category');
        if (empty($ancestors)) return $cat[0];
        return get_category(array_pop($ancestors));
    }

    //insert and resize uploaded attachments
    private function insert_attachment( $file_handler, $post_id, $setthumb='false' ) {
      if ($_FILES[$file_handler]['error'] !== UPLOAD_ERR_OK ) return false;

      require_once(ABSPATH . "wp-admin" . '/includes/image.php');
      require_once(ABSPATH . "wp-admin" . '/includes/file.php');
      require_once(ABSPATH . "wp-admin" . '/includes/media.php');

      $attach_id = media_handle_upload( $file_handler, $post_id );
      if ($setthumb) update_post_meta($post_id,'_thumbnail_id',$attach_id);
      return $attach_id;
    }

    /**
     * Store post views
     */
    public function update_views( $postID ) {
        $count_key = 'cowobo_post_views';
        $count = get_post_meta($postID, $count_key, true);
        if( empty ( $count ) )
            $count = 0;

        $count++;

        update_post_meta($postID, $count_key, $count);


    }

    /**
     * Retrieve post views
     */
    public function get_views($postID){
        $count_key = 'cowobo_post_views';
        $count = get_post_meta($postID, $count_key, true);
        if($count==''){
            delete_post_meta($postID, $count_key);
            add_post_meta($postID, $count_key, '0');
            $count = '0';
        }
        return $count;
    }

    /**
     * Gets the featured image, or the first image of a post
     *
     * If no attachments are found, returns first img in source (external link)
     *
     * @param str|WP_Post $post
     * @return string Image src
     */
    public function get_first_image ( $post ) {
        $image_size = 'medium';

        if ( is_numeric( $post ) )
            $post = get_post ( $post );

        if ( $post_thumbnail_id = get_post_thumbnail_id( $post->ID ) ) {
            $image = wp_get_attachment_image_src( $post_thumbnail_id, $image_size, false );
            return $image[0];
        }

        $images = get_children ( array (
            'post_parent'    => $post->ID,
            'numberposts'    => 1,
            'post_mime_type' =>'image'
        ) );

        if( ! empty ( $images ) ) {
            $images = current ( $images );
            $src = wp_get_attachment_image_src ( $images->ID, $size = $image_size );
            return $src[0];
        }

        if ( ! empty ( $post->post_content ) ) {
            $xpath = new DOMXPath( @DOMDocument::loadHTML( $post->post_content ) );
            $src = $xpath->evaluate( "string(//img/@src)" );
            return $src;
        }

        return '';
    }

    /**
     * Return gallery with captions
     */
    public function loadgallery( $postid ) {

        $slides = array();

        for ($x=0; $x<3; $x++):

            //store slide info
            $caption = get_post_meta($postid, 'caption'.$x, true);
            $imgid = get_post_meta($postid, 'imgid'.$x, true);
            $videocheck = explode("?v=", $caption);
            $image_check = $this->is_image_url( $caption );
            //check if the slide is video or image;
            if( is_array ( $videocheck ) && isset ( $videocheck[1] ) && $url = $videocheck[1]) {

                $slides[$x] = '<div class="slide" id="slide-'.($x+1).'"><object>';
                    $slides[$x] .= '<param name="movie" value="http://www.youtube.com/v/'.$url.'">';
                    $slides[$x] .= '<param NAME="wmode" VALUE="transparent">';
                    $slides[$x] .= '<param name="allowFullScreen" value="true"><param name="allowScriptAccess" value="always">';
                    $slides[$x] .= '<embed src="http://www.youtube.com/v/'.$url.'" type="application/x-shockwave-flash" allowfullscreen="true" allowScriptAccess="always" wmode="opaque" width="100%" height="100%"/>';
                $slides[$x] .= '</object></div>';

            } elseif ( $image_check ) {

                $slides[$x] = '<div class="slide" id="slide-'.($x+1).'">';
                    $slides[$x] .= '<img src="'.$caption.'" width="100%" alt=""/>';
                    //if($caption) $slides[$x] .= '<div class="captionback"></div><div class="caption"></div>';
                $slides[$x] .= '</div>';

            } elseif($imgsrc = wp_get_attachment_image_src($imgid, $size ='large')) {
                $slides[$x] = '<div class="slide" id="slide-'.($x+1).'">';
                    $slides[$x] .= '<img src="'.$imgsrc[0].'" width="100%" alt=""/>';
                    if($caption) $slides[$x] .= '<div class="captionback"></div><div class="caption">'.$caption.'</div>';
                $slides[$x] .= '</div>';
            }

           unset($imgid);

        endfor;


        //construct gallery
        $gallery = '';
        if( ! empty ( $slides ) ) {
            $slides = array_reverse($slides); //so they appear in the correct order
            $gallery = implode('', $slides);
        }

        return $gallery;
    }

	/**
     * Return list of thumbs for post
     *
     * @todo We are doubling up on a lot of work here. Can't we store the whole gallery in one object?
     */
    function load_thumbs($postid, $catslug = false){

        $thumbs[] = '<a href="?img=map" class="fifth"><img src="'.get_bloginfo('template_url').'/images/maps/mapthumb.jpg" width="100%" alt=""/></a>';

		//create thumbs for other images
        for ($x=0; $x<4; $x++) {
            //store slide info
            $caption = get_post_meta($postid, 'caption'.$x, true);
            $imgid = get_post_meta($postid, 'imgid'.$x, true);
            $videocheck = explode("?v=", $caption);
            $imagecheck = $this->is_image_url( $caption );
		    //check if the slide is video or image;
            if( is_array ( $videocheck ) && isset ( $videocheck[1] ) && $url = $videocheck[1]) {
               	$thumbs[] = '<a href="?img='.$x.'" class="fifth"><img src="http://img.youtube.com/vi/'.$url.'/1.jpg" alt=""/></a>';
            } elseif ( $imagecheck ) {

                $thumbs[] = '<a href="?img='.$x.'" class="fifth"><img src="'. $caption .'" width="100%" alt=""/></a>';

            } elseif($thumbsrc = wp_get_attachment_image_src($imgid, $size ='thumbnail')) {
                $thumbs[] = '<a href="?img='.$x.'" class="fifth"><img src="'.$thumbsrc[0].'" width="100%" alt=""/></a>';
            }
        }

        //construct thumb gallery
        $remaining = 5 - count($thumbs);
        for ($x=0; $x<$remaining; $x++) $thumbs[] = '<div class="fifth"><div class="thumb"></div></div>';
        $gallery = '<div class="gallery">'.implode('',$thumbs).'</div>';

		return $gallery;
    }

    /**
     * Echo thumbnail of post
     */
    function the_thumbnail($postid, $catslug = false){
        if($catslug == 'location') {
            echo '<img src="'.get_bloginfo('template_url').'/images/maps/mapthumb.jpg" width="100%" alt=""/>';
            return;
        }
        if ( $catslug == 'coder' ) {
            $fallback = '';
            if ( $attached = get_children( 'post_parent='.$postid.'&numberposts=1&post_mime_type=image' ) ) {
                $attached_src = wp_get_attachment_image_src( current ( $attached )->ID, 'thumbnail' );
                if ( is_array ( $attached_src ) )
                    $fallback = $attached_src[0];
            }
            echo get_avatar( cowobo()->users->get_users_by_profile_id( $postid, true )->ID, '140', $fallback );
            return;
        }

        foreach(get_children('post_parent='.$postid.'&numberposts=1&post_mime_type=image') as $image) {
            $imgsrc = wp_get_attachment_image_src( $image->ID );
            echo '<img src="'.$imgsrc[0].'" width="100%" alt=""/>';
        }

    }

    /**
     * Handle requests to edit posts
     *
     * @todo add BP notifications
     */
    public function edit_request(){
        global $post, $cowobo, $profile_id;
        $rqtype = cowobo()->query->requesttype;
        $rquser = cowobo()->query->requestuser;
        $rqpost = cowobo()->query->requestpost;
        $rqmsg = cowobo()->query->requestmsg;

        //if request is coming from a post use that data instead
        if(!$rquser) $rquser = $profile_id;
        if(!$rqpost) $rqpost = $post->ID;

        //if we are dealing with an existing request get its meta
        if($rqtype != 'add'):
            $requests = get_post_meta($rqpost, 'request', false);
            foreach($requests as $request):
                $rqdata = explode('|', $request);
                if($rqdata[0] == $rquser) $toedit = $request;
            endforeach;
        endif;

        //handle the request
        if($rqtype == 'add'):
            add_post_meta($rqpost, 'request', $rquser.'|'.$rqmsg);
            $notices = 'editrequest_sent';
        elseif($rqtype == 'accept'):
            delete_post_meta($rqpost, 'request', $toedit);
            add_post_meta($rqpost, 'author', $rquser);
            $notices = 'editrequest_accepted';
        elseif($rqtype == 'deny'):
            delete_post_meta($rqpost, 'request', $toedit);
            add_post_meta($rqpost, 'request', $requestuser.'|deny');
            $notices = 'editrequest_denied';
        elseif($rqtype == 'cancel'):
            delete_post_meta($rqpost, 'request', $toedit);
            $notices = 'editrequest_cancelled';
        endif;

        cowobo()->redirect( "message", $notices );
    }

    //Get list of all published IDs
    public function get_published_ids() {
        global $wpdb;
        $postobjs = $wpdb->get_results("SELECT ID FROM $wpdb->posts WHERE post_status = 'publish'");
        $postids = array();
        foreach ( $postobjs as $post ) {
            $postids[] = $post->ID;
        }
        return $postids;
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
     * Converts the url to the right one
      *
      * @param str $url for the rss service with either %enc_feed% or %feed%
      * @param str $feed_url url for the feed to be added
      * @return str Url for the service with feed url
    */
    private function get_feed_url($url, $feed_url) {
        $url = str_replace(
            array('%enc_feed%', '%feed%'),
            array(urlencode($feed_url), esc_url($feed_url),
        ),$url);
        return $url;
    }

    /**
     * Returns the RSS URL for the current feed in the feederbar
     *
     * @return str RSS URL for the current feed in the feederbar
     */
    private function current_feed_url() {
        $url = 'http';
        if ( isset ( $_SERVER["HTTPS"] ) && $_SERVER["HTTPS"] == "on") {$url .= "s";}
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

    private function has_requests() {
        global $profile_id, $cowobo;
        //check if the user has any pending author requests
        $requestposts = get_posts(array('meta_query'=>array(array('key'=>'author', 'value'=> $profile_id ), array('key'=>'request')), ));

        if( ! empty ( $requestposts ) ) {
            foreach($requestposts as $requestpost) {
                $requests = get_post_meta($requestpost->ID, 'request', false);
                $msg = '';
                foreach($requests as $request) {
                    $requestdata = explode('|', $request);
                    if($requestdata[1] != 'deny') {
                        $profile = get_post($requestdata[0]);
                        $msg .= '<form method="post" action="">';
                        $msg .= '<a href="'.get_permalink($profile->ID).'">'.$profile->post_title.'</a> sent you a request for ';
                        $msg .= '<a href="'.get_permalink($requestpost->ID).'">'.$requestpost->post_title.'</a>:<br/> '.$requestdata[1].'<br/>';
                        $msg .= '<input type="hidden" name="requestuser" value="'.$requestdata[0].'"/>';
                        $msg .= '<input type="hidden" name="requestpost" value="'.$requestpost->ID.'"/>';
                        $msg .= '<ul class="horlist">';
                        $msg .= '<li><input type="radio" name="requesttype" value="accept" selected="selected"/>Accept</li>';
                        $msg .= '<li><input type="radio" name="requesttype" value="deny"/>Deny</li>';
                        $msg .= wp_nonce_field( 'request', 'request', true, false );
                        $msg .= '<li><input type="submit" class="auto" value="Update"/></li>';
                        $msg .= '</ul>';
                        $msg .= '</form>';
                    }
                }
            }
            if ( ! empty ( $msg ) ) {
                cowobo()->add_notice( $msg, 'editrequest' );
            }
        }
    }

    public function get_post_authors( $postid = 0 ) {
        if ( ! $postid ) $postid = get_the_ID();
        if ( ! $postid ) return array();

        return get_post_meta( $postid, 'author', false );
    }

    public function is_user_post_author ( $postid = 0, $profile_id = 0 ) {
        if ( ! is_user_logged_in() ) return false;

        if ( ! $profile_id ) $profile_id = $GLOBALS['profile_id'];
        $authors = $this->get_post_authors( $postid );

        return in_array( $profile_id, $authors );
    }

    public function post_by_url ( $url = '' ) {
        if ( empty ( $url ) ) $url = cowobo()->query->url;
        //if ( empty ( $url ) ) return;

		$scheme = parse_url($url, PHP_URL_SCHEME);
		if (! $scheme || ! preg_match ( '/^https?$/', $scheme ) )
			$url = "http://{$url}";

        $warning = "There has been an error processing your link";
        if ( empty ( $url ) ) {
            cowobo()->add_notice( $warning,'error');
            return;
        }

		$images = array();
		$title = '';
		$text = '';

		$page = $this->get_page_contents($url);

        if ( empty ( $page ) ) {
            cowobo()->add_notice( $warning,'error');
            return;
        }

        if ( ! class_exists( 'simple_html_dom' ) )
            require_once( COWOBO_PLUGIN_LIB . 'external/simple_html_dom.php');

		$html = str_get_html($page);
		$str = $html->find('text');

		if ($str) {
			$image_els = $html->find('img');

			foreach ($image_els as $el) {
				if ($el->width > 100 && $el->height > 1) // Disregard spacers
					$images[] = $el->src;

                if ( count ( $images ) == 5 ) break;
			}
			$og_image = $html->find('meta[property=og:image]', 0);
			if ($og_image) array_unshift($images, $og_image->content);

			$title = $html->find('title', 0);
			$title = $title ? $title->plaintext: $url;

            $selectors = array (
                '.instapaper_body', // ReadWriteWeb (or anything with instapaper)
                '.entrytext', // WordPress.com (most WP based blogs)
                '.entry',
                '.post-body', // LifeHacker
                '.DetailedSummary', // Al Jazeera
                'h2 + div',
                'h1 + div', // CNN
                'p + div',
                'p + div',
                'p.introduction', // BBC
            );

            $text = '';
            foreach ( $selectors as $selector ) {
                $obj = $html->find( $selector, 0);
                if ( empty ( $obj ) ) continue;
                if ( $text = $html->find( $selector, 0)->innertext ) break;
            }

			$text = strip_tags ( $text, '<p><a><br><b><strong><i><em><u>' );
            if ( empty ( $text ) ) cowobo()->add_notice('We could not parse the content for this article, try pasting it in manually.', 'error');
		} else {
			$url = '';
		}

        $query = cowobo()->query;
        $query->post_title = trim ( $title );
        $query->post_content = trim ( $text );
        $query->website = $url;

        $x = 0;
        foreach ( $images as $image ) {
            $caption_id = "caption$x";
            $query->$caption_id = $image;
            $x++;
        }
    }

	/**
	 * Remote page retrieving routine.
	 *
	 * @param string Remote URL
	 * @return mixed Remote page as string, or (bool)false on failure
	 * @access private
	 */
	private function get_page_contents ($url) {
		$response = wp_remote_get($url);
		if (is_wp_error($response)) return false;
		return $response['body'];
	}

    public function is_image_url ( $url ) {
        $image_extensions = array ( 'jpg', 'jpeg', 'png', 'gif' );
        return in_array ( substr(strrchr ( $url,'.'), 1 ), $image_extensions );
    }

}