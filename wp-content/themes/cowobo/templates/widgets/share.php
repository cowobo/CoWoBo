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

//echo '<div class="tab">';
	echo '<h2>Spread the word &raquo;</h2>';
	if( isset ( $_POST['user_email'] ) ) echo 'Your message has been sent.';

    echo '<a class="fbbutton" href="'.$fburl.'">Share on Facebook</a>';
    echo '<a class="tweetbutton" target="_blank" href="'.$tweeturl.'">Tweet</a>';
    echo '<div class="fb-like" data-layout="button_count" data-width="250" data-show-faces="false" data-font="trebuchet ms"></div>';


	echo '<form method="post" action="">';
    echo '<div class="clear" style="margin-top:5px"></div>';
    echo '<div class="hide-if-js email-form">';
		echo '<textarea name="emailtext" rows="3" class="emailtext"></textarea>';
		echo '<input type="text" name="user" class="hide" value=""/>'; //spammer trap
		echo '<input type="input" name="user_firstname" placeholder="Your name"/>';
		echo '<input type="input" name="user_friends" placeholder="Email addresses separate by commas" />';
    echo '</div>';
    echo '<button type="submit" class="button email-form-toggle">Send Email</button>';
		//echo '</div>';
	echo '</form>';
    //echo '<div class="clear" style="margin-top:5px"></div>';
//echo '</div>';
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