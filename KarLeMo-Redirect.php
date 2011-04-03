<?php
/*
Plugin Name: KarLeMo Redirect
Plugin URI: http://karlemo.se/redirect
Description: A plugin to separate users with a token from others
Author: <a href="http://www.karlemo.se">Johan Karlemo</a>
Version: 0.1
Author URI: http://www.karlemo.se
*/

define("KARLEMOREDIRECT_PLUGIN_PATH", ABSPATH."wp-content/plugins/KarLeMo-Redirect/");
define("KARLEMOREDIRECT_PLUGIN_URL", WP_PLUGIN_URL."/KarLeMo-Redirect/");
define("KARLEMOREDIRECT_VERSION", "0.1");
$isCookieSet=false;
require_once(KARLEMOREDIRECT_PLUGIN_PATH."classes/main.php");

if (class_exists("KarlemoRedir")){
	$dl_pluginKarlemoRedir = new KarlemoRedir();
}

//Actions and Filters
if (isset($dl_pluginKarlemoRedir)){
	//Actions
	if (isset($_GET['activate']) && $_GET['activate']== true)
	{
		array(&$dl_pluginKarlemoRedir,'activate');
	}
		
	$role = get_role('administrator');
	$role->add_cap('manageRedir');
	
	if (isset($_REQUEST['action'])){
		$action=$_REQUEST['action'];
	}else{
		$action="";		
	}
	
	register_activation_hook( __FILE__,array(&$dl_pluginKarlemoRedir,'activate'));
	register_deactivation_hook( __FILE__,array(&$dl_pluginKarlemoRedir,'deactivate'));
	add_action('wp_head',array(&$dl_pluginKarlemoRedir,'addHeaderCode'), 1);
	
	add_action ('init',array(&$dl_pluginKarlemoRedir,'init'));
	add_action('admin_menu',array(&$dl_pluginKarlemoRedir,'KarlemoRedir_ap'));	
//Filters
}
?>
