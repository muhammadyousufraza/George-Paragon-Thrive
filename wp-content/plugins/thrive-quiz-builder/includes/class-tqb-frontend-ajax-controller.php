<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

/**
 * Class TQB_Frontend_Ajax_Controller
 *
 * Ajax controller to handle frontend ajax requests
 * Specially built for backbone models
 */
class TQB_Frontend_Ajax_Controller {

	/**
	 * @var TQB_Frontend_Ajax_Controller $instance
	 */
	protected static $instance;

	/**
	 * TQB_Frontend_Ajax_Controller constructor.
	 * Protected constructor because we want to use it as singleton
	 */
	protected function __construct() {
	}

	/**
	 * Gets the SingleTone's instance
	 *
	 * @return TQB_Frontend_Ajax_Controller
	 */
	public static function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new TQB_Frontend_Ajax_Controller();
		}

		return self::$instance;
	}

	/**
	 * Sets the request's header with server protocol and status
	 * Sets the request's body with specified $message
	 *
	 * @param string $message the error message.
	 * @param string $status  the error status.
	 */
	protected function error( $message, $status = '404 Not Found' ) {
		header( $_SERVER['SERVER_PROTOCOL'] . ' ' . $status ); // phpcs:ignore
		echo esc_attr( $message );
		wp_die();
	}

	/**
	 * Returns the params from $_POST or $_REQUEST
	 *
	 * @param int  $key     the parameter kew.
	 * @param null $default the default value.
	 *
	 * @return mixed|null|$default
	 */
	protected function param( $key, $default = null ) {
		if ( isset( $_POST[ $key ] ) ) {
			$value = $_POST[ $key ]; //phpcs:ignore
		} else {
			$value = isset( $_REQUEST[ $key ] ) ? $_REQUEST[ $key ] : $default; //phpcs:ignore
		}

		return map_deep( $value, 'sanitize_text_field' );
	}

	/**
	 * Entry-point for each ajax request
	 * This should dispatch the request to the appropriate method based on the "route" parameter
	 *
	 * @return array|object
	 */
	public function handle() {

		$route = $this->param( 'route' );

		$route       = preg_replace( '#([^a-zA-Z0-9-])#', '', $route );
		$method_name = $route . '_action';

		if ( ! method_exists( $this, $method_name ) ) {
			$this->error( sprintf( __( 'Method %s not implemented', 'thrive-quiz-builder' ), $method_name ) );
		}

		return $this->{$method_name}();
	}

	/**
	 * Performs actions for Quiz based on request's method and model
	 * Dies with error if the operation was not executed
	 *
	 * @return mixed
	 */
	protected function shortcode_action() {
		$method = empty( $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ) ? 'GET' : sanitize_text_field( $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] );

		$custom = $this->param( 'custom_action' );
		if ( ! empty( $custom ) ) {
			if ( $custom === 'log_social_share_conversion' ) {
				do_action( 'tqb_register_social_media_conversion', $_POST ); // phpcs:ignore

				return true;
			} elseif ( $custom === 'save_user_custom_social_share_badge' ) {

				if ( ! isset( $_FILES['user_badge'] ) ) {
					$this->error( __( 'Share Badge not available', 'thrive-quiz-builder' ) );
				}

				if ( ! class_exists( 'TQB_Badge' ) ) {
					require_once dirname( __FILE__ ) . '/class-tqb-badge.php';
				}

				$result  = $this->param( 'result' );
				$quiz_id = $this->param( 'quiz_id' );

				$badge = new TQB_Badge( $result, $quiz_id );
				$url   = $badge->save( $_FILES['user_badge'] ); // phpcs:ignore

				if ( empty( $url ) ) {
					$this->error( __( 'Badge could not be generated', 'thrive-quiz-builder' ) );
				}

				do_action( 'tqb_generate_user_social_badge_link', ! empty( $_REQUEST['user_id'] ) ? sanitize_text_field( $_REQUEST['user_id'] ) : '', $url );

				return $url . '?r=' . rand();
			} elseif ( $custom === 'register_question_answer' ) {
				$answer_ids  = $this->param( 'answer_ids' );
				$user_unique = $this->param( 'user_unique' );
				$quiz_id     = $this->param( 'quiz_id' );
				$answer_text = $this->param( 'answer_text' );
				$post_id     = $this->param( 'tqb-post-id' );
				$answer_text = sanitize_textarea_field( $answer_text );

				if ( empty( $answer_ids ) || empty( $user_unique ) || empty( $quiz_id ) ) {
					return false;
				}

				//Store TQB User inside the DB, if it doesn't exist for computing the answers
				$quiz_user_id = TQB_Quiz_Manager::get_quiz_user( $user_unique, $quiz_id, false, array(
					'object_id' => $post_id,
				) );

				$feedback_settings = get_post_meta( $quiz_id, 'tqb_quiz_feedback_settings', true );

				//if quiz navigation was enabled then we might get the same or different answers to an already answered question
				if ( ! empty( $feedback_settings['nav_buttons'] ) ) {

					TQB_Quiz_Manager::handle_answer_already_saved( $answer_ids, $quiz_user_id, $quiz_id );
				}

				foreach ( $answer_ids as $answer_id ) {
					TQB_Quiz_Manager::register_answer( $answer_id, $user_unique, $quiz_id, $answer_text );
				}

				$shortcode_content = array();
				$question_manager  = new TGE_Question_Manager( $quiz_id );

				$shortcode_content['question']            = $question_manager->get_question_content( reset( $answer_ids ) );
				$shortcode_content['question']['page_id'] = $quiz_id;
				$shortcode_content['question']['quiz_id'] = $quiz_id;

				if ( ! empty( $shortcode_content['question']['data']['id'] ) ) {
					$question_manager->register_question_view( $shortcode_content['question']['data']['id'] );
				}

				do_action( 'tqb_register_impression', $shortcode_content['question'], $user_unique );

				if ( ! empty( $feedback_settings['nav_buttons'] ) ) {
					return ( new TQB_Customer( $quiz_user_id ) )->get_resume_quiz_user_answers( $quiz_id, $post_id );
				}

				return true;
			}
		}

		switch ( $method ) {
			case 'POST':
				break;
			case 'PUT':
			case 'PATCH':
				break;
			case 'DELETE':
				break;
			case 'GET':
				$quiz_id         = $this->param( 'quiz_id' );
				$page_type       = $this->param( 'page_type' );
				$answers         = $this->param( 'answer_ids' );
				$user_unique     = $this->param( 'user_unique' );
				$variation       = $this->param( 'variation' );
				$post_id         = $this->param( 'tqb-post-id' );
				$in_tcb_editor   = $this->param( 'tqb_in_tcb_editor', null );
				$data            = TQB_Quiz_Manager::get_shortcode_content( $quiz_id, $page_type, $answers, $user_unique, $variation, $post_id );
				$data['quiz_id'] = $quiz_id;

				$quiz_style = TQB_Post_meta::get_quiz_style_meta( $quiz_id );
				$qna        = get_post_meta( $quiz_id, 'tve_qna_templates', true );

				if ( empty( $data ) ) {
					$this->error( __( 'You have nothing', 'thrive-quiz-builder' ) );
				}

				if ( $in_tcb_editor === 'inside_tcb' && ( empty( $qna[ $quiz_style ] ) || $data['page'] ) ) {
					return tqb_render_shortcode( array( 'quiz_id' => $quiz_id, 'in_tcb_editor' => $in_tcb_editor ) );
				}

				if ( ! empty( $qna[ $quiz_style ] ) ) {
					$data['qna_templates']    = $qna[ $quiz_style ];
					$data['qna_html']         = tqb_render_shortcode( array(
						'quiz_id'       => $quiz_id,
						'in_tcb_editor' => $in_tcb_editor,
						'qna'           => true,
					) );
					$tve_custom_css           = tve_get_post_meta( $quiz_id, 'tve_custom_css', true );
					$tve_custom_css           = tve_prepare_global_variables_for_front( $tve_custom_css );
					$data['tve_custom_style'] = $tve_custom_css;
					$data['quiz_type']        = TQB_Post_meta::get_quiz_type_meta( $quiz_id, true );

					if ( is_editor_page() || ( defined( 'DOING_AJAX' ) && DOING_AJAX && ! empty( $_REQUEST['tqb_in_tcb_editor'] ) ) ) {
						$question_manager = new TGE_Question_Manager( $quiz_id );

						$data['question']['data']['media'] = $question_manager->tqb_build_media_display( $data['question'] );
					}
				}

				/**
				 * Returned value that is sent to front-end
				 *
				 * @param array $data
				 * @param int   $post_id
				 */
				return apply_filters( 'tqb_quiz_shortcode_action_response', $data, $post_id );
				break;
		}
	}

	/**
	 * Handles shortcode render on page content in TAR
	 *
	 * @return array
	 */
	protected function rendershortcode_action() {

		$quizzes_id = isset( $_POST['quizzes'] ) ? array_map( 'sanitize_text_field', $_POST['quizzes'] ) : array();
		$result     = array();

		foreach ( $quizzes_id as $id ) {
			$quiz_style     = TQB_Post_meta::get_quiz_style_meta( $id );
			$qna            = get_post_meta( $id, 'tve_qna_templates', true );
			$tve_custom_css = tve_get_post_meta( $id, 'tve_custom_css', true );
			$tve_custom_css = tve_prepare_global_variables_for_front( $tve_custom_css );
			$style          = TQB_Post_meta::get_quiz_style_meta( $id );
			$data           = TQB_Quiz_Manager::get_shortcode_content( $id );

			$data['quiz_id']  = $id;
			$data['html']     = '';
			$question_manager = new TGE_Question_Manager( $id );

			$part = '';
			if ( $data['page'] ) {
				$part .= str_replace( array( 'tve_empty_dropzone', 'tve_editor_main_content' ), '', $data['page']['html'] );
				foreach ( $data['page']['css'] as $css ) {
					$part .= '<link rel="stylesheet" type="text/css" href="' . $css . '">';
				}
				if ( ! tve_dash_is_google_fonts_blocked() ) {
					foreach ( $data['page']['fonts'] as $font ) {
						$part .= '<link rel="stylesheet" type="text/css" media="all" href="' . $font . '">';
					}
				}

				$data['html'] = $part;
			} else if ( ! empty( $qna[ $quiz_style ] ) ) {
				$data['qna_templates'] = $qna[ $quiz_style ];
				$data['quiz_style']    = $style;

				foreach ( $data['question']['css'] as $css ) {
					$data['html'] .= '<link rel="stylesheet" type="text/css" href="' . $css . '" media="all">';
				}
			} elseif ( $data['question'] ) {
				$part .= $question_manager->get_first_question_preview( $data['question'], $id );

				$data['html'] = $part;
			}

			$data['tve_custom_style']          = $tve_custom_css;
			$data['quiz_type']                 = TQB_Post_meta::get_quiz_type_meta( $id, true );
			$data['question']['data']['media'] = $question_manager->tqb_build_media_display( $data['question'] );

			$result[ $id ] = $data;
		}

		return $result;
	}

	/**
	 * @return mixed|void
	 */
	protected function getquizdata_action() {

		return tqb()->tqb_frontend_ajax_load();
	}
}
