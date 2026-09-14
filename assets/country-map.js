( function () {
	'use strict';

	function initializeMap( map ) {
		if ( map.dataset.upliandaMapReady === 'true' ) {
			return;
		}

		const visual = map.querySelector( '.uplianda-country-map__visual' );
		const tooltip = map.querySelector( '.uplianda-country-map__tooltip' );
		const name = tooltip ? tooltip.querySelector( '[data-uplianda-map-tooltip-name]' ) : null;
		const value = tooltip ? tooltip.querySelector( '[data-uplianda-map-tooltip-value]' ) : null;
		const countries = Array.from( map.querySelectorAll( '[data-country-name][data-country-value][data-country-rank]' ) ).sort( function ( first, second ) {
			return Number( first.dataset.countryRank ) - Number( second.dataset.countryRank );
		} );

		if ( ! visual || ! tooltip || ! name || ! value || countries.length === 0 ) {
			return;
		}

		map.dataset.upliandaMapReady = 'true';

		function positionTooltip( x, y ) {
			const visualRect = visual.getBoundingClientRect();
			const tooltipRect = tooltip.getBoundingClientRect();
			const gap = 14;
			const maximumX = Math.max( gap, visualRect.width - tooltipRect.width - gap );
			const maximumY = Math.max( gap, visualRect.height - tooltipRect.height - gap );

			tooltip.style.left = Math.min( Math.max( gap, x - visualRect.left + gap ), maximumX ) + 'px';
			tooltip.style.top = Math.min( Math.max( gap, y - visualRect.top + gap ), maximumY ) + 'px';
		}

		function showTooltip( country, x, y ) {
			name.textContent = country.dataset.countryName;
			value.textContent = country.dataset.countryValue;
			tooltip.hidden = false;
			tooltip.setAttribute( 'aria-hidden', 'false' );
			positionTooltip( x, y );
		}

		function hideTooltip() {
			tooltip.hidden = true;
			tooltip.setAttribute( 'aria-hidden', 'true' );
		}

		function moveFocus( current, targetIndex ) {
			const next = countries[ Math.max( 0, Math.min( countries.length - 1, targetIndex ) ) ];

			if ( ! next || next === current ) {
				return;
			}

			current.setAttribute( 'tabindex', '-1' );
			next.setAttribute( 'tabindex', '0' );
			next.focus();
		}

		countries.forEach( function ( country, index ) {
			country.addEventListener( 'pointerenter', function ( event ) {
				showTooltip( country, event.clientX, event.clientY );
			} );
			country.addEventListener( 'pointermove', function ( event ) {
				positionTooltip( event.clientX, event.clientY );
			} );
			country.addEventListener( 'pointerleave', hideTooltip );
			country.addEventListener( 'focus', function () {
				const countryRect = country.getBoundingClientRect();
				showTooltip( country, countryRect.left + countryRect.width / 2, countryRect.top + countryRect.height / 2 );
			} );
			country.addEventListener( 'blur', hideTooltip );
			country.addEventListener( 'keydown', function ( event ) {
				let targetIndex = null;

				if ( event.key === 'ArrowRight' || event.key === 'ArrowDown' ) {
					targetIndex = ( index + 1 ) % countries.length;
				} else if ( event.key === 'ArrowLeft' || event.key === 'ArrowUp' ) {
					targetIndex = ( index - 1 + countries.length ) % countries.length;
				} else if ( event.key === 'Home' ) {
					targetIndex = 0;
				} else if ( event.key === 'End' ) {
					targetIndex = countries.length - 1;
				}

				if ( null !== targetIndex ) {
					event.preventDefault();
					moveFocus( country, targetIndex );
				}
			} );
		} );
	}

	function initializeMaps() {
		document.querySelectorAll( '.uplianda-country-map' ).forEach( initializeMap );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', initializeMaps );
	} else {
		initializeMaps();
	}
} )();
