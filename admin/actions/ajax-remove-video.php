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
function amve_remove_video() {
	check_ajax_referer( 'ajax-nonce', 'nonce' );

	if ( ! isset( $_POST['video_id'], $_POST['partner_id'] ) ) {
		wp_die( 'Some parameters are missing!' );
	}

	$video_id           = sanitize_text_field( wp_unslash( $_POST['video_id'] ) );
	$partner_id         = sanitize_text_field( wp_unslash( $_POST['partner_id'] ) );
	$removed_videos_ids = WPSCORE()->get_product_option( 'AMVE', 'removed_videos_ids' );

	if ( ! is_array( $removed_videos_ids ) ) {
		$removed_videos_ids = array();
	}

	if ( ! isset( $removed_videos_ids[ $_POST['partner_id'] ] ) || ! is_array( $removed_videos_ids[ $_POST['partner_id'] ] ) ) {
		$removed_videos_ids[ $partner_id ] = array();
	}

	// add video id.
	$removed_videos_ids[ $partner_id ][] = $video_id;

	// remove duplicates.
	$removed_videos_ids[ $partner_id ] = array_unique( $removed_videos_ids[ $partner_id ], SORT_STRING );

	WPSCORE()->update_product_option( 'AMVE', 'removed_videos_ids', $removed_videos_ids );

	wp_die();
}
add_action( 'wp_ajax_amve_remove_video', 'amve_remove_video' );
