<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\ConditionalDisplay\PostTypes;

use TCB\ConditionalDisplay\Shortcode;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Conditional_Display_Group {

	const NAME = 'tve_cond_display';

	const DISPLAYS_META_KEY           = '_tve_conditional_displays';
	const LAZY_LOAD_META_KEY          = '_tve_lazy_load';
	const UNIFORM_HEIGHT_META_KEY     = '_tve_uniform_height';
	const DISPLAY_HEIGHTS_META_KEY    = '_tve_display_heights';
	const PLACEHOLDER_CSS_META_KEY    = '_tve_placeholder_css';
	const PLACEHOLDER_HTML_META_KEY   = '_tve_placeholder_html';
	const INHERIT_BACKGROUND_META_KEY = '_tve_inherit_background';

	/** @var \WP_Post */
	private $post;

	/** @var string|null */
	private $key;

	/** @var array|null */
	private $displays;

	public static function title() {
		return __( 'Conditional display group', 'thrive-cb' );
	}

	public static function register() {
		register_post_type(
			static::NAME,
			[
				'public'              => isset( $_GET[ TVE_EDITOR_FLAG ] ),
				'publicly_queryable'  => is_user_logged_in(),
				'query_var'           => false,
				'exclude_from_search' => true,
				'rewrite'             => false,
				'_edit_link'          => 'post.php?post=%d',
				'map_meta_cap'        => true,
				'label'               => static::title(),
				'capabilities'        => [
					'edit_others_posts'    => 'tve-edit-cpt',
					'edit_published_posts' => 'tve-edit-cpt',
				],
				'show_in_nav_menus'   => false,
				'show_in_menu'        => false,
				'show_in_rest'        => true,
				'has_archive'         => false,
			] );
	}

	/**
	 * @param null $key
	 *
	 * @return Conditional_Display_Group|null
	 */
	public static function get_instance( $key = null ) {
		if ( $key === null ) {
			return null;
		}

		$posts = get_posts( [
			'post_type'  => static::NAME,
			'meta_query' => [
				[
					'key'   => 'key',
					'value' => $key,
				],
			],
		] );

		if ( empty( $posts ) ) {
			$post = null;
		} else {
			$post = $posts[0];
		}

		return new self( $post, $key );
	}

	private function __construct( $post = null, $key = null ) {
		$this->post = $post;
		$this->key  = $key;
	}

	public function create() {
		$post_id = wp_insert_post( [
			'post_title'  => static::title(),
			'post_type'   => static::NAME,
			'post_status' => 'publish',
			'meta_input'  => [
				'key' => $this->key,
			],
		] );

		$this->post = get_post( $post_id );
	}

	public function update( $data ) {
		if ( $this->post === null ) {
			$this->create();
		}

		foreach ( static::get_data_attributes() as $key => $meta_key ) {
			if ( isset( $data[ $key ] ) ) {
				$this->update_meta_key( $meta_key, $data[ $key ] );
			}
		}
	}

	/**
	 * @return string
	 */
	public function get_key() {
		return $this->key;
	}

	/**
	 * @param $meta_key
	 * @param $default
	 *
	 * @return mixed|null
	 */
	public function get_meta_value( $meta_key, $default = null ) {
		return $this->post === null ? $default : get_post_meta( $this->post->ID, $meta_key, true );
	}

	/**
	 * @param $meta_key
	 * @param $meta_value
	 *
	 * @return void
	 */
	public function update_meta_key( $meta_key, $meta_value ) {
		update_post_meta( $this->post->ID, $meta_key, $meta_value );
	}

	/**
	 * Clone group and displays and return the new type
	 *
	 * @return Conditional_Display_Group
	 */
	public function clone_group( $css_id_map = [] ) {
		$new_group_key = uniqid( 'tve-dg', false );

		$new_group = new Conditional_Display_Group( null, $new_group_key );
		$new_group->create();

		$old_group_key = $this->get_key();

		foreach ( static::get_data_attributes() as $meta_key ) {
			$meta_value = $this->get_meta_value( $meta_key );

			if ( $meta_key === static::DISPLAYS_META_KEY && is_array( $meta_value ) ) {
				/* generate new keys for each display */
				foreach ( $meta_value as $index => $display ) {
					if ( $display['key'] !== 'default' ) {

						$old_display_key = $display['key'];
						$new_display_key = uniqid( '', false );

						$display['key'] = $new_display_key;

						/* replace old display key with the new one */
						$display['html'] = str_replace( $old_display_key, $new_display_key, $display['html'] );
					}

					/* replace old group key with the new one */
					$display['html'] = str_replace( $old_group_key, $new_group_key, $display['html'] );

					/* replace css ids with newly generated ones */
					$display['html'] = str_replace( array_keys( $css_id_map ), array_values( $css_id_map ), $display['html'] );

					/* replace other groups inside the display */
					$display['html'] = static::clone_conditional_groups_in_content( $display['html'], $css_id_map );

					$meta_value[ $index ] = $display;
				}
			} elseif ( \is_string( $meta_value ) ) {
				/* just replace the group key in every string */
				$meta_value = str_replace( $old_group_key, $new_group_key, $meta_value );

				/* also replace css ids */
				$meta_value = str_replace( array_keys( $css_id_map ), array_values( $css_id_map ), $meta_value );
			}

			$new_group->update_meta_key( $meta_key, $meta_value );
		}

		return $new_group;
	}

	/**
	 * Data attributes
	 *
	 * @return string[]
	 */
	public static function get_data_attributes() {
		return [
			'displays'         => static::DISPLAYS_META_KEY,
			'lazy-load'        => static::LAZY_LOAD_META_KEY,
			'uniform-heights'  => static::UNIFORM_HEIGHT_META_KEY,
			'display-heights'  => static::DISPLAY_HEIGHTS_META_KEY,
			'placeholder-css'  => static::PLACEHOLDER_CSS_META_KEY,
			'placeholder-html' => static::PLACEHOLDER_HTML_META_KEY,
			'inherit-bg'       => static::INHERIT_BACKGROUND_META_KEY,
		];
	}

	/**
	 * Return an array of displays from the group
	 *
	 * @param boolean $ordered
	 * @param boolean $is_editor_page
	 *
	 * @return array|mixed
	 */
	public function get_displays( $ordered = true, $is_editor_page = false ) {
		if ( empty( $this->post ) ) {
			$displays = [];
		} else {
			$displays = $this->get_meta_value( static::DISPLAYS_META_KEY );

			if ( empty( $displays ) ) {
				$displays = [];
			} elseif ( $ordered ) {
				$default_display_index = array_search( 'default', array_column( $displays, 'key' ), true );

				$displays[ $default_display_index ]['order'] = $is_editor_page ? - 1 : PHP_INT_MAX;

				uasort( $displays, static function ( $a, $b ) {
					return (int) $a['order'] - (int) $b['order'];
				} );

				/* re-index after sorting */
				$displays = array_values( $displays );
			}
		}

		return $displays;
	}

	/**
	 * Localize display group data
	 *
	 * @param $for_preview boolean for preview we need more data
	 *
	 * @return array
	 */
	public function localize( $for_preview = false, $is_editor_page = false ) {

		if ( empty( $GLOBALS['conditional_display_preview'] ) ) {
			$GLOBALS['conditional_display_preview'] = [];
		}

		if ( ! isset( $GLOBALS['conditional_display_preview'][ $this->key ] ) ) {
			$group_data = [
				'key'             => $this->key,
				'lazy'            => $this->has_lazy_load() ? 1 : 0,
				'displays'        => [],
				'uniform-heights' => $this->has_uniformed_heights() ? 1 : 0,
				'display-heights' => $this->get_display_heights(),
				'inherit-bg'      => $this->get_inherit_background(),
			];

			$external_resources = [
				'js'  => [],
				'css' => [],
			];

			foreach ( $this->get_displays() as $display ) {
				$display['html'] = Shortcode::parse_content( $display['html'], $is_editor_page );

				$external_resources = static::get_external_resources_for_content( $external_resources, $display['html'] );

				if ( $for_preview ) {
					$display['sets'] = [];

					$conditions = Shortcode::parse_condition_config( $display['conditions'] );

					if ( ! empty( $conditions ) ) {
						foreach ( $conditions as $set ) {
							if ( empty( $set['ID'] ) ) {
								$name = $set['label'];
							} else {
								$global_set = Global_Conditional_Set::get_instance( $set['ID'] );

								$name = $global_set === null ? '' : $global_set->get_label();
							}

							$is_valid = Shortcode::verify_set( $set );

							$display['sets'][] = [
								'is_default' => false,
								'is_checked' => $is_valid,
								'is_global'  => empty( $set['ID'] ) ? false : $set['ID'],
								'name'       => $name,
							];
						}
					} elseif ( $display['key'] === 'default' ) {
						$display['sets'][] = [ 'is_default' => true ];
					}
				}

				$group_data['displays'][] = $display;
			}

			static::enqueue_external_resources( $external_resources );

			$GLOBALS['conditional_display_preview'][ $this->key ] = $group_data;
		}

		return $GLOBALS['conditional_display_preview'][ $this->key ];
	}

	/**
	 * Check if we should load external resources for this content
	 *
	 * @param array  $external_resources
	 * @param string $content
	 *
	 * @return array
	 */
	public static function get_external_resources_for_content( $external_resources, $content ) {
		return apply_filters( 'tcb_external_resources_for_content', $external_resources, $content );
	}

	/**
	 * @param array $resources
	 */
	public static function enqueue_external_resources( $resources ) {

		foreach ( $resources['js'] as $id => $script ) {
			if ( ! wp_script_is( $id ) ) {
				tve_enqueue_script( $id, $script['url'], $script['dependencies'], false, true );
			}
		}

		foreach ( $resources['css'] as $id => $style ) {
			if ( ! wp_style_is( $id ) ) {
				tve_enqueue_style( $id, $style );
			}
		}
	}

	/**
	 * Check if the display group uses lazy load or not
	 *
	 * @return bool
	 */
	public function has_lazy_load() {
		return $this->get_meta_value( static::LAZY_LOAD_META_KEY ) === '1';
	}

	/**
	 * @return bool
	 */
	public function has_uniformed_heights() {
		return $this->get_meta_value( static::UNIFORM_HEIGHT_META_KEY ) === '1';
	}

	/**
	 * @return mixed|null
	 */
	public function get_display_heights() {
		return $this->get_meta_value( static::DISPLAY_HEIGHTS_META_KEY, [] );
	}

	/**
	 * @return mixed|null
	 */
	public function get_inherit_background() {
		return $this->get_meta_value( static::INHERIT_BACKGROUND_META_KEY, '' );
	}

	/**
	 * Check if the display group uses lazy load or not
	 *
	 * @return bool
	 */
	public function get_placeholder_css() {
		$css = '';

		if ( $this->post ) {
			$css = $this->get_meta_value( static::PLACEHOLDER_CSS_META_KEY );
		}

		if ( ! empty( $css ) ) {
			$css = \TCB_Utils::wrap_content( $css, 'style', '', 'tve-cd-placeholder-css-' . $this->key );
		}

		return $css;
	}

	public function lazy_load_placeholder() {
		$placeholder_css  = $this->get_placeholder_css();
		$placeholder_html = $this->get_meta_value( static::PLACEHOLDER_HTML_META_KEY );

		return empty( $placeholder_html ) ? '<div class="tcb-conditional-display-placeholder" data-group="' . $this->key . '">' . $placeholder_css . '</div>' : ( $placeholder_css . $placeholder_html );
	}

	/**
	 * @param array|string $groups
	 *
	 * @return void
	 */
	public static function save_groups( $groups = [] ) {
		/* content templates save the groups in the JSON format, so we must decode first */
		if ( ! empty( $groups ) && ! is_array( $groups ) ) {
			$groups = json_decode( stripslashes( $groups ), true );
		}

		if ( is_array( $groups ) ) {
			foreach ( $groups as $group ) {
				if ( isset( $group['key'] ) ) {
					$display_group = static::get_instance( (string) $group['key'] );

					if ( $display_group !== null ) {
						$display_group->update( $group );
					}
				}
			}
		}
	}

	/**
	 * Clone conditional groups from content and replace them with new ids
	 *
	 * @param string $content
	 * @param array  $css_id_map
	 *
	 * @return string
	 */
	public static function clone_conditional_groups_in_content( $content, $css_id_map = [] ) {
		/* match display group key */
		preg_match_all( '/\[' . Shortcode::NAME . " group='([^']*)'/m", $content, $matches );

		if ( $matches !== null ) {
			foreach ( $matches[1] as $display_group_key ) {
				$display_group = static::get_instance( $display_group_key );

				if ( $display_group !== null ) {
					$new_display_group = $display_group->clone_group( $css_id_map );

					$content = str_replace( $display_group_key, $new_display_group->get_key(), $content );
				}
			}
		}

		return $content;
	}
}
