<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package ignition-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

/**
 * Base class for extending the WP_REST_Controller taken from http://v2.wp-api.org/extending/adding/
 */
class TVA_REST_Controller {

	public static $version   = 1;
	public static $namespace = 'tva/v';
	public        $base      = '';

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {

		register_rest_route( self::$namespace . self::$version, '/' . $this->base, array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'permissions_check' ),
				'args'                => array(),
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_item' ),
				'permission_callback' => array( $this, 'permissions_check' ),
				'args'                => $this->get_endpoint_args_for_item_schema( true ),
			),
		) );

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/table', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_table_items' ),
				'permission_callback' => array( $this, 'permissions_check' ),
				'args'                => array(),
			),
		) );

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/(?P<id>[\d]+)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_item' ),
				'permission_callback' => array( $this, 'permissions_check' ),
				'args'                => array(),
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_item' ),
				'permission_callback' => array( $this, 'permissions_check' ),
				'args'                => $this->get_endpoint_args_for_item_schema( false ),
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_item' ),
				'permission_callback' => array( $this, 'permissions_check' ),
				'args'                => array(
					'id' => array(
						'type' => 'integer',
					),
				),
			),
		) );
	}

	/**
	 * Get an array of endpoint arguments from the item schema for the controller.
	 *
	 * @param string $method HTTP method of the request. The arguments
	 *                       for `CREATABLE` requests are checked for required
	 *                       values and may fall-back to a given default, this
	 *                       is not done on `EDITABLE` requests. Default is
	 *                       WP_REST_Server::CREATABLE.
	 *
	 * @return array $endpoint_args
	 */
	public function get_endpoint_args_for_item_schema( $method = WP_REST_Server::CREATABLE ) {

		$schema            = $this->get_item_schema();
		$schema_properties = ! empty( $schema['properties'] ) ? $schema['properties'] : array();
		$endpoint_args     = array();

		foreach ( $schema_properties as $field_id => $params ) {

			// Arguments specified as `readonly` are not allowed to be set.
			if ( ! empty( $params['readonly'] ) ) {
				continue;
			}

			$endpoint_args[ $field_id ] = array(
				'validate_callback' => array( $this, 'validate_schema_property' ),
				'sanitize_callback' => array( $this, 'sanitize_schema_property' ),
			);

			if ( WP_REST_Server::CREATABLE === $method && isset( $params['default'] ) ) {
				$endpoint_args[ $field_id ]['default'] = $params['default'];
			}

			if ( WP_REST_Server::CREATABLE === $method && ! empty( $params['required'] ) ) {
				$endpoint_args[ $field_id ]['required'] = true;
			}

			// Merge in any options provided by the schema property.
			if ( isset( $params['arg_options'] ) ) {

				// Only use required / default from arg_options on CREATABLE endpoints.
				if ( WP_REST_Server::CREATABLE !== $method ) {
					$params['arg_options'] = array_diff_key( $params['arg_options'], array(
						'required' => '',
						'default'  => '',
					) );
				}

				$endpoint_args[ $field_id ] = array_merge( $endpoint_args[ $field_id ], $params['arg_options'] );
			}
		}

		return $endpoint_args;
	}

	/**
	 * Get the item's schema, conforming to JSON Schema.
	 *
	 * @return array
	 */
	public function get_item_schema() {
		return $this->add_additional_fields_schema( array() );
	}

	/**
	 * Add the schema from additional fields to an schema array.
	 *
	 * The type of object is inferred from the passed schema.
	 *
	 * @param array $schema Schema array.
	 *
	 * @return array
	 */
	protected function add_additional_fields_schema( $schema ) {
		if ( empty( $schema['title'] ) ) {
			return $schema;
		}

		/**
		 * Can't use $this->get_object_type otherwise we cause an inf loop.
		 */
		$object_type = $schema['title'];

		$additional_fields = $this->get_additional_fields( $object_type );

		foreach ( $additional_fields as $field_name => $field_options ) {
			if ( ! $field_options['schema'] ) {
				continue;
			}

			$schema['properties'][ $field_name ] = $field_options['schema'];
		}

		return $schema;
	}

	/**
	 * Get all the registered additional fields for a given object-type.
	 *
	 * @param string $object_type
	 *
	 * @return array
	 */
	protected function get_additional_fields( $object_type = null ) {

		if ( ! $object_type ) {
			$object_type = $this->get_object_type();
		}

		if ( ! $object_type ) {
			return array();
		}

		global $wp_rest_additional_fields;

		if ( ! $wp_rest_additional_fields || ! isset( $wp_rest_additional_fields[ $object_type ] ) ) {
			return array();
		}

		return $wp_rest_additional_fields[ $object_type ];
	}

	/**
	 * Get the object type this controller is responsible for managing.
	 *
	 * @return string
	 */
	protected function get_object_type() {
		$schema = $this->get_item_schema();

		if ( ! $schema || ! isset( $schema['title'] ) ) {
			return null;
		}

		return $schema['title'];
	}

	/**
	 * Get a collection of items
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_table_items( $request ) {
		$items = array(); //do a query, call another class, etc
		$data  = array();
		foreach ( $items as $item ) {
			$itemdata = $this->prepare_item_for_response( $item, $request );
			$data[]   = $this->prepare_response_for_collection( $itemdata );
		}

		return new WP_REST_Response( $data, 200 );
	}

	/**
	 * Prepare the item for the REST response
	 *
	 * @param mixed           $item    WordPress representation of the item.
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return mixed
	 */
	public function prepare_item_for_response( $item, $request ) {
		return $item;
	}

	/**
	 * Prepare a response for inserting into a collection.
	 *
	 * @param WP_REST_Response $response Response object.
	 *
	 * @return array|WP_REST_Response Response data, ready for insertion into collection data.
	 */
	public function prepare_response_for_collection( $response ) {
		if ( ! ( $response instanceof WP_REST_Response ) ) {
			return $response;
		}

		$data  = (array) $response->get_data();
		$links = WP_REST_Server::get_response_links( $response );
		if ( ! empty( $links ) ) {
			$data['_links'] = $links;
		}

		return $data;
	}

	/**
	 * Get a collection of items
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {
		$items = array(); //do a query, call another class, etc
		$data  = array();
		foreach ( $items as $item ) {
			$item_data = $this->prepare_item_for_response( $item, $request );
			$data[]    = $this->prepare_response_for_collection( $item_data );
		}

		return new WP_REST_Response( $data, 200 );
	}

	/**
	 * Get one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item( $request ) {
		//get parameters from request
		$params = $request->get_params();
		$item   = array();//do a query, call another class, etc
		$data   = $this->prepare_item_for_response( $item, $request );

		//return a response or error based on some conditional
		if ( 1 == 1 ) {
			return new WP_REST_Response( $data, 200 );
		} else {
			return new WP_Error( 'code', __( 'message', 'thrive-apprentice' ) );
		}
	}

	/**
	 * Create one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function create_item( $request ) {

		$item = $this->prepare_item_for_database( $request );

		if ( function_exists( 'slug_some_function_to_create_item' ) ) {
			$data = slug_some_function_to_create_item( $item );
			if ( is_array( $data ) ) {
				return new WP_REST_Response( $data, 200 );
			}
		}

		return new WP_Error( 'cant-create', __( 'message', 'thrive-apprentice' ), array( 'status' => 500 ) );
	}

	/**
	 * Prepare the item for create or update operation
	 *
	 * @param WP_REST_Request $request Request object
	 *
	 * @return WP_Error|array $prepared_item
	 */
	protected function prepare_item_for_database( $request ) {
		return array();
	}

	/**
	 * Update one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function update_item( $request ) {
		$item = $this->prepare_item_for_database( $request );

		if ( function_exists( 'slug_some_function_to_update_item' ) ) {
			$data = slug_some_function_to_update_item( $item );
			if ( is_array( $data ) ) {
				return new WP_REST_Response( $data, 200 );
			}
		}

		return new WP_Error( 'cant-update', __( 'message', 'thrive-apprentice' ), array( 'status' => 500 ) );

	}

	/**
	 * Delete one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function delete_item( $request ) {
		$item = $this->prepare_item_for_database( $request );

		if ( function_exists( 'slug_some_function_to_delete_item' ) ) {
			$deleted = slug_some_function_to_delete_item( $item );
			if ( $deleted ) {
				return new WP_REST_Response( true, 200 );
			}
		}

		return new WP_Error( 'cant-delete', __( 'message', 'thrive-apprentice' ), array( 'status' => 500 ) );
	}

	/**
	 * Check if a given request has access to the product
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|bool
	 */
	public function permissions_check( $request ) {

		return TVA_Product::has_access();
	}

	/**
	 * Get the query params for collections
	 *
	 * @return array
	 */
	public function get_collection_params() {
		return array(
			'page'     => array(
				'description'       => 'Current page of the collection.',
				'type'              => 'integer',
				'default'           => 1,
				'sanitize_callback' => 'absint',
			),
			'per_page' => array(
				'description'       => 'Maximum number of items to be returned in result set.',
				'type'              => 'integer',
				'default'           => 10,
				'sanitize_callback' => 'absint',
			),
			'search'   => array(
				'description'       => 'Limit results to those matching a string.',
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
		);
	}

	/**
	 * API Token Authorization
	 *
	 * @param $request
	 *
	 * @return bool|WP_Error
	 */
	public function api_token_permission_check( $request ) {

		if ( ! empty( $_SERVER['HTTP_TVA'] ) ) {
			$_SERVER['PHP_AUTH_PW'] = $_SERVER['HTTP_TVA'];
		}

		return $this->basic_authorization_permission_check( $request );
	}

	/**
	 * Permission check for basic authorization endpoint
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return true|WP_Error
	 */
	public function basic_authorization_permission_check( $request ) {

		$header   = null;
		$auth_key = $request->get_param( 'auth' );

		if ( function_exists( 'getallheaders' ) ) {
			$all_header = getallheaders();
			$header     = ! empty( $all_header['Authorization'] ) ? $all_header['Authorization'] : null;
		}

		if ( ! $header ) {
			if ( ! empty( $_SERVER['PHP_AUTH_PW'] ) ) {
				$header = 'Basic ' . base64_encode( 'username:' . $_SERVER['PHP_AUTH_PW'] );
			} else {
				$header = $request->get_header( 'Authorization' );
			}
		}

		if ( empty( $header ) && empty( $auth_key ) ) {

			$error = new WP_Error( 'authentication_failure', __( 'Authentication failed due to invalid authentication credentials.', 'thrive-apprentice' ), array( 'status' => 401 ) );

			TVA_Logger::set_type( 'REQUEST Auth' );
			TVA_Logger::log(
				$error->get_error_code(),
				$_SERVER,
				true
			);

			return $error;
		}

		$auth     = base64_decode( str_replace( 'Basic ', '', $header ? $header : $auth_key ) );
		$username = null;
		$password = null;

		if ( strpos( $auth, ':' ) ) {
			list( $username, $password ) = explode( ':', $auth );
		}

		$username = trim( $username );
		$password = trim( $password );

		$has_access = TVA_Token::auth( $username, $password );

		if ( ! $has_access ) {

			$error = new WP_Error( 'not_authorized', __( 'Authorization failed due to insufficient permissions.', 'thrive-apprentice' ), array( 'status' => 403 ) );

			TVA_Logger::set_type( 'REQUEST Auth' );
			TVA_Logger::log(
				$error->get_error_code(),
				$_SERVER,
				true
			);

			return $error;
		}

		TVA_Logger::set_type( 'REQUEST Auth' );
		TVA_Logger::log(
			'user_authorized',
			$_SERVER,
			true
		);

		return true;
	}

	/**
	 * Returns a contextual HTTP error code for authorization failure.
	 *
	 * @return integer
	 */
	public function rest_authorization_required_code() {
		return is_user_logged_in() ? 403 : 401;
	}
}
