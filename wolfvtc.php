<?php
/**
 * @package WolfVTC
 */
/*
Plugin Name: WolfVTC
Plugin URI: http://humanewolf.com/projects/wolfvtc
Description: VTC system for ETS2MP, plugin for wordpress.
Version: 0.0.1 (Alpha)
Author: HumaneWolf
Author URI: http://humanewolf.com/
*/

defined('ABSPATH') or die('No direct access! Bad user!');

//INSTALLER
require("wolfvtc_installer.php"); // User panel widget

register_activation_hook(__FILE__, 'wolfvtc_install');
register_deactivation_hook(__FILE__, 'wolfvtc_remove');

//FUNCTIONS
require("wolfvtc_functions.php");

//CLASSES


//WIDGETS
require("wolfvtc_user_widget.php"); // User panel widget
require("wolfvtc_stats_widget.php"); // Stats widget

//MEMBER PAGES
require("wolfvtc_dash_front.php"); // Front page

//ADMIN DASH PAGES
require("wolfvtc_admin_front.php"); // Admin dash WolfVTC front page
