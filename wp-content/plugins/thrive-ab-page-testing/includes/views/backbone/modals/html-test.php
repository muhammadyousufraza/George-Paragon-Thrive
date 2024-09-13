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
<div class="tvd-modal-step thrive-ab-start-test">
	<h3 class="tvd-modal-title"><?php echo __( 'Starting your A/B test', 'thrive-ab-page-testing' ) ?></h3>
	<div class="tvd-modal-content">

		<?php include dirname( __FILE__ ) . '/html-test-steps.php' ?>

		<div class="thrive-ab-content">
			<p><?php echo __( 'Set your test details below', 'thrive-ab-page-testing' ) ?></p>
			<div class="tvd-input-field">
				<input type="text" id="title" data-bind="title" value="<#= this.model.get('title') #>">
				<label for="title" class=""><?php echo __( 'Split Test Name', 'thrive-ab-page-testing' ) ?></label>
			</div>
			<div class="tvd-input-field">
				<textarea class="tvd-materialize-textarea" data-bind="notes"><#= this.model.get('notes') #></textarea>
				<label for="tvd-ar-install-url" class=""><?php echo __( 'Short Description', 'thrive-ab-page-testing' ) ?></label>
			</div>
			<?php include dirname( __FILE__ ) . '/html-test-automatic-winner.php'; ?>
		</div>
	</div>
	<div class="tvd-modal-footer">
		<div class="tvd-row">
			<div class="tvd-col tvd-s12 tvd-m6">
				<a href="javascript:void(0)"
				   class="tvd-btn-flat tvd-btn-flat-secondary tvd-btn-flat-dark tvd-waves-effect tvd-modal-close">
					<?php echo __( 'Cancel', 'thrive-ab-page-testing' ) ?>
				</a>
			</div>
			<div class="tvd-col tvd-s12 tvd-m6">
				<a href="javascript:void(0)"
				   class="tvd-waves-effect tvd-waves-light tvd-btn tvd-btn-green tvd-right tvd-modal-next-step thrive-ab-next">
					<?php echo __( 'Next', 'thrive-ab-page-testing' ) ?>
				</a>
			</div>
		</div>
	</div>
</div>
<div class="tvd-modal-step thrive-ab-start-test thrive-ab-step-2">
	<h3 class="tvd-modal-title"><?php echo __( 'Starting your A/B test', 'thrive-ab-page-testing' ) ?></h3>
	<div class="tvd-modal-content">

		<?php include dirname( __FILE__ ) . '/html-test-steps.php' ?>

		<div class="tvd-row thrive-ab-set-goal">
			<?php foreach ( Thrive_AB_Test_Manager::$types as $type ) : ?>
				<?php Thrive_AB_Test_Manager::display_goal_option( $type ); ?>
			<?php endforeach; ?>
		</div>
		<div id="thrive-ab-goal-settings"></div>
	</div>
	<div class="tvd-modal-footer">
		<div class="tvd-row">
			<div class="tvd-col tvd-s12 tvd-m6">
				<a href="javascript:void(0)"
				   class="tvd-btn-flat tvd-btn-flat-secondary tvd-btn-flat-dark tvd-waves-effect tvd-modal-prev-step thrive-ab-prev">
					<?php echo __( 'Back', 'thrive-ab-page-testing' ) ?>
				</a>
			</div>
			<div class="tvd-col tvd-s12 tvd-m6">
				<a href="javascript:void(0)"
				   class="tvd-waves-effect tvd-waves-light tvd-btn tvd-btn-green tvd-right tvd-modal-submit">
					<?php echo __( 'Start A/B Test', 'thrive-ab-page-testing' ) ?>
				</a>
			</div>
		</div>
	</div>
</div>
