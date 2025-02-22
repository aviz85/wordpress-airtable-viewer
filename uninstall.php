<?php
// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete the plugin options
delete_option('airtable_viewer_settings');

// Drop the templates table
global $wpdb;
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}airtable_templates");

// Clear any transients
$wpdb->query(
    "DELETE FROM {$wpdb->options} 
    WHERE option_name LIKE '_transient_airtable_viewer_%' 
    OR option_name LIKE '_transient_timeout_airtable_viewer_%'"
); 