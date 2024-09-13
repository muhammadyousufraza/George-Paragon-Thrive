<?php


use TVA\TTB\Main;
use TVA\TTB\Skin;

class TVA_Skins_Controller extends TVA_REST_Controller {
	public $base = 'skins';

	public function register_routes() {
		parent::register_routes();

		/* set a skin as default */
		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/set-default', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'set_default' ),
				'permission_callback' => array( $this, 'permissions_check' ),
				'args'                => array(
					'id' => array(
						'required' => true,
						'type'     => 'integer',
					),
				),
			),
		) );

		/* duplicate skin */
		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/duplicate', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'duplicate' ),
				'permission_callback' => array( $this, 'permissions_check' ),
				'args'                => array(
					'id' => array(
						'required' => true,
						'type'     => 'integer',
					),
				),
			),
		) );

		/* get installed skins */
		register_rest_route( self::$namespace . self::$version, '/' . $this->base, array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'permissions_check' ),
				'args'                => array(),
			),
		) );

		/* get cloud skins */
		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/cloud', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_cloud_items' ),
				'permission_callback' => array( $this, 'permissions_check' ),
				'args'                => array(),
			),
		) );

		/* download cloud skin */
		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/cloud/' . '(?P<id>.+)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'download_cloud_item' ),
				'permission_callback' => array( $this, 'permissions_check' ),
				'args'                => array(
					'id' => array(
						'type'     => 'string',
						'required' => true,
					),
				),
			),
		) );

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/(?P<id>[\d]+)/change_palette', [
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'change_palette' ],
				'permission_callback' => [ $this, 'permissions_check' ],
				'args'                => [
					'id'          => array(
						'type'     => 'string',
						'required' => true,
					),
					'active_id'   => [
						'type'     => 'integer',
						'required' => true,
					],
					'previous_id' => [
						'type'     => 'integer',
						'required' => true,
					],
				],
			],
		] );

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/(?P<id>[\d]+)/patch', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => [ $this, 'patch_item' ],
				'permission_callback' => [ $this, 'permissions_check' ],
				'args'                => [
					'id'    => [
						'type'     => 'int',
						'required' => true,
					],
					'patch' => [
						'type'     => 'string',
						'required' => true,
						'enum'     => [ 'reset-typography', 'inherit-typography' ],
					],
				],
			),
		) );
	}

	/**
	 * Called when a palette is changed from the UI
	 *
	 * Changes a color palette and saved the modification for the previous palette for later use
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function change_palette( $request ) {
		$previous_id = (int) $request->get_param( 'previous_id' );
		$active_id   = (int) $request->get_param( 'active_id' );
		$skin_id     = (int) $request->get_param( 'id' );

		if ( $previous_id === $active_id ) {
			//We do nothing here
			return new WP_REST_Response( [ 'success' => 1 ], 200 );
		}

		$thrive_skin         = Main::skin( $skin_id );
		$config              = $thrive_skin->get_palettes();
		$config['active_id'] = $active_id;
		$thrive_skin->update_palettes( $config, 2 );

		\TVA\TTB\tva_palettes()->update_master_hsl( $config['palettes'][ $active_id ]['modified_hsl'] );

		return new WP_REST_Response( [ 'success' => 1 ], 200 );
	}

	/**
	 * Get a collection of items
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {

		$items = Main::get_all_skins();
		$data  = array();
		foreach ( $items as $item ) {
			$item_data = $this->prepare_item_for_response( $item, $request );
			$data[]    = $this->prepare_response_for_collection( $item_data );
		}

		return new WP_REST_Response( $data, 200 );
	}

	/**
	 * Set a skin as default
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function set_default( $request ) {
		$id = $request->get_param( 'id' );
		if ( $id === - 1 ) { // activate the legacy editor
			update_option( 'tva_default_skin', 0 );
			Main::show_legacy_design(); // make sure this is shown
			Main::set_use_builder_templates( 0 );
		} else {
			Main::skin( $id )->activate();
		}

		return $this->get_items( $request );
	}

	/**
	 * Delete a skin
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Request|WP_REST_Response
	 */
	public function delete_item( $request ) {
		$id = $request->get_param( 'id' );

		if ( $id === 0 ) {
			// just hide the legacy design
			$can_remove = Main::uses_builder_templates();
		} else {
			$can_remove = Main::get_default_skin_id() !== $id;
		}

		if ( ! $can_remove ) {
			return new WP_Error( 'delete_published_design', 'Cannot remove a published design', [ 'status' => 422 ] );
		}

		if ( $id === 0 ) {
			Main::hide_legacy_design();
		} else {
			$skin = new Skin( $id );

			$skin->remove();
			wp_delete_term( $id, SKIN_TAXONOMY );
		}

		return $this->get_items( $request );
	}

	/**
	 * Get one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item( $request ) {
		$id = $request->get_param( 'id' );

		return rest_ensure_response( new Skin( $id ) );
	}

	/**
	 * Create one item from the collection
	 *
	 * Just a temporary solution
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function create_item( $request ) {

		$name = $request->get_param( 'name' );
		if ( ! $name ) {
			$name = 'rand ' . random_int( 0, 1000000 );
		}

		$request->set_param( 'id', Main::create_skin( $name ) );

		return $this->get_item( $request );
	}

	/**
	 * Patch a skin. Supports various operations
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function patch_item( $request ) {
		$skin_id = (int) $request->get_param( 'id' );
		$skin    = Main::skin( $skin_id );

		if ( ! $skin || $skin->ID !== $skin_id ) {
			return new WP_Error( 'invalid_skin', 'Design not found', [ 'status' => 404 ] );
		}

		switch ( $request->get_param( 'patch' ) ) {
			case 'reset-typography':
				$skin->get_active_typography( 'object' )->reset();
				break;
			case 'inherit-typography':
				$skin->inherit_typography( (bool) $request->get_param( 'value' ) );
				break;
			default:
				return new WP_Error( 'invalid_patch', 'Invalid patch action', [ 'status' => 422 ] );
		}

		$request->set_param( 'id', $skin->ID );

		return $this->get_item( $request );
	}


	/**
	 * Update one skin. Currently only handles updating the term name.
	 *
	 * @param WP_REST_Request $request request object
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function update_item( $request ) {
		$name    = $request->get_param( 'name' );
		$term_id = $request->get_param( 'term_id' );

		if ( ! trim( $name ) || ! $term_id ) {
			return new WP_Error( 'cant-update', __( 'Name and term id are required', 'thrive-apprentice' ), array( 'status' => 422 ) );
		}

		wp_update_term( $term_id, SKIN_TAXONOMY, [
			'name' => $name,
		] );

		$request->set_param( 'id', $term_id );

		return $this->get_item( $request );
	}

	/**
	 * Duplicate a skin
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function duplicate( $request ) {
		$id = $request->get_param( 'id' );
		if ( $id === 0 ) { // activate the legacy editor
			return new WP_Error( 'duplicate_default', __( 'Cannot duplicate legacy design', 'thrive-apprentice' ), [ 'status' => 422 ] );
		}
		Main::skin( $id )->duplicate();

		return $this->get_items( $request );
	}

	/**
	 * This should only be available for admins
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return bool|WP_Error
	 */
	public function permissions_check( $request ) {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Get the list of skins from the cloud
	 *
	 * @param WP_REST_Request $request request object
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_cloud_items( $request ) {
		if ( $request->get_param( 'bypass_cache' ) ) {
			add_filter( 'thrive_theme_bypass_cloud_transient', '__return_true' );
		}

		return rest_ensure_response( Thrive_Skin_Taxonomy::get_cloud_skins( 'tva' ) );
	}

	/**
	 * Download a skin from the cloud
	 *
	 * @param WP_REST_Request $request request object
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function download_cloud_item( $request ) {
		$cloud_id = $request->get_param( 'id' );
		$name     = sanitize_text_field( $request->get_param( 'skin_name' ) );

		//Reset the sanity check before downloading a new skin
		Main::reset_sanity_check();;

		/* First download the skin from the cloud */
		try {
			require_once TVA_Const::plugin_path( 'ttb-bridge/classes/Transfer/class-api-skins.php' );
			/** @var \TVA\TTB\Transfer\Api_Skins $api */
			$api          = \TVA\TTB\Transfer\Api_Skins::getInstance();
			$archive_file = $api->download_item( $cloud_id, $request );
			$thumb        = $api->get_thumb_from_header();
		} catch ( Exception $e ) {
			return new WP_Error( 'error_skin_download', $e->getMessage(), [ 'status' => 500 ] );
		}

		require_once TVA_Const::plugin_path( 'ttb-bridge/classes/Transfer/class-import.php' );

		/* If everything is ok with the download go ahead and import the skin */
		try {
			$import   = new \TVA\TTB\Transfer\Import( $archive_file );
			$response = $import->import( 'skin' );

		} catch ( Exception $e ) {
			return new WP_Error( 'error_skin_import', $e->getMessage(), [ 'status' => 500 ] );
		}

		if ( empty( $response ) ) {
			return new WP_Error( 'invalid_skin_import', 'Skin could not be imported', [ 'status' => 500 ] );
		}

		$skin = new Skin( $response );
		$skin->ensure_scope();

		if ( $request->get_param( 'activate' ) ) {
			$skin->activate();
		}

		if ( $request->get_param( 'fresh_install' ) ) {
			Main::hide_legacy_design();
			tva_get_settings_manager()->save_setting( 'visual_editor_welcome', 1 );
			tva_get_settings_manager()->save_setting( 'wizard', 1 ); // mark the startup wizard as completed
		}

		if ( ! empty( $thumb ) ) {
			$thumb = str_replace( [ '..', '/', '\\' ], '', $thumb );
			$skin->set_meta( 'tva_thumb', $thumb );
		}

		if ( ! empty( $name ) ) {
			wp_update_term( $skin->term_id, SKIN_TAXONOMY, [
				'name' => $name,
			] );
		}

		return new WP_REST_Response( $skin, 200 );
	}
}
