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
 * Class TVE_Dash_InboxManager
 */
class TVE_Dash_InboxManager {

	/**
	 * Option name, for storing NI messages
	 */
	const INBOX_OPTION_NAME = 'tvd_dash_inbox';

	/**
	 * For saving inbox errors into this option
	 */
	const INBOX_OPTION_ERRORS = 'tvd_dash_inbox_err';

	/**
	 * @var null
	 */
	protected static $_instance = null;

	/**
	 * @var array
	 */
	public $list = array();

	/**
	 * Retrieved data from DB
	 *
	 * @var array
	 */
	private $_existing_list = array();

	/**
	 * TVE_Dash_InboxManager constructor.
	 */
	private function __construct() {
	}

	/**
	 * @return TVE_Dash_InboxManager|null
	 */
	public static function instance() {

		if ( empty( static::$_instance ) ) {
			static::$_instance = new self();
		}

		return static::$_instance;
	}

	/**
	 * @param $msg_id
	 *
	 * @throws Exception
	 */
	public function set_read( $msg_id ) {

		if ( empty( $msg_id ) ) {
			throw new Exception( __( __METHOD__ . ' required message_id on updating notification status. ', 'thrive-dash' ) );
		}

		$this->_refresh_messages();

		if ( isset( $this->_existing_list[ $msg_id ] ) ) {
			$this->_existing_list[ $msg_id ]['read'] = 1;
		}

		$this->_update();
	}

	/**
	 * Remove message from API connection lsit
	 *
	 * @param $slug
	 *
	 * @return bool
	 */
	public function remove_api_connection( $slug ) {

		if ( empty( $slug ) ) {
			return false;
		}

		$this->_refresh_messages();

		$found = 0;
		foreach ( $this->_existing_list as $msg_id => $message ) {
			if ( ! empty( $message['slug'] ) && ! empty( $message['type'] ) && $slug === $message['slug'] && TD_Inbox_Message::TYPE_API === $message['type'] ) {
				$found = 1;
				unset( $this->_existing_list[ $msg_id ] );
			}
		}

		if ( $found ) {
			return $this->_update();
		}

		return false;
	}

	/**
	 * Bulk update notifications read
	 */
	public function bulk_read() {
		$this->_refresh_messages();

		foreach ( $this->_existing_list as $id => $notification ) {
			if ( isset( $notification['read'] ) ) {
				$this->_existing_list[ $id ]['read'] = 1;
			}
		}

		return $this->_update();
	}

	/**
	 * Add to the beginning of the list
	 *
	 * @param $message
	 *
	 * @throws Exception
	 */
	public function prepend( $message ) {

		if ( ! $message instanceof TD_Inbox_Message ) {
			throw new Exception( __( __METHOD__ . ' must be instanceof TD_Inbox_Message. ', 'thrive-dash' ) );
		}

		$message_id = $message->get_property( 'id' );

		try {
			if ( $this->_message_exists( $message_id ) ) {
				throw new Exception( __METHOD__ . __( " Could not save, [{$message_id}] already exists.", 'thrive-dash' ) );
			}
		} catch ( Exception $e ) {
			throw new Exception( ( $e->getMessage() ) );
		}

		$this->list = array( $message_id => $message->to_array() ) + $this->list;
	}

	/**
	 * Add to the end of the list
	 *
	 * @param $message
	 *
	 * @throws Exception
	 */
	public function append( $message ) {

		if ( ! $message instanceof TD_Inbox_Message ) {
			throw new Exception( __METHOD__ . __( ' message must be instanceof TD_Inbox_Message. ', 'thrive-dash' ) );
		}

		$message_id = $message->get_property( 'id' );

		try {
			if ( $this->_message_exists( $message_id ) ) {
				throw new Exception( __METHOD__ . __( " Could not save, [{$message_id}] already exists.", 'thrive-dash' ) );
			}
		} catch ( Exception $e ) {
			throw new Exception( ( $e->getMessage() ) );
		}

		$this->list[ $message_id ] = $message->to_array();
	}

	/**
	 * Get all or inbox type option inbox data
	 *
	 * @return array
	 */
	public function get_data( $type = '' ) {

		$data = (array) get_option( self::INBOX_OPTION_NAME, array() );

		if ( ! empty( $type ) && TD_Inbox_Message::TYPE_INBOX === $type ) {
			foreach ( $data as $msg_id => $message ) {
				if ( isset( $message['type'] ) && TD_Inbox_Message::TYPE_API === $message['type'] ) {
					unset( $data[ $msg_id ] );
				}
			}
		}

		return $data;
	}

	/**
	 * @param     $offset
	 * @param int $length
	 *
	 * @return array
	 */
	public function load_more( $offset, $length = 10 ) {

		$this->_refresh_messages();

		return array_slice( $this->_existing_list, $offset, $length );
	}

	/**
	 * Count the sent list or regenerate and count
	 *
	 * @param array $data
	 *
	 * @return int
	 */
	public function count_unread( $data = array(), $type = TD_Inbox_Message::TYPE_INBOX ) {

		$cnt = 0;

		if ( empty( $data ) ) {
			$this->_refresh_messages();
			$data = $this->_existing_list;
		}

		foreach ( $data as $notification ) {
			if ( ! empty( $notification['type'] ) && $type === $notification['type'] && 0 === $notification['read'] ) {
				$cnt ++;
			}
		}

		return $cnt;
	}

	/**
	 * @return mixed|void
	 */
	public function added_messages() {

		return $this->list;
	}

	/**
	 * @return bool
	 * @throws Exception
	 */
	public function push_notifications() {

		if ( empty( $this->list ) ) {
			throw new Exception( __( 'Could not push_notification with empty arguments', 'thrive-dash' ) );
		}

		return $this->_save();
	}

	/**
	 * Save errors into DB
	 *
	 * @param $message
	 *
	 * @return bool
	 */
	public function error( $message ) {

		if ( empty( $message ) ) {
			return false;
		}

		$messages                          = (array) get_option( self::INBOX_OPTION_ERRORS, array() );
		$messages[ date( 'Y-m-d H:i:s' ) ] = $message;

		update_option( self::INBOX_OPTION_ERRORS, $messages );
	}

	/**
	 * @param $slug
	 *
	 * @return array|mixed
	 */
	public function get_by_slug( $slug ) {

		if ( empty( $slug ) || ! is_string( $slug ) ) {
			return array();
		}

		$this->_refresh_messages();

		foreach ( $this->_existing_list as $id => $notification ) {
			if ( ! empty( $notification['slug'] ) && $notification['slug'] === $slug ) {
				return $notification;
			}
		}

		return array();
	}

	/**
	 * Verify if the message is already saved into the DB
	 * based on the message id { md5( $message['title') ] }
	 *
	 * @param $msg_id
	 *
	 * @return bool
	 * @throws Exception
	 */
	private function _message_exists( $msg_id ) {

		if ( ! $msg_id ) {
			throw new Exception( __METHOD__ . __( ' $msg_id can\'t be empty.', 'thrive-dash' ) );
		}

		// Set / refresh existing DB data to $_existing_list array
		$this->_refresh_messages();

		if ( isset( $this->_existing_list[ $msg_id ] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Grab the latest messages from DB
	 */
	private function _refresh_messages() {

		$this->_existing_list = $this->get_data();
	}

	/**
	 * @return bool
	 */
	private function _save() {

		if ( empty( $this->list ) ) {
			return false;
		}

		$this->_refresh_messages();

		$all_data = $this->list + $this->_existing_list;

		return ! empty( $all_data ) ? update_option( self::INBOX_OPTION_NAME, $all_data ) : false;
	}

	/**
	 * @return bool
	 */
	private function _update() {

		return ! empty( $this->_existing_list ) ? update_option( self::INBOX_OPTION_NAME, $this->_existing_list ) : false;
	}
}
