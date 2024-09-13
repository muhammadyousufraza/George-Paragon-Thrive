<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 3/6/2017
 * Time: 1:58 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class TCB_Admin_Ajax {
	const ACTION = 'tcb_admin_ajax_controller';
	const NONCE  = 'tcb_admin_ajax_request';

	/**
	 * Init the object, during the AJAX request. Adds ajax handlers and verifies nonces
	 */
	public function init() {
		add_action( 'wp_ajax_' . self::ACTION, [ $this, 'handle' ] );
	}

	/**
	 * Sets the request's header with server protocol and status
	 * Sets the request's body with specified $message
	 *
	 * @param string $message the error message.
	 * @param string $status  the error status.
	 */
	protected function error( $message, $status = '404 Not Found' ) {
		header( $_SERVER['SERVER_PROTOCOL'] . ' ' . $status ); //phpcs:ignore
		echo esc_attr( $message );
		wp_die();
	}

	/**
	 * Returns the params from $_POST or $_REQUEST
	 *
	 * @param int  $key     the parameter kew.
	 * @param null $default the default value.
	 *
	 * @return mixed|null|$default
	 */
	protected function param( $key, $default = null ) {
		return isset( $_POST[ $key ] ) ? map_deep( $_POST[ $key ], 'sanitize_text_field' ) : ( isset( $_REQUEST[ $key ] ) ? map_deep( $_REQUEST[ $key ], 'sanitize_text_field' ) : $default );
	}

	/**
	 * Entry-point for each ajax request
	 * This should dispatch the request to the appropriate method based on the "route" parameter
	 *
	 * @return array|object
	 */
	public function handle() {
		if ( ! check_ajax_referer( self::NONCE, '_nonce', false ) ) {
			$this->error( sprintf( __( 'Invalid request.', 'thrive-cb' ) ) );
		}

		$route = $this->param( 'route' );

		$route       = preg_replace( '#([^a-zA-Z0-9-_])#', '', $route );
		$method_name = $route . '_action';

		if ( ! method_exists( $this, $method_name ) ) {
			$this->error( sprintf( __( 'Method %s not implemented', 'thrive-cb' ), $method_name ) );
		}

		wp_send_json( $this->{$method_name}() );
	}

	/**
	 * Returns the templates grouped by category.
	 *
	 * Retrieves the templates and categories
	 *
	 * //todo transform into a rest route
	 * //todo return separately, not grouped, and do the rest in JS ( skip the complex processing logic )
	 *
	 * @return array
	 */
	public function templates_fetch_action() {
		$templates_grouped_by_categories = [];

		$method = empty( $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ) ? 'GET' : sanitize_text_field( $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] );

		switch ( $method ) {
			case 'GET':
				$templates = TCB\UserTemplates\Template::localize();
				$templates = array_map( static function ( $template ) {
					$template['name'] = $template['label'];
					unset( $template['label'] );

					return $template;
				}, $templates );
				$templates = array_reverse( $templates );

				if ( $search = $this->param( 'search' ) ) {
					$templates = tcb_filter_templates( $templates, $search );
				}

				$categories = TCB\UserTemplates\Category::get_all();

				/* todo: this next section has to be refactored (see function comments), for now it has to stay like this in order to avoid having to rewrite the JS */
				$templates_for_category = tcb_admin_get_category_templates( $templates );

				foreach ( $categories as $category ) {
					/* @var \TCB\UserTemplates\Category */
					$category_instance = \TCB\UserTemplates\Category::get_instance_with_id( $category['id'] );

					if ( empty( $category_instance->get_meta( 'type' ) ) ) {
						$templates_grouped_by_categories[] = [
							'id'   => $category['id'],
							'name' => $category['name'],
							'tpl'  => empty( $templates_for_category[ $category['id'] ] ) ? [] : $templates_for_category[ $category['id'] ],
						];
					}
				}

				$templates_grouped_by_categories[] = [
					'id'   => 'uncategorized',
					'name' => __( 'Uncategorized templates', 'thrive-cb' ),
					'tpl'  => empty( $templates_for_category['uncategorized'] ) ? [] : $templates_for_category['uncategorized'],
				];

				$page_template_identifier = \TCB\UserTemplates\Category::PAGE_TEMPLATE_IDENTIFIER;

				$templates_grouped_by_categories[] = [
					'id'   => $page_template_identifier,
					'name' => __( 'Page Templates', 'thrive-cb' ),
					'tpl'  => empty( $templates_for_category[ $page_template_identifier ] ) ? [] : $templates_for_category[ $page_template_identifier ],
				];

				break;
			case 'POST':
			case 'PUT':
			case 'PATCH':
			case 'DELETE':
				$this->error( __( 'Invalid call', 'thrive-cb' ) );
				break;
			default:
				break;
		}

		return $templates_grouped_by_categories;
	}

	/**
	 * Category rename and category delete
	 *
	 * //todo transform into a rest route
	 *
	 * @return array
	 */
	public function template_category_model_action() {
		$model    = json_decode( file_get_contents( 'php://input' ), true );
		$method   = empty( $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ) ? 'GET' : sanitize_text_field( $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] );
		$response = [];

		switch ( $method ) {
			case 'POST':
			case 'PUT':
			case 'PATCH':
				$categories = TCB\UserTemplates\Category::get_all();

				if ( empty( $categories ) ) {
					$this->error( __( 'The template category list is empty!', 'thrive-cb' ) );
					break;
				}
				if ( ! is_numeric( $model['id'] ) || empty( $model['name'] ) ) {
					$this->error( __( 'Invalid parameters', 'thrive-cb' ) );
					break;
				}

				/* @var TCB\UserTemplates\Category $category_instance */
				$category_instance = TCB\UserTemplates\Category::get_instance_with_id( $model['id'] );
				$category_instance->rename( $model['name'] );

				$response = [
					'text' => __( 'The category name was modified!', 'thrive-cb' ),
				];
				break;
			case 'DELETE':
				$id = $this->param( 'id', '' );

				if ( ! is_numeric( $id ) ) {
					$this->error( __( 'Undefined parameter: id', 'thrive-cb' ) );
					break;
				}

				$templates = TCB\UserTemplates\Template::get_all();

				// Move existing templates belonging to the deleted category to uncategorized
				$templates_grouped_by_category = tcb_admin_get_category_templates( $templates );

				if ( ! empty( $templates_grouped_by_category[ $id ] ) ) {
					foreach ( $templates_grouped_by_category[ $id ] as $template ) {
						if ( isset( $template['id_category'] ) && (int) $template['id_category'] === (int) $id ) {

							/* @var \TCB\UserTemplates\Template $template_instance */
							$template_instance = TCB\UserTemplates\Template::get_instance_with_id( $template['id'] );

							if ( empty( $_POST['extra_setting_check'] ) ) {
								$template_instance->update( [ 'id_category' => '' ] );
							} else {
								$template_instance->delete();
							}
						}
					}
				}

				/* @var TCB\UserTemplates\Category $category_instance */
				$category_instance = TCB\UserTemplates\Category::get_instance_with_id( $id );
				$category_instance->delete();

				$response = [
					'text' => __( 'The category was deleted!', 'thrive-cb' ),
				];
				break;
			case 'GET':
				$this->error( __( 'Invalid call', 'thrive-cb' ) );
				break;
			default:
				break;
		}

		return $response;
	}

	/**
	 * Template Category action callback
	 *
	 * Strictly for adding new categories
	 * //todo transform into a rest route
	 *
	 * @return array
	 */
	public function template_category_action() {
		$model    = json_decode( file_get_contents( 'php://input' ), true );
		$method   = empty( $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ) ? 'GET' : sanitize_text_field( $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] );
		$response = [];

		switch ( $method ) {
			case 'POST':
			case 'PUT':
			case 'PATCH':
				if ( empty( $model['category'] ) ) {
					$this->error( __( 'Category parameter could not be found!', 'thrive-cb' ) );
					break;
				}

				/* multiple categories can be saved at the same time....for some reason */
				foreach ( $model['category'] as $category ) {
					TCB\UserTemplates\Category::add( $category );
				}

				$response = [
					'text' => __( 'The category was saved!', 'thrive-cb' ),
				];
				break;
			case 'DELETE':
			case 'GET':
				$this->error( __( 'Invalid call', 'thrive-cb' ) );
				break;
			default:
				break;
		}

		return $response;
	}

	/**
	 * Template update - name and category
	 * Template delete
	 * Template preview
	 *
	 * //todo change to rest routes
	 *
	 * @return array
	 */
	public function template_action() {
		$model  = json_decode( file_get_contents( 'php://input' ), true );
		$method = empty( $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ) ? 'GET' : sanitize_text_field( $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] );

		$response = [];

		switch ( $method ) {
			case 'POST':
			case 'PUT':
			case 'PATCH':
				if ( empty( $model['id'] ) || empty( $model['name'] ) ) {
					$this->error( __( 'Invalid parameters', 'thrive-cb' ) );
					break;
				}

				$data_to_update = [
					'name' => $model['name'],
				];

				if ( isset( $model['id_category'] ) ) {
					$data_to_update['id_category'] = $model['id_category'];
				}

				/* @var \TCB\UserTemplates\Template $template_instance */
				$template_instance = \TCB\UserTemplates\Template::get_instance_with_id( $model['id'] );
				$template_instance->update( $data_to_update );

				$response = [ 'text' => __( 'The template saved!', 'thrive-cb' ) ];
				break;
			case 'DELETE':
				$id = $this->param( 'id', '' );

				/* @var \TCB\UserTemplates\Template $template_instance */
				$template_instance = \TCB\UserTemplates\Template::get_instance_with_id( $id );
				$template_instance->delete();

				$response = [ 'text' => __( 'The template was deleted!', 'thrive-cb' ) ];
				break;
			/* template preview */
			case 'GET':
				$id = $this->param( 'id', '' );

				if ( ! is_numeric( $id ) ) {
					$this->error( __( 'Undefined parameter: id', 'thrive-cb' ) );
					break;
				}

				/* @var \TCB\UserTemplates\Template $template_instance */
				$template_instance = \TCB\UserTemplates\Template::get_instance_with_id( $id );

				$response = $template_instance->get();

				if ( empty( $response['thumb'] ) ) {
					$response['thumb'] = [
						'url' => '',
						'w'   => '',
						'h'   => '',
					];
				}

				if ( empty( $response['thumb']['url'] ) ) {
					$response['thumb']['url'] = TCB_Utils::get_placeholder_url();
				}
				break;
			default:
				break;
		}

		return $response;
	}

	/**
	 * upgrade the post_meta key for a post marking it as "migrated" to TCB2.0
	 * Takes care of 2 things:
	 * appends wordpress content at the end of tcb content, saves that into the TCB content
	 * and
	 * updates the post_content field to a text and images version of all the content
	 */
	public function migrate_post_content_action() {
		$post_id = $this->param( 'post_id' );
		$post    = tcb_post( $post_id );
		$post->migrate();

		return [ 'success' => true ];
	}

	/**
	 * Enables the TCB-only editor for a post
	 */
	public function enable_tcb_action() {
		tcb_post( $this->param( 'post_id' ) )->enable_editor();

		return [ 'success' => true ];
	}

	/**
	 * Disable the TCB-only editor for a post
	 */
	public function disable_tcb_action() {
		tcb_post( $this->param( 'post_id' ) )->disable_editor();

		return [ 'success' => true ];
	}

	/**
	 * Change post status created by Gutenberg so Architect can open it
	 */
	public function change_post_status_gutenberg_action() {

		if ( get_post_status( $this->param( 'post_id' ) ) === 'auto-draft' ) {
			$post = array(
				'ID'          => $this->param( 'post_id' ),
				'post_status' => 'draft',
			);
			wp_update_post( $post );
		}

		return [ 'success' => true ];
	}
}

$tcb_admin_ajax = new TCB_Admin_Ajax();
$tcb_admin_ajax->init();

