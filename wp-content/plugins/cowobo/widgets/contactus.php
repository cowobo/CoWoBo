<?php

if( cowobo()->query->user_email ) echo 'Your message has been sent.';
	echo '<form method="post" action="">';
		echo '<textarea name="emailtext" rows="5"></textarea>';		
		echo '<input type="text" name="user" class="hide" value=""/>'; //spammer trap
		echo '<input type="input" class="lefthalf" name="user_firstname" placeholder="Your Name" />';
		echo '<input type="input" class="righthalf" name="user_email" placeholder="Email Address" />';
        wp_nonce_field( 'sendemail', 'sendemail' );
   		echo '<input type="submit" class="button" value="Send Email">';
	echo '</form>';

