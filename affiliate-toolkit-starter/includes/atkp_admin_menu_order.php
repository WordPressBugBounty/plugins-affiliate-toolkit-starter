<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Order of the entries in the affiliate-toolkit admin menu.
 *
 * WordPress adds the menu entries of the post types in the order the post types are registered. The
 * extensions Importset and Influencer are loaded before the main plugin (alphabetical plugin order), so
 * "Importsets" and "Redirects" stood above Shops, Products and Lists, and the top level menu entry opened
 * the importsets. They are moved below the product import.
 */

/**
 * @param array[] $items submenu entries as in $submenu[ $parent ] (index 2 is the slug)
 *
 * @return array[] the entries in the new order, keys renumbered
 */
function atkp_admin_menu_order_items( array $items ) {
	$moved_slugs = array( 'edit.php?post_type=atkp_importset', 'edit.php?post_type=atkp_redirect' );

	$moved = array();
	$rest  = array();
	foreach ( $items as $item ) {
		if ( in_array( $item[2] ?? '', $moved_slugs, true ) ) {
			$moved[] = $item;
		} else {
			$rest[] = $item;
		}
	}

	if ( count( $moved ) == 0 ) {
		return array_values( $items );
	}

	//info: hinter dem Produktimport, ohne ihn (fehlende Berechtigung) ans Ende
	$position = count( $rest );
	foreach ( $rest as $index => $item ) {
		if ( ( $item[2] ?? '' ) === 'atkp_bulkimport' ) {
			$position = $index + 1;
			break;
		}
	}

	array_splice( $rest, $position, 0, $moved );

	return $rest;
}

/**
 * Runs after every extension has added its entries (admin_menu, late priority).
 */
function atkp_admin_menu_order() {
	global $submenu;

	$parent = ATKP_PLUGIN_PREFIX . '_affiliate_toolkit-plugin';
	if ( isset( $submenu[ $parent ] ) && is_array( $submenu[ $parent ] ) ) {
		$submenu[ $parent ] = atkp_admin_menu_order_items( $submenu[ $parent ] );
	}
}

add_action( 'admin_menu', 'atkp_admin_menu_order', 999 );
