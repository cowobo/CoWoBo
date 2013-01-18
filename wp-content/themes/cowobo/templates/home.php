<?php

echo '<div class="introtitle">We instruct technology<br/>to improve the planet</div>';

echo '<div class="feed">';
	echo '<a class="learnmore" href="/tags/wiki">learn more &raquo;</a>';	
	echo '<img class="introimg" src="'.get_bloginfo('template_url').'/images/intro.png" alt=""/>';
	echo '<img class="angel angel1" src="'.get_bloginfo('template_url').'/images/angel1.png" alt=""/>';				
	echo '<img class="angel angel2" src="'.get_bloginfo('template_url').'/images/angel2.png" alt=""/>';	
	include(TEMPLATEPATH.'/templates/search.php');	
echo '</div>';
