<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 4/10/2019
 * Time: 10:39
 */

class TVA_Thankyou {

	protected $endpoint_name = 'thrv_thankyou';

	/**
	 * Name for last seen course cookie
	 */
	const LAST_PURCHASED_PRODUCT = 'last_purchased_product';

	public function __construct() {
		$this->hooks();
	}

	protected function hooks() {

		add_action( 'init', array( $this, 'add_endpoint' ) );
		add_action( 'template_redirect', array( $this, 'endpoint_redirect' ) );
		add_filter( 'tva_admin_localize', array( $this, 'admin_localize' ) );
	}

	/**
	 * add new endpoint to wp rewrite endpoints
	 * so that users would be redirected to the thankyou page set by the admin
	 */
	public function add_endpoint() {
		if ( false === TVA_SendOwl::is_connected() ) {
			return;
		}

		add_rewrite_endpoint( $this->endpoint_name, EP_ALL );
	}

	/**
	 * Generates full site url for the endpoint
	 *
	 * @return string
	 */
	public function get_endpoint_url() {

		$permalink = get_option( 'permalink_structure' );
		$glue      = $permalink ? '' : '?';

		$url = home_url( $glue . $this->endpoint_name );

		return $url;
	}

	/**
	 * Injects the thankyou endpoint into localized data
	 *
	 * @param $data array
	 *
	 * @return array
	 */
	public function admin_localize( $data ) {

		$data['data']['settings']['thankyou_endpoint'] = $this->get_endpoint_url();

		return $data;
	}

	/**
	 * redirects to checkout page set if user access the endpoint
	 */
	public function endpoint_redirect() {

		/** @var $wp_query WP_Query */
		global $wp_query;

		if ( ! isset( $wp_query->query_vars[ $this->endpoint_name ] ) || ! TVA_SendOwl::is_connected() ) {
			return;
		}

		$product_id = (int) TVA_Cookie_Manager::get_cookie( self::LAST_PURCHASED_PRODUCT );
		$url        = $this->get_redirect_url( $product_id );

		TVA_Cookie_Manager::remove_cookie( self::LAST_PURCHASED_PRODUCT );

		wp_redirect( $url );
		exit();
	}

	/**
	 * Get the redirect url after a purchase from sendowl
	 *
	 * @param int $product_id
	 *
	 * @return string
	 */
	public function get_redirect_url( $product_id = 0 ) {
		$thankyou_page_type = tva_get_settings_manager()->factory( 'thankyou_page_type' )->get_value();
		$index_url          = tva_get_settings_manager()->factory( 'index_page' )->get_link();
		$redirect_url       = empty( $index_url ) ? home_url( '/' ) : $index_url;

		if ( 0 === $product_id ) {
			return $redirect_url;
		}

		switch ( $thankyou_page_type ) {
			case 'static':
				$_url         = tva_get_settings_manager()->factory( 'thankyou_page' )->get_link();
				$redirect_url = ! empty( $_url ) ? $_url : $redirect_url;
				break;

			case 'redirect':
				$redirect_url = $this->_get_redirect_url( $product_id );

				break;

			default:
				break;
		}

		return $redirect_url;
	}

	/**
	 * Get the url where the user should be redirected after a purchase on sendowl
	 *
	 * @param $product_id
	 *
	 * @return string
	 */
	private function _get_redirect_url( $product_id ) {
		$products     = TVA_Sendowl_Manager::get_products_that_have_protection( (int) $product_id );
		$redirect_url = tva_get_settings_manager()->factory( 'index_page' )->get_link();

		if ( empty( $products ) ) {
			return $redirect_url;
		}

		$thank_you_page_url = tva_get_settings_manager()->factory( 'thankyou_multiple_page' )->get_link();
		if ( empty( $thank_you_page_url ) ) {
			$thank_you_page_url = $redirect_url;
		}

		if ( count( $products ) > 1 ) {
			return $thank_you_page_url;
		}

		$product = reset( $products );
		$courses = $product->get_courses( false );

		if ( empty( $courses ) ) {
			return $redirect_url;
		}

		if ( count( $courses ) > 1 ) {
			/**
			 * If a product contains more than 1 course, redirect to thank you multiple page
			 */
			return $thank_you_page_url;
		}

		$course = reset( $courses );

		return $course->get_link();
	}
}
