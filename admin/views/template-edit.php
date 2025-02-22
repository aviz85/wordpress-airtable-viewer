<?php
if (!defined('WPINC')) {
    die;
}

$template_data = null;
if (isset($template_id) && $template_id > 0) {
    $template_data = $this->template->get($template_id);
    if (!$template_data) {
        wp_die('Template not found.');
    }
}

$settings = get_option('airtable_viewer_settings');
?>

<div class="wrap">
    <h1><?php echo $template_data ? 'Edit Template' : 'New Template'; ?></h1>

    <?php settings_errors(); ?>

    <form id="template-form" method="post">
        <input type="hidden" name="action" value="airtable_viewer_save_template">
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('airtable_viewer_admin_nonce'); ?>">
        <?php if ($template_data): ?>
            <input type="hidden" name="id" value="<?php echo esc_attr($template_data['id']); ?>">
        <?php endif; ?>

        <table class="form-table">
            <tr>
                <th scope="row"><label for="name">Template Name</label></th>
                <td>
                    <input type="text" id="name" name="name" class="regular-text" required
                           value="<?php echo esc_attr($template_data['name'] ?? ''); ?>">
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="shortcode">Shortcode</label></th>
                <td>
                    <input type="text" id="shortcode" name="shortcode" class="regular-text" required
                           value="<?php echo esc_attr($template_data['shortcode'] ?? ''); ?>">
                    <p class="description">This will be used in the shortcode: [airtable_view template="your-shortcode"]</p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="base_id">Base ID</label></th>
                <td>
                    <input type="text" id="base_id" name="base_id" class="regular-text" required
                           value="<?php echo esc_attr($template_data['base_id'] ?? $settings['default_base_id']); ?>">
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="table_name">Table Name</label></th>
                <td>
                    <input type="text" id="table_name" name="table_name" class="regular-text" required
                           value="<?php echo esc_attr($template_data['table_name'] ?? ''); ?>">
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="formula">Filter Formula</label></th>
                <td>
                    <input type="text" id="formula" name="formula" class="regular-text"
                           value="<?php echo esc_attr($template_data['query_settings']['formula'] ?? ''); ?>">
                    <p class="description">Airtable formula to filter records. Leave empty to show all records.</p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="sort">Sort</label></th>
                <td>
                    <input type="text" id="sort" name="sort" class="regular-text"
                           value="<?php echo esc_attr($template_data['query_settings']['sort'] ?? ''); ?>">
                    <p class="description">Example: field:asc or field:desc</p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="view">View Name</label></th>
                <td>
                    <input type="text" id="view" name="view" class="regular-text"
                           value="<?php echo esc_attr($template_data['query_settings']['view'] ?? ''); ?>">
                    <p class="description">Optional: Use a specific view from your Airtable base</p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="prefix_html">Prefix HTML</label></th>
                <td>
                    <textarea id="prefix_html" name="prefix_html" class="large-text code" rows="5"><?php 
                        echo esc_textarea($template_data['prefix_html'] ?? ''); 
                    ?></textarea>
                    <p class="description">HTML to display before the records list</p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="main_html">Main HTML Template</label></th>
                <td>
                    <textarea id="main_html" name="main_html" class="large-text code" rows="10" required><?php 
                        echo esc_textarea($template_data['main_html'] ?? ''); 
                    ?></textarea>
                    <p class="description">
                        HTML template for each record. Use {{field_name}} to insert field values.<br>
                        Special variables: {{index}}, {{record_id}}
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="suffix_html">Suffix HTML</label></th>
                <td>
                    <textarea id="suffix_html" name="suffix_html" class="large-text code" rows="5"><?php 
                        echo esc_textarea($template_data['suffix_html'] ?? ''); 
                    ?></textarea>
                    <p class="description">HTML to display after the records list</p>
                </td>
            </tr>
            <tr>
                <th scope="row">Pagination</th>
                <td>
                    <label>
                        <input type="checkbox" name="pagination_enabled" value="1"
                               <?php checked($template_data['pagination_enabled'] ?? false); ?>>
                        Enable pagination
                    </label>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="items_per_page">Items per Page</label></th>
                <td>
                    <input type="number" id="items_per_page" name="items_per_page" class="small-text"
                           value="<?php echo esc_attr($template_data['items_per_page'] ?? 10); ?>" min="1">
                </td>
            </tr>
        </table>

        <p class="submit">
            <button type="submit" class="button button-primary">Save Template</button>
            <a href="?page=airtable-viewer" class="button">Cancel</a>
        </p>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    $('#template-form').submit(function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $submitButton = $form.find('button[type="submit"]');
        
        $submitButton.prop('disabled', true).text('Saving...');
        
        $.ajax({
            url: airtableViewerAdmin.ajaxurl,
            type: 'POST',
            data: $form.serialize(),
            success: function(response) {
                if (response.success) {
                    window.location.href = '?page=airtable-viewer&message=saved';
                } else {
                    alert('Error saving template: ' + response.data);
                    $submitButton.prop('disabled', false).text('Save Template');
                }
            },
            error: function() {
                alert('Error saving template. Please try again.');
                $submitButton.prop('disabled', false).text('Save Template');
            }
        });
    });
});</script> 