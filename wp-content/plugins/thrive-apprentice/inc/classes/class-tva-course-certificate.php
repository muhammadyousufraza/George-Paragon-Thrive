<?php

/**
 * Class TVA_Course_Post
 * - post which helps edit the course certificate
 *
 * @property int    ID
 * @property string post_title
 * @property string public_url
 * @property string number
 * @property string download_url
 */
final class TVA_Course_Certificate implements JsonSerializable {

	use TVA_Course_Post;

	/**
	 * Post type used to store course certificate
	 */
	const POST_TYPE = 'tva_certificate';

	/**
	 * Post type used to save certificate as a user template
	 */
	const USER_TEMPLATE_POST_TYPE = 'tva_user_certif_tpl';

	/**
	 * Prefix under a certificate is saved in uploads
	 */
	const FILE_NAME_PREFIX = 'tva-certificate';

	/**
	 * Verification page query string name
	 *
	 * Used in template redirect to redirect the user to the verification page
	 */
	const VERIFICATION_PAGE_QUERY_NAME = 'tva_r_c_v';

	/**
	 * Verification page query string value
	 *
	 * Used in template redirect to redirect the user to verification page
	 */
	const VERIFICATION_PAGE_QUERY_VAL = 'Thr!v3';

	/**
	 * Certificate download URL Query Variable Name and Query Variable Value
	 */
	const DOWNLOAD_URL_QUERY_NAME = 'tva_download_certificate';
	const DOWNLOAD_URL_QUERY_VAL  = 'ThriveAppCerDownload';

	/**
	 * @var TVA_Course_V2
	 */
	protected $_course;

	/**
	 * @var WP_Post which holds the content for certificate page and
	 *              - is editable with TAr
	 */
	protected $_post;

	/**
	 * @var
	 */
	public $number;

	/**
	 * @var TVA_Course_Certificate
	 */
	protected static $_instance;

	/**
	 * Holds data for default template cache
	 *
	 * @var array|null
	 */
	public static $default_template_cache;

	/**
	 * Holds the value of the query string that if present in the URL makes the post type public accessible
	 *
	 * @var string
	 */
	private $public_query_string_var = 'thrive_apprentice_certificate';
	private $public_query_string_val = 'BkaM9fzQTlvN';

	/**
	 * @return TVA_Course_Certificate
	 */
	public static function instance() {
		if ( empty( static::$_instance ) ) {
			static::$_instance = new static();
		}

		return static::$_instance;
	}

	/**
	 * Registers filters and actions
	 */
	private function __construct() {
		add_filter( 'thrive_theme_allow_body_class', [ $this, 'theme_body_class' ], 99, 1 );

		add_filter( 'tve_dash_exclude_post_types_from_index', [ $this, 'add_post_type_to_list' ] );

		add_filter( 'thrive_theme_ignore_post_types', [ $this, 'add_post_type_to_list' ] );

		add_action( 'init', [ $this, 'register_post_type' ] );
	}

	/**
	 * Magic get
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function __get( $key ) {

		$value = null;

		if ( $this->_post instanceof WP_Post && isset( $this->_post->$key ) ) {
			$value = $this->_post->$key;
		} elseif ( method_exists( $this, 'get_' . $key ) ) {
			$method_name = 'get_' . $key;
			$value       = $this->$method_name();
		}

		return $value;
	}

	/**
	 * Returns the URL of the certificate post
	 *
	 * @return string
	 */
	private function get_url() {
		return get_permalink( $this->ID );
	}

	/**
	 * Direct link for downloading the course certificate
	 *
	 * @return string
	 */
	public function get_download_url() {

		return add_query_arg( [
			static::DOWNLOAD_URL_QUERY_NAME => static::DOWNLOAD_URL_QUERY_VAL,
		], get_term_link( $this->_course->get_id() ) );
	}

	/**
	 * Returns the public URL of the certificate post
	 * or a specific customer's public certificate URL if customer ID is provided
	 * public url is the URL that can be accessed externally - It is used for generating the PDF
	 * Contains a the permalink of the certificate post and a query string
	 *
	 * @return string
	 */
	public function get_public_url( $customer_id = false ) {
		if ( false === $customer_id ) {
			$customer_id = get_current_user_id();
		}

		return add_query_arg( [
			$this->public_query_string_var => $this->public_query_string_val,
			'tva_certificate_user_id'      => $customer_id,
			'tva_bypass_cache'             => mt_rand( 1000000, 9999999 ), //Generates a new random number everytime the public URL gets generated to avoid the page being served from cache
		], $this->get_url() );
	}

	/**
	 * Certificate dimensions
	 * format: width x height
	 *
	 * @return string
	 */
	public function get_dimensions() {
		return (string) get_post_meta( $this->ID, 'tva_certificate_dimensions', true );
	}

	/**
	 * Returns the certificate number
	 * The certificate number is a unique number assigned to user meta the moment the certificate is downloaded
	 *
	 * @return string
	 */
	public function get_number() {
		/**
		 * tve_get_current_user_id - works also when generating certificate PDF - process
		 */
		return get_user_meta( tve_get_current_user_id(), TVA_Customer::get_certificate_meta_key( $this->ID ), true );
	}

	/**
	 * @param TVA_Course_V2 $course
	 *
	 * @return TVA_Course_Certificate
	 */
	public function set_course( $course ) {

		if ( true === $course instanceof TVA_Course_V2 ) {
			$this->_course = $course;
		}

		return $this;
	}

	/**
	 * Ensure there is a post for current course
	 *
	 * @return false|WP_Post
	 */
	public function ensure_post() {
		if ( false === $this->_course instanceof TVA_Course_V2 ) {
			return false;
		}

		/**
		 * @var $_post WP_Post
		 */
		$_post = $this->_course->has_certificate( false );

		if ( false === $_post instanceof WP_Post ) {

			$id = wp_insert_post(
				[
					'post_type'   => static::POST_TYPE,
					'post_title'  => $this->_course->name . ' certificate',
					'post_name'   => $this->_course->name . '_certificate',
					'post_status' => 'draft',
				]
			);

			if ( false === is_wp_error( $id ) ) {
				update_post_meta( $id, 'tcb2_ready', 1 );
				update_post_meta( $id, 'tcb_editor_enabled', 1 );

				update_term_meta( $this->_course->term_id, 'tva_certificate', $id );
				wp_set_object_terms( $id, $this->_course->term_id, TVA_Const::COURSE_TAXONOMY );

				$this->add_certificate_element( $id );

				$_post = get_post( $id );
			}

		} /**
		 * If post name doesn't end with _certificate, update it
		 */
		else if ( substr( $_post->post_name, - 12 ) !== '_certificate' ) {
			$_post->post_name = $this->_course->name . '_certificate';
			wp_update_post( $_post );
		}

		$this->_post = $_post;

		return $this->_post;
	}

	/**
	 * Adds the default certificate element into post and updates its meta.
	 * Holds a cache level for the certificate data after download
	 *
	 * @param int $post_id
	 *
	 * @return void
	 */
	public function add_certificate_element( $post_id ) {
		if ( empty( static::$default_template_cache ) ) {
			static::$default_template_cache = tve_get_cloud_template_data( 'certificate', [
				'id'                => 'default',
				'type'              => 'certificate',
				'skip_do_shortcode' => 1,
			] );
		}

		if ( ! empty( static::$default_template_cache ) && is_array( static::$default_template_cache ) && ! empty( static::$default_template_cache['content'] ) ) {
			update_post_meta( $post_id, 'tve_save_post', static::$default_template_cache['content'] );
			update_post_meta( $post_id, 'tve_updated_post', static::$default_template_cache['content'] );
			update_post_meta( $post_id, 'tve_custom_css', static::$default_template_cache['head_css'] );

			if ( ! empty( static::$default_template_cache['tve_globals'] ) && ! empty( static::$default_template_cache['tve_globals']['certificate_dimensions'] ) ) {
				update_post_meta( $post_id, 'tva_certificate_dimensions', sanitize_title( static::$default_template_cache['tve_globals']['certificate_dimensions'] ) );
			}
		}
	}

	/**
	 * Update certificate post status
	 * Sets the status publish
	 *
	 * @return $this
	 */
	public function set_status_publish() {
		if ( $this->_post ) {
			wp_publish_post( $this->ID );
			$this->_post->post_status = 'publish';
		}

		return $this;
	}

	/**
	 * Used on localization
	 *
	 * @return array
	 */
	#[ReturnTypeWillChange]
	public function jsonSerialize() {
		$data = [
			'course_id' => $this->_course->term_id,
		];

		if ( $this->_post && $this->_post->post_status === 'publish' ) {
			$data = array_merge( [
				'title'        => $this->_post->post_title,
				'ID'           => $this->_post->ID,
				'edit_url'     => tcb_get_editor_url( $this->_post->ID ),
				'preview_url'  => $this->get_url(),
				'download_url' => $this->download_url,
			], $data );
		}

		return $data;
	}

	/**
	 * @param $allow_theme_classes
	 *
	 * @return boolean
	 */
	public function theme_body_class( $allow_theme_classes ) {
		$post_type = get_post_type();

		if ( static::POST_TYPE === $post_type ) {
			$allow_theme_classes = false;
		}

		return $allow_theme_classes;
	}

	/**
	 * Adds the certificate post type to a list of post types
	 * Used in various filters that are implemented in TAR/Theme
	 *
	 * @param array $post_types
	 *
	 * @return array
	 */
	public function add_post_type_to_list( $post_types = [] ) {
		$post_types[] = static::POST_TYPE;

		return $post_types;
	}

	/**
	 * Download certificate method
	 *
	 * @param TVA_Customer $customer
	 *
	 * @return array
	 */
	public function download( $customer ) {
		list( $width, $height ) = explode( 'x', $this->get_dimensions() );

		$pdf_from_url = new TVD_PDF_From_URL(
			$this->get_public_url( $customer->get_id() ),
			array(
				'file_name' => $customer->compute_certificate_file_name( $this ),
				'width'     => (int) $width,
				'height'    => (int) $height,
			)
		);

		return $pdf_from_url->generate();
	}

	/**
	 * Register the certificate post type
	 *
	 * @return void
	 */
	public function register_post_type() {
		if ( ! TVA_Const::$tva_during_activation ) {
			register_post_type(
				static::POST_TYPE,
				array(
					'labels'              => array(
						'name' => 'Certificate',
					),
					'exclude_from_search' => true, //This post should not be present in wordpress search.
					'description'         => 'Hidden post type for storing course certificate',
					'public'              => TVA_Product::has_access() || ( ! empty( $_GET[ $this->public_query_string_var ] ) && $_GET[ $this->public_query_string_var ] === $this->public_query_string_val ),
					'show_in_menu'        => false,
					'supports'            => array( 'title', 'content' ),
					'show_in_rest'        => false,
				)
			);

			register_post_type( static::USER_TEMPLATE_POST_TYPE, [
				'public'              => isset( $_GET[ TVE_EDITOR_FLAG ] ),
				'publicly_queryable'  => is_user_logged_in(),
				'query_var'           => false,
				'exclude_from_search' => true,
				'rewrite'             => false,
				'_edit_link'          => 'post.php?post=%d',
				'map_meta_cap'        => true,
				'label'               => 'Certificate User Template',
				'capabilities'        => [
					'edit_others_posts'    => 'tve-edit-cpt',
					'edit_published_posts' => 'tve-edit-cpt',
				],
				'show_in_nav_menus'   => false,
				'show_in_menu'        => false,
				'show_in_rest'        => true,
				'has_archive'         => false,
			] );
		}
	}

	/**
	 * Generates code for the current certificate instance
	 *
	 * @return null|string certificate code for the user
	 */
	public function generate_code( $user_id = null, $segments = 4, $segment_length = 4 ) {

		if ( empty( $user_id ) || ! ( $this->_course instanceof TVA_Course_V2 ) || ! ( $this->_post instanceof WP_Post ) ) {
			return null;
		}

		$site_url  = get_site_url();
		$course_id = $this->_course->term_id;
		$post_id   = $this->_post->ID;

		$raw    = strtoupper( md5( $site_url . $course_id . $user_id . $post_id . time() ) );
		$length = $segments * $segment_length;
		$code   = '';
		for ( $i = 0; $i < $length; $i ++ ) {
			$code .= $raw[ $i ];
			if ( ( $i + 1 ) % $segment_length === 0 && $i !== $length - 1 ) {
				$code .= '-';
			}
		}

		return $code;
	}

	/**
	 * Data to be saved when the certificate is generated
	 *
	 * @param int $user_id
	 *
	 * @return array with prepared data to be saved in DB
	 */
	public function get_data( $user_id ) {
		return array(
			'number'    => $this->number,
			'post_id'   => $this->_post->ID,
			'course_id' => $this->_course->term_id,
			'user_id'   => $user_id,
			'url'       => home_url(),
			'timestamp' => time(),
		);
	}

	/**
	 * Searches the certificate data by number
	 * - appends more details based on the IDs found in data
	 *
	 * @param string $number of certificate
	 *
	 * @return array
	 */
	public function search_by_number( $number ) {

		$data = get_option( 'tva_certificate_' . $number, null );

		if ( ! empty( $data ) ) {
			$data['course']    = new TVA_Course_V2( (int) $data['course_id'] );
			$data['number']    = $number;
			$data['recipient'] = get_userdata( $data['user_id'] );
		}

		return $data;
	}

	/**
	 * Duplicates the certificate on the new course
	 *
	 * @param TVA_Course_V2 $new_course
	 *
	 * @return TVA_Course_Certificate
	 */
	public function duplicate( $new_course ) {
		$old_certificate = $this->_post;
		$new_certificate = $new_course->certificate;
		$new_certificate->ensure_post();

		if ( 'publish' === $old_certificate->post_status ) {
			$new_certificate->set_status_publish();
		}

		$this->duplicate_post_meta( $old_certificate, $new_certificate );

		return $new_certificate;
	}

	/**
	 * Send certificate email to student
	 *
	 * @param $student_id
	 *
	 * @return void
	 */
	public function send_email( $student_id ) {
		$student       = new TVA_Customer( $student_id );
		$download_link = $this->_course->has_completed_post() ? $this->_course->get_completed_post()->get_url() : $this->get_download_url();

		$email_template = tva_email_templates()->check_templates_for_trigger( 'certificate_issued' );
		$email_template = array_merge( $email_template, array(
			'user'                 => $student,
			'course_name'          => $this->_course->name,
			'certificate_download' => $download_link,
		) );

		/**
		 * Prepares the email template before sending it to the student
		 */
		do_action( 'tva_prepare_certificate_email_template', $email_template );

		$to      = $student->get_user()->user_email;
		$subject = $email_template['subject'];
		$body    = do_shortcode( nl2br( $email_template['body'] ) );
		$headers = array( 'Content-Type: text/html' );
		wp_mail( $to, $subject, $body, $headers );
	}
}
