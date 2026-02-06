<?php
/**
 * Modern Shortcode Generator Modal
 *
 * @since 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div id="atkp-generator-modal" class="atkp-modal">
	<div class="atkp-modal-overlay"></div>
	<div class="atkp-modal-content">
		<div class="atkp-modal-header">
			<h2><?php echo esc_html__( 'Shortcode Generator', 'affiliate-toolkit-starter' ); ?></h2>
			<button class="atkp-modal-close" type="button" title="<?php echo esc_attr__( 'Close (ESC)', 'affiliate-toolkit-starter' ); ?>">
				<span class="dashicons dashicons-no"></span>
			</button>
		</div>

		<div class="atkp-modal-body">
			<!-- Compact Progress Steps -->
			<div class="atkp-steps">
				<div class="atkp-step active" data-step="1">
					<span class="atkp-step-number">1</span>
					<span class="atkp-step-label"><?php echo esc_html__( 'Type', 'affiliate-toolkit-starter' ); ?></span>
				</div>
				<div class="atkp-step" data-step="2">
					<span class="atkp-step-number">2</span>
					<span class="atkp-step-label"><?php echo esc_html__( 'Source', 'affiliate-toolkit-starter' ); ?></span>
				</div>
				<div class="atkp-step" data-step="3">
					<span class="atkp-step-number">3</span>
					<span class="atkp-step-label"><?php echo esc_html__( 'Config', 'affiliate-toolkit-starter' ); ?></span>
				</div>
			</div>

			<!-- Step 1: Output Type -->
			<div class="atkp-step-content" data-step="1">
				<div class="atkp-options-grid atkp-compact">
					<div class="atkp-option-card" data-type="product">
						<div class="atkp-option-icon">
							<span class="dashicons dashicons-products"></span>
						</div>
						<h4><?php echo esc_html__( 'Product Box', 'affiliate-toolkit-starter' ); ?></h4>
						<p><?php echo esc_html__( 'Single product with template', 'affiliate-toolkit-starter' ); ?></p>
					</div>

					<div class="atkp-option-card" data-type="list">
						<div class="atkp-option-icon">
							<span class="dashicons dashicons-list-view"></span>
						</div>
						<h4><?php echo esc_html__( 'Product List', 'affiliate-toolkit-starter' ); ?></h4>
						<p><?php echo esc_html__( 'Multiple products from a list', 'affiliate-toolkit-starter' ); ?></p>
					</div>

					<div class="atkp-option-card" data-type="field">
						<div class="atkp-option-icon">
							<span class="dashicons dashicons-editor-code"></span>
						</div>
						<h4><?php echo esc_html__( 'Single Field', 'affiliate-toolkit-starter' ); ?></h4>
						<p><?php echo esc_html__( 'Specific field (price, title, etc.)', 'affiliate-toolkit-starter' ); ?></p>
					</div>

					<div class="atkp-option-card" data-type="link">
						<div class="atkp-option-icon">
							<span class="dashicons dashicons-admin-links"></span>
						</div>
						<h4><?php echo esc_html__( 'Text Link', 'affiliate-toolkit-starter' ); ?></h4>
						<p><?php echo esc_html__( 'Affiliate link with custom text', 'affiliate-toolkit-starter' ); ?></p>
					</div>

					<?php do_action( 'atkp_generator_output_types' ); ?>
				</div>
			</div>

			<!-- Step 2: Data Source -->
			<div class="atkp-step-content" data-step="2" style="display: none;">
				<div class="atkp-source-type" data-source-for="product">
					<div class="atkp-options-grid atkp-compact">
						<div class="atkp-option-card" data-source="search-product">
							<div class="atkp-option-icon">
								<span class="dashicons dashicons-search"></span>
							</div>
							<h4><?php echo esc_html__( 'Search Product', 'affiliate-toolkit-starter' ); ?></h4>
							<p><?php echo esc_html__( 'Use existing product', 'affiliate-toolkit-starter' ); ?></p>
						</div>

						<div class="atkp-option-card" data-source="create-product">
							<div class="atkp-option-icon">
								<span class="dashicons dashicons-download"></span>
							</div>
							<h4><?php echo esc_html__( 'Import Product', 'affiliate-toolkit-starter' ); ?></h4>
							<p><?php echo esc_html__( 'Import from shop', 'affiliate-toolkit-starter' ); ?></p>
						</div>
					</div>
				</div>

				<div class="atkp-source-type" data-source-for="list">
					<div class="atkp-options-grid atkp-compact">
						<div class="atkp-option-card" data-source="search-list">
							<div class="atkp-option-icon">
								<span class="dashicons dashicons-search"></span>
							</div>
							<h4><?php echo esc_html__( 'Search List', 'affiliate-toolkit-starter' ); ?></h4>
							<p><?php echo esc_html__( 'Use existing list', 'affiliate-toolkit-starter' ); ?></p>
						</div>

						<div class="atkp-option-card" data-source="create-list">
							<div class="atkp-option-icon">
								<span class="dashicons dashicons-plus-alt"></span>
							</div>
							<h4><?php echo esc_html__( 'Create List', 'affiliate-toolkit-starter' ); ?></h4>
							<p><?php echo esc_html__( 'New bestseller/custom list', 'affiliate-toolkit-starter' ); ?></p>
						</div>
					</div>
				</div>

				<!-- Search Product Form -->
				<div class="atkp-search-form" data-search-type="product" style="display: none;">
					<div class="atkp-form-group atkp-inline-search">
						<input type="text" id="atkp-search-product-input" class="atkp-search-input"
							   placeholder="<?php echo esc_attr__( 'Type to search products...', 'affiliate-toolkit-starter' ); ?>"
							   autocomplete="off">
						<button type="button" class="button button-primary" id="atkp-search-product-btn">
							<span class="dashicons dashicons-search"></span>
							<?php echo esc_html__( 'Search', 'affiliate-toolkit-starter' ); ?>
						</button>
					</div>
					<div id="atkp-search-product-results" class="atkp-search-results"></div>
				</div>

				<!-- Search List Form -->
				<div class="atkp-search-form" data-search-type="list" style="display: none;">
					<div class="atkp-form-group atkp-inline-search">
						<input type="text" id="atkp-search-list-input" class="atkp-search-input"
							   placeholder="<?php echo esc_attr__( 'Type to search lists...', 'affiliate-toolkit-starter' ); ?>"
							   autocomplete="off">
						<button type="button" class="button button-primary" id="atkp-search-list-btn">
							<span class="dashicons dashicons-search"></span>
							<?php echo esc_html__( 'Search', 'affiliate-toolkit-starter' ); ?>
						</button>
					</div>
					<div id="atkp-search-list-results" class="atkp-search-results"></div>
				</div>

				<!-- Create Product Form -->
				<div class="atkp-create-form" data-create-type="product" style="display: none;">
					<div class="atkp-form-group">
						<label><?php echo esc_html__( 'Shop:', 'affiliate-toolkit-starter' ); ?></label>
						<select id="atkp-create-product-shop">
							<?php
							$shops = atkp_shop::get_list();
							foreach ( $shops as $shop ) {
								if ( $shop->type == atkp_shop_type::SUB_SHOPS ) {
									echo '<option disabled>' . esc_html( $shop->title ) . '</option>';
									foreach ( $shop->children as $child ) {
										echo '<option value="' . esc_attr( $child->id ) . '">- ' . esc_html( $child->title ) . '</option>';
									}
								} else {
									echo '<option value="' . esc_attr( $shop->id ) . '">' . esc_html( $shop->title ) . '</option>';
								}
							}
							?>
						</select>
					</div>
					<div class="atkp-form-group">
						<label><?php echo esc_html__( 'Search Term:', 'affiliate-toolkit-starter' ); ?></label>
						<div class="atkp-inline-search">
							<input type="text" id="atkp-create-product-keyword" class="atkp-search-input"
								   placeholder="<?php echo esc_attr__( 'Enter product name, ASIN, EAN...', 'affiliate-toolkit-starter' ); ?>">
							<button type="button" class="button button-primary" id="atkp-create-product-search">
								<span class="dashicons dashicons-search"></span>
								<?php echo esc_html__( 'Search', 'affiliate-toolkit-starter' ); ?>
							</button>
						</div>
					</div>
					<div id="atkp-create-product-results" class="atkp-create-results"></div>
				</div>

				<!-- Create List Form -->
				<div class="atkp-create-form" data-create-type="list" style="display: none;">
					<div class="atkp-form-group">
						<label><?php echo esc_html__( 'List Name:', 'affiliate-toolkit-starter' ); ?></label>
						<input type="text" id="atkp-create-list-name"
							   placeholder="<?php echo esc_attr__( 'Enter list name...', 'affiliate-toolkit-starter' ); ?>">
					</div>
					<div class="atkp-form-group">
						<label><?php echo esc_html__( 'Shop:', 'affiliate-toolkit-starter' ); ?></label>
						<select id="atkp-create-list-shop">
							<option value=""><?php echo esc_html__( 'No shop', 'affiliate-toolkit-starter' ); ?></option>
							<?php
							foreach ( $shops as $shop ) {
								$sources = $shop->provider ? $shop->provider->get_supportedlistsources() : '';
								if ( empty( $sources ) ) continue;

								if ( $shop->type == atkp_shop_type::SUB_SHOPS ) {
									echo '<option disabled>' . esc_html( $shop->title ) . '</option>';
									foreach ( $shop->children as $child ) {
										echo '<option value="' . esc_attr( $child->id ) . '" data-sources="' . esc_attr( $sources ) . '">- ' . esc_html( $child->title ) . '</option>';
									}
								} else {
									echo '<option value="' . esc_attr( $shop->id ) . '" data-sources="' . esc_attr( $sources ) . '">' . esc_html( $shop->title ) . '</option>';
								}
							}
							?>
						</select>
					</div>
					<div class="atkp-form-group">
						<label><?php echo esc_html__( 'List Type:', 'affiliate-toolkit-starter' ); ?></label>
						<select id="atkp-create-list-type">
							<option value="10"><?php echo esc_html__( 'Category - Best Seller', 'affiliate-toolkit-starter' ); ?></option>
							<option value="11"><?php echo esc_html__( 'Category - New Releases', 'affiliate-toolkit-starter' ); ?></option>
							<option value="20"><?php echo esc_html__( 'Search', 'affiliate-toolkit-starter' ); ?></option>
						</select>
					</div>
					<div class="atkp-form-group">
						<label id="atkp-create-list-keyword-label"><?php echo esc_html__( 'Keyword:', 'affiliate-toolkit-starter' ); ?></label>
						<input type="text" id="atkp-create-list-keyword">
					</div>
					<button type="button" class="button button-primary" id="atkp-create-list-btn">
						<?php echo esc_html__( 'Create List', 'affiliate-toolkit-starter' ); ?>
					</button>
				</div>
			</div>

			<!-- Step 3: Configuration -->
			<div class="atkp-step-content" data-step="3" style="display: none;">
				<div class="atkp-selected-item">
					<span class="dashicons dashicons-yes-alt"></span>
					<strong><?php echo esc_html__( 'Selected:', 'affiliate-toolkit-starter' ); ?></strong>
					<span id="atkp-selected-item-display"></span>
				</div>

				<div class="atkp-config-tabs">
					<button class="atkp-tab-btn active" data-tab="template">
						<?php echo esc_html__( 'Template', 'affiliate-toolkit-starter' ); ?>
					</button>
					<button class="atkp-tab-btn" data-tab="field">
						<?php echo esc_html__( 'Field', 'affiliate-toolkit-starter' ); ?>
					</button>
					<button class="atkp-tab-btn" data-tab="link">
						<?php echo esc_html__( 'Link', 'affiliate-toolkit-starter' ); ?>
					</button>
					<?php do_action( 'atkp_generator_config_tabs' ); ?>
					<button class="atkp-tab-btn" data-tab="advanced">
						<?php echo esc_html__( 'Advanced', 'affiliate-toolkit-starter' ); ?>
					</button>
				</div>

				<!-- Template Tab -->
				<div class="atkp-tab-content active" data-tab="template">
					<div class="atkp-form-group">
						<label><?php echo esc_html__( 'Template:', 'affiliate-toolkit-starter' ); ?></label>
						<select id="atkp-config-template">
							<option value=""><?php echo esc_html__( 'Default', 'affiliate-toolkit-starter' ); ?></option>
							<?php
							$templates = atkp_template::get_list( true, false );
							foreach ( $templates as $template_id => $template_name ) {
								echo '<option value="' . esc_attr( $template_id ) . '">' . esc_html( $template_name ) . '</option>';
							}
							?>
						</select>
					</div>

					<div class="atkp-form-group">
						<label><?php echo esc_html__( 'Button Type:', 'affiliate-toolkit-starter' ); ?></label>
						<select id="atkp-config-buttontype">
							<option value=""><?php echo esc_html__( 'Default', 'affiliate-toolkit-starter' ); ?></option>
							<option value="addtocart"><?php echo esc_html__( 'Add to Cart', 'affiliate-toolkit-starter' ); ?></option>
							<option value="link"><?php echo esc_html__( 'Link', 'affiliate-toolkit-starter' ); ?></option>
							<option value="product"><?php echo esc_html__( 'Product Page', 'affiliate-toolkit-starter' ); ?></option>
						</select>
					</div>

					<div class="atkp-form-group">
						<label><?php echo esc_html__( 'Alignment:', 'affiliate-toolkit-starter' ); ?></label>
						<select id="atkp-config-align">
							<option value=""><?php echo esc_html__( 'No alignment', 'affiliate-toolkit-starter' ); ?></option>
							<option value="atkp-left atkp-clearfix"><?php echo esc_html__( 'Left', 'affiliate-toolkit-starter' ); ?></option>
							<option value="atkp-center"><?php echo esc_html__( 'Center', 'affiliate-toolkit-starter' ); ?></option>
							<option value="atkp-right atkp-clearfix"><?php echo esc_html__( 'Right', 'affiliate-toolkit-starter' ); ?></option>
						</select>
					</div>

					<div class="atkp-form-group atkp-list-only">
						<label><?php echo esc_html__( 'Limit:', 'affiliate-toolkit-starter' ); ?></label>
						<input type="number" id="atkp-config-limit" min="1"
							   placeholder="<?php echo esc_attr__( 'Number of products to display', 'affiliate-toolkit-starter' ); ?>">
					</div>

					<div class="atkp-form-group atkp-list-only">
						<label>
							<input type="checkbox" id="atkp-config-random">
							<?php echo esc_html__( 'Random Sort', 'affiliate-toolkit-starter' ); ?>
						</label>
					</div>

					<div class="atkp-form-group">
						<label>
							<input type="checkbox" id="atkp-config-hidedisclaimer">
							<?php echo esc_html__( 'Hide Disclaimer', 'affiliate-toolkit-starter' ); ?>
						</label>
					</div>

					<div class="atkp-form-group">
						<label><?php echo esc_html__( 'Custom Content:', 'affiliate-toolkit-starter' ); ?></label>
						<input type="text" id="atkp-config-content"
							   placeholder="<?php echo esc_attr__( 'Optional custom content...', 'affiliate-toolkit-starter' ); ?>">
					</div>
				</div>

				<!-- Field Tab -->
				<div class="atkp-tab-content" data-tab="field">
					<div class="atkp-form-group">
						<label><?php echo esc_html__( 'Field:', 'affiliate-toolkit-starter' ); ?></label>
						<select id="atkp-config-field">
							<?php
							$templatehelper = new atkp_template_helper();
							$placeholders = $templatehelper->getPlaceholders();
							foreach ( $placeholders as $placeholder => $caption ) {
								echo '<option value="' . esc_attr( $placeholder ) . '">' . esc_html( $caption ) . '</option>';
							}
							?>
						</select>
					</div>

					<div class="atkp-form-group">
						<label>
							<input type="checkbox" id="atkp-config-fieldlink">
							<?php echo esc_html__( 'Add Hyperlink', 'affiliate-toolkit-starter' ); ?>
						</label>
					</div>
				</div>

				<!-- Link Tab -->
				<div class="atkp-tab-content" data-tab="link">
					<div class="atkp-form-group">
						<label><?php echo esc_html__( 'Link Text:', 'affiliate-toolkit-starter' ); ?></label>
						<input type="text" id="atkp-config-linktext"
							   placeholder="<?php echo esc_attr__( 'Enter link text...', 'affiliate-toolkit-starter' ); ?>">
					</div>
				</div>

				<!-- Advanced Tab -->
				<div class="atkp-tab-content" data-tab="advanced">
					<div class="atkp-form-group">
						<label><?php echo esc_html__( 'Container CSS Class:', 'affiliate-toolkit-starter' ); ?></label>
						<input type="text" id="atkp-config-containercss"
							   placeholder="<?php echo esc_attr__( 'Custom CSS classes for container...', 'affiliate-toolkit-starter' ); ?>">
					</div>

					<div class="atkp-form-group">
						<label><?php echo esc_html__( 'Element CSS Class:', 'affiliate-toolkit-starter' ); ?></label>
						<input type="text" id="atkp-config-elementcss"
							   placeholder="<?php echo esc_attr__( 'Custom CSS classes for elements...', 'affiliate-toolkit-starter' ); ?>">
					</div>

					<div class="atkp-form-group">
						<label><?php echo esc_html__( 'Override Affiliate ID:', 'affiliate-toolkit-starter' ); ?></label>
						<input type="text" id="atkp-config-trackingid"
							   placeholder="<?php echo esc_attr__( 'Amazon or eBay affiliate ID...', 'affiliate-toolkit-starter' ); ?>">
					</div>
				</div>

				<?php do_action( 'atkp_generator_config_tab_contents' ); ?>

				<!-- Shortcode Preview -->
				<div class="atkp-shortcode-preview">
					<label><?php echo esc_html__( 'Generated Shortcode:', 'affiliate-toolkit-starter' ); ?></label>
					<textarea id="atkp-shortcode-output" readonly></textarea>
					<button type="button" class="button" id="atkp-copy-shortcode">
						<span class="dashicons dashicons-clipboard"></span>
						<?php echo esc_html__( 'Copy to Clipboard', 'affiliate-toolkit-starter' ); ?>
					</button>
				</div>
			</div>
		</div>

		<div class="atkp-modal-footer">
			<button type="button" class="button" id="atkp-btn-back" style="display: none;">
				<?php echo esc_html__( 'Back', 'affiliate-toolkit-starter' ); ?>
			</button>
			<button type="button" class="button button-primary" id="atkp-btn-next">
				<?php echo esc_html__( 'Next', 'affiliate-toolkit-starter' ); ?>
			</button>
			<button type="button" class="button button-primary" id="atkp-btn-insert" style="display: none;">
				<?php echo esc_html__( 'Insert Shortcode', 'affiliate-toolkit-starter' ); ?>
			</button>
			<button type="button" class="button button-primary" id="atkp-btn-insert-block" style="display: none;">
				<?php echo esc_html__( 'Insert as Gutenberg Block', 'affiliate-toolkit-starter' ); ?>
			</button>
		</div>
	</div>
</div>

