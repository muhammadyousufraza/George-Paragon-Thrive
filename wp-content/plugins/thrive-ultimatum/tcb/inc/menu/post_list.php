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
<div id="tve-post_list-component" class="tve-component" data-view="post_list">
	<div class="dropdown-header component-name" data-prop="docked">
		<?php echo esc_html__( 'Post List', 'thrive-cb' ); ?>
	</div>
	<div class="dropdown-content">
		<?php
		/*
		 * Warning: some of these controls are toggled in JS, don't trust the visibility of anything you see here!
		 * sep-top: adds a border above
		 * sep-bottom: adds a border below
		 * no-space: removes paddings/margins for sub-elements inside the 'tve-control'
		 */
		?>
		<div class="row sep-bottom tcb-text-center post-list-actions">
			<div class="col-xs-12">
				<button class="tve-button orange click" data-fn="editMode"><?php echo esc_html__( 'Edit Design', 'thrive-cb' ); ?></button>
				<button class="tve-button grey click margin-left-20" data-fn="filterPosts"><?php echo esc_html__( 'Filter Posts', 'thrive-cb' ); ?></button>
			</div>
		</div>

		<div class="tve-control mt-10 hide-tablet hide-mobile full-width" data-view="Type"></div>

		<div class="tve-control sep-top sep-bottom no-space hide-tablet hide-mobile" data-view="Featured"></div>

		<div class="tve-control mt-5" data-view="ColumnsNumber"></div>
		<div class="tve-control sep-top" data-view="HorizontalSpace"></div>
		<div class="tve-control mt-5 mb-5 no-space" data-view="VerticalSpace"></div>

		<div class="tve-control hide-tablet hide-mobile" data-view="PaginationType"></div>

		<div class="tve-control sep-bottom no-space post-list-actions" data-view="NumberOfItems"></div>

		<div class="post-list-content-controls">
			<div class="tve-control mt-5 hide-tablet hide-mobile" data-view="ContentSize"></div>
			<div class="tve-control hide-tablet hide-mobile" data-view="WordsTrim"></div>

			<div class="tve-control no-space hide-tablet hide-mobile" data-view="ReadMoreText"></div>
			<div class="info-text grey-text sep-bottom pb-5">
			<span>
				<?php echo esc_html__( "This is added after the post content, it doesn't apply to the Read More button.", 'thrive-cb' ); ?>
			</span>
			</div>

			<hr class="mt-5 mb-5">
		</div>

		<div class="tve-control no-space hide-tablet hide-mobile" data-view="Linker"></div>
		<div class="info-text orange hide-tablet hide-mobile tve-post-list-link-info">
			<?php echo esc_html__( 'This option disables all animations for this element and all child link options', 'thrive-cb' ); ?>
		</div>
	</div>
</div>
