<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

require_once dirname( __FILE__ ) . '/Ontraport/ObjectType.php';

class Thrive_Dash_Api_Ontraport {

	/**
	 * @var string
	 */
	protected $api_url = 'https://api.ontraport.com/';

	/**
	 * @var string
	 */
	protected $app_id;

	/**
	 * @var string
	 */
	protected $key;

	/**
	 * @var string
	 */
	protected $subscribe_campaign = 'Campaign';

	/**
	 * @var string
	 */
	protected $subscribe_sequence = 'Sequence';

	/**
	 * API Version
	 *
	 * @var int
	 */
	protected $v = 1;

	/**
	 * Thrive_Dash_Api_Ontraport constructor.
	 *
	 * @param $app_id
	 * @param $key
	 *
	 * @throws Thrive_Dash_Api_Ontraport_Exception
	 */
	public function __construct( $app_id, $key ) {

		if ( empty( $app_id ) || empty( $key ) ) {
			throw new Thrive_Dash_Api_Ontraport_Exception( __( 'Invalid API credentials ', 'thrive' ) );
		}

		$this->app_id = $app_id;
		$this->key    = $key;
	}

	/**
	 * Get all sequences [feature only available to Pro customers]
	 *
	 * @return bool
	 * @throws Thrive_Dash_Api_Ontraport_Exception
	 */
	public function get_sequences() {

		$lists  = array();
		$offset = 0;
		$range  = 50;
		$data   = array();

		do {

			$raw_sequences = $this->call(
				"{$this->v}/objects",
				array(
					'objectID' => OntraportObjectType::SEQUENCE,
					'sort'     => 'id',
					'sortDir'  => 'desc',
					'start'    => $offset,
					'range'    => $range,
				)
			);

			if ( ! is_array( $raw_sequences ) || empty( $raw_sequences['data'] ) ) {
				break;
			}

			$data = array_merge_recursive( $data, $raw_sequences['data'] );

			if ( count( $raw_sequences['data'] ) < $range ) {
				break;
			}

			$offset += $range;

		} while ( 0 !== count( $raw_sequences['data'] ) );

		foreach ( $data as $item ) {
			$lists[ $item['drip_id'] ] = $item;
		}

		return $lists;
	}

	/**
	 * Get all campaigns
	 *
	 * @return array
	 * @throws Thrive_Dash_Api_Ontraport_Exception
	 */
	public function get_campaigns() {

		$lists  = array();
		$offset = 0;
		$range  = 50; // max allowed by the API
		$data   = array();

		do {

			$raw_campaigns = $this->call(
				"{$this->v}/CampaignBuilderItems",
				array(
					'sort'    => 'id',
					'sortDir' => 'desc',
					'start'   => $offset,
					'range'   => $range,
				)
			);

			if ( ! is_array( $raw_campaigns ) || empty( $raw_campaigns['data'] ) ) {
				break;
			}

			$data = array_merge_recursive( $data, $raw_campaigns['data'] );

			if ( count( $raw_campaigns['data'] ) < $range ) {
				break;
			}

			$offset += $range;

		} while ( 0 !== count( $raw_campaigns['data'] ) );

		foreach ( $data as $item ) {
			$lists[ $item['id'] ] = $item;
		}

		return $lists;
	}

	/**
	 * Build an array of required data for creating/updating a object
	 *
	 * @param $fields
	 *
	 * @return array
	 */
	public function build_object_data( $fields ) {

		$data = array();

		if ( empty( $fields ) ) {
			return $data;
		}

		$data['objectID'] = OntraportObjectType::CONTACT;

		if ( ! empty( $fields['firstname'] ) ) {
			$data['firstname'] = $fields['firstname'];
		}

		if ( ! empty( $fields['lastname'] ) ) {
			$data['lastname'] = $fields['lastname'];
		}

		if ( ! empty( $fields['email'] ) ) {
			$data['email'] = $fields['email'];
		}

		if ( ! empty( $fields['phone'] ) ) {
			$data['office_phone'] = $fields['phone'];
		}

		$data['use_utm_names'] = false;
		$data['ignore_blanks'] = false;

		return $data;
	}

	/**
	 * Build subscribe fields
	 *
	 * @param $list_id
	 * @param $fields
	 * @param $contact_info
	 *
	 * @return array
	 */
	public function build_subscribe_data( $list_id, $fields, $contact_info ) {

		if ( empty( $list_id ) || empty( $fields ) || empty( $contact_info ) ) {
			return array();
		}

		$subscribe_type = ! empty( $fields['type'] ) ? $fields['type'] : '';

		switch ( $subscribe_type ) {
			case 'sequences':
				$subscribe_type = $this->subscribe_sequence;
				break;
			case 'campaigns':
				$subscribe_type = $this->subscribe_campaign;
				break;
			default:
				$subscribe_type = $this->subscribe_campaign;
				break;
		}

		return array(
			'objectID' => OntraportObjectType::CONTACT,
			'sub_type' => $subscribe_type,
			'add_list' => $list_id,
			'ids'      => ! empty( $contact_info['id'] ) ? $contact_info['id'] : array(),
		);
	}

	/**
	 * Subscribe method
	 *
	 * @param $list_id
	 * @param $fields
	 *
	 * @return bool
	 * @throws Thrive_Dash_Api_Ontraport_Exception
	 */
	public function add_contact( $list_id, $fields ) {

		$pro_account    = false;
		$contact_id     = OntraportObjectType::CONTACT;
		$old_connection = empty( $fields['type'] ) ? true : false;

		// Create or update a contact object with created fields
		$object_fields = $this->build_object_data( $fields );
		$contact_info  = $this->call( "1/objects/saveorupdate?objectID={$contact_id}", $object_fields, 'POST' );

		if ( ! is_array( $contact_info ) || empty( $contact_info['data'] ) ) {
			return false;
		}

		// On update ['data]['attrs'] is returned otherwise only ['data']
		$contact_data = ! empty( $contact_info['data']['attrs'] ) ? $contact_info['data']['attrs'] : $contact_info['data'];

		// Subscribe [add a Contact Object to a Campaign or Sequence based on the created fields]
		$subscribe_fields = $this->build_subscribe_data( $list_id, $fields, $contact_data );

		// Backwards compatibility for connections made with previous API version [no 'type' field for old version]
		if ( $old_connection ) {

			// If doesn't have access to pull sequences it means regular, not a PRO account
			try {

				$this->get_sequences();
				$pro_account = true;
			} catch ( Exception $e ) {
			}

			// Regular acoounts have access only to campaigns, so subscribe to campaign
			if ( ! $pro_account ) {
				$subscribe_fields['sub_type'] = $this->subscribe_campaign;
			}
		}

		$this->call( '1/objects/subscribe', $subscribe_fields, 'PUT' );

		return true;
	}

	/**
	 * @param        $path
	 * @param array $params
	 * @param string $method
	 *
	 * @return array|mixed|object
	 * @throws Thrive_Dash_Api_Ontraport_Exception
	 */
	public function call( $path, $params = array(), $method = 'GET' ) {

		$method = strtoupper( $method );
		$url    = $this->api_url . $path;

		$args = array(
			'headers' => array(
				'Content-Type' => 'application/json',
				'Api-Appid'    => $this->app_id,
				'Api-Key'      => $this->key,
			),
			'timeout' => 45,
		);

		if ( 'PUT' === $method ) {
			$args['headers']['Content-Type'] = 'application/x-www-form-urlencoded';
		}

		switch ( $method ) {
			case 'POST':
				$args['body'] = json_encode( $params );
				$fn           = 'tve_dash_api_remote_post';
				break;
			case 'PUT':
				$args['method'] = 'PUT';
				$args['body']   = $params;
				$fn             = 'tve_dash_api_remote_post';
				break;
			case 'GET':
			default:
				$url .= '?';
				foreach ( $params as $k => $param ) {
					$url .= $k . '=' . $param . '&';
				}
				$url = rtrim( $url, '?& ' );
				$fn  = 'tve_dash_api_remote_get';
				break;
		}

		$response = $fn( $url, $args );

		if ( $response instanceof WP_Error ) {
			throw new Thrive_Dash_Api_Ontraport_Exception( __( 'Failed connecting: ', 'thrive' ) . $response->get_error_message() );
		}

		$status = $response['response']['code'];
		if ( 200 !== (int) $status && 204 !== (int) $status ) {
			throw new Thrive_Dash_Api_Ontraport_Exception(
				__( 'Call failed: ', 'thrive' ) . ( empty( $response['body'] ) ? __( 'HTTP status code: ', 'thrive' ) . $status
					: $response['body']
				)
			);
		}

		$data = @json_decode( $response['body'], true );

		if ( empty( $data ) || ! isset( $data['code'] ) ) {
			throw new Thrive_Dash_Api_Ontraport_Exception( __( 'Unknown problem with the API request. Response was: ', 'thrive' ) . $response['body'] );
		}

		if ( ! empty( $data['code'] ) ) {
			throw new Thrive_Dash_Api_Ontraport_Exception(
				'API Error: ' . isset( $data['result_message'] ) ? $data['result_message'] : (int) $data['result_code']
			);
		}

		return $data;
	}
}
