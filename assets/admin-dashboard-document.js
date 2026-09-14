( function () {
	'use strict';

	var scheduled = false;
	var content = document.getElementById( 'uplianda-dashboard-content' );
	var newTabText = window.upliandaDashboardDocumentL10n && window.upliandaDashboardDocumentL10n.opensInNewTab ?
		window.upliandaDashboardDocumentL10n.opensInNewTab :
		'opens in a new tab';
	var newTabNoticeId = 0;

	function addNewTabAnnouncement( link ) {
		if ( 'true' === link.dataset.upliandaNewTabAnnouncement ) {
			return;
		}

		var existingLabel = link.getAttribute( 'aria-label' ) || '';
		var existingText = ( existingLabel || link.textContent || '' ).replace( /\s+/g, ' ' ).trim();
		if ( existingText.toLowerCase().includes( newTabText.toLowerCase() ) ) {
			link.dataset.upliandaNewTabAnnouncement = 'true';
			return;
		}

		if ( link.hasAttribute( 'aria-labelledby' ) ) {
			newTabNoticeId += 1;
			var notice = document.createElement( 'span' );
			notice.id = 'uplianda-dashboard-new-tab-notice-' + newTabNoticeId;
			notice.className = 'uplianda-dashboard-new-tab-notice';
			notice.textContent = newTabText;
			link.appendChild( notice );

			var describedBy = ( link.getAttribute( 'aria-describedby' ) || '' ).split( /\s+/ ).filter( Boolean );
			describedBy.push( notice.id );
			link.setAttribute( 'aria-describedby', describedBy.join( ' ' ) );
		} else {
			var image = link.querySelector( 'img[alt]' );
			var linkName = existingText || ( image ? image.getAttribute( 'alt' ).trim() : '' ) || link.getAttribute( 'title' ) || link.href;
			link.setAttribute( 'aria-label', linkName + ' (' + newTabText + ')' );
		}

		link.dataset.upliandaNewTabAnnouncement = 'true';
	}

	function prepareLink( link ) {
		var target = ( link.getAttribute( 'target' ) || '' ).toLowerCase();
		if ( ! target || '_self' === target ) {
			link.setAttribute( 'target', '_blank' );
		}

		if ( '_blank' === link.getAttribute( 'target' ).toLowerCase() ) {
			var rel = ( link.getAttribute( 'rel' ) || '' ).split( /\s+/ ).filter( Boolean );
			[ 'noopener', 'noreferrer' ].forEach( function ( value ) {
				if ( ! rel.includes( value ) ) {
					rel.push( value );
				}
			} );
			link.setAttribute( 'rel', rel.join( ' ' ) );
			addNewTabAnnouncement( link );
		}
	}

	function prepareLinks( root ) {
		if ( root.matches && root.matches( 'a[href]' ) ) {
			prepareLink( root );
		}

		if ( root.querySelectorAll ) {
			root.querySelectorAll( 'a[href]' ).forEach( prepareLink );
		}
	}

	function getContentHeight() {
		if ( ! content ) {
			return 480;
		}

		// Measure the dashboard wrapper rather than the document scroll height.
		// Theme rules such as min-height: 100vh make scrollHeight track the
		// iframe viewport and can otherwise create an endless growth loop.
		return Math.max( 480, Math.ceil( content.getBoundingClientRect().bottom + window.scrollY ) );
	}

	function reportHeight() {
		if ( scheduled ) {
			return;
		}

		scheduled = true;
		window.requestAnimationFrame( function () {
			scheduled = false;
			window.parent.postMessage(
				{
					type: 'uplianda-dashboard-height',
					height: getContentHeight(),
				},
				window.location.origin
			);
		} );
	}

	window.addEventListener( 'load', reportHeight );
	window.addEventListener( 'resize', reportHeight );
	window.addEventListener( 'message', function ( event ) {
		if ( event.origin !== window.location.origin || event.source !== window.parent ) {
			return;
		}

		if ( ! event.data || 'uplianda-dashboard-style-preview' !== event.data.type || ! event.data.styles ) {
			return;
		}

		Object.keys( event.data.styles ).forEach( function ( cssVariable ) {
			var isDashboardToken = 0 === cssVariable.indexOf( '--uplianda-dashboard-' );
			var isComponentToken = [
				'--uplianda-bg-hover',
				'--uplianda-bg-secondary',
				'--uplianda-button-active-text',
				'--uplianda-chart-line',
				'--uplianda-chart-bar',
				'--uplianda-progress-color',
			].includes( cssVariable );
			if ( ! isDashboardToken && ! isComponentToken ) {
				return;
			}

			document.body.style.setProperty( cssVariable, String( event.data.styles[ cssVariable ] ) );
		} );

		reportHeight();
	} );

	if ( content && 'ResizeObserver' in window ) {
		new window.ResizeObserver( reportHeight ).observe( content );
	}

	prepareLinks( document );
	if ( content && 'MutationObserver' in window ) {
		new window.MutationObserver( function ( mutations ) {
			mutations.forEach( function ( mutation ) {
				mutation.addedNodes.forEach( prepareLinks );
			} );
		} ).observe( content, { childList: true, subtree: true } );
	}

	reportHeight();
}() );
