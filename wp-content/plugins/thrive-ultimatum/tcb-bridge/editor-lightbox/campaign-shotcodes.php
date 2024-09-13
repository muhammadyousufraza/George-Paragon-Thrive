<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 7/20/2017
 * Time: 12:32 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

$tu_campaigns = tve_ult_get_campaign_with_shortcodes();

?>
<h2 class="tcb-modal-title"><?php echo __( 'Thrive Ultimatum Campaign Shortcodes', 'thrive-ult'); ?></h2>
<hr class="margin-top-0">
<div class="margin-top-0">
	<?php echo __( 'Select a Campaign and a Shortcode design you want to be displayed in your content. All the logic of selected Campaign will be applied on selected design too. Please make sure you do the right settings for your Campaign', 'thrive-ult'); ?>
</div>
<div class="tve-templates-wrapper">
	<div class="row margin-top-20">
		<div class="col col-xs-6"><?php echo __( 'Select Campaign:', 'thrive-ult'); ?></div>
		<div class="col col-xs-6">
			<select id="tve_ult_campaign" name="tve_ult_campaign" class="change" data-fn="campaign_changed">
				<option value=""><?php echo __( 'Select campaign', 'thrive-ult'); ?></option>
				<?php foreach ( $tu_campaigns as $campaign ) : ?>
					<option value="<?php echo $campaign->ID ?>"><?php echo $campaign->post_title ?></option>
				<?php endforeach; ?>
			</select>
		</div>
	</div>
	<div class="row margin-top-20">
		<div class="col col-xs-6"><?php echo __( 'Select Shortcode:', 'thrive-ult'); ?></div>
		<div class="col col-xs-6">
			<select id="tve_ult_shortcode" name="tve_ult_shortcode">
				<option><?php echo __( 'Select shortcode', 'thrive-ult'); ?></option>
			</select>
		</div>
	</div>
</div>
<div class="tcb-modal-footer">
	<button type="button" class="tcb-right tve-button medium green click white-text" data-fn="generate_countdown_html">
		<?php echo __( 'Save and close', 'thrive-ult') ?>
	</button>
</div>
