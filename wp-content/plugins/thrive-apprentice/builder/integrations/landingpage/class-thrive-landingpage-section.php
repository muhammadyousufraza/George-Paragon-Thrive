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
 * Class Thrive_Landingpage_Section
 */
class Thrive_Landingpage_Section extends Thrive_Section {

	/**
	 * Landing page section element type
	 */
	const ELEMENT_TYPE = 'landingpage_section';

	/**
	 * @var Thrive_Post
	 */
	private $page;

	/**
	 * Thrive_Landingpage_Section constructor.
	 *
	 * @param int   $id
	 * @param array $meta_input
	 * @param int   $page_id
	 */
	public function __construct( $id, $meta_input = [], $page_id = 0 ) {
		$this->ID = $id;
		$this->set_page( $page_id );

		$this->post           = empty( $id ) ? null : get_post( $id );
		$this->is_inner_frame = is_editor_page() || Thrive_Utils::during_ajax();

		if ( empty( $id ) ) {
			$this->meta = $meta_input;
		}


		$this->type = $this->get_meta( 'type' );

		if ( $this->has_no_content() ) {
			$this->content = $this->placeholder_content();
		} else {
			$this->content = $this->get_meta( 'content' );
		}
	}

	/**
	 * Set the corresponding page where the sections is rendered
	 *
	 * @param int/bool $id
	 */
	public function set_page( $id ) {
		if ( empty( $id ) ) {
			$id = get_the_ID();
		}

		$this->page = new Thrive_Post( $id );
	}

	/**
	 * Check if we have content for the section
	 *
	 * @return bool
	 */
	private function has_no_content() {
		if ( $this->is_dynamic() ) {
			/* when the section does not exist, we just get the default content for it. */
			$no_content = empty( $this->post );
		} else {
			/* when we have no content and the section was not saved until now in the template */
			$no_content = empty( $this->get_meta( 'content' ) ) && empty( $this->page->get_meta( 'sections' )[ $this->type() ] );
		}

		return $no_content;
	}

	/**
	 * Render section
	 *
	 * @return string|void
	 */
	public function render() {
		thrive_shortcodes()->set_editing_context( 'section', [ 'instance' => $this ] );

		$default_classes = [];
		$extra_attr      = $this->get_extra_attr();
		$is_hidden       = $this->is_hidden();

		if ( $is_hidden ) {
			/* if the section is not visible and we're not inside the architect editor, show nothing. */
			if ( ! $this->is_inner_frame && ! Thrive_Utils::is_architect_editor() ) {
				return '';
			} else {
				/* add 'hide-section' to the class list if we're inside the editor */
				$default_classes[] = 'hide-section';
			}
		}

		$class = $this->class_attr( $default_classes );

		if ( ! empty( $extra_attr['class'] ) ) {
			$class .= ' ' . $extra_attr['class'];
		}
		$id = "landingpage-{$this->type}-section";

		$attributes = $this->generate_attributes();
		$tag        = $this->get_section_tag();
		$background = $this->get_background();
		$content    = $this->content();
		$css        = '';

		if ( ! Thrive_Utils::during_ajax() ) {
			$css = $this->style( true, true );
		}

		if ( ! $this->is_inner_frame ) {
			$css .= tve_get_shared_styles( $content );
		}

		return TCB_Utils::wrap_content( $css . $background . $content, $tag, $id, $class, $attributes );
	}

	/**
	 * Return if the section should be hidden
	 *
	 * @return bool
	 */
	public function is_hidden() {

		if ( Thrive_Utils::during_ajax() ) {
			return false;
		}

		$hide = $this->get_meta( 'hide' );

		if ( $this->is_dynamic() ) {
			$hide = ! empty( $this->page->get_section( $this->type )['hide'] );
		}

		return (bool) $hide;
	}

	/**
	 * Get extra data saved for section
	 *
	 * @return mixed
	 */
	public function get_extra_attr() {
		return $this->get_meta( 'attr' );
	}

	/**
	 * Section html tag
	 *
	 * @return string
	 */
	public function get_section_tag() {
		return 'div';
	}

	/**
	 * Build section class attributes
	 *
	 * @param array $default_classes
	 *
	 * @return string
	 */
	public function class_attr( $default_classes = [] ) {
		$class = array_merge( $default_classes, [
			'landingpage-section',
			"{$this->type()}-section",
		] );

		if ( $this->has_no_content() ) {
			$class[] = 'placeholder-section';

			/* only add the 'hide-section' class if it doesn't already exist */
			if ( ! in_array( 'hide-section', $class ) ) {
				$class[] = 'hide-section';
			}
		}

		if ( $this->is_dynamic() ) {
			$class[] = 'thrive-section-' . $this->ID;
		}

		return implode( ' ', $class );
	}

	/**
	 * Section attributes
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
				'data-tcb-elem-type' => self::ELEMENT_TYPE,
			];

			if ( $this->is_dynamic() ) {
				$attributes['data-id'] = $this->ID;
			}
		}

		return $attributes;
	}

	/**
	 * When we are inside the editor we are showing a placeholder if there is no section saved on the landingpage
	 *
	 * @param string $type
	 *
	 * @return string
	 */
	public function placeholder_content( $type = '' ) {
		if ( empty( $type ) ) {
			$type = $this->type;
		}

		return is_editor_page() ? TCB_Utils::wrap_content( __( 'Insert ', 'thrive-theme' ) . $type . __( ' Section ', 'thrive-theme' ), 'button', '', 'insert-section' ) : '';
	}

	/**
	 * Import section for landing page
	 *
	 * @param $data
	 *
	 * @return int|WP_Error
	 */
	public static function import( $data ) {
		$old_id = $data['ID'];

		unset( $data['ID'] );

		$new_id = wp_insert_post( $data );

		/* replace old selector in styles */
		$style = json_encode( get_post_meta( $new_id, 'style', true ) );
		$style = str_replace( ".thrive-section-$old_id", ".thrive-section-$new_id", $style );
		update_post_meta( $new_id, 'style', json_decode( $style, true ) );

		wp_set_object_terms( $new_id, thrive_skin()->ID, SKIN_TAXONOMY );

		return $new_id;
	}
}
