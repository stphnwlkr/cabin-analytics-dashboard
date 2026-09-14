<?php
defined( 'ABSPATH' ) || exit;

$uplianda_options            = uplianda_get_options();
$uplianda_default_domain     = isset( $uplianda_options['domain'] ) ? $uplianda_options['domain'] : '';
$uplianda_default_chart_type = isset( $uplianda_options['chart_type'] ) ? $uplianda_options['chart_type'] : 'bar';
$uplianda_default_date_range = isset( $uplianda_options['date_range'] ) ? $uplianda_options['date_range'] : '7';

$uplianda_domain          = ! empty( $attributes['domain'] ) ? $attributes['domain'] : $uplianda_default_domain;
$uplianda_title           = ! empty( $attributes['title'] ) ? sanitize_text_field( $attributes['title'] ) : __( 'Site Traffic', 'uplink-analytics-dashboard-for-cabin' );
$uplianda_chart_type      = ! empty( $attributes['chartType'] ) ? $attributes['chartType'] : $uplianda_default_chart_type;
$uplianda_date_range      = ! empty( $attributes['dateRange'] ) ? $attributes['dateRange'] : $uplianda_default_date_range;
$uplianda_allow_switching = isset( $attributes['allowSwitching'] ) ? $attributes['allowSwitching'] : true;
$uplianda_url   = ! empty( $attributes['dashboardUrl'] ) ? $attributes['dashboardUrl'] : '';

if ( empty( $uplianda_url ) ) {
	$uplianda_url = UPLIANDA_CABIN_URL;
}
?>
<div <?php echo wp_kses_data( get_block_wrapper_attributes() ); ?>>
	<div class="uplianda-analytics-widget" 
		data-title="<?php echo esc_attr( $uplianda_title ); ?>"
		data-domain="<?php echo esc_attr( $uplianda_domain ); ?>" 
		data-chart-type="<?php echo esc_attr( $uplianda_chart_type ); ?>" 
		data-date-range="<?php echo esc_attr( $uplianda_date_range ); ?>"
		data-allow-switching="<?php echo esc_attr( $uplianda_allow_switching ? 'true' : 'false' ); ?>"
		data-dashboard-url="<?php echo esc_url( $uplianda_url ); ?>"
		data-rest-url="<?php echo esc_url( rest_url( 'uplianda/v1/stats' ) ); ?>">
	</div>
</div>
