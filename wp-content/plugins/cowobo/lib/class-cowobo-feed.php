<?php
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

class CoWoBo_Feed
{
    public $sort = array();

    /**
     * Filter feed based on parameters set in browse
     *
     * @todo When is this method really called?
     */
    public function filter_feed(){

        //store variables from browse form
        $cats = cowobo()->query->cats;
        $sortby = cowobo()->query->sort;
        $keywords = cowobo()->query->s;

        //store cats to filter
        $catstring = '';
        if( $cats && $cats[0] != 'all' ) $catstring = implode(',',$cats);
        elseif( is_category() ) $catstring = get_query_var('cat');

		//todo: handle multiple sort values
        //$metaquery = array();
        $query = array();
		$sort = $sortby[0];
        $direction = '';
        if ( empty ( $sort ) ) $sort = 'modified';
        elseif( $sort == 'rating' ) {
            $sort = 'meta_value_num';
			//$metaquery[] = array( 'metakey'=>'cowobo_points' );
            $query['meta_key'] = 'cowobo_points';
		} elseif ( $sort == 'a-z' ) {
			$sort = 'title';
		} elseif ( $sort == 'z-a' ) {
			$sort = 'title';
			$direction = 'ASC';
		} elseif ( $sort == 'location') {
            $sort = 'meta_value';
			$query['meta_key'] = $this->sort['meta_key'] = 'country';
		}

        $query_default = array (
            'orderby'=>$sort, 'order'=>$direction, 'cat'=> $catstring, 's'=>$keywords
        );
        $query = array_merge ( $query, $query_default );

        //query filtered posts
        query_posts( $query );

    }

    /**
     * Show all posts related to current post in requested category
     */
    public function related_feed(){
        global $post, $cowobo;
        $postids = cowobo()->relations->get_related_ids($post->ID);
        $catid = get_cat_ID( cowobo()->query->showall );
        query_posts( array( 'cat'=> $catid, 'post__in'=>$postids ) );
    }

    /**
     * Construct feed title;
     *
     * @todo What's showall?
     */
    function feed_title(){
        global $currentcat, $post, $cowobo;

        $feedtitle = '';
        $feedlink = get_bloginfo ( 'url' );
        if( cowobo()->query->new ) {
            $feedtitle .= 'Add '.cowobo()->query->new;
            $feedlink = get_category_link( get_cat_ID ( cowobo()->query->new ) );
        } elseif( is_404() ) {
            $feedtitle = 'Yikes we cannot find that content';
        } elseif( cowobo()->query->userpw ) {
            $feedtitle = 'Welcome to the club';
        } elseif( cowobo()->query->showall ) {
            $feedtitle = '<a href="'.get_permalink( $post->ID ).'">'. cowobo()->L10n->the_title($post->ID).'</a> <b class="grey">></b> '.$currentcat->name;
            $feedlink = '';
        } elseif( cowobo()->query->s ) {
			$feedtitle = 'Search Results';
        } elseif( cowobo()->query->action == 'login') {
            $feedtitle = 'Who are you?';
        } elseif( cowobo()->query->action == 'contact') {
            $feedtitle = 'Contact';
        } elseif( cowobo()->query->action == 'translate') {
            $feedtitle = 'Change Language';
        } elseif( cowobo()->query->action == 'editpost') {
            $feedtitle = 'Edit Post';
            $feedlink = remove_query_arg( 'action' );
        } elseif( cowobo()->users->is_profile() ) {
			$feedtitle = $post->post_title;
            $feedlink = get_permalink();
        } elseif( is_single() || is_category()) {
            $feedtitle = $currentcat->name;
            if ( $currentcat->term_id )
                $feedlink = get_category_link ( $currentcat );
        } elseif( is_home() ) {
            $feedtitle = 'Welcome!';
        }

        if ( ! empty ( $feedlink ) )
            $feedtitle = "<a href='$feedlink' alt='$feedtitle'>$feedtitle</a>";

        return $feedtitle;
    }

    //Get primal category of feed category
    function get_type($catid) {
        $ancestors = get_ancestors($catid,'category');
        if (empty($ancestors))
            $cat = get_category($catid);
        else
            $cat =  get_category(array_pop($ancestors));

        return $cat;
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