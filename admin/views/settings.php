<?php
if (!defined('WPINC')) {
    die;
}

$settings = get_option('airtable_viewer_settings');
?>

<div class="wrap">
    <h1>Airtable Viewer Settings</h1>

    <?php settings_errors(); ?>

    <form method="post" action="">
        <?php wp_nonce_field('airtable_viewer_settings'); ?>
        
        <table class="form-table">
            <tr>
                <th scope="row"><label for="api_key">API Key</label></th>
                <td>
                    <input type="password" id="api_key" name="api_key" class="regular-text"
                           value="<?php echo esc_attr($settings['api_key']); ?>" required>
                    <p class="description">
                        Your Airtable API key. You can find this in your 
                        <a href="https://airtable.com/account" target="_blank">Airtable account settings</a>.
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="default_base_id">Default Base ID</label></th>
                <td>
                    <input type="text" id="default_base_id" name="default_base_id" class="regular-text"
                           value="<?php echo esc_attr($settings['default_base_id']); ?>">
                    <p class="description">
                        The default Airtable base ID to use. You can find this in your base's API documentation.
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="cache_duration">Cache Duration</label></th>
                <td>
                    <input type="number" id="cache_duration" name="cache_duration" class="small-text"
                           value="<?php echo esc_attr($settings['cache_duration']); ?>" min="0">
                    <p class="description">
                        How long to cache API responses, in seconds. Set to 0 to disable caching.
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="global_styles">Global Styles</label></th>
                <td>
                    <textarea id="global_styles" name="global_styles" class="large-text code" rows="10"><?php 
                        echo esc_textarea($settings['global_styles']); 
                    ?></textarea>
                    <p class="description">
                        CSS styles to apply to all Airtable views. These will be added to the &lt;head&gt; of your site.
                    </p>
                </td>
            </tr>
        </table>

        <p class="submit">
            <button type="submit" name="submit" class="button button-primary">Save Settings</button>
            <button type="button" id="test-connection" class="button">Test Connection</button>
        </p>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    $('#test-connection').click(function() {
        var $button = $(this);
        $button.prop('disabled', true).text('Testing...');
        
        $.ajax({
            url: airtableViewerAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'airtable_viewer_test_connection',
                nonce: airtableViewerAdmin.nonce,
                api_key: $('#api_key').val()
            },
            success: function(response) {
                if (response.success) {
                    alert('Connection successful!');
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
});</script> 