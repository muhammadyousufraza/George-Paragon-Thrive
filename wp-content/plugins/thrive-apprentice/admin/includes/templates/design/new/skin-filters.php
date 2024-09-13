<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

//TODO once we have the final design rewrite this part to be more modular
?>
<div class="ttd-dropdown-w-count">
	<div class="ttd-dwc-active click" data-fn="toggleDropdown">
		<span class="templates-title"><#= this.model.get( 'title' ).replace(new RegExp('_', 'g'), ' ') #><?php echo ' ' . __( 'Templates', 'thrive-apprentice' ); ?></span>
		<span class="templates-counter"><#= this.model.get('counter') #></span>
		<?php Thrive_Views::svg_icon( 'angle-down_light' ); ?>
	</div>
	<div class="ttd-dwc-drop">
		<div class="ttd-dwc-elem type-button click <# if ( this.model.get( 'template' ) === 'core' ) { #>active<# } #>" data-fn="mainFilter" data-template="core">
			<h3><?php echo __( 'Core Templates', 'thrive-apprentice' ) ?></h3>
			<p><?php echo __( "Only show the most basic templates that control your website's look.", 'thrive-apprentice' ); ?></p>
		</div>
		<div class="ttd-dwc-elem type-button click <# if ( this.model.get( 'template' ) === 'homepage' ) { #>active<# } #>" data-fn="mainFilter" data-template="homepage">
			<h3><?php echo __( 'Homepage Templates', 'thrive-apprentice' ) ?></h3>
			<p><?php echo __( 'Show templates specifically for building your homepage.', 'thrive-apprentice' ); ?></p>
		</div>
		<div class="ttd-dwc-elem type-button click <# if ( this.model.get( 'template' ) === 'lesson' ) { #>active<# } #>" data-fn="mainFilter" data-template="lesson">
			<h3><?php echo __( 'Lesson Templates', 'thrive-apprentice' ) ?></h3>
			<p><?php echo __( 'Show templates specifically for building your lessons.', 'thrive-apprentice' ); ?></p>
		</div>
		<div class="ttd-dwc-elem type-button click <# if ( this.model.get( 'template' ) === 'module' ) { #>active<# } #>" data-fn="mainFilter" data-template="module">
			<h3><?php echo __( 'Module Templates', 'thrive-apprentice' ) ?></h3>
			<p><?php echo __( 'Show templates specifically for building your module.', 'thrive-apprentice' ); ?></p>
		</div>
		<div class="ttd-dwc-elem type-list">
			<h3><?php echo __( 'Start & Completion', 'thrive-apprentice' ) ?></h3>
			<p class="tad-w-sep"><?php echo __( 'Show templates specifically for starting & completing your courses.', 'thrive-apprentice' ); ?></p>
			<ul class="ttd-dwc-posts">
				<li class="click <# if ( this.model.get( 'template' ) === 'course' ) { #>active<# } #>" data-fn="mainFilter" data-template="course"><?php echo __( 'Course Overview', 'thrive-apprentice' ) ?></li>
				<li class="click <# if ( this.model.get( 'template' ) === 'course_completed' ) { #>active<# } #>" data-fn="mainFilter" data-template="course_completed"><?php echo __( 'Course Completion', 'thrive-apprentice' ) ?></li>
			</ul>
		</div>
		<div class="ttd-dwc-elem type-button click <# if ( this.model.get( 'template' ) === 'all' ) { #>active<# } #>" data-fn="mainFilter" data-template="all">
			<h3><?php echo __( 'All Templates', 'thrive-apprentice' ) ?></h3>
			<p><?php echo __( 'All available templates for the active theme.', 'thrive-apprentice' ); ?></p>
		</div>
	</div>
</div>
