<?php
/**
 * We are doing this through BuddyPress?
 */
// Send notification to author when comment is posted
//
//function cwb_comment_notice($comment_id) {
//	global $wpdb;
//	//to do: check if user has email
//	$comment = get_comment($comment_id);
//	$post = get_post($comment->comment_post_ID);
//	$siteurl = get_option('siteurl');
//	$user = get_userdata($post->post_author);
//	$notify_message  = sprintf( __('A new comment on the post #%1$s "%2$s" is waiting for your approval'), $post->ID, $post->post_title ) . "\r\n";
//	$notify_message .= get_permalink($comment->comment_post_ID) . "\r\n\r\n";
//	$notify_message .= sprintf( __('Author : %1$s'), $comment->comment_author) . "\r\n";
//	$notify_message .= sprintf( __('E-mail : %s'), $comment->comment_author_email ) . "\r\n";
//	$notify_message .= __('Comment: ') . "\r\n" . $comment->comment_content . "\r\n\r\n";
//	$notify_message .= sprintf( __('Please visit the moderation panel:')) . "\r\n";
//	$notify_message .= "$siteurl/wp-admin/moderation.php\r\n";
//	$subject = sprintf( __('[%1$s] New Comment requires moderation'), get_option('blogname') );
//	$notify_message = apply_filters('comment_moderation_text', $notify_message, $comment_id);
//	$subject = apply_filters('comment_moderation_subject', $subject, $comment_id);
//	@wp_mail($user->user_email, $subject, $notify_message);
//	return true;
//}

/**
 * @todo Load our own RSS template?
 */
//include custom feed template
//remove_all_actions( 'do_feed_rss2' );
//add_action( 'do_feed_rss2', 'cwb_feed_rss2');
//
//function cwb_feed_rss2() {
//    $rss_template = get_template_directory() . '/feeds.php';
//    load_template( $rss_template );
//}
