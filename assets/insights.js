document.addEventListener( 'DOMContentLoaded', () => {
	document.querySelectorAll( '[data-uplianda-insights]' ).forEach( ( widget ) => {
		const target = widget.querySelector( '[data-uplianda-insights-target]' );
		const range = widget.querySelector( '[data-uplianda-insights-range]' );

		if ( ! target || ! range || ! widget.dataset.restUrl ) {
			return;
		}

		const loadInsights = () => {
			const url = new URL( widget.dataset.restUrl );
			url.searchParams.set( 'date_range', range.value );
			url.searchParams.set( 'section', widget.dataset.section || '' );
			target.setAttribute( 'aria-busy', 'true' );
			target.textContent = '';

			const status = document.createElement( 'p' );
			status.className = 'uplianda-insights__status';
			status.setAttribute( 'role', 'status' );
			status.textContent = widget.dataset.loadingMessage || '';
			target.appendChild( status );

			fetch( url.toString(), {
				credentials: 'same-origin',
				headers: { 'X-WP-Nonce': widget.dataset.restNonce || '' },
			} )
				.then( ( response ) => {
					if ( ! response.ok ) {
						throw new Error( 'Request failed' );
					}
					return response.json();
				} )
				.then( ( response ) => {
					if ( ! response.success || ! response.html ) {
						throw new Error( response.message || 'No insights returned' );
					}
					target.innerHTML = response.html;
				} )
				.catch( () => {
					target.textContent = '';
					const error = document.createElement( 'p' );
					error.className = 'uplianda-insights__status notice notice-error';
					error.setAttribute( 'role', 'alert' );
					error.textContent = widget.dataset.errorMessage || '';
					target.appendChild( error );
				} )
				.finally( () => target.setAttribute( 'aria-busy', 'false' ) );
		};

		range.addEventListener( 'change', loadInsights );
		loadInsights();
	} );
} );
