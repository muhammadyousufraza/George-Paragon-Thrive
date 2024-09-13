<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-ab-page-testing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}
?>
<div class="thrive-ab-adv-settings">
	<div class="tvd-row">
		<div class="tvd-col tvd-s12 thrive-ab-no-padding">
			<?php echo __( 'Select one or more pages, from your website, on which the user will land. You can also create a new page and edit later.', 'thrive-ab-page-testing' ) ?>
		</div>
	</div>
	<div id="item-forms" class="tvd-row tvd-collapse"></div>
	<div class="tvd-row tvd-collapse">
		<div class="tvd-col tvd-s12">
			<div class="tvd-card tvd-small tvd-card-new thrive-ab-add-new-goal tvd-valign-wrapper">
				<div class="tvd-card-content tvd-valign tvd-center-align">
					<i class="tvd-icon-plus tvd-icon-rounded tvd-icon-medium"></i>
					<h4>
						<?php echo __( 'Add new goal page', 'thrive-ab-page-testing' ) ?>
					</h4>
				</div>
			</div>
		</div>
	</div>
</div>