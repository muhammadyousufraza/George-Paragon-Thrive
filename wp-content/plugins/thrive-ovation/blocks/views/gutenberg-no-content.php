<style>
    .tvo-new-block-container {
        background-color: #f1f1f1;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 15px;
        font-family: Roboto, sans-serif;
        font-size: 16px !important;
        color: #565a5f !important;
        letter-spacing: -0.12px;
        border: 15px solid #fff;
    }

    .tvo-block-title h2 {
        font-size: 24px !important;
        color: #171b1b !important;
        opacity: 0.5;
        font-family: Roboto, sans-serif;
    }

    .tvo-new-block-description {
        color: #565a5f;
        font-family: Roboto, sans-serif;
        line-height: 1.5;
    }

    .tcb-icon {
        opacity: 0.1;
        width: 80px !important;
        height: 80px !important;
    }

    .mb-10 {
        margin-bottom: 10px;
    }

    body {
        border: 1px solid #ebebeb;
    }
</style>
<div class="tvo-new-block-container">
	<div class="mb-10">
		<?php tcb_icon( $element->icon(), false, 'editor' ); ?>
	</div>
	<div class="tvo-block-title mb-10">
		<h2 class="mb-10">
			<?php echo $element->name(); ?>
		</h2>
	</div>
	<div class="tvo-new-block-description">
		<?php echo __( 'Currently this block has no content.', 'thrive-ovation' ); ?>
	</div>
	<div class="tvo-new-block-description mb-10">
		<?php echo __( 'It will update once your block has been saved in Architect.', 'thrive-ovation' ); ?>
	</div>
</div>
