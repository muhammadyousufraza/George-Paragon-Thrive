<?php
/**
 * FileName  class-tcm-rest-settings-controller.php.
 *
 * @project  : thrive-comments
 * @developer: Dragos Petcu
 * @company  : BitStone
 */

/**
 * Class TCM_REST_Settings_Controller
 */
class TCM_REST_Settings_Controller extends TCM_REST_Controller {

	/**
	 * Base rest url
	 *
	 * @var string $base
	 */
	public $base = 'settings';

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {

		register_rest_route( self::$namespace . self::$version, '/' . $this->base, array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_settings' ),
				'permission_callback' => array( $this, 'get_settings_permissions_check' ),
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_setting' ),
				'permission_callback' => array( $this, 'update_settings_permissions_check' ),
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_setting' ),
				'permission_callback' => array( $this, 'delete_settings_permissions_check' ),
			),
		) );

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/keyboard-tooltip', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_keyboard_tooltip_setting' ),
				'permission_callback' => array( $this, 'update_keyboard_tooltip_settings_permissions_check' ),
			),
		) );

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/keywords', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_keywords' ),
				'permission_callback' => array( $this, 'update_settings_permissions_check' ),
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_keywords' ),
				'permission_callback' => array( $this, 'delete_item_permissions_check' ),
				'args'                => array(
					'force' => array(
						'default' => false,
					),
				),
			),
		) );

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/reporting', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'get_reports' ),
				'permission_callback' => array( $this, 'get_settings_permissions_check' ),
			),
		) );
	}

	/**
	 * Check if a given request has access to delete a specific item
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|bool
	 */
	public function delete_item_permissions_check( $request ) {
		return $this->create_item_permissions_check( $request );
	}

	/**
	 * Update keywords
	 *
	 * @param WP_REST_Request $request The request data from admin.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function update_keywords( $request ) {

		$keywords = $this->prepare_keywords_for_database( $request );
		array_push( $keywords, array(
			'name'      => $request->get_param( 'name' ),
			'value'     => $request->get_param( 'value' ),
			'new_tab'   => $request->get_param( 'new_tab' ),
			'no_follow' => $request->get_param( 'no_follow' ),
		) );
		if ( tcah()->tcm_update_option( 'tcm_keywords', $keywords ) ) {
			return new WP_REST_Response( 1, 200 );
		} else {
			return new WP_Error( 'cant-update', __( "Couldn't update setting", Thrive_Comments_Constants::T ), array( 'status' => 500 ) );
		}
	}

	/**
	 * Prepare the item for create or update operation
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return array
	 */
	protected function prepare_keywords_for_database( $request ) {
		$keywords = get_option( 'tcm_keywords' );

		if ( empty( $keywords ) ) {
			$keywords = array();
		}
		$name = $request->get_param( 'name' );

		foreach ( $keywords as $keyword ) {
			if ( $keyword['name'] === $name ) {
				// Delete the option.
				unset( $keyword );
				break;
			}
		}

		return array_values( $keywords );
	}

	/**
	 * Delete one keyword from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function delete_keywords( $request ) {

		if ( tcah()->tcm_delete_option( $request->get_param( 'name' ), $request->get_param( 'value' ) ) ) {
			return new WP_REST_Response( true, 200 );
		} else {
			return new WP_Error( 'cant-delete', __( "Couldn't delete keyword", Thrive_Comments_Constants::T ), array( 'status' => 500 ) );
		}
	}

	/**
	 * Get all settings, if the setting does not exists, create it
	 *
	 * @return WP_REST_Response
	 */
	public function get_settings() {
		$settings = array();
		$defaults = Thrive_Comments_Constants::get_defaults();

		foreach ( $defaults as $setting => $setting_value ) {

			$value = get_option( $setting, $setting_value );

			if ( 'tcm_default_picture' === $setting && empty( $value ) ) {
				$value = tcm()->plugin_url( 'assets/images/' . Thrive_Comments_Constants::TCM_DEFAULT_PICTURE );
			}

			if ( 'tcm_conversion' === $setting ) {
				if ( empty( $value ) ) {
					$value = $this->default_conversion_settings();
				} else {
					$value = tcah()->check_thriveboxes( $value );
				}
			}

			if ( 'comment_order' === $setting && empty( $value ) ) {
				$value = $this->default_comment_order_setting();
			}

			$settings[] = array(
				'name'  => $setting,
				'value' => $value,
			);
		}

		return new WP_REST_Response( $settings, 200 );
	}

	/**
	 * Check if the user can change comments settings
	 *
	 * @return bool
	 */
	public function get_settings_permissions_check() {
		return TCM_Product::has_access();
	}

	/**
	 * Check if the user can update comments settings
	 *
	 * @return bool
	 */
	public function update_settings_permissions_check() {
		return TCM_Product::has_access();
	}

	/**
	 * The user should always be able to check or uncheck the 'Don't show again' on the keyboard tooltip. (if logged in)
	 *
	 * @return bool
	 */
	public function update_keyboard_tooltip_settings_permissions_check() {
		return is_user_logged_in();
	}

	/**
	 * Check if the user can delete comments settings
	 *
	 * @return bool
	 */
	public function delete_settings_permissions_check() {
		return TCM_Product::has_access();
	}

	/**
	 * Update setting from admin
	 *
	 * @param WP_REST_Request $request The request data from admin.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function update_setting( $request ) {

		$name  = $request->get_param( 'name' );
		$value = $request->get_param( 'value' );

		$cache_plugin = tve_dash_detect_cache_plugin();
		if ( $cache_plugin ) {
			tve_dash_cache_plugin_clear( $cache_plugin );
		}

		if ( tcah()->tcm_update_option( $name, $value ) ) {
			return new WP_REST_Response( 1, 200 );
		} else {
			return new WP_Error( 'cant-update', __( "Couldn't update setting", Thrive_Comments_Constants::T ), array( 'status' => 500 ) );
		}
	}

	/*
	 * Updates keyboard tooltip notification display: every user is represented by his id:
	 * if his id is in the array then the notification will not be displayed for him
	 *
	 * @param WP_REST_Request $request The request data from admin.
	 * @return WP_Error|WP_REST_Response
	 */
	public function update_keyboard_tooltip_setting( $request ) {

		$checkbox = $request->get_param( 'value' );

		/* add or update array with current users who do not want the tooltip displayed */
		$option        = get_option( Thrive_Comments_Constants::TCM_KEYBOARD_TOOLTIP );
		$setting_value = empty( $option ) ? array() : json_decode( $option );

		$user_id = (int) wp_get_current_user()->data->ID;

		$key = array_search( $user_id, $setting_value );

		/* user not in the array and checkbox = checked */
		if ( $key === false && ! empty( $checkbox ) ) {
			$setting_value[] = $user_id;
			/* user is in the array and checkbox = unchecked */
		} elseif ( $key !== false && empty( $checkbox ) ) {
			$setting_value = array_diff( $setting_value, array( $user_id ) );
		}

		if ( tcah()->tcm_update_option( Thrive_Comments_Constants::TCM_KEYBOARD_TOOLTIP, json_encode( $setting_value ) ) ) {
			return new WP_REST_Response( 1, 200 );
		} else {
			return new WP_Error( 'cant-update', __( "Couldn't update setting", Thrive_Comments_Constants::T ), array( 'status' => 500 ) );
		}
	}

	/**
	 * Delete setting from admin
	 *
	 * @param WP_REST_Request $request The request data from admin.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function delete_setting( $request ) {
		$name = $request->get_param( 'name' );
		$id   = $request->get_param( 'id' );

		$cache_plugin = tve_dash_detect_cache_plugin();
		if ( $cache_plugin ) {
			tve_dash_cache_plugin_clear( $cache_plugin );
		}

		if ( tcah()->tcm_delete_option( $name, $id ) ) {
			return new WP_REST_Response( 1, 200 );
		} else {
			return new WP_Error( 'cant-delete', __( "Couldn't delete setting", Thrive_Comments_Constants::T ), array( 'status' => 500 ) );
		}
	}

	/**
	 * Set the default for the conversion settings
	 * For thriveboxes set by default the first thrivebox in the list if there is one
	 *
	 * @return array
	 */
	public function default_conversion_settings() {

		$defaults     = Thrive_Comments_Constants::$_tcm_conversion_defaults;
		$thrive_boxes = tcmc()->get_thrive_boxes();

		if ( ! empty( $thrive_boxes ) ) {

			$first_elem = reset( $thrive_boxes );

			$defaults['tcm_thrivebox']['first_time']['thrivebox_id']  = $first_elem->ID;
			$defaults['tcm_thrivebox']['second_time']['thrivebox_id'] = $first_elem->ID;
		}

		return $defaults;
	}

	/**
	 * Return default comment order setting
	 *
	 * @return string
	 */
	public function default_comment_order_setting() {
		$is_voting_active = tcms()->tcm_get_setting_by_name( 'tcm_vote_type' );

		return ( $is_voting_active === 'no_vote' ) ? 'asc' : Thrive_Comments_Constants::DEFAULT_SORT;
	}

	/**
	 * Get report data based on request params.
	 *
	 * @param WP_REST_Request $request request.
	 *
	 * @return array $result all data.
	 */
	public function get_reports( $request ) {
		$graph_name         = $request->get_param( 'name' );
		$date_interval      = $request->get_param( 'date_interval' );
		$graph_interval     = $request->get_param( 'graph_interval' );
		$graph_source       = $request->get_param( 'graph_source' );
		$date_before        = $request->get_param( 'date_before' );
		$date_after         = $request->get_param( 'date_after' );
		$include_moderators = $request->get_param( 'include_moderators' );

		list( $begin, $end ) = tcmh()->get_interval_days( $date_interval, $date_after, $date_before );

		if ( method_exists( $this, $graph_name ) ) {
			return $this->$graph_name( $graph_interval, $graph_source, $begin, $end, $include_moderators, $request );
		}

	}

	/**
	 * Comments graph.
	 *
	 * @param string   $graph_interval     graph time interval.
	 * @param string   $graph_source       post_id.
	 * @param DateTime $begin              beginning date.
	 * @param DateTime $end                ending date.
	 * @param int      $include_moderators include_moderators.
	 *
	 * @return array
	 */
	public function comments_graph( $graph_interval, $graph_source, $begin, $end, $include_moderators, $request = null ) {
		$result       = array();
		$interval     = '+' . $graph_interval;
		$query_result = tcmdb()->get_comment_reports_query( $graph_source, $graph_interval, $begin, $end, $include_moderators, $request );

		while ( $begin <= $end ) {

			list( $comments_counter, $date ) = $this->get_reports_data( $query_result, $begin, $graph_interval );

			$spam_comments       = ! empty( $comments_counter[ Thrive_Comments_Constants::TCM_SPAM ] ) ? intval( $comments_counter[ Thrive_Comments_Constants::TCM_SPAM ] ) : 0;
			$trash_comments      = ! empty( $comments_counter[ Thrive_Comments_Constants::TCM_TRASH ] ) ? intval( $comments_counter[ Thrive_Comments_Constants::TCM_TRASH ] ) : 0;
			$featured_comments   = ! empty( $comments_counter[ Thrive_Comments_Constants::TCM_FEATURED ] ) ? intval( $comments_counter[ Thrive_Comments_Constants::TCM_FEATURED ] ) : 0;
			$unreplied_comments  = ! empty( $comments_counter[ Thrive_Comments_Constants::TCM_UNREPLIED ] ) ? intval( $comments_counter[ Thrive_Comments_Constants::TCM_UNREPLIED ] ) : 0;
			$approved_comments   = ! empty( $comments_counter['1'] ) ? intval( $comments_counter['1'] ) : 0;
			$unapproved_comments = ! empty( $comments_counter['0'] ) ? intval( $comments_counter['0'] ) : 0;
			$all_comments        = $spam_comments + $trash_comments + $approved_comments + $unapproved_comments;
			$replied_comments    = $all_comments - $unreplied_comments;

			$result['all_comments'][] = array(
				'all_comments' => $all_comments,
				'date'         => $date,
			);

			$result['approved_comments'][] = array(
				'approved_comments' => $approved_comments,
				'date'              => $date,
			);

			$result['replied_comments'][] = array(
				'replied_comments' => $replied_comments,
				'date'             => $date,
			);

			$result['featured_comments'][] = array(
				'featured_comments' => $featured_comments,
				'date'              => $date,
			);

			$result['spam_comments'][] = array(
				'spam_comments' => $spam_comments,
				'date'          => $date,
			);

			$result['trash_comments'][] = array(
				'trash_comments' => $trash_comments,
				'date'           => $date,
			);

			$begin->modify( $interval );
		}

		return $result;
	}

	/**
	 * Vote engagemenets report.
	 *
	 * @param string   $graph_interval     graph time interval.
	 * @param string   $graph_source       post_id.
	 * @param DateTime $begin              beginning date.
	 * @param DateTime $end                ending date.
	 * @param int      $include_moderators include_moderators.
	 *
	 * @return array
	 */
	public function vote_engagements( $graph_interval, $graph_source, $begin, $end, $include_moderators, $request = null ) {
		$result       = array();
		$interval     = '+' . $graph_interval;
		$query_result = tcmdb()->get_votes_reports_query( $graph_source, $graph_interval, $begin, $end, $include_moderators, $request );

		while ( $begin <= $end ) {

			list( $votes_counter, $date ) = $this->get_reports_data( $query_result, $begin, $graph_interval );

			$result['upvotes'][] = array(
				'upvotes' => ! empty( $votes_counter['upvote'] ) ? intval( $votes_counter['upvote'] ) : 0,
				'date'    => $date,
			);

			$result['downvotes'][] = array(
				'downvotes' => ! empty( $votes_counter['downvote'] ) ? intval( $votes_counter['downvote'] ) : 0,
				'date'      => $date,
			);

			$begin->modify( $interval );
		}

		return $result;
	}

	/**
	 * Most active commenters report.
	 *
	 * @param string   $graph_interval     graph time interval.
	 * @param string   $graph_source       post_id.
	 * @param DateTime $begin              beginning date.
	 * @param DateTime $end                ending date.
	 * @param int      $include_moderators include_moderators.
	 *
	 * @return array
	 */
	public function most_active_commenters( $graph_interval, $graph_source, $begin, $end, $include_moderators, $request = null ) {
		return tcmdb()->top_comment_authors( 100000, $begin, $end, $include_moderators );
	}

	/**
	 * Most popular posts report.
	 *
	 * @param string   $graph_interval     graph time interval.
	 * @param string   $graph_source       post_id.
	 * @param DateTime $begin              beginning date.
	 * @param DateTime $end                ending date.
	 * @param int      $include_moderators include_moderators.
	 *
	 * @return array
	 */
	public function most_popular_posts( $graph_interval, $graph_source, $begin, $end, $include_moderators, $request = null ) {
		$result = array();
		$args   = array(
			'post_type'      => 'any',
			'posts_per_page' => - 1,
		);

		// Special case for custom range dates being the same.
		$query = new WP_Query( $args );
		if ( $query->have_posts() ) {
			foreach ( $query->posts as $post ) {

				if ( ! $include_moderators ) {
					$mods = implode( ', ', tcamh()->tcm_get_moderator_ids() );
				} else {
					$mods = '0';
				}

				$comments_number = get_comments( array(
					'post_id'        => $post->ID,
					'date_query'     => array(
						'after'     => $begin->format( 'Y-m-d' ),
						'before'    => $end->format( 'Y-m-d' ),
						'inclusive' => true,
					),
					'count'          => true,
					'author__not_in' => $mods,
				) );
				if ( 0 !== $comments_number ) {
					$result[] = array(
						'post_title'    => $post->post_title,
						'comment_count' => $comments_number,
					);
				}
			}
		}

		$result = apply_filters( 'tcm_most_popular_posts', $result, $begin, $end );

		$comments = array();
		foreach ( $result as $key => $row ) {
			$comments[ $key ] = $row['comment_count'];
		}
		array_multisort( $comments, SORT_DESC, $result );

		return $result;
	}

	/**
	 * Get the report numbers for the specified date.
	 *
	 * @param array    $query_result   DB result.
	 * @param DateTime $begin_date     begin date.
	 * @param string   $graph_interval graph interval.
	 *
	 * @return mixed
	 */
	public function get_reports_data( $query_result, $begin_date, $graph_interval ) {
		switch ( $graph_interval ) {
			case '1 week':
				$begin = $begin_date->format( 'Y' ) . '-' . intval( $begin_date->format( 'W' ) );
				$date  = 'Week ' . $begin_date->format( 'W' ) . ', ' . $begin_date->format( 'Y' );
				break;
			case '1 month':
				$begin = $begin_date->format( 'Y-m' );
				$date  = $begin_date->format( 'F' ) . ', ' . $begin_date->format( 'Y' );
				break;
			default:
				$begin = $begin_date->format( 'Y-m-d' );
				$date  = $begin_date->format( 'd M Y' );
				break;
		}
		if ( ! array_key_exists( $begin, $query_result ) ) {
			$query_result[ $begin ] = array();
		}

		return array( $query_result[ $begin ], $date );
	}

	/**
	 * Most upvoted comments.
	 *
	 * @param string   $graph_interval     graph time interval.
	 * @param string   $graph_source       post_id.
	 * @param DateTime $begin              beginning date.
	 * @param DateTime $end                ending date.
	 * @param int      $include_moderators include_moderators.
	 *
	 * @return array
	 */
	public function most_upvoted( $graph_interval, $graph_source, $begin, $end, $include_moderators, $request = null ) {
		$results  = tcmdb()->most_upvoted_comments( $begin, $end, $include_moderators );
		$comments = array();

		foreach ( $results as $result ) {

			if ( ! isset( $comments[ $result->comment_ID ] ) ) {
				$comments[ $result->comment_ID ] = array(
					'comment_ID'           => $result->comment_ID,
					'comment_author'       => $result->comment_author,
					'comment_author_email' => $result->comment_author_email,
					'comment_content'      => $result->comment_content,
					'comment_post'         => get_the_title( $result->comment_post_ID ),
					'comment_post_link'    => get_permalink( $result->comment_post_ID ),
					'comment_post_ID'      => $result->comment_post_ID,
				);
			}

			if ( 'downvote' === $result->meta_key ) {
				$comments[ $result->comment_ID ]['downvote'] = $result->meta_value;
			} else {
				$comments[ $result->comment_ID ]['upvote'] = $result->meta_value;
			}
		}

		usort( $comments, array( $this, 'my_sort' ) );

		$comments = array_values( $comments );

		/**
		 * Added for compatibility with TA
		 *
		 * Filter most upvoted comments
		 */
		return apply_filters( 'tcm_most_upvoted', $comments );
	}

	public function my_sort( $first, $second ) {
		return $first['upvote'] < $second['upvote'];
	}
}
