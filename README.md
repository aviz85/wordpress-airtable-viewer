# WordPress Airtable Viewer

A WordPress plugin that allows you to display Airtable content using customizable templates and shortcodes.

## Features

- Create multiple templates for displaying Airtable data
- Customizable HTML templates with variable support
- Shortcode system for easy integration
- Pagination support
- Caching for better performance
- Filter and sort options
- Responsive design
- AJAX-powered pagination
- Secure API key management

## Requirements

- WordPress 5.8 or higher
- PHP 7.4 or higher
- Airtable API key

## Installation

1. Download the plugin zip file
2. Go to WordPress admin > Plugins > Add New
3. Click "Upload Plugin" and select the zip file
4. Activate the plugin
5. Go to Airtable Viewer > Settings and enter your API key

## Usage

### Creating a Template

1. Go to Airtable Viewer > Templates
2. Click "Add New"
3. Fill in the template details:
   - Name: A descriptive name for your template
   - Shortcode: A unique identifier for the shortcode
   - Base ID: Your Airtable base ID
   - Table Name: The name of your Airtable table
   - Query Settings: Optional filter and sort parameters
   - HTML Templates: Define how your data should be displayed

### Template Variables

You can use these variables in your HTML templates:

- `{{field_name}}`: Displays the value of an Airtable field
- `{{index}}`: The current record index (1-based)
- `{{record_id}}`: The Airtable record ID

### Example Template

```html
<!-- Prefix HTML -->
<ul class="product-list">

<!-- Main HTML (repeated for each record) -->
<li class="product">
    <h3>{{name}}</h3>
    <p class="price">${{price}}</p>
    <p class="description">{{description}}</p>
</li>

<!-- Suffix HTML -->
</ul>
```

### Using Shortcodes

Basic usage:
```
[airtable_view template="your-template-shortcode"]
```

With parameters:
```
[airtable_view template="products" filter="category=electronics" sort="price:desc" page="1" limit="10"]
```

### Pagination

To enable pagination:
1. Edit your template
2. Check "Enable pagination"
3. Set the number of items per page

## Advanced Usage

### Caching

The plugin caches API responses to improve performance. You can configure the cache duration in the settings.

### Custom Styling

You can add custom CSS in the plugin settings to style all Airtable views.

### Query Parameters

The plugin supports Airtable's filtering and sorting syntax:

- Filtering: Use Airtable formula syntax
- Sorting: Use `field:asc` or `field:desc`

## Security

- API keys are stored securely in WordPress options
- All user inputs are sanitized
- Nonce verification for all admin actions
- Capability checks for administrative functions

## Development

### File Structure

```
wordpress-airtable-viewer/
├── admin/                 # Admin interface files
├── includes/             # Core functionality
├── public/               # Public-facing files
└── languages/            # Translations
```

### Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## License

This project is licensed under the GPL v2 or later.

## Support

For support, please [create an issue](https://github.com/yourusername/wordpress-airtable-viewer/issues) on GitHub. 