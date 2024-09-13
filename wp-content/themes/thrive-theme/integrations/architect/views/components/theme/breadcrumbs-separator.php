<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

?>

<div id="tve-breadcrumbs_separator-component" class="tve-component" data-view="BreadcrumbsSeparator">
	<div class="dropdown-header" data-prop="docked">
		<?php echo __( 'Separator Options', 'thrive-theme' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="separator-type-controls mb-10" data-separator="icon">
			<div class="tve-control" data-view="IconPicker"></div>
		</div>
		<div class="separator-type-controls mb-10" data-separator="character">
			<div class="tve-control" data-view="CharacterInput"></div>
		</div>
		<hr>
		<div class="tve-control mb-5" data-view="SeparatorColor"></div>
		<div class="tve-control mb-5" data-view="SeparatorSize"></div>
	</div>
</div>
