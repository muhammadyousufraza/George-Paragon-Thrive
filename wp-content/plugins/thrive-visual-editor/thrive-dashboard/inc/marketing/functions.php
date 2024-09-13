<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Add one or more tags to a subscriber in an autoresponder that’s connected through the API.
 *
 * @param string $connection Connection (required) [href = #connection]
 * @param array  $data       Update Tag Data (required) [href = #updatetagsdata]
 *
 * @return boolean
 *          - true if the tags were added </br>
 *          - false if the process fails (exceptions thrown by autoresponders) or the parameters don’t pass the validations. </br>
 * E.g. E-mail is empty or the autoresponder isn’t connected in Thrive Dashboard
 *
 * @api
 */
function thrv_update_tags( $connection, $data = array() ) {

	$api_instance = Thrive_Dash_List_Manager::connection_instance( $connection );

	if ( true !== $api_instance instanceof Thrive_Dash_List_Connection_Abstract ) {
		return false;
	}

	$email = ! empty( $data['email'] ) ? $data['email'] : null;

	if ( empty( $email ) ) {
		return false;
	}

	$tags  = ! empty( $data['tags'] ) ? $data['tags'] : '';
	$extra = ! empty( $data['extra'] ) ? $data['extra'] : array();

	if ( is_array( $tags ) ) {
		$tags = implode( ',', $tags );
	}

	return $api_instance->update_tags( $email, $tags, $extra );
}

/**
 * Add or update a custom field to a subscriber record in an autoresponder that’s connected through the API.
 *
 * @param string $connection Connection (required) [href = #connection]
 * @param array  $data       Add Custom Fields Data (required) [href = #customfieldsdata]
 *
 * @return int|boolean
 *          - Brevo (SendInBlue), MailerLite, Drip, ConverKit, ActiveCampaign and Aweber will return an int representing the subscriber_id </br>
 *          - Campaign Monitor, Zoho and Infusionsoft will return: </br>
 *              &emsp; - true if the custom fields are added successfully; </br>
 *              &emsp; - false if the process fails (exceptions thrown by autoresponders) or the parameters don’t pass the validations </br>
 * E.g. E-mail is empty or the autoresponder isn’t connected in Thrive Dashboard
 *
 * @api
 */
function thrv_add_custom_fields( $connection, $data = array() ) {

	$api_instance = Thrive_Dash_List_Manager::connection_instance( $connection );

	if ( true !== $api_instance instanceof Thrive_Dash_List_Connection_Abstract ) {
		return false;
	}

	$email = ! empty( $data['email'] ) ? $data['email'] : null;

	if ( empty( $email ) ) {
		return false;
	}

	$custom_fields = ! empty( $data['custom_fields'] ) && is_array( $data['custom_fields'] ) ? $data['custom_fields'] : array();
	$extra         = ! empty( $data['extra'] ) ? $data['extra'] : array();

	return $api_instance->add_custom_fields( $email, $custom_fields, $extra );
}

/**
 * This is a helper for the thrv_add_custom_fields function. It will return the available custom field for a specific connection.
 *
 * @param string $connection Connection (required) [href = #connection]
 *
 * @return boolean|array
 *                  - false if the autoresponder isn’t connected to the website or not supported by Thrive Themes</br>
 *                  - an array containing the id, the name, the type and the label of the available custom fields the specific connection. </br>
 * E.g.
 * <pre>
 * [0]=>[</br>
 *      "id"=>"703417",</br>
 *      "name"=>"MyCustomFields",</br>
 *      "type"=>"text",</br>
 *      "label"=>""</br>
 *      ]</br>
 * </pre>
 *
 * @api
 */
function thrv_get_available_custom_fields( $connection ) {
	$api_instance = Thrive_Dash_List_Manager::connection_instance( $connection );

	if ( true !== $api_instance instanceof Thrive_Dash_List_Connection_Abstract ) {
		return false;
	}

	return $api_instance->get_available_custom_fields();
}

/**
 * This is a helper for the update thrv_update_tags and thrv_add_custom_fields functions. It will return the available lists for a specific connection.
 *
 * @param string $connection Connection (required) [href = #connection]
 *
 * @return boolean|array
 *                  - false if the autoresponder isn’t connected to the website or not supported by Thrive Themes</br>
 *                  - an array containing the id and the name of the available lists for the specific connection.</br>
 * E.g.
 * </br>
 * <pre>
 * [0]=>[</br>
 *      "id"=>"703417",</br>
 *      "name"=>"MyList"</br>
 *      ]</br>
 * </pre>
 *
 * @api
 */
function thrv_get_available_lists( $connection ) {
	$api_instance = Thrive_Dash_List_Manager::connection_instance( $connection );

	if ( true !== $api_instance instanceof Thrive_Dash_List_Connection_Abstract ) {
		return false;
	}

	return $api_instance->get_lists( false );
}

