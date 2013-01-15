<?php
global $cowobo, $profile_id, $langnames, $lang;

if( $cowobo->query->q ) :
	//if translating without javascript load page in google translate iframe
	include(TEMPLATEPATH.'/iframe.php');
else:
	get_header();

	//VARIABLES
	$action = $cowobo->query->action;
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
		if( $cowobo->query->post_ID ) $postid = $_POST['post_ID'];
		if( $post->ID == $userid || $canedit ) $author = true;
		else $author = false;
		if($profiles && $userid && in_array($userid, $profiles))$author = true;
		$cowobo->posts->update_views($post->ID);
		$coordinates = get_post_meta($post->ID, 'coordinates', true);
	endif;

	//add hidden description for google index
	echo '<div class="description hide">'.get_bloginfo('description').'</div>';

	//include site-wide links
	echo '<div class="feedlinks">';
		echo '<a class="sitetitle" href="'.home_url().'">Coders Without Borders</a>';
		echo '<a href="?action=contact">Contact</a>';
		echo '<a href="?action=search'.'">Search</a>';
		if(is_user_logged_in()):
			echo '<a href="'.get_permalink($profile_id).'">Profile</a>';
		else:
			echo '<a href="?action=login'.'">Profile</a>';
		endif;
		echo '<a class="showmap" href="?action=showmap'.'">Show Map ▼</a>';
		echo '<span class="maploading hide"></span>';
		echo '<a class="maplogo" href="http://www.mapquest.com" title="Visit MapQuest"><img src="'.get_bloginfo('template_url').'/images/mapquest.png" alt=""></a>';
	echo '</div>';

	//include translating message for other languages
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

		//include any notifications to user
		include( TEMPLATEPATH . '/templates/notify.php');

		//include the appropriate feed template
		if($action && file_exists(TEMPLATEPATH.'/templates/'.$action.'.php')):
			if($action == 'edit' && !is_user_logged_in()): $redirect = 'edit';
				include(TEMPLATEPATH.'/templates/login.php');
			else:
				include(TEMPLATEPATH.'/templates/'.$action.'.php');
			endif;
		elseif( $cowobo->query->new ): $author = true;
			if(!is_user_logged_in()): $redirect = 'new'; $redirect = 'new';
				 include(TEMPLATEPATH.'/templates/login.php');
			else:
				include(TEMPLATEPATH.'/templates/editpost.php');
			endif;
		elseif(is_404()):
			include(TEMPLATEPATH.'/templates/404.php');
		elseif(is_single()):
			include(TEMPLATEPATH.'/templates/posts.php');
		else:
			include(TEMPLATEPATH.'/templates/categories.php');
		endif;

		//include share forms below feeds
		if( ! $action && ! $cowobo->query->new ) include( TEMPLATEPATH . '/templates/share.php');

	echo '</div>';

	echo '<div class="background">';

		echo '<div class="planet">';
			echo '<img class="cloud" src="'.get_bloginfo('template_url').'/images/cloud.png" width="100%" alt=""/>';
			echo '<img class="angel1" src="'.get_bloginfo('template_url').'/images/angel1.png" alt=""/>';
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

		echo '<div class="footer">';
			echo '<a href="?action=contact">Disclaimer</a>';
			echo '<a href="?action=translate'.'">English (UK)</a>';
			if(is_user_logged_in()):
				echo '<a href="'.wp_logout_url(get_bloginfo('url')).'">Logout</a>';
			else:
				echo '<a href="?action=login'.'">Login</a>';
			endif;
		echo '</div>';

	echo '</div>';

	get_footer();

endif;?>