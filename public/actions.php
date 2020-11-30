<?php
/**
 * Public Actions plugin file.
 *
 * @package AMVE\Public\Actions
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Filter on 'the_content' to insert the embed player in the content.
 *
 * @since 1.0.0
 *
 * @param string $content The content before the filter.
 * @return string $content The content after the filter.
 */
function amve_insert_video( $content ) {

	global $post;

	if ( ! is_object( $post ) ) {
		return $content;
	}

	$post_source       = get_post_meta( $post->ID, 'partner', true );
	$current_theme     = wp_get_theme();
	$player_in_content = xbox_get_field_value( 'amve-options', 'player-in-content' );

	if ( is_single() && 'WP-Script' !== $current_theme->get( 'Author' ) && 'on' === $player_in_content ) {
		$amve_partners = AMVE()->get_partners();
		if ( isset( $amve_partners[ $post_source ] ) ) {
			$embed           = get_post_meta( $post->ID, 'embed', true );
			$player_position = xbox_get_field_value( 'amve-options', 'player-position' );
			if ( 'before' === $player_position ) {
				$content = $embed . $content;
			} else {
				$content = $content . $embed;
			}
		}
	}
	return $content;
}
add_filter( 'the_content', 'amve_insert_video' );

/**
 * Sandbox 'post_metadata' iframes to prevent clicks and redirections.
 *
 * @since 1.3.5
 *
 * @param string $meta_value  The content before the filter.
 * @param int    $object_id   The post id.
 * @param string $meta_key    The custom post meta key.
 * @param bool   $single      If custom post single or not.
 *
 * @return mixed $meta_value  The content after the filter. Null if not changed.
 */
function amve_sandbox_iframes( $meta_value, $object_id, $meta_key, $single ) {
	if ( 'embed' === $meta_key ) {
		$meta_cache = wp_cache_get( $object_id, 'post_meta' );
		if ( ! $meta_cache ) {
			$meta_cache = update_meta_cache( 'post', array( $object_id ) );
			$meta_cache = $meta_cache[ $object_id ];
		}
		if ( isset( $meta_cache[ $meta_key ] ) ) {
			if ( $single ) {
				$meta_value = maybe_unserialize( $meta_cache[ $meta_key ][0] );
			} else {
				$meta_value = array_map( 'maybe_unserialize', $meta_cache[ $meta_key ] );
			}
			$meta_value                    = str_get_html( $meta_value );
			$excluded_sandbox_partners_ids = array(
				'txxx',
				'voyeurhit',
				'hclips',
				'theclassicporn',
				'hdzog',
				'tubepornclassic',
				'upornia',
				'hotmovs',
				'vjav',
				'thegay',
				'shemalez',
			);
			if ( false !== $meta_value ) {
				foreach ( $meta_value->find( 'iframe' ) as $iframe ) {
					if ( $iframe->src ) {
						$partner_id_from_iframe_url = strtolower( str_replace( array( 'www.', 'embed.' ), '', pathinfo( wp_parse_url( $iframe->src, PHP_URL_HOST ), PATHINFO_FILENAME ) ) );
						if ( in_array( $partner_id_from_iframe_url, $excluded_sandbox_partners_ids, true ) ) {
							continue;
						}
						$sandbox_partner_iframes = xbox_get_field_value( 'amve-options', 'sandbox-' . $partner_id_from_iframe_url . '-iframes' );
						$sandbox_partner_mobile_iframes = xbox_get_field_value( 'amve-options', 'sandbox-' . $partner_id_from_iframe_url . '-mobile-iframes' );
						if ( 'on' === $sandbox_partner_iframes ) {
							/* $iframe->sandbox = 'allow-same-origin allow-scripts'; */
							$iframe->sandbox = 'allow-same-origin allow-scripts';
						}
						if ( 'on' === $sandbox_partner_mobile_iframes && wp_is_mobile() ) {
							$iframe->sandbox = 'allow-same-origin allow-scripts';
						}
					}
				}
			}
		}
	}
	return $meta_value;
}
add_filter( 'get_post_metadata', 'amve_sandbox_iframes', 10, 4 );

