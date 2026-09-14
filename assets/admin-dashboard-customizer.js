( function () {
	'use strict';

	var config = window.UpliandaDashboardStyleEditor;
	var toggle = document.querySelector( '.uplianda-dashboard-style-toggle' );
	var overlay = document.getElementById( 'uplianda-dashboard-style-overlay' );
	var drawer = document.getElementById( 'uplianda-dashboard-style-drawer' );
	var frame = document.getElementById( 'uplianda-private-dashboard-frame' );
	var dashboard = document.querySelector( '.uplianda-private-dashboard' );

	if ( ! config || ! toggle || ! overlay || ! drawer || ! frame || ! dashboard ) {
		return;
	}

	var closeButton = drawer.querySelector( '.uplianda-style-drawer__close' );
	var resetButton = drawer.querySelector( '.uplianda-style-drawer__reset' );
	var saveButton = drawer.querySelector( '.uplianda-style-drawer__save' );
	var status = drawer.querySelector( '.uplianda-style-drawer__status' );
	var inputs = Array.prototype.slice.call( drawer.querySelectorAll( '[data-uplianda-style-var]' ) );
	var savedStyles = Object.assign( {}, config.values );

	function valuesFromInputs() {
		var values = {};
		inputs.forEach( function ( input ) {
			values[ input.dataset.upliandaStyleVar ] = input.value;
		} );
		return values;
	}

	function updateColorOutput( input ) {
		var output = input.parentNode.querySelector( 'output' );
		if ( output ) {
			output.textContent = input.value.toUpperCase();
		}
	}

	function populateInputs( values ) {
		inputs.forEach( function ( input ) {
			var cssVariable = input.dataset.upliandaStyleVar;
			if ( Object.prototype.hasOwnProperty.call( values, cssVariable ) ) {
				input.value = values[ cssVariable ];
				updateColorOutput( input );
			}
		} );
	}

	function preview( values ) {
		if ( Object.prototype.hasOwnProperty.call( values, '--uplianda-dashboard-color-background' ) ) {
			dashboard.style.setProperty( '--uplianda-dashboard-color-background', values[ '--uplianda-dashboard-color-background' ] );
		}

		frame.contentWindow.postMessage(
			{
				type: 'uplianda-dashboard-style-preview',
				styles: values,
			},
			window.location.origin
		);
	}

	function openDrawer() {
		overlay.hidden = false;
		document.body.classList.add( 'uplianda-style-drawer-open' );
		toggle.setAttribute( 'aria-expanded', 'true' );
		status.textContent = '';
		window.requestAnimationFrame( function () {
			overlay.classList.add( 'is-open' );
			closeButton.focus();
		} );
	}

	function closeDrawer() {
		populateInputs( savedStyles );
		preview( savedStyles );
		overlay.classList.remove( 'is-open' );
		document.body.classList.remove( 'uplianda-style-drawer-open' );
		toggle.setAttribute( 'aria-expanded', 'false' );
		window.setTimeout( function () {
			overlay.hidden = true;
			toggle.focus();
		}, 180 );
	}

	inputs.forEach( function ( input ) {
		input.addEventListener( 'input', function () {
			updateColorOutput( input );
			status.textContent = '';
			preview( valuesFromInputs() );
		} );
	} );

	toggle.addEventListener( 'click', openDrawer );
	closeButton.addEventListener( 'click', closeDrawer );
	overlay.addEventListener( 'click', function ( event ) {
		if ( event.target === overlay ) {
			closeDrawer();
		}
	} );

	resetButton.addEventListener( 'click', function () {
		populateInputs( config.defaults );
		preview( valuesFromInputs() );
		status.textContent = config.messages.defaultsPreview;
	} );

	saveButton.addEventListener( 'click', function () {
		var values = valuesFromInputs();
		var body = new window.URLSearchParams();
		body.set( 'action', 'uplianda_save_styles' );
		body.set( 'nonce', config.nonce );
		Object.keys( values ).forEach( function ( cssVariable ) {
			body.set( 'styles[' + cssVariable + ']', values[ cssVariable ] );
		} );

		saveButton.disabled = true;
		status.textContent = config.messages.saving;

		window.fetch( config.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
			body: body.toString(),
		} )
			.then( function ( response ) {
				return response.json();
			} )
			.then( function ( response ) {
				if ( ! response.success || ! response.data || ! response.data.styles ) {
					throw new Error( 'Invalid style response.' );
				}
				savedStyles = Object.assign( {}, response.data.styles );
				populateInputs( savedStyles );
				preview( savedStyles );
				status.textContent = config.messages.saved;
			} )
			.catch( function () {
				status.textContent = config.messages.error;
			} )
			.finally( function () {
				saveButton.disabled = false;
			} );
	} );

	drawer.addEventListener( 'keydown', function ( event ) {
		if ( 'Escape' === event.key ) {
			event.preventDefault();
			closeDrawer();
			return;
		}

		if ( 'Tab' !== event.key ) {
			return;
		}

		var focusable = Array.prototype.slice.call( drawer.querySelectorAll( 'button:not([disabled]), input:not([disabled])' ) );
		var first = focusable[0];
		var last = focusable[ focusable.length - 1 ];
		if ( event.shiftKey && document.activeElement === first ) {
			event.preventDefault();
			last.focus();
		} else if ( ! event.shiftKey && document.activeElement === last ) {
			event.preventDefault();
			first.focus();
		}
	} );

	frame.addEventListener( 'load', function () {
		preview( savedStyles );
	} );
}() );
