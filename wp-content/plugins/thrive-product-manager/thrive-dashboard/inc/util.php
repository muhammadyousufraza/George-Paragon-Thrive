<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

use TCB\inc\helpers\FormSettings;
use TCB\Integrations\Automator\Form_Identifier;
use Thrive\Automator\Items\Data_Object;
use Thrive\Automator\Items\Filter;
use Thrive\Automator\Items\Trigger;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
/**
 * Utility functions to be used in all Thrive Products
 */

function tve_dash_get_thrivethemes_shares( $network = 'facebook' ) {
	$cache_for = 300; // 5 minutes
	$url       = 'https://thrivethemes.com/';
	$tt_shares = get_option( 'thrive_tt_shares', array() );
	$fn        = 'tve_dash_fetch_share_count_' . $network;
	if ( ! function_exists( $fn ) ) {
		return 0;
	}
	if ( empty( $tt_shares ) || ! isset( $tt_shares[ $network ] ) || time() - $tt_shares[ $network ]['last_fetch'] > $cache_for ) {
		$tt_shares[ $network ] = array(
			'count'      => $fn( $url ),
			'last_fetch' => time(),
		);
		update_option( 'thrive_tt_shares', $tt_shares );
	}

	return $tt_shares[ $network ]['count'];
}

/**
 * fetch the FB total number of shares for an url
 *
 * @param string $url
 *
 * @return int
 */
function tve_dash_fetch_share_count_facebook( $url ) {
	$credentials = Thrive_Dash_List_Manager::credentials( 'facebook' );

	if ( ! empty( $credentials ) && ! empty( $credentials['app_id'] ) && ! empty( $credentials['app_secret'] ) ) {
		/* General query args for the Facebook API requests */
		$query_args  = array(
			'id'           => rawurlencode( $url ),
			'access_token' => $credentials['app_id'] . '|' . $credentials['app_secret'],
		);
		$url_post_id = url_to_postid( $url );

		/* If we have an id, the url is on this site, so we can save the last updated time */
		if ( $url_post_id ) {
			$last_updated_time = get_post_meta( url_to_postid( $url ), 'tve_facebook_count_updated', true );
			$updated_time      = current_time( 'timestamp' );

			/* We scrape the url every hour or if it was not already scraped */
			if ( empty( $last_updated_time ) || $updated_time - $last_updated_time > ( 60 * 60 ) ) {
				$update_query_args = $query_args;
				/* Reference here: https://developers.facebook.com/docs/sharing/opengraph/using-objects#update */
				$update_query_args['scraped'] = 'true';
				$fb_url_update                = add_query_arg( $update_query_args, 'https://graph.facebook.com/v12.0/' );

				/* Make the post call so that Facebook will scrape again the url */
				_tve_dash_util_helper_get_json( $fb_url_update, 'wp_remote_post' );

				/* Update in post meta the time of the last update */
				update_post_meta( $url_post_id, 'tve_facebook_count_updated', $updated_time );
			}
		}

		/* Changed from engagement to og_object{engagement} to get the accurate number of shares
		Unofficial reference: https://developers.facebook.com/community/threads/178543204270768/ */
		$query_args['fields'] = 'og_object{engagement}';
		$fb_url_get           = add_query_arg( $query_args, 'https://graph.facebook.com/v12.0/' );

		/* Get the Share count */
		$data = _tve_dash_util_helper_get_json( $fb_url_get );
	}

	return ! empty( $data['og_object'] ) && ! empty( $data['og_object']['engagement'] ) ? (int) $data['og_object']['engagement']['count'] : 0;

}

/**
 * fetch the total number of shares for an url from twitter
 *
 * Update Nov. 2015 - twitter removed their share count API
 *
 * @param string $url
 *
 * @return int
 */
function tve_dash_fetch_share_count_twitter( $url ) {
	return 0;
}

/**
 * fetch the total number of shares for an url from Pinterest
 *
 * @param string $url
 *
 * @return int
 */
function tve_dash_fetch_share_count_pinterest( $url ) {
	$response = wp_remote_get( 'http://api.pinterest.com/v1/urls/count.json?callback=_&url=' . rawurlencode( $url ), array(
		'sslverify' => false,
	) );

	$body = wp_remote_retrieve_body( $response );
	if ( empty( $body ) ) {
		return 0;
	}
	$body = preg_replace( '#_\((.+?)\)$#', '$1', $body );
	$data = json_decode( $body, true );

	return empty( $data['count'] ) ? 0 : (int) $data['count'];
}

/**
 * fetch the total number of shares for an url from LinkedIn
 *
 * @param string $url
 *
 * @return int
 */
function tve_dash_fetch_share_count_linkedin( $url ) {
	$data = _tve_dash_util_helper_get_json( 'http://www.linkedin.com/countserv/count/share?format=json&url=' . rawurlencode( $url ) );

	return empty( $data['count'] ) ? 0 : (int) $data['count'];
}

/**
 * fetch the total number of shares for an url from Google
 *
 * @param string $url
 *
 * @return int
 */
function tve_dash_fetch_share_count_google( $url ) {
	$response = wp_remote_post( 'https://clients6.google.com/rpc', array(
		'sslverify' => false,
		'headers'   => array(
			'Content-type' => 'application/json',
		),
		'body'      => json_encode( array(
			array(
				'method'     => 'pos.plusones.get',
				'id'         => 'p',
				'params'     => array(
					'nolog'   => true,
					'id'      => $url,
					'source'  => 'widget',
					'userId'  => '@viewer',
					'groupId' => '@self',
				),
				'jsonrpc'    => '2.0',
				'key'        => 'p',
				'apiVersion' => 'v1',
			),
		) ),
	) );

	if ( $response instanceof WP_Error ) {
		return 0;
	}

	$data = json_decode( wp_remote_retrieve_body( $response ), true );

	if ( empty( $data ) || ! isset( $data[0]['result']['metadata']['globalCounts'] ) ) {
		return 0;
	}

	return (int) $data[0]['result']['metadata']['globalCounts']['count'];
}


/**
 * fetch the total number of shares for an url from Xing
 *
 * @param string $url
 *
 * @return int
 */
function tve_dash_fetch_share_count_xing( $url ) {
	$response = _tve_dash_util_helper_get_json( 'https://www.xing-share.com/spi/shares/statistics?url=' . rawurlencode( $url ), 'wp_remote_post' );

	return isset( $response['share_counter'] ) ? $response['share_counter'] : 0;
}

/**
 * fetch and decode a JSON response from a URL
 *
 * @param string $url
 * @param string $fn
 *
 * @return array
 */
function _tve_dash_util_helper_get_json( $url, $fn = 'wp_remote_get' ) {
	$response = $fn( $url, array( 'sslverify' => false ) );
	if ( $response instanceof WP_Error ) {
		return array();
	}

	$body = wp_remote_retrieve_body( $response );
	if ( empty( $body ) ) {
		return array();
	}

	$data = json_decode( $body, true );

	return empty( $data ) ? array() : $data;
}

/**
 * Checks if the current request is performed by a crawler. It identifies crawlers by inspecting the user agent string
 *
 * @param bool $apply_filter Whether or not to apply the crawler detection filter ( tve_dash_is_crawler )
 *
 * @return int|false False form empty UAS. int 1|0 if a crawler has|not been detected
 */
function tve_dash_is_crawler( $apply_filter = false ) {

	/**
	 * wp_is_mobile() checks to go before bot detection. There are some cases where a false positive is recorded. Example: Pinterest
	 * The Pinterest app built-in web browser's UA string contains "Pinterest" which is flagged as a crawler
	 */
	if ( empty( $_SERVER['HTTP_USER_AGENT'] ) || wp_is_mobile() ) {
		return false;
	}

	if ( isset( $GLOBALS['thrive_dashboard_bot_detection'] ) ) {
		$is_crawler = $GLOBALS['thrive_dashboard_bot_detection'];
	}

	if ( ! isset( $is_crawler ) ) {
		$user_agent = sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] );

		$uas_list = require plugin_dir_path( __FILE__ ) . '_crawlers.php';
		$regexp   = '#(' . implode( '|', $uas_list ) . ')#i';

		$is_crawler = preg_match( $regexp, $user_agent );

		/*
		 * Apply the filter to allow overwriting the bot detection. Can be used by 3rd party plugins to force the initial ajax request
		 * This filter is incorrectly named, it is just being applied during the localization of frontend ajax data from thrive-dashboard
		 */
		if ( $apply_filter ) {
			/**
			 * Filter tve_dash_is_crawler
			 *
			 * @param int $detected 1|0 whether the crawler is detected
			 *
			 * @since 1.0.20
			 */
			$is_crawler = apply_filters( 'tve_dash_is_crawler', $is_crawler );
		}
	}

	/**
	 * Finally, filter the value, allowing any other 3rd party plugin to override bot detection.
	 * To be used in cases where the page is actually fetched by a bot used by a caching plugin to render a cached version of the page.
	 *
	 * @param int $is_crawler Whether a bot has been detected or not ( 1 / 0 )
	 *
	 * @return int
	 */
	$GLOBALS['thrive_dashboard_bot_detection'] = (int) apply_filters( 'tve_dash_is_crawler_override', $is_crawler );

	return $GLOBALS['thrive_dashboard_bot_detection'];
}

/**
 * Whether the server software is Apache or something else
 *
 * @return bool
 */
function tve_dash_is_apache() {
	return ( strpos( $_SERVER['SERVER_SOFTWARE'], 'Apache' ) !== false || strpos( $_SERVER['SERVER_SOFTWARE'], 'LiteSpeed' ) !== false );
}

/**
 * Defines the products order in the Thrive Dashboard WordPress Menu
 *
 * @return array
 */
function tve_dash_get_menu_products_order() {

	//apply a filters here so that other products should not be tight related to TD
	$items = apply_filters( 'tve_dash_menu_products_order', array(
		10  => 'tva',
		20  => 'tcm',
		30  => 'tho',
		40  => 'tvo',
		50  => 'tab',
		60  => 'tl',
		70  => 'tqb',
		80  => 'tu',
		90  => 'license_manager',
		100 => 'general_settings',
		120 => 'font_manager',
		130 => 'font_import_manager',
		140 => 'icon_manager',
		150 => 'access_manager',
		160 => 'tcb',
		170 => 'tcm_sub_menu',
		/*For Thrive Themes*/
		180 => 'thrive_theme_admin_page_templates',
		190 => 'thrive_theme_license_validation',
		200 => 'thrive_theme_admin_options',
	) );

	ksort( $items );

	return $items;
}

/**
 * Enqueue a script during an ajax call - this will make sure the script will be loaded in the page when the ajax call returns content
 *
 * @param string|array $handle
 * @param string|null  $url      if empty, it will try to get it from the WP_Scripts object
 * @param string       $extra_js extra javascript to be outputted before the script
 *
 * @return bool
 */
function tve_dash_ajax_enqueue_script( $handle, $url = null, $extra_js = null ) {
	if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
		return false;
	}

	if ( empty( $url ) ) {
		$scripts = wp_scripts();
		$data    = $scripts->query( $handle );
		if ( empty( $data ) || ! is_object( $data ) || ! $data->src ) {
			return false;
		}
		$url      = $data->ver ? add_query_arg( 'ver', $data->ver, $data->src ) : $data->src;
		$extra_js = $scripts->get_data( $handle, 'data' );
		if ( ! preg_match( '|^(https?:)?//|', $url ) ) {
			$url = $scripts->base_url . $url;
		}

	}

	_tve_dash_ajax_enqueue( $handle, $url, 'js', $extra_js );

	return true;
}

/**
 * Enqueue a CSS external stylesheet during an ajax call
 *
 * @param string|array $handle
 * @param string|null  $url      if empty, it will try to get it from the WP_Scripts object
 * @param string       $extra_js extra javascript to be outputted before the script
 *
 * @return bool
 */
function tve_dash_ajax_enqueue_style( $handle, $url = null ) {
	if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
		return false;
	}

	if ( empty( $url ) ) {
		$styles = wp_styles();
		$data   = $styles->query( $handle );
		if ( empty( $data ) || ! is_object( $data ) || ! $data->src ) {
			return false;
		}
		$url = $data->ver ? add_query_arg( 'ver', $data->ver, $data->src ) : $data->src;
		if ( ! preg_match( '|^(https?:)?//|', $url ) ) {
			$url = $styles->base_url . $url;
		}
	}

	_tve_dash_ajax_enqueue( $handle, $url, 'css' );

	return true;
}

/**
 * Enqueue a resource (css or js) based on $type parameter
 *
 * @param string $handle
 * @param string $url
 * @param string $type
 * @param string $extra used for javascript resources, will prepend a script node with these contents before loading the script
 */
function _tve_dash_ajax_enqueue( $handle, $url, $type = 'js', $extra = '' ) {
	if ( ! isset( $GLOBALS['tve_dash_resources'][ $type ] ) ) {
		$GLOBALS['tve_dash_resources'][ $type ] = array();
	}
	$GLOBALS['tve_dash_resources'][ $type ][ $handle ] = $url;

	if ( 'js' === $type && ! empty( $extra ) ) {
		$GLOBALS['tve_dash_resources'][ $type ][ $handle . '_before' ] = $extra;
	}
}

/**
 * Get server information
 *
 * @return array
 */
function tve_get_debug_data() {

	$info = array();

	global $wpdb;

	$info[] = array(
		'name'  => 'PHP Version',
		'value' => PHP_VERSION,
	);

	$info[] = array(
		'name'  => 'WP Memory Limit',
		'value' => WP_MEMORY_LIMIT,
	);

	$info[] = array(
		'name'  => 'Memory Limit',
		'value' => ini_get( 'memory_limit' ),
	);

	$info[] = array(
		'name'  => 'Max upload size',
		'value' => size_format( wp_max_upload_size() ),
	);

	$info[] = array(
		'name'  => 'Max execution time',
		'value' => ini_get( 'max_execution_time' ),
	);

	$info[] = array(
		'name'  => 'Max Post Size',
		'value' => ini_get( 'post_max_size' ),
	);

	$info[] = array(
		'name'  => 'Max Input Vars',
		'value' => ini_get( 'max_input_vars' ),
	);

	$info[] = array(
		'name'  => 'MySQL Version',
		'value' => $wpdb->db_version(),
	);

	$info[] = array(
		'name'  => 'Server Software',
		'value' => $_SERVER['SERVER_SOFTWARE'],
	);

	return $info;
}

/**
 * Display a nicely-formatted error message generated during plugin activation (e.g. not compatible with the minimum required version of WordPress)
 * Formats the message differently in WP_CLI
 *
 * @param string $error_type error message type. if none is identified, it will be outputted as the error message
 * @param mixed  $_          any number of additional parameters to be used depending on $error_type
 */
function tve_dash_show_activation_error( $error_type, $_ = null ) {

	$args = func_get_args();
	array_shift( $args );

	$is_cli = defined( 'WP_CLI' ) && WP_CLI;

	switch ( $error_type ) {
		case 'wp_version':
			$product        = $args[0];
			$min_wp_version = $args[1];

			$link = admin_url( 'update-core.php' );
			if ( ! $is_cli ) {
				$link = '<a target="_top" href="' . $link . '">' . __( 'updates', 'thrive-dash' ) . '</a>';
			}
			$message = sprintf( __( '%s requires at least WordPress version %s. Your WordPress version is %s. Update WordPress by visiting the %s page', 'thrive-dash' ), $product, $min_wp_version, get_bloginfo( 'version' ), $link );
			break;

		default:
			$message = $error_type;
			break;
	}

	if ( $is_cli ) {
		if ( class_exists( 'WP_CLI' ) ) {
			$message = WP_CLI::colorize( '%r' . trim( $message ) . '%n' );
		}
		echo $message . PHP_EOL; // phpcs:ignore
		exit( 1 );
	}

	/* Regular WP-admin html error */
	$style = '<style type="text/css">body,html {height:100%;margin: 0;padding: 0;font-family: "Open Sans",sans-serif;font-size:13px;color:#810000}div{height:75%;display:flex;align-items:center}</style>';
	exit( $style . '<div><span>' . esc_html( $message ) . '</span></div>' ); // phpcs:ignore
}

/**
 * Prepare Thrive parent node to show in admin bar
 *
 * @return array
 */
function tve_dash_get_thrive_parent_node() {
	return array(
		'id'    => 'tve_parent_node',
		'title' => '<span style="width:18px;height:12px;display:inline-block;background-image:url(' . TVE_DASH_URL . '/css/images/thrive-leaf.png);margin-right:5px !important;" class="thrive-adminbar-icon"></span>' . __( 'Edit with Thrive', 'thrive-cb' ),
		'href'  => '',
		'meta'  => array(
			'class' => 'thrive-admin-bar',
			'html'  => '<style>#wpadminbar .thrive-admin-bar:hover .thrive-adminbar-icon{background-position:bottom left;} #wpadminbar{z-index: 9999999 !important;}</style>',
		),
	);
}

/**
 * Return list of webhook data processing integrations
 *
 * @return array
 */
function tve_dash_get_webhook_trigger_integrated_apis() {
	return array(
		'activecampaign' => array(
			'key'        => 'activecampaign',
			'label'      => 'ActiveCampaign',
			'image'      => TVE_DASH_URL . '/inc/auto-responder/views/images/activecampaign.png',
			'selected'   => false,
			'kb_article' => 'https://help.thrivethemes.com/en/articles/4625431-how-to-set-up-incoming-webhooks-in-thrive-ultimatum-using-activecampaign',
		),
		'drip'           => array(
			'key'        => 'drip',
			'label'      => 'Drip',
			'image'      => TVE_DASH_URL . '/inc/auto-responder/views/images/drip.png',
			'selected'   => false,
			'kb_article' => 'http://help.thrivethemes.com/en/articles/4741857-how-to-set-up-incoming-webhooks-in-thrive-ultimatum-using-drip',
		),
		'fluentcrm'      => array(
			'key'                => 'fluentcrm',
			'label'              => 'FluentCRM',
			'image'              => TVE_DASH_URL . '/inc/auto-responder/views/images/fluentcrm.png',
			'custom_integration' => true,
			'data'               => Thrive_Dash_List_Manager::connection_instance( 'fluentcrm' )->get_tags(),
			'selected'           => false,
			'kb_article'         => 'http://help.thrivethemes.com/en/articles/5024011-how-to-set-up-incoming-webhooks-in-thrive-ultimatum-using-fluentcrm',
		),
		'infusionsoft'   => array(
			'key'        => 'infusionsoft',
			'label'      => 'Keap (Infusionsoft)',
			'image'      => TVE_DASH_URL . '/inc/auto-responder/views/images/infusionsoft.png',
			'selected'   => false,
			'kb_article' => 'http://help.thrivethemes.com/en/articles/4741865-how-to-set-up-incoming-webhooks-in-thrive-ultimatum-using-infusionsoft',
		),
		'zapier'         => array(
			'key'        => 'zapier',
			'label'      => 'Zapier',
			'image'      => TVE_DASH_URL . '/inc/auto-responder/views/images/zapier.png',
			'selected'   => false,
			'kb_article' => 'http://help.thrivethemes.com/en/articles/4741869-how-to-set-up-incoming-webhooks-in-thrive-ultimatum-using-zapier',
		),
		'general'        => array(
			'key'        => 'general',
			'label'      => 'Generic webhook',
			'image'      => TVE_DASH_URL . '/inc/auto-responder/views/images/email.png',
			'selected'   => false,
			'kb_article' => 'http://help.thrivethemes.com/en/articles/4741872-how-to-set-up-incoming-webhooks-in-thrive-ultimatum-using-generic-webhook',
		),
	);
}

/**
 * Sync form data with specific custom fields setup
 *
 * @param $trigger_data
 *
 * @return array|mixed
 * @throws Exception
 */
function tve_sync_form_data( $trigger_data ) {
	if ( empty( $trigger_data['extra_data']['form_identifier']['value'] ) || $trigger_data['extra_data']['form_identifier']['value'] === 'none' ) {
		$trigger = Trigger::get_by_id( $trigger_data['id'] );

		if ( ! $trigger ) {
			return $trigger_data;
		}

		$default = $trigger::get_info();
		if ( ! empty( $trigger_data['conditions'] ) ) {
			$default['conditions'] = $trigger_data['conditions'];
		}
		$default['extra_data']                               = $trigger_data['extra_data'];
		$default['extra_data']['form_identifier']['value']   = '';
		$default['extra_data']['form_identifier']['preview'] = '';

		return $default;
	}
	$custom_fields = [];
	$form          = FormSettings::get_one( $trigger_data['extra_data']['form_identifier']['value'] );
	if ( ! empty( $form ) ) {
		$data_fields                                    = Data_Object::get_all_filterable_fields( [ 'form_data' ] );
		$trigger_data['filterable_fields']['form_data'] = $data_fields['form_data'];
		$not_custom                                     = [ 'email', 'phone', 'name', 'password', 'confirm_password' ];
		foreach ( $form->inputs as $input ) {
			if ( ! empty( $input['id'] ) && ! in_array( $input['id'], $not_custom, true ) ) {
				$custom_fields[ $input['id'] ] = [
					'id'            => $input['id'],
					'validators'    => [],
					'name'          => $input['label'],
					'description'   => 'Custom field',
					'tooltip'       => 'Custom field',
					'placeholder'   => 'Custom field',
					'is_ajax_field' => false,
					'value_type'    => 'string',
					'shortcode_tag' => '%' . $input['id'] . '%',
					'dummy_value'   => $input['label'] . ' field value',
					'primary_key'   => false,
					'filters'       => [ 'string_ec' ],
				];
				if ( $input['id'] === 'user_consent' ) {
					$custom_fields[ $input['id'] ]['filters'] = [ 'boolean' ];
				}
			}

		}

		unset( $trigger_data['filterable_fields']['form_data'][ Form_Identifier::get_id() ] );
		$trigger_data['filterable_fields']['form_data'] = array_merge( $trigger_data['filterable_fields']['form_data'], $custom_fields );
	}

	return $trigger_data;
}


/**
 * get relevant data from webhook trigger
 *
 * @param $request WP_REST_Request
 *
 * @return array
 */
function tve_dash_get_general_webhook_data( $request ) {
	$contact = $request->get_param( 'email' );

	return array( 'email' => empty( $contact ) ? '' : $contact );
}

function tve_dash_to_camel_case( $string ) {
	$string = implode( '', array_map( 'ucfirst', explode( '_', $string ) ) );

	return lcfirst( $string );
}

/**
 * Check if a plugin with the same name for file and folder is active
 *
 * @param $plugin_name
 *
 * @return boolean
 */
function tve_dash_is_plugin_active( $plugin_name ) {
	if ( ! function_exists( 'is_plugin_active' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	if ( substr( $plugin_name, - 4 ) !== '.php' ) {
		$slug = $plugin_name . '/' . $plugin_name . '.php';
	} else {
		$slug = str_replace( '.php', '', $plugin_name ) . '/' . $plugin_name;
	}

	return is_plugin_active( $slug );
}

function tve_dash_is_ttb_active() {
	/**
	 * Allows template builder website or landing page preview website to hook here and modify this functionality
	 *
	 * @returns boolean
	 */

	return apply_filters( 'tve_dash_is_ttb_active', wp_get_theme()->get_template() === 'thrive-theme' );
}

/**
 * Flush the cache for a certain page/post
 * Currently works for: WP Super Cache, W3 Total Cache, WP Rocket, WP Fastest Cache
 * Used in: Thrive Ultimatum(for promotion pages), Thrive Optimize
 *
 * @param $post_id
 */
function tve_flush_cache( $post_id ) {
	$post_id = (int) $post_id;

	if ( ! $post_id ) {
		return;
	}

	/**
	 * WP Super Cache flush the cache when a post is update/saved based on @see wp_transition_post_status()
	 */
	wp_update_post(
		array(
			'ID' => $post_id,
		)
	);

	/**
	 * W3 Total Cache
	 */
	if ( function_exists( 'w3tc_flush_post' ) ) {
		w3tc_flush_post( $post_id );
	}

	/**
	 * WP Rocket
	 */
	if ( function_exists( 'rocket_clean_post' ) ) {
		rocket_clean_post( $post_id );
	}

	/**
	 * WP Fastest Cache
	 */
	if ( function_exists( 'wpfc_clear_post_cache_by_id' ) ) {
		wpfc_clear_post_cache_by_id( $post_id );
	}
}

/**
 * Do not cache a page
 * Currently works for: WP Super Cache, W3 Total Cache, WP Rocket, WP Fastest Cache, or aby cache plugin that users the DONOTCACHEPAGE constant
 */
function tve_do_not_cache_page() {
	! defined( 'DONOTCACHEPAGE' ) && define( 'DONOTCACHEPAGE', true );

	add_filter( 'rocket_override_donotcachepage', '__return_false', PHP_INT_MAX );

	if ( function_exists( 'wpfc_exclude_current_page' ) ) {
		wpfc_exclude_current_page();
	}
}
