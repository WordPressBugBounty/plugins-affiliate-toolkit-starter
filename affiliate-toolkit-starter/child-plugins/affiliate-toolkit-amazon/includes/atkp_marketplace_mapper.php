<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class atkp_marketplace_mapper {

	private static $marketplaces = array(
		// EU Region
		'de'     => array( 'url' => 'www.amazon.de',     'region' => 'EU', 'version' => '2.2' ),
		'co.uk'  => array( 'url' => 'www.amazon.co.uk',  'region' => 'EU', 'version' => '2.2' ),
		'fr'     => array( 'url' => 'www.amazon.fr',     'region' => 'EU', 'version' => '2.2' ),
		'it'     => array( 'url' => 'www.amazon.it',     'region' => 'EU', 'version' => '2.2' ),
		'es'     => array( 'url' => 'www.amazon.es',     'region' => 'EU', 'version' => '2.2' ),
		'nl'     => array( 'url' => 'www.amazon.nl',     'region' => 'EU', 'version' => '2.2' ),
		'pl'     => array( 'url' => 'www.amazon.pl',     'region' => 'EU', 'version' => '2.2' ),
		'com.be' => array( 'url' => 'www.amazon.com.be', 'region' => 'EU', 'version' => '2.2' ),
		'com.tr' => array( 'url' => 'www.amazon.com.tr', 'region' => 'EU', 'version' => '2.2' ),
		'ae'     => array( 'url' => 'www.amazon.ae',     'region' => 'EU', 'version' => '2.2' ),
		'in'     => array( 'url' => 'www.amazon.in',     'region' => 'EU', 'version' => '2.2' ),

		// NA Region
		'com'    => array( 'url' => 'www.amazon.com',    'region' => 'NA', 'version' => '2.1' ),
		'ca'     => array( 'url' => 'www.amazon.ca',     'region' => 'NA', 'version' => '2.1' ),
		'com.br' => array( 'url' => 'www.amazon.com.br', 'region' => 'NA', 'version' => '2.1' ),
		'com.mx' => array( 'url' => 'www.amazon.com.mx', 'region' => 'NA', 'version' => '2.1' ),

		// FE Region
		'co.jp'  => array( 'url' => 'www.amazon.co.jp',  'region' => 'FE', 'version' => '2.3' ),
		'au'     => array( 'url' => 'www.amazon.com.au',  'region' => 'FE', 'version' => '2.3' ),
	);

	/**
	 * Get the marketplace URL for a given country code.
	 *
	 * @param string $country_code The country code (e.g. 'de', 'com', 'co.uk')
	 * @return string The marketplace URL (e.g. 'www.amazon.de')
	 */
	public static function get_marketplace( $country_code ) {
		if ( isset( self::$marketplaces[ $country_code ] ) ) {
			return self::$marketplaces[ $country_code ]['url'];
		}
		return 'www.amazon.' . $country_code;
	}

	/**
	 * Get the default credential version for a given country code.
	 *
	 * @param string $country_code The country code
	 * @return string The default credential version (e.g. '2.1', '2.2', '2.3')
	 */
	public static function get_default_version( $country_code ) {
		if ( isset( self::$marketplaces[ $country_code ] ) ) {
			return self::$marketplaces[ $country_code ]['version'];
		}
		return '2.2'; // Default to EU
	}

	/**
	 * Get the region for a given country code.
	 *
	 * @param string $country_code The country code
	 * @return string The region (NA, EU, FE)
	 */
	public static function get_region( $country_code ) {
		if ( isset( self::$marketplaces[ $country_code ] ) ) {
			return self::$marketplaces[ $country_code ]['region'];
		}
		return 'EU';
	}

	/**
	 * Get all available credential versions.
	 *
	 * @return array Associative array of version => label
	 */
	public static function get_credential_versions() {
		return array(
			'2.1' => '2.1 (NA)',
			'2.2' => '2.2 (EU)',
			'2.3' => '2.3 (FE)',
			'3.1' => '3.1 (NA LWA)',
			'3.2' => '3.2 (EU LWA)',
			'3.3' => '3.3 (FE LWA)',
		);
	}
}
