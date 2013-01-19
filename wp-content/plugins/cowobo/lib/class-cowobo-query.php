<?php
// Exit if accessed directly
if (!defined('ABSPATH'))
        exit;

class CoWoBo_Query
{
    private $nonce = false;
    private $req = array();

    public function __construct( $nonce = false ) {
        $this->req = array_merge($_GET, $_POST);
        if ( $nonce )
            $this->nonce = true;
    }

    private function get($key) {
        if (is_array($key)) {
            $result = array();
            foreach ($key as $k) {
                $result[$k] = $this->get($k);
            }
            return $result;
        }
        $query_var = ( isset($this->req[$key] ) ) ? $this->verify( $key, $this->req[$key] ) : null;
        if ($query_var) {
            return $this->strip_magic_quotes($query_var);
        } else {
            return null;
        }
    }

    public function __get($key) {
        return $this->get($key);
    }

    public function __isset($key) {
        return ($this->get($key) !== null);
    }

    private function strip_magic_quotes($value) {
        if (get_magic_quotes_gpc() && is_string($value)) {
            return stripslashes($value);
        } else {
            return $value;
        }
    }

    private function verify( $action, $nonce ) {
        if ( ! $this->nonce || wp_verify_nonce( $nonce, $action ) ) return $nonce;
        else return null;
    }

    public function set_cookie( $key, $value ) {
        $user_id = get_current_user_id();
        $expiration = time() + apply_filters('auth_cookie_expiration', 172800, $user_id, true);
        $secure = apply_filters('secure_auth_cookie', $secure, $user_id);

        setcookie( $key, $value, $expiration, COOKIEPATH, COOKIE_DOMAIN, $secure, true );
        if ( COOKIEPATH != SITECOOKIEPATH )
            setcookie( $key, $value, $expiration, SITECOOKIEPATH, COOKIE_DOMAIN, $secure, true );
    }

    public function get_cookie( $key ) {
        return ( isset ( $_COOKIE[$key] ) ) ? $_COOKIE[$key] : null;
    }
}