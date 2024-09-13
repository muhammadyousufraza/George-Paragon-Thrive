<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package TCB2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

?>
<div id="tve-toc-component" class="tve-component" data-view="TOC">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="hide-states">
			<div class="tve-toc-default">
				<div class="tcb-text-center mb-10 mr-5 ml-5">
					<button class="tve-button orange click" data-fn="editElement">
						<?php echo esc_html__( 'Edit table of contents', 'thrive-cb' ); ?>
					</button>
				</div>
				<div class="tve-control tve-toc-control" data-view="TOCPalettes"></div>
				<div class="tve-control tve-toc-control" data-view="Headings"></div>
				<span class="click blue-text center-text mt-5 mb-10 flex-mid" data-fn="refresh">
						<?php tcb_icon( 'sync-regular' ); ?>&nbsp; <?php echo esc_html__( 'Update table', 'thrive-cb' ) ?>
					</span>
				<hr>
				<div class="tve-control" data-view="Columns"></div>
				<div class="tve-control" data-view="Evenly"></div>
				<div class="tve-control" data-view="Highlight"></div>
				<div class="tve-control" data-view="Indent"></div>
				<div class="tve-control" data-view="Numbering"></div>
				<div class="tve-control" data-view="ScrollAnimation"></div>
				<div class="tve-control" data-view="Expandable"></div>
				<div class="tve-expandable-toc mb-10">
					<div class="tve-control" data-view="DefaultState"></div>
					<div class="tve-control" data-view="DropdownAnimation"></div>
					<div class="tve-control" data-view="AnimationSpeed"></div>
				</div>
				<div class="tve-control" data-view="TextSize"></div>
				<div class="tve-control" data-key="LineSpacing" data-initializer="lineSpacingControl"></div>
				<div class="tve-advanced-controls toc-items">
					<div class="dropdown-header" data-prop="advanced">
						<span>
							<?php echo esc_html__( 'Modify Headings', 'thrive-cb' ); ?>
						</span>
					</div>
					<div class="dropdown-content pl-0 pr-0 tcb-relative pt-5">
						<div class="tve-control tve-order-list mb-0" data-key="HeadingList" data-initializer="initHeadingList"></div>
						<a class="tve-button click grey tcb-right mt-15" data-fn="resetHeadings">
							<i class="mr-5">
								<?php tcb_icon( 'sync-regular' ); ?>
							</i>
							<?php echo esc_html__( 'Sync', 'thrive-cb' ); ?>
						</a>
					</div>

				</div>
			</div>
		</div>
	</div>
</div>
