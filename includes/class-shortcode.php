<?php
namespace AirtableViewer;

class Shortcode {
    private $plugin_name;
    private $version;
    private $processor;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->processor = new Processor();
    }

    public function render_shortcode($atts) {
        // Extract attributes
        $atts = shortcode_atts(array(
            'template' => '',
            'filter' => '',
            'sort' => '',
            'page' => 1,
            'limit' => null
        ), $atts, 'airtable_view');

        // Get template by shortcode
        $template = new Template();
        $template_data = $template->get_by_shortcode($atts['template']);

        if (!$template_data) {
            return '<p>Template not found: ' . esc_html($atts['template']) . '</p>';
        }

        // Process any additional parameters
        if (!empty($atts['filter'])) {
            $template_data['query_settings']['formula'] = $atts['filter'];
        }
        if (!empty($atts['sort'])) {
            $template_data['query_settings']['sort'] = $atts['sort'];
        }
        if (!empty($atts['limit'])) {
            $template_data['items_per_page'] = intval($atts['limit']);
        }

        // Add shortcode assets
        $this->enqueue_assets();

        // Process the template
        return $this->processor->process_shortcode($template_data['id'], $atts);
    }

    private function enqueue_assets() {
        wp_enqueue_style(
            $this->plugin_name,
            AIRTABLE_VIEWER_PLUGIN_URL . 'public/css/public.css',
            array(),
            $this->version
        );

        wp_enqueue_script(
            $this->plugin_name,
            AIRTABLE_VIEWER_PLUGIN_URL . 'public/js/public.js',
            array('jquery'),
            $this->version,
            true
        );

        wp_localize_script(
            $this->plugin_name,
            'airtableViewerData',
            array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('airtable_viewer_nonce')
            )
        );
    }
} 