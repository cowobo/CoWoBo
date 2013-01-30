<?php


echo '<div class="tab">';

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

	echo '<h2>';
		if ( cowobo()->query->login == 'login' )  echo 'To login';
		elseif ( cowobo()->query->action == 'editrequest' ) echo 'To edit this post';
		elseif( $redirect = 'comment' ) echo 'To comment';
		echo ' simply enter your e-mail address and a password &raquo;';
	echo '</h2>';

    /**
     * @todo is relogin still working?
     */
	$default = ( cowobo()->has_notice( 'WRONGPASSWORD' ) ) ? cowobo()->query->email : 'ie john@doe.com';
	echo '<form method="post" action="?action=login">';
		echo '<input type="text" name="email" class="lefthalf" value="'.$default.'" onfocus="this.value=\'\'" onblur="if(this.value==\'\') this.value=\'ie John\'" />';
		echo '<input type="text" name="user" class="hide" value=""/>'; //spammer trap
		echo '<input type="password" name="userpw" class="righthalf" value=""/>';
        if ( isset ( $redirect ) )
            echo '<input type="hidden" name="redirect" value="'.$redirect.'"/>';
        wp_nonce_field( 'login', 'login' );
		echo '<button type="submit" class="button">Login</button>';
		if ( cowobo()->has_notice( 'WRONGPASSWORD' ) ) echo '<a href="/?action=login&lostpassword=1&email=' . cowobo()->query->email . '">Help, I forgot my password</a>';
		else echo 'We will not disclose your email to others';
	echo '</form>';

endif;

echo '</div>';	?>
