<?php
if (!defined('WPINC')) {
    die;
}

$templates = $this->template->get_all();
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Airtable Templates</h1>
    <a href="?page=airtable-viewer&action=new" class="page-title-action">Add New</a>
    
    <?php settings_errors(); ?>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>Name</th>
                <th>Shortcode</th>
                <th>Base ID</th>
                <th>Table</th>
                <th>Pagination</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($templates)): ?>
                <tr>
                    <td colspan="6">No templates found. <a href="?page=airtable-viewer&action=new">Create one</a>.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($templates as $template): ?>
                    <tr>
                        <td><?php echo esc_html($template['name']); ?></td>
                        <td>
                            <code>[airtable_view template="<?php echo esc_attr($template['shortcode']); ?>"]</code>
                            <button class="button button-small copy-shortcode" data-shortcode='[airtable_view template="<?php echo esc_attr($template['shortcode']); ?>"]'>
                                Copy
                            </button>
                        </td>
                        <td><?php echo esc_html($template['base_id']); ?></td>
                        <td><?php echo esc_html($template['table_name']); ?></td>
                        <td><?php echo $template['pagination_enabled'] ? 'Yes (' . esc_html($template['items_per_page']) . ' per page)' : 'No'; ?></td>
                        <td>
                            <a href="?page=airtable-viewer&action=edit&template_id=<?php echo esc_attr($template['id']); ?>" class="button button-small">
                                Edit
                            </a>
                            <button class="button button-small button-link-delete delete-template" data-id="<?php echo esc_attr($template['id']); ?>">
                                Delete
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
jQuery(document).ready(function($) {
    $('.copy-shortcode').click(function() {
        var shortcode = $(this).data('shortcode');
        navigator.clipboard.writeText(shortcode).then(function() {
            var $button = $(this);
            $button.text('Copied!');
            setTimeout(function() {
                $button.text('Copy');
            }, 2000);
        }.bind(this));
    });

    $('.delete-template').click(function() {
        if (!confirm('Are you sure you want to delete this template?')) {
            return;
        }

        var $row = $(this).closest('tr');
        var templateId = $(this).data('id');

        $.ajax({
            url: airtableViewerAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'airtable_viewer_delete_template',
                template_id: templateId,
                nonce: airtableViewerAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    $row.fadeOut(400, function() {
                        $(this).remove();
                        if ($('tbody tr').length === 0) {
                            $('tbody').html('<tr><td colspan="6">No templates found. <a href="?page=airtable-viewer&action=new">Create one</a>.</td></tr>');
                        }
                    });
                } else {
                    alert('Error deleting template: ' + response.data);
                }
            },
            error: function() {
                alert('Error deleting template. Please try again.');
            }
        });
    });
});
</script> 