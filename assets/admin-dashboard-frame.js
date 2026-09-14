( function () {
	'use strict';

	var frame = document.getElementById( 'uplianda-private-dashboard-frame' );
	if ( ! frame ) {
		return;
	}

	window.addEventListener( 'message', function ( event ) {
		if ( event.origin !== window.location.origin || event.source !== frame.contentWindow ) {
			return;
		}

		if ( ! event.data || 'uplianda-dashboard-height' !== event.data.type ) {
			return;
		}

		var height = Number.parseInt( event.data.height, 10 );
		if ( Number.isFinite( height ) && height > 0 ) {
			var targetHeight = Math.max( 480, height );
			if ( Math.abs( frame.offsetHeight - targetHeight ) > 1 ) {
				frame.style.height = targetHeight + 'px';
			}
		}
	} );
}() );
