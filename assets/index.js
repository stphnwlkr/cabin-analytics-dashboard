( function ( blocks, element, i18n, blockEditor, components ) {
	'use strict';

	var el = element.createElement;
	var Fragment = element.Fragment;
	var useEffect = element.useEffect;
	var useRef = element.useRef;
	var __ = i18n.__;
	var InspectorControls = blockEditor.InspectorControls;
	var useBlockProps = blockEditor.useBlockProps;
	var PanelBody = components.PanelBody;
	var TextControl = components.TextControl;
	var SelectControl = components.SelectControl;
	var ToggleControl = components.ToggleControl;
	var Notice = components.Notice;
	var defaults = window.UpliandaEditorDefaults || {
		domain: '',
		chartType: 'bar',
		dateRange: '7',
		restUrl: '',
	};
	var chartAnalysisIcon = defaults.chartIconSvg
		? el( 'span', {
			className: 'uplianda-editor-icon',
			dangerouslySetInnerHTML: { __html: defaults.chartIconSvg },
		} )
		: 'chart-bar';

	blocks.registerBlockType( 'uplianda/site-traffic', {
		icon: chartAnalysisIcon,
		edit: function ( props ) {
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;
			var previewRef = useRef( null );
			var title = attributes.title || __( 'Site Traffic', 'uplink-analytics-dashboard-for-cabin' );
			var domain = attributes.domain || defaults.domain || '';
			var chartType = attributes.chartType || defaults.chartType || 'bar';
			var dateRange = attributes.dateRange || defaults.dateRange || '7';
			var allowSwitching = false !== attributes.allowSwitching;
			var dashboardUrl = attributes.dashboardUrl || '';
			var blockProps = useBlockProps();

			useEffect( function () {
				if ( ! previewRef.current || ! window.UpliandaChart ) {
					return undefined;
				}

				var controller = window.UpliandaChart.mount( previewRef.current, {
					title: title,
					domain: domain,
					chartType: chartType,
					dateRange: dateRange,
					allowSwitching: false,
					dashboardUrl: '',
					restUrl: defaults.restUrl || '',
				} );

				return function () {
					controller.destroy();
				};
			}, [ title, domain, chartType, dateRange ] );

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Analytics Settings', 'uplink-analytics-dashboard-for-cabin' ), initialOpen: true },
						el( TextControl, {
							label: __( 'Heading', 'uplink-analytics-dashboard-for-cabin' ),
							value: attributes.title || '',
							onChange: function ( value ) { setAttributes( { title: value } ); },
							placeholder: __( 'Site Traffic', 'uplink-analytics-dashboard-for-cabin' ),
							help: __( 'Leave empty to display “Site Traffic”.', 'uplink-analytics-dashboard-for-cabin' ),
						} ),
						el( TextControl, {
							label: __( 'Domain', 'uplink-analytics-dashboard-for-cabin' ),
							value: attributes.domain || '',
							onChange: function ( value ) { setAttributes( { domain: value } ); },
							help: __( 'Leave empty to use the domain from Cabin Analytics settings.', 'uplink-analytics-dashboard-for-cabin' ),
							placeholder: defaults.domain || 'example.com',
						} ),
						el( SelectControl, {
							label: __( 'Chart Type', 'uplink-analytics-dashboard-for-cabin' ),
							value: attributes.chartType || '',
							options: [
								{ label: __( 'Use Default', 'uplink-analytics-dashboard-for-cabin' ), value: '' },
								{ label: __( 'Stacked Bar Chart', 'uplink-analytics-dashboard-for-cabin' ), value: 'bar' },
								{ label: __( 'Line Chart', 'uplink-analytics-dashboard-for-cabin' ), value: 'line' },
							],
							onChange: function ( value ) { setAttributes( { chartType: value } ); },
						} ),
						el( SelectControl, {
							label: __( 'Date Range', 'uplink-analytics-dashboard-for-cabin' ),
							value: attributes.dateRange || '',
							options: [
								{ label: __( 'Use Default', 'uplink-analytics-dashboard-for-cabin' ), value: '' },
								{ label: __( 'Last 7 Days', 'uplink-analytics-dashboard-for-cabin' ), value: '7' },
								{ label: __( 'Last 14 Days', 'uplink-analytics-dashboard-for-cabin' ), value: '14' },
								{ label: __( 'Last 30 Days', 'uplink-analytics-dashboard-for-cabin' ), value: '30' },
								{ label: __( 'Last 90 Days', 'uplink-analytics-dashboard-for-cabin' ), value: '90' },
							],
							onChange: function ( value ) { setAttributes( { dateRange: value } ); },
						} ),
						el( ToggleControl, {
							label: __( 'Allow User Switching', 'uplink-analytics-dashboard-for-cabin' ),
							checked: allowSwitching,
							onChange: function ( value ) { setAttributes( { allowSwitching: value } ); },
							help: __( 'Allow visitors to change chart type and date range.', 'uplink-analytics-dashboard-for-cabin' ),
						} ),
						el( TextControl, {
							label: __( 'Dashboard URL', 'uplink-analytics-dashboard-for-cabin' ),
							value: dashboardUrl,
							onChange: function ( value ) { setAttributes( { dashboardUrl: value } ); },
							help: __( 'Leave empty to use the Cabin dashboard URL for the configured domain.', 'uplink-analytics-dashboard-for-cabin' ),
							placeholder: 'https://withcabin.com',
						} )
					)
				),
				el(
					'div',
					blockProps,
					! window.UpliandaChart ? el( Notice, { status: 'error', isDismissible: false }, __( 'The Cabin chart renderer could not be loaded.', 'uplink-analytics-dashboard-for-cabin' ) ) : null,
					el( 'div', { className: 'uplianda-analytics-widget uplianda-analytics-widget--editor', ref: previewRef } )
				)
			);
		},
		save: function () {
			return null;
		},
	} );
} )( window.wp.blocks, window.wp.element, window.wp.i18n, window.wp.blockEditor, window.wp.components );
