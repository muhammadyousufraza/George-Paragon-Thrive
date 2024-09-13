<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\TTB;

use DateTime;
use Exception;
use TVA\Access\History_Table;
use TVA\Access\Migration;
use TVA\Assessments\TVA_User_Assessment;
use TVA\Product_Migration;
use TVA\Stripe\Credentials;
use TVA_Const;
use TVA_Course_V2;
use TVA_Order;
use TVA_Product;
use TVE\Reporting\Logs;
use function random_int;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Reset
 *
 * @package TVA\TTB
 * @project : thrive-apprentice
 */
class Reset {
	public static function init() {
		add_submenu_page( '', null, null, 'manage_options', 'tva-reset', [ __CLASS__, 'menu_page' ] );

		add_action( 'wp_ajax_tva_progress_reset', [ __CLASS__, 'progress_reset' ] );
		add_action( 'wp_ajax_tva_skin_reset', [ __CLASS__, 'skin_reset' ] );
		add_action( 'wp_ajax_tva_skin_sanity_check_reset', [ __CLASS__, 'skin_reset_sanity_check' ] );
		add_action( 'wp_ajax_tva_products_reset', [ __CLASS__, 'products_reset' ] );
		add_action( 'wp_ajax_tva_remove_demo_content', [ __CLASS__, 'remove_demo_content' ] );
		add_action( 'wp_ajax_tva_create_demo_content', [ __CLASS__, 'create_demo_content' ] );
		add_action( 'wp_ajax_tva_access_history_remove', [ __CLASS__, 'access_history_reset' ] );
		add_action( 'wp_ajax_tva_access_history_index', [ __CLASS__, 'access_history_index' ] );
		add_action( 'wp_ajax_tva_reporting_logs_remove', [ __CLASS__, 'reporting_logs_reset' ] );
		add_action( 'wp_ajax_tva_reset_verification_page', [ __CLASS__, 'reset_verification_page' ] );
		add_action( 'wp_ajax_tva_reporting_logs_generate', [ __CLASS__, 'reporting_logs_generate' ] );
		add_action( 'wp_ajax_tva_remove_user_assessments', [ __CLASS__, 'remove_user_assessments' ] );
		add_action( 'wp_ajax_tva_remove_course_assessments', [ __CLASS__, 'remove_course_assessments' ] );
		add_action( 'wp_ajax_tva_reset_stripe', [ __CLASS__, 'remove_stripe' ] );
		add_action( 'wp_ajax_tva_fix_members', [ __CLASS__, 'fix_members' ] );
	}

	/**
	 * Removes options from the database
	 * - so that the verification page is created again automatically
	 *
	 * @return void
	 */
	public static function reset_verification_page() {
		delete_option( 'tva_setting_certificate_verification' );
		delete_option( 'tva_setting_certificate_validation_page' );
	}

	/**
	 * Admin menu page for the reset
	 */
	public static function menu_page() {
		include TVA_Const::plugin_path( 'ttb-bridge/templates/reset-page.php' );
	}

	/**
	 * Admin menu page for skin sanity check reset
	 *
	 * @return void
	 */
	public static function skin_reset_sanity_check() {
		if ( is_user_logged_in() && TVA_Product::has_access() && Check::is_end_user_site() ) {
			Main::reset_sanity_check();
		}
	}

	/**
	 * Skin Reset
	 */
	public static function skin_reset() {
		if ( is_user_logged_in() && TVA_Product::has_access() && Check::is_end_user_site() ) {
			update_option( 'tva_default_skin', 0 );
			Main::show_legacy_design(); // make sure this is shown
			Main::set_use_builder_templates( 0 );

			tva_palettes()->delete_palette();

			/**
			 * Reset also the Master HSL color code to the default one (ShapeShift color)
			 */
			tva_palettes()->reset_master_hsl();

			$cloud_skins = Main::get_all_skins( false, false );

			foreach ( $cloud_skins as $skin ) {
				$skin->remove();
				wp_delete_term( $skin->term_id, SKIN_TAXONOMY );
			}

			//We need to reset also the share color setting
			tva_get_settings_manager()->factory( 'share_ttb_color' )->set_value( 0 );
		}
	}

	/**
	 * Reset the admin progress
	 */
	public static function progress_reset() {
		if ( is_user_logged_in() && TVA_Product::has_access() ) {
			setcookie( 'tva_learned_lessons', '', 1, '/' );
			$_COOKIE['tva_learned_lessons'] = '';

			delete_user_meta( get_current_user_id(), 'tva_learned_lessons' );
		}
	}

	/**
	 * Removes the demo content from the site
	 */
	public static function remove_demo_content() {
		if ( is_user_logged_in() && TVA_Product::has_access() ) {
			tva_update_demo_content( false );
		}
	}

	/**
	 * Re-creates demo content
	 */
	public static function create_demo_content() {
		if ( is_user_logged_in() && TVA_Product::has_access() ) {
			tva_update_demo_content();
		}
	}

	/**
	 * Remove all access history data
	 *
	 * @return void
	 */
	public static function access_history_reset() {
		if ( is_user_logged_in() && TVA_Product::has_access() ) {
			Migration::revert_migration();
		}
	}

	/**
	 * Try to add index to acccess histroy table
	 *
	 * @return void
	 */
	public static function access_history_index() {
		if ( is_user_logged_in() && TVA_Product::has_access() ) {
			global $wpdb;

			$index_name  = 'IDX_user_product_course';
			$table_name  = $wpdb->prefix . 'tva_' . History_Table::get_table_name();
			$str_columns = 'user_id, product_id, course_id';

			$items = $wpdb->get_results( "show index from {$table_name} where key_name = '{$index_name}'", ARRAY_A );

			if ( count( $items ) === 0 ) {
				$wpdb->query( "CREATE INDEX `{$index_name}` ON `{$table_name}` ({$str_columns})" );
				exit( 'index created' );
			} else {
				exit( 'index exists' );
			}
		}
	}

	/**
	 * Reset products created from migration
	 */
	public static function products_reset() {
		if ( is_user_logged_in() && TVA_Product::has_access() ) {
			Product_Migration::revert_migrate();
		}
	}

	/**
	 * Remove logs generated by us
	 *
	 * @return void
	 */
	public static function reporting_logs_reset() {
		if ( is_user_logged_in() && TVA_Product::has_access() ) {
			Logs::get_instance()->remove_by( 'text_field_1', 'dummy_data' );
		}
	}

	/**
	 * Generate logs
	 *
	 * @throws Exception
	 */
	public static function reporting_logs_generate() {
		if ( is_user_logged_in() && TVA_Product::has_access() ) {
			$start_date = new DateTime( 'now' );
			$start_date->modify( '-6 months' );
			$end_date = new DateTime( 'now' );

			$students = array_map( static function ( $user ) {
				return $user['ID'];
			}, History_Table::get_instance()->get_all_students() );

			$courses = array_map( static function ( $course ) {
				return [
					'id'      => $course->get_id(),
					'lessons' => array_map( static function ( $lesson ) {
						return $lesson->ID;
					}, $course->get_all_lessons() ),
				];
			}, TVA_Course_V2::get_items() );

			foreach ( $students as $user_id ) {
				/* find a random date for "today" */
				$today = new DateTime();
				$today->setTimestamp( random_int( $start_date->getTimestamp(), $end_date->getTimestamp() ) );

				global $wpdb;
				$student_courses = array_map( static function ( $item ) {
					return $item['course_id'];
				}, $wpdb->get_results( 'SELECT course_id FROM ' . $wpdb->prefix . 'tva_' . History_Table::get_table_name() . ' GROUP BY course_id', ARRAY_A ) );

				/* shuffle courses so we won't always start with the same one */
				shuffle( $courses );

				foreach ( $courses as $course ) {

					$lesson_count = count( $course['lessons'] );
					$course_id    = $course['id'];

					if ( $lesson_count === 0 || $today > $end_date || ! in_array( $course_id, $student_courses ) ) {
						continue;
					}

					static::insert_generated_data( 'tva_course_start', $today->format( 'Y-m-d H:i:s' ), $course_id, $course_id, $user_id );

					foreach ( $course['lessons'] as $lesson_id ) {
						if ( $today < $end_date ) {
							/* start lesson */
							static::insert_generated_data( 'tva_lesson_start', $today->format( 'Y-m-d H:i:s' ), $lesson_id, $lesson_id, $user_id, $course_id );
						}

						/* time until a lesson is completed */
						$today->modify( sprintf( '+%d days', random_int( 1, 7 ) ) );

						if ( random_int( 1, 24042 ) % 5 === 0 ) {
							/* something bad happens and we don't continue lessons */
							$today->modify( '+100 years' );
						} elseif ( $today < $end_date ) {
							/* complete lesson */
							static::insert_generated_data( 'tva_lesson_complete', $today->format( 'Y-m-d H:i:s' ), $lesson_id, $lesson_id, $user_id, $course_id );

							/* some time passes */
							$today->modify( sprintf( '+%d days', random_int( 1, 3 ) ) );
						}
					}

					if ( $today < $end_date ) {
						static::insert_generated_data( 'tva_course_finish', $today->format( 'Y-m-d H:i:s' ), $course_id, $course_id, $user_id );

						/* some time passes after we finish the course */
						$today->modify( sprintf( '+%d days', random_int( 1, 10 ) ) );
					}
				}
			}
		}
	}

	/**
	 * @param $event_type
	 * @param $date
	 * @param $item
	 * @param $post_id
	 * @param $user
	 * @param $int_field_1
	 *
	 * @return void
	 */
	public static function insert_generated_data( $event_type, $date, $item, $post_id, $user, $int_field_1 = 0 ) {
		global $wpdb;

		$wpdb->insert( $wpdb->prefix . Logs::TABLE_NAME, [
			'event_type'   => $event_type,
			'created'      => $date,
			'item_id'      => $item,
			'post_id'      => $post_id,
			'user_id'      => $user,
			'int_field_1'  => $int_field_1,
			'text_field_1' => 'dummy_data',
		] );
	}

	/**
	 * Remove all user assessments
	 *
	 * @return void
	 */
	public static function remove_user_assessments() {
		if ( is_user_logged_in() && TVA_Product::has_access() ) {
			$assessments = get_posts(
				[
					'post_type'      => TVA_User_Assessment::POST_TYPE,
					'posts_per_page' => - 1,
					'post_status'    => 'any',
					'fields'         => 'ids',
				]
			);
			foreach ( $assessments as $assessment ) {
				wp_delete_post( $assessment, true );
			}
		}
	}

	/**
	 * Remove all course assessments
	 *
	 * @return void
	 */
	public static function remove_course_assessments() {
		if ( is_user_logged_in() && TVA_Product::has_access() ) {
			$assessments = get_posts(
				[
					'post_type'      => TVA_Const::ASSESSMENT_POST_TYPE,
					'posts_per_page' => - 1,
					'post_status'    => 'any',
					'fields'         => 'ids',
				]
			);
			foreach ( $assessments as $assessment ) {
				wp_delete_post( $assessment, true );
			}
			static::remove_user_assessments();
		}
	}

	public static function remove_stripe() {
		if ( is_user_logged_in() && TVA_Product::has_access() ) {
			Credentials::disconnect();
		}
	}

	public static function fix_members() {
		if ( is_user_logged_in() && TVA_Product::has_access() ) {
			TVA_Order::fix_orders_without_history();
		}
	}
}
