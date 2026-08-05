<?php
defined('ABSPATH') || exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class atkp_queue_entry_table extends WP_List_Table {
	function __construct() {
		parent::__construct( array(
			'singular' => __( 'Queue Entry', 'affiliate-toolkit-starter' ),
			//Singular label
			'plural'   => __( 'Queue Entries', 'affiliate-toolkit-starter' ),
			//plural label, also this well be one of the table css class
			'ajax'     => false
			//We won't support Ajax for this table
		) );
	}

	/**
	 * @var atkp_queue $queue
	 */
	public static $queue;


	/**
	 * Delete a customer record.
	 *
	 * @param int $id customer ID
	 */
	public static function delete_queue( $id ) {
		//$queue = atkp_queue_entry::load($id);

		//$queue->delete();
	}


	/**
	 * Returns the count of records in the database.
	 *
	 * @return null|string
	 */
	public static function record_count( $filter = '' ) {

		return atkp_queue_entry::get_total( self::$queue->id, $filter );
	}


	/** Text displayed when no customer data is available */
	public function no_items() {
		esc_html__( 'No queues available.', 'affiliate-toolkit-starter' );
	}


	/**
	 * Render a column when no column specific method exist.
	 *
	 * @param array $item
	 * @param string $column_name
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'name':
			case 'id':
			default:
				return $item[ $column_name ];

			case 'post_id':
				$post_id   = $item[ $column_name ];
				$post_type = $item['post_type'];

				$link = get_edit_post_link( $post_id );
				if ( $link == null ) {
					$title = get_the_title( $post_id );
					if ( $title == null ) {
						return ( $post_id == 0 ? '' : $post_id );
					} else {
						return esc_html( $title ) . ' (' . $post_id . ', ' . $post_type . ')';
					}
				} else {
					$title = get_the_title( $post_id );

					return '<a href="' . esc_url( $link ) . '" target="_blank">' . esc_html( $title ) . ' (' . esc_html( $post_id ) . ')</a>';
				}
				break;
			case 'shop_id':
				$shopid = $item[ $column_name ];

				if ( $shopid > 0 ) {
					$link = get_edit_post_link( $shopid );

					if ( $link == null ) {
						$title = get_the_title( $shopid );
						if ( $title == null ) {
							return esc_html( $shopid );
						} else {
							return esc_html( $title ) . ' (' . esc_html( $shopid ) . ')';
						}
					} else {
						$title = get_the_title( $shopid );

						return '<a href="' . esc_url( $link ) . '" target="_blank">' . esc_html( $title ) . ' (' . esc_html( $shopid ) . ')</a>';
					}
				}
				break;

			case 'post_type':
				$posttypes = explode( ', ', $item[ $column_name ] );

				$names = array();
				foreach ( $posttypes as $pt ) {
					$post_type_obj = get_post_type_object( $pt );
					if ( $post_type_obj != null ) {
						$names[] = $post_type_obj->labels->singular_name;
					} //Ice Cream.
					else {
						$names[] = $pt;
					}
				}

				return implode( '<br />', $names );

				break;
			case 'status':
				switch ( $item[ $column_name ] ) {
					case atkp_queue_entry_status::SUCCESSFULLY:
						return '<span style="color:green;font-weight:bold;">' . esc_html__( 'Successfully', 'affiliate-toolkit-starter' ) . '</span>';

					case atkp_queue_entry_status::ERROR:
						return '<span style="color:red;font-weight:bold;">' . esc_html__( 'Error', 'affiliate-toolkit-starter' ) . '</span>';

					case atkp_queue_entry_status::NOT_PROCESSED:
						return '<span style="color:orange;font-weight:bold;">' . esc_html__( 'Not processed', 'affiliate-toolkit-starter' ) . '</span>';

					case atkp_queue_entry_status::PROCESSED:
						return '<span style="font-weight:bold;">' . esc_html__( 'Processed', 'affiliate-toolkit-starter' ) . '</span>';
					case atkp_queue_entry_status::FINISHED:
						return '<span style="color:green;font-weight:bold;">' . esc_html__( 'Finalized', 'affiliate-toolkit-starter' ) . '</span>';
					case atkp_queue_entry_status::PREPARED:
						return '<span style="color:orange;font-weight:bold;">' . esc_html__( 'Prepared for processing', 'affiliate-toolkit-starter' ) . '</span>';
				}
				break;
			case 'updatedon':
				return esc_html( ATKPTools::get_formatted_date( strtotime( $item[ $column_name ] ) ) ) . esc_html__( ' at ', 'affiliate-toolkit-starter' ) . esc_html( ATKPTools::get_formatted_time( strtotime( $item[ $column_name ] ) ) );
				break;

			//default:
			//		return print_r( $item, true ); //Show the whole array for troubleshooting purposes
		}
	}


	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['id']
		);
	}


	/**
	 * Method for name column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_name( $item ) {

		$delete_nonce = wp_create_nonce( 'atkp_edit_queue' );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- WP_List_Table display, nonce not applicable.
		$page = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
		$title = sprintf( '<a href="?page=%s&action=%s&queueid=%s&_wpnonce=%s"><strong>%s</strong></a>', esc_attr( $page ), 'detail', absint( $item['id'] ), $delete_nonce, esc_html( $item['title'] ) );

		$actions = [
			//'edit' => sprintf( '<a href="?page=%s&action=%s&queueid=%s&_wpnonce=%s">Edit</a>', esc_attr( $page ), 'edit', absint( $item['id'] ), $delete_nonce ),
			//'delete' => sprintf( '<a href="?page=%s&action=%s&queueid=%s&_wpnonce=%s">Delete</a>', esc_attr( $page ), 'delete', absint( $item['id'] ), $delete_nonce ),
		];

		return $title . $this->row_actions( $actions );
	}


	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	function get_columns() {
		$columns = [
			'id'                => __( 'ID', 'affiliate-toolkit-starter' ),
			'post_id'           => __( 'Object', 'affiliate-toolkit-starter' ),
			'shop_id'           => __( 'Shop', 'affiliate-toolkit-starter' ),
			'status'            => __( 'Status', 'affiliate-toolkit-starter' ),
			'functionname'      => __( 'Function', 'affiliate-toolkit-starter' ),
			'functionparameter' => __( 'Parameter', 'affiliate-toolkit-starter' ),
			'updatedon'         => __( 'Last update', 'affiliate-toolkit-starter' ),
			'updatedmessage'    => __( 'Message', 'affiliate-toolkit-starter' ),
		];

		return $columns;
	}


	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'id'   => array( 'id', true ),
			'name' => array( 'title', false )
		);

		return $sortable_columns;
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = [
			//'bulk-delete' => 'Delete'
		];

		return $actions;
	}


	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items() {

		$this->_column_headers = $this->get_column_info();

		/** Process bulk action */
		$this->process_bulk_action();

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- WP_List_Table filter parameter, nonce not applicable.
		$filter = isset( $_GET['filter'] ) ? sanitize_text_field( wp_unslash( $_GET['filter'] ) ) : '';

		$per_page     = $this->get_items_per_page( 'links_per_page', 50 );
		$current_page = $this->get_pagenum();
		$total_items  = self::record_count( $filter );

		$this->set_pagination_args( [
			'total_items' => $total_items, //WE have to calculate the total number of items
			'per_page'    => $per_page //WE have to determine how many items to show on a page
		] );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- WP_List_Table sorting parameters.
		$orderby_raw    = isset( $_REQUEST['orderby'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) : 'id';
		$allowed_orderby = array( 'id', 'title', 'createdon', 'status' );
		$orderby         = in_array( $orderby_raw, $allowed_orderby, true ) ? $orderby_raw : 'id';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$order_raw = isset( $_REQUEST['order'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) : 'asc';
		$order     = in_array( strtolower( $order_raw ), array( 'asc', 'desc' ), true ) ? $order_raw : 'asc';
		$this->items = atkp_queue_entry::get_list( self::$queue->id, $filter, $per_page, $current_page, $orderby, $order );
	}

	public function process_bulk_action() {

		//Detect when a bulk action is being triggered...
		if ( 'delete' === $this->current_action() ) {

			// In our file that handles the request, verify the nonce.
			$nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';

			if ( ! wp_verify_nonce( $nonce, 'atkp_delete_link' ) ) {
				die( 'Go get a life script kiddies' );
			} else {
				$obj = atkp_queue::load( isset( $_GET['queueid'] ) ? absint( $_GET['queueid'] ) : 0 );

				$obj->delete();

				// esc_url_raw() is used to prevent converting ampersand in url to "#038;"
				// add_query_arg() return the current url
				$page = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
				wp_safe_redirect( sprintf( '?page=%s', esc_attr( $page ) ) );
				exit;
			}

		}

		// If the delete bulk action is triggered
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Bulk action nonce verified by WP_List_Table internally.
		if ( ( isset( $_POST['action'] ) && sanitize_text_field( wp_unslash( $_POST['action'] ) ) == 'bulk-delete' )
		     || ( isset( $_POST['action2'] ) && sanitize_text_field( wp_unslash( $_POST['action2'] ) ) == 'bulk-delete' )
		) {

			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified by WP_List_Table.
			$delete_ids = isset( $_POST['bulk-delete'] ) ? array_map( 'absint', (array) $_POST['bulk-delete'] ) : array();

			// loop over the array of record IDs and delete them
			foreach ( $delete_ids as $id ) {
				$obj = atkp_queue::load( $id );
				$obj->delete();

			}

			// esc_url_raw() is used to prevent converting ampersand in url to "#038;"
			// add_query_arg() return the current url
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			$page = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
			wp_safe_redirect( sprintf( '?page=%s', esc_attr( $page ) ) );
			exit;
		}
	}

}