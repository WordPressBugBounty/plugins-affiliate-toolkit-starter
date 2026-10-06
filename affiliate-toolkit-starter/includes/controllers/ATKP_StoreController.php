<?php
defined('ABSPATH') || exit;

class ATKP_StoreController {

	// Texts that get overlaid from the translated feed
	private static $translatable_fields = array( 'title', 'excerpt', 'content', 'permalink' );

	public static function get_product_discounts( $override = false ) {
		$cache = $override ? false : get_transient( 'atkp_discount' );

		if ( false === $cache ) {
			$url = ATKP_STORE_URL . 'wp-json/affiliate-toolkit/v1/aktion?version=' . ATKP_UPDATE_VERSION;

			$feed = wp_remote_get( esc_url_raw( $url ), array( 'timeout' => 30, 'sslverify' => false ) );

			if ( ! is_wp_error( $feed ) ) {
				if ( isset( $feed['body'] ) && strlen( $feed['body'] ) > 0 ) {
					$cache = json_decode( wp_remote_retrieve_body( $feed ) );

					try {
						if ( isset( $cache->aktion_filename ) && $cache->aktion_filename != '' && $cache->aktion_filecontent ) {
							$upload_dir = wp_upload_dir();

							if ( ! empty( $upload_dir['basedir'] ) ) {
								$user_dirname = $upload_dir['basedir'];
								if ( file_exists( $user_dirname ) ) {
									$user_filename = $user_dirname . '/' . $cache->aktion_filename;
									file_put_contents( $user_filename, $cache->aktion_filecontent );
								}
							}
						}
					} catch ( Exception $ex ) {

					}

					set_transient( 'atkp_discount', $cache, 10800 );
				}
			} else {

				$cache = null;
			}
		} else {
			return ( $cache );
		}

		return $cache;
	}

	/**
	 * Products feed of the store.
	 *
	 * The English feed is always the base: it is the only one that carries complete
	 * licensing, version and pricing data for every product. On a German install the
	 * German feed is loaded additionally and only its translated texts are merged in.
	 * If a translation is missing - or the translated feed is unavailable - the
	 * English entry is kept instead of dropping the product from the list.
	 */
	public static function get_products_feed( $tab = 'popular' ) {
		//ATKP_STORE_URL.'/de/edd-api/v2/products/'

		$translate     = ATKPTools::is_lang_de();
		$transient_key = $translate ? 'atkp_add_ons_feed_de' : 'atkp_add_ons_feed';

		$cache = get_transient( $transient_key );

		if ( false !== $cache ) {
			return $cache;
		}

		$products = self::fetch_products_feed( 'en' );

		if ( $products == null || ! isset( $products->products ) || ! is_array( $products->products ) ) {
			// English feed unavailable: the translated feed is still better than nothing.
			return $translate ? self::fetch_products_feed( 'de' ) : $products;
		}

		if ( $translate ) {
			self::merge_translated_texts( $products, self::fetch_products_feed( 'de' ) );
		}

		set_transient( $transient_key, $products, 3600 );

		return $products;
	}

	private static function fetch_products_feed( $lang ) {
		$url = ATKP_STORE_URL . 'edd-api/v2/products/?number=100&lang=' . $lang;

		$feed = wp_remote_get( esc_url_raw( $url ), array( 'timeout' => 30, 'sslverify' => false ) );

		if ( is_wp_error( $feed ) ) {
			return null;
		}

		$body = wp_remote_retrieve_body( $feed );

		if ( strlen( $body ) === 0 ) {
			return null;
		}

		return json_decode( $body );
	}

	/**
	 * Overlays the translated texts onto the English products.
	 *
	 * Translations are separate store posts, so ids and slugs differ between the
	 * languages. The thumbnail URL is shared by all translations of a product
	 * (create_date as fallback) and is unique per product.
	 */
	private static function merge_translated_texts( $products, $translated ) {
		if ( $translated == null || ! isset( $translated->products ) || ! is_array( $translated->products ) ) {
			return;
		}

		$by_thumbnail = array();
		$by_date      = array();

		foreach ( $translated->products as $product ) {
			if ( ! isset( $product->info ) ) {
				continue;
			}

			if ( ! empty( $product->info->thumbnail ) && is_string( $product->info->thumbnail ) ) {
				$by_thumbnail[ $product->info->thumbnail ] = $product;
			}

			if ( ! empty( $product->info->create_date ) ) {
				$by_date[ $product->info->create_date ] = $product;
			}
		}

		foreach ( $products->products as $product ) {
			if ( ! isset( $product->info ) ) {
				continue;
			}

			$match = null;

			if ( ! empty( $product->info->thumbnail ) && is_string( $product->info->thumbnail ) && isset( $by_thumbnail[ $product->info->thumbnail ] ) ) {
				$match = $by_thumbnail[ $product->info->thumbnail ];
			} elseif ( ! empty( $product->info->create_date ) && isset( $by_date[ $product->info->create_date ] ) ) {
				$match = $by_date[ $product->info->create_date ];
			}

			if ( $match == null ) {
				continue;
			}

			foreach ( self::$translatable_fields as $field ) {
				if ( ! empty( $match->info->$field ) ) {
					$product->info->$field = $match->info->$field;
				}
			}
		}
	}

}
