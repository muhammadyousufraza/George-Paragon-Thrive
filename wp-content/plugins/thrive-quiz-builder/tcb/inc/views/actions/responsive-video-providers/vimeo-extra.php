<?php
$settings = array(
	array(
		'setting'       => 'autoplay',
		'checked_val'   => 1,
		'unchecked_val' => 0,
		'label'         => __( 'Autoplay', 'thrive-cb' ),
		'info'          => true,
		'info_fn'       => 'openTooltip',
	),
	array(
		'setting'       => 'modestbranding',
		'checked_val'   => 0,
		'unchecked_val' => 1,
		'label'         => __( 'Hide logo', 'thrive-cb' ),
	),
	array(
		'setting'       => 'showinfo',
		'checked_val'   => 0,
		'unchecked_val' => 1,
		'label'         => __( 'Hide title bar', 'thrive-cb' ),
	),
	array(
		'setting'       => 'byline',
		'checked_val'   => 0,
		'unchecked_val' => 1,
		'label'         => __( 'Hide byline', 'thrive-cb' ),
	),
);

foreach ( $settings as $setting ) {
	tcb_template( 'actions/responsive-video-providers/provider-extra', $setting );
}
