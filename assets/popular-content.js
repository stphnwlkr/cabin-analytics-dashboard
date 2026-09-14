( function ( blocks, element, i18n, blockEditor, components, serverSideRender ) {
	'use strict';

	var el = element.createElement;
	var __ = i18n.__;
	var useBlockProps = blockEditor.useBlockProps;
	var InspectorControls = blockEditor.InspectorControls;
	var PanelBody = components.PanelBody;
	var TextControl = components.TextControl;
	var SelectControl = components.SelectControl;
	var Notice = components.Notice;
	var Spinner = components.Spinner;
	var ServerSideRender = serverSideRender.default || serverSideRender;

	blocks.registerBlockType( 'uplianda/popular-content', {
		edit: function ( props ) {
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;
			var title = attributes.title || '';
			var qty = attributes.qty || 10;
			var dateRange = attributes.dateRange || '';
			var blockProps = useBlockProps();

			return el(
				element.Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Popular Content Settings', 'uplink-analytics-dashboard-for-cabin' ), initialOpen: true },
						el( TextControl, {
							label: __( 'Heading', 'uplink-analytics-dashboard-for-cabin' ),
							value: title,
							onChange: function ( value ) {
								setAttributes( { title: value } );
							},
							help: __( 'Leave empty to use the default title from Cabin Analytics settings.', 'uplink-analytics-dashboard-for-cabin' ),
							placeholder: __( 'Top Content', 'uplink-analytics-dashboard-for-cabin' )
						} ),
						el( SelectControl, {
							label: __( 'Records to Show', 'uplink-analytics-dashboard-for-cabin' ),
							value: String( qty ),
							options: [
								{ label: '5', value: '5' },
								{ label: '10', value: '10' },
								{ label: '20', value: '20' },
								{ label: '50', value: '50' }
							],
							onChange: function ( value ) {
								setAttributes( { qty: Number.parseInt( value, 10 ) || 10 } );
							}
						} ),
						el( SelectControl, {
							label: __( 'Date Range', 'uplink-analytics-dashboard-for-cabin' ),
							value: dateRange,
							options: [
								{ label: __( 'Use Default', 'uplink-analytics-dashboard-for-cabin' ), value: '' },
								{ label: __( 'Last 1 Day', 'uplink-analytics-dashboard-for-cabin' ), value: '1' },
								{ label: __( 'Last 7 Days', 'uplink-analytics-dashboard-for-cabin' ), value: '7' },
								{ label: __( 'Last 14 Days', 'uplink-analytics-dashboard-for-cabin' ), value: '14' },
								{ label: __( 'Last 30 Days', 'uplink-analytics-dashboard-for-cabin' ), value: '30' },
								{ label: __( 'Last 90 Days', 'uplink-analytics-dashboard-for-cabin' ), value: '90' }
							],
							onChange: function ( value ) {
								setAttributes( { dateRange: value } );
							},
							help: __( 'Leave as Use Default to inherit the admin setting.', 'uplink-analytics-dashboard-for-cabin' )
						} )
					)
				),
				el(
					'div',
					blockProps,
					el( ServerSideRender, {
						block: 'uplianda/popular-content',
						attributes: attributes,
						LoadingResponsePlaceholder: function () { return el( 'div', { className: 'uplianda-block-preview-status' }, el( Spinner ), __( 'Loading popular content…', 'uplink-analytics-dashboard-for-cabin' ) ); },
						ErrorResponsePlaceholder: function () { return el( Notice, { status: 'error', isDismissible: false }, __( 'The Popular Content preview could not be loaded.', 'uplink-analytics-dashboard-for-cabin' ) ); },
						EmptyResponsePlaceholder: function () { return el( Notice, { status: 'warning', isDismissible: false }, __( 'Cabin did not return popular content for this period.', 'uplink-analytics-dashboard-for-cabin' ) ); }
					} )
				)
			);
		},
		save: function () {
			return null;
		}
	} );
} )( window.wp.blocks, window.wp.element, window.wp.i18n, window.wp.blockEditor, window.wp.components, window.wp.serverSideRender );
