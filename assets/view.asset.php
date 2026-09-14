<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'dependencies' => array( 'uplianda-chart-renderer' ),
	'version'      => filemtime( __DIR__ . '/view.js' ),
);
