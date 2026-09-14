( function () {
	'use strict';

	function initializePopularContent( widget ) {
		if ( 'true' === widget.dataset.upliandaPopularContentReady ) {
			return;
		}

		var target = widget.querySelector( '.uplianda-popular-content-dashboard__body' );
		var restUrl = widget.dataset.restUrl || '';
		var nonce = widget.dataset.nonce || '';
		var dateRange = Number.parseInt( widget.dataset.dateRange, 10 ) || 30;
		var qty = Number.parseInt( widget.dataset.qty, 10 ) || 10;

		if ( ! target || ! restUrl ) {
			return;
		}

		widget.dataset.upliandaPopularContentReady = 'true';

		function setActive( selector, value ) {
			widget.querySelectorAll( selector ).forEach( function ( button ) {
				var buttonValue = button.dataset.upliandaPopularRange || button.dataset.upliandaPopularQty;
				var isActive = String( buttonValue ) === String( value );
				button.classList.toggle( 'active', isActive );
				button.setAttribute( 'aria-pressed', isActive ? 'true' : 'false' );
			} );
		}

		function showMessage( text ) {
			var message = document.createElement( 'p' );
			message.className = 'uplianda-popular-content__message';
			message.textContent = text;
			target.replaceChildren( message );
		}

		function loadPopularContent() {
			var url = new URL( restUrl, window.location.href );
			var headers = {};

			url.searchParams.set( 'date_range', dateRange );
			url.searchParams.set( 'qty', qty );
			target.setAttribute( 'aria-busy', 'true' );
			showMessage( widget.dataset.loadingText );

			if ( nonce ) {
				headers[ 'X-WP-Nonce' ] = nonce;
			}

			window.fetch( url.toString(), { credentials: 'same-origin', headers: headers } )
				.then( function ( response ) {
					if ( ! response.ok ) {
						throw new Error( 'Popular Content request failed.' );
					}
					return response.json();
				} )
				.then( function ( data ) {
					if ( data && data.html ) {
						target.innerHTML = data.html;
					} else {
						showMessage( widget.dataset.errorText );
					}
				} )
				.catch( function () {
					showMessage( widget.dataset.errorText );
				} )
				.finally( function () {
					target.removeAttribute( 'aria-busy' );
				} );
		}

		target.addEventListener( 'click', function ( event ) {
			if ( event.target.closest( '.uplianda-popular-content__summary-content-link' ) ) {
				event.stopPropagation();
			}
		} );

		widget.querySelectorAll( '[data-uplianda-popular-range]' ).forEach( function ( button ) {
			button.addEventListener( 'click', function () {
				dateRange = Number.parseInt( button.dataset.upliandaPopularRange, 10 );
				setActive( '[data-uplianda-popular-range]', dateRange );
				loadPopularContent();
			} );
		} );

		widget.querySelectorAll( '[data-uplianda-popular-qty]' ).forEach( function ( button ) {
			button.addEventListener( 'click', function () {
				qty = Number.parseInt( button.dataset.upliandaPopularQty, 10 );
				setActive( '[data-uplianda-popular-qty]', qty );
				loadPopularContent();
			} );
		} );

		if ( 'true' === widget.dataset.loadOnInit ) {
			loadPopularContent();
		}
	}

	function initializeAll() {
		document.querySelectorAll( '[data-uplianda-dashboard-popular-content]' ).forEach( initializePopularContent );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', initializeAll );
	} else {
		initializeAll();
	}
}() );
