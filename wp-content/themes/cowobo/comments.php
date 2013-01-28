<?php
// ##########  Do not delete these lines
if (isset($_SERVER['SCRIPT_FILENAME']) && 'comments.php' == basename($_SERVER['SCRIPT_FILENAME'])){
	die ('Please do not load this page directly. Thanks!'); }
if ( post_password_required() ) { ?>
	<p class="nocomments"><?php _e('This post is password protected. Enter the password to view comments.', 'kubrick'); ?></p>
<?php
	return; }
// ##########  End do not delete section


if(!is_user_logged_in()):
	$redirect = 'comment'; include(TEMPLATEPATH.'/templates/login.php');
else:
	echo '<div class="tab">';
		echo '<h2>Comments &raquo;</h2>';
		comment_form(array(
		  'title_reply' => '',
		  'logged_in_as'=> '',
		  'comment_field' => '<textarea name="comment" id="comment" rows="4" tabindex="4"></textarea>',
		  'label_submit' => 'Submit Comment',
		  'comment_notes_after' => '',
		 ));
	echo '</div>';
endif;

// Calback for comments (display)
if (!function_exists('cowobo_comments')) {
	function cowobo_comments($comment, $args, $depth) {
		global $author, $profile_id;
		$GLOBALS['comment'] = $comment;
		$commentmeta = get_comment_meta($comment->comment_ID, 'privatemsg', true);
		$userprofile = get_post($profile_id);?>
		<li id="comment-<?php comment_ID();?>">
			<div id="div-comment-<?php comment_ID() ?>" class="comment-body"><?php
			echo '<a href="'.get_permalink($userprofile->ID).'">'.$userprofile->post_title.'</a>';?>
			<div class="commenttext">
			<?php comment_text();?>
				<div class="commentbuttons">
					<?php comment_reply_link(array_merge( $args, array('add_below' => 'div-comment', 'depth' => $depth,)));
					if($author):?>
						<a class="editmsg ajax" href="#">Edit</a>
						<a class="deletemsg ajax" href="#">Delete</a><?php
					endif;?>
					<a href="?time="><?php echo cwb_time_passed(strtotime($comment->comment_date));?></a>
				</div>
			</div>
			</div>
		</li><?php
	}
};


// Display Comments Section
if ( have_comments() ) :
	echo '<div class="tab">';
		echo '<ol class="commentlist">'; wp_list_comments('callback=cowobo_comments'); echo '</ol>';
		echo '<div class="navigation">';
			echo '<div class="alignleft">'; previous_comments_link(); echo '</div>';
			echo '<div class="alignright">'; next_comments_link(); echo '</div>';
		echo '</div>';
	echo '</div>';
endif;

paginate_comments_links();

?>