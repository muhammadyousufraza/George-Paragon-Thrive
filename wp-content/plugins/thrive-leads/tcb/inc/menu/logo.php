<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

?>

<div id="tve-<?php echo esc_attr( TCB_Logo::COMPONENT ); ?>-component" class="tve-component" data-view="Logo">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Logo Options', 'thrive-cb' ); ?>
	</div>
	<div class="dropdown-content">
		<div class="tve-logo-p">
			<?php echo esc_html__( "The logo element is a global element, which means it updates simultaneously in all places it's used on your site.", 'thrive-cb' ); ?>
		</div>
		<div class="tcb-logo-selection-container">
			<span><?php echo esc_html__( 'Select a Logo', 'thrive-cb' ); ?></span>

			<ul class="tcb-default-logos tcb-logo-container"></ul>

			<ul class="tcb-user-logos tcb-logo-container"></ul>

			<button class="tve-logo-button click" data-fn="addClickHandler">+ <?php echo esc_html__( 'Add New Logo Variation', 'thrive-cb' ); ?></button>
			<hr/>
		</div>
		<div class="tve-control mt-10" data-view="ImageSize"></div>
		<div class="tve-control" data-view="MenuSplitLogoPosition"></div>
		<div class="tve-control" data-view="ImageAltText"></div>
		<hr>
		<div class="tve-control no-space mb-5" data-view="ImageLoading"></div>
		<div class="tve-control no-space" data-key="ToggleURL" data-extends="Switch" data-label="<?php echo esc_html__( 'Add link to Logo', 'thrive-cb' ); ?>"></div>
		<div class="logo-link mt-10"></div>
	</div>
</div>
