<?php


//define dynamic links
if(!is_user_logged_in()) $editlink = '<li><a class="black" href="?action=login">Edit ▼</a></li>';
elseif(is_single() && ( !isset ( $author ) || ! $author ) ) $editlink = '<li id="editmenu">Edit ▼</li>';
elseif (is_single()) $editlink = '<li><a class="black" href="?action=editpost">Edit ▼</a></li>';
else $editlink = '<li><a class="black" href="?action=editpage">Edit ▼</a></li>';

if(!is_user_logged_in()) $addlink = '<li><a class="black" href="?action=login">Add New ▼</a></li>';
else $addlink = '<li id="addmenu" class="blue">Add New ▼</li>';

if(is_home()) $homelink = '<li id="catmenu">Categories ▼</li>';
else $homelink = '<li id="catmenu">Coders Without Borders ▼</li>';

echo '<div class="tab">';
			
	echo '<ul class="horlist searchbar">';
		echo $homelink;
		echo '<li id="searchmenu">Search ▼</li>';
		echo '<li id="sortmenu">Sort ▼</li>';
		echo $editlink;
		echo $addlink;;
	echo '</ul>';
	
	echo '<form method="GET" action="'.get_bloginfo('url').'" class="searchform">';
		
		if($action != 'search') $state='hide'; else $state= '';
	    echo '<div class="dropmenu searchmenu '.$state.'">';
	        echo '<input type="text" class="searchfield" name="s" value="'.cowobo()->query->s.'" placeholder="Enter keywords to search for here.."/>';
	        echo '<input type="submit" class="button" value="Search"/>';
	        echo '<input type="checkbox" class="auto" name="allcats" value="1"> All Categories';
	    echo '</div>';

		if($action != 'categories') $state='hide'; else $state= '';	
	    echo '<div class="dropmenu catmenu '.$state.'">';
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

		if($action != 'sort') $state='hide'; else $state= '';
	    echo '<div class="dropmenu sortmenu '.$state.'">';
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

	//Add Request form if required
	if( ! isset ( $author ) || ! $author ):
		if($action == 'editpost') $state=''; else $state= 'hide';
		echo '<div class="dropmenu editmenu '.$state.'">';
			include(TEMPLATEPATH.'/templates/editrequest.php');
		echo '</div>';
	endif;
	
	//Add Post form
	if($action == 'add') $state=''; else $state= 'hide';
	echo '<div class="dropmenu addmenu '.$state.'">';
			include(TEMPLATEPATH.'/templates/addpost.php');
	echo '</div>';
		
echo '</div>';