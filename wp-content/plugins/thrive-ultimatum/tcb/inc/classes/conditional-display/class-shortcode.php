<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\ConditionalDisplay;

use TCB\ConditionalDisplay\PostTypes\Conditional_Display_Group;
use TCB\ConditionalDisplay\PostTypes\Global_Conditional_Set;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Shortcode {
	const NAME = 'tve_conditional_display';

	public static function add() {
		add_shortcode( static::NAME, [ __CLASS__, 'render' ] );
	}

	/**
	 * @param $attr
	 *
	 * @return string
	 */
	public static function render( $attr ) {

		$group_key = empty( $attr['group'] ) ? null : $attr['group'];

		$is_editor_page = is_editor_page_raw() || isset( $_REQUEST['tar_editor_page'] );

		/* check for admin just so we won't show displays to unwanted users */
		$during_optimization = \TCB\Lightspeed\Main::is_optimizing() && current_user_can( 'manage_options' );

		$display_group = Conditional_Display_Group::get_instance( $group_key );

		$content      = '';
		$all_displays = '';

		if ( $display_group !== null ) {
			/* We should not localize the groups during optimization or during post list / symbol render */
			if ( ! $during_optimization && static::should_localize() ) {
				/* Basically localize the data related to conditional display */
				$display_group->localize( static::is_preview(), $is_editor_page );
			}

			if ( ! $during_optimization && ( ! ( $is_editor_page || wp_doing_ajax() ) && $display_group->has_lazy_load() ) ) {
				$content = $display_group->lazy_load_placeholder();
			} else {
				foreach ( $display_group->get_displays( true, $is_editor_page ) as $display ) {
					if ( $during_optimization || static::verify_conditions( $display ) ) {
						$content = empty( $display['hide'] ) || $is_editor_page ? $display['html'] : '';

						/* If we are in preview, and we have a hide content we add a placeholder */
						if ( empty( $content ) && static::is_preview() ) {
							$content = \TCB_Utils::wrap_content( '', 'span', '', '', [ 'data-display-group' => $group_key ] );
						}

						$content = static::parse_content( $content, $is_editor_page );

						if ( $during_optimization ) {
							$all_displays .= $content;
						} else {
							break;
						}
					}
				}
			}
		}

		return $during_optimization ? $all_displays : $content;
	}

	/**
	 * There are cases when we do not want to localize the displays, such as post list render or symbol render during lazy load.
	 * @return bool
	 */
	public static function should_localize() {
		$should_localize = \TCB_Post_List::is_outside_post_list_render();

		if ( wp_doing_ajax() ) {
			$should_localize = $should_localize && \TCB_Symbol_Template::is_outside_symbol_render();
		}

		return $should_localize;
	}

	/**
	 * Parse shortcode content - do shortcode, read events, and so on
	 *
	 * @param $content
	 * @param $is_editor_page
	 *
	 * @return string
	 */
	public static function parse_content( $content, $is_editor_page = true ) {

		$content = tve_do_wp_shortcodes( $content, $is_editor_page );

		$content = tve_thrive_shortcodes( $content, $is_editor_page );

		$content = do_shortcode( $content );

		if ( ! $is_editor_page ) {
			$content = tve_restore_script_tags( $content );

			tve_parse_events( $content );

			$global_styles = tve_get_shared_styles( $content, '', false, false );

			$content = $global_styles . $content;
		}

		return $content;
	}

	/**
	 * Verify conditions for a specific display
	 *
	 * @param $display
	 *
	 * @return bool
	 */
	public static function verify_conditions( $display ) {
		$is_verified = $display['key'] === 'default';

		if ( ! $is_verified && ! empty( $display['conditions'] ) ) {
			$conditions = static::parse_condition_config( $display['conditions'] );

			/**
			 * condition = set1 || set2 || set3
			 * set1 = rule1 && rule2 && rule3
			 */
			foreach ( $conditions as $set ) {
				if ( static::verify_set( $set ) ) {
					$is_verified = true;
					/* if we have a verified set, we can stop checking the other sets */
					break;
				}
			}
		}

		return $is_verified;
	}

	/**
	 * replace single quotes with double quotes and json_decode
	 *
	 * @param $conditions
	 *
	 * @return mixed
	 */
	public static function parse_condition_config( $conditions ) {
		$conditions = str_replace( "'", '"', html_entity_decode( $conditions, ENT_QUOTES ) );

		return json_decode( $conditions, true );
	}

	public static function is_preview() {
		$is_editor = is_editor_page_raw();
		$is_ajax   = wp_doing_ajax();

		$ajax_action = empty( $GLOBALS['tve_dash_frontend_ajax_load'] ) ? false : $GLOBALS['tve_dash_frontend_ajax_load'];

		return current_user_can( 'edit_posts' ) && ! $is_editor && ( ! $is_ajax || ( $is_ajax && $ajax_action ) );
	}

	/**
	 * Verify rules from a set
	 *
	 * @param $set
	 *
	 * @return bool
	 */
	public static function verify_set( $set ) {
		if ( empty( $set['ID'] ) ) {
			$rules = $set['rules'];
		} else {
			$global_set = Global_Conditional_Set::get_instance( $set['ID'] );

			$rules = $global_set === null || empty( $global_set->get_post() ) ? [] : $global_set->get_rules();
		}


		foreach ( $rules as $rule ) {
			$is_rule_verified = static::verify_rule( $rule );
			$is_set_verified  = isset( $is_set_verified ) ? $is_set_verified && $is_rule_verified : $is_rule_verified;
		}

		return isset( $is_set_verified ) ? $is_set_verified : false;
	}

	/**
	 * Check if the rule is valid or not
	 *
	 * @param $config
	 *
	 * @return boolean
	 */
	public static function verify_rule( $config ) {
		$is_verified = false;

		if ( ! empty( $config['entity'] ) && ! empty( $config['field'] ) ) {
			$entity = Entity::get_instance( $config['entity'] );

			if ( ! empty( $entity ) ) {
				$field_value = $entity->get_field_value( $config['field'] );

				if ( empty( $config['condition']['key'] ) ) {
					$field = Field::get_instance( $config['field'] );

					if ( $field::is_boolean() ) {
						$is_verified = $field_value;
					}
				} else {
					$condition = Condition::get_instance( $config['condition']['key'], $config['condition'] );

					$is_verified = $condition->apply( [ 'field_value' => $field_value ] );
				}
			}
		}

		return $is_verified;
	}
}
