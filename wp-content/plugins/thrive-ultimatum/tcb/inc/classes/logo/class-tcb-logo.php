<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

defined( 'TCB_TABLET_THRESHOLD' ) || define( 'TCB_TABLET_THRESHOLD', 768 );
defined( 'TCB_DESKTOP_THRESHOLD' ) || define( 'TCB_DESKTOP_THRESHOLD', 1024 );

/**
 * Class TCB_Logo
 *
 * Implementation of the Logo Element, featuring image optimization and art direction with the <picture> tag - load multiple image versions depending on the device type.
 */
class TCB_Logo {
	/* component name */
	const COMPONENT = 'logo';
	/* identifier used as a class name */
	const IDENTIFIER = 'tcb-logo';
	/* shortcode tag in which the logo HTML is wrapped */
	const SHORTCODE_TAG = 'tcb_logo';
	/* name of the option in the DB where the logo data is stored */
	const OPTION_NAME = 'tcb_logo_data';

	const DELETED_PLACEHOLDER_SRC = 'editor/css/images/logo_deleted_placeholder.png';

	/* list of devices that can have their own logo images ( desktop, tablet, mobile were shortened like this in order to work with mediaAttr() in JS ) */
	private static $all_devices = [ 'd', 't', 'm' ];

	/**
	 * TCB_Logo constructor.
	 */
	public function __construct() {
		$this->hooks();
	}

	/**
	 * Add actions and filters.
	 */
	private function hooks() {
		add_action( 'init', [ $this, 'init_shortcode' ] );

		add_filter( 'tcb_main_frame_localize', [ $this, 'add_localize_params' ] );
		add_filter( 'tcb_content_allowed_shortcodes', [ $this, 'tcb_content_allowed_shortcodes' ] );
	}

	/**
	 * Add data to the main frame localize parameters.
	 *
	 * @param $data
	 *
	 * @return mixed
	 */
	public function add_localize_params( $data ) {
		$logos = static::get_logos();

		$active_logos = array_filter( $logos, function ( $logo ) {
			return (int) $logo['active'] === 1;
		} );

		/* get the src for each attachment ID */
		foreach ( $active_logos as $key => $logo ) {
			$logo_data                        = static::get_attachment_data( $logo['id'], $logo );
			$active_logos[ $key ]['src']      = $logo_data['src'];
			$active_logos[ $key ]['width']    = $logo_data['width'];
			$active_logos[ $key ]['height']   = $logo_data['height'];
			$active_logos[ $key ]['data-alt'] = empty( $logo_data['data-alt'] ) ? '' : $logo_data['data-alt'];
		}

		$data['logo'] = array(
			'routes'              => array(
				'base'        => get_rest_url( get_current_blog_id(), 'tcb/v1/logo' ),
				'rename_logo' => get_rest_url( get_current_blog_id(), 'tcb/v1/logo/rename_logo' ),
			),
			/* only localize the active logos */
			'sources'             => array_values( $active_logos ),
			'deleted_placeholder' => tve_editor_url( static::DELETED_PLACEHOLDER_SRC ),
			'is_ttb_active'       => tve_dash_is_ttb_active(),
			'is_ta_active'        => tve_dash_is_plugin_active( 'thrive-apprentice' ),
		);

		return $data;
	}

	/**
	 * Render the element. Inside the editor, render a simplified version without responsive stuff.
	 * On the frontend, render a <source> tag for each logo chosen for a responsive device screen.
	 *
	 * @param array $attr
	 * @param bool  $render_fallback
	 *
	 * @return string
	 */
	public static function render_logo( $attr = [], $render_fallback = false ) {
		/* if the desktop logo ID is not set, use id = 0 as default and set it in the attr */
		if ( ! isset( $attr['data-id-d'] ) ) {
			$attr['data-id-d'] = 0;
		}
		$desktop_id = (int) $attr['data-id-d'];

		$logos = static::get_logos();

		/* set the desktop source as a fallback; get only the src here, since this is an all-browser compatible version */
		$fallback_data = static::get_attachment_data( $desktop_id, isset( $logos[ $desktop_id ] ) ? $logos[ $desktop_id ] : null );

		/* If we do not have alt in attr, we read it from fallback data */
		if ( empty( $attr['data-alt'] ) ) {
			$attr['data-alt'] = empty( $fallback_data['data-alt'] ) ? '' : $fallback_data['data-alt'];
		}

		$img_attr = array(
			'src'    => $fallback_data['src'],
			'height' => $fallback_data['height'],
			'width'  => $fallback_data['width'],
			'alt'    => $attr['data-alt'],
			'style'  => ! empty( $attr['data-img-style'] ) ? $attr['data-img-style'] : '',
		);

		/**
		 * Handle logo image loading
		 */
		if ( isset( $attr['loading'] ) ) {
			$img_attr['loading'] = $attr['loading'];
			unset( $attr['loading'] );
		} else {
			$img_attr['class'] = 'tve-not-lazy-loaded';
		}

		/* GIFs aren't compatible with srcset, so we use the fallback version */
		if ( ! empty( $img_attr['src'] ) && substr( $img_attr['src'], - 4 ) === '.gif' ) {
			$render_fallback = true;
		}

		/* in the editor or when doing ajax ( when the logo is in a symbol ) or when doing rest ( when you add new headers/footers and start from cloud templates ) or when we set a flag, return the desktop src only */
		if ( TCB_Utils::in_editor_render() || wp_doing_ajax() || TCB_Utils::is_rest() || $render_fallback ) {
			$content = TCB_Utils::wrap_content( '', 'img', '', '', $img_attr );
		} else {
			/* if the fallback data is empty and we're outside the editor, return an empty string ( this case happens when the logo was deleted ) */
			if ( empty( $fallback_data['src'] ) ) {
				$content = '';
			} else {
				$content = static::get_picture_element( $attr, $img_attr );
			}
		}

		$logo_url = apply_filters( 'tcb_logo_site_url', '' );

		/* We have to process the shortcode here because we cannot send it as a param inside another shortcode ( logo ) */
		if ( ! empty( $attr['data-dynamic-link'] ) ) {
			$shortcode = "{$attr['data-dynamic-link']} id={$attr['data-shortcode-id']}";
			if ( ! empty( $attr['data-custom-redirect'] ) ) {
				$shortcode .= " logout-redirect={$attr['data-custom-redirect']}";
			}
			$attr['href'] = do_shortcode( "[{$shortcode}]" );
		}

		/* embed the img in a link instead of wrapping it in a div (if an url exists) */
		if ( empty( $attr['href'] ) ) {
			if ( empty( $logo_url ) || ! empty( $attr['data-remove-href'] ) ) {
				unset( $attr['href'] );
			} else {
				$attr['href'] = $logo_url;
			}
		}

		$href = isset( $attr['href'] ) ? $attr['href'] : '';

		/**
		 * Allows filtering the final value of the `href` logo attribute
		 *
		 * @param string $href current url
		 * @param array  $attr array of shortcode attributes
		 */
		$href = apply_filters( 'tcb_logo_url', $href, $attr );

		if ( ! empty( $href ) ) {
			$attr['href'] = $href;
		}

		return TCB_Utils::wrap_content( $content, 'a', '', static::get_classes( $attr ), static::get_attr( $attr ) );
	}

	/**
	 * Get the picture element containing the sources. ( For info on how this works, see https://developer.mozilla.org/en-US/docs/Web/HTML/Element/picture )
	 *
	 * @param array $attr
	 * @param array $img_attr
	 *
	 * @return string
	 */
	public static function get_picture_element( $attr, $img_attr ) {
		$picture_content = '';

		foreach ( static::$all_devices as $device ) {
			if ( isset( $attr[ 'data-id-' . $device ] ) ) {
				$id = (int) $attr[ 'data-id-' . $device ];

				$media_attr = [
					'srcset' => static::get_srcset( $id ),
				];

				/* add media rules to restrict where each source is displayed */
				switch ( $device ) {
					case 'd':
						$media_attr['media'] = '(min-width:' . TCB_DESKTOP_THRESHOLD . 'px)';
						break;
					case 't':
						$media_attr['media'] = '(min-width:' . TCB_TABLET_THRESHOLD . 'px) and (max-width:' . TCB_DESKTOP_THRESHOLD . 'px)';
						break;
					case 'm':
						$media_attr['media'] = '(max-width:' . ( TCB_TABLET_THRESHOLD - 1 ) . 'px)';
						break;
					default:
						break;
				}

				$picture_content .= TCB_Utils::wrap_content( '', 'source', '', '', $media_attr );
			}
		}

		/* add the fallback img */
		$picture_content .= TCB_Utils::wrap_content( '', 'img', '', '', $img_attr );

		/* wrap it in the <picture> tag and return */

		return TCB_Utils::wrap_content( $picture_content, 'picture' );
	}

	/**
	 * Get all the attachment data for this logo ID. If the logo ID is not found or we don't have an attachment ID, return placeholder data instead.
	 *
	 * @param int   $id
	 * @param array $logo
	 *
	 * @return array
	 */
	public static function get_attachment_data( $id, $logo = null ) {
		$attachment_id = static::get_attachment_id( $id );

		if ( empty( $attachment_id ) ) {
			/* get the placeholder for this id ( it can differ depending on the ID : 0 and 1 have their own placeholder ) */
			$data = static::get_placeholder_data( $id, $logo );
		} else {
			$attachment_data = wp_get_attachment_image_src( $attachment_id, 'full' );

			if ( empty( $attachment_data[0] ) ) {
				$data = static::get_placeholder_data( $id );
			} else {
				$data = array(
					'src'      => $attachment_data[0],
					'width'    => $attachment_data[1],
					'height'   => $attachment_data[2],
					'data-alt' => trim( strip_tags( get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ) ),
				);
			}
		}

		return $data;
	}

	/**
	 * For the given logo ID, look for the attachment ID and return it. If it's not found, return null.
	 *
	 * @param int $id
	 *
	 * @return mixed|null
	 */
	public static function get_attachment_id( $id ) {
		$logos = static::get_logos();
		$index = - 1;

		/* look for the logo ID in the array of logo data */
		foreach ( $logos as $key => $logo_data ) {
			if ( $id === $logo_data['id'] ) {
				$index = $key;
				break;
			}
		}

		$attachment_id = null;

		/* if the logo ID was not found, render the placeholder */
		if ( $index !== - 1 ) {
			/* if we found the key for the logo ID, get the src */
			$logo_data = $logos[ $index ];

			/* if the logo is active or it's light or dark, start looking for the image ID */
			if ( $logo_data['active'] || $id === 0 || $id === 1 ) {
				if ( ! empty( $logo_data['attachment_id'] ) ) {
					$attachment_id = $logo_data['attachment_id'];
				}
			}
		}

		return $attachment_id;
	}

	/**
	 * Get the image source for this logo ID. It can be a placeholder too
	 *
	 * @param $id
	 *
	 * @return mixed
	 */
	public static function get_src( $id ) {
		$attachment_data = static::get_attachment_data( $id );

		return $attachment_data['src'];
	}

	/**
	 * Get the srcset attribute for this logo ID. If empty, return the normal source instead.
	 *
	 * @param $id
	 *
	 * @return bool|mixed|string
	 */
	public static function get_srcset( $id ) {
		$attachment_id = static::get_attachment_id( $id );

		if ( ! empty( $attachment_id ) ) {
			/* get the full srcset so the browser can pick the best image size for the current screen */
			$srcset = wp_get_attachment_image_srcset( $attachment_id );
		}

		if ( empty( $srcset ) ) {
			/* if the srcset is empty (this happens for SVGs and for small images) or we don't want the srcset, get the src instead */
			$srcset = static::get_src( $id );
		}

		return $srcset;
	}

	/**
	 * Return the placeholder data according to the ID.
	 * For id = 0 or 1, return the light/dark placeholder, for any other ID, return a 'logo has been deleted' placeholder.
	 *
	 * @param int   $id
	 * @param array $logo
	 *
	 * @return array
	 */
	public static function get_placeholder_data( $id, $logo = null ) {
		$data            = [
			'height' => '',
			'width'  => '',
		];
		$is_default_logo = ! empty( $logo ) && isset( $logo['scope'] ) && $logo['scope'] === 'tva';

		if ( $id === 0 || ( $is_default_logo && $logo['name'] === 'Dark' ) ) {
			$data['src'] = tve_editor_url( 'editor/css/images/logo_placeholder_dark.svg' );
		} elseif ( $id === 1 || ( $is_default_logo && $logo['name'] === 'Light' ) ) {
			$data['src'] = tve_editor_url( 'editor/css/images/logo_placeholder_light.svg' );
		} else {
			/* If we're in the editor, render a <logo image deleted> image. On the frontend, leave it blank. */
			$data['src'] = TCB_Utils::in_editor_render() ? tve_editor_url( static::DELETED_PLACEHOLDER_SRC ) : '';
		}

		$data['is_placeholder'] = 1;

		return $data;
	}

	/**
	 * Get the src directly ( called from TTB )
	 *
	 * @param int $id
	 *
	 * @return mixed
	 */
	public static function get_placeholder_src( $id = 0 ) {
		$data = static::get_placeholder_data( $id );

		return $data['src'];
	}

	/**
	 * Add the two initial logos and their placeholders ( these exist without having to be added manually and cannot be deleted ).
	 *
	 * @return array
	 */
	public static function initialize_default_logos() {
		$logos = [
			0 => [
				'id'            => 0,
				'attachment_id' => '',
				'active'        => 1,
				'default'       => 1,
				'name'          => 'Dark',
			],
			1 => [
				'id'            => 1,
				'attachment_id' => '',
				'active'        => 1,
				'default'       => 1,
				'name'          => 'Light',
			],
		];
		/* update inside the DB */
		update_option( static::OPTION_NAME, $logos );

		return $logos;
	}

	/**
	 * Get the logo data. ( get_option() is cached, so it's ok to call this lots of times ).
	 *
	 * @param bool $apply_filter
	 *
	 * @return mixed|void
	 */
	public static function get_logos( $apply_filter = true ) {
		$logos = get_option( static::OPTION_NAME );

		/* if the option is empty, then we have to initialize the logo array with the default values */
		if ( empty( $logos ) ) {
			/* initialize the default logos */
			$logos = static::initialize_default_logos();
		}

		if ( $apply_filter ) {
			/**
			 * Allow other plugins to alter the logo list.
			 * Used in Thrive Apprentice plugin to modify the default light and dark logo when the system edits an apprentice page
			 */
			$logos = apply_filters( 'tcb_get_logos', $logos );
		}

		return $logos;
	}

	/**
	 * @param $attr
	 *
	 * @return string
	 */
	private static function get_classes( $attr ) {
		$class = [ static::IDENTIFIER, THRIVE_WRAPPER_CLASS ];

		/* set responsive/animation classes, if they are present */
		if ( ! empty( $attr['class'] ) ) {
			$class[] = $attr['class'];
		}

		return implode( ' ', $class );
	}

	/**
	 * @param $attr
	 *
	 * @return array
	 */
	private static function get_attr( $attr ) {
		/* if we're not in the editor or not doing ajax ( for symbols ), remove the logo ID dataset */
		if ( ! TCB_Utils::in_editor_render() && ! wp_doing_ajax() ) {
			foreach ( static::$all_devices as $device ) {
				unset( $attr[ 'data-id-' . $device ] );
			}
		}

		/* we don't need to save this since it's stored in the image */
		unset( $attr['data-alt'] );

		return $attr;
	}

	/**
	 * Add the logo shortcode.
	 */
	public function init_shortcode() {
		add_shortcode( static::SHORTCODE_TAG, function ( $attr, $content, $tag ) {
			/**
			 * Ability to modify logo attributes before render.
			 *
			 * Used in thrive apprentice to modify the image URL for apprentice pages
			 *
			 * @param array $attributes
			 */
			$attr = apply_filters( 'tcb_logo_attributes', TCB_Post_List_Shortcodes::parse_attr( $attr, $tag ) );

			return TCB_Logo::render_logo( $attr );
		} );
	}

	/**
	 * We need to add our shortcodes to this array in order for them to be processed in the editor.
	 * If we're on the frontend, we don't have to do this.
	 *
	 * @param $shortcodes
	 *
	 * @return array
	 */
	public function tcb_content_allowed_shortcodes( $shortcodes ) {
		if ( is_editor_page_raw( true ) ) {
			$shortcodes = array_merge( $shortcodes, [ static::SHORTCODE_TAG ] );
		}

		return $shortcodes;
	}

	/**
	 * Register REST Routes for the Logo.
	 */
	public static function rest_api_init() {
		require_once TVE_TCB_ROOT_PATH . 'inc/classes/logo/class-tcb-logo-rest.php';
	}
}

new TCB_Logo();
