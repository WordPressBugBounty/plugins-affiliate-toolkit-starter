<?php
defined('ABSPATH') || exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class atkp_template_table extends WP_List_Table {
	function __construct() {
		parent::__construct( array(
			'singular' => __( 'Template', 'affiliate-toolkit-starter' ),
			//Singular label
			'plural'   => __( 'Templates', 'affiliate-toolkit-starter' ),
			//plural label, also this well be one of the table css class
			'ajax'     => false
			//We won't support Ajax for this table
		) );
	}

	protected function get_views() {
		$views   = array();
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- WP_List_Table view filter, nonce not applicable.
		$current = ( ! empty( $_REQUEST['view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['view'] ) ) : 'custom' );

		//Foo link
		$foo_url = admin_url( 'admin.php?page=ATKP_viewtemplate' ); // add_query_arg( 'view', 'custom' );

		$bar_url = $foo_url . '&view=custom';
		$class           = ( $current == 'custom' ? ' class="current"' : '' );
		$views['custom'] = "<a href='" . esc_url( $bar_url ) . "' {$class}>" . esc_html__( 'Custom template', 'affiliate-toolkit-starter' ) . "</a>";

		//Bar link
		$bar_url = $foo_url . '&view=system';
		$class           = ( $current == 'system' ? ' class="current"' : '' );
		$views['system'] = "<a href='" . esc_url( $bar_url ) . "' {$class}>" . esc_html__( 'System template', 'affiliate-toolkit-starter' ) . "</a>";

		return $views;
	}


	/**
	 * Delete a customer record.
	 *
	 * @param int $id customer ID
	 */
	public static function delete_template( $id ) {
		$queue = atkp_template::load( $id );

		//$queue->delete();
	}


	/**
	 * Returns the count of records in the database.
	 *
	 * @return null|string
	 */
	public static function record_count() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- WP_List_Table view filter, nonce not applicable.
		$view = ( isset( $_REQUEST['view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['view'] ) ) : 'custom' );

		if ( $view == 'system' ) {
			return count( atkp_template::get_system_list( null, null ) );
		} else {
			return atkp_template::get_total();
		}
	}


	/** Text displayed when no customer data is available */
	public function no_items() {
		esc_html__( 'No templates available.', 'affiliate-toolkit-starter' );
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
				return $item['post_title'];
				break;
			case 'template_type':
				if ( is_numeric( $item['ID'] ) ) {
					$durations = array();

					$durations = apply_filters( 'atkp_get_template_types', $durations );

					foreach ( $durations as $value => $name ) {
						if ( $value == ATKPTools::get_post_setting( $item['ID'], ATKP_TEMPLATE_POSTTYPE . '_template_type' ) ) {
							return $name;
						}
					}

					return '';
				} else {
					return $item['template_type'];
				}
				break;
			case 'template_preview':
				$template_preview_image = apply_filters( 'atkp_template_preview_image_url', '', ( $item['ID'] ) );

				if ( $template_preview_image != '' ) {
					return '<div class="atkp-template-dropdown"><img alt="' . esc_attr( $item['post_title'] ) . '" src="' . esc_attr( $template_preview_image ) . '" style="max-height:120px; max-width: 180px;" />
					<div class="atkp-template-dropdown-content">
  <img src="' . esc_attr( $template_preview_image ) . '" alt="' . esc_attr( $item['post_title'] ) . '" style="max-width:600px">
  <div class="atkp-template-desc">' . esc_html( $item['post_title'] ) . '</div>
  </div></div>';
				} else {
					return '';
				}
				break;
			case 'post_date':
				return mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $item['post_modified'] );;
				break;
			case 'Shortcode':

				$temptype = isset( $item['template_type_id'] ) ? $item['template_type_id'] : '';
				if ( $temptype == '' && is_numeric( $item['ID'] ) ) {
					$temptype = ATKPTools::get_post_setting( $item['ID'], ATKP_TEMPLATE_POSTTYPE . '_template_type' );
				}

				if ( $temptype == 5 ) {
					return '<code>[atkp_searchform template=\'' . $item['ID'] . '\'][/atkp_searchform]</code>';
				} else if ( $item['ID'] == 'simple_live' || $item['ID'] == 'default_live' ) {
					return '<code>[atkp_livelist template=\'' . $item['ID'] . '\' livetemplate=\'secondwide\'][/atkp_livelist]</code>';
				} else {
					return '<code>[atkp template=\'' . $item['ID'] . '\' ids=\'\'][/atkp]</code>';
				}
			case 'title':
			default:
				return $item[ $column_name ];




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
		return '';

		//return sprintf(
		//	'<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['ID']
		//);
	}


	/**
	 * Method for name column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_name( $item ) {

		$delete_nonce = wp_create_nonce( 'atkp_edit_template' );
		$naunce       = wp_create_nonce( 'atkp-export-template' );

		if ( is_numeric( $item['ID'] ) ) {
			$title = sprintf( '<a href="post.php?post=%s&action=edit"><strong>%s</strong></a>', absint( $item['ID'] ), $item['post_title'] );

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- WP_List_Table display, nonce not applicable.
			$page = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
			$actions = [
				'edit'   => sprintf( '<a href="post.php?post=%s&action=edit">%s</a>', absint( $item['ID'] ), esc_html__( 'Edit', 'affiliate-toolkit-starter' ) ),
				'delete' => sprintf( '<a href="?page=%1$s&action=%2$s&templateid=%3$s&_wpnonce=%4$s">%5$s</a>', esc_attr( $page ), 'delete', absint( $item['ID'] ), $delete_nonce, esc_html__( 'Delete', 'affiliate-toolkit-starter' ) ),
				'clone'  => sprintf( '<a href="?page=%1$s&action=%2$s&templateid=%3$s&templatename=%4$s&_wpnonce=%5$s">%6$s</a>', esc_attr( $page ), 'clone', absint( $item['ID'] ), urlencode( $item['post_title'] ), $delete_nonce, esc_html__( 'Duplicate', 'affiliate-toolkit-starter' ) ),
				'export' => sprintf( '<a href="%1$s?action=atkp_export_template&templateid=%2$s&request_nonce=%3$s">%4$s</a>', esc_url( ATKPTools::get_endpointurl() ), absint( $item['ID'] ), $naunce, esc_html__( 'Export', 'affiliate-toolkit-starter' ) ),
			];

		} else {
			$title = $item['post_title'];

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- WP_List_Table display, nonce not applicable.
			$page = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
			$actions = [
				'clone' => sprintf( '<a href="?page=%1$s&action=%2$s&templateid=%3$s&templatename=%4$s&_wpnonce=%5$s">%6$s</a>', esc_attr( $page ), 'clone', esc_attr( $item['ID'] ), urlencode( $item['post_title'] ), $delete_nonce, esc_html__( 'Duplicate', 'affiliate-toolkit-starter' ) ),
			];

		}

		return $title . $this->row_actions( $actions );
	}


	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	function get_columns() {
		$columns = [

			'name'          => __( 'Title', 'affiliate-toolkit-starter' ),
			'Shortcode'     => __( 'Shortcode', 'affiliate-toolkit-starter' ),
			'template_type' => __( 'Template Type', 'affiliate-toolkit-starter' ),
			'post_date'     => __( 'Last modified', 'affiliate-toolkit-starter' ),
		];//'cb'      => '<input type="checkbox" />',

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- WP_List_Table view filter, nonce not applicable.
		$view = ( isset( $_REQUEST['view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['view'] ) ) : 'custom' );

		if ( $view == 'system' ) {
			$columns['template_preview'] = __( 'Preview', 'affiliate-toolkit-starter' );
		}

		return $columns;
	}


	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'id'         => array( 'id', true ),
			'post_title' => array( 'title', false )
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

		];

		return $actions;
	}


	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- WP_List_Table pagination/sorting, nonce not applicable.
		$view = ( isset( $_REQUEST['view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['view'] ) ) : 'custom' );

		$this->_column_headers = $this->get_column_info();

		/** Process bulk action */
		$this->process_bulk_action();

		$per_page     = $this->get_items_per_page( 'links_per_page', 50 );
		$current_page = $this->get_pagenum();
		$total_items  = self::record_count();

		$this->set_pagination_args( [
			'total_items' => $total_items, //WE have to calculate the total number of items
			'per_page'    => $per_page, //WE have to determine how many items to show on a page
		] );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- WP_List_Table sorting parameters.
		$orderby_raw    = isset( $_REQUEST['orderby'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) : 'id';
		$allowed_orderby = array( 'id', 'post_title', 'post_date', 'post_status' );
		$orderby         = in_array( $orderby_raw, $allowed_orderby, true ) ? $orderby_raw : 'id';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$order_raw = isset( $_REQUEST['order'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) : 'desc';
		$order     = in_array( strtolower( $order_raw ), array( 'asc', 'desc' ), true ) ? $order_raw : 'desc';

		if ( $view == 'system' ) {
			$this->items = atkp_template::get_system_list( $per_page, $current_page, $orderby, $order );
		} else {
			$this->items = atkp_template::get_page_list( $per_page, $current_page, $orderby, $order );
		}


	}

	public function process_bulk_action() {

		//Detect when a bulk action is being triggered...
		if ( 'delete' === $this->current_action() ) {

			// In our file that handles the request, verify the nonce.
			$nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';

			if ( ! wp_verify_nonce( $nonce, 'atkp_delete_link' ) ) {
				die( 'Go get a life script kiddies' );
			} else {
				$obj = atkp_template::load( isset( $_GET['templateid'] ) ? absint( $_GET['templateid'] ) : 0 );

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
				$obj = atkp_template::load( $id );
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