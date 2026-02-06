<?php

/**
 * Cache-Manager für Plugin-Einstellungen
 */
class ATKPOptionsCache {
	private static $options_cache = null;

	/**
	 * Prüft, ob der Cache aktiv sein soll
	 *
	 * @return bool
	 */
	private static function should_use_cache() {
		// Im Backend keinen Cache verwenden
		//if ( is_admin() ) {
		//	return false;
		//}

		// Bei AJAX-Requests keinen Cache verwenden
		//if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		//	return false;
		//}
		if ( isset( $_GET['tab'] ) && $_GET['tab'] == 'display_configuration_page' ) {
			return false;
		}

		return true;
	}

	/**
	 * Lädt alle Plugin-Optionen in den Cache
	 *
	 * @return array Alle Plugin-Optionen
	 */
	public static function get_options() {
		// Cache-Prüfung
		if ( ! self::should_use_cache() ) {
			return self::load_options();
		}

		if ( self::$options_cache === null ) {
			self::$options_cache = self::load_options();
		}

		return self::$options_cache;
	}

	/**
	 * Lädt die Optionen aus der Datenbank
	 *
	 * @return array
	 */
	private static function load_options() {
		global $wpdb;

		// Präfix für alle Plugin-Optionen
		$prefix = ATKP_PLUGIN_PREFIX . '_';

		// Alle Optionen mit dem Plugin-Präfix auf einmal abrufen
		$query = $wpdb->prepare(
			"SELECT option_name, option_value FROM {$wpdb->options}
            WHERE option_name LIKE %s",
			$prefix . '%'
		);

		$results = $wpdb->get_results( $query );

		// Standardwerte definieren
		$defaults = [
			$prefix . 'disablestyles'         => 0,
			$prefix . 'hideerrormessages'     => 1,
			$prefix . 'link_click_tracking'   => 0,
			$prefix . 'cache_duration'        => 1440,
			$prefix . 'mark_links'            => 1,
			$prefix . 'show_disclaimer'       => 0,
			$prefix . 'disclaimer_text'       => '',
			$prefix . 'add_to_cart'           => 0,
			$prefix . 'open_window'           => 1,
			$prefix . 'show_linkinfo'         => 0,
			$prefix . 'linkinfo_template'     => '',
			$prefix . 'access_csv_intervall'  => 1440,
			$prefix . 'check_enabled'         => '',
			$prefix . 'notification_interval' => 4320,
			$prefix . 'email_recipient'       => '',
			$prefix . 'short_title_length'    => 0,
			$prefix . 'show_moreoffers'       => 0,
			$prefix . 'moreoffers_template'   => '',
			$prefix . 'list_default_count'    => 0,
			$prefix . 'feature_count'         => 0,
			$prefix . 'description_length'    => 0,
			$prefix . 'boxcontent'            => '',
			$prefix . 'boxstyle'              => 1,
			$prefix . 'showprice'             => 1,
			$prefix . 'linkprime'             => 0,
			$prefix . 'jslink'                => 0,
			$prefix . 'pricecomparisonsort'   => 1,
			$prefix . 'affiliatechar'         => '*',
			$prefix . 'showpricediscount'     => 1,
			$prefix . 'showstarrating'        => 1,
			$prefix . 'showrating'            => 1,
			$prefix . 'loglevel'              => 'off'
		];

		$options = $defaults;

		// Alle Ergebnisse laden
		if ( $results ) {
			foreach ( $results as $row ) {
				$options[ $row->option_name ] = maybe_unserialize( $row->option_value );
			}
		}

		return $options;
	}

	/**
	 * Leert den Cache
	 */
	public static function flush_cache() {
		self::$options_cache = null;
		ATKPSettings::load_settings();
	}

	/**
	 * Holt einen einzelnen Optionswert aus dem Cache
	 *
	 * @param string $option_name Name der Option ohne Präfix
	 * @param mixed $default Standardwert
	 *
	 * @return mixed Optionswert
	 */
	public static function get_option( $option_name, $default = null ) {
		$options   = self::get_options();
		$full_name = ATKP_PLUGIN_PREFIX . $option_name;

		return isset( $options[ $full_name ] ) ? $options[ $full_name ] : $default;
	}
}
