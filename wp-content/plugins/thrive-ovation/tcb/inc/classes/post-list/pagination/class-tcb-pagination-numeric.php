<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Pagination_Numeric
 */
class TCB_Pagination_Numeric extends TCB_Pagination {

	const BUTTON_CLASS            = 'tcb-pagination-button';
	const PREV_NEXT_BUTTON_CLASS  = 'tcb-pagination-prev-next-button';
	const FIRST_LAST_BUTTON_CLASS = 'tcb-pagination-first-last-button';

	private $default_attr = [
		'hide_page_numbers' => false,
		'hide_prev_next'    => false,
		'hide_first_last'   => false,
		'hide_label'        => false,
	];

	/**
	 * Get the content for numeric pagination.
	 *
	 * @return string|null
	 */
	public function get_content() {
		$default_attr = $this->default_attr;

		/* structure: content = label + first + prev + page_numbers + next + last' */
		$content = '';

		if ( empty( $default_attr['hide_page_numbers'] ) ) {
			$content = TCB_Utils::wrap_content( $content, 'div', '', 'tcb-pagination-numbers-wrapper' );
		}

		if ( empty( $default_attr['hide_prev_next'] ) ) {
			$content = $this->get_button( 'prev' ) . $content . $this->get_button( 'next' );
		}

		if ( empty( $default_attr['hide_first_last'] ) ) {
			$content = $this->get_button( 'first' ) . $content . $this->get_button( 'last' );
		}

		$content = TCB_Utils::wrap_content( $content, 'div', '', 'tcb-pagination-navigation-container ' . THRIVE_WRAPPER_CLASS );

		if ( empty( $default_attr['hide_label'] ) ) {
			$content = $this->get_navigation_label() . $content;
		}

		return $content;
	}

	/**
	 * Returns the pagination button html.
	 *
	 * @param $type
	 *
	 * @return string
	 */
	private function get_button( $type ) {
		$attr    = [];
		$classes = [ static::BUTTON_CLASS, THRIVE_WRAPPER_CLASS, 'tcb-pagination-' . $type ];
		$icon    = tcb_template( 'pagination/' . $type . '-icon.php', null, true );

		$name = ucfirst( $type );

		switch ( $type ) {
			/* prev and next are handled together */
			case 'prev':
				$name = __( 'Previous', 'thrive-cb' );
			/* no break */
			case 'next':
				$classes[] = static::PREV_NEXT_BUTTON_CLASS;
				$classes[] = 'tcb-with-icon';

				$attr['data-button_layout'] = 'icon';
				break;
			case 'first':
			case 'last':
				$classes[] = static::FIRST_LAST_BUTTON_CLASS;

				$attr['data-button_layout'] = 'text';
				break;
			default:
				break;
		}

		/* 'Next' and 'Last' have their icons on the right by default */
		if ( in_array( $type, [ 'next', 'last' ] ) ) {
			$classes[] = 'tcb-flip';
		}

		$data = array(
			'name' => $name,
			'icon' => empty( $icon ) ? '' : TCB_Utils::wrap_content( $icon, 'span', '', 'tcb-button-icon' ),
		);

		$content = tcb_template( 'pagination/button.php', $data, true );

		return TCB_Utils::wrap_content( $content, 'p', '', $classes, $attr );
	}

	private function get_navigation_label() {
		$classes = [ 'tcb-pagination-label', THRIVE_WRAPPER_CLASS, 'tve_no_drag' ];

		$content = tcb_template( 'pagination/label-pages.php', null, true );

		return TCB_Utils::wrap_content( $content, 'div', '', $classes );
	}

	/**
	 * Get the label for this type.
	 *
	 * @return string|void
	 */
	public function get_label() {
		return __( 'Numeric', 'thrive-cb' );
	}
}
