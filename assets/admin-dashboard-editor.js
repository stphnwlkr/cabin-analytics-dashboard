( function ( settings ) {
	'use strict';

	if ( ! settings || ! settings.dashboardUrl || ! settings.backLabel ) {
		return;
	}

	var className = 'uplianda-dashboard-editor-return';
	var selectors = [ '.editor-header__settings', '.edit-post-header__settings' ];

	function findToolbar() {
		var toolbar = null;

		selectors.some( function ( selector ) {
			toolbar = document.querySelector( selector );
			return Boolean( toolbar );
		} );

		return toolbar;
	}

	function addButton() {
		var toolbar = findToolbar();
		var link;

		if ( ! toolbar || toolbar.querySelector( '.' + className ) ) {
			return;
		}

		link = document.createElement( 'a' );
		link.className = 'components-button is-secondary ' + className;
		link.href = settings.dashboardUrl;
		link.textContent = settings.backLabel;
		link.setAttribute( 'aria-label', settings.backLabel );

		toolbar.insertBefore( link, toolbar.firstChild );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', addButton );
	} else {
		addButton();
	}

	new MutationObserver( addButton ).observe( document.documentElement, {
		childList: true,
		subtree: true
	} );
}( window.UpliandaDashboardEditor ) );
