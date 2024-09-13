<?php

namespace TCB\inc\helpers;

/**
 * Class FileUploadConfig
 *
 * @package TCB\inc\helpers
 *
 * @property int    $max_size
 * @property int    $max_files
 * @property array  $file_types
 * @property array  $custom_file_types custom extensions
 * @property string $api               key of the connected file upload API service
 * @property string $name              Filename template
 * @property string $folder            identification for the destination folder
 * @property string $required          Whether or not the field is required
 */
class FileUploadConfig extends FormSettings {

	const EMAIL_FILENAME_TEMPLATE = '___T_USR_EMAIL___';

	const POST_TYPE = '_tcb_file_upload';

	/**
	 * Default configuration for file uploads
	 *
	 * @var array
	 */
	public static $defaults = [
		'max_files'         => 1,
		'max_size'          => 1, // in MB
		'file_types'        => [ 'documents' ],
		'custom_file_types' => [],
		'name'              => '{match}', // by default, match the filename with the one uploaded by the visitor
	];

	/**
	 * Get a list of each file group, it's title, icon and associated extensions
	 *
	 * @return array[]
	 */
	public static function get_allowed_file_groups() {
		return array(
			'documents' => array(
				'name'       => __( 'Documents', 'thrive-cb' ),
				'icon'       => 'file',
				'extensions' => [ 'pdf', 'doc', 'docx', 'ppt', 'pptx', 'pps', 'ppsx', 'odt', 'xls', 'xlsx', 'psd', 'txt' ],
			),
			'images'    => array(
				'name'       => __( 'Images', 'thrive-cb' ),
				'icon'       => 'image2',
				'extensions' => [ 'jpg', 'jpeg', 'png', 'gif', 'ico' ],
			),
			'audio'     => array(
				'name'       => __( 'Audio files', 'thrive-cb' ),
				'icon'       => 'audio',
				'extensions' => [ 'mp3', 'm4a', 'ogg', 'wav' ],
			),
			'video'     => array(
				'name'       => __( 'Video files', 'thrive-cb' ),
				'icon'       => 'video',
				'extensions' => [ 'mp4', 'm4v', 'mov', 'wmv', 'avi', 'mpg', 'ogv', '3gp', '3g2' ],
			),
			'zip'       => array(
				'name'       => __( 'Zip archives', 'thrive-cb' ),
				'icon'       => 'archive',
				'extensions' => [ 'zip', 'tar', 'gzip', 'gz' ],
			),
			'custom'    => array(
				'name' => __( 'Custom', 'thrive-cb' ),
				'icon' => 'wrench',
			),
		);
	}

	/**
	 * Get a nonce key to use for file uploads
	 *
	 * @param string $file_id
	 *
	 * @return string
	 */
	public static function get_nonce_key( $file_id ) {
		return "tcb_file_api_{$file_id}";
	}

	/**
	 * Generate a unique nonce for each file_id
	 *
	 * @param string $file_id
	 *
	 * @return string
	 */
	public static function create_nonce( $file_id ) {
		return wp_create_nonce( static::get_nonce_key( $file_id ) );
	}

	/**
	 * @param string $nonce user-submitted nonce key
	 * @param string $file_id
	 *
	 * @return boolean|int
	 */
	public static function verify_nonce( $nonce, $file_id ) {
		return wp_verify_nonce( $nonce, static::get_nonce_key( $file_id ) );
	}

	/**
	 * Setter for config
	 *
	 * @param array $config
	 *
	 * @return $this
	 */
	public function set_config( $config ) {
		parent::set_config( $config );
		/* normalize required value to integer */
		$this->config['required'] = ! empty( $this->config['required'] ) ? 1 : 0;

		return $this;
	}

	/**
	 * Get a subset of the configuration, that only applies for frontend
	 *
	 * @param bool $json get it as JSON
	 *
	 * @return string|array|false
	 */
	public function get_frontend_config( $json = true ) {
		$config = array(
			'max_size'  => $this->max_size,
			'max_files' => $this->max_files,
			'allowed'   => $this->get_allowed_extensions(),
			'required'  => $this->required,
		);

		return $json ? json_encode( $config ) : $config;
	}

	/**
	 * Get a list of allowed file extensions
	 *
	 * @return array
	 */
	public function get_allowed_extensions() {
		$file_types = $this->file_types;
		if ( ! is_array( $file_types ) ) {
			$file_types = [];
		}
		$extensions = [];
		if ( is_array( $this->custom_file_types ) && in_array( 'custom', $file_types, true ) ) {
			$extensions = $this->custom_file_types;
		}

		$extension_groups = [ $extensions ];
		foreach ( static::get_allowed_file_groups() as $key => $group ) {
			if ( isset( $group['extensions'] ) && in_array( $key, $file_types, true ) ) {
				$extension_groups [] = $group['extensions'];
			}
		}

		return call_user_func_array( 'array_merge', $extension_groups );
	}

	/**
	 * Check if the extension is allowed
	 *
	 * @param string $extension
	 *
	 * @return bool
	 */
	public function extension_allowed( $extension ) {
		$extension = strtolower( $extension );

		/* if blacklisted, don't allow this extension */
		if ( in_array( $extension, static::get_extensions_blacklist(), true ) ) {
			return false;
		}

		return in_array( $extension, $this->get_allowed_extensions(), true );
	}

	/**
	 * Check if the filesize is allowed (if it's lower than max accepted file size)
	 *
	 * @param int $uploaded_file_size
	 *
	 * @return bool
	 */
	public function size_allowed( $uploaded_file_size ) {
		/* $this->max_size = maximum file size in MB */
		return $uploaded_file_size <= wp_convert_hr_to_bytes( $this->max_size . 'm' );
	}

	/**
	 * Calculate the filename used to store this on the API service
	 *
	 * @param string $original_name original filename, without extension
	 *
	 * @return string filename without extension
	 */
	public function get_upload_filename( $original_name ) {
		$templates = array(
			'{match}' => sanitize_file_name( $original_name ),
			'{date}'  => current_time( 'm-d-Y' ),
			'{time}'  => current_time( 'Hi' ),
			/**
			 * email is not known at the time of file upload.
			 * Solution is to use this template and rename the files via API when the form is submitted
			 */
			'{email}' => static::EMAIL_FILENAME_TEMPLATE,
		);

		return str_replace( array_keys( $templates ), $templates, $this->name ) . '_' . mt_rand( 1000000, 9999999 );
	}

	/**
	 * Gets the service API that's been setup with this file upload configuration
	 *
	 * @return \Thrive_Dash_List_Connection_Abstract|\WP_Error
	 */
	public function get_service() {
		$api_connection = $this->api;

		if ( ! $api_connection ) {
			return new \WP_Error( 'Missing file upload service. Please contact site owner' );
		}

		return \Thrive_Dash_List_Manager::connection_instance( $api_connection );
	}

	/**
	 * Validates the main LG submitted form
	 *
	 * @param array $data
	 *
	 * @return string|bool
	 */
	public function validate_form_submit( $data ) {

		$files      = ! empty( $data['_tcb_files'] ) && is_array( $data['_tcb_files'] ) ? $data['_tcb_files'] : [];
		$min_files  = $this->required ? 1 : 0;
		$file_count = count( $files );
		$result     = true;

		if ( ! $this->ID ) {
			$result = __( 'Invalid file configuration', 'thrive-cb' );
		} elseif ( $file_count < $min_files || $file_count > $this->max_files ) {
			$result = __( 'Invalid number of files', 'thrive-cb' );
		}

		/* validate nonces */
		foreach ( $files as $nonce => $file_id ) {
			if ( ! static::verify_nonce( $nonce, $file_id ) ) {
				$result = __( 'Invalid request', 'thrive-cb' );
			}
		}

		return $result;
	}

	/**
	 * Checks the configuration to see if any files need to be renamed
	 * This can happen if the user adds '{email}' to the file name settings
	 *
	 * @return bool
	 */
	public function needs_file_rename() {
		return strpos( $this->name, '{email}' ) !== false;
	}

	/**
	 * Returns a list of file extensions that should always be forbidden, regardless of user settings
	 *
	 * @return array
	 */
	public static function get_extensions_blacklist() {
		return [
			'0xe',
			'a6p',
			'action',
			'app',
			'applescript',
			'bash',
			'bat',
			'cgi',
			'cod',
			'com',
			'dek',
			'dex',
			'dmg',
			'ebm',
			'elf',
			'es',
			'esh',
			'ex4',
			'exe',
			'exopc',
			'fpi',
			'gpe',
			'gpu',
			'hms',
			'hta',
			'ipa',
			'isu',
			'jar',
			'jsx',
			'kix',
			'mau',
			'mel',
			'mem',
			'mrc',
			'exe',
			'pex',
			'pif',
			'plsc',
			'pkg',
			'prg',
			'ps1',
			'pwc',
			'qit',
			'rbx',
			'rox',
			'rxe',
			'scar',
			'scb',
			'scpt',
			'sct',
			'seed',
			'sh',
			'u3p',
			'vb',
			'vbe',
			'vbs',
			'vbscript',
			'vlx',
			'widget',
			'workflow',
			'ws',
			'xbe',
			'xex',
			'xys',
		];
	}
}


/**
 * Parse the content and replace file_upload shortcode configuration IDs with the actual config
 *
 */
add_filter( 'tve_thrive_shortcodes', static function ( $content, $is_editor_page ) {
	$content = preg_replace_callback( '#__FILE_SETUP__(\d+)__FILE_SETUP__#', static function ( $matches ) use ( $is_editor_page ) {
		$file_config = FileUploadConfig::get_one( $matches[1] );
		$replacement = $is_editor_page ? $file_config->get_config() : $file_config->get_frontend_config();

		return esc_attr( $replacement );
	}, $content );

	return $content;
}, 10, 2 );

/**
 * Sends an error response to be picked up by the plupload library
 *
 * @param string $error
 * @param int    $code
 */
function upload_error_handler( $error, $code = 400 ) {
	status_header( $code );
	echo esc_html( $error );
	die();
}

/**
 * Handle file uploads submitted through the Lead Gen element
 */
function handle_upload() {
	if ( empty( $_REQUEST['id'] ) ) {
		upload_error_handler( 'Missing required parameter' );
	}

	// this must mean that the uploaded file is too large
	if ( empty( $_FILES ) ) {
		upload_error_handler( 'Missing file, or file is too large' );
	}

	if ( ! empty( $_FILES['file']['error'] ) ) {
		upload_error_handler( 'Error uploading file', 500 );
	}

	$config = FileUploadConfig::get_one( isset( $_REQUEST['id'] ) ? sanitize_text_field( $_REQUEST['id'] ) : '' );
	if ( ! $config->ID ) {
		upload_error_handler( 'Missing / Invalid request parameter' );
	}

	$info = pathinfo( $_FILES['file']['name'] );
	if ( empty( $info['extension'] ) ) {
		/* something is wrong here */
		upload_error_handler( 'Invalid file name' );
	}

	if ( ! $config->extension_allowed( $info['extension'] ) ) {
		upload_error_handler( 'This type of file is not accepted' );
	}

	if ( ! $config->size_allowed( filesize( $_FILES['file']['tmp_name'] ) ) ) { // phpcs:ignore
		upload_error_handler( 'File is too big' );
	}

	$api = $config->get_service();
	if ( is_wp_error( $api ) ) {
		upload_error_handler( 'Could not determine API' );
	}

	$file_meta = array(
		'originalName' => $_FILES['file']['name'], // phpcs:ignore
		'name'         => $config->get_upload_filename( $info['filename'] ) . '.' . $info['extension'],
	);

	/** @var string|\WP_Error $result */
	$result = $api->upload( file_get_contents( $_FILES['file']['tmp_name'] ), $config->folder, $file_meta ); // phpcs:ignore
	if ( is_wp_error( $result ) ) {
		/**
		 * Log API Error
		 */
		global $wpdb;

		$log_data = array(
			'date'          => date( 'Y-m-d H:i:s' ),
			'error_message' => sanitize_text_field( $result->get_error_message() ),
			'api_data'      => serialize( array(
				'email'     => ! empty( $_REQUEST['email'] ) ? sanitize_email( $_REQUEST['email'] ) : '-',
				'file_name' => $file_meta['name'],
			) ),
			'connection'    => $config->api,
			'list_id'       => '',
		);

		$wpdb->insert( $wpdb->prefix . 'tcb_api_error_log', $log_data );
		upload_error_handler( $result->get_error_message() );
	}

	/**
	 * This looks like a successful operation
	 */
	wp_send_json( array(
		'success' => true,
		'nonce'   => FileUploadConfig::create_nonce( $result ), // generate nonce so that it can be used in file delete requests and validate the subsequent POST
		'file_id' => $result,
	) );
}

/**
 * Handle file removal
 */
function handle_remove() {
	if ( empty( $_POST['nonce'] ) || empty( $_POST['file_id'] ) || empty( $_POST['id'] ) ) {
		/* don't generate any error messages */
		exit();
	}

	if ( ! FileUploadConfig::verify_nonce( sanitize_text_field( $_POST['nonce'] ), sanitize_text_field( $_POST['file_id'] ) ) ) {
		exit();
	}

	$api = FileUploadConfig::get_one( sanitize_text_field( $_POST['id'] ) )->get_service();
	if ( is_wp_error( $api ) ) {
		exit();
	}

	$api->delete( sanitize_text_field( $_POST['file_id'] ) );
	exit();
}

/**
 * Process the form data before sending it to various APIs
 * Checks if the form data contains any files and, if a mapping is defined for the file field, make sure data contains a field populated with mapping data
 * Mapped data must contain URLs to uploaded files
 *
 * @param array $data submitted post data
 *
 * @return array
 */
function process_form_data( $data ) {
	if ( empty( $data['tcb_file_id'] ) ) {
		return $data;
	}
	$config = FileUploadConfig::get_one( $data['tcb_file_id'] );
	$api    = $config->get_service();
	if ( is_wp_error( $api ) ) {
		return $data;
	}

	$file_data = [];
	$file_urls = [];
	$file_ids  = [];
	/* transform storage file IDs into URLs */
	if ( ! empty( $data['_tcb_files'] ) ) {
		/* if email has been submitted, and the filenames should include the email, we need to trigger a file rename for each submitted file */
		if ( ! empty( $data['email'] ) && $config->needs_file_rename() ) {
			foreach ( $data['_tcb_files'] as $file_id ) {
				$file_data[ $file_id ] = $api->rename_file( $file_id, function ( $filename ) use ( $data ) {
					$email = str_replace( '@', '__A-Round__', sanitize_email( $data['email'] ) );
					$email = str_replace( '__A-Round__', '+', sanitize_file_name( $email ) );

					return str_replace( FileUploadConfig::EMAIL_FILENAME_TEMPLATE, $email, $filename );
				} );
			}
		}

		foreach ( $data['_tcb_files'] as $index => $file_id ) {
			/* filedata gets populated if the file needs to be renamed. Using it to avoid extra API calls  */
			$data['_tcb_files'][ $index ] = isset( $file_data[ $file_id ] ) ? $file_data[ $file_id ] : $api->get_file_data( $file_id );
			$file_urls[]                  = $data['_tcb_files'][ $index ]['url'];
			$file_ids[ $index ]           = $file_id;
		}
	}
	/**
	 * Mapped field: needs the file URL to be sent to the autoresponder
	 */
	$data[ $data['tcb_file_field'] ] = $file_urls;
	/**
	 * Preserve the file IDs in the form data so that they can be used later on
	 */
	$data['_tcb_files_upload_ids'] = $file_ids;

	return $data;
}

/**
 * Build an HTML list of file URLs based on the submitted files
 *
 * @param array $data post data
 *
 * @return string
 */
function build_html_file_list( $data ) {
	if ( empty( $data['_tcb_files'] ) || ! is_array( $data['_tcb_files'] ) ) {
		return '';
	}

	return sprintf(
		'<ul style="margin:0">%s</ul>',
		array_reduce( $data['_tcb_files'], static function ( $carry, $file ) {
			return $carry . sprintf( '<li><a href="%s">%s</a></li>', esc_attr( $file['url'] ), esc_html( $file['name'] ) );
		}, '' )
	);
}

/**
 * Search and replace the [uploaded_files] shortcode with an HTML list of file links
 *
 * @param string $message
 * @param array  $data
 *
 * @return string
 */
function process_email_message( $message, $data ) {
	return str_replace( '[uploaded_files]', build_html_file_list( $data ), $message );
}

/**
 * Build the HTML to be displayed in [all_fields] shortcode inside the email message.
 * Outputs the file list a HTML <ul> list element containing links to files and file names.
 *
 * @param string $value current value
 * @param string $field field name. Only process if it starts with `mapping_file_`
 * @param array  $data  form submission data
 *
 * @return string
 */
function process_email_field( $value, $field, $data ) {

	if ( strpos( $field, 'mapping_file_' ) === 0 ) {
		$value = build_html_file_list( $data );
	}

	return $value;
}

add_action( 'wp_ajax_nopriv_tcb_file_upload', 'TCB\inc\helpers\handle_upload' );
add_action( 'wp_ajax_tcb_file_upload', 'TCB\inc\helpers\handle_upload' );

add_action( 'wp_ajax_nopriv_tcb_file_remove', 'TCB\inc\helpers\handle_remove' );
add_action( 'wp_ajax_tcb_file_remove', 'TCB\inc\helpers\handle_remove' );

add_filter( 'tcb_before_api_subscribe_data', 'TCB\inc\helpers\process_form_data' );
add_filter( 'thrive_api_email_message', 'TCB\inc\helpers\process_email_message', 10, 2 );
add_filter( 'thrive_email_message_field', 'TCB\inc\helpers\process_email_field', 10, 3 );
