<?php
global $variation;

include tqb()->plugin_path( 'tcb-bridge/editor/page/head.php' ); ?>
<div style="display: none" class="bSe"></div>
<div id="tqb-editor-replace">
	<div id="tve_flt" class="tve_flt">
		<div class="tqb-template-style-<?php echo esc_attr( $variation['style'] ); ?>">
			<?php echo TCB_Hooks::tqb_editor_custom_content( $variation ); ?>
		</div>
	</div>
	<div style="opacity: .6; padding-top: 240px; text-align: center; position: relative; z-index: -1;">
		<h4><?php echo esc_html__( 'This is a Variation type called "Result page". It is displayed on posts that have its code in the content.', 'thrive-quiz-builder' ) ?></h4>
	</div>
</div>
<?php if ( ! is_editor_page() ) : ?>
	<?php include tqb()->plugin_path( 'tcb-bridge/editor/page/state-picker.php' ); ?>
<?php endif; ?>

<?php include tqb()->plugin_path( 'tcb-bridge/editor/page/footer.php' ); ?>
