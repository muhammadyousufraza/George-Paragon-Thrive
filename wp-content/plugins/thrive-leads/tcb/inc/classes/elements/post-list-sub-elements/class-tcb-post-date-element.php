<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Post_Date_Element
 *
 * This element is permanently hidden from the sidebar, and is only kept for compatibility reasons.
 */
class TCB_Post_Date_Element extends TCB_Post_List_Sub_Element_Abstract {

	/**
	 * Hide this.
	 *
	 * @return string
	 */
	public function hide() {
		return false;
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'post-date';
	}

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Post Date', 'thrive-cb' );
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tcb-post-date';
	}

	/**
	 * Return the shortcode tag of the element.
	 *
	 * @return string
	 */
	public function shortcode() {
		return 'tcb_post_published_date';
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		$prefix_config = tcb_selection_root();
		$components    = parent::own_components();

		$components['typography']['config']['FontColor']['css_prefix'] = $prefix_config . ' ';
		$components['typography']['config']['FontSize']['css_prefix']  = $prefix_config . ' ';

		$components['responsive'] = [ 'hidden' => true ];

		$date_format_options = TCB_Utils::get_post_date_format_options( 'date' );
		$time_format_options = TCB_Utils::get_post_date_format_options( 'time' );

		return array_merge( $components, array(
			'post_date' => array(
				'config' => array(
					'Type'             => array(
						'config'  => array(
							'default' => 'published',
							'name'    => __( 'Display', 'thrive-cb' ),
							'options' => array(
								array(
									'name'  => __( 'Published Date', 'thrive-cb' ),
									'value' => 'published',
								),
								array(
									'name'  => __( 'Modified Date', 'thrive-cb' ),
									'value' => 'modified',
								),
							),
						),
						'extends' => 'Select',
					),
					'DateFormatSelect' => array(
						'config'  => array(
							'default' => $date_format_options[0]['value'],
							'name'    => __( 'Date Format', 'thrive-cb' ),
							'options' => $date_format_options,
						),
						'extends' => 'Select',
					),
					'DateFormatInput'  => [
						'config'  => [
							'label'       => '' /* declared in the component html */,
							'extra_attrs' => '',
						],
						'extends' => 'LabelInput',
					],
					'ShowTimeFormat'   => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Show Time?', 'thrive-cb' ),
							'default' => false,
						),
						'extends' => 'Switch',
					),
					'TimeFormatSelect' => array(
						'config'  => array(
							'default' => $time_format_options[0]['value'],
							'name'    => __( 'Time Format', 'thrive-cb' ),
							'options' => $time_format_options,
						),
						'extends' => 'Select',
					),
					'TimeFormatInput'  => [
						'config'  => [
							'label'       => '' /* declared in the component html */,
							'extra_attrs' => '',
						],
						'extends' => 'LabelInput',
					],
				),
			),
		) );
	}
}
