<?php

abstract class MarketPress_Improve_Plugin {

	private static $api_url 			= 'http://marketpress.de/mp-improve/';
	private static $instance_counter 	= 0;

	protected $wp_version 				= null;
	protected $php_version 				= null;
	protected $mysql_version 			= null;
	protected $wc_version 				= null;

	protected $plugin_version 			= null;
	protected $plugin_slug 				= null;
	protected $plugin_data				= array();

	/**
	 * Construct
	 *
	 * @since 	1.0
	 * @final
	 * @return 	void
	 */
	final public function __construct() {

		// Only run in backend
		if ( ! is_admin() ) {
			return;
		}

		self::$instance_counter++;

		// Init Plugin Slug in any case
		$this->plugin_slug 	= $this->get_plugin_slug();

		// Return if we do not have to run
		if ( ! apply_filters( 'marketpress_improve_plugin_message_for_' . str_replace( '-', '_', $this->plugin_slug ), $this->have_to_run() ) ) {
			return;
		}

		// Init data
		$this->wp_version 	= function_exists( 'get_bloginfo' ) ? get_bloginfo( 'version' ) : '0';
		$this->php_version 	= function_exists( 'phpversion' ) ? phpversion() : '0';

		global $woocommerce;
		$this->wc_version 	= $woocommerce->version;

		global $wpdb;
		$this->mysql_version = '0';
		
		if ( ! empty( $wpdb->is_mysql ) ) {
			if ( $wpdb->use_mysqli ) {
				$this->mysql_version = mysqli_get_server_info( $wpdb->dbh );
			} else {
				$this->mysql_version = mysql_get_server_info( $wpdb->dbh );
			}
		}

		$this->plugin_name 		= $this->get_plugin_name();
		$this->plugin_version 	= $this->get_plugin_version();
		$this->plugin_data 		= $this->get_plugin_data();

		// Add hooks only once
		if ( self::$instance_counter == 1 ) {

			// Textdomain
			load_plugin_textdomain( 'marketpress-plugin-improve', false,  plugin_basename( dirname( __FILE__ ) ) . '/languages' );
			
			add_action( 'admin_init', array( $this, 'user_reaction' ) );

			// Admin Notice
			add_action( 'admin_notices', array( $this, 'admin_notice' ) );

		}

	}

	/**
	 * Shall the improver run in Backend and show the message
	 *
	 * @since 	1.0
	 * @final
	 * @return 	Boolean
	 */
	final private function have_to_run() {
		//delete_option( 'makretpress_improve_plugins' );
		$marketpress_improve_option = get_option( 'makretpress_improve_plugins', array() );

		// First time run
		if ( ! isset( $marketpress_improve_option[ $this->plugin_slug ] ) ) {
			
			$today = current_time( 'Y-m-d' );
			$marketpress_improve_option[ $this->plugin_slug ] = $today;
			update_option( 'makretpress_improve_plugins', $marketpress_improve_option );
			return false;
		
		} else {

			// We already have an user reaction
			if ( $marketpress_improve_option[ $this->plugin_slug ] == 'done' ) {
			
				return false;
			
			} else {

				$install_time 	= new DateTime( $marketpress_improve_option[ $this->plugin_slug ] );
				$today			= new DateTime();

				$install_time->add( new DateInterval( 'P14D' ) );

				return $install_time <= $today;

			}

		}


		return false;

	}

	/**
	 * Handle users reaction
	 *
	 * @since 	1.0
	 * @final
	 * @wp-hook admin-init
	 * @return 	void
	 */
	final public function user_reaction() {

		$user_reaction = false;

		if ( isset( $_REQUEST[ 'marketpress-agree-' . $this->plugin_slug ] ) ) {
			$response = $this->send_remote();
			$user_reaction = true;
		}

		if ( isset( $_REQUEST[ 'marketpress-disagree-' . $this->plugin_slug ] ) ) {
			$user_reaction = true;
		}

		if ( $user_reaction ) {
			$marketpress_improve_option = get_option( 'makretpress_improve_plugins', array() );
			$marketpress_improve_option[ $this->plugin_slug ] = 'done';
			update_option( 'makretpress_improve_plugins', $marketpress_improve_option );
		}


	}

	/**
	 * Display Admin Notice
	 *
	 * @since 	1.0
	 * @final
	 * @wp-hook admin_notices
	 * @return 	void
	 */
	final public function admin_notice() {

		if ( isset( $_REQUEST[ 'marketpress-agree-' . $this->plugin_slug ] ) || isset( $_REQUEST[ 'marketpress-disagree-' . $this->plugin_slug ] ) ) {
			
			if ( isset( $_REQUEST[ 'marketpress-agree-' . $this->plugin_slug ] ) ) {

				?>
				<div class="notice notice-success is-dismissible">
					<p><?php echo $this->get_notice_success_text(); ?></p>
				</div>
				<?php
			}

			return;
		}

		// Don't show up on wc settings pages
		if ( get_current_screen()->id == 'woocommerce_page_wc-settings' ) {
			return;
		}

		?>
		<div class="notice notice-warning">
        	
        	<p><?php echo $this->get_notice_text(); ?></p>

    		<table style="border: 1px solid #ddd; padding: 20px; display:none;">
        		
        		<tr>
        			<th colspan="2" style="text-align: right; margin-bottom: 20px;"><a class="marketpress-improve-hide-data" style="cursor: pointer;">X</th>
        		</tr>

        		<?php

        		$data = $this->get_data();

        		foreach ( $data[ 'global_data' ] as $key => $value ) {
        			?>
        			<tr>
        				<td style="padding-right: 10px;"><?php echo $key;?>:</td>
        				<td><?php echo $value;?></td>
        			</tr>
        			<?php
        		}

        		?>
        			<tr>
        				<td style="padding-top: 10px; padding-right: 10px; padding-top: 10px;">Plugin Slug:</td>
        				<td style="padding-top: 10px;"><?php echo $this->plugin_slug;?></td>
        			</tr>

        			<tr>
        				<td style="padding-bottom: 10px;padding-right: 10px;">Plugin Version:</td>
        				<td  style="padding-bottom: 10px;"><?php echo $this->plugin_version;?></td>
        			</tr>

        		<?php

        		foreach ( $data[ 'plugin_data' ][ 'Plugin Data' ] as $key => $value ) {
        			?>
        			<tr>
        				<td style="padding-right: 10px;"><?php echo $key;?>:</td>
        				<td><?php echo $value;?></td>
        			</tr>
        			<?php
        		}

        		?>

    		</table>

        	<p>
        		<form method="post" name="marketpress-improve-<?php echo $this->plugin_slug; ?>">
		        	<input type="submit" class="button-secondary" value="<?php echo $this->get_agree_button_text();?>" name="marketpress-agree-<?php echo $this->plugin_slug; ?>"/>
		        	<input type="submit" class="button-secondary" value="<?php echo $this->get_disagree_button_text();?>" name="marketpress-disagree-<?php echo $this->plugin_slug; ?>"/>
		        </form>
	        </p>
    	
    	</div>

    	<script>
			
			jQuery( '.marketpress-improve-show-data' ).click( function() {
				jQuery( this ).parent().parent().find( 'table' ).show();
			});

			jQuery( '.marketpress-improve-hide-data' ).click( function() {
				jQuery( this ).parent().parent().parent().parent().parent().find( 'table' ).hide();
			});
			
		</script>
    	<?php
	}

	/**
	 * Get Admin Notice Success Text
	 *
	 * @since 	1.0
	 * @return 	String
	 */
	protected function get_notice_success_text() {
		return sprintf( __( 'Thanks for supporting <strong>MarketPress</strong> to improve <strong>%s</strong>', 'marketpress-plugin-improve' ), $this->plugin_name );
	}

	/**
	 * Get Admin Notice Text
	 *
	 * @since 	1.0
	 * @return 	String
	 */
	protected function get_notice_text() {

		return sprintf( __( 'We at <strong>MarketPress</strong> want to constantly improve our plugin <strong>%s</strong>. We would be happy if you help us by sending data on your use of the plugin once. The data is anonymous, there is no conclusion on your used website or MarketPress licence.', 'marketpress-plugin-improve' ), $this->plugin_name ) . '<br /><br />' . __( 'Click <a style="cursor: pointer;" class="marketpress-improve-show-data">here</a> to show the data sent to MarketPress.', 'marketpress-plugin-improve' );

	}

	/**
	 * Get "Agree Button" text
	 *
	 * @since 	1.0
	 * @return 	String
	 */
	protected function get_agree_button_text() {
		return __( 'Yes, send data once', 'marketpress-plugin-improve' );
	}

	/**
	 * Get "Disagree Button" text
	 *
	 * @since 	1.0
	 * @return 	String
	 */
	protected function get_disagree_button_text() {
		return __( 'No, thanks.', 'marketpress-plugin-improve' );
	}

	/**
	 * Get Plugin Name
	 *
	 * @since 	1.0
	 * @abstract
	 * @return 	String
	 */
	abstract protected function get_plugin_name();

	/**
	 * Get Plugin Slug
	 *
	 * @since 	1.0
	 * @abstract
	 * @return 	String
	 */
	abstract protected function get_plugin_slug();

	/**
	 * Get Plugin Version
	 *
	 * @since 	1.0
	 * @abstract
	 * @return 	String
	 */
	abstract protected function get_plugin_version();

	/**
	 * Get Plugin Data
	 *
	 * @since 	1.0
	 * @abstract
	 * @return 	Array
	 */
	abstract protected function get_plugin_data();

	/**
	 * Get Data
	 *
	 * @since 	1.0
	 * @final
	 * @return 	Array
	 */
	final private function get_data() {

		return array(
			
			'global_data' => array(

				'WordPress Version'		=> $this->wp_version,
				'PHP Version'			=> $this->php_version,
				'MySQL Version'			=> $this->mysql_version,
				'WooCommerce Version'	=> $this->wc_version,

			),

			'plugin_data' => array(

				'Plugin Slug'			=> $this->plugin_slug,
				'Plugin Version'		=> $this->plugin_version,
				'Plugin Data'			=> $this->get_plugin_data(),

			)

		);

	}

	/**
	 * Send Remote
	 *
	 * @since 	1.0
	 * @final
	 * @return 	Array|WP_Error
	 */
	final private function send_remote() {

		$remote = wp_remote_post( self::$api_url, array( 'body' => $this->get_data() ) );

		if ( is_wp_error( $remote ) ) {
			return false;
		}

		$response = json_decode( wp_remote_retrieve_body( $remote ) );

		return $response;

	}

}
