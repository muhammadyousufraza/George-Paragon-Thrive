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
 * Class TGE_Ajax_Controller
 *
 * Ajax controller to handle admin ajax requests
 * Specially built for backbone models
 */
class TGE_Ajax_Controller {

	/**
	 * @var TGE_Ajax_Controller $instance
	 */
	protected static $instance;

	/**
	 * TGE_Ajax_Controller constructor.
	 * Protected constructor because we want to use it as singleton
	 */
	protected function __construct() {
	}

	/**
	 * Gets the SingleTone's instance
	 *
	 * @return TGE_Ajax_Controller
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
	 *
	 * @return null
	 */
	protected function error( $message, $status = '404 Not Found' ) {
		header( $_SERVER['SERVER_PROTOCOL'] . ' ' . $status ); //phpcs:ignore
		wp_send_json_error( array( 'message' => $message ) );

		return null;
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
			$this->error( sprintf( __( 'Method %s not implemented', 'thrive-graph-editor' ), $function ) );
		}

		$method = empty( $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ) ? 'GET' : sanitize_text_field( $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] );
		$model  = json_decode( file_get_contents( 'php://input' ), true );

		return call_user_func( array( $this, $function ), $method, $model );
	}

	protected function settings_action( $method, $model ) {
		switch ( $method ) {
			case 'PUT':
			case 'POST':

				if ( ! empty( $model ) && is_array( $model ) ) {

					foreach ( $model as $setting ) {
						$post_id    = $setting['quiz_id'];
						$meta_key   = 'tge_' . $setting['key'];
						$meta_value = $setting['value'];

						update_post_meta( $post_id, $meta_key, $meta_value );
					}
				}

				return $model;
				break;
		}
	}

	protected function question_action( $method, $model ) {

		switch ( $method ) {
			case 'PUT':
				if ( empty( $model['quiz_id'] ) ) {
					$this->error( __( 'Question actions cannot be performed without quiz_id', 'thrive-graph-editor' ) );
				}
				$question_manager = new TGE_Question_Manager( $model['quiz_id'] );

				$question = $question_manager->save_question( $model );

				if ( $question ) {
					return $question_manager->prepare_question( $question );
				}

				return $this->error( __( 'Question could not be saved' ), 'thrive-graph-editor' );

			case 'DELETE':
				$id               = (int) $this->param( 'id' );
				$question_manager = new TGE_Question_Manager();

				return $question_manager->delete_question( $id );
		}

		$this->error( __( 'No action could be executed on question route', 'thrive-graph-editor' ) );
	}

	protected function connection_action( $method, $model ) {
		switch ( $method ) {
			case 'PUT':
				$link_manager = new TGE_Link_Manager( $model['source'], $model['target'] );

				return $link_manager->connect();
				break;
		}
		$this->error( __( 'No action could be executed on connection route', 'thrive-graph-editor' ) );
	}

	protected function disconnection_action( $method, $model ) {
		switch ( $method ) {
			case 'PUT':
				$link_manager = new TGE_Link_Manager( $model['source'], $model['target'] );

				$saved = $link_manager->disconnect();

				return $saved;
				break;
		}
		$this->error( __( 'No action could be executed on connection route', 'thrive-graph-editor' ) );
	}

	/**
	 * Handle any action related to progress bar
	 *
	 * @param $method
	 * @param $model
	 *
	 * @return bool
	 */
	public function progress_action( $method, $model ) {

		$quiz_style_id = ! empty( $model['quiz_style_id'] ) ? (string) $model['quiz_style_id'] : null;
		$quiz_id       = $model['quiz_id'];

		unset( $model['quiz_id'] );

		$settings = new TQB_Progress_Settings( (int) $quiz_id, $quiz_style_id );
		$settings->set_data( $model )->save();

		$model['quiz_id'] = $quiz_id;

		return $model;
	}

	/**
	 * Handle scroll settings action
	 *
	 * @param string $method
	 * @param array  $model
	 *
	 * @return bool
	 */
	public function scroll_action( $method, $model ) {
		$model   = tve_sanitize_data_recursive( $model );
		$quiz_id = $model['quiz_id'];

		unset( $model['quiz_id'] );

		TQB_Post_meta::update_quiz_scroll_settings_meta( (int) $quiz_id, $model );

		return true;
	}
}
