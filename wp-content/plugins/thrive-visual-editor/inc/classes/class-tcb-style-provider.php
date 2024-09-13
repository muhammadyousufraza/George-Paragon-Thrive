<?php

/**
 * Default TAr style class. Extend this to modify default style functionality
 *
 * Class TCB_Style_Provider
 */
class TCB_Style_Provider {

	/**
	 * Get an array of default style specifications
	 *
	 * @return array
	 */
	protected function defaults() {
		$defaults = array(
			'link'           => array(
				/* needs to be quite specific. some 3rd party themes are really specific */
				/* also need to make the selector from _typography.scss */
				'selector'    => ':not(.inc) .thrv_text_element a:not(.tcb-button-link), :not(.inc) .tcb-styled-list a, :not(.inc) .tcb-numbered-list a, .tve-input-option-text a',
				'lp_selector' => '#tcb_landing_page .thrv_text_element a:not(.tcb-button-link), #tcb_landing_page .tcb-styled-list a, #tcb_landing_page .tcb-numbered-list a, #tcb_landing_page .tve-input-option-text a',
			),
			'p_link'         => array(
				'selector'    => ':not(.inc) .thrv_text_element p a:not(.tcb-button-link):not(.thrv-typography-link-text)',
				'lp_selector' => '#tcb_landing_page .thrv_text_element p a:not(.tcb-button-link)',
			),
			'heading_link'   => array(
				'selector'    => ':not(#tve) :not(.inc) .thrv_text_element h1 a:not(.tcb-button-link), :not(#tve) :not(.inc) .thrv_text_element h2 a:not(.tcb-button-link), :not(#tve) :not(.inc) .thrv_text_element h3 a:not(.tcb-button-link), :not(#tve) :not(.inc) .thrv_text_element h4 a:not(.tcb-button-link), :not(#tve) :not(.inc) .thrv_text_element h5 a:not(.tcb-button-link), :not(#tve) :not(.inc) .thrv_text_element h6 a:not(.tcb-button-link)',
				'lp_selector' => ':not(.inc) #tcb_landing_page h1 a:not(.tcb-button-link), :not(.inc) #tcb_landing_page h2 a:not(.tcb-button-link), :not(.inc) #tcb_landing_page h3 a:not(.tcb-button-link), :not(.inc) #tcb_landing_page h4 a:not(.tcb-button-link), :not(.inc) #tcb_landing_page h5 a:not(.tcb-button-link), :not(.inc) #tcb_landing_page h6 a:not(.tcb-button-link)',
			),
			'li_link'        => array(
				'selector'    => ':not(.inc) .tcb-styled-list a, :not(.inc) .tcb-numbered-list a',
				'lp_selector' => '#tcb_landing_page .tcb-styled-list a, :not(.inc) .tcb-numbered-list a',
			),
			'plaintext_link' => array(
				'selector'    => ':not(.inc) .tcb-plain-text a:not(.tcb-button-link)',
				'lp_selector' => '#tcb_landing_page .tve_lp .tcb-plain-text a:not(.tcb-button-link)',
			),
			'p'              => [
				'selector'    => '.tcb-style-wrap p',
				'lp_selector' => '#tcb_landing_page p',
			],
			'ul'             => array(
				'selector'    => '.tcb-style-wrap ul:not([class*="menu"]), .tcb-style-wrap ol',
				'lp_selector' => '#tcb_landing_page ul:not([class*="menu"]), #tcb_landing_page ol',
			),
			'li'             => array(
				'selector'    => '.tcb-style-wrap li:not([class*="menu"])',
				'lp_selector' => '#tcb_landing_page li:not([class*="menu"])',
			),
			'pre'            => [
				'selector'    => '.tcb-style-wrap pre',
				'lp_selector' => '#tcb_landing_page pre',
			],
			'blockquote'     => [
				'selector'    => '.tcb-style-wrap blockquote',
				'lp_selector' => '#tcb_landing_page blockquote',
			],
			'plaintext'      => [
				'selector'    => '.tcb-plain-text',
				'lp_selector' => '.tve_lp .tcb-plain-text',
			],
		);

		foreach ( range( 1, 6 ) as $level ) {
			$defaults[ 'h' . $level ] = [
				'selector'    => '.tcb-style-wrap h' . $level,
				'lp_selector' => '#tcb_landing_page h' . $level,
			];
		}

		/**
		 * When the Typography is inherited from TTB, we need to update the selectors and make them stronger
		 */
		return apply_filters( 'tcb_typography_inherit', $defaults );
	}

	/**
	 * Get list of default TAr styles
	 *
	 * @return array
	 */
	public function get_styles() {
		return $this->prepare_styles( $this->read_styles() );
	}

	/**
	 * Prepares the list of styles taken out of the DB, making sure it's valid, it contains all needed selectors etc
	 *
	 * @param array $styles
	 *
	 * @return array
	 */
	protected function prepare_styles( $styles ) {
		$styles = (array) $styles;

		/* ensure backwards compatibility -> @import rules are now stored in a single array key instead of scattered for each type */
		$imports = isset( $styles['@imports'] ) ? $styles['@imports'] : [];
		foreach ( $styles as $style_type => &$style_data ) {
			if ( isset( $style_data['@imports'] ) ) {
				$imports = array_merge( $imports, $style_data['@imports'] );
				unset( $style_data['@imports'] );
				$found = true;
			}
		}
		if ( isset( $found ) ) {
			$styles['@imports'] = TCB_Utils::merge_google_fonts( $imports );
			$this->save_styles( $styles );
		}
		/* end backwards compat */
		$defaults = $this->defaults();

		$styles             = array_intersect_key( $styles, $defaults ); // cleanup anything unnecessary
		$styles['@imports'] = $imports;

		foreach ( $defaults as $type => $style ) {
			$styles[ $type ]['selector']    = $style['selector'];
			$styles[ $type ]['lp_selector'] = $style['lp_selector'];
		}

		return (array) $styles;
	}

	/**
	 * Read styles from data source.
	 * Default: read them from wp_options
	 *
	 * When extending the class, this method should be overridden
	 *
	 * @return array formatted list of styles
	 */
	protected function read_styles() {
		$styles = get_option( 'tve_default_styles', [] );

		/* li gets the same styles as <p> at first, but it's stylable individually in some places */
		if ( empty( $styles['li'] ) ) {
			$styles['li'] = isset( $styles['p'] ) ? $styles['p'] : [];
		}

		/* For the new links we need to set the styles previously added on the Link element */
		if ( empty( $styles['p_link'] ) ) {
			$styles['p_link'] = isset( $styles['link'] ) ? $styles['link'] : [];
		}

		if ( empty( $styles['li_link'] ) ) {
			$styles['li_link'] = isset( $styles['link'] ) ? $styles['link'] : [];
		}

		if ( empty( $styles['plaintext_link'] ) ) {
			$styles['plaintext_link'] = isset( $styles['link'] ) ? $styles['link'] : [];
		}

		return $styles;
	}

	/**
	 * Saves styles to the database
	 *
	 * @param array $styles
	 */
	public function save_styles( $styles ) {
		update_option( 'tve_default_styles', $styles );
	}

	/**
	 * Process raw styles, building and replacing selectors
	 *
	 * @param array  $raw_styles    raw styles collected from DB
	 * @param string $return        Can be one of 'string', 'object' used to control how to return the results
	 * @param bool   $include_fonts Whether or not to include @import rules in the output
	 *
	 * @return array|string array with @imports and media keys, each of them a string
	 *                      or a string containing all CSS
	 */
	public function get_processed_styles( $raw_styles = null, $return = 'object', $include_fonts = true ) {
		$raw_styles = $this->prepare_styles( isset( $raw_styles ) ? $raw_styles : $this->read_styles() );
		$data       = array(
			'@imports' => isset( $raw_styles['@imports'] ) ? $raw_styles['@imports'] : [],
			'media'    => [],
		);

		unset( $raw_styles['@imports'] );

		foreach ( $raw_styles as $element_type => $style_data ) {
			$selector        = $style_data['selector'];
			$suffix_selector = str_replace( ', ', '%suffix%, ', $selector ) . '%suffix%';
			unset( $style_data['selector'], $style_data['@imports'], $style_data['lp_selector'] );
			foreach ( $style_data as $media_key => $css_rules ) {
				$css_rules = implode( '', $css_rules );
				/**
				 * Default styles should NEVER generate !important. (This was added by :hover for links from typography)
				 */
				$css_rules = str_replace( [ ' !important', '!important' ], '', $css_rules );

				/* make sure suffix selectors are built correctly */
				$css_rules = preg_replace_callback( '#__el__([^{]{2,}?){#m', function ( $matches ) use ( $suffix_selector ) {
					return str_replace( '%suffix%', rtrim( $matches[1] ), $suffix_selector ) . ' {';
				}, $css_rules );

				$data['media'][ $media_key ] = ( isset( $data['media'][ $media_key ] ) ? $data['media'][ $media_key ] : '' ) . str_replace( '__el__', $selector, $css_rules );
			}
		}

		if ( tve_dash_is_google_fonts_blocked() ) {
			$data['@imports'] = [];
		}

		$media_query_order = array_flip( array(
			'(min-width: 300px)',
			'(max-width: 1023px)',
			'(max-width: 767px)',
		) );

		/* Make sure the media queries are in the correct order */
		uksort( $data['media'], static function ( $key1, $key2 ) use ( $media_query_order ) {
			if ( ! isset( $media_query_order[ $key2 ] ) ) {
				return 1;
			}
			if ( ! isset( $media_query_order[ $key1 ] ) ) {
				return - 1;
			}

			return $media_query_order[ $key1 ] - $media_query_order[ $key2 ];
		} );

		if ( $return === 'string' ) {
			$css = $include_fonts ? implode( "", $data['@imports'] ) : '';
			foreach ( $data['media'] as $media => $css_str ) {
				$css .= "@media {$media} { $css_str }";
			}

			return $css;
		}

		return $data;
	}

	/**
	 * @return array
	 */
	public function get_css_imports() {
		$styles = $this->read_styles();

		return isset( $styles['@imports'] ) ? $styles['@imports'] : [];
	}
}
