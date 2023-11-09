<?php

class BM_Product_Meta {

	/**
	 * @var string
	 */
	private $meta_prefix;

	/**
	 * object from BM_Conditional
	 * @var object
	 */
	private $conditional;

	public function __construct() {
		$this->meta_prefix = 'bm_';
		$this->conditional = new BM_Conditionals();
		add_filter( 'woocommerce_product_data_tabs', array( $this, 'add_product_admin_tab' ), 99, 1 );
		add_action( 'woocommerce_product_data_panels', array( $this, 'add_product_admin_tab_fields' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'add_variation_scripts' ) );

		$this->init();
	}

	public function init() {
		add_action( 'save_post', array( $this, 'save_meta' ), 1, 2 );
	}

	public function add_variation_scripts() {

		if ( BM_Helper::get_current_post_type() == 'product' ) {

			$min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : 'min.';

			wp_enqueue_script( 'bm-admin-variation', B2B_PLUGIN_URL . '/assets/admin/bm-admin-variation.' . $min . 'js', array( 'jquery' ), '1.0' );

			$current_groups = BM_Conditionals::is_product_in_customer_groups( get_the_id() );

			if ( isset( $current_groups ) ) {

				$variations_groups = array();

				foreach ( $current_groups as $group ) {

					$group_object = get_post( $group );
					$group_slug   = $group_object->post_name . '_';
					$product      = wc_get_product( get_the_id() );

					if ( ! is_bool( $product ) ) {

						if ( $product->is_type( apply_filters( 'bm_check_product_type', 'variable' ) ) ) {

							$args = array(
								'post_type'   => 'product_variation',
								'post_status' => array( 'private', 'publish' ),
								'numberposts' => -1,
								'orderby'     => 'menu_order',
								'order'       => 'asc',
								'post_parent' => $product->get_id(),
								'fields'      => 'ids',
							);

							$variations = get_posts( $args );

							foreach ( $variations as $variation_id ) {

								$group_object = get_post( $group );
								$group_slug   = $group_object->post_name;

								array_push( $variations_groups, array( $group_slug => $variation_id ) );
							}
						}
					}
				}
			}

			wp_localize_script( 'bm-admin-variation', 'variation_groups', $variations_groups );
		}
	}

	public function add_product_admin_tab( $product_data_tabs ) {
		$product_data_tabs['b2b-market'] = array(
			'label'  => __( 'B2B Market', 'b2b-market' ),
			'target' => 'b2b_fields',
		);

		return $product_data_tabs;
	}

	public function add_product_admin_tab_fields() {

		$quantity_addon = get_option( 'bm_addon_quantities' );

		echo '<div id="b2b_fields" class="panel woocommerce_options_panel">';
		echo '<p>' . $this->get_rrp_meta_content() . '</p>';
		echo '<p>' . $this->get_price_meta_content() . '</p>';
		echo '<p>' . $this->get_bulk_price_meta_content() . '</p>';
		if ( 'on' === $quantity_addon ) {
			echo '<p>' . $this->get_quantities_meta_content() . '</p>';
		}
		echo '</div>';
	}

	/**
	 * output for price metabox
	 */
	public function get_price_meta_content() {

		global $post;

		$content         = '';
		$group_admin_url = admin_url() . 'admin.php?page=b2b-market&tab=groups';
		$current_groups  = BM_Conditionals::is_product_in_customer_groups( get_the_id() );

		if ( empty( $current_groups ) ) {
			echo '<h3>' . __( 'Attention', 'b2b-market' ) . '</h3>';
			_e( 'Before you can define custom group and bulk prices for this product, you have to assign it under "Choose Products" in your customer group.', 'b2b-market' );
			echo ' <a href="' . $group_admin_url . '">' . __( 'Go to customer groups', 'b2b-market' ) . '</a>';
		} else {
			echo '<h1>' . __( 'Group Prices', 'b2b-market' ) . '</h1>';
		}

		wp_nonce_field( basename( __FILE__ ), 'product_price_nonce' );

		$price_types = array(
			__( 'Fixed Price', 'b2b-market' )            => 'fix',
			__( 'Discount (fixed Value)', 'b2b-market' ) => 'discount',
			__( 'Discount (%)', 'b2b-market' )           => 'discount-percent',
		);
		?>
		<div class="group-price-container">

		<?php
		if ( isset( $current_groups ) ) {

			foreach ( $current_groups as $group ) {

				$group_object = get_post( $group );
				$group_slug   = $group_object->post_name . '_';
				$content      = '<article class="beefup" id="group-price"><h2 class="beefup__head">' . __( 'Group price for', 'b2b-market' ) . ' <b>' . get_the_title( $group ) . '</b></h2><div class="beefup__body">';
				$product      = wc_get_product( get_the_id() );

				if ( $product->is_type( apply_filters( 'bm_check_product_type', 'variable' ) ) ) {

					$args = array(
						'post_type'   => 'product_variation',
						'post_status' => array( 'private', 'publish' ),
						'numberposts' => -1,
						'orderby'     => 'menu_order',
						'order'       => 'asc',
						'post_parent' => $product->get_id(),
						'fields'      => 'ids',
					);

					$variations = get_posts( $args );

					foreach ( $variations as $variation_id ) {

						$kg_price      = get_post_meta( $post->ID, $this->meta_prefix . $group_slug . $variation_id . '_price', true );
						$kg_price_type = get_post_meta( $post->ID, $this->meta_prefix . $group_slug . $variation_id . '_price_type', true ); 

						$content .= '<p><b>' . get_the_title( $variation_id ) . '</b></p>';
						$content .= '<label for="' . $this->meta_prefix . $group_slug . $variation_id . '_price">' . __( 'Price:', 'b2b-market' ) . '</label><input class="space-right" type="number" step="0.001" min="0" name="' . $this->meta_prefix . $group_slug . $variation_id . '_price" value="' . esc_textarea( $kg_price ) . '" id="' . $this->meta_prefix . $group_slug . $variation_id . '_price">';
						$content .= '<label for="' . $this->meta_prefix . $group_slug . $variation_id . '_price_type">' . __( 'Price-Type:', 'b2b-market' ) . '</label><select class="space-right" name="' . $this->meta_prefix . $group_slug . $variation_id . '_price_type" id="' . $this->meta_prefix . $group_slug . $variation_id . '_price_type">';

						if ( isset( $price_types ) ) {
							foreach ( $price_types as $key => $value ) {
								if ( $value == $kg_price_type ) {
									$content .= '<option selected value="' . esc_attr( $value ) . '">' . $key . '</option>';
								} else {
									$content .= '<option value="' . esc_attr( $value ) . '">' . esc_textarea( $key ) . '</option>';
								}
							}
						}
						$content .= '</select><br>';
					}
				} else {
					$kg_price      = get_post_meta( $post->ID, $this->meta_prefix . $group_slug . 'price', true );
					$kg_price_type = get_post_meta( $post->ID, $this->meta_prefix . $group_slug . 'price_type', true );

					$content .= '<label for="' . $this->meta_prefix . $group_slug . 'price">' . __( 'Price:', 'b2b-market' ) . '</label><input class="space-right" type="number" step="0.001" min="0" name="' . $this->meta_prefix . $group_slug . 'price" value="' . esc_textarea( $kg_price ) . '" id="' . $this->meta_prefix . $group_slug . 'price">';
					$content .= '<label for="' . $this->meta_prefix . $group_slug . 'price_type">' . __( 'Price-Type:', 'b2b-market' ) . '</label><select class="space-right" name="' . $this->meta_prefix . $group_slug . 'price_type" id="' . $this->meta_prefix . $group_slug . 'price_type">';

					if ( isset( $price_types ) ) {
						foreach ( $price_types as $key => $value ) {
							if ( $value == $kg_price_type ) {
								$content .= '<option selected value="' . esc_attr( $value ) . '">' . $key . '</option>';
							} else {
								$content .= '<option value="' . esc_attr( $value ) . '">' . esc_textarea( $key ) . '</option>';
							}
						}
					}
					$content .= '</select><br>';
				}
				$content .= '</div></article>';

				echo $content;
			}
		}
		?>
	</div>
		<?php

	}

		/**
	 * output for price metabox
	 */
	public function get_rrp_meta_content() {

		global $post;

		$content         = '';
		$group_admin_url = admin_url() . 'admin.php?page=b2b-market&tab=groups';

		echo '<h1>' . __( 'RRP', 'b2b-market' ) . '</h1>';
		wp_nonce_field( basename( __FILE__ ), 'rrp_price_nonce' );

		$product = wc_get_product( get_the_id() );

		$content = '<div class="group-price-container">';

		if ( $product->is_type( apply_filters( 'bm_check_product_type', 'variable' ) ) ) {

			$args = array(
				'post_type'   => 'product_variation',
				'post_status' => array( 'private', 'publish' ),
				'numberposts' => -1,
				'orderby'     => 'menu_order',
				'order'       => 'asc',
				'post_parent' => $product->get_id(),
				'fields'      => 'ids',
			);

			$variations = get_posts( $args );

			foreach ( $variations as $variation_id ) {

				$rrp_price = get_post_meta( $post->ID, $this->meta_prefix . $variation_id . '_rrp', true );

				$content .= '<p><b>' . get_the_title( $variation_id ) . '</b></p>';
				$content .= '<label for="' . $this->meta_prefix . $variation_id . '_rrp">' . __( 'rrp:', 'b2b-market' ) . '</label><input class="space-right" type="number" step="0.001" min="0" name="' . $this->meta_prefix . $variation_id . '_rrp" value="' . esc_textarea( $rrp_price ) . '" id="' . $this->meta_prefix . $variation_id . '_rrp"><br>';
			}
		} else {
			$rrp_price = get_post_meta( $post->ID, $this->meta_prefix . 'rrp', true );
			$content  .= '<label for="' . $this->meta_prefix . 'rrp">' . __( 'rrp:', 'b2b-market' ) . '</label><input class="space-right" type="number" step="0.001" min="0" name="' . $this->meta_prefix . 'rrp" value="' . esc_textarea( $rrp_price ) . '" id="' . $this->meta_prefix . 'rrp">';
		}

		$content .= '</div>';

		echo $content;
	}

	/**
	 * output for bulk price metabox
	 */
	public function get_bulk_price_meta_content() {

		global $post;

		wp_nonce_field( basename( __FILE__ ), 'product_bulk_nonce' );

		$options = array(
			__( 'Fix Price', 'b2b-market' )              => 'fix',
			__( 'Discount (fixed Value)', 'b2b-market' ) => 'discount',
			__( 'Discount (%)', 'b2b-market' )           => 'discount-percent',
		);

		$current_groups = BM_Conditionals::is_product_in_customer_groups( get_the_id() );

		if ( ! empty( $current_groups ) ) {
			echo '<h1>' . __( 'Bulk Prices', 'b2b-market' ) . '</h1>';
		}
		?>
		<div class="group-price-container">
		<?php

		if ( isset( $current_groups ) ) {

			foreach ( $current_groups as $group ) {

				$group_object = get_post( $group );
				$group_slug   = $group_object->post_name . '_';
				$product      = wc_get_product( get_the_id() );

				if ( $product->is_type( apply_filters( 'bm_check_product_type', 'variable' ) ) ) {

					$args = array(
						'post_type'   => 'product_variation',
						'post_status' => array( 'private', 'publish' ),
						'numberposts' => -1,
						'orderby'     => 'menu_order',
						'order'       => 'asc',
						'post_parent' => $product->get_id(),
						'fields'      => 'ids',
					);

					$variations = get_posts( $args );
					?>
					<article class="beefup" id="bulk-price">
						<h2 class="beefup__head"><?php echo  __( 'Bulk prices for', 'b2b-market' ) . ' <b>' . get_the_title( $group ) . '</b>'; ?></b></h2>
						<div class="beefup__body">
					<?php
					foreach ( $variations as $variation_id ) :
						$bulk_prices  = get_post_meta( $post->ID, $this->meta_prefix . $group_slug . $variation_id . '_bulk_prices', false );
						?>

								<div id="bm-bulkprices-inner">
								<p><b><?php echo get_the_title( $variation_id ); ?></b></p>

								<p><?php _e( 'Bulk prices are applied if the current quantity fits in the configured quantity range ', 'b2b-market' ); ?></p>
							<?php

							/* filled with existing data */
							$counter = 0;
							if ( count( $bulk_prices ) > 0 ) {
								if ( ! empty( $bulk_prices ) ) {
									foreach ( $bulk_prices as $bulk_price ) {

										foreach ( $bulk_price as $price ) {

											if ( isset( $price[ 'bulk_price_type' ] ) ) {
												$selected = $price[ 'bulk_price_type' ];
											} else {
												$selected = 'fix';
											}

											if ( isset( $price[ 'bulk_price' ] ) || isset( $price[ 'bulk_price_from' ] ) || isset( $price[ 'bulk_price_to' ] ) ) {
												?>
												<p>
													<label for="<?php echo $group_slug . $variation_id . '_'; ?>bulk_price[<?php echo $counter; ?>][bulk_price]"><?php _e( 'Bulk Price', 'b2b-market' ); ?></label>
													<input type="number" step="0.001" min="0" name="<?php echo $group_slug . $variation_id . '_'; ?>bulk_price[<?php echo $counter; ?>][bulk_price]" value="<?php echo $price[ 'bulk_price']; ?>" />

													<label for="<?php echo $group_slug . $variation_id . '_'; ?>bulk_price[<?php echo $counter; ?>][bulk_price_from]"><?php _e( 'Amount (from)', 'b2b-market' ); ?></label>
													<input type="number" step="1" min="0" name="<?php echo $group_slug . $variation_id . '_'; ?>bulk_price[<?php echo $counter; ?>][bulk_price_from]" value="<?php echo $price[ 'bulk_price_from']; ?>" />

													<label for="<?php echo $group_slug . $variation_id . '_'; ?>bulk_price[<?php echo $counter; ?>][bulk_price_to]"><?php _e( 'Amount (to)', 'b2b-market' ); ?></label>
													<input type="number" step="1" min="0" name="<?php echo $group_slug . $variation_id . '_'; ?>bulk_price[<?php echo $counter; ?>][bulk_price_to]" value="<?php if ( esc_attr( $price[ 'bulk_price_to'] ) != 0 ) { echo $price[ 'bulk_price_to']; } ?>" <?php if ( isset( $price[ 'bulk_price_to'] ) && esc_attr( $price[ 'bulk_price_to'] ) == 0) { echo 'placeholder="∞"'; } ?> />

													<label for="<?php echo $group_slug . $variation_id . '_'; ?>bulk_price[<?php echo $counter; ?>][bulk_price_type]"><?php _e( 'Price-Type', 'b2b-market' ); ?></label>
													<select id="bulk_price_type" name="<?php echo $group_slug . $variation_id . '_'; ?>bulk_price[<?php echo $counter; ?>][bulk_price_type]" class="bulk_price_type">
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
							}
							?>
							<div class="new-bulk">
								<span id="here-<?php echo $group_object->post_name . '-' . $variation_id; ?>"></span>
								<span class="button add-<?php echo $group_object->post_name . '-' . $variation_id; ?> additional-row"><?php _e( 'Add', 'b2b-market' ); ?></span>
							</div>
							<script>
								var $ =jQuery.noConflict();
								$(document).ready(function() {
									var count      = <?php echo $counter; ?>;
									var group_slug = '<?php echo $group_slug . $variation_id . '_'; ?>';
									var group_class = '<?php echo $group_object->post_name . '-' . $variation_id; ?>';

									var bulk_price_label = '<?php _e( "Bulk Price", "b2b-market" ); ?>';
									var bulk_price_from_label = '<?php _e( "Amount (from)", "b2b-market" ); ?>';
									var bulk_price_to_label = '<?php _e( "Amount (to)", "b2b-market" ); ?>';
									var bulk_price_type_label = '<?php _e( "Price-Type", "b2b-market" ); ?>';

									var bulk_price_type_fix_label = '<?php _e( "Fix Price", "b2b-market" ); ?>';
									var bulk_price_type_discount_label = '<?php _e( "Discount (fixed Value)", "b2b-market" ); ?>';
									var bulk_price_type_discount_percent_label = '<?php _e( "Discount (%)", "b2b-market" ); ?>';
									var bulk_price_remove_label = '<?php _e( "Remove", "b2b-market" ); ?>';

									$('.add-' + group_class ).click(function() {
										count = count + 1;
										var content = '<p><label for="' + group_slug + 'bulk_price[' + count + '][bulk_price]">' + bulk_price_label + '</label><input type="number" step="0.001" min="0" name="' + group_slug + 'bulk_price[' + count + '][bulk_price]" value="" />' +
										'<label for="' + group_slug + 'bulk_price[' + count + '][bulk_price_from]">' + bulk_price_from_label + '</label><input type="number" step="1" min="0" name="' + group_slug + 'bulk_price[' + count + '][bulk_price_from]" value="" />' +
										'<label for="' + group_slug + 'bulk_price[' + count + '][bulk_price_to]">' + bulk_price_to_label + '</label><input type="number" step="1" min="0" name="' + group_slug + 'bulk_price[' + count + '][bulk_price_to]" value="" />' +
										'<label for="' + group_slug + 'bulk_price[' + count + '][bulk_price_type]">' + bulk_price_type_label + '</label>' +
										'<select id="bulk_price_type" name="' + group_slug + 'bulk_price[' + count + '][bulk_price_type]" class="bulk_price_type">' +
										'<option value="fix">' + bulk_price_type_fix_label + '</option>' +
										'<option value="discount">' + bulk_price_type_discount_label + '</option>' +
										'<option value="discount-percent">' + bulk_price_type_discount_percent_label + '</option>' +
										'</select>' +
										'<span class="button remove">' + bulk_price_remove_label + '</span>' + 
										'</p>';

										$('#here-' + group_class ).append(content);

										return false;
									});
									$(".remove").live('click', function() {
										$(this).parent().remove();
									});
								});
								</script>
							</div>
					<?php endforeach; ?>
					</div>
				</article>
					<?php
				} else {

					$bulk_prices = get_post_meta( $post->ID, $this->meta_prefix . $group_slug . 'bulk_prices', false );
					?>

					<article class="beefup" id="bulk-price">
					<h2 class="beefup__head"><?php echo  __( 'Bulk prices for', 'b2b-market' ) . ' <b>' . get_the_title( $group ) . '</b>'; ?></b></h2>
					<div class="beefup__body">
						<div id="bm-bulkprices-inner">
							<p><?php _e( 'Bulk prices are applied if the current quantity fits in the configured quantity range ', 'b2b-market' ); ?></p>
						<?php

						/* filled with existing data */
						$counter = 0;
						if ( count( $bulk_prices ) > 0 ) {
							if ( ! empty( $bulk_prices ) ) {
								foreach ( $bulk_prices as $bulk_price ) {
									foreach ( $bulk_price as $price ) {

										if ( isset( $price[ 'bulk_price_type' ] ) ) {
											$selected = $price[ 'bulk_price_type' ];
										} else {
											$selected = 'fix';
										}

										if ( isset( $price[ 'bulk_price' ] ) || isset( $price[ 'bulk_price_from' ] ) || isset( $price[ 'bulk_price_to' ] ) ) {
											?>
											<p>
												<label for="<?php echo $group_slug; ?>bulk_price[<?php echo $counter; ?>][bulk_price]"><?php _e( 'Bulk Price', 'b2b-market' ); ?></label>
												<input type="number" step="0.001" min="0" name="<?php echo $group_slug; ?>bulk_price[<?php echo $counter; ?>][bulk_price]" value="<?php echo $price[ 'bulk_price']; ?>" />

												<label for="<?php echo $group_slug; ?>bulk_price[<?php echo $counter; ?>][bulk_price_from]"><?php _e( 'Amount (from)', 'b2b-market' ); ?></label>
												<input type="number" step="1" min="0" name="<?php echo $group_slug; ?>bulk_price[<?php echo $counter; ?>][bulk_price_from]" value="<?php echo $price[ 'bulk_price_from']; ?>" />

												<label for="<?php echo $group_slug; ?>bulk_price[<?php echo $counter; ?>][bulk_price_to]"><?php _e( 'Amount (to)', 'b2b-market' ); ?></label>
												<input type="number" step="1" min="0" name="<?php echo $group_slug; ?>bulk_price[<?php echo $counter; ?>][bulk_price_to]" value="<?php if ( esc_attr( $price[ 'bulk_price_to'] ) != 0 ) { echo $price[ 'bulk_price_to']; } ?>" <?php if ( isset( $price[ 'bulk_price_to'] ) && esc_attr( $price[ 'bulk_price_to'] ) == 0) { echo 'placeholder="∞"'; } ?> />

												<label for="<?php echo $group_slug; ?>bulk_price[<?php echo $counter; ?>][bulk_price_type]"><?php _e( 'Price-Type', 'b2b-market' ); ?></label>
												<select id="bulk_price_type" name="<?php echo $group_slug; ?>bulk_price[<?php echo $counter; ?>][bulk_price_type]" class="bulk_price_type">
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
						}
						?>
						<div class="new-bulk">
							<span id="here-<?php echo $group_object->post_name; ?>"></span>
							<span class="button add-<?php echo $group_object->post_name; ?> additional-row"><?php _e( 'Add', 'b2b-market' ); ?></span>
						</div>
						<script>
							var $ =jQuery.noConflict();
							$(document).ready(function() {
								var count      = <?php echo $counter; ?>;
								var group_slug = '<?php echo $group_slug; ?>';
								var group_class = '<?php echo $group_object->post_name; ?>';

								var bulk_price_label = '<?php _e( "Bulk Price", "b2b-market" ); ?>';
								var bulk_price_from_label = '<?php _e( "Amount (from)", "b2b-market" ); ?>';
								var bulk_price_to_label = '<?php _e( "Amount (to)", "b2b-market" ); ?>';
								var bulk_price_type_label = '<?php _e( "Price-Type", "b2b-market" ); ?>';

								var bulk_price_type_fix_label = '<?php _e( "Fix Price", "b2b-market" ); ?>';
								var bulk_price_type_discount_label = '<?php _e( "Discount (fixed Value)", "b2b-market" ); ?>';
								var bulk_price_type_discount_percent_label = '<?php _e( "Discount (%)", "b2b-market" ); ?>';
								var bulk_price_remove_label = '<?php _e( "Remove", "b2b-market" ); ?>';

								$('.add-' + group_class).click(function() {
									count = count + 1;
									var content = '<p><label for="' + group_slug + 'bulk_price[' + count + '][bulk_price]">' + bulk_price_label + '</label><input type="number" step="0.001" min="0" name="' + group_slug + 'bulk_price[' + count + '][bulk_price]" value="" />' +
									'<label for="' + group_slug + 'bulk_price[' + count + '][bulk_price_from]">' + bulk_price_from_label + '</label><input type="number" step="1" min="0" name="' + group_slug + 'bulk_price[' + count + '][bulk_price_from]" value="" />' +
									'<label for="' + group_slug + 'bulk_price[' + count + '][bulk_price_to]">' + bulk_price_to_label + '</label><input type="number" step="1" min="0" name="' + group_slug + 'bulk_price[' + count + '][bulk_price_to]" value="" />' +
									'<label for="' + group_slug + 'bulk_price[' + count + '][bulk_price_type]">' + bulk_price_type_label + '</label>' +
									'<select id="bulk_price_type" name="' + group_slug + 'bulk_price[' + count + '][bulk_price_type]" class="bulk_price_type">' +
									'<option value="fix">' + bulk_price_type_fix_label + '</option>' +
									'<option value="discount">' + bulk_price_type_discount_label + '</option>' +
									'<option value="discount-percent">' + bulk_price_type_discount_percent_label + '</option>' +
									'</select>' +
									'<span class="button remove">' + bulk_price_remove_label + '</span>' + 
									'</p>';

									$('#here-' + group_class ).append(content);

									return false;
								});
								$(".remove").live('click', function() {
									$(this).parent().remove();
								});
							});
							</script>
						</div>
					</article>
					<?php
				}
			}
		}
		?>
		</div>
		<?php
	}

		/**
	 * output for price metabox
	 */
	public function get_quantities_meta_content() {

		global $post;

		$current_groups  = BM_Conditionals::is_product_in_customer_groups( get_the_id() );

		wp_nonce_field( basename( __FILE__ ), 'product_quantity_nonce' );

		if ( ! empty( $current_groups ) ) {
			echo'<h1>' . __( 'Quantities', 'b2b-market' ) . '</h1>';
		}
		?>
		<div class="group-quantity-container">

		<?php
		
		if ( isset( $current_groups ) ) {

			foreach ( $current_groups as $group ) {

				$group_object = get_post( $group );
				$group_slug   = $group_object->post_name . '_';
				$content      = '<article class="beefup" id="group-quantity"><h2 class="beefup__head">' . __( 'Quantities', 'b2b-market' ) . ' <b>' . get_the_title( $group ) . '</b></h2><div class="beefup__body">';
				$product      = wc_get_product( get_the_id() );

				$min_quantity  = get_post_meta( $post->ID, $this->meta_prefix . $group_slug . 'min_quantity', true );
				$max_quantity  = get_post_meta( $post->ID, $this->meta_prefix . $group_slug . 'max_quantity', true );
				$step_quantity = get_post_meta( $post->ID, $this->meta_prefix . $group_slug . 'step_quantity', true );

				$content .= '<label for="' . $this->meta_prefix . $group_slug . 'min_quantity">' . __( 'Min:', 'b2b-market' ) . '</label><input class="space-right" type="number" min="0" name="' . $this->meta_prefix . $group_slug . 'min_quantity" value="' . esc_textarea( $min_quantity ) . '" id="' . $this->meta_prefix . $group_slug . 'min_quantity">';
				$content .= '<label for="' . $this->meta_prefix . $group_slug . 'max_quantity">' . __( 'Max:', 'b2b-market' ) . '</label><input class="space-right" type="number" min="0" name="' . $this->meta_prefix . $group_slug . 'max_quantity" value="' . esc_textarea( $max_quantity ) . '" id="' . $this->meta_prefix . $group_slug . 'max_quantity">';
				$content .= '<label for="' . $this->meta_prefix . $group_slug . 'step_quantity">' . __( 'Step:', 'b2b-market' ) . '</label><input class="space-right" type="number" min="0" name="' . $this->meta_prefix . $group_slug . 'step_quantity" value="' . esc_textarea( $step_quantity ) . '" id="' . $this->meta_prefix . $group_slug . 'step_quantity">';

				$content .= '</div></article>';

				echo $content;
			}
		}
		?>
	</div>
		<?php

	}

	/**
	 * @param $post_id
	 * @param $post
	 */
	public function save_meta( $post_id, $post ) {

		if ( BM_Helper::get_current_post_type() == 'product' ) :

			if ( ! current_user_can( 'edit_post', $post_id ) && defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}

			if ( isset( $_POST['product_price_nonce'] ) && wp_verify_nonce( $_POST['product_price_nonce'], basename( __FILE__ ) ) && isset( $_POST['product_bulk_nonce'] ) && wp_verify_nonce( $_POST['product_bulk_nonce'], basename( __FILE__ ) ) && isset( $_POST['rrp_price_nonce'] ) && wp_verify_nonce( $_POST['rrp_price_nonce'], basename( __FILE__ ) ) ) {

				$current_groups  = BM_Conditionals::is_product_in_customer_groups( get_the_id() );
				$product         = wc_get_product( get_the_id() );
				$kg_product_meta = array();
				$conditional     = new BM_Conditionals();

				/* group prices */

				foreach ( $current_groups as $group ) {

					$group_object   = get_post( $group );
					$group_slug     = $group_object->post_name . '_';
					$quantity_addon = get_option( 'bm_addon_quantities' );

					if ( $product->is_type( apply_filters( 'bm_check_product_type', 'variable' ) ) ) {

						$args = array(
							'post_type'   => 'product_variation',
							'post_status' => array( 'private', 'publish' ),
							'numberposts' => -1,
							'orderby'     => 'menu_order',
							'order'       => 'asc',
							'post_parent' => $product->get_id(),
							'fields'      => 'ids',
						);

						$variations = get_posts( $args );

						foreach ( $variations as $variation_id ) {

							$kg_product_meta[ $this->meta_prefix . $group_slug . $variation_id . '_price' ]         = esc_html( $_POST[ $this->meta_prefix . $group_slug . $variation_id . '_price' ] );
							$kg_product_meta[ $this->meta_prefix . $variation_id . '_rrp' ]         = esc_html( $_POST[ $this->meta_prefix . $variation_id . '_rrp' ] );
							$kg_product_meta[ $this->meta_prefix . $group_slug . $variation_id . '_price_type' ]    = esc_html( $_POST[ $this->meta_prefix . $group_slug . $variation_id . '_price_type' ] );

							// variation specific data
							$variation_price_key = $this->meta_prefix . $group_slug . 'price';
							$variation_price     = esc_html( $_POST[ $this->meta_prefix . $group_slug . $variation_id . '_price' ] );

							$variation_rrp_key = $this->meta_prefix . 'rrp';
							$variation_rrp     = esc_html( $_POST[ $this->meta_prefix . $variation_id . '_rrp' ] );

							$variation_price_type_key = $this->meta_prefix . $group_slug . 'price_type';
							$variation_price_type     = esc_html( $_POST[ $this->meta_prefix . $group_slug . $variation_id . '_price_type' ] );

							if ( isset( $variation_price ) && ! empty( $variation_price ) ) {
								update_post_meta( $variation_id, $variation_price_key, $variation_price );
							}
							if ( isset( $variation_price_type ) && ! empty( $variation_price_type ) ) {
								update_post_meta( $variation_id, $variation_price_type_key, $variation_price_type );
							}
							if ( isset( $variation_rrp ) && ! empty( $variation_rrp ) ) {
								update_post_meta( $variation_id, $variation_rrp_key, $variation_rrp );
							}
						}

						if ( 'on' === $quantity_addon ) {
							$kg_product_meta[ $this->meta_prefix . $group_slug . 'min_quantity' ]  = esc_html( $_POST[ $this->meta_prefix . $group_slug . 'min_quantity' ] );
							$kg_product_meta[ $this->meta_prefix . $group_slug . 'max_quantity' ]  = esc_html( $_POST[ $this->meta_prefix . $group_slug . 'max_quantity' ] );
							$kg_product_meta[ $this->meta_prefix . $group_slug . 'step_quantity' ] = esc_html( $_POST[ $this->meta_prefix . $group_slug . 'step_quantity' ] );
						}
					} else {
						$kg_product_meta[ $this->meta_prefix . $group_slug . 'price' ]          = esc_html( $_POST[ $this->meta_prefix . $group_slug . 'price' ] );
						$kg_product_meta[ $this->meta_prefix . 'rrp' ]                          = esc_html( $_POST[ $this->meta_prefix . 'rrp' ] );
						$kg_product_meta[ $this->meta_prefix . $group_slug . 'price_type' ]     = esc_html( $_POST[ $this->meta_prefix . $group_slug . 'price_type' ] );

						if ( 'on' === $quantity_addon ) {
							$kg_product_meta[ $this->meta_prefix . $group_slug . 'min_quantity' ]  = esc_html( $_POST[ $this->meta_prefix . $group_slug . 'min_quantity' ] );
							$kg_product_meta[ $this->meta_prefix . $group_slug . 'max_quantity' ]  = esc_html( $_POST[ $this->meta_prefix . $group_slug . 'max_quantity' ] );
							$kg_product_meta[ $this->meta_prefix . $group_slug . 'step_quantity' ] = esc_html( $_POST[ $this->meta_prefix . $group_slug . 'step_quantity' ] );
						}
					}
				}

				foreach ( $kg_product_meta as $key => $value ) :
					if ( 'revision' === $post->post_type ) {
						return;
					}
					if ( get_post_meta( $post_id, $key, false ) ) {
						update_post_meta( $post_id, $key, $value );
					} else {
						add_post_meta( $post_id, $key, $value );
					}
					if ( ! $value ) {
						delete_post_meta( $post_id, $key );
					}
				endforeach;

				$product = wc_get_product( get_the_id() );

				/* bulk prices */

				foreach ( $current_groups as $group ) {

					$group_object = get_post( $group );
					$group_slug   = $group_object->post_name . '_';


					if ( $product->is_type( apply_filters( 'bm_check_product_type', 'variable' ) ) ) {

						$args = array(
							'post_type'   => 'product_variation',
							'post_status' => array( 'private', 'publish' ),
							'numberposts' => -1,
							'orderby'     => 'menu_order',
							'order'       => 'asc',
							'post_parent' => $product->get_id(),
							'fields'      => 'ids',
						);

						$options = array(
							__( 'Fixed Price', 'b2b-market' )  => 'fix',
							__( 'Discount (fixed Value)', 'b2b-market' ) => 'discount',
							__( 'Discount (%)', 'b2b-market' ) => 'discount-percent',
						);

						$variations = get_posts( $args );

						foreach ( $variations as $variation_id ) {


							if ( isset( $_POST[ $group_slug . $variation_id . '_bulk_price' ] ) && ! empty( $_POST[ $group_slug . $variation_id . '_bulk_price' ] ) ) {
								update_post_meta( $post_id, $this->meta_prefix . $group_slug . $variation_id . '_bulk_prices', $_POST[ $group_slug . $variation_id . '_bulk_price' ] );
								update_post_meta( $variation_id, $this->meta_prefix . $group_slug . 'bulk_prices', $_POST[ $group_slug . $variation_id . '_bulk_price' ] );

							} else {
								delete_post_meta( $post_id, $this->meta_prefix . $group_slug . $variation_id . '_bulk_prices' );
								delete_post_meta( $variation_id, $this->meta_prefix . $group_slug . 'bulk_prices' );
							}
						}
					} else {

						if ( isset( $_POST[ $group_slug . 'bulk_price' ] ) && ! empty( $_POST[ $group_slug . 'bulk_price' ] ) ) {
							update_post_meta( $post->ID, $this->meta_prefix . $group_slug . 'bulk_prices', $_POST[ $group_slug . 'bulk_price' ] );
						} else {
							delete_post_meta( $post->ID, $this->meta_prefix . $group_slug . 'bulk_prices' );
						}
					}
				}

				/* save min variation prices */
				foreach ( $current_groups as $group_id ) {
					$group_object = get_post( $group_id );
					$group_slug   = $group_object->post_name . '_';
					$all_products = get_post_meta( $group_id, 'bm_all_products', true );

					if ( $product->is_type( apply_filters( 'bm_check_product_type', 'variable' ) ) ) {

						$args = array(
							'post_type'   => 'product_variation',
							'post_status' => array( 'private', 'publish' ),
							'numberposts' => -1,
							'orderby'     => 'menu_order',
							'order'       => 'asc',
							'post_parent' => $product->get_id(),
							'fields'      => 'ids',
						);

						$options = array(
							__( 'Fixed Price', 'b2b-market' )  => 'fix',
							__( 'Discount (fixed Value)', 'b2b-market' ) => 'discount',
							__( 'Discount (%)', 'b2b-market' ) => 'discount-percent',
						);

						$variations = get_posts( $args );
						$min_prices = array();
						$prices_meta = array();

						foreach ( $variations as $variation_id ) {

							$variation_product       = wc_get_product( $variation_id );
							$variation_regular_price = floatval( $variation_product->get_price() );

							/* calculate group prices */
							$variation_group_price = floatval( $_POST[ 'bm_' . $group_slug . $variation_id . '_price' ] );
							$group_price           = BM_Pricing_Data::get_group_base_price( $group_id );
							$product_group_price   = BM_Pricing_Data::get_product_group_price( $product->get_id(), $group_id );
							$global_base_price     = BM_Pricing_Data::get_global_base_price();

							if ( is_array( $group_price ) && BM_Helper::is_array_empty( $group_price ) === false && ! is_null( $group_id ) ) {
								$prices_meta[] = $group_price;
							}
							if ( is_array( $product_group_price ) && BM_Helper::is_array_empty( $product_group_price ) === false && ! is_null( $group_id ) ) {
								$prices_meta[] = $product_group_price;
							}
							if ( is_array( $global_base_price ) && BM_Helper::is_array_empty( $global_base_price ) === false ) {
								$prices_meta[] = $global_base_price;
							}

							foreach ( $prices_meta as $meta ) {
								$key   = key( $meta );
								$value = floatval( reset( $meta ) );

								switch ( $key ) {
									case 'fix':
										$b2b_price = $value;
										$min_prices[]  = $b2b_price;
										break;
									case 'discount':
										$b2b_price = $variation_regular_price - $value;
										$min_prices[]  = $b2b_price;
										break;
									case 'discount-percent':
										$b2b_price = $variation_regular_price - ( $value * $variation_regular_price / 100 );
										$min_prices[]  = $b2b_price;
										break;
								}
							}
						}
						if ( isset( $min_prices ) && ! empty( $min_prices ) ) {
							$min_prices[] = $variation_regular_price;
							update_post_meta( $post_id, '_min_variation_group_price', min( $min_prices ) );
						}
					}
				}
			}
		endif;

		BM_Helper::delete_b2b_transients();
		update_option( 'bm_all_options_saved', date( 'Y-m-d-H-i' ) );
	}
}

new BM_Product_Meta();
