<?php

use TVA\Assessments\TVA_User_Assessment;
use TVA\Product;

/**
 * Class TVA_Email_Templates
 * - localizes required data
 * - saves an template item into DB
 * - handles the email templates sent to users
 */
class TVA_Email_Templates {

	const NEW_ACCOUNT_TEMPLATE_SLUG        = 'newAccount';
	const CERTIFICATE_ISSUED_TEMPLATE_SLUG = 'certificateIssued';
	const ASSESSMENT_MARKED_TEMPLATE_SLUG  = 'assessmentMarked';
	const PRODUCT_ACCESS_EXPIRE            = 'productAccessExpire';
	const CONTENT_TYPE                     = 'text/html';
	const TRIGGERS                         = array(
		'thrivecart',
		'sendowl',
		'wordpress',
		'certificate_issued',
		'assessment_passed',
		'assessment_failed',
	);
	/**
	 * @var self
	 */
	protected static $instance;

	/**
	 * @var array
	 */
	protected $_templates = array();

	/**
	 * @var WP_User newly created user or student the certificate is sent to; kept on this instance to be available for rendering the shortcodes
	 */
	protected $_user;

	/**
	 * @var Product
	 */
	protected $_product;

	/**
	 * @var string course name for the certificate template; kept on this instance to be available for rendering the shortcodes
	 */
	protected $_course;

	/**
	 * @var string certificate download link for the certificate template; kept on this instance to be available for rendering the shortcodes
	 */
	protected $_certificate_download;

	/**
	 * @var string assessment for the assessment template; kept on this instance to be available for rendering the shortcodes
	 */
	protected $_user_assessment;

	/**
	 * TVA_Email_Templates constructor.
	 */
	private function __construct() {

		$this->_templates = $this->_get_option();
		/*
		 * Backwards compatibility: [user_pass] shortcode should always have a `if_user_provided` parameter
		 */
		if ( ! empty( $this->_templates['newAccount'] ) && ! empty( $this->_templates['newAccount']['body'] ) ) {
			$this->_templates['newAccount']['body'] = str_replace( '[user_pass]', '[user_pass if_user_provided="The password you chose during registration"]', $this->_templates['newAccount']['body'] );
		}

		$this->_init();
	}

	/**
	 * @return array
	 */
	protected function _get_option() {
		return get_option( 'tva_email_templates', array() );
	}

	/**
	 * @return bool
	 */
	protected function _save_option() {
		return update_option( 'tva_email_templates', $this->_templates );
	}

	/**
	 * Handles wp hooks
	 */
	protected function _init() {
		add_filter( 'tva_admin_localize', array( $this, 'get_connected_email_apis' ) );
		add_filter( 'tva_admin_localize', array( $this, 'get_admin_data_localization' ) );
		add_filter( 'tva_admin_localize', array( $this, 'get_shortcodes' ) );
		add_filter( 'tva_admin_localize', array( $this, 'get_triggers' ) );
		add_action( 'rest_api_init', array( $this, 'rest_api_init' ) );
		add_action( 'tva_prepare_new_user_email_template', array( $this, 'prepare_new_user_email_template' ) );
		add_action( 'tva_prepare_certificate_email_template', array( $this, 'prepare_certificate_email_template' ) );
		add_action( 'tva_prepare_product_expiry_email_template', array( $this, 'prepare_product_expiry_email_template' ) );
		add_action( 'tva_prepare_assessment_marked_email_template', array( $this, 'prepare_assessment_email_template' ) );
		add_action( 'tvd_after_create_wordpress_account', array( $this, 'after_create_wordpress_account' ), 10, 2 );
		add_filter( 'pre_wp_mail', array( $this, 'maybe_send_mail_via_api' ), 10, 2 );

		add_shortcode( 'first_name', function () {

			if ( $this->_user ) {
				$first_name = $this->_user->first_name;

				if ( empty( $first_name ) ) {

					if ( ! empty( $_POST['name'] ) ) {
						/**
						 * This is a bit ugly
						 * Creating a user can be from apprentice or from a lead generation with WP connection form (from TAR)
						 *
						 * When created via lead generation form from TAR (with function register_new_user) the first name of the user is updated after the user has been created with function wp_update_user()
						 * The send user email trigger is fired on user creation.
						 */
						$first_name = sanitize_text_field( $_POST['name'] );
					} else {
						$first_name = $this->_user->user_email;
					}
				}

				return $first_name;
			}

			return null;
		} );

		add_shortcode( 'user_name', function () {
			return $this->_user->user_login;
		} );

		add_shortcode( 'user_pass', function ( $attributes ) {
			/* if the password has been generated, include it in the email message */
			if ( ! empty( $GLOBALS['tva_user_pass_generated'] ) ) {
				return $this->_user->user_pass;
			}

			/* if not, the password must have been chosen by the user, return the `if_user_provided` message */
			if ( empty( $attributes['if_user_provided'] ) ) {
				$attributes['if_user_provided'] = 'The password you chose during registration';
			}

			return $attributes['if_user_provided'];
		} );

		add_shortcode( 'login_button', function ( $attributes, $content ) {
			return '<a target="_blank" href="' . $this->_get_login_url() . '" style="color: #ffffff; border-radius: 4px; background-color: #236085; display: inline-block; padding: 5px 40px;">' . $content . '</a>';
		} );

		add_shortcode( 'login_link', function ( $attributes, $content ) {
			return '<a target="_blank" href="' . $this->_get_login_url() . '">' . $content . '</a>';
		} );

		add_shortcode( 'site_name', function () {
			return get_bloginfo( 'name' );
		} );

		add_shortcode( 'course_name', function () {
			if ( $this->_course instanceof TVA_Course_V2 ) {
				$course_name = $this->_course->name;
			} elseif ( $this->_user_assessment instanceof TVA_User_Assessment ) {
				$course_name = ( new TVA_Course_V2( $this->_user_assessment->get_course_id() ) )->name;
			} else {
				$course_name = '';
			}

			return $course_name;
		} );

		add_shortcode( 'expiring_product', function () {
			$product_name = '';
			if ( $this->_product instanceof Product ) {
				$product_name = $this->_product->get_name();
			}

			return $product_name;
		} );

		add_shortcode( 'download_certificate_button', function ( $attributes, $content ) {
			return '<a target="_blank" href="' . $this->_certificate_download . '" style="color: #ffffff; border-radius: 4px; background-color: #236085; display: inline-block; padding: 5px 40px;">' . $content . '</a>';
		} );

		add_shortcode( 'assessment_button', function ( $attributes, $content ) {
			$url = $this->_get_login_url();
			if ( $this->_user_assessment instanceof TVA_User_Assessment ) {
				$assessment = new TVA_Assessment( $this->_user_assessment->post_parent );
				$url        = $assessment->get_url();
			}

			return '<a target="_blank" href="' . $url . '" style="color: #ffffff; border-radius: 4px; background-color: #236085; display: inline-block; padding: 5px 40px;">' . $content . '</a>';
		} );

		add_shortcode( 'assessment_type', function () {
			if ( $this->_user_assessment instanceof TVA_User_Assessment ) {
				$assessment = new TVA_Assessment( $this->_user_assessment->post_parent );

				return TVA_Assessment::$types[ $assessment->get_type() ];
			}
		} );

		add_shortcode( 'assessment_status', function () {
			if ( $this->_user_assessment instanceof TVA_User_Assessment ) {
				$assessment_status = '';
				$status            = $this->_user_assessment->status;
				if ( $status === TVA_Const::ASSESSMENT_STATUS_COMPLETED_PASSED ) {
					$assessment_status = TVA_Const::ASSESSMENTS_PASSED_TEXT;
				} else {
					$assessment_status = TVA_Const::ASSESSMENTS_FAILED_TEXT;
				}

				return $assessment_status;
			}
		} );

		add_filter( 'tcb_api_subscribe_data_instance', array( $this, 'trigger_wp_new_registration' ), 10, 2 );

		/**
		 * When sending emails from automator or from the default register page via WordPress,
		 * the email templates needs to be changed in case the the backend option is on
		 *
		 * 0 priority is set to hook before the WP hook
		 */
		add_action( 'register_new_user', static function () {
			$email_template = tva_email_templates()->check_templates_for_trigger( 'wordpress' );

			if ( false !== $email_template ) {
				tva_email_templates()->trigger_process( $email_template );
			}
		}, 0 );
	}

	/**
	 * Checks if there is set a Login Page and returns its URL
	 * - otherwise returns wp login url
	 *
	 * @return string
	 */
	protected function _get_login_url() {

		$login_url  = wp_login_url();
		$login_page = tva_get_settings_manager()->get_setting( 'login_page' );

		if ( $login_page ) {
			$login_url = get_permalink( $login_page );
		}

		return $login_url;
	}

	/**
	 * Hooks into `wp_new_user_notification_email` with specified template
	 *
	 * @param array $email_template
	 */
	public function prepare_new_user_email_template( $email_template ) {

		add_filter( 'wp_mail_content_type', function () {
			return self::CONTENT_TYPE;
		} );

		add_filter( 'wp_mail_from_name', static function ( $from_name ) use ( $email_template ) {

			if ( ! empty( $email_template['from_name'] ) ) {
				$from_name = $email_template['from_name'];
			}

			return $from_name;
		}, PHP_INT_MAX );

		add_filter( 'wp_new_user_notification_email', function ( $email_data, $user ) use ( $email_template ) {
			/** @var WP_User $user */
			$this->_user = $user;

			if ( empty( $email_template['user_pass'] ) ) {
				$GLOBALS['tva_user_pass_generated'] = true;
				$new_pass                           = wp_generate_password( 12, false );
				wp_set_password( $new_pass, $user->ID );
			} else {
				$new_pass = $email_template['user_pass'];
			}

			$this->_user->user_pass = $new_pass; //used on generating email body

			$email_data['subject'] = do_shortcode( $email_template['subject'] );
			$email_data['message'] = do_shortcode( nl2br( $email_template['body'] ) );

			return $email_data;
		}, 10, 3 );
	}

	/**
	 * @param array $email_template
	 *
	 * @return void
	 */
	public function prepare_product_expiry_email_template( $email_template ) {
		add_filter( 'wp_mail_content_type', function () {
			return self::CONTENT_TYPE;
		} );

		add_filter( 'wp_mail_from_name', static function ( $from_name ) use ( $email_template ) {

			if ( ! empty( $email_template['from_name'] ) ) {
				$from_name = $email_template['from_name'];
			}

			return $from_name;
		} );

		$this->_user    = $email_template['user'];
		$this->_product = $email_template['product'];
	}

	public function prepare_certificate_email_template( $email_template ) {
		add_filter( 'wp_mail_content_type', function () {
			return self::CONTENT_TYPE;
		} );

		add_filter( 'wp_mail_from_name', static function ( $from_name ) use ( $email_template ) {

			if ( ! empty( $email_template['from_name'] ) ) {
				$from_name = $email_template['from_name'];
			}

			return $from_name;
		} );

		$this->_user                 = $email_template['user'];
		$this->_course               = $email_template['course_name'];
		$this->_certificate_download = $email_template['certificate_download'];
	}

	public function prepare_assessment_email_template( $email_template ) {
		add_filter( 'wp_mail_content_type', function () {
			return self::CONTENT_TYPE;
		} );

		add_filter( 'wp_mail_from_name', static function ( $from_name ) use ( $email_template ) {

			if ( ! empty( $email_template['from_name'] ) ) {
				$from_name = $email_template['from_name'];
			}

			return $from_name;
		} );

		$this->_user            = $email_template['user'];
		$this->_user_assessment = $email_template['user_assessment'];
	}

	/**
	 * Registers required rest API endpoints
	 */
	public function rest_api_init() {
		register_rest_route( 'tva/v1', '/emailTemplate', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'save_template' ),
			'permission_callback' => array( $this, 'permissions_check' ),
		) );
	}


	/**
	 * Checks if there are templates saved for a specified trigger slug
	 *
	 * @param string $trigger_slug
	 *
	 * @return bool|array of template
	 */
	public function check_templates_for_trigger( $trigger_slug ) {

		foreach ( $this->_templates as $template ) {
			if ( ! empty( $template['triggers'] ) && in_array( $trigger_slug, $template['triggers'] ) ) {
				return $template;
			}
		}

		return false;
	}

	/**
	 * @param $template_slug
	 *
	 * @return array
	 */
	public function get_template_details_by_slug( $template_slug ) {
		return [
			'subject'   => $this->_get_template_subject( $template_slug ),
			'from_name' => $this->_get_template_from_name( $template_slug ),
			'body'      => $this->_get_template_body( $template_slug ),
			'triggers'  => $this->_get_template_triggers( $template_slug ),
		];
	}

	/**
	 * Loops through all triggers and if there is a template set for it then return the template
	 * otherwise return false
	 *
	 * @return array|bool
	 */
	public function check_template_for_any_trigger() {

		foreach ( self::TRIGGERS as $trigger ) {
			$template = $this->check_templates_for_trigger( $trigger );
			if ( false !== $template ) {
				return $template;
			}
		}

		return false;
	}

	/**
	 * Callback for saving a template API endpoint
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return true
	 */
	public function save_template( $request ) {

		$this->_templates[ $request->get_param( 'slug' ) ] = array(
			'subject'   => $request->get_param( 'subject' ),
			'from_name' => (string) $request->get_param( 'from_name' ),
			'body'      => $request->get_param( 'body' ),
			'triggers'  => $request->get_param( 'triggers' ),
		);

		$this->_save_option();

		return true;
	}


	/**
	 * Check if a given request has access to the product
	 *
	 * @return WP_Error|bool
	 */
	public function permissions_check() {
		return TVA_Product::has_access();
	}

	/**
	 * Localizes triggers
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public function get_triggers( $data ) {

		$data['emailTriggers'] = array();

		$data['emailTriggers']['stripe'] = array(
			'slug'        => 'stripe',
			'description' => esc_html__( 'Stripe - new account created after purchase', 'thrive-apprentice' ),
		);

		$data['emailTriggers']['sendowl'] = array(
			'slug'        => 'sendowl',
			'description' => esc_html__( 'SendOwl - new account created on registration page (during purchase flow)', 'thrive-apprentice' ),
		);

		$data['emailTriggers']['thrivecart'] = array(
			'slug'        => 'thrivecart',
			'description' => esc_html__( 'ThriveCart - new account created after purchase', 'thrive-apprentice' ),
		);

		$data['emailTriggers']['wordpress'] = array(
			'slug'        => 'wordpress',
			'description' => esc_html__( 'When a user registers to create a new free account', 'thrive-apprentice' ),
		);

		$data['emailTriggers']['certificate_issued'] = array(
			'slug' => 'certificate_issued',
		);

		$data['emailTriggers']['assessment_passed'] = array(
			'slug'        => 'assessment_passed',
			'description' => esc_html__( 'Assessment Passed', 'thrive-apprentice' ),
		);

		$data['emailTriggers']['assessment_failed'] = array(
			'slug'        => 'assessment_failed',
			'description' => esc_html__( 'Assessment Failed', 'thrive-apprentice' ),
		);

		return $data;
	}

	/**
	 * Localizes shortcodes
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public function get_shortcodes( $data ) {

		$data['emailShortcodes'] = array();

		$data['emailShortcodes']['firstName'] = array(
			'slug'  => 'firstName',
			'label' => esc_html__( 'First name' ),
			'text'  => '[first_name]',
		);

		$data['emailShortcodes']['username'] = array(
			'slug'  => 'username',
			'label' => esc_html__( 'Username' ),
			'text'  => '[user_name]',
		);

		$data['emailShortcodes']['password'] = array(
			'slug'  => 'password',
			'label' => esc_html__( 'Password' ),
			'text'  => '[user_pass if_user_provided="The password you chose during registration"]',
		);

		$data['emailShortcodes']['loginButton'] = array(
			'slug'  => 'loginButton',
			'label' => esc_html__( 'Login button' ),
			'text'  => '[login_button]' . esc_html__( 'Log into your account', 'thrive-apprentice' ) . '[/login_button]',
		);

		$data['emailShortcodes']['loginLink'] = array(
			'slug'  => 'loginLink',
			'label' => esc_html__( 'Login link' ),
			'text'  => '[login_link]' . esc_html__( 'Log into your account', 'thrive-apprentice' ) . '[/login_link]',
		);

		$data['emailShortcodes']['siteName'] = array(
			'slug'  => 'siteName',
			'label' => esc_html__( 'Site name' ),
			'text'  => '[site_name]',
		);

		$data['emailShortcodes']['assessmentButton'] = array(
			'slug'  => 'assessmentButton',
			'label' => esc_html__( 'Assessment button' ),
			'text'  => '[assessment_button]' . esc_html__( 'Access your assessment', 'thrive-apprentice' ) . '[/assessment_button]',

		);

		$data['emailShortcodes']['assessmentType'] = array(
			'slug'  => 'assessmentType',
			'label' => esc_html__( 'Assessment Type' ),
			'text'  => '[assessment_type]',
		);

		$data['emailShortcodes']['courseName'] = array(
			'slug'  => 'courseName',
			'label' => esc_html__( 'Course Name' ),
			'text'  => '[course_name]',
		);

		$data['emailShortcodes']['assessmentStatus'] = array(
			'slug'  => 'assessmentStatus',
			'label' => esc_html__( 'Assessment Status' ),
			'text'  => '[assessment_status]',
		);

		$data['emailShortcodes']['product'] = array(
			'slug'  => 'product',
			'label' => esc_html__( 'Product', 'thrive-apprentice' ),
			'text'  => '[expiring_product]',
		);

		return $data;
	}

	/**
	 * Gets a template's body by template's name
	 * - from DB if exists or from file as default
	 *
	 * @param string $tpl_slug
	 *
	 * @return string
	 */
	private function _get_template_body( $tpl_slug ) {

		/**
		 * default body
		 */
		ob_start();
		include TVA_Const::plugin_path( '/admin/views/template/emailTemplates/bodies/' ) . $tpl_slug . '.phtml';
		$body = ob_get_contents();
		ob_end_clean();

		/**
		 * DB saved body
		 */
		if ( ! empty( $this->_templates[ $tpl_slug ]['body'] ) ) {
			$body = $this->_templates[ $tpl_slug ]['body'];
		}

		return $body;
	}

	/**
	 * Based on template's name returns a string as email subject
	 *
	 * @param string $tpl_slug
	 *
	 * @return string
	 */
	private function _get_template_subject( $tpl_slug ) {
		switch ( $tpl_slug ) {
			case self::CERTIFICATE_ISSUED_TEMPLATE_SLUG:
				$subject = 'Download your course certificate here!';
				break;
			case self::ASSESSMENT_MARKED_TEMPLATE_SLUG:
				$subject = 'Your assessment has been marked!';
				break;
			case self::NEW_ACCOUNT_TEMPLATE_SLUG:
				$subject = 'Your account has been created';
				break;
			case static::PRODUCT_ACCESS_EXPIRE:
				$subject = 'Your access is about to expire';
				break;
			default:
				$subject = 'No Template Selected';
				break;
		}

		if ( ! empty( $this->_templates[ $tpl_slug ]['subject'] ) ) {
			$subject = $this->_templates[ $tpl_slug ]['subject'];
		}

		return $subject;
	}

	private function _get_template_from_name( $tpl_slug ) {
		$from_name = get_bloginfo( 'name' );

		if ( ! empty( $this->_templates[ $tpl_slug ]['from_name'] ) ) {
			$from_name = $this->_templates[ $tpl_slug ]['from_name'];
		}

		return $from_name;
	}

	/**
	 * Gets a list of trigger slugs for which a template is activated
	 *
	 * @param string $tpl_slug
	 *
	 * @return array
	 */
	private function _get_template_triggers( $tpl_slug ) {

		/**
		 * by default thrivecart trigger has to be selected for new account template
		 */
		if ( $tpl_slug === self::NEW_ACCOUNT_TEMPLATE_SLUG && empty( $this->_templates[ $tpl_slug ]['triggers'] ) ) {
			$this->_templates[ $tpl_slug ]['triggers'] = array(
				'thrivecart',
			);
		} elseif ( $tpl_slug === self::CERTIFICATE_ISSUED_TEMPLATE_SLUG ) {
			$this->_templates[ $tpl_slug ]['triggers'] = array(
				'certificate_issued',
			);
		} elseif ( $tpl_slug === static::PRODUCT_ACCESS_EXPIRE ) {
			$this->_templates[ $tpl_slug ]['triggers'] = [
				'product_access_expire',
			];
		} elseif ( $tpl_slug === self::ASSESSMENT_MARKED_TEMPLATE_SLUG && empty( $this->_templates[ $tpl_slug ]['triggers'] ) ) {
			$this->_templates[ $tpl_slug ]['triggers'] = array(
				'assessment_passed',
				'assessment_failed',
			);
		}

		return $this->_templates[ $tpl_slug ]['triggers'];
	}

	/**
	 * Localization of available email services
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public function get_connected_email_apis( $data ) {

		$email_connection_instance = Thrive_Dash_List_Manager::connection_instance( 'email' );

		if ( method_exists( $email_connection_instance, 'get_connected_email_providers' ) ) {
			$data['connected_email_providers'] = $email_connection_instance->get_connected_email_providers();
		}

		return $data;
	}

	/**
	 * Checks if the system should send emails through Emails APIs.
	 *
	 * The system will send emails through APIs if the option is selected in the database and the email contains no attachments.
	 * If the API doesn't return a successful response, it sends emails through WordPress
	 *
	 * @param null|mixed $return
	 * @param array      $attrs
	 *
	 * @return mixed
	 */
	public function maybe_send_mail_via_api( $return, $attrs = array() ) {
		$email_service = tva_get_setting( 'email_service' );

		$send_via_api = false;
		$from_name    = get_bloginfo( 'name' );
		if ( did_action( 'tva_prepare_new_user_email_template' ) > 0 ) {
			$send_via_api = true;
			if ( ! empty( $this->_templates['newAccount'] ) && ! empty( $this->_templates['newAccount']['from_name'] ) ) {
				$from_name = $this->_templates['newAccount']['from_name'];
			}
		} elseif ( did_action( 'tva_prepare_certificate_email_template' ) > 0 ) {
			$send_via_api = true;
			if ( ! empty( $this->_templates['certificateIssued'] ) && ! empty( $this->_templates['certificateIssued']['from_name'] ) ) {
				$from_name = $this->_templates['certificateIssued']['from_name'];
			}
		} elseif ( did_action( 'tva_prepare_assessment_marked_email_template' ) > 0 ) {
			$send_via_api = true;
			if ( ! empty( $this->_templates[ self::ASSESSMENT_MARKED_TEMPLATE_SLUG ] ) && ! empty( $this->_templates[ self::ASSESSMENT_MARKED_TEMPLATE_SLUG ]['from_name'] ) ) {
				$from_name = $this->_templates[ self::ASSESSMENT_MARKED_TEMPLATE_SLUG ]['from_name'];
			}
		}

		if ( $send_via_api && empty( $attrs['attachments'] ) && ! empty( $email_service ) && $email_service !== 'own_site' ) {
			$api  = Thrive_List_Manager::connection_instance( $email_service );
			$data = array(
				'html_content' => $attrs['message'],
				'text_content' => strip_tags( $attrs['message'] ),
				'subject'      => $attrs['subject'],
				'from_name'    => $from_name,
				'from_email'   => get_option( 'admin_email' ),
				'bcc'          => '',
				'cc'           => '',
				'emails'       => is_string( $attrs['to'] ) ? array( $attrs['to'] ) : $attrs['to'],
				'email_tags'   => $this->get_tags()
			);

			$sent = false;

			if ( method_exists( $api, 'sendMultipleEmails' ) ) {
				$sent = $api->sendMultipleEmails( $data );
			}

			if ( $sent === true ) {
				/**
				 * If the email API returns success -> we stop emails sending through WordPress
				 */
				$return = false;
			}
		}

		return $return;
	}

	/**
	 * Localizes required data for admin
	 *
	 * @param array $data
	 *
	 * @return array mixed
	 */
	public function get_admin_data_localization( $data ) {

		$data['emailTemplates'] = array(
			self::NEW_ACCOUNT_TEMPLATE_SLUG        => array(
				'slug'      => self::NEW_ACCOUNT_TEMPLATE_SLUG,
				'name'      => esc_html__( 'New Account Created', 'thrive-apprentice' ),
				'subject'   => $this->_get_template_subject( self::NEW_ACCOUNT_TEMPLATE_SLUG ),
				'from_name' => $this->_get_template_from_name( self::NEW_ACCOUNT_TEMPLATE_SLUG ),
				'body'      => $this->_get_template_body( self::NEW_ACCOUNT_TEMPLATE_SLUG ),
				'triggers'  => $this->_get_template_triggers( self::NEW_ACCOUNT_TEMPLATE_SLUG ),
			),
			self::CERTIFICATE_ISSUED_TEMPLATE_SLUG => array(
				'slug'      => self::CERTIFICATE_ISSUED_TEMPLATE_SLUG,
				'name'      => esc_html__( 'Certificate manually issued', 'thrive-apprentice' ),
				'subject'   => $this->_get_template_subject( self::CERTIFICATE_ISSUED_TEMPLATE_SLUG ),
				'from_name' => $this->_get_template_from_name( self::CERTIFICATE_ISSUED_TEMPLATE_SLUG ),
				'body'      => $this->_get_template_body( self::CERTIFICATE_ISSUED_TEMPLATE_SLUG ),
				'triggers'  => $this->_get_template_triggers( self::CERTIFICATE_ISSUED_TEMPLATE_SLUG ),
			),
			self::ASSESSMENT_MARKED_TEMPLATE_SLUG  => array(
				'slug'      => self::ASSESSMENT_MARKED_TEMPLATE_SLUG,
				'name'      => esc_html__( 'Assessment Marked', 'thrive-apprentice' ),
				'subject'   => $this->_get_template_subject( self::ASSESSMENT_MARKED_TEMPLATE_SLUG ),
				'from_name' => $this->_get_template_from_name( self::ASSESSMENT_MARKED_TEMPLATE_SLUG ),
				'body'      => $this->_get_template_body( self::ASSESSMENT_MARKED_TEMPLATE_SLUG ),
				'triggers'  => $this->_get_template_triggers( self::ASSESSMENT_MARKED_TEMPLATE_SLUG ),
			),
			static::PRODUCT_ACCESS_EXPIRE          => [
				'slug'      => static::PRODUCT_ACCESS_EXPIRE,
				'name'      => esc_html__( 'Product access is expiring', 'thrive-apprentice' ),
				'subject'   => $this->_get_template_subject( static::PRODUCT_ACCESS_EXPIRE ),
				'from_name' => $this->_get_template_from_name( static::PRODUCT_ACCESS_EXPIRE ),
				'body'      => $this->_get_template_body( static::PRODUCT_ACCESS_EXPIRE ),
				'triggers'  => $this->_get_template_triggers( static::PRODUCT_ACCESS_EXPIRE ),
			],
		);

		return $data;
	}

	/**
	 * Singleton instance
	 *
	 * @return TVA_Email_Templates
	 */
	public static function get_instance() {

		if ( ! isset( static::$instance ) ) {
			static::$instance = new self();
		}

		return static::$instance;
	}

	/**
	 * Tear down function that destroy the instance
	 *
	 * @return void
	 */
	public static function tear_down() {
		static::$instance = null;
	}

	/**
	 * Call this when necessary:
	 * - new Thrive Cart Orders takes place
	 * - new account was created on registration page
	 * - new user is registered over WP Connection on LG Element
	 * - executes do_action() with a specified template for later process
	 *
	 * @param array $email_template
	 *
	 * @see prepare_new_user_email_template()
	 *
	 */
	public function trigger_process( $email_template ) {
		do_action( 'tva_prepare_new_user_email_template', $email_template );
	}

	/**
	 * On LG Submit if the connection is WordPress checks if there is an email template
	 * triggered for WordPress connection, if yes then execute trigger_process()
	 * and after the user is saved hooks onto `tvd_after_create_wordpress_account` action to send new registration email
	 *
	 * @param array                                 $data from LG Element
	 * @param Thrive_Dash_List_Connection_Wordpress $connection_instance
	 *
	 * @return mixed
	 */
	public function trigger_wp_new_registration( $data, $connection_instance ) {

		if ( false === $connection_instance instanceof Thrive_Dash_List_Connection_Wordpress ) {
			return $data;
		}

		//if there is any email template set for new wp user registration
		$email_template = tva_email_templates()->check_templates_for_trigger( 'wordpress' );
		if ( false !== $email_template ) {
			if ( ! empty( $data['password'] ) ) {
				$email_template['user_pass'] = $data['password'];
			}
			tva_email_templates()->trigger_process( $email_template );
		}

		return $data;
	}

	/**
	 * When password field is sent from LG notification process is not triggered so we have to do it here
	 *
	 * @param WP_User $user
	 * @param array   $arguments
	 */
	public function after_create_wordpress_account( $user, $arguments ) {

		$email_template = tva_email_templates()->check_templates_for_trigger( 'wordpress' );

		if ( false !== $email_template && isset( $arguments['password'] ) ) {
			wp_send_new_user_notifications( $user->ID );
		}
	}

	public function get_tags() {
		$tags = array();
		if ( ! empty( $this->_course ) ) {
			$tags[] = $this->_course;
		}

		if ( ! empty( $this->_product ) ) {
			$tags[] = $this->_product;
		}

		return $tags;
	}
}
