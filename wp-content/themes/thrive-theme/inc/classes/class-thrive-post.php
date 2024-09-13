<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Thrive_Post
 */
class Thrive_Post {
	/**
	 * Use general singleton methods
	 */
	use Thrive_Singleton;

	/**
	 * Use the shortcuts for post meta setters and getters
	 */
	use Thrive_Post_Meta;

	/**
	 * Option name where we store the element visibility data
	 *
	 * @var string
	 */
	const VISIBILITY_META_KEY = 'thrive_element_visibility';

	/**
	 * Class used to hide a section from post settings
	 */
	const HIDDEN_ELEMENT_CLASS = 'hide-section';

	/**
	 * Custom action used to clone a post / page
	 */
	const CLONE_ACTION = 'thrive_clone_item';

	/**
	 * Default meta values - only visibility for now, but can be extended easily.
	 *
	 * @var array
	 */
	private static $default_meta
		= [
			self::VISIBILITY_META_KEY => [
				'top'            => '',
				'sidebar'        => '',
				'bottom'         => '',
				'header'         => '',
				'footer'         => '',
				'post_title'     => '',
				'featured_image' => '',
				'featured_video' => '',
				'featured_audio' => '',
				'author_box'     => '',
				'breadcrumbs'    => '',
				'comments'       => '',
			],
			/* default value for the assigned template - 0 means that the post/page inherits templates normally */
			THRIVE_META_POST_TEMPLATE => 0,
		];

	/**
	 * Current post id
	 *
	 * @var false|int|null
	 */
	public $ID;

	/**
	 * Current post
	 *
	 * @var array|WP_Post|null
	 */
	private $post;

	/**
	 * Thrive_Post constructor.
	 *
	 * @param $id
	 */
	public function __construct( $id = null ) {
		$this->post = get_post( $id );
		$this->ID   = empty( $id ) ? get_the_ID() : $id;
	}

	/**
	 * Return post attribute if it exists
	 *
	 * @param $attr
	 *
	 * @return null|mixed
	 */
	public function get( $attr ) {
		if ( property_exists( $this->post, $attr ) ) {
			return $this->post->$attr;
		}

		return null;
	}

	/**
	 * Check if this page is a landing page.
	 *
	 * @return array|bool|mixed
	 */
	public function is_landing_page() {
		return $this->get_meta( 'tve_landing_page' );
	}

	/**
	 * Returns true if the element is visible, false if it's not.
	 *
	 * @param $element
	 *
	 * @return bool
	 */
	public function is_visible( $element ) {
		return $this->get_visibility( $element ) !== 'hide';
	}

	/**
	 * @return bool
	 */
	public function is_amp_disabled() {
		$amp_meta = $this->get_meta( THRIVE_META_POST_AMP_STATUS );

		return isset( $amp_meta ) ? $amp_meta === 'disabled' : false;
	}

	/**
	 * Return the visibility of the element. This can return 'hide', 'show', or '' ( empty ).
	 *
	 * @param $element
	 *
	 * @return mixed
	 */
	public function get_visibility( $element ) {
		$visibility_meta = $this->get_visibility_meta();

		return isset( $visibility_meta[ $element ] ) ? $visibility_meta[ $element ] : '';
	}

	/**
	 * Get only the visibility meta array.
	 *
	 * @return array|mixed
	 */
	public function get_visibility_meta() {
		return $this->get_meta( static::VISIBILITY_META_KEY );
	}

	/**
	 * When localizing the visibility meta to JS, 'translate' what the empty defaults mean for each case.
	 * For example, for the select controls, '' (empty) means 'inherit'. For the toggle controls, '' means 'show'.
	 *
	 * @return array|mixed
	 */
	public function localize_visibility_meta() {
		$visibility_meta = $this->get_visibility_meta();

		foreach ( $visibility_meta as $key => $value ) {
			if ( array_key_exists( $key, static::get_visibility_config( 'sections' ) ) ) {
				/* for sections, empty means 'inherit' */
				$visibility_meta[ $key ] = empty( $value ) ? 'inherit' : $value;
			} else {
				/* for non-section elements, empty means 'show' */
				$visibility_meta[ $key ] = empty( $value ) ? 'show' : $value;
			}
		}

		return $visibility_meta;
	}

	/**
	 * Update the visibility meta values.
	 * Parse the array of values first, so we only save necessary data.
	 *
	 * @param array $visibility_meta
	 */
	public function set_visibility_meta( $visibility_meta ) {
		/* merge with the existing values ( both defaults and saved values ) for backward and forward compatibility */
		$visibility_meta = array_merge( $this->get_visibility_meta(), $visibility_meta );

		/* filter out the keys that we don't want to save ( nothing in the DB means 'inherit' for sections, and 'show' for elements ) */
		$visibility_meta = array_filter( $visibility_meta, static function ( $value, $key ) {
			/* if this is a section, save 'show' or 'hide', but don't save 'inherit' */
			if ( array_key_exists( $key, static::get_visibility_config( 'sections' ) ) ) {
				$save_this = in_array( $value, [ 'show', 'hide' ] );
			} else {
				/* for non-section elements, only save if it's set to 'hide' */
				$save_this = ( $value === 'hide' );
			}

			return $save_this;
		}, ARRAY_FILTER_USE_BOTH );

		$this->set_meta( static::VISIBILITY_META_KEY, $visibility_meta );
	}

	/**
	 * Return the meta values for the given key. If a value is not found, returns the default values ( or empty/false if there are no defaults ).
	 *
	 * @param string $meta_key
	 *
	 * @return array|mixed|bool
	 */
	public function get_meta( $meta_key ) {
		if ( ! isset( $this->meta[ $meta_key ] ) ) {
			$meta_value = get_post_meta( $this->ID, $meta_key, true );

			if ( empty( static::$default_meta[ $meta_key ] ) ) {
				/* if there are no default values, just use the value we got from get_post_meta - this can be the value itself, or empty, or false */
				$this->meta[ $meta_key ] = $meta_value;

			} elseif ( is_array( static::$default_meta[ $meta_key ] ) ) {
				/* if the defaults are an array of values, merge the meta with them in order to make sure this is forwards compatible with changes we make in the future */
				$this->meta[ $meta_key ] = array_merge( static::$default_meta[ $meta_key ], empty( $meta_value ) || ! is_array( $meta_value ) ? [] : $meta_value );

			} else {
				/* for non-array values, assign the default only when the meta value doesn't exist already */
				$this->meta[ $meta_key ] = empty( $meta_value ) ? static::$default_meta[ $meta_key ] : $meta_value;
			}
		}

		return $this->meta[ $meta_key ];
	}

	/**
	 * Returns true if we should display this element on the page, and returns false if we shouldn't.
	 * The elements are always visible inside the theme templates + preview, regardless of what is set inside the post meta.
	 * This should not be used for sections, only for post components!
	 *
	 * @param string $element
	 * @param array  $classes
	 *
	 * @return bool
	 */
	public function is_element_visible( $element, &$classes ) {
		/* visible by default */
		$is_visible = true;

		$is_architect = is_singular() && ! Thrive_Utils::is_inner_frame() && ! ( Thrive_Utils::is_preview() && Thrive_Utils::inner_frame_id() );

		if ( $is_architect ) {
			$is_visible = $this->is_visible( $element );
		}

		if ( ! $is_visible && Thrive_Utils::is_architect_editor() ) {
			/* in the editor, we render this normally and add a class so it can be toggled */
			$classes[]  = static::HIDDEN_ELEMENT_CLASS;
			$is_visible = true;
		}

		return $is_visible;
	}

	/**
	 * Get all the existing page templates.
	 * This can be extended to get more types of secondary templates by using an array of post types instead of page.
	 *
	 * @return array|int[]|WP_Post[]
	 */
	public function get_all_templates() {
		$templates = [];

		/* arguments in order to get all the custom page templates for this skin */
		$args = [
			'posts_per_page' => - 1,
			'post_type'      => THRIVE_TEMPLATE,
			'tax_query'      => [ thrive_skin()->build_skin_query_params() ],
			'order'          => 'ASC',
			'meta_query'     => [
				[
					'key'   => THRIVE_PRIMARY_TEMPLATE,
					'value' => $this->is_homepage() ? THRIVE_HOMEPAGE_TEMPLATE : THRIVE_SINGULAR_TEMPLATE,
				],
				[
					'key'   => THRIVE_SECONDARY_TEMPLATE,
					'value' => $this->get( 'post_type' ),
				],
			],
		];

		$post_type = $this->get( 'post_type' );

		/**
		 * Allows modifying the template query arguments.
		 *
		 * @param array       $args get_posts() arguments to filter
		 * @param Thrive_Post $post the Thrive_Post instance
		 */
		$args = apply_filters( "thrive_{$post_type}_get_templates_args", $args, $this );

		$all_templates = get_posts( $args );

		if ( empty( $all_templates ) ) {
			unset( $args['meta_query'][1] );
			$all_templates = get_posts( $args );
		}

		/* only keep the ID and the name; add some extra information we need in JS */
		foreach ( $all_templates as $template ) {
			$id = (int) $template->ID;
			/* get all templates with the same post type or the custom ones */
			$templates[] = [
				'ID'                 => $id,
				'name'               => $template->post_title,
				'type'               => get_post_meta( $id, THRIVE_SECONDARY_TEMPLATE, true ),
				'type_label'         => __( 'Custom Design', 'thrive-theme' ),
				'extra_icon_classes' => 'icon-page',
				'is_default'         => $template->default,
				'format'             => $template->format,
			];
		}

		/**
		 * Filter the final template list, ready to be localized.
		 *
		 * @param array       $templates array of templates to filter
		 * @param Thrive_Post $post      the Thrive_Post instance
		 */
		return apply_filters( "thrive_{$post_type}_templates", $templates, $this );
	}

	/**
	 * Get the template loaded for this post
	 *
	 * @return Thrive_Template
	 */
	public function get_template() {
		$default   = null;
		$templates = $this->get_all_templates();

		if ( ! empty( $templates ) ) {
			/* If we have one that is already selected we will use that one, otherwise we will use the default one */
			foreach ( $templates as $template ) {
				if ( ! empty( $template['selected'] ) ) {
					$chosen_one = $template;
					break;
				}

				$post        = $this->post;
				$post_format = get_post_format( $post->ID );

				/* get_post_format() returns false when the value is 'standard' */
				if ( ! $post_format ) {
					$post_format = 'standard';
				}

				if ( $template['is_default'] === '1' && $post_format === $template['format'] ) {
					$default = $template;
				}
			}
		}

		return empty( $chosen_one ) ? $default : $chosen_one;
	}

	/**
	 * Get theme template url from the post
	 *
	 * @return string
	 */
	public function get_theme_template_url() {
		$template = $this->get_template();

		return ( empty( $template ) ) ? '' : tcb_get_editor_url( $template['ID'] );
	}

	/**
	 * Save the visibility meta from the form data only when saving WP Content ( Architect save is handled elsewhere ).
	 * This relies on getting information from $_POST data.
	 */
	public function save_visibility_meta_from_wp() {
		$visibility_meta = [];

		/* get each visibility setting from the form $_POST and add it to an array, then save */
		foreach ( static::$default_meta[ static::VISIBILITY_META_KEY ] as $key => $value ) {
			$visibility_identifier = 'thrive_' . $key . '_visibility';

			/* set 'hide' by default because unchecked checkboxes send empty values */
			$visibility_meta[ $key ] = empty( $_POST[ $visibility_identifier ] ) ? 'hide' : $_POST[ $visibility_identifier ];
		}

		/* save the new visibility settings */
		$this->set_visibility_meta( $visibility_meta );
	}

	/**
	 * If there is nothing set in the post template key, delete it. Else, set the new value
	 */
	public function save_template_meta_from_wp() {
		if ( empty( $_POST[ THRIVE_META_POST_TEMPLATE ] ) ) {
			$this->delete_meta( THRIVE_META_POST_TEMPLATE );
		} else {
			$this->set_meta( THRIVE_META_POST_TEMPLATE, (int) $_POST[ THRIVE_META_POST_TEMPLATE ] );
		}

		if ( empty( $_POST[ THRIVE_META_POST_AMP_STATUS ] ) ) {
			$this->delete_meta( THRIVE_META_POST_AMP_STATUS );
		} else {
			$this->set_meta( THRIVE_META_POST_AMP_STATUS, $_POST[ THRIVE_META_POST_AMP_STATUS ] );
		}
	}

	/**
	 * Get the element visibility config data, grouped depending on the element types.
	 * This returns all the config data, unless a type is provided.
	 *
	 * @param string  $type
	 * @param boolean $get_all
	 *
	 * @return array
	 */
	public static function get_visibility_config( $type = '', $get_all = false ) {
		$config = apply_filters( 'thrive_theme_visibility_config', [
			'sections' => [
				'top'     => [
					'view'       => 'Top',
					'identifier' => '.top-section',
					'label'      => __( 'Top Section', 'thrive-theme' ),
				],
				'sidebar' => [
					'view'       => 'Sidebar',
					'identifier' => '.sidebar-section',
					'label'      => __( 'Sidebar Section', 'thrive-theme' ),
				],
				'bottom'  => [
					'view'       => 'Bottom',
					'identifier' => '.bottom-section',
					'label'      => __( 'Bottom Section', 'thrive-theme' ),
				],
				'header'  => [
					'view'       => 'Header',
					'identifier' => '.thrv_header',
					'label'      => __( 'Header', 'thrive-theme' ),
				],
				'footer'  => [
					'view'       => 'Footer',
					'identifier' => '.thrv_footer',
					'label'      => __( 'Footer', 'thrive-theme' ),
				],
			],
			'elements' => [
				'post_title'     => [
					'view'       => 'PostTitle',
					'identifier' => '.' . TCB_POST_TITLE_IDENTIFIER . ', [data-shortcode="tcb_post_title"]',
					'label'      => __( 'Post Title', 'thrive-theme' ),
				],
				'featured_image' => [
					'view'       => 'FeaturedImage',
					'identifier' => '.' . TCB_POST_THUMBNAIL_IDENTIFIER . ', .thrive-dynamic-source',
					'label'      => __( 'Featured Image', 'thrive-theme' ),
				],
				'featured_video' => [
					'view'       => 'FeaturedVideo',
					'identifier' => '.thrv_responsive_video[data-type="dynamic"], .thrv_responsive_video[data-is-dynamic="true"], .tve_responsive_video_container',
					'label'      => __( 'Featured Video', 'thrive-theme' ),
				],
				'featured_audio' => [
					'view'       => 'FeaturedAudio',
					'identifier' => '.thrv_audio[data-is-dynamic="true"] >', //This selector also works for TA Visual We need first child here
					'label'      => __( 'Featured Audio', 'thrive-theme' ),
				],
				'author_box'     => [
					'view'       => 'AuthorBox',
					'identifier' => '.thrive_author_box',
					'label'      => __( 'About the Author', 'thrive-theme' ),
				],
				'breadcrumbs'    => [
					'view'       => 'Breadcrumbs',
					'identifier' => '.thrive-breadcrumbs',
					'label'      => __( 'Breadcrumbs', 'thrive-theme' ),
				],
				'comments'       => [
					'view'       => 'Comments',
					'identifier' => '.' . Thrive_Theme_Comments::COMMENTS_CONTAINER_CLASS,
					'label'      => __( 'Comments', 'thrive-theme' ),
				],
			],
		] );

		if ( ! $get_all ) {

			$post_id = get_the_ID();
			/**
			 * Allow other plugins to hook here and modify the visibility config
			 *
			 * Used in TA Visual Builder
			 */
			$format = apply_filters( 'thrive_theme_visibility_config_post_format', get_post_format( $post_id ), $post_id );

			switch ( $format ) {
				case 'video':
					unset( $config['elements']['featured_audio'] );
					break;
				case 'audio':
					unset( $config['elements']['featured_video'] );
					break;
				default:
					unset( $config['elements']['featured_video'], $config['elements']['featured_audio'] );
					break;
			}
		}

		return empty( $type ) ? $config : $config[ $type ];
	}

	/**
	 * If we're not in the theme editor and the featured images have to be hidden, we add a class to hide all the dynamic source containers.
	 *
	 * @return bool
	 */
	public function should_hide_dynamic_featured_images() {
		return is_singular() && ! Thrive_Utils::is_inner_frame() && ! Thrive_Utils::is_preview() && ! $this->is_visible( 'featured_image' );
	}

	/**
	 * If we're not in the theme editor and the inline post titles have to be hidden, we add a class to do that.
	 *
	 * @return bool
	 */
	public function should_hide_inline_post_titles() {
		return is_singular() && ! Thrive_Utils::is_inner_frame() && ! Thrive_Utils::is_preview() && ! $this->is_visible( 'post_title' );
	}

	/**
	 * Get link to clone a post or a page
	 *
	 * @return string
	 */
	public function get_clone_link_html() {
		$action = static::CLONE_ACTION;
		$url    = $this->post ? admin_url( "admin.php?action={$action}&post={$this->ID}" ) : '';

		$attr = [
			'href'  => $url,
			'title' => __( 'Clone this item', 'thrive-theme' ),
		];

		return TCB_Utils::wrap_content( __( 'Clone', 'thrive-theme' ), 'a', '', '', $attr );
	}

	/**
	 * Duplicate all the data related to a post or page
	 *
	 * @return int
	 * @throws Exception
	 */
	public function duplicate() {

		if ( ! $this->post ) {
			throw new Exception( __( 'Could not find the original post', 'thrive-theme' ) );
		}

		$meta      = [];
		$meta_keys = array_keys( get_post_meta( $this->ID ) );

		foreach ( $meta_keys as $key ) {
			$meta[ $key ] = get_post_meta( $this->ID, $key, true );
		}

		/* We need to replace the css ids before saving the new post */
		$initial_meta = $meta;

		$new_post_title = __( 'Clone of', 'thrive-theme' ) . ' ' . $this->post->post_title;

		$meta = json_encode( $meta );

		$css_id_map = Thrive_Utils::replace_css_ids( $meta );

		$meta = Thrive_Utils::replace_form_identifiers( $meta, $new_post_title );

		$new_meta = json_decode( $meta, true );

		/* If somehow the replace went wrong, we keep the initial meta */
		if ( empty( $new_meta ) ) {
			$new_meta = $initial_meta;
		}

		$new_post = [
			'post_title'     => $new_post_title,
			'meta_input'     => $new_meta,
			'post_author'    => get_current_user_id(),
			'post_content'   => $this->post->post_content,
			'post_type'      => $this->post->post_type,
			'post_status'    => 'draft',
			'menu_order'     => $this->post->menu_order,
			'comment_status' => $this->post->comment_status,
			'ping_status'    => $this->post->ping_status,
			'post_excerpt'   => $this->post->post_excerpt,
			'post_mime_type' => $this->post->post_mime_type,
			'post_parent'    => $this->post->post_parent,
			'post_password'  => $this->post->post_password,
		];

		$id = wp_insert_post( $new_post );

		if ( ! $id || is_wp_error( $id ) ) {
			throw new Exception( __( 'There was an error while trying to duplicate the item', 'thrive-theme' ) );
		}

		//set also the post format
		set_post_format( $id, get_post_format( $this->post ) );

		/**
		 * Action triggered once we have the duplicated content
		 *
		 * @param int   $new_id
		 * @param int   $original_id
		 * @param array $css_id_map
		 */
		do_action( 'after_thrive_clone_item', $id, $this->ID, $css_id_map );

		return $id;
	}

	/**
	 * Return a specific type of section for the page
	 *
	 * @param string $type
	 *
	 * @return array
	 */
	public function get_section( $type ) {
		$sections = $this->get_meta( 'sections' );

		return array_merge( [
			'id'      => 0,
			'content' => '',
			'type'    => $type,
		], empty( $sections[ $type ] ) ? [] : $sections[ $type ] );
	}

	/**
	 * Return body class for this post / page
	 *
	 * @param boolean $with_dot
	 *
	 * @return string
	 */
	public function body_class( $with_dot = true ) {
		$class = ( $with_dot ? '.' : '' ) . 'page-id-' . $this->ID;

		/**
		 * Allow others to change the theme body class for this post
		 *
		 * @param string      $class The default class set from TTB
		 * @param Thrive_Post $this  The current post object
		 */
		return apply_filters( 'thrive_body_class', $class, $this );
	}

	/**
	 * Return section content by type for a specific landing page
	 *
	 * @param string $type
	 *
	 * @return string|void
	 */
	public function get_section_content( $type ) {
		$section_data = $this->get_section( $type );
		$section      = new Thrive_Landingpage_Section( $section_data['id'], $section_data, $this->ID );

		return $section->render();
	}

	/**
	 * Check if this is set also as a homepage on the website
	 *
	 * @return bool
	 */
	public function is_homepage() {
		$show_on_front = get_option( 'show_on_front' );
		$page_on_front = get_option( 'page_on_front', 0 );

		return ( $show_on_front === 'page' && (int) $page_on_front === $this->ID );
	}

}

if ( ! function_exists( 'thrive_post' ) ) {
	/**
	 * Return Thrive_Post instance
	 *
	 * @param int id - post id
	 *
	 * @return Thrive_Post
	 */
	function thrive_post( $id = 0 ) {
		return Thrive_Post::instance_with_id( $id );
	}
}
