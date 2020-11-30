<?php
class AMVE_Native_Item {

	protected $item;
	protected $video_block;
	protected $params;

	function __construct( $video_block ) {
		$this->video_block = $video_block;
	}

	public function init( $params, $feed_infos ) {

		if ( empty( $params ) || empty( $feed_infos ) ) {
			return false;
		}

		$params     = json_decode( json_encode( $params ), true );
		$feed_infos = json_decode( json_encode( $feed_infos ), true );
		$partner_id = $params['partner']['id'];

		$this->params = $params;

		$this->item['id']     = $this->get_partner_feed_infos( 'feed_item_id', $partner_id, $feed_infos );
		$this->item['title']  = $this->get_partner_feed_infos( 'feed_item_title', $partner_id, $feed_infos );
		$this->item['desc']   = $this->get_partner_feed_infos( 'feed_item_desc', $partner_id, $feed_infos );
		$this->item['tags']   = $this->get_partner_feed_infos( 'feed_item_tags', $partner_id, $feed_infos );
		$this->item['actors'] = $this->get_partner_feed_infos( 'feed_item_actors', $partner_id, $feed_infos );

		$this->add_more_tags_and_actors();// has to be called after $this->item['tags'] and $this->item['actors']

		$this->item['length']        = $this->get_partner_feed_infos( 'feed_item_length', $partner_id, $feed_infos );
		$this->item['length_format'] = $this->get_partner_feed_infos( 'feed_item_length_format', $partner_id, $feed_infos );
		$this->item['thumb_url']     = $this->get_partner_feed_infos( 'feed_item_thumb_url', $partner_id, $feed_infos );

		$this->item['thumbs_urls'] = $this->get_partner_feed_infos( 'feed_item_thumbs_urls', $partner_id, $feed_infos );

		$this->item['trailer_url']  = $this->get_partner_feed_infos( 'feed_item_trailer_url', $partner_id, $feed_infos );
		$this->item['video_url']    = $this->get_partner_feed_infos( 'feed_item_video_url', $partner_id, $feed_infos );
		$this->item['tracking_url'] = $this->get_partner_feed_infos( 'feed_item_join_url', $partner_id, $feed_infos );
		$this->item['code']         = $this->get_partner_feed_infos( 'feed_item_code', $partner_id, $feed_infos );

	}

	public function get_id() {
		return AMVE_Item::get_id( $this->item );
	}

	public function is_valid() {
		return AMVE_Item::is_valid( $this->item );
	}

	public function get_data_for_js() {
		return AMVE_Item::get_data_for_js( $this->item );
	}

	public function get_data_for_json( $cpt = 0 ) {
		return AMVE_Item::get_data_for_json( $this->item, $cpt = 0 );
	}

	private function get_partner_feed_infos( $partner_feed_item, $partner_id, $feed_infos ) {

		$output         = false;
		$feed_item_type = isset( $feed_infos[ $partner_feed_item ] ) ? key( $feed_infos[ $partner_feed_item ] ) : null;
		$short_item     = '';

		if ( isset( $feed_infos[ $partner_feed_item ][ $feed_item_type ] ) ) {

			$short_item = $feed_infos[ $partner_feed_item ][ $feed_item_type ];

			if ( $feed_item_type != 'find' ) {

				$results = array();
				preg_match_all( '/<%(.+)%>/U', $short_item, $results );

				foreach ( $results[1] as $result ) {
					if ( strpos( $result, 'get_partner_option' ) !== false ) {
						$saved_partner_options = WPSCORE()->get_product_option( 'AMVE', $partner_id . '_options' );
						$option                = str_replace( array( 'get_partner_option("', '")' ), array( '', '' ), $result );
						$new_result            = '$saved_partner_options["' . $option . '"]';
						$short_item            = str_replace( '<%' . $result . '%>', eval( 'return ' . $new_result . ';' ), $short_item );
					} else {
						$short_item = str_replace( '<%' . $result . '%>', eval( 'return ' . $result . ';' ), $short_item );
					}
				}
				unset( $results );
			}

			switch ( $feed_item_type ) {
				case 'find':
					$video_block = $this->video_block;
					$query       = $short_item['query'];
					$offset      = $short_item['offset'];
					$attr        = $short_item['attr'];

					if ( isset( $short_item['loop'] ) && isset( $short_item['separator'] ) ) {

						$blocks = $video_block->find( (string) $query );

						foreach ( (array) $blocks as $block ) {
							$output[] = $block->{$attr};
						}

						$output = implode( $short_item['separator'], (array) $output );

					} else {
						if ( $query != '' ) {
							$output = $video_block->find( (string) $query, (int) $offset )->{$attr};
						} else {
							$output = $video_block->{$attr};
						}
					}

					// avgle
					// if( $this->params['partner']['id'] == 'avgle' ){
					// if( $partner_feed_item == 'feed_item_id' ){
					// $output_exploded = explode('/', $output );
					// $output = $output_exploded[2];
					// }
					// if( $partner_feed_item == 'feed_item_code' ){
					// $output_replace = str_replace('video', 'embed', $output );
					// $output = '<iframe width="530" height="298" src="https://avgle.com' . $output_replace . '" frameborder="0" allowfullscreen></iframe>';
					// }
					// }

					// xhamster
					if ( $this->params['partner']['id'] == 'xhamster' ) {
						if ( $partner_feed_item == 'feed_item_id' ) {
							$tmp    = explode( '-', $output );
							$output = end( $tmp );
						}
						// thumb size
						// if( 'feed_item_thumb_url' && strpos($output, 'xhcdn.com') !== false ){
						// replace 240 by 640 only for the last 240 found (url can be .../240/240/... instead of .../123/240/...)
						// if( ( $pos = strrpos( $output , '/240/' ) ) !== false ) {
						// $search_length  = strlen( '/240/' );
						// $output    = substr_replace( $output , '/640/' , $pos , $search_length );
						// }
						// }
					}

					// porntube thumb resize + join_url build + multithumb build
					if ( $this->params['partner']['id'] == 'porntube' ) {
						$output = str_replace( '240x180', '835x470', $output );
						if ( $query == 'a.thumb-link' && $attr == 'href' ) {
							$output = 'http://www.porntube.com' . $output;
						}
						if ( $query == 'ul.mini-slide' && $attr == 'plaintext' ) {
							$output = rtrim( str_replace( '.jpg', '.jpg,', $output ), ',' );
						}
					}

					// pornve
					if ( $this->params['partner']['id'] == 'pornve' ) {
						if ( $partner_feed_item == 'feed_item_id' ) {
							$output_exploded = explode( '/', $output );
							$output          = $output_exploded[3];
						}
						if ( $partner_feed_item == 'feed_item_thumb_url' ) {
							$output_1 = str_replace( 'background-image:url(', '', $output );
							$output   = str_replace( ');', '', $output_1 );
						}
					}

					// xvideos
					if ( $this->params['partner']['id'] == 'xvideos' ) {
						if ( $partner_feed_item == 'feed_item_id' ) {
							$output = str_replace( 'video_', '', $output );
						}
						if ( $partner_feed_item == 'feed_item_thumb_url' ) {
							$output = str_replace( 'THUMBNUM', '1', $output );
							$output = preg_replace( '/\/thumbs169l*\//', '/thumbs169lll/', $output );
						}
						// trailer
						if ( 'feed_item_trailer_url' == $partner_feed_item ) {
							$output = preg_replace( '/\/thumbs169l*\//', '/videopreview/', $output );
							$output = explode( '/', $output );
							array_pop( $output );
							$output = implode( '/', $output ) . '_169.mp4';
						}
					}

					// xnxx
					if ( $this->params['partner']['id'] == 'xnxx' ) {
						if ( $partner_feed_item == 'feed_item_id' ) {
							$output = str_replace( 'video_', '', $output );
						}
						if ( $partner_feed_item == 'feed_item_length' ) {
							$output = str_replace( array( '(', ')' ), '', $output );
						}
					}

					// youjizz
					if ( $this->params['partner']['id'] == 'youjizz' ) {
						if ( $partner_feed_item == 'feed_item_id' ) {
							preg_match( '|-([0-9]+)\.html|U', $output, $matches );
							$output = $matches[1];
						}
					}

					break;

				default:
					$output = $short_item;
					break;
			}

			// Redtube.
			// manage 3 differents types:
			// first thumbs urls type: https://thumbs-cdn.redtube.com/m=eWgr9f/_thumbs/0000841/0841341/0841341_001o.jpg.
			// second thumbs urls type: https://thumbs-cdn.redtube.com/m=eWgr9f/media/videos/201703/23/2066591/original/1.jpg.
			// third thumbs urls type: https://ei-ph.rdtcdn.com/videos/201812/02/194706991/original/(m=eGJF8f)(mh=QYTvPJTv0_Hh3MQ-)0.jpg.
			if ( 'redtube' === $this->params['partner']['id'] ) {
				$output = str_replace( '/=eGJF8f/', '/m=eWgr9f/', $output );
				if ( 'feed_item_id' === $partner_feed_item ) {
					$output = str_replace( 'result_video_', '', $output );
				}
				if ( 'feed_item_length' === $partner_feed_item ) {
					$output = str_replace( 'HD', '', $output );
				}
				if ( 'feed_item_thumbs_urls' === $partner_feed_item ) {
					$thumb_url = $this->item['thumb_url'];
					$regex     = '/.*(?>_0)?(\d+)(?>o)?\.jpg$/U';
					preg_match_all( $regex, $thumb_url, $matches, PREG_OFFSET_CAPTURE, 0 );
					$found_thumb_number_offset = $matches[1][0][1];
					$output                    = array();
					for ( $i = 1; $i <= 15; ++$i ) {
						if ( strpos( $thumb_url, 'o.jpg' ) !== false ) {
							// first case.
							$output[] = substr( $thumb_url, 0, $found_thumb_number_offset ) . sprintf( '%02d', $i ) . 'o.jpg';
						} else {
							// second and third cases.
							$output[] = substr( $thumb_url, 0, $found_thumb_number_offset ) . $i . '.jpg';
						}
					}
					$output = implode( ',', $output );
				}
			}

			// youporn multithumb build
			if ( $this->params['partner']['id'] == 'youporn' || $this->params['partner']['id'] == 'youporngay' ) {
				if ( $partner_feed_item == 'feed_item_thumbs_urls' ) {
					$thumb_url = $this->item['thumb_url'];
					// first thumbs urls type
					$regex1 = '/^.+\/original\/([\d]+)\/.+-([\d]+)\.jpg$/U';
					preg_match_all( $regex1, $thumb_url, $matches1, PREG_SET_ORDER, 0 );
					if ( isset( $matches1[0] ) && isset( $matches1[0][1] ) && isset( $matches1[0][2] ) && $matches1[0][1] == $matches1[0][2] ) {
						$output = array();
						for ( $i = 1; $i <= 16; $i++ ) {
							$thumb_number = $matches1[0][1];
							$output[]     = str_replace( array( "/$thumb_number/", "-$thumb_number.jpg" ), array( "/$i/", "-$i.jpg" ), $thumb_url );
						}
						$output = implode( ',', $output );
					}

					// second thumb urls type
					$regex2 = '/^.+\/videos\/.*\)([\d]+)\.jpg$/U';
					preg_match_all( $regex2, $thumb_url, $matches2, PREG_SET_ORDER, 0 );
					if ( isset( $matches2[0] ) && isset( $matches2[0][1] ) ) {
						$output = array();
						for ( $i = 1; $i <= 16; $i++ ) {
							$thumb_number = $matches2[0][1];
							$output[]     = str_replace( ")$thumb_number.jpg", ")$i.jpg", $thumb_url );
						}
						$output = implode( ',', $output );
					}

					$regex3 = '/^.+\/([\d]+)\.jpg$/U';
					preg_match_all( $regex3, $thumb_url, $matches3, PREG_SET_ORDER, 0 );
					if ( isset( $matches3[0] ) && isset( $matches3[0][1] ) ) {
						$output = array();
						for ( $i = 1; $i <= 16; $i++ ) {
							$thumb_number = $matches3[0][1];
							$output[]     = str_replace( "/$thumb_number.jpg", "/$i.jpg", $thumb_url );
						}
						$output = implode( ',', $output );
					}
				}
			}
		}

		if ( ! $output ) {
			return false;
		}
		return AMVE_Item::clean_string( $output );
	}

	private function add_more_tags_and_actors() {
		// ********************************
		// ** detect and add more actors **
		// ********************************
		$memory_limit = ini_get( 'memory_limit' );
		ini_set( 'memory_limit', '256M' );
		require AMVE_DIR . 'admin/data/actors-x/actors.php';// $data['actors']

		$title       = strtolower( $this->item['title'] );
		$title_words = explode( ' ', strtolower( $this->item['title'] ) );
		$more_actors = array();
		foreach ( (array) $data['actors'] as $actor ) {
			if ( strpos( $title, $actor ) !== false ) {
				// add actor if found actor words >= 2 or exact match if actor words < 2 to prevent bad trunc name to be found
				if ( count( explode( ' ', $actor ) ) >= 2 ) {
					$more_actors[] = $actor;
				} else {
					if ( in_array( $actor, $title_words ) ) {
						$more_actors[] = $actor;
					}
				}
			}
		}
		$more_actors = array_unique( (array) $more_actors );
		$all_actors  = $more_actors;
		if ( $this->item['actors'] ) {
			$actors     = explode( ',', $this->item['actors'] );
			$all_actors = array_merge( (array) $actors, (array) $more_actors );
		}
		unset( $more_actors );
		$this->item['actors'] = implode( ',', $all_actors );
		unset( $data['actors'] );

		// ******************************
		// ** detect and add more tags **
		// ******************************
		require AMVE_DIR . 'admin/data/tags-x/tags.php';// $data['tags']
		$unwanted_chars   = array( ',', '!', '?', ';', ':', '.' );
		$title_words      = explode( ' ', strtolower( str_replace( $unwanted_chars, '', $this->item['title'] ) ) );
		$rephrased_title  = implode( ' ', $title_words );
		$differences_tags = array_diff( $title_words, (array) explode( ',', $this->item['tags'] ) );
		$more_tags        = array_intersect( $differences_tags, $data['tags'] );
		foreach ( (array) $data['tags'] as $tag ) {
			if ( strpos( $rephrased_title, $tag ) !== false && count( explode( ' ', $tag ) ) >= 2 ) {
				$more_tags[] = $tag;
			}
		}
		$all_tags = $more_tags;
		if ( $this->item['tags'] ) {
			$tags     = explode( ',', $this->item['tags'] );
			$all_tags = array_merge( (array) $tags, (array) $more_tags );
		}
		unset( $more_tags );
		// ***********************************
		// ** exclude actor names from tags **
		// ***********************************
		// find each word in names
		$actors_words = array();
		foreach ( (array) $all_actors as $actor ) {
			$actors_words = array_merge( (array) $actors_words, (array) explode( ' ', $actor ) );
		}
		// merge full names and word names
		$actors_words = array_unique( array_merge( (array) $actors_words, (array) $all_actors ) );
		// exclude now
		$all_tags           = array_diff( $all_tags, $actors_words );
		$this->item['tags'] = implode( ',', $all_tags );
		unset( $data['tags'] );
		ini_set( 'memory_limit', $memory_limit );
	}
}
