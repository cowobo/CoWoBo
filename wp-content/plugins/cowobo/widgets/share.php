<?php

//if(is_single()) $description .= get_the_excerpt(); else $description .= get_bloginfo('description');
$pagelink = get_bloginfo('url').$_SERVER['REQUEST_URI'];
$fburl = 'https://www.facebook.com/dialog/feed?app_id=296002893747852';
$fburl .= '&link='. urlencode ( get_bloginfo( 'url' ) );
$fburl .= '&name='.urlencode("Coders Without Borders");
//$fburl .= '&caption='.urlencode('on Coders Without Borders');
$fburl .= '&description='.urlencode( get_bloginfo( 'description' ) );
$fburl .= '&redirect_uri='.$pagelink;
$tweeturl = 'http://twitter.com/home?status='.urlencode('Check this out "http://www.cowobo.org/"');

	if( isset ( $_POST['user_email'] ) ) echo 'Your message has been sent.';

	echo '<form method="post" action="">';
		echo '<input type="text" name="user" class="hide" value=""/>'; //spammer trap
		echo '<input type="input" name="user_firstname" placeholder="Your name"/>';
		echo '<input type="input" name="user_friends" placeholder="Friend\'s addresses separate by commas" />';
   		echo '<input type="submit" class="button sendemail" value="Send Email">';
		echo '<a class="fbbutton" href="'.$fburl.'">Facebook</a>';
    	echo '<a class="tweetbutton" target="_blank" href="'.$tweeturl.'">Twitter</a>';
	echo '</form>';
	
	echo '<div class="fb-like" data-layout="button_count" data-width="250" data-show-faces="false" data-font="trebuchet ms"></div>';

?>

<script>
	(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_GB/all.js#xfbml=1";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));
</script>