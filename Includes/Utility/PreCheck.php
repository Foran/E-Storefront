<?php
/************************************************************
 * E-Storefront by Ben M. Ward is licensed under a
 * Creative Commons Attribution 3.0 Unported License.
 * http://creativecommons.org/licenses/by/3.0/
 * Based on a work at https://github.com/Foran/E-Storefront.
 ***********************************************************/

// Check for basic checking support
if(!function_exists('version_compare') || !function_exists('file_get_contents') || !function_exists('str_ireplace')) die('PHP version too old, need at least 5.3.0');

// Check for minimum version
if (version_compare(PHP_VERSION, '5.3.0') < 0) {
	$content = '';
	echo str_ireplace('<%content%>', $content, file_get_contents('Templates/Default/precheck.html'));
	exit;
}

//If version check passed, mark a variable to indicate success
$global_VersionChecked = true;
global $global_VersionChecked;

//Try to delete PreCheck.php to optimise page loads
@unlink(__FILE__);
?>