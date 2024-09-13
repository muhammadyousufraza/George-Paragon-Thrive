<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

use Thrive\Theme\Integrations\WooCommerce\Main;

/**
 * Class Thrive_Theme_DB
 */
class Thrive_Theme_DB {

	/**
	 * Use general singleton methods
	 */
	use Thrive_Singleton;

	/**
	 * @var wpdb
	 */
	protected $wpdb;

	/**
	 * Thrive_Theme_DB constructor.
	 */
	public function __construct() {
		global $wpdb;

		$this->wpdb = $wpdb;
	}

	/**
	 * Get all the custom fields keys that are used by all the posts, along with specific custom field values to show for the current post.
	 * This is done by getting all the existing meta fields for the current post type, then filtering them.
	 *
	 * @param $post_id
	 *
	 * @return array
	 */
	public function get_custom_fields( $post_id ) {
		$query_args = [];

		/* the post type of the given post */
		$query_args[] = apply_filters( 'thrive_theme_custom_fields_post_type', get_post_type( $post_id ) );

		/* generate an array of static text with placeholders for the query args, then add operators between the conditions */
		$whitelist_conditions = array_fill( 0, count( TCB_Custom_Fields_Shortcode::$whitelisted_fields ), 'meta_key = %s' );
		if ( empty( $whitelist_conditions ) ) {
			$allowed_fields = '1';
		} else {
			$allowed_fields = implode( ' OR ', $whitelist_conditions );
		}

		$blacklist_conditions = array_fill( 0, count( TCB_Custom_Fields_Shortcode::$blacklisted_fields ), 'meta_key NOT LIKE %s' );
		$not_excluded_fields  = '(' . implode( ' AND ', $blacklist_conditions ) . ')';

		$query_args = array_merge( $query_args, TCB_Custom_Fields_Shortcode::$whitelisted_fields, TCB_Custom_Fields_Shortcode::$blacklisted_fields );

		/*
		 * Get all the meta keys that are used by all the posts with this post type and also satisfy the conditions built above
		 * in order to understand the conditions better, do a print_r( $query )
		 */
		$query = "SELECT DISTINCT meta_key FROM {$this->wpdb->prefix}postmeta pm INNER JOIN {$this->wpdb->prefix}posts p
				  ON pm.post_id = p.ID
				  WHERE post_type = %s AND 
				  ( {$allowed_fields} OR {$not_excluded_fields} )";

		$query = $this->wpdb->prepare( $query, $query_args );

		return $this->wpdb->get_col( $query );
	}

	/**
	 * Get the next / previous page that's available for wizard previews (homepage step).
	 *
	 * @param int    $current_page_id ID of the page being previewed.
	 * @param string $dir             'next' or 'prev'
	 *
	 * @return array
	 */
	public function get_wizard_adjacent_page( $current_page_id, $dir = 'next' ) {
		$current_page = get_post( $current_page_id );

		/* Cannot use a simple variation of get_posts because it will not return correct results when pages have the same `post_modified` date */
		$sql = $this->wpdb->prepare(
			"SELECT `ID` AS `id`, `post_title` AS `label` FROM {$this->wpdb->posts} 
				WHERE 
					post_type = 'page'
				AND `ID` <> %d
				AND 
				( 
					post_modified {{cmp}} %s
					OR ( post_modified = %s AND ID {{cmp}} %d )
				)
				AND post_status = 'publish' ORDER BY `post_modified` {{order}}, `ID` {{order}} LIMIT 1",
			$current_page_id,
			$current_page->post_modified,
			$current_page->post_modified,
			$current_page_id
		);

		$sql = str_replace(
			[ '{{cmp}}', '{{order}}' ],
			[
				$dir === 'next' ? '<' : '>',
				$dir === 'next' ? 'DESC' : 'ASC',
			],
			$sql
		);

		$page = $this->wpdb->get_row( $sql, ARRAY_A );
		if ( ! empty( $page ) ) {
			$page['url'] = get_permalink( $page['id'] );
		}

		return (array) $page;
	}

	/**
	 * Adds a WHERE clause to WP Query's get_posts function that makes sure only posts having more than $min_content_size characters are returned.
	 *
	 * @param int $min_characters Number of characters to compare against.
	 */
	public static function add_post_content_length_filter( $post_type, $min_characters = 600 ) {
		$min_characters = (int) $min_characters;

		add_filter( 'posts_where', static function ( $where ) use ( $min_characters, $post_type ) {
			global $wpdb;

			$length_filter = " AND LENGTH( {$wpdb->posts}.post_content ) > {$min_characters}";

			/* This is temporary until we will have demo content for woocommerce */
			if ( $post_type === Main::POST_TYPE ) {
				$where = str_replace( $length_filter, '', $where );
			} else {
				if ( strpos( $where, 'LENGTH' ) === false ) {
					$where = "{$length_filter} {$where}";
				}
			}

			return $where;
		} );
	}
}

if ( ! function_exists( 'thrive_theme_db' ) ) {
	/**
	 * Return Thrive_Theme_DB instance
	 *
	 * @return Thrive_Theme_DB
	 */
	function thrive_theme_db() {
		return Thrive_Theme_DB::instance();
	}
}
