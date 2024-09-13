<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\TTB;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Skin_Templates
 *
 * @package  TVA\TTB
 * @project  : thrive-apprentice
 */
class Skin_Template extends \Thrive_Template {
	/**
	 * Use general singleton methods
	 */
	use \Thrive_Singleton;

	/**
	 * Skin_Templates constructor.
	 *
	 * @param int $id
	 */
	public function __construct( $id = 0 ) {
		parent::__construct( $id );
	}

	/**
	 * Returns true if a template is a Thrive Apprentice template
	 *
	 * @return bool
	 */
	public function is_tva_template() {
		return $this->is_course_template() || in_array( $this->get_secondary(), [
				\TVA_Const::NO_ACCESS_POST,
			] );
	}

	/**
	 * Returns true if a template is a Thrive Apprentice Course template
	 *
	 * @return bool
	 */
	public function is_course_template() {
		return in_array( $this->get_secondary(), [
			\TVA_Const::LESSON_POST_TYPE,
			\TVA_Const::COURSE_POST_TYPE,
			\TVA_Const::COURSE_TAXONOMY,
			\TVA_Const::MODULE_POST_TYPE,
			\TVA_Const::NO_ACCESS,
			\TVA_Course_Completed::POST_TYPE,
			\TVA_Const::ASSESSMENT_POST_TYPE,
			\TVA_Const::CERTIFICATE_VALIDATION_POST,
		] );
	}

	/**
	 * Returns the skin term associated with the template
	 *
	 * @return \WP_Term
	 */
	public function skin_term() {
		$terms = wp_get_object_terms( $this->ID, SKIN_TAXONOMY );

		return reset( $terms );
	}

	/**
	 * Whether or not this template is for the school homepage
	 *
	 * @return bool
	 */
	public function is_school_homepage() {
		return $this->primary_template === THRIVE_HOMEPAGE_TEMPLATE
			   && $this->secondary_template === \TVA_Const::COURSE_POST_TYPE;
	}

	/**
	 * Whether or not this template is for the course overview page
	 *
	 * @return bool
	 */
	public function is_course_overview() {
		return $this->primary_template === THRIVE_ARCHIVE_TEMPLATE
			   && $this->secondary_template === \TVA_Const::COURSE_TAXONOMY;
	}

	/**
	 * Checks if the template is on for certificate verification
	 *
	 * @return bool
	 */
	public function is_certificate_verification() {
		return $this->primary_template === THRIVE_SINGULAR_TEMPLATE
			   && $this->secondary_template === \TVA_Const::CERTIFICATE_VALIDATION_POST;
	}

	/**
	 * Whether or not this template is for the lesson page
	 *
	 * @return bool
	 */
	public function is_lesson() {
		return $this->primary_template === THRIVE_SINGULAR_TEMPLATE
			   && $this->secondary_template === \TVA_Const::LESSON_POST_TYPE;
	}

	/**
	 * Whether or not this template is for the module page
	 *
	 * @return bool
	 */
	public function is_module() {
		return $this->primary_template === THRIVE_SINGULAR_TEMPLATE
			   && $this->secondary_template === \TVA_Const::MODULE_POST_TYPE;
	}

	/**
	 * Whether or not this template is for the no access page
	 *
	 * @return bool
	 */
	public function is_no_access() {
		return $this->primary_template === THRIVE_SINGULAR_TEMPLATE
			   && $this->secondary_template === \TVA_Const::NO_ACCESS;
	}

	/**
	 * Whether or not this template is for the general no access page
	 *
	 * @return bool
	 */
	public function is_general_no_access() {
		return $this->primary_template === THRIVE_SINGULAR_TEMPLATE
			   && $this->secondary_template === \TVA_Const::NO_ACCESS_POST;
	}

	/**
	 * Whether or not this template is for the assessment page
	 *
	 * @return bool
	 */
	public function is_assessment() {
		return $this->primary_template === THRIVE_SINGULAR_TEMPLATE
			   && $this->secondary_template === \TVA_Const::ASSESSMENT_POST_TYPE;
	}


	/**
	 * Called when creating a new template - it will setup all default data needed for a template - styles, content etc
	 *
	 * @return self allow chained calls
	 */
	public function setup_default_data() {
		/* default CSS / fonts that each template should have */
		$template_styles = \Thrive_Theme_Default_Data::template_default_styles( $this );

		/* there's a wrong CSS media key there, unset it */
		unset( $template_styles['css']['(min-width: 767px)'] );

		if ( $this->is_school_homepage() ) {
			/* for a school homepage template, take the default Course List template from the cloud and add it to the content area */
			$default_course_list = tve_get_cloud_template_data(
				'course_list',
				array(
					'skip_do_shortcode' => true,
					'id'                => 'default',
					'type'              => 'course_list',
				)
			);
			if ( ! is_wp_error( $default_course_list ) ) {
				/* process the course list CSS by adding a template prefix to each rule */
				$course_list_style = \Thrive_Css_Helper::get_style_array_from_string( $default_course_list['head_css'] );
				$css               = &$course_list_style['css'];
				$prefix            = $this->body_class( false, 'string' ) . ' .content-section ';
				foreach ( $css as $media => &$rules_string ) {
					$rules_string = preg_replace( '#(^|})(.+?)\{#', "$1{$prefix}$2{", $rules_string );
				}
				unset( $rules_string );
				/* also append the styles and fonts to the default template css */
				$template_styles     = \Thrive_Css_Helper::merge_styles( $template_styles, $course_list_style );
				$sections            = $this->sections;
				$sections['content'] = [
					'id'      => 0,
					'content' => $default_course_list['content'],
				];
				$this->set_meta( 'sections', $sections );
			}
		}

		$this->set_meta( 'style', $template_styles );

		return $this;
	}

	/**
	 * Append a skin parameter to the preview url
	 *
	 * @return string
	 */
	public function preview_url() {
		return add_query_arg( [
			'tva_skin_id' => $this->get_skin_id(),
		], parent::preview_url() );
	}

	/**
	 * Remove CSS Rules for unlinked sections that match the $section argument from all media
	 *
	 * @param string $section
	 *
	 * @return Skin_Template
	 */
	public function remove_section_styles( $section = 'sidebar' ) {
		$styles = $this->style;
		$css    = isset( $styles['css'] ) ? $styles['css'] : [];

		$selector_pattern = [
			"#([^}]*|^)\.tve-theme-{$this->ID} \.{$section}-section(.*?){(.*?)}#s",
			"#([^}]*|^)\.tve-theme-{$this->ID} \.tve-{$section}(.*?){(.*?)}#s",
		];

		foreach ( $css as $media => $css_string ) {
			$css[ $media ] = preg_replace( $selector_pattern, '', $css_string );
		}

		$styles['css'] = $css;

		$this->set_meta( 'style', $styles );
		$this->meta['style'] = $styles;

		return $this;
	}

	/**
	 * A template is considered assessment_ready if there is a course shortcode and the course shortcode is ready for assessments
	 *
	 * @return bool
	 */
	public function is_assessment_ready() {
		$rdy = get_post_meta( $this->ID, 'tva_assessment_ready', true );

		if ( $rdy ) {
			return true;
		}

		$sections = get_post_meta( $this->ID, 'sections', true );
		$rdy      = true;

		if ( ! empty( $sections ) ) {
			$section_string = maybe_serialize( $sections );

			if ( strpos( $section_string, '[tva_course ' ) !== false && strpos( $section_string, 'assessment-ready' ) === false ) {
				$rdy = false;
			}
		}

		return $rdy;
	}
}
