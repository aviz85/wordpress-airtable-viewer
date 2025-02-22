<?php
namespace AirtableViewer;

class Airtable_API {
    const BASE_URL = 'https://api.airtable.com/v0';
    private $api_key;

    public function __construct() {
        $settings = get_option('airtable_viewer_settings');
        $this->api_key = $settings['api_key'] ?? '';
    }

    public function get_table_records($base_id, $table_name, $params = []) {
        $cache_key = 'airtable_viewer_' . md5($base_id . $table_name . serialize($params));
        $cached_result = get_transient($cache_key);

        if ($cached_result !== false) {
            return $cached_result;
        }

        $url = self::BASE_URL . "/{$base_id}/{$table_name}";
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        $response = wp_remote_get($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json',
            ]
        ]);

        if (is_wp_error($response)) {
            return [
                'error' => true,
                'message' => $response->get_error_message()
            ];
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        $status_code = wp_remote_retrieve_response_code($response);

        if ($status_code !== 200) {
            return [
                'error' => true,
                'message' => $body['error']['message'] ?? 'Unknown error occurred'
            ];
        }

        $settings = get_option('airtable_viewer_settings');
        $cache_duration = $settings['cache_duration'] ?? 300;
        set_transient($cache_key, $body, $cache_duration);

        return $body;
    }

    public function get_table_schema($base_id, $table_name) {
        $cache_key = 'airtable_viewer_schema_' . md5($base_id . $table_name);
        $cached_schema = get_transient($cache_key);

        if ($cached_schema !== false) {
            return $cached_schema;
        }

        $url = self::BASE_URL . "/{$base_id}/{$table_name}/";

        $response = wp_remote_get($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json',
            ]
        ]);

        if (is_wp_error($response)) {
            return [
                'error' => true,
                'message' => $response->get_error_message()
            ];
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (!empty($body['records'][0])) {
            $fields = array_keys($body['records'][0]['fields']);
            $schema = ['fields' => $fields];
            set_transient($cache_key, $schema, 3600); // Cache for 1 hour
            return $schema;
        }

        return [
            'error' => true,
            'message' => 'Could not retrieve table schema'
        ];
    }

    public function validate_credentials() {
        if (empty($this->api_key)) {
            return [
                'valid' => false,
                'message' => 'API key is not set'
            ];
        }

        $response = wp_remote_get(self::BASE_URL, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key
            ]
        ]);

        if (is_wp_error($response)) {
            return [
                'valid' => false,
                'message' => $response->get_error_message()
            ];
        }

        $status_code = wp_remote_retrieve_response_code($response);

        return [
            'valid' => $status_code === 200,
            'message' => $status_code === 200 ? 'Valid credentials' : 'Invalid credentials'
        ];
    }

    public function clear_cache($base_id = null, $table_name = null) {
        global $wpdb;
        
        if ($base_id && $table_name) {
            $cache_key = 'airtable_viewer_' . md5($base_id . $table_name);
            $schema_key = 'airtable_viewer_schema_' . md5($base_id . $table_name);
            delete_transient($cache_key);
            delete_transient($schema_key);
        } else {
            $wpdb->query(
                "DELETE FROM {$wpdb->options} 
                WHERE option_name LIKE '_transient_airtable_viewer_%' 
                OR option_name LIKE '_transient_timeout_airtable_viewer_%'"
            );
        }
    }
} 