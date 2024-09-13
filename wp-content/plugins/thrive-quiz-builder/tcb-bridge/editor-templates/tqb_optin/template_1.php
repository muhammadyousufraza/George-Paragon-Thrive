<?php
$config = array(
	'email'    => 'Please enter a valid email address',
	'phone'    => 'Please enter a valid phone number',
	'required' => 'Please fill in the required fields',
)
?>
<div data-css="tve-u-16d632405fa" class="thrv_wrapper tve-tqb-page-type tqb-optin-template-1 tve_editor_main_content" style="<?php echo esc_attr( $main_content_style ); ?>">
	<div data-css="tve-u-16d678d5a2c" class="thrv_wrapper thrv_heading tve-draggable tve-droppable">
		<h2 data-css="tve-u-15d9c5854a2" data-default="Your Heading Here">Great Job, You’ve Finished the Quiz!</h2>
	</div>
	<div class="thrv_wrapper thrv_text_element tve-draggable tve-droppable">
		<p class="tve-droppable" data-default="Enter your text here..." data-css="tve-u-15d9c82267d">To get your results and a full report on how to improve [quiz topic], just let us know where to send it.</p>
	</div>
	<div class="thrv_wrapper thrv_contentbox_shortcode thrv-content-box tve-draggable tve-droppable" data-css="tve-u-15d9c601f22">
		<div class="tve-content-box-background" data-css="tve-u-15d9c5ff0a7"></div>
		<div class="tve-cb tve_empty_dropzone" data-css="tve-u-15d9c6036f5">
			<div class="thrv_wrapper thrv_lead_generation tve-draggable tve-droppable" data-connection="api" data-css="tve-u-15d9c5d0206">
				<input type="hidden" class="tve-lg-err-msg" value="{&quot;email&quot;:&quot;Email address invalid&quot;,&quot;phone&quot;:&quot;Phone number invalid&quot;,&quot;password&quot;:&quot;Password invalid&quot;,&quot;passwordmismatch&quot;:&quot;Password mismatch error&quot;,&quot;required&quot;:&quot;Required field missing&quot;}">
				<div class="thrv_lead_generation_container tve_clearfix">
					<form action="#" method="post" novalidate="novalidate">
						<div class="tve_lead_generated_inputs_container tve_clearfix tve_empty_dropzone">
							<div class="tve_lg_input_container tve_lg_input tve-draggable tve-droppable" data-css="tve-u-15d9c5aced3">
								<input type="text" data-field="name" name="name" placeholder="Your name" data-placeholder="Your name" data-required="1">
							</div>
							<div class="tve_lg_input_container tve_lg_input tve-draggable tve-droppable" data-css="tve-u-15d9c5aced7">
								<input type="email" data-field="email" data-required="1" data-validation="email" name="email" placeholder="your_name@domain.com" data-placeholder="your_name@domain.com">
							</div>
							<div class="tve_lg_input_container tve_submit_container tve_lg_submit tve-draggable tve-droppable" data-css="tve-u-15d9c5b8e9e">
								<button type="submit">
									<span>Yes! Send Me the Detailed Report</span>
								</button>
							</div>
						</div>
					</form>
				</div>
			</div>
			<div class="thrv_wrapper thrv_text_element tve-draggable tve-droppable tve_empty_dropzone">
				<p class="tve-droppable" data-css="tve-u-15d9c642540">
					<a href="javascript:void(0)" class="tve_evt_manager_listen tve_et_click" data-shortcode-id="next_step_in_quiz_<?php echo esc_attr( $variation['quiz_id'] ); ?>" data-dynamic-link="tqb_quiz_options">
						<span data-css="tve-u-15d9c642547">Skip this step</span>
					</a>
				</p>
			</div>
		</div>
	</div>
</div>
