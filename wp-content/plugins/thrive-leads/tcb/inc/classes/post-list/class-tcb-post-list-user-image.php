<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Post_List_User_Image
 */
class TCB_Post_List_User_Image {
	const PLACEHOLDER_URL = 'editor/css/images/author_image.png';

	private static $instance;

	public $user_id;

	/**
	 * Singleton implementation for TCB_Post_List_User_Image
	 *
	 * @return TCB_Post_List_User_Image
	 */
	public static function get_instance( $user_id = null ) {
		if ( self::$instance === null ) {
			self::$instance = new self( $user_id );
		}

		return self::$instance;
	}

	public function __construct( $user_id = null ) {
		$this->user_id = empty( $user_id ) ? get_current_user_id() : $user_id;
	}

	/**
	 * Get the default user image url, or the user image url
	 *
	 * @return string
	 */
	public function get_default_url() {
		$url = static::get_placeholder_url();

		if ( ! empty( $this->user_id ) ) {

			$avatar_data = get_avatar_data( $this->user_id, [ 'size' => 256 ] );

			if ( ! empty( $avatar_data['url'] ) && ! is_wp_error( $avatar_data['url'] ) ) {
				$url = $avatar_data['url'];
			}

		}

		return $url;
	}


	public function get_user_name() {
		$name = '';

		if ( ! empty( $this->user_id ) ) {

			$user = get_userdata( $this->user_id );
			if ( $user && $user->data ) {
				$name = $user->data->display_name;
			}
		}

		return $name;
	}


	/**
	 * Returns post user avatar
	 *
	 * @return false|string
	 */
	public function user_avatar() {

		if ( empty( $this->user_id ) ) {
			$avatar_url = '';
		} else {
			$avatar = get_avatar( $this->user_id, 256 );
			preg_match( '/src=\'([^\']*)\'/m', $avatar, $matches );

			if ( empty( $matches[1] ) ) {
				$avatar_url = get_avatar_url( $this->user_id, [ 'size' => 256 ] );
			} else {
				$avatar_url = html_entity_decode( $matches[1] );
			}

			/* if we're in the editor, append a dynamic flag at the end so we can recognize that the URL is dynamic */
			if ( TCB_Utils::in_editor_render( true ) ) {
				$avatar_url = add_query_arg( [
					'dynamic_user' => 1,
				], $avatar_url );
			}
		}

		return $avatar_url;
	}

	/**
	 * Returns user placeholder URL
	 *
	 * @return false|string
	 */
	public static function get_placeholder_url() {

		return tve_editor_url( static::PLACEHOLDER_URL );
	}

}

/**
 * Returns the instance of the Custom Fields Shortcode Class
 *
 * @return TCB_Post_List_User_Image
 */
function tcb_dynamic_user_image_instance( $user_id ) {
	return TCB_Post_List_User_Image::get_instance( $user_id );
}
