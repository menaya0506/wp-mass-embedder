<?php
/**
 * Admin Action plugin file.
 *
 * @package AMVE\Admin\Actions
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Change feed status in Ajax.
 *
 * @since 1.0.0
 *
 * @return void
 */
function amve_change_feed_status() {
	check_ajax_referer( 'ajax-nonce', 'nonce' );

	if ( ! isset( $_POST['feed_id'], $_POST['new_value'] ) ) {
		wp_die( 'Some parameters are missing!' );
	}

	$feed_id   = sanitize_text_field( wp_unslash( $_POST['feed_id'] ) );
	$new_value = sanitize_text_field( wp_unslash( $_POST['new_value'] ) );

	$output = AMVE()->update_feed( $feed_id, array( 'status' => $new_value ) );

	wp_send_json( $output );

	wp_die();
}
add_action( 'wp_ajax_amve_change_feed_status', 'amve_change_feed_status' );
