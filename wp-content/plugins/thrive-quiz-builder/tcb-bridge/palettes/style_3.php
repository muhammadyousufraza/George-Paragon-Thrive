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
				'parent' => $id1,
			),
			$id3 => array(
				'name'   => '3nd Accent',
				'parent' => $id1,
			),
			$id4 => array(
				'name'   => '3nd Accent',
				'parent' => $id1,
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
						'val'      => 'rgb(43, 54, 84)',
						'hsl'      => array(
							'h' => '224',
							's' => '0.32',
							'l' => '0.30',
						),
						'selector' => '.tqb-progress-container',
					),
					$id2 => array(
						'val'                   => 'rgb(72, 83, 111)',
						'hsl_parent_dependency' => array(
							'h' => '223',
							's' => '0.21',
							'l' => '0.40',
						),
						'selector'              => '.tqb-answers-container',
					),
					$id3 => array(
						'val'                   => 'rgb(103, 112, 135)',
						'hsl_parent_dependency' => array(
							'h' => '223',
							's' => '0.13',
							'l' => '0.47',
						),
						'selector'              => '.tqb-answer-inner-wrapper:not(.tqb-open-ended-wrapper)',
					),
					$id4 => array(
						'val'                   => 'rgb(71, 81, 106)',
						'hsl_parent_dependency' => array(
							'h' => '223',
							's' => '0.20',
							'l' => '0.35',
						),
						'selector'              => '.tqb-answer-inner-wrapper:not(.tqb-open-ended-wrapper) .tqb-answer-action',
						'css_property'          => 'box-shadow',
					),
				),
			),
		),
	),
);
