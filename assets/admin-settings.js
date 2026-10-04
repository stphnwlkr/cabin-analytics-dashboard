( function () {
	'use strict';

	const toggle = document.querySelector( '[data-uplianda-widget-toggle]' );
	const widgetOnlyRows = Array.from( document.querySelectorAll( '.uplianda-widget-only-setting' ) );

	if ( ! toggle || ! widgetOnlyRows.length ) {
		return;
	}

	function syncWidgetSettings() {
		const widgetsEnabled = toggle.checked;

		widgetOnlyRows.forEach( function ( row ) {
			row.hidden = ! widgetsEnabled;
			row.classList.toggle( 'is-hidden', ! widgetsEnabled );
			row.querySelectorAll( 'input, select, textarea, button' ).forEach( function ( control ) {
				control.disabled = ! widgetsEnabled;
			} );
		} );
	}

	toggle.addEventListener( 'change', syncWidgetSettings );
	syncWidgetSettings();
}() );
