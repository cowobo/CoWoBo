<?php 

echo '<div class="tab">';
	
	echo '<div class="feedtitle">Search for posts</div><br/>';
	
	echo '<form method="GET" action="'.get_bloginfo('url').'">';

		echo '<h3>Key words:</h3>';
		echo '<input type="text" name="keywords" class="new" title="Search"/><br/>';

		echo '<br/>';
		
		echo '<h3>Categories:</h3>';
		echo '<div class="box browsecat">';
			echo '<ul class="horlist">';
				echo '<li><input type="checkbox" name="cats[]" value="all" checked="checked">All Categories</li>';
				foreach(get_categories('parent=0&hide_empty=0&exclude='.get_cat_ID('Uncategorized')) as $subcat):
					$countposts = get_posts('cat='.$subcat->term_id.'&numberposts=-1');
					echo '<li><input type="checkbox" name="cats[]" value="'.$subcat->term_id.'">'.$subcat->name.' ('.count($countposts).')</li>';				
			endforeach;
			echo '</ul>';
		echo '</div>';

		echo '<br/>';

		echo '<h3>Country:</h3>';
		echo '<select name="country" class="full" title="Sort">';
			echo '<option value="all">All Countries</option>';
			foreach(get_categories('child_of='.get_cat_ID('Regions')) as $cat):
				echo '<option value="'.$cat->term_id.'">'.$cat->name.'</option>';
			endforeach;
		echo '</select><br/>';

		echo '<br/>';
					
		echo '<h3>Sorted by:</h3>';
		echo '<select name="sort" class="full" title="Sort">';
			echo '<option value="modified">Recently Modified</option>';
			echo '<option value="title">Title</option>';
			echo '<option value="comment_count">Number of Comments</option>';
			echo '<option value="rand">Random</option>';
			echo '<option value="featured">Featured</option>';
		echo '</select><br/>';
	
		echo '<br/>';
		
		echo '<input type="hidden" name="q" value="'.$_GET['q'].'"/>';
		echo '<button type="submit" class="button">Search</button> ';
	echo '</form>';
	
echo '</div>';	