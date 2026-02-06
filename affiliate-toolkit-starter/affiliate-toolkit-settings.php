<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class atkp_settings {
	public static $settings;

	/**
	 * Construct the plugin object
	 */
	public function __construct( $pluginbase ) {
		add_action( 'atkp_register_submenu', array( &$this, 'admin_menu' ), 20, 1 );
	}

	function admin_menu( $parentmenu ) {


		add_submenu_page(
			$parentmenu,
			__( 'Settings', 'affiliate-toolkit-starter' ),
			__( 'Settings', 'affiliate-toolkit-starter' ),
			'manage_options',
			ATKP_PLUGIN_PREFIX . '_affiliate_toolkit-plugin',
			array( &$this, 'toolkit_settings' )
		);

	}


	public function toolkit_settings() {
		if ( ! is_user_logged_in() ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page', 'affiliate-toolkit-starter' ) );
		}

		?>

        <h2 class="nav-tab-wrapper atkp-nav-tab">
			<?php
			$mytab = ATKPTools::get_get_parameter( 'tab', 'string' );

			if ( $mytab == '' ) {
				foreach ( atkp_settings::$settings as $key => $value ) {
					$mytab = $value[1];
					break;
				}
			}

			foreach ( atkp_settings::$settings as $key => $value ) {
				$tab_name = $value[1];
				$class    = ( $mytab == $tab_name ) ? ' nav-tab-active' : '';

				?> <a class="nav-tab<?php echo esc_attr($class) ?>"
                      href="<?php echo esc_url( admin_url() ) ?>admin.php?page=<?php echo esc_html__( ATKP_PLUGIN_PREFIX, 'affiliate-toolkit-starter' ) . '_affiliate_toolkit-plugin' ?>&tab=<?php echo esc_html__( $tab_name, 'affiliate-toolkit-starter' ) ?>"><?php echo esc_html__( $key, 'affiliate-toolkit-starter' ) ?></a><?php
			}
			?>

        </h2>


		<?php

		foreach ( atkp_settings::$settings as $key => $value ) {
			$tab_name = $value[1];

			if ( $mytab == $tab_name ) {

				$plugin = ATKPTools::get_plugin_name_from_callable( $value );

				if ( ! ATKPTools::is_license_active_for_plugin( $plugin ) ) {
					?>
                    <div style="max-width: 800px; margin: 40px auto; padding: 30px; background: #fff; border-left: 4px solid #dc3232; box-shadow: 0 1px 3px rgba(0,0,0,0.13);">
                        <h2 style="margin-top: 0; color: #dc3232; font-size: 24px;">
                            <span class="dashicons dashicons-lock"
                                  style="font-size: 24px; vertical-align: middle; margin-right: 8px;"></span>
							<?php echo esc_html__( 'License Required', 'affiliate-toolkit-starter' ); ?>
                        </h2>
                        <p style="font-size: 16px; line-height: 1.6; color: #444; margin: 20px 0;">
							<?php echo esc_html__( 'No active license found for this extension. Please activate your license to access the settings.', 'affiliate-toolkit-starter' ); ?>
                        </p>
                        <p style="margin-top: 25px;">
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=' . ATKP_PLUGIN_PREFIX . '_affiliate_toolkit-plugin&tab=license_configuration_page' ) ); ?>"
                               class="button button-primary button-large"
                               style="padding: 8px 24px; height: auto; font-size: 16px;">
                                <span class="dashicons dashicons-admin-network"
                                      style="vertical-align: middle; margin-right: 5px;"></span>
								<?php echo esc_html__( 'Manage Licenses', 'affiliate-toolkit-starter' ); ?>
                            </a>
                        </p>
                    </div>

					<?php
					return;
				} else {
					call_user_func( $value );
				}
				break;
			}
		}

		?>




		<?php

	}
}


?>