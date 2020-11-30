<?php
/**
 * Plugin Name: Adult Mass Videos Embedder
 * Plugin URI: https://www.wp-script.com/plugins/adult-mass-videos-embedder/
 * Description: Tired of adding videos manually? Save time by importing thousands of Adult Videos from the best Porn Tubes in minutes!
 * Author: WP-Script
 * Author URI: https://www.wp-script.com
 * Version: 1.4.7
 * Text Domain: amve_lang
 * Domain Path: /languages
 *
 * @package AMVE\Main
 */

if ( ! class_exists( 'AMVE' ) ) {
	/**
	 * Singleton Class.
	 *
	 * @since 1.0.0
	 *
	 * @final
	 */
	final class AMVE {

		/**
		 * The instance of WPS MASS EMBEDDER plugin
		 *
		 * @var instanceof AMVE $instance
		 * @access private
		 * @static
		 */
		private static $instance;

		/**
		 * The config of WPS MASS EMBEDDER plugin
		 *
		 * @var array $config
		 * @access private
		 * @static
		 */
		private static $config;

		/**
		 * __clone method
		 *
		 * @return void
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'amve_lang' ), '1.0' );
		}

		/**
		 * __wakeup method
		 *
		 * @return void
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'amve_lang' ), '1.0' );
		}

		/**
		 * Instance method
		 *
		 * @since 1.0.0
		 *
		 * @return self::$instance
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof AMVE ) ) {
				include_once ABSPATH . 'wp-admin/includes/plugin.php';

				if ( ! is_plugin_active( 'wp-script-core/wp-script-core.php' ) ) {
					require plugin_dir_path( __FILE__ ) . 'admin/vendors/tgm-activation-x/plugin-activation.php';
					require plugin_dir_path( __FILE__ ) . 'admin/vendors/tgm-activation-x/class-tgm-plugin-activation.php';
				} else {
					self::$instance = new AMVE();
					require plugin_dir_path( __FILE__ ) . 'config.php';
					// add cron job.
					require plugin_dir_path( __FILE__ ) . 'admin/cron-x/cron-import.php';
					// add cron job.
					require plugin_dir_path( __FILE__ ) . 'admin/vendors/simple-html-dom-x/simple-html-dom.php';
					// load options file.
					require plugin_dir_path( __FILE__ ) . 'admin/pages/page-options-x.php';
					if ( is_admin() || wp_next_scheduled( 'AMVE_update_one_feed' ) ) {
						// load admin filters.
						self::$instance->load_admin_filters();
						// load admin hooks.
						self::$instance->load_admin_hooks();
						// auto-load admin php files.
						self::$instance->auto_load_php_files( 'admin' );
						// load admin features.
						self::$instance->admin_init();
						// load text domain.
						self::$instance->load_textdomain();
					}
					if ( ! is_admin() ) {
						// load public filters
						// self::$instance->load_public_filters();
						// auto-load admin php files.
						self::$instance->auto_load_php_files( 'public' );
					}
				}
			}
			return self::$instance;
		}

		/**
		 * Add js and css files, tabs, pages, php files in admin mode.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function load_admin_filters() {
			// add js and css files, tabs, pages, php files.
			add_filter( 'WPSCORE-scripts', array( $this, 'add_admin_scripts' ) );
			add_filter( 'WPSCORE-tabs', array( $this, 'add_admin_navigation' ) );
			add_filter( 'WPSCORE-pages', array( $this, 'add_admin_navigation' ) );
		}

		/**
		 * Add admin js and css scripts. This is a WPSCORE-scripts filter callback function.
		 *
		 * @since 1.0.0
		 *
		 * @param array $scripts List of all WPS CORE CSS / JS to load.
		 * @return array $scripts List of all WPS CORE + WPS MASS EMBEDDER CSS / JS to load.
		 */
		public function add_admin_scripts( $scripts ) {
			if ( isset( self::$config['scripts'] ) ) {
				if ( isset( self::$config['scripts']['js'] ) ) {
					$scripts += (array) self::$config['scripts']['js'];
				}
				if ( isset( self::$config['scripts']['css'] ) ) {
					$scripts += (array) self::$config['scripts']['css'];
				}
			}
			return $scripts;
		}

		/**
		 * Add WPS MASS EMBEDDER admin navigation tab. This is a WPSCORE-tabs and WPSCORE-pages filters callback function.
		 *
		 * @since 1.0.0
		 *
		 * @param array $nav List of all WPS CORE navigation tabs to add.
		 * @return array $nav List of all WPS CORE + WPS MASS EMBEDDER navigation tabs to add.
		 */
		public function add_admin_navigation( $nav ) {
			if ( isset( self::$config['nav'] ) ) {
				$nav += (array) self::$config['nav'];
			}
			return $nav;
		}

		/**
		 * Auto-loader for PHP files
		 *
		 * @since 1.0.0
		 *
		 * @param string{'admin','public'} $dir Directory where to find PHP files to load.
		 * @static
		 * @return void
		 */
		public function auto_load_php_files( $dir ) {
			$dirs = (array) ( plugin_dir_path( __FILE__ ) . $dir . '/' );
			foreach ( (array) $dirs as $dir ) {
				$files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir ) );
				if ( ! empty( $files ) ) {
					foreach ( $files as $file ) {
						// exlude dir.
						if ( $file->isDir() ) {
							continue; }
						// exlude index.php.
						if ( $file->getPathname() === 'index.php' ) {
							continue; }
						// exlude files != .php.
						if ( substr( $file->getPathname(), -4 ) !== '.php' ) {
							continue; }
						// exlude files from -x suffixed directories.
						if ( substr( $file->getPath(), -2 ) === '-x' ) {
							continue; }
						// exlude -x suffixed files.
						if ( substr( $file->getPathname(), -6 ) === '-x.php' ) {
							continue; }
						// else require file.
						require $file->getPathname();
					}
				}
			}
		}

		/**
		 * Registering WPS MASS EMBEDDER activation / deactivation / uninstall hooks.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function load_admin_hooks() {
			register_activation_hook( __FILE__, array( 'AMVE', 'activation' ) );
			register_deactivation_hook( __FILE__, array( 'AMVE', 'deactivation' ) );
			register_uninstall_hook( __FILE__, array( 'AMVE', 'uninstall' ) );
		}

		/**
		 * Stuff to do on WPS MASS EMBEDDER activation. This is a register_activation_hook callback function.
		 *
		 * @since 1.0.0
		 *
		 * @static
		 * @return void
		 */
		public static function activation() {
			WPSCORE()->update_client_signature();
			WPSCORE()->init( true );
		}

		/**
		 * Stuff to do on WPS MASS EMBEDDER deactivation. This is a register_deactivation_hook callback function.
		 *
		 * @since 1.0.0
		 *
		 * @static
		 * @return void
		 */
		public static function deactivation() {
			WPSCORE()->update_client_signature();
			wp_clear_scheduled_hook( 'AMVE_update_one_feed' );
			WPSCORE()->init( true );
		}

		/**
		 * Stuff to do on WPS MASS EMBEDDER deactivation. This is a register_deactivation_hook callback function.
		 *
		 * @since 1.0.0
		 *
		 * @static
		 * @return void
		 */
		public static function uninstall() {
			WPSCORE()->update_client_signature();
			wp_clear_scheduled_hook( 'AMVE_update_one_feed' );
			WPSCORE()->init( true );
		}

		/**
		 * Text domain function
		 *
		 * @since 1.0.0
		 *
		 * @return false by default
		 */
		private function load_textdomain() {
			// Set filter for plugin's languages directory.
			$lang_dir = dirname( plugin_basename( AMVE_FILE ) ) . '/languages/';

			// Traditional WordPress plugin locale filter.
			$mofile = sprintf( '%1$s-%2$s.mo', 'amve_lang', get_locale() );

			// Setup paths to current locale file.
			$mofile_local  = $lang_dir . $mofile;
			$mofile_global = WP_LANG_DIR . '/amve_lang/' . $mofile;

			if ( file_exists( $mofile_global ) ) {
				// Look in global /wp-content/languages/WPSCORE/ folder.
				load_textdomain( 'amve_lang', $mofile_global );
			} elseif ( file_exists( $mofile_local ) ) {
				// Look in local /wp-content/plugins/WPSCORE/languages/ folder.
				load_textdomain( 'amve_lang', $mofile_local );
			} else {
				// Load the default language files.
				load_plugin_textdomain( 'amve_lang', false, $lang_dir );
			}
			return false;
		}

		/**
		 * Load public filters.
		 *
		 * @since 1.0.0
		 *
		 * @return   void
		 */
		public function load_public_filters() {
			add_filter( 'WPSCORE-public_dirs', array( $this, 'add_public_dirs' ) );
		}

		/**
		 * Add public php files to require.
		 *
		 * @since 1.0.0
		 *
		 * @param array $public_dirs Array of public directories.
		 * @return array $public_dirs Array of public directories with the current plugin ones.
		 */
		public function add_public_dirs( $public_dirs ) {
			$public_dirs[] = plugin_dir_path( __FILE__ ) . 'public/';
			return $public_dirs;
		}



		/**
		 * Stuff to do on admin init.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		private function admin_init() {}

		/**
		 * Stuff to do on public init.
		 *
		 * @since 1.0.0
		 *
		 * @access private
		 * @return void
		 */
		private function public_init() {}

		/**
		 * Get all partners data.
		 *
		 * @since 1.0.0
		 *
		 * @access public
		 * @return array All the partners data.
		 */
		public function get_partners() {
			$i        = 0;
			$data     = WPSCORE()->get_product_data( 'AMVE' );
			$partners = $data['partners'];

			unset( $data );

			foreach ( (array) $partners as $partner_key => $partner_config ) {
				$is_configured = true;
				// adding options infos.
				if ( isset( $partner_config['options'] ) ) {
					$partner_id            = $partner_config['id'];
					$saved_partner_options = WPSCORE()->get_product_option( 'AMVE', $partner_id . '_options' );
					foreach ( (array) $partner_config['options'] as $key => $option ) {
						if ( isset( $option['id'] ) ) {
							$partners[ $partner_key ]['options'][ $key ]['value'] = isset( $saved_partner_options[ $option['id'] ] ) ? $saved_partner_options[ $option['id'] ] : '';
							if ( isset( $partners[ $partner_key ]['options'][ $key ]['required'] ) && true === $partners[ $partner_key ]['options'][ $key ]['required'] ) {
								if ( ! isset( $saved_partner_options[ $option['id'] ] ) ) {
									$is_configured = false;
								} else {
									if ( '' === $saved_partner_options[ $option['id'] ] ) {
										$is_configured = false;
									}
								}
							}
						}
					}
				}
				$partners[ $partner_key ]['is_configured'] = $is_configured;

				// ordering partners categories.
				$ordered_cats = array();
				foreach ( $partner_config['categories'] as $cat_id => $cat_name ) {
					if ( strpos( $cat_id, 'optgroup' ) !== false ) {
						$cat_id_explode     = explode( '::', $cat_id );
						$ordered_cats[ $i ] = array(
							'id'   => 'optgroup',
							'name' => end( $cat_id_explode ),
						);
						foreach ( (array) $cat_name as $sub_cat_id => $sub_cat_name ) {
							$ordered_cats[ $i ]['sub_cats'][] = array(
								'id'   => $sub_cat_id,
								'name' => $sub_cat_name,
							);
						}
					} else {
						$ordered_cats[ $i ] = array(
							'id'   => $cat_id,
							'name' => $cat_name,
						);
					}
					++$i;
				}
				$partners[ $partner_key ]['categories'] = $ordered_cats;
			}
			return (array) $partners;
		}

		/**
		 * Get a partner infos from a given partner id.
		 *
		 * @since 1.0.0
		 *
		 * @access public
		 * @param string $partner_id the partner id we want to retrieve the data from.
		 * @return array All the wanted partner infos.
		 */
		public function get_partner( $partner_id ) {
			$partners = $this->get_partners();
			return $partners[ $partner_id ];
		}

		/**
		 * Get all WordPress categories depending on the categories taxonomies defined in the options poage.
		 *
		 * @since 1.0.0
		 *
		 * @access public
		 * @return array The categories.
		 */
		public function get_wp_cats() {
			$custom_taxonomy = xbox_get_field_value( 'amve-options', 'custom-video-categories' );
			return (array) get_terms( '' !== $custom_taxonomy ? $custom_taxonomy : 'category', array( 'hide_empty' => 0 ) );
		}

		/**
		 * Get all saved feeds.
		 *
		 * @since 1.0.0
		 *
		 * @access public
		 * @return array|bool The saved feeds data array if success, false if not.
		 */
		public function get_feeds() {
			$saved_feeds = WPSCORE()->get_product_option( 'AMVE', 'feeds' );

			if ( ! is_array( $saved_feeds ) ) {
				$saved_feeds = array();
			}

			if ( empty( $saved_feeds ) ) {
				return false;
			}

			foreach ( (array) $saved_feeds as $feed_id => $feed_data ) {
				$more_data                               = explode( '__', $feed_id );
				$saved_feeds[ $feed_id ]['wp_cat']       = $more_data[0];
				$saved_feeds[ $feed_id ]['partner_id']   = $more_data[1];
				$saved_feeds[ $feed_id ]['partner_cat']  = $more_data[2];
				$saved_feeds[ $feed_id ]['id']           = $feed_id;
				$saved_feeds[ $feed_id ]['wp_cat_state'] = term_exists( intval( $saved_feeds[ $feed_id ]['wp_cat'] ) ) === null ? 0 : 1;
			}
			return (array) $saved_feeds;
		}

		/**
		 * Get a saved feed data from a given feed id.
		 *
		 * @since 1.0.0
		 *
		 * @access public
		 * @param string $feed_id The feed id we want to get get the data from.
		 * @return array|bool The saved feed data if success, false if not.
		 */
		public function get_feed( $feed_id ) {
			$feeds = $this->get_feeds();
			return isset( $feeds[ $feed_id ] ) ? $feeds[ $feed_id ] : false;
		}

		/**
		 * Update a feed from a given freed id and the new data to put.
		 *
		 * @since 1.0.0
		 *
		 * @access public
		 * @param string $feed_id  The feed id we want to update the data from.
		 * @param string $new_data The new data to put.
		 * @return bool true if everything works well, false if not.
		 */
		public function update_feed( $feed_id, $new_data ) {
			if ( ! isset( $feed_id, $new_data ) ) {
				return false;
			}

			$saved_feeds = WPSCORE()->get_product_option( 'AMVE', 'feeds' );

			if ( ! is_array( $saved_feeds ) ) {
				$saved_feeds = array();
			}

			foreach ( (array) $new_data as $key => $value ) {
				$saved_feeds[ $feed_id ][ $key ] = $value;
			}

			// if total videos <= 0, delete the feed.
			if ( ! isset( $saved_feeds[ $feed_id ]['total_videos'] ) || $saved_feeds[ $feed_id ]['total_videos'] <= 0 ) {
				unset( $saved_feeds[ $feed_id ] );
			}

			return WPSCORE()->update_product_option( 'AMVE', 'feeds', $saved_feeds );
		}

		/**
		 * Delete a feed from a given freed id..
		 *
		 * @since 1.0.0
		 *
		 * @access public
		 * @param string $feed_id The feed id we want to delete the data from.
		 * @return bool true if everything works well, false if not.
		 */
		public function delete_feed( $feed_id ) {
			if ( ! isset( $feed_id ) ) {
				return false;
			}

			$saved_feeds = WPSCORE()->get_product_option( 'AMVE', 'feeds' );
			if ( isset( $saved_feeds[ $feed_id ] ) ) {
				unset( $saved_feeds[ $feed_id ] );
			}
			return WPSCORE()->update_product_option( 'AMVE', 'feeds', $saved_feeds );
		}

		/**
		 * Get all expressions to translate.
		 *
		 * @since 1.0.0
		 *
		 * @access public
		 * @return array All expressions to translate.
		 */
		public function get_object_l10n() {
			return array(
				'and'                     => __( 'AND', 'amve_lang' ),
				'check_least'             => __( 'Check at least 1 video', 'amve_lang' ),
				'enable_button'           => __( 'to enable this button', 'amve_lang' ),
				'import'                  => __( 'Import', 'amve_lang' ),
				'or_keyword_if_available' => __( 'or a keyword (if it is available)', 'amve_lang' ),
				'search_feed'             => __( 'videos and save this search as a Feed. All your Feeds are displayed at the bottom of this page.', 'amve_lang' ),
				'select_cat_from'         => __( 'Select a category from', 'amve_lang' ),
				'select_wp_cat'           => __( 'Select a WordPress category', 'amve_lang' ),
			);
		}

		/**
		 * Return the reference of a given variable.
		 *
		 * @since 1.0.0
		 *
		 * @access public
		 * @param mixed $var The variable we want the reference from.
		 * @return mixed The reference of a variable.
		 */
		public function call_by_ref( &$var ) {
			return $var;
		}

		/**
		 * Overcharged media_sideload_image WordPress native function.
		 *
		 * @since 1.0.0
		 *
		 * @access public
		 * @param string     $file    The media filename.
		 * @param string|int $post_id The post id the mediafile is attached to.
		 * @param string     $desc    The description of t he media file.
		 * @param string     $source  unused. To remove.
		 * @return mixed The reference of a variable.
		 */
		public function media_sideload_image( $file, $post_id, $desc = null, $source ) {
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';

			if ( ! empty( $file ) ) {

				// Set variables for storage, fix file filename for query strings.
				preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $file, $matches );
				$tmp                = explode( '.', basename( $matches[0] ) );
				$file_ext           = end( $tmp );
				$file_array         = array();
				$file_array['name'] = sanitize_title( get_the_title( $post_id ) ) . '.' . $file_ext;
				unset( $tmp, $file_ext );

				// Download file to temp location.
				$file_array['tmp_name'] = download_url( $file );

				// If error storing temporarily, return the error.
				if ( is_wp_error( $file_array['tmp_name'] ) ) {
					return $file_array['tmp_name'];
				}

				// Do the validation and storage stuff.
				$id = media_handle_sideload( $file_array, $post_id, $desc );

				// If error storing permanently, unlink.
				if ( is_wp_error( $id ) ) {
					unlink( $file_array['tmp_name'] );
					return $id;
				}
				$src = wp_get_attachment_url( $id );
			}

			// Finally check to make sure the file has been saved, then return the HTML.
			if ( ! empty( $src ) ) {
				$alt  = isset( $desc ) ? esc_attr( $desc ) : '';
				$html = "<img src='$src' alt='$alt' />";
				return $html;
			}
		}

	}
}

if ( ! function_exists( 'AMVE' ) ) {
	/**
	 * Create the WPS MASS EMBEDDER instance in a function and call it.
	 *
	 * @return AMVE::instance();
	 */
	// phpcs:disable
	function AMVE() {
		return AMVE::instance();
	}
	AMVE();
}
