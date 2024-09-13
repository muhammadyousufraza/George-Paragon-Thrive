<?php

/**
 * Class TQB_Structure_Page
 * A page of a Quiz Structure: Splash|Optin|Result
 *
 * @property int minimum_result
 * @property int maximum_result
 */
class TQB_Results_Page extends TQB_Structure_Page {

	/**
	 * Types of Result Page
	 *
	 * @var array
	 */
	protected $_accepted_types
		= array(
			'url',
			'page',
		);

	/**
	 * Holds allowed columns to be inserted in results_links
	 *
	 * @var array
	 */
	private $_allowed_fields = [
		'type',
		'lower_bound',
		'upper_bound',
		'quiz_id',
		'page_id',
		'post_id',
		'result_id',
		'date_added',
		'date_modified',
		'status',
		'link',
	];

	/**
	 * Set defaults metas for current post
	 *
	 * @return bool
	 */
	public function save_default_metas() {

		if ( parent::save_default_metas() ) {

			$this->set_type( 'page' );

			return true;
		}

		return false;
	}

	/**
	 * Set type of result page
	 *
	 * @param string $type
	 *
	 * @return bool
	 */
	public function set_type( $type ) {

		if ( ! in_array( $type, $this->_accepted_types ) ) {
			return false;
		}

		update_post_meta( $this->_post->ID, 'tqb_results_type', $type );

		return true;
	}

	public function to_json() {

		$post = parent::to_json();

		if ( $post instanceof WP_Post ) {

			/**
			 * read meta
			 */
			$type = $post->tqb_results_type;

			if ( empty( $type ) ) {
				$type = 'page';
				$this->set_type( $type );
			}

			$this->_post->type = $type;

			if ( ! empty( $post->post_parent ) ) {
				$quiz_post       = get_post( $post->post_parent );
				$post->quiz_name = $quiz_post->post_title;
				$post->quiz_type = TQB_Post_meta::get_quiz_type_meta( $post->post_parent, true );
				$post->results   = array();
				if ( $post->quiz_type === Thrive_Quiz_Builder::QUIZ_TYPE_PERSONALITY ) {
					$quiz_manager  = new TQB_Quiz_Manager( $post->post_parent );
					$post->results = $quiz_manager->get_results();
				}
			}

			$post->links = $this->get_links();

			$post->message         = get_post_meta( $this->_post->ID, 'tqb_redirect_message', true );
			$post->display_message = (int) get_post_meta( $this->_post->ID, 'tqb_redirect_display_message', true );
			$post->forward_results = (int) get_post_meta( $this->_post->ID, 'tqb_redirect_forward_results', true );
		}

		return $post;
	}

	/**
	 * Based on results page types returns an array to be localized and used in JS views
	 *
	 * @return array
	 */
	public static function localize_types() {

		return array(
			'page' => array(
				'label' => __( 'Results Page', 'thrive-quiz-builder' ),
				'value' => 'page',
			),
			'url'  => array(
				'label' => __( 'URL Redirect', 'thrive-quiz-builder' ),
				'value' => 'url',
			),
		);
	}

	/**
	 * @return stdClass[]
	 */
	public function get_links() {

		/** @var $wpdb wpdb */
		global $wpdb;

		$sql   = 'SELECT * FROM ' . tqb_table_name( 'results_links' ) . ' WHERE page_id = %d ORDER BY lower_bound, upper_bound';
		$sql   = $wpdb->prepare( $sql, array( $this->_post->ID ) );
		$links = $wpdb->get_results( $sql );

		$quiz_manager = new TQB_Quiz_Manager( $this->_post->post_parent );
		$quiz_results = $quiz_manager->get_results();

		if ( ! empty( $links ) ) {

			foreach ( $links as $link ) {

				$link->lower_bound = (int) $link->lower_bound;
				$link->upper_bound = (int) $link->upper_bound;

				if ( $link->type === 'local' ) {
					$post             = get_post( $link->post_id );
					$link->post_title = $post instanceof WP_Post ? $post->post_title : '';
					$link->link       = $post instanceof WP_Post ? get_permalink( $link->post_id ) : '';
				}

				/**
				 * Assign a result name to link to be rendered with js logic
				 */
				if ( ! empty( $link->result_id ) ) {
					$link->result_name = call_user_func( function ( $link ) use ( $quiz_results ) {
						foreach ( $quiz_results as $result ) {
							if ( $result['id'] === $link->result_id ) {
								return $result['text'];
							}
						}

						return '';
					}, $link );
				}
			}
		}

		return $links;
	}

	public function get_link_by_id( $id ) {

		/** @var $wpdb wpdb */
		global $wpdb;

		$table_name = tqb_table_name( 'results_links' );
		$sql        = $wpdb->prepare( 'SELECT * FROM ' . $table_name . ' WHERE id = %d', array( $id ) );
		$item       = $wpdb->get_row( $sql );

		if ( $item && $item->type === 'local' ) {
			$post             = get_post( $item->post_id );
			$item->link       = $post instanceof WP_Post ? get_permalink( $post->ID ) : '';
			$item->post_title = $post instanceof WP_Post ? $post->post_title : '';
		}

		$item->lower_bound = (int) $item->lower_bound;
		$item->upper_bound = (int) $item->upper_bound;

		return $item;
	}

	/**
	 * This was added because other plugins may insert stuff into our ajax request in which case we cannot save what we get from the browser as it is
	 * Ex: SUPP-14484
	 *
	 * Maintenance: This needs to be up to date with the tqb_results_links table columns
	 *
	 * @param $model
	 *
	 * @return array
	 */
	protected function prepare_link_for_db( $model ) {
		if ( ! empty( $model['id'] ) ) {
			$link['id'] = (int) $model['id'];
		}
		if ( ! empty( $model['quiz_id'] ) ) {
			$link['quiz_id'] = (int) $model['quiz_id'];
		}
		if ( ! empty( $model['page_id'] ) ) {
			$link['page_id'] = (int) $model['page_id'];
		}

		$link['status']        = $model['status'] ?: 'valid';
		$link['lower_bound']   = (int) $model['lower_bound'] ?: 0;
		$link['upper_bound']   = (int) $model['upper_bound'] ?: 0;
		$link['type']          = $model['type'] ?: 'local';
		$link['post_id']       = (int) $model['post_id'] ?: null;
		$link['link']          = $model['link'] ?: null;
		$link['result_id']     = (int) $model['result_id'] ?: null;
		$link['date_added']    = $model['date_added'] ?: '0000-00-00 00:00:00';
		$link['date_modified'] = $model['date_modified'] ?: '0000-00-00 00:00:00';

		return $link;
	}

	public function save_link( $link ) {

		/** @var $wpdb wpdb */
		global $wpdb;

		$table_name = tqb_table_name( 'results_links' );

		if ( ! empty( $link['link'] ) && strpos( $link['link'], 'htt' ) === false ) {
			$link['link'] = 'http://' . $link['link'];
		}

		if ( ! empty( $link['link'] ) ) {
			$link['link'] = wp_sanitize_redirect( $link['link'] );
		}

		$link = $this->prepare_link_for_db( $link );

		if ( ! empty( $link['id'] ) ) {
			$link['date_modified'] = date( 'Y-m-d H:i:s' );
			$result                = $wpdb->update( $table_name, $link, array( 'id' => $link['id'] ) );
		} else {
			$link['date_added'] = date( 'Y-m-d H:i:s' );

			foreach ( $link as $key => $field ) {
				if ( ! in_array( $key, $this->_allowed_fields ) ) {
					unset( $link[ $key ] );
				}
			}

			$result = $wpdb->insert( $table_name, $link );
		}

		if ( false === $result ) {
			$link = new WP_Error( 404, __( 'Redirect Link could not be saved into database: ' . $wpdb->last_error, 'thrive-quiz-builder' ) );
		} else {
			$link = $this->get_link_by_id( $wpdb->insert_id ? $wpdb->insert_id : $link['id'] );
		}

		return $link;
	}

	public static function delete_link( $id ) {

		/** @var $wpdb wpdb */
		global $wpdb;
		$table_name = tqb_table_name( 'results_links' );

		return $wpdb->delete( $table_name, array( 'id' => (int) $id ) );
	}

	/**
	 * For each result/category of quiz generate new link
	 *
	 * @param $results
	 *
	 * @return array
	 */
	public function generate_results_links( $results ) {

		if ( false === is_array( $results ) || empty( $results ) ) {
			return array();
		}

		/** @var wpdb $wpdb */
		global $wpdb;
		$table_name = tqb_table_name( 'results_links' );

		foreach ( $results as $result ) {
			$link = array(
				'quiz_id'   => $result['quiz_id'],
				'page_id'   => $this->_post->ID,
				'status'    => 'invalid',
				'result_id' => $result['id'],
			);

			$wpdb->insert( $table_name, $link );
		}

		return $this->get_links();
	}

	/**
	 * Handler for adding/deleting results
	 *
	 * @param int   $quiz_id
	 * @param array $previous_results
	 * @parama array $new_results
	 */
	public static function on_results_changed( $quiz_id, $previous_results, $new_results ) {

		/**
		 * find the results page post
		 */
		$results_page = get_posts( array(
			'post_parent' => $quiz_id,
			'post_type'   => Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_RESULTS,
		) );

		if ( empty( $results_page ) || empty( $previous_results ) || empty( $new_results ) ) {
			return;
		}

		/**
		 * parse for old ids
		 */
		$old_ids = array_map( function ( $result ) {
			return ! empty( $result['id'] ) ? (int) $result['id'] : null;
		}, is_array( $previous_results ) ? $previous_results : array() );

		/**
		 * parse for existing/deleted/new ids
		 */
		$existing_ids = array_map( function ( $result ) {
			return ! empty( $result['id'] ) ? (int) $result['id'] : null;
		}, is_array( $new_results ) ? $new_results : array() );

		/**
		 * which links with result_id should be deleted
		 */
		$delete_ids = array_unique( array_diff( $old_ids, $existing_ids ) );

		/**
		 * new links with new ids have to be added
		 */
		$new_ids = array_unique( array_diff( $existing_ids, $old_ids ) );

		/** @var wpdb $wpdb */
		global $wpdb;
		$table_name = tqb_table_name( 'results_links' );

		if ( ! empty( $delete_ids ) ) {
			$wpdb->query( 'DELETE FROM ' . $table_name . ' WHERE result_id IN (' . implode( ',', $delete_ids ) . ')' );
		}

		if ( ! empty( $new_ids ) ) {

			$page = new TQB_Results_Page( current( $results_page ) );

			foreach ( $new_ids as $new_id ) {
				$link = array(
					'quiz_id'   => $quiz_id,
					'page_id'   => $results_page['0']->ID,
					'status'    => 'invalid',
					'result_id' => $new_id,
				);
				$page->save_link( $link );
			}
		}
	}

	public function save_message( $message ) {

		update_post_meta( $this->_post->ID, 'tqb_redirect_message', $message );
	}

	/**
	 * Clone the current post into $new_post
	 *
	 * @param int|WP_Post $new_post
	 *
	 * @return bool
	 */
	public function clone_to( $new_post ) {

		$new_post = true === $new_post instanceof WP_Post ? $new_post : get_post( $new_post );
		if ( false === $new_post instanceof WP_Post ) {
			return false;
		}

		//clone links
		$links = $this->get_links();
		foreach ( $links as $link ) {
			$l            = (array) $link;
			$l['id']      = null;
			$l['quiz_id'] = $new_post->post_parent;
			$l['page_id'] = $new_post->ID;
			unset( $l['date_added'] );
			unset( $l['date_modified'] );
			$l['result_id'] = $this->_get_related_category_id( $l['result_id'] );

			$this->save_link( $l );
		}

		/**
		 * set the new results page with correct type: url/page
		 */
		update_post_meta( $new_post->ID, 'tqb_results_type', $this->_post->tqb_results_type );

		update_post_meta( $new_post->ID, 'tqb_redirect_display_message', (int) $this->_post->tqb_redirect_display_message );

		/**
		 * set to new results page the message to be displayed
		 */
		update_post_meta( $new_post->ID, 'tqb_redirect_message', $this->_post->tqb_redirect_message );

		/**
		 * set to new results page redirect forward results
		 */
		update_post_meta( $new_post->ID, 'tqb_redirect_forward_results', (int) $this->_post->tqb_redirect_forward_results );

		return true;
	}

	/**
	 * Match the old ID of result with the newer one(just cloned)
	 *
	 * @param int $old_id for old result/category
	 *
	 * @return int|null
	 *
	 * @see TQB_Quiz_Manager::get_clone_pages()
	 * @see TQB_Quiz_Manager::get_related_categories_ids()
	 */
	private function _get_related_category_id( $old_id ) {
		$id = null;

		if ( ! empty( $this->related_categories ) && ! empty( $this->related_categories[ $old_id ] ) ) {
			$id = $this->related_categories[ $old_id ];
		}

		return $id;
	}

	/**
	 * Delete the links for the current post
	 *
	 * @return bool
	 */
	public function delete() {

		$links = $this->get_links();

		foreach ( $links as $link ) {
			self::delete_link( $link->id );
		}

		return true;
	}
}

add_action( 'tqb-quiz-results-modified', array( 'TQB_Results_Page', 'on_results_changed' ), 10, 3 );
