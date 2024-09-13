<div class="tvd-error-log-table">
	<div class="tvd-row tvd-collapse">
		<div class="tvd-col tvd-s6">
			<h3 class="tvd-title"><?php echo esc_html__( "Thrive API Connections - error logs", 'thrive-dash' ) ?></h3>
		</div>
		<div class="tvd-col tvd-s6">
			<h5 class="tvd-right-align">
				<span class="tvd-error-log-item-number"></span>
				<span><?php echo esc_html__( "items", 'thrive-dash' ) ?></span>
			</h5>
		</div>
	</div>
	<table class="tvd-collection tvd-with-header tvd-not-fixed-table">
		<thead>
		<tr class="tvd-collection-header">
			<td>
				<h5>
					<?php echo esc_html__( 'Form data', 'thrive-dash' ) ?>
				</h5>
			</td>
			<td class="tvd-error-log-connection tvd-col tvd-s1 tvd_filter_desc tvd-pointer"
				data-val="connection">
				<h5>
					<?php echo esc_html__( 'Service', 'thrive-dash' ) ?>
					<span class="tvd-icon-expanded tvd-orderby-icons"></span>
				</h5>
			</td>
			<td class="tvd-error-log-date tvd-col tvd-s1 tvd-pointer tvd_filter_desc tvd-order-filter-active"
				data-val="date">
				<h5>
					<?php echo esc_html__( 'Date', 'thrive-dash' ) ?>
					<span class="tvd-icon-expanded tvd-orderby-icons"></span>
				</h5>
			</td>
			<td>
				<h5>
					<?php echo esc_html__( 'Error Message', 'thrive-dash' ) ?>
				</h5>
			</td>
			<td class="tvd-center-align">
				<h5>
					<?php echo esc_html__( 'Actions', 'thrive-dash' ) ?>
				</h5>
			</td>
		</tr>
		</thead>
		<tbody class="tvd-error-log-table-content">

		</tbody>
		<tfoot>
		<tr class="tvd-collection-item">
			<td colspan="4">
				<div class="tvd-right">
					<p class="tvd-inline-block tvd-small-text tvd-no-margin">
						<strong>
							<?php echo esc_html__( 'Rows per page', 'thrive-dash' ) ?>
						</strong>
					</p>
					<div class="tvd-input-field tvd-inline-block tvd-input-field-small tvd-no-margin">
						<select class="" id="tvd-row-nr-per-page" tabindex="-1"
								aria-hidden="true">
							<option value="5">5</option>
							<option value="10" selected>10</option>
							<option value="15">15</option>
							<option value="20">20</option>
						</select>
					</div>
					<span class="tvd-inline-block tvd-margin-left"></span>
					<p class="tvd-inline-block tvd-small-text tvd-no-margin">
						<strong>
							<?php echo esc_html__( 'Jump to page', 'thrive-dash' ) ?>
						</strong>
					</p>
					<div class="tvd-input-field tvd-inline-block">
						<input class="tvd-jump-to-page tvd-no-margin tvd-input-field-small" type="text" name="current_page"
							   data-error="<?php echo esc_html__( 'Page doesn\'t exist', 'thrive-dash' ) ?>"
							   id="current-page-input" value="">
					</div>
					<a class="tvd-jump-to-page-button tvd-waves-effect tvd-waves-light tvd-btn tvd-btn-blue tvd-btn-small tvd-margin-left-small"
					   href="javascript:void(0)" tabindex="1"><?php echo esc_html__( 'Go', 'thrive-dash' ) ?></a>
				</div>
			</td>
			<td>
				<div class="tvd-pagination-container">
					<p class="tvd-inline-block tvd-small-text">
						<strong>
							<?php echo esc_html__( 'Page', 'thrive-dash' ) ?>
							<span class="tvd-error-log-current-page"></span>&nbsp;
							<?php echo esc_html__( 'of', 'thrive-dash' ) ?>&nbsp;
							<span class="tvd-error-log-total-pages"></span>
						</strong>
					</p>
					<a class="tvd-previous-page tvd-text-gray tvd-margin-left" href="javascript:void(0)"><span
							class="tvd-icon-chevron-left"></span></a>
					<a class="tvd-next-page tvd-text-gray tvd-margin-left" href="javascript:void(0)"><span
							class="tvd-icon-chevron-right2"></span></a>
				</div>
			</td>
		</tr>
		</tfoot>
	</table>
</div>
<a class="tvd-waves-effect tvd-waves-light tvd-btn-small tvd-btn-gray"
   href="<?php echo esc_url( admin_url( "admin.php?page=tve_dash_api_connect" ) ); ?>"><?php echo esc_html__( "Back to API Connections", 'thrive-dash' ) ?></a>


<script type="text/template" id="tvd-error-log-entry-template">
	<?php include plugin_dir_path( __FILE__ ) . 'error-log-entry.php'; ?>
</script>
