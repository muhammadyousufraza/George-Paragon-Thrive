<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

$variation         = tqb_get_variation( ! empty( $_REQUEST[ Thrive_Quiz_Builder::VARIATION_QUERY_KEY_NAME ] ) ? absint( $_REQUEST[ Thrive_Quiz_Builder::VARIATION_QUERY_KEY_NAME ] ) : 0 );
$variation_manager = new TQB_Variation_Manager( $variation['quiz_id'], $variation['page_id'] );
$child_variations  = $variation_manager->get_page_variations( array( 'parent_id' => $variation['id'] ) );
?>

<span class="tcb-modal-title ml-0 mt-0"><?php echo esc_html__( 'Import content', 'thrive-quiz-builder' ); ?></span>
<div class="margin-top-20">
	<?php echo esc_html__( 'Select the state you want to bring content from:', 'thrive-quiz-builder' ) ?>
</div>
<div class="mt-20">
	<select id="tqb-import-from">
		<option value=""><?php echo esc_html__( 'Select an interval', 'thrive-quiz-builder' ) ?></option>
		<?php
		foreach ( $child_variations as $child ) :
			?>
			<option value="<?php echo esc_attr( $child['id'] ); ?>"><?php echo esc_html( $child['post_title'] ); ?></option>
			<?php
		endforeach;
		?>
	</select>
</div>
<div class="tcb-modal-footer flex-end pr-0">
	<button type="button" class="tcb-right tve-button medium white-text green click" data-fn="import_content">
		<?php echo esc_html__( 'Import', 'thrive-quiz-builder' ) ?>
	</button>
</div>
