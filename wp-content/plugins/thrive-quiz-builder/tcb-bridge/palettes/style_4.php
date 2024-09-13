<?php

$id1 = uniqid();
$id2 = uniqid();
$id3 = uniqid();

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
		),
		'gradients' => array(),
	),
	'palettes'       => array(
		array(
			'name'  => 'Default',
			'value' => array(
				'colors' => array(
					$id1 => array(
						'val'          => 'rgb(5, 81, 130)',
						'hsl'          => array(
							'h' => '204',
							's' => '0.93',
							'l' => '0.27',
						),
						'selector'     => '.tqb-next-icon',
						'css_property' => 'color',
					),
					$id2 => array(
						'val'                   => 'rgb(5, 81, 130)',
						'hsl_parent_dependency' => array(
							'h' => '204',
							's' => '0.93',
							'l' => '0.27',
						),
						'selector'              => '.tqb-answer-inner-wrapper:not(.tqb-open-ended-wrapper)',
					),
					$id3 => array(
						'val'                   => 'rgb(5, 81, 130)',
						'hsl_parent_dependency' => array(
							'h' => '204',
							's' => '0.93',
							'l' => '0.27',
						),
						'selector'              => '#tqb-open-type-answer',
						'css_property'          => 'border-color',
					),
				),
			),
		),
	),
);
