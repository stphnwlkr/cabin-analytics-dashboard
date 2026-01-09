<?php
$options = get_option( 'cabin_analytics_dashboard_options' );
$default_domain = isset( $options['domain'] ) ? $options['domain'] : '';
$default_chart_type = isset( $options['chart_type'] ) ? $options['chart_type'] : 'bar';
$default_date_range = isset( $options['date_range'] ) ? $options['date_range'] : '7';

$domain = ! empty( $attributes['domain'] ) ? $attributes['domain'] : $default_domain;
$chart_type = isset( $attributes['chartType'] ) && ! empty( $attributes['chartType'] ) ? $attributes['chartType'] : $default_chart_type;
$date_range = isset( $attributes['dateRange'] ) && ! empty( $attributes['dateRange'] ) ? $attributes['dateRange'] : $default_date_range;
$allow_switching = isset( $attributes['allowSwitching'] ) ? $attributes['allowSwitching'] : true;
$dashboard_url = isset( $attributes['dashboardUrl'] ) && ! empty( $attributes['dashboardUrl'] ) ? $attributes['dashboardUrl'] : '';

if ( empty( $dashboard_url ) ) {
	$dashboard_url = 'https://withcabin.com/dashboard/' . $domain;
}
?>
<div <?php echo get_block_wrapper_attributes(); ?>>
	<div class="cabin-analytics-widget" 
		data-domain="<?php echo esc_attr( $domain ); ?>" 
		data-chart-type="<?php echo esc_attr( $chart_type ); ?>" 
		data-date-range="<?php echo esc_attr( $date_range ); ?>"
		data-allow-switching="<?php echo $allow_switching ? 'true' : 'false'; ?>"
		data-dashboard-url="<?php echo esc_attr( $dashboard_url ); ?>">
	</div>
</div>