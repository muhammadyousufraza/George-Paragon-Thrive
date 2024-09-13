<?php

/**
 * Class TVA_Course_Post
 * - post which helps editing the course overview
 *
 * @property int ID
 */
final class TVA_Course_Overview_Post implements JsonSerializable {

	use TVA_Course_Post;

	/**
	 * Post type used to store course overview content
	 */
	const POST_TYPE = 'tva_course_overview';

	/**
	 * @var TVA_Course_V2
	 */
	protected $_course;

	/**
	 * @var WP_Post which holds the content for course overview page and
	 *              - is editable with TAr
	 */
	protected $_post;

	/**
	 * @var TVA_Course_Overview_Post
	 */
	protected static $_instance;

	/**
	 * TVA_Course_Post constructor.
	 */
	private function __construct() {

		$this->_hooks();
	}

	/**
	 * @return TVA_Course_Overview_Post
	 */
	public static function instance() {

		if ( ! self::$_instance ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * This method should be executed only once
	 * - that's why it is called on singleton construct
	 */
	private function _hooks() {

		/**
		 * Overwrite global post with the course overview post when:
		 * - user edits the overview course
		 * - visitor is on course overview page(it was previously edited with TAr)
		 */
		add_action(
			'wp',
			static function () {

				global $post, $posts;

				//didn't use tve_editor()->is_inner_frame because it reads from global post and we do not have it yet, we have to set it!
				if ( true === get_queried_object() instanceof WP_Term && ! empty( $_GET['tva'] ) && ! empty( $_GET['tcbf'] ) ) {
					$overview_post = tva_course()->get_overview_post( true )->get_post();
				} elseif ( tva_course()->get_wp_term() && ! is_single() ) {
					$overview_post = tva_course()->has_overview_post();
				}

				if ( false === is_search() && isset( $overview_post ) && $overview_post instanceof WP_Post ) {
					TVA_Db::setCommentsStatus();
					$post  = $overview_post;
					$posts = [ $post ];

					/**
					 * Do not let TAr reset the wo query on course overview page
					 */
					add_filter( 'tcb_reset_query_for_inner_frame', '__return_false' );

					/**
					 * Include default styles on course overview post
					 * - prints <style class="tve_global_style"/>
					 */
					add_filter( 'tcb_output_default_styles', '__return_true' );
				}
			},
			4 //after tva_course() is initialized and before TAr reads the global post
		);

		/**
		 * BEGIN:Course Overview <-> WordPress Comments Compatibility
		 */
		add_filter( 'thrive_theme_comments_modify_post', static function ( $post ) {
			/**
			 * Backwards compatibility issue:
			 * On course overview the comments are assigned to a single post that is located in an option: tva_get_hidden_post()
			 * We need to have this done also for visual editing
			 */
			if ( TVA_Course_Overview_Post::POST_TYPE === get_post_type() && \TVA\TTB\Main::uses_builder_templates() ) {
				$post = tva_get_hidden_post();
			}

			return $post;
		} );

		/**
		 * Modify author email when a user places a comment to course overview page.
		 * The author of the course should be the one from course form in admin screen
		 */
		add_filter( 'comment_notification_recipients', static function ( $emails, $comment_id ) {

			if ( \TVA\TTB\Main::uses_builder_templates() ) {

				$comment      = get_comment( $comment_id );
				$comment_post = get_post( $comment->comment_post_ID );
				$course       = null;

				if ( TVA_Const::COURSE_POST_TYPE === $comment_post->post_type ) {

					$comment_term_id = ! empty( $_POST['comment_term_ID'] ) ? $_POST['comment_term_ID'] : get_comment_meta( $comment_id, 'tva_course_comment_term_id', true );

					if ( ! empty( $comment_term_id ) && is_numeric( $comment_term_id ) ) {
						$course = new TVA_Course_V2( (int) $comment_term_id );
					}
				} elseif ( in_array( $comment_post->post_type, [ TVA_Const::LESSON_POST_TYPE, TVA_Const::MODULE_POST_TYPE ] ) ) {
					$course = TVA_Post::factory( $comment_post )->get_course_v2();
				}

				if ( $course instanceof TVA_Course_V2 ) {
					$emails = array( $course->get_author()->get_user()->user_email );
				}
			}

			return $emails;
		}, 10, 2 );

		add_filter( 'comments_open', static function ( $open, $post_id ) {
			//check if the post associated with post overview has comments opened
			/**
			 * @var null|WP_Post $thrive_theme_comments_post
			 */
			global $thrive_theme_comments_post;

			if ( ! empty( $thrive_theme_comments_post ) && $post_id === (int) get_option( 'tva_course_hidden_post_id' ) && ! \Thrive_Utils::is_inner_frame() ) {
				$open = comments_open( $thrive_theme_comments_post );
			}

			return $open;
		}, 10, 2 );

		add_filter( 'comment_form_submit_field', static function ( $html_markup, $args ) {
			/**
			 * @var WP_Post $post
			 */
			global $post;

			/**
			 * @var null|WP_Post $thrive_theme_comments_post
			 */
			global $thrive_theme_comments_post;

			if ( ! empty( $thrive_theme_comments_post ) && $post->ID === (int) get_option( 'tva_course_hidden_post_id' ) ) {
				$terms   = wp_get_post_terms( $thrive_theme_comments_post->ID, TVA_Const::COURSE_TAXONOMY );
				$term_id = (int) $terms[0]->term_id;

				$html_markup .= '<input type="hidden" name="redirect_to" value="' . get_term_link( $term_id ) . '" />';
				$html_markup .= '<input type="hidden" name="comment_term_ID" value="' . $term_id . '" />';
			}

			return $html_markup;
		}, 10, 2 );

		add_filter( 'comments_template_query_args', static function ( $comment_args ) {
			/**
			 * @var WP_Post $post
			 */
			global $post;

			/**
			 * @var null|WP_Post $thrive_theme_comments_post
			 */
			global $thrive_theme_comments_post;

			if ( ! empty( $thrive_theme_comments_post ) && $post->ID === (int) get_option( 'tva_course_hidden_post_id' ) ) {
				$terms = wp_get_post_terms( $thrive_theme_comments_post->ID, TVA_Const::COURSE_TAXONOMY );

				$comment_args['meta_query'] = array(
					array(
						'key'     => 'tva_course_comment_term_id',
						'value'   => (int) $terms[0]->term_id,
						'compare' => '=',
					),
				);
			}

			return $comment_args;
		} );

		/**
		 * END:Course Overview <-> WordPress Comments Compatibility
		 */

		/**
		 * Set the global post again because Yoast resets the query on wp_head action
		 */
		add_action(
			'wp_head',
			static function () {

				if ( tva_course()->get_wp_term() && ! is_single() ) {
					$overview_post = tva_course()->has_overview_post();
					if ( $overview_post instanceof WP_Post ) {
						global $post;
						$post = $overview_post;
					}
				}
			}
		);

		/**
		 * Filter preview link of course overview post
		 * to point to course url
		 */
		add_filter(
			'preview_post_link',
			static function ( $link ) {

				global $post;

				if ( $post instanceof WP_Post && TVA_Course_Overview_Post::POST_TYPE === $post->post_type ) {
					$terms  = wp_get_post_terms( $post->ID, TVA_Const::COURSE_TAXONOMY );
					$course = new TVA_Course_V2( $terms[0] );
					$link   = $course->get_overview_post()->get_preview_url();
				}

				return $link;
			}
		);

		/**
		 * Filter the post_image so that for
		 * - course overview post it sets the course cover image
		 * - lesson it sets lesson cover image
		 * - module it sets the course cover image
		 */
		add_filter(
			'tcb_editor_javascript_params',
			static function ( $tve_path_params, $post_id, $post_type ) {

				if ( TVA_Course_Overview_Post::POST_TYPE === $post_type ) {
					$tve_path_params['post_image']['featured'] = ! empty( tva_course()->get_cover_image() ) ? tva_course()->get_cover_image() : $tve_path_params['post_image']['featured'];
					$tve_path_params['post_image']['author']   = tva_course()->get_author()->get_avatar();
				}

				if ( TVA_Const::LESSON_POST_TYPE === $post_type ) {
					$lesson                                    = new TVA_Lesson( $post_id );
					$cover_image                               = $lesson->cover_image;
					$featured_image                            = ! empty( $cover_image ) ? $cover_image : tva_course()->cover_image;
					$tve_path_params['post_image']['featured'] = ! empty( $featured_image ) ? $featured_image : $tve_path_params['post_image']['featured'];
					$tve_path_params['post_image']['author']   = tva_course()->get_author()->get_avatar();
				}

				if ( TVA_Const::MODULE_POST_TYPE === $post_type ) {
					$featured_image                            = tva_course()->cover_image;
					$tve_path_params['post_image']['featured'] = ! empty( $featured_image ) ? $featured_image : $tve_path_params['post_image']['featured'];
					$tve_path_params['post_image']['author']   = tva_course()->get_author()->get_avatar();
				}

				return $tve_path_params;
			},
			10,
			3
		);

		$post_type = static::POST_TYPE;

		/**
		 * Special functionality for getting templates for this post type - it needs to return templates built for the Course Overview archive instead of this post type
		 */
		add_filter(
			"thrive_{$post_type}_get_templates_args",
			/**
			 * @param array $args
			 *
			 * @return array
			 */
			static function ( $args ) {
				$args['meta_query'] = [
					[
						'key'   => THRIVE_PRIMARY_TEMPLATE,
						'value' => THRIVE_ARCHIVE_TEMPLATE,
					],
					[
						'key'   => THRIVE_SECONDARY_TEMPLATE,
						'value' => TVA_Const::COURSE_TAXONOMY,
					],
				];

				return $args;
			}
		);

		/**
		 * Modify the returned template list used for localization in TAr editor - make sure template `type` field is correctly set to the current post type
		 */
		add_filter(
			"thrive_{$post_type}_templates",
			/**
			 * @param array $templates template list
			 *
			 * @return array
			 */
			static function ( $templates ) {

				array_walk( $templates, static function ( &$template ) {
					$template['type'] = static::POST_TYPE;
				} );

				return $templates;
			}
		);

		/**
		 * Allows TVE Page Events on Course Overview
		 */
		add_filter(
			'tcb_overwrite_event_scripts_enqueue',
			function ( $overwrite ) {

				if ( self::POST_TYPE === get_post_type() ) {
					$overwrite = true;
				}

				return $overwrite;
			},
			11
		);

		/**
		 * Thrive Ultimatum doesn't work with Course Overview because WP doesn't see it as single post or page
		 * but implementing this filter from TU will except it from the check
		 *
		 * @return true if the global post it's a Course Overview
		 */
		add_filter( 'tve_ult_shortcode_render_exception', static function ( $is_exception ) {
			if ( self::POST_TYPE === get_post_type() ) {
				$is_exception = true;
			}

			return $is_exception;
		} );
	}

	/**
	 * @return null|WP_Post
	 */
	public function get_post() {

		return $this->_post;
	}

	/**
	 * @param TVA_Course_V2 $course
	 *
	 * @return TVA_Course_Overview_Post
	 */
	public function set_course( $course ) {

		if ( true === $course instanceof TVA_Course_V2 ) {
			$this->_course = $course;
		}

		return $this;
	}

	/**
	 * For current post gets a preview user which points to
	 * course URL
	 *
	 * @return string
	 */
	public function get_preview_url() {

		$url = '';

		if ( $this->_course instanceof TVA_Course_V2 ) {
			$url = $this->_course->get_preview_url();
		}

		return $url;
	}

	/**
	 * Magic get
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function __get( $key ) {

		$value = null;

		if ( $this->_post instanceof WP_Post ) {
			$value = $this->_post->$key;
		}

		return $value;
	}

	/**
	 * Ensure there is a post for current course
	 *
	 * @return false|WP_Post
	 */
	public function ensure_post() {

		if ( false === $this->_course instanceof TVA_Course_V2 ) {
			return false;
		}

		$_post = $this->_course->has_overview_post();

		if ( false === $_post instanceof WP_Post ) {

			$id = wp_insert_post(
				array(
					'post_type'  => self::POST_TYPE,
					'post_title' => 'Course overview post',
				)
			);

			if ( false === is_wp_error( $id ) ) {

				$content = tva_get_file_contents(
					'templates/course-overview/default-content.php',
					array(
						'course'            => $this->_course,
						'template_settings' => tva_get_settings_manager()->factory( 'template' )->get_value(),
					)
				);

				/**
				 * put some defaults metas on post so that TAr knows how to work
				 */
				update_post_meta( $id, 'tve_updated_post', $content );
				update_post_meta( $id, 'tve_custom_css', tva_get_file_contents( 'templates/course-overview/default-style.php' ) );
				update_post_meta( $id, 'tcb2_ready', 1 );
				update_post_meta( $id, 'tcb_editor_enabled', 1 );

				update_term_meta( $this->_course->term_id, 'tva_overview_post_id', $id );
				wp_set_object_terms( $id, $this->_course->term_id, TVA_Const::COURSE_TAXONOMY );
				$_post = get_post( $id );
			}
		}

		$this->_post = $_post;

		return $this->_post;
	}

	/**
	 * Duplicates the course overview onto the course given as parameter
	 *
	 * @param TVA_Course_V2 $new_course
	 *
	 * @return false|WP_Post
	 */
	public function duplicate( $new_course ) {
		$old_course_overview = $this->get_post();
		$new_course_overview = $new_course->overview_post->ensure_post();

		$this->duplicate_post_meta( $old_course_overview, $new_course_overview );

		return $new_course_overview;
	}

	/**
	 * Checks the `the_update_post` meta of course overview post for a string
	 * to see if it contains dynamic video element
	 *
	 * @return bool
	 */
	public function has_dynamic_video_in_content() {

		$has     = false;
		$content = get_post_meta( $this->get_post()->ID, 'tve_updated_post', true );

		if ( $content ) {
			$has = strpos( $content, 'data-in-content="1"' ) !== false;
		}

		return $has;
	}

	/**
	 * Used on localization
	 *
	 * @return array
	 */
	#[\ReturnTypeWillChange]
	public function jsonSerialize() {

		$data = array(
			'preview_url' => $this->get_preview_url(),
		);

		if ( $this->_post ) {

			$data = array_merge(
				$data,
				array(
					'post_id'  => $this->_post->ID,
					'edit_url' => tcb_get_editor_url( $this->_post->ID ),
				)
			);
		}

		return $data;
	}
}
