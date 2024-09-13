<?php

/**
 * Structure for all the available animations
 */
class TVE_Leads_Animation_Abstract {
	const ANIM_INSTANT = 'instant';
	const ANIM_ZOOM_IN = 'zoom_in';
	const ANIM_ZOOM_OUT = 'zoom_out';
	const ANIM_ROTATIONAL = 'rotational';
	const ANIM_SLIDE_IN_TOP = 'slide_top';
	const ANIM_SLIDE_IN_BOT = 'slide_bot';
	const ANIM_SLIDE_IN_LEFT = 'slide_left';
	const ANIM_SLIDE_IN_RIGHT = 'slide_right';
	const ANIM_3D_SLIT = '3d_slit';
	const ANIM_3D_FLIP_HORIZONTAL = '3d_flip_horizontal';
	const ANIM_3D_FLIP_VERTICAL = '3d_flip_vertical';
	const ANIM_3D_SIGN = '3d_sign';
	const ANIM_3D_ROTATE_BOTTOM = '3d_rotate_bottom';
	const ANIM_3D_ROTATE_LEFT = '3d_rotate_left';
	const ANIM_BLUR = 'blur';
	const ANIM_MAKE_WAY = 'make_way';
	const ANIM_SLIP_FORM_TOP = 'slip_from_top';
	const ANIM_BOUNCE_IN = 'bounce_in';
	const ANIM_BOUNCE_IN_DOWN = 'bounce_in_down';
	const ANIM_BOUNCE_IN_LEFT = 'bounce_in_left';
	const ANIM_BOUNCE_IN_RIGHT = 'bounce_in_right';
	const ANIM_BOUNCE_IN_UP = 'bounce_in_up';

	public static $available = array(
		self::ANIM_INSTANT,
		self::ANIM_ZOOM_IN,
		self::ANIM_ZOOM_OUT,
		self::ANIM_ROTATIONAL,
		self::ANIM_SLIDE_IN_TOP,
		self::ANIM_SLIDE_IN_BOT,
		self::ANIM_SLIDE_IN_LEFT,
		self::ANIM_SLIDE_IN_RIGHT,
		self::ANIM_3D_SLIT,
		self::ANIM_3D_FLIP_HORIZONTAL,
		self::ANIM_3D_FLIP_VERTICAL,
		self::ANIM_3D_SIGN,
		self::ANIM_3D_ROTATE_BOTTOM,
		self::ANIM_3D_ROTATE_LEFT,
		self::ANIM_BLUR,
		self::ANIM_MAKE_WAY,
		self::ANIM_SLIP_FORM_TOP,
		self::ANIM_BOUNCE_IN,
		self::ANIM_BOUNCE_IN_DOWN,
		self::ANIM_BOUNCE_IN_LEFT,
		self::ANIM_BOUNCE_IN_RIGHT,
		self::ANIM_BOUNCE_IN_UP,
	);

	/**
	 * @var string title to be displayed
	 */
	protected $title = '';

	/**
	 * @var string internal animation key
	 */
	protected $key = '';

	/**
	 * base dir path for the plugin
	 *
	 * @var string
	 */
	protected $base_dir = '';

	/**
	 * @param $type
	 * @param $config array
	 *
	 * @return TVE_Leads_Animation_Abstract
	 */
	public static function factory( $type ) {
		$parts = explode( '_', $type );

		$class = 'TVE_Leads_Animation';
		foreach ( $parts as $part ) {
			$class .= '_' . ucfirst( $part );
		}

		if ( ! class_exists( $class ) ) {
			return null;
		}

		return new $class( $type );
	}

	/**
	 * merge the received config with the defaults
	 *
	 */
	public function __construct( $key ) {
		$this->key      = $key;
		$this->base_dir = plugin_dir_path( dirname( __FILE__ ) );
	}

	/**
	 * get the title
	 */
	public function get_title() {
		return $this->title;
	}

	/**
	 * prepare data to be used in JS
	 *
	 * @return array
	 */
	public function to_array() {
		return array(
			'title'  => $this->get_title(),
			'key'    => $this->key,
			'config' => $this->config,
		);
	}

	/**
	 * output javascript required for the animation, if the case applies
	 *
	 * renders directly JS code, without returning it
	 *
	 * @param $data - this should usually be the variation
	 */
	public function output_js( $data ) {
		if ( is_file( $this->base_dir . 'js/animations/' . $this->key . '.js.php' ) ) {
			include $this->base_dir . 'js/animations/' . $this->key . '.js.php';
		}
	}

	/**
	 * parse a CSS selector, making sure it's compliant
	 *
	 * @param $raw
	 */
	protected function parse_selector( $raw, $prefix = '.' ) {
		$selector = '';
		$raw      = str_replace( array( '#', '.' ), '', $raw );

		$parts = explode( ',', $raw );
		foreach ( $parts as $part ) {
			$selector .= ( $selector ? ',' : '' ) . $prefix . $part;
		}

		return trim( $selector, ', ' );
	}

	/**
	 * get the human-friendly animation name (and also include the configuration settings)
	 *
	 * @return string
	 */
	public function get_display_name() {
		return $this->get_title();
	}

    public function get_translatable_title() {
        return __( 'Default', 'thrive-leads' );
    }
}

/**
 * Instant Animation - No Animation
 *
 * Class TVE_Leads_Animation_Instant
 */
class TVE_Leads_Animation_Instant extends TVE_Leads_Animation_Abstract {
	protected $title = 'Instant';

	public function get_translatable_title() {
        return __( 'Instant', 'thrive-leads' );
    }
}

/**
 * Make the form zoom in at display
 *
 * Class TVE_Leads_Animation_Zoom_In
 */
class TVE_Leads_Animation_Zoom_In extends TVE_Leads_Animation_Abstract {
	protected $title = 'Zoom In';

    public function get_translatable_title() {
        return __( 'Zoom In', 'thrive-leads' );
    }
}

/**
 * Make the form zoom out at display
 *
 * Class TVE_Leads_Animation_Zoom_Out
 */
class TVE_Leads_Animation_Zoom_Out extends TVE_Leads_Animation_Abstract {
	protected $title = 'Zoom Out';

    public function get_translatable_title() {
        return __( 'Zoom Out', 'thrive-leads' );
    }
}

/**
 * Rotate the form at display
 *
 * Class TVE_Leads_Animation_Rotational
 */
class TVE_Leads_Animation_Rotational extends TVE_Leads_Animation_Abstract {
	protected $title = 'Rotational';

    public function get_translatable_title() {
        return __( 'Rotational', 'thrive-leads' );
    }
}

/**
 * The form slides in from the top
 *
 * Class TVE_Leads_Animation_Slide_Top
 */
class TVE_Leads_Animation_Slide_Top extends TVE_Leads_Animation_Abstract {
	protected $title = 'Slide in from Top';

    public function get_translatable_title() {
        return __( 'Slide in from Top', 'thrive-leads' );
    }
}

/**
 * The form slides in from the Bottom
 *
 * Class TVE_Leads_Animation_Slide_Bot
 */
class TVE_Leads_Animation_Slide_Bot extends TVE_Leads_Animation_Abstract {
	protected $title = 'Slide in from Bottom';

    public function get_translatable_title() {
        return __( 'Slide in from Bottom', 'thrive-leads' );
    }
}

/**
 * Form slides in from lateral
 *
 * Class TVE_Leads_Animation_Slide_Left
 */
class TVE_Leads_Animation_Slide_Left extends TVE_Leads_Animation_Abstract {
	protected $title = 'Slide in from Left';

    public function get_translatable_title() {
        return __( 'Slide in from Left', 'thrive-leads' );
    }
}

/**
 * Form slides in from right
 *
 * Class TVE_Leads_Animation_Slide_Right
 */
class TVE_Leads_Animation_Slide_Right extends TVE_Leads_Animation_Abstract {
	protected $title = 'Slide in from Right';

    public function get_translatable_title() {
        return __( 'Slide in from Right', 'thrive-leads' );
    }
}

/**
 * Form 3D Slit
 *
 * Class TVE_Leads_Animation_3d_Slit
 */
class TVE_Leads_Animation_3d_Slit extends TVE_Leads_Animation_Abstract {
	protected $title = '3D Slit';

    public function get_translatable_title() {
        return __( '3D Slit', 'thrive-leads' );
    }
}

/**
 * Form 3D Flip Horizontal
 *
 * Class TVE_Leads_Animation_3d_Flip_Horizontal
 */
class TVE_Leads_Animation_3d_Flip_Horizontal extends TVE_Leads_Animation_Abstract {
	protected $title = '3D Flip (Horizontal)';

    public function get_translatable_title() {
        return __( '3D Flip (Horizontal)', 'thrive-leads' );
    }
}

/**
 * Form 3D Flip Vertical
 *
 * Class TVE_Leads_Animation_3d_Flip_Vertical
 */
class TVE_Leads_Animation_3d_Flip_Vertical extends TVE_Leads_Animation_Abstract {
	protected $title = '3D Flip (Vertical)';

    public function get_translatable_title() {
        return __( '3D Flip (Vertical)', 'thrive-leads' );
    }
}

/**
 * Form 3D Flip Vertical
 *
 * Class TVE_Leads_Animation_3d_Sign
 */
class TVE_Leads_Animation_3d_Sign extends TVE_Leads_Animation_Abstract {
	protected $title = '3D Sign';

    public function get_translatable_title() {
        return __( '3D Sign', 'thrive-leads' );
    }
}

/**
 * Form 3D Rotate Bottom
 *
 * Class TVE_Leads_Animation_3d_Rotate_Bottom
 */
class TVE_Leads_Animation_3d_Rotate_Bottom extends TVE_Leads_Animation_Abstract {
	protected $title = '3D Rotate Bottom';

    public function get_translatable_title() {
        return __( '3D Rotate Bottom', 'thrive-leads' );
    }
}

/**
 * Form 3D Rotate Bottom
 *
 * Class TVE_Leads_Animation_3d_Rotate_Left
 */
class TVE_Leads_Animation_3d_Rotate_Left extends TVE_Leads_Animation_Abstract {
	protected $title = '3D Rotate Left';

    public function get_translatable_title() {
        return __( '3D Rotate Left', 'thrive-leads' );
    }
}

/**
 * Form Blur
 *
 * Class TVE_Leads_Animation_Blur
 */
class TVE_Leads_Animation_Blur extends TVE_Leads_Animation_Abstract {
	protected $title = 'Blur';

    public function get_translatable_title() {
        return __( 'Blur', 'thrive-leads' );
    }
}

/**
 * Form Make Way
 *
 * Class TVE_Leads_Animation_Make_Way
 */
class TVE_Leads_Animation_Make_Way extends TVE_Leads_Animation_Abstract {
	protected $title = 'Make Way';

    public function get_translatable_title() {
        return __( 'Make Way', 'thrive-leads' );
    }
}

/**
 * Form Slip from Top
 *
 * Class TVE_Leads_Animation_Slip_From_Top
 */
class TVE_Leads_Animation_Slip_From_Top extends TVE_Leads_Animation_Abstract {
	protected $title = 'Slip from Top';

    public function get_translatable_title() {
        return __( 'Slip from Top', 'thrive-leads' );
    }
}

/**
 * Form Bounce In
 *
 * Class TVE_Leads_Animation_Bounce_In
 */
class TVE_Leads_Animation_Bounce_In extends TVE_Leads_Animation_Abstract {
	protected $title = 'Bounce In';

    public function get_translatable_title() {
        return __( 'Bounce In', 'thrive-leads' );
    }
}

/**
 * Form Bounce In Down
 *
 * Class TVE_Leads_Animation_Bounce_In_Down
 */
class TVE_Leads_Animation_Bounce_In_Down extends TVE_Leads_Animation_Abstract {
	protected $title = 'Bounce In Down';

    public function get_translatable_title() {
        return __( 'Bounce In Down', 'thrive-leads' );
    }
}

/**
 * Form Bounce In Left
 *
 * Class TVE_Leads_Animation_Bounce_In_Left
 */
class TVE_Leads_Animation_Bounce_In_Left extends TVE_Leads_Animation_Abstract {
	protected $title = 'Bounce In Left';

    public function get_translatable_title() {
        return __( 'Bounce In Left', 'thrive-leads' );
    }
}

/**
 * Form Bounce In Right
 *
 * Class TVE_Leads_Animation_Bounce_In_Right
 */
class TVE_Leads_Animation_Bounce_In_Right extends TVE_Leads_Animation_Abstract {
	protected $title = 'Bounce In Right';

    public function get_translatable_title() {
        return __( 'Bounce In Right', 'thrive-leads' );
    }
}

/**
 * Form Bounce In Up
 *
 * Class TVE_Leads_Animation_Bounce_In_Up
 */
class TVE_Leads_Animation_Bounce_In_Up extends TVE_Leads_Animation_Abstract {
	protected $title = 'Bounce In Up';

    public function get_translatable_title() {
        return __( 'Bounce In Up', 'thrive-leads' );
    }
}
