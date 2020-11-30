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
function amve_load_import_videos_data() {
	check_ajax_referer( 'ajax-nonce', 'nonce' );

	$feeds_array = array();
	$feeds       = AMVE()->get_feeds();

	if ( false !== $feeds ) {
		foreach ( $feeds as $feed ) {
			$feeds_array[] = $feed;
		}
	}
	$data = array(
		'feeds'             => $feeds_array,
		'objectL10n'        => AMVE()->get_object_l10n(),
		'partners'          => AMVE()->get_partners(),
		'videosLimit'       => xbox_get_field_value( 'amve-options', 'search-results' ),
		'WPCats'            => AMVE()->get_wp_cats(),
		'autoImportEnabled' => xbox_get_field_value( 'amve-options', 'amve-enable-auto-import' ),
	);
	wp_send_json( $data );
	wp_die();
}
add_action( 'wp_ajax_amve_load_import_videos_data', 'amve_load_import_videos_data' );
