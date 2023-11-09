<?php

class RGN_Registration {

	/**
	 * Constructor for RGN_Registration
	 */
	public function __construct() {
		add_action( 'woocommerce_register_form', array( $this, 'ouptput_registration_fields' ), 10 );
		add_action( 'woocommerce_edit_account_form', array( $this, 'ouptput_registration_fields' ), 10 );
		add_filter( 'woocommerce_checkout_fields', array( $this, 'add_checkout_fields' ), 10, 1 );
		add_filter( 'woocommerce_registration_errors', array( $this, 'validate_registration_fields' ), 10 );
		add_filter( 'woocommerce_save_account_details_errors', array( $this, 'validate_registration_fields' ), 10 );
		add_action( 'woocommerce_created_customer', array( $this, 'save_registration_fields' ) );
		add_action( 'woocommerce_save_account_details', array( $this, 'save_registration_fields' ) );
	}

	/**
	 * Output the registration fields markup
	 *
	 * @return void
	 */
	public function ouptput_registration_fields() {
		$fields = $this->get_registration_fields();

		foreach ( $fields as $key => $field_args ) {

			if ( is_user_logged_in() && true === $field_args['hide_in_account'] ) {
				return;
			}
			if ( is_user_logged_in() && true === $field_args['hide_in_registration'] ) {
				return;
			}

			$value = null;

			if ( is_user_logged_in() ) {
				$user_id = get_current_user_id();
				$value   = get_user_meta( $user_id, $key, true );
			}

			$value = isset( $field_args['value'] ) ? $field_args['value'] : $value;
			woocommerce_form_field( $key, $field_args, $value );
		}
	}

	/**
	 * Return the registration fields
	 *
	 * @return array
	 */
	public function get_registration_fields() {

		$groups       = RGN_Helper::get_selectable_groups();
		$remove_label = get_option( 'bm_remove_label_registration' );
		$options      = array();

		foreach ( $groups as $key => $value ) {
			if ( 'customer' === $key ) {
				$options['customer'] = apply_filters( 'bm_customer_registration_label', __( 'Customer', 'b2b-market' ) );
			} else {
				$options[ $key ] = get_the_title( $value );
			}
		}

		$ust_id = array(
			'type'  => 'text',
			'label' => __( 'VAT-ID', 'b2b-market' ),
			'hide_in_account' => apply_filters( 'b2b_hide_in_account', true ),
		);

		$company_registration_number = array(
			'type'                 => 'text',
			'label'                => __( 'Company registration number', 'b2b-market' ),
			'hide_in_account'      => apply_filters( 'b2b_hide_in_account', true ),
		);

		$selectable_groups = array(
			'type'            => 'select',
			'options'         => $options,
			'hide_in_account' => apply_filters( 'b2b_hide_in_account', true ),
		);

		$required_fields = apply_filters( 'bm_required_registration_fields', false );

		if ( $required_fields ) {
			$ust_id['required']                      = true;
			$company_registration_number['required'] = true;
			$selectable_groups['required']           = true;
		}

		if ( 'off' === $remove_label ) {
			$selectable_groups['label'] = __( 'Customer Group', 'b2b-market' );
		}

		return apply_filters( 'bm_account_fields', array( 'b2b_role' => $selectable_groups, 'b2b_uid' => $ust_id, 'b2b_company_registration_number' => $company_registration_number ) );	
	}

	/**
	 * Add registration fields to checkout
	 *
	 * @param array $checkout_fields array of current registration fields.
	 * @return array
	 */
	public function add_checkout_fields( $checkout_fields ) {
		$fields = $this->get_registration_fields();

		foreach ( $fields as $key => $field_args ) {

			$field_args['priority']             = isset( $field_args['priority'] ) ? $field_args['priority'] : 0;
			$checkout_fields['account'][ $key ] = $field_args;
		}

		if ( ! empty( $checkout_fields['account']['account_password'] ) && ! isset( $checkout_fields['account']['account_password']['priority'] ) ) {
			$checkout_fields['account']['account_password']['priority'] = 0;
		}

		return $checkout_fields;
	}

	/**
	 * Registration fields validation
	 *
	 * @param object $validation_errors current validation errors.
	 * @return object
	 */
	public function validate_registration_fields( $validation_errors ) {
		$b2b_groups = RGN_Helper::get_net_tax_groups();

		if ( isset( $b2b_groups ) && ! empty( $b2b_groups ) ) {
			foreach ( $b2b_groups as $group ) {

				if ( isset( $group ) && ! empty( $group ) ) {
					if ( $group == $_POST['b2b_role'] ) {

						/* check uid post */
						if ( empty( $_POST['b2b_uid'] ) ) {
							$validation_errors->add( 'b2b_uid_error', __( 'VAT is required!', 'b2b-market' ) );
						} else {
							/* check uid validation */
							$validator = new RGN_VAT_Validator( array(
								substr( $_POST['b2b_uid'], 0, 2 ),
								substr( $_POST['b2b_uid'], 2 ),
							) );

							if ( false === $validator->is_valid() ) {
								$validation_errors->add( 'b2b_uid_error', __( 'VAT is not valid!', 'b2b-market' ) );
							}
						}

						if ( $required_fields ) {
							if ( empty( $_POST['b2b_company_registration_number'] ) ) {
								$validation_errors->add( 'b2b_company_registration_number_error', __( 'Company registration number is required!', 'b2b-market' ) );
							}
						}
					}
				}
			}
		}

		return $validation_errors;
	}

	/**
	 * Save registration fields to account
	 *
	 * @param int $customer_id current customer id.
	 * @return void
	 */
	public function save_registration_fields( $customer_id ) {

		if ( isset( $_POST['b2b_uid'] ) && ! empty( isset( $_POST['b2b_uid'] ) ) ) {
			update_user_meta( $customer_id, 'b2b_uid', sanitize_text_field( $_POST['b2b_uid'] ) );
		}

		if ( isset( $_POST['b2b_company_registration_number'] ) && ! empty( isset( $_POST['b2b_company_registration_number'] ) ) ) {
			update_user_meta( $customer_id, 'b2b_company_registration_number', sanitize_text_field( $_POST['b2b_company_registration_number'] ) );
		}

		if ( isset( $_POST['b2b_role'] ) && ! empty( $_POST['b2b_role'] ) && $_POST['b2b_role'] != 'customer' ) {
			$user = new WP_User( $customer_id );
			$user->remove_role( 'customer' );
			$user->add_role( sanitize_text_field( $_POST['b2b_role'] ) );
		}

	}
}

new RGN_Registration();


