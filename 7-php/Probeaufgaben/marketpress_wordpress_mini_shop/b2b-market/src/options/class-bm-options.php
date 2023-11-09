<?php

class BM_Options {

	/**
	 * @var BM_Options
	 */
	private static $instance = null;

	/**
	 * @var String
	 */
	private $current_screen_id = null;

	/**
	 * Singletone get_instance
	 *
	 * @static
	 * @return BM_Options
	 */
	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new BM_Options();
		}

		return self::$instance;
	}

	/**
	 * Singletone constructor
	 *
	 * @access private
	 */
	private function __construct() {

		// add submenu
		add_action( 'admin_menu', array( $this, 'add_bm_submenu' ), 51 );

		// enqueue woocommerce admin styles
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts_and_styles' ) );

		// our checkbox
		add_action( 'woocommerce_admin_field_bm_ui_checkbox', array( $this, 'bm_ui_checkbox' ) );

		// our repeatables
		add_action( 'woocommerce_admin_field_bm_repeatable', array( $this, 'bm_repeatable' ) );

		add_filter( 'woocommerce_admin_settings_sanitize_option', array(
			$this,
			'woocommerce_admin_settings_sanitize_option',
		), 10, 3 );

		// let other add actions or remove our actions
		do_action( 'bm_ui_after_actions', $this );

	}

	/**
	 * Add submenu
	 *
	 * @wp-hook admin_enqueue_scripts
	 * @access public
	 * @return void
	 */
	public function enqueue_scripts_and_styles() {

		// load only for German Market Backend Menu
		$current_screen = get_current_screen();

		if ( $current_screen->id == $this->current_screen_id ) {

			$script_debug = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG;
			$suffix       = $script_debug ? '' : '.min';

			wp_enqueue_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION );
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'woocommerce_settings', WC()->plugin_url() . '/assets/js/admin/settings' . $suffix . '.js', array(
				'jquery',
				'jquery-ui-datepicker',
				'jquery-ui-sortable',
				'iris',
				'select2',
			), WC()->version, true );

			// WooCommerce scripts for select2
			wp_register_script( 'select2', WC()->plugin_url() . '/assets/js/select2/select2' . $suffix . '.js', array( 'jquery' ), '3.5.4' );
			wp_register_script( 'wc-enhanced-select', WC()->plugin_url() . '/assets/js/admin/wc-enhanced-select' . $suffix . '.js', array(
				'jquery',
				'select2',
			), WC_VERSION );
			wp_localize_script( 'wc-enhanced-select', 'wc_enhanced_select_params', array(
				'i18n_matches_1'            => _x( 'One result is available, press enter to select it.', 'enhanced select', 'woocommerce' ),
				'i18n_matches_n'            => _x( '%qty% results are available, use up and down arrow keys to navigate.', 'enhanced select', 'woocommerce' ),
				'i18n_no_matches'           => _x( 'No matches found', 'enhanced select', 'woocommerce' ),
				'i18n_ajax_error'           => _x( 'Loading failed', 'enhanced select', 'woocommerce' ),
				'i18n_input_too_short_1'    => _x( 'Please enter 1 or more characters', 'enhanced select', 'woocommerce' ),
				'i18n_input_too_short_n'    => _x( 'Please enter %qty% or more characters', 'enhanced select', 'woocommerce' ),
				'i18n_input_too_long_1'     => _x( 'Please delete 1 character', 'enhanced select', 'woocommerce' ),
				'i18n_input_too_long_n'     => _x( 'Please delete %qty% characters', 'enhanced select', 'woocommerce' ),
				'i18n_selection_too_long_1' => _x( 'You can only select 1 item', 'enhanced select', 'woocommerce' ),
				'i18n_selection_too_long_n' => _x( 'You can only select %qty% items', 'enhanced select', 'woocommerce' ),
				'i18n_load_more'            => _x( 'Loading more results&hellip;', 'enhanced select', 'woocommerce' ),
				'i18n_searching'            => _x( 'Searching&hellip;', 'enhanced select', 'woocommerce' ),
				'ajax_url'                  => admin_url( 'admin-ajax.php' ),
				'search_products_nonce'     => wp_create_nonce( 'search-products' ),
				'search_customers_nonce'    => wp_create_nonce( 'search-customers' ),
			) );

			wp_enqueue_script( 'wc-enhanced-select' );

			// WooCommerce styles for tool tips
			wp_enqueue_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION );

			// Media Uploader
			wp_enqueue_script( 'media-upload' );
			wp_enqueue_script( 'thickbox' );
			wp_enqueue_style( 'thickbox' );

		}
	}

	/**
	 * Add submenu
	 *
	 * @wp-hook admin_menu
	 * @access public
	 */
	public function add_bm_submenu() {

		$submenu_page = add_submenu_page(
			'woocommerce',
			__( 'B2B Market', 'b2b-market' ),
			__( 'B2B Market', 'b2b-market' ),
			apply_filters( 'b2b_ui_capability', 'manage_woocommerce' ),
			'b2b-market',
			array( $this, 'render_bm_menu' )
		);

		$this->current_screen_id = $submenu_page;

	}

	/**
	 * Output type bm_repeater_fields
	 *
	 * @access public
	 * @return void
	 */
	public function bm_repeatable( $value ) {

		$bulk_prices = get_option( 'bm_global_bulk_prices' );

	

		$options = array(
			__( 'Fix Price', 'b2b-market' )              => 'fix',
			__( 'Discount (fixed Value)', 'b2b-market' ) => 'discount',
			__( 'Discount (%)', 'b2b-market' )           => 'discount-percent',
		);

		?>
		<table class="form-table">
			<div id="bm-bulkprices-inner">
			<label class="titledesc"><?php _e( 'Bulk Prices', 'b2b-market' ); ?></label>
				<p><?php _e( 'Bulk prices are applied if the current quantity fits in the configured quantity range ', 'b2b-market' ); ?></p>
			<?php

			/* filled with existing data */
			$counter = 0;
			if ( isset( $bulk_prices ) && ! empty( $bulk_prices ) ) {
				if ( count( $bulk_prices ) > 0 ) {
					foreach ( $bulk_prices as $price ) {

						if ( isset( $price['bulk_price_type'] ) ) {
							$selected = $price['bulk_price_type'];
						} else {
							$selected = 'fix';
						}

						if ( isset( $price['bulk_price'] ) || isset( $price['bulk_price_from'] ) || isset( $price['bulk_price_to'] ) ) {
							?>
							<p>
								<label for="bulk_price[<?php echo $counter; ?>][bulk_price]"><?php _e( 'Bulk Price', 'b2b-market' ); ?></label>
								<input type="number" step="0.001" min="0" name="bulk_price[<?php echo $counter; ?>][bulk_price]" value="<?php echo $price['bulk_price']; ?>" />

								<label for="bulk_price[<?php echo $counter; ?>][bulk_price_from]"><?php _e( 'Amount (from)', 'b2b-market' ); ?></label>
								<input type="number" step="1" min="0" name="bulk_price[<?php echo $counter; ?>][bulk_price_from]" value="<?php echo $price['bulk_price_from']; ?>" />

								<label for="bulk_price[<?php echo $counter; ?>][bulk_price_to]"><?php _e( 'Amount (to)', 'b2b-market' ); ?></label>
								<input type="number" step="1" min="0" name="bulk_price[<?php echo $counter; ?>][bulk_price_to]" value="<?php if ( esc_attr( $price['bulk_price_to'] ) != 0 ) { echo $price['bulk_price_to']; } ?>" <?php if ( isset( $price['bulk_price_to'] ) && esc_attr( $price['bulk_price_to'] ) == 0) { echo 'placeholder="∞"'; } ?> />

								<label for="bulk_price[<?php echo $counter; ?>][bulk_price_type]"><?php _e( 'Price-Type', 'b2b-market' ); ?></label>
								<select id="bulk_price_type" name="bulk_price[<?php echo $counter; ?>][bulk_price_type]" class="bulk_price_type">
								<?php
								if ( isset( $options ) ) : 
									foreach ( $options as $label => $value ) : ?>
									<option value="<?php echo $value; ?>"<?php selected( $selected, $value ); ?>><?php echo $label; ?></option>
									<?php endforeach; ?>
								<?php endif; ?>
								</select>

								<span class="button remove"><?php _e( 'Remove', 'b2b-market' ); ?></span>
							</p>
							<?php
							$counter++;
						}
					}
				}
			}
			?>
			<div class="new-bulk">
				<span id="here"></span>
				<span class="button add"><?php _e( 'Add', 'b2b-market' ); ?></span>
			</div>
			<script>
				var $ =jQuery.noConflict();
				$(document).ready(function() {
					var count = <?php echo $counter; ?>;

					var bulk_price_label = '<?php _e( "Bulk Price", "b2b-market" ); ?>';
					var bulk_price_from_label = '<?php _e( "Amount (from)", "b2b-market" ); ?>';
					var bulk_price_to_label = '<?php _e( "Amount (to)", "b2b-market" ); ?>';
					var bulk_price_type_label = '<?php _e( "Price-Type", "b2b-market" ); ?>';

					var bulk_price_type_fix_label = '<?php _e( "Fix Price", "b2b-market" ); ?>';
					var bulk_price_type_discount_label = '<?php _e( "Discount (fixed Value)", "b2b-market" ); ?>';
					var bulk_price_type_discount_percent_label = '<?php _e( "Discount (%)", "b2b-market" ); ?>';
					var bulk_price_remove_label = '<?php _e( "Remove", "b2b-market" ); ?>';

					$(".add").click(function() {
						count = count + 1;
						var content = '<p><label for="bulk_price[' + count + '][bulk_price]">' + bulk_price_label + '</label><input type="number" step="0.001" min="0" name="bulk_price[' + count + '][bulk_price]" value="" />' +
						'<label for="bulk_price[' + count + '][bulk_price_from]">' + bulk_price_from_label + '</label><input type="number" step="1" min="0" name="bulk_price[' + count + '][bulk_price_from]" value="" />' +
						'<label for="bulk_price[' + count + '][bulk_price_to]">' + bulk_price_to_label + '</label><input type="number" step="1" min="0" name="bulk_price[' + count + '][bulk_price_to]" value="" />' +
						'<label for="bulk_price[' + count + '][bulk_price_type]">' + bulk_price_type_label + '</label>' +
						'<select id="bulk_price_type" name="bulk_price[' + count + '][bulk_price_type]" class="bulk_price_type">' +
						'<option value="fix">' + bulk_price_type_fix_label + '</option>' +
						'<option value="discount">' + bulk_price_type_discount_label + '</option>' +
						'<option value="discount-percent">' + bulk_price_type_discount_percent_label + '</option>' +
						'</select>' +
						'<span class="button remove">' + bulk_price_remove_label + '</span>' + 
						'</p>';

						$('#here').append(content);

						return false;
					});
					$(".remove").live('click', function() {
						$(this).parent().remove();
					});
				});
				</script>
			</div>
		</table>
		
		<?php
	}

	/**
	 * Output type wgm_ui_checkbox
	 *
	 * @access public
	 * @hook woocommerce_admin_field_wgm_ui_checkbox
	 * @return void
	 */
	public function bm_ui_checkbox( $value ) {

	$option_value    = WC_Admin_Settings::get_option( $value['id'], $value['default'] );

		// Description handling
		$field_description = WC_Admin_Settings::get_field_description( $value );
		extract( $field_description );

		$visbility_class = array();

		if ( ! isset( $value['hide_if_checked'] ) ) {
			$value['hide_if_checked'] = false;
		}
		if ( ! isset( $value['show_if_checked'] ) ) {
			$value['show_if_checked'] = false;
		}
		if ( 'yes' == $value['hide_if_checked'] || 'yes' == $value['show_if_checked'] ) {
			$visbility_class[] = 'hidden_option';
		}
		if ( 'option' == $value['hide_if_checked'] ) {
			$visbility_class[] = 'hide_options_if_checked';
		}
		if ( 'option' == $value['show_if_checked'] ) {
			$visbility_class[] = 'show_options_if_checked';
		}

		?>
			<tr valign="top" class="<?php echo esc_attr( implode( ' ', $visbility_class ) ); ?>">
				<th scope="row" class="titledesc"><?php echo esc_html( $value['title'] ) ?><?php echo $tooltip_html; ?></th>
				<td class="forminp forminp-checkbox">
					<fieldset>
		<?php

		if ( ! empty( $value['title'] ) ) {
			?>
				<legend class="screen-reader-text"><span><?php echo esc_html( $value['title'] ) ?></span></legend>
			<?php
		}
		
		?>
			
			<label class="switch" for="<?php echo $value['id'] ?>">
				<input
					name="<?php echo esc_attr( $value['id'] ); ?>"
					id="<?php echo esc_attr( $value['id'] ); ?>"
					type="checkbox"
					class="<?php echo esc_attr( isset( $value['class'] ) ? $value['class'] : '' ); ?>"
					value="on"
					<?php checked( $option_value, 'on' ); ?>

				/>
				<div class="slider round bm-slider"></div>

			</label> 
			
			<?php
				$off_active = $option_value == 'off' ? 'active' : 'clickable';
				$on_active  = $option_value == 'on' ? 'active' : 'clickable';
			?>
			<p class="screen-reader-buttons">
				<span class="bm-ui-checkbox switcher off <?php echo $off_active; ?>"><?php echo __( 'Off', 'b2b-market' ); ?></span>
				<span class="bm-ui-checkbox delimter">|</span>
				<span class="bm-ui-checkbox switcher on <?php echo $on_active; ?>"><?php echo __( 'On', 'b2b-market' ); ?></span>
			</p>

			<?php
				if ( isset( $value[ 'desc' ] ) && $value[ 'desc' ] != '' ) {
					?><br /><span class="description"><?php echo $value[ 'desc' ]; ?></span><?php
				}
			?>
		<?php

		if ( ! isset( $value['checkboxgroup'] ) || 'end' == $value['checkboxgroup'] ) {
						?>
						</fieldset>
					</td>
				</tr>
			<?php
		} else {
			?>
				</fieldset>
			<?php
		}
	}

	/**
	 * Save type wgm_ui_checkbox
	 *
	 * @access public
	 * @hook woocommerce_admin_settings_sanitize_option
	 *
	 * @param Mixed $value
	 * @param Array $option
	 * @param Mixed $raw_value
	 *
	 * @return $value
	 */
	public function woocommerce_admin_settings_sanitize_option( $value, $option, $raw_value ) {

		if ( 'bm_ui_checkbox' == $option['type'] ) {
			$value = is_null( $raw_value ) ? 'off' : 'on';
		}

		if ( 'bm_repeatable' == $option['type'] ) {

			/* bulk meta */

			if ( isset( $_POST['bulk_price'] ) && ! empty( $_POST['bulk_price'] ) ) {
				$bulk_prices = $_POST['bulk_price'];
			}

			if ( isset( $bulk_prices ) && ! empty( $bulk_prices ) ) {
				update_option( 'bm_global_bulk_prices', $bulk_prices );
			} else {
				delete_option( 'bm_global_bulk_prices' );
			}
		}

		return $value;
	}

	/**
	 * Get left menu items
	 *
	 * @access private
	 * @return array
	 */
	private function get_left_menu_items() {

		$groups = array(
			'title'    => __( 'Customer Groups', 'b2b-market' ),
			'slug'     => 'groups',
			'callback' => array( $this, 'groups_tab' ),
		);

		$groups = apply_filters( 'woocommerce_bm_ui_menu_b2b_market', $groups );

		$all_users = array(
			'title'    => __( 'All Customers', 'b2b-market' ),
			'slug'     => 'global',
			'options'  => 'yes',
			'submenu'  => array(
				array(
					'title'    => __( 'All Customers', 'b2b-market' ),
					'slug'     => 'global',
					'callback' => array( $this, 'global_tab' ),
					'options'  => 'yes',
				),
			),
		);

		$all_users = apply_filters( 'woocommerce_bm_ui_menu_b2b_market', $all_users );

		$options = array(
			'title'    => __( 'Options', 'b2b-market' ),
			'slug'     => 'options',
			'options'  => 'yes',
			'submenu'  => array(
				array(
					'title'    => __( 'General', 'b2b-market' ),
					'slug'     => 'misc',
					'callback' => array( $this, 'misc_tab' ),
					'options'  => 'yes',
				),
				array(
					'title'    => __( 'Administration', 'b2b-market' ),
					'slug'     => 'administration',
					'callback' => array( $this, 'admin_tab' ),
					'options'  => 'yes',
				),
			),
		);

		$options = apply_filters( 'woocommerce_bm_ui_menu_b2b_market', $options );

		$add_ons = array(
			'title'    => __( 'Add-ons', 'b2b-market' ),
			'slug'     => 'add-ons',
			'new'      => 'yes',
			'callback' => array( $this, 'render_add_ons' ),
		);

		$add_ons = apply_filters( 'woocommerce_bm_ui_menu_add_ons', $add_ons );

		$items = array(
			0   => $all_users,
			1   => $groups,
			500 => $add_ons,
			1000 => $options,
		);

		$items = apply_filters( 'woocommerce_bm_ui_left_menu_items', $items );
		ksort( $items );

		return $items;
	}

	/**
	 * Add Submenu to WooCommerce Menu
	 *
	 * @add_submenu_page
	 * @access public
	 */
	public function render_bm_menu() {

		do_action( 'render_bm_menu_save_options' );

		?>
		<div class="wrap">
			<div class='b2b-market'>
				<div class="b2b-market-left-menu">
					<div class="logo"></div>
						<div class="mobile-menu-outer">
							<div class="mobile-menu-button">
								<div class="txt"><?php echo __( 'Menu', 'b2b-market' ); ?></div>
									<div class="mobile-icon">
										<span></span>
										<span></span>
										<span></span>
										<span></span>
									</div>
							</div>
						</div>
						<ul>

						<?php

						$page_url = get_admin_url() . 'admin.php?page=b2b-market';

						$left_menu_items = $this->get_left_menu_items();

						$i = 0;

						foreach ( $left_menu_items as $item ) {

							$i ++;

							$classes = array();

							// slug
							$classes[] = $item['slug'];

							// current tab
							if ( isset( $_GET['tab'] ) ) {

								if ( $_GET['tab'] == $item['slug'] ) {
									$classes[] = 'current';
									$current   = $item;
								}
							} else {

								if ( 1 == $i ) {
									$classes[] = 'current'; // if tab is not set, first item is current
									$current   = $item;
								}
							}

							// new
							if ( isset( $item['new'] ) && $item['new'] ) {
								$classes[] = 'new';
							}

							// info
							if ( isset( $item['info'] ) && $item['info'] ) {
								$classes[] = 'info';
							}

							$classes      = apply_filters( 'woocommerce_de_ui_left_menu_item_class', $classes, $item );
							$class_string = implode( ' ', $classes );

							?>
							<li class="<?php echo $class_string; ?>">
								<a href="<?php echo $page_url . '&tab=' . $item['slug']; ?>"
								title="<?php echo esc_attr( $item['title'] ); ?>"><?php echo $item['title']; ?></a>
							</li>
						<?php } ?>
						</ul>
					</div>
					<div class="b2b-market-main-menu">
					<?php $this->render_content( $current ); ?>
					</div>
				</div>
			</div>
		<?php
	}

	/**
	 * Render B2B Market Tab
	 *
	 * @access private
	 * @return array
	 */
	private function render_content( $item ) {

		$callback = isset( $item['callback'] ) ? $item['callback'] : '';
		$page_url = get_admin_url() . 'admin.php?page=b2b-market&tab=' . $item['slug'];
		$current  = $item;

		?>
		<h1><?php echo $item['title']; ?></h1>
		<?php

		do_action( 'woocommerce_de_ui_after_title', $item );

		// submenu
		if ( isset( $item['submenu'] ) ) {

			$submenu = $item['submenu'];
			$classes = array();

			?>
		<ul class="submenu">
			<?php
			$i = 0;

			foreach ( $submenu as $sub_item ) {

				$i ++;

				$classes = array();

				// current sub tab
				if ( isset( $_GET['sub_tab'] ) ) {

					if ( $_GET['sub_tab'] == $sub_item['slug'] ) {
						$classes[] = 'current';
						$current   = $sub_item;
						$callback  = isset( $sub_item['callback'] ) ? $sub_item['callback'] : $callback;
					}
				} else {

					if ( 1 == $i ) {
						$classes[] = 'current'; // if tab is not set, first item is current
						$current   = $sub_item;
						$callback  = isset( $sub_item['callback'] ) ? $sub_item['callback'] : $callback;
					}
				}

				$classes      = apply_filters( 'woocommerce_de_ui_sub_menu_item_class', $classes, $sub_item );
				$class_string = implode( ' ', $classes );
				?>
			<li class="<?php echo $class_string; ?>">
				<a href="<?php echo $page_url . '&sub_tab=' . $sub_item['slug']; ?>"
				title="<?php echo esc_attr( $sub_item['title'] ); ?>"><?php echo $sub_item['title']; ?></a>
			</li>
			<?php } ?>
		</ul>
			<?php
			do_action( 'woocommerce_de_ui_after_submenu', $item );
		}

		do_action( 'woocommerce_de_ui_before_callback', $callback );

		$is_option_page = isset( $current['options'] );

		// callback
		if ( isset( $callback ) ) {

			if ( ( is_array( $callback ) && method_exists( $callback[0], $callback[1] ) ) || ( ! ( is_array( $callback ) ) && function_exists( $callback ) ) ) {

				if ( $is_option_page ) {

					$options = call_user_func( $callback );

					// save settings
					if ( isset( $_POST['submit_save_bm_options'] ) ) {

						if ( ! wp_verify_nonce( $_POST['update_bm_settings'], 'woocommerce_de_update_bm_settings' ) ) {

							?>
					<div class="notice-bm notice-error">
						<p><?php echo __( 'Sorry, but something went wrong while saving your settings. Please, try again.', 'b2b-market' ); ?></p>
					</div>
							<?php

						} else {

							woocommerce_update_options( $options );

							/* save time for last options save */
							update_option( 'bm_all_options_saved', date( 'Y-m-d-H-i' ) );
							BM_Helper::delete_b2b_transients();

							do_action( 'woocommerce_bm_ui_update_options', $options );

							?>
					<div class="notice-bm notice-success">
						<p><?php echo __( 'Your settings have been saved.', 'b2b-market' ); ?></p>
					</div>
					<?php } } ?>
					<form method="post">
					<?php

					$this->save_button( 'top' );

					wp_nonce_field( 'woocommerce_de_update_bm_settings', 'update_bm_settings' );
					woocommerce_admin_fields( $options );

					$this->save_button( 'bottom' );

					/* add hook for custom actions with button displays on bottom of tab */
					do_action( 'woocommerce_bm_ui_after_save_button' );

					?>
					</form>
					<?php
				} else {
					call_user_func( $callback );
				}
			}
		}

		do_action( 'woocommerce_de_ui_after_callback', $callback );

	}

	/**
	 * Render Options for global
	 *
	 * @return void
	 */
	public function groups_tab() {
		$list_table = new BM_ListTable();
		$list_table->prepare_items();

		?>

		<div class="wrap b2b-group-table">
			<form id="groups-filter" method="get">
				<input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>"/>
				<?php $list_table->display(); ?>
				<div class="alignright">
					<a href="" class="button action new-group"><?php _e( 'Add new Customer Group', 'b2b-market' ); ?></a>
				</div>
			</form>
		</div>
		<?php
		require_once( B2B_PLUGIN_PATH . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'options' . DIRECTORY_SEPARATOR . 'class-bm-edit-group.php' );

	}

	/**
	 * Render Options for global
	 *
	 * @access public
	 * @return array
	 */
	public function global_tab() {

		$locale      = get_locale();
		$is_de       = ( stripos( $locale, 'de' ) === 0 ) ? true : false;
		$support_url = $is_de ? 'https://marketpress.de/hilfe/' : 'https://marketpress.com/help/';

		$price_types = array(
			'fix'              => __( 'Fixed Price', 'b2b-market' ),
			'discount'         => __( 'Discount (fixed Value)', 'b2b-market' ),
			'discount-percent' => __( 'Discount (%)', 'b2b-market' ),
		);

		$options = array(

			array(
				'name' => __( 'Prices (All Customers)', 'b2b-market' ),
				'type' => 'title',
				'id'   => 'bm_global_price_title',
				'desc' => __( 'All prices which are defined here are valid for all customers including guests and all members of all customer groups.<br> B2B Market checks for the cheapest price to apply for the current customer.', 'b2b-market' ),
			),
			array(
				'name' => __( 'Base Price', 'b2b-market' ),
				'id'   => 'bm_global_base_price',
				'type' => 'number',
				'custom_attributes' => array( 'min' => '0', 'step' => '0.01' ),
			),
			array(
				'name'    => __( 'Base Price Type', 'b2b-market' ),
				'id'      => 'bm_global_base_price_type',
				'type'    => 'select',
				'default' => 1,
				'options' => $price_types,
			),
			array(
				'name' => _x( 'Bulk Prices', 'b2b-market' ),
				'id'   => 'bm_global_bulk_prices',
				'type' => 'bm_repeatable',
			),

			array(
				'type' => 'sectionend',
				'id'   => 'bm_global_end',
			),
		);

		$options = apply_filters( 'bm_de_ui_options_global', $options );

		return $options;
	}
	/**
	 * Render Options for misc
	 *
	 * @access public
	 * @return array
	 */
	public function misc_tab() {

		$locale      = get_locale();
		$is_de       = ( stripos( $locale, 'de' ) === 0 ) ? true : false;
		$support_url = $is_de ? 'https://marketpress.de/hilfe/' : 'https://marketpress.com/help/';

		$options = array(

			array(
				'name' => __( 'Compatibility', 'b2b-market' ),
				'type' => 'title',
				'id'   => 'bm_global_compatiblity_title',
			),
			array(
				'name'     => __( 'Deactivate Whitelist Function', 'b2b-market' ),
				'id'       => 'deactivate_whitelist_hooks',
				'type'     => 'bm_ui_checkbox',
				'default'  => 'off',
			),
			array(
				'type' => 'sectionend',
				'id'   => 'b2b_global_end',
			),
			array(
				'name' => __( 'Discount Message', 'b2b-market' ),
				'type' => 'title',
				'id'   => 'bm_global_discount_message_title',
			),
			array(
				'name'              => __( 'If global discount activated you can display a custom discount banner in your shop header.', 'b2b-market' ),
				'id'                => 'bm_global_discount_message',
				'type'              => 'textarea',
				'custom_attributes' => array( 'rows' => '10', 'cols' => '80' ),
				'default'           => '',
				'args'              => '',
			),
			array(
				'name'    => __( 'Background-Color', 'b2b-market' ),
				'id'      => 'bm_global_discount_message_background_color',
				'type'    => 'color',
				'default' => '#2fac66',
				'css'     => 'width: 100px;',
			),
			array(
				'name'    => __( 'Font-Color', 'b2b-market' ),
				'id'      => 'bm_global_discount_message_font_color',
				'type'    => 'color',
				'default' => '#FFFFFF',
				'css'     => 'width: 100px;',
			),

			array(
				'type' => 'sectionend',
				'id'   => 'b2b_global_end',
			),
		);

		$options = apply_filters( 'woocommerce_de_ui_options_global', $options );

		return $options;
	}

	/**
	 * Render options for Administration
	 *
	 * @return array
	 */
	public function admin_tab() {

		$locale      = get_locale();
		$is_de       = ( stripos( $locale, 'de' ) === 0 ) ? true : false;
		$support_url = $is_de ? 'https://marketpress.de/hilfe/' : 'https://marketpress.com/help/';

		$options = array(

			array(
				'name' => __( 'Administration', 'b2b-market' ),
				'type' => 'title',
				'id'   => 'bm_administration_title',
			),
			array(
				'name'     => __( 'Activate No-Cache Mode in Admin', 'b2b-market' ),
				'id'       => 'bm_activate_no_cache',
				'type'     => 'bm_ui_checkbox',
				'default'  => 'off',
			),
			array(
				'type' => 'sectionend',
				'id'   => 'b2b_administration_end',
			),
		);

		$options = apply_filters( 'woocommerce_de_ui_options_global', $options );

		return $options;
	}

	/**
	 * Render Add-On Tab
	 *
	 * @access public
	 * @return void
	 */
	public static function render_add_ons() {

		// Init
		$add_ons = self::get_addons();

		// Update Options
		if ( isset( $_POST['update_add_ons'] ) ) {

			if ( ! wp_verify_nonce( $_POST['update_add_ons'], 'woocommerce_bm_update_add_ons' ) ) {

				?>
				<div class="notice notice-error">
					<p><?php echo __( 'Sorry, but something went wrong while saving your settings. Please, try again.', 'b2b-market' ); ?></p>
				</div>
				<?php

			} else {

				foreach ( $add_ons as $add_on ) {

					if ( isset( $_POST[ $add_on['id'] ] ) ) {
						$current_activation = $add_on['on-off'];
						$new_activation     = $current_activation == 'on' ? 'off' : 'on';
						update_option( $add_on['id'], $new_activation );
					}
				}

				// Do a little trick (add-ons are activated after second reload)
				wp_safe_redirect( get_admin_url() . 'admin.php?page=b2b-market&tab=add-ons&updated_bm_add_ons=' . time() );
				exit();
			}
		}

		// Show notice when settings have been saved
		if ( isset( $_REQUEST['updated_bm_add_ons'] ) ) {

			// If someone reloads the page, the message should not be shown
			if ( intval( $_REQUEST['updated_bm_add_ons'] ) + 1 >= time() ) {

				?>
				<div class="notice notice-success">
					<p><?php echo __( 'Your settings have been saved.', 'b2b-market' ); ?></p>
				</div>
				<?php
			}
		}

		?>
		<form method="post">
		<?php wp_nonce_field( 'woocommerce_bm_update_add_ons', 'update_add_ons' ); ?>
			<div class="add-ons">
				<div class="description"></div>
				<?php

				foreach ( $add_ons as $add_on ) {

					?>
				<div class="add-on-box <?php echo $add_on['on-off']; ?>">
					<div class="icon logo-box">
						<?php if ( $add_on['image'] != '' ) { ?>
							<img src="<?php echo $add_on['image']; ?>" alt="logo"/>
							<?php } elseif ( '' != $add_on['dashicon'] ) { ?>
								<span class="dashicons dashicons-<?php echo $add_on['dashicon']; ?>"></span>
							<?php } else { ?>
								<span class="dashicons dashicons-admin-generic"></span>
							<?php } ?>
					</div>
					<div class="on-off-box">
						<label class="switch">
						<?php

						if ( $add_on['on-off'] == 'on' ) { ?>
							<input type="submit" class="add-on-switcher on"name="<?php echo $add_on['id']; ?>"
							value="" />
						<div class="slider round"></div>
						<?php
						} elseif ( 'off' == $add_on['on-off'] ) { ?>
							<input type="submit" class="add-on-switcher off"
							name="<?php echo $add_on['id']; ?>" value="" />
							<div class="slider round"></div>
							<?php } ?>
						</label>
					</div>
					<span style="clear: both; display: block;"></span>
					<div class="title">
					<?php echo $add_on['title']; ?>
					</div>
					<div class="description">
					<?php echo $add_on['description']; ?>
					</div>
				</div>
				<?php } ?>
			</div>
		</form>
		<?php
	}

	/**
	 * Get Add-Ons
	 *
	 * @access private
	 * @return array
	 */
	private static function get_addons() {

		$refund_on_off     = 'always-off';
		$bm_add_ons_refund = array(
			'bm_shipping_and_payment',
		);
		foreach ( $bm_add_ons_refund as $bm_add_on_refund ) {
			if ( get_option( $bm_add_on_refund ) == 'on' ) {
				$refund_on_off = 'always-on';
				break;
			}
		}

		$bm_add_ons = array(
			array(
				'title'       => __( 'Conditional Shipping & Payments', 'b2b-market' ),
				'description' => __( 'B2B-Market Shipping & Payments let you control the conditional displays of shipping and payments options per customer group', 'b2b-market' ),
				'image'       => B2B_PLUGIN_URL . '/assets/admin/img/bedingt-versand.jpg',
				'dashicon'    => '',
				'video'       => 'https://s3.eu-central-1.amazonaws.com/videogm/ustid.mp4',
				'on-off'      => get_option( 'bm_addon_shipping_and_payment' ) == 'on' ? 'on' : 'off',
				'id'          => 'bm_addon_shipping_and_payment',
			),
			array(
				'title'       => __( 'Import & Export', 'b2b-market' ),
				'description' => __( 'B2B-Market Import & Export let you export your customer groups with all pricing options. Works also with plugin settings', 'b2b-market' ),
				'image'       => B2B_PLUGIN_URL . '/assets/admin/img/import-export.jpg',
				'dashicon'    => '',
				'video'       => 'https://s3.eu-central-1.amazonaws.com/videogm/ustid.mp4',
				'on-off'      => get_option( 'bm_addon_import_and_export' ) == 'on' ? 'on' : 'off',
				'id'          => 'bm_addon_import_and_export',
			),
			array(
				'title'       => __( 'Registration', 'b2b-market' ),
				'description' => __( 'Allow Users to registrate for specific Customer Groups. Use Double Opt-In and Vat-Check.', 'b2b-market' ),
				'image'       => B2B_PLUGIN_URL . '/assets/admin/img/registrierung.jpg',
				'dashicon'    => '',
				'video'       => 'https://s3.eu-central-1.amazonaws.com/videogm/ustid.mp4',
				'on-off'      => get_option( 'bm_addon_registration' ) == 'on' ? 'on' : 'off',
				'id'          => 'bm_addon_registration',
			),
			array(
				'title'       => __( 'Min & Max Quantities', 'b2b-market' ),
				'description' => __( 'B2B-Market Min & Max Quantities let you define min and max quantities for products per user group. You could also define steps for each product and group.', 'b2b-market' ),
				'image'       => B2B_PLUGIN_URL . '/assets/admin/img/import-export.jpg',
				'dashicon'    => '',
				'video'       => 'https://s3.eu-central-1.amazonaws.com/videogm/ustid.mp4',
				'on-off'      => get_option( 'bm_addon_quantities' ) == 'on' ? 'on' : 'off',
				'id'          => 'bm_addon_quantities',
			),
			array(
				'title'       => __( 'Slack Connector', 'b2b-market' ),
				'description' => __( 'Allow users to connect woocommerce with slack and get notified about orders, new customers and more.', 'b2b-market' ),
				'image'       => B2B_PLUGIN_URL . '/assets/admin/img/slackconnector.jpg',
				'dashicon'    => '',
				'video'       => 'https://s3.eu-central-1.amazonaws.com/videogm/ustid.mp4',
				'on-off'      => get_option( 'bm_addon_slack' ) == 'on' ? 'on' : 'off',
				'id'          => 'bm_addon_slack',
			),
		);


		return apply_filters( 'woocommerce_bm_add_ons_menu_list', $bm_add_ons );

	}

	/**
	 * Get Save Button
	 *
	 * @return void
	 */
	private function save_button( $class = 'top' ) {
		?>
		<input type="submit" name="submit_save_bm_options" class="save-bm-options <?php echo $class; ?>"
		value="<?php echo __( 'Save changes', 'b2b-market' ); ?>" />
		<?php
	}

	/**
	 * Get Video Div
	 *
	 * @access privat
	 * @static
	 *
	 * @param String $text
	 * @param String $url
	 *
	 * @return String
	 */
	public static function get_video_layer( $url ) {
		return '<div class="bm-video-wrapper">
					<span class="url">' . $url . '</span>
					<a class="open"><span class="dashicons dashicons-format-video icon"></span>' . __( 'Video', 'b2b-market' ) . '</a>
					<div class="videoouter">
                        <div class="videoinner">
                            <a class="close">' . __( 'Close', 'b2b-market' ) . '<span class="dashicons dashicons-no-alt icon"></span></a>
                            <div class="video"></div>
                        </div>
                    </div>
				</div>';
	}

}
