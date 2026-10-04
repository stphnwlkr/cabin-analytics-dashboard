=== Uplink Analytics Dashboard for Cabin ===

Contributors:      stphnwlkr
Tags:              analytics, cabin, dashboard, charts, privacy
Requires at least: 6.9
Requires PHP:      8.3
Tested up to:      7.1
Stable tag:        2.0.0
License:           GPL-2.0-or-later
License URI:       https://spdx.org/licenses/GPL-2.0-or-later.html

Display Cabin Analytics charts, dashboard widgets, shortcodes, and popular content lists in WordPress.

== Description ==

Uplink Analytics Dashboard for Cabin integrates Cabin Analytics into your WordPress site. It provides dashboard widgets, Gutenberg blocks, shortcodes, and popular content output powered by the Cabin Analytics API.

This is an independent UplinkPress plugin. It is not affiliated with, endorsed by, or an official product of Cabin. Learn more about the plugin at [uplink.press](https://uplink.press/code/uplink-analytics-dashboard-for-cabin/).

The Cabin Analytics API is available to Cabin PRO accounts.

**Features:**

* Dashboard widget for viewing analytics in WordPress admin
* Responsive Cabin Dashboard for authorized WordPress users
* Polished standalone Uplink Popular Content for Cabin dashboard widget with title, domain, controls, and summary/detail metrics
* Separate Sustainability, Demographics, and Sources dashboard widgets
* Per-module visibility controls for dashboard reports, blocks, and shortcodes
* Uplink Site Traffic for Cabin block for analytics charts
* Gutenberg block for Popular Content with the same interactive controls and expandable metrics as the Dashboard widget
* Gutenberg blocks for Sustainability, Demographics, and Sources
* Privacy-friendly Country Traffic Map block with an independent date range and accessible country values
* Live data previews for every Cabin block in the WordPress editor
* Editable headings for every analytics block, with clear report-specific defaults
* Shortcode support for analytics charts
* Popular Content shortcode
* Sustainability, Demographics, Sources, and Country Traffic Map shortcodes
* Reconciles Cabin page paths to local WordPress public post types
* Configurable popular content title, quantity, and date range for shortcode and block output
* Popular Content date range setting for shortcode, block, and dashboard widget
* Interactive stacked bar and line charts showing page views and unique visitors
* Configurable date ranges
* Global settings for API key, default domain, chart options, and popular content defaults
* Responsive front-end output

**Popular Content:**

The Popular Content feature retrieves top pages from Cabin Analytics and attempts to match each path to a local WordPress post, page, or public custom post type. Matching content uses the local WordPress title and permalink. If a Cabin path does not exist on the current WordPress installation, the plugin displays a readable path title linked to the configured Cabin domain instead.

Default shortcode:

`[uplianda_popular_content]`

With custom options:

`[uplianda_popular_content qty="10" date_range="30" title="Top Content"]`

Parameters:

* `qty` - Number of posts to display: `5`, `10`, `20`, or `50`
* `date_range` - `1`, `7`, `14`, `30`, or `90`
* `title` - Optional heading text

If no shortcode title or date range is provided, the plugin uses the values configured in Cabin Dashboard → Settings.

**Dashboard Popular Content:**

Popular Content is available as a standalone WordPress dashboard widget titled “Uplink Popular Content for Cabin” when WP dashboard widgets are enabled in Cabin Dashboard → Settings. It can be hidden, shown, and moved using the standard WordPress Dashboard Screen Options. Disabling WP dashboard widgets unregisters all five Cabin widgets, so they no longer appear in Screen Options. The widget, block, and shortcode share the same presentation: title and domain, date range controls, quantity controls for 5, 10, 20, or 50 items, and summary/detail entries with page views, unique visitors, and Attention. Attention uses Cabin's approximate duration style, such as `~52s` or `~2m`. Per-page CO₂ is displayed only when Cabin returns a positive value.

**Sustainability, Demographics, and Sources:**

The WordPress dashboard provides three movable widgets so each report stays compact:

* Sustainability displays available total and average CO₂, transfer volume, and green-hosting status.
* Demographics displays device mix, countries, languages, and browsers.
* Sources displays traffic sources and top referrers.

Each report also has a dynamic Block API v3 block and shortcode. Adding one to public content is the site owner's explicit choice to publish that Cabin data. The dashboard REST route remains limited to logged-in users who can edit posts.

Shortcodes:

`[uplianda_sustainability date_range="30" title="Sustainability"]`
`[uplianda_demographics date_range="30" title="Demographics"]`
`[uplianda_sources date_range="30" title="Sources"]`

Supported reporting periods are `7`, `14`, `30`, and `90` days.

Every module is enabled by default. Set a shortcode module to `false` to hide it:

`[uplianda_sustainability total_co2="true" average_co2="false" transfer_volume="true" green_hosting="true"]`
`[uplianda_demographics devices="true" countries="true" languages="false" browsers="false"]`
`[uplianda_sources traffic_sources="true" referrers="false"]`

The equivalent block settings are available in the block sidebar. Every block also provides a Heading field. Defaults are Site Traffic, Top Content, Sustainability, Demographics, Sources, and Country Traffic. Dashboard-widget modules are configured under Cabin Dashboard → Settings.

**Country Traffic Map:**

The Uplink Country Traffic Map for Cabin block plots Cabin's aggregated country totals on a responsive world choropleth. Each block has its own `7`, `14`, `30`, or `90` day reporting period. Pointer hover reveals a country tooltip. Keyboard users can Tab to the map once and move through countries in descending traffic order with the arrow keys; Home and End jump to the first and last country. A ranked list and expandable table provide the exact values without relying on color or the map graphic.

The map is rendered locally from bundled Natural Earth public-domain geometry. It does not use Google Maps, map tiles, geocoding, external map scripts, browser location, cookies, or visitor-level data. Only the server-side Cabin API request described under External Services is made.

Shortcode:

`[uplianda_country_map date_range="30" title="Traffic by Country"]`

**Cabin Dashboard:**

Uplink Dashboard for Cabin adds a backend-only analytics page to the WordPress admin menu. It presents the Cabin API data in a focused report with section navigation, selectable reporting periods, and light or dark mode. Access is controlled by role from the plugin settings.

The dashboard uses a purpose-built, responsive report layout inspired by Cabin's public analytics experience. A side rail keeps long reports navigable on larger screens, reporting periods are available in the header, and the local light/dark preference follows the user between visits. The page uses bundled fonts and makes no external font request.

Each Cabin report block keeps its own date range and module choices. For example, add four Demographics blocks in different locations and configure them separately for devices, countries, languages, and browsers.

Administrators have access by default and always retain access. Additional WordPress roles can be authorized under Cabin Dashboard → Settings → Cabin Dashboard.

The Cabin Dashboard is always available to authorized roles. Clear Enable WP dashboard widgets to unregister the five widgets from the standard WordPress Dashboard without changing the Cabin Dashboard, blocks, or shortcodes.

**Chart Shortcode:**

Basic usage:

`[uplianda]`

With custom parameters:

`[uplianda title="Site Traffic" domain="example.com" chart_type="line" date_range="30" allow_switching="true"]`

Parameters:

* `domain` - Override default domain
* `title` - Optional heading; defaults to `Site Traffic`
* `chart_type` - `bar` or `line`
* `date_range` - `7`, `14`, `30`, or `90`
* `allow_switching` - `true` or `false`

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/uplink-analytics-dashboard-for-cabin` directory, or install the plugin through the WordPress plugins screen.
2. Activate the plugin through the Plugins screen in WordPress.
3. Navigate to Cabin Dashboard → Settings.
4. Add your Cabin Analytics API key and default domain.
5. Add any Cabin chart, Popular Content, Sustainability, Demographics, Sources, or Country Traffic Map block, or use the included shortcodes.
6. Optionally open Cabin Dashboard to use the private, API-powered analytics workspace.

== wp-config.php Defaults ==

Site owners can define defaults in `wp-config.php`. Constants take precedence over values saved in the WordPress admin.

`define( 'UPLIANDA_API_KEY', 'your-api-key' );`
`define( 'UPLIANDA_DOMAIN', 'example.com' );`
`define( 'UPLIANDA_DEFAULT_CHART_TYPE', 'bar' );`
`define( 'UPLIANDA_DEFAULT_DATE_RANGE', '7' );`
`define( 'UPLIANDA_DASHBOARD_URL', 'https://withcabin.com' );`
`define( 'UPLIANDA_POPULAR_CONTENT_TITLE', 'Top Content' );`
`define( 'UPLIANDA_POPULAR_CONTENT_DATE_RANGE', '30' );`
`define( 'UPLIANDA_DASHBOARD_WIDGETS_ENABLED', true );`

Dashboard module defaults can also be locked with boolean constants. Available suffixes are:

* `UPLIANDA_DASHBOARD_SUSTAINABILITY_TOTAL_CO2`
* `UPLIANDA_DASHBOARD_SUSTAINABILITY_AVERAGE_CO2`
* `UPLIANDA_DASHBOARD_SUSTAINABILITY_TRANSFER_VOLUME`
* `UPLIANDA_DASHBOARD_SUSTAINABILITY_GREEN_HOSTING`
* `UPLIANDA_DASHBOARD_DEMOGRAPHICS_DEVICES`
* `UPLIANDA_DASHBOARD_DEMOGRAPHICS_COUNTRIES`
* `UPLIANDA_DASHBOARD_DEMOGRAPHICS_LANGUAGES`
* `UPLIANDA_DASHBOARD_DEMOGRAPHICS_BROWSERS`
* `UPLIANDA_DASHBOARD_SOURCES_TRAFFIC_SOURCES`
* `UPLIANDA_DASHBOARD_SOURCES_REFERRERS`

For example: `define( 'UPLIANDA_DASHBOARD_DEMOGRAPHICS_BROWSERS', false );`

== External Services ==

This plugin connects to the Cabin Analytics API at `https://api.withcabin.com/v1/analytics` to retrieve analytics data. It sends the configured domain, requested date range, requested data scope, list limit, and the administrator-provided API key. Requests occur when an analytics chart, Popular Content display, dashboard insight widget, Country Traffic Map, or public insight block or shortcode needs uncached data. Responses are cached temporarily in WordPress.

Cabin is a third-party service. This independent UplinkPress plugin is not affiliated with or endorsed by Cabin. A Cabin PRO account and API key are required. Review [Cabin](https://withcabin.com/?utm_campaign=wp_widget&utm_medium=web&utm_source=sw) before use.

The Country Traffic Map includes simplified geographic shapes generated at build time from [Natural Earth](https://www.naturalearthdata.com/downloads/110m-cultural-vectors/), whose map data is in the public domain. No request is made to Natural Earth or any other map provider when the plugin runs.

== Frequently Asked Questions ==

= Where do I get a Cabin Analytics API key? =

Visit https://withcabin.com and log into your account. Navigate to your site settings to generate an API key.

= Can I display analytics for multiple domains? =

Yes. You can configure a default domain in the settings, and individual shortcode or block instances can override it.

= How does Popular Content work? =

The plugin requests top page data from Cabin Analytics, normalizes each returned path, then attempts to match that path to a local WordPress post, page, or public custom post type. Matching content uses the local title and permalink. Unmatched paths fall back to a readable title and an HTTPS link on the configured Cabin domain.

= Can Popular Content appear on the WordPress dashboard? =

Yes. Enable WP dashboard widgets under Cabin Dashboard → Settings. The plugin then registers a standalone widget titled “Uplink Popular Content for Cabin.” Manage its visibility and placement from the WordPress Dashboard Screen Options. Turning off WP dashboard widgets unregisters it and the other Cabin widgets, removing their entries from Screen Options. The widget includes controls for date range and item count. The default date range is controlled in Cabin Dashboard → Settings.

= Can I build a custom analytics page inside WordPress admin? =

The Cabin Dashboard is a purpose-built WordPress admin page available only to authorized roles. It does not use a public post or custom post type. Blocks and shortcodes remain available separately for layouts you create in the WordPress editor.

= What date ranges are supported for Popular Content? =

Popular Content supports `1`, `7`, `14`, `30`, and `90` day ranges. The default is controlled in Cabin Dashboard → Settings.

= Does this work with Cabin Analytics v1 or v2? =

This plugin uses the Cabin Analytics API v1. Visit https://withcabin.com for current Cabin product and support information.

== Screenshots ==

1. Settings screen for connecting Cabin, choosing defaults, controlling dashboard access, and selecting report modules.
2. Responsive Demographics report showing device, country, language, and browser data.
3. Sustainability and Sources reports showing environmental metrics, acquisition channels, and referrers.
4. Custom private dashboard combining the Country Traffic Map, Site Traffic chart, Popular Content, and sustainability summary.

== Changelog ==

= 2.0.0 =
* Breaking: replaced the custom-post-type dashboard and editor workflow with an always-available Cabin Analytics page in WordPress admin. Existing private dashboard post layouts are no longer used.
* Kept existing Cabin blocks and shortcodes unchanged.
* Added a responsive Cabin-style report with light and dark themes, fixed period controls, section navigation, and bundled Public Sans and Wittgenstein fonts.
* Added reports for all available Cabin API data, including events, attention, screen sizes, operating systems, countries, sources, and environmental metrics.
* Added interactive chart modes, Cabin-style data balloons, country flags, an accessible world map, search, pagination, and View all controls.
* Replaced Enable Private Dashboard with Enable WP dashboard widgets. The Cabin Dashboard remains available when widgets are disabled.
* Preserved saved settings during plugin updates and kept Settings under the Cabin Dashboard menu.
* Added responsive layouts for narrower WordPress admin widths and smoother period and list transitions.

= 1.1.2 =
* Restored a visible View Dashboard button in the private dashboard editor and removed the hidden Options-menu action.

= 1.1.1 =
* Removed the redundant admin-bar and editor-panel dashboard links, leaving the editor Options menu return action.

= 1.1.0 =
* Added WordPress 7.1 icon registry support with a compatibility fallback for WordPress 6.9 and 7.0.
* Replaced the legacy chart Dashicon with the UplinkPress analytics icon in the admin menu and block editor.
* Shortened the private dashboard editor tab label to Dashboard.
* Added View Dashboard actions to the editor toolbar, Options menu, and Dashboard settings panel.
* Renamed the WordPress admin menu item to Cabin Dashboard.

= 1.0.1 =
* Fixed Popular Content date-range and quantity controls in dashboard widgets, blocks, and shortcodes.
* Fixed the Popular Content homepage entry so it uses the configured Cabin domain instead of the local WordPress front page.
* Fixed the Site Traffic dashboard widget so the standard WordPress show/hide control fully collapses its content.
* Corrected renamed data attributes used by the Country Traffic Map and private-dashboard style customizer.

= 1.0.0 =
* Initial release under the Uplink Analytics Dashboard for Cabin name by UplinkPress.
* Added dashboard widgets for analytics trends and popular content.
* Added blocks and shortcodes for charts and popular content.
* Added accessible chart controls, keyboard-readable data points, and status messaging.
* Added server-side Cabin API authentication, constrained public requests, and transient caching.
* Added wp-config.php defaults and safe fallback links for unmatched Popular Content paths.
* Added separate Sustainability, Demographics, and Sources dashboard widgets, Block API v3 blocks, and shortcodes.
* Added per-module visibility controls to report blocks, shortcodes, and dashboard settings.
* Renamed Popular Content average duration to Attention with Cabin-style approximate seconds and minutes.
* Refreshed the settings screen with grouped configuration and a complete usage reference.
* Added a distinct UplinkPress identity for this independent Cabin integration.
* Added explicit site-owner-controlled public output for Cabin insight data.
* Set minimum requirements to WordPress 6.9 and PHP 8.3; tested through WordPress 7.1.
* Hid unavailable zero-value CO₂ metrics from Popular Content.
* Added a private, role-controlled Uplink Dashboard for Cabin that authorized users can design freely with core and Cabin blocks.
* Isolated the private dashboard document so it inherits active-theme and responsive block styles without affecting WordPress admin.
* Added core color, typography, spacing, background, gradient, and border controls to Cabin blocks.
* Preserved the accessible Cabin blue/white functional palette for controls, links, focus indicators, charts, and progress bars instead of inheriting potentially low-contrast theme accents.
* Added live dashboard style controls with clearer visual descriptions for page, surface, typography, chart, progress, and spacing tokens.
* Prevented theme viewport rules from causing runaway private-dashboard iframe heights or internal scrollbars.
* Made private-dashboard links open outside the iframe with localized screen-reader announcements.
* Unified the Popular Content widget, block, and shortcode around the same interactive controls, expandable rows, and detail metrics.
* Added an accessible, privacy-friendly Country Traffic Map block and shortcode with independent reporting periods, pointer and keyboard tooltips, bundled public-domain Natural Earth geometry, and no third-party map requests.
* Added editable headings to every analytics block and set the Site Traffic block's displayed default heading to “Site Traffic.”
