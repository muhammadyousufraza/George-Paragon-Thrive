<?php
/**
 * FileName  class-thrive-comments-model.php.
 *
 * @project  : thrive-comments
 * @developer: Dragos Petcu
 * @company  : BitStone
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden.
}

/**
 * Class TCM_Admin_Helper
 */
class Thrive_Admin_Helper {

	/**
	 * The single instance of the class.
	 *
	 * @var Thrive_Admin_Helper singleton instance.
	 */
	protected static $_instance = null;

	/**
	 * Main Thrive Admin Instance.
	 * Ensures only one instance of Thrive Comments Helper is loaded or can be loaded.
	 *
	 * @return Thrive_Admin_Helper
	 */
	public static function instance() {
		if ( empty( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Get option value or add it, if this doesn't exist
	 *
	 * @param string $option_name Name of option to add. Expected to not be SQL-escaped.
	 *
	 * @return array|mixed
	 */
	public function tcm_get_option( $option_name ) {
		$value = maybe_unserialize( get_option( $option_name ) );

		if ( empty( $value ) && $value !== '0' ) {
			$defaults = Thrive_Comments_Constants::get_defaults();

			$value = isset( $defaults[ $option_name ] ) ? $defaults[ $option_name ] : [];

			add_option( $option_name, $value );
		}

		return $value;
	}

	/**
	 * Wrapper over the update option
	 *
	 * @param string $option_name Option name.
	 * @param mixed  $value       Option value.
	 *
	 * @return array|mixed
	 */
	public function tcm_update_option( $option_name, $value ) {

		if ( empty( $option_name ) ) {
			return false;
		}

		/**
		 * Starting with wp 5.5 the two options name were changed for some reason so we need to support that
		 * and also to be backwards compatible
		 */
		global $wp_version;
		if ( version_compare( $wp_version, '5.5-beta', '>=' ) ) {
			$option_name = ( $option_name === 'comment_whitelist' ) ? 'comment_previously_approved' : $option_name;
			$option_name = ( $option_name === 'blacklist_keys' ) ? 'disallowed_keys' : $option_name;
		}

		$old_value = $this->tcm_get_option( $option_name );

		/* Check to see if the old value is the same as the new one */
		if ( is_array( $old_value ) && is_array( $value ) ) {
			$diff = $this->array_diff_assoc_recursive( $old_value, $value ) + $this->array_diff_assoc_recursive( $value, $old_value );
		} elseif ( is_object( $old_value ) && is_object( $value ) ) {
			$diff = array_diff_assoc( get_object_vars( $old_value ), get_object_vars( $value ) ) + array_diff_assoc( get_object_vars( $value ), get_object_vars( $old_value ) );
		} else {
			$diff = ! ( $old_value === $value );
		}

		/* If the new value is the same with the old one, return true and don't update */
		if ( empty( $diff ) ) {
			return true;
		}

		return update_option( $option_name, $value );

	}

	/**
	 * Wrapper over the delete option
	 *
	 * @return array|mixed
	 */
	function tcm_delete_option() {
		$args        = func_get_args();
		$args_number = func_num_args();

		if ( 0 === $args_number ) {
			return false;
		} elseif ( 1 === $args_number ) {
			$name = $args[0];
		} else {
			$name   = $args[0];
			$id     = $args[1];
			$option = $this->tcm_get_option( $name );

			if ( 'tcm_keywords' === $name ) {
				$identifier = 'name';
			} else {
				$identifier = 'id';
			}
			$len = count( $option );
			for ( $i = 0; $i < $len; $i ++ ) {
				if ( $option[ $i ][ $identifier ] === $id ) {
					// Delete the option.
					unset( $option[ $i ] );
				}
			}
			$option = array_values( $option );

			return update_option( $name, $option );
		}
	}


	/**
	 * The recursive version of the array_diff_assoc taken from php.net
	 *
	 * @param $array1
	 * @param $array2
	 * @param $array2
	 *
	 * @return array
	 */
	public function array_diff_assoc_recursive( $array1, $array2 ) {
		$difference = array();
		foreach ( $array1 as $key => $value ) {
			if ( is_array( $value ) ) {
				if ( ! isset( $array2[ $key ] ) || ! is_array( $array2[ $key ] ) ) {
					$difference[ $key ] = $value;
				} else {
					$new_diff = $this->array_diff_assoc_recursive( $value, $array2[ $key ] );
					if ( ! empty( $new_diff ) ) {
						$difference[ $key ] = $new_diff;
					}
				}
			} elseif ( ! array_key_exists( $key, $array2 ) || $array2[ $key ] !== $value ) {
				$difference[ $key ] = $value;
			}
		}

		return $difference;
	}

	/**
	 * Get all roles
	 *
	 * @return array
	 */
	public function get_all_roles() {

		$tcm_roles = wp_cache_get( 'tcm_roles' );
		if ( $tcm_roles ) {
			return $tcm_roles;
		}

		$wp_roles          = wp_roles();
		$all_roles         = $wp_roles->get_names();
		$settings_defaults = Thrive_Comments_Constants::get_defaults();

		$roles = array();
		foreach ( $all_roles as $role ) {
			$role     = strtolower( $role );
			$role     = str_replace( '|user role', '', $role );
			$var_name = 'tcm_mod_' . str_replace( ' ', '_', $role );
			if ( array_key_exists( $var_name, $settings_defaults ) ) {

				$defaults       = $settings_defaults;
				$settings_value = get_option( $var_name );

				if ( false === $settings_value ) {
					add_option( $var_name, $defaults[ $var_name ] );
					$settings_value = $defaults[ $var_name ];
				}
				$roles[ $var_name ] = $settings_value;

			} else {
				$option = get_option( $var_name );
				if ( ! $option ) {
					add_option( $var_name, 0 );
					$roles[ $var_name ] = 0;
				} else {
					$roles[ $var_name ] = $option;
				}
			}
		}

		wp_cache_add( 'tcm_roles', $roles );

		return $roles;
	}


	/**
	 * Verify if the user can see the moderation dashboard.
	 * Check for the current user if the user is not sent.
	 *
	 * @param WP_User|null $user User.
	 *
	 * @return bool
	 */
	public function can_see_moderation( WP_User $user = null ) {
		$roles = $this->get_all_roles();

		$role = ( null === $user ) ? wp_get_current_user()->roles : $user->roles;

		return ( ! empty( $role ) && isset( $roles[ 'tcm_mod_' . $role[0] ] ) && '1' === $roles[ 'tcm_mod_' . $role[0] ] );

	}

	/**
	 * Check if the thrivebox exists, if not, update the setting.
	 *
	 * @param array $setting tcm_conversion setting.
	 *
	 * @return array $setting
	 */
	public function check_thriveboxes( $setting ) {
		$update       = false;
		$thrive_boxes = tcmc()->get_thrive_boxes();
		if ( ! count( $thrive_boxes ) ) {
			return $setting;
		}
		foreach ( $thrive_boxes as $thrive_box ) {
			$boxes_ids[] = $thrive_box->ID;
		}
		if ( ! in_array( $setting['tcm_thrivebox']['first_time']['thrivebox_id'], $boxes_ids ) ) {
			$setting['tcm_thrivebox']['first_time']['thrivebox_id'] = 0;
			$update                                                 = true;
		}
		if ( ! in_array( $setting['tcm_thrivebox']['second_time']['thrivebox_id'], $boxes_ids ) ) {
			$setting['tcm_thrivebox']['second_time']['thrivebox_id'] = 0;
			$update                                                  = true;
		}
		if ( $update ) {
			update_option( 'tcm_conversion', $setting );
		}

		return $setting;
	}

	/**
	 * Iterate over svg collection and return all symbols id(useful when you have a svg with images)
	 *
	 * @param $svg_url
	 *
	 * @return array
	 */
	function get_svg_symbols_ids() {
		$images_ids = array(
			'icon-Approved_comments_01_default',
			'icon-Approved_comments_01_progress_a',
			'icon-Approved_comments_01_progress_b',
			'icon-Approved_comments_01_progress_c',
			'icon-Approved_comments_02_default',
			'icon-Approved_comments_02_progress_a',
			'icon-Approved_comments_02_progress_b',
			'icon-Approved_comments_02_progress_c',
			'icon-Approved_comments_03_default',
			'icon-Approved_comments_03_progress_a',
			'icon-Approved_comments_03_progress_b',
			'icon-Approved_comments_03_progress_c',
			'icon-Approved_replies_01_default',
			'icon-Approved_replies_01_progress_a',
			'icon-Approved_replies_01_progress_b',
			'icon-Approved_replies_01_progress_c',
			'icon-Approved_replies_02_default',
			'icon-Approved_replies_02_progress_a',
			'icon-Approved_replies_02_progress_b',
			'icon-Approved_replies_02_progress_c',
			'icon-Approved_replies_03_default',
			'icon-Approved_replies_03_progress_a',
			'icon-Approved_replies_03_progress_b',
			'icon-Approved_replies_03_progress_c',
			'icon-Approved_replies_04_default',
			'icon-Approved_replies_04_progress_a',
			'icon-Approved_replies_04_progress_b',
			'icon-Approved_replies_04_progress_c',
			'icon-Approved_replies_05_default',
			'icon-Approved_replies_05_progress_a',
			'icon-Approved_replies_05_progress_b',
			'icon-Approved_replies_05_progress_c',
			'icon-Approved_replies_06_default',
			'icon-Approved_replies_06_progress_a',
			'icon-Approved_replies_06_progress_b',
			'icon-Approved_replies_06_progress_c',
			'icon-featured_comments_01_default',
			'icon-featured_comments_01_progress_a',
			'icon-featured_comments_01_progress_b',
			'icon-featured_comments_01_progress_c',
			'icon-featured_comments_02_default',
			'icon-featured_comments_02_progress_a',
			'icon-featured_comments_02_progress_b',
			'icon-featured_comments_02_progress_c',
			'icon-featured_comments_03_default',
			'icon-featured_comments_03_progress_a',
			'icon-featured_comments_03_progress_b',
			'icon-featured_comments_03_progress_c',
			'icon-featured_comments_04_default',
			'icon-featured_comments_04_progress_a',
			'icon-featured_comments_04_progress_b',
			'icon-featured_comments_04_progress_c',
			'icon-general_badges_01_default',
			'icon-general_badges_01_progress_a',
			'icon-general_badges_01_progress_b',
			'icon-general_badges_01_progress_c',
			'icon-general_badges_02_default',
			'icon-general_badges_02_progress_a',
			'icon-general_badges_02_progress_b',
			'icon-general_badges_02_progress_c',
			'icon-general_badges_03_default',
			'icon-general_badges_03_progress_a',
			'icon-general_badges_03_progress_b',
			'icon-general_badges_03_progress_c',
			'icon-general_badges_04_default',
			'icon-general_badges_04_progress_a',
			'icon-general_badges_04_progress_b',
			'icon-general_badges_04_progress_c',
			'icon-general_badges_05_default',
			'icon-general_badges_05_progress_a',
			'icon-general_badges_05_progress_b',
			'icon-general_badges_05_progress_c',
			'icon-general_badges_06_default',
			'icon-general_badges_06_progress_a',
			'icon-general_badges_06_progress_b',
			'icon-general_badges_06_progress_c',
			'icon-general_badges_07_default',
			'icon-general_badges_07_progress_a',
			'icon-general_badges_07_progress_b',
			'icon-general_badges_07_progress_c',
			'icon-general_badges_08_default',
			'icon-general_badges_08_progress_a',
			'icon-general_badges_08_progress_b',
			'icon-general_badges_08_progress_c',
			'icon-general_badges_09_default',
			'icon-general_badges_09_progress_a',
			'icon-general_badges_09_progress_b',
			'icon-general_badges_09_progress_c',
			'icon-general_badges_10_default',
			'icon-general_badges_10_progress_a',
			'icon-general_badges_10_progress_b',
			'icon-general_badges_10_progress_c',
			'icon-upvote_badges_01_default',
			'icon-upvote_badges_01_progress_a',
			'icon-upvote_badges_01_progress_b',
			'icon-upvote_badges_01_progress_c',
			'icon-upvote_badges_02_default',
			'icon-upvote_badges_02_progress_a',
			'icon-upvote_badges_02_progress_b',
			'icon-upvote_badges_02_progress_c',
			'icon-upvote_badges_03_default',
			'icon-upvote_badges_03_progress_a',
			'icon-upvote_badges_03_progress_b',
			'icon-upvote_badges_03_progress_c',
			'icon-upvote_badges_04_default',
			'icon-upvote_badges_04_progress_a',
			'icon-upvote_badges_04_progress_b',
			'icon-upvote_badges_04_progress_c',
		);

		return $images_ids;
	}

	/**
	 * @param        $icon   : the icon name (without the 'tcm-', that gets added here)
	 * @param string $class  : for adding extra classes
	 * @param bool   $return : if you want the function to return instead of printing
	 *
	 * @return : nothing if there is no $return param, a string if there is a $return param.
	 */
	function tcm_icon( $icon, $class = '', $return = false ) {
		$html = '<svg class="' . $class . '"><use xlink:href="#tcm-' . $icon . '"></use></svg>';

		if ( false !== $return ) {
			return $html;
		}
		echo $html;
	}

	/* includes a file containing svg declarations */
	function include_svg_file( $file ) {
		include tcm()->plugin_path( 'assets/images/' . $file );
	}

	/**
	 * Initialize default notification labels if the options does not already exists in the db.
	 *
	 * @return bool
	 */
	public function tcm_default_notification_labels() {

		$default_labels = Thrive_Comments_Constants::$tcm_default_notification_labels;
		$saved_labels   = get_option( 'tcm_notification_labels' );
		$update         = 0;

		foreach ( $default_labels as $key => $value ) {
			if ( ! isset( $saved_labels[ $key ] ) || empty( $saved_labels[ $key ]['text'] ) ) {
				$saved_labels[ $key ] = array(
					'default' => __( $value, Thrive_Comments_Constants::T ),
					'text'    => __( $value, Thrive_Comments_Constants::T ),
				);
				$update               = 1;
			}
		}
		if ( $update ) {
			update_option( 'tcm_notification_labels', $saved_labels );
		}

		return $saved_labels;
	}
}

/**
 *  Main instance of Thrive Comments Helpers
 *
 * @return Thrive_Admin_Helper
 */
function tcah() {
	return Thrive_Admin_Helper::instance();
}

tcah();
