<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class atkp_curl_helper {
	private $cookies = array();
	private $cookie_path;
	private $agent;

	// userId will be used later to keep multiple users logged
	// into ebay site at one time.
	public function __construct( $userId = 'default' ) {
		$this->cookie_path = wp_upload_dir()['basedir'] . '/atkp-cookies/' . $userId . '.txt';

		if ( ! file_exists( dirname( $this->cookie_path ) ) ) {
			wp_mkdir_p( dirname( $this->cookie_path ) );
		}

		$this->agent = "Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)";

		// Load cookies from file if they exist
		if ( file_exists( $this->cookie_path ) ) {
			$cookie_data = file_get_contents( $this->cookie_path );
			if ( ! empty( $cookie_data ) ) {
				$this->cookies = maybe_unserialize( $cookie_data );
				if ( ! is_array( $this->cookies ) ) {
					$this->cookies = array();
				}
			}
		}
	}

	// Build default request arguments
	private function getDefaultArgs() {
		return array(
			'user-agent'  => $this->agent,
			'redirection' => 5,
			'sslverify'   => false,
			'headers'     => array(
				'Accept'     => '*/*',
				'Connection' => 'Keep-Alive',
			),
			'cookies'     => $this->cookies,
		);
	}

	// Save cookies from response
	private function saveCookies( $response ) {
		if ( ! is_wp_error( $response ) ) {
			$response_cookies = wp_remote_retrieve_cookies( $response );
			if ( ! empty( $response_cookies ) ) {
				foreach ( $response_cookies as $cookie ) {
					$this->cookies[ $cookie->name ] = $cookie->value;
				}
				file_put_contents( $this->cookie_path, maybe_serialize( $this->cookies ) );
			}
		}
	}

	// Grab initial cookie data
	public function curl_cookie_set( $submit_url ) {
		$args     = $this->getDefaultArgs();
		$response = wp_remote_get( $submit_url, $args );

		if ( is_wp_error( $response ) ) {
			echo esc_html( $response->get_error_message() );
		}

		$this->saveCookies( $response );
	}

	// Grab hidden fields
	public function get_form_fields( $submit_url ) {
		$args     = $this->getDefaultArgs();
		$response = wp_remote_get( $submit_url, $args );

		if ( is_wp_error( $response ) ) {
			echo esc_html( $response->get_error_message() );

			return array();
		}

		$this->saveCookies( $response );
		$result = wp_remote_retrieve_body( $response );

		return $this->getFormFields( $result );
	}

	// Send login data
	public function curl_post_request( $referer, $submit_url, $data ) {
		$args            = $this->getDefaultArgs();
		$args['body']    = $data;
		$args['headers']['Referer'] = $referer;

		$response = wp_remote_post( $submit_url, $args );

		if ( is_wp_error( $response ) ) {
			echo esc_html( $response->get_error_message() );

			return '';
		}

		$this->saveCookies( $response );

		return wp_remote_retrieve_body( $response );
	}

	// Show the logged in "My eBay" or any other page
	public function show_page( $submit_url ) {
		$args     = $this->getDefaultArgs();
		$response = wp_remote_get( $submit_url, $args );

		if ( is_wp_error( $response ) ) {
			echo esc_html( $response->get_error_message() );

			return '';
		}

		$this->saveCookies( $response );

		return wp_remote_retrieve_body( $response );
	}

	// Used to parse out form
	private function getFormFields( $data ) {
		if ( preg_match( '/(<form name="SignInForm".*?<\/form>)/is', $data, $matches ) ) {
			$inputs = $this->getInputs( $matches[1] );

			return $inputs;
		} else {
			die( 'Form not found.' );
		}
	}

	// Used to parse out hidden field names and values
	private function getInputs( $form ) {
		$inputs   = array();
		$elements = preg_match_all( '/(<input[^>]+>)/is', $form, $matches );

		if ( $elements > 0 ) {
			for ( $i = 0; $i < $elements; $i ++ ) {
				$el = preg_replace( '/\s{2,}/', ' ', $matches[1][ $i ] );

				if ( preg_match( '/name=(?:["\'])?([^"\'\s]*)/i', $el, $name ) ) {
					$name  = $name[1];
					$value = '';

					if ( preg_match( '/value=(?:["\'])?([^"\'\s]*)/i', $el, $value ) ) {
						$value = $value[1];
					}

					$inputs[ $name ] = $value;
				}
			}
		}

		return $inputs;
	}

	// Destroy cookie and clean up.
	public function curl_clean() {
		// cleans cookie data
		if ( file_exists( $this->cookie_path ) ) {
			wp_delete_file( $this->cookie_path );
		}
		$this->cookies = array();
	}
}

?>