<?php
/**
 * FileName  class-thrive-comment-settings.php.
 *
 * @project  : thrive-comments
 * @developer: Dragos Petcu
 * @company  : BitStone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden.
}

/**
 * Class Thrive_Comments_Settings
 *
 * Use admin settings in frontend
 */
class Thrive_Comments_Settings {

	/**
	 * The single instance of the class.
	 *
	 * @var Thrive_Comments_Settings singleton instance.
	 */
	protected static $_instance = null;

	/**
	 * Main Thrive Comments Settings Instance.
	 * Ensures only one instance of Thrive Comments Settings is loaded or can be loaded.
	 *
	 * @return Thrive_Comments_Settings
	 */
	public static function instance() {
		if ( empty( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Get all settings based on their default values
	 *
	 * @return array
	 */
	public function tcm_get_settings() {
		$settings = array();
		$defaults = Thrive_Comments_Constants::get_defaults();

		foreach ( $defaults as $key => $value ) {
			$settings[ $key ] = get_option( $key, $value );
		}

		return $settings;
	}

	/**
	 * Returns a specific setting
	 *
	 * @param string $name Setting name.
	 *
	 * @return mixed
	 */
	public function tcm_get_setting_by_name( $name ) {

		$defaults                   = Thrive_Comments_Constants::get_defaults();
		$defaults['tcm_conversion'] = Thrive_Comments_Constants::$_tcm_conversion_defaults;

		$settings_value = ( isset( $defaults[ $name ] ) ) ? get_option( $name, $defaults[ $name ] ) : get_option( $name );

		if ( empty( $settings_value ) ) {
			return false;
		}

		return $settings_value;
	}

	/**
	 * See if the comments need to be closed for a certain post or page
	 *
	 * @param int $post_id The post or page the user is accessing.
	 *
	 * @return bool
	 */
	public function close_comments( $post_id ) {

		// If on the post/page the comments are not allowed, return true.
		if ( ! comments_open( $post_id ) ) {
			// Special case for landing pages, we do not take into consideration if the comments are allowed for the certain page.
			if ( function_exists( 'tve_post_is_landing_page' ) && tve_post_is_landing_page( $post_id ) ) {
				return false;
			}

			return true;
		}

		$automatically_close_comments = $this->tcm_get_setting_by_name( 'close_comments_for_old_posts' );
		$days_old                     = $this->tcm_get_setting_by_name( 'close_comments_days_old' );
		$close_comments               = false;

		if ( $automatically_close_comments ) {

			$post       = get_post( $post_id, ARRAY_A );
			$date_added = strtotime( $post['post_date'] );
			$date_now   = strtotime( date( 'Y-m-d H:i:s' ) );
			$diff       = intval( ( $date_now - $date_added ) / 86400 );

			if ( $diff > intval( $days_old ) ) {
				$close_comments = true;
			}
		}

		return apply_filters( 'tcm_close_comments', $close_comments );
	}

	/**
	 * Get how the comments are sorted in page
	 *
	 * @return array This contains the field and the order for sorting.
	 */
	public function get_comment_sorting() {

		$sorting      = array();
		$default_sort = $this->tcm_get_setting_by_name( 'comment_order' );

		switch ( $default_sort ) {
			case 'desc':
			{
				$sorting = array(
					'sort_name'  => 'newest',
					'sort_field' => 'comment_ID',
					'order'      => - 1,
				);
				break;
			}
			case 'asc':
			{
				$sorting = array(
					'sort_name'  => 'oldest',
					'sort_field' => 'comment_ID',
					'order'      => 1,
				);
				break;
			}
			case 'top_rated':
			{
				$sorting = array(
					'sort_name'  => 'top_rated',
					'sort_field' => 'comment_karma',
					'order'      => - 1,
				);
				break;
			}
		}

		return $sorting;
	}
}

/**
 *  Main instance of Thrive Comments Settings
 *
 * @return Thrive_Comments_Settings
 */
function tcms() {
	return Thrive_Comments_Settings::instance();
}

tcms();
