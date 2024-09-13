<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

use TCB\ConditionalDisplay\PostTypes\Conditional_Display_Group;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Thrive_Templates_Rest
 */
class Thrive_Templates_Rest {

	public static $namespace = TTB_REST_NAMESPACE;

	public static $route = '/templates';

	public static function register_routes() {
		register_rest_route( static::$namespace, static::$route, [
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ __CLASS__, 'create' ],
				'permission_callback' => [ __CLASS__, 'route_permission' ],
			],
		] );

		register_rest_route( static::$namespace, static::$route . '/cloud', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ __CLASS__, 'get_from_cloud' ],
				'permission_callback' => [ __CLASS__, 'route_permission' ],
			],
		] );

		register_rest_route( static::$namespace, static::$route . '/(?P<id>[\d]+)', [
			[
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => [ __CLASS__, 'update' ],
				'permission_callback' => [ __CLASS__, 'route_permission' ],
			],
			[
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => [ __CLASS__, 'delete' ],
				'permission_callback' => [ __CLASS__, 'route_permission' ],
			],
		] );

		register_rest_route( static::$namespace, static::$route . '/(?P<id>[\d]+)' . '/preview', [
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ __CLASS__, 'preview' ],
				'permission_callback' => [ __CLASS__, 'route_permission' ],
			],
		] );

		register_rest_route( static::$namespace, static::$route . '/suggest-variable', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ __CLASS__, 'suggest_variable_item' ],
				'permission_callback' => [ __CLASS__, 'route_permission' ],
				'args'                => [
					'secondary_template' => [
						'required' => true,
						'type'     => 'string',
					],
					'term'               => [
						'type' => 'string',
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
	public static function route_permission() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Create one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public static function create( $request ) {

		$data = static::prepare_template_for_insert( $request );

		$id = wp_insert_post( $data );

		/* Assign the template to the current skin */
		$template = new Thrive_Template( $id );
		$template->assign_to_skin();

		/* If we have no inherit from or it's default => we build the template from scratch */
		$inherit_from = $request->get_param( 'inherit_from' );
		if ( ! empty( $inherit_from ) && 'default' !== $inherit_from ) {
			if ( is_numeric( $inherit_from ) ) {
				$template->copy_data_from( $inherit_from );
			} else {
				Thrive_Theme_Cloud_Api_Factory::build( 'templates' )->download_item( $inherit_from, '', [ 'update' => $id ] );

				$meta_input = $request->get_param( 'meta_input' );
				/* after we import a template from the cloud we want to override the meta input that we send and the layout which is the default one */
				$meta_input['layout'] = thrive_skin()->get_default_layout();

				/* make sure that the template has the default wizard header & footer, if they are set */
				$template->assign_default_hf_from_wizard();

				wp_update_post( [
					'ID'         => $id,
					'meta_input' => $meta_input,
				] );
			}
		} else {
			$template->update( [
				'style' => Thrive_Theme_Default_Data::template_default_styles( $template ),
			] );
		}

		/* Set the template as default if there is no default one of its type */
		$similar_templates = $template->get_similar_templates( true );
		if ( empty( $similar_templates ) ) {
			$template->meta_default        = 1;
			$data['meta_input']['default'] = 1;

			/* if we create a new default template, we need to regenerate the style file */
			thrive_skin()->generate_style_file();
		}

		$data['ID']             = $id;
		$data['edit_url']       = $template->edit_url();
		$data['preview_url']    = $template->preview_url();
		$data['layout']         = get_the_title( $template->get_layout() );
		$data['thumbnail']      = $template->thumbnail();
		$data['variable_label'] = $template->get_variable_label();

		return new WP_REST_Response( $data, 200 );
	}

	/**
	 * Create one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return mixed
	 */
	public static function get_from_cloud( $request ) {

		$is_singular = ! empty( $request->get_param( 'is_singular' ) );

		try {
			$params = [
				'filters' => [
					'skin_tag' => thrive_skin()->get_tag(),
					'singular' => $is_singular ? '1' : '0',
				],
			];

			$cloud_templates = Thrive_Theme_Cloud_Api_Factory::build( 'templates' )->get_items( $params );

			$cloud_templates = array_map( static function ( $template ) {
				return [
					'id'        => $template['id'],
					'type'      => 'cloud',
					'title'     => $template['post_title'],
					'primary'   => $template['primary'],
					'secondary' => $template['secondary'],
					'thumb'     => $template['thumb'],
				];
			}, array_values( $cloud_templates ) );
		} catch ( Exception $e ) {
			return new WP_Error( 'tcb_api_error', $e->getMessage() );
		}

		return new WP_REST_Response( $cloud_templates, 200 );
	}

	/**
	 * Prepare template before wp_insert_post
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return array
	 */
	private static function prepare_template_for_insert( $request ) {

		$template = Thrive_Template::default_values(
			[
				'post_title' => $request->get_param( 'post_title' ),
				'meta_input' => $request->get_param( 'meta_input' ),
			]
		);

		$template['meta_input']['layout'] = thrive_skin()->get_default_layout();
		$template['meta_input']['tag']    = uniqid( '', true );

		$format = $template['meta_input']['format'];
		if ( ! empty( $format ) && ( 'video' === $format || 'audio' === $format ) ) {
			$template['meta_input']['sections']['content'] = [
				'id'      => 0,
				'content' => Thrive_Utils::return_part( '/inc/templates/content/content-' . $format . '.php', [], false ),
			];
		}

		return $template;
	}

	/**
	 * Manage update requests
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public static function update( $request ) {

		$action = $request->get_param( 'action' );

		$display_groups = $request->get_param( 'conditional-display-groups' );

		if ( ! empty( $display_groups ) ) {
			Conditional_Display_Group::save_groups( $display_groups );
		}

		if ( empty( $action ) || ! method_exists( __CLASS__, $action ) ) {
			$response = new WP_REST_Response( __( 'No action found!' ), 404 );
		} else {
			$response = static::$action( $request );
		}

		return $response;
	}

	/**
	 * General update function
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response
	 */
	public static function update_fields( $request ) {
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

	/**
	 * Create one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response
	 */
	public static function update_template( $request ) {

		$id   = $request->get_param( 'id' );
		$data = $request->get_param( 'template' );

		$template = new Thrive_Template( $id );

		if ( is_string( $data ) ) {
			$data = json_decode( $data, true );
		}

		$template->update( $data );

		if ( method_exists( '\TCB\Lightspeed\Main', 'handle_optimize_saves' ) ) {
			\TCB\Lightspeed\Main::handle_optimize_saves( $id, $data );
		}

		return new WP_REST_Response( $id, 200 );
	}

	/**
	 * Make a template default
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public static function make_default( $request ) {
		$id = $request->get_param( 'id' );

		$template = new Thrive_Template( $id );
		$template->make_default();

		return new WP_REST_Response( $id, 200 );
	}

	/**
	 * Delete template
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response
	 */
	public static function delete( $request ) {

		$id = $request->get_param( 'id' );

		/* better to trash it "just in case" */
		wp_trash_post( $id );

		return new WP_REST_Response( $id, 200 );
	}

	/**
	 * Reset template = download the initial template from the cloud or take the default structure + basic css
	 *
	 * @param WP_REST_Request $request Full data about the request
	 *
	 * @return WP_REST_Response
	 */
	public static function reset_template( $request ) {

		$id       = $request->get_param( 'id' );
		$template = new Thrive_Template( $id );
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
				'success' => true, // this must mean the tag does not exist in the cloud ... I guess it's safe to return a success message. Either way this is ignored in TTB dashboard, it's just displayed when editing with TAr
				'message' => __( 'There was an error during the download process but the template is back to the blank theme form', 'thrive-theme' ),
				'error'   => $e->getMessage(),
			];
		}

		if ( $template->is_default() ) {
			thrive_skin()->generate_style_file();
		}

		return new WP_REST_Response( $data, 200 );
	}

	/**
	 * Save and return a preview for the template.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public static function preview( $request ) {

		if ( ! isset( $_FILES['img_data'] ) ) {
			return new WP_Error( 'template_preview_error', __( 'Wrong template preview data', 'thrive-theme' ) );
		}

		$id = $request->get_param( 'id' );

		try {
			$preview_data = thrive_template( $id )->create_preview();

			$response = new WP_REST_Response( $preview_data, 200 );
		} catch ( Exception $ex ) {
			$response = new WP_Error( 'template_preview_error', $ex->getMessage() );
		}

		return $response;
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public static function suggest_variable_item( $request ) {
		$term               = $request->get_param( 'term' );
		$secondary_template = $request->get_param( 'secondary_template' );
		$results            = [];

		switch ( true ) {
			case THRIVE_AUTHOR_ARCHIVE_TEMPLATE === $secondary_template:
				$search_args = [
					'role__not_in' => [ 'subscriber' ],
					'number'       => 10,
					'search'       => "*$term*",
				];
				foreach ( get_users( $search_args ) as $wp_user ) {
					$results [] = [
						'id'   => $wp_user->ID,
						'text' => $wp_user->display_name,
					];
				}
				break;
			case taxonomy_exists( $secondary_template ):
				$search_args = [
					'taxonomy'   => $secondary_template,
					'hide_empty' => false,
					'name__like' => $term,
				];
				foreach ( get_terms( $search_args ) as $wp_term ) {
					$results[] = [
						'id'   => $wp_term->term_id,
						'text' => $wp_term->name,
					];
				}
				break;
			default:
				break;
		}

		return rest_ensure_response( $results );
	}
}
