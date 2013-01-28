<?php
if(is_user_logged_in()):
	//check if the user has already requested
	$requests = get_post_meta(get_the_ID(), 'request', false);

	echo '<div class="tab">';
	
	foreach($requests as $request):
		$rqdata = explode('|', $request);
		if($rqdata[0] == $GLOBALS['profile_id']):
			if($rqdata[1] == 'deny'):
				$denied = true;
				echo '<h2>Your request for this post was denied</h2>';
				echo 'You can try to convince the author again by submitting a new request:';
			else:
				$pending = true;
				echo '<h2>Your request for this post is still pending</h2>';
				echo 'If the author is refusing to respond please contact us instead.';
				echo '<form method="post" action="">';
				echo '<input type="hidden" name="requesttype" value="cancel"/>';
                wp_nonce_field( 'request', 'request' );
				echo '<button type="submit" class="button">Cancel Request</button>';
				echo '</form>';
			endif;
		endif;
	endforeach;

	if( ! isset ( $pending ) || ! $pending ):
		if( ! isset ( $denied) || ! $denied ) echo '<h2>Request to edit "'.$post->post_title.'"</h2>';
		echo '<form method="post" action="">';
		echo '<textarea name="requestmsg" rows="5">Dear Author, I would like to edit this post because..</textarea>';
		echo '<input type="hidden" name="requesttype" value="add"/>';
        wp_nonce_field( 'request', 'request' );
		echo '<button type="submit" class="button">Send Request</button>';
		echo '</form>';
	endif;

	echo '</div>';

else:
	$redirect='edit'; include( TEMPLATEPATH . '/templates/login.php');
endif;