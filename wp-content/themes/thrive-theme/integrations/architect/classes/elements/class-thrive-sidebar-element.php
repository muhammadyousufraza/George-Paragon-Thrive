<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Thrive_Sidebar_Element
 */
class Thrive_Sidebar_Element extends Thrive_Theme_Element_Abstract {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Widget Area', 'thrive-theme' );
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'sidebar';
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.widget-area';
	}

	/**
	 * @return string
	 */
	protected function html() {
		return $this->html_placeholder( __( 'Insert Widget Area', 'thrive-theme' ) );
	}

	/**
	 * This element is a shortcode
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
		return 'thrive_widget_area';
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		$default = parent::own_components();

		/**
		 * https://thrive.atlassian.net/browse/SUPP-9787 Typography does not really make sense at this level
		 * It does not make sense to control the Typography at this level, because there are many different elements that can go into a Widget Area
		 * (e.g. Headings, Paragraphs, lists, links, buttons etc) and each of those have their own specific typography aspects.
		 */
		unset( $default['typography'] );

		return array_merge( $default, [
			'thrive_widget_area' => [
				'order'  => 1,
				'config' => [
					'Orientation' => [
						'config'  => [
							'name'    => __( 'Orientation', 'thrive-theme' ),
							'buttons' => [
								[
									'value'   => 'column',
									'text'    => __( 'Column', 'thrive-theme' ),
									'default' => true,
								],
								[
									'value' => 'row',
									'text'  => __( 'Row', 'thrive-theme' ),
								],
							],
						],
						'extends' => 'ButtonGroup',
					],
					'Sidebars'    => [
						'config'  => [
							'default' => 'none',
							'name'    => __( 'Source', 'thrive-theme' ),
							'options' => Thrive_Utils::get_sidebars(),
						],
						'extends' => 'Select',
					],
				],
			],
		] );
	}

	/**
	 * @inheritDoc
	 */
	protected function general_components() {
		$components = parent::general_components();

		/**
		 * https://thrive.atlassian.net/browse/SUPP-9787 Typography does not really make sense at this level
		 * It does not make sense to control the Typography at this level, because there are many different elements that can go into a Widget Area
		 * (e.g. Headings, Paragraphs, lists, links, buttons etc) and each of those have their own specific typography aspects.
		 */
		unset( $components['typography'] );

		return $components;
	}


}

return new Thrive_Sidebar_Element( 'thrive_widget_area' );
