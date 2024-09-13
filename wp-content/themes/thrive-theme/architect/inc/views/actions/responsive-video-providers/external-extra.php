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
		'setting'       => 'controls',
		'checked_val'   => 0,
		'unchecked_val' => 1,
		'label'         => __( 'Hide player controls', 'thrive-cb' ),
	),
	array(
		'setting'       => 'loop',
		'checked_val'   => 1,
		'unchecked_val' => 0,
		'label'         => __( 'Loop', 'thrive-cb' ),
	),
);

foreach ( $settings as $setting ) {
	tcb_template( 'actions/responsive-video-providers/provider-extra', $setting );
}
