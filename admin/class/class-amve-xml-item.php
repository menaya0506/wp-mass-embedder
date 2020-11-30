<?php
class AMVE_Xml_Item extends SimpleXMLElement {

	protected $item;

	public function init( $params, $feed_infos ) {

		if ( empty( $params ) || empty( $feed_infos ) ) {
			return false;
		}

		$params     = json_decode( json_encode( $params ), true );
		$feed_infos = json_decode( json_encode( $feed_infos ), true );

		$this->item['id']            = $this->get_partner_feed_infos( 'feed_item_id', $params, $feed_infos );
		$this->item['title']         = $this->get_partner_feed_infos( 'feed_item_title', $params, $feed_infos );
		$this->item['desc']          = $this->get_partner_feed_infos( 'feed_item_desc', $params, $feed_infos );
		$this->item['tags']          = $this->get_partner_feed_infos( 'feed_item_tags', $params, $feed_infos );
		$this->item['length']        = $this->get_partner_feed_infos( 'feed_item_length', $params, $feed_infos );
		$this->item['length_format'] = $this->get_partner_feed_infos( 'feed_item_length_format', $params, $feed_infos );
		$this->item['thumb_url']     = $this->get_partner_feed_infos( 'feed_item_thumb_url', $params, $feed_infos );

		$this->item['thumbs_urls'] = $this->get_partner_feed_infos( 'feed_item_thumbs_urls', $params, $feed_infos );

		$this->item['trailer_url']  = $this->get_partner_feed_infos( 'feed_item_trailer_url', $params, $feed_infos );
		$this->item['video_url']    = $this->get_partner_feed_infos( 'feed_item_video_url', $params, $feed_infos );
		$this->item['tracking_url'] = $this->get_partner_feed_infos( 'feed_item_join_url', $params, $feed_infos );
		$this->item['code']         = $this->get_partner_feed_infos( 'feed_item_code', $params, $feed_infos );
		$this->item['actors']       = $this->get_partner_feed_infos( 'feed_item_actors', $params, $feed_infos );
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
		return AMVE_Item::get_data_for_json( $this->item, $cpt );
	}

	private function get_partner_feed_infos( $partner_feed_item, $params, $feed_infos ) {

		$partner_id = $params['partner']['id'];

		$output         = false;
		$feed_item_type = isset( $feed_infos[ $partner_feed_item ] ) ? key( $feed_infos[ $partner_feed_item ] ) : null;
		$short_item     = '';

		if ( isset( $feed_infos[ $partner_feed_item ][ $feed_item_type ] ) ) {

			$short_item = $feed_infos[ $partner_feed_item ][ $feed_item_type ];

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

			switch ( $feed_item_type ) {

				case 'data':
					$output = $short_item;
					break;

				case 'node':
					$output = $this->$short_item;

					if ( $partner_id == 'pornxs' && $short_item == 'StreamRotatorContent' ) {
						$output = str_replace( 'http:', 'https:', $output );
					}

					break;

				case 'node_foreach':
					$exploded_final_node = explode( 'foreach:', $short_item );

					if ( $exploded_final_node[1] ) {

						$final_node    = $exploded_final_node[1];
						$exploded_path = explode( '/', $exploded_final_node[0] );
						$path          = $exploded_path[0];
						$output        = array();
						$elts          = (array) $this->$path;

						foreach ( (array) $elts[ $final_node ] as $elt ) {
							if ( $partner_id == 'tubepornclassic' ) {
								$elt = 'https://static1.tubepornclassic.com/contents/videos_screenshots/' . $elt;
							}
							$output[] = (string) trim( $elt );
						}
					} else {
						$elts = $this->xpath( '//' . $short_item );
						foreach ( (array) $elts as $elt ) {
							$output[] = (string) trim( $elt );
						}
					}
					$output = implode( ',', (array) $output );
					break;

				case 'attributes':
					$output = $this->attributes()->$short_item;
					break;
			}
		}
		if ( ! $output ) {
			return false;
		}
		return AMVE_Item::clean_string( $output );
	}
}
