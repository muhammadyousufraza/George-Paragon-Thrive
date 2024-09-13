<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

/**
 * Class TQB_Quiz_Manager
 *
 * Handles Quiz operations
 */
class TQB_Quiz_Manager {

	/**
	 * @var WP_Post $instance
	 */
	protected $quiz;

	/**
	 * @var TQB_Database
	 */
	protected $tqbdb;

	/**
	 * TQB_Quiz_Manager constructor.
	 *
	 * @param int|null $quiz_id
	 */
	public function __construct( $quiz_id = null ) {
		global $tqbdb;
		$this->tqbdb = $tqbdb;
		$this->quiz  = get_post( $quiz_id );
	}


	/**
	 * Get the list of quizzes based on filters param
	 *
	 * @param array   $filters allows passing query values to the get_posts function, and some extra values.
	 * @param boolean $include_details
	 *
	 * @return array $posts
	 */
	public static function get_quizzes( $filters = array(), $include_details = true ) {

		$defaults = array(
			'posts_per_page' => - 1,
			'post_type'      => TQB_Post_types::QUIZ_POST_TYPE,
			'orderby'        => 'post_date',
			'order'          => 'ASC',
		);
		$filters  = array_merge( $defaults, $filters );
		$posts    = get_posts( $filters );

		if ( $include_details ) {
			foreach ( $posts as $index => $post ) {
				$post            = TQB_Quiz_Manager::get_quiz_post_details( $post );
				$posts[ $index ] = $post;
			}
		}

		return $posts;

	}

	/**
	 * Get quizzes that a string in title
	 *
	 * @param string $search_word
	 *
	 * @return array
	 */
	public static function get_searched_quizzes( $search_word = '' ) {
		$defaults        = array(
			'posts_per_page' => - 1,
			'post_type'      => TQB_Post_types::QUIZ_POST_TYPE,
			'orderby'        => 'post_date',
			'order'          => 'ASC',
		);
		$posts           = get_posts( $defaults );
		$matched_quizzes = array();
		foreach ( $posts as $index => $post ) {
			$post            = TQB_Quiz_Manager::get_quiz_post_details( $post );
			$posts[ $index ] = $post;
			if ( stripos( $post->post_title, $search_word ) !== false ) {
				$matched_quizzes[] = TQB_Quiz_Manager::get_quiz_post_details( $post );
			}
		}

		return $matched_quizzes;
	}

	/**
	 * Get specific quizzes
	 *
	 * @param array $quizzes_id
	 *
	 * @return array
	 */
	public static function get_specific_quizzes( $quizzes_id = array() ) {
		$posts = array();

		foreach ( $quizzes_id as $quiz_id ) {
			$post    = get_post( $quiz_id );
			$posts[] = TQB_Quiz_Manager::get_quiz_post_details( $post );
		}

		return $posts;

	}

	/**
	 * @param WP_Post $post
	 *
	 * @return mixed
	 */
	public static function get_quiz_post_details( $post ) {
		$post->order           = (int) TQB_Post_meta::get_quiz_order( $post->ID );
		$post_type_meta        = TQB_Post_meta::get_quiz_type_meta( $post->ID );
		$post->type            = isset( $post_type_meta['type'] ) ? $post_type_meta['type'] : '';
		$post->completed_count = TQB_Quiz_Manager::get_completed_quiz_count( $post->ID );
		$post->users_started   = TQB_Quiz_Manager::get_quiz_users_count( $post->ID );

		$post->social_shares = TQB_Quiz_Manager::get_quiz_social_shares_count( $post->ID );
		$structure           = new TQB_Structure_Manager( $post->ID );
		$post->validation    = $structure->get_display_availability();
		$post->is_valid      = $post->validation['valid'];

		$structure_data    = $structure->get_quiz_structure_meta();
		$post->subscribers = TQB_Quiz_Manager::get_quiz_subscribers( $structure_data );

		$quiz          = new TQB_Quiz( $post->ID );
		$post->results = $quiz->get_results();

		return $post;
	}

	/**
	 * Getting number of user's quizzes
	 *
	 * @return int
	 */
	public static function get_quizzes_number() {
		return count( get_posts( array( 'post_type' => TQB_Post_types::QUIZ_POST_TYPE, 'posts_per_page' => '-1' ) ) );
	}

	public static function get_page_subscribers( $id ) {
		global $tqbdb;

		return $tqbdb->get_page_subscribers( $id );
	}

	/**
	 * Get quiz subscribers count
	 */
	public static function get_quiz_subscribers( $structure ) {

		if ( empty( $structure ) ) {
			return 0;
		}
		$optin_subscribers   = 0;
		$results_subscribers = 0;
		if ( is_numeric( $structure['optin'] ) ) {
			$optin_subscribers = TQB_Quiz_Manager::get_page_subscribers( $structure['optin'] );
		}

		if ( is_numeric( $structure['results'] ) ) {
			$results_subscribers = TQB_Quiz_Manager::get_page_subscribers( $structure['results'] );
		}

		return $results_subscribers + $optin_subscribers;
	}

	/**
	 * Gets only the valid quizzes
	 *
	 * @param array $filters
	 *
	 * @return array
	 */
	public static function get_valid_quizzes( $filters = array() ) {
		$quizzes = self::get_quizzes( $filters );
		foreach ( $quizzes as $key => $quiz ) {
			if ( ! $quiz->validation['valid'] ) {
				unset( $quizzes[ $key ] );
			}
		}

		return $quizzes;
	}

	/**
	 * Get a quiz based on filters
	 *
	 * @return false|WP_Post on success or false on error
	 */
	public function get_quiz() {

		if ( empty( $this->quiz ) || $this->quiz->post_type !== TQB_Post_types::QUIZ_POST_TYPE ) {
			return false;
		}

		$type  = TQB_Post_meta::get_quiz_type_meta( $this->quiz->ID );
		$style = TQB_Post_meta::get_quiz_style_meta( $this->quiz->ID );

		if ( ! empty( $type ) ) {
			$this->quiz->type    = $type['type'];
			$this->quiz->results = $this->get_results();

			$this->quiz->feedback_settings  = TQB_Post_meta::get_feedback_settings_meta( $this->quiz->ID );
			$this->quiz->highlight_settings = TQB_Post_meta::get_highlight_settings_meta( $this->quiz->ID );
			$this->quiz->progress_settings  = tqb_progress_settings_instance( (int) $this->quiz->ID, $style )->get();
			$this->quiz->scroll_settings    = TQB_Post_meta::get_quiz_scroll_settings_meta( $this->quiz->ID );
		}

		$this->quiz->page_variations = $this->tqbdb->count_page_variations( array(
			'quiz_id' => $this->quiz->ID,
		) );

		if ( is_numeric( $style ) ) {
			$this->quiz->style = $style;
		}

		$this->quiz->wizard_complete = TQB_Post_meta::get_wizard_meta( $this->quiz->ID );

		$quiz_structure = new TQB_Structure_Manager( $this->quiz->ID );
		$structure      = $quiz_structure->get_quiz_structure_meta();
		if ( is_array( $structure ) && ! empty( $structure ) ) {

			$this->quiz->structure                  = $structure;
			$pages                                  = tqb()->get_structure_internal_identifiers();
			$this->quiz->structure['running_tests'] = array();

			foreach ( $pages as $page ) {
				if ( is_numeric( $structure[ $page ] ) ) {

					$page_manager = new TQB_Page_Manager( $structure[ $page ] );

					$this->quiz->structure['running_tests'][ $page ] = $page_manager->get_tests_for_page( array(
						'page_id' => $structure[ $page ],
						'status'  => 1,
					), true );

					$this->quiz->structure['nr_of_variations'][ $page ] = $this->tqbdb->count_page_variations( array(
						'quiz_id'     => $this->quiz->ID,
						'post_id'     => $structure[ $page ],
						'post_status' => Thrive_Quiz_Builder::VARIATION_STATUS_PUBLISH,
					) );

					if ( $page === 'results' ) {
						$this->quiz->structure['results_page'] = TQB_Structure_Manager::make_page( (int) $structure[ $page ] )->to_json();
					}
				}
			}
			$this->quiz->structure['tge_question_number'] = tge()->count_questions( $this->quiz->ID );
			$this->quiz->structure['qna_editor_url']      = $this->get_qna_editor_url();
		}

		$tpl = TQB_Post_meta::get_quiz_tpl_meta( $this->quiz->ID );
		if ( ! empty( $tpl ) ) {
			$this->quiz->tpl = $tpl;
		}

		tie()->set_images( $this->quiz );

		$this->quiz->tge_url = tge()->editor_url( $this->quiz );

		return $this->quiz;
	}

	/**
	 * Save a quiz
	 *
	 * @param array $model WP post object.
	 *
	 * @return false|int id of model or false on error
	 */
	public function save_quiz( $model ) {

		if ( ! empty( $model['ID'] ) ) {
			$item = get_post( $model['ID'] );
			if ( $item && get_post_type( $item ) === TQB_Post_types::QUIZ_POST_TYPE ) {
				$data = array(
					'ID'         => $model['ID'],
					'post_title' => $model['post_title'],
				);
				$id   = wp_update_post( $data );
			}
		} else {
			$default = array(
				'post_type'   => TQB_Post_types::QUIZ_POST_TYPE,
				'post_status' => 'publish',
			);

			$id = wp_insert_post( array_merge( $default, $model ) );
			TQB_Post_meta::update_quiz_tpl_meta( $id, $model );
		}

		if ( empty( $id ) || is_wp_error( $id ) ) {
			return false;
		}

		if ( isset( $model['order'] ) ) {
			TQB_Post_meta::update_quiz_order( $id, (int) $model['order'] );
		}

		if ( Thrive_Quiz_Builder::QUIZ_TYPE_SURVEY_TPL_ID === (int) $model['tpl'] ) {

			$quiz_type = TQB_Post_meta::get_quiz_type_meta( $id, true );

			if ( ! $quiz_type ) {
				TQB_Post_meta::update_quiz_type_meta( $id, array( 'type' => Thrive_Quiz_Builder::QUIZ_TYPE_SURVEY ) );
			}
		}

		return $id;
	}

	/**
	 * Set post's status on trash
	 *
	 * @param bool $force_delete whether or not to bypass trash and delete the quiz permanently
	 *
	 * @return false | int number of deleted rows or false on error
	 */
	public function delete_quiz( $force_delete = true ) {

		if ( empty( $this->quiz ) || $this->quiz->post_type !== TQB_Post_types::QUIZ_POST_TYPE ) {
			return false;
		}

		if ( $force_delete ) {
			/*Delete Variations*/
			TQB_Variation_Manager::delete_variation( array( 'quiz_id' => $this->quiz->ID ) );

			/*Deletes the quiz child posts*/
			$this->delete_quiz_pages();

			/*Delete quiz answers and quiz questions from graph editor*/
			tge()->delete_all_quiz_dependencies( $this->quiz->ID );

			/*Deletes quiz results and answers*/
			$this->delete_quiz_results_and_user_answers( $this->quiz->ID );

			$deleted = wp_delete_post( $this->quiz->ID, true );
		} else {
			$this->quiz->post_status = 'trash';
			$deleted                 = wp_update_post( $this->quiz );
		}

		$deleted = ! ( $deleted === 0 || is_wp_error( $deleted ) );

		if ( $deleted && $force_delete ) {
			tie()->delete_images( $this->quiz );
		}

		return $deleted;
	}

	/**
	 * Deletes quiz pages
	 */
	public function delete_quiz_pages() {

		$posts = TQB_Page_Manager::get_quiz_pages( $this->quiz->ID );

		if ( is_array( $posts ) && count( $posts ) > 0 ) {
			TQB_Page_Manager::delete_quiz_pages( $posts );
		}
	}

	/**
	 * run do_shortcode on the whole quiz future content
	 */
	public static function run_shortcodes_on_quiz_content( $quiz_id ) {

		$structure      = new TQB_Structure_Manager( $quiz_id );
		$structure_data = $structure->get_quiz_structure_meta();
		$array          = tqb()->get_structure_internal_identifiers();
		global $tqbdb;
		$all_content = '';
		foreach ( $array as $page_type ) {
			if ( isset( $structure_data[ $page_type ] ) && is_numeric( $structure_data[ $page_type ] ) ) {
				$variations = $tqbdb->get_page_variations( array( 'post_id' => $structure_data[ $page_type ] ) );
				foreach ( $variations as $variation ) {
					if ( ! empty( $variation['content'] ) ) {
						$all_content .= $variation['content'];
					}
					$variation_manager = new TQB_Variation_Manager( $quiz_id, $variation['page_id'] );
					$dynamic_content   = $variation_manager->get_page_variations( array( 'parent_id' => $variation['id'] ) );
					foreach ( $dynamic_content as $child_variation ) {
						if ( ! empty( $child_variation['content'] ) ) {
							$all_content .= $child_variation['content'];
						}
					}
				}
			}
		}
		tve_parse_events( $all_content );
		do_shortcode( $all_content );
	}

	/**
	 * Main decision making regarding shortcode content
	 */
	public static function get_shortcode_content( $quiz_id, $page_type = null, $answers = null, $user_unique = null, $variation = null, $post_id = 0 ) {

		global $tqbdb;

		$quiz = get_post( $quiz_id );
		if ( empty( $quiz ) ) {
			return array( 'error' => tqb_create_frontend_error_message( array( __( 'The shortcode is broken', 'thrive-quiz-builder' ) ) ) );
		}

		$structure  = new TQB_Structure_Manager( $quiz_id );
		$validation = $structure->get_display_availability();
		if ( ! $validation['valid'] ) {
			$errors = tqb_create_frontend_error_message( $validation['error'] );

			return array( 'error' => $errors );
		}
		$post_id = $post_id ? $post_id : get_the_ID();

		/**
		 * The TQB User should be created only for frontend, so not editor page
		 */
		if ( empty( $_REQUEST['tar_editor_page'] ) ) {
			if ( empty( $user_unique ) ) {
				$user_unique = uniqid( 'tqb-user-', true );

				//TA-3621 for now this action is removed
//				/**
//				 * Fired when a user starts a quiz
//				 *
//				 * @param array Quiz Details
//				 * @param array User Details
//				 *
//				 * @api
//				 */
//				do_action( 'thrive_quizbuilder_quiz_started', TQB_Quiz_Manager::get_quiz_details( $quiz_id, $user_unique ), tvd_get_current_user_details() );

			} else {
				$user_id = TQB_Quiz_Manager::get_quiz_user( $user_unique, $quiz_id, false, array(
					'object_id' => $post_id,
				) );
			}
		}
		$current_user_id          = get_current_user_id();
		$quiz_completed_triggered = $current_user_id ? get_user_meta( $current_user_id, 'tqb_quiz_completed_triggered', true ) : array();

		/**
		 * Store the quiz user unique to the customer class to make queries on it in case the user is not logged in
		 */
		tqb_customer()->set_random_identifier( $user_unique );

		$shortcode_content['page']        = false;
		$shortcode_content['question']    = null;
		$shortcode_content['user_unique'] = $user_unique;
		$shortcode_content['user_id']     = ( ! empty( $user_id ) ) ? $user_id : null;
		switch ( $page_type ) {

			case null:
				$shortcode_content['page'] = $structure->get_page_content( 'splash', null, $post_id );

				if ( ! empty( $shortcode_content['page'] ) ) {
					$shortcode_content['page_type'] = 'splash';
					do_action( 'tqb_register_impression', $shortcode_content['page'], $user_unique );
					break;
				}

			case 'splash':
				$question_manager = new TGE_Question_Manager( $quiz_id );
				$answer_text      = sanitize_textarea_field( ! empty( $_GET['answer_text'] ) ? $_GET['answer_text'] : null );
				// register the answer
				if ( ! empty( $answers ) ) {

					foreach ( $answers as $answer_id ) {
						TQB_Quiz_Manager::register_answer( $answer_id, $user_unique, $quiz_id, $answer_text );
					}
				}

				/**
				 * Computes the answer for the next question. Needed to register the impression for the next question
				 * Handled the special case for question with multiple answers.
				 */
				if ( ! is_array( $answers ) ) {
					$question_answer = $answers;
				} else {
					$question_answer = empty( $answers ) ? null : reset( $answers );
				}
				$shortcode_content['question'] = $question_manager->get_question_content( $question_answer );
				if ( ! empty( $shortcode_content['question'] ) ) {
					if ( ! empty( $shortcode_content['question']['data']['id'] ) ) {
						$question_manager->register_question_view( $shortcode_content['question']['data']['id'] );
					}
					$shortcode_content['page_type']           = 'splash';
					$shortcode_content['question']['page_id'] = $quiz_id;
					$shortcode_content['question']['quiz_id'] = $quiz_id;

					if ( isset( $variation['id'] ) && $variation['id'] ) {
						$variation['quiz_id'] = $quiz_id;
						do_action( 'tqb_register_conversion', $variation, $user_unique );
					}

					if ( is_user_logged_in() ) {
						$quiz_completed_triggered                         = is_array( $quiz_completed_triggered ) ? $quiz_completed_triggered : array();
						$quiz_completed_triggered[ $quiz_id ][ $post_id ] = false;
						update_user_meta( $current_user_id, 'tqb_quiz_completed_triggered', $quiz_completed_triggered );
					}

					do_action( 'tqb_register_impression', $shortcode_content['question'], $user_unique );

					break;
				} else {
					$shortcode_content['page_type'] = 'qna';
				}

			case 'qna':
				$points                    = TQB_Quiz_Manager::save_user_points( $user_unique, $quiz_id );
				$shortcode_content['page'] = $structure->get_page_content( 'optin', $points, $post_id );
				do_action( 'tqb_register_conversion', array( 'quiz_id' => $quiz_id, 'page_id' => $quiz_id, 'id' => null ), $user_unique );
				if ( ! empty( $shortcode_content['page'] ) && $structure->should_show_optin_gate_page() ) {
					TQB_Quiz_Manager::tqb_register_quiz_completion( $user_unique, $shortcode_content['page']['page_id'] );
					$shortcode_content['page_type'] = 'optin';
					do_action( 'tqb_register_impression', $shortcode_content['page'], $user_unique );
					break;
				}
			case 'optin':
				$points                      = TQB_Quiz_Manager::calculate_user_points( $user_unique, $quiz_id );
				$points['explicit']          = $tqbdb->get_explicit_result( $points );
				$shortcode_content['points'] = $points;
				$shortcode_content['page']   = $structure->get_page_content( 'results', $points, $post_id );
				do_action( 'tqb_register_impression', $shortcode_content['page'], $user_unique );

				$quiz_completed_triggered = is_array( $quiz_completed_triggered ) ? $quiz_completed_triggered : array();
				$do_action                = empty( $quiz_completed_triggered ) || empty ( $quiz_completed_triggered[ $quiz_id ][ $post_id ] );

				TQB_Quiz_Manager::tqb_register_quiz_completion( $user_unique, $shortcode_content['page']['page_id'], $do_action );
				if ( isset( $variation['id'] ) && $variation['id'] ) {
					$variation['quiz_id'] = $quiz_id;
					do_action( 'tqb_register_skip_optin', $variation, $user_unique );

				}
				$variation_arr                                 = TQB_Variation_Manager::get_variation( $shortcode_content['page']['variation_id'] );
				$shortcode_content['page']['has_social_badge'] = ( isset( $variation_arr['tcb_fields']['social_share_badge'] ) ) ? $variation_arr['tcb_fields']['social_share_badge'] : 0; // can be 0 or 1
				$shortcode_content['page_type']                = 'results';

				if ( $shortcode_content['page']['has_social_badge'] ) {
					$result                                         = $tqbdb->get_explicit_result( $points );
					$shortcode_content['page']['result']            = str_replace( '%', '', $result );
					$shortcode_content['page']['social_loader_url'] = tqb()->plugin_url( 'assets/images/social-sharing-badge-loader.gif' );

//					$badge     = new TQB_Badge( $result, $quiz_id );
//					$badge_url = $badge->get_url();
					if ( ! empty( $badge_url ) ) {
						$shortcode_content['page']['badge_url'] = $badge_url;
					} else {

						$image_post = get_posts( array( 'post_parent' => $quiz_id, 'post_type' => TIE_Post_Types::THRIVE_IMAGE ) );
						if ( ! empty( $image_post[0] ) ) {
							$tie_image                            = new TIE_Image( $image_post[0] );
							$shortcode_content['page']['fonts']   = array_merge( $shortcode_content['page']['fonts'], array_values( $tie_image->get_settings()->get_data( 'fonts' ) ) );
							$shortcode_content['page']['fonts'][] = '//fonts.googleapis.com/css?family=Roboto'; //default font for BE

							$html = TQB_Page_Manager::insert_result_shortcodes(
								array(
									'content' => $tie_image->get_html_canvas_content(),
									'quiz_id' => $quiz_id,
								),
								$points
							);

							$shortcode_content['page']['html_canvas'] = do_shortcode( $html );
							$shortcode_content['page']['html_canvas'] = str_replace( Thrive_Quiz_Builder::QUIZ_RESULT_SHORTCODE, $result, $shortcode_content['page']['html_canvas'] ); //old implementation
							$shortcode_content['page']['html_canvas'] .= get_post_meta( $quiz_id, 'tqb_quiz_badge_css', true );
						}
					}
				}

				$user_data = tvd_get_current_user_details();
				$form_data = empty( $_REQUEST['form_data'] ) ? null : $_REQUEST['form_data'];


				if ( $form_data ) {
					$form_data = tve_sanitize_data_recursive( $form_data, 'sanitize_textarea_field' );
				}

				$email = '';
				if ( ! empty( $form_data['email'] ) ) {
					$email = $form_data['email'];
				}

				if ( ! empty( $email ) && ! is_user_logged_in() ) {
					$matched_user = get_user_by( 'email', $email );
					if ( ! empty( $matched_user ) ) {
						$user_data = tvd_get_current_user_details( $matched_user->ID );
					}
				}

				if ( empty( $email ) && ! empty( $user_data['email'] ) && is_user_logged_in() ) {
					$email = $user_data['email'];
				}


				if ( $do_action ) {

					if ( is_user_logged_in() ) {
						tqb_customer()->update_user_completed_quizzes( $quiz_id, $post_id );
					}
					/**
					 * The hook is triggered when a quiz result is loaded. The hook can be fired multiple times, if the user completes the same quiz multiple times,
					 * but it won't trigger on page refresh without completing it again
					 * </br>
					 * Example use case:-  Send an email based on the quiz result.  Start the quiz result to a CRM / Autoresponder.  Start an evergreen campaign based on the quiz result.
					 *
					 * @param array Quiz Details
					 * @param array User Details
					 * @param array Lead Data
					 *
					 * @api
					 */
					do_action( 'thrive_quizbuilder_quiz_completed', TQB_Quiz_Manager::get_quiz_details( $quiz_id, $user_unique, $points['explicit'], $email ), $user_data, $form_data, $post_id );
				}
				break;
		}
		if ( ! empty( $validation['error'] ) ) {
			$shortcode_content['error'] = tqb_create_frontend_error_message( array( __( 'There is an error in the quiz structure', 'thrive-quiz-builder' ) ) );
		}

		return $shortcode_content;
	}

	/**
	 * Register quiz question answer
	 *
	 * @param int    $answer_id
	 * @param string $user_unique
	 * @param int    $quiz_id
	 * @param string $answer_text text provided by user for Open Ended Questions
	 *
	 * @return bool
	 */
	public static function register_answer( $answer_id, $user_unique, $quiz_id, $answer_text = '' ) {

		/** @var TGE_Database $tgedb */
		global $tgedb;

		// get answer check if valid
		$answer = $tgedb->get_answers( array( 'id' => $answer_id ), true );

		if ( empty( $answer ) ) {
			return false;
		}

		// get user check if existing
		global $tqbdb;
		$user = $tqbdb->get_quiz_user( $user_unique, $quiz_id );

		if ( empty( $user ) ) {
			return false;
		}

		$user_answer = array(
			'quiz_id'     => $user['quiz_id'],
			'user_id'     => $user['id'],
			'answer_id'   => $answer['id'],
			'question_id' => $answer['question_id'],
		);

		//Check if the answer is already registered
		//Used when a quiz is resumed
		$answer_exists = $tqbdb->get_user_answers( array_merge( $user_answer, array( 'limit' => 1 ) ) );
		if ( $answer_exists ) {
			return false;
		}

		if ( false === empty( $answer_text ) ) {
			$user_answer['answer_text'] = $answer_text;
		}

		$question_manager = new TGE_Question_Manager( $user['quiz_id'] );
		$question         = $question_manager->get_quiz_questions( array( 'id' => $answer['question_id'] ), true );

		/**
		 * The hook is triggered when a user submits the answer to a question of the quiz. It can be fired multiple times, if the user completes the same quiz multiple times.
		 * </br>
		 * Example use case:- Send the answer selected to your analytics platform
		 *
		 * @param array Quiz Details
		 * @param array Question Details
		 * @param array User Details
		 *
		 * @api
		 */
		do_action( 'thrive_quizbuilder_answer_submitted', TQB_Quiz_Manager::get_quiz_details( $quiz_id, $user_unique ), array(
			'quiz_id'            => $answer['quiz_id'],
			'question_id'        => $answer['question_id'],
			'question_answer'    => empty( $answer_text ) ? $answer['text'] : $answer_text,
			'question_answer_id' => $answer['id'],
			'question_type'      => TGE_Question_Manager::get_question_type_name( (int) $question['q_type'] ),
		), tvd_get_current_user_details() );

		$tqbdb->save_user_answer( $user_answer );

		return true;
	}

	/**
	 * Register a page impression
	 */
	public static function tqb_register_impression( $variation, $user_unique ) {
		if ( static::prevent_access() ) {
			return;
		}

		if ( isset( $_COOKIE[ 'tqb-impression-' . $variation['page_id'] . '-' . str_replace( '.', '_', $user_unique ) ] ) ) {
			return;
		}

		if ( isset( $_COOKIE[ 'tqb-impression-' . $variation['page_id'] ] ) ) {
			$data['duplicate'] = 1;
		}

		global $tqbdb;

		$data['date']         = date( 'Y-m-d H:i:s', time() );
		$data['event_type']   = Thrive_Quiz_Builder::TQB_IMPRESSION;
		$data['variation_id'] = isset( $variation['variation_id'] ) ? $variation['variation_id'] : null;
		$data['user_unique']  = $user_unique;
		$data['page_id']      = $variation['page_id'];
		$data['post_id']      = get_the_ID();

		$page_manager = new TQB_Page_Manager( $variation['page_id'] );
		$active_test  = $page_manager->get_tests_for_page( array(
			'page_id' => $variation['page_id'],
			'status'  => 1,
		), true );

		if ( isset( $variation['variation_id'] ) && ! isset( $data['duplicate'] ) ) {
			$update_data = array( 'variation_id' => $variation['variation_id'], 'impression' => true );
			$tqbdb->update_variation_cached_counter( $update_data );
			if ( ! empty( $active_test ) ) {
				$update_data['test_id'] = $active_test['id'];
				$tqbdb->update_test_item_action_counter( $update_data );

				/*Check for test auto win*/
				$test_manager = new TQB_Test_Manager( $active_test['id'] );
				$test_manager->check_test_auto_win();
				$test_manager->stop_underperforming_variations();
			}
		}

		$tqbdb->create_event_log_entry( $data );

		setcookie( 'tqb-impression-' . $variation['page_id'], 1, time() + ( 30 * 24 * 3600 ), '/' );
		$_COOKIE[ 'tqb-impression-' . $variation['page_id'] ] = true;

		setcookie( 'tqb-impression-' . $variation['page_id'] . '-' . str_replace( '.', '_', $user_unique ), 1, time() + ( 30 * 24 * 3600 ), '/' );
		$_COOKIE[ 'tqb-impression-' . $variation['page_id'] . '-' . str_replace( '.', '_', $user_unique ) ] = true;
	}

	/**
	 * Register a page conversion
	 */
	public static function tqb_register_conversion( $variation, $user_unique ) {
		if ( static::prevent_access() ) {
			return;
		}

		if ( isset( $_COOKIE[ 'tqb-conversion-' . $variation['page_id'] . '-' . str_replace( '.', '_', $user_unique ) ] ) ) {
			return;
		}

		if ( isset( $_COOKIE[ 'tqb-conversion-' . $variation['page_id'] ] ) ) {
			$data['duplicate'] = 1;
		}

		global $tqbdb;

		$data['date']         = date( 'Y-m-d H:i:s', time() );
		$data['event_type']   = Thrive_Quiz_Builder::TQB_CONVERSION;
		$data['variation_id'] = $variation['id'];
		$data['user_unique']  = $user_unique; //TQB_Quiz_Manager::get_quiz_user( $user_unique, $variation['quiz_id'] );
		$data['page_id']      = $variation['page_id'];

		$page_manager = new TQB_Page_Manager( $variation['page_id'] );
		$active_test  = $page_manager->get_tests_for_page( array(
			'page_id' => $variation['page_id'],
			'status'  => 1,
		), true );

		if ( isset( $variation['id'] ) && ! isset( $data['duplicate'] ) ) {
			$update_data = array( 'variation_id' => $variation['id'], 'conversion' => true );
			$tqbdb->update_variation_cached_counter( $update_data );
			if ( ! empty( $active_test ) ) {
				$update_data['test_id'] = $active_test['id'];
				$tqbdb->update_test_item_action_counter( $update_data );

				/*Check for test auto win*/
				$test_manager = new TQB_Test_Manager( $active_test['id'] );
				$test_manager->check_test_auto_win();
				$test_manager->stop_underperforming_variations();
			}
		}

		$tqbdb->create_event_log_entry( $data );

		setcookie( 'tqb-conversion-' . $variation['page_id'], 1, time() + ( 30 * 24 * 3600 ), '/' );
		$_COOKIE[ 'tqb-conversion-' . $variation['page_id'] ] = true;

		setcookie( 'tqb-conversion-' . $variation['page_id'] . '-' . str_replace( '.', '_', $user_unique ), 1, time() + ( 30 * 24 * 3600 ), '/' );
		$_COOKIE[ 'tqb-conversion-' . $variation['page_id'] . '-' . str_replace( '.', '_', $user_unique ) ] = true;
	}

	/**
	 * Update the tqb_users table with the user's id if a new wp account was created after the opt-in-gate submission
	 *
	 * @param array              $data
	 * @param WP_User|array|null $current_user
	 *
	 */
	public static function tqb_lead_signup( $data, $current_user ) {
		if ( ! empty( $data['form_data']['tqb-variation-page_id'] ) && empty( $current_user ) ) {
			$page = get_post( $data['form_data']['tqb-variation-page_id'] );
			if ( empty( $page ) ) {
				return;
			}

			global $tqbdb;
			$variation = $tqbdb->get_variation( $data['form_data']['tqb-variation-variation_id'] );
			if ( empty( $variation ) ) {
				return;
			}

			$page_manager = new TQB_Page_Manager( $variation['page_id'] );

			$email   = ( $page_manager->get_user_consent() === 1 ) ? $data['form_data']['email'] : ( ( function_exists( 'wp_privacy_anonymize_data' ) ) ? wp_privacy_anonymize_data( 'email', $data['form_data']['email'] ) : '' );
			$wp_user = get_user_by( 'email', $email );
			if ( $wp_user !== false ) {
				$user_id                    = TQB_Quiz_Manager::get_quiz_user( $data['form_data']['tqb-variation-user_unique'], $page->post_parent );
				$user_details               = array( 'id' => $user_id, 'email' => $email );
				$user_details['wp_user_id'] = $wp_user->ID;

				$tqbdb->save_quiz_user( $user_details );
			}
		}
	}

	/**
	 * Register a page conversion
	 */
	public static function tqb_register_optin_conversion( $post ) {
		if ( empty( $post['tqb-variation-page_id'] ) || empty( $post['tqb-variation-user_unique'] ) || static::prevent_access() ) {
			return;
		}

		if ( isset( $_COOKIE[ 'tqb-conversion-' . $post['tqb-variation-page_id'] . '-' . str_replace( '.', '_', $post['tqb-variation-user_unique'] ) ] ) ) {
			return;
		}

		if ( isset( $_COOKIE[ 'tqb-conversion-' . $post['tqb-variation-page_id'] ] ) ) {
			$data['duplicate'] = 1;
		}

		$page = get_post( $post['tqb-variation-page_id'] );

		if ( empty( $page ) ) {
			return;
		}

		global $tqbdb;
		$variation = $tqbdb->get_variation( $post['tqb-variation-variation_id'] );

		if ( empty( $variation ) ) {
			return;
		}

		$data['date']         = date( 'Y-m-d H:i:s', time() );
		$data['event_type']   = Thrive_Quiz_Builder::TQB_CONVERSION;
		$data['variation_id'] = $variation['id'];
		$data['user_unique']  = $post['tqb-variation-user_unique']; //TQB_Quiz_Manager::get_quiz_user( $post['tqb-variation-user_unique'], $page->post_parent );
		$data['page_id']      = $variation['page_id'];
		$data['optin']        = 1;

		$result = $tqbdb->create_event_log_entry( $data );

		$page_manager = new TQB_Page_Manager( $variation['page_id'] );
		$active_test  = $page_manager->get_tests_for_page( array(
			'page_id' => $variation['page_id'],
			'status'  => 1,
		), true );

		if ( isset( $variation['id'] ) && ! is_array( $result ) && ! isset( $data['duplicate'] ) ) {
			$update_data = array( 'variation_id' => $variation['id'], 'conversion' => true );
			$tqbdb->update_variation_cached_counter( $update_data );
			if ( ! empty( $active_test ) ) {
				$update_data['test_id'] = $active_test['id'];
				$tqbdb->update_test_item_action_counter( $update_data );
			}
		}

		/**
		 * GDPR: If we have the user consent, we send the email to the database. If not, the email will not be stored.
		 */
		$post['email'] = ( $page_manager->get_user_consent() === 1 ) ? $post['email'] : ( ( function_exists( 'wp_privacy_anonymize_data' ) ) ? wp_privacy_anonymize_data( 'email', $post['email'] ) : '' );

		$user_id = TQB_Quiz_Manager::get_quiz_user( $data['user_unique'], $page->post_parent );
		$tqbdb->save_quiz_user( array( 'id' => $user_id, 'email' => $post['email'] ) );

		setcookie( 'tqb-conversion-' . $variation['page_id'], 1, time() + ( 30 * 24 * 3600 ), '/' );
		$_COOKIE[ 'tqb-conversion-' . $variation['page_id'] ] = true;

		setcookie( 'tqb-conversion-' . $variation['page_id'] . '-' . str_replace( '.', '_', $post['tqb-variation-user_unique'] ), 1, time() + ( 30 * 24 * 3600 ), '/' );
		$_COOKIE[ 'tqb-conversion-' . $variation['page_id'] . '-' . str_replace( '.', '_', $post['tqb-variation-user_unique'] ) ] = true;

		/**
		 * Trigger action on quiz conversion
		 */
		do_action( 'tqb_optin_conversion', $data );
	}

	/**
	 * Register a page conversion
	 */
	public static function tqb_register_social_media_conversion( $post ) {

		if ( static::prevent_access() ) {
			return;
		}

		$page_id      = ( is_numeric( $post['page_id'] ) && ! empty( $post['page_id'] ) ) ? $post['page_id'] : 0;
		$quiz_id      = ( is_numeric( $post['quiz_id'] ) && ! empty( $post['quiz_id'] ) ) ? $post['quiz_id'] : 0;
		$variation_id = ( is_numeric( $post['variation_id'] ) && ! empty( $post['variation_id'] ) ) ? $post['variation_id'] : 0;

		$page = get_post( $page_id );

		if ( empty( $page ) ) {
			return;
		}

		if ( isset( $_COOKIE[ 'tqb-conversion-social-media-' . $page_id . '-' . str_replace( '.', '_', $post['tqb-variation-user_unique'] ) ] ) || $page->post_type !== Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_RESULTS ) {
			return;
		}

		if ( isset( $_COOKIE[ 'tqb-conversion-social-media-' . $page_id ] ) ) {
			$data['duplicate'] = 1;
		}

		global $tqbdb;
		$variation = $tqbdb->get_variation( $variation_id );

		if ( empty( $variation ) ) {
			return;
		}

		$data['date']         = date( 'Y-m-d H:i:s', time() );
		$data['event_type']   = Thrive_Quiz_Builder::TQB_CONVERSION;
		$data['variation_id'] = $variation['id'];
		$data['user_unique']  = $post['tqb-variation-user_unique'];
		$data['page_id']      = $variation['page_id'];
		$data['social_share'] = 1;

		$result = $tqbdb->create_event_log_entry( $data );
		if ( isset( $variation['id'] ) && ! is_array( $result ) && ! isset( $data['duplicate'] ) ) {
			$variation_manager = new TQB_Variation_Manager( $quiz_id, $page_id );
			$variation_manager->update_social_share_conversion( $variation_id );
		}

		setcookie( 'tqb-conversion-social-media-' . $variation['page_id'], 1, time() + ( 30 * 24 * 3600 ), '/' );
		$_COOKIE[ 'tqb-conversion-social-media-' . $variation['page_id'] ] = true;

		setcookie( 'tqb-conversion-social-media-' . $variation['page_id'] . '-' . str_replace( '.', '_', $post['tqb-variation-user_unique'] ), 1, time() + ( 30 * 24 * 3600 ), '/' );
		$_COOKIE[ 'tqb-conversion-social-media-' . $variation['page_id'] . '-' . str_replace( '.', '_', $post['tqb-variation-user_unique'] ) ] = true;
	}

	/**
	 * Whether or not we have to prevent access to events
	 *
	 * @return bool
	 */
	public static function prevent_access() {
		return current_user_can( 'manage_options' ) || TQB_Product::has_access() || tve_dash_is_crawler();
	}

	/**
	 * Register optin skip event
	 */
	public static function tqb_register_skip_optin_event( $variation, $user_unique ) {
		if ( static::prevent_access() ) {
			return;
		}

		if ( isset( $_COOKIE[ 'tqb-conversion-' . $variation['page_id'] . '-' . str_replace( '.', '_', $user_unique ) ] ) ) {
			return;
		}

		if ( isset( $_COOKIE[ 'tqb-conversion-' . $variation['page_id'] ] ) ) {
			$data['duplicate'] = 1;
		}

		global $tqbdb;

		$data['date']         = date( 'Y-m-d H:i:s', time() );
		$data['event_type']   = Thrive_Quiz_Builder::TQB_SKIP_OPTIN;
		$data['variation_id'] = $variation['id'];
		$data['user_unique']  = $user_unique;
		$data['page_id']      = $variation['page_id'];

		$tqbdb->create_event_log_entry( $data );

		setcookie( 'tqb-conversion-' . $variation['page_id'], 1, time() + ( 30 * 24 * 3600 ), '/' );
		$_COOKIE[ 'tqb-conversion-' . $variation['page_id'] ] = true;

		setcookie( 'tqb-conversion-' . $variation['page_id'] . '-' . str_replace( '.', '_', $user_unique ), 1, time() + ( 30 * 24 * 3600 ), '/' );
		$_COOKIE[ 'tqb-conversion-' . $variation['page_id'] . '-' . str_replace( '.', '_', $user_unique ) ] = true;
	}

	/**
	 * Register a quiz completion
	 */
	public static function tqb_register_quiz_completion( $user_unique, $page_id, $do_action = false ) {
		global $tqbdb;
		$page = get_post( $page_id );

		if ( ! empty( $page ) && ( $page->post_type === Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_OPTIN || $page->post_type === Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_RESULTS ) ) {
			$user = TQB_Quiz_Manager::get_quiz_user( $user_unique, $page->post_parent, true );
			$tqbdb->save_quiz_user( array( 'id' => $user['id'], 'completed_quiz' => 1 ) );

			if ( $do_action ) {
				$quiz_manager = new TQB_Quiz_Manager( $page->post_parent );
				$quiz         = $quiz_manager->get_quiz();

				$reporting_manager = new TQB_Reporting_Manager( $page->post_parent, 'users' );
				$data              = $reporting_manager->get_users_answers( $user['id'] );
				$user['flow']      = $data;

				if ( empty( $user['email'] ) && is_user_logged_in() ) {
					/**
					 * Fix for sending notifications from Notification Manager
					 * If user is logged in and there is no email from the lead gen form -> we send the user logged in email
					 */
					$user['email'] = tqb_customer()->get_email();
				}

				do_action( 'tqb_quiz_completed', $quiz, $user );
			}
		}
	}

	/**
	 * Get the quiz details
	 *
	 * @param        $quiz_id
	 * @param        $user_unique
	 * @param string $result
	 *
	 * @return array
	 */
	public static function get_quiz_details( $quiz_id, $user_unique, $result = '', $email = '' ) {
		$answers = array();

		if ( ! empty( $user_unique ) ) {
			/**
			 * Avoid cases where system creates a new user if this function without $user_unique
			 */
			$answers = TQB_Quiz_Manager::get_user_answers_with_questions(
				array(
					'quiz_id' => $quiz_id,
					'user_id' => TQB_Quiz_Manager::get_quiz_user( $user_unique, $quiz_id ),
				)
			);
		}

		return array(
			'quiz_id'    => $quiz_id,
			'quiz_name'  => get_the_title( $quiz_id ),
			'result'     => $result,
			'answers'    => $answers,
			'user_id'    => get_current_user_id(),
			'form_data'  => array(),
			'user_email' => $email,
		);
	}

	/**
	 * Get quiz user using unique identifier and quiz id
	 *
	 * @param null  $user_unique
	 * @param null  $quiz_id
	 * @param bool  $full_data
	 * @param array $filters
	 *
	 * @return array|int
	 */
	public static function get_quiz_user( $user_unique = null, $quiz_id = null, $full_data = false, $filters = array() ) {

		$ignore_user = null;
		if ( tve_dash_is_crawler() ) {
			$ignore_user = 1;
		}

		global $tqbdb;
		$user = $tqbdb->get_quiz_user( $user_unique, $quiz_id );

		if ( empty( $user ) ) {
			return $tqbdb->save_quiz_user( array_merge( array(
				'random_identifier' => $user_unique,
				'quiz_id'           => $quiz_id,
				'ignore_user'       => $ignore_user,
			), $filters ) );
		}

		return $full_data ? $user : $user['id'];
	}

	public function save_results( $results = array(), $prev_results = array() ) {

		if ( ! empty( $prev_results ) ) {
			$aux_prev = array();
			foreach ( $prev_results as $prev_item ) {
				$aux_prev[ $prev_item['id'] ] = $prev_item;
			}

			foreach ( $results as $key => $value ) {
				if ( ! empty( $value['id'] ) ) {
					unset( $aux_prev[ $value['id'] ] );
				}

				if ( empty( $value['text'] ) ) {
					unset( $results[ $key ] );
				}
			}

			foreach ( $aux_prev as $aux_p ) {
				$this->tqbdb->delete_quiz_results( array( 'id' => $aux_p['id'] ) );
			}
		}

		return $this->tqbdb->save_quiz_results( $this->quiz->ID, $results );
	}

	public function get_results() {

		return $this->tqbdb->get_quiz_results( $this->quiz->ID );
	}

	/**
	 * Get count of completed quizzes
	 */
	public static function get_completed_quiz_count( $quiz_id ) {
		global $tqbdb;

		return $tqbdb->get_completed_quiz_count( $quiz_id );
	}

	/**
	 * Calculate a certain user's points on a quiz
	 */
	public static function calculate_user_points( $user_unique, $quiz_id ) {
		global $tqbdb;

		return $tqbdb->calculate_user_points( $user_unique, $quiz_id );
	}

	/**
	 * Get a certain user's points on a quiz
	 */
	public static function get_user_points( $user_unique, $quiz_id ) {
		global $tqbdb;

		return $tqbdb->get_user_points( $user_unique, $quiz_id );
	}

	/**
	 * Get a certain user's answers on a quiz
	 *
	 * @param array $params
	 *
	 * @return array|null
	 */
	public static function get_user_answers( $params ) {
		global $tqbdb;

		return $tqbdb->get_user_answers( $params );
	}

	/**
	 * @param array $args
	 *
	 * @return array|object|null
	 */
	public static function get_user_answers_with_questions( $args ) {
		global $tqbdb;

		return $tqbdb->get_user_answers_with_questions( $args );
	}

	/**
	 * Delete a certain user's answers on a quiz
	 *
	 * @param array $params
	 *
	 * @return bool|int
	 */
	public static function delete_user_answers( $params ) {
		global $tqbdb;

		return $tqbdb->delete_user_answers( $params );
	}

	/**
	 * Save a certain user's points on a quiz
	 */
	public static function save_user_points( $user_unique, $quiz_id ) {
		global $tqbdb;

		$points = TQB_Quiz_Manager::calculate_user_points( $user_unique, $quiz_id );

		$tqbdb->save_quiz_user( array(
			'id'     => TQB_Quiz_Manager::get_quiz_user( $user_unique, $quiz_id ),
			'points' => $tqbdb->get_explicit_result( $points ),
		) );

		return $points;
	}

	/**
	 * Get quiz social share count
	 */
	public static function get_quiz_social_shares_count( $quiz_id ) {
		global $tqbdb;

		return $tqbdb->get_quiz_social_shares_count( $quiz_id );
	}

	/**
	 * Get total quiz users count
	 */
	public static function get_quiz_users_count( $quiz_id ) {
		global $tqbdb;

		return $tqbdb->get_quiz_users_count( $quiz_id );
	}

	/**
	 * Deletes quiz results and user answers
	 *
	 * @param int $quiz_id
	 *
	 * @return int
	 */
	public function delete_quiz_results_and_user_answers( $quiz_id = 0 ) {
		if ( empty( $quiz_id ) ) {
			$quiz_id = $this->quiz->ID;
		}
		global $tqbdb;

		$deleted_results = $tqbdb->delete_quiz_results( array(
			'quiz_id' => $quiz_id,
		) );

		$delete_users = $tqbdb->delete_quiz_users( array(
			'quiz_id' => $quiz_id,
		) );

		$deleted_answers = $tqbdb->delete_user_answers( array(
			'quiz_id' => $quiz_id,
		) );

		return $deleted_results && $deleted_answers;
	}

	/**
	 * Delete Logs, Users and User Answers from Database
	 *
	 * @return bool
	 */
	public function reset_stats() {

		$structure_manager = new TQB_Structure_Manager( $this->quiz->ID );
		$structure_meta    = $structure_manager->get_quiz_structure_meta();

		$pages = array(
			$this->quiz->ID,
		);

		if ( ! empty( $structure_meta['splash'] ) ) {
			$pages[] = $structure_meta['splash'];
		}

		if ( ! empty( $structure_meta['optin'] ) ) {
			$pages[] = $structure_meta['optin'];
		}

		if ( ! empty( $structure_meta['results'] ) ) {
			$pages[] = $structure_meta['results'];
		}

		/**
		 * Delete logs
		 */
		$logs_deleted = $this->tqbdb->delete_multiple_logs( array(
			'page_id' => $pages,
		) );

		$reset_stats = $logs_deleted !== false;

		/**
		 * Delete users
		 */
		if ( $reset_stats ) {
			$users_deleted = $this->tqbdb->delete_quiz_users( array(
				'quiz_id' => $this->quiz->ID,
			) );

			$reset_stats = $users_deleted !== false;
		}

		/**
		 * Delete user answers
		 */
		if ( $reset_stats ) {
			$user_answers_deleted = $this->tqbdb->delete_user_answers( array(
				'quiz_id' => $this->quiz->ID,
			) );

			$reset_stats = $user_answers_deleted !== false;
		}

		/**
		 * Reset Questions Views
		 */
		if ( $reset_stats ) {
			$question_manager      = new TGE_Question_Manager( $this->quiz->ID );
			$questions_views_reset = $question_manager->reset_questions_views();

			$reset_stats = $questions_views_reset !== false;
		}

		/**
		 * Update set reset time
		 */
		$structure_meta['last_reset'] = current_time( 'timestamp' );
		$structure_manager->update_quiz_structure_meta( $structure_meta );

		return $reset_stats;
	}

	/**
	 * Anonymize Quiz Results
	 */
	public function anonymize_quiz_results() {
		$users = $this->tqbdb->get_users( array( 'quiz_id' => $this->quiz->ID ) );
		if ( ! empty( $users ) ) {

			foreach ( $users as $user ) {

				$anonymize_email = ( function_exists( 'wp_privacy_anonymize_data' ) ) ? wp_privacy_anonymize_data( 'email', $user['email'] ) : '';

				$this->tqbdb->save_quiz_user( array(
					'id'         => $user['id'],
					'quiz_id'    => $this->quiz->ID,
					'email'      => $anonymize_email,
					'wp_user_id' => 0,
				) );
			}
		}
	}

	/**
	 * Clone an existing valid Quiz, clone meta, qna and social badge
	 *
	 * @return int|WP_Error
	 */
	public function clone_quiz() {

		$post = get_post( $this->quiz->ID );
		unset( $post->ID );
		$post->post_title = '[' . __( 'Copy', 'thrive-quiz-builder' ) . '] ' . $post->post_title;

		$new_id = wp_insert_post( $post );
		tqb_copy_meta( $this->quiz->ID, $new_id );
		TQB_Post_meta::update_quiz_order( $new_id, 0 );

		return $new_id;
	}

	/**
	 * Generate duplicate quiz's pages and update quiz_structure
	 *
	 * @param string $meta_value 'tqb_quiz_structure'
	 * @param int    $clone_id   quiz_id
	 *
	 * @return mixed
	 */
	public function get_clone_pages( $meta_value, $clone_id ) {

		$pages = tqb()->get_structure_internal_identifiers();
		unset( $pages['qna'] );
		$quiz_type = TQB_Post_meta::get_quiz_type_meta( $this->quiz->ID, true );

		$related_categories = array();

		if ( $quiz_type === Thrive_Quiz_Builder::QUIZ_TYPE_PERSONALITY ) {
			$related_categories = $this->get_related_categories_ids( $clone_id );
		}

		foreach ( $pages as $page ) {
			$page_id = $meta_value[ $page ];
			if ( ! empty( $meta_value[ $page ] ) && is_numeric( $page_id ) ) {
				$variation_manager = new TQB_Variation_Manager( $this->quiz->ID, $page_id );
				$variations        = $variation_manager->get_page_variations( array(
					'parent_id' => 0,
				) );

				$structure_page = TQB_Structure_Manager::make_page( $page_id );
				$post           = get_post( $page_id );
				unset( $post->ID );
				$post->post_parent                  = $clone_id;
				$post->ID                           = wp_insert_post( $post );
				$structure_page->related_categories = $related_categories;
				$structure_page->clone_to( $post->ID );
				$meta_value[ $page ] = $post->ID;
				$clone_manager       = new TQB_Variation_Manager( $clone_id, $post->ID );
				$default_stats       = array(
					'cache_impressions'               => 0,
					'cache_optins'                    => 0,
					'cache_optins_conversions'        => 0,
					'cache_social_shares'             => 0,
					'cache_social_shares_conversions' => 0,
				);
				foreach ( $variations as $variation ) {
					$var_id      = $variation['id'];
					$old_quiz_id = $variation['quiz_id'];

					if ( 'splash' !== $page ) {
						$child_variations = $variation_manager->get_page_variations(
							array(
								'parent_id' => $variation['id'],
							)
						);
					}
					unset( $variation['id'] );
					$variation['quiz_id'] = $clone_id;
					$variation['page_id'] = $post->ID;
					$variation            = array_merge( $variation, $default_stats );
					$clone_variation      = $clone_manager->save_variation( $variation, true );

					$clone_variation['content'] = str_replace( $this->quiz->ID . '.png', $clone_id . '.png', $clone_variation['content'] );
					$clone_variation['content'] = str_replace( 'value="' . $var_id, 'value="' . $clone_variation['id'], $clone_variation['content'] );
					$clone_variation['content'] = str_replace( 'next_step_in_quiz_' . $old_quiz_id, 'next_step_in_quiz_' . $clone_id, $clone_variation['content'] );
					$clone_variation['content'] = str_replace( 'restart_quiz_' . $old_quiz_id, 'restart_quiz_' . $clone_id, $clone_variation['content'] );

					$clone_manager->save_variation( $clone_variation, true );
					if ( ! empty( $child_variations ) ) {
						foreach ( $child_variations as $child_variation ) {
							unset( $child_variation['id'] );
							$child_variation['quiz_id']   = $clone_id;
							$child_variation['page_id']   = $post->ID;
							$child_variation['parent_id'] = $clone_variation['id'];
							if ( $quiz_type === Thrive_Quiz_Builder::QUIZ_TYPE_PERSONALITY && ! empty( $related_categories ) ) {
								$tcb_fields                    = _unserialize_fields( $child_variation['tcb_fields'] );
								$tcb_fields['result_id']       = $related_categories[ $tcb_fields['result_id'] ];
								$child_variation['tcb_fields'] = $tcb_fields;
							}
							$child_variation = array_merge( $child_variation, $default_stats );
							$clone_manager->save_variation( $child_variation, true );
						}
					}
				}
			}
		}
		$meta_value['ID'] = $clone_id;
		/**
		 * Cloning the other component only after the structure has been created so we have related categories created otherwise they will inserted two times
		 */
		$this->clone_social_badge( $clone_id );
		$this->clone_qna( $clone_id, array(
			'related_categories' => $related_categories,
		) );

		return $meta_value;
	}

	/**
	 * Duplicate social badge for the duplicate quiz
	 *
	 * @param $clone_quiz_id
	 */
	public function clone_social_badge( $clone_quiz_id ) {
		$args         = array( 'post_type' => TIE_Post_Types::THRIVE_IMAGE, 'post_parent' => $this->quiz->ID, 'numberposts' => 1 );
		$posts        = get_posts( $args );
		$social_badge = reset( $posts );

		if ( ! empty( $social_badge ) ) {
			$upload_dir    = wp_upload_dir();
			$file_path     = $upload_dir['basedir'] . '/' . Thrive_Quiz_Builder::UPLOAD_DIR_CUSTOM_FOLDER . '/' . $this->quiz->ID . '.png';
			$new_file_path = $upload_dir['basedir'] . '/' . Thrive_Quiz_Builder::UPLOAD_DIR_CUSTOM_FOLDER . '/' . $clone_quiz_id . '.png';
			if ( is_file( $file_path ) ) {
				copy( $file_path, $new_file_path );
				$file_url = $upload_dir['baseurl'] . '/' . Thrive_Quiz_Builder::UPLOAD_DIR_CUSTOM_FOLDER . '/' . $clone_quiz_id . '.png?' . rand();
				do_action( 'tqb_update_social_share_badge_url', $clone_quiz_id, $file_url, null );
			}
			$old_id = $social_badge->ID;
			unset( $social_badge->ID );
			$social_badge->post_parent = $clone_quiz_id;
			$new_id                    = wp_insert_post( $social_badge );
			tqb_copy_meta( $old_id, $new_id );
		}

	}

	/**
	 * Saving clone's categories and getting relationship between original and the new ones
	 *
	 * @param $duplicate_quiz_id
	 *
	 * @return array
	 */
	public function get_related_categories_ids( $duplicate_quiz_id ) {
		$return = array();

		$original_quiz_categories = $this->get_results();
		$categories               = array();
		foreach ( $original_quiz_categories as $cat ) {
			unset( $cat['id'] );
			$categories[] = $cat;
		}
		$categories = $this->tqbdb->save_quiz_results( $duplicate_quiz_id, $categories );
		foreach ( $categories as $key => $value ) {
			$return[ $original_quiz_categories[ $key ]['id'] ] = $value['id'];
		}

		return $return;
	}

	/**
	 * Duplicate Questions And Answers
	 *
	 * @param $duplicate_quiz_id
	 * @param $args array
	 */
	public function clone_qna( $duplicate_quiz_id, $args = array() ) {
		$q_manager        = new TGE_Question_Manager( $this->quiz->ID );
		$questions        = $q_manager->get_quiz_questions( array( 'quiz_id' => $this->quiz->ID, 'with_answers' => true ) );
		$new_quiz_manager = new TGE_Question_Manager( $duplicate_quiz_id );

		$related_questions = array();

		foreach ( $questions as $question ) {
			$q_id = $question['id'];
			unset( $question['id'], $question['answers'] );
			$question['quiz_id'] = $duplicate_quiz_id;
			$question['image']   = json_decode( json_encode( $question['image'] ), true );
			if ( is_string( $question['position'] ) ) {
				wp_parse_str( $question['position'], $question['position'] );
			}
			$q_position = array();
			foreach ( $question['position'] as $key => $value ) {
				$q_position[ $key ] = $value;
			}
			$question['position']       = $q_position;
			$question                   = $new_quiz_manager->save_question( $question );
			$related_questions[ $q_id ] = $question['id'];

		}

		foreach ( $questions as $question ) {
			$old_answers = $q_manager->get_answers( array(
				'question_id' => $question['id'],
			) );

			if ( ! empty( $old_answers ) ) {
				foreach ( $old_answers as $answer ) {
					unset( $answer['id'] );
					$answer['quiz_id'] = $duplicate_quiz_id;
					if ( ! empty( $answer['question_id'] ) ) {
						$answer['question_id'] = $related_questions[ $question['id'] ];
					}
					$answer['image'] = json_decode( json_encode( $answer['image'] ), true );
					if ( ! empty( $answer['next_question_id'] ) ) {
						$answer['next_question_id'] = $related_questions[ $answer['next_question_id'] ];
					}
					if ( ! empty( $answer['result_id'] ) && ! empty( $args['related_categories'] ) ) {
						$answer['result_id'] = $args['related_categories'][ $answer['result_id'] ];
					}
					$new_quiz_manager->save_answer( $answer );
				}
			}
		}

		$new_question = $new_quiz_manager->get_quiz_questions( array( 'quiz_id' => $this->quiz->ID ) );
		foreach ( $new_question as $question ) {
			if ( ! empty( $question['next_question_id'] ) ) {
				$question['next_question_id'] = $related_questions[ $question['next_question_id'] ];
			}
			if ( ! empty( $question['previous_question_id'] ) ) {
				$question['previous_question_id'] = $related_questions[ $question['previous_question_id'] ];
			}
			$question['image'] = json_decode( json_encode( $question['image'] ), true );
			$new_quiz_manager->save_question( $question );
		}
	}

	/**
	 * Based on current quiz post gets QNA Editor Url
	 *
	 * @return string
	 */
	public function get_qna_editor_url() {

		$url = 'javascript:void(0)';

		if ( false === $this->quiz instanceof WP_Post ) {
			return $url;
		}

		$url = set_url_scheme( get_edit_post_link( $this->quiz->ID, '' ) );

		return esc_url(
			add_query_arg(
				array(
					'action' => 'architect',
					'tve'    => 'true',
				),
				$url
			)
		);
	}

	/**
	 * Check if it is a different answer to an already answered question and if so,
	 * then delete the answers which are on a different branch of the quiz
	 *
	 * Otherwise, check for unneeded answers in db, for more info check function "handle_passive_branch_switch"
	 *
	 * @param array  $answer_ids
	 * @param string $quiz_user_id
	 * @param int    $quiz_id
	 *
	 * @return bool
	 */
	public static function handle_answer_already_saved( $answer_ids, $quiz_user_id, $quiz_id ) {
		/** @var TGE_Database $tgedb */
		global $tgedb;
		$data['answer_ids']   = $answer_ids;
		$data['quiz_id']      = $quiz_id;
		$data['quiz_user_id'] = $quiz_user_id;

		if ( $answer_ids[0] ) {

			// get answer check if valid
			$answer = $tgedb->get_answers( array( 'id' => $answer_ids[0] ), true );

			if ( empty( $answer ) || ! isset( $answer['question_id'] ) || ! is_array( $answer ) ) {
				return false;
			}

			$question_manager = new TGE_Question_Manager( $quiz_id );

			$data['db_answers'] = static::get_user_answers( [ 'quiz_id' => $quiz_id, 'user_id' => $quiz_user_id, 'question_id' => $answer['question_id'] ] );

			$data['question'] = $question_manager->get_quiz_questions( [ 'id' => $answer['question_id'] ], true );

			if ( empty( $data['db_answers'] ) && empty ( $data['question']['next_question_id'] ) ) {

				static::handle_passive_branch_switch( $question_manager, $answer, $quiz_id, $quiz_user_id );

			} else if ( empty( $data['db_answers'] ) && ! empty ( $data['question']['next_question_id'] ) ) {
				return false;
			} else {
				static::handle_answer_changed( $question_manager, $data );
			}
		}

		return true;
	}

	/**
	 * The answer to a question was changed, update the db, and if a branch switch happened, delete the unneeded user answers
	 *
	 * @param TGE_Question_Manager $question_manager
	 * @param array                $data
	 */
	protected static function handle_answer_changed( $question_manager, $data ) {
		if ( empty( $data ) || empty( $data['db_answers'] ) || empty( $data['answer_ids'] ) || empty( $data['question'] )
		     || empty( $data['quiz_id'] ) || empty( $data['quiz_user_id'] ) ) {
			return false;
		}

		foreach ( $data['db_answers'] as $db_answer ) {

			$is_new_answer = ! in_array( $db_answer['answer_id'], $data['answer_ids'], true );

			if ( $is_new_answer && empty ( $data['question']['next_question_id'] ) ) {
				/**
				 * If the question has multiple branches we need to delete the answers from the previous branch
				 */
				$question_id           = $db_answer['question_id'];
				$answer_options        = $question_manager->get_answers( [ 'question_id' => $question_id ] );
				$current_answer_object = $question_manager->get_answers( [ 'id' => $data['answer_ids'][0] ], true );
				foreach ( $answer_options as $answer_option ) {

					if ( $answer_option['next_question_id'] !== $current_answer_object['next_question_id'] ) {
						/**
						 * only remove answers from branch if they lead to a different one and up until the branches join together
						 *
						 * @var TGE_Database $tgedb
						 */
						global $tgedb;

						$join_points = $tgedb->get_question_ids_where_branches_meet( $data['quiz_id'] );

						//If the previous answer leads to an intersection as well, we need to ignore that and remove it from the $join_points array
						if ( ( $key = array_search( $answer_option['next_question_id'], $join_points ) ) !== false ) {
							unset( $join_points[ $key ] );
						}
						static::remove_answers_from_branch( $question_manager, $question_id, $data['quiz_id'], $data['quiz_user_id'], $join_points );
					}
				}

				break;
			}

			if ( ! empty ( $data['question']['next_question_id'] ) ) {
				static::delete_user_answers( [ 'quiz_id' => $data['quiz_id'], 'user_id' => $data['quiz_user_id'], 'question_id' => $db_answer['question_id'] ] );
				break;
			}
		}
	}

	/**
	 * Although there are no answers saved in db, a 'branch switch' might be happening if the quiz branches are interconnected in a specific way
	 *
	 * Longer case description:
	 * A user might have gone through a branch, gave some answers and then changed the base branch
	 * if the branches would meet but not fully join, than the answers after that are not yet deleted, because the user might arrive back to that branch.
	 * In this case the user did not go that route so we must delete the remaining answers until the next join point
	 *
	 * @param TGE_Question_Manager $question_manager
	 * @param array                $answer
	 * @param int                  $quiz_id
	 * @param string               $quiz_user_id
	 */
	protected static function handle_passive_branch_switch( $question_manager, $answer, $quiz_id, $quiz_user_id ) {
		$question_id    = $answer['question_id'];
		$answer_options = $question_manager->get_answers( [ 'question_id' => $question_id ] );

		foreach ( $answer_options as $answer_option ) {
			if ( $answer_option['next_question_id'] !== $answer['next_question_id'] ) {

				$branching_question_db_answers = static::get_user_answers( [ 'quiz_id' => $quiz_id, 'user_id' => $quiz_user_id, 'question_id' => $answer_option['next_question_id'] ] );

				if ( ! empty( $branching_question_db_answers ) ) {
					/**
					 * Means we have to delete the answers that we previously left still in the db, because the user chose a different path
					 *
					 * @var TGE_Database $tgedb
					 */
					global $tgedb;

					$join_points = $tgedb->get_question_ids_where_branches_meet( $quiz_id );
					static::remove_answers_from_branch( $question_manager, $answer_option['next_question_id'], $quiz_id, $quiz_user_id, $join_points, true );
				}
			}
		}
	}

	/**
	 * Recursively delete every answer on this branch up until there is a question where branches intersect
	 *
	 * @param TGE_Question_Manager $question_manager
	 * @param int                  $question_id
	 * @param int                  $quiz_id
	 * @param string               $quiz_user_id
	 * @param array                $join_points  contains question_ids where branches intersect in the quiz
	 * @param boolean              $delete_first if we need to delete the answers starting from a question where branches intersect this will be set to true
	 */
	public static function remove_answers_from_branch( $question_manager, $question_id, $quiz_id, $quiz_user_id, $join_points, $delete_first = false ) {
		$db_branch_answers = static::get_user_answers( [ 'quiz_id' => $quiz_id, 'user_id' => $quiz_user_id, 'question_id' => $question_id ] );

		if ( empty( $db_branch_answers ) || empty( $question_id ) ) {
			return;
		}

		if ( ! $delete_first && in_array( (string) $question_id, $join_points, false ) ) {
			return;
		}

		$question = $question_manager->get_quiz_questions( [ 'id' => $question_id ], true );

		/**
		 * If along this branch there was another branch...
		 */
		if ( empty( $question['next_question_id'] ) ) {
			$answer_options = $question_manager->get_answers( [ 'question_id' => $question_id ] );

			foreach ( $answer_options as $answer_option ) {
				static::remove_answers_from_branch( $question_manager, $answer_option['next_question_id'], $quiz_id, $quiz_user_id, $join_points );
			}
		} else {
			static::remove_answers_from_branch( $question_manager, $question['next_question_id'], $quiz_id, $quiz_user_id, $join_points );
		}

		static::delete_user_answers( [ 'quiz_id' => $quiz_id, 'user_id' => $quiz_user_id, 'question_id' => $question_id ] );
	}

	/**
	 * Orders the user's answers and prepares them to be sent by email
	 *
	 * @param $quiz_id
	 * @param $user_id
	 *
	 * @return array
	 */
	public static function get_user_answers_in_order( $quiz_id, $user_id ) {
		global $tqbdb;

		$user_answers = $tqbdb->get_user_answers( array( 'quiz_id' => $quiz_id, 'user_id' => $user_id ) );

		//The first item in this array will contain the id of the first question
		$quiz_map = $tqbdb->get_quiz_map( $quiz_id );
		$answers  = [];

		if ( ! empty( $quiz_map ) && ! empty( $quiz_map[0] ) && $quiz_map[0]['start'] === '1' ) {
			$question_id = $quiz_map[0]['question_id'];

			$question_answers = array_filter( $user_answers, static function ( $user_answer ) use ( $question_id ) {
				return $user_answer['question_id'] === $question_id;
			} );

			while ( ! empty( $question_answers ) ) {
				$answer_ids = [];
				foreach ( $question_answers as $question_answer ) {
					$answer_ids[] = $question_answer['answer_id'];
				}

				$question_answers = array_values( $question_answers );

				$next_questions = array_filter( $quiz_map, static function ( $question ) use ( $question_id, $answer_ids ) {
					return ( $question['question_id'] === $question_id && in_array( $question['answer_id'], $answer_ids ) );
				} );

				if ( ! empty( $next_questions ) ) {
					$next_questions = array_values( $next_questions );
					$next_question  = $next_questions[0];

					//there can be multiple answers to a question
					foreach ( $next_questions as $index => $next_q ) {
						$user_answer           = $question_answers[ $index ];
						$user_answer['a_text'] = $next_q['a_text'];
						$user_answer['q_text'] = $next_q['q_text'];
						$user_answer['q_type'] = $next_q['q_type'];

						$answers[] = $user_answer;
					}

					$question_id = isset( $next_question['q_next_id'] ) ? $next_question['q_next_id'] : $next_question['a_next_id'];

					$question_answers = array_filter( $user_answers, static function ( $user_answer ) use ( $question_id ) {
						return $user_answer['question_id'] === $question_id;
					} );
				} else {
					$question_answers = $next_questions;
				}
			}
		}

		return $answers;
	}

}
