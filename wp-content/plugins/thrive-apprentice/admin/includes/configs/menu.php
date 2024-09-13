<?php

use TVA\TTB\Main;

$index_page            = tva_get_settings_manager()->factory( 'index_page' )->get_value();
$visual_editor_welcome = tva_get_settings_manager()->factory( 'visual_editor_welcome' )->get_value();

$tva_menu = [
	'home'            => [
		'slug'  => 'home',
		'route' => '#home',
		'icon'  => 'app-logo',
		'label' => esc_html__( 'Home', 'thrive-apprentice' ),
	],
	'wizard'          => [
		'slug'   => 'wizard',
		'route'  => '#wizard',
		'hidden' => true,
		'icon'   => 'icon-wizard',
		'label'  => esc_html__( 'Wizard', 'thrive-apprentice' ),
	],
	'courses'         => [
		'slug'     => 'courses',
		'route'    => '#courses',
		'icon'     => 'icon-courses',
		'label'    => esc_html__( 'Courses', 'thrive-apprentice' ),
		'sections' => [
			[
				'simple' => true,
				'slug'   => 'settings',
				'label'  => esc_html__( 'Settings', 'thrive-apprentice' ),
			],
		],
		'items'    => [
			'courses'       => [
				'slug'  => 'courses',
				'route' => '#courses',
				'icon'  => 'all-courses-icon',
				'label' => esc_html__( 'Courses', 'thrive-apprentice' ),
			],
			'archived'      => [
				'slug'  => 'archives',
				'route' => '#courses/archives',
				'icon'  => 'all-archived-courses',
				'label' => esc_html__( 'Archived', 'thrive-apprentice' ),
			],
			'course-topics' => [
				'slug'    => 'topics',
				'section' => 'settings',
				'route'   => '#courses/topics',
				'icon'    => 'course-topics-icon',
				'label'   => esc_html__( 'Course topics', 'thrive-apprentice' ),
			],
		],
	],
	'members'         => [
		'slug'  => 'members',
		'route' => '#members',
		'icon'  => 'icon-customers',
		'label' => esc_html__( 'Members', 'thrive-apprentice' ),
		'items' => [
			'students' => [
				'hidden' => true,
				'slug'   => 'members',
				'route'  => '#members',
				'icon'   => '',
				'label'  => esc_html__( 'Members', 'thrive-apprentice' ),
			],
		],
	],
	'assessments'     => [
		'slug'   => 'assessments',
		'route'  => '#assessments',
		'icon'   => 'icon-assessments',
		'hidden' => ! Main::uses_builder_templates(),
		'label'  => esc_html__( 'Assessments', 'thrive-apprentice' ),
	],
	'reports'         => [
		'slug'     => 'reports',
		'route'    => '#reports',
		'icon'     => 'icon-reporting',
		'label'    => esc_html__( 'Reports', 'thrive-apprentice' ),
		'sections' => [
			[
				'simple' => true,
				'slug'   => 'quiz',
				'label'  => esc_html__( 'Coming soon', 'thrive-apprentice' ),
			],
		],
		'items'    => [
			'reports' => [
				'slug'  => 'reports',
				'route' => '#reports',
				'icon'  => 'course-topics-icon',
				'label' => esc_html__( 'Apprentice snapshot', 'thrive-apprentice' ),
			],
			'courses' => [
				'slug'  => 'courses',
				'route' => '#reports/courses',
				'icon'  => 'all-courses-icon',
				'label' => esc_html__( 'Course dashboard', 'thrive-apprentice' ),
				'items' => [
					'enrollments' => [
						'slug'  => 'enrollments',
						'route' => '#reports/courses/enrollments',
						'label' => esc_html__( 'Course enrollments', 'thrive-apprentice' ),
					],
					'completions' => [
						'slug'  => 'completions',
						'route' => '#reports/courses/completions',
						'label' => esc_html__( 'Course completions', 'thrive-apprentice' ),
					],
					'lessons'     => [
						'slug'  => 'lessons',
						'route' => '#reports/courses/lessons',
						'label' => esc_html__( 'Lesson completions', 'thrive-apprentice' ),
					],
					'progress'    => [
						'slug'  => 'progress',
						'route' => '#reports/courses/progress',
						'label' => esc_html__( 'Progress and drop-off rate', 'thrive-apprentice' ),
					],
					'engagements' => [
						'slug'  => 'engagements',
						'route' => '#reports/courses/engagements',
						'label' => esc_html__( 'Engagements', 'thrive-apprentice' ),
					],
					'members'     => [
						'slug'  => 'members',
						'route' => '#reports/courses/members',
						'label' => esc_html__( 'New members', 'thrive-apprentice' ),
					],
					'popular'     => [
						'slug'  => 'popular',
						'route' => '#reports/courses/popular',
						'label' => esc_html__( 'Popular courses', 'thrive-apprentice' ),
					],
					'top-members' => [
						'slug'  => 'top-members',
						'route' => '#reports/courses/top-members',
						'label' => esc_html__( 'Top members', 'thrive-apprentice' ),
					],
					'activity'    => [
						'slug'  => 'activity',
						'route' => '#reports/courses/activity',
						'label' => esc_html__( 'Latest activity', 'thrive-apprentice' ),
					],
				],
			],
			'quiz'    => [
				'slug'    => 'quiz-reports',
				'route'   => '#reports/quizes',
				'section' => 'quiz',
				'icon'    => 'all-courses-icon',
				'label'   => esc_html__( 'Quiz reports', 'thrive-apprentice' ),
			],
		],
	],
	'design'          => [
		'slug'          => 'design',
		'route'         => empty( $visual_editor_welcome ) ? '#design-welcome' : '#design',
		'icon'          => 'icon-design',
		'label'         => esc_html__( 'Design', 'thrive-apprentice' ),
		'dynamic_items' => 1,
	],
	'products'        => [
		'slug'  => 'products',
		'route' => '#products',
		'icon'  => 'icon-products',
		'label' => esc_html__( 'Products', 'thrive-apprentice' ),
		'items' => [
			'products' => [
				'hidden' => true,
				'slug'   => 'products',
				'route'  => '#products',
				'icon'   => '',
				'label'  => esc_html__( 'Products', 'thrive-apprentice' ),
			],
		],
	],
	'protected-files' => [
		'slug'  => 'protected-files',
		'route' => '#protected-files',
		'icon'  => 'icon-protected-files',
		'label' => esc_html__( 'Protected files', 'thrive-apprentice' ),
		'items' => [
			'files' => [
				'hidden' => true,
				'slug'   => 'protected-files',
				'route'  => '#protected-files',
				'icon'   => '',
				'label'  => esc_html__( 'Protected files', 'thrive-apprentice' ),
			],
		],
	],
	'settings'        => [
		'slug'  => 'settings',
		'route' => '#settings',
		'icon'  => 'icon-settings',
		'label' => esc_html__( 'Settings', 'thrive-apprentice' ),
		'items' => [
			'settings'            => [
				'slug'  => 'settings',
				'route' => '#settings',
				'icon'  => 'settings-icon',
				'label' => esc_html__( 'General settings', 'thrive-apprentice' ),
			],
			'sendowl'             => [
				'slug'     => 'sendowl',
				'route'    => '#settings/sendowl',
				'disabled' => TVA_SendOwl::is_connected() ? 0 : esc_attr__( 'You need to have an active SendOwl API Connection to use this menu.', 'thrive-apprentice' ),
				'icon'     => 'sendowl-logo',
				'label'    => esc_html__( 'SendOwl', 'thrive-apprentice' ),
			],
			'email-templates'     => [
				'slug'  => 'email-templates',
				'route' => '#settings/email-templates',
				'icon'  => 'email-templates-icon',
				'label' => esc_html__( 'Email templates', 'thrive-apprentice' ),
			],
			'assessment-settings' => [
				'slug'  => 'assessment-settings',
				'route' => '#settings/assessment-settings',
				'icon'  => 'assessment-settings-icon',
				'label' => esc_html__( 'Assessment uploads', 'thrive-apprentice' ),
			],
			'labels'              => [
				'slug'  => 'translations',
				'route' => '#settings/translations/access-restrictions',
				'icon'  => 'labels-translations-icon',
				'label' => esc_html__( 'Labels & translations', 'thrive-apprentice' ),
				'items' => [
					'access-restrictions'    => [
						'slug'  => 'access-restrictions',
						'route' => '#settings/translations/access-restrictions',
						'label' => esc_html__( 'Access restrictions', 'thrive-apprentice' ),
					],
					'call-to-action-buttons' => [
						'slug'  => 'call-to-action-buttons',
						'route' => '#settings/translations/call-to-action-buttons',
						'label' => esc_html__( 'Call to action buttons', 'thrive-apprentice' ),
					],
					'course-content-types'   => [
						'slug'  => 'course-content-types',
						'route' => '#settings/translations/course-content-types',
						'label' => esc_html__( 'Course content types', 'thrive-apprentice' ),
					],
					'course-navigation'      => [
						'slug'  => 'course-navigation',
						'route' => '#settings/translations/course-navigation',
						'label' => esc_html__( 'Course navigation', 'thrive-apprentice' ),
					],
					'course-structure'       => [
						'slug'  => 'course-structure',
						'route' => '#settings/translations/course-structure',
						'label' => esc_html__( 'Course structure', 'thrive-apprentice' ),
					],
					'course-progress'        => [
						'slug'  => 'course-progress',
						'route' => '#settings/translations/course-progress',
						'label' => esc_html__( 'Course progress', 'thrive-apprentice' ),
					],
				],
			],
			'access-restriction'  => [
				'slug'  => 'access-restriction',
				'route' => '#settings/access-restriction',
				'icon'  => 'login-icon',
				'label' => esc_html__( 'Login & access restriction', 'thrive-apprentice' ),
			],
			'stripe'              => [
				'slug'  => 'stripe',
				'route' => '#settings/stripe',
				'icon'  => 'dollar-sign',
				'label' => esc_html__( 'Stripe', 'thrive-apprentice' ),
			],
			'logs'                => [
				'slug'  => 'logs',
				'route' => '#settings/logs',
				'icon'  => 'logs-icon',
				'label' => esc_html__( 'Logs', 'thrive-apprentice' ),
			],
			'api-keys'            => [
				'slug'  => 'api-keys',
				'route' => '#settings/api-keys',
				'icon'  => 'api-key-icon',
				'label' => esc_html__( 'Api keys', 'thrive-apprentice' ),
			],
		],
	],
];

/** Check if user has access to generation */
$tva_generate = get_user_meta( get_current_user_id(), 'tva_generate', true );

if ( isset( $tva_generate ) && (int) $tva_generate === 1 ) {
	$tva_menu['generation'] = [
		'slug'  => 'generate',
		'route' => '#generate',
		'icon'  => 'icon-generate-courses',
		'label' => esc_html__( 'Generate', 'thrive-apprentice' ),
		'items' => [],
	];
}

$tva_menu['course-homepage'] = [
	'slug'     => 'course-homepage',
	'href'     => tva_get_settings_manager()->factory( 'index_page' )->get_link(),
	'disabled' => empty( $index_page ) ? esc_attr__( 'You need to have defined a course page', 'thrive-apprentice' ) : 0,
	'icon'     => 'icon-eye',
	'label'    => esc_html__( 'Preview', 'thrive-apprentice' ),
	'items'    => [],
];

return $tva_menu;

