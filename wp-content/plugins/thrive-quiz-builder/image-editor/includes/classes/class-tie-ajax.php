<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-quiz-builder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class TIE_Ajax {

	const AJAX_NONCE_NAME = 'tie_ajax_controller';

	public static function init() {
		self::add_ajax_events();
	}

	public static function add_ajax_events() {

		$ajax_events = array(
			'tie_admin_ajax_controller' => false,
			'tie_save_image_content'    => false,
			'tie_save_image_file'       => false,
		);

		foreach ( $ajax_events as $action => $nopriv ) {
			add_action( 'wp_ajax_' . $action, array( __CLASS__, $action ) );

			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_' . $action, array( __CLASS__, $action ) );
			}
		}
	}

	public static function tie_admin_ajax_controller() {
		if ( empty( $_REQUEST['_nonce'] ) || ! wp_verify_nonce( $_REQUEST['_nonce'], self::AJAX_NONCE_NAME ) ) {
			exit( 0 );
		}
		/**
		 * User needs to have TQB capability to use the image editor
		 */
		if ( ! TQB_Product::has_access() ) {
			exit( 0 );
		}
		$response = TIE_Ajax_Controller::instance()->handle();
		wp_send_json( $response );
	}

	public static function tie_save_image_file() {

		$nonce = ! empty( $_POST['nonce'] ) ? $_POST['nonce'] : null;
		if ( false === wp_verify_nonce( $nonce, 'tie_editor_ajax_nonce' ) ) {
			exit( 0 );
		}

		if ( ! TQB_Product::has_access() ) {
			exit( 0 );
		}

		if ( ! isset( $_FILES['badge'] ) ) {
			wp_send_json_error( array(
				'message' => __( 'Badge content empty', 'thrive-image-editor' ),
			) );
		}

		$image = new TIE_Image( ! empty( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0 );

		$url = $image->save_file( $_FILES['badge'] ); // phpcs:ignore;

		if ( empty( $url ) ) {
			wp_send_json_error( array(
				'message' => __( 'Badge could not be saved', 'thrive-image-editor' ),
			) );
		}

		wp_send_json_success( array(
			'message' => __( 'Badge saved', 'thrive-image-editor' ),
			'url'     => $url,
		) );

	}

	public static function tie_save_image_content() {

		$nonce = ! empty( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : null;
		if ( false === wp_verify_nonce( $nonce, 'tie_editor_ajax_nonce' ) ) {
			exit( 0 );
		}

		if ( ! TQB_Product::has_access() ) {
			exit( 0 );
		}

		$image = new TIE_Image( ! empty( $_REQUEST['post_id'] ) ? absint( $_REQUEST['post_id'] ) : 0 );

		if ( isset( $_REQUEST['html_canvas'] ) ) {
			$image->save_html_canvas( $_REQUEST['html_canvas'] ); //phpcs:ignore

			$img_post = get_post( ! empty( $_REQUEST['post_id'] ) ? absint( $_REQUEST['post_id'] ) : 0 );

			/**
			 * Not needed anymore as now we have the correct share badge url
			 */
			if ( $img_post instanceof WP_Post ) {
				delete_post_meta( $img_post->post_parent, 'tqb_quiz_badge_css' );
			}
		}

		if ( isset( $_REQUEST['content'] ) && $image->save_content( $_REQUEST['content'] ) ) { //phpcs:ignore

			if ( isset( $_REQUEST['image_settings'] ) ) {
				$image->get_settings()->save( $_REQUEST['image_settings'] );
			}

			wp_send_json_success( array(
				'message' => __( 'All changes saved!', 'thrive-image-editor' ),
			) );
		}

		wp_send_json_error( array(
			'message' => __( 'Changes could not be saved!', 'thrive-image-editor' ),
		) );
	}
}

TIE_Ajax::init();
