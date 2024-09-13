<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

?>

<?php include_once( TVA_Const::plugin_path( 'templates/header.php' ) ) ?>

<div class="tva-cm-redesigned-breadcrumbs">
	<?php tva_custom_breadcrumbs(); ?>
</div>
<div class="tva-frontend-template tva-list-of-courses" id="tva-courses-overview">
	<section class="tva-page-content">
		<?php include_once( TVA_Const::plugin_path( 'templates/filters.php' ) ) ?>
		<?php include_once( TVA_Const::plugin_path( 'templates/search.php' ) ) ?>
		<div class="tva-courses-container">

			<?php if ( ! post_password_required( $post ) ) : ?>
				<?php include_once( dirname( __FILE__ ) . '/course-list.php' ) ?>
			<?php else : ?>
				<?php echo the_title( '<h1>', '</h1>' ) ?>
				<?php echo get_the_password_form(); ?>
			<?php endif; ?>
		</div>
	</section>
</div>

<?php echo tva_add_apprentice_label(); ?>

<?php include_once( TVA_Const::plugin_path( 'templates/footer.php' ) ) ?>
