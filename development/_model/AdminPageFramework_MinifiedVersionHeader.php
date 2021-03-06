<?php
/**
 * Admin Page Framework
 * 
 * http://en.michaeluno.jp/admin-page-framework/
 * Copyright (c) 2013-2014 Michael Uno; Licensed MIT
 * 
 */

// If accessed from a browser, exit.
if ( php_sapi_name() !== 'cli' ) {
	exit;
}
 
// For the minifier script.
if ( file_exists( dirname( dirname( __FILE__ ) ) . '/admin-page-framework.php' ) ) {
	include_once( dirname( dirname( __FILE__ ) ) . '/admin-page-framework.php' );
}

if ( ! class_exists( 'AdminPageFramework_MinifiedVersionHeader' ) ) :
/**
 * Provides header information of the framework for the minifed version.
 * 
 * The minifier script will include this file ( but it does not include WordPress ) to use the reflection class to generate the header comment section.
 */
final class AdminPageFramework_MinifiedVersionHeader extends AdminPageFramework_Registry_Base {
	
	const Name			= 'Admin Page Framework - Minified Version';
	const Description	= 'Generated by PHP Class Minifier';	
	
}
endif;