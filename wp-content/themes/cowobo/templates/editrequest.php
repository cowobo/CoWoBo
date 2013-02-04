<?php

	$requests = get_post_meta(get_the_ID(), 'cwb_request', false);

	foreach($requests as $request):
		$rqdata = explode('|', $request);
		if($rqdata[0] == $GLOBALS['profile_id']):
			if($rqdata[1] == 'deny'):
				$denied = true;
				echo '<b>Your request for this post was denied</b>';
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
		if( ! isset ( $denied) || ! $denied ) echo 'Request to edit this post';
		echo '<form method="post" action="">';
		echo '<textarea name="requestmsg" rows="5">Dear Author, I would like to edit this post because..</textarea>';
		echo '<input type="hidden" name="requesttype" value="add"/>';
        wp_nonce_field( 'request', 'request' );
		echo '<button type="submit" class="button">Send Request</button>';
		echo '</form>';
	endif;