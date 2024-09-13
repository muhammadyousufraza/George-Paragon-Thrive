<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-ovation
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TVO_Privacy
 */
class TVO_Privacy {

	/**
	 * Privacy hooks
	 */
	public function __construct() {

		/* add ovation data on personal data export */
		add_filter( 'wp_privacy_personal_data_exporters', array( $this, 'personal_data_exporters' ), 10 );

		/* erase testimonials on email select */
		add_filter( 'wp_privacy_personal_data_erasers', array( $this, 'personal_data_erasers' ), 10 );
	}

	/**
	 * Return export data for personal information
	 *
	 * @param array $exporters
	 *
	 * @return array
	 */
	public function personal_data_exporters( $exporters = array() ) {

		$exporters[] = array(
			'exporter_friendly_name' => __( 'Thrive Ovation', 'thrive-ovation' ),
			'callback'               => array( $this, 'privacy_exporter' ),
		);

		return $exporters;
	}

	/**
	 * Erase personal data upon user request based on email
	 *
	 * @param array $erasers
	 *
	 * @return array
	 */
	public function personal_data_erasers( $erasers = array() ) {
		$erasers[] = array(
			'eraser_friendly_name' => __( 'Thrive Ovation', 'thrive-ovation' ),
			'callback'             => array( $this, 'privacy_eraser' ),
		);

		return $erasers;
	}

	/**
	 * Private data export function
	 *
	 * @param string $email_address
	 *
	 * @return array
	 */
	public function privacy_exporter( $email_address ) {
		$export_items = array();

		$testimonials = tvo_get_testimonials();

		if ( ! empty( $testimonials ) ) {

			foreach ( $testimonials as $testimonial ) {
				if ( $testimonial->_tvo_testimonial_attributes['email'] !== $email_address ) {
					continue;
				}

				$data = array(
					array(
						'name'  => __( 'Author Email', 'thrive-ovation' ),
						'value' => $email_address,
					),
					array(
						'name'  => __( 'Date', 'thrive-ovation' ),
						'value' => $testimonial->post_date,
					),
				);

				if ( ! empty( $testimonial->post_title ) ) {
					$data[] = array(
						'name'  => __( 'Title', 'thrive-ovation' ),
						'value' => $testimonial->post_title,
					);
				}

				if ( ! empty( $testimonial->post_content ) ) {
					$data[] = array(
						'name'  => __( 'Content', 'thrive-ovation' ),
						'value' => $testimonial->post_content,
					);
				}

				if ( ! empty( $testimonial->_tvo_testimonial_attributes['name'] ) ) {
					$data[] = array(
						'name'  => __( 'Author Name', 'thrive-ovation' ),
						'value' => $testimonial->_tvo_testimonial_attributes['name'],
					);
				}

				if ( ! empty( $testimonial->_tvo_testimonial_attributes['role'] ) ) {
					$data[] = array(
						'name'  => __( 'Role/Description', 'thrive-ovation' ),
						'value' => $testimonial->_tvo_testimonial_attributes['role'],
					);
				}

				if ( ! empty( $testimonial->_tvo_testimonial_attributes['website_url'] ) ) {
					$data[] = array(
						'name'  => __( 'Website', 'thrive-ovation' ),
						'value' => $testimonial->_tvo_testimonial_attributes['website_url'],
					);
				}

				if ( ! empty( $testimonial->_tvo_testimonial_attributes['picture_url'] ) ) {
					$data[] = array(
						'name'  => __( 'Picture', 'thrive-ovation' ),
						'value' => $testimonial->_tvo_testimonial_attributes['picture_url'],
					);
				}

				$export_items[] = array(
					'group_id'    => 'tvo-user-privacy',
					'group_label' => __( 'Testimonials', 'thrive-ovation' ),
					'item_id'     => $testimonial->ID,
					'data'        => $data,
				);
			}
		}

		return array(
			'data' => $export_items,
			'done' => true,
		);
	}

	/**
	 * Erase data on privacy request
	 *
	 * @param string $email_address
	 *
	 * @return array
	 */
	public function privacy_eraser( $email_address ) {
		$response = array(
			'items_removed'  => false,
			'items_retained' => false,
			'messages'       => array(),
			'done'           => true,
		);

		if ( empty( $email_address ) ) {
			return $response;
		}

		$testimonials = tvo_get_testimonials();
		$count        = 0;

		if ( ! empty( $testimonials ) ) {

			foreach ( $testimonials as $testimonial ) {
				if ( $testimonial->_tvo_testimonial_attributes['email'] === $email_address ) {
					wp_delete_post( $testimonial->ID );

					delete_post_meta( $testimonial->ID, TVO_POST_META_KEY );
					delete_post_meta( $testimonial->ID, TVO_STATUS_META_KEY );
					delete_post_meta( $testimonial->ID, TVO_SOURCE_META_KEY );

					$count ++;
				}
			}

			if ( $count ) {
				$response['items_removed'] = true;
				$response['messages']      = array( sprintf( '%s testimonials from Thrive Ovation were deleted in order to remove personal related data.', $count ) );
			}
		}


		return $response;
	}
}

new TVO_Privacy();
