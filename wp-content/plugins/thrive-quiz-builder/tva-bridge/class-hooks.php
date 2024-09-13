<?php

namespace TQB\TVA;

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-quiz-builder
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Hooks class
 */
class Hooks {
	/**
	 * Class constructor
	 */
	public function __construct() {
		$this->filters();
	}

	public function filters() {
		add_filter( 'tva_can_be_marked_as_completed', array( $this, 'allow_marked_as_completed' ), 10, 3 );

		add_filter( 'tcb_lazy_load_data', array( $this, 'lazy_load_data' ), 10, 3 );

		/**
		 * Called from ThriveApprentice plugin to get all quizzes
		 */
		add_filter( 'tva_tqb_get_quizzes', static function () {
			return \TQB_Quiz::get_items_for_apprentice_integration();
		} );

		add_filter( 'tva_tqb_drip_valid_result_trigger', array( $this, 'drip_valid_result_trigger' ), 10, 2 );
	}


	/**
	 * @param boolean $is_valid
	 * @param array   $config
	 *
	 * @return bool
	 */
	public function drip_valid_result_trigger( $is_valid = true, $config = array() ) {
		$config = array( 'tqb' => array( $config ) );

		return $this->allow_marked_as_completed( $is_valid, $config, 0 );
	}

	/**
	 * @param boolean $allow
	 * @param array   $config
	 * @param integer $post_id
	 *
	 * @return bool
	 */
	public function allow_marked_as_completed( $allow = true, $config = array(), $post_id = 0 ) {

		if ( $allow && is_array( $config ) && ! empty( $config['tqb'] ) && is_array( $config['tqb'] ) ) {

			foreach ( $config['tqb'] as $dependency_params ) {
				$params = array_merge( array(
					'quiz_id' => 0, //Always INT
					'when'    => '', //can be based_on_result or quiz_complete
					'cond'    => '', //can be string or array
				), $dependency_params );

				if ( empty( $params['quiz_id'] ) || empty( $params['when'] ) || ! in_array( $params['when'], array( 'based_on_result', 'quiz_complete' ) ) ) {
					continue;
				}

				$customer = ! empty( $params['user_id'] ) ? new \TQB_Customer( (int) $params['user_id'] ) : tqb_customer();
				$quiz     = new \TQB_Quiz( (int) $params['quiz_id'] );
				/**
				 * We need to be sure that the quiz hasn't been deleted and the post is actually a quiz post
				 */
				if ( $quiz->get_post() instanceof \WP_Post && $quiz->get_post()->post_type === \TQB_Post_types::QUIZ_POST_TYPE ) {
					$is_completed = $customer->is_quiz_completed( $quiz, $post_id );

					if ( ! $is_completed ) {
						$allow = false;
					} elseif ( $params['when'] === 'based_on_result' && ! empty( $params['cond'] ) ) {

						$quiz_results = $quiz->get_results();
						/**
						 * Particular cases check:
						 */
						if ( $quiz->get_type() === \Thrive_Quiz_Builder::QUIZ_TYPE_PERSONALITY ) {
							/**
							 * - The answers that are store in TAR are different from the ones that are stored inside quiz builder
							 */
							$ids = array_map( static function ( $data ) {
								return $data['id'];
							}, $quiz_results );

							if ( ! empty( array_diff( $params['cond'], $ids ) ) ) {
								$allow = true; //If they are different we allow only based on complete
								continue;
							}
						} else if ( in_array( $quiz->get_type(), array( \Thrive_Quiz_Builder::QUIZ_TYPE_NUMBER, \Thrive_Quiz_Builder::QUIZ_TYPE_RIGHT_WRONG ), true ) ) {
							/**
							 * The passing points that is stored in the quiz config - TAR needs to be between min and max values from the quiz points
							 *
							 * QUIZ_MIN_POINT <= passing_point <= QUIZ_MAX_POINT
							 */
							$passing_point = (int) $params['cond'];

							if ( $passing_point < $quiz_results['min'] || $passing_point > $quiz_results['max'] ) {
								$allow = true; // If the passing point is not between min and max, this means that the quiz has been altered from the backend and we allow based on complete
								continue;
							}
						}

						$allow = $allow && $customer->is_quiz_passed( $quiz, $params['cond'], $post_id );
					}
				}

				if ( $allow === false ) {
					/**
					 * If $allow is false, break the iteration
					 */
					break;
				}
			}
		}

		return $allow;
	}

	/**
	 * @param array            $data
	 * @param int              $post_id
	 * @param \TCB_Editor_Ajax $context
	 *
	 * @return array
	 */
	public function lazy_load_data( $data, $post_id, $context ) {

		if ( class_exists( 'TVA_Const', false ) && get_post_type( $post_id ) === \TVA_Const::LESSON_POST_TYPE ) {
			$data['tqb_quizzes'] = \TQB_Quiz::get_items_for_apprentice_integration();
		}

		return $data;
	}
}

return new Hooks();
