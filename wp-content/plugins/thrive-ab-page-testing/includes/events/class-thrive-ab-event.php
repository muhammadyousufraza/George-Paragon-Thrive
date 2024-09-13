<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-ab-page-testing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class Thrive_AB_Event extends Thrive_AB_Model {

	/**
	 * @inheritdoc
	 */
	protected function _table_name() {

		return thrive_ab()->table_name( 'event_log' );
	}

	/**
	 * @inheritdoc
	 */
	protected function is_valid() {

		$is_valid = true;

		if ( ! ( $this->page_id ) ) {
			$is_valid = false;
		} elseif ( ! ( $this->variation_id ) ) {
			$is_valid = false;
		} elseif ( ! ( $this->test_id ) ) {
			$is_valid = false;
		} elseif ( ! ( $this->event_type ) ) {
			$is_valid = false;
		}

		return $is_valid;
	}

	public function is_impression() {

		return 1 === $this->event_type;
	}

	public function is_conversion() {

		return 2 === $this->event_type;
	}
}
