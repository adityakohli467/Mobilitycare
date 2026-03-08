/**
 * Shared AJAX Form Validation for MobilityCare
 * 
 * Usage: Add these attributes to any <form>:
 *   class="ajax-validate-form"
 *   data-validate-url="index.php?route=information/contact/validateAjax"
 * 
 * The form will be validated via AJAX before submission.
 * If validation fails, errors are shown inline without page reload.
 * If validation passes, the form is submitted normally.
 */
(function($) {
    'use strict';

    // Ensure we only bind once even if script is loaded multiple times
    if (window._ajaxValidateFormBound) return;
    window._ajaxValidateFormBound = true;

    $(document).on('submit', '.ajax-validate-form', function(e) {
        var form = $(this);
        var validateUrl = form.data('validate-url');

        // No validate URL, allow normal submit
        if (!validateUrl) return true;

        // If already validated via AJAX, allow normal submit
        if (form.data('ajax-validated') === true) {
            return true;
        }

        e.preventDefault();

        // Show loader overlay
        var loader = $('#loaderOverlay');
        if (loader.length) {
            loader.css('display', 'flex');
        }

        // Clear previous AJAX errors
        form.find('.ajax-error-msg').remove();

        $.ajax({
            url: validateUrl,
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    // Mark as validated and re-trigger submit
                    form.data('ajax-validated', true);
                    form.trigger('submit');
                    return;
                }

                // Hide loader on error
                if (loader.length) {
                    loader.hide();
                }

                if (res.error) {
                    var lastError = '';

                    $.each(res.error, function(key, msg) {
                        var field = form.find('[name="' + key + '"]');
                        if (field.length) {
                            field.closest('.form-group').find('.ajax-error-msg').remove();
                            field.after('<div class="text-danger ajax-error-msg" style="margin-top:4px;">' + msg + '</div>');
                        }
                        lastError = msg;
                    });

                    // Show toast notification with last error
                    var toast = $('#toastMessage');
                    if (toast.length) {
                        toast.text(lastError).addClass('error').removeClass('success').show();
                        setTimeout(function() { toast.fadeOut(); }, 8000);
                    }
                }
            },
            error: function() {
                if (loader.length) {
                    loader.hide();
                }
                alert('A network error occurred. Please try again.');
            }
        });
    });

})(jQuery);
