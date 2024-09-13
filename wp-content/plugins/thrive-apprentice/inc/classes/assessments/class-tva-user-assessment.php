<?php

namespace TVA\Assessments;

use Exception;
use JsonSerializable;
use ReturnTypeWillChange;
use TQB_Quiz;
use TQB_Reporting_Manager;
use TVA\Architect\Assessment\Main;
use TVA\Assessments\Grading\Base as Grading_Base;
use TVA\Assessments\Grading\Percentage;
use TVA\Assessments\Value\Base as Value_Base;
use TVA_Assessment;
use TVA_Const;
use WP_Post;
use WP_User;

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * @property int          ID
 * @property string       post_date
 * @property int          post_author
 * @property int          post_parent
 * @property string       status
 * @property string       grade
 * @property string       type
 * @property string|array value
 * @property array        config
 * @property array        user_submission_counter
 * @property string       notes
 */
class TVA_User_Assessment implements JsonSerializable {

	/**
	 * User assessment post type
	 */
	const POST_TYPE = 'tva_user_assessment';

	const STATUS_META   = 'tva_assessment_status';
	const OUTDATED_META = 'tva_assessment_outdated';

	const SUBMITTED_CACHE_KEY = 'tva_submitted_assessments';

	const COURSE_META = 'tva_assessment_course_id';

	const GRADE_META = 'tva_assessment_grade';

	const GRADING_METHOD = 'tva_grading_method';

	const NOTES_META = 'tva_assessment_notes';

	const SUBMISSION_COUNTER_META = 'tva_assessment_submission_counter_';

	/**
	 * @var WP_Post|null
	 */
	protected $post;

	/**
	 * @var array
	 */
	protected $data = [];

	/**
	 * @param array|WP_Post $data
	 */
	public function __construct( $data ) {
		if ( $data instanceof WP_Post ) {
			$this->post = $data;

			$data = $data->to_array();
		}

		$this->data = wp_parse_args( [
			'post_type'   => static::POST_TYPE,
			'post_title'  => 'User Assessment',
			'post_status' => 'draft',
		], $data );
	}

	/**
	 * Magic get method
	 *
	 * @param string $key
	 *
	 * @return array|mixed|null
	 */
	public function __get( $key ) {
		$value = null;

		if ( isset( $this->data[ $key ] ) ) {
			//First we check in cache
			$value = $this->data[ $key ];
		} elseif ( $this->post instanceof WP_Post && isset( $this->post->$key ) ) {
			//Second we check in post object
			$value = $this->post->$key;
		} elseif ( method_exists( $this, 'get_' . $key ) ) {
			//If we do not find in cache & post object we check in method
			$method_name = 'get_' . $key;
			$value       = $this->$method_name();
		}

		return $value;
	}

	/**
	 * @throws Exception
	 */
	public function create() {
		if ( $this->ID ) {
			$post_id = 0;
		} else {
			$post_id = wp_insert_post( $this->data, true );
		}

		if ( is_wp_error( $post_id ) ) {
			throw new Exception( $post_id->get_error_message(), $post_id->get_error_code() );
		}

		$this->post = get_post( $post_id );

		if ( $this->type ) {
			$this->set_type( $this->type );
		}

		/**
		 * @var Value_Base
		 */
		$assessment_value = Value_Base::factory( $this->type );

		if ( $this->value ) {
			$assessment_value->set_user_assessment_id( $this->ID )->save( $this->value );
		}

		/**
		 * This hook is triggered when a student submits an assessment
		 *
		 * @param TVA_User_Assessment $this
		 */
		do_action( 'tva_assessment_submitted', $this );

		$this->set_config( array_filter( array_merge( $assessment_value->get_value_config(), $this->config ) ) );
		$grading_details = Grading_Base::get_assessment_grading_details( $this->post_parent );

		if ( (int) $grading_details['grading_manually_mark'] === 0 ) {
			$this->automatic_grading( $grading_details );
		} else {
			$this->set_status( TVA_Const::ASSESSMENT_STATUS_PENDING_ASSESSMENT );
		}
		$this->set_course_id();

		/**
		 * Update user submission counter and set previous assessments to outdated
		 */
		$this->increase_user_submission_counter();
		$this->handle_previous_assessment();
		$this->handle_submitted_assessments();
	}

	/**
	 * Set previous assessment to outdated - so each new assessment will set the previous one to outdated
	 *
	 * @return void
	 */
	private function handle_previous_assessment() {
		$previous_assessments = get_posts(
			[
				'post_type'      => static::POST_TYPE,
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'post_parent'    => $this->post_parent,
				'exclude'        => $this->ID,
				'fields'         => 'ids',
				'author'         => $this->post_author,
			]
		);
		foreach ( $previous_assessments as $id ) {
			update_post_meta( $id, static::OUTDATED_META, 1 );
		}
	}

	public function get_user_submission_counter() {
		return (int) get_user_meta( $this->post_author, static::SUBMISSION_COUNTER_META . $this->post_parent, true ) ?: 0;
	}

	public function increase_user_submission_counter() {
		$counter = $this->get_user_submission_counter() + 1;

		update_user_meta( $this->post_author, static::SUBMISSION_COUNTER_META . $this->post_parent, $counter );
	}

	/**
	 * @return array
	 */
	#[ReturnTypeWillChange]
	public function jsonSerialize() {
		return [
			'ID'                      => $this->ID,
			'date'                    => $this->post_date,
			'type'                    => $this->type,
			'author'                  => get_the_author_meta( 'display_name', $this->post_author ),
			'value'                   => $this->value,
			'config'                  => $this->config,
			'user_submission_counter' => $this->user_submission_counter,
		];
	}

	/**
	 * @return array
	 */
	public function get_inbox_details() {
		return [
			'ID'                      => $this->ID,
			'date'                    => $this->post_date,
			'type'                    => $this->type,
			'author'                  => get_the_author_meta( 'display_name', $this->post_author ),
			'author_id'               => $this->post_author,
			'author_avatar'           => get_avatar_url( $this->post_author ),
			'value'                   => $this->value,
			'status'                  => $this->status,
			'grade'                   => $this->grade,
			'user_submission_counter' => $this->user_submission_counter,
			'config'                  => $this->config,
			'notes'                   => $this->notes,
		];
	}

	/**
	 * Register post type
	 *
	 * @return void
	 */
	public static function init_post_type() {
		register_post_type( static::POST_TYPE, [
			'labels'              => [
				'name'          => 'User Assessments',
				'singular_name' => 'User Assessment',
			],
			'publicly_queryable'  => true, //Needs to be queryable on front-end for products
			'public'              => false,
			'query_var'           => false,
			'rewrite'             => false,
			'show_in_nav_menus'   => false,
			'show_in_menu'        => false,
			'show_ui'             => false,
			'exclude_from_search' => true,
			'show_in_rest'        => true,
			'has_archive'         => false,
			'map_meta_cap'        => true,
		] );
	}

	/**
	 * Called from magic __get
	 *
	 * @return string
	 */
	private function get_type() {
		return get_post_meta( $this->ID, 'tva_assessment_type', true );
	}

	/**
	 * Called from magic __get
	 *
	 * @return array
	 */
	private function get_config() {
		return array_filter( (array) get_post_meta( $this->ID, 'tva_assessment_config', true ) );
	}

	/**
	 * Called from magic __get
	 *
	 * @return mixed
	 */
	private function get_value() {
		return get_post_meta( $this->ID, 'tva_assessment_value', true );
	}

	/**
	 * Called from magic __get
	 *
	 * @return mixed
	 */
	private function get_status() {
		return get_post_meta( $this->ID, static::STATUS_META, true ) ?: TVA_Const::ASSESSMENT_STATUS_PENDING_ASSESSMENT;
	}

	/**
	 * Saves the course ID the assessment is for
	 *
	 * @return void
	 */
	public function set_course_id() {

		if ( $this->post_parent ) {
			update_post_meta( $this->ID, static::COURSE_META, ( new TVA_Assessment( $this->post_parent ) )->get_course_v2()->get_id() );
		}
	}

	/**
	 * @return int The id of the course meta set on the user assessment
	 */
	public function get_course_id() {
		return (int) get_post_meta( $this->ID, static::COURSE_META, true );
	}

	/**
	 * @param boolean|string $status
	 *
	 * @return $this
	 */
	public function set_status( $status ) {

		if ( is_bool( $status ) ) {
			$status = $status === true ? TVA_Const::ASSESSMENT_STATUS_COMPLETED_PASSED : TVA_Const::ASSESSMENT_STATUS_COMPLETED_FAILED;
		}

		if ( ! in_array( $status, [ TVA_Const::ASSESSMENT_STATUS_PENDING_ASSESSMENT, TVA_Const::ASSESSMENT_STATUS_COMPLETED_PASSED, TVA_Const::ASSESSMENT_STATUS_COMPLETED_FAILED ] ) ) {
			$status = TVA_Const::ASSESSMENT_STATUS_PENDING_ASSESSMENT;
		}

		update_post_meta( $this->ID, static::STATUS_META, $status );

		return $this;
	}

	/**
	 * Called from magic __get
	 * Also called from frontend with $processed = true
	 *
	 * @return string
	 */
	public function get_grade( $processed = false ) {
		$grade = get_post_meta( $this->ID, static::GRADE_META, true ) ?: '';

		if ( ! empty( $grade ) && $processed === true ) {
			$method = Grading_Base::factory( get_post_meta( $this->post_parent, static::GRADING_METHOD, true ) );
			$manual_percentage = $method instanceof Percentage && ! str_contains( $method->get_value( $grade ), '%' );

			$grade = $method->get_value( $grade ) . ( $manual_percentage ? '%' : '' );
		}

		return $grade;
	}

	/**
	 * Called from magic __get
	 *
	 * @return mixed
	 */
	private function get_notes() {
		return get_post_meta( $this->ID, static::NOTES_META, true ) ?: '';
	}

	/**
	 * @param $value
	 *
	 * @return $this
	 */
	public function set_grade( $value ) {
		update_post_meta( $this->ID, static::GRADE_META, $value );

		return $this;
	}

	/**
	 * @param string $value
	 *
	 * @return $this
	 */
	public function set_notes( $value ) {
		update_post_meta( $this->ID, static::NOTES_META, $value );

		return $this;
	}

	private function set_type( $value ) {
		update_post_meta( $this->ID, 'tva_assessment_type', $value );
	}

	private function set_config( $config = [] ) {
		update_post_meta( $this->ID, 'tva_assessment_config', $config );
	}

	/**
	 * Returns the user submissions
	 * Used in TAR to render the result list element
	 *
	 * @param array $filters
	 *
	 * @return TVA_User_Assessment[]
	 */
	public static function get_user_submission( $filters = [] ) {
		$args = array_merge(
			[
				'post_type'      => static::POST_TYPE,
				'post_status'    => 'any',
				'posts_per_page' => - 1,
				'author'         => get_current_user_id(),
				'orderby'        => 'post_date',
				'order'          => 'DESC',
			],
			$filters );

		return array_map( static function ( $post ) {
			return new static( $post );
		}, get_posts( $args ) );
	}

	/**
	 * Delete all submissions for a course
	 *
	 * @param $course_id
	 *
	 * @return void
	 */
	public static function delete_course_submissions( $course_id ) {
		$posts = get_posts(
			[
				'post_type'      => static::POST_TYPE,
				'post_status'    => 'any',
				'posts_per_page' => - 1,
				'meta_query'     => [
					[
						'key'     => static::COURSE_META,
						'value'   => $course_id,
						'compare' => '=',
					],
				],
				'fields'         => 'ids',
			]
		);
		foreach ( $posts as $post_id ) {
			wp_delete_post( $post_id, true );
		}
	}

	/**
	 * Delete all submissions for a user
	 *
	 * @param $assessment_id
	 * @param $user_id
	 *
	 * @return void
	 */
	public static function delete_user_submissions( $assessment_id, $user_id = 0 ) {
		$posts = get_posts(
			[
				'post_type'      => static::POST_TYPE,
				'post_status'    => 'any',
				'posts_per_page' => - 1,
				'author'         => $user_id,
				'post_parent'    => $assessment_id,
				'fields'         => 'ids',
			]
		);

		foreach ( $posts as $post_id ) {
			wp_delete_post( $post_id, true );
		}

		delete_user_meta( $user_id, static::SUBMISSION_COUNTER_META . $assessment_id );
	}

	/**
	 * Whether there is at all submission on the site or not
	 *
	 * @return bool
	 */
	public static function has_submissions( $filters = [] ) {
		return (bool) get_posts( array_merge(
			[
				'post_type'      => static::POST_TYPE,
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_query'     => [
					[
						'key'     => 'tva_is_demo',
						'compare' => 'NOT EXISTS',
					],
				],
			],
			$filters
		) );
	}


	/**
	 * Sends email notification regarding the assessment if a template with the given trigger exists
	 *
	 * @param string $trigger The trigger for email notification
	 *
	 * @return bool Whether the mail was sent or not
	 */
	public function send_assessment_email_notification( $trigger ) {
		$email_template = tva_email_templates()->check_templates_for_trigger( $trigger );

		if ( ! $email_template ) {
			return false;
		}
		$user           = new WP_User( $this->post_author );
		$email_template = array_merge( $email_template, array(
			'user'            => $user,
			'user_assessment' => $this,
		) );

		/**
		 * Prepares the email template before sending it to the student
		 */
		do_action( 'tva_prepare_assessment_marked_email_template', $email_template );

		$to      = $user->user_email;
		$subject = $email_template['subject'];
		$body    = do_shortcode( nl2br( $email_template['body'] ) );
		$headers = array( 'Content-Type: text/html' );
		$sent    = wp_mail( $to, $subject, $body, $headers );

		if ( $sent ) {
			return true;
		}

		return false;
	}

	/**
	 * Sets the assessment as submitted for the user
	 *
	 * @return void
	 */
	public function handle_submitted_assessments() {
		$submitted_assessments                                     = get_user_meta( $this->post_author, static::SUBMITTED_CACHE_KEY, true );
		$submitted_assessments                                     = is_array( $submitted_assessments ) ? $submitted_assessments : [];
		$course_id                                                 = $this->get_course_id();
		$submitted_assessments[ $course_id ][ $this->post_parent ] = 1;

		update_user_meta( $this->post_author, static::SUBMITTED_CACHE_KEY, $submitted_assessments );
	}

	/**
	 * @param string|int $grade The grade to be saved
	 * @param string     $notes
	 *
	 * @return bool Whether the grading was successful or not
	 */
	public function save_grade( $grade, $notes ) {
		$grading_details = Grading_Base::get_assessment_grading_details( $this->post_parent );
		$grading         = Grading_Base::factory( $grading_details );

		if ( ! isset( $grading ) ) {
			return false;
		}

		$passed = $grading->passed( $grade );
		$this->set_grade( $grade )->set_status( $passed )->set_notes( $notes );

		$status = $passed ? 'passed' : 'failed';
		/**
		 * 'tva_assessment_passed' hook is triggered when the grade submitted by the user is a passing grade
		 * or 'tva_assessment_failed' hook when the grade is not a passing one
		 *
		 * @param string $status Can be either 'passed' or 'failed' depending on the $grade
		 * @param array  $assessment_data
		 */
		do_action( 'tva_assessment_' . $status, $this );

		$this->send_assessment_email_notification( 'assessment_' . $status );

		return true;
	}

	/**
	 * Sets the grade and status on automatic grading assessments
	 * For quizzes the grade is the quiz result, pass for surveys, or pass if user got more than half the points on r/w
	 *
	 * @param $grading_details
	 *
	 * @return void
	 */
	public function automatic_grading( $grading_details ) {
		$grading = Grading_Base::factory( $grading_details );
		if ( $this->type !== Main::TYPE_QUIZ ) {
			$passed = true;
			$this->set_grade( $grading->get_passing_grade() )->set_status( $passed );
		} else {
			$quiz              = new TQB_Quiz( (int) $this->quiz_id );
			$reporting_manager = new TQB_Reporting_Manager( $this->quiz_id, 'users' );
			$report            = $reporting_manager->get_report()['data'];
			$report_id         = array_search( $this->value, array_column( $report, 'id' ) );
			$user_report       = $report[ $report_id ];
			$grade             = $user_report->points;
			if ( $quiz->get_type() === 'survey' ) {
				$grade = $grading::PASSING_GRADE;
			} elseif ( $quiz->get_type() === 'right_wrong' ) {
				$result = explode( '/', $grade )[0];
				$total  = explode( '/', $grade )[1];
				$grade  = $result / $total >= 0.5 ? $grading::PASSING_GRADE : $grading::FAILING_GRADE;
			} elseif ( $quiz->get_type() === 'personality' ) {
				$grades   = $grading::get_assessment_grading_details( $this->post_parent )['grading_method_data'];
				$grades   = array_merge( $grades['pass'], $grades['fail'] );
				$grade_id = array_search( $grade, array_column( $grades, 'name' ) );
				$grade    = $grades[ $grade_id ]['ID'];
			}

			$passed = $grading->passed( $grade );
			$this->set_grade( $grade )->set_status( $passed );
		}

		/**
		 * A hook is triggered when a student submits an assessment and automatic grading is enabled, based on the assessment type/quiz result
		 *
		 * @param TVA_User_Assessment $this
		 */
		$passed ? do_action( 'tva_assessment_passed', $this ) : do_action( 'tva_assessment_failed', $this );
	}
}
