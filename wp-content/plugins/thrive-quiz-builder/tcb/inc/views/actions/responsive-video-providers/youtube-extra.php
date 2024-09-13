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
		'setting'       => 'no-cookie',
		'checked_val'   => 1,
		'unchecked_val' => 0,
		'label'         => __( 'Disable YouTube cookies', 'thrive-cb' ),
		'info'          => true,
		'info_fn'       => 'cookieTooltip',
	),
	array(
		'setting'       => 'rel',
		'checked_val'   => 0,
		'unchecked_val' => 1,
		'label'         => __( 'Optimize related videos', 'thrive-cb' ),
	),
	array(
		'setting'            => 'controls',
		'checked_val'        => 0,
		'unchecked_val'      => 1,
		'label'              => __( 'Hide player controls', 'thrive-cb' ),
		'disable_option'     => 'modestbranding,fs',
		'disable_option_val' => 0,
	),
	array(
		'setting'       => 'fs',
		'checked_val'   => 0,
		'unchecked_val' => 1,
		'label'         => __( 'Hide full-screen', 'thrive-cb' ),
	),
	array(
		'setting'       => 'modestbranding',
		'checked_val'   => 1,
		'unchecked_val' => 0,
		'label'         => __( 'Hide logo', 'thrive-cb' ),
	),
);

foreach ( $settings as $setting ) {
	tcb_template( 'actions/responsive-video-providers/provider-extra', $setting );
}
