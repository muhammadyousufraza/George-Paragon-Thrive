<?php $palette = array(
	'active_palette' => 0,
	'config'         => [
		'colors'    => [
			'cf6ff' => [
				'name'   => 'Main Color',
				'parent' => - 1,
			],
			'73c8d' => [
				'name'   => 'Dark Accent',
				'parent' => 'cf6ff',
			],
		],
		'gradients' => [],
	],
	'palettes'       => array(
		array(
			'name'  => 'Default',
			'value' => array(
				'colors'    => array(
					'cf6ff' => array(
						'val' => 'rgb(20, 115, 210)',
						'hsl' => [
							'h' => 210,
							's' => 0.82,
							'l' => 0.45,
						],
					),
					'73c8d' => array(
						'val'                   => 'rgb(21, 89, 162)',
						'hsl_parent_dependency' => [
							'h' => 211,
							's' => 0.77,
							'l' => 0.35,
						],
					),
				),
				'gradients' => [],
			),
		),
	),
); ?>
<div class="thrv_wrapper tve-user-profile" data-ct="user_profile--1">
	<div class="tve-user-profile-container tve-prevent-content-edit"
		 data-config="__TCB_UP__{&quot;error_message&quot;:{&quot;email&quot;:&quot;Email address invalid&quot;,&quot;required&quot;:&quot;Required field missing&quot;,&quot;passwordmismatch&quot;:&quot;Password mismatch&quot;},&quot;fields&quot;:[&quot;first_name&quot;,&quot;last_name&quot;,&quot;username&quot;,&quot;user_email&quot;],&quot;label_pos&quot;:&quot;left&quot;,&quot;success_message&quot;:&quot;Profile updated successfully&quot;}__TCB_UP__">
		<input type="hidden" class="tve-up-messages" value="{&quot;success_message&quot;:&quot;Profile updated successfully&quot;,&quot;error_message&quot;:{&quot;email&quot;:&quot;Email address invalid&quot;,&quot;required&quot;:&quot;Required field missing&quot;,&quot;passwordmismatch&quot;:&quot;Password mismatch&quot;}}">
		<form action="#" method="post" novalidate="">
			<div class="tve_lead_generated_inputs_container tve_clearfix">
				<div class="tve-up-editable tve-up-item" data-type="first_name">
					<div class="tve-up-editable tve-up-input">
						<input name="first_name" type="text" placeholder="First Name">
					</div>
				</div>
				<div class="tve-up-editable tve-up-item" data-type="last_name">
					<div class="tve-up-editable tve-up-input">
						<input name="last_name" type="text" placeholder="Last Name">
					</div>
				</div>
				<div class="tve-up-editable tve-up-item" data-type="username">
					<div class="tve-up-editable tve-up-input">
						<input name="username" type="text" placeholder="Username" readonly>
					</div>
				</div>
				<div class="tve-up-editable tve-up-item" data-type="user_email">
					<div class="tve-up-editable tve-up-input">
						<input name="user_email" type="email" placeholder="Email">
					</div>
				</div>
				<div class="thrv_wrapper tve-form-button tcb-local-vars-root tve-up-editable tve-up-button">
					<div class="thrive-colors-palette-config" style="display: none !important">__CONFIG_colors_palette__<?php echo json_encode( $palette ); ?>__CONFIG_colors_palette__
					</div>
					<a href="#" class="tcb-button-link tve-form-button-submit tcb-plain-text">
						<span class="tcb-button-texts"><span class="tcb-button-text thrv-inline-text">Sign Up</span></span>
					</a>
					<input type="submit" style="display: none !important;">
				</div>
			</div>
		</form>
	</div>
</div>
