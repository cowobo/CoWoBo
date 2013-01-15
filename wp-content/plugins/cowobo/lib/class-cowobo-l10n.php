<?php
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

/**
 * L10n
 *
 * Manual localization
 */
class CoWoBo_Localization
{

    public function __construct() {
        global $cowobo, $lang;

        //Update language session
        if ( $lang = $cowobo->query->lang ) $this->set_lang_cookie ( $lang );
        else $lang = $this->get_lang_cookie();
    }

    /**
     * Return translated content if available
     */
    public function the_content($postid) {
        global $lang;
        if ($translated = get_post_meta($postid, 'content-'.$lang, true)) {
            return $translated;
        }
        return get_the_content($postid);
    }

    /**
     * Return translated title if available
     */
    public function the_title($postid) {
        global $lang;
        if ($translated = get_post_meta($postid, 'title-'.$lang, true)) {
            return $translated;
        }
        return get_the_title($postid);
    }

    /**
     * Add translated versions of post as custom fields
     */
    public function correct_translation() {
        global $lang, $post;
        update_post_meta($post->ID, 'title-'.$lang, $_POST['title-'.$lang]);
        update_post_meta($post->ID, 'content-'.$lang, $_POST['content-'.$lang]);
        return $notices;
    }

    /**
     * Set language cookie using the cookie duration for the remembered login cookie
     *
     * @param string $lang
     */
    public function set_lang_cookie ( $lang = '' ) {
        global $cowobo;

        if ( empty ( $lang ) ) return;
        $cowobo->query->set_cookie( 'cowobo_lang', $lang );

    }

    public function get_lang_cookie() {
        global $cowobo;
        return $cowobo->query->get_cookie('cowobo_lang');
    }

}