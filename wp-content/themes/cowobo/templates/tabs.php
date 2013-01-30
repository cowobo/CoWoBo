<?php

global $user_ID;

//common variables
$cubepoints = new CoWoBo_CubePoints(); 
$prefix = '';
$sort = ( isset ( $sort ) ) ? $sort : '';

if($tabtype == 'cat'):
	if ( isset ( $tabposts ) && ! empty ( $tabposts ) ) $catposts = $tabposts;
    /** @todo something is wrong with $sort **/
	else $catposts = get_posts('cat='.$tabcat->term_id.'&numberposts=3&orderby='.$sort);

	if(is_single()):
		if( isset ( $postcat ) && is_object( $postcat ) && $postcat->slug !='coder') $prefix = 'Related ';
		$catlink = '?showall='.$tabcat->name;
	else:
		$catlink = get_category_link($tabcat->term_id);
	endif;

    if ( ! isset ( $catposts[0] ) ) return;

	echo '<div class="tab">';

		echo '<a class="tabthumb" href="'.$catlink.'">';
	    	cowobo()->posts->the_thumbnail($catposts[0]->ID, $tabcat->slug);
		echo '</a>';

		echo '<div class="tabtext">';
			echo '<h2><a class="black" href="'.$catlink.'">'.$prefix.$tabcat->name.' &raquo;</a></h2>';
			if($catposts):
				
				foreach($catposts as $catpost):
					$title = '<li><a class="light" href="'.get_permalink($catpost->ID).'">'. cowobo()->L10n->the_title($catpost->ID).'</a></li>';
					$views = '<li>Views: '.cowobo()->posts->get_views($catpost->ID).'</li>';
					$score = '<li>Score: '.$cubepoints->get_post_points($catpost->ID).'</li>';
					$comments = '<li>Replies: '.get_comments_number($catpost->ID).'</li>';
					$date = '<li>'.cwb_time_passed(strtotime($catpost->post_modified)).'</li>';				
					$status = '<li>'.get_post_meta($catpost->ID, 'status', true).'</li>';					
				
					echo '<ul class="horlist nowrap">';
						if($tabcat->slug == 'event'):
							$date = get_post_meta($catpost->ID, 'startdate', true);
							echo $title.$views.$score.$comments.$date;
						elseif($tabcat->slug == 'coder'):
							$date = 'Last Active: '; //to do last active code
							echo $title.$views.$score.$comments.$date;
						elseif($tabcat->slug == 'project'):
							echo $title.$status.$date.$score;
						elseif($tabcat->slug == 'location'):
							echo $title.$views.$sections;
						elseif($tabcat->slug == 'news'):
							echo $title.$views.$date;
						else:
							echo $title.$views.$score.$comments.$date;
						endif;
					echo '</ul>';
				endforeach;
				
			endif;
		echo '</div>';

	echo '</div>';

else:
	
	$tabpost = $post;
	$title = '<a href="'.get_permalink($tabpost->ID).'">'. cowobo()->L10n->the_title($tabpost->ID).'</a>';
	$comments = '<li>Replies: '.get_comments_number($tabpost->ID).'</li>';
	$views = '<li>Views: '.cowobo()->posts->get_views($tabpost->ID).' </li>';
	$score = '<li>Score: '.$cubepoints->get_post_points($tabpost->ID).'</li>';
	$date = '<li>Updated: '.cwb_time_passed(strtotime($tabpost->post_modified)).'</li>';
	$city = get_post( get_post_meta($tabpost->ID, 'cityid', true));
	$citylink = '<a href="'.get_permalink($city->ID).'">'.$city->post_title.'</a>';
	$country = get_category( get_post_meta($tabpost->ID, 'countryid', true));
	$countrylink = '<a href="'.get_category_link($country->term_id).'">'.$country->name.'</a>';
	$location = '<li>Location: '.$citylink.', '.$countrylink.'</li>';;
	$tags = get_the_category($tabpost->ID);
	$tabcat = cowobo()->posts->get_category($tabpost->ID);
	$oneliner = get_post_meta($tabpost->ID, 'oneliner', true);
	
	if(empty($oneliner)) {
		$firstline = explode('.', $tabpost->post_content);
		$oneliner = $firstline[0];
	}

	echo '<div class="tab">';

		echo '<a class="tabthumb" href="'.get_permalink($tabpost->ID).'">';
			cowobo()->posts->the_thumbnail($tabpost->ID, $tabtype->slug);
		echo '</a>';

		echo '<div class="tabtext">';
			echo '<h2>'.$title.'</h2>';
			if($tabcat->slug == 'project'):
				$status = get_post_meta($tabpost->ID, 'status', true);
				if( empty($status) ) $status = 'Not Specified';
				$status = '<li>Status: '.$status.'</li>';				
				echo '<ul class="horlist nowrap">'.$views.$score.$comments.$date.'</ul>';
				echo '<ul class="horlist">'.$location.$status.'</ul>';
			elseif($tabcat->slug == 'coder'):
				$experience = get_post_meta($tabpost->ID, 'experience', true);
				if( empty($experience) ) $experience = 'Not Specified';
				$experience = '<li>Experience: '.$experience.'</li>';				
				echo '<ul class="horlist nowrap">'.$views.$score.$comments.$date.'</ul>';
				echo '<ul class="horlist">'.$location.$experience.'</ul>';
			elseif($tabcat->slug == 'job'):
				$skills = get_post_meta($tabpost->ID, 'skills', true);
				if( empty($skills) ) $skills = 'Not Specified';
				$skills = '<li>Skills required: '.$skills.'</li>';
				echo '<ul class="horlist nowrap">'.$views.$score.$comments.$date.'</ul>';
				echo '<ul class="horlist">'.$skills.$location.'</ul>';
			elseif($tabcat->slug == 'news'):
				$source = get_post_meta($tabpost->ID, 'source', true);
				if( empty($source) ) $source = 'Not Specified';
				$source = '<li>Skills required: '.$source.'</li>';				
				echo '<ul class="horlist nowrap">'.$views.$score.$comments.$date.'</ul>';
				echo '<ul class="horlist">'.$source.'</ul>';
			elseif($tabcat->slug == 'event'):
				$date = get_post_meta($tabpost->ID, 'source', true);
				echo '<ul class="horlist nowrap">'.$views.$score.$comments.$date.'</ul>';
				echo '<ul class="horlist">'.$location.'</ul>';
			elseif($tabcat->slug == 'forum'):
				echo '<ul class="horlist nowrap">'.$views.$score.$comments.$date.'</ul>';
			elseif($tabcat->slug == 'wiki'):
				echo '<ul class="horlist nowrap">'.$views.$score.$comments.$date.'</ul>';
			elseif($tabcat->slug == 'location'):
				$sections = array();
				foreach(get_categories() as $cat){
					$count = '';
					$sections[] = '';
				}
				echo '<ul class="horlist nowrap">'.$views.$score.$comments.$date.'</ul>';
				echo '<ul class="horlist">'.implode('', $sections).'</ul>';
			endif;
			echo $oneliner;
		echo '</div>';

	echo '</div>';
endif;