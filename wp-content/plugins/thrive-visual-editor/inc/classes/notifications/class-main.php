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

class Main {

	const EDIT_FLAG = 'tve-notifications',
		OPTION_NAME = 'tve_notifications_post_id';

	public static $elements = [];

	public static function init() {

		static::includes();
		static::register_elements();

		Hooks::add_actions();
		Hooks::add_filters();

		Post_Type::init();
	}


	public static function includes() {
		require_once __DIR__ . '/class-post-type.php';
		require_once __DIR__ . '/class-hooks.php';
	}

	/**
	 * Load elements needed for the notifications editor
	 */
	public static function register_elements() {
		$path  = __DIR__ . '/elements';
		$items = array_diff( scandir( $path ), [ '.', '..' ] );

		static::$elements = [];

		foreach ( $items as $item ) {
			$item_path = $path . '/' . $item;

			/* if the item is a file, include it */
			if ( is_file( $item_path ) && substr( $item_path, - 3 ) === 'php' ) {
				$element = include $item_path;
				if ( ! empty( $element ) ) {
					static::$elements[ $element->tag() ] = $element;
				}
			}
		}
	}

	public static function title() {
		return __( 'Notifications', 'thrive-cb' );
	}

	/**
	 * Check if current page is the edit page for the notifications
	 *
	 * @return bool
	 */
	public static function is_edit_screen() {
		$is_edit_screen = isset( $_GET[ Main::EDIT_FLAG ] ) || Post_Type::is_notification();

		return apply_filters( 'tcb_is_notifications_edit_screen', $is_edit_screen );
	}

	/**
	 * Check if current page is the preview page for the notifications
	 *
	 * @return bool
	 */
	public static function is_preview_screen() {
		return isset( $_GET['notification-state'] );
	}

	public static function get_localized_data() {
		return [
			'notifications_edit_url'       => Post_Type::instance()->get_edit_url(),
			'notifications_template'       => static::get_notification_template_id(),
			'notifications_custom_content' => static::get_custom_content(),
		];
	}

	public static function get_notification_post_id() {
		return get_option( static::OPTION_NAME, 0 );
	}

	/**
	 * Get notification content
	 *
	 * @param $should_hide - should be hidden/displayed
	 * @param $state       - what state should be displayed
	 * @param $is_preview  - check if this is the dashboard preview
	 *
	 * @return string
	 */
	public static function get_notification_content( $should_hide, $state, $is_preview, $display_custom ) {
		if ( ! $display_custom && static::get_notification_template_id() === 0 ) {
			$post_content = static::get_default_content();
		} else {
			$post_content = static::get_custom_content();

			if ( empty( $post_content ) ) {
				if ( static::is_preview_screen() || static::is_edit_screen() ) {
					$post_content = static::get_default_content( 'custom' );
				} else {
					$post_content = static::get_default_content();
				}
			}

			/* backwards compatibility for animated class - remove this in a few releases */
			$post_content = str_replace( 'notifications-content-wrapper animated', 'notifications-content-wrapper', $post_content );

			/* Hide the notification element */
			if ( $should_hide && ( ! static::is_edit_screen() || static::is_preview_screen() ) ) {
				$post_content = str_replace( 'notifications-content-wrapper', 'notifications-content-wrapper tcb-permanently-hidden', $post_content );
			}

			/* Change the state to the desired one */
			if ( $state ) {
				$post_content = preg_replace( '/data-state="[a-z]*"/', 'data-state="' . $state . '"', $post_content );
			}

			/* For the dashboard preview, remove the position attribute */
			if ( $is_preview ) {
				$post_content = preg_replace( '/data-position="[a-z]*-[a-z]*"/', '', $post_content );
			}
		}

		tve_parse_events( $post_content );

		$post_content = do_shortcode( $post_content );

		$css = static::get_notification_meta_style( true, $is_preview );

		return $css . $post_content;
	}

	/**
	 * Return default notification content
	 *
	 * @param string $type
	 *
	 * @return false|string
	 */
	public static function get_default_content( $type = '' ) {
		ob_start();

		if ( ! empty( $type ) ) {
			$type = '-' . $type;
		}

		include TVE_TCB_ROOT_PATH . 'inc/views/notifications/notification-default' . $type . '-content.php';

		return ob_get_clean();
	}

	public static function get_custom_content() {
		$post_id = static::get_notification_post_id();

		return get_post_meta( $post_id, 'tve_updated_post', true );
	}

	public static function is_default_design() {
		$post_id = static::get_notification_post_id();

		return get_post_meta( $post_id, 'default', true );
	}

	public static function get_notification_template_id() {
		$posts = get_posts( [
			'post_type'  => Post_Type::NAME,
			'meta_query' => [
				[
					'key'   => 'default',
					'value' => 1,
				],
			],
			'fields'     => 'ids',
		] );

		if ( ! empty( $posts ) ) {
			return $posts[0];
		}

		return 0;
	}

	/**
	 * Get the styling of the notification
	 */
	public static function get_notification_meta_style( $return = true, $is_preview = false ) {
		$post_id = Post_Type::instance()->get_id();
		$css     = '';
		if ( get_the_ID() !== $post_id ) {
			$lightspeed_css = \TCB\Lightspeed\Css::get_instance( $post_id );

			if ( $is_preview || $lightspeed_css->should_load_optimized_styles() ) {
				$css .= $lightspeed_css->get_optimized_styles();
			}

			$css .= sprintf( '<style type="text/css" id="tve_notification_styles">%s</style>', get_post_meta( $post_id, 'tve_custom_css', true ) );
		}
		if ( $return ) {
			return $css;
		}

		echo $css;
	}

	/**
	 * Get the default styling of the notification
	 *
	 * @return false|string
	 */
	public static function get_notification_default_style() {
		ob_start();
		include dirname( __DIR__ ) . '/../../editor/css/sass/elements/_notification.scss';

		return ob_get_clean();
	}

	public static function get_default_notification_element() {
		$default_html_content = static::get_default_content( 'custom' );
		$default_css_content  = static::get_notification_default_style();

		return [ 'html' => $default_html_content, 'css' => $default_css_content ];
	}
}
