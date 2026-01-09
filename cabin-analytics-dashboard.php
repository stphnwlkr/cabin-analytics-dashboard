<?php
/**
 * Plugin Name:       Cabin Analytics Dashboard
 * Plugin URI:        https://flyingw.press
 * Description:       Display Cabin Analytics data with interactive charts via dashboard widgets, blocks, and shortcodes.
 * Version:           1.0.1
 * Requires at least: 6.8.3
 * Requires PHP:      8.1
 * Author:            Stephen Walker
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       cabin-analytics-dashboard
 *
 * @package CabinAnalyticsDashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CABIN_ANALYTICS_VERSION', '0.1.0' );
define( 'CABIN_ANALYTICS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CABIN_ANALYTICS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 */
function cabin_analytics_dashboard_block_init() {
	register_block_type( CABIN_ANALYTICS_PLUGIN_DIR . 'build/' );
}
add_action( 'init', 'cabin_analytics_dashboard_block_init' );

/**
 * Register admin settings page
 */
function cabin_analytics_dashboard_add_admin_menu() {
	add_options_page(
		__( 'Cabin Analytics Settings', 'cabin-analytics-dashboard' ),
		__( 'Cabin Analytics', 'cabin-analytics-dashboard' ),
		'manage_options',
		'cabin-analytics-dashboard',
		'cabin_analytics_dashboard_options_page'
	);
}
add_action( 'admin_menu', 'cabin_analytics_dashboard_add_admin_menu' );

/**
 * Enqueue admin styles
 */
function cabin_analytics_dashboard_admin_styles( $hook ) {
	if ( 'settings_page_cabin-analytics-dashboard' !== $hook ) {
		return;
	}
	
	$asset_file = include( CABIN_ANALYTICS_PLUGIN_DIR . 'build/index.asset.php' );
	
	wp_enqueue_style(
		'cabin-analytics-admin',
		CABIN_ANALYTICS_PLUGIN_URL . 'build/admin.css',
		array(),
		$asset_file['version']
	);
}
add_action( 'admin_enqueue_scripts', 'cabin_analytics_dashboard_admin_styles' );

/**
 * Register settings
 */
function cabin_analytics_dashboard_settings_init() {
	register_setting( 
		'cabin_analytics_dashboard', 
		'cabin_analytics_dashboard_options',
		array(
			'sanitize_callback' => 'cabin_analytics_dashboard_sanitize_options',
			'default' => array(
				'api_key' => '',
				'domain' => '',
				'chart_type' => 'bar',
				'date_range' => '7',
				'dashboard_url' => '',
			),
		)
	);

	add_settings_section(
		'cabin_analytics_dashboard_section',
		__( 'Cabin Analytics Configuration', 'cabin-analytics-dashboard' ),
		'cabin_analytics_dashboard_section_callback',
		'cabin_analytics_dashboard'
	);

	add_settings_field(
		'cabin_analytics_api_key',
		__( 'API Key', 'cabin-analytics-dashboard' ),
		'cabin_analytics_api_key_render',
		'cabin_analytics_dashboard',
		'cabin_analytics_dashboard_section'
	);

	add_settings_field(
		'cabin_analytics_domain',
		__( 'Default Domain', 'cabin-analytics-dashboard' ),
		'cabin_analytics_domain_render',
		'cabin_analytics_dashboard',
		'cabin_analytics_dashboard_section'
	);

	add_settings_field(
		'cabin_analytics_chart_type',
		__( 'Default Chart Type', 'cabin-analytics-dashboard' ),
		'cabin_analytics_chart_type_render',
		'cabin_analytics_dashboard',
		'cabin_analytics_dashboard_section'
	);

	add_settings_field(
		'cabin_analytics_date_range',
		__( 'Default Date Range', 'cabin-analytics-dashboard' ),
		'cabin_analytics_date_range_render',
		'cabin_analytics_dashboard',
		'cabin_analytics_dashboard_section'
	);

	add_settings_field(
		'cabin_analytics_dashboard_url',
		__( 'Default Dashboard URL', 'cabin-analytics-dashboard' ),
		'cabin_analytics_dashboard_url_render',
		'cabin_analytics_dashboard',
		'cabin_analytics_dashboard_section'
	);
}
add_action( 'admin_init', 'cabin_analytics_dashboard_settings_init' );

/**
 * Sanitize options callback
 */
function cabin_analytics_dashboard_sanitize_options( $input ) {
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
	
	return $sanitized;
}

/**
 * Render API key field
 */
function cabin_analytics_api_key_render() {
	$options = get_option( 'cabin_analytics_dashboard_options' );
	$api_key = isset( $options['api_key'] ) ? $options['api_key'] : '';
	?>
	<div class="cabin-field-wrapper">
		<input type="text" name="cabin_analytics_dashboard_options[api_key]" value="<?php echo esc_attr( $api_key ); ?>" class="cabin-input" placeholder="sk_live_..." autocomplete="off" />
		<p class="cabin-description">
			<svg class="cabin-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
				<circle cx="12" cy="12" r="10"/>
				<path d="M12 16v-4M12 8h.01"/>
			</svg>
			<?php esc_html_e( 'Enter your Cabin Analytics API key from withcabin.com', 'cabin-analytics-dashboard' ); ?>
		</p>
	</div>
	<?php
}

/**
 * Render domain field
 */
function cabin_analytics_domain_render() {
	$options = get_option( 'cabin_analytics_dashboard_options' );
	$domain = isset( $options['domain'] ) ? $options['domain'] : '';
	?>
	<div class="cabin-field-wrapper">
		<input type="text" name="cabin_analytics_dashboard_options[domain]" value="<?php echo esc_attr( $domain ); ?>" class="cabin-input" placeholder="example.com" />
		<p class="cabin-description">
			<svg class="cabin-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
				<circle cx="12" cy="12" r="10"/>
				<path d="M12 16v-4M12 8h.01"/>
			</svg>
			<?php esc_html_e( 'Your default domain to track (without https://)', 'cabin-analytics-dashboard' ); ?>
		</p>
	</div>
	<?php
}

/**
 * Render chart type field
 */
function cabin_analytics_chart_type_render() {
	$options = get_option( 'cabin_analytics_dashboard_options' );
	$chart_type = isset( $options['chart_type'] ) ? $options['chart_type'] : 'bar';
	?>
	<div class="cabin-field-wrapper">
		<select name="cabin_analytics_dashboard_options[chart_type]" class="cabin-select">
			<option value="bar" <?php selected( $chart_type, 'bar' ); ?>><?php esc_html_e( 'Stacked Bar Chart', 'cabin-analytics-dashboard' ); ?></option>
			<option value="line" <?php selected( $chart_type, 'line' ); ?>><?php esc_html_e( 'Line Chart', 'cabin-analytics-dashboard' ); ?></option>
		</select>
	</div>
	<?php
}

/**
 * Render date range field
 */
function cabin_analytics_date_range_render() {
	$options = get_option( 'cabin_analytics_dashboard_options' );
	$date_range = isset( $options['date_range'] ) ? $options['date_range'] : '7';
	?>
	<div class="cabin-field-wrapper">
		<select name="cabin_analytics_dashboard_options[date_range]" class="cabin-select">
			<option value="7" <?php selected( $date_range, '7' ); ?>><?php esc_html_e( 'Last 7 Days', 'cabin-analytics-dashboard' ); ?></option>
			<option value="14" <?php selected( $date_range, '14' ); ?>><?php esc_html_e( 'Last 14 Days', 'cabin-analytics-dashboard' ); ?></option>
			<option value="30" <?php selected( $date_range, '30' ); ?>><?php esc_html_e( 'Last 30 Days', 'cabin-analytics-dashboard' ); ?></option>
			<option value="90" <?php selected( $date_range, '90' ); ?>><?php esc_html_e( 'Last 90 Days', 'cabin-analytics-dashboard' ); ?></option>
		</select>
	</div>
	<?php
}

/**
 * Render dashboard URL field
 */
function cabin_analytics_dashboard_url_render() {
	$options = get_option( 'cabin_analytics_dashboard_options' );
	$dashboard_url = isset( $options['dashboard_url'] ) ? $options['dashboard_url'] : '';
	?>
	<div class="cabin-field-wrapper">
		<input type="url" name="cabin_analytics_dashboard_options[dashboard_url]" value="<?php echo esc_attr( $dashboard_url ); ?>" class="cabin-input" placeholder="https://withcabin.com/dashboard/example.com" />
		<p class="cabin-description">
			<svg class="cabin-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
				<circle cx="12" cy="12" r="10"/>
				<path d="M12 16v-4M12 8h.01"/>
			</svg>
			<?php esc_html_e( 'Custom URL for the "Go to Dashboard" link. Leave empty for default: https://withcabin.com/dashboard/{domain}', 'cabin-analytics-dashboard' ); ?>
		</p>
	</div>
	<?php
}

/**
 * Section callback
 */
function cabin_analytics_dashboard_section_callback() {
	echo '<p class="cabin-section-description">' . esc_html__( 'Configure your Cabin Analytics settings. These will be used as defaults for the dashboard widget, block, and shortcode.', 'cabin-analytics-dashboard' ) . '</p>';
}

/**
 * Options page
 */
function cabin_analytics_dashboard_options_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	
	if ( isset( $_GET['settings-updated'] ) ) {
		add_settings_error(
			'cabin_analytics_dashboard_messages',
			'cabin_analytics_dashboard_message',
			__( 'Settings saved successfully.', 'cabin-analytics-dashboard' ),
			'success'
		);
	}
	
	settings_errors( 'cabin_analytics_dashboard_messages' );
	?>
	<div class="cabin-admin-wrap">
		<div class="cabin-admin-header">
			<div class="cabin-header-content">
				<svg class="cabin-logo" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
					<rect x="4" y="12" width="4" height="8"/>
					<rect x="10" y="8" width="4" height="12"/>
					<rect x="16" y="4" width="4" height="16"/>
				</svg>
				<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			</div>
			<p class="cabin-tagline"><?php esc_html_e( 'Privacy-first analytics for WordPress', 'cabin-analytics-dashboard' ); ?></p>
		</div>

		<div class="cabin-admin-content">
			<div class="cabin-admin-main">
				<form action="options.php" method="post" class="cabin-form">
					<?php
					settings_fields( 'cabin_analytics_dashboard' );
					do_settings_sections( 'cabin_analytics_dashboard' );
					?>
					<div class="cabin-form-actions">
						<?php submit_button( __( 'Save Settings', 'cabin-analytics-dashboard' ), 'primary', 'submit', false ); ?>
					</div>
				</form>
			</div>

			<div class="cabin-admin-sidebar">
				<div class="cabin-card">
					<h3 class="cabin-card-title">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
							<path d="M12 2L2 7l10 5 10-5-10-5z"/>
							<path d="M2 17l10 5 10-5M2 12l10 5 10-5"/>
						</svg>
						<?php esc_html_e( 'Usage', 'cabin-analytics-dashboard' ); ?>
					</h3>
					<div class="cabin-card-content">
						<h4><?php esc_html_e( 'Dashboard Widget', 'cabin-analytics-dashboard' ); ?></h4>
						<p><?php esc_html_e( 'Automatically appears on your WordPress dashboard after configuration.', 'cabin-analytics-dashboard' ); ?></p>
						
						<h4><?php esc_html_e( 'Block Editor', 'cabin-analytics-dashboard' ); ?></h4>
						<p><?php esc_html_e( 'Search for "Cabin Analytics" in the block inserter to add analytics to any post or page.', 'cabin-analytics-dashboard' ); ?></p>
						
						<h4><?php esc_html_e( 'Shortcode', 'cabin-analytics-dashboard' ); ?></h4>
						<code class="cabin-code">[cabin_analytics]</code>
						<p><?php esc_html_e( 'With parameters:', 'cabin-analytics-dashboard' ); ?></p>
						<code class="cabin-code">[cabin_analytics domain="example.com" chart_type="line" date_range="30"]</code>
					</div>
				</div>

				<div class="cabin-card">
					<h3 class="cabin-card-title">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
							<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
							<polyline points="15 3 21 3 21 9"/>
							<line x1="10" y1="14" x2="21" y2="3"/>
						</svg>
						<?php esc_html_e( 'Resources', 'cabin-analytics-dashboard' ); ?>
					</h3>
					<div class="cabin-card-content">
						<ul class="cabin-links">
							<li><a href="https://withcabin.com" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Cabin Analytics Website', 'cabin-analytics-dashboard' ); ?></a></li>
							<li><a href="https://docs.withcabin.com" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Documentation', 'cabin-analytics-dashboard' ); ?></a></li>
							<li><a href="https://docs.withcabin.com/api" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'API Reference', 'cabin-analytics-dashboard' ); ?></a></li>
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
function cabin_analytics_dashboard_register_rest_routes() {
	register_rest_route( 'cabin-analytics/v1', '/stats', array(
		'methods' => 'GET',
		'callback' => 'cabin_analytics_dashboard_get_stats',
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
				'validate_callback' => 'cabin_analytics_validate_date',
			),
			'end_date' => array(
				'required' => true,
				'type' => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'cabin_analytics_validate_date',
			),
		),
	) );
}
add_action( 'rest_api_init', 'cabin_analytics_dashboard_register_rest_routes' );

/**
 * Validate date format
 */
function cabin_analytics_validate_date( $value, $request, $param ) {
	return (bool) preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value );
}

/**
 * Get stats from Cabin API
 */
function cabin_analytics_dashboard_get_stats( $request ) {
	$options = get_option( 'cabin_analytics_dashboard_options' );
	$api_key = isset( $options['api_key'] ) ? trim( $options['api_key'] ) : '';
	
	if ( empty( $api_key ) ) {
		return new WP_Error( 'no_api_key', __( 'Cabin Analytics API key not configured', 'cabin-analytics-dashboard' ), array( 'status' => 400 ) );
	}

	$domain = $request->get_param( 'domain' );
	if ( empty( $domain ) ) {
		$domain = isset( $options['domain'] ) ? trim( $options['domain'] ) : '';
	}

	if ( empty( $domain ) ) {
		return new WP_Error( 'no_domain', __( 'Domain not specified', 'cabin-analytics-dashboard' ), array( 'status' => 400 ) );
	}

	$domain = trim( str_replace( array( 'http://', 'https://', 'www.' ), '', $domain ), '/' );

	$start_date = $request->get_param( 'start_date' );
	$end_date = $request->get_param( 'end_date' );

	$cache_key = 'cabin_analytics_' . md5( $domain . $start_date . $end_date );
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
			'user-agent' => 'WordPress Cabin Analytics Dashboard/' . CABIN_ANALYTICS_VERSION,
		)
	);

	if ( is_wp_error( $response ) ) {
		return new WP_Error(
			'api_error',
			sprintf(
				/* translators: %s: Error message */
				__( 'Failed to connect to Cabin Analytics: %s', 'cabin-analytics-dashboard' ),
				$response->get_error_message()
			),
			array( 'status' => 500 )
		);
	}

	$response_code = wp_remote_retrieve_response_code( $response );
	$body = wp_remote_retrieve_body( $response );

	if ( $response_code !== 200 ) {
		$error_data = json_decode( $body, true );
		$error_message = isset( $error_data['message'] ) ? $error_data['message'] : __( 'Unknown error from Cabin Analytics API', 'cabin-analytics-dashboard' );
		
		return new WP_Error(
			'api_error',
			sprintf(
				/* translators: 1: HTTP status code 2: Error message */
				__( 'Cabin Analytics API error (HTTP %1$d): %2$s', 'cabin-analytics-dashboard' ),
				$response_code,
				$error_message
			),
			array( 'status' => $response_code )
		);
	}

	$data = json_decode( $body, true );

	if ( json_last_error() !== JSON_ERROR_NONE ) {
		return new WP_Error(
			'json_error',
			__( 'Failed to parse response from Cabin Analytics', 'cabin-analytics-dashboard' ),
			array( 'status' => 500 )
		);
	}

	set_transient( $cache_key, $data, 5 * MINUTE_IN_SECONDS );

	return rest_ensure_response( $data );
}

/**
 * Enqueue dashboard widget assets
 */
function cabin_analytics_dashboard_enqueue_assets() {
	$asset_file_path = CABIN_ANALYTICS_PLUGIN_DIR . 'build/view.asset.php';
	
	if ( ! file_exists( $asset_file_path ) ) {
		return;
	}
	
	$asset_file = include( $asset_file_path );
	
	wp_register_script(
		'cabin-analytics-dashboard-view',
		CABIN_ANALYTICS_PLUGIN_URL . 'build/view.js',
		$asset_file['dependencies'],
		$asset_file['version'],
		true
	);
	
	wp_register_style(
		'cabin-analytics-dashboard-style',
		CABIN_ANALYTICS_PLUGIN_URL . 'build/style-index.css',
		array(),
		$asset_file['version']
	);
}
add_action( 'admin_enqueue_scripts', 'cabin_analytics_dashboard_enqueue_assets' );
add_action( 'wp_enqueue_scripts', 'cabin_analytics_dashboard_enqueue_assets' );

/**
 * Register dashboard widget
 */
function cabin_analytics_dashboard_add_widget() {
	$options = get_option( 'cabin_analytics_dashboard_options' );
	$api_key = isset( $options['api_key'] ) ? $options['api_key'] : '';
	
	if ( empty( $api_key ) ) {
		return;
	}

	wp_add_dashboard_widget(
		'cabin_analytics_dashboard_widget',
		__( 'Cabin Analytics Dashboard', 'cabin-analytics-dashboard' ),
		'cabin_analytics_dashboard_widget_render'
	);
}
add_action( 'wp_dashboard_setup', 'cabin_analytics_dashboard_add_widget' );

/**
 * Render dashboard widget
 */
function cabin_analytics_dashboard_widget_render() {
	$options = get_option( 'cabin_analytics_dashboard_options' );
	$domain = isset( $options['domain'] ) ? $options['domain'] : '';
	$chart_type = isset( $options['chart_type'] ) ? $options['chart_type'] : 'bar';
	$date_range = isset( $options['date_range'] ) ? $options['date_range'] : '7';
	$dashboard_url = isset( $options['dashboard_url'] ) && ! empty( $options['dashboard_url'] ) ? $options['dashboard_url'] : 'https://withcabin.com/dashboard/' . $domain;

	wp_enqueue_script( 'cabin-analytics-dashboard-view' );
	wp_enqueue_style( 'cabin-analytics-dashboard-style' );

	printf(
		'<div class="cabin-analytics-widget" data-domain="%s" data-chart-type="%s" data-date-range="%s" data-allow-switching="true" data-dashboard-url="%s"></div>',
		esc_attr( $domain ),
		esc_attr( $chart_type ),
		esc_attr( $date_range ),
		esc_url( $dashboard_url )
	);
}

/**
 * Register shortcode
 */
function cabin_analytics_dashboard_shortcode( $atts ) {
	$options = get_option( 'cabin_analytics_dashboard_options' );
	
	$atts = shortcode_atts( array(
		'domain' => isset( $options['domain'] ) ? $options['domain'] : '',
		'chart_type' => isset( $options['chart_type'] ) ? $options['chart_type'] : 'bar',
		'date_range' => isset( $options['date_range'] ) ? $options['date_range'] : '7',
		'allow_switching' => 'true',
		'dashboard_url' => isset( $options['dashboard_url'] ) && ! empty( $options['dashboard_url'] ) ? $options['dashboard_url'] : '',
	), $atts, 'cabin_analytics' );

	if ( empty( $atts['dashboard_url'] ) ) {
		$atts['dashboard_url'] = 'https://withcabin.com/dashboard/' . $atts['domain'];
	}

	wp_enqueue_script( 'cabin-analytics-dashboard-view' );
	wp_enqueue_style( 'cabin-analytics-dashboard-style' );

	return sprintf(
		'<div class="cabin-analytics-widget" data-domain="%s" data-chart-type="%s" data-date-range="%s" data-allow-switching="%s" data-dashboard-url="%s"></div>',
		esc_attr( $atts['domain'] ),
		esc_attr( $atts['chart_type'] ),
		esc_attr( $atts['date_range'] ),
		esc_attr( $atts['allow_switching'] ),
		esc_url( $atts['dashboard_url'] )
	);
}
add_shortcode( 'cabin_analytics', 'cabin_analytics_dashboard_shortcode' );