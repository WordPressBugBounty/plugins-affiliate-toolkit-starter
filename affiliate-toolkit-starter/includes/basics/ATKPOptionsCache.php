<?php
defined('ABSPATH') || exit;

/**
 * Cache-Manager für Plugin-Einstellungen
 */
class ATKPOptionsCache {
	/**
	 * @var array|null ['options' => array merged with the fallbacks, 'db' => keys present in wp_options]
	 */
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
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
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
		return self::get_loaded()['options'];
	}

	/**
	 * Lädt Optionswerte und die Liste der tatsächlich gespeicherten Optionsnamen
	 *
	 * @return array ['options' => array, 'db' => array]
	 */
	private static function get_loaded() {
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
	 * @return array ['options' => array, 'db' => array]
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

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		$results = $wpdb->get_results( $query );

		// Standardwerte definieren
		$defaults = [
			$prefix . 'disablestyles'         => 0,
			$prefix . 'hideerrormessages'     => 1,
			$prefix . 'link_click_tracking'   => 0,
			$prefix . 'cache_duration'        => 1440,
			$prefix . 'mark_links'            => 1,
			$prefix . 'show_disclaimer'       => 1,
			$prefix . 'add_to_cart'           => 'link',
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
			$prefix . 'boxcontent'            => 1,
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
		$db      = array();

		// Alle Ergebnisse laden
		if ( $results ) {
			foreach ( $results as $row ) {
				$options[ $row->option_name ] = maybe_unserialize( $row->option_value );
				$db[ $row->option_name ]      = true;
			}
		}

		return array( 'options' => $options, 'db' => $db );
	}

	/**
	 * Standardtext des Disclaimers - die einzige Quelle dafür, damit atkp_options und
	 * ATKPSettings nicht auseinanderlaufen können. Genau diese Doppelung war die Ursache
	 * dafür, dass die "Last updated on ..." Zeile nicht mehr ausgegeben wurde.
	 *
	 * Absichtlich nicht in der Fallback-Tabelle oben: load_options() läuft vor dem init Hook,
	 * und ein __() Aufruf dort löst auf WordPress 6.7+ die "translation loading triggered too
	 * early" Meldung aus. Der Text wird deshalb erst bei der Ausgabe aufgelöst.
	 *
	 * @return string
	 */
	public static function get_default_disclaimer_text() {
		return stripslashes( __( 'Last updated on %refresh_date% at %refresh_time% - Image source: Amazon Affiliate Program. All statements without guarantee.', 'affiliate-toolkit-starter' ) );
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
		$loaded    = self::get_loaded();
		$full_name = ATKP_PLUGIN_PREFIX . $option_name;

		//gespeicherter Wert gewinnt immer, auch wenn er leer ist
		if ( isset( $loaded['db'][ $full_name ] ) ) {
			return $loaded['options'][ $full_name ];
		}

		//Option wurde nie gespeichert: der Standardwert des Aufrufers ist der maßgebliche.
		//Vor der Cache-Umstellung lieferte get_option() genau diesen Wert zurück, während die
		//Fallback-Tabelle ihn verdeckt hat.
		if ( $default !== null ) {
			return $default;
		}

		return isset( $loaded['options'][ $full_name ] ) ? $loaded['options'][ $full_name ] : $default;
	}
}
