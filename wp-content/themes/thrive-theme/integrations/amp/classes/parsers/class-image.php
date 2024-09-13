<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

namespace Thrive\Theme\AMP\Parsers;

use Thrive\Theme\AMP\Parser;
use Thrive_DOM_Helper as DOM_Helper;

use DOMDocument;
use DOMElement;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Image {

	/* use these sizes in case the image doesn't have other sizes */
	const FALLBACK_SIZES = [
		'height' => 400,
		'width'  => 600,
	];

	/* the logo SVG placeholder size needs some default size values */
	const LOGO_PLACEHOLDER_SIZES = [
		'height' => 40,
		'width'  => 160,
	];

	/**
	 * Remove these attributes from <img>s;
	 * This is further populated dynamically in init() with responsive-dependent attributes such as 'mt-d', 'center-h-d'
	 *
	 * @var array
	 * @see Image::init()
	 */
	public static $attr_to_remove = [
		'loading',
		'scale',
		'border',
		'decoding',
	];

	/**
	 * The keys for image responsive attributes, the suffixes will be added in init
	 */
	const IMAGE_RESPONSIVE_ATTR_KEYS = [
		'ml',
		'mt',
		'center-h',
		'center-v',
	];

	/* Hook into the main parse action. Called dynamically from class-parser.php */
	public static function init() {
		add_action( Parser::PARSE_HOOK, [ __CLASS__, 'parse_images' ] );

		foreach ( static::IMAGE_RESPONSIVE_ATTR_KEYS as $attr_key ) {
			foreach ( \Thrive_Utils::RESPONSIVE_DEVICE_KEYS as $device_key ) {
				static::$attr_to_remove[] = $attr_key . '-' . $device_key;
			}
		}
	}

	/**
	 * For all the image elements in the content, remove some attributes, add sizes if they don't exist, and change the tag from <img> to <amp-img>
	 *
	 * @param DOMDocument $dom
	 */
	public static function parse_images( $dom ) {
		$replacements = [];

		foreach ( $dom->getElementsByTagName( 'img' ) as $img_node ) {
			/* @var DOMElement $img_node */
			$new_node = DOM_Helper::create_node( $dom, 'amp-img', static::prepare_attr( $img_node ) );

			DOM_Helper::add_node_to_replacements_array( $img_node, $new_node, $replacements );
		}

		DOM_Helper::replace_nodes( $replacements );
	}

	/**
	 * @param DOMElement $img_node
	 *
	 * @return string[]
	 */
	public static function prepare_attr( $img_node ) {
		foreach ( static::$attr_to_remove as $forbidden_attr ) {
			$img_node->removeAttribute( $forbidden_attr );
		}

		$has_width  = is_numeric( $img_node->getAttribute( 'width' ) );
		$has_height = is_numeric( $img_node->getAttribute( 'height' ) );

		$attr = DOM_Helper::get_node_attributes_as_assoc_array( $img_node );

		if ( ! $has_width || ! $has_height ) {
			/* if this is a logo placeholder, use predetermined sizes */
			if ( static::is_logo_placeholder( $img_node ) ) {
				$attr['width']  = static::LOGO_PLACEHOLDER_SIZES['width'];
				$attr['height'] = static::LOGO_PLACEHOLDER_SIZES['height'];
			} else {
				$attr['width']  = static::FALLBACK_SIZES['width'];
				$attr['height'] = static::FALLBACK_SIZES['height'];
			}
		}

		/* this allows the image to grow until it reaches the width from image attr */
		$attr['layout'] = 'intrinsic';

		return $attr;
	}

	/**
	 * @param DOMElement $node
	 *
	 * @return bool
	 */
	public static function is_logo_placeholder( $node ) {
		$src = $node->getAttribute( 'src' );

		return strpos( $node->parentNode->getAttribute( 'class' ), 'tcb-logo' ) !== false &&
		       strpos( $src, 'logo_placeholder' ) !== false;
	}
}
