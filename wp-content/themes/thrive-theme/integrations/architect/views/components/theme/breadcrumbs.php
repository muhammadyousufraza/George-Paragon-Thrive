<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

$template = thrive_template();

?>

<div id="tve-thrive_breadcrumbs-component" class="tve-component" data-view="Breadcrumbs">
	<div class="dropdown-header" data-prop="docked">
		<?php echo __( 'Breadcrumbs Options', 'thrive-theme' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tcb-text-center mb-10 mr-5 ml-5">
			<button class="tve-button orange click" data-fn="editBreadcrumbsElements">
				<?php echo __( 'Edit Breadcrumbs', 'thrive-theme' ); ?>
			</button>
		</div>
		<div class="tve-control mb-5" data-view="SeparatorType"></div>

		<div class="separator-type-controls" data-separator="icon">
			<div class="tve-control mb-5" data-view="IconPicker"></div>
		</div>
		<div class="separator-type-controls" data-separator="character">
			<div class="tve-control mb-5" data-view="CharacterInput"></div>
		</div>
		<div class="tcb-hide-if-no-separator">
			<div class="tve-control mb-5" data-view="SeparatorColor"></div>
			<div class="tve-control mb-5" data-view="SeparatorSize"></div>
		</div>
		<div class="tve-control mb-5" data-view="ItemSpacing"></div>
		<div class="tve-control mb-5" data-view="Alignment"></div>

		<?php if ( $template->is_singular() && in_array( $template->get_secondary(), [ THRIVE_POST_TEMPLATE, TCB\Integrations\WooCommerce\Main::POST_TYPE ] ) ) : ?>
			<div class="tve-control mb-5" data-view="ShowCategoriesInPath"></div>
		<?php endif; ?>

		<div class="tve-advanced-controls">
			<div class="dropdown-header" data-prop="advanced">
				<span><?php echo __( 'Customize labels', 'thrive-theme' ); ?></span>
			</div>

			<div class="dropdown-content pt-0 breadcrumbs-labels">
				<?php foreach ( Thrive_Breadcrumbs::get_default_labels() as $key => $label ) : ?>
					<div class="tve-control mb-5" data-view="<?php echo $key . 'Label'; ?>"></div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</div>
