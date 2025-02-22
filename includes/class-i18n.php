<?php
namespace AirtableViewer;

class I18n {
    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            'airtable-viewer',
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );
    }
} 