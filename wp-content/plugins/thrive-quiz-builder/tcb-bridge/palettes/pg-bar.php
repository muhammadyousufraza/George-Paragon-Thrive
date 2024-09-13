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
		),
		'gradients' => array(),
	),
	'palettes'       => array(
		array(
			'name'  => 'Default',
			'value' => array(
				'colors' => array(
					$id1 => array(
						'val'          => 'rgb(27, 146, 19)',
						'hsl'          => array(
							'h' => '116',
							's' => '0.77',
							'l' => '0.32',
						),
						'selector'     => '.tqb-progress-completed',
					),
					$id2 => array(
						'val'                   => 'rgb(150, 209, 145)',
						'hsl_parent_dependency' => array(
							'h' => '115',
							's' => '0.41',
							'l' => '0.69',
						),
						'selector'              => '.tqb-next-item',
					),
				),
			),
		),
	),
);
