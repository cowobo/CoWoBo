<?php
/*
 *      class-cowobo-related.php
 *
 *      Copyright 2011 Coders Without Borders
 *
 *      This program is free software; you can redistribute it and/or modify
 *      it under the terms of the GNU General Public License as published by
 *      the Free Software Foundation; either version 2 of the License, or
 *      (at your option) any later version.
 *
 *      This program is distributed in the hope that it will be useful,
 *      but WITHOUT ANY WARRANTY; without even the implied warranty of
 *      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *      GNU General Public License for more details.
 *
 *      You should have received a copy of the GNU General Public License
 *      along with this program; if not, write to the Free Software
 *      Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 *      MA 02110-1301, USA.
 *
 */

/**
 * This class handles all post relationships more efficiently then through taxonomy or custom fields
 *
 * @package CoWoBo
 * @subpackage Plugin
 */
class Cowobo_Related_Posts {

    /**
     * Runs installation and cleans db on deletion of posts.
     */
	public function __construct() {
		global $pagenow;
		if ( is_admin() && 'themes.php' == $pagenow && isset( $_GET['activated'] ) )
			$this->activate();
		// The various action hooks
		add_action("delete_post", array($this,'delete_relations'));
	}

    /**
     * Run when the plugin is first installed and after an upgrade
     */
	private function activate() {
		global $wpdb;
		// Check if post_relationships table exists, if not, create it
		$query = "SHOW TABLES LIKE '".$wpdb->prefix."post_relationships'";
		if( !count( $wpdb->get_results( $query ) ) ) {
			$query = "CREATE TABLE ".$wpdb->prefix."post_relationships (
						post1_id bigint(20) unsigned NOT NULL,
						post2_id bigint(20) unsigned NOT NULL,
						PRIMARY KEY  (post1_id,post2_id)
					)";
			$create = $wpdb->query( $query );
		}
	}

	/**
     * Get the related post ids for a post
     *
     * @param int post_id
     * @return array of related posts ids
     */
	public function get_related_ids($postid) {
		global $wpdb;
		$query = $wpdb->prepare ( "SELECT * FROM ".$wpdb->prefix."post_relationships wpr WHERE wpr.post1_id = %d OR wpr.post2_id = %d", $postid, $postid );
		$ids = $wpdb->get_results($query);
		//return $ids;
		if($ids):
			foreach($ids as $id):
				if($id->post1_id == $postid) $related[] = $id->post2_id;
				else $related[] = $id->post1_id;
			endforeach;
			return $related;
		endif;
	}

    /**
     * Create relations for a post.
     *
     * @global obj $wpdb
     * @param int $postid of the post to be related
     * @param mixed $relatedpostids String or array of posts to be related
     * @return arr wp queries
     */
    public function create_relations($postid, $relatedpostids) {
        global $wpdb;
        if ( ! is_array ( $relatedpostids ) ) $relatedpostids = array ( $relatedpostids );
        $results = array();
        foreach($relatedpostids as $relatedpostid) {
 			$this->delete_relations($postid, $relatedpostid);
			$query = $wpdb->prepare ( "INSERT INTO ".$wpdb->prefix."post_relationships VALUES(%s, %s)", $postid, $relatedpostid );
			$results[] = $wpdb->query($query);
        }
        return $results;
    }

    /**
     * Delete relationships for a post
     *
     * @param int post_id
     * @param int $linkedid of another post
     * @return wp query
     */

	public function delete_relations($post_id, $linkedid = false) {
		global $wpdb;
		$query = $wpdb->prepare ( 'DELETE FROM '.$wpdb->prefix.'post_relationships WHERE post1_id = %d ', $post_id );

		//if specific link is specified delete just that link else delete all links with that post
		if($linkedid) $query .= $wpdb->prepare ( ' AND post2_id = %d OR post1_id = %d AND post2_id = %d', $linkedid, $linkedid, $post_id );
		else $query .= $wpdb->prepare ( ' OR post2_id = %d', $post_id );

		$delete = $wpdb->query($query);
	}

    //Link post to another related post
    public function link_post(){
        global $post;
        $this->create_relations( $post->ID, cowobo()->query->linkto );
        do_action ( 'cowobo_link_created', $post->ID, cowobo()->query->linkto );
        cowobo()->add_notice( "You post is now linked!", 'link_created' );
    }
}