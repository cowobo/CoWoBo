<?php
global $post, $currlang;
$query = cowobo()->query;
$link_to = 0;

if ( $query->new ) {
    $postid = ( isset ( $GLOBALS['newpostid'] ) && $newpostid = $GLOBALS['newpostid']  ) ? $newpostid : 0;
    if ( is_single() ) $link_to = get_the_ID();

    // Set the category
    $postcat = get_category ( get_cat_ID( $query->new ) );

    // Create dummy post
    $post = new stdClass;
    $post->ID = 0;
    $post->post_content = '';
    $post->post_category = $postcat->term_id;

    // Should we insert query data?
    $unsaved_data = ( ( $query->url || $query->save ) && ! cowobo()->has_notice ( 'saved' ) ) ? true : false;

} else {
    if( $query->post_ID ) $postid = $query->post_ID;
    elseif(! isset ( $postid ) || ! $postid ) $postid = $post->ID;

    $postcat = cowobo()->posts->get_category($postid);
    $unsaved_data = ( cowobo()->has_notice( array ( 'savepost', 'saved' ) ) ) ? true : false;
}

//if user is not author show become editor screen
if( ! isset ( $author ) || ! $author ):
	echo "<h2>You are not an author of this post yet &raquo;</h2>";
	include( TEMPLATEPATH . '/templates/editrequest.php');
else:

if( ! cowobo()->has_notice( array ( 'savepost', 'saved' ) ) ) {
    echo '<div class="tab">';
            echo '<b>Please enter all text in ';
            echo '<a href="http://translate.google.com/translate?hl='.$currlang.'&sl='.$currlang.'&tl=en" target="_blank" title="Use Google Translate">English </a>';
            echo 'so we can translate it to the other languages on our site.</b><br/>';
            echo 'After saving you can correct the translations using the Translate This Page box';
    echo '</div>';
}

cowobo()->print_notices( 'savepost', 'error' );
cowobo()->print_notices( 'saved' );


echo '<form method="post" action="" enctype="multipart/form-data">';
	echo '<input type="hidden" name="postcat" value="' . $postcat->term_id . '">';
	if ( isset ( $GLOBALS['newpostid'] ) && $newpostid = $GLOBALS['newpostid']  )
	    echo '<input type="hidden" name="postid" value="' . $newpostid . '">';
	if(cowobo()->layouts->layout[$postcat->term_id]):
	    $index = 0;
		foreach(cowobo()->layouts->layout[$postcat->term_id] as $field): $index++;
			$slug = $field['type'].$index++;
			echo '<div class="tab">';
			echo '<h3>'.$field['label'].':';
				//if($error = $postmsg[$field['type']]) echo '<span class="red bold">'.$error.'</span>';
				if($field['type'] == 'checkboxes') echo '<span class="hint">Select those which apply</span><br/>';
				elseif($field['type'] == 'dropdown') echo '<span class="hint">Choose one from the dropdown menu</span><br/>';
				elseif($field['type'] == 'largetext') echo '<span class="hint">Enter or paste in text here</span>';
				else echo '<span class="hint">'.$field['hint'].'</span>';
			echo '</h3>';
			if($field['type'] == 'title'):
				$post_title = ( ! $unsaved_data ) ? get_the_title($postid) : $query->post_title;
				echo '<input type="text" name="post_title" value="'.$post_title.'"/>';
			elseif($field['type'] == 'gallery'): unset($thumbs);
				echo '<div class="headerrow">';
					echo '<div class="thumbcol">Thumb</div>';
					echo '<div class="browsecol">Browse for new image</div>';
					echo '<div class="urlcol">or URL of image/youtube video</div>';
					echo '<div class="deletecol"><b>Delete</b></div>';
				echo '</div>';
				for ($x=0; $x<3; $x++):
					if($imgid = get_post_meta($postid, 'imgid'.$x, true)):
						$thumb = wp_get_attachment_image($imgid, $size = 'thumbnail');
					else:
						$imgid = 0; $thumb = '';
					endif;
	                $url_id = "cwb_url$x";
	                if ( $unsaved_data ) {
	                    $imgurl =  $query->$url_id;
	                    if ( cowobo()->posts->is_image_url ( $imgurl ) )
	                        $thumb = "<div style='background: url(\"$imgurl\") no-repeat 50% 50%;background-size: cover;width:40px; height:30px;'></div>";
	                }
	                else
	                    $imgurl =  get_post_meta( $postid, $url_id, true );
					echo '<div class="imgrow">';
						echo '<div class="thumbcol">'.$thumb.'</div>';
						echo '<div class="browsecol"><input type="file" class="full" name="file'.$x.'"></div>';					
						echo '<div class="urlcol"><input type="text" name="cwb_url'.$x.'" class="full" value="'. $imgurl .'"/></div>';
						echo '<div class="deletecol"><input type="checkbox" class="full" name="delete'.$x.'" value="1"><input type="hidden" name="imgid'.$x.'" value="'.$imgid.'"/></div>';
					echo '</div>';
				endfor;
			elseif($field['type'] == 'tags'):
	            if ( ! $unsaved_data ) {
	                $tags = array();
	                foreach(get_the_category($postid) as $cat):
	                    if($cat->term_id != $postcat->term_id) $tags[] = $cat->name;
	                endforeach;
	                $tags = implode(', ', $tags);
	            } else {
	                $tags = $query->tags;
	            }
				echo '<input type="text" name="tags" value="'.$tags.'"/>';
			elseif($field['type'] == 'involvement'):
				$value = ( ! $unsaved_data ) ? get_post_meta($postid, 'involvement', true) : $query->involvement;
				$options = array(
					'none'=>'I am not currently involved in this project',
					'founder'=>'I founded this project',
					'employee'=>'I work for this project',
				);
				echo '<select name="involvement"><option value="none"></option>';
				foreach($options as $optionslug =>$option):
					if($value == $optionslug) $state = 'selected'; else $state = '';
					echo '<option value="'.$optionslug.'" '.$state.'> '.$option.'</option>';
				endforeach;
				echo '</select>';
			elseif($field['type'] == 'dates'):
				$startdate = ( ! $unsaved_data ) ? get_post_meta($postid, 'startdate', true) : $query->startdate;
				$enddate = ( ! $unsaved_data ) ? get_post_meta($postid, 'enddate', true) : $query->enddate;
				echo '<input tabindex="'.$index.'" type="text" name="startdate" class="lefthalf" value="'.$startdate.'" placeholder="Starting Date"/>';
				echo '<input tabindex="'.$index.'" type="text" name="enddate" class="righthalf" value="'.$enddate.'" placeholder="Ending Date"/>';
			elseif($field['type'] == 'website'):
				$websiteurl = ( ! $unsaved_data ) ? get_post_meta($postid, 'website', true) : $query->website;
				echo '<input tabindex="'.$index.'" type="text" name="website" class="blue bold" value="'.$websiteurl.'"/>';
				echo '<br/>';
			elseif($field['type'] == 'email'):
				$email = ( ! $unsaved_data ) ? get_post_meta($postid, 'email', true) : $query->email;
				echo '<input tabindex="'.$index.'" type="text" name="email" class="blue bold" value="'.$email.'"/>';
				echo '<br/>';
			elseif($field['type'] == 'country'):
				$cat = ( ! $unsaved_data ) ? get_post_meta($postid, 'country', true) : $query->country ;
				echo '<input type="text" class="" tabindex="'.$index.'" name="country" value="'.$cat->name.'"/>';
				echo '<br/>';
			elseif($field['type'] == 'location'):
				$location = ( ! $unsaved_data ) ? get_post_meta($postid, 'location', true) : $query->location;
				$map = ( ! $unsaved_data ) ? get_post_meta($postid, 'cwb_includemap', true) : $query->cwb_includemap;
				$street = ( ! $unsaved_data ) ? get_post_meta($postid, 'cwb_includestreet', true) : $query->cwb_includestreet;
				if($street==1) $streetstate = 'checked'; else $streetstate = '';
				if($map==1) $mapstate = 'checked'; else $mapstate = '';
				echo '<input type="text" class="lefthalf" tabindex="'.$index.'" name="location" value="'.$location.'"/>';
				echo '<input type="checkbox" class="auto" tabindex="'.$index.'" name="cwb_includemap" value="1" '.$mapstate.'/> Show Map';
				echo '<input type="checkbox" class="auto" tabindex="'.$index.'" name="cwb_includestreet" value="1" '.$streetstate.'/> Show Streetview';
			elseif($field['type'] == 'smalltext'):
				$value = ( ! $unsaved_data ) ? get_post_meta($postid, $slug, true) : $query->$slug;
				echo '<input type="text" tabindex="'.$index.'" name="'.$slug.'" value="'.$value.'"/>';
			elseif($field['type'] == 'checkboxes'):
				$options = explode(',', $field['hint']);
	            $slug_checked = "$slug-checked";
				$values = ( ! $unsaved_data ) ? get_post_meta($postid, $slug.'-checked', false) : $query->$slug_checked;
				if($values == false) $values = array();
				unset($counter);
				echo '<ul class="horlist box">';
	            $counter = 0;
				foreach($options as $option): $counter++;
					if(in_array($slug.$counter, $values)) $state = 'checked'; else $state = '';
					echo  '<li><input type="checkbox" name="'.$slug.'-checked[]" value="'.$slug.$counter.'" '.$state.'/>'.$option.'</li>';
				endforeach;
				echo '</ul>';
			elseif($field['type'] == 'dropdown'):
				$options = explode(',',$field['hint']); unset($counter);
				echo '<select name="'.$slug.'"><option></option>';
	            $value = ( ! $unsaved_data ) ? get_post_meta($postid, $slug, true) : $query->$slug;
				foreach($options as $option): $counter++;
					if($value == $slug.$counter) $state = 'selected'; else $state = '';
					echo '<option value="'.$slug.$counter.'" '.$state.'> '.$option.'</option>';
				endforeach;
				echo '</select>';
			elseif($field['type'] == 'slogan'):
				//if($error = $postmsg['slogan']) echo '<span class="red bold">'.$error.'</span>';
				echo '<span class="hint">'.$field['hint'].'</span><br/>';
				$value = ( ! $unsaved_data ) ? get_post_meta($postid, 'slogan', true) : $query->slogan;
				echo '<input type="text" tabindex="'.$index.'" name="slogan" value="'.$value.'"/>';
			elseif($field['type'] == 'largetext'):
				if ( ! $unsaved_data ) {
	                //$thispost = get_post($postid);
	                $post_content = $post->post_content;
	            } else {
	                $post_content = $query->post_content;
	            }
				echo '<div class="richbuttons">';
					echo '<a class="makebold" href="#">Bold</a>';
					echo '<a class="makeitalic" href="#">Italic</a>';
					echo '<a class="makeunderline" href="#">Underline</a>';
					echo '<a class="makelink" href="#">Link</a>';
					echo '<a class="htmlmode" href="#">HTML</a>';
					echo '<a class="richmode" href="#">WYSIWYG</a>';
				echo '</div>';
				echo '<div id="rte" contenteditable="true" unselectable="off" tabindex="'.$index.'" class="richtext">'.trim ( $post_content ).'</div>';
				echo '<textarea name="post_content" rows="12" class="htmlbox"></textarea>';
			endif;
			echo '</div>';
	
	        cowobo()->print_notices( $field['type'], 'error' );
	
		endforeach;
	
	    cowobo()->print_notices( 'confirmenglish', 'error' );
	
		echo '<div class="tab">';
			$state = ($query->new) ? '' : 'checked="checked"';
			echo '<input type="checkbox" class="auto" name="confirmenglish" value="1" '.$state.'"/> I confirm all text has been added in English.';
			echo '<br/>';
			echo '<a class="button" href="'.get_permalink($postid).'">Cancel</a>';
			echo '<input type="hidden" name="post_ID" value="'.$postid.'"/>';
			if ( $postcat->slug != 'coder' || $postcat->slug != 'location')
	            echo '<button type="submit" class="button" value="' .wp_create_nonce( 'delete' ). '" name="delete">Delete</button>';
	        wp_nonce_field( 'save', 'save' );
	        if ( $link_to ) echo "<input type='hidden' name='link_to' value='$link_to'>";
			echo '<button id="formsubmit" type="submit" class="button submitform">Save</button>';
			echo '<span class="loadicon"></span>';
		echo '</div>';
	endif;
echo '</form>';

endif;