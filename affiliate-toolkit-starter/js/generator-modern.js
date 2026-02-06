/**
 * Modern Shortcode Generator JavaScript
 * @since 3.0.0
 */

(function($) {
	'use strict';

	const ATKPGenerator = {
		currentStep: 1,
		maxStep: 3,
		outputType: '',
		sourceType: '',
		selectedId: '',
		selectedTitle: '',

		init: function() {
			this.bindEvents();
		},

		bindEvents: function() {
			const self = this;

			// Open modal
			$(document).on('click', '.atkp-generator-button, .mce-atkp_button', function(e) {
				e.preventDefault();
				self.openModal();
			});

			// Close modal
			$(document).on('click', '.atkp-modal-close, .atkp-modal-overlay', function() {
				self.closeModal();
			});

			// Navigation buttons
			$('#atkp-btn-next').on('click', function() {
				self.nextStep();
			});

			$('#atkp-btn-back').on('click', function() {
				self.prevStep();
			});

			// Output type selection - auto advance to next step
			$(document).on('click', '.atkp-option-card[data-type], .atkp-option-card[data-output]', function() {
				$('.atkp-option-card[data-type], .atkp-option-card[data-output]').removeClass('selected');
				$(this).addClass('selected');
				self.outputType = $(this).data('type') || $(this).data('output');

				// Auto advance to next step
				setTimeout(function() {
					self.nextStep();
				}, 300);
			});

			// Source type selection - show form but don't auto advance
			$(document).on('click', '.atkp-option-card[data-source]', function() {
				$('.atkp-option-card[data-source]').removeClass('selected');
				$(this).addClass('selected');
				self.sourceType = $(this).data('source');
				self.showSourceForm();
			});

			// Search products
			$('#atkp-search-product-btn').on('click', function() {
				self.searchProducts();
			});

			$('#atkp-search-product-input').on('keypress', function(e) {
				if (e.which === 13) {
					e.preventDefault();
					self.searchProducts();
				}
			});

			// Search lists
			$('#atkp-search-list-btn').on('click', function() {
				self.searchLists();
			});

			$('#atkp-search-list-input').on('keypress', function(e) {
				if (e.which === 13) {
					e.preventDefault();
					self.searchLists();
				}
			});

			// Import external products
			$('#atkp-create-product-search').on('click', function() {
				self.searchExternalProducts();
			});

			$('#atkp-create-product-keyword').on('keypress', function(e) {
				if (e.which === 13) {
					e.preventDefault();
					self.searchExternalProducts();
				}
			});

			// Import product from external source
			$(document).on('click', '.atkp-import-product-btn', function(e) {
				// Prevent event bubbling to parent .atkp-result-item
				e.stopPropagation();

				const asin = $(this).data('asin');
				const shopId = $(this).data('shop-id');
				self.importProduct(asin, shopId, $(this));
			});

			// Create list
			$('#atkp-create-list-btn').on('click', function() {
				self.createList();
			});

			// Select search result (only for already imported products, not for products with import button)
			$(document).on('click', '.atkp-result-item', function(e) {
				// Don't proceed if clicking on a button or if this item has an import button
				if ($(e.target).is('button') || $(e.target).closest('button').length > 0) {
					return;
				}

				// Only auto-advance if the product is already imported (has an ID)
				const productId = $(this).data('id');
				if (!productId || productId === 'null' || productId === '') {
					// Product not imported yet, don't auto-advance
					return;
				}

				self.selectedId = productId;
				self.selectedTitle = $(this).data('title');

				$('.atkp-result-item').removeClass('selected');
				$(this).addClass('selected');

				// Auto advance to config after selection (only for already imported products)
				setTimeout(function() {
					self.nextStep();
				}, 300);
			});

			// Config tabs
			$(document).on('click', '.atkp-tab-btn', function() {
				const tab = $(this).data('tab');
				console.log('Tab clicked:', tab);
				$('.atkp-tab-btn').removeClass('active');
				$(this).addClass('active');
				$('.atkp-tab-content').removeClass('active');
				$('.atkp-tab-content[data-tab="' + tab + '"]').addClass('active');
				console.log('Tab content active:', $('.atkp-tab-content[data-tab="' + tab + '"]').length);
			});

			// Update shortcode on config change
			$(document).on('change', '[id^="atkp-config-"], [id^="atkp_"]', function() {
				console.log('Config changed:', $(this).attr('id'), '=', $(this).val());
				self.updateShortcode();
			});

			// Additional listeners for Select2 fields
			$(document).on('select2:select select2:unselect', '[id^="atkp-config-"], [id^="atkp_"]', function() {
				console.log('Select2 changed:', $(this).attr('id'), '=', $(this).val());
				self.updateShortcode();
			});

			// Listen to all input changes in the configuration area (text, number, checkbox)
			$(document).on('input change keyup', '.atkp-tab-content input[type="text"], .atkp-tab-content input[type="number"], .atkp-tab-content input[type="checkbox"], .atkp-tab-content select:not([class*="select2"])', function() {
				console.log('Input changed:', $(this).attr('id'), '=', $(this).val());
				self.updateShortcode();
			});

			// Debug: Log template dropdown when it's interacted with
			$(document).on('focus', '#atkp-config-template', function() {
				console.log('Template dropdown focused, options:', $(this).find('option').length);
			});

			// Copy shortcode
			$('#atkp-copy-shortcode').on('click', function() {
				self.copyShortcode();
			});

			// Insert shortcode
			$('#atkp-btn-insert').on('click', function() {
				self.insertShortcode();
			});

			// Escape key to close
			$(document).on('keydown', function(e) {
				if (e.key === 'Escape' && $('.atkp-modal').is(':visible')) {
					self.closeModal();
				}
			});
		},

		openModal: function() {
			$('#atkp-generator-modal').fadeIn(300);
			$('body').addClass('atkp-modal-open');
			this.resetModal();
		},

		closeModal: function() {
			$('#atkp-generator-modal').fadeOut(300);
			$('body').removeClass('atkp-modal-open');
		},

		resetModal: function() {
			this.currentStep = 1;
			this.outputType = '';
			this.sourceType = '';
			this.selectedId = '';
			this.selectedTitle = '';

			$('.atkp-option-card').removeClass('selected');
			$('.atkp-step').removeClass('active completed');
			$('.atkp-step[data-step="1"]').addClass('active');
			$('.atkp-step-content').hide();
			$('.atkp-step-content[data-step="1"]').show();

			$('#atkp-btn-back').hide();
			$('#atkp-btn-next').show();
			$('#atkp-btn-insert').hide();

			this.resetForms();
		},

		resetForms: function() {
			$('#atkp-shortcode-output').val('');
			$('[id^="atkp-config-"]').val('').prop('checked', false);
		},

		nextStep: function() {
			if (this.currentStep === 1 && !this.outputType) {
				alert('Bitte wählen Sie einen Output-Typ');
				return;
			}

			// Skip step 2 for searchform and dynamicfilter types (they don't need a source)
			if (this.currentStep === 1 && (this.outputType === 'searchform' || this.outputType === 'dynamicfilter')) {
				$('.atkp-step[data-step="1"]').removeClass('active').addClass('completed');
				$('.atkp-step[data-step="2"]').addClass('completed'); // Skip step 2
				this.currentStep = 3;
				$('.atkp-step[data-step="3"]').addClass('active');
				$('.atkp-step-content').hide();
				$('.atkp-step-content[data-step="3"]').show();

				this.selectedTitle = ''; // No selection needed
				this.showConfiguration();
				$('#atkp-btn-next').hide();
				$('#atkp-btn-insert').show();
				$('#atkp-btn-back').show();
				return;
			}

			if (this.currentStep < this.maxStep) {
				$('.atkp-step[data-step="' + this.currentStep + '"]').removeClass('active').addClass('completed');
				this.currentStep++;
				$('.atkp-step[data-step="' + this.currentStep + '"]').addClass('active');
				$('.atkp-step-content').hide();
				$('.atkp-step-content[data-step="' + this.currentStep + '"]').show();

				if (this.currentStep === 2) {
					// Reset and hide all forms when entering step 2
					$('.atkp-search-form, .atkp-create-form').hide();
					$('#atkp-search-product-results, #atkp-search-list-results, #atkp-create-product-results').empty();
					this.showDataSourceOptions();
				}

				if (this.currentStep === 3) {
					this.showConfiguration();
					$('#atkp-btn-next').hide();
					$('#atkp-btn-insert').show();
				}

				$('#atkp-btn-back').show();
			}
		},

		prevStep: function() {
			if (this.currentStep > 1) {
				// If on step 3 with searchform/dynamicfilter, skip back to step 1
				if (this.currentStep === 3 && (this.outputType === 'searchform' || this.outputType === 'dynamicfilter')) {
					$('.atkp-step[data-step="3"]').removeClass('active');
					$('.atkp-step[data-step="2"]').removeClass('completed');
					this.currentStep = 1;
					$('.atkp-step[data-step="1"]').addClass('active').removeClass('completed');
					$('.atkp-step-content').hide();
					$('.atkp-step-content[data-step="1"]').show();

					$('#atkp-btn-back').hide();
					this.outputType = '';
					this.sourceType = '';
					this.selectedId = '';
					this.selectedTitle = '';
					$('.atkp-option-card').removeClass('selected');

					$('#atkp-btn-next').show();
					$('#atkp-btn-insert').hide();
					return;
				}

				$('.atkp-step[data-step="' + this.currentStep + '"]').removeClass('active');
				this.currentStep--;
				$('.atkp-step[data-step="' + this.currentStep + '"]').addClass('active').removeClass('completed');
				$('.atkp-step-content').hide();
				$('.atkp-step-content[data-step="' + this.currentStep + '"]').show();

				if (this.currentStep === 1) {
					$('#atkp-btn-back').hide();
					// Reset output type when going back to step 1
					this.outputType = '';
					this.sourceType = '';
					this.selectedId = '';
					this.selectedTitle = '';
					$('.atkp-option-card').removeClass('selected');
				}

				if (this.currentStep === 2) {
					// Reset source selection when going back to step 2
					this.sourceType = '';
					this.selectedId = '';
					this.selectedTitle = '';
					$('.atkp-option-card[data-source]').removeClass('selected');
					$('.atkp-search-form, .atkp-create-form').hide();
					$('#atkp-search-product-results, #atkp-search-list-results, #atkp-create-product-results').empty();
				}

				$('#atkp-btn-next').show();
				$('#atkp-btn-insert').hide();
			}
		},

		showDataSourceOptions: function() {
			$('.atkp-source-type').hide();

			if (this.outputType === 'product' || this.outputType === 'field' || this.outputType === 'link') {
				$('.atkp-source-type[data-source-for="product"]').show();
			} else if (this.outputType === 'list') {
				$('.atkp-source-type[data-source-for="list"]').show();
			}
		},

		showSourceForm: function() {
			// Hide all forms first
			$('.atkp-search-form, .atkp-create-form').hide();

			// Show the appropriate form based on source type
			if (this.sourceType === 'search-product') {
				$('.atkp-search-form[data-search-type="product"]').show();
				$('#atkp-search-product-input').focus();
			} else if (this.sourceType === 'search-list') {
				$('.atkp-search-form[data-search-type="list"]').show();
				$('#atkp-search-list-input').focus();
			} else if (this.sourceType === 'create-product') {
				$('.atkp-create-form[data-create-type="product"]').show();
				$('#atkp-create-product-keyword').focus();
			} else if (this.sourceType === 'create-list') {
				$('.atkp-create-form[data-create-type="list"]').show();
				$('#atkp-create-list-name').focus();
			}
		},

		searchProducts: function() {
			const keyword = $('#atkp-search-product-input').val();
			const self = this;

			$('#atkp-search-product-results').html('<div class="atkp-loading">' + (atkpGenerator.i18n.loading || 'Loading...') + '</div>');

			$.ajax({
				url: atkpGenerator.ajaxurl,
				type: 'POST',
				data: {
					action: 'atkp_block_search_products',
					keyword: keyword,
					type: 'atkp_product',
					limit: 20,
					nonce: atkpGenerator.nonce
				},
				success: function(response) {
					console.log('Search response:', response);
					if (response.success && response.data && response.data.length > 0) {
						let html = '';
						response.data.forEach(function(item) {
							html += '<div class="atkp-result-item" data-id="' + item.id + '" data-title="' + item.title + '">';
							// Add thumbnail if available
							if (item.imageurl) {
								html += '<div class="atkp-result-thumbnail">';
								html += '<img src="' + item.imageurl + '" alt="' + item.title + '">';
								html += '</div>';
							}

							html += '<div class="atkp-result-info">';
							html += '<div class="atkp-result-title">';
							html += item.title;

							// Add edit link icon
							if (item.edit_url) {
								html += ' <a href="' + item.edit_url + '" target="_blank" class="atkp-edit-link" title="Produkt bearbeiten" onclick="event.stopPropagation();">';
								html += '<span class="dashicons dashicons-external"></span>';
								html += '</a>';
							}

							html += '</div>';
							html += '<div class="atkp-result-meta">ID: ' + item.id + '</div>';
							html += '</div>';
							html += '</div>';
						});
						$('#atkp-search-product-results').html(html);
					} else {
						$('#atkp-search-product-results').html('<p style="padding:15px;text-align:center;color:#666;">Keine Produkte gefunden</p>');
					}
				},
				error: function(xhr, status, error) {
					console.error('Search error:', xhr, status, error);
					$('#atkp-search-product-results').html('<p style="padding:15px;text-align:center;color:#d63638;">Fehler bei der Suche: ' + error + '</p>');
				}
			});
		},

		searchLists: function() {
			const keyword = $('#atkp-search-list-input').val();
			const self = this;

			$('#atkp-search-list-results').html('<div class="atkp-loading">' + (atkpGenerator.i18n.loading || 'Loading...') + '</div>');

			$.ajax({
				url: atkpGenerator.ajaxurl,
				type: 'POST',
				data: {
					action: 'atkp_block_search_products',
					keyword: keyword,
					type: 'atkp_list',
					limit: 20,
					nonce: atkpGenerator.nonce
				},
				success: function(response) {
					console.log('Search response:', response);
					if (response.success && response.data && response.data.length > 0) {
						let html = '';
						response.data.forEach(function(item) {
							html += '<div class="atkp-result-item" data-id="' + item.id + '" data-title="' + item.title + '">';
							html += '<div class="atkp-result-info">';
							html += '<div class="atkp-result-title">';
							html += item.title;

							// Add edit link icon
							if (item.edit_url) {
								html += ' <a href="' + item.edit_url + '" target="_blank" class="atkp-edit-link" title="Liste bearbeiten" onclick="event.stopPropagation();">';
								html += '<span class="dashicons dashicons-external"></span>';
								html += '</a>';
							}

							html += '</div>';
							html += '<div class="atkp-result-meta">ID: ' + item.id + '</div>';
							html += '</div>';
							html += '</div>';
						});
						$('#atkp-search-list-results').html(html);
					} else {
						$('#atkp-search-list-results').html('<p style="padding:15px;text-align:center;color:#666;">Keine Listen gefunden</p>');
					}
				},
				error: function(xhr, status, error) {
					console.error('Search error:', xhr, status, error);
					$('#atkp-search-list-results').html('<p style="padding:15px;text-align:center;color:#d63638;">Fehler bei der Suche: ' + error + '</p>');
				}
			});
		},

		searchExternalProducts: function() {
			const keyword = $('#atkp-create-product-keyword').val();
			const shopId = $('#atkp-create-product-shop').val();
			const self = this;

			if (!keyword) {
				alert('Bitte Suchbegriff eingeben');
				return;
			}

			$('#atkp-create-product-results').html('<div class="atkp-loading">Suche externe Produkte...</div>');

			$.ajax({
				url: atkpGenerator.ajaxurl,
				type: 'POST',
				data: {
					action: 'atkp_block_search_external',
					keyword: keyword,
					shop_id: shopId,
					page: 1,
					nonce: atkpGenerator.nonce
				},
				success: function(response) {
					console.log('External search response:', response);
					if (response.success && response.data && response.data.products && response.data.products.length > 0) {
						let html = '';
						response.data.products.forEach(function(item) {
							html += '<div class="atkp-result-item">';

							// Add thumbnail
							if (item.imageurl) {
								html += '<div class="atkp-result-thumbnail">';
								html += '<img src="' + item.imageurl + '" alt="' + item.title + '">';
								html += '</div>';
							}

							html += '<div class="atkp-result-info">';
							html += '<div class="atkp-result-title">' + item.title + '</div>';
							html += '<div class="atkp-result-meta">ASIN: ' + item.asin;
							if (item.price) {
								html += ' | ' + item.price;
							}
							html += '</div>';
							html += '</div>';
							html += '<button type="button" class="button button-primary atkp-import-product-btn" data-asin="' + item.asin + '" data-shop-id="' + item.shop_id + '">Importieren</button>';
							html += '</div>';
						});
						$('#atkp-create-product-results').html(html);
					} else {
						$('#atkp-create-product-results').html('<p style="padding:15px;text-align:center;color:#666;">Keine Produkte gefunden</p>');
					}
				},
				error: function(xhr, status, error) {
					console.error('External search error:', xhr, status, error);
					$('#atkp-create-product-results').html('<p style="padding:15px;text-align:center;color:#d63638;">Fehler bei der Suche: ' + error + '</p>');
				}
			});
		},

		importProduct: function(asin, shopId, $button) {
			const self = this;
			const originalText = $button.text();

			// Debug: Log all available objects and nonces
			console.log('=== IMPORT PRODUCT DEBUG ===');
			console.log('atkpGenerator:', typeof atkpGenerator !== 'undefined' ? atkpGenerator : 'UNDEFINED');
			console.log('atkpBlocks:', typeof atkpBlocks !== 'undefined' ? atkpBlocks : 'UNDEFINED');

			// Check for importNonce in atkpGenerator
			if (typeof atkpGenerator !== 'undefined') {
				console.log('atkpGenerator.nonce:', atkpGenerator.nonce);
				console.log('atkpGenerator.importNonce:', atkpGenerator.importNonce);
				console.log('atkpGenerator.restNonce:', atkpGenerator.restNonce);
			}

			// Check for nonce in atkpBlocks
			if (typeof atkpBlocks !== 'undefined') {
				console.log('atkpBlocks.nonce:', atkpBlocks.nonce);
				console.log('atkpBlocks.restNonce:', atkpBlocks.restNonce);
			}

			// Priority order: Try multiple nonce sources
			let importNonce = null;
			let nonceSource = '';

			// 1. Try atkpGenerator.importNonce (most specific)
			if (typeof atkpGenerator !== 'undefined' && atkpGenerator.importNonce) {
				importNonce = atkpGenerator.importNonce;
				nonceSource = 'atkpGenerator.importNonce';
			}
			// 2. Try atkpBlocks.nonce (Gutenberg context)
			else if (typeof atkpBlocks !== 'undefined' && atkpBlocks.nonce) {
				importNonce = atkpBlocks.nonce;
				nonceSource = 'atkpBlocks.nonce';
			}
			// 3. Try atkpGenerator.restNonce (REST API nonce - longer lifetime)
			else if (typeof atkpGenerator !== 'undefined' && atkpGenerator.restNonce) {
				importNonce = atkpGenerator.restNonce;
				nonceSource = 'atkpGenerator.restNonce (REST API)';
			}
			// 4. Try atkpBlocks.restNonce
			else if (typeof atkpBlocks !== 'undefined' && atkpBlocks.restNonce) {
				importNonce = atkpBlocks.restNonce;
				nonceSource = 'atkpBlocks.restNonce (REST API)';
			}
			// 5. Try atkpGenerator.nonce (general fallback)
			else if (typeof atkpGenerator !== 'undefined' && atkpGenerator.nonce) {
				importNonce = atkpGenerator.nonce;
				nonceSource = 'atkpGenerator.nonce (GENERAL FALLBACK)';
			}

			console.log('Selected nonce source:', nonceSource);
			console.log('Selected nonce value:', importNonce);
			console.log('=== END DEBUG ===');

			if (!importNonce) {
				alert('Fehler: Keine Nonce gefunden. Bitte laden Sie die Seite neu (Strg+Shift+R).\n\nWenn das Problem weiterhin besteht, leeren Sie bitte den Browser-Cache.');
				console.error('CRITICAL: No nonce available!');
				return;
			}


			$button.prop('disabled', true).text('Importiere...');

			const requestData = {
				action: 'atkp_import_product',
				asin: asin,
				shop: shopId,
				asintype: 'ASIN',
				title: '',
				status: 'publish',
				request_nonce: importNonce
			};

			console.log('Import request data:', requestData);

			$.ajax({
				url: atkpGenerator.ajaxurl,
				type: 'POST',
				data: requestData,
				success: function(response) {
					console.log('Import response:', response);
					console.log('Response type:', typeof response);
					console.log('Is array:', Array.isArray(response));

					// Check if response is an array and has data
					if (Array.isArray(response) && response.length > 0) {
						const data = response[0];
						console.log('Response data:', data);

						// Check for error
						if (data.error) {
							alert('Fehler beim Import: ' + (data.message || data.error));
							$button.prop('disabled', false).text(originalText);
							return;
						}

						// Success
						if (data.postid) {
							self.selectedId = data.postid;
							self.selectedTitle = data.title;

							$button.text('Importiert!').removeClass('button-primary').addClass('button-secondary');

							// Auto advance to config after import
							setTimeout(function() {
								self.nextStep();
							}, 500);
						} else {
							alert('Fehler beim Import: Keine Produkt-ID erhalten');
							$button.prop('disabled', false).text(originalText);
						}
					} else {
						console.error('Invalid response format:', response);
						alert('Fehler beim Import: Ungültige Antwort vom Server. Siehe Browser-Konsole für Details.');
						$button.prop('disabled', false).text(originalText);
					}
				},
				error: function(xhr, status, error) {
					console.error('Import error - Status:', status);
					console.error('Import error - Error:', error);
					console.error('Import error - Response:', xhr.responseText);
					alert('Fehler beim Import: ' + error + '\nDetails in der Browser-Konsole.');
					$button.prop('disabled', false).text(originalText);
				}
			});
		},

		createList: function() {
			const self = this;
			const listName = $('#atkp-create-list-name').val().trim();
			const shopId = $('#atkp-create-list-shop').val();
			const listType = $('#atkp-create-list-type').val();
			const keyword = $('#atkp-create-list-keyword').val().trim();

			if (!listName) {
				alert('Bitte geben Sie einen Listennamen ein.');
				return;
			}

			if (!shopId) {
				alert('Bitte wählen Sie einen Shop aus.');
				return;
			}

			const $button = $('#atkp-create-list-btn');
			const originalText = $button.text();

			$button.prop('disabled', true).text('Erstelle Liste...');

			// Get the appropriate nonce (same as for import)
			let createNonce = null;
			if (typeof atkpGenerator !== 'undefined' && atkpGenerator.importNonce) {
				createNonce = atkpGenerator.importNonce;
			} else if (typeof atkpBlocks !== 'undefined' && atkpBlocks.nonce) {
				createNonce = atkpBlocks.nonce;
			} else if (typeof atkpGenerator !== 'undefined' && atkpGenerator.restNonce) {
				createNonce = atkpGenerator.restNonce;
			} else if (typeof atkpGenerator !== 'undefined' && atkpGenerator.nonce) {
				createNonce = atkpGenerator.nonce;
			}

			$.ajax({
				url: atkpGenerator.ajaxurl,
				type: 'POST',
				data: {
					action: 'atkp_create_list',
					title: listName,
					shop: shopId,
					listtype: listType,
					searchterm: keyword,
					request_nonce: createNonce
				},
				success: function(response) {
					console.log('Create list response:', response);
					console.log('Response type:', typeof response);
					console.log('Is array:', Array.isArray(response));

					// Check if response is an array and has data
					if (Array.isArray(response) && response.length > 0) {
						const data = response[0];
						console.log('Response data:', data);

						// Check for error
						if (data.error) {
							alert('Fehler beim Erstellen der Liste: ' + (data.message || data.error));
							$button.prop('disabled', false).text(originalText);
							return;
						}

						// Success
						if (data.postid) {
							self.selectedId = data.postid;
							self.selectedTitle = data.title;

							$button.text('Liste erstellt!').removeClass('button-primary').addClass('button-secondary');

							// Auto advance to config after creation
							setTimeout(function() {
								self.nextStep();
							}, 500);
						} else {
							alert('Fehler beim Erstellen der Liste: Keine Listen-ID erhalten');
							$button.prop('disabled', false).text(originalText);
						}
					} else {
						console.error('Invalid response format:', response);
						alert('Fehler beim Erstellen der Liste: Ungültige Antwort vom Server. Siehe Browser-Konsole für Details.');
						$button.prop('disabled', false).text(originalText);
					}
				},
				error: function(xhr, status, error) {
					console.error('Create list error - Status:', status);
					console.error('Create list error - Error:', error);
					console.error('Create list error - Response:', xhr.responseText);
					alert('Fehler beim Erstellen der Liste: ' + error + '\nDetails in der Browser-Konsole.');
					$button.prop('disabled', false).text(originalText);
				}
			});
		},

		showConfiguration: function() {
			console.log('showConfiguration - outputType:', this.outputType);

			// Show or hide the selected item display
			if (this.selectedTitle) {
				$('#atkp-selected-item-display').text(this.selectedTitle);
				$('.atkp-selected-item').show();
			} else {
				$('.atkp-selected-item').hide();
			}

			$('.atkp-modal-body').attr('data-output-type', this.outputType);

			// Hide/show relevant tabs based on output type
			$('.atkp-tab-btn').hide();
			$('.atkp-tab-content').removeClass('active');

			if (this.outputType === 'list') {
				// Show only template and advanced for lists
				$('.atkp-tab-btn[data-tab="template"], .atkp-tab-btn[data-tab="advanced"]').show();
			} else if (this.outputType === 'field') {
				// Show only field and advanced for single field
				$('.atkp-tab-btn[data-tab="field"], .atkp-tab-btn[data-tab="advanced"]').show();
			} else if (this.outputType === 'link') {
				// Show only link and advanced for text link
				$('.atkp-tab-btn[data-tab="link"], .atkp-tab-btn[data-tab="advanced"]').show();
			} else if (this.outputType === 'searchform') {
				// Show only searchform tab for search forms
				$('.atkp-tab-btn[data-tab="searchform"]').show();
			} else if (this.outputType === 'dynamicfilter') {
				// Show only dynamicfilter and advanced for dynamic filter lists
				$('.atkp-tab-btn[data-tab="dynamicfilter"], .atkp-tab-btn[data-tab="advanced"]').show();
			} else {
				// Show template and advanced for product box
				$('.atkp-tab-btn[data-tab="template"], .atkp-tab-btn[data-tab="advanced"]').show();
			}

			console.log('Visible tabs:', $('.atkp-tab-btn:visible').length);

			// Set default tab (first visible) - this will show the content
			const $firstTab = $('.atkp-tab-btn:visible:first');
			console.log('First visible tab:', $firstTab.data('tab'));
			$firstTab.trigger('click');

			this.updateShortcode();
		},

		updateShortcode: function() {
			let shortcode = '';

			// Handle searchform shortcode
			if (this.outputType === 'searchform') {
				shortcode = '[atkp_searchform';

				const searchTemplate = $('#atkp-config-searchform-template').val();
				if (searchTemplate) {
					shortcode += ' template="' + searchTemplate + '"';
				}

				const targetPage = $('#atkp-config-searchform-targetpage').val();
				if (targetPage) {
					shortcode += ' target_page="' + targetPage + '"';
				}

				shortcode += ']';
				$('#atkp-shortcode-output').val(shortcode);
				return;
			}

			// Handle dynamicfilter shortcode
			if (this.outputType === 'dynamicfilter') {
				shortcode = '[atkp_list';

				// Build filter string for the filter attribute
				let filterParts = [];

				// Get all filter field values from the dynamic filter tab (exclude parseparams and itemsperpage)
				$('.atkp-tab-content[data-tab="dynamicfilter"] select, .atkp-tab-content[data-tab="dynamicfilter"] input[type="text"], .atkp-tab-content[data-tab="dynamicfilter"] input[type="number"]:not(#atkp-config-itemsperpage), .atkp-tab-content[data-tab="dynamicfilter"] input[type="checkbox"]:not(#atkp-config-parseparams)').each(function() {
					const $field = $(this);
					const fieldId = $field.attr('id');
					let value;

					// Handle checkbox differently - check if it's checked
					if ($field.attr('type') === 'checkbox') {
						value = $field.is(':checked') ? '1' : '';
					} else {
						value = $field.val();
					}

					// Skip if no value or empty string (for checkboxes, only add if checked)
					if (!value || value === '') return;

					// Skip "0" for non-checkbox fields
					if ((value === '0' || value === 0) && $field.attr('type') !== 'checkbox') return;

					// For arrays (multi-select), check if empty
					if (Array.isArray(value) && value.length === 0) return;

					// Extract field name from ID (remove atkp_ prefix)
					let fieldName = fieldId;
					if (fieldId.startsWith('atkp_')) {
						fieldName = fieldId.replace('atkp_', '');
					}

					// Handle array values (multi-select) - join with comma
					if (Array.isArray(value)) {
						value = value.join(',');
					}

					// Add to filter parts
					filterParts.push(fieldName + '=' + encodeURIComponent(value));
				});

				// Add filter attribute if there are any filters
				if (filterParts.length > 0) {
					shortcode += ' filter="' + filterParts.join('&') + '"';
				}

				// Parse parameters checkbox
				if ($('#atkp-config-parseparams').is(':checked')) {
					shortcode += ' parseparams="yes"';
				}

				// Items per page (limit attribute)
				const itemsPerPage = $('#atkp-config-itemsperpage').val();
				if (itemsPerPage && itemsPerPage != '25') {
					shortcode += ' limit="' + itemsPerPage + '"';
				}

				// Add tracking ID from advanced tab if present
				const trackingId = $('#atkp-config-trackingid').val();
				if (trackingId) {
					shortcode += ' tracking_id="' + trackingId + '"';
				}

				shortcode += ']';
				$('#atkp-shortcode-output').val(shortcode);
				return;
			}

			// Build shortcode based on output type
			if (this.outputType === 'list') {
				shortcode = '[atkp_list';
				if (this.selectedId) {
					shortcode += ' id="' + this.selectedId + '"';
				}
			} else {
				shortcode = '[atkp_product';
				if (this.selectedId) {
					shortcode += ' id="' + this.selectedId + '"';
				}
			}

			// Add template
			const template = $('#atkp-config-template').val();
			if (template) {
				shortcode += ' template="' + template + '"';
			}

			// Add button type
			const buttonType = $('#atkp-config-buttontype').val();
			if (buttonType) {
				shortcode += ' buttontype="' + buttonType + '"';
			}

			// Add alignment and CSS
			let containerCss = '';
			const align = $('#atkp-config-align').val();
			if (align) {
				containerCss = align;
			}
			const customContainerCss = $('#atkp-config-containercss').val();
			if (customContainerCss) {
				containerCss += (containerCss ? ' ' : '') + customContainerCss;
			}
			if (containerCss) {
				shortcode += ' containercss="' + containerCss + '"';
			}

			const elementCss = $('#atkp-config-elementcss').val();
			if (elementCss) {
				shortcode += ' elementcss="' + elementCss + '"';
			}

			// Add list-specific options
			if (this.outputType === 'list') {
				const limit = $('#atkp-config-limit').val();
				if (limit) {
					shortcode += ' limit="' + limit + '"';
				}

				if ($('#atkp-config-random').is(':checked')) {
					shortcode += ' randomsort="yes"';
				}
			}

			// Add field
			const field = $('#atkp-config-field').val();
			if (field && this.outputType === 'field') {
				shortcode += ' field="' + field + '"';
			}

			// Add link
			if (this.outputType === 'link' || $('#atkp-config-fieldlink').is(':checked')) {
				shortcode += ' link="yes"';
			}

			// Add hide disclaimer
			if ($('#atkp-config-hidedisclaimer').is(':checked')) {
				shortcode += ' hidedisclaimer="yes"';
			}

			// Add tracking ID
			const trackingId = $('#atkp-config-trackingid').val();
			if (trackingId) {
				shortcode += ' tracking_id="' + trackingId + '"';
			}

			shortcode += ']';

			// Add content
			const content = $('#atkp-config-content').val() || $('#atkp-config-linktext').val();
			if (content) {
				shortcode += content;
			}

			// Close shortcode
			if (this.outputType === 'list') {
				shortcode += '[/atkp_list]';
			} else {
				shortcode += '[/atkp_product]';
			}

			$('#atkp-shortcode-output').val(shortcode);
		},

		copyShortcode: function() {
			const $textarea = $('#atkp-shortcode-output');
			$textarea.select();
			document.execCommand('copy');

			const $btn = $('#atkp-copy-shortcode');
			const originalText = $btn.html();
			$btn.html('<span class="dashicons dashicons-yes"></span> Kopiert!');

			setTimeout(function() {
				$btn.html(originalText);
			}, 2000);
		},

		insertShortcode: function() {
			const shortcode = $('#atkp-shortcode-output').val();

			if (!shortcode) {
				return;
			}

			// Insert into editor
			if (typeof tinyMCE !== 'undefined' && tinyMCE.activeEditor && !tinyMCE.activeEditor.isHidden()) {
				tinyMCE.activeEditor.execCommand('mceInsertContent', false, shortcode);
			} else {
				// Fallback to textarea
				const $editor = $('#content');
				if ($editor.length) {
					const content = $editor.val();
					$editor.val(content + shortcode);
				}
			}

			this.closeModal();
		}
	};

	// Initialize when document is ready
	$(document).ready(function() {
		ATKPGenerator.init();
	});

	// Expose to global scope
	window.ATKPGenerator = ATKPGenerator;

})(jQuery);
