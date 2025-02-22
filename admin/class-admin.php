<?php
namespace AirtableViewer;

class Admin {
    private $plugin_name;
    private $version;
    private $template;
    private $api;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->template = new Template();
        $this->api = new Airtable_API();
    }

    public function enqueue_styles() {
        wp_enqueue_style(
            $this->plugin_name . '-admin',
            AIRTABLE_VIEWER_PLUGIN_URL . 'admin/css/admin.css',
            array(),
            $this->version
        );
    }

    public function enqueue_scripts() {
        wp_enqueue_script(
            $this->plugin_name . '-admin',
            AIRTABLE_VIEWER_PLUGIN_URL . 'admin/js/admin.js',
            array('jquery'),
            $this->version,
            true
        );

        wp_localize_script(
            $this->plugin_name . '-admin',
            'airtableViewerAdmin',
            array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('airtable_viewer_admin_nonce')
            )
        );
    }

    public function add_menu_pages() {
        add_menu_page(
            'Airtable Viewer',
            'Airtable Viewer',
            'manage_options',
            'airtable-viewer',
            array($this, 'render_templates_page'),
            'dashicons-grid-view'
        );

        add_submenu_page(
            'airtable-viewer',
            'Templates',
            'Templates',
            'manage_options',
            'airtable-viewer',
            array($this, 'render_templates_page')
        );

        add_submenu_page(
            'airtable-viewer',
            'Settings',
            'Settings',
            'manage_options',
            'airtable-viewer-settings',
            array($this, 'render_settings_page')
        );
    }

    public function render_templates_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        $template_id = isset($_GET['template_id']) ? intval($_GET['template_id']) : 0;

        switch ($action) {
            case 'edit':
                require_once AIRTABLE_VIEWER_PLUGIN_DIR . 'admin/views/template-edit.php';
                break;
            case 'new':
                require_once AIRTABLE_VIEWER_PLUGIN_DIR . 'admin/views/template-edit.php';
                break;
            default:
                require_once AIRTABLE_VIEWER_PLUGIN_DIR . 'admin/views/templates.php';
                break;
        }
    }

    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Save settings if form was submitted
        if (isset($_POST['submit']) && check_admin_referer('airtable_viewer_settings')) {
            $settings = array(
                'api_key' => sanitize_text_field($_POST['api_key']),
                'default_base_id' => sanitize_text_field($_POST['default_base_id']),
                'cache_duration' => intval($_POST['cache_duration']),
                'global_styles' => sanitize_textarea_field($_POST['global_styles'])
            );
            
            update_option('airtable_viewer_settings', $settings);
            add_settings_error(
                'airtable_viewer_messages',
                'settings_updated',
                'Settings saved.',
                'updated'
            );
        }

        require_once AIRTABLE_VIEWER_PLUGIN_DIR . 'admin/views/settings.php';
    }

    public function save_template() {
        check_ajax_referer('airtable_viewer_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
            return;
        }

        $data = array(
            'name' => sanitize_text_field($_POST['name']),
            'shortcode' => sanitize_text_field($_POST['shortcode']),
            'base_id' => sanitize_text_field($_POST['base_id']),
            'table_name' => sanitize_text_field($_POST['table_name']),
            'query_settings' => wp_json_encode(array(
                'formula' => sanitize_text_field($_POST['formula']),
                'sort' => sanitize_text_field($_POST['sort']),
                'view' => sanitize_text_field($_POST['view'])
            )),
            'prefix_html' => wp_kses_post($_POST['prefix_html']),
            'main_html' => wp_kses_post($_POST['main_html']),
            'suffix_html' => wp_kses_post($_POST['suffix_html']),
            'pagination_enabled' => isset($_POST['pagination_enabled']) ? 1 : 0,
            'items_per_page' => intval($_POST['items_per_page']),
            'parameter_config' => wp_json_encode(array())
        );

        if (!empty($_POST['id'])) {
            $result = $this->template->update(intval($_POST['id']), $data);
        } else {
            $result = $this->template->create($data);
        }

        if (isset($result['error'])) {
            wp_send_json_error($result['message']);
        } else {
            wp_send_json_success($result);
        }
    }

    public function delete_template() {
        check_ajax_referer('airtable_viewer_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
            return;
        }

        $template_id = intval($_POST['template_id']);
        $result = $this->template->delete($template_id);

        if (isset($result['error'])) {
            wp_send_json_error($result['message']);
        } else {
            wp_send_json_success();
        }
    }

    public function get_tables() {
        check_ajax_referer('airtable_viewer_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
            return;
        }

        $base_id = sanitize_text_field($_POST['base_id']);
        $result = $this->api->get_table_schema($base_id, '');

        if (isset($result['error'])) {
            wp_send_json_error($result['message']);
        } else {
            wp_send_json_success($result);
        }
    }
} 