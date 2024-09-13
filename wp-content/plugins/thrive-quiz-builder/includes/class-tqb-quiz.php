<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-quiz-builder
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Quiz Class
 *
 * @property string $post_title
 * @property string $ID
 * @property array  $results
 */
class TQB_Quiz implements JsonSerializable {

	/**
	 * @var WP_Post
	 */
	protected $post;

	/**
	 * @var array
	 */
	protected $_data = array();

	/**
	 * @var TQB_Structure_Manager
	 */
	private $structure_manager;

	/**
	 * Stores a cached value for the result of "is_valid()". Used in cases where is_valid() is called multiple times for a quiz
	 *
	 * @var bool
	 */
	protected $valid;

	/**
	 * Quiz class constructor
	 *
	 * @param int|WP_Post $data
	 */
	public function __construct( $data ) {

		if ( is_int( $data ) ) {
			$this->post = get_post( $data );
		} elseif ( $data instanceof WP_Post ) {
			$this->post = $data;
		}

		if ( $this->post instanceof WP_Post ) {
			$this->structure_manager = new TQB_Structure_Manager( $this->post->ID );
			$this->quiz_manager      = new TQB_Quiz_Manager( $this->post );
		}
	}

	/**
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function __get( $key ) {
		$value = null;

		if ( isset( $this->_data[ $key ] ) ) {
			$value = $this->_data[ $key ];
		} elseif ( $this->post instanceof WP_Post && isset( $this->post->$key ) ) {
			$value = $this->post->$key;
		} elseif ( method_exists( $this, 'get_' . $key ) ) {
			$method_name = 'get_' . $key;
			$value       = $this->$method_name();
		}

		return $value;
	}

	/**
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return void
	 */
	public function __set( $key, $value ) {
		$this->_data[ $key ] = $value;
	}

	/**
	 * @param string $key
	 *
	 * @return bool
	 */
	public function __isset( $key ) {
		return isset( $this->_data[ $key ] ) || ( $this->post instanceof WP_Post && $this->post->$key );
	}

	/**
	 * @return WP_Post|null
	 */
	public function get_post() {
		return $this->post;
	}

	/**
	 * @return int
	 */
	public function get_id() {
		return $this->post->ID;
	}

	/**
	 * @return string
	 */
	public function get_type() {
		return TQB_Post_meta::get_quiz_type_meta( $this->get_id(), true );
	}

	/**
	 * @return int
	 */
	public function is_valid() {
		if ( null === $this->valid ) {
			$validation  = $this->structure_manager->get_display_availability();
			$this->valid = $validation['valid'];
		}

		return (int) $this->valid;
	}

	/**
	 * @return int
	 */
	public function is_scroll_enabled() {
		$scroll_settings = TQB_Post_meta::get_quiz_scroll_settings_meta( $this->get_id() );

		return (int) $scroll_settings['enable_scroll'];
	}

	/**
	 * Returns the quiz results page type (can be page or URL)
	 *
	 * @return string
	 */
	public function get_result_page_type() {
		return (string) get_post_meta( $this->get_structure_property( 'results' ), 'tqb_results_type', true );
	}

	/**
	 * @return array
	 */
	public function get_results() {

		if ( ! isset( $this->_data['results'] ) ) {
			$this->_data['results'] = array();

			if ( $this->get_type() === Thrive_Quiz_Builder::QUIZ_TYPE_PERSONALITY ) {
				global $tqbdb;

				$this->_data['results'] = $tqbdb->get_quiz_results( $this->get_id() );
			} elseif ( in_array( $this->get_type(), array(
				Thrive_Quiz_Builder::QUIZ_TYPE_NUMBER,
				Thrive_Quiz_Builder::QUIZ_TYPE_PERCENTAGE,
				Thrive_Quiz_Builder::QUIZ_TYPE_RIGHT_WRONG,
			), true ) ) {
				/**
				 * This function may can consume many resources
				 */
				$this->_data['results'] = tqb_compute_quiz_absolute_max_min_values( $this->get_id(), true );
			}
		}


		return $this->_data['results'];
	}

	/**
	 * Returns the optin page ID
	 * If the quiz has no optin page it returns 0
	 *
	 * @return int
	 */
	public function get_optin_gate_id() {
		return (int) $this->get_structure_property( 'optin' );
	}

	/**
	 * Returns true if the optin page exists AND the optin page is set to be shown for the logged_in users
	 *
	 * @return bool
	 */
	public function optin_gate_is_enabled() {
		return ! empty( $this->get_optin_gate_id() ) && $this->structure_manager->should_show_optin_gate_page();
	}

	/**
	 * @param array $filters
	 *
	 * @return TQB_Quiz[]
	 */
	public static function get_items( $filters = array() ) {
		$defaults = array(
			'posts_per_page' => - 1,
			'post_type'      => TQB_Post_types::QUIZ_POST_TYPE,
			'orderby'        => 'post_date',
			'order'          => 'ASC',
		);

		//Cache get_posts request if this is heavy used
		$posts = get_posts( array_merge( $defaults, $filters ) );
		$data  = array();

		foreach ( $posts as $post ) {
			$data[] = new TQB_Quiz( $post );
		}

		return $data;
	}

	/**
	 * @return TQB_Quiz[]
	 */
	public static function get_items_for_architect_integration() {
		$return = array();

		foreach ( static::get_items() as $quiz ) {
			if ( ! $quiz->is_valid() ) {
				continue;
			}

			$return[ $quiz->get_id() ] = $quiz;
		}

		return $return;
	}

	/**
	 * tqb_compute_quiz_absolute_max_min_values function is a very high query function.
	 * Therefore it should be called async
	 *
	 * @return array
	 */
	public static function get_items_for_apprentice_integration() {
		$return = array();

		foreach ( static::get_items_for_architect_integration() as $ID => $quiz ) {

			$return[ $quiz->get_id() ] = array_merge( $quiz->jsonSerialize(), array(
				'results' => $quiz->get_results(),
			) );
		}

		return $return;
	}

	#[\ReturnTypeWillChange]
	public function jsonSerialize() {
		return array(
			'ID'                => $this->get_id(),
			'post_title'        => $this->post_title,
			'type'              => $this->get_type(),
			'is_valid'          => $this->is_valid(),
			'auto_scroll'       => $this->is_scroll_enabled(),
			'results_page_type' => $this->get_result_page_type(),
		);
	}

	/**
	 * Returns the quiz structure
	 *
	 * @return array|null
	 */
	private function get_structure() {
		return $this->structure_manager->get_quiz_structure_meta();
	}

	/**
	 * Returns a structure item
	 *
	 * @param string $key
	 *
	 * @return mixed|null
	 */
	private function get_structure_property( $key ) {
		$structure = $this->get_structure();

		return is_array( $structure ) && isset( $structure[ $key ] ) ? $structure[ $key ] : null;
	}
}
