<?php
namespace AirtableViewer;

class Template {
    private $table_name;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'airtable_templates';
    }

    public function create($data) {
        global $wpdb;

        $defaults = [
            'name' => '',
            'shortcode' => '',
            'base_id' => '',
            'table_name' => '',
            'query_settings' => '{}',
            'prefix_html' => '',
            'main_html' => '',
            'suffix_html' => '',
            'pagination_enabled' => 0,
            'items_per_page' => 10,
            'parameter_config' => '{}'
        ];

        $data = wp_parse_args($data, $defaults);
        
        // Ensure JSON fields are valid
        $data['query_settings'] = json_encode(json_decode($data['query_settings'], true) ?: new \stdClass());
        $data['parameter_config'] = json_encode(json_decode($data['parameter_config'], true) ?: new \stdClass());

        $result = $wpdb->insert(
            $this->table_name,
            $data,
            [
                '%s', // name
                '%s', // shortcode
                '%s', // base_id
                '%s', // table_name
                '%s', // query_settings
                '%s', // prefix_html
                '%s', // main_html
                '%s', // suffix_html
                '%d', // pagination_enabled
                '%d', // items_per_page
                '%s'  // parameter_config
            ]
        );

        if ($result === false) {
            return [
                'error' => true,
                'message' => 'Failed to create template'
            ];
        }

        return [
            'success' => true,
            'id' => $wpdb->insert_id
        ];
    }

    public function update($id, $data) {
        global $wpdb;

        // Ensure JSON fields are valid
        if (isset($data['query_settings'])) {
            $data['query_settings'] = json_encode(json_decode($data['query_settings'], true) ?: new \stdClass());
        }
        if (isset($data['parameter_config'])) {
            $data['parameter_config'] = json_encode(json_decode($data['parameter_config'], true) ?: new \stdClass());
        }

        $result = $wpdb->update(
            $this->table_name,
            $data,
            ['id' => $id],
            array_fill(0, count($data), '%s'),
            ['%d']
        );

        if ($result === false) {
            return [
                'error' => true,
                'message' => 'Failed to update template'
            ];
        }

        return [
            'success' => true,
            'id' => $id
        ];
    }

    public function delete($id) {
        global $wpdb;

        $result = $wpdb->delete(
            $this->table_name,
            ['id' => $id],
            ['%d']
        );

        if ($result === false) {
            return [
                'error' => true,
                'message' => 'Failed to delete template'
            ];
        }

        return [
            'success' => true
        ];
    }

    public function get($id) {
        global $wpdb;

        $template = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE id = %d",
                $id
            ),
            ARRAY_A
        );

        if (!$template) {
            return null;
        }

        // Decode JSON fields
        $template['query_settings'] = json_decode($template['query_settings'], true);
        $template['parameter_config'] = json_decode($template['parameter_config'], true);

        return $template;
    }

    public function get_by_shortcode($shortcode) {
        global $wpdb;

        $template = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE shortcode = %s",
                $shortcode
            ),
            ARRAY_A
        );

        if (!$template) {
            return null;
        }

        // Decode JSON fields
        $template['query_settings'] = json_decode($template['query_settings'], true);
        $template['parameter_config'] = json_decode($template['parameter_config'], true);

        return $template;
    }

    public function get_all() {
        global $wpdb;

        $templates = $wpdb->get_results(
            "SELECT * FROM {$this->table_name} ORDER BY name ASC",
            ARRAY_A
        );

        foreach ($templates as &$template) {
            $template['query_settings'] = json_decode($template['query_settings'], true);
            $template['parameter_config'] = json_decode($template['parameter_config'], true);
        }

        return $templates;
    }

    public function validate_shortcode($shortcode, $template_id = null) {
        global $wpdb;

        $query = "SELECT COUNT(*) FROM {$this->table_name} WHERE shortcode = %s";
        $params = [$shortcode];

        if ($template_id) {
            $query .= " AND id != %d";
            $params[] = $template_id;
        }

        $exists = $wpdb->get_var($wpdb->prepare($query, $params));

        return $exists == 0;
    }
} 