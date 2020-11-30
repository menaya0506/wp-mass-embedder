<?php
/**
 * Admin Action plugin file.
 *
 * @package AMVE\Admin\Actions
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Remove a video in Ajax.
 *
 * @since 1.0.0
 *
 * @return void
 */
function amve_save_partner_options() {
	check_ajax_referer( 'ajax-nonce', 'nonce' );

	if ( ! isset( $_POST['partner_id'], $_POST['partner_options'] ) ) {
		wp_die( 'Some parameters are missing!' );
	}

	$partner_id      = sanitize_text_field( wp_unslash( $_POST['partner_id'] ) );
	$partner_options = $_POST['partner_options'];
	$is_configured   = true;

	foreach ( $partner_options as $option ) {
		if ( isset( $option['id'] ) ) {
			$options_to_save[ $option['id'] ] = $option['value'];
			if ( 'true' === $option['required'] && '' === $option['value'] ) {
				$is_configured = false;
			}
		}
	}

	WPSCORE()->update_product_option( 'AMVE', $partner_id . '_options', $options_to_save );

	wp_send_json( array( 'is_configured' => $is_configured ) );

	wp_die();
}
add_action( 'wp_ajax_amve_save_partner_options', 'amve_save_partner_options' );
