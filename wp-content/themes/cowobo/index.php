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

	if(is_home() && !$cowobo->query->s && !$cowobo->query->new){
		$mapheight = 'style="margin-top:-100px"'; $ishome = true;
	} else if(is_single()){
		$mapheight = 'style="margin-top:-100px"';
	} else {
		$mapheight = 'style="margin-top:-200px"';
	}
	
	//include hidden description for google index
	echo '<div class="description hide">'.get_bloginfo('description').'</div>';

	//include header links
	echo '<div class="headerlinks">';
		echo '<a class="sitetitle" href="'.get_bloginfo('url').'"><b>Coders</b> Without <b>Borders</b></a>';
		echo '<a href="?action=contact">Contact</a>';
		if(is_user_logged_in()):
			echo '<a href="'.get_permalink($profile_id).'">Your Profile</a>';
		else:
			echo '<a href="?action=login'.'">Your Profile</a>';
		endif;
	echo '</div>';	

	//include planet/imageviewer controls
	echo '<div class="nav pandiv">';
		echo '<a class="pan panleft" href="?pan=left">&#60;</a>';
		echo '<a class="pan panright" href="?pan=right">&#62;</a>';
		echo '<a class="pan panup" href="?pan=up">&#8743;</a>';
		echo '<a class="pan pandown" href="?pan=down">&#8744;</a>';
		echo '<img src="'.get_bloginfo('template_url').'/images/circle.png" alt=""/>';
	echo '</div>';
	echo '<div class="nav zoomindiv">';
		echo '<a class="zoom zoomin" href="?zoom=in">+</a>';
		echo '<img src="'.get_bloginfo('template_url').'/images/circle.png" alt=""/>';
	echo '</div>';
	
	echo '<div class="nav zoomoutdiv">';
		echo '<a class="zoom zoomout" href="?zoom=out">-</a>';
		echo '<img src="'.get_bloginfo('template_url').'/images/circle.png" alt=""/>';
	echo '</div>';
	
	//include planet/imageviewer
	echo '<div class="planet grabcursor">';
		echo '<img class="shadow" src="'.get_bloginfo('template_url').'/images/shadow.png" alt=""/>';
		echo '<img class="proportion" src="'.get_bloginfo('template_url').'/images/proportion.png" width="100%" alt=""/>';
		echo cwb_loadmap();
		
	echo '</div>';

	//include page
	echo '<div class="page" '.$mapheight.'>';
		
		//include titlebar
		echo '<div class="titlebar">';
			echo '<div class="titlebox">';
				echo '<span class="feedtitle">'.cowobo()->feed->feed_title().'</span>';
				echo '<a class="searchform" href="?action=search">Search ▼</a>';
				if(is_single()) $editlink = '?action=editpost';
				else  $editlink = '?action=contribute';
				echo '<a class="editpage" href="'.$editlink.'">Edit Page ▼</a>';
			echo '</div>';
			echo '<div class="shade"></div>';
			echo '<img class="resizeicon" src="'.get_bloginfo('template_url').'/images/resizeicon.png" title="Expand" alt=""/>';
		echo '</div>';
		
		
		//include dragbar to resize imageviewer
		echo '<div class="dragbar"></div>';
		
		//include shadow
				
		//include feed (hide if we are translating with javascript)
		echo '<div class="feed">';
			
			echo '<img class="shadow" src="'.get_bloginfo('template_url').'/images/shadow.png" alt=""/>';

			//include searchform
			include(TEMPLATEPATH.'/templates/search.php');
	
			//if translating show notice
			if($translate) echo '<h2 class="translating">'.$subtitle.'<span class="loading"></span></h2>';
	
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
			elseif(is_category() or cowobo()->query->s):
				include(TEMPLATEPATH.'/templates/categories.php');
			endif;
		
				do_action ( 'cowobo_after_content' );
	
	        do_action ( 'cowobo_after_content' );
	        if (is_user_logged_in() )
	            do_action ( 'cowobo_after_content_loggedin' );
	
			//clear floats in feed
			echo '<div class="clear"></div>';
			
			include(TEMPLATEPATH.'/templates/footer.php');
	
		echo '</div>';

		echo '<div class="background">';
			
			echo '<img class="shadow" src="'.get_bloginfo('template_url').'/images/shadow.png" alt=""/>';

			echo '<div class="pagesource unselectable" unselectable="on">';
				echo '<div class="rownumbers">';
					for($x=1; $x<300; $x++): echo $x.'<br/>'; endfor;
				echo '</div>';
				echo '<div class="notranslate code">'.htmlentities(file_get_contents(TEMPLATEPATH.'/templates/pagesource.php')).'</div>';
			echo '</div>';
		echo '</div>';
	
	echo '</div>';

	get_footer();

endif;?>