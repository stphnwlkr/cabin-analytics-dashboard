( function () {
	'use strict';

	var instanceCount = 0;

	function escapeHtml( value ) {
		var element = document.createElement( 'div' );
		element.textContent = String( value ?? '' );
		return element.innerHTML;
	}

	function isoDate( date ) {
		return date.toLocaleDateString( 'en-CA' );
	}

	function shortDate( value ) {
		var date = new Date( value + 'T00:00:00' );
		return ( date.getMonth() + 1 ) + '/' + date.getDate();
	}

	function longDate( value ) {
		return new Date( value + 'T00:00:00' ).toLocaleDateString( undefined, {
			month: 'short',
			day: 'numeric',
			year: 'numeric',
		} );
	}

	function normalizeData( data, startDate, endDate ) {
		var dates = [];
		var pageviews = [];
		var visitors = [];

		if ( data && Array.isArray( data.daily_data ) ) {
			data.daily_data.forEach( function ( item ) {
				if ( ! item.timestamp || ( null === item.page_views && null === item.unique_visitors ) ) {
					return;
				}

				var date = new Date( item.timestamp );
				dates.push(
					date.getUTCFullYear() + '-' +
					String( date.getUTCMonth() + 1 ).padStart( 2, '0' ) + '-' +
					String( date.getUTCDate() ).padStart( 2, '0' )
				);
				pageviews.push( Number.parseInt( item.page_views, 10 ) || 0 );
				visitors.push( Number.parseInt( item.unique_visitors, 10 ) || 0 );
			} );
		}

		return {
			dates: dates,
			pageviews: pageviews,
			visitors: visitors,
			totalPageviews: Number.parseInt( data?.summary?.page_views, 10 ) || 0,
			totalVisitors: Number.parseInt( data?.summary?.unique_visitors, 10 ) || 0,
			bounceRate: Number.parseFloat( data?.summary?.bounce_rate ) || 0,
			startDate: startDate,
			endDate: endDate,
		};
	}

	function svgElement( name, attributes ) {
		var element = document.createElementNS( 'http://www.w3.org/2000/svg', name );
		Object.keys( attributes ).forEach( function ( attribute ) {
			element.setAttribute( attribute, attributes[ attribute ] );
		} );
		return element;
	}

	function drawAxes( svg, data, dimensions ) {
		var maximum = Math.max( 1, ...data.pageviews );
		for ( var index = 0; index <= 4; index++ ) {
			var y = dimensions.top + ( dimensions.height / 4 ) * index;
			svg.appendChild( svgElement( 'line', {
				x1: dimensions.left,
				y1: y,
				x2: dimensions.width + dimensions.left,
				y2: y,
				stroke: 'var(--uplianda-grid-line, #e0e0e0)',
				'stroke-width': 1,
			} ) );

			var label = svgElement( 'text', {
				x: dimensions.left - 10,
				y: y + 4,
				'text-anchor': 'end',
				'font-size': 12,
				fill: 'var(--uplianda-dashboard-color-surface-muted, #666)',
			} );
			label.textContent = Math.round( maximum * ( 1 - index / 4 ) );
			svg.appendChild( label );
		}
		return maximum;
	}

	function addPointInteraction( element, controller, date, pageviews, visitors ) {
		function show( event ) {
			controller.showTooltip( event.currentTarget, date, pageviews, visitors );
		}

		element.addEventListener( 'mouseenter', show );
		element.addEventListener( 'focus', show );
		element.addEventListener( 'mouseleave', controller.hideTooltip );
		element.addEventListener( 'blur', controller.hideTooltip );
		element.addEventListener( 'keydown', function ( event ) {
			if ( 'Enter' === event.key || ' ' === event.key ) {
				event.preventDefault();
				show( event );
			}
		} );
	}

	function drawBarChart( svg, data, controller ) {
		var width = Math.max( svg.clientWidth, 320 );
		var height = Math.max( svg.clientHeight, 200 );
		var margin = { top: 20, right: 20, bottom: 40, left: 50 };
		var dimensions = {
			top: margin.top,
			left: margin.left,
			width: width - margin.left - margin.right,
			height: height - margin.top - margin.bottom,
		};
		var maximum = drawAxes( svg, data, dimensions );
		var step = dimensions.width / Math.max( data.dates.length, 1 );

		svg.setAttribute( 'viewBox', '0 0 ' + width + ' ' + height );
		data.dates.forEach( function ( date, index ) {
			var x = dimensions.left + index * step;
			var visitorHeight = ( data.visitors[ index ] / maximum ) * dimensions.height;
			var pageviewHeight = ( data.pageviews[ index ] / maximum ) * dimensions.height;
			var shared = {
				x: x + step * 0.1,
				width: step * 0.8,
				rx: 2,
				role: 'button',
				tabindex: 0,
				'aria-label': longDate( date ) + ': ' + data.visitors[ index ] + ' unique visitors, ' + data.pageviews[ index ] + ' total views',
			};
			var visitors = svgElement( 'rect', Object.assign( {}, shared, {
				y: dimensions.top + dimensions.height - visitorHeight,
				height: visitorHeight,
				fill: 'var(--uplianda-chart-bar, #2271b1)',
			} ) );
			var pageviews = svgElement( 'rect', Object.assign( {}, shared, {
				y: dimensions.top + dimensions.height - pageviewHeight,
				height: Math.max( 0, pageviewHeight - visitorHeight ),
				fill: 'var(--uplianda-chart-line, #72aee6)',
			} ) );

			addPointInteraction( visitors, controller, date, data.pageviews[ index ], data.visitors[ index ] );
			addPointInteraction( pageviews, controller, date, data.pageviews[ index ], data.visitors[ index ] );
			svg.appendChild( visitors );
			svg.appendChild( pageviews );

			if ( 0 === index % Math.max( 1, Math.ceil( data.dates.length / 10 ) ) ) {
				var label = svgElement( 'text', {
					x: x + step / 2,
					y: height - margin.bottom + 20,
					'text-anchor': 'middle',
					'font-size': 11,
					fill: 'var(--uplianda-dashboard-color-surface-muted, #666)',
				} );
				label.textContent = shortDate( date );
				svg.appendChild( label );
			}
		} );
	}

	function drawLineChart( svg, data, controller ) {
		var width = Math.max( svg.clientWidth, 320 );
		var height = Math.max( svg.clientHeight, 200 );
		var margin = { top: 20, right: 20, bottom: 40, left: 50 };
		var dimensions = {
			top: margin.top,
			left: margin.left,
			width: width - margin.left - margin.right,
			height: height - margin.top - margin.bottom,
		};
		var maximum = drawAxes( svg, data, dimensions );
		var step = data.dates.length > 1 ? dimensions.width / ( data.dates.length - 1 ) : 0;

		svg.setAttribute( 'viewBox', '0 0 ' + width + ' ' + height );
		[
			{ values: data.pageviews, color: 'var(--uplianda-chart-line, #72aee6)' },
			{ values: data.visitors, color: 'var(--uplianda-chart-bar, #2271b1)' },
		].forEach( function ( series ) {
			var points = series.values.map( function ( value, index ) {
				return ( dimensions.left + index * step ) + ',' + ( dimensions.top + dimensions.height - ( value / maximum ) * dimensions.height );
			} );
			svg.appendChild( svgElement( 'polyline', {
				points: points.join( ' ' ),
				fill: 'none',
				stroke: series.color,
				'stroke-width': 3,
				'stroke-linecap': 'round',
				'stroke-linejoin': 'round',
			} ) );
		} );

		data.dates.forEach( function ( date, index ) {
			var x = dimensions.left + index * step;
			[
				{ value: data.pageviews[ index ], color: 'var(--uplianda-chart-line, #72aee6)' },
				{ value: data.visitors[ index ], color: 'var(--uplianda-chart-bar, #2271b1)' },
			].forEach( function ( point ) {
				var circle = svgElement( 'circle', {
					cx: x,
					cy: dimensions.top + dimensions.height - ( point.value / maximum ) * dimensions.height,
					r: 4,
					fill: point.color,
					stroke: 'var(--uplianda-dashboard-color-surface, #fff)',
					'stroke-width': 2,
					role: 'button',
					tabindex: 0,
					'aria-label': longDate( date ) + ': ' + data.visitors[ index ] + ' unique visitors, ' + data.pageviews[ index ] + ' total views',
				} );
				addPointInteraction( circle, controller, date, data.pageviews[ index ], data.visitors[ index ] );
				svg.appendChild( circle );
			} );

			if ( 0 === index % Math.max( 1, Math.ceil( data.dates.length / 10 ) ) ) {
				var label = svgElement( 'text', {
					x: x,
					y: height - margin.bottom + 20,
					'text-anchor': 'middle',
					'font-size': 11,
					fill: 'var(--uplianda-dashboard-color-surface-muted, #666)',
				} );
				label.textContent = shortDate( date );
				svg.appendChild( label );
			}
		} );
	}

	function mount( container, options ) {
		options = Object.assign( {
			title: 'Site Traffic',
			domain: '',
			chartType: 'bar',
			dateRange: 7,
			allowSwitching: true,
			dashboardUrl: '',
			restUrl: '',
		}, options || {} );

		var instanceId = ++instanceCount;
		var controller = {
			container: container,
			data: null,
			chartType: options.chartType || 'bar',
			dateRange: Number.parseInt( options.dateRange, 10 ) || 7,
			request: null,
			resizeObserver: null,
			tooltip: null,
			destroyed: false,
		};

		controller.hideTooltip = function () {
			if ( controller.tooltip ) {
				controller.tooltip.remove();
				controller.tooltip = null;
			}
		};

		controller.showTooltip = function ( target, date, pageviews, visitors ) {
			controller.hideTooltip();
			var tooltip = document.createElement( 'div' );
			tooltip.className = 'uplianda-analytics-tooltip';
			tooltip.setAttribute( 'role', 'tooltip' );
			tooltip.innerHTML = '<strong>' + escapeHtml( longDate( date ) ) + '</strong>' +
				'<div><span class="tooltip-color" style="background:var(--uplianda-chart-line,#72aee6)" aria-hidden="true"></span> Total Views: ' + pageviews.toLocaleString() + '</div>' +
				'<div><span class="tooltip-color" style="background:var(--uplianda-chart-bar,#2271b1)" aria-hidden="true"></span> Unique Visitors: ' + visitors.toLocaleString() + '</div>';
			document.body.appendChild( tooltip );
			var bounds = target.getBoundingClientRect();
			tooltip.style.left = ( bounds.left + window.scrollX ) + 'px';
			tooltip.style.top = ( bounds.top + window.scrollY - tooltip.offsetHeight - 10 ) + 'px';
			controller.tooltip = tooltip;
		};

		controller.draw = function () {
			if ( ! controller.data || controller.destroyed ) {
				return;
			}

			var data = controller.data;
			var engagement = ( 100 * ( 1 - data.bounceRate ) ).toFixed( 1 );
			var dashboardLink = options.dashboardUrl ? '<a href="' + escapeHtml( options.dashboardUrl ) + '" target="_blank" rel="noopener noreferrer" class="uplianda-link" aria-label="Visit Cabin (opens in a new tab)">Visit Cabin →</a>' : '';
			var controls = options.allowSwitching ?
				'<div class="uplianda-analytics-controls">' +
					'<div class="uplianda-dashboard-control"><span id="uplianda-chart-type-' + instanceId + '" class="uplianda-dashboard-control__label">Chart Type</span><div class="uplianda-analytics-chart-toggle uplianda-dashboard-control__button-group" role="group" aria-labelledby="uplianda-chart-type-' + instanceId + '"><button class="uplianda-analytics-chart-button uplianda-dashboard-control__button ' + ( 'bar' === controller.chartType ? 'active' : '' ) + '" data-type="bar" aria-label="Bar Chart" aria-pressed="' + ( 'bar' === controller.chartType ) + '">▥</button><button class="uplianda-analytics-chart-button uplianda-dashboard-control__button ' + ( 'line' === controller.chartType ? 'active' : '' ) + '" data-type="line" aria-label="Line Chart" aria-pressed="' + ( 'line' === controller.chartType ) + '">⌁</button></div></div>' +
					'<div class="uplianda-dashboard-control"><span id="uplianda-date-range-' + instanceId + '" class="uplianda-dashboard-control__label">Date Range</span><div class="uplianda-analytics-button-group uplianda-dashboard-control__button-group" role="group" aria-labelledby="uplianda-date-range-' + instanceId + '">' + [ 7, 14, 30, 90 ].map( function ( range ) { return '<button class="uplianda-analytics-button uplianda-dashboard-control__button ' + ( range === controller.dateRange ? 'active' : '' ) + '" data-range="' + range + '" aria-pressed="' + ( range === controller.dateRange ) + '">' + range + 'd</button>'; } ).join( '' ) + '</div></div>' +
				'</div>' : '';

			container.innerHTML =
				'<div class="uplianda-analytics-header"><div class="uplianda-analytics-title-wrapper"><h2 class="uplianda-analytics-title">' + escapeHtml( options.title || 'Site Traffic' ) + '</h2><div class="uplianda-analytics-domain">' + escapeHtml( options.domain || 'Configured domain' ) + '</div></div>' + controls + '</div>' +
				'<div class="uplianda-analytics-stats"><div class="uplianda-analytics-stat"><div class="uplianda-analytics-stat-label">Total Views</div><div class="uplianda-analytics-stat-value">' + data.totalPageviews.toLocaleString() + '</div></div><div class="uplianda-analytics-stat"><div class="uplianda-analytics-stat-label">Unique Visitors</div><div class="uplianda-analytics-stat-value">' + data.totalVisitors.toLocaleString() + '</div></div><div class="uplianda-analytics-stat"><div class="uplianda-analytics-stat-label">Bounce Rate</div><div class="uplianda-analytics-stat-value">' + engagement + '%</div></div></div>' +
				'<div class="uplianda-analytics-chart-container"><svg class="uplianda-analytics-chart" role="img" aria-label="Analytics chart showing page views and unique visitors over time"></svg></div>' +
				'<div class="uplianda-analytics-footer"><div class="uplianda-analytics-date-range" role="status">' + escapeHtml( longDate( data.startDate ) + ' - ' + longDate( data.endDate ) ) + '</div>' + dashboardLink + '</div>';

			var svg = container.querySelector( '.uplianda-analytics-chart' );
			if ( data.dates.length ) {
				if ( 'line' === controller.chartType ) {
					drawLineChart( svg, data, controller );
				} else {
					drawBarChart( svg, data, controller );
				}
			} else {
				svg.outerHTML = '<p class="uplianda-analytics-loading">No analytics data is available for this period.</p>';
			}

			container.querySelectorAll( '[data-type]' ).forEach( function ( button ) {
				button.addEventListener( 'click', function () {
					controller.chartType = button.dataset.type;
					controller.draw();
				} );
			} );
			container.querySelectorAll( '[data-range]' ).forEach( function ( button ) {
				button.addEventListener( 'click', function () {
					controller.dateRange = Number.parseInt( button.dataset.range, 10 );
					controller.load();
				} );
			} );
		};

		controller.load = function () {
			if ( controller.request ) {
				controller.request.abort();
			}

			var end = new Date();
			var start = new Date();
			start.setDate( end.getDate() - ( controller.dateRange - 1 ) );
			var startDate = isoDate( start );
			var endDate = isoDate( end );
			var restUrl = options.restUrl || ( window.wpApiSettings?.root ? window.wpApiSettings.root + 'uplianda/v1/stats' : '/wp-json/uplianda/v1/stats' );
			var url = new URL( restUrl, window.location.href );
			if ( options.domain ) {
				url.searchParams.set( 'domain', options.domain );
			}
			url.searchParams.set( 'start_date', startDate );
			url.searchParams.set( 'end_date', endDate );

			controller.request = new AbortController();
			container.innerHTML = '<div class="uplianda-analytics-loading" role="status">Loading Cabin analytics…</div>';
			window.fetch( url.toString(), { signal: controller.request.signal, credentials: 'same-origin' } )
				.then( function ( response ) {
					if ( ! response.ok ) {
						return response.json().catch( function () { return {}; } ).then( function ( body ) {
							throw new Error( body.message || 'The analytics request failed.' );
						} );
					}
					return response.json();
				} )
				.then( function ( data ) {
					controller.data = normalizeData( data, startDate, endDate );
					controller.draw();
				} )
				.catch( function ( error ) {
					if ( 'AbortError' !== error.name && ! controller.destroyed ) {
						container.innerHTML = '<div class="uplianda-analytics-error" role="alert">' + escapeHtml( error.message ) + '</div>';
					}
				} );
		};

		controller.destroy = function () {
			controller.destroyed = true;
			controller.hideTooltip();
			if ( controller.request ) {
				controller.request.abort();
			}
			if ( controller.resizeObserver ) {
				controller.resizeObserver.disconnect();
			}
		};

		if ( 'ResizeObserver' in window ) {
			var resizeTimer;
			controller.resizeObserver = new window.ResizeObserver( function () {
				window.clearTimeout( resizeTimer );
				resizeTimer = window.setTimeout( function () {
					if ( controller.data ) {
						controller.draw();
					}
				}, 100 );
			} );
			controller.resizeObserver.observe( container );
		}

		controller.load();
		return controller;
	}

	window.UpliandaChart = { mount: mount };
}() );
