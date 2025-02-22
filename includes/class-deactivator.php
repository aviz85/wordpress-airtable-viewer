<?php
namespace AirtableViewer;

class Deactivator {
    public static function deactivate() {
        // Clear any scheduled hooks
        wp_clear_scheduled_hook('airtable_viewer_cache_cleanup');
        
        // Clear transients
        self::clear_transients();
    }

    private static function clear_transients() {
        global $wpdb;
        $wpdb->query(
            "DELETE FROM {$wpdb->options} 
            WHERE option_name LIKE '_transient_airtable_viewer_%' 
            OR option_name LIKE '_transient_timeout_airtable_viewer_%'"
        );
    }
} 