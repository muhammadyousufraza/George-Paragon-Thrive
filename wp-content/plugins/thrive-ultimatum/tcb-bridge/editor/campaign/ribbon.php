<?php
global $design;
$key  = '';
$type = '';
if ( ! empty( $design[ TVE_Ult_Const::FIELD_TEMPLATE ] ) ) {
	list( $type, $key ) = TU_Template_Manager::tpl_type_key( $design[ TVE_Ult_Const::FIELD_TEMPLATE ] );
}
$key               = preg_replace( '#_v(.+)$#', '', $key );
$is_header_bar     = $design['post_type'] == TVE_Ult_Const::DESIGN_TYPE_HEADER_BAR;
$design_body_class = 'tve-ult-body-ribbon' . ( ! $is_header_bar ? '-footer' : '' );

$css = $is_header_bar ? 'bottom: 5%;' : 'top: 5%;';

include TVE_Ult_Const::plugin_path() . 'tcb-bridge/editor/head.php'; ?>

<div id="tve-ult-editor-replace">
	<div class="tvu-triggered tve-ult-bar<?php if ( ! $is_header_bar ) {
		echo ' tvu-footer';
	} else echo ' tvu-header' ?>">
		<div class="tl-style" id="tvu_<?php echo $key ?>" data-state="<?php echo $design['id'] ?>">
			<?php echo tve_ult_editor_custom_content( $design ) ?>
		</div>
		<?php //echo apply_filters( 'tve_leads_variation_append_states', '', $design ); ?>
	</div>
	<div class="tve-ult-template-description"
		 style="opacity: .6;  text-align: center;  z-index: -1; position: absolute; left: 50%; transform: translateX(-50%);width: fit-content;  <?php echo $css; ?>">
		<?php if ( $is_header_bar ) : ?>
			<h4><?php echo __( 'This is a Design type called "Header Bar". It is displayed on the top of the page and it\'s usually a long horizontal bar', 'thrive-ult') ?></h4>
			<h4><?php echo __( 'The content of the page will be scrolled down with the same amount as the ribbon\'s height', 'thrive-ult') ?></h4>
			<h4><?php echo __( 'The ribbon will always stay on top, even when the user scrolls the page', 'thrive-ult') ?></h4>
		<?php else : ?>
			<h4><?php echo __( 'This is a Design type called "Footer Bar". It is displayed on the bottom of the page and it\'s usually a long horizontal bar', 'thrive-ult') ?></h4>
		<?php endif ?>
	</div>
</div>

<?php include TVE_Ult_Const::plugin_path( 'tcb-bridge/editor/footer.php' ); ?>
