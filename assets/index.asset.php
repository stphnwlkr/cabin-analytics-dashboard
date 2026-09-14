<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'dependencies' => array(
		'uplianda-chart-renderer',
		'wp-block-editor',
		'wp-blocks',
		'wp-components',
		'wp-element',
		'wp-i18n',
	),
	'version'      => filemtime( __DIR__ . '/index.js' ),
);
