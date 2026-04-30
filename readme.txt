=== Cabin Analytics Dashboard ===

Contributors:      Stephen Walker
Tags:              block, analytics, cabin, dashboard, charts, popular content
Tested up to:      7.0
Stable tag:        2.0.1
License:           GPLv2 or later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html

Display Cabin Analytics charts, dashboard widgets, shortcodes, and popular content lists in WordPress.

== Description ==

Cabin Analytics Dashboard integrates Cabin Analytics into your WordPress site. It provides dashboard widgets, Gutenberg blocks, shortcodes, and popular content output powered by the Cabin Analytics API.

**Features:**

* Dashboard widget for viewing analytics in WordPress admin
* Polished standalone Cabin Popular Content dashboard widget with title, domain, controls, and summary/detail metrics
* Gutenberg block for analytics charts
* Gutenberg block for Popular Content
* Shortcode support for analytics charts
* Popular Content shortcode
* Reconciles Cabin page paths to local WordPress public post types
* Configurable popular content title, quantity, and date range for shortcode and block output
* Popular Content date range setting for shortcode, block, and dashboard widget
* Interactive stacked bar and line charts showing page views and unique visitors
* Configurable date ranges
* Global settings for API key, default domain, chart options, and popular content defaults
* Responsive front-end output

**Popular Content:**

The Popular Content feature retrieves top pages from Cabin Analytics and attempts to match each path to a local WordPress post, page, or public custom post type. Matched items are displayed using the local WordPress title and permalink.

Default shortcode:

`[cabin_popular_content]`

With custom options:

`[cabin_popular_content qty="10" date_range="30" title="Top Content"]`

Parameters:

* `qty` - Number of posts to display
* `date_range` - `1`, `7`, `14`, `30`, or `90`
* `title` - Optional heading text

If no shortcode title or date range is provided, the plugin uses the values configured in Settings → Cabin Analytics.

**Dashboard Popular Content:**

Popular Content is available as a standalone WordPress dashboard widget titled “Cabin Popular Content.” It can be shown, hidden, and moved using the standard WordPress Dashboard Screen Options. The widget displays a title and domain, supports date range controls, supports quantity controls for 5, 10, 20, or 50 items, and renders each item as a summary/detail entry with page views, unique visitors, average duration seconds, and CO2 grams.

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

Yes. The plugin registers a standalone dashboard widget titled “Cabin Popular Content.” Manage its visibility and placement from the WordPress Dashboard Screen Options. The widget includes controls for date range and item count. The default date range is controlled in Settings → Cabin Analytics.

= What date ranges are supported for Popular Content? =

Popular Content supports `1`, `7`, `14`, `30`, and `90` day ranges. The default is controlled in Settings → Cabin Analytics.

= Does this work with Cabin Analytics v1 or v2? =

This plugin uses the Cabin Analytics API v1 as documented at https://docs.withcabin.com/api

== Screenshots ==

1. Dashboard widget showing analytics data with interactive charts
2. Standalone Cabin Popular Content dashboard widget with summary/detail metrics
3. Block editor interface with customization options
4. Admin settings page for global configuration
5. Line chart view with date range selector
6. Stacked bar chart showing unique visitors vs total visitors
7. Popular Content block output

== Changelog ==

= 2.0.1 =
* Fixed Plugin Check i18n translator comments and escaped segmented control output.
* Restricted dashboard widgets to content creators and kept them available to users who can edit posts.
* Hardened the Cabin stats REST endpoint by limiting requests to the configured domain and supported date ranges.
* Changed the stored API key field to a password input.
* Removed development-only files from the distributable WordPress plugin package.

= 2.0 =
* Fixed dashboard asset cache-busting so updated chart widget JavaScript and CSS load reliably without changing the public plugin version
* Aligned chart dashboard widget header, title, domain, and controls with the Popular Content dashboard widget
* Added visible labels for chart type and date range controls in the chart dashboard widget
* Redesigned the standalone Cabin Popular Content dashboard widget
* Added a dashboard title and domain display matching the chart widget pattern
* Added labeled, reusable dashboard controls for Popular Content date range
* Added labeled, reusable dashboard controls for item quantity: 5, 10, 20, or 50
* Rendered each dashboard Popular Content item as a summary/detail component
* Added dashboard metrics for page views, unique visitors, average duration seconds, and CO2 grams
* Added View Content links beside View Details in dashboard Popular Content summaries
* Updated dashboard Popular Content metric labels to use readable text without underscores or colons
* Increased spacing between dashboard Popular Content date range and quantity controls
* Refactored dashboard Popular Content controls into a shared segmented control helper
* Removed links from dashboard Popular Content titles and added accessible View Content link labels
* Restored the [cabin_analytics] chart shortcode
* Removed development-only files from the distributable WordPress plugin package

= 1.5.1 =
* Fixed missing Popular Content date range helper used by the REST endpoint.

= 1.5 =
* Added a standalone Cabin Popular Content dashboard widget
* Removed the Popular Content enable checkbox from the settings screen; the widget can now be managed with WordPress Dashboard Screen Options
* Removed the Popular Content title setting from the settings screen
* Reused the Popular Content default date range setting for the shortcode, block, and dashboard widget
* Updated plugin version to 1.5

= 1.3.1 =
* Changed the default Popular Content title to Top Content
* Added dashboard widget styling for Popular Content output

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
