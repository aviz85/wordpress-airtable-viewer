<?php
/**
 * Plugin Name: Airtable Viewer
 * Plugin URI: https://github.com/yourusername/wordpress-airtable-viewer
 * Description: Display Airtable content in WordPress using customizable templates and shortcodes
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 * Text Domain: airtable-viewer
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Plugin version
define('AIRTABLE_VIEWER_VERSION', '1.0.0');
define('AIRTABLE_VIEWER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AIRTABLE_VIEWER_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Autoload classes
 */
spl_autoload_register(function ($class_name) {
    $namespace = 'AirtableViewer\\';

    if (strpos($class_name, $namespace) === 0) {
        $classes_dir = realpath(plugin_dir_path(__FILE__)) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR;
        $class_file = str_replace('\\', DIRECTORY_SEPARATOR, str_replace($namespace, '', $class_name)) . '.php';
        $file = $classes_dir . $class_file;
        
        if (file_exists($file)) {
            require_once $file;
        }
    }
});

// Activation hook
register_activation_hook(__FILE__, function() {
    require_once AIRTABLE_VIEWER_PLUGIN_DIR . 'includes/class-activator.php';
    AirtableViewer\Activator::activate();
});

// Deactivation hook
register_deactivation_hook(__FILE__, function() {
    require_once AIRTABLE_VIEWER_PLUGIN_DIR . 'includes/class-deactivator.php';
    AirtableViewer\Deactivator::deactivate();
});

/**
 * Begin execution of the plugin
 */
function run_airtable_viewer() {
    require_once AIRTABLE_VIEWER_PLUGIN_DIR . 'includes/class-plugin.php';
    $plugin = new AirtableViewer\Plugin();
    $plugin->run();
}

run_airtable_viewer(); 