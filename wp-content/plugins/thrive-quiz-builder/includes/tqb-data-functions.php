<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 9/2/2016
 * Time: 9:05 AM
 *
 * @package Thrive Quiz Builder
 */

/**
 * prepare the chart data for a quiz
 *
 * @param int $quiz_id
 *
 * @return array
 */
function tqb_get_chart_data( $quiz_id ) {
	// TODO: implement this
	return null;
}


/**
 * Delete all impression/conversions post meta
 */
function tqb_purge_cache() {
	// TODO: implement this after the log data implementation is done.
	return true;
}

/**
 * Get variation from variation table based on variation ID
 *
 * @param int $variation_id
 * @param int $parent_id
 *
 * @return array|bool|null|object
 */
function tqb_get_variation( $variation_id = 0, $parent_id = 0 ) {
	global $tqbdb;

	if ( empty( $variation_id ) ) {
		return false;
	}

	$variation = $tqbdb->get_page_variations( array( 'id' => $variation_id, 'parent_id' => $parent_id ), ARRAY_A );

	if ( empty( $variation ) ) {
		return false;
	}

	$post = get_post( $variation['page_id'] );

	$variation['post_type']    = $post->post_type;
	$variation['tcb_edit_url'] = TQB_Variation_Manager::get_editor_url( $variation['page_id'], $variation['id'] );

	$variation = _unserialize_fields( $variation, array( 'tcb_fields' ) );
	$variation = _assign_fields( $variation, array( 'tcb_fields' ) );

	return $variation;
}


/**
 * Unserialize fields from an array
 *
 * @param $array  array where to search the fields
 * @param $fields array fields to be unserialized
 *
 * @return array modified, containing the unserialized fields
 */
function _unserialize_fields( $array, $fields = array() ) {

	foreach ( $fields as $field ) {
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
 * assign all fields from the fields array to the main array
 *
 * @param array $array
 * @param array $fields
 *
 * @return array
 */
function _assign_fields( $array, $fields = array() ) {

	foreach ( $fields as $field ) {
		if ( ! isset( $array[ $field ] ) || ! is_array( $array[ $field ] ) ) {
			continue;
		}
		/**
		 * assign each field from the $fields in the main item array, so they can be accessed directly
		 */
		foreach ( $array[ $field ] as $k => $v ) {
			if ( ! isset( $array[ $k ] ) ) {
				$array[ $k ] = $v;
			}
		}
	}

	return $array;
}

/**
 * @param int $number_of_intervals
 * @param int $pipe_max_size
 *
 * @return array
 */
function tqb_compute_result_intervals_width( $number_of_intervals = 0, $pipe_max_size = 1000 ) {
	$width        = array();
	$with_initial = floor( ( $pipe_max_size / 10 ) / $number_of_intervals ) * 10;
	$rest         = $pipe_max_size - $number_of_intervals * $with_initial;

	foreach ( range( 0, $number_of_intervals - 1 ) as $i ) {
		$width[ $i ] = $with_initial + ( $rest > 0 ? 10 : 0 );
		$rest        -= 10;
	}

	return $width;
}

/**
 * @param array $width_arr
 * @param int   $min_val
 * @param int   $max_val
 *
 * @return array
 */
function tqb_compute_results_intervals_limits_from_with( $width_arr = array(), $min_val = 0, $max_val = 100 ) {
	$number_of_intervals = count( $width_arr );
	$val                 = $max_val - $min_val;
	$cat                 = intval( $val / $number_of_intervals );
	$rest                = $val % $number_of_intervals;
	$return              = array();
	foreach ( range( 0, $number_of_intervals - 1 ) as $i ) {
		$data = array();

		if ( $i == 0 ) {
			$data['min'] = $min_val;
		} else {
			$data['min'] = $return[ $i - 1 ]['max'] + 1;
		}
		$data['max'] = $data['min'] + $cat - 1;
		if ( $rest > 0 ) {
			$data['max'] ++;
			$rest --;
		}

		$data['width'] = $width_arr[ $i ];

		$return[ $i ] = $data;
	}

	$return[ count( $return ) - 1 ]['max'] ++; // Increment the last value!

	return $return;
}

/**
 * Get quiz absolute min - max limits
 *
 * @param int  $quiz_id
 * @param bool $is_result_page
 *
 * @return array
 */
function tqb_compute_quiz_absolute_max_min_values( $quiz_id = 0, $is_result_page = false ) {

	if ( ! $is_result_page ) {
		return array( 'min' => 0, 'max' => 0 );
	}

	$quiz_type = TQB_Post_meta::get_quiz_type_meta( $quiz_id );

	if ( $quiz_type['type'] === Thrive_Quiz_Builder::QUIZ_TYPE_PERCENTAGE ) {

		return array( 'min' => 0, 'max' => 100 );

	} elseif ( ( $quiz_type['type'] === Thrive_Quiz_Builder::QUIZ_TYPE_NUMBER || $quiz_type['type'] === Thrive_Quiz_Builder::QUIZ_TYPE_RIGHT_WRONG ) && tge()->count_questions( $quiz_id ) > 0 ) {

		$q_manager    = new TGE_Question_Manager( $quiz_id );
		$min_max_flow = $q_manager->get_min_max_flow();

		$min_max = array(
			'min' => $min_max_flow['min'],
			'max' => $min_max_flow['max'],
		);

		return $min_max;

	} else {

		return array( 'min' => false, 'max' => false );
	}
}

/**
 * Updates result page social share badge URL
 */
add_action( 'tqb_update_social_share_badge_url', 'tqb_update_result_page_social_share_badge_url', 10, 3 );

/**
 * @param $quiz_id
 * @param $image_url_for_update
 * @param $image_url_for_search
 */
function tqb_update_result_page_social_share_badge_url( $quiz_id, $image_url_for_update, $image_url_for_search ) {
	$results_page = get_posts( array(
		'post_parent' => $quiz_id,
		'post_type'   => Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_RESULTS,
	) );

	if ( empty( $results_page[0] ) ) {
		return;
	}

	if ( empty( $image_url_for_search ) ) {
		$image_url_for_search = tqb()->plugin_url( 'tcb-bridge/assets/images/share-badge-default.png' );
	}

	$page_manager = new TQB_Page_Manager( $results_page[0]->ID );
	$page_manager->update_social_share_links( $quiz_id, $image_url_for_update, $image_url_for_search );
}

add_action( 'tqb_generate_user_social_badge_link', 'tqb_update_user_social_share_badge_url', 10, 2 );

/**
 * @param $user_id
 * @param $social_badge_lnk
 */
function tqb_update_user_social_share_badge_url( $user_id, $social_badge_lnk ) {
	if ( empty( $user_id ) || empty( $social_badge_lnk ) ) {
		return;
	}

	global $tqbdb;
	$tqbdb->save_quiz_user( array(
		'id'                => $user_id,
		'social_badge_link' => $social_badge_lnk,
	) );
}

add_action( 'tqb-quiz-results-modified', 'tqb_update_result_page_intervals', 10, 3 );

/**
 * Updates the Personality Page intervals with the new results
 *
 * @param       $quiz_id
 * @param array $old_results
 * @param array $new_results
 */
function tqb_update_result_page_intervals( $quiz_id, $old_results = array(), $new_results = array() ) {
	$results_page = get_posts( array(
		'post_parent' => $quiz_id,
		'post_type'   => Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_RESULTS,
	) );

	if ( empty( $results_page[0] ) ) {
		return;
	}

	tqb_update_page_intervals( $quiz_id, $old_results, $new_results, $results_page[0]->ID );
}

add_action( 'tqb-quiz-results-modified', 'tqb_update_optin_page_intervals', 10, 3 );

/**
 * Updates the Personality Page intervals with the new results
 *
 * @param       $quiz_id
 * @param array $old_results
 * @param array $new_results
 */
function tqb_update_optin_page_intervals( $quiz_id, $old_results = array(), $new_results = array() ) {
	$optin_page = get_posts( array(
		'post_parent' => $quiz_id,
		'post_type'   => Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_OPTIN,
	) );

	if ( empty( $optin_page[0] ) ) {
		return;
	}

	tqb_update_page_intervals( $quiz_id, $old_results, $new_results, $optin_page[0]->ID );
}

/**
 * Updates in the database the personality dynamic content intervals
 *
 * @param       $quiz_id
 * @param array $old_results
 * @param array $new_results
 * @param int   $page_id
 */
function tqb_update_page_intervals( $quiz_id, $old_results = array(), $new_results = array(), $page_id = 0 ) {
	$original_old = array();
	$original_new = array();

	foreach ( $old_results as $result ) {
		$original_old[ $result['id'] ] = $result;
	}

	foreach ( $new_results as $result ) {
		$original_new[] = array( 'id' => ( ! empty( $result['id'] ) ? $result['id'] : '' ), 'text' => $result['text'] );
		if ( ! empty( $result['id'] ) ) {
			unset( $original_old[ $result['id'] ] );
		}
	}

	$variation_manager = new TQB_Variation_Manager( $quiz_id, $page_id );
	$parent_variation  = $variation_manager->get_page_variations();
	foreach ( $parent_variation as $parent ) {
		$child_variations = $variation_manager->get_page_variations( array( 'parent_id' => $parent['id'] ) );
		if ( ! empty( $child_variations ) ) {
			$old = $original_old;
			$new = $original_new;
			foreach ( $child_variations as $child ) {
				if ( ! empty( $child ['tcb_fields']['result_id'] ) ) {
					foreach ( $new as $i => $n ) {
						if ( ! empty( $n['id'] ) && $n['id'] == $child ['tcb_fields']['result_id'] ) {
							TQB_Variation_Manager::save_child_variation( array(
								'id'         => $child['id'],
								'post_title' => $n['text'],
								'parent_id'  => $child['parent_id'],
							) );
							unset( $new[ $i ] );
						}
					}
					foreach ( $old as $aux_old ) {
						if ( ! empty( $aux_old['id'] ) && $aux_old['id'] == $child ['tcb_fields']['result_id'] ) {
							TQB_Variation_Manager::delete_variation( array( 'id' => $child['id'], 'parent_id' => $child['parent_id'] ) );
						}
					}
				}
			}

			if ( ! empty( $new ) ) {
				$child = TQB_State_Manager::get_instance( $parent )->build_child_variation_arr( $parent );
				foreach ( $new as $i => $n ) {
					TQB_Variation_Manager::save_child_variation( array_merge( $child, array(
						'post_title' => $n['text'],
						'tcb_fields' => array(
							'result_id' => $n['id'],
						),
					) ) );
				}
			}
		}
	}
}

/**
 * Check for similar dynamic content
 *
 * @param $variation
 *
 * @return array|bool|null|object|void
 */
function tqb_has_similar_dynamic_content( $variation ) {
	if ( empty( $variation['post_type'] ) || ! TCB_Hooks::enable_tqb_advanced_menu( $variation['post_type'] ) ) {
		return false;
	}

	switch ( $variation['post_type'] ) {
		case Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_RESULTS:
			$searched_post_type = Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_OPTIN;
			break;
		case Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_OPTIN:
			$searched_post_type = Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_RESULTS;
			break;
		default:
			return false;
			break;
	}

	$neighbour_page = get_posts( array(
		'post_parent' => $variation['quiz_id'],
		'post_type'   => $searched_post_type,
	) );

	if ( empty( $neighbour_page[0] ) ) {
		return false;
	}

	$variation_manager = new TQB_Variation_Manager( $variation['quiz_id'], $neighbour_page[0]->ID );
	$control_variation = $variation_manager->get_page_variations( array( 'is_control' => 1, 'quiz_id' => $variation['quiz_id'] ) );

	if ( ! empty( $control_variation ) ) {
		$child_variations = $variation_manager->get_page_variations( array(
			'is_control' => 0,
			'quiz_id'    => $variation['quiz_id'],
			'parent_id'  => $control_variation['id'],
		) );
		if ( ! empty( $child_variations ) ) {
			return $child_variations;
		}
	}

	return false;
}

/**
 * Renders TQB Shortcode
 *
 * @param array $arguments
 *
 * @return string
 */
function tqb_render_shortcode( $arguments = array() ) {

	if ( ! empty( $arguments['quiz_id'] ) ) {
		$style = TQB_Post_meta::get_quiz_style_meta( $arguments['quiz_id'] );
		if ( ( ! empty( $arguments['in_tcb_editor'] ) && $arguments['in_tcb_editor'] === 'inside_tcb' ) ) {
			/**
			 * Here it enters only one time! from tqb_render_shortcode
			 */
			$data = TQB_Quiz_Manager::get_shortcode_content( $arguments['quiz_id'] );

			$html = '';
			$html .= '<div class="thrive-shortcode-config" style="display: none !important">';
			$html .= '__CONFIG_quiz_shortcode__' . json_encode( array( 'quiz_id' => $arguments['quiz_id'] ) ) . '__CONFIG_quiz_shortcode__';
			$html .= '</div>';
			$html .= '<div class="thrive-shortcode-html">';
			$html .= '<div>';
			$html .= '<div class="tqb-loading-overlay tqb-template-overlay-style-' . $style . '"><div class="tqb-loading-bullets"></div></div><div class="tqb-frontend-error-message"></div>';
			$html .= '<div class="tqb-shortcode-old-content"></div>';
			$html .= '<div class="tqb-shortcode-new-content tqb-template-style-' . $style . '">';

			if ( empty( $arguments['qna'] ) ) {
				if ( $data['page'] ) {
					$html .= str_replace( array( 'tve_empty_dropzone', 'tve_editor_main_content' ), '', $data['page']['html'] );
					foreach ( $data['page']['css'] as $css ) {
						$html .= '<link rel="stylesheet" type="text/css" href="' . $css . '">';
					}
					if ( ! tve_dash_is_google_fonts_blocked() ) {
						foreach ( $data['page']['fonts'] as $font ) {
							$html .= '<link rel="stylesheet" type="text/css" media="all" href="' . $font . '">';
						}
					}
				} elseif ( $data['question'] ) {
					$question_manager = new TGE_Question_Manager( $arguments['quiz_id'] );
					$html             .= $question_manager->get_first_question_preview( $data['question'], $arguments['quiz_id'] );
				}
			} else {
				foreach ( $data['question']['css'] as $css ) {
					$html .= '<link rel="stylesheet" type="text/css" href="' . $css . '" media="all">';
				}
			}

			$html .= '</div>';

			$html .= '</div>';

			return $html;
		}

		/**
		 * Check if quiz is not deleted
		 */
		$quiz_manager = new TQB_Quiz_Manager( $arguments['quiz_id'] );
		$quiz         = $quiz_manager->get_quiz();

		if ( $quiz === false ) {
			$error_msg = tqb_create_frontend_error_message( array( __( 'The quiz is no longer available!', 'thrive-quiz-builder' ) ) );

			return '<div class="thrive-shortcode-html"><div>' . $error_msg . '</div></div>';
		}

		if ( is_editor_page_raw( true ) ) {
			$part = '';

			$html
				= '<div class="tve_flt">
				<div class="tqb-loading-overlay tqb-template-overlay-style-' . $style . '"><div class="tqb-loading-bullets"></div></div><div class="tqb-frontend-error-message"></div>
				<div class="tqb-shortcode-old-content"></div>
				<div class="tqb-shortcode-new-content tqb-template-style-' . $style . '" data-quiz-id="' . $arguments['quiz_id'] . '">' . $part . '</div>
			</div>';

			$html = str_replace( array( 'id="tve_editor"' ), '', $html );
			$html = '<div class="thrive-shortcode-html"><div>' . $html . '</div><style>.tqb-shortcode-wrapper{pointer-events: none;}</style></div>';

			return $html;
		}

		return TQB_Shortcodes::render_quiz_shortcode( $arguments );
	}

	return '';
}


/**
 * Copy meta of an item to another
 *
 * @param int $post_id  source quiz ID
 * @param int $clone_id new quiz id
 */
function tqb_copy_meta( $post_id, $clone_id ) {
	$meta_keys = get_post_custom_keys( $post_id );
	if ( ! empty( $meta_keys ) ) {
		foreach ( $meta_keys as $meta_key ) {
			$meta_values = get_post_custom_values( $meta_key, $post_id );
			foreach ( $meta_values as $meta_value ) {
				$meta_value = maybe_unserialize( $meta_value );
				if ( $meta_key === 'tqb_quiz_structure' ) {
					$q_manager = new TQB_Quiz_Manager( $post_id );
					if ( is_string( $meta_value ) ) {
						wp_parse_str( $meta_value, $meta_value );
					}
					add_post_meta( $clone_id, $meta_key, $q_manager->get_clone_pages( $meta_value, $clone_id ) );
				} else {
					add_post_meta( $clone_id, $meta_key, $meta_value );
				}
			}
		}
	}
}
