<?php

class Thrive_Ult_Visitors_Status_Tab extends Thrive_Ult_Tab {
	protected $items;

	/**
	 * Specific tab has to implement this function which transforms
	 * items(pages, posts, post types) into Option models
	 *
	 * @return void
	 */
	protected function matchItems() {
		if ( ! $this->getItems() ) {
			return;
		}

		$optionArr = $this->getSavedOptions()->getTabSavedOptions( 7, $this->hanger );

		foreach ( $this->getItems() as $id => $label ) {
			$option = new Thrive_Ult_Option();
			$option->setLabel( $label );
			$option->setId( $id );
			$option->setIsChecked( in_array( $id, $optionArr ) );
			$this->options[] = $option;
		}
	}

	/**
	 * Has to get the Option from json string based on the $item
	 *
	 * @param $item
	 *
	 * @return Option
	 */
	protected function getSavedOption( $item ) {
		return $this->getSavedOptionForTab( 7, $item );
	}

	/**
	 * Read items from the database and initiate them
	 *
	 * @return $this
	 */
	protected function initItems() {
		$this->items = array(
			'logged_in'  => __( 'Logged in', 'thrive-ult' ),
			'logged_out' => __( 'Logged out', 'thrive-ult' ),
		);

		return $this;
	}

	public function isStatusAllowed( $status ) {
		$this->hanger = 'show_options';

		return $this->getSavedOption( $status )->isChecked;
	}

	public function isStatusDenied( $status ) {
		$this->hanger = 'hide_options';

		return $this->getSavedOption( $status )->isChecked;
	}

}
