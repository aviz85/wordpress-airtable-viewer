jQuery(document).ready(function($) {
    // Template form validation
    function validateShortcode(shortcode) {
        return /^[a-z0-9-_]+$/.test(shortcode);
    }

    $('#shortcode').on('input', function() {
        var shortcode = $(this).val();
        if (!validateShortcode(shortcode)) {
            $(this).addClass('invalid');
            $(this).next('.description').html(
                'Shortcode can only contain lowercase letters, numbers, hyphens, and underscores.'
            ).css('color', '#dc3232');
        } else {
            $(this).removeClass('invalid');
            $(this).next('.description').html(
                'This will be used in the shortcode: [airtable_view template="your-shortcode"]'
            ).css('color', '');
        }
    });

    // Base ID and Table Name validation
    $('#base_id, #table_name').on('input', function() {
        var $input = $(this);
        var value = $input.val();
        
        if (value && !/^[a-zA-Z0-9]+$/.test(value)) {
            $input.addClass('invalid');
            $input.next('.description').html(
                'This field can only contain letters and numbers.'
            ).css('color', '#dc3232');
        } else {
            $input.removeClass('invalid');
            $input.next('.description').html('').css('color', '');
        }
    });

    // Dynamic field variables insertion
    var $mainHtml = $('#main_html');
    if ($mainHtml.length) {
        var $fieldVariables = $('<div class="field-variables"></div>');
        $mainHtml.after($fieldVariables);

        function updateFieldVariables() {
            var baseId = $('#base_id').val();
            var tableName = $('#table_name').val();

            if (!baseId || !tableName) {
                $fieldVariables.html('');
                return;
            }

            $.ajax({
                url: airtableViewerAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'airtable_viewer_get_fields',
                    base_id: baseId,
                    table_name: tableName,
                    nonce: airtableViewerAdmin.nonce
                },
                success: function(response) {
                    if (response.success && response.data.fields) {
                        var html = '<p class="description">Available fields: ';
                        response.data.fields.forEach(function(field) {
                            html += '<code>{{' + field + '}}</code> ';
                        });
                        html += '</p>';
                        $fieldVariables.html(html);
                    }
                }
            });
        }

        $('#base_id, #table_name').on('change', updateFieldVariables);
    }

    // Pagination toggle
    $('#items_per_page').closest('tr').toggle($('input[name="pagination_enabled"]').is(':checked'));
    $('input[name="pagination_enabled"]').on('change', function() {
        $('#items_per_page').closest('tr').toggle($(this).is(':checked'));
    });

    // Template form submission
    $('#template-form').on('submit', function(e) {
        var $form = $(this);
        var shortcode = $('#shortcode').val();
        
        if (!validateShortcode(shortcode)) {
            e.preventDefault();
            alert('Please fix the shortcode format before saving.');
            return;
        }

        if ($form.find('.invalid').length) {
            e.preventDefault();
            alert('Please fix the validation errors before saving.');
            return;
        }
    });

    // Copy shortcode button
    $('.copy-shortcode').on('click', function() {
        var $button = $(this);
        var shortcode = $button.data('shortcode');
        
        navigator.clipboard.writeText(shortcode).then(function() {
            var originalText = $button.text();
            $button.text('Copied!');
            setTimeout(function() {
                $button.text(originalText);
            }, 2000);
        });
    });

    // Delete template confirmation
    $('.delete-template').on('click', function(e) {
        if (!confirm('Are you sure you want to delete this template? This action cannot be undone.')) {
            e.preventDefault();
        }
    });

    // Settings page - Test connection
    $('#test-connection').on('click', function() {
        var $button = $(this);
        var apiKey = $('#api_key').val();
        
        if (!apiKey) {
            alert('Please enter an API key first.');
            return;
        }

        $button.prop('disabled', true).text('Testing...');

        $.ajax({
            url: airtableViewerAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'airtable_viewer_test_connection',
                api_key: apiKey,
                nonce: airtableViewerAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('Connection successful! The API key is valid.');
                } else {
                    alert('Connection failed: ' + response.data);
                }
            },
            error: function() {
                alert('Error testing connection. Please try again.');
            },
            complete: function() {
                $button.prop('disabled', false).text('Test Connection');
            }
        });
    });
}); 