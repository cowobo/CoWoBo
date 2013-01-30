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

        $deleteid = cowobo()->query->post_ID;
        cowobo()->relations->delete_relations($deleteid);
        if ( wp_delete_post($deleteid) ) {
            wp_redirect ( add_query_arg ( array ( 'message' => 'post_deleted' ), get_bloginfo ( 'url' ) ) );
            exit;
        } else {
            cowobo()->notifications[] = array (
                "error" => "An error occurred deleting your post."
            );
            /*cowobo()->notifications[] = array (
                "success" => "Post succesfully deleted."
            );*/
        }
    }

    /**
     * Save post with new data
     * @todo This is one beast of a method - can we make some subroutines?
     */
    public function save_post(){

        global $post, $cowobo, $profile_id;
        $linkedid = 0; $tagarray = array();

        //store all data
        $postid = cowobo()->query->post_ID;

        $post_title  = ( cowobo()->query->post_title ) ? trim(strip_tags( cowobo()->query->post_title ) ) : null;
        $post_content = ( cowobo()->query->post_content ) ? trim( cowobo()->query->post_content ) : null;
        $tags  = ( cowobo()->query->tags ) ? trim(strip_tags( cowobo()->query->tags ) ) : null;
        $oldcityid = get_post_meta($postid, 'cwb_city', true);
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


		//delete old post data in case they were cleared in the form
		foreach (get_post_custom_keys($postid) as $key ) {
		    $valuet = trim($key);
		    if ( '_' == $valuet{0} ) continue; // don't touch wordpress fields
		    delete_post_meta($postid, $key);
		}

		//now store the new data
        foreach ($_POST as $key => $value) {
            if( empty ( $value ) ) continue;
            if(strpos($key,'-checked')== true) {
                foreach ($value as $newval) {
                    add_post_meta($postid, $key, $newval);
                }
            }else {
                add_post_meta($postid, $key, $value);
            }
        }

        //if its a new location post geocode its location
        if( $postcat->slug == 'location') {
            if( $country = cowobo()->query->country ) {
				if($location = cwb_geocode( $post_title.', '.$country ) ) {
					//check if location has already been added
					$coordinates = $location['lat'].','.$location['lng'];
					$citypost = get_posts('meta_key=coordinates&meta_value='.$coordinates);
					if( $citypost && $citypost[0]->ID != $postid ) {
                        $postmsg['title'] = 'The location you are trying to add already exists';
                    } else {
						//use title and country returned from geocode to avoid spelling duplicates
                        $post_title = $location['cwb_city'];
						update_post_meta($postid, 'cwb_country', $location['country']);
						add_post_meta($postid, 'coordinates', $coordinates);
						if($countryid = get_cat_ID( $location['country'] ))
							$tagarray[] = $countryid;
						else {
							$tagid = wp_insert_term( $location['country'] , 'category', array('parent'=> get_cat_ID('Locations')));
							$tagarray[] = $tagid['term_id'];
				            $tagarray = array_map('intval', $tagarray);
						}
                    }
                    if( ! empty( $linkedid ) ) cowobo()->relations->create_relations($postid, array($linkedid));
                } else {
                    $postmsg['title'] = 'We could not find that city. Check your spelling or internet connection.';
                }
            } else {
                $postmsg['country'] = 'Please enter a country';
            }
        }

        //if post contains a location create or link to that location post
        if( $newlocation = cowobo()->query->location) {
			if( $location = cwb_geocode( $newlocation ) ):
				$coordinates = $location['lat'].','.$location['lng'];
				$citypost = get_page_by_title( $location['city'], 'OBJECT', 'post' );
				//check if location has already been added
                if( $citypost ):
                	$cityid = $citypost->ID;
					$countrycat = get_the_category($cityid);
					$countryid  = $countrycat[0]->term_id;
                else:
					if( $countrycat = get_cat_ID( $location['country'] ) )
						$countryid = $countrycat;
					else {
						$tagid = wp_insert_term( $location['country'] , 'category', array('parent'=> get_cat_ID('Locations')));
						$countryid = $tagid['term_id'];
					}
					$cityid = wp_insert_post(array('post_title'=>$location['city'], 'post_category'=>array($countryid), 'post_status'=>'Publish'));
					update_post_meta( $cityid, 'coordinates', $coordinates);
				endif;
				//check if streetview is available
				if(cowobo()->query->cwb_includestreet && !cwb_streetview($postid) ) {
					$postmsg['location'] = 'The address you entered does not have streetview, try another?';
				}
				update_post_meta( $postid, 'cwb_country', $countryid );
				update_post_meta( $postid, 'cwb_city', $cityid );
				update_post_meta( $postid, 'coordinates', $coordinates);
                cowobo()->relations->delete_relations($postid, $oldcityid);
                cowobo()->relations->create_relations($postid, array($cityid));
			 else:
             	$postmsg['location'] = 'We could not find that city. Check your spelling or internet connection.';
             endif;
		} else {
			delete_post_meta( $postid, 'cwb_country');
			delete_post_meta( $postid, 'cwb_city' );
			delete_post_meta( $postid, 'coordinates', $coordinates);
			cowobo()->relations->delete_relations($postid, $oldcityid);
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
         */
        for ($x=0; $x<3; $x++):
            $imgid = "imgid$x";
            $imgid = cowobo()->query->imgid;
            $file = ( isset ( $_FILES['file'.$x] ) ) ? $_FILES['file'.$x]['name'] : '';
            $url_id = "cwb_url$x";
            $imgurl = cowobo()->query->$url_id;
            $videocheck = explode("?v=", $imgurl );
            $imagecheck = $this->is_image_url ( $imgurl );

            //delete image if selected or being replaced by something else
            $deletex = "delete$x";
            if(cowobo()->query->$deletex || !empty($file) || !empty($videocheck[1]) || !empty($imagecheck) ):
                wp_delete_attachment($imgid, true);
                delete_post_meta($postid, 'imgid'.$x);
            endif;


            //add new image
            if(!empty($file)) {
                $imgid = $this->insert_attachment('file'.$x, $postid);
                update_post_meta($postid, 'imgid'.$x, $imgid);
				delete_post_meta($postid, 'cwb_url'.$x);
            } elseif ( $imagecheck ) {
                update_post_meta($postid, 'cwb_url'.$x, $imgid);
            }
        endfor;

        //update draft post
        // @todo does this work well with new posts?
        //$postdata = array('ID' => $postid, 'post_title' => $post_title, 'post_content' => $post_content, 'post_status' => 'auto-draft', 'post_category' => $tagarray);
        //wp_update_post($postdata);

        // if there are no errors publish post, add links, and show thanks for saving message
        if(empty($postmsg)) {
            $post_content = preg_replace( '@(?<![.*">])\b(?:(?:https?|ftp|file)://|[a-z]\.)[-A-Z0-9+&#/%=~_|$?!:,.]*[A-Z0-9+&#/%=~_|$]@i', '<a href="\0" target="_blank">\0</a>', $post_content );
            wp_update_post( array('ID' => $postid,'post_status' => 'publish', 'post_title' => $post_title, 'post_content' => $post_content, 'post_category' => $tagarray ) );

            if ( ! isset ( $GLOBALS['newpostid'] ) || empty ( $GLOBALS['newpostid'] ) ) {
                do_action( 'cowobo_post_updated', $postid, $post_title );
            }

            if ( cowobo()->query->link_to ) cowobo()->relations->create_relations($postid, cowobo()->query->link_to );
            if(!empty($linkedid)) cowobo()->relations->create_relations($postid, $linkedid );

            wp_redirect ( add_query_arg ( array ( "action" => "editpost", "message" => "post_saved" ), get_permalink ( $postid ) ) );
            //cowobo()->add_notice ( 'Thank you, your post was saved successfully. <a href="'.get_permalink($postid).'">Click here to view the result</a> or add another', "saved" );
            //$GLOBALS['newpostid'] = null;
        } else {
            cowobo()->add_notice ( "There has been an error saving your post. Please check all the fields below.", "savepost" );
            foreach ( $postmsg as $key => $msg ) {
                cowobo()->add_notice ( $msg, $key );
            }
        }

        //return $postmsg;
    }

	/**
     * Save captions with new data
     */

    public function save_captions(){
		$postid = cowobo()->query->post_ID;
		$fields = array('0','1','2','3','map','street');
		foreach ($fields as $field) {
			$fieldname = 'caption-'.$field;
	        update_post_meta($postid, $fieldname, cowobo()->query->$fieldname);
		}
	}


    /**
     * Get primal category of post
     */
    public function get_category( $postid = 0 ) {
        if ( ! $postid )
            $postid = get_the_ID();

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
     * Return gallery with captions and thumbs
     */
    public function loadgallery( $postid = false ) {

        $slides = array();
		$thumbs = array();
		$imgfolder = get_bloginfo('template_url').'/images';

		if($postid) {

			for ($x=0; $x<3; $x++):

				//store slide info
	            $url = get_post_meta($postid, 'cwb_url'.$x, true);
	            $imgid = get_post_meta($postid, 'imgid'.$x, true);
	            $videocheck = explode( "?v=", $url );
	            $image_check = $this->is_image_url( $url );
				$caption = get_post_meta($postid, 'caption-'.$x, true);

				//check if the slide is uploaded image, youtuve video, or image url;
	            if($imgsrc = wp_get_attachment_image_src($imgid, $size ='large')) {
	                $zoom2src = wp_get_attachment_image_src($imgid, $size ='extra-large');
					$thumbsrc = wp_get_attachment_image_src($imgid, $size ='thumbnail');
					$slides[$x] = '<div class="slide hide" id="slide-'.$x.'">';
	                    $slides[$x] .= '<img class="slideimg" src="'.$imgsrc[0].'" width="100%" alt=""/>';
						if( $zoom2src ) $slides[$x] .= '<input type="hidden" class="zoomsrc2" value="'.$zoom2src[0].'"/>';
	                $slides[$x] .= '</div>';
					$thumbs[] = '<a class="'.$x.'" href="?img='.$x.'"><img src="'.$thumbsrc[0].'" height="100%" alt=""/></a>';
				} elseif( is_array ( $videocheck ) && isset ( $videocheck[1] ) && $videourl = $videocheck[1]) {
	                $slides[$x] = '<div class="slide hide" id="slide-'.$x.'"><object>';
	                    $slides[$x] .= '<param name="movie" value="http://www.youtube.com/v/'.$url.'">';
	                    $slides[$x] .= '<param NAME="wmode" VALUE="transparent">';
	                    $slides[$x] .= '<param name="allowFullScreen" value="true"><param name="allowScriptAccess" value="always">';
	                    $slides[$x] .= '<embed src="http://www.youtube.com/v/'.$videourl.'" type="application/x-shockwave-flash" allowfullscreen="true" allowScriptAccess="always" wmode="opaque" width="100%" height="100%"/>';
	                $slides[$x] .= '</object></div>';
					$thumbs[] = '<a class="'.$x.'" href="?img='.$x.'"><img src="http://img.youtube.com/vi/'.$videourl.'/1.jpg" height="100%" alt=""/></a>';
	            } elseif ( $image_check ) {
	                $slides[$x] = '<div class="slide hide" id="slide-'.$x.'">';
	                    $slides[$x] .= '<img class="slideimg" src="'.$url.'" width="100%" alt=""/>';
	                $slides[$x] .= '</div>';
					$thumbs[] = '<a class="'.$x.'" href="?img='.$x.'"><img src="'. $caption .'" height="100%" alt=""/></a>';

	            }

				//store captions
				if($this->is_user_post_author() && $postid) {
					$captions[] = '<input type="text" class="caption hide" id="caption-'.$x.'" name="caption-'.$x.'" value="'.htmlentities($caption).'" placeholder="Click here to add a caption"/>';
				} else {
					if( empty($caption) ) $caption = '<a class="resize" href="#">Click here to resize the media viewer</a>';
					$captions[] = '<span class="caption hide" id="caption-'.$x.'">'.$caption.'</span>';
				}

	           unset($imgid); unset($zoom2src);

	        endfor;

		}

		//include streetview if available
		if( get_post_meta($postid, 'cwb_includestreet', true) && $slides[] = cwb_streetview($postid) ) {
			$coordinates = get_post_meta($postid, 'coordinates', true);
			$thumbs[] = '<a class="street" href="?img=street"><img src="http://maps.googleapis.com/maps/api/streetview?size=50x50&location='.$coordinates.'&sensor=false" /></a>';
			$caption = get_post_meta($postid, 'caption-street', true);
			if($this->is_user_post_author() && $postid) {
				$default = 'Click here to add a caption';
				$captions[] = '<input type="text" class="caption hide" id="caption-street" name="caption-street" value="'.htmlentities($caption).'" placeholder="'.$default.'"/>';
			} else {
				$default = 'Use the navigation controls to zoom into the map';
				$captions[] = '<span class="caption hide" id="caption-street">'.$default.'</span>';
			}
		}

		//include map if available
		if( get_post_meta($postid, 'cwb_includemap', true) or empty ( $thumbs ) ) {
			$thumbs[] = '<a class="map" href="?img=map"><img src="'.$imgfolder.'/maps/day_thumb.jpg" height="100%" /></a>';
			$slides[] = cwb_loadmap();
			$caption = get_post_meta($postid, 'caption-map', true);
			if($this->is_user_post_author() && $postid) {
				$default = 'Click here to add a caption';
				$captions[] = '<input type="text" class="caption hide" id="caption-map" name="caption-map" value="'.htmlentities($caption).'" placeholder="'.$default.'"/>';
			} else {
				$default = 'Use the navigation controls to zoom into the map';
				$captions[] = '<span class="caption hide" id="caption-map">'.$default.'</span>';
			}
		}

		//show map first on homepage
		if( ! is_home() ) {
			$thumbs = array_reverse( $thumbs );
		} else {
			$slides = array_reverse($slides);
			$captions = array_reverse( $captions );
		}

		//show first caption and slide
		$captions[0] = str_replace('caption hide' , 'caption' , $captions[0]);
		$slides[0] = str_replace('slide hide' , 'slide' , $slides[0]);

		//include caption form if user is author of post
		if($this->is_user_post_author() && $postid) {
			$captionsdiv = '<form method="post" action="" class="capform">'.implode('', $captions);
			$captionsdiv .= '<input type="hidden" name="post_ID" value="'.$postid.'" />';
			$captionsdiv .= '<input type="submit" class="button" value="Save Captions" />';
			$captionsdiv .= wp_nonce_field( 'captions', 'captions' );
			$captionsdiv .= '</form>';
		} else {
			$captionsdiv = implode('', $captions);
		}

		//echo html
		echo '<div class="imageholder">';
			echo implode('', $slides);
			echo '<img src="'.$imgfolder.'/ratio-map.png" width="100%" alt=""/>';
		echo '</div>';

		echo '<div class="titlebar">';
			echo '<div class="shade"></div>';
			echo '<img class="resizeicon" src="'.$imgfolder.'/resizeicon.png" title="Toggle viewer height" alt=""/>';
			echo '<div class="smallthumbs">'.implode('', $thumbs).'</div>';
			$margin = count($thumbs)*45+20;
			echo '<div class="captions" style="margin-right:'.$margin.'px">'.$captionsdiv.'</div>';
		echo '</div>';
    }


    /**
     * Echo thumbnail of post
     */
    function the_thumbnail($postid, $catslug = false){
        if($catslug == 'location') {
			$coordinates = get_post_meta($postid, 'coordinates', true);
            $position = get_map_position(149, 100, $coordinates);
			echo '<img style="'.$position.'" src="'.get_bloginfo('template_url').'/images/maps/day_thumb.jpg"/>';
            return;
        }

        if ( $catslug == 'coder' ) {
            $fallback = '';
            if ( $attached = get_children( 'post_parent='.$postid.'&numberposts=1&post_mime_type=image' ) ) {
                $attached_src = wp_get_attachment_image_src( current ( $attached )->ID );
                if ( is_array ( $attached_src ) )
                    $fallback = $attached_src[0];
            }
            if ( $user = cowobo()->users->get_users_by_profile_id( $postid, true ) ) {
                echo get_avatar( $user->ID, '149', $fallback );
                return;
            }
        }

		for ($x=0; $x<3; $x++):
			$url = get_post_meta($postid, 'cwb_url'.$x, true);
	        $imgid = get_post_meta($postid, 'imgid'.$x, true);
	        $videocheck = explode( "?v=", $url );
	        $image_check = $this->is_image_url( $url );
			if($imgsrc = wp_get_attachment_image_src($imgid, $size ='thumbnail')) {
				echo '<img src="'.$imgsrc[0].'" width="100%" alt=""/></a>'; return;
			} elseif( is_array ( $videocheck ) && isset ( $videocheck[1] ) && $videourl = $videocheck[1]) {
				echo '<img src="http://img.youtube.com/vi/'.$videourl.'/1.jpg" width="100%" alt=""/></a>'; return;
	        } elseif ( $image_check ) {
				echo '<img src="'.$url.'" width="100%" alt=""/></a>'; return;
			}
		endfor;

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
        $toedit = '';
        if($rqtype != 'add'):
            $requests = get_post_meta($rqpost, 'request', false);
            foreach($requests as $request):
                $rqdata = explode('|', $request);
                if ($rqdata[0] == $rquser) $toedit =  $request;
            endforeach;
        endif;

        //handle the request
        if($rqtype == 'add'):
            add_post_meta($rqpost, 'request', $rquser.'|'.$rqmsg);
            $notices = 'editrequest_sent';
        elseif($rqtype == 'accept'):
            delete_post_meta($rqpost, 'request', $toedit);
            add_post_meta($rqpost, 'author', $rquser);
            do_action ( 'editrequest_accepted', $rquser, $rqpost );
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
        global $profile_id;
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

		if(current_user_can('edit_others_posts') || in_array( $profile_id, $authors ))

        return true;
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
				if ($el->width > 100 && $el->height > 100) // Disregard spacers
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
                '#mw-content-text p', // Wikipedia
                'h2 + div',
                'h1 + div', // CNN
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
            $caption_id = "cwb_url$x";
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
