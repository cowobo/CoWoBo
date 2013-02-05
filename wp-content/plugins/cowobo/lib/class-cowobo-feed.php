<?php
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

class CoWoBo_Feed
{
    public $sort = array(
        'type'  => ''
    );

    public $default_cat_sorting = array (
       "news"       => "date",
       "event"      => "startdate",
       "default"    => "rating"
    );

    public function __construct() {
        add_filter('pre_get_posts', array ( &$this, 'filter_category_pages' ) );

    }

    public function filter_category_pages ( $wp_query ) {

        if (is_category() ) {
            $cat = $wp_query->get_queried_object();

            $sort = get_category_sort_query ( $cat );

            $query = $this->get_sort_query( $sort );

            foreach ( $query as $query_key => $query_value ) {
                $wp_query->set( $query_key, $query_value );

            }
        }
        return $wp_query;
    }

    /**
     * Filter feed based on parameters set in browse
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

        $query = array();
		$sort = ( is_array( $sortby ) ) ? $sortby[0] : $sortby;
        if ( empty ( $sort ) ) $sort = 'modified';

        $this->set_sort_and_query ( $sort, $query );

        $query_default = array (
            'orderby'=>$sort, 'cat'=> $catstring, 's'=>$keywords
        );
        $query = array_merge ( $query, $query_default );

        //query filtered posts
        query_posts( $query );

    }

    public function get_category_sort_query( $cat ) {
        $sort = $this->get_category_default_sort( $cat );
        return $this->get_sort_query( $sort );
    }

        private function get_sort_and_query ( $sort = '', $query = array(), $set_sort_in_query = false ) {
            if ( $sort == 'a-z' ) {
                $sort = 'title';
                $query['order'] = 'ASC';
            } elseif ( $sort == 'z-a' ) {
                $sort = 'title';
                $query['order'] = 'DESC';
            } elseif ( $sort == 'location') {
                $sort = $this->sort['type'] = 'meta_value';
                $query['meta_key'] = $this->sort['meta_key'] = 'cwb_country';
            } elseif ( 'date' == $sort ) {
                $sort = $this->sort['type'] = 'modified';
            } elseif ( 'category' == $sort ) {
                $sort = $this->sort['type'] = 'category';
            } elseif ( 'startdate' == $sort ) {
                $sort = $this->sort['type'] = 'meta_value';
                $query['meta_key'] = $this->sort['meta_key'] = 'cwb_startdate_timestamp';
                $query['order'] = 'ASC';
            } elseif( $sort == 'rating' || empty ( $sort ) ) { // Default
                $sort = 'meta_value_num';
                $query['meta_key'] = 'cwb_points';
            }

            if ( $set_sort_in_query )
                $query['orderby'] = $sort;

            return array ( "sort" => $sort, "query" => $query );
        }

        private function get_sort_query ( $sort ) {
            $results = $this->get_sort_and_query( $sort, array(), true );
            return $results['query'];
        }

        private function set_sort_and_query ( &$sort, &$query, $set_sort_in_query = false ) {
            $results = $this->get_sort_and_query( $sort, $query, $set_sort_in_query );

            $sort = $results['sort'];
            $query = $results['query'];
        }


    public function get_catposts( $cat, $numposts = 3, $sort = '' ) {
        $query = array(
            'numberposts'   => $numposts,
            'cat'           => $cat->term_id
        );

        if ( empty ( $sort ) )
            $sort = $this->get_category_default_sort( $cat );

        $this->set_sort_and_query( $sort, $query, true );

        return get_posts( $query );
    }

        private function get_category_default_sort ( $cat ) {
            if ( isset ( $cat->slug ) && array_key_exists ( $cat->slug, $this->default_cat_sorting ) )
            $sort = $this->default_cat_sorting[$cat->slug];
            else
                $sort = $this->default_cat_sorting['default'];

            return $sort;
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
            $feedtitle = 'Welcome to the club!';
        } elseif( cowobo()->query->showall ) {
            $feedtitle = '<a href="'.get_permalink( $post->ID ).'">'.get_the_title($post->ID).'</a> <b class="grey">></b> '.$currentcat->name;
            $feedlink = '';
        } elseif( cowobo()->query->s ) {
			$feedtitle = 'Search Results';
        } elseif( cowobo()->query->action == 'login') {
            $feedtitle = 'Who are you angel?';
        } elseif( cowobo()->query->action == 'contact') {
            $feedtitle = 'Contact';
        } elseif( cowobo()->query->action == 'translate') {
            $feedtitle = 'Change Language';
        } elseif( cowobo()->query->action == 'editpost') {
            if( is_user_logged_in() ) {
				$postcat = cowobo()->posts->get_category($post->ID);
				if($postcat->slug == 'coder') $feedtitle = 'Update Your Profile';
				else $feedtitle = 'Edit Post';
			} else $feedtitle = 'Who are you angel?';
            $feedlink = remove_query_arg( 'action' );
        } elseif( is_single() ) {
			$feedtitle = $post->post_title;
            $feedlink = get_permalink();
        } elseif(is_category()) {
            $feedtitle = $currentcat->name;
            $feedlink = get_category_link ( $currentcat );
            //$feedlink = get_category_link ( $currentcat );
        } elseif( is_home() ) {
			$feedtitle = '<b>Coders</b> Without <b>Borders</b> <a class="tour" href="'.get_bloginfo('url').'/category/wiki">Take the tour >></a>';
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
        $pagination = '';
         $showitems = ($range * 2)+1;
         global $paged;
         if(empty($paged)) $paged = 1;
         if($pages == ''){
             global $wp_query;
             $pages = $wp_query->max_num_pages;
             if(!$pages) $pages = 1;
         }
         if(1 != $pages){
		 	$pagination = '<div class="tab center horlinks">';
            for ($i=1; $i <= $pages; $i++){
                 if (1 != $pages &&( !($i >= $paged+$range+1 || $i <= $paged-$range-1) || $pages <= $showitems ))
                     $pagination .= ($paged == $i)? "<span class='current'>".$i."</span>":"<a href='".get_pagenum_link($i)."' class='inactive' >".$i."</a>";
            }
            $pagination .= '<a href="'.get_pagenum_link($paged + 1).'">Next</a>';
			$pagination .= '</div>';
         }

         return $pagination;
    }
}