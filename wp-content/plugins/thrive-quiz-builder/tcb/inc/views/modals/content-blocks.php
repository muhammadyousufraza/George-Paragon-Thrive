<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 2/15/2019
 * Time: 12:26 PM
 */
?>
<div class="error-container"></div>
<div class="cb-modal-step" data-step="1">
	<div class="tve-modal-content">
		<div id="cb-cloud-menu" class="modal-sidebar tcb-hidden">
			<select id="cb-pack-select" class="change" data-fn="dom_pack_changed"></select>
			<span><?php echo esc_html__( 'CATEGORY', 'thrive-cb' ); ?></span>
			<div id="cb-pack-categories"></div>
			<div class="cb-filter-wrapper">
				<span>
					<?php echo esc_html__( 'Block types', 'thrive-cb' ); ?>
				</span>
				<div>
					<input type="text" id="filter_groups" class="keyup" data-fn-keyup="filter_groups" placeholder="<?php echo esc_html__( 'Filter Blocks...', 'thrive-cb' ); ?>">
					<span>
						<?php tcb_icon( 'search-regular', false, 'sidebar', 'cb-search-icon' ); ?>
						<?php tcb_icon( 'times-regular', false, 'sidebar', 'cb-clear-search click', [ 'data-fn' => 'dom_clear_groups_search' ] ); ?>
					</span>
				</div>

			</div>
			<div class="cb-groups-wrapper">
				<div id="cb-pack-groups"></div>
				<a href="javascript:void(0);" class="cb-clear-search click" data-fn="dom_clear_groups_search"><?php echo esc_html__( 'Show All', 'thrive-cb' ); ?></a>
			</div>
		</div>
		<div id="cb-cloud-templates" class="modal-content">
			<div id="cb-pack-title" class="tcb-modal-title"></div>

			<div id="cb-pack-content"></div>
		</div>
	</div>
</div>
<div class="cb-modal-step" data-step="2" style="display: none;">
	<div class="cb-preview-bar">
		<div class="cb-preview-title">
			<a href="javascript:void(0);" id="cb-frame-favorite" class="click" data-id="" data-fn="dom_prev_fav">
				<span data-tooltip=""></span>
			</a>
			<div class="cb-separator"></div>
			<div id="cb-frame-title"></div>
		</div>
	</div>
	<div class="cb-iframe-wrapper">
		<iframe id="cb-preview-frame"></iframe>
	</div>
	<div class="cb-steps">
		<div class="tcb-left">
			<a class="tve-button click" href="javascript:void(0);" data-fn="dom_go_to_step" data-step="1">
				<span class="cb-back"><?php tcb_icon( 'chevron-up-regular' ); ?></span>
				<span><?php echo esc_html__( 'Back to Blocks', 'thrive-cb' ) ?></span>
			</a>
		</div>
		<div class="tcb-right">
			<button id="cb-preview-insert-block" type="button" class="tve-button medium click" data-fn="dom_insert_into_content">
				<?php tcb_icon( 'arrow-alt-to-bottom-light' ); ?>
				<div class="cb-separator"></div>
				<?php echo esc_html__( 'Insert Into Content', 'thrive-cb' ) ?>
			</button>
		</div>
	</div>
</div>
