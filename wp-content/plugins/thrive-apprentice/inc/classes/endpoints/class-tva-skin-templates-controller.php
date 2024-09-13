<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

use \TVA\TTB\Skin_Template;
use \TVA\TTB\Main;

/**
 * Class TVA_Skin_Template_Controller
 *
 * @project  : thrive-apprentice
 */
class TVA_Skin_Template_Controller extends TVA_REST_Controller {
	public $base = 'templates';

	public function register_routes() {
		parent::register_routes();

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/cloud', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_from_cloud' ],
				'permission_callback' => [ $this, 'permissions_check' ],
				'args'                => [
					'tva_skin_id'  => [
						'type'     => 'integer',
						'required' => true,
					],
					'bypass_cache' => [
						'required' => false,
						'type'     => 'integer',
						'default'  => 0,
					],
				],
			],
		] );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function get_items( $request ) {
		$skin_id = (int) $request->get_param( 'tva_skin_id' );

		return new WP_REST_Response( Skin_Template::localize_all( $skin_id ), 200 );
	}

	/**
	 * Returns the cloud templates for the skin
	 *
	 * @param $request
	 *
	 * @return WP_REST_Response
	 */
	public function get_from_cloud( $request ) {
		$skin_id      = (int) $request->get_param( 'tva_skin_id' );
		$bypass_cache = (bool) $request->get_param( 'bypass_cache' );

		try {
			$params = [
				'filters' => [
					'skin_tag' => Main::skin( $skin_id )->get_tag(),
					'scope'    => 'tva',
				],
			];

			if ( $bypass_cache ) {
				add_filter( 'thrive_theme_bypass_cloud_transient', '__return_true' );
			}

			$cloud_templates = Thrive_Theme_Cloud_Api_Factory::build( 'templates' )->get_items( $params );

			$cloud_templates = array_map( static function ( $template ) {
				return [
					'id'        => $template['id'],
					'type'      => 'cloud',
					'title'     => $template['post_title'],
					'primary'   => $template['primary'],
					'secondary' => $template['secondary'],
					'format'    => empty( $template['type'] ) ? 'standard' : $template['type'],
					'thumb'     => $template['thumb'],
				];
			}, array_values( $cloud_templates ) );
		} catch ( Exception $e ) {
			return new WP_Error( 'tcb_api_error', $e->getMessage() );
		}

		return new WP_REST_Response( $cloud_templates, 200 );
	}

	/**
	 * Create one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function create_item( $request ) {
		$skin_id = empty( $_REQUEST['tva_skin_id'] ) ? (int) $request->get_param( 'tva_skin_id' ) : (int) $_REQUEST['tva_skin_id'];

		if ( empty( $skin_id ) ) {
			return new WP_Error( 'invalid-skin-id', __( 'Failed read the skin_id from the request', 'thrive-apprentice' ) );
		}

		$post_title = sanitize_text_field( $request->get_param( 'post_title' ) );
		$meta_input = $request->get_param( 'meta_input' );

		$data = $this->prepare_template( $post_title, $meta_input, $skin_id );
		$id   = wp_insert_post( $data );

		/* Assign the template to the current skin */
		$template = new Skin_Template( $id );
		$template->assign_to_skin( $skin_id );

		$inherit_from = $request->get_param( 'inherit_from' );
		if ( ! empty( $inherit_from ) && 'default' !== $inherit_from ) {
			if ( is_numeric( $inherit_from ) ) {
				$template->copy_data_from( $inherit_from );
			} else {
				/**
				 * TODO: replace with the TA CLOUD API FACTORY
				 */
				Thrive_Theme_Cloud_Api_Factory::build( 'templates' )->download_item( $inherit_from, '', [ 'update' => $id ] );

				$meta_input = $request->get_param( 'meta_input' );

				unset( $meta_input['tag'] );

				$meta_input['layout'] = Main::skin( $skin_id )->get_default_layout();

				/* make sure that the template has the default wizard header & footer, if they are set */
				$template->assign_default_hf_from_wizard();

				wp_update_post( [
					'ID'         => $id,
					'meta_input' => $meta_input,
				] );
			}
		} else {
			$template->setup_default_data();
		}

		/* Set the template as default if there is no default one of its type */
		$similar_templates = $template->get_similar_templates( true );
		if ( empty( $similar_templates ) ) {
			$template->meta_default        = 1;
			$data['meta_input']['default'] = 1;

			/* if we create a new default template, we need to regenerate the style file */
			Main::skin( $skin_id )->generate_style_file();
		}

		return new WP_REST_Response( Skin_Template::localize_all( $skin_id ), 200 );
	}

	public function delete_item( $request ) {
		$post_id = $request->get_param( 'id' );

		wp_trash_post( $post_id );

		return new WP_REST_Response( $post_id, 200 );
	}

	public function update_item( $request ) {
		$action = $request->get_param( 'action' );

		if ( empty( $action ) || ! method_exists( $this, $action ) ) {
			$response = new WP_REST_Response( __( 'No action found!' ), 404 );
		} else {
			$response = call_user_func_array( array( $this, $action ), array( $request ) );
		}

		return $response;
	}

	private function update_fields( $request ) {
		$id   = $request->get_param( 'id' );
		$post = $request->get_param( 'fields' );
		$meta = $request->get_param( 'meta' );

		if ( is_array( $meta ) ) {
			foreach ( $meta as $key => $value ) {
				update_post_meta( $id, $key, $value );
			}
		}

		if ( is_array( $post ) ) {
			$post['ID'] = $id;
			wp_update_post( $post );
		}

		return new WP_REST_Response( $id, 200 );
	}

	private function make_default( $request ) {
		$id = $request->get_param( 'id' );

		$template = new Skin_Template( $id );
		$template->make_default();

		return new WP_REST_Response( $id, 200 );
	}

	private function reset_template( $request ) {
		$id       = $request->get_param( 'id' );
		$template = new Skin_Template( $id );
		$tag      = $template->meta( 'tag' );

		/* reset settings before applying new ones */
		$template->reset();

		try {
			/* If the tag is empty ( when the user tries to reset a default theme template, just break here */
			if ( empty( $tag ) ) {
				throw new Exception( 'The tag parameter is empty, the template will be back to the blank theme form' );
			}

			$data = Thrive_Theme_Cloud_Api_Factory::build( 'templates' )->download_item( $tag, '', [ 'update' => $id ] );
		} catch ( Exception $e ) {
			$data = [
				'success' => false,
				'message' => __( 'There was an error during the download process but the template it\'s back to the blank theme form', THEME_DOMAIN ),
				'error'   => $e->getMessage(),
			];
		}

		thrive_skin()->generate_style_file();

		return new WP_REST_Response( $data, 200 );
	}

	private function prepare_template( $post_title, $meta_input, $skin_id ) {
		$template = Skin_Template::default_values( [
			'post_title' => $post_title,
			'meta_input' => $meta_input,
		] );

		$template['meta_input']['layout'] = Main::skin( $skin_id )->get_default_layout();
		$template['meta_input']['tag']    = uniqid( '', true );

		$format = $template['meta_input']['format'];
		if ( ! empty( $format ) && ( 'video' === $format || 'audio' === $format ) ) {
			$template['meta_input']['sections']['content'] = [
				'id'      => 0,
				//TODO: maybe change this to respect the TA video/audio format
				'content' => Thrive_Utils::return_part( '/inc/templates/content/content-' . $format . '.php', [], false ),
			];
		}

		return $template;
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
}
