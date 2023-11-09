<?php

class BM_Edit_Group {

	/**
	 * @var string
	 */
	public $meta_prefix;
	/**
	 * @var string|void
	 */
	public $slug;
	/**
	 * @var string
	 */
	public $group_admin_url;


	/**
	 * BM_Edit_Group constructor.
	 */
	public function __construct() {
		$this->meta_prefix     = 'bm_';
		$this->slug            = __( 'customer_groups', 'b2b-market' );
		$this->group_admin_url = admin_url() . DIRECTORY_SEPARATOR . 'admin.php?page=b2b-market&tab=groups';

		$this->init();

	}


	/**
	 * initialize settings area
	 */
	public function init() {

		if ( isset( $_GET['group_id'] ) && 'new' != $_GET['group_id'] ) {
			$group_id = $_GET['group_id'];
		} elseif ( isset( $_GET['group_id'] ) && 'new' == $_GET['group_id'] ) {
			$group_id = '';
		} else {
			$group_id = '';
		}

		$group = get_post( $group_id );

		?>
		<div class="group-box">
			<a class="button" id="backtogroups" href="<?php echo $this->group_admin_url; ?>" style="margin-bottom:15px;"><?php _e( 'Return to all groups', 'b2b-market' ); ?></a>
			<form id="new_post" name="new_post" method="post" action="">
				<div class="group-line">
					<h3><?php _e( 'Title', 'b2b-market' ); ?></h3>
					<input class="space-right b2b-group-title" type="text" name="customer_group_title"
					value="<?php echo get_the_title( $group_id ); ?>" placeholder="Title">
				</div>

				<div class="group-line">
					<h3><?php _e( 'Group Price', 'b2b-market' ); ?></h3>
					<?php $this->price_output( $group_id ); ?>
				</div>

				<div class="group-line">
					<h3><?php _e( 'Bulk Price', 'b2b-market' ); ?></h3>
					<?php $this->bulk_price_output( $group_id ); ?>
				</div>

				<div class="group-line">
					<h3><?php _e( 'Apply Prices', 'b2b-market' ); ?></h3>
					<?php $this->product_output( $group_id ); ?>
				</div>

				<div class="group-line">
					<h3><?php _e( 'Blacklist', 'b2b-market' ); ?></h3>
					<?php $this->conditional_display_output( $group_id ); ?>
				</div>
				<?php
				$guest_group = get_option( 'bm_guest_group' );
				if ( $group_id != $guest_group ) :
				?>
				<div class="group-line">
					<h3><?php _e( 'Discounts', 'b2b-market' ); ?></h3>
					<?php $this->automatic_actions_output( $group_id ); ?>
				</div>
				<?php endif; ?>
				<div class="group-line">
					<h3><?php _e( 'Tax Control', 'b2b-market' ); ?></h3>
					<?php $this->tax_control_output( $group_id ); ?>
				</div>
				<p align="right">
					<input class="button" type="submit" value="<?php _e( 'Save Group', 'b2b-market' ); ?>" tabindex="6" id="submit" name="submit"/>
				</p>
				<?php wp_nonce_field( basename( __FILE__ ), 'group_nonce' ); ?>
			</form>
		</div>

		<?php
		$this->save( $group_id );

	}


	/**
	 * output price fields
	 *
	 * @param $group_id
	 */
	public function price_output( $group_id ) {

		$kg_price      = get_post_meta( $group_id, $this->meta_prefix . 'price', true );
		$kg_price_type = get_post_meta( $group_id, $this->meta_prefix . 'price_type', true );

		$price_types = array(
			__( 'Fixed Price', 'b2b-market' )            => 'fix',
			__( 'Discount (fixed Value)', 'b2b-market' ) => 'discount',
			__( 'Discount (%)', 'b2b-market' )           => 'discount-percent',
		);

		$content  = '<p>' . __( 'Modify the price for each product assigned to this customer group', 'b2b-market' ) . '</p>';
		$content .= '<div class="b2b-third"><label for="' . $this->meta_prefix . 'price">' . __( 'Price:', 'b2b-market' ) . '</label><br><input type="number" step="0.001" min="0" name="' . $this->meta_prefix . 'price" value="' . esc_textarea( $kg_price ) . '"></div>';
		$content .= '<div class="b2b-third"><label for="' . $this->meta_prefix . 'price_type">' . __( 'Price-Type:', 'b2b-market' ) . '</label><br><select name="' . $this->meta_prefix . 'price_type" id="' . $this->meta_prefix . 'price_type">';

		if ( isset( $price_types ) ) {
			foreach ( $price_types as $key => $value ) {
				if ( $value == $kg_price_type ) {
					$content .= '<option selected value="' . esc_attr( $value ) . '">' . $key . '</option>';
				} else {
					$content .= '<option value="' . esc_attr( $value ) . '">' . esc_textarea( $key ) . '</option>';
				}
			}
		}
		$content .= '</select></div>';

		echo $content;
	}


	/**
	 * output product fields
	 *
	 * @param $group_id
	 */
	public function product_output( $group_id ) {

		$kg_products     = get_post_meta( $group_id, $this->meta_prefix . 'products', true );
		$kg_categories   = get_post_meta( $group_id, $this->meta_prefix . 'categories', true );
		$kg_all_products = get_post_meta( $group_id, $this->meta_prefix . 'all_products', true );

		if ( false == $kg_all_products ) {
			$kg_all_products = 'on';
		}

		$off_active = $kg_all_products == 'off' ? 'active' : 'clickable';
		$on_active  = $kg_all_products == 'on' ? 'active' : 'clickable';

		if ( 'on' == $kg_all_products ) {
			$check = 'checked="checked"';
		} else {
			$check = '';
		}

		$content  = '<p>' . __( 'For choosen products the settings from group price and bulk prices will be applied. It does not apply to your blacklist settings.', 'b2b-market' ) . '</p>';
		$content .= '<div class="b2b-third selection-products"><label for="' . $this->meta_prefix . 'products">' . __( 'Products:', 'b2b-market' ) . '</label><br><input id="searchable-products" size="100" type="text" name="' . $this->meta_prefix . 'products" value="' . esc_textarea( $kg_products ) . '"></div>';
		$content .= '<div class="b2b-third"><label for="' . $this->meta_prefix . 'categories">' . __( 'Product Categories:', 'b2b-market' ) . '</label><br><input id="searchable-categories" size="100" type="text" name="' . $this->meta_prefix . 'categories" value="' . esc_textarea( $kg_categories ) . '"></div>';

		$content .= '<div class="b2b-third"><span>' . __( 'Override and activate for all products:', 'b2b-market' ) . '</span><br>';
		$content .= '<label class="switch" style="margin-top:5px;" for="' . $this->meta_prefix . 'all_products">
				<input
					name="' . $this->meta_prefix . 'all_products"
					id="' . $this->meta_prefix . 'all_products"
					type="checkbox"
					class="' . esc_attr( isset( $value['class'] ) ? $value['class'] : '' ) . '"
					value="on"
					' . $check . '
				/>
				<div class="slider round bm-slider"></div>
			</label> 
			<p class="screen-reader-buttons">
				<span class="bm-ui-checkbox switcher off ' . $off_active . '">' . __( 'Off', 'b2b-market' ) . '</span>
				<span class="bm-ui-checkbox delimter">|</span>
				<span class="bm-ui-checkbox switcher on ' . $on_active . '">' . __( 'On', 'b2b-market' ) . '</span>
			</p></div>';

		echo $content;
	}


	/**
	 * output bulk fields
	 *
	 * @param $group_id
	 */
	public function bulk_price_output( $group_id ) {

		global $post;

		$bulk_prices = get_post_meta( $group_id, 'bm_bulk_prices', false );
		$options     = array(
			__( 'Fix Price', 'b2b-market' )              => 'fix',
			__( 'Discount (fixed Value)', 'b2b-market' ) => 'discount',
			__( 'Discount (%)', 'b2b-market' )           => 'discount-percent',
		);

		?>
		<div id="bm-bulkprices-inner">
			<p><?php _e( 'Bulk prices are applied if the current quantity fits in the configured quantity range ', 'b2b-market' ); ?></p>
		<?php

		/* filled with existing data */
		$counter = 0;

		if ( ! empty( $bulk_prices ) ) {
			if ( count( $bulk_prices ) > 0 ) {
				foreach ( $bulk_prices as $bulk_price ) {
					foreach ( $bulk_price as $price ) {

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
		}
		?>
		<div class="new-bulk">
			<span id="here"></span>
			<span class="button add"><?php _e( 'Add', 'b2b-market' ); ?></span>
		</div>
		<script>
			jQuery(document).ready(function ($) {
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
		<?php
	}


	/**
	 * output conditional fields
	 *
	 * @param $group_id
	 */
	public function conditional_display_output( $group_id ) {

		$kg_conditional_products     = get_post_meta( $group_id, $this->meta_prefix . 'conditional_products', true );
		$kg_conditional_categories   = get_post_meta( $group_id, $this->meta_prefix . 'conditional_categories', true );
		$kg_conditional_all_products = get_post_meta( $group_id, $this->meta_prefix . 'conditional_all_products', true );

		if ( false == $kg_conditional_all_products ) {
			$kg_conditional_all_products = 'off';
		}

		$off_active = $kg_conditional_all_products == 'off' ? 'active' : 'clickable';
		$on_active  = $kg_conditional_all_products == 'on' ? 'active' : 'clickable';

		if ( 'on' == $kg_conditional_all_products ) {
			$check = 'checked="checked"';
		} else {
			$check = '';
		}

		$content  = '<p>' . __( 'Choose which products and categories you want to exclude for this customer group.', 'b2b-market' ) . '</p>';
		$content .= '<div class="b2b-third  selection-products"><label for="' . $this->meta_prefix . 'conditional_products">' . __( 'Products:', 'b2b-market' ) . '</label><br><input id="searchable-conditional-products" class="space-right" size="100" type="text" name="' . $this->meta_prefix . 'conditional_products" value="' . esc_textarea( $kg_conditional_products ) . '"></div>';
		$content .= '<div class="b2b-third"><label for="' . $this->meta_prefix . 'conditional_categories">' . __( 'Product Categories:', 'b2b-market' ) . '</label><br><input id="searchable-conditional-categories" class="space-right" size="100" type="text" name="' . $this->meta_prefix . 'conditional_categories" value="' . esc_textarea( $kg_conditional_categories ) . '"></div>';

		$content .= '<div style="float: left;"><span>' . __( 'Invert to Whitelist', 'b2b-market' ) . '</span><br>';
		$content .= '<label class="switch" style="margin-top:5px;" for="' . $this->meta_prefix . 'conditional_all_products">
				<input
					name="' . $this->meta_prefix . 'conditional_all_products"
					id="' . $this->meta_prefix . 'conditional_all_products"
					type="checkbox"
					class="' . esc_attr( isset( $value['class'] ) ? $value['class'] : '' ) . '"
					value="on"
					' . $check . '
				/>
				<div class="slider round bm-slider"></div>
			</label> 
			<p class="screen-reader-buttons">
				<span class="bm-ui-checkbox switcher off ' . $off_active . '">' . __( 'Off', 'b2b-market' ) . '</span>
				<span class="bm-ui-checkbox delimter">|</span>
				<span class="bm-ui-checkbox switcher on ' . $on_active . '">' . __( 'On', 'b2b-market' ) . '</span>
			</p></div>';

		echo $content;

	}

	/**
	 * output tax fields
	 *
	 * @param $group_id
	 */
	public function tax_control_output( $group_id ) {

		$content  = '<div class="bm-tax-settings"><p>' . __( 'Show net prices instead of gross price?', 'b2b-market' ) . '</p>';
		$tax_type = get_post_meta( $group_id, $this->meta_prefix . 'tax_type', true );
		$vat_type = get_post_meta( $group_id, $this->meta_prefix . 'vat_type', true );

		$guest_group = get_option( 'bm_guest_group' );

		if ( false == $tax_type ) {
			$tax_type = 'off';
		}
		if ( false == $vat_type ) {
			$vat_type = 'off';
		}

		$off_active_tax = $tax_type == 'off' ? 'active' : 'clickable';
		$on_active_tax  = $tax_type == 'on' ? 'active' : 'clickable';

		$off_active_vat = $vat_type == 'off' ? 'active' : 'clickable';
		$on_active_vat  = $vat_type == 'on' ? 'active' : 'clickable';

		if ( 'on' == $tax_type ) {
			$check_tax = 'checked="checked"';
		} else {
			$check_tax = '';
		}

		if ( 'on' == $vat_type ) {
			$check_vat = 'checked="checked"';
		} else {
			$check_vat = '';
		}

		$content .= '<label class="switch" style="margin-top:5px;" for="' . $this->meta_prefix . 'tax_type">
				<input
					name="' . $this->meta_prefix . 'tax_type"
					id="' . $this->meta_prefix . 'tax_type"
					type="checkbox"
					class="' . esc_attr( isset( $value['class'] ) ? $value['class'] : '' ) . '"
					value="on"
					' . $check_tax . '
				/>
				<div class="slider round bm-slider"></div>
			</label> 
			<p class="screen-reader-buttons">
				<span class="bm-ui-checkbox switcher off ' . $off_active_tax . '">' . __( 'Off', 'b2b-market' ) . '</span>
				<span class="bm-ui-checkbox delimter">|</span>
				<span class="bm-ui-checkbox switcher on ' . $on_active_tax . '">' . __( 'On', 'b2b-market' ) . '</span>
			</p>';

		if ( $group_id != $guest_group ) :
			$content .= '<p>' . __( 'Use VAT validation for this group registration?', 'b2b-market' ) . '</p>';
			$content .= '<label class="switch" style="margin-top:5px;" for="' . $this->meta_prefix . 'vat_type">
					<input
						name="' . $this->meta_prefix . 'vat_type"
						id="' . $this->meta_prefix . 'vat_type"
						type="checkbox"
						class="' . esc_attr( isset( $value['class'] ) ? $value['class'] : '' ) . '"
						value="on"
						' . $check_vat . '
					/>
					<div class="slider round bm-slider"></div>
				</label> 
				<p class="screen-reader-buttons">
					<span class="bm-ui-checkbox switcher off ' . $off_active_vat . '">' . __( 'Off', 'b2b-market' ) . '</span>
					<span class="bm-ui-checkbox delimter">|</span>
					<span class="bm-ui-checkbox switcher on ' . $on_active_vat . '">' . __( 'On', 'b2b-market' ) . '</span>
				</p></div>';
		endif;

		echo $content;

	}

	/**
	 * output automatic action fields
	 *
	 * @param $group_id
	 */
	public function automatic_actions_output( $group_id ) {

		$discount_products     = get_post_meta( $group_id, $this->meta_prefix . 'discount_products', true );
		$discount_categories   = get_post_meta( $group_id, $this->meta_prefix . 'discount_categories', true );
		$discount_all_products = get_post_meta( $group_id, $this->meta_prefix . 'discount_all_products', true );

		if ( false == $discount_all_products ) {
			$discount_all_products = 'off';
		}

		$off_active = $discount_all_products == 'off' ? 'active' : 'clickable';
		$on_active  = $discount_all_products == 'on' ? 'active' : 'clickable';

		if ( 'on' == $discount_all_products ) {
			$check = 'checked="checked"';
		} else {
			$check = '';
		}

		/* first order discount */
		$discount_name = get_post_meta( $group_id, $this->meta_prefix . 'discount_name', true );
		$discount      = get_post_meta( $group_id, $this->meta_prefix . 'discount', true );
		$discount_type = get_post_meta( $group_id, $this->meta_prefix . 'discount_type', true );

		/* discount per category */
		$goods_categories    = get_post_meta( $group_id, $this->meta_prefix . 'goods_discount_categories', true );
		$goods_product_count = get_post_meta( $group_id, $this->meta_prefix . 'goods_product_count', true );
		$goods_discount      = get_post_meta( $group_id, $this->meta_prefix . 'goods_discount', true );
		$goods_discount_type = get_post_meta( $group_id, $this->meta_prefix . 'goods_discount_type', true );

		$discount_types = array(
			__( 'Discount (fixed Value)', 'b2b-market' ) => 'order-discount-fix',
			__( 'Discount (%)', 'b2b-market' )           => 'order-discount-percent',
		);

		/* first order */
		$content = '<div class="discount-box"><b>' . __( 'First Order', 'b2b-market' ) . '</b><p>' . __( 'Set a discount for the first order of a user from this group', 'b2b-market' ) . '</p>';

		$content .= '<div class="b2b-third"><label for="' . $this->meta_prefix . 'discount_name">' . __( 'Label:', 'b2b-market' ) . '</label><input type="text" name="' . $this->meta_prefix . 'discount_name" value="' . esc_textarea( $discount_name ) . '"></div>';
		$content .= '<div class="b2b-third"><label for="' . $this->meta_prefix . 'discount">' . __( 'Discount:', 'b2b-market' ) . '</label><input type="number" step="0.001" min="0"  name="' . $this->meta_prefix . 'discount" value="' . esc_textarea( $discount ) . '"></div>';
		$content .= '<div class="b2b-third"><label for="' . $this->meta_prefix . 'discount_type">' . __( 'Discount-Type:', 'b2b-market' ) . '</label><br><select name="' . $this->meta_prefix . 'discount_type" id="' . $this->meta_prefix . 'discount_type">';

		if ( isset( $discount_types ) ) {
			foreach ( $discount_types as $key => $value ) {
				if ( $value == $discount_type ) {
					$content .= '<option selected value="' . esc_attr( $value ) . '">' . $key . '</option>';
				} else {
					$content .= '<option value="' . esc_attr( $value ) . '">' . esc_textarea( $key ) . '</option>';
				}
			}
		}
		$content .= '</select></div>';
		$content .= '<div class="b2b-third  selection-products"><label for="' . $this->meta_prefix . 'discount_products">' . __( 'Products:', 'b2b-market' ) . '</label><br><input id="discount-products" size="100" type="text" name="' . $this->meta_prefix . 'discount_products" value="' . esc_textarea( $discount_products ) . '"></div>';
		$content .= '<div class="b2b-third"><label for="' . $this->meta_prefix . 'discount_categories">' . __( 'Product Categories:', 'b2b-market' ) . '</label><br><input id="discount-categories" size="100" type="text" name="' . $this->meta_prefix . 'discount_categories" value="' . esc_textarea( $discount_categories ) . '"></div>';

		$content .= '<div class="b2b-third"><span>' . __( 'Override and activate for all products:', 'b2b-market' ) . '</span><br>';
		$content .= '<label class="switch" style="margin-top:5px;" for="' . $this->meta_prefix . 'discount_all_products">
				<input
					name="' . $this->meta_prefix . 'discount_all_products"
					id="' . $this->meta_prefix . 'discount_all_products"
					type="checkbox"
					class="' . esc_attr( isset( $value['class'] ) ? $value['class'] : '' ) . '"
					value="on"
					' . $check . '
				/>
				<div class="slider round bm-slider"></div>
			</label> 
			<p class="screen-reader-buttons">
				<span class="bm-ui-checkbox switcher off ' . $off_active . '">' . __( 'Off', 'b2b-market' ) . '</span>
				<span class="bm-ui-checkbox delimter">|</span>
				<span class="bm-ui-checkbox switcher on '. $on_active . '">'. __( 'On', 'b2b-market' ) . '</span>
			</p></div></div>';

		/* category */

		$content .= '<div class="goods discount-box">';
		$content .= '<b>' . __( 'Products from Category', 'b2b-market' ) . '</b><p>' . __( 'Set a discount if a customer has the defined quantity of products from the given category in their cart.', 'b2b-market' ) . '</p>';

		$content .= '<div class="goods-part"><label for="' . $this->meta_prefix . 'goods_discount_categories">' . __( 'Product Categories', 'b2b-market' ) . ': </label><br><input id="searchable-discount-categories" class="space-right" size="100" type="text" name="' . $this->meta_prefix . 'goods_discount_categories" value="' . esc_textarea( $goods_categories ) . '"></div>';

		$content .= '<div class="goods-part"><label for="' . $this->meta_prefix . 'goods_product_count">' . __( 'Quantity', 'b2b-market' ) . ': </label><br><input type="number" min="0" name="' . $this->meta_prefix . 'goods_product_count" value="' . esc_textarea( $goods_product_count ) . '"></div>';

		$content .= '<div class="goods-part"><label for="' . $this->meta_prefix . 'goods_discount">' . __( 'Discount', 'b2b-market' ) . ': </label><br><input type="number" step="0.001" min="0" name="' . $this->meta_prefix . 'goods_discount" value="' . esc_textarea( $goods_discount ) . '"></div>';

		$content .= '<div class="goods-part"><label for="' . $this->meta_prefix . 'goods_discount_type">' . __( 'Discount-Type', 'b2b-market' ) . ': </label><br><select class="space-right" name="' . $this->meta_prefix . 'goods_discount_type" id="' . $this->meta_prefix . 'goods_discount_type">';

		if ( isset( $discount_types ) ) {
			foreach ( $discount_types as $key => $value ) {
				if ( $value == $goods_discount_type ) {
					$content .= '<option selected value="' . esc_attr( $value ) . '">' . $key . '</option>';
				} else {
					$content .= '<option value="' . esc_attr( $value ) . '">' . esc_textarea( $key ) . '</option>';
				}
			}
		}
		$content .= '</select></div>';
		$content .= '</div>';

		echo $content;
	}


	/**
	 * save post and metadata
	 *
	 * @param $group_id
	 */
	public function save( $group_id ) {

		if ( isset( $_POST['submit'] ) && isset( $_POST['group_nonce'] ) && wp_verify_nonce( $_POST['group_nonce'], basename( __FILE__ ) ) ) {

			if ( isset( $_GET['group_id'] ) && 'new' == $_GET['group_id'] ) {

				$args = array(
					'post_title'   => $_POST['customer_group_title'],
					'post_name'    => $_POST['customer_group_title'],
					'post_content' => '',
					'post_status'  => 'publish',
					'post_type'    => 'customer_groups',
				);

				$group_id = wp_insert_post( $args );

			} else {

				if ( isset( $_POST['customer_group_title'] ) ) {

					$group_object = get_post( $group_id );
					$role         = $group_object->post_name;

					$user_args = array(
						'role__in' => array( $role ),
						'fields'   => 'ids',
					);

					$old_users = get_users( $user_args );

					$args = array(
						'ID'           => $group_id,
						'post_title'   => $_POST['customer_group_title'],
						'post_name'    => $_POST['customer_group_title'],
						'post_content' => '',
						'post_status'  => 'publish',
					);

					wp_update_post( $args );

					/* migrate users to new group if exists */

					if ( isset( $old_users ) && ! empty( $old_users ) ) {

						$new_group = get_post( $group_id );
						$new_role  = $new_group->post_name;

						foreach ( $old_users as $user_id ) {

							$current_user = new WP_User( $user_id );

							$current_user->remove_role( $role );
							$current_user->add_role( $new_role );
						}
					}

					/* delete old role */

					if ( 'customer' !== $role ) {
						remove_role( $role );
					}
				}
			}

			/* general meta */

			/* apply prices */
			if ( isset( $_POST[ $this->meta_prefix . 'price' ] ) ) {
				$kg_meta[ $this->meta_prefix . 'price' ] = esc_html( $_POST[ $this->meta_prefix . 'price' ] );
			}

			if ( isset( $_POST[ $this->meta_prefix . 'price_type' ] ) ) {
				$kg_meta[ $this->meta_prefix . 'price_type' ] = esc_html( $_POST[ $this->meta_prefix . 'price_type' ] );
			}

			if ( isset( $_POST[ $this->meta_prefix . 'products' ] ) ) {
				$kg_meta[ $this->meta_prefix . 'products' ] = esc_html( $_POST[ $this->meta_prefix . 'products' ] );
			}

			if ( isset( $_POST[  $this->meta_prefix . 'categories' ] ) ) {
				$kg_meta[ $this->meta_prefix . 'categories' ] = esc_html( $_POST[ $this->meta_prefix . 'categories' ] );
			}

			if ( isset( $_POST[ $this->meta_prefix . 'all_products' ] ) ) {
				$kg_meta[ $this->meta_prefix . 'all_products' ] = $_POST[ $this->meta_prefix . 'all_products' ];

			} else {
				$kg_meta[ $this->meta_prefix . 'all_products' ] = 'off';
			}
			/* blacklist whitelist */
			if ( isset( $_POST[ $this->meta_prefix . 'conditional_categories' ] ) ) {
				$kg_meta[ $this->meta_prefix . 'conditional_categories' ] = esc_html( $_POST[ $this->meta_prefix . 'conditional_categories' ] );
			}
			if ( isset( $_POST[ $this->meta_prefix . 'conditional_products' ] ) ) {
				$kg_meta[ $this->meta_prefix . 'conditional_products' ] = esc_html( $_POST[ $this->meta_prefix . 'conditional_products' ] );
			}

			if ( isset( $_POST[ $this->meta_prefix . 'conditional_all_products' ] ) ) {
				$kg_meta[ $this->meta_prefix . 'conditional_all_products' ] = $_POST[ $this->meta_prefix . 'conditional_all_products' ];
			} else {
				$kg_meta[ $this->meta_prefix . 'conditional_all_products' ] = 'off';
			}
			/* discounts */
			if ( isset( $_POST[ $this->meta_prefix . 'discount_categories' ] ) ) {
				$kg_meta[ $this->meta_prefix . 'discount_categories' ] = esc_html( $_POST[ $this->meta_prefix . 'discount_categories' ] );
			}

			if ( isset( $_POST[ $this->meta_prefix . 'discount_products' ] ) ) {
				$kg_meta[ $this->meta_prefix . 'discount_products' ] = esc_html( $_POST[ $this->meta_prefix . 'discount_products' ] );
			}

			if ( isset( $_POST[ $this->meta_prefix . 'discount_all_products' ] ) ) {
				$kg_meta[ $this->meta_prefix . 'discount_all_products' ] = $_POST[ $this->meta_prefix . 'discount_all_products' ];
			} else {
				$kg_meta[ $this->meta_prefix . 'discount_all_products' ] = 'off';
			}

			/* tax and vat */
			if ( isset( $_POST[ $this->meta_prefix . 'tax_type' ] ) ) {
				$kg_meta[ $this->meta_prefix . 'tax_type' ] = $_POST[ $this->meta_prefix . 'tax_type' ];
			} else {
				$kg_meta[ $this->meta_prefix . 'tax_type' ] = 'off';
			}

			if ( isset( $_POST[ $this->meta_prefix . 'vat_type' ] ) ) {
				$kg_meta[ $this->meta_prefix . 'vat_type' ] = $_POST[ $this->meta_prefix . 'vat_type' ];
			} else {
				$kg_meta[ $this->meta_prefix . 'vat_type' ] = 'off';
			}

			foreach ( $kg_meta as $key => $value ) :

				if ( get_post_meta( $group_id, $key, false ) ) {
					update_post_meta( $group_id, $key, $value );
				} else {
					add_post_meta( $group_id, $key, $value );
				}
				if ( ! $value ) {
					delete_post_meta( $group_id, $key );
				}

			endforeach;

			/* discount meta */
			$discount_meta = array();

			if ( isset( $_POST[ $this->meta_prefix . 'discount_name' ] ) ) {
				$discount_meta[ $this->meta_prefix . 'discount_name' ] = esc_html( $_POST[ $this->meta_prefix . 'discount_name' ] );
			}

			if ( isset( $_POST[ $this->meta_prefix . 'discount' ] ) ) {
				$discount_meta[ $this->meta_prefix . 'discount' ]      = esc_html( $_POST[ $this->meta_prefix . 'discount' ] );
			}

			if ( isset( $_POST[ $this->meta_prefix . 'discount_type' ] ) ) {
				$discount_meta[ $this->meta_prefix . 'discount_type' ]      = esc_html( $_POST[ $this->meta_prefix . 'discount_type' ] );
			}

			if ( isset( $_POST[ $this->meta_prefix . 'goods_discount_categories' ] ) ) {
				$discount_meta[ $this->meta_prefix . 'goods_discount_categories' ]      = esc_html( $_POST[ $this->meta_prefix . 'goods_discount_categories' ] );
			}

			if ( isset( $_POST[ $this->meta_prefix . 'goods_product_count' ] ) ) {
				$discount_meta[ $this->meta_prefix . 'goods_product_count' ]      = esc_html( $_POST[ $this->meta_prefix . 'goods_product_count' ] );
			}

			if ( isset( $_POST[ $this->meta_prefix . 'goods_discount' ] ) ) {
				$discount_meta[ $this->meta_prefix . 'goods_discount' ]      = esc_html( $_POST[ $this->meta_prefix . 'goods_discount' ] );
			}

			if ( isset( $_POST[ $this->meta_prefix . 'goods_discount_type' ] ) ) {
				$discount_meta[ $this->meta_prefix . 'goods_discount_type' ]      = esc_html( $_POST[ $this->meta_prefix . 'goods_discount_type' ] );

				if ( $discount_meta[ $this->meta_prefix . 'goods_discount_type' ] !== get_post_meta( $group_id, $this->meta_prefix . 'goods_discount_type', true ) ) {

					$coupon = new WC_Coupon( 'category_discount' );

					if ( ! empty( $coupon->get_id() ) ) {
						wp_delete_post( $coupon->get_id() );
					}
				}
			}

			if ( isset( $discount_meta ) && ! empty( $discount_meta ) ) {
				foreach ( $discount_meta as $key => $value ) :
					if ( get_post_meta( $group_id, $key, false ) ) {
						update_post_meta( $group_id, $key, $value );
					} else {
						add_post_meta( $group_id, $key, $value );
					}
					if ( ! $value ) {
						delete_post_meta( $group_id, $key );
					}
				endforeach;
			}

			/* bulk meta */

			if ( isset( $_POST['bulk_price'] ) && ! empty( $_POST['bulk_price'] ) ) {
				$bulk_prices = $_POST['bulk_price'];
			}

			if ( isset( $bulk_prices ) && ! empty( $bulk_prices ) ) {
				update_post_meta( $group_id, 'bm_bulk_prices', $bulk_prices );
			} else {
				delete_post_meta( $group_id, 'bm_bulk_prices' );
			}

			/* add user role */

			$role = new BM_User();
			$role->add_customer_group( $group_id );

			/* update options */
			$payment_shipping_addon = get_option( 'bm_addon_shipping_and_payment' );

			if ( 'on' == $payment_shipping_addon ) {
				CSP_PaymentManager::update_payment_options_for_group();
				CSP_ShippingManager::update_shipping_options_for_group();
			}
			/* safe redirect */

			wp_safe_redirect( get_admin_url() . 'admin.php?page=b2b-market&tab=groups&group_id=' . $group_id );
			exit();

		}

			/* delete transients */
			BM_Helper::delete_b2b_transients();
			update_option( 'bm_all_options_saved', date( 'Y-m-d-H-i' ) );
	}


}

new BM_Edit_Group();
