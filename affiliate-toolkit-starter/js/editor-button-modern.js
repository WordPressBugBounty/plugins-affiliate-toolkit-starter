/**
 * TinyMCE Button for affiliate-toolkit Shortcode Generator
 * Opens the shortcode generator in a new tab
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
				// Get the URL to the shortcode generator page
				var generatorUrl = ajaxurl.replace('/admin-ajax.php', '/admin.php?page=ATKP_affiliate_toolkit-shortcodegenerator');

				// Open in new tab
				window.open(generatorUrl, '_blank');
			}
		});

		// Add menu item (optional, appears in "Insert" menu)
		editor.addMenuItem('atkp_button', {
			text: 'affiliate-toolkit Shortcode',
			image: iconUrl,
			context: 'insert',
			onclick: function() {
				var generatorUrl = ajaxurl.replace('/admin-ajax.php', '/admin.php?page=ATKP_affiliate_toolkit-shortcodegenerator');
				window.open(generatorUrl, '_blank');
			}
		});
	});
})();
