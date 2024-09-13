<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Dash_List_Connection_Ovation extends Thrive_Dash_List_Connection_Abstract {
	/**
	 * Return the connection type
	 *
	 * @return String
	 */
	public static function get_type() {
		return 'testimonial';
	}

	/**
	 * @return string the API connection title
	 */
	public function get_title() {
		return 'Thrive Ovation';
	}

	/**
	 * check whether or not the Thrive Ovation plugin is installed
	 */
	public function is_plugin_installed() {
		return function_exists( 'tvo_plugin_init' );
	}

	public function read_credentials() {
		if ( ! $this->is_plugin_installed() ) {
			return $this->error( __( 'Thrive Ovation must be installed and activated.', 'thrive-ovation' ) );
		}

		$this->set_credentials( $this->post( 'connection', array() ) );

		$result = $this->test_connection();

		if ( $result !== true ) {
			return $this->error( '<strong>' . $result . '</strong>)' );
		}
		/**
		 * finally, save the connection details
		 */
		$this->save();

		return true;
	}

	/**
	 * test if a connection can be made to the service using the stored credentials
	 *
	 * @return bool|string true for success or error message for failure
	 */
	public function test_connection() {
		if ( ! $this->is_plugin_installed() ) {
			return __( 'Thrive Ovation must be installed and activated.', 'thrive-ovation' );
		}

		return true;
	}

	/**
	 * instantiate the API code required for this connection
	 *
	 * @return null
	 */
	protected function get_api_instance() {
		// no API instance needed here
		return null;
	}

	protected function get_ovation_tags( $arguments ) {
		$tags    = empty( $arguments ) ? [] : explode( ',', $arguments['ovation_tags'] );
		$tag_ids = [];
		foreach ( $tags as $tag ) {
			$result = get_term_by( 'name', $tag, TVO_TESTIMONIAL_TAG_TAXONOMY, ARRAY_A );
			if ( ! empty( $result ) ) {
				$tag_ids[] = $result['term_id'];
			} else {
				$new_term = tvo_save_testimonial_tag( [ 'name' => $tag ] );
				if ( $new_term['status'] === 'ok' ) {
					$tag_ids[] = $new_term['tag']['term_id'];
				}
			}
		}

		return $tag_ids;
	}

	/**
	 * add a contact to a list
	 *
	 * @param mixed $list_identifier
	 * @param array $arguments
	 *
	 * @return mixed
	 */
	public function add_subscriber( $list_identifier, $arguments ) {

		if ( function_exists( 'tvo_create_testimonial' ) ) {
			$testimonial = [
				'content' => '',
				'tags'    => $this->get_ovation_tags( $arguments ),
			];

			foreach ( $arguments as $key => $value ) {

				foreach ( $this->_mapped_custom_fields as $field ) {
					if ( strpos( $key, $field['id'] ) !== false ) {
						if ( $field['id'] === 'question' ) {
							$testimonial['content'] .= wpautop( sanitize_textarea_field( $value, true ) );
						} else {
							$testimonial[ $field['id'] ] = $value;
						}

					}
				}
				foreach ( $this->_default_form_fields as $field ) {
					if ( $key === $field['id'] ) {
						$testimonial[ $field['id'] ] = $value;
					}
				}
			}
			$testimonial['source'] = TVO_SOURCE_DIRECT_CAPTURE;
			$testimonial['status'] = TVO_STATUS_AWAITING_REVIEW;

			$result = tvo_create_testimonial( $testimonial );
			if ( $result['status'] == 'ok' ) {
				/* Trigger action on testimonial added through capture form */
				do_action( TVO_ACTION_TESTIMONIAL_ADDED_CAPTURE_FORM, $result['testimonial'], array(
					'source' => $testimonial['source'],
					'url'    => admin_url( 'admin.php?page=tvo_admin_dashboard#testimonials/' . $result['testimonial']['id'] ),
				) );

				if ( is_user_logged_in() ) {
					$user = wp_get_current_user();
				} else {
					$user = get_user_by( 'email', $testimonial['email'] );
				}

				/**
				 * The hook is triggered when a user submits a testimonial through Thrive Ovation. The hook can be fired multiple times, as the user can leave multiple testimonials.
				 * </br>
				 * Example use case:-  Give students access to a bonus course after they have submitted a testimonial.
				 * </br>
				 * <b>Note:</b> This parameter will provide the user details only if the user is logged in. It will not provide the name/email used when the user submits a testimonial.
				 *
				 * @param array Testimonial Details
				 * @param null|array User Details
				 *
				 * @api
				 */
				do_action( 'thrive_ovation_testimonial_submit', tvo_get_testimonial_details( $result['testimonial']['id'], $arguments['post_id'] ), $user );

				return true;
			} else {
				return new WP_Error( 'code', __( 'Something went wrong while trying to send data. Please try again.', 'thrive-ovation' ) );
			}
		} else {
			return new WP_Error( 'code', __( 'Thrive Ovation is not installed or activated on website', 'thrive-ovation' ) );
		}

	}

	public function output_setup_form() {
		$this->output_controls_html( 'ovation' );
	}

	protected function _get_lists() {
		return null;
	}

	/**
	 * Mapped custom fields setter
	 */
	protected function set_custom_fields_mapping() {

		$this->_mapped_custom_fields =
			array(
				array(
					'id'          => 'role',
					'placeholder' => __( 'Role', 'thrive-ovation' ),
					'unique'      => true,
				),
				array(
					'id'          => 'website_url',
					'placeholder' => __( 'Website URL', 'thrive-ovation' ),
					'unique'      => true,
				),
				array(
					'id'          => 'title',
					'placeholder' => __( 'Title', 'thrive-ovation' ),
					'unique'      => true,
				),
				array(
					'id'          => 'question',
					'placeholder' => __( 'Testimonial', 'thrive-ovation' ),
					'unique'      => false,
				),
				array(
					'id'          => 'mapping_hidden',
					'placeholder' => __( 'Hidden', 'thrive-ovation' ),
					'unique'      => false,
				),
				array(
					'id'          => 'mapping_avatar_picker',
					'placeholder' => __( 'Avatar picker', 'thrive-ovation' ),
					'unique'      => true,
				),
			);
	}

	protected function set_custom_default_fields_mapping() {
		$this->_default_form_fields =
			array(
				array(
					'id'          => 'name',
					'placeholder' => __( 'Name', 'thrive-ovation' ),
					'unique'      => true,
					'mandatory'   => true,
				),
				array(
					'id'          => 'email',
					'placeholder' => __( 'Email', 'thrive-ovation' ),
					'unique'      => true,
					'mandatory'   => false,
				),
			);
	}

	/**
	 * This cannot be deleted
	 *
	 * @return bool
	 */
	public function can_delete() {
		return false;
	}

	/**
	 * This cannot be edited
	 *
	 * @return bool
	 */
	public function can_edit() {
		return false;
	}

	/**
	 * This cannot be tested
	 *
	 * @return bool
	 */
	public function can_test() {
		return false;
	}
}
