<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

use Thrive\Theme\Integrations\WooCommerce\Main as Woo;

/**
 * Class Thrive_Theme
 */
class Thrive_Theme {

	/**
	 * @var bool
	 */
	private static $is_active;

	/**
	 * Check if thrive theme is the current active theme
	 *
	 * @return bool
	 */
	public static function is_active() {
		if ( static::$is_active === null ) {
			$current_theme = wp_get_theme();

			static::$is_active = $current_theme->get_template() === THEME_DOMAIN;
		}

		return static::$is_active;
	}

	/**
	 * Thrive_Theme constructor.
	 */
	public function __construct() {
		$this->includes();

		$this->integrations();

		$this->actions();

		$this->filters();
	}

	private function includes() {

		require_once THEME_PATH . '/inc/traits/trait-thrive-singleton.php';

		require_once THEME_PATH . '/inc/traits/trait-thrive-term-meta.php';

		require_once THEME_PATH . '/inc/traits/trait-thrive-post-meta.php';
		require_once THEME_PATH . '/inc/traits/trait-thrive-belongs-to-skin.php';

		require_once THEME_PATH . '/inc/classes/class-thrive-shortcodes.php';

		require_once THEME_PATH . '/inc/classes/utils/class-thrive-utils.php';
		require_once THEME_PATH . '/inc/classes/utils/class-thrive-dom-helper.php';
		require_once THEME_PATH . '/inc/classes/utils/class-thrive-css-helper.php';

		require_once THEME_PATH . '/inc/classes/class-thrive-defaults.php';

		require_once THEME_PATH . '/inc/classes/class-thrive-breadcrumbs.php';

		require_once THEME_PATH . '/inc/classes/class-thrive-views.php';

		require_once THEME_PATH . '/inc/classes/class-thrive-post.php';

		require_once THEME_PATH . '/inc/classes/class-thrive-prev-next.php';

		require_once THEME_PATH . '/inc/classes/class-thrive-category.php';

		require_once THEME_PATH . '/inc/classes/class-thrive-theme-db.php';

		require_once THEME_PATH . '/inc/classes/class-thrive-branding.php';

		Thrive_Branding::init();

		/* also needed inside TPM before the theme is active */
		require_once THEME_PATH . '/inc/classes/transfer/class-thrive-transfer-import.php';
		require_once THEME_PATH . '/inc/classes/transfer/class-thrive-transfer-utils.php';
		require_once THEME_PATH . '/inc/classes/class-thrive-theme-comments.php';

		if ( static::is_active() ) {
			require_once THEME_PATH . '/inc/classes/transfer/class-thrive-transfer-export.php';

			require_once THEME_PATH . '/inc/classes/class-thrive-theme-update.php';

			require_once THEME_PATH . '/inc/classes/class-thrive-reset.php';
		}

		/* files needed for video posts */
		require_once THEME_PATH . '/inc/classes/video-post-format/class-thrive-video-post-format-main.php';
		require_once THEME_PATH . '/inc/classes/video-post-format/class-thrive-video-post-format.php';
		require_once THEME_PATH . '/inc/classes/video-post-format/class-thrive-video-post-custom.php';
		require_once THEME_PATH . '/inc/classes/video-post-format/class-thrive-video-post-youtube.php';
		require_once THEME_PATH . '/inc/classes/video-post-format/class-thrive-video-post-vimeo.php';
		require_once THEME_PATH . '/inc/classes/video-post-format/class-thrive-video-post-wistia.php';
		require_once THEME_PATH . '/inc/classes/video-post-format/class-thrive-video-post-bunnynet.php';

		/* files needed for audio posts */
		require_once THEME_PATH . '/inc/classes/audio-post-format/class-thrive-audio-post-format-main.php';
		require_once THEME_PATH . '/inc/classes/audio-post-format/class-thrive-audio-post-format.php';
		require_once THEME_PATH . '/inc/classes/audio-post-format/class-thrive-audio-post-custom.php';
		require_once THEME_PATH . '/inc/classes/audio-post-format/class-thrive-audio-post-spotify.php';
		require_once THEME_PATH . '/inc/classes/audio-post-format/class-thrive-audio-post-soundcloud.php';

		require_once THEME_PATH . '/inc/classes/image-post-format/class-thrive-image-post-format.php';

		/**
		 * Handle compatibility with 3rd party plugins.
		 */
		require_once THEME_PATH . '/integrations/compatibility.php';

		/* handle backwards compatibility for old functionality */
		require_once THEME_PATH . '/integrations/backwards.php';

		if ( is_dir( THEME_PATH . '/tests' ) ) {
			require_once THEME_PATH . '/tests/inc/classes/class-thrive-theme-tests.php';

			Thrive_Theme_Tests::init();
		}
	}

	private function integrations() {

		if ( static::is_active() ) {
			/* include the AMP integration class */
			require_once THEME_PATH . '/integrations/amp/classes/class-main.php';

			require_once THEME_PATH . '/integrations/conditional-display/class-main.php';

			/* Cache integration */
			require_once THEME_PATH . '/integrations/cache/class-thrive-plugin-contract.php';
			require_once THEME_PATH . '/integrations/cache/class-thrive-fastest-cache.php';
			require_once THEME_PATH . '/integrations/cache/class-thrive-total-cache.php';
			require_once THEME_PATH . '/integrations/cache/class-thrive-wp-rocket.php';
			require_once THEME_PATH . '/integrations/cache/class-thrive-sg-optimizer.php';
			require_once THEME_PATH . '/integrations/cache/class-thrive-plugins-manager.php';

			/* Load Thrive Dashboard and integrate the theme insides */
			require_once THEME_PATH . '/integrations/dashboard/class-thrive-theme-dashboard.php';

			Thrive_Theme_Dashboard::init();
		}

		require_once THEME_PATH . '/integrations/optimole-wp/class-thrive-optimole-wp.php';

		require_once THEME_PATH . '/integrations/typography/class-thrive-typography.php';

		/* Wizard */
		require_once THEME_PATH . '/integrations/wizard/class-thrive-wizard.php';

		/* Landing page integration */
		require_once THEME_PATH . '/integrations/landingpage/class-thrive-landingpage.php';

		require_once THEME_PATH . '/integrations/architect/class-thrive-architect.php';

		Thrive_Architect::init();
	}

	private function actions() {

		add_action( 'init', [ $this, 'init' ], 11 );

		add_action( 'rest_api_init', [ $this, 'rest_api_init' ] );

		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ], 11 );

		add_action( 'wp_head', [ $this, 'wp_head' ] );

		add_action( 'wp', function () {
			/* Add hooks for custom post scripts */
			if ( ! tve_post_is_landing_page() ) {
				tcb_scripts()->hooks();
			}
		} );

		add_action( 'after_switch_theme', [ $this, 'tcb_enable_lightspeed' ] );

		if ( static::is_active() ) {
			add_action( 'after_setup_theme', [ $this, 'theme_setup' ], 11 );

			add_action( 'widgets_init', [ $this, 'widgets_init' ] );

			add_action( 'theme_after_body_open', [ $this, 'theme_after_body_open' ] );

			add_action( 'tcb_landing_head', [ $this, 'print_amp_link_in_landing_page' ] );

			add_action( 'template_redirect', [ $this, 'template_redirect' ] );

			add_action( 'wp_footer', [ $this, 'wp_footer' ] );

			if ( is_admin() ) {

				add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );

				add_action( 'admin_body_class', [ $this, 'admin_body_class' ], PHP_INT_MAX );

				/* actions to show and edit the social URL fields in the profile */
				add_action( 'show_user_profile', [ 'Thrive_Views', 'social_fields_display' ], 10 );
				add_action( 'edit_user_profile', [ 'Thrive_Views', 'social_fields_display' ], 10 );

				/* function called when the URL fields are saved */
				add_action( 'personal_options_update', [ 'Thrive_Utils', 'save_user_fields' ] );
				add_action( 'edit_user_profile_update', [ 'Thrive_Utils', 'save_user_fields' ] );

				add_action( 'admin_footer', [ $this, 'admin_footer' ] );

				add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );

				/* action for saving a post ( from both WP and Architect!! ) */
				add_action( 'save_post', [ $this, 'save_post' ] );

				add_action( 'pre_get_posts', [ $this, 'hide_attachment_from_media_library_dashboard' ] );

				add_action( 'tcb_after_symbol_save', [ $this, 'after_symbol_save' ] );

				add_action( 'admin_action_' . Thrive_Post::CLONE_ACTION, [ $this, 'clone_item' ] );
			}
		}
	}

	private function filters() {

		if ( static::is_active() ) {
			add_filter( 'option_posts_per_page', [ $this, 'posts_per_page' ] );

			add_filter( 'show_admin_bar', [ $this, 'show_admin_bar' ] );

			add_filter( 'tve_dash_features', [ $this, 'enable_dashboard_features' ] );

			add_filter( 'td_include_script_manager', '__return_true' );

			add_filter( 'tcb_post_list_content_default_attr', [ $this, 'post_list_content_default_attr' ], 10, 1 );

			add_filter( 'tve_dash_admin_bar_nodes', [ $this, 'theme_admin_bar_menu' ], 10, 1 );

			add_filter( 'ajax_query_attachments_args', [ $this, 'hide_attachment_from_media_library_lightbox' ] );

			add_filter( 'tcb_edit_post_default_url', [ $this, 'template_dashboard_redirect' ], 10, 2 );

			add_filter( 'tve_intrusive_forms', [ $this, 'intrusive_forms' ], 10, 2 );

			add_filter( 'tve_leads_do_not_show_two_step', [ $this, 'do_not_show_two_step_lighbox' ], 10, 2 );

			add_filter( 'tcb_inline_shortcodes', [ $this, 'inline_shortcodes' ], 11 );

			/* Used to add a clone link in the post / page listing */
			add_filter( 'post_row_actions', [ $this, 'clone_link' ], 10, 2 );
			add_filter( 'page_row_actions', [ $this, 'clone_link' ], 10, 2 );

			/* Allow SVG uploads */
			add_filter( 'upload_mimes', [ $this, 'allow_upload_svg' ] );
		}

		/* these 2 filters make sure users can edit Apprentice design templates even if TA is the only active plugin */
		add_filter( 'tcb_user_has_post_access', [ $this, 'architect_access' ] );
		add_filter( 'tcb_user_has_plugin_edit_cap', [ $this, 'architect_access' ] );

		/* add extra classes for body */
		add_filter( 'body_class', [ $this, 'body_class' ] );

		add_filter( 'tcb_symbol_css_before', [ $this, 'change_symbols_css' ] );

		add_filter( 'tve_allowed_post_type', [ $this, 'tve_allowed_post_type' ], 11, 2 );

		add_filter( 'tcb_event_manager_action_tabs', [ $this, 'tcb_event_manager_action_tabs' ] );
		add_filter( 'tcb_event_action_classes', [ $this, 'tcb_event_action_classes' ] );

		add_filter( 'tcm_allow_comments_editor', [ $this, 'allow_thrive_comments' ] );
		add_filter( 'wp_prepare_themes_for_js', [ $this, 'auto_update' ] );

		/**
		 * Show User Profile element while the TTB is active
		 */
		add_filter( 'tve_user_profile_hidden', '__return_false' );

		add_filter( 'tve_dash_exclude_post_types_from_index', [ $this, 'exclude_post_types_from_index' ] );
	}

	public function init() {
		/**
		 * Require this file here because we need to load it after the file from architect ( TCB_Landing_Page_Transfer.php ) is loaded if this exists
		 */
		require_once THEME_PATH . '/inc/classes/cloud-api/class-thrive-theme-cloud-api-factory.php';

		require_once THEME_PATH . '/inc/classes/class-thrive-featured-image.php';

		require_once THEME_PATH . '/inc/classes/class-thrive-theme-sidebar-toggle-action.php';

		/** If a file called .flag-staging-templates exists, show staging templates */
		if ( file_exists( get_template_directory() . '/.flag-staging-templates' ) && ! defined( 'TCB_CLOUD_API_LOCAL' ) ) {
			define( 'TCB_CLOUD_API_LOCAL', 'https://staging.landingpages.thrivethemes.com/cloud-api/index-api.php' );
		}

		/* WooCommerce integration */
		if ( ! class_exists( 'TCB\Integrations\WooCommerce\Main', false ) ) {
			require_once THEME_PATH . '/architect/inc/woocommerce/classes/class-main.php';
		}
		require_once THEME_PATH . '/integrations/woocommerce/class-main.php';

		//todo remove this in 2-3 releases ( we want to ensure compatibility with old TCB until then )
		if ( ! class_exists( 'Tcb_Scripts', false ) ) {
			require_once THEME_PATH . '/architect/inc/classes/class-tcb-scripts.php';
		}

		Thrive_Skin_Taxonomy::register_thrive_templates_tax();

		Thrive_Breadcrumbs::register_translate_strings();

		if ( static::is_active() ) {
			Thrive_Landingpage::init();

			Woo::init();

			Thrive\Theme\AMP\Main::init();

			if ( class_exists( '\TCB\ConditionalDisplay\Main', false ) ) {
				Thrive\Theme\ConditionalDisplay\Main::init();
			}

			Thrive_Theme_Update::init();

			Thrive_Theme_Default_Data::init();

			Thrive_Demo_Content::init( is_admin() );

			Thrive_Reset::init();

			Thrive_Category::init();
		}
	}

	/**
	 * check if there is a valid activated license for the theme
	 *
	 * @return bool
	 */
	public static function licence_check() {
		return TVE_Dash_Product_LicenseManager::getInstance()->itemActivated( Thrive_Theme_Product::TAG );
	}

	/**
	 * Add specific class to body
	 *
	 * @param $classes
	 *
	 * @return array
	 */
	public function body_class( $classes ) {
		return array_merge( $classes, thrive_template()->body_class( true, 'array' ) );
	}

	public function theme_setup() {
		$domain = 'thrive-theme';
		$locale = apply_filters( 'theme_locale', get_locale(), $domain );

		load_textdomain( $domain, WP_LANG_DIR . '/thrive/' . $domain . '-' . $locale . '.mo' );
		load_theme_textdomain( $domain, get_template_directory() . '/languages' );

		/* Add default posts and comments RSS feed links to head. */
		add_theme_support( 'automatic-feed-links' );

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */
		add_theme_support( 'title-tag' );

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		 */
		add_theme_support( 'post-thumbnails' );

		/* This theme uses wp_nav_menu() in one location. */
		register_nav_menus( [
			'theme-menu' => __( 'Primary', 'thrive-theme' ),
		] );

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support( 'html5', [ 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption' ] );

		/* Add theme support for selective refresh for widgets. */
		add_theme_support( 'customize-selective-refresh-widgets' );

		/*
		 * Enable support for Post Formats.
		 *
		 * See: https://codex.wordpress.org/Post_Formats
		 */
		add_theme_support( 'post-formats', static::post_formats() );

		add_theme_support( 'tve-wc-mini-cart' );

		add_theme_support( 'woocommerce' );

		add_theme_support( 'wc-product-gallery-zoom' );
		add_theme_support( 'wc-product-gallery-lightbox' );
		add_theme_support( 'wc-product-gallery-slider' );

		require_once THEME_PATH . '/integrations/dashboard/class-thrive-theme-product.php';
	}

	/**
	 *  Call a first time activation function for lightspeed
	 */
	public function tcb_enable_lightspeed() {
		if ( method_exists( '\TCB\Lightspeed\Main', 'first_time_enable_lightspeed' ) ) {
			\TCB\Lightspeed\Main::first_time_enable_lightspeed();
		}
	}

	/**
	 * Register frontend rest routes
	 */
	public function rest_api_init() {
		require_once THEME_PATH . '/inc/classes/class-thrive-frontend-rest.php';

		Thrive_Frontend_REST::register_routes();
	}

	public function widgets_init() {
		register_sidebar( [
			'name'          => __( 'Default Widget Area', 'thrive-theme' ),
			'id'            => THRIVE_DEFAULT_SIDEBAR,
			'description'   => __( 'Add widgets here.', 'thrive-theme' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		] );

		$sidebars = get_option( THRIVE_SIDEBARS_OPTION, [] );
		if ( is_array( $sidebars ) ) {
			foreach ( $sidebars as $sidebar ) {
				register_sidebar( [
					'name'          => $sidebar['name'],
					'id'            => 'thrive-theme-sidebar-' . $sidebar['id'],
					'description'   => __( 'Add widgets here.', 'thrive-theme' ),
					'before_widget' => '<section id="%1$s" class="widget %2$s">',
					'after_widget'  => '</section>',
					'before_title'  => '<h2 class="widget-title">',
					'after_title'   => '</h2>',
				] );
			}
		}
	}

	/**
	 * Enqueue front scripts
	 */
	public function enqueue_scripts() {

		$thrive_template = thrive_template();
		$landing_page    = tcb_landing_page( get_the_ID() );
		$lightspeed      = \TCB\Lightspeed\Css::get_instance( $thrive_template->ID );

		/* structure and typography */
		wp_enqueue_style( 'thrive-theme', trailingslashit( THEME_URL ) . 'style.css', [], THEME_VERSION );

		if ( ! is_singular() || ! $landing_page->is_landing_page() ) {
			/* the landing page will load it's own styles - if those are not optimized we will load the theme css */
			$lightspeed->load_optimized_style( 'base' );

			\TCB\Lightspeed\JS::get_instance( $thrive_template->ID )->enqueue_scripts();

			$thrive_template->enqueue_global_scripts();
		}

		$theme_frontend_dependency = [ 'tve_frontend', 'jquery' ];

		if ( is_editor_page_raw() ) {//jquery UI is not needed in frontend.
			$theme_frontend_dependency[] = 'jquery-ui-resizable';
		}

		tve_dash_enqueue_script( 'theme-frontend', THEME_ASSETS_URL . '/frontend.min.js', $theme_frontend_dependency, THEME_VERSION );

		wp_localize_script( 'theme-frontend', 'thrive_front_localize', $this->localize_object( 'front' ) );

		if ( apply_filters( 'thrive_theme_display_css', $thrive_template->is_default() && ( ! is_singular() || ! $landing_page->is_landing_page() ) && ! (
				Thrive_Utils::is_inner_frame() ||
				Thrive_Utils::is_preview() ||
				Thrive_Utils::is_skin_preview() ||
				Thrive_Utils::is_theme_typography()
			) ) ) {
			/* display the css file only on frontend because that's the only place where we display only the default templates */
			$lightspeed->load_optimized_style( 'template', [ $lightspeed->get_style_handle( 'base' ) ] );
		}

		if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}

		if ( is_user_logged_in() ) {
			wp_enqueue_style( 'thrive-theme-logged-in-style', THEME_ASSETS_URL . '/logged-in.css', false, THEME_VERSION );
		}

		if ( static::is_active() && Thrive_Wizard::is_frontend() ) {
			tve_dash_enqueue_script( 'ttb-wizard-preview', THEME_ASSETS_URL . '/wizard.min.js', [ 'theme-frontend' ] );
			wp_enqueue_style( 'ttb-wizard', THEME_ASSETS_URL . '/wizard-ui.css' );
		}
	}

	/**
	 * Enqueue admin scripts
	 */
	public function admin_enqueue_scripts() {
		if ( Thrive_Utils::is_thrive_page( THRIVE_THEME_DASH_PAGE ) ) {
			wp_enqueue_media();

			tve_dash_enqueue_licensing_assets();

			wp_enqueue_style( 'thrive-admin-style', THEME_ASSETS_URL . '/admin-styles.css', false, THEME_VERSION );

			wp_enqueue_style( 'ttb-wizard', THEME_ASSETS_URL . '/wizard-ui.css' );
			/**
			 * Output the skin variables also inside dashboard (main frame)
			 */
			wp_add_inline_style( 'thrive-admin-style', ':root{' . thrive_skin()->get_variables_for_css() . '}' );
			wp_enqueue_script( 'jquery-masonry', [ 'jquery' ] );
			tve_dash_enqueue_script( 'thrive-admin-script', THEME_ASSETS_URL . '/admin.min.js', [
				'jquery',
				'backbone',
				'underscore',
				'jquery-ui-autocomplete',
			], THEME_VERSION, true );

			tve_dash_enqueue_script( 'thrive-admin-libs', THEME_ASSETS_URL . '/admin-libs.min.js', [
				'jquery',
				'backbone',
				'underscore',
				'jquery-ui-tooltip',
			] );
			wp_enqueue_script( 'tar-lazyload', tve_editor_url() . '/editor/js/libs/lazyload.min.js', [ 'thrive-admin-libs' ] );

			wp_localize_script( 'thrive-admin-script', 'ttd_admin_localize', $this->localize_object( 'admin' ) );

			tve_dash_enqueue_script( 'thrive-select2', TVE_DASH_URL . '/js/dist/select2.min.js', [ 'jquery' ] );
			tve_dash_enqueue_style( 'thrive-select2-style', TVE_DASH_URL . '/css/select2.css' );

			tve_dash_enqueue_script( 'tve-modal', TVE_DASH_URL . '/js/dist/modal' . TCB_Utils::get_js_suffix(), [
				'jquery',
				'backbone',
			], THEME_VERSION, true );

			tve_dash_enqueue_style( 'tve-dash-modal-css', TVE_DASH_URL . '/css/modal.css' );
		}

		if ( Thrive_Utils::is_thrive_page( 'widgets' ) ) {
			tve_dash_enqueue_script( 'thrive-widgets', THEME_ASSETS_URL . '/widgets.min.js', [ 'jquery', 'backbone', 'underscore' ], THEME_VERSION, true );

			wp_localize_script( 'thrive-widgets', 'ttb_widgets', $this->localize_object( 'widgets' ) );
		}

		/* add this css on the 'add new post/page' and 'edit post/page' screens */
		if ( is_admin() && Thrive_Utils::is_allowed_post_type( thrive_post()->ID ) && ! thrive_post()->is_landing_page() && ! Thrive_Utils::is_architect_editor() ) {
			wp_enqueue_style( 'thrive-admin-style', THEME_ASSETS_URL . '/post.css', false, THEME_VERSION );
			tve_dash_enqueue_script( 'thrive-admin-post-edit', THEME_ASSETS_URL . '/post-edit.min.js', [
				'jquery',
				'backbone',
				'underscore',
			], THEME_VERSION, true );
		}

	}

	/**
	 * Localize object for site scripts
	 *
	 * @param string $context for where to localize things.
	 *
	 * @return array
	 */
	private function localize_object( $context = '' ) {
		$blog_id = get_current_blog_id();

		switch ( $context ) {
			case 'admin':
				$object = [
					'debug'                      => tve_dash_is_debug_on(),
					'templates'                  => array_values( Thrive_Template::localize_all() ),
					'post_formats'               => array_merge( [ THRIVE_STANDARD_POST_FORMAT ], static::post_formats() ),
					//we need to add standard because it doesn't really exists
					'content_types'              => Thrive_Utils::get_content_types( 'localize' ),
					'woocommerce'                => Woo::admin_localize(),
					'skins'                      => Thrive_Skin_Taxonomy::get_all(),
					'skin_id'                    => thrive_skin()->ID,
					'typography'                 => thrive_skin()->get_typographies(),
					'branding'                   => Thrive_Branding::localize(),
					'options'                    => Thrive_Utils::get_homepage_options(),
					'nonce'                      => wp_create_nonce( 'wp_rest' ),
					'editor_nonce'               => wp_create_nonce( TCB_Editor_Ajax::NONCE_KEY ),
					'routes'                     => [
						'templates'  => get_rest_url( $blog_id, TTB_REST_NAMESPACE . '/templates' ),
						'typography' => get_rest_url( $blog_id, TTB_REST_NAMESPACE . '/typography' ),
						'skins'      => get_rest_url( $blog_id, TTB_REST_NAMESPACE . '/skins' ),
						'options'    => get_rest_url( $blog_id, TTB_REST_NAMESPACE . '/options' ),
						'images'     => get_rest_url( $blog_id, TTB_REST_NAMESPACE . '/image' ),
						'logo'       => get_rest_url( $blog_id, TCB_REST_NAMESPACE . '/logo' ),
						'wizard'     => get_rest_url( $blog_id, TTB_REST_NAMESPACE . '/wizard' ),
						'content'    => get_rest_url( $blog_id, TTB_REST_NAMESPACE . '/content' ),
						'plugins'    => get_rest_url( $blog_id, TTB_REST_NAMESPACE . '/plugins' ),
						'amp'        => get_rest_url( $blog_id, TTB_REST_NAMESPACE . '/amp' ),
						'palette'    => get_rest_url( $blog_id, TTB_REST_NAMESPACE . '/palette' ),
					],
					'list_templates'             => Thrive_Utils::list_templates(),
					'theme_url'                  => THEME_URL,
					'wizard'                     => Thrive_Wizard::localize_admin(),
					'home_url'                   => home_url( '/' ),
					'admin_url'                  => trailingslashit( admin_url() ),
					'menus'                      => tve_get_custom_menus(), //todo maybe we should call our function for getting the menus
					'dismissed_tooltips'         => (array) get_user_meta( wp_get_current_user()->ID, 'ttb_dismissed_tooltips', true ),
					'architect_url'              => tve_editor_url() . '/',
					'cache_plugins'              => Thrive_Plugins_Manager::get_cache_plugins(),
					'image_optimization_plugins' => Thrive_Plugins_Manager::get_image_optimization_plugins(),
					'amp'                        => Thrive\Theme\AMP\Settings::localize(),
					'dashboard_card_columns'     => Thrive_Defaults::dashboard_card_columns(),
					'default_tpm_theme_tag'      => static::get_default_tpm_theme_tag(),
					'license'                    => [
						'exp'           => ! TD_TTW_User_Licenses::get_instance()->has_active_license( 'ttb' ),
						'gp'            => TD_TTW_User_Licenses::get_instance()->is_in_grace_period( 'ttb' ),
						'show_lightbox' => TD_TTW_User_Licenses::get_instance()->show_gp_lightbox( 'ttb' ),
						'grace_time'    => TD_TTW_User_Licenses::get_instance()->get_grace_period_left( 'ttb' ),
						'link'          => tvd_get_individual_plugin_license_link( 'ttb' ),
					],
					't'                          => array(
						'nav' => array(
							'lightspeed' => __( 'Project Lightspeed', 'thrive-theme' ),
							'image'      => __( 'Image Optimization', 'thrive-theme' ),
							'cache'      => __( 'Minification and Caching', 'thrive-theme' ),
							'amp'        => __( 'Accelerated Mobile Pages', 'thrive-theme' ),
						),
					),
				];

				/**
				 * Include The skin Variables only for the end user. So not for Theme Builder Site
				 */
				if ( Thrive_Utils::is_end_user_site() ) {
					$object = array_merge( $object, [
						'skin_palettes'  => thrive_skin()->get_palettes(),
						'skin_variables' => thrive_skin()->get_variables( true ),
					] );
				}

				break;
			case 'front':
				$post_id         = get_the_ID();
				$thrive_template = thrive_template();

				$object = [
					'comments_form'      => [
						'error_defaults' => Thrive_Theme_Comments::get_comment_form_error_labels(),
					],
					'routes'             => [
						'posts'    => get_rest_url( $blog_id, TCB_REST_NAMESPACE . '/posts' ),
						'frontend' => get_rest_url( $blog_id, TTB_REST_NAMESPACE . '/frontend' ),
					],
					'tar_post_url'       => add_query_arg( 'from_theme', true, tcb_get_editor_url( $post_id ) ),
					'is_editor'          => is_editor_page_raw(),
					'ID'                 => $thrive_template->ID,
					'template_url'       => add_query_arg( 'from_tar', $post_id, tcb_get_editor_url( $thrive_template->ID ) ),
					'pagination_url'     => [
						'template' => Thrive_Utils::get_pagination_url_template(),
						'base'     => get_pagenum_link(),
					],
					'sidebar_visibility' => $thrive_template->get_user_sidebar_visibility(),
					'is_singular'        => is_singular(),
					'is_user_logged_in'  => is_user_logged_in(),
				];
				if ( static::is_active() && Thrive_Wizard::is_frontend() ) {
					$object['wizard'] = thrive_wizard()->localize_frontend();
				}
				break;
			case 'widgets':
				$object = [
					'nonce' => wp_create_nonce( 'wp_rest' ),
					'route' => get_rest_url( $blog_id, TTB_REST_NAMESPACE . '/sidebar' ),
				];
				break;
			default:
				$object = [];
		}

		/**
		 * Filter the returned localized data.
		 *
		 * @param array        $object ALL the data
		 * @param Thrive_Theme $theme  theme instance
		 */
		return apply_filters( "thrive_theme_localize_{$context}", $object, $this );
	}

	public function wp_head() {
		/* if we're on a landing page and we want to remove the theme css, we don't run anything from here */
		/**
		 * Used in Thrive Apprentice for general no access template
		 *
		 * @param boolean $block
		 */
		if ( apply_filters( 'thrive_theme_block_theme_template_styles', tve_post_is_landing_page() && ! empty( get_post_meta( get_the_ID(), 'tve_disable_theme_dependency', true ) ) ) ) {
			return;
		}

		/* output the head scripts added from Analytics & Scripts if we're not on a LP */
		$this->print_global_theme_scripts( 'head' );

		/* Add a pingback url auto-discovery header for singularly identifiable articles. */
		if ( is_singular() && pings_open() && static::is_active() ) {
			echo '<link rel="pingback" href="', esc_url( get_bloginfo( 'pingback_url' ) ), '">';
		}

		global $wp_query;
		if ( empty( $wp_query->posts ) ) {
			/* on a blog with no posts (I don't know why you would have that) default shared styles need to be loaded manually in case we need them for a template */
			echo tve_get_shared_styles( '' );
		}

		$thrive_template = thrive_template();

		if ( apply_filters( 'thrive_theme_use_inline_css', ! $thrive_template->is_default() || Thrive_Utils::is_inner_frame() || Thrive_Utils::is_preview() || Thrive_Utils::is_skin_preview() ) ) {
			/* inside the editor we print the css because the file contains only styles from default templates */
			echo $thrive_template->style();
		}

		echo thrive_layout()->style( true );

		/**
		 * output typography CSS in a custom style node just when editing TTB typography.
		 * Rest of the time this is printed from tcb_print_frontend_styles()
		 */
		if ( Thrive_Utils::is_theme_typography() ) {
			echo thrive_typography( get_the_ID() )->style( true );
		}

		if ( is_singular() && ! Thrive_Utils::is_inner_frame() ) {
			echo $thrive_template->dynamic_style();
		}

		if ( method_exists( '\TCB\Lightspeed\Main', 'preload_assets' ) ) {
			\TCB\Lightspeed\Main::preload_assets( $thrive_template->ID );
		}

		if ( ! tve_post_is_landing_page() ) {
			/* on landing pages we don't look for shared styles because the template does not render */
			$thrive_template->check_for_meta_tags();
		}
	}

	/**
	 * Scripts that render after body open
	 */
	public function theme_after_body_open() {
		/* output the body_open scripts added from Analytics & Scripts if we're not on a LP */
		$this->print_global_theme_scripts( 'body_open' );
	}

	public function wp_footer() {
		/* output the body_close scripts added from Analytics & Scripts if we're not on a LP */
		$this->print_global_theme_scripts( 'body_close' );
	}

	/**
	 * Output specific scripts from Analytics & Scripts if we're not on a LP
	 *
	 * @param $tag
	 *
	 * @return void
	 */
	public function print_global_theme_scripts( $tag ) {
		if ( tve_post_is_landing_page() === false && ! is_editor_page_raw( true ) && class_exists( 'TVD_SM_Frontend', false ) && method_exists( 'TVD_SM_Frontend', 'theme_scripts' ) ) {
			echo TVD_SM_Frontend()->theme_scripts( $tag );
		}
	}

	/**
	 * Prepare Thrive Theme node
	 *
	 * @param array $nodes
	 *
	 * @return array
	 */
	public function theme_admin_bar_menu( $nodes ) {
		if ( ! is_admin() && ! tve_post_is_landing_page() && ( ! is_singular() || Thrive_Utils::is_allowed_post_type( get_the_ID() ) ) && current_user_can( 'edit_posts' ) ) {
			$license_expired_class = '';
			if ( ! apply_filters( 'tcb_skip_license_check', false ) ) {
				if ( TD_TTW_User_Licenses::get_instance()->is_in_grace_period( 'ttb' ) ) {
					$license_expired_class = 'thrive-license-warning';
				} elseif ( ! TD_TTW_User_Licenses::get_instance()->has_active_license( 'ttb' ) ) {
					$license_expired_class = 'thrive-license-warning-red';
				}
			}
			$post_title    = thrive_template()->post_title;
			$template_name = empty( $post_title ) ? '' : ' "' . $post_title . '"';
			$args          = [
				'id'    => 'thrive-builder',
				'title' => __( 'Edit Theme Template', 'thrive-theme' ) . $template_name,
				'href'  => add_query_arg( [ 'from_tar' => get_the_ID() ], tcb_get_editor_url( thrive_template()->ID ) ),
				'order' => 1,
				'meta'  => array(
					'class' => $license_expired_class,
				),
			];

			/* Add the node to the others */
			$nodes[] = $args;
		}

		return $nodes;
	}

	/**
	 * @return string|null
	 */
	public static function get_default_tpm_theme_tag() {
		$tag = null;

		if ( class_exists( 'TPM_Product_Skin', false ) && defined( 'TPM_Product_Skin::DEFAULT_TAG' ) ) {
			$tag = TPM_Product_Skin::DEFAULT_TAG;
		}

		return $tag;
	}

	/**
	 * Print admin backbone templates and dashboard svgs
	 */
	public function admin_footer() {
		if ( Thrive_Utils::is_thrive_page( THRIVE_THEME_DASH_PAGE ) ) {
			$templates = tve_dash_get_backbone_templates( THEME_PATH . '/inc/templates/backbone', 'backbone' );

			tve_dash_output_backbone_templates( $templates, 'ttd-' );

			$templates = tve_dash_get_backbone_templates( THEME_PATH . '/thrive-dashboard/templates/modal', 'templates' );

			tve_dash_output_backbone_templates( $templates );

			include THEME_PATH . '/inc/assets/svg/dashboard.svg';
		}

		/**
		 * Enqueue typography for gutenberg editor too
		 */
		if ( 'post' === tve_get_current_screen_key( 'base' ) ) {

			/**
			 * Prevents apply default styles to whole page but only to editable area
			 */
			add_filter( 'thrive_theme_typography_plain_selector', function ( $selector ) {
				return '.tcb-plain-text';
			}, 10, 1 );

			tve_load_global_variables();
			tcb_print_frontend_styles();
		}
	}

	/**
	 * Add animation class to the admin body
	 *
	 * @param $classes
	 *
	 * @return string
	 */
	public function admin_body_class( $classes ) {
		if ( ! tve_dash_is_debug_on() && Thrive_Utils::is_thrive_page( THRIVE_THEME_DASH_PAGE ) ) {
			$classes .= ' ttd-init ';
		}

		return $classes;
	}

	/**
	 * Add meta boxes for post/page settings.
	 */
	public function add_meta_boxes() {
		$thrive_post = thrive_post();

		/* don't add anything on landing pages; add these meta boxes only for post / page / custom post types */
		if ( ! $thrive_post->is_landing_page() && Thrive_Utils::is_allowed_post_type( $thrive_post->ID ) ) {
			if ( tcb_admin()->tcb_enabled( $thrive_post->ID ) ) {
				add_meta_box(
					'thrive-template-notice',
					__( 'Thrive Theme Builder', 'thrive-theme' ),
					[ Thrive_Views::class, 'no_template_settings_notice' ],
					null,
					'side',
					'high'
				);
			} else {
				/* we can edit the shop page in admin, but we don't want to display the template dropdown */
				if ( ! Woo::is_admin_shop_page() ) {
					add_meta_box(
						'thrive-template-meta',
						__( 'Theme Builder Templates', 'thrive-theme' ),
						[ Thrive_Views::class, 'template_meta_box' ],
						null,
						'side',
						'high'
					);
				}

				/* add a meta box for post visibility settings */
				add_meta_box(
					'thrive-visibility-meta',
					__( 'Theme Builder Visibility', 'thrive-theme' ),
					[ Thrive_Views::class, 'visibility_meta_box' ],
					null,
					'side',
					'high'
				);
			}

			add_meta_box(
				'thrive_post_format_options',
				__( 'Thrive Post Format Options', 'thrive-theme' ),
				[ Thrive_Views::class, 'post_format_options' ],
				'post',
				'normal',
				'high'
			);

			add_meta_box(
				'thrive_post_scripts',
				__( 'Custom Scripts', 'thrive-theme' ),
				[ $this, 'admin_metabox' ],
				null,
				'side',
				'high'
			);
		}
	}


	/**
	 * Save the information ( visibility, video, audio ) from the meta boxes.
	 *
	 * @param $post_id
	 */
	public function save_post( $post_id ) {

		/* only save any post meta if the save was done from the WP screen ( this is also called by TAr save, but we handle that case elsewhere ) */
		if ( isset( $_POST['action'] ) && $_POST['action'] === 'editpost' ) {
			/* instantiate a Thrive_Post with this post ID and save the visibility meta */
			$post = new Thrive_Post( $post_id );

			/* save the visibility settings if they exist( if the post is edited with TAr, the visibility settings are hidden in the WP post screen! )  */
			if ( isset( $_POST['thrive_visibility_settings_enabled'] ) ) {
				$post->save_visibility_meta_from_wp();
			}

			/* save the template settings only if they exist ( same as above ) */
			if ( isset( $_POST['thrive_template_settings_enabled'] ) ) {
				$post->save_template_meta_from_wp();
			}

			/* save the information from the video format meta box */
			Thrive_Video_Post_Format_Main::save_video_meta_fields();

			/* save the information from the audio format meta box */
			Thrive_Audio_Post_Format_Main::save_audio_meta_fields();

			/* save custom post / page scripts */
			if ( ! empty( $_POST['post_ID'] ) && ! tve_post_is_landing_page( $_POST['post_ID'] ) ) {
				tcb_scripts()->save( $_POST );
			}
		}
	}

	/**
	 * Post formats supported by the theme
	 *
	 * @return array
	 */
	public static function post_formats() {
		return [ 'image', 'video', 'audio' ];
	}

	/**
	 * Hide admin bar when preview on iframe
	 *
	 * @param $show_admin_bar
	 *
	 * @return bool
	 */
	public function show_admin_bar( $show_admin_bar ) {

		if ( isset( $_GET[ THRIVE_NO_BAR ] ) ) {
			$show_admin_bar = false;
		}

		return $show_admin_bar;
	}

	/**
	 * Check if we are in preview mode and add parameters accordingly, then redirect
	 */
	public function template_redirect() {
		global $wp;
		$current_url = home_url( add_query_arg( $_GET, $wp->request ) );

		if (
			isset( $_SERVER['HTTP_REFERER'] )
			&& strpos( $_SERVER['HTTP_REFERER'], THRIVE_NO_BAR ) !== false //if the referer has thrive_no_bar ( so we are in preview mode )
			&& strpos( $current_url, THRIVE_NO_BAR ) === false // and the current url doesn't have thrive_no_bar -> than we should add it to the current url
		) {
			$location = add_query_arg( THRIVE_NO_BAR, '1', $current_url );

			//check if we are also in the skin preview mode
			if ( strpos( $_SERVER['HTTP_REFERER'], THRIVE_SKIN_PREVIEW ) !== false ) {
				//match the thrive_skin_preview=123 pattern
				preg_match( '/' . THRIVE_SKIN_PREVIEW . '=\\d+/', $_SERVER['HTTP_REFERER'], $matches, PREG_OFFSET_CAPTURE );

				if ( ! empty( $matches ) ) {
					//take the skin_preview_id from the matches
					$skin_preview_id = (int) filter_var( $matches[0][0], FILTER_SANITIZE_NUMBER_INT );
				}

				$location = add_query_arg( THRIVE_SKIN_PREVIEW, $skin_preview_id, $location );
			}
			wp_redirect( $location );
		}
	}

	/**
	 * Apply do_shortcode on the symbol's css, just in case there is some dynamic css in it
	 *
	 * @param $css
	 *
	 * @return string
	 */
	public function change_symbols_css( $css ) {
		return do_shortcode( $css );
	}

	/**
	 * If the symbol has theme shortcodes in it we need to save the meta values from their data attr
	 *
	 * @param $data
	 */
	public function after_symbol_save( $data ) {
		$theme_metas = [ 'icons', 'decorations' ];

		foreach ( $theme_metas as $meta ) {
			if ( isset( $data[ $meta ] ) ) {
				update_post_meta( $data['symbol']->ID, $meta, $data[ $meta ] );
			}
		}
	}

	/**
	 * Enable features for Thrive Dashboard
	 *
	 * @param array $enabled
	 *
	 * @return array
	 */
	public function enable_dashboard_features( $enabled ) {
		$enabled['script_manager']      = true;
		$enabled['coming-soon']         = true;
		$enabled['thrive_design_packs'] = true;

		return $enabled;
	}

	/**
	 * Even if we have Architect light, we still display the buttons to edit
	 *
	 * @param $access
	 *
	 * @return bool
	 */
	public function architect_access( $access ) {
		$post_id = get_the_ID();
		if ( $post_id && Thrive_Architect::is_light() ) {
			/* display edit button only if the user can edit posts */
			$access = current_user_can( 'edit_post', $post_id );
		}
		//for editor
		if ( ! $access ) {
			$access = Thrive_Theme_Product::has_access();
		}

		return $access;
	}

	/**
	 * Blog list has default content as default size
	 *
	 * @param $attr
	 *
	 * @return mixed
	 */
	public function post_list_content_default_attr( $attr ) {

		if ( Thrive_Shortcodes::is_inside_shortcode( [ 'thrive_blog_list' ] ) ) {
			$attr['size'] = is_search() ? 'excerpt' : 'content';
		}

		return $attr;
	}

	/**
	 * Hide attachment files from the Media Library's overlay (modal) view if they have theme section meta key set.
	 *
	 * @param array $args An array of query variables.
	 *
	 * @return mixed
	 */
	public function hide_attachment_from_media_library_lightbox( $args ) {

		if ( is_admin() ) {
			// Modify the query.
			$args['meta_query'] = [
				[
					'key'     => THRIVE_DEMO_CONTENT_THUMBNAIL,
					'compare' => 'NOT EXISTS',
				],
			];
		}

		return $args;
	}

	/**
	 * Hide attachment files from the Media Library's list view if they have the 'demo content' flag set.
	 * Taken from https://wordpress.stackexchange.com/a/271592
	 *
	 * @param WP_Query $query
	 */
	public function hide_attachment_from_media_library_dashboard( $query ) {
		if ( is_admin() && $query->is_main_query() ) {
			$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

			if ( $screen && $screen->id === 'upload' && $screen->post_type === 'attachment' ) {
				$query->set( 'meta_query', [
					[
						'key'     => THRIVE_DEMO_CONTENT_THUMBNAIL,
						'compare' => 'NOT EXISTS',
					],
				] );
			}
		}
	}

	/**
	 * Replace the redirect link from edit page to template dashboard
	 *
	 * @param $redirect_link
	 * @param $post
	 *
	 * @return mixed
	 */
	public function template_dashboard_redirect( $redirect_link, $post ) {

		if ( Thrive_Utils::is_theme_template() ) {
			$redirect_link = admin_url( 'admin.php?page=thrive-theme-dashboard&tab=other#templates' );
		}

		return $redirect_link;
	}

	/**
	 * Output <html>'s CSS class attribute
	 */
	public static function html_class() {
		/**
		 * Allows adding dynamic classes on the HTML element
		 *
		 * @param array $classes current list of classes
		 *
		 * @return array
		 */
		$classes = (array) apply_filters( 'thrive_html_class', [] );
		$attr    = '';

		if ( $classes ) {
			$attr = ' class="' . implode( ' ', $classes ) . '"';
		}

		echo $attr;
	}

	/**
	 * If this is a List template and we're not in the editor, get the posts_per_page from the content section meta
	 * The reason for doing this is that WP manually checks the 'global' posts_per_page set from 'Reading' and
	 * sets pages as 'not found' even if they exist according to the specific 'posts_per_page' setting of the blog list
	 *
	 * @param $value
	 *
	 * @return mixed
	 */
	public function posts_per_page( $value ) {
		global $wp_query;

		if (
			empty( $GLOBALS[ THRIVE_THEME_INSIDE_PRE_GET_POSTS ] ) && /* prevent infinite loops */
			! empty( $wp_query->query ) && /* only if we have a query */
			$wp_query->is_main_query() && /* only for the main query */
			! is_admin() && /* not in admin */
			! TCB_Utils::is_rest() && /* not for rest */
			! is_singular() && /* only for list templates */
			! is_404() && /* make sure this isn't a 404 page */
			! isset( $_GET[ TVE_EDITOR_FLAG ], $_GET[ TVE_FRAME_FLAG ] ) /* make sure we're not in the editor */
		) {
			$GLOBALS[ THRIVE_THEME_INSIDE_PRE_GET_POSTS ] = true;
			$thrive_template                              = thrive_template();
			$GLOBALS[ THRIVE_THEME_INSIDE_PRE_GET_POSTS ] = false;

			$posts_per_page = $thrive_template->get_meta_from_sections( 'posts_per_page' );

			if ( ! empty( $posts_per_page ) ) {
				$value = $posts_per_page;
			}
		}

		return $value;
	}

	/**
	 * Do not allow some TL form types to be show in the wizard and branding iframe
	 *
	 * @param array $items
	 *
	 * @return array
	 */
	public function intrusive_forms( $items, $product ) {
		if ( Thrive_Utils::is_iframe() ) {

			switch ( $product ) {
				case 'tl':
					$do_not_show = [ 'lightbox', 'ribbon', 'screen_filler', 'greedy_ribbon', 'slide_in' ];
					$filter_fn   = static function ( $item ) use ( $do_not_show ) {
						return ! in_array( $item->tve_form_type, $do_not_show, true );
					};
					break;
				case 'tu':
					$allowed_items = [ 'shortcode', 'widget' ];
					$filter_fn     = static function ( $item ) use ( $allowed_items ) {
						return in_array( $item['post_type'], $allowed_items, true );
					};
					break;
				case 'tcb':
					$items = [];
					break;
				default:
					break;
			}

			if ( ! empty( $filter_fn ) && is_callable( $filter_fn ) ) {
				$items = array_filter( $items, $filter_fn );
			}
		}

		return $items;
	}

	/**
	 * Allow SVG uploads
	 */
	public function allow_upload_svg( $allowed ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return $allowed;
		}

		$allowed['svg'] = 'image/svg+xml';

		return $allowed;
	}

	/**
	 * Show clone link next to the post name in the post list page
	 *
	 * @param array   $actions
	 * @param WP_Post $post
	 *
	 * @return mixed
	 */
	public function clone_link( $actions, $post ) {
		if ( current_user_can( 'edit_posts' ) && $post->post_status !== 'trash' ) {
			$actions['edit_as_new_draft'] = thrive_post( $post->ID )->get_clone_link_html();
		}

		return $actions;
	}

	/**
	 * Custom action for sidebar trigger
	 *
	 * @param $actions
	 *
	 * @return mixed
	 */
	public function tcb_event_manager_action_tabs( $actions ) {

		if ( Thrive_Utils::is_theme_template() ) {
			$actions['popup']['actions']['sidebar_toggle'] = [
				'class' => 'Thrive_Theme_Sidebar_Toggle_Action',
				'order' => 100,
			];
		}

		return $actions;
	}

	/**
	 * Custom action for sidebar trigger
	 *
	 * @param $classes
	 *
	 * @return mixed
	 */
	public function tcb_event_action_classes( $classes ) {
		$classes['sidebar_toggle'] = 'Thrive_Theme_Sidebar_Toggle_Action';

		return $classes;
	}

	/**
	 * Entry point for cloning a post / page
	 */
	public function clone_item() {
		if ( isset( $_GET['post'] ) ) {
			try {
				$id = thrive_post( $_GET['post'] )->duplicate();

				// Redirect to the edit screen for the new post / page
				wp_redirect( admin_url( 'post.php?action=edit&post=' . $id ) );
			} catch ( Exception $exception ) {
				wp_die( $exception->getMessage() );
			}
		} else {
			wp_die( __( 'No post to duplicate has been supplied!', 'thrive-theme' ) );
		}
	}

	/**
	 * When we're on a landing page and AMP is active, print the link towards the AMP equivalent of this LP.
	 *
	 * @param $landing_page_id
	 */
	public function print_amp_link_in_landing_page( $landing_page_id ) {
		Thrive\Theme\AMP\Main::print_amp_permalink( $landing_page_id );
	}

	/**
	 * Decide if we will show two step lighbox in the theme
	 * We would like to prevent that only within our iframes and if there is a page event setup for that specific lightbox
	 *
	 * @param bool $do_not_show
	 * @param int  $id
	 *
	 * @return bool
	 */
	public function do_not_show_two_step_lighbox( $do_not_show, $id ) {
		if ( Thrive_Utils::is_iframe() ) {
			$events = tve_get_post_meta( get_the_ID(), 'tve_page_events' );

			if ( ! empty( $events ) ) {
				foreach ( $events as $event ) {
					if ( ! empty( $event['config']['l_id'] ) && $event['config']['l_id'] === $id ) {
						$do_not_show = true;
					}
				}
			}
		}

		return $do_not_show;
	}

	/**
	 * Change the inline shortcodes for taxonomies when we are on a list pge
	 *
	 * @param $shortcodes
	 *
	 * @return mixed
	 */
	public function inline_shortcodes( $shortcodes ) {
		if ( thrive_template()->is_archive() ) {
			$shortcodes['Archive'] = [
				[
					'name'   => __( 'Archive Name', 'thrive-theme' ),
					'option' => __( 'Archive Name', 'thrive-theme' ),
					'value'  => 'thrive_archive_name',
				],
				[
					'name'   => __( 'Archive Description', 'thrive-theme' ),
					'option' => __( 'Archive Description', 'thrive-theme' ),
					'value'  => 'thrive_archive_description',
				],
				[
					'name'   => __( 'Archive Parent Name', 'thrive-theme' ),
					'option' => __( 'Archive Parent Name', 'thrive-theme' ),
					'value'  => 'thrive_archive_parent_name',
				],
			];
		}

		return $shortcodes;
	}

	/**
	 * Do not allow Thrive Optimize A/B Test option to show when the user is no a theme template
	 * This filter is also user in TAR
	 *
	 * @param bool   $allow
	 * @param string $post_type
	 *
	 * @return bool
	 */
	public function tve_allowed_post_type( $allow, $post_type ) {
		if ( $post_type === THRIVE_TEMPLATE ) {
			$allow = false;
		}

		return $allow;
	}

	/**
	 * Exclude theme post types from google index
	 *
	 * @param $post_types
	 *
	 * @return mixed
	 */
	public function exclude_post_types_from_index( $post_types ) {
		$post_types[] = THRIVE_TEMPLATE;
		$post_types[] = THRIVE_SECTION;

		return $post_types;
	}

	/**
	 * Allow Thrive Comments on singular templates
	 *
	 * @param bool $allow
	 *
	 * @return bool
	 */
	public function allow_thrive_comments( $allow ) {
		if ( ! Thrive_Utils::is_iframe() && Thrive_Utils::is_editor() && thrive_template()->is_singular() ) {
			$allow = true;
		}

		return $allow;
	}

	/**
	 * Render the post scripts meta box from post / page edit view
	 */
	public static function admin_metabox() {
		include THEME_PATH . '/inc/templates/admin/scripts-metabox.php';
	}

	/**
	 * Always show the "Enable auto-updates" link for TTB, even if there is no update available
	 *
	 * @param array $themes
	 *
	 * @return mixed
	 */
	public function auto_update( $themes ) {

		if ( ! empty( $themes['thrive-theme'] ) && empty( $themes['thrive-theme']['autoupdate']['supported'] ) ) {
			$themes['thrive-theme']['autoupdate']['supported'] = true;
		}

		return $themes;
	}
}
