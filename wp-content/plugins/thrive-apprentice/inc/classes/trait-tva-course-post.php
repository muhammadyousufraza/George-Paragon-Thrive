<?php

trait TVA_Course_Post {
	public function duplicate_post_meta( $old_post, $new_post ) {
		foreach ( get_post_meta( $old_post->ID ) as $meta_key => $post_meta_item ) {
			if ( isset( $post_meta_item[0] ) ) {
				if ( is_serialized( $post_meta_item[0] ) ) {
					update_post_meta( $new_post->ID, $meta_key, thrive_safe_unserialize( $post_meta_item[0] ) );
				} else {
					update_post_meta( $new_post->ID, $meta_key, $post_meta_item[0] );
				}
			}
		}
	}
}
