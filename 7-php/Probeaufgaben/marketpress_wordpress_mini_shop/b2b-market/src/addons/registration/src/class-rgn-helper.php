<?php

class RGN_Helper {

	/**
	 * @return array
	 */
	public static function get_selectable_groups() {

		$groups            = new BM_User();
		$all_groups        = $groups->get_all_customer_groups();
		$selectable_groups = array();

		if ( isset( $all_groups ) && ! empty( $all_groups ) ) {
			foreach ( $all_groups as $group ) {
				if ( isset( $group ) && ! empty( $group ) ) {
					foreach ( $group as $key => $value ) {
						if ( get_option( 'register_' . $key ) == 'on' ) {
							$selectable_groups[ $key ] = $value;
						}
					}
				}
			}
		}
		return $selectable_groups;
	}

	/**
	 * @return array
	 */
	public static function get_net_tax_groups() {

		$groups        = new BM_User();
		$all_groups    = $groups->get_all_customer_groups();
		$net_tax_group = array();

		if ( isset( $all_groups ) && ! empty( $all_groups ) ) {
			foreach ( $all_groups as $group ) {

				if ( isset( $group ) && ! empty( $group ) ) {
					foreach ( $group as $key => $group_id ) {
						$vat_type = get_post_meta( $group_id, 'bm_vat_type', true );

						if ( ! empty( $vat_type ) ) {
							if ( 'on' == $vat_type ) {
								array_push( $net_tax_group, $key );
							}
						}
					}
				}
			}
		}
		return $net_tax_group;
	}
}
