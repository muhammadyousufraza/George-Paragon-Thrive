<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

use TVD\Autoresponder\FacebookPixel\ActionSource;
use TVD\Autoresponder\FacebookPixel\Api;
use TVD\Autoresponder\FacebookPixel\CustomData;
use TVD\Autoresponder\FacebookPixel\Event;
use TVD\Autoresponder\FacebookPixel\EventRequest;
use TVD\Autoresponder\FacebookPixel\UserData;
use TVD\Autoresponder\FacebookPixel\Util;

class Thrive_Dash_Api_FacebookPixel {

	/**
	 * @var Api
	 */
	protected $api;

	/**
	 * @var EventRequest
	 */
	protected $event_request;


	/**
	 * @var array
	 */
	private $connection_details;

	public function __construct( $connection_details ) {
		static::load_dependencies();

		$this->connection_details = $connection_details;
		/**
		 * init api
		 */
		if ( ! empty( $connection_details['access_token'] ) ) {
			$this->api = Api::init( null, null, $connection_details['access_token'] );
		}

		/**
		 * init event request
		 */
		if ( ! empty( $connection_details['pixel_id'] ) ) {
			$this->event_request = new EventRequest( $connection_details['pixel_id'] );
		}
	}

	/**
	 * Load dependencies
	 *
	 * @return void
	 */
	public static function load_dependencies() {
		foreach ( glob( __DIR__ . '/FacebookPixel/*.php' ) as $file ) {
			require_once $file;
		}
	}

	/**
	 * Generate an UserData object
	 *
	 * @param $user_details
	 *
	 * @return UserData
	 */
	public function prepare_user_data( $user_details = [] ) {
		$user_data = new UserData( $user_details );

		$fbc = Util::getFbc();
		if ( $fbc ) {
			$user_data->setFbc( $fbc );
		}

		$fbp = Util::getFbp();
		if ( $fbp ) {
			$user_data->setFbp( $fbp );
		}

		$user_data->setClientIpAddress( Util::getIpAddress() )
		          ->setClientUserAgent( Util::getHttpUserAgent() );

		return $user_data;
	}

	/**
	 * Get event default values
	 *
	 * @return array
	 */
	public static function get_event_defaults() {
		return [
			'event_time'       => time(),
			'action_source'    => ActionSource::WEBSITE,
			'event_source_url' => get_home_url(),
		];
	}

	/**
	 * Generate a random event ID
	 *
	 * @throws Exception
	 */
	public function generate_event_id() {
		$pixel_id = $this->connection_details['pixel_id'];
		$bytes    = random_bytes( 16 );

		return $pixel_id . bin2hex( $bytes );
	}

	/**
	 * Initiate an Event object
	 *
	 * @param $event_details
	 *
	 * @return Event
	 * @throws Exception
	 */
	public function prepare_event_data( $event_details = [] ) {
		$event_data = new Event();
		$setters    = Event::setters();

		$event_details['event_id'] = $this->generate_event_id();

		$event_details = array_merge( static::get_event_defaults(), $event_details );

		foreach ( $setters as $param => $setter ) {
			if ( isset( $event_details[ $param ] ) ) {
				$event_data->$setter( $event_details[ $param ] );
			}
		}

		return $event_data;
	}

	/**
	 * Initialize a CustomData object
	 *
	 * @param $custom_details
	 *
	 * @return CustomData
	 */
	public function prepare_custom_data( $custom_details = [] ) {
		$custom_data = new CustomData();
		$setters     = CustomData::setters();

		foreach ( $setters as $param => $setter ) {
			if ( isset( $custom_details[ $param ] ) ) {
				$custom_data->$setter( $custom_details[ $param ] );
			}
		}

		return $custom_data;
	}

	/**
	 * Send a custom event to Facebook Pixel in order to validate pixel ID and access token
	 *
	 * @return array|bool[]
	 */
	public function send_test_event() {
		$user_data = $this->prepare_user_data( [ 'emails' => [ get_option( 'admin_email' ) ] ] );

		$event = $this->prepare_event_data( [
			'event_name' => 'Thrive test connection',
			'user_data'  => $user_data,
		] );

		return $this->send_events( $event );
	}

	/**
	 * Send a list of events to Facebook Pixel
	 *
	 * @param $events
	 *
	 * @return array|bool[]
	 */
	public function send_events( $events ) {
		if ( ! is_array( $events ) ) {
			$events = [ $events ];
		}

		$this->event_request->setEvents( $events );

		try {
			$this->event_request->execute( $this->api );
			$response = [ 'success' => true ];
		} catch ( Exception $e ) {
			$response = [
				'success' => false,
				'message' => $e->getMessage(),
			];
		}

		return $response;
	}

}
