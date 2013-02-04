<?php	
echo '<form method="GET" action="'.get_bloginfo('url').'">';
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
echo '</form>';