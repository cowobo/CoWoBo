<?php


	echo '<div class="feedtitle">'. cowobo()->feed->feed_title() .'</div>';
	echo '<img class="angel angel3" src="'.get_bloginfo('template_url').'/images/angel3.png" alt=""/>';						
	echo '<img class="angel angel4" src="'.get_bloginfo('template_url').'/images/angel1.png" alt=""/>';
	echo '<img class="angel angel5" src="'.get_bloginfo('template_url').'/images/angel2.png" alt=""/>';												

	include(TEMPLATEPATH.'/templates/search.php');		

	if (have_posts()):
		//$sort = $sort[$cat->term_id];
		while (have_posts()) : the_post();
			$tabpost = $post;
			$tabtype = 'post'; include(TEMPLATEPATH.'/templates/tabs.php');
		endwhile;
	endif;

	echo '<div class="tabthumb left">+</div>';
	echo '<div class="tabtext right">';
		echo '<h2>Add your post here &raquo;</h2>';
		echo '<span class="horlist">';
			$exclude = get_cat_ID('Uncategorized').','.get_cat_ID('Coders').','.get_cat_ID('Partners');
			foreach(get_categories('parent=0&exclude='.$exclude.'&hide_empty=0') as $cat):
				echo '<a href="?new='.$cat->name.'">'.$cat->name.'</a>';
			endforeach;
		echo '</span>';
		echo 'Adding relevant content helps boost your profile ratings';
	echo '</div>';

	//include navigation links
	echo '<div class="center">'; cowobo()->feed->pagination(); echo '</div>';

?>
