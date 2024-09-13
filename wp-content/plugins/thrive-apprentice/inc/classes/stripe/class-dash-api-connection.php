<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Stripe;

use Thrive_Dash_List_Connection_Abstract;
use TVA_Const;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Dash_Api_Connection extends Thrive_Dash_List_Connection_Abstract {
	public static function get_type() {
		return 'sellings';
	}

	public function get_title() {
		return 'Stripe';
	}

	public function output_setup_form() {
		include_once __DIR__ . '/templates/stripe-template.php';
	}

	public function get_logo_url() {
		return TVA_Const::plugin_url( 'img/stripe.svg' );
	}

	public function read_credentials() {
		$api_key  = ! empty( $_POST['connection']['api_key'] ) ? sanitize_text_field( $_POST['connection']['api_key'] ) : '';
		$test_key = ! empty( $_POST['connection']['test_key'] ) ? sanitize_text_field( $_POST['connection']['test_key'] ) : '';

		if ( empty( $api_key ) && empty( $test_key ) ) {
			return $this->error( __( 'You must provide at least a Stripe API key', 'thrive-apprentice' ) );
		}

		if ( ! empty( $api_key ) && str_contains( $api_key, '_test_' ) ) {
			return $this->error( __( 'You must provide a live Stripe API key', 'thrive-apprentice' ) );
		}

		$this->set_credentials( [
				'api_key'  => $api_key,
				'test_key' => $test_key,
			]
		);

		$result = $this->test_connection();

		if ( $result['success'] !== true ) {
			return $result['message'] ?: $this->error( __( 'Invalid API key', 'thrive-apprentice' ) );
		}

		$this->save();

		return $this->success( __( 'Connection successful', 'thrive-apprentice' ) );
	}

	public function test_connection() {
		$credentials = $this->get_credentials();
		$connection  = Connection::get_instance();
		$response    = $connection->ensure_endpoint();

		if ( ! empty( $credentials['test_key'] ) ) {
			$connection->set_test_mode( true );
			$response = $connection->ensure_endpoint();
		}

		return $response;
	}

	public function add_subscriber( $list_identifier, $arguments ) {

	}

	protected function get_api_instance() {
	}

	protected function _get_lists() {
	}
}
