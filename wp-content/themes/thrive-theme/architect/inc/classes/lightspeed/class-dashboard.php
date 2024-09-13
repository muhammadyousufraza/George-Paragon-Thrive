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
 * Class Dashboard
 *
 * @package TCB\Lightspeed
 */
class Dashboard {

	const MENU_SLUG = 'tve_lightspeed';

	const TITLE = 'Project Lightspeed';

	public static function init() {
		add_action( 'admin_menu', [ __CLASS__, 'admin_menu' ] );

		add_filter( 'tve_dash_filter_features', [ __CLASS__, 'tve_dash_filter_features' ] );

		add_filter( 'tve_dash_features', [ __CLASS__, 'tve_dash_features' ] );

		add_action( 'admin_enqueue_scripts', [ __CLASS__, 'admin_enqueue_scripts' ] );

		if ( ! \tvd_is_during_update() ) {
			add_action( 'admin_print_footer_scripts', [ __CLASS__, 'render_backbone_templates' ] );
		}
	}

	public static function admin_menu() {
		add_submenu_page(
			'',
			static::TITLE,
			static::TITLE,
			'manage_options',
			static::MENU_SLUG,
			static function () {
				include TVE_TCB_ROOT_PATH . '/admin/includes/views/asset-optimization.php';
			}
		);
	}

	/**
	 * Add Card to dashboard
	 *
	 * @param array $features
	 *
	 * @return array
	 */
	public static function tve_dash_filter_features( $features ) {
		$features['lightspeed'] = array(
			'icon'        => 'tvd-lightspeed',
			'title'       => static::TITLE,
			'description' => __( 'Optimize your site assets for speed', 'thrive-cb' ),
			'btn_link'    => add_query_arg( 'page', static::MENU_SLUG, admin_url( 'admin.php' ) ),
			'btn_text'    => __( 'Speed settings', 'thrive-cb' ),
		);

		return $features;
	}

	/**
	 * Enable lightspeed card when architect is active
	 *
	 * @param $features
	 *
	 * @return mixed
	 */
	public static function tve_dash_features( $features ) {
		$features['lightspeed'] = true;

		return $features;
	}

	public static function admin_enqueue_scripts( $screen = '' ) {
		if ( ! empty( $screen ) && $screen === 'admin_page_tve_lightspeed' ) {
			tve_dash_enqueue();

			tve_dash_enqueue_script( 'tcb-admin-lightspeed', tve_editor_url( 'admin/assets/js/lightspeed.min.js' ), [
				'jquery',
				'backbone',
			], TVE_VERSION, true );

			tve_dash_enqueue_style( 'tcb-admin-lightspeed', tve_editor_url( 'admin/assets/css/admin-lightspeed.css' ) );

			$data = [
				'options' => [
					'is_enabled'                     => Main::is_enabled(),
					Fonts::ENABLE_ASYNC_FONTS_LOAD   => Fonts::is_loading_fonts_async(),
					Fonts::ENABLE_FONTS_OPTIMIZATION => Fonts::is_enabled(),
					Fonts::DISABLE_GOOGLE_FONTS      => Fonts::is_blocking_google_fonts(),
					Gutenberg::DISABLE_GUTENBERG     => Gutenberg::is_gutenberg_disabled(),
					Gutenberg::DISABLE_GUTENBERG_LP  => Gutenberg::is_gutenberg_disabled( true ),
					Emoji::DISABLE_EMOJI     => Emoji::is_emoji_disabled()
				],
				'nonce'   => wp_create_nonce( 'wp_rest' ),
				'route'   => get_rest_url( get_current_blog_id(), 'tcb/v1/lightspeed' ),
				/* if a user needs a bigger timeout, he can set the constant in wp config */
				'timeout' => defined( 'LIGHTSPEED_TIMEOUT' ) ? LIGHTSPEED_TIMEOUT : 10,
				't' => array(
					'assets'   => __( 'Asset Optimization', 'thrive-cb' ),
					'fonts'    => __( 'Font Settings', 'thrive-cb' ),
					'advanced' => __( 'Advanced Settings', 'thrive-cb' ),
				)
			];

			if ( \TCB\Integrations\WooCommerce\Main::active() ) {
				$data['options'][ Woocommerce::DISABLE_WOOCOMMERCE ]    = Woocommerce::is_woocommerce_disabled();
				$data['options'][ Woocommerce::DISABLE_WOOCOMMERCE_LP ] = Woocommerce::is_woocommerce_disabled( true );
			}

			wp_localize_script( 'tcb-admin-lightspeed', 'lightspeed_localize', $data );
		}
	}

	public static function render_backbone_templates() {
		$templates = tve_dash_get_backbone_templates( TVE_TCB_ROOT_PATH . 'admin/includes/views/templates/lightspeed', 'lightspeed' );

		tve_dash_output_backbone_templates( $templates, 'lightspeed-' );
	}
}
