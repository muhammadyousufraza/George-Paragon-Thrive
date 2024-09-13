<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Thrive_Blog_List_Element
 */
class Thrive_Blog_List_Element extends TCB_Post_List_Element {
	/**
	 * Name of the element.
	 *
	 * @return string
	 */
	public function name() {
		return thrive_template() . ' List';
	}

	/**
	 * WordPress element identifier.
	 *
	 * @return string
	 */
	public function identifier() {
		return THRIVE_BLOG_LIST_IDENTIFIER;
	}

	/**
	 * Hide this in the sidebar.
	 */
	public function hide() {
		return true;
	}

	/**
	 * Component and control config.
	 *
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();

		/* re-use the post-list component */
		$components['blog_list'] = $components['post_list'];

		unset( $components['post_list'] );

		$pagination_types = [];

		/* for each pagination instance, get the label and the type for the select control config */
		foreach ( [ TCB_Pagination::NONE, 'numeric', TCB_Pagination::LOAD_MORE, 'infinite_scroll' ] as $type ) {
			$instance = tcb_pagination( $type );

			$pagination_types[] = [
				'name'  => $instance->get_label(),
				'value' => $instance->get_type(),
			];
		}

		$components['blog_list']['config']['PaginationType']['config']['options'] = $pagination_types;

		return $components;
	}
}

return new Thrive_Blog_List_Element( 'blog_list' );
