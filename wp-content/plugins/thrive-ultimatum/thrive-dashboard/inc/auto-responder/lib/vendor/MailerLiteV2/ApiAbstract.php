<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

abstract class Thrive_Dash_Api_MailerLiteV2_ApiAbstract {

	/**
	 * @var Thrive_Dash_Api_MailerLiteV2_RestClient
	 */
	protected $rest_client;

	/**
	 * Endpoint for each request
	 */
	protected $endpoint;

	/**
	 * Limit in a query
	 */
	private $_limit;

	/**
	 * Offset in a query
	 */
	private $_offset;

	/**
	 * Order in a query
	 */
	private $_orders;
	/**
	 * For the where conditions
	 */
	private $_where;

	public function __construct( Thrive_Dash_Api_MailerLiteV2_RestClient $rest_client ) {
		$this->rest_client = $rest_client;
	}

	/**
	 * Get collection of items
	 *
	 * @param array $fields
	 *
	 * @return Thrive_Dash_Api_MailerLite_Collection
	 * @throws Thrive_Dash_Api_MailerLite_MailerLiteSdkException
	 */
	public function get( $fields = array( '*' => '' ) ) {
		$params = $this->prepare_params();

		if ( ! empty( $fields ) && is_array( $fields ) && $fields != $fields['*'] ) {
			$params['fields'] = $fields;
		}

		$response = $this->rest_client->get( $this->endpoint, $params );

		return $response['data'];
	}

	/**
	 * Get single item
	 *
	 * @param $id
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function find( $id ) {
		if ( empty( $id ) ) {
			throw new Exception( 'ID must be set' );
		}

		$response = $this->rest_client->get( $this->endpoint . '/' . $id );

		return $response['body'];
	}

	/**
	 * Create new item
	 *
	 * @param $data
	 *
	 * @return mixed
	 * @throws Thrive_Dash_Api_MailerLite_MailerLiteSdkException
	 */
	public function create( $data ) {
		$response = $this->rest_client->post( $this->endpoint, $data );

		return $response['body'];
	}

	/**
	 * Update an item
	 *
	 * @param $id
	 * @param $data
	 *
	 * @return mixed
	 * @throws Thrive_Dash_Api_MailerLite_MailerLiteSdkException
	 */
	public function update( $id, $data ) {
		$response = $this->rest_client->put( $this->endpoint . '/' . $id, $data );

		return $response['body'];
	}

	/**
	 * Delete an item
	 *
	 * @param $id
	 *
	 * @return mixed
	 * @throws Thrive_Dash_Api_MailerLite_MailerLiteSdkException
	 */
	public function delete( $id ) {
		$response = $this->rest_client->delete( $this->endpoint . '/' . $id );

		return $response['body'];
	}

	/**
	 * Return only count of items
	 *
	 * @return mixed
	 * @throws Thrive_Dash_Api_MailerLite_MailerLiteSdkException
	 */
	public function count() {
		$response = $this->rest_client->get( $this->endpoint . '/count' );

		return $response['body'];
	}

	/**
	 * Set size of limit in query
	 *
	 * @param $limit
	 *
	 * @return $this
	 */
	public function limit( $limit ) {
		$this->_limit = $limit;

		return $this;
	}

	/**
	 * Set size of offset in query
	 *
	 * @param $offset
	 *
	 * @return $this
	 */
	public function offset( $offset ) {
		$this->_offset = $offset;

		return $this;
	}

	/**
	 * Set an order in of items in query
	 *
	 * @param        $field
	 * @param string $order
	 *
	 * @return $this
	 */
	public function orderBy( $field, $order = 'ASC' ) {
		$this->_orders[ $field ] = $order;

		return $this;
	}

	/**
	 * Set where conditions
	 *
	 * @param  [type] $column
	 * @param  [type] $operator
	 * @param  [type] $value
	 * @param string $boolean
	 *
	 * @return $this
	 */
	public function where(
		$column,
		$operator = null,
		$value = null,
		$boolean = 'and'
	) {
		if ( is_array( $column ) ) {
			$this->_where = $column;
		}

		return $this;
	}

	/**
	 * Prepare query parameters
	 *
	 * @return array
	 */
	protected function prepare_params() {
		$params = array();

		if ( ! empty( $this->_where ) && is_array( $this->_where ) ) {
			$params['filters'] = $this->_where;
		}

		if ( ! empty( $this->_offset ) ) {
			$params['offset'] = $this->_offset;
		}

		if ( ! empty( $this->_limit ) ) {
			$params['limit'] = $this->_limit;
		}

		if ( ! empty( $this->_orders ) && is_array( $this->_orders ) ) {
			foreach ( $this->_orders as $field => $order ) {
				$params['order_by'][ $field ] = $order;
			}
		}

		return $params;
	}

	/**
	 * @return string[]
	 */

	public function get_allowed_types() {
		return array(
			'text',
		);
	}

	/**
	 * @param $field
	 *
	 * @return array
	 */
	public function get_normalize_custom_field( $field ) {

		$field = (array) $field;

		return array(
			'id'    => isset( $field['id'] ) ? $field['id'] : '',
			'name'  => ! empty( $field['name'] ) ? $field['name'] : '',
			'type'  => ! empty( $field['type'] ) ? $field['type'] : '',
			'label' => ! empty( $field['name'] ) ? $field['name'] : '',
			'key'   => ! empty( $field['key'] ) ? $field['key'] : '',
		);
	}

}
