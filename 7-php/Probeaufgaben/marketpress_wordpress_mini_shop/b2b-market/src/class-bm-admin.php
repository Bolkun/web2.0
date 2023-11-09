<?php

class BM_Admin {

	/**
	 * BM_Admin constructor.
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * hooks and includes for other classes
	 */
	public function init() {

		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'init', array( $this, 'add_special_groups' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'add_admin_assets' ) );

		if ( get_option( 'b2b_marketpress_notice_gm_atomion', 'on' ) != '1.0' ) {
			add_action( 'admin_notices', array( $this, 'marketpress_notices_gm_and_atomion' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'backend_script_market_press_notices' ) );
			add_action( 'wp_ajax_b2b_dismiss_marketprss_notice', array( $this, 'backend_script_market_press_dismiss_notices' ) );
		}

		require_once( B2B_PLUGIN_PATH . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'options' . DIRECTORY_SEPARATOR . 'class-bm-options.php' );
		require_once( B2B_PLUGIN_PATH . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'options' . DIRECTORY_SEPARATOR . 'class-bm-list-table.php' );
		require_once( B2B_PLUGIN_PATH . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'class-bm-helper.php' );
		require_once( B2B_PLUGIN_PATH . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'class-bm-user.php' );
		require_once( B2B_PLUGIN_PATH . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'class-bm-conditionals.php' );
		require_once( B2B_PLUGIN_PATH . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'class-bm-product-meta.php' );
		require_once( B2B_PLUGIN_PATH . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'class-bm-whitelist.php' );
		require_once( B2B_PLUGIN_PATH . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'class-bm-pricing-data.php' );
		require_once( B2B_PLUGIN_PATH . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'class-bm-calculation.php' );
		require_once( B2B_PLUGIN_PATH . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'class-bm-live-price.php' );
		require_once( B2B_PLUGIN_PATH . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'class-bm-tax.php' );
		require_once( B2B_PLUGIN_PATH . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'class-bm-public.php' );
		require_once( B2B_PLUGIN_PATH . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'class-bm-shortcode.php' );
		require_once( B2B_PLUGIN_PATH . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'class-bm-automatic-actions.php' );

		require_once( B2B_PLUGIN_PATH . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'hooks.php' );

		BM_Options::get_instance();
		$this->addons_init();

		add_action( 'init', 'BM_Live_Price::init_bm_live_price' );
		add_action( 'before_delete_post', array( $this, 'delete_related_postmeta' ) );

	}

	/**
	 * init for addons
	 */
	public function addons_init() {
		$addons = array(
			'bm_addon_shipping_and_payment',
			'bm_addon_import_and_export',
			'bm_addon_registration',
			'bm_addon_slack',
			'bm_addon_quantities',
		);

		foreach ( $addons as $addon ) {

			if ( 'bm_addon_shipping_and_payment' == $addon ) {
				if ( get_option( $addon ) == 'on' ) {
					require_once( B2B_ADDON_PATH . 'conditional-shipping-payment' . DIRECTORY_SEPARATOR . 'class-csp.php' );
				}
			}
			if ( 'bm_addon_import_and_export' == $addon ) {
				if ( get_option( $addon ) == 'on' ) {
					require_once( B2B_ADDON_PATH . 'import-export' . DIRECTORY_SEPARATOR . 'class-ie.php' );
				}
			}
			if ( 'bm_addon_registration' == $addon ) {
				if ( get_option( $addon ) == 'on' ) {
					require_once( B2B_ADDON_PATH . 'registration' . DIRECTORY_SEPARATOR . 'class-rgn.php' );
				}
			}
			if ( 'bm_addon_quantities' == $addon ) {
				if ( get_option( $addon ) == 'on' ) {
					require_once( B2B_ADDON_PATH . 'min-max-quantities' . DIRECTORY_SEPARATOR . 'class-bm-quantities.php' );
				}
			}
			if ( 'bm_addon_slack' == $addon ) {
				if ( get_option( $addon ) == 'on' ) {
					require_once( B2B_ADDON_PATH . 'slack-connector' . DIRECTORY_SEPARATOR . 'slack-connector.php' );
				}
			}
		}
	}


	/**
	 * handler for enqueue admin scripts
	 */
	public function add_admin_assets() {

		global $my_admin_page;
		$screen = get_current_screen();

		$min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : 'min.';

		if ( 'woocommerce_page_b2b-market' === $screen->base || 'post' === $screen->base && 'product' === $screen->post_type ) {

			wp_enqueue_style( 'select-woo-css', B2B_PLUGIN_URL . '/assets/admin/selectWoo.min.css', '1.0.2', 'all' );
			wp_enqueue_script( 'select-woo-js', B2B_PLUGIN_URL . '/assets/admin/selectWoo.full.min.js', array( 'jquery' ), '1.0.2', true );
			wp_enqueue_style( 'bm-admin', B2B_PLUGIN_URL . '/assets/admin/bm-admin.' . $min . 'css', '1.0.2', 'all' );
			wp_enqueue_script( 'beefup', B2B_PLUGIN_URL . '/assets/admin/jquery.beefup.min.js', array( 'jquery' ), '1.0.2', true );
			wp_enqueue_script( 'bm-admin', B2B_PLUGIN_URL . '/assets/admin/bm-admin.' . $min . 'js', array( 'jquery', 'beefup' ), '1.0.2', true );

			$current_groups = array();

			foreach ( BM_Conditionals::is_product_in_customer_groups( get_the_id() ) as $group ) {

				$group_object = get_post( $group );
				$group_slug   = $group_object->post_name;

				array_push( $current_groups, $group_slug );
			}

			$count_products  = wp_count_posts( 'product' );
			$group_admin_url = admin_url() . DIRECTORY_SEPARATOR . 'admin.php?page=b2b-market&tab=groups';

			if ( intval( $count_products->publish ) > 1000 ) {

				$autocomplete_data = array(
					'product_max'        => true,
					'categories'         => BM_Helper::get_available_categories(),
					'current_groups'     => $current_groups,
					'bulk_valid_message' => __( 'Please check the values for amount (from) and amount (to). There should be never the same value.', 'b2b-market' ),
					'admin_url'          => $group_admin_url,
					'nocache'            => get_option( 'bm_activate_no_cache' ),
				);
			} else {
				$autocomplete_data = array(
					'products'           => BM_Helper::get_available_products(),
					'categories'         => BM_Helper::get_available_categories(),
					'current_groups'     => $current_groups,
					'bulk_valid_message' => __( 'Please check the values for amount (from) and amount (to). There should be never the same value.', 'b2b-market' ),
					'admin_url'          => $group_admin_url,
					'nocache'            => get_option( 'bm_activate_no_cache' ),
				);
			}

			wp_localize_script( 'bm-admin', 'autocomplete_data', $autocomplete_data );
		}
	}

	/**
	 * register post type "customer_groups"
	 */
	public function register_post_type() {
		$labels = array(
			'name'               => _x( 'Customer Groups', 'post type general name', 'b2b-market' ),
			'singular_name'      => _x( 'Customer Group', 'post type singular name', 'b2b-market' ),
			'menu_name'          => _x( 'Customer Groups', 'admin menu', 'b2b-market' ),
			'name_admin_bar'     => _x( 'Customer Group', 'add new on admin bar', 'b2b-market' ),
			'add_new'            => _x( 'Add New', 'b2b-market' ),
			'add_new_item'       => __( 'Add New Customer Group', 'b2b-market' ),
			'new_item'           => __( 'New Customer Group', 'b2b-market' ),
			'edit_item'          => __( 'Edit Customer Group', 'b2b-market' ),
			'view_item'          => __( 'View Customer Group', 'b2b-market' ),
			'all_items'          => __( 'All Customer Groups', 'b2b-market' ),
			'search_items'       => __( 'Search Customer Groups', 'b2b-market' ),
			'parent_item_colon'  => __( 'Parent Customer Group', 'b2b-market' ),
			'not_found'          => __( 'No Customer Groups found.', 'b2b-market' ),
			'not_found_in_trash' => __( 'No Customer Groups found in Trash.', 'b2b-market' ),
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Description.', 'b2b-market' ),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => false,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'customer_groups' ),
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title' ),
		);

		register_post_type( 'customer_groups', $args );
	}

	/**
	 * Add special groups initially
	 *
	 * @return void
	 */
	public function add_special_groups() {

		// expecting there is no special group.
		$guest_group    = false;
		$customer_group = false;

		$args = array(
			'post_type' => 'customer_groups',
		);

		$groups = get_posts( $args );

		foreach ( $groups as $group ) {

			$possible_guest_group_names    = array( 'Gast', 'Gäste', 'Guest', 'Guests', 'gast', 'gäste', 'guest', 'guests' );
			$possible_customer_group_names = array( 'Kunde', 'Kunden', 'Customer', 'Customers', 'customer', 'kunde', 'kunden', 'customers' );

			if ( in_array( $group->post_title, $possible_guest_group_names ) ) {
				$guest_group = true;
				update_option( 'bm_guest_group', $group->ID );
			}
			if ( in_array( $group->post_title, $possible_customer_group_names ) ) {
				$customer_group = true;
				update_option( 'bm_customer_group', $group->ID );
			}
		}

		if ( ! $guest_group ) {
			$args = array(
				'post_title'   => __( 'Gast', 'b2b-market' ),
				'post_name'    => 'guest',
				'post_type'    => 'customer_groups',
				'post_content' => '',
				'post_status'  => 'publish',
			);
			$guest = wp_insert_post( $args );
			update_option( 'bm_guest_group', $guest );
		}
		if ( ! $customer_group ) {
			$args = array(
				'post_title'   => __( 'Kunde', 'b2b-market' ),
				'post_name'    => 'customer',
				'post_type'    => 'customer_groups',
				'post_content' => '',
				'post_status'  => 'publish',
			);
			$customer = wp_insert_post( $args );
			update_option( 'bm_customer_group', $customer );
		}

	}

	/**
	 * Add Admin notices German Market and B2B Market
	 *
	 * @wp-hook 	admin_notices
	 * @return 		void
	 */
	public function marketpress_notices_gm_and_atomion() {

		$gm_exists      = false;
		$atomion_exists = false;

		if ( ! function_exists( 'is_plugin_inactive' ) ) {
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		}

		if ( is_plugin_inactive( 'woocommerce-german-market/woocommerce-german-market.php' ) && ( is_dir( WP_PLUGIN_DIR . '/woocommerce-german-market' ) ) ) {
			$gm_exists = true;
		} elseif ( class_exists( 'Woocommerce_German_Market' ) ) {
			$gm_exists = true;
		}

		if ( is_dir( WP_CONTENT_DIR . '/themes/wordpress-theme-atomion' ) ) {
			$atomion_exists = true;
		} elseif ( function_exists( 'atomion_setup' ) ) {
			return;
			$atomion_exists = true;
		}

		if ( $atomion_exists ) {
			return;
		}

		$text = '';

		if ( ( ! $gm_exists ) && ( ! $atomion_exists ) ) {

			$text = sprintf(
				__( 'You use our plugin <strong>B2B Market</strong>. That\'s great! Take a look at the plugin <strong>%s</strong> and the theme <strong>%s</strong>, they fit perfectly.', 'b2b-market' ),
				'<a href="https://marketpress.de/shop/plugins/woocommerce-german-market/?mp-notice-from=b2b" target="_blank">German Market</a>',
				'<a href="https://marketpress.de/shop/themes/wordpress-theme-atomion/?mp-notice-from=b2b" target="_blank">Atomion</a>'
			);
		} elseif ( ! $gm_exists ) {
			$text = sprintf(
				__( 'You use our plugin <strong>B2B Market</strong>. That\'s great! Take a look at the plugin <strong>%s</strong>, it fits perfectly.', 'b2b-market' ),
				'<a href="https://marketpress.de/shop/plugins/woocommerce-german-market/?mp-notice-from=b2b" target="_blank">German Market</a>'
			);
		} elseif ( ! $atomion_exists ) {
			$text = sprintf(
				__( 'You use our plugin <strong>B2B Market</strong>. That\'s great! Take a look at the theme <strong>%s</strong>, it fits perfectly.', 'b2b-market' ),
				'<a href="https://marketpress.de/shop/themes/wordpress-theme-atomion/?mp-notice-from=b2b" target="_blank">Atomion</a>'
			);
		}

		if ( ! empty( $text ) ) {
			?>
			<div class="notice notice-warning is-dismissible marketpress-atomion-gm-b2b-notice-in-b2b">
				<p><?php echo $text; ?></p>
			</div>
			<?php
		}
	}

	/**
	* Load JavaScript so you can dismiss the MarketPress Plugin Notice
	*
	* @wp-hook admin_enqueue_scripts
	* @return void
	*/
	function backend_script_market_press_notices() {
		$min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : 'min.';
		wp_enqueue_script( 'b2b-marketpress-notices', B2B_PLUGIN_URL . '/assets/admin/backend-marketpress-notices.' . $min . 'js', array( 'jquery' ), wp_get_theme()->get( 'Version' ) );
	    wp_localize_script( 'b2b-marketpress-notices', 'b2b_marketpress_ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
	}

	/**
	* Dismiss MarketPress Notice
	*
	* @wp-hook wp_ajax_atomion_dismiss_marketprss_notice
	* @return void
	*/
	function backend_script_market_press_dismiss_notices() {
		update_option( 'b2b_marketpress_notice_gm_atomion', '1.0' );
	    exit();
	}


	public function delete_related_postmeta( $postid ) {

		global $post_type;

		if ( $post_type != 'customer_groups' ) {
			return;
		}

		$group  = get_post( $postid );
		$prefix = 'bm_' . $group->post_name;

		global $wpdb;

		$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE '{$prefix}%'" );
	}

}

new BM_Admin();
