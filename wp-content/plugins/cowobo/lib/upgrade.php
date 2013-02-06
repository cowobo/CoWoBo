<?php
global $wpdb;
$cowobo_ver = get_option( 'cowobo_db_version', '0.0' );

if ( version_compare( $cowobo_ver, "0.1" ) == -1 ) {
    $sql="SELECT id FROM {$wpdb->prefix}posts";
    $posts = $wpdb->get_col($sql);
    foreach ( $posts as $post_id ) {
        if ( ! get_post_meta ( $post_id, 'cwb_points', true ) )
            update_post_meta ( $post_id, 'cwb_points', 0 );
    }
}

update_option ( 'cowobo_db_version', COWOBO_PLUGIN_VERSION );