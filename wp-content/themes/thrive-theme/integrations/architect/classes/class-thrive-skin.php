<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

use Thrive\Theme\Integrations\WooCommerce\Main as WooMain;

/**
 * Class Thrive_Skin
 */
class Thrive_Skin {
	/**
	 * Use general singleton methods
	 */
	use Thrive_Singleton;

	/**
	 * Use the shortcuts for term meta setters and getters
	 */
	use Thrive_Term_Meta;

	/**
	 * Meta to know that a skin is active
	 */
	const SKIN_META_ACTIVE = 'is_active';

	const BREADCRUMBS_LABELS = 'breadcrumbs_labels';

	const DEFAULT_LAYOUT = 'default_layout';

	/**
	 * Meta name for skin palettes
	 */
	const SKIN_META_PALETTES    = 'palettes';
	const SKIN_META_PALETTES_V2 = 'palettes_v2';

	/**
	 * Meta name for skin variables
	 */
	const SKIN_META_VARIABLES = 'variables';

	/**
	 * The user can skip the wizard - this means that he will no longer be redirected to the wizard in the TTB dashboard
	 * We keep this setting at the skin-level
	 */
	const IS_WIZARD_SKIPPED = 'is_wizard_skipped';

	/* Skin tag */
	const TAG = 'tag';

	/**
	 * Name of the default skin
	 */
	const DEFAULT_SKIN = 'Default Theme';

	/**
	 * @var array|false|WP_Term
	 */
	public $term;
	/**
	 * Term id
	 *
	 * @var int
	 */
	public $ID;

	/**
	 * Style file prefix
	 * Allow to be modified in other classes that extends Thrive_Skin
	 *
	 * @var string
	 */
	protected $style_file_prefix = 'theme-template';

	/**
	 * Thrive_Skin constructor.
	 *
	 * @param $skin_id
	 */
	public function __construct( $skin_id ) {

		$this->set_term( $skin_id );

		if ( is_wp_error( $this->term ) || empty( $this->term ) ) {
			$this->ID = 0;
		} else {
			$this->ID = $this->term->term_id;
		}

		if ( Thrive_Utils::is_end_user_site() && ! $this->is_default() && ! \Thrive_Utils::during_ajax() ) {
			$this->normalize_palettes();
		}
	}

	/**
	 * Set the term to be used in this instance
	 *
	 * @param $skin_id
	 */
	public function set_term( $skin_id = 0 ) {
		if ( empty( $skin_id ) ) {
			//get active skin
			$active_skin = get_terms( [
				'taxonomy'   => SKIN_TAXONOMY,
				'meta_query' => [
					[
						'key'     => static::SKIN_META_ACTIVE,
						'value'   => 1,
						'compare' => '=',
					],
				],
				'hide_empty' => 0,
			] );

			$this->term = is_wp_error( $active_skin ) || empty( $active_skin ) ? [] : $active_skin[0];
		} else {
			$this->term = Thrive_Skin_Taxonomy::get_skin_by_id( $skin_id );
		}
	}

	/**
	 * Checks if the active skin is the default skin
	 *
	 * @return bool
	 */
	public function is_default() {
		return $this->get_tag() === 'default';
	}

	/**
	 * @return bool
	 */
	public function is_active() {
		return (int) $this->get_meta( static::SKIN_META_ACTIVE ) === 1;
	}

	/**
	 * Get active typography for the skin
	 *
	 * @return mixed
	 */
	public function get_active_typography() {
		$typography_id = 0;

		$posts = get_posts( [
			'post_type'      => THRIVE_TYPOGRAPHY,
			'posts_per_page' => 1,
			'meta_query'     => [
				[
					'key'   => 'default',
					'value' => '1',
				],
			],
			'tax_query'      => [ $this->build_skin_query_params() ],
		] );

		if ( ! empty( $posts ) ) {
			$typography_id = $posts[0]->ID;
		}

		return $typography_id;
	}

	/**
	 * Get skin with meta fields
	 *
	 * @return mixed
	 */
	public function export() {
		/**
		 * @var Thrive_Skin $skin
		 */
		$skin = get_term( $this->ID, SKIN_TAXONOMY, ARRAY_A );

		foreach ( static::meta_fields() as $meta_key => $default_value ) {
			$skin['term_meta'][ $meta_key ] = get_term_meta( $this->ID, $meta_key, true );
		}

		return $skin;
	}

	/**
	 * Build query params for getting templates based on skin
	 *
	 * @return array
	 */
	public function build_skin_query_params() {
		return [
			'taxonomy' => SKIN_TAXONOMY,
			'field'    => 'id',
			'terms'    => $this->ID,
		];
	}

	/**
	 * Copy all the meta fields from the target skin to the current skin.
	 *
	 * @param $source_skin_id
	 */
	public function duplicate_meta( $source_skin_id ) {
		$metas = get_term_meta( $source_skin_id );

		foreach ( static::meta_fields() as $meta_field => $default_value ) {
			if ( ! empty( $metas[ $meta_field ] ) ) {
				$value = thrive_safe_unserialize( $metas[ $meta_field ][0] );
			} else {
				$value = $default_value;
			}

			$this->set_meta( $meta_field, $value );
		}
	}

	/**
	 * Duplicate the templates from the current skin and assigned them to the other one
	 *
	 * @param int $source_skin_id
	 */
	public function duplicate_templates( $source_skin_id ) {
		$source_skin = new Thrive_Skin( $source_skin_id );
		$templates   = $source_skin->get_templates();

		$layout_map = [];

		foreach ( $templates as $data ) {
			$source_id = $data['ID'];
			unset( $data['ID'], $data['meta_input']['tag'] );

			$template_id = wp_insert_post( $data );
			$template    = new Thrive_Template( $template_id );

			$template->copy_data_from( $source_id );
			$template->assign_to_skin( $this->ID );

			$source_template_layout = get_post_meta( $source_id, 'layout', true );
			/* create a copy for the layout so we can assign it to the new template */
			if ( empty( $layout_map[ $source_template_layout ] ) ) {
				$layout_map[ $source_template_layout ] = Thrive_Layout::create( $this->ID, $source_template_layout );
			}

			$template->set_meta( 'layout', $layout_map[ $source_template_layout ] );
		}

		$default_source_skin_layout = $source_skin->get_default_layout();
		/* default skin layout is either the copy of the one from the other skin, or one from the newly created, or just a default one */
		if ( empty( $layout_map[ $default_source_skin_layout ] ) ) {
			if ( empty( $layout_map ) ) {
				$default_layout_id = Thrive_Layout::create( $this->ID );
			} else {
				$default_layout_id = array_pop( $layout_map );
			}
		} else {
			$default_layout_id = $layout_map[ $default_source_skin_layout ];
		}

		$this->set_meta( static::DEFAULT_LAYOUT, $default_layout_id );
	}

	/**
	 * Get templates all the data from a certain skin
	 *
	 * @param              $output  string
	 * @param              $default bool
	 * @param array        $filters allow filtering results (just in case of $output !== 'ids')
	 * @param array|object $args    extra get_posts() query arguments
	 *
	 * @return array
	 */
	public function get_templates( $output = 'array', $default = false, $filters = [], $args = [] ) {
		$defaults = [
			'post_type'      => THRIVE_TEMPLATE,
			'posts_per_page' => - 1,
			'tax_query'      => [ $this->build_skin_query_params() ],
		];

		if ( $default ) {
			$defaults['meta_query'] = [
				[
					'key'   => 'default',
					'value' => '1',
				],
			];
		}

		$args = wp_parse_args( $args, $defaults );

		array_map( static function ( $key ) use ( &$filters ) {
			if ( isset( $filters[ $key ] ) ) {
				/* make sure these are always arrays */
				$filters[ $key ] = (array) $filters[ $key ];
			}
		}, [ 'primary', 'secondary', 'variable' ] );

		$posts = get_posts( $args );

		return array_filter( array_map( function ( $post ) use ( $output, $filters ) {
			if ( 'ids' === $output ) {
				$template = $post->ID;
			} else {
				$template = new Thrive_Template( $post->ID );

				if ( isset( $filters['primary'] ) && ! in_array( $template->primary_template, $filters['primary'], true ) ) {
					return false; // so it gets filtered out
				}

				if ( isset( $filters['secondary'] ) && ! in_array( $template->secondary_template, $filters['secondary'], true ) ) {
					return false; // so it gets filtered out
				}

				if ( isset( $filters['variable'] ) && ! in_array( $template->variable_template, $filters['variable'], true ) ) {
					return false; // so it gets filtered out
				}

				if ( 'array' === $output ) {
					$template = $template->export();
				}
			}

			return $template;
		}, $posts ) );
	}

	/**
	 * Get all the sections assigned to the skin
	 *
	 * @param array  $args
	 * @param string $output
	 *
	 * @return array
	 */
	public function get_sections( $args = [], $output = 'array' ) {

		$args = array_merge_recursive( [
			'post_type'      => THRIVE_SECTION,
			'posts_per_page' => - 1,
			'tax_query'      => [ $this->build_skin_query_params() ],
		], $args );

		$posts = get_posts( $args );

		return array_map( static function ( $post ) use ( $output ) {
			if ( $output === 'ids' ) {
				$section = $post->ID;
			} else {
				$section = new Thrive_Section( $post->ID );
				if ( $output === 'array' ) {
					$section = [
						'id'    => $section->ID,
						'name'  => $section->name(),
						'type'  => $section->type(),
						'thumb' => $section->thumbnail(),
					];
				}
			}

			return $section;
		}, $posts );
	}

	/**
	 * Get all the layouts assigned to the skin
	 *
	 * @param string $output
	 * @param array  $meta_fields
	 *
	 * @return array
	 */
	public function get_layouts( $output = 'array', $meta_fields = [] ) {
		$posts = get_posts( [
			'post_type'      => THRIVE_LAYOUT,
			'posts_per_page' => - 1,
			'tax_query'      => [ $this->build_skin_query_params() ],
		] );

		return array_map( static function ( $post ) use ( $output, $meta_fields ) {
			if ( $output === 'ids' ) {
				$layout = $post->ID;
			} else {
				$layout = new Thrive_Layout( $post->ID );

				if ( $output === 'array' ) {
					$layout = $layout->export( $meta_fields );
				}
			}

			return $layout;
		}, $posts );
	}

	/**
	 * Get all typographies for output
	 *
	 * @param string $output
	 *
	 * @return mixed
	 */
	public function get_typographies( $output = 'array' ) {

		$typographies = get_posts( [
			'post_type'      => THRIVE_TYPOGRAPHY,
			'posts_per_page' => - 1,
			'tax_query'      => [ $this->build_skin_query_params() ],
		] );

		/* used to fix cases where there are multiple active typography sets */
		$has_default_typography = false;

		return array_map( function ( $post ) use ( $output, &$has_default_typography ) {

			if ( $output === 'ids' ) {
				$typography = $post->ID;
			} else {
				$typography = new Thrive_Typography( $post->ID );
				$is_default = $typography->is_default();
				/* set cached ID of the skin */
				$typography->set_cached_skin_id( $this->ID );
				/* cleanup - if an active typography already exists, this one should not be active */
				if ( $is_default && $has_default_typography ) {
					$is_default = false;
					$typography->set_meta( Thrive_Typography::META_DEFAULT, 0 );
				}

				if ( $output === 'array' ) {
					$typography = [
						'ID'          => $post->ID,
						'post_title'  => $post->post_title,
						'meta_input'  => [
							'default' => $is_default,
							'style'   => $typography->style(),
						],
						'edit_url'    => tcb_get_editor_url( $post->ID ),
						'preview_url' => tcb_get_preview_url( $post->ID ),
					];
				}
				if ( $is_default ) {
					$has_default_typography = true;
				}
			}

			return $typography;
		}, $typographies );
	}

	/**
	 * Get skin preview url
	 *
	 * @return string
	 */
	public function get_preview_url() {

		$url = add_query_arg( [
			THRIVE_NO_BAR       => 1,
			THRIVE_SKIN_PREVIEW => $this->ID,
		], home_url() );

		return $url;
	}

	/**
	 * Reset default template / typography / layout and remove the rest
	 */
	public function reset() {

		$templates    = $this->get_templates( 'object' );
		$typographies = $this->get_typographies( 'object' );

		foreach ( array_merge( $templates, $typographies ) as $post ) {
			if ( $post->is_default() ) {
				$post->reset();
			} else {
				wp_trash_post( $post->ID );
			}
		}
		$layout = new Thrive_Layout( $this->get_default_layout() );
		$layout->reset();

		/* remove wizard data */
		$this->delete_meta( 'thrive_defaults' );
		$this->delete_meta( 'ttb_wizard' );
	}

	/**
	 * Get all global styles specific for the current skin
	 *
	 * @return array
	 */
	public function get_global_styles() {
		$skin_styles = [];
		$tag         = $this->get_tag();

		$global_styles_options = [
			'tve_global_button_styles',
			'tve_global_section_styles',
			'tve_global_contentbox_styles',
		];

		foreach ( $global_styles_options as $option ) {
			$styles = get_option( $option, [] );
			if ( ! empty( $styles ) && is_array( $styles ) ) {
				foreach ( $styles as $key => $value ) {
					if ( ! empty( $value['skin_tag'] ) && $value['skin_tag'] === $tag ) {
						$skin_styles[ $option ][ $key ] = $value;
					}
				}
			}
		}

		return $skin_styles;
	}

	/**
	 * Get all templates and typography and generate a new css file that will be used by the whole skin
	 */
	public function generate_style_file() {

		$style = thrive_css_helper( $this, [ 'default' => true ] )
			->generate_style( true, false, false ) // fonts are no longer included in the generated file
			->get_style();

		$templates_dir = UPLOAD_DIR_PATH . '/thrive/';

		if ( ! is_dir( $templates_dir ) ) {
			wp_mkdir_p( $templates_dir );
		}

		/* remove old template style files */
		foreach ( scandir( $templates_dir ) as $file ) {
			if ( strpos( $file, $this->style_file_prefix ) === 0 ) {
				unlink( $templates_dir . $file );
			}
		}

		/* save file name with time included so it won't be cached */
		$filename = $this->style_file_prefix . '-' . time() . '.css';

		/**
		 * Filters the actual CSS string that's going to be saved in the file.
		 *
		 * @param string      $style    CSS full style string
		 * @param string      $filename Name of the file where this will be saved
		 * @param Thrive_Skin $this     thrive skin instance
		 *
		 * @return string filtered CSS style string
		 */
		$style = apply_filters( 'thrive_css_file_content', $style, $filename, $this );

		TCB_Utils::write_file( $templates_dir . $filename, $style );

		update_option( $this->get_template_style_option_name(), $filename, true );

		/* each time we generate a new css file we also have to clear the cache */
		Thrive_Utils::clear_cache();
	}

	/**
	 * Remove all templates and typographies
	 */
	public function remove() {
		$everything_related_to_skin = array_merge(
			$this->get_sections( [], 'ids' ),
			$this->get_templates( 'ids' ),
			$this->get_layouts( 'ids' ),
			$this->get_typographies( 'ids' )
		);

		foreach ( $everything_related_to_skin as $id ) {
			wp_delete_post( $id, true );
		}
	}

	/**
	 * Get the default layout for this skin. This one is automatically assigned when creating a new template
	 *
	 * @return mixed
	 */
	public function get_default_layout() {
		return $this->get_meta( static::DEFAULT_LAYOUT );
	}

	/**
	 * Get skin tag
	 *
	 * @return mixed
	 */
	public function get_tag() {
		return $this->get_meta( static::TAG );
	}

	/**
	 * Returns the Skin Palettes
	 *
	 * @return mixed
	 */
	public function get_palettes() {
		$palettes = $this->get_meta( static::SKIN_META_PALETTES_V2 );

		if ( empty( $palettes ) ) {

			$palettes = $this->get_meta( static::SKIN_META_PALETTES );

			if ( empty( $palettes ) ) {
				$palettes = Thrive_Defaults::skin_pallets();
			}
		}

		return $palettes;
	}

	/**
	 * Updates the skin palette meta with the corresponding value received as parameter
	 *
	 * @param array   $palettes
	 * @param integer $version
	 */
	public function update_palettes( $palettes = [], $version = 1 ) {
		$this->set_meta( $version === 1 ? static::SKIN_META_PALETTES : static::SKIN_META_PALETTES_V2, $palettes );
	}

	/**
	 * Returns the skin variables
	 *
	 * @param bool $add_prefixes
	 *
	 * @return array
	 */
	public function get_variables( $add_prefixes = false ) {
		$variables = $this->get_meta( static::SKIN_META_VARIABLES );

		if ( empty( $variables ) ) {
			$variables = Thrive_Defaults::skin_variables();
		} else {
			foreach ( $variables as $key => $variable ) {
				if ( $key === 'gradients' ) {
					/**
					 * For the moment we do not support skin gradients
					 */
					$variable = [];
				}

				$variables[ $key ] = array_values( $variable );
			}
		}

		if ( $add_prefixes ) {
			$variables = array_merge( $variables, [
				'colors_prefix'    => THEME_SKIN_COLOR_VARIABLE_PREFIX,
				'gradients_prefix' => THEME_SKIN_GRADIENT_VARIABLE_PREFIX,
			] );
		}

		return $variables;
	}

	/**
	 * Constructs the variable structure for css output
	 *
	 * @return string
	 */
	public function get_variables_for_css() {
		$skin_variables = $this->get_variables();
		$data           = '';

		if ( thrive_palettes()->has_palettes() && ! empty( $this->get_meta( static::SKIN_META_PALETTES_V2 ) ) ) {
			$palette = thrive_palettes()->get_palette();

			foreach ( $palette as $variable_id => $variable ) {
				$color_name = THEME_SKIN_COLOR_VARIABLE_PREFIX . $variable['id'];
				if ( ! empty( $variable['hsla_code'] ) && ! empty( $variable['hsla_vars'] ) && is_array( $variable['hsla_vars'] ) ) {
					$data .= $color_name . ':' . $variable['hsla_code'] . ';';

					foreach ( $variable['hsla_vars'] as $var => $css_variable ) {
						$data .= $color_name . '-' . $var . ':' . $css_variable . ';';
					}
				} else {
					$data .= $color_name . ':' . $variable['color'] . ';';

					if ( function_exists( 'tve_rgb2hsl' ) && function_exists( 'tve_print_color_hsl' ) ) {
						$data .= tve_print_color_hsl( $color_name, tve_rgb2hsl( $variable['color'] ) );
					}
				}
			}
		} elseif ( ! empty( $skin_variables ) ) {
			/**
			 * TODO: remove this after some time passes and all the clients have updated the website to palettes V2
			 */
			foreach ( $skin_variables['colors'] as $color ) {
				$color_name = THEME_SKIN_COLOR_VARIABLE_PREFIX . $color['id'];

				if ( empty( $color['hsla_code'] ) && empty( $color['hsla_vars'] ) ) {
					$data .= $color_name . ':' . $color['color'] . ';';
					/* convert variables to hsl & print them */
					if ( function_exists( 'tve_rgb2hsl' ) && function_exists( 'tve_print_color_hsl' ) ) {
						$hsl_data = tve_rgb2hsl( $color['color'] );
						$data     .= tve_print_color_hsl( $color_name, $hsl_data );
					}
				} else {
					$data .= $color_name . ':' . $color['hsla_code'] . ';';
					/* convert variables to hsl & print them */
					$data .= $this->print_hsla_vars( $color_name, $color['hsla_vars'] );
				}
			}

			foreach ( $skin_variables['gradients'] as $gradient ) {
				$data .= THEME_SKIN_GRADIENT_VARIABLE_PREFIX . $gradient['id'] . ':' . $gradient['gradient'] . ';';
			}
		}

		if ( function_exists( 'tve_prepare_master_variable' ) ) {

			$master_variable         = thrive_palettes()->get_master_hsl();
			$general_master_variable = tve_prepare_master_variable( [ 'hsl' => $master_variable ] );
			$theme_master_variable   = str_replace( '--tcb-main-master', '--tcb-theme-main-master', $general_master_variable );

			$data .= $general_master_variable;
			$data .= $theme_master_variable;
		}

		return $data;
	}

	/**
	 * Normalize palette function
	 */
	public function normalize_palettes() {

		$palettes = $this->get_palettes();

		if ( empty( $palettes['version'] ) ) {
			$skin_variables = $this->get_variables();
			$palette_types  = [ 'original', 'modified' ];
			$palettes_v2    = [];

			foreach ( $palette_types as $type ) {
				if ( ! empty( $palettes[ $type ] ) && is_array( $palettes[ $type ] ) ) {
					foreach ( $palettes[ $type ] as $index => $palette ) {
						$master_var = array_filter( $palettes[ $type ][ $index ]['colors'], static function ( $ar ) {
							return isset( $ar['hsl'] ) && ! empty( $ar['hsl'] );
						} );
						$master_var = reset( $master_var );

						if ( empty( $palettes_v2[ $index ] ) ) {
							$palettes_v2[ $index ] = [
								'name' => $palette['name'],
							];
						}

						foreach ( $palettes[ $type ][ $index ]['colors'] as $j => $data ) {
							$hsla_code = '';
							$hsla_vars = [];

							if ( isset( $data['hsl'] ) && ! empty( $data['hsl'] ) ) {
								$palettes_v2[ $index ][ $type . '_hsl' ] = $data['hsl'];

								$hsla_vars      = $this->get_hsla_vars( $data['hsl'] );
								$hsla_vars['a'] = 'var(' . THEME_MAIN_COLOR_A . ',' . $hsla_vars['a'] . ')';

								$hsla_code = tve_prepare_hsla_code( $hsla_vars );
							} elseif ( isset( $data['hsl_parent_dependency'] ) && ! empty( $data['hsl_parent_dependency'] ) ) {
								$_color_index = array_search( $data['id'], array_column( $skin_variables['colors'], 'id' ) );
								$lock         = ! empty( $skin_variables['colors'][ $_color_index ]['lock'] ) && is_array( $skin_variables['colors'][ $_color_index ]['lock'] ) ? $skin_variables['colors'][ $_color_index ]['lock'] : [];

								$hsla_vars = $this->get_hsla_vars( $master_var['hsl'], $data['hsl_parent_dependency'], $lock );
								$hsla_code = tve_prepare_hsla_code( $hsla_vars );
							}

							if ( ! empty( $hsla_vars ) ) {
								$palettes[ $type ][ $index ]['colors'][ $j ]['hsla_code'] = $hsla_code;
								$palettes[ $type ][ $index ]['colors'][ $j ]['hsla_vars'] = $hsla_vars;
								if ( $palettes['active_id'] === $index ) {
									$color_index = array_search( $data['id'], array_column( $skin_variables['colors'], 'id' ) );

									if ( $color_index !== false ) {
										$skin_variables['colors'][ $color_index ]['hsla_code'] = $hsla_code;
										$skin_variables['colors'][ $color_index ]['hsla_vars'] = $hsla_vars;
									}
								}
							}
						}
					}
				}
			}

			if ( ! empty( $skin_variables['colors'] ) && is_array( $skin_variables['colors'] ) ) {

				$master_variables = array_filter( $skin_variables['colors'], static function ( $ar ) {
					return ( isset( $ar['parent'] ) && (int) $ar['parent'] === - 1 );
				} );

				if ( ! empty( $master_variables ) ) {
					$variable = reset( $master_variables );
					if ( ! empty( $variable['hsl'] ) && empty( thrive_palettes()->get_master_hsl() ) ) {
						thrive_palettes()->update_master_hsl( $variable['hsl'] );
						$palettes_v2[ $palettes['active_id'] ]['modified_hsl'] = $variable['hsl'];
					}
				}

				$config = [
					'v'       => 1,
					'palette' => [],
				];
				foreach ( $skin_variables['colors'] as $color ) {
					$config['palette'][ $color['id'] ] = [
						'id'    => $color['id'],
						'color' => $color['color'],
						'name'  => $color['name'],
					];

					if ( isset( $color['parent'] ) && (int) $color['parent'] === - 1 ) {
						$config['palette'][ $color['id'] ]['parent'] = - 1;
					}

					if ( ! empty( $color['hsla_code'] ) && ! empty( $color['hsla_vars'] ) ) {
						$config['palette'][ $color['id'] ]['hsla_code'] = $color['hsla_code'];
						$config['palette'][ $color['id'] ]['hsla_vars'] = $color['hsla_vars'];
					}
				}

				thrive_palettes()->maybe_update( $config );

				$this->update_palettes( [
					'active_id' => $palettes['active_id'],
					'version'   => 2,
					'palettes'  => $palettes_v2,
				], 2 );
			}

			$this->update_variables( $skin_variables );
			$palettes['version'] = 1;
			$this->update_palettes( $palettes );
		}
	}

	/**
	 * Returns the HSLA Vars
	 *
	 * @param array $master_hsl
	 * @param array $parent_dependency
	 * @param array $lock
	 *
	 * @return array
	 */
	public function get_hsla_vars( $master_hsl = [], $parent_dependency = [], $lock = [] ) {

		$master_s = (float) ( $master_hsl['s'] * 100 );
		$master_l = (float) ( $master_hsl['l'] * 100 );

		$hue        = 'var(' . THEME_MAIN_COLOR_H . ',' . $master_hsl['h'] . ')';
		$saturation = 'var(' . THEME_MAIN_COLOR_S . ',' . $master_s . '%)';
		$lightness  = 'var(' . THEME_MAIN_COLOR_L . ',' . $master_l . '%)';
		$alpha      = empty( $master_hsl['a'] ) ? '1' : $master_hsl['a'];

		if ( ! empty( $parent_dependency ) ) {
			$hue_diff     = $master_hsl['h'] - $parent_dependency['h'];
			$hue_diff_abs = abs( $hue_diff );
			$hue_sign     = $hue_diff < 0 ? '+' : '-';
			$hue          = "calc($hue $hue_sign $hue_diff_abs )";

			if ( ! empty( $lock['saturation'] ) ) {
				$saturation = ( (int) ( $parent_dependency['s'] * 100 ) ) . '%';
			} else {
				$saturation_diff     = (int) ( ( $master_hsl['s'] - $parent_dependency['s'] ) * 100 );
				$saturation_diff_abs = abs( $saturation_diff );
				$saturation_sign     = $saturation_diff < 0 ? '+' : '-';
				$saturation          = "calc($saturation $saturation_sign $saturation_diff_abs% )";
			}

			if ( ! empty( $lock['lightness'] ) ) {
				$lightness = ( (int) ( $parent_dependency['l'] * 100 ) ) . '%';
			} else {
				$lightness_diff     = (int) ( ( $master_hsl['l'] - $parent_dependency['l'] ) * 100 );
				$lightness_diff_abs = abs( $lightness_diff );
				$lightness_sign     = $lightness_diff < 0 ? '+' : '-';
				$lightness          = "calc($lightness $lightness_sign $lightness_diff_abs% )";
			}
			if ( ! empty( $parent_dependency['a'] ) ) {
				$alpha = $parent_dependency['a'];
			}
		}

		return [
			'h' => $hue,
			's' => $saturation,
			'l' => $lightness,
			'a' => $alpha,
		];
	}

	/**
	 * Updates the skin variables
	 *
	 * @param array $variables
	 */
	public function update_variables( $variables = [] ) {
		$this->set_meta( static::SKIN_META_VARIABLES, $variables );
	}

	/**
	 * Called after a skin is marked as active.
	 *
	 * Updates the active palette modified variables with the previously variables
	 */
	public function update_colors() {
		$config = $this->get_meta( static::SKIN_META_PALETTES_V2 );

		if ( ! empty( $config ) ) {

			$active_id = $config['active_id'];

			if ( ! empty( $config['palettes'][ $active_id ]['modified_hsl'] ) ) {
				if ( empty( thrive_palettes()->get_master_hsl() ) ) {
					thrive_palettes()->update_master_hsl( $config['palettes'][ $active_id ]['modified_hsl'] );
				} else {
					$config['palettes'][ $active_id ]['modified_hsl'] = thrive_palettes()->get_master_hsl();
					$this->set_meta( static::SKIN_META_PALETTES_V2, $config );
				}
			}
		}
	}

	/**
	 * Filter templates based on the current active skin
	 *
	 * @param array  $templates
	 * @param string $type
	 *
	 * @return mixed
	 */
	public function filter_templates( $templates, $type = '' ) {

		$active_skin_tag = $this->get_tag();

		foreach ( $templates as $key => & $template ) {
			$order = 0; // this is used to place the theme templates first in the list
			if ( Thrive_Architect::is_light() ) {
				if ( empty( $template['unlocked'] ) ) {
					if ( ! empty( $template['skin_tag'] ) && $template['skin_tag'] !== $active_skin_tag ) {
						unset( $templates[ $key ] );
					} elseif ( empty( $type ) || ! Thrive_Utils::ct_type_is_unlocked_light_template( $type ) ) {
						$template['locked'] = 1;
						$order              = PHP_INT_MAX;
					}
				}
			} else {
				/* Here we just need to set theme templates to be the first on the list */
				if ( ! empty( $template['skin_tag'] ) ) {
					$order = - 1;
				}
			}

			$template['order'] = $order;

			// If the skin tag is set and different from the active skin tag => we do not show it at all
			if ( ! empty( $template['skin_tag'] && $template['skin_tag'] !== $active_skin_tag ) ) {
				unset( $templates[ $key ] );
			}
		}

		return $templates;
	}

	/**
	 * Filter landing pages based on their skin tag
	 *
	 * @param $landing_pages
	 *
	 * @return mixed
	 */
	public function filter_landing_pages( $landing_pages ) {
		$active_skin_tag   = $this->get_tag();
		$has_theme_builder = Thrive_Theme::is_active();
		foreach ( $landing_pages as $key => &$template ) {
			if ( ! empty( $template['order'] ) ) {
				$template['order'] = 0;// this is used to place the theme templates first in the list

				//if it's from current skin -> show it first and marked it as being part of the theme
				if ( isset( $template['skin_tag'] ) && $has_theme_builder && ! empty( $template['skin_tag'] ) ) {
					$template['order']     = $active_skin_tag === $template['skin_tag'] ? - 2 : - 1;
					$template['from_skin'] = 1;
				}
				/* If TAR is not installed -> we show only the landing pages which are set as homepages or as silo */
				if ( isset( $template['home'], $template['silo'] ) && Thrive_Architect::is_light() && $template['home'] !== '1' && $template['silo'] !== '1' ) {
					$landing_pages[ $key ]['locked'] = 1;
				}
			}

		}
		return $landing_pages;
	}

	/**
	 * Get an array where the keys are each layout ID used on this skin, and the values are the names and edit URLs of the templates which use that layout.
	 *
	 * @return array
	 */
	public function get_layouts_templates_map() {
		$layouts_templates_map = [];

		foreach ( $this->get_templates( 'object' ) as $template ) {
			$layouts_templates_map[ (int) $template->get_layout() ][] = [
				'name'     => $template->post_title,
				'edit_url' => $template->edit_url(),
			];
		}

		return $layouts_templates_map;
	}

	/**
	 * Check for duplicate skin names and generates a unique one
	 *
	 * @param string $name
	 * @param int    $index
	 *
	 * @return string
	 */
	public static function generate_unique_name( $name = '', $index = 0 ) {
		$additional = '';
		if ( $index ) {
			$additional = ' ' . $index;
		}

		$same_name_skin = get_term_by( 'name', $name . $additional, SKIN_TAXONOMY );

		if ( ! empty( $same_name_skin ) ) {
			return static::generate_unique_name( $name, ++ $index );
		}

		return $name . $additional;
	}

	/**
	 * Breadcrumbs labels are saved at skin level
	 *
	 * @return array
	 */
	public function get_breadcrumbs_labels() {
		$labels         = $this->get_meta( static::BREADCRUMBS_LABELS );
		$default_labels = Thrive_Breadcrumbs::get_default_labels();

		$labels = array_merge( $default_labels, is_array( $labels ) ? $labels : [] );

		foreach ( $labels as $key => $label ) {
			$labels[ $key ] = apply_filters( 'wpml_translate_single_string', $label, 'Breadcrumbs', ucfirst( $key ) );
		}

		return $labels;
	}

	/**
	 * Get the default header / footer WP_Post instance
	 *
	 * @param string $key
	 *
	 * @return int|mixed
	 */
	public function get_default_data( $key ) {
		$skin_defaults = $this->get_meta( 'thrive_defaults' ) ?: [];

		return isset( $skin_defaults[ $key ] ) ? $skin_defaults[ $key ] : 0;
	}

	/**
	 * Update the default meta for the skin
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return $this
	 */
	public function set_default_data( $key, $value ) {
		$skin_defaults         = $this->get_meta( 'thrive_defaults' ) ?: [];
		$skin_defaults[ $key ] = $value;

		$this->set_meta( 'thrive_defaults', $skin_defaults );

		return $this;
	}

	/**
	 * Generate a new tag for the skin
	 */
	public function regenerate_tag() {
		$tag = uniqid();
		$this->set_meta( static::TAG, $tag );

		return $tag;
	}

	/**
	 * Returns the Thrive Template style option name
	 *
	 * can be overridden by other functions that extend this class
	 * Used in thrive apprentice to override this option name
	 *
	 * @return string
	 */
	public function get_template_style_option_name() {
		return THRIVE_TEMPLATE_STYLE;
	}

	/**
	 * Get all the meta fields for a skin
	 *
	 * @return array
	 */
	public static function meta_fields() {

		return [
			self::SKIN_META_ACTIVE              => 0,
			self::TAG                           => '',
			self::DEFAULT_LAYOUT                => 0,
			self::SKIN_META_PALETTES            => [],
			self::SKIN_META_VARIABLES           => [],
			self::BREADCRUMBS_LABELS            => [],
			self::IS_WIZARD_SKIPPED             => 0,
			self::SKIN_META_PALETTES_V2         => [],
			WooMain::GENERATED_TEMPLATES_OPTION => 0,
		];
	}

	/**
	 * Prepares the variables for print
	 *
	 * @param {string} $color_name
	 * @param {array} $vars
	 *
	 * @return string
	 */
	private function print_hsla_vars( $color_name, $vars ) {
		$hsl_vars = [
			$color_name . '-h:' . $vars['h'],
			$color_name . '-s:' . $vars['s'],
			$color_name . '-l:' . $vars['l'],
			$color_name . '-a:' . $vars['a'],
		];

		return implode( ';', $hsl_vars ) . ';';
	}
}

/**
 * Return a Thrive_Skin instance
 *
 * @param int  $id
 * @param bool $apply_filter whether or not to apply the `thrive_theme_override_skin` filter.
 *                           This allows getting the default TTB skin in a context where it would be otherwise overridden, such as on a Thrive Apprentice page
 *
 * @return Thrive_Skin
 */
function thrive_skin( $id = 0, $apply_filter = true ) {

	if ( empty( $id ) && Thrive_Utils::is_skin_preview() ) {
		$id = (int) $_GET[ THRIVE_SKIN_PREVIEW ];
	}

	$skin = Thrive_Skin::instance_with_id( $id );

	if ( $apply_filter ) {
		/**
		 * Allows other plugins that uses the skin logic to override the skin that is constructed in this function
		 * used in thrive-apprentice plugin
		 *
		 * @param Thrive_Skin $skin
		 */
		$skin = apply_filters( 'thrive_theme_override_skin', $skin );
	}

	return $skin;
}
