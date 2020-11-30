<?php
/**
 * Admin Action plugin file.
 *
 * @package AMVE\Admin\Actions
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Load partner cats in Ajax.
 *
 * @since 1.0.0
 *
 * @return void
 */
function amve_load_partner_cats() {
	check_ajax_referer( 'ajax-nonce', 'nonce' );

	if ( ! isset( $_POST['partner_id'], $_POST['method'], $_POST['partner_categories'] ) ) {
		wp_die( 'Some parameters are missing!' );
	}

	$partner_id         = sanitize_text_field( wp_unslash( $_POST['partner_id'] ) );
	$method             = sanitize_text_field( wp_unslash( $_POST['method'] ) );
	$partner_categories = $_POST['partner_categories'];
	$cats_used          = array();
	$cpt_valid_options  = 0;

	$feeds = AMVE()->get_feeds();

	foreach ( (array) $feeds as $feed ) {
		if ( $partner_id === $feed['partner_id'] ) {
			array_push( $cats_used, $feed['partner_cat'] );
		}
	}
	unset( $feeds );

	$output = array();
	$i      = 0;

	foreach ( (array) $partner_categories as $partner_category ) {
		$output[ $i ] = $partner_category;
		if ( 'optgroup' === $partner_category['id'] ) {
			foreach ( $partner_category['sub_cats'] as $index => $partner_sub_cat ) {
				$output[ $i ]['sub_cats'][ $index ]['disabled'] = in_array( $partner_sub_cat['id'], $cats_used, true );
			}
		} else {
			$output[ $i ]['disabled'] = in_array( $partner_category['id'], $cats_used, true );
		}
		$i++;
	}
	wp_send_json( $output );
	wp_die();
}
add_action( 'wp_ajax_amve_load_partner_cats', 'amve_load_partner_cats' );
