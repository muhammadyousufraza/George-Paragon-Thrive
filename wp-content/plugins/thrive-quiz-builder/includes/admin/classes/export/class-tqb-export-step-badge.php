<?php

/**
 * Class TQB_Export_Step_Badge
 * - prepares quiz badge
 */
class TQB_Export_Step_Badge extends TQB_Export_Step_Abstract {

	protected $_name = 'badge';

	/**
	 * @var TIE_Image
	 */
	private $_image;

	/**
	 * Prepare instance data
	 *
	 * @return bool|true
	 * @throws Exception
	 */
	protected function _prepare_data() {

		if ( true !== $this->_init_image() ) {
			return true;
		}

		$this->_copy();

		$_data = $this->_image->to_array();

		unset( $_data['ID'] );

		$badge_url   = $_data['settings']['background_image']['url'];
		$need_export = ! strpos( $badge_url, 'thrive-quiz-builder/image-editor/includes/templates/images' );

		/**
		 * No need to export default images used by TQB, those already uploaded
		 */
		if ( true === $need_export && true === WP_Filesystem() ) {
			/** @var WP_Filesystem_Direct $wp_filesystem */
			global $wp_filesystem, $wpdb;

			$cache_key = md5( $badge_url );
			$img_id    = wp_cache_get( $cache_key, 'TQB' );
			if ( ! $img_id ) {
				$img_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE guid=%s", $badge_url ) );
				wp_cache_set( $cache_key, $img_id, 'TQB', DAY_IN_SECONDS );
			}
			if ( $img_id ) {
				$img_path = realpath( get_attached_file( $img_id ) );

				$wp_filesystem->copy( $img_path, $this->get_path() . '/' . basename( $badge_url ) );
			}
		}

		$site_url    = get_site_url();
		$placeholder = TQB_Export_Step_Structure::URL_PLACEHOLDER;

		foreach ( array( 'image_url', 'editor_url', 'guid' ) as $key ) {
			$_data[ $key ] = str_replace( $site_url, $placeholder, $_data[ $key ] );
		}

		$_data['settings']['background_image']['url'] = str_replace( $site_url, $placeholder, $badge_url );

		$_data['content']     = str_replace( $site_url, $placeholder, $this->_image->get_content() );
		$_data['html_canvas'] = str_replace( $site_url, $placeholder, $this->_image->get_html_canvas_content() );

		$this->data['post'] = $_data;

		return true;
	}

	/**
	 * Set $_image prop
	 *
	 * @return bool
	 */
	private function _init_image() {
		$image = tie_get_images( $this->quiz->ID );
		$image = isset( $image[0] ) ? $image[0] : false;

		if ( false !== $image ) {
			$this->_image = new TIE_Image( $image );

			return true;
		}

		return false;
	}

	/**
	 * Copy the image into export folder
	 *
	 * @return bool
	 * @throws Exception
	 */
	private function _copy() {

		if ( true !== $this->_image instanceof TIE_Image || empty( $this->_image->get_image_url() ) ) {
			return false;
		}

		$image_name  = $this->_image->get_post_parent_id() . '.' . TIE_Image::TYPE;
		$file_path   = $this->get_upload_dir()['basedir'] . '/' . Thrive_Quiz_Builder::UPLOAD_DIR_CUSTOM_FOLDER . '/' . $image_name;
		$destination = trailingslashit( $this->get_path() ) . '/' . $image_name;

		WP_Filesystem();

		/** @var WP_Filesystem_Direct $wp_filesystem */
		global $wp_filesystem;

		return $wp_filesystem->copy( $file_path, $destination );
	}
}
