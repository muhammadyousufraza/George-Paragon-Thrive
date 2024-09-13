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

<div id="tve-breadcrumbs_leaf-component" class="tve-component" data-view="BreadcrumbsLeaf">
	<div class="dropdown-header" data-prop="docked">
		<?php echo __( 'Breadcrumb Leaf Options', 'thrive-theme' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control mt-5" data-view="EnableTruncateChars"></div>
		<div class="tve-control mt-5 tcb-hidden" data-view="CharactersTruncate"></div>
	</div>
</div>
