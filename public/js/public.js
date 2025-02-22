jQuery(document).ready(function($) {
    // Handle pagination clicks
    $('.airtable-viewer-pagination a').on('click', function(e) {
        e.preventDefault();
        
        var $link = $(this);
        var $container = $link.closest('.airtable-viewer-container');
        var url = $link.attr('href');
        
        // Show loading state
        $container.addClass('loading');
        $container.append('<div class="airtable-viewer-loading">Loading</div>');

        // Extract the page number from the URL
        var page = url.match(/[?&]page=(\d+)/)[1];

        $.ajax({
            url: airtableViewerData.ajaxurl,
            type: 'POST',
            data: {
                action: 'airtable_viewer_paginate',
                page: page,
                template_id: $container.data('template-id'),
                nonce: airtableViewerData.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Update the content
                    $container.html(response.data);
                    
                    // Update the URL without refreshing
                    if (history.pushState) {
                        history.pushState(null, null, url);
                    }
                    
                    // Scroll to the top of the container
                    $('html, body').animate({
                        scrollTop: $container.offset().top - 50
                    }, 500);
                } else {
                    // Show error message
                    $container.removeClass('loading');
                    $('.airtable-viewer-loading').remove();
                    $container.append(
                        '<div class="airtable-viewer-error">' + 
                        'Error loading content: ' + response.data +
                        '</div>'
                    );
                }
            },
            error: function() {
                // Show error message
                $container.removeClass('loading');
                $('.airtable-viewer-loading').remove();
                $container.append(
                    '<div class="airtable-viewer-error">' +
                    'Error loading content. Please try again.' +
                    '</div>'
                );
            }
        });
    });
}); 