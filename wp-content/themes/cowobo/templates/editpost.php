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
				if($postcat->slug == 'location') $state = 'readonly'; else $state = '';
				echo '<input type="text" name="post_title" value="'.$post_title.'" '.$state.'/>';
			elseif($field['type'] == 'gallery'):
				cowobo()->posts->cwb_upload_form($postid, 3);
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
				$value = ( ! $unsaved_data ) ? get_post_meta($postid, 'cwb_involvement', true) : $query->cwb_involvement;
				$options = array(
					'none'=>'I am not currently involved in this project',
					'founder'=>'I founded this project',
					'employee'=>'I work for this project',
				);
				echo '<select name="cwb_involvement"><option value="none"></option>';
				foreach($options as $optionslug =>$option):
					if($value == $optionslug) $state = 'selected'; else $state = '';
					echo '<option value="'.$optionslug.'" '.$state.'> '.$option.'</option>';
				endforeach;
				echo '</select>';
			elseif($field['type'] == 'dates'):
				$startdate = ( ! $unsaved_data ) ? get_post_meta($postid, 'cwb_startdate', true) : $query->cwb_startdate;
				$enddate = ( ! $unsaved_data ) ? get_post_meta($postid, 'cwb_enddate', true) : $query->cwb_enddate;
				echo '<input tabindex="'.$index.'" type="text" name="cwb_startdate" class="lefthalf" value="'.$startdate.'" placeholder="Starting Date"/>';
				echo '<input tabindex="'.$index.'" type="text" name="cwb_enddate" class="righthalf" value="'.$enddate.'" placeholder="Ending Date"/>';
			elseif($field['type'] == 'website'):
				$websiteurl = ( ! $unsaved_data ) ? get_post_meta($postid, 'cwb_website', true) : $query->cwb_website;
				echo '<input tabindex="'.$index.'" type="text" name="cwb_website" class="blue bold" value="'.$websiteurl.'"/>';
				echo '<br/>';
			elseif($field['type'] == 'country'):
				$country = ( ! $unsaved_data ) ? get_post_meta($postid, 'cwb_country', true) : $query->cwb_country ;
				if( empty($country) ) $country = get_post_meta($postid, 'cwb_country', true);
				echo '<input type="text" tabindex="'.$index.'" name="cwb_country" value="'.$country.'"/>';
			elseif($field['type'] == 'location'):
				$location = ( ! $unsaved_data ) ? get_post_meta($postid, 'cwb_location', true) : $query->cwb_location;
				$map = ( ! $unsaved_data ) ? get_post_meta($postid, 'cwb_includemap', true) : $query->cwb_includemap;
				$street = ( ! $unsaved_data ) ? get_post_meta($postid, 'cwb_includestreet', true) : $query->cwb_includestreet;
				$zoom = ( ! $unsaved_data ) ? get_post_meta($postid, 'cwb_zoom', true) : $query->cwb_zoom;
				if( $street == 1 ) $streetstate = 'checked'; else $streetstate = '';
				if( $map == 1 ) $mapstate = 'checked'; else $mapstate = '';
				echo '<input type="text" class="lefthalf" tabindex="'.$index.'" name="cwb_location" value="'.$location.'"/>';
				echo '<ul class="righthalf horlist">';
					echo '<li><select name="cwb_zoom" class="auto">';
						echo '<option value="">Zoom</option>'; 
						for($x=3; $x<17; $x++):
							if($x == $zoom) $state = 'selected'; else $state='';
							echo '<option value="'.$x.'" '.$state.'>'.$x.'</option>'; 
						endfor;
					echo '</select></li>';
					echo '<li><input type="checkbox" class="auto" tabindex="'.$index.'" name="cwb_includemap" value="1" '.$mapstate.'/> Map</li>';
					echo '<li><input type="checkbox" class="auto" tabindex="'.$index.'" name="cwb_includestreet" value="1" '.$streetstate.'/> Streetview</li>';
				echo '</ul>';
			elseif($field['type'] == 'smalltext'):
				$value = ( ! $unsaved_data ) ? get_post_meta($postid, "cwb_" . $slug, true) : $query->{"cwb_$slug"};
				echo '<input type="text" tabindex="'.$index.'" name="cwb_'.$slug.'" value="'.$value.'"/>';
			elseif($field['type'] == 'checkboxes'):
				$options = explode(',', $field['hint']);
	            $slug_checked = "cwb_$slug-checked";
				$values = ( ! $unsaved_data ) ? get_post_meta($postid, $slug_checked, false) : $query->$slug_checked;
				if($values == false) $values = array();
				unset($counter);
				echo '<ul class="horlist box">';
	            $counter = 0;
				foreach($options as $option): $counter++;
					if(in_array($slug.$counter, $values)) $state = 'checked'; else $state = '';
					echo  '<li><input type="checkbox" name="cwb_'.$slug.'-checked[]" value="'.$slug.$counter.'" '.$state.'/>'.$option.'</li>';
				endforeach;
				echo '</ul>';
			elseif($field['type'] == 'dropdown'):
				$options = explode(',',$field['hint']); unset($counter);
				echo '<select name="cwb_'.$slug.'"><option></option>';
	            $value = ( ! $unsaved_data ) ? get_post_meta($postid, "cwb_" . $slug, true) : $query->{"cwb_".$slug};
				foreach($options as $option): $counter++;
					if($value == $slug.$counter) $state = 'selected'; else $state = '';
					echo '<option value="'.$slug.$counter.'" '.$state.'> '.$option.'</option>';
				endforeach;
				echo '</select>';
			elseif($field['type'] == 'slogan'):
				//if($error = $postmsg['slogan']) echo '<span class="red bold">'.$error.'</span>';
				echo '<span class="hint">'.$field['hint'].'</span><br/>';
				$value = ( ! $unsaved_data ) ? get_post_meta($postid, 'cwb_slogan', true) : $query->cwb_slogan;
				echo '<input type="text" tabindex="'.$index.'" name="cwb_slogan" value="'.$value.'"/>';
			elseif($field['type'] == 'largetext'):
				if ( ! $unsaved_data ) {
	                //$thispost = get_post($postid);
	                $post_content = apply_filters('the_content', $post->post_content);
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
				echo '<div id="rte" contenteditable="true" onpaste="tag_cleanup_listener(this, event)" unselectable="off" tabindex="'.$index.'" class="richtext">'.trim ( $post_content ).'</div>';
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