<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Dash_Api_CampaignMonitor_List {

	/**
	 * @var Thrive_Dash_Api_CampaignMonitor
	 */
	private $_manager;

	/**
	 * @var string|int
	 */
	protected $id;

	public function __construct( $id ) {

		$this->id = $id;
	}

	/**
	 * Makes a REST request for the current list to retrieve all its custom fields
	 *
	 * @return array
	 * @throws Exception
	 */
	public function get_custom_fields() {

		$custom_fields = array();
		$response      = $this->_manager->request( 'lists/' . $this->id . '/customfields', 'get' );

		foreach ( $response as $index => $custom_field ) {
			$custom_fields[] = array(
				'name' => $custom_field['FieldName'],
				'key'  => $custom_field['Key'],
			);
		}

		return $custom_fields;
	}

	/**
	 * Makes a post through API to create a custom field for a list. E.g. Phone
	 *
	 * @param $field
	 *
	 * @throws Exception
	 */
	public function create_custom_field( $field ) {

		$_default = array(
			'FieldName'                 => 'Test Field',
			'DataType'                  => 'Text',
			'Options'                   => array(),
			'VisibleInPreferenceCenter' => true,
		);

		$field = array_merge( $_default, $field );
		$data  = array(
			'body' => json_encode( $field ),
		);

		$this->_manager->request( 'lists/' . $this->id . '/customfields', 'post', $data );
	}

	/**
	 * Adds a subscriber through API Manager for current list
	 *
	 * @param $subscriber
	 *
	 * @throws Exception
	 */
	public function add_subscriber( $subscriber ) {

		$defaults = array(
			'ConsentToTrack' => 'Yes',
		);

		$subscriber = array_merge( $defaults, $subscriber );

		$data = array(
			'body' => json_encode( $subscriber ),
		);

		$this->_manager->request( 'subscribers/' . $this->id, 'post', $data );
	}

	/**
	 * Delete a subscriber
	 *
	 * @param $email
	 *
	 * @throws Exception
	 */
	public function delete_subscriber( $email ) {

		$data = array(
			'body' => array( 'email' => $email )
		);

		$this->_manager->request( 'subscribers/' . $this->id, 'DELETE', $data );
	}

	/**
	 * Working with current list instance requires a manager which is able to make requests
	 *
	 * @param $manager Thrive_Dash_Api_CampaignMonitor
	 *
	 * @throws Exception
	 */
	public function set_manager( $manager ) {

		if ( false === $manager instanceof Thrive_Dash_Api_CampaignMonitor ) {
			throw new Exception( 'Invalid manager for current list', 400 );
		}

		$this->_manager = $manager;
	}
}
