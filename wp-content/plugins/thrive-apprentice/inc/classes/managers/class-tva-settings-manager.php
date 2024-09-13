<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TVA_Settings_Manager
 *
 * @project  : thrive-apprentice
 */
class TVA_Settings_Manager implements JsonSerializable {

	/**
	 * Class Instance
	 *
	 * @var TVA_Settings_Manager
	 */
	private static $_instance;

	/**
	 * Location for overloading instances
	 *
	 * @var array
	 */
	private $_data = array();

	/**
	 * TVA_Settings_Manager constructor.
	 */
	private function __construct() {
		add_filter( 'tva_admin_localize', array( $this, 'localize' ) );
	}

	/**
	 * Localize settings
	 *
	 * @param array $data
	 *
	 * @return array|mixed
	 */
	public function localize( $data = array() ) {
		$data['settings'] = $this->jsonSerialize();

		return $data;
	}

	/**
	 * Returns the setting instance
	 *
	 * @return TVA_Settings_Manager
	 */
	public static function get_instance() {
		if ( empty( static::$_instance ) ) {
			static::$_instance = new self();
		}

		return static::$_instance;
	}

	/**
	 * @return string[]
	 */
	public function get_keys() {
		return array(
			'per_page'                    => 'general',
			'index_page'                  => 'general',
			'load_scripts'                => 'general',
			'auto_login'                  => 'general',
			'skip_homepage_redirect'      => 'general',
			'loginform'                   => 'general',
			'apprentice_label'            => 'general',
			'register_page'               => 'general',
			'comment_status'              => 'general',
			'email_service'               => 'emails',
			'wizard'                      => 'wizard',
			'visual_editor_welcome'       => 'visual_editor',
			'share_ttb_color'             => 'visual_editor',
			'template'                    => 'template',
			'preview_option'              => 'template',
			'account_keys'                => 'integration',
			'login_page'                  => 'page',
			'thankyou_page'               => 'page',
			'checkout_page'               => 'page',
			'thankyou_multiple_page'      => 'page',
			'completed_post_page'         => 'page',
			'thankyou_page_type'          => 'sendowl',
			'welcome_message'             => 'sendowl',
			'certificate_validation_page' => 'page',
			'certificate_verification'    => 'general',
		);
	}

	/**
	 * Factory for Settings
	 *
	 * @param $name
	 *
	 * @return TVA_Page_Setting|TVA_Setting
	 */
	public function factory( $name ) {

		if ( empty( $this->_data[ $name ] ) ) {
			$class_name = 'TVA_Setting';
			if ( in_array( $name, $this->pages_indexes() ) ) {
				$class_name = 'TVA_Page_Setting';
			}

			$keys = $this->get_keys();

			$this->_data[ $name ] = new $class_name( $name, $keys[ $name ] );
		}

		return $this->_data[ $name ];
	}

	/**
	 * @return array
	 */
	public function pages_indexes() {

		return array(
			'index_page',
			'register_page',
			'login_page',
			'thankyou_page',
			'checkout_page',
			'thankyou_multiple_page',
			'certificate_validation_page',
			'completed_post_page',
		);
	}

	/**
	 * @param array $args
	 *
	 * @return false|int|WP_Error
	 */
	public function create( $args = array() ) {

		if ( isset( $args['name'] ) && in_array( $args['name'], $this->pages_indexes() ) ) {
			return $this->factory( $args['name'] )->add( $args );
		}

		return false;
	}

	/**
	 * Returns the factory class to Array
	 *
	 * @param $name
	 *
	 * @return array
	 */
	public function get_setting_array( $name ) {
		return $this->factory( $name )->to_array();
	}

	/**
	 * Saves a setting
	 *
	 * @param string $name
	 * @param mixed  $value
	 *
	 * @return bool
	 */
	public function save_setting( $name, $value ) {
		$names = array_keys( $this->get_keys() );

		if ( in_array( $name, $names ) ) {
			return $this->factory( $name )->set_value( $value );
		}

		return false;
	}

	/**
	 * @return array
	 */
	#[\ReturnTypeWillChange]
	public function jsonSerialize() {

		$return = array();
		$keys   = $this->get_keys();

		foreach ( $keys as $key => $options ) {
			$return[ $key ] = $this->get_setting_array( $key );
		}

		return $return;
	}

	/**
	 * Localize Setting Values - Used in front end
	 *
	 * @return array
	 */
	public function localize_values() {
		$names = array_keys( $this->get_keys() );

		$values = array();

		foreach ( $names as $name ) {
			$values[ $name ] = $this->get_setting( $name );
		}

		return $values;
	}

	/**
	 * Returns a value for a particular setting
	 *
	 * @param $name
	 *
	 * @return mixed|null
	 */
	public function get_setting( $name ) {
		return $this->factory( $name )->get_value();
	}

	/**
	 * Check if given page is Apprentice login page
	 *
	 * @param null $page
	 *
	 * @return bool
	 */
	public function is_login_page( $page = null ) {

		return $this->is_ta_page( 'login_page', $page );
	}

	/**
	 * Check if given page is Apprentice index page
	 *
	 * @param null $page
	 *
	 * @return bool
	 */
	public function is_index_page( $page = null ) {

		return $this->is_ta_page( 'index_page', $page );
	}

	/**
	 * Checks if given page is a page where users can verify their certificate
	 *
	 * @param int|WP_Post $page to be checked
	 *
	 * @return bool
	 */
	public function is_certificate_validation_page( $page = null ) {

		return $this->is_ta_page( 'certificate_validation_page', $page );
	}

	/**
	 * Check if given page is Apprentice index page
	 *
	 * @param null $page
	 *
	 * @return bool
	 */
	public function is_register_page( $page = null ) {

		return $this->is_ta_page( 'register_page', $page );
	}

	/**
	 * Checks if $post is the post set as sendowl checkout page
	 *
	 * @param WP_Post|int $post
	 *
	 * @return bool
	 */
	public function is_checkout_page( $post = null ) {

		return $this->is_ta_page( 'checkout_page', $post );
	}

	/**
	 * Checks if $post is the post set as sendowl thankyou page
	 *
	 * @param WP_Post|int $post
	 *
	 * @return bool
	 */
	public function is_thankyou_page( $post = null ) {

		return $this->is_ta_page( 'thankyou_page', $post );
	}

	/**
	 * Checks if $post is the post set as sendowl thankyou page for multiple courses access
	 *
	 * @param WP_Post|int $post
	 *
	 * @return bool
	 */
	public function is_thankyou_multiple_page( $post = null ) {

		return $this->is_ta_page( 'thankyou_multiple_page', $post );
	}

	/**
	 * Returns true if the post is a system page
	 *
	 * @param null $post
	 *
	 * @return bool
	 */
	public function is_system_page( $post = null ) {

		return $this->is_login_page( $post ) ||
			   $this->is_index_page( $post ) ||
			   $this->is_register_page( $post ) ||
			   $this->is_checkout_page( $post ) ||
			   $this->is_thankyou_page( $post ) ||
			   $this->is_thankyou_multiple_page( $post ) ||
			   $this->is_certificate_validation_page( $post );
	}

	/**
	 * Check if a given page is one of those used by TA based on the provided key
	 *
	 * @param string                    $key
	 * @param WP_Post|int|stdClass|null $page
	 *
	 * @return bool
	 */
	private function is_ta_page( $key, $page = null ) {

		$page = null !== $page ? $page : get_post();

		if ( ! in_array( $key, $this->pages_indexes() ) ) {
			return false;
		}

		if ( $page instanceof WP_Post || $page instanceof stdClass ) {
			return isset( $page->ID ) && (int) $page->ID === $this->factory( $key )->get_value();
		}

		if ( is_int( $page ) ) {
			return ! empty( $page ) && $page === (int) $this->factory( $key )->get_value();
		}

		return false;
	}

	/**
	 * @param int $page_id
	 *
	 * Insert Login Element on a given page
	 */
	public function add_login_element( $page_id = 0 ) {

		$cloud_element = tcb_elements()->element_factory( 'login' );
		$cloud_data    = $cloud_element->get_cloud_template_data( 60345, array( 'type' => 'login' ) );
		$save_post     = get_post_meta( $page_id, 'tve_save_post', true );
		$updated_post  = get_post_meta( $page_id, 'tve_updated_post', true );

		if ( ! is_wp_error( $cloud_data ) && strpos( $updated_post, 'thrv-login-element' ) === false ) {
			$content = empty( $cloud_data['content'] ) ? tcb_template( 'elements/login.php', array(), true ) : $cloud_data['content'];
			/* create a default form settings instance */
			$default_config = array(
				'v'           => 1,
				'apis'        => array( 'wordpress' => 'subscriber' ),
				'captcha'     => 0,
				'extra'       => array(),
				'custom_tags' => array(),
			);
			$form_settings  = TCB\inc\helpers\FormSettings::get_one( get_option( 'tva_registration_settings', null ) );
			$form_settings->set_config( $default_config );
			$form_settings->save( 'Auto-generated registration form settings' );
			if ( ! is_wp_error( $form_settings->ID ) ) {
				update_option( 'tva_registration_settings', (int) $form_settings->ID );

				/* process $content in order to correctly create a settings instance for the form */
				$content = preg_replace( '#data-settings-id="(.+?)"#', "data-settings-id=\"{$form_settings->ID}\"", $content );
			}

			$content = str_replace( 'data-type="login"', 'data-type="both"', $content );
			$content = urldecode_deep( $content );

			$save_post    .= $content;
			$updated_post .= $content;
			$head_css     = ! empty( $cloud_data['head_css'] ) ? $cloud_data['head_css'] : '';

			update_post_meta( $page_id, 'tve_save_post', $save_post );
			update_post_meta( $page_id, 'tve_updated_post', $updated_post );
			update_post_meta( $page_id, 'tve_custom_css', $head_css );
		}
	}

	/**
	 * Add the Certificate Verification Element on a post/page sent as param
	 *
	 * @param int $page_id for Certificate Verification Page
	 *
	 * @return void
	 */
	public function add_certificate_validation_element( $page_id ) {

		$page_id = (int) $page_id;

		if ( ! $page_id ) {
			return;
		}

		$updated_post = get_post_meta( $page_id, 'tve_updated_post', true );
		//if the page already has the certificate validation element, don't add it again
		if ( strpos( $updated_post, 'tva-certificate-verification-element' ) !== false ) {
			return;
		}

		$template = tve_get_cloud_template_data(
			'certificate_verification',
			array(
				'id'   => 'default',
				'type' => 'certificate_verification',
			)
		);

		if ( ! is_wp_error( $template ) ) {
			update_post_meta( $page_id, 'tve_save_post', $template['content'] );
			update_post_meta( $page_id, 'tve_updated_post', $template['content'] );
			update_post_meta( $page_id, 'tve_custom_css', $template['head_css'] );
		}
	}

	/**
	 * @param int $page_id
	 * Insert Checkout Element on a given page
	 */
	public function add_checkout_element( $page_id = 0 ) {

		ob_start();
		include( TVA_Const::plugin_path( '/tcb-bridge/editor-layouts/elements/checkout.php' ) );

		$element = ob_get_contents();

		$save_post    = get_post_meta( $page_id, 'tve_save_post', true );
		$updated_post = get_post_meta( $page_id, 'tve_updated_post', true );

		if ( strpos( $updated_post, 'thrv-checkout' ) === false ) {
			$save_post    .= $element;
			$updated_post .= $element;
			update_post_meta( $page_id, 'tve_save_post', $save_post );
			update_post_meta( $page_id, 'tve_updated_post', $updated_post );
		}

		ob_end_clean();
	}
}
