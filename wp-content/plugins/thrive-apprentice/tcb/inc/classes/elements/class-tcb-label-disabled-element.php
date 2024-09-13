<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 11/7/2017
 * Time: 9:26 AM
 */

require_once 'class-tcb-label-element.php';

/**
 * Class TCB_Label_Disabled_Element
 *
 * Non edited label element. For inline text we use typography control
 */
class TCB_Label_Disabled_Element extends TCB_Label_Element {

	/**
	 * Section element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv-disabled-label';
	}

	/**
	 * There is no need for HTML for this element since we need it only for control filter
	 *
	 * @return string
	 */
	protected function html() {
		return '';
	}

	/**
	 * Removes the unnecessary components from the element json string
	 *
	 * @return array
	 */
	protected function general_components() {
		$general_components = parent::general_components();

		if ( isset( $general_components['animation'] ) ) {
			unset( $general_components['animation'] );
		}

		if ( isset( $general_components['responsive'] ) ) {
			unset( $general_components['responsive'] );
		}
		if ( isset( $general_components['styles-templates'] ) ) {
			unset( $general_components['styles-templates'] );
		}

		return $general_components;
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		return [
			'typography' => [
				'disabled_controls' => [
					'TextTransform',
					'typography-text-transform-hr',
					'.tve-advanced-controls',
				],
				'config'            => [
					'FontColor'  => [
						'css_suffix' => ' .tcb-numbered-list-index',
					],
					'FontSize'   => [
						'css_suffix' => ' .tcb-numbered-list-index',
					],
					'FontFace'   => [
						'css_suffix' => ' .tcb-numbered-list-index',
					],
					'TextStyle'  => [
						'css_suffix' => ' .tcb-numbered-list-index',
					],
					'LineHeight' => [
						'css_suffix' => ' .tcb-numbered-list-index',
					],
				],
			],
			'layout'     => [
				'disabled_controls' => [
					'.tve-advanced-controls',
					'Width',
					'Height',
					'Alignment',
				],
			],
		];
	}
}
