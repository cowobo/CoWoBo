<?php


echo '<ul class="tab horlist searchbar">';
	echo '<li id="searchmenu">Search ▼</li>';
	echo '<li id="catmenu">Categories ▼</li>';			
	echo '<li id="sortmenu">Sort ▼</li>';
	echo '<li id="addmenu" class="blue">Add New ▼</li>';
	echo '<li id="layoutmenu">Layout ▼</li>';
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
			foreach( get_categories('parent=0&hide_empty=0&exclude='.$exclude) as $cat ):
				if(in_array($cat->term_id, $selected)) $state = 'checked'; else $state='';
				echo '<span class="'.$state.'"><input type="checkbox" name="cats[]" value="'.$cat->term_id.'" '.$state.'>'.$cat->name.'</span>';
			endforeach;
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
			echo '<div class="clear">';
				foreach( $sorttypes as $sortslug => $sortlabel ):
					if( in_array($sortslug, $selected) ) $state = 'checked'; else $state='';
					echo '<span class="'.$state.'"><input type="checkbox" name="sort[]" value="'.$sortslug.'" '.$state.'>'.$sortlabel.'</span>';			
				endforeach;
			echo '</div>';
			echo '<input type="submit" class="button" value="Update"/>';
		echo '</div>';
		
		echo '<div class="hide dropmenu layoutmenu">';
				echo 'This functionality is coming soon..';
		echo '</div>';
	
echo '</form>';	

echo '<form method="GET" action="'.get_bloginfo('url').'" class="tab">';
	echo '<div class="hide dropmenu addmenu">';
		echo '<select name="addnew" class="addnew">';
			foreach( get_categories('parent=0&hide_empty=0&exclude='.get_cat_ID('Uncategorized')) as $cat ):
				if(in_array($cat->term_id, $selected)) $state = 'checked'; else $state='';
				echo '<option value="'.$cat->term_id.'" '.$state.'>'.$cat->name.'</option>';
			endforeach;
		echo '</select>';
		echo '<input type="text" class="extracturl" name="url" placeholder="Optional Url"/>';
		echo '<br/><input type="submit" class="button clear" value="Add Post"/>';
		echo '<input type="checkbox" class="auto" name="selectcat" value="1"> To this page';
	echo '</div>';
echo '</form>';	