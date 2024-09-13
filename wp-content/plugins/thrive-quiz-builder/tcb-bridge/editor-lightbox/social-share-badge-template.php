<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
$style_templates = tqb()->get_tcb_social_share_badge_templates();
?>

<span class="tcb-modal-title ml-0 mt-0"><?php echo esc_html__( 'Social share badge template', 'thrive-quiz-builder' ); ?></span>
<div class="tcb-modal-description mb-10">
	<?php echo esc_html__( 'Select the display template for the social share badge.', 'thrive-quiz-builder' ) ?>
</div>
<div class="tve-templates-wrapper">
	<div class="tve-default-templates-list expanded-set">
		<?php foreach ( $style_templates as $key => $template ) : ?>
			<div class="tve-template-item">
				<div class="template-wrapper click" data-fn="select_template" data-key="<?php echo esc_attr( $template['file'] ); ?>">
					<div class="template-thumbnail" style="background-image: url('<?php echo $template['image']; ?>')"></div>
					<div class="template-name">
						<?php echo esc_html( $template['name'] ); ?>
					</div>
					<div class="selected"></div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</div>
<div class="tcb-modal-footer flex-end pr-0">
	<button type="button" class="tcb-right tve-button medium white-text green click" data-fn="choose_template">
		<?php echo esc_html__( 'Choose Template', 'thrive-quiz-builder' ) ?>
	</button>
</div>
