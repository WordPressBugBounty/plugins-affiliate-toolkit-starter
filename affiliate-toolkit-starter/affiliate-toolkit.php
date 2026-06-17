<?php
/*** Plugin Name: affiliate-toolkit – Multi-Network Affiliate & Amazon Product Display
 * Plugin URI: https://www.affiliate-toolkit.com
 * Description: Display products from Amazon, AWIN, CJ, eBay and 10+ affiliate networks with beautiful product boxes, comparison tables, and automatic price updates.
 * Version: 3.8.8
 * Requires PHP:      8.2
 * Author: SERVIT Software Solutions
 * Author URI: https://servit.dev
 *
 *  Text Domain: affiliate-toolkit-starter
 *  Domain Path: /lang
 * License: GPL2
 */

define( 'ATKP_UPDATE_VERSION', '3.8.8' );
define( 'ATKP_UPDATE_ITEM_ID', '7680' );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

define( 'ATKP_PLUGIN_PREFIX', 'ATKP' );
define( 'ATKP_PLUGIN_DIR', dirname( __FILE__ ) );
define( 'ATKP_PLUGIN_FILE', __FILE__ );

//registering the internal autoloader
require_once ATKP_PLUGIN_DIR . '/includes/atkp_autoloader.php';
new atkp_autoloader();
atkp_autoloader::$loader->register_classes();

add_action( 'init', 'atkp_lang' );
function atkp_lang() {
	// Note: Since WP 4.6+, WordPress automatically loads translations from
	// wp-content/languages/plugins/ for the standard text domain. This call
	// is kept for backward compatibility with custom translation paths.
	// phpcs:ignore PluginCheck.CodeAnalysis.DiscouragedFunctions.load_plugin_textdomainFound
	load_plugin_textdomain( 'affiliate-toolkit-starter', false, dirname( plugin_basename( ATKP_PLUGIN_FILE ) ) . '/lang' );
}

add_action( 'plugins_loaded', 'atkp_plugins_loaded' );
function atkp_plugins_loaded() {
	do_action( 'atkp_module_updater' );
}

add_action( 'publish_to_trash', 'atkp_to_trash' );
add_action( 'draft_to_trash', 'atkp_to_trash' );
add_action( 'future_to_trash', 'atkp_to_trash' );
add_action( 'deleted_post', 'atkp_deleted', 10, 2 );


function atkp_deleted( $post_id, $post ) {

	if ( $post->post_type == 'atkp_product' ) {
		do_action( 'atkp_product_deleted', $post->ID );
	} else if ( $post->post_type == 'atkp_shop' ) {
		do_action( 'atkp_shop_deleted', $post->ID );
	} else if ( $post->post_type == 'atkp_list' ) {
		do_action( 'atkp_list_deleted', $post->ID );
	}
}

function atkp_to_trash( $post ) {

	if ( $post->post_type == 'atkp_product' ) {
		do_action( 'atkp_product_to_trash', $post->ID );
	} else if ( $post->post_type == 'atkp_shop' ) {
		do_action( 'atkp_shop_to_trash', $post->ID );
	} else if ( $post->post_type == 'atkp_list' ) {
		do_action( 'atkp_list_to_trash', $post->ID );
	}
}

require_once ATKP_PLUGIN_DIR . '/includes/atkp_basics.php';
require_once ATKP_PLUGIN_DIR . '/includes/atkp_newsletter_popup.php';


add_action( 'atkp_initialize_widgets', 'atkp_initialize_widgets_callback' );

function atkp_initialize_widgets_callback() {

	new atkp_widget();
}


//** Plugin initialisieren **//

add_action( 'init', 'atkp_init', 11 );

function atkp_init() {
	if ( version_compare( get_bloginfo( 'version' ), '4.0', '<' ) ) {
		wp_die( "You must update WordPress to use affiliate-toolkit!" );
	}


	// Register Custom Post Types (needed for both admin and frontend)
	new atkp_posttypes_shop( array() );
	new atkp_posttypes_product( array() );
	new atkp_posttypes_list( array() );
	new atkp_posttypes_template( array() );

	do_action( 'atkp_register_cpt' );

	// Modern Shortcode Generator mit Gutenberg-Unterstützung
	// Initialize for both frontend and backend to support block rendering
	atkp_shortcode_generator_modern::get_instance( array() );

	if ( is_admin() ) {
		add_action( 'admin_menu', 'atkp_init_menu', 20 );

		// Alternative: Alter Generator (deprecated)
		// new atkp_shortcode_generator2( array() );

		//$g = new atkp_generator();
		//$g->register_subpage();

		add_action( 'admin_enqueue_scripts', 'atkp_admin_styles' );

		new atkp_bulkimport( array() );
		new atkp_extensions();
		new atkp_queue_view();
		new atkp_template_view();

	} else {

		new atkp_posttypes_product( array() );
		new atkp_posttypes_template( array() );


		add_action( 'wp_enqueue_scripts', 'atkp_frontend_styles' );

		//enable shortcodes at widget area
		//add_filter('widget_text', 'do_shortcode');

		if ( atkp_options::$loader->get_show_credits() ) {
			add_filter( 'the_content', 'atkp_credits', 20 );
		}
	}


	//shortcodes für diverse backend editoren immer erzeugen..

	new atkp_shortcodes_product( array() );
	new atkp_shortcodes_list( array() );
	new atkp_shortcodes_atkp( array() );

	new atkp_wp_cronjob( true);
	new atkp_queueservices();
	new atkp_output_handle();


}

$atkp_cronjob = new atkp_wp_cronjob( false );
$atkp_cronjob->register_cron_hooks();

register_activation_hook( ATKP_PLUGIN_FILE, 'atkp_activated');

function atkp_activated() {

	//if ((get_option('atkp_version_plugin', false)) === false) {
	update_option( 'atkp_version_plugin', '1.0' );
	update_option( 'atkp_redirect_to_welcome', true );
	//}

}


add_action( 'admin_init', 'atkp_welcome_redirect', 9999 );

function atkp_welcome_redirect() {
	if ( ! get_option( 'atkp_redirect_to_welcome', false ) ) {
		return;
	}

	delete_option( 'atkp_redirect_to_welcome' );

	wp_safe_redirect( admin_url( 'admin.php?page=ATKP_affiliate_toolkit-tools&tab=welcome_page' ) );
	exit;
}

add_action( 'plugins_loaded', 'atkp_loaded');

function atkp_loaded() {
	do_action( 'atkp_initialize_extensions' );

	if ( is_admin() ) {

		$atkp_settings = new atkp_settings( array() );

		$tempsettings = array(
			__( 'General settings', 'affiliate-toolkit-starter' )  => array(
				new atkp_settings_toolkit( array() ),
				'toolkit_configuration_page'
			),
			__( 'Advanced settings', 'affiliate-toolkit-starter' ) => array(
				new atkp_settings_advanced( array() ),
				'advanced_configuration_page'
			),
			__( 'Display settings', 'affiliate-toolkit-starter' )  => array(
				new atkp_settings_display( array() ),
				'display_configuration_page'
			),
			__( 'Licenses', 'affiliate-toolkit-starter' )          => array(
				new atkp_settings_license( array() ),
				'license_configuration_page'
			)
		);


		$tempsettings = apply_filters( 'atkp_pages_settings', $tempsettings );


		$atkp_settings::$settings = $tempsettings;

		$compatibility = array();
		$compatibility = apply_filters( 'atkp_pages_compatibility', $compatibility );

		if ( count( $compatibility ) > 0 ) {
			$atkp_compatibility         = new atkp_compatibility( array() );
			$atkp_compatibility::$modes = $compatibility;
		}


		$temptools = array();


		$temptools = apply_filters( 'atkp_pages_tools', $temptools );

		$temptools[ __( 'Shop replacement', 'affiliate-toolkit-starter' ) ] = array(
			new atkp_tools_shopreplace( array() ),
			'shopreplace_configuration_page'
		);
		$temptools[ __( 'Debug', 'affiliate-toolkit-starter' ) ]            = array(
			new atkp_tools_debug( array() ),
			'debug_configuration_page'
		);
		$temptools[ __( 'Welcome', 'affiliate-toolkit-starter' ) ]          = array(
			new atkp_tools_welcome( array() ),
			'welcome_page'
		);
		//$temptools[ __( 'Import template', ATKP_PLUGIN_PREFIX ) ] = array(
		//	new atkp_tools_import_template( array() ),
		//		'importtools_configuration_page'
		//);


		new atkp_tools_shortcodegenerator( array() );


		if ( count( $temptools ) > 0 ) {
			$atkp_tools         = new atkp_tools( array() );
			$atkp_tools::$tools = $temptools;
		}

	}
}

function atkp_init_menu() {
	add_menu_page(
		__( 'affiliate-toolkit', 'affiliate-toolkit-starter' ),
		__( 'affiliate-toolkit', 'affiliate-toolkit-starter' ),
		'edit_posts',
		ATKP_PLUGIN_PREFIX . '_affiliate_toolkit-plugin',
		null,
		plugin_dir_url( __FILE__ ) . '/images/affiliate_toolkit_menu.png',
		30
	);

	do_action( 'atkp_register_submenu', ATKP_PLUGIN_PREFIX . '_affiliate_toolkit-plugin' );
}


new atkp_gutenberg_editor( array() );


new atkp_endpoints( array() );

function atkp_credits( $content ) {

	if ( is_singular() && in_the_loop() && is_main_query() ) {
		global $post;
		if ( is_a( $post, 'WP_Post' ) ) {
			if ( has_shortcode( $post->post_content, 'affiliate-toolkit-starter' ) || has_shortcode( $post->post_content, 'atkp_list' ) || has_shortcode( $post->post_content, 'atkp_product' ) ) {
				$content .= '<div class="atkp-credits">' . ATKPTools::get_credits_link() . '</div>';
			}
		}


	}

	return $content;
}

function atkp_admin_styles( $hook ) {
	$fontawesome = false;
	$codemirror  = false;
	$colorpicker = false;

	if ( 'toplevel_page_ATKP_affiliate_toolkit-plugin' == $hook || 'affiliate-toolkit_page_ATKP_affiliate_toolkit-compatibility' == $hook || 'affiliate-toolkit_page_ATKP_affiliate_toolkit-tools' == $hook || 'atkp_product_page_atkp_bulkimport' == $hook || 'affiliate-toolkit_page_ATKP_affiliate_toolkit-shortcodegenerator' == $hook ) {
		wp_register_style( 'atkp-styles', plugins_url( '/css/admin-style.css', __FILE__ ), array( 'wp-admin' ), ATKP_UPDATE_VERSION );
		wp_enqueue_style( 'atkp-styles' );

		if ( $hook == 'toplevel_page_ATKP_affiliate_toolkit-plugin' || 'affiliate-toolkit_page_ATKP_affiliate_toolkit-shortcodegenerator' == $hook ) {
			$fontawesome = true;
		}

		$colorpicker = true;
	} else if ( 'post.php' == $hook || 'post-new.php' == $hook ) {
		wp_register_style( 'atkp-styles', plugins_url( '/dist/style.css', __FILE__ ), array(), ATKP_UPDATE_VERSION );
		wp_enqueue_style( 'atkp-styles' );

		$fontawesome = true;

		global $post_type;

		if ( ATKP_TEMPLATE_POSTTYPE == $post_type ) {
			$codemirror  = true;
			$colorpicker = true;
		}
	}

	if ( $fontawesome ) {
		wp_register_style( 'atkp-fontawesome', plugins_url( '/lib/font-awesome/css/font-awesome.min.css', __FILE__ ), array(), ATKP_UPDATE_VERSION );
		wp_enqueue_style( 'atkp-fontawesome' );
	}

	if ( $codemirror ) {
		// Use WordPress core bundled CodeMirror (available since WP 4.9+)
		// instead of shipping our own copy in lib/codemirror/
		$cm_settings = array(
			'codemirror' => array(
				'mode'              => 'htmlmixed',
				'lineNumbers'       => true,
				'autoCloseTags'     => true,
				'matchTags'         => array( 'bothTags' => true ),
				'extraKeys'         => array( 'Ctrl-J' => 'toMatchingTag' ),
			),
		);
		wp_enqueue_code_editor( $cm_settings );
	}

	if ( $colorpicker ) {
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
	}

	$disable_select2 = ATKPTools::get_setting( ATKP_PLUGIN_PREFIX . '_disableselect2', false );

	if ( ! $disable_select2 ) {
		wp_register_style( 'atkp-select2-styles', plugins_url( '/lib/select2/css/select2atkp.min.css', __FILE__ ), array(), ATKP_UPDATE_VERSION );
		wp_enqueue_style( 'atkp-select2-styles' );

		wp_register_script( 'atkp-select2-scripts', plugins_url( '/lib/select2/js/select2atkp.min.js', __FILE__ ), array(), ATKP_UPDATE_VERSION, true );
		wp_enqueue_script( 'atkp-select2-scripts' );
	}
}

function atkp_frontend_styles() {

	if ( ! atkp_options::$loader->get_disablestyles() ) {
		//register plugin styles
		wp_register_style( 'atkp-styles', plugins_url( '/dist/style.css', __FILE__ ), array(), ATKP_UPDATE_VERSION );
		wp_enqueue_style( 'atkp-styles' );

		//register custom styles
		if ( atkp_options::$loader->get_css_inline() == atkp_css_type::CssFile || atkp_options::$loader->get_css_inline() == atkp_css_type::Inline ) {
			$custom_styleurl = ATKPTools::get_global_style_url();

			if ( $custom_styleurl != null ) {
				wp_register_style( 'atkp-styles-custom', $custom_styleurl, array(), ATKP_UPDATE_VERSION );
				wp_enqueue_style( 'atkp-styles-custom' );
			}

		} else if ( atkp_options::$loader->get_css_inline() == atkp_css_type::InlineHead ) {
			$output     = new atkp_output();
			$custom_css = $output->get_css_output();

			wp_add_inline_style( 'atkp-styles', $custom_css );

			//wp_enqueue_style( 'atkp-styles-custom' );
		}
	}

	//wp_register_script( 'atkp-jquery', plugins_url( 'dist/jquery.min.js', __FILE__ ), array( 'jquery' ), '3.4.1', true );
	//wp_enqueue_script( 'atkp-jquery' );


	if ( ! atkp_options::$loader->get_disablejs() ) {
		wp_register_script( 'atkp-scripts', plugins_url( '/dist/script.js', __FILE__ ), array( 'jquery' ), ATKP_UPDATE_VERSION, true );

		$custom_scripturl = ATKPTools::get_global_script_url();
		//var_dump($custom_scripturl);exit;

		if ( $custom_scripturl == null ) {
			ATKPTools::add_global_scripts( 'atkp-custom-scripts' );
		} else {
			wp_register_script( 'atkp-custom-scripts', $custom_scripturl, array( 'jquery' ), ATKP_UPDATE_VERSION, true );
		}

		wp_enqueue_script( 'atkp-scripts' );
		wp_enqueue_script( 'atkp-custom-scripts' );
	}
}

function atkp_template_list_previewtemplates( $templates ) {
	unset( $templates['ajax_load'] );
	unset( $templates['moreoffers'] );
	unset( $templates['moreoffers2'] );
	unset( $templates['floatingpanel'] );
	unset( $templates['variationboxes'] );
	unset( $templates['popup'] );


	unset( $templates['comparebox'] );
	unset( $templates['compareproduct'] );
	unset( $templates['default_live'] );
	unset( $templates['simple_live'] );
	unset( $templates['searchbox'] );
	unset( $templates['searchform'] );
	unset( $templates['searchtext'] );
	unset( $templates['productcompare_form'] );
	unset( $templates['productcompare_form-widget'] );

	return $templates;
}

add_filter( 'atkp_template_preview_list', 'atkp_template_list_previewtemplates', 10 );

function atkp_template_list_remove_search_templates( $templates, $add_systemdir, $is_searchform ) {
	// Remove search templates from product template list
	if ( ! $is_searchform ) {
		unset( $templates['comparebox'] );
		unset( $templates['compareproduct'] );
		unset( $templates['default_live'] );
		unset( $templates['simple_live'] );
		unset( $templates['searchbox'] );
		unset( $templates['searchform'] );
		unset( $templates['searchtext'] );
		unset( $templates['productcompare_form'] );
		unset( $templates['productcompare_form-widget'] );
	}
	return $templates;
}

add_filter( 'atkp_template_list', 'atkp_template_list_remove_search_templates', 10, 3 );

do_action( 'atkp_initialize_widgets' );


/**
 * @param bool $supported
 * @param atkp_shop $shop
 *
 * @return bool
 */
function atkp_shop_support_articlenumber_search_at( $supported, $shop ) {

	$hasarticlesearch = ATKPTools::has_articlenumbersearch( $shop->webservice );

	return $supported ? $supported : $hasarticlesearch;
}

add_filter( 'atkp_shop_support_articlenumber_search', 'atkp_shop_support_articlenumber_search_at', 10, 2 );


//add_action( 'admin_notices', 'atkp_migration_admin_notice' );
add_action( 'admin_notices', 'atkp_admin_discounts' );
//add_action( 'admin_notices', 'atkp_admin_display_settings' );
add_action( 'admin_notices', 'atkp_admin_license' );
add_action( 'admin_notices', 'atkp_admin_template_sanitize_notice' );
add_action( 'wp_ajax_atkp_dismiss_template_sanitize_notice', 'atkp_dismiss_template_sanitize_notice' );
add_action( 'wp_ajax_atkp_confirm_template_sanitize_notice', 'atkp_confirm_template_sanitize_notice' );

function atkp_admin_template_sanitize_notice() {
	// Only show for admins
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// Don't show if already confirmed
	if ( get_option( 'atkp_template_sanitize_notice_confirmed', false ) ) {
		return;
	}

	$settings_url = admin_url( 'admin.php?page=ATKP_affiliate_toolkit-plugin&tab=advanced_configuration_page' );
	$nonce        = wp_create_nonce( 'atkp_template_sanitize_notice' );

	?>
	<div class="notice atkp-template-sanitize-notice" id="atkp-template-sanitize-notice" style="border: 1px solid #005162; border-left: 4px solid #D8000C; background: #fff; padding: 0; margin: 15px 0;">
		<div style="background: #005162; color: #fff; padding: 12px 18px; font-size: 14px; font-weight: bold;">
			&#9888; <?php echo __( 'affiliate-toolkit: Important Security Notice', 'affiliate-toolkit-starter' ); ?>
		</div>
		<div style="padding: 15px 18px;">
			<p style="font-size: 14px; margin-top: 0;">
				<strong><?php echo sprintf( __( 'Starting with version 3.8.6, raw PHP code (e.g. %s tags) is automatically filtered from templates and will no longer be executed.', 'affiliate-toolkit-starter' ), '<code>&lt;?php ?&gt;</code>' ); ?></strong>
			</p>
			<p style="font-size: 13px;">
				<?php echo __( 'This change was introduced to improve the security of your website. The Blade template engine (BladeOne) uses eval() to render templates. In previous versions, it was possible to inject arbitrary PHP code into templates, which could pose a significant security risk.', 'affiliate-toolkit-starter' ); ?>
			</p>
			<p style="font-size: 13px;">
				<?php echo __( 'Please use only the Blade template syntax (@if, @foreach, {{ }}, etc.) in your templates. Raw PHP code is no longer supported by default.', 'affiliate-toolkit-starter' ); ?>
			</p>
			<div style="background: #FEEFB3; border: 1px solid #9F6000; border-radius: 3px; padding: 10px 14px; margin: 12px 0;">
				<span style="color: #9F6000; font-size: 13px;">
				<?php
				/* translators: %1$s: opening link tag, %2$s: closing link tag */
				echo sprintf(
					__( 'If you absolutely need raw PHP code in your templates and understand the security risks, you can disable this protection in the %1$sAdvanced Settings%2$s.', 'affiliate-toolkit-starter' ),
					'<a href="' . esc_url( $settings_url ) . '" style="color: #005162; font-weight: bold;">',
					'</a>'
				);
				?>
				</span>
			</div>
			<p style="margin-bottom: 5px; margin-top: 15px;">
				<button type="button" id="atkp-confirm-template-sanitize" data-nonce="<?php echo esc_attr( $nonce ); ?>"
				        style="background-color: #005162; border: 1px solid #005162; color: #fff; border-radius: 3px; padding: 10px 16px; line-height: 1.6; font-size: 14px; font-weight: 400; cursor: pointer;">
					<?php echo __( 'I understand, do not show this message again', 'affiliate-toolkit-starter' ); ?>
				</button>
			</p>
		</div>
	</div>
	<script>
	(function() {
		var btn = document.getElementById('atkp-confirm-template-sanitize');
		if (!btn) return;
		btn.addEventListener('click', function() {
			var xhr = new XMLHttpRequest();
			xhr.open('POST', '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', true);
			xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
			xhr.onload = function() {
				var notice = document.getElementById('atkp-template-sanitize-notice');
				if (notice) {
					notice.style.display = 'none';
				}
			};
			xhr.send('action=atkp_confirm_template_sanitize_notice&nonce=' + encodeURIComponent(btn.getAttribute('data-nonce')));
		});
	})();
	</script>
	<?php
}

function atkp_confirm_template_sanitize_notice() {
	check_ajax_referer( 'atkp_template_sanitize_notice', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error();
	}

	update_option( 'atkp_template_sanitize_notice_confirmed', true );
	wp_send_json_success();
}

function atkp_dismiss_template_sanitize_notice() {
	check_ajax_referer( 'atkp_template_sanitize_notice', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error();
	}

	update_option( 'atkp_template_sanitize_notice_confirmed', true );
	wp_send_json_success();
}

function atkp_migration_admin_notice() {
	global $pagenow;

	$done = ATKPTools::get_setting( 'atkp_migration_done', 0 );

	if ( $done ) {
		return;
	}

	$count = wp_count_posts( ATKP_PRODUCT_POSTTYPE );

	if ( ! $count || ( $count->publish == 0 && $count->draft == 0 ) ) {
		ATKPTools::set_setting( 'atkp_migration_done', 1 );

		return;
	}

	/* translators: %s: URL to the migration page */
	ATKPTools::show_notification( '<span style="font-weight:bold">' . esc_html__( 'affiliate-toolkit:', 'affiliate-toolkit-starter' ) . '</span> ' . sprintf( esc_html__( 'You must migrate your products to newest version (v2 to v3). Please %1$sclick here%2$s to migrate now. Please create a backup before you migrate.', 'affiliate-toolkit-starter' ), '<a href="' . esc_url( admin_url( 'admin.php?page=ATKP_affiliate_toolkit-tools&tab=debug_configuration_page' ) ) . '">', '</a>' ), 'notice', 'warning' );


}

function atkp_admin_display_settings() {
	$display_check_done = ATKPTools::get_setting( 'atkp_display_check_done', 0 );

	//if(is_admin()) {
	//	ATKPTools::show_notification( sprintf( __( '<span style="font-weight:bold">affiliate-toolkit:</span> Please uninstall this plugin and install affiliate-toolkit from the <a href="%s" target="_blank">WordPress directory</a>. You will receive further updates via WordPress.org.', ATKP_PLUGIN_PREFIX ), 'https://wordpress.org/plugins/affiliate-toolkit-starter/' ), 'notice', 'warning' );
	//}

	if ( $display_check_done ) {
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! isset( $_GET['tab'] ) || $_GET['tab'] == 'display_configuration_page' ) {
		return;
	}

	/* translators: %1$s: opening link tag, %2$s: closing link tag */
	ATKPTools::show_notification( '<span style="font-weight:bold">' . esc_html__( 'affiliate-toolkit:', 'affiliate-toolkit-starter' ) . '</span> ' . sprintf( esc_html__( 'Please check your display settings. Please %1$sclick here%2$s to go to your display settings page.', 'affiliate-toolkit-starter' ), '<a href="' . esc_url( admin_url( 'admin.php?page=ATKP_affiliate_toolkit-plugin&tab=display_configuration_page' ) ) . '">', '</a>' ), 'notice', 'warning' );
}

function atkp_admin_license() {
	$status = ATKP_LicenseController::get_license_status();

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET['tab'] ) && $_GET['tab'] == 'welcome_page' ) {
		return;
	}

	if ( $status != null ) {
		/* translators: %s: license status message */
		ATKPTools::show_notification( '<span style="font-weight:bold">' . sprintf( esc_html__( 'affiliate-toolkit: %s', 'affiliate-toolkit-starter' ), wp_kses_post( $status ) ) . '</span>', 'notice', 'info' );
	}
}

function atkp_admin_discounts() {
	if ( atkp_options::$loader->get_disablediscounts() ) {
		return;
	}

	if ( ! class_exists( 'ATKP_StoreController' ) ) {
		return;
	}

	$discounts = ATKP_StoreController::get_product_discounts();

	// phpcs:disable WordPress.Security.NonceVerification.Recommended
	if ( ! is_string( $discounts ) && $discounts->active &&
	     ( ( isset( $_GET['page'] ) && ATKPTools::startsWith( sanitize_text_field( wp_unslash( $_GET['page'] ) ), 'ATKP' ) ) || ( isset( $_GET['post_type'] ) && ATKPTools::startsWith( sanitize_text_field( wp_unslash( $_GET['post_type'] ) ), 'affiliate-toolkit-starter' ) ) ) ) {
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
		$aktion_bis = new DateTime( $discounts->aktion_bis );

		/* translators: %1$s: discount teaser text, %2$s: link URL, %3$s: link text, %4$s: end date */
		ATKPTools::show_notification( '<span style="font-weight:bold">' . sprintf( esc_html__( 'affiliate-toolkit - %1$s:', 'affiliate-toolkit-starter' ), esc_html( $discounts->teaser ) ) . '</span> <a target="_blank" href="' . esc_url( $discounts->link_url ) . '">' . esc_html( $discounts->link_text ) . '</a> ' . sprintf( esc_html__( '(Ends on %s)', 'affiliate-toolkit-starter' ), esc_html( $aktion_bis->format( get_option( 'date_format' ) . ' H:i:s' ) ) ), 'notice', 'info' );
	}
}

function atkp_param_starts_with( $param, $prefix ) {
	if ( is_string( $param ) ) {
		return substr( $param, 0, strlen( $prefix ) ) === $prefix;
	}
	if ( is_array( $param ) ) {
		foreach ( $param as $p ) {
			if ( is_string( $p ) && substr( $p, 0, strlen( $prefix ) ) === $prefix ) {
				return true;
			}
		}
	}

	return false;
}

function atkp_is_page() {
	$is_atkp = false;

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET['post_type'] ) && atkp_param_starts_with( sanitize_text_field( wp_unslash( $_GET['post_type'] ) ), 'affiliate-toolkit-starter' ) ) {
		$is_atkp = true;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET['page'] ) && atkp_param_starts_with( sanitize_text_field( wp_unslash( $_GET['page'] ) ), 'ATKP' ) ) {
		$is_atkp = true;
	}

	return $is_atkp;
}

if ( atkp_is_page() ) {
	add_filter( 'admin_footer_text', 'atkp_admin_rateus', 1 );
}

function atkp_admin_rateus() {
	$text = sprintf(
		wp_kses(
			/* translators: %1$s: 5 star symbols linked to wordpress.org review page, %2$s: opening link tag, %3$s: closing link tag */
			_x(
				'Please rate affiliate-toolkit %1$s on %2$sWordPress.org%3$s to help us spread the word. Thank you from the affiliate-toolkit team!',
				'%1$s represents 5 star symbols linked to wordpress.org review page, %2$s and %3$s represent opening and closing link tags',
				'affiliate-toolkit-starter'
			),
			array(
				'a'      => array(
					'href'   => array(),
					'target' => array(),
					'rel'    => array(),
				),
				'strong' => array(),
			)
		),
		'<a href="https://wordpress.org/support/plugin/affiliate-toolkit-starter/reviews/#new-post" target="_blank" rel="noopener noreferrer">' .
		'&#9733;&#9733;&#9733;&#9733;&#9733;</a>',
		'<a href="https://wordpress.org/support/plugin/affiliate-toolkit-starter/reviews/#new-post" target="_blank" rel="noopener">',
		'</a>'
	);

	return $text;
}


// directory handle
$atkp_child_plugin_dir = ATKP_PLUGIN_DIR . '/child-plugins/';
if ( file_exists( $atkp_child_plugin_dir ) ) {
	$atkp_dir = dir( $atkp_child_plugin_dir );
	if ( ! ! $atkp_dir ) {
		while ( false !== ( $atkp_entry = $atkp_dir->read() ) ) {
			if ( $atkp_entry != '.' && $atkp_entry != '..' ) {
				if ( is_dir( $atkp_child_plugin_dir . '/' . $atkp_entry ) ) {
					$atkp_child_file_name = $atkp_child_plugin_dir . '/' . $atkp_entry . '/' . $atkp_entry . '.php';

					if ( is_file( $atkp_child_file_name ) ) {
						require_once( $atkp_child_file_name );
					}

				}
			}
		}
	}
}

define( 'ATKP_INIT', '1' );

?>