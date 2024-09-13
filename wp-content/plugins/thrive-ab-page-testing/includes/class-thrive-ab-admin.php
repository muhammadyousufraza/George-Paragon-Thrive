<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-ab-page-testing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class Thrive_AB_Admin {

	protected static $_variations = array();

	public static $pages
		= array(
			'view_test' => array(
				'slug' => 'tab_admin_view_test',
			),
		);

	public static function init() {

		/**
		 * Init pages
		 */
		add_action( 'init', array( __CLASS__, 'init_pages' ) );

		/**
		 * on this filter we are very sure the user is on post.php within edit case/action
		 */
		add_filter( 'replace_editor', array( __CLASS__, 'remove_tar_edit_button' ), 10, 2 );

		add_filter( 'admin_body_class', array( __CLASS__, 'wp_editor_body_class' ), 10, 4 );

		add_filter( 'page_row_actions', array( __CLASS__, 'page_row_actions' ), 11, 2 );

		/**
		 * Add Thrive A/B Page Testing To Dashboard
		 */
		add_filter( 'tve_dash_admin_product_menu', array( __CLASS__, 'add_to_dashboard_menu' ) );


		/**
		 * Add admin scripts and styles
		 */
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );

		/**
		 * On Page delete, delete all the A/B Test Data
		 */
		add_action( 'delete_post', array( __CLASS__, 'delete_page_tests' ), 10 );

		/**
		 * Hooks the notification manager trigger types
		 */
		add_filter( 'td_nm_trigger_types', array( __CLASS__, 'filter_nm_trigger_types' ) );

		/**
		 * Thrown by Thrive Themes, maybe we should support more themes?
		 */
		add_filter( 'thrive_save_post_option', array( __CLASS__, 'save_meta_to_variations' ), 10, 3 );
	}

	/**
	 * Hook when a Thrive Theme Post Option is saved
	 * We need to replicate it to it's variations
	 *
	 * @param $post_id
	 * @param $meta_name
	 * @param $meta_value
	 *
	 * @return mixed
	 */
	public static function save_meta_to_variations( $meta_value, $post_id, $meta_name ) {

		$post_id = (int) $post_id;

		try {

			$page = new Thrive_AB_Page( $post_id );

			if ( empty( self::$_variations ) ) {
				self::$_variations = $page->get_variations( array(), 'obj' );
			}

			/** @var Thrive_AB_Page_Variation $variation */
			foreach ( self::$_variations as $variation ) {
				if ( $variation->ID === $post_id ) {
					continue;
				}

				$variation->get_meta()->update( $meta_name, $meta_value );
			}

		} catch ( Exception $e ) {

		}

		return $meta_value;
	}

	/**
	 * On page delete - Delete all the AB page data linked to the deleted page
	 *
	 * @param int $post_id
	 */
	public static function delete_page_tests( $post_id = 0 ) {

		if ( empty( $post_id ) ) {
			return;
		}

		$test_manager = new Thrive_AB_Test_Manager();

		$tests = $test_manager->get_tests( array( 'page_id' => $post_id ), 'array' );

		if ( empty( $tests ) ) {
			return;
		}

		foreach ( $tests as $test ) {
			Thrive_AB_Test_Manager::delete_test( array(
				'id'      => $test['id'],
				'page_id' => $post_id,
			) );
		}
	}

	public static function remove_tar_edit_button( $return, $post ) {

		try {
			$page    = new Thrive_AB_Page( (int) $post->ID );
			$test_id = $page->get_meta()->get( 'running_test_id' );
		} catch ( Exception $e ) {
		}

		if ( ! empty( $test_id ) ) {
			remove_action( 'edit_form_after_title', array( tcb_admin(), 'admin_edit_button' ) );

			add_action( 'edit_form_after_title', array( __CLASS__, 'tar_edit_button' ) );
		}

		return $return;
	}

	public static function tar_edit_button() {

		include dirname( __FILE__ ) . '/views/admin/tar-edit-button.php';
	}

	public static function wp_editor_body_class( $classes ) {

		$screen = get_current_screen();
		if ( empty( $screen ) || ! $screen->base || 'post' != $screen->base ) {
			return $classes;
		}
		$post_type = get_post_type();
		$post_id   = get_the_ID();

		if ( empty( $post_id ) || empty( $post_type ) ) {
			return $classes;
		}

		try {
			$page    = new Thrive_AB_Page( (int) $post_id );
			$test_id = $page->get_meta()->get( 'running_test_id' );
		} catch ( Exception $e ) {
		}

		if ( ! empty( $test_id ) ) {
			$classes .= ' tcb-hide-wp-editor';
		}

		return $classes;
	}

	public static function page_row_actions( $actions, $page ) {

		if ( empty( $actions['tcb'] ) ) {
			return $actions;
		}

		try {
			$page_instance = new Thrive_AB_Page( $page );
			$test_id       = $page_instance->get_meta()->get( 'running_test_id' );

			if ( ! empty( $test_id ) ) {
				/**
				 * when a pages has a test running remove some actions
				 */
				unset( $actions['tcb'] );
				unset( $actions['edit_as_new_draft'] );
				unset( $actions['trash'] );
				$test_url = Thrive_AB_Test_Manager::get_test_url( $test_id );
				$icon_url = thrive_ab()->url( 'assets/images/tab-logo.png' );
				?>
				<style type="text/css">
                    .thrive-ab-view-test-action {
                        background: url('<?php echo $icon_url ?>');
                        background-size: 17px 17px;
                        padding-left: 20px;
                        background-repeat: no-repeat;
                    }
				</style>
				<?php
				$actions['thrive-ab'] = '<a class="thrive-ab-view-test-action" href="' . $test_url . '">' . __( 'View test details', 'thrive-ab-page-testing' ) . '</a>';
			}
		} catch ( Exception $e ) {

		}

		return $actions;
	}


	/**
	 * Push the Thrive A/B Testing to Thrive Dashboard menu
	 *
	 * @param array $menus items already in Thrive Dashboard.
	 *
	 * @return array
	 */
	public static function add_to_dashboard_menu( $menus = array() ) {
		if ( ! class_exists( 'Thrive_AB_Product', false ) ) {
			require_once dirname( __FILE__ ) . '/class-thrive-ab-product.php';
		}

		$menus['tab'] = array(
			'parent_slug' => 'tve_dash_section',
			'page_title'  => __( 'Thrive Optimize', 'thrive-ab-page-testing' ),
			'menu_title'  => __( 'Thrive Optimize', 'thrive-ab-page-testing' ),
			'capability'  => Thrive_AB_Product::cap(),
			'menu_slug'   => 'tab_admin_dashboard',
			'function'    => array( __CLASS__, 'dashboard' ),
		);


		return $menus;
	}


	/**
	 * Enqueue all required scripts and styles
	 *
	 * @param string $hook page hook.
	 */
	public static function enqueue_scripts( $hook ) {

		$accepted_hooks = apply_filters( 'tab_accepted_admin_pages', array(
			'thrive-dashboard_page_tab_admin_dashboard',
			'admin_page_tab_admin_view_test',
		) );

		if ( ! in_array( $hook, $accepted_hooks, true ) ) {
			return;
		}

		/**
		 * Enqueue dash scripts
		 */
		tve_dash_enqueue();

		if ( ! thrive_ab()->license_activated() ) {
			return;
		}

		$js_suffix = defined( 'TVE_DEBUG' ) && TVE_DEBUG ? '.js' : '.min.js';

		/**
		 * Specific admin styles
		 */
		wp_enqueue_style( 'tab-admin-style', thrive_ab()->url( 'assets/css/admin-styles.css' ), array(), Thrive_AB::V );

		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'backbone' );

		wp_enqueue_script( 'tab-admin-js', thrive_ab()->url( 'assets/js/dist/tab-admin' . $js_suffix ), array(
			'jquery',
			'backbone',
		), Thrive_AB::V, true );

		wp_localize_script( 'tab-admin-js', 'ThriveAbAdmin', self::get_localization() );

		/**
		 * Output the main templates for backbone views used in dashboard.
		 */
		add_action( 'admin_print_footer_scripts', array( __CLASS__, 'render_backbone_templates' ) );
	}

	/**
	 * Render backbone templates
	 */
	public static function render_backbone_templates() {
		$templates = tve_dash_get_backbone_templates( thrive_ab()->path( 'includes/views/admin/backbone' ), 'backbone' );
		tve_dash_output_backbone_templates( $templates );
	}

	/**
	 * Output Thrive A/B Testing Dashboard - the main plugin admin page
	 */
	public static function dashboard() {

		if ( ! thrive_ab()->license_activated() ) {
			return;
		}

		include dirname( __FILE__ ) . '/views/admin/dashboard.php';
	}

	/**
	 * Hook into TD Notification Manager and push trigger types
	 *
	 * @param $trigger_types
	 *
	 * @return array
	 */
	public static function filter_nm_trigger_types( $trigger_types ) {

		if ( ! in_array( 'split_test_ends', array_keys( $trigger_types ) ) ) {
			$trigger_types['split_test_ends'] = __( 'A/B Test Ends', 'thrive-ab-page-testing' );
		}

		return $trigger_types;
	}

	/**
	 * Gets the javascript variables.
	 *
	 * @return array
	 */
	public static function get_localization() {
		return array(
			't'        => array(
				'Thrive_Dashboard'          => __( 'Thrive Dashboard', 'thrive-ab-page-testing' ),
				'Dashboard'                 => __( 'Optimize Dashboard', 'thrive-ab-page-testing' ),
				'about_to_delete_variation' => __( 'Are you sure you want to delete %s ?', 'thrive-ab-page-testing' ),
				'yes'                       => __( 'Yes', 'thrive-ab-page-testing' ),
				'no'                        => __( 'No', 'thrive-ab-page-testing' ),
			),
			'ajax'     => array(
				'url'               => admin_url( 'admin-ajax.php' ),
				'nonce'             => wp_create_nonce( Thrive_AB_Ajax::NONCE_NAME ),
				'action'            => Thrive_AB_Ajax::$action,
				'controller_action' => Thrive_AB_Ajax::$controller_action,
			),
			'dash_url' => admin_url( 'admin.php?page=tve_dash_section' ),
			'license'  => Thrive_AB::license_data(),
		);
	}

	/**
	 * callback for setting an admin menu for viewing a test
	 * initialize the page and add it to admin menu as submenu page
	 * it's not displayed on wp menu
	 */
	public static function view_test_menu() {

		$view_test_page = new Thrive_AB_Admin_View_Test_Page();
		$has_access     = class_exists( 'Thrive_AB_Product', false ) ? Thrive_AB_Product::cap() : current_user_can( 'manage_options' );
		add_submenu_page(
			null,
			__( 'View test', 'thrive-ab-page-testing' ),
			__( 'View Test', 'thrive-ab-page-testing' ),
			$has_access,
			self::$pages['view_test']['slug'],
			array( $view_test_page, 'render' )
		);
	}

	public static function init_pages() {

		require_once dirname( __FILE__ ) . '/admin/class-thrive-ab-view-test-page.php';

		add_action( 'admin_menu', array( __CLASS__, 'view_test_menu' ) );
	}
}

Thrive_AB_Admin::init();
