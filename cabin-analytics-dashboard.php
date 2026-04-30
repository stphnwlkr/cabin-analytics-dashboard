<?php
/**
 * Plugin Name:       Cabin Analytics Dashboard
 * Plugin URI:        https://flyingw.press
 * Description:       Display Cabin Analytics data with interactive charts via dashboard widgets, blocks, and shortcodes.
 * Version:           2.0.1
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

define( 'CABIN_ANALYTICS_VERSION', '2.0.1' );
define( 'CABIN_ANALYTICS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CABIN_ANALYTICS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 */
function cabin_analytics_dashboard_block_init() {
	register_block_type( CABIN_ANALYTICS_PLUGIN_DIR . 'build/' );

	$popular_content_asset_path = CABIN_ANALYTICS_PLUGIN_DIR . 'build/popular-content.asset.php';
	$popular_content_asset      = file_exists( $popular_content_asset_path ) ? include $popular_content_asset_path : array(
		'dependencies' => array( 'wp-blocks', 'wp-element', 'wp-i18n', 'wp-block-editor', 'wp-components' ),
		'version'      => CABIN_ANALYTICS_VERSION,
	);

	wp_register_script(
		'cabin-popular-content-editor',
		CABIN_ANALYTICS_PLUGIN_URL . 'build/popular-content.js',
		$popular_content_asset['dependencies'],
		$popular_content_asset['version'],
		true
	);

	register_block_type(
		'cabin/popular-content',
		array(
			'api_version'     => 3,
			'title'           => __( 'Cabin Popular Content', 'cabin-analytics-dashboard' ),
			'category'        => 'widgets',
			'icon'            => 'editor-ol',
			'description'     => __( 'Display popular local content from Cabin Analytics.', 'cabin-analytics-dashboard' ),
			'editor_script'   => 'cabin-popular-content-editor',
			'render_callback' => 'cabin_analytics_dashboard_popular_content_block_render',
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
				'html'  => false,
				'align' => true,
			),
		)
	);
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
	if ( 'settings_page_cabin-analytics-dashboard' !== $hook && 'index.php' !== $hook ) {
		return;
	}
	
	$asset_file = include( CABIN_ANALYTICS_PLUGIN_DIR . 'build/index.asset.php' );
	
	wp_enqueue_style(
		'cabin-analytics-admin',
		CABIN_ANALYTICS_PLUGIN_URL . 'build/admin.css',
		array(),
		$asset_file['version']
	);

	if ( 'index.php' === $hook ) {
		$view_asset_file = CABIN_ANALYTICS_PLUGIN_DIR . 'build/view.asset.php';
		$view_asset      = file_exists( $view_asset_file ) ? include $view_asset_file : array( 'version' => CABIN_ANALYTICS_VERSION );

		wp_enqueue_style(
			'cabin-analytics-dashboard-style',
			CABIN_ANALYTICS_PLUGIN_URL . 'build/style-index.css',
			array(),
			isset( $view_asset['version'] ) ? $view_asset['version'] : CABIN_ANALYTICS_VERSION
		);
	}
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
				'popular_content_title' => 'Top Content',
				'popular_content_date_range' => '30',
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

	add_settings_field(
		'cabin_analytics_popular_content_date_range',
		__( 'Popular Content Default Date Range', 'cabin-analytics-dashboard' ),
		'cabin_analytics_popular_content_date_range_render',
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

	if ( isset( $input['popular_content_title'] ) ) {
		$sanitized['popular_content_title'] = sanitize_text_field( $input['popular_content_title'] );
	}

	if ( isset( $input['popular_content_date_range'] ) && in_array( $input['popular_content_date_range'], array( '1', '7', '14', '30', '90' ), true ) ) {
		$sanitized['popular_content_date_range'] = $input['popular_content_date_range'];
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
		<input type="password" name="cabin_analytics_dashboard_options[api_key]" value="<?php echo esc_attr( $api_key ); ?>" class="cabin-input" placeholder="sk_live_..." autocomplete="off" />
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

/**
 * Render popular content default date range field.
 */
function cabin_analytics_popular_content_date_range_render() {
	$options    = get_option( 'cabin_analytics_dashboard_options' );
	$date_range = isset( $options['popular_content_date_range'] ) ? $options['popular_content_date_range'] : '30';

	?>
	<div class="cabin-field-wrapper">
		<select name="cabin_analytics_dashboard_options[popular_content_date_range]" class="cabin-select">
			<option value="1" <?php selected( $date_range, '1' ); ?>><?php esc_html_e( 'Last 1 Day', 'cabin-analytics-dashboard' ); ?></option>
			<option value="7" <?php selected( $date_range, '7' ); ?>><?php esc_html_e( 'Last 7 Days', 'cabin-analytics-dashboard' ); ?></option>
			<option value="14" <?php selected( $date_range, '14' ); ?>><?php esc_html_e( 'Last 14 Days', 'cabin-analytics-dashboard' ); ?></option>
			<option value="30" <?php selected( $date_range, '30' ); ?>><?php esc_html_e( 'Last 30 Days', 'cabin-analytics-dashboard' ); ?></option>
			<option value="90" <?php selected( $date_range, '90' ); ?>><?php esc_html_e( 'Last 90 Days', 'cabin-analytics-dashboard' ); ?></option>
		</select>
		<p class="cabin-description">
			<svg class="cabin-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
				<circle cx="12" cy="12" r="10"/>
				<path d="M12 16v-4M12 8h.01"/>
			</svg>
			<?php esc_html_e( 'Default range used by [cabin_popular_content] and the Popular Content block when no range is provided.', 'cabin-analytics-dashboard' ); ?>
		</p>
	</div>
	<?php
}

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

						<h4><?php esc_html_e( 'Popular Content Shortcode / Block', 'cabin-analytics-dashboard' ); ?></h4>
						<code class="cabin-code">[cabin_popular_content]</code>
						<p><?php esc_html_e( 'Optional overrides:', 'cabin-analytics-dashboard' ); ?></p>
						<code class="cabin-code">[cabin_popular_content qty="10" date_range="30" title="Top Content"]</code>
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

	register_rest_route( 'cabin-analytics/v1', '/popular-content', array(
		'methods'             => 'GET',
		'callback'            => 'cabin_analytics_dashboard_get_popular_content_rest',
		'permission_callback' => function() {
			return current_user_can( 'edit_posts' );
		},
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
}
add_action( 'rest_api_init', 'cabin_analytics_dashboard_register_rest_routes' );

/**
 * Return the allowed Popular Content date ranges.
 *
 * @return int[] Allowed date ranges in days.
 */
function cabin_analytics_dashboard_get_allowed_popular_ranges() {
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
function cabin_analytics_dashboard_get_popular_content_rest( $request ) {
	$options    = get_option( 'cabin_analytics_dashboard_options' );
	$qty        = absint( $request->get_param( 'qty' ) );
	$date_range = absint( $request->get_param( 'date_range' ) );

	if ( ! in_array( $qty, cabin_analytics_dashboard_get_allowed_popular_quantities(), true ) ) {
		$qty = 10;
	}

	if ( ! in_array( $date_range, cabin_analytics_dashboard_get_allowed_popular_ranges(), true ) ) {
		$date_range = isset( $options['popular_content_date_range'] ) ? absint( $options['popular_content_date_range'] ) : 30;
	}

	if ( ! in_array( $date_range, cabin_analytics_dashboard_get_allowed_popular_ranges(), true ) ) {
		$date_range = 30;
	}

	$items = cabin_analytics_dashboard_get_popular_content( $qty, $date_range );

	if ( is_wp_error( $items ) ) {
		return rest_ensure_response(
			array(
				'success' => false,
				'html'    => '<p class="cabin-popular-content__message">' . esc_html( $items->get_error_message() ) . '</p>',
			)
		);
	}

	return rest_ensure_response(
		array(
			'success' => true,
			'html'    => cabin_analytics_dashboard_render_popular_content_dashboard_items( $items ),
		)
	);
}

/**
 * Return the allowed dashboard Popular Content quantities.
 *
 * @return int[] Allowed quantities.
 */
function cabin_analytics_dashboard_get_allowed_popular_quantities() {
	return array( 5, 10, 20, 50 );
}

/**
 * Format a metric value for Popular Content output.
 *
 * @param mixed  $value Metric value.
 * @param string $type  Metric type.
 * @return string
 */
function cabin_analytics_dashboard_format_popular_metric( $value, $type = 'integer' ) {
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
 * Render dashboard Popular Content items.
 *
 * @param array $items Popular content items.
 * @return string
 */
function cabin_analytics_dashboard_render_popular_content_dashboard_items( $items ) {
	if ( empty( $items ) ) {
		return '<p class="cabin-popular-content__message">' . esc_html__( 'No popular content was found for this date range.', 'cabin-analytics-dashboard' ) . '</p>';
	}

	ob_start();
	?>
	<ol class="cabin-popular-content__summary-list">
		<?php foreach ( $items as $index => $item ) : ?>
			<li class="cabin-popular-content__summary-item">
				<details class="cabin-popular-content__details">
					<summary class="cabin-popular-content__summary">
						<span class="cabin-popular-content__summary-title">
							<span class="cabin-popular-content__summary-number"><?php echo esc_html( (string) ( $index + 1 ) ); ?>.</span>
							<span class="cabin-popular-content__summary-name"><?php echo esc_html( $item['title'] ); ?></span>
						</span>
						<span class="cabin-popular-content__summary-actions">
							<span class="cabin-popular-content__summary-action"><?php esc_html_e( 'view details', 'cabin-analytics-dashboard' ); ?></span>
							<?php /* translators: %s: content title. */ ?>
							<a class="cabin-popular-content__summary-content-link" href="<?php echo esc_url( $item['url'] ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Visit %s', 'cabin-analytics-dashboard' ), $item['title'] ) ); ?>"><?php esc_html_e( 'view content', 'cabin-analytics-dashboard' ); ?></a>
						</span>
					</summary>
					<dl class="cabin-popular-content__metrics">
						<div class="cabin-popular-content__metric">
							<dt class="cabin-popular-content__metric-label"><?php esc_html_e( 'Page views', 'cabin-analytics-dashboard' ); ?></dt>
							<dd class="cabin-popular-content__metric-value"><?php echo esc_html( cabin_analytics_dashboard_format_popular_metric( $item['page_views'] ?? 0 ) ); ?></dd>
						</div>
						<div class="cabin-popular-content__metric">
							<dt class="cabin-popular-content__metric-label"><?php esc_html_e( 'Unique visitors', 'cabin-analytics-dashboard' ); ?></dt>
							<dd class="cabin-popular-content__metric-value"><?php echo esc_html( cabin_analytics_dashboard_format_popular_metric( $item['unique_visitors'] ?? 0 ) ); ?></dd>
						</div>
						<div class="cabin-popular-content__metric">
							<dt class="cabin-popular-content__metric-label"><?php esc_html_e( 'Average duration seconds', 'cabin-analytics-dashboard' ); ?></dt>
							<dd class="cabin-popular-content__metric-value"><?php echo esc_html( cabin_analytics_dashboard_format_popular_metric( $item['average_duration_seconds'] ?? 0 ) ); ?></dd>
						</div>
						<div class="cabin-popular-content__metric">
							<dt class="cabin-popular-content__metric-label"><?php esc_html_e( 'CO2 grams', 'cabin-analytics-dashboard' ); ?></dt>
							<dd class="cabin-popular-content__metric-value"><?php echo esc_html( cabin_analytics_dashboard_format_popular_metric( $item['co2_grams'] ?? 0, 'float' ) ); ?></dd>
						</div>
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
function cabin_analytics_dashboard_get_allowed_chart_ranges() {
	return array( 7, 14, 30, 90 );
}

/**
 * Normalize a domain value for comparison and Cabin API requests.
 *
 * @param string $domain Domain value.
 * @return string Normalized domain.
 */
function cabin_analytics_dashboard_normalize_domain( $domain ) {
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
function cabin_analytics_dashboard_validate_stats_request( $request, $domain ) {
	$options         = get_option( 'cabin_analytics_dashboard_options' );
	$saved_domain    = isset( $options['domain'] ) ? cabin_analytics_dashboard_normalize_domain( $options['domain'] ) : '';
	$requested_start = (string) $request->get_param( 'start_date' );
	$requested_end   = (string) $request->get_param( 'end_date' );

	if ( '' === $saved_domain || $domain !== $saved_domain ) {
		return new WP_Error(
			'invalid_domain',
			__( 'Analytics can only be requested for the configured Cabin Analytics domain.', 'cabin-analytics-dashboard' ),
			array( 'status' => 403 )
		);
	}

	try {
		$start = new DateTimeImmutable( $requested_start, wp_timezone() );
		$end   = new DateTimeImmutable( $requested_end, wp_timezone() );
	} catch ( Exception $exception ) {
		return new WP_Error(
			'invalid_date',
			__( 'Invalid analytics date range.', 'cabin-analytics-dashboard' ),
			array( 'status' => 400 )
		);
	}

	$days = (int) $start->diff( $end )->format( '%a' ) + 1;

	if ( $start > $end || ! in_array( $days, cabin_analytics_dashboard_get_allowed_chart_ranges(), true ) ) {
		return new WP_Error(
			'invalid_date_range',
			__( 'Analytics date range must be one of the supported ranges.', 'cabin-analytics-dashboard' ),
			array( 'status' => 400 )
		);
	}

	return true;
}

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

	$domain = cabin_analytics_dashboard_normalize_domain( $domain );

	$validation = cabin_analytics_dashboard_validate_stats_request( $request, $domain );
	if ( is_wp_error( $validation ) ) {
		return $validation;
	}

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
	
	$asset_file   = include( $asset_file_path );
	$view_js_path = CABIN_ANALYTICS_PLUGIN_DIR . 'build/view.js';
	$style_path   = CABIN_ANALYTICS_PLUGIN_DIR . 'build/style-index.css';
	$script_ver   = file_exists( $view_js_path ) ? filemtime( $view_js_path ) : CABIN_ANALYTICS_VERSION;
	$style_ver    = file_exists( $style_path ) ? filemtime( $style_path ) : CABIN_ANALYTICS_VERSION;
	
	wp_register_script(
		'cabin-analytics-dashboard-view',
		CABIN_ANALYTICS_PLUGIN_URL . 'build/view.js',
		$asset_file['dependencies'],
		$script_ver,
		true
	);
	
	wp_register_style(
		'cabin-analytics-dashboard-style',
		CABIN_ANALYTICS_PLUGIN_URL . 'build/style-index.css',
		array(),
		$style_ver
	);
}
add_action( 'admin_enqueue_scripts', 'cabin_analytics_dashboard_enqueue_assets' );
add_action( 'wp_enqueue_scripts', 'cabin_analytics_dashboard_enqueue_assets' );


/**
 * Register dashboard widget.
 */
function cabin_analytics_dashboard_add_widget() {
	$options = get_option( 'cabin_analytics_dashboard_options' );
	$api_key = isset( $options['api_key'] ) ? $options['api_key'] : '';
	
	if ( empty( $api_key ) || ! current_user_can( 'edit_posts' ) ) {
		return;
	}

	wp_add_dashboard_widget(
		'cabin_analytics_dashboard_widget',
		__( 'Cabin Analytics Dashboard', 'cabin-analytics-dashboard' ),
		'cabin_analytics_dashboard_widget_render'
	);

	wp_add_dashboard_widget(
		'cabin_analytics_popular_content_widget',
		__( 'Cabin Popular Content', 'cabin-analytics-dashboard' ),
		'cabin_analytics_dashboard_popular_content_widget_render'
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

	echo '<style>
		#cabin_analytics_dashboard_widget .inside { margin: 0; padding: 0; }
		#cabin_analytics_dashboard_widget .cabin-analytics-title { color: var(--cabin-text-primary, #1e1e1e);font-size: 1.25rem;
    font-weight: var(--cabin-font-weight-semibold, 600); }
		#cabin_analytics_dashboard_widget .cabin-analytics-domain { font-weight: var(--cabin-font-weight-semibold, 600); }
		#cabin_analytics_dashboard_widget .cabin-analytics-controls { align-items: center; display: flex; flex-wrap: wrap; width: 100%; gap: var(--cabin-gap-lg, 24px); justify-content: space-between; }
		#cabin_analytics_dashboard_widget .cabin-dashboard-control { display: flex; flex-direction: column; gap: var(--cabin-gap-xs, 4px); }
		#cabin_analytics_dashboard_widget .cabin-dashboard-control__label { color: var(--cabin-text-tertiary, #666); font-size: 10px; font-weight: var(--cabin-font-weight-medium, 500); letter-spacing: var(--cabin-letter-spacing, 0.5px); line-height: 1.2; text-transform: uppercase; }
		#cabin_analytics_dashboard_widget .cabin-dashboard-control__button-group { border: 1px solid var(--cabin-border-color, #dcdcde); border-radius: var(--cabin-border-radius-sm, 6px); display: flex; overflow: hidden; }
		#cabin_analytics_dashboard_widget .cabin-dashboard-control__button { background: var(--cabin-widget-bg, #fff); border: 0; border-right: 1px solid var(--cabin-border-color, #dcdcde); color: var(--cabin-text-secondary, #555); cursor: pointer; font-size: var(--cabin-font-size-base, 14px); font-weight: var(--cabin-font-weight-medium, 500); padding: var(--cabin-button-padding-v, 8px) var(--cabin-gap-md, 16px); transition: var(--cabin-transition, all 0.2s ease); }
		#cabin_analytics_dashboard_widget .cabin-dashboard-control__button:last-child { border-right: 0; }
		#cabin_analytics_dashboard_widget .cabin-dashboard-control__button:hover:not(.active) { background: var(--cabin-bg-hover, #f5f5f5); }
		#cabin_analytics_dashboard_widget .cabin-dashboard-control__button:focus-visible { outline: var(--cabin-outline-width, 2px) solid var(--cabin-primary, #2271b1); outline-offset: var(--cabin-outline-offset, -2px); z-index: 1; }
		#cabin_analytics_dashboard_widget .cabin-dashboard-control__button.active { background: var(--cabin-button-active-bg, #2271b1); color: var(--cabin-button-active-text, #fff); }
	</style>';

	printf(
		'<div class="cabin-analytics-widget" data-domain="%s" data-chart-type="%s" data-date-range="%s" data-allow-switching="true" data-dashboard-url="%s"></div>',
		esc_attr( $domain ),
		esc_attr( $chart_type ),
		esc_attr( $date_range ),
		esc_url( $dashboard_url )
	);

}

/**
 * Return the allowed HTML for dashboard segmented controls.
 *
 * @return array Allowed HTML.
 */
function cabin_analytics_dashboard_get_segmented_control_allowed_html() {
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
			'data-cabin-popular-range' => true,
			'data-cabin-popular-qty'   => true,
		),
	);
}


/**
 * Render a reusable segmented control for dashboard widgets.
 *
 * @param array $args Control arguments.
 * @return string
 */
function cabin_analytics_dashboard_render_segmented_control( $args ) {
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
	$label_id    = 'cabin-dashboard-control-' . wp_unique_id();

	if ( '' === $label || empty( $options ) || '' === $data_attr ) {
		return '';
	}

	ob_start();
	?>
	<div class="cabin-dashboard-control <?php echo $class ? esc_attr( $class ) : ''; ?>">
		<span id="<?php echo esc_attr( $label_id ); ?>" class="cabin-dashboard-control__label"><?php echo esc_html( $label ); ?></span>
		<div class="cabin-dashboard-control__button-group" role="group" aria-labelledby="<?php echo esc_attr( $label_id ); ?>"<?php echo $group_label ? ' aria-label="' . esc_attr( $group_label ) . '"' : ''; ?>>
			<?php foreach ( $options as $value => $text ) : ?>
				<?php $is_active = (string) $value === $active; ?>
				<button
					class="cabin-dashboard-control__button <?php echo $is_active ? 'active' : ''; ?>"
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
 * Render the standalone popular content dashboard widget.
 */
function cabin_analytics_dashboard_popular_content_widget_render() {
	$options    = get_option( 'cabin_analytics_dashboard_options' );
	$domain     = isset( $options['domain'] ) ? trim( $options['domain'] ) : '';
	$date_range = isset( $options['popular_content_date_range'] ) && in_array( (string) $options['popular_content_date_range'], array( '1', '7', '14', '30', '90' ), true ) ? absint( $options['popular_content_date_range'] ) : 30;
	$qty        = 10;
	$target_id  = 'cabin-popular-content-dashboard-' . wp_rand( 1000, 9999 );
	$nonce      = wp_create_nonce( 'wp_rest' );
	$rest_url   = rest_url( 'cabin-analytics/v1/popular-content' );

	$date_range_options = array();
	foreach ( cabin_analytics_dashboard_get_allowed_popular_ranges() as $range ) {
		$date_range_options[ $range ] = $range . 'd';
	}

	$quantity_options = array();
	foreach ( cabin_analytics_dashboard_get_allowed_popular_quantities() as $quantity ) {
		$quantity_options[ $quantity ] = (string) $quantity;
	}

	wp_enqueue_style( 'cabin-analytics-dashboard-style' );
	?>
	<style>
		#cabin_analytics_popular_content_widget .inside { margin: 0; padding: 0; }
		#cabin_analytics_popular_content_widget .cabin-popular-content-dashboard { background: var(--cabin-widget-bg, #fff); border: 1px solid var(--cabin-border-color, #dcdcde); border-radius: var(--cabin-border-radius, 8px); font-family: var(--cabin-font-family, inherit); padding: var(--cabin-padding, 24px); }
		#cabin_analytics_popular_content_widget .cabin-popular-content-dashboard__header { align-items: flex-start; display: flex; flex-wrap: wrap; gap: var(--cabin-gap-lg, 24px); justify-content: space-between; margin-bottom: var(--cabin-gap-lg, 24px); }
		#cabin_analytics_popular_content_widget .cabin-popular-content-dashboard__title-wrapper { display: flex; flex-direction: column; gap: var(--cabin-gap-xs, 4px); }
		#cabin_analytics_popular_content_widget .cabin-popular-content-dashboard__title { color: var(--cabin-text-primary, #1e1e1e); font-size: 1.25rem; font-weight: var(--cabin-font-weight-semibold, 600); line-height: 1.2; margin: 0; }
		#cabin_analytics_popular_content_widget .cabin-popular-content-dashboard__domain { color: var(--cabin-text-tertiary, #666); font-size: var(--cabin-font-size-sm, 12px); font-weight: var(--cabin-font-weight-semibold, 600); letter-spacing: var(--cabin-letter-spacing, 0.5px); text-transform: uppercase; }
		#cabin_analytics_popular_content_widget .cabin-popular-content-dashboard__controls { align-items: center; display: flex; flex-wrap: wrap; width: 100%; gap: var(--cabin-gap-lg, 24px); justify-content: space-between; }
		#cabin_analytics_popular_content_widget .cabin-dashboard-control { display: flex; flex-direction: column; gap: var(--cabin-gap-xs, 4px); }
		#cabin_analytics_popular_content_widget .cabin-dashboard-control__label { color: var(--cabin-text-tertiary, #666); font-size: 10px; font-weight: var(--cabin-font-weight-medium, 500); letter-spacing: var(--cabin-letter-spacing, 0.5px); line-height: 1.2; text-transform: uppercase; }
		#cabin_analytics_popular_content_widget .cabin-dashboard-control__button-group { border: 1px solid var(--cabin-border-color, #dcdcde); border-radius: var(--cabin-border-radius-sm, 6px); display: flex; overflow: hidden; }
		#cabin_analytics_popular_content_widget .cabin-dashboard-control__button { background: var(--cabin-widget-bg, #fff); border: 0; border-right: 1px solid var(--cabin-border-color, #dcdcde); color: var(--cabin-text-secondary, #555); cursor: pointer; font-size: var(--cabin-font-size-base, 14px); font-weight: var(--cabin-font-weight-medium, 500); padding: var(--cabin-button-padding-v, 8px) var(--cabin-gap-md, 16px); transition: var(--cabin-transition, all 0.2s ease); }
		#cabin_analytics_popular_content_widget .cabin-dashboard-control__button:last-child { border-right: 0; }
		#cabin_analytics_popular_content_widget .cabin-dashboard-control__button:hover:not(.active) { background: var(--cabin-bg-hover, #f5f5f5); }
		#cabin_analytics_popular_content_widget .cabin-dashboard-control__button:focus-visible { outline: var(--cabin-outline-width, 2px) solid var(--cabin-primary, #2271b1); outline-offset: var(--cabin-outline-offset, -2px); z-index: 1; }
		#cabin_analytics_popular_content_widget .cabin-dashboard-control__button.active { background: var(--cabin-button-active-bg, #2271b1); color: var(--cabin-button-active-text, #fff); }
		#cabin_analytics_popular_content_widget .cabin-popular-content__summary-list { display: grid; gap: var(--cabin-gap-sm, 8px); list-style: none; margin: 0; padding: 0; }
		#cabin_analytics_popular_content_widget .cabin-popular-content__summary-item { margin: 0; }
		#cabin_analytics_popular_content_widget .cabin-popular-content__details { border-bottom: 1px solid var(--cabin-grid-line, #e0e0e0); padding: var(--cabin-gap-sm, 8px) 0; }
		#cabin_analytics_popular_content_widget .cabin-popular-content__summary { align-items: center; cursor: pointer; display: flex; gap: var(--cabin-gap-md, 16px); justify-content: space-between; list-style: none; }
		#cabin_analytics_popular_content_widget .cabin-popular-content__summary::-webkit-details-marker { display: none; }
		#cabin_analytics_popular_content_widget .cabin-popular-content__summary-title { align-items: baseline; display: flex; gap: var(--cabin-gap-sm, 8px); min-width: 0; }
		#cabin_analytics_popular_content_widget .cabin-popular-content__summary-number, #cabin_analytics_popular_content_widget .cabin-popular-content__summary-name { font-size: 1rem; font-weight: var(--cabin-font-weight-semibold, 600); line-height: 1.2; }
		#cabin_analytics_popular_content_widget .cabin-popular-content__summary-name { color: var(--cabin-text-primary, #1e1e1e); }
		#cabin_analytics_popular_content_widget .cabin-popular-content__summary-actions { align-items: center; display: flex; gap: var(--cabin-gap-sm, 8px); white-space: nowrap; }
		#cabin_analytics_popular_content_widget .cabin-popular-content__summary-content-link { color: var(--cabin-primary, #2271b1); font-size: var(--cabin-font-size-base, 14px); text-decoration: none; }
		#cabin_analytics_popular_content_widget .cabin-popular-content__summary-content-link:hover, #cabin_analytics_popular_content_widget .cabin-popular-content__summary-content-link:focus-visible { text-decoration: underline; }
		#cabin_analytics_popular_content_widget .cabin-popular-content__summary-action { color: var(--cabin-text-primary, #1e1e1e); font-size: var(--cabin-font-size-base, 14px); }
		#cabin_analytics_popular_content_widget .cabin-popular-content__metrics { display: grid; gap: var(--cabin-gap-sm, 8px); grid-template-columns: repeat(4, minmax(0, 1fr)); margin: var(--cabin-gap-md, 16px) 0 0; }
		#cabin_analytics_popular_content_widget .cabin-popular-content__metric { background: var(--cabin-bg-secondary, #f9f9f9); border-radius: var(--cabin-border-radius-sm, 6px); padding: var(--cabin-spacing-sm, 12px); }
		#cabin_analytics_popular_content_widget .cabin-popular-content__metric-label { color: var(--cabin-text-tertiary, #666); font-size: 10px; letter-spacing: var(--cabin-letter-spacing, 0.5px); line-height: 1.2; margin: 0 0 var(--cabin-gap-xs, 4px); text-transform: uppercase; }
		#cabin_analytics_popular_content_widget .cabin-popular-content__metric-value { color: var(--cabin-text-primary, #1e1e1e); font-size: 16px; font-weight: 500; line-height: 1.2; margin: 0; }
		#cabin_analytics_popular_content_widget .cabin-popular-content__message { color: var(--cabin-text-tertiary, #666); font-size: var(--cabin-font-size-base, 14px); margin: 0; }
		@media (max-width: 782px) { #cabin_analytics_popular_content_widget .cabin-popular-content-dashboard__header, #cabin_analytics_popular_content_widget .cabin-popular-content-dashboard__controls { align-items: stretch; flex-direction: column; } #cabin_analytics_popular_content_widget .cabin-dashboard-control__button-group { width: 100%; } #cabin_analytics_popular_content_widget .cabin-dashboard-control__button { flex: 1; } #cabin_analytics_popular_content_widget .cabin-popular-content__summary { align-items: flex-start; flex-direction: column; } #cabin_analytics_popular_content_widget .cabin-popular-content__metrics { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
	</style>
	<div class="cabin-popular-content-dashboard" data-cabin-dashboard-popular-content>
		<div class="cabin-popular-content-dashboard__header">
			<div class="cabin-popular-content-dashboard__title-wrapper">
				<h2 class="cabin-popular-content-dashboard__title"><?php esc_html_e( 'Cabin Analytics Popular Content', 'cabin-analytics-dashboard' ); ?></h2>
				<?php if ( '' !== $domain ) : ?>
					<?php /* translators: %s: configured Cabin Analytics domain. */ ?>
					<div class="cabin-popular-content-dashboard__domain" aria-label="<?php echo esc_attr( sprintf( __( 'Domain: %s', 'cabin-analytics-dashboard' ), $domain ) ); ?>"><?php echo esc_html( $domain ); ?></div>
				<?php endif; ?>
			</div>
			<div class="cabin-popular-content-dashboard__controls">
				<?php
				echo wp_kses(
					cabin_analytics_dashboard_render_segmented_control(
						array(
							'label'       => __( 'Date range', 'cabin-analytics-dashboard' ),
							'group_label' => __( 'Select popular content date range', 'cabin-analytics-dashboard' ),
							'options'     => $date_range_options,
							'active'      => $date_range,
							'data_attr'   => 'data-cabin-popular-range',
							'class'       => 'cabin-dashboard-control--date-range',
						)
					),
					cabin_analytics_dashboard_get_segmented_control_allowed_html()
				);
				echo wp_kses(
					cabin_analytics_dashboard_render_segmented_control(
						array(
							'label'       => __( 'Records to show', 'cabin-analytics-dashboard' ),
							'group_label' => __( 'Select number of records to show', 'cabin-analytics-dashboard' ),
							'options'     => $quantity_options,
							'active'      => $qty,
							'data_attr'   => 'data-cabin-popular-qty',
							'class'       => 'cabin-dashboard-control--quantity',
						)
					),
					cabin_analytics_dashboard_get_segmented_control_allowed_html()
				);
				?>
			</div>
		</div>
		<div id="<?php echo esc_attr( $target_id ); ?>" class="cabin-popular-content-dashboard__body" aria-live="polite">
			<p class="cabin-popular-content__message"><?php esc_html_e( 'Loading popular content…', 'cabin-analytics-dashboard' ); ?></p>
		</div>
	</div>
	<script>
		(function() {
			const widget = document.querySelector('[data-cabin-dashboard-popular-content]');
			const target = document.getElementById(<?php echo wp_json_encode( $target_id ); ?>);
			const restUrl = <?php echo wp_json_encode( esc_url_raw( $rest_url ) ); ?>;
			const nonce = <?php echo wp_json_encode( $nonce ); ?>;
			let dateRange = <?php echo wp_json_encode( $date_range ); ?>;
			let qty = <?php echo wp_json_encode( $qty ); ?>;

			if (!widget || !target) { return; }

			target.addEventListener('click', (event) => {
				if (event.target.closest('.cabin-popular-content__summary-content-link')) { event.stopPropagation(); }
			});

			function setActive(selector, value) {
				widget.querySelectorAll(selector).forEach((button) => {
					const buttonValue = button.dataset.cabinPopularRange || button.dataset.cabinPopularQty;
					const isActive = String(buttonValue) === String(value);
					button.classList.toggle('active', isActive);
					button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
				});
			}

			function loadPopularContent() {
				const url = new URL(restUrl);
				url.searchParams.set('date_range', dateRange);
				url.searchParams.set('qty', qty);
				target.innerHTML = '<p class="cabin-popular-content__message"><?php echo esc_js( __( 'Loading popular content…', 'cabin-analytics-dashboard' ) ); ?></p>';

				fetch(url.toString(), { credentials: 'same-origin', headers: { 'X-WP-Nonce': nonce } })
					.then((response) => response.json())
					.then((data) => { target.innerHTML = data && data.html ? data.html : '<p class="cabin-popular-content__message"><?php echo esc_js( __( 'Popular content could not be loaded.', 'cabin-analytics-dashboard' ) ); ?></p>'; })
					.catch(() => { target.innerHTML = '<p class="cabin-popular-content__message"><?php echo esc_js( __( 'Popular content could not be loaded.', 'cabin-analytics-dashboard' ) ); ?></p>'; });
			}

			widget.querySelectorAll('[data-cabin-popular-range]').forEach((button) => {
				button.addEventListener('click', () => {
					dateRange = parseInt(button.dataset.cabinPopularRange, 10);
					setActive('[data-cabin-popular-range]', dateRange);
					loadPopularContent();
				});
			});

			widget.querySelectorAll('[data-cabin-popular-qty]').forEach((button) => {
				button.addEventListener('click', () => {
					qty = parseInt(button.dataset.cabinPopularQty, 10);
					setActive('[data-cabin-popular-qty]', qty);
					loadPopularContent();
				});
			});

			loadPopularContent();
		})();
	</script>
	<?php
}

/**
 * Normalize a Cabin path so WordPress can resolve it consistently.
 *
 * @param string $path Path returned by Cabin.
 * @return string
 */
function cabin_analytics_dashboard_normalize_path( $path ) {
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
function cabin_analytics_dashboard_resolve_path_to_post( $path ) {
	$path = cabin_analytics_dashboard_normalize_path( $path );

	if ( '' === $path ) {
		return null;
	}

	$post_id = 0;

	if ( '/' === $path ) {
		$post_id = (int) get_option( 'page_on_front' );
	} else {
		$post_id = url_to_postid( home_url( $path ) );
	}

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
 * Fetch popular pages from Cabin and reconcile them to local WP posts.
 *
 * @param int $qty Number of resolved posts to return.
 * @param int $date_range Number of days to look back.
 * @return array|WP_Error
 */
function cabin_analytics_dashboard_get_popular_content( $qty = 10, $date_range = 30 ) {
	$options = get_option( 'cabin_analytics_dashboard_options' );
	$api_key = isset( $options['api_key'] ) ? trim( $options['api_key'] ) : '';
	$domain  = isset( $options['domain'] ) ? trim( $options['domain'] ) : '';

	if ( empty( $api_key ) ) {
		return new WP_Error( 'no_api_key', __( 'Cabin Analytics API key not configured.', 'cabin-analytics-dashboard' ) );
	}

	if ( empty( $domain ) ) {
		return new WP_Error( 'no_domain', __( 'Cabin Analytics domain not configured.', 'cabin-analytics-dashboard' ) );
	}

	$qty = absint( $qty );
	$qty = $qty > 0 ? min( $qty, 50 ) : 10;

	$date_range = absint( $date_range );
	if ( ! in_array( $date_range, cabin_analytics_dashboard_get_allowed_popular_ranges(), true ) ) {
		$date_range = 30;
	}

	$domain = trim( str_replace( array( 'http://', 'https://', 'www.' ), '', $domain ), '/' );

	$today     = new DateTimeImmutable( 'today', wp_timezone() );
	$date_to   = $today->format( 'Y-m-d' );
	$date_from = $today->modify( '-' . $date_range . ' days' )->format( 'Y-m-d' );

	$limit = min( max( $qty * 4, $qty ), 250 );

	$cache_key = 'cabin_popular_content_' . md5( $domain . '|' . $date_from . '|' . $date_to . '|' . $qty . '|' . $limit );
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
			'user-agent' => 'WordPress Cabin Analytics Dashboard/' . CABIN_ANALYTICS_VERSION,
		)
	);

	if ( is_wp_error( $response ) ) {
		return $response;
	}

	$response_code = wp_remote_retrieve_response_code( $response );
	$body          = wp_remote_retrieve_body( $response );

	if ( 200 !== $response_code ) {
		return new WP_Error(
			'api_error',
			sprintf(
				/* translators: %d: HTTP status code */
				__( 'Cabin Analytics API returned HTTP %d.', 'cabin-analytics-dashboard' ),
				$response_code
			)
		);
	}

	$data = json_decode( $body, true );

	if ( JSON_ERROR_NONE !== json_last_error() || empty( $data['pages'] ) || ! is_array( $data['pages'] ) ) {
		return new WP_Error( 'no_pages', __( 'No page data was returned by Cabin Analytics.', 'cabin-analytics-dashboard' ) );
	}

	$items = array();
	$seen  = array();

	foreach ( $data['pages'] as $page ) {
		if ( empty( $page['path'] ) ) {
			continue;
		}

		$post = cabin_analytics_dashboard_resolve_path_to_post( $page['path'] );

		if ( ! $post || isset( $seen[ $post->ID ] ) ) {
			continue;
		}

		$seen[ $post->ID ] = true;

		$items[] = array(
			'id'                       => $post->ID,
			'title'                    => get_the_title( $post ),
			'url'                      => get_permalink( $post ),
			'post_type'                => get_post_type( $post ),
			'path'                     => cabin_analytics_dashboard_normalize_path( $page['path'] ),
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
function cabin_analytics_dashboard_render_popular_content( $args = array() ) {
	$options            = get_option( 'cabin_analytics_dashboard_options' );
	$default_title      = isset( $options['popular_content_title'] ) && '' !== trim( $options['popular_content_title'] ) ? $options['popular_content_title'] : __( 'Top Content', 'cabin-analytics-dashboard' );
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

	if ( ! in_array( $date_range, cabin_analytics_dashboard_get_allowed_popular_ranges(), true ) ) {
		$date_range = absint( $default_date_range );
	}

	if ( '' === trim( $title ) ) {
		$title = $default_title;
	}

	$items = cabin_analytics_dashboard_get_popular_content( $qty, $date_range );

	if ( is_wp_error( $items ) || empty( $items ) ) {
		return '';
	}

	ob_start();
	?>
	<div class="cabin-popular-content" data-cabin-popular-content data-date-range="<?php echo esc_attr( $date_range ); ?>">
		<h2 class="cabin-popular-content__heading"><?php echo esc_html( $title ); ?></h2>
		<ol class="cabin-popular-content__list">
			<?php foreach ( $items as $item ) : ?>
				<li class="cabin-popular-content__item">
					<a class="cabin-popular-content__link" href="<?php echo esc_url( $item['url'] ); ?>">
						<?php echo esc_html( $item['title'] ); ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ol>
	</div>
	<?php
	return trim( ob_get_clean() );
}

/**
 * Render Cabin Analytics chart shortcode.
 *
 * Usage: [cabin_analytics domain="example.com" chart_type="line" date_range="30" allow_switching="true"]
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function cabin_analytics_dashboard_chart_shortcode( $atts ) {
	$options = get_option( 'cabin_analytics_dashboard_options' );

	$atts = shortcode_atts(
		array(
			'domain'          => isset( $options['domain'] ) ? $options['domain'] : '',
			'chart_type'      => isset( $options['chart_type'] ) ? $options['chart_type'] : 'bar',
			'date_range'      => isset( $options['date_range'] ) ? $options['date_range'] : '7',
			'allow_switching' => 'true',
			'dashboard_url'   => '',
		),
		$atts,
		'cabin_analytics'
	);

	$domain          = sanitize_text_field( $atts['domain'] );
	$chart_type      = in_array( $atts['chart_type'], array( 'bar', 'line' ), true ) ? $atts['chart_type'] : 'bar';
	$date_range      = in_array( (string) $atts['date_range'], array( '7', '14', '30', '90' ), true ) ? (string) $atts['date_range'] : '7';
	$allow_switching = filter_var( $atts['allow_switching'], FILTER_VALIDATE_BOOLEAN ) ? 'true' : 'false';
	$dashboard_url   = '' !== $atts['dashboard_url'] ? esc_url_raw( $atts['dashboard_url'] ) : '';

	if ( '' === $domain ) {
		return '<p class="cabin-analytics-error">' . esc_html__( 'Cabin Analytics domain is not configured.', 'cabin-analytics-dashboard' ) . '</p>';
	}

	if ( '' === $dashboard_url ) {
		$dashboard_url = 'https://withcabin.com/dashboard/' . $domain;
	}

	wp_enqueue_script( 'cabin-analytics-dashboard-view' );
	wp_enqueue_style( 'cabin-analytics-dashboard-style' );

	return sprintf(
		'<div class="cabin-analytics-widget" data-domain="%s" data-chart-type="%s" data-date-range="%s" data-allow-switching="%s" data-dashboard-url="%s"></div>',
		esc_attr( $domain ),
		esc_attr( $chart_type ),
		esc_attr( $date_range ),
		esc_attr( $allow_switching ),
		esc_url( $dashboard_url )
	);
}
add_shortcode( 'cabin_analytics', 'cabin_analytics_dashboard_chart_shortcode' );

/**
 * Render popular content shortcode.
 *
 * Usage: [cabin_popular_content qty="10" date_range="30" title="Top Content"]
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function cabin_analytics_dashboard_popular_content_shortcode( $atts ) {
	$options            = get_option( 'cabin_analytics_dashboard_options' );
	$default_title      = isset( $options['popular_content_title'] ) && '' !== trim( $options['popular_content_title'] ) ? $options['popular_content_title'] : __( 'Top Content', 'cabin-analytics-dashboard' );
	$default_date_range = isset( $options['popular_content_date_range'] ) && in_array( (string) $options['popular_content_date_range'], array( '1', '7', '14', '30', '90' ), true ) ? $options['popular_content_date_range'] : '30';

	$atts = shortcode_atts(
		array(
			'qty'        => 10,
			'date_range' => $default_date_range,
			'title'      => $default_title,
		),
		$atts,
		'cabin_popular_content'
	);

	return cabin_analytics_dashboard_render_popular_content(
		array(
			'qty'        => $atts['qty'],
			'date_range' => $atts['date_range'],
			'title'      => $atts['title'],
		)
	);
}
add_shortcode( 'cabin_popular_content', 'cabin_analytics_dashboard_popular_content_shortcode' );

/**
 * Render callback for the Cabin Popular Content block.
 *
 * @param array $attributes Block attributes.
 * @return string
 */
function cabin_analytics_dashboard_popular_content_block_render( $attributes ) {
	return cabin_analytics_dashboard_render_popular_content(
		array(
			'qty'        => isset( $attributes['qty'] ) ? $attributes['qty'] : 10,
			'date_range' => isset( $attributes['dateRange'] ) ? $attributes['dateRange'] : '',
			'title'      => isset( $attributes['title'] ) ? $attributes['title'] : '',
		)
	);
}
