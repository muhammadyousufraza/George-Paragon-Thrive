<?php
/**
 * Privacy/GDPR related functionality which ties into WordPress functionality.
 *
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 5/9/2018
 * Time: 4:57 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

/**
 * Class Thrive_Leads_Privacy
 */
class Thrive_Leads_Privacy {

	/**
	 * Add the privacy hooks
	 */
	public static function init() {
		/**
		 * We need to ensure we're using a version of WP with GDPR support.
		 * @since 4.9.6
		 */
		if ( ! function_exists( 'wp_privacy_anonymize_data' ) ) {
			return;
		}

		/**
		 * Registers Thrive Leads Exporter to WordPress Privacy Data Exporter
		 *
		 * Goal: Makes Thrive Leads GDPR Compliant
		 */
		add_filter( 'wp_privacy_personal_data_exporters', array( __CLASS__, 'register_privacy_exporter' ), 10 );

		/**
		 * Registers Thrive Leads Eraser to WordPress Privacy Data Eraser
		 *
		 * Goal: Makes Thrive Leads GDPR Compliant
		 */
		add_filter( 'wp_privacy_personal_data_erasers', array( __CLASS__, 'register_privacy_eraser' ), 10 );
	}

	/**
	 * Registers Thrive Leads Exporter to WordPress Privacy Data Exporter in order to make the plugin GDPR Compliant
	 *
	 * @param array $exporters
	 *
	 * @return array
	 */
	public static function register_privacy_exporter( $exporters = array() ) {
		$exporters[] = array(
			'exporter_friendly_name' => __( 'Thrive Leads', 'thrive-leads' ),
			'callback'               => array( __CLASS__, 'privacy_exporter' ),
		);

		return $exporters;
	}

	/**
	 * Registers Thrive Leads Eraser to WordPress Privacy Data Eraser in order to make the plugin GDPR Compliant
	 *
	 * @param array $erasers
	 *
	 * @return array
	 */
	public static function register_privacy_eraser( $erasers = array() ) {
		$erasers[] = array(
			'eraser_friendly_name' => __( 'Thrive Leads', 'thrive-leads' ),
			'callback'             => array( __CLASS__, 'privacy_eraser' ),
		);

		return $erasers;
	}

	/**
	 * Thrive Leads - Exporter Function
	 *
	 * @param     $email_address
	 * @param int $page
	 *
	 * @return array
	 */
	public static function privacy_exporter( $email_address, $page = 1 ) {
		$export_items = array();
		global $tvedb;

		$contacts = $tvedb->tve_get_contact_by_email( $email_address );

		if ( ! empty( $contacts ) ) {
			$group_id    = 'tve-leads-user-privacy';
			$group_label = __( 'Thrive Leads', 'thrive-leads' );

			foreach ( $contacts as $contact ) {
				$item_id       = $contact['id'] . '-' . $email_address;
				$custom_fields = json_decode( $contact['custom_fields'], true );

				$data = array(
					array(
						'name'  => __( 'Name', 'thrive-leads' ),
						'value' => $contact['name'],
					),
					array(
						'name'  => __( 'Email', 'thrive-leads' ),
						'value' => $contact['email'],
					),
					array(
						'name'  => __( 'Date and Start Time', 'thrive-leads' ),
						'value' => date( 'Y-m-d H:i:s', strtotime( $contact['date'] ) ),
					),
					array(
						'name'  => __( 'Form Preview', 'thrive-leads' ),
						'value' => sprintf( '<a href="%s" target="_blank">%s</a>', tve_leads_get_preview_url( $contact['form_type_id'], $contact['variation_key'] ), $contact['source'] ),
					),
				);

				if ( ! empty( $custom_fields ) && is_array( $custom_fields ) ) {

					$data[] = array(
						'name'  => __( 'Raw submitted data', 'thrive-leads' ),
						'value' => str_replace( array( 'Array', '(', ')' ), '', print_r( $custom_fields, true ) ),
					);

					$data[] = array(
						'name'  => __( 'Checkbox State', 'thrive-leads' ),
						'value' => ( ! empty( $custom_fields['user_consent'] ) && 1 === intval( $custom_fields['user_consent'] ) ) ? __( 'Ticked', 'thrive-leads' ) : __( 'Unticked', 'thrive-leads' ),
					);

					$data[] = array(
						'name'  => __( 'Phone', 'thrive-leads' ),
						'value' => ( ! empty( $custom_fields['phone'] ) ) ? $custom_fields['phone'] : '-',
					);
				}

				$export_items[] = array(
					'group_id'    => $group_id,
					'group_label' => $group_label,
					'item_id'     => $item_id,
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
	 * Thrive Leads - Eraser Function
	 *
	 * @param     $email_address
	 * @param int $page
	 *
	 * @return array
	 */
	public static function privacy_eraser( $email_address, $page = 1 ) {
		$response = array(
			'items_removed'  => false,
			'items_retained' => false,
			'messages'       => array(),
			'done'           => true,
		);

		if ( empty( $email_address ) ) {
			return $response;
		}

		global $tvedb;
		$contacts = $tvedb->tve_get_contact_by_email( $email_address );

		if ( ! empty( $contacts ) ) {
			foreach ( $contacts as $contact ) {
				$tvedb->delete_contact_from_db( $contact );
			}

			$response['items_removed'] = true;
			$response['messages']      = array( sprintf( '%s rows from Thrive Leads plugin were deleted in order to remove personal data', count( $contacts ) ) );
		}

		return $response;
	}
}

Thrive_Leads_Privacy::init();
