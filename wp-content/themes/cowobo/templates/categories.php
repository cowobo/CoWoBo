<?php

//add tabs for each post in feed
if(is_home()):
	
	echo '<img src="'.get_bloginfo('template_url').'/images/home.png" alt=""/>';
	echo '<img class="angel1" src="'.get_bloginfo('template_url').'/images/angel1.png" alt=""/>';				
	echo '<img class="angel2" src="'.get_bloginfo('template_url').'/images/angel2.png" alt=""/>';						
	echo ' <a class="learnmore" href="/wikis">learn more &raquo;</a>';
	
	echo '<div class="tab">';
		echo '<form method="GET" action="'.get_bloginfo('url').'" class="searchbar">';		
			echo '<select name="lookingfor" class="lookingfor">';
				echo '<option>I am looking for..</option>';
				foreach(get_categories('parent=0&hide_empty=0&exclude='.get_cat_ID('Uncategorized')) as $cat):
					echo '<option value="'.$cat->term_id.'">'.$cat->name.'</option>';
				endforeach;
			echo '</select>';
			echo '<select name="sort" class="sortby">';
				echo '<option>Sorted by..</option>';
				echo '<option value="modified">Recently Modified</option>';
				echo '<option value="title">Title</option>';
				echo '<option value="comment_count">Number of Comments</option>';
				echo '<option value="rand">Random</option>';
				echo '<option value="featured">Featured</option>';
			echo '</select>';
			
			echo '<input type="hidden" name="q" value="'.$_GET['q'].'"/>';
			echo '<button type="submit" class="button">Go</button> ';
		echo '</form>';
	echo '</div>';
	
else:

	echo '<div class="tab">';
		echo '<div class="feedtitle">'.cwb_feed_title().'</div>';
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
	echo '<div class="center">'; cwb_pagination(); echo '</div>';
	
endif;
?>
