<?php
/**
 * Admin Action plugin file.
 *
 * @package AMVE\Admin\Actions
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Import videos in Ajax or PHP call.
 *
 * @param mixed $params       Array of parameters if this function is called in PHP.
 * @return void|array $output New post ID if success, -1 if not. Returned only if this function is called in PHP.
 */
function amve_import_video( $params = '' ) {
	$ajax_call = '' === $params;

	if ( $ajax_call ) {
		check_ajax_referer( 'ajax-nonce', 'nonce' );
		$params = $_POST;
	}

	if ( ! isset( $params['partner_id'], $params['video_infos'], $params['status'], $params['feed_id'], $params['cat_s'], $params['cat_wp'] ) ) {
		wp_die( 'Some parameters are missing!' );
	}

	// get custom post type.
	$custom_post_type = xbox_get_field_value( 'amve-options', 'custom-video-post-type' );
	if ( ! isset( $params['video_infos']['title'] ) || '' === $params['video_infos']['title'] ) {
		$params['video_infos']['title'] = 'Untitled';
	}

	// prepare post data.
	$post_args = array(
		'post_author'  => '1',
		'post_status'  => '' !== $params['status'] ? $params['status'] : xbox_get_field_value( 'amve-options', 'default-status' ),
		'post_type'    => '' !== $custom_post_type ? $custom_post_type : 'post',
		'post_title'   => $params['video_infos']['title'],
		'post_content' => isset( $params['video_infos']['desc'] ) ? $params['video_infos']['desc'] : '',
	);

	// insert post.
	$post_id = wp_insert_post( $post_args );

	// add post metas & taxonomies.
	if ( $post_id ) {
		// add partner id.
		update_post_meta( $post_id, 'partner', (string) $params['partner_id'] );
		// add video id.
		update_post_meta( $post_id, 'video_id', (string) $params['video_infos']['id'] );
		// add main thumb.
		update_post_meta( $post_id, 'thumb', (string) $params['video_infos']['thumb_url'] );
		// add partner_cat.
		update_post_meta( $post_id, 'partner_cat', (string) $params['cat_s'] );
		// add feed.
		update_post_meta( $post_id, 'feed', (string) $params['feed_id'] );
		// add video length.
		$custom_duration = xbox_get_field_value( 'amve-options', 'custom-duration' );
		update_post_meta( $post_id, '' !== $custom_duration ? $custom_duration : 'duration', (string) $params['video_infos']['duration'] );
		// add embed player.
		$custom_embed_player = xbox_get_field_value( 'amve-options', 'custom-embed-player' );
		update_post_meta( $post_id, '' !== $custom_embed_player ? $custom_embed_player : 'embed', (string) $params['video_infos']['embed'] );
		// add video url.
		$custom_video_url = xbox_get_field_value( 'amve-options', 'custom-video-url' );
		update_post_meta( $post_id, '' !== $custom_video_url ? $custom_video_url : 'video_url', (string) $params['video_infos']['video_url'] );
		// add tracking url.
		$custom_tracking_url = xbox_get_field_value( 'amve-options', 'custom-tracking-url' );
		update_post_meta( $post_id, '' !== $custom_tracking_url ? $custom_tracking_url : 'tracking_url', (string) $params['video_infos']['tracking_url'] );
		// add vr info.
		if ( in_array( 'vr', (array) explode( ',', strtolower( str_replace( ';', ',', $params['video_infos']['tags'] ) ) ), true ) ) {
			update_post_meta( $post_id, 'is_vr', 'yes' );
		} else {
			update_post_meta( $post_id, 'is_vr', 'no' );
		}
		// add category.
		$custom_taxonomy = xbox_get_field_value( 'amve-options', 'custom-video-categories' );
		wp_set_object_terms( $post_id, intval( $params['cat_wp'] ), '' !== $custom_taxonomy ? $custom_taxonomy : 'category', false );
		// add tags.
		$custom_tags = xbox_get_field_value( 'amve-options', 'custom-video-tags' );
		if ( '' === $custom_tags ) {
			$custom_tags = 'post_tag';
		}
		wp_set_object_terms( $post_id, explode( ',', str_replace( ';', ',', (string) $params['video_infos']['tags'] ) ), AMVE()->call_by_ref( $custom_tags ), false );
		// add actors.
		$custom_actors = xbox_get_field_value( 'amve-options', 'custom-video-actors' );
		if ( '' === $custom_actors ) {
			$custom_actors = 'actors';
		}
		if ( ! empty( $params['video_infos']['actors'] ) ) {
			wp_set_object_terms( $post_id, explode( ',', str_replace( ';', ',', (string) $params['video_infos']['actors'] ) ), AMVE()->call_by_ref( $custom_actors ), false );
		}
		// add thumbs.
		foreach ( (array) $params['video_infos']['thumbs_urls'] as $thumb ) {
			if ( ! empty( $thumb ) ) {
				add_post_meta( $post_id, 'thumbs', $thumb, false );
			}
		}
		// add trailer.
		update_post_meta( $post_id, 'trailer_url', (string) $params['video_infos']['trailer_url'] );

		// downloading main thumb.
		if ( 'on' === xbox_get_field_value( 'amve-options', 'import-thumb' ) ) {

			$default_thumb = (string) $params['video_infos']['thumb_url'];

			if ( strpos( $default_thumb, 'http' ) === false ) {
				$default_thumb = 'http:' . $default_thumb;
			}

			// magic sideload image returns an HTML image, not an ID.
			$media = AMVE()->media_sideload_image( $default_thumb, $post_id, null, $params['partner_id'] );

			// therefore we must find it so we can set it as featured ID.
			if ( ! empty( $media ) && ! is_wp_error( $media ) ) {
				$args = array(
					'post_type'      => 'attachment',
					'posts_per_page' => -1,
					'post_status'    => 'any',
					'post_parent'    => $post_id,
				);

				// reference new image to set as featured.
				$attachments = get_posts( $args );
				if ( isset( $attachments ) && is_array( $attachments ) ) {
					foreach ( $attachments as $attachment ) {
						// grab partner_id of full size images (so no 300x150 nonsense in path).
						$default_thumb = wp_get_attachment_image_src( $attachment->ID, 'full' );
						// determine if in the $media image we created, the string of the URL exists.
						if ( strpos( $media, $default_thumb[0] ) !== false ) {
							// if so, we found our image. set it as thumbnail.
							set_post_thumbnail( $post_id, $attachment->ID );
							// only want one image.
							break;
						}
					}
				}
			}
		}

		// post format video.
		set_post_format( $post_id, 'video' );

		$output = $params['video_infos']['id'];

	} else {
		$output = -1;
	}

	if ( ! $ajax_call ) {
		return $output;
	}

	wp_send_json( $output );

	wp_die();

}
add_action( 'wp_ajax_amve_import_video', 'amve_import_video' );
