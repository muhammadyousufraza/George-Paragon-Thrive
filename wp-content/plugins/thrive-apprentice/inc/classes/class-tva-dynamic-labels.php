<?php

use TVA\Access\Expiry\Base;

class TVA_Dynamic_Labels {
	const OPT = 'tva_dynamic_labels';

	/**
	 * Holds a cache of logged user's relation with an array of courses
	 *
	 * @var array
	 */
	protected static $USER_COURSE_CACHE = [];

	/**
	 * Available options for users that have access to the course
	 *
	 * @return array
	 */
	public static function get_user_switch_contexts() {
		return array(
			'not_started'            => __( 'If user has access but not started the course', 'thrive-apprentice' ),
			'in_progress'            => __( 'If user has started the course', 'thrive-apprentice' ),
			'finished'               => __( 'If user has finished the course', 'thrive-apprentice' ),
			'access_about_to_expire' => __( 'If access is about to expire', 'thrive-apprentice' ),
			'access_expired'         => __( 'If access is expired', 'thrive-apprentice' ),
		);
	}

	/**
	 * Default label values when inputs from the options above are saved empty
	 *
	 * @return array
	 */
	public static function get_user_switch_default_labels() {
		return array(
			'not_started'            => __( 'Not started yet', 'thrive-apprentice' ),
			'in_progress'            => __( 'In progress', 'thrive-apprentice' ),
			'finished'               => __( 'Course complete!', 'thrive-apprentice' ),
			'access_about_to_expire' => __( 'Expires in [days] days', 'thrive-apprentice' ),
			'access_expired'         => __( 'Expired', 'thrive-apprentice' ),
		);
	}

	/**
	 * Available options for CTA buttons depending on user context and relation to the course
	 *
	 * @return array
	 */
	public static function get_cta_contexts() {
		return array(
			'view'        => __( 'View course details', 'thrive-apprentice' ),
			'not_started' => __( 'If user has access to the course but not started it yet', 'thrive-apprentice' ),
			'in_progress' => __( 'If user is midway through a course', 'thrive-apprentice' ),
			'finished'    => __( 'If user has finished a course', 'thrive-apprentice' ),
			'buy_now' => __( 'If course is configured for a buy now button', 'thrive-apprentice' ),
		);
	}


	public static function get_cta_contexts_labels() {
		return array_map( function ( $context ) {
			return [
				'title' => $context,
			];
		}, static::get_cta_default_labels() );
	}

	/**
	 * Default label values when inputs from the options above are saved empty
	 *
	 * @return array
	 */
	public static function get_cta_default_labels() {
		return array(
			'view'        => __( 'Learn more', 'thrive-apprentice' ),
			'not_started' => __( 'Start course', 'thrive-apprentice' ),
			'in_progress' => __( 'Continue course', 'thrive-apprentice' ),
			'finished'    => __( 'Revisit the course', 'thrive-apprentice' ),
			'buy_now' => __( 'Buy now', 'thrive-apprentice' ),
		);
	}

	/**
	 * Available options for Course type labels depending on user context and relation to the course
	 *
	 * @return array
	 */
	public static function get_course_type_label_contexts() {
		return array(
			'guide'            => __( 'A course that consists of only one lesson', 'thrive-apprentice' ),
			'text'             => __( 'A course that contains only text content', 'thrive-apprentice' ),
			'audio'            => __( 'A course that contains only audio', 'thrive-apprentice' ),
			'video'            => __( 'A course that contains only video', 'thrive-apprentice' ),
			'audio_text'       => __( 'A course that contains audio and text', 'thrive-apprentice' ),
			'video_text'       => __( 'A course that contains video and text', 'thrive-apprentice' ),
			'video_audio'      => __( 'A course that contains video and audio', 'thrive-apprentice' ),
			'video_audio_text' => __( 'A course that contains video, audio and text', 'thrive-apprentice' ),
			'assessments'      => __( 'A course that contains only assessments', 'thrive-apprentice' ),
		);
	}

	/**
	 * Default label values when inputs from the options above are saved empty
	 *
	 * @return array
	 */
	public static function get_course_type_label_default_labels() {
		return array(
			'assessments'      => __( 'Assessments', 'thrive-apprentice' ),
			'guide'            => __( 'Guide', 'thrive-apprentice' ),
			'text'             => __( 'Text', 'thrive-apprentice' ),
			'audio'            => __( 'Audio', 'thrive-apprentice' ),
			'video'            => __( 'Video', 'thrive-apprentice' ),
			'audio_text'       => __( 'Audio/Text', 'thrive-apprentice' ),
			'video_text'       => __( 'Video/Text', 'thrive-apprentice' ),
			'video_audio'      => __( 'Video/Audio', 'thrive-apprentice' ),
			'video_audio_text' => __( 'Video/Audio/Text', 'thrive-apprentice' ),
		);
	}

	/**
	 * Available options for Course navigation labels
	 *
	 * @return array
	 */
	public static function get_course_navigation_contexts() {
		return array(
			'next_lesson'        => __( 'Navigate to next lesson in the course', 'thrive-apprentice' ),
			'prev_lesson'        => __( 'Navigate to the previous lesson in the course', 'thrive-apprentice' ),
			'next_item'          => __( 'Navigate to next content (e.g assessment) in the course', 'thrive-apprentice' ),
			'prev_item'          => __( 'Navigate to the previous content (e.g assessment) in the course', 'thrive-apprentice' ),
			'to_course_page'     => __( 'Navigate to the course overview', 'thrive-apprentice' ),
			'to_completion_page' => __( 'Navigate to the course completion page', 'thrive-apprentice' ),
			'mark_complete'      => __( 'Mark lesson complete', 'thrive-apprentice' ),
		);
	}

	/**
	 * Default label values when inputs from the options above are saved empty
	 *
	 * @return array
	 */
	public static function get_course_navigation_default_labels() {
		return array(
			'next_lesson'        => __( 'Next lesson', 'thrive-apprentice' ),
			'prev_lesson'        => __( 'Previous lesson', 'thrive-apprentice' ),
			'next_item'          => __( 'Next', 'thrive-apprentice' ),
			'prev_item'          => __( 'Previous', 'thrive-apprentice' ),
			'to_course_page'     => __( 'To course page', 'thrive-apprentice' ),
			'to_completion_page' => __( 'View completion page', 'thrive-apprentice' ),
			'mark_complete'      => __( 'Mark lesson complete', 'thrive-apprentice' ),
		);
	}

	/**
	 * Available options for Course navigation warnings
	 *
	 * @return array
	 */
	public static function get_course_navigation_warnings() {
		return array(
			'mark_complete_requirements'  => __( 'When lesson contains progress requirements', 'thrive-apprentice' ),
			'download_certificate_notice' => __( 'When certificates download link is accessed by a student that has not earned a certificate', 'thrive-apprentice' ),
		);
	}

	/**
	 * Default label values when inputs from the options above are saved empty
	 *
	 * @return array
	 */
	public static function get_course_navigation_default_warnings() {
		return array(
			'mark_complete_requirements'  => __( 'You must complete all requirements for this lesson to mark it as complete', 'thrive-apprentice' ),
			'download_certificate_notice' => __( 'Certificate unavailable. You must be logged in and have earned a certificate in order to download it', 'thrive-apprentice' ),
		);
	}

	/**
	 * Available options for Course structure labels
	 *
	 * @return array
	 */
	public static function get_course_structure_contexts() {
		return array(
			'course_lesson'         => __( 'Content type that contains lesson content', 'thrive-apprentice' ),
			'course_chapter'        => __( 'Content type that contains a group of only lessons', 'thrive-apprentice' ),
			'course_module'         => __( 'Content type that contains a group of chapters and lessons', 'thrive-apprentice' ),
			'course_resources'      => __( 'Supporting resources (files and links) for lessons', 'thrive-apprentice' ),
			'resources_open'        => __( 'Button label to open a resource', 'thrive-apprentice' ),
			'resources_download'    => __( 'Button label to download a resource', 'thrive-apprentice' ),
			'certificate_download'  => __( 'Button label to download a certificate', 'thrive-apprentice' ),
			'course_assessment'     => __( 'Content type that contains an assessment', 'thrive-apprentice' ),
			'assessment_not_graded' => __( 'When an assessment is pending display', 'thrive-apprentice' ),
			'item_not_completed'    => __( 'Lesson or assessment status label in the lesson list when not completed', 'thrive-apprentice' ),
			'item_completed'        => __( 'Lesson or assessment status label in the lesson list when completed', 'thrive-apprentice' ),
			'assessments_external'  => __( 'Assessments that are of a External Link type', 'thrive-apprentice' ),
			'assessments_youtube'   => __( 'Assessments that are of a YouTube link type', 'thrive-apprentice' ),
			'assessments_quiz'      => __( 'Assessments that are of a Quiz type', 'thrive-apprentice' ),
			'assessments_upload'    => __( 'Assessments that are of a File Upload type', 'thrive-apprentice' ),
			'assessments_pass'      => __( 'When an assessment has been passed', 'thrive-apprentice' ),
			'assessments_fail'      => __( 'When an assessment has been failed', 'thrive-apprentice' ),
		);
	}

	/**
	 * Default label values when inputs from the options above are saved empty
	 *
	 * @return array
	 */
	public static function get_course_structure_default_labels() {
		return array(
			'course_lesson'        => array(
				'singular' => __( 'Lesson', 'thrive-apprentice' ),
				'plural'   => __( 'Lessons', 'thrive-apprentice' ),
			),
			'course_chapter'       => array(
				'singular' => __( 'Chapter', 'thrive-apprentice' ),
				'plural'   => __( 'Chapters', 'thrive-apprentice' ),
			),
			'course_module'        => array(
				'singular' => __( 'Module', 'thrive-apprentice' ),
				'plural'   => __( 'Modules', 'thrive-apprentice' ),
			),
			'course_resources'     => array(
				'plural' => __( 'Resources', 'thrive-apprentice' ),
			),
			'resources_open'       => array(
				'singular' => __( 'Open', 'thrive-apprentice' ),
			),
			'resources_download'   => array(
				'singular' => __( 'Download', 'thrive-apprentice' ),
			),
			'certificate_download' => array(
				'singular' => __( 'Download certificate', 'thrive-apprentice' ),
			),
			'course_assessment'    => array(
				'singular' => __( 'Assessment', 'thrive-apprentice' ),
				'plural'   => __( 'Assessments', 'thrive-apprentice' ),
			),
			'assessment_not_graded'    => array(
				'singular' => __( 'Not Graded', 'thrive-apprentice' ),
			),
			'item_not_completed'    => array(
				'singular' => __( 'Not Completed', 'thrive-apprentice' ),
			),
			'item_completed'    => array(
				'singular' => __( 'Completed', 'thrive-apprentice' ),
			),
			'assessments_external' => array(
				'singular' => __( 'External Link', 'thrive-apprentice' ),
			),
			'assessments_youtube'  => array(
				'singular' => __( 'YouTube Link', 'thrive-apprentice' ),
			),
			'assessments_quiz'     => array(
				'singular' => __( 'Quiz', 'thrive-apprentice' ),
			),
			'assessments_upload'   => array(
				'singular' => __( 'File Upload', 'thrive-apprentice' ),
			),
			'assessments_pass'     => array(
				'singular' => __( 'Passed', 'thrive-apprentice' ),
			),
			'assessments_fail'     => array(
				'singular' => __( 'Failed', 'thrive-apprentice' ),
			),
		);
	}

	/**
	 * Available options for Course structure labels
	 *
	 * @return array
	 */
	public static function get_course_progress_contexts() {
		return array(
			'label'            => __( 'Label for progress', 'thrive-apprentice' ),
			'not_started'      => __( 'A course that has not been started yet', 'thrive-apprentice' ),
			'in_progress'      => __( 'A course that is in progress', 'thrive-apprentice' ),
			'finished'         => __( 'A course that is finished', 'thrive-apprentice' ),
			'lesson_completed' => __( 'Lesson completed notification', 'thrive-apprentice' ),
		);
	}

	/**
	 * Default label values when inputs from the options above are saved empty
	 *
	 * @return array
	 */
	public static function get_course_progress_default_labels() {
		return array(
			'label'            => __( 'Progress', 'thrive-apprentice' ),
			'not_started'      => __( 'Not started', 'thrive-apprentice' ),
			'in_progress'      => __( 'In progress', 'thrive-apprentice' ),
			'finished'         => __( 'Finished', 'thrive-apprentice' ),
			'lesson_completed' => __( 'Lesson completed', 'thrive-apprentice' ),
		);
	}

	/**
	 * Store the settings to the wp_options table
	 *
	 * @param array $settings
	 *
	 * @return array the saved array of settings
	 */
	public static function save( $settings ) {
		$defaults = static::defaults();
		$settings = array_replace_recursive( $defaults, $settings );

		/**
		 * Make sure no extra keys are saved.
		 */
		$settings = array_intersect_key( $settings, $defaults );

		update_option( static::OPT, $settings );

		return $settings;
	}

	/**
	 * Get the stored settings, with some default values
	 *
	 * @param string $key allows retrieving only a single setting
	 *
	 * @return bool|mixed|void
	 */
	public static function get( $key = null ) {
		$defaults = static::defaults();

		$db_settings = $settings = get_option( static::OPT, $defaults );

		if ( ! isset( $settings['course_labels'] ) ) {
			$settings['course_labels'] = static::get_course_type_labels();
		} else {
			$settings['course_labels'] = $settings['course_labels'] + static::get_course_type_labels();
		}

		if ( ! isset( $settings['course_navigation'] ) ) {
			$settings['course_navigation'] = static::get_course_navigation_labels();
		} else {
			$settings['course_navigation'] = $settings['course_navigation'] + static::get_course_navigation_labels();
		}

		if ( ! isset( $settings['course_structure'] ) ) {
			$settings['course_structure'] = static::get_course_structure_labels();
		} else {
			$settings['course_structure'] = $settings['course_structure'] + static::get_course_structure_labels();
		}

		if ( ! isset( $settings['course_progress'] ) ) {
			$settings['course_progress'] = static::get_course_progress_labels();
		} else {
			$settings['course_progress'] = $settings['course_progress'] + static::get_course_progress_labels();
		}

		if ( count( $settings['labels'] ) < count( $defaults['labels'] ) ) {
			$settings['labels'] = array_merge( $defaults['labels'], $settings['labels'] );
		}

		if ( ! isset( $settings['buttons'] ) ) {
			$settings['buttons'] = static::get_cta_contexts_labels();
		} else {
			$settings['buttons'] = $settings['buttons'] + static::get_cta_contexts_labels();
		}

		if ( $db_settings !== $settings ) {
			update_option( static::OPT, $settings );
		}

		if ( isset( $key ) ) {
			return isset( $settings[ $key ] ) ? $settings[ $key ] : null;
		}

		return $settings;
	}

	/**
	 * Check if a dynamic label applies to the $course
	 * If nothing found, just output the regular selected label
	 *
	 * @param WP_Term|TVA_Course_V2 $course
	 *
	 * @return null|array containing label ID, color and title
	 */
	public static function get_course_label( $course ) {
		if ( $course instanceof WP_Term ) {
			$course = new TVA_Course_V2( $course );
		}

		$settings = static::get();

		if ( ! empty( $settings['switch_labels'] ) && get_current_user_id() ) {
			if ( $course->has_access() ) {
				/* switch label based on user's relation to the course */
				/* check if user has started the course */
				$label_key = static::get_user_course_context( $course );
			} elseif ( $course->has_expired_access() ) {
				$label_key = 'access_expired';
			}

			/**
			 * This should always exist, however, just to make sure no warnings will be generated, perform this extra check
			 */
			$label = ! empty( $label_key ) && isset( $settings['labels'][ $label_key ] ) ? $settings['labels'][ $label_key ] : [];

			if ( empty( $label ) || $label['opt'] === 'hide' ) {
				return null;
			}

			/**
			 * For public courses, show the "In progress" or "Completed" labels for logged users where possible
			 * Do not show the "Not started yet" label
			 */
			$should_hide = $label_key !== 'in_progress' && $label_key !== 'finished';
			if ( ! $course->is_private() && ( $should_hide || $label['opt'] !== 'show' ) ) {
				return null;
			}

			if ( $label['opt'] === 'show' ) {
				$label['ID'] = $label_key;

				return $label;
			}
		}

		/* at this point, return the selected course label - no dynamic label found, or the dynamic label has the "nochange" option selected. */

		return tva_get_labels( array( 'ID' => $course->get_label_id() ) );
	}

	/**
	 * Get the course CTA button text
	 *
	 * @param WP_Term|TVA_Course_V2 $course
	 * @param string                $context
	 * @param string                $default string to use in case no suitable CTA text is found
	 *
	 * @return mixed
	 */
	public static function get_course_cta( $course, $context = 'list', $default = null ) {
		/**
		 * The current implementation only supports the `list` $context. Moving forward, other contexts will be added.
		 * on the list of courses, the default text should be the one defined for 'view'.
		 * only possible options are:
		 *      - view
		 *      - not_started
		 *      - in_progress
		 *      - finished
		 */
		$button_key = 'view';
		if ( $context === 'list' ) {
			if ( get_current_user_id() && $course->has_access() ) {
				$button_key = static::get_user_course_context( $course );
			} else {
				$button_key = 'view';
			}
		} elseif ( $context === 'single' ) {
			$button_key = static::get_user_course_context( $course );
		}

		return static::get_cta_label( $button_key, $default );
	}

	/**
	 * Get the logged user's relation to the course. This does not check for access. To be used when user has access to a course
	 *
	 * @param WP_Term|TVA_Course_V2 $course
	 *
	 * @return string 'not_started' / 'in_progress' / 'finished'
	 */
	public static function get_user_course_context( $course ) {
		if ( $course instanceof WP_Term ) {
			$course = new TVA_Course_V2( $course );
		}

		$course_id = $course->get_id();
		if ( ! isset( static::$USER_COURSE_CACHE[ $course_id ] ) ) {
			$lessons_learnt = TVA_Shortcodes::get_learned_lessons();

			if ( empty( $lessons_learnt[ $course_id ] ) ) {
				static::$USER_COURSE_CACHE[ $course_id ] = 'not_started';
			} else {
				static::$USER_COURSE_CACHE[ $course_id ] = count( $lessons_learnt[ $course->get_id() ] ) >= $course->published_lessons_count ? 'finished' : 'in_progress';
			}

			if ( Base::is_about_to_expire( get_current_user_id(), $course->get_product() ) ) {
				static::$USER_COURSE_CACHE[ $course_id ] = 'access_about_to_expire';
			}
		}

		return static::$USER_COURSE_CACHE[ $course_id ];
	}

	/**
	 * Output the CSS required for each dynamic label
	 */
	public static function output_css() {
		$options = static::get();
		if ( ! empty( $options['switch_labels'] ) ) {
			foreach ( $options['labels'] as $id => $label ) {
				echo sprintf(
					'.tva_members_only-%1$s { background: %2$s }.tva_members_only-%1$s:before { border-color: %2$s transparent transparent transparent }',
					$id,
					$label['color']
				);
			}
		}
	}

	/**
	 * Return the CTA set for a user context ($key)
	 *
	 * @param string $key     identifier for the value
	 * @param null   $default default value to return if nothing is found
	 *
	 * @return string
	 */
	public static function get_cta_label( $key, $default = null ) {
		$buttons = static::get( 'buttons' );

		if ( empty( $default ) ) {
			$default = $buttons['view']['title'];
		}

		return isset( $buttons[ $key ]['title'] ) ? $buttons[ $key ]['title'] : $default;
	}

	/**
	 * Get the default values for dynamic settings
	 *
	 * @return array
	 */
	public static function defaults() {
		$template = TVA_Setting::get( 'template' );

		//backwards compat -> "Start course" should read from an existing setting
		$defaults = array(
			'start_course' => isset( $template['start_course'] ) ? $template['start_course'] : TVA_Const::TVA_START,
		);

		return array(
			'switch_labels'     => false,
			'labels'            => array(
				'not_started'            => array(
					'opt'   => 'show',
					'title' => __( 'Not started yet', 'thrive-apprentice' ),
					'color' => '#58a545',
				),
				'in_progress'            => array(
					'opt'   => 'show',
					'title' => __( 'In progress', 'thrive-apprentice' ),
					'color' => '#58a545',
				),
				'finished'               => array(
					'opt'   => 'show',
					'title' => __( 'Course complete!', 'thrive-apprentice' ),
					'color' => '#58a545',
				),
				'access_about_to_expire' => [
					'opt'   => 'show',
					'title' => __( 'Expire in [days] days', 'thrive-apprentice' ),
					'color' => '#FF0000',
				],
				'access_expired'         => [
					'opt'   => 'show',
					'title' => __( 'Expired', 'thrive-apprentice' ),
					'color' => '#FF0000',
				],
			),
			'buttons'           => array(
				'view'        => array(
					'title' => __( 'Learn more', 'thrive-apprentice' ),
				),
				'not_started' => array(
					'title' => $defaults['start_course'],
				),
				'in_progress' => array(
					'title' => __( 'Continue course', 'thrive-apprentice' ),
				),
				'finished'    => array(
					'title' => __( 'Revisit the course', 'thrive-apprentice' ),
				),
			),
			'course_labels'     => static::get_course_type_labels( $template ),
			'course_navigation' => static::get_course_navigation_labels( $template ),
			'course_structure'  => static::get_course_structure_labels( $template ),
			'course_progress'   => static::get_course_progress_labels( $template ),
		);
	}

	/**
	 * Get the values for course type labels
	 * this is also used for backwards compatibility
	 *
	 * @param TVA_Setting $template
	 *
	 * @return array[]
	 */
	public static function get_course_type_labels( $template = null ) {
		if ( ! $template ) {
			$template = TVA_Setting::get( 'template' );
		}

		return array(
			'guide'            => array(
				'title' => $template['course_type_guide'],
			),
			'text'             => array(
				'title' => $template['course_type_text'],
			),
			'audio'            => array(
				'title' => $template['course_type_audio'],
			),
			'video'            => array(
				'title' => $template['course_type_video'],
			),
			'audio_text'       => array(
				'title' => $template['course_type_audio_text_mix'],
			),
			'video_text'       => array(
				'title' => $template['course_type_video_text_mix'],
			),
			'video_audio'      => array(
				'title' => $template['course_type_video_audio_mix'],
			),
			'video_audio_text' => array(
				'title' => $template['course_type_big_mix'],
			),
			'assessments'      => [
				'title' => isset( $template['course_type_assessments'] ) ? $template['course_type_assessments'] : 'Assessments',
			],
		);
	}

	/**
	 * Get the values for course navigation labels
	 * this is also used for backwards compatibility
	 *
	 * @param TVA_Setting $template
	 *
	 * @return array[]
	 */
	public static function get_course_navigation_labels( $template = null ) {
		if ( ! $template ) {
			$template = TVA_Setting::get( 'template' );
		}

		return array(
			'next_lesson'                 => array(
				'title' => $template['next_lesson'],
			),
			'prev_lesson'                 => array(
				'title' => $template['prev_lesson'],
			),
			'next_item'                   => array(
				'title' => isset( $template['next_item'] ) ? $template['next_item'] : __( 'Next', 'thrive-apprentice' ),
			),
			'prev_item'                   => array(
				'title' => isset( $template['prev_item'] ) ? $template['prev_item'] : __( 'Previous', 'thrive-apprentice' ),
			),
			'to_course_page'              => array(
				'title' => $template['to_course_page'],
			),
			'to_completion_page'          => array(
				'title' => __( 'View completion page', 'thrive-apprentice' ),
			),
			'mark_complete'               => array(
				'title' => __( 'Mark lesson complete', 'thrive-apprentice' ),
			),
			'mark_complete_requirements'  => array(
				'title' => __( 'You must complete all requirements for this lesson in order to mark it as complete', 'thrive-apprentice' ),
			),
			'download_certificate_notice' => array(
				'title' => __( 'Certificate unavailable. You must be logged in and have earned a certificate in order to download it', 'thrive-apprentice' ),
			),
		);
	}

	/**
	 * Get the values for course structure labels
	 * this is also used for backwards compatibility
	 *
	 * @param TVA_Setting $template
	 *
	 * @return array[]
	 */
	public static function get_course_structure_labels( $template = null ) {
		if ( ! $template ) {
			$template = TVA_Setting::get( 'template' );
		}

		return array(
			'course_lesson'        => array(
				'singular' => $template['course_lesson'],
				'plural'   => $template['course_lessons'],
			),
			'course_chapter'       => array(
				'singular' => $template['course_chapter'],
				'plural'   => $template['course_chapters'],
			),
			'course_module'        => array(
				'singular' => $template['course_module'],
				'plural'   => $template['course_modules'],
			),
			'course_resources'     => array(
				'plural' => isset( $template['resources_label'] ) ? $template['resources_label'] : 'Resources',
			),
			'resources_open'       => array(
				'singular' => isset( $template['resources_open'] ) ? $template['resources_open'] : 'Open',
			),
			'resources_download'   => array(
				'singular' => isset( $template['resources_download'] ) ? $template['resources_download'] : 'Download',
			),
			'certificate_download' => array(
				'singular' => isset( $template['certificate_download'] ) ? $template['certificate_download'] : 'Download certificate',
			),
			'course_assessment'        => array(
				'singular' => __( 'Assessment', 'thrive-apprentice' ),
				'plural'   => __( 'Assessments', 'thrive-apprentice' ),
			),
			'assessment_not_graded'    => array(
				'singular' => __( 'Not Graded', 'thrive-apprentice' ),
			),
			'item_not_completed'    => array(
				'singular' => __( 'Not Completed', 'thrive-apprentice' ),
			),
			'item_completed'    => array(
				'singular' => __( 'Completed', 'thrive-apprentice' ),
			),
			'assessments_external' => array(
				'singular' => isset( $template['certificate_download'] ) ? $template['certificate_download'] : 'External Link',
			),
			'assessments_youtube'  => array(
				'singular' => isset( $template['certificate_download'] ) ? $template['certificate_download'] : 'YouTube Link',
			),
			'assessments_quiz'     => array(
				'singular' => isset( $template['certificate_download'] ) ? $template['certificate_download'] : 'Quiz',
			),
			'assessments_upload'   => array(
				'singular' => isset( $template['certificate_download'] ) ? $template['certificate_download'] : 'File Upload',
			),
			'assessments_pass'     => array(
				'singular' => isset( $template['certificate_download'] ) ? $template['certificate_download'] : 'Passed',
			),
			'assessments_fail'     => array(
				'singular' => isset( $template['certificate_download'] ) ? $template['certificate_download'] : 'Failed',
			),
		);
	}

	/**
	 * Get the values for course progress labels
	 * this is also used for backwards compatibility
	 *
	 * @param TVA_Setting $template
	 *
	 * @return array[]
	 */
	public static function get_course_progress_labels( $template = null ) {
		if ( ! $template ) {
			$template = TVA_Setting::get( 'template' );
		}

		return array(
			'not_started'      => array(
				'title' => $template['progress_bar_not_started'],
			),
			'in_progress'      => array(
				'title' => 'In progress',
			),
			'finished'         => array(
				'title' => $template['progress_bar_finished'],
			),
			'label'            => array(
				'title' => $template['progress_bar'],
			),
			'lesson_completed' => array(
				'title' => 'Lesson completed',
			),
		);
	}
}
