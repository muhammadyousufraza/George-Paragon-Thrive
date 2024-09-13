<?php

class TCB_Editor_Meta_Boxes {

	const FLAG = 'tcb-only-meta-boxes';

	public static function init() {
		add_filter( 'redirect_post_location', [ __CLASS__, 'redirect_post_location' ], 10, 2 );

		if ( isset( $_GET[ static::FLAG ] ) ) {
			add_filter( 'replace_editor', '__return_true' );

			add_action( 'admin_action_edit', [ __CLASS__, 'admin_action_edit' ] );
		}
	}

	public static function admin_action_edit() {
		global $hook_suffix, $post;

		$post   = get_post( $_GET['post'] );
		$screen = get_current_screen();

		wp_enqueue_media( [ 'post' => $post ] );

		/* display all meta boxes */
		add_filter( 'hidden_meta_boxes', '__return_empty_array' );

		do_action( 'admin_enqueue_scripts', $hook_suffix );

		require_once ABSPATH . 'wp-admin/includes/meta-boxes.php';

		wp_enqueue_style( 'wp-edit-post' );
		wp_enqueue_style( 'buttons' );
		wp_enqueue_style( 'common' );
		wp_enqueue_style( 'forms' );
		wp_enqueue_style( 'edit' );

		wp_enqueue_script( 'post' );
		wp_print_scripts( [ 'jquery' ] );

		wp_enqueue_style( 'tcb-editor-meta-boxes', tve_editor_css( 'editor-meta-boxes.css' ), [], TVE_VERSION );

		register_and_do_post_meta_boxes( $post );

		include TVE_TCB_ROOT_PATH . '/admin/includes/views/editor-meta-boxes.php';
	}

	public static function redirect_post_location( $location, $post_id ) {
		if ( isset( $_POST['_wp_http_referer'] ) && strpos( $_POST['_wp_http_referer'], static::FLAG ) !== false ) {
			$location = add_query_arg( static::FLAG, 1, $location );
		}

		return $location;
	}
}



