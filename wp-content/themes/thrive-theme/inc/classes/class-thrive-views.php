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
 * Class Thrive_Views
 */
class Thrive_Views {
	/**
	 * Render a notice with a link towards the editor in case the editor is enabled and we're on the WP Post page
	 */
	public static function no_template_settings_notice() {
		include THEME_PATH . '/inc/templates/admin/no-template-settings-notice.php';
	}

	/**
	 * Render the meta box for singular pages to select a specific thrive template for display
	 */
	public static function template_meta_box() {
		include THEME_PATH . '/inc/templates/admin/templates-meta-box.php';
	}

	/**
	 * Render the meta box for singular posts visibility.
	 */
	public static function visibility_meta_box() {
		include THEME_PATH . '/inc/templates/admin/visibility-meta-box.php';
	}

	/**
	 * Render the post format settings.
	 */
	public static function post_format_options() {
		include THEME_PATH . '/inc/templates/admin/post-format-metabox.php';
	}

	/**
	 * Render SVG icon for dashboard
	 *
	 * @param string $icon
	 * @param string $class
	 * @param string $namespace
	 * @param bool   $return
	 *
	 * @return string
	 */
	public static function svg_icon( $icon, $class = '', $namespace = 'ttd-', $return = false ) {
		if ( empty( $class ) ) {
			$class = $namespace . $icon;
		}

		$class .= ' ' . $namespace . 'svg-icon';

		$html = TCB_Utils::wrap_content( '<use xlink:href="#' . $namespace . $icon . '"></use>', 'svg', '', $class );

		if ( $return ) {
			return $html;
		}

		echo $html;
	}

	/**
	 * Displays the fields for adding social page URLs. Gets current URLs from the user meta.
	 *
	 * @param $user
	 */
	public static function social_fields_display( $user ) {
		require_once THEME_PATH . '/inc/templates/admin/user-social-url.php';
	}

	/**
	 * Build the title for the archive description element, for authors without posts.
	 *
	 * @param $user_id
	 *
	 * @return string
	 */
	public static function get_archive_description_title( $user_id ) {
		$display_name = get_the_author_meta( 'display_name', $user_id );

		$title = TCB_Utils::wrap_content( $display_name, 'span', '', 'vcard' );
		$title = 'Author: ' . $title;
		$title = TCB_Utils::wrap_content( $title, 'h1', '', 'page-title' );

		return $title;
	}

	/**
	 * Build the text for the archive description element, for authors without posts.
	 *
	 * @param $user_id
	 *
	 * @return string
	 */
	public static function get_archive_description_text( $user_id ) {
		$bio = get_the_author_meta( 'description', $user_id );
		$bio = TCB_Utils::wrap_content( $bio, 'div', '', 'archive-description-text' );

		return $bio;
	}
}
