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
 * Class Thrive_Section
 */
class Thrive_Section {

	use Thrive_Post_Meta;

	/**
	 * @var int
	 */
	public $ID;

	/**
	 * @var array|WP_Post|null
	 */
	protected $post;

	/**
	 * @var mixed|string
	 */
	protected $content = '';

	/**
	 * @var mixed|string
	 */
	protected $type;
	/**
	 * @var Thrive_Noop|Thrive_Template
	 */
	public $template;

	/**
	 * @var bool Check if the section has empty content after shortcodes have been parsed
	 */
	public $empty_content = false;

	/**
	 * @var bool
	 */
	protected $is_inner_frame;

	/**
	 * Keeps the default placeholder url and sizes
	 */
	const SECTION_THUMBNAIL_PLACEHOLDER
		= [
			'url' => THEME_URL . '/inc/assets/images/featured_image.png',
			/* hardcoded values for the 'featured_image' */
			'w'   => 743,
			'h'   => 385,
		];

	public static $meta_fields
		= [
			'skin_tag'      => '',
			'content'       => '',
			'type'          => '',
			'singular'      => 0,
			'template_type' => '',
			'style'         => Thrive_Css_Helper::DEFAULT_STYLE_ARRAY,
			'icons'         => [],
			'decoration'    => [],
			'comments'      => [
				'labels' => [],
				'icons'  => [],
			],
			'tve_globals'   => [
				'js_sdk'            => [],
				'fb_comment_admins' => '',
			],
			'attr'          => [
				'class' => '',
			],
		];

	/**
	 * Thrive_Section constructor.
	 *
	 * @param        $id
	 * @param array  $meta_input
	 * @param int    $template_id
	 */
	public function __construct( $id, $meta_input = [], $template_id = 0 ) {
		$this->ID = $id;

		$this->template = empty( $template_id ) ? thrive_template() : new Thrive_Template( $template_id );

		if ( empty( $id ) ) {
			$this->meta = array_merge( [
				'icons' => $this->template->meta( 'icons' ),
			], $meta_input );
		}

		$this->is_inner_frame = Thrive_Utils::is_inner_frame() || Thrive_Utils::during_ajax();
		$this->post           = empty( $id ) ? null : get_post( $id );
		$this->type           = $this->get_meta( 'type' );

		if ( $this->has_no_content() ) {
			$this->content = $this->default_content();
		} else {
			$this->content = $this->get_meta( 'content' );
		}
	}

	/**
	 * Check if the current section has content or not
	 *
	 * @return bool
	 */
	private function has_no_content() {
		if ( $this->is_dynamic() ) {
			/* when the section does not exist, we just get the default content for it. */
			$no_content = empty( $this->post );
		} else {
			/* when we have no content and the section was not saved until now in the template */
			$no_content = empty( $this->get_meta( 'content' ) ) && empty( $this->template->meta( 'sections' )[ $this->type() ] );
		}

		return $no_content;
	}

	/**
	 * Actions that should be done when we want to remove a section
	 *
	 * @return false|WP_Post|null
	 */
	public function delete() {
		$this->unlink_from_templates();

		return wp_delete_post( $this->ID );
	}

	/**
	 * It does what it says.
	 */
	public static function register_post_type() {
		register_post_type( THRIVE_SECTION, [
			'public'              => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => Thrive_Theme_Product::has_access(),
			'query_var'           => false,
			'description'         => 'Thrive Section',
			'rewrite'             => false,
			'labels'              => [
				'name' => 'Thrive Section',
			],
			'_edit_link'          => 'post.php?post=%d',
			'show_in_rest'        => true,
		] );
	}

	/**
	 * Get section name
	 *
	 * @return string
	 */
	public function name() {
		$title = ucfirst( $this->type() ) . ' Section';

		if ( $this->is_dynamic() ) {
			$title = $this->post->post_title;
		}

		return $title;
	}

	/**
	 * Get section thumbnail
	 *
	 * @return array
	 */
	public function thumbnail() {
		return TCB_Utils::get_thumb_data( $this->ID, THEME_UPLOADS_PREVIEW_SUB_DIR, static::SECTION_THUMBNAIL_PLACEHOLDER );
	}

	/**
	 * Get section type
	 *
	 * @return mixed
	 */
	public function type() {
		return $this->type;
	}

	/**
	 * If this section is dynamic, get an attribute from the section's meta.
	 * If it's not dynamic, get it from the template's meta.
	 *
	 * @param $key
	 *
	 * @return mixed|string
	 */
	public function get_attr( $key ) {
		$attr = $this->get_meta( 'attr' );

		return isset( $attr[ $key ] ) ? $attr[ $key ] : '';
	}

	/**
	 * Render section html
	 *
	 * @return string
	 */
	public function render() {
		thrive_shortcodes()->set_editing_context( 'section', [ 'instance' => $this ] );

		$is_visible = Thrive_Utils::get_section_visibility( $this->type() );

		/* if the section is not visible and we're not inside the theme editor or the architect editor, show nothing. */
		if ( ! $is_visible && ! $this->is_inner_frame && ! Thrive_Utils::is_architect_editor() ) {
			return '';
		}

		$class      = $this->class_attr( $is_visible );
		$attributes = $this->generate_attributes();

		$css             = '';
		$content         = $this->content();
		$background      = $this->get_background();
		$sidebar_trigger = $this->get_sidebar_trigger();
		$tag             = $this->get_section_tag();

		if ( ! $this->is_inner_frame ) {
			$css = tve_get_shared_styles( $content );

			if ( $this->is_dynamic() ) {
				$css .= $this->dynamic_css( true );

				$lightspeed = \TCB\Lightspeed\Css::get_instance( $this->ID );

				if ( $lightspeed->should_load_optimized_styles() ) {
					$css .= $lightspeed->get_optimized_styles();
				}
			}
		}

		/* wrap the whole content and add some classes */
		$section_html = TCB_Utils::wrap_content( $css . $background . $content . $sidebar_trigger, $tag, 'theme-' . $this->type() . '-section', $class, $attributes );

		thrive_shortcodes()->set_editing_context( null );


		/**
		 * Allow other plugins to alter section HTML
		 * Used in Thrive Apprentice visual builder
		 *
		 * @param string         $section_html
		 * @param Thrive_Section $this
		 */
		return apply_filters( 'thrive_theme_section_html', $section_html, $this );
	}

	/**
	 * Get decorations and background video for section
	 *
	 * @return string
	 */
	public function get_background() {
		$background = '';

		$decoration = $this->get_meta( 'decoration' );
		$attr       = $this->html_attr( 'background' );
		$class      = [ 'section-background' ];

		if ( ! empty( $decoration['video'] ) ) {
			$class[] = 'tcb-video-background-el';

			if ( has_shortcode( $decoration['video'], 'tcb_custom_field' ) ) {
				//get the attributes as an array so we can make some checks and add attributes
				$shortcode_attributes = shortcode_parse_atts( $decoration['video'] );
				$class[]              = 'tcb-custom-field-source';

				$attr['data-type']                = $shortcode_attributes['data-type'];
				$attr['data-is-video-background'] = $shortcode_attributes['data-is-video-background'];
				if ( ! empty( $shortcode_attributes['data-placeholder'] ) ) {
					$attr['data-placeholder-id'] = $shortcode_attributes['data-placeholder'];
				}

				//for LP sections we need to do this, we might not need it while editing a template
				$video_element = do_shortcode( $decoration ['video'] );
			} else {
				$video_element = $decoration ['video'];
			}
			$background .= $video_element;
		}

		if ( ! empty( $decoration['svg'] ) ) {
			$background .= $decoration ['svg'];

			if ( $this->is_inner_frame && ! empty( $decoration['clip-id'] ) ) {
				$attr['data-clip-id'] = $decoration['clip-id'];
			}
		}

		return TCB_Utils::wrap_content( $background, 'div', '', implode( ' ', $class ), $attr );
	}

	/**
	 * Sidebar trigger
	 *
	 * @return string
	 */
	protected function get_sidebar_trigger() {
		$trigger = '';

		if ( $this->is_sidebar() ) {
			$off_screen = $this->get_off_screen_sidebar_settings();

			if ( $this->is_inner_frame || ( ! empty( $off_screen ) && ! empty( $off_screen['hasDefaultTrigger'] ) ) ) {
				$trigger = Thrive_Utils::return_part( '/inc/templates/parts/sidebar-trigger.php', [
					'collapsed' => $this->template->get_icon_svg( empty( $off_screen['collapsedIcon'] ) ? 'icon-menu-left-solid' : $off_screen['collapsedIcon'] ),
					'expanded'  => $this->template->get_icon_svg( empty( $off_screen['expandedIcon'] ) ? 'icon-menu-right-solid' : $off_screen['expandedIcon'] ),
				] );
			}
		}

		return $trigger;
	}

	/**
	 * Element name used in editor
	 *
	 * @return string
	 */
	protected function element_name() {
		return ucfirst( $this->type() ) . ' ' . ( 'content' === $this->type() ? 'Area' : 'Section' );
	}

	/**
	 * Element attributes
	 *
	 * @return array
	 */
	public function generate_attributes() {
		$attributes = [];

		if ( $this->is_inner_frame ) {
			$attributes = [
				'data-section'       => $this->type(),
				'data-selector'      => $this->selector(),
				'data-element-name'  => $this->element_name(),
				'data-tcb-elem-type' => 'theme_section',
			];

			if ( $this->is_dynamic() ) {
				$attributes['data-id'] = $this->ID;
			}
		}

		if ( $this->is_sidebar() ) {
			$attributes['role'] = 'complementary';

			$attributes['data-display-type'] = $this->template->get_meta( 'sidebar-type' );

			$sticky_data = $this->template->get_meta( 'sticky-sidebar' );
			if ( ! empty( $sticky_data ) ) {
				$attributes['data-sticky'] = $sticky_data;
			}

			$off_screen = $this->get_off_screen_sidebar_settings( false );
			if ( ! empty( $off_screen ) ) {
				$attributes['data-off-screen'] = $off_screen;
			}
		}

		return $attributes;
	}

	/**
	 * Tells us if we have a saved section or just the html
	 *
	 * @return bool
	 */
	public function is_dynamic() {
		return ! empty( $this->ID );
	}

	/**
	 * Check if the current section is the sidebar
	 *
	 * @return bool
	 */
	public function is_sidebar() {
		return $this->type() === 'sidebar';
	}

	/**
	 * Section selector
	 *
	 * @return string
	 */
	public function selector() {
		return '.' . ( $this->is_dynamic() ? 'thrive-section-' . $this->ID : $this->type() . '-section' );
	}

	/**
	 * Section html tag
	 *
	 * @return string
	 */
	public function get_section_tag() {
		$tag = 'div';

		if ( $this->is_sidebar() ) {
			$tag = 'aside';
		}

		return $tag;
	}

	/**
	 * Inner section content attributes
	 *
	 * @param string $area
	 *
	 * @return array
	 */
	private function html_attr( $area = 'content' ) {

		$selector = ".section-{$area}";

		if ( ! $this->is_dynamic() ) {
			$selector = "{$this->selector()} {$selector}";
		}

		return $this->is_inner_frame ? [
			'data-element-name' => $this->element_name(),
			'data-selector'     => $selector,
			'data-section'      => $this->type(),
		] : [];
	}

	/**
	 * Get section content
	 *
	 * @return mixed|string
	 */
	public function content() {

		$attr = $this->html_attr( 'content' );

		/* parse the content, do shortcodes and stuff */
		$content = static::parse( $this->content );

		/**
		 * Checks if the section is empty after shortcodes have been parsed
		 * This regex has been modified to avoid catastrophic backtracking
		 */
		$this->empty_content = empty( trim( preg_replace( "/<section(.*?)>/im", '', str_replace( '</section>', '', $content ) ) ) );

		/* if it's empty, let's make it 100% empty */
		if ( trim( str_replace( [ "\n", "\r" ], '', $content ) ) === '' ) {
			$content = '';
		}

		if ( $this->is_sidebar() ) {
			$off_screen = $this->get_off_screen_sidebar_settings();

			$icon = $this->template->get_icon_svg( empty( $off_screen['closeIcon'] ) ? 'icon-close-solid' : $off_screen['closeIcon'] );

			if ( empty( $icon ) ) {
				$icon = $this->template->get_icon_svg( 'icon-close-solid' );
			}

			$content = TCB_Utils::wrap_content( $icon, 'button', '', 'tve-sidebar-close-icon' ) . $content;
		}


		return TCB_Utils::wrap_content( $content, 'div', '', 'section-content', $attr );
	}

	/**
	 * Default content for each section
	 *
	 * @param $type
	 *
	 * @return string
	 */
	public function default_content( $type = '' ) {

		if ( empty( $type ) ) {
			$type = $this->type;
		}

		/**
		 * Filter for default content on a specific section
		 *
		 * @param string         $content
		 * @param Thrive_Section $this
		 */
		$content = apply_filters( 'thrive_theme_section_default_content', '', $this, $type );

		if ( empty( $content ) ) {
			if ( $type === 'content' ) {

				$type = $this->template->get_primary();

				if ( $this->template->is_home() ) {
					$type = $this->template->is_singular() ? THRIVE_SINGULAR_TEMPLATE : THRIVE_ARCHIVE_TEMPLATE;
				}
			}

			$content = Thrive_Utils::return_part( '/inc/templates/default/' . $type . '.php' );
		}

		return $content;
	}

	/**
	 * Section classes
	 *
	 * @param $is_visible
	 *
	 * @return string
	 */
	public function class_attr( $is_visible ) {

		$type = $this->type();

		$class = [
			'theme-section',
			$type . '-section',
		];

		/* add the saved classes */
		if ( ! empty( $this->get_attr( 'class' ) ) ) {
			$class[] = $this->get_attr( 'class' );
		}

		if ( ! $is_visible && ( $this->is_inner_frame || Thrive_Utils::is_architect_editor() ) ) {
			/* in the inner frame we just hide the section, but we insert the html */
			$class[] = 'hide-section';
		}

		if ( $this->is_dynamic() ) {
			$class[] = 'thrive-section-' . $this->ID;
		}

		if ( $this->is_sidebar() ) {
			$display_type = json_decode( $this->template->get_meta( 'sidebar-type' ), true );

			if ( ! empty( $display_type ) ) {
				/* add classes so we can hide the sidebar on some devices until they initialize */
				foreach ( $display_type as $media => $display ) {
					$class[] = 'sidebar-' . $display . '-on-' . $media;
				}
			}
		}

		return implode( ' ', $class );
	}

	/**
	 * Parse content, do shortcodes, parse events, prepare images
	 *
	 * @param $content
	 *
	 * @return mixed
	 */
	public static function parse( $content ) {
		$in_editor = TCB_Utils::in_editor_render( true );

		global $shortcode_tags;
		$temp_shortcodes = $shortcode_tags;

		if ( $in_editor ) {
			$shortcode_tags = array_filter( $shortcode_tags, static function ( $tag ) {
				$allow_tag = false;

				/* add prefixes from all plugins so we won't risk a partial implementation and break a page */
				foreach ( [ 'thrive_', 'tcb_', 'tve_', 'tva_', 'tqb_', 'thrv_', 'tvo_' ] as $piece ) {
					$allow_tag = $allow_tag || stripos( $tag, $piece ) !== false;
				}

				/**
				 * Chose if we want to render a specific shortcode tag while inside the editor
				 *
				 * @param $allow_tag boolean if we want to render or not
				 * @param $tag       string shortcode tag we want to render
				 *
				 * @return boolean
				 */
				return apply_filters( 'thrive_theme_render_shortcode_in_editor', $allow_tag, $tag );
			}, ARRAY_FILTER_USE_KEY );
		}

		/* render all elements that were saved as shortcodes. for non singular pages we don't do shortcodes from inside html because we might have dynamic css/links there. */
		$content = do_shortcode( $content, true );
		$content = tve_thrive_shortcodes( $content, $in_editor );
		/* the second time is for when we render symbols that have shortcodes inside them */
		$content = do_shortcode( $content );

		/* restore all script tags from custom html controls. script tags are replaced with <code class="tve_js_placeholder"> */
		if ( ! $in_editor ) {
			/* dont restore them in editor to prevent conflicts */
			$content = tve_restore_script_tags( $content );

			$content = tcb_clean_frontend_content( $content );
		}

		$content = tve_do_wp_shortcodes( $content, $in_editor );

		$content = TCB_Post_List_Shortcodes::check_dynamic_links( $content );

		/* Check for existing custom icons in the section */
		if ( strpos( $content, 'tve_sc_icon' ) !== false ) {
			TCB_Icon_Manager::enqueue_icon_pack();
		}

		/* make sure images added from the editor are responsive */
		if ( function_exists( 'wp_filter_content_tags' ) ) {
			try {
				$content = wp_filter_content_tags( $content );
			} catch ( Error $e ) {
				/* just so it won't break the page */
			}
		} elseif ( function_exists( 'wp_make_content_images_responsive' ) ) {
			$content = wp_make_content_images_responsive( $content );
		}

		/* parse content for tcb events so we can enqueue the animations for them */
		tve_parse_events( $content );

		$shortcode_tags = $temp_shortcodes;

		return $content;
	}

	/**
	 * Return the css saved in the section meta, remove extra spaces and maybe wrap it in a style node.
	 *
	 * @param boolean $wrap
	 * @param boolean $include_dynamic
	 *
	 * @return mixed
	 */
	public function style( $wrap = false, $include_dynamic = false ) {
		return thrive_css_helper( $this )
			->generate_style( false, $include_dynamic )
			->maybe_wrap( $wrap ? 'thrive-section-' . $this->ID : '' );
	}

	/**
	 * Render dynamic css specific to current section
	 *
	 * @param bool $wrap
	 *
	 * @return string
	 */
	public function dynamic_css( $wrap = false ) {
		return thrive_css_helper( $this )
			->generate_dynamic_style()
			->maybe_wrap( $wrap ? 'thrive-dynamic-section-' . $this->ID : '' );
	}

	/**
	 * Export section data
	 *
	 * @return array
	 */
	public function export() {
		$data = [
			'ID'          => $this->ID,
			'post_title'  => $this->name(),
			'post_type'   => THRIVE_SECTION,
			'post_status' => 'publish',
			'meta_input'  => [],
		];

		foreach ( static::$meta_fields as $key => $value ) {
			$data['meta_input'][ $key ] = $this->get_meta( $key );
		}

		return $data;
	}

	/**
	 * Get the icon HTML saved for this section. If the section is not dynamic, look for the icon in the template.
	 *
	 * @param $name
	 *
	 * @return mixed|string
	 */
	public function get_icon( $name ) {
		$icons = $this->get_meta( 'icons' );

		return empty( $icons[ $name ] ) ? '' : $icons[ $name ];
	}

	/**
	 *  Search and replace section data
	 *
	 * @param string $search
	 * @param string $replace
	 */
	public function replace_data_ids( $search = '', $replace = '' ) {

		$style   = json_encode( $this->get_meta( 'style' ) );
		$content = json_encode( $this->get_meta( 'content' ) );

		$style   = json_decode( str_replace( $search, $replace, $style ), true );
		$content = json_decode( str_replace( $search, $replace, $content ), true );

		if ( $this->is_dynamic() ) {
			$this->set_meta( 'style', $style );
			$this->set_meta( 'content', $content );
		} else {
			$this->content       = $content;
			$this->meta['style'] = $style;
		}
	}

	/**
	 * Generate an image preview for the section.
	 * Return an array that contains the url, the height and the width of the preview.
	 *
	 * @return array
	 * @throws Exception
	 */
	public function create_preview() {
		$args = [
			'action'      => 'section_preview',
			/* for content/sidebar, crop the width to 300; for top/bottom, crop to 700 */
			'crop_width'  => $this->is_sidebar() || $this->type() === 'content' ? 300 : 700,
			/* crop the height to 600px AFTER resizing */
			'crop_height' => 600,
		];

		return Thrive_Utils::create_preview( $this->ID, $args );
	}

	/**
	 * Get off screen settings for sidebar section
	 *
	 * @param bool $json_decode
	 *
	 * @return array|mixed
	 */
	public function get_off_screen_sidebar_settings( $json_decode = true ) {
		$settings = [];

		if ( $this->is_sidebar() ) {
			$settings = $this->template->get_meta( 'off-screen-sidebar' );

			if ( $json_decode ) {
				$settings = json_decode( $settings, true );
			}
		}

		return $settings;
	}

	/**
	 * For all the templates that use this section, manually unlink the section and copy the data to the template meta.
	 */
	public function unlink_from_templates() {

		$section_data = [
			/* set the ID to 0 to mark the section as static */
			'id'         => 0,
			'content'    => $this->get_meta( 'content' ),
			'decoration' => $this->get_meta( 'decoration' ),
			'comments'   => $this->get_meta( 'comments' ),
			'attr'       => $this->get_meta( 'attr' ),
		];

		foreach ( thrive_skin()->get_templates( 'object' ) as $template ) {
			/* @var $template Thrive_Template */
			$template_section = $template->get_section( $this->type() );

			if ( ! empty( $template_section['id'] ) && (int) $this->ID === (int) $template_section['id'] ) {
				$template_sections = $template->meta( 'sections' );

				/* set the new data for this section type */
				$template_sections[ $this->type() ] = $section_data;

				/* get the section style and convert the css from dynamic to static */
				$section_style = $this->get_converted_styles_for_template( $template );

				$template->update( [
					'icons'    => array_merge( $template->meta( 'icons' ), $this->get_meta( 'icons' ) ),
					'sections' => $template_sections,
					/*  Merge the styles of the unlinked section into the template styles - css, fonts and dynamic css. */
					'style'    => Thrive_Css_Helper::merge_styles( $template->meta( 'style' ), $section_style ),
				] );
			}
		}
	}

	/**
	 * Search the meta of the section for the posts_per_page key and return it if it's found
	 *
	 * @param $section
	 *
	 * @return mixed|null
	 */
	public static function get_posts_per_page( $section ) {
		$posts_per_page = null;

		/* look for the section meta value */
		if ( empty( $section['id'] ) ) {
			if ( ! empty( $section['posts_per_page'] ) ) {
				$posts_per_page = $section['posts_per_page'];
			}
		} else {
			$posts_per_page = get_post_meta( $section['id'], 'posts_per_page', true );
		}

		return $posts_per_page;
	}

	/**
	 * Read query from section in case we have anything
	 *
	 * @return array
	 */
	public function get_list_query() {
		try {
			$query = $this->get_meta( 'query' );

			if ( is_string( $query ) ) {
				$query = str_replace( "'", '"', $query );
				$query = json_decode( $query, true );
			} else {
				$query = [];
			}
		} catch ( Exception $e ) {
			$query = [];
		}

		return is_array( $query ) ? $query : [];
	}

	/**
	 * Replace '.thrive-section-{ID}' with {body_class} .{type}-section ( conversion from dynamic to static ).
	 *
	 * @param Thrive_Template $template
	 *
	 * @return array|mixed|object
	 */
	public function get_converted_styles_for_template( $template ) {
		$style = json_encode( $this->get_meta( 'style' ) );

		$old_selector = '.thrive-section-' . $this->ID;

		$template_class = $template->body_class( false, 'string' );
		$new_selector   = $template_class . ' .' . $this->type() . '-section';

		$style = str_replace( $old_selector, $new_selector, $style );

		return json_decode( $style, true );
	}
}
