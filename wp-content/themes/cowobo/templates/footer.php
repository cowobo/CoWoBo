<?php
global $cowobo;

$tweeturl = 'http://twitter.com/home?status='.urlencode('Check this out "http://www.cowobo.org/"');


//$cowobo->posts->print_rss_links();

echo '<div class="footer">';
		
		echo '<a href="'.home_url().'">Home</a>';
		echo '<a href="?action=contact">Contact</a>';
		if(is_user_logged_in()):
			echo '<a href="'.get_permalink($profile_id).'">Your Profile</a>';
		else:
			echo '<a href="?action=login'.'">Your Profile</a>';
		endif;
		
		echo '<a href="?action=rss">RSS</a>';
		echo '<a href="?action=translate'.'">English (UK)</a>';
		if(is_user_logged_in()):
			echo '<a href="'.wp_logout_url(get_bloginfo('url')).'">Logout</a>';
		else: 
			echo '<a href="?action=login'.'">Login</a>';				
		endif;
		
		$url="http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		echo '<br/><div class="fb-like" data-href="'.$url.'" data-send="false" data-layout="button_count" data-width="450" data-show-faces="false"></div>';
		//echo '<a href="'.$tweeturl.'">Twitter</a>';
		
echo '</div>';

//include share forms below feeds
echo '<div class="hide">';
	echo '<h2>Share with friends &raquo;</h2>';
	if ( $cowobo->query->user_email ) echo 'Your message has been sent.';
	echo '<form method="post" action="">';
        wp_nonce_field( 'sendemail', 'sendemail' );
		echo '<textarea name="emailtext" rows="3" class="emailtext"></textarea>';
		echo '<input type="text" name="user" class="hide" value=""/>'; //spammer trap
		echo '<input type="input" class="half left" name="user_firstname" value="Your name" onfocus="this.value=\'\'"/>';
		echo '<input type="input" class="half right" name="user_friends" value="Email addresses separate by commas" onfocus="this.value=\'\'"/>';
		echo '<div class="clear" style="margin-top:5px">';
			echo '<button type="submit" class="button">Send Email</button>';
		echo '</div>';
	echo '</form>';
echo '</div>';?>

<div id="fb-root"></div>

<script>
	(function(d, s, id) {
	  var js, fjs = d.getElementsByTagName(s)[0];
	  if (d.getElementById(id)) return;
	  js = d.createElement(s); js.id = id;
	  js.src = "//connect.facebook.net/en_GB/all.js#xfbml=1";
	  fjs.parentNode.insertBefore(js, fjs);
	}(document, 'script', 'facebook-jssdk'));
</script>