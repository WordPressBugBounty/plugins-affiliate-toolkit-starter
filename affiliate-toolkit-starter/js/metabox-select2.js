/**
 * Select2 initialization for ATKP metaboxes
 */
jQuery(document).ready(function($) {
    'use strict';

    // Initialize Select2 for product/list metaboxes with AJAX
    $('.atkp-product-select').each(function() {
        var $select = $(this);
        var postType = $select.data('posttype');

        // Check if select2atkp is available
        if (typeof $select.select2atkp !== 'function') {
            console.log('ATKP Metabox: select2atkp not available');
            return;
        }

        // Destroy existing select2atkp if any
        if ($select.hasClass('select2-hidden-accessible')) {
            try {
                $select.select2atkp('destroy');
            } catch(e) {
                console.log('ATKP Metabox: Error destroying select2', e);
            }
        }

        // Initialize select2atkp with AJAX
        $select.select2atkp({
            ajax: {
                url: atkpMetabox.ajaxurl,
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        action: 'atkp_search_posts',
                        post_type: postType,
                        search: params.term || '',
                        nonce: atkpMetabox.nonce
                    };
                },
                processResults: function(data) {
                    if (!data || !Array.isArray(data)) {
                        return { results: [] };
                    }
                    return {
                        results: data.map(function(item) {
                            return {
                                id: item.id,
                                text: item.title + ' (' + item.id + ')'
                            };
                        })
                    };
                },
                cache: true
            },
            minimumInputLength: 0,
            placeholder: atkpMetabox.searchPlaceholder,
            allowClear: true,
            width: '100%'
        });

        console.log('ATKP Metabox: Initialized select2 for', postType);
    });
});
