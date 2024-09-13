<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

namespace TVD\Autoresponder\FacebookPixel;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

use ArrayAccess;

/**
 * Conversions API Event Request
 *
 * @category    Class
 */
class EventRequest implements ArrayAccess {
	/**
	 * Array of property to type mappings. Used for (de)serialization
	 *
	 * @var string[]
	 */
	protected static $param_types = array(
		'events'          => '\TVD\Autoresponder\FacebookPixel[]',
		'test_event_code' => 'string',
		'partner_agent'   => 'string',
		'namespace_id'    => 'string',
		'upload_id'       => 'string',
		'upload_tag'      => 'string',
		'upload_source'   => 'string',
	);
	/**
	 * Array of attributes where the key is the local name, and the value is the original name
	 *
	 * @var string[]
	 */
	protected static $attributeMap = array(
		'events'          => 'events',
		'test_event_code' => 'test_event_code',
		'partner_agent'   => 'partner_agent',
		'namespace_id'    => 'namespace_id',
		'upload_id'       => 'upload_id',
		'upload_tag'      => 'upload_tag',
		'upload_source'   => 'upload_source',
	);
	/**
	 * Array of attributes to setter functions (for deserialization of responses)
	 *
	 * @var string[]
	 */
	protected static $setters = array(
		'events'          => 'setEvents',
		'test_event_code' => 'setTestEventCode',
		'partner_agent'   => 'setPartnerAgent',
		'namespace_id'    => 'setNamespaceId',
		'upload_id'       => 'setUploadId',
		'upload_tag'      => 'setUploadTag',
		'upload_source'   => 'setUploadSource',
	);
	/**
	 * Array of attributes to getter functions (for serialization of requests)
	 *
	 * @var string[]
	 */
	protected static $getters = array(
		'events'          => 'getEvents',
		'test_event_code' => 'getTestEventCode',
		'partner_agent'   => 'getPartnerAgent',
		'namespace_id'    => 'getNamespaceId',
		'upload_id'       => 'getUploadId',
		'upload_tag'      => 'getUploadTag',
		'upload_source'   => 'getUploadSource',
	);
	/**
	 * Associative array for storing property values
	 *
	 * @var mixed[]
	 */
	protected $container = array();

	protected $http_client = null;

	/**
	 * Constructor
	 *
	 * @param string  $pixel_id pixel id
	 * @param mixed[] $data     Associated array of property value initializing the model
	 */
	public function __construct( $pixel_id, array $data = null ) {
		$this->container['pixel_id']        = $pixel_id;
		$this->container['events']          = isset( $data['events'] ) ? $data['events'] : null;
		$this->container['test_event_code'] = isset( $data['test_event_code'] ) ? $data['test_event_code'] : null;
		$this->container['partner_agent']   = isset( $data['partner_agent'] ) ? $data['partner_agent'] : null;
		$this->container['namespace_id']    = isset( $data['namespace_id'] ) ? $data['namespace_id'] : null;
		$this->container['upload_id']       = isset( $data['upload_id'] ) ? $data['upload_id'] : null;
		$this->container['upload_tag']      = isset( $data['upload_tag'] ) ? $data['upload_tag'] : null;
		$this->container['upload_source']   = isset( $data['upload_source'] ) ? $data['upload_source'] : null;
	}

	public static function paramTypes() {
		return self::$param_types;
	}

	public static function attributeMap() {
		return self::$attributeMap;
	}

	public static function setters() {
		return self::$setters;
	}

	public static function getters() {
		return self::$getters;
	}

	/**
	 * show all the invalid properties with reasons.
	 *
	 * @return array invalid properties with reasons
	 */
	public function listInvalidProperties() {
		$invalid_properties = array();
		if ( $this->container['events'] === null ) {
			$invalid_properties[] = "'events' can't be null";
		}

		return $invalid_properties;
	}

	/**
	 * validate all the properties in the model
	 * return true if all passed
	 *
	 * @return bool True if all properties are valid
	 */
	public function valid() {
		return $this->container['events'] !== null;
	}

	/**
	 * Sets an array of Server Event objects
	 *
	 * @param Event[] $events An array of Server Event objects
	 *
	 * @return $this
	 */
	public function setEvents( $events ) {
		$this->container['events'] = $events;

		return $this;
	}

	/**
	 * Gets code used to verify that your server events are received correctly by Facebook. Use this
	 * code to test your server events in the Test Events feature in Events Manager.
	 * See Test Events Tool
	 * (https://developers.facebook.com/docs/marketing-api/conversions-api/using-the-api#testEvents)
	 * for an example.
	 *
	 * @return string
	 */
	public function getTestEventCode() {
		return $this->container['test_event_code'];
	}

	/**
	 * Sets code used to verify that your server events are received correctly by Facebook. Use this
	 * code to test your server events in the Test Events feature in Events Manager.
	 * See Test Events Tool
	 * (https://developers.facebook.com/docs/marketing-api/conversions-api/using-the-api#testEvents)
	 * for an example.
	 *
	 * @param string $test_event_code Code used to verify that your server events are received correctly by Facebook.
	 *                                Use this code to test your server events in the Test Events feature in Events Manager. See Test Events Tool
	 *                                (https://developers.facebook.com/docs/marketing-api/conversions-api/using-the-api#testEvents)
	 *                                for an example.
	 *
	 * @return $this
	 */
	public function setTestEventCode( $test_event_code ) {
		$this->container['test_event_code'] = $test_event_code;

		return $this;
	}

	/**
	 * Sets Partner Agent, which specifies who is sending the event.
	 *
	 * @param string $partner_agent The partner agent who is sending the event
	 *
	 * @return $this
	 */
	public function setPartnerAgent( $partner_agent ) {
		$this->container['partner_agent'] = $partner_agent;

		return $this;
	}

	/**
	 * Sets a custom HTTP Client object, which overrides the default HTTP service
	 * used to send the event request.
	 *
	 * @param HttpServiceInterface $http_client An object that implements the HttpServiceInterface
	 *
	 * @return $this
	 */
	public function setHttpClient( $http_client ) {
		$this->http_client = $http_client;

		return $this;
	}

	/**
	 * Execute the request
	 *
	 * Override this method to from FB class, so we don't import another 10 classes
	 *
	 * @return ResponseInterface
	 */
	public function execute( $api ) {
		$api = $api ?: Api::instance();
		$url = '/' . $this->container['pixel_id'] . '/events';

		$headers = new Headers( array(
			'User-Agent'      => 'fbbizsdk-php-v' . ApiConfig::SDKVersion,
			'Accept-Encoding' => '*',
		) );
		$params  = $this->normalize();

		return $api->call(
			$url,
			HttpMethod::POST,
			$headers,
			$params
		);
	}

	/**
	 * Normalize
	 *
	 * @return array
	 */
	public function normalize() {
		$normalized_events = array();
		$events            = $this->getEvents();
		if ( ! is_null( $events ) ) {
			foreach ( $events as $event ) {
				$normalized_event    = $event->normalize();
				$normalized_events[] = $normalized_event;
			}
		}

		$normalized_payload = array(
			'data'            => $normalized_events,
			'test_event_code' => $this->container['test_event_code'],
			'partner_agent'   => $this->container['partner_agent'],
			'namespace_id'    => $this->container['namespace_id'],
			'upload_id'       => $this->container['upload_id'],
			'upload_tag'      => $this->container['upload_tag'],
			'upload_source'   => $this->container['upload_source'],
		);

		return array_filter( $normalized_payload );
	}

	/**
	 * Gets an array of Server Event objects
	 *
	 * @return Event[]
	 */
	public function getEvents() {
		return $this->container['events'];
	}

	/**
	 * Gets Partner Agent, which specifies who is sending the event.
	 *
	 * @return string
	 */
	public function getPartnerAgent() {
		return $this->container['partner_agent'];
	}

	/**
	 * Gets namespace_id, a scope used to resolve extern_id or Third-party ID.
	 * Can be another data set or data partner ID.
	 *
	 * @return string
	 */
	public function getNamespaceId() {
		return $this->container['namespace_id'];
	}

	/**
	 * Sets namespace_id, a scope used to resolve extern_id or Third-party ID.
	 * Can be another data set or data partner ID.
	 *
	 * @return $this
	 */
	public function setNamespaceId( $namespace_id ) {
		$this->container['namespace_id'] = $namespace_id;

		return $this;
	}

	/**
	 * Gets upload_id, a unique id used to denote the current set being uploaded.
	 *
	 * @return string
	 */
	public function getUploadId() {
		return $this->container['upload_id'];
	}

	/**
	 * Sets upload_id, a unique id used to denote the current set being uploaded.
	 *
	 * @return $this
	 */
	public function setUploadId( $upload_id ) {
		$this->container['upload_id'] = $upload_id;

		return $this;
	}

	/**
	 * Gets upload_tag, a tag string added to track your Offline event uploads.
	 *
	 * @return string
	 */
	public function getUploadTag() {
		return $this->container['upload_tag'];
	}

	/**
	 * Sets upload_tag, a tag string added to track your Offline event uploads.
	 *
	 * @return $this
	 */
	public function setUploadTag( $upload_tag ) {
		$this->container['upload_tag'] = $upload_tag;

		return $this;
	}

	/**
	 * Gets upload_source, the origin/source of data for the dataset to be uploaded.
	 *
	 * @return string
	 */
	public function getUploadSource() {
		return $this->container['upload_source'];
	}

	/**
	 * Sets upload_source, the origin/source of data for the dataset to be uploaded.
	 *
	 * @return $this
	 */
	public function setUploadSource( $upload_source ) {
		$this->container['upload_source'] = $upload_source;

		return $this;
	}

	/**
	 * Returns true if offset exists. False otherwise.
	 *
	 * @param integer $offset Offset
	 *
	 * @return boolean
	 */
	#[\ReturnTypeWillChange]
	public function offsetExists( $offset ) {
		return isset( $this->container[ $offset ] );
	}

	/**
	 * Gets offset.
	 *
	 * @param integer $offset Offset
	 *
	 * @return mixed
	 */
	#[\ReturnTypeWillChange]
	public function offsetGet( $offset ) {
		return isset( $this->container[ $offset ] ) ? $this->container[ $offset ] : null;
	}

	/**
	 * Sets value based on offset.
	 *
	 * @param integer $offset Offset
	 * @param mixed   $value  Value to be set
	 *
	 * @return void
	 */
	#[\ReturnTypeWillChange]
	public function offsetSet( $offset, $value ) {
		if ( is_null( $offset ) ) {
			$this->container[] = $value;
		} else {
			$this->container[ $offset ] = $value;
		}
	}

	/**
	 * Unsets offset.
	 *
	 * @param integer $offset Offset
	 *
	 * @return void
	 */
	#[\ReturnTypeWillChange]
	public function offsetUnset( $offset ) {
		unset( $this->container[ $offset ] );
	}


	/**
	 * Gets the string presentation of the object
	 *
	 * @return string
	 */
	public function __toString() {
		if ( defined( 'JSON_PRETTY_PRINT' ) ) { // use JSON pretty print
			return json_encode( $this, JSON_PRETTY_PRINT );
		}

		return json_encode( $this );
	}
}
