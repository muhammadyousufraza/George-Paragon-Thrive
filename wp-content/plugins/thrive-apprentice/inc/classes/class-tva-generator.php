<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

use TVA\Product;

/**
 * Class TVA_Generation <br>
 * Has utilities for :
 * - course generation
 * - product generations
 * - member generation
 * - completion generation
 *
 * @property TVA_Generator $instance                    The instance of the class for singleton use
 * @property array         $data                        An array containing the data for generation
 * @property string[]      $random_words                An array containing random words for titles/names generation
 * @property string[]      $random_paragraphs           An array containing random paragraphs retrieved from lorem ipsum
 * @property bool          $block_completion_generation Whether the completion generation should be blocked, true when completion prepared data is invalid
 *
 */
class TVA_Generator {

	protected static $instance;
	const MIN_PERCENTAGE = 0;
	const MAX_PERCENTAGE = 100;
	const DEFAULTS       = [
		'courseCount'             => 0,
		'minModules'              => 0,
		'maxModules'              => 0,
		'minChapters'             => 0,
		'maxChapters'             => 0,
		'minLessons'              => 0,
		'maxLessons'              => 0,
		'productsCount'           => 0,
		'coursePercentageMin'     => 0,
		'coursePercentageMax'     => 0,
		'membersCount'            => 0,
		'productPercentageMin'    => 0,
		'productPercentageMax'    => 0,
		'completionPercentageMin' => 0,
		'completionPercentageMax' => 0,
	];
	private $data;
	private $random_words;
	private $random_paragraphs;
	private $block_completion_generation = false;

	/**
	 * Private constructor, does basic setup of the class properties
	 */
	private function __construct() {
		$this->random_words      = self::get_random_words_array();
		$this->random_paragraphs = self::get_random_paragraphs_array( 20 );
		$this->data              = self::DEFAULTS;
	}

	/**
	 * @return TVA_Generator The instance of the object
	 */
	public static function get_instance() {
		if ( empty( static::$instance ) ) {
			static::$instance = new TVA_Generator();
		}

		return static::$instance;
	}

	/**
	 * Prepares data for course generation
	 *
	 * @param array $data should contain all data needed for course generation
	 *
	 * @return void
	 */
	public function prepare_data( $data ) {
		$this->data = self::DEFAULTS;

		foreach ( $data as $key => $value ) {
			if ( isset( $this->data[ $key ] ) ) {
				$this->data[ $key ] = $value;
			}
		}

		$this->validate_data();
	}

	/**
	 * Generates courses with a random number of lessons/chapters/modules based on the properties prepared
	 *
	 * Should be used after data for course generation was prepared.
	 *
	 * @return array The generated courses or empty array if no courses have been generated
	 * @see TVA_Generator::prepare_for_course_generation()
	 */
	public function generate_courses() {
		$course_list          = array();
		$current_course_count = TVA_Course_V2::get_items( array(), true );
		mt_srand( time() );

		for ( $i = 0; $i < $this->data['courseCount']; $i ++ ) {
			/**
			 * Add some random data to the course
			 * The order will start from current max order a.k.a. $current_course_count
			 */
			$name = $this->generate_random_title();
			$data = array(
				'name'        => $name,
				'cover_image' => self::generate_robo_hash_image( $name ),
				'excerpt'     => $this->generate_random_paragraphs_string( 1 ),
				'order'       => $current_course_count + $i + 1,
			);

			$course    = new TVA_Course_V2( $data );
			$course_id = $course->save();

			$this->try_perform_next_generation( 'modules', $course );

			TVA_Course_V2::assign_author( (int) $course_id, get_currentuserinfo() );
			update_term_meta( (int) $course_id, 'tva_generated', '1' );

			$course = new TVA_Course_V2( $course_id );
			$course->publish();
			$course->load_structure();
			$course_list[] = $course->jsonSerialize();
		}

		return $course_list;
	}

	/**
	 * Delete the generated courses
	 *
	 * @return int[] A list of the deletes courses ids
	 */
	public static function delete_generated_courses() {
		$course_list = TVA_Course_V2::get_items(
			array(
				'meta_key'   => 'tva_generated',
				'meta_value' => '1',
			)
		);
		$deleted     = array();
		foreach ( $course_list as $course ) {
			if ( get_term_meta( $course->ID, 'tva_generated', true ) === '1' ) {
				$course->delete();
				$deleted[] = $course->ID;
			}
		}

		return $deleted;
	}

	/**
	 * Deletes all the generated customers
	 *
	 * @return TVA_Customer[] An array containing all the deleted customers
	 */
	public static function delete_generated_customers() {
		$customer_list    = TVA_Customer::get_customers(
			array(
				'meta_key'   => 'tva_generated',
				'meta_value' => '1',
			)
		);
		$customer_id_list = [];
		require_once( ABSPATH . 'wp-admin/includes/user.php' );
		foreach ( $customer_list as $customer ) {
			$customer_id_list[] = $customer->get_id();
			wp_delete_user( $customer->get_id() );
		}

		return $customer_list;
	}

	/**
	 * Deletes all the generated completion
	 *
	 * @return TVA_Customer[] An array containing all the affected customers
	 */
	public static function delete_generated_completion() {
		$customer_list = TVA_Customer::get_customers(
			array(
				'meta_key'   => 'tva_generated',
				'meta_value' => '1',
			)
		);

		foreach ( $customer_list as $customer ) {
			$customer->get_courses();
			$course_ids = $customer->get_course_ids();
			foreach ( $course_ids as $course_id ) {
				$lessons_ids = $customer->get_course_completed_lessons_for_student( $course_id );

				$customer->bulk_reset_items(
					array_keys( $lessons_ids ),
					$course_id
				);
			}
		}

		return $customer_list;
	}

	/**
	 * Deletes all the generated products
	 *
	 * @return TVA\Product[] The array of affected products
	 */
	public static function delete_generated_products() {
		$product_list      = TVA\Product::get_items(
			array(
				'meta_key'   => 'tva_generated',
				'meta_value' => '1',
			)
		);
		$affected_products = [];
		foreach ( $product_list as $product ) {
			if ( get_term_meta( $product->get_id(), 'tva_generated', true ) === '1' ) {
				$affected_products[] = $product;
				$product->delete();
			}
		}

		return $product_list;
	}

	/**
	 * Generates products based on the class properties prepared
	 *
	 * The products will be generated with courses attached
	 *
	 * @return TVA_Product[] a list containing the generated products
	 *
	 * @see TVA_Generator::prepare_data()
	 */
	public function generate_products() {
		$course_list          = TVA_Course_V2::get_items(
			array(
				'meta_key'   => 'tva_generated',
				'meta_value' => '1',
			)
		);
		$generated_products   = [];
		$default_course_count = empty( $course_list ) ? 0 : 1;
		mt_srand( time() );

		for ( $i = 0; $i < $this->data['productsCount']; $i ++ ) {
			$random_number             = mt_rand(
				sizeof( $course_list ) * $this->data['coursePercentageMin'] / self::MAX_PERCENTAGE,
				sizeof( $course_list ) * $this->data['coursePercentageMax'] / self::MAX_PERCENTAGE
			);
			$course_count_per_products = $random_number > 0 ? $random_number : $default_course_count;
			$random_indexes            = array_rand( $course_list, (int) $course_count_per_products );
			$product_courses           = array();

			if ( is_int( $random_indexes ) ) {
				$random_indexes = [ $random_indexes ];
			}

			/**
			 * collect the course ids
			 */
			foreach ( $random_indexes as $index ) {
				$product_courses[] = $course_list[ $index ]->get_id();
			}

			$product = $this->create_product( $product_courses );

			if ( $product instanceof TVA\Product ) {
				$generated_products[] = $product;
			}
		}

		return $generated_products;
	}

	/**
	 * Generates random members based on the class properties
	 *
	 * @return void
	 * @see TVA_Generator::prepare_data()
	 */
	public function generate_members() {
		$email_template        = tva_email_templates()->check_template_for_any_trigger();
		$products              = Product::get_items(
			array(
				'meta_key'   => 'tva_generated',
				'meta_value' => '1',
			)
		);
		$default_product_count = empty( $products ) ? 0 : 1;
		mt_srand( time() );

		for ( $i = 0; $i < $this->data['membersCount']; $i ++ ) {
			$name  = $this->generate_random_title();
			$email = self::get_random_email( $name );
			/**
			 * Make sure that the user wasn't generated before
			 */
			while ( get_user_by( 'email', $email ) instanceof WP_User ) {
				$name  = $this->generate_random_title();
				$email = $this->get_random_email( $name );
			}

			TVA_Customer_Manager::insert_customer(
				array(
					'name'  => $name,
					'email' => $email,
				),
				[],
				array(
					'email_template' => $email_template,
					'send_email'     => false,
					'order_type'     => TVA_Order::MANUAL,
				)
			);

			$user                     = get_user_by( 'email', $email );
			$random_number            = mt_rand(
				sizeof( $products ) * $this->data['productPercentageMin'] / self::MAX_PERCENTAGE,
				sizeof( $products ) * $this->data['productPercentageMax'] / self::MAX_PERCENTAGE
			);
			$product_count_per_member = $random_number > 0 ? $random_number : $default_product_count;
			$keys                     = array_rand( $products, $product_count_per_member );

			if ( is_int( $keys ) ) {
				$keys = [ $keys ];
			}

			foreach ( $keys as $key ) {
				TVA_Customer::enrol_user_to_product(
					$user->ID,
					$products[ $key ]->get_id()
				);
				$order = new TVA_Order( $products[ $key ]->get_order() );
				$order->save();
			}

			add_user_meta( $user->ID, 'tva_generated', '1' );
		}
	}

	/**
	 * Generates random completion for all generated members based on the prepared data
	 *
	 * @return void
	 * @see TVA_Generator::prepare_for_completion_generation()
	 */
	public function generate_completion() {
		if ( ! $this->block_completion_generation ) {

			$generated_customers = TVA_Customer::get_customers(
				array(
					'meta_key'   => 'tva_generated',
					'meta_value' => '1',
				)
			);
			mt_srand( time() );

			foreach ( $generated_customers as $customer ) {
				$courses = $customer->get_courses();

				foreach ( $courses as $course ) {
					$lessons_to_complete = array();

					if ( $course instanceof TVA_Course_V2 ) {
						$lessons              = $course->get_all_lessons();
						$default_lesson_count = sizeof( $lessons ) > 0 ? 1 : 0;
						$random_number        = mt_rand(
							sizeof( $lessons ) * $this->data['completionPercentageMin'] / self::MAX_PERCENTAGE,
							sizeof( $lessons ) * $this->data['completionPercentageMax'] / self::MAX_PERCENTAGE
						);
						$lesson_count         = $random_number > 0 ? $random_number : $default_lesson_count;

						foreach ( $lessons as $lesson ) {
							if ( $lesson_count <= 0 ) {
								break;
							}
							$lessons_to_complete[] = $lesson->ID;
							$lesson_count --;
						}

						$customer->bulk_complete_lessons( $lessons_to_complete, $course->get_id() );
					}
				}
			}
		}
	}

	/**
	 * Tries to perform the next generation depending by type : modules, chapters, lessons
	 *
	 * @param string                                $type   the type of generation that will be performed
	 * @param TVA_Course_V2|TVA_Module| TVA_Chapter $parent The parent to which the next generated posts will be assigned to
	 *
	 * @return void
	 */
	private function try_perform_next_generation( $type, $parent ) {
		switch ( $type ) {
			case 'modules':
				$module_count = mt_rand( $this->data['minModules'], $this->data['maxModules'] );

				if ( $module_count > 0 ) {
					$this->generate_modules( $module_count, $parent );
				} else {
					$this->try_perform_next_generation( 'chapters', $parent );
				}
				break;
			case 'chapters':
				$chapter_count = mt_rand( $this->data['minChapters'], $this->data['maxChapters'] );

				if ( $chapter_count > 0 ) {
					$this->generate_chapters( $chapter_count, $parent );
				} else {
					$this->try_perform_next_generation( 'lessons', $parent );
				}
				break;
			case 'lessons':
				$lesson_count = mt_rand( $this->data['minLessons'], $this->data['maxLessons'] );
				$this->generate_lessons( $lesson_count, $parent );
				break;
			default:
				/**
				 * do nothing, this will never be reached in current flow
				 */
		}
	}

	/**
	 * Generates $module_count modules based on the class properties prepared and assigns them to $course
	 *
	 * @param int           $module_count The number of modules to be generated
	 * @param TVA_Course_V2 $course       The course to which the module will be assigned
	 *
	 * @return void
	 */
	private function generate_modules( $module_count, $course ) {

		for ( $order = 0; $order < $module_count; $order ++ ) {
			$module = new TVA_Module(
				$this->generate_post_data( $order, 1 )
			);

			try {
				$module->save();
			} catch ( Exception $e ) {
				continue;
			}

			$this->try_perform_next_generation( 'chapters', $module );

			$module->assign_to_course( $course->get_id() );
		}
	}

	/**
	 * Generates $chapter_count chapters based on the class properties prepared and assigns them to $parent
	 *
	 * @param int                      $chapter_count The number of chapters to be generated
	 * @param TVA_Course_V2|TVA_Module $parent        The parent of the chapter
	 *
	 * @return void
	 */
	private function generate_chapters( $chapter_count, $parent ) {
		for ( $order = 0; $order < $chapter_count; $order ++ ) {
			$data    = $this->generate_post_data( $order );
			$chapter = new TVA_Chapter( $data );

			if ( $parent instanceof TVA_Module ) {
				$chapter->post_parent = $parent->ID;
			}

			try {
				$chapter->save();
				$this->try_perform_next_generation( 'lessons', $chapter );
				if ( $parent instanceof TVA_Course_V2 ) {
					$chapter->assign_to_course( $parent->get_wp_term() );
				}
			} catch ( Exception $e ) {
				continue;
			}
		}
	}

	/**
	 * Generates $lesson_count lessons and assigns them to $parent
	 *
	 * @param int                                  $lesson_count The number of lessons to be generated
	 * @param TVA_Course_V2|TVA_Module|TVA_Chapter $parent       The parent of the lessons
	 *
	 * @return void
	 */
	private function generate_lessons( $lesson_count, $parent ) {
		for ( $order = 0; $order < $lesson_count; $order ++ ) {
			$lesson = new TVA_Lesson(
				$this->generate_post_data( $order, 1 )
			);

			$lesson->post_parent = $parent instanceof TVA_Course_V2 ? '' : $parent->ID;

			try {
				$lesson->save();
				if ( $parent instanceof TVA_Course_V2 ) {
					$lesson->assign_to_course( $parent->get_id() );
				}
			} catch ( Exception $e ) {
				continue;
			}
		}
	}

	/**
	 * Creates a product with that has courses assigned through the courses_ids list
	 *
	 * @param array $courses_ids A list of courses ids to be assigned to the product that is going to be created
	 *
	 * @return string|TVA_Product|WP_Error The generated product
	 */
	private function create_product( $courses_ids ) {
		/**
		 * make a post containing a list of the ids of the courses that will be assigned to the product
		 */
		$post = new TVA_Post(
			array(
				'post_content' => serialize(
					array(
						array(
							'content_type' => 'term',
							'content'      => 'tva_courses',
							'field'        => 'title',
							'operator'     => '===',
							'value'        => $courses_ids,
						),
					)
				),
				'post_title'   => 'content set',
				'post_status'  => 'publish',
				'post_type'    => 'tvd_content_set',
			)
		);

		try {
			$post->save();
		} catch ( Exception $exception ) {
			return 'A server side error occurred';
		}
		$product = new TVA\Product(
			array(
				'name'  => $this->generate_random_title() . $post->ID,
				'_term' => $post,
			)
		);
		$product = $product->save();
		wp_set_object_terms( $post->ID, $product->get_id(), 'tva_product' );
		update_term_meta( (int) $product->get_id(), 'tva_generated', '1' );

		return $product;
	}

	/**
	 * Generates post data
	 *
	 * @param int  $order                   The order of the post
	 * @param int  $excerpt_paragraph_count The number of paragraphs for the excerpt
	 * @param bool $with_title              Whether title should be generated for the post
	 * @param bool $publish                 Whether the post should be published
	 *
	 * @return array Containing the generated data
	 */
	private function generate_post_data( $order = null, $excerpt_paragraph_count = 0, $with_title = true, $publish = true ) {
		$data = array();

		if ( $with_title ) {
			$data['post_title'] = $this->generate_random_title();
		}
		if ( $excerpt_paragraph_count ) {
			$data['post_excerpt'] = $this->generate_random_paragraphs_string( $excerpt_paragraph_count );
		}
		if ( isset( $order ) ) {
			$data['order'] = $order;
		}
		if ( $publish ) {
			$data['post_status'] = 'publish';
		} else {
			$data['post_status'] = 'draft';
		}

		return $data;
	}

	/**
	 * Validate the data
	 *
	 * @return void
	 */
	public function validate_data() {

		if ( ! self::valid_percentage_interval( $this->data['productPercentageMin'], $this->data['productPercentageMax'] ) ) {
			$this->data['membersCount'] = self::DEFAULTS['membersCount'];
		}
		if ( ! self::valid_percentage_interval( $this->data['coursePercentageMin'], $this->data['coursePercentageMax'] ) ) {
			$this->data['productsCount'] = self::DEFAULTS['productsCount'];
		}

		$this->block_completion_generation = ! self::valid_percentage_interval( $this->data['completionPercentageMin'], $this->data['completionPercentageMax'] );
		if (
			! self::is_interval( $this->data['minModules'], $this->data['maxModules'] ) ||
			! self::is_interval( $this->data['minChapters'], $this->data['maxChapters'] ) ||
			! self::is_interval( $this->data['minLessons'], $this->data['maxLessons'] )
		) {
			$this->data['courseCount'] = self::DEFAULTS['courseCount'];
		}
	}

	/**
	 * Generates a random title containing two words from the random_words property
	 *
	 * @return string The random title
	 */
	private function generate_random_title() {
		$rez = array_rand( $this->random_words, 2 );

		return $this->random_words[ $rez[0] ] . ' ' . $this->random_words[ $rez[1] ];
	}

	/**
	 * Creates a string containing $count paragraphs from the random_paragraphs property
	 *
	 * @param int $count The number of paragraphs
	 *
	 * @return string The string created
	 */
	private function generate_random_paragraphs_string( $count ) {
		$indexes = array_rand( $this->random_paragraphs, $count );
		$rez     = '';
		$indexes = array( $indexes );

		foreach ( $indexes as $index ) {
			$rez .= $this->random_paragraphs[ $index ] . '\n';
		}

		return $rez;
	}

	/**
	 * Creates an array of random words
	 *
	 * @return false|string[] the created array
	 */
	private static function get_random_words_array() {
		$txt = 'Balloon Car Disease Evening Gas Horse Juice Lock Nail Parrot Raincoat Soccer Train Yacht Apple Brother Denmark Energy France Helmet Jelly Lighter Monkey Oxygen Quill Scooter Tomato Window Airport Belgium Crayon Egg Flower Guitar Insurance Lamp Manchester Notebook Plastic Rose Telephone Vegetable Advertisement Beard China Dress Fish Grass Ice Kite Magazine Nigeria Pizza Rocket Sweden Van Animal Branch Daughter Egypt Forest Hamburger Island Leather Match Oil Potato Sandwich Tent Wall Ambulance Boy Crowd Eggplant Football Hair Iron Lawyer Market Ocean Portugal Russia Television Vulture Banana Caravan Doctor Eye Girl Hospital Kangaroo London Napkin Pencil Refrigerator Spoon Truck Yak Australia Candle Dinner England Garden Honey Jordan Lizard Motorcycle Painting Rainbow Shoe Traffic Xylophone Battery Carpet Dog Family Glass House King Lunch Needle Piano Restaurant Stone Uganda Zebra Answer Breakfast Death Elephant Fountain Helicopter Jackal Library Microphone Orange Queen School Thailand Whale Actor Beach Cartoon Dream Finland Gold Hydrogen Kitchen Machine Nest Pillow River Sugar Umbrella Zoo Afternoon Bed Church Easter Flag Greece Insect Knife Magician Night Planet Room Teacher Vase Army Camera Diamond Engine Furniture Holiday Jewellery Lion Morning Oyster Rain Shampoo Toothbrush Wire';

		return explode( ' ', $txt );
	}

	/**
	 * Makes a request to the lorem ipsum api and converts it to a list of $count paragraphs
	 *
	 * @param int $count The number of paragraphs
	 *
	 * @return array|false|string[] The list of paragraphs, false in case of failure
	 */
	private static function get_random_paragraphs_array( $count ) {
		$paragraphs = file_get_contents( "http://loripsum.net/api/$count/short/plaintext" );
		$paragraphs = htmlspecialchars_decode( $paragraphs );
		$rez        = preg_split( '/\n\n/', $paragraphs );
		unset( $rez[ $count ] );

		return $rez;
	}

	/**
	 * Creates an email address from the $name
	 *
	 * @param int $name
	 *
	 * @return string The generated email address
	 */
	private static function get_random_email( $name ) {
		$name  = explode( ' ', $name );
		$email = '';

		if ( sizeof( $name ) > 1 ) {
			$email = $name[0] . '@' . $name[1] . '.com';
		}

		return $email;
	}

	/**
	 * Creates a link to a random image from https://picsum.photos/ photos using the current timestamp
	 *
	 * @return string The link to the image
	 */
	private static function generate_robo_hash_image( $name ) {
		$arg = str_replace( ' ', '%20', $name );

		return "https://robohash.org/$arg";
	}

	/**
	 * Checks whether the two given values form a valid percentage interval
	 *
	 * @param int $lower The lower limit
	 * @param int $upper The upper limit
	 *
	 * @return bool Whether the two given values form a valid percentage interval
	 */
	private static function valid_percentage_interval( $lower, $upper ) {
		return ( 0 !== $upper ) && self::valid_percentage( $lower ) && self::valid_percentage( $upper ) && self::is_interval( $lower, $upper );
	}

	/**
	 * Checks if a given pair of integers represents a valid interval
	 *
	 * @param int $lower The lower limit
	 * @param int $upper The upper limit
	 *
	 * @return bool Whether the pair of numbers represents a valid interval
	 */
	private static function is_interval( $lower, $upper ) {
		return $upper >= $lower;
	}

	/**
	 * Checks whether $value is a valid percentage, between 0 and 100
	 *
	 * @param int $value The value to be evaluated
	 *
	 * @return bool Whether the value is a valid percentage
	 */
	private static function valid_percentage( $value ) {
		return self::in_range( $value, self::MIN_PERCENTAGE, self::MAX_PERCENTAGE );
	}

	/**
	 * Checks whether $value is between $min and $max
	 *
	 * @param int $value The value to be evaluated
	 * @param int $min   The lower boundary
	 * @param int $max   The upper boundary
	 *
	 * @return bool Whether the value is in given range
	 */
	private static function in_range( $value, $min, $max ) {
		return is_int( $value ) && $value >= $min && $value <= $max;
	}

}
