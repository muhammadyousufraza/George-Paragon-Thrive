<?php
/**
 * Handles database operations
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 9/22/2016
 * Time: 5:16 PM
 */

global $tgedb;

/**
 * Encapsulates the global $wpdb object
 *
 * Class Tho_Db
 */
class TGE_Database {
	/**
	 * @var $wpdb wpdb
	 */
	protected $wpdb = null;

	/**
	 * class constructor
	 */
	public function __construct() {
		global $wpdb;

		$this->wpdb = $wpdb;
	}

	/**
	 * forward the call to the $wpdb object
	 *
	 * @param $method_name
	 * @param $args
	 *
	 * @return mixed
	 */
	public function __call( $method_name, $args ) {
		return call_user_func_array( array( $this->wpdb, $method_name ), $args );
	}

	/**
	 * unserialize fields from an array
	 *
	 * @param array $array  where to search the fields
	 * @param array $fields fields to be unserialized
	 *
	 * @return array the modified array containing the unserialized fields
	 */
	protected function _unserialize_fields( $array, $fields = array() ) {

		foreach ( $fields as $field ) {
			if ( ! isset( $array[ $field ] ) ) {
				continue;
			}
			/* the serialized fields should be trigger_config and tcb_fields */
			$array[ $field ] = empty( $array[ $field ] ) ? array() : unserialize( $array[ $field ] );
			$array[ $field ] = wp_unslash( $array[ $field ] );

			/* extra checks to ensure we'll have consistency */
			if ( ! is_array( $array[ $field ] ) ) {
				$array[ $field ] = array();
			}
		}

		return $array;
	}

	/**
	 *
	 * replace table names in form of {table_name} with the prefixed version
	 *
	 * @param $sql
	 * @param $params
	 *
	 * @return false|null|string
	 */
	public function prepare( $sql, $params ) {
		$prefix = tge_table_name( '' );
		$sql    = preg_replace( '/\{(.+?)\}/', '`' . $prefix . '$1' . '`', $sql );

		if ( strpos( $sql, '%' ) === false ) {
			return $sql;
		}

		return $this->wpdb->prepare( $sql, $params );
	}

	/**
	 * get quiz questions
	 *
	 * @param array  $filters
	 * @param bool   $single
	 * @param string $return_type
	 *
	 * @return array|null|object
	 */
	public function get_quiz_questions( $filters, $single, $return_type = ARRAY_A ) {
		$params   = array();
		$where    = ' 1=1 ';
		$order_by = ' ORDER by start DESC, id ASC';

		if ( ! empty( $filters['id'] ) ) {
			$params ['id'] = $filters['id'];
			$where         .= 'AND `id`=%d ';
		}

		if ( ! empty( $filters['quiz_id'] ) ) {
			$params ['quiz_id'] = $filters['quiz_id'];
			$where              .= 'AND `quiz_id`=%d ';
		}

		if ( ! empty( $filters['start'] ) ) {
			$where .= 'AND `start`=1 ';
		}

		if ( isset( $filters['previous_question_id'] ) ) {
			$params ['previous_question_id'] = $filters['previous_question_id'];
			$where                           .= 'AND `previous_question_id`=%d ';
		}

		$sql = 'SELECT * FROM ' . tge_table_name( 'questions' ) . ' WHERE ' . $where . $order_by;

		if ( $single ) {
			$model = $this->wpdb->get_row( $this->prepare( $sql, $params ), $return_type );
			if ( ! empty( $model['image'] ) ) {
				$model['image'] = json_decode( $model['image'] ) ? json_decode( $model['image'] ) : $model['image'];
			}

			return $model;
		}

		$models = $this->wpdb->get_results( $this->prepare( $sql, $params ), $return_type );
		foreach ( $models as &$question ) {
			if ( ! empty( $question['image'] ) ) {
				$question['image'] = json_decode( $question['image'] ) ? json_decode( $question['image'] ) : $question['image'];
			}
		}

		return $models;
	}

	/**
	 * @param array $filters
	 *
	 * @return null|string
	 */
	public function count_questions( $filters = array() ) {
		$sql    = 'SELECT COUNT(id) FROM ' . tge_table_name( 'questions' ) . '  WHERE 1 ';
		$params = array();

		if ( ! empty( $filters['quiz_id'] ) ) {
			$sql       .= ' AND quiz_id = %d';
			$params [] = $filters['quiz_id'];
		}

		if ( ! empty( $filters['q_type'] ) && is_array( $filters['q_type'] ) ) {
			$sql .= ' AND q_type IN (' . implode( ',', array_map( static function ( $type ) {
					return (int) $type;
				}, $filters['q_type'] ) ) . ')';
		}

		return $this->wpdb->get_var( $this->prepare( $sql, $params ) );
	}

	/**
	 *
	 * get question answers
	 *
	 * @param $filters
	 *
	 * @return false|null|string
	 */
	public function get_answers( $filters, $single, $return_type = ARRAY_A ) {
		$params = array();
		$where  = ' 1=1 ';

		if ( ! empty( $filters['id'] ) ) {
			$params ['id'] = $filters['id'];
			$where         .= 'AND `id`=%d ';
		}

		if ( ! empty( $filters['question_id'] ) ) {
			$params ['question_id'] = $filters['question_id'];
			$where                  .= 'AND `question_id`=%d ';
		}

		if ( ! empty( $filters['quiz_id'] ) ) {
			$params ['quiz_id'] = $filters['quiz_id'];
			$where              .= 'AND `quiz_id`=%d ';
		}

		$sql = 'SELECT * FROM ' . tge_table_name( 'answers' ) . ' WHERE ' . $where . ' ORDER BY `order` ASC';

		if ( $single ) {
			$model = $this->wpdb->get_row( $this->prepare( $sql, $params ), $return_type );

			if ( ! empty( $model['image'] ) ) {
				$model['image'] = json_decode( $model['image'] ) ? json_decode( $model['image'] ) : $model['image'];
			}

			return $model;

		}

		$models = $this->wpdb->get_results( $this->prepare( $sql, $params ), $return_type );
		foreach ( $models as &$answer ) {
			if ( ! empty( $answer['image'] ) ) {
				$answer['image'] = json_decode( $answer['image'] ) ? json_decode( $answer['image'] ) : $answer['image'];
			}
		}

		return $models;
	}

	/**
	 * Insert or Update question in DB
	 *
	 * @param array $data
	 *
	 * @return false|int
	 */
	public function save_question( $data ) {

		$model   = array();
		$columns = array(
			'id',
			'quiz_id',
			'start',
			'q_type',
			'text',
			'image',
			'description',
			'settings',
			'display_settings',
			'next_question_id',
			'previous_question_id',
			'position',
		);

		$sanitize = array(
			'text',
			'description',
		);

		/**
		 * filter the data accordingly to $columns
		 */
		foreach ( $data as $key => $value ) {
			if ( in_array( $key, $columns ) ) {
				$model[ $key ] = in_array( $key, $sanitize ) ? sanitize_text_field( $value ) : $value;
			}
		}

		if ( ! empty( $model['position'] ) && is_array( $model['position'] ) ) {
			$position['x']     = ! empty( $model['position']['x'] ) ? $model['position']['x'] : 0;
			$position['y']     = ! empty( $model['position']['y'] ) ? $model['position']['y'] : 0;
			$model['position'] = wp_json_encode( $position );
		}

		if ( ! empty( $model['image'] ) && is_array( $model['image'] ) ) {
			$model['image'] = wp_json_encode( $model['image'] );
		}

		if ( ! empty( $model['settings'] ) && ! is_string( $model['settings'] ) ) {
			$model['settings'] = wp_json_encode( $model['settings'] );
		}

		if ( ! empty( $model['display_settings'] ) && ! is_string( $model['display_settings'] ) ) {
			$model['display_settings']                 = (array) $model['display_settings'];
			$model['display_settings']['new_question'] = 0;

			$this->save_display_settings( $model );

			$model['display_settings'] = wp_json_encode( $model['display_settings'] );
		}

		if ( ! empty( $model['id'] ) ) {
			return $this->wpdb->update( tge_table_name( 'questions' ), $model, array( 'id' => $model['id'] ) );
		}

		/**
		 * Some mysql servers will fail if no value is send for AUTOINCREMENT key
		 */
		unset( $model['id'] );

		return $this->wpdb->insert( tge_table_name( 'questions' ), $model ) !== false ? $this->wpdb->insert_id : false;
	}

	/**
	 * Save quiz display settings
	 *
	 * @param array $model
	 */
	public function save_display_settings( $model ) {

		if ( ! is_array( $model ) ) {
			return;
		}

		$type = ! empty( $model['display_settings']['display_type'] ) ? (int) $model['display_settings']['display_type'] : null;

		switch ( $type ) {

			case 1:
				$this->save_quiz_video_style( $model );
				$this->save_quiz_video_options( $model );

				break;

			case 2:
				$this->save_quiz_audio_options( $model );
				break;

			default:
				break;
		}
	}

	/**
	 * Save quiz audio options
	 *
	 * @param array $model
	 *
	 * @return bool|int
	 */
	public function save_quiz_audio_options( $model ) {

		if (
			empty( $model['display_settings']['source_audio_labels'] )
			|| empty( $model['display_settings']['audio_source'] )
			|| ! array_key_exists( $model['display_settings']['audio_source'], (array) $model['display_settings']['source_audio_labels'] )
		) {
			return false;
		}

		$return = false;

		if ( is_array( $model['display_settings']['options'] ) && ! empty( $model['display_settings']['options'] ) ) {

			$meta         = array();
			$last_options = $model['display_settings']['options'];
			$source       = ! empty( $model['display_settings']['audio_source'] ) ? $model['display_settings']['audio_source'] : 'custom';

			$meta[ $source ] = $last_options;

			$quiz_id = (int) $model['quiz_id'];

			if ( ! empty( $quiz_id ) ) {
				$existing_meta = (array) get_post_meta( $model['quiz_id'], TQB_Post_meta::META_NAME_FOR_QUIZ_AUDIO_OPTIONS, true );

				// Save and return if no options saved already
				if ( ! $existing_meta ) {
					return update_post_meta( $model['quiz_id'], TQB_Post_meta::META_NAME_FOR_QUIZ_AUDIO_OPTIONS, $meta );
				}

				// Update options by source player
				$existing_meta[ $source ] = $meta[ $source ];

				$return = update_post_meta( $model['quiz_id'], TQB_Post_meta::META_NAME_FOR_QUIZ_AUDIO_OPTIONS, $existing_meta );
			}
		}

		return $return;
	}

	/**
	 * Save post meta data [save video style at quiz lvl]
	 *
	 * @param $model
	 *
	 * @return bool
	 */
	public function save_quiz_video_style( $model ) {

		if ( empty( $model ) || ! is_array( $model ) ) {
			return false;
		}

		if ( is_array( $model['display_settings']['video_style'] ) && isset( $model['display_settings']['video_style']['selected'] ) ) {

			$last_quiz_video_style = $model['display_settings']['video_style']['selected'];

			$quiz_id = (int) $model['quiz_id'];

			if ( ! empty( $quiz_id ) ) {
				return update_post_meta( $model['quiz_id'], TQB_Post_meta::META_NAME_FOR_QUIZ_VIDEO_STYLE, $last_quiz_video_style );
			}
		}

		return false;
	}

	public function save_quiz_video_options( $model ) {

		$return = false;

		if ( empty( $model ) || ! is_array( $model ) ) {
			return $return;
		}

		if ( is_array( $model['display_settings']['options'] ) && ! empty( $model['display_settings']['options'] ) ) {

			$meta                    = array();
			$last_quiz_video_options = $model['display_settings']['options'];
			$source                  = ! empty( $model['display_settings']['source'] ) ? $model['display_settings']['source'] : 'youtube';

			$meta[ $source ] = $last_quiz_video_options;

			$quiz_id = (int) $model['quiz_id'];

			if ( ! empty( $quiz_id ) ) {
				$existing_meta = (array) get_post_meta( $model['quiz_id'], TQB_Post_meta::META_NAME_FOR_QUIZ_VIDEO_OPTIONS, true );

				// Save and return if no options saved already
				if ( ! $existing_meta ) {
					return update_post_meta( $model['quiz_id'], TQB_Post_meta::META_NAME_FOR_QUIZ_VIDEO_OPTIONS, ( $meta ) );
				}

				// Update options by source player
				$existing_meta[ $source ] = $meta[ $source ];

				$return = update_post_meta( $model['quiz_id'], TQB_Post_meta::META_NAME_FOR_QUIZ_VIDEO_OPTIONS, ( $existing_meta ) );
			}
		}

		return $return;
	}

	/**
	 * Insert or Update an answer in DB
	 *
	 * @param array $data
	 *
	 * @return false|int
	 */
	public function save_answer( $data ) {

		$model   = array();
		$columns = array(
			'id',
			'quiz_id',
			'question_id',
			'next_question_id',
			'order',
			'text',
			'image',
			'points',
			'is_right',
			'tags',
			'result_id',
			'feedback',
		);

		$sanitize = array(
			'text',
			'feedback',
		);

		/**
		 * filter the data accordingly to $columns
		 */
		foreach ( $data as $key => $value ) {
			if ( in_array( $key, $columns ) ) {
				$model[ $key ] = in_array( $key, $sanitize ) ? sanitize_textarea_field( $value ) : $value;
			}
		}

		if ( ! empty( $model['image'] ) && is_array( $model['image'] ) ) {
			$model['image'] = wp_json_encode( $model['image'] );
		}

		if ( ! empty( $model['id'] ) ) {
			return $this->wpdb->update( tge_table_name( 'answers' ), $model, array( 'id' => $model['id'] ) );
		}

		return $this->wpdb->insert( tge_table_name( 'answers' ), $model ) !== false ? $this->wpdb->insert_id : false;
	}

	/**
	 * Deletes from question table based on a given filter
	 *
	 * @param array $filters
	 *
	 * @return bool|false|int
	 */
	public function delete_question( $filters = array() ) {

		if ( ! empty( $filters ) ) {
			return $this->wpdb->delete( tge_table_name( 'questions' ), $filters );
		}

		return false;
	}

	/**
	 * Deletes from answer table based on a given filter
	 *
	 * @param array $filters
	 *
	 * @return false|int
	 */
	public function delete_answer( $filters = array() ) {

		if ( ! empty( $filters ) ) {
			return $this->wpdb->delete( tge_table_name( 'answers' ), $filters );
		}

		return false;
	}

	/**
	 * Update all answers that have assigned $results_ids
	 * with new $value for result_id column
	 *
	 * @param array    $results_ids
	 * @param null|int $value
	 *
	 * @return int|false
	 */
	public function update_answers_result( $results_ids, $value ) {

		if ( ! is_int( $value ) ) {
			$value = 'NULL';
		}

		$ids = implode( ',', $results_ids );
		$sql = 'UPDATE ' . tge_table_name( 'answers' ) . ' SET `result_id` = ' . $value . ' WHERE result_id IN (' . $ids . ')';

		return $this->wpdb->query( $sql );
	}


	/**
	 * Increase question view counter
	 *
	 * @param $question_id
	 *
	 * @return false|array
	 */
	public function register_question_view( $question_id ) {

		$sql = 'UPDATE ' . tge_table_name( 'questions' ) . ' SET `views` = `views` + 1 WHERE id = ' . $question_id;

		return $this->wpdb->query( $sql );
	}

	public function reset_questions_views_by_quiz_id( $quiz_id ) {

		return $this->wpdb->update( tge_table_name( 'questions' ), array( 'views' => 0 ), array( 'quiz_id' => $quiz_id ) );
	}

	/**
	 * Get the question ids where there's a meeting point between two branches of the quiz
	 *
	 * @param $quiz_id
	 *
	 * @return array of question ids where multiple branches meet
	 */
	public function get_question_ids_where_branches_meet( $quiz_id ) {
		$params = [];
		array_push( $params, $quiz_id, $quiz_id );

		$select_answers = 'SELECT * FROM ' . tge_table_name( 'answers' ) . ' WHERE quiz_id = %d GROUP by next_question_id, question_id';

		$sql = "SELECT CONCAT_WS( '', a.next_question_id, q.next_question_id ) AS next_q_id FROM ";
		$sql .= '(' . $select_answers . ')' . ' AS a JOIN ' . tge_table_name( 'questions' ) . ' AS q ON a.quiz_id = q.quiz_id AND q.id = a.question_id ';
		$sql .= "WHERE a.quiz_id = %d GROUP BY next_q_id HAVING COUNT(a.id) > 1 AND TRIM(next_q_id) <> ''";

		return $this->wpdb->get_col( $this->prepare( $sql, $params ) );
	}
}

$tgedb = new TGE_Database();
