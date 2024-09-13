<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TVA_Setting
 *
 * @project  : thrive-apprentice
 */
class TVA_Setting implements JsonSerializable {

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var string
	 */
	private $value;

	/**
	 * @var string
	 */
	private $category;

	/**
	 * TVA_Setting constructor.
	 *
	 * @param string $name
	 * @param string $category
	 * @param string $value
	 */
	public function __construct( $name, $category = 'general', $value = null ) {
		$this->name     = $name;
		$this->category = $category;
		$this->value    = $value;
	}

	/**
	 * Saves the settings
	 *
	 * @param mixed $value
	 *
	 * @return boolean
	 */
	public function set_value( $value ) {

		$this->value = $value;

		return update_option( $this->get_option_name( $this->name ), $this->value );
	}

	/**
	 * Returns the setting from the database
	 *
	 * @return mixed
	 */
	public function get_value() {

		if ( isset( $this->value ) ) {
			return $this->value;
		}

		$this->value = get_option( $this->get_option_name( $this->name ), '' );

		if ( $this->value === '' ) { //The default value if the option doesn't exist. We can not use empty here

			/**
			 * Backwards Compatibility.
			 *
			 * Read from old settings if it doesn't exists
			 */
			$this->value = $this->get_old_setting( $this->name );

			if ( $this->value === null ) {
				// avoid extra future calls.
				$this->value = '';
			}
		}

		if ( is_numeric( $this->value ) ) {
			$this->value = (int) $this->value;
		}

		return $this->value;
	}

	/**
	 *
	 *
	 * @return array
	 */
	public function to_array() {
		return array(
			'category' => $this->category,
			'name'     => $this->name,
			'value'    => $this->get_value(),
		);
	}

	/**
	 * @param $name
	 *
	 * @return mixed|null
	 */
	private function get_old_setting( $name ) {
		$old_settings = TVA_Settings::instance()->get_settings();

		switch ( $name ) {

			case 'welcome_message':

				return TVA_Sendowl_Settings::instance()->get_th_message();

			case 'load_scripts':

				return (int) get_option( 'tva_load_all_scripts', 0 );

			default:
				break;
		}

		$so_settings = array(
			'checkout_page',
			'thankyou_page',
			'thankyou_multiple_page',
			'thankyou_page_type',
			'account_keys',
		);

		if ( in_array( $name, $so_settings ) ) {

			$old_settings = TVA_Sendowl_Settings::instance()->get_settings();
		}

		if ( isset( $old_settings[ $name ] ) ) {

			$old_setting = $old_settings[ $name ];

			if ( in_array( $name, tva_get_settings_manager()->pages_indexes() ) && is_array( $old_setting ) && isset( $old_setting['ID'] ) ) {
				$old_setting = $old_setting['ID'];
			}

			return $old_setting;
		}

		return null;
	}

	/**
	 * Returns a setting option name from a $suffix
	 *
	 * @param string $suffix
	 *
	 * @return string
	 */
	private function get_option_name( $suffix = '' ) {
		if ( $suffix === '' ) {
			$suffix = $this->name;
		}

		return 'tva_setting_' . $suffix;
	}

	/**
	 * Get a setting value
	 *
	 * @param string $name
	 *
	 * @return int|mixed|string|null
	 */
	#[\ReturnTypeWillChange]
	public static function get( $name ) {
		$instance = new static( $name );

		return $instance->get_value();
	}

	/**
	 * Remove the option from the database
	 *
	 * @return TVA_Setting
	 */
	public function delete() {
		delete_option( $this->get_option_name() );

		return $this;
	}

	#[\ReturnTypeWillChange]
	public function jsonSerialize() {
		return $this->to_array();
	}
}
