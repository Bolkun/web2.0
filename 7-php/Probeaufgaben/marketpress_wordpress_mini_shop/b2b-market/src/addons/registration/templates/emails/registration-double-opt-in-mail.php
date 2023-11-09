<?php
/**
 * Customer confirmation order email
 *
 * @author      MarketPress
 * @package     WooCommerce_German_Market
 * @version     2.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>

<?php do_action('woocommerce_email_header', $email_heading); ?>

<p>
	<?php echo sprintf( __( 'Thanks for creating a customer account on %s. Your username is %s. Please follow the activation link to activate your account:', 'b2b-market' ), esc_html ( get_bloginfo( 'name' ) ), '<strong>' . esc_html( $user_login ) . '</strong>' ); ?>
</p>

<p>
	<a href="<?php echo $activation_link; ?>"><?php echo $activation_link; ?></a>
</p>

<p>
	<?php echo sprintf( __( 'If you haven\'t created an account on %s please ignore this email.', 'b2b-market' ), esc_html( get_bloginfo( 'name' ) ) );?>
</p>


<?php do_action( 'woocommerce_email_footer' ); ?>
