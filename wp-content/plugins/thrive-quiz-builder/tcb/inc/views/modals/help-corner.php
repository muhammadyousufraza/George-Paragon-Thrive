<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Help cards content
 */
$items = array(
	array(
		'title'       => __( 'Knowledge Base', 'thrive-cb' ),
		'picture'     => tve_editor_url( 'editor/css/images/help-corner/knowledge-base.svg' ),
		'picture-alt' => __( 'Knowledge Base Item Picture', 'thrive-cb' ),
		'text'        => array(
			__( 'Search our extensive knowledge base for “how-to” articles and instructions.', 'thrive-cb' ),
		),
		'class'       => 'knowledge-base',

	),
	array(
		'title'       => __( 'Thrive University', 'thrive-cb' ),
		'picture'     => tve_editor_url( 'editor/css/images/help-corner/thrive-university.svg' ),
		'picture-alt' => __( 'Thrive University Item Picture', 'thrive-cb' ),
		'text'        => array(
			__( 'Take one of our online courses on website building and online marketing.', 'thrive-cb' ),
		),
		'class'       => 'thrive-university',
	),
	array(
		'title'       => __( 'Get Support', 'thrive-cb' ),
		'picture'     => tve_editor_url( 'editor/css/images/help-corner/support.svg' ),
		'picture-alt' => __( 'Support Item Picture', 'thrive-cb' ),
		'text'        => array(
			__( 'Contact our friendly support team who will help you with any issues or questions.', 'thrive-cb' ),
		),
		'class'       => 'support',
	),
);
?>

<h2><?php echo esc_html__( 'Help Corner', 'thrive-cb' ); ?></h2>
<div class="parent">
	<?php foreach ( $items as $item ) : ?>
		<div class="<?php echo esc_attr( $item['class'] ); ?> click item" data-fn="chooseLink">
			<img src="<?php echo esc_url( $item['picture'] ); ?>" alt="<?php echo esc_attr( $item['picture-alt'] ); ?>"/>
			<div class="item-title">
				<span><?php echo esc_html( $item['title'] ); ?></span>
			</div>
			<div class="item-text">
				<?php foreach ( $item['text'] as $text ) : ?>
					<p><?php echo esc_html( $text ); ?></p>
				<?php endforeach; ?>
			</div>
			<?php if ( $item['title'] == 'Knowledge Base' ): ?>
				<div class="kb-search">
					<input type="text" class="kb-input-search keyup-enter" data-fn="searchKB" placeholder="Search knowledge base" autocomplete="off">
					<?php tcb_icon( 'search-regular', false, 'sidebar', 'click', [ 'data-fn' => 'searchKB' ] ) ?>
				</div>
			<?php endif ?>
		</div>
	<?php endforeach; ?>
</div>
