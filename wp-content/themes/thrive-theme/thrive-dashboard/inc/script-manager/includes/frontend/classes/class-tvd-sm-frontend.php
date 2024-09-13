<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden.
}

/**
 * Class TVD_SM_Frontend
 */
class TVD_SM_Frontend {

	const LP_HOOK_HEAD       = 'tcb_landing_head_frontend';
	const LP_HOOK_BODY_OPEN  = 'tcb_landing_body_open_frontend';
	const LP_HOOK_BODY_CLOSE = 'tcb_landing_body_close_frontend';

	const THEME_LOCATION = 'ttb';
	const LP_LOCATION    = 'lp';

	private $frontend_scripts = array(
		TVD_SM_Constants::HEAD_PLACEMENT       => array( self::THEME_LOCATION => '', self::LP_LOCATION => '' ),
		TVD_SM_Constants::BODY_OPEN_PLACEMENT  => array( self::THEME_LOCATION => '', self::LP_LOCATION => '' ),
		TVD_SM_Constants::BODY_CLOSE_PLACEMENT => array( self::THEME_LOCATION => '', self::LP_LOCATION => '' ),
	);

	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * The single instance of the class.
	 *
	 * @var TVD_SM_Frontend singleton instance.
	 */
	protected static $_instance = null;

	/**
	 * Main TVD_SM_Frontend Instance.
	 * Ensures only one instance of TVD_SM_Frontend is loaded or can be loaded.
	 *
	 * @return TVD_SM_Frontend
	 */
	public static function instance() {
		if ( static::$_instance === null ) {
			static::$_instance = new self();
		}

		return static::$_instance;
	}

	public function init() {
		/* update the script code */
		$this->update_script_code();

		/* hooks for adding scripts to specific sections of the landing pages or regular posts or pages */
		add_action( static::LP_HOOK_HEAD, array( $this, 'head_scripts' ) );
		add_action( static::LP_HOOK_BODY_OPEN, array( $this, 'body_open_scripts' ) );
		add_action( static::LP_HOOK_BODY_CLOSE, array( $this, 'body_close_scripts' ) );
	}

	public function update_script_code() {
		/* get all the scripts  */
		$scripts = tah()->tvd_sm_get_scripts();

		/* sort the array according to the 'order' field */
		usort( $scripts, array( tah(), 'sort_by_order' ) );

		/* update the section strings */
		foreach ( $scripts as $script ) {
			foreach ( $script['status'] as $location => $status ) {
				if ( isset( $script['placement'] ) && $status ) {
					$this->frontend_scripts[ $script['placement'] ][ $location ] .= $script['code'];
				}
			}
		}
	}

	public function theme_scripts( $placement ) {
		return $this->frontend_scripts[ $placement ][ static::THEME_LOCATION ];
	}

	public function head_scripts() {
		/* add all the head scripts */
		echo $this->frontend_scripts[ TVD_SM_Constants::HEAD_PLACEMENT ][ static::LP_LOCATION ]; // phpcs:ignore
	}

	public function body_open_scripts() {
		/* add all the body start scripts */
		echo $this->frontend_scripts[ TVD_SM_Constants::BODY_OPEN_PLACEMENT ][ static::LP_LOCATION ]; // phpcs:ignore
	}

	public function body_close_scripts() {
		/* add all the body end scripts */
		echo $this->frontend_scripts[ TVD_SM_Constants::BODY_CLOSE_PLACEMENT ][ static::LP_LOCATION ]; // phpcs:ignore
	}
}

/**
 * @return TVD_SM_Frontend
 */
function TVD_SM_Frontend() {
	return TVD_SM_Frontend::instance();
}

TVD_SM_Frontend();
