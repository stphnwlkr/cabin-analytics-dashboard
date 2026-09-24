<?php
/**
 * Plugin Name:       Uplink Analytics Dashboard for Cabin
 * Plugin URI:        https://uplink.press/code/uplink-analytics-dashboard-for-cabin/
 * Description:       Display Cabin Analytics data with interactive charts via dashboard widgets, blocks, and shortcodes.
 * Version:           1.1.2
 * Requires at least: 6.9
 * Requires PHP:      8.3
 * Author:            UplinkPress
 * Author URI:        https://uplink.press
 * License:           GPL-2.0-or-later
 * License URI:       https://spdx.org/licenses/GPL-2.0-or-later.html
 * Text Domain:       uplink-analytics-dashboard-for-cabin
 *
 * @package Uplianda
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'UPLIANDA_VERSION', '1.1.2' );
define( 'UPLIANDA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'UPLIANDA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'UPLIANDA_CABIN_URL', 'https://withcabin.com/?utm_campaign=wp_widget&utm_medium=web&utm_source=sw' );

require_once UPLIANDA_PLUGIN_DIR . 'includes/admin-dashboard.php';

/**
 * Return editable private-dashboard style tokens.
 *
 * Component aliases such as --uplianda-widget-bg and --uplianda-primary continue to
 * reference these foundation tokens in admin-dashboard-document.css.
 *
 * @return array<string, array{css_var:string,default:string,type:string,group:string,label:string,description:string}> Style settings.
 */
function uplianda_get_style_settings() {
	return array(
		'dashboard_style_color_background' => array( 'css_var' => '--uplianda-dashboard-color-background', 'default' => '#ffffff', 'type' => 'color', 'group' => 'colors', 'label' => __( 'Page background', 'uplink-analytics-dashboard-for-cabin' ), 'description' => __( 'Canvas behind the dashboard blocks.', 'uplink-analytics-dashboard-for-cabin' ) ),
		'dashboard_style_color_surface'    => array( 'css_var' => '--uplianda-dashboard-color-surface', 'default' => '#ffffff', 'type' => 'color', 'group' => 'colors', 'label' => __( 'Surface', 'uplink-analytics-dashboard-for-cabin' ), 'description' => __( 'Background for Cabin report panels.', 'uplink-analytics-dashboard-for-cabin' ) ),
		'dashboard_style_color_surface_text' => array( 'css_var' => '--uplianda-dashboard-color-surface-text', 'default' => '#1e1e1e', 'type' => 'color', 'group' => 'colors', 'label' => __( 'Surface text', 'uplink-analytics-dashboard-for-cabin' ), 'description' => __( 'Primary text displayed on Cabin report surfaces.', 'uplink-analytics-dashboard-for-cabin' ) ),
		'dashboard_style_color_surface_muted' => array( 'css_var' => '--uplianda-dashboard-color-surface-muted', 'default' => '#50575e', 'type' => 'color', 'group' => 'colors', 'label' => __( 'Surface muted text', 'uplink-analytics-dashboard-for-cabin' ), 'description' => __( 'Labels and supporting text displayed on report surfaces.', 'uplink-analytics-dashboard-for-cabin' ) ),
		'dashboard_style_color_text'       => array( 'css_var' => '--uplianda-dashboard-color-text', 'default' => '#1e1e1e', 'type' => 'color', 'group' => 'colors', 'label' => __( 'Page text', 'uplink-analytics-dashboard-for-cabin' ), 'description' => __( 'Headings and content outside Cabin report surfaces.', 'uplink-analytics-dashboard-for-cabin' ) ),
		'dashboard_style_color_muted'      => array( 'css_var' => '--uplianda-dashboard-color-muted', 'default' => '#50575e', 'type' => 'color', 'group' => 'colors', 'label' => __( 'Page muted text', 'uplink-analytics-dashboard-for-cabin' ), 'description' => __( 'Descriptions and supporting content outside report surfaces.', 'uplink-analytics-dashboard-for-cabin' ) ),
		'dashboard_style_color_border'     => array( 'css_var' => '--uplianda-dashboard-color-border', 'default' => '#dcdcde', 'type' => 'color', 'group' => 'colors', 'label' => __( 'Border', 'uplink-analytics-dashboard-for-cabin' ), 'description' => __( 'Panel borders and chart grid lines.', 'uplink-analytics-dashboard-for-cabin' ) ),
		'dashboard_style_color_accent'     => array( 'css_var' => '--uplianda-dashboard-color-accent', 'default' => '#2271b1', 'type' => 'color', 'group' => 'colors', 'label' => __( 'Accent', 'uplink-analytics-dashboard-for-cabin' ), 'description' => __( 'Links, focus rings, and active controls.', 'uplink-analytics-dashboard-for-cabin' ) ),
		'dashboard_style_color_accent_hover' => array( 'css_var' => '--uplianda-dashboard-color-accent-hover', 'default' => '#135e96', 'type' => 'color', 'group' => 'colors', 'label' => __( 'Accent hover', 'uplink-analytics-dashboard-for-cabin' ), 'description' => __( 'Hover color for links and interactive controls.', 'uplink-analytics-dashboard-for-cabin' ) ),
		'dashboard_style_color_chart_line' => array( 'css_var' => '--uplianda-chart-line', 'default' => '#72aee6', 'type' => 'color', 'group' => 'colors', 'label' => __( 'Chart line', 'uplink-analytics-dashboard-for-cabin' ), 'description' => __( 'Page-view line and the lighter stacked-bar series.', 'uplink-analytics-dashboard-for-cabin' ) ),
		'dashboard_style_color_chart_bar'  => array( 'css_var' => '--uplianda-chart-bar', 'default' => '#2271b1', 'type' => 'color', 'group' => 'colors', 'label' => __( 'Chart bar', 'uplink-analytics-dashboard-for-cabin' ), 'description' => __( 'Visitor line and the darker stacked-bar series.', 'uplink-analytics-dashboard-for-cabin' ) ),
		'dashboard_style_color_progress'   => array( 'css_var' => '--uplianda-progress-color', 'default' => '#2271b1', 'type' => 'color', 'group' => 'colors', 'label' => __( 'Progress bars', 'uplink-analytics-dashboard-for-cabin' ), 'description' => __( 'Progress indicators in demographics and source reports.', 'uplink-analytics-dashboard-for-cabin' ) ),
		'dashboard_style_color_hover'      => array( 'css_var' => '--uplianda-bg-hover', 'default' => '#f0f0f1', 'type' => 'color', 'group' => 'colors', 'label' => __( 'Control hover', 'uplink-analytics-dashboard-for-cabin' ), 'description' => __( 'Background for inactive controls on hover.', 'uplink-analytics-dashboard-for-cabin' ) ),
		'dashboard_style_color_secondary'  => array( 'css_var' => '--uplianda-bg-secondary', 'default' => '#f6f7f7', 'type' => 'color', 'group' => 'colors', 'label' => __( 'Secondary surface', 'uplink-analytics-dashboard-for-cabin' ), 'description' => __( 'Background for statistics and supporting panels.', 'uplink-analytics-dashboard-for-cabin' ) ),
		'dashboard_style_color_active_text'=> array( 'css_var' => '--uplianda-button-active-text', 'default' => '#ffffff', 'type' => 'color', 'group' => 'colors', 'label' => __( 'Active control text', 'uplink-analytics-dashboard-for-cabin' ), 'description' => __( 'Text and icon color on active controls.', 'uplink-analytics-dashboard-for-cabin' ) ),
		'dashboard_style_font_family'      => array( 'css_var' => '--uplianda-dashboard-font-family', 'default' => '-apple-system, BlinkMacSystemFont, "Avenir Next", Avenir, "Segoe UI", "Helvetica Neue", "Adwaita Sans", Cantarell, Ubuntu, Roboto, Noto, Helvetica, Arial, sans-serif', 'type' => 'font', 'group' => 'typography', 'label' => __( 'Font stack', 'uplink-analytics-dashboard-for-cabin' ), 'description' => __( 'Comma-separated system and fallback fonts.', 'uplink-analytics-dashboard-for-cabin' ) ),
		'dashboard_style_font_size_small'  => array( 'css_var' => '--uplianda-dashboard-font-size-small', 'default' => '.875rem', 'type' => 'length', 'group' => 'typography', 'label' => __( 'Small text', 'uplink-analytics-dashboard-for-cabin' ), 'description' => __( 'CSS length used for compact supporting text.', 'uplink-analytics-dashboard-for-cabin' ) ),
		'dashboard_style_font_size_base'   => array( 'css_var' => '--uplianda-dashboard-font-size-base', 'default' => '1rem', 'type' => 'length', 'group' => 'typography', 'label' => __( 'Base text', 'uplink-analytics-dashboard-for-cabin' ), 'description' => __( 'Default dashboard text size.', 'uplink-analytics-dashboard-for-cabin' ) ),
		'dashboard_style_font_size_large'  => array( 'css_var' => '--uplianda-dashboard-font-size-large', 'default' => '1.5rem', 'type' => 'length', 'group' => 'typography', 'label' => __( 'Large text', 'uplink-analytics-dashboard-for-cabin' ), 'description' => __( 'Default section-heading size.', 'uplink-analytics-dashboard-for-cabin' ) ),
		'dashboard_style_font_size_x_large'=> array( 'css_var' => '--uplianda-dashboard-font-size-x-large', 'default' => 'clamp(1.75rem, 3vw, 2.25rem)', 'type' => 'length', 'group' => 'typography', 'label' => __( 'Extra-large text', 'uplink-analytics-dashboard-for-cabin' ), 'description' => __( 'Default top-level heading size; CSS clamp() is supported.', 'uplink-analytics-dashboard-for-cabin' ) ),
		'dashboard_style_line_height'      => array( 'css_var' => '--uplianda-dashboard-line-height', 'default' => '1.5', 'type' => 'line_height', 'group' => 'typography', 'label' => __( 'Line height', 'uplink-analytics-dashboard-for-cabin' ), 'description' => __( 'Unitless value from 1 through 3.', 'uplink-analytics-dashboard-for-cabin' ) ),
		'dashboard_style_space_1'          => array( 'css_var' => '--uplianda-dashboard-space-1', 'default' => '.25rem', 'type' => 'length', 'group' => 'spacing', 'label' => __( 'Space 1', 'uplink-analytics-dashboard-for-cabin' ), 'description' => __( 'Smallest reusable gap for compact custom layouts.', 'uplink-analytics-dashboard-for-cabin' ) ),
		'dashboard_style_space_2'          => array( 'css_var' => '--uplianda-dashboard-space-2', 'default' => '.5rem', 'type' => 'length', 'group' => 'spacing', 'label' => __( 'Space 2', 'uplink-analytics-dashboard-for-cabin' ), 'description' => __( 'Minimum padding around dashboard content on narrow screens.', 'uplink-analytics-dashboard-for-cabin' ) ),
		'dashboard_style_space_3'          => array( 'css_var' => '--uplianda-dashboard-space-3', 'default' => '1rem', 'type' => 'length', 'group' => 'spacing', 'label' => __( 'Space 3', 'uplink-analytics-dashboard-for-cabin' ), 'description' => __( 'Vertical space after headings, paragraphs, lists, figures, and tables.', 'uplink-analytics-dashboard-for-cabin' ) ),
		'dashboard_style_space_4'          => array( 'css_var' => '--uplianda-dashboard-space-4', 'default' => '1.5rem', 'type' => 'length', 'group' => 'spacing', 'label' => __( 'Space 4', 'uplink-analytics-dashboard-for-cabin' ), 'description' => __( 'Maximum padding around dashboard content and the default gap between Columns blocks.', 'uplink-analytics-dashboard-for-cabin' ) ),
		'dashboard_style_space_5'          => array( 'css_var' => '--uplianda-dashboard-space-5', 'default' => '2rem', 'type' => 'length', 'group' => 'spacing', 'label' => __( 'Space 5', 'uplink-analytics-dashboard-for-cabin' ), 'description' => __( 'Large reusable gap for custom dashboard layouts.', 'uplink-analytics-dashboard-for-cabin' ) ),
		'dashboard_style_space_6'          => array( 'css_var' => '--uplianda-dashboard-space-6', 'default' => '3rem', 'type' => 'length', 'group' => 'spacing', 'label' => __( 'Space 6', 'uplink-analytics-dashboard-for-cabin' ), 'description' => __( 'Largest reusable gap for custom dashboard sections.', 'uplink-analytics-dashboard-for-cabin' ) ),
	);
}

/** Return private-dashboard style defaults keyed by option name. */
function uplianda_get_style_defaults() {
	$defaults = array();
	foreach ( uplianda_get_style_settings() as $option_key => $setting ) {
		$defaults[ $option_key ] = $setting['default'];
	}
	return $defaults;
}

/**
 * Sanitize one private-dashboard style value.
 *
 * @param mixed $value   Submitted value.
 * @param array $setting Style definition.
 * @return string Sanitized value or the setting default.
 */
function uplianda_sanitize_style_value( $value, $setting ) {
	$value   = is_scalar( $value ) ? trim( (string) $value ) : '';
	$default = $setting['default'];

	if ( 'color' === $setting['type'] ) {
		$color = sanitize_hex_color( $value );
		return $color ? $color : $default;
	}

	if ( 'font' === $setting['type'] ) {
		$value = sanitize_text_field( $value );
		$value = preg_replace( '/[^a-zA-Z0-9,\- _"\']/', '', $value );
		return '' !== $value ? $value : $default;
	}

	if ( 'line_height' === $setting['type'] ) {
		$number = is_numeric( $value ) ? (float) $value : (float) $default;
		return (string) max( 1, min( 3, $number ) );
	}

	$number = '(?:0|(?:\d*\.?\d+)(?:px|rem|em|%|vw|vh|ch))';
	if ( preg_match( '/^' . $number . '$/i', $value ) ) {
		return $value;
	}

	if ( preg_match( '/^clamp\(\s*' . $number . '\s*,\s*' . $number . '\s*,\s*' . $number . '\s*\)$/i', $value ) ) {
		return $value;
	}

	return $default;
}

/** Return sanitized saved values for the private-dashboard style tokens. */
function uplianda_get_style_values() {
	$options = uplianda_get_options();
	$values  = array();

	foreach ( uplianda_get_style_settings() as $option_key => $setting ) {
		$values[ $option_key ] = uplianda_sanitize_style_value( $options[ $option_key ] ?? $setting['default'], $setting );
	}

	return $values;
}

/** Return the validated custom-property declarations for the dashboard document. */
function uplianda_get_style_css() {
	$settings     = uplianda_get_style_settings();
	$style_values = uplianda_get_style_values();
	$declarations = array();

	foreach ( $settings as $option_key => $setting ) {
		$declarations[] = $setting['css_var'] . ':' . $style_values[ $option_key ];
	}

	return 'body.uplianda-dashboard-document{' . implode( ';', $declarations ) . ';}';
}

/**
 * Return plugin options with optional wp-config.php overrides applied.
 *
 * Supported constants are UPLIANDA_API_KEY, UPLIANDA_DOMAIN,
 * UPLIANDA_DEFAULT_CHART_TYPE, UPLIANDA_DEFAULT_DATE_RANGE,
 * UPLIANDA_DASHBOARD_URL, UPLIANDA_POPULAR_CONTENT_TITLE, and
 * UPLIANDA_POPULAR_CONTENT_DATE_RANGE. Dashboard report modules may be
 * overridden with the UPLIANDA_DASHBOARD_* constants documented in
 * readme.txt.
 *
 * @return array<string, mixed> Plugin options.
 */
function uplianda_get_options() {
	$options = get_option( 'uplianda_options', array() );
	$options = is_array( $options ) ? $options : array();
	$options = wp_parse_args(
		$options,
		array_merge(
			array(
			'api_key'                    => '',
			'domain'                     => '',
			'chart_type'                 => 'bar',
			'date_range'                 => '7',
			'dashboard_url'               => '',
			'popular_content_title'       => __( 'Top Content', 'uplink-analytics-dashboard-for-cabin' ),
			'popular_content_date_range'  => '30',
			'dashboard_sustainability_total_co2'      => 1,
			'dashboard_sustainability_average_co2'    => 1,
			'dashboard_sustainability_transfer_volume'=> 1,
			'dashboard_sustainability_green_hosting'  => 1,
			'dashboard_demographics_devices'          => 1,
			'dashboard_demographics_countries'        => 1,
			'dashboard_demographics_languages'        => 1,
			'dashboard_demographics_browsers'         => 1,
				'dashboard_sources_traffic_sources'        => 1,
				'dashboard_sources_referrers'              => 1,
				'private_dashboard_enabled'                 => 1,
				'dashboard_access_roles'                    => array( 'administrator' ),
			),
			uplianda_get_style_defaults()
		)
	);

	$constant_map = array(
		'api_key'                    => 'UPLIANDA_API_KEY',
		'domain'                     => 'UPLIANDA_DOMAIN',
		'chart_type'                 => 'UPLIANDA_DEFAULT_CHART_TYPE',
		'date_range'                 => 'UPLIANDA_DEFAULT_DATE_RANGE',
		'dashboard_url'              => 'UPLIANDA_DASHBOARD_URL',
		'popular_content_title'      => 'UPLIANDA_POPULAR_CONTENT_TITLE',
		'popular_content_date_range' => 'UPLIANDA_POPULAR_CONTENT_DATE_RANGE',
		'private_dashboard_enabled'  => 'UPLIANDA_PRIVATE_DASHBOARD_ENABLED',
		'dashboard_sustainability_total_co2'       => 'UPLIANDA_DASHBOARD_SUSTAINABILITY_TOTAL_CO2',
		'dashboard_sustainability_average_co2'     => 'UPLIANDA_DASHBOARD_SUSTAINABILITY_AVERAGE_CO2',
		'dashboard_sustainability_transfer_volume' => 'UPLIANDA_DASHBOARD_SUSTAINABILITY_TRANSFER_VOLUME',
		'dashboard_sustainability_green_hosting'   => 'UPLIANDA_DASHBOARD_SUSTAINABILITY_GREEN_HOSTING',
		'dashboard_demographics_devices'           => 'UPLIANDA_DASHBOARD_DEMOGRAPHICS_DEVICES',
		'dashboard_demographics_countries'         => 'UPLIANDA_DASHBOARD_DEMOGRAPHICS_COUNTRIES',
		'dashboard_demographics_languages'         => 'UPLIANDA_DASHBOARD_DEMOGRAPHICS_LANGUAGES',
		'dashboard_demographics_browsers'          => 'UPLIANDA_DASHBOARD_DEMOGRAPHICS_BROWSERS',
		'dashboard_sources_traffic_sources'         => 'UPLIANDA_DASHBOARD_SOURCES_TRAFFIC_SOURCES',
		'dashboard_sources_referrers'               => 'UPLIANDA_DASHBOARD_SOURCES_REFERRERS',
	);

	foreach ( $constant_map as $option_key => $constant_name ) {
		if ( defined( $constant_name ) ) {
			$options[ $option_key ] = constant( $constant_name );
		}
	}

	return $options;
}

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 */
function uplianda_block_init() {
	$renderer_path = UPLIANDA_PLUGIN_DIR . 'assets/chart-renderer.js';
	wp_register_script(
		'uplianda-chart-renderer',
		UPLIANDA_PLUGIN_URL . 'assets/chart-renderer.js',
		array(),
		file_exists( $renderer_path ) ? filemtime( $renderer_path ) : UPLIANDA_VERSION,
		true
	);

	$country_map_script_path = UPLIANDA_PLUGIN_DIR . 'assets/country-map.js';
	wp_register_script(
		'uplianda-country-map',
		UPLIANDA_PLUGIN_URL . 'assets/country-map.js',
		array(),
		file_exists( $country_map_script_path ) ? filemtime( $country_map_script_path ) : UPLIANDA_VERSION,
		true
	);

	$chart_block = register_block_type( UPLIANDA_PLUGIN_DIR . 'assets/' );
	if ( $chart_block instanceof WP_Block_Type && ! empty( $chart_block->editor_script_handles ) ) {
		$options = uplianda_get_options();
		wp_localize_script(
			$chart_block->editor_script_handles[0],
			'UpliandaEditorDefaults',
			array(
				'domain'    => $options['domain'],
				'chartType' => $options['chart_type'],
				'dateRange' => $options['date_range'],
				'restUrl'   => rest_url( 'uplianda/v1/stats' ),
				'chartIconSvg' => function_exists( 'uplianda_chart_icon_markup' ) ? uplianda_chart_icon_markup( 24 ) : '',
			)
		);
	}

	$popular_content_asset_path = UPLIANDA_PLUGIN_DIR . 'assets/popular-content.asset.php';
	$popular_content_asset      = file_exists( $popular_content_asset_path ) ? include $popular_content_asset_path : array(
		'dependencies' => array( 'wp-blocks', 'wp-element', 'wp-i18n', 'wp-block-editor', 'wp-components', 'wp-server-side-render' ),
		'version'      => UPLIANDA_VERSION,
	);

	wp_register_script(
		'uplianda-popular-content-editor',
		UPLIANDA_PLUGIN_URL . 'assets/popular-content.js',
		$popular_content_asset['dependencies'],
		$popular_content_asset['version'],
		true
	);

	$popular_content_view_path = UPLIANDA_PLUGIN_DIR . 'assets/popular-content-view.js';
	wp_register_script(
		'uplianda-popular-content-view',
		UPLIANDA_PLUGIN_URL . 'assets/popular-content-view.js',
		array(),
		file_exists( $popular_content_view_path ) ? filemtime( $popular_content_view_path ) : UPLIANDA_VERSION,
		true
	);

	register_block_type(
		'uplianda/popular-content',
		array(
			'api_version'     => 3,
			'title'           => __( 'Uplink Popular Content for Cabin', 'uplink-analytics-dashboard-for-cabin' ),
			'category'        => 'widgets',
			'icon'            => 'editor-ol',
			'description'     => __( 'Display popular local content from Cabin Analytics.', 'uplink-analytics-dashboard-for-cabin' ),
			'editor_script'   => 'uplianda-popular-content-editor',
			'view_script'     => 'uplianda-popular-content-view',
			'style'           => 'uplianda-style',
			'editor_style'    => 'uplianda-style-editor',
			'render_callback' => 'uplianda_popular_content_block_render',
			'attributes'      => array(
				'title'     => array(
					'type'    => 'string',
					'default' => '',
				),
				'qty'       => array(
					'type'    => 'number',
					'default' => 10,
				),
				'dateRange' => array(
					'type'    => 'string',
					'default' => '',
				),
			),
			'supports'        => array(
				'html'       => false,
				'align'      => true,
				'color'      => array(
					'text'       => true,
					'background' => true,
					'gradients'  => true,
					'link'       => true,
				),
				'spacing'    => array(
					'margin'  => true,
					'padding' => true,
				),
				'typography' => array(
					'fontSize'   => true,
					'fontFamily' => true,
					'lineHeight' => true,
				),
				'border'     => array(
					'color'  => true,
					'radius' => true,
					'style'  => true,
					'width'  => true,
				),
			),
		)
	);

	$insight_blocks_asset_path = UPLIANDA_PLUGIN_DIR . 'assets/insight-blocks.asset.php';
	$insight_blocks_asset      = file_exists( $insight_blocks_asset_path ) ? include $insight_blocks_asset_path : array(
		'dependencies' => array( 'wp-blocks', 'wp-element', 'wp-i18n', 'wp-block-editor', 'wp-components', 'wp-server-side-render' ),
		'version'      => UPLIANDA_VERSION,
	);

	wp_register_script(
		'uplianda-insight-blocks-editor',
		UPLIANDA_PLUGIN_URL . 'assets/insight-blocks.js',
		$insight_blocks_asset['dependencies'],
		$insight_blocks_asset['version'],
		true
	);

	$insight_blocks = array(
		'sustainability' => array(
			'title'       => __( 'Uplink Sustainability for Cabin', 'uplink-analytics-dashboard-for-cabin' ),
			'default_title' => __( 'Sustainability', 'uplink-analytics-dashboard-for-cabin' ),
			'icon'        => 'cloud',
			'description' => __( 'Display Cabin CO₂, transfer volume, and green-hosting data.', 'uplink-analytics-dashboard-for-cabin' ),
			'attributes'  => array( 'showTotalCo2', 'showAverageCo2', 'showTransferVolume', 'showGreenHosting' ),
		),
		'demographics'   => array(
			'title'       => __( 'Uplink Demographics for Cabin', 'uplink-analytics-dashboard-for-cabin' ),
			'default_title' => __( 'Demographics', 'uplink-analytics-dashboard-for-cabin' ),
			'icon'        => 'groups',
			'description' => __( 'Display device, country, language, and browser data from Cabin.', 'uplink-analytics-dashboard-for-cabin' ),
			'attributes'  => array( 'showDevices', 'showCountries', 'showLanguages', 'showBrowsers' ),
		),
		'sources'        => array(
			'title'       => __( 'Uplink Sources for Cabin', 'uplink-analytics-dashboard-for-cabin' ),
			'default_title' => __( 'Sources', 'uplink-analytics-dashboard-for-cabin' ),
			'icon'        => 'networking',
			'description' => __( 'Display traffic sources and top referrers from Cabin.', 'uplink-analytics-dashboard-for-cabin' ),
			'attributes'  => array( 'showTrafficSources', 'showReferrers' ),
		),
		'country-map'    => array(
			'title'       => __( 'Uplink Country Traffic Map for Cabin', 'uplink-analytics-dashboard-for-cabin' ),
			'default_title' => __( 'Country Traffic', 'uplink-analytics-dashboard-for-cabin' ),
			'icon'        => 'location-alt',
			'description' => __( 'Display an accessible, privacy-friendly map of aggregated Cabin traffic by country.', 'uplink-analytics-dashboard-for-cabin' ),
			'attributes'  => array(),
		),
	);

	foreach ( $insight_blocks as $section => $block ) {
		register_block_type(
			'uplianda/' . $section,
			array(
				'api_version'     => 3,
				'title'           => $block['title'],
				'category'        => 'widgets',
				'icon'            => $block['icon'],
				'description'     => $block['description'],
				'editor_script'   => 'uplianda-insight-blocks-editor',
				'style'           => 'uplianda-insights',
				'editor_style'    => 'uplianda-insights-editor',
				'render_callback' => static function ( $attributes ) use ( $section ) {
					if ( 'country-map' === $section ) {
						return uplianda_render_country_map_block( $attributes );
					}

					return uplianda_render_insight_block( $section, $attributes );
				},
				'attributes'      => array_merge(
					array(
						'title'     => array(
							'type'    => 'string',
							'default' => $block['default_title'],
						),
						'dateRange' => array(
							'type'    => 'string',
							'default' => '30',
						),
					),
					array_fill_keys(
						$block['attributes'],
						array(
							'type'    => 'boolean',
							'default' => true,
						)
					)
				),
				'supports'        => array(
					'html'       => false,
					'align'      => true,
					'color'      => array(
						'text'       => true,
						'background' => true,
						'gradients'  => true,
						'link'       => true,
					),
					'spacing'    => array(
						'margin'  => true,
						'padding' => true,
					),
					'typography' => array(
						'fontSize'   => true,
						'fontFamily' => true,
						'lineHeight' => true,
					),
					'border'     => array(
						'color'  => true,
						'radius' => true,
						'style'  => true,
						'width'  => true,
					),
				),
			)
		);
	}
}
add_action( 'init', 'uplianda_block_init' );
/**
 * Register admin settings page
 */
function uplianda_add_admin_menu() {
	add_options_page(
		__( 'Uplink Analytics for Cabin Settings', 'uplink-analytics-dashboard-for-cabin' ),
		__( 'Uplink Analytics', 'uplink-analytics-dashboard-for-cabin' ),
		'manage_options',
		'uplink-analytics-dashboard-for-cabin',
		'uplianda_options_page'
	);
}
add_action( 'admin_menu', 'uplianda_add_admin_menu' );

/**
 * Enqueue admin styles
 */
function uplianda_admin_styles( $hook ) {
	if ( 'settings_page_uplink-analytics-dashboard-for-cabin' !== $hook && 'index.php' !== $hook ) {
		return;
	}
	
	$asset_file = include( UPLIANDA_PLUGIN_DIR . 'assets/index.asset.php' );
	
	wp_enqueue_style(
		'uplianda-admin',
		UPLIANDA_PLUGIN_URL . 'assets/admin.css',
		array(),
		$asset_file['version']
	);

	if ( 'index.php' === $hook ) {
		$view_asset_file = UPLIANDA_PLUGIN_DIR . 'assets/view.asset.php';
		$view_asset      = file_exists( $view_asset_file ) ? include $view_asset_file : array( 'version' => UPLIANDA_VERSION );

		wp_enqueue_style(
			'uplianda-style',
			UPLIANDA_PLUGIN_URL . 'assets/style-index.css',
			array(),
			isset( $view_asset['version'] ) ? $view_asset['version'] : UPLIANDA_VERSION
		);
	}
}
add_action( 'admin_enqueue_scripts', 'uplianda_admin_styles' );

/**
 * Register settings
 */
function uplianda_settings_init() {
	register_setting( 
		'uplianda', 
		'uplianda_options',
		array(
			'sanitize_callback' => 'uplianda_sanitize_options',
			'default' => array_merge(
				array(
				'api_key' => '',
				'domain' => '',
				'chart_type' => 'bar',
				'date_range' => '7',
				'dashboard_url' => '',
				'popular_content_title' => 'Top Content',
				'popular_content_date_range' => '30',
				'dashboard_sustainability_total_co2'       => 1,
				'dashboard_sustainability_average_co2'     => 1,
				'dashboard_sustainability_transfer_volume' => 1,
				'dashboard_sustainability_green_hosting'   => 1,
				'dashboard_demographics_devices'           => 1,
				'dashboard_demographics_countries'         => 1,
				'dashboard_demographics_languages'         => 1,
				'dashboard_demographics_browsers'          => 1,
				'dashboard_sources_traffic_sources'         => 1,
				'dashboard_sources_referrers'               => 1,
				'private_dashboard_enabled'                  => 1,
					'dashboard_access_roles'                     => array( 'administrator' ),
				),
				uplianda_get_style_defaults()
			),
		)
	);

	add_settings_section(
		'uplianda_connection_section',
		__( 'Connection', 'uplink-analytics-dashboard-for-cabin' ),
		'uplianda_connection_section_callback',
		'uplianda'
	);

	add_settings_section(
		'uplianda_defaults_section',
		__( 'Display Defaults', 'uplink-analytics-dashboard-for-cabin' ),
		'uplianda_defaults_section_callback',
		'uplianda'
	);

	add_settings_section(
		'uplianda_private_dashboard_section',
		__( 'Private Uplink Dashboard for Cabin', 'uplink-analytics-dashboard-for-cabin' ),
		'uplianda_private_section_callback',
		'uplianda'
	);

	add_settings_section(
		'uplianda_modules_section',
		__( 'Dashboard Report Modules', 'uplink-analytics-dashboard-for-cabin' ),
		'uplianda_modules_section_callback',
		'uplianda'
	);

	add_settings_field(
		'uplianda_api_key',
		__( 'API Key', 'uplink-analytics-dashboard-for-cabin' ),
		'uplianda_api_key_render',
		'uplianda',
		'uplianda_connection_section',
		array( 'label_for' => 'uplianda_api_key' )
	);

	add_settings_field(
		'uplianda_domain',
		__( 'Default Domain', 'uplink-analytics-dashboard-for-cabin' ),
		'uplianda_domain_render',
		'uplianda',
		'uplianda_connection_section',
		array( 'label_for' => 'uplianda_domain' )
	);

	add_settings_field(
		'uplianda_chart_type',
		__( 'Default Chart Type', 'uplink-analytics-dashboard-for-cabin' ),
		'uplianda_chart_type_render',
		'uplianda',
		'uplianda_defaults_section',
		array( 'label_for' => 'uplianda_chart_type' )
	);

	add_settings_field(
		'uplianda_date_range',
		__( 'Default Date Range', 'uplink-analytics-dashboard-for-cabin' ),
		'uplianda_date_range_render',
		'uplianda',
		'uplianda_defaults_section',
		array( 'label_for' => 'uplianda_date_range' )
	);

	add_settings_field(
		'uplianda_url',
		__( 'Default Dashboard URL', 'uplink-analytics-dashboard-for-cabin' ),
		'uplianda_url_render',
		'uplianda',
		'uplianda_defaults_section',
		array( 'label_for' => 'uplianda_url' )
	);

	add_settings_field(
		'uplianda_popular_content_title',
		__( 'Popular Content Title', 'uplink-analytics-dashboard-for-cabin' ),
		'uplianda_popular_content_title_render',
		'uplianda',
		'uplianda_defaults_section',
		array( 'label_for' => 'uplianda_popular_content_title' )
	);

	add_settings_field(
		'uplianda_popular_content_date_range',
		__( 'Popular Content Default Date Range', 'uplink-analytics-dashboard-for-cabin' ),
		'uplianda_popular_content_date_range_render',
		'uplianda',
		'uplianda_defaults_section',
		array( 'label_for' => 'uplianda_popular_content_date_range' )
	);

	add_settings_field(
		'uplianda_private_dashboard_enabled',
		__( 'Enable Private Dashboard', 'uplink-analytics-dashboard-for-cabin' ),
		'uplianda_enabled_render',
		'uplianda',
		'uplianda_private_dashboard_section'
	);

	add_settings_field(
		'uplianda_access_roles',
		__( 'Dashboard Access', 'uplink-analytics-dashboard-for-cabin' ),
		'uplianda_access_roles_render',
		'uplianda',
		'uplianda_private_dashboard_section'
	);

	add_settings_field(
		'uplianda_modules',
		__( 'Visible Modules', 'uplink-analytics-dashboard-for-cabin' ),
		'uplianda_modules_render',
		'uplianda',
		'uplianda_modules_section'
	);
}
add_action( 'admin_init', 'uplianda_settings_init' );

/**
 * Sanitize options callback
 */
function uplianda_sanitize_options( $input ) {
	$sanitized = array();
	
	if ( isset( $input['api_key'] ) ) {
		$sanitized['api_key'] = sanitize_text_field( $input['api_key'] );
	}
	
	if ( isset( $input['domain'] ) ) {
		$sanitized['domain'] = sanitize_text_field( trim( str_replace( array( 'http://', 'https://', 'www.' ), '', $input['domain'] ), '/' ) );
	}
	
	if ( isset( $input['chart_type'] ) && in_array( $input['chart_type'], array( 'bar', 'line' ), true ) ) {
		$sanitized['chart_type'] = $input['chart_type'];
	}
	
	if ( isset( $input['date_range'] ) && in_array( $input['date_range'], array( '7', '14', '30', '90' ), true ) ) {
		$sanitized['date_range'] = $input['date_range'];
	}
	
	if ( isset( $input['dashboard_url'] ) ) {
		$sanitized['dashboard_url'] = esc_url_raw( $input['dashboard_url'] );
	}

	if ( isset( $input['popular_content_title'] ) ) {
		$sanitized['popular_content_title'] = sanitize_text_field( $input['popular_content_title'] );
	}

	if ( isset( $input['popular_content_date_range'] ) && in_array( $input['popular_content_date_range'], array( '1', '7', '14', '30', '90' ), true ) ) {
		$sanitized['popular_content_date_range'] = $input['popular_content_date_range'];
	}

	foreach ( uplianda_get_dashboard_module_options() as $option_key => $module ) {
		$sanitized[ $option_key ] = isset( $input[ $option_key ] ) ? 1 : 0;
	}

	$sanitized['private_dashboard_enabled'] = isset( $input['private_dashboard_enabled'] ) ? 1 : 0;

	$available_roles = array_keys( wp_roles()->roles );
	$selected_roles  = isset( $input['dashboard_access_roles'] ) && is_array( $input['dashboard_access_roles'] ) ? array_map( 'sanitize_key', $input['dashboard_access_roles'] ) : array();
	$selected_roles  = array_values( array_intersect( $selected_roles, $available_roles ) );

	if ( ! in_array( 'administrator', $selected_roles, true ) ) {
		$selected_roles[] = 'administrator';
	}

	$sanitized['dashboard_access_roles'] = array_values( array_unique( $selected_roles ) );

	$current_options = get_option( 'uplianda_options', array() );
	$current_options = is_array( $current_options ) ? $current_options : array();
	foreach ( uplianda_get_style_settings() as $option_key => $setting ) {
		$value = $input[ $option_key ] ?? ( $current_options[ $option_key ] ?? $setting['default'] );
		$sanitized[ $option_key ] = uplianda_sanitize_style_value( $value, $setting );
	}
	
	return $sanitized;
}

/**
 * Render API key field
 */
function uplianda_api_key_render() {
	$options = uplianda_get_options();
	$api_key = isset( $options['api_key'] ) ? $options['api_key'] : '';
	?>
	<div class="uplianda-field-wrapper">
		<input id="uplianda_api_key" type="password" name="uplianda_options[api_key]" value="<?php echo esc_attr( $api_key ); ?>" class="uplianda-input" placeholder="sk_live_..." autocomplete="off" />
		<p class="uplianda-description">
			<svg class="uplianda-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
				<circle cx="12" cy="12" r="10"/>
				<path d="M12 16v-4M12 8h.01"/>
			</svg>
			<?php esc_html_e( 'Enter your Cabin Analytics API key from withcabin.com', 'uplink-analytics-dashboard-for-cabin' ); ?>
		</p>
	</div>
	<?php
}

/**
 * Render domain field
 */
function uplianda_domain_render() {
	$options = uplianda_get_options();
	$domain = isset( $options['domain'] ) ? $options['domain'] : '';
	?>
	<div class="uplianda-field-wrapper">
		<input id="uplianda_domain" type="text" name="uplianda_options[domain]" value="<?php echo esc_attr( $domain ); ?>" class="uplianda-input" placeholder="example.com" />
		<p class="uplianda-description">
			<svg class="uplianda-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
				<circle cx="12" cy="12" r="10"/>
				<path d="M12 16v-4M12 8h.01"/>
			</svg>
			<?php esc_html_e( 'Your default domain to track (without https://)', 'uplink-analytics-dashboard-for-cabin' ); ?>
		</p>
	</div>
	<?php
}

/**
 * Render chart type field
 */
function uplianda_chart_type_render() {
	$options = uplianda_get_options();
	$chart_type = isset( $options['chart_type'] ) ? $options['chart_type'] : 'bar';
	?>
	<div class="uplianda-field-wrapper">
		<select id="uplianda_chart_type" name="uplianda_options[chart_type]" class="uplianda-select">
			<option value="bar" <?php selected( $chart_type, 'bar' ); ?>><?php esc_html_e( 'Stacked Bar Chart', 'uplink-analytics-dashboard-for-cabin' ); ?></option>
			<option value="line" <?php selected( $chart_type, 'line' ); ?>><?php esc_html_e( 'Line Chart', 'uplink-analytics-dashboard-for-cabin' ); ?></option>
		</select>
	</div>
	<?php
}

/**
 * Render date range field
 */
function uplianda_date_range_render() {
	$options = uplianda_get_options();
	$date_range = isset( $options['date_range'] ) ? $options['date_range'] : '7';
	?>
	<div class="uplianda-field-wrapper">
		<select id="uplianda_date_range" name="uplianda_options[date_range]" class="uplianda-select">
			<option value="7" <?php selected( $date_range, '7' ); ?>><?php esc_html_e( 'Last 7 Days', 'uplink-analytics-dashboard-for-cabin' ); ?></option>
			<option value="14" <?php selected( $date_range, '14' ); ?>><?php esc_html_e( 'Last 14 Days', 'uplink-analytics-dashboard-for-cabin' ); ?></option>
			<option value="30" <?php selected( $date_range, '30' ); ?>><?php esc_html_e( 'Last 30 Days', 'uplink-analytics-dashboard-for-cabin' ); ?></option>
			<option value="90" <?php selected( $date_range, '90' ); ?>><?php esc_html_e( 'Last 90 Days', 'uplink-analytics-dashboard-for-cabin' ); ?></option>
		</select>
	</div>
	<?php
}

/**
 * Render dashboard URL field
 */
function uplianda_url_render() {
	$options = uplianda_get_options();
	$dashboard_url = isset( $options['dashboard_url'] ) ? $options['dashboard_url'] : '';
	?>
	<div class="uplianda-field-wrapper">
		<input id="uplianda_url" type="url" name="uplianda_options[dashboard_url]" value="<?php echo esc_attr( $dashboard_url ); ?>" class="uplianda-input" placeholder="<?php echo esc_attr( UPLIANDA_CABIN_URL ); ?>" />
		<p class="uplianda-description">
			<svg class="uplianda-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
				<circle cx="12" cy="12" r="10"/>
				<path d="M12 16v-4M12 8h.01"/>
			</svg>
			<?php esc_html_e( 'Custom URL for the Cabin link. Leave empty to use the tracked Cabin website link.', 'uplink-analytics-dashboard-for-cabin' ); ?>
		</p>
	</div>
	<?php
}

/**
 * Section callback
 */

/**
 * Render popular content default date range field.
 */
function uplianda_popular_content_date_range_render() {
	$options    = uplianda_get_options();
	$date_range = isset( $options['popular_content_date_range'] ) ? $options['popular_content_date_range'] : '30';

	?>
	<div class="uplianda-field-wrapper">
		<select id="uplianda_popular_content_date_range" name="uplianda_options[popular_content_date_range]" class="uplianda-select">
			<option value="1" <?php selected( $date_range, '1' ); ?>><?php esc_html_e( 'Last 1 Day', 'uplink-analytics-dashboard-for-cabin' ); ?></option>
			<option value="7" <?php selected( $date_range, '7' ); ?>><?php esc_html_e( 'Last 7 Days', 'uplink-analytics-dashboard-for-cabin' ); ?></option>
			<option value="14" <?php selected( $date_range, '14' ); ?>><?php esc_html_e( 'Last 14 Days', 'uplink-analytics-dashboard-for-cabin' ); ?></option>
			<option value="30" <?php selected( $date_range, '30' ); ?>><?php esc_html_e( 'Last 30 Days', 'uplink-analytics-dashboard-for-cabin' ); ?></option>
			<option value="90" <?php selected( $date_range, '90' ); ?>><?php esc_html_e( 'Last 90 Days', 'uplink-analytics-dashboard-for-cabin' ); ?></option>
		</select>
		<p class="uplianda-description">
			<svg class="uplianda-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
				<circle cx="12" cy="12" r="10"/>
				<path d="M12 16v-4M12 8h.01"/>
			</svg>
			<?php esc_html_e( 'Default range used by [uplianda_popular_content] and the Popular Content block when no range is provided.', 'uplink-analytics-dashboard-for-cabin' ); ?>
		</p>
	</div>
	<?php
}

/** Render the Popular Content default title field. */
function uplianda_popular_content_title_render() {
	$options = uplianda_get_options();
	$title   = isset( $options['popular_content_title'] ) ? $options['popular_content_title'] : __( 'Top Content', 'uplink-analytics-dashboard-for-cabin' );
	?>
	<div class="uplianda-field-wrapper">
		<input id="uplianda_popular_content_title" type="text" name="uplianda_options[popular_content_title]" value="<?php echo esc_attr( $title ); ?>" class="uplianda-input" />
		<p class="uplianda-description"><?php esc_html_e( 'Used when the Popular Content block or shortcode does not provide its own title.', 'uplink-analytics-dashboard-for-cabin' ); ?></p>
	</div>
	<?php
}

/**
 * Return dashboard module option definitions.
 *
 * @return array<string, array{section:string,label:string}> Module settings.
 */
function uplianda_get_dashboard_module_options() {
	return array(
		'dashboard_sustainability_total_co2'       => array( 'section' => 'sustainability', 'label' => __( 'Total CO₂', 'uplink-analytics-dashboard-for-cabin' ) ),
		'dashboard_sustainability_average_co2'     => array( 'section' => 'sustainability', 'label' => __( 'Average CO₂ per page', 'uplink-analytics-dashboard-for-cabin' ) ),
		'dashboard_sustainability_transfer_volume' => array( 'section' => 'sustainability', 'label' => __( 'Transfer volume', 'uplink-analytics-dashboard-for-cabin' ) ),
		'dashboard_sustainability_green_hosting'   => array( 'section' => 'sustainability', 'label' => __( 'Green-hosting status', 'uplink-analytics-dashboard-for-cabin' ) ),
		'dashboard_demographics_devices'           => array( 'section' => 'demographics', 'label' => __( 'Device mix', 'uplink-analytics-dashboard-for-cabin' ) ),
		'dashboard_demographics_countries'         => array( 'section' => 'demographics', 'label' => __( 'Countries', 'uplink-analytics-dashboard-for-cabin' ) ),
		'dashboard_demographics_languages'         => array( 'section' => 'demographics', 'label' => __( 'Languages', 'uplink-analytics-dashboard-for-cabin' ) ),
		'dashboard_demographics_browsers'          => array( 'section' => 'demographics', 'label' => __( 'Browsers', 'uplink-analytics-dashboard-for-cabin' ) ),
		'dashboard_sources_traffic_sources'         => array( 'section' => 'sources', 'label' => __( 'Traffic sources', 'uplink-analytics-dashboard-for-cabin' ) ),
		'dashboard_sources_referrers'               => array( 'section' => 'sources', 'label' => __( 'Top referrers', 'uplink-analytics-dashboard-for-cabin' ) ),
	);
}

/** Render dashboard report module checkboxes. */
function uplianda_modules_render() {
	$options        = uplianda_get_options();
	$section_titles = array(
		'sustainability' => __( 'Sustainability', 'uplink-analytics-dashboard-for-cabin' ),
		'demographics'   => __( 'Demographics', 'uplink-analytics-dashboard-for-cabin' ),
		'sources'        => __( 'Sources', 'uplink-analytics-dashboard-for-cabin' ),
	);
	$modules_by_section = array_fill_keys( array_keys( $section_titles ), array() );

	foreach ( uplianda_get_dashboard_module_options() as $option_key => $module ) {
		$modules_by_section[ $module['section'] ][ $option_key ] = $module['label'];
	}
	?>
	<div class="uplianda-module-groups">
		<?php foreach ( $section_titles as $section => $section_title ) : ?>
			<fieldset class="uplianda-module-group">
				<legend><?php echo esc_html( $section_title ); ?></legend>
				<?php foreach ( $modules_by_section[ $section ] as $option_key => $label ) : ?>
					<label>
						<input type="checkbox" name="uplianda_options[<?php echo esc_attr( $option_key ); ?>]" value="1" <?php checked( ! empty( $options[ $option_key ] ) ); ?> />
						<?php echo esc_html( $label ); ?>
					</label>
				<?php endforeach; ?>
			</fieldset>
		<?php endforeach; ?>
	</div>
	<p class="uplianda-description"><?php esc_html_e( 'These choices affect WordPress dashboard widgets only. Each block and shortcode can choose its own modules.', 'uplink-analytics-dashboard-for-cabin' ); ?></p>
	<?php
}

/** Connection section description. */
function uplianda_connection_section_callback() {
	echo '<p class="uplianda-section-description">' . esc_html__( 'Connect the Cabin PRO API and choose the site whose aggregated analytics should be displayed.', 'uplink-analytics-dashboard-for-cabin' ) . '</p>';
}

/** Display-defaults section description. */
function uplianda_defaults_section_callback() {
	echo '<p class="uplianda-section-description">' . esc_html__( 'Set the initial chart, reporting period, links, and Popular Content presentation.', 'uplink-analytics-dashboard-for-cabin' ) . '</p>';
}

/** Dashboard-modules section description. */
function uplianda_modules_section_callback() {
	echo '<p class="uplianda-section-description">' . esc_html__( 'Choose which modules appear inside the three Cabin report widgets on the WordPress dashboard.', 'uplink-analytics-dashboard-for-cabin' ) . '</p>';
}

/**
 * Options page
 */
function uplianda_options_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	
	settings_errors( 'uplianda_messages' );
	?>
	<div class="uplianda-admin-wrap">
		<div class="uplianda-admin-header">
			<div class="uplianda-header-content">
				<div class="uplianda-brand-lockup" aria-hidden="true">
					<img class="uplianda-brand-lockup__uplink" src="<?php echo esc_url( UPLIANDA_PLUGIN_URL . 'assets/branding/uplink-mark.svg' ); ?>" alt="" width="50" height="40" />
					<span></span>
					<strong class="uplianda-brand-lockup__name"><?php esc_html_e( 'Analytics Dashboard for Cabin', 'uplink-analytics-dashboard-for-cabin' ); ?></strong>
				</div>
				<h1><?php esc_html_e( 'Analytics Dashboard Settings', 'uplink-analytics-dashboard-for-cabin' ); ?></h1>
			</div>
			<p class="uplianda-tagline"><?php esc_html_e( 'Configure charts, popular content, sustainability, demographics, and acquisition reports.', 'uplink-analytics-dashboard-for-cabin' ); ?></p>
			<p class="uplianda-tagline"><?php esc_html_e( 'An independent UplinkPress integration for Cabin. Not affiliated with or endorsed by Cabin.', 'uplink-analytics-dashboard-for-cabin' ); ?></p>
		</div>

		<div class="uplianda-admin-content">
			<div class="uplianda-admin-main">
				<div class="uplianda-capability-grid" role="group" aria-label="<?php esc_attr_e( 'Available Cabin reports', 'uplink-analytics-dashboard-for-cabin' ); ?>">
					<div><strong><?php esc_html_e( 'Analytics', 'uplink-analytics-dashboard-for-cabin' ); ?></strong><span><?php esc_html_e( 'Traffic charts and trends', 'uplink-analytics-dashboard-for-cabin' ); ?></span></div>
					<div><strong><?php esc_html_e( 'Popular Content', 'uplink-analytics-dashboard-for-cabin' ); ?></strong><span><?php esc_html_e( 'Views, visitors, and attention', 'uplink-analytics-dashboard-for-cabin' ); ?></span></div>
					<div><strong><?php esc_html_e( 'Site Reports', 'uplink-analytics-dashboard-for-cabin' ); ?></strong><span><?php esc_html_e( 'Sustainability, demographics, and sources', 'uplink-analytics-dashboard-for-cabin' ); ?></span></div>
				</div>
				<form action="options.php" method="post" class="uplianda-form">
					<?php
					settings_fields( 'uplianda' );
					do_settings_sections( 'uplianda' );
					?>
					<div class="uplianda-form-actions">
						<?php submit_button( __( 'Save Settings', 'uplink-analytics-dashboard-for-cabin' ), 'primary', 'submit', false ); ?>
					</div>
				</form>
			</div>

			<div class="uplianda-admin-sidebar">
				<div class="uplianda-card">
					<h3 class="uplianda-card-title">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
							<path d="M12 2L2 7l10 5 10-5-10-5z"/>
							<path d="M2 17l10 5 10-5M2 12l10 5 10-5"/>
						</svg>
						<?php esc_html_e( 'Usage', 'uplink-analytics-dashboard-for-cabin' ); ?>
					</h3>
					<div class="uplianda-card-content">
						<h4><?php esc_html_e( 'Private Uplink Dashboard for Cabin', 'uplink-analytics-dashboard-for-cabin' ); ?></h4>
						<p><?php esc_html_e( 'Open Uplink Dashboard for Cabin → Edit Dashboard to design a private admin page with core WordPress blocks and the active site styles. Repeat any Cabin report block and choose different modules for each instance.', 'uplink-analytics-dashboard-for-cabin' ); ?></p>

						<h4><?php esc_html_e( 'Dashboard Widgets', 'uplink-analytics-dashboard-for-cabin' ); ?></h4>
						<p><?php esc_html_e( 'Analytics, Popular Content, Sustainability, Demographics, and Sources appear after configuration. Use the module settings on this page and Dashboard Screen Options to tailor the view.', 'uplink-analytics-dashboard-for-cabin' ); ?></p>
						
						<h4><?php esc_html_e( 'Block Editor', 'uplink-analytics-dashboard-for-cabin' ); ?></h4>
						<p><?php esc_html_e( 'Search for “Cabin” to find six blocks. Report blocks include module toggles in the block sidebar, and the Country Traffic Map has its own reporting period.', 'uplink-analytics-dashboard-for-cabin' ); ?></p>
						
						<h4><?php esc_html_e( 'Charts', 'uplink-analytics-dashboard-for-cabin' ); ?></h4>
						<code class="uplianda-code">[uplianda]</code>

						<h4><?php esc_html_e( 'Popular Content', 'uplink-analytics-dashboard-for-cabin' ); ?></h4>
						<code class="uplianda-code">[uplianda_popular_content]</code>

						<h4><?php esc_html_e( 'Site Reports', 'uplink-analytics-dashboard-for-cabin' ); ?></h4>
						<code class="uplianda-code">[uplianda_sustainability]</code>
						<code class="uplianda-code">[uplianda_demographics]</code>
						<code class="uplianda-code">[uplianda_sources]</code>
						<code class="uplianda-code">[uplianda_country_map date_range="30"]</code>
						<p><?php esc_html_e( 'Set a module attribute to false to hide it. Example:', 'uplink-analytics-dashboard-for-cabin' ); ?></p>
						<code class="uplianda-code">[uplianda_demographics languages="false" browsers="false"]</code>
						<details class="uplianda-shortcode-reference">
							<summary><?php esc_html_e( 'View shortcode attributes', 'uplink-analytics-dashboard-for-cabin' ); ?></summary>
							<p><strong><?php esc_html_e( 'Sustainability:', 'uplink-analytics-dashboard-for-cabin' ); ?></strong> <code>total_co2</code>, <code>average_co2</code>, <code>transfer_volume</code>, <code>green_hosting</code></p>
							<p><strong><?php esc_html_e( 'Demographics:', 'uplink-analytics-dashboard-for-cabin' ); ?></strong> <code>devices</code>, <code>countries</code>, <code>languages</code>, <code>browsers</code></p>
							<p><strong><?php esc_html_e( 'Sources:', 'uplink-analytics-dashboard-for-cabin' ); ?></strong> <code>traffic_sources</code>, <code>referrers</code></p>
							<p><strong><?php esc_html_e( 'All reports:', 'uplink-analytics-dashboard-for-cabin' ); ?></strong> <code>date_range="7|14|30|90"</code></p>
						</details>
					</div>
				</div>

				<div class="uplianda-card">
					<h3 class="uplianda-card-title">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
							<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
							<polyline points="15 3 21 3 21 9"/>
							<line x1="10" y1="14" x2="21" y2="3"/>
						</svg>
						<?php esc_html_e( 'Resources', 'uplink-analytics-dashboard-for-cabin' ); ?>
					</h3>
					<div class="uplianda-card-content">
						<ul class="uplianda-links">
							<li><a href="<?php echo esc_url( UPLIANDA_CABIN_URL ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'Cabin Analytics Website (opens in a new tab)', 'uplink-analytics-dashboard-for-cabin' ); ?>"><?php esc_html_e( 'Cabin Analytics Website', 'uplink-analytics-dashboard-for-cabin' ); ?></a></li>
							<li><a href="https://uplink.press/code/uplink-analytics-dashboard-for-cabin/" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'Plugin documentation (opens in a new tab)', 'uplink-analytics-dashboard-for-cabin' ); ?>"><?php esc_html_e( 'Plugin Documentation', 'uplink-analytics-dashboard-for-cabin' ); ?></a></li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Register REST API endpoint for fetching analytics data
 */
function uplianda_register_rest_routes() {
	register_rest_route( 'uplianda/v1', '/stats', array(
		'methods' => 'GET',
		'callback' => 'uplianda_get_stats',
		'permission_callback' => '__return_true',
		'args' => array(
			'domain' => array(
				'required' => false,
				'type' => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'start_date' => array(
				'required' => true,
				'type' => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'uplianda_validate_date',
			),
			'end_date' => array(
				'required' => true,
				'type' => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'uplianda_validate_date',
			),
		),
	) );

	register_rest_route( 'uplianda/v1', '/popular-content', array(
		'methods'             => 'GET',
		'callback'            => 'uplianda_get_popular_content_rest',
		'permission_callback' => '__return_true',
		'args'                => array(
			'qty'        => array(
				'required'          => false,
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
			),
			'date_range' => array(
				'required'          => false,
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
			),
		),
	) );

	register_rest_route( 'uplianda/v1', '/insights', array(
		'methods'             => 'GET',
		'callback'            => 'uplianda_get_insights_rest',
		'permission_callback' => function() {
			return current_user_can( 'edit_posts' );
		},
		'args'                => array(
			'date_range' => array(
				'required'          => false,
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
			),
			'section'    => array(
				'required'          => true,
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_key',
				'validate_callback' => static function ( $value ) {
					return in_array( $value, uplianda_get_insight_sections(), true );
				},
			),
		),
	) );
}
add_action( 'rest_api_init', 'uplianda_register_rest_routes' );

/**
 * Return the allowed Popular Content date ranges.
 *
 * @return int[] Allowed date ranges in days.
 */
function uplianda_get_allowed_popular_ranges() {
	return array( 1, 7, 14, 30, 90 );
}

/**
 * REST callback for the standalone dashboard Popular Content widget.
 *
 * This keeps the WordPress Dashboard from blocking while Cabin data is fetched.
 *
 * @param WP_REST_Request $request REST request.
 * @return WP_REST_Response
 */
function uplianda_get_popular_content_rest( $request ) {
	$options    = uplianda_get_options();
	$qty        = absint( $request->get_param( 'qty' ) );
	$date_range = absint( $request->get_param( 'date_range' ) );

	if ( ! in_array( $qty, uplianda_get_allowed_popular_quantities(), true ) ) {
		$qty = 10;
	}

	if ( ! in_array( $date_range, uplianda_get_allowed_popular_ranges(), true ) ) {
		$date_range = isset( $options['popular_content_date_range'] ) ? absint( $options['popular_content_date_range'] ) : 30;
	}

	if ( ! in_array( $date_range, uplianda_get_allowed_popular_ranges(), true ) ) {
		$date_range = 30;
	}

	$items = uplianda_get_popular_content( $qty, $date_range );

	if ( is_wp_error( $items ) ) {
		return rest_ensure_response(
			array(
				'success' => false,
				'html'    => '<p class="uplianda-popular-content__message">' . esc_html( $items->get_error_message() ) . '</p>',
			)
		);
	}

	return rest_ensure_response(
		array(
			'success' => true,
			'html'    => uplianda_render_popular_content_dashboard_items( $items ),
		)
	);
}

/**
 * REST callback for the manager-only Site Insights dashboard widget.
 *
 * @param WP_REST_Request $request REST request.
 * @return WP_REST_Response
 */
function uplianda_get_insights_rest( $request ) {
	$date_range = absint( $request->get_param( 'date_range' ) );
	$section    = sanitize_key( $request->get_param( 'section' ) );

	if ( ! in_array( $date_range, uplianda_get_allowed_chart_ranges(), true ) ) {
		$options    = uplianda_get_options();
		$date_range = isset( $options['date_range'] ) ? absint( $options['date_range'] ) : 30;
	}

	if ( ! in_array( $date_range, uplianda_get_allowed_chart_ranges(), true ) ) {
		$date_range = 30;
	}

	$insights = uplianda_get_site_insights( $date_range );

	if ( is_wp_error( $insights ) ) {
		return rest_ensure_response(
			array(
				'success' => false,
				'message' => $insights->get_error_message(),
			)
		);
	}

	return rest_ensure_response(
		array(
			'success' => true,
			'html'    => uplianda_render_insight_section( $section, $insights, $date_range, uplianda_get_dashboard_modules( $section ) ),
		)
	);
}

/**
 * Return the insight sections exposed by dashboard widgets, blocks, and shortcodes.
 *
 * @return string[] Insight section slugs.
 */
function uplianda_get_insight_sections() {
	return array( 'sustainability', 'demographics', 'sources' );
}

/**
 * Return enabled modules for a dashboard insight section.
 *
 * @param string $section Insight section slug.
 * @return array<string, bool> Module visibility map.
 */
function uplianda_get_dashboard_modules( $section ) {
	$options = uplianda_get_options();
	$modules = array();
	$prefix  = 'dashboard_' . $section . '_';

	foreach ( uplianda_get_dashboard_module_options() as $option_key => $module ) {
		if ( $section === $module['section'] ) {
			$module_key             = substr( $option_key, strlen( $prefix ) );
			$modules[ $module_key ] = ! empty( $options[ $option_key ] );
		}
	}

	return $modules;
}

/**
 * Fetch audience, acquisition, and sustainability insights.
 *
 * @param int $date_range Number of days to retrieve.
 * @return array|WP_Error Cabin response data or an error.
 */
function uplianda_get_site_insights( $date_range = 30 ) {
	$options = uplianda_get_options();
	$api_key = isset( $options['api_key'] ) ? trim( (string) $options['api_key'] ) : '';
	$domain  = isset( $options['domain'] ) ? uplianda_normalize_domain( $options['domain'] ) : '';

	if ( '' === $api_key ) {
		return new WP_Error( 'uplianda_no_api_key', __( 'Cabin Analytics API key not configured.', 'uplink-analytics-dashboard-for-cabin' ) );
	}

	if ( '' === $domain ) {
		return new WP_Error( 'uplianda_no_domain', __( 'Cabin Analytics domain not configured.', 'uplink-analytics-dashboard-for-cabin' ) );
	}

	$date_range = absint( $date_range );
	if ( ! in_array( $date_range, uplianda_get_allowed_chart_ranges(), true ) ) {
		$date_range = 30;
	}

	$today     = new DateTimeImmutable( 'today', wp_timezone() );
	$date_to   = $today->format( 'Y-m-d' );
	$date_from = $today->modify( '-' . ( $date_range - 1 ) . ' days' )->format( 'Y-m-d' );
	$cache_key = 'uplianda_site_insights_' . md5( 'v1|' . $domain . '|' . $date_from . '|' . $date_to );
	$cached    = get_transient( $cache_key );

	if ( false !== $cached ) {
		return $cached;
	}

	$url = add_query_arg(
		array(
			'domain'      => $domain,
			'date_from'   => $date_from,
			'date_to'     => $date_to,
			'scope'       => 'core,pages,referrals',
			'limit_lists' => 10,
		),
		'https://api.withcabin.com/v1/analytics'
	);

	$response = wp_remote_get(
		$url,
		array(
			'headers'    => array(
				'x-api-key' => $api_key,
			),
			'timeout'    => 15,
			'user-agent' => 'WordPress Uplink Analytics Dashboard for Cabin/' . UPLIANDA_VERSION,
		)
	);

	if ( is_wp_error( $response ) ) {
		return $response;
	}

	$response_code = wp_remote_retrieve_response_code( $response );
	if ( 200 !== $response_code ) {
		return new WP_Error(
			'uplianda_api_error',
			sprintf(
				/* translators: %d: HTTP status code. */
				__( 'Cabin Analytics API returned HTTP %d.', 'uplink-analytics-dashboard-for-cabin' ),
				$response_code
			)
		);
	}

	$data = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( JSON_ERROR_NONE !== json_last_error() || ! is_array( $data ) ) {
		return new WP_Error( 'uplianda_json_error', __( 'Cabin Analytics returned an invalid response.', 'uplink-analytics-dashboard-for-cabin' ) );
	}

	set_transient( $cache_key, $data, 15 * MINUTE_IN_SECONDS );

	return $data;
}

/**
 * Fetch the complete aggregated country breakdown for the map.
 *
 * This is kept separate from the broader insights request so the larger list
 * limit applies only to the lightweight core Cabin response.
 *
 * @param int $date_range Number of days to retrieve.
 * @return array|WP_Error Normalized country rows or an error.
 */
function uplianda_get_country_traffic( $date_range = 30 ) {
	$options = uplianda_get_options();
	$api_key = isset( $options['api_key'] ) ? trim( (string) $options['api_key'] ) : '';
	$domain  = isset( $options['domain'] ) ? uplianda_normalize_domain( $options['domain'] ) : '';

	if ( '' === $api_key ) {
		return new WP_Error( 'uplianda_no_api_key', __( 'Cabin Analytics API key not configured.', 'uplink-analytics-dashboard-for-cabin' ) );
	}

	if ( '' === $domain ) {
		return new WP_Error( 'uplianda_no_domain', __( 'Cabin Analytics domain not configured.', 'uplink-analytics-dashboard-for-cabin' ) );
	}

	$date_range = absint( $date_range );
	if ( ! in_array( $date_range, uplianda_get_allowed_chart_ranges(), true ) ) {
		$date_range = 30;
	}

	$today     = new DateTimeImmutable( 'today', wp_timezone() );
	$date_to   = $today->format( 'Y-m-d' );
	$date_from = $today->modify( '-' . ( $date_range - 1 ) . ' days' )->format( 'Y-m-d' );
	$cache_key = 'uplianda_country_traffic_' . md5( 'v1|' . $domain . '|' . $date_from . '|' . $date_to );
	$cached    = get_transient( $cache_key );

	if ( false !== $cached ) {
		return $cached;
	}

	$url = add_query_arg(
		array(
			'domain'      => $domain,
			'date_from'   => $date_from,
			'date_to'     => $date_to,
			'scope'       => 'core',
			'limit_lists' => 250,
		),
		'https://api.withcabin.com/v1/analytics'
	);

	$response = wp_remote_get(
		$url,
		array(
			'headers'    => array( 'x-api-key' => $api_key ),
			'timeout'    => 15,
			'user-agent' => 'WordPress Uplink Analytics Dashboard for Cabin/' . UPLIANDA_VERSION,
		)
	);

	if ( is_wp_error( $response ) ) {
		return $response;
	}

	$response_code = wp_remote_retrieve_response_code( $response );
	if ( 200 !== $response_code ) {
		return new WP_Error(
			'uplianda_api_error',
			sprintf(
				/* translators: %d: HTTP status code. */
				__( 'Cabin Analytics API returned HTTP %d.', 'uplink-analytics-dashboard-for-cabin' ),
				$response_code
			)
		);
	}

	$data = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( JSON_ERROR_NONE !== json_last_error() || ! is_array( $data ) ) {
		return new WP_Error( 'uplianda_json_error', __( 'Cabin Analytics returned an invalid response.', 'uplink-analytics-dashboard-for-cabin' ) );
	}

	$items = uplianda_prepare_country_traffic( $data['countries'] ?? array() );
	set_transient( $cache_key, $items, 15 * MINUTE_IN_SECONDS );

	return $items;
}

/**
 * Normalize Cabin country rows while preserving their ISO alpha-2 codes.
 *
 * @param array $values Country rows from Cabin.
 * @return array<int, array{code:string,value:float}> Normalized rows.
 */
function uplianda_prepare_country_traffic( $values ) {
	$items = array();

	if ( ! is_array( $values ) ) {
		return $items;
	}

	foreach ( $values as $key => $row ) {
		if ( is_array( $row ) ) {
			$code  = isset( $row['code'] ) ? strtoupper( sanitize_key( (string) $row['code'] ) ) : '';
			$value = isset( $row['value'] ) ? (float) $row['value'] : 0;
		} else {
			$code  = strtoupper( sanitize_key( (string) $key ) );
			$value = (float) $row;
		}

		if ( 2 !== strlen( $code ) || $value <= 0 ) {
			continue;
		}

		$items[ $code ] = array(
			'code'  => $code,
			'value' => $value,
		);
	}

	$items = array_values( $items );
	usort(
		$items,
		static function ( $first, $second ) {
			return $second['value'] <=> $first['value'];
		}
	);

	return $items;
}

/**
 * Load the bundled public-domain Natural Earth country geometry.
 *
 * @return array Map metadata.
 */
function uplianda_get_country_map_data() {
	static $map_data = null;

	if ( null === $map_data ) {
		$map_data = include UPLIANDA_PLUGIN_DIR . 'includes/country-map-data.php';
	}

	return is_array( $map_data ) ? $map_data : array();
}

/**
 * Render the privacy-friendly country traffic map and its text equivalent.
 *
 * @param array $items      Normalized country traffic rows.
 * @param int   $date_range Reporting period in days.
 * @return string Map markup.
 */
function uplianda_render_country_map( $items, $date_range, $title = '' ) {
	$map_data  = uplianda_get_country_map_data();
	$geometry  = isset( $map_data['countries'] ) && is_array( $map_data['countries'] ) ? $map_data['countries'] : array();
	$view_box  = isset( $map_data['view_box'] ) ? (string) $map_data['view_box'] : '0 0 960 500';
	$by_code   = array();
	$maximum   = 0;
	$map_id    = wp_unique_id( 'uplianda-country-map-' );
	$title_id  = $map_id . '-title';
	$desc_id   = $map_id . '-description';
	$title     = sanitize_text_field( (string) $title );
	$title     = '' !== $title ? $title : __( 'Country Traffic', 'uplink-analytics-dashboard-for-cabin' );

	foreach ( $items as $item ) {
		$by_code[ $item['code'] ] = (float) $item['value'];
		$maximum = max( $maximum, (float) $item['value'] );
	}

	$rank_by_code = array_flip( array_column( $items, 'code' ) );

	ob_start();
	?>
	<figure class="uplianda-country-map">
		<div class="uplianda-country-map__heading">
			<div>
				<h3 class="uplianda-country-map__title"><?php echo esc_html( $title ); ?></h3>
				<p class="uplianda-country-map__description"><?php esc_html_e( 'Aggregated visits reported by Cabin. No map service or visitor location request is used.', 'uplink-analytics-dashboard-for-cabin' ); ?></p>
			</div>
			<span class="uplianda-country-map__period"><?php echo esc_html( sprintf( /* translators: %d: number of days. */ _n( 'Last %d day', 'Last %d days', $date_range, 'uplink-analytics-dashboard-for-cabin' ), $date_range ) ); ?></span>
		</div>

		<?php if ( empty( $items ) || empty( $geometry ) ) : ?>
			<p class="uplianda-insights__empty"><?php esc_html_e( 'No country traffic data is available for this period.', 'uplink-analytics-dashboard-for-cabin' ); ?></p>
		<?php else : ?>
			<div class="uplianda-country-map__layout">
				<div class="uplianda-country-map__visual">
					<svg class="uplianda-country-map__svg" viewBox="<?php echo esc_attr( $view_box ); ?>" role="group" aria-labelledby="<?php echo esc_attr( $title_id . ' ' . $desc_id ); ?>">
						<title id="<?php echo esc_attr( $title_id ); ?>"><?php esc_html_e( 'World map of aggregated Cabin traffic by country', 'uplink-analytics-dashboard-for-cabin' ); ?></title>
						<desc id="<?php echo esc_attr( $desc_id ); ?>"><?php esc_html_e( 'Darker countries represent more visits. Tab to the map and use the arrow keys to move through countries in traffic order. Exact values are listed beside and below the map.', 'uplink-analytics-dashboard-for-cabin' ); ?></desc>
						<?php foreach ( $geometry as $code => $country ) : ?>
							<?php
							$value = $by_code[ $code ] ?? 0;
							$level = $value > 0 && $maximum > 0 ? max( 1, min( 5, (int) ceil( sqrt( $value / $maximum ) * 5 ) ) ) : 0;
							?>
							<?php if ( $value > 0 ) : ?>
								<path
									class="uplianda-country-map__country uplianda-country-map__country--<?php echo esc_attr( $level ); ?>"
									d="<?php echo esc_attr( $country['path'] ); ?>"
									tabindex="<?php echo esc_attr( 0 === $rank_by_code[ $code ] ? '0' : '-1' ); ?>"
									role="img"
									aria-label="<?php echo esc_attr( sprintf( /* translators: 1: country name, 2: number of visits. */ __( '%1$s: %2$s visits', 'uplink-analytics-dashboard-for-cabin' ), $country['name'], number_format_i18n( $value ) ) ); ?>"
									data-country-name="<?php echo esc_attr( $country['name'] ); ?>"
									data-country-value="<?php echo esc_attr( number_format_i18n( $value ) ); ?>"
									data-country-rank="<?php echo esc_attr( $rank_by_code[ $code ] + 1 ); ?>"
								><title><?php echo esc_html( sprintf( /* translators: 1: country name, 2: number of visits. */ __( '%1$s: %2$s visits', 'uplink-analytics-dashboard-for-cabin' ), $country['name'], number_format_i18n( $value ) ) ); ?></title></path>
							<?php else : ?>
								<path class="uplianda-country-map__country uplianda-country-map__country--0" d="<?php echo esc_attr( $country['path'] ); ?>"></path>
							<?php endif; ?>
						<?php endforeach; ?>
					</svg>
					<div class="uplianda-country-map__tooltip" aria-hidden="true" hidden>
						<strong data-uplianda-map-tooltip-name></strong>
						<span class="uplianda-country-map__tooltip-value" data-uplianda-map-tooltip-value></span>
						<span><?php esc_html_e( 'visits', 'uplink-analytics-dashboard-for-cabin' ); ?></span>
					</div>
					<div class="uplianda-country-map__legend" aria-hidden="true">
						<span><?php esc_html_e( 'Fewer visits', 'uplink-analytics-dashboard-for-cabin' ); ?></span>
						<span class="uplianda-country-map__swatch uplianda-country-map__swatch--1"></span>
						<span class="uplianda-country-map__swatch uplianda-country-map__swatch--2"></span>
						<span class="uplianda-country-map__swatch uplianda-country-map__swatch--3"></span>
						<span class="uplianda-country-map__swatch uplianda-country-map__swatch--4"></span>
						<span class="uplianda-country-map__swatch uplianda-country-map__swatch--5"></span>
						<span><?php esc_html_e( 'More visits', 'uplink-analytics-dashboard-for-cabin' ); ?></span>
					</div>
				</div>

				<section class="uplianda-country-map__ranking" aria-labelledby="<?php echo esc_attr( $map_id . '-ranking' ); ?>">
					<h4 id="<?php echo esc_attr( $map_id . '-ranking' ); ?>" class="uplianda-country-map__ranking-title"><?php esc_html_e( 'Top countries', 'uplink-analytics-dashboard-for-cabin' ); ?></h4>
					<ol class="uplianda-country-map__list">
						<?php foreach ( array_slice( $items, 0, 10 ) as $item ) : ?>
							<?php $country_name = isset( $geometry[ $item['code'] ]['name'] ) ? $geometry[ $item['code'] ]['name'] : $item['code']; ?>
							<li><span><?php echo esc_html( $country_name ); ?></span><strong><?php echo esc_html( number_format_i18n( $item['value'] ) ); ?></strong></li>
						<?php endforeach; ?>
					</ol>
				</section>
			</div>

			<?php if ( count( $items ) > 10 ) : ?>
				<details class="uplianda-country-map__all">
					<summary><?php esc_html_e( 'View all country data', 'uplink-analytics-dashboard-for-cabin' ); ?></summary>
					<table>
						<thead><tr><th scope="col"><?php esc_html_e( 'Country', 'uplink-analytics-dashboard-for-cabin' ); ?></th><th scope="col"><?php esc_html_e( 'Visits', 'uplink-analytics-dashboard-for-cabin' ); ?></th></tr></thead>
						<tbody>
							<?php foreach ( $items as $item ) : ?>
								<?php $country_name = isset( $geometry[ $item['code'] ]['name'] ) ? $geometry[ $item['code'] ]['name'] : $item['code']; ?>
								<tr><th scope="row"><?php echo esc_html( $country_name ); ?></th><td><?php echo esc_html( number_format_i18n( $item['value'] ) ); ?></td></tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</details>
			<?php endif; ?>
		<?php endif; ?>
	</figure>
	<?php
	return trim( ob_get_clean() );
}

/**
 * Fetch and render the public country map.
 *
 * @param int $date_range Reporting period in days.
 * @return string Map markup.
 */
function uplianda_render_public_country_map( $date_range = 30, $title = '' ) {
	$date_range = absint( $date_range );
	if ( ! in_array( $date_range, uplianda_get_allowed_chart_ranges(), true ) ) {
		$date_range = 30;
	}

	$items = uplianda_get_country_traffic( $date_range );
	if ( is_wp_error( $items ) ) {
		return '<p class="uplianda-insights__status" role="status">' . esc_html( $items->get_error_message() ) . '</p>';
	}

	wp_enqueue_style( 'uplianda-insights' );
	wp_enqueue_script( 'uplianda-country-map' );

	return '<div class="uplianda-insights uplianda-insights--public uplianda-insights--country-map">' . uplianda_render_country_map( $items, $date_range, $title ) . '</div>';
}

/**
 * Normalize an associative Cabin breakdown and sort it by value.
 *
 * @param array $values Breakdown values.
 * @param int   $limit  Maximum number of rows.
 * @return array<int, array{label:string,value:float}> Normalized rows.
 */
function uplianda_prepare_map_breakdown( $values, $limit = 10 ) {
	$items = array();

	if ( ! is_array( $values ) ) {
		return $items;
	}

	foreach ( $values as $label => $value ) {
		$value = (float) $value;
		if ( $value <= 0 ) {
			continue;
		}

		$items[] = array(
			'label' => ucwords( str_replace( array( '_', '-' ), ' ', sanitize_text_field( (string) $label ) ) ),
			'value' => $value,
		);
	}

	usort(
		$items,
		static function ( $first, $second ) {
			return $second['value'] <=> $first['value'];
		}
	);

	return array_slice( $items, 0, absint( $limit ) );
}

/**
 * Normalize a list-shaped Cabin breakdown.
 *
 * @param array  $values    Breakdown rows.
 * @param string $label_key Row key containing the label.
 * @param string $value_key Row key containing the value.
 * @param int    $limit     Maximum number of rows.
 * @param bool   $uppercase Whether to uppercase the label.
 * @return array<int, array{label:string,value:float}> Normalized rows.
 */
function uplianda_prepare_list_breakdown( $values, $label_key, $value_key, $limit = 10, $uppercase = false ) {
	$items = array();

	if ( ! is_array( $values ) ) {
		return $items;
	}

	foreach ( $values as $row ) {
		if ( ! is_array( $row ) || ! isset( $row[ $label_key ], $row[ $value_key ] ) ) {
			continue;
		}

		$label = sanitize_text_field( (string) $row[ $label_key ] );
		$value = (float) $row[ $value_key ];

		if ( '' === $label || $value <= 0 ) {
			continue;
		}

		$items[] = array(
			'label' => $uppercase ? strtoupper( $label ) : $label,
			'value' => $value,
		);
	}

	usort(
		$items,
		static function ( $first, $second ) {
			return $second['value'] <=> $first['value'];
		}
	);

	return array_slice( $items, 0, absint( $limit ) );
}

/**
 * Render one manager insight breakdown.
 *
 * @param string $title Section title.
 * @param array  $items Normalized rows.
 * @return string Section markup.
 */
function uplianda_render_insight_breakdown( $title, $items ) {
	ob_start();
	?>
	<section class="uplianda-insights__panel">
		<h3 class="uplianda-insights__panel-title"><?php echo esc_html( $title ); ?></h3>
		<?php if ( empty( $items ) ) : ?>
			<p class="uplianda-insights__empty"><?php esc_html_e( 'No data available for this period.', 'uplink-analytics-dashboard-for-cabin' ); ?></p>
		<?php else : ?>
			<?php $maximum = max( array_column( $items, 'value' ) ); ?>
			<ul class="uplianda-insights__list">
				<?php foreach ( $items as $item ) : ?>
					<li class="uplianda-insights__item">
						<span class="uplianda-insights__item-row">
							<span class="uplianda-insights__item-label"><?php echo esc_html( $item['label'] ); ?></span>
							<span class="uplianda-insights__item-value"><?php echo esc_html( number_format_i18n( $item['value'] ) ); ?></span>
						</span>
						<progress class="uplianda-insights__progress" max="<?php echo esc_attr( $maximum ); ?>" value="<?php echo esc_attr( $item['value'] ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: 1: metric label, 2: metric value. */ __( '%1$s: %2$s', 'uplink-analytics-dashboard-for-cabin' ), $item['label'], number_format_i18n( $item['value'] ) ) ); ?>"></progress>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</section>
	<?php
	return trim( ob_get_clean() );
}

/**
 * Render a report heading and reporting period.
 *
 * @param string $title      Report heading.
 * @param int    $date_range Reporting period in days.
 * @return string Heading markup.
 */
function uplianda_render_insight_heading( $title, $date_range ) {
	$title = sanitize_text_field( (string) $title );

	if ( '' === $title ) {
		return '';
	}

	return sprintf(
		'<div class="uplianda-insights__section-heading"><h3>%1$s</h3><span>%2$s</span></div>',
		esc_html( $title ),
		esc_html( sprintf( /* translators: %d: number of days. */ _n( 'Last %d day', 'Last %d days', $date_range, 'uplink-analytics-dashboard-for-cabin' ), $date_range ) )
	);
}

/**
 * Render the Sustainability insight section.
 *
 * @param array $data       Cabin API response.
 * @param int   $date_range Reporting period in days.
 * @param array $modules    Module visibility map.
 * @return string Sustainability markup.
 */
function uplianda_render_sustainability( $data, $date_range, $modules = array(), $title = '' ) {
	$modules         = wp_parse_args( $modules, array( 'total_co2' => true, 'average_co2' => true, 'transfer_volume' => true, 'green_hosting' => true ) );
	$energy          = isset( $data['energy'] ) && is_array( $data['energy'] ) ? $data['energy'] : array();
	$total_co2       = isset( $energy['total_co2_grams'] ) ? (float) $energy['total_co2_grams'] : 0;
	$average_co2     = isset( $energy['average_co2_grams'] ) ? (float) $energy['average_co2_grams'] : 0;
	$transfer_bytes  = isset( $energy['transferred_bytes'] ) ? (float) $energy['transferred_bytes'] : 0;
	$green_hosting   = isset( $energy['green_hosting'] ) && is_array( $energy['green_hosting'] ) ? $energy['green_hosting'] : array();
	$green_status    = array_key_exists( 'green', $green_hosting ) ? (bool) $green_hosting['green'] : null;
	$host_name       = isset( $green_hosting['hosted_by'] ) ? sanitize_text_field( (string) $green_hosting['hosted_by'] ) : '';
	$has_energy      = ( $modules['total_co2'] && $total_co2 > 0 ) || ( $modules['average_co2'] && $average_co2 > 0 ) || ( $modules['transfer_volume'] && $transfer_bytes > 0 ) || ( $modules['green_hosting'] && null !== $green_status );
	$has_modules     = count( array_filter( $modules ) ) > 0;
	$title           = sanitize_text_field( (string) $title );
	$title           = '' !== $title ? $title : __( 'Sustainability summary', 'uplink-analytics-dashboard-for-cabin' );

	ob_start();
	?>
	<div class="uplianda-insights__results">
		<section class="uplianda-insights__sustainability">
			<?php echo wp_kses_post( uplianda_render_insight_heading( $title, $date_range ) ); ?>
			<?php if ( $has_energy ) : ?>
				<dl class="uplianda-insights__metrics">
					<?php if ( $modules['total_co2'] && $total_co2 > 0 ) : ?>
						<div class="uplianda-insights__metric"><dt><?php esc_html_e( 'Total CO₂', 'uplink-analytics-dashboard-for-cabin' ); ?></dt><dd><?php echo esc_html( number_format_i18n( $total_co2, 2 ) ); ?> g</dd></div>
					<?php endif; ?>
					<?php if ( $modules['average_co2'] && $average_co2 > 0 ) : ?>
						<div class="uplianda-insights__metric"><dt><?php esc_html_e( 'Average CO₂ per page', 'uplink-analytics-dashboard-for-cabin' ); ?></dt><dd><?php echo esc_html( number_format_i18n( $average_co2, 4 ) ); ?> g</dd></div>
					<?php endif; ?>
					<?php if ( $modules['transfer_volume'] && $transfer_bytes > 0 ) : ?>
						<div class="uplianda-insights__metric"><dt><?php esc_html_e( 'Transfer volume', 'uplink-analytics-dashboard-for-cabin' ); ?></dt><dd><?php echo esc_html( size_format( $transfer_bytes, 1 ) ); ?></dd></div>
					<?php endif; ?>
					<?php if ( $modules['green_hosting'] && null !== $green_status ) : ?>
						<div class="uplianda-insights__metric"><dt><?php esc_html_e( 'Green hosting', 'uplink-analytics-dashboard-for-cabin' ); ?></dt><dd><?php echo esc_html( $green_status ? __( 'Verified', 'uplink-analytics-dashboard-for-cabin' ) : __( 'Not verified', 'uplink-analytics-dashboard-for-cabin' ) ); ?><?php echo '' !== $host_name ? ' · ' . esc_html( $host_name ) : ''; ?></dd></div>
					<?php endif; ?>
				</dl>
			<?php elseif ( $has_modules ) : ?>
				<p class="uplianda-insights__empty"><?php esc_html_e( 'Cabin did not return sustainability data for this period.', 'uplink-analytics-dashboard-for-cabin' ); ?></p>
			<?php else : ?>
				<p class="uplianda-insights__empty"><?php esc_html_e( 'No Sustainability modules are enabled.', 'uplink-analytics-dashboard-for-cabin' ); ?></p>
			<?php endif; ?>
		</section>
	</div>
	<?php
	return trim( ob_get_clean() );
}

/**
 * Render the Demographics insight section.
 *
 * @param array $data    Cabin API response.
 * @param array $modules Module visibility map.
 * @return string Demographics markup.
 */

function uplianda_render_demographics( $data, $modules = array(), $title = '', $date_range = 30 ) {
	$modules   = wp_parse_args( $modules, array( 'devices' => true, 'countries' => true, 'languages' => true, 'browsers' => true ) );
	$devices   = uplianda_prepare_map_breakdown( $data['devices'] ?? array() );
	$browsers  = uplianda_prepare_list_breakdown( $data['browsers'] ?? array(), 'name', 'value' );
	$countries = uplianda_prepare_list_breakdown( $data['countries'] ?? array(), 'code', 'value', 10, true );
	$languages = uplianda_prepare_list_breakdown( $data['languages'] ?? array(), 'code', 'value', 10, true );

	$markup = '';
	$markup .= $modules['devices'] ? uplianda_render_insight_breakdown( __( 'Device mix', 'uplink-analytics-dashboard-for-cabin' ), $devices ) : '';
	$markup .= $modules['countries'] ? uplianda_render_insight_breakdown( __( 'Countries', 'uplink-analytics-dashboard-for-cabin' ), $countries ) : '';
	$markup .= $modules['languages'] ? uplianda_render_insight_breakdown( __( 'Languages', 'uplink-analytics-dashboard-for-cabin' ), $languages ) : '';
	$markup .= $modules['browsers'] ? uplianda_render_insight_breakdown( __( 'Browsers', 'uplink-analytics-dashboard-for-cabin' ), $browsers ) : '';

	if ( '' === $markup ) {
		return '<div class="uplianda-insights__results">' . uplianda_render_insight_heading( $title, $date_range ) . '<p class="uplianda-insights__empty">' . esc_html__( 'No Demographics modules are enabled.', 'uplink-analytics-dashboard-for-cabin' ) . '</p></div>';
	}

	return '<div class="uplianda-insights__results">' . uplianda_render_insight_heading( $title, $date_range ) . '<div class="uplianda-insights__grid">' . $markup . '</div></div>';
}

/**
 * Render the Sources insight section.
 *
 * @param array $data    Cabin API response.
 * @param array $modules Module visibility map.
 * @return string Sources markup.
 */

function uplianda_render_sources( $data, $modules = array(), $title = '', $date_range = 30 ) {
	$modules         = wp_parse_args( $modules, array( 'traffic_sources' => true, 'referrers' => true ) );
	$traffic_sources = uplianda_prepare_map_breakdown( $data['traffic_sources'] ?? array() );
	$referrers       = uplianda_prepare_list_breakdown( $data['referrals'] ?? array(), 'source', 'page_views' );

	$markup = '';
	$markup .= $modules['traffic_sources'] ? uplianda_render_insight_breakdown( __( 'Traffic sources', 'uplink-analytics-dashboard-for-cabin' ), $traffic_sources ) : '';
	$markup .= $modules['referrers'] ? uplianda_render_insight_breakdown( __( 'Top referrers', 'uplink-analytics-dashboard-for-cabin' ), $referrers ) : '';

	if ( '' === $markup ) {
		return '<div class="uplianda-insights__results">' . uplianda_render_insight_heading( $title, $date_range ) . '<p class="uplianda-insights__empty">' . esc_html__( 'No Sources modules are enabled.', 'uplink-analytics-dashboard-for-cabin' ) . '</p></div>';
	}

	return '<div class="uplianda-insights__results">' . uplianda_render_insight_heading( $title, $date_range ) . '<div class="uplianda-insights__grid">' . $markup . '</div></div>';
}

/**
 * Render one named insight section from prepared Cabin data.
 *
 * @param string $section    Insight section slug.
 * @param array  $data       Cabin API response.
 * @param int    $date_range Reporting period in days.
 * @param array  $modules    Module visibility map.
 * @return string Section markup.
 */
function uplianda_render_insight_section( $section, $data, $date_range, $modules = array(), $title = '' ) {
	if ( 'sustainability' === $section ) {
		return uplianda_render_sustainability( $data, $date_range, $modules, $title );
	}

	if ( 'demographics' === $section ) {
		return uplianda_render_demographics( $data, $modules, $title, $date_range );
	}

	if ( 'sources' === $section ) {
		return uplianda_render_sources( $data, $modules, $title, $date_range );
	}

	return '';
}

/**
 * Fetch and render one insight section for public block and shortcode output.
 *
 * Content is public only when a site owner explicitly places its block or shortcode.
 *
 * @param string $section    Insight section slug.
 * @param int    $date_range Reporting period in days.
 * @param array  $modules    Module visibility map.
 * @return string Public section markup.
 */
function uplianda_render_public_insight( $section, $date_range = 30, $modules = array(), $title = '' ) {
	if ( ! in_array( $section, uplianda_get_insight_sections(), true ) ) {
		return '';
	}

	$date_range = absint( $date_range );
	if ( ! in_array( $date_range, uplianda_get_allowed_chart_ranges(), true ) ) {
		$date_range = 30;
	}

	$data = uplianda_get_site_insights( $date_range );
	if ( is_wp_error( $data ) ) {
		return '<p class="uplianda-insights__status" role="status">' . esc_html( $data->get_error_message() ) . '</p>';
	}

	wp_enqueue_style( 'uplianda-insights' );
	$default_titles = array(
		'sustainability' => __( 'Sustainability', 'uplink-analytics-dashboard-for-cabin' ),
		'demographics'   => __( 'Demographics', 'uplink-analytics-dashboard-for-cabin' ),
		'sources'        => __( 'Sources', 'uplink-analytics-dashboard-for-cabin' ),
	);
	$title = sanitize_text_field( (string) $title );
	$title = '' !== $title ? $title : $default_titles[ $section ];

	return '<div class="uplianda-insights uplianda-insights--public uplianda-insights--' . esc_attr( $section ) . '">' . uplianda_render_insight_section( $section, $data, $date_range, $modules, $title ) . '</div>';
}

/**
 * Return the allowed dashboard Popular Content quantities.
 *
 * @return int[] Allowed quantities.
 */
function uplianda_get_allowed_popular_quantities() {
	return array( 5, 10, 20, 50 );
}

/**
 * Format a metric value for Popular Content output.
 *
 * @param mixed  $value Metric value.
 * @param string $type  Metric type.
 * @return string
 */
function uplianda_format_popular_metric( $value, $type = 'integer' ) {
	if ( '' === $value || null === $value ) {
		return '0';
	}

	if ( 'float' === $type ) {
		$value = (float) $value;
		return number_format_i18n( $value, 2 );
	}

	return number_format_i18n( (int) round( (float) $value ) );
}

/**
 * Format average page duration as Cabin-style approximate attention.
 *
 * @param mixed $seconds Average duration in seconds.
 * @return string Approximate duration such as ~52s or ~2m.
 */
function uplianda_format_attention( $seconds ) {
	$seconds = max( 0, (int) round( (float) $seconds ) );

	if ( $seconds >= MINUTE_IN_SECONDS ) {
		return sprintf(
			/* translators: %s: approximate number of minutes. */
			__( '~%sm', 'uplink-analytics-dashboard-for-cabin' ),
			number_format_i18n( (int) round( $seconds / MINUTE_IN_SECONDS ) )
		);
	}

	return sprintf(
		/* translators: %s: approximate number of seconds. */
		__( '~%ss', 'uplink-analytics-dashboard-for-cabin' ),
		number_format_i18n( $seconds )
	);
}

/**
 * Render dashboard Popular Content items.
 *
 * @param array $items Popular content items.
 * @return string
 */
function uplianda_render_popular_content_dashboard_items( $items ) {
	if ( empty( $items ) ) {
		return '<p class="uplianda-popular-content__message">' . esc_html__( 'No popular content was found for this date range.', 'uplink-analytics-dashboard-for-cabin' ) . '</p>';
	}

	ob_start();
	?>
	<ol class="uplianda-popular-content__summary-list">
		<?php foreach ( $items as $index => $item ) : ?>
			<li class="uplianda-popular-content__summary-item">
				<details class="uplianda-popular-content__details">
					<summary class="uplianda-popular-content__summary">
						<span class="uplianda-popular-content__summary-title">
							<span class="uplianda-popular-content__summary-number"><?php echo esc_html( (string) ( $index + 1 ) ); ?>.</span>
							<span class="uplianda-popular-content__summary-name"><?php echo esc_html( $item['title'] ); ?></span>
						</span>
						<span class="uplianda-popular-content__summary-actions">
							<span class="uplianda-popular-content__summary-action"><?php esc_html_e( 'view details', 'uplink-analytics-dashboard-for-cabin' ); ?></span>
							<?php /* translators: %s: content title. */ ?>
							<a class="uplianda-popular-content__summary-content-link" href="<?php echo esc_url( $item['url'] ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Visit %s', 'uplink-analytics-dashboard-for-cabin' ), $item['title'] ) ); ?>"><?php esc_html_e( 'view content', 'uplink-analytics-dashboard-for-cabin' ); ?></a>
						</span>
					</summary>
					<dl class="uplianda-popular-content__metrics">
						<div class="uplianda-popular-content__metric">
							<dt class="uplianda-popular-content__metric-label"><?php esc_html_e( 'Page views', 'uplink-analytics-dashboard-for-cabin' ); ?></dt>
							<dd class="uplianda-popular-content__metric-value"><?php echo esc_html( uplianda_format_popular_metric( $item['page_views'] ?? 0 ) ); ?></dd>
						</div>
						<div class="uplianda-popular-content__metric">
							<dt class="uplianda-popular-content__metric-label"><?php esc_html_e( 'Unique visitors', 'uplink-analytics-dashboard-for-cabin' ); ?></dt>
							<dd class="uplianda-popular-content__metric-value"><?php echo esc_html( uplianda_format_popular_metric( $item['unique_visitors'] ?? 0 ) ); ?></dd>
						</div>
						<div class="uplianda-popular-content__metric">
							<dt class="uplianda-popular-content__metric-label"><?php esc_html_e( 'Attention', 'uplink-analytics-dashboard-for-cabin' ); ?></dt>
							<dd class="uplianda-popular-content__metric-value"><?php echo esc_html( uplianda_format_attention( $item['average_duration_seconds'] ?? 0 ) ); ?></dd>
						</div>
						<?php if ( isset( $item['co2_grams'] ) && (float) $item['co2_grams'] > 0 ) : ?>
							<div class="uplianda-popular-content__metric">
								<dt class="uplianda-popular-content__metric-label"><?php esc_html_e( 'CO₂ grams', 'uplink-analytics-dashboard-for-cabin' ); ?></dt>
								<dd class="uplianda-popular-content__metric-value"><?php echo esc_html( uplianda_format_popular_metric( $item['co2_grams'], 'float' ) ); ?></dd>
							</div>
						<?php endif; ?>
					</dl>
				</details>
			</li>
		<?php endforeach; ?>
	</ol>
	<?php
	return trim( ob_get_clean() );
}

/**
 * Return the allowed chart date ranges.
 *
 * @return int[] Allowed date ranges in days.
 */
function uplianda_get_allowed_chart_ranges() {
	return array( 7, 14, 30, 90 );
}

/**
 * Normalize a domain value for comparison and Cabin API requests.
 *
 * @param string $domain Domain value.
 * @return string Normalized domain.
 */
function uplianda_normalize_domain( $domain ) {
	$domain = sanitize_text_field( (string) $domain );
	$domain = preg_replace( '#^https?://#i', '', $domain );
	$domain = preg_replace( '#^www\.#i', '', $domain );
	return trim( $domain, "/ \t\n\r\0\x0B" );
}

/**
 * Validate whether a stats request is limited to a supported range and the saved domain.
 *
 * @param WP_REST_Request $request REST request.
 * @param string          $domain  Normalized requested domain.
 * @return true|WP_Error True when valid, otherwise an error.
 */
function uplianda_validate_stats_request( $request, $domain ) {
	$options         = uplianda_get_options();
	$saved_domain    = isset( $options['domain'] ) ? uplianda_normalize_domain( $options['domain'] ) : '';
	$requested_start = (string) $request->get_param( 'start_date' );
	$requested_end   = (string) $request->get_param( 'end_date' );

	if ( '' === $saved_domain || $domain !== $saved_domain ) {
		return new WP_Error(
			'uplianda_invalid_domain',
			__( 'Analytics can only be requested for the configured Cabin Analytics domain.', 'uplink-analytics-dashboard-for-cabin' ),
			array( 'status' => 403 )
		);
	}

	try {
		$start = new DateTimeImmutable( $requested_start, wp_timezone() );
		$end   = new DateTimeImmutable( $requested_end, wp_timezone() );
	} catch ( Exception $exception ) {
		return new WP_Error(
			'uplianda_invalid_date',
			__( 'Invalid analytics date range.', 'uplink-analytics-dashboard-for-cabin' ),
			array( 'status' => 400 )
		);
	}

	$days = (int) $start->diff( $end )->format( '%a' ) + 1;

	if ( $start > $end || ! in_array( $days, uplianda_get_allowed_chart_ranges(), true ) ) {
		return new WP_Error(
			'uplianda_invalid_date_range',
			__( 'Analytics date range must be one of the supported ranges.', 'uplink-analytics-dashboard-for-cabin' ),
			array( 'status' => 400 )
		);
	}

	return true;
}

/**
 * Validate date format
 */
function uplianda_validate_date( $value, $request, $param ) {
	return (bool) preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value );
}

/**
 * Get stats from Cabin API
 */
function uplianda_get_stats( $request ) {
	$options = uplianda_get_options();
	$api_key = isset( $options['api_key'] ) ? trim( $options['api_key'] ) : '';
	
	if ( empty( $api_key ) ) {
		return new WP_Error( 'uplianda_no_api_key', __( 'Cabin Analytics API key not configured', 'uplink-analytics-dashboard-for-cabin' ), array( 'status' => 400 ) );
	}

	$domain = $request->get_param( 'domain' );
	if ( empty( $domain ) ) {
		$domain = isset( $options['domain'] ) ? trim( $options['domain'] ) : '';
	}

	if ( empty( $domain ) ) {
		return new WP_Error( 'uplianda_no_domain', __( 'Domain not specified', 'uplink-analytics-dashboard-for-cabin' ), array( 'status' => 400 ) );
	}

	$domain = uplianda_normalize_domain( $domain );

	$validation = uplianda_validate_stats_request( $request, $domain );
	if ( is_wp_error( $validation ) ) {
		return $validation;
	}

	$start_date = $request->get_param( 'start_date' );
	$end_date = $request->get_param( 'end_date' );

	$cache_key = 'uplianda_' . md5( 'public-v1|' . $domain . $start_date . $end_date );
	$cached_data = get_transient( $cache_key );
	
	if ( false !== $cached_data ) {
		return rest_ensure_response( $cached_data );
	}

	$url = add_query_arg(
		array(
			'domain' => $domain,
			'date_from' => $start_date,
			'date_to' => $end_date,
			'scope' => 'core',
			'limit_lists' => 20,
		),
		'https://api.withcabin.com/v1/analytics'
	);

	$response = wp_remote_get(
		$url,
		array(
			'headers' => array(
				'x-api-key' => $api_key,
			),
			'timeout' => 15,
			'user-agent' => 'WordPress Uplink Analytics Dashboard for Cabin/' . UPLIANDA_VERSION,
		)
	);

	if ( is_wp_error( $response ) ) {
		return new WP_Error(
			'uplianda_api_error',
			sprintf(
				/* translators: %s: Error message */
				__( 'Failed to connect to Cabin Analytics: %s', 'uplink-analytics-dashboard-for-cabin' ),
				$response->get_error_message()
			),
			array( 'status' => 500 )
		);
	}

	$response_code = wp_remote_retrieve_response_code( $response );
	$body = wp_remote_retrieve_body( $response );

	if ( $response_code !== 200 ) {
		$error_data = json_decode( $body, true );
		$error_message = isset( $error_data['message'] ) ? $error_data['message'] : __( 'Unknown error from Cabin Analytics API', 'uplink-analytics-dashboard-for-cabin' );
		
		return new WP_Error(
			'uplianda_api_error',
			sprintf(
				/* translators: 1: HTTP status code 2: Error message */
				__( 'Cabin Analytics API error (HTTP %1$d): %2$s', 'uplink-analytics-dashboard-for-cabin' ),
				$response_code,
				$error_message
			),
			array( 'status' => $response_code )
		);
	}

	$data = json_decode( $body, true );

	if ( json_last_error() !== JSON_ERROR_NONE ) {
		return new WP_Error(
			'uplianda_json_error',
			__( 'Failed to parse response from Cabin Analytics', 'uplink-analytics-dashboard-for-cabin' ),
			array( 'status' => 500 )
		);
	}

	$public_data = array(
		'summary'    => isset( $data['summary'] ) && is_array( $data['summary'] ) ? $data['summary'] : array(),
		'daily_data' => isset( $data['daily_data'] ) && is_array( $data['daily_data'] ) ? $data['daily_data'] : array(),
	);

	set_transient( $cache_key, $public_data, 5 * MINUTE_IN_SECONDS );

	return rest_ensure_response( $public_data );
}

/**
 * Enqueue dashboard widget assets
 */
function uplianda_enqueue_assets() {
	$asset_file_path = UPLIANDA_PLUGIN_DIR . 'assets/view.asset.php';
	
	if ( ! file_exists( $asset_file_path ) ) {
		return;
	}
	
	$asset_file   = include( $asset_file_path );
	$view_js_path = UPLIANDA_PLUGIN_DIR . 'assets/view.js';
	$style_path   = UPLIANDA_PLUGIN_DIR . 'assets/style-index.css';
	$script_ver   = file_exists( $view_js_path ) ? filemtime( $view_js_path ) : UPLIANDA_VERSION;
	$style_ver    = file_exists( $style_path ) ? filemtime( $style_path ) : UPLIANDA_VERSION;
	
	wp_register_script(
		'uplianda-view',
		UPLIANDA_PLUGIN_URL . 'assets/view.js',
		$asset_file['dependencies'],
		$script_ver,
		true
	);
	
	wp_register_style(
		'uplianda-style',
		UPLIANDA_PLUGIN_URL . 'assets/style-index.css',
		array(),
		$style_ver
	);

	wp_register_style(
		'uplianda-style-editor',
		UPLIANDA_PLUGIN_URL . 'assets/style-index.css',
		array(),
		$style_ver
	);

	$insights_js_path  = UPLIANDA_PLUGIN_DIR . 'assets/insights.js';
	$insights_css_path = UPLIANDA_PLUGIN_DIR . 'assets/insights.css';

	wp_register_script(
		'uplianda-insights',
		UPLIANDA_PLUGIN_URL . 'assets/insights.js',
		array(),
		file_exists( $insights_js_path ) ? filemtime( $insights_js_path ) : UPLIANDA_VERSION,
		true
	);

	wp_register_style(
		'uplianda-insights',
		UPLIANDA_PLUGIN_URL . 'assets/insights.css',
		array(),
		file_exists( $insights_css_path ) ? filemtime( $insights_css_path ) : UPLIANDA_VERSION
	);

	wp_register_style(
		'uplianda-insights-editor',
		UPLIANDA_PLUGIN_URL . 'assets/insights.css',
		array(),
		file_exists( $insights_css_path ) ? filemtime( $insights_css_path ) : UPLIANDA_VERSION
	);
}
/** Register block styles early enough for the iframe editor and front end. */
add_action( 'init', 'uplianda_enqueue_assets', 5 );
add_action( 'admin_enqueue_scripts', 'uplianda_enqueue_assets' );
add_action( 'wp_enqueue_scripts', 'uplianda_enqueue_assets' );


/**
 * Register dashboard widget.
 */
function uplianda_add_widget() {
	$options = uplianda_get_options();
	$api_key = isset( $options['api_key'] ) ? $options['api_key'] : '';
	
	if ( empty( $api_key ) || ! current_user_can( 'edit_posts' ) ) {
		return;
	}

	wp_add_dashboard_widget(
		'uplianda_widget',
		__( 'Uplink Site Traffic for Cabin', 'uplink-analytics-dashboard-for-cabin' ),
		'uplianda_widget_render'
	);

	wp_add_dashboard_widget(
		'uplianda_popular_content_widget',
		__( 'Uplink Popular Content for Cabin', 'uplink-analytics-dashboard-for-cabin' ),
		'uplianda_popular_content_widget_render'
	);


	wp_add_dashboard_widget(
		'uplianda_sustainability_widget',
		__( 'Uplink Sustainability for Cabin', 'uplink-analytics-dashboard-for-cabin' ),
		'uplianda_sustainability_widget_render'
	);

	wp_add_dashboard_widget(
		'uplianda_demographics_widget',
		__( 'Uplink Demographics for Cabin', 'uplink-analytics-dashboard-for-cabin' ),
		'uplianda_demographics_widget_render'
	);

	wp_add_dashboard_widget(
		'uplianda_sources_widget',
		__( 'Uplink Sources for Cabin', 'uplink-analytics-dashboard-for-cabin' ),
		'uplianda_sources_widget_render'
	);
}
add_action( 'wp_dashboard_setup', 'uplianda_add_widget' );

/**
 * Render dashboard widget
 */
function uplianda_widget_render() {
	$options = uplianda_get_options();
	$domain = isset( $options['domain'] ) ? $options['domain'] : '';
	$chart_type = isset( $options['chart_type'] ) ? $options['chart_type'] : 'bar';
	$date_range = isset( $options['date_range'] ) ? $options['date_range'] : '7';
	$dashboard_url = isset( $options['dashboard_url'] ) && ! empty( $options['dashboard_url'] ) ? $options['dashboard_url'] : UPLIANDA_CABIN_URL;

	wp_enqueue_script( 'uplianda-view' );
	wp_enqueue_style( 'uplianda-style' );

	printf(
		'<div class="uplianda-analytics-widget" data-title="%s" data-domain="%s" data-chart-type="%s" data-date-range="%s" data-allow-switching="true" data-dashboard-url="%s" data-rest-url="%s"></div>',
		esc_attr__( 'Uplink Site Traffic for Cabin', 'uplink-analytics-dashboard-for-cabin' ),
		esc_attr( $domain ),
		esc_attr( $chart_type ),
		esc_attr( $date_range ),
		esc_url( $dashboard_url ),
		esc_url( rest_url( 'uplianda/v1/stats' ) )
	);

}

/**
 * Render one manager-only insights dashboard widget shell.
 *
 * @param string $section Insight section slug.
 */
function uplianda_insights_widget_render( $section ) {
	$options       = uplianda_get_options();
	$domain        = isset( $options['domain'] ) ? uplianda_normalize_domain( $options['domain'] ) : '';
	$default_range = isset( $options['date_range'] ) ? absint( $options['date_range'] ) : 30;
	$default_range = in_array( $default_range, uplianda_get_allowed_chart_ranges(), true ) ? $default_range : 30;
	$target_id     = 'uplianda-' . $section . '-' . wp_rand( 1000, 9999 );

	wp_enqueue_script( 'uplianda-insights' );
	wp_enqueue_style( 'uplianda-insights' );
	?>
	<div
		class="uplianda-insights"
		data-uplianda-insights
		data-rest-url="<?php echo esc_url( rest_url( 'uplianda/v1/insights' ) ); ?>"
		data-rest-nonce="<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ); ?>"
		data-section="<?php echo esc_attr( $section ); ?>"
		data-target-id="<?php echo esc_attr( $target_id ); ?>"
		data-loading-message="<?php esc_attr_e( 'Loading Cabin data…', 'uplink-analytics-dashboard-for-cabin' ); ?>"
		data-error-message="<?php esc_attr_e( 'Cabin data could not be loaded.', 'uplink-analytics-dashboard-for-cabin' ); ?>"
	>
		<div class="uplianda-insights__header">
			<?php if ( '' !== $domain ) : ?>
				<p class="uplianda-insights__domain"><?php echo esc_html( $domain ); ?></p>
			<?php endif; ?>
			<div class="uplianda-insights__control">
				<label for="<?php echo esc_attr( $target_id ); ?>-range"><?php esc_html_e( 'Reporting period', 'uplink-analytics-dashboard-for-cabin' ); ?></label>
				<select id="<?php echo esc_attr( $target_id ); ?>-range" data-uplianda-insights-range>
					<?php foreach ( uplianda_get_allowed_chart_ranges() as $range ) : ?>
						<option value="<?php echo esc_attr( $range ); ?>" <?php selected( $default_range, $range ); ?>>
							<?php echo esc_html( sprintf( /* translators: %d: number of days. */ _n( 'Last %d day', 'Last %d days', $range, 'uplink-analytics-dashboard-for-cabin' ), $range ) ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
		<div id="<?php echo esc_attr( $target_id ); ?>" class="uplianda-insights__body" data-uplianda-insights-target aria-live="polite" aria-busy="true">
			<p class="uplianda-insights__status" role="status"><?php esc_html_e( 'Loading Cabin data…', 'uplink-analytics-dashboard-for-cabin' ); ?></p>
		</div>
	</div>
	<?php
}

/** Render the Sustainability dashboard widget. */
function uplianda_sustainability_widget_render() {
	uplianda_insights_widget_render( 'sustainability' );
}

/** Render the Demographics dashboard widget. */
function uplianda_demographics_widget_render() {
	uplianda_insights_widget_render( 'demographics' );
}

/** Render the Sources dashboard widget. */
function uplianda_sources_widget_render() {
	uplianda_insights_widget_render( 'sources' );
}

/**
 * Return the allowed HTML for dashboard segmented controls.
 *
 * @return array Allowed HTML.
 */
function uplianda_get_segmented_control_allowed_html() {
	return array(
		'div'    => array(
			'class'            => true,
			'role'             => true,
			'aria-label'       => true,
			'aria-labelledby'  => true,
		),
		'span'   => array(
			'id'    => true,
			'class' => true,
		),
		'button' => array(
			'class'                    => true,
			'type'                     => true,
			'aria-pressed'             => true,
			'data-uplianda-popular-range' => true,
			'data-uplianda-popular-qty'   => true,
		),
	);
}


/**
 * Render a reusable segmented control for dashboard widgets.
 *
 * @param array $args Control arguments.
 * @return string
 */
function uplianda_render_segmented_control( $args ) {
	$args = wp_parse_args(
		$args,
		array(
			'label'       => '',
			'group_label' => '',
			'options'     => array(),
			'active'      => '',
			'data_attr'   => '',
			'class'       => '',
		)
	);

	$label       = sanitize_text_field( $args['label'] );
	$group_label = sanitize_text_field( $args['group_label'] );
	$options     = is_array( $args['options'] ) ? $args['options'] : array();
	$active      = (string) $args['active'];
	$data_attr   = preg_replace( '/[^a-zA-Z0-9_-]/', '', (string) $args['data_attr'] );
	$class       = sanitize_html_class( (string) $args['class'] );
	$label_id    = 'uplianda-dashboard-control-' . wp_unique_id();

	if ( '' === $label || empty( $options ) || '' === $data_attr ) {
		return '';
	}

	ob_start();
	?>
	<div class="uplianda-dashboard-control <?php echo $class ? esc_attr( $class ) : ''; ?>">
		<span id="<?php echo esc_attr( $label_id ); ?>" class="uplianda-dashboard-control__label"><?php echo esc_html( $label ); ?></span>
		<div class="uplianda-dashboard-control__button-group" role="group" aria-labelledby="<?php echo esc_attr( $label_id ); ?>"<?php echo $group_label ? ' aria-label="' . esc_attr( $group_label ) . '"' : ''; ?>>
			<?php foreach ( $options as $value => $text ) : ?>
				<?php $is_active = (string) $value === $active; ?>
				<button
					class="uplianda-dashboard-control__button <?php echo $is_active ? 'active' : ''; ?>"
					type="button"
					<?php echo esc_attr( $data_attr ); ?>="<?php echo esc_attr( $value ); ?>"
					aria-pressed="<?php echo $is_active ? 'true' : 'false'; ?>"
				>
					<?php echo esc_html( $text ); ?>
				</button>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
	return trim( ob_get_clean() );
}

/**
 * Render the canonical Popular Content component used by widgets, blocks, and shortcodes.
 *
 * @param array $args Component arguments.
 * @return string
 */
function uplianda_render_popular_content_component( $args = array() ) {
	$options = uplianda_get_options();
	$args    = wp_parse_args(
		$args,
		array(
			'async'      => false,
			'date_range' => $options['popular_content_date_range'] ?? 30,
			'domain'     => $options['domain'] ?? '',
			'qty'        => 10,
			'title'      => $options['popular_content_title'] ?? __( 'Top Content', 'uplink-analytics-dashboard-for-cabin' ),
		)
	);

	$date_range = absint( $args['date_range'] );
	$qty        = absint( $args['qty'] );
	$title      = sanitize_text_field( $args['title'] );
	$domain     = sanitize_text_field( $args['domain'] );
	$is_async   = (bool) $args['async'];

	if ( ! in_array( $date_range, uplianda_get_allowed_popular_ranges(), true ) ) {
		$date_range = 30;
	}

	if ( ! in_array( $qty, uplianda_get_allowed_popular_quantities(), true ) ) {
		$qty = 10;
	}

	if ( '' === trim( $title ) ) {
		$title = __( 'Top Content', 'uplink-analytics-dashboard-for-cabin' );
	}

	$date_range_options = array();
	foreach ( uplianda_get_allowed_popular_ranges() as $range ) {
		$date_range_options[ $range ] = $range . 'd';
	}

	$quantity_options = array();
	foreach ( uplianda_get_allowed_popular_quantities() as $quantity ) {
		$quantity_options[ $quantity ] = (string) $quantity;
	}

	if ( $is_async ) {
		$body = '<p class="uplianda-popular-content__message">' . esc_html__( 'Loading popular content…', 'uplink-analytics-dashboard-for-cabin' ) . '</p>';
	} else {
		$items = uplianda_get_popular_content( $qty, $date_range );
		$body  = is_wp_error( $items ) ?
			'<p class="uplianda-popular-content__message">' . esc_html( $items->get_error_message() ) . '</p>' :
			uplianda_render_popular_content_dashboard_items( $items );
	}

	wp_enqueue_style( 'uplianda-style' );
	wp_enqueue_script( 'uplianda-popular-content-view' );

	ob_start();
	?>
	<div
		class="uplianda-popular-content-dashboard"
		data-uplianda-dashboard-popular-content
		data-date-range="<?php echo esc_attr( $date_range ); ?>"
		data-error-text="<?php esc_attr_e( 'Popular content could not be loaded.', 'uplink-analytics-dashboard-for-cabin' ); ?>"
		data-load-on-init="<?php echo $is_async ? 'true' : 'false'; ?>"
		data-loading-text="<?php esc_attr_e( 'Loading popular content…', 'uplink-analytics-dashboard-for-cabin' ); ?>"
		data-nonce="<?php echo is_user_logged_in() ? esc_attr( wp_create_nonce( 'wp_rest' ) ) : ''; ?>"
		data-qty="<?php echo esc_attr( $qty ); ?>"
		data-rest-url="<?php echo esc_url( rest_url( 'uplianda/v1/popular-content' ) ); ?>"
	>
		<div class="uplianda-popular-content-dashboard__header">
			<div class="uplianda-popular-content-dashboard__title-wrapper">
				<h2 class="uplianda-popular-content-dashboard__title"><?php echo esc_html( $title ); ?></h2>
				<?php if ( '' !== $domain ) : ?>
					<?php /* translators: %s: configured Cabin Analytics domain. */ ?>
					<div class="uplianda-popular-content-dashboard__domain" aria-label="<?php echo esc_attr( sprintf( __( 'Domain: %s', 'uplink-analytics-dashboard-for-cabin' ), $domain ) ); ?>"><?php echo esc_html( $domain ); ?></div>
				<?php endif; ?>
			</div>
			<div class="uplianda-popular-content-dashboard__controls">
				<?php
				echo wp_kses(
					uplianda_render_segmented_control(
						array(
							'label'       => __( 'Date range', 'uplink-analytics-dashboard-for-cabin' ),
							'group_label' => __( 'Select popular content date range', 'uplink-analytics-dashboard-for-cabin' ),
							'options'     => $date_range_options,
							'active'      => $date_range,
							'data_attr'   => 'data-uplianda-popular-range',
							'class'       => 'uplianda-dashboard-control--date-range',
						)
					),
					uplianda_get_segmented_control_allowed_html()
				);
				echo wp_kses(
					uplianda_render_segmented_control(
						array(
							'label'       => __( 'Records to show', 'uplink-analytics-dashboard-for-cabin' ),
							'group_label' => __( 'Select number of records to show', 'uplink-analytics-dashboard-for-cabin' ),
							'options'     => $quantity_options,
							'active'      => $qty,
							'data_attr'   => 'data-uplianda-popular-qty',
							'class'       => 'uplianda-dashboard-control--quantity',
						)
					),
					uplianda_get_segmented_control_allowed_html()
				);
				?>
			</div>
		</div>
		<div class="uplianda-popular-content-dashboard__body" aria-live="polite">
			<?php echo $body; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Generated by escaped component renderers above. ?>
		</div>
	</div>
	<?php
	return trim( ob_get_clean() );
}

/**
 * Render the standalone popular content dashboard widget.
 */
function uplianda_popular_content_widget_render() {
	$options    = uplianda_get_options();
	$domain     = isset( $options['domain'] ) ? trim( $options['domain'] ) : '';
	$date_range = isset( $options['popular_content_date_range'] ) && in_array( (string) $options['popular_content_date_range'], array( '1', '7', '14', '30', '90' ), true ) ? absint( $options['popular_content_date_range'] ) : 30;

	echo uplianda_render_popular_content_component( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by the shared component renderer.
		array(
			'async'      => true,
			'date_range' => $date_range,
			'domain'     => $domain,
			'qty'        => 10,
			'title'      => __( 'Uplink Popular Content for Cabin', 'uplink-analytics-dashboard-for-cabin' ),
		)
	);
}

/**
 * Normalize a Cabin path so WordPress can resolve it consistently.
 *
 * @param string $path Path returned by Cabin.
 * @return string
 */
function uplianda_normalize_path( $path ) {
	$path = is_string( $path ) ? trim( $path ) : '';

	if ( '' === $path ) {
		return '';
	}

	$parts = wp_parse_url( $path );

	if ( isset( $parts['path'] ) ) {
		$path = $parts['path'];
	}

	$path = '/' . ltrim( $path, '/' );
	$path = strtok( $path, '?' );
	$path = strtok( $path, '#' );

	return user_trailingslashit( $path );
}

/**
 * Resolve a Cabin path to a local public WordPress post.
 *
 * @param string $path Path returned by Cabin.
 * @return WP_Post|null
 */
function uplianda_resolve_path_to_post( $path ) {
	$path = uplianda_normalize_path( $path );

	if ( '' === $path ) {
		return null;
	}

	$post_id = 0;

	if ( '/' === $path ) {
		return null;
	}

	$post_id = url_to_postid( home_url( $path ) );

	if ( ! $post_id ) {
		return null;
	}

	$post = get_post( $post_id );

	if ( ! $post instanceof WP_Post ) {
		return null;
	}

	$public_post_types = get_post_types( array( 'public' => true ), 'names' );

	if ( ! in_array( $post->post_type, $public_post_types, true ) ) {
		return null;
	}

	if ( 'publish' !== get_post_status( $post ) ) {
		return null;
	}

	return $post;
}

/**
 * Create a readable fallback title for a Cabin path.
 *
 * @param string $path Normalized Cabin path.
 * @return string Readable title.
 */
function uplianda_get_path_title( $path ) {
	$path = uplianda_normalize_path( $path );

	if ( '/' === $path ) {
		return __( 'Home', 'uplink-analytics-dashboard-for-cabin' );
	}

	$segments = array_values( array_filter( explode( '/', trim( $path, '/' ) ) ) );
	$slug     = end( $segments );
	$title    = str_replace( array( '-', '_' ), ' ', rawurldecode( (string) $slug ) );

	return ucwords( sanitize_text_field( $title ) );
}

/**
 * Fetch popular pages from Cabin and reconcile them to local WP posts.
 *
 * @param int $qty Number of resolved posts to return.
 * @param int $date_range Number of days to look back.
 * @return array|WP_Error
 */
function uplianda_get_popular_content( $qty = 10, $date_range = 30 ) {
	$options = uplianda_get_options();
	$api_key = isset( $options['api_key'] ) ? trim( $options['api_key'] ) : '';
	$domain  = isset( $options['domain'] ) ? trim( $options['domain'] ) : '';

	if ( empty( $api_key ) ) {
		return new WP_Error( 'uplianda_no_api_key', __( 'Cabin Analytics API key not configured.', 'uplink-analytics-dashboard-for-cabin' ) );
	}

	if ( empty( $domain ) ) {
		return new WP_Error( 'uplianda_no_domain', __( 'Cabin Analytics domain not configured.', 'uplink-analytics-dashboard-for-cabin' ) );
	}

	$qty = absint( $qty );
	if ( ! in_array( $qty, uplianda_get_allowed_popular_quantities(), true ) ) {
		$qty = 10;
	}

	$date_range = absint( $date_range );
	if ( ! in_array( $date_range, uplianda_get_allowed_popular_ranges(), true ) ) {
		$date_range = 30;
	}

	$domain = trim( str_replace( array( 'http://', 'https://', 'www.' ), '', $domain ), '/' );

	$today     = new DateTimeImmutable( 'today', wp_timezone() );
	$date_to   = $today->format( 'Y-m-d' );
	$date_from = $today->modify( '-' . $date_range . ' days' )->format( 'Y-m-d' );

	$limit = min( max( $qty * 4, $qty ), 250 );

	$cache_key = 'uplianda_popular_content_' . md5( 'fallback-v2|' . $domain . '|' . $date_from . '|' . $date_to . '|' . $qty . '|' . $limit );
	$cached    = get_transient( $cache_key );

	if ( false !== $cached ) {
		return $cached;
	}

	$url = add_query_arg(
		array(
			'domain'      => $domain,
			'date_from'   => $date_from,
			'date_to'     => $date_to,
			'scope'       => 'pages',
			'limit_lists' => $limit,
		),
		'https://api.withcabin.com/v1/analytics'
	);

	$response = wp_remote_get(
		$url,
		array(
			'headers'    => array(
				'x-api-key' => $api_key,
			),
			'timeout'    => 15,
			'user-agent' => 'WordPress Uplink Analytics Dashboard for Cabin/' . UPLIANDA_VERSION,
		)
	);

	if ( is_wp_error( $response ) ) {
		return $response;
	}

	$response_code = wp_remote_retrieve_response_code( $response );
	$body          = wp_remote_retrieve_body( $response );

	if ( 200 !== $response_code ) {
		return new WP_Error(
			'uplianda_api_error',
			sprintf(
				/* translators: %d: HTTP status code */
				__( 'Cabin Analytics API returned HTTP %d.', 'uplink-analytics-dashboard-for-cabin' ),
				$response_code
			)
		);
	}

	$data = json_decode( $body, true );

	if ( JSON_ERROR_NONE !== json_last_error() || empty( $data['pages'] ) || ! is_array( $data['pages'] ) ) {
		return new WP_Error( 'uplianda_no_pages', __( 'No page data was returned by Cabin Analytics.', 'uplink-analytics-dashboard-for-cabin' ) );
	}

	$items = array();
	$seen  = array();

	foreach ( $data['pages'] as $page ) {
		if ( empty( $page['path'] ) ) {
			continue;
		}

		$path = uplianda_normalize_path( $page['path'] );
		$post = uplianda_resolve_path_to_post( $path );
		$key  = $post ? 'post:' . $post->ID : 'path:' . $path;

		if ( '' === $path || isset( $seen[ $key ] ) ) {
			continue;
		}

		$seen[ $key ] = true;

		$items[] = array(
			'id'                       => $post ? $post->ID : 0,
			'title'                    => $post ? get_the_title( $post ) : uplianda_get_path_title( $path ),
			'url'                      => $post ? get_permalink( $post ) : esc_url_raw( 'https://' . $domain . $path ),
			'post_type'                => $post ? get_post_type( $post ) : 'external',
			'path'                     => $path,
			'page_views'               => isset( $page['page_views'] ) ? absint( $page['page_views'] ) : 0,
			'unique_visitors'          => isset( $page['unique_visitors'] ) ? absint( $page['unique_visitors'] ) : 0,
			'average_duration_seconds' => isset( $page['average_duration_seconds'] ) ? (float) $page['average_duration_seconds'] : 0,
			'co2_grams'                => isset( $page['co2_grams'] ) ? (float) $page['co2_grams'] : 0,
		);

		if ( count( $items ) >= $qty ) {
			break;
		}
	}

	set_transient( $cache_key, $items, 30 * MINUTE_IN_SECONDS );

	return $items;
}

/**
 * Render popular content markup.
 *
 * @param array $args Render arguments.
 * @return string
 */
function uplianda_render_popular_content( $args = array() ) {
	$options            = uplianda_get_options();
	$default_title      = isset( $options['popular_content_title'] ) && '' !== trim( $options['popular_content_title'] ) ? $options['popular_content_title'] : __( 'Top Content', 'uplink-analytics-dashboard-for-cabin' );
	$default_date_range = isset( $options['popular_content_date_range'] ) && in_array( (string) $options['popular_content_date_range'], array( '1', '7', '14', '30', '90' ), true ) ? $options['popular_content_date_range'] : '30';

	$args = wp_parse_args(
		$args,
		array(
			'qty'        => 10,
			'date_range' => $default_date_range,
			'title'      => $default_title,
		)
	);

	$qty        = absint( $args['qty'] );
	$date_range = absint( $args['date_range'] );
	$title      = sanitize_text_field( $args['title'] );

	$qty = $qty > 0 ? min( $qty, 50 ) : 10;

	if ( ! in_array( $date_range, uplianda_get_allowed_popular_ranges(), true ) ) {
		$date_range = absint( $default_date_range );
	}

	if ( '' === trim( $title ) ) {
		$title = $default_title;
	}

	return uplianda_render_popular_content_component(
		array(
			'date_range' => $date_range,
			'qty'        => $qty,
			'title'      => $title,
		)
	);
}

/**
 * Render Cabin Analytics chart shortcode.
 *
 * Usage: [uplianda domain="example.com" chart_type="line" date_range="30" allow_switching="true"]
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function uplianda_chart_shortcode( $atts ) {
	$options = uplianda_get_options();

	$atts = shortcode_atts(
		array(
			'title'           => __( 'Site Traffic', 'uplink-analytics-dashboard-for-cabin' ),
			'domain'          => isset( $options['domain'] ) ? $options['domain'] : '',
			'chart_type'      => isset( $options['chart_type'] ) ? $options['chart_type'] : 'bar',
			'date_range'      => isset( $options['date_range'] ) ? $options['date_range'] : '7',
			'allow_switching' => 'true',
			'dashboard_url'   => '',
		),
		$atts,
		'uplianda'
	);

	$title           = sanitize_text_field( $atts['title'] );
	$title           = '' !== $title ? $title : __( 'Site Traffic', 'uplink-analytics-dashboard-for-cabin' );
	$domain          = sanitize_text_field( $atts['domain'] );
	$chart_type      = in_array( $atts['chart_type'], array( 'bar', 'line' ), true ) ? $atts['chart_type'] : 'bar';
	$date_range      = in_array( (string) $atts['date_range'], array( '7', '14', '30', '90' ), true ) ? (string) $atts['date_range'] : '7';
	$allow_switching = filter_var( $atts['allow_switching'], FILTER_VALIDATE_BOOLEAN ) ? 'true' : 'false';
	$dashboard_url   = '' !== $atts['dashboard_url'] ? esc_url_raw( $atts['dashboard_url'] ) : '';

	if ( '' === $domain ) {
		return '<p class="uplianda-analytics-error">' . esc_html__( 'Cabin Analytics domain is not configured.', 'uplink-analytics-dashboard-for-cabin' ) . '</p>';
	}

	if ( '' === $dashboard_url ) {
		$dashboard_url = UPLIANDA_CABIN_URL;
	}

	wp_enqueue_script( 'uplianda-view' );
	wp_enqueue_style( 'uplianda-style' );

	return sprintf(
		'<div class="uplianda-analytics-widget" data-title="%s" data-domain="%s" data-chart-type="%s" data-date-range="%s" data-allow-switching="%s" data-dashboard-url="%s" data-rest-url="%s"></div>',
		esc_attr( $title ),
		esc_attr( $domain ),
		esc_attr( $chart_type ),
		esc_attr( $date_range ),
		esc_attr( $allow_switching ),
		esc_url( $dashboard_url ),
		esc_url( rest_url( 'uplianda/v1/stats' ) )
	);
}
add_shortcode( 'uplianda', 'uplianda_chart_shortcode' );

/**
 * Render popular content shortcode.
 *
 * Usage: [uplianda_popular_content qty="10" date_range="30" title="Top Content"]
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function uplianda_popular_content_shortcode( $atts ) {
	$options            = uplianda_get_options();
	$default_title      = isset( $options['popular_content_title'] ) && '' !== trim( $options['popular_content_title'] ) ? $options['popular_content_title'] : __( 'Top Content', 'uplink-analytics-dashboard-for-cabin' );
	$default_date_range = isset( $options['popular_content_date_range'] ) && in_array( (string) $options['popular_content_date_range'], array( '1', '7', '14', '30', '90' ), true ) ? $options['popular_content_date_range'] : '30';

	$atts = shortcode_atts(
		array(
			'qty'        => 10,
			'date_range' => $default_date_range,
			'title'      => $default_title,
		),
		$atts,
		'uplianda_popular_content'
	);

	return uplianda_render_popular_content(
		array(
			'qty'        => $atts['qty'],
			'date_range' => $atts['date_range'],
			'title'      => $atts['title'],
		)
	);
}
add_shortcode( 'uplianda_popular_content', 'uplianda_popular_content_shortcode' );

/**
 * Render a public insight shortcode.
 *
 * @param array  $atts      Shortcode attributes.
 * @param string $content   Enclosed shortcode content (unused).
 * @param string $shortcode Shortcode tag.
 * @return string Insight markup.
 */
function uplianda_insight_shortcode( $atts, $content = null, $shortcode = '' ) {
	$section_map = array(
		'uplianda_sustainability' => 'sustainability',
		'uplianda_demographics'   => 'demographics',
		'uplianda_sources'        => 'sources',
	);
	$defaults = array(
		'title'           => '',
		'date_range'      => '30',
		'total_co2'       => 'true',
		'average_co2'     => 'true',
		'transfer_volume' => 'true',
		'green_hosting'   => 'true',
		'devices'         => 'true',
		'countries'       => 'true',
		'languages'       => 'true',
		'browsers'        => 'true',
		'traffic_sources' => 'true',
		'referrers'       => 'true',
	);
	$section  = isset( $section_map[ $shortcode ] ) ? $section_map[ $shortcode ] : '';
	$atts     = shortcode_atts( $defaults, $atts, $shortcode );
	$modules  = array();

	foreach ( $atts as $key => $value ) {
		if ( ! in_array( $key, array( 'title', 'date_range' ), true ) ) {
			$modules[ $key ] = filter_var( $value, FILTER_VALIDATE_BOOLEAN );
		}
	}

	return uplianda_render_public_insight( $section, $atts['date_range'], $modules, $atts['title'] );
}
add_shortcode( 'uplianda_sustainability', 'uplianda_insight_shortcode' );
add_shortcode( 'uplianda_demographics', 'uplianda_insight_shortcode' );
add_shortcode( 'uplianda_sources', 'uplianda_insight_shortcode' );

/**
 * Render the country traffic map shortcode.
 *
 * @param array $atts Shortcode attributes.
 * @return string Map markup.
 */
function uplianda_country_map_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'title'      => __( 'Country Traffic', 'uplink-analytics-dashboard-for-cabin' ),
			'date_range' => '30',
		),
		$atts,
		'uplianda_country_map'
	);

	return uplianda_render_public_country_map( $atts['date_range'], $atts['title'] );
}
add_shortcode( 'uplianda_country_map', 'uplianda_country_map_shortcode' );

/**
 * Render callback for a public insight block.
 *
 * @param string $section    Insight section slug.
 * @param array  $attributes Block attributes.
 * @return string Insight markup.
 */
function uplianda_render_insight_block( $section, $attributes ) {
	$date_range = isset( $attributes['dateRange'] ) ? $attributes['dateRange'] : '30';
	$title      = isset( $attributes['title'] ) ? $attributes['title'] : '';
	$attribute_maps = array(
		'sustainability' => array(
			'total_co2'       => 'showTotalCo2',
			'average_co2'     => 'showAverageCo2',
			'transfer_volume' => 'showTransferVolume',
			'green_hosting'   => 'showGreenHosting',
		),
		'demographics'   => array(
			'devices'   => 'showDevices',
			'countries' => 'showCountries',
			'languages' => 'showLanguages',
			'browsers'  => 'showBrowsers',
		),
		'sources'        => array(
			'traffic_sources' => 'showTrafficSources',
			'referrers'       => 'showReferrers',
		),
	);
	$modules = array();

	foreach ( $attribute_maps[ $section ] ?? array() as $module_key => $attribute_key ) {
		$modules[ $module_key ] = ! isset( $attributes[ $attribute_key ] ) || (bool) $attributes[ $attribute_key ];
	}

	return sprintf(
		'<div %1$s>%2$s</div>',
		get_block_wrapper_attributes(),
		uplianda_render_public_insight( $section, $date_range, $modules, $title )
	);
}

/**
 * Render callback for the Country Traffic Map block.
 *
 * @param array $attributes Block attributes.
 * @return string Map markup.
 */
function uplianda_render_country_map_block( $attributes ) {
	$date_range = isset( $attributes['dateRange'] ) ? $attributes['dateRange'] : '30';
	$title      = isset( $attributes['title'] ) ? $attributes['title'] : '';

	return sprintf(
		'<div %1$s>%2$s</div>',
		get_block_wrapper_attributes(),
		uplianda_render_public_country_map( $date_range, $title )
	);
}

/**
 * Render callback for the Uplink Popular Content for Cabin block.
 *
 * @param array $attributes Block attributes.
 * @return string
 */
function uplianda_popular_content_block_render( $attributes ) {
	return sprintf(
		'<div %1$s>%2$s</div>',
		get_block_wrapper_attributes(),
		uplianda_render_popular_content(
			array(
				'qty'        => isset( $attributes['qty'] ) ? $attributes['qty'] : 10,
				'date_range' => isset( $attributes['dateRange'] ) ? $attributes['dateRange'] : '',
				'title'      => isset( $attributes['title'] ) ? $attributes['title'] : '',
			)
		)
	);
}
