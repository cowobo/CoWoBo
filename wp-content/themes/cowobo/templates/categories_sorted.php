<?php
    // Do some search logic
    $sort = cowobo()->feed->sort;
    $previous_sort_value = '';

	if (have_posts()):
		//$sort = $sort[$cat->term_id];
		while (have_posts()) : the_post();
            $sort_value = get_post_meta( $post->ID, $sort['meta_key'], true );
            if ( $sort_value != $previous_sort_value ) {
                echo "<h3>$sort_value</h3>";
                $previous_sort_value = $sort_value;
            }

			$tabpost = $post;
			$tabtype = 'post'; include(TEMPLATEPATH.'/templates/tabs.php');
		endwhile;
	endif;

	//include navigation links
	echo '<div class="center">'; cowobo()->feed->pagination(); echo '</div>';

?>
