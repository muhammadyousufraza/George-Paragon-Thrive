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
 * Class TVD_Content_Sets_Controller
 *
 * @project: thrive-dashboard
 */
class TVD_Content_Sets_Controller extends WP_REST_Controller {
	/**
	 * @var string Base name
	 */
	public $rest_base = 'content-sets';

	protected $version   = 1;
	protected $namespace = 'tss/v';

	/**
	 * Register routes function
	 */
	public function register_routes() {
		register_rest_route( $this->namespace . $this->version, $this->rest_base . '/normalize-rule', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'normalize_rule' ),
				'permission_callback' => array( $this, 'permission_check' ),
				'args'                => array(
					'content_type' => array(
						'type'     => 'string',
						'required' => true,
						'enum'     => array(
							'post',
							'term',
							'archive',
							\TVD\Content_Sets\Rule::HIDDEN_POST_TYPE_SEARCH_RESULTS,
							\TVD\Content_Sets\Rule::HIDDEN_POST_TYPE_BLOG,
						),
					),
					'content'      => array(
						'type'     => 'string',
						'required' => true,
					),
					'field'        => array(
						'type'     => 'string',
						'required' => true,
					),
					'operator'     => array(
						'type'     => 'string',
						'required' => true,
					),
					'value'        => array(
						'type'    => array( 'string', 'array' ),
						'default' => '',
					),
				),
			),
		) );

		register_rest_route( $this->namespace . $this->version, $this->rest_base, array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_item' ),
				'permission_callback' => array( $this, 'permission_check' ),
				'args'                => array(
					'post_title'   => array(
						'type'     => 'string',
						'required' => true,
					),
					'post_content' => array(
						'type'     => 'array',
						'required' => true,
					),
				),
			),
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'search_items' ),
				'permission_callback' => array( $this, 'permission_check' ),
				'args'                => array(
					'content_type' => array(
						'type'     => 'string',
						'required' => true,
						'enum'     => array( 'post', 'term', 'archive' ),
					),
					'content'      => array(
						'type'     => 'string',
						'required' => true,
					),
					'field'        => array(
						'type'     => 'string',
						'required' => true,
					),
					'operator'     => array(
						'type'     => 'string',
						'required' => true,
					),
					'value'        => array(
						'type'    => array( 'string', 'array' ),
						'default' => '',
					),
					'query_string' => array(
						'type'     => 'string',
						'required' => true,
					),
				),
			),
		) );

		register_rest_route( $this->namespace . $this->version, $this->rest_base . '/(?P<ID>.+)', array(
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_item' ),
				'permission_callback' => array( $this, 'permission_check' ),
				'args'                => array(
					'ID' => array(
						'type'     => 'integer',
						'required' => true,
					),
				),
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_item' ),
				'permission_callback' => array( $this, 'permission_check' ),
				'args'                => array(
					'ID'           => array(
						'type'     => 'integer',
						'required' => true,
					),
					'post_title'   => array(
						'type'     => 'string',
						'required' => true,
					),
					'post_content' => array(
						'type'     => 'array',
						'required' => true,
					),
				),
			),
		) );
	}

	/**
	 * rename this into search posts/ search items
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function search_items( $request ) {
		$rule  = \TVD\Content_Sets\Rule::factory( $request->get_params() );
		$paged = false;
		if ( $request->get_param( 'page' ) ) {
			$paged    = (int) $request->get_param( 'page' );
			$per_page = (int) $request->get_param( 'per_page' ) ?: 15;
		}

		return new WP_REST_Response( $rule->get_items( $request->get_param( 'query_string' ), $paged, $per_page ), 200 );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function create_item( $request ) {
		$set = new \TVD\Content_Sets\Set( $request->get_params() );

		$new_set_id = $set->create();

		$cache_plugin = tve_dash_detect_cache_plugin();
		if ( $cache_plugin ) {
			tve_dash_cache_plugin_clear( $cache_plugin );
		}

		if ( ! empty( $new_set_id ) ) {
			return new WP_REST_Response( \TVD\Content_Sets\Set::get_items(), 200 );
		}

		return new WP_Error( 'cant-create', __( 'The request contains invalid rules', 'thrive-dash' ), array( 'status' => 422 ) );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function delete_item( $request ) {
		$set      = new \TVD\Content_Sets\Set( $request->get_param( 'ID' ) );
		$response = $set->delete();

		return new WP_REST_Response( $response, 200 );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function update_item( $request ) {
		$set = new \TVD\Content_Sets\Set( $request->get_params() );

		$set_id = $set->update();

		$cache_plugin = tve_dash_detect_cache_plugin();
		if ( $cache_plugin ) {
			tve_dash_cache_plugin_clear( $cache_plugin );
		}

		if ( ! empty( $set_id ) ) {
			return new WP_REST_Response( ! empty( $request->get_param( 'return_one' ) ) ? $set->jsonSerialize() : \TVD\Content_Sets\Set::get_items(), 200 );
		}

		return new WP_Error( 'cant-update', __( 'The request contains invalid rules', 'thrive-dash' ), array( 'status' => 422 ) );
	}

	/**
	 * Normalizing a rule so that the rule contains all information
	 *
	 * Mainly this is for content_label rule key.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function normalize_rule( $request ) {
		$rule = \TVD\Content_Sets\Rule::factory( $request->get_params() );

		return new WP_REST_Response( $rule, 200 );
	}


	/**
	 * If the user has access to the admin pages, then he is allowed to perform any operation on the scripts.
	 *
	 * @return bool
	 */
	public function permission_check() {
		return current_user_can( TVE_DASH_CAPABILITY );
	}
}
