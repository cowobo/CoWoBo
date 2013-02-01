<?php

//Warn users trying to access via the google translate iframe.
if( isset ( $_SERVER['HTTP_VIA'] ) && ! empty ( $_SERVER['HTTP_VIA'] ) ):

	echo '<h2>To interact in your language you must have javascript enabled.</h2>';
	echo 'Please check your browser settings or use another device to access our site';

elseif ( cowobo()->query->lostpassword ) :

    echo '<h2>Lost Password?</h2>';
    echo '<form method="post" action="">';
    wp_nonce_field('lost_pass','lost_pass');
    echo '<p>We will send you a link you can use to reset your password!</p>';
    echo '<input type="text" name="user_login" placeholder="Your e-mailadres" value="' . cowobo()->query->email . '">';
    echo '<button type="submit" class="button">Reset password</button>';

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

	echo '<h2>We could not find your profile, are you new here?</h2><br/>';
	echo '<form method="post" action="">';
        wp_nonce_field('confirm', 'confirm');
		echo '<input type="hidden" name="userpw" value="'.cowobo()->query->userpw.'"/>';
		echo '<input type="hidden" name="email" value="'.cowobo()->query->email.'"/>';
		echo '<button type="submit" class="button">Yes, add me</button>';
		echo '<a class="button" href="?action=login&relogin='.cowobo()->query->email.'">No, I have logged in before</a>';
	echo '</form>';

else:

    /**
     * @todo is relogin still working?
     */
	$default = cowobo()->query->email;
	echo '<div style="margin-bottom:10px">Simply enter your e-mail address and a password:</div>';
	
	echo '<form method="post" action="?action=login">';
		echo '<input type="text" name="email" class="lefthalf" value="'.$default.'" placeholder="ie john@doe.com" />';
		echo '<input type="text" name="user" class="hide" value=""/>'; //spammer trap
		echo '<input type="password" name="userpw" class="righthalf" placeholder="&#9679;&#9679;&#9679;&#9679;&#9679;"/>';
        if ( isset ( $redirect ) )
            echo '<input type="hidden" name="redirect" value="'.$redirect.'"/>';
		echo '<div class="clear">';
			echo '<button type="submit" class="button">Enter</button>';
			echo '<input type="checkbox" class="auto" name="rememberme"> Remember me';
		echo '</div>';
		if ( cowobo()->has_notice( 'WRONGPASSWORD' ) ) 
			echo '<a href="/?action=login&lostpassword=1&email=' . cowobo()->query->email . '">Help, I forgot my password</a>';
		wp_nonce_field( 'login', 'login' );
	echo '</form>';

endif;
?>
