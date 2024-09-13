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
 * Class Term_Rule
 *
 * @package TVD\Content_Sets
 * @project : thrive-dashboard
 */
class Term_Rule extends Rule {

	private $option_fields
		= array(
			'tva_courses' => array(
				self::FIELD_DIFFICULTY,
				self::FIELD_TOPIC,
				self::FIELD_LABEL,
			),
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
		$response           = array();

		if ( $this->should_search_options() ) {
			$response = $this->search_option_fields();
		} elseif ( $this->should_search_users() ) {
			$response = parent::search_users();
		} else {
			add_filter( 'terms_clauses', array( $this, 'filter_terms_clauses' ), 10, 3 );
			$query = new \WP_Term_Query();
			$args  = array(
				'taxonomy'   => $this->content,
				'hide_empty' => 0,
			);
			if ( $paged !== false ) {
				$args['number'] = $per_page;
				$args['offset'] = ( $paged - 1 ) * $per_page;
			}
			$terms = $query->query( $args );
			remove_filter( 'terms_clauses', array( $this, 'filter_terms_clauses' ), 10 );

			foreach ( $terms as $term ) {
				/**
				 * Allow other plugins to hook here and remove the term from the content set dropdown
				 *
				 * @param boolean return value
				 * @param \WP_Term $term
				 */
				if ( ! apply_filters( 'tvd_content_sets_allow_select_term', true, $term ) ) {
					continue;
				}

				$response[] = array(
					'id'   => $term->term_id,
					'text' => $this->alter_frontend_title( $term->name, $this->get_term_status( $term ) ),
				);
			}
		}

		return $response;
	}

	/**
	 * @param \WP_Term $term
	 *
	 * @return string
	 */
	private function get_term_status( $term ) {
		/**
		 * Returns the status for the term
		 *
		 * @param string   $status
		 * @param \WP_Term $term
		 */
		return apply_filters( 'tvd_content_sets_get_term_status', 'publish', $term );
	}

	/**
	 * @param $pieces
	 * @param $taxonomies
	 * @param $args
	 *
	 * @return mixed
	 */
	public function filter_terms_clauses( $pieces, $taxonomies, $args ) {
		global $wpdb;

		if ( $this->field === self::FIELD_TITLE ) {
			$operation       = $this->operator === self::OPERATOR_IS ? 'LIKE' : 'NOT LIKE';
			$pieces['where'] .= ' AND lower(name) ' . $operation . ' ' . '\'%' . esc_sql( $wpdb->esc_like( strtolower( $this->query_string ) ) ) . '%\' ';
		}

		return $pieces;
	}

	/**
	 * Prepares the item for front-end
	 *
	 * @param int $item
	 *
	 * @return array
	 */
	public function get_frontend_item( $item ) {
		if ( $this->should_search_options() ) {
			$items = $this->get_option_fields();

			if ( empty( $items ) || empty( $items[ $item ] ) ) {
				return array();
			}

			return array(
				'id'   => (string) $item,
				'text' => $items[ $item ],
			);
		}

		if ( $this->should_search_users() ) {
			return parent::get_frontend_user( $item );
		}

		return $this->get_frontend_term( $item );
	}

	/**
	 * Constructs the item from a term, needed for front-end
	 *
	 * @param int $item
	 *
	 * @return array
	 */
	public function get_frontend_term( $item ) {
		$term = get_term( $item );

		if ( empty( $term ) || is_wp_error( $term ) ) {
			return array();
		}

		return array(
			'id'   => $term->term_id,
			'text' => $this->alter_frontend_title( $term->name, $this->get_term_status( $term ) ),
		);
	}

	/**
	 * @return array
	 */
	private function search_option_fields() {
		$items    = $this->get_option_fields();
		$response = array();

		foreach ( $items as $ID => $title ) {
			if ( stripos( $title, $this->query_string ) !== false ) {
				$response[] = array(
					'id'   => (string) $ID, //can be also 0 from the DB
					'text' => $title,
				);
			}
		}

		return $response;
	}

	/**
	 * @return array
	 */
	private function get_option_fields() {
		return apply_filters( 'tvd_content_sets_get_option_fields', [], $this );
	}

	/**
	 * Returns true if the system should search the option table for values
	 *
	 * @return bool
	 */
	private function should_search_options() {
		return ! empty( $this->option_fields[ $this->content ] ) && in_array( $this->field, $this->option_fields[ $this->content ] );
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

		if ( $this->should_search_options() || $this->should_search_users() ) {
			$field_value = apply_filters( 'tvd_content_sets_field_value', '', $this, $post_or_term );

			if ( $this->operator === self::OPERATOR_IS ) {
				return in_array( $field_value, $this->value );
			}

			return ! in_array( $field_value, $this->value );
		}

		return parent::match_value( $value, $post_or_term );
	}
}
