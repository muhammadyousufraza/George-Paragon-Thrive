<?php

/**
 * Class TVA_Topics_Controller
 * - handles CRUD operations for course topics
 */
class TVA_Topics_Controller extends TVA_REST_Controller {

	/**
	 * @var string
	 */
	public $base = 'topics';

	/**
	 * Register Routes
	 */
	public function register_routes() {

		register_rest_route( self::$namespace . self::$version, '/' . $this->base, array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_item' ),
				'permission_callback' => array( 'TVA_Product', 'has_access' ),
				'args'                => array(),
			),
		) );

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/(?P<ID>[\d]+)', array(
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_item' ),
				'permission_callback' => array( 'TVA_Product', 'has_access' ),
				'args'                => array(),
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_item' ),
				'permission_callback' => array( 'TVA_Product', 'has_access' ),
				'args'                => array(),
			),
		) );
	}

	/**
	 * Adds a new topic item in DB
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function create_item( $request ) {

		$topics = array();

		foreach ( TVA_Topic::get_items() as $item ) {
			$topics[] = $item->jsonSerialize();
		}

		$new_model = $this->_prepare_model( $request );

		$topics[] = $new_model->jsonSerialize();

		$result = update_option( 'tva_filter_topics', $topics );

		if ( $result ) {
			return rest_ensure_response( $new_model );
		}

		return new WP_Error( 'no-results', __( 'No topic was updated!', 'thrive-apprentice' ) );
	}

	/**
	 * Deletes a topic from DB by ID
	 * - and updates courses with default topic
	 * - default topic cannot be deleted
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function delete_item( $request ) {

		$response = array();
		$id       = (int) $request->get_param( 'ID' );
		if ( $id === 0 ) {
			return new WP_Error( 'not_allowed', esc_html__( 'Topic not allowed to be deleted!', 'thrive-apprentice' ) );
		}

		$topics = get_option( 'tva_filter_topics', array() );

		foreach ( $topics as $key => $topic ) {
			if ( $topic['ID'] == $id ) {
				unset( $topics[ $key ] );
			}
		}

		$topics = array_values( $topics );

		$courses = TVA_Course_V2::get_items( array( 'limit' => 1000 ) );

		foreach ( $courses as $course ) {
			if ( $course->get_topic()->id === $id ) {
				$course->topic = 0;
				$course->save();
			}
		}

		$result = update_option( 'tva_filter_topics', $topics );

		if ( $result ) {
			return rest_ensure_response( $response );
		}

		return new WP_Error( 'no-results', __( 'No topic was deleted!', 'thrive-apprentice' ) );
	}

	/**
	 * Updates a new topic in the list based on ID
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function update_item( $request ) {

		$new_topic = $this->_prepare_model( $request );
		$topics    = array();

		foreach ( TVA_Topic::get_items() as $key => $topic ) {

			$topics[ $key ] = $topic->jsonSerialize();

			if ( (int) $topic->ID === (int) $new_topic->ID ) {
				$topics[ $key ] = $new_topic->jsonSerialize();
			}
		}

		$result = update_option( 'tva_filter_topics', $topics );

		if ( $result ) {
			return rest_ensure_response( $new_topic );
		}

		return new WP_Error( 'no-results', __( 'No topic was updated!', 'thrive-apprentice' ) );
	}

	/**
	 * Creates a topic model instance based on the request
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return TVA_Topic
	 */
	protected function _prepare_model( $request ) {

		$icon_param = $request->get_param( 'icon' );
		$icon       = preg_replace( '#^https?://#', '//', $icon_param );
		$rec_id     = $request->get_param( 'ID' );
		$id         = isset( $rec_id ) ? $rec_id : $this->_get_next_topic_id();

		$model = array(
			'ID'                  => $id,
			'id'                  => $id,
			'color'               => $request->get_param( 'color' ),
			'icon'                => $icon,
			'title'               => $request->get_param( 'title' ),
			'svg_icon'            => $request->get_param( 'svg_icon' ),
			'icon_type'           => $request->get_param( 'icon_type' ),
			'layout_icon_color'   => $request->get_param( 'layout_icon_color' ),
			'overview_icon_color' => $request->get_param( 'overview_icon_color' ),
		);

		return new TVA_Topic( $model );
	}

	/**
	 * Gets the next new ID available for new topic item
	 *
	 * @return int
	 */
	protected function _get_next_topic_id() {

		$topics = TVA_Topic::get_items();
		$id     = 0;

		if ( ! empty( $topics ) ) {
			/**
			 * Get the biggest ID so we can create our new one
			 */
			foreach ( $topics as $topic ) {
				if ( $topic->id > $id ) {
					$id = (int) $topic->id;
				}
			}
		}

		$id ++;

		return $id;
	}
}
