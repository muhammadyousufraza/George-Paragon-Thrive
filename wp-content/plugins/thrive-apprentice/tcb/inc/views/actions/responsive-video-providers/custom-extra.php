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
		'setting'            => 'controls',
		'checked_val'        => 0,
		'unchecked_val'      => 1,
		'label'              => __( 'Hide player controls', 'thrive-cb' ),
		'disable_option'     => 'no-download',
		'disable_option_val' => 0,
	),
	array(
		'setting'       => 'loop',
		'checked_val'   => 1,
		'unchecked_val' => 0,
		'label'         => __( 'Loop', 'thrive-cb' ),
	),
	array(
		'setting'       => 'no-download',
		'checked_val'   => 0,
		'unchecked_val' => 1,
		'label'         => __( 'Allow users to download', 'thrive-cb' ),
	),
);

foreach ( $settings as $setting ) {
	tcb_template( 'actions/responsive-video-providers/provider-extra', $setting );
}
