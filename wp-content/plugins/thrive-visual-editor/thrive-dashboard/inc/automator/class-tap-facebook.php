<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

namespace TVE\Dashboard\Automator;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

use function Thrive\Automator\tap_logger;

class Facebook {

	const ADD_TO_CART           = 'AddToCart';
	const ADD_TO_WISHLIST       = 'AddToWishlist';
	const ADD_PAYMENT_INFO      = 'AddPaymentInfo';
	const CONTACT               = 'Contact';
	const COMPLETE_REGISTRATION = 'CompleteRegistration';
	const CUSTOMIZE_PRODUCT     = 'CustomizeProduct';
	const DONATE                = 'Donate';
	const FIND_LOCATION         = 'FindLocation';
	const INITIATE_CHECKOUT     = 'InitiateCheckout';
	const LEAD                  = 'Lead';
	const PURCHASE              = 'Purchase';
	const SEARCH                = 'Search';
	const SUBSCRIBE             = 'Subscribe';
	const START_TRIAL           = 'StartTrial';
	const SUBMIT_APPLICATION    = 'SubmitApplication';
	const SUBMIT_CONTACT        = 'SubmitContact';
	const SCHEDULE              = 'Schedule';
	const VIEW_CONTENT          = 'ViewContent';

	const CONTENT_CATEGORY  = 'content_category';
	const CONTENT_IDS       = 'content_ids';
	const CONTENT_NAME      = 'content_name';
	const CONTENT_TYPE      = 'content_type';
	const CONTENTS          = 'contents';
	const CURRENCY          = 'currency';
	const VALUE             = 'value';
	const STATUS            = 'status';
	const NUM_ITEMS         = 'num_items';
	const SEARCH_STRING     = 'search_string';
	const PREDICTED_LTV     = 'predicted_ltv';
	const ORDER_ID          = 'order_id';
	const DELIVERY_CATEGORY = 'delivery_category';

	const USER_CITY            = 'city';
	const USER_COUNTRY         = 'country';
	const USER_GENDER          = 'gender';
	const USER_EMAIL           = 'email';
	const USER_PHONE           = 'phone';
	const USER_ZIP             = 'zip_code';
	const USER_DOB             = 'date_of_birth';
	const USER_FN              = 'first_name';
	const USER_LN              = 'last_name';
	const USER_LEAD_ID         = 'lead_id';
	const USER_FB_LOGIN_ID     = 'fb_login_id';
	const USER_SUBSCRIPTION_ID = 'subscription_id';
	const USER_EXTERNAL_ID     = 'external_id';
	const USER_STATE           = 'state';

	const EVENT_ID         = 'event_id';
	const EVENT_SOURCE_URL = 'event_source_url';

	public static function init() {
		static::hooks();
	}

	public static function hooks() {
		add_filter( 'td_automator_should_load_file', [ __CLASS__, 'should_load_files' ], 10, 2 );
	}

	public static function should_load_files( $load, $filename ) {
		if ( strpos( basename( $filename, '.php' ), '-facebook-' ) !== false && ! static::exists() ) {
			$load = false;
		}

		return $load;
	}

	public static function exists() {
		return static::get_connection() !== null;
	}

	/**
	 * @return \Thrive_Dash_List_Connection_Abstract|null
	 */
	public static function get_connection() {
		$facebook_instance = \Thrive_Dash_List_Manager::connection_instance( 'facebookpixel' );

		return $facebook_instance !== null && $facebook_instance->is_connected() ? $facebook_instance : null;
	}

	/**
	 *
	 * @return \Thrive_Dash_Api_FacebookPixel|null
	 */
	public static function get_api() {
		$facebook_instance = static::get_connection();

		return $facebook_instance ? $facebook_instance->get_api() : null;
	}

	public static function map_data( $data ) {
		return [
			'id'    => $data,
			'label' => $data,
		];
	}

	public static function get_event_types() {
		return array_map( [ __CLASS__, 'map_data' ], [
			static::ADD_TO_CART,
			static::ADD_TO_WISHLIST,
			static::ADD_PAYMENT_INFO,
			static::CONTACT,
			static::COMPLETE_REGISTRATION,
			static::CUSTOMIZE_PRODUCT,
			static::DONATE,
			static::FIND_LOCATION,
			static::INITIATE_CHECKOUT,
			static::LEAD,
			static::PURCHASE,
			static::SEARCH,
			static::SUBSCRIBE,
			static::START_TRIAL,
			static::SUBMIT_APPLICATION,
			static::SUBMIT_CONTACT,
			static::SCHEDULE,
			static::VIEW_CONTENT,
		] );
	}

	public static function get_event_keys() {
		return [ static::EVENT_ID, static::EVENT_SOURCE_URL ];
	}

	public static function get_standard_keys() {
		return [
			static::CONTENT_CATEGORY,
			static::CONTENT_IDS,
			static::CONTENT_NAME,
			static::CONTENT_TYPE,
			static::CONTENTS,
			static::CURRENCY,
			static::VALUE,
			static::STATUS,
			static::NUM_ITEMS,
			static::SEARCH_STRING,
			static::PREDICTED_LTV,
			static::ORDER_ID,
			static::DELIVERY_CATEGORY,
		];
	}

	public static function get_standard_options() {
		return array_map( [ __CLASS__, 'map_data' ], static::get_standard_keys() );
	}

	public static function get_event_options() {
		return array_map( [ __CLASS__, 'map_data' ], static::get_event_keys() );
	}

	public static function extract_event_fields( $args ) {
		$allowed_keys = static::get_event_keys();

		return array_filter(
			$args,
			static function ( $key ) use ( $allowed_keys ) {
				return in_array( $key, $allowed_keys, true );
			},
			ARRAY_FILTER_USE_KEY
		);
	}

	public static function get_user_options() {
		return [
			[
				'id'    => static::USER_CITY,
				'label' => __( 'City', 'thrive-dash' ),
			],
			[
				'id'    => static::USER_COUNTRY,
				'label' => __( 'Country', 'thrive-dash' ),
			],
			[
				'id'    => static::USER_GENDER,
				'label' => __( 'Gender', 'thrive-dash' ),
			],
			[
				'id'    => static::USER_EMAIL,
				'label' => __( 'Email', 'thrive-dash' ),
			],
			[
				'id'    => static::USER_PHONE,
				'label' => __( 'Phone', 'thrive-dash' ),
			],
			[
				'id'    => static::USER_ZIP,
				'label' => __( 'Zip', 'thrive-dash' ),
			],
			[
				'id'    => static::USER_DOB,
				'label' => __( 'Date of Birth', 'thrive-dash' ),
			],
			[
				'id'    => static::USER_FN,
				'label' => __( 'First Name', 'thrive-dash' ),
			],
			[
				'id'    => static::USER_LN,
				'label' => __( 'Last Name', 'thrive-dash' ),
			],
			[
				'id'    => static::USER_LEAD_ID,
				'label' => __( 'Lead ID', 'thrive-dash' ),
			],
			[
				'id'    => static::USER_FB_LOGIN_ID,
				'label' => __( 'Facebook Login ID', 'thrive-dash' ),
			],
			[
				'id'    => static::USER_SUBSCRIPTION_ID,
				'label' => __( 'Subscription ID', 'thrive-dash' ),
			],
			[
				'id'    => static::USER_EXTERNAL_ID,
				'label' => __( 'External ID', 'thrive-dash' ),
			],
			[
				'id'    => static::USER_STATE,
				'label' => __( 'State', 'thrive-dash' ),
			],
		];
	}

	public static function get_user_keys() {
		return [
			static::USER_CITY,
			static::USER_COUNTRY,
			static::USER_EMAIL,
			static::USER_PHONE,
			static::USER_GENDER,
			static::USER_ZIP,
			static::USER_DOB,
			static::USER_FN,
			static::USER_LN,
			static::USER_LEAD_ID,
			static::USER_FB_LOGIN_ID,
			static::USER_SUBSCRIPTION_ID,
			static::USER_EXTERNAL_ID,
			static::USER_STATE,
		];
	}

	/**
	 * Get only user specific entries
	 *
	 * @param $args
	 *
	 * @return array
	 */
	public static function extract_user_fields( $args ) {
		$allowed_keys = static::get_user_keys();

		return array_filter(
			$args,
			static function ( $key ) use ( $allowed_keys ) {
				return in_array( $key, $allowed_keys, true );
			},
			ARRAY_FILTER_USE_KEY
		);
	}

	/**
	 * Use Automator logger so insert additional messages
	 *
	 * @param $automation_id
	 * @param $response
	 *
	 * @return void
	 */
	public static function log_error_request( $automation_id, $response ) {
		tap_logger( $automation_id )->insert_log(
			[
				'facebook_webhook' => [
					'data-webhook-fail' => [
						'message'    => __( 'Facebook fire event failed', 'thrive-dash' ),
						'label'      => __( 'Facebook event response', 'thrive-dash' ),
						'is_success' => false,
					],
				],
			],
			[
				'request_body' => $response['message'],
			]
		);
	}
}
