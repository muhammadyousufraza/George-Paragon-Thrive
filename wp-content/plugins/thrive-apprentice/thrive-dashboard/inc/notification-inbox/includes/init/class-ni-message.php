<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TD_Inbox_Message
 *
 * Build the Message object, required for push_notification
 */
class TD_Inbox_Message {

	/**
	 * Type of the message [used on displaying in API Connections]
	 */
	const TYPE_API = 'api_connections';

	/**
	 * Message inbox type [used on displaying in Dash Inbox]
	 */
	const TYPE_INBOX = 'inbox';

	/**
	 * @var string
	 */
	private $id;

	/**
	 * @var string
	 */
	private $title;

	/**
	 * @var string
	 */
	private $info;

	/**
	 * @var int
	 */
	private $read = 0;

	/**
	 * @var string
	 */
	private $date;

	/**
	 * @var null
	 */
	private $slug = null;

	/**
	 * @var null
	 */
	private $type = null;

	/**
	 * TD_Inbox_Message constructor.
	 *
	 * @param $data
	 *
	 * @throws Exception
	 */
	public function __construct( $data ) {

		if ( ! is_array( $data ) ) {
			throw new Exception( __METHOD__ . __( ' message must be array ', 'thrive-dash' ) );
		}

		if ( empty( $data['title'] ) ) {
			throw new Exception( __METHOD__ . __( ' title can not be empty.. ', 'thrive-dash' ) );
		}

		$this->_set( $data );
	}

	/**
	 * Returns objects properties as array
	 *
	 * @return array
	 */
	public function to_array() {

		return (array) $this->_get_data();
	}

	/**
	 * Returns objects properties as json
	 *
	 * @return false|mixed|string|void
	 */
	public function to_json() {

		return json_encode( $this->_get_data() );
	}

	/**
	 * @param $property
	 *
	 * @return bool
	 */
	public function get_property( $property ) {

		if ( ! isset( $this->{$property} ) ) {
			return false;
		}

		return $this->{$property};
	}

	/**
	 * Returns all object accessible non-static properties in this scope
	 *
	 * @return array
	 */
	private function _get_data() {

		return get_object_vars( $this );
	}

	/**
	 * @param $data
	 */
	private function _set( $data ) {

		foreach ( $data as $key => $value ) {

			if ( property_exists( $this, $key ) ) {
				$this->{$key} = $value;
			}
		}

		if ( $this->title ) {
			$this->id = md5( $this->title );
		}

		$this->date = date( 'jS F Y' );
		$this->read = 0;
	}
}
