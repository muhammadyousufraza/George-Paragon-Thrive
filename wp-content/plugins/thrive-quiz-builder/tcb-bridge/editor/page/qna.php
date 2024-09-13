<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-quiz-builder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

if ( ! empty( $is_ajax_render ) ) {
	/**
	 * If AJAX-rendering the contents, we need to only output the html part,
	 * and do not include any of the custom css / fonts etc needed - used in the state manager
	 */
	return;
}

global $post;

$quiz_id = $post->ID;

$quiz_style         = TQB_Post_meta::get_quiz_style_meta( $quiz_id );
$data               = TQB_Quiz_Manager::get_shortcode_content( $quiz_id );
$question_manager   = new TGE_Question_Manager( $quiz_id );
$content            = tcb_post( $quiz_id )->tcb_content;
$progress_settings  = tqb_progress_settings_instance( (int) $quiz_id )->get();
$palettes           = new TQB_Quiz_Palettes( $quiz_style );
$colors             = $palettes->get_palettes_as_string();
$qna                = get_post_meta( $quiz_id, 'tve_qna_templates', true );
$has_qna_templates  = ! empty( $qna[ $quiz_style ] );
$quiz_type          = TQB_Post_meta::get_quiz_type_meta( $quiz_id, true );
$is_write_wrong     = 'right_wrong' === $quiz_type;
$feedback_settings  = TQB_Post_meta::get_feedback_settings_meta( $quiz_id );
$navigation_enabled = isset ( $feedback_settings['nav_buttons'] ) ? $feedback_settings['nav_buttons'] : false;
?>

	<!DOCTYPE html>
	<!--[if IE 7]>
<html class="ie ie7" <?php language_attributes(); ?>>
<![endif]-->
	<!--[if IE 8]>
<html class="ie ie8" <?php language_attributes(); ?>>
<![endif]-->
	<!--[if !(IE 7) | !(IE 8)  ]><!-->
<html class="no-touch" <?php language_attributes(); ?>>
	<!--<![endif]-->
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>"/>
		<meta name="robots" content="noindex, nofollow"/>
		<title>
			<?php /* Genesis wraps the meta title into another <title> tag using this hook: genesis_doctitle_wrap. the following line makes sure this isn't called */ ?>
			<?php /* What if they change the priority at which this hook is registered ? :D */ ?>
			<?php remove_filter( 'wp_title', 'genesis_doctitle_wrap', 20 ); ?>
			<?php wp_title( '' ); ?>
		</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
		<?php foreach ( $question_manager->get_question_css() as $css_file ) : ?>
			<link rel="stylesheet" type="text/css" href="<?php echo esc_url( $css_file ); ?>" media="all">
		<?php endforeach; ?>
		<?php wp_head(); ?>
	</head>
<body <?php body_class(); ?>>
<div id="tve_editor"
	 class="no-touch tqb-shortcode-new-content tqb-template-style-<?php echo esc_attr( $quiz_style ); ?>">
	<?php if ( $has_qna_templates ): ?>
		<?php echo $content; // phpcs:ignore ?>
	<?php else: ?>
		<?php
		ob_start();
		include tqb()->plugin_path( 'tcb-bridge/editor-layouts/elements/question.php' );
		$content = ob_get_contents();
		ob_end_clean();

		echo $content; // phpcs:ignore
		?>
	<?php endif; ?>
</div>
<?php
include tqb()->plugin_path( 'tcb-bridge/editor/page/footer.php' );
tqb_add_frontend_svg_file();
?>
<style>

	.tqb-progress-label {
		color: <?php echo esc_attr($progress_settings['label_color']);?>;
	}

	.tqb-progress-completed {
		width: calc(33%) !important;
		background-color: <?php echo esc_attr($progress_settings['fill_color']);?>;
	}

	.tqb-next-item {
		width: calc(33%) !important;
		background-color: <?php echo esc_attr($progress_settings['next_step_color']);?>;
	}

	.tqb-remaining-progress {
		width: calc(35%) !important;
		background-color: <?php echo esc_attr($progress_settings['background_color']);?>;
	}

	.tqb-progress-label, .tqb-next-item, .tqb-remaining-progress {
		font-size: <?php echo esc_attr($progress_settings['font_size']);?>px;
	}

</style>
<?php
