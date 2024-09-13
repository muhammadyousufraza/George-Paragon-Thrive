<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

namespace Thrive\Theme\AMP;

use Thrive_Css_Helper as CSS_Helper;

use Thrive_Section;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Styles {

	/* The template styles that we're saving have to contain one of these classes */
	const SELECTORS_TO_SAVE = [
		'#wrapper ',
		'#content ',
		'.main-content-background ',
		'.content-section ',
		'.top-section',
		'.bottom-section',
		'.thrv_header',
		'.thrv_footer',
	];

	/* These styles always have to be skipped ( even if they are allowed through TEMPLATE_STYLES_TO_SAVE ) */
	const SELECTORS_TO_FILTER = [
		'.thrv_widget_menu',
		'.tve_w_menu',
		'.tcb-post-list',
		'.tve-toggle-column',
		'.tve-toggle-grid',
	];

	/**
	 * @param $post_id
	 *
	 * @return array|mixed
	 */
	public static function get_styles( $post_id ) {
		$style  = CSS_Helper::DEFAULT_STYLE_ARRAY;
		$lp_key = tve_post_is_landing_page();

		/*
		 * For each string of styles:
		 * 1) Turn the string into an array that has the media query part as the keys and the rules as the values
		 * 1.1) Filter the rules [optional]
		 * 2) Merge the current array into the accumulator array
		 */
		foreach ( static::get_styles_for_context( $post_id, $lp_key ) as $key => $style_string ) {
			$style_to_merge = CSS_Helper::get_style_array_from_string( $style_string );

			if ( ! empty( $style_to_merge['css'] ) && in_array( $key, [ 'post', THRIVE_HEADER_SECTION, THRIVE_FOOTER_SECTION ], true ) ) {
				$style_to_merge['css'] = static::filter_post_rules( $style_to_merge['css'] );
			}

			$style = CSS_Helper::merge_styles( $style, $style_to_merge );
		}

		$style = CSS_Helper::merge_styles( $style, [ 'css' => static::get_typography_css() ] );

		$full_style_string = thrive_css_helper()
			->set_css( $style['css'] )
			->generate_style( true, true, false )
			->get_style();

		$full_style_string = static::get_global_variables() . static::get_user_custom_css( $post_id ) . static::get_base_styles() . $full_style_string;
		$full_style_string = static::filter_css_string( $full_style_string );

		$full_style_string = static::compress_css( $full_style_string );

		/* if the amp version is over 75KB, redirect to the canonical version ( the only way to reach this case is by manually pasting the URL ) */
		if (
			Main::is_amp_url() && /* only make this check on AMP pages */
			strlen( $full_style_string ) > 75000 &&
			! isset( $_GET['thrive_debug'] ) /* leave a possibility for debugging AMP pages that exceed 75KB */
		) {
			wp_redirect( Main::get_canonical_url() );
		}

		return $full_style_string;
	}

	/**
	 * Return only the styles that make sense in this context.
	 *
	 * @param $post_id
	 * @param $lp_key
	 *
	 * @return array
	 */
	public static function get_styles_for_context( $post_id, $lp_key ) {
		/* these are always returned because they apply in any context */
		$styles = [
			'post'                => static::get_post_styles( $post_id, $lp_key ),
			THRIVE_HEADER_SECTION => static::get_hf_styles( $post_id, THRIVE_HEADER_SECTION, $lp_key ),
			THRIVE_FOOTER_SECTION => static::get_hf_styles( $post_id, THRIVE_FOOTER_SECTION, $lp_key ),
		];

		/* this can be called from non-amp locations, and in that case we don't want shared styles */
		if ( Main::is_amp_url() ) {
			$styles['shared'] = static::get_shared_styles();
		}

		/* the template styles are added only if we're not on a landing page */
		if ( empty( $lp_key ) ) {
			$styles = array_merge( $styles, [
				'template'         => static::get_template_styles(),
				'template_dynamic' => static::get_template_dynamic_styles(),
				'top_section'      => static::get_section_styles( 'top' ),
				'bottom_section'   => static::get_section_styles( 'bottom' ),
			] );
		}

		return $styles;
	}

	/**
	 * Get basic css for the elements
	 *
	 * @return string
	 */
	public static function get_base_styles() {
		return Main::get_amp_file( 'assets/base.css' );
	}

	/**
	 * @param $post_id
	 *
	 * @return string
	 */
	public static function get_user_custom_css( $post_id ) {
		return get_post_meta( $post_id, 'tve_user_custom_css', true );
	}

	/**
	 * @param $post_id
	 * @param $hf_key
	 * @param $lp_key
	 *
	 * @return string
	 */
	public static function get_hf_styles( $post_id, $hf_key, $lp_key ) {
		$css = '';

		if ( empty( $lp_key ) ) {
			/* for non-LPs, get the styles from the template's HF */
			$hf = thrive_template()->get_section( $hf_key );

			/* if the header/footer is dynamic, get the css; if it's static, it is automatically included in the template css, nothing to do here */
			if ( ! empty( $hf['id'] ) ) {
				$css = static::get_hf_css( $hf['id'] );
			}
		} else {
			/* for landing pages, fetch the styles from the LP meta */
			$hf_id = get_post_meta( $post_id, '_tve_' . $hf_key, true );

			$css = static::get_hf_css( $hf_id );
		}

		return $css;
	}

	/**
	 * Get css for the symbol ID - copied from symbol PHP
	 *
	 * @param $symbol_id
	 *
	 * @return string
	 */
	public static function get_hf_css( $symbol_id ) {
		$symbol_css = trim( get_post_meta( $symbol_id, 'tve_custom_css', true ) );

		$symbol_css = apply_filters( 'tcb_symbol_css_before', $symbol_css, $symbol_id );

		return $symbol_css;
	}

	/**
	 * @param $post_id
	 * @param $lp_key
	 *
	 * @return mixed|string
	 */
	public static function get_post_styles( $post_id, $lp_key ) {

		$css_meta_key = empty( $lp_key ) ? 'tve_custom_css' : 'tve_custom_css_' . $lp_key;

		return get_post_meta( $post_id, $css_meta_key, true );
	}

	/**
	 * @return mixed|string
	 */
	public static function get_template_styles() {
		$template_style = thrive_template()->get_meta( 'style' );

		$css = empty( $template_style['css'] ) ? [] : $template_style['css'];

		$css = static::filter_template_rules( $css );

		return thrive_css_helper()
			->set_css( $css )
			->generate_style( true, true, false )
			->get_style();
	}

	/**
	 * @return string
	 */
	public static function get_template_dynamic_styles() {
		$template_style = thrive_template()->get_meta( 'style' );

		$dynamic_css = empty( $template_style['dynamic'] ) ? [] : $template_style['dynamic'];

		$dynamic_css = static::filter_template_rules( $dynamic_css );

		return thrive_css_helper()
			->set_css( $dynamic_css )
			->generate_dynamic_style()
			->get_style();
	}

	/**
	 * @param $type
	 *
	 * @return mixed
	 */
	public static function get_section_styles( $type ) {
		$section_data = thrive_template()->get_section( $type );

		$section = new Thrive_Section( $section_data['id'], $section_data );

		return $section->style( false, true );
	}

	/**
	 * Iterate through the template CSS and keep only the allowed rules
	 *
	 * @param $css
	 *
	 * @return mixed
	 */
	public static function filter_template_rules( $css ) {
		return static::filter_rules( $css, 'is_template_rule_forbidden' );
	}

	/**
	 * Eliminate some rules from the post CSS ( mostly the ones that apply to blacklisted elements ).
	 *
	 * @param $css
	 *
	 * @return mixed
	 */
	public static function filter_post_rules( $css ) {
		return static::filter_rules( $css, 'is_rule_forbidden' );
	}

	/**
	 * Delete selectors and their css text if the given callback returns true
	 *
	 * @param $css
	 * @param $should_filter_fn
	 * @param $migrate_important - if we find '!important' in the css text, prefix the selector with ':not(#s)' because we're removing '!important' later
	 *
	 * @return mixed
	 */
	public static function filter_rules( $css, $should_filter_fn, $migrate_important = true ) {
		foreach ( $css as $media => $rules_string ) {
			foreach ( CSS_Helper::get_rules_from_string( $rules_string ) as $rule ) {
				$full_original_rule = $rule['selector'] . '{' . $rule['css_text'] . '}';

				if ( static::$should_filter_fn( $rule['selector'] ) ) {
					$css[ $media ] = str_replace( $full_original_rule, '', $css[ $media ] );
				} else if ( $migrate_important && strpos( $rule['css_text'], '!important' ) !== false ) {
					$css[ $media ] = str_replace( $full_original_rule, ':not(#s) ' . ltrim( $full_original_rule ), $css[ $media ] );
				}
			}
		}

		return $css;
	}

	/**
	 * We only keep a few important rules from the template:
	 * An accepted rule must be listed in TEMPLATE_STYLES_TO_SAVE and not be listed in SELECTORS_TO_FILTER
	 *
	 * @param $selector
	 *
	 * @return bool
	 */
	public static function is_template_rule_forbidden( $selector ) {
		$forbidden = true;

		if ( static::has_at_least_one_needle_in_haystack( $selector, static::SELECTORS_TO_SAVE ) ) {
			$forbidden = static::is_rule_forbidden( $selector );
		}

		return $forbidden;
	}

	/**
	 * A rule is forbidden if it contains one of the blacklisted classes or one of the selectors that we want to filter out
	 *
	 * @param $selector
	 *
	 * @return bool
	 */
	public static function is_rule_forbidden( $selector ) {
		return static::has_at_least_one_needle_in_haystack( $selector, Parser::BLACKLISTED_CLASSES ) ||
		       static::has_at_least_one_needle_in_haystack( $selector, static::SELECTORS_TO_FILTER );
	}

	/**
	 * Check if at least one of the needles exists in the haystack
	 *
	 * @param string $haystack
	 * @param array  $needles
	 *
	 * @return bool
	 */
	public static function has_at_least_one_needle_in_haystack( $haystack, $needles = [] ) {
		$needle_exists = false;

		foreach ( $needles as $needle ) {
			if ( strpos( $haystack, $needle ) !== false ) {
				$needle_exists = true;
				break;
			}
		}

		return $needle_exists;
	}

	/**
	 * @return array
	 */
	public static function get_typography_css() {
		$styles = tcb_default_style_provider()->get_processed_styles( null, 'object', false );

		return empty( $styles['media'] ) ? [] : $styles['media'];
	}

	/**
	 * Get all the shared and global styles.
	 * Provide the content as a parameter in order for the function to only print the global styles that are currently used.
	 *
	 * @return string
	 */
	public static function get_shared_styles() {
		return strip_tags( tve_get_shared_styles( Main::$content ) );
	}

	/**
	 * Get all the global variables.
	 * This copies tve_load_global_variables() from TAR except for the <style> HTML part
	 * @return string
	 */
	public static function get_global_variables() {

		$global_colors    = get_option( apply_filters( 'tcb_global_colors_option_name', 'thrv_global_colours' ), [] );
		$global_gradients = get_option( apply_filters( 'tcb_global_gradients_option_name', 'thrv_global_gradients' ), [] );

		$global_variable_string = ':root{';

		foreach ( $global_colors as $color ) {
			$global_variable_string .= TVE_GLOBAL_COLOR_VAR_CSS_PREFIX . $color['id'] . ':' . $color['color'] . ';';
		}
		foreach ( $global_gradients as $gradient ) {
			$global_variable_string .= TVE_GLOBAL_GRADIENT_VAR_CSS_PREFIX . $gradient['id'] . ':' . $gradient['gradient'] . ';';
		}

		ob_start();

		/* Insert extra global variables in the tve_global_variables style node */
		do_action( 'tcb_get_extra_global_variables' );

		$global_variable_string .= ob_get_clean();

		$global_variable_string .= '}';

		return $global_variable_string;
	}

	/**
	 * @return string
	 */
	public static function get_boilerplate_styles() {
		return Main::get_amp_file( 'boilerplate-css.php' );
	}

	/**
	 * Remove specific things from the CSS string: '!important', the template body class, [to be continued]
	 *
	 * @param $css
	 *
	 * @return mixed
	 */
	public static function filter_css_string( $css ) {
		$replacement_array = [
			'search'  => [
				'!important', /* remove '!important' */
				'! important', /* remove '! important' xD */
				thrive_template()->body_class( false, 'string' ), /* remove the template body class */
				'dynamic_author=1#038;}', /* fix for encoded trailing ampersand */
			],
			'replace' => [
				'',
				'',
				'',
				'dynamic_author=1"); }',
			],
		];

		return str_replace( $replacement_array['search'], $replacement_array['replace'], $css );
	}

	/**
	 * Search the selectors for keywords that we can shorten ( for instance, shorten the data-css identifiers ) and add them to an array.
	 * The map is then used to shorten the stylesheet, and will also be used to make the same replacements in the post content.
	 *
	 * @param $css_string
	 *
	 * @return mixed
	 */
	public static function compress_css( $css_string ) {

		/* add the entire array of classes/strings that we want to compress to the replacements map */
		foreach ( static::$strings_to_compress as $index => $string_to_compress ) {
			/* prefix with a 't' ( short for thrive ) to reduce the chances of accidentally matching other classes */
			static::$compression_map[ $string_to_compress ] = 't' . base_convert( $index, 10, 36 );
		}

		if ( preg_match_all( '/\[data-css="(.+?)"\]/m', $css_string, $matches ) && ! empty( $matches[1] ) ) {
			$css_identifiers = $matches[1];

			/* remove duplicates and re-index by using array_values() */
			$css_identifiers = array_values( array_unique( $css_identifiers ) );

			/* add to the replacements map */
			foreach ( $css_identifiers as $index => $css_identifier ) {
				static::$compression_map[ 'data-css="' . $css_identifier . '"' ] = 'data-c="' . base_convert( $index, 10, 36 ) . '"';
			}
		}

		/* replace everything in the map with their equivalent */
		$css_string = str_replace( array_keys( static::$compression_map ), array_values( static::$compression_map ), $css_string );

		if ( Main::is_amp_url() ) {
			/* mirror the changes in the content in order for the CSS to keep applying */
			Main::$content = str_replace( array_keys( static::$compression_map ), array_values( static::$compression_map ), Main::$content );
		}

		return $css_string;
	}

	/**
	 * Store selectors that we want to shorten here in key - value pairs
	 * Example: [
	 * 'tve-u-170390ed004' => c1,
	 * 'tve-u-170390ab002' => c2,
	 *
	 * @var array
	 */
	public static $compression_map = [
		':not(#tve)' => ':not(#s)',

		/* this list is populated dynamically in compress_css() */
		/* @see compress_css */
	];

	/**
	 * The class names that we want to shorten in the stylesheet ( but also in the content ) are added here
	 * This list can be extended ( the idea is to save stylesheet size by shorterning everything we can )
	 *
	 * @param array $strings_to_compress
	 */
	public static $strings_to_compress = [
		'thrv_wrapper',
		'tcb-button-link',
		'thrv-button-group-item',
		'thrv-button',
		'tcb-button-texts',
		'tcb-button-icon',
		'thrv-content-box',
		'tve-content-box-background',
		'thrv-page-section',
		'tve-page-section-in',
		'tve-page-section-out',
		'tcb-flex-col',
		'theme-section',
		'theme-sidebar-section',
		'theme-content-section',
		'theme-top-section',
		'theme-bottom-section',
		'main-container',
		'main-content-background',
		'main-columns-separator',
		'sidebar-section',
		'content-section',
		'top-section',
		'bottom-section',
		'thrv_header',
		'thrv_footer',
		'thrive-symbol-shortcode',
		'section-background',
		'section-content',
		'thrv_symbol_',
		'thrv_symbol',
		'symbol-section-in',
		'symbol-section-out',
		'theme-has-off-screen-sidebar',
		'tve-off-screen-sidebar-trigger',
		'.tve-sidebar-close-icon',
		'visible-off-screen-sidebar',
		'off-screen-type',
		'off-screen-side',
		'thrv_icon',
		'thrive-progress-bar',
		'thrive-breadcrumbs',
		'thrive-breadcrumb-separator',
		'thrive-breadcrumb-path',
		'thrive-breadcrumb-leaf',
		'thrive-breadcrumb',
		'thrive-comments-link',
		'thrv-divider',
		'tcb-flex-row',
		'tve_image_caption',
		'tcb-post-author-picture',
		'tcb-numbered-list-index',
		'tcb-numbered-list-text',
		'tcb-numbered-list-number',
		'thrv-numbered-list-v2',
		'tcb-numbered-list',
		'thrv-numbered_list',
		'thrv-styled-list-icon-text',
		'tcb-styled-list-icon-text',
		'thrv-styled-list-item',
		'tcb-styled-list-item',
		'thrv-styled-list-icon',
		'tcb-styled-list-icon',
		'tcb-styled-list',
		'thrv-styled-list',
		'thrv_text_element',
		'theme-bottom-divider',
		'thrive-header',
		'thrive-footer',
		'tve-default-state',
		'thrive-shortcode-html',
		'thrv-columns',
		'tcb-col',
		'tcb-logo',
		'horizontal-menu-icon',
		'tcb-icon',
		'tcb-post-thumbnail',
		'tcb-style-wrap',
		'tve-elem-default-pad',
		'tve-cb',
		'hide-section',
		'thrive_show_hidden_elements',
		'ttb-editor-page',
		'tve_editor_page',
		'tve_editor',
		'tcb-permanently-hidden',
		'tcb-clear',
		'theme-has-off-screen-overlay',
		'trigger-position',
		'sidebar-off-screen-on-tablet',
		'sidebar-off-screen-on-mobile',
		'tve-sticky-sidebar',
		'tve-is-sticky',
		'single-tcb_symbol',
		'trigger-collapsed-icon',
		'trigger-expanded-icon',
		'sidebar-off-screen-on-desktop',
		'tcb-with-divider',
		'tcb-flip',
		'tcb-replaceable-placeholder',
		'tcb-window-width',
		'tve_more_tag',
		'thrv_heading',
		'thrv-rating',
		'tve_symbol_inside',
		'tve-bg-section-drag-down',
		'tve-section-full-height',
		'amp-nested-menu-close',
		'wp-caption-text',
		'thrive-dynamic-source',
		'tcb-with-icon',
		'tve_lp',
		'separator-size',
		'sidebar-size',
		'tve-off-screen-overlay-color',
		'cb_style_',
		'thrive-text-justify',
		'thrive-text-center',
		'thrive-text-right',
		'thrive-text-left',
		'tve-toc-expandable',
		'tve-toc-divider',
		'tve-vert-divider',
		'tve-toc-title-icon',
		'tve-toc-title',
		'tve-toc-content',
		'tve-toc-list',
		'tve-toc-number',
		'tve-toc-heading-level',
		'tve-toc-heading',
		'tve-toc-anchor',
		'tve-toc',
		'tve_sep',
		'show-icon',
	];
}
