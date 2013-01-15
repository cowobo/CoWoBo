<?php
// Exit if accessed directly
if (!defined('ABSPATH'))
        exit;

class CoWoBo_Query
{
    private $nonce = false;

    function __construct( $nonce = false ) {
        if ( $nonce = true )
            $this->nonce = true;
    }

    function get($key) {
        if (is_array($key)) {
            $result = array();
            foreach ($key as $k) {
                $result[$k] = $this->get($k);
            }
            return $result;
        }
        $query_var = ( isset($_REQUEST[$key] ) ) ? $this->verify( $key, $_REQUEST[$key] ) : null;
        if ($query_var) {
            return $this->strip_magic_quotes($query_var);
        } else {
            return null;
        }
    }

    function __get($key) {
        return $this->get($key);
    }

    function __isset($key) {
        return ($this->get($key) !== null);
    }

    function strip_magic_quotes($value) {
        if (get_magic_quotes_gpc()) {
            return stripslashes($value);
        } else {
            return $value;
        }
    }

    private function verify( $action, $nonce ) {
        if ( ! $this->nonce ) return $nonce;
        if ( ! wp_verify_nonce( $nonce, $action ) ) return null;
    }
}