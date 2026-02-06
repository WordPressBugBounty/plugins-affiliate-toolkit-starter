/**
 * Affiliate Toolkit Product Block
 * @since 3.0.0
 */

		(function (blocks, element, blockEditor, components, i18n) {
		const el = element.createElement;
		const {registerBlockType} = blocks;
		const {InspectorControls, useBlockProps} = blockEditor;
		const {PanelBody, TextControl, SelectControl, ToggleControl, Button, TabPanel, Spinner} = components;

			// Translation data from PHP
			const t = atkpBlocks.i18n || {};

		// Translation function - maps English text to translated text
		const __ = (text, domain) => {
			// Mapping von englischen Texten zu i18n Keys
			const keyMap = {
				'affiliate-toolkit Product': t.affiliateToolkitProduct || 'affiliate-toolkit Produkt',
				'Product Selection': t.productSelection || 'Produktauswahl',
				'Selected Product:': t.selectedProduct || 'Ausgewähltes Produkt:',
				'Change Product': t.changeProduct || 'Produkt ändern',
				'Select': t.select || 'Auswählen',
				'Import': t.import || 'Importieren',
				'Search existing products:': t.searchExistingProducts || 'Bestehende Produkte suchen:',
				'Enter product title or ID...': t.enterProductTitleOrId || 'Produkttitel oder ID eingeben...',
				'Enter product name or ID...': t.enterProductTitleOrId || 'Produktname oder ID eingeben...',
				'Enter product name, ASIN, or EAN...': 'Produktname, ASIN oder EAN eingeben...',
				'Search': t.search || 'Suchen',
				'Searching...': 'Suche läuft...',
				'Importing...': 'Wird importiert...',
				'Showing 5 newest products': t.showing5NewestProducts || 'Zeige 5 neueste Produkte',
				'Import products from shop:': t.importProductsFromShop || 'Produkte aus Shop importieren:',
				'Import product from shop:': 'Produkt aus Shop importieren:',
				'Select Shop': t.selectShop || 'Shop auswählen',
				'-- Select Shop --': t.selectShopPlaceholder || '-- Shop auswählen --',
				'Search Keyword': t.searchKeyword || 'Suchbegriff',
				'e.g., "iPhone 15"': t.exampleIphone || 'z.B. "iPhone 15"',
				'Keyword to search for products': t.keywordToSearch || 'Suchbegriff für Produkte',
				'Loading Preview...': t.loadingPreview || 'Vorschau wird geladen...',
				'Preview Results': t.previewResults || 'Ergebnisse anzeigen',
				'Import & Select': t.importAndSelect || 'Importieren & Auswählen',
				'No products found. Try a different search term.': t.noProductsFound || 'Keine Produkte gefunden. Versuchen Sie einen anderen Suchbegriff.',
				'Or enter Product ID directly below:': t.orEnterProductId || 'Oder geben Sie die Produkt-ID direkt unten ein:',
				'Product ID (Manual)': t.productIdManual || 'Produkt-ID (Manuell)',
				'Enter product ID if you know it': t.enterProductIdIfKnown || 'Produkt-ID eingeben, falls bekannt',
				'Display Settings': t.displaySettings || 'Anzeigeeinstellungen',
				'Template': t.template || 'Template',
				'Button Type': t.buttonType || 'Button-Typ',
				'Default': t.default || 'Standard',
				'Add to Cart': t.addToCart || 'In den Warenkorb',
				'Link': t.link || 'Link',
				'Product Page': t.productPage || 'Produktseite',
				'Alignment': 'Ausrichtung',
				'No alignment': 'Keine Ausrichtung',
				'Left': 'Links',
				'Center': 'Zentriert',
				'Right': 'Rechts',
				'Custom Content': 'Benutzerdefinierter Inhalt',
				'Optional custom content or link text': 'Optionaler benutzerdefinierter Inhalt oder Link-Text',
				'Hide Disclaimer': t.hideDisclaimer || 'Disclaimer ausblenden',
				'Hide the affiliate disclaimer text': t.hideDisclaimerText || 'Affiliate-Disclaimer-Text ausblenden',
				'Advanced Settings': t.advancedSettings || 'Erweiterte Einstellungen',
				'Container CSS Class': t.containerCssClass || 'Container CSS-Klasse',
				'Custom CSS classes for the container': t.customCssContainer || 'Benutzerdefinierte CSS-Klassen für den Container',
				'Element CSS Class': t.elementCssClass || 'Element CSS-Klasse',
				'Custom CSS classes for elements': t.customCssElements || 'Benutzerdefinierte CSS-Klassen für Elemente',
				'Override Affiliate ID': t.overrideAffiliateId || 'Affiliate-ID überschreiben',
				'Amazon or eBay tracking ID': t.amazonEbayTrackingId || 'Amazon oder eBay Tracking-ID',
				'Product ID:': t.productId || 'Produkt-ID:',
				'Template:': t.templateLabel || 'Template:',
				'Output will be rendered on frontend': 'Ausgabe wird im Frontend gerendert',
				'Please select a product using the sidebar →': t.pleaseSelectProduct || 'Bitte wählen Sie ein Produkt in der Seitenleiste aus →',
				'Please select a shop first': t.pleaseSelectShopFirst || 'Bitte wählen Sie zuerst einen Shop aus',
				'Product already exists and has been selected': t.productAlreadyExists || 'Produkt existiert bereits und wurde ausgewählt',
				'Product imported successfully!': t.productImportedSuccessfully || 'Produkt erfolgreich importiert!',
				'Failed to import product: ': t.failedToImportProduct || 'Fehler beim Importieren des Produkts: ',
				'Failed to import product: Invalid response': t.failedToImportInvalidResponse || 'Fehler beim Importieren des Produkts: Ungültige Antwort',
				'Failed to import product: Invalid response format': t.failedToImportInvalidFormat || 'Fehler beim Importieren des Produkts: Ungültiges Antwortformat',
				'Error importing product': t.errorImportingProduct || 'Fehler beim Importieren des Produkts',
				'Search failed: ': t.searchFailed || 'Suche fehlgeschlagen: ',
				'Search failed': t.searchFailedGeneric || 'Suche fehlgeschlagen',
				'Server error (500). Please check error log.': t.serverError500 || 'Serverfehler (500). Bitte prüfen Sie das Fehlerprotokoll.'
			};

			return keyMap[text] || text;
		};

			// Helper function to decode HTML entities
			const decodeHtml = (html) => {
				if (!html) return html;
				const txt = document.createElement('textarea');
				txt.innerHTML = html;
				return txt.value;
			};

			const {useState, useEffect} = element;

	registerBlockType('atkp/product', {
		title: __('affiliate-toolkit Product', 'affiliate-toolkit-starter'),
		icon: {
			src: el('img', {
				src: (typeof atkpBlocks !== 'undefined' && atkpBlocks.pluginUrl)
					? atkpBlocks.pluginUrl + '/images/affiliate_toolkit_menu.png'
					: '',
				alt: 'affiliate-toolkit',
				style: { width: '20px', height: '20px' }
			})
		},
		category: 'affiliate-toolkit',
		attributes: {
			productId: {
				type: 'string',
				default: ''
			},
			productTitle: {
				type: 'string',
				default: ''
			},
			template: {
				type: 'string',
				default: ''
			},
			buttonType: {
				type: 'string',
				default: ''
			},
			align: {
				type: 'string',
				default: ''
			},
			elementCss: {
				type: 'string',
				default: ''
			},
			containerCss: {
				type: 'string',
				default: ''
			},
			content: {
				type: 'string',
				default: ''
			},
			field: {
				type: 'string',
				default: ''
			},
			link: {
				type: 'boolean',
				default: false
			},
			hideDisclaimer: {
				type: 'boolean',
				default: false
			},
			trackingId: {
				type: 'string',
				default: ''
			}
		},

		edit: function(props) {
			const { attributes, setAttributes } = props;
			const blockProps = useBlockProps();
			const [searchTerm, setSearchTerm] = useState('');
			const [searchResults, setSearchResults] = useState([]);
			const [isSearching, setIsSearching] = useState(false);
			const [searchMode, setSearchMode] = useState('internal'); // 'internal' or 'external'
			const [selectedShop, setSelectedShop] = useState('');
			const [externalResults, setExternalResults] = useState([]);
			const [isImporting, setIsImporting] = useState(false);
			const [hasSearched, setHasSearched] = useState(false);


			// Get templates and shops from localized data
			const templateOptions = (typeof atkpBlocks !== 'undefined' && atkpBlocks.templates)
				? atkpBlocks.templates
				: [{ label: __('Default', 'affiliate-toolkit-starter'), value: '' }];

			const shopOptions = (typeof atkpBlocks !== 'undefined' && atkpBlocks.shops)
				? atkpBlocks.shops
				: [];

			// Load templates and fields on mount
			useEffect(() => {

				// Load initial 5 newest products
				loadInitialProducts();
			}, []);

			// Load initial products
			const loadInitialProducts = () => {
				if (typeof atkpBlocks === 'undefined' || !atkpBlocks.ajaxurl) {
					return;
				}

				setIsSearching(true);

				jQuery.ajax({
					url: atkpBlocks.ajaxurl,
					type: 'POST',
					dataType: 'json',
					data: {
						action: 'atkp_block_search_products',
						type: 'atkp_product',
						keyword: '',
						limit: 5,
						nonce: atkpBlocks.nonce
					},
					success: function(response) {
						if (response.success && response.data && Array.isArray(response.data)) {
							setSearchResults(response.data);
						}
						setIsSearching(false);
					},
					error: function() {
						setIsSearching(false);
					}
				});
			};

			// Search products
			const searchProducts = () => {
				if (!searchTerm || searchTerm.length < 2) {
					loadInitialProducts();
					return;
				}

				if (typeof atkpBlocks === 'undefined' || !atkpBlocks.ajaxurl) {
					return;
				}

				setIsSearching(true);

				console.log('ATKP: Searching products with keyword:', searchTerm);

				jQuery.ajax({
					url: atkpBlocks.ajaxurl,
					type: 'POST',
					dataType: 'json',
					data: {
						action: 'atkp_block_search_products',
						type: 'atkp_product',
						keyword: searchTerm,
						limit: 20,
						nonce: atkpBlocks.nonce
					},
					success: function(response) {
						console.log('ATKP: Search response:', response);
						if (response.success && response.data && Array.isArray(response.data)) {
							console.log('ATKP: Found products:', response.data.length);
							setSearchResults(response.data);
						} else {
							console.log('ATKP: No products found or invalid response');
							setSearchResults([]);
						}
						setIsSearching(false);
					},
					error: function(xhr, status, error) {
						console.error('ATKP: Search error:', error);
						console.error('ATKP: XHR:', xhr);
						setIsSearching(false);
						setSearchResults([]);
					}
				});
			};

			// Select product from search
			const selectProduct = (product) => {
				setAttributes({
					productId: product.id.toString(),
					productTitle: decodeHtml(product.title)
				});
				setSearchResults([]);
				setSearchTerm('');
			};

			// Search external products
			const searchExternalProducts = () => {
				if (!searchTerm || searchTerm.length < 2) {
					return;
				}

				if (!selectedShop) {
					alert(__('Please select a shop first', 'affiliate-toolkit-starter'));
					return;
				}

				if (typeof atkpBlocks === 'undefined' || !atkpBlocks.ajaxurl) {
					return;
				}

				setIsSearching(true);
				setExternalResults([]);
				setHasSearched(true);

				jQuery.ajax({
					url: atkpBlocks.ajaxurl,
					type: 'POST',
					dataType: 'json',
					data: {
						action: 'atkp_block_search_external',
						keyword: searchTerm,
						shop_id: selectedShop,
						page: 1
					},
					success: function(response) {
						console.log('Search response:', response);
						if (response.success && response.data && response.data.products) {
							setExternalResults(response.data.products);
							// Removed alert for empty results - message is shown below results
						} else {
							setExternalResults([]);
							if (response.data && response.data.message) {
								alert(__('Search failed: ', 'affiliate-toolkit-starter') + response.data.message);
							}
						}
						setIsSearching(false);
					},
					error: function(xhr, status, error) {
						console.error('External search error:', error);
						console.error('XHR:', xhr);
						console.error('Response text:', xhr.responseText);
						
						let errorMessage = __('Search failed', 'affiliate-toolkit-starter');
						if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
							errorMessage = xhr.responseJSON.data.message;
						} else if (xhr.status === 500) {
							errorMessage = __('Server error (500). Please check error log.', 'affiliate-toolkit-starter');
						}
						
						alert(errorMessage);
						setIsSearching(false);
						setExternalResults([]);
					}
				});
			};

			// Import external product
			const importProduct = (product) => {
				if (typeof atkpBlocks === 'undefined' || !atkpBlocks.ajaxurl) {
					return;
				}

				setIsImporting(true);

				jQuery.ajax({
					url: atkpBlocks.ajaxurl,
					type: 'POST',
					dataType: 'json',
					data: {
						action: 'atkp_import_product',
						request_nonce: atkpBlocks.nonce,
						shop: product.shop_id,
						asin: product.asin,
						asintype: 'ASIN',
						title: '',
						status: 'publish',
						importurl: '',
						brand: '',
						mpn: '',
						subshopid: ''
					},
					success: function(response) {
						console.log('Import response:', response);

						// atkp_import_product returns an array
						if (response && Array.isArray(response) && response.length > 0) {
							const result = response[0];
							if (result.postid) {
								// Use product.title as fallback if result.title is empty
								// Decode HTML entities in both cases
								const productTitle = result.title && result.title.trim() !== ''
									? decodeHtml(result.title)
									: decodeHtml(product.title);

								setAttributes({
									productId: result.postid.toString(),
									productTitle: productTitle
								});
								setExternalResults([]);
								setSearchTerm('');
								setSearchMode('internal');

								if (result.alreadyexists) {
									alert(__('Product already exists and has been selected', 'affiliate-toolkit-starter'));
								} else {
									alert(__('Product imported successfully!', 'affiliate-toolkit-starter'));
								}
							} else if (result.error) {
								alert(__('Failed to import product: ', 'affiliate-toolkit-starter') + result.error);
							} else {
								alert(__('Failed to import product: Invalid response', 'affiliate-toolkit-starter'));
							}
						} else if (response && response.error) {
							alert(__('Failed to import product: ', 'affiliate-toolkit-starter') + response.error);
						} else {
							alert(__('Failed to import product: Invalid response format', 'affiliate-toolkit-starter'));
						}
						setIsImporting(false);
					},
					error: function(xhr, status, error) {
						console.error('Import error:', error);
						console.error('XHR:', xhr);
						alert(__('Error importing product', 'affiliate-toolkit-starter'));
						setIsImporting(false);
					}
				});
			};

			return el('div', blockProps,
				el(InspectorControls, {},
					// Product Selection Panel
					el(PanelBody, {
						title: __('Product Selection', 'affiliate-toolkit-starter'),
						initialOpen: true
					},
						attributes.productId
							? el('div', { className: 'atkp-selected-product' },
								el('p', { style: { marginBottom: '8px', fontWeight: 'bold' } },
									__('Selected Product:', 'affiliate-toolkit-starter')
								),
								el('p', { style: { marginBottom: '8px' } },
									decodeHtml(attributes.productTitle) + ' (ID: ' + attributes.productId + ')'
								),
								el(Button, {
									isSecondary: true,
									onClick: () => setAttributes({ productId: '', productTitle: '' })
								}, __('Change Product', 'affiliate-toolkit-starter'))
							)
							: el('div', { className: 'atkp-product-search' },
								// Search mode toggle
								el('div', { style: { display: 'flex', gap: '8px', marginBottom: '12px', borderBottom: '1px solid #ddd', paddingBottom: '8px' } },
									el(Button, {
										isPrimary: searchMode === 'internal',
										isSecondary: searchMode !== 'internal',
										onClick: () => {
											setSearchMode('internal');
											setExternalResults([]);
										}
									}, __('Select', 'affiliate-toolkit-starter')),
									el(Button, {
										isPrimary: searchMode === 'external',
										isSecondary: searchMode !== 'external',
										onClick: () => {
											setSearchMode('external');
											setSearchResults([]);
										}
									}, __('Import', 'affiliate-toolkit-starter'))
								),

								// Internal search mode
								searchMode === 'internal' && el('div', {},
									el('p', { style: { marginBottom: '8px' } },
										__('Search existing products:', 'affiliate-toolkit-starter')
									),
									el('div', { style: { display: 'flex', gap: '8px', marginBottom: '8px' } },
										el(TextControl, {
											value: searchTerm,
											onChange: setSearchTerm,
											placeholder: __('Enter product name or ID...', 'affiliate-toolkit-starter'),
											onKeyDown: (e) => {
												if (e.key === 'Enter') {
													searchProducts();
												}
											}
										})
									),
									el(Button, {
										isPrimary: true,
										onClick: searchProducts,
										disabled: searchTerm.length < 2
									}, __('Search', 'affiliate-toolkit-starter')),

									isSearching && el(Spinner),

									searchResults.length > 0 && el('div', {
										style: {
											marginTop: '12px',
											maxHeight: '200px',
											overflowY: 'auto',
											border: '1px solid #ddd',
											borderRadius: '4px'
										}
									},
									searchResults.map(product =>
										el('div', {
											key: product.id,
											onClick: () => selectProduct(product),
											style: {
												padding: '8px',
												cursor: 'pointer',
												borderBottom: '1px solid #f0f0f0'
											},
											onMouseEnter: (e) => e.currentTarget.style.background = '#f5f5f5',
											onMouseLeave: (e) => e.currentTarget.style.background = 'transparent'
										},
											el('div', {
												style: {
													display: 'flex',
													gap: '12px',
													alignItems: 'flex-start'
												}
											},
												product.imageurl && el('img', {
													src: product.imageurl,
													style: {
														width: '60px',
														height: '60px',
														objectFit: 'contain',
														flexShrink: 0
													}
												}),
												el('div', { style: { flex: 1, minWidth: 0 } },
													el('div', {
														style: {
															fontWeight: 'bold',
															marginBottom: '4px',
															fontSize: '13px',
															lineHeight: '1.4'
														}
													},
														decodeHtml(product.title)
													),
													el('div', { style: { fontSize: '12px', color: '#666' } },
														'ID: ' + product.id
													)
												)
											)
										)
									),
									),

									!searchTerm && searchResults.length > 0 && el('p', {
										style: {
											marginTop: '8px',
											fontSize: '12px',
											color: '#666',
											textAlign: 'center'
										}
									}, __('Showing 5 newest products', 'affiliate-toolkit-starter'))
								),

								// External search mode
								searchMode === 'external' && el('div', {},
									el('p', { style: { marginBottom: '8px' } },
										__('Import product from shop:', 'affiliate-toolkit-starter')
									),
									
									// Shop selector
									shopOptions.length > 0 && el(SelectControl, {
										label: __('Select Shop', 'affiliate-toolkit-starter'),
										value: selectedShop,
										onChange: setSelectedShop,
										options: [
											{ label: __('-- Select Shop --', 'affiliate-toolkit-starter'), value: '' },
											...shopOptions
										]
									}),

									el(TextControl, {
										label: __('Search Keyword', 'affiliate-toolkit-starter'),
										value: searchTerm,
										onChange: setSearchTerm,
										placeholder: __('Enter product name, ASIN, or EAN...', 'affiliate-toolkit-starter'),
										onKeyDown: (e) => {
											if (e.key === 'Enter') {
												searchExternalProducts();
											}
										},
										disabled: !selectedShop
									}),
									el(Button, {
										isPrimary: true,
										onClick: searchExternalProducts,
										disabled: !selectedShop || searchTerm.length < 2 || isSearching
									}, isSearching ? __('Searching...', 'affiliate-toolkit-starter') : __('Search', 'affiliate-toolkit-starter')),

									isSearching && el(Spinner),

									externalResults.length > 0 && el('div', {
										style: {
											marginTop: '12px',
											maxHeight: '300px',
											overflowY: 'auto',
											border: '1px solid #ddd',
											borderRadius: '4px'
										}
									},
										externalResults.map((product, index) =>
											el('div', {
												key: index,
												style: {
													padding: '12px',
													borderBottom: '1px solid #f0f0f0',
													display: 'flex',
													gap: '12px',
													alignItems: 'flex-start'
												}
											},
												product.imageurl && el('img', {
													src: product.imageurl,
													style: {
														width: '60px',
														height: '60px',
														objectFit: 'contain',
														flexShrink: 0
													}
												}),
												el('div', { style: { flex: 1, minWidth: 0 } },
													el('div', { 
														style: { 
															fontWeight: 'bold', 
															marginBottom: '4px',
															fontSize: '13px',
															lineHeight: '1.4'
														} 
													}, product.title),
													el('div', { style: { fontSize: '12px', color: '#666', marginBottom: '4px' } },
														'ID: ' + product.asin
													),
													product.price && el('div', { 
														style: { 
															fontSize: '13px', 
															color: '#2e7d32',
															fontWeight: '500',
															marginBottom: '8px'
														} 
													}, product.price),
													el(Button, {
														isPrimary: true,
														isSmall: true,
														onClick: () => importProduct(product),
														disabled: isImporting
													}, isImporting ? __('Importing...', 'affiliate-toolkit-starter') : __('Import & Select', 'affiliate-toolkit-starter'))
												)
											)
										)
									),

									externalResults.length === 0 && !isSearching && hasSearched && el('p', {
										style: {
											marginTop: '12px',
											padding: '12px',
											background: '#fff3cd',
											border: '1px solid #ffc107',
											borderRadius: '4px',
											fontSize: '13px'
										}
									}, __('No products found. Try a different search term.', 'affiliate-toolkit-starter'))
								)
							),

						!attributes.productId && el('p', {
							style: {
								marginTop: '12px',
								padding: '8px',
								background: '#f0f0f0',
								borderRadius: '4px',
								fontSize: '12px'
							}
						}, __('Or enter Product ID directly below:', 'affiliate-toolkit-starter')),

						el(TextControl, {
							label: __('Product ID (Manual)', 'affiliate-toolkit-starter'),
							value: attributes.productId,
							onChange: (value) => setAttributes({ productId: value, productTitle: '' }),
							help: __('Enter product ID if you know it', 'affiliate-toolkit-starter'),
							type: 'number'
						})
					),

					// Display Settings Panel
					el(PanelBody, {
						title: __('Display Settings', 'affiliate-toolkit-starter'),
						initialOpen: false
					},
						el(SelectControl, {
							label: __('Template', 'affiliate-toolkit-starter'),
							value: attributes.template,
							onChange: (value) => setAttributes({ template: value }),
							options: templateOptions
						}),

						el(SelectControl, {
							label: __('Button Type', 'affiliate-toolkit-starter'),
							value: attributes.buttonType,
							onChange: (value) => setAttributes({ buttonType: value }),
							options: [
								{ label: __('Default', 'affiliate-toolkit-starter'), value: '' },
								{ label: __('Add to Cart', 'affiliate-toolkit-starter'), value: 'addtocart' },
								{ label: __('Link', 'affiliate-toolkit-starter'), value: 'link' },
								{ label: __('Product Page', 'affiliate-toolkit-starter'), value: 'product' }
							]
						}),

						el(SelectControl, {
							label: __('Alignment', 'affiliate-toolkit-starter'),
							value: attributes.align,
							onChange: (value) => setAttributes({ align: value }),
							options: [
								{ label: __('No alignment', 'affiliate-toolkit-starter'), value: '' },
								{ label: __('Left', 'affiliate-toolkit-starter'), value: 'atkp-left atkp-clearfix' },
								{ label: __('Center', 'affiliate-toolkit-starter'), value: 'atkp-center' },
								{ label: __('Right', 'affiliate-toolkit-starter'), value: 'atkp-right atkp-clearfix' }
							]
						}),

						el(TextControl, {
							label: __('Custom Content', 'affiliate-toolkit-starter'),
							value: attributes.content,
							onChange: (value) => setAttributes({ content: value }),
							help: __('Optional custom content or link text', 'affiliate-toolkit-starter')
						}),

						el(ToggleControl, {
							label: __('Hide Disclaimer', 'affiliate-toolkit-starter'),
							checked: attributes.hideDisclaimer,
							onChange: (value) => setAttributes({ hideDisclaimer: value })
						})
					),

					// Advanced Settings Panel
					el(PanelBody, {
						title: __('Advanced Settings', 'affiliate-toolkit-starter'),
						initialOpen: false
					},
						el(TextControl, {
							label: __('Container CSS Class', 'affiliate-toolkit-starter'),
							value: attributes.containerCss,
							onChange: (value) => setAttributes({ containerCss: value }),
							help: __('Custom CSS classes for the container', 'affiliate-toolkit-starter')
						}),

						el(TextControl, {
							label: __('Element CSS Class', 'affiliate-toolkit-starter'),
							value: attributes.elementCss,
							onChange: (value) => setAttributes({ elementCss: value }),
							help: __('Custom CSS classes for elements', 'affiliate-toolkit-starter')
						}),

						el(TextControl, {
							label: __('Override Affiliate ID', 'affiliate-toolkit-starter'),
							value: attributes.trackingId,
							onChange: (value) => setAttributes({ trackingId: value }),
							help: __('Amazon or eBay tracking ID', 'affiliate-toolkit-starter')
						})
					)
				),

				// Block Editor Preview (no wrapper needed, blockProps handles it)
				el('div', {
					className: 'atkp-product-block-editor'
				},
					el('div', {
						className: 'atkp-block-placeholder'
					},
						el('h4', { style: { margin: '10px 0', color: '#333' } },
							__('affiliate-toolkit Product', 'affiliate-toolkit-starter')
						),
						attributes.productId
							? el('div', { style: { marginTop: '15px' } },
								el('p', { style: { margin: '5px 0', fontWeight: 'bold', color: '#005162', fontSize: '15px' } },
									decodeHtml(attributes.productTitle) || __('Product ID:', 'affiliate-toolkit-starter') + ' ' + attributes.productId
								),
								attributes.template && el('p', { style: { margin: '8px 0 5px', fontSize: '13px', color: '#666', background: '#e6f7f9', padding: '6px 10px', borderRadius: '4px', display: 'inline-block' } },
									'📋 ' + __('Template:', 'affiliate-toolkit-starter') + ' ' + attributes.template
								),
								el('p', { style: { margin: '10px 0 0', fontSize: '12px', color: '#999', fontStyle: 'italic' } },
									__('Output will be rendered on frontend', 'affiliate-toolkit-starter')
								)
							)
							: el('p', { style: { color: '#999', marginTop: '10px' } },
								__('Please select a product using the sidebar →', 'affiliate-toolkit-starter')
							)
					)
				)
			);
		},

		save: function() {
			return null; // Dynamic block rendered on server
		}
	});

})(
	window.wp.blocks,
	window.wp.element,
	window.wp.blockEditor,
	window.wp.components,
	window.wp.i18n
);
