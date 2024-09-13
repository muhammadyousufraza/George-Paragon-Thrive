<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-comments
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Thrive_Comments_Privacy
 */
class Thrive_Comments_Privacy {

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
			'exporter_friendly_name' => __( 'Thrive Comments', Thrive_Comments_Constants::T ),
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
			'eraser_friendly_name' => __( 'Thrive Comments', Thrive_Comments_Constants::T ),
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
		$post_types   = array( 'post', 'page' );
		$posts        = get_posts( array(
			'post_type'      => apply_filters( 'tcm_privacy_post_types', $post_types ),
			'posts_per_page' => - 1,
			'meta_query'     => array(
				array(
					'key'     => 'tcm_post_subscribers',
					'value'   => '"' . $email_address . '"',
					'compare' => 'LIKE',
				),
			),
		) );

		if ( ! empty( $posts ) ) {
			foreach ( $posts as $post ) {
				if ( in_array( $email_address, $post->tcm_post_subscribers ) ) {
					$label_text     = 'Subscribed to comments for a post';
					$label_text     = apply_filters( 'tcm_label_privacy_text', $label_text, $post );
					$export_items[] = array(
						'group_id'    => 'comments-user-privacy',
						'group_label' => __( $label_text, Thrive_Comments_Constants::T ),
						'item_id'     => $post->ID,
						'data'        => array(
							array(
								'name'  => __( 'Visitor Email', Thrive_Comments_Constants::T ),
								'value' => $email_address,
							),
							array(
								'name'  => __( 'Post Url', Thrive_Comments_Constants::T ),
								'value' => get_permalink( $post->ID ),
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

		$count      = 0;
		$post_types = array( 'post', 'page' );
		$posts      = get_posts( array(
			'post_type'      => apply_filters( 'tcm_privacy_post_types', $post_types ),
			'posts_per_page' => - 1,
			'meta_query'     => array(
				array(
					'key'     => 'tcm_post_subscribers',
					'value'   => '"' . $email_address . '"',
					'compare' => 'LIKE',
				),
			),
		) );

		if ( ! empty( $posts ) ) {
			foreach ( $posts as $post ) {
				$subscribers = $post->tcm_post_subscribers;
				if ( in_array( $email_address, $subscribers ) ) {

					unset( $subscribers[ array_search( $email_address, $subscribers ) ] );

					update_post_meta( $post->ID, 'tcm_post_subscribers', $subscribers );

					$count ++;
				}
			}

			if ( $count ) {
				$response['items_removed'] = true;
				$response['messages']      = array( sprintf( '%s users email were removed from being subscribed to posts comments.', $count ) );
			}
		}

		return $response;
	}
}

new Thrive_Comments_Privacy();
