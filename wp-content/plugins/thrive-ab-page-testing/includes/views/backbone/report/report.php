<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 11/27/2017
 * Time: 11:21 AM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}
?>
<div id="thrive-ab-top-bar" class="tvd-row thrive-ab-logo-holder">
	<div class="tvd-col tvd-s12 thrive-ab-logo">
		<a href="<?php echo admin_url( 'admin.php?page=tab_admin_dashboard' ); ?>" class="logo-holder"></a>
	</div>
</div>
<?php echo tvd_get_individual_plugin_license_message( new Thrive_AB_Product(), true ); ?>
<div class="tvd-row thrive-ab-container">
	<div class="tvd-col tvd-s12">
		<h1 class="thrive-ab-variation-title">
			<#= model.get('title') #>
		</h1>
	</div>
</div>
<div class="tvd-row thrive-ab-container">
	<div class="tvd-col tvd-s4 tvd-m6">
		<#= model.get('notes') #>&nbsp;
	</div>
	<div class="tvd-col tvd-s2 tvd-m1">
		<label for="tve-chart-interval-select" class="thrive-ab-interval-label">
			<?php echo __( 'Graph type', 'thrive-ab-page-testing' ); ?>:
		</label>
	</div>
	<div class="tvd-col tvd-s2 tvd-m2">
		<div class="tve-chart-interval">
			<div class="tvd-input-field">
				<select autocomplete="off" class="change tab-graph-type" data-fn="update_chart">
					<option value="conversion_rate"><?php echo __( 'Conversion rate', 'thrive-ab-page-testing' ); ?></option>
					<option value="conversion"><?php echo __( 'Conversions', 'thrive-ab-page-testing' ); ?></option>
				</select>
			</div>
		</div>
	</div>
	<div class="tvd-col tvd-s2 tvd-m1">
		<label for="tve-chart-interval-select" class="thrive-ab-interval-label">
			<?php echo __( 'Graph interval', 'thrive-ab-page-testing' ); ?>:
		</label>
	</div>
	<div class="tvd-col tvd-s2 tvd-m2">
		<div class="tve-chart-interval">
			<div class="tvd-input-field">
				<select autocomplete="off" class="change tab-graph-interval" data-fn="update_chart">
					<option selected value="day"><?php echo __( 'Daily', 'thrive-ab-page-testing' ); ?></option>
					<option value="week"><?php echo __( 'Weekly', 'thrive-ab-page-testing' ); ?></option>
					<option value="month"><?php echo __( 'Monthly', 'thrive-ab-page-testing' ); ?></option>
				</select>
			</div>
		</div>
	</div>
</div>

<div class="tvd-relative tvd-row thrive-ab-container" id="thrive-ab-chart">
	<div class="tvd-col tvd-s10">
		<div id="tab-test-chart"></div>
	</div>
	<div class="tvd-col tvd-s2 thrive-ab-chart-info">
		<p id="thrive-ab-chart-title">
			<#= ThriveAB.test_chart.title #>
		</p>
		<p id="thrive-ab-chart-total-value">
			<#= ThriveAB.test_chart.total_over_time #> <#= ThriveAB.test_chart.test_type_txt #>
		</p>
	</div>
</div>

<div id="thrive-ab-test" class="thrive-ab-container"></div>

<div class="tvd-relative tvd-row" style="display: <#= model.get('status') === 'running' ? 'block' : 'none' #>">
	<div class="tvd-col tvd-s8">
		<a href="<#= edit_page_link #>" class="tvd-btn-flat tvd-btn-flat-primary tvd-btn-flat-dark tvd-waves-effect">
			&laquo; <?php echo __( 'Back to Page Settings', 'thrive-ab-page-testing' ) ?></a>
	</div>
	<div class="tvd-col tvd-s4">
		<div class="tvd-right" id="thrive-ab-stop-test">
			<a class="tvd-waves-effect tvd-waves-light tvd-btn tvd-btn-green click" href="javascript:void(0)" data-fn="stop_test"
			   title="<?php echo __( 'Stop test and choose winner', 'thrive-ab-page-testing' ) ?>"><?php echo __( 'Stop test and choose winner', 'thrive-ab-page-testing' ) ?></a>
		</div>
	</div>
</div>

<div class="tvd-relative tvd-row" style="display: <#= model.get('status') === 'completed' ? 'block' : 'none' #>">
	<div class="tvd-col tvd-s12">
		<div class="tvd-right" id="thrive-ab-stop-test">
			<a class="tvd-waves-effect tvd-waves-light tvd-btn tvd-btn-green click" href="<#= edit_page_link #>">
				<?php echo __( 'Page settings', 'thrive-ab-page-testing' ) ?>
			</a>
		</div>
	</div>
</div>
