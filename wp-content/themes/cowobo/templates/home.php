<?php

echo '<div class="introtitle">Instructing technology to<br/>improve the planet. ';
echo '<a href="/tags/wiki">Tour &raquo;</a></div>';

echo '<div class="feed">';
	echo '<img class="introimg" src="'.get_bloginfo('template_url').'/images/intro.png" alt=""/>';
	echo '<img class="angel angel1" src="'.get_bloginfo('template_url').'/images/angel1.png" alt=""/>';				
	echo '<img class="angel angel2" src="'.get_bloginfo('template_url').'/images/angel2.png" alt=""/>';	
	include(TEMPLATEPATH.'/templates/search.php');	
echo '</div>';
