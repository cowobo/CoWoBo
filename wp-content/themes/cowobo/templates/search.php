<?php

if(is_single()) $editlink = '?action=editpost';
else  $editlink = '?action=editpage';

if(is_home()) $homelink = 'Categories';
else $homelink = 'Coders Without Borders';

echo '<div class="tab">';
			
	echo '<ul class="horlist searchbar">';
		echo '<li id="catmenu">'.$homelink.' ▼</li>';
		echo '<li id="searchmenu">Search ▼</li>';
		echo '<li id="sortmenu">Sort ▼</li>';
		echo '<li id="addmenu" class="blue">Add New ▼</li>';
		echo '<li id="editmenu">';
			echo '<a class="black" href="'.$editlink.'">Edit ▼</a>';
		echo '</li>';
	echo '</ul>';
	
	echo '<form method="GET" action="'.get_bloginfo('url').'" class="searchform">';
	
	    echo '<div class="hide dropmenu searchmenu">';
	        echo '<input type="text" class="searchfield" name="s" value="'.cowobo()->query->s.'" placeholder="Enter keywords to search for here.."/>';
	        echo '<input type="submit" class="button" value="Search"/>';
	        echo '<input type="checkbox" class="auto" name="allcats" value="1"> All Categories';
	    echo '</div>';
	
	    echo '<div class="hide dropmenu catmenu">';
	        echo '<div class="clear dropoptions">';
				echo '<span><a href="'.get_bloginfo('url').'">HOME (538)</a></span>';
	            foreach( get_categories('parent=0&hide_empty=0&exclude='.get_cat_ID('Uncategorized')) as $cat ):
					$catposts = get_posts('cat='.$cat->term_id.'&numberposts=-1');
	                echo '<span><a href="'.get_category_link($cat->term_id).'">'.$cat->name.' ('.count($catposts).')</a></span>';
	            endforeach;
	        echo '</div>';
	    echo '</div>';
	
	    $sorttypes = array(
	        'rating'=>'Rating',
	        'location'=>'Locations',
	        'modified'=>'Date',
	        'category'=>'Category',
	        'a-z'=>'Title A-Z',
	        'z-a'=>'Title Z-A',
	        'comment_count'=>'Replies',
	        'rand'=>'Random',
	    );
	
	    echo '<div class="hide dropmenu sortmenu">';
	        if( $querysort = cowobo()->query->sort ) $selected = $querysort;
	        else $selected = 'modified';
	        echo '<div class="clear dropoptions">';
	            foreach( $sorttypes as $sortslug => $sortlabel ):
	                if( $sortslug == $selected ) $state = 'checked'; else $state='';
	                echo '<span class="'.$state.'"><input type="radio" name="sort" value="'.$sortslug.'" '.$state.'>'.$sortlabel.'</span>';
	            endforeach;
	        echo '</div>';
	        echo '<input type="submit" class="button" value="Update"/>';
	    echo '</div>';
		
	echo '</form>';
	
	//Add Post form
	echo '<form method="GET" action="'.get_bloginfo('url').'">';
		echo '<div class="dropmenu addmenu hide">';
			echo '<input type="text" class="extracturl" name="url" placeholder="Insert a URL or leave blank to create a post from scratch"/>';
			echo '<select name="new" class="addnew">';
				$exclude = get_cat_ID('Uncategorized').','.get_cat_ID('Partners').','.get_cat_ID('Coders').','.get_cat_ID('Locations');
				foreach( get_categories('parent=0&hide_empty=0&exclude='.$exclude) as $cat ):
					if($cat->slug == 'news') $state = 'selected'; else $state='';
					echo '<option value="'.$cat->name.'" '.$state.'>'.ucfirst($cat->slug).'</option>';
				endforeach;
			echo '</select>';
			echo '<input type="submit" class="button clear" value="Create"/>';
			if(is_single() && $author) echo '<span class="right"><input type="checkbox" class="auto" name="linkto" value="1"> Add to this page</span>';
		echo '</div>';
	echo '</form>';
	
echo '</div>';