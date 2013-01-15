<?php
global $cowobo;

//add tabs for each post in feed
if(is_home()):

	echo '<div class="tab">';
		echo '<div class="feedtitle grey">We instruct technology to make the world a happy place</div>';
		echo '<img src="'.get_bloginfo('template_url').'/images/intro.png" alt=""/>';
	echo '</div>';

	foreach(get_categories('parent=0&hide_empty=0&exclude='.get_cat_ID('Uncategorized')) as $tabcat):
			$sort = $sort[$cat->term_id];
			$tabtype = 'cat'; include(TEMPLATEPATH.'/templates/tabs.php');
	endforeach;
else:

	echo '<div class="tab">';
		echo '<div class="feedtitle">'. $cowobo->feed->feed_title() .'</div>';
		echo '<div class="horlist">';
			echo '<a href="?sort=modified">Recently Modified</a>';
			echo '<a href="?sort=title">Title</a>';
			echo '<a href="?sort=comment_count">Number of Comments</a>';
			echo '<a href="?sort=rand">Random</a>';
			echo '<a href="?sort=featured">Featured</a>';
		echo '</div>';

	echo '</div>';

	if (have_posts()):
		$sort = $sort[$cat->term_id];
		while (have_posts()) : the_post();
			$tabpost = $post;
			$tabtype = 'post'; include(TEMPLATEPATH.'/templates/tabs.php');
		endwhile;
	endif;

	echo '<div class="tabthumb right">+</div>';
	echo '<div class="tabtext left">';
		echo '<h2>Add more posts &raquo;</h2>';
		echo '<span class="horlist">';
			$exclude = get_cat_ID('Uncategorized').','.get_cat_ID('Coders').','.get_cat_ID('Partners');
			foreach(get_categories('parent=0&exclude='.$exclude.'&hide_empty=0') as $cat):
				echo '<a href="?new='.$cat->name.'">'.$cat->name.'</a>';
			endforeach;
		echo '</span>';
		echo 'Adding relevant content helps boost your profile ratings';
	echo '</div>';

	//include navigation links
	echo '<div class="center">'; $cowobo->feed->pagination(); echo '</div>';

endif;
?>
