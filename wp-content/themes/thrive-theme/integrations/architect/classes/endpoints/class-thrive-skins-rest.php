<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Thrive_Skins_Rest
 */
class Thrive_Skins_Rest extends WP_REST_Terms_Controller {

	use Thrive_Term_Meta;

	public static $version = 1;
	public static $route   = '/skins';

	public static function instance() {
		return new static();
	}

	public function __construct() {
		parent::__construct( SKIN_TAXONOMY );

		$this->namespace = TTB_REST_NAMESPACE;
		$this->rest_base = static::$route;

		$this->register_routes();
		$this->hooks();
		$this->register_meta_fields();

		static::register_term_routes( $this->namespace, static::$route, [ $this, 'route_permission' ] );
	}

	public function register_routes() {
		parent::register_routes();

		register_rest_route( $this->namespace, static::$route . '/export', [
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'export' ],
				'permission_callback' => [ $this, 'route_permission' ],
			],
		] );

		register_rest_route( $this->namespace, static::$route . '/import', [
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'import' ],
				'permission_callback' => [ $this, 'route_permission' ],

			],
		] );

		register_rest_route( $this->namespace, static::$route . '/cloud', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'fetch_cloud_skins' ],
				'permission_callback' => [ $this, 'route_permission' ],

			],
		] );

		register_rest_route( $this->namespace, static::$route . '/cloud_import', [
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'cloud_import' ],
				'permission_callback' => [ $this, 'route_permission' ],

			],
		] );

		register_rest_route( $this->namespace, static::$route . '/(?P<skin_id>[\d]+)/skin_variables', [
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'skin_variables' ],
				'permission_callback' => [ $this, 'route_permission' ],
				'args'                => [
					'color' => [
						'type'     => 'string',
						'required' => true,
					],
					'id'    => [
						'type'     => 'integer',
						'required' => true,
					],
					'h'     => [
						'type'     => 'integer',
						'required' => false,
					],
					's'     => [
						'type'     => 'number',
						'required' => false,
					],
					'l'     => [
						'type'     => 'number',
						'required' => false,
					],
				],
			],
		] );

		register_rest_route( $this->namespace, static::$route . '/(?P<id>[\d]+)/change_palette', [
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'change_palette' ],
				'permission_callback' => [ $this, 'route_permission' ],
				'args'                => [
					'active_id'   => [
						'type'     => 'integer',
						'required' => true,
					],
					'previous_id' => [
						'type'     => 'integer',
						'required' => true,
					],
					'version'     => [
						'type'     => 'integer',
						'required' => true,
					],
					'master_id'   => [
						'type'     => 'integer',
						'required' => false,
					],
				],
			],
		] );

		register_rest_route( $this->namespace, static::$route . '/(?P<id>[\d]+)/reset_palette', [
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'reset_palette' ],
				'permission_callback' => [ $this, 'route_permission' ],
				'args'                => [
					'active_id' => [
						'type'     => 'integer',
						'required' => true,
					],
				],

			],
		] );
	}

	/**
	 * Check if a given request has access to route
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|bool
	 */
	public function route_permission( $request ) {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Hooks to change the terms rest api
	 */
	public function hooks() {
		add_action( 'rest_insert_' . SKIN_TAXONOMY, [ $this, 'after_skin_insert' ], 10, 3 );
		add_filter( 'rest_prepare_' . SKIN_TAXONOMY, [ $this, 'skin_prepare_response' ], 10, 2 );
	}

	/**
	 * Add meta fields to be able to update / get them with the rest api
	 */
	public function register_meta_fields() {
		/* for each meta field, register a new rest field */
		foreach ( Thrive_Skin::meta_fields() as $meta_field => $default_value ) {
			$get_callback    = 'get_' . $meta_field;
			$update_callback = 'update_' . $meta_field;

			/* add a get callback and an update callback, if they exist (null is the default callback value) */
			register_rest_field( $this->get_object_type(), $meta_field, [
				'get_callback'    => method_exists( $this, $get_callback ) ? [ $this, $get_callback ] : null,
				'update_callback' => method_exists( $this, $update_callback ) ? [ $this, $update_callback ] : null,
			] );
		}
	}

	/**
	 * @param WP_Term         $term     Inserted or updated term object.
	 * @param WP_REST_Request $request  Request object.
	 * @param bool            $creating True when creating a term, false when updating.
	 */
	public function after_skin_insert( $term, $request, $creating ) {
		if ( $creating ) {
			$skin_id        = $term->term_id;
			$source_skin_id = $request->get_param( 'source_skin_id' );

			if ( $source_skin_id ) {
				$new_skin = new Thrive_Skin( $skin_id );

				/* duplicate the meta fields from the source to the new skin */
				$new_skin->duplicate_meta( $source_skin_id );

				/* set the 'is_active' meta as inactive (we don't want to activate the duplicated skin) */
				$new_skin->set_meta( Thrive_Skin::SKIN_META_ACTIVE, 0 );
			}

			/* create templates */
			Thrive_Theme_Default_Data::create_skin_templates( $skin_id, $source_skin_id );

			/* create default typography */
			Thrive_Theme_Default_Data::create_skin_typographies( $skin_id, $source_skin_id );

			do_action( 'theme_after_skin_insert', $skin_id, $source_skin_id );
		}
	}

	/**
	 * Add some extra data to the response in order for skin operations to work on the newly-added skin
	 * After any refresh, the skin will have this data anyway, so we only have to do this here.
	 *
	 * @param $response
	 * @param $skin_term
	 *
	 * @return mixed
	 */
	public function skin_prepare_response( $response, $skin_term ) {
		$term_id = $skin_term->term_id;

		$response->data['tag']     = thrive_skin( $term_id )->get_meta( Thrive_Skin::TAG );
		$response->data['term_id'] = $term_id;

		return $response;
	}

	/**
	 * Get meta value for is skin active
	 *
	 * @param $skin_data
	 *
	 * @return mixed
	 */
	public function get_is_active( $skin_data ) {
		return get_term_meta( $skin_data['id'], Thrive_Skin::SKIN_META_ACTIVE, true );
	}

	/**
	 * Make a skin active
	 *
	 * @param $meta_value
	 * @param $skin
	 * @param $meta_key
	 */
	public function update_is_active( $meta_value, $skin, $meta_key ) {
		if ( (int) $meta_value === 1 ) {

			Thrive_Skin_Taxonomy::set_skin_active( $skin->term_id );

			$this->after_skin_activation_update( $skin->term_id );
		}
	}

	/**
	 * @param $skin_data
	 *
	 * @return mixed
	 */
	public function get_is_wizard_skipped( $skin_data ) {
		return thrive_skin( $skin_data['id'] )->get_meta( Thrive_Skin::IS_WIZARD_SKIPPED );
	}

	/**
	 * @param $meta_value
	 * @param $skin
	 * @param $meta_key
	 */
	public function update_is_wizard_skipped( $meta_value, $skin, $meta_key ) {
		thrive_skin( $skin->term_id )->set_meta( Thrive_Skin::IS_WIZARD_SKIPPED, empty( $meta_value ) ? 0 : 1 );
	}

	/**
	 * We need to make sure that the instance is the one with the active skin before we generate the css file
	 *
	 * @param $skin_id
	 */
	public function after_skin_activation_update( $skin_id ) {
		thrive_skin( $skin_id )->generate_style_file();
	}

	/**
	 * Export skin
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function export( $request ) {

		$name    = sanitize_file_name( $request->get_param( 'name' ) );
		$skin_id = $request->get_param( 'skin_id' );

		try {
			$transfer    = new Thrive_Transfer_Export( $name );
			$export_info = $transfer->export( 'skin', $skin_id );
			$response    = $export_info['url'];
		} catch ( Exception $ex ) {
			$response = $ex->getMessage();
		}

		return new WP_REST_Response( $response, 200 );
	}

	/**
	 * Import skin
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function import( $request ) {
		$archive_file = $this->get_archive_file( $request );

		try {
			$import   = new Thrive_Transfer_Import( $archive_file );
			$skin     = $import->import( 'skin' );
			$response = new WP_REST_Response( $skin, 200 );
		} catch ( Exception $e ) {
			$response = new WP_Error( 'import_error', $e->getMessage(), [ 'status' => 412 ] );
		}

		return $response;
	}

	/**
	 * Get all cloud skins
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response
	 */
	public function fetch_cloud_skins( $request ) {
		$scope = $request->get_param( 'scope' );

		if ( ! empty( $request->get_param( 'force' ) ) ) {
			add_filter( 'thrive_theme_bypass_cloud_transient', '__return_true' );
		}

		if ( empty( $scope ) ) {
			$scope = 'ttb';
		}

		$skins = Thrive_Skin_Taxonomy::get_cloud_skins( $scope );

		return new WP_REST_Response( $skins, is_array( $skins ) ? 200 : 404 );
	}

	/**
	 * Get the file path from where the skin will be imported
	 *
	 * @param $request WP_REST_Request
	 *
	 * @return false|string
	 */
	public function get_archive_file( $request ) {
		$attachment_id = $request->get_param( 'attachment_id' );

		if ( isset( $attachment_id ) ) {
			$archive_file = get_attached_file( $attachment_id );
		} else {
			$archive_file = $request->get_param( 'archive_path' );
		}

		return $archive_file;
	}

	/**
	 * Resets the skin palette to the original
	 *
	 * Called from the UI
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response
	 */
	public function reset_palette( $request ) {
		$active_id = (int) $request->get_param( 'active_id' );
		$skin_id   = (int) $request->get_param( 'id' );

		$thrive_skin    = thrive_skin( $skin_id );
		$skin_palettes  = $thrive_skin->get_palettes();
		$skin_variables = $thrive_skin->get_variables();

		$master_var     = array_filter( $skin_palettes['original'][ $active_id ]['colors'], static function ( $ar ) {
			return isset( $ar['hsl'] ) && ! empty( $ar['hsl'] );
		} );
		$master_var     = reset( $master_var );
		$active_palette = $skin_palettes['active_id'];
		$master_index   = array_search( $master_var['id'], array_column( $skin_palettes['original'][ $active_id ]['colors'], 'id' ) );
		$original_color = $skin_palettes['original'][ $active_palette ]['colors'][ $master_index ]['color'];

		$skin_palettes['modified'][ $active_palette ]['colors'][ $master_index ]['color'] = $original_color;
		$skin_variables['colors'][ $master_index ]['color']                               = $original_color;

		$skin_palettes['modified'] = $skin_palettes['original'];

		$thrive_skin->update_variables( $skin_variables );
		$thrive_skin->update_palettes( $skin_palettes );
		thrive_palettes()->update_master_hsl( $master_var['hsl'] );

		return new WP_REST_Response( [ 'success' => 1 ], 200 );
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
		$master_id   = (int) $request->get_param( 'master_id' );
		$version     = (int) $request->get_param( 'version' );

		if ( $previous_id === $active_id ) {
			//We do nothing here
			return new WP_REST_Response( [ 'success' => 1 ], 200 );
		}

		$thrive_skin = thrive_skin( $skin_id );

		if ( $version === 2 ) {

			$config              = $thrive_skin->get_palettes();
			$config['active_id'] = $active_id;
			$thrive_skin->update_palettes( $config, $version );

			thrive_palettes()->update_master_hsl( $config['palettes'][ $active_id ]['modified_hsl'] );
		} else {
			$skin_palettes  = $thrive_skin->get_palettes();
			$skin_variables = $thrive_skin->get_variables();

			foreach ( $skin_palettes['modified'][ $active_id ]['colors'] as $color ) {
				$color_index = array_search( $color['id'], array_column( $skin_variables['colors'], 'id' ) );
				if ( $color_index !== false && ! empty( $color['hsla_code'] ) ) {
					$skin_variables['colors'][ $color_index ]['hsla_code'] = $color['hsla_code'];
					$skin_variables['colors'][ $color_index ]['hsla_vars'] = $color['hsla_vars'];
					$skin_variables['colors'][ $color_index ]['color']     = $color['color'];
				}
			}

			$master_index = array_search( $master_id, array_column( $skin_palettes['modified'][ $active_id ]['colors'], 'id' ) );
			thrive_palettes()->update_master_hsl( $skin_palettes['modified'][ $active_id ]['colors'][ $master_index ]['hsl'] );

			$skin_palettes['active_id'] = $active_id;

			$thrive_skin->update_variables( $skin_variables );
			$thrive_skin->update_palettes( $skin_palettes );
		}

		return new WP_REST_Response( [ 'success' => 1 ], 200 );
	}

	/**
	 * Used to update the skin variables
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response|WP_Error
	 * @deprecated Should be removed after all users update the theme to palettes 2.0
	 */
	public function skin_variables( $request ) {
		$skin_id     = (int) $request->get_param( 'skin_id' );
		$color       = (string) $request->get_param( 'color' );
		$id          = (int) $request->get_param( 'id' );
		$hsl         = [
			'h' => $request->get_param( 'h' ),
			's' => $request->get_param( 's' ),
			'l' => $request->get_param( 'l' ),
		];
		$thrive_skin = thrive_skin( $skin_id );

		if ( ! empty( array_filter( $hsl ) ) ) {
			thrive_palettes()->update_master_hsl( $hsl );
		}
		$variables = $thrive_skin->get_variables();

		$index = array_search( $id, array_column( $variables['colors'], 'id' ) );

		if ( $index !== false ) {
			$variables['colors'][ $index ]['color'] = $color;
			$variables['colors'][ $index ]['hsl']   = $hsl;

			$thrive_skin->update_variables( $variables );
		}

		//Update also the skin modified palettes
		$skin_palettes = $thrive_skin->get_palettes();

		$active_palette = $skin_palettes['active_id'];
		$master_index   = array_search( $id, array_column( $skin_palettes['modified'][ $active_palette ]['colors'], 'id' ) );

		if ( $master_index !== false ) {
			$skin_palettes['modified'][ $active_palette ]['colors'][ $master_index ]['color'] = $color;
			$skin_palettes['modified'][ $active_palette ]['colors'][ $master_index ]['hsl']   = $hsl;

			$thrive_skin->update_palettes( $skin_palettes );
		}

		return new WP_REST_Response( [ 'success' => 1 ], 200 );
	}

	/**
	 * Download a skin archive from the cloud
	 *
	 * @param $request WP_REST_Request
	 *
	 * @return WP_REST_Response
	 */
	public function cloud_import( $request ) {
		$skin_id = $request->get_param( 'skin_id' );

		/* First download the skin from the cloud */
		try {
			$archive_file = Thrive_Theme_Cloud_Api_Factory::build( 'skins' )->download_item( $skin_id, $request );
		} catch ( Exception $e ) {
			$archive_file = $e;
		}

		if ( $archive_file instanceof Exception ) {
			$response = $archive_file->getMessage();
		} else {
			/* If everything it's ok with the download go ahead and import the skin */
			try {
				$import   = new Thrive_Transfer_Import( $archive_file );
				$response = $import->import( 'skin' );

				if ( (int) Thrive_Wizard::is_fresh_install() === 1 && ! empty( $response->term_id ) ) {
					Thrive_Skin_Taxonomy::set_skin_active( $response->term_id );

					$this->after_skin_activation_update( $response->term_id );

					$response->is_active = 1;

					if (
						class_exists( 'TPM_Product_Theme_Builder', false ) &&
						method_exists( 'TPM_Product_Theme_Builder', 'set_fresh_install_flag' )
					) {
						TPM_Product_Theme_Builder::set_fresh_install_flag( 0 );
					}

					/**
					 * On fresh install, if a new theme is activated, other than ShapeShift,
					 * we need to reset the theme palettes color so that the new user will see the new theme color as the main color
					 */
					$palettes_config = thrive_skin()->get_palettes();
					$active_id       = (int) $palettes_config['active_id'];
					thrive_palettes()->update_master_hsl( $palettes_config['palettes'][ $active_id ]['original_hsl'] );
				}
			} catch ( Exception $e ) {
				$response = $e->getMessage();
			}
		}

		return new WP_REST_Response( $response, 200 );
	}
}
