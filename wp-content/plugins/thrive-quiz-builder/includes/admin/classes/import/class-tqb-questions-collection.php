<?php

/**
 * Class TQB_Questions_Collection
 * - help for looping through a questions collection
 * - and import then recursively
 * - with all their images attached
 */
class TQB_Questions_Collection {

	/**
	 * @var array questions to be imported
	 */
	private $_stack;

	/**
	 * List of question ids as index
	 * and new question id as value for questions
	 * which are already visited
	 *
	 * @var array
	 */
	private $_visited = array();

	/**
	 * @var string where the import file exists
	 */
	private $_import_path;

	/**
	 * Map for quiz results/categories
	 * - key is the old id
	 * - value is the new id
	 *
	 * @var array
	 */
	private $_results_map = array();

	/**
	 * @var int fallback value 1
	 */
	private $_quiz_id = 1;

	/** @var TGE_Question_Manager */
	private $manager;

	/**
	 * TQB_Questions_Collection constructor.
	 *
	 * @param array $data
	 */
	public function __construct( $data ) {

		$this->_stack  = (array) $data;
		$this->manager = new TGE_Question_Manager( $this->_quiz_id );
	}

	/**
	 * Where to read import files from
	 *
	 * @param string $path
	 */
	public function set_import_path( $path ) {

		$this->_import_path = $path;
	}

	/**
	 * Results map data
	 *
	 * @param array $data
	 */
	public function set_results_map( $data ) {

		$this->_results_map = $data;
	}

	/**
	 * Main entry point into questions loop
	 *
	 * @param int $quiz_id to attach questions to
	 *
	 * @return bool
	 */
	public function import( $quiz_id ) {

		if ( empty( $this->_import_path ) ) {
			return false;
		}

		$this->_quiz_id = (int) $quiz_id;
		$start_question = current( $this->_stack );//this should be the start question

		$id = $this->_save_question( $start_question );

		return is_int( $id );
	}

	/**
	 * Saves question into DB
	 * - loops through answers and save them too
	 * - recursive method
	 *
	 * @param array $question model
	 *
	 * @return int
	 */
	protected function _save_question( $question ) {

		if ( ! empty( $this->_visited[ $question['id'] ] ) ) {
			//this means the question has already been visited from another branch
			//and we dont need to save it again
			return $this->_visited[ $question['id'] ];
		}

		$answers    = $question['answers'];
		$current_id = $question['id'];
		unset( $question['answers'], $question['id'] );
		$question['quiz_id'] = $this->_quiz_id;

		/**
		 * import question image before saving it
		 */
		if ( ! empty( $question['image'] ) ) {
			$question['image'] = $this->_import_file( $question['image'] );
		}

		/**
		 * if we have to import a media file for the new question
		 */
		if ( $this->_get_question_source( $question ) === 'custom' && ! empty( $question['display_settings']['url'] ) ) {
			$media = $this->_import_file(
				array(
					'filename' => basename( $question['display_settings']['url'] ),
				)
			);

			$question['display_settings']['url'] = ! empty( $media['url'] ) ? $media['url'] : $question['display_settings']['url'];
		}

		$this->manager->save_question( $question );

		$new_id = $question['id'];

		$this->_visited[ $current_id ] = $new_id;

		/**
		 * save answers to newly added question
		 */
		$this->_save_answers( $answers, $new_id );

		/**
		 * If the question has a next question id
		 * then save that question and get its id to be saved on current question
		 */
		if ( $question['next_question_id'] ) {
			$next_question                         = $this->_stack[ $question['next_question_id'] ];
			$next_question['previous_question_id'] = $new_id;
			/**
			 * Updates the current question with the new next question id
			 */
			$question['next_question_id'] = $this->_save_question( $next_question );
			$this->manager->save_question( $question );
		}

		return $new_id;
	}

	/**
	 * Loops through answers array and save them
	 * - attach the answers to $question_id
	 *
	 * @param array $answers
	 * @param int   $question_id
	 */
	protected function _save_answers( $answers, $question_id ) {

		foreach ( $answers as $answer ) {
			$answer['question_id']      = $question_id;
			$answer['next_question_id'] = $answer['next_question_id'] ? $this->_save_question( $this->_stack[ $answer['next_question_id'] ] ) : null;

			/**
			 * Set Answer Category from Result Map
			 */
			if ( ! empty( $this->_results_map[ $answer['result_id'] ] ) ) {
				$answer['result_id'] = $this->_results_map[ $answer['result_id'] ];
			}

			$this->_save_answer( $answer );
		}
	}

	/**
	 * Saves the answer model into DB
	 * - if the model has image property then try to import that image
	 *
	 * @param array $model
	 */
	protected function _save_answer( $model ) {

		$model['quiz_id'] = $this->_quiz_id;
		unset( $model['id'] );

		if ( ! empty( $model['image'] ) ) {
			$model['image'] = $this->_import_file( $model['image'] );
		}

		$this->manager->save_answer( $model );
	}

	/**
	 * Check if the file is already in Media Library
	 * - if it exists then return it
	 * - if not upload the image from /import folder to wp uploads and insert new media post
	 *
	 * @param array $file
	 *
	 * @return array|void wp_prepare_attachment_for_js()
	 */
	protected function _import_file( $file ) {

		if ( empty( $file['filename'] ) ) {
			return;
		}

		$filepath   = trailingslashit( $this->_import_path ) . $file['filename'];
		$attachment = tqb_import_file( $filepath );

		/**
		 * Fix for a weird bug where wp does not generate thumbnails for very small images and TQB relies on thumbnail in js
		 */
		if ( is_array( $attachment ) && ! isset( $attachment['sizes']['thumbnail'] ) ) {
			$attachment['sizes']['thumbnail'] = isset( $attachment['sizes']['full'] ) ? $attachment['sizes']['full'] : '';
		}

		return $attachment;
	}

	/**
	 * Checks in question model if some settings are set and return display_type
	 *
	 * @param array $question
	 *
	 * @return int
	 */
	private function _get_question_display_type( $question ) {

		$type = 0;

		if ( ! empty( $question['display_settings']['display_type'] ) ) {
			$type = (int) $question['display_settings']['display_type'];
		}

		return $type;
	}

	/**
	 * Gets the source for a question: youtube/custom/spotify/etc
	 *
	 * @param $question
	 *
	 * @return string
	 */
	private function _get_question_source( $question ) {

		$source = ! empty( $question['display_settings']['source'] ) ? $question['display_settings']['source'] : '';

		if ( $this->_get_question_display_type( $question ) === 2 ) {
			$source = ! empty( $question['display_settings']['audio_source'] ) ? $question['display_settings']['audio_source'] : '';
		}

		return $source;
	}
}
