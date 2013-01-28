<?php

echo '<div class="tab">';

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
		echo '<div id="rte" contenteditable="true" unselectable="off" tabindex="'.$index.'" class="richtext">'.trim ( $post_content ).'</div>';
		echo '<textarea name="emailtext" rows="12" class="htmlbox"></textarea>';		
		echo '<input type="text" name="user" class="hide" value=""/>'; //spammer trap
		echo '<input type="input" class="lefthalf" name="user_firstname" placeholder="Name" />';
		echo '<input type="input" class="righthalf" name="user_email" placeholder="Email" />';
		echo '<div class="clear" style="margin-top:5px">';
        wp_nonce_field( 'sendemail', 'sendemail' );
		echo '<button type="submit" class="button submitform">Send Email</button>';
		echo '</div>';
	echo '</form>';
	
echo '</div>';
