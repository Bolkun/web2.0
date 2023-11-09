<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

echo "= " . $email_heading . " =\n\n";

echo sprintf( __( 'Thanks for creating a customer account on %s. Your username is %s. Please follow the activation link to activate your account:', 'b2b-market' ), esc_html ( get_bloginfo( 'name' ) ), '<strong>' . esc_html( $user_login ) . '</strong>' );

echo '\n\n' . $activation_link . '\n\n';

echo sprintf( __( 'If you haven\'t created an account on %s please ignore this email.', 'b2b-market' ), esc_html( get_bloginfo( 'name' ) ) );

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );
