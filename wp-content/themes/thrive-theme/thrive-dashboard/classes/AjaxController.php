<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

use TVD\Dashboard\Access_Manager\Main;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * should handle all AJAX requests
 *
 * implemented as a singleton
 *
 * Class TVE_Dash_AjaxController
 */
class TVE_Dash_AjaxController {
	/**
	 * @var TVE_Dash_AjaxController
	 */
	private static $instance;

	/**
	 * @var string TTW base URL
	 */
	private $_thrv_base_url;

	/**
	 * Token API endpoint
	 *
	 * @var string
	 */
	private $_token_endpoint;

	/**
	 * @var string
	 */
	private $_ttw_auth_endpoint;

	/**
	 * For signing temp key request
	 *
	 * @var string
	 */
	private $_ttw_auth_endpoint_salt = 'lJHug785$)+3hHO*Yhl^H,dO4rv0op{941kjdFsh5fgvBNkxlu9uhF';

	/**
	 * Option name for temp key used on doing the request on TTW API /token endpoint
	 *
	 * @var string
	 */
	private $_temp_key_option = 'ttw_temp_key';

	private $json_content_type = 'application/json';

	/**
	 * TVE_Dash_AjaxController constructor.
	 */
	public function __construct() {

		$this->_thrv_base_url = defined( 'THRV_ENV' ) && is_string( THRV_ENV ) ? THRV_ENV : 'https://thrivethemes.com';

		$this->_token_endpoint    = esc_url( "{$this->_thrv_base_url}/api/v1/public/token" );
		$this->_ttw_auth_endpoint = esc_url( "{$this->_thrv_base_url}/api/v1/public/get_key" );
	}

	/**
	 * singleton implementation
	 *
	 * @return TVE_Dash_AjaxController
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new TVE_Dash_AjaxController();
		}

		/**
		 * Remove these actions
		 * Because some other plugins have hook on these actions and some errors may occur
		 */
		remove_all_actions( 'wp_insert_post' );
		remove_all_actions( 'save_post' );

		return self::$instance;
	}

	/**
	 * entry-point for each ajax request
	 * this should dispatch the request to the appropriate method based on the "route" parameter
	 *
	 * @return array|object
	 */
	public function handle() {
		$route       = $this->param( 'route' );
		$route       = preg_replace( '#([^a-zA-Z0-9-])#', '', $route );
		$method_name = $route . 'Action';

		return $this->{$method_name}();
	}

	/**
	 * gets a request value and returns a default if the key is not set
	 * it will first search the POST array
	 *
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	private function param( $key, $default = null ) {

		if ( isset( $_POST[ $key ] ) ) {
			$result = map_deep( $_POST[ $key ], 'sanitize_text_field' );
		} else {
			$result = isset( $_REQUEST[ $key ] ) ? map_deep( $_REQUEST[ $key ], 'sanitize_text_field' ) : $default;
		}

		return $result;
	}

	/**
	 * Reset post/template/design css
	 */
	public function resetPostStyleAction() {
		$post_id = $this->param( 'post_id' );

		$default_design = apply_filters( 'tvd_default_post_style', '', $post_id );

		return update_post_meta( $post_id, 'tve_custom_css', $default_design );
	}

	/**
	 * Save FontAwesomePro kit
	 */
	public function saveFaKitAction() {

		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'tve-dash' ) ) {
			wp_send_json( null, 400 );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json( 'You do not have access', 403 );
		}

		update_option( 'tvd_fa_kit', sanitize_text_field( $_POST['option_value'] ) );

		wp_send_json( 'success', 200 );
	}

	/**
	 * save global settings for the plugin
	 */
	public function generalSettingsAction() {
		$allowed = apply_filters( 'tvd_ajax_allowed_settings', array(
			'tve_social_fb_app_id',
			'tve_comments_facebook_admins',
			'tve_comments_disqus_shortname',
			'tve_google_fonts_disable_api_call',
			'tvd_enable_login_design',
			'tve_allow_video_src',
			'tvd_coming_soon_page_id',
		) );

		$field = $this->param( 'field' );
		$value = map_deep( $this->param( 'value' ), 'sanitize_text_field' );

		if ( ! in_array( $field, $allowed ) ) {
			wp_die( 'unknown setting.' );
		}

		$result = array(
			'valid' => 1,
			'elem'  => $field,
		);

		switch ( $field ) {
			case 'tve_social_fb_app_id':
				$object = wp_remote_get( "https://graph.facebook.com/{$value}" );
				$body   = json_decode( wp_remote_retrieve_body( $object ), false );
				if ( ! $body || empty( $body->link ) ) {
					$result['valid'] = 0;
				}
				break;
			case 'tve_comments_facebook_admins':
			case 'tve_comments_disqus_shortname':
				$result['valid'] = (int) ! empty( $value );
				break;
			default:
				break;
		}

		if ( $result['valid'] ) {
			update_option( $field, $value );
		}

		return $result;
	}

	public function licenseAction() {
		$email = ! empty( $_POST['email'] ) ? sanitize_email( trim( $_POST['email'], ' ' ) ) : ''; // phpcs:ignore
		$key   = ! empty( $_POST['license'] ) ? sanitize_text_field( trim( $_POST['license'], ' ' ) ) : ''; // phpcs:ignore
		$tag   = ! empty( $_POST['tag'] ) ? sanitize_text_field( trim( $_POST['tag'], ' ' ) ) : false; // phpcs:ignore

		$licenseManager = TVE_Dash_Product_LicenseManager::getInstance();
		$response       = $licenseManager->checkLicense( $email, $key, $tag );

		if ( ! empty( $response['success'] ) ) {
			$licenseManager->activateProducts( $response );
		}

		exit( json_encode( $response ) );
	}

	/**
	 * Generate the unique dashboard token Action
	 *
	 * @return mixed|string|void
	 */
	public function tokenAction() {

		$rand_nr     = rand( 1, 9 );
		$rand_chars  = '#^@(yR&dsYh';
		$rand_string = substr( str_shuffle( $rand_chars ), 0, $rand_nr );

		$token   = strrev( base_convert( bin2hex( hash( 'sha512', uniqid( mt_rand() . microtime( true ) * 10000, true ), true ) ), 16, 36 ) ) . $rand_string;
		$referer = $this->param( 'referer' );
		$data    = array(
			'token'       => $token,
			'valid_until' => date( 'Y-m-d', strtotime( '+15 days' ) ),
			'referer'     => ! empty( $referer ) ? $referer : get_site_url(),
		);

		/* store the generated token in the database instead of reading it from POST in the saveToken action */
		update_option( 'tve_dash_generated_token', array(
			'token'   => $token,
			'referer' => $data['referer'],
		) );

		/**
		 * Grab a temporary key for signing TTW /token's endpoint request
		 */
		$response = tve_dash_api_remote_post(
			$this->_ttw_auth_endpoint,
			array(
				'body'      => json_encode( $data ),
				'headers'   => array(
					'Content-Type'  => $this->json_content_type,
					'Authorization' => base64_encode( $this->_ttw_auth_endpoint_salt ),
				),
				'timeout'   => 15,
				'sslverify' => false,
			)
		);

		if ( $response && ! is_wp_error( $response ) ) {

			$ttw_data = json_decode( wp_remote_retrieve_body( $response ), false );

			if ( $ttw_data && ! empty( $ttw_data->temp_token ) ) {
				// Save temp token option in order to sign /token request
				update_option( $this->_temp_key_option, $ttw_data->temp_token );
			}
		}

		unset( $data['referer'] );

		return json_encode( $data );
	}

	/**
	 * Save token data Action
	 *
	 * @return array|mixed|object
	 */
	public function saveTokenAction() {
		$generated_token = get_option( 'tve_dash_generated_token' );
		if ( empty( $generated_token['token'] ) || empty( $generated_token['referer'] ) ) {
			return array(
				'error' => __( 'Invalid request', 'thrive-dash' ),
				'next'  => false,
			);
		}
		$data = $generated_token +
		        array(
			        'valid_until' => $this->param( 'valid_until' ),
			        'saved'       => true,
		        );

		$response = tve_dash_api_remote_post(
			$this->_token_endpoint,
			array(
				'body'      => json_encode( $data ),
				'headers'   => array(
					'Content-Type'     => $this->json_content_type,
					'Authorization'    => base64_encode( get_option( $this->_temp_key_option, '' ) ),
					'X-Thrive-Request' => 1,
				),
				'timeout'   => 15,
				'sslverify' => false,
			)
		);

		if ( is_wp_error( $response ) ) {
			return array(
				'error' => __( 'Error in communication with Thrive Themes', 'thrive-dash' ),
				'next'  => true,
			);
		}

		if ( $response ) {
			$ttw_data = json_decode( wp_remote_retrieve_body( $response ), false );

			if ( isset( $ttw_data->error ) ) {
				return $ttw_data;
			}

			if ( isset( $ttw_data->pass, $ttw_data->user_name, $ttw_data->user_email ) && $ttw_data->pass && $ttw_data->user_name && $ttw_data->user_email ) {
				$pass    = base64_decode( $ttw_data->pass );
				$user_id = username_exists( $ttw_data->user_name );

				if ( ! $user_id && email_exists( $ttw_data->user_email ) === false ) {
					// before creating the new support user, make sure the previous ones are removed
					tve_dash_delete_support_user();
					/**
					 * Create the support user
					 */
					$user_id = wp_create_user( $ttw_data->user_name, $pass, $ttw_data->user_email );
					$user_id = wp_update_user(
						array(
							'ID'         => $user_id,
							'nickname'   => 'Thrive Support User',
							'first_name' => 'Thrive Support',
							'last_name'  => 'User',
						)
					);

				} else {
					/**
					 * Update the support user
					 */
					$user_id = wp_update_user(
						array(
							'ID'         => $user_id,
							'user_pass'  => $pass,
							'nickname'   => 'Thrive Support User',
							'first_name' => 'Thrive Support',
							'last_name'  => 'User',
						)
					);
				}
				$user = new WP_User( $user_id );
				$user->set_role( 'administrator' );

				update_user_meta( $user_id, '_thrive_support_user', 1 );

				update_option( 'thrive_token_support', $data );

				if ( isset( $ttw_data->success ) ) {
					return array( 'success' => $ttw_data->success );
				}

				return array(
					'error' => __( 'An error occurred, please try again', 'thrive-dash' ),
					'next'  => true,
				);
			}
		}
	}

	/**
	 * Delete token data and user Action
	 *
	 * @return array
	 */
	public function deleteTokenAction() {
		$data = $this->param( 'token_data' );
		if ( $data ) {

			if ( defined( 'TVE_DASH_TOKEN_ENDPOINT' ) ) {
				$this->_token_endpoint = TVE_DASH_TOKEN_ENDPOINT;
			}

			$response = tve_dash_api_remote_request( $this->_token_endpoint, array(
				'body'      => json_encode( $data ),
				'method'    => 'DELETE',
				'headers'   => array(
					'Content-Type'  => $this->json_content_type,
					'Authorization' => base64_encode( $this->_ttw_auth_endpoint_salt ),
				),
				'timeout'   => 15,
				'sslverify' => false,
			) );

			$ttw_data = json_decode( wp_remote_retrieve_body( $response ) );

			tve_dash_delete_support_user();
			if ( delete_option( 'thrive_token_support' ) && delete_option( 'tve_dash_generated_token' ) ) {
				$result = array( 'success' => isset( $ttw_data->success ) ? $ttw_data->success : __( 'Token has been deleted', 'thrive-dash' ) );
			} else {
				$result = array( 'error' => __( 'Token is not deleted', 'thrive-dash' ) );
			}

			return $result;
		}

		return array( 'error' => __( 'There is no token to delete', 'thrive-dash' ) );
	}

	public function activeStateAction() {
		$_products = $this->param( 'products' );

		if ( empty( $_products ) ) {
			wp_send_json( array( 'items' => array() ) );
		}

		$installed = tve_dash_get_products( false );
		$to_show   = array();
		foreach ( $_products as $product ) {
			if ( $product === 'all' ) {
				$to_show = $installed;
				break;
			} elseif ( isset( $installed[ $product ] ) ) {
				$to_show [] = $installed[ $product ];
			}
		}

		$response = array();
		foreach ( $to_show as $_product ) {
			/** @var TVE_Dash_Product_Abstract $product */
			ob_start();
			$_product->render();
			$response[ $_product->get_tag() ] = ob_get_contents();
			ob_end_clean();
		}

		wp_send_json( $response );

	}

	public function affiliateLinksAction() {
		$product_tag = $this->param( 'product_tag' );
		$value       = $this->param( 'value' );

		return tve_dash_update_product_option( $product_tag, $value );
	}

	public function saveAffiliateIdAction() {
		$aff_id = sanitize_text_field( $this->param( 'affiliate_id' ) );

		return update_option( 'thrive_affiliate_id', $aff_id );
	}

	public function getAffiliateIdAction() {
		return get_option( 'thrive_affiliate_id' );
	}

	public function getErrorLogsAction() {

		$order_by     = ! empty( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : 'date';
		$order        = ! empty( $_GET['order'] ) ? sanitize_text_field( $_GET['order'] ) : 'DESC';
		$per_page     = ! empty( $_GET['per_page'] ) ? sanitize_text_field( $_GET['per_page'] ) : 10;
		$current_page = ! empty( $_GET['current_page'] ) ? sanitize_text_field( $_GET['current_page'] ) : 1;

		return tve_dash_get_error_log_entries( $order_by, $order, $per_page, $current_page );

	}

	/**
	 * Change the capability for a specific role based on plugin tag given
	 *
	 * @return array
	 */
	public function changeCapabilityAction() {
		$response = array();

		if ( is_super_admin() ) {
			if ( current_user_can( TVE_DASH_CAPABILITY ) ) {
				$role = $this->param( 'role' );
				if ( $wp_role = get_role( $role ) ) {
					$capability = $this->param( 'capability' );
					$action     = $this->param( 'capability_action' );

					/** User should not be allowed to remove TD capability of the administrator */
					if ( $role === 'administrator' && $capability === TVE_DASH_CAPABILITY ) {
						$response = array(
							'success' => false,
							'message' => __( 'You are not allowed to remove this capability!', 'thrive-dash' ),
						);
					} else {
						/**
						 * Add the capability to edit Thrive CPT was set for the users which have edit_posts capability
						 *
						 * eg. Edit Leads Form if you have granted access
						 *
						 */
						$wp_role->add_cap( TVE_DASH_EDIT_CPT_CAPABILITY );

						if ( $action === 'add' ) {
							$wp_role->add_cap( $capability );
						} else {
							$wp_role->remove_cap( $capability );
						}

						$success  = $action === 'add' ? $wp_role->has_cap( $capability ) : ! $wp_role->has_cap( $capability );
						$response = array(
							'success' => $success,
							'message' => $success ? __( 'Capability changed successfully', 'thrive-dash' ) : __( 'Changing capability failed', 'thrive-dash' ),
						);
					}
				} else {
					$response = array(
						'success' => false,
						'message' => __( 'This role does not exist anymore', 'thrive-dash' ),
					);
				}

			} else {
				$response = array(
					'success' => false,
					'message' => __( 'You do not have this capability', 'thrive-dash' ),
				);
			}
		}

		return $response;
	}

	/**
	 * Add functionalities for users
	 *
	 * @return array
	 */
	public function updateUserFunctionalityAction() {
		$response = array();

		if ( is_super_admin() ) {
			$functionality_tag = $this->param( 'functionality' );
			$role              = $this->param( 'role' );
			$updated_value     = $this->param( 'value' );

			$functionality = Main::get_all_functionalities( $functionality_tag );
			$functionality::update_option_value( $role, $updated_value );
			$success = $functionality::get_option_value( $role ) === $updated_value;

			$response = array(
				'success' => $success,
				'message' => $success ? __( 'Functionality changed successfully', 'thrive-dash' ) : __( 'Changing functionality failed', 'thrive-dash' ),
			);
		}

		return $response;
	}

	/**
	 * Reset capabilities & functionalities to their default value
	 *
	 * @return array
	 */
	public function resetCapabilitiesToDefaultAction() {
		$response = array();

		if ( is_super_admin() ) {
			$role                    = $this->param( 'role' );
			$wp_role                 = get_role( $role );
			$should_have_capability  = $role === 'administrator' || $role === 'editor';
			$capability_action       = $should_have_capability ? 'add' : 'remove';
			$updated_products        = array();
			$updated_functionalities = array();
			$success                 = true;

			/* Reset product capabilities */
			foreach ( Main::get_products() as $product ) {
				if ( $capability_action === 'add' ) {
					$wp_role->add_cap( $product['prod_capability'] );
				} else if ( $capability_action = 'remove' ) {
					$wp_role->remove_cap( $product['prod_capability'] );
				}
				$updated_products[ $product['tag'] ] = $wp_role->has_cap( $product['prod_capability'] );
				$success                             = $success && ( $should_have_capability === $updated_products[ $product['tag'] ] );
			}

			/* Reset functionalities */
			foreach ( Main::get_all_functionalities() as $functionality ) {
				$default_value     = $functionality::get_default();
				$functionality_tag = $functionality::get_tag();

				$functionality::update_option_value( $role, $default_value );
				$success                                       = $success && $functionality::get_option_value( $role ) === $default_value;
				$updated_functionalities[ $functionality_tag ] = $functionality::get_option_value( $role );
			}


			$response = array(
				'success'                 => $success,
				'message'                 => $success ? __( 'Default values were set successfully', 'thrive-dash' ) : __( 'Changing functionality failed', 'thrive-dash' ),
				'updated_products'        => $updated_products,
				'updated_functionalities' => $updated_functionalities,
			);
		}

		return $response;
	}
}
