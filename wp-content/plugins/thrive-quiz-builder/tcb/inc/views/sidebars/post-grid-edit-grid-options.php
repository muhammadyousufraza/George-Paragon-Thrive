<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 5/30/2017
 * Time: 11:30 AM
 */
?>
<div id="tve-postgrid-layout-component" class="tve-component" data-view="PostGridLayout">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Layout', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control" data-view="grid_layout"></div>
		<div class="tve-control" data-view="number_of_columns"></div>
		<div class="tve-control" data-view="display"></div>
		<div class="tve-control" data-view="text_type"></div>
		<hr>
		<span class="grey-text"><?php echo esc_html__( 'Teaser Layout', 'thrive-cb' ); ?></span>
		<div class="row">
			<div class="col-xs-6">
				<div class="tve-control" data-view="featured_image"></div>
			</div>
			<div class="col-xs-6">
				<div class="tve-control" data-view="title"></div>
			</div>
			<div class="col-xs-6">
				<div class="tve-control" data-view="read_more_lnk"></div>
			</div>
			<div class="col-xs-6">
				<div class="tve-control" data-view="text"></div>
			</div>
		</div>
		<hr>
		<span class="grey-text"><?php echo esc_html__( 'Display Order', 'thrive-cb' ); ?></span>
		<div class="tve-control" data-key="preview" data-initializer="order_control"></div>
	</div>
</div>

<div id="tve-postgrid-query-component" class="tve-component" data-view="PostGridQuery">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Query', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control" data-view="content"></div>
		<div class="tve-control" data-view="number_of_posts"></div>
		<div class="tve-control" data-view="order_by"></div>
		<div class="tve-control" data-view="order_mode"></div>
		<div class="control-grid no-space">
			<div class="label"><?php echo esc_html__( 'Show Items More recent than', 'thrive-cb' ); ?></div>
			<div class="tve-control input" data-view="recent_days"></div>
		</div>
		<div class="tve-control" data-view="start"></div>
	</div>
</div>

<div id="tve-postgrid-filter-component" class="tve-component" data-view="PostGridFilter">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Filters', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control" data-view="categories"></div>
		<hr>
		<div class="tve-control" data-view="tags"></div>
		<hr>
		<div class="tve-control" data-view="authors"></div>
		<hr>
		<div class="tve-control" data-view="custom_taxonomies"></div>
		<hr>
		<div class="tve-control" data-view="individual_post_pages"></div>
	</div>
</div>
