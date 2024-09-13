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
 * Class Thrive_DOM_Helper
 */
class Thrive_DOM_Helper {
	/**
	 * Tries to initialize a DomDocument object and returns it on success, or returns false otherwise
	 *
	 * @param $content
	 *
	 * @return bool|DOMDocument
	 */
	public static function initialize_dom_document( $content ) {
		$libxml_error_state = libxml_use_internal_errors( true );

		/* Wrap in dummy tags, since XML needs one parent node
		 * Add charset so loadHTML does not have parsing problems
		 */
		$document = sprintf(
			'<html><head><meta http-equiv="content-type" content="text/html; charset=%s"></head><body>%s</body></html>',
			get_bloginfo( 'charset' ),
			$content
		);

		$dom = new DOMDocument();

		if ( ! $dom->loadHTML( $document ) ) {
			$dom = false;
		}

		libxml_clear_errors();
		libxml_use_internal_errors( $libxml_error_state );

		return $dom;
	}

	/**
	 * Extract a DOMElement node's HTML element attributes and return as an array.
	 *
	 * @param DOMElement $node Represents an HTML element for which to extract attributes.
	 *
	 * @return string[] The attributes for the passed node, or an
	 *                  empty array if it has no attributes.
	 */
	public static function get_node_attributes_as_assoc_array( $node ) {
		$attributes = [];
		if ( ! $node->hasAttributes() ) {
			return $attributes;
		}

		foreach ( $node->attributes as $attribute ) {
			$attributes[ $attribute->nodeName ] = $attribute->nodeValue;
		}

		return $attributes;
	}

	/**
	 * Create a new node w/attributes (a DOMElement) and add to the passed DOMDocument.
	 *
	 * @param DOMDocument $dom        A representation of an HTML document to add the new node to.
	 * @param string      $tag        A valid HTML element tag for the element to be added.
	 * @param string[]    $attributes One of more valid attributes for the new node.
	 *
	 * @return DOMElement|false The DOMElement for the given $tag, or false on failure
	 */
	public static function create_node( $dom, $tag, $attributes ) {
		$node = $dom->createElement( $tag );
		static::add_attributes_to_node( $node, $attributes );

		return $node;
	}

	/**
	 * Add one or more HTML element attributes to a node's DOMElement.
	 *
	 * @param DOMElement $node       Represents an HTML element.
	 * @param string[]   $attributes One or more attributes for the node's HTML element.
	 */
	public static function add_attributes_to_node( $node, $attributes ) {
		foreach ( $attributes as $name => $value ) {
			$node->setAttribute( $name, $value );
		}
	}

	/**
	 * @param DOMElement|DOMNode $node
	 * @param DOMElement|DOMNode $new_node
	 * @param array              $replacements
	 */
	public static function add_node_to_replacements_array( $node, $new_node, &$replacements ) {
		$replacements[] = [
			'current' => $node,
			'new'     => $new_node,
		];
	}

	/**
	 * For each element in the array, replace the node at the 'old' index with the node at the 'new' index
	 *
	 * @param $array_of_replacements
	 */
	public static function replace_nodes( $array_of_replacements ) {
		$length = count( $array_of_replacements );

		/* we have to iterate the 'old way' because otherwise the index messes up after deleting elements */
		for ( $i = 0; $i < $length; $i ++ ) {
			/* @var DOMElement $current_node */
			$current_node = $array_of_replacements[ $i ]['current'];
			$new_node     = $array_of_replacements[ $i ]['new'];

			if ( $new_node === null ) {
				static::delete_node( $current_node );
			} else {
				$current_node->parentNode->replaceChild( $new_node, $current_node );
			}
		}
	}

	/**
	 * @param $node
	 */
	public static function delete_node( $node ) {
		$node->parentNode->removeChild( $node );
	}

	/**
	 * Helper function for replacing $node_to_replace ( a DOMNode ) with a string
	 * In order to replace the node, we create a new DOMDocument for the replacement content
	 * Won't work properly if the content has multiple parent tags ( for instance <div>1</div><div>2</div> )
	 *
	 * @param DOMDocument $dom
	 * @param DOMNode     $node_to_replace
	 * @param string      $content
	 */
	public static function replace_node_with_string( &$node_to_replace, $content, $dom ) {
		$new_node = static::create_node_from_string( $content, $dom );

		$node_to_replace->parentNode->replaceChild( $new_node, $node_to_replace );
	}

	/**
	 * We can't just append nested HTML to a node, but we can initialize a new DOMDocument and extract the created DOMNode from there.
	 * Afterwards, we import it to the current DOMDocument.
	 *
	 * @param string      $content
	 * @param DOMDocument $dom
	 *
	 * @return DOMNode $node
	 */
	public static function create_node_from_string( $content, $dom ) {
		/* @var DOMDocument $new_dom */
		$new_dom = static::initialize_dom_document( $content );

		/* we assume the new content has one parent tag ( I think that's normal though? ) */
		$new_node = $new_dom->getElementsByTagName( 'body' )->item( 0 )->firstChild;;

		/* we're not using this anymore, so we unset it because it's a large-ish object */
		unset( $new_dom );

		return $dom->importNode( $new_node, true ); /* the 'true' sets the deep import flag, this way it imports all the nested HTML */
	}

	/**
	 * Check if this node has the given class
	 *
	 * @param DOMElement|DOMNode $node
	 * @param string             $class - the class we're looking for
	 *
	 * @return boolean
	 */
	public static function has_class( $node, $class ) {
		return strpos( $node->getAttribute( 'class' ), $class ) !== false;
	}

	/**
	 * Return all the nodes for the given tag and class. If the class is empty, return all the nodes for the tag. If the tag is empty, assume it's 'div'.
	 *
	 * @param DOMDocument $dom
	 * @param string      $tag
	 * @param string      $class
	 *
	 * @return [ DOMElement ] $nodes - array of nodes
	 */
	public static function get_all_nodes_for_tag_and_class( $dom, $tag = 'div', $class = '' ) {
		$nodes = [];

		foreach ( $dom->getElementsByTagName( $tag ) as $node ) {
			/* @var DOMElement $node */
			if ( empty( $class ) || static::has_class( $node, $class ) ) {
				$nodes[] = $node;
			}
		}

		return $nodes;
	}

	/**
	 * Return valid HTML *body* content extracted from the DOMDocument passed as a parameter.
	 *
	 * @param DOMDocument $dom Represents an HTML document from which to extract HTML content.
	 *
	 * @return string Returns the HTML content of the body element represented in the DOMDocument.
	 */
	public static function get_content_from_dom( $dom ) {
		$body = $dom->getElementsByTagName( 'body' )->item( 0 );

		// The DOMDocument may contain no body. In which case return nothing.
		if ( $body === null ) {
			$content = '';
		} else {
			$body    = $dom->saveHTML( $body );
			$content = preg_replace( '#^.*?<body.*?>(.*)</body>.*?$#si', '$1', $body );
		}

		return $content;
	}
}
