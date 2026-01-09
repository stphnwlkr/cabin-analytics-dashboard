
=== Cabin Analytics Dashboard ===

Contributors:      Stephen Walker, WordPress Telex
Tags:              block, analytics, cabin, dashboard, charts
Tested up to:      6.9
Stable tag:        1.0.1
License:           GPLv2 or later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html

Display beautiful analytics charts from Cabin Analytics with dashboard widgets, blocks, and shortcodes.

== Description ==

Cabin Analytics Dashboard seamlessly integrates Cabin Analytics into your WordPress site. This plugin provides multiple ways to display your site analytics:

<img width="1212" height="766" alt="image" src="https://github.com/user-attachments/assets/02022d28-5fad-4158-a3dd-ccb1ed75fdce" />


* **Dashboard Widget**: View your analytics directly in the WordPress admin dashboard
* **Gutenberg Block**: Add analytics charts to any post or page using the block editor
* **Shortcode**: Use `[cabin_analytics]` shortcode for maximum flexibility

**Features:**

* Interactive stacked bar and line charts showing page views and unique visitors
* Switch between chart types (bar/line) on the fly
* Configurable date ranges (7, 14, 30, or 90 days)
* Global settings in WordPress admin for API key, default domain, and preferences
* Per-block/shortcode overrides for domain and display settings
* Real-time data fetching from Cabin Analytics API
* Responsive design that works on all devices
* Clean, modern UI matching WordPress design patterns

**Chart Options:**

* Stacked bar chart for daily comparisons
* Line chart for trend visualization
* Toggle between views without page reload
* Hover tooltips showing detailed statistics

**Admin Configuration:**

Set up your Cabin Analytics integration once in Settings → Cabin Analytics:
* Add your Cabin Analytics API key
* Configure default domain to track
* Choose default chart type (bar or line)
* Set default date range
* Test API connection

**Block & Shortcode Customization:**

Each block or shortcode instance can override defaults:
* Track different domains
* Use different chart types
* Show different date ranges
* Allow or restrict user interactions

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/cabin-analytics-dashboard` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Navigate to Settings → Cabin Analytics to configure your API key and preferences
4. Add the Cabin Analytics Dashboard block to any page or use the `[cabin_analytics]` shortcode

== Frequently Asked Questions ==

= Where do I get a Cabin Analytics API key? =

Visit https://withcabin.com and log into your account. Navigate to your site settings to generate an API key.

= Can I display analytics for multiple domains? =

Yes! While you set a default domain in the settings, each block or shortcode instance can override this to display analytics for different domains.

= What's the difference between the block and shortcode? =

They provide the same functionality. Use the block in the Gutenberg editor for a visual interface, or use the shortcode `[cabin_analytics]` in classic editor, widgets, or custom code.

= Can visitors switch between chart types? =

Yes, by default visitors can toggle between bar and line charts and change date ranges. You can disable this in block settings if you prefer.

= Does this work with Cabin Analytics v1 or v2? =

This plugin uses the Cabin Analytics API v1 as documented at https://docs.withcabin.com/api

== Screenshots ==

1. Dashboard widget showing analytics data with interactive charts
2. Block editor interface with customization options
3. Admin settings page for global configuration
4. Line chart view with date range selector
5. Stacked bar chart showing unique visitors vs total visitors

== Changelog ==

= 0.1.0 =
* Initial release
* Dashboard widget integration
* Gutenberg block with full customization
* Shortcode support
* Admin settings page
* Interactive chart switching (bar/line)
* Multiple date range options (7, 14, 30, 90 days)
* API key management
* Multi-domain support

== Shortcode Usage ==

Basic usage:
`[cabin_analytics]`

With custom parameters:
`[cabin_analytics domain="example.com" chart_type="line" date_range="30" allow_switching="true"]`

Parameters:
* `domain` - Override default domain
* `chart_type` - "bar" or "line"
* `date_range` - "7", "14", "30", or "90"
* `allow_switching` - "true" or "false"
