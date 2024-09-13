<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

/**
 * Class TQB_Blocks
 */
class TQB_Blocks {
	const NAME = 'quiz-block';

	public static function init() {
		if ( self::can_use_blocks() ) {
			TQB_Blocks::hooks();
			TQB_Blocks::register_block();
		}
	}

	public static function can_use_blocks() {
		return function_exists( 'register_block_type' );
	}


	public static function hooks() {
		global $wp_version;
		add_filter( version_compare( $wp_version, '5.7.9', '>' ) ? 'block_categories_all' : 'block_categories', array( __CLASS__, 'register_block_category' ), 10, 2 );

		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );

		add_action( 'template_redirect', array( __CLASS__, 'template_redirect' ), 0 );
	}

	public static function enqueue_scripts( $hook ) {
		if ( tve_should_load_blocks() ) {
			wp_localize_script( 'tqb-block', 'TQB_Data',
				array(
					'dashboard_url' => admin_url( 'admin.php?page=tqb_admin_dashboard' ),
					'preview_url'   => add_query_arg( array( 'tqb-block-preview' => 'true' ), site_url() ),
					'edit_url'      => admin_url( 'admin.php?page=tqb_admin_dashboard#dashboard/quiz/' ),
					'block_preview' => tqb()->plugin_url( '/blocks/img/block-preview.png' ),
				)
			);
			tqb_enqueue_style( 'tqb-block-style', tqb()->plugin_url( '/blocks/css/styles.css' ) );
		}
	}

	public static function template_redirect() {
		if ( isset( $_REQUEST['tqb-block-preview'] ) ) {
			include tqb()->plugin_path( 'blocks/block-preview.php' );
			exit;
		}
	}

	public static function register_block() {

		$asset_file = include tqb()->plugin_path( 'blocks/build/index.asset.php' );

		// Register our block script with WordPress
		wp_register_script( 'tqb-block', tqb()->plugin_url( 'blocks/build/index.js' ), $asset_file['dependencies'], $asset_file['version'], false );

		register_block_type(
			'thrive/' . self::NAME,
			array(
				'render_callback' => 'TQB_Blocks::render_block',
				'editor_script'   => 'tqb-block',
				'editor_style'    => 'tqb-block',
			)
		);
	}

	public static function render_block( $attributes ) {
		if ( isset( $attributes['selectedBlock'] ) && ! is_admin() ) {

			$data = array(
				'quiz_id' => $attributes['selectedBlock'],
			);

			return TQB_Shortcodes::render_quiz_shortcode( $data );
		}

		return '';
	}

	public static function register_block_category( $categories, $post ) {
		$category_slugs = wp_list_pluck( $categories, 'slug' );

		return in_array( 'thrive', $category_slugs, true ) ? $categories : array_merge(
			array(
				array(
					'slug'  => 'thrive',
					'title' => __( 'Thrive Library', 'thrive-quiz-builder' ),
					'icon'  => 'wordpress',
				),
			),
			$categories
		);
	}
}
