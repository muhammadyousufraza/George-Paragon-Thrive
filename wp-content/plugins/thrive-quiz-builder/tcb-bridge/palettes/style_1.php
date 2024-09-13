<?php

$id1 = uniqid();
$id2 = uniqid();
$id3 = uniqid();
$id4 = uniqid();

$palettes = array(
	'active_palette' => 0,
	'config'         => array(
		'colors'    => array(
			$id1 => array(
				'name'   => 'Main Accent',
				'parent' => - 1,
			),
			$id2 => array(
				'name'   => '2nd Accent',
				'parent' => - 1,
			),
			$id3 => array(
				'name'   => '3nd Accent',
				'parent' => $id2,
			),
			$id4 => array(
				'name'   => '4nd Accent',
				'parent' => $id2,
			),
		),
		'gradients' => array(),
	),
	'palettes'       => array(
		array(
			'name'  => 'Default',
			'value' => array(
				'colors' => array(
					$id1 => array(
						'val'      => 'rgb(19, 28, 37)',
						'hsl'      => array(
							'h' => '210',
							's' => '0.32',
							'l' => '0.11',
						),
						'selector' => '.tqb-answer-inner-wrapper:not(.tqb-open-ended-wrapper)',
					),
					$id2 => array(
						'val'      => 'rgb(247, 202, 101)',
						'hsl'      => array(
							'h' => '42',
							's' => '0.90',
							'l' => '0.68',
						),
						'selector' => '.tqb-answer-inner-wrapper:not(.tqb-open-ended-wrapper):hover',
					),
					$id3 => array(
						'val'                   => 'rgb(247, 202, 101)',
						'hsl_parent_dependency' => array(
							'h' => '42',
							's' => '0.90',
							'l' => '0.68',
						),
						'selector'              => '.tqb-next-icon .tcb-icon, .tqb-next-icon.tcb-icon',
						'css_property'          => 'color',
					),
					$id4 => array(
						'val'                   => 'rgb(247, 202, 101)',
						'hsl_parent_dependency' => array(
							'h' => '42',
							's' => '0.90',
							'l' => '0.68',
						),
						'selector'              => '.tqb-question-image-container',
						'css_property'          => 'border-color',
					),
				),
			),
		),
	),
);
