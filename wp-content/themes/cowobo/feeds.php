<?php
/*
Template Name: Cowobo Favourite feed
*/

require_once('lib/class-cowobo-feed.php');

//$args = array('sort' => 'ASC');

// Is this a userfeed?
if ( isset ( $wp_query->query_vars['userfeed'] ) && $userfeed = get_user_by ( 'slug', $wp_query->query_vars['userfeed'] ) ) {
	$args['users'] = $userfeed->ID;
} else {
    $args['cats'] = (isset($_GET['cats']))? $_GET['cats'] : false;
    $args['posts'] = (isset($_GET['posts']))? $_GET['posts'] : false;
    $args['users'] = (isset($_GET['user']))? $_GET['user'] : false;
}

$feed = new Cowobo_Feed($args);

$feedposts = $feed->get_feed();
$lastpost = $feed->last_post();

// Let's start parsing
header('Content-Type: ' . feed_content_type('rss-http') . '; charset=' . get_option('blog_charset'), true);
echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>'; ?>
<rss version="2.0">
	<channel>
		<title><?php echo $feed->feed_title; ?></title>
		<link>http://cowobo.org/</link>
		<description></description>
		<language>en-us</language>
		<pubDate><?php $feed->rss_date( strtotime($feedposts[$lastpost]->post_date_gmt) ); ?></pubDate>
		<lastBuildDate><?php $feed->rss_date( strtotime($feedposts[$lastpost]->post_date_gmt) ); ?></lastBuildDate>
		<managingEditor>contactus@cowobo.org</managingEditor>
<?php foreach ($feedposts as $post) { 
		$postcat = cwob_get_category($post->ID);?>
		<item>
			<title><?php echo $postcat->name.': '.$post->post_title; ?></title>
			<link><?php echo get_permalink($post->ID); ?></link>
			<description><?php $feed->add_image($post->ID); echo '<![CDATA['.$feed->rss_text_limit($post->post_content, 500).'<br/><br/><a href="'.get_permalink($post->ID).'">..read more</a>'.']]>';  ?></description>
			<pubDate><?php $feed->rss_date( strtotime($post->post_date_gmt) ); ?></pubDate>
			<guid><?php echo get_permalink($post->ID); ?></guid>
		</item>
<?php } ?>
	</channel>
</rss>