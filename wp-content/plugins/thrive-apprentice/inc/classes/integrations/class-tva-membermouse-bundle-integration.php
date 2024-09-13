<?php
/**
 * Created by PhpStorm.
 * User: dan bilauca
 * Date: 16-Apr-19
 * Time: 10:59 AM
 */

/**
 * Class TVA_Membermouse_Bundle_Integration
 * - implements TVA_Integration methods
 */
class TVA_Membermouse_Bundle_Integration extends TVA_Membermouse_Abstract_Integration {

	protected $_course_membership_meta_name = 'tva_bundle_ids';

	protected $_membership_key = 'membermouse';

	protected function init_items() {

		$items = array();

		if ( class_exists( 'MM_Bundle' ) ) {

			$bundles = MM_Bundle::getBundlesList();

			if ( ! empty( $bundles ) && is_array( $bundles ) ) {

				foreach ( $bundles as $id => $name ) {
					try {
						$items[] = new TVA_Integration_Item( $id, $name );
					} catch ( Exception $e ) {

					}
				}
			}
		}

		$this->set_items( $items );
	}

	protected function _get_item_from_membership( $key, $value ) {

		$bundle = new MM_Bundle( $value );

		return new TVA_Integration_Item( $bundle->getId(), $bundle->getName() );
	}

	public function is_rule_applied( $rule ) {

		$applied = false;

		$user_id      = get_current_user_id();
		$mm_user      = new MM_User( $user_id );
		$user_bundles = $mm_user->getAppliedBundles();

		if ( true === is_array( $user_bundles ) ) {

			/** @var MM_AppliedBundle $bundle */
			foreach ( $user_bundles as $bundle ) {

				if ( $applied ) {
					break;
				}

				$bundle_id = (int) $bundle->getBundleId();

				foreach ( $rule['items'] as $item ) {

					$item_id = (int) $item['id'];

					if ( $bundle_id === $item_id ) {

						$applied = true;

						break;
					}
				}
			}
		}

		return $applied;
	}

	public function get_customer_access_items( $customer ) {
		return array();
	}

}
