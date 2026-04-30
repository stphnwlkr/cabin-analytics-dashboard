=== Cabin Analytics Dashboard ===

Contributors:      Stephen Walker
Tags:              block, analytics, cabin, dashboard, charts, popular content
Tested up to:      7.0
Stable tag:        1.3
License:           GPLv2 or later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html

Display Cabin Analytics charts, dashboard widgets, shortcodes, and popular content lists in WordPress.

== Description ==

Cabin Analytics Dashboard integrates Cabin Analytics into your WordPress site. It provides dashboard widgets, Gutenberg blocks, shortcodes, and popular content output powered by the Cabin Analytics API.

**Features:**

* Dashboard widget for viewing analytics in WordPress admin
* Optional Popular Content output in the WordPress dashboard widget
* Gutenberg block for analytics charts
* Gutenberg block for Popular Content
* Shortcode support for analytics charts
* Popular Content shortcode
* Reconciles Cabin page paths to local WordPress public post types
* Configurable popular content title, quantity, and date range
* Dashboard-specific Popular Content date range setting
* Interactive stacked bar and line charts showing page views and unique visitors
* Configurable date ranges
* Global settings for API key, default domain, chart options, and popular content defaults
* Responsive front-end output

**Popular Content:**

The Popular Content feature retrieves top pages from Cabin Analytics and attempts to match each path to a local WordPress post, page, or public custom post type. Matched items are displayed using the local WordPress title and permalink.

Default shortcode:

`[cabin_popular_content]`

With custom options:

`[cabin_popular_content qty="10" date_range="30" title="Top Pages"]`

Parameters:

* `qty` - Number of posts to display
* `date_range` - `1`, `7`, `14`, `30`, or `90`
* `title` - Optional heading text

If no shortcode title or date range is provided, the plugin uses the values configured in Settings → Cabin Analytics.

**Dashboard Popular Content:**

Popular Content can also be displayed inside the WordPress dashboard widget. Enable it from Settings → Cabin Analytics and choose a dashboard-specific date range of `1`, `7`, `14`, `30`, or `90` days.

**Chart Shortcode:**

Basic usage:

`[cabin_analytics]`

With custom parameters:

`[cabin_analytics domain="example.com" chart_type="line" date_range="30" allow_switching="true"]`

Parameters:

* `domain` - Override default domain
* `chart_type` - `bar` or `line`
* `date_range` - `7`, `14`, `30`, or `90`
* `allow_switching` - `true` or `false`

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/cabin-analytics-dashboard` directory, or install the plugin through the WordPress plugins screen.
2. Activate the plugin through the Plugins screen in WordPress.
3. Navigate to Settings → Cabin Analytics.
4. Add your Cabin Analytics API key and default domain.
5. Add the Cabin Analytics Dashboard block, Cabin Popular Content block, or use the included shortcodes.

== Frequently Asked Questions ==

= Where do I get a Cabin Analytics API key? =

Visit https://withcabin.com and log into your account. Navigate to your site settings to generate an API key.

= Can I display analytics for multiple domains? =

Yes. You can configure a default domain in the settings, and individual shortcode or block instances can override it.

= How does Popular Content work? =

The plugin requests top page data from Cabin Analytics, normalizes each returned path, then attempts to match that path to a local WordPress post, page, or public custom post type. Matching content is displayed with the local title and permalink.

= Can Popular Content appear on the WordPress dashboard? =

Yes. Enable “Show Popular Content on Dashboard” in Settings → Cabin Analytics. You can also choose a separate dashboard date range.

= What date ranges are supported for Popular Content? =

Popular Content supports `1`, `7`, `14`, `30`, and `90` day ranges. The default is controlled in Settings → Cabin Analytics.

= Does this work with Cabin Analytics v1 or v2? =

This plugin uses the Cabin Analytics API v1 as documented at https://docs.withcabin.com/api

== Screenshots ==

1. Dashboard widget showing analytics data with interactive charts
2. Optional Popular Content output in the dashboard widget
3. Block editor interface with customization options
4. Admin settings page for global configuration
5. Line chart view with date range selector
6. Stacked bar chart showing unique visitors vs total visitors
7. Popular Content block output

== Changelog ==

= 1.3 =
* Added optional Popular Content output to the WordPress dashboard widget
* Added dashboard-specific Popular Content date range setting
* Updated plugin version to 1.3

= 1.2 =
* Added Cabin Popular Content Gutenberg block
* Added Popular Content shortcode
* Added admin settings for Popular Content title and default date range
* Added local path reconciliation for public WordPress post types
* Updated compatibility testing through WordPress 7.0

= 1.0.2 =
* Updated plugin metadata and compatibility requirements

= 1.0.0 =
* Initial release
* Dashboard widget integration
* Gutenberg block with customization
* Shortcode support
* Admin settings page
* Interactive chart switching
* Multiple date range options
* API key management
* Multi-domain support
