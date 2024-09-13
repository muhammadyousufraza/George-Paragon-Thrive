<?php

namespace TD\DB_Updater;

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}


abstract class Updater {
	protected $product = 'Thrive Dashboard';

	public function get_total_steps() {
		return 1;
	}

	public function output_welcome_message() {
		echo "<h4>The database tables for {$this->product} need updating. Click the button below to start the process.</h4>"; // phpcs:ignore
	}

	public function get_dashboard_url() {
		return '';
	}

	/**
	 * Main entry point for the ajax "step" request
	 */
	public function ajax_handler() {
		if ( empty( $_REQUEST['step'] ) ) {
			$response = array(
				'error' => 'Invalid step',
			);
		} else {
			$response = (array) $this->execute_step( sanitize_text_field( $_REQUEST['step'] ) );
		}

		wp_send_json( $response );
	}

	public function get_product_name() {
		return $this->product;
	}

	abstract public function execute_step( $step );
}
