<?php
if ( ! defined ( COWOBO_DEFAULT_AVATAR_URL ) || ! COWOBO_DEFAULT_AVATAR_URL )
    define ( 'COWOBO_DEFAULT_AVATAR_URL', get_template_directory_uri() . '/images/angel3.png' );


//Add console log for easier debugging
function console_log ( $content ) {
	echo "<script>console.log('$content')</script>";
}

// Utility functions

/**
 * Return time passed since publish date
 */
function cwb_time_passed($timestamp){
    $timestamp = (int) $timestamp;
    $current_time = time();
    $diff = $current_time - $timestamp;
    $intervals = array ('day' => 86400, 'hour' => 3600, 'minute'=> 60);
    //now we just find the difference
    if ($diff == 0) return 'just now &nbsp;';
    if ($diff < $intervals['hour']){
        $diff = floor($diff/$intervals['minute']);
        return $diff == 1 ? $diff . ' min ago' : $diff . ' mins ago';
    }
    if ($diff >= $intervals['hour'] && $diff < $intervals['day']){
        $diff = floor($diff/$intervals['hour']);
        return $diff == 1 ? $diff . ' hour ago' : $diff . ' hours ago';
    }
    if ($diff >= $intervals['day']){
        $diff = floor($diff/$intervals['day']);
        return $diff == 1 ? $diff . ' day ago' : $diff . ' days ago';
    }
}

//Remove admin bar
add_filter('show_admin_bar', 'cowobo_show_admin_bar');
function cowobo_show_admin_bar(){
    return false;
}

//check if a website is down
function check_website_status($url){
	$resURL = curl_init();
	curl_setopt($resURL, CURLOPT_URL, $url);
	curl_setopt($resURL, CURLOPT_BINARYTRANSFER, 1);
	curl_setopt($resURL, CURLOPT_HEADERFUNCTION, 'curlHeaderCallback');
	curl_setopt($resURL, CURLOPT_FAILONERROR, 1);
	curl_exec ($resURL);
	$intReturnCode = curl_getinfo($resURL, CURLINFO_HTTP_CODE);
	curl_close ($resURL);
	if ($intReturnCode != 200 && $intReturnCode != 302 && $intReturnCode != 304) {
	    return false;
	}
	else return true;
}


add_filter( 'image_make_intermediate_size', 'rename_intermediates_wpse_82193' );

function rename_intermediates_wpse_82193( $image ) {
    $info = pathinfo($image);
    $dir = $info['dirname'] . '/';
    $ext = '.' . $info['extension'];
    $name = wp_basename( $image, "$ext" );

    $name_prefix = substr( $name, 0, strrpos( $name, '-' ) );
    $size_extension = substr( $name, strrpos( $name, '-' ) + 1 );
    $new_name = $dir . $size_extension . '-' . $name_prefix . $ext;

    $did_it = rename( $image, $new_name );

    if( $did_it )
        return $new_name;

    return $image;
}


add_filter('wp_handle_upload_prefilter', 'wpsx_5505_modify_uploaded_file_names', 1, 1);

function wpsx_5505_modify_uploaded_file_names($arr) {
    // Get the parent post ID, if there is one
    if( isset($_REQUEST['post_id']) ) {
        $post_id = $_REQUEST['post_id'];
    } else {
        $post_id = false;
    }
    // Only do this if we got the post ID--otherwise they're probably in
    //  the media section rather than uploading an image from a post.
    if($post_id && is_numeric($post_id)) {
        // Get the post slug
        $post_obj = get_post($post_id); 
        $post_slug = $post_obj->post_name;

        // If we found a slug
        if($post_slug) {
            $random_number = rand(10000,99999);
            $arr['name'] = $post_slug . '-' . $random_number . '.jpg';

        }

    }

    return $arr;
}

