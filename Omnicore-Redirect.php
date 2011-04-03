<?php
/*
Plugin Name: Omnicore Redirect
Plugin URI: http://www.omnicore.se
Description: A plugin to separate users with a token from others
Author: <a href="http://www.omnicore.se">Johan Omnicore</a>
Version: 0.1
Author URI: http://www.omnicore.se
*/

define("OMNICOREREDIRECT_PLUGIN_PATH", ABSPATH."wp-content/plugins/Omnicore-Redirect/");
define("OMNICOREREDIRECT_PLUGIN_URL", WP_PLUGIN_URL."/Omnicore-Redirect/");
define("OMNICOREREDIRECT_VERSION", "0.1");
$isCookieSet=false;
require_once(OMNICOREREDIRECT_PLUGIN_PATH."classes/main.php");

if (class_exists("OmnicoreRedir")){
	$dl_pluginOmnicoreRedir = new OmnicoreRedir();
}

//Actions and Filters
if (isset($dl_pluginOmnicoreRedir)){
	//Actions
	if (isset($_GET['activate']) && $_GET['activate']== true)
	{
		array(&$dl_pluginOmnicoreRedir,'activate');
	}
		
	$role = get_role('administrator');
	$role->add_cap('manageRedir');
	
	if (isset($_REQUEST['action'])){
		$action=$_REQUEST['action'];
	}else{
		$action="";		
	}
	
	register_activation_hook( __FILE__,array(&$dl_pluginOmnicoreRedir,'activate'));
	register_deactivation_hook( __FILE__,array(&$dl_pluginOmnicoreRedir,'deactivate'));
	add_action('wp_head',array(&$dl_pluginOmnicoreRedir,'addHeaderCode'), 1);
	
	add_action ('init',array(&$dl_pluginOmnicoreRedir,'init'));
	add_action('admin_menu',array(&$dl_pluginOmnicoreRedir,'OmnicoreRedir_ap'));	
//Filters
}
?>
