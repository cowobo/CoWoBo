<?php


echo '<div class="tab">';

//Warn users trying to access via the google translate iframe.
if( isset ( $_SERVER['HTTP_VIA'] ) && ! empty ( $_SERVER['HTTP_VIA'] ) ):

	echo '<h2>To interact in your language you must have javascript enabled.</h2>';
	echo 'Please check your browser settings or use another device to access our site';

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
	$default = ( cowobo()->query->relogin ) ? cowobo()->query->relogin : 'ie john@doe.com';
	echo '<form method="post" action="?action=login">';
		echo '<input type="text" name="email" class="lefthalf" value="'.$default.'" onfocus="this.value=\'\'" onblur="if(this.value==\'\') this.value=\'ie John\'" />';
		echo '<input type="text" name="user" class="hide" value=""/>'; //spammer trap
		echo '<input type="password" name="userpw" class="righthalf" value=""/>';
		echo '<input type="hidden" name="redirect" value="'.$redirect.'"/>';
        wp_nonce_field( 'login', 'login' );
		echo '<button type="submit" class="button">Login</button>';
		echo 'We will not disclose your email to others';
		if ( cowobo()->query->relogin ) echo '<a href="">Help, I forgot my password</a>';
	echo '</form>';

endif;

echo '</div>';	?>
