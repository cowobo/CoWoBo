<?php

if (have_posts()):
	//$sort = $sort[$cat->term_id];
	while (have_posts()) : the_post();
		$tabpost = $post;
		$tabtype = 'post'; include(TEMPLATEPATH.'/templates/tabs.php');
	endwhile;
endif;

//include navigation links
echo '<div class="center">'; cowobo()->feed->pagination(); echo '</div>';

?>
