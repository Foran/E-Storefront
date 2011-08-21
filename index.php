<?php
/************************************************************
 * E-Storefront by Ben M. Ward is licensed under a
 * Creative Commons Attribution 3.0 Unported License.
 * http://creativecommons.org/licenses/by/3.0/
 * Based on a work at https://github.com/Foran/E-Storefront.
 ***********************************************************/

// If the prerequesits checker is present, transfer control
if(file_exists('Includes/Utility/PreCheck.php')) require_once('Includes/Utility/PreCheck.php');

// Initialize the Session
session_start();

// Load Basic utility classes
require_once('Classes/Utility/Config.php');
require_once('Classes/Utility/Database.php');

//Include the template engine
require_once('Includes/Utility/Templates.php');

//Load the Permalink
$global_Permalink = isset($_SERVER['REDIRECT_URL']) ? $_SERVER['REDIRECT_URL'] : @$_SERVER['REQUEST_URI'];
global $global_Permalink;

//Load the visitors IP Address
$global_IP_Address = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : (isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) ? $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'] : @$_SERVER['REMOTE_ADDR']);
global $global_IP_Address;

// If the installer is present, transfer control
if(file_exists('Includes/Utility/Installer.php')) require_once('Includes/Utility/Installer.php');

//Load the static configuration
$global_Config = new Utility_Config('Main');
global $global_Config;

//Load and connect to the database
$global_Database = new Utility_Database();
global $global_Database;
$global_Database->Connect(@$global_Config->get_Database_Host(), @$global_Config->get_Database_Username(), @$global_Config->get_Database_Password(), @$global_Config->get_Database_Database());
$global_Database->set_Prefix($global_Config->get_Database_Prefix());

?>