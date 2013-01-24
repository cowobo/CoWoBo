<?php

foreach(get_categories('parent=0&hide_empty=0&exclude='.get_cat_ID('Uncategorized')) as $tabcat):
		$tabtype = 'cat'; include(TEMPLATEPATH.'/templates/tabs.php');
endforeach;

?>