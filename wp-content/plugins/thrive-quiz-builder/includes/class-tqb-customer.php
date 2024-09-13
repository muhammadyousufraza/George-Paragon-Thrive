<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-quiz-builder
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TQB Customer
 *
 * User based data and function for the active tqb customer
 */
class TQB_Customer {
	/**
	 * Cache for completed quiz data on request
	 *
	 * @var array
	 */
	public static $COMPLETED_QUIZ_DATA_CACHE = array();

	/**
	 * Cache for last user try
	 *
	 * @var array
	 */
	public static $LAST_USER_TRY_CACHE = array();

	/**
	 * @var WP_User
	 */
	protected $user;

	/**
	 * @var string
	 */
	private $user_random_identifier;

	/**
	 * @var TQB_Database
	 */
	public $tqbdb;

	/**
	 * @param int|WP_User|null $data
	 */
	public function __construct( $data ) {
		if ( is_numeric( $data ) ) {
			$this->user = new WP_User( (int) $data );
		} elseif ( $data instanceof WP_User ) {
			$this->user = $data;
		} else {
			$this->user = new WP_User( get_current_user_id() );
		}

		global $tqbdb;

		$this->tqbdb = $tqbdb;
	}

	/**
	 * @return int
	 */
	public function get_id() {
		return $this->user->ID;
	}

	/**
	 * @return string
	 */
	public function get_email() {
		return $this->user->user_email;
	}

	/**
	 * @param string|null $user_random_identifier
	 *
	 * @return void
	 */
	public function set_random_identifier( $user_random_identifier ) {
		$this->user_random_identifier = $user_random_identifier;
	}

	/**
	 * @return string|null
	 */
	public function get_random_identifier() {
		return $this->user_random_identifier;
	}

	/**
	 * @return string
	 */
	public function get_display_name() {
		$mail_sign_pos = strpos( $this->user->display_name, '@' );

		if ( $mail_sign_pos !== false ) {
			return substr( $this->user->display_name, 0, $mail_sign_pos );
		}

		return $this->user->display_name;
	}

	/**
	 * @param int $quiz_id
	 *
	 * @return array
	 */
	public function get_completed_quiz_data( $quiz_id ) {
		$args = array(
			'quiz_id'        => $quiz_id,
			'completed_quiz' => 1,
		);

		if ( is_user_logged_in() ) {
			$args['wp_user_id'] = $this->get_id();
		} else {
			$args['random_identifier'] = empty( $this->get_random_identifier() ) ? 'tqb-user-unknown' : $this->get_random_identifier();
		}

		$key = json_encode( $args );

		if ( isset( static::$COMPLETED_QUIZ_DATA_CACHE[ $key ] ) ) {
			return static::$COMPLETED_QUIZ_DATA_CACHE[ $key ];
		}

		static::$COMPLETED_QUIZ_DATA_CACHE[ $key ] = $this->tqbdb->get_users( $args );

		return static::$COMPLETED_QUIZ_DATA_CACHE[ $key ];
	}

	/**
	 * @param int $quiz_id
	 * @param int $post_id
	 *
	 * @return array
	 */
	private function get_completed_quiz_data_on_post( $quiz_id, $post_id ) {
		return array_filter( $this->get_completed_quiz_data( $quiz_id ), static function ( $data ) use ( $post_id ) {
			return (int) $data['object_id'] === (int) $post_id;
		} );
	}

	/**
	 * Returns true if the quiz has been completed by the active customer at least once
	 *
	 * @param TQB_Quiz $quiz
	 *
	 * @return bool
	 */
	public function is_quiz_completed( $quiz, $post_id = 0 ) {
		$completed_quiz_data = $this->get_completed_quiz_data( $quiz->get_id() );

		$is_completed = is_array( $completed_quiz_data ) && count( $completed_quiz_data ) > 0;

		if ( ! empty( $post_id ) && $is_completed ) {
			$is_completed = $this->is_quiz_completed_on_post( $quiz, $post_id );
		}

		return $is_completed;
	}

	/**
	 * Returns true if a specific quiz has been completed on a specific post at least once
	 *
	 * @param $quiz
	 * @param $post_id
	 *
	 * @return bool
	 */
	private function is_quiz_completed_on_post( $quiz, $post_id ) {
		$completed_quiz_data = $this->get_completed_quiz_data_on_post( $quiz->get_id(), $post_id );

		return is_array( $completed_quiz_data ) && count( $completed_quiz_data ) > 0;
	}

	/**
	 * Returns true if the quiz has been passed
	 * The quiz is considered passed if the active user achieved a results that satify the $score_offset parameter
	 *
	 * For numeric, percentage and right/wrong quiz the result must be <= score_offset
	 * For personality quiz the result must be in array of the score offset
	 *
	 * @param TQB_Quiz      $quiz
	 * @param array|numeric $score_offset
	 * @param integer       $post_id
	 *
	 * @return bool
	 */
	public function is_quiz_passed( $quiz, $score_offset, $post_id = 0 ) {
		$passed = false;

		if ( $this->is_quiz_completed( $quiz, $post_id ) ) {
			$quiz_type = $quiz->get_type();
			switch ( $quiz_type ) {
				case Thrive_Quiz_Builder::QUIZ_TYPE_SURVEY:
					$passed = true;
					break;
				case Thrive_Quiz_Builder::QUIZ_TYPE_PERSONALITY:
					$points     = $this->get_points( $quiz->get_id(), $post_id );
					$points_ids = array_filter( array_map( static function ( $data ) use ( $points ) {
						if ( in_array( $data['text'], $points, true ) ) {
							return (int) $data['id'];
						}
					}, $quiz->get_results() ) );

					$passed = ! empty( array_intersect( $score_offset, $points_ids ) );
					break;
				case Thrive_Quiz_Builder::QUIZ_TYPE_NUMBER:
				case Thrive_Quiz_Builder::QUIZ_TYPE_PERCENTAGE:
				case Thrive_Quiz_Builder::QUIZ_TYPE_RIGHT_WRONG:

					$max_number_of_points = $this->get_max_points_from_quiz_data( $quiz->get_id(), $post_id );
					if ( (int) $score_offset <= $max_number_of_points ) {
						$passed = true;
					}
					break;
				default:
					break;
			}
		}

		return $passed;
	}

	/**
	 * Returns the points that the customer have achieved from a specific quiz
	 *
	 * @param int $quiz_id
	 * @param int $post_id
	 *
	 * @return array
	 */
	private function get_points( $quiz_id, $post_id = 0 ) {
		return array_map( static function ( $data ) {
			return $data['points'];
		}, empty( $post_id ) ? $this->get_completed_quiz_data( $quiz_id ) : $this->get_completed_quiz_data_on_post( $quiz_id, $post_id ) );
	}

	/**
	 * This only works for number, percentage and right/wrong quiz type
	 * Returns the max number of points the user achieved
	 *
	 * @param int $quiz_id
	 * @param int $post_id
	 *
	 * @return int
	 */
	private function get_max_points_from_quiz_data( $quiz_id, $post_id = 0 ) {
		$points = array_map( static function ( $point ) {
			/**
			 * We need to cast to int for percentage quiz type -> to remove the % from the result and to cast to int
			 *
			 * Result can be 49.49% -> should be transformed into 49
			 */
			return (int) $point;
		}, $this->get_points( $quiz_id, $post_id ) );

		return max( $points );
	}

	/**
	 * Returns the last user try
	 *
	 * @param int $quiz_id
	 * @param int $post_id
	 *
	 * @return false|mixed
	 */
	private function get_last_user_try( $quiz_id, $post_id ) {
		$key = "{$quiz_id}_{$post_id}";

		if ( isset( static::$LAST_USER_TRY_CACHE[ $key ] ) ) {
			return static::$LAST_USER_TRY_CACHE[ $key ];
		}

		$results = $this->tqbdb->get_users( [
			'quiz_id'         => $quiz_id,
			'wp_user_id'      => $this->get_id(),
			'object_id'       => $post_id,
			'limit'           => 1,
			'order_by'        => 'id',
			'order_direction' => 'DESC',
		] );


		if ( empty( $results ) || ! is_array( $results ) ) {
			static::$LAST_USER_TRY_CACHE[ $key ] = false;
		} else {
			static::$LAST_USER_TRY_CACHE[ $key ] = reset( $results );
		}

		return static::$LAST_USER_TRY_CACHE[ $key ];
	}

	/**
	 * @param int $quiz_id
	 * @param int $post_id
	 *
	 * @return false|mixed
	 */
	public function get_user_unique_id( $quiz_id, $post_id ) {
		$user_quiz_try = $this->get_last_user_try( $quiz_id, $post_id );

		return $user_quiz_try ? $user_quiz_try['random_identifier'] : false;
	}

	/**
	 * @param int $quiz_id
	 * @param int $post_id
	 *
	 * @return int|null
	 */
	public function get_resume_quiz_last_answer_id( $quiz_id, $post_id ) {
		$user_quiz_try  = $this->get_last_user_try( $quiz_id, $post_id );
		$last_answer_id = null;

		if ( $user_quiz_try ) {
			$user_answers = TQB_Quiz_Manager::get_user_answers_in_order( $quiz_id, $user_quiz_try['id'] );
			$last_answer  = $user_answers[ array_key_last( $user_answers ) ];

			if ( ! empty( $last_answer ) ) {
				$last_answer_id = (int) $last_answer['answer_id'];
			}
		}

		return $last_answer_id;
	}

	/**
	 * Returns the existing user answers for the resume quiz functionality
	 * Used to localize the existing quiz flow -> in front-end in case the progress bar is enabled for the quiz
	 *
	 * @param int $quiz_id
	 * @param int $post_id
	 *
	 * @return array|false|null
	 */
	public function get_resume_quiz_user_answers( $quiz_id, $post_id ) {
		$user_quiz_try = $this->get_last_user_try( $quiz_id, $post_id );
		$return        = [];

		if ( $user_quiz_try ) {
			$return = TQB_Quiz_Manager::get_user_answers_in_order( $quiz_id, $user_quiz_try['id'] );
		}

		return $return;
	}

	/**
	 * Returns the completed quizzes for the active user
	 *
	 * @return array
	 */
	public function get_user_completed_quizzes() {
		$value = get_user_meta( $this->get_id(), 'tqb_quiz_completed_triggered', true );

		if ( empty( $value ) ) {
			$value = array();
		}

		return $value;
	}


	/**
	 * Updates completed quizzes meta
	 *
	 * @param int $quiz_id
	 * @param int $post_id
	 * @param int $meta_value
	 *
	 * @return bool|int
	 */
	public function update_user_completed_quizzes( $quiz_id, $post_id, $meta_value = 1 ) {
		if ( empty( $post_id ) ) {
			return false;
		}

		$value = $this->get_user_completed_quizzes();

		if ( empty( $value[ $quiz_id ] ) ) {
			$value[ $quiz_id ] = array();
		}

		$value[ $quiz_id ][ $post_id ] = $meta_value;

		return update_user_meta( $this->get_id(), 'tqb_quiz_completed_triggered', $value );
	}

	/**
	 * Decide what page should the customer see when resuming a quiz
	 *
	 * @param int $quiz_id
	 * @param int $post_id
	 *
	 * @return string|null
	 */
	public function get_resume_quiz_page( $quiz_id, $post_id ) {
		$page = null;

		$user_quiz_try = $this->get_last_user_try( $quiz_id, $post_id );

		if ( $user_quiz_try ) {
			$last_answer = $this->get_resume_quiz_last_answer_id( $quiz_id, $post_id );

			if ( $last_answer ) {
				$page = 'splash';

				if ( (int) $user_quiz_try['completed_quiz'] === 1 ) {
					$page = 'optin';
					if ( ! TQB_Product::has_access() ) {
						//If the user is not an admin, we need to check if there is a conversion made for the optin
						$quiz = new TQB_Quiz( $quiz_id );

						/**
						 * If the optin page is enabled and there is no conversion for the optin page,
						 * on quiz resume we show the optin page
						 */
						if ( $quiz->optin_gate_is_enabled() ) {
							$log = $this->tqbdb->get_log_by_filters( array(
								'user_unique' => $user_quiz_try['random_identifier'],
								'page_id'     => $quiz->get_optin_gate_id(),
								'limit'       => 1,
								'event_type'  => array(
									Thrive_Quiz_Builder::TQB_CONVERSION,
									Thrive_Quiz_Builder::TQB_SKIP_OPTIN,
								),
							) );

							if ( empty( $log ) ) {
								$page = 'qna';
							}
						}
					}
				}
			}
		}

		return $page;
	}


}

/**
 * Returns an instance of TQB customer of the logged in user
 *
 * @return TQB_Customer
 */
function tqb_customer() {
	global $tqb_customer;

	/**
	 * if we have a customer then return it
	 */
	if ( $tqb_customer instanceof TQB_Customer ) {
		return $tqb_customer;
	}

	/**
	 * After WP is fully loaded
	 */
	add_action(
		'wp_loaded',
		function () {
			global $tqb_customer;

			$tqb_customer = new TQB_Customer( get_current_user_id() );
		}
	);

	return $tqb_customer;
}

tqb_customer();
