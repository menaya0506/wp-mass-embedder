<?php
/**
 * Config plugin file.
 *
 * @package AMVE\Main
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Define Constants
 */
define( 'AMVE_VERSION', '1.4.7' );
define( 'AMVE_DIR', plugin_dir_path( __FILE__ ) );
define( 'AMVE_URL', plugin_dir_url( __FILE__ ) );
define( 'AMVE_FILE', __FILE__ );

/**
 * Navigation config
 */
self::$config['nav'] = array(
	'30'           => array(
		'slug'     => 'amve-import-videos',
		'callback' => 'amve_import_videos_page',
		'title'    => 'Mass Embedder',
		'icon'     => 'fa-play-circle',
	),
	'amve-options' => array(
		'slug' => 'amve-options',
	),
);

/**
 * JS config
 */
self::$config['scripts']['js'] = array(
	// vendors.
	'AMVE_bootstrap-select.js' => array(
		'in_pages'  => array( 'amve-import-videos' ),
		'path'      => 'admin/vendors/bootstrap-select/bootstrap-select.min.js',
		'require'   => array(),
		'version'   => '1.12.4',
		'in_footer' => false,
	),
	'AMVE_vue-paginate.js'     => array(
		'in_pages'  => array( 'amve-import-videos' ),
		'path'      => 'admin/vendors/vue-paginate/vue-paginate.min.js',
		'require'   => array(),
		'version'   => '3.6.0',
		'in_footer' => false,
	),
	// pages.
	'AMVE_import-videos.js'    => array(
		'in_pages'  => array( 'amve-import-videos' ),
		'path'      => 'admin/pages/page-import-videos.js',
		'require'   => array(),
		'version'   => AMVE_VERSION,
		'in_footer' => false,
		'localize'  => array(
			'ajax'       => true,
			'objectL10n' => array(),
		),
	),
);

/**
 *  CSS config.
 */
self::$config['scripts']['css'] = array(
	// vendors.
	'AMVE_bootstrap-select.css' => array(
		'in_pages' => array( 'amve-import-videos' ),
		'path'     => 'admin/vendors/bootstrap-select/bootstrap-select.min.css',
		'require'  => array(),
		'version'  => '1.12.4',
		'media'    => 'all',
	),
	// assets.
	'AMVE_admin.css'            => array(
		'in_pages' => array( 'amve-import-videos' ),
		'path'     => 'admin/assets/css/admin.css',
		'require'  => array(),
		'version'  => AMVE_VERSION,
		'media'    => 'all',
	),
);
