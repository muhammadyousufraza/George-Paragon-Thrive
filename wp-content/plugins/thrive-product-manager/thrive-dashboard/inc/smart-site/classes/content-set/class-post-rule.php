<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

namespace TVD\Content_Sets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Post_Rule
 *
 * @package TVD\Content_Sets
 * @project : thrive-dashboard
 */
class Post_Rule extends Rule {

	/**
	 * @var string[]
	 */
	private $tax_fields
		= array(
			self::FIELD_CATEGORY => 'category',
			self::FIELD_TAG      => 'post_tag',
			self::FIELD_AUTHOR   => 'author',
		);


	/**
	 * Should be extended in child classes
	 *
	 * @param string   $query_string
	 * @param bool|int $paged    if non-false, it will return limited results
	 * @param int      $per_page number of results per page. ignored if $paged = false
	 *
	 * @return array
	 */
	public function get_items( $query_string = '', $paged = false, $per_page = 15 ) {

		/**
		 * Needed for the filter
		 */
		$this->query_string = $query_string;
		$this->paged        = $paged;
		$this->per_page     = $per_page;

		return $this->should_search_terms() ? $this->search_terms() : $this->search_posts();
	}


	/**
	 * posts_where hook callback
	 * Searches particular post by title
	 *
	 * @param string    $where
	 * @param \WP_Query $wp_query
	 *
	 * @return string
	 */
	public function title_filter( $where, $wp_query ) {

		if ( $this->field === self::FIELD_TITLE ) {
			global $wpdb;

			$operation = $this->operator === self::OPERATOR_IS ? 'LIKE' : 'NOT LIKE';

			$where .= ' AND ' . $wpdb->posts . '.post_title ' . $operation . ' \'%' . esc_sql( $wpdb->esc_like( $this->query_string ) ) . '%\'';
		}

		return $where;
	}

	/**
	 * terms_clauses hook callback
	 * Searches for a particular term by name
	 *
	 * @param $pieces
	 * @param $taxonomies
	 * @param $args
	 *
	 * @return array
	 */
	public function name_filter( $pieces, $taxonomies, $args ) {

		if ( $this->should_search_terms() ) {
			global $wpdb;

			$operation       = 'LIKE';
			$pieces['where'] .= ' AND name ' . $operation . ' ' . ' \'%' . esc_sql( $wpdb->esc_like( $this->query_string ) ) . '%\' ';
		}

		return $pieces;
	}

	/**
	 * Prepares the item for front-end
	 *
	 * @param int  $item
	 * @param bool $is_term
	 *
	 * @return array
	 */
	public function get_frontend_item( $item ) {

		if ( $this->should_search_terms() ) {
			return parent::get_frontend_item( $item );
		}

		$post = get_post( $item );

		if ( empty( $post ) ) {
			return array();
		}

		return array(
			'id'   => $post->ID,
			'text' => $this->alter_frontend_title( $post->post_title, $post->post_status ),
		);
	}

	/**
	 * Used to get all public content types needed for the content sets
	 *
	 * @return array
	 */
	public static function get_content_types() {
		$ignored_types = apply_filters( 'thrive_ignored_post_types', array(
			'attachment',
			'tcb_lightbox',
			'tcb_symbol',
			'tva-acc-restriction',
		) );

		$all = get_post_types( array( 'public' => true ) );

		$post_types = array();

		foreach ( $all as $key => $post_type ) {
			if ( in_array( $key, $ignored_types, true ) ) {
				continue;
			}

			$post_types[ $key ] = tvd_get_post_type_label( $key );
		}

		/**
		 * Allow other functionality to be injected here
		 * Used to inject the Protected File post_type in the list so it can be protected by an apprentice product
		 *
		 * @param array $post_types
		 */
		return apply_filters( 'tvd_content_sets_get_content_types', $post_types );
	}

	/**
	 * Returns true if the UI needs to perform a terms search
	 *
	 * @return bool
	 */
	private function should_search_terms() {
		return array_key_exists( $this->field, $this->tax_fields );
	}

	/**
	 * Returns the terms (category|post_tags) needed for the UI
	 * called from get_items method
	 *
	 * @return array
	 */
	private function search_terms() {
		$response = array();
		$taxonomy = $this->tax_fields[ $this->field ];

		if ( $this->field === self::FIELD_AUTHOR ) {
			$response = parent::search_users();
		} else {

			add_filter( 'terms_clauses', array( $this, 'name_filter' ), 10, 3 );
			$query = new \WP_Term_Query();

			$args = array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => false,
			);
			if ( $this->paged !== false ) {
				$args['number'] = $this->per_page;
				$args['offset'] = ( $this->paged - 1 ) * $this->per_page;
			}

			$terms = $query->query( $args );
			remove_filter( 'terms_clauses', array( $this, 'name_filter' ), 10 );

			foreach ( $terms as $term ) {
				$response[] = array(
					'id'   => $term->term_id,
					'text' => $term->name,
				);
			}
		}

		return $response;
	}

	/**
	 * Returns the posts needed for the UI
	 * called from get_items method
	 *
	 * @return array
	 */
	private function search_posts() {
		$response = array();

		add_filter( 'posts_where', array( $this, 'title_filter' ), 10, 2 );
		$query = new \WP_Query;
		$args  = array(
			'post_type'      => $this->content,
			'post_status'    => array( 'draft', 'publish' ),
			'posts_per_page' => $this->paged !== false ? $this->per_page : - 1,
		);
		if ( $this->paged !== false ) {
			$args['paged'] = $this->paged;
		}
		$posts = $query->query( $args );
		remove_filter( 'posts_where', array( $this, 'title_filter' ), 10 );

		foreach ( $posts as $post ) {
			/**
			 * Allow other plugins to hook here and remove the post from the content set dropdown
			 *
			 * @param boolean return value
			 * @param \WP_Post $post
			 */
			if ( ! apply_filters( 'tvd_content_sets_allow_select_post', true, $post ) ) {
				continue;
			}

			$response[] = array(
				'id'   => $post->ID,
				'text' => $this->alter_frontend_title( $post->post_title, $post->post_status ),
			);
		}

		return $response;
	}

	/**
	 * Test if a rule matches the given params
	 *
	 * @param int|string        $value
	 * @param \WP_Post|\WP_Term $post_or_term
	 *
	 * @return bool
	 */
	public function match_value( $value, $post_or_term ) {

		if ( $this->field === self::FIELD_PUBLISHED_DATE ) {
			$post_published_date = get_the_date( 'Y-m-d', $post_or_term );

			switch ( $this->operator ) {
				case self::OPERATOR_LOWER_EQUAL:
					return strtotime( $post_published_date ) <= strtotime( $this->value );
				case self::OPERATOR_GRATER_EQUAL:
					return strtotime( $post_published_date ) >= strtotime( $this->value );
				case self::OPERATOR_WITHIN_LAST:
					return strtotime( $post_published_date ) >= strtotime( '-' . $this->value );
				default:
					break;
			}

			return false;
		}

		if ( $this->field === self::FIELD_TAG ) {

			$common = count( array_intersect( $this->value, wp_get_post_tags( $post_or_term->ID, array( 'fields' => 'ids' ) ) ) );

			if ( $this->operator === self::OPERATOR_IS ) {
				return $common > 0;
			}

			return $common === 0;
		}

		if ( $this->field === self::FIELD_CATEGORY ) {

			$common = count( array_intersect( $this->value, wp_get_post_categories( $post_or_term->ID, array( 'fields' => 'ids' ) ) ) );

			if ( $this->operator === self::OPERATOR_IS ) {
				return $common > 0;
			}

			return $common === 0;
		}

		if ( $this->field === self::FIELD_AUTHOR ) {
			return in_array( (int) $post_or_term->post_author, $this->value, true );
		}

		return parent::match_value( $value, $post_or_term );
	}
}
