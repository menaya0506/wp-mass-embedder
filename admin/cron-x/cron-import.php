<?php
/**
 * Admin Cron Import file.
 *
 * @package AMVE\Admin\Cron
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Function to import videos in auto-pilot.
 * Native cron callback.
 *
 * @return void|bool false if $ae_feeds == 0
 */
function AMVE_cron_import() {
	// auto-import start.
	WPSCORE()->write_log( 'notice', 'AUTO-IMPORT: Start at ' . current_time( 'mysql' ), __FILE__, __LINE__ );
	$feeds    = AMVE()->get_feeds();
	$ae_feeds = array();
	// find out if no feed is set to auto-import.
	foreach ( (array) $feeds as $feed ) {
		if ( $feed['auto_import'] == true ) {
			$ae_feeds[] = $feed;
		}
	}
	unset( $feeds );
	if ( count( $ae_feeds ) == 0 ) {
		WPSCORE()->write_log( 'notice', 'AUTO-IMPORT: No feed has Auto-import set to <code>Enabled</code>', __FILE__, __LINE__ );
		return false;
	} else {
		usort( $ae_feeds, 'AMVE_sort_array_by_date' );
		foreach ( (array) $ae_feeds as $feed ) {
			$partner           = AMVE()->get_partner( $feed['partner_id'] );
			$is_updatable_feed = $partner['is_configured'];
			if ( $is_updatable_feed ) {
				$cat_s = str_replace( ' ', $partner['filters']['search_sep'], $feed['partner_cat'] );
				if ( strpos( $cat_s, 'kw::' ) !== false ) {
					$kw    = 1;
					$cat_s = str_replace( 'kw::', '', $cat_s );
				} else {
					$kw = 0;
				}
				$feed_id              = $feed['id'];
				$kw                   = strpos( $cat_s, 'kw::' ) !== false ? 1 : 0;
				$method               = 'update';
				$original_cat_s       = str_replace( '&', '%%', $cat_s );
				$partner_id           = $partner['id'];
				$status               = $feed['status'];
				$wp_cat               = $feed['wp_cat'];
				$search_videos_params = array(
					'cat_s'          => $cat_s,
					'from'           => 'cron',
					'kw'             => $kw,
					'feed_id'        => $feed_id,
					'limit'          => xbox_get_field_value( 'amve-options', 'auto-import-amount' ),
					'method'         => $method,
					'original_cat_s' => $original_cat_s,
					'partner'        => $partner,
				);
				// search videos.
				$videos_found = AMVE_search_videos( $search_videos_params );
				if ( count( $videos_found ) == 0 ) {
					WPSCORE()->write_log( 'notice', 'AUTO-IMPORT: No new video found with Feed ID <code>' . $feed['id'] . '</code>', __FILE__, __LINE__ );
					WPSCORE()->write_log( 'notice', 'AUTO-IMPORT: Auto-import for Feed ID <code>' . $feed['id'] . '</code> has been set to Disabled', __FILE__, __LINE__ );
					AMVE()->update_feed( $feed['id'], array( 'auto_import' => false ) );
					break;
				}
				// foreach video found.
				$total_videos = 0;
				foreach ( $videos_found as $video_infos ) {
					$video_params = array(
						'cat_s'       => $cat_s,
						'cat_wp'      => $wp_cat,
						'feed_id'     => $feed_id,
						'kw'          => $kw,
						'method'      => $method,
						'partner_id'  => $partner_id,
						'status'      => $status,
						'video_infos' => $video_infos,
					);
					// import video.
					if ( AMVE_import_video( $video_params ) !== -1 ) {
						++$total_videos;
					}
				}
				// update feed.
				$update_feed_data = array(
					'cat_s'        => $cat_s,
					'cat_wp'       => $wp_cat,
					'feed_id'      => $feed_id,
					'kw'           => $kw,
					'method'       => $method,
					'partner_id'   => $partner_id,
					'status'       => $status,
					'total_videos' => $total_videos,
				);
				AMVE_update_feed( $update_feed_data );
				WPSCORE()->write_log( 'success', 'AUTO-IMPORT: ' . count( $videos_found ) . ' videos imported with feed ID <code>' . $feed['id'] . '</code>', __FILE__, __LINE__ );
				break;
			}
		}
	}
	// auto-import end
	WPSCORE()->write_log( 'notice', 'AUTO-IMPORT: End at ' . current_time( 'mysql' ), __FILE__, __LINE__ );
}
add_action( 'AMVE_update_one_feed', 'AMVE_cron_import' );

// add_action( 'admin_init', 'AMVE_cron_import' );
add_filter( 'cron_schedules', 'AMVE_add_every_six_hours_cron_schedule' );
function AMVE_add_every_six_hours_cron_schedule( $schedules ) {
	$schedules['every_six_hours'] = array(
		'interval' => 21600, // 6 hours in seconds
		'display'  => __( 'Every 6 hours' ),
	);
	return $schedules;
}

function AMVE_sort_array_by_date( $a, $b ) {
	return strtotime( $a['last_update'] ) - strtotime( $b['last_update'] );
}
