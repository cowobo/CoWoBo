<?php

//check if the user has any pending author requests
$requestposts = get_posts(array('meta_query'=>array(array('key'=>'author', 'value'=>$GLOBALS['profile_id']), array('key'=>'request')), ));

if($requestposts):
	foreach($requestposts as $requestpost):
		$requests = get_post_meta($requestpost->ID, 'request', false);
		foreach($requests as $request):
			$requestdata = explode('|', $request);
			if($requestdata[1] != 'deny'):
				$profile = get_post($requestdata[0]);
				$msg .= '<form method="post" action="">';
				$msg .= '<a href="'.get_permalink($profile->ID).'">'.$profile->post_title.'</a> sent you a request for ';
				$msg .= '<a href="'.get_permalink($requestpost->ID).'">'.$requestpost->post_title.'</a>:<br/> '.$requestdata[1].'<br/>';
				$msg .= '<input type="hidden" name="requestuser" value="'.$requestdata[0].'"/>';
				$msg .= '<input type="hidden" name="requestpost" value="'.$requestpost->ID.'"/>';
				$msg .= '<ul class="horlist">';
				$msg .= '<li><input type="radio" name="requesttype" value="accept" selected="selected"/>Accept</li>';
				$msg .= '<li><input type="radio" name="requesttype" value="deny"/>Deny</li>';
                $msg .= wp_nonce_field( 'request', 'request' );
				$msg .= '<li><input type="submit" class="auto" value="Update"/></li>';
				$msg .= '</ul>';
				$msg .= '</form>';
			endif;
		endforeach;
	endforeach;
endif;

//check if the user sent an email
if($_POST['emailmsg']):
 	$msg .= 'Your email has been sent. We will get back to you shortly!';
endif;

//TODO
//check if user has setup an email and explain why its needed
//check for unread comments
//check if its the user's first time
//check for any pending job applications

//if there are notifcations display the notification box
if(!empty($msg)) echo '<div class="tab">'.$msg.'</div>';