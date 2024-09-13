<?php

class TCB_User_Profile_Handler {

	const DEFAULT_FIELDS = [
		'user_email',
		'username',
		'nickname',
		'first_name',
		'last_name',
		'display_name',
		'website',
		'user_bio',
		'pass1',
		'pass2',
	];
	/**
	 * Config attribute separator
	 */
	const SEP = '__TCB_UP__';

	const SHORTCODE = 'tve_user_profile';

	const NONCE = 'tve-user-profile-nonce';

	public static function init() {
		static::hooks();
	}

	public static function hooks() {
		add_shortcode( static::SHORTCODE, [ __CLASS__, 'render_shortcode' ] );

		add_action( 'wp_ajax_tve_user_profile_update', [ __CLASS__, 'handle_update_data' ] );
		add_filter( 'tve_thrive_shortcodes', [ __CLASS__, 'thrive_shortcodes' ], 10, 2 );
		add_filter( 'tcb.content_pre_save', [ __CLASS__, 'handle_content_pre_save' ], 10, 2 );
		add_filter( 'tcb_content_allowed_shortcodes', [ __CLASS__, 'content_allowed_shortcodes_filter' ] );

		add_filter( 'tve_frontend_options_data', [ __CLASS__, 'frontend_options_data' ], 10, 2 );
	}

	/**
	 * On frontend contexts, always remove the config
	 */
	public static function thrive_shortcodes( $content, $is_editor_page ) {
		if ( ! ( $is_editor_page || strpos( $content, static::SEP ) === false ) ) {
			return preg_replace( static::pattern( true ), '', $content );
		}

		return $content;
	}

	/**
	 * Add nonce to frontend options
	 *
	 * @param array $data The frontend options data
	 *
	 * @return array The updated frontend options data with nonce added
	 */
	public static function frontend_options_data( $data ) {
		$data['user_profile_nonce'] = wp_create_nonce( static::NONCE );

		return $data;
	}

	/**
	 * Handle the ajax request and validate fields
	 */
	public static function handle_update_data() {
		if ( ! wp_verify_nonce( $_REQUEST['form-nonce'], static::NONCE ) ) {
			wp_send_json( [
				'errors'  => __( 'Unauthorized access.', 'thrive-cb' ),
				'success' => false,
			] );
		}

		if ( current_user_can( 'manage_options' ) ) {
			wp_send_json( [
				'errors'  => __( "Admins can't update profile data using the profile element.", 'thrive-cb' ),
				'success' => false,
			] );
		}

		$data     = [];
		$response = [ 'success' => true ];

		/**
		 * Check whether or not the form settings exist
		 */
		if ( empty( $_POST['form_id'] ) || ! $config = get_option( sanitize_text_field( $_POST['form_id'] ) ) ) {
			$response['errors'] = __( 'Form settings validation failed', 'thrive-cb' );
		} else {
			$config = json_decode( wp_unslash( $config ) );

			if ( ! empty( $config->fields ) ) {

				foreach ( static::DEFAULT_FIELDS as $field ) {

					if ( ! empty( $_POST[ $field ] ) && in_array( $field, $config->fields ) ) {
						if ( $field === 'user_bio' ) {
							$data[ $field ] = sanitize_textarea_field( $_POST[ $field ] );
						} else {
							$data[ $field ] = sanitize_text_field( $_POST[ $field ] );
						}
					}
				}
			}
		}

		if ( empty( $data ) ) {
			$response['success'] = false;
		} else {
			$required_fields_fulfilled = true;

			/**
			 * Double-check for required fields
			 */
			if ( ! empty( $config ) && ! empty( $config->required ) ) {
				foreach ( $config->required as $field ) {
					if ( empty( $data[ $field ] ) ) {
						$required_fields_fulfilled = false;
					}
				}
			}
			if ( $required_fields_fulfilled ) {
				$update_response = static::update( $data );
				if ( is_wp_error( $update_response ) ) {
					$response['success'] = false;
					$response['errors']  = $update_response;
				}
			} else {
				$response['success'] = false;
				$response['errors']  = __( 'Missing required fields', 'thrive-cb' );
			}
		}
		wp_send_json( $response );
	}

	/**
	 * Update data function
	 * Based on wp function edit_user
	 *
	 * @param $data
	 *
	 * @return int|mixed|string|WP_Error
	 */
	public static function update( $data ) {
		$current_user = wp_get_current_user();

		if ( empty( $current_user->data ) || empty( $current_user->data->ID ) ) {
			return '';
		}

		$user_id = (int) $current_user->data->ID;

		$user = new stdClass;

		$user->ID         = $user_id;
		$userdata         = get_userdata( $user_id );
		$user->user_login = wp_slash( $userdata->user_login );

		$pass1 = '';

		if ( isset( $data['pass1'] ) ) {
			$pass1 = trim( $data['pass1'] );
		}
		if ( isset( $data['pass2'] ) ) {
			$pass2 = trim( $data['pass2'] );
		}

		if ( isset( $data['user_email'] ) ) {
			$user->user_email = $data['user_email'];
		}
		if ( isset( $data['website'] ) ) {
			if ( empty( $data['website'] ) || 'http://' === $data['website'] ) {
				$user->user_url = '';
			} else {
				$user->user_url = esc_url_raw( $data['website'] );
				$protocols      = implode( '|', array_map( 'preg_quote', wp_allowed_protocols() ) );
				$user->user_url = preg_match( '/^(' . $protocols . '):/is', $user->user_url ) ? $user->user_url : 'http://' . $user->user_url;
			}
		}
		if ( isset( $data['first_name'] ) ) {
			$user->first_name = $data['first_name'];
		}
		if ( isset( $data['last_name'] ) ) {
			$user->last_name = $data['last_name'];
		}
		if ( isset( $data['nickname'] ) ) {
			$user->nickname = $data['nickname'];
		}
		if ( isset( $data['display_name'] ) ) {
			$user->display_name = $data['display_name'];
		}

		if ( isset( $data['user_bio'] ) ) {
			$user->description = trim( $data['user_bio'] );
		}

		$errors = new WP_Error();
		/**
		 * Fires before the password and confirm password fields are checked for congruity.
		 *
		 * @param string $user_login The username.
		 * @param string $pass1      The password (passed by reference).
		 * @param string $pass2      The confirmed password (passed by reference).
		 *
		 * @since 1.5.1
		 *
		 */
		do_action_ref_array( 'check_passwords', [ $user->user_login, &$pass1, &$pass2 ] );
		// Check for "\" in password.
		if ( false !== strpos( wp_unslash( $pass1 ), '\\' ) ) {
			$errors->add( 'pass_slash', __( '<strong>Error</strong>: Passwords may not contain the character "\\".', 'thrive-cb' ), [ 'form-field' => 'pass1' ] );
		}

		if ( ! empty( $pass1 ) ) {
			$user->user_pass = $pass1;
			$score           = tve_score_password( $pass1 );

			if ( $score <= 30 ) {
				$errors->add( 'password_score', __( 'Please choose a stronger password. Try including numbers, symbols, and a mix of upper and lowercase letters and remove common words.', 'thrive-cb' ), [ 'form-field' => 'pass1' ] );
			}

			// Checking the password has been typed twice the same.
			if ( ! empty( $pass2 ) && $pass1 != $pass2 ) {
				$errors->add( 'passwordmismatch', __( "Passwords don't match. Please enter the same password in both password fields." ), [ 'form-field' => 'pass1' ] );
			}
		}

		/* checking email address */
		if ( ! empty( $user->user_email ) ) {
			if ( ! is_email( $user->user_email ) ) {
				$errors->add( 'invalid_email', __( "The email address isn't correct.", 'thrive-cb' ), [ 'form-field' => 'email' ] );
			} else {
				$owner_id = email_exists( $user->user_email );
				if ( $owner_id && ( $owner_id != $user->ID ) ) {
					$errors->add( 'email_exists', __( 'This email is already registered. Please choose another one.', 'thrive-cb' ), [ 'form-field' => 'email' ] );
				}
			}
		}

		/**
		 * Fires before user profile update errors are returned.
		 *
		 * @param WP_Error $errors WP_Error object (passed by reference).
		 * @param bool     $update Whether this is a user update.
		 * @param stdClass $user   User object (passed by reference).
		 *
		 * @since 2.8.0
		 *
		 */
		do_action_ref_array( 'user_profile_update_errors', [ &$errors, true, &$user ] );

		if ( $errors->has_errors() ) {
			return $errors;
		}

		return wp_update_user( $user );
	}

	/**
	 * Build the regex pattern for matching form json configuration
	 *
	 * @param bool $with_attribute whether or not to also match the `data-form-settings` attribute
	 *
	 * @return string
	 */
	public static function pattern( $with_attribute = false ) {
		$regex = static::SEP . '(.+?)' . static::SEP;

		if ( $with_attribute ) {
			$regex = ' data-config="' . $regex . '"';
		}

		return "#{$regex}#s";
	}

	/**
	 * Only render the shortcode if the user logged
	 *
	 * @param $attr
	 * @param $content
	 * @param $tag
	 *
	 * @return string
	 */
	public static function render_shortcode( $attr, $content ) {
		$html = '';

		if ( is_user_logged_in() ) {
			$html = TCB_Utils::wrap_content( $content, 'div', '', 'thrv_wrapper tve-user-profile tcb-local-vars-root', $attr );
		}

		return $html;
	}

	/**
	 * Allow the shortcode to be rendered in the editor
	 *
	 * @param $shortcodes
	 *
	 * @return array
	 */
	public static function content_allowed_shortcodes_filter( $shortcodes ) {
		if ( is_editor_page_raw( true ) ) {
			$shortcodes[] = static::SHORTCODE;
		}

		return $shortcodes;
	}

	/**
	 * Handle user profile settings and store them as options
	 *
	 * @param $response
	 * @param $post_data
	 *
	 * @return mixed
	 */
	public static function handle_content_pre_save( $response, $post_data ) {

		if ( ! empty( $post_data['user_profile_forms'] ) ) {
			foreach ( $post_data['user_profile_forms'] as $form_id => $settings ) {
				if ( strncmp( $form_id, 'tve-up-', 7 ) === 0 ) {
					update_option( $form_id, $settings );
				}
			}
		}

		if ( ! empty( $post_data['user_profile_deleted_forms'] ) ) {
			foreach ( $post_data['user_profile_deleted_forms'] as $form_id ) {
				if ( strncmp( $form_id, 'tve-up-', 7 ) === 0 ) {
					delete_option( $form_id );
				}
			}
		}

		return $response;
	}
}

TCB_User_Profile_Handler::init();
