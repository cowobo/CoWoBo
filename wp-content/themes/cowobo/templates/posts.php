<?php

if (have_posts()) : while (have_posts()) : the_post();
	$postid = get_the_ID();
	$postcat = cowobo()->posts->get_category($postid);

	//include thumbnails
	echo cowobo()->posts->load_thumbs($post->ID);

	echo '<div class="posttitle">'.cowobo()->L10n->the_title($post->ID);
	    if ( ! cowobo()->users->is_profile() || $author ) {
			echo '<a class="feededit" href="?action=editpost">';
			echo ( $author ) ? '+edit' : "+contribute?";
			echo '</a>';
		}
	echo '</div>';

    if ( isset ( cowobo()->layouts->layout[$postcat->term_id] ) ) {

        $index = 0;
        echo '<div class="tab">';
        foreach(cowobo()->layouts->layout[$postcat->term_id] as $field): $index++;
            $slug = $field['type'].$index;
            if($field['type'] == 'tags'):
                echo '<span class="field"><h3>'.$field['label'].':</h3>';
                $tagcount = 0;
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

        do_action ( 'cowobo_after_layouts', $postid, $postcat, $author );

        echo '</div>';

    }

	//include main text if post has content
	if(get_the_content()):
		echo '<div class="tab">';
			echo apply_filters('the_content',  cowobo()->L10n->the_content(get_the_ID()));
			if($translate) echo '<br/><a href="?action=correct">Correct this translation</a>';
		echo '</div>';
	endif;

	//sort linked posts by type
	if($linkedids = cowobo()->relations->get_related_ids($postid)):
		foreach($linkedids as $linkedid):
			$typecat = cowobo()->posts->get_category($linkedid);
			$excludecats = array(get_cat_ID('Uncategorized'));
			if($postcat->slug == 'coder' or $postcat->slug == 'location') $excludecats[] = $postcat->term_id;
			if($typecat && !in_array($typecat->term_id, $excludecats)):
				$types[$typecat->term_id][] = $linkedid;
			endif;
		endforeach;
	endif;

	//show linked posts
	if(isset ( $types ) && is_array ( $types ) ):
		foreach($types as $typeid => $typeposts):
			$tabcat = get_category($typeid);
			$tabposts = get_posts(array('post__in'=>$typeposts, 'numberposts'=>3));
			$tabtype = 'cat';
			include(TEMPLATEPATH.'/templates/tabs.php');
		endforeach;
	endif;

	if($author) {
		echo '<div class="tabthumb right">+</div>';
		echo '<div class="tabtext left">';
			echo '<h2>Add posts to this page &raquo;</h2>';
			echo '<div class="horlist">';
				$exclude = get_cat_ID('Uncategorized').','.get_cat_ID('Coders').','.get_cat_ID('Partners').','.$postcat->term_id;
				foreach(get_categories('parent=0&exclude='.$exclude.'&hide_empty=0') as $cat):
					echo '<a href="?new=' .$cat->name.'">'.$cat->name.'</a>';
				endforeach;
			echo '</div>';
			echo '<form method="post" action="">';
				echo '<select name="linkto" class="smallfield">';
				echo '<option>Or link to your other posts:</option>';
				echo '<option></option>';
				foreach(get_posts('meta_key=author&meta_value='.$GLOBALS['profile_id'].'&numberposts=-1') as $userpost):
					echo '<option value="'.$userpost->ID.'">' . cowobo()->L10n->the_title($userpost->ID).'</option>';
				endforeach;
				echo '</select>';
                wp_nonce_field( 'linkposts' );
				echo '<button type="submit" class="button">Add</button>';
			echo '</form>';
		echo '</div>';
    }

    do_action ( 'cowobo_after_post', $postid, $postcat, $author );


	//show comments
    if ( ! $postcat->slug == 'coder' )
        comments_template();
endwhile;
endif;
?>