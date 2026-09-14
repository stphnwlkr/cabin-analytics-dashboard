<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'dependencies' => array( 'wp-blocks', 'wp-element', 'wp-i18n', 'wp-block-editor', 'wp-components', 'wp-server-side-render' ),
	'version'      => filemtime( __DIR__ . '/insight-blocks.js' ),
);
