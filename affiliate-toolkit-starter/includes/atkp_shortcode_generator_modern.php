<?php
/**
 * Modern Shortcode Generator for Affiliate Toolkit
 * Combines and modernizes atkp_shortcode_generator and atkp_shortcode_generator2
 * Adds Gutenberg block support
 *
 * @since 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly


class atkp_shortcode_generator_modern {

	private $pluginbase;
	private static $instance = null;

	/**
	 * Get singleton instance
	 */
	public static function get_instance( $pluginbase = null ) {
		if ( null === self::$instance ) {
			if ( $pluginbase !== null ) {
				self::$instance = new self( $pluginbase );
			}
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	public function __construct( $pluginbase ) {
		$this->pluginbase = $pluginbase;

		// Meta boxes for posts
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_meta_boxes' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_metabox_assets' ) );

		// Editor buttons - NO MODAL ANYMORE, only on dedicated shortcode generator page
		add_action( 'media_buttons', array( $this, 'add_media_button' ) );
		// Removed: add_action( 'admin_footer', array( $this, 'render_popup' ) );

		// TinyMCE button
		add_action( 'admin_head', array( $this, 'add_tinymce_button' ) );

		// Gutenberg block support
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_gutenberg_assets' ) );
		add_action( 'init', array( $this, 'register_gutenberg_blocks' ), 20 ); // Higher priority to ensure registration
		add_filter( 'block_categories_all', array( $this, 'register_block_category' ), 10, 2 );

		// AJAX handlers for Gutenberg blocks
		add_action( 'wp_ajax_atkp_block_search_products', array( $this, 'ajax_search_products' ) );
		add_action( 'wp_ajax_atkp_block_search_external', array( $this, 'ajax_search_external_products' ) );
		// Note: atkp_import_product and atkp_create_list are handled by atkp_endpoints.php
		add_action( 'wp_ajax_atkp_search_posts', array( $this, 'ajax_search_posts' ) );
	}

	/**
	 * Add meta boxes to posts and pages
	 */
	public function add_meta_boxes() {
		$post_types = array( 'post', 'page' );
		$custom_types = get_option( ATKP_PLUGIN_PREFIX . '_custom_posttypes', array() );

		if ( is_array( $custom_types ) ) {
			$post_types = array_merge( $post_types, $custom_types );
		}

		$post_types = apply_filters( 'atkp_mainproduct_posttypes', $post_types );

		foreach ( $post_types as $type ) {
		add_meta_box(
			ATKP_PLUGIN_PREFIX . '_product_box',
			esc_html__( 'affiliate-toolkit', 'affiliate-toolkit-starter' ),
			array( $this, 'render_meta_box' ),
			$type,
			'normal',
			'default'
		);
		}
	}

	/**
	 * Render meta box content
	 */
	public function render_meta_box( $post ) {
		wp_nonce_field( plugin_basename( __FILE__ ), 'atkp_meta_box_nonce' );

		$product_id = ATKPTools::get_post_setting( $post->ID, ATKP_PLUGIN_PREFIX . '_product' );
		$list_id = ATKPTools::get_post_setting( $post->ID, ATKP_PLUGIN_PREFIX . '_list' );

		?>
		<div class="atkp-modern-metabox">
			<table class="form-table">
				<tr>
					<th scope="row">
						<label for="atkp_main_product">
							<?php echo esc_html__( 'Main Product:', 'affiliate-toolkit-starter' ); ?>
						</label>
					</th>
					<td>
						<select id="atkp_main_product"
								name="<?php echo esc_attr( ATKP_PLUGIN_PREFIX . '_product' ); ?>"
								class="widefat atkp-product-select"
								data-posttype="<?php echo esc_attr( ATKP_PRODUCT_POSTTYPE ); ?>"
								style="width: 100%;">
							<?php
							if ( atkp_options::$loader->get_disableselect2_backend() ) {
								echo '<option value="">' . esc_html__( 'None', 'affiliate-toolkit-starter' ) . '</option>';

								$products = get_posts( array(
									'post_type' => ATKP_PRODUCT_POSTTYPE,
									'numberposts' => 500,
									'post_status' => array( 'publish', 'draft' )
								) );

								foreach ( $products as $product ) {
									$selected = ( $product_id == $product->ID ) ? 'selected' : '';
									echo '<option value="' . esc_attr( $product->ID ) . '" ' . $selected . '>'
										. esc_html( $product->post_title ) . ' (' . esc_html( $product->ID ) . ')</option>';
								}
							} else {
								if ( $product_id ) {
									$product = get_post( $product_id );
									if ( $product ) {
										echo '<option value="' . esc_attr( $product->ID ) . '" selected>'
											. esc_html( $product->post_title ) . ' (' . esc_html( $product->ID ) . ')</option>';
									}
								} else {
									echo '<option value="">' . esc_html__( 'None', 'affiliate-toolkit-starter' ) . '</option>';
								}
							}
							?>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="atkp_main_list">
							<?php echo esc_html__( 'Main List:', 'affiliate-toolkit-starter' ); ?>
						</label>
					</th>
					<td>
						<select id="atkp_main_list"
								name="<?php echo esc_attr( ATKP_PLUGIN_PREFIX . '_list' ); ?>"
								class="widefat atkp-product-select"
								data-posttype="<?php echo esc_attr( ATKP_LIST_POSTTYPE ); ?>"
								style="width: 100%;">
							<?php
							if ( atkp_options::$loader->get_disableselect2_backend() ) {
								echo '<option value="">' . esc_html__( 'None', 'affiliate-toolkit-starter' ) . '</option>';

								$lists = get_posts( array(
									'post_type' => ATKP_LIST_POSTTYPE,
									'numberposts' => 500,
									'post_status' => array( 'publish', 'draft' )
								) );

								foreach ( $lists as $list ) {
									$selected = ( $list_id == $list->ID ) ? 'selected' : '';
									echo '<option value="' . esc_attr( $list->ID ) . '" ' . $selected . '>'
										. esc_html( $list->post_title ) . ' (' . esc_html( $list->ID ) . ')</option>';
								}
							} else {
								if ( $list_id ) {
									$list = get_post( $list_id );
									if ( $list ) {
										echo '<option value="' . esc_attr( $list->ID ) . '" selected>'
											. esc_html( $list->post_title ) . ' (' . esc_html( $list->ID ) . ')</option>';
									}
								} else {
									echo '<option value="">' . esc_html__( 'None', 'affiliate-toolkit-starter' ) . '</option>';
								}
							}
							?>
						</select>
					</td>
				</tr>
			</table>
		</div>
		<?php
	}

	/**
	 * Enqueue assets for metaboxes
	 */
	public function enqueue_metabox_assets( $hook ) {
		global $post_type;

		// Only load on post edit screens
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ) ) ) {
			return;
		}

		$post_types = array( 'post', 'page' );
		$custom_types = get_option( ATKP_PLUGIN_PREFIX . '_custom_posttypes', array() );
		if ( is_array( $custom_types ) ) {
			$post_types = array_merge( $post_types, $custom_types );
		}
		$post_types = apply_filters( 'atkp_mainproduct_posttypes', $post_types );

		if ( ! in_array( $post_type, $post_types ) ) {
			return;
		}

		// Check if Select2 is disabled
		if ( atkp_options::$loader->get_disableselect2_backend() ) {
			return;
		}

		// Enqueue Select2 - use registered scripts
		wp_enqueue_style( 'atkp-select2-styles' );
		wp_enqueue_script( 'atkp-select2-scripts' );

		// Enqueue custom metabox script
		wp_enqueue_script(
			'atkp-metabox-select2',
			plugins_url( 'js/metabox-select2.js', ATKP_PLUGIN_FILE ),
			array( 'jquery', 'atkp-select2-scripts' ),
			ATKP_UPDATE_VERSION,
			true
		);

		// Localize script with data
		wp_localize_script( 'atkp-metabox-select2', 'atkpMetabox', array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'atkp-search-nonce' ),
			'searchPlaceholder' => __( 'Type to search...', 'affiliate-toolkit-starter' )
		) );

		// Add custom CSS for Select2 in metaboxes
		wp_add_inline_style( 'atkp-select2-styles', "
			.atkp-modern-metabox .select2-container {
				width: 100% !important;
				display: block;
			}
			.atkp-modern-metabox .select2-container .select2-selection--single {
				height: auto;
				min-height: 32px;
				padding: 4px 8px;
				border: 1px solid #8c8f94;
				border-radius: 4px;
				background: #fff;
			}
			.atkp-modern-metabox .select2-container--default .select2-selection--single .select2-selection__rendered {
				line-height: 24px;
				padding-left: 0;
				padding-right: 20px;
			}
			.atkp-modern-metabox .select2-container--default .select2-selection--single .select2-selection__arrow {
				height: 30px;
				right: 4px;
			}
			.atkp-modern-metabox .select2-container--default .select2-selection--single .select2-selection__placeholder {
				color: #646970;
			}
			.atkp-modern-metabox .select2-container--default.select2-container--focus .select2-selection--single {
				border-color: #2271b1;
				box-shadow: 0 0 0 1px #2271b1;
			}
			/* Hide duplicate search containers */
			.atkp-modern-metabox .select2-container + .select2-container {
				display: none !important;
			}
			.select2-container--default .select2-results__option--highlighted[aria-selected] {
				background-color: #2271b1;
			}
			.select2-dropdown {
				border: 1px solid #8c8f94;
				border-radius: 4px;
			}
			.select2-search--dropdown {
				padding: 8px;
			}
			.select2-search--dropdown .select2-search__field {
				border: 1px solid #8c8f94;
				border-radius: 4px;
				padding: 6px 8px;
				width: 100%;
			}
			.select2-search--dropdown .select2-search__field:focus {
				border-color: #2271b1;
				outline: none;
				box-shadow: 0 0 0 1px #2271b1;
			}
		" );
	}

	/**
	 * Save meta box data
	 */
	public function save_meta_boxes( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		$nonce = ATKPTools::get_post_parameter( 'atkp_meta_box_nonce', 'string' );
		if ( ! wp_verify_nonce( $nonce, plugin_basename( __FILE__ ) ) ) {
			return;
		}

		$post_types = array( 'post', 'page' );
		$custom_types = get_option( ATKP_PLUGIN_PREFIX . '_custom_posttypes', array() );
		if ( is_array( $custom_types ) ) {
			$post_types = array_merge( $post_types, $custom_types );
		}
		$post_types = apply_filters( 'atkp_mainproduct_posttypes', $post_types );

		// Save product
		$this->save_main_reference( $post_id, '_product', ATKP_PRODUCT_POSTTYPE, $post_types );

		// Save list
		$this->save_main_reference( $post_id, '_list', ATKP_LIST_POSTTYPE, $post_types );
	}

	/**
	 * Save main product/list reference
	 */
	private function save_main_reference( $post_id, $field, $posttype, $posttypes ) {
		$new_id = ATKPTools::get_post_parameter( ATKP_PLUGIN_PREFIX . $field, 'int' );
		$old_id = ATKPTools::get_post_setting( $post_id, ATKP_PLUGIN_PREFIX . $field );

		$item = get_post( $new_id );

		if ( ! $item ) {
			ATKPTools::set_post_setting( $post_id, ATKP_PLUGIN_PREFIX . $field, null );
		} else {
			ATKPTools::set_post_setting( $post_id, ATKP_PLUGIN_PREFIX . $field, $new_id );
		}

		// Update references
		if ( $old_id != $new_id ) {
			$changed_ids = array();

			if ( $old_id ) {
				$changed_ids[] = $old_id;
			}
			if ( $new_id ) {
				$changed_ids[] = $new_id;
			}

			foreach ( $changed_ids as $item_id ) {
				$this->update_post_references( $item_id, $field, $posttype, $posttypes );
			}
		}
	}

	/**
	 * Update post references for a product/list
	 */
	private function update_post_references( $item_id, $field, $posttype, $posttypes ) {
		$post_ids = array();

		$query = new WP_Query( array(
			'post_type' => $posttypes,
			'post_status' => array( 'publish', 'draft' ),
			'posts_per_page' => -1,
			'meta_query' => array(
				array(
					'key' => ATKP_PLUGIN_PREFIX . $field,
					'value' => $item_id,
					'compare' => '=',
				),
			),
		) );

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$post_ids[] = get_the_ID();
			}
			wp_reset_postdata();
		}

		ATKPTools::set_post_setting( $item_id, $posttype . '_postid', $post_ids );
	}

	/**
	 * Add TinyMCE button
	 */
	public function add_tinymce_button() {
		global $typenow;

		$page = ATKPTools::get_get_parameter( 'page', 'string' );
		$allowed_pages = array( 'ATKP_affiliate_toolkit-plugin' );

		$post_types = array( 'post', 'page' );
		$custom_types = get_option( ATKP_PLUGIN_PREFIX . '_custom_posttypes', array() );
		if ( is_array( $custom_types ) ) {
			$post_types = array_merge( $post_types, $custom_types );
		}
		$post_types[] = ATKP_PRODUCT_POSTTYPE;

		if ( ! in_array( $typenow, $post_types ) && ! in_array( $page, $allowed_pages ) ) {
			return;
		}

		add_filter( 'mce_external_plugins', array( $this, 'add_tinymce_plugin' ) );
		add_filter( 'mce_buttons', array( $this, 'register_tinymce_button' ) );
	}

	public function add_tinymce_plugin( $plugin_array ) {
		$plugin_array['atkp_button'] = plugins_url( '/js/editor-button-modern.js', ATKP_PLUGIN_FILE );
		return $plugin_array;
	}

	public function register_tinymce_button( $buttons ) {
		array_push( $buttons, 'separator', 'atkp_button' );
		return $buttons;
	}

	/**
	 * Add media button to post editor
	 * Button opens the dedicated shortcode generator page in a new tab
	 */
	public function add_media_button( $args = array() ) {
		global $typenow;

		// Check if we should show the button on post/page edit screens
		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}

		$post_types = array( 'post', 'page' );
		$custom_types = get_option( ATKP_PLUGIN_PREFIX . '_custom_posttypes', array() );
		if ( is_array( $custom_types ) ) {
			$post_types = array_merge( $post_types, $custom_types );
		}
		$post_types = apply_filters( 'atkp_mainproduct_posttypes', $post_types );

		$is_editor_screen = in_array( $typenow, $post_types );

		if ( ! $is_editor_screen ) {
			return;
		}

		// Add the button that links to the dedicated generator page
		$generator_url = admin_url( 'admin.php?page=ATKP_affiliate_toolkit-shortcodegenerator' );
		$icon_url = plugins_url( '/images/affiliate_toolkit_menu.png', ATKP_PLUGIN_FILE );
		$button_text = __( 'AT Shortcode', 'affiliate-toolkit-starter' );

		echo '<a href="' . esc_url( $generator_url ) . '" target="_blank" class="button atkp-generator-link" title="' . esc_attr( $button_text ) . '" style="padding-left: .4em;">';
		echo '<span class="wp-media-buttons-icon" style="background: url(' . esc_url( $icon_url ) . '); background-repeat: no-repeat; background-position: left center; background-size: 18px 18px; margin-right: .2em;"></span>';
		echo esc_html( $button_text );
		echo '</a>';
	}

	/**
	 * Render popup modal
	 * REMOVED: Modal is now only shown on dedicated shortcode generator page
	 * This function is kept for backward compatibility but does nothing
	 */
	public function render_popup() {
		// Intentionally empty - modal removed from post/page edit screens
		// Modal is now only available on the dedicated shortcode generator page
	}

	/**
	 * Register block category for Gutenberg
	 */
	public function register_block_category( $categories, $editor_context ) {
		return array_merge(
			$categories,
			array(
				array(
					'slug'  => 'affiliate-toolkit',
					'title' => __( 'affiliate-toolkit', 'affiliate-toolkit-starter' ),
					'icon'  => null,
				),
			)
		);
	}

	/**
	 * Register Gutenberg blocks
	 */
	public function register_gutenberg_blocks() {
		if ( ! function_exists( 'register_block_type' ) ) {
			ATKPLog::LogError( 'ATKP: register_block_type function not available!' );
			return;
		}

		ATKPLog::LogDebug( '========== ATKP: Registering blocks... ==========' );

		// Register block scripts (they will be enqueued automatically by WordPress)
		// Force cache bust with new version
		$version = ATKP_UPDATE_VERSION . '.blocks.v2';

		wp_register_script(
			'atkp-block-product',
			plugins_url( 'js/blocks/product-block.js', ATKP_PLUGIN_FILE ),
			array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n', 'jquery' ),
			$version,
			false
		);

		wp_register_script(
			'atkp-block-list',
			plugins_url( 'js/blocks/list-block.js', ATKP_PLUGIN_FILE ),
			array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n', 'jquery' ),
			$version,
			false
		);

		// Prepare localized data for blocks
		$templates = atkp_template::get_list( true, false );
		$template_options = array();
		$template_options[] = array(
			'label' => __( 'Default', 'affiliate-toolkit-starter' ),
			'value' => ''
		);

		foreach ( $templates as $template_id => $template_name ) {
			$template_options[] = array(
				'label' => $template_name,
				'value' => (string) $template_id
			);
		}

		$shops = atkp_shop::get_list();
		$shop_options = array();
		if ( $shops ) {
			foreach ( $shops as $shop ) {
				$shop_options[] = array(
					'label' => $shop->title,
					'value' => (string) $shop->id,
				);

				// Add children shops with indentation
				if ( isset( $shop->children ) && is_array( $shop->children ) && count( $shop->children ) > 0 ) {
					foreach ( $shop->children as $child ) {
						$shop_options[] = array(
							'label' => '  ↳ ' . $child->title,
							'value' => (string) $child->id,
						);
					}
				}
			}
		}

		$localize_data = array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'atkp-import-nonce' ),  // For atkp_endpoints.php functions
			'restUrl' => rest_url(),
			'restNonce' => wp_create_nonce( 'wp_rest' ),
			'templates' => $template_options,
			'shops' => $shop_options,
			'pluginUrl' => plugins_url( '', ATKP_PLUGIN_FILE ),
			// Translations - all strings used in JavaScript
			'i18n' => array(
				// Block Titles
				'affiliateToolkitProduct' => __( 'affiliate-toolkit Product', 'affiliate-toolkit-starter' ),
				'affiliateToolkitProductList' => __( 'affiliate-toolkit Product List', 'affiliate-toolkit-starter' ),

				// Product Block
				'productSelection' => __( 'Product Selection', 'affiliate-toolkit-starter' ),
				'selectedProduct' => __( 'Selected Product:', 'affiliate-toolkit-starter' ),
				'changeProduct' => __( 'Change Product', 'affiliate-toolkit-starter' ),
				'select' => __( 'Select', 'affiliate-toolkit-starter' ),
				'import' => __( 'Import', 'affiliate-toolkit-starter' ),
				'searchExistingProducts' => __( 'Search existing products:', 'affiliate-toolkit-starter' ),
				'enterProductTitleOrId' => __( 'Enter product title or ID...', 'affiliate-toolkit-starter' ),
				'search' => __( 'Search', 'affiliate-toolkit-starter' ),
				'showing5NewestProducts' => __( 'Showing 5 newest products', 'affiliate-toolkit-starter' ),
				'importProductsFromShop' => __( 'Import products from shop:', 'affiliate-toolkit-starter' ),
				'selectShop' => __( 'Select Shop', 'affiliate-toolkit-starter' ),
				'selectShopPlaceholder' => __( '-- Select Shop --', 'affiliate-toolkit-starter' ),
				'searchKeyword' => __( 'Search Keyword', 'affiliate-toolkit-starter' ),
				'exampleIphone' => __( 'e.g., "iPhone 15"', 'affiliate-toolkit-starter' ),
				'keywordToSearch' => __( 'Keyword to search for products', 'affiliate-toolkit-starter' ),
				'loadingPreview' => __( 'Loading Preview...', 'affiliate-toolkit-starter' ),
				'previewResults' => __( 'Preview Results', 'affiliate-toolkit-starter' ),
				'importAndSelect' => __( 'Import & Select', 'affiliate-toolkit-starter' ),
				'noProductsFound' => __( 'No products found. Try a different search term.', 'affiliate-toolkit-starter' ),
				'orEnterProductId' => __( 'Or enter Product ID directly below:', 'affiliate-toolkit-starter' ),
				'productIdManual' => __( 'Product ID (Manual)', 'affiliate-toolkit-starter' ),
				'enterProductIdIfKnown' => __( 'Enter product ID if you know it', 'affiliate-toolkit-starter' ),
				'displaySettings' => __( 'Display Settings', 'affiliate-toolkit-starter' ),
				'template' => __( 'Template', 'affiliate-toolkit-starter' ),
				'buttonType' => __( 'Button Type', 'affiliate-toolkit-starter' ),
				'default' => __( 'Default', 'affiliate-toolkit-starter' ),
				'addToCart' => __( 'Add to Cart', 'affiliate-toolkit-starter' ),
				'link' => __( 'Link', 'affiliate-toolkit-starter' ),
				'productPage' => __( 'Product Page', 'affiliate-toolkit-starter' ),
				'hideDisclaimer' => __( 'Hide Disclaimer', 'affiliate-toolkit-starter' ),
				'hideDisclaimerText' => __( 'Hide the affiliate disclaimer text', 'affiliate-toolkit-starter' ),
				'advancedSettings' => __( 'Advanced Settings', 'affiliate-toolkit-starter' ),
				'containerCssClass' => __( 'Container CSS Class', 'affiliate-toolkit-starter' ),
				'customCssContainer' => __( 'Custom CSS classes for the container', 'affiliate-toolkit-starter' ),
				'elementCssClass' => __( 'Element CSS Class', 'affiliate-toolkit-starter' ),
				'customCssElements' => __( 'Custom CSS classes for elements', 'affiliate-toolkit-starter' ),
				'overrideAffiliateId' => __( 'Override Affiliate ID', 'affiliate-toolkit-starter' ),
				'amazonEbayTrackingId' => __( 'Amazon or eBay tracking ID', 'affiliate-toolkit-starter' ),
				'productId' => __( 'Product ID:', 'affiliate-toolkit-starter' ),
				'templateLabel' => __( 'Template:', 'affiliate-toolkit-starter' ),
				'pleaseSelectProduct' => __( 'Please select a product using the sidebar →', 'affiliate-toolkit-starter' ),
				'pleaseSelectShopFirst' => __( 'Please select a shop first', 'affiliate-toolkit-starter' ),
				'productAlreadyExists' => __( 'Product already exists and has been selected', 'affiliate-toolkit-starter' ),
				'productImportedSuccessfully' => __( 'Product imported successfully!', 'affiliate-toolkit-starter' ),
				'failedToImportProduct' => __( 'Failed to import product: ', 'affiliate-toolkit-starter' ),
				'failedToImportInvalidResponse' => __( 'Failed to import product: Invalid response', 'affiliate-toolkit-starter' ),
				'failedToImportInvalidFormat' => __( 'Failed to import product: Invalid response format', 'affiliate-toolkit-starter' ),
				'errorImportingProduct' => __( 'Error importing product', 'affiliate-toolkit-starter' ),
				'searchFailed' => __( 'Search failed: ', 'affiliate-toolkit-starter' ),
				'searchFailedGeneric' => __( 'Search failed', 'affiliate-toolkit-starter' ),
				'serverError500' => __( 'Server error (500). Please check error log.', 'affiliate-toolkit-starter' ),

				// List Block
				'listSelection' => __( 'List Selection', 'affiliate-toolkit-starter' ),
				'selectedList' => __( 'Selected List:', 'affiliate-toolkit-starter' ),
				'changeList' => __( 'Change List', 'affiliate-toolkit-starter' ),
				'create' => __( 'Create', 'affiliate-toolkit-starter' ),
				'searchExistingLists' => __( 'Search existing lists:', 'affiliate-toolkit-starter' ),
				'enterListNameOrId' => __( 'Enter list name or ID...', 'affiliate-toolkit-starter' ),
				'showing5NewestLists' => __( 'Showing 5 newest lists', 'affiliate-toolkit-starter' ),
				'createNewProductList' => __( 'Create new product list:', 'affiliate-toolkit-starter' ),
				'listName' => __( 'List Name', 'affiliate-toolkit-starter' ),
				'enterListName' => __( 'Enter list name...', 'affiliate-toolkit-starter' ),
				'giveListDescriptiveName' => __( 'Give your list a descriptive name', 'affiliate-toolkit-starter' ),
				'listType' => __( 'List Type', 'affiliate-toolkit-starter' ),
				'topSeller' => __( 'Top Seller', 'affiliate-toolkit-starter' ),
				'newReleases' => __( 'New Releases', 'affiliate-toolkit-starter' ),
				'searchResults' => __( 'Search Results', 'affiliate-toolkit-starter' ),
				'productsMatchingKeyword' => __( 'Products matching search keyword', 'affiliate-toolkit-starter' ),
				'categoryBasedList' => __( 'Category-based list (requires node/category ID)', 'affiliate-toolkit-starter' ),
				'categoryNodeId' => __( 'Category/Node ID', 'affiliate-toolkit-starter' ),
				'example123456' => __( 'e.g., "123456"', 'affiliate-toolkit-starter' ),
				'browseNodeOrCategoryId' => __( 'Browse node or category ID from shop', 'affiliate-toolkit-starter' ),
				'previewFirst5' => __( 'Preview (first 5 results):', 'affiliate-toolkit-starter' ),
				'creating' => __( 'Creating...', 'affiliate-toolkit-starter' ),
				'createList' => __( 'Create List', 'affiliate-toolkit-starter' ),
				'listWillBeCreated' => __( '💡 The list will be created and automatically selected for this block.', 'affiliate-toolkit-starter' ),
				'orEnterListId' => __( 'Or enter List ID directly below:', 'affiliate-toolkit-starter' ),
				'listIdManual' => __( 'List ID (Manual)', 'affiliate-toolkit-starter' ),
				'enterListIdIfKnown' => __( 'Enter list ID if you know it', 'affiliate-toolkit-starter' ),
				'limit' => __( 'Limit', 'affiliate-toolkit-starter' ),
				'numberOfProducts' => __( 'Number of products to display (0 = all)', 'affiliate-toolkit-starter' ),
				'randomSort' => __( 'Random Sort', 'affiliate-toolkit-starter' ),
				'randomizeProductOrder' => __( 'Randomize product order on each page load', 'affiliate-toolkit-starter' ),
				'listId' => __( 'List ID:', 'affiliate-toolkit-starter' ),
				'limitLabel' => __( 'Limit:', 'affiliate-toolkit-starter' ),
				'products' => __( 'products', 'affiliate-toolkit-starter' ),
				'randomSortEnabled' => __( 'Random sort enabled', 'affiliate-toolkit-starter' ),
				'pleaseSelectList' => __( 'Please select a list using the sidebar →', 'affiliate-toolkit-starter' ),
				'pleaseEnterListName' => __( 'Please enter a list name', 'affiliate-toolkit-starter' ),
				'pleaseSelectShop' => __( 'Please select a shop', 'affiliate-toolkit-starter' ),
				'pleaseEnterSearchKeyword' => __( 'Please enter a search keyword', 'affiliate-toolkit-starter' ),
				'pleaseEnterCategoryNodeId' => __( 'Please enter a category/node ID', 'affiliate-toolkit-starter' ),
				'listCreatedSuccessfully' => __( 'List created successfully!', 'affiliate-toolkit-starter' ),
				'failedToCreateList' => __( 'Failed to create list: ', 'affiliate-toolkit-starter' ),
				'failedToCreateListInvalidResponse' => __( 'Failed to create list: Invalid response', 'affiliate-toolkit-starter' ),
				'failedToCreateListInvalidFormat' => __( 'Failed to create list: Invalid response format', 'affiliate-toolkit-starter' ),
				'errorCreatingList' => __( 'Error creating list', 'affiliate-toolkit-starter' ),
				'exampleLaptopGaming' => __( 'e.g., "laptop gaming"', 'affiliate-toolkit-starter' ),
			)
		);

		// Localize both block scripts
		wp_localize_script( 'atkp-block-product', 'atkpBlocks', $localize_data );
		wp_localize_script( 'atkp-block-list', 'atkpBlocks', $localize_data );

		// Set script translations - WordPress will load from .po/.mo files
		wp_set_script_translations( 'atkp-block-product', 'affiliate-toolkit-starter' );
		wp_set_script_translations( 'atkp-block-list', 'affiliate-toolkit-starter' );

		// Register product block with static callback
		$product_block = register_block_type( 'atkp/product', array(
			'api_version' => 2,
			'editor_script' => 'atkp-block-product',
			'editor_style' => 'atkp-block-editor',
			'render_callback' => array( __CLASS__, 'static_render_product_block' ),
			'supports' => array(
				'html' => false,
				'multiple' => true,
				'reusable' => true,
				'customClassName' => false,
				'className' => false,
				'anchor' => false,
			),
			'attributes' => array(
				'productId' => array( 'type' => 'string', 'default' => '' ),
				'productTitle' => array( 'type' => 'string', 'default' => '' ),
				'template' => array( 'type' => 'string', 'default' => '' ),
				'buttonType' => array( 'type' => 'string', 'default' => '' ),
				'align' => array( 'type' => 'string', 'default' => '' ),
				'elementCss' => array( 'type' => 'string', 'default' => '' ),
				'containerCss' => array( 'type' => 'string', 'default' => '' ),
				'content' => array( 'type' => 'string', 'default' => '' ),
				'field' => array( 'type' => 'string', 'default' => '' ),
				'link' => array( 'type' => 'boolean', 'default' => false ),
				'hideDisclaimer' => array( 'type' => 'boolean', 'default' => false ),
				'trackingId' => array( 'type' => 'string', 'default' => '' ),
			),
		) );

		if ( $product_block ) {
			ATKPLog::LogDebug( 'ATKP: Product block registered successfully' );
			ATKPLog::LogDebug( 'ATKP: Callback type: ' . gettype( $product_block->render_callback ) );
			ATKPLog::LogDebug( 'ATKP: Callback value: ' . print_r( $product_block->render_callback, true ) );
		} else {
			ATKPLog::LogError( 'ATKP: ERROR - Product block registration failed!' );
		}

		// Register list block with static callback
		$list_block = register_block_type( 'atkp/list', array(
			'api_version' => 2,
			'editor_script' => 'atkp-block-list',
			'editor_style' => 'atkp-block-editor',
			'render_callback' => array( __CLASS__, 'static_render_list_block' ),
			'supports' => array(
				'html' => false,
				'multiple' => true,
				'reusable' => true,
				'customClassName' => false,
				'className' => false,
				'anchor' => false,
			),
			'attributes' => array(
				'listId' => array( 'type' => 'string', 'default' => '' ),
				'listTitle' => array( 'type' => 'string', 'default' => '' ),
				'template' => array( 'type' => 'string', 'default' => '' ),
				'limit' => array( 'type' => 'number', 'default' => 0 ),
				'randomSort' => array( 'type' => 'boolean', 'default' => false ),
				'buttonType' => array( 'type' => 'string', 'default' => '' ),
				'elementCss' => array( 'type' => 'string', 'default' => '' ),
				'containerCss' => array( 'type' => 'string', 'default' => '' ),
				'hideDisclaimer' => array( 'type' => 'boolean', 'default' => false ),
				'trackingId' => array( 'type' => 'string', 'default' => '' ),
			),
		) );

		if ( $list_block ) {
			ATKPLog::LogDebug( 'ATKP: List block registered successfully' );
		} else {
			ATKPLog::LogError( 'ATKP: ERROR - List block registration failed!' );
		}

		ATKPLog::LogDebug( '========== ATKP: Block registration complete ==========' );
	}

	/**
	 * Enqueue Gutenberg assets
	 * Note: Block scripts are registered in register_gutenberg_blocks() and automatically
	 * enqueued by WordPress via the editor_script parameter in register_block_type()
	 */
	public function enqueue_gutenberg_assets() {
		// Only enqueue editor styles here
		// Scripts are handled automatically by WordPress from register_block_type()
		wp_enqueue_style(
			'atkp-block-editor',
			plugins_url( 'css/blocks-editor.css', ATKP_PLUGIN_FILE ),
			array( 'wp-edit-blocks' ),
			ATKP_UPDATE_VERSION
		);
	}

	/**
	 * Render product block
	 */
	public function render_product_block( $attributes ) {
		ATKPLog::LogDebug( '========== ATKP RENDER_PRODUCT_BLOCK CALLED ==========' );
		ATKPLog::LogDebug( 'Attributes: ' . print_r( $attributes, true ) );

		// Don't render in editor context - let JavaScript handle it
		// This is CRUCIAL for block editability after save
		if ( is_admin() && function_exists( 'get_current_screen' ) ) {
			$screen = get_current_screen();
			if ( $screen && $screen->is_block_editor() ) {
				ATKPLog::LogDebug( 'ATKP: In block editor context - skipping server render' );
				return '';
			}
		}

		// Check if we have a product ID
		if ( empty( $attributes['productId'] ) ) {
			ATKPLog::LogDebug( 'ATKP: No product ID - returning notice' );
			return '<div class="atkp-block-notice" style="padding:20px;background:#f0f0f0;border:2px dashed #999;border-radius:4px;text-align:center;">No product selected. Please select a product in the block settings.</div>';
		}

		ATKPLog::LogDebug( 'ATKP: Building shortcode for product ID: ' . $attributes['productId'] );

		// Build shortcode
		$shortcode = '[atkp_product';

		if ( ! empty( $attributes['productId'] ) ) {
			$shortcode .= ' id="' . esc_attr( $attributes['productId'] ) . '"';
		}

		if ( ! empty( $attributes['template'] ) ) {
			$shortcode .= ' template="' . esc_attr( $attributes['template'] ) . '"';
			ATKPLog::LogDebug( 'ATKP: Using template: ' . $attributes['template'] );
		}

		if ( ! empty( $attributes['buttonType'] ) ) {
			$shortcode .= ' buttontype="' . esc_attr( $attributes['buttonType'] ) . '"';
		}

		// Handle container CSS - combine align and containerCss
		$container_css = '';
		if ( ! empty( $attributes['align'] ) ) {
			$container_css .= $attributes['align'];
		}
		if ( ! empty( $attributes['containerCss'] ) ) {
			$container_css .= ' ' . $attributes['containerCss'];
		}
		if ( $container_css ) {
			$shortcode .= ' containercss="' . esc_attr( trim( $container_css ) ) . '"';
		}

		if ( ! empty( $attributes['elementCss'] ) ) {
			$shortcode .= ' elementcss="' . esc_attr( $attributes['elementCss'] ) . '"';
		}

		if ( ! empty( $attributes['link'] ) ) {
			$shortcode .= ' link="yes"';
		}

		if ( ! empty( $attributes['hideDisclaimer'] ) ) {
			$shortcode .= ' hidedisclaimer="yes"';
		}

		if ( ! empty( $attributes['trackingId'] ) ) {
			$shortcode .= ' tracking_id="' . esc_attr( $attributes['trackingId'] ) . '"';
		}

		$shortcode .= ']';

		// Add content if provided
		if ( ! empty( $attributes['content'] ) ) {
			$shortcode .= $attributes['content'];
		}

		$shortcode .= '[/atkp_product]';

		ATKPLog::LogDebug( 'ATKP: Generated shortcode: ' . $shortcode );

		// Execute shortcode and return output
		$output = do_shortcode( $shortcode );

		ATKPLog::LogDebug( 'ATKP: Output length: ' . strlen( $output ) );
		ATKPLog::LogDebug( 'ATKP: Output preview: ' . substr( strip_tags( $output ), 0, 200 ) );
		ATKPLog::LogDebug( '========== ATKP RENDER_PRODUCT_BLOCK END ==========' );

		return $output;
	}

	/**
	 * Render list block
	 */
	public function render_list_block( $attributes ) {
		// Don't render in editor context - let JavaScript handle it
		// This is CRUCIAL for block editability after save
		if ( is_admin() && function_exists( 'get_current_screen' ) ) {
			$screen = get_current_screen();
			if ( $screen && $screen->is_block_editor() ) {
				ATKPLog::LogDebug( 'ATKP: In block editor context - skipping server render for list' );
				return '';
			}
		}

		// Check if we have a list ID
		if ( empty( $attributes['listId'] ) ) {
			return '<div class="atkp-block-notice" style="padding:20px;background:#f0f0f0;border:2px dashed #999;border-radius:4px;text-align:center;">No list selected. Please select a list in the block settings.</div>';
		}

		// Build shortcode
		$shortcode = '[atkp_list';

		if ( ! empty( $attributes['listId'] ) ) {
			$shortcode .= ' id="' . esc_attr( $attributes['listId'] ) . '"';
		}

		if ( ! empty( $attributes['template'] ) ) {
			$shortcode .= ' template="' . esc_attr( $attributes['template'] ) . '"';
		}

		if ( ! empty( $attributes['limit'] ) ) {
			$shortcode .= ' limit="' . esc_attr( $attributes['limit'] ) . '"';
		}

		if ( ! empty( $attributes['randomSort'] ) ) {
			$shortcode .= ' randomsort="yes"';
		}

		if ( ! empty( $attributes['buttonType'] ) ) {
			$shortcode .= ' buttontype="' . esc_attr( $attributes['buttonType'] ) . '"';
		}

		if ( ! empty( $attributes['elementCss'] ) ) {
			$shortcode .= ' elementcss="' . esc_attr( $attributes['elementCss'] ) . '"';
		}

		if ( ! empty( $attributes['containerCss'] ) ) {
			$shortcode .= ' containercss="' . esc_attr( $attributes['containerCss'] ) . '"';
		}

		if ( ! empty( $attributes['hideDisclaimer'] ) ) {
			$shortcode .= ' hidedisclaimer="yes"';
		}

		if ( ! empty( $attributes['trackingId'] ) ) {
			$shortcode .= ' tracking_id="' . esc_attr( $attributes['trackingId'] ) . '"';
		}

		$shortcode .= '][/atkp_list]';

		// Execute shortcode and return output
		return do_shortcode( $shortcode );
	}

	/**
	 * Static wrapper for render_product_block
	 * WordPress calls this static method, which then calls the instance method
	 */
	public static function static_render_product_block( $attributes, $content = '', $block = null ) {
		error_log( '========== STATIC WRAPPER CALLED ==========' );
		error_log( 'Attributes received: ' . print_r( $attributes, true ) );

		// Ensure attributes is always an array
		if ( ! is_array( $attributes ) ) {
			$attributes = array();
		}

		$instance = self::get_instance();
		if ( ! $instance ) {
			error_log( 'ERROR: No instance found, creating new instance' );
			// Try to get pluginbase from global
			global $atkp_plugin;
			if ( isset( $atkp_plugin ) ) {
				$instance = new self( $atkp_plugin );
			} else {
				error_log( 'ERROR: Cannot create instance, $atkp_plugin not available' );
				return '<div style="padding:20px;background:#ffebee;border:2px solid #f44336;border-radius:4px;">Error: Generator instance not found. Plugin may not be fully initialized.</div>';
			}
		}

		error_log( 'Instance found, calling render_product_block' );
		return $instance->render_product_block( $attributes );
	}

	/**
	 * Static wrapper for render_list_block
	 * WordPress calls this static method, which then calls the instance method
	 */
	public static function static_render_list_block( $attributes, $content = '', $block = null ) {
		error_log( '========== STATIC LIST WRAPPER CALLED ==========' );
		error_log( 'List Attributes received: ' . print_r( $attributes, true ) );

		// Ensure attributes is always an array
		if ( ! is_array( $attributes ) ) {
			$attributes = array();
		}

		$instance = self::get_instance();
		if ( ! $instance ) {
			error_log( 'ERROR: No list instance found, creating new instance' );
			// Try to get pluginbase from global
			global $atkp_plugin;
			if ( isset( $atkp_plugin ) ) {
				$instance = new self( $atkp_plugin );
			} else {
				error_log( 'ERROR: Cannot create instance, $atkp_plugin not available' );
				return '<div style="padding:20px;background:#ffebee;border:2px solid #f44336;border-radius:4px;">Error: Generator instance not found. Plugin may not be fully initialized.</div>';
			}
		}

		error_log( 'Instance found, calling render_list_block' );
		return $instance->render_list_block( $attributes );
	}

	/**
	 * AJAX: Search products
	 */
	public function ajax_search_products() {
		error_log( 'ATKP: ajax_search_products called' );

		// Check if user is logged in and has permissions
		if ( ! is_user_logged_in() ) {
			error_log( 'ATKP: User not logged in' );
			wp_send_json_error( array( 'message' => 'Not logged in' ) );
			return;
		}

		if ( ! current_user_can( 'edit_posts' ) ) {
			error_log( 'ATKP: User cannot edit posts' );
			wp_send_json_error( array( 'message' => 'Permission denied' ) );
			return;
		}

		// Verify nonce - accept multiple nonce types
		$nonce = isset( $_POST['nonce'] ) ? $_POST['nonce'] : '';
		if ( $nonce ) {
			// Accept multiple nonce types for compatibility
			$nonce_valid = false;
			if ( wp_verify_nonce( $nonce, 'atkp-generator-nonce' ) ) {
				$nonce_valid = true;
			} elseif ( wp_verify_nonce( $nonce, 'atkp-import-nonce' ) ) {
				$nonce_valid = true;
			} elseif ( wp_verify_nonce( $nonce, 'wp_rest' ) ) {
				$nonce_valid = true;
			}

			if ( ! $nonce_valid ) {
				// If nonce is provided but invalid, reject
				error_log( 'ATKP: Invalid nonce - tried all types' );
				wp_send_json_error( array( 'message' => 'Invalid nonce' ) );
				return;
			}
		}

		$keyword = sanitize_text_field( $_POST['keyword'] ?? '' );
		$type = sanitize_text_field( $_POST['type'] ?? ATKP_PRODUCT_POSTTYPE );
		$limit = intval( $_POST['limit'] ?? 20 );

		error_log( 'ATKP: Searching - keyword: ' . $keyword . ', type: ' . $type . ', limit: ' . $limit );

		$results = array();

		$args = array(
			'post_type' => $type,
			'posts_per_page' => $limit,
			'post_status' => array( 'publish', 'draft' ),
			'orderby' => 'date',
			'order' => 'DESC'
		);

		// Add search if keyword provided
		if ( ! empty( $keyword ) ) {
			// Search in title and content
			$args['s'] = $keyword;

			// Also search in post meta (ASIN, EAN, etc.)
			if ( $type === ATKP_PRODUCT_POSTTYPE ) {
				add_filter( 'posts_search', array( $this, 'extend_product_search' ), 10, 2 );
				add_filter( 'posts_where', array( $this, 'extend_product_search_where' ), 10, 2 );
			}
		}

		error_log( 'ATKP: Query args: ' . print_r( $args, true ) );

		$query = new WP_Query( $args );

		// Remove filters after query
		if ( ! empty( $keyword ) && $type === ATKP_PRODUCT_POSTTYPE ) {
			remove_filter( 'posts_search', array( $this, 'extend_product_search' ) );
			remove_filter( 'posts_where', array( $this, 'extend_product_search_where' ) );
		}

		error_log( 'ATKP: Found posts: ' . $query->found_posts );

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();

				$product_id = get_the_ID();
				$imageurl = '';

				// Only load product images for products, not for lists
				if ( $type === ATKP_PRODUCT_POSTTYPE ) {
					$x = atkp_product_collection::load($product_id);
					$mainproduct = $x->get_main_product();

					$imageurl = ATKPTools::get_post_setting( $product_id, ATKP_PRODUCT_POSTTYPE . '_mediumimageurl' );

					if ( $imageurl == '' ) {
						$imageurl = ATKPTools::get_post_setting( $product_id, ATKP_PRODUCT_POSTTYPE . '_smallimageurl' );
					}

					if ( $imageurl == '' ) {
						$imageurl = ATKPTools::get_post_setting( $product_id, ATKP_PRODUCT_POSTTYPE . '_largeimageurl' );
					}

					if ( $imageurl == '' && $mainproduct != null ) {
						$imageurl = $mainproduct->mediumimageurl;
					}
					if ( $imageurl == '' && $mainproduct != null ) {
						$imageurl = $mainproduct->smallimageurl;
					}
					if ( $imageurl == '' && $mainproduct != null ) {
						$imageurl = $mainproduct->largeimageurl;
					}
				}

				$results[] = array(
					'id' => $product_id,
					'title' => get_the_title(),
					'edit_url' => get_edit_post_link( $product_id ),
					'imageurl' => $imageurl,
				);
			}
			wp_reset_postdata();
		}

		error_log( 'ATKP: Returning ' . count( $results ) . ' results' );

		wp_send_json_success( $results );
	}

	/**
	 * Extend product search to include meta fields (ASIN, EAN, etc.)
	 */
	public function extend_product_search( $search, $query ) {
		global $wpdb;

		if ( ! is_main_query() && ! $query->is_search() ) {
			return $search;
		}

		$search_term = $query->get( 's' );
		if ( empty( $search_term ) ) {
			return $search;
		}

		// This filter is called, but we'll use posts_where for the actual modification
		return $search;
	}

	/**
	 * Add meta query to search WHERE clause
	 */
	public function extend_product_search_where( $where, $query ) {
		global $wpdb;

		if ( ! $query->is_search() ) {
			return $where;
		}

		$search_term = $query->get( 's' );
		if ( empty( $search_term ) ) {
			return $where;
		}

		// Add search in post meta (ASIN, EAN, etc.)
		$meta_keys = array(
			ATKP_PRODUCT_POSTTYPE . '_asin',
			ATKP_PRODUCT_POSTTYPE . '_ean',
			ATKP_PRODUCT_POSTTYPE . '_isbn',
			ATKP_PRODUCT_POSTTYPE . '_mpn',
			ATKP_PRODUCT_POSTTYPE . '_articlenumber',
		);

		$meta_query = array();
		foreach ( $meta_keys as $meta_key ) {
			$meta_query[] = $wpdb->prepare(
				"({$wpdb->postmeta}.meta_key = %s AND {$wpdb->postmeta}.meta_value LIKE %s)",
				$meta_key,
				'%' . $wpdb->esc_like( $search_term ) . '%'
			);
		}

		if ( ! empty( $meta_query ) ) {
			$where .= " OR (
				{$wpdb->posts}.ID IN (
					SELECT DISTINCT {$wpdb->postmeta}.post_id 
					FROM {$wpdb->postmeta} 
					WHERE " . implode( ' OR ', $meta_query ) . "
				)
			)";
		}

		return $where;
	}

	/**
	 * AJAX: Search posts (for Select2 metabox dropdowns)
	 */
	public function ajax_search_posts() {
		// Verify nonce
		$nonce = sanitize_text_field( $_GET['nonce'] ?? '' );
		if ( ! wp_verify_nonce( $nonce, 'atkp-search-nonce' ) ) {
			wp_send_json_error( array( 'message' => 'Invalid nonce' ) );
			return;
		}

		// Check permissions
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => 'Permission denied' ) );
			return;
		}

		$post_type = sanitize_text_field( $_GET['post_type'] ?? '' );
		$search = sanitize_text_field( $_GET['search'] ?? '' );

		if ( empty( $post_type ) || empty( $search ) ) {
			wp_send_json( array() );
			return;
		}

		// Search posts
		$args = array(
			'post_type' => $post_type,
			'posts_per_page' => 50,
			'post_status' => array( 'publish', 'draft' ),
			's' => $search,
			'orderby' => 'relevance',
			'order' => 'DESC'
		);

		$query = new WP_Query( $args );
		$results = array();

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$results[] = array(
					'id' => get_the_ID(),
					'title' => get_the_title()
				);
			}
			wp_reset_postdata();
		}

		wp_send_json( $results );
	}

	/**
	 * AJAX: Search external products (for import)
	 */
	public function ajax_search_external_products() {
		// Add error handling at the top level
		try {
			// Check permissions
			if ( ! is_user_logged_in() || ! current_user_can( 'edit_posts' ) ) {
				wp_send_json_error( array( 'message' => 'Permission denied' ) );
				return;
			}

			$keyword = sanitize_text_field( $_POST['keyword'] ?? '' );
			$shop_id = intval( $_POST['shop_id'] ?? 0 );
			$page = intval( $_POST['page'] ?? 1 );

			// Log request for debugging
			error_log( 'External search request: keyword=' . $keyword . ', shop_id=' . $shop_id );

			if ( empty( $keyword ) ) {
				wp_send_json_error( array( 'message' => 'Keyword required' ) );
				return;
			}

			if ( empty( $shop_id ) ) {
				wp_send_json_error( array( 'message' => 'Shop ID required' ) );
				return;
			}

			// Get shop
			$shop = atkp_shop::load( $shop_id );
			if ( ! $shop ) {
				error_log( 'Shop not found: ' . $shop_id );
				wp_send_json_error( array( 'message' => 'Shop not found (ID: ' . $shop_id . ')' ) );
				return;
			}

			// Get shop provider
			$provider = $shop->provider;
			if ( ! $provider ) {
				error_log( 'Provider not available for shop: ' . $shop_id );
				wp_send_json_error( array( 'message' => 'Provider not available for this shop' ) );
				return;
			}

			// Check if provider supports search
			if ( ! method_exists( $provider, 'quick_search' ) ) {
				error_log( 'Provider does not support search: ' . get_class( $provider ) );
				wp_send_json_error( array( 'message' => 'Provider does not support product search' ) );
				return;
			}

			// Check login/credentials
			$message = $provider->checklogon( $shop );
			if ( $message != '' ) {
				error_log( 'Provider checklogon failed: ' . $message );
				wp_send_json_error( array( 'message' => $message ) );
				return;
			}

			// Search products - quick_search returns atkp_product_collection
			$search_result = $provider->quick_search( $keyword, 'product', $page );

			error_log( 'ATKP: quick_search returned: ' . print_r( $search_result, true ) );

			$results = array();
			if ( $search_result && isset( $search_result->products ) && is_array( $search_result->products ) ) {
				error_log( 'ATKP: Found ' . count( $search_result->products ) . ' products from API' );

				$skipped_count = 0;
				foreach ( $search_result->products as $product ) {
					// Check if product is an array or object
					$title = '';
					$asin = '';

					if ( is_array( $product ) ) {
						$title = $product['title'] ?? '';
						$asin = $product['asin'] ?? '';
					} else if ( is_object( $product ) ) {
						$title = $product->title ?? '';
						$asin = $product->asin ?? '';
					}

					// Skip products without title or ASIN
					if ( empty( $title ) || empty( $asin ) ) {
						$skipped_count++;
						error_log( 'ATKP: Skipping product - title: "' . $title . '", asin: "' . $asin . '"' );
						continue;
					}

					// Get additional fields based on product type
					if ( is_array( $product ) ) {
						$results[] = array(
							'asin' => $asin,
							'title' => $title,
							'imageurl' => $product['imageurl'] ?? '',
							'price' => $product['saleprice'] ?? $product['listprice'] ?? '',
							'shop_id' => $shop_id,
						);
					} else {
						$results[] = array(
							'asin' => $asin,
							'title' => $title,
							'imageurl' => $product->smallimageurl ?? $product->mediumimageurl ?? '',
							'price' => $product->saleprice ?? $product->listprice ?? '',
							'shop_id' => $shop_id,
						);
					}
				}

				error_log( 'ATKP: Skipped ' . $skipped_count . ' products (missing title/asin)' );
				error_log( 'ATKP: Returning ' . count( $results ) . ' valid products' );
			} else {
				error_log( 'ATKP: No products in search result or invalid format' );
			}

			// Log for debugging
			error_log( 'External search results: ' . print_r( array(
				'keyword' => $keyword,
				'shop_id' => $shop_id,
				'result_count' => count( $results ),
			), true ) );

			wp_send_json_success( array(
				'products' => $results,
				'total_pages' => $search_result->totalpages ?? 1,
				'current_page' => $page,
			) );

		} catch ( Exception $e ) {
			error_log( 'External search exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() );
			error_log( 'Stack trace: ' . $e->getTraceAsString() );
			wp_send_json_error( array( 'message' => 'Error: ' . $e->getMessage() ) );
		} catch ( Error $e ) {
			error_log( 'External search fatal error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() );
			wp_send_json_error( array( 'message' => 'Fatal error: ' . $e->getMessage() ) );
		}
	}

	/**
	 * Note: Product import and list creation are handled by atkp_endpoints.php
	 * - atkp_import_product (wp_ajax_atkp_import_product)
	 * - atkp_create_list (wp_ajax_atkp_create_list)
	 *
	 * The JavaScript should call these actions directly.
	 */
}


