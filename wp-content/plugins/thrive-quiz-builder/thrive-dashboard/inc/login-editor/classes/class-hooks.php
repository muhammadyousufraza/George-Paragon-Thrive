<?php
/**
 * Thrive Dashboard - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

namespace TVD\Login_Editor;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Hooks
 *
 * @package TVD\Login_Editor
 */
class Hooks {

	public static function actions() {
		add_action( 'template_redirect', array( __CLASS__, 'template_redirect' ), 1 );

		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );

		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue_scripts' ) );

		add_action( 'wp_loaded', array( __CLASS__, 'enqueue_scripts' ) );

		add_action( 'login_head', array( __CLASS__, 'login_head' ) );

		if ( Main::is_edit_screen() ) {
			add_action( 'tcb_sidebar_extra_links', array( __CLASS__, 'add_extra_links' ) );

			add_filter( 'thrive_theme_scripts_post_types', '__return_empty_array', PHP_INT_MAX );

			add_filter( 'thrive_theme_content_types', '__return_empty_array', PHP_INT_MAX );

			add_action( 'tcb_output_components', array( __CLASS__, 'tcb_output_components' ) );

			add_action( 'tcb_main_frame_enqueue', array( __CLASS__, 'tcb_main_frame_enqueue' ) );
		}

		if ( Main::is_login_design_enabled() ) {
			add_action( 'login_footer', array( __CLASS__, 'default_enqueue' ), PHP_INT_MAX );
		}
	}

	public static function filters() {
		add_filter( 'tve_dash_filter_features', array( __CLASS__, 'tve_dash_filter_features' ) );

		add_filter( 'tve_dash_features', array( __CLASS__, 'tve_dash_features' ) );

		add_filter( 'tcb_frame_request_uri', array( __CLASS__, 'tcb_frame_request_uri' ) );

		add_filter( 'tcb_editor_preview_link_query_args', array( __CLASS__, 'tcb_editor_preview_link_query_args' ), 10, 2 );

		add_filter( 'tcb_element_instances', array( __CLASS__, 'tcb_element_instances' ) );

		add_filter( 'tve_main_js_dependencies', array( __CLASS__, 'tve_main_js_dependencies' ) );

		add_filter( 'tcb_modal_templates', array( __CLASS__, 'tcb_modal_templates' ) );

		add_filter( 'thrive_ignored_post_types', array( __CLASS__, 'thrive_ignored_post_types' ) );

		add_filter( 'tve_intrusive_forms', array( __CLASS__, 'intrusive_forms' ), 10, 2 );

		add_filter( 'login_headerurl', array( __CLASS__, 'logo_url' ) );

		add_filter( 'enable_login_autofocus', array( __CLASS__, 'login_focus' ) );

		add_filter( 'tcb_alter_cloud_template_meta', array( __CLASS__, 'tcb_alter_cloud_template_meta' ), 10, 2 );

		add_filter( 'tcb_allowed_ajax_options', array( __CLASS__, 'tcb_allowed_ajax_options' ) );
		/**
		 * Add custom post types to post visibility options blacklist
		 */
		add_filter( 'tcb_post_visibility_options_availability', array( __CLASS__, 'tve_dash_post_visibility_options' ) );
	}

	/* ###################################### ACTIONS ###################################### */

	/*
	 * Display admin dashboard
	 */
	public static function admin_dashboard() {
		$edit_url       = Post_Type::instance()->get_edit_url();
		$preview_url    = Post_Type::instance()->get_preview_url();
		$default_url    = add_query_arg( array( Main::EDIT_FLAG => 1 ), get_site_url() );
		$design_enabled = Main::is_login_design_enabled();

		include __DIR__ . '/../views/admin.php';
	}

	/**
	 * Display the login screen inside the editor when we have the param present
	 */
	public static function template_redirect() {
		if ( isset( $_REQUEST[ Main::EDIT_FLAG ] ) ) {
			add_filter( 'authenticate', '__return_null', PHP_INT_MAX );
			/* force no authentication on the login editor screen and hide errors  */
			add_filter( 'wp_login_errors', array( __CLASS__, 'wp_login_errors' ), 10, 2 );

			if ( function_exists( 'login_header' ) ) {
				login_header();
			} else {
				global $user_login, $error;
				$user_login = '';
				$error      = '';
				require_once ABSPATH . 'wp-login.php';
			}

			exit();
		}
	}

	/**
	 * Disable input focus on the login form from the iframe
	 *
	 * @param bool $to_focus
	 *
	 * @return false
	 */
	public static function login_focus( $to_focus ) {
		if ( Main::is_edit_screen() && Main::is_login_design_enabled() ) {
			$to_focus = false;
		}

		return $to_focus;
	}

	/**
	 * Change logo url for the login form
	 *
	 * @param string $url
	 *
	 * @return string|void
	 */
	public static function logo_url( $url ) {
		if ( Main::is_login_design_enabled() ) {
			$url = home_url();
		}

		return $url;
	}

	/**
	 * Create menu page for the login editor
	 */
	public static function admin_menu() {
		add_submenu_page(
			'',
			Main::title(),
			Main::title(),
			'manage_options',
			Main::MENU_SLUG,
			array( __CLASS__, 'admin_dashboard' )
		);
	}

	/**
	 * Enqueue admin scripts
	 *
	 * @param $screen
	 */
	public static function admin_enqueue_scripts( $screen ) {
		if ( ! empty( $screen ) && $screen === 'admin_page_tve_dash_login_editor' ) {
			tve_dash_enqueue();
		}
	}

	/**
	 * Enqueue scripts in admin but also in the editor
	 */
	public static function enqueue_scripts() {
		if ( Main::is_edit_screen() && Main::is_login_design_enabled() ) {
			tve_dash_enqueue_script( 'tvd-login-editor', TVE_DASH_URL . '/inc/login-editor/assets/dist/editor.min.js', array( 'jquery' ) );

			$elements = array();
			foreach ( Main::$elements as $element ) {
				$elements[] = $element->tag();
			}

			$has_template = get_option( 'tvd_login_screen_has_template', 0 );
			wp_localize_script( 'tvd-login-editor', 'tvd_login_editor',
				array(
					'elements'     => $elements,
					'logo'         => Main::get_main_logo_image(),
					'has_template' => empty( $has_template ) ? 0 : 1,
				) );
		}
	}

	/**
	 * Head actions for the login page
	 */
	public static function login_head() {
		if ( Main::is_login_design_enabled() ) {
			Post_Type::instance()->get_styles();

			if ( Main::is_edit_screen() && function_exists( 'tve_enqueue_editor_scripts' ) ) {
				tve_enqueue_editor_scripts();
				tve_frontend_enqueue_scripts();
				tve_load_global_variables();
			}
		}
	}

	/**
	 * Include extra links in the sidebar
	 */
	public static function add_extra_links() {
		include dirname( __DIR__ ) . '/views/sidebar.php';
	}

	/**
	 * Include Login editor components
	 */
	public static function tcb_output_components() {
		$path  = dirname( __DIR__ ) . '/views/components/';
		$files = array_diff( scandir( $path ), array( '.', '..' ) );

		foreach ( $files as $file ) {
			include $path . $file;
		}
	}

	public static function tcb_main_frame_enqueue() {
		tve_dash_enqueue_style( 'tvd-login-editor-main-frame', TVE_DASH_URL . '/inc/login-editor/assets/css/main-frame.css' );
	}

	/**
	 * Load default styles/js for admin login
	 */
	public static function default_enqueue() {
		echo '<style>';
		include dirname( __DIR__ ) . '/assets/css/default-style.css';
		echo '</style>';

		echo '<script type="text/javascript">';
		include dirname( __DIR__ ) . '/assets/dist/image-fix' . ( tve_dash_is_debug_on() ? '' : '.min' ) . '.js';
		echo '</script>';
	}

	/* ###################################### FILTERS ###################################### */

	/**
	 * Add dashboard card for the login editor
	 *
	 * @param array $features
	 *
	 * @return array
	 */
	public static function tve_dash_filter_features( $features ) {
		$features['login-editor'] = array(
			'icon'        => 'tvd-login-editor',
			'title'       => Main::title(),
			'description' => __( 'Visually design your WordPress login screen.', 'thrive-dash' ),
			'btn_link'    => add_query_arg( 'page', Main::MENU_SLUG, admin_url( 'admin.php' ) ),
			'btn_text'    => __( 'Manage Login Screen', 'thrive-dash' ),
		);

		return $features;
	}

	/**
	 * Enable this feature for all plugins for now
	 *
	 * @param array $enabled
	 *
	 * @return array
	 */
	public static function tve_dash_features( $enabled ) {
		$enabled['login-editor'] = true;

		return $enabled;
	}

	/**
	 * Hide authentication errors on the login screen
	 *
	 * @param \WP_Error $errors
	 * @param string    $redirect_to
	 *
	 * @return mixed
	 */
	public static function wp_login_errors( $errors, $redirect_to ) {
		if ( isset( $_REQUEST[ Main::EDIT_FLAG ] ) && $errors instanceof \WP_Error ) {
			$errors->remove( 'authentication_failed' );
		}

		return $errors;
	}

	/**
	 * Add edit flag for the iframe when we edit the
	 *
	 * @param string $frame_uri
	 *
	 * @return string
	 */
	public static function tcb_frame_request_uri( $frame_uri ) {
		if ( Main::is_edit_screen() ) {
			$frame_uri = add_query_arg( array( Main::EDIT_FLAG => 1 ), $frame_uri );
		}

		return $frame_uri;
	}

	/**
	 * Add edit flag for the preview link
	 *
	 * @param $args
	 * @param $post_id
	 *
	 * @return mixed
	 */
	public static function tcb_editor_preview_link_query_args( $args, $post_id ) {
		if ( get_post_type( $post_id ) === Post_Type::NAME ) {
			$args[ Main::EDIT_FLAG ] = 1;
		}

		return $args;
	}

	/**
	 * Add Login elements to the editor
	 *
	 * @param array $instances
	 *
	 * @return array
	 */
	public static function tcb_element_instances( $instances ) {
		if ( Main::is_edit_screen() || wp_doing_ajax() ) {
			$instances = array_merge( $instances, Main::$elements );
		}

		return $instances;
	}

	/**
	 * Load the main js only after our files are loaded
	 *
	 * @param array $dependencies
	 *
	 * @return array
	 */
	public static function tve_main_js_dependencies( $dependencies ) {
		if ( Main::is_edit_screen() ) {
			$dependencies[] = 'tvd-login-editor';
		}

		return $dependencies;
	}

	/**
	 * Include reset modal in the editor
	 *
	 * @param $files
	 *
	 * @return mixed
	 */
	public static function tcb_modal_templates( $files ) {
		if ( Main::is_edit_screen() ) {
			$files[] = dirname( __DIR__ ) . '/views/reset-login-design-modal.php';
		}

		return $files;
	}


	/**
	 * Remove the login post from the TCB post types
	 *
	 * @param $ignored_post_types
	 *
	 * @return array
	 */
	public static function thrive_ignored_post_types( $ignored_post_types ) {
		$ignored_post_types[] = Post_Type::NAME;

		return $ignored_post_types;
	}

	/**
	 * Do not allow some TL form types to be show in the wizard and branding iframe
	 *
	 * @param array  $items
	 * @param string $product
	 *
	 * @return array
	 */
	public static function intrusive_forms( $items, $product ) {
		if ( Main::is_edit_screen() ) {
			$items = array();
		}

		return $items;
	}

	/**
	 * Fix logo url when getting cloud templates
	 *
	 * @param $data
	 * @param $meta
	 *
	 * @return mixed
	 */
	public static function tcb_alter_cloud_template_meta( $data, $meta ) {
		if ( $data['type'] === 'tvd_login_screen' ) {
			$data['head_css'] = Main::update_logo_in_content( $data['head_css'] );

			preg_match( '/http[^"]*/m', $data['head_css'], $urls );

			if ( ! empty( $urls ) && function_exists( 'media_sideload_image' ) ) {
				$home_url = home_url();

				foreach ( $urls as $url ) {
					if ( strpos( $url, $home_url ) === false ) {
						try {
							$post_id = ! empty( $_REQUEST['post_id'] ) ? absint( $_REQUEST['post_id'] ) : 0;
							$new_url = media_sideload_image( $url, $post_id, null, 'src' );

							$data['head_css'] = str_replace( $url, $new_url, $data['head_css'] );
						} catch ( \Exception $e ) {
							/* for some reason the replace could not be done */
						}
					}
				}
			}
		}

		return $data;
	}

	/**
	 * Add our option to the allowed list
	 *
	 * @param $allowed
	 *
	 * @return mixed
	 */
	public static function tcb_allowed_ajax_options( $allowed ) {
		$allowed[] = 'tvd_login_screen_has_template';

		return $allowed;
	}

	/**
	 * Post visibility options blacklist
	 *
	 * @param $post_types
	 *
	 * @return array
	 */
	public static function tve_dash_post_visibility_options( $post_types ) {
		$post_types = array_merge( $post_types, array(
			Post_Type::NAME,
		) );

		return $post_types;
	}
}
