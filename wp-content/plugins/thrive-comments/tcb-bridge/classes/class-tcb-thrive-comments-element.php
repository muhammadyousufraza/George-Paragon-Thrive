<?php
/**
 * FileName  class-tcb-thrive-comments-element.php.
 * @project: thrive-comments
 * @developer: Dragos Petcu
 * @company: BitStone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Thrive_Comments_Element
 */
class TCB_Thrive_Comments_Element extends TCB_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Thrive Comments', Thrive_Comments_Constants::T );
	}

	/**
	 * Get element alternate
	 *
	 * @return string
	 */
	public function alternate() {
		return 'thrive';
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'comments';
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv-comments';
	}

	/**
	 * Whether or not this element is only a placeholder ( it has no menu, it's not selectable etc )
	 * e.g. Content Templates
	 *
	 * @return bool
	 */
	public function is_placeholder() {
		return false;
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		return array(
			'thrive_comments' => array(
				'config' => array(
					'ColorPicker' => array(
						'css_prefix' => '',
						'css_suffix' => '',
						'config'     => array(
							'label' => __( 'Accent color', Thrive_Comments_Constants::T ),
						),
					),
				),
			),
		);
	}

	/**
	 * General components that apply to all elements
	 *
	 * @return array
	 */
	public function general_components() {
		return array(
			'layout' => array(
				'order' => 100,
			),
		);
	}

	/**
	 * Element HTML
	 *
	 * @return string
	 */
	public function html() {
		ob_start();
		include dirname( __FILE__ ) . '/../templates/tcb-tc-element.php';
		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	/**
	 * Element category that will be displayed in the sidebar
	 * @return string
	 */
	public function category() {
		return $this->get_thrive_integrations_label();
	}
}
