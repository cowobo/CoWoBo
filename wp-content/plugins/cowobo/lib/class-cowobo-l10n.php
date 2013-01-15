<?php
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

class CoWoBo_Localization
{
    //return translated content if available
    public function the_content($postid) {
        global $lang;
        if ($translated = get_post_meta($postid, 'content-'.$lang, true)) {
            return $translated;
        }
        return get_the_content($postid);
    }

    //return translated title if available
    public function the_title($postid) {
        global $lang;
        if ($translated = get_post_meta($postid, 'title-'.$lang, true)) {
            return $translated;
        }
        return get_the_title($postid);
    }

    //add translated versions of post as custom fields
    public function correct_translation() {
        global $lang; global $post;
        update_post_meta($post->ID, 'title-'.$lang, $_POST['title-'.$lang]);
        update_post_meta($post->ID, 'content-'.$lang, $_POST['content-'.$lang]);
        return $notices;
    }

}
