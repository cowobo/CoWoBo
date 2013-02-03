<?php
global $profile_id, $langnames, $lang;

get_header();

//VARIABLES
if ( ! isset ( $postid ) ) $postid = 0;
$action = cowobo()->query->action;
$imgfolder = get_bloginfo('template_url').'/images';
$viewerheight = '70%';

if(is_home()) {
	$feedtitle = $langnames[$lang][1];
	$subtitle = $langnames[$lang][2];
	$intropost = get_page_by_title( 'All About Us', 'OBJECT', 'post' );
	if ( $intropost ) $postid = $intropost->ID;
} elseif(is_single()) {
	$userid = $profile_id;
	$location = get_post_meta($post->ID, 'cwb_location', true);
	$profiles = get_post_meta($post->ID, 'cwb_author', false);
	$canedit = current_user_can('edit_others_posts') || cowobo()->debug;
	if( ! $postid ) $postid = $post->ID;
	if( cowobo()->query->post_ID ) $postid = $_POST['post_ID'];
	if( $post->ID == $userid || $canedit ) $author = true;
	else $author = false;
	if($profiles && $userid && in_array($userid, $profiles))$author = true;
	cowobo()->posts->update_views($post->ID);
	$coordinates = get_post_meta($post->ID, 'cwb_coordinates', true);
}

//include hidden description for google index
echo '<div class="description hide">'.get_bloginfo('description').'</div>';

//include image viewer
echo '<div class="imageviewer grabcursor">';

	//set the height on load of the image viewer
	echo '<img class="ratio" src="'.$imgfolder.'/ratio-map.png" width="'.$viewerheight.'" alt=""/>';

	//include planet/imageviewer controls
	echo '<a class="pan panleft" href="?pan=left"></a>';
	echo '<a class="pan panright" href="?pan=right"></a>';
	echo '<a class="pan panup" href="?pan=up"></a>';
	echo '<a class="pan pandown" href="?pan=down"></a>';
	echo '<a class="zoom zoomin" href="?zoom=in"></a>';
	echo '<a class="zoom zoomout" href="?zoom=out"></a>';

	cowobo()->posts->loadgallery($postid);

echo '</div>';


//include page
echo '<div class="page">';

	//include page shadow
	echo '<img class="shadow" src="'.$imgfolder.'/shadow.png" alt=""/>';

	//center dynamic content with container
	echo '<div class="container">';

		//include feed reader
		echo '<div class="feed">';

			//include search
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

		echo '</div>';

		//include action sidebar
		echo '<div class="widgets">';
	        if (is_user_logged_in() ) dynamic_sidebar('sidebar_logged_in');
	        else dynamic_sidebar('sidebar');
			dynamic_sidebar('always');
		echo '</div>';

	echo '</div>';

echo '</div>';

get_footer();

;?>