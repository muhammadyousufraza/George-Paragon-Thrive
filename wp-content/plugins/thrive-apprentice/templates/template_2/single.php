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

<div class="wrp cnt uni clearfix">
	<div class="bSeCont tuni-page-container ">
		<section class="bSe tuni-page-content">
			<?php if ( have_posts() ) : ?>
				<?php while ( have_posts() ) : the_post(); ?>
					<?php echo the_title(); ?>
					<?php the_content(); ?>
				<?php endwhile; ?>
			<?php endif; ?>
		</section>
	</div>
</div>

<?php include_once( TVA_Const::plugin_path( 'templates/footer.php' ) ) ?>


