<?php

if ( ! class_exists( 'TCB_Landing_Page_Cloud_Templates_Api' ) ) {
	require_once TVE_TCB_ROOT_PATH . 'landing-page/inc/TCB_Landing_Page_Transfer.php';
}

class TCB_Content_Templates_Api extends TCB_Landing_Page_Cloud_Templates_Api {

	/**
	 * Needed to support extending the parent singleton
	 *
	 * @return TCB_Content_Templates_Api
	 */
	public static function getInstance() {
		return new TCB_Content_Templates_Api();
	}

	/**
	 * Called from wp-includes/kses.php
	 *
	 * Adds extra css into a list of allowed style css.
	 * That list is used to filter the inline style attributes and removes disallowed rules from content
	 *
	 * @param array $allowed_style_css
	 *
	 * @return array
	 */
	public function add_extra_allowed_style_css( $allowed_style_css = [] ) {
		if ( ! in_array( 'display', $allowed_style_css ) ) {
			$allowed_style_css[] = 'display';
		}

		return $allowed_style_css;
	}

	/**
	 *
	 * Fetches all Content Templates of a type from landingpages.thrivethemes.com
	 *
	 * @param string $type
	 * @param array  $args
	 *
	 * @return array
	 *
	 * @throws Exception
	 */
	public function get_all( $type = null, $args = [] ) {
		$params = wp_parse_args(
			$args,
			array(
				'route'       => 'getAll',
				'tar_version' => defined( 'TCB_CLOUD_DEBUG' ) && TCB_CLOUD_DEBUG ? '10' : TVE_VERSION,
				'type'        => $type,
				'ct'          => md5( time() ),
			)
		);

		$response = $this->_request( $params );
		$data     = json_decode( $response, true );

		if ( empty( $data ) ) {
			throw new Exception( 'Got response: ' . $response );
		}

		if ( empty( $data['success'] ) ) {
			throw new Exception( $data['error_message'] );
		}

		if ( ! isset( $data['data'] ) ) {
			throw new Exception( 'Could not fetch templates.' );
		}

		$this->_validateReceivedHeader( $data );

		$templates = apply_filters( 'tcb_cloud_templates', $data['data'], $type );

		return $templates;
	}

	/**
	 * Just forward the call to get_all()
	 *
	 * Should not be used
	 *
	 * @param string $type
	 *
	 * @return array
	 */
	public function getTemplateList() {
		$args = func_get_args();
		if ( ! count( $args ) ) {
			$args = [ 'testimonial' ];
		}

		return $this->get_all( array_shift( $args ) );
	}

	/**
	 * Get a post associated with a cloud content template
	 *
	 * @param string $id
	 *
	 * @return WP_Post|null
	 */
	public function get_post_for_content_template( $id ) {

		$maybe = get_posts( [
			'post_type'      => TCB_CT_POST_TYPE,
			'meta_key'       => 'tcb_ct_id',
			'meta_value'     => $id,
			'posts_per_page' => 1,
		] );

		return $maybe ? $maybe[0] : null;
	}

	/**
	 * get content template data
	 *
	 * @param      $id
	 * @param bool $do_shortcode whether or not to execute `do_shortcode` on the content
	 *
	 * @return null|array
	 */
	public function get_content_template( $id, $do_shortcode = true ) {
		$post = $this->get_post_for_content_template( $id );

		if ( ! $post ) {
			return null;
		}

		$meta = get_post_meta( $post->ID, 'tcb_ct_meta', true );

		/**
		 * Change post data for a content template
		 *
		 * @param WP_Post $post
		 * @param array   $meta
		 *
		 */
		do_action( 'tcb_before_get_content_template', $post, $meta );

		$content  = $do_shortcode ? do_shortcode( $post->post_content ) : $post->post_content;
		$head_css = $do_shortcode ? do_shortcode( $meta['head_css'] ) : $meta['head_css'];

		$data = array(
			'id'          => $id,
			'type'        => $meta['type'],
			'name'        => $post->post_title,
			'content'     => $content,
			'head_css'    => $head_css,
			'custom_css'  => $meta['custom_css'],
			'v'           => (int) ( isset( $meta['v'] ) ? $meta['v'] : 0 ),
			'config'      => isset( $meta['config'] ) ? $meta['config'] : [],
			'tve_globals' => isset( $meta['tve_globals'] ) ? $meta['tve_globals'] : [],
			'thumb'       => isset( $meta['thumb'] ) ? $meta['thumb'] : [],
		);

		return apply_filters( 'tcb_alter_cloud_template_meta', $data, $meta, $do_shortcode );
	}

	/**
	 * Get data for a content template, or download it if it's not available locally
	 *
	 * @param string|int $id
	 * @param array      $args
	 * @param bool       $do_shortcode
	 *
	 * @return array|WP_Error|null
	 * @throws Exception
	 *
	 */
	public function download( $id, $args = [], $do_shortcode = true ) {
		/**
		 * This needs to always be a string
		 */
		$id        = (string) $id;
		$type      = (string) $args['type'];
		$post_type = get_post_type( $args['post_id'] );

		/**
		 * first make sure we can save the downloaded template
		 */
		$upload = wp_upload_dir();
		if ( ! empty( $upload['error'] ) ) {
			throw new Exception( $upload['error'] );
		}

		$base = trailingslashit( $upload['basedir'] ) . TVE_CLOUD_TEMPLATES_FOLDER . '/';
		if ( false === wp_mkdir_p( $base . 'images' ) ) {
			throw new Exception( 'Could not create the templates folder' );
		}

		$params = array(
			'route'       => 'download',
			'type'        => $type,
			'post_type'   => $post_type,
			'tar_version' => TVE_VERSION,
			'ct'          => md5( time() ),
			'id'          => $id,
		);

		$params = apply_filters( 'tcb_download_template', $params );

		$body = $this->_request( $params );

		$control = [
			'auth' => $this->request['headers']['X-Thrive-Authenticate'],
			'id'   => $id,
		];

		/**
		 * this means an error -> error message is json_encoded
		 */
		if ( empty( $this->received_auth_header ) || strpos( $body, '{"success' ) === 0 ) {
			$data = json_decode( $body, true );
			throw new Exception( isset( $data['error_message'] ) ? $data['error_message'] : ( 'Invalid response: ' . $body ) );
		}

		$this->_validateReceivedHeader( $control );

		if ( $id === 'default' && ! empty( $this->response['headers']['X-Thrive-Template-Id'] ) ) {
			$id = $this->response['headers']['X-Thrive-Template-Id'];
		}

		/**
		 * at this point, $body holds the contents of the zip file
		 */
		$zip_path = trailingslashit( $upload['basedir'] ) . TVE_CLOUD_TEMPLATES_FOLDER . '/ct-' . $id . '.zip';

		tve_wp_upload_bits( $zip_path, $body );

		$template_data = $this->process_zip( $zip_path );

		$post = $this->get_post_for_content_template( $id );
		$data = [
			'post_title'   => $template_data['name'],
			'post_content' => $template_data['content'],
			'post_type'    => TCB_CT_POST_TYPE,
			'post_status'  => 'publish',
		];

		add_filter( 'safe_style_css', [ $this, 'add_extra_allowed_style_css' ] );

		/*
		 * This filter causes serious issues on some of our header / footer templates, for which the html
		 * is a bit more than WordPress can handle - especially in cases where we have html code inside element attributes
		 */
		remove_filter( 'content_save_pre', 'wp_filter_post_kses' );

		if ( ! $post ) {
			$post_id = wp_insert_post( $data );
		} else {
			$data['ID'] = $post->ID;
			wp_update_post( $data );
			$post_id = $post->ID;
		}

		remove_filter( 'safe_style_css', [ $this, 'add_extra_allowed_style_css' ] );

		update_post_meta( $post_id, 'tcb_ct_id', $id );
		update_post_meta( $post_id, 'tcb_ct_meta', apply_filters( 'tcb_alter_cloud_template_meta', array(
			'v'           => isset( $template_data['v'] ) ? $template_data['v'] : '0',
			'type'        => $template_data['type'],
			'head_css'    => $template_data['head_css'],
			'custom_css'  => $template_data['custom_css'],
			'config'      => isset( $template_data['config'] ) ? $template_data['config'] : [],
			'tve_globals' => isset( $template_data['tve_globals'] ) ? $template_data['tve_globals'] : [],
			'thumb'       => isset( $template_data['thumb'] ) ? $template_data['thumb'] : [],
		), $template_data, $do_shortcode ) );

		return $id;
	}

	/**
	 * Extract the content template data from the archive located at $path
	 *
	 * @param string $path
	 *
	 * @throws Exception
	 *
	 */
	public function process_zip( $zip_file_path ) {
		$old_umask = umask( 0 );

		defined( 'FS_METHOD' ) || define( 'FS_METHOD', 'direct' );

		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
		}

		/** @var $wp_filesystem WP_Filesystem_Base */
		global $wp_filesystem;

		$upload         = wp_upload_dir();
		$wp_uploads_dir = $upload['basedir'];

		if ( FS_METHOD !== 'direct' ) {
			WP_Filesystem( array(
				'hostname' => defined( 'FTP_HOST' ) ? FTP_HOST : '',
				'username' => defined( 'FTP_USER' ) ? FTP_USER : '',
				'password' => defined( 'FTP_PASS' ) ? FTP_PASS : '',
			) );
			if ( FS_METHOD !== 'ssh2' ) {
				$wp_uploads_dir = str_replace( ABSPATH, '', $wp_uploads_dir );
			}
		} else {
			WP_Filesystem();
		}

		if ( ! $wp_filesystem->connect() && $wp_filesystem->errors instanceof WP_Error ) {
			throw new Exception( $wp_filesystem->errors->get_error_message() );
		}

		$folder = trailingslashit( $wp_uploads_dir ) . TVE_CLOUD_TEMPLATES_FOLDER . '/';
		//$folder = trailingslashit( $upload['basedir'] ) . TVE_CLOUD_TEMPLATES_FOLDER . '/';

		/* this means the template archive is coming directly from the Thrive Template Cloud, we can trust it */
		$result = unzip_file( $zip_file_path, $folder );

		if ( $result instanceof WP_Error ) {
			umask( $old_umask );
			throw new Exception( __( 'Could not extract the archive file', 'thrive-cb' ) );
		}

		if ( ! $wp_filesystem->is_readable( $folder . 'data.json' ) ) {
			throw new Exception( __( 'Invalid archive contents', 'thrive-cb' ) );
		}

		@unlink( $zip_file_path );

		$config = json_decode( $wp_filesystem->get_contents( $folder . 'data.json' ), true );

		$type = empty( $config['type'] ) ? '' : $config['type'] . '/';

		$baseurl = str_replace(
			array(
				'http://',
				'https://',
			),
			'//',
			$upload['baseurl']
		);

		$upload_url = trailingslashit( $baseurl ) . TVE_CLOUD_TEMPLATES_FOLDER . '/' . $type;

		$images_uri = $upload_url . 'images/';

		$this->replace_images( $config['head_css'], $config['image_map'], $images_uri );
		$this->replace_images( $config['content'], $config['image_map'], $images_uri );

		@unlink( $folder . 'data.json' );

		if ( ! empty( $config['thumb']['url'] ) ) {
			$config['thumb']['url'] = $upload_url . 'thumbnails/' . $config['thumb']['url'];
		}

		return $config;
	}

	/**
	 * Modified string, replaces md5 image codes with image URLs
	 *
	 * @param string $string
	 * @param array  $image_map
	 * @param string $uri
	 */
	protected function replace_images( &$string, $image_map, $uri ) {
		foreach ( $image_map as $key => $name ) {
			$string = str_replace( "{{img={$key}}}", $uri . $name, $string );
		}
	}
}

/**
 * @var TCB_Content_Templates_Api
 */
global $tcb_content_templates_api;

function tcb_content_templates_api() {
	global $tcb_content_templates_api;

	if ( ! isset( $tcb_content_templates_api ) ) {
		$tcb_content_templates_api = TCB_Content_Templates_Api::getInstance();
	}

	return $tcb_content_templates_api;
}
