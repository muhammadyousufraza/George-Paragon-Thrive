<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Architect;

use TVA\TTB\Check;

if ( ! class_exists( '\TVA\Architect\Abstract_Sub_Element', false ) ) {
	require_once __DIR__ . '/class-abstract-sub-element.php';
}

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}


class Buy_Now_Button_Element extends Abstract_Sub_Element {
	/**
	 * @var string
	 */
	protected $_tag = 'product_buy_now_button';

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Buy now', 'thrive-apprentice' );
	}

	/**
	 * Return the icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'button';
	}

	public function category() {
		return $this->get_thrive_integrations_label();
	}

	public function hide() {
		return ! empty( $_REQUEST['tva_skin_id'] ) || tva_is_apprentice() || Check::course_item();
	}
}
