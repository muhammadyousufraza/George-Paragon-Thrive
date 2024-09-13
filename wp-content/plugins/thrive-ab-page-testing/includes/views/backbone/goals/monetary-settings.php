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
			<?php echo __( 'Measure revenue when...', 'thrive-ab-page-testing' ); ?>
		</div>
		<select name="service" id="thrive-ab-monetary-services">
			<option value=""><?php echo __( 'Select how you would like to measure revenue', 'thrive-ab-page-testing' ); ?></option>
		</select>
	</div>
	<div class="thrive-ab-monetary-service" id="sendowl">
		<p>
			<?php
			/* translators: */
			echo sprintf( __( '%s will receive revenue data automatically from SendOwl when a purchase is made.', 'thrive-ab-page-testing' ), thrive_ab()->plugin_name() );
			?>
		</p>
		<p>
			<?php
			$admin_url = add_query_arg(
				array(
					'page' => 'tva_dashboard',
				),
				network_admin_url( 'admin.php' )
			);
			$link      = '<a target="_blank" href="' . $admin_url . '#sendowl_quick_start">' . __( 'setup steps', 'thrive-ab-page-testing' ) . '</a>';
			/* translators: */
			echo sprintf( __( 'Make sure you complete all the SendOwl %s in Thrive Apprentice for the integration to work.', 'thrive-ab-page-testing' ), $link );
			?>
		</p>
	</div>
	<div class="thrive-ab-monetary-service" id="visit_page">
		<div class="tvd-row">
			<div class="tvd-col tvd-s12 thrive-ab-no-padding">
				<?php echo __( 'Select one or more pages from your website, on which the user will land, and write the corresponding value of that conversion for your business. You can also create a new page and edit later.', 'thrive-ab-page-testing' ); ?>
			</div>
		</div>
		<div id="item-forms" class="tvd-row tvd-collapse"></div>
		<div class="tvd-row tvd-collapse">
			<div class="tvd-col tvd-s12">
				<div class="tvd-card tvd-small tvd-card-new thrive-ab-add-new-goal tvd-valign-wrapper">
					<div class="tvd-card-content tvd-valign tvd-center-align">
						<i class="tvd-icon-plus tvd-icon-rounded tvd-icon-medium"></i>
						<h4>
							<?php echo __( 'Add new thank you page', 'thrive-ab-page-testing' ); ?>
						</h4>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
