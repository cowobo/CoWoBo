<?php

if( cowobo()->query->user_email ) echo 'Your message has been sent.';
	echo '<form method="post" action="">';
		echo '<div class="richbuttons">';
			echo '<a class="makebold" href="#">Bold</a>';
			echo '<a class="makeitalic" href="#">Italic</a>';
			echo '<a class="makeunderline" href="#">Underline</a>';
			echo '<a class="makelink" href="#">Link</a>';
			echo '<a class="htmlmode" href="#">HTML</a>';
			echo '<a class="richmode" href="#">WYSIWYG</a>';
		echo '</div>';
		echo '<div id="rte" contenteditable="true" unselectable="off" class="richtext"></div>';
		echo '<textarea name="emailtext" rows="5" class="htmlbox"></textarea>';		
		echo '<input type="text" name="user" class="hide" value=""/>'; //spammer trap
		echo '<input type="input" class="lefthalf" name="user_firstname" placeholder="Your Name" />';
		echo '<input type="input" class="righthalf" name="user_email" placeholder="Email Address" />';
        wp_nonce_field( 'sendemail', 'sendemail' );
   		echo '<input type="submit" class="button" value="Send Email">';
	echo '</form>';

