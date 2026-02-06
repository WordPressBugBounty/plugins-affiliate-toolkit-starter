<?php
/**
 * Created by PhpStorm.
 * User: Christof
 * Date: 01.12.2018
 * Time: 11:35
 */

class ATKPSettings {
	//Plugin-Prefix: Affiliate Toolkit Plugin (atkp)


	public static $access_csv_intervall;

	public static $access_cache_duration;
	public static $access_mark_links;
	public static $access_show_disclaimer;
	public static $access_disclaimer_text;
	public static $add_to_cart;
	public static $open_window;

	public static $enable_ssl;

	public static $show_linkinfo;
	public static $linkinfo_template;

	public static $check_enabled;
	public static $notification_interval;
	public static $email_recipient;
	public static $short_title_length;

	public static $show_moreoffers;
	public static $moreoffers_template;

	public static $list_default_count;
	public static $feature_count;
	public static $description_length;
	public static $boxcontent;

	public static $boxstyle;
	public static $bestsellerribbon;
	public static $showprice;
	public static $showpricediscount;
	public static $showstarrating;
	public static $showrating;

	public static $jslink;
	public static $linktracking;
	public static $linkprime;

	public static $disablestyles;
	public static $hideerrormessages;

	public static $pricecomparisonsort;


	public static $affiliate_char;

	/**
	 * Returns current plugin version.
	 *
	 * @return string Plugin version
	 */
	public static function plugin_get_version() {

		$plugin_data    = get_plugin_data( ATKP_PLUGIN_FILE );
		$plugin_version = $plugin_data['Version'];

		return $plugin_version;
	}

	/**
	 * Lädt alle Plugin-Einstellungen mit einer einzigen Datenbankabfrage
	 */
	public static function load_settings() {
		global $wpdb;

		// Präfix für alle Plugin-Optionen
		$prefix  = ATKP_PLUGIN_PREFIX . '_';
		$options = ATKPOptionsCache::get_options();

		// Werte den Klassenvariablen zuweisen
		ATKPSettings::$disablestyles          = $options[ $prefix . 'disablestyles' ];
		ATKPSettings::$hideerrormessages      = $options[ $prefix . 'hideerrormessages' ];
		ATKPSettings::$linktracking           = $options[ $prefix . 'link_click_tracking' ];
		ATKPSettings::$access_cache_duration  = $options[ $prefix . 'cache_duration' ];
		ATKPSettings::$access_mark_links      = $options[ $prefix . 'mark_links' ];
		ATKPSettings::$access_show_disclaimer = $options[ $prefix . 'show_disclaimer' ];
		ATKPSettings::$access_disclaimer_text = $options[ $prefix . 'disclaimer_text' ];
		ATKPSettings::$add_to_cart            = $options[ $prefix . 'add_to_cart' ];
		ATKPSettings::$open_window            = $options[ $prefix . 'open_window' ];
		ATKPSettings::$show_linkinfo          = $options[ $prefix . 'show_linkinfo' ];
		ATKPSettings::$linkinfo_template      = $options[ $prefix . 'linkinfo_template' ];
		ATKPSettings::$access_csv_intervall   = $options[ $prefix . 'access_csv_intervall' ];
		ATKPSettings::$check_enabled          = $options[ $prefix . 'check_enabled' ];
		ATKPSettings::$notification_interval  = $options[ $prefix . 'notification_interval' ];
		ATKPSettings::$email_recipient        = $options[ $prefix . 'email_recipient' ];
		ATKPSettings::$short_title_length     = $options[ $prefix . 'short_title_length' ];
		ATKPSettings::$show_moreoffers        = $options[ $prefix . 'show_moreoffers' ];
		ATKPSettings::$moreoffers_template    = $options[ $prefix . 'moreoffers_template' ];
		ATKPSettings::$list_default_count     = $options[ $prefix . 'list_default_count' ];
		ATKPSettings::$feature_count          = $options[ $prefix . 'feature_count' ];
		ATKPSettings::$description_length     = $options[ $prefix . 'description_length' ];
		ATKPSettings::$boxcontent             = $options[ $prefix . 'boxcontent' ];
		ATKPSettings::$boxstyle               = $options[ $prefix . 'boxstyle' ];
		ATKPSettings::$bestsellerribbon       = 2; // Dieser Wert scheint hardcodiert zu sein
		ATKPSettings::$showprice              = $options[ $prefix . 'showprice' ];
		ATKPSettings::$linkprime              = $options[ $prefix . 'linkprime' ];
		ATKPSettings::$jslink                 = $options[ $prefix . 'jslink' ];
		ATKPSettings::$pricecomparisonsort    = $options[ $prefix . 'pricecomparisonsort' ];
		ATKPSettings::$affiliate_char         = $options[ $prefix . 'affiliatechar' ];
		ATKPSettings::$showpricediscount      = $options[ $prefix . 'showpricediscount' ];
		ATKPSettings::$showstarrating         = $options[ $prefix . 'showstarrating' ];
		ATKPSettings::$showrating             = $options[ $prefix . 'showrating' ];

		// Log-Level initialisieren
		ATKPLog::Init( ATKP_LOGFILE, $options[ $prefix . 'loglevel' ] );
	}

	public static function atkp_version_compare( $ver1, $ver2, $operator = null ) {
		$p    = '#(\.0+)+($|-)#';
		$ver1 = preg_replace( $p, '', $ver1 );
		$ver2 = preg_replace( $p, '', $ver2 );

		return isset( $operator ) ?
			version_compare( $ver1, $ver2, $operator ) :
			version_compare( $ver1, $ver2 );
	}

}
