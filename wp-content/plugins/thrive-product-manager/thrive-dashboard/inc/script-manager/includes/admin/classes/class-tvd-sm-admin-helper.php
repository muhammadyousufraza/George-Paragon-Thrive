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
 * Class TVD_SM_Admin_Helper
 */
class TVD_SM_Admin_Helper {

	/**
	 * The single instance of the class.
	 *
	 * @var TVD_SM_Admin_Helper singleton instance.
	 */
	protected static $_instance = null;

	/**
	 * Main TVD_SM_Admin_Helper Instance.
	 * Ensures only one instance of TVD_SM_Admin_Helper is loaded or can be loaded.
	 *
	 * @return TVD_SM_Admin_Helper
	 */
	public static function instance() {
		if ( static::$_instance === null ) {
			static::$_instance = new self();
		}

		return static::$_instance;
	}

	/**
	 * Get used scripts
	 *
	 * @return array
	 */
	public function tvd_sm_get_scripts() {
		$scripts = tah()->tvd_sm_get_option( 'global_lp_scripts', array() );

		/* backward compatibility - the old status field was not an array; scripts added before the ttb integration had a boolean value */
		foreach ( $scripts as $key => $script ) {
			$status = $script['status'];
			if ( ! is_array( $status ) ) {
				$scripts[ $key ]['status'] = array(
					TVD_SM_Frontend::LP_LOCATION    => $status,
					TVD_SM_Frontend::THEME_LOCATION => false,
				);
			}
			/* special weird character most likey copy-pasted from a word document or smth - "long dash" */
			/* SUPP-6590 - when this happens the script manager is useless - it doesn't render. Also, the LPs are blank .. */
			$scripts[ $key ]['code'] = str_replace( '—', '--', $script['code'] );
		}

		return array_values( $scripts );
	}

	/**
	 * Get option value or add it, if this doesn't exists
	 *
	 * @param string $option_name    Name of option to add. Expected to not be SQL-escaped.
	 * @param array  $default_values options default values.
	 *
	 * @return array|mixed
	 */
	public function tvd_sm_get_option( $option_name, $default_values = array() ) {
		$option = get_option( $option_name );

		if ( empty( $option ) ) {

			add_option( $option_name, $default_values );

			$option = $default_values;
		}

		return $option;
	}


	/**
	 * returns value inside the given array, if no value is found then it returns -1
	 *
	 * @param $id
	 * @param $scripts
	 *
	 * @return int|string
	 */
	public function tvd_sm_retrieve_key_for_id( $id, $scripts ) {
		foreach ( $scripts as $key => $val ) {
			if ( array_search( $id, $val ) === 'id' ) {
				return $key;
			}
		}

		return $this->tvd_sm_get_last_id_plus_one( $scripts );
	}

	/**
	 * Return next if for the scripts
	 *
	 * @param $scripts
	 *
	 * @return int
	 */
	public function tvd_sm_get_last_id_plus_one( $scripts ) {
		if ( empty( $scripts ) ) {
			$id = 1;
		} else {
			$script = array_slice( $scripts, - 1 );
			$id     = $script[0]['id'] + 1;
		}

		return $id;
	}

	/**
	 * comparator for usort
	 *
	 * @param $a
	 * @param $b
	 *
	 * @return int
	 */
	public function sort_by_order( $a, $b ) {
		if ( $a['order'] === $b['order'] ) {
			return 0;
		}

		return ( $a['order'] > $b['order'] ) ? 1 : - 1;
	}

	/**
	 * Wrapper over the update option
	 *
	 * @param string $option_name Option name.
	 * @param mixed  $value       Option value.
	 *
	 * @return array|mixed
	 */
	public function tvd_sm_update_option( $option_name, $value ) {
		/* allow only specific options to be saved */
		if ( empty( $option_name ) || ! in_array( $option_name, [ 'global_lp_scripts' ] ) ) {
			return false;
		}

		$old_value = $this->tvd_sm_get_option( $option_name );

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
	 * The recursive version of the array_diff_assoc taken from php.net
	 *
	 * @param $array1
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
}

/**
 * @return TVD_SM_Admin_Helper
 */
function tah() {
	return TVD_SM_Admin_Helper::instance();
}

tah();
