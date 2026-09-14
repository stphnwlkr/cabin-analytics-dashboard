( function ( wp, settings ) {
	'use strict';

	if ( ! wp || ! wp.element || ! wp.plugins || ! settings ) {
		return;
	}

	var PluginMoreMenuItem = wp.editor && wp.editor.PluginMoreMenuItem
		? wp.editor.PluginMoreMenuItem
		: wp.editPost && wp.editPost.PluginMoreMenuItem;
	var PluginPostStatusInfo = wp.editor && wp.editor.PluginPostStatusInfo
		? wp.editor.PluginPostStatusInfo
		: wp.editPost && wp.editPost.PluginPostStatusInfo;

	if ( ! PluginMoreMenuItem ) {
		return;
	}

	var chartAnalysisIcon = settings.iconSvg
		? wp.element.createElement( 'span', {
			className: 'uplianda-editor-icon',
			dangerouslySetInnerHTML: { __html: settings.iconSvg }
		} )
		: 'chart-bar';

	wp.plugins.registerPlugin( 'uplianda-dashboard-editor-navigation', {
		render: function () {
			return wp.element.createElement(
				wp.element.Fragment,
				null,
				wp.element.createElement(
					PluginMoreMenuItem,
					{
						icon: chartAnalysisIcon,
						onClick: function () {
							window.location.assign( settings.dashboardUrl );
						}
					},
					settings.backLabel
				),
				PluginPostStatusInfo && wp.components
					? wp.element.createElement(
						PluginPostStatusInfo,
						null,
						wp.element.createElement(
							wp.components.Button,
							{
								href: settings.dashboardUrl,
								icon: chartAnalysisIcon,
								variant: 'secondary'
							},
							settings.viewLabel
						)
					)
					: null
			);
		}
	} );
}( window.wp, window.UpliandaDashboardEditor ) );
