<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-automator
 */

namespace Thrive\Automator\Items;

use Thrive\Automator\Traits\Automation_Item;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}


abstract class App {

	use Automation_Item;

	/**
	 * Get the application identifier. Must be unique across all the other application
	 *
	 * @return string
	 */
	abstract public static function get_id();

	/**
	 * Get the application name/label
	 *
	 * @return string
	 */
	abstract public static function get_name();

	/**
	 * Get the application description
	 *
	 * @return string
	 */
	abstract public static function get_description();

	/**
	 * Get the application logo
	 *
	 * @return string
	 */
	abstract public static function get_logo();

	/**
	 * An url where the user can learn more about the application and how to get access its features
	 *
	 * @return string
	 */
	public static function get_acccess_url() {
		return '';
	}

	/**
	 * Can be used to condition the display of the app
	 *
	 * @return bool
	 */
	public static function has_access(){
		return true;
	}

	/**
	 * Required properties for application implementation
	 *
	 * @return string[]
	 */
	final public static function required_properties(): array {
		return [
			'get_id'          => 'string',
			'get_name'        => 'string',
			'get_description' => 'string',
			'get_logo'        => 'string',
			'has_access'      => 'boolean',
		];
	}

	/**
	 * Get action information mostly to be localized in the admin dashboard
	 */
	final public static function get_info(): array {
		return [
			'id'          => static::get_id(),
			'name'        => static::get_name(),
			'logo'        => static::get_logo(),
			'description' => static::get_description(),
			'has_access'  => static::has_access(),
			'access_url'  => static::get_acccess_url(),
		];
	}

	public static function hidden() {
		return false;
	}
}
