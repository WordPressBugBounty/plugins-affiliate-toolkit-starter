<div class="atkp-noapi-packages-info" style="margin-top: 20px;">
	<h4 style="margin: 0 0 15px 0; color: #005162;">
		📦 <?php echo esc_html__( 'No-API Limits by License Plan', 'affiliate-toolkit-starter' ) ?>
	</h4>

	<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; margin-bottom: 20px;">
		<div style="background: #f6f7f7; border: 1px solid #c3c4c7; border-radius: 4px; padding: 12px; text-align: center;">
			<div style="font-weight: 600; font-size: 14px; color: #646970; margin-bottom: 6px;">
				<?php echo esc_html__( 'Starter', 'affiliate-toolkit-starter' ) ?>
			</div>
			<div style="font-size: 20px; font-weight: bold; color: #999; margin: 8px 0;">
				—
			</div>
			<div style="font-size: 11px; color: #999;">
				<?php echo esc_html__( 'No NoAPI included', 'affiliate-toolkit-starter' ) ?>
			</div>
		</div>

		<div style="background: #fff; border: 1px solid #ddd; border-radius: 4px; padding: 12px; text-align: center;">
			<div style="font-weight: 600; font-size: 14px; color: #23282d; margin-bottom: 6px;">
				<?php echo esc_html__( 'Niche', 'affiliate-toolkit-starter' ) ?>
			</div>
			<div style="font-size: 20px; font-weight: bold; color: #005162; margin: 8px 0;">
				25
			</div>
			<div style="font-size: 11px; color: #646970;">
				<?php echo esc_html__( 'products / month', 'affiliate-toolkit-starter' ) ?>
			</div>
		</div>

		<div style="background: #fff; border: 1px solid #ddd; border-radius: 4px; padding: 12px; text-align: center;">
			<div style="font-weight: 600; font-size: 14px; color: #23282d; margin-bottom: 6px;">
				<?php echo esc_html__( 'Extended', 'affiliate-toolkit-starter' ) ?>
			</div>
			<div style="font-size: 20px; font-weight: bold; color: #005162; margin: 8px 0;">
				75
			</div>
			<div style="font-size: 11px; color: #646970;">
				<?php echo esc_html__( 'products / month', 'affiliate-toolkit-starter' ) ?>
			</div>
		</div>

		<div style="background: #fff; border: 1px solid #ddd; border-radius: 4px; padding: 12px; text-align: center;">
			<div style="font-weight: 600; font-size: 14px; color: #23282d; margin-bottom: 6px;">
				<?php echo esc_html__( 'Pro', 'affiliate-toolkit-starter' ) ?>
			</div>
			<div style="font-size: 20px; font-weight: bold; color: #005162; margin: 8px 0;">
				100
			</div>
			<div style="font-size: 11px; color: #646970;">
				<?php echo esc_html__( 'products / month', 'affiliate-toolkit-starter' ) ?>
			</div>
		</div>

		<div style="background: #fff; border: 1px solid #54b9ca; border-radius: 4px; padding: 12px; text-align: center;">
			<div style="font-weight: 600; font-size: 14px; color: #005162; margin-bottom: 6px;">
				<?php echo esc_html__( 'All Access', 'affiliate-toolkit-starter' ) ?>
			</div>
			<div style="font-size: 20px; font-weight: bold; color: #005162; margin: 8px 0;">
				250
			</div>
			<div style="font-size: 11px; color: #646970;">
				<?php echo esc_html__( 'products / month', 'affiliate-toolkit-starter' ) ?>
			</div>
		</div>
	</div>

	<div style="margin-top: 15px;">
		<?php
		// Determine language-specific URLs
		$locale = get_locale();
		$is_german = (strpos($locale, 'de') === 0); // Checks if locale starts with 'de'

		$pricing_url = $is_german
			? 'https://www.affiliate-toolkit.com/de/preise/'
			: 'https://www.affiliate-toolkit.com/pricing/';

		$noapi_url = $is_german
			? 'https://www.affiliate-toolkit.com/de/downloads/amazon-kein-api-modus/'
			: 'https://www.affiliate-toolkit.com/downloads/amazon-no-api-mode/';
		?>
		<a href="<?php echo esc_url($pricing_url); ?>"
		   target="_blank"
		   class="button button-primary"
		   style="background: #005162; border-color: #005162;">
			<?php echo esc_html__( '💎 View Pricing Plans', 'affiliate-toolkit-starter' ) ?>
		</a>
		<a href="<?php echo esc_url($noapi_url); ?>"
		   target="_blank"
		   class="button"
		   style="margin-left: 10px;">
			<?php echo esc_html__( '🔍 Learn More About No-API', 'affiliate-toolkit-starter' ) ?>
		</a>
	</div>
</div>

