import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, SelectControl, ToggleControl, Placeholder } from '@wordpress/components';
import { chartBar } from '@wordpress/icons';
import { useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import './editor.scss';

export default function Edit( { attributes, setAttributes } ) {
	const { domain, chartType, dateRange, allowSwitching, dashboardUrl } = attributes;

	useEffect( () => {
		if ( ! domain ) {
			apiFetch( { path: '/wp/v2/settings' } ).then( ( settings ) => {
				const options = settings.cabin_analytics_dashboard_options;
				if ( options && options.domain ) {
					setAttributes( { domain: options.domain } );
				}
			} );
		}
	}, [] );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Analytics Settings', 'cabin-analytics-dashboard' ) }>
					<TextControl
						label={ __( 'Domain', 'cabin-analytics-dashboard' ) }
						value={ domain }
						onChange={ ( value ) => setAttributes( { domain: value } ) }
						help={ __( 'Leave empty to use default from settings', 'cabin-analytics-dashboard' ) }
						placeholder="example.com"
					/>
					<SelectControl
						label={ __( 'Chart Type', 'cabin-analytics-dashboard' ) }
						value={ chartType }
						options={ [
							{ label: __( 'Use Default', 'cabin-analytics-dashboard' ), value: '' },
							{ label: __( 'Stacked Bar Chart', 'cabin-analytics-dashboard' ), value: 'bar' },
							{ label: __( 'Line Chart', 'cabin-analytics-dashboard' ), value: 'line' },
						] }
						onChange={ ( value ) => setAttributes( { chartType: value } ) }
					/>
					<SelectControl
						label={ __( 'Date Range', 'cabin-analytics-dashboard' ) }
						value={ dateRange }
						options={ [
							{ label: __( 'Use Default', 'cabin-analytics-dashboard' ), value: '' },
							{ label: __( 'Last 7 Days', 'cabin-analytics-dashboard' ), value: '7' },
							{ label: __( 'Last 14 Days', 'cabin-analytics-dashboard' ), value: '14' },
							{ label: __( 'Last 30 Days', 'cabin-analytics-dashboard' ), value: '30' },
							{ label: __( 'Last 90 Days', 'cabin-analytics-dashboard' ), value: '90' },
						] }
						onChange={ ( value ) => setAttributes( { dateRange: value } ) }
					/>
					<ToggleControl
						label={ __( 'Allow User Switching', 'cabin-analytics-dashboard' ) }
						checked={ allowSwitching }
						onChange={ ( value ) => setAttributes( { allowSwitching: value } ) }
						help={ __( 'Allow visitors to change chart type and date range', 'cabin-analytics-dashboard' ) }
					/>
					<TextControl
						label={ __( 'Dashboard URL', 'cabin-analytics-dashboard' ) }
						value={ dashboardUrl }
						onChange={ ( value ) => setAttributes( { dashboardUrl: value } ) }
						help={ __( 'Custom link to Cabin dashboard. Leave empty for default: https://withcabin.com/dashboard/{domain}', 'cabin-analytics-dashboard' ) }
						placeholder="https://withcabin.com/dashboard/example.com"
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...useBlockProps() }>
				<Placeholder
					icon={ chartBar }
					label={ __( 'Cabin Analytics Dashboard', 'cabin-analytics-dashboard' ) }
					instructions={ __( 'This block will display your Cabin Analytics data. Configure settings in the sidebar.', 'cabin-analytics-dashboard' ) }
				>
					<div style={ { padding: '20px', textAlign: 'left', width: '100%' } }>
						<p><strong>{ __( 'Current Settings:', 'cabin-analytics-dashboard' ) }</strong></p>
						<ul style={ { margin: '10px 0', paddingLeft: '20px' } }>
							<li>{ __( 'Domain:', 'cabin-analytics-dashboard' ) } { domain || __( 'Default', 'cabin-analytics-dashboard' ) }</li>
							<li>{ __( 'Chart Type:', 'cabin-analytics-dashboard' ) } { chartType === 'bar' ? __( 'Bar', 'cabin-analytics-dashboard' ) : chartType === 'line' ? __( 'Line', 'cabin-analytics-dashboard' ) : __( 'Default', 'cabin-analytics-dashboard' ) }</li>
							<li>{ __( 'Date Range:', 'cabin-analytics-dashboard' ) } { dateRange || __( 'Default', 'cabin-analytics-dashboard' ) }</li>
							<li>{ __( 'User Switching:', 'cabin-analytics-dashboard' ) } { allowSwitching ? __( 'Enabled', 'cabin-analytics-dashboard' ) : __( 'Disabled', 'cabin-analytics-dashboard' ) }</li>
							<li>{ __( 'Dashboard URL:', 'cabin-analytics-dashboard' ) } { dashboardUrl || __( 'Default', 'cabin-analytics-dashboard' ) }</li>
						</ul>
					</div>
				</Placeholder>
			</div>
		</>
	);
}