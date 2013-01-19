<?php
global $cowobo, $post, $currlang;
$query = &$cowobo->query;

if( $query->post_ID ) $postid = $query->post_ID;
elseif(! isset ( $postid ) || ! $postid ) $postid = $post->ID;

$postcat = $cowobo->posts->get_category($postid);

//if user is not author show become editor screen
if( ! isset ( $author ) || ! $author ):
	echo "<h2>You are not an author of this post yet &raquo;</h2>";
	include( TEMPLATEPATH . '/templates/editrequest.php');
else:

echo '<div class="feedtitle">'. $cowobo->feed->feed_title() .'</div>';
echo '<img class="angel angel3" src="'.get_bloginfo('template_url').'/images/angel3.png" alt=""/>';						
echo '<img class="angel angel4" src="'.get_bloginfo('template_url').'/images/angel1.png" alt=""/>';
echo '<img class="angel angel5" src="'.get_bloginfo('template_url').'/images/angel2.png" alt=""/>';												
		
echo '<div class="tab">';
	$cowobo->print_notices( array ( 'savepost', 'saved' ) );
	$unsaved_data = ( $cowobo->has_notice( 'savepost' ) ) ? true : false;
	if( ! $cowobo->has_notice( array ( 'savepost', 'saved' ) ) ) {
		echo '<b>Please enter all text in ';
		echo '<a href="http://translate.google.com/translate?hl='.$currlang.'&sl='.$currlang.'&tl=en" target="_blank" title="Use Google Translate">English </a>';
		echo 'so we can translate it to the other languages on our site.</b><br/>';
		echo 'When you view the page in another language you can then click on <b>Correct Translation.</b>';
	}
echo '</div>';

echo '<form method="post" action="" enctype="multipart/form-data">';
if($cowobo->layouts->layout[$postcat->term_id]):
    $index = 0;
	foreach($cowobo->layouts->layout[$postcat->term_id] as $field): $index++;
		$slug = $field['type'].$index++;
		echo '<div class="tab">';
		echo '<h3>'.$field['label'].':';
			//if($error = $postmsg[$field['type']]) echo '<span class="red bold">'.$error.'</span>';
			if($field['type'] == 'checkboxes') echo '<span class="hint">Select those which apply</span><br/>';
			elseif($field['type'] == 'dropdown') echo '<span class="hint">Choose one from the dropdown menu</span><br/>';
			elseif($field['type'] == 'largetext') echo '<ul class="horlist right"><li class="makelink blue bold">Add Links</li><li class="makebold bold">Bold text</li></ul>';
			else echo '<span class="hint">'.$field['hint'].'</span>';
		echo '</h3>';
		if($field['type'] == 'title'):
			$post_title = get_the_title($postid);
			echo '<input type="text" name="post_title" value="'.$post_title.'"/>';
		elseif($field['type'] == 'gallery'): unset($thumbs);
			echo '<div class="headerrow">';
				echo '<div class="thumbcol">Thumb</div>';
				echo '<div class="captioncol">Caption or Youtube URL</div>';
				echo '<div class="browsecol">New image</div>';
				echo '<div class="deletecol">Delete</div>';
			echo '</div>';
			for ($x=0; $x<5; $x++):
				if($imgid = get_post_meta($postid, 'imgid'.$x, true)):
					$thumb = wp_get_attachment_image($imgid, $size = 'thumbnail');
				else:
					$imgid = 0; $thumb = '';
				endif;
				echo '<div class="imgrow">';
					echo '<div class="thumbcol">'.$thumb.'</div>';
					echo '<div class="captioncol"><input type="text" name="caption'.$x.'" class="full" value="'.get_post_meta($postid, 'caption'.$x, true).'"/></div>';
					echo '<div class="browsecol"><input type="file" class="full" name="file'.$x.'"></div>';
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
			echo '<input tabindex="'.$index.'" type="text" name="startdate" class="half left" value="'.$startdate.'"/>';
			echo '<input tabindex="'.$index.'" type="text" name="enddate" class="half right" value="'.$enddate.'"/>';
		elseif($field['type'] == 'website'):
			$websiteurl = ( ! $unsaved_data ) ? get_post_meta($postid, 'website', true) : $query->website;
			echo '<input tabindex="'.$index.'" type="text" name="website" class="blue bold" value="'.$websiteurl.'"/>';
			echo '<br/>';
		elseif($field['type'] == 'email'):
			$email = ( ! $unsaved_data ) ? get_post_meta($postid, 'email', true) : $query->email;
			echo '<input tabindex="'.$index.'" type="text" name="email" class="blue bold" value="'.$email.'"/>';
			echo '<br/>';
		elseif($field['type'] == 'country'):
			$cat = ( ! $unsaved_data ) ? get_the_category($postid) : $query->country;
			echo '<select name="country" class="full">';
			echo '<option></option>';
			foreach(get_categories('parent='.get_cat_ID('Locations').'&orderby=name&hide_empty=0') as $country):
				if($cat[0]->term_id == $country->term_id) $state = 'selected'; else $state = '';
				echo '<option value="'.$country->term_id.'" '.$state.'> '.$country->name.'</option>';
			endforeach;
			echo '</select>';
			echo '<br/>';
		elseif($field['type'] == 'location'):
			$city = ( ! $unsaved_data ) ? get_post_meta($postid, 'city', true) : $query->city;
			$countryid = ( ! $unsaved_data ) ? get_post_meta($postid, 'country', true) : $query->country;
			$zoomlevel = ( ! $unsaved_data ) ? get_post_meta($postid, 'zoomlevel', true) : $query->zoomlevel;
			echo '<div style="overflow:hidden">';
			echo '<div class="half"><input type="text" tabindex="'.$index.'" name="city" value="'.$city.'"/></div>';
			echo '<div class="half">';
			echo '<select tabindex="'.$index.'" name="country" class="country left">';
				echo '<option></option>';
				foreach(get_categories('parent='.get_cat_ID('Locations').'&orderby=name') as $country):
					if($countryid == $country->term_id) $state = 'selected'; else $state = '';
					echo '<option value="'.$country->term_id.'" '.$state.'> '.$country->name.'</option>';
				endforeach;
			echo '</select>';
			echo '<select tabindex="'.$index.'" name="zoomlevel" class="zoomlevel">';
				echo '<option></option>';
				for($x=3; $x<17; $x++):
					if($x == $zoomlevel) $state="selected"; else $state='';
					echo '<option value="'.$x.'" '.$state.'>'.($x-2).'</option>';
				endfor;
				echo '<option value="14">Max Zoom</option>';
			echo '</select>';
			echo '</div>';
			echo '</div>';
		elseif($field['type'] == 'encpath'):
			//echo '<a href="?action=encodepath">'.$field['hint'].'</a><br/>';
			$value = ( ! $unsaved_data ) ? get_post_meta($postid, 'encpath', true) : $query->encpath;
			echo '<input type="text" tabindex="'.$index.'" name="encpath" value="'.$value.'"/>';
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
                $thispost = get_post($postid);
                $post_content = $thispost->post_content;
            } else {
                $post_content = $query->post_content;
            }
			//hide extra formating so its easier to edit
			$stripped = str_replace(array('<br/>','</p>'), '\n', $post_content);
			$stripped = str_replace('<p>', '', $stripped);
			echo '<textarea tabindex="'.$index.'" name="post_content" rows="12" class="richtext">'.$stripped.'</textarea>';
		endif;
		echo '</div>';

        $cowobo->print_notices( $field['type'] );

	endforeach;

	echo '<div class="tab">';
		$state = ($query->new) ? '' : 'checked="checked"';
		echo '<input type="checkbox" class="auto" name="confirmenglish" value="1" '.$state.'"/> I confirm all text has been added in English.';
		echo '<br/>';
		echo '<a class="button" href="'.get_permalink($postid).'">Cancel</a>';
		echo '<a class="button" href="'.get_bloginfo('url').'?delete=' . wp_create_nonce( 'delete' ). '&id='.$postid.'">Delete</a>';
		echo '<input type="hidden" name="post_ID" value="'.$postid.'"/>';
        wp_nonce_field( 'save', 'save' );
		echo '<button type="submit" class="button">Save</button>';
		echo '<span class="loadicon"></span>';
	echo '</div>';
endif;
echo '</form>';

endif;