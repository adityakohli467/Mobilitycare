<?php
error_log('ENTRY: admin/index.php hit at ' . date('H:i:s') . ' | METHOD: ' . $_SERVER['REQUEST_METHOD'] . ' | URI: ' . $_SERVER['REQUEST_URI'] . ' | Content-Length: ' . ($_SERVER['CONTENT_LENGTH'] ?? 'none'));
// Version
define('VERSION', '3.0.3.8');
ini_set('session.gc_maxlifetime', 864000); 


// Configuration
if (is_file('config.php')) {
	require_once('config.php');
}

// Install
if (!defined('DIR_APPLICATION')) {
	header('Location: ../install/index.php');
	exit;
}

// Startup
require_once(DIR_SYSTEM . 'startup.php');

start('admin');