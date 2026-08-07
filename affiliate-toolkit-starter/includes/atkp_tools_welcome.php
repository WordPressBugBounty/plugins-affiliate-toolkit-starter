<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class atkp_tools_welcome {
	/**
	 * Construct the plugin object
	 */
	public function __construct( $pluginbase ) {


	}


	public function welcome_page() {

		$is_de = ATKPTools::is_lang_de();
		$kb_url = ( $is_de ? 'https://www.affiliate-toolkit.com/de/kb/' : 'https://www.affiliate-toolkit.com/kb/' ) . '?utm_medium=welcome-page&utm_content=KnowledgeBase&utm_source=WordPress&utm_campaign=starterpass';

		?>
        <div class="wrap" id="atkp-welcome">
            <div class="atkp-welcome-container">

                <!-- Header -->
                <div class="atkp-welcome-header">
                    <div class="atkp-welcome-logo">
                        <img src="<?php echo esc_url( plugins_url( 'images/affiliate-toolkit-wb.jpg', ATKP_PLUGIN_FILE ) ) ?>"
                             alt="affiliate-toolkit-logo">
                    </div>
                    <h1><?php echo esc_html__( 'Welcome to affiliate-toolkit', 'affiliate-toolkit-starter' ) ?></h1>
                    <p class="atkp-welcome-subtitle"><?php echo esc_html__( 'Thank you for choosing affiliate-toolkit - the most powerful WordPress affiliate plugin.', 'affiliate-toolkit-starter' ) ?></p>
                </div>

                <!-- Getting Started Guide -->
                <div class="atkp-welcome-section">
                    <h2><?php echo esc_html__( 'Getting Started', 'affiliate-toolkit-starter' ) ?></h2>
                    <p class="atkp-welcome-section-desc"><?php echo esc_html__( 'Follow these steps to set up your first affiliate products and start earning commissions.', 'affiliate-toolkit-starter' ) ?></p>

                    <div class="atkp-welcome-steps">

                        <div class="atkp-welcome-step">
                            <div class="atkp-welcome-step-number">1</div>
                            <div class="atkp-welcome-step-content">
                                <h3><span class="dashicons dashicons-store"></span> <?php echo esc_html__( 'Create a Shop', 'affiliate-toolkit-starter' ) ?></h3>
                                <p><?php echo esc_html__( 'A shop is your connection to an affiliate network like Amazon, eBay or AWIN. Enter your API credentials and affiliate-toolkit will handle the rest. You can connect multiple shops to compare prices across networks.', 'affiliate-toolkit-starter' ) ?></p>
                                <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=atkp_shop' ) ) ?>"
                                   class="button button-primary"><?php echo esc_html__( 'Create Your First Shop', 'affiliate-toolkit-starter' ) ?></a>
                            </div>
                        </div>

                        <div class="atkp-welcome-step">
                            <div class="atkp-welcome-step-number">2</div>
                            <div class="atkp-welcome-step-content">
                                <h3><span class="dashicons dashicons-download"></span> <?php echo esc_html__( 'Import Products', 'affiliate-toolkit-starter' ) ?></h3>
                                <p><?php echo esc_html__( 'Search for products by keyword, ASIN or EAN directly from the WordPress backend. Found products can be imported with one click. All product data like title, images, prices and descriptions are fetched automatically and kept up to date.', 'affiliate-toolkit-starter' ) ?></p>
                                <div class="atkp-welcome-step-buttons">
                                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=atkp_bulkimport' ) ) ?>"
                                       class="button button-primary"><?php echo esc_html__( 'Import product', 'affiliate-toolkit-starter' ) ?></a>
                                    <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=atkp_product' ) ) ?>"
                                       class="button button-secondary"><?php echo esc_html__( 'Go to Products', 'affiliate-toolkit-starter' ) ?></a>
                                </div>
                            </div>
                        </div>

                        <div class="atkp-welcome-step">
                            <div class="atkp-welcome-step-number">3</div>
                            <div class="atkp-welcome-step-content">
                                <h3><span class="dashicons dashicons-shortcode"></span> <?php echo esc_html__( 'Embed in Your Content', 'affiliate-toolkit-starter' ) ?></h3>
                                <p><?php echo esc_html__( 'Use the Gutenberg block or the shortcode generator to embed products into your posts and pages. Display them as product boxes, comparison tables or lists. The shortcode generator helps you build the perfect shortcode without any coding.', 'affiliate-toolkit-starter' ) ?></p>
                                <a href="<?php echo esc_url( admin_url( 'admin.php?page=ATKP_affiliate_toolkit-shortcodegenerator' ) ) ?>"
                                   class="button button-secondary"><?php echo esc_html__( 'Open Shortcode Generator', 'affiliate-toolkit-starter' ) ?></a>
                            </div>
                        </div>

                        <div class="atkp-welcome-step">
                            <div class="atkp-welcome-step-number">4</div>
                            <div class="atkp-welcome-step-content">
                                <h3><span class="dashicons dashicons-layout"></span> <?php echo esc_html__( 'Templates', 'affiliate-toolkit-starter' ) ?></h3>
                                <p><?php echo esc_html__( 'affiliate-toolkit comes with over 50 ready-to-use system templates for product boxes, comparison tables and listings. All templates are fully responsive. Optionally, you can create your own templates with the built-in editor using HTML, CSS and Blade syntax.', 'affiliate-toolkit-starter' ) ?></p>
                                <div class="atkp-welcome-step-buttons">
                                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=ATKP_viewtemplate&view=system' ) ) ?>"
                                       class="button button-secondary"><?php echo esc_html__( 'System Templates', 'affiliate-toolkit-starter' ) ?></a>
                                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=ATKP_viewtemplate&view=custom' ) ) ?>"
                                       class="button button-secondary"><?php echo esc_html__( 'Custom Templates', 'affiliate-toolkit-starter' ) ?></a>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="atkp-welcome-step-footer">
                        <a href="<?php echo esc_url( $kb_url ); ?>"
                           class="button button-primary" target="_blank" rel="noopener noreferrer">
							<?php echo esc_html__( 'Read the Knowledge Base', 'affiliate-toolkit-starter' ) ?>
                        </a>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=ATKP_affiliate_toolkit-Extensions' ) ) ?>"
                           class="button button-secondary">
							<?php echo esc_html__( 'Browse Extensions', 'affiliate-toolkit-starter' ) ?>
                        </a>
                    </div>
                </div>

                <!-- Features -->
                <div class="atkp-welcome-section">
                    <h2><?php echo esc_html__( 'Key Features', 'affiliate-toolkit-starter' ) ?></h2>
                    <p class="atkp-welcome-section-desc"><?php echo esc_html__( 'Everything you need to build a professional affiliate website with WordPress.', 'affiliate-toolkit-starter' ) ?></p>

                    <div class="atkp-welcome-features">
                        <div class="atkp-welcome-feature">
                            <span class="dashicons dashicons-database"></span>
                            <h4><?php echo esc_html__( 'Central management', 'affiliate-toolkit-starter' ) ?></h4>
                            <p><?php echo esc_html__( 'All product information is stored in one place. Update a product once and the changes apply everywhere it is embedded.', 'affiliate-toolkit-starter' ) ?></p>
                        </div>

                        <div class="atkp-welcome-feature">
                            <span class="dashicons dashicons-update"></span>
                            <h4><?php echo esc_html__( 'Automatic updates', 'affiliate-toolkit-starter' ) ?></h4>
                            <p><?php echo esc_html__( 'Product data, images and prices are updated automatically. Set it up once and let affiliate-toolkit do the work.', 'affiliate-toolkit-starter' ) ?></p>
                        </div>

                        <div class="atkp-welcome-feature">
                            <span class="dashicons dashicons-editor-table"></span>
                            <h4><?php echo esc_html__( 'Comparison tables', 'affiliate-toolkit-starter' ) ?></h4>
                            <p><?php echo esc_html__( 'Build professional comparison tables and bestseller lists that help your visitors make purchase decisions.', 'affiliate-toolkit-starter' ) ?></p>
                        </div>

                        <div class="atkp-welcome-feature">
                            <span class="dashicons dashicons-cart"></span>
                            <h4><?php echo esc_html__( 'WooCommerce ready', 'affiliate-toolkit-starter' ) ?></h4>
                            <p><?php echo esc_html__( 'Import affiliate products into WooCommerce as external products, including descriptions, images and prices.', 'affiliate-toolkit-starter' ) ?></p>
                        </div>

                        <div class="atkp-welcome-feature">
                            <span class="dashicons dashicons-admin-multisite"></span>
                            <h4><?php echo esc_html__( 'GeoIP Targeting', 'affiliate-toolkit-starter' ) ?></h4>
                            <p><?php echo esc_html__( 'Show visitors the right offer based on their country. Maximize conversions with automatic geo-targeting.', 'affiliate-toolkit-starter' ) ?></p>
                        </div>

                        <div class="atkp-welcome-feature">
                            <span class="dashicons dashicons-email-alt"></span>
                            <h4><?php echo esc_html__( 'Email notifications', 'affiliate-toolkit-starter' ) ?></h4>
                            <p><?php echo esc_html__( 'Get notified when products become unavailable or when errors occur. Stay on top of your affiliate inventory.', 'affiliate-toolkit-starter' ) ?></p>
                        </div>
                    </div>

                    <div class="atkp-welcome-step-footer">
                        <a href="<?php echo esc_url( ( $is_de ? 'https://www.affiliate-toolkit.com/de/?utm_medium=welcome-page&utm_content=AllFeatures&utm_source=WordPress&utm_campaign=starterpass#funktionen' : 'https://www.affiliate-toolkit.com/?utm_medium=welcome-page&utm_content=AllFeatures&utm_source=WordPress&utm_campaign=starterpass#features' ) ); ?>"
                           class="button button-secondary" rel="noopener noreferrer" target="_blank">
							<?php echo esc_html__( 'See All Features', 'affiliate-toolkit-starter' ) ?>
                        </a>
                    </div>
                </div>

                <!-- Upgrade -->
                <div class="atkp-welcome-section atkp-welcome-upgrade upgrade">
                    <h2><?php echo esc_html__( 'Upgrade your package', 'affiliate-toolkit-starter' ) ?></h2>
                    <p class="atkp-welcome-section-desc"><?php echo esc_html__( 'Unlock all integrations and premium features to grow your affiliate business.', 'affiliate-toolkit-starter' ) ?></p>

                    <div class="atkp-welcome-upgrade-grid">
                        <div class="atkp-welcome-upgrade-col">
                            <h4><?php echo esc_html__( 'Integrations', 'affiliate-toolkit-starter' ) ?></h4>
                            <ul>
                                <li><span class="dashicons dashicons-yes-alt"></span> AWIN Feeds</li>
                                <li><span class="dashicons dashicons-yes-alt"></span> billiger.de API</li>
                                <li><span class="dashicons dashicons-yes-alt"></span> eBay API</li>
                                <li><span class="dashicons dashicons-yes-alt"></span> Geizhals API</li>
                                <li><span class="dashicons dashicons-yes-alt"></span> CSV Feeds</li>
                                <li><span class="dashicons dashicons-yes-alt"></span> Yadore API</li>
                                <li><span class="dashicons dashicons-yes-alt"></span> Tradedoubler API</li>
                                <li><span class="dashicons dashicons-yes-alt"></span> HomeToGo API</li>
                            </ul>
                        </div>
                        <div class="atkp-welcome-upgrade-col">
                            <h4><?php echo esc_html__( 'Premium Features', 'affiliate-toolkit-starter' ) ?></h4>
                            <ul>
                                <li><span class="dashicons dashicons-yes-alt"></span> <?php echo esc_html__( 'Responsive Compare Table', 'affiliate-toolkit-starter' ) ?></li>
                                <li><span class="dashicons dashicons-yes-alt"></span> <?php echo esc_html__( 'Template Pack', 'affiliate-toolkit-starter' ) ?></li>
                                <li><span class="dashicons dashicons-yes-alt"></span> <?php echo esc_html__( 'Price History', 'affiliate-toolkit-starter' ) ?></li>
                                <li><span class="dashicons dashicons-yes-alt"></span> <?php echo esc_html__( 'Frontend Product Search', 'affiliate-toolkit-starter' ) ?></li>
                                <li><span class="dashicons dashicons-yes-alt"></span> <?php echo esc_html__( 'Custom Fields', 'affiliate-toolkit-starter' ) ?></li>
                                <li><span class="dashicons dashicons-yes-alt"></span> <?php echo esc_html__( 'Product Pages', 'affiliate-toolkit-starter' ) ?></li>
                                <li><span class="dashicons dashicons-yes-alt"></span> <?php echo esc_html__( 'WooCommerce Connector', 'affiliate-toolkit-starter' ) ?></li>
                                <li><span class="dashicons dashicons-yes-alt"></span> <?php echo esc_html__( 'Automatic price comparisons', 'affiliate-toolkit-starter' ) ?></li>
                            </ul>
                        </div>
                    </div>

                    <div class="atkp-welcome-step-footer">
                        <a href="<?php echo esc_url( ( $is_de ? 'https://www.affiliate-toolkit.com/de/preise/' : 'https://www.affiliate-toolkit.com/pricing/' ) . '?utm_medium=welcome-page&utm_content=Upgrade+Now&utm_source=WordPress&utm_campaign=starterpass' ); ?>"
                           rel="noopener noreferrer" target="_blank" class="button button-primary">
							<?php echo esc_html__( 'Upgrade Now', 'affiliate-toolkit-starter' ) ?>
                        </a>
                        <a href="<?php echo esc_url( ( $is_de ? 'https://www.affiliate-toolkit.com/de/erweiterungen/' : 'https://www.affiliate-toolkit.com/extensions/' ) . '?utm_medium=welcome-page&utm_content=AllExtensions&utm_source=WordPress&utm_campaign=starterpass' ); ?>"
                           rel="noopener noreferrer" target="_blank" class="button button-secondary">
							<?php echo esc_html__( 'Browse Extensions', 'affiliate-toolkit-starter' ) ?>
                        </a>
                    </div>
                </div>

                <!-- Testimonials -->
                <div class="atkp-welcome-section atkp-welcome-testimonials upgrade">
                    <h2><?php echo esc_html__( 'Testimonials', 'affiliate-toolkit-starter' ) ?></h2>
                    <p class="atkp-welcome-section-desc"><?php echo esc_html__( 'See what other affiliate marketers say about affiliate-toolkit.', 'affiliate-toolkit-starter' ) ?></p>

                    <div class="atkp-welcome-testimonial-grid">
                        <div class="atkp-welcome-testimonial">
                            <?php // phpcs:ignore PluginCheck.CodeAnalysis.Offloading.OffloadedContent -- Intentional remote image from own domain on welcome page. ?>
                            <img src="https://www.affiliate-toolkit.com/wp-content/uploads/2021/11/Bild2.jpg" alt="Simon Lüthje">
                            <blockquote>
                                <p><?php echo esc_html__( 'Affiliate Toolkit is a powerful, comprehensive and flexible WordPress plugin that makes my daily work on my niche sites easier. Besides the "standard" features, Affiliate Toolkit also contains other helpful and valuable additional features that currently no other plugin offer. With the help of the Affiliate Toolkit, I have been able to demonstrably increase my earnings.', 'affiliate-toolkit-starter' ) ?></p>
                                <cite><strong>Simon Lüthje</strong>, Blogger</cite>
                            </blockquote>
                        </div>

                        <div class="atkp-welcome-testimonial">
                            <?php // phpcs:ignore PluginCheck.CodeAnalysis.Offloading.OffloadedContent -- Intentional remote image from own domain on welcome page. ?>
                            <img src="https://www.affiliate-toolkit.com/wp-content/uploads/2021/11/peerwandiger-selbstaendig-im-netz-1.jpg" alt="Peer Wandiger">
                            <blockquote>
                                <p><?php echo esc_html__( 'The Affiliate Toolkit plugin seriously surprised me. The first impression was slightly dry and it is missing some kind of introduction to the plugin. But in principle it is very easy to use it and offers many functions. The free version is also very helpful but just the paid version holds all the aces.', 'affiliate-toolkit-starter' ) ?></p>
                                <cite><strong>Peer Wandiger</strong>, Blogger</cite>
                            </blockquote>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <style>
            #atkp-welcome {
                max-width: 960px;
                margin: 20px auto;
            }

            #atkp-welcome *,
            #atkp-welcome *::before,
            #atkp-welcome *::after {
                box-sizing: border-box;
            }

            /* Header */
            .atkp-welcome-header {
                text-align: center;
                padding: 40px 20px 30px;
                background: #fff;
                border: 1px solid #c3c4c7;
                border-radius: 4px;
                margin-bottom: 20px;
            }

            .atkp-welcome-logo {
                width: 80px;
                height: 80px;
                margin: 0 auto 20px;
                border-radius: 50%;
                overflow: hidden;
                border: 2px solid #c3c4c7;
            }

            .atkp-welcome-logo img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }

            .atkp-welcome-header h1 {
                font-size: 28px;
                font-weight: 600;
                margin: 0 0 8px;
                color: #1d2327;
            }

            .atkp-welcome-subtitle {
                font-size: 15px;
                color: #646970;
                margin: 0;
            }

            /* Sections */
            .atkp-welcome-section {
                background: #fff;
                border: 1px solid #c3c4c7;
                border-radius: 4px;
                padding: 30px 40px 40px;
                margin-bottom: 20px;
            }

            @media (max-width: 767px) {
                .atkp-welcome-section {
                    padding: 20px;
                }
            }

            .atkp-welcome-section h2 {
                font-size: 22px;
                font-weight: 600;
                color: #1d2327;
                margin: 0 0 8px;
                padding: 0;
            }

            .atkp-welcome-section-desc {
                color: #646970;
                font-size: 14px;
                margin: 0 0 24px;
            }

            /* Steps */
            .atkp-welcome-steps {
                position: relative;
                padding-left: 40px;
            }

            .atkp-welcome-steps::before {
                content: '';
                position: absolute;
                left: 17px;
                top: 0;
                bottom: 0;
                width: 2px;
                background: #c3c4c7;
            }

            .atkp-welcome-step {
                display: flex;
                align-items: flex-start;
                margin-bottom: 16px;
                position: relative;
            }

            .atkp-welcome-step:last-child {
                margin-bottom: 0;
            }

            .atkp-welcome-step-number {
                position: absolute;
                left: -40px;
                width: 36px;
                height: 36px;
                background: var(--wp-admin-theme-color, #2271b1);
                color: #fff;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 15px;
                font-weight: 600;
                flex-shrink: 0;
                z-index: 1;
            }

            .atkp-welcome-step-content {
                background: #f6f7f7;
                border: 1px solid #e0e0e0;
                border-radius: 4px;
                padding: 16px 20px;
                flex: 1;
            }

            .atkp-welcome-step-content h3 {
                font-size: 15px;
                font-weight: 600;
                margin: 0 0 8px;
                color: #1d2327;
            }

            .atkp-welcome-step-content h3 .dashicons {
                color: var(--wp-admin-theme-color, #2271b1);
                margin-right: 4px;
                font-size: 18px;
                width: 18px;
                height: 18px;
                line-height: 22px;
            }

            .atkp-welcome-step-content p {
                font-size: 13px;
                color: #50575e;
                margin: 0 0 10px;
                line-height: 1.5;
            }

            .atkp-welcome-step-buttons {
                display: flex;
                gap: 8px;
                flex-wrap: wrap;
                margin-top: 4px;
            }

            .atkp-welcome-step-content .button {
                margin-top: 4px;
            }

            .atkp-welcome-step-footer {
                text-align: center;
                margin-top: 30px;
                padding-top: 24px;
                border-top: 1px solid #f0f0f1;
                display: flex;
                justify-content: center;
                align-items: center;
                gap: 12px;
                flex-wrap: wrap;
            }


            /* Features Grid */
            .atkp-welcome-features {
                display: grid;
                grid-template-columns: 1fr 1fr 1fr;
                gap: 20px;
            }

            @media (max-width: 767px) {
                .atkp-welcome-features {
                    grid-template-columns: 1fr;
                }
            }

            .atkp-welcome-feature {
                padding: 16px 20px;
                background: #f6f7f7;
                border: 1px solid #e0e0e0;
                border-radius: 4px;
            }

            .atkp-welcome-feature .dashicons {
                font-size: 24px;
                width: 24px;
                height: 24px;
                color: var(--wp-admin-theme-color, #2271b1);
                margin-bottom: 8px;
            }

            .atkp-welcome-feature h4 {
                font-size: 14px;
                font-weight: 600;
                margin: 0 0 4px;
                color: #1d2327;
            }

            .atkp-welcome-feature p {
                font-size: 13px;
                color: #50575e;
                margin: 0;
                line-height: 1.5;
            }

            /* Upgrade */
            .atkp-welcome-upgrade {
                background: #1d2327;
                color: #fff;
            }

            .atkp-welcome-upgrade h2 {
                color: #fff;
            }

            .atkp-welcome-upgrade .atkp-welcome-section-desc {
                color: #a7aaad;
            }

            .atkp-welcome-upgrade-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 30px;
            }

            @media (max-width: 767px) {
                .atkp-welcome-upgrade-grid {
                    grid-template-columns: 1fr;
                }
            }

            .atkp-welcome-upgrade-col h4 {
                font-size: 14px;
                font-weight: 600;
                color: #fff;
                margin: 0 0 12px;
                padding-bottom: 8px;
                border-bottom: 1px solid #3c434a;
            }

            .atkp-welcome-upgrade-col ul {
                margin: 0;
                padding: 0;
                list-style: none;
            }

            .atkp-welcome-upgrade-col ul li {
                font-size: 14px;
                padding: 4px 0;
                color: #d4d4d4;
            }

            .atkp-welcome-upgrade-col ul li .dashicons {
                color: var(--wp-admin-theme-color, #72aee6);
                font-size: 16px;
                width: 16px;
                height: 16px;
                margin-right: 6px;
                vertical-align: text-bottom;
            }

            .atkp-welcome-upgrade .atkp-welcome-step-footer {
                border-top-color: #3c434a;
            }

            /* Testimonials */
            .atkp-welcome-testimonial-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 20px;
            }

            @media (max-width: 767px) {
                .atkp-welcome-testimonial-grid {
                    grid-template-columns: 1fr;
                }
            }

            .atkp-welcome-testimonial {
                background: #f6f7f7;
                border: 1px solid #e0e0e0;
                border-radius: 4px;
                padding: 20px;
                display: flex;
                gap: 14px;
                align-items: flex-start;
            }

            .atkp-welcome-testimonial img {
                width: 56px;
                height: 56px;
                border-radius: 50%;
                object-fit: cover;
                flex-shrink: 0;
            }

            .atkp-welcome-testimonial blockquote {
                margin: 0;
                padding: 0;
            }

            .atkp-welcome-testimonial p {
                font-size: 13px;
                line-height: 1.6;
                color: #50575e;
                margin: 0 0 10px;
                font-style: italic;
            }

            .atkp-welcome-testimonial cite {
                font-size: 13px;
                font-style: normal;
                color: #1d2327;
            }

            /* Pro variant hides upgrade sections */
            #atkp-welcome.pro .upgrade {
                display: none;
            }
        </style>

		<?php

	}


}

?>
