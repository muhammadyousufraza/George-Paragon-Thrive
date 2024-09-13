<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}


return [
	'steps'    => [
		[
			'id'           => 'logo',
			'sidebarLabel' => __( 'Logo', 'thrive-apprentice' ),
			'section'      => 'branding',
			'hasTopMenu'   => false,
		],
		[
			'id'           => 'color',
			'sidebarLabel' => __( 'Brand colour', 'thrive-apprentice' ),
			'section'      => 'branding',
			'hasTopMenu'   => false,
		],
		[
			'id'                    => 'header',
			'title'                 => __( 'Header - Choose a template', 'thrive-apprentice' ),
			'sidebarLabel'          => __( 'Header', 'thrive-apprentice' ),
			'section'               => 'design',
			'hasTopMenu'            => true,
			'selector'              => [
				'label' => __( 'Select a header', 'thrive-apprentice' ),
			],
			'popupMessage'          => 'You can change the <strong>Header</strong> from the top dropdown or<br>by pressing the arrow keys &lt; &gt;<br>When you are done click the <strong>Choose and Continue</strong> button.',
			'completedPopupMessage' => 'You can change the <strong>Header</strong> from the dropdown',
			'hasTemplates'          => true,
		],
		[
			'id'                    => 'footer',
			'title'                 => __( 'Footer - Choose a template', 'thrive-apprentice' ),
			'sidebarLabel'          => __( 'Footer', 'thrive-apprentice' ),
			'section'               => 'design',
			'hasTopMenu'            => true,
			'selector'              => [
				'label' => __( 'Select a footer', 'thrive-apprentice' ),
			],
			'popupMessage'          => 'You can change the <strong>Footer</strong> from the top dropdown or<br>by pressing the arrow keys &lt; &gt;<br>When you are done click the <strong>Choose and Continue</strong> button.',
			'completedPopupMessage' => 'You can change the <strong>Footer</strong> from the dropdown',
			'hasTemplates'          => true,
		],
		[
			'id'                   => 'school',
			'title'                => __( 'Choose a school homepage', 'thrive-apprentice' ),
			'sidebarLabel'         => __( 'School homepage', 'thrive-apprentice' ),
			'section'              => 'design',
			'previewMode'          => 'iframe',
			'hasTopMenu'           => true,
			'hideTemplateSelector' => true,
			'selector'             => [
				'label' => __( 'Select a template', 'thrive-apprentice' ),
			],
			'narrowTemplate'       => true,
			'hasTemplates'         => true,
		],
		[
			'id'                    => 'course',
			'title'                 => __( 'Course overview - choose a template', 'thrive-apprentice' ),
			'sidebarLabel'          => __( 'Course overview', 'thrive-apprentice' ),
			'section'               => 'design',
			'hasTopMenu'            => true,
			'previewMode'           => 'iframe',
			'selector'              => [
				'label' => __( 'Select a template', 'thrive-apprentice' ),
			],
			'narrowTemplate'        => true,
			'popupMessage'          => 'You can change the <strong>course overview template</strong> from the top dropdown or<br>by pressing the arrow keys &lt; &gt;<br>When you are done click the <strong>Choose and Continue</strong> button.',
			'completedPopupMessage' => 'You can change the <strong>course overview template</strong> from the dropdown',
			'hasTemplates'          => true,
		],
		[
			'id'                   => 'menu',
			'title'                => __( 'Menu - Choose a menu', 'thrive-apprentice' ),
			'sidebarLabel'         => __( 'Menu', 'thrive-apprentice' ),
			'section'              => 'design',
			'hasTopMenu'           => true,
			'hideTemplateSelector' => true,
		],
		[
			'id'                    => 'module',
			'title'                 => __( 'Module overview - choose a template', 'thrive-apprentice' ),
			'sidebarLabel'          => __( 'Module overview', 'thrive-apprentice' ),
			'section'               => 'content',
			'hasTopMenu'            => true,
			'previewMode'           => 'iframe',
			'selector'              => [
				'label' => __( 'Select a template', 'thrive-apprentice' ),
			],
			'narrowTemplate'        => true,
			'popupMessage'          => 'You can change the <strong>module overview template</strong> from the top dropdown or<br>by pressing the arrow keys &lt; &gt;<br>When you are done click the <strong>Choose and Continue</strong> button.',
			'completedPopupMessage' => 'You can change the <strong>module overview template</strong> from the dropdown',
			'hasTemplates'          => true,
		],
		[
			'id'                    => 'sidebar',
			'title'                 => __( 'Sidebar - Choose a template', 'thrive-apprentice' ),
			'sidebarLabel'          => __( 'Navigation sidebar', 'thrive-apprentice' ),
			'section'               => 'content',
			'hasTopMenu'            => true,
			'selector'              => [
				'label' => __( 'Select a sidebar', 'thrive-apprentice' ),
			],
			'popupMessage'          => 'You can change the <strong>Sidebar</strong> from the top dropdown or<br>by pressing the arrow keys &lt; &gt;<br>When you are done click the <strong>Choose and Continue</strong> button.',
			'completedPopupMessage' => 'You can change the <strong>Sidebar</strong> from the dropdown',
			'hasTemplates'          => true,
			'narrowTemplate'        => true,
			'delayNextStep'         => true,
		],
		[
			'id'                    => 'lesson',
			'sidebarLabel'          => __( 'Text lesson', 'thrive-apprentice' ),
			'title'                 => __( 'Text lesson - choose a template', 'thrive-apprentice' ),
			'section'               => 'content',
			'hasTopMenu'            => true,
			'previewMode'           => 'iframe',
			'selector'              => [
				'label' => __( 'Select a template', 'thrive-apprentice' ),
			],
			'narrowTemplate'        => true,
			'popupMessage'          => 'You can change the <strong>lesson template</strong> from the top dropdown or<br>by pressing the arrow keys &lt; &gt;<br>When you are done click the <strong>Choose and Continue</strong> button.',
			'completedPopupMessage' => 'You can change the <strong>lesson template</strong> from the top dropdown',
			'hasTemplates'          => true,
		],
		[
			'id'                    => 'video_lesson',
			'sidebarLabel'          => __( 'Video lesson', 'thrive-apprentice' ),
			'title'                 => __( 'Video lesson - choose a template', 'thrive-apprentice' ),
			'section'               => 'content',
			'hasTopMenu'            => true,
			'previewMode'           => 'iframe',
			'selector'              => [
				'label' => __( 'Select a template', 'thrive-apprentice' ),
			],
			'narrowTemplate'        => true,
			'popupMessage'          => 'You can change the <strong>lesson template</strong> from the top dropdown or<br>by pressing the arrow keys &lt; &gt;<br>When you are done click the <strong>Choose and Continue</strong> button.',
			'completedPopupMessage' => 'You can change the <strong>lesson template</strong> from the top dropdown',
			'hasTemplates'          => true,
		],
		[
			'id'                    => 'audio_lesson',
			'sidebarLabel'          => __( 'Audio lesson', 'thrive-apprentice' ),
			'title'                 => __( 'Audio lesson - choose a template', 'thrive-apprentice' ),
			'section'               => 'content',
			'hasTopMenu'            => true,
			'previewMode'           => 'iframe',
			'selector'              => [
				'label' => __( 'Select a template', 'thrive-apprentice' ),
			],
			'narrowTemplate'        => true,
			'popupMessage'          => 'You can change the <strong>lesson template</strong> from the top dropdown or<br>by pressing the arrow keys &lt; &gt;<br>When you are done click the <strong>Choose and Continue</strong> button.',
			'completedPopupMessage' => 'You can change the <strong>lesson template</strong> from the top dropdown',
			'hasTemplates'          => true,
		],
		[
			'id'                    => TVA_Assessment::WIZARD_ID,
			'title'                 => __( 'Assessment - choose a template', 'thrive-apprentice' ),
			'sidebarLabel'          => __( 'Assessment', 'thrive-apprentice' ),
			'section'               => 'content',
			'hasTopMenu'            => true,
			'previewMode'           => 'iframe',
			'selector'              => [
				'label' => __( 'Select a template', 'thrive-apprentice' ),
			],
			'narrowTemplate'        => true,
			'popupMessage'          => 'You can change the <strong>assessment template</strong> from the top dropdown or<br>by pressing the arrow keys &lt; &gt;<br>When you are done click the <strong>Choose and Continue</strong> button.',
			'completedPopupMessage' => 'You can change the <strong>assessment template</strong> from the dropdown',
			'hasTemplates'          => true,
		],
		[
			'id'                    => TVA_Course_Completed::WIZARD_ID,
			'title'                 => __( 'Course completion - choose a template', 'thrive-apprentice' ),
			'sidebarLabel'          => __( 'Course completion', 'thrive-apprentice' ),
			'section'               => 'content',
			'hasTopMenu'            => true,
			'previewMode'           => 'iframe',
			'selector'              => [
				'label' => __( 'Select a template', 'thrive-apprentice' ),
			],
			'narrowTemplate'        => true,
			'popupMessage'          => 'You can change the <strong>course completion template</strong> from the top dropdown or<br>by pressing the arrow keys &lt; &gt;<br>When you are done click the <strong>Choose and Continue</strong> button.',
			'completedPopupMessage' => 'You can change the <strong>course completion template</strong> from the dropdown',
			'hasTemplates'          => true,
		],
	],
	'sections' => [
		[
			'id'           => 'branding',
			'sidebarLabel' => __( 'School branding', 'thrive-apprentice' ),
		],
		[
			'id'           => 'design',
			'sidebarLabel' => __( 'School design', 'thrive-apprentice' ),
		],
		[
			'id'           => 'content',
			'sidebarLabel' => __( 'Course content', 'thrive-apprentice' ),
		],
	],
];
