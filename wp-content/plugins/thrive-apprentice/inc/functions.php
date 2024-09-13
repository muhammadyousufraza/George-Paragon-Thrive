<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-university
 */

use TVA\Assessments\Grading\Category;
use TVA\Assessments\TVA_User_Assessment;
use TVA\Product;
use TVA\Stripe\Hooks as Stripe_Hooks;
use TVA\TTB\Apprentice_Wizard;
use TVA\TTB\Check as TTB_Check;
use TVA\TTB\Main as TTB_Main;
use TVD\Content_Sets\Set;
use function TVA\Architect\Dynamic_Actions\tcb_tva_dynamic_actions;
use function TVA\Architect\Visual_Builder\tcb_tva_visual_builder;
use function TVA\TTB\tva_wizard;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

/**
 * Add the dummy course and lessons in order to be able to see the preview without any lessons
 */
function tva_create_default_data() {
	$courses = tva_get_courses( array( 'private' => true ) );

	if ( empty( $courses ) ) {
		update_option( 'tva_switch_topic_options', true );
		$data = include TVA_Const::plugin_path( 'templates/default_courses.php' );

		foreach ( $data as $course ) {
			tva_add_course( $course );
		}
	}
}

/**
 * Add default courses
 *
 * @param $course
 */
function tva_add_course( $course ) {
	$current_user = wp_get_current_user();

	$course['author'] = array(
		'ID'         => $current_user->ID,
		'user_login' => $current_user->user_login,
	);

	$result = wp_insert_term( $course['name'], TVA_Const::COURSE_TAXONOMY, $course['args'] );

	if ( ! is_wp_error( $result ) ) {
		$term_id = $result['term_id'];
		foreach ( $course as $meta_key => $meta_value ) {
			if ( $meta_key === 'name' || $meta_key === 'args' || $meta_key === 'lessons' || $meta_key === 'chapters' || $meta_key === 'modules' ) {
				continue;
			}

			update_term_meta( $term_id, 'tva_' . $meta_key, $meta_value );
		}

		/* make sure the course has a `course overview` post associated */
		$instance = new TVA_Course_V2( $term_id );
		$instance->get_overview_post( true );

		if ( isset( $course['modules'] ) && is_array( $course['modules'] ) ) {
			tva_insert_default_modules( $course['modules'], $term_id );
		} elseif ( isset( $course['chapters'] ) && is_array( $course['chapters'] ) ) {
			tva_insert_default_chapters( $course['chapters'], $term_id );
		} elseif ( isset( $course['lessons'] ) && is_array( $course['lessons'] ) ) {
			tva_insert_default_lessons( $course['lessons'], $term_id );
		} elseif ( isset( $course['assessments'] ) && is_array( $course['assessments'] ) ) {
			tva_insert_default_assessments( $course['assessments'], $term_id );
		}

		if ( isset( $course['completed'] ) && is_array( $course['completed'] ) ) {
			tva_insert_default_course_completion( $course['completed'], $term_id );
		}
	}
}

/**
 * Create default lessons
 *
 * @param array $lessons
 * @param       $term_id
 */
function tva_insert_default_lessons( $lessons, $term_id, $post_parent = 0 ) {
	foreach ( (array) $lessons as $lesson ) {
		$lesson['args']['post_parent'] = $post_parent;

		$post_id = wp_insert_post( $lesson['args'] );
		update_post_meta( $post_id, 'tva_is_demo', 1 );

		foreach ( $lesson as $meta_key => $meta_value ) {
			if ( $meta_key === 'args' ) {
				continue;
			}

			if ( $meta_key === 'status' ) {
				$learned_lessons[ $term_id ][ $post_id ] = $meta_value;

				/** Replace or set the cookie */
				$time = strtotime( date( 'Y-m-d', time() ) . ' + 365 day' );
				if ( ! headers_sent() ) {
					setcookie( 'tva_learned_lessons', json_encode( $learned_lessons ), $time, '/' );
				}

				if ( is_user_logged_in() ) {
					/**
					 * we should also mark the user meta with the lessons he's seen so even if the
					 * user changes browsers we'll ba able to show the correct data
					 */
					$user_id = get_current_user_id();
					update_user_meta( $user_id, 'tva_learned_lessons', $learned_lessons );

				}
			}

			update_post_meta( $post_id, 'tva_' . $meta_key, $meta_value );
		}

		wp_set_object_terms( $post_id, $term_id, TVA_Const::COURSE_TAXONOMY );
	}
}

/**
 * Create default chapters
 *
 * @param array $chapters
 * @param       $term_id
 * @param int   $post_parent
 */
function tva_insert_default_chapters( $chapters, $term_id, $post_parent = 0 ) {
	foreach ( (array) $chapters as $chapter ) {
		$chapter['args']['post_parent'] = $post_parent;

		$post_id = wp_insert_post( $chapter['args'] );
		wp_set_object_terms( $post_id, $term_id, TVA_Const::COURSE_TAXONOMY );
		update_post_meta( $post_id, 'tva_chapter_order', $chapter['order'] );
		update_post_meta( $post_id, 'tva_is_demo', 1 );

		if ( isset( $chapter['lessons'] ) && is_array( $chapter['lessons'] ) ) {
			tva_insert_default_lessons( $chapter['lessons'], $term_id, $post_id );
		}
	}
}

/**
 * Create default modules
 *
 * @param array $modules
 * @param       $term_id
 */
function tva_insert_default_modules( $modules, $term_id ) {
	foreach ( (array) $modules as $module ) {
		$post_id = wp_insert_post( $module['args'] );

		wp_set_object_terms( $post_id, $term_id, TVA_Const::COURSE_TAXONOMY );
		update_post_meta( $post_id, 'tva_module_order', $module['order'] );
		update_post_meta( $post_id, 'tva_is_demo', 1 );

		if ( isset( $module['chapters'] ) && is_array( $module['chapters'] ) ) {
			tva_insert_default_chapters( $module['chapters'], $term_id, $post_id );
		} else if ( isset( $module['lessons'] ) && is_array( $module['lessons'] ) ) {
			tva_insert_default_lessons( $module['lessons'], $term_id, $post_id );
		} else if ( isset( $module['assessments'] ) && is_array( $module['assessments'] ) ) {
			tva_insert_default_assessments( $module['assessments'], $term_id, $post_id );
		}
	}
}

/**
 * Create default course completion post
 *
 * @param array $posts
 * @param int   $term_id
 *
 * @return void
 */
function tva_insert_default_course_completion( $posts, $term_id ) {
	foreach ( (array) $posts as $post ) {
		$post_id = wp_insert_post( $post['args'] );

		wp_set_object_terms( $post_id, $term_id, TVA_Const::COURSE_TAXONOMY );
		update_post_meta( $post_id, 'tva_is_demo', 1 );
	}
}


/**
 * Creates default assessments
 *
 * @param array $posts
 * @param int   $term_id
 * @param int   $post_parent
 *
 * @return void
 */
function tva_insert_default_assessments( $posts, $term_id, $post_parent = 0 ) {
	foreach ( (array) $posts as $post ) {
		$post['args']['post_parent'] = $post_parent;

		$post_id = wp_insert_post( $post['args'] );
		wp_set_object_terms( $post_id, $term_id, TVA_Const::COURSE_TAXONOMY );
		update_post_meta( $post_id, 'tva_is_demo', 1 );

		foreach ( $post as $meta_key => $meta_value ) {
			if ( $meta_key === 'args' ) {
				continue;
			}

			update_post_meta( $post_id, $meta_key, $meta_value );
		}
	}
}

/**
 * Called after dash has been loaded
 */
function tva_dashboard_loaded() {
	require_once __DIR__ . '/classes/class-tva-product.php';
}

/**
 * Initialize the Update Checker
 */
function tva_update_checker() {
	new TVE_PluginUpdateChecker(
		'http://service-api.thrivethemes.com/plugin/update',
		TVA_Const::plugin_path( 'thrive-apprentice.php' ),
		'thrive-apprentice',
		12,
		'',
		'thrive_apprentice'
	);

	/**
	 * Adding icon of the product for update-core page
	 */
	add_filter( 'puc_request_info_result-thrive-apprentice', 'apprentice_set_product_icon' );
}

/**
 * Adding the product icon for the update core page
 *
 * @param $info
 *
 * @return mixed
 */

function apprentice_set_product_icon( $info ) {
	$info->icons['1x'] = TVA_Const::plugin_url( 'admin/img/thrive-apprentice-dashboard.png' );

	return $info;
}

/**
 * Checks if the current TCB version is the one required by Thrive Apprentice
 *
 * @return bool
 */
function tva_check_tcb_version() {

	/**
	 * Thrive Architect plugin is not activated
	 * and the code inside Thrive Apprentice will always be up to date
	 */
	if ( false === tve_in_architect() ) {
		return true;
	}

	$internal_architect_version = include TVA_Const::plugin_path( 'tcb/version.php' );

	/* make sure that we have the same version of architect inside the plugin and as individual plugin, otherwise conflicts can appear */

	return defined( 'TVE_VERSION' ) && version_compare( TVE_VERSION, $internal_architect_version, '=' );
}

/**
 * Checks if the current TTB version is the one required by Thrive Apprentice
 *
 * @return bool
 */
function tva_check_ttb_version() {

	/**
	 * Thrive Theme Builder is not the active theme -> the code inside Thrive Apprentice will always be up to date
	 */
	if ( ! TTB_Main::is_thrive_theme_active() ) {
		return true;
	}

	$internal_ttb_version = wp_get_theme( 'builder', TVA_Const::plugin_path() )->version;

	/* make sure we have the same version of TTB inside the plugin and in the standalone theme, otherwise conflicts can appear */

	return defined( 'THEME_VERSION' ) && $internal_ttb_version && version_compare( THEME_VERSION, $internal_ttb_version, '=' );
}

/**
 * make sure the TVA_product is displayed in thrive dashboard
 *
 * @param array $items
 *
 * @return array
 */
function tva_add_to_dashboard( $items ) {
	$items[] = new TVA_Product();

	return $items;
}

/**
 * Load the version file of Thrive Dashboard
 */
function tva_load_dash_version() {
	$tve_dash_path      = dirname( dirname( __FILE__ ) ) . '/thrive-dashboard';
	$tve_dash_file_path = $tve_dash_path . '/version.php';

	if ( is_file( $tve_dash_file_path ) ) {
		$version                                  = require( $tve_dash_file_path );
		$GLOBALS['tve_dash_versions'][ $version ] = array(
			'path'   => $tve_dash_path . '/thrive-dashboard.php',
			'folder' => '/thrive-apprentice',
			'from'   => 'plugins',
		);
	}
}

/**
 * Called on template_redirect hook
 *
 * If the user views a lesson, it sends the start actions for lesson, module, course depending on the lesson status
 * Should only be executed on lesson pages
 */
function tva_hooks() {
	/* 1. some general exclusions: */
	$should_fire = ! is_admin() && ! Apprentice_Wizard::is_frontend() && ! tve_dash_is_crawler();
	/* 2. ensure this is a lesson */
	$should_fire = $should_fire && is_single() && get_post_type() === TVA_Const::LESSON_POST_TYPE;
	/* 3. make sure this is not a TAr editing page */
	$should_fire = $should_fire && function_exists( 'is_editor_page' ) && ! is_editor_page();

	if ( $should_fire ) {
		if ( tva_access_manager()->has_access() ) {
			tva_send_hooks( get_the_ID() );
		} else {
			/**
			 * This hook is triggered when a user tries to access a premium course, but they don’t have access to it. The hook can be fired multiple times per user, each time they try to access the restricted course.
			 * </br>
			 * Example use case:- Send an email to let the user know how to login to the course
			 *
			 * @param array Course Details
			 * @param null|array User Details
			 *
			 * @api
			 */
			do_action( 'thrive_apprentice_restricted_course', tcb_tva_visual_builder()->get_active_course(), tvd_get_current_user_details() );
		}
	}
}

/**
 * Update the last login timestamp for the current user
 *
 * @return void
 */
function tva_update_last_online() {
	/* 1. some general exclusions: */
	$should_fire = ! is_admin() && ! Apprentice_Wizard::is_frontend() && ! tve_dash_is_crawler();
	/* 2. make sure the user is logged in */
	$should_fire = $should_fire && is_user_logged_in();
	/* 3. make sure this is not a TAr editing page */
	$should_fire = $should_fire && function_exists( 'is_editor_page' ) && ! is_editor_page();

	if ( $should_fire ) {
		update_user_meta( get_current_user_id(), 'tve_last_online', time() );
	}
}

/**
 * Returns the module status with respect to the provided lesson id
 *
 * @param                 $lesson_id
 * @param null|TVA_Course $course
 *
 * @return array|bool
 */
function tva_get_lesson_module_status( $lesson_id, $course = null ) {

	$lesson_post = get_post( $lesson_id );

	if ( ! $course instanceof TVA_Course ) {
		$terms  = wp_get_post_terms( $lesson_id, TVA_Const::COURSE_TAXONOMY );
		$course = new TVA_Course( $terms[0] );
	}

	$lesson_module_id = false;

	if ( $lesson_post->post_parent ) {
		$post_ancestors = get_post_ancestors( $lesson_post );

		foreach ( $post_ancestors as $ancestor_id ) {
			if ( get_post_type( $ancestor_id ) === TVA_Const::MODULE_POST_TYPE ) {
				$lesson_module_id = $ancestor_id;
				break;
			}
		}
	}

	$lesson_module = null;
	if ( ! empty( $lesson_module_id ) ) {
		foreach ( $course->modules as $module ) {
			if ( $module->ID === $lesson_module_id ) {
				$lesson_module = $module;
				break;
			}
		}
	}

	if ( null === $lesson_module ) {
		return false;
	}

	$tva_module = $lesson_module;
	if ( $tva_module instanceof WP_Post ) {
		$tva_module = TVA_Post::factory( $tva_module );
	}

	return array(
		'module_id' => $lesson_module_id,
		'start'     => tva_is_module_started( $lesson_module ),
		'end'       => $tva_module->is_completed(),
	);
}

/**
 * Main functionality for sending TVA Hooks in the admin
 *
 * @param               $lesson_id
 * @param               $user_id
 * @param string        $state
 */
function tva_send_hooks_for_item( $lesson_id, $state = 'start', $user_id = null ) {
	$post = get_post( $lesson_id );

	if ( ! isset( $post ) ) {
		return;
	}

	$lesson          = new TVA_Lesson( $post );
	$course          = $lesson->get_course_v2();
	$user_details    = tvd_get_current_user_details( $user_id );
	$lesson_details  = $lesson->get_details();
	$course_details  = $course->get_details();
	$customer        = new TVA_Customer( $user_id );
	$learned_lessons = $customer->get_learned_lessons_for_student();

	if ( $state === 'start' ) {

		do_action( 'thrive_apprentice_lesson_start', $lesson_details, $user_details );
	} elseif ( $state === 'end' ) {

		$excluded_lesson_ids = $course->get_excluded_lessons( true );
		if ( $lesson->is_free_for_all() ) {

			do_action( 'thrive_apprentice_free_lesson_completed', $lesson_details, $user_details );

			$learned_lessons_ids = array();
			if ( ! empty( $learned_lessons[ $course->get_id() ] ) ) {
				$learned_lessons_ids = array_map( 'intval', array_keys( $learned_lessons[ $course->get_id() ] ) );
			}
			$completed_lessons = array_intersect( $excluded_lesson_ids, $learned_lessons_ids ); //what lessons have been completed
			$remained_lessons  = empty( array_diff( $excluded_lesson_ids, $completed_lessons ) );
			if ( $remained_lessons ) { //all free lessons have been completed
				do_action( 'thrive_apprentice_all_free_lessons_completed', $user_details, $course->get_id() );
			}
		}

		do_action( 'thrive_apprentice_lesson_complete', $lesson_details, $user_details );

		do_action( 'thrive_apprentice_course_progress', $course, $user_details );
	}

	$module = $lesson->get_module();

	if ( $module ) {
		$lessons_count  = $customer->get_count_module_completed_lessons( $course->get_id(), $module );
		$module_details = $lesson->get_module_details();

		if ( $state === 'start' && $lessons_count === 1 ) {

			do_action( 'thrive_apprentice_module_start', $module_details, $user_details );
		} elseif ( $state === 'end' && $lessons_count === $module->get_published_lessons_count() ) {

			do_action( 'thrive_apprentice_module_finish', $module_details, $user_details );
		}
	}

	$lessons_count = $customer->get_count_course_completed_lessons( $course->get_id() );

	if ( $state === 'start' && $lessons_count === 1 ) {

		do_action( 'thrive_apprentice_course_start', $course_details, $user_details );
	} elseif ( $state === 'end' && $lessons_count === $course->get_published_lessons_count() ) {

		do_action( 'thrive_apprentice_course_finish', $course_details, $user_details );
	}
}

/**
 * Main functionality for sending TVA Hooks in the frontend
 *
 * @param               $lesson_id
 * @param string        $state
 * @param TVA_Course_V2 $course_v2 optional, it should be the course the lesson belongs to. If not sent, it's the current course identified from request
 */
function tva_send_hooks( $lesson_id, $state = 'start', $course_v2 = null ) {
	$lesson = new TVA_Lesson( get_post( $lesson_id ) );

	if ( $lesson->is_demo_content() ) {
		/**
		 * We do not send any hooks for demo content
		 */
		return;
	}

	$user_id         = get_current_user_id();
	$learned_lessons = tva_get_learned_lessons();
	$course_v2       = $course_v2 ?: tva_course();

	if ( empty( $course_v2->get_id() ) ) {
		/**
		 * If user accesses a deleted demo course, then there is no need to do anything here
		 */
		return;
	}

	tva_update_progress_cookie();

	$course           = new TVA_Course( $course_v2->get_wp_term() );
	$lesson_course_id = $course->get_id();

	if ( ! empty( $user_id ) ) {
		$author = get_term_meta( $lesson_course_id, 'tva_author', true );

		/**
		 * No need to go further if the current user is the author
		 */
		if ( isset( $author['ID'] ) && $user_id === (int) $author['ID'] ) {
			return;
		}
	}

	$lesson_found = false;
	$course_found = false;
	$user_details = tvd_get_current_user_details();

	if ( isset( $learned_lessons[ $course_v2->get_id() ] ) ) {
		$learned_lessons_ids = $learned_lessons[ $course_v2->get_id() ];

		if ( ! empty( $learned_lessons_ids ) ) {

			$ids                   = array_keys( $learned_lessons_ids );
			$published_lessons_ids = [];
			$learned_lessons_count = 0;

			foreach ( $course_v2->get_published_lessons() as $iterated_lesson ) {
				$published_lessons_ids[] = $iterated_lesson->ID;
			}

			foreach ( $ids as $id ) {
				$learned_lessons_count += in_array( $id, $published_lessons_ids );
			}

			if ( $state === 'start' && in_array( $lesson_id, $ids ) ) {
				$lesson_found = true;
			}

			if ( $state === 'start' || ( $state === 'end' && $learned_lessons_count !== $course_v2->published_lessons_count ) ) {
				$course_found = true;
			}
		}
	}

	if ( ! $lesson_found ) {
		$lesson_details     = $lesson->get_details();
		$lesson_cookie_name = 'lesson_' . $lesson_id . '_started';

		if ( $state === 'start' ) {

			if ( ! TVA_Cookie_Manager::get_cookie( $lesson_cookie_name ) ) {
				/**
				 * Triggered when a user views a lesson for the first time.The hook is fired each time the user starts a new lesson, but only once per lesson.
				 * </br>
				 * Example use case:- Trigger an email event when a students views a lesson for the first time
				 *
				 * @param array Lesson Details
				 * @param null|array User Details
				 *
				 * @api
				 */
				do_action( 'thrive_apprentice_lesson_start', $lesson_details, $user_details );
			}
			/**
			 * If there is progress, but the tva_course_start cookie was deleted in previous flows
			 */
			$course_cookie_name = 'course_' . $lesson_course_id . '_started';
			if ( empty( TVA_Cookie_Manager::get_cookie( $course_cookie_name ) ) ) {
				$course_found = false;
			}

			TVA_Cookie_Manager::set_cookie( $lesson_cookie_name, 1 );
		} elseif ( $state === 'end' && TVA_Cookie_Manager::get_cookie( $lesson_cookie_name ) ) {
			TVA_Cookie_Manager::remove_cookie( $lesson_cookie_name );

			$product = $course_v2->get_product();
			if ( $product instanceof Product ) {
				$excluded_lesson_ids = $course_v2->get_excluded_lessons( true );
				if ( count( $excluded_lesson_ids ) > 0 && in_array( $lesson_id, $excluded_lesson_ids ) ) {

					/**
					 * This hook is triggered when a user finishes a lesson that is free for all.
					 *
					 * @param array Lesson Details
					 * @param null|array User Details
					 *
					 * @api
					 */
					do_action( 'thrive_apprentice_free_lesson_completed', $lesson_details, $user_details );

					//are all lessons completed?
					$learned_lessons_ids = array();
					if ( ! empty( $learned_lessons[ $course_v2->get_id() ] ) ) {
						$learned_lessons_ids = array_map( 'intval', array_keys( $learned_lessons[ $course_v2->get_id() ] ) );
					}
					$completed_lessons = array_intersect( $excluded_lesson_ids, $learned_lessons_ids ); //what lessons have been completed
					$remained_lessons  = empty( array_diff( $excluded_lesson_ids, $completed_lessons ) );
					if ( $remained_lessons ) { //all free lessons have been completed
						/**
						 * This hook is triggered when all lessons marked as free for all are completed.
						 *
						 * @param null|array User Details
						 * @param int Course id
						 *
						 * @api
						 */
						do_action( 'thrive_apprentice_all_free_lessons_completed', $user_details, $course_v2->get_id() );
					}
				}
			}
			/**
			 * This hook is triggered when the user finishes a lesson, and another one loads, in the same session. The hook will only be fired once per user, for each completed lesson.</br></br>
			 * Example use case:- Display in-course notifications and interactive widgets, so that the course participants are able to navigate through the course.
			 *
			 * @param array Lesson Details
			 * @param null|array User Details
			 *
			 * @api
			 */
			do_action( 'thrive_apprentice_lesson_complete', $lesson_details, $user_details );

			/**
			 * This hook is triggered when a user finishes a lesson and is used to calculate the course progress
			 *
			 * @param array - Course Details
			 * @param null|array - User Details
			 */

			do_action( 'thrive_apprentice_course_progress', [ 'course_id' => $course_v2->term_id ], $user_details );
		}
	}

	$module_status = tva_get_lesson_module_status( $lesson_id, $course );

	if ( is_array( $module_status ) && $module_status[ $state ] ) {
		$module_details     = $lesson->get_module_details();
		$module_cookie_name = 'module_' . $module_status['module_id'] . '_started';

		if ( $state === 'start' ) {
			if ( ! TVA_Cookie_Manager::get_cookie( $module_cookie_name ) ) {
				/**
				 * This hook is triggered when a user views a module for the first time. Only fired once per module per user.
				 * </br>
				 * Can be useful if, for example, you would like to congratulate the students by sending an email through an autoresponder, when they start a module in Thrive Apprentice.
				 *
				 * @param array Module Details
				 * @param null|array User Details
				 *
				 * @api
				 */
				do_action( 'thrive_apprentice_module_start', $module_details, $user_details );
			}

			TVA_Cookie_Manager::set_cookie( $module_cookie_name, 1 );
		} elseif ( $state === 'end' && TVA_Cookie_Manager::get_cookie( $module_cookie_name ) ) {

			TVA_Cookie_Manager::remove_cookie( $module_cookie_name );


			/**
			 * This hook is triggered when a user finishes all the lessons from a module. The hook will only be fired once per user, when the module has been completed.
			 * </br>
			 * Example use case:- Send an email to congratulate the student on completing a module.
			 *
			 * @param array Module Details
			 * @param null|array User Details
			 *
			 * @api
			 */
			do_action( 'thrive_apprentice_module_finish', $module_details, $user_details );
		}
	}

	if ( ! $course_found ) {
		$course_details     = $course->get_details();
		$course_cookie_name = 'course_' . $lesson_course_id . '_started';
		if ( $state === 'start' ) {
			if ( ! TVA_Cookie_Manager::get_cookie( $course_cookie_name ) ) {
				/**
				 * This hook is triggered when someone loads the first lesson of a course for the first time. The hook will only be fired once per user, when they start a new course.
				 * </br>
				 * Example use case:- Send the date the student started the course to an autoresponder
				 *
				 * @param array Course Details
				 * @param null|array User Details
				 *
				 * @api
				 */
				do_action( 'thrive_apprentice_course_start', $course_details, $user_details );
			}

			TVA_Cookie_Manager::set_cookie( $course_cookie_name, 1 );
		} elseif ( $state === 'end' && TVA_Cookie_Manager::get_cookie( $course_cookie_name ) ) {
			TVA_Cookie_Manager::remove_cookie( $course_cookie_name );

			/**
			 * This hook is triggered when a user finishes all the lessons from a course. It will only be fired once per user, once the course has been completed.
			 * </br>
			 * Example use case:- Send an email to congratulate the student on completing the course
			 *
			 * @param array Course Details
			 * @param null|array User Details
			 *
			 * @api
			 */
			do_action( 'thrive_apprentice_course_finish', $course_details, $user_details );
		}
	}
}


/**
 * Register the post type and taxonomy used for the courses and lessons
 */
function tva_init() {

	global $tva_shortcodes;

	/**
	 * check if we have the term query class which is needed for the courses, if not we should just end it here
	 */
	if ( ! class_exists( 'WP_Term_Query' ) ) {
		return;
	}

	/**
	 * Flag for Apprentice used in main query
	 */
	if ( ! defined( 'TVA_IS_APPRENTICE' ) ) {
		define( 'TVA_IS_APPRENTICE', 1 );
	}

	register_post_type( TVA_Const::LESSON_POST_TYPE,
		array(
			'labels'             => array(
				'name' => 'Thrive Apprentice Lesson',
			),
			'publicly_queryable' => true,
			'public'             => true,
			'has_archive'        => false,
			'show_ui'            => false,
			'rewrite'            => array( 'slug' => TVA_Routes::get_route( TVA_Const::LESSON_POST_TYPE ) ),
			'hierarchical'       => false,
			'show_in_nav_menus'  => true,
			'taxonomies'         => array( TVA_Const::COURSE_TAXONOMY ),
			'show_in_rest'       => true,
			'_edit_link'         => 'post.php?post=%d',
			'map_meta_cap'       => true,
			'capabilities'       => array(
				'edit_others_posts'    => defined( 'TVE_DASH_EDIT_CPT_CAPABILITY' ) ? TVE_DASH_EDIT_CPT_CAPABILITY : 'tve-edit-cpt',
				'edit_published_posts' => defined( 'TVE_DASH_EDIT_CPT_CAPABILITY' ) ? TVE_DASH_EDIT_CPT_CAPABILITY : 'tve-edit-cpt',
			),
		)
	);

	register_post_type( TVA_Const::CHAPTER_POST_TYPE,
		array(
			'labels'             => array(
				'name' => 'Thrive Apprentice Chapter',
			),
			'publicly_queryable' => true,
			'public'             => true,
			'has_archive'        => false,
			'show_ui'            => false,
			'rewrite'            => array( 'slug' => 'chapter' ),
			'hierarchical'       => false,
			'show_in_nav_menus'  => true,
			'taxonomies'         => array( TVA_Const::COURSE_TAXONOMY ),
			'show_in_rest'       => true,
		)
	);

	register_post_type( TVA_Const::MODULE_POST_TYPE,
		array(
			'labels'             => array(
				'name' => 'Thrive Apprentice Module',
			),
			'publicly_queryable' => true,
			'public'             => true,
			'has_archive'        => false,
			'show_ui'            => false,
			'rewrite'            => array( 'slug' => TVA_Routes::get_route( TVA_Const::MODULE_POST_TYPE ) ),
			'hierarchical'       => false,
			'show_in_nav_menus'  => true,
			'taxonomies'         => array( TVA_Const::COURSE_TAXONOMY ),
			'show_in_rest'       => true,
			'_edit_link'         => 'post.php?post=%d',
		)
	);
	register_post_type( TVA_Const::ASSESSMENT_POST_TYPE,
		[
			'labels'             => [
				'name' => 'Thrive Apprentice Assessment',
			],
			'publicly_queryable' => true,
			'public'             => true,
			'has_archive'        => false,
			'show_ui'            => false,
			'rewrite'            => [ 'slug' => 'assessment' ],
			'hierarchical'       => false,
			'show_in_nav_menus'  => true,
			'taxonomies'         => [ TVA_Const::COURSE_TAXONOMY ],
			'show_in_rest'       => true,
			'_edit_link'         => 'post.php?post=%d',
		]
	);

	/**
	 * Needed to bound comments from courses pag on it
	 */
	register_post_type( TVA_Const::COURSE_POST_TYPE,
		array(
			'labels'       => array(
				'name' => 'Thrive Apprentice - Course',
			),
			'description'  => 'Hidden post type used to bind any comment to',
			'public'       => false,
			'supports'     => array( 'title', 'comments' ),
			'query_var'    => false,
			'show_in_rest' => true,
		)
	);

	/**
	 * post type used for storing TAr content for the "Restricted access" message
	 * This seems to be called during the activation hook, when TVA_Product is not yet loaded. No need to register the post type then.
	 */
	if ( ! TVA_Const::$tva_during_activation ) {
		register_post_type( TVA_Access_Restriction::POST_TYPE,
			array(
				'labels'       => array(
					'name' => 'Content Restriction',
				),
				'description'  => 'Hidden post type for storing restricted access content',
				'public'       => TVA_Product::has_access(), // only allow public access for logged administrators
				'show_in_menu' => false,
				'supports'     => array( 'title', 'content' ),
				'show_in_rest' => false,
				'rewrite'      => false,
			)
		);
		/**
		 * post type used to for storing TAr content for Course Overview page
		 */
		register_post_type(
			TVA_Course_Overview_Post::POST_TYPE,
			array(
				'labels'              => array(
					'name' => 'Course Overview',
				),
				'exclude_from_search' => true, //This post should not be present in wordpress search.
				'description'         => 'Hidden post type for storing course overview content',
				'public'              => TVA_Product::has_access(), // only allow public access for logged administrators
				'show_in_menu'        => false,
				'supports'            => array( 'title', 'content' ),
				'show_in_rest'        => false,
				'rewrite'             => false,
			)
		);

		/**
		 * Needs to be called not during the activation because of the Dashboard Trait that is inside this class
		 */
		TVA\Product::init();

		/**
		 * Register post type
		 */
		TVA_Protected_File::init_post_type();
		TVA_User_Assessment::init_post_type();
		Category::init_post_type();
		TVA_Course_Completed::register_post_type();
	}

	register_post_type( TVA_Resource::POST_TYPE, array(
		'labels'       => array(
			'name' => 'Resource',
		),
		'public'       => false,
		'show_in_rest' => false,
		'rest_base'    => 'resources',
	) );

	register_taxonomy(
		TVA_Const::COURSE_TAXONOMY,
		array(
			TVA_Const::LESSON_POST_TYPE,
			TVA_Const::CHAPTER_POST_TYPE,
			TVA_Const::MODULE_POST_TYPE,
		),
		array(
			'hierarchical'       => true,
			'public'             => true,
			'publicly_queryable' => true,
			'labels'             => array(
				'name'          => 'Courses',
				'singular_name' => 'Course',
			),
			'rewrite'            => array( 'slug' => tva_get_slug_for_courses() ),
		)
	);

	$reg_page_option = get_option( 'tva_default_register_page' );
	if ( ! TVA_Const::$tva_during_activation && $reg_page_option === false ) {
		tva_create_default_register_page();
	}

	/**
	 * Added for backwards compatibility
	 * We need to update the comment status for all courses which are already created and we only do it once!
	 */
	$is_comment_status_updated = get_option( 'tva_update_courses_comment_status' );

	if ( $is_comment_status_updated === false ) {
		add_option( 'tva_update_courses_comment_status', true );
		$terms = get_terms( array( 'taxonomy' => TVA_Const::COURSE_TAXONOMY ) );

		foreach ( $terms as $term ) {
			update_term_meta( $term->term_id, 'tva_comment_status', TVA_Const::TVA_DEFAULT_COMMENT_STATUS );
		}
	}

	/**
	 * Initialize the apprentice default logos
	 * CRUCIAL: If the option is empty initialize the tar/ttb default logos FIRST and then the apprentice ones
	 * FOr backwards comp: If the user had a logo before the dark/light update, keep and update that logo
	 */
	$are_default_logos_updated = get_option( 'tva_update_default_logo_wizard' );

	if ( ! $are_default_logos_updated ) {
		if ( ! class_exists( 'TCB_Logo', false ) ) {
			require_once __DIR__ . '/../tcb/inc/classes/logo/class-tcb-logo.php';
		}

		$logos = get_option( TCB_Logo::OPTION_NAME );

		/* if the option is empty, then we have to initialize the logo array with the default values */
		if ( empty( $logos ) ) {
			/* initialize the default logos */
			$logos = TCB_Logo::initialize_default_logos();
		}

		$logos = ensure_ta_default_logos( $logos );

		update_option( TCB_Logo::OPTION_NAME, $logos );
		update_option( 'tva_update_default_logo_wizard', 1 );
	}

	/**
	 * `wp_doing_ajax()` check added to make sure there are no concurrent updates for the demo content
	 */
	if ( ! TVA_Const::$tva_during_activation && is_admin() && ! wp_doing_ajax() && ! wp_doing_cron() && ! get_option( 'tva_visual_demo' ) ) {
		update_option( 'tva_visual_demo', true );
		tva_update_demo_content();
	}

	tva_get_hidden_post();
	$tva_shortcodes = new TVA_Shortcodes();

	/**
	 * Hide MemberMouse admin bar on design tab
	 */
	add_filter( 'pre_option_mm-option-show-preview-bar', static function () {
		if ( tva_is_inner_frame() ) {
			return '0';
		}

		return false;
	} );

	/**
	 * Hide Thrive Ultimatum HTML from Apprentice Inner Frame
	 *
	 * Also for apprentice controlled posts, if the user doesn't have access to view the post we do not display the campaign
	 */
	add_filter( 'thrive_ult_can_display_campaign', static function ( $can_display ) {
		if ( tva_is_inner_frame() || Apprentice_Wizard::is_frontend() ) {
			$can_display = false;
		}

		/**
		 * This works for both apprentice content and wordpress controlled content
		 * If the user hasn't got access to the content do not display the ultimatum campaign
		 */
		if ( ! tva_access_manager()->has_access() ) {
			$can_display = false;
		}

		return $can_display;
	} );

	/**
	 * Init protected files admin hooks
	 * Such as admin localize and other stuff like this
	 */
	TVA_Protected_File::init_admin_hooks();

	Stripe_Hooks::init();

	/**
	 * Flush permalinks
	 *
	 * The permalinks are flushed on plugin activation/deactivation
	 * The permalinks are flushed when the constants defined in the code is grater than it's value in the database
	 */
	$flush_rules_version = get_option( 'tva_flush_rewrite_rules_version', false );

	if ( ! $flush_rules_version || version_compare( $flush_rules_version, TVA_Const::FLUSH_REWRITE_RULES_VERSION, '<' ) ) {
		update_option( 'tva_flush_rewrite_rules_version', TVA_Const::FLUSH_REWRITE_RULES_VERSION );
		flush_rewrite_rules();
	}
}

/**
 * If apprentice default logos do not exist then add them
 * If only one logo exists, then set it to be the dark logo and add a light one
 *
 * @param array|array of arrays $logos
 *
 * @return array|array of arrays
 */
function ensure_ta_default_logos( $logos ) {
	$has_apprentice_dark  = false;
	$has_apprentice_light = false;

	foreach ( $logos as $id => $logo ) {
		if ( isset( $logo['scope'] ) && $logo['scope'] === 'tva' && isset( $logo['name'] ) ) {
			if ( $logo['name'] === 'Light' ) {
				$has_apprentice_light = true;
			} else if ( $logo['name'] === 'Dark' ) {
				$has_apprentice_dark = true;
			} else if ( $logo['name'] === 'Apprentice default logo' ) {
				$logos[ $id ]['name']    = 'Dark';
				$logos[ $id ]['default'] = 1;
				$has_apprentice_dark     = $id;
			}
		}
	}

	$logo_id = count( $logos );

	if ( ! $has_apprentice_dark ) {
		$logos[] = array(
			'id'            => $logo_id,
			'attachment_id' => '',
			'name'          => 'Dark',
			'default'       => 1,
			'active'        => 1,
			'scope'         => 'tva',
		);
		$logo_id ++;
	}

	if ( ! $has_apprentice_light ) {
		$attachment_id = is_int( $has_apprentice_dark ) ? $logos[ $has_apprentice_dark ]['attachment_id'] : '';

		$logos[] = array(
			'id'            => $logo_id,
			'attachment_id' => $attachment_id,
			'name'          => 'Light',
			'default'       => 1,
			'active'        => 1,
			'scope'         => 'tva',
		);
	}

	return $logos;
}

/**
 * Get the rewrite slug for courses
 *
 * @return string
 * @deprecated since 3.0 - Luca has to implement a general settings class and this method should be deleted and instead is should be used the URL for course index page
 */
function tva_get_slug_for_courses() {
	/**
	 * We cannot use TVA_Settings instance here as the tva_courses taxonomy is not yet registered
	 */
	$index_page = tva_get_setting( 'index_page' );

	if ( empty( $index_page ) ) {
		$provisional_page = get_option( 'tva_provisional_index_page', array() );
		if ( ! empty( $provisional_page ) ) {
			$index_page = $provisional_page;
		}
	}

	if ( ! empty( $index_page ) ) {
		$post = get_post( $index_page );
	}

	$invalid = array( 'course', 'chapter', 'module' );
	$slug    = isset( $post ) && ! is_wp_error( $post ) && ! in_array( $post->post_name, $invalid ) ? $post->post_name : 'courses';

	/**
	 * Allow others to customize course slug
	 */
	return apply_filters( 'tva_course_slug', $slug );
}

/**
 * Create rest routes for ajax calls
 */
function tva_create_initial_rest_routes() {

	$endpoints = array(
		'TVA_Topics_Controller',
		'TVA_Labels_Controller',
		'TVA_Levels_Controller',
		'TVA_Courses_Controller',
		'TVA_Lessons_Controller',
		'TVA_Settings_Controller', //<- @deprecated
		'TVA_Settings_Controller_V2',
		'TVA_Frontend_Controller',
		'TVA_User_Controller', //<- @deprecated
		'TVA_Customer_Controller',
		'TVA_Chapters_Controller',
		'TVA_Modules_Controller',
		'TVA_Logs_Controller',
		'TVA_Sendowl_Settings_Controller',
		'TVA_Tokens_Controller',
		'TVA_Orders_Controller',
		'TVA_Structure_Controller',
		'TVA_Bundle_Controller',
		'TVA_Access_Restriction_Controller',
		'TVA_Admin_Controller',
		'TVA_Access_Migration_Controller',
		'TVA_Protected_Files_Controller',
		'TVA_Assessments_Controller',
		'TVA_Resources_Controller',
		'TVA_Routes_Controller',
		'TVA_Skins_Controller',
		'TVA_Wizard_Controller',
		'TVA_Skin_Template_Controller',
		'TVA_Certificate_Controller',
		'TVA_Completed_Post_Controller',
		'TVA_Palette_Controller',
		'TVA_Products_Controller',
		'TVA_Campaigns_Controller',
		'TVA_Stripe_Controller',
	);

	foreach ( $endpoints as $e ) {
		/** @var TVA_REST_Controller $controller */
		$controller = new $e();
		$controller->register_routes();
	}
}

/**
 * Get the saved topics from the db, if none exists we should create the base one
 *
 * @param array $args
 *
 * @return array|mixed
 */
function tva_get_topics( $args = array() ) {

	if ( isset( $_REQUEST[ TVA_Const::TVA_FRAME_FLAG ] ) ) {
		$preview = get_option( 'tva_preview_option', true );

		/**
		 * In case we preview the demo index page we only need the default topic
		 */
		if ( 'false' === $preview ) {
			return array( TVA_Const::default_topic() );
		}
	}

	$topics = get_option( 'tva_filter_topics', array() );

	if ( empty( $topics ) ) {
		$topics[] = TVA_Const::default_topic();

		update_option( 'tva_filter_topics', $topics );
	}

	if ( isset( $args['by_courses'] ) && true === $args['by_courses'] ) {

		$courses    = tva_get_courses( array( 'published' => true ) );
		$new_topics = array();
		foreach ( $courses as $course ) {
			foreach ( $topics as $topic ) {
				if ( $topic['ID'] == $course->topic ) {
					$new_topics[ $topic['ID'] ] = $topic;
				}
			}
		}

		$topics = $new_topics;
	}

	usort( $topics, 'tva_sort_topics_by_id' );

	return $topics;
}

/**
 * @param array $args
 *
 * @return array|mixed
 */
function tva_get_labels( $args = array() ) {
	$labels = get_option( 'tva_filter_labels', array() );

	if ( empty( $labels ) ) {
		$labels = TVA_Const::default_labels();

		update_option( 'tva_filter_labels', $labels );
	}

	/**
	 * Add the no label to the list of labels
	 * This label is not saved in the database. It's for courses to be marked that it has no label
	 */
	$labels = array_merge( array(
		array(
			'ID'    => TVA_Const::NO_LABEL_ID,
			'class' => 'tva-no-label',
			'color' => '#FFF', //this needs to be hardcoded
			'title' => __( 'No label', 'thrive-apprentice' ),
		),
	), $labels );

	usort( $labels, 'tva_sort_topics_by_id' );

	/**
	 * Return label info with requested id, similar to getter
	 */
	if ( isset( $args['ID'] ) ) {
		$found = reset( $labels );

		foreach ( $labels as $label ) {
			if ( $label['ID'] == $args['ID'] ) {
				$found = $label;
				break;
			}
		}

		return $found;
	}

	return $labels;
}

/**
 * Sort the Topics ascending by their ID
 *
 * @param $a
 * @param $b
 *
 * @return int
 */
function tva_sort_topics_by_id( $a, $b ) {
	return $a['ID'] - $b['ID'];
}

/**
 * Get a topic by it's id\
 *
 * @param $id
 *
 * @return array
 */
function tva_get_topic_by_id( $id ) {
	$topics = get_option( 'tva_filter_topics', array() );

	if ( ! empty( $topics ) ) {
		foreach ( $topics as $topic ) {
			if ( $topic['ID'] == $id ) {
				return $topic;
			}
		}
	}

	return TVA_Const::default_topic();
}

/**
 * Update the learned lessons cookie if the admin changed the progress for that user
 */
function tva_update_progress_cookie() {
	$meta = get_user_meta( get_current_user_id(), 'tva_progress_manually_changed', true );

	if ( $meta ) {
		foreach ( $_COOKIE as $cookieKey => $cookieValue ) {
			if ( preg_match( '/tva_(lesson|module|course)_[0-9]+_started/', $cookieKey ) ) {
				TVA_Cookie_Manager::remove_cookie( str_replace( 'tva_', '', $cookieKey ) );
			}
		}

		TVA_Cookie_Manager::set_cookie( 'learned_lessons', json_encode( get_user_meta( get_current_user_id(), 'tva_learned_lessons', true ) ) );
		delete_user_meta( get_current_user_id(), 'tva_progress_manually_changed' );
	}
}

/**
 * Get a list of courses filtered by topic
 *
 * @param array $arguments
 *
 * @return WP_Term[]|TVA_Course[]
 */
function tva_get_courses( $arguments = array() ) {

	$term_query = new stdClass();
	$page       = isset( $arguments['page'] ) ? $arguments['page'] : 1;
	$per_page   = isset( $arguments['per_page'] ) ? $arguments['per_page'] : TVA_Const::DEFAULT_COURSES_PER_PAGE;

	$args = tva_get_courses_args( $arguments );

	if ( isset( $arguments['page'] ) ) {
		$args['number'] = $per_page;
		$args['offset'] = ( $page - 1 ) * $per_page;
	}

	if ( class_exists( 'WP_Term_Query' ) ) {
		$term_query = new WP_Term_Query( $args );
	}

	/**
	 * Push all the term custom fields into the term object
	 */
	$courses = array();
	if ( ! isset( $arguments['without_data'] ) || $arguments['without_data'] !== true ) {

		if ( ! empty( $term_query->terms ) ) {
			foreach ( $term_query->terms as $term ) {
				$term      = tva_get_term_data( $term, $arguments );
				$courses[] = $term;
			}
		}
	} else {
		return $term_query->terms;
	}

	$order = array();

	foreach ( $courses as $key => $course ) {
		$order[ $key ] = $course->order;
	}

	/**
	 * Sort the courses by order in a descending way. Last added course will be the first in the list
	 */
	array_multisort( $order, SORT_DESC, $courses );

	return $courses;
}

/**
 * Get a course by it's ID
 *
 * @param       $id
 * @param array $args
 *
 * @return mixed
 */
function tva_get_course_by_id( $id, $args = array() ) {
	$term = get_term_by( 'id', $id, TVA_Const::COURSE_TAXONOMY );

	return tva_get_term_data( $term, $args );
}

/**
 * Get a course by it's slug
 *
 * @param       $slug
 * @param array $args
 *
 * @return mixed
 */
function tva_get_course_by_slug( $slug, $args = array() ) {
	$term = get_term_by( 'slug', $slug, TVA_Const::COURSE_TAXONOMY );

	return tva_get_term_data( $term, $args );
}

/**
 * Set the arguments for the query
 *
 * @param $arguments
 *
 * @return array
 */
function tva_get_courses_args( $arguments ) {
	if ( isset( $arguments['topics'] ) ) {
		$args = array(
			'taxonomy'   => TVA_Const::COURSE_TAXONOMY,
			'count'      => true,
			'hide_empty' => false,
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key'     => 'tva_topic',
					'value'   => $arguments['topics'],
					'compare' => 'IN',
				),
			),
		);
	} else {
		$args = array(
			'taxonomy'   => TVA_Const::COURSE_TAXONOMY,
			'hide_empty' => false,
			'meta_query' => array(
				'relation' => 'AND',
			),
		);
	}

	if ( isset( $arguments['published'] ) ) {
		$args['meta_query'][] = array(
			'key'     => 'tva_status',
			'value'   => 'publish',
			'compare' => 'IN',
		);
	} elseif ( isset( $arguments['private'] ) ) {
		$args['meta_query'][] = array(
			'key'     => 'tva_status',
			'value'   => 'private',
			'compare' => 'IN',
		);
	} else {
		$args['meta_query'][] = array(
			'key'     => 'tva_status',
			'value'   => TVA_Post::$accepted_statuses,
			'compare' => 'IN',
		);
	}

	$args['meta_key'] = 'tva_order';
	$args['orderby']  = 'meta_value_num';
	$args['order']    = 'DESC';

	if ( isset( $arguments['s'] ) ) {
		$args['search'] = $arguments['s'];
	}

	return $args;
}

/**
 * Extend the term object
 *
 * @param $term WP_Term
 * @param $filters
 *
 * @return TVA_Course|WP_Term
 */
function tva_get_term_data( $term, $filters = array() ) {

	if ( ! ( $term instanceof WP_Term ) ) {
		return $term;
	}

	/**
	 * Handle dirty business first !!!
	 */
	$old_meta = get_term_meta( $term->term_id, 'tva_video_url', true );

	if ( ! empty( $old_meta ) ) {
		$new_meta = array(
			'media_type'          => get_term_meta( $term->term_id, 'tva_video_type', true ),
			'media_url'           => $old_meta,
			'media_extra_options' => get_term_meta( $term->term_id, 'tva_video_extra', true ),
		);

		update_term_meta( $term->term_id, 'tva_term_media', $new_meta );
		delete_term_meta( $term->term_id, 'tva_video_url' );
	}

	$course = new TVA_Course( $term, $filters );

	return apply_filters( 'tva_extra_term_data', $course->get_term() );
}

/**
 * Check if a course is guide
 *
 * @param $course
 *
 * @return bool
 */
function tva_is_course_guide( $course ) {
	if ( ! $course instanceof WP_Term ) {
		return false;
	}

	return 1 === $course->published_lessons_count;
}

/**
 * Fill in the data we're missing for any given lesson
 *
 * @param WP_Post|null $post
 *
 * @return mixed
 */
function tva_get_post_data( $post ) {

	if ( false === $post instanceof WP_Post ) {
		return $post;
	}

	$extra = get_post_meta( $post->ID, 'tva_video_extra', true );

	/**
	 * Handle dirty business first !!!
	 */
	$old_meta = get_post_meta( $post->ID, 'tva_video_url', true );

	if ( ! empty( $old_meta ) ) {
		$new_meta = array(
			'media_type'          => get_post_meta( $post->ID, 'tva_video_type', true ),
			'media_url'           => $old_meta,
			'media_extra_options' => get_post_meta( $post->ID, 'tva_video_extra', true ),
		);

		update_post_meta( $post->ID, 'tva_post_media', $new_meta );
		delete_post_meta( $post->ID, 'tva_video_url' );
	}

	$tcb_content = get_post_meta( $post->ID, 'tve_updated_post', true );

	$post->order            = (int) get_post_meta( $post->ID, 'tva_lesson_order', true );
	$post->tva_lesson_order = get_post_meta( $post->ID, 'tva_lesson_order', true );
	$post->video_extra      = $extra ? $extra : new stdClass();
	$post->cover_image      = get_post_meta( $post->ID, 'tva_cover_image', true );
	$post->lesson_type      = get_post_meta( $post->ID, 'tva_lesson_type', true );
	$post->post_media       = maybe_unserialize( get_post_meta( $post->ID, 'tva_post_media', true ) );
	$post->has_tcb_content  = ! empty( $tcb_content );
	$post->video_embed      = '';
	$post->state            = TVA_Const::NORMAL_STATE;

	if ( $post->post_media && $post->lesson_type !== 'text' ) {
		$fn = 'tva_get_' . $post->post_media['media_type'] . '_embed_code';

		if ( ! function_exists( $fn ) ) {
			return $post;
		}

		$embed = $fn( $post->ID, 'post' );

		if ( ! is_wp_error( $embed ) ) {
			$post->video_embed = $embed;
		}
	}

	return $post;
}

/**
 * Get the youtube embed code
 *
 * @param $post_id
 * @param $type
 *
 * @return string
 */
function tva_get_youtube_embed_code( $post_id, $type ) {
	$url_params = array();
	$rand_id    = 'player' . rand( 1, 1000 );

	$fn   = 'get_' . $type . '_meta';
	$data = $fn( $post_id, 'tva_' . $type . '_media', true );
	$url  = $data['media_url'];
	$attr = $data['media_extra_options'];

	parse_str( parse_url( $url, PHP_URL_QUERY ), $url_params );

	$video_id = ( isset( $url_params['v'] ) ) ? $url_params['v'] : 0;

	if ( strpos( $url, 'youtu.be' ) !== false ) {
		$chunks   = array_filter( explode( '/', $url ) );
		$video_id = array_pop( $chunks );
	}

	$src_url = '//www.youtube.com/embed/' . $video_id . '?not_used=1';

	/**
	 * Check if the url is a playlist url
	 */
	$matches = array();

	preg_match( '/^(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|list\/|playlist\?list=|playlist\?.+&list=))((\w|-){34})(?:\S+)?$/', $url, $matches );

	if ( isset( $matches[1] ) ) {
		$src_url = '//www.youtube.com/embed?listType=playlist&list=' . $matches[1];
	}

	if ( ! isset( $attr['show-related'] ) || ( isset( $attr['show-related'] ) && ( $attr['show-related'] == 0 || $attr['show-related'] === 'false' ) ) ) {
		$src_url .= '&rel=0';
	}
	if ( isset( $attr['hide-logo'] ) && ( $attr['hide-logo'] == 1 || $attr['hide-logo'] === 'true' ) ) {
		$src_url .= '&modestbranding=1';
	}
	if ( isset( $attr['hide-controls'] ) && ( $attr['hide-controls'] == 1 || $attr['hide-controls'] === 'true' ) ) {
		$src_url .= '&controls=0';
	}
	if ( isset( $attr['hide-title'] ) && ( $attr['hide-title'] == 1 || $attr['hide-title'] === 'true' ) ) {
		$src_url .= '&showinfo=0';
	}
	$hide_fullscreen = 'allowfullscreen';
	if ( isset( $attr['hide-full-screen'] ) && ( $attr['hide-full-screen'] == 1 || $attr['hide-full-screen'] === 'true' ) ) {
		$src_url .= '&fs=0';
	}
	if ( isset( $attr['autoplay'] ) && ( $attr['autoplay'] == 1 || $attr['autoplay'] === 'true' ) && ! is_editor_page() ) {
		$src_url .= '&autoplay=1&mute=1';
	}
	if ( ! isset( $attr['video_width'] ) ) {
		$attr['video_width']  = '100%';
		$attr['video_height'] = 400;
	} else {
		if ( $attr['video_width'] > 1080 ) {
			$attr['video_width'] = 1080;
		}
		$attr['video_height'] = ( $attr['video_width'] * 9 ) / 16;
	}

	$embed_code = '<iframe id="' . $rand_id . '" src="' . $src_url . '" height="' . $attr['video_height'] . '" width="' . $attr['video_width'] . '" frameborder="0" ' . $hide_fullscreen . ' ></iframe>';

	return $embed_code;
}

/**
 * Get the vimeo embed code
 *
 * @param $post_id
 *
 * @return string
 */
function tva_get_vimeo_embed_code( $post_id, $type ) {
	$width = '100%';
	$fn    = 'get_' . $type . '_meta';
	$data  = $fn( $post_id, 'tva_' . $type . '_media', true );
	$url   = $data['media_url'];

	if ( ! preg_match( '/(https?:\/\/)?(www\.)?(player\.)?vimeo\.com\/([a-z]*\/)*([0-9]{6,11})[?]?.*/', $url, $m ) ) {
		return '';
	}

	$video_id = $m[5];
	$rand_id  = 'player' . rand( 1, 1000 );

	$src_url = '//player.vimeo.com/video/' . $video_id;

	$video_height = '400';

	$embed_code = "<iframe id='" . $rand_id . "' src='" . $src_url . "' height='" . $video_height . "' width='" . $width . "' frameborder='0' webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>";

	return $embed_code;
}

/**
 * Get the wistia embed code
 *
 * @param $post_id
 *
 * @return string
 */
function tva_get_wistia_embed_code( $post_id, $type ) {
	$fn   = 'get_' . $type . '_meta';
	$data = $fn( $post_id, 'tva_' . $type . '_media', true );
	$url  = $data['media_url'];
	$url  = preg_replace( '/\?.*/', '', $url );

	$split = parse_url( $url );
	if ( strpos( $split['host'], 'wistia' ) === false ) {
		return '';
	}

	$exploded = explode( '/', $split['path'] );
	$video_id = end( $exploded );

	$src_url = '//fast.wistia.com/embed/medias/' . $video_id . '.jsonp';

	$embed_code = '<script src="' . $src_url . '" async></script>';
	$embed_code .= '<script src="//fast.wistia.com/assets/external/E-v1.js" async></script>';
	$embed_code .= '<div class="wistia_responsive_padding" style="padding:56.25% 0 0 0;position:relative;">';
	$embed_code .= '<div class="wistia_responsive_wrapper" style="height:100%;left:0;position:absolute;top:0;width:100%;">';
	$embed_code .= '<div class="wistia_embed wistia_async_' . $video_id . ' seo=false videoFoam=true" style="height:100%;width:100%">&nbsp;</div></div></div>';

	return $embed_code;
}

/**
 * Get the custom embed code
 *
 * @param $post_id
 *
 * @return string
 */
function tva_get_custom_embed_code( $post_id, $type ) {
	$fn   = 'get_' . $type . '_meta';
	$data = $fn( $post_id, 'tva_' . $type . '_media', true );

	/**
	 * If by any change someone puts a wistia url here we try to generate the html based on that url
	 */

	if ( preg_match( '/wistia/', $data['media_url'] ) && ! preg_match( '/(script)|(iframe)/', $data['media_url'] ) ) {
		return tva_get_wistia_embed_code( $post_id, $type );
	}

	return $data['media_url'];
}

/**
 * Get the custom embed code for soundcloud
 *
 * @param $post_id
 * @param $type
 *
 * @return string|WP_Error
 */
function tva_get_soundcloud_embed_code( $post_id, $type ) {
	$fn      = 'get_' . $type . '_meta';
	$data    = $fn( $post_id, 'tva_' . $type . '_media', true );
	$url     = $data['media_url'];
	$api_url = 'http://soundcloud.com/oembed';
	$args    = array(
		'url'      => $url,
		'autoplay' => false,
		'format'   => 'json',
	);

	$api_url .= '?';
	foreach ( $args as $k => $param ) {
		$api_url .= $k . '=' . $param . '&';
	}

	$api_url  = rtrim( $api_url, '?& ' );
	$response = tve_dash_api_remote_get( $api_url );

	if ( $response instanceof WP_Error ) {
		return new WP_Error( 'no-results', 'Wrong' );
	}

	$status = (int) $response['response']['code'];
	if ( $status !== 200 && $status !== 204 ) {
		return new WP_Error( 'bad-request', 'Bad Request' );
	}

	$data = @json_decode( $response['body'], true );

	return $data['html'];
}

/**
 * get the difficulty levels
 */
function tva_get_levels() {
	$levels = get_option( 'tva_difficulty_levels', array() );

	if ( empty( $levels ) ) {
		$levels = array(
			array(
				'ID'   => 0,
				'name' => 'None',
			),
			array(
				'ID'   => 1,
				'name' => 'Easy',
			),
			array(
				'ID'   => 2,
				'name' => 'Intermediate',
			),
			array(
				'ID'   => 3,
				'name' => 'Advanced',
			),

		);

		update_option( 'tva_difficulty_levels', $levels );
	}

	return $levels;
}

/**
 * Get a list of user roles
 *
 * @return array
 */
function tva_get_roles() {
	$wp_roles = get_editable_roles();
	$roles    = array();

	foreach ( $wp_roles as $id => $role ) {
		$roles[] = array(
			'ID'   => $id,
			'name' => $role['name'],
		);
	}

	return $roles;
}

/**
 * Overwrite the default templates in order to have them in the plugin folder and choose between them
 *
 * @param $template
 *
 * @return string
 */
function tva_template( $template ) {

	global $wp_query;

	if ( $wp_query->is_404() ) {
		return $template;
	}

	$obj = get_queried_object();

	$template_settings = tva_get_setting( 'template' );

	$template_id = isset( $template_settings['ID'] ) ? $template_settings['ID'] : 1;

	if ( tva_is_inner_frame() ) {
		$template_id = sanitize_key( $_REQUEST['tpl'] );
	}

	/**
	 * While registration page is the default one we load our custom template for it
	 */
	$default_reg_page = get_option( 'tva_default_register_page' );
	if ( ! empty( $obj->ID ) && isset( $default_reg_page['ID'] ) && $obj->ID === $default_reg_page['ID'] ) {
		return TVA_Const::plugin_path( '/templates/template_' . $template_id . '/register.php' );
	}

	if ( ! empty( $obj->ID ) && tva_get_settings_manager()->is_index_page( $obj->ID ) ) {
		// we should load the index template here
		return TVA_Const::plugin_path( '/templates/template_' . $template_id . '/apprentice.php' );
	}

	if ( is_tax( TVA_Const::COURSE_TAXONOMY ) ) {
		// we should load the archive template here
		return TVA_Const::plugin_path( '/templates/template_' . $template_id . '/archive.php' );
	}

	if ( isset( $obj ) && isset( $obj->post_type )
		 && ( ( $obj->post_type == TVA_Const::LESSON_POST_TYPE )
			  || ( $obj->post_type == TVA_Const::MODULE_POST_TYPE ) )
	) {
		// we should load the single template here
		return TVA_Const::plugin_path( '/templates/template_' . $template_id . '/single.php' );
	}

	return $template;
}

/**
 * redirect the user if only one course is set up and published
 */
function tva_template_redirect() {

	//if $skip_homepage_redirect is not set (null), then it should be considered as true to have the default behaviour
	$should_redirect = ! is_editor_page_raw() && ! tva_is_inner_frame() && (int) tva_get_setting( 'skip_homepage_redirect' ) !== 1;
	// don't redirect on wizard preview pages
	$should_redirect = $should_redirect && ! Apprentice_Wizard::is_during_preview();
	// only redirect if the current page the course index page
	$should_redirect = $should_redirect && tva_get_settings_manager()->is_index_page();

	if ( $should_redirect ) {

		$courses = tva_get_courses( array( 'published' => true, 'page' => 1, 'per_page' => 2 ) );
		if ( count( $courses ) === 1 ) {

			if ( count( $courses[0]->lessons ) === 1 ) {
				$url = get_permalink( $courses[0]->lessons[0]->ID );
				wp_redirect( $url, 302 );
				exit;
			}

			/* more than one lesson found, just redirect to the course page */
			wp_redirect( $courses[0]->url, 302 );
		}
	}
}

/**
 * Redirect the user when try to access a private course
 */
function tva_redirect_if_private() {
	if ( TVA_Product::has_access() ) {
		return;
	}

	if ( tva_is_private_term() ) {
		global $wp_query;
		$wp_query->set_404();
		status_header( 404 );
		nocache_headers();
	}
}

/**
 * Modifies the Admin Bar by removing the edit and thrive nodes for certain Apprentice Pages
 */
function tva_modify_admin_bar_before_render() {

	$post_id = get_the_ID();

	if ( tva_get_settings_manager()->is_index_page( $post_id ) ) {
		global $wp_admin_bar;
		$wp_admin_bar->remove_menu( 'edit' );
		$wp_admin_bar->remove_menu( 'tve_parent_node' );
	}
}

/**
 * Check if the current request is related to a private (demo) course
 *
 * @return bool
 */
function tva_is_private_term() {
	$result = false;

	if ( tva_is_apprentice() ) {
		$obj             = get_queried_object();
		$is_course       = $obj instanceof WP_Term && $obj->taxonomy === TVA_Const::COURSE_TAXONOMY;
		$is_appr_content = TTB_Check::course_item( $obj );

		/* 1. check courses */
		$result = $is_course && get_term_meta( $obj->term_id, 'status', true ) === 'private';

		/* 2. check individual lessons / modules */
		$result = $result || ( $is_appr_content && (bool) get_post_meta( $obj->ID, 'tva_is_demo', true ) );

	} elseif ( get_post_meta( get_the_ID(), 'tva_is_demo', true ) ) {
		$result = true;
	}

	return $result;
}

/**
 * Check if the current screen (request) if the inner contents iframe ( the one displaying the actual post content )
 */
function tva_is_inner_frame() {
	if ( empty( $_REQUEST[ TVA_Const::TVA_FRAME_FLAG ] ) ) {
		return false;
	}

	/**
	 * the iframe receives a query string variable
	 */
	if ( ! wp_verify_nonce( $_REQUEST[ TVA_Const::TVA_FRAME_FLAG ], TVA_Const::TVA_FRAME_FLAG ) ) {
		return false;
	}

	return true;
}

/**
 * Enqueue scripts on the frontend
 */
function tva_frontend_enqueue_scripts() {

	//load checkout css only when it's required
	if ( tva_get_settings_manager()->is_checkout_page() ) {
		tva_enqueue_style( 'tva-checkout-css', TVA_Const::plugin_url( 'css/checkout.css' ) );
	}

	/** enqueue scripts only on the apprentice pages */
	if ( tva_is_apprentice() ) {

		/**
		 * Don't let other styles or scripts beside the thrive ones to be available on these pages
		 */
		$load_scripts = (int) tva_get_settings_manager()->factory( 'load_scripts' )->get_value();

		if ( ( isset( $_REQUEST[ TVA_Const::TCB_FRAME_FLAG ] ) || ! isset( $_REQUEST[ TVA_Const::TCB_EDITOR ] ) ) && ! $load_scripts ) {
			global $wp_scripts;
			global $wp_styles;

			/**
			 * we should keep the admin bar and the media enqueue because media enqueue can only be added once with the wp_enqueue_media()
			 */
			$scripts_args = array(
				'admin-bar',
				'tve_leads_frontend',
			);
			$style_args   = array(
				'admin-bar',
				'tve_style_family_tve_flt',
				'tve_leads_forms',
			);

			if ( in_array( 'media-editor', $wp_scripts->queue, true ) ) {
				$scripts_args[] = 'media-editor';
				$scripts_args[] = 'media-audiovideo';

				$style_args[] = 'media-views';
				$style_args[] = 'imgareaselect';
			}

			/**
			 * Load the dependencies for Thrive Comments
			 */
			if ( class_exists( 'Thrive_Comments' ) ) {
				$scripts_args[] = 'tcm-frontend-js';
				$style_args[]   = 'tcm-front-styles-css';
				$style_args[]   = 'wp-auth-check';
				$style_args[]   = 'twentyseventeen-fonts';
				$style_args[]   = 'twentyseventeen-style';
				$style_args[]   = 'twentyseventeen-ie8';
			}

			/**
			 * Exception for editor script from dashboard
			 */
			$scripts_args[] = 'tvd-ss-tcb-hooks';

			$wp_scripts->queue = apply_filters( 'tva_allowed_scripts', $scripts_args );
			$wp_styles->queue  = apply_filters( 'tva_allowed_styles', $style_args );

			if ( class_exists( 'MemberMouse', false ) ) {
				$mm = new MemberMouse();
				$mm->loadResources();
			}

			/**
			 * force our themes scripts in the frontend
			 */

			if ( function_exists( 'thrive_enqueue_scripts' ) && function_exists( 'thrive_enqueue_scripts_for_tve' ) ) {
				thrive_enqueue_scripts();

				if ( isset( $_REQUEST[ TVA_Const::TCB_EDITOR ] ) ) {
					thrive_enqueue_scripts_for_tve();
				}
			}

			/**
			 * put back TTB scripts
			 */
			if ( function_exists( 'thrive_theme' ) && Thrive_Theme::is_active() ) {
				thrive_theme()->enqueue_scripts();
			}

			if ( function_exists( 'tve_frontend_enqueue_scripts' ) ) {
				tve_frontend_enqueue_scripts();
			}

			if ( function_exists( 'tve_enqueue_editor_scripts' ) && ! tva_is_inner_frame() ) {
				tve_enqueue_editor_scripts();
				tve_dash_frontend_enqueue();
			}

			if ( function_exists( 'tve_leads_enqueue_form_scripts' ) ) {
				tve_leads_enqueue_form_scripts();
			}
		}

		wp_enqueue_media();
		tva_enqueue_style( 'tva-syles-css', TVA_Const::plugin_url( 'css/styles.css' ) );
		tva_enqueue_style( 'tva-animate-css', TVA_Const::plugin_url( 'css/animate.css' ) );
		tva_enqueue_style( 'tva-scrollbar-css', TVA_Const::plugin_url( 'css/jquery.scrollbar.css' ) );

		if ( class_exists( 'TCB_Icon_Manager' ) ) {
			TCB_Icon_Manager::enqueue_icon_pack();
		}

		if ( function_exists( 'is_editor_page' ) && ! is_editor_page() ) {
			tva_enqueue_script( 'tva-scrollbar-js', TVA_Const::plugin_url( 'js/dist/jquery.scrollbar.min.js' ), array( 'jquery' ), false, true );
			tva_enqueue_script( 'tva-frontend-js', TVA_Const::plugin_url( 'js/dist/frontend.min.js' ), array( 'jquery', 'underscore', 'wp-api-request' ), false, true );

			wp_localize_script( 'tva-frontend-js', 'ThriveAppFront', tva_get_frontend_localization() );
		}
	}
	/**
	 * Enqueue scripts on checkout page (mainly for validation)
	 */
	$enqueue = tva_get_settings_manager()->is_checkout_page();
	$enqueue = $enqueue || tva_get_settings_manager()->is_thankyou_page();
	$enqueue = $enqueue || tva_get_settings_manager()->is_thankyou_multiple_page();
	$enqueue = $enqueue || tva_get_settings_manager()->is_login_page();

	if ( true === $enqueue ) {
		tva_enqueue_script( 'tva-sendowl-checkout-js', 'https://transactions.sendowl.com/assets/sendowl.js', array( 'jquery' ), false, true );
		tva_enqueue_script( 'tva-frontend-js', TVA_Const::plugin_url( 'js/dist/frontend.min.js' ), array( 'jquery', 'underscore', 'wp-api-request' ), false, true );
		wp_localize_script( 'tva-frontend-js', 'ThriveAppFront', tva_get_frontend_localization() );
	}

	if ( true === tva_is_inner_frame() ) {
		tva_enqueue_script( 'tva-editor-js', TVA_Const::plugin_url( 'js/dist/editor.min.js' ), array( 'jquery' ), false, true );
	}

	if ( Apprentice_Wizard::is_frontend() ) {
		tve_dash_enqueue_script( 'ttb-wizard-preview', THEME_ASSETS_URL . '/wizard.min.js', [ 'theme-frontend' ] );
		tve_dash_enqueue_style( 'ttb-wizard', THEME_ASSETS_URL . '/wizard-ui.css' );
		tva_enqueue_style( 'tva-wizard', TVA_Const::plugin_url( 'css/wizard.css' ) );
	}
}

/**
 * Localize the frontend
 *
 * @return array
 */
function tva_get_frontend_localization() {
	$obj       = get_queried_object();
	$lesson_id = false;
	$lesson    = new stdClass();
	$course    = new stdClass();

	if ( is_tax( TVA_Const::COURSE_TAXONOMY ) && ! is_single() ) {
		$course_id = $obj->term_id;
		$course    = tva_get_course_by_id( $course_id, array( 'published' => true, 'protection' => true ) );
	} elseif ( is_single() && in_array( $obj->post_type, [ TVA_Const::LESSON_POST_TYPE, TVA_Const::MODULE_POST_TYPE, TVA_Course_Completed::POST_TYPE, TVA_Const::ASSESSMENT_POST_TYPE ] ) ) {
		$lesson_id = $obj->ID;
		$lesson    = get_post( $lesson_id );
		$terms     = wp_get_post_terms( $lesson_id, TVA_Const::COURSE_TAXONOMY );
		if ( ! empty( $terms ) ) {
			$course = tva_get_course_by_slug( $terms[0]->slug, array( 'published' => true, 'protection' => true ) );
		}
	}

	$page_id = isset( $obj->ID ) ? $obj->ID : '';

	global $post;
	$post = tva_get_post_data( $post );

	$template = wp_parse_args( tva_get_setting( 'template' ), array(
		'collapse_modules'  => 0,
		'collapse_chapters' => 0,
	) );

	$data = array(
		'post_id'            => $page_id,
		'is_inner_frame'     => tva_is_inner_frame(),
		'index_page'         => ! empty( $page_id ) && tva_get_settings_manager()->is_index_page( $page_id ),
		'lesson_page'        => $lesson_id,
		'is_user_logged_in'  => is_user_logged_in(),
		'is_admin'           => TVA_Product::has_access(),
		'current_user'       => tva_get_current_user(),
		'allowed'            => tva_access_manager()->has_access(),
		'lesson'             => $lesson, //@todo this should really be changed
		'course'             => $course,
		'course_ref_post_id' => get_option( 'tva_course_hidden_post_id' ),
		'nonce'              => wp_create_nonce( 'wp_rest' ),
		'is_login'           => tva_get_settings_manager()->is_login_page(),
		'is_checkout'        => TVA_Const::tva_is_checkout_page(),
		'is_thankyou'        => TVA_Const::tva_is_thankyou_page() || tva_get_settings_manager()->is_thankyou_multiple_page(),
		'routes'             => array(
			'certificate' => tva_get_route_url( 'certificate' ),
			'frontend'    => tva_get_route_url( 'frontend' ),
			'user'        => tva_get_route_url( 'user' ),
		),
		'tva_register_page'  => tva_get_settings_manager()->is_register_page( $page_id ),
		't'                  => include dirname( dirname( __FILE__ ) ) . '/i18n.php',
		'has_comment_plugin' => tva_has_comment_plugin(),
		'is_editor_page'     => is_editor_page(),
		'frontend_warnings'  => [
			'download_certificate_notice' => tcb_tva_dynamic_actions()->get_course_nav_label( 'download_certificate_notice' ),
		],
		'template'           => array(
			'collapse_modules'  => (int) $template['collapse_modules'],
			'collapse_chapters' => (int) $template['collapse_chapters'],
		),
	);

	if ( Apprentice_Wizard::is_frontend() ) {
		$data['wizard'] = tva_wizard()->localize_frontend();
	}

	/**
	 * Allow other functionality to be hooked here
	 *
	 * @param {array} $data
	 */
	$data = apply_filters( 'tva_get_frontend_localization', $data );

	return $data;
}

/**
 * Remove admin bar from frame
 */
function tva_clean_inner_frame() {
	if ( ! tva_is_inner_frame() ) {
		return;
	}
	add_filter( 'show_admin_bar', '__return_false' );
}


/**
 * @return array|bool
 * Get current user data
 */
function tva_get_current_user() {

	if ( ! is_user_logged_in() ) {
		return false;
	}

	$current_user = wp_get_current_user();
	$result       = $current_user->to_array();

	//do not send sensitive data in frontend
	unset( $result['user_pass'] );
	unset( $result['user_activation_key'] );
	unset( $result['user_status'] );

	return $result;
}

/**
 * Thrive university breadcrumbs.
 *
 * We need to use the custom template as the archive instead of the normal archive page
 */
function tva_custom_breadcrumbs() {

	// Settings
	$prefix           = '';
	$cat_display      = '';
	$cat_nicename     = '';
	$cat_link         = '';
	$cat_name         = '';
	$breadcrums_id    = 'breadcrumbs';
	$breadcrums_class = 'breadcrumbs';

	// If you have any custom post types with custom taxonomies, put the taxonomy name below (e.g. product_cat)
	$custom_taxonomy = TVA_Const::COURSE_TAXONOMY;

	// Get the query & post information
	global $post, $wp_query;

	// Do not display on the homepage
	if ( ! is_front_page() ) {

		// Build the breadcrums
		echo '<ul id="' . $breadcrums_id . '" class="' . $breadcrums_class . '">';

		if ( is_archive() && ! is_tax() && ! is_category() && ! is_tag() ) {

			echo '<li class="item-current item-archive"><strong class="bread-current bread-archive tva_main_color">' . post_type_archive_title( $prefix, false ) . '</strong></li>';

		} elseif ( is_archive() && is_tax() && ! is_category() && ! is_tag() ) {

			// If post is a custom post type
			$post_type = get_post_type();

			// If it is a custom post type display name and link
			if ( $post_type != 'post' ) {
				$index_post_id = tva_get_setting( 'index_page' );

				if ( ! $index_post_id ) {
					echo '<li class="item-cat item-custom-post-type-apprentice"><a class="bread-cat bread-custom-post-type-apprentice" href="javascript:void(0)" title="some title">Apprentice Page</a></li>';
				} else {
					$index_post = get_post( $index_post_id );

					if ( $index_post instanceof WP_Post ) {
						echo '<li class="item-cat item-custom-post-type-' . $post_type . '"><a class="bread-cat bread-custom-post-type-' . $post_type . ' " href="' . get_permalink( $index_post ) . '" title="' . $index_post->post_title . '">' . $index_post->post_title . '</a><span>' . tva_get_svg_icon( 'breadcrumbs-arrow', '', true ) . '</span></li>';
					}
				}
			}

			$custom_tax_name = get_queried_object()->name;
			echo '<li class="item-current item-archive"><strong class="bread-current bread-archive tva_main_color">' . $custom_tax_name . '</strong></li>';

		} elseif ( is_single() ) {
			// If post is a custom post type
			$post_type = get_post_type();

			// If it is a custom post type display name and link
			if ( $post_type != 'post' ) {
				$index_post_id = tva_get_setting( 'index_page' );

				if ( ! $index_post_id ) {
					echo '<li class="item-cat item-custom-post-type-apprentice"><a class="bread-cat bread-custom-post-type-apprentice" href="javascript:void(0)" title="some title">Apprentice Page</a></li>';
				} else {
					$index_post = get_post( $index_post_id );

					echo '<li class="item-cat item-custom-post-type-' . $post_type . '"><a class="bread-cat bread-custom-post-type-' . $post_type . '" href="' . get_permalink( $index_post ) . '" title="' . $index_post->post_title . '">' . $index_post->post_title . '</a><span>' . tva_get_svg_icon( 'breadcrumbs-arrow', '', true ) . '</span></li>';
				}
			}

			// Get post category info
			$category = get_the_category();

			if ( ! empty( $category ) ) {

				$category = array_values( $category );

				// Get last category post is in
				$last_category = end( $category );

				// Get parent any categories and create array
				$get_cat_parents = rtrim( get_category_parents( $last_category->term_id, true, ',' ), ',' );
				$cat_parents     = explode( ',', $get_cat_parents );

				// Loop through parent categories and store in variable $cat_display
				$cat_display = '';
				foreach ( $cat_parents as $parents ) {
					$cat_display .= '<li class="item-cat">' . $parents . '<span>' . tva_get_svg_icon( 'breadcrumbs-arrow', '', true ) . '</span></li>';
				}
			}

			// If it's a custom post type within a custom taxonomy
			$taxonomy_exists = taxonomy_exists( $custom_taxonomy );
			if ( empty( $last_category ) && ! empty( $custom_taxonomy ) && $taxonomy_exists ) {

				$taxonomy_terms = get_the_terms( $post->ID, $custom_taxonomy );

				if ( empty( $taxonomy_terms ) ) {
					$taxonomy_terms = get_the_terms( $wp_query->queried_object->ID, $custom_taxonomy );
				}

				if ( ! empty( $taxonomy_terms ) ) {
					$cat_id       = $taxonomy_terms[0]->term_id;
					$cat_nicename = $taxonomy_terms[0]->slug;
					$cat_link     = get_term_link( $taxonomy_terms[0]->term_id, $custom_taxonomy );
					$cat_name     = $taxonomy_terms[0]->name;
				}
			}

			// Check if the post is in a category
			if ( ! empty( $last_category ) ) {
				echo $cat_display;
				echo '<li class="item-current item-' . $post->ID . '"><strong class="bread-current bread-' . $post->ID . ' tva_main_color" title="' . get_the_title() . '">' . get_the_title() . '</strong><span>' . tva_get_svg_icon( 'breadcrumbs-arrow', '', true ) . '</span></li>';

				// Else if post is in a custom taxonomy
			} elseif ( ! empty( $cat_id ) ) {
				echo '<li class="item-cat item-cat-' . $cat_id . ' item-cat-' . $cat_nicename . '"><a class="bread-cat bread-cat-' . $cat_id . ' bread-cat-' . $cat_nicename . ' tva_main_color" href="' . $cat_link . '" title="' . $cat_name . '">' . $cat_name . '</a><span>' . tva_get_svg_icon( 'breadcrumbs-arrow', '', true ) . '</span></li>';

				if ( $post->post_parent > 0 ) {
					$first_parent = get_post( $post->post_parent );

					if ( $first_parent->post_parent > 0 ) {
						$big_parent = get_post( $first_parent->post_parent );
						echo '<li class="item-cat item-custom-post-type-' . $big_parent->post_type . '"><a class="bread-cat bread-custom-post-type-' . $big_parent->post_type . ' tva_main_color" href="' . get_permalink( $big_parent->ID ) . '" title="' . get_the_title( $big_parent->ID ) . '">' . get_the_title( $big_parent->ID ) . '</a><span>' . tva_get_svg_icon( 'breadcrumbs-arrow', '', true ) . '</span></li>';
					}

					echo '<li class="item-cat item-custom-post-type-' . $first_parent->post_type . '">';

					if ( $first_parent->post_type === TVA_Const::MODULE_POST_TYPE ) {
						echo '<a class="bread-cat bread-custom-post-type-' . $first_parent->post_type . ' tva_main_color" 
					    href="' . get_permalink( $first_parent->ID ) . '" 
					    title="' . get_the_title( $first_parent->ID ) . '">' . get_the_title( $first_parent->ID ) . '</a>';
					} else {
						echo '<a href="' . $cat_link . '#tva-chapter-' . $first_parent->ID . '" 
					    class="chapter">' . get_the_title( $first_parent->ID ) . '</a>';
					}
					echo '<span>' . tva_get_svg_icon( 'breadcrumbs-arrow', '', true ) . '</span>';
					echo '</li>';
				}

				echo '<li class="item-current item-' . $wp_query->queried_object->ID . '"><strong class="bread-current tva_main_color bread-' . $wp_query->queried_object->ID . '" title="' . get_the_title() . '"> ' . get_the_title( $wp_query->queried_object->ID ) . '</strong></li>';

			} else {

				echo '<li class="item-current item-' . $post->ID . '"><strong class="bread-current tva_main_color bread-' . $post->ID . '" title="' . get_the_title() . '">' . get_the_title() . '</strong></li>';

			}
		} elseif ( is_category() ) {

			// Category page
			echo '<li class="item-current item-cat"><strong class="bread-current tva_main_color bread-cat">' . single_cat_title( '', false ) . '</strong></li>';

		} elseif ( is_page() ) {

			// Standard page
			if ( $post->post_parent ) {
				$parents = '';
				// If child page, get parents
				$anc = get_post_ancestors( $post->ID );

				// Get parents in the right order
				$anc = array_reverse( $anc );

				// Parent page loop
				foreach ( $anc as $ancestor ) {
					$parents = '<li class="item-parent item-parent-' . $ancestor . '"><a class="bread-parent bread-parent-' . $ancestor . ' tva_main_color" href="' . get_permalink( $ancestor ) . '" title="' . get_the_title( $ancestor ) . '">' . get_the_title( $ancestor ) . '</a><span>' . tva_get_svg_icon( 'breadcrumbs-arrow', '', true ) . '</span></li>';
				}

				// Display parent pages
				echo $parents;

				// Current page
				echo '<li class="item-current item-' . $post->ID . '"><strong title="' . get_the_title() . '"> ' . get_the_title() . '</strong></li>';

			} else {
				// Just display current page if not parents
				echo '<li class="item-current item-' . $post->ID . '"><strong class="bread-current tva_main_color bread-' . $post->ID . '"> ' . get_the_title() . '</strong></li>';

			}
		} elseif ( is_tag() ) {

			// Tag page

			// Get tag information
			$term_id       = get_query_var( 'tag_id' );
			$taxonomy      = 'post_tag';
			$args          = 'include=' . $term_id;
			$terms         = get_terms( $taxonomy, $args );
			$get_term_id   = $terms[0]->term_id;
			$get_term_slug = $terms[0]->slug;
			$get_term_name = $terms[0]->name;

			// Display the tag name
			echo '<li class="item-current item-tag-' . $get_term_id . ' item-tag-' . $get_term_slug . '"><strong class="bread-current tva_main_color bread-tag-' . $get_term_id . ' bread-tag-' . $get_term_slug . '">' . $get_term_name . '</strong></li>';

		} elseif ( is_day() ) {

			// Day archive

			// Year link
			echo '<li class="item-year item-year-' . get_the_time( 'Y' ) . '"><a class="bread-year bread-year-' . get_the_time( 'Y' ) . ' tva_main_color" href="' . get_year_link( get_the_time( 'Y' ) ) . '" title="' . get_the_time( 'Y' ) . '">' . get_the_time( 'Y' ) . ' Archives</a><span>' . tva_get_svg_icon( 'breadcrumbs-arrow', '', true ) . '</span></li>';

			// Month link
			echo '<li class="item-month item-month-' . get_the_time( 'm' ) . '"><a class="bread-month bread-month-' . get_the_time( 'm' ) . ' tva_main_color" href="' . get_month_link( get_the_time( 'Y' ), get_the_time( 'm' ) ) . '" title="' . get_the_time( 'M' ) . '">' . get_the_time( 'M' ) . ' Archives</a><span>' . tva_get_svg_icon( 'breadcrumbs-arrow', '', true ) . '</span></li>';

			// Day display
			echo '<li class="item-current item-' . get_the_time( 'j' ) . '"><strong class="bread-current tva_main_color bread-' . get_the_time( 'j' ) . '"> ' . get_the_time( 'jS' ) . ' ' . get_the_time( 'M' ) . ' Archives</strong></li>';

		} elseif ( is_month() ) {

			// Month Archive

			// Year link
			echo '<li class="item-year item-year-' . get_the_time( 'Y' ) . '"><a class="bread-year bread-year-' . get_the_time( 'Y' ) . ' tva_main_color" href="' . get_year_link( get_the_time( 'Y' ) ) . '" title="' . get_the_time( 'Y' ) . '">' . get_the_time( 'Y' ) . ' Archives</a><span>' . tva_get_svg_icon( 'breadcrumbs-arrow', '', true ) . '</span></li>';

			// Month display
			echo '<li class="item-month item-month-' . get_the_time( 'm' ) . '"><strong class="bread-month bread-month-' . get_the_time( 'm' ) . '" title="' . get_the_time( 'M' ) . '">' . get_the_time( 'M' ) . ' Archives</strong></li>';

		} elseif ( is_year() ) {

			// Display year archive
			echo '<li class="item-current item-current-' . get_the_time( 'Y' ) . '"><strong class="bread-current tva_main_color bread-current-' . get_the_time( 'Y' ) . '" title="' . get_the_time( 'Y' ) . '">' . get_the_time( 'Y' ) . ' Archives</strong></li>';

		} elseif ( is_author() ) {

			// Auhor archive

			// Get the author information
			global $author;
			$userdata = get_userdata( $author );

			// Display author name
			echo '<li class="item-current item-current-' . $userdata->user_nicename . '"><strong class="bread-current tva_main_color bread-current-' . $userdata->user_nicename . '" title="' . $userdata->display_name . '">' . 'Author: ' . $userdata->display_name . '</strong></li>';

		} elseif ( get_query_var( 'paged' ) ) {

			// Paginated archives
			echo '<li class="item-current item-current-' . get_query_var( 'paged' ) . '"><strong class="bread-current tva_main_color bread-current-' . get_query_var( 'paged' ) . '" title="Page ' . get_query_var( 'paged' ) . '">' . __( 'Page' ) . ' ' . get_query_var( 'paged' ) . '</strong></li>';

		} elseif ( is_search() ) {

			// Search results page
			echo '<li class="item-current item-current-' . get_search_query() . '"><strong class="bread-current tva_main_color bread-current-' . get_search_query() . '" title="Search results for: ' . get_search_query() . '">Search results for: ' . get_search_query() . '</strong></li>';

		} elseif ( is_404() ) {

			// 404 page
			echo '<li>' . 'Error 404' . '</li>';
		}

		echo '</ul>';

	}

}

/**
 * Include the styles and settings set by the editor
 */
function tva_add_head_styles() {

	$template_settings = tva_get_setting( 'template' );

	if ( empty( $template_settings ) ) {
		$id                = isset( $_REQUEST['tpl'] ) ? sanitize_key( $_REQUEST['tpl'] ) : 1;
		$settings          = include TVA_Const::plugin_path( 'templates/template_' . $id . '/data.php' );
		$template_settings = $settings['template'];
	}

	$index_page_id = ( int ) tva_get_setting( 'index_page' );

	/** Get what was queried in order to determine the topics */
	$topics = tva_get_topics();
	$labels = tva_get_labels();

	if ( tva_is_apprentice() ) {
		?>
		<?php if ( 'google' === $template_settings['font_source'] && ! tve_dash_is_google_fonts_blocked() ) : ?>
			<?php $font_url = $template_settings['font_url'] ? $template_settings['font_url'] : ''; ?>
			<link rel="stylesheet" id="tva_google_font" href="<?php echo $font_url; ?>" type="text/css" media="all">
		<?php endif; ?>

		<style id="tva_custom_styles">
            :root {
                --tva-template-main-color: <?php echo $template_settings['main_color']; ?>;
            }

			<?php foreach ( $labels as $label ) : ?>
            .tva_members_only-<?php echo $label['ID']; ?> {
                background-color: <?php echo $label['color']; ?> !important;
            }

            .tva_members_only-<?php echo $label['ID']; ?>:before {
                border-color: <?php echo $label['color']; ?> transparent transparent transparent;
            }

			<?php endforeach; ?>

			<?php /* dynamic labels */ TVA_Dynamic_Labels::output_css(); ?>

			<?php $obj = get_queried_object(); ?>

			<?php foreach ( $topics as $topic ) : ?>
			<?php $rgb = tva_hex2rgb( $topic['color'] ); ?>

            /**
			body tag removed, if any issues occur we need to put them back
			 */
            p, h1, h2, h3, h4, h5, span, a, strong, body .tva_paragraph, .tva-sidebar-container, .tva-checkbox-holder label, li, .tva-filter-checkbox-container.tva-clear-filters, #ta-registration-form, .tva_page_headline_wrapper {
                font-family: <?php echo $template_settings['font_family']; ?>;
            }

            #tve_editor strong {
                font-family: inherit;
            }

			<?php if ( 'google' === $template_settings['font_source'] ) : ?>

            strong {
                font-weight: <?php echo $template_settings['font_bold']; ?>
            }

            p, h1, h2, h3, h4, h5, span, a, body .tva_paragraph, .tva-checkbox-holder label, li, .tva-filter-checkbox-container.tva-clear-filters {
                font-weight: <?php echo $template_settings['font_regular']; ?>
            }

			<?php endif; ?>

            .tva-course-head-<?php echo $topic['ID']; ?>, .tva-course-footer-<?php echo $topic['ID']; ?> .tva-card-topic-action, .tva-course-footer-<?php echo $topic['ID']; ?> {
                background-color: <?php echo $topic['color']; ?>;
                color: <?php echo $topic['color']; ?>;
                border-color: <?php echo $topic['color']; ?>;
            }

            .tva-course-footer-<?php echo $topic['ID']; ?> .tva-card-topic-action:hover {
                background-color: <?php echo $topic['color']; ?> !important;
                color: #fff !important;
                border-color: <?php echo $topic['color']; ?> !important;
            }

            .tva-filter-checkbox-color-<?php echo $topic['ID']; ?>.tva-filter-checkbox-selected {
                background-color: <?php echo $topic['color']; ?>;
            }

            .tva-course-text-<?php echo $topic['ID']; ?> {
                color: <?php echo $topic['color']; ?>;
            }

            .tva-course-card-image-overlay-<?php echo $topic['ID']; ?> {
                background-color: <?php echo $topic['color']; ?>;
            }

            .image-<?php echo $topic['ID']; ?>-overlay {
                background: rgba(<?php echo $rgb['red'] . ', ' . $rgb['green'] . ', ' . $rgb['blue']; ?>, 0.25);
            }

			<?php if ( isset( $topic['icon_type'] ) && 'svg_icon' === $topic['icon_type'] ) : ?>

			<?php $svg_color = isset( $obj->post_type ) && ( $obj->ID === $index_page_id ) ? $topic['layout_icon_color'] : $topic['overview_icon_color']; ?>
			<?php $svg_color = $svg_color === $topic['color'] && $obj->ID !== $index_page_id ? '#ffffff' : $svg_color  ?>

            #tva-topic-<?php echo $topic['ID']; ?> {
                fill: <?php echo $svg_color; ?>;
                color: <?php echo $svg_color; ?>;
            }

            #tva-topic-<?php echo $topic['ID']; ?> .tva-custom-icon {
                color: <?php echo $svg_color; ?>
            }

			<?php endif; ?>

			<?php endforeach; ?>

            .tva_main_color, .tva-widget a.tva_main_color {
                color: <?php echo $template_settings['main_color']; ?>;
                fill: <?php echo $template_settings['main_color']; ?>;
            }

            .tva-cm-redesigned-breadcrumbs ul li a:hover {
                color: <?php echo $template_settings['main_color']; ?>;
            }

            .tva-courses-container .tva-course-card .tva-course-card-content .tva-course-description p a,
            body .tva-frontend-template#tva-course-overview .tva-container .tva-course-section .tva_paragraph a,
            body .tva-frontend-template .tva-course-lesson .tva-lesson-description a, .tva-module-single-page a,
            .tva-widget a {
                text-decoration: none;
                color: <?php echo $template_settings['main_color']; ?>;
            }

            .tva-courses-container .tva-course-card .tva-course-card-content .tva-course-description p a:hover,
            .tva-module-single-page a:hover {
                text-decoration: underline;
            }

            .tva-sidebar-container .tva-lessons-container .tva-lesson-container:hover .tva-icon-container svg.ta-sym-two,
            .tva-cm-container .tva-cm-lesson:hover .tva-cm-icons svg.ta-sym-two {
                fill: <?php echo $template_settings['main_color']; ?>;
            }

            @media (min-width: 700px) {
                .tva_lesson_headline {
                    font-size: <?php echo $template_settings['lesson_headline']; ?>px;
                }

                .tva_chapter_headline {
                    font-size: <?php echo $template_settings['chapter_headline']; ?>px;
                }

                .tva_module_headline {
                    font-size: <?php echo $template_settings['module_headline']; ?>px;
                }

                .tva_chapter_headline {
                    font-size: <?php echo $template_settings['chapter_headline']; ?>px;
                }

                .tva_module_headline {
                    font-size: <?php echo $template_settings['module_headline']; ?>px;
                }

                .tva_course_title {
                    font-size: <?php echo $template_settings['course_title']; ?>px;
                }

                .tva_page_headline {
                    font-size: <?php echo $template_settings['page_headline']; ?>px;
                }
            }

            .tva_main_color {
                color: <?php echo $template_settings['main_color']; ?>;
                fill: <?php echo $template_settings['main_color']; ?>;
            }

            .tva-courses-container .tva-course-card .tva-course-card-content .tva-course-description p a,
            body .tva-frontend-template#tva-course-overview .tva-container .tva-course-section .tva_paragraph a,
            body .tva-frontend-template .tva-course-lesson .tva-lesson-description a {
                text-decoration: none;
                color: <?php echo $template_settings['main_color']; ?>;
            }

            .tva-courses-container .tva-course-card .tva-course-card-content .tva-course-description p a:hover {
                text-decoration: underline;
            }

            .tva-sidebar-container .tva-lessons-container .tva-lesson-container:hover .tva-icon-container svg.ta-sym-two,
            .tva-cm-container .tva-cm-lesson:hover .tva-cm-icons svg.ta-sym-two {
                fill: <?php echo $template_settings['main_color']; ?>;
            }

            a.tva_main_color:hover, .tva-sidebar-container ul li a {
                color: <?php echo $template_settings['main_color']; ?>;
            }

            #tva_main_color_bg {
                background-color: <?php echo $template_settings['main_color']; ?>;
            }

			<?php $rgb = tva_hex2rgb( $template_settings['main_color'] ); ?>

            .tva_start_course:hover {
                box-shadow: 0 2px 7px 0 rgba(<?php echo $rgb['red'] . ', ' . $rgb['green'] . ', ' . $rgb['blue']; ?>, 0.67);
                color: #fff;
            }

            body .tva-header > div ul li:hover, header.tva-header ul.menu > li.h-cta {
                background-color: <?php echo $template_settings['main_color']; ?>;
            }

            #menu-primary-menu li ul li:hover {
                filter: grayscale(25%);
            }

            .tva-checkmark-stem, .tva-checkmark-kick {
                background-color: <?php echo $template_settings['main_color']; ?>;
            }

            body .tva-frontend-template#tva-course-overview .tva-container .tva-course-section .tva-course-lessons .tva-course-lesson-item:hover .tva-right .tva_lesson_headline .tva-custom-arrow {
                border: 2px solid<?php echo $template_settings['main_color']; ?>;
            }

            body .tva-frontend-template#tva-course-overview .tva-container .tva-course-section .tva-course-lessons .tva-course-lesson-item:hover .tva-right .tva_lesson_headline .tva-custom-arrow:before {
                border-top: 3px solid<?php echo $template_settings['main_color']; ?>;
                border-right: 3px solid<?php echo $template_settings['main_color']; ?>;
            }

            .tva_main_color_bg {
                background-color: <?php echo $template_settings['main_color']; ?>;
            }

            #tva_main_color_bg {
                background-color: <?php echo $template_settings['main_color']; ?>;
            }

            .tva_paragraph, .tva_paragraph p, .tva_thank_you p, .tva-author-description p, .tva-course-description {
                font-size: <?php echo $template_settings['paragraph']; ?>px;
                color: <?php echo $template_settings['paragraph_color']; ?>;
                line-height: 1.63;
            }

            .tva_paragraph p {
                line-height: 1.63;
                margin: 0 0 10px;
            }

            .tva_course_title {
                font-family: <?php echo $template_settings['font_family']; ?>;
                font-weight: <?php echo isset( $template_settings['font_bold'] ) && ! empty( $template_settings['font_bold'] ) ? $template_settings['font_bold'] : 500; ?>;
                color: <?php echo $template_settings['course_title_color']; ?>;
            }

            .tva_page_headline {
                font-weight: <?php echo isset( $template_settings['font_bold'] ) ? $template_settings['font_bold'] : 500; ?>;
                color: <?php echo $template_settings['page_headline_color']; ?>;
            }

            li[id^="tcb_custom_menu_"] span, .thrv_wrapper span {
                font-family: inherit;
            }

			<?php if ( isset( $template_settings['logo_size'] ) ) : ?>

            .tva_logo_size, .tva-img-logo {
                width: <?php echo $template_settings['logo_size']; ?>px;
            }

            .tva_text_logo_size {
                font-size: <?php echo $template_settings['logo_size']; ?>px;
                color: <?php echo $template_settings['main_color']; ?>;
            }

			<?php endif; ?>
            .tva-ghost-main-color {
                border: 1px solid <?php echo $template_settings['main_color']; ?> !important;
                color: <?php echo $template_settings['main_color']; ?> !important;
            }

            .tva-ghost-main-color:hover {
                background-color: <?php echo $template_settings['main_color']; ?> !important;
                color: #fff !important;
            }

            .tva-main-color-forced {
                color: <?php echo $template_settings['main_color']; ?> !important;
            }
		</style>
		<?php
	}
}

/**
 * Initialize the widgets
 */
function tva_widgets_init() {
	register_sidebar( array(
		'name'          => __( 'Thrive Apprentice Course Sidebar' ),
		'id'            => 'tva-sidebar',
		'before_widget' => '<section id="%1$s" class="tva-widget widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );

	register_sidebar( array(
		'name'          => __( 'Thrive Apprentice Module Sidebar' ),
		'id'            => 'tva-module-sidebar',
		'before_widget' => '<section id="%1$s" class="tva-widget widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );

	register_sidebar( array(
		'name'          => __( 'Thrive Apprentice Lesson Sidebar' ),
		'id'            => 'tva-lesson-sidebar',
		'before_widget' => '<section id="%1$s" class="tva-widget widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );

	register_sidebar( array(
		'name'          => __( 'Thrive Apprentice Footer' ),
		'id'            => 'tva-footer',
		'before_widget' => '<section id="%1$s" class="tva-widget widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<p class="ttl">',
		'after_title'   => '</p>',
	) );

	require_once TVA_Const::plugin_path( 'inc/classes/widgets/class-tva-menu.php' );
	require_once TVA_Const::plugin_path( 'inc/classes/widgets/class-tva-progress-bar.php' );
	require_once TVA_Const::plugin_path( 'inc/classes/widgets/class-tva-author.php' );
	require_once TVA_Const::plugin_path( 'inc/classes/widgets/class-tva-lesson-list.php' );
	require_once TVA_Const::plugin_path( 'inc/classes/widgets/class-tva-recent-comments.php' );

	register_widget( 'TVA_Menu' );
	register_widget( 'TVA_Progress_Bar_Widget' );
	register_widget( 'TVA_Author_Widget' );
	register_widget( 'TVA_Lesson_List_Widget' );
	register_widget( 'TVA_Recent_Comments' );

	/*
	 * There was a bunch of code below, which created some default widgets, and assigned them to the sidebars.
	 * If some of the indexes got messed up, hundreds of widgets were created - 9 new widgets per admin request ..
	 *
	 * Since the introduction of visual editing there's no point in creating default widgets - users can drag an drop an Apprentice widget wherever they want.
	 */
}

/**
 * Get the route url
 *
 * @param       $endpoint
 * @param int   $id
 * @param array $args
 *
 * @return string
 */
function tva_get_route_url( $endpoint, $id = 0, $args = array() ) {

	$url = get_rest_url() . TVA_Const::REST_NAMESPACE . '/' . $endpoint;

	if ( ! empty( $id ) && is_numeric( $id ) ) {
		$url .= '/' . $id;
	}

	if ( ! empty( $args ) ) {
		add_query_arg( $args, $url );
	}

	return $url;
}

/**
 * Exclude demo posts from search
 *
 * @param $query
 *
 * @return mixed
 */
function tva_exclude_posts_from_search( $query ) {
	/** @var $query WP_Query */
	if ( ! $query->is_search() || ! $query->is_main_query() ) {
		return $query;
	}

	$post_ids = (array) $query->get( 'post__not_in' );

	$query->set( 'post__not_in', array_merge( $post_ids, TVA_Manager::get_demo_posts( [ 'fields' => 'ids' ] ) ) );

	return $query;
}

/**
 * Change the attributes for the next post link
 *
 * @param $format
 *
 * @return mixed
 */
function tva_next_posts_link_attributes( $format ) {
	global $post;

	if ( tva_is_apprentice() ) {
		$terms  = wp_get_post_terms( $post->ID, TVA_Const::COURSE_TAXONOMY );
		$course = tva_get_course_by_slug( $terms[0]->slug );
		$topic  = tva_get_topic_by_id( $course->topic );

		$format = str_replace( 'href=', 'class="tva-button tva-next-button-' . $topic['ID'] . ' tva-next right clean-gray" href=', $format );
		$format = str_replace( '</a>', '<svg class="tva_main_color" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" width="24" height="24" viewBox="0 0 24 24"><path d="M4,11V13H16L10.5,18.5L11.92,19.92L19.84,12L11.92,4.08L10.5,5.5L16,11H4Z" /></svg></a>', $format );

	}

	return $format;
}

/**
 * Change the attributes for the previous post link
 *
 * @param $format
 *
 * @return mixed
 */
function tva_prev_posts_link_attributes( $format ) {
	global $post;

	if ( tva_is_apprentice() ) {
		$terms  = wp_get_post_terms( $post->ID, TVA_Const::COURSE_TAXONOMY );
		$course = tva_get_course_by_slug( $terms[0]->slug );
		$topic  = tva_get_topic_by_id( $course->topic );

		$format = str_replace( 'href=', 'class="tva-button tva-prev-button-' . $topic['ID'] . ' tva-prev left clean-gray" href=', $format );
		$format = str_replace( 'rel="prev">', 'rel="next"> <svg class="tva_main_color" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" width="24" height="24" viewBox="0 0 24 24"><path d="M20,11V13H8L13.5,18.5L12.08,19.92L4.16,12L12.08,4.08L13.5,5.5L8,11H20Z" /></svg> ', $format );
	}

	return $format;
}

/**
 * Get the preview URL
 *
 * @return mixed
 */
function tva_get_preview_url() {

	$courses = tva_get_courses( array( 'published' => true, 'without_data' => true, 'page' => 1, 'per_page' => 1 ) );
	$preview = tva_get_preview_option();

	if ( ! empty( $courses ) && $preview ) {
		$course = $courses[0];
	} else {
		$first_published_course = ! empty( $courses[0] ) ? $courses[0] : null;
		$courses                = tva_get_courses( array( 'private' => true, 'without_data' => true, 'page' => 1, 'per_page' => 1 ) );

		$course = ! empty( $courses[0] ) ? $courses[0] : $first_published_course;
	}

	return $course !== null ? get_term_link( $courses[0]->term_id, TVA_Const::COURSE_TAXONOMY ) : false;
}

/**
 * Get preview option as a bool
 *
 * @return bool
 */
function tva_get_preview_option() {
	$preview = get_option( 'tva_preview_option', true );

	if ( ! is_bool( $preview ) ) {
		$preview = $preview !== 'false';
	}

	return $preview;
}

/**
 * Get the pagination
 *
 * @param $arguments
 *
 * @return WP_Term_Query
 */
function tva_get_courses_pagination_query( $arguments ) {
	$args       = tva_get_courses_args( $arguments );
	$term_query = new WP_Term_Query( $args );

	return $term_query;
}

/**
 * Course pagination
 *
 * @param array $args
 * @param int   $current_page
 *
 * @return string
 */
function tva_get_paginated_numbers( $args = array(), $current_page = 1 ) {

	$current_page   = (int) $current_page;
	$paginated_text = '';
	$page_numbers   = array();
	//Set defaults to use
	$defaults = array(
		'query'              => $GLOBALS['wp_query'],
		'previous_page_text' => __( '&laquo;', 'thrive-apprentice' ),
		'next_page_text'     => __( '&raquo;', 'thrive-apprentice' ),
		'first_page_text'    => __( 'First', 'thrive-apprentice' ),
		'last_page_text'     => __( 'Last', 'thrive-apprentice' ),
		'next_link_text'     => __( 'Older Entries', 'thrive-apprentice' ),
		'previous_link_text' => __( 'Newer Entries', 'thrive-apprentice' ),
		'show_posts_links'   => false,
		'range'              => 5,
	);

	// Merge default arguments with user set arguments
	$args = wp_parse_args( $args, $defaults );

	// Get the amount of pages from the query
	if ( is_object( $args['query'] ) && null !== $args['query']->terms ) {
		$max_pages = (int) ceil( count( $args['query']->terms ) / (int) tva_get_setting( 'per_page' ) );
	} else {
		$max_pages = 0;
	}

	/**
	 * If $args['show_posts_links'] is set to false, numbered paginated links are returned
	 * If $args['show_posts_links'] is set to true, pagination links are returned
	 */
	if ( false === $args['show_posts_links'] ) {

		// Don't display links if only one page exists
		if ( $max_pages <= 1 ) {
			$paginated_text = '';
		} else {
			/**
			 * For multi-paged queries, we need to set the variable ranges which will be used to check
			 * the current page against and according to that set the correct output for the paginated numbers
			 */
			$mid_range   = (int) floor( $args['range'] / 2 );
			$start_range = range( 1, $mid_range );
			$end_range   = range( ( $max_pages - $mid_range + 1 ), $max_pages );
			$exclude     = array_merge( $start_range, $end_range );

			/**
			 * The amount of pages must now be checked against $args['range']. If the total amount of pages
			 * is less than $args['range'], the numbered links must be returned as is
			 *
			 * If the total amount of pages is more than $args['range'], then we need to calculate the offset
			 * to just return the amount of page numbers specified in $args['range']. This defaults to 5, so at any
			 * given instance, there will be 5 page numbers displayed
			 */
			$check_range   = ( $args['range'] > $max_pages ) ? true : false;
			$range_numbers = array();

			if ( true === $check_range ) {
				$range_numbers = range( 1, $max_pages );
			} elseif ( false === $check_range ) {
				if ( ! in_array( $current_page, $exclude ) ) {
					$range_numbers = range( ( $current_page - $mid_range ), ( $current_page + $mid_range ) );
				} elseif ( in_array( $current_page, $start_range ) && ( $current_page - $mid_range ) <= 0 ) {
					$range_numbers = range( 1, $args['range'] );
				} elseif ( in_array( $current_page, $end_range ) && ( $current_page + $mid_range ) >= $max_pages ) {
					$range_numbers = range( ( $max_pages - $args['range'] + 1 ), $max_pages );
				}
			}

			/**
			 * The page numbers are set into an array through this foreach loop. The current page, or active page
			 * gets the class 'current' assigned to it. All the other pages get the class 'inactive' assigned to it
			 */
			foreach ( $range_numbers as $v ) {
				if ( $v == $current_page ) {
					$page_numbers[] = '<span class="page-numbers current">' . $v . '</span>';
				} else {
					$page_numbers[] = '<a data-tva-page-nr="' . $v . '" href="javascript:void(0)" class="tva-page-numbers page-numbers inactive">' . $v . '</a>';
				}
			}

			/**
			 * All the texts are set here and when they should be displayed which will link back to:
			 * - $previous_page The previous page from the current active page
			 * - $next_page The next page from the current active page
			 * - $first_page Links back to page number 1
			 * - $last_page Links to the last page
			 */
			$previous_page = ( $current_page !== 1 ) ? '<a class="tva-page-numbers page-numbers" data-tva-page-nr="' . ( $current_page - 1 ) . '" href="javascript:void(0)">' . $args['previous_page_text'] . '</a>' : '';
			$next_page     = ( $current_page !== $max_pages ) ? '<a class="tva-page-numbers page-numbers" data-tva-page-nr="' . ( $current_page + 1 ) . '" href="javascript:void(0)">' . $args['next_page_text'] . '</a>' : '';
			$first_page    = ( ! in_array( 1, $range_numbers ) ) ? '<a class="tva-page-numbers page-numbers" data-tva-page-nr="1" href="javascript:void(0)">' . $args['first_page_text'] . '</a>' : '';
			$last_page     = ( ! in_array( $max_pages, $range_numbers ) ) ? '<a class="tva-page-numbers page-numbers" data-tva-page-nr="' . $max_pages . '" href="javascript:void(0)">' . $args['last_page_text'] . '</a>' : '';
			/**
			 * Text to display before the page numbers
			 * This is set to the following structure:
			 * - Page X of Y
			 */
			$page_text = '<span class="tva-pagination-overview">' . sprintf( __( 'Page %s of %s' ), $current_page, $max_pages ) . '</span>';
			// Turn the array of page numbers into a string
			$numbers_string    = implode( ' ', $page_numbers );
			$numbers_beginning = '<div class="tva-pagination-links">';
			$numbers_ending    = '</div>';
			// The final output of the function
			$paginated_text = '<div class="tva-pagination">';
			$paginated_text .= $page_text . $numbers_beginning . $first_page . $previous_page . $numbers_string . $next_page . $last_page . $numbers_ending;
			$paginated_text .= '</div>';

		}
	} elseif ( true === $args['show_posts_links'] ) {
		/**
		 * If $args['show_posts_links'] is set to true, only links to the previous and next pages are displayed
		 * The $max_pages parameter is already set by the function to accommodate custom queries
		 */
		$paginated_text = next_posts_link( '<div class="tva-next-page-link">' . $args['next_link_text'] . '</div>', $max_pages );
		$paginated_text .= previous_posts_link( '<div class="tva-previous-page-link next">' . $args['previous_link_text'] . '</div>' );

	}

	return $paginated_text;

}

/**
 * adds an icon and link to the admin bar for quick access to the editor. Only shows when not already in Thrive Architect
 *
 * @param WP_Admin_Bar $wp_admin_bar
 */
function tva_admin_bar( $wp_admin_bar ) {
	$theme = wp_get_theme();
	// SUPP-1408 Hive theme leaves the query object in an unknown state
	if ( 'Hive' === $theme->name || 'Hive' === $theme->parent_theme ) {
		wp_reset_query();
	}
	$post_id = get_the_ID();
	$args    = array();

	if ( isset( $_REQUEST[ TVA_Const::TCB_EDITOR ] ) && ( get_post_type() === TVA_Const::LESSON_POST_TYPE || TVA_Const::tva_is_checkout_page() ) ) {
		$close_editor_link = tva_get_editor_close_url( $post_id );
		$args              = array(
			'id'    => 'tve_button',
			'title' => '<span class="thrive-adminbar-icon"></span>' . __( 'Close Thrive Architect', 'thrive-apprentice' ),
			'href'  => $close_editor_link,
			'meta'  => array(
				'class' => 'thrive-admin-bar',
			),
		);
		$wp_admin_bar->add_node( $args );
	}

	$obj = get_queried_object();

	$is_index_page = tva_get_settings_manager()->is_index_page();

	if ( current_user_can( 'edit_posts' ) && tva_is_apprentice() ) {
		$obj            = get_queried_object();
		$dashboard_link = get_admin_url() . 'admin.php?page=thrive_apprentice';

		if ( is_tax( TVA_Const::COURSE_TAXONOMY ) ) {
			$id   = isset( $obj->term_id ) ? $obj->term_id : $obj->ID;
			$link = $dashboard_link . '#courses/' . $id;

			$args = array(
				'id'    => 'tve_edit_course_button',
				'title' => '<span class="thrive-adminbar-edit-course-icon"></span>' . __( 'Edit Course', 'thrive-apprentice' ),
				'href'  => $link,
				'meta'  => array(
					'class' => 'thrive-admin-bar',
				),
			);

		} elseif ( is_single() && $obj->post_type === TVA_Const::MODULE_POST_TYPE ) {
			$id    = isset( $obj->term_id ) ? $obj->term_id : $obj->ID;
			$terms = wp_get_post_terms( $id, TVA_Const::COURSE_TAXONOMY );
			$link  = $dashboard_link . '#courses/' . $terms[0]->term_id;

			$args = array(
				'id'    => 'tve_edit_course_button',
				'title' => '<span class="thrive-adminbar-edit-course-icon"></span>' . __( 'Edit Course', 'thrive-apprentice' ),
				'href'  => $link,
				'meta'  => array(
					'class' => 'thrive-admin-bar',
				),
			);

		} elseif ( is_page() && $is_index_page ) {
			$args = array(
				'id'    => 'tve_edit_courses_button',
				'title' => '<span class="thrive-adminbar-edit-course-icon"></span>' . __( 'Edit Courses', 'thrive-apprentice' ),
				'href'  => $dashboard_link . '#courses',
				'meta'  => array(
					'class' => 'thrive-admin-bar',
				),
			);
		}

		$wp_admin_bar->add_node( $args );
	}

	if ( ( is_page() && $is_index_page && ! isset( $_REQUEST[ TVA_Const::TCB_EDITOR ] ) ) || ( is_single() && $obj->post_type === TVA_Const::MODULE_POST_TYPE ) ) {
		$wp_admin_bar->remove_node( 'tve_button' );
	}

	if ( is_tax( TVA_Const::COURSE_TAXONOMY ) ) {
		$wp_admin_bar->remove_node( 'edit' );
	}
}

/**
 * Checks if there is a valid activated license for the plugin
 *
 * @return bool
 */
function tva_license_activated() {
	return TVE_Dash_Product_LicenseManager::getInstance()->itemActivated( TVE_Dash_Product_LicenseManager::TVA_TAG );
}

/**
 * Register the Thrive Apprentice Menu
 */
function tva_register_menu() {
	register_nav_menu( 'tva_apprentice_menu', __( 'Thrive Apprentice Menu (Legacy design only!)' ) );
}

/**
 * Gets the progress of our user in percentage
 *
 * @param $course
 *
 * @return float|int
 */
function tva_get_user_progress( $course ) {
	$progress = 0;

	$lessons_learned = tva_get_learned_lessons();

	/**
	 * Count the progress (this includes the lesson which is in progress right now)
	 */
	if ( isset( $lessons_learned[ $course->term_id ] ) ) {
		$done     = count( $lessons_learned[ $course->ID ] );
		$count    = count( $course->lessons );
		$progress = $count == 0 ? 0 : $done / $count * 100;
	}

	return $progress;
}

/**
 * Returns an array of courses and lessons that the user has seen
 * IMPORTANT NOTE: this will also include deleted lessons(already learned)
 *
 * @return array|mixed|object|string
 */
function tva_get_learned_lessons() {
	if ( is_user_logged_in() ) {
		$user_id         = get_current_user_id();
		$lessons         = get_user_meta( $user_id, 'tva_learned_lessons', true );
		$lessons_learned = $lessons ? $lessons : array();
	} else {
		$lessons_learned = isset( $_COOKIE['tva_learned_lessons'] ) ? $_COOKIE['tva_learned_lessons'] : array();
		if ( ! is_array( $lessons_learned ) ) {
			$lessons_learned = stripslashes( $lessons_learned );
			$lessons_learned = json_decode( $lessons_learned, JSON_OBJECT_AS_ARRAY );
		}
	}

	return $lessons_learned;
}

/**
 * An array of submitted assessments(event deleted ones)
 *
 * @return array
 */
function tva_get_submitted_assessments() {
	$submitted_assessments = [];
	if ( is_user_logged_in() ) {
		$user_id               = get_current_user_id();
		$submitted_assessments = get_user_meta( $user_id, TVA_User_Assessment::SUBMITTED_CACHE_KEY, true );
		$submitted_assessments = is_array( $submitted_assessments ) ? $submitted_assessments : [];
	}

	return $submitted_assessments;
}

/**
 * Change post type query to join the post meta too
 *
 * @param $join
 * @param $in_same_term
 * @param $excluded_terms
 * @param $taxonomy
 * @param $post
 *
 * @return string
 */
function tva_post_join( $join, $in_same_term, $excluded_terms, $taxonomy, $post ) {

	if ( $post->post_type == TVA_Const::LESSON_POST_TYPE ) {
		global $wpdb;

		$join .= ' INNER JOIN ' . $wpdb->postmeta . ' AS pm ON pm.post_id = p.ID';
	}

	return $join;
}

/**
 * Change the post type query to get posts by menu_order instead of post date
 *
 * @param $where
 * @param $in_same_term
 * @param $excluded_terms
 * @param $taxonomy
 * @param $post
 *
 * @return mixed
 */
function tva_get_where_post_type_adjacent_post( $where, $in_same_term, $excluded_terms, $taxonomy, $post ) {

	if ( $post->post_type == TVA_Const::LESSON_POST_TYPE ) {
		$adjacent = strpos( $where, '>' ) !== false ? '>' : '<';
		$search   = '/[^WHERE ](.*)(?=AND p.post_type)/';

		$order   = get_post_meta( $post->ID, 'tva_lesson_order', true );
		$replace = " pm.meta_key = 'tva_lesson_order' AND pm.meta_value " . $adjacent . ' ' . $order . ' ';

		$where = preg_replace( $search, $replace, $where );
	}

	return $where;
}

/**
 * Change the post type query to get posts by menu_order instead of post date
 *
 * @param $orderby
 *
 * @return string
 */
function tva_get_next_sort_post_type_adjacent_post( $orderby, $post ) {
	if ( $post->post_type == TVA_Const::LESSON_POST_TYPE ) {
		$orderby = 'ORDER BY cast(pm.meta_value as SIGNED)  ASC LIMIT 1';
	}

	return $orderby;
}

/**
 * Change the post type query to get posts by menu_order instead of post date
 *
 * @param $orderby
 *
 * @return string
 */
function tva_get_prev_sort_post_type_adjacent_post( $orderby, $post ) {
	if ( $post->post_type == TVA_Const::LESSON_POST_TYPE ) {
		$orderby = 'ORDER BY cast(pm.meta_value as SIGNED) DESC LIMIT 1';
	}

	return $orderby;
}

/**
 * Hook into the wordpress registration from TD in order to auto-login the user if
 * auto login option is on in TA General Settings
 *
 * @param WP_User $user
 * @param array   $arguments
 */
function tva_perform_auto_login( $user, $arguments = array() ) {

	if ( class_exists( 'MM_User' ) ) {
		$mm_user = new MM_User( $user->data->ID );
		$mm_user->commitData();
	}

	$login = ( int ) tva_get_setting( 'auto_login' ) === 1;
	$login = $login && ! empty( $arguments['password'] );
	$login = $login && true === $user instanceof WP_User;

	if ( $login ) {
		$credentials                  = array();
		$credentials['user_login']    = $user->user_login;
		$credentials['user_password'] = $arguments['password'];
		$credentials['remember']      = true;

		wp_signon( $credentials, false );
	} else if ( isset( $_COOKIE['tva_lesson_to_redirect'] ) ) {
		wp_redirect( $_COOKIE['tva_lesson_to_redirect'] );
	}
}

/**
 * Return the wp_login_form with our settings
 *
 * @param $course
 *
 * @return string
 */
function tva_login_form() {
	// Login form arguments.
	$args = array(
		'echo'           => false,
		'redirect'       => tva_access_manager()->get_login_redirect_url(),
		'form_id'        => 'loginform',
		'label_username' => __( 'Username' ),
		'label_password' => __( 'Password' ),
		'label_remember' => __( 'Remember Me' ),
		'label_log_in'   => __( 'Log In' ),
		'id_username'    => 'user_login',
		'id_password'    => 'user_pass',
		'id_remember'    => 'rememberme',
		'id_submit'      => 'tva_main_color_bg',
		'remember'       => true,
		'value_username' => null,
		'value_remember' => true,
	);

	// Add Thrive Apprentice register page
	add_filter( 'login_form_bottom', 'tva_get_register_page' );

	// Calling the login form.
	return wp_login_form( $args );
}

/**
 * Sendowl API connection check
 */
function tva_check_sendowl() {
	$memberships = array();
	/**
	 * Check for SendOwl API connection
	 */
	if ( class_exists( 'Thrive_Dash_List_Manager', false ) ) {

		if ( TVA_SendOwl::is_connected() ) {
			$memberships[] = TVA_SendOwl::get_memberships();
		}
	}

	return $memberships;
}

/**
 * Check if we're on a course page
 *
 * @return bool
 */
function tva_is_course_page() {
	if ( is_tax( TVA_Const::COURSE_TAXONOMY ) ) {
		return true;
	}

	return false;
}

/**
 * @param $commment_data
 * Process comment data, here we add our meta key
 *
 * @return mixed
 */
function tva_process_comment_data( $commment_data ) {
	$hidden_post_id = get_option( 'tva_course_hidden_post_id', 0 );

	if ( empty( $hidden_post_id ) || (int) $commment_data['comment_post_ID'] !== (int) $hidden_post_id ) {
		return $commment_data;
	}

	if ( ! empty( $_POST['comment_term_ID'] ) && empty( $commment_data['comment_term_ID'] ) ) {
		$commment_data['comment_term_ID'] = (int) $_POST['comment_term_ID'];
	}

	if ( ( $commment_data['comment_post_ID'] == $hidden_post_id ) && ! isset( $commment_data['comment_term_ID'] ) && ( $commment_data['comment_parent'] > 0 ) ) {
		$term_id                       = get_comment_meta( $commment_data['comment_parent'], 'tva_course_comment_term_id', true );
		$commment_data['comment_meta'] = array(
			'tva_course_comment_term_id' => $term_id,
		);
	}

	if ( ( $commment_data['comment_post_ID'] == $hidden_post_id ) && ( isset( $commment_data['comment_term_ID'] ) ) ) {
		$commment_data['comment_meta'] = array(
			'tva_course_comment_term_id' => $commment_data['comment_term_ID'],
		);
	}

	return $commment_data;
}

/**
 * Create a fake instance of WP_post to bound comment on it
 *
 * @return WP_Post|null
 */
function tva_get_hidden_post() {
	$hidden_post = get_posts( array( 'post_type' => TVA_Const::COURSE_POST_TYPE ) );

	if ( ! isset( $hidden_post[0] ) ) {
		$hidden_post_args = array(
			'post_title'     => '',
			'post_type'      => TVA_Const::COURSE_POST_TYPE,
			'post_excerpt'   => '',
			'post_status'    => 'publish',
			'comment_status' => 'open',
		);

		if ( ! is_wp_error( wp_insert_post( $hidden_post_args ) ) ) {
			$hidden_post = get_posts( array( 'post_type' => TVA_Const::COURSE_POST_TYPE ) );
			if ( ! empty( $hidden_post ) ) {
				add_option( 'tva_course_hidden_post_id', $hidden_post[0]->ID );
			}
		}
	}

	return isset( $hidden_post[0] ) ? $hidden_post[0] : null;
}

/**
 * Handle comments template
 *
 * @param $template
 *
 * @return string
 */
function tva_handle_comments_template( $template ) {
	global $post;

	if ( ! tva_is_apprentice() ) {
		return $template;
	}

	/**
	 * Thrive comments get respect!!!
	 */
	if ( ( $post->comment_status === 'open' ) && ( class_exists( 'Thrive_Comments' ) ) ) {

		/**
		 * Check if the plugin is active sitewide
		 */
		$tc_comments_status = tcms()->tcm_get_setting_by_name( 'activate_comments' );

		if ( $tc_comments_status === false ) {
			return TVA_Const::plugin_path( 'templates/comments.php' );
		}

		return $template;
	}

	/**
	 * Load Disqus comment template
	 */
	if ( class_exists( TVA_Const::DISQUS_REF_CLASS ) && tva_is_apprentice() ) {

		/**
		 * Disqus loads its comment template even if post comment status is closed, so we prevent that here
		 */
		if ( is_single() && $post->comment_status == 'closed' ) {
			return TVA_Const::plugin_path( 'templates/comments.php' );
		}

		return ABSPATH . 'wp-content/plugins/disqus-comment-system/comments.php';
	}

	/**
	 * Load our comment template
	 */
	if ( tva_is_apprentice() ) {
		return TVA_Const::plugin_path( 'templates/comments.php' );
	}

	return $template;
}

/**
 * Overwrite $post global
 */
function tva_overwrite_post() {
	if ( tva_is_course_page() && ! class_exists( TVA_Const::DISQUS_REF_CLASS ) ) {
		global $post;
		$post = tva_get_hidden_post();
	}
}

/**
 * Count comments for the course in wp-admin
 *
 * @return array|int
 */
function tva_count_course_comments( $count ) {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : false;
	if ( isset( $GLOBALS['comment'] ) && $screen !== false && is_object( $screen ) && $screen->base == 'edit-comments' ) {
		$hidden_post_id = get_option( 'tva_course_hidden_post_id' );
		if ( $GLOBALS['comment']->comment_post_ID == $hidden_post_id ) {
			$comment_term_id = get_comment_meta( $GLOBALS['comment']->comment_ID, 'tva_course_comment_term_id', true );
			$args            = array(
				'post_id'    => $hidden_post_id,
				'meta_value' => $comment_term_id,
				'count'      => true,
			);

			$comment_query = new WP_Comment_Query();
			$count         = $comment_query->query( $args );
		}
	}

	return $count;
}

/**
 * Pass term data to disqus when a comment is posted
 */
function tva_add_course_to_disqus() {
	if ( tva_is_course_page() ) {
		$obj = get_queried_object();
		?>

		<script type="text/javascript">
			disqus_url = "<?php echo get_term_link( $obj->term_id, TVA_Const::COURSE_TAXONOMY ); ?>";
			disqus_identifier = <?php echo $obj->term_id ?>;
			disqus_title = "<?php echo $obj->name; ?>";
		</script>
		<?php
	}
}

/**
 * @param $notify_message
 * @param $comment_id
 * Modify moderation email text for Thrive Apprentice courses
 *
 * @return string
 */
function tva_comment_moderation_text( $notify_message, $comment_id ) {
	$comment_term_id = get_comment_meta( $comment_id, 'tva_course_comment_term_id', true );
	$comment         = get_comment( $comment_id );

	if ( $comment_term_id ) {
		global $wpdb;
		$comment_author_domain = @gethostbyaddr( $comment->comment_author_IP );
		$comment_content       = wp_specialchars_decode( $comment->comment_content );
		$comment_term          = get_term( $comment_term_id );
		$comments_waiting      = $wpdb->get_var( "SELECT count(comment_ID) FROM $wpdb->comments WHERE comment_approved = '0'" );
		$notify_message        = sprintf( __( 'A new comment on the course "%s" is waiting for your approval' ), $comment_term->name ) . "\r\n";
		$notify_message        .= get_term_link( $comment_term, TVA_Const::COURSE_TAXONOMY ) . "\r\n\r\n";
		$notify_message        .= sprintf( __( 'Author: %1$s (IP address: %2$s, %3$s)' ), $comment->comment_author, $comment->comment_author_IP, $comment_author_domain ) . "\r\n";
		$notify_message        .= sprintf( __( 'Email: %s' ), $comment->comment_author_email ) . "\r\n";
		$notify_message        .= sprintf( __( 'URL: %s' ), $comment->comment_author_url ) . "\r\n";
		$notify_message        .= sprintf( __( 'Comment: %s' ), "\r\n" . $comment_content ) . "\r\n\r\n";
		$notify_message        .= sprintf( __( 'Approve it: %s' ), admin_url( "comment.php?action=approve&c={$comment_id}#wpbody-content" ) ) . "\r\n";

		if ( EMPTY_TRASH_DAYS ) {
			/* translators: Comment moderation. 1: Comment action URL */
			$notify_message .= sprintf( __( 'Trash it: %s' ), admin_url( "comment.php?action=trash&c={$comment_id}#wpbody-content" ) ) . "\r\n";
		} else {
			/* translators: Comment moderation. 1: Comment action URL */
			$notify_message .= sprintf( __( 'Delete it: %s' ), admin_url( "comment.php?action=delete&c={$comment_id}#wpbody-content" ) ) . "\r\n";
		}

		/* translators: Comment moderation. 1: Comment action URL */
		$notify_message .= sprintf( __( 'Spam it: %s' ), admin_url( "comment.php?action=spam&c={$comment_id}#wpbody-content" ) ) . "\r\n";

		/* translators: Comment moderation. 1: Number of comments awaiting approval */
		$notify_message .= sprintf( _n( 'Currently %s comment is waiting for approval. Please visit the moderation panel:',
				'Currently %s comments are waiting for approval. Please visit the moderation panel:', $comments_waiting ), number_format_i18n( $comments_waiting ) ) . "\r\n";
		$notify_message .= admin_url( "edit-comments.php?comment_status=moderated#wpbody-content" ) . "\r\n";
	}

	return $notify_message;
}

/**
 * @param $subject
 * @param $comment_id
 *  * Modify moderation email header for Thrive Apprentice courses
 *
 * @return string
 */
function tva_comment_moderation_subject( $subject, $comment_id ) {
	$comment_term_id = get_comment_meta( $comment_id, 'tva_course_comment_term_id', true );

	if ( $comment_term_id ) {
		$comment_term = get_term( $comment_term_id );
		$blogname     = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
		$subject      = sprintf( __( '[%1$s] Please moderate: "%2$s"' ), $blogname, $comment_term->name );
	}

	return $subject;
}

/**
 * Load facebook sdk on apprentice pages
 */
function tva_load_fb_sdk() {
	global $is_thrive_theme;

	if ( $is_thrive_theme && ( $is_thrive_theme === true ) && tva_is_apprentice() ) {

		$obj            = get_queried_object();
		$comment_status = $obj->taxonomy
			?
			get_term_meta( $obj->term_id, 'tva_comment_status', true )
			:
			$obj->comment_status;

		if ( $comment_status == 'open' ) {
			$enable_fb_comments = thrive_get_theme_options( "enable_fb_comments" );
			$fb_app_id          = thrive_get_theme_options( "fb_app_id" );

			if ( ( ( $enable_fb_comments == "only_fb"
					 || $enable_fb_comments == "both_fb_regular"
					 || ( ! comments_open() && $enable_fb_comments == "fb_when_disabled" ) )
				   && ! empty( $fb_app_id ) )
			) {

				?>
				<div id="fb-root"></div>
				<script>( function ( d, s, id ) {
						var js, fjs = d.getElementsByTagName( s )[ 0 ];
						if ( d.getElementById( id ) ) {
							return;
						}
						js = d.createElement( s );
						js.id = id;
						js.src = 'https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.11&appId="<?php echo $fb_app_id ?>"';
						fjs.parentNode.insertBefore( js, fjs );
					}( document, 'script', 'facebook-jssdk' ) );</script>
				<?php
			}
		}
	}
}

/**
 *  Add facebook html for comments on apprentice pages
 */
function tva_load_fb_comment_html() {
	global $is_thrive_theme, $post;

	if ( tva_is_apprentice() && $is_thrive_theme ) {
		$obj                = get_queried_object();
		$url                = $obj->taxonomy ? get_term_link( $obj, TVA_Const::COURSE_TAXONOMY ) : get_permalink( $post->ID );
		$fb_app_id          = thrive_get_theme_options( "fb_app_id" );
		$enable_fb_comments = thrive_get_theme_options( "enable_fb_comments" );

		if ( $enable_fb_comments == "only_fb"
			 || $enable_fb_comments == "both_fb_regular"
			 || ( ! comments_open() && $enable_fb_comments == "fb_when_disabled" ) && ! empty( $fb_app_id )
		) {
			$html = '<article id="comments_fb" style="min-height: 100px; border: 1px solid #ccc;">';
			$html .= '<div class="fb-comments" data-href="' . $url . '"';
			$html .= 'data-numposts="' . thrive_get_theme_options( "fb_no_comments" ) . '" data-width="100%"';
			$html .= 'data-colorscheme="' . thrive_get_theme_options( "fb_color_scheme" ) . '"></div>';
			$html .= ' </article>';
			echo $html;
		}
	}
}

/**
 * @param $link
 * @param $comment
 * Get course permalink by a given comment
 *
 * @return string|WP_Error
 */
function tva_on_comment_course_permalink( $permalink, $post, $leavename ) {

	$hidden_post_id  = get_option( 'tva_course_hidden_post_id' );
	$hidden_post_obj = get_post( $hidden_post_id );

	if ( ( $hidden_post_obj instanceof WP_Post )
		 && ( $post->ID == $hidden_post_id )
		 && ( $hidden_post_obj->post_type == TVA_Const::COURSE_POST_TYPE )
	) {
		if ( isset( $GLOBALS['comment'] ) ) {
			$comment_term_id = get_comment_meta( $GLOBALS['comment']->comment_ID, 'tva_course_comment_term_id', true );
			$term            = get_term( $comment_term_id, TVA_Const::COURSE_TAXONOMY );
			$permalink       = $term !== null && ! is_wp_error( $term ) ? get_term_link( $term, TVA_Const::COURSE_TAXONOMY ) : '';
		}
	}

	return $permalink;
}

/**
 * @param $link
 * @param $comment
 * Get course url by a given comment
 *
 * @return string|WP_Error
 */
function tva_on_comment_course_url( $link, $post_id = null ) {
	$hidden_post_id  = get_option( 'tva_course_hidden_post_id' );
	$hidden_post_obj = get_post( $hidden_post_id );

	if ( ( $hidden_post_obj instanceof WP_Post )
		 && ( $post_id == $hidden_post_id )
		 && ( $hidden_post_obj->post_type == TVA_Const::COURSE_POST_TYPE )
	) {
		if ( isset( $GLOBALS['comment'] ) ) {
			$comment_term_id = get_comment_meta( $GLOBALS['comment']->comment_ID, 'tva_course_comment_term_id', true );
			$term            = get_term( $comment_term_id, TVA_Const::COURSE_TAXONOMY );
			$link            = $term !== null && ! is_wp_error( $term ) ? get_term_link( $term, TVA_Const::COURSE_TAXONOMY ) : '';
		}
	}

	return $link;
}

/**
 * @param $title
 * @param $post_id
 * Get course title by a given comment
 *
 * @return string
 */
function tva_on_comment_course_title( $title, $post_id = null ) {
	$screen          = function_exists( 'get_current_screen' ) ? get_current_screen() : false;
	$hidden_post_id  = get_option( 'tva_course_hidden_post_id' );
	$hidden_post_obj = get_post( $hidden_post_id );

	if ( ( $hidden_post_obj instanceof WP_Post )
		 && ( $post_id == $hidden_post_id )
		 && ( $hidden_post_obj->post_type == TVA_Const::COURSE_POST_TYPE )
	) {
		if ( isset( $GLOBALS['comment'] ) ) {
			$comment_term_id = get_comment_meta( $GLOBALS['comment']->comment_ID, 'tva_course_comment_term_id', true );
			$term            = get_term( $comment_term_id, TVA_Const::COURSE_TAXONOMY );
			$title           = $term !== null && ! is_wp_error( $term ) ? $term->name : '';
		} else if ( isset( $GLOBALS['wp_list_table'] ) && $screen !== false && $screen->base = 'edit-comments' ) {
			$title = __( 'Thrive Apprentice courses' );
		}
	}

	return $title;
}

/**
 * @param $link
 * @param $comment
 * Get course url by a given comment
 *
 * @return string|WP_Error
 */
function tva_get_term_url_by_comment( $link, $comment ) {

	$hidden_post_id  = get_option( 'tva_course_hidden_post_id' );
	$hidden_post_obj = get_post( $hidden_post_id );

	if ( ( $hidden_post_obj instanceof WP_Post )
		 && ( $comment->comment_post_ID == $hidden_post_id )
		 && ( $hidden_post_obj->post_type == TVA_Const::COURSE_POST_TYPE )
	) {
		$comment_term_id = get_comment_meta( $comment->comment_ID, 'tva_course_comment_term_id', true );
		$term            = get_term( $comment_term_id, TVA_Const::COURSE_TAXONOMY );
		$link            = $term !== null && ! is_wp_error( $term ) ? get_term_link( $term, TVA_Const::COURSE_TAXONOMY ) : '';
	}

	return $link;
}

/**
 * Make sure that comments are allowed on course pages
 *
 * @param $open
 *
 * @return bool
 */
function tva_ensure_comments_open( $open ) {
	$obj = get_queried_object();

	if ( $obj && isset( $obj->term_id ) && ! is_admin() ) {
		$comment_status = get_term_meta( $obj->term_id, 'tva_comment_status', true );

		if ( ( tva_is_apprentice() ) && ( $comment_status == 'open' ) ) {
			$open = true;
		}
	}

	return $open;
}

/**
 * Load tc comments template
 */
function tva_load_tc_template( $show_comments ) {
	if ( tva_is_apprentice() ) {
		$show_comments = true;
	}

	$obj = get_queried_object();

	if ( tva_is_apprentice() && is_single() && $obj->comment_status == 'closed' ) {
		$show_comments = false;
	}

	if ( tva_is_apprentice() && TVA\TTB\Check::apprentice_skin( thrive_template() ) ) {
		$show_comments = true;
	}

	return $show_comments;
}

/**
 * Added for compatibility with TC
 * Send current course id in frontend at page first load
 *
 * @param $localization
 *
 * @return mixed
 */

function tva_tcm_comments_localization( $localization ) {
	if ( tva_is_course_page() ) {
		$obj  = get_queried_object();
		$post = tva_get_hidden_post();

		$post->subscriber_list    = get_term_meta( $obj->term_id, 'tva_term_subscribers', true );
		$localization['post']     = $post;
		$localization['tva_term'] = $obj->term_id;
	}

	return $localization;
}

/**
 * Added for compatibility with TC
 * Build the query to retrieve comments in frontend on course pages
 *
 * @param                 $query
 * @param WP_REST_Request $request
 *
 * @return mixed
 */
function tva_tcm_get_comments( $query, $request ) {
	$term_id = $request->get_param( 'tva_term' );

	if ( $term_id !== null ) {
		$query['meta_key']   = 'tva_course_comment_term_id';
		$query['meta_value'] = $term_id;
	}

	return $query;
}

/**
 * Added for compatibility with TC
 * Add comment meta for comments added on courses in frontend
 *
 * @param                 $comment_fields
 * @param WP_REST_Request $request
 *
 * @return mixed
 */
function tva_tcm_comments_fields( $comment_fields, $request ) {
	$term_id = $request->get_param( 'tva_term' );

	if ( $term_id !== null && $term_id !== false ) {
		$comment_fields['comment_meta'] = array(
			'tva_course_comment_term_id' => $term_id,
		);
	}

	return $comment_fields;
}

/**
 * Added for compatibility with TC
 * Section: comment moderation: Get course data in wp-admin
 *
 * @param $post
 * @param $comment_data
 *
 * @return array|null|WP_Error|WP_Term
 */

function tva_tcm_get_course_for_comment( $post, $comment_data ) {
	$hidden_post_id = get_option( 'tva_course_hidden_post_id' );
	if ( $hidden_post_id == $comment_data['post'] ) {
		$term_id = get_comment_meta( $comment_data['id'], 'tva_course_comment_term_id', true );
		if ( $term_id && ! is_wp_error( $term_id ) ) {
			$comment_term = get_term( $term_id, TVA_Const::COURSE_TAXONOMY );
			$post         = $comment_term;
			$term_author  = get_term_meta( $term_id, 'tva_author', true );
			$author       = get_user_by( 'ID', $term_author['ID'] );
			$term_url     = get_term_link( $comment_term, TVA_Const::COURSE_TAXONOMY );

			$post->user_display_name = ( $author ) ? $author->user_nicename : '';
			$post->post_title        = $comment_term->name;
			$post->edit_link         = get_admin_url() . '/admin.php?page=thrive_apprentice#courses/' . $term_id;
			$post->guid              = $term_url;
			$post->ID                = $hidden_post_id;
		}
	}

	return $post;
}

/**
 * Added for compatibility with TC
 * Section: comment moderation: Add courses to tc autocomplete list
 *
 * @param                 $json
 * @param WP_REST_Request $request
 *
 * @return array
 */
function tva_tcm_posts_autocomplete( $json, $request ) {
	$q     = $request->get_param( 'q' );
	$args  = array(
		'taxonomy'   => TVA_Const::COURSE_TAXONOMY,
		'name__like' => $q,
	);
	$terms = get_terms( $args );

	foreach ( $terms as $term ) {
		$json[] = array(
			'id'       => $term->term_id,
			'label'    => $term->name,
			'value'    => $term->name,
			'tva_term' => true,
		);
	}

	return $json;
}

/**
 * Added for compatibility with TC
 * Section: comments moderation: Build the query to retrieve courses comments
 *
 * @param                 $args
 * @param WP_REST_Request $request
 *
 * @return mixed
 */
function tva_rest_comment_query( $args, $request ) {
	$post_id = $request->get_param( 'post_id' );

	//when the search input is reset
	if ( $post_id === 0 ) {
		return $args;
	}

	$hidden_post_id = get_option( 'tva_course_hidden_post_id' );
	$tva_term       = $request->get_param( 'tva_term' );

	if ( ( $hidden_post_id === $post_id ) && is_numeric( $tva_term ) && is_tva_term( $tva_term ) ) {
		$args['post_id']    = $hidden_post_id;
		$args['meta_key']   = 'tva_course_comment_term_id';
		$args['meta_value'] = $tva_term;
	}

	if ( is_tva_term( $post_id ) && $tva_term === 'true' ) {
		$args['post_id']    = $hidden_post_id;
		$args['meta_key']   = 'tva_course_comment_term_id';
		$args['meta_value'] = $post_id;
	}

	return $args;
}

/**
 * Added for compatibility with TC
 * Section: comments moderation: Catch any reply to a comment which belong to a course and add the required data
 *
 * @param                 $prepared_comment
 * @param WP_REST_Request $request
 *
 * @return mixed
 */
function tva_rest_preprocess_comment( $prepared_comment, $request ) {
	$tva_term_id = $request->get_param( 'tva_term_id' );

	if ( is_tva_term( $tva_term_id ) ) {
		$prepared_comment['comment_post_ID'] = get_option( 'tva_course_hidden_post_id' );
		$prepared_comment['comment_meta']    = array(
			'tva_course_comment_term_id' => $tva_term_id,
		);
	}

	return $prepared_comment;
}

/**
 * Added for compatibility with TC
 * Catch any comments added for courses and append course id to the data sent in frontend
 *
 * @param WP_REST_Response $response
 *
 * @return mixed
 */
function tva_comment_rest_moderation_response( $response ) {
	$hidden_post_id  = get_option( 'tva_course_hidden_post_id' );
	$comment_post_id = $response->data['post'];

	if ( $hidden_post_id == $comment_post_id ) {
		$comment_id      = $response->data['id'];
		$comment_term_id = get_comment_meta( $comment_id, 'tva_course_comment_term_id', true );
		if ( is_tva_term( $comment_term_id ) ) {
			$response->data['tva_term_id'] = $comment_term_id;
		}
	}

	return $response;
}

/**
 * Chack if a term belongs to tva_courses taxonomy
 *
 * @param $term_id
 *
 * @return bool
 */
function is_tva_term( $term_id ) {
	$term = get_term( $term_id, TVA_Const::COURSE_TAXONOMY );

	if ( $term !== null && ! is_wp_error( $term ) ) {
		return true;
	}

	return false;
}

/**
 * Added for compatibility with TC
 * Count comments number for a given course in frontend
 *
 * @param                 $comment_count
 * @param WP_REST_Request $request
 *
 * @return array|int
 */
function tva_tcm_comment_count( $comment_count, $request ) {
	$hidden_post_id = get_option( 'tva_course_hidden_post_id' );
	$tva_term_id    = $request->get_param( 'tva_term' );
	$post_id        = $request->get_param( 'post_id' );

	if ( is_tva_term( $tva_term_id ) && ( $hidden_post_id == $post_id ) ) {
		$args = array(
			'post_id'    => get_option( 'tva_course_hidden_post_id' ),
			'meta_value' => $tva_term_id,
			'count'      => true,
			'status'     => 'approve',
		);

		$comment_query = new WP_Comment_Query();
		$comment_count = $comment_query->query( $args );
	}

	return $comment_count;
}

/**
 * Added for compatibility with TC
 *
 * Count comments in TC admin moderation header
 *
 * @param                 $args
 * @param WP_REST_Request $request
 *
 * @return mixed
 */
function tva_tcm_header_comment_count( $args, $request ) {
	$post_id  = $request->get_param( 'post_id' );
	$tva_term = $request->get_param( 'tva_term' );
	$tcm_post = $request->get_param( 'tcm_comment_post' );

	if ( $tcm_post && isset( $tcm_post['term_id'] ) && $tcm_post['term_id'] == $post_id ) {
		$args['post_id']    = get_option( 'tva_course_hidden_post_id' );
		$args['meta_key']   = 'tva_course_comment_term_id';
		$args['meta_value'] = $post_id;

		return $args;
	}

	$hidden_post_id = get_option( 'tva_course_hidden_post_id' );

	if ( ( $hidden_post_id === $post_id ) && is_numeric( $tva_term ) && is_tva_term( $tva_term ) ) {
		$args['post_id']    = $hidden_post_id;
		$args['meta_key']   = 'tva_course_comment_term_id';
		$args['meta_value'] = $tva_term;
	}

	if ( $tcm_post ) {
		return $args;
	}

	if ( is_tva_term( $post_id ) && $tva_term === 'true' ) {
		$args['post_id']    = $hidden_post_id;
		$args['meta_key']   = 'tva_course_comment_term_id';
		$args['meta_value'] = $post_id;

		return $args;
	}

	return $args;
}

/**
 * Added for compatibility with TC
 *
 * Prepare the args for unreplied comments in admin moderation
 *
 * @param                 $args
 * @param WP_REST_Request $request
 *
 * @return mixed
 */
function tva_tcm_get_unreplied_args( $args, $request ) {
	if ( ! $request instanceof WP_REST_Request ) {
		return $args;
	}

	$tcm_post       = $request->get_param( 'tcm_comment_post' );
	$term_id        = $request->get_param( 'post_id' );
	$tva_term       = $request->get_param( 'tva_term' );
	$hidden_post_id = get_option( 'tva_course_hidden_post_id' );
	$mt_query       = $request->get_param( 'meta_query' );

	if ( ( $tcm_post && isset( $tcm_post['term_id'] ) && ( $tcm_post['term_id'] == $term_id ) )
		 || ( is_tva_term( $term_id ) && $tva_term === 'true' )
		 || ( $hidden_post_id === $term_id && is_tva_term( $tva_term ) )
		 || ( is_array( $mt_query ) && isset( $mt_query['tcm_delegate'] ) && ( $mt_query['tcm_delegate'] == 1 ) )
	) {
		if ( isset( $args['meta_key'] ) && $args['meta_key'] === 'tcm_needs_reply' ) {
			// This is a really ugly one but as long as TC uses meta_key here we need to keep it
			unset( $args['meta_key'] );
			$args['meta_query'] = array(
				'relation' => 'AND',
				array(
					'key'   => 'tcm_needs_reply',
					'value' => '1',
				),
				array(
					'key'   => 'tva_course_comment_term_id',
					'value' => is_numeric( $tva_term ) ? $tva_term : $term_id,
				),
			);
		}
	}

	return $args;
}

/**
 * Added for compatibility with TC
 * Get featured comments for given course in frontend
 *
 * @param $args
 * @param $query_comments
 *
 * @return mixed
 */
function tva_tcm_get_featured_comments( $args, $query_comments ) {
	$hidden_post_id = get_option( 'tva_course_hidden_post_id' );

	if ( ( $hidden_post_id == $query_comments['post_id'] )
		 && isset( $query_comments['meta_key'] )
		 && ( $query_comments['meta_key'] === 'tva_course_comment_term_id' )
		 && ! empty( $query_comments['meta_value'] )
	) {
		$args['meta_key']   = 'tva_course_comment_term_id';
		$args['meta_value'] = $query_comments['meta_value'];
	}

	return $args;
}

/**
 * Added for compatibility with TC
 *
 * Unsubscribe user from comment
 *
 * @param WP_REST_Request $request
 */
function tva_tcm_post_unsubscribe( $request ) {
	$term_id = $request->get_param( 'tva_term' );
	if ( ( $term_id !== null ) && ! empty( $term_id ) ) {
		$all_subscribers = get_term_meta( $term_id, 'tva_term_subscribers', true );
		$email           = $request->get_param( 'email' );

		if ( ( $key = array_search( $email, $all_subscribers ) ) !== false ) {
			unset( $all_subscribers[ $key ] );
		}

		update_term_meta( $term_id, 'tva_term_subscribers', $all_subscribers );
	}
}

/**
 * Added for compatibility with TC
 *
 * Subscribe user to comment
 *
 * @param WP_REST_Request $request
 */
function tva_tcm_post_subscribe( $request ) {

	$term_id = $request->get_param( 'tva_term' );
	if ( ( $term_id !== null ) && ! empty( $term_id ) ) {
		$all_subscribers = get_term_meta( $term_id, 'tva_term_subscribers', true );

		if ( empty( $all_subscribers ) ) {
			$all_subscribers = array();
		}

		$all_subscribers[] = $request->get_param( 'email' );

		update_term_meta( $term_id, 'tva_term_subscribers', $all_subscribers );
	}
}

/**
 * Get term subscribers for TC
 *
 * @param $post_subscribers
 * @param $comment
 *
 * @return mixed
 */
function tva_get_term_subscribers( $post_subscribers, $comment ) {
	$hidden_post_id = get_option( 'tva_course_hidden_post_id' );

	if ( $comment->comment_post_ID == $hidden_post_id ) {
		$comment_term_id  = get_comment_meta( $comment->comment_ID, 'tva_course_comment_term_id', true );
		$post_subscribers = get_term_meta( $comment_term_id, 'tva_term_subscribers', true );
	}

	return $post_subscribers;
}

/**
 * Get term url for TC email template
 *
 * @param $post_url
 * @param $comment
 *
 * @return string|WP_Error
 */
function tva_tcm_get_term_url( $post_url, $comment ) {
	$hidden_post_id = get_option( 'tva_course_hidden_post_id' );

	if ( $comment->comment_post_ID == $hidden_post_id ) {
		$comment_term_id = get_comment_meta( $comment->comment_ID, 'tva_course_comment_term_id', true );
		$term_obj        = get_term( $comment_term_id, TVA_Const::COURSE_TAXONOMY );
		$post_url        = get_term_link( $term_obj, TVA_Const::COURSE_TAXONOMY );
	}

	return $post_url;
}

/**
 * Added for compatibility with TC
 *
 * @param                 $args
 * @param WP_REST_Request $request
 *
 * @return mixed
 */
function tva_tcm_user_comment_count( $args, $request ) {
	$term_id = $request->get_param( 'tva_term' );

	if ( is_tva_term( $term_id ) ) {
		$hidden_post_id     = get_option( 'tva_course_hidden_post_id' );
		$args['meta_key']   = $request->get_param( 'post_id' ) == $hidden_post_id ? 'tva_course_comment_term_id' : '';
		$args['meta_value'] = $request->get_param( 'post_id' ) == $hidden_post_id ? $term_id : '';
	}

	return $args;
}

/**
 * Add TC conversion triggers
 * Section Frontend
 */
function tva_add_tcm_triggers() {
	if ( class_exists( 'Thrive_Comments' ) ) {
		$tc_comments_status = tcms()->tcm_get_setting_by_name( 'activate_comments' );
		if ( $tc_comments_status == true ) {
			$extra = apply_filters( 'tva_term_extra_content', $extra = '' );
			echo $extra;
		}
	}
}

/**
 * Added for compatibility with TC
 * Append comments from courses to TC meta query
 *
 * @param                 $query
 * @param WP_REST_Request $request
 *
 * @return string
 */
function tva_tcm_reports_featured_query( $query, $request ) {
	$tva_term = $request->get_param( 'tva_term' );
	$obj_id   = $request->get_param( 'graph_source' );

	if ( ( $tva_term == true ) && is_tva_term( $obj_id ) ) {
		global $wpdb;
		$query = " INNER JOIN {$wpdb->prefix}commentmeta as terms ON ( c.comment_ID = terms.comment_ID AND terms.meta_key = 'tva_course_comment_term_id' AND terms.meta_value = '$obj_id' )";
	}

	return $query;
}

/**
 * Added for compatibility with TC
 * Add courses to main TC comments graph query
 *
 * @param                 $query
 * @param WP_REST_Request $request
 *
 * @return string
 */
function tva_tcm_reports_extra_filter( $query, $request ) {
	$tva_term = $request->get_param( 'tva_term' );
	$obj_id   = $request->get_param( 'graph_source' );

	if ( ( $tva_term == true ) && is_tva_term( $obj_id ) ) {
		global $wpdb;
		$query = " AS c INNER JOIN {$wpdb->prefix}commentmeta AS cm ON c.comment_ID = cm.comment_id AND cm.meta_key = 'tva_course_comment_term_id' AND cm.meta_value = '$obj_id'";
	}

	return $query;
}

/**
 * Added for compatibility with TC
 *
 * @param                 $graph_source
 * @param WP_REST_Request $request
 *
 * @return mixed
 */
function tva_tcm_reports_post_filter( $graph_source, $request ) {
	$tva_term = $request->get_param( 'tva_term' );
	$obj_id   = $request->get_param( 'graph_source' );

	if ( ( $tva_term == true ) && is_tva_term( $obj_id ) ) {
		$graph_source = get_option( 'tva_course_hidden_post_id' );
	}

	return $graph_source;
}

/**
 * Added for compatibility with TC
 * Add courses to TC Votes reports query
 *
 * @param $query
 * @param $request
 *
 * @return string
 */
function tva_tcm_reports_votes_extra_filter( $query, $request ) {

	$tva_term = $request->get_param( 'tva_term' );
	$obj_id   = $request->get_param( 'graph_source' );

	if ( ( $tva_term == true ) && is_tva_term( $obj_id ) ) {
		global $wpdb;
		$query = " INNER JOIN {$wpdb->prefix}commentmeta AS terms ON ( c.comment_ID = terms.comment_ID AND terms.meta_key = 'tva_course_comment_term_id' AND terms.meta_value = $obj_id ) ";
	}

	return $query;
}

/**
 * Added for compatibility with TC
 * Add courses to TC reports
 *
 * @param $result
 * @param $begin
 * @param $end
 *
 * @return array
 */
function tva_tcm_most_popular_posts( $result, $begin, $end ) {
	if ( class_exists( 'WP_Term_Query' ) ) {
		$args       = tva_get_courses_args( array( 'published' ) );
		$term_query = new WP_Term_Query( $args );

		foreach ( $term_query->terms as $term ) {
			$args            = array(
				'post_id'    => get_option( 'tva_course_hidden_post_id' ),
				'date_query' => array(
					'after'     => $begin->format( 'Y-m-d' ),
					'before'    => $end->format( 'Y-m-d' ),
					'inclusive' => true,
				),
				'meta_key'   => 'tva_course_comment_term_id',
				'meta_value' => $term->term_id,
				'count'      => true,
			);
			$comments_number = get_comments( $args );

			if ( 0 !== $comments_number ) {
				$result[] = array(
					'post_title'    => $term->name,
					'comment_count' => $comments_number,
				);
			}
		}
	}

	return $result;
}

/**
 * Added for compatibility with TC
 * We don't have related courses yet, so we prevent unexpected behavior on courses here
 *
 * @param WP_Comment $comment
 *
 * @return mixed
 * @todo change this function if/when related courses will be implemented
 *
 */
function tva_tcm_comment_after_save( $comment ) {
	$hidden_post_id = get_option( 'tva_course_hidden_post_id' );

	if ( $hidden_post_id == $comment->comment_post_ID ) {
		$conversion_settings = $comment->conversion_settings;

		if ( $conversion_settings['first_time']['active'] == 'tcm_related_posts' ) {
			$conversion_settings['first_time']['active'] = false;
		}

		if ( $conversion_settings['second_time']['active'] == 'tcm_related_posts' ) {
			$conversion_settings['second_time']['active'] = false;
		}

		$comment->conversion_settings = $conversion_settings;
	}

	return $comment;
}

/**
 * Added for compatibility with TC
 * Used in TC comments moderation to delegate/assign comments to course author
 * Return course author
 *
 * @param       $tcm_delegate
 * @param array $request
 */
function tva_tcm_comment_delegate( $tcm_delegate, $request ) {
	$hidden_post_id = get_option( 'tva_course_hidden_post_id' );
	$post_ID        = $request['post'];
	$comment_id     = $request['id'];

	if ( $post_ID == $hidden_post_id ) {
		$term_comment = get_comment_meta( $comment_id, 'tva_course_comment_term_id', true );
		if ( is_tva_term( $term_comment ) ) {
			$term_author  = get_term_meta( $term_comment, 'tva_author', true );
			$tcm_delegate = $term_author['ID'];
		}
	}

	return $tcm_delegate;
}

/**
 * Added for compatibility with TC
 * Add a new meta query to main comments query
 *
 * @param                 $meta_query
 * @param WP_REST_Request $request
 *
 * @return mixed
 */
function tva_tcm_delegate_rest_meta_query( $meta_query, $request ) {
	if ( ! $request instanceof WP_REST_Request ) {
		return $meta_query;
	}

	$meta_query[] = array(
		'key'     => 'tva_course_comment_term_id',
		'value'   => '',
		'compare' => '=',
	);

	return $meta_query;
}

/**
 * Added for compatibility with TC
 * section: comments moderation
 * Add comments from courses to pending my reply tab
 *
 * @param $extra_where
 * @param $delegate_id
 * @param $comment_query
 *
 * @return string
 */
function tva_tcm_delegate_extra_where( $extra_where, $delegate_id, $comment_query ) {
	$hidden_post_id = get_option( 'tva_course_hidden_post_id' );
	$comments       = get_comments( array( 'post_id' => $hidden_post_id ) );
	$comment_ids    = wp_list_pluck( $comments, 'comment_ID' );
	$valid_ids      = array();
	$tva_term       = isset( $_REQUEST['tva_term'] ) && $_REQUEST['tva_term'] === 'true';
	$course_id      = isset( $_REQUEST['post_id'] ) ? $_REQUEST['post_id'] : '';

	foreach ( $comment_ids as $comment_id ) {
		$meta      = get_comment_meta( $comment_id, 'tva_course_comment_term_id', true );
		$term_meta = get_term_meta( $meta, 'tva_author', true );

		if ( isset( $term_meta['ID'] ) && (int) $term_meta['ID'] === get_current_user_id() ) {
			$valid_ids[] = $comment_id;

			if ( $tva_term === true && (int) $course_id !== (int) $meta ) {
				$key = count( $valid_ids );
				array_splice( $valid_ids, $key - 1, 1 );
			}
		}
	}

	$meta_query = $comment_query->meta_query;
	$queries    = $meta_query->queries;

	global $wpdb;

	foreach ( $queries as $query ) {
		if ( is_array( $query ) && isset( $query['key'] ) && ( $query['key'] === 'tva_course_comment_term_id' ) && ! empty( $query['value'] ) ) {

			foreach ( $comments as $comment ) {
				$meta = get_comment_meta( $comment->comment_ID, 'tva_course_comment_term_id', true );
				if ( ( $meta === (int) $query['value'] ) && ! in_array( $meta, $valid_ids ) ) {

					if ( $tva_term && $course_id && (int) $term_meta['ID'] === get_current_user_id() ) {
						$term_meta   = get_term_meta( $meta, 'tva_author', true );
						$valid_ids[] = $comment->comment_ID;
					}
				}
			}
		}
	}

	$valid_ids = implode( ',', $valid_ids );

	if ( empty( $valid_ids ) ) {
		$valid_ids = '""';
	}

	return " OR (
	        ( mt3.meta_key = 'tva_course_comment_term_id' ) AND ( {$wpdb->prefix}comments.comment_id IN (" . $valid_ids . ") AND (
	            ( mt2.meta_key = 'tcm_needs_reply' AND mt2.meta_value = '1' ) OR
	            ( {$wpdb->prefix}commentmeta.meta_key = 'tcm_delegate' AND {$wpdb->prefix}commentmeta.meta_value IN (" . $delegate_id . ")
                AND ( mt2.meta_key = 'tcm_needs_reply' AND mt2.meta_value = '1' ))
            )))";
}

/**
 * Added for compatibility with TC
 * Section: comments moderation
 * Add a join clause to TC delegate query
 *
 * @param $join
 *
 * @return string
 */
function tva_tcm_delegate_extra_join( $join ) {
	global $wpdb;

	$join .= "LEFT JOIN {$wpdb->prefix}commentmeta AS mt3 ON ({$wpdb->prefix}comments.comment_ID = mt3.comment_id and mt3.meta_key = 'tva_course_comment_term_id')";

	return $join;
}

/**
 * Added for compatibility with TC
 * Ensure comments open on courses and lessons
 *
 * @param $close_comments
 *
 * @return bool
 */
function tva_tcm_close_comments( $close_comments ) {
	if ( tva_is_apprentice() ) {
		$close_comments = false;
	}

	return $close_comments;
}

/**
 * Added for compatibility with TC
 * Section TC reports
 *
 * Update data for comments posted on courses
 *
 * @param $comments
 *
 * @return mixed
 */
function tva_tcm_most_upvoted( $comments ) {
	$hidden_post_id = get_option( 'tva_course_hidden_post_id' );

	foreach ( $comments as $key => $comment ) {
		if ( $hidden_post_id === $comment['comment_post_ID'] ) {
			$parent_course_id = get_comment_meta( $comment['comment_ID'], 'tva_course_comment_term_id', true );
			$course_obj       = get_term( $parent_course_id, TVA_Const::COURSE_TAXONOMY );

			$comment['comment_post_link'] = get_term_link( $course_obj, TVA_Const::COURSE_TAXONOMY );
			$comment['comment_post']      = $course_obj->name;

			$comments[ $key ] = $comment;
		}
	}

	return $comments;
}

/**
 * Add lessons post type to TC privacy export
 *
 * @param $post_types
 *
 * @return array
 */
function tva_tcm_privacy_post_types( $post_types ) {
	$post_types[] = TVA_Const::LESSON_POST_TYPE;

	return $post_types;
}

/**
 * @param $label_text
 * @param $post
 *
 * @return string
 */
function tva_tcm_label_privacy_text( $label_text, $post ) {
	if ( ! is_wp_error( $post ) && ( $post->post_type === TVA_Const::LESSON_POST_TYPE ) ) {
		$label_text = 'Subscribed to comments for a lesson';
	}

	return $label_text;
}

/**
 * Check if we have a comment plugin
 *
 * @return bool
 */
function tva_has_comment_plugin() {
	if ( class_exists( 'Thrive_Comments' ) ) {
		return tcms()->tcm_get_setting_by_name( 'activate_comments' );
	}

	if ( class_exists( TVA_Const::DISQUS_REF_CLASS ) ) {
		return true;
	}

	return false;
}

/**
 * Added for compatibility with TC
 *
 * Ensure that the correct post is passed further: TASK: TA-997
 *
 * @param $post
 *
 * @return mixed|WP_Post
 */
function tva_tcm_get_post( $post ) {
	$obj = get_queried_object();

	if ( is_single() && $obj->post_type == TVA_Const::LESSON_POST_TYPE ) {
		$post            = get_post( $obj->ID );
		$post->permalink = get_permalink( $post );
	}

	return $post;
}

/**
 * Build the html for tva register url
 *
 * @param $login_form_bottom string to be append in bottom of login form
 *
 * @return string
 */
function tva_get_register_page( $login_form_bottom ) {

	if ( tva_is_apprentice() ) {
		$register_page = tva_get_setting( 'register_page' );

		global $post;
		$login_form_bottom .= '<input type="hidden" name="tva_post" value="' . $post->ID . '"/>';

		$register_page = ! empty( $register_page ) ? get_post( $register_page ) : null;
		if ( true === $register_page instanceof WP_Post ) {
			$login_form_bottom .= '<a class="tva-register-url" href="' . get_page_link( $register_page ) . '"> ' . __( 'Register a new account here', 'thrive-apprentice' ) . '</a>';
		}
	}

	return $login_form_bottom;
}

/*
 * Build registration form html
 * return $html
 */
function tva_build_registration_page_html() {

	if ( ! tva_is_apprentice() ) {
		return;
	}

	$args_array = array(
		'first_name'   => array(
			'input_name'  => 'first_name',
			'input_type'  => 'text',
			'placeholder' => __( 'Name', 'thrive-apprentice' ),
			'required'    => true,
		),
		'user_email'   => array(
			'input_name'  => 'user_email',
			'input_type'  => 'email',
			'placeholder' => __( 'Email', 'thrive-apprentice' ),
			'required'    => true,
		),
		'user_pass'    => array(
			'input_name'  => 'user_pass',
			'input_type'  => 'password',
			'placeholder' => __( 'Password', 'thrive-apprentice' ),
			'required'    => true,
		),
		'confirm_pass' => array(
			'input_name'  => 'confirm_pass',
			'input_type'  => 'password',
			'placeholder' => __( 'Confirm Password', 'thrive-apprentice' ),
			'required'    => true,
		),
	);

	$html = '<div class="tva-register-title"><h2>' . __( 'Fill out the form below to create your free account', 'thrive-apprentice' ) . '</h2></div>';
	$html .= '<form action="' . esc_attr( $_SERVER['REQUEST_URI'] ) . '" method="post" id="ta-registration-form">';
	foreach ( $args_array as $key_arg => $arg ) {
		$html .= '<div class="tva-register-input-wrapper">';
		$html .= '<input type="' . $arg['input_type'] . '" name="' . $arg['input_name'] . '" placeholder="' . $arg['placeholder'] . '">';

		if ( $arg['input_type'] == 'password' ) {
			$html .= '<div class="tve-password-strength-wrapper">';
			$html .= '<div class="tve-password-strength tve-password-strength-0"></div>';
			$html .= '<div class="tve-password-strength tve-password-strength-1"></div>';
			$html .= '<div class="tve-password-strength tve-password-strength-2"></div>';
			$html .= '<div class="tve-password-strength tve-password-strength-3"></div>';
			$html .= '<span class="tve-password-strength-icon"></span>';
			$html .= '<span class="tve-password-strength-text"></span>';
			$html .= '</div>';
		}

		if ( $arg['input_name'] == 'confirm_pass' ) {
			$html .= '<p class="tva-invalid-password">' . __( 'Password not match.', 'thrive-apprentice' ) . '</p>';
		}

		$html .= '</div>';

		if ( $key_arg == 'user_email' ) {
			$html .= '<p class="tva-invalid-email">' . __( 'Invalid email address.', 'thrive-apprentice' ) . '</p>';
		}

		if ( isset( $arg['required'] ) ) {
			$html .= '<p class="tva-required-filed">' . __( 'Required field', 'thrive-apprentice' ) . '</p>';
		}
	}

	$reg_errors = tva_register_user();
	if ( is_wp_error( $reg_errors ) && isset( $reg_errors->errors['pasword_not_match'] ) ) {
		$html .= '<p class="tva-password-mismatch">' . $reg_errors->errors['pasword_not_match'][0] . '</p>';
	}
	$html .= '<input type="hidden" name="tva_captcha" value="' . uniqid( 'tva_captcha' ) . '">';
	$html .= '<input type="hidden" name="tva_register_nonce" value="' . wp_create_nonce( 'tva-register-user-nonce' ) . '">';
	$html .= '<button id="tva-register-button" type="submit" name="register_button">' . __( 'Sign Up', 'thrive-apprentice' ) . '</button>';
	$html .= '<div id="tva-email-error-wrapper">' . __( 'An account with this email already exists. In order to view the courses, please login first or use another email.', 'thrive-apprentice' ) . '</div>';
	$html .= '</form>';

	echo $html;
}

/**
 * Register new user
 *
 * @return int|WP_Error
 */
function tva_register_user() {

	$request = $_POST;

	if ( ! isset( $request['tva_captcha'] ) && isset( $request['register_button'] ) && ! empty( $request['tva_register_nonce'] ) && wp_verify_nonce( $request['tva_register_nonce'], 'tva-register-user-nonce' ) ) {
		$user_args = array(
			'user_login'   => $request['user_email'],
			'user_email'   => $request['user_email'],
			'user_pass'    => $request['user_pass'],
			'confirm_pass' => $request['confirm_pass'],
			'first_name'   => $request['first_name'],
		);

		if ( $user_args['user_pass'] !== $user_args['confirm_pass'] ) {
			return new WP_Error( 'password_not_match', 'Password and confirm password field does not match' );
		}

		$new_user = wp_insert_user( $user_args );

		if ( ! is_wp_error( $new_user ) ) {
			$credential['user_login']    = $user_args['user_login'];
			$credential['user_password'] = $user_args['user_pass'];
			$subject                     = __( 'You have a New Signup' );

			ob_start();
			include TVA_Const::plugin_path( 'templates/email-template.php' );
			$message = ob_get_contents();
			ob_end_clean();

			wp_mail( get_option( 'admin_email' ), $subject, $message );
			tva_perform_auto_login( get_user_by( 'id', $new_user ), array( 'password' => $user_args['user_pass'] ) );
		}

		return $new_user;
	}
}

/**
 * Redirect new user to the lesson from where it came on the register page
 */
function tva_redirect_user() {
	if ( isset( $_COOKIE['tva_lesson_to_redirect'] ) && ! wp_doing_ajax() ) {
		wp_redirect( $_COOKIE['tva_lesson_to_redirect'] );
		exit();
	}
}

/*
 * Create apprentice default register page
 */
function tva_create_default_register_page() {
	$args = array(
		'post_type'   => 'page',
		'post_title'  => 'Apprentice registration page',
		'post_status' => 'publish',
	);

	$register_page_ID = wp_insert_post( $args );

	if ( ! is_wp_error( $register_page_ID ) ) {
		update_option( 'tva_default_register_page', array( 'ID' => $register_page_ID, 'name' => $args['post_title'] ) );
		tva_get_settings_manager()->factory( 'register_page' )->set_value( $register_page_ID );

		return array(
			'ID'   => $register_page_ID,
			'name' => $args['post_title'],
		);
	}
}

/**
 * @param $query
 * Hide the default page from wp admin
 */
function tva_hide_default_register_page( $query ) {
	global $pagenow, $post_type;

	$reg_page_option = get_option( 'tva_default_register_page' );

	if ( is_admin() && $pagenow == 'edit.php' && $post_type == 'page' && is_array( $query->query_vars['post__not_in'] ) ) {

		if ( $reg_page_option !== false ) {
			$query->query_vars['post__not_in'][] = $reg_page_option['ID'];
		}

		/**
		 * Hides the course index page from page list
		 * Hopefully solves the problem that customer have when index pages are getting deleted.
		 */
		$index_page = tva_get_settings_manager()->factory( 'index_page' )->get_value();
		if ( ! empty( $index_page ) ) {
			$query->query_vars['post__not_in'][] = $index_page;
		}

		/**
		 * Hide the certificate validation page if set from the pages list
		 */
		$certificate_verification_page = tva_get_settings_manager()->factory( 'certificate_validation_page' )->get_value();
		if ( ! empty( $certificate_verification_page ) ) {
			$query->query_vars['post__not_in'][] = $certificate_verification_page;
		}
	}
}

/**
 * Add the lessons to the autocomplete search results
 *
 * @param $selected_post_types
 *
 * @return array
 */
function tva_add_post_types( $selected_post_types ) {

	$selected_post_types[] = TVA_Const::LESSON_POST_TYPE;
	$selected_post_types[] = TVA_Const::COURSE_POST_TYPE;
	$selected_post_types[] = TVA_Const::MODULE_POST_TYPE;

	return $selected_post_types;
}

function tva_default_add_post_types( $post_types ) {

	array_push( $post_types, TVA_Const::LESSON_POST_TYPE, TVA_Const::COURSE_POST_TYPE, TVA_Const::MODULE_POST_TYPE );

	return $post_types;
}

/**
 * Add the courses to the results of the search in TCB
 *
 * @param $posts
 * @param $s
 *
 * @return array
 */
function tva_add_courses_to_results( $posts, $s ) {

	$args = array(
		'taxonomy'   => array( TVA_Const::COURSE_TAXONOMY ), // taxonomy name
		'orderby'    => 'id',
		'order'      => 'ASC',
		'hide_empty' => true,
		'fields'     => 'all',
		'name__like' => $s,
	);

	$courses = get_terms( $args );
	$count   = count( $courses );

	if ( $count > 0 ) {
		foreach ( $courses as $course ) {

			$title = $course->name;
			if ( ! empty( $s ) ) {
				$course->name = preg_replace( "#($s)#i", '<b>$0</b>', $course->name );
			}
			$post = array(
				'label'    => $course->name,
				'title'    => $title,
				'id'       => $course->term_id,
				'value'    => $course->name,
				'url'      => get_term_link( (int) $course->term_id, TVA_Const::COURSE_TAXONOMY ),
				'type'     => TVA_Const::COURSE_TAXONOMY,
				'is_popup' => false,
			);

			$posts [] = $post;
		}
	}

	return $posts;
}

/**
 * Load svg icons
 *
 * @param        $icon
 * @param string $class
 * @param bool   $return
 *
 * @return string
 */
function tva_get_svg_icon( $icon, $class = '', $return = false ) {
	if ( ! $class ) {
		$class = 'ta-' . $icon;
	}

	$html = '<svg class="tva-icon ' . $class . '"><use xlink:href="#ta-' . $icon . '"></use></svg>';

	if ( false !== $return ) {
		return $html;
	}
	echo $html;
}

/**
 * Load svg icons
 */
function add_frontend_svg_file() {
	if ( ( tva_is_apprentice() && ! TTB_Main::uses_builder_templates( false ) ) || ( is_admin() && tve_get_current_screen_key() === TVA_Admin::SCREEN_ID ) ) {
		include TVA_Const::plugin_path( 'img/ta-svg-icons.svg' );
	}
}

/**
 * Generate single lesson html
 *
 * @param TVA_Lesson $lesson
 * @param bool       $is_parent_allowed
 *
 * @return string
 */
function tva_generate_lesson_html( $lesson, $is_parent_allowed = false ) {

	ob_start();

	include dirname( __FILE__ ) . '/views/lesson.phtml';

	return ob_get_clean();
}

/**
 * Generate single chapter html
 *
 * @param TVA_Chapter|WP_Post $chapter
 * @param bool                $is_parent_allowed
 *
 * @return string
 */
function tva_generate_chapter_html( $chapter, $is_parent_allowed = false ) {

	ob_start();

	include dirname( __FILE__ ) . '/views/chapter.phtml';

	return ob_get_clean();
}

/**
 * Generate the html for a single module item
 *
 * @param TVA_Module $module
 *
 * @return string
 */
function tva_generate_module_html( $module ) {

	ob_start();

	include dirname( __FILE__ ) . '/views/module.phtml';

	return ob_get_clean();
}

/**
 * Check if a chapter is completed, all its lessons are marked as learned
 *
 * @param TVA_Chapter|WP_Post $chapter
 *
 * @return bool
 * @deprecated TVA_Post ->is_completed should be used
 */
function tva_is_chapter_completed( $chapter ) {

	if ( true === $chapter instanceof WP_Post ) {
		$chapter = TVA_Post::factory( $chapter );
	}

	$learned_lessons = tva_get_learned_lessons();

	if ( is_user_logged_in() && TVA_Product::has_access() ) {
		return false;
	}

	if ( empty( $learned_lessons ) ) {
		return false;
	}

	$ids = wp_list_pluck( $chapter->get_visible_lessons(), 'ID', 'ID' );

	if ( isset( $learned_lessons[ $chapter->course_id ] ) && is_array( $learned_lessons[ $chapter->course_id ] ) ) {
		$completed_lessons = array_diff_key( $ids, array_keys( $learned_lessons[ $chapter->course_id ] ) );

		return count( $completed_lessons ) == 0;
	}

	return $chapter->is_completed();
}

/**
 * Check if a module is completed, all its lessons are marked as learned or all lessons from all its chapters are marked as learned
 *
 * @param TVA_Module|WP_Post $module
 *
 * @return bool
 * @deprecated TVA_Post ->is_completed should be used
 */
function tva_is_module_completed( $module ) {

	if ( true === $module instanceof WP_Post ) {
		$module = TVA_Post::factory( $module );
	}

	$learned_lessons = tva_get_learned_lessons();

	if ( empty( $learned_lessons ) ) {
		return false;
	}

	if ( $module->get_published_lessons_count() > 0 ) {
		$ids = wp_list_pluck( $module->get_published_lessons(), 'ID', 'ID' );

		if ( isset( $learned_lessons[ $module->course_id ] ) ) {
			$completed_lessons = array_diff_key( $ids, array_keys( $learned_lessons[ $module->course_id ] ) );

			return count( $completed_lessons ) === 0;
		}

		return false;
	}

	if ( $module->get_published_chapters_count() > 0 ) {
		$completed = false;
		foreach ( $module->get_published_chapters() as $chapter ) {
			$completed = tva_is_chapter_completed( $chapter );

			/**
			 * If a single chapter isn't completed, the module isn't completed, so we stop the loop here
			 */
			if ( ! $completed ) {
				break;
			}
		}

		return $completed;
	}

	return false;
}

/**
 * Computes the completed count depending on the post type
 *
 * @param TVA_Post $post
 *
 * @return int
 */
function tva_get_completed_count( $post ) {
	$count = 0;

	if ( $post instanceof WP_Post ) {
		$post = TVA_Post::factory( $post );
	}

	$structure = $post->get_structure();

	/**
	 * @var TVA_Post $structure_post
	 */
	foreach ( $structure as $structure_post ) {
		if ( $structure_post->is_completed() ) {
			$count ++;
		}
	}

	return $count;
}

/**
 * Returns the completed lessons number
 *
 * @param array $items
 *
 * @return int
 */
function tva_count_completed_items( $items = [] ) {
	$count = 0;

	/**
	 * @var $item TVA_Lesson| TVA_Assessment
	 */
	foreach ( $items as $item ) {
		if ( $item->is_completed() ) {
			$count ++;
		}
	}

	return $count;
}

/**
 * Returns true if the module has just been started.
 * If the a lesson from a module is completed by the user
 *
 * @param $module
 *
 * @return bool
 */
function tva_is_module_started( $module ) {

	if ( true === $module instanceof WP_Post ) {
		$module = TVA_Post::factory( $module );
	}

	$lessons = TVA_Manager::get_all_module_items( $module->get_the_post(), [
		'post_type' => TVA_Const::LESSON_POST_TYPE,
	] );

	if ( count( $lessons ) > 0 ) {

		$learned_lessons = tva_get_learned_lessons();

		if ( empty( $learned_lessons ) ) {
			return true;
		}

		if ( empty( $learned_lessons[ $module->course_id ] ) ) {
			/**
			 * Solves the case when the course has modules and no lesson has been completed for that course
			 */
			return true;
		}

		if ( isset( $learned_lessons[ $module->course_id ] ) ) {
			$ids = array_values( wp_list_pluck( $lessons, 'ID', 'ID' ) );

			if ( count( array_intersect( $ids, array_keys( $learned_lessons[ $module->course_id ] ) ) ) === 0 ) {
				return true;
			}
		}
	}

	return false;
}

/**
 * Build apprentice label html based on user settings
 *
 * @return string
 */
function tva_add_apprentice_label() {
	$html = '';

	if ( tva_get_setting( 'apprentice_label' ) ) {
		$affiliate_id = get_option( 'thrive_affiliate_id' );

		$url = $affiliate_id
			? 'https://thrivethemes.com/affiliates/ref.php?id=' . $affiliate_id
			: 'https://thrivethemes.com/apprentice/';

		$html .= '<div class="tva-label-container">';
		$html .= '<img src="' . TVA_Const::plugin_url( 'img/tva-apprentice-logo.png' ) . '" >';
		$html .= '<span class="tva-label-text">' . __( 'Powered by', 'thrive-apprentice' ) . ' </span>';
		$html .= '<a class="tva-apprentice-url" href="' . $url . '" target="_blank">Thrive Apprentice</a>';
		$html .= '</div>';
	}

	return $html;
}

/**
 * Get first lesson url
 *
 * @param TVA_Course_V2 $course
 *
 * @return string
 */
function tva_get_start_course_url( $course ) {

	$url = '';

	if ( $course->published_lessons_count > 0 ) {
		$lessons      = TVA_Manager::get_course_items( $course instanceof WP_Term ? $course : $course->get_wp_term(), [ 'post_type' => TVA_Const::LESSON_POST_TYPE ] );
		$first_lesson = $lessons[0];
		$url          = get_permalink( $first_lesson->ID );
	} elseif ( $course->published_chapters_count > 0 ) {
		$chapters      = $course->get_published_chapters();
		$first_chapter = $chapters[0];
		$url           = get_permalink( $first_chapter->ID );
	} elseif ( $course->published_modules_count > 0 ) {
		$modules         = $course->get_published_modules();
		$first_module    = $modules[0];
		$direct_children = $first_module->get_direct_children();
		$child           = $direct_children[0];
		$url             = get_permalink( $child->ID );
	}

	return ! empty( $url ) ? $url : 'javascript:void(0)';
}

/**
 * Get course url depending by its content
 *
 * @param TVA_Course|TVA_Course_V2 $course
 *
 * @return mixed
 */
function tva_get_course_url( $course ) {

	if ( true === $course instanceof TVA_Course_V2 ) {
		return $course->get_link( false );
	}

	if ( count( $course->lessons ) === 1 ) {
		return get_permalink( $course->lessons[0]->ID );
	}

	if ( ( count( $course->chapters ) === 1 ) && ( count( $course->chapters[0]->lessons ) === 1 ) ) {
		return get_permalink( $course->chapters[0]->lessons[0]->ID );
	}

	if ( count( $course->modules ) === 1 ) {
		$module = $course->modules[0];

		if ( count( $module->lessons ) === 1 ) {
			return get_permalink( $module->lessons[0]->ID );
		}

		if ( ( count( $module->chapters ) === 1 ) && ( count( $module->chapters[0]->lessons ) === 1 ) ) {
			return get_permalink( $module->chapters[0]->lessons[0]->ID );
		}
	}

	return get_term_link( $course->term_id, TVA_Const::COURSE_TAXONOMY );
}

/**
 * Get next lesson url
 *
 * @param       $curent_lesson
 * @param array $lessons
 * @param null  $operator
 * @param bool  $is_next
 *
 * @return string
 */
function tva_get_next_lesson( $curent_lesson, $lessons = array(), $operator = null, $is_next = false ) {
	$next = '';

	if ( $is_next ) {
		if ( $operator == '-' ) {
			$last_lesson = end( $lessons );

			return get_permalink( $last_lesson->ID );
		}

		return get_permalink( $lessons[0]->ID );
	}

	foreach ( $lessons as $key => $lesson ) {
		$arr_key = $operator == '+' ? $key + 1 : $key - 1;

		if ( ( $curent_lesson->ID === $lesson->ID ) && array_key_exists( $arr_key, $lessons ) ) {
			$next_post = $lessons[ $arr_key ];
			$next      = get_permalink( $next_post->ID );
			break;
		}
	}

	return $next;
}

/**
 * Get next
 *
 * @param       $current_lesson
 * @param array $chapters
 * @param null  $operator
 * @param bool  $is_next
 *
 * @return null|string
 */
function tva_get_next_chapter( $current_lesson, $chapters = array(), $operator = null, $is_next = false ) {
	$next = null;

	if ( $is_next ) {
		if ( $operator == '-' ) {
			$last_chapter = end( $chapters );
			$last_lesson  = end( $last_chapter->lessons );

			return get_permalink( $last_lesson->ID );
		}

		return get_permalink( $chapters[0]->lessons[0]->ID );
	}

	foreach ( $chapters as $key => $chapter ) {
		$arr_key = $operator == '+' ? $key + 1 : $key - 1;

		if ( ( count( $chapter->lessons ) > 0 ) && ( $chapter->ID === $current_lesson->post_parent ) ) {
			$next = tva_get_next_lesson( $current_lesson, $chapter->lessons, $operator );

			if ( $next ) {
				break;
			} else {
				if ( array_key_exists( $arr_key, $chapters ) ) {
					$next = tva_get_next_lesson( $current_lesson, $chapters[ $arr_key ]->lessons, $operator, true );
					break;
				}
			}
		}
	}

	return $next;
}

/**
 * @param       $current_lesson
 * @param array $modules
 * @param null  $operator
 * @param bool  $is_next
 *
 * @return null|string
 */
function tva_get_next_module( $current_lesson, $modules = array(), $operator = null, $is_next = false ) {
	$next = '';

	foreach ( $modules as $key => $module ) {
		$arr_key = $operator == '+' ? $key + 1 : $key - 1;

		/**
		 * Check if the module is the first parent of this lesson
		 */
		if ( $current_lesson->post_parent === $module->ID ) {
			$next = tva_get_next_lesson( $current_lesson, $module->lessons, $operator );

			if ( $next ) {
				break;
			} else {
				if ( array_key_exists( $arr_key, $modules ) ) {
					$next_module = $modules[ $arr_key ];

					if ( count( $next_module->lessons ) > 0 ) {
						$next = tva_get_next_lesson( $current_lesson, $next_module->lessons, $operator, true );
					} else {
						$next = tva_get_next_chapter( $current_lesson, $next_module->chapters, $operator, true );
					}

					break;
				}
			}
		} else {
			if ( count( $module->chapters ) > 0 ) {
				$chapter_ids = wp_list_pluck( $module->chapters, 'ID' );

				if ( in_array( $current_lesson->post_parent, $chapter_ids ) ) {
					$next = tva_get_next_chapter( $current_lesson, $module->chapters, $operator );

					if ( $next ) {
						break;
					} else {
						if ( array_key_exists( $arr_key, $modules ) ) {
							$next_module = $modules[ $arr_key ];

							if ( count( $next_module->lessons ) > 0 ) {
								$next = tva_get_next_lesson( $current_lesson, $next_module->lessons, $operator, true );
							} else {
								$next = tva_get_next_chapter( $current_lesson, $next_module->chapters, $operator, true );
							}

							break;
						}
					}
				}
			}
		}
	}

	return $next;
}

/**
 * @param $course
 * @param $post
 *
 * @return null|string
 */
function tva_get_next_or_prev_post_url( $course, $post, $operator ) {
	$next = '';

	if ( count( $course->lessons ) > 0 ) {
		$next = tva_get_next_lesson( $post, $course->lessons, $operator );
	} elseif ( count( $course->chapters ) > 0 ) {
		$next = tva_get_next_chapter( $post, $course->chapters, $operator );
	} elseif ( count( $course->modules ) > 0 ) {
		$next = tva_get_next_module( $post, $course->modules, $operator );
	}

	return $next;
}

/**
 * Update demo content - first, delete all demo courses. Then, recreate them
 *
 * @param boolean $create if true, the system also creates demo content
 */
function tva_update_demo_content( $create = true ) {
	$courses = tva_get_courses( array( 'private' => true ) );

	foreach ( $courses as $course ) {
		$args = array(
			'posts_per_page' => - 1,
			'post_type'      => array(
				TVA_Const::MODULE_POST_TYPE,
				TVA_Const::CHAPTER_POST_TYPE,
				TVA_Const::LESSON_POST_TYPE,
			),
			'post_status'    => TVA_Post::$accepted_statuses,
			'tax_query'      => array(
				array(
					'taxonomy' => TVA_Const::COURSE_TAXONOMY,
					'field'    => 'term_id',
					'terms'    => array( $course->term_id ),
					'operator' => 'IN',
				),
			),
		);

		$posts = get_posts( $args );
		wp_delete_term( $course->term_id, TVA_Const::COURSE_TAXONOMY );

		if ( ! empty( $posts ) ) {
			foreach ( $posts as $post ) {
				wp_delete_post( $post->ID, true );
			}
		}
	}

	$leftover_demo_posts = TVA_Manager::get_demo_posts();
	if ( ! empty( $leftover_demo_posts ) ) {
		foreach ( $leftover_demo_posts as $leftover_post ) {
			wp_delete_post( $leftover_post->ID, true );
		}
	}

	if ( $create ) {
		tva_create_default_data();
	}
}

/**
 * Always allow access to apprentice pages with membermouse page protection
 *
 * @param $data
 *
 * @return bool
 */
function tva_custom_content_protection( $data ) {
	$obj = get_queried_object();

	if ( is_editor_page() && is_single() && isset( $obj ) && $obj->post_type == TVA_Const::LESSON_POST_TYPE && TVA_Product::has_access() ) {
		return true;
	}

	return $data;
}

/**
 * In case of WP and MM rules on a course
 * - overwrite MM logic with the one applied by the rules set
 *
 * @param bool $has_access
 *
 * @return bool
 */
function tva_mm_filter_access( $has_access ) {

	if ( false === $has_access ) {
		global $post;

		$sets = Set::get_for_object( $post, $post->ID );

		$product = Product::get_from_set( $sets );

		if ( ! empty( $post ) && $product instanceof Product ) {
			$has_access = tva_access_manager()->has_access_to_object( $post );
		}
	}

	return $has_access;
}

/**
 * Overwrite wishlist redirect when user logs in via checkout element or on a lesson/module page
 *
 * @return bool
 */
function tva_wishlistmember_login_redirect_override( $override ) {
	$url     = wp_get_referer();
	$post_id = url_to_postid( $url );
	$post    = get_post( $post_id );

	if ( $post instanceof WP_Post ) {
		$checkout = get_option( 'tva_checkout_page', array( 'name' => '', 'ID' => '' ) );

		$avoid_post_types = array( TVA_Const::MODULE_POST_TYPE, TVA_Const::LESSON_POST_TYPE );

		if ( ( $post->ID == (int) $checkout['ID'] ) || ( in_array( $post->post_type, $avoid_post_types ) ) ) {
			$override = true;
		}
	}

	return $override;
}

function tva_filter_ab_monetary_services( $services ) {

	if ( TVA_SendOwl::is_connected() ) {
		$services['sendowl'] = array(
			'name'  => 'Sendowl',
			'label' => __( 'A customer purchases a product through SendOwl', 'thrive-apprentice' ),
			'slug'  => 'sendowl',
		);
	}

	return $services;
}

/**
 * @param $event Thrive_AB_Event
 */
function tva_ab_event_saved( $event ) {

	if ( $event->is_impression() ) {

		$test = new Thrive_AB_Test( (int) $event->test_id );

		if ( $test->goal_pages() === 'sendowl' ) {

			$cookie_name  = 'top-ta-last-variation';
			$cookie_value = maybe_serialize( $event->get_data() );

			setcookie( $cookie_name, $cookie_value, time() + ( 30 * 24 * 3600 ), '/' );
			$_COOKIE[ $cookie_name ] = $cookie_value;
		}
	}
}

function tva_filter_order_tag_data( $data ) {

	if ( isset( $_COOKIE['top-ta-last-variation'] ) ) {

		$event = maybe_unserialize( wp_unslash( $_COOKIE['top-ta-last-variation'] ) );
	}

	if ( isset( $event ) && is_array( $event ) && ! empty( $event['variation_id'] ) ) {

		$data[] = $event['variation_id'];
	}

	return $data;
}

function tva_calculate_order_tag( $data = array() ) {

	$data = apply_filters( 'tva_order_tag_data', $data );

	$tag = implode( '|', $data );

	return $tag;
}

function tva_try_do_top_conversion( $raw_data, $data ) {

	if ( ! is_object( $data ) ) {
		return;
	}

	$tag = $data->order->tag;
	if ( strpos( $tag, '|' ) === false ) {
		return;
	}

	$chunks       = explode( '|', $tag );
	$variation_id = $chunks[1];

	$details = array(
		'revenue'   => $data->order->settled_gross,
		'goal_page' => 'sendowl',
	);

	if ( ! class_exists( 'Thrive_AB_Test_Manager' ) ) {
		return;
	}

	$test_manager = new Thrive_AB_Test_Manager();
	$tests        = $test_manager->get_tests( array( 'status' => 'running' ), 'object' );

	/** @var Thrive_AB_Test $test */
	foreach ( $tests as $test ) {

		if ( $test->goal_pages !== 'sendowl' ) {
			continue;
		}

		/** @var Thrive_AB_Test_Item $item */
		foreach ( $test->get_items() as $item ) {
			if ( (int) $item->variation_id === (int) $variation_id ) {
				Thrive_AB_Event_Manager::do_conversion( $test->id, $test->page_id, $variation_id, $details );
			}
		}
	}
}

/**
 * If user is not logged in by the WordPress then redirect it to the
 * redirect_to url
 *
 * @param $errors
 * @param $redirect_to
 *
 * @return mixed
 */
function tva_login_form_redirect( $errors, $redirect_to ) {

	if ( ! empty( $_REQUEST['tva_post'] ) && ! is_user_logged_in() ) {

		$redirect_url = add_query_arg( array( 'wrong_data' => '' ), $redirect_to );

		wp_redirect( $redirect_url );
		die;
	}

	return $errors;
}

/**
 * Handles the redirect only for TA logic
 *
 * @param $redirect_to           string
 * @param $requested_redirect_to string from request
 * @param $user                  WP_Error|WP_User
 *
 * @return mixed
 */
function tva_login_redirect( $redirect_to, $requested_redirect_to, $user ) {

	if ( ! empty( $_REQUEST['tva_post'] ) ) {

		if ( is_wp_error( $user ) ) {
			$redirect_to = add_query_arg( array( 'wrong_data' => '' ), $redirect_to );
		}

		wp_redirect( $redirect_to );
		die();
	}

	return $redirect_to;
}

add_filter( 'rest_request_parameter_order', 'tva_rest_request_parameter_order', 10, 2 );

/**
 * @param                 $order
 * @param WP_REST_Request $instance
 *
 * @return array
 */
function tva_rest_request_parameter_order( $order, $instance ) {

	$attr = $instance->get_attributes();

	if ( ! empty( $attr['callback'] ) && is_array( $attr['callback'] ) ) {
		foreach ( $attr['callback'] as $callback ) {
			if ( $callback === 'tva_upload_file' ) {
				$order[] = 'FILES';
			}
		}
	}

	return $order;
}

/**
 * Check if the user has TCB
 * IF don't skip license validation when lessons and checkout page are edited
 *
 * @param $skip_license
 *
 * @return bool
 */
function tva_tcb_skip_license_check( $skip_license ) {
	$has_tcb = TVE_Dash_Product_LicenseManager::getInstance()->itemActivated( TVE_Dash_Product_LicenseManager::TCB_TAG );

	if ( ! $has_tcb ) {
		if ( tva_is_post_editable_with_tar() || tva_is_ta_editor_page() ) {
			return true;
		}

		$is_architect_link = ! empty( $_REQUEST['tve'] ) && ! empty( $_REQUEST['action'] ) && $_REQUEST['action'] === 'architect';
		$is_post           = ! empty( $_REQUEST['post'] ) && is_numeric( $_REQUEST['post'] );

		if ( $is_architect_link && $is_post ) {
			$post = get_post( $_REQUEST['post'] );

			if ( ! $post instanceof WP_Post ) {
				return $skip_license;
			}

			if ( $post->post_type === TVA_Const::LESSON_POST_TYPE || TVA_Const::tva_is_checkout_page( $_REQUEST['post'] ) ) {
				$skip_license = true;
			}
		}
	}

	return $skip_license;
}

/**
 * Exclude the url of private courses from sitemap generated by Yoast
 *
 * @param array   $url    Array of URL parts.
 * @param string  $type   URL type.
 * @param WP_Term $object Data object for the URL.
 *
 * @return array
 */

function tva_wpseo_sitemap_entry( $url, $type, $object ) {
	$tva_term = $type === 'term' && $object instanceof WP_Term && $object->taxonomy === TVA_Const::COURSE_TAXONOMY;

	if ( ! $tva_term ) {
		return $url;
	}

	/**
	 * Yoast won't add an empty url into the sitemap, so to prevent that for private therms we empty the url here
	 */
	if ( get_term_meta( $object->term_id, 'tva_status', true ) === 'private' ) {
		$url = [];
	}

	return $url;
}

/**
 * Exclude demo posts generated by TA from sitemap generated by Yoast
 *
 * @param array $ids
 */
function tva_wpseo_exclude_from_sitemap_by_post_ids( $ids ) {
	if ( ! is_array( $ids ) ) {
		$ids = [];
	}

	return array_merge( $ids, TVA_Manager::get_demo_posts( [ 'fields' => 'ids' ] ) );
}

/**
 * Show the welcome message after a purchase trough sendowl
 *
 * @param TVA_Course_V2 $course
 */
function show_welcome_msg( $course ) {

	if ( ! TVA_SendOwl::is_connected() ) {
		return;
	}

	$show_msg = $course->get_product() instanceof Product && 'redirect' === tva_get_settings_manager()->factory( 'thankyou_page_type' )->get_value();
	$show_msg = $show_msg && ! empty( $_SERVER['HTTP_REFERER'] ) && false !== strpos( $_SERVER['HTTP_REFERER'], 'transactions.sendowl' );
	$show_msg = $show_msg || current_user_can( 'manage_options' ) && isset( $_REQUEST['show_welcome_msg'] );

	if ( true === $show_msg ) {
		$message = str_replace( '[course_name]', $course->name, tva_get_settings_manager()->factory( 'welcome_message' )->get_value() );

		echo '<div class="tva_thank_you">' . $message . '</div>';
	}
}

/**
 * Prevent TL forms to be displayed in template settings
 *
 * @param $skip
 *
 * @return bool
 */
function tva_thrive_leads_skip_request( $skip ) {

	if ( isset( $_REQUEST[ TVA_Const::TVA_FRAME_FLAG ] ) || Apprentice_Wizard::is_frontend() ) {
		$skip = true;
	}

	return $skip;
}

/**
 * @param                  $course
 * @param WP_REST_Request  $request
 */
function tva_update_yoast_term_tax_meta( $course, $request ) {

	if ( class_exists( 'WPSEO_Taxonomy_Meta', false ) && ! empty( $course['term_id'] ) && $request instanceof WP_REST_Request ) {
		WPSEO_Taxonomy_Meta::set_values( $course['term_id'], TVA_Const::COURSE_TAXONOMY, array(
			//facebook
			'wpseo_opengraph-image'       => $request->get_param( 'cover_image' ),
			'wpseo_opengraph-description' => $request->get_param( 'description' ),

			//twitter
			'wpseo_twitter-image'         => $request->get_param( 'cover_image' ),
			'wpseo_twitter-description'   => $request->get_param( 'description' ),
		) );
	}
}

/**
 * Set the path where the translation files are being kept
 */
function tva_load_plugin_textdomain() {
	$domain = 'thrive-apprentice';
	$locale = apply_filters( 'plugin_locale', get_locale(), $domain );
	$path   = 'thrive-apprentice/languages/';
	load_textdomain( $domain, WP_LANG_DIR . '/thrive/' . $domain . "-" . $locale . ".mo" );
	load_plugin_textdomain( $domain, false, $path );
}

/**
 * Add Redirect To Index Page in Login Element options
 *
 * @param array $actions
 *
 * @return array
 */
function tva_tcb_post_login_actions( $actions ) {

	$actions[] = array(
		'key'          => 'redirect_to_ta_index',
		'label'        => __( 'Redirect to Apprentice course index', 'thrive-apprentice' ),
		'icon'         => 'url',
		'preview_icon' => 'redirect-resp',
	);

	return $actions;
}

/**
 * Add index page url in response after user log in
 *
 * @param array $data
 *
 * @return mixed
 */
function tva_tcb_after_user_logged_in( $data ) {

	if ( ! empty( $data['after_submit'] ) && $data['after_submit'] === 'redirect_to_ta_index' ) {
		$data['external_redirect_url'] = tva_get_settings_manager()->factory( 'index_page' )->get_link(); // force redirection in TAr
	}

	return $data;
}

function tva_add_apprentice_post_types( $post_types ) {

	$post_types = array_merge( $post_types, array(
		TVA_Const::LESSON_POST_TYPE => TVA_Const::LESSON_POST_TYPE,
		TVA_Const::COURSE_POST_TYPE => TVA_Const::COURSE_POST_TYPE,
		TVA_Const::MODULE_POST_TYPE => TVA_Const::MODULE_POST_TYPE,
	) );

	return $post_types;
}


/**
 * Exclude index and register page for the search query from ttb
 *
 * @param array $args
 *
 * @return mixed
 */
function tva_theme_exclude_ta_pages( $args ) {
	$index_page            = tva_get_settings_manager()->factory( 'index_page' )->get_value();
	$register_page         = tva_get_settings_manager()->factory( 'register_page' )->get_value();
	$default_register_page = get_option( 'tva_default_register_page' );

	if ( ! empty( $index_page ) ) {
		$exclude_args    = empty( $args['exclude'] ) ? array() : $args['exclude'];
		$args['exclude'] = array_merge( $exclude_args, array( $index_page ) );
	}

	if ( ! empty( $register_page ) ) {
		$exclude_args    = empty( $args['exclude'] ) ? array() : $args['exclude'];
		$args['exclude'] = array_merge( $exclude_args, array( $register_page ) );
	}

	if ( ! empty( $default_register_page['ID'] ) ) {
		$exclude_args    = empty( $args['exclude'] ) ? array() : $args['exclude'];
		$args['exclude'] = array_merge( $exclude_args, array( $default_register_page['ID'] ) );
	}

	return $args;
}

/**
 * Check weather any post or page created or used by TA can be edited with TAR when it's inactive
 *
 * @param string $post_type
 * @param int    $post_id
 *
 * @return bool
 */
function tva_is_post_editable_with_tar( $post_type = null, $post_id = null ) {

	if ( empty( $post_type ) ) {
		$post_type = get_post_type();
	}

	if ( empty( $post_id ) ) {
		$post    = get_post();
		$post_id = true === $post instanceof WP_Post ? $post->ID : null;
	}

	return TVA_Const::LESSON_POST_TYPE === $post_type
		   || TVA_Course_Overview_Post::POST_TYPE === $post_type
		   || TVA_Access_Restriction::POST_TYPE === $post_type
		   || tva_get_settings_manager()->is_checkout_page( $post_id )
		   || tva_get_settings_manager()->is_login_page( $post_id )
		   || TVA_Access_Restriction::is_custom_redirect_page( $post_id );
}

/**
 * SUPP-8834 Fix a conflict with Rank Math SEO plugin.
 * The plugin modifies to early page title and is no longer correctly generated
 *
 * @param $title
 *
 * @return string
 */
function tva_pre_get_document_title( $title ) {

	global $post;

	$post_types = array( TVA_Const::LESSON_POST_TYPE, TVA_Const::MODULE_POST_TYPE );

	if ( true === $post instanceof WP_Post && in_array( $post->post_type, $post_types ) ) {
		$title = '';
	}

	return $title;
}

/**
 * Check weather or not cloud templates should be filtered. For TA pages no filter is needed
 *
 * @param bool $filter
 *
 * @return bool
 */
function tva_tcb_filter_landing_page_templates( $filter ) {

	$request = $_REQUEST;

	if ( empty( $request['post_id'] ) || 'tcb_editor_ajax' !== $request['action'] ) {
		return $filter;
	}

	if ( tva_is_ta_editor_page( (int) $request['post_id'] ) ) {
		$filter = false;
	}

	return $filter;
}

/**
 * Check if a given page is one used by TA and can be edited wit TAR
 *
 * @param null $page
 *
 * @return bool
 */
function tva_is_ta_editor_page( $page = null ) {

	if ( null === $page ) {
		$page = get_post();
	} elseif ( true !== $page instanceof WP_Post ) {
		$page = (int) $page;
	}

	$is_ta_page = tva_get_settings_manager()->is_checkout_page( $page );
	$is_ta_page = $is_ta_page || tva_get_settings_manager()->is_thankyou_page( $page );
	$is_ta_page = $is_ta_page || tva_get_settings_manager()->is_thankyou_multiple_page( $page );
	$is_ta_page = $is_ta_page || tva_get_settings_manager()->is_login_page( $page );

	return $is_ta_page;
}

/**
 * Disable Style Panel for TA pages
 *
 * @param bool $is_landing_page
 *
 * @return bool
 */
function tva_tcb_allow_central_style_panel( $is_landing_page ) {
	global $post;

	if ( tva_is_ta_editor_page( $post ) ) {
		$is_landing_page = false;
	}

	return $is_landing_page;
}

/**
 * Add TA shortcodes into TTB shortcodes list
 *
 * @param array $shordcodes
 *
 * @return array
 */
function tva_thrive_theme_shortcode_prefixes( $shordcodes ) {
	$shordcodes[] = 'tva_';

	return $shordcodes;
}

/**
 * Modify TC post url in frontend for TA courses
 *
 * @param string $url
 *
 * @return string
 */
function tva_tcm_post_url( $url ) {
	if ( is_tax( TVA_Const::COURSE_TAXONOMY ) ) {
		$obj = get_queried_object();
		$url = get_term_link( $obj->term_id, TVA_Const::COURSE_TAXONOMY );
	}

	return $url;
}

/**
 * Disable some ThriveApprentice post types for TOP A/B Testing
 *
 * @param bool   $is_allowed
 * @param string $post_type
 *
 * @return bool
 */
function tva_disable_ab_testing( $is_allowed, $post_type ) {

	if ( in_array( $post_type, [
		TVA_Const::LESSON_POST_TYPE,
		TVA_Const::MODULE_POST_TYPE,
		TVA_Access_Restriction::POST_TYPE,
		TVA_Course_Overview_Post::POST_TYPE,
		TVA_Course_Certificate::POST_TYPE,
		TVA_Course_Completed::POST_TYPE,
		TVA_Const::ASSESSMENT_POST_TYPE,
	] ) ) {
		$is_allowed = false;
	}

	return $is_allowed;
}

/**
 * @param string          $content
 * @param Thrive_Template $context
 *
 * @return string
 */
function tva_thrive_theme_template_content( $content, $context ) {

	global $post;

	$excluded_post_types = array( TVA_Const::LESSON_POST_TYPE, TVA_Const::MODULE_POST_TYPE );

	if ( $post instanceof WP_Post && in_array( $post->post_type, $excluded_post_types ) ) {
		$content = '';
	}

	return $content;
}

/**
 * @param array           $data
 * @param int             $post_id
 * @param TCB_Editor_Ajax $context
 *
 * @return mixed
 */
function tva_tcb_lazy_load_data( $data, $post_id, $context ) {

	if ( isset( $_REQUEST['structure_course_ids'] ) && is_array( $_REQUEST['structure_course_ids'] ) ) {
		/**
		 * This is called for Course Element.
		 * For any course element that is on page (Editor_page) we need the structure of the course to update the structure control
		 */
		$courses = TVA_Course_V2::get_items( array( 'include' => $_REQUEST['structure_course_ids'] ) );

		foreach ( $courses as $course ) {
			$data['tva_courses'][ $course->get_id() ] = array(
				'structure' => $course->load_structure(),
			);
		}
	}

	return $data;
}

/**
 * Supports query strings to display filters in front-end
 * Supports array: ?filters[]=0&filters[]=2
 * or
 * String: ?filters=0,2
 *
 * @return array
 */
function tva_get_frontend_filters() {
	$filters = array();

	if ( isset( $_REQUEST['filters'] ) ) {
		if ( is_string( $_REQUEST['filters'] ) ) {
			$filters = explode( ',', urldecode( $_REQUEST['filters'] ) );
		} elseif ( is_array( $_REQUEST['filters'] ) ) {
			$filters = $_REQUEST['filters'];
		}

		$filters = array_filter( $filters, 'is_numeric' );
	}

	return $filters;
}

/**
 * Add Apprentice relevant data for the current user
 *
 * @param array $user_details
 *
 * @return array
 */
function tva_extra_user_data( $user_details ) {

	if ( empty( $user_details['user_id'] ) ) {
		return $user_details;
	}

	$courses    = TVA_Course_V2::get_items( array( 'status' => 'publish' ) );
	$has_access = array();

	foreach ( $courses as $course ) {
		if ( $course->has_access() ) {
			$has_access[] = $course->get_id();
		}
	}

	$user_details['tva_last_lesson_viewed'] = get_user_meta( $user_details['user_id'], 'tva_last_lesson_viewed' );
	$user_details['tva_last_module_viewed'] = get_user_meta( $user_details['user_id'], 'tva_last_module_viewed' );
	$user_details['tva_last_course_viewed'] = get_user_meta( $user_details['user_id'], 'tva_last_course_viewed' );
	$user_details['tva_courses']            = $has_access;

	return $user_details;
}

/**
 * We do not change the template here, just update some user data.
 * We do it here because we have to be sure that no redirect has occurred
 *
 * @param $template
 *
 * @return mixed
 */
function tva_set_user_data( $template ) {

	if ( ! is_user_logged_in() || ! tva_is_apprentice() ) {
		return $template;
	}

	$user_id = get_current_user_id();

	if ( is_tax( TVA_Const::COURSE_TAXONOMY ) ) {

		update_user_meta( $user_id, 'tva_last_course_viewed', get_queried_object()->term_id );
	} elseif ( is_single() ) {

		$post_id = get_the_ID();

		switch ( get_post_type() ) {

			case TVA_Const::MODULE_POST_TYPE:
				update_user_meta( $user_id, 'tva_last_module_viewed', $post_id );

				break;
			case TVA_Const::LESSON_POST_TYPE:
				update_user_meta( $user_id, 'tva_last_lesson_viewed', $post_id );

				break;
			default:
				break;
		}
	}

	return $template;
}

/**
 * Execute a file in php context and return the output
 *
 * @param string $file_path
 * @param array  $data data to pass to the template
 *
 * @return false|string
 */
function tva_get_file_contents( $file_path, $data = array() ) {

	ob_start();

	include TVA_Const::plugin_path( $file_path );

	$content = ob_get_contents();
	ob_end_clean();

	return $content;
}

/**
 * Prevent wishlist level logic on apprentice pages
 *
 * @param $content
 *
 * @return mixed|null
 */
function tva_wl_content_for_level( $content ) {

	if ( is_tax( TVA_Const::COURSE_TAXONOMY ) ) {
		$content = null;
	}

	return $content;
}

/**
 * Called by corn, handles course scheduling
 *
 * @param int $term_id
 */
function tva_publish_future_term( $term_id ) {

	$term = new TVA_Course_V2( (int) $term_id );

	$term->publish();
}

/**
 * Called by cron
 * Publish all parents of a lesson
 *
 * @param $post_id
 */
function tva_publish_parents( $post_id ) {
	$post = get_post( $post_id );
	$stop = true !== $post instanceof WP_Post;
	$stop = $stop || TVA_Const::LESSON_POST_TYPE !== $post->post_type;
	$stop = $stop || (int) $post->post_parent === 0;
	$stop = $stop || 'publish' !== $post->post_status;

	if ( $stop ) {
		return;
	}

	TVA_Manager::review_status( (int) $post->post_parent );
}

/**
 * Get gmt date starting from a given date. Timezone won't be taken into account here
 *
 * @param string $date
 *
 * @return string
 */
function tva_get_post_date_gmt( $date ) {

	if ( empty( $date ) ) {
		return '';
	}

	$date_data = rest_get_date_with_gmt( $date );
	$date_gmt  = '';

	if ( ! empty( $date_data[1] ) ) {
		$date_gmt = $date_data[1];
	}

	return $date_gmt;
}

/**
 * Enable features for Thrive Dashboard
 *
 * @param array $enabled
 *
 * @return array
 */

function tva_enable_dashboard_features( $enabled ) {
	$enabled['smart_site']           = true;
	$enabled['coming-soon']          = true;
	$enabled['api_connections']      = true;
	$enabled['notification_manager'] = true;

	return $enabled;
}

/**
 * Fix older gravatar URLs that contain `s=96` ( size too small and it looks blurry ).
 * These are saved in course meta
 *
 * @param string $avatar_url
 *
 * @return string
 */
function tva_fix_gravatar_url( $avatar_url ) {
	return preg_replace( '/([?&])s=96/', '$1s=256', $avatar_url );
}

/**
 * Checks if the current page link is a preview one or not
 *
 * @return bool
 */
function tva_is_preview() {

	return ( isset( $_GET["preview"] ) && $_GET["preview"] === 'true' );
}

/**
 * Orders the courses by order flag array before sending them to front
 *
 * @param array $courses
 *
 * @return array
 */
function tva_order_courses_by_order_flag( $courses ) {
	return array_values( array_filter(
		array_map( static function ( $value ) use ( $courses ) {
			$found_course = array_values(
				array_filter( $courses, static function ( $value2 ) use ( $value ) {
					return $value2['id'] === $value;
				} )
			);

			return is_array( $found_course ) && count( $found_course ) > 0 ? $found_course[0] : array();
		}, TVA_Course_V2::get_ordered_items_indexes() )
	) );
}

/**
 * Removes the trash control from edit.php page list view for index page
 *
 * @param array   $actions
 * @param WP_Post $page
 */
add_filter( 'page_row_actions', static function ( $actions, $page ) {

	if ( tva_get_settings_manager()->is_index_page( $page ) ) {
		unset( $actions['trash'] );
	}

	return $actions;
}, 10, 2 );

/**
 * Gets the author of the course instead of the course overview author
 *
 * @param int     $author_id
 * @param WP_Post $post
 */
add_filter( 'tcb_get_post_author', function ( $author_id, $post ) {
	if ( isset( $post ) && $post instanceof WP_Post ) {
		$term = wp_get_post_terms( $post->ID, TVA_Const::COURSE_TAXONOMY );
	}
	if ( isset( $term[0] ) ) {
		$course = new TVA_Course_V2( $term[0] );
		$author = $course->get_author();

		if ( isset( $author ) ) {
			$author_id = $author->get_details()['ID'];
		}
	}

	return $author_id;
}, 10, 2 );

/**
 * Filter courses array to contain only published ones
 *
 * @param array $course_ids
 *
 * @return array
 */
function tva_filter_published_courses_ids( $course_ids ) {
	return array_filter(
		$course_ids,
		function ( $course_id ) {
			$course = new TVA_Course_V2( $course_id );

			return in_array( $course->get_status(), [ 'publish', 'hidden', 'archived' ] );
		}
	);
}

/**
 * Create a new user if it doesn't exist and check for specific email trigger
 *
 * @param $email
 * @param $email_trigger
 * @param $first_name
 * @param $last_name
 *
 * @return false|int|WP_Error|WP_User
 */
function tva_ensure_new_user( $email, $email_trigger = '', $first_name = '', $last_name = '' ) {
	$display_name = trim( $first_name . ' ' . $last_name );

	if ( 0 === strlen( $display_name ) ) {
		$display_name = substr( $email, 0, strpos( $email, '@' ) );
	}
	//check if there are templates set for trigger
	$email_template = tva_email_templates()->check_templates_for_trigger( $email_trigger );
	if ( false !== $email_template ) {
		tva_email_templates()->trigger_process( $email_template );
	}

	$user_login = sanitize_user( $email, true );
	$pass       = wp_generate_password( 12, false );
	$user_data  = array(
		'user_email'   => $email,
		'user_login'   => $user_login,
		'display_name' => $display_name,
		'first_name'   => $first_name,
		'last_name'    => $last_name,
		'user_pass'    => $pass,
		'role'         => 'subscriber',
	);
	$user       = wp_insert_user( $user_data );

	//because of wp_insert_user() notification has to be triggered manually
	if ( false !== $email_template ) {
		$GLOBALS['tva_user_pass_generated'] = true;
		wp_send_new_user_notifications( $user, 'both' );
	}

	if ( ! is_wp_error( $user ) ) {
		$user = get_user_by( 'id', $user );
		$user->set_role( 'subscriber' );
		$user->first_name = empty( $first_name ) ? $display_name : $first_name;
		$user->last_name  = $last_name;
	}

	return $user;
}
