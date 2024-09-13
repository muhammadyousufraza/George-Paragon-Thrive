<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\Notifications;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Hooks {
	public static function add_actions() {
		if ( Main::is_edit_screen() ) {
			add_action( 'tcb_main_frame_enqueue', [ __CLASS__, 'main_frame_enqueue' ] );

			add_action( 'wp_loaded', [ __CLASS__, 'enqueue_scripts' ] );

			add_action( 'tcb_output_components', [ __CLASS__, 'tcb_output_components' ] );
		}
		add_action( 'tcb_editor_enqueue_scripts', [ __CLASS__, 'editor_enqueue' ] );

		add_action( 'wp_footer', [ __CLASS__, 'insert_notification_element' ] );

		add_action( 'wp_ajax_notification_update_template', [ __CLASS__, 'update_template' ] );

		add_action( 'admin_footer', [ __CLASS__, 'add_global_variables' ] );
	}

	public static function add_filters() {
		add_filter( 'tcb_element_instances', [ __CLASS__, 'tcb_element_instances' ] );

		add_filter( 'tcb_has_templates_tab', [ __CLASS__, 'has_templates' ] );

		add_filter( 'tve_main_js_dependencies', [ __CLASS__, 'tve_main_js_dependencies' ] );
	}

	/* ###################################### ACTIONS ###################################### */

	/**
	 * Enqueue scripts in the main frame
	 */
	public static function main_frame_enqueue() {
		tve_dash_enqueue_script( 'tve-notifications-main', tve_editor_js( '/notifications-main.min.js' ), [ 'jquery' ] );
	}

	/**
	 * Used to localize the elements (wrapper and message)
	 */
	public static function enqueue_scripts() {
		static::main_frame_enqueue();

		$elements = [];
		foreach ( Main::$elements as $element ) {
			$elements[] = $element->tag();
		}

		wp_localize_script( 'tve-notifications-main', 'tve_notification', [
			'elements' => $elements,
		] );
	}

	/**
	 * Include the notification controls component
	 */
	public static function tcb_output_components() {
		$path  = TVE_TCB_ROOT_PATH . 'inc/views/notifications/components/';
		$files = array_diff( scandir( $path ), [ '.', '..' ] );

		foreach ( $files as $file ) {
			include $path . $file;
		}
	}

	/**
	 * Enqueue scripts in the editor
	 */
	public static function editor_enqueue() {
		if ( Main::is_edit_screen() ) {
			tve_dash_enqueue_script( 'tve-notifications-editor', tve_editor_js( '/notifications-editor.min.js' ), [ 'jquery' ] );
		}
	}

	/**
	 * Insert the notification in every page
	 */
	public static function insert_notification_element() {
		if ( ! ( Main::is_preview_screen() || Main::is_edit_screen() ) ) {
			echo Main::get_notification_content( true, '', false, false );
		}
	}

	/**
	 * Update notification template with the selected one
	 */
	public static function update_template() {
		$id = filter_input( INPUT_POST, 'post_id', FILTER_SANITIZE_NUMBER_INT );

		/* Un-set the previously set template */
		$posts = get_posts( [
			'post_type' => Post_Type::NAME,
			'fields'    => 'ids',
		] );

		if ( ! empty( $posts ) ) {
			foreach ( $posts as $key => $postId ) {
				update_post_meta( $postId, 'default', 0 );
			}
		}

		/* Set the selected template as default */
		update_post_meta( $id, 'default', 1 );
	}

	/**
	 * Only load global variables in the Global Elements tab inside the Dashboard
	 */
	public static function add_global_variables() {
		if ( tve_get_current_screen_key() === 'admin_page_tcb_admin_dashboard' ) {
			tve_load_global_variables();
		}
	}


	/* ###################################### FILTERS ###################################### */

	/**
	 * Add Notifications elements to the editor
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
	 * Remove cloud templates icon from the right sidebar
	 *
	 * @param bool $has_templates
	 *
	 * @return false
	 */
	public static function has_templates( $has_templates ) {
		if ( Post_Type::is_notification() ) {
			$has_templates = false;
		}

		return $has_templates;
	}

	/**
	 * Add notification dependency
	 *
	 * @param $dependencies
	 *
	 * @return mixed
	 */
	public static function tve_main_js_dependencies( $dependencies ) {
		if ( Main::is_edit_screen() ) {
			$dependencies[] = 'tve-notifications-main';
		}

		return $dependencies;
	}
}
