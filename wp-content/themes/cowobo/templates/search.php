<?php
global $cowobo;

echo '<form method="GET" action="'.get_bloginfo('url').'" class="tab searchform">';		
	
	echo '<input type="text" class="searchbar blue" name="s" value="'.$cowobo->query->s.'" placeholder="I am looking for .."/>';
	echo '<input type="text" class="sortbar green" name="sortbar" disabled placeholder="Sorted by .."/>';
	echo '<input type="submit" class="gobutton" value="" title="Search"/>';

	if(!is_home()) $dropclass = 'hide';
	echo '<div class="dropmenu '.$dropclass.'">';
		
		echo '<div id="searchbar" class="half shade left blue">';
			if($querycats = $cowobo->query->cats) $selected = $querycats; 
			else $selected = array(get_cat_ID('Coders'), get_cat_ID('Jobs'));
			foreach( get_categories('parent=0&hide_empty=0&exclude='.get_cat_ID('Uncategorized')) as $cat ):
				if(in_array($cat->term_id, $selected)) $state = 'checked'; else $state='';
				echo '<span class="'.$state.'"><input type="checkbox" name="cats[]" value="'.$cat->term_id.'" '.$state.'>'.$cat->name.'</span>';
			endforeach;
		echo '</div>';
		
		$sorttypes = array(
			'rating'=>'Rating',
			'location'=>'Locations',
			'modified'=>'Date',
			'login'=>'Last Login',
			'category'=>'Category',
			'a-z'=>'Title A-Z',
			'z-a'=>'Title Z-A',
			'comment_count'=>'Replies',
			'rand'=>'Random',
		);
			
		echo '<div id="sortbar" class="half shade right green">';
			if( $querysort = $cowobo->query->sort ) $selected = $querysort; 
			else $selected = array( 'modified' );
			foreach( $sorttypes as $sortslug => $sortlabel ):
				if( in_array($sortslug, $selected) ) $state = 'checked'; else $state='';
				echo '<span class="'.$state.'"><input type="checkbox" name="sort[]" value="'.$sortslug.'" '.$state.'>'.$sortlabel.'</span>';			
			endforeach;
		echo '</div>';
		
		echo '<div class="closebutton" title="Hide Options"></div>';
			
	echo '</div>';
	
	if(is_home()):
		//echo '<div class="featured">Featured </div>';
	endif;
	
echo '</form>';	
