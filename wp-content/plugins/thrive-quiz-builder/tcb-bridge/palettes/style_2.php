<?php

$id1 = uniqid();
$id2 = uniqid();

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
		),
		'gradients' => array(),
	),
	'palettes'       => array(
		array(
			'name'  => 'Default',
			'value' => array(
				'colors' => array(
					$id1 => array(
						'val'          => 'rgb(247, 150, 22)',
						'hsl'          => array(
							'h' => '34',
							's' => '0.93',
							'l' => '0.53',
						),
						'selector'     => '.tqb-next-icon .tcb-icon, .tqb-next-icon.tcb-icon',
						'css_property' => 'color',
					),
					$id2 => array(
						'val'                   => 'rgb(247, 150, 22)',
						'hsl_parent_dependency' => array(
							'h' => '34',
							's' => '0.93',
							'l' => '0.53',
						),
						'selector'              => '.tqb-answer-inner-wrapper:not(.tqb-open-ended-wrapper)',
					),
				),
			),
		),
	),
);
