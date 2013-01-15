<?php
global $cowobo;

if(is_single()) $description .= get_the_excerpt(); else $description .= get_bloginfo('description');
$pagelink = get_bloginfo('url').$_SERVER['REQUEST_URI'];
$fburl = 'https://www.facebook.com/dialog/feed?app_id=296002893747852';
$fburl .= '&link='.$pagelink;
$fburl .= '&name='.urlencode($cowobo->feed->feed_title(false));
$fburl .= '&caption='.urlencode('on Coders Without Borders');
$fburl .= '&%20description='.urlencode($description);
$fburl .= '&redirect_uri='.$pagelink;
$tweeturl = 'http://twitter.com/home?status='.urlencode('Check this out "http://www.cowobo.org/"');

echo '<div class="tab">';
	echo '<h2>Subscribe to this feed &raquo;</h2>';
	echo '<div class="rsslinks">';
		$cowobo->posts->print_rss_links();
	echo '</div>';
echo '</div>';

echo '<div class="tab">';
	echo '<h2>Share with friends &raquo;</h2>';
	if($_POST['user_email']) echo 'Your message has been sent.';
	echo '<form method="post" action="">';
        wp_nonce_field( 'sendemail', 'sendemail' );
		echo '<textarea name="emailtext" rows="3" class="emailtext"></textarea>';
		echo '<input type="text" name="user" class="hide" value=""/>'; //spammer trap
		echo '<input type="input" class="half" name="user_firstname" value="Your name" onfocus="this.value=\'\'"/>';
		echo '<input type="input" class="half" name="user_friends" value="Email addresses separate by commas" onfocus="this.value=\'\'"/>';
		echo '<div class="clear" style="margin-top:5px">';
		echo '<button type="submit" class="button">Send Email</button>';
		echo '<a class="fbbutton" href="'.$fburl.'">Share on Facebook</a>';
		echo '<a class="tweetbutton" target="_blank" href="'.$tweeturl.'">Tweet</a>';
		echo '<div class="fb-like" data-layout="button_count" data-width="250" data-show-faces="false" data-font="trebuchet ms"></div>';
		echo '</div>';
	echo '</form>';
echo '</div>';?>

<script>
	(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_GB/all.js#xfbml=1";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));
</script>