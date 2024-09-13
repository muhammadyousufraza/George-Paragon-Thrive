<div class="error-container"></div>
<div style="position: absolute;top: 0;" id="blocks-lightbox-drop-panels"></div>
<div class="tve-modal-content">
	<div id="cb-cloud-menu" class="modal-sidebar">
		<div class="lp-search">
			<?php tcb_icon( 'search-regular' ); ?>
			<input type="text" data-source="search" class="keydown" data-fn="filter" placeholder="<?php echo esc_html__( 'Search', 'thrive-cb' ); ?>"/>
			<?php tcb_icon( 'close2', false, 'sidebar', 'click', [ 'data-fn' => 'domClearSearch' ] ); ?>
		</div>
		<div class="lp-menu-wrapper">
			<div id="block-source-select-wrapper">
				<span class="text-12"><?php echo esc_html__( 'Filter blocks:', 'thrive-cb' ); ?></span>
				<select id="block-source-select" class="change" data-fn="sourceChanged"></select>
			</div>
			<div class="sidebar-title">
				<p><?php echo esc_html__( 'Block types', 'thrive-cb' ); ?></p>
				<span class="tcb-hl"></span>
			</div>
			<div id="lp-groups-wrapper"></div>
		</div>
	</div>
	<div id="cb-cloud-templates" class="modal-content">
		<div class="tcb-modal-tabs flex-center space-between">
			<span id="lp-blk-pack-title" class="tcb-modal-title ml-20"></span>
			<span data-fn="clearCache" class="tcb-refresh mr-30 click flex-center">
				<span class="mr-10"><?php tcb_icon( 'sync-regular' ); ?></span>
				<span class="mr-10"><?php echo esc_html__( 'Refresh from cloud', 'thrive-cb' ); ?></span>
			</span>
		</div>
		<div id="lp-blk-pack-description" class="mb-30 ml-20"></div>
		<div id="cb-pack-content"></div>
	</div>
</div>
