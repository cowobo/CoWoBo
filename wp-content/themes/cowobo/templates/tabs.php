<?php
global $cowobo;

if($tabtype == 'cat'):
	if ($tabposts) $catposts = $tabposts;
	else $catposts = get_posts('cat='.$tabcat->term_id.'&numberposts=3&orderby='.$sort);
	if(is_single()):
		if($postcat->slug !='coder') $prefix = 'Related ';
		$catlink = '?showall='.$tabcat->name;
	else:
		$catlink = get_category_link($tabcat->term_id);
	endif;

	echo '<div class="tabthumb left">';
			cwb_the_thumbnail($catposts[0]->ID, $tabcat->slug);
	echo '</div>';

	echo '<div class="tabtext right">';
		echo '<h2><a class="black" href="'.$catlink.'">'.$prefix.$tabcat->name.' &raquo;</a></h2>';
		if($catposts):
			foreach($catposts as $catpost):
				$title = '<li class="inline"><a class="light" href="'.get_permalink($catpost->ID).'">'. $cowobo->L10n->the_title($catpost->ID).'</a></li>';
				$comments = '<li>'.get_comments_number($catpost->ID).' Comments</li>';
				$views = '<li>'.$cowobo->posts->get_views($catpost->ID).' Views</li>';
				//$coders = '<li>1&nbsp;&nbsp;Coder</li>';
				$date = '<li>'.cwb_time_passed(strtotime($catpost->post_modified)).'</li>';
				$tabcat = get_the_category($catpost->ID);
				$catlink = '<li><a href="'.get_category_link($tabcat[0]->term_id).'">'.$tabcat[0]->name.'</a></li>';
				if($tabcat->slug == 'wiki'):
					echo '<ul class="horlist nowrap">'.$title.$date.$comments.$views.$coders.'</ul>';
				elseif($tabcat->slug == 'news'):
					echo '<ul class="horlist nowrap">'.$title.$date.$comments.$views.$coders.'</ul>';

				else:
					echo '<ul class="horlist nowrap">'.$title.$date.$comments.$views.$coders.'</ul>';
				endif;
			endforeach;
		else:
			echo '<span class="grey">No posts here yet, check back soon</span>';
		endif;
	echo '</div>';
else:
	$title = '<a href="'.get_permalink($tabpost->ID).'">'. $cowobo->L10n->the_title($tabpost->ID).'</a>';
	$comments = '<li>'.get_comments_number($tabpost->ID).' Comments</li>';
	$views = '<li>'.$cowobo->posts->get_views($tabpost->ID).' Views</li>';
	$coders = '<li>1&nbsp;&nbsp;Coder</li>';
	$date = '<li>'.cwb_time_passed(strtotime($tabpost->post_modified)).'</li>';
	$tabcat = get_the_category($tabpost->ID);
	$tabtype = $cowobo->feed->get_type($tabcat[0]->term_id);
	$catlink = '<li><a href="'.get_category_link($tabcat[0]->term_id).'">'.$tabcat[0]->name.'</a></li>';

	echo '<div class="tabthumb left">';
		cwb_the_thumbnail($tabpost->ID, $tabtype->slug);
	echo '</div>';

	echo '<div class="tabtext right">';
		if($tabtype->slug == 'wiki'):
			echo $title.'<br/><ul class="horlist grey">'.$date.$comments.$views.$coders.'</ul>';
		elseif($tabtype->slug == 'location'):
			echo $title.', <a href="'.get_category_link($tabcat[0]->term_id).'">'.$tabcat[0]->name.'</a>';
			echo '<ul class="horlist grey">'.$date.$comments.$views.$coders.'</ul>';
		elseif($tabtype->slug == 'news'):
			echo $title.'<ul class="horlist grey">'.$date.$comments.$views.$coders.'</ul>';
		else:
			echo $title.'<ul class="horlist grey">'.$date.$comments.$views.$coders.'</ul>';
		endif;
	echo '</div>';
endif;