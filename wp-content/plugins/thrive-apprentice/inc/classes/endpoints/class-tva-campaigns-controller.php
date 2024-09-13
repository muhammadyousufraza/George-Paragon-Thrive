<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package ${NAMESPACE}
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

use TVA\Drip\Campaign;

/**
 * Class TVA_Campaigns_Controller
 *
 * @project: thrive-apprentice
 */
class TVA_Campaigns_Controller extends WP_REST_Controller {

	protected $rest_base = 'campaigns';

	public function register_routes() {

		register_rest_route( 'tva/v1', '/' . $this->rest_base, array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => array( 'TVA_Product', 'has_access' ),
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_item' ),
				'permission_callback' => array( 'TVA_Product', 'has_access' ),
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'mass_update' ),
				'permission_callback' => array( 'TVA_Product', 'has_access' ),
			),
		) );

		register_rest_route( 'tva/v1', '/' . $this->rest_base . '/(?P<ID>[\d]+)', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_item' ),
				'permission_callback' => array( 'TVA_Product', 'has_access' ),
				'args'                => array(
					'ID'               => array(
						'type'     => 'integer',
						'required' => true,
					),
					'post_title'       => array(
						'type'     => 'string',
						'required' => true,
					),
					'new_weekly_logic' => [
						'type'     => 'integer',
						'required' => false,
					],
				),
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_item' ),
				'permission_callback' => array( 'TVA_Product', 'has_access' ),
			),
		) );


		register_rest_route( 'tva/v1', '/' . $this->rest_base . '/new_weekly_logic', array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'update_new_weekly_logic' ),
				'permission_callback' => array( 'TVA_Product', 'has_access' ),
				'args'                => [],
			),
		) );

		register_rest_route( 'tva/v1', '/' . $this->rest_base . '/new_weekly_logic_hide_notice', array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'new_weekly_logic_hide_notice' ),
				'permission_callback' => array( 'TVA_Product', 'has_access' ),
				'args'                => [],
			),
		) );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function get_items( $request ) {
		$items = Campaign::get_items();

		return new WP_REST_Response(
			array(
				'total' => count( $items ),
				'items' => $items,
			), 200 );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return void|WP_Error|WP_REST_Response
	 */
	public function update_item( $request ) {
		$id = absint( $request->get_param( 'id' ) );
		if ( ! $id ) {
			return new WP_Error( 'invalid_id', 'Invalid campaign ID' );
		}
		$campaign = new Campaign( $id );
		$campaign->set_data( $request->get_params() );

		if ( ! empty( $request->get_param( 'new_weekly_logic' ) ) && (int) $request->get_param( 'new_weekly_logic' ) === 1 ) {
			$campaign->update_new_weekly_logic();
		}

		return $campaign->save();
	}

	/**
	 * Updates a flag that signifies that this campaign is ready for new weekly logic
	 *
	 * @return void
	 */
	public function update_new_weekly_logic() {
		$campaigns = Campaign::get_items_without_weekly_logic();

		foreach ( $campaigns as $campaign ) {
			$campaign->update_new_weekly_logic();
		}
	}

	public function new_weekly_logic_hide_notice() {
		update_option( 'tva_new_weekly_logic_hide_notice', 1, 'yes' );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return int|WP_Error|WP_REST_Response
	 */
	public function create_item( $request ) {
		$campaign = new Campaign( $request->get_params() );

		return $campaign->save()->update_new_weekly_logic();
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return int|WP_Error|WP_REST_Response
	 */
	public function delete_item( $request ) {
		$campaign = new Campaign( intval( $request->get_param( 'ID' ) ) );

		return $campaign->delete();
	}

	/**
	 * Mass-update a list of campaigns - just update the meta fields
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return int|WP_Error|WP_REST_Response
	 */
	public function mass_update( $request ) {
		foreach ( $request->get_params() as $campaign_data ) {
			$post_id = absint( $campaign_data['id'] );
			if ( $post_id ) {
				$campaign = new Campaign( $campaign_data );
				$campaign->update_meta_fields();
			}
		}
	}
}
