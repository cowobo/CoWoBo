<?php

// Do some search logic
$sort = cowobo()->feed->sort;
$out_arr = array();

	if (have_posts()):
        ob_start();

		while (have_posts()) : the_post();
            if ( $sort['type'] == 'meta_value' )
                $sort_value = get_post_meta( $post->ID, $sort['meta_key'], true );
            elseif ( $sort['type'] == 'category' ) {
                $currentcat = cowobo()->posts->get_category();
                $sort_value = $currentcat->name;
            } else $sort_value = '';

			$tabpost = $post;
			$tabtype = 'post'; include(TEMPLATEPATH.'/templates/tabs.php');

            if ( ! isset ( $out_arr[$sort_value] ) )
                $out_arr[$sort_value] = array();

            $out_arr[$sort_value][] = ob_get_clean();
            ob_start();

		endwhile;
        ob_end_clean();

        //$previous_sort_value = '';
        foreach ( $out_arr as $sort_value => $out_tabs ) {
            if ( is_numeric( $sort_value ) )
                $sort_value = get_cat_name ( $sort_value );
            
            echo "<h3>$sort_value</h3>";
            foreach ( $out_tabs as $out_tab )
                echo $out_tab;
        }

	endif;

	//include navigation links
	echo '<div class="tab center">'; cowobo()->feed->pagination(); echo '</div>';


?>
