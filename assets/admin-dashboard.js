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
