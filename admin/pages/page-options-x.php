<?php
/**
 * Options page.
 *
 * @package AMVE\Admin\Pages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'amve-options', 'amve_options_page' );
/**
 * This function is a ctpl-options filter callback to display WP-Script logo & tabs at the top of the Xbox options page.
 *
 * @param string $options_table - default options HTML string to render the Xbox options in the page.
 * @return $output
 */
function amve_options_page( $options_table ) {
	$output  = '<div id="wp-script"><div class="content-tabs">';
	$output .= WPSCORE()->display_logo( false );
	$output .= WPSCORE()->display_tabs( false );
	$output .= '
		<div class="tab-content tab-options">
			<div class="tab-pane fade in active" id="AMVE-options-tab">
				<div v-cloak>
					<ul class="list-inline">
						<li><a href="admin.php?page=amve-import-videos"><i class="fa fa-cloud-download"></i> ' . __( 'Import videos', 'amve_lang' ) . '</a></li>
						<li>|</li>
						<li class="active"><a href="admin.php?page=amve-options"><i class="fa fa-wrench"></i> ' . __( 'Options', 'amve_lang' ) . '</a></li>
					</ul>
				</div>
			</div>';
	$output .= $options_table;
	$output .= '</div></div>';
	$output .= WPSCORE()->display_footer( false );
	$output .= '</div>';
	return $output;
}
add_action( 'xbox_init', 'amve_options' );

/**
 * Set AMVE options.
 * xbox_init action callback to define all the plugin Xbox options.
 *
 * @return void
 */
function amve_options() {
	$options = array(
		'id'         => 'AMVE-options',
		'icon'       => XBOX_URL . 'img/xbox-light-small.png', // Menu icon.
		'skin'       => 'pink', // Skins: blue, lightblue, green, teal, pink, purple, bluepurple, yellow, orange'.
		'layout'     => 'boxed', // wide.
		'header'     => array(
			'icon' => '<img src="' . XBOX_URL . 'img/xbox-light.png"/>',
			'desc' => 'Customize here your Theme',
		),
		'capability' => 'edit_published_posts',
	);
	$xbox    = xbox_new_admin_page( $options );
	$xbox->add_main_tab(
		array(
			'name'  => 'Main tab',
			'id'    => 'main-tab',
			'items' => array(
				'general'             => '<i class="xbox-icon xbox-icon-gear"></i>General',
				'data-to-import'      => '<i class="xbox-icon xbox-icon-download"></i>Data to import',
				'theme-compatibility' => '<i class="xbox-icon xbox-icon-desktop"></i>Theme Compatibility',
				'iframes'             => '<i class="xbox-icon xbox-icon-code"></i>Sandbox Iframes',
			),
		)
	);
		/**
		 * GENERAL
		 */
		$xbox->open_tab_item( 'general' );
			$xbox->add_field(
				array(
					'id'         => 'search-results',
					'name'       => 'Search results',
					'type'       => 'number',
					'default'    => 60,
					'grid'       => '4-of-8',
					'options'    => array(
						'unit' => 'videos / results',
					),
					'attributes' => array(
						'min'  => 1,
						'max'  => 120,
						'step' => 1,
					),
					'desc'       => 'Choose the number of videos displayed for each search (6 - 120)',
				)
			);
			$xbox->add_field(
				array(
					'id'      => 'default-status',
					'name'    => esc_html__( 'Default Status', 'amve_lang' ),
					'type'    => 'radio',
					'default' => 'publish',
					'items'   => array(
						'draft'   => 'Draft',
						'publish' => 'Publish',
					),
					'desc'    => esc_html__( 'Choose the default status of the imported videos (This option can be changed individually for each saved feed)', 'amve_lang' ),
				)
			);
			$xbox->add_field(
				array(
					'name'    => esc_html__( 'Enable Auto-import', 'amve_lang' ),
					'id'      => 'amve-enable-auto-import',
					'type'    => 'switcher',
					'default' => 'on',
					'grid'    => '4-of-8',
					'desc'    => esc_html__( 'Enable auto-import features (Don\'t forget to set auto-import option to Enabled for any saved feed you want)', 'amve_lang' ),
				)
			);
			$xbox->open_mixed_field(
				array(
					'id'   => 'displayed-when:switch:amve-enable-auto-import:on:auto-import-settings',
					'name' => 'Auto import settings',
				)
			);
				$xbox->add_field(
					array(
						'id'         => 'auto-import-amount',
						'name'       => 'Amount of videos to import',
						'type'       => 'number',
						'default'    => 10,
						'grid'       => '4-of-8',
						'options'    => array(
							'unit' => 'videos / auto-import',
						),
						'attributes' => array(
							'min'  => 1,
							'max'  => 50,
							'step' => 1,
						),
						'desc'       => 'Choose how many videos to import (1 - 50)',
					)
				);
				$xbox->add_field(
					array(
						'name'    => esc_html__( 'Frequency', 'amve_lang' ),
						'id'      => 'amve-auto-import-frequency',
						'type'    => 'select',
						'desc'    => esc_html__( 'Choose how often to import videos', 'amve_lang' ),
						'default' => 'twicedaily',
						'items'   => array(
							'hourly'          => 'Every 1 hour',
							'every_six_hours' => 'Every 6 hours',
							'twicedaily'      => 'Every 12 hours',
							'daily'           => 'Every 24 hours',
						),
						'grid'    => '4-of-8-last',
					)
				);
				$xbox->add_field(
					array(
						'name'    => esc_html__( 'Server Cron', 'amve_lang' ),
						'id'      => 'auto-import-server-cron',
						'type'    => 'text',
						'desc'    => esc_html__( 'Copy this command and <a href="https://www.wp-script.com/setup-server-cron-job-wordpress/" target="_blank">setup your Server Cron job</a>', 'amve_lang' ),
						'default' => 'wget -qO- ' . site_url( '/wp-cron.php' ) . ' &> /dev/null',
					)
				);
			$xbox->close_mixed_field();

			$xbox->open_mixed_field(
				array(
					'name' => 'Proxy',
					'desc' => 'Use a proxy if your server IP has been banned by some partners',
				)
			);
				$xbox->add_field(
					array(
						'id'   => 'proxy-ip',
						'name' => 'IP Address',
						'type' => 'text',
						'grid' => '2-of-8',
						'desc' => 'Enter a valid Proxy IP',
					)
				);
				$xbox->add_field(
					array(
						'id'   => 'proxy-port',
						'name' => 'Port',
						'type' => 'text',
						'desc' => 'Enter a valid Proxy Port',
						'grid' => '2-of-8',
					)
				);
				$xbox->add_field(
					array(
						'id'   => 'proxy-user',
						'name' => 'User',
						'type' => 'text',
						'desc' => 'Enter the user name if auth required',
						'grid' => '2-of-8',
					)
				);
				$xbox->add_field(
					array(
						'id'   => 'proxy-password',
						'name' => 'Password',
						'type' => 'text',
						'desc' => 'Enter the password if auth required',
						'grid' => '2-of-8',
					)
				);
			$xbox->close_mixed_field();

		$xbox->close_tab_item( 'general' );

		/**
		 * DATA TO IMPORT
		 */
		$xbox->open_tab_item( 'data-to-import' );
			$xbox->add_field(
				array(
					'name'    => esc_html__( 'Title', 'amve_lang' ),
					'id'      => 'import-title',
					'type'    => 'switcher',
					'default' => 'on',
					'desc'    => esc_html__( 'Check if you want to import videos title', 'amve_lang' ),
				)
			);
			$xbox->add_field(
				array(
					'name'    => esc_html__( 'Main thumb file', 'amve_lang' ),
					'id'      => 'import-thumb',
					'type'    => 'switcher',
					'default' => 'on',
					'desc'    => esc_html__( 'Check if you want to download main thumb files (the thumb url will be saved in all cases)', 'amve_lang' ),
				)
			);
			$xbox->add_field(
				array(
					'name'    => esc_html__( 'Description', 'amve_lang' ),
					'id'      => 'import-description',
					'type'    => 'switcher',
					'default' => 'on',
					'desc'    => esc_html__( 'Check if you want to import videos description (when provided by the partner)', 'amve_lang' ),
				)
			);
			$xbox->add_field(
				array(
					'name'    => esc_html__( 'Tags', 'amve_lang' ),
					'id'      => 'import-tags',
					'type'    => 'switcher',
					'default' => 'on',
					'desc'    => esc_html__( 'Check if you want to import videos tags (when provided by the partner)', 'amve_lang' ),
				)
			);
			$xbox->add_field(
				array(
					'name'    => esc_html__( 'Actors', 'amve_lang' ),
					'id'      => 'import-actors',
					'type'    => 'switcher',
					'default' => 'on',
					'desc'    => esc_html__( 'Check if you want to import videos actors (when provided by the partner)', 'amve_lang' ),
				)
			);
		$xbox->close_tab_item( 'data-to-import' );

		/**
		 * THEME COMPATIBILITY
		 */
		$xbox->open_tab_item( 'theme-compatibility' );

			$xbox->add_tab(
				array(
					'name'  => 'Theme compatibility tabs',
					'id'    => 'theme-compatibility-tabs',
					'items' => array(
						'player-in-content' => 'Player in content',
						'custom-fields'     => 'Custom fields',
						'custom-post-type'  => 'Custom post type',
					),
				)
			);

				/* Tab: Player in content */
				$xbox->open_tab_item( 'player-in-content' );
					$xbox->add_field(
						array(
							'id'      => 'player-in-content',
							'name'    => esc_html__( 'Player in content', 'amve_lang' ),
							'type'    => 'switcher',
							'default' => 'off',
							'desc'    => esc_html__( 'Check if you want to display the video player in the content', 'amve_lang' ),
							'grid'    => '2-of-8',
						)
					);
					$xbox->open_mixed_field(
						array(
							'id'   => 'displayed-when:switch:player-in-content:on:player-settings',
							'name' => 'Player position',
						)
					);
						$xbox->add_field(
							array(
								'id'      => 'player-position',
								'type'    => 'radio',
								'default' => 'before',
								'items'   => array(
									'before' => 'Before the content',
									'after'  => 'After the content',
								),
								'desc'    => esc_html__( 'Choose where to display the video player in the content', 'amve_lang' ),
								'grid'    => '4-of-8',
							)
						);
					$xbox->close_mixed_field();
				$xbox->close_tab_item( 'player-in-content' );

				/* Tab: Custom Fields */
				$xbox->open_tab_item( 'custom-fields' );
					$xbox->add_field(
						array(
							'id'      => 'custom-thumbnail',
							'name'    => esc_html__( 'Thumbnail', 'amve_lang' ),
							'type'    => 'text',
							'default' => 'thumb',
							'grid'    => '3-of-6',
						)
					);

					$xbox->add_field(
						array(
							'id'      => 'custom-embed-player',
							'name'    => esc_html__( 'Embed player', 'amve_lang' ),
							'type'    => 'text',
							'default' => 'embed',
							'grid'    => '3-of-6',
						)
					);

					$xbox->add_field(
						array(
							'id'      => 'custom-video-url',
							'name'    => esc_html__( 'Video URL', 'amve_lang' ),
							'type'    => 'text',
							'default' => 'video_url',
							'grid'    => '3-of-6',
						)
					);

					$xbox->add_field(
						array(
							'id'      => 'custom-duration',
							'name'    => esc_html__( 'Duration', 'amve_lang' ),
							'type'    => 'text',
							'default' => 'duration',
							'grid'    => '3-of-6',
						)
					);

					$xbox->add_field(
						array(
							'id'      => 'custom-tracking-url',
							'name'    => esc_html__( 'Tracking URL', 'amve_lang' ),
							'type'    => 'text',
							'default' => 'tracking_url',
							'grid'    => '3-of-6',
						)
					);
				$xbox->close_tab_item( 'custom-fields' );

				/* Custom post type */
				$xbox->open_tab_item( 'custom-post-type' );
					$xbox->add_field(
						array(
							'id'      => 'custom-video-post-type',
							'name'    => esc_html__( 'Video custom post type name', 'amve_lang' ),
							'type'    => 'select',
							'default' => 'post',
							'items'   => amve_options_get_all_post_types(),
							'desc'    => esc_html__( 'Set the video custom post type used by your theme', 'amve_lang' ),
							'grid'    => '3-of-6',
						)
					);
					$xbox->add_field(
						array(
							'id'      => 'custom-video-categories',
							'name'    => esc_html__( 'Video custom categories', 'amve_lang' ),
							'type'    => 'select',
							'default' => 'category',
							'items'   => amve_options_get_all_taxonomies(),
							'desc'    => esc_html__( 'Set the video categories used by your theme', 'amve_lang' ),
							'grid'    => '3-of-6',
						)
					);
					$xbox->add_field(
						array(
							'id'      => 'custom-video-actors',
							'name'    => esc_html__( 'Video custom actors', 'amve_lang' ),
							'type'    => 'select',
							'default' => 'actors',
							'items'   => amve_options_get_all_taxonomies(),
							'desc'    => esc_html__( 'Set the video actors taxonomy used by your theme', 'amve_lang' ),
							'grid'    => '3-of-6',
						)
					);
					$xbox->add_field(
						array(
							'id'      => 'custom-video-tags',
							'name'    => esc_html__( 'Video custom tags', 'amve_lang' ),
							'type'    => 'select',
							'default' => 'post_tag',
							'items'   => amve_options_get_all_taxonomies(),
							'desc'    => esc_html__( 'Set the video tags used by your theme', 'amve_lang' ),
							'grid'    => '3-of-6',
						)
					);
				$xbox->close_tab_item( 'custom-post-type' );

			$xbox->close_tab( 'theme-compatibility-tabs' );

		$xbox->close_tab_item( 'theme-compatibility' );

		$xbox->open_tab_item( 'iframes' );
		$xbox->add_field(
			array(
				'id'   => 'block-iframes-redirections',
				'name' => esc_html__( 'Sandbox iframes (experimetal)', 'amve_lang' ),
				'type' => 'title',
				'desc' => esc_html__( 'Try to prevent iframe ads and redirections to the original tube site when your visitors click on their iframes on your site. Enabling those options may prevent the iframe to work properly.', 'amve_lang' ),
			)
		);
	$partners = AMVE()->get_partners();
	usort( $partners, 'amve_options_sort_by_popularity' );

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

	foreach ( (array) $partners as $partner ) {
		if ( in_array( $partner['id'], $excluded_sandbox_partners_ids, true ) ) {
			continue;
		}

		$xbox->open_mixed_field(
			array(
				'name' => esc_html__( 'Sandbox ' . $partner['name'] . ' iframes', 'amve_lang' ),
			)
		);
		$xbox->add_field(
			array(
				'id'      => 'sandbox-' . $partner['id'] . '-iframes',
				'name'    => 'Desktop',
				'type'    => 'switcher',
				'default' => 'off',
				'grid'    => '2-of-6',
			)
		);
		$xbox->add_field(
			array(
				'id'      => 'sandbox-' . $partner['id'] . '-mobile-iframes',
				'name'    => 'Mobile',
				'type'    => 'switcher',
				'default' => 'off',
				'grid'    => '2-of-6 last',
			)
		);
		$xbox->close_mixed_field();
	}
	$xbox->close_tabe_item( 'iframes' );

	$xbox->close_tab( 'main-tab' );
}

/**
 * Returns all post types for theme compatibilty options.
 * Exclude all built_in post types except Post.
 *
 * @since 1.3.5
 *
 * @return array The array with all post types.
 */
function amve_options_get_all_post_types() {
	$all_post_types = get_post_types();
	unset( $all_post_types['attachment'] );
	unset( $all_post_types['custom_css'] );
	unset( $all_post_types['customize_changeset'] );
	unset( $all_post_types['nav_menu_item'] );
	unset( $all_post_types['oembed_cache'] );
	unset( $all_post_types['wp_block'] );
	unset( $all_post_types['page'] );
	unset( $all_post_types['revision'] );
	unset( $all_post_types['user_request'] );
	asort( $all_post_types, SORT_STRING );
	return $all_post_types;
}

/**
 * Returns all post types for theme compatibilty options.
 * Exclude all built_in post types except category and post_tag.
 *
 * @since 1.3.5
 *
 * @return array The array with all post types.
 */
function amve_options_get_all_taxonomies() {
	$all_taxonomies = get_taxonomies();
	unset( $all_taxonomies['nav_menu'] );
	unset( $all_taxonomies['link_category'] );
	unset( $all_taxonomies['post_format'] );
	if ( ! isset( $all_taxonomies['actors'] ) ) {
		$all_taxonomies['actors'] = 'actors';
	}
	asort( $all_taxonomies, SORT_STRING );
	return $all_taxonomies;
}

/**
 * Helper function to sort Partners array by popularity.
 * Exemple of use: usort( $my_array, 'amve_options_sort_by_popularity' );
 *
 * @see usort()
 *
 * @since 1.3.5
 *
 * @param string $a Parameter A to sort the array.
 * @param string $b Parameter B to sort the array.
 * @return array The sorted array.
 */
function amve_options_sort_by_popularity( $a, $b ) {
	return $b['popularity'] - $a['popularity'];
}
