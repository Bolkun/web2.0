<?php

class RGN_UserMeta {

	/**
	 * RGN_UserMeta constructor.
	 */
	public function __construct() {

		add_action( 'show_user_profile', array( $this, 'add_customer_profile_fields' ) );
		add_action( 'edit_user_profile', array( $this, 'add_customer_profile_fields' ) );
		add_action( 'user_profile_update_errors', array( $this, 'validate_customer_profile_fields' ), 10, 3 );
		add_action( 'personal_options_update', array( $this, 'update_customer_profile_fields' ) );
		add_action( 'edit_user_profile_update', array( $this, 'update_customer_profile_fields' ) );
	}

	/**
	 * @param $user
	 */
	public function add_customer_profile_fields( $user ) {

		$uid                         = get_the_author_meta( 'b2b_uid', $user->ID );
		$company_registration_number = get_the_author_meta( 'b2b_company_registration_number', $user->ID );
		
		?>

		<h3><?php esc_html_e( 'B2B Market Registration', 'b2b-market' ); ?></h3>

		<table class="form-table">
			<tr>
				<th>
					<label for="b2b_uid"><?php esc_html_e( 'VAT-ID', 'b2b-market' ); ?></label>
				</th>
				<td>
					<input type="text" id="b2b_uid" name="b2b_uid" value="<?php echo esc_attr( $uid ); ?>"
					class="regular-text"/>
				</td>
			</tr>
			<tr>
				<th>
					<label for="b2b_company_registration_number"><?php esc_html_e( 'Company registration number', 'b2b-market' ); ?></label>
				</th>
				<td>
					<input type="text" id="b2b_company_registration_number" name="b2b_company_registration_number" value="<?php echo esc_attr( $company_registration_number ); ?>"
					class="regular-text"/>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * @param $errors
	 * @param $update
	 * @param $user
	 */
	public function validate_customer_profile_fields( $errors, $update, $user ) {

		if ( ! $update ) {
			return;
		}
	}

	/**
	 * @param $user_id
	 *
	 * @return bool
	 */
	public function update_customer_profile_fields( $user_id ) {

		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return false;
		}

		if ( isset( $_POST['b2b_uid'] ) && ! empty( $_POST['b2b_uid'] ) ) {
			update_user_meta( $user_id, 'b2b_uid', sanitize_text_field( $_POST['b2b_uid'] ) );
		}
		if ( isset( $_POST['b2b_company_registration_number'] ) && ! empty( $_POST['b2b_company_registration_number'] ) ) {
			update_user_meta( $user_id, 'b2b_company_registration_number', sanitize_text_field( $_POST['b2b_company_registration_number'] ) );
		}

		return $user_id;
	}
}

new RGN_UserMeta();
