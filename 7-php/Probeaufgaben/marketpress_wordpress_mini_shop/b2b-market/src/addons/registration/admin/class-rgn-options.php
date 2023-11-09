<?php

class RGN_Options {

	/**
	 * IE_Options constructor.
	 */
	public function __construct() {
	}

	/**
	 * @param $items
	 *
	 * @return mixed
	 */
	public function add_menu_item( $items ) {

		$items[5] = array(
			'title'    => __( 'Registration', 'b2b-market' ),
			'slug'     => 'registration',
			'options'  => true,
			'callback' => array( $this, 'registration_tab' ),
		);

		return $items;

	}

	/**
	 * @return array|mixed|void
	 */
	public function registration_tab() {

		/* export */
		$options = array();
		$groups  = new BM_User();

		$guest_group = get_option( 'bm_guest_group' );

		$heading = array(
			'name' => __( 'Choose which groups users can register for', 'b2b-market' ),
			'type' => 'title',
			'id'   => 'register_options_title',
		);
		array_push( $options, $heading );

		foreach ( $groups->get_all_customer_groups() as $group ) {

			foreach ( $group as $key => $value ) {

				$customer_groups = array(
					'name'    => get_the_title( $value ),
					'id'      => 'register_' . $key,
					'type'    => 'bm_ui_checkbox',
					'default' => 'off',
				);
				if ( $value != $guest_group ) {
					array_push( $options, $customer_groups );
				}
			}
		}

		$end = array(
			'type' => 'sectionend',
			'id'   => 'register_options',
		);

		array_push( $options, $end );

		$heading_double_optin = array(
			'name' => __( 'Do you want to activate Double Opt-in?', 'b2b-market' ),
			'type' => 'title',
			'id'   => 'double_optin_title',
		);
		array_push( $options, $heading_double_optin );

		$double_optin = array(
			'name'    => __( 'Activate Double Opt-In', 'b2b-market' ),
			'id'      => 'bm_double_opt_in_customer_registration',
			'type'    => 'bm_ui_checkbox',
			'default' => 'off',
		);
		array_push( $options, $double_optin );

		$end = array(
			'type' => 'sectionend',
			'id'   => 'double_optin_options',
		);

		array_push( $options, $end );

		$heading_remove_label = array(
			'name' => __( 'Remove the label for Customer group?', 'b2b-market' ),
			'type' => 'title',
			'id'   => 'remove_label_title',
		);
		array_push( $options, $heading_remove_label );

		$remove_label = array(
			'name'    => __( 'Remove Label', 'b2b-market' ),
			'id'      => 'bm_remove_label_registration',
			'type'    => 'bm_ui_checkbox',
			'default' => 'off',
		);
		array_push( $options, $remove_label );

		array_push( $options, $end );

		array_push( $options, $end );

		$options = apply_filters( 'woocommerce_bm_ui_register_options', $options );

		return $options;

	}

}
