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

global $related;
$related = new Cowobo_Related_Posts;

/**
 * This class handles all post relationships more efficiently then through taxonomy or custom fields
 *
 * @package related-posts
 */
class Cowobo_Related_Posts {

    /**
     * Runs installation and cleans db on deletion of posts.
     */
	public function __construct() {
		// Run at theme activation
		global $pagenow;
		if ( is_admin() && 'themes.php' == $pagenow && isset( $_GET['activated'] ) )
			$this->cwob_activate();
		// The various action hooks
		add_action("delete_post", array($this,'delete_relations'));
	}

    /**
     * Run when the plugin is first installed and after an upgrade
     */
	private function cwob_activate() {
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
	public function cwob_get_related_ids($postid) {
		global $wpdb;
		$query = "SELECT * FROM ".$wpdb->prefix."post_relationships	wpr WHERE wpr.post1_id = ".$postid." OR wpr.post2_id = ".$postid;
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
     * @param arr $relatedpostids of posts to be related
     * @return arr wp queries
     */
    public function create_relations($postid, $relatedpostids) {
        global $wpdb;
        $results = array();
        foreach($relatedpostids as $relatedpostid) {
 			//$this->delete_relations($postid, $relatedpostid);
			$query = "INSERT INTO ".$wpdb->prefix."post_relationships VALUES($postid, $relatedpostid)";
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
		$query = 'DELETE FROM '.$wpdb->prefix.'post_relationships WHERE post1_id = '.$post_id;
		
		//if specific link is specified delete just that link else delete all links with that post
		if($linkedid) $query .= ' AND post2_id = '.$linkedid.' OR post1_id = '.$linkedid.' AND post2_id = '.$post_id;
		else $query .= ' OR post2_id = '.$post_id;
		
		$delete = $wpdb->query($query);
	}
}