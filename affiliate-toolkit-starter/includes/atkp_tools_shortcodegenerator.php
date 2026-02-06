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

		// Enqueue modern generator assets
		wp_enqueue_style( 'atkp-modern-generator', plugins_url( 'css/generator-modern.css', ATKP_PLUGIN_FILE ), array(), ATKP_UPDATE_VERSION );
		wp_enqueue_script( 'atkp-modern-generator', plugins_url( 'js/generator-modern.js', ATKP_PLUGIN_FILE ), array( 'jquery' ), ATKP_UPDATE_VERSION, true );

		wp_localize_script( 'atkp-modern-generator', 'atkpGenerator', array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'atkp-generator-nonce' ),
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