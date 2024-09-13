<?php

/**
 * Class TQB_Progress_Settings
 *
 * Manage Progress bar settings
 */
class TQB_Progress_Settings {

	/**
	 * @var null
	 */
	private $_quiz_id;

	/**
	 * @var string Quiz Style ID
	 */
	protected $_quiz_style_id;

	/**
	 * @var array
	 */
	private $_quiz_style_settings = array();

	/**
	 * @var array
	 */
	private $_general_settings = array();

	/**
	 * TQB_Progress_Settings constructor.
	 *
	 * @param int $quiz_id
	 * @param int $quiz_style_id
	 */
	public function __construct( $quiz_id, $quiz_style_id = null ) {

		$this->_quiz_id = $quiz_id;
		$this->_set_quiz_style( $quiz_style_id );
		$this->_init_quiz_style_settings();
	}

	/**
	 * Set quiz style id
	 *
	 * @param string $quiz_style
	 */
	protected function _set_quiz_style( $quiz_style ) {

		if ( empty( $quiz_style ) ) {
			$quiz_style = TQB_Post_meta::get_quiz_style_meta( $this->_quiz_id );
		}

		$this->_quiz_style_id = (string) $quiz_style;
	}

	/**
	 * Here settings are moved from the old format to the new one
	 *
	 * Set $_quiz_style_settings based on what exists in DB
	 */
	private function _init_quiz_style_settings() {

		$this->_quiz_style_settings = TQB_Post_meta::get_quiz_progress_settings_meta( $this->_quiz_id );

		$key      = $this->_get_style_key();
		$defaults = $this->_get_defaults( $this->_get_quiz_style_id() );

		$style_settings = array();

		/**
		 * Backwards compatibility check
		 * Migrate data from old format to the new one
		 */
		if ( isset( $this->_quiz_style_settings['display_progress'] ) ) {
			$style_settings = $this->_quiz_style_settings;

			$this->_quiz_style_settings = array();
		}

		if ( isset( $this->_quiz_style_settings[ $key ] ) ) {
			$style_settings = $this->_quiz_style_settings[ $key ];
		}

		$style_settings = array_merge( $defaults, $style_settings );

		$this->_quiz_style_settings[ $key ] = $style_settings;
	}

	/**
	 * Get progress bar general settings
	 *
	 * @return array
	 */
	public function get_general_settings() {

		$settings = TQB_Post_meta::get_quiz_progress_general_settings( $this->_quiz_id );
		$defaults = array(
			'label_text'        => __( 'COMPLETED', 'thrive-quiz-builder' ),
			'display_progress'  => 0,
			'percent_type'      => 'percentage_completed',
			'progress_position' => 'position_top',
		);

		$style_settings = $this->_quiz_style_settings[ $this->_get_style_key() ];

		/**
		 * Backwards compatibility
		 */
		if ( isset( $style_settings['label_text'] ) ) {
			$settings['label_text']        = $style_settings['label_text'];
			$settings['display_progress']  = $style_settings['display_progress'];
			$settings['percent_type']      = $style_settings['percent_type'];
			$settings['progress_position'] = $style_settings['progress_position'];
		}

		$settings = array_merge( $defaults, $settings );

		$settings['display_progress'] = (int) $settings['display_progress'];

		return $settings;
	}

	/**
	 * Get skin settings
	 *
	 * @return array
	 */
	public function get_skin_settings() {

		return $this->_quiz_style_settings[ $this->_get_style_key() ];
	}

	/**
	 * Get progress bar settings based on quiz skin/style
	 *
	 * @return array
	 */
	public function get() {

		$general           = $this->get_general_settings(); //get general options
		$progress_settings = $this->get_skin_settings(); //get progress skin settings

		return array_merge( $progress_settings, $general );
	}

	/**
	 * Get quiz style id
	 *
	 * @return string
	 */
	protected function _get_quiz_style_id() {
		if ( ! $this->_quiz_style_id ) {
			$this->_quiz_style_id = TQB_Post_meta::get_quiz_style_meta( $this->_quiz_id );
		}

		return (string) $this->_quiz_style_id;
	}

	/**
	 * Get quiz style key
	 *
	 * @return string
	 */
	private function _get_style_key() {
		return 'style_id_' . $this->_get_quiz_style_id();
	}

	/**
	 * Set $_quiz_style_settings and $_general_settings
	 *
	 * @param array $data
	 *
	 * @return $this
	 */
	public function set_data( $data ) {

		$this->_general_settings = array(
			'label_text'        => $data['label_text'],
			'display_progress'  => (int) $data['display_progress'],
			'percent_type'      => $data['percent_type'],
			'progress_position' => $data['progress_position'],
		);

		unset( $data['label_text'] );
		unset( $data['display_progress'] );
		unset( $data['percent_type'] );
		unset( $data['progress_position'] );

		$this->_quiz_style_settings[ $this->_get_style_key() ] = $data;

		return $this;
	}

	/**
	 * Save progress bar settings
	 */
	public function save() {

		TQB_Post_meta::update_quiz_progress_settings_meta( $this->_quiz_id, $this->_quiz_style_settings );
		TQB_Post_meta::update_quiz_progress_general_settings( $this->_quiz_id, $this->_general_settings );
	}

	/**
	 * @param string $quiz_style_id
	 *
	 * @return array
	 */
	protected static function _get_defaults( $quiz_style_id = null ) {

		$data = array(
			array(
				'fill_color'        => '#1b9213',
				'next_step_color'   => '#96d191',
				'background_color'  => '#fff',
				'label_color'       => '#242424',
				'font_size'         => 12,
				'percent_type'      => 'percentage_completed',
				'progress_position' => 'position_top',
				'quiz_style'        => array( '0' ),
			),
			array(
				'fill_color'        => '#a6acb1',
				'next_step_color'   => '#141d25',
				'background_color'  => '#223240',
				'label_color'       => '#a6acb1',
				'font_size'         => 12,
				'percent_type'      => 'percentage_completed',
				'progress_position' => 'position_top',
				'quiz_style'        => array( '1' ),
			),
			array(
				'fill_color'        => '#1b9213',
				'next_step_color'   => '#96d191',
				'background_color'  => '#fff',
				'label_color'       => '#242424',
				'font_size'         => 12,
				'percent_type'      => 'percentage_completed',
				'progress_position' => 'position_top',
				'quiz_style'        => array( '2' ),
			),
			array(
				'fill_color'        => '#1b9213',
				'next_step_color'   => '#96d191',
				'background_color'  => '#ffffff',
				'label_color'       => '#fff',
				'font_size'         => 12,
				'percent_type'      => 'percentage_completed',
				'progress_position' => 'position_top',
				'quiz_style'        => array( '3' ),
			),
			array(
				'fill_color'        => '#1b9213',
				'next_step_color'   => '#96d191',
				'background_color'  => '#fff',
				'label_color'       => '#242424',
				'font_size'         => 12,
				'percent_type'      => 'percentage_completed',
				'progress_position' => 'position_top',
				'quiz_style'        => array( '4' ),
			),
			array(
				'fill_color'        => '#8868e0',
				'next_step_color'   => '#cfc2f2',
				'background_color'  => '#fff',
				'label_color'       => '#362761',
				'font_size'         => 12,
				'percent_type'      => 'percentage_completed',
				'progress_position' => 'position_top',
				'quiz_style'        => array( '5' ),
			),
		);

		if ( null !== $quiz_style_id ) {
			$data = array_filter(
				$data,
				function ( $item ) use ( $quiz_style_id ) {
					if ( in_array( $quiz_style_id, $item['quiz_style'] ) ) {
						return $item;
					}
				}
			);

			$data = array_values( $data );
			$data = isset( $data[0] ) ? $data[0] : array();
		}

		return $data;
	}

	/**
	 * Get default progress bat values based on quiz style
	 *
	 * @param string $quiz_style
	 *
	 * @return array
	 */
	public static function get_quiz_style_defaults( $quiz_style ) {
		return self::_get_defaults( (string) $quiz_style );
	}
}

/**
 * Wrapper over TQB_Progress_Settings instance
 *
 * @param int  $quiz_id
 * @param null $skin_id
 *
 * @return TQB_Progress_Settings
 */
function tqb_progress_settings_instance( $quiz_id, $skin_id = null ) {
	return new TQB_Progress_Settings( $quiz_id, $skin_id );
}
