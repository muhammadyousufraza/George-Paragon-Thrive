<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

?>
<div id="tve-ct-symbol-component" class="tve-component" data-view="CtSymbol">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="pb-10 tcb-text-center grey-text">
			<?php echo esc_html__( 'This is a symbol. You can edit it as a global element( it updates simultaneously all over the places you used it) or unlink it and you edit it as a regular element', 'thrive-cb' ); ?>
		</div>
		<hr>
		<div class="row pb-10">
			<div class="col-xs-6">
				<button class="tve-button grey long click" data-fn="edit_symbol">
					<?php echo esc_html__( 'Edit as Symbol', 'thrive-cb' ); ?>
				</button>
			</div>

			<div class="col-xs-6">
				<button class="tve-button blue long click" data-fn="unlink_symbol">
					<?php echo esc_html__( 'Unlink', 'thrive-cb' ); ?>
				</button>
			</div>
		</div>
	</div>
</div>
