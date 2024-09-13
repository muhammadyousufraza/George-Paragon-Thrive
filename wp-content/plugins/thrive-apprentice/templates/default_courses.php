<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

$data = array(
	array(
		'name'         => 'Thrive Themes - Make More Sales Without Needing More Traffic',
		'args'         => array(
			'description' => __( 'Learn the most reliable method we\'ve ever found, to increase conversion rates.', 'thrive-apprentice' ),
		),
		'cover_image'  => TVA_Const::plugin_url( 'img/default_cover.png' ),
		'term_media'   => [
			'options' => [],
			'source'  => 'https://www.youtube.com/watch?v=P0UogSxdt74',
			'type'    => 'youtube',
		],
		'video_status' => 1,
		'level'        => 0,
		'logged_in'    => 1,
		'roles'        => array(),
		'topic'        => 0,
		'status'       => 'private',
		'order'        => 4,
		'modules'      => array(
			array(
				'args'     => array(
					'post_title'   => __( 'Learn how to use Scarcity Marketing', 'thrive-apprentice' ),
					'post_type'    => TVA_Const::MODULE_POST_TYPE,
					'post_excerpt' => __( 'In this module, we introduce the concept of scarcity marketing. It is tricky, if you do it right, it’s incredibly powerful. But if you do it wrong, it can cost you dearly and damage your reputation.', 'thrive-apprentice' ),
					'post_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus tristique ligula sit amet nulla eleifend, nec imperdiet ante semper. Nulla lobortis urna ac massa sagittis, a posuere ipsum commodo. Sed at nisl urna. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Pellentesque laoreet hendrerit metus, at fermentum dui elementum eget. Fusce porta tincidunt nisl id viverra. Nunc accumsan lectus eget nunc ultricies, vestibulum commodo purus suscipit. Quisque vestibulum elit ullamcorper, scelerisque velit nec, rutrum mi. In ac ligula at odio malesuada tristique et ac mauris. Morbi sed tristique lectus.',
					'post_status'  => 'publish',
				),
				'order'    => 0,
				'chapters' => array(
					array(
						'args'    => array(
							'post_title'  => __( 'Scarcity Marketing Secrets', 'thrive-apprentice' ),
							'post_type'   => TVA_Const::CHAPTER_POST_TYPE,
							'post_status' => 'publish',
						),
						'order'   => 0,
						'lessons' => array(
							array(
								'args'         => array(
									'post_title'   => __( 'Intro & Proof', 'thrive-apprentice' ),
									'post_type'    => TVA_Const::LESSON_POST_TYPE,
									'post_excerpt' => __( 'To start with, we’ll take a look at some proof. That way, you’ll see that the recipes in this course actually work (and produce some impressive results) in the real world and aren’t just marketing theory.', 'thrive-apprentice' ),
									'post_content' => __( 'To start with, we’ll take a look at some proof. That way, you’ll see that the recipes in this course actually work (and produce some impressive results) in the real world and aren’t just marketing theory.', 'thrive-apprentice' ),
									'post_status'  => 'publish',
								),
								'lesson_type'  => 'text',
								'lesson_order' => 0,
								'status'       => 1,
							),
							array(
								'args'         => array(
									'post_title'   => __( 'Scarcity Marketing – Good & Bad Examples', 'thrive-apprentice' ),
									'post_type'    => TVA_Const::LESSON_POST_TYPE,
									'post_excerpt' => __( 'In this lesson, we introduce the concept of scarcity marketing. And very importantly: we look at some examples of how not to do it and the principles for doing it right. Scarcity marketing is tricky in this regard. If you do it right, it’s incredibly powerful. But if you do it wrong, it can cost you dearly and damage your reputation.', 'thrive-apprentice' ),
									'post_content' => __( 'In this lesson, we introduce the concept of scarcity marketing. And very importantly: we look at some examples of how not to do it and the principles for doing it right. Scarcity marketing is tricky in this regard. If you do it right, it’s incredibly powerful. But if you do it wrong, it can cost you dearly and damage your reputation.', 'thrive-apprentice' ),
									'post_status'  => 'publish',
								),
								'lesson_type'  => 'text',
								'lesson_order' => 1,
								'status'       => 1,
							),
							array(
								'args'         => array(
									'post_title'   => __( 'How to Build Courses With Thrive Apprentice', 'thrive-apprentice' ),
									'post_type'    => TVA_Const::LESSON_POST_TYPE,
									'post_excerpt' => __( 'In this lesson, we introduce the concept of scarcity marketing. And very importantly: we look at some examples of how not to do it and the principles for doing it right. Scarcity marketing is tricky in this regard. If you do it right, it’s incredibly powerful. But if you do it wrong, it can cost you dearly and damage your reputation.', 'thrive-apprentice' ),
									'post_content' => __( 'In this lesson, we introduce the concept of scarcity marketing. And very importantly: we look at some examples of how not to do it and the principles for doing it right. Scarcity marketing is tricky in this regard. If you do it right, it’s incredibly powerful. But if you do it wrong, it can cost you dearly and damage your reputation.', 'thrive-apprentice' ),
									'post_status'  => 'publish',
								),
								'lesson_type'  => 'video',
								'lesson_order' => 2,
								'status'       => 1,
								'video'        => array(
									'options' => array(),
									'source'  => 'https://www.youtube.com/watch?v=P0UogSxdt74',
									'type'    => 'youtube',
								),
							),
							array(
								'args'         => array(
									'post_title'   => __( 'How to Build a Digital Product Empire', 'thrive-apprentice' ),
									'post_type'    => TVA_Const::LESSON_POST_TYPE,
									'post_excerpt' => __( 'In this lesson, we introduce the concept of scarcity marketing. And very importantly: we look at some examples of how not to do it and the principles for doing it right. Scarcity marketing is tricky in this regard. If you do it right, it’s incredibly powerful. But if you do it wrong, it can cost you dearly and damage your reputation.', 'thrive-apprentice' ),
									'post_content' => __( 'In this lesson, we introduce the concept of scarcity marketing. And very importantly: we look at some examples of how not to do it and the principles for doing it right. Scarcity marketing is tricky in this regard. If you do it right, it’s incredibly powerful. But if you do it wrong, it can cost you dearly and damage your reputation.', 'thrive-apprentice' ),
									'post_status'  => 'publish',
								),
								'lesson_type'  => 'audio',
								'lesson_order' => 3,
								'status'       => 1,
								'audio'        => array(
									'options' => array(),
									'source'  => 'https://soundcloud.com/activegrowth/forget-traffic-3-how-to-build',
									'type'    => 'soundcloud',
								),
							),
						),
					),
				),
			),
			array(
				'args'    => array(
					'post_title'   => __( 'Putting Scarcity Marketing Into Practice', 'thrive-apprentice' ),
					'post_type'    => TVA_Const::MODULE_POST_TYPE,
					'post_excerpt' => __( 'In this module, we get very practical: you’ll see exactly how we apply various countdowns and time limits on a website. No matter what your website and your promotion are about, you can follow along with these steps to create your own campaign.', 'thrive-apprentice' ),
					'post_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus tristique ligula sit amet nulla eleifend, nec imperdiet ante semper. Nulla lobortis urna ac massa sagittis, a posuere ipsum commodo. Sed at nisl urna. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Pellentesque laoreet hendrerit metus, at fermentum dui elementum eget. Fusce porta tincidunt nisl id viverra. Nunc accumsan lectus eget nunc ultricies, vestibulum commodo purus suscipit. Quisque vestibulum elit ullamcorper, scelerisque velit nec, rutrum mi. In ac ligula at odio malesuada tristique et ac mauris. Morbi sed tristique lectus.',
					'post_status'  => 'publish',
				),
				'order'   => 1,
				'lessons' => array(
					array(
						'args'         => array(
							'post_title'   => __( 'Behind the Scenes: Our Exact Scarcity Marketing Sequence', 'thrive-apprentice' ),
							'post_type'    => TVA_Const::LESSON_POST_TYPE,
							'post_excerpt' => __( 'Continuing the theme of keeping it real, in this video we take a look at the exact scarcity marketing sequence we used on one of our product launches. And by exact, I mean: you’ll see the actual emails we sent, the exact timings of when we sent them and the sales results corresponding to them.', 'thrive-apprentice' ),
							'post_content' => __( 'Continuing the theme of keeping it real, in this video we take a look at the exact scarcity marketing sequence we used on one of our product launches. And by exact, I mean: you’ll see the actual emails we sent, the exact timings of when we sent them and the sales results corresponding to them.', 'thrive-apprentice' ),
							'post_status'  => 'publish',
						),
						'lesson_type'  => 'text',
						'lesson_order' => 0,
						'status'       => 1,
					),
					array(
						'args'         => array(
							'post_title'   => __( 'Two Scarcity Marketing Recipes', 'thrive-apprentice' ),
							'post_type'    => TVA_Const::LESSON_POST_TYPE,
							'post_excerpt' => __( 'In this short lesson, we introduce two recipes you can apply to your business: the “under the radar” promotion and the classic product launch or sale. You’ll see the exact steps and timings required to make these promotions as effective as possible.', 'thrive-apprentice' ),
							'post_content' => __( 'In this short lesson, we introduce two recipes you can apply to your business: the “under the radar” promotion and the classic product launch or sale. You’ll see the exact steps and timings required to make these promotions as effective as possible.', 'thrive-apprentice' ),
							'post_status'  => 'publish',
						),
						'lesson_type'  => 'text',
						'lesson_order' => 1,
						'status'       => 1,
					),
				),
			),
		),
		'completed'    => array(
			array(
				'args' => array(
					'post_title'   => __( 'Congratulations!', 'thrive-apprentice' ),
					'post_type'    => TVA_Course_Completed::POST_TYPE,
					'post_excerpt' => __( 'It is indeed great news as you have completed the course in a short amount of time. I have seen you study all day and be hard on yourself for so many days. I really hoped that your hard work would pay off, and I am thrilled as God has granted my wish. It\'s hard work and dedication that have impacted your result.', 'thrive-apprentice' ),
					'post_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus tristique ligula sit amet nulla eleifend, nec imperdiet ante semper. Nulla lobortis urna ac massa sagittis, a posuere ipsum commodo. Sed at nisl urna. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Pellentesque laoreet hendrerit metus, at fermentum dui elementum eget. Fusce porta tincidunt nisl id viverra. Nunc accumsan lectus eget nunc ultricies, vestibulum commodo purus suscipit. Quisque vestibulum elit ullamcorper, scelerisque velit nec, rutrum mi. In ac ligula at odio malesuada tristique et ac mauris. Morbi sed tristique lectus.',
					'post_status'  => 'publish',
				),
			),
		),
	),
	array(
		'name'         => 'Thrive Themes - From Internet Rubbish to Content Gold',
		'args'         => array(
			'description' => __( 'How to improve your content marketing so you can stand out from all the rubbish content out there', 'thrive-apprentice' ),
		),
		'cover_image'  => TVA_Const::plugin_url( 'img/default_cover.png' ),
		'term_media'   => [
			'options' => [],
			'source'  => 'https://www.youtube.com/watch?v=P0UogSxdt74',
			'type'    => 'youtube',
		],
		'video_status' => 1,
		'level'        => 0,
		'logged_in'    => 1,
		'roles'        => array(),
		'topic'        => 0,
		'order'        => 3,
		'status'       => 'private',
		'lessons'      => array(
			array(
				'args'         => array(
					'post_title'   => __( 'Introduction – From Internet Rubbish to Content Marketing Gold', 'thrive-apprentice' ),
					'post_type'    => TVA_Const::LESSON_POST_TYPE,
					'post_excerpt' => __( 'Why is it so hard to create engaging content? And what is engaging content actually? Discover what you’ll need to learn in order to make your content marketing efficient', 'thrive-apprentice' ),
					'post_content' => __( 'Why is it so hard to create engaging content? And what is engaging content actually? Discover what you’ll need to learn in order to make your content marketing efficient', 'thrive-apprentice' ),
					'post_status'  => 'publish',
				),
				'lesson_type'  => 'text',
				'lesson_order' => 0,
				'status'       => 1,
			),
			array(
				'args'         => array(
					'post_title'   => __( 'Why Most Internet Content Never Sees The Light of Day', 'thrive-apprentice' ),
					'post_type'    => TVA_Const::LESSON_POST_TYPE,
					'post_excerpt' => __( 'Over two million blog articles are written every day. And most of them will never be read. Discover why this happens and how to avoid being part of this junkyard of internet rubbish.', 'thrive-apprentice' ),
					'post_content' => __( 'Over two million blog articles are written every day. And most of them will never be read. Discover why this happens and how to avoid being part of this junkyard of internet rubbish.', 'thrive-apprentice' ),
					'post_status'  => 'publish',
				),
				'lesson_type'  => 'text',
				'lesson_order' => 1,
				'status'       => 0,
			),

			array(
				'args'         => array(
					'post_title'   => __( 'Mastering Your Technique and Presentation', 'thrive-apprentice' ),
					'post_type'    => TVA_Const::LESSON_POST_TYPE,
					'post_excerpt' => __( 'Check these 4 things before publishing any content online. If you get one of these wrong, the actual content of your blog will not even matter all that much…', 'thrive-apprentice' ),
					'post_content' => __( 'Check these 4 things before publishing any content online. If you get one of these wrong, the actual content of your blog will not even matter all that much…', 'thrive-apprentice' ),
					'post_status'  => 'publish',
				),
				'lesson_type'  => 'text',
				'lesson_order' => 2,
			),
		),
	),
	array(
		'name'        => 'Thrive Themes - 22 Tips to Build Your Mailing List Faster',
		'args'        => array(
			'description' => __( 'The Cheat Sheet for Turning Your Website Into a More Effective List Building Machine', 'thrive-apprentice' ),
		),
		'cover_image' => TVA_Const::plugin_url( 'img/default_cover.png' ),
		'term_media'  => [
			'options' => [],
			'source'  => 'https://www.youtube.com/watch?v=P0UogSxdt74',
			'type'    => 'youtube',
		],
		'level'       => 0,
		'logged_in'   => 0,
		'roles'       => array(),
		'topic'       => 0,
		'order'       => 2,
		'status'      => 'private',

		'lessons' => array(
			array(
				'args'         => array(
					'post_title'   => __( 'Introduction – From Internet Rubbish to Content Marketing Gold', 'thrive-apprentice' ),
					'post_type'    => TVA_Const::LESSON_POST_TYPE,
					'post_excerpt' => __( 'Why is it so hard to create engaging content? And what is engaging content actually? Discover what you’ll need to learn in order to make your content marketing efficient', 'thrive-apprentice' ),
					'post_content' => __( 'Why is it so hard to create engaging content? And what is engaging content actually? Discover what you’ll need to learn in order to make your content marketing efficient', 'thrive-apprentice' ),
					'post_status'  => 'publish',
				),
				'lesson_type'  => 'text',
				'lesson_order' => 0,
				'status'       => 1,
			),
		),
	),
	array(
		'name'        => 'Thrive Themes - Build a Conversion Focused Website From Scratch',
		'args'        => array(
			'description' => __( 'This course will take you from zero to conversion optimized website in 10 steps.', 'thrive-apprentice' ),
		),
		'cover_image' => TVA_Const::plugin_url( 'img/default_cover.png' ),
		'level'       => 0,
		'logged_in'   => 0,
		'roles'       => array(),
		'topic'       => 0,
		'order'       => 1,
		'status'      => 'private',

		'lessons' => array(
			array(
				'args'         => array(
					'post_title'   => __( 'Introduction – From Internet Rubbish to Content Marketing Gold', 'thrive-apprentice' ),
					'post_type'    => TVA_Const::LESSON_POST_TYPE,
					'post_excerpt' => __( 'Why is it so hard to create engaging content? And what is engaging content actually? Discover what you’ll need to learn in order to make your content marketing efficient', 'thrive-apprentice' ),
					'post_content' => __( 'Why is it so hard to create engaging content? And what is engaging content actually? Discover what you’ll need to learn in order to make your content marketing efficient', 'thrive-apprentice' ),
					'post_status'  => 'publish',
				),
				'lesson_type'  => 'text',
				'lesson_order' => 0,
			),
		),
	),
	array(
		'name'        => 'Thrive Themes - Multi Step Mastery: Build a Targeted Mailing List',
		'args'        => array(
			'description' => __( 'Discover the 4 most powerful methods to put this new list building strategy to use.', 'thrive-apprentice' ),
		),
		'cover_image' => TVA_Const::plugin_url( 'img/default_cover.png' ),
		'level'       => 0,
		'logged_in'   => 0,
		'roles'       => array(),
		'topic'       => 0,
		'order'       => 0,
		'status'      => 'private',
		'lessons'     => array(
			array(
				'args'         => array(
					'post_title'   => __( 'Introduction – From Internet Rubbish to Content Marketing Gold', 'thrive-apprentice' ),
					'post_type'    => TVA_Const::LESSON_POST_TYPE,
					'post_excerpt' => __( 'Why is it so hard to create engaging content? And what is engaging content actually? Discover what you’ll need to learn in order to make your content marketing efficient', 'thrive-apprentice' ),
					'post_content' => __( 'Why is it so hard to create engaging content? And what is engaging content actually? Discover what you’ll need to learn in order to make your content marketing efficient', 'thrive-apprentice' ),
					'post_status'  => 'publish',
				),
				'lesson_type'  => 'text',
				'lesson_order' => 1,
			),
		),
	),
	array(
		'name'        => 'Thrive Themes - Assessment course demo',
		'args'        => array(
			'description' => __( 'Discover the 4 most powerful methods to put this new list building strategy to use.', 'thrive-apprentice' ),
		),
		'cover_image' => TVA_Const::plugin_url( 'img/default_cover.png' ),
		'level'       => 0,
		'logged_in'   => 0,
		'roles'       => array(),
		'topic'       => 0,
		'order'       => 0,
		'status'      => 'private',
		'assessments' => [
			[
				'args'                => [
					'post_title'   => __( 'Assessment: Two Scarcity Marketing Recipes', 'thrive-apprentice' ),
					'post_type'    => TVA_Const::ASSESSMENT_POST_TYPE,
					'post_excerpt' => __( 'Examinations, finals, quizzes, and graded papers are examples of summative assessments that test student knowledge of a given topic or subject. These graded assessments and assignments are often high stakes and are geared towards testing students.', 'thrive-apprentice' ),
					'post_content' => __( 'Lorem Ipsum este pur și simplu un text fals al industriei de tipărire și de tipărire. Lorem Ipsum a fost textul fals standard al industriei încă din anii 1500, când o imprimantă necunoscută a luat o bucătărie de tipărire și a amestecat-o pentru a face o carte cu specimene de tipar. A supraviețuit nu numai cinci secole, ci și saltului în compunerea electronică, rămânând în esență neschimbat. A fost popularizat în anii 1960 odată cu lansarea foilor Letraset care conțin pasaje Lorem Ipsum și, mai recent, cu software-ul de publicare desktop precum Aldus PageMaker, inclusiv versiuni de Lorem Ipsum.', 'thrive-apprentice' ),
					'post_status'  => 'publish',
				],
				'tva_assessment_type' => 'youtube_link',
			],
		],
	),
);

return $data;
