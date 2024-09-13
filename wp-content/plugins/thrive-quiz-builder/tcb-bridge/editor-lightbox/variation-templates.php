<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 10/4/2016
 * Time: 11:48 AM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

global $variation;
if ( empty( $variation ) ) {
	$variation = tqb_get_variation( ! empty( $_REQUEST[ Thrive_Quiz_Builder::VARIATION_QUERY_KEY_NAME ] ) ? absint( $_REQUEST[ Thrive_Quiz_Builder::VARIATION_QUERY_KEY_NAME ] ) : 0 );
}

$page_type_name   = tqb()->get_style_page_name( $variation['post_type'] );
$current_template = ! empty( $variation[ Thrive_Quiz_Builder::FIELD_TEMPLATE ] ) ? $variation[ Thrive_Quiz_Builder::FIELD_TEMPLATE ] : '';
$templates        = TQB_Template_Manager::get_templates( $variation['post_type'], $variation['quiz_id'] );
?>

<div class="modal-sidebar">
	<div class="lp-search">
		<?php tcb_icon( 'search-regular' ); ?>
		<input class="tve-c-modal-search-input input" type="text" data-source="search" data-fn="onSearch"
			   placeholder="<?php echo esc_html__( 'Search', 'thrive-quiz-builder' ); ?>"/>
		<?php tcb_icon( 'close2', false, 'sidebar', 'click', array( 'data-fn' => 'clearSearch' ) ); ?>
	</div>

	<div class="lp-menu-wrapper">
		<div class="mt-30">
			<div class="sidebar-title">
				<p><?php echo esc_html__( 'Type', 'thrive-quiz-builder' ); ?></p>
				<span class="tcb-hl"></span>
			</div>
			<div id="tqb-default-filters">
				<a href="javascript:void(0);" class="click tqb-category-filter active" data-content="default" data-fn="filterClick">
					<span class="tqb-filter-label"><?php echo esc_html__( 'Default templates', 'thrive-quiz-builder' ); ?></span>
					<span class="tqb-filter-counter"></span>
				</a>
			</div>
		</div>
		<div class="mt-30">
			<div class="sidebar-title">
				<p><?php echo esc_html__( 'My Templates', 'thrive-quiz-builder' ); ?></p>
				<span class="tcb-hl"></span>
			</div>
			<div id="tqb-saved-filters">
				<a href="javascript:void(0);" class="click tqb-category-filter" data-content="saved" data-fn="filterClick">
					<span class="tqb-filter-label"><?php echo esc_html__( 'Saved templates', 'thrive-quiz-builder' ); ?></span>
					<span class="tqb-filter-counter"></span>
				</a>
			</div>
		</div>
	</div>
</div>
<div class="modal-content">
	<span class="tcb-modal-title ml-30"><?php echo sprintf( esc_html__( 'Choose %s Template', 'thrive-quiz-builder' ), $page_type_name ); ?></span>
	<div class="warning-ct-change ml-30 mr-30">
		<div class="tcb-notification info-text">
			<div class="tcb-notification-content">
				<?php echo esc_html__( 'Any changes you’ve made to the current form will be lost when you select a new template. We recommend you to save your current template first.', 'thrive-quiz-builder' ) ?>
			</div>
		</div>
	</div>
	<div class="tqb-templates-wrapper ml-20">
		<div class="tqb-templates tqb-default-templates">
			<?php foreach ( $templates as $data ) : ?>
				<div class="cloud-template-item modal-item click<?php echo $current_template == $data['key'] ? ' active' : '' ?>" data-fn="selectTemplate" data-key="<?php echo esc_attr( $data['key'] ); ?>">
					<div class="modal-title-w-options">
						<div class="cb-template-name-wrapper">
							<div class="cb-template-name"><?php echo esc_html( $data['name'] ); ?></div>
						</div>
						<?php tcb_icon( 'check-light' ); ?>
					</div>
					<div class="cloud-item click">
						<div class="cb-template-wrapper">
							<img class="cb-template-thumbnail" src="<?php echo esc_attr( $data['thumbnail'] ); ?>">
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
		<div class="tqb-templates tqb-saved-templates" style="display: none">

		</div>
	</div>
</div>

<div class="tcb-modal-footer flex-end">
	<button type="button" class="tcb-right tve-button medium green tcb-modal-save click" data-fn="applyTemplate">
		<?php echo esc_html__( 'Choose Template', 'thrive-leads' ) ?>
	</button>
</div>

