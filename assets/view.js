document.addEventListener( 'DOMContentLoaded', function () {
	'use strict';

	if ( ! window.UpliandaChart ) {
		return;
	}

	document.querySelectorAll( '.uplianda-analytics-widget' ).forEach( function ( widget ) {
		window.UpliandaChart.mount( widget, {
			title: widget.dataset.title || 'Site Traffic',
			domain: widget.dataset.domain || '',
			chartType: widget.dataset.chartType || 'bar',
			dateRange: widget.dataset.dateRange || '7',
			allowSwitching: 'false' !== widget.dataset.allowSwitching,
			dashboardUrl: widget.dataset.dashboardUrl || '',
			restUrl: widget.dataset.restUrl || '',
		} );
	} );
} );
