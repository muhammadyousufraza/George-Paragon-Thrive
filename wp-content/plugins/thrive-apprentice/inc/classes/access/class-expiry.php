<?php

namespace TVA\Access;

use TVA\Access\Expiry\After_Purchase;
use TVA\Access\Expiry\Specific_Time;
use TVA\Product;

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Expiry {

	public static function init() {
		add_action( Specific_Time::EVENT, [ __CLASS__, 'cron_expiry_specific_time_callback' ], 10, 1 );
		add_action( Specific_Time::EVENT_REMINDER, [ __CLASS__, 'cron_expiry_specific_time_reminder_callback' ], 10, 1 );

		add_action( After_Purchase::EVENT, [ __CLASS__, 'cron_expiry_after_purchase_callback' ], 10, 2 );
		add_action( After_Purchase::EVENT_REMINDER, [ __CLASS__, 'cron_expiry_after_purchase_reminder_callback' ], 10, 2 );
	}

	public static function cron_expiry_specific_time_callback( $product_id ) {
		$product = new Product( (int) $product_id );

		if ( empty( $product->get_id() ) ) {
			//This means that the product is no longer available
			return;
		}

		Main::bulk_remove_access( $product, \TVA_Const::ACCESS_HISTORY_REASON_EXPIRE );
	}

	public static function cron_expiry_specific_time_reminder_callback( $product_id ) {
		$product = new Product( (int) $product_id );

		if ( empty( $product->get_id() ) ) {
			//This means that the product is no longer available
			return;
		}

		$user_ids = $product->get_users_with_access();

		foreach ( $user_ids as $user_id ) {
			$user = get_user_by( 'ID', (int) $user_id );
			if ( ! $user instanceof \WP_User ) {
				//This means that the user is no longer inside tha database
				continue;
			}

			static::send_single_user_reminder( $product, $user );
		}
	}

	public static function cron_expiry_after_purchase_callback( $product_id, $user_id ) {
		$product = new Product( (int) $product_id );

		if ( empty( $product->get_id() ) ) {
			//This means that the product is no longer available
			return;
		}

		$user = get_user_by( 'ID', (int) $user_id );
		if ( ! $user instanceof \WP_User ) {
			//This means that the user is no longer inside tha database
			return;
		}

		Main::remove_order_access( $product, $user_id, \TVA_Const::ACCESS_HISTORY_REASON_EXPIRE );
	}

	public static function cron_expiry_after_purchase_reminder_callback( $product_id, $user_id ) {
		$product = new Product( (int) $product_id );

		if ( empty( $product->get_id() ) ) {
			//This means that the product is no longer available
			return;
		}

		$user = get_user_by( 'ID', (int) $user_id );
		if ( ! $user instanceof \WP_User ) {
			//This means that the user is no longer inside tha database
			return;
		}

		static::send_single_user_reminder( $product, $user );
	}

	/**
	 * Sends a reminder to a single user
	 *
	 * @param Product  $product
	 * @param \WP_User $user
	 *
	 * @return void
	 */
	private static function send_single_user_reminder( $product, $user ) {
		$email_template = tva_email_templates()->get_template_details_by_slug( \TVA_Email_Templates::PRODUCT_ACCESS_EXPIRE );

		$email_template = array_merge( $email_template, [
			'product' => $product,
			'user'    => $user,
		] );

		do_action( 'tva_prepare_product_expiry_email_template', $email_template );

		$to      = $user->user_email;
		$subject = $email_template['subject'];
		$body    = do_shortcode( nl2br( $email_template['body'] ) );
		$headers = [ 'Content-Type: text/html' ];

		wp_mail( $to, $subject, $body, $headers );
	}
}
