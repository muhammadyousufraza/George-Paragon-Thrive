<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

$topics            = tva_get_topics( array( 'by_courses' => true ) );
$filters           = tva_get_frontend_filters();
$template_settings = tva_get_setting( 'template' );
?>

<div class="tva-filters-wrapper">
	<div class="tva-filters-container">
		<?php if ( count( $topics ) > 1 ) : ?>
			<?php foreach ( $topics as $topic ) : ?>
				<div class="tva-filter-checkbox-container">
					<div class="tva-filter-checkbox-color-<?php echo $topic['ID']; ?> <?php echo in_array( $topic['ID'], $filters ) ? 'tva-filter-checkbox-selected' : ''; ?>"></div>
					<div class="tva-checkbox-holder">
						<?php if ( isset( $topic['icon_type'] ) && ( 'svg_icon' === $topic['icon_type'] ) && isset( $topic['svg_icon'] ) ) : ?>
							<div class="tva-svg-front" id="tva-topic-<?php echo $topic['ID']; ?>">
								<?php echo $topic['svg_icon']; ?>
							</div>
						<?php else : ?>
							<?php $img_url = $topic['icon'] ? $topic['icon'] : TVA_Const::get_default_course_icon_url(); ?>
							<img class="tva-filter-icon" src="<?php echo $img_url; ?>"/>
						<?php endif; ?>
						<input type="checkbox" class="tva-filter tva-filter-course" name="tva-filter-course" <?php echo ( in_array( $topic['ID'], $arguments['topics'] ) || in_array( $topic['ID'], $filters ) ) ? 'checked="checked"' : ''; ?> id="tva-filter-course-<?php echo $topic['title']; ?>" value="<?php echo $topic['ID']; ?>">
						<label for="tva-filter-course-<?php echo $topic['title']; ?>">
							<?php echo $topic['title']; ?>
						</label>
					</div>
				</div>
			<?php endforeach; ?>

			<div class="tva-filter-checkbox-container tva-clear-filters">
				<span class="close-icon"></span>
				<input type="hidden" class="tva-filter tva-filter-course-clear-filters" id="tva-filter-course-clear-filters" value="">
				<label class="tva-filters-label" for="tva-filter-course-clear-filters">
					<?php echo __( 'Clear Filters', 'thrive-apprentice' ); ?>
				</label>
			</div>
		<?php endif; ?>
	</div>

	<div class="tva-search-wrapper">
		<input id="tva_front_search" class="tva-search-input tva_search_text" type="text" onfocus="this.placeholder = ''" onblur="this.placeholder = '<?php echo esc_attr( $template_settings['search_text'] ); ?>'" placeholder="<?php echo esc_attr( $template_settings['search_text'] ); ?>">
		<a class="tva-search-submit" href="javascript:void(0)"></a>
	</div>
</div>
