<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-image-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

/**
 * Class TIE_Ajax_Controller
 *
 * Ajax controller to handle admin ajax requests
 * Specially built for backbone models
 */
class TIE_Ajax_Controller {

	/**
	 * @var TIE_Ajax_Controller $instance
	 */
	protected static $instance;

	/**
	 * TIE_Ajax_Controller constructor.
	 * Protected constructor because we want to use it as singleton
	 */
	protected function __construct() {
	}

	/**
	 * Gets the SingleTone's instance
	 *
	 * @return TIE_Ajax_Controller
	 */
	public static function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self;
		}

		return self::$instance;
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
		wp_send_json_error( [ 'message' => $message ] );
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
		if ( isset( $_POST[ $key ] ) ) {
			$value = $_POST[ $key ]; //phpcs:ignore
		} else {
			$value = isset( $_REQUEST[ $key ] ) ? $_REQUEST[ $key ] : $default; //phpcs:ignore
		}

		return map_deep( $value, 'sanitize_text_field' );
	}

	/**
	 * Entry-point for each ajax request
	 * This should dispatch the request to the appropriate method based on the "route" parameter
	 *
	 * @return array|object
	 */
	public function handle() {

		$route = $this->param( 'route' );

		$route    = preg_replace( '#([^a-zA-Z0-9-])#', '', $route );
		$function = $route . '_action';

		if ( ! method_exists( $this, $function ) ) {
			$this->error( sprintf( __( 'Method %s not implemented', 'thrive-image-editor' ), $function ) );
		}

		$method = empty( $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ) ? 'GET' : sanitize_text_field( $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] );
		$model  = json_decode( file_get_contents( 'php://input' ), true );

		return call_user_func( [ $this, $function ], $method, $model );
	}

	protected function image_action( $method, $model ) {

		switch ( $method ) {
			case 'POST':
			case 'PUT':
			case 'PATCH':
				if ( ! ( $id = tie_save_image( $model ) ) ) {
					$this->error( __( 'Image Post could not be saved', 'thrive-image-editor' ) );
				}

				if ( isset( $model['template'] ) ) {
					tie()->template_manager()->set_template( $id, $model['template'] );
				}

				return tie_get_image( $id );
				break;
			case 'DELETE':
				$id = $this->param( 'ID', 0 );

				if ( empty( $id ) ) {
					$this->error( __( 'Invalid parameter', 'thrive-image-editor' ) );
				}

				$image       = new TIE_Image( $id );
				$image_url   = $image->get_image_url();
				$image_url   = substr( $image_url, 0, strpos( $image_url, '?' ) );
				$default_url = tqb()->plugin_url( 'tcb-bridge/assets/images/share-badge-default.png' );

				do_action( 'tqb_update_social_share_badge_url', $image->get_post_parent_id(), $default_url, $image_url );

				if ( ! ( $deleted = tie_delete_image( $id ) ) ) {
					$this->error( __( 'Image Post could not be deleted', 'thrive-image-editor' ) );
				}

				return $deleted;
				break;
		}

		$this->error( __( 'Bad request', 'thrive-image-editor' ) );
	}
}
