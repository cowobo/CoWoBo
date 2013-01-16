<?php
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

class CoWoBo_Feed
{
    /**
     * Filter feed based on parameters set in browse
     *
     * @todo When is this method really called?
     */
    public function filter_feed(){
        global $cowobo;

        //store variables from browse form
        $cats = $cowobo->query->cats;
        $sortby = $cowobo->query->sort;
        $keywords = $cowobo->query->keywords;
        $country = $cowobo->query->country;

        //store cats to filter
        $catstring = '';
        if( $cats && $cats[0] != 'all' )
            $catstring = implode(',',$cats);
        elseif( is_category() )
            $catstring = get_query_var('cat');

        $metaquery = array();
        if($country != 'all')
            $metaquery[] = array( 'key' => 'country', 'value' => $country );

        if ( empty ( $sort ) ) $sort = 'modified';
        elseif( $sort == 'featured' ) {
            $sort = 'meta_value';
            $metaquery[] = array( 'metakey'=>'featured' );
        }

        //query filtered posts
        query_posts( array( 'orderby'=>$sort, 'cat'=> $catstring, 's'=>$keywords, 'meta_query' => $metaquery ) );

    }

    /**
     * Show all posts related to current post in requested category
     */
    public function related_feed(){
        global $post, $cowobo;
        $postids = $cowobo->relations->get_related_ids($post->ID);
        $catid = get_cat_ID( $cowobo->query->showall );
        query_posts( array( 'cat'=> $catid, 'post__in'=>$postids ) );
    }

    /**
     * Construct feed title;
     */
    function feed_title(){
        global $currentcat, $post, $cowobo;

        $feedtitle = '';
        if( $cowobo->query->new )
            $feedtitle .= 'Add '.$cowobo->query->new;
        elseif( is_404() )
            $feedtitle .= 'Yikes we cannot find that content';
        elseif( $cowobo->query->userpw )
            $feedtitle .= 'Welcome to the club';
        elseif( $cowobo->query->showall )
            $feedtitle .= '<a href="'.get_permalink( $post->ID ).'">'. $cowobo->L10n->the_title($post->ID).'</a> <b class="grey">></b> '.$currentcat->name;
        elseif( $cowobo->query->action == 'login')
            $feedtitle .= 'Who are you?';
        elseif( $cowobo->query->action == 'search')
            $feedtitle .= 'Search for posts';
        elseif( $cowobo->query->keywords )
            $feedtitle .= 'Search results';
        elseif( $cowobo->query->action == 'contact')
            $feedtitle .= 'Contact';
        elseif( $cowobo->query->action == 'translate')
            $feedtitle .= 'Language';
        elseif( $cowobo->query->action == 'editpost')
            $feedtitle .= 'Edit Post';
        elseif(is_single())
            $feedtitle .= $cowobo->L10n->the_title($post->ID);
        elseif( $cowobo->query->sort2 ) {
            $cats = $cowobo->query->cats;
            $country = $cowobo->query->country;
            if( $cats && $cats[0] != 'all' ) {
                $count = count( $cats );
                $x = 0;
                foreach( $cats as $catid ){ $x++;
                    $cat = get_category( $catid );
                    $feedtitle .= $cat->name;
                    if( $x < $count ) $feedtitle .= ', ';
                    if( $x == $count-1 ) $feedtitle .= 'and ';
                }
            } else
                $feedtitle .= 'All posts ';
            if($country != 'all') {
                $countrycat = get_category($country);
                $feedtitle .= ' in "'.$countrycat->name.'"';
            }
            if($keywords = $cowobo->query->keywords) {
                $feedtitle .= ' containing "'.$keywords.'"';
            }
            if($sort = $cowobo->query->sort)
                $feedtitle .= ' sorted by '.$sort;
        } elseif( is_category() || $cowobo->query->sort )
            $feedtitle .= $currentcat->name;
        else {
            //$feedtitle = $langnames[$lang][1];
            $feedtitle = 'Welcome to Coders Without Borders';
        }

        return $feedtitle;
    }

    //Get primal category of feed category
    function get_type($catid) {
        $ancestors = get_ancestors($catid,'category');
        if (empty($ancestors)):
            return get_category($catid);
        else:
            return get_category(array_pop($ancestors));
        endif;
    }

    // Returns pagination for a feed
    public function pagination($pages = '', $range = 2){
         $showitems = ($range * 2)+1;
         global $paged;
         if(empty($paged)) $paged = 1;
         if($pages == ''){
             global $wp_query;
             $pages = $wp_query->max_num_pages;
             if(!$pages) $pages = 1;
         }
         $pagination = '<span class="horspans">';
         if(1 != $pages){
             for ($i=1; $i <= $pages; $i++){
                 if (1 != $pages &&( !($i >= $paged+$range+1 || $i <= $paged-$range-1) || $pages <= $showitems ))
                     $pagination .= ($paged == $i)? "<span class='current'>".$i."</span>":"<a href='".get_pagenum_link($i)."' class='inactive' >".$i."</a>";
             }
             $pagination .= '<a href="'.get_pagenum_link($paged + 1).'">Next</a>';
         }
         return $pagination;
    }
}