<?php
/**
 * Created by PhpStorm.
 * User: Andrei
 * Date: 5/3/2019
 * Time: 5:25 PM
 */

/**
 * Class TVA_Sendowl_Settings
 *
 * Manage all sendowl related settings
 */
class TVA_Sendowl_Settings {

	/**
	 * Name for checkout page option
	 */
	const CHECKOUT_PAGE = 'tva_checkout_page';

	/**
	 * Name for thankyou page option
	 */
	const THANKYOU_PAGE = 'tva_thankyou_page';

	/**
	 * Name for thankyou page type option
	 */
	const THANKYOU_PAGE_TYPE = 'tva_thankyou_page_type';

	/**
	 * Name for the page used as thankyou page for multiple courses access
	 */
	const TH_MULTIPLE_PAGE = 'tva_thankyou_multiple_page';

	/**
	 * Holds the option which checks if create products tutorial is completed
	 */
	const TUTORIAL_COMPLETED = 'tva_tutorial_completed';

	/**
	 * Holds the option which checks if the thankyou tutorial notice should be displayed
	 */
	const SHOW_THANKYOU_TUTORIAL = 'tva_show_thankyou_tutorial';

	/**
	 * Holds the welcome message to be displayed after a purchase
	 */
	const WELCOME_MESSAGE = 'tva_welcome_message';

	/**
	 * Holds a minimal representation of a page used by sendowl
	 *
	 * @var array
	 */
	public static $default_page
		= array(
			'ID'   => '',
			'name' => '',
		);

	/**
	 * @var string
	 */
	public $th_page_type = 'static';

	/**
	 * @var array
	 */
	private $_data = array();

	/**
	 * @var
	 */
	protected static $instance;

	/**
	 * TVA_Sendowl_Settings constructor.
	 */
	public function __construct() {
		$this->_set_data();
		$this->hooks();
	}

	public function hooks() {
		add_filter( 'tva_admin_localize', array( $this, 'localize' ) );
	}

	/**
	 * @return array
	 */
	public function get_settings() {
		return $this->_data;
	}

	/**
	 * @return TVA_Sendowl_Settings
	 */
	public static function instance() {

		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		/**
		 * Even though is_connected is false for those early calls
		 * - for later calls, e.g. on localize, try to set the data again, when is_connected might be true
		 *
		 * - this hack might be applicable for those users who had set the SendOwl accounts before the new admin interface was rewritten
		 *
		 * @see TVA\TTB\Palette::__construct() //tva_get_settings_manager() instantiates the current class too early
		 */
		if ( false === self::$instance->_data['is_connected'] ) {
			self::$instance->_set_data();
		}

		return self::$instance;
	}

	/**
	 * Set instance data
	 */
	private function _set_data() {

		$this->_data['is_connected'] = TVA_SendOwl::is_connected();

		if ( false === $this->_data['is_connected'] ) {
			return;
		}

		$_data = array(
			'thankyou_page_type'     => $this->_get_option( self::THANKYOU_PAGE_TYPE, $this->th_page_type ),
			'checkout_page'          => $this->_get_option( self::CHECKOUT_PAGE, self::$default_page ),
			'thankyou_page'          => $this->_get_option( self::THANKYOU_PAGE, self::$default_page ),
			'thankyou_multiple_page' => $this->_get_option( self::TH_MULTIPLE_PAGE, self::$default_page ),
			'account_keys'           => TVA_SendOwl::get_account_keys(),
		);

		/**
		 * We on;y need this in frontend
		 */
		if ( ! is_admin() ) {
			$_data['thankyou_page']['message'] = $this->_get_option( self::WELCOME_MESSAGE, $this->get_default_welcome_msg() );
		}

		$this->_data = wp_parse_args( $this->_data, $_data );
	}

	/**
	 * @return bool|mixed
	 */
	public function get_th_message() {

		return $this->_get_option( self::WELCOME_MESSAGE, $this->get_default_welcome_msg() );
	}

	/**
	 * Get an option from db
	 *
	 * @param      $option
	 * @param bool $default
	 *
	 * @return bool|mixed
	 */
	private function _get_option( $option, $default = false ) {
		$_value = get_option( $option, $default );

		/**
		 * Force the returned value to be of the same type as default one
		 */
		if ( false !== $default && gettype( $_value ) !== gettype( $default ) ) {
			$_value = $default;
		}

		return $_value;
	}

	/**
	 * Checks if $post is the post set as sendowl checkout page
	 *
	 * @param WP_Post|int $post
	 *
	 * @return bool
	 */
	public function is_checkout_page( $post = null ) {

		return $this->_is_sendowl_page( self::CHECKOUT_PAGE, $post );
	}

	/**
	 * Checks if $post is the post set as sendowl thankyou page
	 *
	 * @param WP_Post|int $post
	 *
	 * @return bool
	 */
	public function is_thankyou_page( $post = null ) {

		return $this->_is_sendowl_page( self::THANKYOU_PAGE, $post );
	}

	/**
	 * Checks if $post is the post set as sendowl thankyou page
	 *
	 * @param WP_Post|int $post
	 *
	 * @return bool
	 */
	public function is_thankyou_multiple_page( $post = null ) {

		return $this->_is_sendowl_page( self::TH_MULTIPLE_PAGE, $post );
	}

	/**
	 * Check if a given page is one of those used by sendowl based on the provided key
	 *
	 * @param string               $page_key
	 * @param WP_Post|int|stdClass $page
	 *
	 * @return bool
	 */
	private function _is_sendowl_page( $page_key, $page = null ) {

		if ( false === $this->_data['is_connected'] ) {
			return false;
		}

		$page = null !== $page ? $page : get_post();

		$allowed_keys = array(
			self::THANKYOU_PAGE    => 'thankyou_page',
			self::CHECKOUT_PAGE    => 'checkout_page',
			self::TH_MULTIPLE_PAGE => 'thankyou_multiple_page',
		);

		if ( ! array_key_exists( $page_key, $allowed_keys ) ) {
			return false;
		}

		if ( $page instanceof WP_Post || $page instanceof stdClass ) {
			return isset( $page->ID ) && (int) $page->ID === (int) $this->_data[ $allowed_keys[ $page_key ] ]['ID'];
		}

		if ( is_int( $page ) ) {
			return $page === (int) $this->_data[ $allowed_keys[ $page_key ] ]['ID'];
		}

		return false;
	}

	/**
	 * Localize sendowl related settings
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public function localize( $data ) {

		if ( false === $this->_data['is_connected'] ) {
			$data['data']['sendowl'] = $this->_data;

			return $data;
		}

		$_settings = $this->_get_pages_data();

		$_settings['customers_per_page']     = TVA_Const::SENDOWL_CUSTOMERS_PER_PAGE;
		$_settings['customers_per_request']  = TVA_Const::SENDOWL_CUSTOMER_PER_REQUEST;
		$_settings['customers_count']        = TVA_User::count_sendowl_customers();
		$_settings['tutorial_completed']     = $this->_get_option( self::TUTORIAL_COMPLETED, false );
		$_settings['show_thankyou_tutorial'] = (int) $this->_get_option( self::SHOW_THANKYOU_TUTORIAL, '1' );
		$_settings['preview_msg_url']        = $this->_get_preview_msg_url();

		$_settings = wp_parse_args( $_settings, $this->_get_products() );

		$data['data']['sendowl'] = $_settings;

		return $data;
	}

	/**
	 * Get all data needed for pages used by sendowl
	 *
	 * @return array
	 */
	private function _get_pages_data() {
		$_settings         = $this->get_settings();
		$tcb_plugin_active = is_plugin_active( 'thrive-visual-editor/thrive-visual-editor.php' );

		$_settings['tcb_plugin_active'] = $tcb_plugin_active;
		$_settings['welcome_message']   = array( 'message' => $this->_get_option( self::WELCOME_MESSAGE, $this->get_default_welcome_msg() ) );

		$_settings['checkout_page']['edit_url']    = tva_get_editor_url( $_settings['checkout_page']['ID'] );
		$_settings['checkout_page']['edit_text']   = __( 'Edit with Thrive Architect', 'thrive-apprentice' );
		$_settings['checkout_page']['preview_url'] = get_permalink( $_settings['checkout_page']['ID'] );

		$_settings['thankyou_page']['preview_url'] = get_permalink( $_settings['thankyou_page']['ID'] );
		$_settings['thankyou_page']['edit_text']   = $tcb_plugin_active
			? __( 'Edit with Thrive Architect', 'thrive-apprentice' )
			: __( 'Edit', 'thrive-apprentice' );
		$_settings['thankyou_page']['edit_url']    = $tcb_plugin_active
			? tva_get_editor_url( $_settings['thankyou_page']['ID'] )
			: get_edit_post_link( $_settings['thankyou_page']['ID'] );

		$_settings['thankyou_multiple_page']['preview_url'] = get_permalink( $_settings['thankyou_multiple_page']['ID'] );
		$_settings['thankyou_multiple_page']['edit_text']   = $tcb_plugin_active
			? __( 'Edit with Thrive Architect', 'thrive-apprentice' )
			: __( 'Edit', 'thrive-apprentice' );
		$_settings['thankyou_multiple_page']['edit_url']    = $tcb_plugin_active
			? tva_get_editor_url( $_settings['thankyou_multiple_page']['ID'] )
			: get_edit_post_link( $_settings['thankyou_multiple_page']['ID'] );

		return $_settings;
	}

	/**
	 * Get products and bundles
	 *
	 * @return array
	 */
	private function _get_products() {
		$_data     = $this->_get_option( 'tva_sendowl_products', array() );
		$_products = TVA_Products_Collection::make( $_data );

		return array(
			'products' => array_values( $_products->get_products()->get_items() ),
			'bundles'  => array_values( $_products->get_bundles()->get_items() ),
		);
	}

	/**
	 * Get the default message to be displayed after a purchase
	 *
	 * @return string
	 */
	public function get_default_welcome_msg() {
		return '<p>Thank You!</p><p>Your purchase is complete and you now have access to [course_name]</p>';
	}

	/**
	 * Get the url where welcome message will be previewed
	 *
	 * @return string
	 */
	private function _get_preview_msg_url() {

		$courses = tva_get_courses( array( 'private' => true ) );
		$url     = tva_get_settings_manager()->factory( 'index_page' )->get_link();

		if ( ! empty( $courses ) ) {
			$_url = get_term_link( $courses[0], TVA_Const::COURSE_TAXONOMY );
			$url  = $_url instanceof WP_Error ? $url : $_url;
		}

		return $url;
	}
}
