<?php /** @noinspection PhpCSValidationInspection */
/** @noinspection PhpCSValidationInspection */
/** @noinspection PhpCSValidationInspection */

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

abstract class TVE_Dash_Product_Abstract {

	private static $instances = array();

	protected $tag;

	protected $slug = '';

	protected $version = '';

	protected $logoUrl;

	protected $logoUrlWhite;

	protected $title;

	protected $description;

	protected $productIds;

	protected $type;

	protected $activated = false;

	protected $moreLinks = array();

	protected $button = array();

	protected $required_data
		= array(
			'tag',
			'logoUrl',
			'title',
			'description',
			'productIds',
			'type',
			'activated',
		);

	protected $needs_architect = false;

	/**
	 * Is true if the TAR version is incompatible with the product version
	 *
	 * @var bool
	 */
	protected $incompatible_architect_version = false;

	public function __construct( $data = array() ) {
		foreach ( $data as $key => $value ) {
			$this->{$key} = $value;
		}
	}

	public function localize_data() {
		return [
			'type'         => $this->type,
			'tag'          => $this->tag,
			'slug'         => $this->slug,
			'title'        => $this->title,
			'is_activated' => $this->is_activated(),
			'capability'   => $this->get_cap(),
			'has_access'   => self::has_access(),
			'admin_url'    => $this->get_admin_url(),
			'version'      => $this->version,
		];
	}

	/**
	 * Check each  required data if it's empty
	 *
	 * @throws Exception
	 */
	final public function validate() {
		foreach ( $this->required_data as $data ) {
			if ( $data === 'productIds' && ! is_array( $this->productIds ) ) {
				throw new Exception( "{$data} is not array" );
			}
			if ( is_null( $this->{$data} ) ) {
				throw new Exception( "Field {$data} is empty" );
			}
		}
	}

	public function render() {
		try {
			$this->validate();
		} catch ( Exception $exception ) {
			require TVE_DASH_PATH . '/templates/product/error.phtml';

			return;
		}

		if ( $this->is_activated() ) {
			require TVE_DASH_PATH . '/templates/product/activated.phtml';

			return;
		}

		require TVE_DASH_PATH . '/templates/product/inactive.phtml';
	}

	public function is_activated() {
		if ( $this->activated === true ) {
			return true;
		}

		return TVE_Dash_Product_LicenseManager::getInstance()->itemActivated( $this );
	}

	public function render_button() {
		return sprintf( '<a class="%s" href="%s" target="%s" data-source="%s">%s</a>',
			"tvd-waves-effect tvd-waves-light tvd-btn tvd-btn-green tvd-full-btn" . ( $this->button['active'] ? '' : 'tvd-disabled' ) . ' ' . ( ! empty( $this->button['classes'] ) ? $this->button['classes'] : '' ),
			$this->button['active'] && ! empty( $this->button['url'] ) ? $this->button['url'] : 'javascript:void(0)',
			! empty( $this->button['target'] ) ? $this->button['target'] : '_self',
			! empty( $this->button['data-source'] ) ? $this->button['data-source'] : '',
			$this->button['label']
		);
	}

	public function render_more_links() {

		if ( $this->is_activated() && ! empty( $this->moreLinks ) ) {
			$links = '<a class="tvd-dropdown-button tvd-btn-floating tvd-right tvd-card-options" data-constrainwidth="false" data-beloworigin="true" data-alignment="right" data-activates="dropdown-' . $this->tag . '" href="javascript:void(0)"><i class="tvd-icon-more_vert"></i></a>';
			$links .= '<ul id="dropdown-' . $this->tag . '" class="tvd-dropdown-content" style="white-space: nowrap; position: absolute; top: 43px; left: 162px; opacity: 1; display: none;">';
			foreach ( $this->moreLinks as $link ) {
				$icon_class = isset( $link['icon_class'] ) ? $link['icon_class'] : '';
				$class      = isset( $link['class'] ) ? $link['class'] : '';
				$target     = isset( $link['target'] ) ? $link['target'] : '_self';
				$href       = isset( $link['href'] ) ? $link['href'] : '';
				$text       = isset( $link['text'] ) ? $link['text'] : 'Item';
				$links      .= "<li><a class='" . $class . "' target='" . $target . "' href='" . $href . "'><i class='" . $icon_class . "'></i>" . $text . "</a></li>";

			}
			$links .= '</ul>';

			return $links;
		}

		return '';

	}

	public function get_tag() {
		return $this->tag;
	}

	public function get_slug() {
		return $this->slug;
	}

	public function get_title() {
		return $this->title;
	}

	public function get_logo() {
		return $this->logoUrl;
	}

	public function get_type() {
		return $this->type;
	}

	public function setMoreLinks( $links ) {
		$this->moreLinks = $links;
	}

	public function get_cap() {
		return 'tve-use-' . $this->tag;
	}

	/**
	 * Checking if the default capabilities were set for the current plugin
	 */
	public function check_default_cap() {
		$admin  = get_role( 'administrator' );
		$option = $this->tag . '_def_caps_set';

		if ( $admin && ! get_option( $option ) && $admin->has_cap( $this->get_cap() ) ) {
			update_option( $option, true );

			return;
		}

		if ( ! get_option( $option ) ) {
			$editor = get_role( 'editor' );
			if ( $admin ) {
				$admin->add_cap( $this->get_cap() );
			}
			if ( $editor ) {
				$editor->add_cap( $this->get_cap() );
			}
		}
	}

	/**
	 * Check if the current user has access to the product
	 *
	 * @return bool
	 */
	public static function has_access() {
		$product = static::instance()->get_tag();

		return apply_filters( 'thrive_has_access_' . $product, current_user_can( static::cap() ) );
	}

	public static function cap() {
		return static::instance()->get_cap();
	}

	/**
	 * Make sure that each class is instantiated only once
	 *
	 * @return mixed
	 */
	public static function instance() {
		$cls = get_called_class();
		if ( ! isset( self::$instances[ $cls ] ) ) {
			self::$instances[ $cls ] = new static;
		}

		return self::$instances[ $cls ];
	}

	/**
	 * Whether or not this product has a dependency on TAr
	 *
	 * @return bool
	 */
	public function needs_architect() {
		return $this->needs_architect;
	}

	/**
	 * Getter for invalid architect version
	 *
	 * @return bool
	 */
	public function get_incompatible_architect_version() {
		return $this->incompatible_architect_version;
	}

	/**
	 * Returns the product admin URL
	 *
	 * @return string
	 */
	public function get_admin_url() {
		return ! empty( $this->button['url'] ) ? $this->button['url'] : '';
	}

	/**
	 * Define reset functionality for our products
	 *
	 * @return bool
	 */
	public static function reset_plugin() {
		return true;
	}

	/**
	 * Returns true if product is ready from TPM point of view
	 * Used in ThriveApprentice for certificate functionality
	 *
	 * @return boolean
	 */
	public static function is_ready() {
		if ( ! TD_TTW_Connection::get_instance()->is_connected() || ! class_exists( 'TPM_Product_List', false ) ) {
			return false;
		}

		$tpm_product = TPM_Product_List::get_instance()->get_product_instance( static::instance()->get_tag() );

		return $tpm_product->get_status() === 'ready';
	}
}
