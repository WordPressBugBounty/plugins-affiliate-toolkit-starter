<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * When the security notice about raw PHP in templates (since 3.8.6) is shown.
 *
 * The notice is only relevant for sites updated from an older version. A fresh installation never had
 * raw PHP templates, so it is marked as confirmed on the first activation.
 */

/**
 * Called from the activation hook, before atkp_version_plugin is written. That option is set on every
 * activation and never deleted, so it is missing only on the very first activation on this site.
 */
function atkp_template_sanitize_notice_on_activation() {
	if ( get_option( 'atkp_version_plugin', false ) === false ) {
		update_option( 'atkp_template_sanitize_notice_confirmed', true );
	}
}

/**
 * @param string $tab current admin tab (?tab=)
 */
function atkp_template_sanitize_notice_visible( $tab ) {
	// The first steps page right after activation stays free of warnings.
	if ( $tab === 'welcome_page' ) {
		return false;
	}

	return ! get_option( 'atkp_template_sanitize_notice_confirmed', false );
}
