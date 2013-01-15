<?php
/*
 *      class-cowobo-similar.php
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

global $similar;
$similar = new Cowobo_Similar_Posts;

/**
 * This class retrieves posts similar to the current post
 *
 * @package related-posts
 */
class Cowobo_Similar_Posts {

    /**
     * Runs installation and cleans db on deletion of posts.
     */
	public function __construct() {
		// Run at theme activation
		global $pagenow;
		if ( is_admin() && 'themes.php' == $pagenow && isset( $_GET['activated'] ) )
			$this->activate_similar_posts();
	}

	/**
     *  Installs similar posts functionality on the db
     */
	public function activate_similar_posts() {
		require(dirname(__FILE__).'/../../../../' .'wp-config.php');

		global $table_prefix;

		$connexion = mysql_connect(DB_HOST,DB_USER,DB_PASSWORD) or die("Can't connect.<br />".mysql_error());
		$dbconnexion = mysql_select_db(DB_NAME, $connexion);

		if ( !$dbconnexion ) {
			echo mysql_error();
			die();
		}
		$sql_run = 'ALTER TABLE `'.$table_prefix.'posts` ENGINE = MYISAM' ;
		$sql_result = mysql_query($sql_run);
		if ($sql_result) {
			$sql_run = 'ALTER TABLE `'.$table_prefix.'posts` ADD FULLTEXT `post_related` ( `post_name` , `post_content` )';
			$sql_result = mysql_query($sql_run);
		}

		if ($sql_result)
			return true;
		else {
			echo "Something in the installation of the related posts plugin went wrong, please contact your nearest coding angel...";
			echo mysql_error();
			die;
			return false;
		}
	}

    /**
     * Find similar posts by content
     *
     * @param int (optional) limit the number of posts. Standard 10 for all posts, 30 for categorized.
     * @param bool (optional) if true, method returns posts in a multidimensional array of categories
     * @param int (optional) postid if not in the loop.
     * @return array sorted similar posts by popularity and score
     */
	public function find_similar_posts ( $limit = false, $cat = false, $postid = false ) {
		global $wpdb;

        if ( $postid ) $post = get_post ( $postid );
        else global $post;

		$terms = $this->current_post_keywords( $post );

		$time_difference = get_settings('gmt_offset');
		$now = gmdate("Y-m-d H:i:s",(time()+($time_difference*3600)));

        if ( ! $limit )
            $limit = ( $cat ) ? 30 : 10;

		// Match
		$sql = "SELECT ID, post_title, post_content, "
			. "MATCH (post_name, post_content) "
			. "AGAINST ('$terms') AS score "
			. "FROM {$wpdb->posts} WHERE "
			. "MATCH (post_name, post_content) "
			. "AGAINST ('$terms') "
			. "AND post_date <= '$now' "
			. "AND (post_status IN ( 'publish', 'static' ) && ID != '{$post->ID}') "
			. "AND post_password ='' "
            . "AND post_type = 'post' "
			. "ORDER BY score DESC LIMIT $limit";
		$results = $wpdb->get_results($sql);
		$results = $this->sort_similar_posts ( $results );

        if ( ! $cat ) return $results;

        return $this->categorize_posts ( $results );
	}

    /**
     * Takes an array of posts and returns an array of categories holding the posts
     * @param arr $results
     * @return arr Postobjects in an array of categories.
     */
    protected function categorize_posts ( $results ) {
        $cat_results = array();
        foreach ( $results as $result ) {
            $category = cwob_get_category ( $result->ID )->term_id;
            if ( array_key_exists ( $category, $cat_results ) )
                $cat_results[ $category ][] = $result;
            else
                $cat_results[ $category ] = array ( $result );
        }

		return $cat_results;
    }

    /**
     * Sort posts based on populairty and similarity scores
     *
     * @param array posts with scores
     * @return array sorted posts
     */
	private function sort_similar_posts ( $posts ) {
		// Get the highest matching score
		$highest_score = 0;
		foreach ( $posts as $post ) {
			$highest_score = ( $highest_score < $post->score ) ? $post->score : $highest_score;
		}
		// Get the highest popularity
		$highest_popularity = 0;
		$popularities = array();
		foreach ( $posts as $post ) {
			$popularity = $popularities[] = get_post_meta ( $post->ID, 'cowobo_popularity', true);
			$highest_popularity = ( $highest_popularity < $popularity ) ? $popularity : $highest_popularity;
		}

		// Grade them posts
		$i = 0;
		$popularity_score = array();
		foreach ( $posts as $postpos => $post ) {
			$score = $post->score / $highest_score; // Match on a scale from 0-1
			// $popularity = get_post_meta ( $post->ID, 'cowobo_popularity', true);
			$popularity = ( $highest_popularity > 0 ) ? $popularities[$i] / $highest_popularity : 1; // Match on a scale from 0-1
			$popularity_score[$postpos] = $score + ( $popularity / $this->popularity_weight );
			$i++;
		}
		arsort ( $popularity_score );
		$results = array();
		foreach ( $popularity_score as $postpos => $score ) {
			$results[] = $posts[$postpos];
		}
		return $results;
	}

    /**
     * Extract the keywords from the postobject
     *
     * @param obj (optional) post, if not in the loop
     * @param int (optional) number of terms to get
     * @return array current post keywords
     */
	private function current_post_keywords( $post = false, $num_to_ret = 20 ) {
		if ( ! $post ) global $post;

		$string =	$post->post_title.' '.
				str_replace('-', ' ', $post->post_name).' '.
				$post->post_content;

		// Remove punctuation
		$wordlist = preg_split('/\s*[\s+\.|\?|,|(|)|\-+|\'|\"|=|;|&#0215;|\$|\/|:|{|}]\s*/i', $string);

		// Build array of words and number of times they occur
		$all = array_count_values($wordlist);

		// Remove words without information
		$stopwords = array( 'coders', 'without', 'borders', '', 'a', 'an', 'the', 'and', 'of', 'i', 'to', 'is', 'in', 'with', 'for', 'as', 'that', 'on', 'at', 'this', 'my', 'was', 'our', 'it', 'you', 'we', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '10', 'about', 'after', 'all', 'almost', 'along', 'also', 'amp', 'another', 'any', 'are', 'area', 'around', 'available', 'back', 'be', 'because', 'been', 'being', 'best', 'better', 'big', 'bit', 'both', 'but', 'by', 'c', 'came', 'can', 'capable', 'control', 'could', 'course', 'd', 'dan', 'day', 'decided', 'did', 'didn', 'different', 'div', 'do', 'doesn', 'don', 'down', 'drive', 'e', 'each', 'easily', 'easy', 'edition', 'end', 'enough', 'even', 'every', 'example', 'few', 'find', 'first', 'found', 'from', 'get', 'go', 'going', 'good', 'got', 'gt', 'had', 'hard', 'has', 'have', 'he', 'her', 'here', 'how', 'if', 'into', 'isn', 'just', 'know', 'last', 'left', 'li', 'like', 'little', 'll', 'long', 'look', 'lot', 'lt', 'm', 'made', 'make', 'many', 'mb', 'me', 'menu', 'might', 'mm', 'more', 'most', 'much', 'name', 'nbsp', 'need', 'new', 'no', 'not', 'now', 'number', 'off', 'old', 'one', 'only', 'or', 'original', 'other', 'out', 'over', 'part', 'place', 'point', 'pretty', 'probably', 'problem', 'put', 'quite', 'quot', 'r', 're', 'really', 'results', 'right', 's', 'same', 'saw', 'see', 'set', 'several', 'she', 'sherree', 'should', 'since', 'size', 'small', 'so', 'some', 'something', 'special', 'still', 'stuff', 'such', 'sure', 'system', 't', 'take', 'than', 'their', 'them', 'then', 'there', 'these', 'they', 'thing', 'things', 'think', 'those', 'though', 'through', 'time', 'today', 'together', 'too', 'took', 'two', 'up', 'us', 'use', 'used', 'using', 've', 'very', 'want', 'way', 'well', 'went', 'were', 'what', 'when', 'where', 'which', 'while', 'white', 'who', 'will', 'would', 'your');
		foreach ($stopwords as $stopword) {
			 unset($all[$stopword]);
		}

		// Sort it, count it, slice it
		arsort($all, SORT_NUMERIC);
		$num_words = count($all);
		$num_to_ret = $num_words > $num_to_ret ? $num_to_ret : $num_words;
		$outwords = array_slice($all, 0, $num_to_ret);

		return implode(' ', array_keys($outwords));
	}
}