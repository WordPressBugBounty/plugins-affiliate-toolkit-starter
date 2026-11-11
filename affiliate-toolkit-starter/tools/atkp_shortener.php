<?php
// phpcs:disable WordPress.WP.AlternativeFunctions.curl_curl_init, WordPress.WP.AlternativeFunctions.curl_curl_setopt, WordPress.WP.AlternativeFunctions.curl_curl_exec
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class atkp_shortener {

	/**
	 * This function is for generating short links
	 *
	 * @param string|null $url
	 * @param string|null $url_title
	 * @param atkp_redirection_type|int $shortener_id
	 * @param string|null $api_key
	 *
	 * @return string
	 */
	public function shorten_url( $url, $url_title, $shortener_id, $api_key ) {
		//the values come from post meta and can be null (e.g. a product without add-to-cart url)
		$url       = (string) $url;
		$url_title = (string) $url_title;
		$api_key   = (string) $api_key;

		if ( $url == '' ) {
			return $url;
		}

		switch ( $shortener_id ) {
			case atkp_redirection_type::BIT_LY:

				if ( $api_key == '' ) {
					return $url;
				}

				$apiv4 = 'https://api-ssl.bitly.com/v4/bitlinks';

				$data    = array(
					'long_url' => $url,
					'title'    => $url_title
				);
				$payload = json_encode( $data );

				$response = wp_remote_post( $apiv4, array(
					'headers' => array(
						'Authorization' => 'Bearer ' . $api_key,
						'Content-Type'  => 'application/json',
					),
					'body'    => $payload,
					'timeout' => 30,
				) );
				$result       = wp_remote_retrieve_body( $response );
				$resultToJson = json_decode( $result );

				if ( isset( $resultToJson->link ) ) {
					$url = $resultToJson->link;
				}

				break;
		}

		return $url;
	}


}


