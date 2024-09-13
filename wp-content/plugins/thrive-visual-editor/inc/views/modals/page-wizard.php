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
 * Allows the Thrive Theme or other plugins to hook here
 *
 * Returns the configuration array for page wizard
 */
$items = apply_filters( 'tcb_get_page_wizard_items', array(
	array(
		'title'   => __( 'Normal Page', 'thrive-cb' ),
		'layout'  => 'normal',
		'order'   => 0,
		'picture' => tve_editor_url( 'editor/css/images/page-wizard/normal-page.png' ),
		'text'    => array(
			__( 'Used for creating content pages that should look like other content on your site.', 'thrive-cb' ),
			__( 'These pages use theme templates and are useful for creating content rich company pages (about us, services, pricing etc.).', 'thrive-cb' ),
		),
	),
	array(
		'title'   => __( 'Pre-built Landing Page', 'thrive-cb' ),
		'layout'  => 'lp',
		'order'   => 100,
		'picture' => tve_editor_url( 'editor/css/images/page-wizard/pre-built-lp.png' ),
		'text'    => array(
			__( 'Choose from our library over 200 pre-built landing pages.', 'thrive-cb' ),
			__( 'This is mostly useful if you want to build a marketing page but don’t want the hassle of designing it yourself.', 'thrive-cb' ),
			__( 'Simply choose a design you like and modify to fit your needs.', 'thrive-cb' ),
		),
	),
) );

/**
 * Sort the items array based on order index
 */
usort( $items, function ( $a, $b ) {
	return $a['order'] - $b['order'];
} )
?>
<h2><?php echo esc_html__( 'What page would you like to create?', 'thrive-cb' ); ?></h2>
<div class="info-text red">
	<span>
		<?php echo esc_html__( 'Warning!', 'thrive-cb' ); ?>
	</span>
	<span>
		<?php echo esc_html__( 'If you change your page template, any custom content you added to the page will be deleted', 'thrive-cb' ); ?>
	</span>
</div>
<div class="parent">
	<?php foreach ( $items as $item ) : ?>
		<div class="click item" data-fn="chooseLayout" data-layout="<?php echo esc_attr( $item['layout'] ); ?>">
			<div>
				<img src="<?php echo esc_url( $item['picture'] ); ?>" alt="Item Picture"/>
			</div>
			<div>
				<span><?php echo esc_html( $item['title'] ); ?></span>
			</div>
			<hr class="mb-20">
			<div>
				<?php foreach ( $item['text'] as $text ) : ?>
					<p><?php echo esc_html( $text ); ?></p>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endforeach; ?>
</div>
