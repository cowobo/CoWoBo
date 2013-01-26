<?php
global $profile_id, $langnames, $lang;

get_header();

//VARIABLES
if ( ! isset ( $postid ) ) $postid = 0;
$action = cowobo()->query->action;
$feedtitle = $langnames[$lang][2];
$viewerheight = '70%';

if(is_home()) {
	$feedtitle = $langnames[$lang][1];
	$subtitle = $langnames[$lang][2];
	$intropost = get_page_by_title( 'Take the tour' );
       if ( $intropost ) $postid = $intropost->ID;
} elseif(is_single()) {
	$userid = $profile_id;
	$location = get_post_meta($post->ID, 'location', true);
	$profiles = get_post_meta($post->ID, 'author', false);
	$canedit = current_user_can('edit_others_posts') || cowobo()->debug;
	if( ! $postid ) $postid = $post->ID;
	if( cowobo()->query->post_ID ) $postid = $_POST['post_ID'];
	if( $post->ID == $userid || $canedit ) $author = true;
	else $author = false;
	if($profiles && $userid && in_array($userid, $profiles))$author = true;
	cowobo()->posts->update_views($post->ID);
	$coordinates = get_post_meta($post->ID, 'coordinates', true);
}

//include hidden description for google index
echo '<div class="description hide">'.get_bloginfo('description').'</div>';

//include image viewer
echo '<div class="imageviewer grabcursor">';

	//set the height on load of the image viewer
	echo '<img src="'.get_bloginfo('template_url').'/images/proportion.png" width="'.$viewerheight.'" alt=""/>';

		//include header links
	echo '<div class="headerlinks">';
		echo '<a class="sitetitle" href="'.get_bloginfo('url').'"><b>Coders</b> Without <b>Borders</b></a>';
	echo '</div>';

	//include planet/imageviewer controls
	echo '<a class="pan panleft" href="?pan=left"></a>';
	echo '<a class="pan panright" href="?pan=right"></a>';
	echo '<a class="pan panup" href="?pan=up"></a>';
	echo '<a class="pan pandown" href="?pan=down"></a>';
	echo '<a class="zoom zoomin" href="?zoom=in"></a>';
	echo '<a class="zoom zoomout" href="?zoom=out"></a>';


	echo '<div class="imageholder">';
		echo '<img class="shadow" src="'.get_bloginfo('template_url').'/images/shadow.png" alt=""/>';
		echo '<img src="'.get_bloginfo('template_url').'/images/proportion.png" width="100%" alt=""/>';
		$captions = cowobo()->posts->loadgallery($postid);
		cwb_loadmap();
	echo '</div>';

	echo '<div class="titlebar">';
		echo '<div class="shade"></div>';
		echo '<div class="titlepadding">';
			echo '<span class="feedtitle">'.cowobo()->feed->feed_title().'</span>';
			echo '<span class="captions">Site will be ready on February 4th, <a href="?action=contact">contact us for more info</a></span>';
			echo '<div class="right smallthumbs">';
				echo cowobo()->posts->load_thumbs($postid);
				echo '<img class="resizeicon right" src="'.get_bloginfo('template_url').'/images/resizeicon.png" title="Toggle viewer height" alt=""/>';
			echo '</div>';
		echo '</div>';
	echo '</div>';
echo '</div>';


//include dragbar
echo '<div class="dragbar"></div>';

//include page
echo '<div class="page">';

	//include page shadow
	echo '<img class="shadow" src="'.get_bloginfo('template_url').'/images/shadow.png" alt=""/>';

	//include feed
	echo '<div class="feed">';

		//include searchform
		include(TEMPLATEPATH.'/templates/search.php');

		//include any notifications to user
		include( TEMPLATEPATH . '/templates/notify.php');

		//include the appropriate feed template
		if(is_home() && ! cowobo()->query->s && ! cowobo()->query->new && ! cowobo()->query->action ):
			include(TEMPLATEPATH.'/templates/home.php');
		endif;

		if($action && file_exists(TEMPLATEPATH.'/templates/'.$action.'.php')):
			if($action == 'edit' && !is_user_logged_in()): $redirect = 'edit';
				include(TEMPLATEPATH.'/templates/login.php');
			else:
				include(TEMPLATEPATH.'/templates/'.$action.'.php');
			endif;
		elseif( cowobo()->query->new ): $author = true;
			if(!is_user_logged_in()): $redirect = 'new'; $redirect = 'new';
				include(TEMPLATEPATH.'/templates/login.php');
			else:
				include(TEMPLATEPATH.'/templates/editpost.php');
			endif;
		elseif(is_404()):
			include(TEMPLATEPATH.'/templates/404.php');
		elseif(is_single()):
			include(TEMPLATEPATH.'/templates/posts.php');
		elseif(is_category() || cowobo()->query->s):
            if ( empty ( cowobo()->feed->sort ) ) :
                include(TEMPLATEPATH.'/templates/categories.php');
            else :
                include(TEMPLATEPATH.'/templates/categories_sorted.php');
            endif;
		endif;

		//include plugin boxes
	    do_action ( 'cowobo_after_content' );

		include(TEMPLATEPATH.'/templates/footlinks.php');

	echo '</div>';

	echo '<div class="background">';
		echo '<div class="pagesource unselectable" unselectable="on">';
			echo '<div class="notranslate code"><pre>'.htmlentities(file_get_contents(TEMPLATEPATH.'/templates/pagesource.php')).'</pre></div>';
		echo '</div>';
	echo '</div>';

echo '</div>';

get_footer();

;?>