<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Thrive_Architect_Utils
 */
class Thrive_Architect_Utils {

	/**
	 * @var array Architect Elements that will be visible in the theme
	 */
	public static $theme_elements = [];

	/**
	 * Get all elements used by architect in the theme
	 *
	 * @param null $path
	 *
	 * @return array
	 */
	public static function get_architect_theme_elements( $path = null ) {
		$root_path = ARCHITECT_INTEGRATION_PATH . '/classes/elements';

		/* if there's no recursion, use the root path */
		$path = ( $path === null ) ? $root_path : $path;

		$items    = array_diff( scandir( $path ), [ '.', '..' ] );
		$elements = [];

		require_once ARCHITECT_INTEGRATION_PATH . '/classes/class-thrive-theme-element-abstract.php';

		foreach ( $items as $item ) {
			$item_path = $path . '/' . $item;
			/* if the item is a folder, enter it and do recursion */
			if ( is_dir( $item_path ) ) {
				$elements = array_merge( $elements, static::get_architect_theme_elements( $item_path ) );
			}

			/* if the item is a file, include it */
			if ( is_file( $item_path ) && substr( $item_path, - 3 ) === 'php' ) {
				$element = include $item_path;

				if ( ! empty( $element ) ) {
					$elements[ $element->tag() ] = $element;
				}
			}
		}

		return $elements;
	}

	/**
	 * Set theme element instance
	 *
	 * @param      $element_type
	 * @param null $path
	 *
	 */
	public static function set_theme_element( $element_type, $path = null ) {

		$root_path = ARCHITECT_INTEGRATION_PATH . '/classes/elements';
		$path      = ( null === $path ) ? $root_path : $path;
		$items     = array_diff( scandir( $path ), [ '.', '..' ] );

		$file_name = 'class-' . str_replace( '_', '-', $element_type ) . '-element.php';

		foreach ( $items as $item ) {
			$item_path = $path . '/' . $item;
			if ( is_dir( $item_path ) ) {
				static::set_theme_element( $element_type, $item_path );
			}

			/* if the item is what we are searching for and it's a file, include it */
			if ( $item === $file_name && is_file( $item_path ) && substr( $item_path, - 3 ) === 'php' ) {
				$element = require_once $item_path;

				static::$theme_elements[ $element->tag() ] = $element;
			}
		}
	}

	/**
	 * Selector for each element that we'll use in the theme
	 *
	 * @return array
	 */
	public static function get_architect_elements_selector() {
		$selectors = [];

		foreach ( static::$theme_elements as $element ) {
			$identifier = $element->identifier();

			if ( ! empty( $identifier ) ) {
				$selectors[ $element->tag() ] = $identifier;
			}
		}

		return $selectors;
	}

	/**
	 * Overwrite elements instances if they are available
	 *
	 * @param $instances
	 */
	public static function overwrite_elements( &$instances ) {
		if ( ! is_dir( ARCHITECT_INTEGRATION_PATH . '/classes/overrides' ) ) {
			return;
		}

		$overridden_elements = static::get_architect_theme_elements( ARCHITECT_INTEGRATION_PATH . '/classes/overrides' );

		/**
		 * @var TCB_Element_Abstract $overridden_elem
		 */
		foreach ( $overridden_elements as $overridden_elem ) {
			if ( $overridden_elem->is_available() ) {
				$instances[ $overridden_elem->tag() ] = $overridden_elem;
			}
		}
	}

	/**
	 * Get the custom field keys from the DB and collect some additional data that is used for inline shortcodes
	 *
	 * @param $post_id
	 *
	 * @return array
	 */
	public static function get_filtered_custom_fields_data( $post_id ) {
		$custom_fields       = [];
		$custom_field_links  = [];
		$custom_field_colors = [];
		$real_data           = [];
		$labels              = [];

		/* filter the CF keys and keep only those that are not protected meta */
		$filtered_cf = array_filter( thrive_theme_db()->get_custom_fields( $post_id ), static function ( $meta_key ) {
			return ! static::filter_custom_fields( false, $meta_key );
		} );

		$id_suffix = empty ( $post_id ) ? '' : '_' . $post_id;

		/* for each custom field key, collect relevant data */
		foreach ( $filtered_cf as $meta_key ) {
			/* initialize the label to be an empty string */
			$label = '';

			/* get the custom field value for this specific post */
			$meta_value = get_post_meta( $post_id, $meta_key, true );

			/* check if this key + post have ACF data [ when more CF plugins are integrated in TCB, move each integration to another function ) */
			$acf_data = TCB_Custom_Fields_Shortcode::get_post_acf_data( $meta_key, $post_id );

			/* exclude some fields from being text-type custom fields  */
			$is_inline_shortcode_cf = strpos( $meta_key, 'color' ) === false; /* don't show colors as inline shortcodes */

			/* if we have ACF data for this key, then we have to append a prefix to the key ( to mirror the TCB implementation ) */
			if ( ! empty( $acf_data ) ) {
				$meta_key = TCB_Custom_Fields_Shortcode::ACF_PREFIX . $meta_key;

				/* retrieve the label if it exists */
				if ( ! empty( $acf_data['label'] ) ) {
					$label = $acf_data['label'];
				}
			}

			/* if this is an URL custom field, add it to the array of links */
			if ( filter_var( $meta_value, FILTER_VALIDATE_URL ) ) {
				$custom_field_links[ $meta_key ] = [
					'name' => empty( $label ) ? $meta_key : $label,
					'url'  => $meta_value,
					'show' => true,
					'id'   => $meta_key,
				];
			} elseif ( ! empty( $acf_data['type'] ) && ! empty( $acf_data['value'] ) && $acf_data['type'] === 'color_picker' ) {
				$custom_field_colors[] = [
					'name'        => sanitize_title( empty( $label ) ? $meta_key : $label ),
					'label'       => empty( $label ) ? $meta_key : $label,
					'id'          => sanitize_title( str_replace( ' ', '', $meta_key ) . $id_suffix ),
					'active'      => 1,
					'custom_name' => 1,
					'color'       => $acf_data['value'],
				];
			} elseif ( $is_inline_shortcode_cf ) {
				/* the data is added to separate arrays because it's mapped directly like this to the inline shortcode config in TCB */
				$real_data[ $meta_key ]     = $meta_value;
				$labels[ $meta_key ]        = empty( $label ) ? '' : $label;
				$custom_fields[ $meta_key ] = $meta_key;
			}
		}

		return [
			'value'          => $custom_fields,
			'real_data'      => $real_data,
			'labels'         => $labels,
			/* if not empty, this has to be wrapped in an extra array to be compatible with the TCB shortcode config format */
			'links'          => empty( $custom_field_links ) ? [] : [ $custom_field_links ],
			'colors'         => empty( $custom_field_colors ) ? [] : $custom_field_colors,
			'has_acf_colors' => empty( $custom_field_colors ) ? 0 : 1,
		];
	}

	/**
	 * Prevent custom fields from being modified in the standard WP post / page edit screen
	 *
	 * @param $protected
	 * @param $meta_key
	 *
	 * @return bool
	 */
	public static function filter_custom_fields( $protected, $meta_key ) {
		if ( tve_whitelist_custom_fields( $meta_key ) ) {
			return false;
		}

		foreach ( TCB_Custom_Fields_Shortcode::$protected_fields as $key ) {
			if ( $key === $meta_key || strpos( $meta_key, $key ) === 0 ) {
				return true;
			}
		}

		return $protected;
	}

	/**
	 * Whether to show or not the reading progress bar or time
	 *
	 * @return mixed|void
	 */
	public static function show_progress_bar() {
		$secondary = thrive_template()->get_secondary();

		/* only on singular posts / pages which are inside the iframe in TTB */
		$show = ( Thrive_Utils::is_theme_template() && ( $secondary === THRIVE_POST_TEMPLATE || $secondary === THRIVE_PAGE_TEMPLATE ) ) ||
		        /* only on theme post / page templates */
		        ( is_singular() && isset( $_GET[ THRIVE_THEME_FLAG ] ) );

		return apply_filters( 'thrive_theme_show_reading_progress', $show );
	}
}
