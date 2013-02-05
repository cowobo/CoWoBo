<?php

//common variables
$cubepoints = &cowobo()->points;
$prefix = '';

if ( ! isset ( $sort ) ) $sort = '';

if( isset ( $tabtype ) && $tabtype == 'cat'):
	if ( isset ( $tabposts ) && ! empty ( $tabposts ) ) $catposts = $tabposts;
    /** Generate a query for this tab **/
	else {
        $cowobo_feed = cowobo()->feed;
        $catposts = $cowobo_feed->get_catposts ( $tabcat, 3, $sort );
    }

	if(is_single()):
		if( isset ( $postcat ) && is_object( $postcat ) && $postcat->slug =='coder') $prefix = 'My ';
		else $prefix = 'Related ';
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
					$score = '<li class="icon-heart">'.$cubepoints->get_post_points($catpost->ID).'</li>';
					$comments = '<li>Replies: '.get_comments_number($catpost->ID).'</li>';
					$date = '<li>'.cwb_time_passed(strtotime($catpost->post_modified)).'</li>';
					$status = '<li>'.get_post_meta($catpost->ID, 'cwb_status', true).'</li>';

					echo '<ul class="horlist nowrap grey">';
						if($tabcat->slug == 'event'):
							$date = get_post_meta($catpost->ID, 'cwb_startdate', true);
							echo $title.$views.$date;
						elseif($tabcat->slug == 'coder'):
							echo $title.$views.$score.$comments;

                            $last_activity = $cubepoints->get_user_last_activity_by_profile ( $catpost->ID );
                            if ( ! empty ( $last_activity ) ) :
                                echo 'Last Active: ' . $last_activity; //to do last active code
                            endif;
                            unset ( $last_activity );

						elseif($tabcat->slug == 'project'):
							echo $title.$status.$date.$score;
						elseif($tabcat->slug == 'location'):
							echo $title.$views; // .$sections;
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
	$tabcat = cowobo()->posts->get_category($tabpost->ID);
	$title = '<a href="'.get_permalink($tabpost->ID).'">'. cowobo()->L10n->the_title($tabpost->ID).' &raquo;</a>';
	$comments = '<li>Replies: '.get_comments_number($tabpost->ID).'</li>';
	$views = '<li>Views: '.cowobo()->posts->get_views($tabpost->ID).' </li>';
	$score = '<li class="icon-heart">'.$cubepoints->get_post_points($tabpost->ID).'</li>';
	$date = '<li>Updated: '.cwb_time_passed(strtotime($tabpost->post_modified)).'</li>';
	$tags = get_the_category($tabpost->ID);
	$oneliner = get_post_meta($tabpost->ID, 'cwb_oneliner', true);
    $location = '';

	if(count($tags)>1) {
		$taglist = '<br/>Posted under: ';
		foreach($tags as $tag) {
			$taglinks[] = '<a href="'.get_category_link($tag->term_id).'">'.$tag->name.'</a>';
		}
		$taglist .= implode(', ', $taglinks);
	}

	if(empty($oneliner)) {
		$firstline = explode('.', strip_tags($tabpost->post_content));
		$oneliner = substr($firstline[0], 0 , 140).'..';
	}

	if($cityid = get_post_meta($tabpost->ID, 'cwb_cityid', true)){
		$citypost = get_post($cityid);
		$citylink = '<a href="'.get_permalink($citypost->ID).'">'.$citypost->post_title.'</a>';
		$country = current ( get_the_category( $cityid ) );
        $countrylink = '<a href="'.get_category_link($country->term_id).'">'.$country->name.'</a>';
		$location = '<li>Location: '.$citylink.', '.$countrylink.'</li>';
	}

	echo '<div class="tab">';

		echo '<a class="tabthumb" href="'.get_permalink($tabpost->ID).'">';
			cowobo()->posts->the_thumbnail($tabpost->ID, $tabcat->slug);
		echo '</a>';

		echo '<div class="tabtext">';
			echo '<h2>'.$title.'</h2>';
			if($tabcat->slug == 'project'):
				$status = get_post_meta($tabpost->ID, 'cwb_status', true);
				if( !empty($status) ) $status = '<li>Status: '.$status.'</li>';
				echo '<ul class="horlist nowrap grey">'.$views.$score.$comments.$date.'</ul>';
				echo $oneliner;
				echo '<ul class="horlist nowrap">'.$location.$status.'</ul>';
			elseif($tabcat->slug == 'coder'):
				$specialty = get_post_meta($tabpost->ID, 'cwb_specialty', true);
				if( !empty($specialty) ) $specialty = '<li>Specialty: '.$specialty.'</li>';
				else $specialty = $views.$comments;

                $last_activity = $cubepoints->get_user_last_activity_by_profile ( $tabpost->ID );
                $date = ( ! empty ( $last_activity ) ) ? 'Last Active: ' . $last_activity : '';
                unset ( $last_activity );
				echo '<ul class="horlist nowrap grey">'.$score.$specialty.$date.'</ul>';
				echo $oneliner;
				echo '<ul class="horlist nowrap">'.$location.'</ul>';
			elseif($tabcat->slug == 'job'):
				$skills = get_post_meta($tabpost->ID, 'cwb_skills', true);
				if( !empty($skills) ) $skills = '<li>Skills required: '.$skills.'</li>';
				echo '<ul class="horlist nowrap grey">'.$views.$score.$comments.$date.'</ul>';
				echo $oneliner;
				echo '<ul class="horlist nowrap">'.$skills.$location.'</ul>';
			elseif($tabcat->slug == 'news'):
				$source = get_post_meta($tabpost->ID, 'cwb_source', true);
				if( !empty($source) ) $source = '<li>Skills required: '.$source.'</li>';
				echo '<ul class="horlist nowrap grey">'.$views.$score.$comments.$date.'</ul>';
				echo $oneliner;
				echo '<ul class="horlist nowrap">'.$source.'</ul>';
			elseif($tabcat->slug == 'event'):
				$startdate = get_post_meta($tabpost->ID, 'cwb_startdate', true);
				$enddate = get_post_meta($tabpost->ID, 'cwb_enddate', true);
				if($startdate != $enddate) $date = 'Date: '.date("j F", strtotime($startdate)).' - '.date("j F", strtotime($enddate));
				else $date = 'Date: '.date("j F", strtotime($startdate));
				echo '<ul class="horlist nowrap grey">'.$date.$location.'</ul>';
				echo $oneliner;
				echo $taglist;
			elseif($tabcat->slug == 'forum'):
				echo '<ul class="horlist nowrap grey">'.$views.$score.$comments.$date.'</ul>';
				echo $oneliner;
			elseif($tabcat->slug == 'wiki'):
				echo '<ul class="horlist nowrap grey">'.$views.$score.$comments.$date.'</ul>';
				echo $oneliner;
			elseif($tabcat->slug == 'location'):
				$linkedids = cowobo()->relations->get_related_ids( $tabpost->ID );
				$linkedlinks = array();
				if( $types = cowobo()->relations->get_related_types( $linkedids ) ):
					foreach($types as $typeid => $typeposts):
						$linkedcat = get_category($typeid);
						$linkedlinks[] = '<li><a href="'.get_permalink($tabpost->ID).'?showall='.$linkedcat->name.'">'.$linkedcat->name.' ('.count($typeposts).')</a></li>';
					endforeach;
				endif;
				$linkedlist = implode( '', $linkedlinks );
				echo '<ul class="horlist nowrap grey">'.$views.$score.$comments.$date.'</ul>';
				echo '<ul class="horlist">'.$linkedlist.'</ul>';
				unset($linkedlist);
			endif;
		echo '</div>';

	echo '</div>';
endif;