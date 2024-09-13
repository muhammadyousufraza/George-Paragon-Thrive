<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

/**
 * Class TVA_Course
 *
 * @deprecated
 * @property WP_Term term
 */
class TVA_Course extends TVA_Term {

	protected $_modules = array();

	protected $_lessons = array();

	/**
	 * This will return an un-grouped array of elements which are children of an element
	 *
	 * @param $id
	 *
	 * @return array
	 */
	public function get_element_children( $id ) {
		$elements = $this->search_in_modules( $id, $this->term->modules );

		if ( empty( $elements ) ) {
			$elements = $this->search_in_chapters( $id, $this->term->chapters );
		}

		return $elements;
	}

	/**
	 * @param $id
	 * @param $modules
	 *
	 * @return array
	 */
	public function search_in_modules( $id, $modules ) {
		$elements = array();

		if ( ! empty( $modules ) ) {
			foreach ( $modules as $module ) {

				// we've found the element let's return it's children
				if ( $module->ID == $id ) {
					if ( ! empty( $module->lessons ) ) {
						return $module->lessons;
					}

					if ( ! empty( $module->chapters ) ) {
						foreach ( $module->chapters as $chapter ) {
							$elements[] = $chapter;
							$elements   = array_merge( $elements, $chapter->lessons );
						}

						return $elements;
					}
				} else {
					$elements = $this->search_in_chapters( $id, $module->chapters );

					if ( ! empty( $elements ) ) {
						return $elements;
					}
				}
			}
		}

		return $elements;
	}

	/**
	 *
	 * @param $id
	 * @param $chapters
	 *
	 * @return array
	 */
	public function search_in_chapters( $id, $chapters ) {

		$elements = array();

		if ( ! is_array( $chapters ) ) {
			return $elements;
		}

		foreach ( $chapters as $chapter ) {
			if ( $chapter->ID == $id ) {
				return $chapter->lessons;
			}

		}

		return $elements;
	}

	/**
	 * Read some data from DB and set it on current instance
	 * so it can be localized
	 */
	public function init_data() {

		$this->get_term();
	}

	/**
	 * @return WP_Term
	 */
	public function get_term() {

		$this->get_rules();

		return parent::get_term();
	}

	/**
	 * @return array|mixed
	 */
	public function get_rules() {

		if ( empty( $this->term ) ) {
			return array();
		}

		/** @var $tva_integrations TVA_Integrations_Manager */
		global $tva_integrations;

		if ( empty( $this->term->rules ) && $tva_integrations ) {

			$this->term->rules = $tva_integrations->get_rules( $this );
		}

		return $this->term->rules ? $this->term->rules : array();
	}

	/**
	 * Magic __get for:
	 * - current instance
	 * - term property
	 * - calls get_$key() method if exist
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function __get( $key ) {

		if ( ! is_string( $key ) ) {
			return null;
		}

		if ( isset( $this->term->$key ) ) {
			return $this->term->$key;
		}

		if ( isset( $this->$key ) ) {
			return $this->$key;
		}

		$_method = 'get_' . $key;
		if ( method_exists( $this, $_method ) ) {
			return $this->$_method();
		}
	}

	public function get_id() {

		return $this->term_id;
	}

	/**
	 * Gets a term meta value
	 *
	 * @return int
	 */
	public function get_excluded() {

		$excluded = 0;

		if ( $this->term instanceof WP_Term ) {
			$excluded = (int) get_term_meta( $this->term->term_id, 'tva_excluded', true );
		}

		return $excluded;
	}

	/**
	 * Gets all lesson posts
	 *
	 * @return array []
	 */
	public function get_lessons() {

		if ( empty( $this->_lessons ) ) {
			$this->_init_lessons();
		}

		return $this->_lessons;
	}

	protected function _init_lessons() {
		if ( $this->term instanceof WP_Term ) {
			$this->_lessons = TVA_Manager::get_course_items( $this->term, [ 'post_status' => 'publish', 'post_type' => TVA_Const::LESSON_POST_TYPE ] );
		}
	}

	/**
	 * Gets all module posts
	 *
	 * @return array
	 */
	public function get_modules() {

		if ( empty( $this->_modules ) ) {
			$this->_init_modules();
		}

		return $this->_modules;
	}

	protected function _init_modules() {

		if ( $this->term instanceof WP_Term ) {

			$args = array(
				'posts_per_page' => - 1,
				'post_type'      => TVA_Const::MODULE_POST_TYPE,
				'tax_query'      => array(
					array(
						'taxonomy' => TVA_Const::COURSE_TAXONOMY,
						'terms'    => $this->term->term_id,
					),
				),
				'meta_key'       => 'tva_module_order',
				'order'          => 'ASC',
				'orderby'        => 'meta_value',
			);

			$this->_modules = get_posts( $args );
		}
	}

	/**
	 * Returns the course details
	 *
	 * @return array
	 */
	public function get_details() {
		return array(
			'course_id'          => $this->get_id(),
			'course_url'         => tva_get_course_url( $this ),
			'course_title'       => $this->get_term()->name,
			'course_description' => $this->get_term()->description,
			'course_image_url'   => (string) get_term_meta( $this->get_id(), 'tva_cover_image', true ),
		);
	}
}
