<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 11/14/2017
 * Time: 2:00 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Class Thrive_AB_Meta_Box_Completed_Tests_Table
 */
class Thrive_AB_Meta_Box_Completed_Tests_Table extends WP_List_Table {
	/**
	 * @var Thrive_AB_Page
	 */
	private $_page;
	private $_page_tests = array();
	private $_items_per_page = 100;

	/**
	 * Thrive_AB_Meta_Box_Completed_Tests_Table constructor.
	 *
	 * @param $page Thrive_AB_Page
	 */
	public function __construct( $page ) {
		$this->_page       = $page;
		$this->_page_tests = $this->_page->get_tests( array( 'status' => 'completed' ), 'instance' );

		parent::__construct( array(
			'singular' => 'thrive-ab-completed-test', //singular name of the listed records
			'plural'   => 'thrive-ab-completed-tests', //plural name of the listed records
			'ajax'     => false,
		) );
	}

	/** Text displayed when no customer data is available */
	public function no_items() {
		echo __( 'There are no Thrive A/B Completed Tests for this page', 'thrive-ab-page-testing' );
	}

	/**
	 * Override the parent columns method. Defines the columns to use in your listing table
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'title'          => __( 'Completed Test', 'thrive-ab-page-testing' ),
			'notes'          => __( 'Description', 'thrive-ab-page-testing' ),
			'date_started'   => __( 'Start Date', 'thrive-ab-page-testing' ),
			'date_completed' => __( 'End Date', 'thrive-ab-page-testing' ),
			'view_test'      => '',
			'delete_test'    => '',
		);

		return $columns;
	}

	/**
	 * Define which columns are hidden
	 *
	 * @return array
	 */
	public function get_hidden_columns() {
		return array();
	}

	/**
	 * Define the sortable columns
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return array();
	}


	/**
	 * Get the table data
	 *
	 * @return array
	 */
	private function table_data() {
		$data = array();

		/**@var $test Thrive_AB_Test */
		foreach ( $this->_page_tests as $test ) {
			$tmp = $test->get_data();

			$time_started   = strtotime( $tmp['date_started'] );
			$time_completed = strtotime( $tmp['date_completed'] );

			$delete_href = sprintf( admin_url( 'admin.php?action=%s&ab_test_ID=%s&post_ID=%s' ), 'thrive-ab-tests-delete', absint( $tmp['id'] ), absint( $tmp['page_id'] ) );

			$preview_link = '<a href="' . $this->_page->get_test_link( $test->id ) . '">' . tcb_icon( 'external-link', true, 'sidebar', 'thrive-ab-edit-post-icons' ) . ' ' . __( 'View Test', 'thrive-ab-page-testing' ) . '</a>';
			$delete_link  = '<a href="' . $delete_href . '">' . tcb_icon( 'trash-o', true, 'sidebar', 'thrive-ab-edit-post-icons' ) . ' ' . __( 'Delete', 'thrive-ab-page-testing' ) . '</a>';

			$tmp['view_test']      = $preview_link;
			$tmp['delete_test']    = $delete_link;
			$tmp['date_started']   = $time_started ? date( 'd-m-Y', $time_started ) : '';
			$tmp['date_completed'] = $time_completed ? date( 'd-m-Y', $time_completed ) : '';

			$data[] = $tmp;
		}

		return $data;
	}

	/**
	 * Prepare the items for the table to process
	 *
	 * @return Void
	 */
	public function prepare_items() {
		$columns  = $this->get_columns();
		$hidden   = $this->get_hidden_columns();
		$sortable = $this->get_sortable_columns();
		$data     = $this->table_data();
		$per_page = $this->_items_per_page;

		$current_page = $this->get_pagenum();
		$total_items  = count( $data );
		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page,
		) );
		$data                  = array_slice( $data, ( ( $current_page - 1 ) * $per_page ), $per_page );
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items           = $data;
	}


	/**
	 * Override the parent display method. Defines the HTML content for your listing table
	 *
	 * @since  3.1.0
	 * @access public
	 */
	public function display() {
		$singular = $this->_args['singular'];

		$this->screen->render_screen_reader_content( 'heading_list' );

		include dirname( __FILE__ ) . '/views/admin/edit-post/tests-table.php';
	}

	/**
	 * Define what data to show on each column of the table
	 *
	 * @param  array  $item        Data
	 * @param  String $column_name - Current column name
	 *
	 * @return Mixed
	 */
	public function column_default( $item, $column_name ) {
		$value = '';

		if ( ! empty( $item[ $column_name ] ) ) {
			$value = $item[ $column_name ];
		}

		return $value;
	}

	/**
	 * Get a list of CSS classes for the WP_List_Table table tag.
	 *
	 * @since 3.1.0
	 *
	 * @return array List of CSS classes for the table tag.
	 */
	public function get_table_classes() {
		$classes = array_diff( parent::get_table_classes(), array( 'striped' ) );

		return $classes;
	}
}
