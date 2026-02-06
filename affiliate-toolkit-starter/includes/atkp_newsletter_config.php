<?php
/**
 * Newsletter Configuration
 * Konfiguration für die Newsletter-Anmeldung
 *
 * SICHERHEIT: Der API Key liegt auf dem Server (WordPress-Installation), nicht im Plugin!
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// WordPress REST API Endpoint für Newsletter-Anmeldung
// WICHTIG: Das Snippet muss zuerst in der WordPress-Installation unter affiliate-toolkit.com installiert sein!
// Siehe: QUICKSTART_WP.md für Installationsanleitung
define( 'ATKP_NEWSLETTER_ENDPOINT', 'https://www.affiliate-toolkit.com/wp-json/atkp/v1/newsletter' );

