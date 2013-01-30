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
    public $current_user_profile_id = 0;
    public $current_user_profile_name = '';
    public $displayed_user = null;

    public function __construct() {
        global $profile_id;
        $profile_id = $this->get_current_user_profile_id();
        $this->has_sent_email();
        $this->_maybe_save_avatar();

        $this->actions_and_filters();
    }

    private function actions_and_filters() {
        add_action('show_user_profile',             array ( &$this, 'show_extra_profile_fields' ) );
        add_action('edit_user_profile',             array ( &$this, 'show_extra_profile_fields' ) );
        add_action('personal_options_update',       array ( &$this, 'save_extra_profile_fields' ) );
        add_action('edit_user_profile_update',      array ( &$this, 'save_extra_profile_fields' ) );

        //add_action('cowobo_profile_dropdown', array ( &$this, 'current_user_box' ) );
        add_action('cowobo_profile_widget',         array ( &$this, 'current_user_box' ) );
        add_action('current_user_box',              array ( &$this, 'do_avatar_current_user' ), 5 );
        add_action('current_user_box',              array ( &$this, 'do_avatar_with_upload_form_current_user' ), 99 );
        add_action('current_user_box',              array ( &$this, 'do_user_link' ), 10 );
        add_action('cowobo_before_postcontent',     array ( &$this, 'do_profile_avatar' ), 10 );

        add_filter( 'avatar_defaults' ,             array( &$this , 'avatar_defaults' ) );

    }

    private function _maybe_save_avatar() {
        if ( cowobo()->query->user_avatar_edit_submit || cowobo()->query->{'simple-local-avatar-erase'} ) {
           do_action('edit_user_profile_update', get_current_user_id() );
        }
    }

    public function current_user_box() {
        if ( ! has_action ( 'current_user_box') ) return;
        echo "<div class='current-user'>";
            do_action ( 'current_user_box' );
        echo "</div>";
    }

    public function do_user_link() {
        echo "<h3>";
        do_action ( 'cowobo_before_user_link' );
        echo "<a href='" . get_permalink ( $this->current_user_profile_id ) . "'>" . $this->current_user_profile_name . "</a>";
        do_action ( 'cowobo_after_user_link' );
        echo "</h3>";
    }

    public function do_profile_avatar() {
        if ( ! $this->is_profile() ) return;
        echo "<div class='fourth square'>";
        echo get_avatar( get_current_user_id(), 149 );
        echo "</div>";
    }

    public function do_avatar_current_user() {
        echo "<p class='left'><a href='?upload-avatar' class='upload-avatar-link'>";
        echo get_avatar( get_current_user_id() );
        echo "</a></p>";
    }

    public function do_avatar_with_upload_form() {

        //$default = ( defined ( 'COWOBO_DEFAULT_AVATAR_URL' ) ) ? COWOBO_DEFAULT_AVATAR_URL : '';

        echo "<p class='left'><a href='?upload-avatar' class='upload-avatar-link'>";
        echo get_avatar( get_current_user_id() );
        echo "</a></p>";

        echo "<div class='upload-avatar hide-if-js'>";
        $this->avatar_upload_form();
        echo "</div>";
    }

    public function do_avatar_with_upload_form_current_user() {
        echo "</div><div class='current-user-avatar-form hide-if-js'>";
        $this->avatar_upload_form();
        echo "<a href='#' class='upload-avatar-link right'>Cancel</a>";
    }

    private function avatar_upload_form() {
        do_action( 'simple_local_avatar_notices' );
        ?>
        <form id='your-profile' action='' method='post'>
            <?php wp_nonce_field( 'simple_local_avatar_nonce', '_simple_local_avatar_nonce', false ); ?>
            <input type="file" name="simple-local-avatar" id="simple-local-avatar" /><br />

            <p>
                <input type="submit" class='button' name="user_avatar_edit_submit" value="Upload avatar"/>

                <?php if ( ! empty( get_userdata( get_current_user_id() )->simple_local_avatar ) ) : ?>

                    or <input type="submit" name="simple-local-avatar-erase" value="delete avatar" class="button button-secondary right">

                <?php endif; ?>
            </p>
            <script type="text/javascript">var form = document.getElementById('your-profile');form.encoding = 'multipart/form-data';form.setAttribute('enctype', 'multipart/form-data');</script>
        </form>
        <div class="clear"></div>
        <?php
    }

    private function has_sent_email() {

        //check if the user sent an email
        if( cowobo()->query->emailmsg ) {
            cowobo()->add_notice( 'Your email has been sent. We will get back to you shortly!', 'emailsent' );
        }
    }

    /**
     * Create user and redirect them to profile
     *
     * Creates a username based on the emailaddress.
     */
    public function create_user(){

        $email = cowobo()->query->email;
        if ( ! $name = sanitize_user ( $email ) ) {
            cowobo()->notices[] = array ( "NOEMAIL" => "Please supply an e-mail address." );
            return ; // Userpw is posted, so login.php knows something is wrong
        }
        if ( ! is_email( $email ) ) {
            cowobo()->notices[] = array ( "INVALIDEMAIL" => "E-mail address not valid." );
            return;
        }

        //add user to database
        $userid = wp_create_user ( $name, cowobo()->query->userpw, $email );
        if ( is_a ( $userid, 'WP_Error' ) ) {
            cowobo()->notices[] = array ( "USEREXISTS" => "User already exists." );
            return;
        }

        $profile = array(
            'post_author' => $userid,
            'post_category' => array( get_cat_ID('Coders') ),
            'post_content' => "",
            'post_status' => 'publish',
            'post_title' => substr ( $name, 0, strpos ( $name, '@' ) ),
            'post_type' => 'post'
        );

        $profileid = wp_insert_post ( $profile ) ;
        update_user_meta( $userid, 'cowobo_profile', $profileid );
        $this->login_user( true );
    }

    /**
     * Login user based on email and redirect
     */
    public function login_user( $go_to_profile = false ){
        $email = cowobo()->query->email;

        // Shouldn't happen, but hey..
        if ( empty ( $email ) || ! is_email( $email ) ) {
            cowobo()->notices[] = array ( "NOEMAIL" => "Please supply a valid e-mail address." );
            return;
        };

        // Get user and check if she exists
        $user = get_user_by( 'email', $email );
        if ( ! isset( $user, $user->user_login, $user->user_status ) ) {
            cowobo()->notices[] = array ( "INVALIDUSER" => "User does not exist." );
            return;
        }

        $username = $user->user_login;
        $signed_in_user = wp_signon( array ( 'user_login'=> $username, 'user_password'=> cowobo()->query->userpw, 'remember'=> true ), false);

        if ( is_a ( $signed_in_user, 'WP_Error' ) ) {
            cowobo()->notices[] = array ( "WRONGPASSWORD" => "The supplied password is incorrect." );
            return;
        }

        $profileid = get_user_meta( $signed_in_user->ID, 'cowobo_profile', true );
        if( $go_to_profile )
            wp_safe_redirect( get_permalink( $profileid ) . '?action=editpost' );
        else
            cowobo()->redirect();

    }

    public function lost_password() {

        $new_pwd = $this->retrieve_password();
        if ( ! is_wp_error( $new_pwd ) ) {
            cowobo()->add_notice('Password reset e-mail sent.', 'message');
            return;
        }
        $messages = $new_pwd->get_error_messages();
        foreach ( $messages as $message ) {
            cowobo()->add_notice ( $message, 'error' );
        }

    }

    /**
     * From wp-login.php
     * @global type $wpdb
     * @global type $current_site
     * @return \WP_Error|boolean
     */
    private function retrieve_password() {
        global $wpdb, $current_site;

        $errors = new WP_Error();

        if ( empty( $_POST['user_login'] ) ) {
            $errors->add('empty_username', __('<strong>ERROR</strong>: Enter a username or e-mail address.'));
        } else if ( strpos( $_POST['user_login'], '@' ) ) {
            $user_data = get_user_by( 'email', trim( $_POST['user_login'] ) );
            if ( empty( $user_data ) )
                $errors->add('invalid_email', __('<strong>ERROR</strong>: There is no user registered with that email address.'));
        } else {
            $login = trim($_POST['user_login']);
            $user_data = get_user_by('login', $login);
        }

        do_action('lostpassword_post');

        if ( $errors->get_error_code() )
            return $errors;

        if ( !$user_data ) {
            $errors->add('invalidcombo', __('<strong>ERROR</strong>: Invalid username or e-mail.'));
            return $errors;
        }

        // redefining user_login ensures we return the right case in the email
        $user_login = $user_data->user_login;
        $user_email = $user_data->user_email;

        do_action('retreive_password', $user_login);  // Misspelled and deprecated
        do_action('retrieve_password', $user_login);

        $allow = apply_filters('allow_password_reset', true, $user_data->ID);

        if ( ! $allow )
            return new WP_Error('no_password_reset', __('Password reset is not allowed for this user'));
        else if ( is_wp_error($allow) )
            return $allow;

        $key = $wpdb->get_var($wpdb->prepare("SELECT user_activation_key FROM $wpdb->users WHERE user_login = %s", $user_login));
        if ( empty($key) ) {
            // Generate something random for a key...
            $key = wp_generate_password(20, false);
            do_action('retrieve_password_key', $user_login, $key);
            // Now insert the new md5 key into the db
            $wpdb->update($wpdb->users, array('user_activation_key' => $key), array('user_login' => $user_login));
        }
        $message = __('Someone requested that the password be reset for the following account:') . "\r\n\r\n";
        $message .= network_home_url( '/' ) . "\r\n\r\n";
        $message .= sprintf(__('Username: %s'), $user_login) . "\r\n\r\n";
        $message .= __('If this was a mistake, just ignore this email and nothing will happen.') . "\r\n\r\n";
        $message .= __('To reset your password, visit the following address:') . "\r\n\r\n";
//        $message .= '<' . network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login') . ">\r\n";
        $message .= '<' . get_bloginfo('url') . "/?action=login&rp=1&key=$key&login=" . rawurlencode($user_login) . ">\r\n";

        if ( is_multisite() )
            $blogname = $GLOBALS['current_site']->site_name;
        else
            // The blogname option is escaped with esc_html on the way into the database in sanitize_option
            // we want to reverse this for the plain text arena of emails.
            $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

        $title = sprintf( __('[%s] Password Reset'), $blogname );

        $title = apply_filters('retrieve_password_title', $title);
        $message = apply_filters('retrieve_password_message', $message, $key);

        if ( $message && !wp_mail($user_email, $title, $message) )
            wp_die( __('The e-mail could not be sent.') . "<br />\n" . __('Possible reason: your host may have disabled the mail() function...') );

        return true;
    }

    public function check_password_reset_key() {
        global $wpdb;

        $key = cowobo()->query->key;
        $login = cowobo()->query->login;

        $key = preg_replace('/[^a-z0-9]/i', '', $key);

        if ( empty( $key ) || !is_string( $key ) )
            return new WP_Error('invalid_key', __('Invalid key'));

        if ( empty($login) || !is_string($login) )
            return new WP_Error('invalid_key', __('Invalid key'));

        $user = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->users WHERE user_activation_key = %s AND user_login = %s", $key, $login));

        if ( empty( $user ) )
            return new WP_Error('invalid_key', __('Invalid key'));

        return $user;
    }

    public function reset_password($user, $new_pass) {
        do_action('password_reset', $user, $new_pass);

        wp_set_password($new_pass, $user->ID);

        wp_password_change_notification($user);
    }

    /**
     * Get the 'cowobo_profile' metavalue for the current user
     * @return type
     */
    public function get_current_user_profile_id() {
        if ( ! $this->current_user_profile_id || empty ( $this->current_user_profile_id ) ) {
            $user_id = wp_get_current_user()->ID;
            $this->current_user_profile_id = $this->get_user_profile_id( $user_id );
            $this->current_user_profile_name = get_the_title ( $this->current_user_profile_id );
        }

        return $this->current_user_profile_id;
    }

        public function get_current_user_profile_link() {
            return get_permalink( $this->get_current_user_profile_id() );
        }

        public function get_user_profile_id ( $user_id = 0 ) {
            if ( ! $user_id ) $user_id = wp_get_current_user()->ID;
            elseif ( is_a ( $user_id, 'WP_User' ) )
                $user_id = $user_id->ID;

            if ( ! $user_id ) return false;

            return get_user_meta($user_id, 'cowobo_profile', true);
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
    public function save_extra_profile_fields( $user_id = false ) {
        if ( ! is_admin() || ! cowobo()->query->cowobo_profile ) return;

        if ( ! $user_id ) $user_id = get_current_user_id();

        if ( !current_user_can( 'edit_user', $user_id ) )
            return false;
        update_usermeta( $user_id, 'cowobo_profile', $_POST['cowobo_profile'] );
    }

    public function get_users_by_profile_id( $id, $single = false ) {
        $users = get_users ( array ( 'meta_key' => 'cowobo_profile', 'meta_value' => $id ) );
        if ( ! $single ) return $users;
        return current ( $users );
    }

    public function is_profile( $post_id = 0 ) {
        if ( $post_id ) {
            $category = cowobo()->posts->get_category( $post_id );
            if ( ! is_object ( $category ) || $category->slug != 'coder' ) return false;
            $users = cowobo()->users->get_users_by_profile_id( get_the_ID() );
            if ( empty ( $users ) ) return false;
            return current ( $users );
        }

        if ( $this->displayed_user && ! empty ( $this->displayed_user ) )
            return $this->displayed_user;

        if ( ! is_single () ) return false;
        $category = cowobo()->posts->get_category();

        if ( ! is_object ( $category ) || $category->slug != 'coder' ) return false;

        $users = cowobo()->users->get_users_by_profile_id( get_the_ID() );
        if ( empty ( $users ) ) return false;

        $this->displayed_user = current ( $users );
        return $this->displayed_user;
    }

    public function get_user_domain ( $user_id = 0 ) {

        if ( ! $domain = wp_cache_get( 'cowobo_user_domain_' . $user_id, 'cowobo' ) ) {
            $profile_id = $this->get_user_profile_id( $user_id );
            $domain = get_permalink( $profile_id );
            wp_cache_set( 'cowobo_user_domain_' . $user_id, $domain, 'cowobo' );
        }
        return $domain;

    }

    public function is_current_user_profile() {
        return ( get_the_ID() == $this->current_user_profile_id );
    }

	function avatar_defaults( $avatar_defaults ) {
       $cowoboavatar = COWOBO_DEFAULT_AVATAR_URL;
       $avatar_defaults[$cowoboavatar] = 'Cowobo';
       return $avatar_defaults;
	}

}