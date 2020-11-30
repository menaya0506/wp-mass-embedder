<?php
/**
 * Admin Action plugin file.
 *
 * @package AMVE\Admin\Actions
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Search for videos in Ajax or PHP call.
 *
 * @since 1.0.0
 *
 * @param mixed $params       Array of parameters if this function is called in PHP.
 * @return void|array $output New post ID if success, -1 if not. Returned only if this function is called in PHP.
 */
function amve_search_videos( $params = '' ) {
	$ajax_call = '' === $params;

	if ( $ajax_call ) {
		check_ajax_referer( 'ajax-nonce', 'nonce' );
		$params = $_POST;
	}

	$search_videos = new AMVE_search_videos( $params );
	$errors        = array();

	if ( $search_videos->has_errors() ) {
		$errors = $search_videos->get_errors();
	}

	$videos = $search_videos->get_videos();

	if ( ! $ajax_call ) {
		return $videos;
	}

	$searched_data = $search_videos->get_searched_data();

	wp_send_json(
		array(
			'videos'        => $videos,
			'searched_data' => $searched_data,
			'errors'        => $errors,
		)
	);

	wp_die();
}
add_action( 'wp_ajax_amve_search_videos', 'amve_search_videos' );
