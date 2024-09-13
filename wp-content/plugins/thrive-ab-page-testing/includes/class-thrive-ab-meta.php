<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-ab-page-testing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

/**
 * Class Thrive_AB_Meta
 *
 * Wrapper upon sets of metas
 *
 * Use this for posts before deleting the post because the WP deletes the meta and then the post;
 */
class Thrive_AB_Meta {

	const PREFIX = 'thrive_ab_';

	protected $_post_id;

	protected $_meta = array();

	protected $_variation_prefix = 'thrive_ab_';

	private $_meta_types
		= array(
			'page',
			'template',
			'variation',
		);

	public function __construct( $post_id ) {

		$this->_post_id = $post_id;
	}

	/**
	 * @return array with meta keys
	 */
	public function page_meta() {

		return array(
			'tve_landing_page',
			'thrv_lp_template_colours', // Template Colors
			'thrv_lp_template_gradients', // Template Gradients
			'thrv_lp_template_button', // Template Button Styles
			'thrv_lp_template_section', // Template Section Styles
			'thrv_lp_template_contentbox', // Template ContentBox Styles
			'thrv_lp_template_palettes', // Template Palettes
			'tve_globals',
			'thrive_tcb_post_fonts',
			'tve_page_events',
			'thrive_icon_pack',
			'tve_has_masonry',
			'tve_has_typefocus',
			'tve_has_wistia_popover',
			'tve_content_before_more',
			'tve_updated_post',
			'tve_content_more_found',
			'tve_custom_css',
			'tve_user_custom_css',
			//set for landing page but with no tpl suffix
			'tve_global_scripts',
			'tve_disable_theme_dependency',
			'_tve_header',
			'_tve_footer',
			//template
			'thrive_post_template'

		);
	}

	/**
	 * @return array with meta keys that have template name as suffix
	 */
	public function template_meta() {

		return array(
			'thrive_icon_pack',
			'thrive_tcb_post_fonts',
			'tve_content_before_more',
			'tve_content_more_found',
			'tve_custom_css',
			'tve_globals',
			'tve_has_masonry',
			'tve_has_typefocus',
			'tve_has_wistia_popover',
			'tve_page_events',
			'tve_updated_post',
			'tve_user_custom_css',
		);
	}

	public function variation_meta() {

		return array(
			'traffic',
			'status',
			'running_test_id',
		);
	}

	public function get_thrive_theme_meta() {

		if ( function_exists( '_thrive_get_meta_fields' ) ) {
			return _thrive_get_meta_fields( 'post' ); //dono why the heck is post ? :)
		}

		$meta = array(
			'thrive_meta_show_post_title',
			'thrive_meta_post_breadcrumbs',
			'thrive_meta_post_featured_image',
			'thrive_meta_post_header_scripts',
			'thrive_meta_post_body_scripts',
			'thrive_meta_post_body_scripts_top',
			'thrive_meta_post_custom_css',
			'thrive_meta_post_share_buttons',
			'thrive_meta_post_floating_icons',
			'thrive_meta_social_data_title',
			'thrive_meta_social_data_description',
			'thrive_meta_social_image',
			'thrive_meta_social_twitter_username',
			'thrive_meta_post_focus_area_top',
			'thrive_meta_post_focus_area_bottom',
		);

		return $meta;
	}

	/**
	 * if the key is not in local array then read it from db
	 *
	 * @param $key
	 *
	 * @return mixed
	 */
	public function get( $key ) {

		if ( in_array( $key, $this->template_meta() ) ) {
			$this->_meta[ $key ] = tve_get_post_meta( $this->_post_id, $key );
		} elseif ( in_array( $key, $this->variation_meta() ) ) {
			$this->_meta[ $key ] = get_post_meta( $this->_post_id, $this->_variation_prefix . $key, true );
		} else {
			$this->_meta[ $key ] = get_post_meta( $this->_post_id, $key, true );
		}

		if ( method_exists( $this, $key ) ) {
			return call_user_func( array( $this, $key ) );
		}

		return $this->_meta[ $key ];
	}

	/**
	 * Gets current post meta tve_disable_theme_dependency and covert into int
	 * - set it into local _meta[] array
	 *
	 * @return int
	 */
	public function tve_disable_theme_dependency() {

		$value = (int) get_post_meta( $this->_post_id, 'tve_disable_theme_dependency', true );

		$this->_meta['tve_disable_theme_dependency'] = $value;

		return $value;
	}

	/**
	 * Update the DB and the local value
	 *
	 * @param $key
	 * @param $value
	 *
	 * @return mixed
	 */
	public function update( $key, $value ) {

		$this->_meta[ $key ] = $value;

		if ( in_array( $key, $this->template_meta() ) ) {
			return tve_update_post_meta( $this->_post_id, $key, $value );
		}

		if ( in_array( $key, $this->variation_meta() ) ) {
			return update_post_meta( $this->_post_id, 'thrive_ab_' . $key, $value );
		}

		return update_post_meta( $this->_post_id, $key, $value );
	}

	public function init( $types ) {

		if ( is_string( $types ) ) {
			$types = array( $types );
		}

		$keys = array();

		if ( is_array( $types ) && ! empty( $types ) ) {

			foreach ( $types as $type ) {

				if ( ! in_array( $type, $this->_meta_types ) ) {
					continue;
				}

				$method = $type . '_meta';

				if ( method_exists( $this, $method ) ) {
					$keys = array_merge( $keys, call_user_func( array( $this, $method ) ) );
				}
			}
		}

		foreach ( $keys as $key ) {
			$this->get( $key );
		}

		return $this;
	}

	public function copy_to( $post_id ) {

		$new_meta = new Thrive_AB_Meta( $post_id );

		foreach ( $this->_meta as $key => $value ) {
			$new_meta->update( $key, $value );
		}

		return $new_meta;
	}

	/**
	 * Copy thrive themes page options to a variation
	 *
	 * @param $variation_id
	 *
	 * @return $this
	 */
	public function copy_thrive_theme_meta( $variation_id ) {

		$meta = $this->get_thrive_theme_meta();

		foreach ( $meta as $key ) {
			$key   = '_' . $key;
			$value = get_post_meta( $this->_post_id, $key, true );
			update_post_meta( $variation_id, $key, $value );
		}

		return $this;
	}

	/**
	 * Copy non thrive post meta to variations
	 *
	 * @param $post_id
	 *
	 * @return $this
	 */
	public function copy_non_thrive_meta( $post_id ) {

		$theme_metas = get_post_meta( $this->_post_id );

		if ( is_array( $theme_metas ) && ! empty( $theme_metas ) ) {
			foreach ( $theme_metas as $key => $value ) {
				if ( $this->maybe_thrive_meta( $key ) ) {
					continue;
				} else {
					update_post_meta( $post_id, $key, maybe_unserialize( $value[0] ) );
				}
			}
		}

		return $this;
	}

	/**
	 * Remove unused meta of variantion if it was deleted from parent
	 *
	 * @param $post_id
	 *
	 * @return $this
	 */
	public function removed_unused_non_thrive_meta( $post_id ) {
		if ( get_post_meta( $post_id, '_thumbnail_id' ) && ! get_post_meta( $this->_post_id, '_thumbnail_id' ) ) {
			delete_post_meta( $post_id, '_thumbnail_id' );
		}

		return $this;
	}

	/**
	 * Check if the key contains any of the thrive prefixes
	 *
	 * @param $key
	 *
	 * @return bool true if contains any prefix which means it is a thrive meta key
	 */
	public function maybe_thrive_meta( $key ) {

		preg_match( '/(thrive)|(tcb)|(tve)|(is_control)/', $key, $matches );

		return ! empty( $matches );
	}

	public function get_data() {

		return $this->_meta;
	}

	public function is_control() {

		return $this->_meta['is_control'] == 1;//we should have prefixed that
	}

	public function get_variation_prefix() {

		return $this->_variation_prefix;
	}

	public static function hooks() {

		$instance = new self( null );

		add_filter( 'is_protected_meta', array( $instance, 'is_protected' ), 10, 2 );
	}

	public function is_protected( $is_protected, $meta_key ) {

		$variation_metas = $this->variation_meta();

		foreach ( $variation_metas as $key ) {
			if ( $this->_variation_prefix . $key === $meta_key || $meta_key === 'is_control' ) {
				$is_protected = true;
			}
		}

		return $is_protected;
	}
}

return Thrive_AB_Meta::hooks();
