<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Thrive_Leads_Contacts_List extends WP_List_Table {

	protected $table_name;
	protected $per_page = 20;
	protected $connections;
	protected $wpdb;

	public function __construct( $args ) {
		if ( ! is_array( $args ) ) {
			$args = array();
		}

		global $wpdb;
		$this->wpdb = $wpdb;

		$this->table_name = $wpdb->prefix . 'tve_leads_contacts';

		$args['plural'] = ! empty( $args['plural'] ) ? $args['plural'] : 'contacts';

		parent::__construct( $args );
	}

	public function get_columns() {
		$columns = array(
			'cb'            => '<input type="checkbox" />',
			'name'          => __( 'Name', "thrive-leads" ),
			'email'         => __( 'Email', "thrive-leads" ),
			'date'          => __( 'Date and time', "thrive-leads" ),
			'custom_fields' => __( 'Custom Data', "thrive-leads" ),
			'actions'       => 'Actions',
		);

		return $columns;
	}

	/**
	 * Columns that will have sortable links in table's header
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		$columns = array(
			'date' => array( 'date', 'ASC' ),
			'name' => array( 'name', 'ASC' ),
		);

		return $columns;
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = array(
			'bulk-delete' => __( 'Delete', "thrive-leads" ),
		);

		return $actions;
	}

	public function prepare_items() {
		/* Process bulk action */
		$this->process_bulk_action();

		$this->per_page = $this->get_contact_filter( 'per-page' );

		//get total items
		$total_items = $this->get_contacts();

		//init current page
		$current_page = ! empty( $_REQUEST['paged'] ) ? (int) $_REQUEST['paged'] : 1;
		$current_page = $current_page >= 1 ? $current_page : 1;

		//calculate total pages
		$total_pages = ceil( $total_items / $this->per_page );

		//calculate the offset from where to begin the query
		$offset      = ( $current_page - 1 ) * $this->per_page;
		$this->items = $this->get_contacts( false, $offset );

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'total_pages' => $total_pages,
			'per_page'    => $this->per_page,
		) );

		//init header columns
		$this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );
	}

	/**
	 * Get contacts from the database for the table
	 *
	 * @param bool|true $count
	 * @param int       $offset
	 *
	 * @return array|false|int|null|object
	 */
	private function get_contacts( $count = true, $offset = 0 ) {
		$sql       = "SELECT " . ( $count ? "COUNT(*)" : "*" ) . " FROM {$this->table_name} `contacts` ";
		$post_data = $_REQUEST;

		$start_date = $this->get_contact_filter( 'start-date' );
		$end_date   = $this->get_contact_filter( 'end-date' );
		$source     = $this->get_contact_filter( 'source' );

		$params = array();
		if ( $source > 0 ) {
			$sql      .= "JOIN " . tve_leads_table_name( 'event_log' ) . " `logs` ON `logs`.id=`contacts`.`log_id` WHERE `logs`.`main_group_id`=%s ";
			$params[] = $source;
		} else {
			$sql .= "WHERE 1";
		}

		if ( ! empty( $start_date ) && ! empty( $end_date ) ) {
			$sql       .= " AND `contacts`.`date` BETWEEN %s AND %s ";
			$params [] = $start_date;
			$params [] = $end_date . ' 23:59:59';
		}

		if ( empty( $post_data['orderby'] ) ) {
			$sql .= " ORDER BY `contacts`.`date` DESC ";
		} else {
			$sql .= " ORDER BY `contacts`." . esc_sql( $post_data['orderby'] );
			if ( ! empty( $post_data['order'] ) ) {
				$sql .= " " . $post_data['order'];
			}
		}

		if ( $count ) {
			return $this->wpdb->get_var( $this->prepare( $sql, $params ) );
		} else {
			$sql .= " LIMIT {$offset}," . $this->per_page;

			return $this->wpdb->get_results( $this->prepare( $sql, $params ) );
		}
	}

	public function process_bulk_action() {

		switch ( $this->current_action() ) {
			case 'delete':
				// In our file that handles the request, verify the nonce.
				$nonce = esc_attr( $_REQUEST['_wpnonce'] );

				if ( ! wp_verify_nonce( $nonce, 'tl_delete_contact' ) ) {
					die( 'Not cool!' );
				} else {
					$this->delete_contact( absint( $_REQUEST['contact'] ) );
				}
				break;

			case 'bulk-delete':
				if ( empty( $_REQUEST['bulk-action'] ) ) {
					return;
				}

				$delete_ids = esc_sql( $_REQUEST['bulk-action'] );
				// loop over the array of record IDs and delete them
				foreach ( $delete_ids as $id ) {
					$this->delete_contact( $id );

				}
		}

	}

	/**
	 * Delete contact from database
	 *
	 * @param $id
	 */
	private function delete_contact( $id ) {
		$this->wpdb->delete(
			"{$this->table_name}",
			array( 'ID' => $id ),
			array( '%d' )
		);
	}

	/** Text displayed when no customer data is available */
	public function no_items() {
		_e( 'No contacts saved.', 'thrive-contacts' );
	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	protected function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="bulk-action[]" value="%s" />', $item->id
		);
	}

	/**
	 * This function is called for each column for each row
	 * If there is no specific function for column this is called
	 * And this is the default implementation for showing a value in each cell of the table
	 *
	 * @param $item        object of entire row from db
	 * @param $column_name string column name to be displayed
	 *
	 * @return mixed
	 */
	protected function column_default( $item, $column_name ) {
		return esc_html( $item->$column_name );
	}

	protected function column_name( $item ) {
		if ( empty( $item->name ) ) {
			return __( 'N/A', "thrive-leads" );
		}

		return esc_html( $item->name );
	}

	protected function column_date( $item ) {
		return tve_leads_format_date( $item->date );
	}

	protected function column_custom_fields( $item ) {
		$fields = json_decode( $item->custom_fields, true );

		$info = "";
		if ( empty( $fields ) ) {
			return __( 'N/A', "thrive-leads" );
		}

		foreach ( $fields as $name => $value ) {
			$info .= "<strong>" . esc_html( $name ) . "</strong>: " . esc_html( $value ) . "<br/>";
		}

		return sprintf( '%1$s',
			trim( $info )
		);
	}

	protected function column_actions( $item ) {
		// create a nonce
		$delete_nonce = wp_create_nonce( 'tl_delete_contact' );

		$actions = array(
			'delete' => sprintf( '<a class="tvd-delete tvd-btn-icon tvd-btn-icon-red" href="?page=thrive_leads_contacts&action=%s&contact=%s&paged=%s&_wpnonce=%s" title="%s"><span class="tvd-icon-trash-o"></span>%s</a>',
				'delete',
				$item->id,
				$this->get_pagenum(),
				$delete_nonce,
				__( 'Delete', "thrive-leads" ),
				__( 'Delete', "thrive-leads" )
			),
		);

		return $this->row_actions( $actions, true );
	}

	protected function extra_tablenav( $which ) {
		$start_date = $this->get_contact_filter( 'start-date' );
		$end_date   = $this->get_contact_filter( 'end-date' );
		$source     = $this->get_contact_filter( 'source' );
		$per_page   = $this->get_contact_filter( 'per-page' );

		include dirname( dirname( __DIR__ ) ) . '/views/contacts/contacts_filters.php';
	}

	/**
	 *
	 * replace table names in form of {table_name} with the prefixed version
	 *
	 * @param $sql
	 * @param $params
	 *
	 * @return false|null|string
	 */
	public function prepare( $sql, $params ) {
		$prefix = tve_leads_table_name( '' );
		$sql    = preg_replace( '/\{(.+?)\}/', '`' . $prefix . '$1' . '`', $sql );

		if ( strpos( $sql, '%' ) === false ) {
			return $sql;
		}

		return $this->wpdb->prepare( $sql, $params );
	}

	/**
	 * Get a valid date from a raw string
	 *
	 * @param string $req_field name of the request parameter
	 * @param string $default   default value to return in case date does not exist or is invalid. Defaults to current date
	 *
	 * @return string
	 */
	private function get_date_filter( $req_field, $default = null ) {
		if ( $default === null ) {
			$default = date( 'Y-m-d' );
		}

		if ( ! empty( $_REQUEST[ $req_field ] ) ) {
			$date = strtotime( $_REQUEST[ $req_field ] );
			if ( ! empty( $date ) ) {
				$date = date( 'Y-m-d', $date );
			}
		}

		return empty( $date ) ? $default : $date;
	}

	private function get_contact_filter( $filter ) {
		switch ( $filter ) {
			case 'start-date':
				$value = $this->get_date_filter( 'tve-start-date', date( 'Y-m-d', strtotime( '-7 days' ) ) );
				break;

			case 'end-date':
				$value = $this->get_date_filter( 'tve-end-date' );
				break;

			case 'source':
				$value = empty( $_REQUEST['tve-source'] ) ? - 1 : (int) $_REQUEST['tve-source'];
				break;

			case 'per-page':
				$value = empty( $_REQUEST['tve-per-page'] ) ? 20 : (int) $_REQUEST['tve-per-page'];
				break;

			default:
				$value = '';
		}

		return $value;
	}

}
