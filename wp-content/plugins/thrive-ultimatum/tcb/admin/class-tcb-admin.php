<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 3/3/2017
 * Time: 1:11 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden.
}

/**
 * Thrive Architect admin class.
 */
class TCB_Admin {

	/**
	 * Define namespace for the rest endpoints
	 */
	const TCB_REST_NAMESPACE = 'tcb/v1';

	/**
	 * The single instance of the class.
	 *
	 * @var TCB_Admin singleton instance.
	 */
	protected static $_instance = null;

	public function __construct() {

		add_action( 'init', [ $this, 'includes' ] );

		add_filter( 'tve_dash_admin_product_menu', [ $this, 'add_to_dashboard_menu' ] );

		/**
		 * Add admin scripts and styles
		 */
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

		add_filter( 'tve_dash_features', [ $this, 'dashboard_add_features' ] );

		add_action( 'admin_footer', [ $this, 'admin_page_loader' ] );

		/* admin TCB edit button */
		add_action( 'edit_form_after_title', [ $this, 'admin_edit_button' ] );

		add_action( 'admin_footer', [ $this, 'tcb_architect_gutenberg_switch' ] );

		add_filter( 'admin_body_class', [ $this, 'wp_editor_body_class' ], 10, 4 );

		add_action( 'save_post', [ $this, 'maybe_disable_tcb_editor' ] );
	}

	/**
	 * Main TCB Admin Instance.
	 * Ensures only one instance of TCB Admin is loaded or can be loaded.
	 *
	 * @return TCB_Admin
	 */
	public static function instance() {
		if ( empty( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Includes required files
	 */
	public function includes() {
		require_once 'includes/tcb-admin-functions.php';
		require_once 'includes/class-tcb-admin-ajax.php';
	}

	/**
	 * Push the Thrive Quiz Builder to Thrive Dashboard menu
	 *
	 * @param array $menus items already in Thrive Dashboard.
	 *
	 * @return array
	 */
	public function add_to_dashboard_menu( $menus = [] ) {
		$cap = tcb_has_external_cap( true );

		if ( $cap ) {
			$menus['tcb'] = array(
				'parent_slug' => '',
				'page_title'  => __( 'Content Templates', 'thrive-cb' ),
				'menu_title'  => __( 'Content Templates', 'thrive-cb' ),
				'capability'  => $cap,
				'menu_slug'   => 'tcb_admin_dashboard',
				'function'    => [ $this, 'dashboard' ],
			);
		}

		return $menus;
	}

	/**
	 * Output TCB Admin dashboard
	 */
	public function dashboard() {
		include $this->admin_path( 'includes/views/dashboard.phtml' );
	}

	public function enqueue_scripts( $hook ) {
		$accepted_hooks = apply_filters( 'tcb_admin_accepted_admin_pages', [
			'thrive-dashboard_page_tcb_admin_dashboard',  // Visible in Thrive Dashboard side menu
			'admin_page_tcb_admin_dashboard',  // Not visible in Thrive Dashboard side menu
		] );

		/* if classic editor plugin is activated ( `should_load_blocks()` => false ), load styles on post.php and post-new.php */
		$should_load = tve_should_load_blocks() || $hook === 'post.php' || $hook === 'post-new.php';

		if ( $should_load && tve_is_post_type_editable( get_post_type( get_the_ID() ) ) ) {
			$this->enqueue_post_editor();

			return;
		}

		if ( ! in_array( $hook, $accepted_hooks, true ) ) {
			return;
		}

		if ( tve_in_architect() && ! tve_tcb__license_activated() ) {
			return;
		}

		$js_suffix = TCB_Utils::get_js_suffix();

		/**
		 * Enqueue dash scripts
		 */
		tve_dash_enqueue();

		/**
		 * Specific admin styles
		 */
		tve_enqueue_style( 'tcb-admin-style', $this->admin_url( 'assets/css/tcb-admin-styles.css' ) );
		tve_enqueue_script( 'tcb-admin-js', $this->admin_url( 'assets/js/tcb-admin' . $js_suffix ), [
			'jquery',
			'backbone',
		] );

		wp_localize_script( 'tcb-admin-js', 'TVE_Admin', tcb_admin_get_localization() );

		/**
		 * Output the main templates for backbone views used in dashboard.
		 */
		add_action( 'admin_print_footer_scripts', [ $this, 'render_backbone_templates' ] );
	}

	/**
	 * make sure all the features required by TCB are shown in the dashboard
	 *
	 * @param array $features
	 *
	 * @return array
	 */
	public function dashboard_add_features( $features ) {

		if ( tcb_has_external_cap() ) {
			$features['smart_site']           = true;
			$features['font_manager']         = true;
			$features['icon_manager']         = true;
			$features['api_connections']      = true;
			$features['general_settings']     = true;
			$features['notification_manager'] = true;
		}

		return $features;
	}

	/**
	 * Render backbone templates
	 */
	public function render_backbone_templates() {
		$templates = tve_dash_get_backbone_templates( $this->admin_path( 'includes/views/templates' ), 'templates' );

		tve_dash_output_backbone_templates( $templates );
	}

	/**
	 * Full admin path to file if specified
	 *
	 * @param string $file to be appended to the plugin path.
	 *
	 * @return string
	 */
	public function admin_path( $file = '' ) {
		return plugin_dir_path( __FILE__ ) . ltrim( $file, '\\/' );
	}

	/**
	 * Full plugin url to file if specified
	 *
	 * @param string $file to be appended to the plugin url.
	 *
	 * @return string
	 */
	public function admin_url( $file = '' ) {
		return tve_editor_url( 'admin' ) . '/' . ltrim( $file, '\\/' );
	}

	/**
	 * Enqueue and localize scripts on the admin post edit page.
	 */
	public function enqueue_post_editor() {
		$js_suffix = TCB_Utils::get_js_suffix();

		wp_enqueue_style( 'wp-pointer' );
		wp_enqueue_script( 'wp-pointer' );

		tve_enqueue_script( 'tcb-admin-edit-post', tve_editor_js( '/admin' . $js_suffix ) );
		wp_localize_script( 'tcb-admin-edit-post', 'TCB_Post_Edit_Data', array_merge( tcb_admin_get_localization(), array(
			'post_id'      => get_the_ID(),
			'landing_page' => tve_post_is_landing_page(),
		) ) );

		tve_enqueue_style( 'tcb-admin-style', $this->admin_url( 'assets/css/tcb-admin-styles.css' ) );
	}

	/**
	 * Include the HTML for a loading overlay on admin pages.
	 */
	public function admin_page_loader() {
		tcb_template( 'admin/page-loader' );
	}

	/**
	 * output TCB editor button in the admin area
	 */
	public function admin_edit_button() {
		$post_type      = get_post_type();
		$post_id        = get_the_ID();
		$page_for_posts = get_option( 'page_for_posts' );

		if ( ! tve_is_post_type_editable( $post_type ) || ! TCB_Product::has_post_access( $post_id ) ) {
			return;
		}

		if ( 'page' === $post_type && $page_for_posts && $post_id == $page_for_posts ) {
			tcb_template( 'admin/cannot-edit-blog-page' );

			return;
		}

		$url          = tcb_get_editor_url( get_the_ID() );
		$post_id      = get_the_ID();
		$post         = get_post( $post_id );
		$landing_page = tve_post_is_landing_page( $post_id );
		$wp_content   = $post->post_content;
		/* this means that this post has been saved with TCB at least once */
		$tcb_content = tve_get_post_meta( $post_id, 'tve_globals' );

		$show_migrate_button = false;
		if ( ! $landing_page && ! get_post_meta( $post_id, 'tcb2_ready', true ) ) {

			$show_migrate_button = true;

			/**
			 * If this meta does not exist, there are a couple of possible cases:
			 * 1) post is just being created - no TCB content and no WP content
			 * 2) no WordPress content, but with TCB content
			 * 3) WordPress content, but no TCB content - this means the user never saved the post with TCB
			 */
			if ( empty( $wp_content ) || empty( $tcb_content ) ) {
				$show_migrate_button = false;
			}
		}

		tcb_template( 'admin/post-edit-button', array(
			'edit_url'            => $url,
			'post_id'             => $post_id,
			'show_migrate_button' => $show_migrate_button,
			'landing_page'        => $landing_page,
			'tcb_enabled'         => ! $show_migrate_button && $this->tcb_enabled( $post_id ),
			'nonce'               => wp_create_nonce( 'tcb_revert_content' ),
		) );
	}

	/**
	 * output TCB editor button in the gutenberg edit page/post admin area
	 */
	public function tcb_architect_gutenberg_switch() {
		$post_id = get_the_ID();
		/** prevent to appear if TAR is not active or if the user does not have access*/
		if ( apply_filters( 'tcb_gutenberg_switch', ! tve_in_architect() || ! TCB_Product::has_post_access( $post_id ) ) ) {
			return false;
		}

		if ( 'post' !== tve_get_current_screen_key( 'base' ) ) {
			return false;
		}

		$url          = tcb_get_editor_url( $post_id );
		$post         = get_post( $post_id );
		$landing_page = tve_post_is_landing_page( $post_id );
		$wp_content   = $post->post_content;
		/* this means that this post has been saved with TCB at least once */
		$tcb_content = tve_get_post_meta( $post_id, 'tve_globals' );

		$show_migrate_button = false;
		if ( ! $landing_page && ! get_post_meta( $post_id, 'tcb2_ready', true ) ) {

			$show_migrate_button = true;

			/**
			 * If this meta does not exist, there are a couple of possible cases:
			 * 1) post is just being created - no TCB content and no WP content
			 * 2) no WordPress content, but with TCB content
			 * 3) WordPress content, but no TCB content - this means the user never saved the post with TCB
			 */
			if ( empty( $wp_content ) || empty( $tcb_content ) ) {
				$show_migrate_button = false;
			}
		}
		echo '<script id="thrive-gutenberg-switch" type="text/html">';
		tcb_template( 'admin/post-edit-button', array(
			'edit_url'            => $url,
			'post_id'             => $post_id,
			'show_migrate_button' => $show_migrate_button,
			'landing_page'        => $landing_page,
			'tcb_enabled'         => ! $show_migrate_button && $this->tcb_enabled( $post_id ),
			'nonce'               => wp_create_nonce( 'tcb_revert_content' ),
		) );
		echo '</script>';
		$js_suffix = TCB_Utils::get_js_suffix();
		tve_enqueue_script( 'thrive-gutenberg-switch', tve_editor_js( '/gutenberg' . $js_suffix ), [ 'jquery' ] );
	}

	/**
	 * For pages where TCB was enabled, add a class to the body in order to hide the default WP tinymce editor for the content
	 *
	 * @param string $classes
	 *
	 * @return string
	 */
	public function wp_editor_body_class( $classes ) {
		if ( 'post' !== tve_get_current_screen_key( 'base' ) ) {
			return $classes;
		}
		$post_type = get_post_type();
		$post_id   = get_the_ID();

		if ( empty( $post_id ) || empty( $post_type ) ) {
			return $classes;
		}

		if ( ! tve_is_post_type_editable( $post_type ) || ! TCB_Product::has_post_access( $post_id ) ) {
			return $classes;
		}

		$post = tcb_post( $post_id );

		$post->maybe_auto_migrate();

		if ( $post->editor_enabled() ) {
			$classes .= ' tcb-hide-wp-editor';
		}

		return $classes;
	}

	/**
	 * Check to see if a "disable_tcb_editor" input has been submitted - if yes, we disable the tcb editor for this post, and show the default WP content.
	 */
	public function maybe_disable_tcb_editor() {
		global $post;
		$tcb_post = tcb_post( $post );
		if ( ! empty( $_POST['tcb_disable_editor'] ) && wp_verify_nonce( sanitize_text_field( $_POST['tcb_disable_editor'] ), 'tcb_disable_editor' ) ) {
			$tcb_post->disable_editor();
		}
	}

	/**
	 * Return complete url for route endpoint
	 *
	 * @param string $endpoint Rest endpoint.
	 * @param int    $id       Specific endpoint.
	 * @param array  $args     Additional arguments.
	 *
	 * @return string
	 */
	public function tcm_get_route_url( $endpoint, $id = 0, $args = [] ) {

		$url = get_rest_url() . self::TCB_REST_NAMESPACE . '/' . $endpoint;

		if ( ! empty( $id ) && is_numeric( $id ) ) {
			$url .= '/' . $id;
		}

		if ( ! empty( $args ) ) {
			add_query_arg( $args, $url );
		}

		return $url;
	}

	/**
	 * @param $post_id
	 *
	 * @return bool
	 */
	public function tcb_enabled( $post_id ) {
		$editor_enabled  = get_post_meta( $post_id, 'tcb_editor_enabled', true );
		$editor_disabled = get_post_meta( $post_id, 'tcb_editor_disabled', true );

		return ! empty( $editor_enabled ) && empty( $editor_disabled );
	}
}

/**
 * @return TCB_Admin
 */
function tcb_admin() {
	return TCB_Admin::instance();
}

tcb_admin();
