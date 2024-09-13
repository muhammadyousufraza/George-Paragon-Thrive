<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

namespace Thrive\Theme\AMP;

use Thrive_DOM_Helper as DOM_Helper;

use DOMDocument;
use DOMElement;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Parser
 * @package Thrive\Theme\AMP
 */
class Parser {

	const PARSER_PATH = Main::AMP_PATH . 'classes/parsers/';

	const EARLY_PARSE_HOOK = 'thrive_theme_amp_early_parsed_content';
	const PARSE_HOOK       = 'thrive_theme_amp_parsed_content';

	const PARSER_NAMESPACE = 'Parsers';

	/* we delete the elements that have these classes */
	const BLACKLISTED_CLASSES = [
		'tcb-pagination',
		'thrv_social',
		'thrv_lead_generation',
		'thrv-search-form',
		'thrv-contact-form',
		'thrv-pricing-table',
		'thrv_tabs_shortcode',
		'thrv-tabbed-content',
		'thrv_toggle',
		'thrive-progress-bar',
		/* this is a social sharing plugin ( Sassy Social Share Premium ) that adds all sorts of event attributes on HTML */
		'heateor_sssp_sharing_container',
	];

	/* if we find these classes, we have to add 'width:100%' to them because they usually have fixed widths in their style attribute */
	const RESPONSIVE_TARGET_CLASSES = [
		'tcb-window-width',
	];

	/*
	 * Initialize the element parsers - they should hook into the parsing hook in order to modify the content
	 */
	public static function init() {

		$dir = static::PARSER_PATH;

		/* iterate through the parser folder and include each file */
		foreach ( scandir( $dir ) as $file ) {
			if ( in_array( $file, [ '.', '..' ] ) ) {
				continue;
			}

			require_once $dir . $file;

			/* for each file, dynamically call the init function of the class */
			if ( preg_match( '/class-(.*).php/m', $file, $m ) && ! empty( $m[1] ) ) {
				$element = ucfirst( $m[1] );
				$class   = __NAMESPACE__ . '\\' . static::PARSER_NAMESPACE . '\\' . $element;

				if ( method_exists( $class, 'init' ) ) {
					$class::init();
				}
			}
		}
	}

	/**
	 * Check the content for invalid elements, then modify some elements so they are compatible with AMP.
	 *
	 * @param string $content
	 *
	 * @return string $content
	 */
	public static function parse_content( $content ) {
		if ( empty( $content ) || ! extension_loaded( 'dom' ) ) {
			return '';
		}

		/* @var DOMDocument $dom */
		$dom = DOM_Helper::initialize_dom_document( $content );

		if ( $dom ) {
			/**
			 * Let the element parsers know that they can do their own early parsing on the DOMDocument
			 *
			 * @param DOMDocument $dom
			 */
			do_action( static::EARLY_PARSE_HOOK, $dom );

			static::parse_comments( $dom );

			static::fix_responsiveness( $dom );

			/* this relies on <img> tags still existing, it has to be done before the parse action */
			static::parse_logos( $dom );

			/**
			 * Let the element parsers know that they can do their own parsing on the DOMDocument
			 *
			 * @param DOMDocument $dom
			 */
			do_action( static::PARSE_HOOK, $dom );

			static::eliminate_invalid_elements( $dom );
			static::eliminate_invalid_attributes( $dom );

			static::optimize_inline_styles( $dom );

			$content = DOM_Helper::get_content_from_dom( $dom );

			$content = str_replace( array_keys( static::REPLACE_IN_CONTENT ), array_values( static::REPLACE_IN_CONTENT ), $content );

			/* re-write this when we need more replacement patterns, but for now this is enough */
			$content = preg_replace( '/zoom: ?.+;/mU', '', $content ); /* replace 'zoom: 1;', 'zoom:0.34;', etc ( the zoom attribute is not allowed ) */

			$content = str_replace( static::REMOVE_FROM_CONTENT, '', $content );
		}

		return $content;
	}

	/**
	 * Iterate through some target classes and apply 'width:100%' to them
	 * The reason is that these classes have fixed widths that are not responsive.
	 *
	 * @param DOMDocument $dom
	 */
	public static function fix_responsiveness( $dom ) {
		foreach ( $dom->getElementsByTagName( 'div' ) as $node ) {
			/* @var DOMElement $node */
			foreach ( static::RESPONSIVE_TARGET_CLASSES as $class ) {
				if ( DOM_Helper::has_class( $node, $class ) ) {
					$node->setAttribute( 'style', 'width:100%' );
				}
			}
		}
	}

	/**
	 * Hide some stuff that we're not ready to display yet, such as social shares, lead generation forms, etc
	 * Also eliminate incompatible HTML tags
	 *
	 * @param DOMDocument $dom
	 */
	public static function eliminate_invalid_elements( $dom ) {
		$nodes_to_delete = [];

		foreach ( $dom->getElementsByTagName( 'div' ) as $node ) {
			/* @var DOMElement $node */
			foreach ( static::BLACKLISTED_CLASSES as $class ) {
				if ( DOM_Helper::has_class( $node, $class ) ) {
					$nodes_to_delete[] = $node;
					break;
				}
			}
		}

		foreach ( static::BLACKLISTED_TAGS as $tag ) {
			foreach ( $dom->getElementsByTagName( $tag ) as $node ) {
				/* @var DOMElement $node */
				$nodes_to_delete[] = $node;
			}
		}

		foreach ( $nodes_to_delete as $node_to_delete ) {
			DOM_Helper::delete_node( $node_to_delete );
		}
	}

	/**
	 * Remove attributes that are not AMP-compatible for specific tags
	 *
	 * @param DOMDocument $dom
	 */
	public static function eliminate_invalid_attributes( $dom ) {
		foreach ( static::FORBIDDEN_ATTR as $tag => $forbidden_attrs ) {
			$nodes = $dom->getElementsByTagName( $tag );

			foreach ( $nodes as $node ) {
				/* @var $node DOMElement */
				foreach ( $forbidden_attrs as $attr ) {
					/* @var DOMElement $node */
					$node->removeAttribute( $attr );
				}
			}
		}
	}

	/**
	 * Move some common css rules to equivalent classes ( the main reason is that we want to reduce the amount of inline styles )
	 * Exp: <h1 data-c="3r" style="text-align: center;"> becomes <h1 class="thrive-text-center" data-c="3r">
	 * The drawback is that we're making them less specific by doing this, so only use it for 'safe' rules such as text-align
	 *
	 * @param DOMDocument $dom
	 */
	public static function optimize_inline_styles( $dom ) {
		foreach ( static::TEXT_TAGS as $tag ) {
			$nodes = $dom->getElementsByTagName( $tag );

			foreach ( $nodes as $node ) {
				/* @var $node DOMElement */
				$inline_styles = $node->getAttribute( 'style' );

				if ( ! empty( $inline_styles ) ) {
					$class_string = $node->getAttribute( 'class' );

					foreach ( static::COMMON_INLINE_RULE_CLASS_MAP as $rule => $class_replacement ) {

						if ( strpos( $inline_styles, $rule ) !== false ) {
							$inline_styles = str_replace( $rule, '', $inline_styles );

							$class_string .= ( empty( $class_string ) ? '' : ' ' ) . $class_replacement;
						}

						$node->setAttribute( 'style', $inline_styles );
						$node->setAttribute( 'class', $class_string );
					}
				}
			}
		}
	}

	/**
	 * Replace the comments section with a button that links to the comments form on the non-amp page
	 *
	 * @param DOMDocument $dom
	 */
	public static function parse_comments( $dom ) {
		$comments_nodes = DOM_Helper::get_all_nodes_for_tag_and_class( $dom, 'div', 'comments-area' );

		if ( ! empty( $comments_nodes ) ) {
			$comments_node = $comments_nodes[0];

			$post_id = get_the_ID();

			/* generate a link without 'amp' in it */
			$GLOBALS[ Main::GENERATE_AMP_PERMALINK_KEY ] = false;

			$link = get_comments_link( $post_id );

			$GLOBALS[ Main::GENERATE_AMP_PERMALINK_KEY ] = true;

			$comments_link = Main::get_amp_file( 'templates/comments-link.php', [
				'link' => $link,
				'text' => esc_html__( comments_open( $post_id ) ? 'Leave a Comment' : 'View Comments', 'thrive-theme' ),
			] );

			DOM_Helper::replace_node_with_string( $comments_node, $comments_link, $dom );
		}
	}

	/**
	 * The <picture> tag used by the logo is not AMP-compatible.
	 * In order to 'fix' it, we replace it with the fallback <img> tag that we already have inside the picture element.
	 *
	 * @param DOMDocument $dom
	 */
	public static function parse_logos( $dom ) {
		foreach ( DOM_Helper::get_all_nodes_for_tag_and_class( $dom, 'picture' ) as $picture_node ) {
			/* @var DOMElement $picture_node */
			$logo_wrapper = $picture_node->parentNode;

			/* make sure the picture tag is a child of the logo wrapper */
			if ( strpos( $logo_wrapper->getAttribute( 'class' ), 'tcb-logo' ) !== false ) {
				/* replace <picture> with the image fallback that's inside it */
				$logo_wrapper->replaceChild( $picture_node->getElementsByTagName( 'img' )->item( 0 ), $picture_node );
			}
		}
	}

	/**
	 * Map of attributes that must be removed from specific tags
	 * todo: this is a work in progress, it will need to be extended based on the specs from https://amp.dev/documentation/guides-and-tutorials/learn/spec/amphtml/
	 */
	const FORBIDDEN_ATTR = [
		'a'          => [
			'contenteditable',
			'dynamic-postlink',
			'jump-animation',
			'open',
			'spellcheck',
		],
		'address'    => Parser::TEXT_FORBIDDEN_ATTR,
		'blockquote' => Parser::TEXT_FORBIDDEN_ATTR,
		'button'     => [
			'target',
			'tagname',
		],
		'clippath'   => [
			'decoration-type',
			'pointer-height',
			'pointer-width',
			'slanted-angle',
			'style',
		],
		'div'        => [
			'jump-animation',
			'spellcheck',
			'tcb-template-id',
			'tcb-template-name',
			'tcb-template-pack',
		],
		'input'      => Parser::INPUT_FORBIDDEN_ATTR,
		'h1'         => Parser::TEXT_FORBIDDEN_ATTR,
		'h2'         => Parser::TEXT_FORBIDDEN_ATTR,
		'h3'         => Parser::TEXT_FORBIDDEN_ATTR,
		'h4'         => Parser::TEXT_FORBIDDEN_ATTR,
		'h5'         => Parser::TEXT_FORBIDDEN_ATTR,
		'h6'         => Parser::TEXT_FORBIDDEN_ATTR,
		'li'         => Parser::TEXT_FORBIDDEN_ATTR,
		'line'       => [
			'stroke-line-cap',
		],
		'p'          => Parser::TEXT_FORBIDDEN_ATTR,
		'path'       => [
			'stroke-line-cap',
		],
		'pre'        => Parser::TEXT_FORBIDDEN_ATTR,
		'span'       => Parser::TEXT_FORBIDDEN_ATTR,
		'symbol'     => [
			'version',
		],
		'svg'        => [
			'decoration-type',
		],
		'textarea'   => Parser::INPUT_FORBIDDEN_ATTR,
		'ul'         => Parser::TEXT_FORBIDDEN_ATTR,
	];

	/* attributes that shouldn't be on text-type HMTL tags */
	const TEXT_FORBIDDEN_ATTR = [
		'open',
		'spellcheck',
		'type',
		'contenteditable',
	];

	/* attributes that shouldn't be on input-type HMTL tags */
	const INPUT_FORBIDDEN_ATTR = [
		'open',
	];

	/**
	 * The most commonly used text tags
	 */
	const TEXT_TAGS = [
		'h1',
		'h2',
		'h3',
		'h4',
		'h5',
		'h6',
		'p',
	];

	/**
	 * A replacement map for converting common inline rules into classes
	 */
	const COMMON_INLINE_RULE_CLASS_MAP = [
		'text-align: justify;' => 'thrive-text-justify',
		'text-align: center;'  => 'thrive-text-center',
		'text-align: right;'   => 'thrive-text-right',
		'text-align: left;'    => 'thrive-text-left',
	];

	/**
	 * Remove the elements that have these tags ( some of these are changed into AMP elements during the parse, but they're still included here )
	 * see the 'HTML Tags' section from https://amp.dev/documentation/guides-and-tutorials/learn/spec/amphtml/
	 */
	const BLACKLISTED_TAGS = [
		'audio',
		'applet',
		'base',
		'canvas',
		'embed',
		'font',
		'form',
		'frame',
		'frameset',
		'iframe',
		'img',
		'link',
		'object',
		'param',
		'picture',
		'script',
		'style',
		'thrive_headline', /* hello, thrive headline optimizer */
		'video',
		'lt-div',
		'lt-highlighter',
	];

	/* remove these from the content */
	const REMOVE_FROM_CONTENT = [
		'!important',
		'! important',
		'javascript:void(0)',
		'--tve-',
		/* these are the most common ones, but ideally it would be better in the future to have a regex that catches them all ( but also riskier ) */
		'onblur',
		'oncancel',
		'onchange',
		'onclick',
		'onclose',
		'ondrag',
		'oninput',
		'onkeydown',
		'onkeypress',
		'onkeyup',
		'onload',
		'onsubmit',
		'target="_Top"',
		'target="_top"',
		'tve-droppable', /* sometimes this is added as an attribute ( no idea how that happens ) */
	];

	/* replace these with their values in the content */
	const REPLACE_IN_CONTENT = [
		/* todo: do a proper regex where we also delete the rule value */
		'--tcb-applied-color'         => 'color',
		'--tcb-local-color-var'       => 'color',
		'--tcb-local-color-icon'      => 'color',
		'--background-image'          => 'background-image',
		'--background-attachment'     => 'background-attachment',
		'--background-position'       => 'background-position',
		'--background-repeat'         => 'background-repeat',
		'--background-size'           => 'background-size',
		'position:fixed'              => 'position:relative', /* position:fixed is a disallowed inline style */
		'position: fixed'             => 'position:relative',
		'-webkit-tap-highlight-color' => 'color',
		'style="stroke-dasharray'     => 'style="color', /* stroke-dasharray is not valid inside inline styles */
	];
}
