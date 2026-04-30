( function ( blocks, element, i18n, blockEditor, components ) {
	'use strict';

	var el = element.createElement;
	var __ = i18n.__;
	var useBlockProps = blockEditor.useBlockProps;
	var InspectorControls = blockEditor.InspectorControls;
	var PanelBody = components.PanelBody;
	var TextControl = components.TextControl;
	var SelectControl = components.SelectControl;
	var RangeControl = components.RangeControl;
	var Placeholder = components.Placeholder;

	blocks.registerBlockType( 'cabin/popular-content', {
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
						{ title: __( 'Popular Content Settings', 'cabin-analytics-dashboard' ), initialOpen: true },
						el( TextControl, {
							label: __( 'Title', 'cabin-analytics-dashboard' ),
							value: title,
							onChange: function ( value ) {
								setAttributes( { title: value } );
							},
							help: __( 'Leave empty to use the default title from Cabin Analytics settings.', 'cabin-analytics-dashboard' ),
							placeholder: __( 'Top Content', 'cabin-analytics-dashboard' )
						} ),
						el( RangeControl, {
							label: __( 'Quantity', 'cabin-analytics-dashboard' ),
							value: qty,
							onChange: function ( value ) {
								setAttributes( { qty: value || 10 } );
							},
							min: 1,
							max: 50
						} ),
						el( SelectControl, {
							label: __( 'Date Range', 'cabin-analytics-dashboard' ),
							value: dateRange,
							options: [
								{ label: __( 'Use Default', 'cabin-analytics-dashboard' ), value: '' },
								{ label: __( 'Last 1 Day', 'cabin-analytics-dashboard' ), value: '1' },
								{ label: __( 'Last 7 Days', 'cabin-analytics-dashboard' ), value: '7' },
								{ label: __( 'Last 14 Days', 'cabin-analytics-dashboard' ), value: '14' },
								{ label: __( 'Last 30 Days', 'cabin-analytics-dashboard' ), value: '30' },
								{ label: __( 'Last 90 Days', 'cabin-analytics-dashboard' ), value: '90' }
							],
							onChange: function ( value ) {
								setAttributes( { dateRange: value } );
							},
							help: __( 'Leave as Use Default to inherit the admin setting.', 'cabin-analytics-dashboard' )
						} )
					)
				),
				el(
					'div',
					blockProps,
					el(
						Placeholder,
						{
							icon: 'editor-ol',
							label: __( 'Cabin Popular Content', 'cabin-analytics-dashboard' ),
							instructions: __( 'Displays a server-rendered list of popular local content based on Cabin Analytics page data.', 'cabin-analytics-dashboard' )
						},
						el(
							'div',
							{ style: { padding: '12px 0', width: '100%' } },
							el( 'p', null, el( 'strong', null, title || __( 'Top Content', 'cabin-analytics-dashboard' ) ) ),
							el(
								'ul',
								{ style: { margin: '10px 0', paddingLeft: '20px' } },
								el( 'li', null, __( 'Quantity:', 'cabin-analytics-dashboard' ) + ' ' + qty ),
								el( 'li', null, __( 'Date Range:', 'cabin-analytics-dashboard' ) + ' ' + ( dateRange ? __( 'Last ', 'cabin-analytics-dashboard' ) + dateRange + __( ' Days', 'cabin-analytics-dashboard' ) : __( 'Default', 'cabin-analytics-dashboard' ) ) )
							)
						)
					)
				)
			);
		},
		save: function () {
			return null;
		}
	} );
} )( window.wp.blocks, window.wp.element, window.wp.i18n, window.wp.blockEditor, window.wp.components );
