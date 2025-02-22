<?php
namespace AirtableViewer;

class Plugin {
    protected $loader;
    protected $plugin_name;
    protected $version;

    public function __construct() {
        $this->plugin_name = 'airtable-viewer';
        $this->version = AIRTABLE_VIEWER_VERSION;
        
        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    private function load_dependencies() {
        require_once AIRTABLE_VIEWER_PLUGIN_DIR . 'includes/class-loader.php';
        require_once AIRTABLE_VIEWER_PLUGIN_DIR . 'includes/class-i18n.php';
        require_once AIRTABLE_VIEWER_PLUGIN_DIR . 'includes/class-airtable-api.php';
        require_once AIRTABLE_VIEWER_PLUGIN_DIR . 'includes/class-template.php';
        require_once AIRTABLE_VIEWER_PLUGIN_DIR . 'includes/class-processor.php';
        require_once AIRTABLE_VIEWER_PLUGIN_DIR . 'includes/class-shortcode.php';
        require_once AIRTABLE_VIEWER_PLUGIN_DIR . 'admin/class-admin.php';
        
        $this->loader = new Loader();
    }

    private function set_locale() {
        $plugin_i18n = new I18n();
        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    private function define_admin_hooks() {
        $plugin_admin = new Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_menu_pages');
        
        // Ajax handlers
        $this->loader->add_action('wp_ajax_airtable_viewer_save_template', $plugin_admin, 'save_template');
        $this->loader->add_action('wp_ajax_airtable_viewer_delete_template', $plugin_admin, 'delete_template');
        $this->loader->add_action('wp_ajax_airtable_viewer_get_tables', $plugin_admin, 'get_tables');
    }

    private function define_public_hooks() {
        $shortcode = new Shortcode($this->get_plugin_name(), $this->get_version());
        add_shortcode('airtable_view', [$shortcode, 'render_shortcode']);
    }

    public function run() {
        $this->loader->run();
    }

    public function get_plugin_name() {
        return $this->plugin_name;
    }

    public function get_version() {
        return $this->version;
    }
} 