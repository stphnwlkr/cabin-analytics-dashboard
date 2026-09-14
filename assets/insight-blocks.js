( function ( blocks, element, i18n, blockEditor, components, serverSideRender ) {
	'use strict';

	const el = element.createElement;
	const __ = i18n.__;
	const InspectorControls = blockEditor.InspectorControls;
	const useBlockProps = blockEditor.useBlockProps;
	const PanelBody = components.PanelBody;
	const Notice = components.Notice;
	const Spinner = components.Spinner;
	const ServerSideRender = serverSideRender.default || serverSideRender;
	const SelectControl = components.SelectControl;
	const TextControl = components.TextControl;
	const ToggleControl = components.ToggleControl;
	const dateOptions = [
		{ label: __( 'Last 7 Days', 'uplink-analytics-dashboard-for-cabin' ), value: '7' },
		{ label: __( 'Last 14 Days', 'uplink-analytics-dashboard-for-cabin' ), value: '14' },
		{ label: __( 'Last 30 Days', 'uplink-analytics-dashboard-for-cabin' ), value: '30' },
		{ label: __( 'Last 90 Days', 'uplink-analytics-dashboard-for-cabin' ), value: '90' },
	];
	const definitions = {
		sustainability: {
			title: __( 'Uplink Sustainability for Cabin', 'uplink-analytics-dashboard-for-cabin' ),
			defaultHeading: __( 'Sustainability', 'uplink-analytics-dashboard-for-cabin' ),
			icon: 'cloud',
			description: __( 'CO₂, transfer volume, and green-hosting status.', 'uplink-analytics-dashboard-for-cabin' ),
			modules: [
				{ attribute: 'showTotalCo2', label: __( 'Total CO₂', 'uplink-analytics-dashboard-for-cabin' ) },
				{ attribute: 'showAverageCo2', label: __( 'Average CO₂ per page', 'uplink-analytics-dashboard-for-cabin' ) },
				{ attribute: 'showTransferVolume', label: __( 'Transfer volume', 'uplink-analytics-dashboard-for-cabin' ) },
				{ attribute: 'showGreenHosting', label: __( 'Green-hosting status', 'uplink-analytics-dashboard-for-cabin' ) },
			],
		},
		demographics: {
			title: __( 'Uplink Demographics for Cabin', 'uplink-analytics-dashboard-for-cabin' ),
			defaultHeading: __( 'Demographics', 'uplink-analytics-dashboard-for-cabin' ),
			icon: 'groups',
			description: __( 'Device, country, language, and browser mix.', 'uplink-analytics-dashboard-for-cabin' ),
			modules: [
				{ attribute: 'showDevices', label: __( 'Device mix', 'uplink-analytics-dashboard-for-cabin' ) },
				{ attribute: 'showCountries', label: __( 'Countries', 'uplink-analytics-dashboard-for-cabin' ) },
				{ attribute: 'showLanguages', label: __( 'Languages', 'uplink-analytics-dashboard-for-cabin' ) },
				{ attribute: 'showBrowsers', label: __( 'Browsers', 'uplink-analytics-dashboard-for-cabin' ) },
			],
		},
		sources: {
			title: __( 'Uplink Sources for Cabin', 'uplink-analytics-dashboard-for-cabin' ),
			defaultHeading: __( 'Sources', 'uplink-analytics-dashboard-for-cabin' ),
			icon: 'networking',
			description: __( 'Traffic sources and top referrers.', 'uplink-analytics-dashboard-for-cabin' ),
			modules: [
				{ attribute: 'showTrafficSources', label: __( 'Traffic sources', 'uplink-analytics-dashboard-for-cabin' ) },
				{ attribute: 'showReferrers', label: __( 'Top referrers', 'uplink-analytics-dashboard-for-cabin' ) },
			],
		},
		'country-map': {
			title: __( 'Uplink Country Traffic Map for Cabin', 'uplink-analytics-dashboard-for-cabin' ),
			defaultHeading: __( 'Country Traffic', 'uplink-analytics-dashboard-for-cabin' ),
			icon: 'location-alt',
			description: __( 'An accessible, privacy-friendly map of aggregated Cabin traffic by country.', 'uplink-analytics-dashboard-for-cabin' ),
			modules: [],
		},
	};

	Object.keys( definitions ).forEach( ( section ) => {
		const definition = definitions[ section ];

		blocks.registerBlockType( 'uplianda/' + section, {
			edit: ( props ) => {
				const dateRange = props.attributes.dateRange || '30';
				return el(
					element.Fragment,
					null,
					el(
						InspectorControls,
						null,
						el(
							PanelBody,
							{ title: __( 'Uplink Settings', 'uplink-analytics-dashboard-for-cabin' ), initialOpen: true },
							el( TextControl, {
								label: __( 'Heading', 'uplink-analytics-dashboard-for-cabin' ),
								value: props.attributes.title || '',
								onChange: ( value ) => props.setAttributes( { title: value } ),
								placeholder: definition.defaultHeading,
								help: __( 'Leave empty to use the default heading.', 'uplink-analytics-dashboard-for-cabin' ),
							} ),
							el( SelectControl, {
								label: __( 'Reporting period', 'uplink-analytics-dashboard-for-cabin' ),
								value: dateRange,
								options: dateOptions,
								onChange: ( value ) => props.setAttributes( { dateRange: value } ),
							} ),
							definition.modules.length > 0 ? el(
								element.Fragment,
								null,
								el( 'hr' ),
								el( 'p', null, el( 'strong', null, __( 'Visible modules', 'uplink-analytics-dashboard-for-cabin' ) ) ),
								definition.modules.map( ( module ) => el( ToggleControl, {
									key: module.attribute,
									label: module.label,
									checked: props.attributes[ module.attribute ] !== false,
									onChange: ( value ) => props.setAttributes( { [ module.attribute ]: value } ),
								} ) )
							) : null
						)
					),
					el(
						'div',
						useBlockProps(),
						el( ServerSideRender, {
							block: 'uplianda/' + section,
							attributes: props.attributes,
							LoadingResponsePlaceholder: () => el( 'div', { className: 'uplianda-block-preview-status' }, el( Spinner ), __( 'Loading Cabin data…', 'uplink-analytics-dashboard-for-cabin' ) ),
							ErrorResponsePlaceholder: () => el( Notice, { status: 'error', isDismissible: false }, __( 'The Cabin preview could not be loaded.', 'uplink-analytics-dashboard-for-cabin' ) ),
							EmptyResponsePlaceholder: () => el( Notice, { status: 'warning', isDismissible: false }, __( 'Cabin did not return data for this block.', 'uplink-analytics-dashboard-for-cabin' ) ),
						} )
					)
				);
			},
			save: () => null,
		} );
	} );
} )( window.wp.blocks, window.wp.element, window.wp.i18n, window.wp.blockEditor, window.wp.components, window.wp.serverSideRender );
