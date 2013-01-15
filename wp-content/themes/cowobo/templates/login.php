<?php

echo '<div class="tab">';

//Warn users trying to access via the google translate iframe.
if($_SERVER['HTTP_VIA']):

	echo '<h2>To interact in your language you must have javascript enabled.</h2>';
	echo 'Please check your browser settings or use another device to access our site';

elseif ($_POST['userpw']):

	echo '<h2>We could not find your profile, are you new here?</h2><br/>';
	echo '<form method="post" action="">';
        wp_nonce_field('confirm', 'confirm');
		echo '<input type="hidden" name="userpw" value="'.$_POST['userpw'].'"/>';
		echo '<input type="hidden" name="email" value="'.$_POST['email'].'"/>';
		echo '<button type="submit" class="button">Yes, add me</button>';
		echo '<a class="button" href="?action=login&relogin='.$_POST['email'].'">No, I have logged in before</a>';
	echo '</form>';

else:

	echo '<h2>';
		if($_GET['action'] == 'login')  echo 'To login';
		elseif($_GET['action'] == 'editrequest') echo 'To edit this post';
		elseif($redirect = 'comment') echo 'To comment';
		echo ' simply enter your first name and a password &raquo;';
	echo '</h2>';

	if($_GET['relogin']) $default = $_GET['relogin']; else $default ='ie John';
	echo '<form method="post" action="?action=login">';
		echo '<input type="text" name="email" class="smallfield" value="'.$default.'" onfocus="this.value=\'\'" onblur="if(this.value==\'\') this.value=\'ie John\'" />';
		echo '<input type="text" name="user" class="hide" value=""/>'; //spammer trap
		echo '<input type="password" name="userpw" class="smallfield" value=""/>';
		echo '<input type="hidden" name="redirect" value="'.$redirect.'"/>';
		echo '<button type="submit" class="button">Go</button>';
		if($_GET['relogin']) echo '<a href="">Help, I forgot my password</a>';
	echo '</form>';

endif;

echo '</div>';	?>
