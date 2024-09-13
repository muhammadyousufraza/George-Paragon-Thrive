<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

/**
 * Class TQB_Structure_Manager
 *
 * Handles Structure operations
 */
class TQB_Structure_Manager {

	/**
	 * @var TQB_Structure_Manager $instance
	 */
	protected $quiz_id;

	/**
	 * @var array|null
	 */
	private $structure;

	/**
	 * TQB_Structure_Manager constructor.
	 *
	 * @param int $quiz_id
	 */
	public function __construct( $quiz_id ) {
		$this->quiz_id = $quiz_id;
	}

	/**
	 * Get quiz structure meta
	 * Added cache level to quiz structure
	 *
	 * @return mixed
	 */
	public function get_quiz_structure_meta() {
		if ( empty( $this->structure ) ) {
			$this->structure = get_post_meta( $this->quiz_id, TQB_Post_meta::META_NAME_FOR_QUIZ_STRUCTURE, true );
		}

		return $this->structure;
	}

	/**
	 * Updates the quiz structure
	 *
	 * @param $model
	 *
	 * @return false|int
	 */
	public function update_quiz_structure( $model ) {
		$old_structure = $this->get_quiz_structure_meta();
		if ( empty( $old_structure ) ) {
			$model['count_views'] = true;
			$old_structure        = array();
			$this->update_quiz_structure_meta( $model );
		}
		$model = $this->update_structure_item_posts( $model, $old_structure );
		$this->update_quiz_structure_meta( $model );

		return $model;
	}

	/**
	 * Updates the quiz structure page viewed status
	 *
	 * @param $type
	 *
	 * @return false|int
	 */
	public function update_quiz_viewed_status( $type, $value ) {
		$structure                    = $this->get_quiz_structure_meta();
		$structure['viewed'][ $type ] = $value;
		$result                       = $this->update_quiz_structure_meta( $structure );

		return $result;
	}

	/**
	 * Updates the quiz structure meta
	 * On Update quiz structure, we invalidate cache
	 *
	 * @param $model
	 *
	 * @return false|int
	 */
	public function update_quiz_structure_meta( $model ) {
		if ( isset( $model['running_tests'] ) ) {
			unset( $model['running_tests'] );
		}
		$this->structure = null;
		$result          = update_post_meta( $this->quiz_id, TQB_Post_meta::META_NAME_FOR_QUIZ_STRUCTURE, $model );

		return $result;
	}

	/**
	 * Updates structure items posts
	 *
	 * @param $model
	 * @param $old_structure
	 *
	 * @return mixed
	 */
	public function update_structure_item_posts( $model, $old_structure ) {
		$array = tqb()->get_structure_internal_identifiers();
		foreach ( $array as $value ) {
			if ( empty( $old_structure[ $value ] ) ) {
				$old_structure[ $value ] = false;
			}
			if ( $model[ $value ] !== $old_structure[ $value ] ) {
				$model[ $value ]           = $this->update_structure_item( $model[ $value ], $old_structure[ $value ], $value );
				$model['viewed'][ $value ] = false;
			}
		}

		return $model;
	}

	/**
	 * Updates structure item
	 *
	 * @param $new_value
	 * @param $old_value
	 * @param $type
	 *
	 * @return bool/int
	 */
	public function update_structure_item( $new_value, $old_value, $type ) {

		if ( ! isset( $old_value ) ) {
			return $new_value;
		}
		if ( is_int( $old_value ) ) {
			$page_structure = new TQB_Page_Manager( $old_value );
			$page           = $page_structure->get_page();
			if ( empty( $new_value ) && ! empty( $page ) ) {
				$page_structure->delete_page();
			}
		} elseif ( $new_value && $type != 'qna' ) {
			/**
			 * those calls... switches... :(
			 */
			$post_type_name      = tqb()->get_structure_post_type_name( $type );
			$structure_page_name = tqb()->get_style_page_name( $post_type_name );
			$post_title          = sprintf( __( 'First %s', 'thrive-quiz-builder' ), $structure_page_name );

			$data = $this->generate_first_variation( array(
				'type'       => $type,
				'page_id'    => null,
				'quiz_id'    => $this->quiz_id,
				'post_title' => $post_title,
			) );

			$new_value = $data['page_id'];
		}

		return $new_value;
	}

	/**
	 * Generate first variation and/or first page
	 *
	 * @param $model
	 *
	 * @return array
	 */
	public function generate_first_variation( $model ) {
		$variation = new TQB_Variation_Manager( $this->quiz_id, $model['page_id'] );
		if ( empty( $model['post_title'] ) ) {
			$model['post_title'] = __( 'Control', 'thrive-quiz-builder' );
		}
		if ( $model['page_id'] == 'false' ) {
			$model['page_id'] = null;
		}
		$model = $variation->validate_variation( $model );
		$model = $variation->get_default_variation_content( $model );

		if ( ! $variation->has_control( $model['page_id'] ) ) {
			$model['is_control'] = 1;
		}

		$model                   = $variation->save_variation( $model, false );
		$model['tcb_editor_url'] = TQB_Variation_Manager::get_editor_url( $model['page_id'], $model['id'] );

		return $model;
	}

	/**
	 * Update an individual structure item
	 *
	 * @param $type
	 * @param $value
	 *
	 * @return int|WP_Error
	 */
	public function update_individual_structure_item( $type, $value ) {
		$structure          = $this->get_quiz_structure_meta();
		$structure[ $type ] = $value;
		$result             = $this->update_quiz_structure_meta( $structure );

		return $result;
	}

	/**
	 * Saved the page
	 *
	 * @param $type
	 *
	 * @return int|WP_Error
	 */
	public function save_structure_item( $type ) {
		$page_structure = new TQB_Page_Manager();
		$post_id        = $page_structure->save_page( $type, $this->quiz_id );

		if ( $post_id ) {
			$this->update_individual_structure_item( $type, $post_id );
			$item = TQB_Structure_Manager::make_page( $post_id );
			$item->save_default_metas();
		}

		return $post_id;
	}

	/**
	 * Get page html to display on frontend
	 *
	 * @return array|bool
	 */
	public function get_page_content( $page_type, $points = null, $post_id = 0, $with_scripts = true, $render_shortcodes = true ) {
		$structure = $this->get_quiz_structure_meta();

		if ( ! is_numeric( $structure[ $page_type ] ) ) {
			return false;
		}

		$page_manager = new TQB_Page_Manager( $structure[ $page_type ] );
		if ( empty( $page_manager ) ) {
			return false;
		}

		$variation = $page_manager->get_page_display_html( $points );
		if ( empty( $variation[ Thrive_Quiz_Builder::FIELD_CONTENT ] ) ) {
			return false;
		}

		$tcb_fields = is_array( $variation['tcb_fields'] ) ? $variation['tcb_fields'] : unserialize( $variation['tcb_fields'] );

		if ( ! empty( $tcb_fields[ Thrive_Quiz_Builder::FIELD_INLINE_CSS ] ) ) { /* inline style rules = custom colors */
			$dynamic_content_rules = '';
			if ( ! empty( $variation[ 'dynamic_content_' . Thrive_Quiz_Builder::FIELD_INLINE_CSS ] ) && is_array( $variation[ 'dynamic_content_' . Thrive_Quiz_Builder::FIELD_INLINE_CSS ] ) ) {
				$dynamic_content_rules = tqb_merge_media_query_styles( $variation[ 'dynamic_content_' . Thrive_Quiz_Builder::FIELD_INLINE_CSS ] );
			}

			$css = apply_filters( 'tcb_custom_css', $tcb_fields[ Thrive_Quiz_Builder::FIELD_INLINE_CSS ] . $dynamic_content_rules );

			$variation[ Thrive_Quiz_Builder::FIELD_CONTENT ] .= sprintf( '<style type="text/css" class="tve_custom_style">%s</style>', stripslashes( $css ) );
		}

		if ( function_exists( 'tve_get_shared_styles' ) && ! is_editor_page_raw( true ) ) {
			/**
			 * TODO: remove the if clause after some time.
			 */

			/**
			 * We have to store global styles in a global as this method is called multiple times and at some calls they are empty
			 */
			global $shared_styles;

			$styles = tve_get_shared_styles( $variation[ Thrive_Quiz_Builder::FIELD_CONTENT ] );

			if ( ! empty( $styles ) ) {
				$shared_styles[ $page_type ] = $styles;
			}
		}

		list( $variation_type, $key ) = TQB_Template_Manager::tpl_type_key( $tcb_fields[ Thrive_Quiz_Builder::FIELD_TEMPLATE ] );
		$config = require tqb()->plugin_path( 'tcb-bridge/editor-templates/config.php' );

		$data['fonts'] = array();
		/*Include variation custom fonts*/
		if ( ! empty( $tcb_fields[ Thrive_Quiz_Builder::FIELD_CUSTOM_FONTS ] ) ) {
			foreach ( $tcb_fields[ Thrive_Quiz_Builder::FIELD_CUSTOM_FONTS ] as $variation_custom_font ) {
				$data['fonts'][] = str_replace( array( 'http:', 'https:' ), '', $variation_custom_font );
			}
		}

		/*Include config fonts*/
		if ( ! empty( $config[ $variation_type ][ $key ] ) ) {
			$config = $config[ $variation_type ][ $key ];
			if ( ! empty( $config['fonts'] ) && ! tve_dash_is_google_fonts_blocked() ) {
				foreach ( $config['fonts'] as $font ) {
					$data['fonts'][] = $font;
				}
			}
		}

		$quiz_style_meta   = TQB_Post_meta::get_quiz_style_meta( $variation['quiz_id'] );
		$template_css_file = tqb()->get_style_css( $quiz_style_meta );
		/* include also the CSS for each variation template */
		if ( ! empty( $template_css_file ) ) {
			$data['css'] = array(
				tqb()->plugin_url( 'tcb-bridge/editor-templates/css/' . TQB_Template_Manager::type( $variation['post_type'] ) . '/' . $template_css_file ),
			);
		}

		if ( ! empty( $post_id ) && is_numeric( $post_id ) ) {
			$GLOBALS['tcb_main_post_lightbox'] = get_post( $post_id );
		}

		$data[ Thrive_Quiz_Builder::FIELD_USER_CSS ]     = ( ! empty( $tcb_fields[ Thrive_Quiz_Builder::FIELD_USER_CSS ] ) ) ? $tcb_fields[ Thrive_Quiz_Builder::FIELD_USER_CSS ] : '';
		$variation[ Thrive_Quiz_Builder::FIELD_CONTENT ] = preg_replace( "/\[tqb_quiz id=(\"|')(\d+)(\"|')\]/", '', $variation[ Thrive_Quiz_Builder::FIELD_CONTENT ] );
		$data['html']                                    = $variation[ Thrive_Quiz_Builder::FIELD_CONTENT ];
		if ( $render_shortcodes ) {
			$data['html'] = do_shortcode( tve_do_wp_shortcodes( tve_thrive_shortcodes( $data['html'] ) ) );
		}
		if ( function_exists( 'tve_restore_script_tags' ) ) {
			$data['html'] = tve_restore_script_tags( $data['html'] );
		}
		$data['page_id']          = $structure[ $page_type ];
		$data['variation_id']     = $variation['id'];
		$data['quiz_id']          = $variation['quiz_id'];
		$data['quiz_user_result'] = $variation['quiz_user_result'];

		if ( true === $with_scripts ) {
			$data['optimized_assets'] = TQB_Lightspeed::get_optimized_assets( $variation );
		}

		$data['fonts'] = TQB_Lightspeed::optimize_font_imports( $data['fonts'] );

		return $data;
	}

	/**
	 * Validate questions streak
	 *
	 * @return bool
	 */
	public function validate_qna() {
		global $tgedb;

		$filters = array( 'quiz_id' => $this->quiz_id, 'start' => 1 );

		$first_question = $tgedb->get_quiz_questions( $filters, true );
		if ( empty( $first_question ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Get preview error messages in case content is missing on pages
	 *
	 * @return array
	 */
	public function get_display_availability() {
		$structure = $this->get_quiz_structure_meta();
		$pages     = tqb()->get_structure_internal_identifiers();
		$result    = array(
			'valid'          => true,
			'error_messages' => array(),
		);

		foreach ( $pages as $page ) {
			switch ( $page ) {
				case 'splash'://not mandatory
					if ( ! empty( $structure[ $page ] ) ) {

						if ( ! is_numeric( $structure[ $page ] ) ) {
							$result['error'][ $page ] = __( 'Your Splash Page is empty! Make sure you have at least one variation for it.', 'thrive-quiz-builder' );
							$result['notice']         = true;
						} else {
							$data = $this->get_page_content( $page, null, 0, false, false );
							if ( ! $data ) {
								$result['error'][ $page ] = $this->get_error( 'no_splash_design' );
								$result['notice']         = true;
							}
						}
					}
					break;
				case 'qna'://mandatory
					if ( ! $this->validate_qna() ) {
						$result['error'][ $page ] = $this->get_error( 'no_start_question' );
						$result['valid']          = false;
					}
					break;
				case 'optin': //not mandatory
					if ( isset( $structure[ $page ] ) && $structure[ $page ] ) {
						if ( ! is_numeric( $structure[ $page ] ) ) {
							$result['error'][ $page ] = __( 'Your Opt-in Page is empty! Make sure you have at least one variation.', 'thrive-quiz-builder' );
							$result['notice']         = true;
						} else {
							$data = $this->get_page_content( $page, null, 0, false, false );
							if ( ! $data ) {
								$result['error'][ $page ] = $this->get_error( 'no_optin_design' );
								$result['notice']         = true;
							}
						}
					}
					break;
				case 'results'://mandatory

					if ( ! isset( $structure[ $page ] ) ) {
						$result = array(
							'valid'          => false,
							'error_messages' => array(),
						);

						break;
					}

					/** @var TQB_Results_Page $results_page */
					$results_page          = self::make_page( $structure[ $page ] );
					$results_page_settings = $results_page->to_json();

					if ( $results_page_settings && $results_page_settings->type === 'url' && empty( $results_page_settings->links ) ) {
						$result['error'][ $page ] = $this->get_error( 'no_result_url' );
						$result['valid']          = false;
						break;
					}

					if ( ! isset( $structure[ $page ] ) || ! is_numeric( $structure[ $page ] ) ) {
						$result['error'][ $page ] = __( 'Your Results Page has not been set!', 'thrive-quiz-builder' );
						$result['valid']          = false;
					} else {
						$data = $this->get_page_content( $page, null, 0, false, false );
						if ( ! $data && $results_page_settings && $results_page_settings->type === 'page' ) {
							$result['error'][ $page ] = $this->get_error( 'no_results_design' );
							$result['valid']          = false;
						}
					}
					break;
			}
		}

		$quiz_type = TQB_Post_meta::get_quiz_type_meta( $this->quiz_id, true );

		if ( empty( $quiz_type ) ) {
			$result['error']['type'] = $this->get_error( 'no_quiz_type' );
			$result['valid']         = false;
		}

		return $result;
	}

	/**
	 * Factory for TQB_Structure_Page
	 *
	 * @param $post
	 *
	 * @return TQB_Structure_Page
	 */
	public static function make_page( $post ) {

		$post = $post instanceof WP_Post ? $post : get_post( (int) $post );

		if ( $post instanceof WP_Post && $post->post_type === TQB_Post_types::RESULTS_PAGE_POST_TYPE ) {
			$instance = new TQB_Results_Page( $post );
		} else {
			$instance = new TQB_Structure_Page( $post );
		}

		return $instance;
	}

	/**
	 * Based on $slug an error message is composed and returned
	 *
	 * @param string $slug
	 *
	 * @return string
	 */
	public function get_error( $slug ) {

		$domain            = 'thrive-quiz-builder';
		$tqb_dashboard_url = admin_url( 'admin.php?page=tqb_admin_dashboard' );
		$error_message     = __( 'Something when wrong!', 'thrive-quiz-builder' );

		switch ( $slug ) {
			case 'no_splash_design':
				$splash_editor_t      = __( 'splash editor', 'thrive-quiz-builder' );
				$splash_editor_url    = $tqb_dashboard_url . '#dashboard/page/' . $this->get_splash_page_id();
				$splash_editor_target = TQB_Product::has_access() ? '<a href="' . $splash_editor_url . '" target="_blank">' . $splash_editor_t . '</a>' : $splash_editor_t;
				$error_message        = sprintf( '<strong>%s</strong> - %s', __( "This Quiz doesn't have a Splash Page", 'thrive-quiz-builder' ), sprintf( __( 'Please create a design for your splash page in the %s.', 'thrive-quiz-builder' ), $splash_editor_target ) );
				break;
			case 'no_optin_design':
				$optin_gate_editor_url    = $tqb_dashboard_url . '#dashboard/page/' . $this->get_optin_gate_page_id();
				$optin_gate_editor_t      = __( 'optin gate editor', 'thrive-quiz-builder' );
				$optin_gate_editor_target = TQB_Product::has_access() ? '<a href="' . $optin_gate_editor_url . '" target="_blank">' . $optin_gate_editor_t . '</a>' : $optin_gate_editor_t;
				$error_message            = sprintf( '<strong>%s</strong> - %s', __( "This Quiz doesn't have an Optin Gate", 'thrive-quiz-builder' ), sprintf( __( 'Please create a design for your optin gate in the %s.', 'thrive-quiz-builder' ), $optin_gate_editor_target ) );
				break;
			case 'no_start_question':
				$tge_editor_url    = add_query_arg( array( 'tge' => 'true' ), get_permalink( $this->quiz_id ) );
				$tge_editor_t      = __( 'questions editor', 'thrive-quiz-builder' );
				$tge_editor_target = TQB_Product::has_access() ? '<a href="' . $tge_editor_url . '" target="_blank">' . $tge_editor_t . '</a>' : $tge_editor_t;
				$error_message     = sprintf( '<strong>%s</strong> - %s', __( "This Quiz doesn't have a Start Question", 'thrive-quiz-builder' ), sprintf( __( 'Please add a start question in the %s.', 'thrive-quiz-builder' ), $tge_editor_target ) );
				break;
			case 'no_result_url':
				$redirect_manager_url = $tqb_dashboard_url . '#dashboard/page/' . $this->get_results_page_id() . '/redirect-settings';
				$redirect_manager_t   = __( 'redirect manager', 'thrive-quiz-builder' );
				$redirect_manager     = TQB_Product::has_access() ? '<a href="' . $redirect_manager_url . '" target="_blank">' . $redirect_manager_t . '</a>' : $redirect_manager_t;
				$error_message        = sprintf( '<strong>%s</strong> - %s', __( "This Quiz doesn't have a Redirect", 'thrive-quiz-builder' ), sprintf( __( 'Please ensure your redirects are setup for each possible result in the %s.', 'thrive-quiz-builder' ), $redirect_manager ) );
				break;
			case 'no_results_design':
				$results_editor_url    = $tqb_dashboard_url . '#dashboard/page/' . $this->get_results_page_id();
				$results_editor_t      = __( 'results page editor', 'thrive-quiz-builder' );
				$results_editor_target = TQB_Product::has_access() ? '<a href="' . $results_editor_url . '" target="_blank">' . $results_editor_t . '</a>' : $results_editor_t;
				$error_message         = sprintf( '<strong>%s</strong> - %s', __( "This Quiz doesn't have a Results Page", 'thrive-quiz-builder' ), sprintf( __( 'Please create a design for your results page in the %s.', 'thrive-quiz-builder' ), $results_editor_target ) );
				break;
			case 'no_quiz_type':
				$splash_editor_t      = __( 'quiz type', 'thrive-quiz-builder' );
				$splash_editor_url    = $tqb_dashboard_url . '#dashboard/quiz/' . $this->quiz_id;
				$splash_editor_target = TQB_Product::has_access() ? '<a href="' . $splash_editor_url . '" target="_blank">' . $splash_editor_t . '</a>' : $splash_editor_t;
				$error_message        = sprintf( '<strong>%s</strong> - %s', __( "This Quiz doesn't have a type set.", 'thrive-quiz-builder' ), sprintf( __( 'Please choose %s.', 'thrive-quiz-builder' ), $splash_editor_target ) );

				break;
		}

		return $error_message;
	}

	/**
	 * Based on type return structure page id
	 *
	 * @param string $type
	 *
	 * @return int
	 */
	public function get_structure_page_id( $type ) {

		$id             = null;
		$structure_meta = $this->get_quiz_structure_meta();

		$id = ! empty( $structure_meta[ $type ] ) ? $structure_meta[ $type ] : null;

		return (int) $id;
	}

	/**
	 * @return int
	 */
	public function get_splash_page_id() {

		return $this->get_structure_page_id( 'splash' );
	}

	/**
	 * @return int
	 */
	public function get_optin_gate_page_id() {

		return $this->get_structure_page_id( 'optin' );
	}

	/**
	 * @return int
	 */
	public function get_results_page_id() {

		return $this->get_structure_page_id( 'results' );
	}

	/**
	 * Returns TRUE if the system should show the option page in the front-end
	 *
	 * @return bool
	 */
	public function should_show_optin_gate_page() {

		if ( ! is_user_logged_in() ) {
			return true;
		}

		return empty( TQB_Post_meta::get_quiz_page_skip_optin( $this->get_optin_gate_page_id() ) );
	}
}
