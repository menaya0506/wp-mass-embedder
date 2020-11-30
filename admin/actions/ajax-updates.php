<?php
/**
 * Admin Action plugin file.
 *
 * @package AMVE\Admin\Actions
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Update 1.3.1
 * - Fix: Merged thumbnails imported in auto-import mode
 *
 * @since 1.3.1
 *
 * @return void
 */
function amve_update_1_3_1() {
	check_ajax_referer( 'ajax-nonce', 'nonce' );

	$args = array(
		'posts_per_page'   => 50,
		'meta_query'       => array(
			array(
				'key'     => 'thumbs',
				'value'   => ',',
				'compare' => 'LIKE',
			),
		),
		'post_type'        => 'any',
		'post_status'      => 'any',
		'suppress_filters' => true,
		'fields'           => 'ids',
	);

	$posts_ids = get_posts( $args );

	if ( count( $posts_ids ) > 0 ) {
		foreach ( $posts_ids as $post_id ) {
			$prev_thumbs = get_post_meta( $post_id, 'thumbs', true );
			$thumbs      = explode( ',', $prev_thumbs );
			delete_post_meta( $post_id, 'thumbs' );
			foreach ( $thumbs as $thumb ) {
				add_post_meta( $post_id, 'thumbs', $thumb );
			}
		}
	}
	$output = array(
		'update'   => '1.3.1',
		'nb_posts' => count( $posts_ids ),
	);
	wp_send_json( $output );

	wp_die();
}
add_action( 'wp_ajax_amve_update_1_3_1', 'amve_update_1_3_1' );

/**
 * Update 1.4.5
 * - Fix: Merged thumbnails imported in auto-import mode
 *
 * @since 1.4.5
 *
 * @return void
 */
function amve_update_1_4_5() {
	check_ajax_referer( 'ajax-nonce', 'nonce' );

	$args = array(
		'posts_per_page'   => 50,
		'meta_query'       => array(
			'relation' => 'AND',
			array(
				'key'     => 'partner',
				'value'   => 'txxx',
				'compare' => '=',
			),
			array(
				'key'     => 'updated_1_4_5',
				'compare' => 'NOT EXISTS',
			),
		),
		'post_type'        => 'any',
		'post_status'      => 'any',
		'suppress_filters' => true,
		'fields'           => 'ids',
	);

	$posts_ids = get_posts( $args );

	if ( count( $posts_ids ) > 0 ) {
		foreach ( $posts_ids as $post_id ) {
			$prev_thumb    = get_post_meta( $post_id, 'thumb', true );
			$repeated_http = explode( 'https://', $prev_thumb );
			$main_thumb    = 'https://' . end( $repeated_http );
			if ( count( $repeated_http ) < 2 ) {
				continue;
			}
			// update old thumb.
			update_post_meta( $post_id, 'thumb', $main_thumb );

			// update old thumbs.
			$prev_thumbs = get_post_meta( $post_id, 'thumbs', false );
			delete_post_meta( $post_id, 'thumbs' );
			foreach ( $prev_thumbs as $prev_thumb ) {
				$thumb = 'https://' . end( explode( 'https://', $prev_thumb ) );
				add_post_meta( $post_id, 'thumbs', $thumb );
			}
			update_post_meta( $post_id, 'updated_1_4_5', 1 );
		}
	}
	$output = array(
		'update'    => '1.4.4',
		'nb_posts'  => count( $posts_ids ),
		'posts_ids' => $posts_ids,
	);
	wp_send_json( $output );

	wp_die();
}
add_action( 'wp_ajax_amve_update_1_4_5', 'amve_update_1_4_5' );

