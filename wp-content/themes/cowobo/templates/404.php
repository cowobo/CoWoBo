<?php

echo '<div class="tab center">';
	echo '<h2>Could it be one of these?</h2>';
echo '</div>';

//todo query related posts to missing url
query_posts('numberposts=8');
if (have_posts()): while (have_posts()) : the_post();
	$tabtype = 'post'; include(TEMPLATEPATH.'/templates/tabs.php');
endwhile; endif;


?>