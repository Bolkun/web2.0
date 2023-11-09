<?php
/**
 * Plugin Name:  B2B Market
 * Plugin URI:   https://marketpress.de/shop/plugins/b2b-market/
 * Description:  B2B solution for WooCommerce with role-based pricing and simultaneous sales to B2B and B2C.
 * Version:      1.0.3
 * Author:       MarketPress
 * Author URI:   https://marketpress.de
 * Licence:      GPLv3
 * Text Domain:  b2b-market
 * Domain Path:  /languages
 * WC requires at least: 3.3.1
 * WC tested up to: 3.5
 */


define( 'B2B_PLUGIN_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'B2B_ADDON_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'addons' . DIRECTORY_SEPARATOR );
define( 'B2B_PLUGIN_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );

class BM {

	public static $version = false;

	/**
	 * BM constructor.
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'init' ) );
	}

	/**
	 * initialize plugin
	 */
	public function init() {

		// init plugin version
		$plugindata_import = get_file_data( __FILE__, array( 'version' => 'Version' ) );
		self::$version = $plugindata_import[ 'version' ];

		register_activation_hook( __FILE__, array( $this, 'set_activate_option' ) );

		/* register wizard */
		//add_action( 'admin_init', array( $this, 'set_activate_redirect' ) );

		/* check auto update */
		$this->check_auto_update();

		/* localize */
		$textdomain_dir = plugin_basename( dirname( __FILE__ ) ) . '/languages';
		load_plugin_textdomain( 'b2b-market', false, $textdomain_dir );

		/* check if woocommerce active */
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		if ( ! ( is_plugin_active( 'woocommerce/woocommerce.php' ) || is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) || function_exists( 'WC' ) ) ) {
			add_action( 'admin_notices', array( $this, 'get_activate_woocommerce_notice' ) );

			return;
		}
		/* check if rbp active */
		if ( is_plugin_active( 'woocommerce-role-based-prices/woocommerce-role-based-prices.php' ) || is_plugin_active_for_network( 'woocommerce-role-based-prices/woocommerce-role-based-prices.php' ) ) {
			update_option( 'bm_addon_import_and_export', 'on' );
			add_action( 'admin_notices', array( $this, 'get_activate_rbp_notice' ) );
		}

		/* boot admin */
		require_once( B2B_PLUGIN_PATH . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'class-bm-admin.php' );

		/* plugin improver */
		$this->plugin_improver();
	}

	/**
	 * marketpress plugin improve
	 */
	public function plugin_improver() {

		require_once untrailingslashit( plugin_dir_path(__FILE__) ) . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'marketpress-improve-plugin' . DIRECTORY_SEPARATOR . 'class-marketpress-improve-b2b-market.php';
       	$improve_german_market = new MarketPress_Improve_B2B_Market();

	}

	/**
	 * marketpress auto updater
	 */
	public function check_auto_update() {

		if ( ! class_exists( 'MarketPress_Auto_Update_B2B' ) ) {
			require_once( B2B_PLUGIN_PATH . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'marketpress-autoupdater' . DIRECTORY_SEPARATOR . 'class-MarketPress_Auto_Update_B2B.php' );
		}

		$plugindata_import             = get_file_data(
			__FILE__,
			array(
				'plugin_uri'  => 'Plugin URI',
				'plugin_name' => 'Plugin Name',
				'version'     => 'Version',
			)
		);
		$plugin_data                   = new stdClass();
		$plugin_data->plugin_slug      = 'b2b-market';
		$plugin_data->shortcode        = 'b2bm';
		$plugin_data->plugin_name      = $plugindata_import['plugin_name'];
		$plugin_data->plugin_base_name = plugin_basename( __FILE__ );
		$plugin_data->plugin_url       = $plugindata_import['plugin_uri'];
		$plugin_data->version          = $plugindata_import['version'];
		$autoupdate                    = new MarketPress_Auto_Update_B2B();

		$autoupdate->setup( $plugin_data );
	}


	/**
	 * Add admin notice if woocommerce is not activated
	 *
	 * @wp-hook admin_notices
	 * @return void
	 */
	public function get_activate_woocommerce_notice() {
		?>
		<div class="notice notice-success">
			<p><?php echo __( '<strong>WooCommerce is not active.</strong> In order to use B2B Market, please activate WooCommerce first.', 'b2b-market' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Add admin notice if rbp is activated
	 * @return void
	 */
	public function get_activate_rbp_notice() {

		$link = admin_url( 'admin.php?page=b2b-market&tab=import_and_export&sub_tab=migrator' );
		echo '<div class="notice notice-success"><p>';
		/* translators: %s: link to b2b-market migration settings */
		printf( __( '<strong>Role Based Prices is active</strong>. If you switch from RBP to B2B Market use our <a href="%s">migrator</a> and deactivate Role Based Prices after that.', 'b2b-market' ), $link );
		echo '</p></div>';
	}

	/**
	 * add options for activation
	 */
	public function set_activate_option( $networkwide ) {
		if ( is_multisite() && $networkwide ) {
			wp_die( '<p>' . __( 'B2B Market could not be activated networke wide due to the license restrictions. Please activate it on a subsite of your multisite.', 'b2b-market' ) . '</p>' );
		}
		add_option( 'b2b_market_active', true );
		add_option( 'bm_global_price_label', '' );
	}

	/**
	 * check if b2b market active, then redirect to wizard
	 */
	public function set_activate_redirect() {
		if ( get_option( 'b2b_market_active', false ) ) {
			delete_option( 'b2b_market_active' );
			wp_redirect( '?page=b2b-market-setup' );
			exit;
		}

	}
}

new BM();

