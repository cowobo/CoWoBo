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
    public $displayed_user = null;

    public function __construct() {
        global $profile_id;
        $profile_id = $this->get_current_user_profile_id();
        $this->has_sent_email();

        $this->actions_and_filters();
    }

    private function actions_and_filters() {
        add_action('show_user_profile',             array ( &$this, 'show_extra_profile_fields' ) );
        add_action('edit_user_profile',             array ( &$this, 'show_extra_profile_fields' ) );
        add_action('personal_options_update',       array ( &$this, 'save_extra_profile_fields' ) );
        add_action('edit_user_profile_update',      array ( &$this, 'save_extra_profile_fields' ) );

        add_action('cowobo_after_content_loggedin', array ( &$this, 'current_user_box' ) );
        add_action('current_user_box',              array ( &$this, 'show_avatar_with_upload_form' ), 5 );

    }

    public function current_user_box() {
        if ( ! has_action ( 'current_user_box') ) return;
        echo "<div class='tab'>";
        do_action ( 'current_user_box', &$this );
        echo "</div>";
    }

    public function show_avatar_with_upload_form() {
        if( isset ( $_POST['user_avatar_edit_submit'] ) ) {
           do_action('edit_user_profile_update', get_current_user_id() );
        }


        $default = ( defined ( 'COWOBO_DEFAULT_AVATAR_URL' ) ) ? COWOBO_DEFAULT_AVATAR_URL : '';

        echo "<p class='hide-if-no-js left'><a href='?upload-avatar' class='upload-avatar-link'>";
        echo get_avatar( get_current_user_id() );
        echo "</a></p>";

        echo "<div class='upload-avatar hide-if-js'>";
        $this->avatar_upload_form();
        echo "</div>";
    }

    private function avatar_upload_form() {
        do_action( 'simple_local_avatar_notices' );
        ?>
        <form id='your-profile' action='' method='post'>
            <?php wp_nonce_field( 'simple_local_avatar_nonce', '_simple_local_avatar_nonce', false ); ?>
            <input type="file" name="simple-local-avatar" id="simple-local-avatar" /><br />

            <?php
            if ( empty( get_userdata( get_current_user_id() )->simple_local_avatar ) )
                echo '<span class="description">' . __('No local avatar is set. Use the upload field to add a local avatar.','simple-local-avatars') . '</span>';
            else
                echo '
                    <input type="checkbox" name="simple-local-avatar-erase" value="1" /> ' . __('Delete local avatar','simple-local-avatars') . '
                ';
            ?>
            <p><input type="submit" class='button' name="user_avatar_edit_submit" value="Upload avatar"/></p>
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

    /**
     * Get the 'cowobo_profile' metavalue for the current user
     * @return type
     */
    public function get_current_user_profile_id() {
        if ( ! $this->current_user_profile_id ) {
            $user_id = wp_get_current_user()->ID;
            $this->current_user_profile_id = $this->get_user_profile_id( $user_id );
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
        if ( ! $user_id ) $user_id = get_current_user_id();

        if ( !current_user_can( 'edit_user', $user_id ) )
            return false;
        update_usermeta( $user_id, 'cowobo_profile', $_POST['cowobo_profile'] );
    }

    public function get_users_by_profile_id( $id ) {
        return get_users ( array ( 'meta_key' => 'cowobo_profile', 'meta_value' => $id ) );
    }

    public function is_profile() {

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

}