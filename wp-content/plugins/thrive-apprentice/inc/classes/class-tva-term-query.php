<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 4/24/2019
 * Time: 14:01
 */

if ( ! class_exists( 'WP_Term_Query' ) ) {
	return;
}

/**
 * Class TVA_Term_Query
 */
class TVA_Term_Query {

	/**
	 * @var array
	 */
	private $_query_vars_defaults;

	/**
	 * @var array
	 */
	private $_query_vars = array();

	/**
	 * TVA_Term_Query constructor.
	 *
	 * @param array $args
	 */
	public function __construct( $args = array() ) {
		$this->_query_vars_defaults = array(
			'meta_key'   => 'tva_order',
			'orderby'    => 'meta_value',
			'order'      => 'DESC',
			'hide_empty' => false,
			'taxonomy'   => TVA_Const::COURSE_TAXONOMY,
		);

		$this->set_query_vars( $args );
	}

	/**
	 * @param array $args
	 */
	public function set_query_vars( $args = array() ) {
		if ( isset( $args['topics'] ) ) {
			$this->_query_vars = array(
				'count'      => true,
				'meta_query' => array(
					'relation' => 'AND',
					array(
						'key'     => 'tva_topic',
						'value'   => $args['topics'],
						'compare' => 'IN',
					),
				),
			);
		} else {
			$this->_query_vars = array(
				'meta_query' => array(
					'relation' => 'AND',
				),
			);
		}

		if ( isset( $args['s'] ) ) {
			$this->_query_vars['search'] = $args['s'];
		}

		$this->_query_vars = wp_parse_args( $this->_query_vars, $this->_query_vars_defaults );
	}

	/**
	 * @return array
	 */
	private function _get_items() {
		$term_query = new WP_Term_Query( $this->_query_vars );

		return $term_query->get_terms();
	}

	/**
	 * @return array
	 */
	public function get_private_items() {
		$this->_query_vars['meta_query'][] = array(
			'key'     => 'tva_status',
			'value'   => 'private',
			'compare' => 'IN',
		);

		return $this->_get_items();
	}

	/**
	 * @return array
	 */
	public function get_public_items() {
		$this->_query_vars['meta_query'][] = array(
			'key'     => 'tva_status',
			'value'   => 'publish',
			'compare' => 'IN',
		);

		return $this->_get_items();
	}

	/**
	 * @return array
	 */
	public function get_all_terms() {
		$this->_query_vars['meta_query'][] = array(
			'key'     => 'tva_status',
			'value'   => TVA_Post::$accepted_statuses,
			'compare' => 'IN',
		);

		return $this->_get_items();
	}

	/**
	 * Get all terms which are protected either by a membership or by wp roles
	 *
	 * @return array
	 */
	public function get_protected_items() {
		$this->_query_vars['meta_query'] = array(
			'relation' => 'AND',
			array(
				'key'     => 'tva_logged_in',
				'value'   => 1,
				'compare' => '=',
			),
			array(
				'key'     => 'tva_status',
				'value'   => 'publish',
				'compare' => '=',
			),
		);

		return $this->_get_items();
	}
}
