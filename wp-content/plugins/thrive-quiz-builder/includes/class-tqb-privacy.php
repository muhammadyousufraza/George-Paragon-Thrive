<?php
/**
 * Privacy/GDPR related functionality which ties into WordPress functionality.
 *
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 5/9/2018
 * Time: 1:52 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TQB_Privacy
 */
class TQB_Privacy {

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
		 * Registers Thrive Quiz Builder Exporter to WordPress Privacy Data Exporter
		 *
		 * Goal: Makes Thrive Quiz Builder GDPR Compliant
		 */
		add_filter( 'wp_privacy_personal_data_exporters', array( __CLASS__, 'register_privacy_exporter' ), 10 );

		/**
		 * Registers Thrive Quiz Builder Eraser to WordPress Privacy Data Eraser
		 *
		 * Goal: Makes Thrive Quiz Builder GDPR Compliant
		 */
		add_filter( 'wp_privacy_personal_data_erasers', array( __CLASS__, 'register_privacy_eraser' ), 10 );
	}

	/**
	 * Registers Thrive Quiz Builder Exporter to WordPress Privacy Data Exporter in order to make the plugin GDPR Compliant
	 *
	 * @param array $exporters
	 *
	 * @return array
	 */
	public static function register_privacy_exporter( $exporters = array() ) {

		$exporters[] = array(
			'exporter_friendly_name' => __( 'Thrive Quiz Builder', 'thrive-quiz-builder' ),
			'callback'               => array( __CLASS__, 'privacy_exporter' ),
		);

		return $exporters;
	}

	/**
	 * Registers Thrive Quiz Builder Eraser to WordPress Privacy Data Eraser in order to make the plugin GDPR Compliant
	 *
	 * @param array $erasers
	 *
	 * @return array
	 */
	public static function register_privacy_eraser( $erasers = array() ) {
		$erasers[] = array(
			'eraser_friendly_name' => __( 'Thrive Quiz Builder', 'thrive-quiz-builder' ),
			'callback'             => array( __CLASS__, 'privacy_eraser' ),
		);

		return $erasers;
	}

	/**
	 * Thrive Quiz Builder - Exporter Function
	 *
	 * @param     $email_address
	 * @param int $page
	 *
	 * @return array
	 */
	public static function privacy_exporter( $email_address, $page = 1 ) {
		$export_items = array();
		global $tqbdb;

		$users = $tqbdb->get_users( array( 'email' => $email_address ) );

		if ( ! empty( $users ) ) {

			$group_id      = 'tqb-user-privacy';
			$group_label   = __( 'Quiz Builder', 'thrive-quiz-builder' );
			$timezone_diff = current_time( 'timestamp' ) - time();

			foreach ( $users as $user ) {
				$item_id           = $user['random_identifier'];
				$reporting_manager = new TQB_Reporting_Manager( $user['quiz_id'], 'users' );
				$user_answers      = $reporting_manager->get_users_answers( $user['id'] );


				$data = array(
					array(
						'name'  => __( 'Email', 'thrive-quiz-builder' ),
						'value' => $user['email'],
					),
					array(
						'name'  => __( 'Date and Start Time', 'thrive-quiz-builder' ),
						'value' => date( 'Y-m-d H:i:s', strtotime( $user['date_started'] ) + $timezone_diff ),
					),
					array(
						'name'  => __( 'Result', 'thrive-quiz-builder' ),
						'value' => $user['points'],
					),
					array(
						'name'  => __( 'Generated Social Badge', 'thrive-quiz-builder' ),
						'value' => ! empty( $user['social_badge_link'] ) ? sprintf( '<a href="%s" target="_blank">%s</a>', $user['social_badge_link'], $user['social_badge_link'] ) : '-',
					),
				);
				foreach ( $user_answers as $key => $user_answer_data ) {
					$html = __( 'Question', 'thrive-quiz-builder' ) . ': ' . $user_answer_data['text'];
					foreach ( $user_answer_data['answers'] as $answer ) {
						if ( ! empty( $answer['chosen'] ) && true === $answer['chosen'] ) {
							$html .= '<br>' . __( 'Answer', 'thrive-quiz-builder' ) . ': ';
							if ( ! empty( $answer['image'] ) ) {
								$html .= sprintf( '<a href="%s" target="_blank">%s</a>', $answer['image']->sizes->full->url, $answer['image']->sizes->full->url );
							} else {
								$html .= $answer['text'];
							}
						}
					}
					$data[] = array(
						'name'  => __( 'Question', 'thrive-quiz-builder' ) . ' #' . ++ $key,
						'value' => $html,
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
	 * Thrive Quiz Builder - Eraser Function
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

		global $tqbdb;

		$users = $tqbdb->get_users( array( 'email' => $email_address ) );
		if ( ! empty( $users ) ) {

			foreach ( $users as $user ) {
				$tqbdb->save_quiz_user( array( 'id' => $user['id'], 'email' => wp_privacy_anonymize_data( 'email', $email_address ) ) );
			}

			$response['items_removed'] = true;
			$response['messages']      = array( sprintf( '%s rows from Thrive Quiz Builder plugin were modified in order to remove personal data related', count( $users ) ) );
		}

		return $response;
	}
}

TQB_Privacy::init();
