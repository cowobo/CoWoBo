<?php
echo '<div class="tab">';
//Warn users trying to access via the google translate iframe.
	if( isset ( $_SERVER['HTTP_VIA'] ) && ! empty ( $_SERVER['HTTP_VIA'] ) ):
	
		echo '<h2>To interact in your language you must have javascript enabled.</h2>';
		echo 'Please check your browser settings or use another device to access our site';
	
	elseif ( cowobo()->query->lostpassword ) :
	
	    echo '<h2>Lost Your Password?</h2>';
	    echo '<form method="post" action="">';
	    wp_nonce_field('lost_pass','lost_pass');
	    echo '<p>We will send you a link you can use to reset your password!</p>';
	    echo '<input type="text" name="user_login" placeholder="Your e-mailadres" value="' . cowobo()->query->email . '">';
	    echo '<button type="submit" class="button">Reset password</button>';
		echo "</form>";
		
	elseif ( cowobo()->query->rp ) :
	
	    $user = cowobo()->users->check_password_reset_key();
	    if ( is_wp_error($user) ) {
	        cowobo()->add_notice('An error has occurred.', 'error' );
	    } else {
	        if ( cowobo()->query->pass1 ) {
	            if ( cowobo()->query->pass1 != cowobo()->query->pass2 )
	                cowobo()->add_notice ( 'The passwords do not match.', 'error' );
	            cowobo()->users->reset_password($user, cowobo()->query->pass1 );
	            cowobo()->add_notice('Your password has been reset.', 'message' );
	        } else {
	            echo "<form action='' method='POST'>";
	            echo '<input type="hidden" id="user_login" value="' . cowobo()->query->login . '">';
	            echo '<label for="pass1">New password<br />';
	            echo '<input type="password" name="pass1" id="pass1" class="input" size="20" value=""></label>';
	            echo '<label for="pass2">Confirm new password<br />';
	            echo '<input type="password" name="pass2" id="pass2" class="input" size="20" value=""></label>';
	            echo '<button type="submit" class="button">Reset Password</button>';
	            echo "</form>";
	        }
	    }
	    cowobo()->print_notices( array ( 'message', 'error' ) );
	
	
	elseif ( cowobo()->has_notice( 'INVALIDUSER' ) ) :
		
		echo '<h2>Is this your first time here?</h2><br/>';
		echo '<form method="post" action="">';
		    wp_nonce_field('confirm', 'confirm');
			echo '<input type="hidden" name="userpw" value="'.cowobo()->query->userpw.'"/>';
			echo '<input type="hidden" name="email" value="'.cowobo()->query->email.'"/>';
			echo '<button type="submit" class="button" style="margin-right:15px">Yes, please add me</button>';
			echo '<a class="button" href="?action=login&relogin='.cowobo()->query->email.'">No, I have logged in before</a>';
		echo '</form>';
		
	else:

		if($relogin = cowobo()->query->relogin) {
			echo '<h2>Enter the password you used last time</h2>';
			echo 'Click here if you want us to mail it to you';
		} else {
			echo '<h2>To interact you need a basic identity</h2>';
			echo 'Simply enter your email and a password:';
		}
		
		echo '<form method="post" action="?action=login" style="margin-top:10px">';
			echo '<input type="text" name="email" class="lefthalf" value="'.$relogin.'" placeholder="ie john@doe.com" />';
			echo '<input type="text" name="user" class="hide" value=""/>'; //spammer trap
			echo '<input type="password" name="userpw" class="righthalf" placeholder="&#9679;&#9679;&#9679;&#9679;&#9679;"/>';
			echo '<div class="clear">';
				echo '<button type="submit" class="button">That\'s Me</button>';
				echo '<input type="checkbox" class="auto" name="rememberme"> Don\'t ask again';
			echo '</div>';
			if ( cowobo()->has_notice( 'WRONGPASSWORD' ) ) 
				echo '<a href="/?action=login&lostpassword=1&email=' . cowobo()->query->email . '">Help, I forgot my password</a>';
			wp_nonce_field( 'login', 'login' );
		echo '</form>';
		
	endif;
echo '</div>';
	
echo '<div class="tab">';
	echo '<h2>Without an identity</h2>';
	echo '- We can\'t save any of the posts or comments you add<br/>';
	echo '- Store your preferred language settings<br/>';
	echo '- Customize the types of content you are interested in<br/>';	
	echo '- Hide content you do not want to share with everyone<br/>';
	echo '- Contact you in case a client or coder wants is interested in your work<br/>';
	echo '- Prevent others from copying your projects<br/>';
echo '</div>';
	
echo '<div class="tab">';
	echo '<h2>Tips to avoid this message</h2>';
	echo '- Turn on cookies in your browser<br/>';
	echo '- Use your browser\'s autocomplete functions or lastpas.com<br/>';
	echo '- Enter a valid email so we can send your lost password<br/>';
echo '</div>';

?>
