<?php


namespace Thrive\Automator\Items;

use Thrive\Automator\Utils;
use WP_Error;
use function Thrive\Automator\tap_logger;


class Webhook extends Action {

	public static function get_id() {
		return 'wordpress/webhook';
	}

	public static function get_name() {
		return __( 'Send webhook', 'thrive-automator' );
	}

	public static function get_description() {
		return __( 'Send data in a webhook to an external service. Methods supported include POST, PUT and GET.', 'thrive-automator' );
	}

	public static function get_image() {
		return 'tap-send-webhook';
	}

	public static function get_required_action_fields() {
		return [

			Url_Webhook::get_id(),
			Request_Method::get_id()         => [ Request_Format::get_id() ],
			Fields_Webhook::get_id(),
			Request_Headers_Toggle::get_id() => [ Webhook_Headers::get_id() ],
			Connection_Test::get_id(),
		];
	}

	public static function get_required_data_objects() {
		return [];
	}

	public static function is_top_level() {
		return true;
	}

	public function prepare_data( $data = [] ) {
		$this->data = $data;
	}

	/**
	 * Do an external request based on webhook url, action fields and automation fields
	 *
	 * @return array
	 */
	public function do_action( $data = [] ) {
		$webhook_url = $this->get_automation_data_value( 'url_webhook' );
		if ( empty( $webhook_url ) ) {
			$status = 404;
		} else {

			$request_method = $this->get_automation_data_value( 'request_method' );
			$request_format = '';
			/**
			 * Try to get request format for POST/PUT methods
			 */
			if ( $request_method !== 'get' ) {
				$request_method_subfield = $this->get_automation_data( 'request_method' )['subfield'];
				if ( ! empty( $request_method_subfield['request_format']['value'] ) ) {
					$request_format = $request_method_subfield['request_format']['value'];
				}
			}

			$fields = [];
			foreach ( $this->get_automation_data_value( 'fields_webhook', [] ) as $field ) {
				$original_key = str_replace( ']', '', $field['key'] );
				$reference    = &$fields;
				foreach ( explode( '[', $original_key ) as $key ) {
					if ( ! array_key_exists( $key, $reference ) ) {
						$reference[ $key ] = [];
					}
					$reference = &$reference[ $key ];

				}
				$reference = $field['value'];
				unset( $reference );
			}

			$headers = [];
			/**
			 * Get custom headers if needed
			 */
			if ( $this->get_automation_data_value( 'request_headers_toggle' ) === 'custom' ) {
				$request_method_subfield = $this->get_automation_data( 'request_headers_toggle' )['subfield'];
				if ( ! empty( $request_method_subfield ) && ! empty( $request_method_subfield['webhook_headers'] ) ) {
					$headers = array_reduce( $request_method_subfield['webhook_headers']['value'], 'Thrive\Automator\Utils::flat_key_value_pairs', [] );
				}
			}

			if ( ! empty( $request_format ) ) {
				switch ( $request_format ) {
					case 'json':
						$fields                  = json_encode( $fields );
						$headers['content-type'] = 'application/json';
						break;
					case 'xml':
						$fields = Utils::xml_encode( $fields );
						break;
					case 'form':
					default:
						break;
				}
			}
			$http = _wp_http_get_object();

			$args     = [
				'method'  => strtoupper( $request_method ),
				'body'    => $fields,
				'headers' => $headers,
			];
			$response = $http->request( $webhook_url, $args );
			$status   = wp_remote_retrieve_response_code( $response );
			$body     = wp_remote_retrieve_body( $response );

			if ( $response instanceof WP_Error ) {
				$status = $response->get_error_code();
				$body   = $response->get_error_message();
			}
			$success   = $status >= 200 && $status < 300;
			$error_key = $success ? 'data-webhook' : 'data-webhook-fail';
			tap_logger( $this->get_automation_id() )->insert_log(
				[
					'send_webhook' => [
						$error_key => [
							'message'    => $success ? __( 'Send webhook successfully executed', 'thrive-automator' ) : __( 'Send webhook failed', 'thrive-automator' ),
							'label'      => __( 'Send webhook response', 'thrive-automator' ),
							'is_success' => $success,
						],
					],
				],
				[
					'request_status_code' => $status,
					'request_body'        => strip_tags( $body ),
				]
			);
		}

		return [ 'status_code' => $status ];
	}

	public static function is_compatible_with_trigger( $trigger ) {
		return true;
	}

	public static function get_subfields( $field, $selected_value, $action_data ) {
		$fields           = [];
		$available_fields = Action_Field::get();
		if ( is_array( $field ) ) {
			$field = $field[0];
		}
		if ( ( $field === 'request_format' && $selected_value !== 'get' ) || ( $field === 'webhook_headers' && $selected_value === 'custom' ) ) {
			$subfield   = $available_fields[ $field ];
			$state_data = $subfield::localize();

			if ( Utils::is_multiple( $subfield::get_type() ) ) {
				$state_data['values'] = $subfield::get_options_callback( static::get_id(), $action_data );
			}
			$fields[ $state_data['id'] ] = $state_data;
		}

		return $fields;
	}
}
