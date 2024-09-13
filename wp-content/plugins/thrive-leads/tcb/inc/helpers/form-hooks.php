<?php

use TCB\inc\helpers\FormSettings;

/**
 * Processes each form settings instance, saving it to the database
 *
 * @param array $forms array of form settings
 * @param int   $post_parent
 *
 * @return array map of replacements
 */
function tve_save_form_settings( $forms, $post_parent ) {
	$replaced   = [];
	$post_title = 'Form settings' . ( $post_parent ? ' for content ' . $post_parent : '' );

	foreach ( $forms as $form ) {
		$id       = ! empty( $form['id'] ) ? (int) $form['id'] : 0;
		$instance = FormSettings::get_one( $form['id'], $post_parent )
		                        ->set_config( wp_unslash( $form['settings'] ) )
		                        ->save( $post_title, empty( $post_parent ) ? null : [ 'post_parent' => $post_parent ] );
		if ( $instance->ID !== $id ) {
			$replaced[ $form['id'] ] = $instance->ID;
		}
	}

	return $replaced;
}

/**
 * Delete one or multiple form settings
 *
 * @param array|int|string $id
 */
function tve_delete_form_settings( $id ) {
	if ( empty( $id ) ) {
		return;
	}

	if ( ! is_array( $id ) ) {
		$id = [ $id ];
	}

	foreach ( $id as $form_id ) {
		$form_id = (int) $form_id;
		FormSettings::get_one( $form_id )->delete();
	}
}

/**
 * Delete all forms specific to a post
 *
 * @param $post_id
 *
 * @return void
 */
function tve_delete_post_form_settings( $post_id ) {
	$query = new \WP_Query( [
			'post_type'      => [
				FormSettings::POST_TYPE,
			],
			'fields'         => 'ids',
			'post_parent'    => $post_id,
			'posts_per_page' => '-1',
		]
	);

	$post_ids = $query->posts;
	foreach ( $post_ids as $id ) {
		tve_delete_form_settings( $id );
	}
}

/**
 * Once a page is deleted remove also forms associated
 */
add_action( 'before_delete_post', static function ( $post_id, $post ) {
	if ( $post->post_type !== FormSettings::POST_TYPE ) {
		tve_delete_post_form_settings( $post_id );
	}
}, 10, 2 );

add_action( 'tve_leads_delete_post', 'tve_delete_post_form_settings', 10, 1 );

add_action( 'after_delete_post', 'tve_delete_post_form_settings' );

/**
 * On frontend contexts, always remove form settings from content
 */
add_filter( 'tve_thrive_shortcodes', static function ( $content, $is_editor_page ) {
	if ( ! $is_editor_page && strpos( $content, FormSettings::SEP ) !== false ) {
		$content = preg_replace( FormSettings::pattern( true ), '', $content );
	}

	return $content;
}, 10, 2 );

/**
 * Process content pre-save
 */
add_filter( 'tcb.content_pre_save', static function ( $response, $post_data ) {
	/**
	 * Allows skipping the process of saving form settings to database
	 *
	 * @param bool $skip whether or not to skip
	 *
	 * @return bool
	 */
	$process_form_settings = apply_filters( 'tcb_process_form_settings', true );

	if ( $process_form_settings && ! empty( $post_data['forms'] ) ) {
		/**
		 * save form settings to the database
		 */
		$post_id = isset( $post_data['post_id'] ) ? (int) $post_data['post_id'] : 0;
		if ( ! empty( $post_data['ignore_post_parent'] ) ) {
			$post_id = null;
		}
		$response['forms'] = tve_save_form_settings( $post_data['forms'], $post_id );
	}

	if ( $process_form_settings && ! empty( $post_data['deleted_forms'] ) ) {
		tve_delete_form_settings( $post_data['deleted_forms'] );
	}

	return $response;
}, 10, 2 );

