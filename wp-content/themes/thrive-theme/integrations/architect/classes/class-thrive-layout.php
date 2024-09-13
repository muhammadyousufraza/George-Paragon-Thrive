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
 * Class Thrive_Layout
 */
class Thrive_Layout {

	/**
	 * The $post object of the layout
	 *
	 * @var array|null|stdClass|WP_Post
	 */
	public $post;

	public $ID;

	use Thrive_Post_Meta;

	protected static $_instance;

	public static $meta_fields = [
		'style'           => Thrive_Css_Helper::DEFAULT_STYLE_ARRAY,
		'default'         => 0,
		'sidebar_on_left' => 0,
		'hide_sidebar'    => 0,
		'content_width'   => '',
	];

	/**
	 * Singleton implementation for class instance
	 *
	 * @param int id layout id
	 *
	 * @return Thrive_Layout
	 */
	public static function instance( $id = 0 ) {

		/* if we don't have any instance or when we send an id that it's not the same as the previous one, we create a new instance */
		if ( empty( static::$_instance ) || ( ! empty( $id ) && static::$_instance->ID !== $id ) ) {

			if ( empty( $id ) ) {
				/* by default we try to get the layout from the template */
				$id = thrive_template()->get_layout();
			}

			if ( get_post( $id ) === null ) {
				/* in case we don't have a template or for some reason the template doesn't have a layout, we get the default one from the skin */
				$id = thrive_skin()->get_default_layout();
			} elseif ( empty( get_the_terms( $id, SKIN_TAXONOMY ) ) ) {
				/* if the layout exists but is not assigned to a skin, we assign it to the active skin */
				wp_set_object_terms( $id, thrive_skin()->ID, SKIN_TAXONOMY );
			}

			static::$_instance = new self( $id );
		}

		return static::$_instance;
	}

	/**
	 * Thrive_Layout constructor.
	 *
	 * @param int $id
	 */
	public function __construct( $id = 0 ) {

		$this->ID = $id;

		$this->post = get_post( $id );
	}

	/**
	 * Layout specific body class so we can apply the style
	 *
	 * @return string
	 */
	public function body_class() {
		return empty( $this->ID ) ? '' : 'thrive-layout-' . $this->ID;
	}

	/**
	 * The location of the sidebar in this layout
	 *
	 * @return bool
	 */
	public function has_sidebar_on_left() {
		return ! empty( $this->get_meta( 'sidebar_on_left' ) );
	}

	/**
	 * The location of the sidebar in this layout
	 *
	 * @return bool
	 */
	public function is_sidebar_visible() {
		return empty( $this->get_meta( 'hide_sidebar' ) );
	}

	/**
	 * Check which layout is the default one
	 *
	 * @return bool
	 */
	public function is_default() {
		return ! empty( $this->get_meta( 'default' ) );
	}

	/**
	 * Export layout data
	 *
	 * @param null $meta_fields
	 *
	 * @return array
	 */
	public function export( $meta_fields = null ) {

		if ( $meta_fields === null ) {
			$meta_fields = array_keys( static::$meta_fields );
		}

		$data = [
			'ID'          => $this->ID,
			'post_title'  => $this->post === null ? '' : $this->post->post_title,
			'post_type'   => THRIVE_LAYOUT,
			'meta_input'  => [],
			'post_status' => 'publish',
		];

		foreach ( $meta_fields as $field ) {
			$value = $this->get_meta( $field );

			/* make sure all the numeric meta is returned as integers */
			if ( is_numeric( $value ) ) {
				$value = (int) $value;
			}

			$data['meta_input'][ $field ] = $value;
		}

		return $data;
	}

	/**
	 * Does what is says. Reverts everything to the default values
	 */
	public function reset() {

		$fields = static::$meta_fields;

		if ( $this->is_default() ) {
			unset( $fields['default'] );
		}

		foreach ( $fields as $field => $default_value ) {
			update_post_meta( $this->ID, $field, $default_value );
		}

		thrive_skin()->generate_style_file();
	}

	/**
	 * Return the css saved in the section meta, remove extra spaces and maybe wrap it in a style node.
	 *
	 * @param boolean $wrap
	 *
	 * @return mixed
	 */
	public function style( $wrap = false ) {
		return thrive_css_helper( $this )
			->generate_style()
			->maybe_wrap( $wrap ? 'thrive-theme-layout' : '' );
	}

	/**
	 * It does what it says.
	 */
	public static function register_post_type() {
		register_post_type( THRIVE_LAYOUT, [
			'public'              => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => Thrive_Theme_Product::has_access(),
			'query_var'           => false,
			'description'         => 'Thrive Layout',
			'rewrite'             => false,
			'labels'              => [
				'name' => 'Thrive Layout',
			],
			'_edit_link'          => 'post.php?post=%d',
			'show_in_rest'        => true,
		] );
	}

	/**
	 * Get skin specific layouts
	 *
	 * @param string $output
	 * @param array  $meta_inputs
	 *
	 * @return array
	 */
	public static function get_all( $output = 'array', $meta_inputs = null ) {

		$posts = get_posts( [
			'post_type'      => THRIVE_LAYOUT,
			'posts_per_page' => - 1,
		] );

		return array_map( static function ( $post ) use ( $output, $meta_inputs ) {
			if ( $output === 'ids' ) {
				$layout = $post->ID;
			} else {
				$layout = new Thrive_Layout( $post->ID );

				if ( $output === 'array' ) {
					$layout = $layout->export( $meta_inputs );
				}
			}

			return $layout;
		}, $posts );
	}

	/**
	 * Replace ids hash in the new inserted layout
	 *
	 * @param $hash
	 */
	public function replace_id_from_style( $hash ) {
		$style = $this->get_meta( 'style' );

		if ( ! empty( $style ) ) {
			$style_content = json_encode( $style );

			$style_content = str_replace( $hash, $this->ID, $style_content );

			$this->set_meta( 'style', json_decode( $style_content, true ) );
		}
	}

	/**
	 * Create a default/empty layout with nothing or data from another layout
	 *
	 * @param int $skin_id
	 * @param int $source_layout_id
	 *
	 * @return int|WP_Error
	 */
	public static function create( $skin_id = 0, $source_layout_id = 0 ) {

		$args = [
			'post_title'  => 'Boxed Layout',
			'post_status' => 'publish',
			'post_type'   => THRIVE_LAYOUT,
			'meta_input'  => array_merge( static::$meta_fields, [ 'default' => 1 ] ),
		];

		/* if we have layout id => we clone the layout*/
		if ( $source_layout_id ) {
			$args = wp_parse_args( ( new Thrive_Layout( $source_layout_id ) )->export(), $args );
			unset( $args['ID'] );
		}

		$layout_id = wp_insert_post( $args );

		if ( $skin_id ) {
			wp_set_object_terms( $layout_id, $skin_id, SKIN_TAXONOMY );
		}

		return $layout_id;
	}
}

/**
 * Return the current layout that is applied
 *
 * @param int $id
 *
 * @return Thrive_Layout
 */
function thrive_layout( $id = 0 ) {
	return Thrive_Layout::instance( $id );
}
