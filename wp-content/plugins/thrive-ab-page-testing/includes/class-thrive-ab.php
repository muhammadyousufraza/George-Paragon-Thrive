<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package ab-page-testing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class Thrive_AB {

	const V  = '2.28';
	const DB = '1.2';

	private static $_instance;

	/**
	 * @var null|Thrive_AB_Dashboard
	 */
	private $_dashboard;

	/**
	 * @var Thrive_AB_Query
	 */
	private $_query;

	protected static $_test_url;

	private function __construct() {

		$this->_includes();

		$this->_query = new Thrive_AB_Query();

		add_action( 'admin_init', array( $this, 'register_deactivation_hooks' ) );

		/**
		 * When TAr hooks into template_redirect
		 */
		add_action( 'tcb_hook_template_redirect', array( 'Thrive_AB_Editor', 'init' ) );

		add_action( 'has_non_landing_page_settings', array( __CLASS__, 'has_non_lp_settings' ), 10, 1 );


		add_filter( 'tve_dash_installed_products', array( __CLASS__, 'add_to_dashboard_list' ) );

		add_filter( 'tve_display_manager_current_post', static function ( $post ) {
			if ( $post->post_status === 'tab_variation' ) {
				/* if the display manager from leads or utilamtum tries to read current page, return the parent */
				$post = get_post( $post->post_parent );
			}

			return $post;
		} );

		if ( is_admin() ) {
			add_action( 'before_delete_post', array( 'Thrive_AB_Page', 'delete' ) );
			add_action( 'pre_trash_post', array( 'Thrive_AB_Page', 'trash' ), 10, 2 );
			add_action( 'save_post_page', array( __CLASS__, 'save_page' ), 10, 3 );
		} else {
			add_action( 'wp', array( $this, 'initiate_dashboard' ) );
			add_action( 'wp', array( $this, 'remove_admin_bar_tar_button' ) );
			add_filter( 'template_include', array( $this, 'determine_variation_template' ) );
			add_filter( 'tu_is_page_allowed', array( $this, 'is_campaign_allowed_on_variation' ), 10, 3 );
			add_filter( 'admin_bar_menu', array( $this, 'admin_bar_menu' ), 90 );
		}

		add_action( 'init', array( $this, 'update_checker' ) );
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
		add_action( 'thrive_dashboard_loaded', array( $this, 'dash_loaded' ) );

		add_action( 'thrive_prepare_migrations', array( $this, 'register_db_migrations' ) );
	}

	public static function instance() {

		if ( ! self::$_instance ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Data needed for license processing
	 * @return array
	 */
	public static function license_data() {
		$manager = TD_TTW_User_Licenses::get_instance();

		return [
			'exp'           => ! $manager->has_active_license( 'tab' ),
			'gp'            => $manager->is_in_grace_period( 'tab' ),
			'show_lightbox' => $manager->show_gp_lightbox( 'tab' ),
			'grace_time'    => $manager->get_grace_period_left( 'tab' ),
			'link'          => tvd_get_individual_plugin_license_link( 'tab' ),
		];
	}

	/**
	 * Push Thrive A/B Page Testing to Thrive Dashboard installed products list
	 *
	 * @param array $items
	 *
	 * @return array
	 */
	public static function add_to_dashboard_list( $items = array() ) {
		$items[] = new Thrive_AB_Product();

		return $items;
	}

	/**
	 * Check if the plugin can be used on a specific post type
	 *
	 * @param $post_type
	 *
	 * @return bool
	 */
	public function is_cpt_allowed( $post_type ) {
		return ! empty( $post_type ) && apply_filters( 'tve_allowed_post_type', true, $post_type );
	}


	/**
	 * @return Thrive_AB_Query
	 */
	public function get_query() {

		return $this->_query;
	}

	protected function _includes() {

		/**
		 * CORE
		 */
		require_once dirname( __FILE__ ) . '/class-thrive-ab-checker.php';
		require_once dirname( __FILE__ ) . '/class-thrive-ab-query.php';
		require_once dirname( __FILE__ ) . '/class-thrive-ab-post-types.php';
		require_once dirname( __FILE__ ) . '/class-thrive-ab-post-status.php';
		require_once dirname( __FILE__ ) . '/class-thrive-ab-post.php';
		require_once dirname( __FILE__ ) . '/class-thrive-ab-meta.php';
		require_once dirname( __FILE__ ) . '/class-thrive-ab-page.php';
		require_once dirname( __FILE__ ) . '/variations/class-thrive-ab-variation.php';
		require_once dirname( __FILE__ ) . '/variations/class-thrive-ab-page-variation.php';
		require_once dirname( __FILE__ ) . '/ajax/class-thrive-ab-ajax.php';
		require_once dirname( __FILE__ ) . '/class-thrive-ab-model.php';
		require_once dirname( __FILE__ ) . '/test/class-thrive-ab-test.php';
		require_once dirname( __FILE__ ) . '/test/class-thrive-ab-test-item.php';
		require_once dirname( __FILE__ ) . '/test/class-thrive-ab-test-manager.php';
		require_once dirname( __FILE__ ) . '/events/class-thrive-ab-event.php';
		require_once dirname( __FILE__ ) . '/events/class-thrive-ab-event-manager.php';
		require_once dirname( __FILE__ ) . '/class-thrive-ab-cookie-manager.php';
		require_once dirname( __FILE__ ) . '/class-thrive-ab-report-manager.php';
		require_once dirname( __FILE__ ) . '/compat.php';

		if ( is_admin() ) {
			require_once dirname( __FILE__ ) . '/class-thrive-admin-notices.php';
			require_once dirname( __FILE__ ) . '/class-thrive-ab-admin.php';
			require_once dirname( __FILE__ ) . '/class-thrive-ab-meta-box.php';
		}
		require_once dirname( __FILE__ ) . '/class-thrive-ab-editor.php';
		require_once dirname( __FILE__ ) . '/class-thrive-ab-dashboard.php';
		require_once dirname( __FILE__ ) . '/variations/class-thrive-ab-variation-manager.php';

	}

	/**
	 * Hook for plugin deactivation and TAr deactivation
	 */
	public function register_deactivation_hooks() {

		register_deactivation_hook( THRIVE_AB_PLUGIN_FILE, array( 'Thrive_Admin_Notices', 'remove_notices' ) );

		if ( defined( 'TVE_PLUGIN_FILE' ) ) {
			register_deactivation_hook( TVE_PLUGIN_FILE, array( 'Thrive_Admin_Notices', 'push_notice_active' ) );
		}
	}

	/**
	 * Helper for deactivating this plugin
	 */
	public function deactivate() {

		deactivate_plugins( THRIVE_AB_PLUGIN_FILE );
	}

	function load_plugin_textdomain() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'thrive-ab-page-testing' );

		load_textdomain( 'thrive-ab-page-testing', WP_LANG_DIR . '/thrive/thrive-ab-page-testing-' . $locale . '.mo' );
		load_plugin_textdomain( 'thrive-ab-page-testing', false, 'ab-page-testing/languages' );
	}

	public function path( $file = '' ) {
		return plugin_dir_path( THRIVE_AB_PLUGIN_FILE ) . ltrim( $file, '\\/' );
	}

	public function url( $file = '' ) {
		return plugin_dir_url( THRIVE_AB_PLUGIN_FILE ) . ltrim( $file, '\\/' );
	}

	public function plugin_name() {

		return 'Thrive Optimize';
	}

	/**
	 * Dashboard is instantiated if the WP_Query has specific query string
	 *
	 * @return null|Thrive_AB_Dashboard
	 */
	public function initiate_dashboard() {

		$this->_dashboard = Thrive_AB_Dashboard::instance();

		return $this->_dashboard;
	}

	public function is_dashboard() {

		return $this->_query->get_var( 'thrive-variations' ) === 'true';
	}

	public function table_name( $table_name ) {

		if ( class_exists( 'TD_DB_Migration' ) ) {
			$migration  = new TD_DB_Migration( 'tab' );
			$table_name = $migration->get_table_name( $table_name );
		}

		return $table_name;
	}

	/**
	 * Wrapper over the wp_enqueue_script function.
	 * It will add the plugin version to the script source if no version is specified.
	 *
	 * @param        $handle
	 * @param string $src
	 * @param array  $deps
	 * @param bool   $ver
	 * @param bool   $in_footer
	 */
	public function enqueue_script( $handle, $src = '', $deps = array(), $ver = false, $in_footer = false ) {
		if ( false === $ver ) {
			$ver = Thrive_AB::V;
		}

		wp_enqueue_script( $handle, $src, $deps, $ver, $in_footer );
	}

	/**
	 * Wrapper over the wp_enqueue_style function.
	 * It will add the plugin version to the style link if no version is specified
	 *
	 * @param        $handle
	 * @param bool   $src
	 * @param array  $deps
	 * @param bool   $ver
	 * @param string $media
	 */
	public function enqueue_style( $handle, $src = false, $deps = array(), $ver = false, $media = 'all' ) {
		if ( false === $ver ) {
			$ver = Thrive_AB::V;
		}

		wp_enqueue_style( $handle, $src, $deps, $ver, $media );
	}

	public function remove_admin_bar_tar_button() {
		global $post;

		if ( ! ( $post instanceof WP_Post ) ) {
			return;
		}

		try {
			$post_id      = $post->ID;
			$is_variation = Thrive_AB_Post_Types::VARIATION === $post->post_type || Thrive_AB_Post_Status::VARIATION === $post->post_status;
			if ( $is_variation && ! empty( $post->post_parent ) ) {
				$post_id = $post->post_parent;
			}

			$page    = new Thrive_AB_Page( $post_id );
			$test_id = $page->get_meta()->get( 'running_test_id' );

		} catch ( Exception $e ) {
		}

		if ( ! empty( $test_id ) ) {

			/**
			 * set this here for later use @see view_test_button()
			 */
			self::$_test_url = Thrive_AB_Test_Manager::get_test_url( $test_id );

			remove_action( 'admin_bar_menu', 'thrive_editor_admin_bar', 100 );
			add_action( 'admin_bar_menu', array( $this, 'view_test_button' ), 100 );
		}
	}

	/**
	 * Check if there is a valid activated license for the TAB plugin.
	 *
	 * @return bool
	 */
	public function license_activated() {

		if ( ! defined( 'TVE_Dash_Product_LicenseManager::TAB_TAG' ) ) {
			return false;
		}

		return TVE_Dash_Product_LicenseManager::getInstance()->itemActivated( TVE_Dash_Product_LicenseManager::TAB_TAG );
	}


	/**
	 * Called after dash has been loaded
	 */
	public function dash_loaded() {
		require_once dirname( __FILE__ ) . '/class-thrive-ab-product.php';
	}

	/**
	 * Checks for updates
	 */
	public function update_checker() {
		/** plugin updates script **/

		if ( ! class_exists( 'TVE_PluginUpdateChecker', false ) ) {
			return;
		}

		new TVE_PluginUpdateChecker(
			'http://service-api.thrivethemes.com/plugin/update',
			dirname( dirname( __FILE__ ) ) . '/thrive-ab-page-testing.php',
			'thrive-ab-page-testing',
			12,
			'',
			'thrive_ab_page_testing'
		);
		/**
		 * Adding icon of the product for update-core page
		 */
		add_filter( 'puc_request_info_result-thrive-ab-page-testing', array( $this, 'tab_set_product_icon' ) );
	}

	/**
	 * Adding the product icon for the update core page
	 *
	 * @param $info
	 *
	 * @return mixed
	 */
	public function tab_set_product_icon( $info ) {
		$info->icons['1x'] = thrive_ab()->url( 'assets/images/tab-logo.png' );

		return $info;
	}

	/**
	 * Force page template to be loaded when user edits/sees the variation custom post
	 *
	 * @param $template
	 *
	 * @return string
	 * @deprecated we should removed this and its caller because the variations are no longer custom posts
	 *
	 */
	public function determine_variation_template( $template ) {

		global $post;

		if ( $post instanceof WP_Post && Thrive_AB_Post_Types::VARIATION === $post->post_type ) {
			$template = get_page_template();
		}

		return $template;
	}

	/**
	 * @param $wp_admin_bar WP_Admin_Bar
	 */
	public function view_test_button( $wp_admin_bar ) {

		$test_link = self::$_test_url;
		$icon_url  = thrive_ab()->url( 'assets/images/tab-logo-admin-bar.png' );

		$args = array(
			'id'    => 'tve_button',
			'title' => __( 'View test details', 'thrive-ab-page-testing' ),
			'href'  => $test_link,
			'meta'  => array(
				'class' => 'thrive-ab-view-test',
				'html'  => '<style type="text/css">.thrive-ab-view-test .ab-item:before {content: url("' . $icon_url . '"); margin-right: 4px !important;}</style>',
			),
		);

		if ( ! empty( $test_link ) ) {
			$wp_admin_bar->add_node( $args );
		}
	}

	/**
	 * Check if $post is a variation
	 *
	 * @param $post
	 *
	 * @return bool|null
	 */
	public function maybe_variation( $post ) {

		if ( ! ( $post instanceof WP_Post ) ) {
			return null;
		}

		return Thrive_AB_Post_Status::VARIATION === $post->post_status || Thrive_AB_Post_Types::VARIATION === $post->post_type;
	}

	/**
	 * Hook when a page is update/added
	 * Hook for updating page variations options: thrive metas, _wp_page_template meta, post_password
	 *
	 * @param $post_ID int
	 * @param $post    WP_Post
	 * @param $update  bool
	 *
	 * @return $this|null|Thrive_AB_Meta
	 */
	public static function save_page( $post_ID, $post, $update ) {

		$is_insert = ! $update;

		/**
		 * this is an insert of a non Thrive_AB_Variation
		 */
		if ( $is_insert && Thrive_AB_Post_Status::VARIATION !== $post->post_status ) {
			return null;
		}

		try {
			/**
			 * insert of new variation
			 */
			if ( $is_insert && thrive_ab()->maybe_variation( $post ) ) {
				$page = new Thrive_AB_Page( $post->post_parent );

				$variation = new Thrive_AB_Page_Variation( $post );
				$variation->save(
					array(
						'ID'            => $variation->get_post()->ID,
						'post_password' => $page->get_post()->post_password,
					)
				);

				return $page->get_meta()->copy_non_thrive_meta( $post_ID )->copy_thrive_theme_meta( $post_ID );
			}

			/**
			 * user updates a page and we need to check if it has variations
			 * to update them too
			 */
			if ( $update && ! thrive_ab()->maybe_variation( $post ) ) {
				$page       = new Thrive_AB_Page( $post_ID );
				$variations = $page->get_variations( array(), 'obj' );
				array_shift( $variations );
				/** @var Thrive_AB_Page_Variation $variation */
				foreach ( $variations as $variation ) {
					$variation->save(
						array(
							'ID'            => $variation->get_post()->ID,
							'post_password' => $page->get_post()->post_password,
						)
					);
					$page->get_meta()
//					     ->copy_non_thrive_meta( $variation->get_post()->ID )
                         ->removed_unused_non_thrive_meta( $variation->get_post()->ID )
					     ->copy_thrive_theme_meta( $variation->get_post()->ID );
				}
			}

		} catch ( Exception $e ) {

		}

		return null;
	}

	/**
	 * Allow TU to display campaigns on page variations too
	 * if campaign is allowed on parent page
	 *
	 * @param $is_allowed bool
	 * @param $page       WP_Post
	 * @param $pages_tab  Thrive_Ult_Pages_Tab
	 *
	 * @return bool
	 */
	public function is_campaign_allowed_on_variation( $is_allowed, $page, $pages_tab ) {

		if ( $page instanceof WP_Post && thrive_ab()->maybe_variation( $page ) ) {
			$parent = get_post( $page->post_parent );

			$is_allowed = $parent instanceof WP_Post && $pages_tab->isPageAllowed( $parent );
		}

		return $is_allowed;
	}

	/**
	 * Hooks into the TD DB migrations manager
	 *
	 * @throws Exception
	 */
	public function register_db_migrations() {
		TD_DB_Manager::add_manager(
			thrive_ab()->path( 'migrations' ),
			'thrive_ab_page_testing_db',
			Thrive_AB::DB,
			'Thrive Optimize',
			'tab',
			'thrive_optimize_reset'
		);
	}

	/**
	 * Manage the wp admin bar
	 * - removes the Edit Page menu for variations
	 *
	 * @param WP_Admin_Bar $admin_bar
	 */
	public function admin_bar_menu( WP_Admin_Bar $admin_bar ) {

		global $post;

		if ( true === $this->maybe_variation( $post ) ) {
			$admin_bar->remove_menu( 'edit' );
		}
	}
}
