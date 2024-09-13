<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package TCB2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

?>
<div id="tve-toc_old-component" class="tve-component" data-view="TOCOld">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="hide-states">
			<div class="tve-control tve-toc-control" data-view="Headings"></div>
			<span class="click blue-text center-text mt-5 mb-10 flex-mid" data-fn="refresh">
				<?php tcb_icon( 'sync-regular' ); ?>&nbsp; <?php echo esc_html__( 'Update Table', 'thrive-cb' ) ?>
			</span>
			<hr>
			<div class="tve-control" data-view="HeaderColor"></div>
			<div class="tve-control" data-view="HeadBackground"></div>
			<div class="tve-control" data-view="Columns"></div>
			<div class="tve-control" data-view="Evenly"></div>
		</div>
	</div>
</div>
