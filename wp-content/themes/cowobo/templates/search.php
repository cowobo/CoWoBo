<?php
global $cowobo;

echo '<form method="GET" action="'.get_bloginfo('url').'" class="tab searchform">';		
	
	echo '<input type="text" class="searchbar blue" name="s" placeholder="I am looking for .."/>';
	echo '<input type="text" class="sortbar green" name="sortbar" disabled placeholder="Sorted by .."/>';
	echo '<input type="submit" class="gobutton" value="" title="Search"/>';

	echo '<div class="dropmenu">';	
		
		echo '<div id="searchbar" class="half shade left blue">';
			$default = array('Coders', 'Jobs');
			foreach(get_categories('parent=0&hide_empty=0&exclude='.get_cat_ID('Uncategorized')) as $cat):
				if(in_array($cat->name, $default)) $state = 'checked'; else $state='';
				echo '<span class="'.$state.'"><input type="checkbox" name="cats[]" value="'.$cat->term_id.'" '.$state.'>'.$cat->name.'</span>';
			endforeach;
		echo '</div>';
		
		echo '<div id="sortbar" class="half shade right green">';
			echo '<span><input type="checkbox" name="sort" value="rating">Rating</span>';
			echo '<span><input type="checkbox" name="sort" value="location">Location</span>';			
			echo '<span class="checked"><input type="checkbox" name="sort" value="modified" checked>Date</span>';
			echo '<span><input type="checkbox" name="sort" value="login">Last login</span>';
			echo '<span><input type="checkbox" name="sort" value="category">Category</span>';
			echo '<span><input type="checkbox" name="sort" value="a-z">Title A-Z</span>';
			echo '<span><input type="checkbox" name="sort" value="z-a">Title Z-A</span>';
			echo '<span><input type="checkbox" name="sort" value="comment_count">Comments</span>';
			echo '<span><input type="checkbox" name="sort" value="rand">Random</span>';
		echo '</div>';
		
		echo '<div class="closebutton" title="Hide Options"></div>';
			
	echo '</div>';
	
echo '</form>';	
