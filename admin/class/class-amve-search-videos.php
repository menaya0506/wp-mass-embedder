<?php
class AMVE_search_videos {

	private $params,
			$errors,
			$feed_url,
			$feed_infos,
			$videos,
			$searched_data,
			$wp_version,
			$partner_existing_videos_ids,
			$partner_unwanted_videos_ids;

	function __construct( $params ) {

		global $wp_version;

		$this->wp_version = $wp_version;
		$this->params     = $params;

		// connecting to API
		$api_params = array(
			'license_key'  => WPSCORE()->get_license_key(),
			'signature'    => WPSCORE()->get_client_signature(),
			'server_addr'  => WPSCORE()->get_server_addr(),
			'server_name'  => WPSCORE()->get_server_name(),
			'core_version' => WPSCORE_VERSION,
			'time'         => ceil( time() / 1000 ),
			'partner_id'   => $this->params['partner']['id'],
		);

		$args = array(
			'timeout'   => 50,
			'sslverify' => false,
		);

		$base64_params = base64_encode( serialize( $api_params ) );

		$response = wp_remote_get( WPSCORE()->get_api_url( 'amve/get_feed', $base64_params ), $args );

		if ( ! is_wp_error( $response ) && $response['headers']['content-type'] === 'application/json; charset=UTF-8' ) {

			$response_body = json_decode( wp_remote_retrieve_body( $response ) );

			if ( $response_body === null ) {
				WPSCORE()->write_log( 'error', 'Connection to API (get_feed) failed (null)', __FILE__, __LINE__ );
				return false;
			} elseif ( 200 !== $response_body->data->status ) {
				WPSCORE()->write_log( 'error', 'Connection to API (get_feed) failed (status: <code>' . $response_body->data->status . '</code> message: <code>' . $response_body->message . '</code>)', __FILE__, __LINE__ );
				return false;
			} else {
				// success
				if ( isset( $response_body->data->feed_infos ) ) {
					$this->feed_infos = $response_body->data->feed_infos;
					$this->feed_url   = $this->get_partner_feed_infos( $this->feed_infos->feed_url->data );
					// $this->feed_url   = strtolower( $this->feed_url );
					if ( ! $this->feed_url ) {
						WPSCORE()->write_log( 'error', 'Connection to Partner\'s API failed (feed url: <code>' . $this->feed_url . '</code> partner id: <code>:' . $this->params['partner']['id'] . '</code>)', __FILE__, __LINE__ );
						return false;
					}
					switch ( $this->params['partner']['data_type'] ) {
						case 'native':
							return $this->retrieve_videos_from_native_feed();
							break;
						case 'json':
							if ( $this->params['partner']['id'] == 'pornhub' ) {
								return $this->retrieve_videos_from_pornhub();
							} else {
								return $this->retrieve_videos_from_json_feed();
							}
							break;
						default:
							return $this->retrieve_videos_from_xml_feed();
							break;
					}
				} else {
					WPSCORE()->write_log( 'error', 'Connection to API (get_feed) failed (message: <code>' . $response_body->message . '</code>)', __FILE__, __LINE__ );
				}
			}
		} else {
			if ( isset( $response->errors['http_request_failed'] ) ) {
				WPSCORE()->write_log( 'error', 'Connection to API (get_feed) failed (error: <code>' . json_encode( $response->errors ) . '</code>)', __FILE__, __LINE__ );
				return false;
			}
		}
		return false;
	}

	public function get_videos() {
		return $this->videos;
	}
	public function get_searched_data() {
		return $this->searched_data;
	}

	public function get_errors() {
		return $this->errors;
	}

	public function has_errors() {
		return ! empty( $this->errors );
	}

	private function retrieve_videos_from_native_feed() {
		$array_valid_videos     = array();
		$count_valid_feed_items = 0;
		$counters               = array(
			'valid_videos'    => 0,
			'invalid_videos'  => 0,
			'existing_videos' => 0,
			'removed_videos'  => 0,
		);
		$videos_details         = array();
		$current_page           = (int) $first_page = $this->get_partner_feed_infos( $this->feed_infos->feed_first_page->data );

		// get latest page crawled for this feed
		$saved_feed_infos = AMVE()->get_feed( $this->params['feed_id'] );

		$last_page_crawled = ( isset( $saved_feed_infos['last_page_crawled'] ) && $saved_feed_infos['last_page_crawled'] > 0 ) ? (int) $saved_feed_infos['last_page_crawled'] : $first_page;
		$end               = false;
		$existing_ids      = $this->get_partner_existing_ids();
		$root_feed_url     = $this->feed_url;

		$paged = '';
		if ( isset( $this->feed_infos->feed_paged ) ) {
			$paged = $this->get_partner_feed_infos( $this->feed_infos->feed_paged->data );
		}

		$array_videos_ids_index = $current_page;
		$array_found_ids        = array();

		while ( false === $end ) {
			// XNXX
			if ( $this->params['partner']['id'] == 'xnxx' ) {
				$this->feed_url = $root_feed_url . '/' . $current_page . '/';
			}

			// Youjizz
			if ( $this->params['partner']['id'] == 'youjizz' ) {
				$this->feed_url = $root_feed_url . '-' . $current_page . '.html';
			}

			if ( $current_page > $first_page ) {
				if ( $this->params['partner']['id'] == 'youjizz' ) {
					$this->feed_url = $root_feed_url . '-' . $current_page . '.html';
				} else {
					$this->feed_url = $root_feed_url . $paged . $current_page;
				}
			}

			WPSCORE()->write_log( 'info', $this->feed_url, __FILE__, __LINE__ );

			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_HEADER, true );

			$custom_proxy      = false;
			$custom_proxy_ip   = xbox_get_field_value( 'amve-options', 'proxy-ip' );
			$custom_proxy_port = xbox_get_field_value( 'amve-options', 'proxy-port' );

			if ( ! empty( $custom_proxy_ip ) && ! empty( $custom_proxy_port ) ) {
				$custom_proxy = true;
				curl_setopt( $ch, CURLOPT_PROXY, $custom_proxy_ip );
				curl_setopt( $ch, CURLOPT_PROXYPORT, $custom_proxy_port );

				$custom_proxy_user     = xbox_get_field_value( 'amve-options', 'proxy-user' );
				$custom_proxy_password = xbox_get_field_value( 'amve-options', 'proxy-password' );
				if ( ! empty( $custom_proxy_user ) && ! empty( $custom_proxy_password ) ) {
					curl_setopt( $ch, CURLOPT_PROXYUSERPWD, $custom_proxy_user . ':' . $custom_proxy_password );
				}
			}

			curl_setopt( $ch, CURLOPT_REFERER, trim( $this->feed_url ) );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
			curl_setopt( $ch, CURLOPT_URL, trim( $this->feed_url ) );
			curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
			curl_setopt( $ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8) AppleWebKit/535.6.2 (KHTML, like Gecko) Version/5.2 Safari/535.6.2' );

			if ( isset( $this->feed_infos->feed_cookies ) ) {
				$cookies       = $this->feed_infos->feed_cookies;
				$array_cookies = array();

				foreach ( (array) $cookies as $key => $value ) {
					$array_cookies[] = 'Cookie: ' . $key . '=' . $this->get_partner_feed_infos( $value );
				}
				curl_setopt( $ch, CURLOPT_HTTPHEADER, $array_cookies );
			}

			$raw_html = curl_exec( $ch );
			$httpcode = curl_getinfo( $ch, CURLINFO_HTTP_CODE );

			if ( strpos( $raw_html, 'function leastFactor(n)' ) !== false ) {
				$this->errors = array(
					'code'     => 'CURL ERROR ' . $httpcode,
					'message'  => 'Too many requests. Your server IP has been banned from ' . $this->params['partner']['id'],
					'solution' => 'Configure the Proxy options or use an other partner',
				);
				WPSCORE()->write_log( 'error', 'Connection to native site (' . $this->params['partner']['id'] . ') failed <code>Too many requests. Your server IP has been banned</code>', __FILE__, __LINE__ );
				curl_close( $ch );
				break;
			}

			$html = str_get_html( $raw_html );
			unset( $raw_html );
			curl_close( $ch );
			if ( 200 != $httpcode ) {
				switch ( $httpcode ) {
					case 403:
					case 0:
						if ( $custom_proxy ) {
							$this->errors = array(
								'code'     => 'CURL ERROR ' . $httpcode,
								'message'  => 'This Proxy (' . $custom_proxy_ip . ':' . $custom_proxy_port . ') might have been banned for this partner.',
								'solution' => 'Please configure the Proxy options.',
							);
							WPSCORE()->write_log( 'error', 'Connection to native site (' . $this->params['partner']['id'] . ') failed <code>CURL HTTP CODE: ' . $httpcode . '</code> with Proxy <code>' . $custom_proxy_ip . ':' . $custom_proxy_port . '</code>', __FILE__, __LINE__ );
						} else {
							$this->errors = array(
								'code'     => 'CURL ERROR ' . $httpcode,
								'message'  => 'Your Server IP might have been banned for this partner.',
								'solution' => 'Please configure the Proxy options.',
							);
							WPSCORE()->write_log( 'error', 'Connection to native site (' . $this->params['partner']['id'] . ') failed <code>CURL HTTP CODE: ' . $httpcode . '</code>', __FILE__, __LINE__ );
						}
						return false;
						break;
					case 400:
						$this->errors = array(
							'code'     => 'CURL ERROR ' . $httpcode,
							'message'  => 'Do not panic!',
							'solution' => 'Please contact the WP-Script dev team',
						);
						WPSCORE()->write_log( 'error', 'Connection to native site (' . $this->params['partner']['id'] . ' / ' . $this->feed_url . ') failed <code>CURL HTTP CODE: ' . $httpcode . '</code>', __FILE__, __LINE__ );
						return false;
						break;
					default:
						if ( ! empty( $array_valid_videos ) ) {
							$end = true;
						} else {
							$this->errors = array(
								'code'     => 'CURL ERROR ' . $httpcode,
								'message'  => 'Do not panic!',
								'solution' => 'Please contact the WP-Script dev team',
							);
							WPSCORE()->write_log( 'error', 'Connection to native site (' . $this->params['partner']['id'] . ' / ' . $this->feed_url . ') failed <code>CURL HTTP CODE: ' . $httpcode . '</code>', __FILE__, __LINE__ );
							return false;
							break;
						}
				}
			} else {
				// TMP récupération des blocks de la page courante
				$videos_blocks         = $html->find( $this->get_partner_feed_infos( $this->feed_infos->feed_item_path->node ) );
				$videos_blocks_counter = count( (array) $videos_blocks );

				// TMP s'il n'y a pas de blocs vidéos, on met $end à true.
				if ( 0 === $videos_blocks_counter ) {
					$end = true;
				} else {
					// TMP pour chaque block de video de la page en cours...
					foreach ( (array) $videos_blocks as $video_block ) {

						if ( $end ) {
							break;
						} else {

							// TMP on récupère l'id du block courant.
							$video_id = $this->find_native( $video_block, 'feed_item_id' );

							// TMP modif de la condition, on extrait le cas où le video_id n'existe pas => c'est qu'il y a un soucis.
							if ( false === $video_id ) {
								// TMP une erreur est apparue car pas d'id video.
								$this->errors = array(
									'code'     => 'Video ID not found ' . $httpcode,
									'message'  => 'Do not panic!',
									'solution' => 'Please contact the WP-Script dev team',
								);
								WPSCORE()->write_log( 'error', 'Video ID not found (' . $this->params['partner']['id'] . ' / ' . $this->feed_url . ')', __FILE__, __LINE__ );
								// TMP on relève la page courante pour l'assigner aux infos du flux en cours.
								$saved_feed_infos['last_page_crawled'] = $current_page;
								$end                                   = true;
							} else {

								// xnxx.
								if ( 'xnxx' === $this->params['partner']['id'] ) {
									$video_id = str_replace( 'video_', '', $video_id );
								}

								// Xvideos.
								if ( 'xvideos' === $this->params['partner']['id'] ) {
									$video_id = str_replace( 'video_', '', $video_id );
								}

								// Youjizz.
								if ( 'youjizz' === $this->params['partner']['id'] ) {
									preg_match( '/-([0-9]+)\.html/u', $video_id, $matches );
									$video_id = $matches[1];
								}

								// xHamster.
								if ( 'xhamster' === $this->params['partner']['id'] ) {
									$tmp      = explode( '-', $video_id );
									$video_id = end( $tmp );
								}

								$current_page_ids[ $array_videos_ids_index ][] = $video_id;

								// TMP si l'id video est défini et n'existe pas dans les vidéos déjà importées...
								// if (!in_array($video_id, (array) $existing_ids['partner_all_videos_ids'])) {
								if ( ! in_array( $video_id, (array) $existing_ids['partner_all_videos_ids'] ) && ! in_array( $video_id, $array_found_ids ) ) {
									$array_found_ids[] = $video_id;

									// TMP on ajoute la vidéo aux vidéo à télécharger
									$feed_item = new AMVE_Native_Item( $video_block );
									$feed_item->init( $this->params, $this->feed_infos );
									if ( $feed_item->is_valid() ) {
										$array_valid_videos[] = (array) $feed_item->get_data_for_json( $count_valid_feed_items );
										$videos_details[]     = array(
											'id'       => $video_id,
											'response' => 'Success',
										);
										++$counters['valid_videos'];
										++$count_valid_feed_items;
									} else {
										$videos_details[] = array(
											'id'       => $video_id,
											'response' => 'Invalid',
										);
										++$counters['invalid_videos'];
									}
									// TMP si on atteins la limide de vidéos à récupérer, on met $end à true
									if ( $count_valid_feed_items >= $this->params['limit'] ) {
										// TMP on relève la page courante pour l'assigner aux infos du flux en cours
										$saved_feed_infos['last_page_crawled'] = $current_page;
										$end                                   = true;
									}
								} else {
									// partner_existing_videos_ids'   => $partner_existing_videos_ids,
									// 'partner_unwanted_videos_ids'   => $partner_unwanted_videos_ids,
									if ( in_array( $video_id, (array) $existing_ids['partner_existing_videos_ids'] ) ) {
										$videos_details[] = array(
											'id'       => $video_id,
											'response' => 'Already imported',
										);
										++$counters['existing_videos'];
									} elseif ( in_array( $video_id, (array) $existing_ids['partner_unwanted_videos_ids'] ) ) {
										$videos_details[] = array(
											'id'       => $video_id,
											'response' => 'You removed it from search results',
										);
										++$counters['removed_videos'];
									}
									// WPSCORE()->write_log( 'notice', "AUTO-IMPORT: existing video_id : $video_id", __FILE__, __LINE__ );
									// TMP sinon la video existe, donc on passe directement à la dernière page crawlée
									// TMP si la dernière page crawlée n'existe pas ou si $current_page < $last_page_crawled (nécessaire pour éviter les doublons dans le cas contraire)
									// if (intval($current_page) <= intval($last_page_crawled)) {
										// TMP on l'estime et on change la current page
										$estimated_current_page = ceil( $saved_feed_infos['total_videos'] / $videos_blocks_counter );
										// $current_page = ($last_page_crawled > $estimated_current_page ) ? $last_page_crawled : $estimated_current_page;
										// WPSCORE()->write_log( 'notice', "AUTO-IMPORT: Goto page : $current_page", __FILE__, __LINE__ );
									// } else {
										// $saved_feed_infos['last_page_crawled'] = $current_page;
									// }
									if ( intval( $current_page ) <= intval( $estimated_current_page ) ) {
										$current_page = $estimated_current_page;
									}
								}
							}
						}
						$video_block->clear();
						unset( $video_block );
					} //end foreach current page videos block

					// set $end to true if all the current page ids are the same as the latest page ids (= stuck in the end of a search)
					if ( isset( $current_page_ids[ $array_videos_ids_index ] ) && isset( $current_page_ids[ $array_videos_ids_index - 1 ] ) && $current_page_ids[ $array_videos_ids_index ] == $current_page_ids[ $array_videos_ids_index - 1 ] ) {
						$end = true;
						WPSCORE()->write_log( 'notice', 'AUTO-IMPORT: Last page reached (p.' . $current_page . '). No new videos found with feed ID <code>' . $this->params['feed_id'] . '</code>', __FILE__, __LINE__ );
						AMVE()->update_feed( $this->params['feed_id'], array( 'auto_import' => false ) );
					}

					++$current_page;
					++$array_videos_ids_index;
				}

				AMVE()->update_feed( $this->params['feed_id'], $saved_feed_infos );
				$html->clear();
				unset( $html );
			}
		} //end main while loop

		$this->searched_data = array(
			'videos_details' => $videos_details,
			'counters'       => $counters,
			'videos'         => $array_valid_videos,
		);
		$this->videos        = $array_valid_videos;
		return true;
	}

	private function retrieve_videos_from_json_feed() {

		$existing_ids           = $this->get_partner_existing_ids();
		$array_valid_videos     = array();
		$counters               = array(
			'valid_videos'    => 0,
			'invalid_videos'  => 0,
			'existing_videos' => 0,
			'removed_videos'  => 0,
		);
		$videos_details         = array();
		$count_valid_feed_items = 0;
		$end                    = false;

		$args = array(
			'timeout'   => 300,
			'sslverify' => false,
		);
		// add user agent
		if ( $this->params['partner']['id'] == 'porncom' ) {
			// porn.com user agent
			$args['user-agent'] = $_SERVER['HTTP_USER_AGENT'];
		} else {
			// default user agent
			$args['user-agent'] = 'WordPress/' . $this->wp_version . '; ' . home_url();
		}

		if ( isset( $this->feed_infos->feed_auth ) ) {
			$args['headers'] = array( 'Authorization' => $this->get_partner_feed_infos( $this->feed_infos->feed_auth->data ) );
		}

		$response = wp_remote_get( $this->feed_url, $args );

		if ( is_wp_error( $response ) ) {
			WPSCORE()->write_log( 'error', 'Retrieving videos from JSON feed failed <code>PARTNER: ' . $this->params['partner']['name'] . '</code> <code>ERROR: ' . json_encode( $response->errors ) . '</code>', __FILE__, __LINE__ );
			return false;
		}

		$response_body = json_decode( wp_remote_retrieve_body( $response ), true );

		// améliorer root selon paramètres / ou si null dans la config
		if ( isset( $this->feed_infos->feed_item_path->node ) ) {
			$root       = $this->feed_infos->feed_item_path->node;
			$array_feed = $response_body[ $root ];
		} else {
			$root       = 0;
			$array_feed = $response_body;
		}

		$sub_root      = '';
		$exploded_root = explode( '/', $root );

		// subroot case
		if ( count( $exploded_root ) >= 2 ) {
			$root          = reset( $exploded_root );
			$sub_root      = end( $exploded_root );
			$sub_root_type = explode( ':', $sub_root );

			if ( reset( $sub_root_type ) == 'single' ) {
				$sub_root = end( $sub_root_type );
			}
			$array_feed = $response_body[ $root ];
		}

		$count_total_feed_items = count( $array_feed );
		if ( $count_total_feed_items < $this->params['limit'] ) {
			$this->params['limit'] = $count_total_feed_items;
		}
		$current_item = 0;
		while ( $end === false ) {
			if ( $sub_root != '' ) {
				$feed_item = new AMVE_Json_Item( $array_feed[ $current_item ][ $sub_root ] );
			} else {
				$feed_item = new AMVE_Json_Item( $array_feed[ $current_item ] );
			}
			$feed_item->init( $this->params, $this->feed_infos );
			if ( $feed_item->is_valid() ) {
				if ( ! in_array( $feed_item->get_id(), (array) $existing_ids['partner_all_videos_ids'] ) ) {
					$array_valid_videos[] = (array) $feed_item->get_data_for_json( $count_valid_feed_items );
					$videos_details[]     = array(
						'id'       => $feed_item->get_id(),
						'response' => 'Success',
					);
					++$counters['valid_videos'];
					++$count_valid_feed_items;
				} else {
					if ( in_array( $feed_item->get_id(), (array) $existing_ids['partner_existing_videos_ids'] ) ) {
						$videos_details[] = array(
							'id'       => $feed_item->get_id(),
							'response' => 'Already imported',
						);
						++$counters['existing_videos'];
					} elseif ( in_array( $feed_item->get_id(), (array) $existing_ids['partner_unwanted_videos_ids'] ) ) {
						$videos_details[] = array(
							'id'       => $feed_item->get_id(),
							'response' => 'You removed it from search results',
						);
						++$counters['removed_videos'];
					}
				}
			} else {
				$videos_details[] = array(
					'id'       => $feed_item->get_id(),
					'response' => 'Invalid',
				);
				++$counters['invalid_videos'];
			}
			if ( ( $count_valid_feed_items >= $this->params['limit'] ) || $current_item >= ( $count_total_feed_items - 1 ) ) {
				$videos_details[] = array(
					'id'       => 'end',
					'response' => 'No more videos',
				);
				$end              = true;
			}
			++$current_item;
		}
		unset( $array_feed );
		$this->searched_data = array(
			'videos_details' => $videos_details,
			'counters'       => $counters,
			'videos'         => $array_valid_videos,
		);
		$this->videos        = $array_valid_videos;
		return true;
	}

	private function retrieve_videos_from_pornhub() {
		$existing_ids           = $this->get_partner_existing_ids();
		$array_valid_videos     = array();
		$counters               = array(
			'valid_videos'    => 0,
			'invalid_videos'  => 0,
			'existing_videos' => 0,
			'removed_videos'  => 0,
		);
		$videos_details         = array();
		$count_valid_feed_items = 0;
		$end                    = false;

		$root_feed_url = $this->feed_url;

		$args = array(
			'timeout'   => 300,
			'sslverify' => false,
		);

		$args['user-agent'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.169 Safari/537.36';

		if ( isset( $this->feed_infos->feed_auth ) ) {
			$args['headers'] = array( 'Authorization' => $this->get_partner_feed_infos( $this->feed_infos->feed_auth->data ) );
		}

		$current_page = 1;

		while ( $end === false ) {

			if ( $current_page > 1 ) {
				$this->feed_url = $root_feed_url . '&page=' . $current_page;
			}

			$response = wp_remote_get( $this->feed_url, $args );

			if ( is_wp_error( $response ) ) {
				WPSCORE()->write_log( 'error', 'Retrieving videos from JSON feed failed <code>PARTNER: ' . $this->params['partner']['name'] . '</code> <code>ERROR: ' . json_encode( $response->errors ) . '</code>', __FILE__, __LINE__ );
				return false;
			}

			$response_body = json_decode( wp_remote_retrieve_body( $response ), true );

			// feed url last page reached
			if ( ( isset( $response_body['code'] ) && ( $response_body['code'] == 2000 || $response_body['code'] == 2001 ) ) || ( isset( $response_body['videos'] ) && empty( $response_body['videos'] ) ) ) {
				$end              = true;
				$page_end         = true;
				$videos_details[] = array(
					'id'       => 'end',
					'response' => 'No more videos',
				);
			} else {
				// améliorer root selon paramètres / ou si null dans la config
				if ( isset( $this->feed_infos->feed_item_path->node ) ) {
					$root       = $this->feed_infos->feed_item_path->node;
					$array_feed = $response_body[ $root ];
				} else {
					$root       = 0;
					$array_feed = $response_body;
				}

				$sub_root      = '';
				$exploded_root = explode( '/', $root );

				// subroot case
				if ( count( $exploded_root ) >= 2 ) {
					$root          = reset( $exploded_root );
					$sub_root      = end( $exploded_root );
					$sub_root_type = explode( ':', $sub_root );

					if ( reset( $sub_root_type ) == 'single' ) {
						$sub_root = end( $sub_root_type );
					}
					$array_feed = $response_body[ $root ];
				}

				$count_total_feed_items = count( $array_feed );
				// if( $count_total_feed_items < $this->params['limit'] ){
				//     $this->params['limit'] = $count_total_feed_items;
				// }
				$current_item = 0;
				$page_end = false;
			}

			while ( $page_end === false ) {
				if ( $sub_root != '' ) {
					$feed_item = new AMVE_Json_Item( $array_feed[ $current_item ][ $sub_root ] );
				} else {
					$feed_item = new AMVE_Json_Item( $array_feed[ $current_item ] );
				}
				$feed_item->init( $this->params, $this->feed_infos );
				if ( $feed_item->is_valid() ) {
					if ( ! in_array( $feed_item->get_id(), (array) $existing_ids['partner_all_videos_ids'] ) ) {
						$array_valid_videos[] = (array) $feed_item->get_data_for_json( $count_valid_feed_items );
						$videos_details[]     = array(
							'id'       => $feed_item->get_id(),
							'response' => 'Success',
						);
						++$counters['valid_videos'];
						++$count_valid_feed_items;
					} else {
						if ( in_array( $feed_item->get_id(), (array) $existing_ids['partner_existing_videos_ids'] ) ) {
							$videos_details[] = array(
								'id'       => $feed_item->get_id(),
								'response' => 'Already imported',
							);
							++$counters['existing_videos'];
						} elseif ( in_array( $feed_item->get_id(), (array) $existing_ids['partner_unwanted_videos_ids'] ) ) {
							$videos_details[] = array(
								'id'       => $feed_item->get_id(),
								'response' => 'You removed it from search results',
							);
							++$counters['removed_videos'];
						}
					}
				} else {
					$videos_details[] = array(
						'id'       => $feed_item->get_id(),
						'response' => 'Invalid',
					);
					++$counters['invalid_videos'];
				}
				if ( ( $count_valid_feed_items >= $this->params['limit'] ) || $current_item >= ( $count_total_feed_items - 1 ) ) {
					$page_end = true;
					++$current_page;
					if ( $count_valid_feed_items >= $this->params['limit'] ) {
						$end = true;
					}
				}
				++$current_item;
			}
		}

		unset( $array_feed );
		$this->searched_data = array(
			'videos_details' => $videos_details,
			'counters'       => $counters,
			'videos'         => $array_valid_videos,
		);
		$this->videos        = $array_valid_videos;
		return true;
	}

	private function retrieve_videos_from_xml_feed() {

		$xml_reader             = new XMLReader();
		$existing_ids           = $this->get_partner_existing_ids();
		$array_valid_videos     = array();
		$counters               = array(
			'valid_videos'    => 0,
			'invalid_videos'  => 0,
			'existing_videos' => 0,
			'removed_videos'  => 0,
		);
		$videos_details         = array();
		$count_valid_feed_items = 0;
		$end                    = false;

		if ( ! $xml_reader->open( $this->feed_url ) || ! $xml_reader->read() ) {
			// full xml
			$args     = array(
				'timeout'    => 300,
				'sslverify'  => false,
				'user-agent' => 'WordPress/' . $this->wp_version . '; ' . home_url(),
			);
			$response = wp_remote_get( $this->feed_url, $args );

			if ( ! is_wp_error( $response ) ) {
				$response_body          = wp_remote_retrieve_body( $response );
				$full_xml               = simplexml_load_string( $response_body );
				$count_total_feed_items = count( $full_xml );
				if ( $count_total_feed_items < $this->params['limit'] ) {
					$this->params['limit'] = $count_total_feed_items;
				}
				$current_item = 0;
				while ( $end === false ) {
					$xml_elts  = $full_xml->children()->{$this->feed_infos->feed_item_path->node};
					$feed_item = new AMVE_Xml_Item( $xml_elts[ $current_item ]->asXML() );
					$feed_item->init( $this->params, $this->feed_infos );

					if ( $feed_item->is_valid() ) {
						if ( ! in_array( $feed_item->get_id(), (array) $existing_ids['partner_all_videos_ids'] ) ) {
							$array_valid_videos[] = (array) $feed_item->get_data_for_json( $count_valid_feed_items );
							$videos_details[]     = array(
								'id'       => $feed_item->get_id(),
								'response' => 'Success',
							);
							++$counters['valid_videos'];
							++$count_valid_feed_items;
						} else {
							if ( in_array( $feed_item->get_id(), (array) $existing_ids['partner_existing_videos_ids'] ) ) {
								$videos_details[] = array(
									'id'       => $feed_item->get_id(),
									'response' => 'Already imported',
								);
								++$counters['existing_videos'];
							} elseif ( in_array( $feed_item->get_id(), (array) $existing_ids['partner_unwanted_videos_ids'] ) ) {
								$videos_details[] = array(
									'id'       => $feed_item->get_id(),
									'response' => 'You removed it from search results',
								);
								++$counters['removed_videos'];
							}
						}
					} else {
						$videos_details[] = array(
							'id'       => $feed_item->get_id(),
							'response' => 'Invalid',
						);
						++$counters['invalid_videos'];
					}
					if ( ( $count_valid_feed_items >= $this->params['limit'] ) || $current_item >= ( $count_total_feed_items - 1 ) ) {
						$end = true;
					}
					++$current_item;
				}
				unset( $full_xml, $xml_elts, $feed_item );
			} else {
				if ( isset( $response->errors['http_request_failed'] ) ) {
					wp_die( $response->errors['http_request_failed'] );
				}
			}
		} else {
			// stream xml
			$end = false;
			while ( $xml_reader->read() && $end === false ) {
				switch ( $xml_reader->nodeType ) {
					case ( XMLREADER::ELEMENT ):
						if ( $xml_reader->localName == $this->feed_infos->feed_item_path->node ) {
							$feed_item = new AMVE_Xml_Item( $xml_reader->readOuterXML() );// extends SimpleXMLElement
							$feed_item->init( $this->params, $this->feed_infos );
							if ( $feed_item->is_valid() ) {
								if ( ! in_array( $feed_item->get_id(), (array) $existing_ids['partner_all_videos_ids'] ) ) {
									$array_valid_videos[] = (array) $feed_item->get_data_for_json( $count_valid_feed_items );
									$videos_details[]     = array(
										'id'       => $feed_item->get_id(),
										'response' => 'Success',
									);
									++$counters['valid_videos'];
									++$count_valid_feed_items;
								} else {
									if ( in_array( $feed_item->get_id(), (array) $existing_ids['partner_existing_videos_ids'] ) ) {
										$videos_details[] = array(
											'id'       => $feed_item->get_id(),
											'response' => 'Already imported',
										);
										++$counters['existing_videos'];
									} elseif ( in_array( $feed_item->get_id(), (array) $existing_ids['partner_unwanted_videos_ids'] ) ) {
										$videos_details[] = array(
											'id'       => $feed_item->get_id(),
											'response' => 'You removed it from search results',
										);
										++$counters['removed_videos'];
									}
								}
							} else {
								$videos_details[] = array(
									'id'       => $feed_item->get_id(),
									'response' => 'Invalid',
								);
								++$counters['invalid_videos'];
							}
							if ( $count_valid_feed_items >= $this->params['limit'] ) {
								$end = true;
							}
						}
				}
			}
			$xml_reader->close();
		}
		$this->searched_data = array(
			'videos_details' => $videos_details,
			'counters'       => $counters,
			'videos'         => $array_valid_videos,
		);
		$this->videos        = $array_valid_videos;
		return true;
	}


	private function find_native( $video_block, $feed_item ) {

		$array_video_id = array(
			'query'  => $this->get_partner_feed_infos( $this->feed_infos->{$feed_item}->find->query ),
			'offset' => $this->get_partner_feed_infos( $this->feed_infos->{$feed_item}->find->offset ),
			'attr'   => $this->get_partner_feed_infos( $this->feed_infos->{$feed_item}->find->attr ),
		);

		if ( $array_video_id['query'] != '' ) {
			return $video_block->find( (string) $array_video_id['query'], (int) $array_video_id['offset'] )->{$array_video_id['attr']};
		} else {
			return $video_block->{$array_video_id['attr']};
		}
	}


	private function get_partner_feed_infos( $partner_feed_item ) {
		$results = array();
		preg_match_all( '/<%(.+)%>/U', $partner_feed_item, $results );

		foreach ( (array) $results[1] as $result ) {
			if ( strpos( $result, 'get_partner_option' ) !== false ) {
				$saved_partner_options = WPSCORE()->get_product_option( 'AMVE', $this->params['partner']['id'] . '_options' );
				$option                = str_replace( array( 'get_partner_option("', '")' ), array( '', '' ), $result );
				$new_result            = '$saved_partner_options["' . $option . '"]';
				$partner_feed_item     = str_replace( '<%' . $result . '%>', eval( 'return ' . $new_result . ';' ), $partner_feed_item );
			} else {
				$partner_feed_item = str_replace( '<%' . $result . '%>', eval( 'return ' . $result . ';' ), $partner_feed_item );
			}
		}

		return $partner_feed_item;
	}

	private function get_partner_existing_ids() {

		// retrieve existing ids from imported videos
		global $wpdb;

		$custom_post_type = xbox_get_field_value( 'amve-options', 'custom-video-post-type' );
		$custom_post_type = $custom_post_type != '' ? $custom_post_type : 'post';

		$query_str = "
            SELECT wposts.ID, wpostmetaVideoId.meta_value videoId
            FROM $wpdb->posts wposts, $wpdb->postmeta wpostmetasponsor, $wpdb->postmeta wpostmetaVideoId
            WHERE wposts.ID = wpostmetasponsor.post_id
            AND ( wpostmetasponsor.meta_key = 'partner' AND wpostmetasponsor.meta_value = %s )
            AND (wposts.ID =  wpostmetaVideoId.post_id AND wpostmetaVideoId.meta_key = 'video_id')
            AND wposts.post_type = %s
        ";

		$bdd_videos = $wpdb->get_results( $wpdb->prepare( $query_str, $this->params['partner']['id'], $custom_post_type ), OBJECT );

		$partner_existing_videos_ids = array();

		foreach ( (array) $bdd_videos as $bdd_video ) {
			$partner_existing_videos_ids[] = $bdd_video->videoId;
		}
		unset( $bdd_videos );

		// retrieve existing ids from unwanted videos
		$partner_unwanted_videos_ids = array();
		$unwanted_videos_ids         = WPSCORE()->get_product_option( 'AMVE', 'removed_videos_ids' );
		if ( isset( $unwanted_videos_ids[ $this->params['partner']['id'] ] ) && is_array( $unwanted_videos_ids[ $this->params['partner']['id'] ] ) ) {
			$partner_unwanted_videos_ids = $unwanted_videos_ids[ $this->params['partner']['id'] ];
		}

		unset( $unwanted_videos_ids );

		return array(
			'partner_existing_videos_ids' => $partner_existing_videos_ids,
			'partner_unwanted_videos_ids' => $partner_unwanted_videos_ids,
			'partner_all_videos_ids'      => array_merge( $partner_existing_videos_ids, $partner_unwanted_videos_ids ),
		);
	}
}
