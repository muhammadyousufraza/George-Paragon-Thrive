<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\VideoReporting;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Main {
	public static function init() {
		static::includes();
		static::hooks();

		Video::register_post_type();
	}

	public static function includes() {
		require_once __DIR__ . '/class-rest-api.php';
		require_once __DIR__ . '/trait-has-post-type.php';
		require_once __DIR__ . '/class-video.php';
	}

	public static function hooks() {
		add_action( 'tcb_ajax_save_post', [ __CLASS__, 'save_video_data' ] );
		add_action( 'rest_api_init', [ Rest_API::class, 'register_routes' ] );
	}

	public static function save_video_data() {
		if ( ! empty( $_REQUEST['video_reporting_data'][0] ) ) {
			/* at some point when we will be saving more video data batches at the same time, we can iterate on this array instead */
			$video_data = $_REQUEST['video_reporting_data'][0];

			if ( ! empty( $video_data['url'] ) ) {
				/* the URL is used to identify videos that were already added */
				$existing_post_id = Video::get_post_id_by_video_url( $video_data['url'] );

				if ( empty( $existing_post_id ) ) {
					Video::insert_post( $video_data );
				} else {
					Video::get_instance_with_id( $existing_post_id )->update_post( $video_data );
				}
			}
		}
	}
}
