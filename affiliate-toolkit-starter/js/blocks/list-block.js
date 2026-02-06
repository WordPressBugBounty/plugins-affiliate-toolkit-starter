/**
 * Affiliate Toolkit List Block
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
		const keyMap = {
			'affiliate-toolkit Product List': t.affiliateToolkitProductList || 'affiliate-toolkit Produktliste',
			'List Selection': t.listSelection || 'Listenauswahl',
			'Selected List:': t.selectedList || 'Ausgewählte Liste:',
			'Change List': t.changeList || 'Liste ändern',
			'Select': t.select || 'Auswählen',
			'Create': t.create || 'Erstellen',
			'Search existing lists:': t.searchExistingLists || 'Bestehende Listen suchen:',
			'Enter list name or ID...': t.enterListNameOrId || 'Listenname oder ID eingeben...',
			'Search': t.search || 'Suchen',
			'Showing 5 newest lists': t.showing5NewestLists || 'Zeige 5 neueste Listen',
			'Create new product list:': t.createNewProductList || 'Neue Produktliste erstellen:',
			'List Name': t.listName || 'Listenname',
			'Enter list name...': t.enterListName || 'Listenname eingeben...',
			'Give your list a descriptive name': t.giveListDescriptiveName || 'Geben Sie Ihrer Liste einen aussagekräftigen Namen',
			'Select Shop': t.selectShop || 'Shop auswählen',
			'-- Select Shop --': t.selectShopPlaceholder || '-- Shop auswählen --',
			'List Type': t.listType || 'Listentyp',
			'Top Seller': t.topSeller || 'Bestseller',
			'New Releases': t.newReleases || 'Neuerscheinungen',
			'Search Results': t.searchResults || 'Suchergebnisse',
			'Products matching search keyword': t.productsMatchingKeyword || 'Produkte passend zum Suchbegriff',
			'Category-based list (requires node/category ID)': t.categoryBasedList || 'Kategoriebasierte Liste (benötigt Node-/Kategorie-ID)',
			'Category/Node ID': t.categoryNodeId || 'Kategorie-/Node-ID',
			'e.g., "123456"': t.example123456 || 'z.B. "123456"',
			'Browse node or category ID from shop': t.browseNodeOrCategoryId || 'Browse-Node oder Kategorie-ID aus dem Shop',
			'Search Keyword': t.searchKeyword || 'Suchbegriff',
			'e.g., "laptop gaming"': t.exampleLaptopGaming || 'z.B. "Gaming Laptop"',
			'Keyword to search for products': t.keywordToSearch || 'Suchbegriff für Produkte',
			'Loading Preview...': t.loadingPreview || 'Vorschau wird geladen...',
			'Preview Results': t.previewResults || 'Ergebnisse anzeigen',
			'Preview (first 5 results):': t.previewFirst5 || 'Vorschau (erste 5 Ergebnisse):',
			'Creating...': t.creating || 'Wird erstellt...',
			'Create List': t.createList || 'Liste erstellen',
			'💡 The list will be created and automatically selected for this block.': t.listWillBeCreated || '💡 Die Liste wird erstellt und automatisch für diesen Block ausgewählt.',
			'Or enter List ID directly below:': t.orEnterListId || 'Oder geben Sie die Listen-ID direkt unten ein:',
			'List ID (Manual)': t.listIdManual || 'Listen-ID (Manuell)',
			'Enter list ID if you know it': t.enterListIdIfKnown || 'Listen-ID eingeben, falls bekannt',
			'Display Settings': t.displaySettings || 'Anzeigeeinstellungen',
			'Template': t.template || 'Template',
			'Limit': t.limit || 'Limit',
			'Number of products to display (0 = all)': t.numberOfProducts || 'Anzahl der anzuzeigenden Produkte (0 = alle)',
			'Random Sort': t.randomSort || 'Zufällige Sortierung',
			'Randomize product order on each page load': t.randomizeProductOrder || 'Produktreihenfolge bei jedem Seitenaufruf zufällig anordnen',
			'Button Type': t.buttonType || 'Button-Typ',
			'Default': t.default || 'Standard',
			'Add to Cart': t.addToCart || 'In den Warenkorb',
			'Link': t.link || 'Link',
			'Product Page': t.productPage || 'Produktseite',
			'Hide Disclaimer': t.hideDisclaimer || 'Disclaimer ausblenden',
			'Hide the affiliate disclaimer text': t.hideDisclaimerText || 'Affiliate-Disclaimer-Text ausblenden',
			'Advanced Settings': t.advancedSettings || 'Erweiterte Einstellungen',
			'Container CSS Class': t.containerCssClass || 'Container CSS-Klasse',
			'Custom CSS classes for the container': t.customCssContainer || 'Benutzerdefinierte CSS-Klassen für den Container',
			'Element CSS Class': t.elementCssClass || 'Element CSS-Klasse',
			'Custom CSS classes for elements': t.customCssElements || 'Benutzerdefinierte CSS-Klassen für Elemente',
			'Override Affiliate ID': t.overrideAffiliateId || 'Affiliate-ID überschreiben',
			'Amazon or eBay tracking ID': t.amazonEbayTrackingId || 'Amazon oder eBay Tracking-ID',
			'List ID:': t.listId || 'Listen-ID:',
			'Limit:': t.limitLabel || 'Limit:',
			'Template:': t.templateLabel || 'Template:',
			'products': t.products || 'Produkte',
			'Random sort enabled': t.randomSortEnabled || 'Zufällige Sortierung aktiviert',
			'Please select a list using the sidebar →': t.pleaseSelectList || 'Bitte wählen Sie eine Liste in der Seitenleiste aus →',
			'Please enter a list name': t.pleaseEnterListName || 'Bitte geben Sie einen Listennamen ein',
			'Please select a shop': t.pleaseSelectShop || 'Bitte wählen Sie einen Shop aus',
			'Please enter a search keyword': t.pleaseEnterSearchKeyword || 'Bitte geben Sie einen Suchbegriff ein',
			'Please enter a category/node ID': t.pleaseEnterCategoryNodeId || 'Bitte geben Sie eine Kategorie-/Node-ID ein',
			'Please select a shop first': t.pleaseSelectShopFirst || 'Bitte wählen Sie zuerst einen Shop aus',
			'List created successfully!': t.listCreatedSuccessfully || 'Liste erfolgreich erstellt!',
			'Failed to create list: ': t.failedToCreateList || 'Fehler beim Erstellen der Liste: ',
			'Failed to create list: Invalid response': t.failedToCreateListInvalidResponse || 'Fehler beim Erstellen der Liste: Ungültige Antwort',
			'Failed to create list: Invalid response format': t.failedToCreateListInvalidFormat || 'Fehler beim Erstellen der Liste: Ungültiges Antwortformat',
			'Error creating list': t.errorCreatingList || 'Fehler beim Erstellen der Liste'
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

	registerBlockType('atkp/list', {
		title: __('affiliate-toolkit Product List', 'affiliate-toolkit-starter'),
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
			listId: {
				type: 'string',
				default: ''
			},
			listTitle: {
				type: 'string',
				default: ''
			},
			template: {
				type: 'string',
				default: ''
			},
			limit: {
				type: 'number',
				default: 0
			},
			randomSort: {
				type: 'boolean',
				default: false
			},
			buttonType: {
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
			const [listType, setListType] = useState('10'); // 10 = Top Seller, 11 = New Releases, 20 = Search
			const [listName, setListName] = useState('');
			const [externalKeyword, setExternalKeyword] = useState('');
			const [externalResults, setExternalResults] = useState([]);
			const [isCreating, setIsCreating] = useState(false);


			// Get templates and shops from localized data
			const templateOptions = (typeof atkpBlocks !== 'undefined' && atkpBlocks.templates)
				? atkpBlocks.templates
				: [{ label: __('Default', 'affiliate-toolkit-starter'), value: '' }];

			const shopOptions = (typeof atkpBlocks !== 'undefined' && atkpBlocks.shops)
				? atkpBlocks.shops
				: [];

			// Load initial lists on mount
			useEffect(() => {
				loadInitialLists();
			}, []);

			// Load initial lists
			const loadInitialLists = () => {
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
						type: 'atkp_list',
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

			// Search lists
			const searchLists = () => {
				if (!searchTerm || searchTerm.length < 2) {
					loadInitialLists();
					return;
				}

				if (typeof atkpBlocks === 'undefined' || !atkpBlocks.ajaxurl) {
					return;
				}

				setIsSearching(true);

				console.log('ATKP: Searching lists with keyword:', searchTerm);

				jQuery.ajax({
					url: atkpBlocks.ajaxurl,
					type: 'POST',
					dataType: 'json',
					data: {
						action: 'atkp_block_search_products',
						type: 'atkp_list',
						keyword: searchTerm,
						limit: 20,
						nonce: atkpBlocks.nonce
					},
					success: function(response) {
						console.log('ATKP: List search response:', response);
						if (response.success && response.data && Array.isArray(response.data)) {
							console.log('ATKP: Found lists:', response.data.length);
							setSearchResults(response.data);
						} else {
							console.log('ATKP: No lists found or invalid response');
							setSearchResults([]);
						}
						setIsSearching(false);
					},
					error: function(xhr, status, error) {
						console.error('ATKP: List search error:', error);
						console.error('ATKP: XHR:', xhr);
						setIsSearching(false);
						setSearchResults([]);
					}
				});
			};

			// Select list from search
			const selectList = (list) => {
				setAttributes({
					listId: list.id.toString(),
					listTitle: decodeHtml(list.title)
				});
				setSearchResults([]);
				setSearchTerm('');
			};

			// Search external products for preview
			const searchExternalProducts = () => {
				if (!externalKeyword || externalKeyword.length < 2) {
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

				jQuery.ajax({
					url: atkpBlocks.ajaxurl,
					type: 'POST',
					dataType: 'json',
					data: {
						action: 'atkp_block_search_external',
						keyword: externalKeyword,
						shop_id: selectedShop,
						page: 1
					},
					success: function(response) {
						if (response.success && response.data && response.data.products) {
							setExternalResults(response.data.products);
						} else {
							setExternalResults([]);
						}
						setIsSearching(false);
					},
					error: function(xhr, status, error) {
						console.error('External search error:', error);
						setIsSearching(false);
						setExternalResults([]);
					}
				});
			};

			// Create list
			const createList = () => {
				if (!listName) {
					alert(__('Please enter a list name', 'affiliate-toolkit-starter'));
					return;
				}

				if (!selectedShop) {
					alert(__('Please select a shop', 'affiliate-toolkit-starter'));
					return;
				}

				if (listType === '20' && !externalKeyword) {
					alert(__('Please enter a search keyword', 'affiliate-toolkit-starter'));
					return;
				}

				if ((listType === '10' || listType === '11') && !externalKeyword) {
					alert(__('Please enter a category/node ID', 'affiliate-toolkit-starter'));
					return;
				}

				if (typeof atkpBlocks === 'undefined' || !atkpBlocks.ajaxurl) {
					return;
				}

				setIsCreating(true);

				// listType is already '10', '11', or '20' from the SelectControl
				// '10' = Top Seller, '11' = New Releases, '20' = Search Results

				jQuery.ajax({
					url: atkpBlocks.ajaxurl,
					type: 'POST',
					dataType: 'json',
					data: {
						action: 'atkp_create_list',
						request_nonce: atkpBlocks.nonce,
						shop: selectedShop,
						title: listName,
						listtype: listType,  // Pass numeric value: '10', '11', or '20'
						searchterm: externalKeyword,
						department: '',
						sortby: '',
						loadmoreoffers: false
					},
					success: function(response) {
						console.log('Create list response:', response);

						// atkp_create_list returns an array
						if (response && Array.isArray(response) && response.length > 0) {
							const result = response[0];
							if (result.postid) {
								// Use listName as fallback if result.title is empty
								// Decode HTML entities in both cases
								const listTitle = result.title && result.title.trim() !== ''
									? decodeHtml(result.title)
									: listName;

								setAttributes({
									listId: result.postid.toString(),
									listTitle: listTitle
								});
								setListName('');
								setExternalKeyword('');
								setExternalResults([]);
								setSearchMode('internal');
								alert(__('List created successfully!', 'affiliate-toolkit-starter'));
							} else if (result.error) {
								alert(__('Failed to create list: ', 'affiliate-toolkit-starter') + result.message);
							} else {
								alert(__('Failed to create list: Invalid response', 'affiliate-toolkit-starter'));
							}
						} else if (response && response.error) {
							alert(__('Failed to create list: ', 'affiliate-toolkit-starter') + response.message);
						} else {
							alert(__('Failed to create list: Invalid response format', 'affiliate-toolkit-starter'));
						}
						setIsCreating(false);
					},
					error: function(xhr, status, error) {
						console.error('Create list error:', error);
						console.error('XHR:', xhr);
						alert(__('Error creating list', 'affiliate-toolkit-starter'));
						setIsCreating(false);
					}
				});
			};

			return el('div', blockProps,
				el(InspectorControls, {},
					// List Selection Panel
					el(PanelBody, {
						title: __('List Selection', 'affiliate-toolkit-starter'),
						initialOpen: true
					},
						attributes.listId
							? el('div', { className: 'atkp-selected-list' },
								el('p', { style: { marginBottom: '8px', fontWeight: 'bold' } },
									__('Selected List:', 'affiliate-toolkit-starter')
								),
								el('p', { style: { marginBottom: '8px' } },
									decodeHtml(attributes.listTitle) + ' (ID: ' + attributes.listId + ')'
								),
								el(Button, {
									isSecondary: true,
									onClick: () => setAttributes({ listId: '', listTitle: '' })
								}, __('Change List', 'affiliate-toolkit-starter'))
							)
							: el('div', { className: 'atkp-list-search' },
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
									}, __('Create', 'affiliate-toolkit-starter'))
								),

								// Internal search mode
								searchMode === 'internal' && el('div', {},
									el('p', { style: { marginBottom: '8px' } },
										__('Search existing lists:', 'affiliate-toolkit-starter')
									),
									el('div', { style: { display: 'flex', gap: '8px', marginBottom: '8px' } },
										el(TextControl, {
											value: searchTerm,
											onChange: setSearchTerm,
											placeholder: __('Enter list name or ID...', 'affiliate-toolkit-starter'),
											onKeyDown: (e) => {
												if (e.key === 'Enter') {
													searchLists();
												}
											}
										})
									),
									el(Button, {
										isPrimary: true,
										onClick: searchLists,
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
										searchResults.map(list =>
											el('div', {
												key: list.id,
												onClick: () => selectList(list),
												style: {
													padding: '8px',
													cursor: 'pointer',
													borderBottom: '1px solid #f0f0f0'
												},
											onMouseEnter: (e) => e.currentTarget.style.background = '#f5f5f5',
											onMouseLeave: (e) => e.currentTarget.style.background = 'transparent'
										},
											el('div', { style: { fontWeight: 'bold', marginBottom: '4px' } },
												decodeHtml(list.title)
											),
												el('div', { style: { fontSize: '12px', color: '#666' } },
													'ID: ' + list.id
												)
											)
										)
									),

									!searchTerm && searchResults.length > 0 && el('p', {
										style: {
											marginTop: '8px',
											fontSize: '12px',
											color: '#666',
											textAlign: 'center'
										}
									}, __('Showing 5 newest lists', 'affiliate-toolkit-starter'))
								),

								// External create mode
								searchMode === 'external' && el('div', {},
									el('p', { style: { marginBottom: '12px', fontWeight: '500' } },
										__('Create new product list:', 'affiliate-toolkit-starter')
									),
									
									el(TextControl, {
										label: __('List Name', 'affiliate-toolkit-starter'),
										value: listName,
										onChange: setListName,
										placeholder: __('Enter list name...', 'affiliate-toolkit-starter'),
										help: __('Give your list a descriptive name', 'affiliate-toolkit-starter')
									}),

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

									el(SelectControl, {
										label: __('List Type', 'affiliate-toolkit-starter'),
										value: listType,
										onChange: (value) => {
											setListType(value);
											setExternalKeyword('');
											setExternalResults([]);
										},
										options: [
											{ label: __('Top Seller', 'affiliate-toolkit-starter'), value: '10' },
											{ label: __('New Releases', 'affiliate-toolkit-starter'), value: '11' },
											{ label: __('Search Results', 'affiliate-toolkit-starter'), value: '20' }
										],
										help: listType === '20' 
											? __('Products matching search keyword', 'affiliate-toolkit-starter')
											: __('Category-based list (requires node/category ID)', 'affiliate-toolkit-starter')
									}),

									el(TextControl, {
										label: listType === '20' 
											? __('Search Keyword', 'affiliate-toolkit-starter')
											: __('Category/Node ID', 'affiliate-toolkit-starter'),
										value: externalKeyword,
										onChange: setExternalKeyword,
										placeholder: listType === '20'
											? __('e.g., "laptop gaming"', 'affiliate-toolkit-starter')
											: __('e.g., "123456"', 'affiliate-toolkit-starter'),
										help: listType === '20'
											? __('Keyword to search for products', 'affiliate-toolkit-starter')
											: __('Browse node or category ID from shop', 'affiliate-toolkit-starter')
									}),

									// Preview button for search type
									listType === '20' && el('div', { style: { marginBottom: '12px' } },
										el(Button, {
											isSecondary: true,
											onClick: searchExternalProducts,
											disabled: !selectedShop || !externalKeyword || externalKeyword.length < 2 || isSearching
										}, isSearching ? __('Loading Preview...', 'affiliate-toolkit-starter') : __('Preview Results', 'affiliate-toolkit-starter')),
										
										isSearching && el(Spinner)
									),

									// Preview results
									externalResults.length > 0 && el('div', {
										style: {
											marginTop: '12px',
											marginBottom: '12px',
											padding: '12px',
											background: '#f8f9fa',
											border: '1px solid #dee2e6',
											borderRadius: '4px',
											maxHeight: '200px',
											overflowY: 'auto'
										}
									},
										el('p', { style: { fontSize: '12px', fontWeight: 'bold', marginBottom: '8px', color: '#495057' } },
											__('Preview (first 5 results):', 'affiliate-toolkit-starter')
										),
										externalResults.slice(0, 5).map((product, index) =>
											el('div', {
												key: index,
												style: {
													padding: '6px 0',
													borderBottom: index < 4 ? '1px solid #e9ecef' : 'none',
													display: 'flex',
													gap: '8px',
													alignItems: 'center'
												}
											},
												product.imageurl && el('img', {
													src: product.imageurl,
													style: {
														width: '40px',
														height: '40px',
														objectFit: 'contain',
														flexShrink: 0
													}
												}),
												el('div', { 
													style: { 
														fontSize: '12px',
														flex: 1,
														minWidth: 0,
														overflow: 'hidden',
														textOverflow: 'ellipsis',
														whiteSpace: 'nowrap'
													} 
												}, product.title)
											)
										)
									),

									// Create button
									el('div', { style: { marginTop: '16px', paddingTop: '12px', borderTop: '1px solid #ddd' } },
										el(Button, {
											isPrimary: true,
											onClick: createList,
											disabled: !listName || !selectedShop || !externalKeyword || isCreating,
											style: { marginRight: '8px' }
										}, isCreating ? __('Creating...', 'affiliate-toolkit-starter') : __('Create List', 'affiliate-toolkit-starter')),

										isCreating && el(Spinner)
									),

									el('p', {
										style: {
											marginTop: '12px',
											padding: '8px',
											background: '#e3f2fd',
											border: '1px solid #2196f3',
											borderRadius: '4px',
											fontSize: '12px',
											color: '#1565c0'
										}
									}, __('💡 The list will be created and automatically selected for this block.', 'affiliate-toolkit-starter'))
								)
							),

						!attributes.listId && el('p', {
							style: {
								marginTop: '12px',
								padding: '8px',
								background: '#f0f0f0',
								borderRadius: '4px',
								fontSize: '12px'
							}
						}, __('Or enter List ID directly below:', 'affiliate-toolkit-starter')),

						el(TextControl, {
							label: __('List ID (Manual)', 'affiliate-toolkit-starter'),
							value: attributes.listId,
							onChange: (value) => setAttributes({ listId: value, listTitle: '' }),
							help: __('Enter list ID if you know it', 'affiliate-toolkit-starter'),
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

						el(TextControl, {
							label: __('Limit', 'affiliate-toolkit-starter'),
							type: 'number',
							value: attributes.limit,
							onChange: (value) => setAttributes({ limit: parseInt(value) || 0 }),
							help: __('Number of products to display (0 = all)', 'affiliate-toolkit-starter')
						}),

						el(ToggleControl, {
							label: __('Random Sort', 'affiliate-toolkit-starter'),
							checked: attributes.randomSort,
							onChange: (value) => setAttributes({ randomSort: value }),
							help: __('Randomize product order on each page load', 'affiliate-toolkit-starter')
						}),

						el(ToggleControl, {
							label: __('Hide Disclaimer', 'affiliate-toolkit-starter'),
							checked: attributes.hideDisclaimer,
							onChange: (value) => setAttributes({ hideDisclaimer: value }),
							help: __('Hide the affiliate disclaimer text', 'affiliate-toolkit-starter')
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
					className: 'atkp-list-block-editor'
				},
					el('div', {
						className: 'atkp-block-placeholder'
					},
						el('h4', { style: { margin: '10px 0' } },
							__('affiliate-toolkit Product List', 'affiliate-toolkit-starter')
						),
						attributes.listId
							? el('div', {},
								el('p', { style: { margin: '5px 0', fontWeight: 'bold', color: '#005162', fontSize: '15px' } },
									decodeHtml(attributes.listTitle) || __('List ID:', 'affiliate-toolkit-starter') + ' ' + attributes.listId
								),
								attributes.template && el('p', { style: { margin: '5px 0', fontSize: '12px', color: '#666' } },
									__('Template:', 'affiliate-toolkit-starter') + ' ' + attributes.template
								),
								attributes.limit > 0 && el('p', { style: { margin: '5px 0', fontSize: '12px', color: '#666' } },
									__('Limit:', 'affiliate-toolkit-starter') + ' ' + attributes.limit + ' ' + __('products', 'affiliate-toolkit-starter')
								),
								attributes.randomSort && el('p', { style: { margin: '5px 0', fontSize: '12px', color: '#666' } },
									'🔀 ' + __('Random sort enabled', 'affiliate-toolkit-starter')
								)
							)
							: el('p', { style: { color: '#999' } },
								__('Please select a list using the sidebar →', 'affiliate-toolkit-starter')
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
