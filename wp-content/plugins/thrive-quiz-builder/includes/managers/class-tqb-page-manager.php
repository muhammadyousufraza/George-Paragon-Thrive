<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

/**
 * Class TQB_Page_Manager
 *
 * Handles Page operations
 */
class TQB_Page_Manager {

	/**
	 * @var TQB_Page_Manager $instance
	 */
	protected $page;

	/**
	 * TQB_Page_Manager constructor.
	 */
	public function __construct( $page_id = null ) {

		$this->page = get_post( $page_id );
	}

	/**
	 *  Get all quiz pages
	 */
	public static function get_quiz_pages( $quiz_id ) {
		$posts = query_posts(
			array(
				'post_type'   => array(
					Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_SPLASH_PAGE,
					Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_QNA,
					Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_OPTIN,
					Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_RESULTS,
				),
				'post_parent' => $quiz_id,
			)
		);

		return $posts;
	}

	/**
	 *  Get the quiz_id for page
	 */
	public function get_quiz_id() {
		return $this->page->post_parent;
	}

	/**
	 * Delete all quiz pages and the tests that are linked to them
	 *
	 * @param array $posts
	 */
	public static function delete_quiz_pages( $posts = array() ) {
		global $tqbdb;

		foreach ( $posts as $post ) {
			wp_delete_post( $post->ID, true );

			$structure_page = TQB_Structure_Manager::make_page( $post );
			$structure_page->delete();

			//delete event log for splash and result and optin page
			$tqbdb->delete_logs( array( 'page_id' => $post->ID ) );
			//delete event log for Q&A
			$tqbdb->delete_logs( array( 'page_id' => $post->post_parent ) );

			// delete quiz test and test items
			$tests = $tqbdb->get_test( array( 'page_id' => $post->ID ), false );
			if ( empty( $tests ) ) {
				continue;
			}

			foreach ( $tests as $test ) {
				$test_manager = new TQB_Test_Manager( $test['id'] );
				$test_manager->delete_test_items();
				$test_manager->delete_test( array(
					'page_id' => $post->ID,
					'id'      => $test['id'],
				) );
			}
		}
	}

	/**
	 * Gets running test for current page
	 *
	 * @param array  $filters
	 * @param bool   $single
	 * @param string $return_type ARRAY_A|OBJECT
	 *
	 * @return array|false
	 */
	public function get_tests_for_page( $filters, $single = false, $return_type = ARRAY_A ) {
		global $tqbdb;

		if ( empty( $this->page ) ) {
			return false;
		}

		return $tqbdb->get_test( $filters, $single, $return_type );
	}

	/**
	 * Gets a quiz page based on a given id
	 *
	 * @param bool $is_front
	 * @param bool $viewed
	 *
	 * @return array|bool|TQB_Page_Manager|WP_Post|null
	 */
	public function get_page( $is_front = false, $viewed = false ) {
		global $tqbdb;

		if ( empty( $this->page ) ) {
			return false;
		}
		if ( $viewed ) {
			$this->update_page_viewed_status( $this->page->post_parent, $this->page->post_type );
		}

		$this->page->variations = $tqbdb->get_page_variations( array(
			'post_id'     => $this->page->ID,
			'post_status' => Thrive_Quiz_Builder::VARIATION_STATUS_PUBLISH,
		), OBJECT );

		foreach ( $this->page->variations as $variation ) {
			$variation->tcb_editor_url = TQB_Variation_Manager::get_editor_url( $this->page->ID, $variation->id );
			$variation->post_type      = $this->page->post_type;
		}
		//get test and its items
		$this->page->running_test = $this->get_tests_for_page( array( 'page_id' => $this->page->ID, 'status' => 1 ), true );

		//get stuff only for backend
		if ( ! $is_front ) {
			$this->page->archived_variations = $tqbdb->get_page_variations( array(
				'post_id'     => $this->page->ID,
				'post_status' => Thrive_Quiz_Builder::VARIATION_STATUS_ARCHIVE,
			), OBJECT );
			foreach ( $this->page->archived_variations as $archived_variation ) {
				$archived_variation->tcb_preview_url = TQB_Variation_Manager::get_preview_url( $this->page->ID, $archived_variation->id );
				$archived_variation->post_type       = $this->page->post_type;
			}

			$this->page->completed_tests = $this->get_tests_for_page( array( 'page_id' => $this->page->ID, 'status' => 0 ), false );

			$this->page->tqb_page_name        = tqb()->get_style_page_name( $this->page->post_type );
			$this->page->tqb_page_description = tqb()->get_style_page_description( $this->page->post_type );
			$this->page->quiz_name            = html_entity_decode( get_the_title( $this->page->post_parent ) );
			$this->page->gdpr_user_consent    = $this->get_user_consent();
			$this->page->skip_optin           = TQB_Post_meta::get_quiz_page_skip_optin( $this->page->ID );
		}

		return $this->page;
	}

	/**
	 * Returns user consent
	 *
	 * @return int
	 */
	public function get_user_consent() {
		$consent = TQB_Post_meta::get_quiz_page_gdpr_user_consent( $this->page->ID );

		if ( is_numeric( $consent ) && intval( $consent ) === 1 || $consent === '' ) {
			return 1;
		}

		return 0;
	}

	/**
	 * Gets a quiz page based on a given id
	 *
	 * @return array|bool|null
	 */
	public function update_page_viewed_status( $quiz_id, $type ) {

		$type           = tqb()->get_structure_type_name( $type );
		$type           = str_replace( 'tqb_', '', $type );
		$quiz_structure = new TQB_Structure_Manager( $quiz_id );

		return $quiz_structure->update_quiz_viewed_status( $type, true );
	}

	/**
	 * Saved the page
	 *
	 * @param $type
	 * @param $quiz_id
	 *
	 * @return int|WP_Error
	 */
	public function save_page( $type, $quiz_id ) {
		$post_type = tqb()->get_structure_post_type_name( $type );
		$page_name = tqb()->get_style_page_name( $post_type );
		$args      = array(
			'post_type'   => $post_type,
			'post_parent' => $quiz_id,
			'post_title'  => $page_name,
			'post_status' => 'publish',
		);

		return wp_insert_post( $args );
	}

	/**
	 * Delete the page
	 *
	 * @return int|WP_Error
	 */
	public function delete_page() {

		wp_delete_post( $this->page->ID );
		global $tqbdb;
		$tqbdb->delete_tests( array( 'page_id' => $this->page->ID ) );

		return $tqbdb->delete_variations( array( 'page_id' => $this->page->ID ) );
	}

	/**
	 * Get page html to display
	 *
	 * @param $points
	 *
	 * @return array()
	 */
	public function get_page_display_html( $points ) {
		$page = $this->get_page( true );
		if ( empty( $page ) ) {
			return array();
		}
		$page_type = TQB_Post_meta::get_quiz_type_meta( $page->post_parent );
		global $tqbdb;
		$variation_manager = new TQB_Variation_Manager( $page->post_parent, $page->ID );

		if ( ! empty( $page->running_test ) && empty( $_POST['tqb_in_tcb_editor'] ) ) {
			$variation = $variation_manager->determine_variation( $page->running_test );
		} else {
			$variation = $variation_manager->get_page_variations(
				array(
					'is_control'  => 1,
					'post_status' => 'publish',
				)
			);
		}
		$variation['post_type'] = $page->post_type;

		if ( ( $page->post_type === Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_RESULTS || $page->post_type === Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_OPTIN ) && isset( $points['user_points'] ) ) {

			$content    = '';
			$variations = $tqbdb->get_page_variations( array( 'parent_id' => $variation['id'] ) );

			$result = $tqbdb->get_explicit_result( $points );

			foreach ( $variations as $child_variation ) {
				$tcb_fields = is_array( $child_variation['tcb_fields'] ) ? $child_variation['tcb_fields'] : unserialize( $child_variation['tcb_fields'] );
				switch ( $page_type['type'] ) {
					case Thrive_Quiz_Builder::QUIZ_TYPE_NUMBER:
					case Thrive_Quiz_Builder::QUIZ_TYPE_RIGHT_WRONG:
						if ( $points['user_points'] >= $child_variation['tcb_fields']['min'] && $points['user_points'] <= $child_variation['tcb_fields']['max'] ) {
							$content = explode( Thrive_Quiz_Builder::STATES_DYNAMIC_CONTENT_PATTERN, $child_variation['content'] );
							if ( ! empty( $tcb_fields[ Thrive_Quiz_Builder::FIELD_INLINE_CSS ] ) ) {
								$variation[ 'dynamic_content_' . Thrive_Quiz_Builder::FIELD_INLINE_CSS ] = $tcb_fields[ Thrive_Quiz_Builder::FIELD_INLINE_CSS ];
							}
							$variation['content_child_variation_id'] = $child_variation['id'];
						}
						break;
					case Thrive_Quiz_Builder::QUIZ_TYPE_PERCENTAGE:
						if ( (float) $result >= $child_variation['tcb_fields']['min'] && (float) $result <= $child_variation['tcb_fields']['max'] ) {
							$content = explode( Thrive_Quiz_Builder::STATES_DYNAMIC_CONTENT_PATTERN, $child_variation['content'] );
							if ( ! empty( $tcb_fields[ Thrive_Quiz_Builder::FIELD_INLINE_CSS ] ) ) {
								$variation[ 'dynamic_content_' . Thrive_Quiz_Builder::FIELD_INLINE_CSS ] = $tcb_fields[ Thrive_Quiz_Builder::FIELD_INLINE_CSS ];
							}
							$variation['content_child_variation_id'] = $child_variation['id'];
						}
						break;
					case Thrive_Quiz_Builder::QUIZ_TYPE_PERSONALITY:
						if ( $points['result_id'] == $child_variation['tcb_fields']['result_id'] ) {
							$content = explode( Thrive_Quiz_Builder::STATES_DYNAMIC_CONTENT_PATTERN, $child_variation['content'] );
							if ( ! empty( $tcb_fields[ Thrive_Quiz_Builder::FIELD_INLINE_CSS ] ) ) {
								$variation[ 'dynamic_content_' . Thrive_Quiz_Builder::FIELD_INLINE_CSS ] = $tcb_fields[ Thrive_Quiz_Builder::FIELD_INLINE_CSS ];
							}
							$variation['content_child_variation_id'] = $child_variation['id'];
						}
						break;
				}
			}

			$m = explode( Thrive_Quiz_Builder::STATES_DYNAMIC_CONTENT_PATTERN, $variation['content'] );

			if ( isset( $m[1] ) && isset( $content[1] ) ) {
				$variation['content'] = str_replace( ( Thrive_Quiz_Builder::STATES_DYNAMIC_CONTENT_PATTERN . $m[1] . Thrive_Quiz_Builder::STATES_DYNAMIC_CONTENT_PATTERN ), $content[1], $variation['content'] );
			}

			// Replace %result shortcode with actual result%

			$variation['content'] = str_replace( Thrive_Quiz_Builder::QUIZ_RESULT_SHORTCODE, $result, $variation['content'] );

			$variation['content'] = self::insert_result_shortcodes( $variation, $points );
		} else {
			$content              = ! empty( $variation['content'] ) ? $variation['content'] : '';
			$variation['content'] = str_replace( Thrive_Quiz_Builder::QUIZ_RESULT_SHORTCODE, '', $content );
		}

		$variation['quiz_user_result'] = ! empty( $result ) || ( isset( $result ) && is_numeric( $result ) ) ? $result : '';

		return $variation;
	}

	/**
	 * Insert result shortcodes in variation content
	 *
	 * @param array $variation
	 * @param int   $points
	 *
	 * @return mixed
	 */
	public static function insert_result_shortcodes( $variation, $points ) {

		$q_type = get_post_meta( $variation['quiz_id'], TQB_Post_meta::META_NAME_FOR_QUIZ_TYPE, true );
		$type   = ! empty( $q_type['type'] ) ? $q_type['type'] : '';

		$data = array(
			'points'    => $points,
			'quiz_type' => $type,
		);

		foreach ( self::get_result_shortcodes( $data ) as $key => $shortcode ) {

			$new_sh_regex = '/\[tqb_quiz_result result_type=\'(' . $key . ')\' inline=\'.*\'/';
			$old_sh_regex = '/' . $shortcode['initial'] . '/';

			$match   = preg_match( $new_sh_regex, $variation['content'] ) || preg_match( $old_sh_regex, $variation['content'] );
			$replace = array(
				$shortcode['initial'],
				"[tqb_quiz_result result_type='" . $key . "' inline='1']",
				"[tqb_quiz_result result_type='" . $key . "' link='0']",
			);

			if ( $match ) {
				$variation['content'] = str_replace( $replace, $shortcode['replacement'], $variation['content'] );
			}
		}

		return $variation['content'];
	}

	/**
	 * Returns the shortcodes for result page
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public static function get_result_shortcodes( $data ) {

		if ( ! empty( $data['points']['explicit'] ) ) {
			$category = str_replace( "'", Thrive_Quiz_Builder::COMMA_PLACEHOLDER, $data['points']['explicit'] );

			$data['points']['explicit'] = addslashes( $category );
		}

		return array(
			'whole_number' => array(
				'initial'     => "[tqb_quiz_result result_type='whole_number']",
				'replacement' => "[tqb_quiz_result result_type='whole_number' data='" . json_encode( $data ) . "']",
			),
			'one_decimal'  => array(
				'initial'     => "[tqb_quiz_result result_type='one_decimal']",
				'replacement' => "[tqb_quiz_result result_type='one_decimal' data='" . json_encode( $data ) . "']",
			),
			'two_decimal'  => array(
				'initial'     => "[tqb_quiz_result result_type='two_decimal']",
				'replacement' => "[tqb_quiz_result result_type='two_decimal' data='" . json_encode( $data ) . "']",
			),
			'default'      => array(
				'initial'     => "[tqb_quiz_result result_type='default']",
				'replacement' => "[tqb_quiz_result result_type='default' data='" . json_encode( $data ) . "']",
			),
		);
	}

	/**
	 * Update social share badge links
	 *
	 * @param int $quiz_id
	 * @param     $social_share_badge_url
	 * @param     $social_share_badge_searched_url
	 */
	public function update_social_share_links( $quiz_id, $social_share_badge_url, $social_share_badge_searched_url ) {
		global $tqbdb;
		$variations = $tqbdb->get_page_variations( array( 'post_id' => $this->page->ID ) );


		if ( empty( $variations ) ) {
			return;
		}

		foreach ( $variations as $variation ) {
			if ( empty( $variation ['tcb_fields'][ Thrive_Quiz_Builder::FIELD_SOCIAL_SHARE_BADGE ] ) ) {
				continue;
			}

			$new_variation_content = str_replace( $social_share_badge_searched_url, $social_share_badge_url, $variation['content'] );

			$tqbdb->save_variation( array(
				'id'      => $variation['id'],
				'quiz_id' => $quiz_id,
				'page_id' => $this->page->ID,
				'content' => $new_variation_content,
			) );
		}
	}
}
