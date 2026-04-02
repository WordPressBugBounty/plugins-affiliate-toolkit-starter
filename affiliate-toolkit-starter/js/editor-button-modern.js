/**
 * TinyMCE Button for affiliate-toolkit Shortcode Generator
 * Opens the shortcode generator in a Thickbox modal
 */
(function() {
	tinymce.PluginManager.add('atkp_button', function(editor, url) {
		// Get the plugin base URL
		var pluginUrl = url.substring(0, url.lastIndexOf('/js'));
		var iconUrl = pluginUrl + '/images/affiliate_toolkit_menu.png';

		// Add button to editor
		editor.addButton('atkp_button', {
			title: 'affiliate-toolkit Shortcode Generator',
			image: iconUrl,
			onclick: function() {
				// Build URL with TB_iframe parameter properly
				var baseUrl = ajaxurl.replace('/admin-ajax.php', '/admin.php');
				var generatorUrl = baseUrl + '?page=ATKP_affiliate_toolkit-shortcodegenerator&atkp_iframe=1&width=1400&height=700&TB_iframe=true';

				// Open in Thickbox modal
				tb_show('affiliate-toolkit Shortcode Generator', generatorUrl);
			}
		});

		// Add menu item (optional, appears in "Insert" menu)
		editor.addMenuItem('atkp_button', {
			text: 'affiliate-toolkit Shortcode',
			image: iconUrl,
			context: 'insert',
			onclick: function() {
				var baseUrl = ajaxurl.replace('/admin-ajax.php', '/admin.php');
				var generatorUrl = baseUrl + '?page=ATKP_affiliate_toolkit-shortcodegenerator&atkp_iframe=1&width=1400&height=700&TB_iframe=true';
				tb_show('affiliate-toolkit Shortcode Generator', generatorUrl);
			}
		});
	});
})();
