<?php
/**
 * Affiliate Toolkit
 * Diese Datei stellt eine erweiterte Cronjob-Funktionalität zur Verfügung.
 * Hiermit kan man auch eine große Anzahl an Dateien verarbeiten.
 */
defined('ABSPATH') || exit;

// PHP-Konfiguration optimieren
//@error_reporting( E_ALL );
//1 Stunde maximale Ausführungszeit aktivieren (falls erlaubt)
//@ini_set( "max_execution_time", 3600 );
//@ini_set( "memory_limit", "512M" );
if ( ! defined( 'WP_MAX_MEMORY_LIMIT' ) ) {
	define( 'WP_MAX_MEMORY_LIMIT', '512M' );
}

if ( ! defined( 'SAVEQUERIES' ) ) {
	define( 'SAVEQUERIES', 0 ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound
}

if ( ! defined( 'WP_DISABLE_FATAL_ERROR_HANDLER' ) ) {
	define( 'WP_DISABLE_FATAL_ERROR_HANDLER', true ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound
}

if ( defined( 'WP_LOAD_PATH' ) ) {
	$atkp_default_wp_path = WP_LOAD_PATH;
} else {
	$atkp_default_wp_path = './../../../wp-load.php';

	if ( ! file_exists( $atkp_default_wp_path ) && isset( $_SERVER['DOCUMENT_ROOT'] ) ) {
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- WP not loaded yet, using realpath for validation
		$atkp_default_wp_path = realpath( stripslashes( $_SERVER['DOCUMENT_ROOT'] ) ) . '/wp-load.php';
	}

	if ( ! file_exists( $atkp_default_wp_path ) && isset( $_SERVER['SCRIPT_FILENAME'] ) ) {
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- WP not loaded yet, using realpath for validation
		$atkp_script_filename = realpath( stripslashes( $_SERVER['SCRIPT_FILENAME'] ) );
		$atkp_parse_uri       = explode( 'wp-content', $atkp_script_filename );
		$atkp_default_wp_path = $atkp_parse_uri[0] . 'wp-load.php';
	}
}

if ( ! file_exists( $atkp_default_wp_path ) ) {
	atkp_cron_error_page( 'wp-load.php not found', 'WordPress could not be loaded. The file was expected at: ' . htmlspecialchars( $atkp_default_wp_path ) );
}

try {
	require( $atkp_default_wp_path );

	if ( ! class_exists( 'atkp_options' ) || ! class_exists( 'ATKPTools' ) ) {
		atkp_cron_error_page( 'Plugin not loaded', 'affiliate-toolkit is not active or was not loaded correctly. Please make sure the plugin is activated in WordPress.' );
	}

	$atkp_cron = new atkp_external_cron();
	$atkp_cron->execute();
} catch ( \Throwable $e ) {
	atkp_cron_error_page( get_class( $e ) . ': ' . $e->getMessage(), null, $e );
}

function atkp_cron_error_page( string $title, ?string $detail = null, ?\Throwable $exception = null ) {
	if ( ! headers_sent() ) {
		header( 'Content-Type: text/html; charset=UTF-8', true, 500 );
	}
	?>
	<!DOCTYPE html>
	<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>affiliate-toolkit Cron Error</title>
		<style>
			* { margin: 0; padding: 0; box-sizing: border-box; }
			body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background: #f0f2f5; color: #1e1e1e; padding: 40px 20px; }
			.atkp-error-wrap { max-width: 720px; margin: 0 auto; }
			.atkp-error-header { display: flex; align-items: center; gap: 12px; margin-bottom: 24px; }
			.atkp-error-header svg { flex-shrink: 0; }
			.atkp-error-header h1 { font-size: 20px; font-weight: 600; color: #1e1e1e; }
			.atkp-error-card { background: #fff; border-radius: 8px; border: 1px solid #e0e0e0; overflow: hidden; }
			.atkp-error-card-header { background: #dc3232; color: #fff; padding: 16px 20px; font-size: 15px; font-weight: 600; }
			.atkp-error-card-body { padding: 20px; }
			.atkp-error-card-body p { font-size: 14px; line-height: 1.6; color: #50575e; margin-bottom: 16px; }
			.atkp-error-card-body p:last-child { margin-bottom: 0; }
			.atkp-stacktrace { background: #1e1e1e; color: #e0e0e0; border-radius: 6px; padding: 16px; font-family: "SFMono-Regular", Consolas, "Liberation Mono", Menlo, monospace; font-size: 12px; line-height: 1.7; overflow-x: auto; white-space: pre-wrap; word-break: break-all; margin-top: 16px; }
			.atkp-stacktrace .atkp-st-label { color: #8c8c8c; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 8px; }
			.atkp-stacktrace .atkp-st-file { color: #7cacf8; }
			.atkp-stacktrace .atkp-st-line { color: #f5a623; }
			.atkp-stacktrace .atkp-st-num { color: #8c8c8c; user-select: none; }
			.atkp-error-footer { margin-top: 20px; font-size: 12px; color: #8c8c8c; text-align: center; }
			.atkp-error-info { background: #f6f7f7; border: 1px solid #e0e0e0; border-radius: 6px; padding: 12px 16px; margin-top: 16px; font-size: 13px; color: #50575e; }
			.atkp-error-info dt { font-weight: 600; display: inline; }
			.atkp-error-info dt::after { content: ": "; }
			.atkp-error-info dd { display: inline; margin: 0; }
			.atkp-error-info dd::after { content: ""; display: block; margin-bottom: 4px; }
			.atkp-error-info dd:last-child::after { margin-bottom: 0; }
		</style>
	</head>
	<body>
		<div class="atkp-error-wrap">
			<div class="atkp-error-header">
				<svg width="32" height="32" viewBox="0 0 32 32" fill="none"><circle cx="16" cy="16" r="16" fill="#dc3232"/><path d="M16 9v8" stroke="#fff" stroke-width="2.5" stroke-linecap="round"/><circle cx="16" cy="22" r="1.5" fill="#fff"/></svg>
				<h1>affiliate-toolkit &mdash; Cron Error</h1>
			</div>
			<div class="atkp-error-card">
				<div class="atkp-error-card-header"><?php echo esc_html( $title ); ?></div>
				<div class="atkp-error-card-body">
					<?php if ( $detail ) : ?>
						<p><?php
						// Using htmlspecialchars because this may run before WordPress is loaded.
						echo htmlspecialchars( $detail, ENT_QUOTES, 'UTF-8' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?></p>
					<?php endif; ?>

					<?php if ( $exception ) : ?>
						<dl class="atkp-error-info">
							<dt>File</dt><dd><?php echo esc_html( $exception->getFile() ); ?></dd>
							<dt>Line</dt><dd><?php echo (int) $exception->getLine(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></dd>
							<dt>Code</dt><dd><?php echo htmlspecialchars( $exception->getCode(), ENT_QUOTES, 'UTF-8' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></dd>
						</dl>

						<div class="atkp-stacktrace"><span class="atkp-st-label">Stack Trace</span><?php
							$frames = explode( "\n", $exception->getTraceAsString() );
							foreach ( $frames as $frame ) {
								$frame = htmlspecialchars( $frame );
								// highlight file paths and line numbers
								$frame = preg_replace( '/(#\d+)/', '<span class="atkp-st-num">$1</span>', $frame );
								$frame = preg_replace( '/([^\s(]+\.php)/', '<span class="atkp-st-file">$1</span>', $frame );
								$frame = preg_replace( '/\((\d+)\)/', '(<span class="atkp-st-line">$1</span>)', $frame );
								// Frame already escaped via htmlspecialchars; span tags are intentional.
								echo $frame . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							}
						?></div>
					<?php endif; ?>
				</div>
			</div>
			<div class="atkp-error-footer">affiliate-toolkit v<?php echo defined( 'ATKP_VERSION' ) ? htmlspecialchars( ATKP_VERSION, ENT_QUOTES, 'UTF-8' ) : '?'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> &middot; PHP <?php echo htmlspecialchars( PHP_VERSION, ENT_QUOTES, 'UTF-8' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> &middot; <?php echo htmlspecialchars( gmdate( 'Y-m-d H:i:s T' ), ENT_QUOTES, 'UTF-8' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
		</div>
	</body>
	</html>
	<?php
	exit;
}

class atkp_external_cron {

	function execute() {
		$crontype = atkp_options::$loader->get_crontype();
		$mode     = ATKPTools::get_get_parameter( 'mode', 'string' );
		$key = ATKPTools::get_get_parameter( 'key', 'string' );

		$cron_key = ATKPTools::get_setting( ATKP_PLUGIN_PREFIX . '_cronkey' );

		if ( php_sapi_name() == 'cli' )
			echo 'cmd mode running'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		else if ( defined( 'WP_CLI' ) && WP_CLI ) {
			echo 'cli mode running'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			if ( $key == '' || trim( $cron_key ) != trim( $key ) ) {
				echo 'key invalid'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				die( 401 );
			}
		}


		switch ( $crontype ) {
			default:
			case 'wpcron':
				//wp cron? nothing todo...
			echo 'external cronjob deactivated'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			die( 404 );
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