<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

/**
 * Class TGE_Question_Manager
 *
 * Handles Question operations
 */
class TGE_Question_Manager {

	/**
	 * @var TGE_Question_Manager $instance
	 */
	protected $quiz_id;

	/**
	 * @var TGE_Database
	 */
	protected $tgedb;

	protected $questions = array();

	protected $cache_min_max = array();

	protected $costs = array();

	protected $quiz_type = '';

	/**
	 * TGE_Question_Manager constructor.
	 *
	 * @param null $quiz_id
	 */
	public function __construct( $quiz_id = null ) {

		global $tgedb;

		$this->quiz_id   = $quiz_id;
		$this->tgedb     = $tgedb;
		$this->quiz_type = TQB_Post_meta::get_quiz_type_meta( $this->quiz_id, true );
	}

	/**
	 * Get all quiz questions according to filter
	 *
	 * @param array $filters
	 * @param bool  $single
	 *
	 * @return array
	 */
	public function get_quiz_questions( $filters = array(), $single = false ) {

		$single  = (bool) $single;
		$filters = array_merge( $filters, array( 'quiz_id' => $this->quiz_id ) );

		$questions = $this->tgedb->get_quiz_questions( $filters, $single );

		if ( ! empty( $filters['with_answers'] ) ) {
			foreach ( $questions as &$question ) {
				$question['position']         = json_decode( $question['position'] );
				$question['settings']         = json_decode( $question['settings'] );
				$question['display_settings'] = json_decode( $question['display_settings'] );
				$question['answers']          = $this->get_answers( array(
					'question_id' => $question['id'],
				) );
			}
		}

		return $questions;
	}

	/**
	 * Gets the markup and the CSS for the first question of the quiz, as a preview
	 *
	 * @param array $question_data
	 * @param int   $quiz_id
	 *
	 * @return string
	 */
	public function get_first_question_preview( $question_data = array(), $quiz_id = 0 ) {
		$html      = '';
		$questions = $this->get_quiz_questions( array( 'with_answers' => 1 ) );
		$question  = $this->get_quiz_questions( array( 'id' => $question_data['data']['id'] ) )[0];
		$settings  = json_decode( $question['settings'], true );
		$media     = $this->tqb_build_media_display( $question_data );

		if ( ! empty( $quiz_id ) ) {
			$feedback_settings  = TQB_Post_meta::get_feedback_settings_meta( $quiz_id );
			$navigation_enabled = isset ( $feedback_settings['nav_buttons'] ) && $feedback_settings['nav_buttons'];
			$is_openended       = (int) $question['q_type'] === 3;
			$has_next_button    = $navigation_enabled || $feedback_settings['press_next'] || $is_openended || ( $settings['allowed_answers'] > 1 );
		} else {
			$has_next_button = false;
		}

		foreach ( $question_data['css'] as $css ) {
			$html .= '<link rel="stylesheet" type="text/css" href="' . $css . '" media="all">';
		}
		ob_start();
		include tqb()->plugin_path( 'includes/frontend/views/templates/question-answer.php' );
		$html .= ob_get_contents();
		ob_end_clean();

		return $html;
	}

	/**
	 * Build media for showing in TAr quiz shortcode
	 *
	 * @param $data
	 *
	 * @return string
	 */
	public function tqb_build_media_display( $data ) {

		$return = '';
		if ( empty( $data ) ) {
			return $return;
		}

		if ( ! empty( $data['data'] ) && ! empty( $data['data']['display_settings'] ) ) {

			if ( $this->tqb_string_is_json( $data['data']['display_settings'] ) ) {
				$display_settings = json_decode( $data['data']['display_settings'] );

				if ( ! empty( $display_settings->type ) ) {

					switch ( $display_settings->type ) {
						case 'video':
							$return = $this->tqb_build_video_frame( $display_settings );
							break;
						case 'audio':
							$return = $this->tqb_build_audio_frame( $display_settings );
							break;
					}
				}
			}
		}

		return $return;
	}

	/**
	 * Build Audio frames
	 *
	 * @param $display_settings
	 *
	 * @return string
	 */
	public function tqb_build_audio_frame( $display_settings ) {

		if ( empty( $display_settings ) || ! is_object( $display_settings ) ) {
			return '';
		}

		$embed_urls = array(
			'spotify'    => '//open.spotify.com/embed',
			'soundcloud' => '//w.soundcloud.com/player/?url=',
			'custom'     => '',
		);

		$source    = ! empty( $display_settings->audio_source ) ? $display_settings->audio_source : 'custom';
		$video_id  = ! empty( $display_settings->video_id ) ? $display_settings->video_id : '';
		$video_url = 'custom' === $source ? $display_settings->url : $embed_urls[ $source ] . $video_id;

		if ( 'soundcloud' === $source ) {
			$video_url = $embed_urls[ $source ] . $display_settings->url . '?' . $video_id;
		}

		if ( 'custom' === $source ) {
			$options       = ! empty( $display_settings->options ) ? $display_settings->options : false;
			$autoplay      = ! empty( $options->autoplay ) ? 1 === (int) $options->autoplay->value ? 'autoplay' : '' : false;
			$loop          = ! empty( $options->loop ) ? 1 === (int) $options->loop->value ? 'loop' : '' : false;
			$url_arr       = explode( '/', $video_url );
			$file_name     = end( $url_arr );
			$mime_arr      = explode( '.', $file_name );
			$mime_type     = end( $mime_arr );
			$not_supported = __( 'Your browser does not support the audio tag.', 'thrive-graph-editor' );

			return '<div class="tqb-tar-question-audio">
				<audio controls playsinline controlsList="nodownload" style="width: 100%;"' . $autoplay . ' ' . $loop . '
					<source src="' . $video_url . '" type="' . $mime_type . '">
					<source src="' . $video_url . '" type="audio/ogg">
					' . $not_supported . '
					</audio>
				</div>';
		}

		return '<div class="tqb-tar-question-audio"><iframe data-provider="' . $source . '" src="' . $video_url . '"
				height="313" width="651" frameborder="0"
				allowfullscreen="true"
				allowtransparency="true"></iframe></div>';
	}

	/**
	 * Build iframe by display_settings
	 *
	 * @param $display_settings
	 *
	 * @return string
	 */
	public function tqb_build_video_frame( $display_settings ) {

		if ( empty( $display_settings ) || ! is_object( $display_settings ) ) {
			return '';
		}

		$embed_urls     = array(
			'youtube' => '//www.youtube.com/embed/',
			'wistia'  => '//fast.wistia.net/embed/iframe/',
			'vimeo'   => '//player.vimeo.com/video/',
			'bunnynet'   => 'https://iframe.mediadelivery.net/embed/',
			'custom'  => '',
		);
		$source         = ! empty( $display_settings->source ) ? $display_settings->source : 'custom';
		$video_id       = ! empty( $display_settings->video_id ) ? $display_settings->video_id : '';
		$library_id       = ! empty( $display_settings->library_id   ) ? $display_settings->library_id   : '';
		$video_url = ('custom' === $source) ? $display_settings->url : (('bunnynet' === $source) ? $embed_urls[$source] . $library_id . '/' . $video_id : $embed_urls[$source] . $video_id);
		$image_overlay  = $this->video_image_overlay( $display_settings );
		$selected_frame = ! empty( $display_settings->video_style->selected ) ? $display_settings->video_style->selected : '0';

		return '<div class="tqb-question-media"><div class="tge-video-form tge-video-style-' . $selected_frame . '"><div class="tqb-video-container-responsive">' . $image_overlay . '<iframe data-provider="' . $source . '" src="' . $video_url . '"
				height="313" width="651" frameborder="0"
				allowfullscreen="true"
				allowtransparency="true"></iframe></div></div></div>';
	}

	/**
	 * Build image overlay structure
	 *
	 * @param $display_settings
	 *
	 * @return string
	 */
	public function video_image_overlay( $display_settings ) {

		$return = '';
		if ( ! $display_settings || ! is_object( $display_settings ) || empty( $display_settings->thumb ) ) {
			return $return;
		}

		if ( is_object( $display_settings->thumb ) && ! empty( $display_settings->thumb->sizes->full->url ) ) {
			$return = '<div class="tqb_video_overlay_image tqb_overlay_custom" style="background-image: url(' . $display_settings->thumb->sizes->full->url . ')">
						<span class="tqb_overlay_play_button">
							<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"><title>play</title><path fill="#fff" d="M18.659 4.98c-0.889-1.519-2.12-2.75-3.593-3.614l-0.047-0.025q-2.298-1.341-5.020-1.341t-5.019 1.341c-1.52 0.889-2.751 2.12-3.614 3.593l-0.025 0.047q-1.341 2.298-1.341 5.020t1.341 5.020c0.889 1.519 2.12 2.75 3.593 3.614l0.047 0.025q2.298 1.341 5.020 1.341t5.020-1.341c1.519-0.889 2.75-2.12 3.614-3.593l0.025-0.047q1.341-2.298 1.341-5.020t-1.341-5.020zM15 10.716l-7.083 4.167c-0.118 0.074-0.262 0.117-0.416 0.117-0 0-0 0-0.001 0h0c-0.153-0.002-0.296-0.040-0.422-0.107l0.005 0.002q-0.417-0.247-0.417-0.729v-8.333q0-0.482 0.417-0.729 0.43-0.234 0.833 0.013l7.084 4.167q0.416 0.234 0.416 0.716t-0.416 0.716z"></path></svg>		
							</span>
						</div>';
		}

		return $return;
	}

	/**
	 * Check if a string is json
	 *
	 * @param $string
	 *
	 * @return bool
	 */
	public function tqb_string_is_json( $string ) {
		json_decode( $string );

		return ( json_last_error() == JSON_ERROR_NONE );
	}

	protected function get_items() {

		if ( ! empty( $this->questions ) ) {
			return $this->questions;
		}

		$questions = $this->get_quiz_questions( array(
			'with_answers' => true,
		) );

		foreach ( $questions as $question ) {
			$this->questions[ $question['id'] ] = array(
				'id'              => $question['id'],
				'next'            => ! empty( $question['next_question_id'] ) ? $question['next_question_id'] : null,
				'allowed_answers' => ! empty( $question['settings'] ) && ! empty( $question['settings']->allowed_answers ) ? (int) $question['settings']->allowed_answers : 1,
			);

			$answers = array();

			foreach ( $question['answers'] as $answer ) {
				$answers[ $answer['id'] ] = array(
					'id'     => $answer['id'],
					'points' => $answer['points'],
					'next'   => ! empty( $answer['next_question_id'] ) ? $answer['next_question_id'] : null,
				);
			}

			$this->questions[ $question['id'] ]['answers'] = $answers;
		}

		return $this->questions;
	}

	/**
	 * Count all quiz questions according to filter
	 *
	 * @param array $filters
	 *
	 * @return int
	 */
	public function count_questions( $filters = array() ) {

		$filters = array_merge( $filters, array(
			'quiz_id' => $this->quiz_id,
		) );

		return intval( $this->tgedb->count_questions( $filters ) );
	}

	/**
	 * Get all answers according to filter
	 *
	 * @param array $filters
	 * @param bool  $single
	 *
	 * @return false|null|string
	 */
	public function get_answers( $filters, $single = false ) {

		return $this->tgedb->get_answers( $filters, $single );
	}

	/**
	 * Increase question view counter
	 *
	 * @param $question_id
	 *
	 * @return false|null|string
	 */
	public function register_question_view( $question_id ) {

		return $this->tgedb->register_question_view( $question_id );
	}

	/**
	 * Get question html for frontend
	 *
	 * @param int|null $answer_id
	 *
	 * @return array|false
	 */
	public function get_question_content( $answer_id = null ) {

		if ( empty( $answer_id ) ) {
			$question['data'] = $this->get_quiz_questions( array( 'start' => 1 ), true );
		} else {
			$answer           = $this->get_answers( array( 'id' => $answer_id ), true );
			$question['data'] = $this->get_next_question( $answer );
		}

		if ( empty( $question['data'] ) ) {
			return false;
		}

		$question['answers'] = $this->get_answers( array( 'question_id' => $question['data']['id'] ), false );
		$question['css']     = $this->get_question_css();

		return $question;
	}

	/**
	 * Returns the question CSS File(s)
	 *
	 * @return array
	 */
	public function get_question_css() {
		$quiz_style_meta   = TQB_Post_meta::get_quiz_style_meta( $this->quiz_id );
		$template_css_file = tqb()->get_style_css( $quiz_style_meta );
		$return            = array();

		if ( ! empty( $template_css_file ) ) {
			$font_style_file = str_replace( '.css', '-fonts.css', $template_css_file );
			$font_file       = tqb()->plugin_path( 'tcb-bridge/editor-templates/css/tqb_qna/' . $font_style_file );

			if ( ! tve_dash_is_google_fonts_blocked() && file_exists( $font_file ) ) {
				$return[] = tqb()->plugin_url( 'tcb-bridge/editor-templates/css/tqb_qna/' . $font_style_file );
			}

			$file = tqb()->plugin_path( 'tcb-bridge/editor-templates/css/tqb_qna/' . $template_css_file );
			if ( file_exists( $file ) ) {
				$return[] = tqb()->plugin_url( 'tcb-bridge/editor-templates/css/tqb_qna/' . $template_css_file );
			}
		}

		if ( ! empty( $_REQUEST['tar_editor_page'] ) && ! wp_style_is( 'tqb-shortcode' ) ) {
			//Enqueue this file only in the editor page when viewed from a post -> so external from TQB
			$return[] = tqb()->plugin_url( 'assets/css/frontend/tqb-shortcode.css' );
		}

		return $return;
	}

	/**
	 * Get get next question information
	 *
	 * @param array $answer
	 *
	 * @return array|false
	 */
	public function get_next_question( $answer ) {

		if ( ! is_array( $answer ) ) {
			return false;
		}

		if ( ! empty( $answer['next_question_id'] ) ) {
			return $this->get_quiz_questions( array( 'id' => $answer['next_question_id'] ), true );
		}

		$question = $this->get_quiz_questions( array( 'id' => $answer['question_id'] ), true );
		if ( empty( $question['next_question_id'] ) ) {
			return false;
		}

		return $this->get_quiz_questions( array( 'id' => $question['next_question_id'] ), true );
	}

	/**
	 * Question types
	 *
	 * @return array
	 */
	public static function get_question_types() {
		return array(
			array(
				'id'   => 1,
				'key'  => 'button',
				'name' => __( 'Multiple Choice with Buttons', 'thrive-graph-editor' ),
			),
			array(
				'id'   => 2,
				'key'  => 'image',
				'name' => __( 'Multiple Choice with Images', 'thrive-graph-editor' ),
			),
			array(
				'id'   => 3,
				'key'  => 'open',
				'name' => __( 'Open Ended Question', 'thrive-graph-editor' ),
			),
		);
	}

	/**
	 * Returns the question type name from a type ID
	 *
	 * @param $type_id
	 *
	 * @return false|string
	 */
	public static function get_question_type_name( $type_id ) {
		$types = TGE_Question_Manager::get_question_types();

		foreach ( $types as $type ) {
			if ( $type_id === $type['id'] ) {
				return $type['name'];
			}
		}

		return false;
	}

	/**
	 * Save question
	 *
	 * @param array $question
	 *
	 * @return false|array
	 */
	public function save_question( &$question ) {
		$question_id = null;

		if ( empty( $question['id'] ) ) {
			$question_id = $this->tgedb->save_question( $question );
		} else {
			$question_id = $this->tgedb->save_question( $question ) !== false ? $question['id'] : false;
		}

		/**
		 * question not saved
		 */
		if ( empty( $question_id ) ) {
			return false;
		}

		$question['id'] = $question_id;

		if ( ! empty( $question['answers'] ) ) {

			$old_answers = $this->get_answers( array(
				'question_id' => $question['id'],
			) );

			$answers_to_be_deleted = array();

			foreach ( $old_answers as $old ) {
				$found = false;
				foreach ( $question['answers'] as $new ) {
					if ( ! empty( $old['id'] ) && ! empty( $new['id'] ) && intval( $old['id'] ) === intval( $new['id'] ) ) {
						$found = true;
						break;
					}
				}

				if ( ! $found ) {
					$answers_to_be_deleted[] = $old['id'];
				}
			}

			foreach ( $answers_to_be_deleted as $d ) {
				$this->tgedb->delete_answer( array(
					'id' => $d,
				) );
			}
		}

		if ( ! empty( $question['answers'] ) && is_array( $question['answers'] ) ) {
			foreach ( $question['answers'] as &$answer ) {
				$answer['question_id'] = $question_id;
				$answer['quiz_id']     = $this->quiz_id;
				$this->save_answer( $answer );
			}
		}

		return $question;
	}


	public function delete_question( $id ) {

		$q_deleted = $this->tgedb->delete_question( array(
			'id' => $id,
		) );

		$a_deleted = $this->tgedb->delete_answer( array(
			'question_id' => $id,
		) );

		return $q_deleted && $a_deleted;
	}

	public function save_answer( &$answer ) {

		$answer_id = null;

		if ( empty( $answer['id'] ) ) {
			$answer_id = $this->tgedb->save_answer( $answer );
		} else {
			$answer_id = $this->tgedb->save_answer( $answer ) !== false ? $answer['id'] : false;
		}

		/**
		 * answer not saved
		 */
		if ( empty( $answer_id ) ) {
			return false;
		}

		$answer['id'] = $answer_id;

		return $answer;
	}

	/**
	 * Loop through questions and answers and prefix question ids with 'q' char
	 * and with 'a' answers id
	 *
	 * @param array $questions
	 *
	 * @return array
	 */
	public function prepare_questions( $questions ) {

		foreach ( $questions as &$question ) {
			$this->prepare_question( $question );
		}

		return $questions;
	}

	public function prepare_question( &$question ) {
		$question['id']   = intval( $question['id'] ) . 'q';
		$question['type'] = $question['q_type'] == 1 ? 'tge.Question' : 'tge.Question';

		! empty( $question['next_question_id'] ) ? $question['next_question_id'] = intval( $question['next_question_id'] ) . 'q' : null;
		! empty( $question['previous_question_id'] ) ? $question['previous_question_id'] = intval( $question['previous_question_id'] ) . 'q' : null;

		if ( ! empty( $question['answers'] ) ) {
			foreach ( $question['answers'] as &$answer ) {
				$answer['id'] = intval( $answer['id'] ) . 'a';
			}
		}

		$question['allowed_answers'] = 1;
		if ( ! empty( $question['settings'] ) ) {
			if ( is_object( $question['settings'] ) && ! empty( $question['settings']->allowed_answers ) ) {
				$question['allowed_answers'] = $question['settings']->allowed_answers;
			} elseif ( is_array( $question['settings'] ) && ! empty( $question['settings']['allowed_answers'] ) ) {
				$question['allowed_answers'] = $question['settings']['allowed_answers'];
			}
		} else {
			$question['settings'] = [];
		}

		return $question;
	}

	/**
	 * Deletes all quiz data from the graph editor table
	 *
	 * @param array $filters
	 *
	 * @return bool
	 */
	public function delete_quiz_dependencies( $filters = array() ) {

		$filters = array_merge( $filters, array(
			'quiz_id' => $this->quiz_id,
		) );

		$answer_deleted   = $this->tgedb->delete_answer( $filters );
		$question_deleted = $this->tgedb->delete_question( $filters );

		return $answer_deleted && $question_deleted;
	}

	protected function calculate_paths( $q, $point_sum, $path_key ) {

		$return = array(
			'q'        => $q['id'],
			'points'   => $point_sum,
			'path_key' => $path_key,
			'paths'    => array(),
		);

		foreach ( $q['answers'] as $id => $answer ) {
			$next_question_id = ! empty( $answer['next'] ) ? $answer['next'] : $q['next'];
			if ( $next_question_id && isset( $this->questions[ $next_question_id ] ) ) {
				$return['paths'][ 'answer-' . $id ] = $this->calculate_paths( $this->questions[ $next_question_id ], $point_sum + $answer['points'], $path_key . ':' . 'answer-' . $id );
			} else {
				/* end path */
				$return['paths'][ 'answer-' . $id ]                = array(
					'points' => $point_sum + $answer['points'],
				);
				$this->costs [ $path_key . ':' . 'answer-' . $id ] = $return['paths'][ 'answer-' . $id ]['points'];
			}
		}

		return $return;
	}


	/**
	 * Tests if a question is leaf
	 * (has no question that follows it)
	 *
	 * @param $q
	 *
	 * @return bool
	 */
	protected function is_leaf( $q ) {
		if ( ! empty( $q['next'] ) || empty( $q['answers'] ) ) {
			return false;
		}
		foreach ( $q['answers'] as $id => $answer ) {
			if ( ! empty( $answer['next'] ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Returns the answer points
	 *
	 * @param $a
	 *
	 * @return int
	 */
	protected function get_answer_points( $a ) {
		return intval( $a['points'] );
	}

	/**
	 * @param $q
	 *
	 * @return mixed
	 */
	public function get_min_max( $q ) {
		if ( isset( $this->cache_min_max[ $q['id'] ] ) ) {
			return $this->cache_min_max[ $q['id'] ];
		}
		if ( $this->is_leaf( $q ) ) {
			$points                          = array_map( array( $this, 'get_answer_points' ), $q['answers'] );
			$this->cache_min_max[ $q['id'] ] = [ 'min' => min( $points ), 'max' => $this->get_max_points( $points, $q ) ];

			return $this->cache_min_max[ $q['id'] ];
		}

		$min = PHP_INT_MAX;
		$max = - 1;

		/**
		 * Cycle check!
		 * Questions that end in an infinite loop is not permitted.
		 * Therefore we break the recursion here if a visited node has been found
		 */
		if ( ! empty( $this->questions[ $q['id'] ]['visited'] ) ) {
			return array(
				'min' => 0,
				'max' => 0,
			);
		}
		$this->questions[ $q['id'] ]['visited'] = 1;
		$answer_points                          = array_map( array( $this, 'get_answer_points' ), $q['answers'] );
		$min_points                             = min( $answer_points );
		$max_points                             = $this->get_max_points( $answer_points, $q );
		foreach ( $q['answers'] as $answer ) {
			if ( empty( $answer['next'] ) && empty( $q['next'] ) ) {
				$min_max = array( 'min' => 0, 'max' => 0 );
			} else {
				$next_q = $this->questions[ empty( $answer['next'] ) ? $q['next'] : $answer['next'] ];
				if ( empty( $next_q ) ) {
					$min_max = array( 'min' => 0, 'max' => 0 );
				} else {
					$min_max = $this->get_min_max( $next_q );
				}
			}

			if ( $min_points + $min_max['min'] < $min ) {
				$min = $min_points + $min_max['min'];
			}

			if ( $max_points + $min_max['max'] > $max ) {
				$max = $max_points + $min_max['max'];
			}
		}
		$this->cache_min_max[ $q['id'] ] = array( 'min' => $min, 'max' => $max );

		return $this->cache_min_max[ $q['id'] ];
	}

	/**
	 * Computes the maximum number of points for a question
	 * Takes into consideration if the question has multiple allowed answers or not
	 *
	 * @param array $points
	 * @param array $question
	 *
	 * @return numeric
	 */
	public function get_max_points( $points, $question ) {
		// for right_wrong quiz type we don't take into consideration multiple answers
		if ( $this->quiz_type !== Thrive_Quiz_Builder::QUIZ_TYPE_RIGHT_WRONG && ['allowed_answers'] > 1 && count( $points ) >= $question['allowed_answers'] ) {
			//Sort the points array in descending order. From max to min
			rsort( $points );

			//We slice the ordered array. The new array will have the length of the allowed answers.
			//Afterwards we add all the elements of the array
			return array_sum( array_slice( $points, 0, $question['allowed_answers'] ) );
		}

		return max( $points );
	}


	public function get_costs() {

		if ( ! empty( $this->costs ) ) {
			return $this->costs;
		}

		$questions = $this->get_items();
		$start     = current( $questions );
		$this->calculate_paths( $start, 0, 'root' );

		return $this->costs;
	}

	/**
	 * Gets the min max from a flow
	 *
	 * @return mixed
	 */
	public function get_min_max_flow() {
		$questions = $this->get_items();

		if ( empty( $questions ) || ! is_array( $questions ) ) {
			return array(
				'min' => 0,
				'max' => 0,
			);
		}

		return $this->get_min_max( current( $questions ) );
	}

	/*
	 *TODO: This method is ABSOLITE please check dependencies and replace it with get_min_max method
	 */
	public function get_min_flow() {

		return min( array_values( $this->get_costs() ) );
	}

	/*
	 *TODO: This method is ABSOLITE please check dependencies and replace it with get_min_max method
	 */
	public function get_max_flow() {

		return max( array_values( $this->get_costs() ) );
	}

	/**
	 * Set the answers with $results_ids
	 * with 0 for result_id column
	 *
	 * @param array $results_ids
	 *
	 * @return bool
	 */
	public function set_answers_on_none( $results_ids ) {

		return $this->tgedb->update_answers_result( $results_ids, 0 ) === false;
	}

	public function reset_questions_views() {
		return $this->tgedb->reset_questions_views_by_quiz_id( $this->quiz_id );
	}
}
