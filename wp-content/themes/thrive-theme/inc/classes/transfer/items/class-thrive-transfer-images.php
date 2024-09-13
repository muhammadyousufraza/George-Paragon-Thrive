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
 * Class Thrive_Transfer_Images
 */
class Thrive_Transfer_Images extends Thrive_Transfer_Base {

	/**
	 * File in which the images are saved
	 *
	 * @var string
	 */
	public static $file = 'images.json';

	/**
	 * Element key in the archive
	 *
	 * @var string
	 */
	protected $tag = 'images';

	/**
	 * Images map used at import in order to replace the identifiers in html
	 *
	 * @var array
	 */
	private $map = [];

	/**
	 * Read images from the content
	 * //TODO do not export all the image sizes
	 *
	 * @param string $content
	 *
	 * @return $this
	 */
	public function read( &$content ) {
		$site_url = str_replace( [ 'http://', 'https://', '//' ], '', site_url() );

		/* regular expression that finds image links that point to the current server */
		$image_regexp = '#((http(s)?:)?((\\\)?/(\\\)?/)?)(' . preg_quote( $site_url, '#' ) . ')([^ "\']+?)(\.[png|gif|jpg|jpeg]+)#is';

		if ( preg_match_all( $image_regexp, $content, $matches ) ) {

			foreach ( $matches[8] as $index => $src ) {
				/* site_url + image + extension */
				$img_src    = $matches[7][ $index ] . $src . $matches[9][ $index ];
				$no_qstring = explode( '?', $img_src );
				$img_src    = $no_qstring[0];

				$server_path = stripslashes( $src . $matches[9][ $index ] );

				$full_image_path = untrailingslashit( ABSPATH ) . $server_path;
				if ( ! file_exists( $full_image_path ) ) {
					continue;
				}

				$replacement                 = md5_file( $full_image_path );
				$this->items[ $replacement ] = [
					'name' => basename( $img_src ),
					'path' => $full_image_path,
				];

				$content = str_replace( $matches[0][ $index ], '{{img=' . $replacement . '}}', $content );
			}
		}

		return $this;
	}

	/**
	 * Add a section/template thumbnail to the images data
	 *
	 * @param int    $id
	 * @param string $folder - right now, this can be the theme folder or the symbols folder
	 *
	 * @return $this
	 */
	public function export_thumbnail( $id, $folder = THEME_UPLOADS_PREVIEW_SUB_DIR ) {
		$thumb_data = TCB_Utils::get_thumb_data( $id, $folder );

		if ( ! empty( $thumb_data['url'] ) ) {
			/* get the full path */
			$full_path = TCB_Utils::get_uploads_path( $folder . '/' . $id . '.png' );

			$this->items[ md5( $id ) ] = [
				'name'          => $id . '.png',
				'path'          => $full_path,
				'folder'        => $folder,
				'h'             => $thumb_data['h'],
				'w'             => $thumb_data['w'],
				/* flag set to 1 in order to avoid saving this as an attachment */
				'custom_subdir' => 1,
			];
		}

		return $this;
	}

	/**
	 * Add data to the transfer object
	 */
	public function add() {
		//First take the existent items and than do the merge with the new ones
		$previous_items = empty( $this->archive_data['images'] ) ? [] : $this->archive_data['images'];

		$this->archive_data['images'] = array_merge( $previous_items, $this->items );
	}

	/**
	 * Add images directly to the zip archive, in the images/ folder
	 *
	 * @param array      $images
	 * @param ZipArchive $zip
	 */
	public static function add_to_archive( $images, $zip ) {
		$image_data = [];

		$zip->addEmptyDir( 'images' );
		foreach ( $images as $key => $info ) {
			$zip->addFile( $info['path'], 'images/' . $key . '.img_file' );
			unset( $info['path'] );
			$image_data[ $key ] = $info;
		}

		$zip->addFromString( static::$file, json_encode( $image_data ) );
	}

	/**
	 * Check if the archive has the images folder
	 *
	 * @return $this|Thrive_Transfer_Base
	 * @throws Exception
	 */
	public function validate() {
		$this->data = json_decode( $this->controller->zip->getFromName( static::$file ), true );

		if ( ! empty( $this->data ) ) {
			foreach ( $this->data as $hash => $image ) {
				/* avoid throwing exceptions for thumbnails */
				if ( empty( $image['custom_subdir'] ) && $this->controller->zip->locateName( 'images/' . pathinfo( $hash, PATHINFO_FILENAME ) . '.img_file' ) === false ) {
					throw new Exception( __( 'Invalid archive,' . $image['name'] . '::' . $hash . '::' . ' image file not found!', 'thrive-theme' ) );
				}
			}
		}

		return $this;
	}

	/**
	 * Import the new images from the archive
	 *
	 * @throws Exception
	 */
	public function import() {
		foreach ( $this->data as $hash => $image ) {
			if ( ! $this->already_saved( $hash ) ) {
				$this->map[ $hash ] = $this->upload_image( $image, $this->controller->zip->getFromName( 'images/' . $hash . '.img_file' ) );
			}
		}

		$this->archive_data[ $this->tag ] = $this->map;
	}

	/**
	 * Upload an image to the base uploads folder / to a custom sub-directory.
	 *
	 * @param $image
	 * @param $image_content
	 *
	 * @return string|array
	 * @throws Exception
	 */
	private function upload_image( $image, $image_content ) {
		$image_name = $image['name'];

		/* 'not_attachment' is kept for backwards compat for old exports, the new flag is 'custom_subdir'  */
		if ( empty( $image['not_attachment'] ) && empty( $image['custom_subdir'] ) ) {
			$upload_dir = [ 'Thrive_Utils', 'get_image_upload_dir' ];
		} else {
			/* different callbacks depending on the folder */
			$upload_dir = $image['folder'] === THEME_UPLOADS_PREVIEW_SUB_DIR ?
				[ 'Thrive_Utils', 'get_preview_upload_dir_callback' ] :
				[ 'TCB_Symbol_Element_Abstract', 'upload_dir' ];
		}

		/* add and remove the 'upload_dir' filter in order to overwrite the default upload directory */
		add_filter( 'upload_dir', $upload_dir );

		$upload = wp_upload_bits( $image_name, null, $image_content );

		remove_filter( 'upload_dir', $upload_dir );

		if ( ! empty( $upload['error'] ) ) {
			throw new Exception( sprintf( __( 'Could not upload the image: %s', 'thrive-theme' ), $image_name ) );
		}

		if ( empty( $image['not_attachment'] ) && empty( $image['custom_subdir'] ) ) {
			$data = [
				'url' => $upload['url'],
			];
		} else {
			$data = [
				'url'  => $upload['url'],
				'file' => $upload['file'],
				'h'    => $image['h'],
				'w'    => $image['w'],
			];
		}

		return $data;
	}
}
