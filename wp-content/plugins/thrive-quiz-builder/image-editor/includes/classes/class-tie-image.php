<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-image-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class TIE_Image {

	const TYPE = 'png';

	/**
	 * @var int
	 */
	private $post_id;

	/**
	 * @var WP_Post
	 */
	private $post;

	/**
	 * @var TIE_Image_Settings
	 */
	private $settings;

	/**
	 * @var int
	 */
	private $post_parent_id;

	/**
	 * @var WP_Post
	 */
	private $post_parent;

	/**
	 * TIE_Image constructor.
	 *
	 * @param WP_Post|int $post
	 */
	public function __construct( $post ) {

		if ( $post instanceof WP_Post ) {
			$this->post           = $post;
			$this->post_id        = $post->ID;
			$this->post_parent_id = $post->post_parent;
		} else {
			$this->post_id = intval( $post );
		}
	}

	/**
	 * @return null|WP_Post
	 */
	private function _get_post() {

		if ( ! ( $this->post instanceof WP_Post ) ) {
			$this->post = get_post( $this->post_id );
		}

		return $this->post;
	}

	/**
	 * @return int|null
	 */
	public function get_post_parent_id() {

		if ( empty( $this->post_parent_id ) ) {
			$post = $this->_get_post();

			$this->post_parent_id = empty( $post ) ? null : $post->post_parent;
		}

		return $this->post_parent_id;
	}

	/**
	 * @return null|WP_Post
	 */
	private function _get_post_parent() {

		if ( ! ( $this->post_parent instanceof WP_Post ) ) {
			$this->post_parent = get_post( $this->get_post_parent_id() );
		}

		return $this->post_parent;
	}

	public function save_content( $content ) {
		if ( ! $this->_can_save() ) {
			return false;
		}

		update_post_meta( $this->post_id, 'tie_image_content', $content );

		return true;
	}

	/**
	 * Saves the html canvas in meta
	 *
	 * @param $html_canvas
	 *
	 * @return bool
	 */
	public function save_html_canvas( $html_canvas ) {
		if ( ! $this->_can_save() ) {
			return false;
		}

		$html_canvas    = preg_replace( '@\>\s{1,}\<@', '><', $html_canvas );// remove spaces between tags
		$replace_params = array(
			'tie-canvas-overlay',
			'tie-canvas',
			'ui-droppable',
			'tie-element-actions',
			'tie-element',
			'ui-draggable',
			'tie-editable',
			'mce-content-body',
			'mce_0',
		);
		$html_canvas    = str_replace( $replace_params, '', $html_canvas );

		update_post_meta( $this->post_id, 'tie_html_canvas_content', $html_canvas );

		return true;
	}

	/**
	 * Returns the image URL stored in WordPress uploads thrive-quiz-builder folder
	 *
	 * @return null|string
	 */
	public function get_image_url() {

		$upload_dir = wp_upload_dir();
		$file_path  = $upload_dir['basedir'] . '/' . Thrive_Quiz_Builder::UPLOAD_DIR_CUSTOM_FOLDER . '/' . $this->get_post_parent_id() . '.' . self::TYPE;

		if ( is_file( $file_path ) ) {
			$file_url = $upload_dir['baseurl'] . '/' . Thrive_Quiz_Builder::UPLOAD_DIR_CUSTOM_FOLDER . '/' . $this->get_post_parent_id() . '.' . self::TYPE . '?' . rand();

			return $file_url;
		}

		return null;
	}

	public function filter_upload_location( $upload ) {

		$subdir = '/thrive-quiz-builder';

		$upload['path']   = $upload['basedir'] . $subdir;
		$upload['url']    = $upload['baseurl'] . $subdir;
		$upload['subdir'] = $subdir;

		return $upload;
	}

	/**
	 * Save badge on disk
	 *
	 * @param $uploaded_file
	 *
	 * @return string|null
	 */
	public function save_file( &$uploaded_file ) {

		add_filter( 'upload_dir', array( $this, 'filter_upload_location' ) );

		if ( ! function_exists( 'wp_handle_upload' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
		}

		$upload_overrides = array(
			'action'                   => 'tie_save_image_file',
			'unique_filename_callback' => array( $this, 'filter_filename' ),
		);

		$file_exists = (bool) $this->get_image_url();

		$moved_file = wp_handle_upload( $uploaded_file, $upload_overrides );

		$url = ! isset( $moved_file['error'] ) ? $moved_file['url'] : null;

		remove_filter( 'upload_dir', array( $this, 'filter_upload_location' ) );

		if ( ! $file_exists ) {
			do_action( 'tqb_update_social_share_badge_url', $this->get_post_parent_id(), $url, null );
		}

		return $url;
	}

	public function filter_filename() {

		return $this->_filename();
	}

	private function _filename() {

		$filename = $this->_get_post_parent()->ID . '.' . self::TYPE;
		$filename = sanitize_file_name( $filename );

		return $filename;
	}

	public function get_content() {
		return get_post_meta( $this->post_id, 'tie_image_content', true );
	}

	public function get_html_canvas_content() {
		return get_post_meta( $this->post_id, 'tie_html_canvas_content', true );
	}

	/**
	 * @return TIE_Image_Settings
	 */
	public function get_settings() {
		if ( ! ( $this->settings instanceof TIE_Image_Settings ) ) {
			$this->settings = new TIE_Image_Settings( $this->post_id );
		}

		return $this->settings;
	}

	public function get_canvas_style() {
		$style = 'background-repeat: no-repeat;';
		if ( ! ( $this->get_settings() instanceof TIE_Image_Settings ) ) {
			return $style;
		}
		$style .= "width: {$this->get_settings()->get_data('size/width')}px;";
		$style .= "height: {$this->get_settings()->get_data('size/height')}px;";

		if ( $this->get_settings()->get_data( 'background_image/url' ) !== 'none' ) {
			$style .= "background-image: url('{$this->get_settings()->get_data('background_image/url')}');";
		}

		$style .= "background-size: {$this->get_settings()->get_data('background_image/size')};";
		$style .= "background-position: {$this->get_settings()->get_data('background_image/position')};";

		return $style;
	}

	public function get_overlay_style() {
		$style = 'height: 100%;';
		if ( ! ( $this->get_settings() instanceof TIE_Image_Settings ) ) {
			return $style;
		}
		$_opacity = (int) $this->get_settings()->get_data( 'overlay/opacity' );
		if ( $_opacity > 1 ) {
			$_opacity /= 100;
		}
		$style .= "background-color: {$this->get_settings()->get_data('overlay/bg_color')};";
		$style .= "opacity: {$_opacity};";

		return $style;
	}

	public function print_fonts() {
		$fonts = $this->get_settings()->get_data( 'fonts' );

		if ( ! empty( $fonts ) ) {
			foreach ( $fonts as $font_name => $font_url ) {
				printf( '<link rel="stylesheet" class="tie-loaded-google-fonts" data-family="%s" type="text/css" href="%s">' . "\n", esc_attr( $font_name ), esc_url( $font_url ) );
			}
		}
	}

	private function _can_save() {
		return is_numeric( $this->post_id ) && $this->post_id > 0;
	}

	/**
	 * @return array
	 */
	public function to_array() {

		return (array) $this->_get_post();
	}
}
