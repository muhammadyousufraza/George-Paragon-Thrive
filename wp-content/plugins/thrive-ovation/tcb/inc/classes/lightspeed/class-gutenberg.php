<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\Lightspeed;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

/**
 * Class Gutenberg
 *
 * @package TCB\Lightspeed
 */
class Gutenberg {
	const  DISABLE_GUTENBERG_LP = '_tve_disable_gutenberg_lp';

	const  DISABLE_GUTENBERG = '_tve_disable_gutenberg';

	const HAS_GUTENBERG = '_tve_js_modules_gutenberg';

	public static function get_gutenberg_assets( $module = '', $key = '' ) {
		$data = [
			'gutenberg' => [
				'identifier' => '[class^="wp-block"]',
			],

		];

		if ( ! empty( $key ) ) {
			$data = array_map( static function ( $item ) use ( $key ) {
				return empty( $item[ $key ] ) ? [] : $item[ $key ];
			}, $data );
		}

		return empty( $module ) ? $data : $data[ $module ];
	}

	/**
	 * Checks if gutenberg scripts are disabled on a certain page
	 *
	 * @param false $is_lp
	 *
	 * @return bool
	 */
	public static function is_gutenberg_disabled( $is_lp = false ) {
		return ! empty( get_option( $is_lp ? static::DISABLE_GUTENBERG_LP : static::DISABLE_GUTENBERG, 0 ) );
	}

	public static function needs_gutenberg_assets() {
		$id = get_the_ID();

		if ( ! static::is_gutenberg_disabled( tve_post_is_landing_page( $id ) ) || ! empty( $_GET['force-all-js'] ) || is_editor_page_raw() || empty( get_post_meta( $id, 'tcb2_ready', true ) ) ) {
			return true;
		}

		$has_gutenberg = static::process_gutenberg_meta( $id );

		return ! empty( get_post_meta( $id, static::HAS_GUTENBERG, true ) ) && $has_gutenberg;
	}

	/**
	 * Process the gutenberg meta at the post level
	 *
	 * @param $id
	 *
	 * @return bool
	 */
	public static function process_gutenberg_meta( $id ) {
		$gutenberg_meta = get_post_meta( $id, static::DISABLE_GUTENBERG, true );
		/* for enabled */
		$has_gutenberg = true;

		if ( $gutenberg_meta === '' ) {
			/* for inherit */
			$gutenberg_disabled = static::is_gutenberg_disabled( tve_post_is_landing_page( $id ) );

			$has_gutenberg = ! $gutenberg_disabled;
		} else if ( $gutenberg_meta === '0' ) {
			/* for disabled */
			$has_gutenberg = false;
		}

		return $has_gutenberg;
	}

	/**
	 * f we have gutenberg added on this page we save the optimize modules
	 *
	 * @param $post_id
	 * @param $post_content
	 */
	public static function update_post( $post_id, $post ) {
		/* We need this only if we are editing with WP */
		if ( ! empty( $_POST['action'] ) && $_POST['action'] === 'tcb_editor_ajax' ) {
			return;
		}

		$post_content = $post->post_content;

		$data = $post_content && ( strpos( $post_content, 'wp-block' ) !== false || strpos( $post_content, '/wp:' ) !== false ) ? [ 'gutenberg' ] : [];

		update_post_meta( $post_id, static::HAS_GUTENBERG, $data );
	}
}
