<?php 
if (have_posts()) : while (have_posts()) : the_post(); 
	$postcat = cwob_get_category($post->ID);
	$postid = get_the_ID();

	//include post title and data
	echo '<div class="tab">';
	echo '<div class="feedtitle">'.cwb_feed_title().'</div>';
	foreach($layouts->layout[$postcat->term_id] as $field):$index++;
		$slug = $field['type'].$index++;
		if($field['type'] == 'tags'):
			echo '<span class="field"><h3>'.$field['label'].':</h3>';
			foreach(get_the_category() as $cat): $tagcount++; 
				echo '<a href="'.get_category_link($cat->term_id).'">'.$cat->name.'</a>'; 
				if($tagcount < count(get_the_category())) echo ', ';
			endforeach;
			echo '</span>';
		elseif($field['type'] == 'dates'):
			echo '<span class="field"><h3>'.$field['label'].':</h3>';
			$startdate = get_post_meta(get_the_ID(), 'startdate', true);
			$enddate = get_post_meta(get_the_ID(), 'enddate', true);
			if($enddate) $date = $startdate.' to '.$enddate; else $date = $startdate;		
			if($date):
				echo $date;
			else:
				echo '<span class="hint">not specified</span>';
			endif;
			echo '</span>';
		elseif($field['type'] == 'website'):
			echo '<span class="field"><h3>'.$field['label'].':</h3>';
			if($value = get_post_meta(get_the_ID(), 'website', true)):
				$checkurl = parse_url($value);
				if (!isset($checkurl["scheme"])) $value = 'http://'.$value;
				$domain = str_replace('www.', '', parse_url($value, PHP_URL_HOST));
				echo '<a href="'.$value.'">'.$domain.'</a>';
			else: 
				echo '<span class="hint">not specified</span>';
			endif;
			echo '</span>';
		elseif($field['type'] == 'country'):
			echo '<span class="field"><h3>'.$field['label'].':</h3>';
			if($country = get_the_category()):
				echo '<a href="'.get_category_link($country[0]->term_id).'">'.$country[0]->name.'</a><br/>';
			else: 
				echo '<span class="hint">not specified</span>';
			endif;
			echo '</span>';
		elseif($field['type'] == 'smalltext'):
			echo '<span class="field"><h3>'.$field['label'].':</h3>';
			if($value = get_post_meta(get_the_ID(), $slug, true)):
				echo $value;
			else: 
				echo '<span class="hint">not specified</span>';
			endif;
			echo '</span>';
		elseif($field['type'] == 'checkboxes'):
			echo '<span class="field"><h3>'.$field['label'].':</h3>';
			if($values = get_post_meta(get_the_ID(), $slug.'-checked', false)):
				foreach(explode(',',$field['hint']) as $option): $counter++;
					$labels[$slug.$counter] = $option;
				endforeach;
				foreach($values as $value):
					$valuecat = get_cat_ID($labels[$value]);
					$titles[] = '<a href="'.get_category_link($valuecat->term_id).'">'.$labels[$value].'</a>';
				endforeach;
				echo implode(', ',$titles);
			else: 
				echo '<span class="hint">not specified</span>';
			endif;
			unset($counter);
			echo '</span>';
		elseif($field['type'] == 'dropdown'): unset($counter);
			echo '<span class="field"><h3>'.$field['label'].':</h3>';
			if($value = get_post_meta(get_the_ID(), $slug, true)):
				foreach(explode(',',$field['hint']) as $option): $counter++;
					$labels[$slug.$counter] = $option;
				endforeach;
				echo $labels[$value];
			else:
				echo '<span class="hint">not specified</span>';
			endif;
			echo '</span>';
		elseif($field['type'] == 'slogan'):
			if($value = get_post_meta(get_the_ID(), 'slogan', true)):
				echo '<b>'.$value.'</b>';
			endif;
		endif;
	endforeach;
	echo '</div>';
	
	//include gallery if post has images
	if($images = cwb_loadgallery($post->ID)):
		echo '<div class="tab">'.$images.'</div>';
	endif;
	
	//include main text if post has content
	if(get_the_content()):
		echo '<div class="tab">';
			echo apply_filters('the_content', cwb_the_content(get_the_ID()));
			if($translate) echo '<br/><a href="?action=correct">Correct this translation</a>';
			if($author) echo '<br/><a href="?action=editpost">Edit Page</a>';	
		echo '</div>';
	endif;
	
	//sort linked posts by type
	if($linkedids = $related->cwob_get_related_ids($postid)):
		foreach($linkedids as $linkedid):
			$typecat = cwob_get_category($linkedid);
			$excludecats = array(get_cat_ID('Uncategorized'));
			if($postcat->slug == 'coder' or $postcat->slug == 'location') $excludecats[] = $postcat->term_id;
			if($typecat && !in_array($typecat->term_id, $excludecats)):
				$types[$typecat->term_id][] = $linkedid;
			endif;
		endforeach;
	endif;
	
	//show linked posts
	if($types):
		foreach($types as $typeid => $typeposts):
			$tabcat = get_category($typeid);
			$tabposts = get_posts(array('post__in'=>$typeposts, 'numberposts'=>3));
			$tabtype = 'cat';
			include(TEMPLATEPATH.'/templates/tabs.php');
		endforeach;
	endif;
			
	if($author):
		echo '<div class="tabthumb right">+</div>';
		echo '<div class="tabtext left">';
			echo '<h2>Add posts to this page &raquo;</h2>';
			echo '<div class="horlist">';
				$exclude = get_cat_ID('Uncategorized').','.get_cat_ID('Coders').','.get_cat_ID('Partners').','.$postcat->term_id;
				foreach(get_categories('parent=0&exclude='.$exclude.'&hide_empty=0') as $cat):
					echo '<a href="?new='.$cat->name.'">'.$cat->name.'</a>';
				endforeach;
			echo '</div>';
			echo '<form method="post" action="">';					
				echo '<select name="linkto" class="smallfield">';
				echo '<option>Or link to your other posts:</option>';
				echo '<option></option>';
				foreach(get_posts('meta_key=author&meta_value='.$social->ID.'&numberposts=-1') as $userpost):
					echo '<option value="'.$userpost->ID.'">'.cwb_the_title($userpost->ID).'</option>';
				endforeach;
				echo '</select>';
				echo '<button type="submit" class="button">Add</button>';
			echo '</form>';
		echo '</div>';
	endif;

	//show comments
	comments_template();
endwhile;
endif;
?>