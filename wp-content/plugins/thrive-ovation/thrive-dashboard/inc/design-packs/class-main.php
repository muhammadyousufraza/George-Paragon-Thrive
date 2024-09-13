<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

namespace TVE\Dashboard\Design_Packs;

use function preg_replace;
use function str_ireplace;
use function strpos;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Main class of design packs
 * Handles the file logic, actions & filters and generic functions
 */
class Main {

	const SLUG                = 'thrive_design_packs';
	const TITLE               = 'Design Packs';
	const PAGE_SLUG           = 'admin_page_thrive_design_packs';
	const DESIGN_PACKS_FOLDER = 'thrive-design-packs';
	const PER_PAGE_LIMIT      = 100;
	const CFG_NAME            = 'tve-design-pack-config.json';

	public static function init() {
		$has_ttb = \tve_dash_is_ttb_active();
		$has_tar = defined( 'TVE_PLUGIN_FILE' );

		if ( PHP_VERSION_ID >= 70000 && class_exists( 'ZipArchive' ) && ( $has_ttb || $has_tar ) && static::has_access() ) {
			try {
				static::ensure_folders();

				static::require_extra_files( __DIR__ );
				static::add_hooks();
			} catch ( \Exception $e ) {
				add_action( 'admin_notices', static function () use ( $e ) {
					echo sprintf( '<div class="notice notice-error is-dismissible"><p>%s</p></div>', esc_html( $e->getMessage() ) );
				} );
			}
		}
	}

	public static function get_rest_string_arg_data() {
		return [
			'type'              => 'string',
			'required'          => true,
			'validate_callback' => static function ( $param ) {
				return ! empty( $param );
			},
		];
	}

	public static function get_rest_optional_string_arg_data() {
		return [
			'type'     => 'string',
			'required' => false,
		];
	}

	public static function get_rest_integer_arg_data( $required = true ) {
		return [
			'type'     => 'integer',
			'required' => $required,
		];
	}

	/**
	 * load the files needed for the design packs
	 *
	 * @param $path
	 *
	 * @return void
	 */
	public static function require_extra_files( $path ) {
		$items = array_diff( scandir( $path ), [ '.', '..' ] );

		foreach ( $items as $item ) {
			$item_path = $path . '/' . $item;
			if ( is_dir( $item_path ) ) {
				static::require_extra_files( $item_path );
			}

			if ( is_file( $item_path ) && substr( $item_path, - 3 ) === 'php' ) {
				require_once $item_path;
			}
		}
	}

	public static function add_hooks() {
		static::add_filters();
		static::add_actions();
	}

	public static function add_actions() {
		add_action( 'admin_menu', [ __CLASS__, 'admin_menu' ] );
		add_action( 'admin_enqueue_scripts', [ __CLASS__, 'admin_enqueue_scripts' ] );
		add_action( 'rest_api_init', [ __CLASS__, 'rest_api_init' ] );
	}

	public static function add_filters() {
		add_filter( 'tve_dash_filter_features', [ __CLASS__, 'add_dash_feature' ] );
		add_filter( 'thrive_theme_imported_skin_name', [ __CLASS__, 'rename_imported_file' ] );
		add_filter( 'tve_imported_content_name', [ __CLASS__, 'rename_imported_file' ] );
		add_filter( 'tve_imported_lp_name', [ __CLASS__, 'rename_imported_file' ] );
	}


	public static function rest_api_init() {
		$rest = new Rest();
		$rest->register_routes();
	}

	public static function admin_menu() {
		add_submenu_page(
			'options.php',
			static::TITLE,
			static::TITLE,
			'manage_options',
			static::SLUG,
			static function () {
				echo '<div id="thrive-design-packs"></div>';
			}
		);
	}

	/**
	 * Add the feature to dashboard
	 *
	 * @param $features
	 *
	 * @return mixed
	 */
	public static function add_dash_feature( $features ) {
		$features[ static::SLUG ] = array(
			'icon'        => 'tvd-thrive-design-packs',
			'title'       => static::TITLE,
			'description' => __( 'All of your Thrive Suite designs (Theme Templates, Landing Pages, Page/Post Content) gathered in one place, available for you to quickly export or import.', 'thrive-dash' ),
			'btn_link'    => add_query_arg( 'page', static::SLUG, admin_url( 'admin.php' ) ),
			'btn_text'    => __( 'Manage Design Packs', 'thrive-dash' ),
		);

		return $features;
	}

	/**
	 * Setup basic permission callback
	 */
	public static function has_access() {
		return current_user_can( TVE_DASH_CAPABILITY );
	}

	public static function admin_enqueue_scripts( $screen ) {
		if ( ! empty( $screen ) && $screen === static::PAGE_SLUG ) {
			tve_dash_enqueue_vue();

			tve_dash_enqueue_script( static::SLUG, TVE_DASH_URL . '/assets/dist/js/design-packs.js', [], TVE_DASH_VERSION, true );

			if ( is_file( TVE_DASH_PATH . '/assets/dist/css/design-packs.css' ) ) {
				tve_dash_enqueue_style( static::SLUG, TVE_DASH_URL . '/assets/dist/css/design-packs.css' );
			}

			wp_localize_script( static::SLUG, 'TD_DesignPacks', static::localize_data() );
		}
	}

	public static function localize_data() {
		$has_ttb = tve_dash_is_ttb_active();
		$types   = [
			[
				'type'                 => 'skin',
				'name'                 => 'Thrive Theme Builder Themes',
				'accessible'           => $has_ttb,
				'error_export_message' => sprintf(
					__( 'In order to use this feature, please <a href="%s" target="_blank" class="error-export-message-link">Activate Thrive Theme Builder</a>', 'thrive-dash' ),
					admin_url( 'admin.php?page=thrive_product_manager' ) ),
				'error_import_message' => __( 'This Thrive Design Pack contains some themes. In order for themes to be imported Thrive Theme Builder needs to be active.', 'thrive-dash' ),
				'displayedName'        => 'theme',
				'redirect_message'     => 'Go to Thrive Theme Builder',
				'redirect_link'        => admin_url( 'admin.php?page=thrive-theme-dashboard&tab=other#skins' ),
			],
			[
				'type'             => 'landing_page',
				'name'             => 'Landing Pages',
				'import_name'      => 'Landing Page Templates',
				'accessible'       => true,
				'displayedName'    => 'landing page',
				'redirect_message' => 'Learn more',
				'redirect_link'    => 'https://help.thrivethemes.com/en/articles/6353871-where-can-i-find-my-page-post-content-and-landing-pages-after-importing-a-design-kit',
			],
			[
				'type'             => 'page',
				'name'             => 'Pages',
				'import_name'      => 'Page Content Templates',
				'accessible'       => true,
				'displayedName'    => 'page',
				'redirect_message' => 'Learn more',
				'redirect_link'    => 'https://help.thrivethemes.com/en/articles/6353871-where-can-i-find-my-page-post-content-and-landing-pages-after-importing-a-design-kit',
			],
			[
				'type'             => 'post',
				'name'             => 'Posts',
				'import_name'      => 'Post Content Templates',
				'accessible'       => true,
				'displayedName'    => 'post',
				'redirect_message' => 'Learn more',
				'redirect_link'    => 'https://help.thrivethemes.com/en/articles/6353871-where-can-i-find-my-page-post-content-and-landing-pages-after-importing-a-design-kit',
			],
		];

		/**
		 * Filter the types of content that can be imported/exported
		 */
		$types = apply_filters( 'tve_dash_design_packs_types', $types );

		return [
			'routes'            => get_rest_url( get_current_blog_id(), Rest::REST_NAMESPACE ),
			'wp_routes'         => get_rest_url( get_current_blog_id(), 'wp/v2' ),
			'has_ttb'           => $has_ttb,
			'has_tar'           => tve_dash_is_plugin_active( 'thrive-visual-editor' ),
			'page_limit'        => static::PER_PAGE_LIMIT,
			'export_types'      => $types,
			'placeholder_image' => TVE_DASH_URL . '/inc/design-packs/assets/img/lp-placeholder.png',
			'spinner_image'     => TVE_DASH_URL . '/inc/design-packs/assets/img/spinner.svg',
		];
	}

	public static function get_exported_dir_url() {
		return wp_upload_dir()['baseurl'] . '/' . static::DESIGN_PACKS_FOLDER . '/exported/';
	}

	public static function get_exported_dir_path() {
		return wp_upload_dir()['basedir'] . '/' . static::DESIGN_PACKS_FOLDER . '/exported/';
	}

	public static function get_imported_dir_url() {
		return wp_upload_dir()['baseurl'] . '/' . static::DESIGN_PACKS_FOLDER . '/imported/';
	}

	public static function get_imported_dir_path() {
		return wp_upload_dir()['basedir'] . '/' . static::DESIGN_PACKS_FOLDER . '/imported/';
	}

	/**
	 * Ensure the folders in which we will save the archive exists
	 *
	 * @throws \Exception
	 */
	public static function ensure_folders() {
		/**
		 * first make sure we can save the archive
		 */
		$upload = wp_upload_dir();
		if ( ! empty( $upload['error'] ) ) {
			throw new \Exception( $upload['error'] );
		}

		$base = trailingslashit( $upload['basedir'] ) . static::DESIGN_PACKS_FOLDER . '/imported';

		if ( ! is_dir( $base ) && ! mkdir( $base, 0777, true ) && ! is_dir( $base ) ) {
			throw new \RuntimeException( sprintf( 'Directory "%s" was not created', $base ) );
		}

		$base = trailingslashit( $upload['basedir'] ) . static::DESIGN_PACKS_FOLDER . '/exported';

		if ( ! is_dir( $base ) && ! mkdir( $base, 0777, true ) && ! is_dir( $base ) ) {
			throw new \RuntimeException( sprintf( 'Directory "%s" was not created', $base ) );
		}
	}

	/**
	 * Make sure that imported data name doesn't contain export naming
	 *
	 * @param $name
	 *
	 * @return string
	 */
	public static function rename_imported_file( $name ) {
		if ( strpos( $name, Export::EXPORT_SUFFIX ) !== false ) {
			$name = str_ireplace( '.zip', '', $name );
			$name = preg_replace( '#tve_exp_[A-Za-z\d]*$#', ' (imported)', $name );
		}

		return $name;
	}
}
