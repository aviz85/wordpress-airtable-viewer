<?php
namespace AirtableViewer;

class Processor {
    private $api;
    private $template;
    private $current_page = 1;
    private $total_records = 0;

    public function __construct() {
        $this->api = new Airtable_API();
        $this->template = new Template();
    }

    public function process_shortcode($template_id, $atts = []) {
        $template_data = $this->template->get($template_id);
        if (!$template_data) {
            return '<p>Template not found.</p>';
        }

        // Process pagination
        $this->current_page = isset($atts['page']) ? max(1, intval($atts['page'])) : 1;
        $per_page = $template_data['pagination_enabled'] ? $template_data['items_per_page'] : 100;

        // Build query parameters
        $params = $this->build_query_params($template_data, $atts, $per_page);
        
        // Get records from Airtable
        $response = $this->api->get_table_records(
            $template_data['base_id'],
            $template_data['table_name'],
            $params
        );

        if (isset($response['error'])) {
            return '<p>Error: ' . esc_html($response['message']) . '</p>';
        }

        $this->total_records = $response['total_records'] ?? count($response['records']);
        
        return $this->render_template($template_data, $response['records']);
    }

    private function build_query_params($template, $atts, $per_page) {
        $params = [];

        // Add formula if specified in template or attributes
        if (!empty($template['query_settings']['formula'])) {
            $params['filterByFormula'] = $this->parse_formula($template['query_settings']['formula'], $atts);
        }

        // Add sorting if specified
        if (!empty($template['query_settings']['sort'])) {
            $params['sort'] = $template['query_settings']['sort'];
        }

        // Add pagination parameters
        if ($template['pagination_enabled']) {
            $params['pageSize'] = $per_page;
            $params['offset'] = ($this->current_page - 1) * $per_page;
        }

        // Add view if specified
        if (!empty($template['query_settings']['view'])) {
            $params['view'] = $template['query_settings']['view'];
        }

        return $params;
    }

    private function parse_formula($formula, $atts) {
        // Replace parameter placeholders with actual values
        preg_match_all('/\{\{(.*?)\}\}/', $formula, $matches);
        
        foreach ($matches[1] as $param) {
            $value = isset($atts[$param]) ? $atts[$param] : '';
            $formula = str_replace('{{'.$param.'}}', $value, $formula);
        }

        return $formula;
    }

    private function render_template($template, $records) {
        $output = '';

        // Add prefix if exists
        if (!empty($template['prefix_html'])) {
            $output .= $template['prefix_html'];
        }

        // Process each record
        foreach ($records as $index => $record) {
            $html = $template['main_html'];
            
            // Replace field variables
            foreach ($record['fields'] as $field => $value) {
                $placeholder = '{{'.$field.'}}';
                
                // Handle array values
                if (is_array($value)) {
                    $value = implode(', ', $value);
                }
                
                $html = str_replace($placeholder, esc_html($value), $html);
            }

            // Replace special variables
            $html = str_replace('{{index}}', $index + 1, $html);
            $html = str_replace('{{record_id}}', $record['id'], $html);
            
            $output .= $html;
        }

        // Add suffix if exists
        if (!empty($template['suffix_html'])) {
            $output .= $template['suffix_html'];
        }

        // Add pagination if enabled
        if ($template['pagination_enabled'] && $this->total_records > $template['items_per_page']) {
            $output .= $this->render_pagination($template);
        }

        return $output;
    }

    private function render_pagination($template) {
        $total_pages = ceil($this->total_records / $template['items_per_page']);
        
        if ($total_pages <= 1) {
            return '';
        }

        $output = '<div class="airtable-viewer-pagination">';
        
        // Previous page
        if ($this->current_page > 1) {
            $output .= sprintf(
                '<a href="%s" class="prev">%s</a>',
                add_query_arg('page', $this->current_page - 1),
                '&laquo; Previous'
            );
        }

        // Page numbers
        for ($i = 1; $i <= $total_pages; $i++) {
            if ($i == $this->current_page) {
                $output .= sprintf('<span class="current">%d</span>', $i);
            } else {
                $output .= sprintf(
                    '<a href="%s">%d</a>',
                    add_query_arg('page', $i),
                    $i
                );
            }
        }

        // Next page
        if ($this->current_page < $total_pages) {
            $output .= sprintf(
                '<a href="%s" class="next">%s</a>',
                add_query_arg('page', $this->current_page + 1),
                'Next &raquo;'
            );
        }

        $output .= '</div>';

        return $output;
    }
} 