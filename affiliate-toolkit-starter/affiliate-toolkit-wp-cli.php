<?php
/**
 * Affiliate Toolkit
 * Diese Datei stellt eine erweiterte Cronjob-Funktionalität zur Verfügung.
 * Hiermit kan man auch eine große Anzahl an Dateien verarbeiten.
 */
defined('ABSPATH') || exit;


//execute command line: wp eval-file --url=https://webseite.de/wp-content/plugins/affiliate-toolkit/affiliate-toolkit-wp-cli.php /var/www/html/wp-content/plugins/affiliate-toolkit/affiliate-toolkit-wp-cli.php

add_filter( 'do_rocket_generate_caching_files', '__return_false' );
ob_end_flush();
// PHP-Konfiguration optimieren
// phpcs:ignore WordPress.PHP.DevelopmentFunctions.prevent_path_disclosure_error_reporting
@error_reporting( E_ALL );
//1 Stunde maximale Ausführungszeit aktivieren (falls erlaubt)
// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_ini_set, Squiz.PHP.DiscouragedFunctions.Discouraged
@ini_set( "max_execution_time", 3600 );
// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_ini_set, Squiz.PHP.DiscouragedFunctions.Discouraged
@ini_set( "memory_limit", "4G" );
define( 'BASE_PATH', '/var/www/html/' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound
define( 'DOING_CRON', true ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound

$cron = new atkp_external_cron(); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$cron->execute();

class atkp_external_cron {

	function execute() {

		$crontype = atkp_options::$loader->get_crontype();
		$mode     = ATKPTools::get_get_parameter( 'mode', 'string' );


		switch ( $crontype ) {
			default:
			case 'wpcron':
				//wp cron? nothing todo...
				throw new exception( 'external cronjob deactivated' );
				exit;
			case 'external':
			case 'externaloutput':
				$cronjob = new atkp_cronjob_new( $crontype == 'externaloutput' );
				$cronjob->do_work( false, $mode );
				break;
		}


		//exit the script
		exit;
	}

}

?>
