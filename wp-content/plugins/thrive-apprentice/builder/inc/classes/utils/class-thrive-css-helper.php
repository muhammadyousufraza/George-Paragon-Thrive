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
 * Class Thrive_Css_Helper
 */
class Thrive_Css_Helper {

	const MEDIA_QUERY = [ '(min-width: 300px)', '(max-width: 1023px)', '(max-width: 767px)' ];

	/* indexes for the media query array from above */
	const DESKTOP_INDEX = 0;
	const TABLET_INDEX  = 1;
	const MOBILE_INDEX  = 2;

	/**
	 * @var array
	 */
	private $css = [];
	/**
	 * @var array
	 */
	private $fonts = [];

	/**
	 * @var string
	 */
	private $style = '';
	/**
	 * @var array
	 */
	private $items = [];

	/**
	 * Array with the default values for the style components - fonts, css, dynamic css.
	 *
	 * @var array
	 */
	const DEFAULT_STYLE_ARRAY
		= [
			'fonts'   => [],
			'css'     => [],
			'dynamic' => [],
		];

	/**
	 * By passing the current object, the class collects the post meta ID ( so it knows where to look for the styles ).
	 *
	 * @param       $source
	 * @param array $options
	 */
	public function prepare_templates( $source, $options = [] ) {
		/**
		 * @var Thrive_Template[]
		 */
		$templates = [];

		/* check the type of the object and collect the template objects and/or the meta IDs */
		switch ( get_class( $source ) ) {
			case Thrive_Skin::class:
				/* @var $source Thrive_Skin */
				$templates = $source->get_templates( 'object', ! empty( $options['default'] ) );
				break;
			case Thrive_Template::class:
				/* @var $source Thrive_Template */
				$templates = [ $source ];
				break;
			case Thrive_Layout::class:
			case Thrive_Typography::class:
				/* @var $source Thrive_Layout|Thrive_Typography */
				$this->items = [ $source ];
				break;
			default:
				break;
		}

		/* If we have an object of Thrive_Section type or subtypes */
		if ( is_a( $source, Thrive_Section::class ) ) {
			$this->items = [ $source ];
		}

		/* Get an array of IDs that contains the template ID and the section IDs for each template.  */
		foreach ( $templates as $template ) {
			/* @var $template Thrive_Template */

			$this->items[] = $template;

			foreach ( $template->get_sections() as $section ) {
				$this->items[] = $section;
			}
		}

		//filter duplicates objects from the array
		$this->items = array_filter( $this->items, function ( $object ) {
			static $ids = [];
			if ( in_array( $object->ID, $ids, true ) ) {
				return false;
			}
			$ids[] = $object->ID;

			return true;
		} );
	}

	/**
	 * Filter function provided in case extra processing of the style string is needed.
	 *
	 * @param $callback
	 *
	 * @return Thrive_Css_Helper $this
	 */
	public function filter( $callback ) {
		if ( is_callable( $callback ) ) {
			$this->style = $callback( $this->style );
		}

		return $this;
	}

	/**
	 * Parse the css and fonts.
	 * Get the styles for each item ID and merge them together based on their types.
	 *
	 * @param bool $include_dynamic
	 *
	 * @return mixed|string
	 */
	private function parse( $include_dynamic = false ) {

		foreach ( $this->items as $item ) {
			if ( method_exists( $item, 'get_meta' ) ) {
				$style = $item->get_meta( 'style' );
			}

			if ( ! empty( $style['fonts'] ) ) {
				$this->read_fonts( $style['fonts'] );
			}

			if ( ! empty( $style['css'] ) ) {
				$this->read_css( $style['css'] );
			}

			if ( $include_dynamic && ! empty( $style['dynamic'] ) ) {
				/* the dynamic is parsed and added after the normal css, so it can apply over the 'static' images that are saved by default */
				$this->read_css( $style['dynamic'] );
			}
		}

		return $this;
	}

	/**
	 * Get all CSS @import rules stored in associated objects
	 *
	 * @return array
	 */
	public function get_css_imports() {
		$this->parse( false );

		return $this->fonts;
	}

	/**
	 * Go through each css media and stringify the array of medias.
	 */
	public function get_prepared_css() {
		$style = '';

		/* iterate over media so we know it will be written in this order */
		foreach ( static::MEDIA_QUERY as $media ) {
			if ( ! empty( $this->css[ $media ] ) ) {
				$style .= '@media ' . $media . '{' . $this->css[ $media ] . '}';
			}
		}

		$this->css = Thrive_Utils::remove_extra_spaces( $style );
	}

	/**
	 * Filter fonts, remove duplicates, and stringify the array of fonts.
	 */
	public function get_unique_fonts() {
		if ( empty( $this->fonts ) || ! is_array( $this->fonts ) ) {
			$this->fonts = '';
		} else {
			$this->fonts = TCB_Utils::merge_google_fonts( $this->fonts );

			$this->fonts = implode( '', $this->fonts );
		}
	}

	/**
	 * Read the fonts and the css, then stringify and do some processing.
	 *
	 * @param boolean $replace_global_variables
	 * @param boolean $include_dynamic
	 * @param boolean $include_fonts whether or not to include @import rules
	 *
	 * @return Thrive_Css_Helper $this
	 */
	public function generate_style( $replace_global_variables = false, $include_dynamic = false, $include_fonts = true ) {
		$this->parse( $include_dynamic );

		if ( $include_fonts ) {
			/* stringify the array of fonts and the array of css */
			$this->get_unique_fonts();
		} else {
			$this->fonts = '';
		}
		$this->get_prepared_css();

		if ( $include_dynamic ) {
			/* call do_shortcode on the dynamic css. Regarding the ( string ) cast, it's already a string, but the IDE doesnt know this */
			$this->css = do_shortcode( (string) $this->css );
		}

		$this->style = $this->fonts . $this->css;

		/* Call the function only if we are not in the tar editor */
		if ( ! TCB_Utils::in_editor_render( true ) ) {
			$this->style = tve_prepare_global_variables_for_front( $this->style, $replace_global_variables );
		}

		$this->style = Thrive_Utils::remove_extra_spaces( $this->style );

		if ( class_exists( 'TCB\Lightspeed\Fonts', false ) ) {
			$this->style = TCB\Lightspeed\Fonts::parse_google_fonts( $this->style );
		}

		return $this;
	}

	/**
	 * Read the dynamic css, then stringify it and call do_shortcode() on it.
	 * This function is useful when you only want to generate the dynamic css and nothing else ( this is the most common use case )
	 * If you want to generate the css, fonts and dynamic at the same time, don't use this! use generate_style() with $include_dynamic = true
	 *
	 * @return Thrive_Css_Helper $this
	 */
	public function generate_dynamic_style() {
		$this->parse_dynamic();

		/* stringify the array of css */
		$this->get_prepared_css();

		$this->style = $this->css;

		/*
		 * This is caused by the gravatar URL being escaped inside the get_avatar() WP function called from TCB_Post_List_Author_Image
		 * I am not sure what causes the trailing '&' or why it deletes the existing ');', but this is a fix that solves the problem for now.
		 * todo: investigate what actually causes this
		 */
		$this->style = str_replace( 'dynamic_author=1#038;}', 'dynamic_author=1"); }', $this->style );

		$this->style = do_shortcode( $this->style );

		return $this;
	}

	/**
	 * Wrap the current style (if it's not empty) in the identifier (if it exists).
	 *
	 * @param $identifier
	 *
	 * @return string
	 */
	public function maybe_wrap( $identifier ) {
		$style = $this->style;

		if ( ! empty( $this->style ) && ! empty( $identifier ) ) {
			$style = TCB_Utils::wrap_content( $this->style, 'style', $identifier . '-css', '', [ 'type' => 'text/css' ] );
		}

		return $style;
	}

	/**
	 * @return string
	 */
	public function get_style() {
		return $this->style;
	}

	/**
	 * @param string
	 *
	 * @return Thrive_Css_Helper $this
	 */
	public function set_css( $css ) {
		$this->css = $css;

		return $this;
	}

	/**
	 * @param string
	 *
	 * @return Thrive_Css_Helper $this
	 */
	public function set_fonts( $fonts ) {
		$this->fonts = $fonts;

		return $this;
	}

	/**
	 * Parse the dynamic css.
	 * Get the styles for each item ID and merge them together.
	 *
	 * @return mixed|string
	 */
	private function parse_dynamic() {
		foreach ( $this->items as $item ) {
			if ( method_exists( $item, 'get_meta' ) ) {
				$style = $item->get_meta( 'style' );
			}

			if ( ! empty( $style['dynamic'] ) ) {
				$this->read_css( $style['dynamic'] );
			}
		}

		return $this;
	}

	/**
	 * Read css from each media and append it to the existing
	 *
	 * @param $css
	 */
	private function read_css( $css ) {
		if ( ! empty( $css ) && is_array( $css ) ) {
			foreach ( $css as $media => $style ) {

				if ( empty( $this->css[ $media ] ) ) {
					$this->css[ $media ] = '';
				}

				$this->css[ $media ] .= $style;
			}
		}
	}

	/**
	 * Combine the existing fonts with the fonts from the parameter.
	 *
	 * @param $fonts
	 */
	private function read_fonts( $fonts = [] ) {
		if ( is_array( $fonts ) ) {
			$this->fonts = array_merge( $this->fonts, $fonts );
		}
	}

	/**
	 * Merge the styles of second parameter into the styles of the first - css, fonts and dynamic css.
	 *
	 * @param array        $style
	 * @param array|string $style_to_add
	 *
	 * @return array
	 */
	public static function merge_styles( $style, $style_to_add ) {
		if ( is_string( $style_to_add ) ) {
			$style_to_add = static::get_style_array_from_string( $style_to_add );
		}
		/* make sure there are default values for css, fonts and dynamic css */
		$style        = array_merge( static::DEFAULT_STYLE_ARRAY, $style );
		$style_to_add = array_merge( static::DEFAULT_STYLE_ARRAY, $style_to_add );

		foreach ( $style_to_add['css'] as $media => $css ) {
			if ( empty( $style['css'][ $media ] ) ) {
				$style['css'][ $media ] = '';
			}

			$style['css'][ $media ] .= $css;
		}

		$style['fonts'] = array_unique( array_merge( $style['fonts'], $style_to_add['fonts'] ) );

		foreach ( $style_to_add['dynamic'] as $media => $dynamic_css ) {
			if ( empty( $style['dynamic'][ $media ] ) ) {
				$style['dynamic'][ $media ] = '';
			}

			$style['dynamic'][ $media ] .= $dynamic_css;
		}

		return $style;
	}

	/**
	 * Accepts a string of styles that can contain media rules and font rules
	 * Parses the string into an array of css and fonts
	 *
	 * @param $style
	 *
	 * @return array
	 */
	public static function get_style_array_from_string( $style ) {
		return [
			'css'   => static::get_css_from_string( $style ),
			'fonts' => static::get_fonts_from_string( $style ),
		];
	}

	/**
	 * Parse the css from the string and add it to an array with separate media keys
	 *
	 * @param $style
	 *
	 * @return array
	 */
	public static function get_css_from_string( $style ) {
		$css = [];

		/* the extra '\}' is there in order not to accidentally grab rules that are outside media queries */
		if ( preg_match_all( '/media\s?\(([^)]*)\)\s?\{([^@]*)\}(\}|@|$)/mU', $style, $media_matches, PREG_SET_ORDER ) ) {

			foreach ( $media_matches as $media ) {
				if ( ! empty( $media[1] ) && ! empty( $media[2] ) ) {
					$media_query_key = '(' . $media[1] . ')';

					$css[ $media_query_key ] = $media[2];

					if ( ! empty( $media[3] ) && $media[3] === '}' ) {
						$css[ $media_query_key ] .= '}';
					}
				}
			}

			/* add all the rules that are outside media queries inside '300 min-width' */
			if ( preg_match( '/}{2}([^@]*)$/m', $style, $matches ) && isset( $css[ static::MEDIA_QUERY[ static::DESKTOP_INDEX ] ] ) ) {
				if ( ! empty( $matches[1] ) ) {
					$css[ static::MEDIA_QUERY[ static::DESKTOP_INDEX ] ] .= $matches[1];
				}
			}
		}

		return $css;
	}

	/**
	 * Parse the fonts and add them to an array
	 *
	 * @param $style
	 *
	 * @return array
	 */
	public static function get_fonts_from_string( $style ) {
		$fonts = [];

		if ( preg_match_all( '/(@import[^;]*;)/m', $style, $font_matches, PREG_SET_ORDER ) ) {
			foreach ( $font_matches as $media ) {
				if ( ! empty( $media[1] ) ) {
					/* duplicates are handled elsewhere */
					$fonts[] = $media[1];
				}
			}
		}

		return $fonts;
	}

	/**
	 * Parse a CSS string containing only simple rules (no @media, @imports etc) into an array of individual rules
	 * each CSS rule is returned with the following keys
	 *      string $selector rule's CSS selector
	 *      string $css_text rule's CSS text
	 *
	 * @param string $style raw css style
	 *
	 * @return array|WP_Error $result array with selectors / cssText, WP_Error in case $style is invalid (contains @media queries)
	 */
	public static function get_rules_from_string( $style ) {
		if ( strpos( $style, '@import' ) !== false || strpos( $style, '@media' ) !== false ) {
			return new WP_Error( 'thrive_invalid_style', 'Invalid style specified. Did not expect @media / @import' );
		}

		$rules = [];
		preg_match_all( '#(.+?){(.+?)}#s', $style, $matches );
		if ( $matches ) {
			foreach ( $matches[1] as $index => $selector ) {
				$rules [] = [
					'selector' => $selector,
					'css_text' => $matches[2][ $index ],
				];
			}
		}

		return $rules;
	}

	/**
	 * Build a CSS string from an array of rules
	 *
	 * @param array $rules
	 *
	 * @return string
	 * @see Thrive_Css_Helper::get_rules_from_string()
	 *
	 */
	public static function build_string_from_rules( $rules ) {
		return implode( '', array_map( static function ( $rule ) {
			return "{$rule['selector']}{{$rule['css_text']}}";
		}, $rules ) );
	}

	/**
	 * Style setter
	 *
	 * @param string $style_string
	 *
	 * @return $this
	 */
	public function set_style( $style_string ) {
		$this->style = $style_string;

		return $this;
	}
}

if ( ! function_exists( 'thrive_css_helper' ) ) {
	/**
	 * @param $source
	 * @param $options array
	 *
	 * @return Thrive_Css_Helper
	 */
	function thrive_css_helper( $source = null, $options = [] ) {

		$instance = new Thrive_Css_Helper();

		if ( $source !== null ) {
			$instance->prepare_templates( $source, $options );
		}

		return $instance;
	}
}
