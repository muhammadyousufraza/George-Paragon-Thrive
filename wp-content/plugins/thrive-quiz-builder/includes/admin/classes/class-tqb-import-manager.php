<?php

require_once __DIR__ . '/import/class-tqb-questions-collection.php';
require_once __DIR__ . '/import/class-tqb-import-structure-item.php';

class TQB_Import_Manager {

	/**
	 * @var array import file
	 */
	private $file;

	/**
	 * @var array
	 */
	private $wp_upload_dir;

	/**
	 * @var int $_quiz_id
	 */
	private $_quiz_id;

	/**
	 * Map for quiz results/categories
	 * - key is the old id
	 * - value is the new id
	 *
	 * @var array
	 */
	private $_results_map = array();

	/**
	 * TQB_Import_Manager constructor.
	 *
	 * @param array $uploaded_file
	 *
	 * @throws Exception
	 */
	public function __construct( $uploaded_file ) {

		$this->_validate_file( $uploaded_file );
		$this->_get_upload_dir();

		$this->file = $uploaded_file;
	}

	/**
	 * Process import
	 * - main entry point
	 *
	 * @throws Exception
	 */
	public function execute() {

		$this->_init_file_system();
		$this->_unzip_file();
		$this->_process_quiz_details();
		$this->_process_quiz_questions();
		$this->_process_quiz_structure();
		$this->_process_quiz_badge();
		$this->_process_quiz_resultlinks();
		$this->_clean();

		return $this->_quiz_id;
	}

	/**
	 * Unzips file in imports path
	 *
	 * @throws Exception
	 */
	private function _unzip_file() {

		$old_umask = umask( 0 );

		/** @var $wp_filesystem WP_Filesystem_Direct */
		global $wp_filesystem;

		if ( $wp_filesystem->errors instanceof WP_Error && ! $wp_filesystem->connect() ) {
			throw new Exception( $wp_filesystem->errors->get_error_message() );
		}

		if ( ! $wp_filesystem->is_dir( $this->_get_imports_path() ) ) {
			wp_mkdir_p( $this->_get_imports_path() );
		}

		$result = unzip_file( $this->file['tmp_name'], $this->_get_imports_path() );

		if ( $result instanceof WP_Error ) {
			umask( $old_umask );
			throw new Exception( __( 'Could not extract the archive file', 'thrive-quiz-builder' ) );
		}

		$structure = trailingslashit( $this->_get_imports_path() ) . 'structure.json';
		$questions = trailingslashit( $this->_get_imports_path() ) . 'questions.json';

		if ( ! $wp_filesystem->is_file( $questions ) || ! $wp_filesystem->is_file( $structure ) ) {
			throw new Exception( __( 'Invalid zip file', 'thrive-quiz-builder' ) );
		}
	}

	/**
	 * Gets the path to Wordpress uploads dir
	 *
	 * @return array
	 * @throws Exception
	 * @see wp_upload_dir()
	 *
	 */
	private function _get_upload_dir() {

		if ( empty( $this->wp_upload_dir ) ) {
			$this->wp_upload_dir = wp_upload_dir();
		}

		if ( ! empty( $this->wp_upload_dir['error'] ) ) {
			throw new Exception( sprintf( __( 'Could not determine uploads folder (%s)', 'thrive-quiz-builder' ), $this->wp_upload_dir['error'] ) );
		}

		return $this->wp_upload_dir;
	}

	/**
	 * Returns the path where the zip file is processed/unzipped
	 *
	 * @return string
	 */
	private function _get_imports_path() {

		return trailingslashit( $this->wp_upload_dir['basedir'] ) . 'thrive-quiz-builder/imports';
	}

	/**
	 * Validates zip import file
	 *
	 * @param array $file
	 *
	 * @throws Exception
	 */
	private function _validate_file( $file ) {

		$is_valid = false;

		$zip_types = array(
			'application/x-zip-compressed',
			'application/zip',
		);

		if ( strpos( $file['name'], '.zip' ) > 0 && in_array( $file['type'], $zip_types, false ) && ! empty( $file['tmp_name'] ) ) {
			$is_valid = true;
		}

		if ( ! $is_valid ) {
			throw new Exception( __( 'Invalid File', 'thrive-quiz-builder' ) );
		}
	}

	/**
	 * Based on user environment inits filesystem
	 *
	 * @throws Exception
	 */
	private function _init_file_system() {

		defined( 'FS_METHOD' ) || define( 'FS_METHOD', 'direct' );

		if ( FS_METHOD !== 'direct' ) {
			WP_Filesystem( array(
				'hostname' => defined( 'FTP_HOST' ) ? FTP_HOST : '',
				'username' => defined( 'FTP_USER' ) ? FTP_USER : '',
				'password' => defined( 'FTP_PASS' ) ? FTP_PASS : '',
			) );
		} else {
			WP_Filesystem();
		}
	}

	/**
	 * Reads the whole questions collection from json file and imports them into DB
	 *
	 * @throws Exception
	 */
	private function _process_quiz_questions() {

		$file_content = $this->_get_file_content( 'questions.json' );

		if ( ! $file_content ) {
			throw new Exception( 'Could not read quiz questions', 'thrive-quiz-builder' );
		}

		$questions = json_decode( $file_content, true );

		if ( empty( $questions ) ) {
			return;
		}

		$collection = new TQB_Questions_Collection( $questions );
		$collection->set_import_path( $this->_get_imports_path() );
		$collection->set_results_map( $this->_results_map );
		$collection->import( $this->_quiz_id );
	}

	/**
	 * Reads the file content by including it
	 *
	 * @param string $filename
	 *
	 * @return false|string
	 */
	private function _get_file_content( $filename ) {

		$filename = trailingslashit( $this->_get_imports_path() ) . $filename;
		ob_start();
		include $filename;

		return ob_get_clean();
	}

	/**
	 * Reads the quiz details from json file and create a new quiz in DB
	 *
	 * @return int quiz id
	 * @throws Exception
	 */
	private function _process_quiz_details() {

		$file_content = $this->_get_file_content( 'details.json' );

		if ( ! $file_content ) {
			throw new Exception( 'Could not read quiz details', 'thrive-quiz-builder' );
		}

		$quiz_details = json_decode( $file_content, true );
		$quiz_manager = new TQB_Quiz_Manager();
		$quiz_id      = $quiz_manager->save_quiz(
			array(
				'post_title' => $quiz_details['post_title'] . ' - imported',
				'tpl'        => $quiz_details['tpl'],
			)
		);


		if ( ! $quiz_id ) {
			throw new Exception( __( 'Could not import quiz details', 'thrive-quiz-builder' ) );
		}

		TQB_Post_meta::update_quiz_type_meta(
			$quiz_id,
			array( 'type' => $quiz_details['type'] )
		);

		TQB_Post_meta::update_highlight_settings_meta(
			$quiz_id,
			! empty( $quiz_details['highlight_settings'] ) ? $quiz_details['highlight_settings'] : array()
		);

		TQB_Post_meta::update_feedback_settings_meta(
			$quiz_id,
			! empty( $quiz_details['feedback_settings'] ) ? $quiz_details['feedback_settings'] : array()
		);

		TQB_Post_meta::update_quiz_scroll_settings_meta(
			$quiz_id,
			! empty( $quiz_details['scroll_settings'] ) ? $quiz_details['scroll_settings'] : array()
		);

		TQB_Post_meta::update_quiz_style_meta(
			$quiz_id,
			array( 'style' => $quiz_details['style'] )
		);

		tqb_progress_settings_instance( $quiz_id )->set_data( $quiz_details['progress_settings'] )->save();

		$this->_quiz_id = $quiz_id;
		/**
		 * results/categories are the same thing
		 */
		$this->_import_quiz_results( $quiz_id, $quiz_details['results'] );

		update_post_meta( $quiz_id, TQB_Post_meta::META_NAME_FOR_QUIZ_VIDEO_OPTIONS, $quiz_details['video_options'] );
		update_post_meta( $quiz_id, TQB_Post_meta::META_NAME_FOR_QUIZ_AUDIO_OPTIONS, $quiz_details['audio_options'] );

		if ( ! empty( $quiz_details['qna_templates'] ) ) {
			wp_update_post( array(
					'ID'           => $quiz_id,
					'post_content' => $quiz_details['post_content'],
				)
			);

			update_post_meta( $quiz_id, 'tve_qna_templates', $quiz_details['qna_templates'] );
			update_post_meta( $quiz_id, 'tve_custom_css', $quiz_details['custom_css'] );
			update_post_meta( $quiz_id, 'tve_content_before_more', $quiz_details['content_before_more'] );
			update_post_meta( $quiz_id, '_tve_post_constants', $quiz_details['post_constants'] );
			update_post_meta( $quiz_id, 'tve_globals', $quiz_details['tve_globals'] );
			update_post_meta( $quiz_id, 'tve_updated_post', $quiz_details['tve_updated_post'] );
		}

		foreach ( $quiz_details['settings'] as $key => $value ) {
			update_post_meta( $quiz_id, $key, $value );
		}

		return $this->_quiz_id;
	}

	/**
	 * Insert into DB the results/categories
	 *
	 * @param int   $quiz_id
	 * @param array $results
	 */
	private function _import_quiz_results( $quiz_id, $results ) {

		if ( ! is_int( $quiz_id ) ) {
			return;
		}

		$results = (array) $results;

		/** @var $tqbdb TQB_Database */
		global $tqbdb;

		foreach ( $results as $result ) {
			$id = $tqbdb->insert_new_quiz_result( $quiz_id, $result['text'] );

			$this->_results_map[ $result['id'] ] = $id;
		}
	}

	/**
	 * Imports all quiz structures pages and running tests for each of them
	 */
	private function _process_quiz_structure() {

		$structure = json_decode( $this->_get_file_content( 'structure.json' ), true );

		if ( ! is_array( $structure ) ) {
			return;
		}

		foreach ( array( 'splash', 'optin', 'results' ) as $item ) {

			if ( empty( $structure[ $item ] ) ) {
				continue;
			}

			$structure_item = new TQB_Import_Structure_Item( $this->_quiz_id );
			$structure_item->set_import_path( $this->_get_imports_path() );
			$structure_item->set_results_map( $this->_results_map );
			$structure_item->set_global_colours( json_decode( $this->_get_file_content( 'global_colours.json' ) ) );

			/**
			 * Import structure post/page
			 */
			$post_data                = json_decode( $this->_get_file_content( $structure[ $item ] . '_post.json' ), true );
			$post_data['post_parent'] = $this->_quiz_id;
			$post                     = $structure_item->save_post( $post_data );

			if ( false === $post instanceof WP_Post ) {
				continue;
			}

			/**
			 * import variations
			 */
			$variations_data = json_decode( $this->_get_file_content( $structure[ $item ] . '_variations.json' ), true );
			$new_variations  = $structure_item->save_variations( $variations_data, $post->ID );

			$test_data = json_decode( $this->_get_file_content( $structure[ $item ] . '_test.json' ), true );
			$structure_item->save_test( $test_data, $new_variations );

			$structure[ $item ] = $post->ID;
		}

		/**
		 * replace the site url for this image prop
		 */
		$structure['image'] = tqb()->plugin_url( 'assets/images/' . basename( $structure['image'] ) );
		$structure['ID']    = $this->_quiz_id;

		unset( $structure['results_page'] );

		update_post_meta( $this->_quiz_id, TQB_Post_meta::META_NAME_FOR_QUIZ_STRUCTURE, $structure );
	}

	/**
	 * Import quiz badge and badge settings
	 */
	private function _process_quiz_badge() {

		$badge_details = (array) json_decode( $this->_get_file_content( 'badge.json' ), true );

		if ( empty( $badge_details ) ) {
			return;
		}

		$badge_details   = $badge_details['post'];
		$old_post_parent = $badge_details['post_parent']; //Previous quiz id

		$import_path = $this->_get_imports_path();
		$old_name    = $import_path . '/' . $old_post_parent . '.png';
		$new_name    = $import_path . '/' . $this->_quiz_id . '.png';
		$site_url    = get_site_url();
		$placeholder = TQB_Export_Step_Structure::URL_PLACEHOLDER;
		$badge_url   = $badge_details['settings']['background_image']['url'];
		$need_import = ! strpos( $badge_url, 'thrive-quiz-builder/image-editor/includes/templates/images' );

		/**
		 * Only import custom images, the default ones used by TQB are already in place
		 */
		if ( 'none' !== $badge_url && $need_import ) {
			$attachment = tqb_import_file( trailingslashit( $this->_get_imports_path() ) . basename( $badge_url ) );

			if ( ! empty( $attachment['url'] ) ) {
				$badge_url = $attachment['url'];

				/**
				 * Force the new img url to be used for share badge
				 * Replacing just site url is not enough because the image might be uploaded in a different folder structure and the url would be wrong
				 */
				$css = '<style> #tie-html-canvas > div:first-of-type {background-image: url("' . $badge_url . '")!important;} </style>';

				update_post_meta( $this->_quiz_id, 'tqb_quiz_badge_css', $css );
			}
		}

		foreach ( array( 'image_url', 'editor_url', 'guid' ) as $key ) {
			$badge_details[ $key ] = str_replace( $placeholder, $site_url, $badge_details[ $key ] );
		}

		$badge_details['settings']['background_image']['url'] = str_replace( $placeholder, $site_url, $badge_url );
		$badge_details['post_parent']                         = $this->_quiz_id;

		$_post  = wp_insert_post( (array) $badge_details );
		$_image = new TIE_Image( $_post );

		$_image->get_settings()->save( $badge_details['settings'] );
		$_image->save_content( str_replace( $placeholder, $site_url, $badge_details['content'] ) );
		$_image->save_html_canvas( str_replace( $placeholder, $site_url, $badge_details['html_canvas'] ) );

		WP_Filesystem();

		/** @var WP_Filesystem_Direct $wp_filesystem */
		global $wp_filesystem;

		$destination = $this->_get_upload_dir()['basedir'] . '/thrive-quiz-builder/' . $this->_quiz_id . '.png';

		if ( $wp_filesystem->is_file( $old_name ) ) {
			rename( $old_name, $new_name );
			$wp_filesystem->move( $new_name, $destination );
		}
	}

	/**
	 * Handle Result LInks Import
	 */
	private function _process_quiz_resultlinks() {
		$structure = get_post_meta( $this->_quiz_id, TQB_Post_meta::META_NAME_FOR_QUIZ_STRUCTURE, true );

		if ( ! isset( $structure['results'] ) ) {
			return;
		}

		$json_content    = (array) json_decode( $this->_get_file_content( 'resultlinks.json' ), true );
		$result_instance = new TQB_Results_Page( $structure['results'] );

		$result_instance->set_type( $json_content['result_type'] );

		foreach ( $json_content['result_links'] as $link ) {
			$link['result_id'] = isset( $this->_results_map[ $link['result_id'] ] ) ? $this->_results_map[ $link['result_id'] ] : '';
			$link['page_id']   = $structure['results'];

			$result_instance->save_link( $link );
		}

		$data = $result_instance->to_json();

		update_post_meta( $data->ID, 'tqb_redirect_display_message', (int) $json_content['display_message'] );
		update_post_meta( $data->ID, 'tqb_redirect_forward_results', (int) $json_content['forward_results'] );
		update_post_meta( $data->ID, 'tqb_redirect_message', $json_content['redirect_message'] );
	}

	/**
	 * Delete all files from import folder
	 */
	private function _clean() {
		tqb_empty_folder( $this->_get_imports_path() );
	}
}
