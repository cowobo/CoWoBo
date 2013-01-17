<?php
global $cowobo;

echo '<div class="tab">';
	echo '<form method="GET" action="'.get_bloginfo('url').'" class="center">';		

		echo '<span class="findlabel">Find</span>';
			
		echo '<select name="cats[]" class="findselect">';
			foreach(get_categories('parent=0&hide_empty=0&exclude='.get_cat_ID('Uncategorized')) as $cat):
				echo '<option value="'.$cat->term_id.'">'.$cat->name.'</option>';
			endforeach;
		echo '</select>';
			
		echo '<span class="bylabel">by</span>';			
			
		echo '<select name="sort" class="byselect">';
			echo '<option value="featured">Awesome Index</option>';
			echo '<option value="modified">Recently Updated</option>';
			echo '<option value="title">Alphabetical Order</option>';
			echo '<option value="comment_count">Number of Comments</option>';
			echo '<option value="rand">Give me something random</option>';
		echo '</select>';
			
		echo '<button type="submit" class="gobutton">Go</button> ';
	echo '</form>';
echo '</div>';