<?php
namespace AirtableViewer;

class Activator {
    public static function activate() {
        self::create_tables();
        self::set_default_options();
    }

    private static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}airtable_templates` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `shortcode` varchar(50) NOT NULL,
            `base_id` varchar(255) NOT NULL,
            `table_name` varchar(255) NOT NULL,
            `query_settings` JSON,
            `prefix_html` TEXT,
            `main_html` TEXT NOT NULL,
            `suffix_html` TEXT,
            `pagination_enabled` tinyint(1) DEFAULT 0,
            `items_per_page` int DEFAULT 10,
            `parameter_config` JSON,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `shortcode` (`shortcode`)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    private static function set_default_options() {
        $default_options = [
            'api_key' => '',
            'default_base_id' => '',
            'cache_duration' => 300, // 5 minutes
            'global_styles' => ''
        ];

        add_option('airtable_viewer_settings', $default_options);
    }
} 