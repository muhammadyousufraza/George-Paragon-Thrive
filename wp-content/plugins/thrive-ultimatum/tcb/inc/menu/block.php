<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
} ?>

<div id="tve-block-component" class="tve-component" data-view="Block">
	<div class="dropdown-header" data-prop="docked">
		<div class="group-description">
			<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
		</div>
		<i></i>
	</div>
	<div class="dropdown-content">
		<?php if ( tcb_post()->is_landing_page() ) : ?>
			<div class="tve-control hide-tablet hide-mobile" data-view="InheritFromLandingPage"></div>
		<?php endif; ?>

		<div class="tve-control" data-view="ContentWidth"></div>
		<div class="tve-control" data-view="ContentFullWidth"></div>
		<hr>
		<div class="tve-control" data-view="SectionHeight"></div>
		<div class="tve-control" data-view="FullHeight"></div>
		<hr>
		<div class="tve-control" data-view="VerticalPosition"></div>
		<hr>
		<div class="tve-control" data-view="SectionFullWidth"></div>
	</div>
</div>

