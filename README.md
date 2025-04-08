# VMC Simple Cache

A lightweight, file-based caching solution for WordPress developed by VMCSoft.

## Description

VMC Simple Cache is a performance optimization plugin that caches your WordPress pages and posts to improve load times. It's designed to be simple, efficient, and easy to configure.

### Features

- File-based caching system
- Cache front page, blog page, and individual posts
- Configurable cache TTL (Time To Live)
- Debug mode for troubleshooting
- Automatic cache clearing on post updates
- Admin interface for easy management
- Cache disabled for logged-in users
- Secure cache directory protection

## Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher
- Write permissions for the wp-content/cache directory

## Installation

1. Download the plugin
2. Upload the plugin files to the `/wp-content/plugins/vmc-simple-cache` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Go to Settings > VMC Simple Cache to configure the plugin

## Configuration

### Cache Settings

1. **Front Page Caching**
   - Enable/disable caching for the front page
   - Useful for static homepages

2. **Blog Page Caching**
   - Enable/disable caching for the blog page
   - Useful for sites using a separate blog page

3. **Cache TTL**
   - Set how long cached content should be valid
   - Range: 60 seconds to 24 hours (86400 seconds)
   - Default: 1 hour (3600 seconds)

4. **Debug Mode**
   - Enable/disable debug information
   - Adds HTML comments with cache status
   - Useful for troubleshooting

### Cache Management

- Use the "Clear Cache" button to manually clear all cached content
- Cache is automatically cleared when posts are updated
- Cache is disabled for logged-in users

## Support

For support, please visit:
- Website: https://vmcsoft.com/support
- Email: info@vmcsoft.com
- GitHub: https://github.com/vmcsoft/vmc-simple-cache

## Development

Developed and maintained by VMCSoft.

### Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This plugin is licensed under the GPL v2 or later.

## Credits

Developed by VMCSoft - https://vmcsoft.com 