<?php

namespace TVA\Architect\Certificate;

use TVA\Reporting\Events\Course_Finish;
use TVE\Reporting\Logs;

class Shortcodes {
	/**
	 * @var string[]
	 */
	private static $_shortcodes = [
		'tva_certificate_title'          => 'certificate_title',
		'tva_certificate_course_name'    => 'course_name',
		'tva_certificate_course_summary' => 'course_summary',
		'tva_certificate_course_author'  => 'course_author',
		'tva_certificate_inline_link'    => 'inline_link',
		'tva_certificate_number'         => 'number',
		'tva_certificate_recipient'      => 'recipient',
		'tva_certificate_date1'          => 'date',
		'tva_certificate_date2'          => 'date',
		'tva_certificate_date3'          => 'date',
		'tva_qr_source'                  => 'qr_source',
	];

	/**
	 * Shortcodes constructor.
	 */
	public static function init() {
		foreach ( static::$_shortcodes as $shortcode => $function ) {
			add_shortcode( $shortcode, array( __CLASS__, $function ) );
		}
	}

	/**
	 * Renders the Certificate Date ShortCode
	 *
	 * @param array  $attr      of the shortcode
	 * @param string $content   of the shortcode to be displayed
	 * @param string $shortcode string
	 *
	 * @return string $content
	 */
	public static function date( $attr, $content, $shortcode ) {

		global $certificate;
		$user_data = array();

		if ( get_post_type() === \TVA_Course_Certificate::POST_TYPE ) {
			$user_data   = tve_current_user_data();
			$certificate = static::get_certificate_course()->get_certificate()->get_data( ! empty( $user_data['id'] ) ? (int) $user_data['id'] : null );
		}

		$formats = array(
			'1' => 'd M Y',
			'2' => 'd/m/Y',
			'3' => 'm/d/Y',
		);

		$format_index = str_replace( 'tva_certificate_date', '', $shortcode );
		$date         = 'certificate date';

		if ( ! empty( $certificate ) ) {
			$logs = Logs::get_instance();
			$args = [
				'fields'             => 'created',
				'event_type'         => Course_Finish::key(),
				'filters'            => [
					'user_id' => is_array( $user_data ) && isset( $user_data['id'] ) ? $user_data['id'] : null,
					'post_id' => static::get_certificate_course()->get_id(),
				],
				'page'               => '1',
				'items_per_page'     => '1',
				'order_by'           => 'created',
				'order_by_direction' => 'DESC',
			];

			$results = $logs->set_query( $args )->get_results();
			$result  = reset( $results );

			if ( ! empty( $result ) && ! empty( $result['created'] ) ) {
				$date = wp_date( $formats[ $format_index ], strtotime( $result['created'] ) );
			} else {
				$date = wp_date( $formats[ $format_index ], $certificate['timestamp'] );
			}
		}

		return $content . $date;
	}

	/**
	 * Renders the Certificate Recipient ShortCode
	 *
	 * @return string
	 */
	public static function recipient() {
		$recipient = 'certificate recipient';

		if ( get_post_type() === \TVA_Course_Certificate::POST_TYPE ) {
			return tve_current_user_data()['display_name'];
		}

		global $certificate;

		if ( ! empty( $certificate ) ) {
			$recipient = $certificate['recipient']->display_name;
		}

		return $recipient;
	}

	/**
	 * Renders the Certificate Number ShortCode
	 *
	 * @return string $content
	 */
	public static function number() {
		$number = 'certificate number';

		if ( get_post_type() === \TVA_Course_Certificate::POST_TYPE ) {
			//In certificate context - Edit certificate with TAR & Certificate PDF
			$custom_code = static::get_certificate_course()->get_certificate()->get_number();

			if ( ! empty( $custom_code ) ) {
				$number = $custom_code;
			}

			return $number;
		}

		global $certificate;

		if ( ! empty( $certificate ) ) {
			$number = $certificate['number'];
		}

		return $number;
	}

	/**
	 * Inline link used into the Certificate Verification Element to get back to previous state
	 * - handled by JS
	 *
	 * @return string
	 */
	public static function inline_link() {
		return '#';
	}

	/**
	 * Certificate title shortcode callback
	 *
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return string
	 */
	public static function certificate_title( $attr = array(), $content = '' ) {
		if ( get_post_type() === \TVA_Course_Certificate::POST_TYPE ) {
			return get_the_title();
		}

		return '';
	}

	private static function get_certificate_course() {
		return Main::get_certificate_course( get_the_ID() );
	}

	/**
	 * Course name shortcode callback
	 *
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return string
	 */
	public static function course_name( $attr = array(), $content = '' ) {

		if ( get_post_type() === \TVA_Course_Certificate::POST_TYPE ) {
			$course = static::get_certificate_course();

			return $course->name;
		}

		global $certificate;

		$name = 'certificate course name';

		if ( ! empty( $certificate ) ) {
			$name = $certificate['course']->name;
		}

		return $content . $name;
	}

	/**
	 * Course summary shortcode callback
	 *
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return string
	 */
	public static function course_summary( $attr = array(), $content = '' ) {
		if ( get_post_type() === \TVA_Course_Certificate::POST_TYPE ) {
			$course = static::get_certificate_course();

			return $course->excerpt;
		}

		return '';
	}

	/**
	 * Course author shortcode callback
	 *
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return string
	 */
	public static function course_author( $attr = array(), $content = '' ) {

		if ( get_post_type() === \TVA_Course_Certificate::POST_TYPE ) {
			$course = static::get_certificate_course();

			return $course->get_author()->get_user()->display_name;
		}

		return '';
	}

	/**
	 * QR code source callback
	 *
	 * @param $attr
	 * @param $content
	 *
	 * @return string|null
	 */
	public static function qr_source( $attr = array() ) {
		$type   = $attr['type'];
		$source = '';
		if ( $type === 'verification' ) {
			$source = add_query_arg( [
				'u'                                                   => static::number(),
				\TVA_Course_Certificate::VERIFICATION_PAGE_QUERY_NAME => \TVA_Course_Certificate::VERIFICATION_PAGE_QUERY_VAL,
			], home_url() );
		} else if ( $type === 'course' ) {
			if ( is_int( get_the_ID() ) ) {
				$source = static::get_certificate_course()->get_link( false );
			}
		} else if ( $type === 'site' ) {
			$source = site_url();
		}

		return $source;
	}
}
