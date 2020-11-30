<?php
/**
 * Admin Action plugin file.
 *
 * @package AMVE\Admin\Actions
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Toggle a feed for auto import in Ajax.
 *
 * @since 1.0.0
 *
 * @return void
 */
function amve_toggle_feed_auto_import() {
	check_ajax_referer( 'ajax-nonce', 'nonce' );

	if ( ! isset( $_POST['feed_id'], $_POST['new_value'] ) ) {
		wp_die( 'Some parameters are missing!' );
	}

	$_POST['new_value'] = $_POST['new_value'] == 'true' ? true : false;

	$output = AMVE()->update_feed( $_POST['feed_id'], array( 'auto_import' => $_POST['new_value'] ) );

	wp_send_json( $output );

	wp_die();
}
add_action( 'wp_ajax_amve_toggle_feed_auto_import', 'amve_toggle_feed_auto_import' );
