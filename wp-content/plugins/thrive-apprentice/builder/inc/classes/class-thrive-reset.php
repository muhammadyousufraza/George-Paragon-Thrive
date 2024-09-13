<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Thrive_Reset
 */
class Thrive_Reset {

	/**
	 * Add admin page for resetting settings and set the ajax action for this
	 */
	public static function init() {
		add_submenu_page( '', null, null, 'manage_options', 'ttb-reset', [ __CLASS__, 'menu_page' ] );

		add_action( 'wp_ajax_ttb_skin_reset', [ __CLASS__, 'skin_reset' ] );
		add_action( 'wp_ajax_ttb_factory_reset', [ __CLASS__, 'factory_reset' ] );

	}

	/**
	 * Admin menu page for the reset
	 */
	public static function menu_page() {
		include THEME_PATH . '/inc/templates/admin/reset-page.php';
	}

	/**
	 * Remove everything
	 */
	public static function factory_reset() {
		if ( Thrive_Theme_Product::has_access() ) {
			foreach ( Thrive_Skin_Taxonomy::get_all( 'ids', false ) as $skin_id ) {
				wp_delete_term( $skin_id, SKIN_TAXONOMY );
			}

			static::remove_all_section();

			static::remove_headers_footers();

			/* reset branding settings */
			Thrive_Branding::reset();

			/* reset site settings */
			static::reset_options();

			/* remove all demo content */
			Thrive_Demo_Content::clean();

			Thrive_Theme_Default_Data::create_skin();

			die( 'Everything has been removed!' );
		}

		die( 'No access!' );
	}

	/**
	 * Reset current skin
	 */
	public static function skin_reset() {

		if ( Thrive_Theme_Product::has_access() ) {
			/* reset skin templates, layout and typographies */
			thrive_skin()->reset();

			/* reset branding settings */
			Thrive_Branding::reset();

			/* reset site settings */
			static::reset_options();

			die( 'The current theme has been reset!' );
		}

		die( 'No access!' );
	}

	/**
	 * Reset site settings
	 */
	private static function reset_options() {
		delete_option( THRIVE_FEATURED_IMAGE_OPTION );
		delete_option( THRIVE_USE_INLINE_CSS );
		delete_option( THRIVE_FAVICON_OPTION );
		delete_option( THRIVE_LOGO_URL_OPTION );
		delete_option( THRIVE_THEME_HOMEPAGE_SET_FROM_WIZARD );
		delete_option( Thrive_Palette::THEME_MASTER_VARIABLES );
		delete_option( Thrive_Palette::THEME_PALETTE_CONFIG );

		/* remove switch content cookie */
		setcookie( THRIVE_THEME_SWITCHED_CONTENT, '', time() - 3600 );

		delete_user_meta( get_current_user_id(), 'ttb_dismissed_tooltips' );
	}

	/**
	 * Get all symbols that are either headers or footers and remove them.
	 */
	private static function remove_headers_footers() {
		$posts = get_posts( [
			'post_type'   => TCB_Symbols_Post_Type::SYMBOL_POST_TYPE,
			'numberposts' => - 1,
			'tax_query'   => [
				[
					'taxonomy' => TCB_Symbols_Taxonomy::SYMBOLS_TAXONOMY,
					'field'    => 'slug',
					'terms'    => [ 'Headers', 'Footers' ],
				],
			],
		] );

		foreach ( $posts as $post ) {
			wp_delete_post( $post->ID, true );
		}
	}

	/**
	 * Remove all sections from all skins
	 */
	private static function remove_all_section() {
		$sections = get_posts( [
			'post_type'      => THRIVE_SECTION,
			'posts_per_page' => - 1,
		] );

		foreach ( (array) $sections as $section ) {
			wp_delete_post( $section->ID, true );
		}
	}
}
