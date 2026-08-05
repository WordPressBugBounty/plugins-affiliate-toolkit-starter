<?php
defined('ABSPATH') || exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class atkp_queue_table extends WP_List_Table {
	function __construct() {
		parent::__construct( array(
			'singular' => __( 'Queue', 'affiliate-toolkit-starter' ),
			//Singular label
			'plural'   => __( 'Queues', 'affiliate-toolkit-starter' ),
			//plural label, also this well be one of the table css class
			'ajax'     => false
			//We won't support Ajax for this table
		) );
	}


	/**
	 * Delete a customer record.
	 *
	 * @param int $id customer ID
	 */
	public static function delete_queue( $id ) {
		$queue = atkp_queue::load( $id );

		$queue->delete();
	}


	/**
	 * Returns the count of records in the database.
	 *
	 * @return null|string
	 */
	public static function record_count() {

		return atkp_queue::get_total();
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
			case 'title':
			default:
				return $item[ $column_name ];

			case 'runtime':
				$createdon = $item['createdon'];
				$updatedon = $item['updatedon'];
				$seconds   = abs( strtotime( $updatedon ) - strtotime( $createdon ) );

				if ( $seconds > 60 ) {
					/* translators: %s: number of minutes */
					return sprintf( esc_html__( '%s Minutes', 'affiliate-toolkit-starter' ), round( $seconds / 60, 0 ) );
				} else {
					/* translators: %s: number of seconds */
					return sprintf( esc_html__( '%s Seconds', 'affiliate-toolkit-starter' ), round( $seconds, 0 ) );
				}

				break;

			case 'retries':
				/* translators: %s: number of retries */
				return sprintf( esc_html__( '%s Retries', 'affiliate-toolkit-starter' ), $item[ $column_name ] == null || $item[ $column_name ] <= 1 ? 0 : ( $item[ $column_name ] - 1 ) );
			case 'entries':
				$atkp_queuetable_helper = new atkp_queuetable_helper();

				$cnt      = $atkp_queuetable_helper->get_queue_count( $item['id'] );
				$finished = $atkp_queuetable_helper->get_queue_finished( $item['id'] );

				$createdon = $item['createdon'];
				$updatedon = $item['updatedon'];
				$seconds   = abs( strtotime( $updatedon ) - strtotime( $createdon ) );

				$itemspersecond = $finished > 0 ? ( $seconds / $finished ) : 0;

				/* translators: %s: number of entries */
				return '<span style="">' . sprintf( esc_html__( '%s Entries', 'affiliate-toolkit-starter' ), $cnt ) . '</span>' . ( $finished != $cnt ? '<br /><span style="">' . sprintf( esc_html__( '%s Finished', 'affiliate-toolkit-starter' ), $finished ) . '</span>' : '' ) . ( $itemspersecond > 0 ? '<br /><span style="">' . sprintf( esc_html__( '%s Entries/Minute', 'affiliate-toolkit-starter' ), round( $itemspersecond * 60, 2 ) ) . '</span>' : '' );


				break;
			case 'type':
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
				$atkp_queuetable_helper = new atkp_queuetable_helper();

				$percent = '0,0%';

				$total = $atkp_queuetable_helper->get_queue_count( $item['id'] );
				if ( $total > 0 ) {
					$finished = $atkp_queuetable_helper->get_queue_finished( $item['id'] );
					$percent  = number_format( round( ( ( $finished / $total ) * 100 ), 1 ), 1, ',', '.' ) . '%';
				}

				switch ( $item[ $column_name ] ) {
					case atkp_queue_status::SUCCESSFULLY:
						return '<span style="color:green;font-weight:bold;">' . esc_html__( 'Successfully', 'affiliate-toolkit-starter' ) . '</span>' . '<br /> ';

					case atkp_queue_status::ERROR:
						$cnt = $atkp_queuetable_helper->get_queue_errors( $item['id'] );

						if ( $cnt > 0 ) {
							// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- WP_List_Table display, nonce not applicable.
							$page = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
							/* translators: %s: number of errors */
							return sprintf( '<a href="?page=%1$s&action=%2$s&queueid=%3$s&filter=error"><span style="color:red;font-weight:bold;text-decoration:underline">' . esc_html__( '%4$s Errors', 'affiliate-toolkit-starter' ) . '</span></a>', esc_attr( $page ), 'detail', absint( $item['id'] ), $cnt );
						} else {
							/* translators: %s: number of errors */
							return '<span style="color:red;font-weight:bold;">' . sprintf( esc_html__( '%s Errors', 'affiliate-toolkit-starter' ), $cnt ) . '</span>';
						}

					case atkp_queue_status::ABORT:
						return '<span style="color:orange;font-weight:bold;">' . esc_html__( 'Abort', 'affiliate-toolkit-starter' ) . '</span>' . ' (' . esc_html( $percent ) . ')';

					case atkp_queue_status::ACTIVE:
						return '<span style="color:green;font-weight:bold;">' . esc_html__( 'Running', 'affiliate-toolkit-starter' ) . '</span>' . ' (' . esc_html( $percent ) . ')';
				}
				break;
			case 'updatedon':
			case 'createdon':

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
		return ''; //sprintf(			'<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['id']		);
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
			'delete' => sprintf( '<a href="?page=%s&action=%s&queueid=%s&_wpnonce=%s">Delete</a>', esc_attr( $page ), 'delete', absint( $item['id'] ), $delete_nonce ),

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
			'cb'        => '<input type="checkbox" />',
			'name'      => __( 'Title', 'affiliate-toolkit-starter' ),
			'status'    => __( 'Status', 'affiliate-toolkit-starter' ),
			'type'      => __( 'Type', 'affiliate-toolkit-starter' ),
			'entries'   => __( 'Entries', 'affiliate-toolkit-starter' ),
			'createdon' => __( 'Created on', 'affiliate-toolkit-starter' ),
			'updatedon' => __( 'Last Activity', 'affiliate-toolkit-starter' ),
			'runtime'   => __( 'Runtime', 'affiliate-toolkit-starter' ),
			'retries'   => __( 'Retries', 'affiliate-toolkit-starter' ),
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

		$per_page     = $this->get_items_per_page( 'links_per_page', 25 );
		$current_page = $this->get_pagenum();
		$total_items  = self::record_count();

		$this->set_pagination_args( [
			'total_items' => $total_items, //WE have to calculate the total number of items
			'per_page'    => $per_page //WE have to determine how many items to show on a page
		] );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- WP_List_Table sorting parameters.
		$orderby_raw    = isset( $_REQUEST['orderby'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) : 'id';
		$allowed_orderby = array( 'id', 'title', 'createdon', 'status' );
		$orderby         = in_array( $orderby_raw, $allowed_orderby, true ) ? $orderby_raw : 'id';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$order_raw = isset( $_REQUEST['order'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) : 'desc';
		$order     = in_array( strtolower( $order_raw ), array( 'asc', 'desc' ), true ) ? $order_raw : 'desc';
		$this->items = atkp_queue::get_list( $per_page, $current_page, $orderby, $order );
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