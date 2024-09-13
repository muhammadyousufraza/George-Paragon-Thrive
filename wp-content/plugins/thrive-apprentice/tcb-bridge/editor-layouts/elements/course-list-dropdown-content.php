<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
if ( empty( $data ) || ! is_array( $data ) ) {
	return;
}

$display_topics                  = $data['has_topics'];
$display_topics_subheading       = $data['has_topics'] && ! empty( $data['topics-subheading'] );
$display_restrictions            = $data['has_restrictions'];
$display_restrictions_subheading = $data['has_restrictions'] && ! empty( $data['restrictions-subheading'] );

$display_progress            = is_user_logged_in() && $data['has_progress'];
$display_progress_subheading = $data['has_progress'] && ! empty( $data['progress-subheading'] );

?>
<input type="text" style="position: absolute; opacity: 0;" autocomplete="off" readonly/>
<a class="tve-lg-dropdown-trigger tcb-plain-text" tabindex="-1">
	<span class="tve-disabled-text-inner"><?php echo esc_attr( $data['placeholder'] ); ?></span>
	<span class="tve-item-dropdown-trigger">
		<svg xmlns="http://www.w3.org/2000/svg" viewBox="<?php echo $data['icon']['box']; ?>"><?php echo $data['icon']['up']; ?></svg>
	</span>
</a>
<ul class="tve-lg-dropdown-list tve-dynamic-dropdown-editable tve_no_icons" data-selector='[data-css="<?php echo $data['css']; ?>"] .tve-lg-dropdown-list'>
	<?php if ( $data['is_editor_page'] ) : ?>
		<?php $args = array(
			'title'     => '<strong>' . esc_attr( $data['progress-subheading'] ) . '</strong>',
			'type'      => 'progress',
			'css'       => $data['css'],
			'extra_cls' => 'tve-dynamic-dropdown-option-group tva-filter-progress-subheading',
			'hide'      => ! $display_progress_subheading,
		); ?>
		<?php include 'course-list-dropdown-item.php'; ?>
		<?php foreach ( $data['progress'] as $id => $title ) : ?>
			<?php $args = array(
				'id'        => $id,
				'title'     => $title,
				'type'      => 'progress',
				'value'     => 'progress_' . $id,
				'css'       => $data['css'],
				'extra_cls' => 'tve-dynamic-dropdown-editable',
				'hide'      => ! $display_progress,
			); ?>
			<?php include 'course-list-dropdown-item.php'; ?>
		<?php endforeach; ?>
		<?php $args = array(
			'title'     => '<strong>' . esc_attr( $data['topics-subheading'] ) . '</strong>',
			'type'      => 'topics',
			'css'       => $data['css'],
			'extra_cls' => 'tve-dynamic-dropdown-option-group tva-filter-topics-subheading',
			'hide'      => ! $display_topics_subheading,
		); ?>
		<?php include 'course-list-dropdown-item.php'; ?>
		<?php foreach ( $data['topics'] as $topic ) : ?>
			<?php $args = array(
				'id'        => $topic->ID,
				'title'     => $topic->title,
				'type'      => 'topics',
				'value'     => 'topics_' . $topic->ID,
				'css'       => $data['css'],
				'extra_cls' => 'tve-dynamic-dropdown-editable',
				'hide'      => ! $display_topics,
			); ?>
			<?php include 'course-list-dropdown-item.php'; ?>
		<?php endforeach; ?>
		<?php $args = array(
			'title'     => '<strong>' . esc_attr( $data['restrictions-subheading'] ) . '</strong>',
			'type'      => 'labels',
			'css'       => $data['css'],
			'extra_cls' => 'tve-dynamic-dropdown-option-group tva-filter-restrictions-subheading',
			'hide'      => ! $display_restrictions_subheading,
		); ?>
		<?php include 'course-list-dropdown-item.php'; ?>
		<?php foreach ( $data['restrictions'] as $restriction ) : ?>
			<?php $args = array(
				'id'        => $restriction['ID'],
				'title'     => $restriction['title'],
				'type'      => 'labels',
				'value'     => 'labels_' . $restriction['ID'],
				'css'       => $data['css'],
				'extra_cls' => 'tve-dynamic-dropdown-editable',
				'hide'      => ! $display_restrictions,
			); ?>
			<?php include 'course-list-dropdown-item.php'; ?>
		<?php endforeach; ?>
	<?php else: ?>
		<li class="tve-dynamic-dropdown-option tve_no_icons tve-state-active" data-selector='[data-css="<?php echo $data['css']; ?>"] .tve-dynamic-dropdown-option' data-value="">
			<div class="tve-input-option-text tcb-plain-text">
				<span contenteditable="false"><?php echo esc_attr( $data['placeholder'] ); ?></span>
			</div>
		</li>
		<?php if ( $display_progress ) : ?>
			<?php if ( $display_progress_subheading ) : ?>
				<?php $args = array(
					'title'     => '<strong>' . esc_attr( $data['progress-subheading'] ) . '</strong>',
					'type'      => 'progress',
					'css'       => $data['css'],
					'extra_cls' => 'tve-dynamic-dropdown-option-group tva-filter-progress-subheading',
				); ?>
				<?php include 'course-list-dropdown-item.php'; ?>
			<?php endif; ?>
			<li class="tve-dynamic-dropdown-option tve_no_icons" data-selector='[data-css="<?php echo $data['css']; ?>"] .tve-dynamic-dropdown-option' data-value="progress-all">
				<div class="tve-input-option-text tcb-plain-text">
					<span contenteditable="false"><?php echo __( 'All', 'thrive-apprentice' ); ?></span>
				</div>
			</li>
			<?php foreach ( $data['progress'] as $id => $title ) : ?>
				<?php
				if ( $id === TVA_Const::TVA_COURSE_PROGRESS_NO_ACCESS ) {
					continue;
				}

				$args = array(
					'id'        => $id,
					'title'     => $title,
					'type'      => 'progress',
					'value'     => 'progress_' . $id,
					'css'       => $data['css'],
					'extra_cls' => 'tve-dynamic-dropdown-editable',
				);
				?>
				<?php include 'course-list-dropdown-item.php'; ?>
			<?php endforeach; ?>
		<?php endif; ?>
		<?php if ( $display_topics ) : ?>
			<?php if ( $display_topics_subheading ) : ?>
				<?php $args = array(
					'title'     => '<strong>' . esc_attr( $data['topics-subheading'] ) . '</strong>',
					'type'      => 'topics',
					'css'       => $data['css'],
					'extra_cls' => 'tve-dynamic-dropdown-option-group tva-filter-topics-subheading',
				); ?>
				<?php include 'course-list-dropdown-item.php'; ?>
			<?php endif; ?>
			<?php foreach ( $data['topics'] as $topic ) : ?>
				<?php $args = array(
					'id'        => $topic->ID,
					'title'     => $topic->title,
					'type'      => 'topics',
					'value'     => 'topics_' . $topic->ID,
					'css'       => $data['css'],
					'extra_cls' => 'tve-dynamic-dropdown-editable',
				); ?>
				<?php include 'course-list-dropdown-item.php'; ?>
			<?php endforeach; ?>
		<?php endif; ?>
		<?php if ( $display_restrictions ) : ?>
			<?php if ( $display_restrictions_subheading ) : ?>
				<?php $args = array(
					'title'     => '<strong>' . esc_attr( $data['restrictions-subheading'] ) . '</strong>',
					'type'      => 'labels',
					'css'       => $data['css'],
					'extra_cls' => 'tve-dynamic-dropdown-option-group tva-filter-restrictions-subheading',
				); ?>
				<?php include 'course-list-dropdown-item.php'; ?>
			<?php endif; ?>
			<?php foreach ( $data['restrictions'] as $restriction ) : ?>
				<?php $args = array(
					'id'        => $restriction['ID'],
					'title'     => $restriction['title'],
					'type'      => 'labels',
					'value'     => 'labels_' . $restriction['ID'],
					'css'       => $data['css'],
					'extra_cls' => 'tve-dynamic-dropdown-editable',
				); ?>
				<?php include 'course-list-dropdown-item.php'; ?>
			<?php endforeach; ?>
		<?php endif; ?>
	<?php endif; ?>
</ul>
