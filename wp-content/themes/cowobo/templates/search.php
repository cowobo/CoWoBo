<?php

if(is_single()) $editlink = '?action=editpost';
else  $editlink = '?action=editpage';

if(! is_user_logged_in()) $profilelink = '<li id="loginmenu">Your Profile ▼</li>';
else $profilelink = '<li><a class="black" href="'.get_permalink (cowobo()->users->current_user_profile_id ).'">Your Profile ▼</a></li>';



echo '<div class="tab">';

	echo '<div class="feedtitle">'.cowobo()->feed->feed_title().'</div>';
			
	echo '<ul class="horlist searchbar">';
		echo '<li id="searchmenu">Search ▼</li>';
		echo '<li id="catmenu">Categories ▼</li>';
		echo '<li id="sortmenu">Sort ▼</li>';
		echo '<li id="addmenu" class="blue">Add New ▼</li>';
		echo '<li id="editmenu">';
			echo '<a class="black" href="'.$editlink.'">Edit ▼</a>';
		echo '</li>';
		echo $profilelink;
	echo '</ul>';
	
	echo '<form method="GET" action="'.get_bloginfo('url').'" class="searchform">';
	
	    echo '<div class="hide dropmenu searchmenu">';
	        echo '<input type="text" class="searchfield" name="s" value="'.cowobo()->query->s.'" placeholder="Enter keywords to search for here.."/>';
	        echo '<input type="submit" class="button" value="Search"/>';
	        echo '<input type="checkbox" class="auto" name="allcats" value="1"> All Categories';
	    echo '</div>';
	
	    echo '<div class="hide dropmenu catmenu">';
	        if($querycats = cowobo()->query->cats) $selected = $querycats;
	        else $selected = array(get_cat_ID('Coders'), get_cat_ID('Jobs'));
	        $exclude = get_cat_ID('Uncategorized').','.get_cat_ID('Partners');
	        echo '<div class="clear dropoptions">';
	            foreach( get_categories('parent=0&hide_empty=0&exclude='.$exclude) as $cat ):
	                if(in_array($cat->term_id, $selected)) $state = 'checked'; else $state='';
	                echo '<span class="'.$state.'"><input type="checkbox" name="cats[]" value="'.$cat->term_id.'" '.$state.'>'.$cat->name.'</span>';
	            endforeach;
	        echo '</div>';
	        echo '<input type="submit" class="button clear" value="Search"/>';
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
	        else $selected = array( 'modified' );
	        echo '<div class="clear dropoptions">';
	            foreach( $sorttypes as $sortslug => $sortlabel ):
	                if( in_array($sortslug, $selected) ) $state = 'checked'; else $state='';
	                echo '<span class="'.$state.'"><input type="checkbox" name="sort[]" value="'.$sortslug.'" '.$state.'>'.$sortlabel.'</span>';
	            endforeach;
	        echo '</div>';
	        echo '<input type="submit" class="button" value="Update"/>';
	    echo '</div>';
		
		echo '<div class="hide dropmenu loginmenu">';
			include(TEMPLATEPATH.'/templates/login.php');
	    echo '</div>';
	
	echo '</form>';
	
	//Add Post form
	if(! is_user_logged_in() or cowobo()->query->new or cowobo()->query->action or is_single())
	$onload = 'hide'; else $onload = 'show';
	
	echo '<form method="GET" action="'.get_bloginfo('url').'">';
		echo '<div class="dropmenu addmenu '.$onload.'">';
			echo '<input type="text" class="extracturl" name="url" placeholder="Insert a URL or leave blank to create a post from scratch"/>';
			echo '<br/><input type="submit" class="button clear" value="Create"/>';
			echo '<select name="new" class="addnew">';
				$exclude = get_cat_ID('Uncategorized').','.get_cat_ID('Partners').','.get_cat_ID('Coders');
				foreach( get_categories('parent=0&hide_empty=0&exclude='.$exclude) as $cat ):
					if($cat->slug == 'news') $state = 'selected'; else $state='';
					echo '<option value="'.$cat->name.'" '.$state.'>'.ucfirst($cat->slug).'</option>';
				endforeach;
			echo '</select>';
			if(is_single() && $author) echo '<input type="checkbox" class="auto" name="linkto" value="1"> Add to this page';
		echo '</div>';
	echo '</form>';
	
echo '</div>';