<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Reporting;

use TVE\Reporting\Shortcode;

class Main {
	public static function init() {
		add_action( 'thrive_reporting_register_events', [ __CLASS__, 'register_events' ] );
		add_action( 'thrive_reporting_register_report_apps', [ __CLASS__, 'register_report_apps' ] );

		add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_scripts' ] );
	}

	public static function enqueue_scripts() {
		if ( tve_get_current_screen_key() === 'thrive-dashboard_page_thrive_apprentice' ) {
			Shortcode::enqueue_scripts();
		}
	}

	public static function register_events() {
		\TVA\Reporting\Events\Lesson_Start::register();
		\TVA\Reporting\Events\Lesson_Complete::register();
		\TVA\Reporting\Events\Lesson_Unlocked_By_Quiz::register();
		\TVA\Reporting\Events\Free_Lesson_Complete::register();
		\TVA\Reporting\Events\All_Free_Lessons_Completed::register();
		\TVA\Reporting\Events\Course_Start::register();
		\TVA\Reporting\Events\Module_Start::register();
		\TVA\Reporting\Events\Module_Finish::register();
		\TVA\Reporting\Events\Course_Finish::register();
		\TVA\Reporting\Events\Product_Purchase::register();
		\TVA\Reporting\Events\Drip_Unlocked_For_User::register();
		\TVA\Reporting\Events\File_Download::register();
		\TVA\Reporting\Events\Certificate_Download::register();
		\TVA\Reporting\Events\Certificate_Verify::register();
		\TVA\Reporting\Events\Assessment_Failed::register();
		\TVA\Reporting\Events\Assessment_Passed::register();
		\TVA\Reporting\Events\Assessment_Submitted::register();
		\TVA\Reporting\Events\Video_Start::register();
		\TVA\Reporting\Events\Video_Data::register();
		\TVA\Reporting\Events\Video_Completed::register();
	}

	public static function register_report_apps() {
		\TVA\Reporting\ReportApps\Lesson::register();
		\TVA\Reporting\ReportApps\Module::register();
		\TVA\Reporting\ReportApps\Course::register();
		\TVA\Reporting\ReportApps\Product::register();
		\TVA\Reporting\ReportApps\Drip::register();
		\TVA\Reporting\ReportApps\User::register();
	}

	/**
	 * Shortcode config for reports available in the reports area
	 *
	 * @param string $type
	 * @param string $report_key
	 * @param string $icon
	 * @param array  $extra_attr
	 *
	 * @return void
	 */
	public static function render_report( $type = '', $report_key = '', $icon = '', $extra_attr = [] ) {
		$shortcodes = [
			'courses_filter'       => [
				'persist-value'       => 1,
				'is-global'           => 1,
				'report-app'          => 'tva_course',
				'report-type'         => 'tva_course_start',
				'field-key'           => 'course_id',
				'placeholder'         => 'All courses',
				'no-options-text'     => 'No courses found.',
				'retrieve-all-values' => 1,
			],
			'course_status_filter' => [
				'persist-value'       => 1,
				'is-global'           => 1,
				'report-app'          => 'tva_course',
				'report-type'         => 'popular_courses',
				'field-key'           => 'course_status',
				'placeholder'         => 'All statuses',
				'no-options-text'     => 'No status found.',
				'retrieve-all-values' => 1,
				'default-value'       => 'publish',
				'options'             => Utils::encode_array( [
					[
						'id'    => 'publish',
						'label' => __( 'Published courses', 'thrive-apprentice' ),
					],
					[
						'id'    => 'draft',
						'label' => __( 'Draft courses', 'thrive-apprentice' ),
					],
				] ),
			],
			'engagements_filter'   => [
				'persist-value'       => 1,
				'is-global'           => 1,
				'report-app'          => 'tva_course',
				'report-type'         => 'engagement_type',
				'field-key'           => 'engagement_type',
				'options'             => Utils::encode_array( [
					[
						'id'    => 'comments',
						'label' => __( 'Comments', 'thrive-apprentice' ),
					],
					[
						'id'    => 'enrollments',
						'label' => __( 'Enrollments', 'thrive-apprentice' ),
					],
					[
						'id'    => 'lesson_completions',
						'label' => __( 'Lesson completions', 'thrive-apprentice' ),
					],
					[
						'id'    => 'quiz_completions',
						'label' => __( 'Quiz completions', 'thrive-apprentice' ),
					],
					[
						'id'    => 'certificate_downloads',
						'label' => __( 'Certificate downloads', 'thrive-apprentice' ),
					],
					[
						'id'    => 'certificate_verifications',
						'label' => __( 'Certificate verifications', 'thrive-apprentice' ),
					],
					[
						'id'    => 'assessment_submissions',
						'label' => __( 'Assessment submissions', 'thrive-apprentice' ),
					],
					[
						'id'    => 'assessment_passes',
						'label' => __( 'Assessment passes', 'thrive-apprentice' ),
					],
					[
						'id'    => 'assessment_fails',
						'label' => __( 'Assessment fails', 'thrive-apprentice' ),
					],
					[
						'id'    => 'video_started',
						'label' => __( 'Started videos', 'thrive-apprentice' ),
					],
					[
						'id'    => 'video_complete',
						'label' => __( 'Completed videos', 'thrive-apprentice' ),
					],
				] ),
				'placeholder'         => 'All types',
				'retrieve-all-values' => 1,
			],
			'users_filter'         => [
				'persist-value'       => 1,
				'is-global'           => 1,
				'report-app'          => 'tva_course',
				'report-type'         => 'tva_course_start',
				'field-key'           => 'user_id',
				'placeholder'         => 'All members',
				'no-options-text'     => 'No users found.',
				'retrieve-all-values' => 1,
			],
			'products_filter'      => [
				'persist-value'       => 1,
				'is-global'           => 1,
				'report-app'          => 'tva_drip',
				'report-type'         => 'tva_drip_unlocked_for_user',
				'field-key'           => 'product_id',
				'placeholder'         => 'All products',
				'retrieve-all-values' => 1,
			],
			'date_filter'          => [
				'persist-value' => 1,
				'is-global'     => 1,
				'report-app'    => 'tva_course',
				'report-type'   => 'tva_course_start',
				'field-key'     => 'date',
				'default-value' => 'last_3_months',
			],
			'enrollments'          => [
				'report-app'            => 'tva_course',
				'report-type'           => 'tva_course_enrollments',
				'report-group-by'       => 'date',
				'report-title'          => 'Course enrollments',
				'report-global-filters' => 'date,course_id,user_id',
				'report-expanded-view'  => '#reports/courses/enrollments',
				'has-chart'             => 1,
				'chart-config'          => [
					'type'       => 'line',
					'cumulative' => 1,
				],
			],
			'course_completion'    => [
				'has-chart'             => 1,
				'report-app'            => 'tva_course',
				'report-type'           => 'tva_course_finish',
				'report-group-by'       => 'date',
				'report-title'          => 'Course completions',
				'report-global-filters' => 'date,course_id,user_id',
				'report-expanded-view'  => '#reports/courses/completions',
				'chart-config'          => [
					'cumulative' => 1,
					'type'       => 'line',
				],
			],
			'course_progress'      => [
				'report-app'            => 'tva_course',
				'report-type'           => 'course_progress',
				'report-group-by'       => 'date',
				'report-title'          => 'Average course progress',
				'report-global-filters' => 'date,course_id,user_id',
				'report-expanded-view'  => '#reports/courses/progress',
				'has-chart'             => 1,
				'chart-config'          => [
					'type'       => 'line',
					'cumulative' => 0,
				],
			],
			'engagements'          => [
				'has-date-comparison'   => 1,
				'report-app'            => 'tva_course',
				'report-type'           => 'engagement_type',
				'report-group-by'       => 'engagement_type,date',
				'report-data-type'      => 'chart',
				'report-title'          => 'Engagements',
				'report-global-filters' => 'date,course_id,user_id,engagement_type',
				'report-expanded-view'  => '#reports/courses/engagements',
				'has-chart'             => 0,
				'chart-config'          => [
					'type'       => 'line',
					'cumulative' => 1,
				],
			],
			'all_engagements'      => [
				'has-date-comparison' => 1,
				'report-app'          => 'tva_course',
				'report-type'         => 'all_engagements',
				'report-group-by'     => 'date',
				'report-data-type'    => 'chart',
				'report-title'        => 'Member activity',
				'has-chart'           => 0,
				'chart-config'        => [
					'type'              => 'line',
					'cumulative'        => 0,
					'cumulative-toggle' => 1,
				],
			],
			'new_members'          => [
				'report-group-by'       => 'date',
				'has-date-comparison'   => 1,
				'report-app'            => 'tva_user',
				'report-type'           => 'tva_new_members',
				'report-title'          => 'New members',
				'report-global-filters' => 'date,course_id,user_id',
				'report-expanded-view'  => '#reports/courses/members',
				'chart-config'          => [
					'type'       => 'line',
					'cumulative' => 1,
				],
			],
			'lesson_completion'    => [
				'has-date-comparison'   => 1,
				'report-size'           => 'lg',
				'report-app'            => 'tva_lesson',
				'report-type'           => 'tva_lesson_complete',
				'report-global-filters' => 'date,course_id,user_id',
				'report-group-by'       => 'course_id,date',
				'report-title'          => 'Lesson drop-off rate',
				'report-expanded-view'  => '#reports/courses/lessons',
				'chart-config'          => [
					'type'       => 'line',
					'cumulative' => 1,
				],
			],
			'popular_courses'      => [
				'report-items-per-page' => 6,
				'report-group-by'       => 'course_id',
				'report-app'            => 'tva_course',
				'report-type'           => 'popular_courses',
				'report-global-filters' => 'date,course_id,user_id,course_status',
				'report-title'          => 'Popular courses',
				'report-expanded-view'  => '#reports/courses/popular',
				'report-table-columns'  => 'course_id,enrollments,completed_lessons,completion_rate',
				'chart-config'          => [
					'type' => 'pie',
				],
			],
			'top_members'          => [
				'report-app'            => 'tva_user',
				'report-type'           => 'top_members',
				'report-items-per-page' => 6,
				'report-global-filters' => 'date,course_id,user_id',
				'report-title'          => 'Top members',
				'report-data-type'      => 'table',
				'report-expanded-view'  => '#reports/courses/top-members',
				'report-table-columns'  => 'user_id,enrollments,completed_lessons,completion_rate',
			],
			'latest_activity'      => [
				'report-global-filter-fields' => 'date,user_id,course_id',
				'report-size'                 => 'sm',
				'report-title'                => 'Latest activity',
				'report-expanded-view'        => '#reports/courses/activity',
				'user-url'                    => '#members/{user}/courses',
			],
			'active_members'       => [
				'has-date-comparison'   => 1,
				'report-app'            => 'tva_user',
				'report-type'           => 'tva_active_members',
				'report-title'          => 'Active members',
				'report-global-filters' => 'date',
			],
			'average_products'     => [
				'has-date-comparison'   => 1,
				'report-app'            => 'tva_product',
				'report-type'           => 'tva_average_products',
				'report-title'          => 'Average products',
				'report-global-filters' => 'date',
			],
		];

		echo do_shortcode( sprintf( '[%s %s] %s [/%s]', $type,
			Shortcode::render_attr( Shortcode::recursive_merge_atts( $shortcodes[ $report_key ], $extra_attr, false ) ),
			static::icons( $icon ),
			$type ) );
	}

	/**
	 * Icons used for reporting area
	 *
	 * @param $key
	 *
	 * @return string
	 */
	public static function icons( $key = '' ) {
		$icons = [
			'arrow-in'    => tva_get_svg_icon( 'report-course-enrollments', '', true ),
			'flag'        => '<svg class="trd-icon" viewBox="0 0 20 20">
								<path d="M6.137 3.479a6.78 6.78 0 0 0-3.008 1.015v2.774c.312-.208.749-.404 1.308-.586a9.722 9.722 0 0 1 1.7-.39V3.478zm5.976 1.21c-.52-.103-1.25-.299-2.187-.585l-.781-.274v2.657c.26.078.664.208 1.21.39.782.235 1.368.404 1.758.508V4.69zM6.137 9.066C7.23 8.96 8.233 9 9.145 9.182V6.487a7.598 7.598 0 0 0-1.563-.274c-.469-.026-.95 0-1.445.078v2.774zm3.008 2.812c.52.104 1.25.3 2.187.586l.781.274V10.04L11.06 9.69c-.834-.26-1.472-.43-1.914-.508v2.695zm-6.016.625c.885-.286 1.888-.495 3.008-.625V9.065a13.531 13.531 0 0 0-3.008.664v2.773zm15-8.75a15.162 15.162 0 0 1-3.008.899v2.812c.755-.13 1.758-.443 3.008-.937V3.752zm0 5.547c-.339.208-.781.404-1.328.586a9.254 9.254 0 0 1-1.68.39v2.813a6.78 6.78 0 0 0 3.008-1.015V9.299zm-6.016.742c1.068.287 2.07.365 3.008.235V7.463a8.39 8.39 0 0 1-3.008-.078v2.656zm6.563-8.554c-2.084.963-3.828 1.445-5.235 1.445-.468 0-.963-.065-1.484-.195a20.3 20.3 0 0 1-1.484-.43c-.756-.26-1.342-.43-1.758-.508a8.683 8.683 0 0 0-1.953-.234c-1.068 0-2.07.182-3.008.547.078-.573-.085-1.075-.488-1.504C2.862.178 2.374-.024 1.8.002A1.876 1.876 0 0 0 .57.55C.218.888.03 1.285.003 1.741c-.026.455.078.852.312 1.19.235.34.547.574.938.704v15.898c0 .13.045.241.137.332a.452.452 0 0 0 .332.137h.312c.13 0 .241-.045.332-.137a.452.452 0 0 0 .137-.332v-4.765a13.884 13.884 0 0 1 2.187-.781c.938-.235 1.98-.352 3.125-.352.47 0 .964.065 1.485.195a20.3 20.3 0 0 1 1.484.43c.755.26 1.341.43 1.758.508a8.683 8.683 0 0 0 1.953.234c.964 0 1.875-.156 2.734-.469.782-.26 1.563-.664 2.344-1.21a.873.873 0 0 0 .43-.782V2.346c0-.365-.143-.638-.43-.82-.286-.183-.586-.196-.898-.04zm-17.578.39a.75.75 0 0 1 .234-.547.75.75 0 0 1 .547-.234.75.75 0 0 1 .547.234.75.75 0 0 1 .234.547.75.75 0 0 1-.234.547.75.75 0 0 1-.547.234.75.75 0 0 1-.547-.234.75.75 0 0 1-.234-.547zm17.656.938v9.57a6.53 6.53 0 0 1-1.797.938 7.54 7.54 0 0 1-2.46.43c-.548 0-1.12-.066-1.72-.196a85.968 85.968 0 0 1-1.62-.488 20.772 20.772 0 0 0-1.622-.45 6.808 6.808 0 0 0-1.719-.234c-.911 0-1.862.098-2.851.293-.99.195-1.81.436-2.461.723V4.18A6.53 6.53 0 0 1 4.3 3.245a7.54 7.54 0 0 1 2.46-.43c.548 0 1.12.066 1.72.196.364.104.904.267 1.62.488.717.222 1.257.371 1.622.45a6.808 6.808 0 0 0 1.718.234c.834 0 1.784-.156 2.852-.469a13.727 13.727 0 0 0 2.46-.898z"
									  fill="#B0B9C1" fill-rule="nonzero"/>
							</svg>',
			'chart-grow'  => '<svg class="trd-icon" viewBox="0 0 19 20">
								<g fill="#B0B9C1" fill-rule="evenodd">
									<path d="M1.266 14.741c.117 0 .216.041.298.123a.407.407 0 0 1 .123.299v3.656a.407.407 0 0 1-.123.299.407.407 0 0 1-.298.123H.422a.407.407 0 0 1-.299-.123.407.407 0 0 1-.123-.299v-3.656c0-.117.041-.217.123-.299a.407.407 0 0 1 .299-.123h.844zm3.09-1.266c0-.117.042-.216.124-.298a.407.407 0 0 1 .299-.124h.843c.118 0 .217.041.3.124a.407.407 0 0 1 .122.298v5.344a.407.407 0 0 1-.123.299.407.407 0 0 1-.299.123H4.78a.407.407 0 0 1-.3-.123.407.407 0 0 1-.122-.299v-5.344zm4-2.812c0-.117.042-.217.124-.299a.407.407 0 0 1 .299-.123h.843c.118 0 .217.041.3.123a.407.407 0 0 1 .122.299v8.156a.407.407 0 0 1-.123.299.407.407 0 0 1-.299.123H8.78a.407.407 0 0 1-.3-.123.407.407 0 0 1-.122-.299v-8.156zm4-3.938c0-.117.042-.216.124-.298a.407.407 0 0 1 .299-.124h.843c.118 0 .217.041.3.124a.407.407 0 0 1 .122.298V18.82a.407.407 0 0 1-.123.299.407.407 0 0 1-.299.123h-.843a.407.407 0 0 1-.3-.123.407.407 0 0 1-.122-.299V6.725zm4-5.062c0-.117.042-.217.124-.299a.407.407 0 0 1 .299-.123h.843c.118 0 .217.041.3.123a.407.407 0 0 1 .122.299v17.156a.407.407 0 0 1-.123.299.407.407 0 0 1-.299.123h-.843a.407.407 0 0 1-.3-.123.407.407 0 0 1-.122-.299V1.663z"
										  fill-rule="nonzero"/>
									<path d="m.378 9.861 7.57-7.593L6.533.854A.5.5 0 0 1 6.886 0h4.179a.5.5 0 0 1 .5.5v3.805a.5.5 0 0 1-.854.354L9.475 3.423l-7.761 7.692a.5.5 0 0 1-.68.023l-.629-.546a.5.5 0 0 1-.027-.73z"/>
								</g>
							</svg>',
			'users'       => tva_get_svg_icon( 'report-total-students', '', true ),
			'check'       => '<svg width="20" height="20" viewBox="0 0 20 20">
								<path d="M9.687 0a9.39 9.39 0 0 1 4.844 1.309 9.789 9.789 0 0 1 3.535 3.535 9.39 9.39 0 0 1 1.309 4.843 9.39 9.39 0 0 1-1.309 4.844 9.789 9.789 0 0 1-3.535 3.535 9.39 9.39 0 0 1-4.844 1.309 9.39 9.39 0 0 1-4.843-1.309 9.789 9.789 0 0 1-3.535-3.535A9.39 9.39 0 0 1 0 9.687a9.39 9.39 0 0 1 1.309-4.843 9.789 9.789 0 0 1 3.535-3.535A9.39 9.39 0 0 1 9.687 0zm0 1.875A7.58 7.58 0 0 0 5.781 2.93 7.878 7.878 0 0 0 2.93 5.78a7.58 7.58 0 0 0-1.055 3.906 7.58 7.58 0 0 0 1.055 3.907 7.878 7.878 0 0 0 2.851 2.851A7.58 7.58 0 0 0 9.687 17.5a7.58 7.58 0 0 0 3.907-1.055 7.878 7.878 0 0 0 2.851-2.851A7.58 7.58 0 0 0 17.5 9.687a7.58 7.58 0 0 0-1.055-3.906 7.878 7.878 0 0 0-2.851-2.851 7.58 7.58 0 0 0-3.907-1.055zm5.47 5.078a.486.486 0 0 1 .155.352c0 .13-.052.234-.156.312l-6.719 6.68a.486.486 0 0 1-.351.156.371.371 0 0 1-.313-.156L4.22 10.742a.486.486 0 0 1-.157-.351c0-.13.053-.235.157-.313l.898-.898a.422.422 0 0 1 .313-.118c.13 0 .247.04.351.118l2.305 2.382 5.547-5.507a.422.422 0 0 1 .312-.118c.13 0 .248.053.352.157l.86.86z"
									  fill="#B0B9C1" fill-rule="nonzero"/>
							</svg>',
			'star'        => '<svg class="trd-icon" viewBox="0 0 576 512">
								<path d="M528.1 171.5L382 150.2 316.7 17.8c-11.7-23.6-45.6-23.9-57.4 0L194 150.2 47.9 171.5c-26.2 3.8-36.7 36.1-17.7 54.6l105.7 103-25 145.5c-4.5 26.3 23.2 46 46.4 33.7L288 439.6l130.7 68.7c23.2 12.2 50.9-7.4 46.4-33.7l-25-145.5 105.7-103c19-18.5 8.5-50.8-17.7-54.6zM388.6 312.3l23.7 138.4L288 385.4l-124.3 65.3 23.7-138.4-100.6-98 139-20.2 62.2-126 62.2 126 139 20.2-100.6 98z"></path>
							</svg>',
			'trophy'      => '<svg width="23px" height="20px" viewBox="0 0 23 20" version="1.1">
								<g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
									<g id="Downloadable-icons" transform="translate(-429.000000, -26.000000)" fill="#B0B9C1" fill-rule="nonzero">
										<g id="trophy-alt" transform="translate(429.000000, 26.000000)">
											<path d="M14.0234041,5.42967455 C14.1796537,5.45571636 14.2838203,5.54035118 14.3359033,5.6835802 C14.3879863,5.82680922 14.3619449,5.95050645 14.2577785,6.05467306 L13.0077815,7.26560768 L13.3202807,8.94529117 C13.3463225,9.1015408 13.2942389,9.22523862 13.1640311,9.31638404 C13.0338233,9.40752946 12.9036149,9.41404006 12.773407,9.33591524 L11.2499732,8.5156047 L9.72653931,9.33591524 C9.59633149,9.41404006 9.46612307,9.40752946 9.33591524,9.31638404 C9.20570742,9.22523862 9.15362381,9.1015408 9.17966561,8.94529117 L9.49216487,7.26560768 L8.24216785,6.05467306 C8.13800147,5.95050645 8.11196002,5.82680922 8.16404304,5.6835802 C8.21612605,5.54035118 8.32029266,5.45571636 8.47654229,5.42967455 L10.1562258,5.19530011 L10.9374739,3.63280384 C10.9895569,3.50259601 11.0937236,3.4374918 11.2499732,3.4374918 C11.4062228,3.4374918 11.52341,3.50259601 11.6015348,3.63280384 L12.3437206,5.19530011 L14.0234041,5.42967455 Z M17.4999583,2.5 L21.8749478,2.5 C22.0572393,2.5 22.2069783,2.55858765 22.3241655,2.67577487 C22.4413527,2.79296209 22.4999464,2.94270112 22.4999464,3.12499255 L22.4999464,5.50779937 C22.4999464,6.28904751 22.2265095,7.06378504 21.6796358,7.83201258 C21.1327621,8.60024011 20.3840658,9.27732163 19.4335474,9.86325773 C18.483029,10.4491938 17.4218335,10.8463285 16.2499613,11.0546611 C15.7551706,12.1223667 15.1301721,12.9947609 14.3749657,13.6718424 C13.6718424,14.2707991 12.9426773,14.6744444 12.1874709,14.882777 L12.1874709,18.1249568 L14.687465,18.1249568 C15.1301721,18.1249568 15.5012649,18.2746958 15.8007436,18.5741745 C16.1002222,18.8736531 16.2499613,19.244746 16.2499613,19.6874531 C16.2499613,19.7655779 16.2174094,19.8371921 16.1523052,19.9022963 C16.087201,19.9674005 16.0155868,19.9999523 15.937462,19.9999523 L6.56248435,19.9999523 C6.48435954,19.9999523 6.41274533,19.9674005 6.34764112,19.9022963 C6.28253691,19.8371921 6.2499851,19.7655779 6.2499851,19.6874531 C6.2499851,19.244746 6.39972413,18.8736531 6.69920278,18.5741745 C6.99868143,18.2746958 7.36977429,18.1249568 7.81248137,18.1249568 L10.3124754,18.1249568 L10.3124754,14.882777 C9.55726908,14.6744444 8.82810395,14.2707991 8.12498063,13.6718424 C7.36977429,12.9947609 6.74477578,12.1223667 6.2499851,11.0546611 C5.07811289,10.8463285 4.01691731,10.4491938 3.06639894,9.86325773 C2.11588057,9.27732163 1.36718424,8.60024011 0.820310544,7.83201258 C0.273436848,7.06378504 0,6.28904751 0,5.50779937 L0,3.12499255 C0,2.94270112 0.0585936103,2.79296209 0.175780831,2.67577487 C0.292968052,2.55858765 0.442707079,2.5 0.62499851,2.5 L4.99998808,2.5 L4.99998808,0.62499851 C4.99998808,0.442707079 5.05858169,0.292968052 5.17576891,0.175780831 C5.29295613,0.0585936103 5.44269516,0 5.62498659,0 L16.8749598,0 C17.0572512,0 17.2069902,0.0585936103 17.3241774,0.175780831 C17.4413647,0.292968052 17.4999583,0.442707079 17.4999583,0.62499851 L17.4999583,2.5 Z M1.87499553,5.50779937 C1.87499553,6.08071487 2.21353659,6.70571338 2.89061811,7.3827949 C3.56769963,8.05987642 4.42707258,8.58070891 5.46873696,8.94529117 C5.15623771,7.66925235 4.99998808,6.14581848 4.99998808,4.37498957 L1.87499553,4.37498957 L1.87499553,5.50779937 Z M11.2499732,13.1249687 C11.9530965,13.1249687 12.630178,12.7994488 13.2812183,12.1484085 C13.9843417,11.471327 14.5312154,10.5208081 14.9218394,9.29685283 C15.3905883,7.968731 15.6249627,6.43227653 15.6249627,4.68748882 L15.6249627,1.87499553 L6.87498361,1.87499553 L6.87498361,4.68748882 C6.87498361,6.40623473 7.09633745,7.92966859 7.53904453,9.25779043 C7.9557104,10.4817456 8.50258409,11.4322646 9.17966561,12.1093461 C9.85674713,12.7864276 10.5468499,13.1249687 11.2499732,13.1249687 Z M20.6249508,5.50779937 L20.6249508,4.37498957 L17.4999583,4.37498957 C17.4999583,6.14581848 17.3437086,7.66925235 17.0312094,8.94529117 C18.0728738,8.58070891 18.9322467,8.05987642 19.6093282,7.3827949 C20.2864098,6.70571338 20.6249508,6.08071487 20.6249508,5.50779937 Z"
												  id="Shape"></path>
										</g>
									</g>
								</g>
							</svg>',
			'clock'       => '<svg width="20" height="20" viewBox="0 0 20 20">
								<path d="M9.687 0a9.39 9.39 0 0 1 4.844 1.309 9.789 9.789 0 0 1 3.535 3.535 9.39 9.39 0 0 1 1.309 4.843 9.39 9.39 0 0 1-1.309 4.844 9.789 9.789 0 0 1-3.535 3.535 9.39 9.39 0 0 1-4.844 1.309 9.39 9.39 0 0 1-4.843-1.309 9.789 9.789 0 0 1-3.535-3.535A9.39 9.39 0 0 1 0 9.687a9.39 9.39 0 0 1 1.309-4.843 9.789 9.789 0 0 1 3.535-3.535A9.39 9.39 0 0 1 9.687 0zm0 17.5a7.58 7.58 0 0 0 3.907-1.055 7.878 7.878 0 0 0 2.851-2.851A7.58 7.58 0 0 0 17.5 9.687a7.58 7.58 0 0 0-1.055-3.906 7.878 7.878 0 0 0-2.851-2.851 7.58 7.58 0 0 0-3.907-1.055A7.58 7.58 0 0 0 5.781 2.93 7.878 7.878 0 0 0 2.93 5.78a7.58 7.58 0 0 0-1.055 3.906 7.58 7.58 0 0 0 1.055 3.907 7.878 7.878 0 0 0 2.851 2.851A7.58 7.58 0 0 0 9.687 17.5zm-.898-6.484a.481.481 0 0 1-.195-.391V4.219c0-.13.045-.241.136-.332a.452.452 0 0 1 .332-.137h1.25c.13 0 .241.046.333.137a.452.452 0 0 1 .136.332v5.547l2.617 1.875a.428.428 0 0 1 .176.312.486.486 0 0 1-.097.352l-.704 1.015a.5.5 0 0 1-.312.196.423.423 0 0 1-.352-.079l-3.32-2.421z"
									  fill="#B0B9C1" fill-rule="nonzero"/>
							</svg>',
			'user-clock'  => '<svg width="26" height="21" viewBox="0 0 26 21" xmlns="http://www.w3.org/2000/svg">
    								<g fill="#B0B9C1" fill-rule="nonzero">
      								  <path d="M5.25 12.375c1.125 0 1.66.625 3.5.625 1.562 0 2.186-.45 3.025-.586a8.012 8.012 0 0 0-.275 2.01c-.585.184-1.43.451-2.75.451-2.004 0-2.93-.625-3.5-.625a3.38 3.38 0 0 0-3.375 3.375v1h10.769a8.039 8.039 0 0 0 1.565 1.875H1.875A1.875 1.875 0 0 1 0 18.625v-1a5.251 5.251 0 0 1 5.25-5.25zM8.75.5a5.626 5.626 0 0 1 5.625 5.625A5.626 5.626 0 0 1 8.75 11.75a5.626 5.626 0 0 1-5.625-5.625A5.626 5.626 0 0 1 8.75.5zm0 1.875A3.756 3.756 0 0 0 5 6.125a3.756 3.756 0 0 0 3.75 3.75 3.756 3.756 0 0 0 3.75-3.75 3.756 3.756 0 0 0-3.75-3.75z"/>
     								   <path d="M19 8.588c1.633 0 3.111.661 4.181 1.731a5.894 5.894 0 0 1 1.731 4.181 5.894 5.894 0 0 1-1.731 4.181A5.894 5.894 0 0 1 19 20.412a5.894 5.894 0 0 1-4.181-1.73 5.894 5.894 0 0 1-1.732-4.182c0-1.633.662-3.111 1.732-4.181A5.894 5.894 0 0 1 19 8.588zm0 1.325a4.573 4.573 0 0 0-3.244 1.343 4.573 4.573 0 0 0-1.344 3.244c0 1.267.514 2.414 1.344 3.244A4.573 4.573 0 0 0 19 19.088a4.573 4.573 0 0 0 3.244-1.344 4.573 4.573 0 0 0 1.343-3.244 4.573 4.573 0 0 0-1.343-3.244A4.573 4.573 0 0 0 19 9.913zm.465.896.287 3.677 1.59 1.157-.448 1.182-2.534-1.472-.118-4.21 1.223-.334z" stroke="#B0B9C1" stroke-width=".2"/>
 								   </g>
							</svg>',
			'user-plus'   => '<svg width="24" height="20" viewBox="0 0 24 20">
								<path d="M8.312 11.063a5.345 5.345 0 0 0 5.344-5.344A5.345 5.345 0 0 0 8.312.375 5.345 5.345 0 0 0 2.97 5.719a5.345 5.345 0 0 0 5.343 5.344zm0-8.907c1.964 0 3.563 1.6 3.563 3.563s-1.6 3.562-3.563 3.562A3.568 3.568 0 0 1 4.75 5.72c0-1.963 1.6-3.563 3.562-3.563zm3.325 9.5c-1.065 0-1.577.594-3.325.594-1.747 0-2.256-.594-3.324-.594A4.989 4.989 0 0 0 0 16.644v.95c0 .983.798 1.781 1.781 1.781h13.063c.983 0 1.781-.798 1.781-1.781v-.95a4.989 4.989 0 0 0-4.988-4.988zm3.207 5.938H1.78v-.95a3.212 3.212 0 0 1 3.207-3.206c.541 0 1.42.593 3.324.593 1.919 0 2.78-.593 3.325-.593a3.212 3.212 0 0 1 3.207 3.206v.95zm8.312-9.203h-2.672V5.719a.596.596 0 0 0-.593-.594h-.594a.596.596 0 0 0-.594.594V8.39h-2.672a.596.596 0 0 0-.594.593v.594c0 .327.268.594.594.594h2.672v2.672c0 .326.267.594.594.594h.594a.596.596 0 0 0 .593-.594v-2.672h2.672a.596.596 0 0 0 .594-.594v-.594a.596.596 0 0 0-.594-.593z" fill="#B0B9C1" fill-rule="nonzero"/>
							</svg>',
			'abacus'      => '<svg width="21" height="19" viewBox="0 0 21 19">
								<path d="M19.406.25a.844.844 0 0 0-.844.844V2.78h-1.687v-.843a.562.562 0 0 0-.563-.563h-.562a.562.562 0 0 0-.563.563v.843h-6.75v-.843a.562.562 0 0 0-.562-.563h-.563a.562.562 0 0 0-.562.563v.843H5.062v-.843a.562.562 0 0 0-.562-.563h-.563a.562.562 0 0 0-.562.563v.843H1.687V1.094a.844.844 0 1 0-1.687 0v16.734c0 .233.189.422.422.422h.844a.422.422 0 0 0 .421-.422v-2.11h1.688v.844c0 .311.252.563.562.563H4.5c.31 0 .562-.252.562-.563v-.843H6.75v.843c0 .311.252.563.562.563h.563c.31 0 .562-.252.562-.563v-.843h6.75v.843c0 .311.252.563.563.563h.562c.311 0 .563-.252.563-.563v-.843h1.687v2.11c0 .232.19.421.422.421h.844a.422.422 0 0 0 .422-.422V1.094a.844.844 0 0 0-.844-.844zM3.375 4.469v.844c0 .31.252.562.562.562H4.5c.31 0 .562-.252.562-.562v-.844H6.75v.844c0 .31.252.562.562.562h.563c.31 0 .562-.252.562-.562v-.844h6.75v.844c0 .31.252.562.563.562h.562c.311 0 .563-.252.563-.562v-.844h1.687v3.937h-6.75v-.843A.562.562 0 0 0 11.25 7h-.563a.562.562 0 0 0-.562.563v.843H8.437v-.843A.562.562 0 0 0 7.875 7h-.563a.562.562 0 0 0-.562.563v.843H5.062v-.843A.562.562 0 0 0 4.5 7h-.563a.562.562 0 0 0-.562.563v.843H1.687V4.47h1.688zm13.5 9.562v-.843a.562.562 0 0 0-.563-.563h-.562a.562.562 0 0 0-.563.563v.843h-6.75v-.843a.562.562 0 0 0-.562-.563h-.563a.562.562 0 0 0-.562.563v.843H5.062v-.843a.562.562 0 0 0-.562-.563h-.563a.562.562 0 0 0-.562.563v.843H1.687v-3.937h1.688v.844c0 .31.252.562.562.562H4.5c.31 0 .562-.252.562-.562v-.844H6.75v.844c0 .31.252.562.562.562h.563c.31 0 .562-.252.562-.562v-.844h1.688v.844c0 .31.252.562.562.562h.563c.31 0 .562-.252.562-.562v-.844h6.75v3.937h-1.687z" fill="#B0B9C1" fill-rule="nonzero"/>
							</svg>',
			'arrow-clock' => '<svg width="20" height="21" viewBox="0 0 20 21">
								<path d="M19.687 10.482C19.677 5.14 15.344.812 10 .812A9.656 9.656 0 0 0 3.122 3.68L1.361 1.94a.469.469 0 0 0-.798.334V7.22c0 .259.21.469.469.469h5.013c.419 0 .627-.509.329-.803L4.456 4.994A7.787 7.787 0 0 1 10 2.688a7.808 7.808 0 0 1 7.812 7.812A7.808 7.808 0 0 1 10 18.312a7.785 7.785 0 0 1-5.2-1.98.47.47 0 0 0-.648.016l-.662.662a.47.47 0 0 0 .017.68c1.72 1.554 4 2.5 6.5 2.497 5.337-.003 9.69-4.368 9.68-9.705zm-6.525 3.522.551-.758a.469.469 0 0 0-.103-.655l-2.673-1.943V5.03a.469.469 0 0 0-.468-.468H9.53a.469.469 0 0 0-.469.468v6.571l3.445 2.505c.21.153.502.106.655-.103z" fill="#B0B9C1" fill-rule="nonzero"/>
							</svg>',
			'heart-pulse' => '<svg width="20" height="19" viewBox="0 0 20 19" xmlns="http://www.w3.org/2000/svg">
								<path d="M10.406 16.207a.582.582 0 0 1-.816 0L5.34 12H2.676l5.597 5.54c.957.944 2.497.948 3.454 0L17.324 12H14.66l-4.254 4.207zM13.856.75c-1.426 0-2.774.48-3.856 1.363A6.064 6.064 0 0 0 6.145.75C3.367.75 0 2.973 0 6.844 0 8.3.535 9.66 1.477 10.75h4.562l1.168-2.8 2.223 4.933a.625.625 0 0 0 1.129.023L12.5 9.023l.863 1.727h5.156c.942-1.09 1.477-2.45 1.477-3.906C20 2.973 16.633.75 13.856.75zm3.277 8.75h-2.996l-1.078-2.156a.624.624 0 0 0-1.118 0l-1.91 3.824-2.273-5.05a.626.626 0 0 0-1.149.015L5.207 9.5h-2.34C.57 6.785 2.594 2.625 6.145 2.625c1.21 0 1.992.242 3.855 2.086 1.996-1.973 2.7-2.086 3.855-2.086 3.563 0 5.575 4.16 3.278 6.875z" fill="#B0B9C1" fill-rule="nonzero"/>
							</svg>',
			'circle-x'    => '<svg width="20" height="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
								<path d="M9.687 0A9.686 9.686 0 0 0 0 9.687a9.686 9.686 0 0 0 9.687 9.688 9.686 9.686 0 0 0 9.688-9.688A9.686 9.686 0 0 0 9.687 0zm0 17.5a7.81 7.81 0 0 1-7.812-7.813 7.81 7.81 0 0 1 7.812-7.812A7.81 7.81 0 0 1 17.5 9.687 7.81 7.81 0 0 1 9.687 17.5zm3.977-10.242a.47.47 0 0 0 0-.664l-.883-.883a.47.47 0 0 0-.664 0l-2.43 2.43-2.43-2.43a.47.47 0 0 0-.663 0l-.883.883a.47.47 0 0 0 0 .664l2.43 2.43-2.43 2.43a.47.47 0 0 0 0 .663l.883.883a.47.47 0 0 0 .664 0l2.43-2.43 2.43 2.43a.47.47 0 0 0 .663 0l.883-.883a.47.47 0 0 0 0-.664l-2.43-2.43 2.43-2.43z" fill="#B0B9C1" fill-rule="nonzero"/>
							</svg>',
			/* TODO: this could be duplicate */
			'info'        => '<svg width="20" height="21" viewBox="0 0 20 21">
								<path d="M10 .813a9.687 9.687 0 1 0 0 19.375 9.687 9.687 0 0 0 9.687-9.688C19.687 5.152 15.35.813 10 .813zm0 17.5A7.808 7.808 0 0 1 2.187 10.5 7.81 7.81 0 0 1 10 2.688a7.81 7.81 0 0 1 7.812 7.812A7.808 7.808 0 0 1 10 18.312zm0-13.204a1.64 1.64 0 1 0 0 3.282 1.64 1.64 0 0 0 0-3.282zm2.187 9.922v-.937a.469.469 0 0 0-.468-.469h-.469V9.719a.469.469 0 0 0-.469-.469h-2.5a.469.469 0 0 0-.469.469v.937c0 .26.21.469.47.469h.468v2.5h-.469a.469.469 0 0 0-.469.469v.937c0 .26.21.469.47.469h3.437c.259 0 .468-.21.468-.469z" fill="#B0B9C1" fill-rule="nonzero"/>
							</svg>',
			'stat-bars'   => tva_get_svg_icon( 'report-average-products', '', true ),
			'light-bulb'  => tva_get_svg_icon( 'report-active-students', '', true ),
		];

		return empty( $icons[ $key ] ) ? '' : $icons[ $key ];
	}
}
