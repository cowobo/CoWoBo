<?php
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

class CoWoBo_Feeds
{
    //Filter feed based on parameters set in browse
    function filter_feed(){
        global $wp_query; global $lang; global $langnames;

        //store variables from browse form
        $cats = $_GET['cats'];
        $sortby = $_GET['sort'];
        $keywords = $_GET['keywords'];
        $country = $_GET['country'];

        //store cats to filter
        if($cats && $cats[0] != 'all'):
            $catstring = implode(',',$cats);
        elseif(is_category()):
            $catstring = get_query_var('cat');
        endif;

        if($country != 'all'):
            $metaquery = array('key'=>'country', 'value'=>$country);
        endif;

        if(empty($sort)) $sort = 'modified';
        if($sort == 'featured'):
            $sort = 'meta_value';
            $metaquery = array('meta_key'=>'featured');
        endif;

        //query filtered posts
        query_posts(array('orderby'=>$sort, 'cat'=> $catstring, 's'=>$keywords, 'meta_query' =>array($metaquery)));

    }

    //Construct feed title;
    function feed_title($link = true){
        global $currentcat; global $post; global $lang; global $langnames;

        if($_GET['new']):
            $feedtitle .= 'Add '.$_GET['new'];
        elseif(is_404()):
            $feedtitle .= 'Yikes we cannot find that content';
        elseif($_POST['userpw']):
            $feedtitle .= 'Welcome to the club';
        elseif($_GET['showall']):
            $feedtitle .= '<a href="'.get_permalink($post->ID).'">'.cwb_the_title($post->ID).'</a> <b class="grey">></b> '.$currentcat->name;
        elseif($_GET['action'] == 'login'):
            $feedtitle .= 'Who are you?';
        elseif($_GET['action'] == 'search'):
            $feedtitle .= 'Search for posts';
        elseif($_GET['action'] == 'contact'):
            $feedtitle .= 'Contact';
        elseif($_GET['action'] == 'translate'):
            $feedtitle .= 'Language';
        elseif($_GET['action'] == 'editpost'):
            $feedtitle .= 'Edit Post';
        elseif(is_single()):
            $feedtitle .= cwb_the_title($post->ID);
        elseif($_GET['sort2']):
            $cats = $_GET['cats'];
            $country = $_GET['country'];
            if($cats && $cats[0] != 'all'):
                $count = count($cats);
                foreach($cats as $catid): $x++;
                    $cat = get_category($catid);
                    $feedtitle .= $cat->name;
                    if($x < $count) $feedtitle .= ', ';
                    if($x == $count-1) $feedtitle .= 'and ';
                endforeach;
            else:
                $feedtitle .= 'All posts ';
            endif;
            if($country != 'all'):
                $countrycat = get_category($country);
                $feedtitle .= ' in "'.$countrycat->name.'"';
            endif;
            if($keywords = $_GET['keywords']):
                $feedtitle .= ' containing "'.$keywords.'"';
            endif;
            if($sort = $_GET['sort']):
                $feedtitle .= ' sorted by '.$sort;
            endif;
        elseif(is_category() or $_GET['sort']):
            $feedtitle .= $currentcat->name;
        else:
            //$feedtitle = $langnames[$lang][1];
            $feedtitle = 'Welcome to Coders Without Borders';
        endif;

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
}