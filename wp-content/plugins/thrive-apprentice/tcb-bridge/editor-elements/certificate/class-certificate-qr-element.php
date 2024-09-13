<?php

namespace TVA\Architect\Certificate;

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Certificate QR Element class
 */
class Certificate_Qr_Element extends \TVA\Architect\Abstract_Sub_Element {
	protected $_tag = 'certificate_qr';

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Verification QR code', 'thrive-apprentice' );
	}

	/**
	 * Return the icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'certificates-qr';
	}

	/**
	 * @return string
	 */
	public function category() {
		return $this->get_thrive_basic_label();
	}

	public function hide() {
		return false;
	}

}
