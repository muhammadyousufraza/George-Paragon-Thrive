<?php

$id1  = uniqid();
$id2  = uniqid();
$id3  = uniqid();
$id4  = uniqid();
$id5  = uniqid();
$id6  = uniqid();
$id7  = uniqid();
$id8  = uniqid();
$id9  = uniqid();
$id10 = uniqid();

$palettes = array(
	'active_palette' => 0,
	'config'         => array(
		'colors'    => array(
			$id1  => array(
				'name'   => 'Main Accent',
				'parent' => - 1,
			),
			$id2  => array(
				'name'   => '2nd Accent',
				'parent' => $id1,
			),
			$id3  => array(
				'name'   => '3th Accent',
				'parent' => $id1,
			),
			$id4  => array(
				'name'   => '4th Accent',
				'parent' => $id1,
			),
			$id5  => array(
				'name'   => '5th Accent',
				'parent' => $id1,
			),
			$id6  => array(
				'name'   => '6th Accent',
				'parent' => $id1,
			),
			$id7  => array(
				'name'   => '7th Accent',
				'parent' => $id1,
			),
			$id8  => array(
				'name'   => '8th Accent',
				'parent' => $id1,
			),
			$id9  => array(
				'name'   => '9th Accent',
				'parent' => $id1,
			),
			$id10 => array(
				'name'   => '10th Accent',
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
					$id1  => array(
						'val'      => 'rgb(136, 104, 224)',
						'hsl'      => array(
							'h' => '254',
							's' => '0.86',
							'l' => '0.68',
						),
						'selector' => '.tqb-progress-completed',
					),
					$id2  => array(
						'val'                   => 'rgb(207, 194, 242)',
						'hsl_parent_dependency' => array(
							'h' => '256',
							's' => '0.65',
							'l' => '0.85',
						),
						'selector'              => '.tqb-next-item',
					),
					$id3  => array(
						'val'                   => 'rgba(158, 120, 255, 0.15)',
						'hsl_parent_dependency' => array(
							'h' => '256',
							's' => '0.65',
							'l' => '0.85',
							'a' => '0.15',
						),
						'selector'              => '.tqb-fancy-icon',
					),
					$id4  => array(
						'val'                   => 'rgba(157,120,254,0.51)',
						'hsl_parent_dependency' => array(
							'h' => '256',
							's' => '0.65',
							'l' => '0.85',
						),
						'selector'              => '.tqb-answer-inner-wrapper:not(.tqb-open-ended-wrapper)',
						'css_property'          => 'box-shadow',
					),
					$id5  => array(
						'val'                   => 'rgb(158, 120, 255)',
						'hsl_parent_dependency' => array(
							'h' => '254',
							's' => '0.86',
							'l' => '0.68',
						),
						'selector'              => '.tqb-answer-inner-wrapper:not(.tqb-open-ended-wrapper)',
						'css_property'          => 'background-image',
						'gradient_colors'       => array(
							'left'  => $id5,
							'right' => $id6,
						),
						'gradient_val'          => 'linear-gradient(to right, rgb(158, 120, 255), rgb(141, 170, 252))',
					),
					$id6  => array(
						'val'                   => 'rgb(141, 170, 252)',
						'hsl_parent_dependency' => array(
							'h' => '224',
							's' => '0.95',
							'l' => '0.77',
						),
					),
					$id7  => array(
						'val'                   => 'rgb(136, 104, 224)',
						'hsl_parent_dependency' => array(
							'h' => '254',
							's' => '0.86',
							'l' => '0.68',
						),
						'selector'              => '.tqb-check',
						'css_property'          => 'color',
					),
					$id8  => array(
						'val'                   => 'rgb(158, 120, 255)',
						'hsl_parent_dependency' => array(
							'h' => '256',
							's' => '0.65',
							'l' => '0.85',
						),
						'selector'              => '.tqb-next-icon .tcb-icon, .tqb-next-icon.tcb-icon',
						'css_property'          => 'color',
					),
					$id9  => array(
						'val'                   => 'rgb(158, 120, 255)',
						'hsl_parent_dependency' => array(
							'h' => '254',
							's' => '0.86',
							'l' => '0.68',
						),
						'selector'              => '.tve-state-expanded.tqb-active-answer',
						'css_property'          => 'background-image',
						'gradient_colors'       => array(
							'left'  => $id5,
							'right' => $id6,
						),
						'gradient_val'          => 'linear-gradient(to right, rgb(158, 120, 255), rgb(141, 170, 252))',
					),
					$id10 => array(
						'val'                   => 'rgb(198,184,239)',
						'hsl_parent_dependency' => array(
							'h' => '256',
							's' => '0.65',
							'l' => '0.85',
						),
						'selector'              => '.tqb-answer-open-type textarea',
						'css_property'          => 'border-color',
					),
				),
			),
		),
	),
);
