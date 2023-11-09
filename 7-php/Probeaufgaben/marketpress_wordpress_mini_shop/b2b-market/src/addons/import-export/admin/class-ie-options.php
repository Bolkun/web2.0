<?php

class IE_Options {

	/**
	 * IE_Options constructor.
	 */
	public function __construct() {

		add_action( 'woocommerce_bm_ui_after_save_button', array( $this, 'export_button' ) );
	}

	/**
	 * @param $items
	 *
	 * @return mixed
	 */
	public function add_menu_item( $items ) {

		$items[4] = array(
			'title'   => __( 'Import and Export', 'b2b-market' ),
			'slug'    => 'import_and_export',
			'options' => false,
			'submenu' => array(
				array(
					'title'    => __( 'Export', 'b2b-market' ),
					'slug'     => 'export',
					'callback' => array( $this, 'export_tab' ),
					'options'  => 'yes',
				),
				array(
					'title'    => __( 'Import', 'b2b-market' ),
					'slug'     => 'import',
					'callback' => array( $this, 'import_tab' ),
					'options'  => 'yes',
				),
				array(
					'title'    => __( 'Migrator', 'b2b-market' ),
					'slug'     => 'migrator',
					'callback' => array( $this, 'migrator_tab' ),
					'options'  => 'yes',
				),
			),
		);

		return $items;

	}

	/**
	 * @return array|mixed|void
	 */
	public function export_tab() {

		/* export */
		$options = array();
		$groups  = new BM_User();

		$heading = array(
			'name' => __( 'Export Customer Groups', 'b2b-market' ),
			'type' => 'title',
			'id'   => 'export_options',
		);
		array_push( $options, $heading );

		foreach ( $groups->get_all_customer_groups() as $group ) {

			foreach ( $group as $key => $value ) {

				$customer_groups = array(
					'name'     => ucfirst( $key ),
					'desc_tip' => __( 'Include this group in your export', 'b2b-market' ),
					'id'       => 'export_' . $key,
					'type'     => 'bm_ui_checkbox',
					'default'  => 'on',
				);
				array_push( $options, $customer_groups );
			}
		}
		$end = array(
			'type' => 'sectionend',
			'id'   => 'export_options',
		);
		array_push( $options, $end );

		$plugin_settings_title = array(
			'name' => __( 'Export Plugin Settings', 'b2b-market' ),
			'type' => 'title',
			'id'   => 'export_plugin_settings_title',
		);
		array_push( $options, $plugin_settings_title );

		$plugin_settings = array(
			'name'     => __( 'Plugin Settings', 'b2b-market' ),
			'desc_tip' => __( 'Include plugin settings', 'b2b-market' ),
			'id'       => 'export_plugin_settings',
			'type'     => 'bm_ui_checkbox',
			'default'  => 'off',
		);
		array_push( $options, $plugin_settings );

		$export_data = array(
			'name'              => __( 'Export Data', 'b2b-market' ),
			//'desc_tip'          => __( 'After generate the export, you can copy and paste the code to another page on import settings', 'b2b-market' ),
			'id'                => 'export_options_raw_data',
			'type'              => 'textarea',
			'custom_attributes' => array(
				'rows' => '10',
				'cols' => '80',
			),
		);
		array_push( $options, $export_data );

		$end = array(
			'type' => 'sectionend',
			'id'   => 'export_options',
		);

		array_push( $options, $end );

		$options = apply_filters( 'woocommerce_bm_ui_export_options', $options );

		return $options;

	}

	/**
	 * @return array|mixed|void
	 */
	public function import_tab() {

		$options = array();

		$heading = array(
			'name' => __( 'Import', 'b2b-market' ),
			'type' => 'title',
			'id'   => 'import_options',
		);

		array_push( $options, $heading );

		$import_data = array(
			'name'              => __( 'Import Data', 'b2b-market' ),
			//'desc_tip'          => __( 'After generate the export, you can copy and paste the code to another page on import settings', 'b2b-market' ),
			'id'                => 'import_options_raw_data',
			'type'              => 'textarea',
			'custom_attributes' => array(
				'rows' => '10',
				'cols' => '80',
			),
		);

		array_push( $options, $import_data );

		$end = array(
			'type' => 'sectionend',
			'id'   => 'import_options_file_attachement',
		);

		array_push( $options, $end );

		$options = apply_filters( 'woocommerce_bm_ui_export_options', $options );

		return $options;

	}

	/**
	 * @return array|mixed|void
	 */
	public function migrator_tab() {

		$options = array();

		$heading = array(
			'name' => __( 'Migrate all Settings from your current installation of Role Based Prices', 'b2b-market' ),
			'type' => 'title',
			'id'   => 'migrator_options',
		);

		array_push( $options, $heading );

		$end = array(
			'type' => 'sectionend',
			'id'   => 'migrator_options_file_attachement',
		);

		array_push( $options, $end );

		$options = apply_filters( 'woocommerce_bm_ui_migrator_options', $options );

		return $options;

	}

	/**
	 * @param string $class
	 */
	public function export_button( $class = 'bottom' ) {

		if ( ! empty( $_REQUEST['tab'] ) ) :
			if ( 'import_and_export' == $_REQUEST['tab'] && empty( $_REQUEST['sub_tab'] ) || ! empty( $_REQUEST['sub_tab'] ) && 'import' != $_REQUEST['sub_tab'] && 'migrator' != $_REQUEST['sub_tab'] ) : ?>
			<div class="export-container">
				<a id="submit_export_groups" name="submit_export_groups" class="save-bm-options <?php echo $class; ?>"><?php _e( 'Export', 'b2b-market' ); ?></a>
				<p><?php _e( 'Please save your settings before click export', 'b2b-market' ); ?>.</p>
				<div class="modal"><h3><?php _e( 'Export complete', 'b2b-market' ); ?>.</h3><p><?php _e( 'Your export was successfull', 'b2b-market' ); ?>.</div>
			</div>
			<?php elseif ( 'import_and_export' == $_REQUEST['tab'] && ! empty( $_REQUEST['sub_tab'] ) && 'export' != $_REQUEST['sub_tab'] && 'migrator' != $_REQUEST['sub_tab'] ) : ?>
			<div class="import-container">
				<a id="submit_import_groups" name="submit_import_groups"
				class="save-bm-options <?php echo $class; ?>"><?php _e( 'Import', 'b2b-market' ); ?></a>
				<p><?php _e( 'Paste in your export data, save your settings and then import', 'b2b-market' ); ?></p>
				<div class="modal"><h3><?php _e( 'Import complete', 'b2b-market' ); ?>.</h3><p><?php _e( 'Your import was successfull', 'b2b-market' ); ?>.</div>
			</div>
			<?php elseif ( 'import_and_export' == $_REQUEST['tab'] && ! empty( $_REQUEST['sub_tab'] ) && 'import' != $_REQUEST['sub_tab'] && 'export' != $_REQUEST['sub_tab'] ) : ?>
			<a id="submit_migrate" name="submit_migrate"
			class="save-bm-options <?php echo $class; ?>"><?php _e( 'Migrate', 'b2b-market' ); ?></a>
			<style>input.save-bm-options.bottom, .save-bm-options.top { display: none;}</style>
			<div class="modal migrate"><h3><?php _e( 'Migration complete', 'b2b-market' ); ?>.</h3><p><?php _e( 'Your migration was successfull', 'b2b-market' ); ?>.</div>
			<?php endif; ?>
			<?php
		endif;
	}
}
