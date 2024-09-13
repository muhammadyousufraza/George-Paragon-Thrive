<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Thrive_Taxonomy_Term_Description_Element
 */
class Thrive_Taxonomy_Term_Description_Element extends Thrive_Theme_Element_Abstract {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Taxonomy Term Description', 'thrive-theme' );
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'archive';
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrive-taxonomy-term-description';
	}

	/**
	 * Add/disable/change controls.
	 *
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();

		/* make sure typography elements also apply on paragraphs inside the element, if they exist */
		foreach ( $components['typography']['config'] as $control => $config ) {
			if ( in_array( $control, [ 'css_suffix', 'css_prefix' ] ) ) {
				continue;
			}

			$components['typography']['config'][ $control ]['css_suffix'] = [ ' p', '' ];
		}

		return $components;
	}


	/**
	 * Element category that will be displayed in the sidebar
	 *
	 * @return string
	 */
	public function category() {
		return Thrive_Defaults::theme_group_label();
	}

	/**
	 * Show the element only on archive pages
	 *
	 * @return bool
	 */
	public function hide() {
		return ! thrive_template()->is_archive();
	}

	/**
	 * This element is a shortcode.
	 *
	 * @return bool
	 */
	public function is_shortcode() {
		return true;
	}

	/**
	 * Return the shortcode tag of the element.
	 *
	 * @return string
	 */
	public static function shortcode() {
		return 'thrive_taxonomy_term_description';
	}
}

return new Thrive_Taxonomy_Term_Description_Element( 'thrive_taxonomy_term_description' );
