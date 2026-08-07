<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class atkp_extensions {

	// Amazon plugin ships with the starter - always treat as installed
	private static $bundled_plugin_titles = array(
		'Amazon Affiliate WordPress Plugin',
	);

	/**
	 * Construct the plugin object
	 */
	public function __construct() {
		add_action( 'atkp_register_submenu', array( &$this, 'admin_menu' ), 25, 1 );
	}

	function admin_menu( $parentmenu ) {

		add_submenu_page(
			$parentmenu,
			esc_html__( 'Extensions', 'affiliate-toolkit-starter' ),
			esc_html__( 'Extensions', 'affiliate-toolkit-starter' ),
			'manage_options',
			ATKP_PLUGIN_PREFIX . '_affiliate_toolkit-Extensions',
			array( &$this, 'toolkit_extensions' )
		);

	}

	private static function is_de() {
		$locale = get_locale();
		return ( strpos( $locale, 'de_' ) === 0 || $locale === 'de' );
	}

	private function get_installed_extensions() {
		$installed = array();

		if ( isset( atkp_options::$loader->edd_plugin_data ) && ! empty( atkp_options::$loader->edd_plugin_data ) ) {
			foreach ( atkp_options::$loader->edd_plugin_data as $plugin_slug => $appdata ) {
				$installed[] = array(
					'slug'    => $plugin_slug,
					'item_id' => isset( $appdata['item_id'] ) ? intval( $appdata['item_id'] ) : 0,
					'version' => isset( $appdata['version'] ) ? $appdata['version'] : '',
				);
			}
		}

		return $installed;
	}

	private function find_installed( $product, $installed_extensions ) {
		$product_id = isset( $product->info->id ) ? intval( $product->info->id ) : 0;
		$product_slug = isset( $product->info->slug ) ? $product->info->slug : '';

		foreach ( $installed_extensions as $ext ) {
			// Match by item_id
			if ( $product_id > 0 && $ext['item_id'] === $product_id ) {
				return $ext;
			}
			// Match by slug (plugin filename vs API slug)
			if ( ! empty( $product_slug ) && ! empty( $ext['slug'] ) && strpos( $ext['slug'], $product_slug ) !== false ) {
				return $ext;
			}
		}

		return null;
	}

	private function is_bundled_plugin( $product ) {
		foreach ( self::$bundled_plugin_titles as $title ) {
			if ( isset( $product->info->title ) && $product->info->title === $title ) {
				return true;
			}
		}
		return false;
	}

	public function toolkit_extensions() {
		if ( ! is_user_logged_in() ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page', 'affiliate-toolkit-starter' ) );
		}

		if ( ! class_exists( 'ATKP_StoreController' ) ) {
			return;
		}

		$products = ATKP_StoreController::get_products_feed();

		$pricing_url = self::is_de()
			? 'https://www.affiliate-toolkit.com/de/preise/'
			: 'https://www.affiliate-toolkit.com/pricing/';
		$pricing_url .= '?utm_medium=extension-page&utm_content=Pricing&utm_source=WordPress&utm_campaign=starterpass';

		$free_extensions = array();
		$pro_extensions  = array();

		if ( $products != null && isset( $products->products ) ) {
			foreach ( $products->products as $product ) {
				if ( ATKPTools::str_contains( $product->info->title, 'Pass' ) || $product->info->title == 'affiliate-toolkit' || $product->licensing->enabled != true ) {
					continue;
				}

				$is_free = isset( $product->pricing->amount ) && $product->pricing->amount == '0.00';

				if ( $is_free ) {
					$free_extensions[] = $product;
				} else {
					$pro_extensions[] = $product;
				}
			}
		}

		usort( $free_extensions, function( $a, $b ) { return strcasecmp( $a->info->title, $b->info->title ); } );
		usort( $pro_extensions, function( $a, $b ) { return strcasecmp( $a->info->title, $b->info->title ); } );

		$installed_extensions = $this->get_installed_extensions();

		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php echo esc_html__( 'Extensions', 'affiliate-toolkit-starter' ); ?></h1>
			<hr class="wp-header-end">

			<?php if ( $products == null ) : ?>
				<div class="notice notice-error">
					<p><?php echo esc_html__( 'There was an error retrieving the extensions list from the server. Please try again later.', 'affiliate-toolkit-starter' ); ?></p>
				</div>
			<?php else : ?>

				<?php if ( ! empty( $pro_extensions ) ) : ?>
					<div class="notice notice-info inline" style="margin: 15px 0;">
						<p>
							<?php
							printf(
								/* translators: %1$s: opening link tag, %2$s: closing link tag */
								esc_html__( 'Pro extensions require an active plan. %1$sView plans & pricing%2$s', 'affiliate-toolkit-starter' ),
								'<a href="' . esc_url( $pricing_url ) . '" target="_blank"><strong>',
								'</strong></a>'
							);
							?>
						</p>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $pro_extensions ) ) : ?>
					<h2><?php echo esc_html__( 'Pro Extensions', 'affiliate-toolkit-starter' ); ?></h2>
					<p class="description"><?php
						printf(
							/* translators: %1$s: opening link tag, %2$s: closing link tag */
							esc_html__( 'These extensions are included in a plan. %1$sChoose your plan%2$s to get access.', 'affiliate-toolkit-starter' ),
							'<a href="' . esc_url( $pricing_url ) . '" target="_blank">',
							'</a>'
						);
					?></p>
					<?php $this->render_extensions_table( $pro_extensions, $installed_extensions, false ); ?>
				<?php endif; ?>

				<?php if ( ! empty( $free_extensions ) ) : ?>
					<h2 style="margin-top: 25px;"><?php echo esc_html__( 'Free Extensions', 'affiliate-toolkit-starter' ); ?></h2>
					<?php $this->render_extensions_table( $free_extensions, $installed_extensions, true ); ?>
				<?php endif; ?>

			<?php endif; ?>
		</div>
		<?php
	}

	private function render_extensions_table( $extensions, $installed_extensions, $is_free ) {

		$download_url = self::is_de()
			? 'https://www.affiliate-toolkit.com/de/konto/all-access-pass/'
			: 'https://www.affiliate-toolkit.com/account/all-access-pass/';
		$download_url .= '?utm_medium=extension-page&utm_content=Download&utm_source=WordPress&utm_campaign=starterpass';

		$pricing_url = self::is_de()
			? 'https://www.affiliate-toolkit.com/de/preise/'
			: 'https://www.affiliate-toolkit.com/pricing/';
		$pricing_url .= '?utm_medium=extension-page&utm_content=Pricing&utm_source=WordPress&utm_campaign=starterpass';

		?>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th scope="col" style="width: 20%;"><?php echo esc_html__( 'Extension', 'affiliate-toolkit-starter' ); ?></th>
					<th scope="col"><?php echo esc_html__( 'Description', 'affiliate-toolkit-starter' ); ?></th>
					<th scope="col" style="width: 8%;"><?php echo esc_html__( 'Version', 'affiliate-toolkit-starter' ); ?></th>
					<th scope="col" style="width: 10%;"><?php echo esc_html__( 'Last update', 'affiliate-toolkit-starter' ); ?></th>
					<th scope="col" style="width: 10%;"><?php echo esc_html__( 'Status', 'affiliate-toolkit-starter' ); ?></th>
					<th scope="col" style="width: 12%;"></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $extensions as $product ) :
					$is_bundled    = $this->is_bundled_plugin( $product );
					$match         = $this->find_installed( $product, $installed_extensions );
					$is_installed  = $is_bundled || $match !== null;
					$installed_ver = $match !== null ? $match['version'] : '';
					$has_update    = $is_installed && ! empty( $installed_ver ) && version_compare( $installed_ver, $product->licensing->version, '<' );

					// Bundled plugins: don't show update date
					$last_updated   = $is_bundled ? '' : ( isset( $product->info->modified_date ) ? $product->info->modified_date : '' );
					$update_display = '';
					if ( ! empty( $last_updated ) ) {
						$timestamp = strtotime( $last_updated );
						if ( $timestamp ) {
							/* translators: %s: human-readable time difference */
							$update_display = sprintf( esc_html__( '%s ago', 'affiliate-toolkit-starter' ), human_time_diff( $timestamp, current_time( 'timestamp' ) ) );
						}
					}
				?>
					<tr>
						<td><strong><?php echo esc_html( $product->info->title ); ?></strong></td>
						<td><?php echo esc_html( $product->info->excerpt ); ?></td>
						<td>
							<?php echo esc_html( $product->licensing->version ); ?>
							<?php if ( $has_update ) : ?>
								<br><span style="color: #d63638;">
									<?php
									/* translators: %s: installed version number */
									printf( esc_html__( 'Installed: %s', 'affiliate-toolkit-starter' ), esc_html( $installed_ver ) );
									?>
								</span>
							<?php endif; ?>
						</td>
						<td><?php echo esc_html( $update_display ); ?></td>
						<td>
							<?php if ( $is_installed ) : ?>
								<span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span> <?php echo esc_html__( 'Installed', 'affiliate-toolkit-starter' ); ?>
							<?php else : ?>
								<span class="dashicons dashicons-minus" style="color: #999;"></span> <?php echo esc_html__( 'Not installed', 'affiliate-toolkit-starter' ); ?>
							<?php endif; ?>
						</td>
						<td>
							<?php if ( ! $is_installed ) : ?>
								<?php if ( $is_free ) : ?>
									<a href="<?php echo esc_url( $product->info->permalink . '?utm_medium=extension-page&utm_content=Download&utm_source=WordPress&utm_campaign=starterpass' ); ?>"
									   target="_blank"
									   class="button button-primary"><?php echo esc_html__( 'Download', 'affiliate-toolkit-starter' ); ?></a>
								<?php else : ?>
									<a href="<?php echo esc_url( $pricing_url ); ?>"
									   target="_blank"
									   class="button"><?php echo esc_html__( 'View plans', 'affiliate-toolkit-starter' ); ?></a>
								<?php endif; ?>
							<?php elseif ( $has_update ) : ?>
								<a href="<?php echo esc_url( $download_url ); ?>"
								   target="_blank"
								   class="button"><?php echo esc_html__( 'Update', 'affiliate-toolkit-starter' ); ?></a>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php
	}
}
