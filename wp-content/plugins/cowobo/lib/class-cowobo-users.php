<?php
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

/**
 * CoWoBo User classes
 *
 * @package CoWoBo
 * @subpackage Plugin
 */
class CoWoBo_Users
{
    public function __construct() {
        $this->actions_and_filters();
    }

    private function actions_and_filters() {
        add_action('show_user_profile', array ( &$this, 'show_extra_profile_fields' ) );
        add_action('edit_user_profile', array ( &$this, 'show_extra_profile_fields' ) );
        add_action('personal_options_update', array ( &$this, 'save_extra_profile_fields' ) );
        add_action('edit_user_profile_update', array ( &$this, 'save_extra_profile_fields' ) );
    }


    /**
     * Create user and redirect them to profile
     */
    public function create_user(){

        //combine first name and password to form unique username
        $name = sanitize_user($_POST['username']);
        $userpw = sanitize_user($_POST['userpw']);
        $username = $name.$userpw;

        //add user to database
        $tempemail = $username.'@cowobo.org';
        $userid = wp_create_user($username, $userpw, $tempemail);
        $usercount = count_users();
        $profile = array(
            'post_author' => $userid,
            'post_category' => array(get_cat_ID('Coders')),
            'post_content' => " ",
            'post_status' => 'publish',
            'post_title' => $name,
            'post_type' => 'post'
        );
        $profileid = wp_insert_post($profile);
        update_user_meta($userid, 'cowobo_profile', $profileid);
        cwb_login_user();
    }

    /**
     * Login user
     */
    public function cwb_login_user(){
        //combine first name and password to form unique user
        $name = sanitize_user($_POST['username']);
        $userpw = sanitize_user($_POST['userpw']);
        $username = $name.$userpw;

        //login user with this username
        if($userid = username_exists($username)){
            wp_signon(array('user_login'=> $username, 'user_password'=> $userpw, 'remember'=> true), false);
            $profileid = get_user_meta($userid, 'cowobo_profile', true);
            if($_GET['confirm']) wp_safe_redirect(get_permalink($profileid).'?action=editpost');
            elseif($_POST['redirect'] == 'profile') wp_safe_redirect(get_permalink($profileid));
            elseif($_POST['redirect'] == 'contact') wp_safe_redirect('?action=contact');
            elseif($_POST['redirect'] == 'edit') wp_safe_redirect('?action=editpost');
            else wp_safe_redirect($_SERVER["REQUEST_URI"]);
        }

    }


    /**
     * Add profile id to backend profile
     */
    public function show_extra_profile_fields( $user ) {
        echo '<table class="form-table">';
            echo '<tr>';
                echo '<th><label>Profile ID:</label></th>';
                echo '<td><input type="text" name="cowobo_profile" id="cowobo_profile" value="'.esc_attr(get_the_author_meta('cowobo_profile', $user->ID )).'"/><br/>';
                echo '</td>';
            echo '</tr>';
        echo '</table>';
    }

    /**
     * Save profile id field
     */
    public function save_extra_profile_fields( $user_id ) {
        if ( !current_user_can( 'edit_user', $user_id ) )
            return false;
        update_usermeta( $user_id, 'cowobo_profile', $_POST['cowobo_profile'] );
    }
}