<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

define( 'ATKP_AMZ_WAIT', 3 );

//https://webservices.amazon.com/paapi5/documentation/quick-start/using-sdk.html
class atkp_shop_provider_amazon extends atkp_shop_provider_base {
	//das ist die basis klasse für alle shop provider


	public function __construct() {

	}

	public function get_maxproductcount() {
		return 10;
	}

	public function get_caption() {
		return esc_html__( 'Amazon Product Advertising API', 'affiliate-toolkit-starter' );
	}

	public function get_default_logo($post_id) {
		$website = $post_id == null ? '' : ATKPTools::get_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_access_website' );

		switch ( $website ) {
			default:
				return plugins_url( 'images/logo-normal-amazon-com.png', ATKP_AMAZON_PLUGIN_FILE );
			case 'de':
				return plugins_url( 'images/logo-normal-amazon-de.jpg', ATKP_AMAZON_PLUGIN_FILE );

		}
	}

	public function get_default_small_logo($post_id) {
		$website = $post_id == null ? '' : ATKPTools::get_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_access_website' );

		switch ( $website ) {
			default:
				return plugins_url( 'images/logo-small-amazon-com.png', ATKP_AMAZON_PLUGIN_FILE );
			case 'de':
				return plugins_url( 'images/logo-small-amazon-de.jpg', ATKP_AMAZON_PLUGIN_FILE );
		}
	}

	public function get_defaultbtn1_text() {
		return esc_html__( 'Buy now at Amazon', 'affiliate-toolkit-starter' );
	}

	public function get_defaultbtn2_text() {
		return esc_html__( 'Add to Amazon Cart', 'affiliate-toolkit-starter' );
	}

	public function replace_trackingid( $shopId, $url, $trackingId ) {
		//$associateTag = ATKPTools::get_post_setting($shopId, ATKP_SHOP_POSTTYPE.'_access_tracking_id');

		if ( $url == '' ) {
			return $url;
		}

		$startpos = strrpos( $url, '&AssociateTag=' );

		if ( ! $startpos ) {
			$startpos = strrpos( $url, '&tag=' );

			if ( ! $startpos ) {
				$startpos = strrpos( $url, '?tag=' );

				if ( ! $startpos ) {
					throw new exception( esc_html__( 'trackingcode not found: ' . $url, 'affiliate-toolkit-starter' ) );
				} else {
					$startpos = $startpos + 5;
				}
			} else {
				$startpos = $startpos + 5;
			}
		} else {
			$startpos = $startpos + 14;
		}

		$endofstring = substr( $url, $startpos );

		$endpos = stripos( $endofstring, '&' );

		if ( ! $endpos ) {
			$endpos = strlen( $endofstring );
		}

		//echo $url .'<br /><br />';
		//echo $startpos.'<br /><br />';
		//echo $endpos.'<br /><br />';
		//echo $endofstring.'<br /><br />';
		//echo substr($url, 0, $startpos).'<br /><br />';
		//echo  substr($url, $endpos, strlen($url) - $endpos).'<br /><br />';


		$url = substr( $url, 0, $startpos ) . $trackingId . substr( $endofstring, $endpos, strlen( $endofstring ) - $endpos );
		//echo $url;
		//exit;

		//$url =  str_replace('&AssociateTag='.$associateTag, '&AssociateTag='.$trackingId, $url);
		//$url =  str_replace('&tag='.$associateTag, '&tag='.$trackingId, $url);
		//$url =  str_replace('?tag='.$associateTag, '?tag='.$trackingId, $url);

		return $url;
	}


	private function validate_request_v5( $searchItemsRequest ) {
		$invalidPropertyList = $searchItemsRequest->listInvalidProperties();
		$length              = count( $invalidPropertyList );
		if ( $length > 0 ) {
			$txt = "Error forming the request" . PHP_EOL;
			foreach ( $invalidPropertyList as $invalidProperty ) {
				$txt .= $invalidProperty . PHP_EOL;
			}
			throw new Exception( esc_html__( $txt, 'affiliate-toolkit-starter' ) );
		}
	}

	private function validate_response_v5( $getItemsResponse ) {

		if ( $getItemsResponse->getErrors() != null ) {
			throw new Exception( esc_html__( $getItemsResponse->getErrors()[0]->getCode() . ': ' . $getItemsResponse->getErrors()[0]->getMessage(), 'affiliate-toolkit-starter' ) );
		}
	}


	private function set_default_shop( $post_id ) {
		$subshopsold = ATKPTools::get_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_default_shops' );
		$subshops    = array();

		//add subshop for amazon
		$subshop         = new subshop();
		$subshop->title = esc_html__( 'Amazon', 'affiliate-toolkit-starter' );
		$subshop->shopid = $post_id;

		$website = ATKPTools::get_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_access_website' );

		switch ( $website ) {
			default:
				$subshop->logourl      = plugins_url( 'images/logo-normal-amazon.jpg', ATKP_AMAZON_PLUGIN_FILE );
				$subshop->smalllogourl = plugins_url( 'images/logo-small-amazon.jpg', ATKP_AMAZON_PLUGIN_FILE );
				break;
			case 'de':
				$subshop->logourl      = plugins_url( 'images/logo-normal-amazon-de.jpg', ATKP_AMAZON_PLUGIN_FILE );
				$subshop->smalllogourl = plugins_url( 'images/logo-small-amazon-de.jpg', ATKP_AMAZON_PLUGIN_FILE );
				break;

		}

		$subshop->enabled = true;

		array_push( $subshops, $subshop );

		//für bestehende alte subshops ist dieser teil noch drinnen
		if ( is_array( $subshopsold ) ) {
			foreach ( $subshopsold as $shopold ) {
				if ( $subshop->shopid == $shopold->shopid && $subshop->programid == $shopold->programid ) {
					$subshop->enabled            = $shopold->enabled;
					$subshop->customtitle        = $shopold->customtitle;
					$subshop->customsmalllogourl = $shopold->customsmalllogourl;
					$subshop->customlogourl      = $shopold->customlogourl;
					$subshop->customfield1       = $shopold->customfield1;
					$subshop->customfield2       = $shopold->customfield2;
					$subshop->customfield3       = $shopold->customfield3;
				}
			}
		}

		ATKPTools::set_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_default_shops', $subshops );
	}

	private function get_config( $apikey, $apisecretkey, $country ) {
		$config = new Amazon\ProductAdvertisingAPI\v1\Configuration();

		$config->setAccessKey( $apikey );
		$config->setSecretKey( $apisecretkey );

		$host   = '';
		$region = '';

		switch ( $country ) {
			default;
			case 'de':
				$host   = 'webservices.amazon.de';
				$region = 'eu-west-1';
				break;
			case 'nl':
				$host   = 'webservices.amazon.nl';
				$region = 'eu-west-1';
				break;
			case 'com':
				$host   = 'webservices.amazon.com';
				$region = 'us-east-1';
				break;
			case 'co.uk':
				$host   = 'webservices.amazon.co.uk';
				$region = 'eu-west-1';
				break;
			case 'ca':
				$host   = 'webservices.amazon.ca';
				$region = 'us-east-1';
				break;
			case 'fr':
				$host   = 'webservices.amazon.fr';
				$region = 'eu-west-1';
				break;
			case 'co.jp':
				$host   = 'webservices.amazon.co.jp';
				$region = 'us-west-2';
				break;
			case 'it':
				$host   = 'webservices.amazon.it';
				$region = 'eu-west-1';
				break;
			case 'cn':
			case 'es':
				$host   = 'webservices.amazon.es';
				$region = 'eu-west-1';
				break;
			case 'in':
				$host   = 'webservices.amazon.in';
				$region = 'eu-west-1';
				break;
			case 'au':
				$host   = 'webservices.amazon.com.au';
				$region = 'us-west-2';
				break;
			case 'com.br':
				$host   = 'webservices.amazon.com.br';
				$region = 'us-east-1';
				break;
			case 'com.mx':
				$host   = 'webservices.amazon.com.mx';
				$region = 'us-east-1';
				break;
			case 'com.tr':
				$host   = 'webservices.amazon.com.tr';
				$region = 'eu-west-1';
				break;
			case 'ae':
				$host   = 'webservices.amazon.ae';
				$region = 'eu-west-1';
				break;
			case 'pl':
				$host   = 'webservices.amazon.pl';
				$region = 'eu-west-1';
				break;
			case 'com.be':
				$host   = 'webservices.amazon.com.be';
				$region = 'eu-west-1';
				break;
		}


		$config->setHost( $host );
		$config->setRegion( $region );

		return $config;
	}

	private function check_guzzle() {
		$funcInc = [
			'GuzzleHttp\choose_handler'      => 'lib/vendor/guzzlehttp/guzzle/src/functions_include.php',
			'GuzzleHttp\Psr7\build_query'    => 'lib/vendor/guzzlehttp/psr7/src/functions.php',
			'GuzzleHttp\Promise\promise_for' => 'lib/vendor/guzzlehttp/promises/src/functions.php',
			//'Promise\promise_for' => 'lib/vendor/guzzlehttp/promises/src/functions.php',
		];

		foreach ( $funcInc as $function => $incPath ) {
			if ( ! function_exists( $function ) ) {
				$includePath = ATKP_AMAZON_PLUGIN_DIR . DIRECTORY_SEPARATOR . $incPath;
				if ( file_exists( $includePath ) ) {
					require_once $includePath;
				}
			}
		}
	}

	private function get_api_instance( $apikey, $apisecretkey, $website ) {
		require_once ATKP_AMAZON_PLUGIN_DIR . '/lib/vendor/autoload.php';

		$config = $this->get_config( $apikey, $apisecretkey, $website );

		$this->check_guzzle();

		$apiInstance = new Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\api\DefaultApi( new \GuzzleHttp\Client(), $config );

		return $apiInstance;
	}

	private function check_configuration_v5( $post_id, $apikey, $apisecretkey, $website, $usessl, $trackingid ) {
		//require_once ATKP_PLUGIN_DIR . '/lib/paapi5-sdk/vendor/autoload.php';

		$apiInstance = $this->get_api_instance( $apikey, $apisecretkey, $website );

		$searchItemsRequest = new Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\SearchItemsRequest();
		$searchItemsRequest->setSearchIndex( 'All' );
		$searchItemsRequest->setKeywords( 'Harry Potter' );
		$searchItemsRequest->setItemCount( 1 );
		$searchItemsRequest->setPartnerTag( $trackingid );
		$searchItemsRequest->setPartnerType( Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\PartnerType::ASSOCIATES );

		$this->validate_request_v5( $searchItemsRequest );

		$test = '';

		try {
			$searchItemsResponse = $apiInstance->searchItems( $searchItemsRequest );

			$this->validate_response_v5( $searchItemsResponse );

			$itemcount = $searchItemsResponse->getSearchResult()->getTotalResultCount();
			if ( $itemcount == 0 ) {
				$test = 'item count is null';
			}

		} catch ( Amazon\ProductAdvertisingAPI\v1\ApiException $exception ) {
			$test = "API-Error: " . $exception->getCode() . " " . $exception->getMessage();

			if ( $exception->getResponseObject() instanceof Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\ProductAdvertisingAPIClientException ) {
				$errors = $exception->getResponseObject()->getErrors();
				foreach ( $errors as $error ) {
					$test = "Response-Error: " . $error->getCode() . " " . $error->getMessage();
				}
			} else {
				$test .= "Error response body: " . $exception->getResponseBody();
			}
		} catch ( Exception $exception ) {
			$test = "Error Message: " . $exception->getMessage(); //. ' ' . $exception->getTraceAsString();
		}

		if ( $test == '' ) {
			$this->set_default_shop( $post_id );
		} else {
			return $test;
		}
	}

	public function check_configuration( $post_id ) {
		try {
			$apikey       = ATKPTools::get_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_access_key' );
			$apisecretkey = ATKPTools::get_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_access_secret_key' );
			$website      = ATKPTools::get_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_access_website' );
			$trackingid   = ATKPTools::get_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_access_tracking_id' );
			$usessl       = ATKPTools::get_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_access_tracking_id' );
			$sitestripe   = ATKPTools::get_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_sitestripe' );

			$this->seconds_wait = ATKPTools::get_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_seconds_wait' );
			if ( $this->seconds_wait <= 0 ) {
				$this->seconds_wait = 1;
			}


			if ( $sitestripe == 2 || $sitestripe == 3 ) {
				return '';
			}

			$message = '';
			if ( $apikey != '' && $apisecretkey != '' ) {

					return $this->check_configuration_v5( $post_id, $apikey, $apisecretkey, $website, $usessl, $trackingid ) . '';

			} else {
				//wenn zugangscodes gelöscht werden muss message auch geleert werden
				$message = 'Credientials are empty';
			}

			return $message;
		} catch ( Exception $e ) {
			if ( ATKPLog::$logenabled ) {
				ATKPLog::LogError( $e->getMessage() );
			}

			return $e->getMessage();
		}
	}

	private function convert_response( $response ) {

		//return json_decode(json_encode($response), false);
		return json_decode( json_encode( (array) simplexml_load_string( $response ) ), 0 );
	}

	public function set_configuration( $post_id ) {

		ATKPTools::set_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_amz_medium_image_size', ATKPTools::get_post_parameter( ATKP_SHOP_POSTTYPE . '_amz_medium_image_size', 'int' ) );
		ATKPTools::set_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_amz_small_image_size', ATKPTools::get_post_parameter( ATKP_SHOP_POSTTYPE . '_amz_small_image_size', 'int' ) );


		ATKPTools::set_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_access_website', ATKPTools::get_post_parameter( ATKP_SHOP_POSTTYPE . '_amz_access_website', 'string' ) );
		ATKPTools::set_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_access_tracking_id', ATKPTools::get_post_parameter( ATKP_SHOP_POSTTYPE . '_amz_access_tracking_id', 'string' ) );
		ATKPTools::set_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_languages_of_preference', ATKPTools::get_post_parameter( ATKP_SHOP_POSTTYPE . '_languages_of_preference', 'string' ) );
		ATKPTools::set_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_asindataapikey', ATKPTools::get_post_parameter( ATKP_SHOP_POSTTYPE . '_asindataapikey', 'string' ) );

		ATKPTools::set_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_load_customer_reviews', ATKPTools::get_post_parameter( ATKP_SHOP_POSTTYPE . '_amz_load_customer_reviews', 'bool' ) );
		ATKPTools::set_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_load_variations', ATKPTools::get_post_parameter( ATKP_SHOP_POSTTYPE . '_amz_load_variations', 'bool' ) );
		ATKPTools::set_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_onlynew', ATKPTools::get_post_parameter( ATKP_SHOP_POSTTYPE . '_amz_onlynew', 'int' ) );

		ATKPTools::set_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_access_key', ATKPTools::get_post_parameter( ATKP_SHOP_POSTTYPE . '_amz_access_key', 'string' ) );
		ATKPTools::set_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_access_secret_key', ATKPTools::get_post_parameter( ATKP_SHOP_POSTTYPE . '_amz_access_secret_key', 'string' ) );

		ATKPTools::set_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_sitestripe', ATKPTools::get_post_parameter( ATKP_SHOP_POSTTYPE . '_sitestripe', 'int' ) );
		ATKPTools::set_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_seconds_wait', ATKPTools::get_post_parameter( ATKP_SHOP_POSTTYPE . '_amz_seconds_wait', 'int' ) );

		ATKPTools::set_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_apiversion', ATKPTools::get_post_parameter( ATKP_SHOP_POSTTYPE . '_amz_apiversion', 'string' ) );


	}

	private function get_defaultshops( $post_id ) {
		$subshops = array();

		$website = ATKPTools::get_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_access_website' );

		$subshop = new subshop();

		$subshop->title = esc_html__( 'Amazon', 'affiliate-toolkit-starter' );

		switch ( $website ) {
            default:
	            $subshop->logourl      = plugins_url( 'images/logo-normal-amazon.jpg', ATKP_AMAZON_PLUGIN_FILE );
	            $subshop->smalllogourl = plugins_url( 'images/logo-small-amazon.jpg', ATKP_AMAZON_PLUGIN_FILE );
	            break;
			case 'de':
				$subshop->logourl      = plugins_url( 'images/logo-normal-amazon-de.jpg', ATKP_AMAZON_PLUGIN_FILE );
				$subshop->smalllogourl = plugins_url( 'images/logo-small-amazon-de.jpg', ATKP_AMAZON_PLUGIN_FILE );
				break;

		}

		$subshop->shopid    = $post_id;
		$subshop->programid = '';

		$subshop->enabled = true;

		array_push( $subshops, $subshop );

		return $subshops;
	}

	public function get_configuration( $post ) {
		$webservice = ATKPTools::get_post_setting( $post->ID, ATKP_SHOP_POSTTYPE . '_access_webservice' );

		$apikey       = '';
		$apisecretkey = '';
		$subshops     = null;

		if ( $webservice == '1' ) {
			$apikey       = ATKPTools::get_post_setting( $post->ID, ATKP_SHOP_POSTTYPE . '_access_key' );
			$apisecretkey = ATKPTools::get_post_setting( $post->ID, ATKP_SHOP_POSTTYPE . '_access_secret_key' );
		}

		// Determine the mode: Check if NoAPI plugin is available
		$noapi_available = defined( 'ATKP_AMAZNOAPI_ITEM_ID' ) && ATKP_LicenseController::get_module_license_status( 'amaznoapi' ) == 'valid';
		$noapi_mode = ATKPTools::get_post_setting( $post->ID, ATKP_SHOP_POSTTYPE . '_sitestripe' );

		// Determine which mode to show (API or NoAPI)
		$use_mode = 'api'; // default to API
		if ($noapi_available && ($noapi_mode == 2 || $noapi_mode == 3)) {
			$use_mode = 'noapi';
		} else if ($noapi_available && !$apikey) {
			// If NoAPI is available but no API key is set, suggest NoAPI
			$use_mode = 'noapi';
		}
		?>

		<style>
			.atkp-mode-selection {
				background: #f8f9fa;
				border: 1px solid #ddd;
				border-radius: 6px;
				padding: 20px;
				margin-bottom: 20px;
			}

			.atkp-mode-selection h3 {
				margin: 0 0 15px 0;
				font-size: 16px;
				color: #23282d;
			}

			.atkp-mode-options {
				display: grid;
				grid-template-columns: 1fr 1fr;
				gap: 15px;
				margin-bottom: 15px;
			}

			.atkp-mode-option {
				background: #fff;
				border: 2px solid #ddd;
				border-radius: 6px;
				padding: 20px;
				cursor: pointer;
				transition: all 0.3s ease;
				position: relative;
			}

			.atkp-mode-option:hover {
				border-color: #54b9ca;
				box-shadow: 0 2px 8px rgba(84, 185, 202, 0.2);
			}

			.atkp-mode-option input[type="radio"] {
				position: absolute;
				top: 15px;
				right: 15px;
				width: 20px;
				height: 20px;
				cursor: pointer;
			}

			.atkp-mode-option.selected {
				border-color: #005162;
				background: #f0f8fa;
				box-shadow: 0 2px 8px rgba(0, 81, 98, 0.15);
			}

			.atkp-mode-option-title {
				font-size: 15px;
				font-weight: 600;
				color: #005162;
				margin: 0 0 8px 0;
				display: flex;
				align-items: center;
				gap: 8px;
			}

			.atkp-mode-option-desc {
				font-size: 13px;
				color: #666;
				line-height: 1.5;
				margin: 0;
			}

			/* Config sections are TR elements */
			tr.atkp-config-section {
				display: none;
			}

			tr.atkp-config-section.active {
				display: table-row;
			}

			.atkp-config-section h4 {
				margin: 0 0 15px 0;
				font-size: 15px;
				color: #005162;
				border-bottom: 2px solid #54b9ca;
				padding-bottom: 10px;
			}
		</style>

		<tr>
			<td colspan="2">
				<div class="atkp-mode-selection">
					<h3>🔧 <?php echo esc_html__( 'Select Data Source', 'affiliate-toolkit-starter' ) ?></h3>
					<p style="margin: 0 0 15px 0; color: #666; font-size: 13px;">
						<?php echo esc_html__( 'Choose how you want to retrieve Amazon product data:', 'affiliate-toolkit-starter' ) ?>
					</p>

					<div class="atkp-mode-options">
						<label class="atkp-mode-option <?php echo $use_mode == 'api' ? 'selected' : ''; ?>" for="atkp_use_api">
							<input type="radio"
								   id="atkp_use_api"
								   name="atkp_mode_selection"
								   value="api"
								   <?php echo $use_mode == 'api' ? 'checked' : ''; ?>>
							<div class="atkp-mode-option-title">
								🔑 <?php echo esc_html__( 'Amazon Product Advertising API', 'affiliate-toolkit-starter' ) ?>
							</div>
							<p class="atkp-mode-option-desc">
								<?php echo esc_html__( 'Official Amazon API. Requires API credentials and has usage limits based on revenue.', 'affiliate-toolkit-starter' ) ?>
							</p>
						</label>

						<label class="atkp-mode-option <?php echo $use_mode == 'noapi' ? 'selected' : ''; ?>" for="atkp_use_noapi">
							<input type="radio"
								   id="atkp_use_noapi"
								   name="atkp_mode_selection"
								   value="noapi"
								   <?php echo $use_mode == 'noapi' ? 'checked' : ''; ?>>
							<div class="atkp-mode-option-title">
								⚡ <?php echo esc_html__( 'No-API Mode', 'affiliate-toolkit-starter' ) ?>
								<?php if (!$noapi_available) { ?>
									<span style="background: #ff9800; color: #fff; padding: 2px 6px; border-radius: 3px; font-size: 10px;">
										<?php echo esc_html__( 'PURCHASE REQUIRED', 'affiliate-toolkit-starter' ) ?>
									</span>
								<?php } ?>
							</div>
							<p class="atkp-mode-option-desc">
								<?php echo esc_html__( 'Retrieve product data without API limits. Included in your affiliate-toolkit license.', 'affiliate-toolkit-starter' ) ?>
							</p>
						</label>
					</div>
				</div>

				<script>
					jQuery(document).ready(function($) {
						// Handle mode selection
						$('.atkp-mode-option').on('click', function() {
							$('.atkp-mode-option').removeClass('selected');
							$(this).addClass('selected');
							$(this).find('input[type="radio"]').prop('checked', true);

							var selectedMode = $(this).find('input[type="radio"]').val();

							// Show/hide relevant config sections
							$('.atkp-config-section').removeClass('active');
							$('#atkp-config-' + selectedMode).addClass('active');

							// Show/hide relevant info sections
							$('.atkp-info-section').removeClass('active');
							$('#atkp-info-' + selectedMode).addClass('active');

							// Update hidden sitestripe value based on mode
							var hiddenSitestripeField = $('#<?php echo esc_js(ATKP_SHOP_POSTTYPE . '_sitestripe_hidden'); ?>');
							var visibleSitestripeField = $('#<?php echo esc_js(ATKP_SHOP_POSTTYPE . '_sitestripe'); ?>');

							if (selectedMode == 'noapi') {
								// When NoAPI mode is selected, get value from dropdown or default to 2
								var currentVal = visibleSitestripeField.length ? visibleSitestripeField.val() : hiddenSitestripeField.val();
								if (!currentVal || currentVal == '1') {
									currentVal = '2'; // Default to Always Active
								}
								hiddenSitestripeField.val(currentVal);
								if (visibleSitestripeField.length) {
									visibleSitestripeField.val(currentVal);
								}
							} else {
								// When API mode is selected, set to Disabled (1)
								hiddenSitestripeField.val('1');
							}
						});

						// Sync dropdown changes back to hidden field
						$('#<?php echo esc_js(ATKP_SHOP_POSTTYPE . '_sitestripe'); ?>').on('change', function() {
							$('#<?php echo esc_js(ATKP_SHOP_POSTTYPE . '_sitestripe_hidden'); ?>').val($(this).val());
						});

						// Trigger initial display based on checked radio
						function initializeModeSelection() {
							var initialMode = $('input[name="atkp_mode_selection"]:checked');
							if (initialMode.length) {
								// Trigger click on the checked option's label
								initialMode.closest('.atkp-mode-option').trigger('click');
							} else {
								// Default to API mode if nothing is checked
								$('#atkp_use_api').prop('checked', true).closest('.atkp-mode-option').trigger('click');
							}
						}

						// Initialize on page load
						initializeModeSelection();
					});
				</script>

				<!-- Hidden field to store sitestripe value - always present in form -->
				<input type="hidden"
					   id="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_sitestripe_hidden'); ?>"
					   name="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_sitestripe'); ?>"
					   value="<?php echo esc_attr(ATKPTools::get_post_setting( $post->ID, ATKP_SHOP_POSTTYPE . '_sitestripe' )); ?>">
			</td>
		</tr>

		<!-- Amazon API Configuration Section -->
		<tr class="atkp-config-section" id="atkp-config-api">
			<td colspan="2">
				<div class="atkp-config-section active">
					<h4>🔑 <?php echo esc_html__( 'Amazon API Configuration', 'affiliate-toolkit-starter' ) ?></h4>

					<table class="form-table">
        <tr>
            <th scope="row">
                <label for="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_amz_access_key') ?>">
	                <?php echo esc_html__( 'Amazon Access Key ID', 'affiliate-toolkit-starter' ) ?> <span
                            class="description"><?php echo esc_html__( '(required)', 'affiliate-toolkit-starter' ) ?></span>
                </label>
            </th>
            <td>
                <input style="width:40%" type="text" id="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_amz_access_key') ?>"
                       name="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_amz_access_key') ?>" value="<?php echo esc_attr($apikey); ?>">
                <label for="">

                </label>
	            <?php ATKPTools::display_helptext( esc_html__( 'You can find your API key in the Amazon Partnernet. In the Submenu "Tools > Product Advertising API > Manage Your Credentials".', 'affiliate-toolkit-starter' ) ) ?>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_amz_access_secret_key') ?>">
	                <?php echo esc_html__( 'Amazon Secret Access Key', 'affiliate-toolkit-starter' ) ?> <span
                            class="description"><?php echo esc_html__( '(required)', 'affiliate-toolkit-starter' ) ?></span>
                </label>

            </th>
            <td>
                <input style="width:40%" type="password"
                       id="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_amz_access_secret_key') ?>"
                       name="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_amz_access_secret_key') ?>"
                       value="<?php echo esc_attr($apisecretkey); ?>">
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_amz_access_website') ?>">
	                <?php echo esc_html__( 'Amazon Website', 'affiliate-toolkit-starter' ) ?> <span
                            class="description"><?php echo esc_html__( '(required)', 'affiliate-toolkit-starter' ) ?></span>
                </label>
            </th>
            <td>
                <select name="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_amz_access_website') ?>" >
					<?php
					//        public static $locations = array('de', 'com', 'co.uk', 'ca', 'fr', 'co.jp', 'it', 'cn', 'es', 'in', 'com.br');

					$locations = array(
						'de'    => esc_html__( 'Amazon Germany', 'affiliate-toolkit-starter' ),
						'com'   => esc_html__( 'Amazon United States', 'affiliate-toolkit-starter' ),
						'co.uk' => esc_html__( 'Amazon United Kingdom', 'affiliate-toolkit-starter' ),
						'ca'    => esc_html__( 'Amazon Canada', 'affiliate-toolkit-starter' ),
						'fr'    => esc_html__( 'Amazon France', 'affiliate-toolkit-starter' ),
						'co.jp' => esc_html__( 'Amazon Japan', 'affiliate-toolkit-starter' ),
						'it'    => esc_html__( 'Amazon Italy', 'affiliate-toolkit-starter' ),

						'es'     => esc_html__( 'Amazon Spain', 'affiliate-toolkit-starter' ),
						'in'     => esc_html__( 'Amazon India', 'affiliate-toolkit-starter' ),
						'com.br' => esc_html__( 'Amazon Brazil', 'affiliate-toolkit-starter' ),
						'au'     => esc_html__( 'Amazon Australia', 'affiliate-toolkit-starter' ),
						'com.mx' => esc_html__( 'Amazon Mexico', 'affiliate-toolkit-starter' ),
						'com.tr' => esc_html__( 'Amazon Turkey', 'affiliate-toolkit-starter' ),
						'com.be' => esc_html__( 'Amazon Belgium', 'affiliate-toolkit-starter' ),
						'ae'     => esc_html__( 'Amazon United Arab Emirates', 'affiliate-toolkit-starter' ),
						'nl'     => esc_html__( 'Amazon Netherlands', 'affiliate-toolkit-starter' ),
						'pl'     => esc_html__( 'Amazon Poland', 'affiliate-toolkit-starter' ),
					);
					//'cn'     => esc_html__( 'Amazon China', ATKP_PLUGIN_PREFIX ),

					foreach ( $locations as $value => $name ) {
						if ( $value == ATKPTools::get_post_setting( $post->ID, ATKP_SHOP_POSTTYPE . '_access_website' ) ) {
							$sel = ' selected';
						} else {
							$sel = '';
						}


						echo '<option value="' . esc_attr( $value ) . '"' . esc_attr( $sel ) . '>' . esc_html__( $name, 'affiliate-toolkit-starter' ) . '</option>';
					} ?>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_amz_access_tracking_id') ?>">
	                <?php echo esc_html__( 'Amazon Tracking ID', 'affiliate-toolkit-starter' ) ?> <span
                            class="description"><?php echo esc_html__( '(required)', 'affiliate-toolkit-starter' ) ?></span>
                </label>
            </th>
            <td>
                <input type="text" id="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_amz_access_tracking_id') ?>"
                       name="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_amz_access_tracking_id') ?>"
                       value="<?php echo esc_attr(ATKPTools::get_post_setting( $post->ID, ATKP_SHOP_POSTTYPE . '_access_tracking_id' )); ?>">
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="<?php echo esc_attr( ATKP_SHOP_POSTTYPE . '_languages_of_preference' ) ?>">
					<?php echo esc_html__( 'Languages Of Preference', 'affiliate-toolkit-starter' ) ?>
                </label>

            </th>
            <td>
                <input type="text" id="<?php echo esc_attr( ATKP_SHOP_POSTTYPE . '_languages_of_preference' ) ?>"
                       name="<?php echo esc_attr( ATKP_SHOP_POSTTYPE . '_languages_of_preference' ) ?>"
                       value="<?php echo esc_attr( ATKPTools::get_post_setting( $post->ID, ATKP_SHOP_POSTTYPE . '_languages_of_preference' ) ); ?>">
				<?php ATKPTools::display_helptext( 'You can set an list of languages you want to receive (comma separated). You can find the valid languages for each marketplace <a href="https://webservices.amazon.de/paapi5/documentation/locale-reference.html" target="_blank">here</a>.' ) ?>

            </td>
        </tr>
					</table>
				</div>
			</td>
		</tr>

		<!-- No-API Configuration Section -->
		<tr class="atkp-config-section" id="atkp-config-noapi">
			<td colspan="2">
				<?php if ( defined( 'ATKP_AMAZNOAPI_ITEM_ID' ) && ATKP_LicenseController::get_module_license_status( 'amaznoapi' ) == 'valid' ) { ?>
					<div class="atkp-config-section">
						<h4>⚡ <?php echo esc_html__( 'No-API Mode Configuration', 'affiliate-toolkit-starter' ) ?></h4>

						<div style="background: #f0f8fa; border-left: 3px solid #54b9ca; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
							<p style="margin: 0 0 10px 0; font-weight: 600; color: #005162;">
								✓ <?php echo esc_html__( 'No-API Extension Active', 'affiliate-toolkit-starter' ) ?>
							</p>
							<p style="margin: 0; font-size: 13px; color: #666;">
								<?php echo esc_html__( 'Product data will be retrieved without Amazon API limits based on your license plan.', 'affiliate-toolkit-starter' ) ?>
							</p>
						</div>

						<table class="form-table">
							<tr>
								<th scope="row">
									<label for="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_sitestripe') ?>">
										<?php echo esc_html__( 'Operating Mode', 'affiliate-toolkit-starter' ) ?>
									</label>
								</th>
								<td>
									<select id="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_sitestripe') ?>"
											style="width: 100%; max-width: 500px;">
										<?php
										$noapi_selected = ATKPTools::get_post_setting( $post->ID, ATKP_SHOP_POSTTYPE . '_sitestripe' );
										if (empty($noapi_selected) || $noapi_selected == 1) $noapi_selected = 2; // Default to Always Active for NoAPI mode

										echo '<option value="2" ' . ( $noapi_selected == 2 ? 'selected' : '' ) . '>' . esc_html__( 'Always Active - Use No-API for all requests', 'affiliate-toolkit-starter' ) . '</option>';
										echo '<option value="3" ' . ( $noapi_selected == 3 ? 'selected' : '' ) . '>' . esc_html__( 'Smart Mode - No-API as fallback (API key required)', 'affiliate-toolkit-starter' ) . '</option>';
										?>
									</select>
									<?php ATKPTools::display_helptext( esc_html__( 'Always Active: Uses only No-API. Smart Mode: Tries Amazon API first, uses No-API as fallback (requires API credentials).', 'affiliate-toolkit-starter' ) ) ?>
								</td>
							</tr>

							<tr id="atkp-noapi-smart-mode-info" style="display: none;">
								<td colspan="2">
									<div style="background: #fff3e0; border-left: 3px solid #ff9800; padding: 15px; border-radius: 4px;">
										<p style="margin: 0; font-size: 13px; color: #e65100;">
											<strong>⚡ <?php echo esc_html__( 'Smart Mode requires Amazon API credentials', 'affiliate-toolkit-starter' ) ?></strong><br>
											<?php echo esc_html__( 'Please switch to "Amazon Product Advertising API" mode above and enter your API credentials. Then you can use Smart Mode as fallback.', 'affiliate-toolkit-starter' ) ?>
										</p>
									</div>
								</td>
							</tr>

							<tr>
								<th scope="row">
									<label for="<?php echo esc_attr( ATKP_SHOP_POSTTYPE . '_asindataapikey' ) ?>">
										<?php echo esc_html__( 'ASIN Data API Key', 'affiliate-toolkit-starter' ) ?><br>
										<span class="description"><?php echo esc_html__( '(optional)', 'affiliate-toolkit-starter' ) ?></span>
									</label>
								</th>
								<td>
									<input type="text"
										   id="<?php echo esc_attr( ATKP_SHOP_POSTTYPE . '_asindataapikey' ) ?>"
										   name="<?php echo esc_attr( ATKP_SHOP_POSTTYPE . '_asindataapikey' ) ?>"
										   value="<?php echo esc_attr( ATKPTools::get_post_setting( $post->ID, ATKP_SHOP_POSTTYPE . '_asindataapikey' ) ); ?>"
										   placeholder="<?php echo esc_attr__( 'Enter your ASIN Data API key...', 'affiliate-toolkit-starter' ) ?>"
										   style="width: 100%; max-width: 500px;">
									<?php ATKPTools::display_helptext( esc_html__( 'No-API requests are included in your affiliate-toolkit license. For unlimited requests, you can purchase an external ASIN Data API key.', 'affiliate-toolkit-starter' ) . ' <a href="https://trajectdata.com/ecommerce/asin-data-api/pricing/" target="_blank">' . esc_html__( 'Get API Key', 'affiliate-toolkit-starter' ) . '</a>' ) ?>
								</td>
							</tr>

							<tr>
								<th scope="row">
									<?php echo esc_html__( 'Star Ratings', 'affiliate-toolkit-starter' ) ?>
								</th>
								<td>
									<label style="display: flex; align-items: flex-start; gap: 8px;">
										<input type="checkbox"
											   id="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_amz_load_customer_reviews') ?>"
											   name="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_amz_load_customer_reviews') ?>"
											   value="1"
											   <?php echo checked( 1, ATKPTools::get_post_setting( $post->ID, ATKP_SHOP_POSTTYPE . '_load_customer_reviews' ), true ); ?>>
										<span>
											<?php echo esc_html__( 'Load star ratings from Amazon webpages', 'affiliate-toolkit-starter' ) ?>
											<span style="color: #dc3232; font-weight: 600;"><?php echo esc_html__( '(Not Recommended)', 'affiliate-toolkit-starter' ) ?></span>
											<br>
											<span style="font-size: 13px; color: #666;">
												<?php echo esc_html__( 'Reads star ratings directly from Amazon. Not officially allowed - use at your own risk.', 'affiliate-toolkit-starter' ) ?>
											</span>
										</span>
									</label>
								</td>
							</tr>
						</table>
					</div>
				<?php } else { ?>
					<div class="atkp-config-section">
						<div style="background: #fff3e0; border-left: 3px solid #ff9800; padding: 20px; border-radius: 4px;">
							<h4 style="margin: 0 0 15px 0; color: #e65100;">
								⚠️ <?php echo esc_html__( 'No-API Extension Required', 'affiliate-toolkit-starter' ) ?>
							</h4>
							<p style="margin: 0 0 15px 0; font-size: 14px; color: #666;">
								<?php echo esc_html__( 'To use No-API Mode, you need to activate the "affiliate-toolkit - Amazon No API Mode" extension.', 'affiliate-toolkit-starter' ) ?>
							</p>

							<?php
							// Show package information
							if (file_exists(dirname(__FILE__) . '/../views/noapi-package-info.php')) {
								include dirname(__FILE__) . '/../views/noapi-package-info.php';
							}
							?>
						</div>
					</div>
				<?php } ?>
			</td>
		</tr>

		<script>
			jQuery(document).ready(function($) {
				// Show/hide Smart Mode info
				function toggleSmartModeInfo() {
					var mode = $('#<?php echo esc_js(ATKP_SHOP_POSTTYPE . '_sitestripe'); ?>').val();
					if (mode == '3') {
						$('#atkp-noapi-smart-mode-info').show();
					} else {
						$('#atkp-noapi-smart-mode-info').hide();
					}
				}

				$('#<?php echo esc_js(ATKP_SHOP_POSTTYPE . '_sitestripe'); ?>').on('change', toggleSmartModeInfo);
				toggleSmartModeInfo();
			});
		</script>


        <tr>
                <th colspan="2">
                    <style>
                        .atkp-api-notice {
                            background: #fff;
                            border-left: 4px solid #54b9ca;
                            padding: 20px;
                            margin: 15px 0;
                            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
                        }

                        .atkp-api-notice h3 {
                            margin-top: 0;
                            color: #23282d;
                            font-size: 16px;
                            font-weight: 600;
                        }

                        .atkp-api-limits {
                            background: #f0f8fa;
                            border-radius: 4px;
                            padding: 15px;
                            margin: 15px 0;
                            border-left: 3px solid #54b9ca;
                        }

                        .atkp-api-limits h4 {
                            margin: 0 0 10px 0;
                            color: #005162;
                            font-size: 14px;
                            font-weight: 600;
                        }

                        .atkp-api-limits ul {
                            margin: 5px 0;
                            padding-left: 20px;
                        }

                        .atkp-api-limits li {
                            margin: 5px 0;
                            line-height: 1.6;
                        }

                        .atkp-alert-danger {
                            background: #fef5f5;
                            border: 1px solid #dc3232;
                            border-radius: 4px;
                            padding: 12px 15px;
                            margin: 15px 0;
                            color: #dc3232;
                            font-weight: 500;
                        }

                        .atkp-noapi-section {
                            background: #f0f8fa;
                            border: 2px solid #54b9ca;
                            border-radius: 6px;
                            padding: 20px;
                            margin: 20px 0;
                        }

                        .atkp-noapi-section h3 {
                            margin: 0 0 15px 0;
                            color: #005162;
                            font-size: 16px;
                            font-weight: 600;
                        }

                        .atkp-packages {
                            display: grid;
                            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                            gap: 15px;
                            margin: 15px 0;
                        }

                        .atkp-package {
                            background: #fff;
                            border: 1px solid #ddd;
                            border-radius: 4px;
                            padding: 15px;
                            text-align: center;
                        }

                        .atkp-package-name {
                            font-weight: 600;
                            font-size: 15px;
                            color: #23282d;
                            margin-bottom: 8px;
                        }

                        .atkp-package-limit {
                            font-size: 24px;
                            font-weight: bold;
                            color: #005162;
                            margin: 10px 0;
                        }

                        .atkp-package-period {
                            font-size: 12px;
                            color: #646970;
                        }

                        .atkp-package-disabled {
                            background: #f6f7f7;
                            border-color: #c3c4c7;
                        }

                        .atkp-package-disabled .atkp-package-limit {
                            color: #999;
                        }

                        .atkp-cta-buttons {
                            margin-top: 20px;
                            display: flex;
                            gap: 10px;
                            flex-wrap: wrap;
                        }

                        .atkp-btn-primary {
                            background: #005162;
                            color: #fff;
                            padding: 10px 20px;
                            border-radius: 4px;
                            text-decoration: none;
                            font-weight: 500;
                            display: inline-block;
                            transition: all 0.3s ease;
                            border: none;
                            box-shadow: 0 2px 4px rgba(0, 81, 98, 0.2);
                        }

                        .atkp-btn-primary:hover {
                            background: #003a48;
                            color: #fff;
                            box-shadow: 0 4px 8px rgba(0, 81, 98, 0.3);
                            transform: translateY(-1px);
                        }

                        .atkp-btn-secondary {
                            background: #fff;
                            color: #005162;
                            padding: 10px 20px;
                            border: 2px solid #54b9ca;
                            border-radius: 4px;
                            text-decoration: none;
                            font-weight: 500;
                            display: inline-block;
                            transition: all 0.3s ease;
                        }

                        .atkp-btn-secondary:hover {
                            background: #005162;
                            color: #fff;
                            border-color: #005162;
                        }
                    </style>
                </th>
            </tr>

		<!-- API Requirements Info - Only shown in API mode -->
		<tr class="atkp-config-section atkp-info-section" id="atkp-info-api">
			<th colspan="2">
				<div class="atkp-api-notice">
					<h3>📊 <?php echo esc_html__( 'Amazon Product Advertising API (PA-API 5.0) Requirements', 'affiliate-toolkit-starter' ) ?></h3>

					<div class="atkp-api-limits">
						<h4><?php echo esc_html__( '🚀 Initial Limits (First 30 days)', 'affiliate-toolkit-starter' ) ?></h4>
						<ul>
							<li><?php echo esc_html__( 'Up to 1 request per second (1 TPS)', 'affiliate-toolkit-starter' ) ?></li>
							<li><?php echo esc_html__( 'Maximum of 8,640 requests per day (8,640 TPD)', 'affiliate-toolkit-starter' ) ?></li>
						</ul>
					</div>

					<div class="atkp-api-limits">
						<h4><?php echo esc_html__( '💰 Revenue-based Limits (After 30 days)', 'affiliate-toolkit-starter' ) ?></h4>
						<ul>
							<li><?php echo esc_html__( '1 TPD for every $0.05 of shipped item revenue', 'affiliate-toolkit-starter' ) ?></li>
							<li><?php echo esc_html__( '1 TPS for every $4,320 of shipped item revenue (maximum 10 TPS)', 'affiliate-toolkit-starter' ) ?></li>
						</ul>
					</div>

					<div class="atkp-alert-danger">
						⚠️ <?php echo esc_html__( 'Your account will lose API access if it has not generated qualified referring sales for a consecutive 30-day period. Access will be restored within 2 days after referred sales are shipped.', 'affiliate-toolkit-starter' ) ?>
					</div>
				</div>
			</th>
		</tr>


        <tr>
            <th scope="row">
                <label for="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_amz_small_image_size') ?>">
	                <?php echo esc_html__( 'Small image size', 'affiliate-toolkit-starter' ) ?>
                </label>
            </th>
            <td>
                <input type="number" min="0" max="1000" placeholder="75" id="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_amz_small_image_size') ?>"
                       name="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_amz_small_image_size') ?>"
                       value="<?php echo esc_attr( ATKPTools::get_post_setting( $post->ID, ATKP_SHOP_POSTTYPE . '_amz_small_image_size' ) ); ?>"> <?php echo esc_html__( 'px', 'affiliate-toolkit-starter' ) ?>
	            <?php ATKPTools::display_helptext( esc_html__( 'Amazon offers flexible image sizes. If you wan\'t to override the default size of 75px you can change it here. Changes for already imported products are visible after the cache update.', 'affiliate-toolkit-starter' ) ) ?>
            </td>
        </tr>

        <tr>
            <th scope="row">
                <label for="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_amz_medium_image_size') ?>">
	                <?php echo esc_html__( 'Medium image size', 'affiliate-toolkit-starter' ) ?>
                </label>
            </th>
            <td>
                <input type="number" min="0" max="1000" placeholder="160" id="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_amz_medium_image_size') ?>"
                       name="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_amz_medium_image_size') ?>"
                       value="<?php echo esc_attr( ATKPTools::get_post_setting( $post->ID, ATKP_SHOP_POSTTYPE . '_amz_medium_image_size' ) ); ?>"> <?php echo esc_html__( 'px', 'affiliate-toolkit-starter' ) ?>
	            <?php ATKPTools::display_helptext( esc_html__( 'Amazon offers flexible image sizes. If you wan\'t to override the default size of 160px you can change it here. Changes for already imported products are visible after the cache update.', 'affiliate-toolkit-starter' ) ) ?>
            </td>
        </tr>



        <tr>
            <th scope="row">
                <label for="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_amz_onlynew') ?>">
	                <?php echo esc_html__( 'Product condition:', 'affiliate-toolkit-starter' ) ?>
                </label>
            </th>
            <td>


                <select id="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_amz_onlynew') ?>"
                        name="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_amz_onlynew') ?>" style="width:300px">
					<?php
					$selected = ATKPTools::get_post_setting( $post->ID, ATKP_SHOP_POSTTYPE . '_onlynew' );

					echo '<option value="" ' . ( $selected == '' || $selected == '0' ? 'selected' : '' ) . ' >' . esc_html__( 'Any', 'affiliate-toolkit-starter' ) . '</option>';

					echo '<option value="1" ' . ( $selected == '1' ? 'selected' : '' ) . '>' . esc_html__( 'New', 'affiliate-toolkit-starter' ) . '</option>';
					echo '<option value="2" ' . ( $selected == '2' ? 'selected' : '' ) . '>' . esc_html__( 'Used', 'affiliate-toolkit-starter' ) . '</option>';
					echo '<option value="3" ' . ( $selected == '3' ? 'selected' : '' ) . '>' . esc_html__( 'Collectible', 'affiliate-toolkit-starter' ) . '</option>';
					echo '<option value="4" ' . ( $selected == '4' ? 'selected' : '' ) . '>' . esc_html__( 'Refurbished', 'affiliate-toolkit-starter' ) . '</option>';


					?>

                </select>
	            <?php ATKPTools::display_helptext( esc_html__( 'You can filter if you only wan\'t prices for special conditions of products. By default you receive all offers. If you only wan\'t to show "used" products on your website you can select a different option.', 'affiliate-toolkit-starter' ) ) ?>
            </td>
        </tr>

        <tr>
            <th scope="row">
                <label for="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_amz_seconds_wait') ?>">
	                <?php echo esc_html__( 'Wait x seconds before sending the request', 'affiliate-toolkit-starter' ) ?>
                </label>
            </th>
            <td>
                <input type="number" min="0" max="20" id="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_amz_seconds_wait') ?>" placeholder="1"
                       name="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_amz_seconds_wait') ?>"
                       value="<?php echo esc_attr( ATKPTools::get_post_setting( $post->ID, ATKP_SHOP_POSTTYPE . '_seconds_wait' ) ); ?>"/> <?php echo esc_html__( 'seconds', 'affiliate-toolkit-starter' ) ?>
	            <?php ATKPTools::display_helptext( esc_html__( 'In normal cases you don\'t need to change to a higher limit. By default the API is waiting one second..', 'affiliate-toolkit-starter' ) ) ?>
            </td>
        </tr>



        <tr>
            <th scope="row">

            </th>
            <td>
                <input type="checkbox" id="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_amz_load_variations') ?>"
                       name="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_amz_load_variations') ?>"
                       value="1" <?php echo checked( 1, ATKPTools::get_post_setting( $post->ID, ATKP_SHOP_POSTTYPE . '_load_variations' ), true ); ?>>

                <label for="<?php echo esc_attr(ATKP_SHOP_POSTTYPE . '_amz_load_variations') ?>">
	                <?php echo esc_html__( 'Load variations for products', 'affiliate-toolkit-starter' ) ?>
                </label>
	            <?php ATKPTools::display_helptext( esc_html__( 'If you wan\'t to retrieve also other colors or variations for one product (e.g. Shirts) you can enable this option but this cost one extra request per product', 'affiliate-toolkit-starter' ) ) ?>
            </td>
        </tr>
		<?php

	}

	public function get_shops( $post_id, $allshops = false ) {

		$subshops = ATKPTools::get_post_setting( $post_id, ATKP_SHOP_POSTTYPE . '_default_shops' );

		if ( $subshops == null || count( $subshops ) > 1 ) {
			$subshops = $this->get_defaultshops( $post_id );
		}

		foreach ( $subshops as $subshop ) {
			$subshop->shopid    = $post_id;
			$subshop->programid = '';

			$subshop->logourl      = $subshop->customlogourl == '' ? $subshop->logourl : $subshop->customlogourl;
			$subshop->smalllogourl = $subshop->customsmalllogourl == '' ? $subshop->smalllogourl : $subshop->customsmalllogourl;
			$subshop->title        = $subshop->customtitle == '' ? $subshop->title : $subshop->customtitle;

			$subshop->enabled = true;
		}

		return $subshops;
	}

	/* @var Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\api\DefaultApi $helper */
	private $helper = null;
	private $enable_ssl = false;
	private $country = '';
	private $load_customer_reviews = false;
	private $associateTag = '';
	/**
	 * @var string[]
	 */
	private $languages_of_preference = '';
	private $accessKey = '';
	private $shopid = '';

	private $smallimagesize = 0;
	private $mediumimagesize = 0;

	public $sitetripemode = 0;
	private $usev5 = 0;
	private $load_variations = 0;
	private $onlynew = 0;
    private $seconds_wait = 0;

	private $asindataapikey = '';


	private function checklogon_v5( $access_website, $access_key, $access_secret_key, $access_tracking_id ) {

		$this->helper = $this->get_api_instance( $access_key, $access_secret_key, $access_website );
	}

	public function checklogon( $shop ) {
		$this->shopid                = $shop->id;
		$this->accessKey             = $access_key = ATKPTools::get_post_setting( $shop->id, ATKP_SHOP_POSTTYPE . '_access_key' );
		$access_secret_key           = ATKPTools::get_post_setting( $shop->id, ATKP_SHOP_POSTTYPE . '_access_secret_key' );
		$this->country               = $access_website = ATKPTools::get_post_setting( $shop->id, ATKP_SHOP_POSTTYPE . '_access_website' );
		$this->associateTag          = $access_tracking_id = ATKPTools::get_post_setting( $shop->id, ATKP_SHOP_POSTTYPE . '_access_tracking_id' );

		$lang                          = ATKPTools::get_post_setting( $shop->id, ATKP_SHOP_POSTTYPE . '_languages_of_preference' );
		$this->languages_of_preference = $lang != '' ? explode( ',', $lang ) : null;

		$this->asindataapikey = ATKPTools::get_post_setting( $shop->id, ATKP_SHOP_POSTTYPE . '_asindataapikey' );

		$this->load_variations       = ATKPTools::get_post_setting( $shop->id, ATKP_SHOP_POSTTYPE . '_load_variations' );
		$this->enable_ssl            = true;
		$this->onlynew =  ATKPTools::get_post_setting( $shop->id, ATKP_SHOP_POSTTYPE . '_onlynew' );

		$this->seconds_wait = ATKPTools::get_post_setting( $shop->id, ATKP_SHOP_POSTTYPE . '_seconds_wait' );
		if ( $this->seconds_wait <= 0 ) {
			$this->seconds_wait = 1;
		}

		if ( ATKP_LicenseController::get_module_license_status( 'amaznoapi' ) == 'valid' &&
		     ATKP_LicenseController::get_module_license( 'amaznoapi' ) != '' ) {
			$this->sitetripemode         = ATKPTools::get_post_setting( $shop->id, ATKP_SHOP_POSTTYPE . '_sitestripe' );
			$this->load_customer_reviews = ATKPTools::get_post_setting( $shop->id, ATKP_SHOP_POSTTYPE . '_load_customer_reviews' );
		}


		$this->smallimagesize  = ATKPTools::get_post_setting( $shop->id, ATKP_SHOP_POSTTYPE . '_amz_small_image_size' );
		$this->mediumimagesize = ATKPTools::get_post_setting( $shop->id, ATKP_SHOP_POSTTYPE . '_amz_medium_image_size' );

		if ( $this->smallimagesize <= 0 ) {
			$this->smallimagesize = 75;
		}
		if ( $this->mediumimagesize <= 0 ) {
			$this->mediumimagesize = 160;
		}

		//http://ws-eu.amazon-adsystem.com/widgets/q?ServiceVersion=20070822&OneJS=1&Operation=GetAdHtml&MarketPlace=DE&source=ss&ref=as_ss_li_til&ad_type=product_link&tracking_id=werbeanzeige1-21&language=de_DE&marketplace=amazon&region=DE&placement=B01MR8IST0&asins=B01MR8IST0&linkId=2b2c154e99d12d52b2eeedaca502173f&show_border=true&link_opens_in_new_window=true
		//http://ws-eu.amazon-adsystem.com/widgets/q?ServiceVersion=20070822&OneJS=1&Operation=GetAdHtml&MarketPlace=DE&source=ss&ref=as_ss_li_til&ad_type=product_link&tracking_id=werbeanzeige1-21&language=de_DE&marketplace=amazon&region=DE&placement=B01MR8IST0&asins=B01MR8IST0&linkId=f9c12eaff04df7a156ab2470af8795b6&show_border=false&link_opens_in_new_window=true
		//https://www.amazon.de/Anbernic-Handheld-Spielkonsole-Konsole-Retro/dp/B079KC8Y4Z/ref=as_li_ss_il?pf_rd_p=bf2f9e9c-e5d5-4935-a04d-fda59481ccaa&pd_rd_wg=o3sJU&pf_rd_r=2XY13YPN803RR4ST4DQ6&ref_=pd_gw_cr_cartx&pd_rd_w=VtV3b&pd_rd_r=9a23560a-0d57-4b39-bb61-6df20e25109c&linkCode=li3&tag=werbeanzeige1-21&linkId=3ece46f52aef6db73c170a5784594205&language=de_DE" target="_blank"><img border="0" src="//ws-eu.amazon-adsystem.com/widgets/q?_encoding=UTF8&ASIN=B079KC8Y4Z&Format=_SL250_&ID=AsinImage&MarketPlace=DE&ServiceVersion=20070822&WS=1&tag=werbeanzeige1-21&language=de_DE" ></a><img src="https://ir-de.amazon-adsystem.com/e/ir?t=werbeanzeige1-21&language=de_DE&l=li3&o=3&a=B079KC8Y4Z
		//<a href="https://www.amazon.de/Anbernic-Handheld-Spielkonsole-Konsole-Retro/dp/B079KC8Y4Z/ref=as_li_ss_il?pf_rd_p=bf2f9e9c-e5d5-4935-a04d-fda59481ccaa&pd_rd_wg=o3sJU&pf_rd_r=2XY13YPN803RR4ST4DQ6&ref_=pd_gw_cr_cartx&pd_rd_w=VtV3b&pd_rd_r=9a23560a-0d57-4b39-bb61-6df20e25109c&linkCode=li3&tag=werbeanzeige1-21&linkId=3ece46f52aef6db73c170a5784594205&language=de_DE" target="_blank"><img border="0" src="//ws-eu.amazon-adsystem.com/widgets/q?_encoding=UTF8&ASIN=B079KC8Y4Z&Format=_SL250_&ID=AsinImage&MarketPlace=DE&ServiceVersion=20070822&WS=1&tag=werbeanzeige1-21&language=de_DE" ></a><img src="https://ir-de.amazon-adsystem.com/e/ir?t=werbeanzeige1-21&language=de_DE&l=li3&o=3&a=B079KC8Y4Z" width="1" height="1" border="0" alt="" style="border:none !important; margin:0px !important;" />

		if ( $access_tracking_id == '' ) {
			$access_tracking_id = 'empty';
		}

		$this->checklogon_v5( $access_website, $access_key, $access_secret_key, $access_tracking_id );

	}

	/**
	 * Sets itemIdType
	 *
	 * @param \Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\BrowseNodeAncestor $browsenodeancestor
	 *
	 * @return string $result
	 */
	private function getBrowseNodeTreeRec( $browsenodeancestor, &$nodes ) {
		if ( $browsenodeancestor == null ) {
			return '';
		}

		$nodes[ $browsenodeancestor->getId() ] = $browsenodeancestor->getDisplayName();

		if ( $browsenodeancestor->getAncestor() != null ) {
			$this->getBrowseNodeTreeRec( $browsenodeancestor->getAncestor(), $nodes );
		}
	}

	private function retrieve_browsenodes_v5( $keyword ) {

		$nodes = array();
		$items = null;
		try {
			$searchItemsRequest = new Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\SearchItemsRequest();
			$searchItemsRequest->setSearchIndex( 'All' );
			$searchItemsRequest->setKeywords( $keyword );
			$searchItemsRequest->setItemCount( 10 );
			$searchItemsRequest->setPartnerTag( $this->associateTag );
			if ( $this->languages_of_preference != null ) {
				$searchItemsRequest->setLanguagesOfPreference( $this->languages_of_preference );
			}
			$searchItemsRequest->setPartnerType( Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\PartnerType::ASSOCIATES );
			$searchItemsRequest->setResources(
				\Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\SearchItemsResource::getAllowableEnumValues()
			);
			$searchItemsRequest->setItemPage( 1 );

			$searchItemsResponse = $this->sendSearchRequest( $searchItemsRequest );

			if ( $searchItemsResponse->getSearchResult() != null && $searchItemsResponse->getSearchResult()->getItems() != null ) {
				$items = $searchItemsResponse->getSearchResult()->getItems();
			}

		} catch ( Amazon\ProductAdvertisingAPI\v1\ApiException $exception ) {
			$check = "API-Error: " . $exception->getCode() . " " . $exception->getMessage();

			if ( $exception->getResponseObject() instanceof Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\ProductAdvertisingAPIClientException ) {
				$errors = $exception->getResponseObject()->getErrors();
				foreach ( $errors as $error ) {
					$check = "Response-Error: " . $error->getCode() . " " . $error->getMessage();
				}
			} else {
				$check .= "Error response body: " . $exception->getResponseBody();
			}
		} catch ( Exception $exception ) {
			$check = "Error Message: " . $exception->getMessage(); //. ' ' . $exception->getTraceAsString();
		}

		if ( $items != null ) {
			foreach ( $items as $item ) {
				foreach ( $item->getBrowseNodeInfo()->getBrowseNodes() as $bnw ) {
					$this->getBrowseNodeTreeRec( $bnw->getAncestor(), $nodes );
				}
			}
		}

		return $nodes;
	}


	public function retrieve_browsenodes( $keyword ) {
		if ( $this->helper == null ) {
			throw new Exception( 'checklogon required' );
		}

			$nodes = $this->retrieve_browsenodes_v5( $keyword );


		$newNodes = array();

		foreach ( $nodes as $node => $value ) {
			if ( ! array_key_exists( $node, $newNodes ) ) {
				$newNodes[ $node ] = $value;
			}
		}

		return $newNodes;
	}

	private function retrieve_recursive_browsenodes( $parentBrowseNode ) {
		$nodes = array();
		if ( isset( $parentBrowseNode->Ancestors ) ) {
			foreach ( $parentBrowseNode->Ancestors as $browsenode ) {
				if ( ! isset( $browsenode->Name ) || ! is_string( $browsenode->Name ) ) {
					continue;
				}

				$nodes[ $browsenode->BrowseNodeId ] = $browsenode->Name;

				foreach ( $this->retrieve_recursive_browsenodes( $browsenode ) as $node => $value ) {
					$nodes[ $node ] = $value;
				}

				//array_push($nodes, $this->RecursiveBrowseNodes($browsenode));
			}
		}

		return $nodes;
	}


	private function quick_search_v5( $keyword, $searchType, $pagination ) {
		$products = new atkp_search_resp();
		$maxCount = 10;

		if ( $this->sitetripemode == 2) {

			if ( $searchType == 'product' ) {
				$products = $this->search_sitestripeproduct( $keyword, $searchType, $pagination );
			} else {
				$products->message = esc_html__( 'Search and import not supported. You enabled "sitestripe mode" in your amazon shop.', 'affiliate-toolkit-starter' );
			}
            return $products;
		}

		$items = array();
		try {
			//$searchType == 'ean'
			if ( $searchType == 'asin' || $searchType == 'articlenumber' ) {
				$getItemsRequest = new Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\GetItemsRequest();
				$getItemsRequest->setItemIds( explode( ',', $keyword ) );
				$getItemsRequest->setItemIdType( ( $searchType == 'ean' ? 'EAN' : 'ASIN' ) );
				$getItemsRequest->setPartnerTag( $this->associateTag );
				if ( $this->languages_of_preference != null )
					$getItemsRequest->setLanguagesOfPreference( $this->languages_of_preference);
				$getItemsRequest->setPartnerType( \Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\PartnerType::ASSOCIATES );
				$getItemsRequest->setResources(
					\Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\GetItemsResource::getAllowableEnumValues()
				);

				$getItemsResponse = $this->sendGetItemsRequest( $getItemsRequest );

				if ( $getItemsResponse->getItemsResult() != null && $getItemsResponse->getItemsResult()->getItems() != null ) {
					$items = $getItemsResponse->getItemsResult()->getItems();
				}


			} else {
				$searchItemsRequest = new Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\SearchItemsRequest();
				$searchItemsRequest->setSearchIndex( 'All' );
				$searchItemsRequest->setKeywords( $keyword );
				$searchItemsRequest->setItemCount( $maxCount );
				$searchItemsRequest->setPartnerTag( $this->associateTag );
				if ( $this->languages_of_preference != null )
					$searchItemsRequest->setLanguagesOfPreference( $this->languages_of_preference);
				$searchItemsRequest->setPartnerType( Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\PartnerType::ASSOCIATES );
				$searchItemsRequest->setResources(
					\Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\SearchItemsResource::getAllowableEnumValues()
				);
				$searchItemsRequest->setItemPage( $pagination );


				$searchItemsResponse = $this->sendSearchRequest( $searchItemsRequest );

				if ( $searchItemsResponse->getSearchResult() != null ) {
					$products->pagecount = ceil( floatval( $searchItemsResponse->getSearchResult()->getTotalResultCount() ) / floatval( $maxCount ) );
					$products->total     = intval( $searchItemsResponse->getSearchResult()->getTotalResultCount() );
				}
				$products->currentpage = intval( $pagination );


				if ( $searchItemsResponse->getSearchResult() != null && $searchItemsResponse->getSearchResult()->getItems() != null ) {
					$items = $searchItemsResponse->getSearchResult()->getItems();
				}
			}
		} catch ( Amazon\ProductAdvertisingAPI\v1\ApiException $exception ) {
			$check = "API-Error: " . $exception->getCode() . " " . $exception->getMessage();

			if ( $exception->getResponseObject() instanceof Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\ProductAdvertisingAPIClientException ) {
				$errors = $exception->getResponseObject()->getErrors();
				foreach ( $errors as $error ) {
					$check = "Response-Error: " . $error->getCode() . " " . $error->getMessage();
				}
			} else {
				$check .= "Error response body: " . $exception->getResponseBody();
			}
		} catch ( Exception $exception ) {
			$check = "Error Message: " . $exception->getMessage(); //. ' ' . $exception->getTraceAsString();
		}

		if ( ! empty( $check ) ) {
			throw new Exception( esc_html__( $check, 'affiliate-toolkit-starter' ) );
		}


		foreach ( $items as $result ) {
			if ( $result->getASIN() != null ) {
				$product = array();

				if ( $result->getImages() != null && $result->getImages()->getPrimary() != null ) {
					$product['imageurl'] = $this->checkimageurl( $result->getImages()->getPrimary()->getSmall()->getURL(), 'small' );
				}

				$product['articlenumber'] = $product['asin'] = $result->getASIN();
				//var_dump($result->getExternalIds());exit;

				$ean_full = '';
				if ( $result->getItemInfo()->getExternalIds() != null && $result->getItemInfo()->getExternalIds()->getEANs() != null ) {
					foreach ( $result->getItemInfo()->getExternalIds()->getEANs()->getDisplayValues() as $ean ) {
						if ( $ean_full != '' ) {
							$ean_full .= ',';
						}

						$ean_full .= $ean;
					}
				}
				$product['ean'] = $ean_full;

				if ( $result->getDetailPageURL() != null ) {
					$product['producturl'] = $result->getDetailPageURL();
				}
				if ( $result->getItemInfo() != null && $result->getItemInfo()->getTitle() != null && $result->getItemInfo()->getTitle()->getDisplayValue() != null ) {
					$product['title'] = htmlspecialchars( $result->getItemInfo()->getTitle()->getDisplayValue() );
				}
			if ( $result->getOffersV2() != null ) {
				foreach ( $result->getOffersV2()->getListings() as $listing ) {
					if ( $listing->getAvailability() != null && $listing->getAvailability()->getMessage() != null ) {
						$product['availability'] = $listing->getAvailability()->getMessage();
					}

					if ( $listing->getPrice() != null ) {
						// OffersV2 hat eine verschachtelte Money-Struktur
						$price = $listing->getPrice();

						// SavingBasis für listprice
						if ( $price->getSavingBasis() != null ) {
							$savingBasis = $price->getSavingBasis();
							// Prüfe ob SavingBasis das neue Money-Objekt hat
							if ( $savingBasis->getMoney() != null ) {
								$product['listprice'] = $savingBasis->getMoney()->getDisplayAmount();
								$product['listpricefloat'] = $savingBasis->getMoney()->getAmount();
							}
						} else {
							$product['listprice'] = '';
							$product['listpricefloat'] = 0;
						}

						// Preis aus dem Money-Objekt holen
						if ( $price->getMoney() != null ) {
							$product['saleprice'] = $price->getMoney()->getDisplayAmount();
							$product['salepricefloat'] = $price->getMoney()->getAmount();
						}

						// PricePerUnit (baseprice) - nur OffersV2 Struktur
						if ( $price->getPricePerUnit() != null ) {
							$pricePerUnit = $price->getPricePerUnit();
							if ( $pricePerUnit->getAmount() != null ) {
								$product['basepricefloat'] = $pricePerUnit->getAmount();

								// Extrahiere Preis und Einheit aus dem displayAmount
								if ( $pricePerUnit->getDisplayAmount() != null ) {
									$parts = explode(' / ', $pricePerUnit->getDisplayAmount());
									$product['baseprice'] = trim($parts[0]);
									if ( count($parts) > 1 ) {
										$product['baseunit'] = trim($parts[1]);
									}
								}
							}
						}

						// Savings
						if ( $price->getSavings() != null ) {
							$savings = $price->getSavings();
							if ( $savings->getPercentage() != null ) {
								$product['percentagesaved'] = $savings->getPercentage();
							}
							if ( $savings->getMoney() != null ) {
								$product['amountsaved'] = $savings->getMoney()->getDisplayAmount();
								$product['amountsavedfloat'] = $savings->getMoney()->getAmount();
							}
						}

						break;
					}
				}
			}


				$description = '';
				if ( $result->getItemInfo()->getFeatures() != null && $result->getItemInfo()->getFeatures()->getDisplayValues() != null ) {
					$description = implode( '<br />', $result->getItemInfo()->getFeatures()->getDisplayValues() );
				}

				$product['features'] = $description != '' && strlen( $description ) > 350 ? substr( $description, 0, 350 ) : $description;


				//$product['availability'] = $result->Offers->Offer->OfferListing->Availability;

				array_push( $products->products, $product );
			}
		}


		return $products;
	}

	public function quick_search( $keyword, $searchType, $pagination = 1 ) {
		if ( $this->helper == null ) {
			throw new Exception( 'checklogon required' );
		}

//			try {
				$products = $this->quick_search_v5( $keyword, $searchType, $pagination );
//			} catch ( Exception $exception ) {
//
//				if ( ATKPTools::str_contains( $exception->getMessage(), 'The request was denied due to request throttling.', false ) ) {
//					sleep( ATKP_AMZ_WAIT );
//					$products = $this->quick_search_v5( $keyword, $searchType, $pagination );
//				} else {
//					throw $exception;
//				}
//			}


		return $products;
	}

	private function checkurl( $url, $enable_ssl = null ) {

		if ( $enable_ssl == null ) {
			$enable_ssl = $this->enable_ssl;
		}

		if ( $enable_ssl ) {
			$url = str_replace( 'http://', 'https://', $url );
		}

		return $url;
	}

	private function checkimageurl( $url, $size ) {

		//if ( $this->enable_ssl ) {
		//	$url = str_replace( 'http://ecx.images-amazon.com', 'https://images-na.ssl-images-amazon.com', $url );
		//}

		if ( $size == 'small' && $this->smallimagesize > 0 ) {
			$url = str_replace( 'SL75', 'SL' . $this->smallimagesize, $url );
		}
		if ( $size == 'medium' && $this->mediumimagesize > 0 ) {
			$url = str_replace( 'SL160', 'SL' . $this->mediumimagesize, $url );
		}

		return $url;
	}

	private function checkimageurl_sitestripe( $url, $size ) {

		//if ( $this->enable_ssl ) {
		//	$url = str_replace( 'http://ecx.images-amazon.com', 'https://images-na.ssl-images-amazon.com', $url );
		//}

		if ( $size == 'small' && $this->smallimagesize > 0 ) {
			$url = str_replace( '._AC_AC_SR98,95_', '.SL' . $this->smallimagesize, $url );
		}
		if ( $size == 'medium' && $this->mediumimagesize > 0 ) {
			$url = str_replace( '._AC_AC_SR98,95_', '.SL' . $this->mediumimagesize, $url );
		}
		if ( $size == 'large' ) {
			$url = str_replace( '._AC_AC_SR98,95_', '', $url );
		}

		return $url;
	}

	private function checkResponse( $response ) {
		$requestHelp = null;
		if ( isset( $response->BrowseNodes->Request ) ) {
			$requestHelp = $response->BrowseNodes->Request;
		} else if ( isset( $response->Items->Request ) ) {
			$requestHelp = $response->Items->Request;
		}

		//echo('$response: ' .serialize($response));

		$message = '';

		if ( isset( $requestHelp->IsValid ) && $requestHelp->IsValid != 'True' ) {

			$message .= 'Invalid Request. IsValid: ' . $requestHelp->IsValid;

			//echo('xx '.serialize($requestHelp->Errors->Error));

		}

		if ( isset( $requestHelp->Errors->Error ) ) {

			if ( isset( $requestHelp->Errors->Error->Code ) && $requestHelp->Errors->Error->Code != '' ) {
				$error = $requestHelp->Errors->Error;
				if ( $message != '' ) {
					$message .= ' ';
				}
				$message .= 'ErrorCode: ' . $error->Code;
				if ( $message != '' ) {
					$message .= ' ';
				}
				$message .= 'Message: ' . $error->Message;
			} else {
				foreach ( $requestHelp->Errors->Error as $error ) {
					if ( $message != '' ) {
						$message .= ' ';
					}
					$message .= 'ErrorCode: ' . $error->Code;
					if ( $message != '' ) {
						$message .= ' ';
					}
					$message .= 'Message: ' . $error->Message;
				}
			}
		}

		return $message;
	}

	private function parse_department_file( $filename ) {
		$departments = array();

		if ( ( $handle = fopen( ATKP_AMAZON_PLUGIN_DIR . '/files/' . $filename, "r" ) ) !== false ) {
			while ( ( $data = fgetcsv( $handle, 1000, ";" ) ) !== false ) {

				$departments[ $data[0] ] = array(
					'caption'    => $data[1],
					'sortvalues' => array(
						'AvgCustomerReviews' => esc_html__( 'Sorts results according to average customer reviews', 'affiliate-toolkit-starter' ),
						'Featured'           => esc_html__( 'Sorts results with featured items having higher rank', 'affiliate-toolkit-starter' ),
						'NewestArrivals'     => esc_html__( 'Sorts results with according to newest arrivals', 'affiliate-toolkit-starter' ),
						'Price:HighToLow'    => esc_html__( 'Sorts results according to most expensive to least expensive', 'affiliate-toolkit-starter' ),
						'Price:LowToHigh'    => esc_html__( 'Sorts results according to least expensive to most expensive', 'affiliate-toolkit-starter' ),
						'Relevance'          => esc_html__( 'Sorts results with relevant items having higher rank', 'affiliate-toolkit-starter' ),
					)
				);

			}
			fclose( $handle );
		}

		return $departments;
	}

	private function retrieve_departments_v5() {
		switch ( $this->country ) {
			case 'de':
				return $this->parse_department_file( 'germany.csv' );
				break;
			default:
			case 'en':
				return $this->parse_department_file( 'unitedstates.csv' );
				break;
			case 'co.uk':
				return $this->parse_department_file( 'unitedkingdom.csv' );
				break;
			case 'ca':
				return $this->parse_department_file( 'canada.csv' );
				break;
			case 'fr':
				return $this->parse_department_file( 'france.csv' );
				break;
			case 'com.be':
				return $this->parse_department_file( 'belgium.csv' );
				break;
			case 'co.jp':
				return $this->parse_department_file( 'japan.csv' );
				break;
			case 'it':
				return $this->parse_department_file( 'italy.csv' );
				break;
			case 'cn':
			case 'es':
				return $this->parse_department_file( 'spain.csv' );
				break;
			case 'in':
				return $this->parse_department_file( 'india.csv' );
				break;
			case 'au':
				return $this->parse_department_file( 'australia.csv' );
				break;
			case 'com.br':
				return $this->parse_department_file( 'brazil.csv' );
				break;
			case 'com.mx':
				return $this->parse_department_file( 'mexico.csv' );
				break;
			case 'com.tr':
				return $this->parse_department_file( 'turkey.csv' );
				break;
			case 'ae':
				return $this->parse_department_file( 'emirates.csv' );
				break;
			case 'pl':
				return $this->parse_department_file( 'poland.csv' );
				break;
            case 'nl':
	            return $this->parse_department_file( 'netherlands.csv' );
                break;
		}
	}

	public function retrieve_departments() {
		if ( $this->helper == null ) {
			throw new Exception( 'checklogon required' );
		}

			$departments = $this->retrieve_departments_v5();


		return $departments;
	}


	private function retrieve_filters_v5() {
		$durations = array(
			'' => esc_html__( 'Not selected', 'affiliate-toolkit-starter' ),

			'Actor'                 => esc_html__( 'Actor', 'affiliate-toolkit-starter' ),
			'Artist'                => esc_html__( 'Artist', 'affiliate-toolkit-starter' ),
			'Author'                => esc_html__( 'Author', 'affiliate-toolkit-starter' ),
			'Availability'          => esc_html__( 'Availability', 'affiliate-toolkit-starter' ),
			'Brand'                 => esc_html__( 'Brand', 'affiliate-toolkit-starter' ),
			'BrowseNode'            => esc_html__( 'BrowseNode', 'affiliate-toolkit-starter' ),
			'Condition'             => esc_html__( 'Condition', 'affiliate-toolkit-starter' ),
			'CurrencyOfPreference'  => esc_html__( 'Currency Of Preference', 'affiliate-toolkit-starter' ),
			'DeliveryFlags'         => esc_html__( 'DeliveryFlags', 'affiliate-toolkit-starter' ),
			'LanguagesOfPreference' => esc_html__( 'Languages Of Preference', 'affiliate-toolkit-starter' ),
			'Marketplace'           => esc_html__( 'Marketplace', 'affiliate-toolkit-starter' ),

			'MaximumPrice'     => esc_html__( 'Maximum price', 'affiliate-toolkit-starter' ),
			'MinimumPrice'     => esc_html__( 'Minimum price', 'affiliate-toolkit-starter' ),
			'MerchantId'       => esc_html__( 'Merchant Id', 'affiliate-toolkit-starter' ),
			'MinReviewsRating' => esc_html__( 'Min Reviews Rating', 'affiliate-toolkit-starter' ),
			'MinPercentageOff' => esc_html__( 'Min percentage off', 'affiliate-toolkit-starter' ),

			'Keywords'    => esc_html__( 'Keywords', 'affiliate-toolkit-starter' ),
			'SearchIndex' => esc_html__( 'SearchIndex', 'affiliate-toolkit-starter' ),
			'Sort'        => esc_html__( 'Sort', 'affiliate-toolkit-starter' ),

			'Title' => esc_html__( 'Title', 'affiliate-toolkit-starter' ),
		);

		return $durations;
	}

	public function retrieve_filters() {

			$durations = $this->retrieve_filters_v5();


		return $durations;
	}

	public function retrieve_products( $asins, $id_type = 'ASIN' ) {

			//try {
				return $this->retrieve_products_v5( $asins, $id_type );
//			} catch ( Exception $exception ) {
//
//				if ( ATKPTools::str_contains( $exception->getMessage(), 'The request was denied due to request throttling.', false ) ) {
//					sleep( ATKP_AMZ_WAIT );
//
//					return $this->retrieve_products_v5( $asins, $id_type );
//				} else {
//					throw $exception;
//				}
//			}

	}

	private $second_try_api = false;

	private function search_sitestripeproduct( $keyword, $searchType, $pagination ) {

		$license = ATKP_LicenseController::get_module_license( 'amaznoapi' );

		try {

			$url = 'https://api.affiliate-toolkit.com/amazon/noapi.php?keywords=' . urlencode( $keyword ) . '&tag=' . $this->associateTag . '&country=' . strtoupper( $this->country ) . '&key=' . $license . '&page_number=' . $pagination;

			if ( $this->asindataapikey != '' )
				$url .= '&apikey=' . $this->asindataapikey;


			$page       = '';
			$statusCode = null;

			if ( function_exists( 'wp_remote_get' ) ) {

				$response = wp_remote_get( $url );

				if ( function_exists( 'is_wp_error' ) && ! is_wp_error( $response ) ) {

					// Success
					if ( isset( $response['response']['code'] ) ) {
						$statusCode = $response['response']['code'];
					}

					if ( isset( $response['body'] ) ) {
						$page = $response['body'];
					}
				}
			}

			$products   = new atkp_search_resp();
			$products_x = array();

			$products->currentpage = 0;
			$products->pagecount   = 0;
			$products->total       = 0;

			if ( 200 == $statusCode ) {
				$xx = json_decode( $page );

				if ( $xx != null && isset( $xx->data->errormessage ) && $xx->data->errormessage != '' ) {
					$products          = new atkp_search_resp();
					$products->message = $xx->data->errormessage . ' / ' . esc_url( $url );

					return $products;
				}

				if ( $xx != null && isset( $xx->products ) ) {
					foreach ( $xx->products as $p ) {
						$xxd             = array();
						$xxd['imageurl'] = $p->imageurl;

						$xxd['asin'] = $p->asin;
						//$product['ean'] = $ean_full;
						$xxd['producturl'] = $p->producturl;
						$xxd['title']      = $p->title;

						//$product['availability'] = $listing->getAvailability();

						$xxd['saleprice'] = isset( $p->saleprice ) ? $p->saleprice : '';
						$xxd['listprice'] = '';

						$products_x[] = $xxd;
					}
				}

				$products->products = $products_x;
				if ( isset( $xx->data->currentpage ) ) {
					$products->currentpage = intval( $xx->data->currentpage );
					$products->pagecount   = intval( $xx->data->pagecount );
					$products->total       = intval( $xx->data->total );
				}
			} else {
				if ( ! $this->second_try_api ) {
					$this->second_try_api = true;
					sleep( 1 );

					return $this->search_sitestripeproduct( $keyword, $searchType, $pagination );
				}


				$products = new atkp_search_resp();
				if ( $statusCode == 429 ) {
					$products->message = __( 'The query limit has been reached.', 'affiliate-toolkit-starter' );
				} else {
					$products->message = 'Invalid Status code: ' . $statusCode . ' / ' . esc_url( $url ) . ' / Please try again.';
				}

				return $products;
			}

			return $products;

		} catch ( Exception $e ) {
			$titlecheck = $e->getMessage();

			$products          = new atkp_search_resp();
			$products->message = $titlecheck;

			return $products;
		}

	}

	/**
	 * @param $asins
	 *
	 * @return atkp_response
	 */
	private function load_sitestripeproduct( $asins ) {
		$atkpresponse = new atkp_response();

		//https://ws-eu.amazon-adsystem.com/widgets/q?ServiceVersion=20070822&OneJS=1&Operation=GetAdHtml&MarketPlace=DE&ad_type=product_link&tracking_id=werbeanzeige1-21&marketplace=amazon&region=DE&asins=B07H2BRGPS

		$license = ATKP_LicenseController::get_module_license( 'amaznoapi');

		foreach ( $asins as $asin ) {

			try {
				$url = 'https://api.affiliate-toolkit.com/amazon/noapi.php?asin=' . $asin . '&tag=' . $this->associateTag . '&country=' . strtoupper( $this->country ) . '&key=' . $license;
				if ( $this->asindataapikey != '' )
					$url .= '&apikey=' . $this->asindataapikey;

                $page = '';

				if ( function_exists( 'wp_remote_get' ) ) {

					$response   = wp_remote_get( $url, array(
						'timeout'    => 20,
					) );
					$statusCode = null;

					if ( function_exists( 'is_wp_error' ) && ! is_wp_error( $response ) ) {

						// Success
						if ( isset( $response['response']['code'] ) ) {
							$statusCode = $response['response']['code'];
						}

						if ( '200' == $statusCode ) {
							$page = $response['body'];
						}
					}
					if ( $statusCode != 200 ) {
						$product             = new atkp_response_item();
						$product->uniqueid   = $asin;
						$product->uniquetype = 'ASIN';

						array_push( $atkpresponse->responseitems, $product );
						if ( $statusCode == 429 ) {
							$product->errormessage = __( 'The query limit has been reached.', 'affiliate-toolkit-starter' );
						} else {
							$product->errormessage = 'Invalid Status code: ' . $statusCode . ' / ' . esc_url( $url ) . ' / Please try again.';
						}
						array_push( $atkpresponse->responseitems, $product );
					}
				}

                $xx = json_decode($page );

				if ( $xx != null && isset( $xx->data->errormessage ) && $xx->data->errormessage != '' ) {

					$product               = new atkp_response_item();
					$product->errormessage = 'product error: ' . $xx->data->errormessage . ' / ' . esc_url( $url );
					$product->uniqueid     = $asin;
					$product->uniquetype   = 'ASIN';

					array_push( $atkpresponse->responseitems, $product );

				} else {
					if ( $xx != null && $xx->productitem != null ) {
						$myproduct = new atkp_product();

						foreach ( $xx->productitem->data as $key => $val ) {
							$myproduct->$key = $val;
						}
						$myproduct->updatedon = ATKPTools::get_currenttime();
						$myproduct->shopid    = $this->shopid;
						$myproduct->asin      = $asin;

						$product = new atkp_response_item();
						if ( isset( $xx->errormessage ) ) {
							$product->errormessage = $xx->errormessage;
						}
						$product->uniqueid   = $asin;
						$product->uniquetype = 'ASIN';

						$product->productitem = $myproduct;

						array_push( $atkpresponse->responseitems, $product );
					}
				}

			} catch ( Exception $e ) {
				if ( ! $this->second_try_api ) {
					$this->second_try_api = true;
					sleep( 1 );

					return $this->load_sitestripeproduct( $asins );
				}
				$titlecheck = $e->getMessage();

				$product               = new atkp_response_item();
				$product->errormessage = 'product error: ' . $titlecheck;
				$product->uniqueid     = $asin;
				$product->uniquetype   = 'ASIN';

				array_push( $atkpresponse->responseitems, $product );
			}
		}


		return $atkpresponse;
	}

	public function retrieve_products_v5( $asins, $id_type ) {
		$atkpresponse = new atkp_response();

		if ( count( $asins ) == 0 ) {
			return $atkpresponse;
		}
		switch ( strtoupper($id_type) ) {
			case 'TITLE':
			case "EAN":

                if ( $this->sitetripemode == 2) {
                    return $atkpresponse;
                }

				foreach ( $asins as $title ) {
					$items      = null;
					$titlecheck = '';
					try {
						$searchItemsRequest = new Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\SearchItemsRequest();
						$searchItemsRequest->setSearchIndex( 'All' );
						$searchItemsRequest->setKeywords( $title );
						$searchItemsRequest->setItemCount( 2 );
						if ( $this->languages_of_preference != null )
							$searchItemsRequest->setLanguagesOfPreference( $this->languages_of_preference);
						$searchItemsRequest->setPartnerTag( $this->associateTag );
						$searchItemsRequest->setPartnerType( Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\PartnerType::ASSOCIATES );
						$searchItemsRequest->setResources(
							\Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\SearchItemsResource::getAllowableEnumValues()
						);
						$searchItemsRequest->setItemPage( 1 );

						$searchItemsResponse = $this->sendSearchRequest( $searchItemsRequest );

						if ( $searchItemsResponse->getSearchResult() != null && $searchItemsResponse->getSearchResult()->getItems() != null ) {
							$items = $searchItemsResponse->getSearchResult()->getItems();
						}

					} catch ( Amazon\ProductAdvertisingAPI\v1\ApiException $exception ) {
						$titlecheck = "API-Error: " . $exception->getCode() . " " . $exception->getMessage();

						if ( $exception->getResponseObject() instanceof Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\ProductAdvertisingAPIClientException ) {
							$errors = $exception->getResponseObject()->getErrors();
							foreach ( $errors as $error ) {
								$titlecheck = "Response-Error: " . $error->getCode() . " " . $error->getMessage();
							}
						} else {
							$titlecheck .= "Error response body: " . $exception->getResponseBody();
						}
					} catch ( Exception $exception ) {

                        //NoResults
						if ( !ATKPTools::str_contains( $exception->getMessage(), 'NoResults: No results found for your request.', false ) ) {
							$titlecheck = "Error Message: " . $exception->getMessage(); //. ' ' . $exception->getTraceAsString();
						}
					}

					$added = false;

					if ( $titlecheck != '') {

						$responseitem               = new atkp_response_item();
						$responseitem->errormessage = $titlecheck;

						$responseitem->uniqueid   = $title;
						$responseitem->uniquetype = $id_type;

						array_push( $atkpresponse->responseitems, $responseitem );
						$added = true;

					} else {

						if ( $items != null ) {
							foreach ( $items as $result2 ) {
								if ( $result2->getASIN() == null || $added ) {
									continue;
								}

								$result = $result2;
								break;
							}


							if ( $result != null ) {
								$responseitem              = new atkp_response_item();
								$responseitem->productitem = $this->fill_product_v5( $result );

								$responseitem->uniqueid   = $title;
								$responseitem->uniquetype = $id_type;

								array_push( $atkpresponse->responseitems, $responseitem );
								$added = true;
							}
						}

					}
				}

				break;
			case 'ARTICLENUMBER':
			case 'ASIN':

				if ( $this->sitetripemode == 2 && $id_type == 'ASIN' ) {
					//bevorzugte nutzung
					$atkpresponse = $this->load_sitestripeproduct( $asins );

					return $atkpresponse;
				}

				foreach ( $asins as $asin ) {
					$items = array();
					$check = '';
					try {
						$getItemsRequest = new Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\GetItemsRequest();
						$getItemsRequest->setItemIds( array( $asin ) );
						$getItemsRequest->setItemIdType( ( 'ASIN' ) );
						if ( $this->languages_of_preference != null )
							$getItemsRequest->setLanguagesOfPreference( $this->languages_of_preference);
						$getItemsRequest->setPartnerTag( $this->associateTag );
						$getItemsRequest->setPartnerType( \Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\PartnerType::ASSOCIATES );
						$getItemsRequest->setResources(
							\Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\GetItemsResource::getAllowableEnumValues()
						);


						$getItemsResponse = $this->sendGetItemsRequest( $getItemsRequest );

						if ( $getItemsResponse->getItemsResult() != null && $getItemsResponse->getItemsResult()->getItems() != null ) {
							$items = $getItemsResponse->getItemsResult()->getItems();
						}


                    } catch ( Amazon\ProductAdvertisingAPI\v1\ApiException $exception ) {
						$check = "API-Error: " . $exception->getCode() . " " . $exception->getMessage();

						if ( $exception->getResponseObject() instanceof Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\ProductAdvertisingAPIClientException ) {
							$errors = $exception->getResponseObject()->getErrors();
							foreach ( $errors as $error ) {
								$check = "Response-Error: " . $error->getCode() . " " . $error->getMessage();
							}
						} else {
							$check .= "Error response body: " . $exception->getResponseBody();
						}
					} catch ( Exception $exception ) {
						$check = "Error Message: " . $exception->getMessage(); //. ' ' . $exception->getTraceAsString();
					}


					$added = false;

					if ( ! empty( $check ) && $this->sitetripemode == 3 && strtoupper($id_type) == 'ASIN' ) {
						$atkpresponse2 = $this->load_sitestripeproduct( array( $asin ) );

						array_push( $atkpresponse->responseitems, $atkpresponse2->responseitems[0] );
						$added = true;
					} else if ( $check != '' || $items == null ) {

						$responseitem               = new atkp_response_item();
						$responseitem->errormessage = empty( $check ) ? 'product not found' : $check;

						$responseitem->uniqueid   = $asin;
						$responseitem->uniquetype = $id_type;

						array_push( $atkpresponse->responseitems, $responseitem );
						$added = true;

					} else {

						if ( $items != null ) {
							$result = null;
							foreach ( $items as $result2 ) {
								if ( $result2->getASIN() == null || $added ) {
									continue;
								}

								$result = $result2;
								break;
							}
							if ( $result != null ) {
								$responseitem              = new atkp_response_item();
								$responseitem->productitem = $this->fill_product_v5( $result );

								$responseitem->uniqueid   = $asin;
								$responseitem->uniquetype = $id_type;


								array_push( $atkpresponse->responseitems, $responseitem );
								$added = true;
							}
						}
					}

				}


				break;
			default:
				throw new Exception( esc_html__( 'unknown id_type: ' . $id_type, 'affiliate-toolkit-starter' ) );
				break;
		}

		return $atkpresponse;
	}


	/**
	 * Sets itemIdType
	 *
	 * @param atkp_product $myproduct
	 * @param \Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\Item $result
	 *
	 * @return $myproduct atkp_product
	 */
	public function load_variations_v5( $myproduct, $result ) {
		$variations = array();
		$dimmension = array();


		$parentasin = $result->getParentASIN();
		if ( $parentasin == '' ) {
			//try to load the variations
			try {
				$getItemsRequest = new Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\GetVariationsRequest();
				$getItemsRequest->setASIN( $result->getASIN() );
				$getItemsRequest->setVariationCount( 10 );
				$getItemsRequest->setPartnerTag( $this->associateTag );
				if ( $this->languages_of_preference != null )
					$getItemsRequest->setLanguagesOfPreference( $this->languages_of_preference);
				$getItemsRequest->setPartnerType( \Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\PartnerType::ASSOCIATES );

				$getItemsRequest->setResources(
					\Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\GetVariationsResource::getAllowableEnumValues()
				);


				$getItemsResponse = $this->sendVariationRequest( $getItemsRequest );


				if ( $getItemsResponse->getVariationsResult() != null && $getItemsResponse->getVariationsResult()->getItems() != null ) {

					foreach ( $getItemsResponse->getVariationsResult()->getItems() as $variationItem ) {
						$att = $variationItem->getVariationAttributes();

						foreach ( $att as $tmp ) {
							$dimmension[ $tmp['name'] ] = apply_filters( 'atkp_variation_name', $tmp['name'] );
						}

						$varpd = $this->fill_product_v5( $variationItem, $result );


						$dimmfullname = array();

						foreach ( $att as $tmp ) {
							$dimmfullname[ $tmp['name'] ] = $tmp['value'];
						}

						$varpd->variationname = $dimmfullname;
						array_push( $variations, $varpd );

					}
				}
			} catch ( Exception $x ) {

			}
		}
		$myproduct->variationname = $dimmension;
		$myproduct->variations    = $variations;

		return $myproduct;
	}

	private function allowedCondition($condition) {
		//Any       	Offer Listings for items across any condition
		//New	        Offer Listings for New items
		//Used	        Offer Listings for Used items
		//Collectible	Offer Listings for Collectible items
		//Refurbished	Offer Listings for Certified Refurbished items

        return true;
/*
		if($condition == '' || $condition == null)
		    return true;

		if(ATKPTools::str_contains($condition, 'New', true)) {
		    return true;
        } else if($this->onlynew)
            return false;
		else
		    return true;
*/
    }

	/**
	 * Sets itemIdType
	 *
	 * @param \Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\Item $result
	 *
	 * @return $myproduct atkp_product
	 */
	private function fill_product_v5( $result, $parentResult = null ) {

		$myproduct            = new atkp_product();
		$myproduct->updatedon = ATKPTools::get_currenttime();
		$myproduct->shopid    = $this->shopid;

		//store the ASIN code in case we need it
		$myproduct->asin       = $result->getASIN();
		$myproduct->parentasin = $result->getParentASIN();

		//TODO: Variationen einbauen mit optionsfeld im shop

		if ( $parentResult == null && $this->load_variations ) {
			$myproduct = $this->load_variations_v5( $myproduct, $result );
		}

		if ( $result->getDetailPageURL() != null ) {
			$myproduct->producturl = urldecode( $result->getDetailPageURL() );
			//für 100% im titel - replace muss geklärt werden..
			$myproduct->producturl = str_replace( '%', '%25', $myproduct->producturl );

			$myproduct->addtocarturl = $this->checkurl( 'http://www.amazon.' . $this->country . '/gp/aws/cart/add.html?AWSAccessKeyId=' . $this->accessKey . '&AssociateTag=' . $this->associateTag . '&ASIN.1=' . $myproduct->asin . '&Quantity.1=1' );
		} else if ( $parentResult != null && $parentResult->getDetailPageURL() != null ) {

			$myproduct->producturl = urldecode( $parentResult->getDetailPageURL() );
			//für 100% im titel - replace muss geklärt werden..
			$myproduct->producturl = str_replace( '%', '%25', $myproduct->producturl );

			$myproduct->producturl   = $this->checkurl( 'https://www.amazon.' . $this->country . '/dp/' . $myproduct->asin . '?tag=' . $this->associateTag );
			$myproduct->addtocarturl = $this->checkurl( 'https://www.amazon.' . $this->country . '/gp/aws/cart/add.html?AWSAccessKeyId=' . $this->accessKey . '&AssociateTag=' . $this->associateTag . '&ASIN.1=' . $parentResult->ASIN . '&Quantity.1=1' );

		} else {
			$myproduct->producturl   = '';
			$myproduct->addtocarturl = '';
		}


		$images = array();
		if ( $result->getImages() != null ) {
			if ( $result->getImages()->getPrimary() != null ) {
				$myproduct->smallimageurl  = $this->checkimageurl( $result->getImages()->getPrimary()->getSmall()->getURL(), 'small' );
				$myproduct->mediumimageurl = $this->checkimageurl( $result->getImages()->getPrimary()->getMedium()->getURL(), 'medium' );
				$myproduct->largeimageurl  = $this->checkimageurl( $result->getImages()->getPrimary()->getLarge()->getURL(), 'large' );
			}

			if ( $result->getImages()->getVariants() != null ) {
				foreach ( $result->getImages()->getVariants() as $variant ) {
					if ( $variant->getLarge() == null && $variant->getMedium() == null && $variant->getSmall() == null ) {
						continue;
					}

					$udf     = new atkp_product_image();
					$udf->id = uniqid();
					if ( $variant->getSmall() != null ) {
						$udf->smallimageurl = $this->checkimageurl( $variant->getSmall()->getURL(), 'small' );
					}
					if ( $variant->getMedium() != null ) {
						$udf->mediumimageurl = $this->checkimageurl( $variant->getMedium()->getURL(), 'medium' );
					}
					if ( $variant->getLarge() != null ) {
						$udf->largeimageurl = $this->checkimageurl( $variant->getLarge()->getURL(), 'large' );
					}

					array_push( $images, $udf );
				}
			}
		}
		$myproduct->images = $images;

		if($result->getCustomerReviews() != null) {
		    if($result->getCustomerReviews()->getStarRating() != null)
		        $myproduct->rating = $result->getCustomerReviews()->getStarRating()->getValue();
		    if($result->getCustomerReviews() != null)
    			$myproduct->reviewcount = $result->getCustomerReviews()->getCount();
		}

		if ( $this->load_customer_reviews && $myproduct->reviewcount == 0) {

			$averageRating = 0;
			$totalReviews  = 0;

			$this->get_customer_rating_api( $myproduct->asin, $averageRating, $totalReviews );

			$myproduct->rating      = $averageRating;
			$myproduct->reviewcount = $totalReviews;

		}

		$myproduct->customerreviewurl = $this->checkurl( 'http://www.amazon.' . $this->country . '/product-reviews/' . $myproduct->asin . '/?tag=' . $this->associateTag );

		$description = '';
		$features = '';

		if ( $result->getItemInfo()->getFeatures() != null && $result->getItemInfo()->getFeatures()->getDisplayValues() != null ) {
			foreach ( $result->getItemInfo()->getFeatures()->getDisplayValues() as $feature ) {
				$features .= '<li>' . $feature . '</li>';
				$description .= $feature.' <br />';
			}
		}

		$myproduct->features    = $features == '' ? '' : '<ul>' . $features . '</ul>';
		$myproduct->description = $description;
		if ( $result->getItemInfo() != null && $result->getItemInfo()->getTitle() != null && $result->getItemInfo()->getTitle()->getDisplayValue() != null ) {
			$myproduct->title = htmlentities( $result->getItemInfo()->getTitle()->getDisplayValue() );
		} else {
			$myproduct->title = '';
		}

        if($result->getItemInfo() != null && $result->getItemInfo()->getProductInfo() != null) {
            $productInfo = $result->getItemInfo()->getProductInfo();

            if($productInfo->getColor() != null)
                $myproduct->customfields["a_color"] = $productInfo->getColor()->getDisplayValue();

	        if($productInfo->getItemDimensions() != null) {
                if( $productInfo->getItemDimensions()->getHeight() != null)
		            $myproduct->customfields["a_height"] = round($productInfo->getItemDimensions()->getHeight()->getDisplayValue(), 2). ' '.($productInfo->getItemDimensions()->getHeight()->getUnit());
                if($productInfo->getItemDimensions()->getLength() != null)
		            $myproduct->customfields["a_length"] = round($productInfo->getItemDimensions()->getLength()->getDisplayValue(),2). ' '.($productInfo->getItemDimensions()->getLength()->getUnit());
                if($productInfo->getItemDimensions()->getWidth() != null)
		            $myproduct->customfields["a_width"] = round($productInfo->getItemDimensions()->getWidth()->getDisplayValue(), 2). ' '.($productInfo->getItemDimensions()->getWidth()->getUnit());
                if($productInfo->getItemDimensions()->getWeight() != null)
		            $myproduct->customfields["a_weight"] = round($productInfo->getItemDimensions()->getWeight()->getDisplayValue(), 2) . ' '.($productInfo->getItemDimensions()->getWeight()->getUnit());
	        }
        }

		//preise laden

		// Prüfe zuerst OffersV2 (neue API), dann Offers (alte API) als Fallback
		$offersObject = null;
		$isOffersV2 = false;

		if ( $result->getOffersV2() != null ) {
			$offersObject = $result->getOffersV2();
			$isOffersV2 = true;
		} else if ( $result->getOffers() != null ) {
			$offersObject = $result->getOffers();
			$isOffersV2 = false;
		}

		if ( $offersObject != null ) {
			$offerlisting = null;
			if($offersObject->getListings() != null) {
				foreach ( $offersObject->getListings() as $listing ) {
					if ( $listing->getIsBuyBoxWinner() &&
                         $this->allowedCondition( $listing->getCondition() == null ? null : $listing->getCondition()->getValue() ) ) {
						$offerlisting = $listing;
						break;
					}
				}

				if ( $offerlisting == null) {
					$listings = array();
					foreach ( $offersObject->getListings() as $list ) {
						if ( $this->allowedCondition( $list->getCondition() == null ? null : $list->getCondition()->getValue() ) ) {
							$listings[] = $list;
						}
					}

					$offerlisting = reset( $listings );
				}
			}

			$myproduct->iswarehouse = false;
			if ( $offerlisting && $offerlisting != null ) {
				if($offerlisting->getDeliveryInfo() != null) {
					$myproduct->isprime = $offerlisting->getDeliveryInfo()->getIsPrimeEligible();
					if ( $offerlisting->getDeliveryInfo()->getShippingCharges() != null && count( $offerlisting->getDeliveryInfo()->getShippingCharges() ) > 0 ) {
						$myproduct->shipping = $offerlisting->getDeliveryInfo()->getShippingCharges()[0]->getDisplayAmount();
					}
				}

				if($offerlisting->getMerchantInfo() != null) {
				    if($offerlisting->getMerchantInfo()->getName() == 'Amazon Warehouse') {
					    $myproduct->iswarehouse = true;
                    }
                }

				// Helper-Funktion für OffersV2 Money-Struktur
				$getDisplayAmount = function($priceObject) use ($isOffersV2) {
					if ($priceObject == null) return '';
					if ($isOffersV2 && $priceObject->getMoney() != null) {
						return $priceObject->getMoney()->getDisplayAmount();
					}
					return $priceObject->getDisplayAmount();
				};

				$getAmount = function($priceObject) use ($isOffersV2) {
					if ($priceObject == null) return 0;
					if ($isOffersV2 && $priceObject->getMoney() != null) {
						return $priceObject->getMoney()->getAmount();
					}
					return $priceObject->getAmount();
				};

				// Baseprice und PricePerUnit für OffersV2
				$pricePerUnit = 0;
				$pricePerUnitDisplay = '';
				if ($offerlisting->getPrice() != null && $offerlisting->getPrice()->getPricePerUnit() != null) {
					$pricePerUnitObj = $offerlisting->getPrice()->getPricePerUnit();
					if ($isOffersV2 && is_object($pricePerUnitObj) && method_exists($pricePerUnitObj, 'getAmount')) {
						// OffersV2: PricePerUnit hat direkt amount und displayAmount
						$pricePerUnit = $pricePerUnitObj->getAmount();
						$pricePerUnitDisplay = $pricePerUnitObj->getDisplayAmount();
					}
				}

                if($pricePerUnit > 0) {
	                $myproduct->basepricefloat = $pricePerUnit;

					// Extrahiere Preis (vor /) und Einheit (nach /) aus displayAmount
                    $parts = explode(' / ', $pricePerUnitDisplay);
					$myproduct->baseprice = trim($parts[0]);
                    if(count($parts) > 1) {
	                    $myproduct->baseunit = trim($parts[1]);
                    }
                }

				$myproduct->saleprice      = $getDisplayAmount($offerlisting->getPrice());
				$myproduct->salepricefloat = $getAmount($offerlisting->getPrice());
				$myproduct->unitpricefloat = $pricePerUnit;

			if ( $offerlisting->getPrice() != null && $offerlisting->getPrice()->getSavings() != null ) {
				$savings = $offerlisting->getPrice()->getSavings();
				$myproduct->percentagesaved  = $savings->getPercentage();

				// OffersV2 hat Money-Objekt in Savings
				if ($isOffersV2 && $savings->getMoney() != null) {
					$myproduct->amountsaved      = $savings->getMoney()->getDisplayAmount();
					$myproduct->amountsavedfloat = $savings->getMoney()->getAmount();
				} else {
					$myproduct->amountsaved      = $savings->getDisplayAmount();
					$myproduct->amountsavedfloat = $savings->getAmount();
				}
			}

			if ( $offerlisting->getPrice() != null && $offerlisting->getPrice()->getSavingBasis() != null ) {
				$savingBasis = $offerlisting->getPrice()->getSavingBasis();

				// OffersV2 hat ein OffersV2SavingBasis-Objekt mit Money
				if ($isOffersV2 && is_object($savingBasis) && method_exists($savingBasis, 'getMoney') && $savingBasis->getMoney() != null) {
					$myproduct->listprice      = $savingBasis->getMoney()->getDisplayAmount();
					$myproduct->listpricefloat = $savingBasis->getMoney()->getAmount();
				} else if (is_object($savingBasis) && method_exists($savingBasis, 'getDisplayAmount')) {
					// Alte Offers-Struktur (SavingBasis ist ein OfferPrice-Objekt)
					$myproduct->listprice      = $savingBasis->getDisplayAmount();
					$myproduct->listpricefloat = $savingBasis->getAmount();
				}
			}

				if ( $offerlisting->getAvailability() != null ) {
                    /*
					if(ATKPTools::str_contains($offerlisting->getAvailability()->getMessage() ,'Nicht auf Lager', false)) {
						//preis = 0 ?
						$myproduct->saleprice = '';
						$myproduct->salepricefloat = 0;
					}*/
					$myproduct->availability = $offerlisting->getAvailability()->getMessage();
				}

			}
		}


		//$myproduct->salepricefloat   = $this->price_to_float( $myproduct->saleprice );
		//$myproduct->amountsavedfloat = $this->price_to_float( $myproduct->amountsaved );
		//$myproduct->listpricefloat   = $this->price_to_float( $myproduct->listprice );
		$myproduct->shippingfloat = (float) 0;


		if ( $result->getItemInfo() != null && $result->getItemInfo()->getByLineInfo() != null && $result->getItemInfo()->getByLineInfo()->getManufacturer() != null ) {
			$myproduct->manufacturer = $result->getItemInfo()->getByLineInfo()->getManufacturer()->getDisplayValue();
		}
		if ( $result->getItemInfo() != null && $result->getItemInfo()->getByLineInfo() != null && $result->getItemInfo()->getByLineInfo()->getBrand() != null ) {
			$myproduct->brand = $result->getItemInfo()->getByLineInfo()->getBrand()->getDisplayValue();
		}

		$isbn_full = '';
		if ( $result->getItemInfo()->getExternalIds() != null && $result->getItemInfo()->getExternalIds()->getISBNs() != null ) {
			foreach ( $result->getItemInfo()->getExternalIds()->getISBNs()->getDisplayValues() as $ean ) {
				if ( $isbn_full != '' ) {
					$isbn_full .= ',';
				}

				$isbn_full .= $ean;
			}
		}
		$myproduct->isbn = $isbn_full;

		$ean_full = '';
		if ( $result->getItemInfo()->getExternalIds() != null && $result->getItemInfo()->getExternalIds()->getEANs() != null ) {
			foreach ( $result->getItemInfo()->getExternalIds()->getEANs()->getDisplayValues() as $ean ) {
				if ( $ean_full != '' ) {
					$ean_full .= ',';
				}

				$ean_full .= $ean;
			}
		}
		$myproduct->ean = $ean_full;

		$category = '';
		if ( $result->getBrowseNodeInfo() != null ) {
			foreach ( $result->getBrowseNodeInfo()->getBrowseNodes() as $bnw ) {
				$category .= $this->getBrowseNodeTree( $bnw->getAncestor() );
				break;
			}
		}

		$myproduct->productgroup = $category;

		if ( $result->getItemInfo()->getProductInfo() && $result->getItemInfo()->getProductInfo()->getReleaseDate() ) {
			$myproduct->releasedate = substr( $result->getItemInfo()->getProductInfo()->getReleaseDate()->getDisplayValue(), 0, 10 );
		}
		if ( $result->getItemInfo()->getByLineInfo() != null && $result->getItemInfo()->getByLineInfo()->getContributors() != null ) {
			foreach ( $result->getItemInfo()->getByLineInfo()->getContributors() as $const ) {
				if ( $const->getRole() == 'Autor' ) {
					$myproduct->author = $const->getName();
					break;
				}
			}
		}

		if ( $result->getItemInfo()->getContentInfo() != null && $result->getItemInfo()->getContentInfo()->getPagesCount() != null ) {
			$myproduct->numberofpages = $result->getItemInfo()->getContentInfo()->getPagesCount()->getDisplayValue();
		}

		$myproduct->mpn = '';
		if ( $result->getItemInfo()->getManufactureInfo() != null && $result->getItemInfo()->getManufactureInfo()->getItemPartNumber() != null ) {
			$myproduct->mpn = $result->getItemInfo()->getManufactureInfo()->getItemPartNumber()->getDisplayValue();
		}


		if ( count( $myproduct->variations ) > 0 && $myproduct->salepricefloat == 0 ) {
			foreach ( $myproduct->variations as $variation ) {

				if ( $variation->salepricefloat > 0 ) {
					$myproduct->listprice   = $variation->listprice;
					$myproduct->amountsaved = $variation->amountsaved;
					$myproduct->saleprice   = $variation->saleprice;

					$myproduct->listpricefloat   = $variation->listpricefloat;
					$myproduct->amountsavedfloat = $variation->amountsavedfloat;
					$myproduct->percentagesaved  = $variation->percentagesaved;
					$myproduct->salepricefloat   = $variation->salepricefloat;
					$myproduct->shippingfloat    = $variation->shippingfloat;

					$myproduct->availability = $variation->availability;
					$myproduct->shipping     = $variation->shipping;
					$myproduct->isprime      = $variation->isprime;
					$myproduct->iswarehouse = $variation->iswarehouse;

					$myproduct->smallimageurl  = $variation->smallimageurl;
					$myproduct->mediumimageurl = $variation->mediumimageurl;
					$myproduct->largeimageurl  = $variation->largeimageurl;
					break;
				}
			}
		}

		if ($myproduct->salepricefloat == 0 && $this->sitetripemode == 3 && $myproduct->asin != '') {
				//bevorzugte nutzung
				$atkpresponse = $this->load_sitestripeproduct( array($myproduct->asin) );
				if(count($atkpresponse->responseitems) > 0) {
					$myproduct->saleprice = $atkpresponse->responseitems[0]->productitem->saleprice;
					$myproduct->salepricefloat = $atkpresponse->responseitems[0]->productitem->salepricefloat;
				}
		}

		return $myproduct;
	}

	/**
	 * Sets itemIdType
	 *
	 * @param \Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\BrowseNodeAncestor $browsenodeinfo
	 *
	 * @return string $result
	 */
	private function getBrowseNodeTree( $browsenodeancestor ) {
		if ( $browsenodeancestor == null ) {
			return '';
		}
		$result = '';

		if ( $browsenodeancestor->getAncestor() != null ) {
			$result .= ( $this->getBrowseNodeTree( $browsenodeancestor->getAncestor() ) ) . ' > ';
		}

		$result .= $browsenodeancestor->getDisplayName();

		return $result;
	}


    private function get_customer_rating_api($asin, &$averageRating, &$totalReviews) {
	    $p = $this->load_sitestripeproduct(array($asin));

        if(count($p->responseitems) > 0) {
	        $averageRating = $p->responseitems[0]->productitem->rating;
	        $totalReviews  = $p->responseitems[0]->productitem->reviewcount;
        }
    }
	private function setCondition($request) {

	    switch($this->onlynew) {
            default:

	            //$request->setCondition(\Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\Condition::ANY);
                break;
            case  1:
	            $request->setCondition(\Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\Condition::_NEW);
                break;
		    case  2:
			    $request->setCondition(\Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\Condition::USED);
			    break;
		    case  3:
			    $request->setCondition(\Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\Condition::COLLECTIBLE);
			    break;
		    case  4:
			    $request->setCondition(\Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\Condition::REFURBISHED);
			    break;
        }
	    return $request;
    }

	private function sendSearchRequest( $searchItemsRequest ) {
		$searchItemsRequest = $this->setCondition($searchItemsRequest);
		$this->validate_request_v5( $searchItemsRequest );

		if ( $this->seconds_wait > 0 ) {
			sleep( $this->seconds_wait );
		}

		//try {
			$searchItemsResponse = $this->helper->searchItems( $searchItemsRequest );
//		} catch ( Exception $exception ) {
//			if ( ATKPTools::str_contains( $exception->getMessage(), '429 Too Many Requests', false ) ) {
//
//				sleep( ATKP_AMZ_WAIT );
//				$searchItemsResponse = $this->helper->searchItems( $searchItemsRequest );
//			} else {
//				throw $exception;
//			}
//		}


		$this->validate_response_v5( $searchItemsResponse );

		return $searchItemsResponse;
	}

	private function sendVariationRequest( $variationRequest ) {

		$this->validate_request_v5( $variationRequest );

		if ( $this->seconds_wait > 0 ) {
			sleep( $this->seconds_wait );
		}


		//try {
			$getItemsResponse = $this->helper->getVariations( $variationRequest );
//		} catch ( Exception $exception ) {
//			if ( ATKPTools::str_contains( $exception->getMessage(), '429 Too Many Requests', false ) ) {
//				sleep( ATKP_AMZ_WAIT );
//				$getItemsResponse = $this->helper->getVariations( $variationRequest );
//			} else {
//				throw $exception;
//			}
//		}

		$this->validate_response_v5( $getItemsResponse );

		return $getItemsResponse;
	}

	private function sendGetItemsRequest( $getItemsRequest ) {
		$searchItemsRequest = $this->setCondition($getItemsRequest);

		$this->validate_request_v5( $getItemsRequest );

		if ( $this->seconds_wait > 0 ) {
			sleep( $this->seconds_wait );
		}

		//try {
			$getItemsResponse = $this->helper->getItems( $getItemsRequest );
//		} catch ( Exception $exception ) {
//			if ( ATKPTools::str_contains( $exception->getMessage(), '429 Too Many Requests', false ) ) {
//				sleep( ATKP_AMZ_WAIT );
//				$getItemsResponse = $this->helper->getItems( $getItemsRequest );
//			} else {
//				throw $exception;
//			}
//		}

		$this->validate_response_v5( $getItemsResponse );

		return $getItemsResponse;
	}

	public function retrieve_product_list( $search_request ) {
		if ( $this->helper == null ) {
			throw new Exception( 'checklogon required' );
		}

		$mylist            = new atkp_list_resp();
		$mylist->updatedon = ATKPTools::get_currenttime();
		$mylist->asins     = array();
		$mylist->products  = null;

		switch ( $search_request->request_type ) {
            case atkp_list_request_type::TopSellers:
            case atkp_list_request_type::NewReleases:

				$searchItemsRequest = new Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\SearchItemsRequest();
				//$searchItemsRequest->setSearchIndex( 'All' );
				$searchItemsRequest->setSortBy( $search_request->request_type == atkp_list_request_type::TopSellers ? 'Featured' : 'NewestArrivals' );

				$searchItemsRequest->setBrowseNodeId( $search_request->category );
				$searchItemsRequest->setKeywords( "*" );
			if ( $this->languages_of_preference != null )
				$searchItemsRequest->setLanguagesOfPreference( $this->languages_of_preference);
				$searchItemsRequest->setPartnerTag( $this->associateTag );
				$searchItemsRequest->setPartnerType( Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\PartnerType::ASSOCIATES );
				$searchItemsRequest->setResources(
					\Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\SearchItemsResource::getAllowableEnumValues()
				);

				$searchItemsRequest->setItemCount( 10 );
				$searchItemsRequest->setItemPage( 1 );

				$searchItemsResponse = $this->sendSearchRequest( $searchItemsRequest );

				$products = array();

                if ( $searchItemsResponse->getSearchResult() != null) {
                    $mylist->total_items_count = $searchItemsResponse->getSearchResult()->getTotalResultCount();
                    $mylist->total_pages       = ceil( $mylist->total_items_count / $this->get_maximum_items_per_page() );

                    if ($searchItemsResponse->getSearchResult()->getItems() != null ) {
                        $items = $searchItemsResponse->getSearchResult()->getItems();

                        foreach ( $items as $item ) {
                            if ( $item->getASIN() != null ) {
                                $products[] = $this->fill_product_v5( $item );
                            }
                        }
                    }
                }

				$mylist->products = $products;
				break;
            case atkp_list_request_type::ExtendedSearch:
            case atkp_list_request_type::Search:

				$searchItemsRequest = new Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\SearchItemsRequest();
				if ( $search_request->category != '' && $search_request->category != 'All' ) {
					$searchItemsRequest->setSearchIndex( $search_request->category );

					if ( $search_request->sort_order != '' ) {
						//AvgCustomerReviews', 'Featured', 'NewestArrivals', 'Price:HighToLow', 'Price:LowToHigh', 'Relevance

						switch ( $search_request->sort_order ) {
							case '-pubdate':
							case '-publication_date':
							case 'date-desc-rank':
							case 'launch_date':
								$sortorder = 'NewestArrivals';
								break;
							case 'popularity-rank':
							case 'relevancerank':
							case 'salesrank':
							case 'psrank':
							case 'titlerank':
							case '-titlerank':
							case '-unit-sales':
							default:
								$sortorder = 'Relevance';
								break;
							case 'reviewrank':
							case 'pmrank':
							case 'reviewrank_authority':
							case 'review-rank':
								$sortorder = 'AvgCustomerReviews';
								break;
							case 'price':
							case 'price-asc-rank':
							case 'pricerank':
								$sortorder = 'Price:LowToHigh';
								break;
							case '-price':
							case 'price-desc-rank':
							case 'inverse-pricerank':
								$sortorder = 'Price:HighToLow';
								break;
							case 'featured':
								$sortorder = 'Featured';
								break;
							case 'AvgCustomerReviews':
							case 'Featured':
							case 'NewestArrivals':
							case 'Price:HighToLow':
							case 'Price:LowToHigh':
							case 'Relevance':
								break;
						}

						$searchItemsRequest->setSortBy( $sortorder );
					}
				}

				//TODO: Filterfelder ergänzen
			    $keyword = $search_request->keyword;

				if ( $search_request->filter != null ) {
					foreach ( $search_request->filter as $field => $value ) {
						switch ( $field ) {
							case 'Keywords':
								$keyword = $value;
								break;
							case 'SearchIndex':
								$searchItemsRequest->setSearchIndex( $value );
								break;
							case 'Sort':
								$searchItemsRequest->setSortBy( $value );
								break;
							case 'Actor':
								$searchItemsRequest->setActor( $value );
								break;
							case 'Artist':
								$searchItemsRequest->setArtist( $value );
								break;
							case 'Author':
								$searchItemsRequest->setAuthor( $value );
								break;
							case 'Availability':
								$searchItemsRequest->setAvailability( $value );
								break;
							case 'Brand':
								$searchItemsRequest->setBrand( $value );
								break;
							case 'Condition':
								$searchItemsRequest->setCondition( $value );
								break;
							case 'DeliveryFlags':
								$searchItemsRequest->setDeliveryFlags( explode(',', $value) );
								break;
							case 'CurrencyOfPreference':
								$searchItemsRequest->setCurrencyOfPreference( $value );
								break;
							case 'LanguagesOfPreference':
								$searchItemsRequest->setLanguagesOfPreference( $value );
								break;
							case 'Marketplace':
								$searchItemsRequest->setMarketplace( $value );
								break;
							case 'MaximumPrice':
								$searchItemsRequest->setMaxPrice( floatval($value) );
								break;
							case 'MinimumPrice':
								$searchItemsRequest->setMinPrice( floatval($value) );
								break;
							case 'MerchantId':
								$searchItemsRequest->setMerchant( $value );
								break;
							case 'MinPercentageOff':
								$searchItemsRequest->setMinSavingPercent( intval($value) );
								break;
							case 'MinReviewsRating':
								$searchItemsRequest->setMinReviewsRating( intval($value) );
								break;
							case 'Title':
								$searchItemsRequest->setTitle( $value );
								break;
							case 'BrowseNode':
								$searchItemsRequest->setBrowseNodeId( $value );
								break;
						}
					}
				}

				if ( $keyword != '' ) {
					$keywords = explode( ',', $keyword );
					if ( $keywords != null && count( $keywords ) > 1 ) {
						$searchItemsRequest->setKeywords( $keywords );
					} else {
						$searchItemsRequest->setKeywords( $keyword );
					}
				}
			if ( $this->languages_of_preference != null )
				$searchItemsRequest->setLanguagesOfPreference( $this->languages_of_preference);
				$searchItemsRequest->setPartnerTag( $this->associateTag );
				$searchItemsRequest->setPartnerType( Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\PartnerType::ASSOCIATES );
				$searchItemsRequest->setResources(
					\Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\SearchItemsResource::getAllowableEnumValues()
				);

                $init_page = 1;
                if($search_request->items_per_page > 0) {
	                $searchItemsRequest->setItemCount( $search_request->items_per_page );
	                $init_page = $search_request->page;
	                $pages = $search_request->page;
                }else {
	                $itemsperpage = $this->get_maximum_items_per_page() > $search_request->max_count ? $search_request->max_count : $this->get_maximum_items_per_page();
	                $pages        = ceil( $search_request->max_count / $itemsperpage );

	                $searchItemsRequest->setItemCount( intval( $itemsperpage ) );
                }

				$products = array();
				for ( $x = $init_page; $x <= $pages; $x ++ ) {
					$searchItemsRequest->setItemPage( $x );

					$searchItemsResponse = $this->sendSearchRequest( $searchItemsRequest );

                    if($searchItemsResponse->getSearchResult() != null) {
                        $mylist->total_items_count = $searchItemsResponse->getSearchResult()->getTotalResultCount();
                        $mylist->total_pages       = ceil( $mylist->total_items_count / $this->get_maximum_items_per_page() );

                        if ($searchItemsResponse->getSearchResult()->getItems() != null ) {
                            $items = $searchItemsResponse->getSearchResult()->getItems();

                            foreach ( $items as $item ) {
                                if ( $item->getASIN() != null ) {
                                    $products[] = $this->fill_product_v5( $item );

                                    if ( $search_request->items_per_page == 0 && count( $products ) >= $search_request->max_count ) {
                                        break;
                                    }
                                }
                            }

                            if ( count( $items ) < $this->get_maximum_items_per_page() || ($search_request->items_per_page == 0 && count( $products ) >= $search_request->max_count   )) {
                                break;
                            }
                        }
                    }
				}

				$mylist->products = $products;

				break;
			default:
				$mylist->message =  'unknown request_type: ' . $search_request->request_type ;
				break;
		}

        return $mylist;
	}

	public function get_maximum_items_per_page() {
		return 10;
	}
	public function get_maximum_pages() {
		return 10;
	}

	public function retrieve_list( $requestType, $nodeid, $keyword, $asin, $maxCount, $sortByOrder, $filters ) {
		$my_request            = new atkp_list_req();
		$my_request->keyword = $keyword;
		$my_request->request_type = $requestType;
		$my_request->max_count = $maxCount;
		$my_request->filter = $filters;
		$my_request->category = $nodeid;
		$my_request->sort_order = $sortByOrder;

		return $this->retrieve_product_list($my_request);
	}

	public function get_supportedlistsources() {
		return implode(',', array(atkp_list_source_type::BestSeller,atkp_list_source_type::NewReleases, atkp_list_source_type::Search, atkp_list_source_type::ExtendedSearch));
	}

}


?>