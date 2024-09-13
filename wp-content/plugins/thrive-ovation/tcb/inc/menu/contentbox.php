<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
} ?>

<div id="tve-contentbox-component" class="tve-component" data-view="ContentBox">
	<div class="action-group">
		<div class="dropdown-header" data-prop="docked">
			<div class="group-description">
				<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
			</div>
			<i></i>
		</div>
		<div class="dropdown-content">
			<div class="tve-control hide-states" data-view="ContentPalettes"></div>
			<div class="tve-control" data-view="BoxWidth"></div>
			<div class="tve-control" data-view="BoxHeight"></div>
			<hr>
			<div class="tve-control no-space" data-key="ToggleURL" data-extends="Switch" data-label="<?php echo esc_attr__( 'Add link to Content Box', 'thrive-cb' ); ?>"></div>
			<div class="cb-link mt-10"></div>
			<div class="row mt-10">
				<div class="col-xs-12">
					<div class="tve-control" data-view="VerticalPosition"></div>
				</div>
			</div>

			<div class="tve-bg-img">
				<hr class="mt-10">
				<div class="tcb-label mb-10"><?php echo esc_html__( 'Background Image', 'thrive-cb' ); ?><span class="click tve-cb-img-info ml-5" data-fn="openTooltip"><?php tcb_icon( 'info-circle-solid' ); ?></span></div>
				<div class="control-grid full-width">
					<a class="image-picker click" href="javascript:void(0)" data-fn="replaceBgImage">
						<span class="preview"><?php tcb_icon( 'image-solid' ); ?></span>
						<span class="text"><?php echo esc_html__( 'Replace Image', 'thrive-cb' ); ?></span>
						<?php tcb_icon( 'exchange-regular' ); ?>
					</a>
				</div>
			</div>
		</div>
	</div>
</div>
