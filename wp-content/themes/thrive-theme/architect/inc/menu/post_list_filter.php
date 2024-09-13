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
<div id="tve-post_list_filter-component" class="tve-component" data-view="PostListFilter">
	<div class="dropdown-header component-name" data-prop="docked">
		<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
	</div>
	<div class="dropdown-content">
		<div class="tve-control" data-view="FilterType"></div>
		<div class="tve-control" data-view="FilterOption"></div>
		<div class="tve-control" data-view="MultipleSelections"></div>
		<div class="tve-advanced-controls tve-option-selection-control mb-10">
			<div class="dropdown-header" data-prop="advanced">
				<span class="options-type"></span>
			</div>
			<div class="dropdown-content pt-5 pb-0">
				<div class="tve-control options-selection-control" data-view="OptionsSelection"></div>
			</div>
		</div>
		<div class="tve-control" data-view="AllOption"></div>
		<div class="tve-control" data-view="AllLabel"></div>
		<div class="tve-control" data-view="DefaultValue"></div>
		<div class="tve-control" data-view="URLQueryKey"></div>
		<div class="mb-10">
			<div class="info-text grey-text">
					<span>
						<?php echo __( 'The query must match the dynamic filter on a post list for filtering to apply.', 'thrive-cb' ); ?>
						<a href="https://help.thrivethemes.com/en/articles/6533678-how-to-use-the-post-list-filter-element#h_f7158be702" target="_blank" class="blue-text">
							<?php echo __( 'Learn more.', 'thrive-cb' ); ?>
						</a>
					</span>
			</div>
		</div>
		<div class="pb-10 mb-10 tve-grey-box filter-display-options">
			<div class="tve-control" data-view="DisplayOption"></div>
			<div class="tve-control" data-view="HorizontalSpace"></div>
			<div class="tve-control" data-view="VerticalSpace"></div>
		</div>
	</div>
</div>
