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
 * Class Custom_Payment
 *
 * This class extends the Generic class and provides methods for handling custom payment operations.
 */
class Custom_Payment extends Generic {

	/**
	 * Get the URL associated with the custom payment.
	 *
	 * This method overrides the abstract method in the Generic class.
	 * It returns the URL associated with the custom payment.
	 *
	 * @return string The URL associated with the custom payment.
	 */
	public function get_url() {
		return empty( $this->data['url'] ) ? '' : $this->data['url'];
	}

	/**
	 * Check if the custom payment is valid.
	 *
	 * This method overrides the method in the Generic class.
	 * It checks if the URL associated with the custom payment is not empty.
	 *
	 * @return bool True if the URL is not empty, false otherwise.
	 */
	public function is_valid() {
		return ! empty( $this->data['url'] );
	}
}
