<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 11/13/2017
 * Time: 4:32 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Class Thrive_AB_Meta_Box_Variations_Table
 */
class Thrive_AB_Meta_Box_Variations_Table extends WP_List_Table {
	/**
	 * @var Thrive_AB_Page
	 */
	private $_page;
	private $_variations_ins = array();
	private $_page_tests = array();
	private $_table_has_no_test_class = 'thrive-ab-variation-no-test';
	private $_variations_per_page = 100;

	/**
	 * Thrive_AB_Meta_Box_Variations_Table constructor.
	 *
	 * @param $page Thrive_AB_Page
	 */
	public function __construct( $page ) {

		$this->_page           = $page;
		$this->_page_tests     = $this->_page->get_tests( array( 'status' => 'running' ) );
		$this->_variations_ins = $this->_page->get_variations( array(), 'instance' );

		parent::__construct( array(
			'singular' => 'thrive-ab-variation', //singular name of the listed records
			'plural'   => 'thrive-ab-variations', //plural name of the listed records
			'ajax'     => false,
		) );
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
		$data     = $this->_variations_ins;
		$per_page = $this->_variations_per_page;

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
	 * Override the parent columns method. Defines the columns to use in your listing table
	 *
	 * @return array
	 */
	public function get_columns() {

		$columns = array(
			'post_title'          => __( 'Name', 'thrive-ab-page-testing' ),
			'traffic'             => __( 'Traffic', 'thrive-ab-page-testing' ),
			'impressions'         => __( 'Unique Visitors', 'thrive-ab-page-testing' ),
			'conversions'         => __( 'Conversions', 'thrive-ab-page-testing' ),
			'conversions_rate'    => __( 'Conversions Rate', 'thrive-ab-page-testing' ),
			'revenue'             => __( 'Revenue', 'thrive-ab-page-testing' ),
			'revenue_visitor'     => __( 'Revenue per visitor', 'thrive-ab-page-testing' ),
			'improvement'         => __( 'Improvement', 'thrive-ab-page-testing' ),
			'chance_to_beat_orig' => __( 'Chance to Beat Original', 'thrive-ab-page-testing' ),
		);

		if ( is_array( $this->_page_tests ) && count( $this->_page_tests ) && $this->_page_tests[0]['type'] === 'optins' ) {
			$columns['conversions']      = __( 'Subscriptions', 'thrive-ab-page-testing' );
			$columns['conversions_rate'] = __( 'Subscriptions Rate', 'thrive-ab-page-testing' );
		}

		return $columns;
	}

	/** Text displayed when no customer data is available */
	public function no_items() {
		echo __( 'There are no Thrive A/B Test variations for this page', 'thrive-ab-page-testing' );
	}

	/**
	 * Get a list of CSS classes for the WP_List_Table table tag.
	 *
	 * @since 3.1.0
	 *
	 * @return array List of CSS classes for the table tag.
	 */
	public function get_table_classes() {
		$classes = parent::get_table_classes();
		if ( count( $this->_page_tests ) === 0 ) {
			$classes[] = $this->_table_has_no_test_class;
		}
		$classes = array_diff( $classes, array( 'striped' ) );

		return $classes;
	}


	/**
	 * Define which columns are hidden
	 *
	 * @return array
	 */
	public function get_hidden_columns() {
		$return        = array( 'conversions', 'conversions_rate' );
		$running_tests = count( $this->_page_tests );

		if ( $running_tests === 0 || $running_tests > 0 && ( $this->_page_tests[0]['type'] === 'visits' || $this->_page_tests[0]['type'] === 'optins' ) ) {
			$return = array( 'revenue', 'revenue_visitor' );
		}

		return $return;
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

		if ( isset( $item[ $column_name ] ) ) {
			$value = $item[ $column_name ];
		}

		return $value;
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
	 * Generates content for a single row of the table
	 *
	 * @since 3.1.0
	 *
	 * @param object $item The current item
	 */
	public function single_row( $item ) {
		if ( (int) $item->get_test_item()->active === 1 ) {
			echo '<tr>';
			$this->single_row_columns( $item );
			echo '</tr>';
		}
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

		include dirname( __FILE__ ) . '/views/admin/edit-post/variations-table.php';
	}

	/**
	 * Returns Basic HTML for a column
	 *
	 * @param $classes
	 * @param $data
	 *
	 * @return string
	 */
	private function _column_template( $classes, $data ) {
		$html = '';

		$html .= "<td class='$classes' $data>";
		$html .= '%s';
		$html .= '</td>';

		return $html;
	}

	/**
	 * @param $item Thrive_AB_Variation
	 * @param $classes
	 * @param $data
	 * @param $primary
	 *
	 * @return string
	 */
	public function _column_post_title( $item, $classes, $data, $primary ) {
		$html = $this->_column_template( $classes, $data );

		$edit_link = '';
//		if ( count( $this->_page_tests ) === 0 ) {
			$edit_link .= '&nbsp;&nbsp;';
			$edit_link .= '<a href="' . $item->get_editor_url() . '" target="_blank" class="top-edit-icon">' . tcb_icon( 'pencil', true, 'sidebar', 'thrive-ab-edit-post-icons' ) . '</a>';
//		}

		$preview_link = '<a href="' . $item->get_preview_url() . '" target="_blank">' . tcb_icon( 'external-link', true, 'sidebar', 'thrive-ab-edit-post-icons' ) . '</a>';

		$post_title = '<span>' . $item->post_title . '</span>&nbsp;' . $preview_link . '' . $edit_link;


		return sprintf( $html, $post_title );
	}

	/**
	 * @param $item Thrive_AB_Variation
	 * @param $classes
	 * @param $data
	 * @param $primary
	 *
	 * @return string
	 */
	public function _column_traffic( $item, $classes, $data, $primary ) {
		$html = $this->_column_template( $classes, $data );

		$traffic   = $item->get_traffic();
		$item_data = $item->get_data();

		$range = sprintf( '<input class="thrive-ab-traffic-input" id="thrive-ab-traffic-range-%s" type="range" min="0" max="100" step="1" value="%s" data-tab_variation_value="%s" data-tab_variation_id="%s" />', $item_data['ID'], $traffic, $traffic, $item_data['ID'] );
		$input = sprintf( '<input class="thrive-ab-traffic-input" id="thrive-ab-traffic-input-%s" type="number" min="0" max="100" value="%s" data-tab_variation_value="%s" data-tab_variation_id="%s" />', $item_data['ID'], $traffic, $traffic, $item_data['ID'] );

		$traffic_html = '<div class="thrive-ab-edit-post-traffic-holder"><div class="thrive-ab-edit-post-slider-holder">' . $range . '</div><div class="thrive-ab-edit-post-input-holder">' . $input . '</div></div>';//%

		return sprintf( $html, $traffic_html );
	}

	/**
	 * Handles the impressions column content
	 *
	 * @param $item Thrive_AB_Variation
	 * @param $classes
	 * @param $data
	 * @param $primary
	 *
	 * @return string
	 */
	public function _column_impressions( $item, $classes, $data, $primary ) {
		$html = $this->_column_template( $classes, $data );

		return sprintf( $html, $item->get_test_item()->get_impressions() );
	}

	/**
	 * Handles the conversions column content
	 *
	 * @param $item Thrive_AB_Variation
	 * @param $classes
	 * @param $data
	 * @param $primary
	 *
	 * @return string
	 */
	public function _column_conversions( $item, $classes, $data, $primary ) {
		$html = $this->_column_template( $classes, $data );

		return sprintf( $html, $item->get_test_item()->get_conversions() );
	}

	/**
	 * Handles the conversions_rate column content
	 *
	 * @param $item Thrive_AB_Variation
	 * @param $classes
	 * @param $data
	 * @param $primary
	 *
	 * @return string
	 */
	public function _column_conversions_rate( $item, $classes, $data, $primary ) {
		$html = $this->_column_template( $classes, $data );

		return sprintf( $html, $item->get_test_item()->conversion_rate() . '%' );
	}

	/**
	 * Handles the revenue column content
	 *
	 * @param $item Thrive_AB_Variation
	 * @param $classes
	 * @param $data
	 * @param $primary
	 *
	 * @return string
	 */
	public function _column_revenue( $item, $classes, $data, $primary ) {
		$html = $this->_column_template( $classes, $data );

		return sprintf( $html, $item->get_test_item()->revenue );
	}

	/**
	 * Handles the revenue_visitor column content
	 *
	 * @param $item Thrive_AB_Variation
	 * @param $classes
	 * @param $data
	 * @param $primary
	 *
	 * @return string
	 */
	public function _column_revenue_visitor( $item, $classes, $data, $primary ) {
		$html = $this->_column_template( $classes, $data );

		$value = $item->get_test_item()->revenue_per_visitor();

		return sprintf( $html, $value );
	}

	/**
	 * Handles the improvement column content if current item is control
	 *
	 * @param $item Thrive_AB_Variation
	 * @param $classes
	 * @param $data
	 * @param $primary
	 *
	 * @return string
	 */
	public function _column_improvement( $item, $classes, $data, $primary ) {
		$html = '';
		if ( $item->is_control() ) {
			$classes .= ' thrv-ab-variation-control';
			$html    .= "<td class='$classes' $data colspan='2'>[ " . __( 'This is the control', 'thrive-ab-page-testing' ) . ' ]</td>';
			echo $html;

			return;
		}

		$improvement = $item->get_test_item()->get_improvement();

		$classes .= $improvement < 0 ? ' thrive-ab-red' : ' thrive-ab-green';

		$html = $this->_column_template( $classes, $data );

		return sprintf( $html, $improvement . '%' );
	}

	/**
	 * Handles the chance_to_beat_orig column content if current item is control
	 *
	 * @param $item Thrive_AB_Variation
	 * @param $classes
	 * @param $data
	 * @param $primary
	 *
	 * @return string
	 */
	public function _column_chance_to_beat_orig( $item, $classes, $data, $primary ) {
		if ( $item->is_control() ) {
			return;
		}

		$chance  = $item->get_test_item()->get_chance_to_beat_original();
		$classes .= $chance < 0 ? ' thrive-ab-red' : ' thrive-ab-green';

		$html = $this->_column_template( $classes, $data );

		return sprintf( $html, $chance . '%' );
	}
}
