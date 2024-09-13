<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 5/11/2018
 * Time: 12:00
 */

/**
 * Class TVA_Privacy
 */
class TVA_Privacy {

	/**
	 * Privacy hooks
	 */
	public function __construct() {

		/* add comment subscribers data on personal data export */
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
			'exporter_friendly_name' => __( 'Thrive Apprentice', 'thrive-apprentice' ),
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
			'eraser_friendly_name' => __( 'Thrive Apprentice', 'thrive-apprentice' ),
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
		$export_items     = array();
		$args             = tva_get_courses_args( array( 'published' ) );
		$args['meta_key'] = 'tva_term_subscribers';
		$terms            = new WP_Term_Query( $args );

		if ( ! is_wp_error( $terms ) && ! empty( $terms->terms ) ) {
			foreach ( $terms->terms as $term ) {
				$subscribers = get_term_meta( $term->term_id, 'tva_term_subscribers', true );

				if ( in_array( $email_address, $subscribers ) ) {
					$export_items[] = array(
						'group_id'    => 'comments-user-privacy',
						'group_label' => __( 'Subscribed to comments for a course from Thrive Apprentice', 'thrive-apprentice' ),
						'item_id'     => $term->term_id,
						'data'        => array(
							array(
								'name'  => __( 'Visitor Email', 'thrive-apprentice' ),
								'value' => $email_address,
							),
							array(
								'name'  => __( 'Course Url', 'thrive-apprentice' ),
								'value' => get_term_link( $term, TVA_Const::COURSE_TAXONOMY ),
							),
						),
					);
				}
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

		$count            = 0;
		$args             = tva_get_courses_args( array( 'published' ) );
		$args['meta_key'] = 'tva_term_subscribers';
		$terms            = new WP_Term_Query( $args );

		if ( ! is_wp_error( $terms ) && ! empty( $terms->terms ) ) {
			foreach ( $terms->terms as $term ) {
				$subscribers = get_term_meta( $term->term_id, 'tva_term_subscribers', true );

				if ( in_array( $email_address, $subscribers ) ) {

					unset( $subscribers[ array_search( $email_address, $subscribers ) ] );

					update_term_meta( $term->term_id, 'tva_term_subscribers', $subscribers );

					$count ++;
				}
			}

			if ( $count ) {
				$response['items_removed'] = true;
				$response['messages']      = array( sprintf( '%s users email were removed from being subscribed to courses comments.', $count ) );
			}
		}

		return $response;
	}
}
