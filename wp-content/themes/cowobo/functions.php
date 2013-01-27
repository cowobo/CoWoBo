<?php
if ( ! defined ( 'COWOBO_DEFAULT_AVATAR_URL' ) || ! COWOBO_DEFAULT_AVATAR_URL )
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

//Register sidebars
if (function_exists('dynamic_sidebar')) {
	register_sidebar(array(
		'before_widget'=>'<div class="widget">',
		'after_widget'=>'</div>',
		'id'=>'sidebar',
		'name'=>'Main Sidebar',
	));
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


add_theme_support( 'post-thumbnails' );
add_image_size( 'extra-large', 2000, 9999 ); //300 pixels wide (and unlimited height)

