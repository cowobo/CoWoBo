<?php
global $profile_id, $langnames, $lang;

if( cowobo()->query->q ) :
	//if translating without javascript load page in google translate iframe
	include(TEMPLATEPATH.'/iframe.php');
else:
	get_header();

	//VARIABLES
	$action = cowobo()->query->action;
	$feedtitle = $langnames[$lang][2];
	if(is_home()):
		$feedtitle = $langnames[$lang][1];
		$subtitle = $langnames[$lang][2];
	elseif(is_single()):
        $post = get_post();
		$userid = $profile_id;
		$location = get_post_meta($post->ID, 'location', true);
		$profiles = get_post_meta($post->ID, 'author', false);
		$canedit = current_user_can('edit_settings');
		if(! isset ( $postid ) || ! $postid ) $postid = $post->ID;
		if( cowobo()->query->post_ID ) $postid = $_POST['post_ID'];
		if( $post->ID == $userid || $canedit ) $author = true;
		else $author = false;
		if($profiles && $userid && in_array($userid, $profiles))$author = true;
		cowobo()->posts->update_views($post->ID);
		$coordinates = get_post_meta($post->ID, 'coordinates', true);
	endif;

	//add hidden description for google index
	echo '<div class="description hide">'.get_bloginfo('description').'</div>';

	//include planet/imageviewer
	echo '<div class="planet grabcursor" '.$mapheight.'>';
		echo '<img class="cloud" src="'.get_bloginfo('template_url').'/images/cloud.png" width="100%" alt=""/>';
		echo cwb_loadmap();
		echo '<div class="titlebar">';
			echo '<span class="feedtitle">'.$cowobo->feed->feed_title().'</span>';
			echo '<img src="'.get_bloginfo('template_url').'/images/intro.png" alt=""/>';
		echo '</div>';
	echo '</div>';

	//include planet/imageviewer controls
	echo '<div class="navcontrols">';
		echo '<a class="pan panleft" href="?pan=left">&#60;</a>';
		echo '<a class="pan panright" href="?pan=right">&#62;</a>';
		echo '<a class="pan panup" href="?pan=up">&#8743;</a>';
		echo '<a class="pan pandown" href="?pan=down">&#8744;</a>';
		echo '<a class="zoom zoomin" href="?zoom=in">+</a>';
		echo '<a class="zoom zoomout" href="?zoom=out">-</a>';
	echo '</div>';

	
	//include page
	echo '<div class="page">';

		//include dragbar to resize imageviewer
		echo '<div class="dragbar"></div>';
			
		//if translating hide feed and show notice
	if($translate):
		echo '<div class="feeds translating">';
			echo '<div class="feedtitle">'.$feedtitle.'</div>';
			echo '<h2 class="center">'.$subtitle.'<span class="loading"></span></h2>';
		echo '</div>';
		$state='hide';
	else:
		$state='';
	endif;

	//include feed (hide if we are translating with javascript)
	echo '<div class="feed" '.$state.'>';

			if(is_home() && ! cowobo()->query->s && ! cowobo()->query->new && ! cowobo()->query->action ):
				include(TEMPLATEPATH.'/templates/search.php');
			endif;

		//include any notifications to user
		include( TEMPLATEPATH . '/templates/notify.php');

		//include the appropriate feed template
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
		elseif(is_category() or cowobo()->query->s):
			include(TEMPLATEPATH.'/templates/categories.php');
		endif;

        do_action ( 'cowobo_after_content' );
        if (is_user_logged_in() )
            do_action ( 'cowobo_after_content_loggedin' );

		//include share forms below feeds
		//if( ! $action && ! cowobo()->query->new ) include( TEMPLATEPATH . '/templates/share.php');

		//clear floats in feed
		echo '<div class="clear"></div>';

	echo '</div>';

	echo '<div class="background">';

		echo '<div class="planet">';
			echo '<img class="cloud" src="'.get_bloginfo('template_url').'/images/cloud.png" width="100%" alt=""/>';
			echo cwb_loadmap();
		echo '</div>';

		//include background for image disabled browsers
		echo '<div class="menuback"></div>';

		echo '<div class="pagesource unselectable" unselectable="on">';
			echo '<div class="rownumbers">';
				for($x=1; $x<300; $x++): echo $x.'<br/>'; endfor;
			echo '</div>';
			echo '<pre class="notranslate">'.htmlentities(file_get_contents(TEMPLATEPATH.'/templates/pagesource.php')).'</pre>';
		echo '</div>';

		include(TEMPLATEPATH.'/templates/footer.php');

	echo '</div>';

	get_footer();

endif;?>