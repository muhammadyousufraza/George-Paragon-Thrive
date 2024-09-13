<?php
/**
 * Created by PhpStorm.
 * User: dan bilauca
 * Date: 15-Feb-19
 * Time: 10:49 AM
 */

/**
 * Class TVA_Checkout
 *
 * Keeps logic for checkout process
 */
class TVA_Checkout {

	protected $endpoint_name = 'thrv_checkout';

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
	 * so that users would be redirected to the checkout page set by the admin
	 */
	public function add_endpoint() {
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
	 * redirects to checkout page set if user access the endpoint
	 */
	public function endpoint_redirect() {

		/** @var $wp_query WP_Query */
		global $wp_query;

		if ( isset( $wp_query->query_vars[ $this->endpoint_name ] ) ) {

			$destination  = tva_get_settings_manager()->factory( 'checkout_page' )->get_link();
			$query_string = array();

			$pp = ! empty( $_GET['pp'] ) ? sanitize_text_field( $_GET['pp'] ) : null;

			$pid = ! empty( $_GET['pid'] ) ? intval( sanitize_text_field( $_GET['pid'] ) ) : null;

			$bid = ! empty( $_GET['bid'] ) ? intval( sanitize_text_field( $_GET['bid'] ) ) : null;

			$discount_code = ! empty( $_GET['thrv_so_discount'] ) ? sanitize_text_field( $_GET['thrv_so_discount'] ) : null;

			if ( empty( $pp ) ) {
				return;
			}

			if ( empty( $pid ) && empty( $bid ) ) {
				return;
			}

			$query_string['pp'] = $pp;

			if ( ! empty( $pid ) ) {
				$query_string['pid'] = $pid;
			}

			if ( ! empty( $bid ) ) {
				$query_string['bid'] = $bid;
			}

			if ( ! empty( $discount_code ) ) {
				$query_string['thrv_so_discount'] = $discount_code;
			}

			if ( ! empty( $destination ) ) {

				$destination = add_query_arg( $query_string, $destination );

				wp_redirect( $destination );
				exit();
			}
		}
	}

	/**
	 * Injects the checkout endpoint into localized data
	 *
	 * @param $data array
	 *
	 * @return array
	 */
	public function admin_localize( $data ) {
		$data['checkout_endpoint'] = $this->get_endpoint_url();

		//@deprecated -> it should be deleted after the old functionality is deleted
		$data['data']['settings']['checkout_endpoint'] = $this->get_endpoint_url();

		return $data;
	}
}
