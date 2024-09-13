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
 * Class TCB_Contact_Form
 */
class TCB_Contact_Form {

	private $posted_data       = [];
	private $empty_posted_data = true;
	private $invalid_fields    = [];
	private $types             = [];

	private $config_parsing_error;
	private $blog_name;

	/**
	 * The permalink the Contact Form is located in
	 *
	 * @var string
	 */
	private $permalink = '';
	private $config;

	/**
	 * The ID of the post the Contact Form is located in
	 */
	private $post_id;

	/**
	 * An array containing security info for backend validation
	 *
	 * @var array
	 */
	private $security = [];

	/**
	 * Used on Zapier CF element connection
	 *
	 * @var array
	 */
	private $_excluded_inputs = [
		'zapier_send_ip',
		'zapier_tags',
	];

	/**
	 * TCB_Contact_Form constructor.
	 *
	 * @param array $data
	 */
	public function __construct( $data = [] ) {
		$data       = $this->sanitize_posted_data( $data );
		$has_config = $this->setup_config( $data );

		if ( $has_config ) {
			$this->setup_posted_data( $data );
			$this->blog_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
			$this->permalink = get_permalink( $this->post_id );

			if ( tve_is_code_debug() ) {
				add_action( 'wp_mail_failed', [ $this, 'mail_error' ] );
			}
		}
	}

	/**
	 * Setup the Posted Data
	 *
	 * @param array $data
	 */
	private function setup_posted_data( $data = [] ) {
		$this->post_id = $data['post_id'];
		$this->types   = static::get_types();

		foreach ( $data as $type => $value ) {
			/**
			 * Filter the Post Data
			 */
			if ( isset( $this->types[ $type ] ) || in_array( (string) $type, $this->_excluded_inputs, true ) ) {
				$this->posted_data[ $type ] = $value;
			}
		}
	}

	/**
	 * @param array $data
	 *
	 * @return bool
	 */
	private function setup_config( $data = [] ) {
		if ( empty( $data['config'] ) ) {
			$this->config_parsing_error = __( 'ERROR: Empty Config', 'thrive-cb' );

			return false;
		}

		$this->config = base64_decode( $data['config'] );
		if ( false !== $this->config ) {
			$this->config = thrive_safe_unserialize( $this->config );

			if ( is_array( $this->config ) ) {
				/**
				 * Filter if a specific string can be found in content edited with thrive architect
				 *
				 * @param boolean $has_string
				 * @param string  $string
				 * @param int     $post_id
				 *
				 * @return boolean
				 */
				$valid_config = apply_filters( 'tcb_architect_content_has_string', false, $data['config'], (int) $data['post_id'] );

				if ( ! $valid_config ) {
					$this->config_parsing_error = __( 'ERROR: Invalid Config', 'thrive-cb' );

					return false;
				}
			}
		}

		if ( empty( $this->config['to_email'] ) || empty( $this->config['submit'] ) ) {
			$this->config_parsing_error = __( 'ERROR: Invalid Config', 'thrive-cb' );

			return false;
		}
		$this->security = json_decode( wp_unslash( $data['security'] ), true );
		if ( ! is_array( $this->security ) || (int) $this->security['check'] !== 1 ) {
			$this->config_parsing_error = __( 'ERROR: Security Warning', 'thrive-cb' );

			return false;
		}

		$this->config = wp_array_slice_assoc( $this->config, [ 'to_email', 'submit' ] );

		return true;
	}

	/**
	 * Submit Form: Validate and Anti Spam Checks
	 *
	 * @return array
	 */
	public function submit() {

		$return = [
			'errors'  => [],
			'success' => 0,
		];

		// Form connected to Zapier
		if ( ( isset( $this->posted_data['zapier_send_ip'] ) || isset( $this->posted_data['zapier_tags'] ) ) && class_exists( 'Thrive_Dash_List_Manager', false ) ) {

			// API connection check
			if ( ! array_key_exists( 'zapier', Thrive_Dash_List_Manager::available() ) ) {
				$return['errors'][] = __( 'Please create a Zapier API connection', 'thrive-cb' );

				return $return;
			}

			// Build params and call Zapier's contact form trigger
			$arguments               = $this->posted_data;
			$arguments['optin_hook'] = 'cf-optin';

			$zapier_instance    = Thrive_Dash_List_Manager::connectionInstance( 'zapier' );
			$zapier_subscribe   = $zapier_instance->addSubscriber( '', $arguments );
			$return['errors'][] = $zapier_subscribe;

			if ( true === filter_var( $zapier_subscribe, FILTER_VALIDATE_BOOLEAN ) ) {
				$return = [
					'errors'  => [],
					'success' => 1,
				];
			}

			return $return;
		}

		if ( ! empty( $this->config_parsing_error ) ) {
			$return['errors'][] = $this->config_parsing_error;

			return $return;
		}

		if ( ! $this->is_valid() ) {

			if ( $this->empty_posted_data ) {
				$return['errors'][] = __( 'The posted data is completely empty!', 'thrive-cb' );
			}

			foreach ( $this->invalid_fields as $invalid_field ) {

				$return['errors'][] = $this->types[ $invalid_field ]['validation_error'];

			}
		} elseif ( $this->is_spam() ) {
			$return['errors'][] = __( 'Invalid reCAPTCHA!', 'thrive-cb' );
		} elseif ( $this->mail() ) {
			$return['success'] = 1;
		} else {
			$return['errors'][] = __( 'Ups, we encountered some issues, please try again later!', 'thrive-cb' );
		}

		return $return;
	}

	/**
	 * Validates The Contact Form based on field type
	 *
	 * @return bool
	 */
	private function is_valid() {

		if ( empty( $this->posted_data ) ) {
			return false;
		}

		foreach ( $this->posted_data as $type => $value ) {

			if ( empty( $value ) ) {
				continue;
			}

			$this->empty_posted_data = false;

			if ( 'email' === $type && ! is_email( $value ) ) {
				$this->invalid_fields[] = $type;
			}

			if ( 'url' === $type && false === filter_var( ( strpos( $value, 'http' ) !== 0 ? 'http://' . $value : $value ), FILTER_VALIDATE_URL ) ) {
				$this->invalid_fields[] = $type;
			}

			if ( 'phone' === $type && ! preg_match( '%^[+]?[0-9\(\)/ -]*$%', $value ) ) {
				$this->invalid_fields[] = $type;
			}
		}

		return empty( $this->invalid_fields ) && $this->empty_posted_data === false;
	}

	/**
	 * Ensures that the request is not SPAM
	 *
	 * @return bool
	 */
	private function is_spam() {

		if ( $this->security['has_recaptcha'] ) {

			if ( empty( $this->posted_data['g-recaptcha-response'] ) ) {
				return true;
			}

			/**
			 * Google reCAPTCHA verification
			 */
			$captcha_api = Thrive_Dash_List_Manager::credentials( 'recaptcha' );

			$_captcha_params = array(
				'response' => $this->posted_data['g-recaptcha-response'],
				'secret'   => empty( $captcha_api['secret_key'] ) ? '' : $captcha_api['secret_key'],
				'remoteip' => ! empty( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( $_SERVER['REMOTE_ADDR'] ) : '',
			);

			$request_captcha = tve_dash_api_remote_post( 'https://www.google.com/recaptcha/api/siteverify', [ 'body' => $_captcha_params ] );
			$response        = json_decode( wp_remote_retrieve_body( $request_captcha ) );
			if ( empty( $response ) || false === $response->success ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Sends the actual mail
	 *
	 * @return bool
	 */
	private function mail() {

		if ( $this->config['submit']['send_confirmation_email'] && ! empty( $this->posted_data['email'] ) ) {

			/**
			 * We an email to the user that completed the contact form
			 */
			$user_email = array(
				'to'      => $this->posted_data['email'],
				'subject' => ! empty( $this->config['submit']['confirmation_subject'] ) ? $this->config['submit']['confirmation_subject'] : __( '[%s] Contact Form submission confirmation', 'thrive-cb' ),
				'message' => $this->get_user_email_message(),
				'headers' => [ 'Content-Type: text/html; charset=UTF-8' ],
			);

			if ( $this->config['submit']['sender_personalized'] ) {
				add_filter( 'wp_mail_from', [ $this, 'user_from_mail' ] );
				add_filter( 'wp_mail_from_name', [ $this, 'user_from_mail_name' ] );

				$user_email['headers'][] = 'reply-to: ' . $this->config['submit']['reply_to'];
			}

			wp_mail( $user_email['to'], sprintf( $user_email['subject'], $this->blog_name ), $user_email['message'], $user_email['headers'] );

			remove_filter( 'wp_mail_from', [ $this, 'user_from_mail' ] );
			remove_filter( 'wp_mail_from_name', [ $this, 'user_from_mail_name' ] );

			/**
			 * Added action after the contact form is submitted to capture the user details that is being sent
			 *
			 * Useful for other functionality to be hooked here
			 */
			do_action( 'tcb_contact_form_user_mail_after_send', $this->posted_data );
		}

		/**
		 * We send an email to the admin informing him that the contact form has been submitted by a user
		 */
		$admin_email_data = $this->get_admin_email_data();
		$admin_email      = [
			'to'      => $this->config['to_email']['to'],
			'subject' => $admin_email_data['subject'],
			'message' => $admin_email_data['message'],
			'headers' => [ 'Content-Type: text/html; charset=UTF-8' ],
		];

		if ( ! empty( $this->posted_data['email'] ) ) {
			$admin_email['headers'][] = 'reply-to: ' . $this->posted_data['email'];
		}

		if ( ! empty( $this->config['to_email']['cc'] ) ) {
			/**
			 * We strip the white spaces of the email list in case user inputs something like this "test1@thrive.com , test2@thrive.com"
			 */
			$admin_email['headers'][] = 'cc: ' . str_replace( ' ', '', $this->config['to_email']['cc'] );
		}

		if ( ! empty( $this->config['to_email']['bcc'] ) ) {
			/**
			 * We strip the white spaces of the email list in case user inputs something like this "test1@thrive.com , test2@thrive.com"
			 */
			$admin_email['headers'][] = 'Bcc: ' . str_replace( ' ', '', $this->config['to_email']['bcc'] );
		}

		$admin_email = wp_unslash( $admin_email );

		return wp_mail( $admin_email['to'], $admin_email['subject'], $admin_email['message'], $admin_email['headers'] );
	}

	/**
	 * Function used to display errors from wp_mail function
	 *
	 * @param WP_Error $wp_error
	 */
	public function mail_error( $wp_error ) {
		echo '<pre>';
		print_r( $wp_error );
		echo '<pre>';
	}

	/**
	 * sanitize $_POST
	 *
	 * @param $value
	 *
	 * @return array|string
	 */
	private function sanitize_posted_data( $value ) {
		if ( is_array( $value ) ) {
			$value = array_map( [ $this, 'sanitize_posted_data' ], $value );
		} elseif ( is_string( $value ) ) {
			$value = wp_check_invalid_utf8( $value );
			$value = wp_kses_no_null( $value );
		}

		return $value;
	}

	/**
	 * Returns the admin email text
	 *
	 * @return array
	 */
	private function get_admin_email_data() {

		$email_data = '';
		$subject    = str_replace(
			array(
				'[form_url_slug]',
			),
			array(
				trim( parse_url( $this->permalink, PHP_URL_PATH ), '/' ),
			),
			$this->config['to_email']['subject']
		);
		foreach ( $this->posted_data as $type => $value ) {

			if ( in_array( $type, [ 'g-recaptcha-response' ] ) ) {
				/**
				 * We have done this to ensure some info is left out of the admin email.
				 * Example: google reCaptcha code
				 */
				continue;
			}

			/**
			 * Build the table for admin mail
			 */
			$type_cell  = '<td>' . $this->types[ $type ]['label'] . ':</td>';
			$value_cell = '<td>' . $value . '</td>';

			$email_data .= '<tr>' . $type_cell . $value_cell . '</tr>';

			if ( empty( $this->types[ $type ]['shortcode'] ) ) {
				continue;
			}
			/**
			 * Replace Subject Tags
			 */
			$subject = str_replace( $this->types[ $type ]['shortcode'], $value, $subject );
		}
		$posted_data = '<table>' . $email_data . '</table>';

		$date_row  = '<tr><td>' . __( 'Date', 'thrive-cb' ) . ':</td><td>' . date_i18n( 'F j, Y' ) . '</td></tr>';
		$time_row  = '<tr><td>' . __( 'Time', 'thrive-cb' ) . ':</td><td>' . date_i18n( 'g:i a' ) . '</td></tr>';
		$date_info = '<table>' . $date_row . $time_row . '</table>';

		$message = $posted_data . '<br>' . '----' . '<br>' . $date_info;

		$message = nl2br( $message );

		return [
			'subject' => $subject,
			'message' => $message,
		];
	}

	/**
	 * Returns the user email message
	 *
	 * @return string
	 */
	private function get_user_email_message() {
		$message = str_replace(
			array(
				'[wp_site_title]',
				'[form_url_slug]',
			),
			array(
				$this->blog_name,
				trim( parse_url( $this->permalink, PHP_URL_PATH ), '/' ),
			),
			$this->config['submit']['confirmation_message']
		);

		foreach ( $this->posted_data as $type => $value ) {
			if ( empty( $this->types[ $type ]['shortcode'] ) ) {
				continue;
			}
			$message = str_replace( $this->types[ $type ]['shortcode'], $value, $message );
		}

		$message = nl2br( $message );

		return $message;
	}

	/**
	 * Modifies the from email address
	 *
	 * @return string
	 */
	public function user_from_mail() {
		return $this->config['submit']['from_email'];
	}

	/**
	 * Modifies the from email name
	 *
	 * @return string
	 */
	public function user_from_mail_name() {
		return wp_specialchars_decode( $this->config['submit']['from_name'], ENT_QUOTES );
	}

	/**
	 * Returns Contact Form Field Types
	 *
	 * @return array
	 */
	public static function get_types() {
		return array(
			'first_name'           => array(
				'label'            => __( 'First Name', 'thrive-cb' ),
				'tag_name'         => 'input',
				'defaults'         => array(
					'label'       => __( 'First Name', 'thrive-cb' ),
					'placeholder' => __( 'John', 'thrive-cb' ),
				),
				'type'             => 'text',
				'shortcode'        => '[first_name]',
				'validation_error' => __( 'Invalid First Name', 'thrive-cb' ),
			),
			'last_name'            => array(
				'label'            => __( 'Last Name', 'thrive-cb' ),
				'tag_name'         => 'input',
				'type'             => 'text',
				'defaults'         => array(
					'label'       => __( 'Last Name', 'thrive-cb' ),
					'placeholder' => __( 'Doe', 'thrive-cb' ),
				),
				'shortcode'        => '[last_name]',
				'validation_error' => __( 'Invalid Last Name', 'thrive-cb' ),
			),
			'full_name'            => array(
				'label'            => __( 'Full Name', 'thrive-cb' ),
				'tag_name'         => 'input',
				'type'             => 'text',
				'defaults'         => array(
					'label'       => __( 'Full Name', 'thrive-cb' ),
					'placeholder' => __( 'John Doe', 'thrive-cb' ),
				),
				'shortcode'        => '[full_name]',
				'validation_error' => __( 'Invalid Full Name', 'thrive-cb' ),
			),
			'email'                => array(
				'label'            => __( 'Email Address', 'thrive-cb' ),
				'tag_name'         => 'input',
				'type'             => 'email',
				'defaults'         => array(
					'label'       => __( 'Email Address', 'thrive-cb' ),
					'placeholder' => __( 'j.doe@inbox.com', 'thrive-cb' ),
				),
				'shortcode'        => '[user_email]',
				'validation_error' => __( 'Invalid Email', 'thrive-cb' ),
			),
			'message'              => array(
				'label'            => __( 'Message', 'thrive-cb' ),
				'tag_name'         => 'textarea',
				'type'             => '',
				'defaults'         => array(
					'label'       => __( 'Message', 'thrive-cb' ),
					'placeholder' => __( 'Type your message here...', 'thrive-cb' ),
				),
				'validation_error' => __( 'Invalid Message', 'thrive-cb' ),
			),
			'phone'                => array(
				'label'            => __( 'Phone Number', 'thrive-cb' ),
				'tag_name'         => 'input',
				'type'             => 'tel',
				'defaults'         => array(
					'label'       => __( 'Phone Number', 'thrive-cb' ),
					'placeholder' => __( '+1 555 2368', 'thrive-cb' ),
				),
				'shortcode'        => '[user_phone]',
				'validation_error' => __( 'Invalid Phone', 'thrive-cb' ),
			),
			'url'                  => array(
				'label'            => __( 'Website', 'thrive-cb' ),
				'tag_name'         => 'input',
				'type'             => 'url',
				'defaults'         => array(
					'label'       => __( 'Website', 'thrive-cb' ),
					'placeholder' => __( 'https://yourwebsite.com/', 'thrive-cb' ),
				),
				'shortcode'        => '[user_url]',
				'validation_error' => __( 'Invalid URL', 'thrive-cb' ),
			),
			'g-recaptcha-response' => array(
				'label'            => __( 'Recaptcha', 'thrive-cb' ),
				'tag_name'         => 'recaptcha',
				'type'             => 'g-recaptcha-response',
				'defaults'         => array(
					'label'       => __( 'ReCaptcha', 'thrive-cb' ),
					'placeholder' => __( 'ReCaptcha', 'thrive-cb' ),
				),
				'shortcode'        => '',//Google reCAPTCHA Should Not have a shortcode attached.
				'validation_error' => __( 'Invalid reCAPTCHA', 'thrive-cb' ),
			),
		);
	}
}
