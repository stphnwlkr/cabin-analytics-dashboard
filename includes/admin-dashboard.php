<?php
/**
 * Standalone private Uplink Dashboard for Cabin.
 *
 * @package UpliandaDashboard
 */

defined( 'ABSPATH' ) || exit;

/** Register the plugin icon with the WordPress 7.1 icon registry. */
function uplianda_register_icons() {
	if ( ! function_exists( 'wp_register_icon_collection' ) || ! function_exists( 'wp_register_icon' ) ) {
		return;
	}
	$collections = WP_Icon_Collections_Registry::get_instance();
	if ( ! $collections->is_registered( 'uplinkpress' ) ) {
		wp_register_icon_collection( 'uplinkpress', array( 'label' => __( 'UplinkPress', 'uplink-analytics-dashboard-for-cabin' ) ) );
	}
	$icons = WP_Icons_Registry::get_instance();
	if ( ! $icons->is_registered( 'uplinkpress/chart-analysis' ) ) {
		wp_register_icon( 'uplinkpress/chart-analysis', array( 'label' => __( 'Chart analysis', 'uplink-analytics-dashboard-for-cabin' ), 'file_path' => UPLIANDA_PLUGIN_DIR . 'assets/branding/chart-analysis.svg' ) );
	}
}
add_action( 'init', 'uplianda_register_icons', 1 );

/** Return the chart icon markup. */
function uplianda_chart_icon_markup( $size = 24, $class = '' ) {
	return function_exists( 'wp_get_icon' ) ? wp_get_icon( 'uplinkpress/chart-analysis', array( 'size' => $size, 'class' => $class ) ) : '';
}

/** URL for the plugin admin icon. */
function uplianda_admin_menu_icon_url() {
	$icon = uplianda_chart_icon_markup( 20 );
	return $icon ? 'data:image/svg+xml;base64,' . base64_encode( $icon ) : UPLIANDA_PLUGIN_URL . 'assets/branding/chart-analysis.svg';
}

/** Capability required to view the private dashboard. */
function uplianda_manage_capability() {
	return 'uplianda_manage_dashboard';
}

/** Return roles selected for dashboard access. */
function uplianda_get_access_roles( $options = null ) {
	$options = is_array( $options ) ? $options : uplianda_get_options();
	$roles   = isset( $options['dashboard_access_roles'] ) && is_array( $options['dashboard_access_roles'] ) ? $options['dashboard_access_roles'] : array( 'administrator' );
	$roles   = array_values( array_unique( array_map( 'sanitize_key', $roles ) ) );
	if ( ! in_array( 'administrator', $roles, true ) ) {
		$roles[] = 'administrator';
	}
	return $roles;
}

/** Synchronize dashboard capabilities. */
function uplianda_sync_access_roles( $options = null ) {
	$allowed = uplianda_get_access_roles( $options );
	foreach ( wp_roles()->role_objects as $slug => $role ) {
		in_array( $slug, $allowed, true ) ? $role->add_cap( uplianda_manage_capability() ) : $role->remove_cap( uplianda_manage_capability() );
	}
}

/** Install dashboard capabilities. */
function uplianda_activate_private_dashboard() {
	uplianda_sync_access_roles();
	update_option( 'uplianda_capability_version', 1, false );
}
register_activation_hook( UPLIANDA_PLUGIN_DIR . 'uplink-analytics-dashboard-for-cabin.php', 'uplianda_activate_private_dashboard' );

/** Upgrade existing installations. */
function uplianda_maybe_upgrade_private_dashboard() {
	if ( 1 !== (int) get_option( 'uplianda_capability_version', 0 ) ) {
		uplianda_activate_private_dashboard();
	}
}
add_action( 'admin_init', 'uplianda_maybe_upgrade_private_dashboard' );

/** Synchronize capabilities when settings change. */
function uplianda_access_roles_updated( $old_value, $value ) {
	unset( $old_value );
	uplianda_sync_access_roles( is_array( $value ) ? $value : null );
}
add_action( 'update_option_uplianda_options', 'uplianda_access_roles_updated', 10, 2 );

/** Register dashboard menu items. */
function uplianda_register_private_dashboard_menu() {
	$capability = uplianda_manage_capability();
	add_menu_page( __( 'Uplink Dashboard for Cabin', 'uplink-analytics-dashboard-for-cabin' ), __( 'Cabin Dashboard', 'uplink-analytics-dashboard-for-cabin' ), $capability, 'uplianda-dashboard', 'uplianda_render_private_dashboard', uplianda_admin_menu_icon_url(), 81 );
	add_submenu_page( 'uplianda-dashboard', __( 'Uplink Dashboard for Cabin', 'uplink-analytics-dashboard-for-cabin' ), __( 'Dashboard', 'uplink-analytics-dashboard-for-cabin' ), $capability, 'uplianda-dashboard', 'uplianda_render_private_dashboard' );
	if ( current_user_can( 'manage_options' ) ) {
		$GLOBALS['uplianda_settings_page_hook'] = add_submenu_page(
			'uplianda-dashboard',
			__( 'Cabin Dashboard Settings', 'uplink-analytics-dashboard-for-cabin' ),
			__( 'Settings', 'uplink-analytics-dashboard-for-cabin' ),
			'manage_options',
			'uplianda-settings',
			'uplianda_options_page'
		);
	}
}
add_action( 'admin_menu', 'uplianda_register_private_dashboard_menu', 20 );

/**
 * Paint the saved dashboard theme before WordPress renders the page body.
 *
 * Date-range controls perform a normal document navigation so the API response
 * remains server rendered. Setting the canvas color in the document head keeps
 * that navigation from exposing WordPress's default white background.
 */
function uplianda_print_private_dashboard_theme_bootstrap() {
	$style = '
		html { background: var(--uplianda-dashboard-boot-bg, #f7f7f5); }
		body.toplevel_page_uplianda-dashboard,
		body.toplevel_page_uplianda-dashboard #wpwrap,
		body.toplevel_page_uplianda-dashboard #wpcontent,
		body.toplevel_page_uplianda-dashboard #wpbody,
		body.toplevel_page_uplianda-dashboard #wpbody-content {
			background-color: var(--uplianda-dashboard-boot-bg, #f7f7f5);
		}
		@view-transition { navigation: auto; }
	';

	$script = "(function(){var theme='light';try{var saved=window.localStorage.getItem('uplianda-dashboard-theme');theme=saved==='dark'||saved==='light'?saved:(window.matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light');}catch(error){theme=window.matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light';}var background=theme==='dark'?'#151515':'#f7f7f5';document.documentElement.dataset.upliandaDashboardTheme=theme;document.documentElement.style.setProperty('--uplianda-dashboard-boot-bg',background);document.documentElement.style.colorScheme=theme;}());";

	printf( '<style id="uplianda-dashboard-theme-bootstrap">%s</style>', $style ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static plugin-owned CSS, not user input.
	wp_print_inline_script_tag( $script, array( 'id' => 'uplianda-dashboard-theme-bootstrap-js' ) );
}
add_action( 'admin_head-toplevel_page_uplianda-dashboard', 'uplianda_print_private_dashboard_theme_bootstrap', 0 );

/** Enqueue standalone dashboard assets. */
function uplianda_prepare_private_dashboard( $hook_suffix ) {
	if ( 'toplevel_page_uplianda-dashboard' !== $hook_suffix ) {
		return;
	}
	wp_enqueue_style( 'uplianda-analytics-private-dashboard', UPLIANDA_PLUGIN_URL . 'assets/admin-dashboard.css', array(), filemtime( UPLIANDA_PLUGIN_DIR . 'assets/admin-dashboard.css' ) );
	wp_enqueue_script( 'uplianda-analytics-private-dashboard', UPLIANDA_PLUGIN_URL . 'assets/admin-dashboard.js', array(), filemtime( UPLIANDA_PLUGIN_DIR . 'assets/admin-dashboard.js' ), true );
	wp_enqueue_script( 'uplianda-country-map', UPLIANDA_PLUGIN_URL . 'assets/country-map.js', array(), filemtime( UPLIANDA_PLUGIN_DIR . 'assets/country-map.js' ), true );
}
add_action( 'admin_enqueue_scripts', 'uplianda_prepare_private_dashboard', 30 );

/** Return the requested dashboard reporting period. */
function uplianda_get_dashboard_range() {
	$range = isset( $_GET['range'] ) ? absint( wp_unslash( $_GET['range'] ) ) : 30; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only display preference.
	return in_array( $range, array( 1, 7, 14, 30, 90 ), true ) ? $range : 30;
}

/** Fetch the expanded Cabin data set used only by the private dashboard. */
function uplianda_get_private_dashboard_data( $date_range = 30 ) {
	$options = uplianda_get_options();
	$api_key = isset( $options['api_key'] ) ? trim( (string) $options['api_key'] ) : '';
	$domain  = isset( $options['domain'] ) ? uplianda_normalize_domain( $options['domain'] ) : '';
	if ( '' === $api_key || '' === $domain ) {
		return new WP_Error( 'uplianda_missing_connection', __( 'Add a Cabin API key and domain in Settings before opening the dashboard.', 'uplink-analytics-dashboard-for-cabin' ) );
	}
	$date_range = in_array( absint( $date_range ), array( 1, 7, 14, 30, 90 ), true ) ? absint( $date_range ) : 30;
	$today      = new DateTimeImmutable( 'today', wp_timezone() );
	$date_to    = $today->format( 'Y-m-d' );
	$date_from  = $today->modify( '-' . ( $date_range - 1 ) . ' days' )->format( 'Y-m-d' );
	$cache_key  = 'uplianda_private_dashboard_' . md5( 'v2|' . $domain . '|' . $date_from . '|' . $date_to );
	$cached     = get_transient( $cache_key );
	if ( false !== $cached ) {
		return $cached;
	}
	$url      = add_query_arg( array( 'domain' => $domain, 'date_from' => $date_from, 'date_to' => $date_to, 'scope' => 'core,pages,referrals,events', 'limit_lists' => 50 ), 'https://api.withcabin.com/v1/analytics' );
	$response = wp_remote_get( $url, array( 'headers' => array( 'x-api-key' => $api_key ), 'timeout' => 20, 'user-agent' => 'WordPress Uplink Analytics Dashboard for Cabin/' . UPLIANDA_VERSION ) );
	if ( is_wp_error( $response ) ) {
		return $response;
	}
	$status = wp_remote_retrieve_response_code( $response );
	if ( 200 !== $status ) {
		return new WP_Error( 'uplianda_api_error', sprintf( /* translators: %d: HTTP status code. */ __( 'Cabin API returned HTTP %d.', 'uplink-analytics-dashboard-for-cabin' ), $status ) );
	}
	$data = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( JSON_ERROR_NONE !== json_last_error() || ! is_array( $data ) ) {
		return new WP_Error( 'uplianda_json_error', __( 'Cabin returned an invalid response.', 'uplink-analytics-dashboard-for-cabin' ) );
	}
	set_transient( $cache_key, $data, 15 * MINUTE_IN_SECONDS );
	return $data;
}

/** Format a Cabin-style approximate duration. */
function uplianda_dashboard_duration( $seconds ) {
	$seconds = max( 0, (int) round( (float) $seconds ) );
	if ( $seconds >= HOUR_IN_SECONDS ) {
		return sprintf( '~%dh %dm', floor( $seconds / HOUR_IN_SECONDS ), floor( ( $seconds % HOUR_IN_SECONDS ) / MINUTE_IN_SECONDS ) );
	}
	if ( $seconds >= MINUTE_IN_SECONDS ) {
		$minutes = floor( $seconds / MINUTE_IN_SECONDS );
		$remain  = $seconds % MINUTE_IN_SECONDS;
		return $remain ? sprintf( '~%dm %ds', $minutes, $remain ) : sprintf( '~%dm', $minutes );
	}
	return sprintf( '~%ds', $seconds );
}

/** Format bytes with a compact unit. */
function uplianda_dashboard_bytes( $bytes ) {
	$bytes = max( 0, (float) $bytes );
	if ( $bytes >= GB_IN_BYTES ) {
		return number_format_i18n( $bytes / GB_IN_BYTES, 1 ) . ' GB';
	}
	if ( $bytes >= MB_IN_BYTES ) {
		return number_format_i18n( $bytes / MB_IN_BYTES, 1 ) . ' MB';
	}
	return number_format_i18n( $bytes / max( 1, KB_IN_BYTES ), 1 ) . ' KB';
}

/** Normalize keyed and row-based Cabin lists. */
function uplianda_dashboard_rows( $values, $label_key = 'name' ) {
	$rows = array();
	if ( ! is_array( $values ) ) {
		return $rows;
	}
	foreach ( $values as $key => $row ) {
		$label = is_array( $row ) ? ( $row[ $label_key ] ?? ( $row['code'] ?? $key ) ) : $key;
		$value = is_array( $row ) ? ( $row['value'] ?? 0 ) : $row;
		if ( '' !== (string) $label ) {
			$rows[] = array( 'label' => (string) $label, 'value' => (float) $value );
		}
	}
	usort( $rows, static fn( $a, $b ) => $b['value'] <=> $a['value'] );
	return $rows;
}

/** Render search and pagination controls for a dashboard list. */
function uplianda_dashboard_list_controls( $list_id, $label, $item_count, $show_value_toggle = false ) {
	if ( $item_count <= 10 ) {
		return;
	}
	$total_pages = (int) ceil( $item_count / 10 );
	?>
	<div class="uplianda-dash-list-tools">
		<label class="uplianda-dash-list-search">
			<span class="screen-reader-text"><?php echo esc_html( sprintf( /* translators: %s: list name. */ __( 'Search %s', 'uplink-analytics-dashboard-for-cabin' ), $label ) ); ?></span>
			<input type="search" placeholder="<?php esc_attr_e( 'Search', 'uplink-analytics-dashboard-for-cabin' ); ?>" autocomplete="off" aria-controls="<?php echo esc_attr( $list_id ); ?>" data-uplianda-list-search />
		</label>
		<div class="uplianda-dash-list-actions">
			<div class="uplianda-dash-list-pagination" role="group" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: list name. */ __( '%s pagination', 'uplink-analytics-dashboard-for-cabin' ), $label ) ); ?>">
				<button type="button" aria-label="<?php esc_attr_e( 'Previous page', 'uplink-analytics-dashboard-for-cabin' ); ?>" data-uplianda-list-previous disabled><svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" width="18" height="18"><path d="m15 18-6-6 6-6" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg></button>
				<output aria-live="polite" data-uplianda-list-page><?php echo esc_html( '1 / ' . $total_pages ); ?></output>
				<button type="button" aria-label="<?php esc_attr_e( 'Next page', 'uplink-analytics-dashboard-for-cabin' ); ?>" data-uplianda-list-next><svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" width="18" height="18"><path d="m9 18 6-6-6-6" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg></button>
			</div>
			<?php if ( $show_value_toggle ) : ?>
			<div class="uplianda-dash-country-modes" role="group" aria-label="<?php esc_attr_e( 'Country value display', 'uplink-analytics-dashboard-for-cabin' ); ?>">
				<button type="button" aria-label="<?php esc_attr_e( 'Show unique visitors', 'uplink-analytics-dashboard-for-cabin' ); ?>" aria-pressed="true" data-uplianda-country-mode="count"><svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" width="18" height="18"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="2.5" fill="none" stroke="currentColor" stroke-width="2"/></svg></button>
				<button type="button" aria-label="<?php esc_attr_e( 'Show percentages', 'uplink-analytics-dashboard-for-cabin' ); ?>" aria-pressed="false" data-uplianda-country-mode="percent">%</button>
			</div>
			<?php endif; ?>
		</div>
	</div>
	<?php
}

/** Render the footer controls for a dashboard list. */
function uplianda_dashboard_list_footer( $list_id, $item_count ) {
	if ( $item_count <= 10 ) {
		return;
	}
	?>
	<p class="uplianda-dash-list-empty" data-uplianda-list-empty hidden><?php esc_html_e( 'No matching items.', 'uplink-analytics-dashboard-for-cabin' ); ?></p>
	<p class="screen-reader-text" aria-live="polite" data-uplianda-list-status></p>
	<div class="uplianda-dash-list-footer">
		<button type="button" aria-controls="<?php echo esc_attr( $list_id ); ?>" aria-pressed="false" data-uplianda-list-view-all data-view-all-text="<?php esc_attr_e( 'View all', 'uplink-analytics-dashboard-for-cabin' ); ?>" data-paginate-text="<?php esc_attr_e( 'Show 10 per page', 'uplink-analytics-dashboard-for-cabin' ); ?>"><svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" width="18" height="18"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="2.5" fill="none" stroke="currentColor" stroke-width="2"/></svg><span data-uplianda-list-view-all-label><?php esc_html_e( 'View all', 'uplink-analytics-dashboard-for-cabin' ); ?></span></button>
	</div>
	<?php
}

/** Render a ranked list with proportional bars. */
function uplianda_dashboard_ranked_list( $rows, $label = '' ) {
	$rows = array_values( $rows );
	usort(
		$rows,
		static function ( $first, $second ) {
			$value_order = (float) ( $second['value'] ?? 0 ) <=> (float) ( $first['value'] ?? 0 );
			if ( 0 !== $value_order ) {
				return $value_order;
			}

			return strnatcasecmp( (string) ( $first['label'] ?? '' ), (string) ( $second['label'] ?? '' ) );
		}
	);

	$maximum = ! empty( $rows ) ? max( 1, (float) $rows[0]['value'] ) : 1;
	if ( empty( $rows ) ) {
		echo '<p class="uplianda-dash-empty">' . esc_html__( 'No data is available for this period.', 'uplink-analytics-dashboard-for-cabin' ) . '</p>';
		return;
	}
	$label   = '' !== $label ? $label : __( 'Report items', 'uplink-analytics-dashboard-for-cabin' );
	$list_id = wp_unique_id( 'uplianda-dashboard-list-' );
	?>
	<div class="uplianda-dash-list" data-uplianda-list data-page-size="10">
	<?php uplianda_dashboard_list_controls( $list_id, $label, count( $rows ) ); ?>
	<ol id="<?php echo esc_attr( $list_id ); ?>" class="uplianda-dash-ranking">
		<?php foreach ( $rows as $row ) : ?>
		<li data-uplianda-list-item><div><span><?php echo esc_html( $row['label'] ); ?></span><strong><?php echo esc_html( number_format_i18n( $row['value'] ) ); ?></strong></div><span class="uplianda-dash-ranking__track" aria-hidden="true"><span style="width:<?php echo esc_attr( max( 1, ( $row['value'] / $maximum ) * 100 ) ); ?>%"></span></span></li>
		<?php endforeach; ?>
	</ol>
	<?php uplianda_dashboard_list_footer( $list_id, count( $rows ) ); ?>
	</div>
	<?php
}

/** Render the site-wide scroll-depth metric and cumulative depth graph. */
function uplianda_dashboard_scroll_depth_stat( $scroll_depth ) {
	$average = (float) ( $scroll_depth['average_percentage'] ?? 0 );
	$buckets = is_array( $scroll_depth['reached_at_least'] ?? null ) ? $scroll_depth['reached_at_least'] : array();
	$buckets = array_values(
		array_filter(
			$buckets,
			static fn( $row ) => is_array( $row ) && isset( $row['percent'], $row['visitors_percentage'] ) && (float) $row['percent'] > 0
		)
	);
	usort( $buckets, static fn( $first, $second ) => (float) $first['percent'] <=> (float) $second['percent'] );
	?>
	<div class="uplianda-dash-stat uplianda-dash-stat--scroll-depth">
		<div class="uplianda-dash-scroll-depth__metric">
			<span><?php esc_html_e( 'Scroll Depth', 'uplink-analytics-dashboard-for-cabin' ); ?></span>
			<strong><?php echo esc_html( number_format_i18n( $average, 0 ) . '%' ); ?></strong>
			<small><?php esc_html_e( 'Average', 'uplink-analytics-dashboard-for-cabin' ); ?></small>
		</div>
		<?php if ( ! empty( $buckets ) ) : ?>
		<div class="uplianda-dash-scroll-depth__visual">
			<div class="uplianda-dash-scroll-depth__bar" style="--uplianda-scroll-average:<?php echo esc_attr( min( 100, max( 0, $average ) ) . '%' ); ?>">
				<?php foreach ( $buckets as $bucket ) :
					$depth = min( 100, max( 0, (float) $bucket['percent'] ) );
					$reach = min( 100, max( 0, (float) $bucket['visitors_percentage'] ) );
					$alpha = 0.18 + ( 0.82 * $reach / 100 );
					$description = sprintf(
						/* translators: 1: percentage of measured visits, 2: page depth percentage. */
						__( '%1$s%% of measured visits reached at least %2$s%% of the page', 'uplink-analytics-dashboard-for-cabin' ),
						number_format_i18n( $reach, 1 ),
						number_format_i18n( $depth, 0 )
					);
					?>
					<button type="button" class="uplianda-dash-scroll-depth__segment" style="--uplianda-scroll-reach:<?php echo esc_attr( number_format( $alpha, 3, '.', '' ) ); ?>" aria-label="<?php echo esc_attr( $description ); ?>">
						<span class="uplianda-dash-scroll-depth__balloon" aria-hidden="true"><strong><?php echo esc_html( number_format_i18n( $reach, 1 ) . '%' ); ?></strong><small><?php esc_html_e( 'got this far', 'uplink-analytics-dashboard-for-cabin' ); ?></small></span>
					</button>
				<?php endforeach; ?>
				<span class="uplianda-dash-scroll-depth__average" aria-hidden="true"></span>
			</div>
		</div>
		<?php endif; ?>
	</div>
	<?php
}

/** Build the page-level data used by the Top Pages preview dialog. */
function uplianda_dashboard_page_details( $pages, $events, $base ) {
	$details = array();

	foreach ( $pages as $index => $page ) {
		$path        = uplianda_normalize_path( (string) ( $page['path'] ?? '/' ) );
		$post        = uplianda_resolve_path_to_post( $path );
		$page_events = array();

		foreach ( $events as $event ) {
			foreach ( is_array( $event['pages'] ?? null ) ? $event['pages'] : array() as $event_page ) {
				if ( $path !== uplianda_normalize_path( (string) ( $event_page['path'] ?? '' ) ) ) {
					continue;
				}
				$page_events[] = array(
					'name'       => (string) ( $event['name'] ?? __( 'Unnamed event', 'uplink-analytics-dashboard-for-cabin' ) ),
					'count'      => (int) ( $event_page['count'] ?? 0 ),
					'percentage' => (float) ( $event_page['percentage'] ?? 0 ),
				);
				break;
			}
		}

		usort(
			$page_events,
			static function ( $left, $right ) {
				$count_order = $right['count'] <=> $left['count'];
				return 0 !== $count_order ? $count_order : strnatcasecmp( $left['name'], $right['name'] );
			}
		);

		$details[ (string) $index ] = array(
			'path'                     => $path,
			'title'                    => $post ? get_the_title( $post ) : uplianda_get_path_title( $path ),
			'url'                      => esc_url_raw( trailingslashit( $base ) . ltrim( $path, '/' ) ),
			'page_views'               => (int) ( $page['page_views'] ?? 0 ),
			'unique_visitors'          => (int) ( $page['unique_visitors'] ?? 0 ),
			'page_views_percentage'    => (float) ( $page['page_views_percentage'] ?? 0 ),
			'average_duration_seconds' => (float) ( $page['average_duration_seconds'] ?? 0 ),
			'total_bytes'              => (float) ( $page['total_bytes'] ?? 0 ),
			'co2_grams'                => (float) ( $page['co2_grams'] ?? 0 ),
			'scroll_depth'             => is_array( $page['scroll_depth'] ?? null ) ? $page['scroll_depth'] : array(),
			'events'                   => $page_events,
		);
	}

	return $details;
}

/** Render the reusable page-level preview dialog. */
function uplianda_dashboard_page_modal( $details ) {
	?>
	<script type="application/json" data-uplianda-page-details><?php echo wp_json_encode( $details, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON is safely hex encoded. ?></script>
	<dialog class="uplianda-dash-page-modal" data-uplianda-page-modal aria-labelledby="uplianda-page-modal-title">
		<div class="uplianda-dash-page-modal__frame">
			<header class="uplianda-dash-page-modal__header">
				<div>
					<h2 id="uplianda-page-modal-title" data-uplianda-page-path></h2>
					<p><span data-uplianda-page-title></span> <span aria-hidden="true">&middot;</span> <a data-uplianda-page-open href="#" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'open', 'uplink-analytics-dashboard-for-cabin' ); ?><span class="screen-reader-text"> <?php esc_html_e( '(opens in a new tab)', 'uplink-analytics-dashboard-for-cabin' ); ?></span><svg aria-hidden="true" viewBox="0 0 24 24" width="15" height="15"><path d="M14 5h5v5M19 5l-9 9M18 13v5a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg></a></p>
				</div>
				<button class="uplianda-dash-page-modal__close" type="button" data-uplianda-page-close aria-label="<?php esc_attr_e( 'Close page details', 'uplink-analytics-dashboard-for-cabin' ); ?>"><svg aria-hidden="true" viewBox="0 0 24 24" width="22" height="22"><path d="m6 6 12 12M18 6 6 18" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="2"/></svg></button>
			</header>
			<div class="uplianda-dash-page-modal__body">
				<section aria-labelledby="uplianda-page-overview-title">
					<h3 id="uplianda-page-overview-title"><?php esc_html_e( 'Page overview', 'uplink-analytics-dashboard-for-cabin' ); ?></h3>
					<div class="uplianda-dash-page-modal__overview">
						<article class="uplianda-dash-page-modal__card">
							<h4><?php esc_html_e( 'Traffic', 'uplink-analytics-dashboard-for-cabin' ); ?></h4>
							<div class="uplianda-dash-page-modal__metrics">
								<div class="uplianda-dash-page-modal__metric"><span><?php esc_html_e( 'Unique Visitors', 'uplink-analytics-dashboard-for-cabin' ); ?></span><strong data-uplianda-page-visitors></strong><small data-uplianda-page-visitor-share></small></div>
								<div class="uplianda-dash-page-modal__metric"><span><?php esc_html_e( 'Pageviews', 'uplink-analytics-dashboard-for-cabin' ); ?></span><strong data-uplianda-page-views></strong><small data-uplianda-page-view-share></small></div>
							</div>
							<p class="uplianda-dash-page-modal__note"><?php esc_html_e( 'Page totals for the selected period. Cabin does not currently provide a page-specific daily series.', 'uplink-analytics-dashboard-for-cabin' ); ?></p>
						</article>
						<article class="uplianda-dash-page-modal__card uplianda-dash-page-modal__attention">
							<h4><?php esc_html_e( 'Attention', 'uplink-analytics-dashboard-for-cabin' ); ?></h4>
							<div class="uplianda-dash-page-modal__attention-layout">
								<div class="uplianda-dash-page-modal__metric"><span><?php esc_html_e( 'Average time spent', 'uplink-analytics-dashboard-for-cabin' ); ?></span><strong data-uplianda-page-duration></strong><small><?php esc_html_e( 'per pageview', 'uplink-analytics-dashboard-for-cabin' ); ?></small></div>
								<div class="uplianda-dash-page-modal__depth" data-uplianda-page-depth-card><div><span><?php esc_html_e( 'Scroll Depth', 'uplink-analytics-dashboard-for-cabin' ); ?></span><strong data-uplianda-page-depth></strong><small><?php esc_html_e( 'Average', 'uplink-analytics-dashboard-for-cabin' ); ?></small></div><div class="uplianda-dash-page-modal__depth-strip" data-uplianda-page-depth-strip aria-hidden="true"></div></div>
							</div>
							<p class="uplianda-dash-page-modal__empty" data-uplianda-page-depth-empty hidden><?php esc_html_e( 'Cabin did not return scroll-depth measurements for this page.', 'uplink-analytics-dashboard-for-cabin' ); ?></p>
						</article>
						<article class="uplianda-dash-page-modal__card" data-uplianda-page-energy-card>
							<h4><?php esc_html_e( 'Energy', 'uplink-analytics-dashboard-for-cabin' ); ?></h4>
							<div class="uplianda-dash-page-modal__metrics"><div class="uplianda-dash-page-modal__metric"><span><?php esc_html_e( 'Page Weight', 'uplink-analytics-dashboard-for-cabin' ); ?></span><strong data-uplianda-page-bytes></strong></div><div class="uplianda-dash-page-modal__metric"><span><?php esc_html_e( 'CO₂', 'uplink-analytics-dashboard-for-cabin' ); ?></span><strong data-uplianda-page-co2></strong></div></div>
						</article>
					</div>
				</section>
				<section class="uplianda-dash-page-modal__events-section" aria-labelledby="uplianda-page-events-title">
					<div class="uplianda-dash-page-modal__section-heading">
						<h3 id="uplianda-page-events-title"><?php esc_html_e( 'Events', 'uplink-analytics-dashboard-for-cabin' ); ?></h3>
						<div class="uplianda-dash-list-pagination" data-uplianda-page-events-pagination role="group" aria-label="<?php esc_attr_e( 'Page events pagination', 'uplink-analytics-dashboard-for-cabin' ); ?>" hidden>
							<button type="button" aria-label="<?php esc_attr_e( 'Previous events page', 'uplink-analytics-dashboard-for-cabin' ); ?>" data-uplianda-page-events-previous disabled><svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" width="18" height="18"><path d="m15 18-6-6 6-6" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg></button>
							<output aria-live="polite" data-uplianda-page-events-page>1 / 1</output>
							<button type="button" aria-label="<?php esc_attr_e( 'Next events page', 'uplink-analytics-dashboard-for-cabin' ); ?>" data-uplianda-page-events-next disabled><svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" width="18" height="18"><path d="m9 18 6-6-6-6" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg></button>
						</div>
					</div>
					<div class="uplianda-dash-card uplianda-dash-page-modal__events"><div class="uplianda-dash-ranked__header"><span><?php esc_html_e( 'Event', 'uplink-analytics-dashboard-for-cabin' ); ?></span><span><?php esc_html_e( 'Count', 'uplink-analytics-dashboard-for-cabin' ); ?></span></div><ol data-uplianda-page-events></ol><p class="uplianda-dash-page-modal__empty" data-uplianda-page-events-empty hidden><?php esc_html_e( 'No events were returned for this page and reporting period.', 'uplink-analytics-dashboard-for-cabin' ); ?></p></div>
				</section>
			</div>
		</div>
	</dialog>
	<?php
}

/** Render the dashboard country data list beneath the map. */
function uplianda_dashboard_country_list( $items ) {
	if ( empty( $items ) ) {
		return;
	}

	$map_data = uplianda_get_country_map_data();
	$geometry = isset( $map_data['countries'] ) && is_array( $map_data['countries'] ) ? $map_data['countries'] : array();
	$maximum  = max( 1, (float) max( array_column( $items, 'value' ) ) );
	$total    = max( 1, (float) array_sum( array_column( $items, 'value' ) ) );
	$list_id  = wp_unique_id( 'uplianda-dashboard-countries-' );
	$label    = __( 'Countries', 'uplink-analytics-dashboard-for-cabin' );
	?>
	<div class="uplianda-dash-country-data uplianda-dash-list" data-uplianda-list data-uplianda-country-list data-page-size="10">
		<?php uplianda_dashboard_list_controls( $list_id, $label, count( $items ), true ); ?>
		<div class="uplianda-dash-country-data__head" aria-hidden="true"><span><?php esc_html_e( 'Country', 'uplink-analytics-dashboard-for-cabin' ); ?></span><span><?php esc_html_e( 'Uniques', 'uplink-analytics-dashboard-for-cabin' ); ?></span></div>
		<ol id="<?php echo esc_attr( $list_id ); ?>" class="uplianda-dash-country-data__list">
			<?php foreach ( $items as $item ) :
				$country_name = isset( $geometry[ $item['code'] ]['name'] ) ? $geometry[ $item['code'] ]['name'] : $item['code'];
				$value        = (float) $item['value'];
				$percent      = ( $value / $total ) * 100;
				$flag_code    = strtolower( sanitize_key( $item['code'] ) );
				$flag_path    = UPLIANDA_PLUGIN_DIR . 'assets/flags/4x3/' . $flag_code . '.svg';
				?>
				<li data-uplianda-list-item><div><span class="uplianda-dash-country-data__country"><?php if ( file_exists( $flag_path ) ) : ?><img src="<?php echo esc_url( UPLIANDA_PLUGIN_URL . 'assets/flags/4x3/' . $flag_code . '.svg' ); ?>" alt="" width="24" height="18" loading="lazy" decoding="async" /><?php endif; ?><span><?php echo esc_html( $country_name ); ?></span></span><strong data-uplianda-country-value data-count="<?php echo esc_attr( number_format_i18n( $value ) ); ?>" data-percent="<?php echo esc_attr( number_format_i18n( $percent, 1 ) . '%' ); ?>"><?php echo esc_html( number_format_i18n( $value ) ); ?></strong></div><span class="uplianda-dash-country-data__track" aria-hidden="true"><span style="width:<?php echo esc_attr( max( 1, ( $value / $maximum ) * 100 ) ); ?>%"></span></span></li>
			<?php endforeach; ?>
		</ol>
		<?php uplianda_dashboard_list_footer( $list_id, count( $items ) ); ?>
	</div>
	<?php
}

/** Build a smooth SVG path that passes through each chart point. */
function uplianda_dashboard_smooth_chart_path( $points ) {
	if ( count( $points ) < 2 ) {
		return '';
	}

	$path  = 'M ' . $points[0][0] . ' ' . $points[0][1];
	$last  = count( $points ) - 1;
	$limit = static fn( $value ) => max( 24, min( 258, $value ) );

	for ( $index = 0; $index < $last; $index++ ) {
		$previous = $points[ max( 0, $index - 1 ) ];
		$current  = $points[ $index ];
		$next     = $points[ $index + 1 ];
		$after    = $points[ min( $last, $index + 2 ) ];
		$control1 = array(
			$current[0] + ( $next[0] - $previous[0] ) / 6,
			$limit( $current[1] + ( $next[1] - $previous[1] ) / 6 ),
		);
		$control2 = array(
			$next[0] - ( $after[0] - $current[0] ) / 6,
			$limit( $next[1] - ( $after[1] - $current[1] ) / 6 ),
		);
		$path    .= ' C ' . $control1[0] . ' ' . $control1[1] . ', ' . $control2[0] . ' ' . $control2[1] . ', ' . $next[0] . ' ' . $next[1];
	}

	return $path;
}

/** Render an accessible SVG chart from daily Cabin data. */
function uplianda_dashboard_chart( $daily, $field = 'page_views', $type = 'bars' ) {
	if ( empty( $daily ) || ! is_array( $daily ) ) {
		echo '<p class="uplianda-dash-empty">' . esc_html__( 'No chart data is available for this period.', 'uplink-analytics-dashboard-for-cabin' ) . '</p>';
		return;
	}
	$values       = array_map( static fn( $row ) => (float) ( $row[ $field ] ?? 0 ), $daily );
	$is_visitors  = 'unique_visitors' === $field;
	$pageviews    = $is_visitors ? array_map( static fn( $row ) => (float) ( $row['page_views'] ?? 0 ), $daily ) : array();
	$scale_values = $is_visitors ? array_merge( $values, $pageviews ) : $values;
	$maximum      = max( 1, max( $scale_values ) );
	$step         = 960 / max( 1, count( $values ) );
	?>
	<div class="uplianda-dash-chart" data-uplianda-chart data-chart-type="<?php echo esc_attr( $type ); ?>" role="img" aria-label="<?php esc_attr_e( 'Daily analytics chart. Hover over a data point for its values. Exact values are also available below.', 'uplink-analytics-dashboard-for-cabin' ); ?>">
		<svg viewBox="0 0 960 300" aria-hidden="true" focusable="false">
			<?php for ( $line = 0; $line < 4; $line++ ) : ?><line class="uplianda-dash-chart__grid" x1="0" x2="960" y1="<?php echo esc_attr( 24 + ( $line * 78 ) ); ?>" y2="<?php echo esc_attr( 24 + ( $line * 78 ) ); ?>"></line><?php endfor; ?>
			<?php if ( 'line' === $type ) : ?>
				<?php if ( $is_visitors ) : $pageview_points = array(); foreach ( $pageviews as $index => $value ) { $pageview_points[] = array( $index * $step + $step / 2, 258 - ( $value / $maximum ) * 220 ); } ?><path class="uplianda-dash-chart__line uplianda-dash-chart__line--secondary" d="<?php echo esc_attr( uplianda_dashboard_smooth_chart_path( $pageview_points ) ); ?>"></path><?php endif; ?>
				<?php $points = array(); foreach ( $values as $index => $value ) { $points[] = array( $index * $step + $step / 2, 258 - ( $value / $maximum ) * 220 ); } ?><path class="uplianda-dash-chart__line" d="<?php echo esc_attr( uplianda_dashboard_smooth_chart_path( $points ) ); ?>"></path>
			<?php else : ?>
				<?php foreach ( $values as $index => $value ) : $bar_height = ( $value / $maximum ) * 220; ?>
					<?php if ( $is_visitors ) : $pageview_height = ( $pageviews[ $index ] / $maximum ) * 220; ?><rect class="uplianda-dash-chart__bar uplianda-dash-chart__bar--secondary" x="<?php echo esc_attr( $index * $step + $step * .18 ); ?>" y="<?php echo esc_attr( 258 - $pageview_height ); ?>" width="<?php echo esc_attr( $step * .64 ); ?>" height="<?php echo esc_attr( $pageview_height ); ?>" rx="3"></rect><?php endif; ?>
					<rect class="uplianda-dash-chart__bar" x="<?php echo esc_attr( $index * $step + $step * .18 ); ?>" y="<?php echo esc_attr( 258 - $bar_height ); ?>" width="<?php echo esc_attr( $step * .64 ); ?>" height="<?php echo esc_attr( $bar_height ); ?>" rx="3"></rect>
				<?php endforeach; ?>
			<?php endif; ?>
			<line class="uplianda-dash-chart__guide" data-uplianda-chart-guide x1="0" x2="0" y1="18" y2="264" hidden></line>
			<circle class="uplianda-dash-chart__point" data-uplianda-chart-point cx="0" cy="0" r="5" hidden></circle>
			<?php foreach ( $daily as $index => $row ) :
				$value          = (float) ( $row[ $field ] ?? 0 );
				$point_x        = $index * $step + $step / 2;
				$point_y        = 258 - ( $value / $maximum ) * 220;
				$primary_value  = 'bounce_rate' === $field ? number_format_i18n( $value * 100, 0 ) . '%' : number_format_i18n( $value );
				$secondary_value = $is_visitors ? number_format_i18n( (float) ( $row['page_views'] ?? 0 ) ) : '';
				?>
				<rect class="uplianda-dash-chart__hit" data-uplianda-chart-hit data-chart-x="<?php echo esc_attr( $point_x ); ?>" data-chart-y="<?php echo esc_attr( $point_y ); ?>" data-chart-date="<?php echo esc_attr( wp_date( 'D, j M Y', (int) floor( (float) ( $row['timestamp'] ?? 0 ) / 1000 ) ) ); ?>" data-primary-label="<?php echo esc_attr( 'bounce_rate' === $field ? __( 'Bounce Rate', 'uplink-analytics-dashboard-for-cabin' ) : __( 'Unique visitors', 'uplink-analytics-dashboard-for-cabin' ) ); ?>" data-primary-value="<?php echo esc_attr( $primary_value ); ?>" data-secondary-label="<?php echo esc_attr( $is_visitors ? __( 'Pageviews', 'uplink-analytics-dashboard-for-cabin' ) : '' ); ?>" data-secondary-value="<?php echo esc_attr( $secondary_value ); ?>" x="<?php echo esc_attr( $index * $step ); ?>" y="0" width="<?php echo esc_attr( $step ); ?>" height="280"></rect>
			<?php endforeach; ?>
		</svg>
		<div class="uplianda-dash-chart__tooltip <?php echo $is_visitors ? 'uplianda-dash-chart__tooltip--multi' : 'uplianda-dash-chart__tooltip--single'; ?>" data-uplianda-chart-tooltip hidden aria-hidden="true"><strong data-uplianda-chart-tooltip-date></strong><span class="uplianda-dash-chart__tooltip-row uplianda-dash-chart__tooltip-row--secondary" data-uplianda-chart-tooltip-secondary hidden><i aria-hidden="true"></i><span data-uplianda-chart-tooltip-secondary-label></span><b data-uplianda-chart-tooltip-secondary-value></b></span><span class="uplianda-dash-chart__tooltip-row" data-uplianda-chart-tooltip-primary><i aria-hidden="true"></i><span data-uplianda-chart-tooltip-primary-label></span><b data-uplianda-chart-tooltip-primary-value></b></span></div>
		<div class="uplianda-dash-chart__labels" aria-hidden="true"><?php $label_step = count( $daily ) > 14 ? 5 : 1; foreach ( $daily as $index => $row ) : if ( 0 !== $index % $label_step && $index !== count( $daily ) - 1 ) { continue; } ?><span><?php echo esc_html( wp_date( 'M j', (int) floor( (float) ( $row['timestamp'] ?? 0 ) / 1000 ) ) ); ?></span><?php endforeach; ?></div>
	</div>
	<details class="uplianda-dash-data-table"><summary><?php esc_html_e( 'View daily values', 'uplink-analytics-dashboard-for-cabin' ); ?></summary><table><thead><tr><th scope="col"><?php esc_html_e( 'Date', 'uplink-analytics-dashboard-for-cabin' ); ?></th><th scope="col"><?php esc_html_e( 'Value', 'uplink-analytics-dashboard-for-cabin' ); ?></th></tr></thead><tbody><?php foreach ( $daily as $row ) : ?><tr><th scope="row"><?php echo esc_html( wp_date( get_option( 'date_format' ), (int) floor( (float) ( $row['timestamp'] ?? 0 ) / 1000 ) ) ); ?></th><td><?php echo esc_html( number_format_i18n( (float) ( $row[ $field ] ?? 0 ), 'bounce_rate' === $field ? 2 : 0 ) ); ?></td></tr><?php endforeach; ?></tbody></table></details>
	<?php
}

/** Render a chart panel with an accessible line/bar view control. */
function uplianda_dashboard_chart_panel( $title, $daily, $field = 'page_views', $default_type = 'bars' ) {
	$default_type = 'line' === $default_type ? 'line' : 'bars';
	?>
	<div class="uplianda-dash-card uplianda-dash-card--chart uplianda-dash-card--interactive-chart" data-uplianda-chart-panel>
		<header class="uplianda-dash-chart-header">
			<h3><?php echo esc_html( $title ); ?></h3>
			<div class="uplianda-dash-chart-controls" role="group" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: chart title. */ __( '%s chart type', 'uplink-analytics-dashboard-for-cabin' ), $title ) ); ?>">
				<button type="button" data-uplianda-chart-type="line" aria-pressed="<?php echo 'line' === $default_type ? 'true' : 'false'; ?>" aria-label="<?php esc_attr_e( 'Line chart', 'uplink-analytics-dashboard-for-cabin' ); ?>" title="<?php esc_attr_e( 'Line chart', 'uplink-analytics-dashboard-for-cabin' ); ?>"><svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" width="18" height="18"><path d="M4 18V6m0 12h16M7 14l4-4 3 2 5-6" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg></button>
				<button type="button" data-uplianda-chart-type="bars" aria-pressed="<?php echo 'bars' === $default_type ? 'true' : 'false'; ?>" aria-label="<?php esc_attr_e( 'Bar chart', 'uplink-analytics-dashboard-for-cabin' ); ?>" title="<?php esc_attr_e( 'Bar chart', 'uplink-analytics-dashboard-for-cabin' ); ?>"><svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" width="18" height="18"><path d="M5 19V11m5 8V5m5 14V8m5 11V3" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="2"/></svg></button>
			</div>
		</header>
		<div class="uplianda-dash-chart-view" data-uplianda-chart-view="line"<?php echo 'line' === $default_type ? '' : ' hidden'; ?>><?php uplianda_dashboard_chart( $daily, $field, 'line' ); ?></div>
		<div class="uplianda-dash-chart-view" data-uplianda-chart-view="bars"<?php echo 'bars' === $default_type ? '' : ' hidden'; ?>><?php uplianda_dashboard_chart( $daily, $field, 'bars' ); ?></div>
	</div>
	<?php
}

/** Render a dashboard section heading. */
function uplianda_dashboard_section_heading( $id, $title, $description, $range ) {
	?>
	<header class="uplianda-dash-section__heading"><div><h2 id="<?php echo esc_attr( $id . '-title' ); ?>"><?php echo esc_html( $title ); ?></h2><span class="uplianda-dash-section__rule" aria-hidden="true"></span><p class="uplianda-dash-section__eyebrow"><?php echo esc_html( $description ); ?></p></div><span><?php echo esc_html( sprintf( /* translators: %d: number of days. */ _n( 'Last %d day', 'Last %d days', $range, 'uplink-analytics-dashboard-for-cabin' ), $range ) ); ?></span></header>
	<?php
}

/** Render the dashboard header and navigation. */
function uplianda_dashboard_open( $domain, $range, $data, $dashboard_url ) {
	?>
	<div class="wrap uplianda-private-dashboard" data-uplianda-dashboard>
	<a class="uplianda-dash-skip" href="#uplianda-dash-main"><?php esc_html_e( 'Skip to dashboard reports', 'uplink-analytics-dashboard-for-cabin' ); ?></a>
	<header class="uplianda-dash-topbar"><div><p class="uplianda-dash-brand"><?php esc_html_e( 'Cabin Analytics For', 'uplink-analytics-dashboard-for-cabin' ); ?></p><h1><?php echo esc_html( $domain ?: __( 'Cabin Dashboard', 'uplink-analytics-dashboard-for-cabin' ) ); ?></h1></div><div class="uplianda-dash-toolbar">
	<nav class="uplianda-dash-ranges" aria-label="<?php esc_attr_e( 'Reporting period', 'uplink-analytics-dashboard-for-cabin' ); ?>"><?php foreach ( array( 1, 7, 14, 30, 90 ) as $days ) : ?><a href="<?php echo esc_url( add_query_arg( array( 'page' => 'uplianda-dashboard', 'range' => $days ), admin_url( 'admin.php' ) ) ); ?>"<?php echo $range === $days ? ' aria-current="page"' : ''; ?>><?php echo esc_html( 1 === $days ? __( 'Today', 'uplink-analytics-dashboard-for-cabin' ) : sprintf( /* translators: %d: number of days. */ _n( '%d day', '%d days', $days, 'uplink-analytics-dashboard-for-cabin' ), $days ) ); ?></a><?php endforeach; ?></nav>
	<button type="button" class="uplianda-dash-theme" data-uplianda-theme-toggle aria-label="<?php esc_attr_e( 'Switch to dark mode', 'uplink-analytics-dashboard-for-cabin' ); ?>" title="<?php esc_attr_e( 'Switch to dark mode', 'uplink-analytics-dashboard-for-cabin' ); ?>" aria-pressed="false"><span data-uplianda-theme-icon aria-hidden="true">◐</span><span class="screen-reader-text" data-uplianda-theme-label><?php esc_html_e( 'Switch to dark mode', 'uplink-analytics-dashboard-for-cabin' ); ?></span></button>
	<a class="uplianda-dash-cabin" href="<?php echo esc_url( $dashboard_url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'Open Cabin dashboard in a new tab', 'uplink-analytics-dashboard-for-cabin' ); ?>" title="<?php esc_attr_e( 'Open Cabin dashboard', 'uplink-analytics-dashboard-for-cabin' ); ?>"><svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" width="18" height="18"><path d="M14 5h5v5M19 5l-9 9M18 13v5a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg><span class="screen-reader-text"><?php esc_html_e( 'Cabin dashboard (opens in a new tab)', 'uplink-analytics-dashboard-for-cabin' ); ?></span></a>
	<?php if ( current_user_can( 'manage_options' ) ) : ?><a class="uplianda-dash-settings" href="<?php echo esc_url( admin_url( 'admin.php?page=uplianda-settings' ) ); ?>" aria-label="<?php esc_attr_e( 'Dashboard settings', 'uplink-analytics-dashboard-for-cabin' ); ?>" title="<?php esc_attr_e( 'Dashboard settings', 'uplink-analytics-dashboard-for-cabin' ); ?>"><svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" width="18" height="18"><path d="M4 7h10m4 0h2M4 17h2m4 0h10M14 4v6M6 14v6" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="2"/></svg><span class="screen-reader-text"><?php esc_html_e( 'Settings', 'uplink-analytics-dashboard-for-cabin' ); ?></span></a><?php endif; ?>
	</div></header>
	<div class="uplianda-dash-shell"><nav class="uplianda-dash-rail" aria-label="<?php esc_attr_e( 'Dashboard sections', 'uplink-analytics-dashboard-for-cabin' ); ?>"><p><?php esc_html_e( 'Reports', 'uplink-analytics-dashboard-for-cabin' ); ?></p>
	<?php
	$links = array( 'summary' => __( 'Summary', 'uplink-analytics-dashboard-for-cabin' ) );
	if ( ! empty( $data['energy'] ) || ! empty( $data['scroll_depth'] ) ) { $links['attention'] = __( 'Attention', 'uplink-analytics-dashboard-for-cabin' ); }
	$links += array( 'pages' => __( 'Top Pages', 'uplink-analytics-dashboard-for-cabin' ), 'referrals' => __( 'Referrals', 'uplink-analytics-dashboard-for-cabin' ), 'sources' => __( 'Sources', 'uplink-analytics-dashboard-for-cabin' ) );
	if ( ! empty( $data['ai_agents'] ) ) { $links['ai-agents'] = __( 'AI agents', 'uplink-analytics-dashboard-for-cabin' ); }
	if ( ! empty( $data['energy'] ) ) { $links['energy'] = __( 'Energy & Carbon', 'uplink-analytics-dashboard-for-cabin' ); }
	$links += array( 'bounce' => __( 'Bounce Rate', 'uplink-analytics-dashboard-for-cabin' ), 'countries' => __( 'Countries', 'uplink-analytics-dashboard-for-cabin' ), 'languages' => __( 'Languages', 'uplink-analytics-dashboard-for-cabin' ), 'devices' => __( 'Devices', 'uplink-analytics-dashboard-for-cabin' ), 'screens' => __( 'Desktop Screen Sizes', 'uplink-analytics-dashboard-for-cabin' ), 'browsers' => __( 'Browsers', 'uplink-analytics-dashboard-for-cabin' ), 'operating-systems' => __( 'OS', 'uplink-analytics-dashboard-for-cabin' ) );
	if ( array_key_exists( 'events', $data ) ) { $links['events'] = __( 'Events', 'uplink-analytics-dashboard-for-cabin' ); }
	foreach ( $links as $id => $label ) : ?><a href="#<?php echo esc_attr( $id ); ?>"<?php echo 'summary' === $id ? ' aria-current="location"' : ''; ?>><?php echo esc_html( $label ); ?></a><?php endforeach; ?>
	</nav><main id="uplianda-dash-main" class="uplianda-dash-main">
	<?php
}

/** Open a dashboard report section. */
function uplianda_dashboard_section_open( $id, $title, $description, $range, $class = '' ) {
	echo '<section id="' . esc_attr( $id ) . '" class="uplianda-dash-section ' . esc_attr( $class ) . '" aria-labelledby="' . esc_attr( $id . '-title' ) . '">';
	uplianda_dashboard_section_heading( $id, $title, $description, $range );
}

/** Render the standalone private dashboard. */
function uplianda_render_private_dashboard() {
	if ( ! current_user_can( uplianda_manage_capability() ) ) {
		wp_die( esc_html__( 'You do not have permission to access the Uplink Dashboard for Cabin.', 'uplink-analytics-dashboard-for-cabin' ) );
	}
	$range   = uplianda_get_dashboard_range();
	$data    = uplianda_get_private_dashboard_data( $range );
	$options = uplianda_get_options();
	$domain  = isset( $options['domain'] ) ? uplianda_normalize_domain( $options['domain'] ) : '';
	if ( is_wp_error( $data ) ) {
		echo '<div class="wrap uplianda-private-dashboard"><main class="uplianda-dash-error"><h1>' . esc_html__( 'Dashboard unavailable', 'uplink-analytics-dashboard-for-cabin' ) . '</h1><p>' . esc_html( $data->get_error_message() ) . '</p></main></div>';
		return;
	}
	$base    = 'https://' . $domain;
	$summary = is_array( $data['summary'] ?? null ) ? $data['summary'] : array();
	$dashboard_url = ! empty( $options['dashboard_url'] ) ? $options['dashboard_url'] : UPLIANDA_CABIN_URL;
	uplianda_dashboard_open( $domain, $range, $data, $dashboard_url );

	uplianda_dashboard_section_open( 'summary', __( 'Summary', 'uplink-analytics-dashboard-for-cabin' ), __( 'Audience overview for the selected period.', 'uplink-analytics-dashboard-for-cabin' ), $range );
	?>
	<div class="uplianda-dash-report uplianda-dash-report--summary">
	<div class="uplianda-dash-summary"><div class="uplianda-dash-stat"><span><?php esc_html_e( 'Pageviews', 'uplink-analytics-dashboard-for-cabin' ); ?></span><strong><?php echo esc_html( number_format_i18n( (float) ( $summary['page_views'] ?? 0 ) ) ); ?></strong></div><div class="uplianda-dash-stat"><span><?php esc_html_e( 'Unique Visitors', 'uplink-analytics-dashboard-for-cabin' ); ?></span><strong><?php echo esc_html( number_format_i18n( (float) ( $summary['unique_visitors'] ?? 0 ) ) ); ?></strong><?php if ( ! empty( $summary['page_views'] ) ) : ?><small><?php echo esc_html( number_format_i18n( ( (float) ( $summary['unique_visitors'] ?? 0 ) / (float) $summary['page_views'] ) * 100, 0 ) . '% ' . __( 'of pageviews', 'uplink-analytics-dashboard-for-cabin' ) ); ?></small><?php endif; ?></div><div class="uplianda-dash-stat"><span><?php esc_html_e( 'Bounce Rate', 'uplink-analytics-dashboard-for-cabin' ); ?></span><strong><?php echo esc_html( number_format_i18n( (float) ( $summary['bounce_rate'] ?? 0 ) * 100, 1 ) . '%' ); ?></strong></div></div>
	<?php uplianda_dashboard_chart_panel( __( 'Visitors', 'uplink-analytics-dashboard-for-cabin' ), $data['daily_data'] ?? array(), 'unique_visitors', 'bars' ); ?></div></section>

	<?php
	if ( ! empty( $data['energy'] ) || ! empty( $data['scroll_depth'] ) ) {
		$energy       = is_array( $data['energy'] ?? null ) ? $data['energy'] : array();
		$scroll_depth = is_array( $data['scroll_depth'] ?? null ) ? $data['scroll_depth'] : array();
		uplianda_dashboard_section_open( 'attention', __( 'Attention', 'uplink-analytics-dashboard-for-cabin' ), __( 'Time and scroll engagement', 'uplink-analytics-dashboard-for-cabin' ), $range );
		?>
		<div class="uplianda-dash-summary uplianda-dash-summary--attention">
			<?php if ( ! empty( $energy ) ) : ?>
			<div class="uplianda-dash-stat"><span><?php esc_html_e( 'Average Time Spent / Page', 'uplink-analytics-dashboard-for-cabin' ); ?></span><strong><?php echo esc_html( ltrim( uplianda_dashboard_duration( ( $energy['average_time_spent_ms'] ?? 0 ) / 1000 ), '~' ) ); ?></strong></div>
			<div class="uplianda-dash-stat"><span><?php esc_html_e( 'Total Time Spent', 'uplink-analytics-dashboard-for-cabin' ); ?></span><strong><?php echo esc_html( ltrim( uplianda_dashboard_duration( ( $energy['duration_total_ms'] ?? 0 ) / 1000 ), '~' ) ); ?></strong></div>
			<?php endif; ?>
			<?php if ( ! empty( $scroll_depth ) ) : uplianda_dashboard_scroll_depth_stat( $scroll_depth ); endif; ?>
		</div></section>
		<?php
	}

	$pages        = is_array( $data['pages'] ?? null ) ? array_values( $data['pages'] ) : array();
	$pages_id     = wp_unique_id( 'uplianda-dashboard-pages-' );
	$pages_label  = __( 'Top Pages', 'uplink-analytics-dashboard-for-cabin' );
	$page_details = uplianda_dashboard_page_details( $pages, is_array( $data['events'] ?? null ) ? $data['events'] : array(), $base );
	$page_views_total = max( 1, array_sum( array_map( static fn( $page ) => (float) ( $page['page_views'] ?? 0 ), $pages ) ) );
	uplianda_dashboard_section_open( 'pages', $pages_label, __( 'Pages ranked by visits and attention.', 'uplink-analytics-dashboard-for-cabin' ), $range );
	?>
	<div class="uplianda-dash-card uplianda-dash-list" data-uplianda-list data-page-size="10">
		<?php uplianda_dashboard_list_controls( $pages_id, $pages_label, count( $pages ) ); ?>
		<div class="uplianda-dash-table-wrap">
			<table id="<?php echo esc_attr( $pages_id ); ?>"><thead><tr><th scope="col"><?php esc_html_e( 'Page', 'uplink-analytics-dashboard-for-cabin' ); ?></th><th scope="col"><?php esc_html_e( 'Views', 'uplink-analytics-dashboard-for-cabin' ); ?></th><th scope="col"><?php esc_html_e( 'Visitors', 'uplink-analytics-dashboard-for-cabin' ); ?></th><th scope="col"><?php esc_html_e( 'Attention', 'uplink-analytics-dashboard-for-cabin' ); ?></th></tr></thead><tbody>
			<?php foreach ( $pages as $page_index => $page ) :
				$path       = (string) ( $page['path'] ?? '/' );
				$page_views = (float) ( $page['page_views'] ?? 0 );
				$share      = max( 1, ( $page_views / $page_views_total ) * 100 );
				?>
				<tr class="uplianda-dash-pages__row" data-uplianda-list-item><th scope="row"><button class="uplianda-dash-page-peek" type="button" data-uplianda-page-open-modal="<?php echo esc_attr( $page_index ); ?>"><?php echo esc_html( $path ); ?><span class="screen-reader-text"> <?php esc_html_e( 'View page details', 'uplink-analytics-dashboard-for-cabin' ); ?></span></button></th><td><?php echo esc_html( number_format_i18n( $page_views ) ); ?></td><td><?php echo esc_html( number_format_i18n( (float) ( $page['unique_visitors'] ?? 0 ) ) ); ?></td><td><?php echo esc_html( uplianda_dashboard_duration( $page['average_duration_seconds'] ?? 0 ) ); ?></td></tr>
				<tr class="uplianda-dash-pages__measure" data-uplianda-list-measure aria-hidden="true"><td colspan="4"><span><i style="width:<?php echo esc_attr( $share ); ?>%"></i></span></td></tr>
			<?php endforeach; ?>
			</tbody></table>
		</div>
		<?php uplianda_dashboard_list_footer( $pages_id, count( $pages ) ); ?>
	</div></section>
	<?php uplianda_dashboard_page_modal( $page_details ); ?>

	<?php
	uplianda_dashboard_section_open( 'referrals', __( 'Referrals', 'uplink-analytics-dashboard-for-cabin' ), __( 'Where visitors came from', 'uplink-analytics-dashboard-for-cabin' ), $range );
	$referrals = array_map( static fn( $row ) => array( 'label' => (string) ( $row['source'] ?? __( 'Unknown', 'uplink-analytics-dashboard-for-cabin' ) ), 'value' => (float) ( $row['page_views'] ?? 0 ) ), $data['referrals'] ?? array() );
	echo '<div class="uplianda-dash-card">'; uplianda_dashboard_ranked_list( $referrals, __( 'Referrals', 'uplink-analytics-dashboard-for-cabin' ) ); echo '</div></section>';

	uplianda_dashboard_section_open( 'sources', __( 'Sources', 'uplink-analytics-dashboard-for-cabin' ), __( 'Acquisition channels', 'uplink-analytics-dashboard-for-cabin' ), $range );
	echo '<div class="uplianda-dash-summary uplianda-dash-summary--sources">';
	foreach ( uplianda_dashboard_rows( $data['traffic_sources'] ?? array() ) as $source ) {
		echo '<div class="uplianda-dash-stat"><span>' . esc_html( ucwords( str_replace( '_', ' ', $source['label'] ) ) ) . '</span><strong>' . esc_html( number_format_i18n( $source['value'] ) ) . '</strong></div>';
	}
	echo '</div></section>';

	if ( ! empty( $data['ai_agents'] ) ) {
		uplianda_dashboard_section_open( 'ai-agents', __( 'AI agents', 'uplink-analytics-dashboard-for-cabin' ), __( 'Automated traffic', 'uplink-analytics-dashboard-for-cabin' ), $range );
		echo '<div class="uplianda-dash-card">'; uplianda_dashboard_ranked_list( uplianda_dashboard_rows( $data['ai_agents'] ), __( 'AI agents', 'uplink-analytics-dashboard-for-cabin' ) ); echo '</div></section>';
	}

	if ( ! empty( $data['energy'] ) ) {
		$energy = $data['energy'];
		uplianda_dashboard_section_open( 'energy', __( 'Energy & Carbon', 'uplink-analytics-dashboard-for-cabin' ), __( 'Estimated transfer and carbon impact.', 'uplink-analytics-dashboard-for-cabin' ), $range );
		?>
		<div class="uplianda-dash-report">
		<div class="uplianda-dash-card uplianda-dash-green"><span><?php esc_html_e( 'Green hosting', 'uplink-analytics-dashboard-for-cabin' ); ?></span><strong><?php echo ! empty( $energy['green_hosting']['green'] ) ? esc_html__( 'Verified', 'uplink-analytics-dashboard-for-cabin' ) : esc_html__( 'Not verified', 'uplink-analytics-dashboard-for-cabin' ); ?></strong></div>
		<div class="uplianda-dash-summary"><div class="uplianda-dash-stat"><span><?php esc_html_e( 'Transferred data', 'uplink-analytics-dashboard-for-cabin' ); ?></span><strong><?php echo esc_html( uplianda_dashboard_bytes( $energy['transferred_bytes'] ?? 0 ) ); ?></strong></div><div class="uplianda-dash-stat"><span><?php esc_html_e( 'Total CO₂', 'uplink-analytics-dashboard-for-cabin' ); ?></span><strong><?php echo esc_html( number_format_i18n( (float) ( $energy['total_co2_grams'] ?? 0 ), 2 ) . ' g' ); ?></strong></div><div class="uplianda-dash-stat"><span><?php esc_html_e( 'Average CO₂ per page', 'uplink-analytics-dashboard-for-cabin' ); ?></span><strong><?php echo esc_html( number_format_i18n( (float) ( $energy['average_co2_grams'] ?? 0 ), 4 ) . ' g' ); ?></strong></div></div></div></section>
		<?php
	}

	uplianda_dashboard_section_open( 'bounce', __( 'Bounce Rate', 'uplink-analytics-dashboard-for-cabin' ), __( 'Daily bounce rate for the selected period.', 'uplink-analytics-dashboard-for-cabin' ), $range );
	uplianda_dashboard_chart_panel( __( 'Bounce Rate', 'uplink-analytics-dashboard-for-cabin' ), $data['daily_data'] ?? array(), 'bounce_rate', 'line' );
	echo '</section>';

	uplianda_dashboard_section_open( 'countries', __( 'Countries', 'uplink-analytics-dashboard-for-cabin' ), __( 'Geographic distribution', 'uplink-analytics-dashboard-for-cabin' ), $range );
	$countries = uplianda_prepare_country_traffic( $data['countries'] ?? array() );
	?>
	<div class="uplianda-dash-card uplianda-dash-card--map uplianda-dash-country-card">
		<header class="uplianda-dash-country-card__header"><svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" width="20" height="20"><circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M3 12h18M12 3c2.4 2.5 3.6 5.5 3.6 9s-1.2 6.5-3.6 9c-2.4-2.5-3.6-5.5-3.6-9S9.6 5.5 12 3Z" fill="none" stroke="currentColor" stroke-width="1.8"/></svg><h3><?php esc_html_e( 'Global reach', 'uplink-analytics-dashboard-for-cabin' ); ?></h3></header>
		<div class="uplianda-dash-country-card__body">
			<?php echo uplianda_render_country_map( $countries, $range, __( 'Global reach', 'uplink-analytics-dashboard-for-cabin' ), __( 'unique visitors', 'uplink-analytics-dashboard-for-cabin' ), true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by renderer. ?>
			<?php uplianda_dashboard_country_list( $countries ); ?>
		</div>
	</div></section>
	<?php

	$ranked_sections = array(
		'languages'         => array( __( 'Languages', 'uplink-analytics-dashboard-for-cabin' ), __( 'Browser language preferences', 'uplink-analytics-dashboard-for-cabin' ), uplianda_dashboard_rows( $data['languages'] ?? array(), 'code' ) ),
		'devices'           => array( __( 'Devices', 'uplink-analytics-dashboard-for-cabin' ), __( 'Device categories', 'uplink-analytics-dashboard-for-cabin' ), uplianda_dashboard_rows( $data['devices'] ?? array() ) ),
		'screens'           => array( __( 'Desktop Screen Sizes', 'uplink-analytics-dashboard-for-cabin' ), __( 'Reported desktop viewport groups.', 'uplink-analytics-dashboard-for-cabin' ), uplianda_dashboard_rows( $data['screen_sizes'] ?? array() ) ),
		'browsers'          => array( __( 'Browsers', 'uplink-analytics-dashboard-for-cabin' ), __( 'Visitor software', 'uplink-analytics-dashboard-for-cabin' ), uplianda_dashboard_rows( $data['browsers'] ?? array() ) ),
		'operating-systems' => array( __( 'OS', 'uplink-analytics-dashboard-for-cabin' ), __( 'Visitor operating systems.', 'uplink-analytics-dashboard-for-cabin' ), uplianda_dashboard_rows( $data['operating_systems'] ?? array() ) ),
	);
	foreach ( $ranked_sections as $id => $section ) {
		uplianda_dashboard_section_open( $id, $section[0], $section[1], $range ); echo '<div class="uplianda-dash-card">'; uplianda_dashboard_ranked_list( $section[2], $section[0] ); echo '</div></section>';
	}

	if ( array_key_exists( 'events', $data ) ) {
		uplianda_dashboard_section_open( 'events', __( 'Events', 'uplink-analytics-dashboard-for-cabin' ), __( 'Tracked interactions', 'uplink-analytics-dashboard-for-cabin' ), $range );
		$events = array_map( static fn( $row ) => array( 'label' => (string) ( $row['name'] ?? __( 'Unnamed event', 'uplink-analytics-dashboard-for-cabin' ) ), 'value' => (float) ( $row['count'] ?? 0 ) ), $data['events'] ?? array() );
		echo '<div class="uplianda-dash-card">'; uplianda_dashboard_ranked_list( $events, __( 'Events', 'uplink-analytics-dashboard-for-cabin' ) ); echo '</div></section>';
	}
	echo '</main></div></div>';
}

/** Render the WordPress dashboard widgets setting. */
function uplianda_dashboard_widgets_enabled_render() {
	$options = uplianda_get_options();
	?>
	<input type="hidden" name="uplianda_options[dashboard_widgets_enabled]" value="0" />
	<label class="uplianda-setting-check" for="uplianda_dashboard_widgets_enabled"><input id="uplianda_dashboard_widgets_enabled" data-uplianda-widget-toggle type="checkbox" name="uplianda_options[dashboard_widgets_enabled]" value="1" <?php checked( ! empty( $options['dashboard_widgets_enabled'] ) ); ?> /> <span><?php esc_html_e( 'Show the five Cabin widgets on the WordPress Dashboard.', 'uplink-analytics-dashboard-for-cabin' ); ?></span></label>
	<p class="uplianda-description"><?php esc_html_e( 'Turning this off unregisters the widgets and removes them from Screen Options. It does not affect the Cabin Dashboard, blocks, or shortcodes.', 'uplink-analytics-dashboard-for-cabin' ); ?></p>
	<?php
}

/** Render dashboard role settings. */
function uplianda_access_roles_render() {
	$selected = uplianda_get_access_roles();
	?>
	<fieldset class="uplianda-access-roles"><legend class="screen-reader-text"><?php esc_html_e( 'Roles allowed to view the Cabin Dashboard', 'uplink-analytics-dashboard-for-cabin' ); ?></legend>
	<?php foreach ( wp_roles()->roles as $slug => $role_data ) : $administrator = 'administrator' === $slug; ?><label><input type="checkbox" name="uplianda_options[dashboard_access_roles][]" value="<?php echo esc_attr( $slug ); ?>" <?php checked( in_array( $slug, $selected, true ) ); ?> <?php disabled( $administrator ); ?> /> <?php echo esc_html( translate_user_role( $role_data['name'] ) ); ?></label><?php if ( $administrator ) : ?><input type="hidden" name="uplianda_options[dashboard_access_roles][]" value="administrator" /><?php endif; endforeach; ?>
	</fieldset><p class="uplianda-description"><?php esc_html_e( 'Administrators always retain access. Select any additional roles that may view the Cabin Dashboard.', 'uplink-analytics-dashboard-for-cabin' ); ?></p>
	<?php
}

/** Private dashboard settings section description. */
function uplianda_private_section_callback() {
	echo '<p class="uplianda-section-description">' . esc_html__( 'Set the external Cabin link and choose who can open the analytics dashboard inside WordPress.', 'uplink-analytics-dashboard-for-cabin' ) . '</p>';
}
