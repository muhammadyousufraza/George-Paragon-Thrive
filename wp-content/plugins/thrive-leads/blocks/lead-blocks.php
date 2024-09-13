<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package TCB2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

/**
 * Class TVE_Leads_Blocks
 */
class TVE_Leads_Blocks {
	const NAME = 'leads-block';

	public static function init() {
		if ( self::can_use_blocks() ) {
			TVE_Leads_Blocks::hooks();
			TVE_Leads_Blocks::register_block();
		}
	}

	public static function can_use_blocks() {
		return function_exists( 'register_block_type' );
	}


	public static function hooks() {
		global $wp_version;

		add_filter( version_compare( $wp_version, '5.7.9', '>' ) ? 'block_categories_all' : 'block_categories', array(
			__CLASS__,
			'register_block_category'
		), 10, 2 );

		add_filter( "rest_prepare_" . TVE_LEADS_POST_SHORTCODE_TYPE, array(
			__CLASS__,
			'rest_prepare_shortcode'
		), 10, 2 );

		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );

	}

	public static function enqueue_scripts( $hook ) {
		if ( tve_should_load_blocks() ) {
			wp_localize_script( 'tve-leads-block-editor', 'TL_Data',
				array(
					'dashboard_url' => admin_url( 'admin.php?page=thrive_leads_dashboard' ),
					'edit_url'      => admin_url( 'admin.php?page=thrive_leads_dashboard#shortcode/' ),
					'block_preview' => plugins_url( 'thrive-leads/admin/img' ) . '/block-preview.png',
				)
			);
			tve_leads_enqueue_style( 'tl-block-style', TVE_LEADS_URL . '/blocks/css/styles.css' );
		}
	}


	public static function register_block() {

		$asset_file = include TVE_LEADS_PATH . 'blocks/build/index.asset.php';

		// Register our block script with WordPress
		wp_register_script( 'tve-leads-block-editor', TVE_LEADS_URL . 'blocks/build/index.js', $asset_file['dependencies'], $asset_file['version'], false );

		register_block_type(
			'thrive/' . self::NAME,
			array(
				'render_callback' => 'TVE_Leads_Blocks::render_block',
				'editor_script'   => 'tve-leads-block-editor',
				'editor_style'    => 'tve-leads-block-editor',
				'style'           => 'tve-leads-block',
			)
		);
	}

	public static function render_block( $attributes ) {
		/**
		 * Prevent showing the block if there is conversion already set
		 *
		 */
		if ( isset( $attributes['selectedBlock'] ) && ! is_admin() && ( ! tve_leads_check_conversion_cookie( $attributes['selectedBlock'] ) || tve_leads_has_already_subscribed_state( $attributes['selectedBlock'] ) ) ) {

			$data = array(
				'id' => $attributes['selectedBlock'],
			);

			return tve_leads_shortcode_render( $data );
		}

		return '';
	}

	public static function register_block_category( $categories, $post ) {
		$category_slugs = wp_list_pluck( $categories, 'slug' );

		return in_array( 'thrive', $category_slugs, true ) ? $categories : array_merge(
			array(
				array(
					'slug'  => 'thrive',
					'title' => __( 'Thrive Library', 'thrive-leads' ),
					'icon'  => 'wordpress',
				),
			),
			$categories
		);
	}

	public static function rest_prepare_shortcode( $response, $post ) {

		$control_variation = tve_leads_get_form_variations( $post->ID, array(
			'tracking_data' => false,
			'post_status'   => TVE_LEADS_STATUS_PUBLISH,
			'get_control'   => true,
		) );

		if ( ! empty( $control_variation ) && ! empty( $control_variation['key'] ) ) {
			$response->data['edit_url']          = tve_leads_get_editor_url( $post->ID, $control_variation['key'], false );
			$response->data['preview_variation'] = tve_leads_get_preview_url( $post->ID, $control_variation['key'], false );
		}

		return $response;
	}
}
