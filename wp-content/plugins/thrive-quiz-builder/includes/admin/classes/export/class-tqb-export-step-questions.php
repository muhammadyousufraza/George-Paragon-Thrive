<?php

/**
 * Class TQB_Export_Step_Questions
 * - prepares quiz questions
 */
class TQB_Export_Step_Questions extends TQB_Export_Step_Abstract {

	protected $_name = 'questions';

	/**
	 * Prepares questions and answers to be exported
	 * - copy question/answer images into export folder
	 *
	 * @throws Exception
	 */
	protected function _prepare_data() {

		$data             = array();
		$question_manager = new TGE_Question_Manager( $this->quiz->ID );
		$questions        = $question_manager->get_quiz_questions( array(
			'with_answers' => 1,
		) );

		foreach ( $questions as $question ) {

			$data[ $question['id'] ] = $question;

			/**
			 * Export question images
			 */
			if ( ! empty( $question['image'] ) ) {
				$this->_prepare_file( $question['image'] );
			}

			/**
			 * Export answers images
			 */
			if ( ! empty( $question['answers'] ) && is_array( $question['answers'] ) ) {
				foreach ( $question['answers'] as $answer ) {
					if ( ! empty( $answer['image'] ) ) {
						$this->_prepare_file( $answer['image'] );
					}
				}
			}

			/**
			 * Search for custom video/audio files and export/prepare them
			 */
			$display_type = ! empty( $question['display_settings']->display_type ) ? (int) $question['display_settings']->display_type : 0;
			if ( $display_type > 0 ) { //at least video or audio
				$source = $display_type === 2 ? $question['display_settings']->audio_source : $question['display_settings']->source;
				if ( $source === 'custom' ) {
					$filename   = basename( $question['display_settings']->url );
					$attachment = tqb_get_attachment_by_filename( $filename );

					if ( $attachment ) {
						$item           = new stdClass();
						$item->id       = $attachment->ID;
						$item->filename = $filename;
						$this->_prepare_file( $item );
					}
				}
			}
		}

		$this->data = $data;
	}
}
