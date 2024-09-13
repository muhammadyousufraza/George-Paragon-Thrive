<?php

namespace TVA\Architect\Assessment;

use TCB_Utils;
use TVA\Assessments\TVA_User_Assessment;
use TVA\Assessments\Types\Upload;
use TVA_Assessment;
use TVA_Const;
use TVD_Global_Shortcodes;
use WP_Post;
use function TVA\Architect\Dynamic_Actions\tcb_tva_dynamic_actions;
use function TVA\TQB\tva_tqb_integration;

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Shortcodes {

	/**
	 * List of shortcodes
	 *
	 * @var string[]
	 */
	private static $shortcodes = [
		'tva_assessment'                   => 'assessment',
		'tva_assessment_type'              => 'assessment_type',
		'tva_tqb_assessment_quiz'          => 'assessment_quiz',
		'tva_assessment_upload_config'     => 'assessment_upload_config',
		//Results element
		'tva_assessment_result_list'       => 'result_list',
		'tva_assessment_result_item'       => 'result_item',
		'tva_assessment_result_item_state' => 'result_item_state',
		//Inline shortcodes & dynamic links
		'tva_assessment_dynamic_link'      => 'dynamic_link',
		'tva_assessment_title'             => 'title',
		'tva_assessment_summary'           => 'summary',
		'tva_assessment_submission_date1'  => 'date',
		'tva_assessment_submission_date2'  => 'date',
		'tva_assessment_submission_date3'  => 'date',
		'tva_submission_latest_date1'      => 'date_latest',
		'tva_submission_latest_date2'      => 'date_latest',
		'tva_submission_latest_date3'      => 'date_latest',
		'tva_assessment_grade1'            => 'grade_latest',
		'tva_assessment_grade2'            => 'grade',
		'tva_assessment_notes1'            => 'notes_latest',
		'tva_assessment_notes2'            => 'notes',
		'tva_assessment_pass_fail1'        => 'pass_fail_latest',
		'tva_assessment_pass_fail2'        => 'pass_fail',
	];

	/**
	 * Holds the value of assessment in case the assessment is set from request
	 *
	 * @var null|TVA_Assessment
	 */
	public static $assessment_from_request;

	/**
	 * @var null|string
	 */
	public static $assessment_identifier;

	/**
	 * Holds the assessment form state.
	 * Can be auto, submit, results
	 *
	 * @var null|string
	 */
	public static $assessment_form_state;

	/**
	 * Holds the value of user assessment
	 *
	 * @var null|TVA_User_Assessment
	 */
	public static $user_assessment;

	/**
	 * Define the shortcodes callbacks
	 *
	 * @return void
	 */
	public static function init() {
		foreach ( static::$shortcodes as $shortcode => $function ) {
			add_shortcode( $shortcode, [ __CLASS__, $function ] );
		}
	}

	/**
	 * Returns shortcodes keys
	 * Needed for allow shortcodes filter
	 *
	 * @return string[]
	 */
	public static function get() {
		return array_keys( static::$shortcodes );
	}

	/**
	 * Callback for tva_assessment_submit shortcode
	 * Renders the shortcode and returns the HTML string
	 *
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return string
	 */
	public static function assessment( $attr = [], $content = '' ) {

		if ( ! is_user_logged_in() ) {
			//The assessment element is only available for logged in users
			return '';
		}

		/**
		 * Fixes the case when the assessment shortcode has no attributes
		 */
		if ( ! is_array( $attr ) ) {
			$attr = (array) $attr;
		}

		/**
		 * @var array contains the classes of the assessment submit element
		 */
		$classes            = static::get_classes( str_replace( '.', '', Main::IDENTIFIER ), $attr );
		$is_inside_template = ! empty( $attr['data-inside-template'] );

		if ( empty( $attr['assessment-id'] ) ) {
			return static::warning_content( 'Invalid Assessment ID', $classes, [] );
		}

		$assessment_id = (int) $attr['assessment-id'];

		if ( $assessment_id === - 1 ) {

			if ( wp_doing_ajax() && ! empty( $_REQUEST['post_id'] ) ) {
				$post_id = (int) $_REQUEST['post_id'];
			} else {
				$post_id = get_the_ID();
			}
		} else {
			$post_id = $assessment_id;
		}

		static::$assessment_form_state   = empty( $attr['type'] ) || ! in_array( $attr['type'], [ Main::STATE_SUBMIT, Main::STATE_RESULTS, Main::STATE_AUTO ] ) ? Main::STATE_AUTO : $attr['type'];
		static::$assessment_from_request = new TVA_Assessment( $post_id );
		static::$assessment_identifier   = $attr['css'];

		if ( $is_inside_template && ! Main::$is_editor_page && static::$assessment_from_request->has_assessment_in_content() ) {
			return '';
		}

		//TODO check if the assessments has not been deleted

		/**
		 * Used for jump links
		 */
		$id = empty( $attr['id'] ) ? '' : $attr['id'];
		if ( empty( $content ) ) {
			$content = Main::get_default_content();
		}

		$data = [
			'data-default-type'  => Main::get_assessment_type( static::$assessment_from_request ),
			'data-assessment-id' => $assessment_id,
		];

		foreach ( $attr as $key => $value ) {
			if ( ! in_array( $key, [ 'id', 'class' ] ) ) {
				$data[ 'data-' . $key ] = esc_attr( $value );
			}
		}


		$content = do_shortcode( $content );

		static::$assessment_from_request = null;
		static::$assessment_form_state   = null;
		static::$assessment_identifier   = null;

		return TCB_Utils::wrap_content( trim( $content ), 'div', $id, $classes, $data );
	}

	/**
	 * Callback for tva_assessment_type shortcode
	 * Renders the shortcode and returns the HTML string
	 *
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return string
	 */
	public static function assessment_type( $attr = [], $content = '' ) {
		$type            = $attr['type'];
		$attributes      = [
			'data-type' => $type,
		];
		$classes         = [ 'thrv_wrapper', str_replace( '.', '', Main::TYPE_IDENTIFIER ) ];
		$assessment_type = Main::get_assessment_type( static::$assessment_from_request );

		if ( Main::$is_editor_page ) {
			$classes[] = 'tve_no_drag';
			$classes[] = 'tve_no_icons';

			$type_to_show = $assessment_type;

			if ( static::$assessment_form_state === Main::STATE_RESULTS ) {
				$type_to_show = Main::TYPE_RESULTS;
			}

			if ( $type !== $type_to_show ) {
				$classes[] = 'tcb-permanently-hidden';
			}

			$attributes['data-element-name'] = Main::get_editor_sub_element_name( $type );
		} else {

			if ( static::$assessment_form_state === Main::STATE_RESULTS && $type !== Main::TYPE_RESULTS ) {
				/**
				 * If the form state is to show only results and the form type is different from results do not render anything
				 */
				return '';
			}

			if ( static::$assessment_form_state === Main::STATE_SUBMIT && $type === Main::TYPE_RESULTS ) {
				/**
				 * If the form state is to show only submit state, do not render the results form type
				 */
				return '';
			}

			if ( in_array( static::$assessment_form_state, [ Main::STATE_AUTO, Main::STATE_SUBMIT ] ) ) {

				$allowed_form_types = [ $assessment_type, Main::TYPE_CONFIRMATION ];

				if ( static::$assessment_form_state === Main::STATE_AUTO ) {
					$allowed_form_types[] = Main::TYPE_RESULTS;
				}

				/**
				 * Render only allowed form types
				 */
				if ( ! in_array( $type, $allowed_form_types ) ) {
					return '';
				}

				if ( static::$assessment_form_state === Main::STATE_AUTO && count( TVA_User_Assessment::get_user_submission( [ 'post_parent' => static::$assessment_from_request->ID ] ) ) > 0 ) {
					/**
					 * If the form state is auto and the active user has form submissions the assessment type becomes result
					 */
					$assessment_type = Main::TYPE_RESULTS;
				}

				/**
				 * If the current assessment type is different from the rendered form type we need to hide it
				 * This ensures we show only the default type on page refresh
				 */
				if ( $assessment_type !== $type ) {
					$classes[] = 'tcb-permanently-hidden';
				}

				//Particular stuff for each assessment type
				if ( $type === Main::TYPE_UPLOAD ) {
					$attributes['data-f-id'] = 99999;
				}
			}

			if ( $type === Main::TYPE_RESULTS ) {

				/* add the data of each rendered assessment result list to $GLOBALS so we can localize it in a footer script later in the execution */
				$GLOBALS['tva_assessment_results_localize'][] = [
					'identifier' => '[data-css="' . static::$assessment_identifier . '"]',
					'template'   => static::$assessment_identifier,
					'content'    => $content,
				];
			}
		}

		/*
		 * Compatibility with shortcodes placed inside attributes tags
		 * see do_shortcodes_in_html_tags function
		 */
		$content = strtr( $content, [
			'&#091;' => '[',
			'&#093;' => ']',
			'&#91;'  => '[',
			'&#93;'  => ']',
		] );

		return TCB_Utils::wrap_content( trim( do_shortcode( $content ) ), 'div', '', $classes, $attributes );
	}

	public static function assessment_quiz( $attr = [], $content = '' ) {
		$classes    = [ 'tva-tqb-assessment-quiz', 'thrv_wrapper' ];
		$attributes = [
			'data-css' => ! empty( $attr['css'] ) ? $attr['css'] : '',
		];

		if ( Main::$is_editor_page ) {
			$classes = array_merge( $classes, [ 'tcb-no-clone', 'tcb-no-delete', 'tcb-no-save' ] );

			ob_start();
			include __DIR__ . '/editor-quiz-preview.php';
			$content = ob_get_contents();
			ob_end_clean();

		} else {
			$assessment = get_post_type() !== TVA_Const::ASSESSMENT_POST_TYPE ? static::$assessment_from_request : '';
			$quiz_id    = Main::get_assessment_quiz_id( $assessment );

			if ( tva_tqb_integration()->is_quiz_builder_active() && get_post( $quiz_id ) instanceof WP_Post ) {
				$content = "[tqb_quiz id='" . Main::get_assessment_quiz_id( $assessment ) . "']";
			} else {
				$content = 'Invalid quiz. Please contact the site admin!';
			}
		}

		return TCB_Utils::wrap_content( trim( do_shortcode( $content ) ), 'div', '', $classes, $attributes );
	}

	/**
	 * Upload configuration for frontend
	 * Contains dynamic values set by the course creator in the backend
	 *
	 * @param array  $attr
	 * @param string $content
	 * @param string $shortcode
	 *
	 * @return string
	 */
	public static function assessment_upload_config( $attr = [], $content = '', $shortcode = '' ) {

		/**
		 * This shortcode is placed inside HTML attributes and it is parsed via do_shortcodes_in_html_tags function
		 * This happens before do_shortcode logic occurs. Therefore if we do not have the assessment object yet, we return the shortcode
		 */
		if ( empty( static::$assessment_from_request ) ) {
			return '[' . $shortcode . ']';
		}

		$values = [
			'required'  => 1,
			'max_size'  => 1,
			'max_files' => 1,
			'allowed'   => [ 'pdf' ],
		];

		//We do not have static::$assessment_from_request here because the shortcode is rendered inside a data attribute
		if ( static::$assessment_from_request->get_type() === Main::TYPE_UPLOAD ) {
			$values = [
				'required'  => 1,
				'max_size'  => get_post_meta( static::$assessment_from_request->ID, 'tva_upload_max_file_size', true ),
				'max_files' => get_post_meta( static::$assessment_from_request->ID, 'tva_upload_max_files', true ),
				'allowed'   => Upload::get_extensions( static::$assessment_from_request ),
			];
		}

		return htmlspecialchars( wp_json_encode( $values ), ENT_QUOTES );
	}


	/**
	 * Renders the assessment result item
	 *
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return string
	 */
	public static function result_item( $attr = [], $content = '' ) {
		$classes = static::get_classes( str_replace( '.', '', Result::ITEM_IDENTIFIER ), $attr );

		if ( Main::$is_editor_page ) {
			$classes = array_merge( [ 'tcb-no-delete', 'tve_no_drag' ], $classes );
		}

		return TCB_Utils::wrap_content( trim( do_shortcode( $content ) ), 'div', '', $classes, [
			'data-selector'      => '.tva-assessment-result-item',
			'data-assessment-id' => static::$user_assessment->ID,
			'data-result-state'  => Result::get_active_state( static::$user_assessment ),
		] );
	}

	/**
	 * Handles the result item state both editor and frontend
	 *
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return string
	 */
	public static function result_item_state( $attr = [], $content = '' ) {
		if ( ! static::$user_assessment instanceof TVA_User_Assessment ) {
			return static::warning_content( 'Result item State: Invalid User Assessment' );
		}

		if ( ! is_array( $attr ) ) {
			$attr = (array) $attr;
		}

		$classes    = 'tva-assessment-result-state';
		$attributes = static::get_data_attributes( $attr );
		$state      = (int) $attributes['data-result-state'];

		if ( Main::$is_editor_page ) {
			if ( $state !== 0 ) {
				$attributes['style'] = 'display:none;';
			}

			return TCB_Utils::wrap_content( do_shortcode( $content ), 'div', '', $classes, $attributes );
		}

		if ( $state !== Result::get_active_state( static::$user_assessment ) ) {
			return '';
		}

		return TCB_Utils::wrap_content( do_shortcode( $content ), 'div', '', $classes, $attributes );
	}

	/**
	 * Result list shortcode implementation
	 *
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return string
	 */
	public static function result_list( $attr = [], $content = '' ) {
		if ( ! static::$assessment_from_request instanceof TVA_Assessment ) {
			return static::warning_content( 'Invalid Assessment' );
		}

		if ( ! is_array( $attr ) ) {
			$attr = (array) $attr;
		}

		if ( empty( $content ) ) {
			$content = Result::get_default_content();
		}

		//We make a copy not to modify the original
		$prepared_content   = $content;
		$exclude            = [];
		$should_hide_latest = ! isset( $attr['hide-latest'] );
		$should_hide_list   = isset( $attr['disable-list'] ) && (int) $attr['disable-list'] === 1;

		if ( $should_hide_list && ! Main::$is_editor_page ) {
			return '';
		}

		if ( $should_hide_latest ) {
			$latest_user_assessment = static::get_latest_user_assessment( static::$assessment_from_request );
			if ( ! empty( $latest_user_assessment ) ) {
				$exclude = [ $latest_user_assessment->ID ];
			}
		}

		$user_assessments = TVA_User_Assessment::get_user_submission( [
			'post_parent'    => static::$assessment_from_request->ID,
			'posts_per_page' => empty( $attr['posts_per_page'] ) ? Result::DEFAULT_NO_OF_ITEMS : (int) $attr['posts_per_page'],
			'exclude'        => $exclude,
		] );


		if ( empty( $user_assessments ) && Main::$is_editor_page ) {
			$user_assessments = Result::get_demo_items();
		}

		$return = '';
		foreach ( $user_assessments as $user_assessment ) {
			static::$user_assessment = $user_assessment;

			$return .= trim( do_shortcode( $prepared_content ) );

			static::$user_assessment = null;
		}

		$data    = static::get_data_attributes( $attr );
		$classes = static::get_classes( str_replace( '.', '', Result::LIST_IDENTIFIER ), $attr );

		if ( Main::$is_editor_page ) {
			$classes = array_merge( [ 'tcb-no-delete' ], $classes );
		}

		return TCB_Utils::wrap_content( trim( $return ), 'div', '', $classes, $data );
	}

	public static function title( $attr = [] ) {
		if ( ! static::$assessment_from_request instanceof TVA_Assessment ) {
			return static::warning_content( 'Invalid Assessment' );
		}

		$title = static::$assessment_from_request->post_title;

		if ( ! empty( $attr['link'] ) ) {
			$attributes = [
				'href' => get_permalink( static::$assessment_from_request->ID ),
			];

			if ( ! empty( $attr['target'] ) ) {
				$attributes['target'] = '_blank';
			}

			if ( ! empty( $attr['rel'] ) ) {
				$attributes['rel'] = 'nofollow';
			}

			if ( ! empty( $attr['link-css-attr'] ) ) {
				$attributes['data-css'] = $attr['link-css-attr'];
			}

			$title = TCB_Utils::wrap_content( $title, 'a', '', array(), $attributes );
		} else {
			$title = TVD_Global_Shortcodes::maybe_link_wrap( $title, $attr );
		}

		return $title;
	}

	/**
	 * @return string
	 */
	public static function summary() {
		if ( ! static::$assessment_from_request instanceof TVA_Assessment ) {
			return static::warning_content( 'Invalid Assessment' );
		}

		return static::$assessment_from_request->post_excerpt;
	}

	/**
	 * @param array  $attr
	 * @param string $content
	 * @param string $shortcode
	 *
	 * @return string
	 */
	public static function date( $attr = [], $content = '', $shortcode = '' ) {
		if ( ! static::$user_assessment instanceof TVA_User_Assessment ) {
			return static::warning_content( 'Invalid User Assessment1' );
		}

		$formats = [
			'1' => 'd M Y',
			'2' => 'd/m/Y',
			'3' => 'm/d/Y',
		];

		$format_index = str_replace( 'tva_assessment_submission_date', '', $shortcode );
		$date         = wp_date( $formats[ $format_index ], get_post_timestamp( static::$user_assessment->ID ) );

		return $content . $date;
	}

	/**
	 * @param array  $attr
	 * @param string $content
	 * @param string $shortcode
	 *
	 * @return string
	 */
	public static function date_latest( $attr = [], $content = '', $shortcode = '' ) {
		$date = '-';

		if ( static::$assessment_from_request instanceof TVA_Assessment ) {

			$latest_user_assessment = static::get_latest_user_assessment( static::$assessment_from_request );

			if ( ! empty( $latest_user_assessment ) ) {

				$formats = [
					'1' => 'd M Y',
					'2' => 'd/m/Y',
					'3' => 'm/d/Y',
				];

				$format_index = str_replace( 'tva_submission_latest_date', '', $shortcode );
				$date         = wp_date( $formats[ $format_index ], get_post_timestamp( $latest_user_assessment->ID ) );

				if ( empty( $date ) ) {
					$date = '-';
				}

			}
		}

		return $content . $date;
	}

	/**
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return string
	 */
	public static function grade() {
		if ( ! static::$user_assessment instanceof TVA_User_Assessment ) {
			return static::warning_content( 'Invalid User Assessment' );
		}

		$grade = static::$user_assessment->get_grade( true );

		if ( empty( $grade ) ) {
			$grade = Result::get_not_graded_text();
		}

		return $grade;
	}

	/**
	 * Latest assessment grade - shortcode callback
	 *
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return string
	 */
	public static function grade_latest() {
		$grade = Result::get_not_graded_text();

		if ( static::$assessment_from_request instanceof TVA_Assessment ) {

			$latest_user_assessment = static::get_latest_user_assessment( static::$assessment_from_request );

			if ( ! empty( $latest_user_assessment ) ) {
				$grade = $latest_user_assessment->get_grade( true );

				if ( empty( $grade ) ) {
					$grade = Result::get_not_graded_text();
				}

			}
		}

		return $grade;
	}

	public static function pass_fail() {
		$return = '';

		if ( ! static::$user_assessment instanceof TVA_User_Assessment ) {
			return static::warning_content( 'Invalid User Assessment' );
		}

		$status = static::$user_assessment->status;

		switch ( $status ) {
			case TVA_Const::ASSESSMENT_STATUS_COMPLETED_FAILED:
				$return = tcb_tva_dynamic_actions()->get_course_structure_label( 'assessments_fail', 'singular' );
				break;
			case TVA_Const::ASSESSMENT_STATUS_COMPLETED_PASSED:
				$return = tcb_tva_dynamic_actions()->get_course_structure_label( 'assessments_pass', 'singular' );
				break;
			default:
				break;
		}

		return trim( $return );
	}

	public static function pass_fail_latest() {
		$return = '';
		if ( static::$assessment_from_request instanceof TVA_Assessment ) {
			$latest_user_assessment = static::get_latest_user_assessment( static::$assessment_from_request );

			if ( ! empty( $latest_user_assessment ) ) {
				$status = $latest_user_assessment->status;

				switch ( $status ) {
					case TVA_Const::ASSESSMENT_STATUS_COMPLETED_FAILED:
						$return = tcb_tva_dynamic_actions()->get_course_structure_label( 'assessments_fail', 'singular' );
						break;
					case TVA_Const::ASSESSMENT_STATUS_COMPLETED_PASSED:
						$return = tcb_tva_dynamic_actions()->get_course_structure_label( 'assessments_pass', 'singular' );
						break;
					default:
						break;
				}
			}
		}

		return trim( $return );
	}

	/**
	 * @return string
	 */
	public static function notes() {
		if ( ! static::$user_assessment instanceof TVA_User_Assessment ) {
			return static::warning_content( 'Invalid User Assessment' );
		}

		$notes = static::$user_assessment->notes;

		if ( empty( $notes ) ) {
			$notes = Result::get_no_notes_text();
		}

		return $notes;
	}

	/**
	 * Latest assessment notes - shortcode callback
	 *
	 * @return string
	 */
	public static function notes_latest() {
		$notes = Result::get_no_notes_text();

		if ( static::$assessment_from_request instanceof TVA_Assessment ) {

			$latest_user_assessment = static::get_latest_user_assessment( static::$assessment_from_request );

			if ( $latest_user_assessment instanceof TVA_User_Assessment ) {
				$notes = $latest_user_assessment->notes;

				if ( empty( $notes ) ) {
					$notes = Result::get_no_notes_text();
				}

			}
		}

		return $notes;
	}

	/**
	 * Assessments dynamic link
	 * - Back to assessment submit
	 *
	 * @param array $attr
	 *
	 * @return string
	 */
	public static function dynamic_link( $attr = [] ) {
		if ( ! isset( $attr['id'] ) ) {
			return '#';
		}

		return '#tcb-state--' . $attr['id'];
	}

	/**
	 * Returns the warning content.
	 * On frontend if there is a warning returns nothing
	 *
	 * @param string $content
	 * @param array  $classes
	 * @param array  $data
	 *
	 * @return string
	 */
	private static function warning_content( $content = '', $classes = [], $data = [] ) {
		if ( ! Main::$is_editor_page ) {
			$content = '';
		}

		$classes[] = 'WARNING';

		return TCB_Utils::wrap_content( $content, 'div', '', $classes, $data );
	}

	/**
	 * @param string $element_identifier
	 * @param array  $attr
	 *
	 * @return array
	 */
	private static function get_classes( $element_identifier, $attr = [] ) {
		$classes = [ 'thrv_wrapper', $element_identifier ];

		/* hide the 'Save as Symbol' icon */
		if ( Main::$is_editor_page ) {
			$classes[] = 'tcb-selector-no_save';
			$classes[] = 'tve_no_duplicate';
		}

		/* set custom classes, if they are present */
		if ( ! empty( $attr['class'] ) ) {
			$classes[] = $attr['class'];
		}

		return $classes;
	}

	/**
	 * Computes data attributes
	 *
	 * @param array $attr
	 *
	 * @return array
	 */
	private static function get_data_attributes( $attr = [] ) {
		$data = [];
		foreach ( $attr as $key => $value ) {
			if ( $key !== 'class' ) { /* we don't want data-class to persist, we process it inside get_classes() */
				$data[ 'data-' . $key ] = esc_attr( $value );
			}
		}

		return $data;
	}


	/**
	 * Returns the latest submitted user assessment
	 *
	 * @param TVA_Assessment $assessment
	 *
	 * @return false|TVA_User_Assessment
	 */
	private static function get_latest_user_assessment( $assessment ) {
		return current( TVA_User_Assessment::get_user_submission( [
			'post_parent'    => $assessment->ID,
			'posts_per_page' => 1,
		] ) );
	}
}
