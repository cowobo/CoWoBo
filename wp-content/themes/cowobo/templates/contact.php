<?php
global $cowobo;

echo '<div class="tab">';
	echo '<h2>Send us an email &raquo;</h2>';
	if( $cowobo->query->user_email ) echo 'Your message has been sent.';
	echo '<form method="post" action="">';
		echo '<textarea name="emailtext" rows="3" class="emailtext"></textarea>';
		echo '<input type="text" name="user" class="hide" value=""/>'; //spammer trap
		echo '<input type="input" class="half" name="user_firstname" value="Your Name" onfocus="this.value=\'\'"/>';
		echo '<input type="input" class="half" name="user_email" value="Your Email" onfocus="this.value=\'\'"/>';
		echo '<div class="clear" style="margin-top:5px">';
        wp_nonce_field( 'sendemail', 'sendemail' );
		echo '<button type="submit" class="button">Send Email</button>';
		echo '</div>';
	echo '</form>';
echo '</div>';
