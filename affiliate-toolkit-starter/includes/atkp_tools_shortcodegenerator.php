<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class atkp_tools_shortcodegenerator {
	/**
	 * Construct the plugin object
	 */
	public function __construct( $pluginbase ) {
		add_action( 'atkp_register_submenu', array( &$this, 'admin_menu' ), 16, 1 );
		// Hook early to catch iframe mode before admin header is rendered
		add_action( 'admin_init', array( &$this, 'maybe_render_iframe' ) );
	}

	/**
	 * Check if we should render iframe mode and do it before WordPress admin header
	 */
	public function maybe_render_iframe() {
		// Check if this is the shortcode generator page
		if ( isset( $_GET['page'] ) &&
		     $_GET['page'] === ATKP_PLUGIN_PREFIX . '_affiliate_toolkit-shortcodegenerator' ) {

			// Check for our custom iframe parameter
			$is_iframe = isset( $_GET['atkp_iframe'] ) && $_GET['atkp_iframe'] == '1';

			if ( ! $is_iframe ) {
				return;
			}

			if ( ! is_user_logged_in() || ! current_user_can( 'edit_posts' ) ) {
				wp_die( esc_html__( 'You do not have sufficient permissions to access this page', 'affiliate-toolkit-starter' ) );
			}

			// Render clean iframe view and exit
			$this->render_iframe_view();
			exit;
		}
	}

	/**
	 * Render clean iframe view without WordPress admin chrome
	 */
	private function render_iframe_view() {
		// Enqueue WordPress admin styles
		wp_enqueue_style( 'common' );
		wp_enqueue_style( 'forms' );
		wp_enqueue_style( 'buttons' );

		// Enqueue our plugin assets
		wp_enqueue_style( 'atkp-modern-generator', plugins_url( 'css/generator-modern.css', ATKP_PLUGIN_FILE ), array(), ATKP_UPDATE_VERSION );
		wp_enqueue_script( 'atkp-modern-generator', plugins_url( 'js/generator-modern.js', ATKP_PLUGIN_FILE ), array( 'jquery' ), ATKP_UPDATE_VERSION, true );

		wp_localize_script( 'atkp-modern-generator', 'atkpGenerator', array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'atkp-generator-nonce' ),
			'isThickbox' => true,
			'i18n' => array(
				'title' => esc_html__( 'affiliate-toolkit - Shortcode Generator', 'affiliate-toolkit-starter' ),
				'close' => esc_html__( 'Close', 'affiliate-toolkit-starter' ),
				'insert' => esc_html__( 'Insert', 'affiliate-toolkit-starter' ),
				'update' => esc_html__( 'Update Preview', 'affiliate-toolkit-starter' ),
				'copy' => esc_html__( 'Copy to Clipboard', 'affiliate-toolkit-starter' ),
				'loading' => esc_html__( 'Loading...', 'affiliate-toolkit-starter' ),
				'error' => esc_html__( 'An error occurred', 'affiliate-toolkit-starter' ),
			),
		) );

		?>
		<!DOCTYPE html>
		<html <?php language_attributes(); ?>>
		<head>
			<meta charset="<?php bloginfo( 'charset' ); ?>">
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<?php
			// Print WordPress admin styles and scripts
			do_action( 'admin_enqueue_scripts' );
			do_action( 'admin_print_styles' );
			do_action( 'admin_print_scripts' );
			do_action( 'admin_head' );
			?>
			<style>
				body {
					margin: 0;
					padding: 20px;
					background: #fff;
					overflow-x: hidden;
					font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
					font-size: 13px;
					line-height: 1.4em;
					color: #2c3338;
				}
				.atkp-generator-iframe {
					max-width: 100%;
				}
				.atkp-generator-iframe .atkp-modal {
					position: relative;
					display: block !important;
					padding: 0;
				}
				.atkp-generator-iframe .atkp-modal-overlay {
					display: none;
				}
				.atkp-generator-iframe .atkp-modal-content {
					max-width: 100%;
					width: 100%;
					max-height: none;
					margin: 0;
					animation: none;
					box-shadow: none;
				}
				.atkp-generator-iframe .atkp-modal-close {
					display: none;
				}
			</style>
		</head>
		<body class="wp-admin wp-core-ui">
			<div class="atkp-generator-iframe">
				<?php include( ATKP_PLUGIN_DIR . '/templates/shortcode-generator-modal.php' ); ?>
			</div>
			<script>
			jQuery(document).ready(function($) {
				$('#atkp-generator-modal').show();

				// Handle insert button - send shortcode to parent window
				$(document).on('click', '#atkp-btn-insert, #atkp-btn-insert-block', function(e) {
					e.preventDefault();
					var shortcode = $('#atkp-shortcode-output').val();
					if (shortcode && window.parent && window.parent.send_to_editor) {
						window.parent.send_to_editor(shortcode);
						window.parent.tb_remove();
					}
				});
			});
			</script>
			<?php
			do_action( 'admin_print_footer_scripts' );
			?>
		</body>
		</html>
		<?php
	}

	function admin_menu( $parentmenu ) {


		add_submenu_page(
			$parentmenu,
			__( 'Shortcode Generator', 'affiliate-toolkit-starter' ),
			__( 'Shortcode Generator', 'affiliate-toolkit-starter' ),
			'edit_posts',
			ATKP_PLUGIN_PREFIX . '_affiliate_toolkit-shortcodegenerator',
			array( &$this, 'shortcodegenerator_configuration_page' )
		);
	}

	public function shortcodegenerator_configuration_page() {
		if ( ! is_user_logged_in() ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page', 'affiliate-toolkit-starter' ) );
		}

		// Note: Iframe mode is now handled in maybe_render_iframe() via admin_init hook
		// This function only handles normal admin page view

		// Enqueue modern generator assets
		wp_enqueue_style( 'atkp-modern-generator', plugins_url( 'css/generator-modern.css', ATKP_PLUGIN_FILE ), array(), ATKP_UPDATE_VERSION );
		wp_enqueue_script( 'atkp-modern-generator', plugins_url( 'js/generator-modern.js', ATKP_PLUGIN_FILE ), array( 'jquery' ), ATKP_UPDATE_VERSION, true );

		wp_localize_script( 'atkp-modern-generator', 'atkpGenerator', array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'atkp-generator-nonce' ),
			'isThickbox' => false,
			'i18n' => array(
				'title' => esc_html__( 'affiliate-toolkit - Shortcode Generator', 'affiliate-toolkit-starter' ),
				'close' => esc_html__( 'Close', 'affiliate-toolkit-starter' ),
				'insert' => esc_html__( 'Insert', 'affiliate-toolkit-starter' ),
				'update' => esc_html__( 'Update Preview', 'affiliate-toolkit-starter' ),
				'copy' => esc_html__( 'Copy to Clipboard', 'affiliate-toolkit-starter' ),
				'loading' => esc_html__( 'Loading...', 'affiliate-toolkit-starter' ),
				'error' => esc_html__( 'An error occurred', 'affiliate-toolkit-starter' ),
			),
		) );

		// Normal admin page view (not in iframe)
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'Shortcode Generator', 'affiliate-toolkit-starter' ); ?></h1>
			<p><?php echo esc_html__( 'Generate shortcodes for displaying products and lists on your website.', 'affiliate-toolkit-starter' ); ?></p>

			<div class="atkp-generator-page">
				<?php
				// Include the modern generator modal template
				include( ATKP_PLUGIN_DIR . '/templates/shortcode-generator-modal.php' );
				?>
			</div>
		</div>

		<style>
			.atkp-generator-page .atkp-modal {
				position: relative;
				display: block !important;
				padding: 0;
			}

			.atkp-generator-page .atkp-modal-overlay {
				display: none;
			}

			.atkp-generator-page .atkp-modal-content {
				max-width: 100%;
				width: 100%;
				max-height: none;
				margin: 20px 0;
				animation: none;
			}

			.atkp-generator-page .atkp-modal-close {
				display: none;
			}

			.atkp-generator-page #atkp-btn-insert,
			.atkp-generator-page #atkp-btn-insert-block {
				display: none !important;
			}
		</style>

		<script>
		jQuery(document).ready(function($) {
			// Auto-open the modal on page load
			$('#atkp-generator-modal').show();
		});
		</script>
		<?php
	}
}

?>