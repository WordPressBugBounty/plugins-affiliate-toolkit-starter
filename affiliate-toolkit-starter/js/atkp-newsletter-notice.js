jQuery(document).ready(function($) {
    'use strict';

    // Newsletter-Formular absenden
    $('#atkp-newsletter-form').on('submit', function(e) {
        e.preventDefault();

        var $form = $(this);
        var $submitBtn = $form.find('button[type="submit"]');
        var $message = $('.atkp-newsletter-message');

        var firstname = $form.find('input[name="firstname"]').val().trim();
        var email = $form.find('input[name="email"]').val().trim();

        if (!firstname || !email) {
            showMessage('Please fill in all fields.', 'error');
            return;
        }

        $submitBtn.prop('disabled', true).text('Subscribing...');
        $message.html('');

        $.ajax({
            url: atkpNewsletterAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'atkp_subscribe_newsletter',
                nonce: atkpNewsletterAjax.nonce,
                firstname: firstname,
                email: email
            },
            success: function(response) {
                if (response.success) {
                    showMessage('✓ ' + response.data.message, 'success');
                    // Formular ausblenden nach 4 Sekunden (Zeit zum Lesen der Bestätigungs-Nachricht)
                    setTimeout(function() {
                        $('#atkp-newsletter-notice').fadeOut(300);
                    }, 4000);
                } else {
                    showMessage('✗ ' + response.data.message, 'error');
                    $submitBtn.prop('disabled', false).text('Get Updates');
                }
            },
            error: function() {
                showMessage('✗ An error occurred.', 'error');
                $submitBtn.prop('disabled', false).text('Get Updates');
            }
        });
    });

    // "Nicht mehr anzeigen" Button
    $('.atkp-newsletter-dismiss').on('click', function(e) {
        e.preventDefault();

        $.ajax({
            url: atkpNewsletterAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'atkp_dismiss_newsletter',
                nonce: atkpNewsletterAjax.nonce
            },
            success: function() {
                $('#atkp-newsletter-notice').fadeOut(300);
            }
        });
    });

    function showMessage(text, type) {
        var $message = $('.atkp-newsletter-message');
        var color = type === 'success' ? '#46b450' : '#dc3232';
        $message.html('<span style="color: ' + color + '; font-weight: bold;">' + text + '</span>');
    }
});

