<?php
/**
 * Thrive Themes - https=>//thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

return [
	'steps'    => [
		[
			'id'           => 'logo',
			'sidebarLabel' => __( 'Logo', 'thrive-theme' ),
			'section'      => 'branding',
			'hasTopMenu'   => false,
		],
		[
			'id'           => 'color',
			'sidebarLabel' => __( 'Brand Colour', 'thrive-theme' ),
			'section'      => 'branding',
			'hasTopMenu'   => false,
		],
		[
			'id'                    => 'header',
			'title'                 => __( 'Header', 'thrive-theme' ),
			'subtitle'              => __( '- Choose a template', 'thrive-theme' ),
			'sidebarLabel'          => __( 'Header', 'thrive-theme' ),
			'section'               => 'site',
			'hasTopMenu'            => true,
			'selector'              => [
				'label' => __( 'Select a Header', 'thrive-theme' ),
			],
			'popupMessage'          => 'You can change the <strong>Header</strong> from the top dropdown or<br>by pressing the arrow keys &lt; &gt;<br>When you are done click the <strong>Choose and Continue</strong> button.',
			'completedPopupMessage' => 'You can change the <strong>Header</strong> from the dropdown',
		],
		[
			'id'                    => 'footer',
			'title'                 => __( 'Footer', 'thrive-theme' ),
			'subtitle'              => __( '- Choose a template', 'thrive-theme' ),
			'sidebarLabel'          => __( 'Footer', 'thrive-theme' ),
			'section'               => 'site',
			'hasTopMenu'            => true,
			'selector'              => [
				'label' => __( 'Select a Footer', 'thrive-theme' ),
			],
			'popupMessage'          => 'You can change the <strong>Footer</strong> from the top dropdown or<br>by pressing the arrow keys &lt; &gt;<br>When you are done click the <strong>Choose and Continue</strong> button.',
			'completedPopupMessage' => 'You can change the <strong>Footer</strong> from the dropdown',
		],
		[
			'id'                   => 'homepage',
			'title'                => __( 'Homepage', 'thrive-theme' ),
			'sidebarLabel'         => __( 'Homepage', 'thrive-theme' ),
			'section'              => 'site',
			'previewMode'          => 'iframe',
			'hasTopMenu'           => true,
			'hideTemplateSelector' => true,
			'selector'             => [
				'label' => __( 'Select a Page', 'thrive-theme' ),
			],
			'narrowTemplate'       => true,
		],
		[
			'id'                    => 'post',
			'title'                 => __( 'Single Blog Post', 'thrive-theme' ),
			'subtitle'              => __( '- Choose a template', 'thrive-theme' ),
			'sidebarLabel'          => __( 'Single Blog Post', 'thrive-theme' ),
			'section'               => 'site',
			'hasTopMenu'            => true,
			'previewMode'           => 'iframe',
			'selector'              => [
				'label' => __( 'Select a Template', 'thrive-theme' ),
			],
			'narrowTemplate'        => true,
			'popupMessage'          => 'You can change the <strong>Blog Post Template</strong> from the top dropdown or<br>by pressing the arrow keys &lt; &gt;<br>When you are done click the <strong>Choose and Continue</strong> button.',
			'completedPopupMessage' => 'You can change the <strong>Blog Post Template</strong> from the dropdown',
		],
		[
			'id'                    => 'blog',
			'title'                 => __( 'Blog Post List', 'thrive-theme' ),
			'subtitle'              => __( '- Choose a template', 'thrive-theme' ),
			'sidebarLabel'          => __( 'Blog Post List', 'thrive-theme' ),
			'section'               => 'site',
			'hasTopMenu'            => true,
			'previewMode'           => 'iframe',
			'selector'              => [
				'label' => __( 'Select a Template', 'thrive-theme' ),
			],
			'narrowTemplate'        => true,
			'popupMessage'          => 'You can change the <strong>Blog Post List Template</strong> from the top dropdown or<br>by pressing the arrow keys &lt; &gt;<br>When you are done click the <strong>Choose and Continue</strong> button.',
			'completedPopupMessage' => 'You can change the <strong>Blog Post List Template</strong> from the dropdown',
		],
		[
			'id'                    => 'page',
			'sidebarLabel'          => __( 'Page', 'thrive-theme' ),
			'title'                 => __( 'Page', 'thrive-theme' ),
			'subtitle'              => __( '- Choose a template', 'thrive-theme' ),
			'section'               => 'site',
			'hasTopMenu'            => true,
			'previewMode'           => 'iframe',
			'selector'              => [
				'label' => __( 'Select a Template', 'thrive-theme' ),
			],
			'narrowTemplate'        => true,
			'popupMessage'          => 'You can change the <strong>Page Template</strong> from the top dropdown or<br>by pressing the arrow keys &lt; &gt;<br>When you are done click the <strong>Choose and Continue</strong> button.',
			'completedPopupMessage' => 'You can change the <strong>Page Template</strong> from the top dropdown',
		],
		[
			'id'                   => 'menu',
			'title'                => __( 'Menu', 'thrive-theme' ),
			'subtitle'             => __( '- Choose a menu', 'thrive-theme' ),
			'sidebarLabel'         => __( 'Menu', 'thrive-theme' ),
			'section'              => 'site',
			'hasTopMenu'           => true,
			'hideTemplateSelector' => true,
		],
	],
	'sections' => [
		[
			'id'           => 'branding',
			'sidebarLabel' => __( 'Site Branding', 'thrive-theme' ),
		],
		[
			'id'           => 'site',
			'sidebarLabel' => __( 'Site Structure', 'thrive-theme' ),
		],
	],
];
