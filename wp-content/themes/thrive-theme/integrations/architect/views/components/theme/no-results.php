<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

?>
<div id="tve-no_results-component" class="tve-component" data-view="NoResults">
	<div class="dropdown-header component-name" data-prop="docked">
		<?php echo __( 'No Results', 'thrive-theme' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control" data-view="BoxWidth"></div>
		<div class="tve-control" data-view="BoxHeight"></div>
		<hr>
		<div class="tve-control no-space" data-key="ToggleURL" data-extends="Switch" data-label="<?php echo __( 'Add link to Content Box', 'thrive-theme' ); ?>"></div>
		<div class="cb-link mt-10"></div>
		<div class="row mt-10">
			<div class="col-xs-12">
				<div class="tve-control" data-view="VerticalPosition"></div>
			</div>
		</div>

		<div class="tve-bg-img">
			<hr class="mt-10">
			<div class="tcb-label mb-10"><?php echo __( 'Background Image', 'thrive-theme' ); ?><span class="click tve-cb-img-info ml-5" data-fn="openTooltip"><?php tcb_icon( 'info-circle-solid' ); ?></span></div>
			<div class="control-grid full-width">
				<a class="image-picker click" href="javascript:void(0)" data-fn="replaceBgImage">
					<span class="preview"><?php tcb_icon( 'image-solid' ); ?></span>
					<span class="text"><?php echo __( 'Replace Image', 'thrive-theme' ); ?></span>
					<?php tcb_icon( 'exchange-regular' ); ?>
				</a>
			</div>
		</div>
	</div>
</div>
