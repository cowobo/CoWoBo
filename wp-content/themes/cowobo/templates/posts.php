<?php

if (have_posts()) : while (have_posts()) : the_post();
	$postid = get_the_ID();
	$postcat = cowobo()->posts->get_category($postid);

	if($postcat->slug != 'coder' && $postcat->slug != 'location') {
		echo '<div class="posttitle">'.cowobo()->L10n->the_title($post->ID).'</div>';
	}

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
            do_action ( 'cowobo_before_postcontent' );
			echo apply_filters('the_content',  cowobo()->L10n->the_content(get_the_ID()));
			if( isset ( $translate ) && $translate ) echo '<br/><a href="?action=correct">Correct this translation</a>';
		echo '</div>';
	endif;

	//sort linked posts by type
	if($linkedids = cowobo()->relations->get_related_ids($postid)):
		foreach($linkedids as $linkedid):
			$typecat = cowobo()->posts->get_category($linkedid);
			$excludecats = array(get_cat_ID('Uncategorized'));
			if( $postcat &&  ( $postcat->slug == 'coder' || $postcat->slug == 'location' ) )
                $excludecats[] = $postcat->term_id;
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

	echo '<div class="tab">';
		echo '<div class="tabthumb">+</div>';
		echo '<div class="tabtext">';
			echo '<h2>Link your posts to this page &raquo;</h2>';
			$exclude = get_cat_ID('Uncategorized').','.get_cat_ID('Coders').','.get_cat_ID('Locations');
			echo '<form method="post" action="">';
				echo '<select name="linkto">';
					echo '<option>Select a post or scroll up and click on Add New</option>';
					$exclude = '-'.get_cat_ID('Uncategorized').', -'.get_cat_ID('Partners').', -'.get_cat_ID('Coders');
					foreach(get_posts('meta_key=author&meta_value='.$GLOBALS['profile_id'].'&cat='.$exclude.'&numberposts=-1') as $userpost):
						if($userpost->ID == cowobo()->query->linkto) $state = 'selected'; else $state='';
						echo '<option value="'.$userpost->ID.'">' . $userpost->post_title.'</option>';
					endforeach;
				echo '</select>';
                wp_nonce_field( 'linkposts', 'linkposts' );
				echo '<button type="submit" class="button">Link Post</button>';
			echo '</form>';
		echo '</div>';
	echo '</div>';

    do_action ( 'cowobo_after_post', $postid, $postcat, $author );

	//show comments
    //if ( !  ) { var_dump ( $postcat );  die; }
    if (  is_object ( $postcat ) && $postcat->slug != 'coder' )
        comments_template();
endwhile;
endif;
?>