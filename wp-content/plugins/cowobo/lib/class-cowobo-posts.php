<?php
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

class CoWoBo_Posts
{
    /**
     * Create a new post
     */
    public function create_post(){
        global $cowobo, $profile_id;

        $newcat = $cowobo->query->cat;
        $catid = get_cat_ID( $newcat );

        //insert the post
        $current_user = wp_get_current_user();
        $postid = wp_insert_post( array(
            'post_status' => 'auto-draft',
            'post_title' => ' ',
            'post_category' => array( $catid ),
            'post_author' => $current_user->ID,
        ));

        //add the user to the authors list (used for multiple author checks)
        add_post_meta( $postid, 'author', $profile_id );

        return $postid;
    }

    /**
     * Delete post and all links associated with it
     */
    public function delete_post() {
        global $related, $cowobo;

        $deleteid = $cowobo->query->id;
        $related->delete_relations($deleteid);
        if ( wp_delete_post($deleteid) ) {
            $cowobo->notifications[] = array (
                "error" => "An error occurred deleting your post."
            );
        } else {
            $cowobo->notifications[] = array (
                "success" => "Post succesfully deleted."
            );
        }
    }

    /**
     * Save post with new data
     * @todo This is one beast of a method - can we make some subroutines?
     */
    public function save_post(){
        global $related, $post, $social, $cowobo, $profile_id;

        //store all data
        $postid = $cowobo->query->post_ID;

        $post_title  = ( $cowobo->query->post_title ) ? trim(strip_tags( $cowobo->query->post_title ) ) : null;
        $post_content = ( $cowobo->query->post_content ) ? trim( $cowobo->query->post_content ) : null;
        $tags  = ( $cowobo->query->tags ) ? trim(strip_tags( $cowobo->query->tags ) ) : null;
        $oldcity = get_post_meta( $postid, 'cityid', true );
        //$oldslug = $post->post_name;
        $involvement = $cowobo->query->involvement;
        $newslug = sanitize_title($post_title);
        $postcat = $this->get_category($postid);

        //check if post is created from within another post
        if($postid != $post->ID) $linkedid = $post->ID;

        //if the user is not involved don't link it to their profile
        if($involvement == 'none') {
            $related->delete_relations($postid, $profile_id); //existing posts
            $linkedid = false;
        } else {
            $linkedid = $profile_id;
        }

        //check if title filled correctly
        if ($post_title == '') $postmsg['post_title'] = 'You forgot to add one.';

        //check if the user entered all text in english
        if(!$cowobo->query->confirmenglish)  $postmsg['confirmenglish'] = 'Please check if all text is in English and check the checbox below';

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
            if( $countryid = $cowobo->query->country ) {
                $tagarray = array( $countryid );
                if($latlng = cwb_geocode( $post_title.', '.$country ) ) {
                    $coordinates = $latlng['lat'].','.$latlng['lng'];
                    $citypost = get_posts('meta_key=coordinates&meta_value='.$coordinates);
                    //check if coordinates have already been added (avoids international spelling differences)
                    if($citypost && $citypost[0]->ID != $postid) {
                        $postmsg['post_title'] = 'The location you are trying to add already exists';
                    } else {
                        add_post_meta($postid, 'coordinates', $coordinates);
                    }
                    if( ! empty( $linkedid ) ) $related->create_relations($postid, array($linkedid));
                } else {
                    $postmsg['post_title'] = 'We could not find that city. Check your spelling or internet connection.';
                }
            } else {
                $postmsg['country'] = 'Please select a country';
            }
        }

        //if post contains a location create or link to that location post
        if( $city = $cowobo->query->city ) {
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
                        $related->delete_relations($postid, $oldcity);
                        $related->create_relations($postid, array($cityid));
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
                $tagarray[] = $tagid['term_id'];
            }
            $tagarray = array_map('intval', $tagarray);
            $tagarray = array_unique($tagarray);
        } elseif($postcat->slug != 'location') {
             $postmsg['tags'] = 'You must add atleast one.';
        }

        //handle images
        /**
         * @todo check for malicious code in jpg?
         */
        for ($x=0; $x<5; $x++):
            $imgid = $_POST['imgid'.$x];
            $file = $_FILES['file'.$x]['name'];
            $videocheck = explode("?v=", $_POST['caption'.$x]);
            //delete image if selected or being replaced by something else
            if($_POST['delete'.$x] or !empty($file) or !empty($videocheck[1])):
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
        $postdata = array('ID' => $postid, 'post_title' => $post_title, 'post_content' => $post_content, 'post_status' => 'draft', 'post_category' => $tagarray);
        wp_update_post($postdata);

        // if there are no errors publish post, add links, and show thanks for saving message
        if(empty($postmsg)):
            wp_update_post( array('ID' => $postid,'post_status' => 'publish', 'post_name' =>$newslug));
            if(!empty($linkedid)) $related->create_relations($postid, array($linkedid));
            $postmsg = 'saved';
        endif;

        return $postmsg;
    }

    /**
     * Get primal category of post
     */
    public function get_category($postid) {
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
        if( empty ( $count ) ) {
            $count = 0;
            delete_post_meta($postID, $count_key);
            add_post_meta($postID, $count_key, '0');
        } else {
            $count++;
            update_post_meta($postID, $count_key, $count);
        }


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


}



//Link post to another related post
function cwb_link_post(){
	global $related; global $post;
	$related->create_relations($post->ID, array($_POST['linkto']));
}