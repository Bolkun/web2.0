<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-wp-list-table.php' );
}

class BM_ListTable extends WP_List_Table {

	/**
	 * BM_ListTable constructor.
	 */
	public function __construct() {

		parent::__construct( array(
			'singular' => 'customer_group',
			'plural'   => 'customer_groups',
			'ajax'     => true,
		) );

	}

	/**
	 * add edit and delete links under group title
	 *
	 * @param $item
	 *
	 * @return string
	 */
	public function column_customer_group( $item ) {

		$edit_url   = wp_nonce_url( admin_url() . 'post.php?post=' . $item['ID'] . '&action=edit', 'edit-post' );
		$delete_url = get_delete_post_link( $item['ID'], '', true );

		$actions = array(
			'edit'   => '<a data-group="' . $item['ID'] . '" href="">' . __( 'Edit', 'b2b-market' ) . '</a>',
			'delete' => '<a href="' . $delete_url . '">' . __( 'Delete', 'b2b-market' ) . '</a>',
		);

		return sprintf( '%1$s <span style="color:silver">(id:%2$s)</span>%3$s', $item['title'], $item['ID'], $this->row_actions( $actions ) );
	}

	/**
	 * adding checkboxes for bulk selection
	 *
	 * @param object $item
	 *
	 * @return string|void
	 */
	public function column_cb( $item ) {
		//return sprintf( '<input type="checkbox" name="customer_group[]" value="%s" />', $item['ID'] );
	}

	/**
	 * add column for group price
	 *
	 * @param $item
	 *
	 * @return string
	 */
	public function column_group_price( $item ) {

		$bm_price = get_post_meta( $item['ID'], 'bm_price', true );
		$bm_type  = get_post_meta( $item['ID'], 'bm_price_type', true );
		$price    = '';

		if ( empty( $bm_price ) ) {
			$bm_price = 0;
		}

		if ( isset( $bm_type ) && ! empty( $bm_type ) ) {

			if ( 'fix' == $bm_type ) {

				$price = $bm_price . ' ' . get_woocommerce_currency_symbol() . ' (' . __( 'Fixed Price', 'b2b-market' ) . ')';

			} elseif ( 'discount' == $bm_type ) {

				$price = $bm_price . ' ' . get_woocommerce_currency_symbol() . ' (' . __( 'Discount', 'b2b-market' ) . ')';

			} elseif ( 'discount-percent' == $bm_type ) {

				$price = $bm_price . '% (' . __( 'Discount', 'b2b-market' ) . ')';
			}
		}

		return $price;
	}

	/**
	 * add column for bulk prices
	 *
	 * @param $item
	 *
	 * @return string
	 */
	public function column_bulk_price( $item ) {

		$bulk_prices = get_post_meta( $item['ID'], 'bm_bulk_prices', true );

		$set = __( 'No bulk prices set', 'b2b-market' );

		if ( ! empty( $bulk_prices ) && isset( $bulk_prices[0]['bulk_price'] ) ) {
			$set = __( 'Yes', 'b2b-market' );
		}

		return $set;
	}

	/**
	 * add column for products
	 *
	 * @param $item
	 *
	 * @return mixed|string
	 */
	public function column_include_products( $item ) {

		$bm_products = get_post_meta( $item['ID'], 'bm_products', true );
		if ( ! empty( $bm_products ) ) {

			$products = '';
			$elements = explode( ',', $bm_products );

			if ( isset( $elements ) && ! empty( $elements ) ) {
				if ( empty( $elements[0] ) ) {
					array_shift( $elements );
				}
			}

			foreach ( $elements as $element ) {
				$products .= get_the_title( $element ) . ', ';
			}
		} else {
			$products = __( 'No products included', 'b2b-market' );
		}

		return $products;
	}

	/**
	 * add column for categories
	 *
	 * @param $item
	 *
	 * @return mixed|string
	 */
	public function column_include_categories( $item ) {

		$bm_categories = get_post_meta( $item['ID'], 'bm_categories', true );

		if ( ! empty( $bm_categories ) ) {

			$cats     = '';
			$elements = explode( ',', $bm_categories );

			array_shift( $elements );

			foreach ( $elements as $element ) {
				$term  = get_term( $element, 'product_cat' );
				$cats .= $term->name . ', ';
			}
		} else {
			$cats = __( 'No categories included', 'b2b-market' );
		}

		return $cats;
	}


	/**
	 * get all columns
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			//'cb'                 => '<input type="checkbox" />',
			'customer_group'     => __( 'Customer Group', 'b2b-market' ),
			'group_price'        => __( 'Group Price', 'b2b-market' ),
			'bulk_price'         => __( 'Bulk Prices set?', 'b2b-market' ),
			'include_products'   => __( 'Included Products', 'b2b-market' ),
			'include_categories' => __( 'included Categories', 'b2b-market' ),
		);

		return $columns;
	}

	/**
	 * get sortable columns
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'customer_group' => array( 'title', false ),
		);

		return $sortable_columns;
	}


	/**
	 * get bulk actions
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		/*
		 * currently collide with bm-admin-ajax.js cause of redirect
		 */

		/*
		$actions = array(
			'delete' => __( 'Delete', 'b2b-market' )
		);

		return $actions;
		*/
	}


	/**
	 * process bulk actions
	 */
	public function process_bulk_action() {

		if ( isset( $_POST['_wpnonce'] ) && ! empty( $_POST['_wpnonce'] ) ) {

			$nonce  = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
			$action = 'bulk-' . $this->_args['plural'];

			if ( ! wp_verify_nonce( $nonce, $action ) ) {
				wp_die( 'Nope! Security check failed!' );
			}
		}

		$action = $this->current_action();

		switch ( $action ) {

			case 'delete':

				/* delete all product meta for group */

				$group  = get_post( $_GET['group'] );
				$prefix = 'bm_' . $group->post_name;

				global $wpdb;

				$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE '{$prefix}%'" );


				/* delete group itself */
				wp_delete_post( $_GET['group'], true );

				break;

			default:
				// do nothing
				return;
				break;
		}

		return;
	}


	/**
	 *  prepare loop for output
	 */
	public function prepare_items() {
		/* args */
		$per_page = 10;
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->process_bulk_action();

		/* get data for table */
		$data = array();

		$args   = array(
			'posts_per_page' => - 1,
			'post_type'      => 'customer_groups',
		);
		$groups = get_posts( $args );

		foreach ( $groups as $group ) {
			$arr = array(
				'title' => $group->post_title,
				'ID'    => $group->ID,
			);
			array_push( $data, $arr );
		}

		usort( $data, array( $this, 'usort_reorder' ) );

		/* handles pagination */
		$current_page = $this->get_pagenum();
		$total_items  = count( $data );
		$data         = array_slice( $data, ( ( $current_page - 1 ) * $per_page ), $per_page );

		$this->items = $data;
		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page,
			'total_pages' => ceil( $total_items / $per_page ),
		) );
	}

	/**
	 * modify usort for reordering groups
	 *
	 * @param $a
	 * @param $b
	 *
	 * @return int
	 */
	public function usort_reorder( $a, $b ) {
		$orderby = ( ! empty( $_REQUEST['orderby'] ) ) ? $_REQUEST['orderby'] : 'title';
		$order   = ( ! empty( $_REQUEST['order'] ) ) ? $_REQUEST['order'] : 'asc';
		$result  = strcmp( $a[ $orderby ], $b[ $orderby ] );

		return ( 'asc' === $order) ? $result : - $result;
	}
}
