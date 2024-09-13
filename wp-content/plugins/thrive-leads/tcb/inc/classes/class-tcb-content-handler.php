<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
if ( ! class_exists( 'TCB_Landing_Page_Cloud_Templates_Api' ) ) {
	require_once TVE_TCB_ROOT_PATH . 'landing-page/inc/TCB_Landing_Page_Transfer.php';
}

class TCB_Content_Handler extends TCB_Landing_Page_Transfer {
	/**
	 * Zip data
	 *
	 * @var string
	 */
	protected $cfg_name = 'tve_content.json';
	protected $html_file_name = 'tve_content.html';
	protected $archive_prefix = 'tve-content-';
	protected $thumbnail_prefix = 'content-';
	/**
	 * hold a copy of the config array for the import process
	 *
	 * @var array
	 */
	private $import_config = [];
	/**
	 * holds the current WP Page (or landing page)
	 *
	 * @var WP_Post
	 */
	private $current_page = null;
	/**
	 * Whether or not LP vars should be replaced by their values on export
	 */
	protected $allow_lp_vars = true;

	/**
	 * Whether or not we expect a LP to be imported
	 *
	 * @return bool
	 */
	protected function should_check_lp_page() {
		return false;
	}

	public function get_cfg_name() {
		return $this->cfg_name;
	}

	public function get_html_file_name() {
		return $this->html_file_name;
	}

	public function import( $file, $page_id ) {
		$this->importValidateFile( $file );

		$zip = new ZipArchive();
		if ( $zip->open( $file ) !== true ) {
			throw new Exception( __( 'Could not open the archive file', 'thrive-cb' ) );
		}
		/* 1. read config & validate */
		$config              = $this->importReadConfig( $zip );
		$this->import_config = $config;
		$this->current_page  = get_post( $page_id );

		/* 2. import all the images (add them as attachments) and store the links in the config array */
		$this->importImages( $config, $zip );

		/* 3. import all lightboxes (create new posts with type tcb_lightbox) */
		$lightbox_id_map = $this->importLightboxes( $config );

		/* 4. replace images from config*/
		$this->importParseImageLinks( $config );
		/* 5. get content*/
		$page_content = $zip->getFromName( $this->html_file_name );
		/* 6. replace images & lightboxes from content*/
		$this->importParseImageLinks( $page_content );

		$this->importReplaceLightboxIds( $page_content, $lightbox_id_map );

		$this->import_symbols( $config );

		if ( ! empty( $config['lightbox'] ) ) {
			foreach ( $config['lightbox'] as $old_id => $data ) {
				$lb_content = $zip->getFromName( 'lightboxes/' . $old_id . '.html' );
				$this->importReplaceLightboxIds( $lb_content, $lightbox_id_map );
				$this->importParseImageLinks( $lb_content );
				update_post_meta( $lightbox_id_map[ $old_id ], 'tve_updated_post', $lb_content );
			}
		}
		$template_name            = basename( $file, '.zip' );
		$page_content             = $this->replace_content_symbols_on_insert( $page_content );
		$config['tve_custom_css'] = $this->replace_content_symbols_on_insert( $config['tve_custom_css'] );

		/**
		 * Change imported content name before saving as template
		 *
		 */
		$template_name = apply_filters( 'tve_imported_content_name', $template_name );

		$preview_data_url  = $this->importThumbnail( $config, $zip );
		$preview_data_path = str_replace( wp_upload_dir()['baseurl'], wp_upload_dir()['basedir'], $preview_data_url );

		$new_template_data = [
			'name'        => $template_name,
			'content'     => $page_content,
			'type'        => '',
			'thumb'       => '',
			'id_category' => \TCB\UserTemplates\Category::PAGE_TEMPLATE_IDENTIFIER,
			'css'         => $config['tve_user_custom_css'],
			'media_css'   => $config['tve_custom_css'],
		];

		$new_template_data = tve_update_image_size( $preview_data_path, $new_template_data, $preview_data_url );

		$template_id = TCB\UserTemplates\Template::insert( $new_template_data );

		update_post_meta( $template_id, 'tve_kit_imported', 1 );

		return [
			'content'     => $page_content,
			'inline_css'  => $config['tve_custom_css'],
			'custom_css'  => $config['tve_user_custom_css'],
			'template_id' => $template_id,
		];
	}


	/**
	 * Get the generated preview of the LP(it's previously done on save)
	 *
	 * @param $page_id
	 * @param $zip
	 *
	 * @return false
	 */
	protected function exportGeneratedThumbnail( $page_id, $zip ) {
		$upload = tve_filter_content_preview_location( wp_upload_dir() );
		$path   = $upload['path'] . '/' . $this->thumbnail_prefix . $page_id . '.png';

		$zip->addEmptyDir( 'thumbnail' );

		if ( is_file( $path ) && $zip->addFile( $path, 'thumbnail/' . $this->thumbnail_prefix . $page_id . '.png' ) === false ) {
			$zip->deleteName( 'thumbnail' );

			return false;
		}

		return true;
	}
}
