<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 1/21/2019
 * Time: 3:27 PM
 */
//Central Style Panel Drawer

$has_data = array(
	'styles' => ! empty( $data['styles']['button'] ) || ! empty( $data['styles']['section'] ) || ! empty( $data['styles']['contentbox'] ),
	'vars'   => ! empty( $data['vars']['colours'] ) || ! empty( $data['vars']['gradients'] ) || ! empty( $data['skin_palettes'] ),
	'fonts'  => true,
);

?>
<div id="c-s-p-content" class="scrollbar">
	<div class="items-4 input tve-btn-group tve-csp-button-group">
		<?php if ( $has_data['vars'] ) : ?>
			<div class="tve-btn click" data-fn="dom_switch_section" data-value="vars">
				<span><?php tcb_icon( 'palette' ); ?></span>
				<span><?php echo esc_html__( 'Colors', 'thrive-cb' ); ?></span>
			</div>
		<?php endif; ?>
		<?php if ( $has_data['styles'] ) : ?>
			<div class="tve-btn click" data-fn="dom_switch_section" data-value="styles">
				<span><?php tcb_icon( 'blocks' ); ?></span>
				<span><?php echo esc_html__( 'Elements', 'thrive-cb' ); ?></span>
			</div>
		<?php endif; ?>
		<?php if ( $has_data['fonts'] ) : ?>
			<div class="tve-btn click" data-fn="dom_switch_section" data-value="fonts">
				<span><?php tcb_icon( 'typography' ); ?></span>
				<span><?php echo esc_html__( 'Fonts', 'thrive-cb' ); ?></span>
			</div>
		<?php endif; ?>
	</div>

	<?php if ( $has_data['styles'] ) : ?>
		<div class="c-s-p-section" data-section="styles">
			<?php foreach ( $data['styles'] as $key => $set_templates ) : ?>
				<?php if ( empty( $set_templates ) ) : ?>
					<?php continue; ?>
				<?php endif; ?>
				<div class="c-s-p-tpl-list-wrapper">
					<span class="c-s-p-list-title"></span>
					<div class="c-s-p-tpl-list" data-list="<?php echo esc_attr( $key ); ?>"></div>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<?php if ( $has_data['vars'] ) : ?>
		<div class="c-s-p-section" data-section="vars"></div>
	<?php endif; ?>

	<?php if ( $has_data['fonts'] ) : ?>
		<div class="c-s-p-section" data-section="fonts"></div>
	<?php endif; ?>
</div>
