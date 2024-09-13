<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Buy_Now;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Generic
 *
 * This is an abstract class that provides a blueprint for payment integration classes.
 * It contains methods that are common to all payment integrations.
 */
abstract class Generic {

	/**
	 * @var array $data The data associated with the payment integration.
	 */
	protected $data;

	/**
	 * Generic constructor.
	 *
	 * @param array $data The data associated with the payment integration.
	 */
	public function __construct( $data ) {
		$this->data = $data;
	}

	/**
	 * Get the list of available integrations.
	 *
	 * @return array The list of available integrations.
	 */
	public static function get_integrations() {
		return [
			'stripe'         => __( 'Stripe', 'thrive-apprentice' ),
			'custom_payment' => __( 'Custom payment', 'thrive-apprentice' ),
		];
	}

	/**
	 * Check if the integration is valid.
	 *
	 * @return bool Always returns true.
	 */
	public function is_valid() {
		return true;
	}

	/**
	 * Get the URL associated with the integration.
	 *
	 * This method must be implemented by all subclasses.
	 *
	 * @return string The URL associated with the integration.
	 */
	abstract public function get_url();

	/**
	 * Get the class name associated with the integration.
	 *
	 * @param string $identifier The identifier of the integration.
	 *
	 * @return string The class name associated with the integration.
	 */
	public static function get_class_name( $identifier ) {
		$identifier = str_replace( '_', ' ', $identifier );
		$identifier = ucwords( $identifier );

		return str_replace( ' ', '_', $identifier );
	}

	/**
	 * Get an instance of the integration class.
	 *
	 * @param array $data The data associated with the integration.
	 *
	 * @return Generic|null An instance of the integration class, or null if the class does not exist.
	 */
	public static function get_instance( $data = [] ) {
		$instance = null;
		if ( ! empty( $data['integration'] ) ) {
			$class_name = __NAMESPACE__ . '\\' . static::get_class_name( $data['integration'] );

			if ( class_exists( $class_name ) ) {
				unset( $data['integration'] );
				$instance = new $class_name( $data );
			}
		}

		return $instance;
	}
}
