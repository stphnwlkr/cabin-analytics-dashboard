( function () {
	'use strict';

	const dashboard = document.querySelector( '[data-uplianda-dashboard]' );
	if ( ! dashboard ) {
		return;
	}

	const toggle = dashboard.querySelector( '[data-uplianda-theme-toggle]' );
	const label = dashboard.querySelector( '[data-uplianda-theme-label]' );
	const themeIcon = dashboard.querySelector( '[data-uplianda-theme-icon]' );
	const storageKey = 'uplianda-dashboard-theme';
	const topbar = dashboard.querySelector( '.uplianda-dash-topbar' );

	function updateTopbarHeight() {
		if ( topbar ) {
			dashboard.style.setProperty( '--uplianda-dash-topbar-height', Math.ceil( topbar.getBoundingClientRect().height ) + 'px' );
		}
	}

	updateTopbarHeight();
	if ( topbar && 'ResizeObserver' in window ) {
		new ResizeObserver( updateTopbarHeight ).observe( topbar );
	} else {
		window.addEventListener( 'resize', updateTopbarHeight );
	}

	function preferredTheme() {
		const saved = window.localStorage.getItem( storageKey );
		if ( saved === 'light' || saved === 'dark' ) {
			return saved;
		}
		return window.matchMedia( '(prefers-color-scheme: dark)' ).matches ? 'dark' : 'light';
	}

	function setTheme( theme ) {
		document.documentElement.dataset.upliandaDashboardTheme = theme;
		dashboard.dataset.theme = theme;
		if ( toggle ) {
			toggle.setAttribute( 'aria-pressed', theme === 'dark' ? 'true' : 'false' );
			const action = theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode';
			toggle.setAttribute( 'aria-label', action );
			toggle.setAttribute( 'title', action );
		}
		if ( label ) {
			label.textContent = theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode';
		}
		if ( themeIcon ) {
			themeIcon.textContent = theme === 'dark' ? '☀' : '◐';
		}
	}

	setTheme( preferredTheme() );
	if ( toggle ) {
		toggle.addEventListener( 'click', function () {
			const theme = dashboard.dataset.theme === 'dark' ? 'light' : 'dark';
			window.localStorage.setItem( storageKey, theme );
			setTheme( theme );
		} );
	}

	const rangeLinks = Array.from( dashboard.querySelectorAll( '.uplianda-dash-ranges a' ) );
	rangeLinks.forEach( function ( link, index ) {
		link.addEventListener( 'click', function ( event ) {
			if ( event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || link.hasAttribute( 'aria-current' ) ) {
				return;
			}
			event.preventDefault();
			dashboard.classList.add( 'is-navigating' );
			dashboard.setAttribute( 'aria-busy', 'true' );
			window.setTimeout( function () {
				window.location.assign( link.href );
			}, window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ? 0 : 140 );
		} );

		link.addEventListener( 'keydown', function ( event ) {
			let targetIndex = null;
			if ( event.key === 'ArrowRight' || event.key === 'ArrowDown' ) {
				targetIndex = ( index + 1 ) % rangeLinks.length;
			} else if ( event.key === 'ArrowLeft' || event.key === 'ArrowUp' ) {
				targetIndex = ( index - 1 + rangeLinks.length ) % rangeLinks.length;
			} else if ( event.key === 'Home' ) {
				targetIndex = 0;
			} else if ( event.key === 'End' ) {
				targetIndex = rangeLinks.length - 1;
			}
			if ( null !== targetIndex ) {
				event.preventDefault();
				rangeLinks[ targetIndex ].focus();
			}
		} );
	} );

	window.addEventListener( 'pageshow', function () {
		dashboard.classList.remove( 'is-navigating' );
		dashboard.removeAttribute( 'aria-busy' );
	} );

	dashboard.querySelectorAll( '[data-uplianda-chart-panel]' ).forEach( function ( panel ) {
		const buttons = Array.from( panel.querySelectorAll( '[data-uplianda-chart-type]' ) );
		const views = Array.from( panel.querySelectorAll( '[data-uplianda-chart-view]' ) );

		function selectChartType( type ) {
			buttons.forEach( function ( button ) {
				button.setAttribute( 'aria-pressed', button.dataset.upliandaChartType === type ? 'true' : 'false' );
			} );
			views.forEach( function ( view ) {
				view.hidden = view.dataset.upliandaChartView !== type;
			} );
		}

		buttons.forEach( function ( button, index ) {
			button.addEventListener( 'click', function () {
				selectChartType( button.dataset.upliandaChartType );
			} );
			button.addEventListener( 'keydown', function ( event ) {
				if ( event.key !== 'ArrowLeft' && event.key !== 'ArrowRight' ) {
					return;
				}
				event.preventDefault();
				const direction = event.key === 'ArrowRight' ? 1 : -1;
				const next = buttons[ ( index + direction + buttons.length ) % buttons.length ];
				next.focus();
				selectChartType( next.dataset.upliandaChartType );
			} );
		} );
	} );

	dashboard.querySelectorAll( '[data-uplianda-chart]' ).forEach( function ( chart ) {
		const svg = chart.querySelector( 'svg' );
		const hits = Array.from( chart.querySelectorAll( '[data-uplianda-chart-hit]' ) );
		const guide = chart.querySelector( '[data-uplianda-chart-guide]' );
		const point = chart.querySelector( '[data-uplianda-chart-point]' );
		const tooltip = chart.querySelector( '[data-uplianda-chart-tooltip]' );
		const date = chart.querySelector( '[data-uplianda-chart-tooltip-date]' );
		const primaryLabel = chart.querySelector( '[data-uplianda-chart-tooltip-primary-label]' );
		const primaryValue = chart.querySelector( '[data-uplianda-chart-tooltip-primary-value]' );
		const secondary = chart.querySelector( '[data-uplianda-chart-tooltip-secondary]' );
		const secondaryLabel = chart.querySelector( '[data-uplianda-chart-tooltip-secondary-label]' );
		const secondaryValue = chart.querySelector( '[data-uplianda-chart-tooltip-secondary-value]' );

		if ( ! svg || ! hits.length || ! guide || ! point || ! tooltip || ! date || ! primaryLabel || ! primaryValue || ! secondary || ! secondaryLabel || ! secondaryValue ) {
			return;
		}

		function hideTooltip() {
			tooltip.hidden = true;
			guide.hidden = true;
			point.hidden = true;
		}

		function positionTooltip( hit ) {
			const chartBox = chart.getBoundingClientRect();
			const svgBox = svg.getBoundingClientRect();
			const chartX = Number.parseFloat( hit.dataset.chartX || '0' );
			const chartY = Number.parseFloat( hit.dataset.chartY || '0' );
			const anchorX = svgBox.left - chartBox.left + ( chartX / 960 ) * svgBox.width;
			const anchorY = svgBox.top - chartBox.top + ( chartY / 300 ) * svgBox.height;
			const tooltipWidth = tooltip.offsetWidth;
			const tooltipHeight = tooltip.offsetHeight;
			const halfWidth = tooltipWidth / 2;
			const left = Math.max( halfWidth + 6, Math.min( chartBox.width - halfWidth - 6, anchorX ) );
			let top = anchorY - tooltipHeight - 14;

			if ( top < 6 ) {
				top = anchorY + 14;
			}
			top = Math.max( 6, Math.min( chartBox.height - tooltipHeight - 6, top ) );
			tooltip.style.left = left + 'px';
			tooltip.style.top = top + 'px';
		}

		function showTooltip( hit ) {
			date.textContent = hit.dataset.chartDate || '';
			primaryLabel.textContent = hit.dataset.primaryLabel || '';
			primaryValue.textContent = hit.dataset.primaryValue || '';
			secondaryLabel.textContent = hit.dataset.secondaryLabel || '';
			secondaryValue.textContent = hit.dataset.secondaryValue || '';
			secondary.hidden = ! hit.dataset.secondaryLabel;
			guide.setAttribute( 'x1', hit.dataset.chartX || '0' );
			guide.setAttribute( 'x2', hit.dataset.chartX || '0' );
			guide.hidden = false;
			point.setAttribute( 'cx', hit.dataset.chartX || '0' );
			point.setAttribute( 'cy', hit.dataset.chartY || '0' );
			point.hidden = 'line' !== chart.dataset.chartType;
			tooltip.hidden = false;
			positionTooltip( hit );
		}

		hits.forEach( function ( hit ) {
			hit.addEventListener( 'pointerenter', function () {
				showTooltip( hit );
			} );
			hit.addEventListener( 'pointermove', function () {
				showTooltip( hit );
			} );
			hit.addEventListener( 'pointerdown', function () {
				showTooltip( hit );
			} );
		} );

		svg.addEventListener( 'pointerleave', function ( event ) {
			if ( 'touch' !== event.pointerType ) {
				hideTooltip();
			}
		} );
		document.addEventListener( 'pointerdown', function ( event ) {
			if ( ! chart.contains( event.target ) ) {
				hideTooltip();
			}
		} );
	} );

	dashboard.querySelectorAll( '[data-uplianda-list]' ).forEach( function ( list ) {
		const pageSize = Number.parseInt( list.dataset.pageSize || '10', 10 );
		const items = Array.from( list.querySelectorAll( '[data-uplianda-list-item]' ) );
		const search = list.querySelector( '[data-uplianda-list-search]' );
		const previous = list.querySelector( '[data-uplianda-list-previous]' );
		const next = list.querySelector( '[data-uplianda-list-next]' );
		const pageOutput = list.querySelector( '[data-uplianda-list-page]' );
		const pagination = list.querySelector( '.uplianda-dash-list-pagination' );
		const viewAll = list.querySelector( '[data-uplianda-list-view-all]' );
		const viewAllLabel = list.querySelector( '[data-uplianda-list-view-all-label]' );
		const footer = list.querySelector( '.uplianda-dash-list-footer' );
		const empty = list.querySelector( '[data-uplianda-list-empty]' );
		const status = list.querySelector( '[data-uplianda-list-status]' );

		if ( items.length <= pageSize || ! search || ! previous || ! next || ! pageOutput || ! pagination || ! viewAll || ! viewAllLabel || ! footer || ! empty || ! status ) {
			return;
		}

		let currentPage = 1;
		let showAll = false;

		function matchingItems() {
			const query = search.value.trim().toLocaleLowerCase();
			return items.filter( function ( item ) {
				return ! query || item.textContent.toLocaleLowerCase().includes( query );
			} );
		}

		function renderList() {
			const matches = matchingItems();
			const pageCount = Math.max( 1, Math.ceil( matches.length / pageSize ) );
			currentPage = Math.min( currentPage, pageCount );
			const start = showAll ? 0 : ( currentPage - 1 ) * pageSize;
			const end = showAll ? matches.length : Math.min( start + pageSize, matches.length );
			const visible = new Set( matches.slice( start, end ) );

			items.forEach( function ( item ) {
				item.hidden = ! visible.has( item );
				const measure = item.nextElementSibling;
				if ( measure && measure.hasAttribute( 'data-uplianda-list-measure' ) ) {
					measure.hidden = item.hidden;
				}
			} );

			pageOutput.textContent = currentPage + ' / ' + pageCount;
			previous.disabled = showAll || currentPage <= 1;
			next.disabled = showAll || currentPage >= pageCount;
			pagination.hidden = showAll || matches.length <= pageSize;
			footer.hidden = matches.length <= pageSize;
			empty.hidden = matches.length > 0;
			viewAll.setAttribute( 'aria-pressed', showAll ? 'true' : 'false' );
			viewAllLabel.textContent = showAll ? viewAll.dataset.paginateText : viewAll.dataset.viewAllText;
			status.textContent = matches.length ? ( start + 1 ) + '–' + end + ' / ' + matches.length : '0 / 0';
		}

		search.addEventListener( 'input', function () {
			currentPage = 1;
			showAll = false;
			renderList();
		} );

		previous.addEventListener( 'click', function () {
			currentPage = Math.max( 1, currentPage - 1 );
			renderList();
		} );

		next.addEventListener( 'click', function () {
			currentPage += 1;
			renderList();
		} );

		viewAll.addEventListener( 'click', function () {
			const shouldScrollToCard = showAll;
			showAll = ! showAll;
			currentPage = 1;
			renderList();

			if ( shouldScrollToCard ) {
				const card = list.closest( '.uplianda-dash-card' ) || list;
				card.scrollIntoView( {
					behavior: window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ? 'auto' : 'smooth',
					block: 'start',
				} );
			}
		} );

		renderList();
	} );

	dashboard.querySelectorAll( '[data-uplianda-country-list]' ).forEach( function ( list ) {
		const buttons = Array.from( list.querySelectorAll( '[data-uplianda-country-mode]' ) );
		const values = Array.from( list.querySelectorAll( '[data-uplianda-country-value]' ) );

		function selectMode( mode ) {
			buttons.forEach( function ( button ) {
				button.setAttribute( 'aria-pressed', button.dataset.upliandaCountryMode === mode ? 'true' : 'false' );
			} );
			values.forEach( function ( value ) {
				value.textContent = 'percent' === mode ? value.dataset.percent : value.dataset.count;
			} );
		}

		buttons.forEach( function ( button, index ) {
			button.addEventListener( 'click', function () {
				selectMode( button.dataset.upliandaCountryMode );
			} );
			button.addEventListener( 'keydown', function ( event ) {
				if ( 'ArrowLeft' !== event.key && 'ArrowRight' !== event.key ) {
					return;
				}
				event.preventDefault();
				const direction = 'ArrowRight' === event.key ? 1 : -1;
				const next = buttons[ ( index + direction + buttons.length ) % buttons.length ];
				next.focus();
				selectMode( next.dataset.upliandaCountryMode );
			} );
		} );
	} );

	const pageModal = dashboard.querySelector( '[data-uplianda-page-modal]' );
	const pageDataNode = dashboard.querySelector( '[data-uplianda-page-details]' );
	if ( pageModal && pageDataNode ) {
		let pageDetails = {};
		try {
			pageDetails = JSON.parse( pageDataNode.textContent || '{}' );
		} catch ( error ) {
			pageDetails = {};
		}

		const numberFormat = new Intl.NumberFormat();
		const decimalFormat = new Intl.NumberFormat( undefined, { maximumFractionDigits: 1 } );
		const preciseFormat = new Intl.NumberFormat( undefined, { maximumFractionDigits: 4 } );
		const path = pageModal.querySelector( '[data-uplianda-page-path]' );
		const title = pageModal.querySelector( '[data-uplianda-page-title]' );
		const openLink = pageModal.querySelector( '[data-uplianda-page-open]' );
		const closeButton = pageModal.querySelector( '[data-uplianda-page-close]' );
		const visitors = pageModal.querySelector( '[data-uplianda-page-visitors]' );
		const visitorShare = pageModal.querySelector( '[data-uplianda-page-visitor-share]' );
		const views = pageModal.querySelector( '[data-uplianda-page-views]' );
		const viewShare = pageModal.querySelector( '[data-uplianda-page-view-share]' );
		const duration = pageModal.querySelector( '[data-uplianda-page-duration]' );
		const depth = pageModal.querySelector( '[data-uplianda-page-depth]' );
		const depthCard = pageModal.querySelector( '[data-uplianda-page-depth-card]' );
		const depthStrip = pageModal.querySelector( '[data-uplianda-page-depth-strip]' );
		const depthEmpty = pageModal.querySelector( '[data-uplianda-page-depth-empty]' );
		const bytes = pageModal.querySelector( '[data-uplianda-page-bytes]' );
		const co2 = pageModal.querySelector( '[data-uplianda-page-co2]' );
		const energyCard = pageModal.querySelector( '[data-uplianda-page-energy-card]' );
		const eventsList = pageModal.querySelector( '[data-uplianda-page-events]' );
		const eventsEmpty = pageModal.querySelector( '[data-uplianda-page-events-empty]' );
		const eventsPagination = pageModal.querySelector( '[data-uplianda-page-events-pagination]' );
		const eventsPrevious = pageModal.querySelector( '[data-uplianda-page-events-previous]' );
		const eventsNext = pageModal.querySelector( '[data-uplianda-page-events-next]' );
		const eventsPageOutput = pageModal.querySelector( '[data-uplianda-page-events-page]' );
		const eventsPageSize = 7;
		let currentEventRows = [];
		let currentEventPage = 1;
		let opener = null;

		function percentage( value ) {
			const numeric = Number( value ) || 0;
			return decimalFormat.format( numeric <= 1 ? numeric * 100 : numeric ) + '%';
		}

		function formatDuration( value ) {
			const seconds = Math.max( 0, Math.round( Number( value ) || 0 ) );
			if ( seconds >= 3600 ) {
				return Math.floor( seconds / 3600 ) + 'h ' + Math.floor( ( seconds % 3600 ) / 60 ) + 'm';
			}
			if ( seconds >= 60 ) {
				return Math.floor( seconds / 60 ) + 'm ' + ( seconds % 60 ) + 's';
			}
			return seconds + 's';
		}

		function formatBytes( value ) {
			return decimalFormat.format( Math.max( 0, Number( value ) || 0 ) ) + ' MB';
		}

		function renderDepth( scrollDepth ) {
			const rows = scrollDepth && Array.isArray( scrollDepth.reached_at_least ) ? scrollDepth.reached_at_least : [];
			const average = Number( scrollDepth && scrollDepth.average_percentage ) || 0;
			depth.textContent = decimalFormat.format( average ) + '%';
			depthStrip.replaceChildren();
			depthCard.hidden = ! rows.length;
			depthEmpty.hidden = rows.length > 0;

			rows.filter( function ( row ) { return Number( row.percent ) > 0; } ).forEach( function ( row ) {
				const segment = document.createElement( 'button' );
				const reached = Number( row.visitors_percentage ) || 0;
				const labelText = decimalFormat.format( reached ) + '% got this far';
				segment.type = 'button';
				segment.style.setProperty( '--uplianda-page-depth-reach', Math.max( 0.16, reached / 100 ) );
				segment.dataset.label = labelText;
				segment.setAttribute( 'aria-label', Number( row.percent ) + '% page depth: ' + labelText );
				depthStrip.appendChild( segment );
			} );
			depthStrip.style.setProperty( '--uplianda-page-depth-average', Math.max( 0, Math.min( 100, average ) ) + '%' );
		}

		function drawEvents() {
			const pageCount = Math.max( 1, Math.ceil( currentEventRows.length / eventsPageSize ) );
			currentEventPage = Math.min( currentEventPage, pageCount );
			const start = ( currentEventPage - 1 ) * eventsPageSize;
			const rows = currentEventRows.slice( start, start + eventsPageSize );
			const maximum = Math.max.apply( null, currentEventRows.map( function ( event ) { return Number( event.count ) || 0; } ).concat( [ 1 ] ) );
			eventsList.replaceChildren();
			eventsEmpty.hidden = currentEventRows.length > 0;
			eventsPagination.hidden = currentEventRows.length <= eventsPageSize;
			eventsPrevious.disabled = currentEventPage <= 1;
			eventsNext.disabled = currentEventPage >= pageCount;
			eventsPageOutput.textContent = currentEventPage + ' / ' + pageCount;

			rows.forEach( function ( event ) {
				const item = document.createElement( 'li' );
				const row = document.createElement( 'div' );
				const name = document.createElement( 'span' );
				const count = document.createElement( 'strong' );
				const track = document.createElement( 'span' );
				const measure = document.createElement( 'i' );
				name.textContent = event.name || '';
				count.textContent = numberFormat.format( Number( event.count ) || 0 );
				row.append( name, count );
				track.className = 'uplianda-dash-page-modal__event-track';
				measure.style.width = Math.max( 1, ( ( Number( event.count ) || 0 ) / maximum ) * 100 ) + '%';
				track.appendChild( measure );
				item.append( row, track );
				eventsList.appendChild( item );
			} );
		}

		function renderEvents( eventRows ) {
			currentEventRows = Array.isArray( eventRows ) ? eventRows : [];
			currentEventPage = 1;
			drawEvents();
		}

		function populatePageModal( detail ) {
			const pageViews = Number( detail.page_views ) || 0;
			const uniqueVisitors = Number( detail.unique_visitors ) || 0;
			const hasEnergy = Number( detail.total_bytes ) > 0 || Number( detail.co2_grams ) > 0;
			path.textContent = detail.path || '/';
			title.textContent = detail.title || detail.path || '/';
			openLink.href = detail.url || '#';
			visitors.textContent = numberFormat.format( uniqueVisitors );
			visitorShare.textContent = pageViews ? percentage( uniqueVisitors / pageViews ) + ' of pageviews' : '';
			views.textContent = numberFormat.format( pageViews );
			viewShare.textContent = Number( detail.page_views_percentage ) > 0 ? percentage( detail.page_views_percentage ) + ' of all pageviews' : '';
			duration.textContent = formatDuration( detail.average_duration_seconds );
			bytes.textContent = formatBytes( detail.total_bytes );
			co2.textContent = Number( detail.co2_grams ) > 0 ? preciseFormat.format( Number( detail.co2_grams ) ) + ' g' : 'N/A';
			energyCard.hidden = ! hasEnergy;
			renderDepth( detail.scroll_depth || {} );
			renderEvents( detail.events || [] );
		}

		dashboard.querySelectorAll( '[data-uplianda-page-open-modal]' ).forEach( function ( button ) {
			button.addEventListener( 'click', function () {
				const detail = pageDetails[ button.dataset.upliandaPageOpenModal ];
				if ( ! detail ) {
					return;
				}
				opener = button;
				populatePageModal( detail );
				dashboard.classList.add( 'has-page-modal' );
				if ( 'function' === typeof pageModal.showModal ) {
					pageModal.showModal();
				} else {
					pageModal.setAttribute( 'open', '' );
				}
				closeButton.focus();
			} );
		} );

		dashboard.querySelectorAll( '.uplianda-dash-pages__row' ).forEach( function ( row ) {
			const button = row.querySelector( '[data-uplianda-page-open-modal]' );
			if ( ! button ) {
				return;
			}
			row.addEventListener( 'click', function ( event ) {
				if ( event.target.closest( 'a, button, input, select, textarea' ) || window.getSelection().toString() ) {
					return;
				}
				button.click();
			} );
		} );

		eventsPrevious.addEventListener( 'click', function () {
			currentEventPage = Math.max( 1, currentEventPage - 1 );
			drawEvents();
		} );

		eventsNext.addEventListener( 'click', function () {
			currentEventPage += 1;
			drawEvents();
		} );

		function closePageModal() {
			if ( 'function' === typeof pageModal.close ) {
				pageModal.close();
			} else {
				pageModal.removeAttribute( 'open' );
			}
		}

		closeButton.addEventListener( 'click', closePageModal );
		pageModal.addEventListener( 'click', function ( event ) {
			if ( event.target === pageModal ) {
				closePageModal();
			}
		} );
		pageModal.addEventListener( 'close', function () {
			dashboard.classList.remove( 'has-page-modal' );
			if ( opener ) {
				opener.focus();
			}
		} );
		pageModal.addEventListener( 'cancel', function () {
			dashboard.classList.remove( 'has-page-modal' );
		} );
	}

	const railLinks = Array.from( dashboard.querySelectorAll( '.uplianda-dash-rail a[href^="#"]' ) );
	const sections = railLinks.map( function ( link ) {
		return document.querySelector( link.getAttribute( 'href' ) );
	} ).filter( Boolean );

	if ( 'IntersectionObserver' in window ) {
		const observer = new IntersectionObserver( function ( entries ) {
			const visible = entries.filter( function ( entry ) { return entry.isIntersecting; } ).sort( function ( a, b ) { return b.intersectionRatio - a.intersectionRatio; } )[ 0 ];
			if ( ! visible ) {
				return;
			}
			railLinks.forEach( function ( link ) {
				if ( link.getAttribute( 'href' ) === '#' + visible.target.id ) {
					link.setAttribute( 'aria-current', 'location' );
				} else {
					link.removeAttribute( 'aria-current' );
				}
			} );
		}, { rootMargin: '-15% 0px -65% 0px', threshold: [ 0, 0.2, 0.5 ] } );
		sections.forEach( function ( section ) { observer.observe( section ); } );
	}
} )();
