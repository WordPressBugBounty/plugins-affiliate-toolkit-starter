=== affiliate-toolkit - Amazon Partner Program ===
Contributors: cservit
Requires at least: 5.6
Tested up to: 7.0
Stable tag: 1.1.4
Requires PHP: 8.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Adds the Amazon Partner Program to affiliate-toolkit.

== Description ==

This extension connects affiliate-toolkit to Amazon. Product data is retrieved either through
the Product Advertising API 5.0, through the new Amazon Creators API (OAuth 2.0 credentials) or,
together with the "Amazon No API Mode" extension, without any Amazon API. Products are found by
ASIN, EAN or keyword and imported with title, images, prices, offers, browse node and - if
enabled - variations; lists are filled from best sellers, new releases, a keyword search or an
extended search. The API modes can use the No-API mode as fallback for products the API does
not deliver. 17 Amazon marketplaces can be selected.

Requires the affiliate-toolkit main plugin and an Amazon Associates account; the API modes
additionally require Product Advertising API or Creators API credentials.

== Changelog ==

= 1.1.4 =
*   FIX: The fallback modes "Product Advertising API with No-API fallback" and "Creators API with No-API fallback" could not be selected, and a shop that already used the Creators API with fallback was silently switched to the Creators API without fallback the next time it was saved. The shop settings now have a "No-API fallback" checkbox for the two API modes (shown when the No-API extension is licensed), and an existing fallback setting is shown and kept - also when the extension is inactive or its licence has expired and the checkbox is not shown.
*   FIX: The No-API card in the mode selection said "PURCHASE REQUIRED" and "Included in your affiliate-toolkit license" at the same time when the No-API extension was not active. Without the extension the card now explains that the "affiliate-toolkit - Amazon No API Mode" extension has to be activated.
*   FIX: The "Credential Version" of the Creators API had no empty entry, so a new shop always showed and saved "2.1 (NA)" - also for a German or other European marketplace, whose credentials need a different version. When no version has been saved yet, the version that belongs to the selected Amazon website is preselected now.
*   FIX: In No-API mode, lists were requested from the Product Advertising API even when no API credentials were configured, which only produced an unclear API error. Such a list now reports that lists are not supported in No-API mode. Lookups by EAN or title returned no result and no message at all; every product now gets the message that the No-API mode can only load products by ASIN. Products imported by article number - which is the ASIN at Amazon - are loaded through the No-API mode now instead of the Product Advertising API.
*   FIX: A switch statement used "default;" instead of "default:", which newer PHP versions report as deprecated.
*   FIX: The German translation of the main plugin did not load on WordPress 6.7 and later, so affiliate-toolkit and its extensions were shown in English. The plugin header of this extension claimed the text domain of the main plugin, and WordPress registers the header's text domain together with the extension's folder as the place to look for translations - the extension loaded last therefore redirected the main plugin's translations to a folder that has none. The header now names the extension's own text domain.

= 1.1.3 and earlier =
*   The change history of the earlier versions is kept with the extension on affiliate-toolkit.com.
