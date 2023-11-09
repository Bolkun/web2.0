<?php

class BM_Wizard {

	/**
	 * Current step
	 *
	 * @var string
	 */
	private $step = '';
	public $meta_prefix = 'bm';

	/**
	 * Steps for the setup wizard
	 *
	 * @var array
	 */
	private $steps = array();

	/**
	 * Hook in tabs.
	 */
	public function __construct() {

		require_once( B2B_PLUGIN_PATH . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'options' . DIRECTORY_SEPARATOR . 'BM_Options.php' );

		add_action( 'admin_menu', array( $this, 'admin_menus' ) );
		add_action( 'admin_init', array( $this, 'setup_wizard' ) );
	}

	/**
	 * Add admin menus/screens.
	 */
	public function admin_menus() {
		add_dashboard_page( '', '', 'manage_options', 'b2b-market-setup', '' );
	}

	/**
	 * Show the setup wizard.
	 */
	public function setup_wizard() {

		if ( empty( $_GET['page'] ) || 'b2b-market-setup' !== $_GET['page'] ) {
			return;
		}

		wp_enqueue_style( 'bm-wizard', untrailingslashit( plugins_url() ) . '/assets/bm-wizard.css', array(
			'dashicons',
			'install',
		), '1.0.2', 'all' );

		wp_enqueue_style( 'bm-admin', untrailingslashit( plugins_url() ) . '/assets/bm-admin.css', '1.0.2', 'all' );
		wp_enqueue_script( 'bm-wizard', untrailingslashit( plugins_url() ) . 'assets/bm-wizard.js', array( 'jquery' ), '1.0.2', false );

		wp_deregister_script( 'admin-bar' );
		wp_deregister_style( 'admin-bar' );
		remove_action( 'init', '_wp_admin_bar_init' );
		remove_action( 'wp_footer', 'wp_admin_bar_render', 1000 );
		remove_action( 'admin_footer', 'wp_admin_bar_render', 1000 );

		wp_footer();


		/* to add new steps add_filter to bm_add_steps and add callback functions */

		$default_steps = apply_filters( 'bm_add_steps', array(
			'b2b_customer_group' => array(
				'name'    => __( 'Customer Group', 'b2b-market' ),
				'view'    => array( $this, 'b2b_group_setup' ),
				'handler' => array( $this, 'b2b_group_setup_save' ),
			),
			'b2b_migration'      => array(
				'name'    => __( 'Migration', 'b2b-market' ),
				'view'    => array( $this, 'b2b_group_setup' ),
				'handler' => array( $this, 'b2b_group_setup_save' ),
			),
			'b2b_global'         => array(
				'name'    => __( 'Global Options', 'b2b-market' ),
				'view'    => array( $this, 'b2b_group_setup' ),
				'handler' => array( $this, 'b2b_group_setup_save' ),
			),
			'b2b_addons'         => array(
				'name'    => __( 'Addons', 'b2b-market' ),
				'view'    => array( $this, 'b2b_setup_addons' ),
				'handler' => array( $this, 'b2b_setup_addons_save' ),
			),
			'next_steps'         => array(
				'name'    => __( 'Ready!', 'b2b-market' ),
				'view'    => array( $this, 'b2b_setup_ready' ),
				'handler' => '',
			),
		) );


		$this->steps = apply_filters( 'woocommerce_setup_wizard_steps', $default_steps );
		$this->step  = isset( $_GET['step'] ) ? sanitize_key( $_GET['step'] ) : current( array_keys( $this->steps ) ); // WPCS: CSRF ok, input var ok.

		if ( ! empty( $_POST['save_step'] ) && isset( $this->steps[ $this->step ]['handler'] ) ) {
			call_user_func( $this->steps[ $this->step ]['handler'], $this );
		}

		ob_start();

		$this->setup_wizard_header();
		$this->setup_wizard_steps();
		$this->setup_wizard_content();
		$this->setup_wizard_footer();


		exit;
	}

	/**
	 * Get the URL for the next step's screen.
	 *
	 * @param string $step slug (default: current step).
	 *
	 * @return string       URL for next step if a next step exists.
	 *                      Admin URL if it's the last step.
	 *                      Empty string on failure.
	 * @since 3.0.0
	 */
	public function get_next_step_link( $step = '' ) {
		if ( ! $step ) {
			$step = $this->step;
		}

		$keys = array_keys( $this->steps );
		if ( end( $keys ) === $step ) {
			return admin_url();
		}

		$step_index = array_search( $step, $keys, true );
		if ( false === $step_index ) {
			return '';
		}

		return add_query_arg( 'step', $keys[ $step_index + 1 ], remove_query_arg( 'activate_error' ) );
	}

	/**
	 * Setup Wizard Header.
	 */
	public function setup_wizard_header() {

		?>
        <!DOCTYPE html>
        <html <?php language_attributes(); ?>>
        <head>
            <meta name="viewport" content="width=device-width"/>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
            <title><?php esc_html_e( 'B2B Market Setup Wizard', 'b2b-market' ); ?></title>
			<?php wp_print_scripts( 'wc-setup' ); ?>
			<?php do_action( 'admin_print_styles' ); ?>
			<?php do_action( 'admin_head' ); ?>
        </head>
        <body class="bm-setup wp-core-ui">
        <h1 id="bm-logo">
            <a href="https://marketpress.de/">
                <img src="<?php echo plugins_url(); ?>/assets/img/b2b-market.svg"
                     alt="B2B Market"/>
            </a>
        </h1>
		<?php
	}

	/**
	 * Setup Wizard Footer.
	 */
	public function setup_wizard_footer() {

		?>
		<?php if ( 'b2b_customer_group' === $this->step ) : ?>
            <a class="bm-return-to-dashboard"
               href="<?php echo esc_url( admin_url() ); ?>"><?php esc_html_e( 'Not right now', 'b2b-market' ); ?></a>

		<?php elseif ( 'next_steps' === $this->step ) : ?>
            <a class="bm-return-to-dashboard"
               href="<?php echo esc_url( admin_url() ); ?>"><?php esc_html_e( 'Return to your dashboard', 'b2b-market' ); ?></a>
		<?php else : ?>
            <a class="bm-return-to-dashboard"
               href="<?php echo esc_url( admin_url() ); ?>"><?php esc_html_e( 'Return to your dashboard', 'b2b-market' ); ?></a>
            <a class="bm-return-to-dashboard"
               href="<?php echo esc_url( $this->get_next_step_link() ); ?>"><?php esc_html_e( 'Skip this step', 'b2b-market' ); ?></a>

		<?php endif; ?>
        </body>
        </html>
		<?php

	}

	/**
	 * Output the steps.
	 */
	public function setup_wizard_steps() {
		$output_steps = $this->steps;
		?>
        <ol class="bm-setup-steps">
			<?php foreach ( $output_steps as $step_key => $step ) : ?>
                <li class="
					<?php
				if ( $step_key === $this->step ) {
					echo 'active';
				} elseif ( array_search( $this->step, array_keys( $this->steps ), true ) > array_search( $step_key, array_keys( $this->steps ), true ) ) {
					echo 'done';
				}
				?>
				"><?php echo esc_html( $step['name'] ); ?></li>
			<?php endforeach; ?>
        </ol>
		<?php
	}

	/**
	 * Output the content for the current step.
	 */
	public function setup_wizard_content() {
		echo '<div class="bm-setup-content">';
		if ( ! empty( $this->steps[ $this->step ]['view'] ) ) {
			call_user_func( $this->steps[ $this->step ]['view'], $this );
		}
		echo '</div>';
	}

	/**
	 * Initial "b2b group setup" step.
	 */
	public function b2b_group_setup() {

	?>
        <h1><?php esc_html_e( 'First Steps', 'b2b-market' ); ?></h1>

        <form method="post" class="bm-wizard-general-form">

			<?php wp_nonce_field( 'bm-setup' ); ?>

            <p class="b2b-setup"><?php esc_html_e( 'The following wizard will help you create a your start setup for B2B Market. Following this guide step-by-step and your ready to go to sell with B2B-Market.', 'b2b-market' ); ?></p>

            <h3><?php _e( 'Do you want to import some sample groups?', 'b2b-market' ); ?></h3>


            <table class="form-table">

                <tbody><tr valign="top" class="">
                    <th scope="row" class="titledesc">Activate Global Prices</th>
                    <td class="forminp forminp-checkbox">
                        <fieldset>
                            <legend class="screen-reader-text"><span>Activate Global Prices</span></legend>
                            <label class="switch" for="bm_global_activate_prices">
                                <input name="bm_global_activate_prices" id="bm_global_activate_prices" type="checkbox" class="" value="on" checked="checked">
                                <div class="slider round"></div>

                            </label>
                        </fieldset>
                    </td>
                </tr>
                </tbody>
            </table>


            <p class="bm-setup-actions step">
                <button type="submit" class="button-primary button button-large button-next"
                        value="<?php esc_attr_e( "Let's go!", 'b2b-market' ); ?>"
                        name="save_step"><?php esc_html_e( "Next", 'b2b-market' ); ?></button>
            </p>
        </form>
		<?php
	}

	/**
	 * Save initial b2b market settings.
	 */
	public function b2b_group_setup_save() {
		check_admin_referer( 'bm-setup' );

		// $group_name = sanitize_text_field( $_POST['b2b_group'] );

		/* todo: create post and update meta
		* todo:  or/and create samples
		*/

		wp_safe_redirect( esc_url_raw( $this->get_next_step_link() ) );

		exit;
	}

	/**
	 * Addon Step.
	 */
	public function b2b_setup_addons() { ?>

        <h1><?php esc_html_e( 'Addons', 'b2b-market' ); ?></h1>

        <form method="post" class="bm-wizard-addon-form">
            <p class="b2b-setup"><?php esc_html_e( 'The following wizard will help you create a your start setup for B2B Market. Following this guide step-by-step and your ready to go to sell with B2B-Market.', 'b2b-market' ); ?></p>

            <table class="form-table">
                <tbody>
                <tr>
                    <th scope="row" class="titledesc">
                        <label for="b2b_register"><?php _e( 'Activate B2B Registration', 'b2b-market' ); ?></label>
                    </th>
                    <td class="forminp forminp-checkbox">
                        <input name="b2b_register" id="b2b_register" type="checkbox">
                    </td>
                </tr>
                <tr>
                    <th scope="row" class="titledesc">
                        <label for="b2b_import"><?php _e( 'Activate Import/Exprt', 'b2b-market' ); ?></label>
                    </th>
                    <td class="forminp forminp-checkbox">
                        <input name="b2b_import" id="b2b_import" type="checkbox">
                    </td>
                </tr>
                <tr>
                    <th scope="row" class="titledesc">
                        <label for="b2b_payment"><?php _e( 'Activate Conditional Payment & Shipping', 'b2b-market' ); ?></label>
                    </th>
                    <td class="forminp forminp-checkbox">
                        <input name="b2b_payment" id="b2b_payment" type="checkbox">
                    </td>
                </tr>
                </tbody>
            </table>

            <p class="bm-setup-actions step">
                <button type="submit" class="button-primary button button-large button-next"
                        value="<?php esc_attr_e( 'Continue', 'b2b-market' ); ?>"
                        name="save_step"><?php esc_html_e( 'Done. Go further.', 'b2b-market' ); ?></button>
				<?php wp_nonce_field( 'bm-setup' ); ?>
            </p>
        </form>
		<?php
	}

	/**
	 * Addons Step save.
	 */
	public function b2b_setup_addons_save() {
		check_admin_referer( 'bm-setup' );

		/* todo: update options */

		wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
		exit;
	}

	/**
	 * Final step.
	 */
	public function b2b_setup_ready() {
		$videos_url = '';
		$docs_url   = '';
		$help_text  = sprintf( __( 'Watch our <a href="%1$s" target="_blank">guided tour videos</a> to learn more about B2B Market, and visit marketpress.de to learn more about <a href="%2$s" target="_blank">getting started</a>.', 'b2b-market' ), $videos_url, $docs_url );
		?>

        <h1><?php esc_html_e( "You're ready to start.", 'b2b-market' ); ?></h1>

        <ul class="bm-wizard-next-steps">
            <li class="bm-wizard-next-step-item">
                <div class="bm-wizard-next-step-description">
                    <p class="next-step-heading"><?php esc_html_e( 'Next step', 'b2b-market' ); ?></p>
                    <h3 class="next-step-description"><?php esc_html_e( 'Read the guides', 'b2b-market' ); ?></h3>
                    <p class="next-step-extra-info"><?php esc_html_e( "Read the offical docs and unleash your super-power!", 'b2b-market' ); ?></p>
                </div>
                <div class="bm-wizard-next-step-action">
                    <p class="bm-setup-actions step">
                        <a class="button button-primary button-large"
                           href="#">
							<?php esc_html_e( 'Docs', 'b2b-market' ); ?>
                        </a>
                    </p>
                </div>
            </li>
            <li class="bm-wizard-next-step-item">
                <div class="bm-wizard-next-step-description">
                    <p class="next-step-heading"><?php esc_html_e( 'Next step', 'b2b-market' ); ?></p>
                    <h3 class="next-step-description"><?php esc_html_e( 'Check the settings', 'b2b-market' ); ?></h3>
                    <p class="next-step-extra-info"><?php esc_html_e( "Go to the settings page and configure your addon details", 'b2b-market' ); ?></p>
                </div>
                <div class="bm-wizard-next-step-action">
                    <p class="bm-setup-actions step">
                        <a class="button button-primary button-large"
                           href="#">
							<?php esc_html_e( 'Settings', 'b2b-market' ); ?>
                        </a>
                    </p>
                </div>
            </li>
        </ul>
        <p class="next-steps-help-text"><?php echo wp_kses_post( $help_text ); ?></p>
		<?php
	}
}

new BM_Wizard();

