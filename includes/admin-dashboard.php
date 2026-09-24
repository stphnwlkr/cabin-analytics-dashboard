<?php
/**
 * Private, block-editor-driven Uplink Dashboard for Cabin.
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
		wp_register_icon_collection(
			'uplinkpress',
			array(
				'label'       => __( 'UplinkPress', 'uplink-analytics-dashboard-for-cabin' ),
				'description' => __( 'Icons provided by UplinkPress plugins.', 'uplink-analytics-dashboard-for-cabin' ),
			)
		);
	}

	$icons = WP_Icons_Registry::get_instance();
	if ( ! $icons->is_registered( 'uplinkpress/chart-analysis' ) ) {
		wp_register_icon(
			'uplinkpress/chart-analysis',
			array(
				'label'     => __( 'Chart analysis', 'uplink-analytics-dashboard-for-cabin' ),
				'file_path' => UPLIANDA_PLUGIN_DIR . 'assets/branding/chart-analysis.svg',
			)
		);
	}
}
add_action( 'init', 'uplianda_register_icons', 1 );

/** Return the registered chart icon markup when the WordPress API is available. */
function uplianda_chart_icon_markup( $size = 24, $class = '' ) {
	if ( ! function_exists( 'wp_get_icon' ) ) {
		return '';
	}

	return wp_get_icon(
		'uplinkpress/chart-analysis',
		array(
			'size'  => $size,
			'class' => $class,
		)
	);
}

/** URL for the plugin's chart-analysis admin icon. */
function uplianda_admin_menu_icon_url() {
	$icon = uplianda_chart_icon_markup( 20 );
	if ( '' !== $icon ) {
		return 'data:image/svg+xml;base64,' . base64_encode( $icon );
	}

	return UPLIANDA_PLUGIN_URL . 'assets/branding/chart-analysis.svg';
}

/** Dashboard layout post type. */
function uplianda_layout_post_type() {
	return 'uplianda_dash';
}

/** Capability required to view and edit the private dashboard. */
function uplianda_manage_capability() {
	return 'uplianda_manage_dashboard';
}

/** Whether the optional private dashboard interface is enabled. */
function uplianda_private_dashboard_enabled() {
	$options = uplianda_get_options();
	return ! empty( $options['private_dashboard_enabled'] );
}

/** Register the private dashboard layout for the core block editor. */
function uplianda_register_layout_post_type() {
	$capability = uplianda_manage_capability();
	$capabilities = array(
		'edit_post'              => $capability,
		'read_post'              => $capability,
		'delete_post'            => $capability,
		'edit_posts'             => $capability,
		'edit_others_posts'      => $capability,
		'delete_posts'           => $capability,
		'publish_posts'          => $capability,
		'read_private_posts'     => $capability,
		// The plugin creates and manages one canonical layout internally.
		'create_posts'           => 'do_not_allow',
		'delete_private_posts'   => $capability,
		'delete_published_posts' => $capability,
		'delete_others_posts'    => $capability,
		'edit_private_posts'     => $capability,
		'edit_published_posts'   => $capability,
	);

	register_post_type(
		uplianda_layout_post_type(),
		array(
			'labels' => array(
				'name'          => __( 'Uplink Dashboard for Cabin Pages', 'uplink-analytics-dashboard-for-cabin' ),
				'singular_name' => __( 'Dashboard', 'uplink-analytics-dashboard-for-cabin' ),
				'edit_item'     => __( 'Edit Uplink Dashboard for Cabin', 'uplink-analytics-dashboard-for-cabin' ),
				'add_new'       => __( 'Add New', 'uplink-analytics-dashboard-for-cabin' ),
				'add_new_item'  => __( 'A second dashboard cannot be created.', 'uplink-analytics-dashboard-for-cabin' ),
			),
			'public'              => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'show_ui'             => true,
			'show_in_menu'        => false,
			'show_in_nav_menus'   => false,
			'show_in_rest'        => true,
			'rest_base'           => 'uplianda-dashboard-layouts',
			'has_archive'         => false,
			'rewrite'             => false,
			'query_var'           => false,
			'supports'            => array( 'title', 'editor', 'revisions' ),
			'capabilities'        => $capabilities,
			'map_meta_cap'        => false,
			'menu_icon'           => uplianda_admin_menu_icon_url(),
		)
	);
}
add_action( 'init', 'uplianda_register_layout_post_type' );

/**
 * Require dashboard authorization for every layout REST request.
 *
 * The core posts controller otherwise exposes an empty collection to anonymous
 * requests for non-public post types. The block editor still works normally for
 * authorized users.
 *
 * @param mixed           $response Current pre-dispatch response.
 * @param WP_REST_Server  $server   REST server instance.
 * @param WP_REST_Request $request  REST request.
 * @return mixed Original response or authorization error.
 */
function uplianda_protect_layout_rest_routes( $response, $server, $request ) {
	unset( $server );

	$route_prefix = '/wp/v2/uplianda-dashboard-layouts';
	if ( ! str_starts_with( $request->get_route(), $route_prefix ) ) {
		return $response;
	}

	if ( current_user_can( uplianda_manage_capability() ) ) {
		return $response;
	}

	return new WP_Error(
		'uplianda_rest_forbidden',
		__( 'You do not have permission to access the Uplink Dashboard for Cabin layout.', 'uplink-analytics-dashboard-for-cabin' ),
		array( 'status' => rest_authorization_required_code() )
	);
}
add_filter( 'rest_pre_dispatch', 'uplianda_protect_layout_rest_routes', 10, 3 );

/**
 * Return the roles selected for dashboard access.
 *
 * @param array|null $options Optional plugin options.
 * @return string[] Role slugs.
 */
function uplianda_get_access_roles( $options = null ) {
	$options = is_array( $options ) ? $options : uplianda_get_options();
	$roles   = isset( $options['dashboard_access_roles'] ) && is_array( $options['dashboard_access_roles'] ) ? $options['dashboard_access_roles'] : array( 'administrator' );
	$roles   = array_values( array_unique( array_map( 'sanitize_key', $roles ) ) );

	if ( ! in_array( 'administrator', $roles, true ) ) {
		$roles[] = 'administrator';
	}

	return $roles;
}

/**
 * Grant the dashboard capability to configured roles and remove it elsewhere.
 *
 * @param array|null $options Optional plugin options.
 */
function uplianda_sync_access_roles( $options = null ) {
	$allowed_roles = uplianda_get_access_roles( $options );
	$wp_roles      = wp_roles();

	foreach ( $wp_roles->role_objects as $role_slug => $role ) {
		if ( in_array( $role_slug, $allowed_roles, true ) ) {
			$role->add_cap( uplianda_manage_capability() );
		} else {
			$role->remove_cap( uplianda_manage_capability() );
		}
	}
}

/** Install the initial access capability. */
function uplianda_activate_private_dashboard() {
	uplianda_sync_access_roles();
	update_option( 'uplianda_capability_version', 1, false );
}
register_activation_hook( UPLIANDA_PLUGIN_DIR . 'uplink-analytics-dashboard-for-cabin.php', 'uplianda_activate_private_dashboard' );

/** Ensure existing installations receive the new capability after an update. */
function uplianda_maybe_upgrade_private_dashboard() {
	if ( 1 !== (int) get_option( 'uplianda_capability_version', 0 ) ) {
		uplianda_sync_access_roles();
		update_option( 'uplianda_capability_version', 1, false );
	}
}
add_action( 'admin_init', 'uplianda_maybe_upgrade_private_dashboard' );

/** Synchronize capabilities when dashboard access settings change. */
function uplianda_access_roles_updated( $old_value, $value ) {
	unset( $old_value );
	uplianda_sync_access_roles( is_array( $value ) ? $value : null );
}
add_action( 'update_option_uplianda_options', 'uplianda_access_roles_updated', 10, 2 );

/**
 * Find or create the single private dashboard layout.
 *
 * @return int Dashboard layout post ID, or zero on failure.
 */
function uplianda_get_layout_id() {
	$layout_id = absint( get_option( 'uplianda_layout_id', 0 ) );
	$layout    = $layout_id ? get_post( $layout_id ) : null;

	if ( $layout instanceof WP_Post && uplianda_layout_post_type() === $layout->post_type && 'trash' !== $layout->post_status ) {
		return $layout_id;
	}

	$existing = get_posts(
		array(
			'post_type'              => uplianda_layout_post_type(),
			'post_status'            => array( 'private', 'draft', 'publish' ),
			'posts_per_page'         => 1,
			'orderby'                => 'ID',
			'order'                  => 'ASC',
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);

	if ( ! empty( $existing ) ) {
		$layout_id = absint( $existing[0] );
	} elseif ( current_user_can( uplianda_manage_capability() ) ) {
		$layout_id = wp_insert_post(
			array(
				'post_type'    => uplianda_layout_post_type(),
				'post_status'  => 'private',
				'post_title'   => __( 'Uplink Dashboard for Cabin', 'uplink-analytics-dashboard-for-cabin' ),
				'post_content' => '',
			),
			true
		);

		$layout_id = is_wp_error( $layout_id ) ? 0 : absint( $layout_id );
	}

	if ( $layout_id ) {
		update_option( 'uplianda_layout_id', $layout_id, false );
	}

	return $layout_id;
}

/** Register the Uplink Dashboard for Cabin menu and editing links. */
function uplianda_register_private_dashboard_menu() {
	if ( ! uplianda_private_dashboard_enabled() ) {
		return;
	}

	$capability = uplianda_manage_capability();

	add_menu_page(
		__( 'Uplink Dashboard for Cabin', 'uplink-analytics-dashboard-for-cabin' ),
		__( 'Cabin Dashboard', 'uplink-analytics-dashboard-for-cabin' ),
		$capability,
		'uplianda-dashboard',
		'uplianda_render_private_dashboard',
		uplianda_admin_menu_icon_url(),
		81
	);

	add_submenu_page(
		'uplianda-dashboard',
		__( 'View Uplink Dashboard for Cabin', 'uplink-analytics-dashboard-for-cabin' ),
		__( 'View Dashboard', 'uplink-analytics-dashboard-for-cabin' ),
		$capability,
		'uplianda-dashboard',
		'uplianda_render_private_dashboard'
	);

	$layout_id = uplianda_get_layout_id();
	if ( $layout_id ) {
		add_submenu_page(
			'uplianda-dashboard',
			__( 'Edit Uplink Dashboard for Cabin', 'uplink-analytics-dashboard-for-cabin' ),
			__( 'Edit Dashboard', 'uplink-analytics-dashboard-for-cabin' ),
			$capability,
			'post.php?post=' . $layout_id . '&action=edit'
		);
	}

	if ( current_user_can( 'manage_options' ) ) {
		add_submenu_page(
			'uplianda-dashboard',
			__( 'Uplink Analytics for Cabin Settings', 'uplink-analytics-dashboard-for-cabin' ),
			__( 'Settings', 'uplink-analytics-dashboard-for-cabin' ),
			'manage_options',
			'options-general.php?page=uplink-analytics-dashboard-for-cabin'
		);
	}
}
add_action( 'admin_menu', 'uplianda_register_private_dashboard_menu', 20 );

/** Add a visible dashboard return action to the block editor toolbar. */
function uplianda_enqueue_dashboard_editor_navigation() {
	$screen = get_current_screen();
	if ( ! $screen || uplianda_layout_post_type() !== $screen->post_type || ! current_user_can( uplianda_manage_capability() ) ) {
		return;
	}

	wp_enqueue_style(
		'uplianda-dashboard-editor',
		UPLIANDA_PLUGIN_URL . 'assets/admin-dashboard-editor.css',
		array(),
		filemtime( UPLIANDA_PLUGIN_DIR . 'assets/admin-dashboard-editor.css' )
	);

	wp_enqueue_script(
		'uplianda-dashboard-editor-navigation',
		UPLIANDA_PLUGIN_URL . 'assets/admin-dashboard-editor.js',
		array(),
		filemtime( UPLIANDA_PLUGIN_DIR . 'assets/admin-dashboard-editor.js' ),
		true
	);

	wp_localize_script(
		'uplianda-dashboard-editor-navigation',
		'UpliandaDashboardEditor',
		array(
			'dashboardUrl' => admin_url( 'admin.php?page=uplianda-dashboard' ),
			'backLabel'    => __( 'View Dashboard', 'uplink-analytics-dashboard-for-cabin' ),
		)
	);
}
add_action( 'enqueue_block_editor_assets', 'uplianda_enqueue_dashboard_editor_navigation' );

/** Enqueue the protected dashboard shell assets. */
function uplianda_prepare_private_dashboard( $hook_suffix ) {
	if ( 'toplevel_page_uplianda-dashboard' !== $hook_suffix ) {
		return;
	}

	$style_dependencies = array();
	if ( wp_style_is( 'wp-theme', 'registered' ) ) {
		$style_dependencies[] = 'wp-theme';
	}

	wp_enqueue_style(
		'uplianda-analytics-private-dashboard',
		UPLIANDA_PLUGIN_URL . 'assets/admin-dashboard.css',
		$style_dependencies,
		filemtime( UPLIANDA_PLUGIN_DIR . 'assets/admin-dashboard.css' )
	);
	wp_enqueue_script(
		'uplianda-analytics-private-dashboard-frame',
		UPLIANDA_PLUGIN_URL . 'assets/admin-dashboard-frame.js',
		array(),
		filemtime( UPLIANDA_PLUGIN_DIR . 'assets/admin-dashboard-frame.js' ),
		true
	);

	wp_enqueue_script(
		'uplianda-analytics-private-dashboard-customizer',
		UPLIANDA_PLUGIN_URL . 'assets/admin-dashboard-customizer.js',
		array(),
		filemtime( UPLIANDA_PLUGIN_DIR . 'assets/admin-dashboard-customizer.js' ),
		true
	);

	$style_settings = uplianda_get_style_settings();
	$style_values   = uplianda_get_style_values();
	$preview_values = array();
	$default_values = array();
	foreach ( $style_settings as $option_key => $setting ) {
		$preview_values[ $setting['css_var'] ] = $style_values[ $option_key ];
		$default_values[ $setting['css_var'] ] = $setting['default'];
	}

	wp_add_inline_style(
		'uplianda-analytics-private-dashboard',
		'.uplianda-private-dashboard{--uplianda-dashboard-color-background:' . $style_values['dashboard_style_color_background'] . ';}'
	);

	wp_localize_script(
		'uplianda-analytics-private-dashboard-customizer',
		'UpliandaDashboardStyleEditor',
		array(
			'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'uplianda_dash_save_styles' ),
			'values'   => $preview_values,
			'defaults' => $default_values,
			'messages' => array(
				'saving'          => __( 'Saving styles…', 'uplink-analytics-dashboard-for-cabin' ),
				'saved'           => __( 'Dashboard styles saved.', 'uplink-analytics-dashboard-for-cabin' ),
				'error'           => __( 'Styles could not be saved. Please try again.', 'uplink-analytics-dashboard-for-cabin' ),
				'defaultsPreview' => __( 'Default styles are being previewed. Save to keep them.', 'uplink-analytics-dashboard-for-cabin' ),
			),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'uplianda_prepare_private_dashboard', 30 );

/** Return the protected, site-styled dashboard document URL. */
function uplianda_document_url() {
	return add_query_arg(
		array(
			'uplianda-dashboard-document' => '1',
			'uplianda_nonce'              => wp_create_nonce( 'uplianda_dash_document' ),
		),
		home_url( '/' )
	);
}

/**
 * Render the dashboard as a front-end document inside the admin iframe.
 *
 * This keeps active-theme CSS isolated from the WordPress admin shell while
 * giving core and Cabin blocks the same theme.json, Global Styles, block
 * supports, theme stylesheet, and responsive behavior as normal site content.
 */
function uplianda_render_document() {
	if ( empty( $_GET['uplianda-dashboard-document'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Verified below for the protected document request.
		return;
	}

	$nonce = isset( $_GET['uplianda_nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['uplianda_nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'uplianda_dash_document' ) ) {
		wp_die( esc_html__( 'The Uplink Dashboard for Cabin link has expired. Reload the WordPress admin page and try again.', 'uplink-analytics-dashboard-for-cabin' ), '', array( 'response' => 403 ) );
	}

	if ( ! is_user_logged_in() || ! current_user_can( uplianda_manage_capability() ) ) {
		wp_die( esc_html__( 'You do not have permission to view the Uplink Dashboard for Cabin.', 'uplink-analytics-dashboard-for-cabin' ), '', array( 'response' => 403 ) );
	}

	$layout_id = uplianda_get_layout_id();
	$layout    = $layout_id ? get_post( $layout_id ) : null;
	if ( ! $layout instanceof WP_Post ) {
		wp_die( esc_html__( 'The Uplink Dashboard for Cabin layout could not be loaded.', 'uplink-analytics-dashboard-for-cabin' ), '', array( 'response' => 404 ) );
	}

	$GLOBALS['post'] = $layout;
	setup_postdata( $layout );
	show_admin_bar( false );
	add_filter(
		'private_title_format',
		static function () {
			return '%s';
		}
	);
	add_action(
		'wp_enqueue_scripts',
		static function () {
			wp_dequeue_style( 'admin-bar' );
			wp_dequeue_script( 'admin-bar' );
		},
		PHP_INT_MAX
	);
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_enqueue_scripts', 'wp_enqueue_emoji_styles' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	uplianda_enqueue_assets();

	// Apply the standard WordPress content pipeline to the authorized block layout.
	$rendered = '' !== trim( $layout->post_content ) ? apply_filters( 'the_content', $layout->post_content ) : ''; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- This is the WordPress core content hook.

	wp_enqueue_style( 'wp-block-library' );
	wp_enqueue_style(
		'uplianda-document',
		UPLIANDA_PLUGIN_URL . 'assets/admin-dashboard-document.css',
		array( 'wp-block-library' ),
		filemtime( UPLIANDA_PLUGIN_DIR . 'assets/admin-dashboard-document.css' )
	);
	wp_add_inline_style( 'uplianda-document', uplianda_get_style_css() );
	wp_enqueue_script(
		'uplianda-document',
		UPLIANDA_PLUGIN_URL . 'assets/admin-dashboard-document.js',
		array(),
		filemtime( UPLIANDA_PLUGIN_DIR . 'assets/admin-dashboard-document.js' ),
		true
	);
	wp_localize_script(
		'uplianda-document',
		'upliandaDashboardDocumentL10n',
		array(
			'opensInNewTab' => __( 'opens in a new tab', 'uplink-analytics-dashboard-for-cabin' ),
		)
	);

	nocache_headers();
	header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
	header( 'X-Robots-Tag: noindex, nofollow, noarchive', true );
	?>
	<!doctype html>
	<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<title><?php esc_html_e( 'Uplink Dashboard for Cabin', 'uplink-analytics-dashboard-for-cabin' ); ?></title>
		<?php wp_head(); ?>
	</head>
	<body <?php body_class( 'uplianda-dashboard-document' ); ?>>
		<main id="uplianda-dashboard-content" class="uplianda-dashboard-document__content wp-site-blocks">
			<?php echo $rendered; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core block rendering and authorized saved content. ?>
		</main>
		<?php wp_footer(); ?>
	</body>
	</html>
	<?php
	exit;
}
add_action( 'template_redirect', 'uplianda_render_document', 0 );

/** Save private-dashboard style tokens from the live style drawer. */
function uplianda_save_styles() {
	check_ajax_referer( 'uplianda_dash_save_styles', 'nonce' );

	if ( ! current_user_can( uplianda_manage_capability() ) ) {
		wp_send_json_error( array( 'message' => __( 'You do not have permission to customize the Uplink Dashboard for Cabin.', 'uplink-analytics-dashboard-for-cabin' ) ), 403 );
	}

	$submitted = isset( $_POST['styles'] ) && is_array( $_POST['styles'] ) ? map_deep( wp_unslash( $_POST['styles'] ), 'sanitize_text_field' ) : array();
	$settings  = uplianda_get_style_settings();
	$options   = get_option( 'uplianda_options', array() );
	$options   = is_array( $options ) ? $options : array();
	$saved     = array();

	foreach ( $settings as $option_key => $setting ) {
		$raw_value = $submitted[ $setting['css_var'] ] ?? ( $options[ $option_key ] ?? $setting['default'] );
		$value     = uplianda_sanitize_style_value( $raw_value, $setting );
		$options[ $option_key ]             = $value;
		$saved[ $setting['css_var'] ] = $value;
	}

	update_option( 'uplianda_options', $options );
	wp_send_json_success( array( 'styles' => $saved ) );
}
add_action( 'wp_ajax_uplianda_save_styles', 'uplianda_save_styles' );

/** Render the live private-dashboard style drawer. */
function uplianda_render_style_drawer() {
	$settings = uplianda_get_style_settings();
	$values   = uplianda_get_style_values();
	$groups   = array(
		'colors'     => __( 'Colors', 'uplink-analytics-dashboard-for-cabin' ),
		'typography' => __( 'Typography', 'uplink-analytics-dashboard-for-cabin' ),
		'spacing'    => __( 'Spacing', 'uplink-analytics-dashboard-for-cabin' ),
	);
	?>
	<div id="uplianda-dashboard-style-overlay" class="uplianda-style-overlay" hidden>
		<aside id="uplianda-dashboard-style-drawer" class="uplianda-style-drawer" role="dialog" aria-modal="true" aria-labelledby="uplianda-style-drawer-title">
			<div class="uplianda-style-drawer__header">
				<div>
					<h2 id="uplianda-style-drawer-title"><?php esc_html_e( 'Dashboard Styles', 'uplink-analytics-dashboard-for-cabin' ); ?></h2>
					<p><?php esc_html_e( 'Changes preview immediately. Save when the dashboard looks right.', 'uplink-analytics-dashboard-for-cabin' ); ?></p>
				</div>
				<button type="button" class="uplianda-style-drawer__close" aria-label="<?php esc_attr_e( 'Close style editor', 'uplink-analytics-dashboard-for-cabin' ); ?>">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>

			<div class="uplianda-style-drawer__body">
				<?php foreach ( $groups as $group_key => $group_label ) : ?>
					<fieldset class="uplianda-style-group">
						<legend><?php echo esc_html( $group_label ); ?></legend>
						<?php foreach ( $settings as $option_key => $setting ) : ?>
							<?php if ( $group_key !== $setting['group'] ) { continue; } ?>
							<div class="uplianda-style-control uplianda-style-control--<?php echo esc_attr( $setting['type'] ); ?>">
								<label for="<?php echo esc_attr( 'uplianda-style-' . $option_key ); ?>"><?php echo esc_html( $setting['label'] ); ?></label>
								<div class="uplianda-style-control__input">
									<?php if ( 'color' === $setting['type'] ) : ?>
										<input id="<?php echo esc_attr( 'uplianda-style-' . $option_key ); ?>" type="color" value="<?php echo esc_attr( $values[ $option_key ] ); ?>" data-uplianda-style-var="<?php echo esc_attr( $setting['css_var'] ); ?>" data-uplianda-style-default="<?php echo esc_attr( $setting['default'] ); ?>" />
										<output for="<?php echo esc_attr( 'uplianda-style-' . $option_key ); ?>"><?php echo esc_html( $values[ $option_key ] ); ?></output>
									<?php elseif ( 'line_height' === $setting['type'] ) : ?>
										<input id="<?php echo esc_attr( 'uplianda-style-' . $option_key ); ?>" type="number" min="1" max="3" step="0.1" value="<?php echo esc_attr( $values[ $option_key ] ); ?>" data-uplianda-style-var="<?php echo esc_attr( $setting['css_var'] ); ?>" data-uplianda-style-default="<?php echo esc_attr( $setting['default'] ); ?>" />
									<?php else : ?>
										<input id="<?php echo esc_attr( 'uplianda-style-' . $option_key ); ?>" type="text" value="<?php echo esc_attr( $values[ $option_key ] ); ?>" data-uplianda-style-var="<?php echo esc_attr( $setting['css_var'] ); ?>" data-uplianda-style-default="<?php echo esc_attr( $setting['default'] ); ?>" spellcheck="false" />
									<?php endif; ?>
								</div>
								<p><?php echo esc_html( $setting['description'] ); ?></p>
							</div>
						<?php endforeach; ?>
					</fieldset>
				<?php endforeach; ?>
			</div>

			<div class="uplianda-style-drawer__footer">
				<p class="uplianda-style-drawer__status" role="status" aria-live="polite"></p>
				<div>
					<button type="button" class="button uplianda-style-drawer__reset"><?php esc_html_e( 'Reset Defaults', 'uplink-analytics-dashboard-for-cabin' ); ?></button>
					<button type="button" class="button button-primary uplianda-style-drawer__save"><?php esc_html_e( 'Save Styles', 'uplink-analytics-dashboard-for-cabin' ); ?></button>
				</div>
			</div>
		</aside>
	</div>
	<?php
}

/** Render the protected, block-designed Uplink Dashboard for Cabin. */
function uplianda_render_private_dashboard() {
	if ( ! current_user_can( uplianda_manage_capability() ) ) {
		wp_die( esc_html__( 'You do not have permission to access the Uplink Dashboard for Cabin.', 'uplink-analytics-dashboard-for-cabin' ) );
	}

	$layout_id = uplianda_get_layout_id();
	$edit_url  = $layout_id ? get_edit_post_link( $layout_id, 'raw' ) : '';
	$layout    = $layout_id ? get_post( $layout_id ) : null;
	$content   = $layout instanceof WP_Post ? $layout->post_content : '';
	?>
	<div class="wrap uplianda-private-dashboard">
		<div class="uplianda-private-dashboard__header">
			<div>
				<h1><?php esc_html_e( 'Uplink Dashboard for Cabin', 'uplink-analytics-dashboard-for-cabin' ); ?></h1>
				<p><?php esc_html_e( 'A private analytics workspace designed with WordPress blocks.', 'uplink-analytics-dashboard-for-cabin' ); ?></p>
			</div>
			<div class="uplianda-private-dashboard__actions">
				<button type="button" class="button uplianda-dashboard-style-toggle" aria-controls="uplianda-dashboard-style-drawer" aria-expanded="false"><?php esc_html_e( 'Customize Styles', 'uplink-analytics-dashboard-for-cabin' ); ?></button>
				<?php if ( $edit_url ) : ?>
					<a class="button button-primary" href="<?php echo esc_url( $edit_url ); ?>"><?php esc_html_e( 'Edit Dashboard', 'uplink-analytics-dashboard-for-cabin' ); ?></a>
				<?php endif; ?>
				<?php if ( current_user_can( 'manage_options' ) ) : ?>
					<a class="button" href="<?php echo esc_url( admin_url( 'options-general.php?page=uplink-analytics-dashboard-for-cabin' ) ); ?>"><?php esc_html_e( 'Uplink Settings', 'uplink-analytics-dashboard-for-cabin' ); ?></a>
				<?php endif; ?>
			</div>
		</div>

		<div class="uplianda-private-dashboard__canvas">
			<?php if ( '' !== trim( $content ) ) : ?>
				<iframe
					id="uplianda-private-dashboard-frame"
					class="uplianda-private-dashboard__frame"
					src="<?php echo esc_url( uplianda_document_url() ); ?>"
					title="<?php esc_attr_e( 'Site-styled Uplink Dashboard for Cabin', 'uplink-analytics-dashboard-for-cabin' ); ?>"
					loading="eager"
				></iframe>
			<?php else : ?>
				<div class="uplianda-private-dashboard__empty">
					<h2><?php esc_html_e( 'Design your private dashboard', 'uplink-analytics-dashboard-for-cabin' ); ?></h2>
					<p><?php esc_html_e( 'Use Columns, Groups, Headings, and any Cabin blocks to build the layout you want. Add the same report block more than once and enable different modules in each instance.', 'uplink-analytics-dashboard-for-cabin' ); ?></p>
					<?php if ( $edit_url ) : ?>
						<a class="button button-primary" href="<?php echo esc_url( $edit_url ); ?>"><?php esc_html_e( 'Open Block Editor', 'uplink-analytics-dashboard-for-cabin' ); ?></a>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>

		<?php uplianda_render_style_drawer(); ?>
	</div>
	<?php
}

/** Render the dashboard access role settings. */
function uplianda_enabled_render() {
	$options = uplianda_get_options();
	?>
	<label for="uplianda_private_dashboard_enabled">
		<input
			id="uplianda_private_dashboard_enabled"
			type="checkbox"
			name="uplianda_options[private_dashboard_enabled]"
			value="1"
			<?php checked( ! empty( $options['private_dashboard_enabled'] ) ); ?>
		/>
		<?php esc_html_e( 'Show the private Uplink Dashboard for Cabin in the WordPress admin menu.', 'uplink-analytics-dashboard-for-cabin' ); ?>
	</label>
	<p class="uplianda-description"><?php esc_html_e( 'Disabling this hides the dashboard and its editor links without deleting the saved block layout.', 'uplink-analytics-dashboard-for-cabin' ); ?></p>
	<?php
}

/** Render the dashboard access role settings. */
function uplianda_access_roles_render() {
	$selected_roles = uplianda_get_access_roles();
	$roles          = wp_roles()->roles;
	?>
	<fieldset class="uplianda-access-roles">
		<legend class="screen-reader-text"><?php esc_html_e( 'Roles allowed to view and edit the Uplink Dashboard for Cabin', 'uplink-analytics-dashboard-for-cabin' ); ?></legend>
		<?php foreach ( $roles as $role_slug => $role_data ) : ?>
			<?php $is_administrator = 'administrator' === $role_slug; ?>
			<label>
				<input
					type="checkbox"
					name="uplianda_options[dashboard_access_roles][]"
					value="<?php echo esc_attr( $role_slug ); ?>"
					<?php checked( in_array( $role_slug, $selected_roles, true ) ); ?>
					<?php disabled( $is_administrator ); ?>
				/>
				<?php echo esc_html( translate_user_role( $role_data['name'] ) ); ?>
			</label>
			<?php if ( $is_administrator ) : ?>
				<input type="hidden" name="uplianda_options[dashboard_access_roles][]" value="administrator" />
			<?php endif; ?>
		<?php endforeach; ?>
	</fieldset>
	<p class="uplianda-description"><?php esc_html_e( 'Administrators always retain access. Selected roles can view the private dashboard and edit its block layout.', 'uplink-analytics-dashboard-for-cabin' ); ?></p>
	<?php
}

/** Private dashboard settings section description. */
function uplianda_private_section_callback() {
	echo '<p class="uplianda-section-description">' . esc_html__( 'Control who can view and design the private, block-based Uplink Dashboard for Cabin in WordPress admin.', 'uplink-analytics-dashboard-for-cabin' ) . '</p>';
}
