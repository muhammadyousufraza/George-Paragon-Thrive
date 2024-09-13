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
<div id="tve-blog_list-component" class="tve-component" data-view="BlogList">
	<div class="dropdown-header component-name" data-prop="docked">
		<?php echo __( 'Blog List', 'thrive-theme' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="row sep-bottom tcb-text-center post-list-actions">
			<div class="col-xs-12">
				<button class="tve-button orange click" data-fn="editMode"><?php echo __( 'Edit Design', 'thrive-theme' ); ?></button>
				<button class="tve-button grey click margin-left-20" data-fn="filterBlog"><?php echo __( 'Filter Posts', 'thrive-theme' ); ?></button>
			</div>
		</div>

		<div class="tve-control mt-10 hide-tablet hide-mobile full-width" data-view="Type"></div>

		<div class="tve-control sep-top sep-bottom no-space hide-tablet hide-mobile" data-view="Featured"></div>

		<div class="tve-control mt-5" data-view="ColumnsNumber"></div>

		<div class="tve-control sep-top" data-view="HorizontalSpace"></div>
		<div class="tve-control mt-5 mb-5 no-space sep-bottom" data-view="VerticalSpace"></div>

		<div class="tve-control hide-tablet hide-mobile" data-view="PaginationType"></div>
		<div class="tve-control sep-bottom no-space post-list-actions" data-view="NumberOfItems"></div>

		<div class="post-list-content-controls">
			<div class="tve-control mt-5 hide-tablet hide-mobile" data-view="ContentSize"></div>
			<div class="tve-control hide-tablet hide-mobile" data-view="WordsTrim"></div>

			<div class="tve-control no-space hide-tablet hide-mobile" data-view="ReadMoreText"></div>
			<div class="info-text grey-text sep-bottom pb-5">
			<span>
				<?php echo __( "This is added after the post content, it doesn't apply to the Read More button.", 'thrive-theme' ); ?>
			</span>
			</div>

			<hr class="mt-5 mb-5">
		</div>

		<div class="tve-control no-space hide-tablet hide-mobile" data-view="Linker"></div>
		<div class="info-text orange hide-tablet hide-mobile tve-post-list-link-info">
			<?php echo __( 'This option disables all animations for this element and all child link options', 'thrive-theme' ); ?>
		</div>
	</div>
</div>
