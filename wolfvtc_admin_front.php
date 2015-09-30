<?php
defined('ABSPATH') or die('No direct access! Bad user!');

add_action('admin_menu', 'wolfvtc_admin_menu');

function wolfvtc_admin_menu() {
	add_options_page('WolfVTC Admin', 'WolfVTC Admin', 'manage_options', 'wolfvtcadmin', 'wolfvtc_admin_front');
}

function wolfvtc_admin_front() {
	echo '<div class="wrap">
	<h1>WolfVTC Admin Panel</h1>';
	if (get_option("wolfvtc_setup") == FALSE) {
		echo '<div style="width:60%;margin-left:20%;margin-right:20%;background-color:#2980b9;text-align:center"><h1 style="color:#ffffff">WolfVTC is now installed.</h1></div>
		<p>We recommend that you add the User Panel widget added by the WolfVTC plugin to the sidebar and turn on the WordPress option allowing everyone to register.</p>
		<p>Changing the registration setting can be done under General Settings, and adding the widget can be done under Appearances->Widgets, both here in the WP Dashboard.
		<p><a href="?page=wolfvtcadmin"><input type="button" class="button-primary" style="width:100%" value="Go to control panel"></a></p>';

		delete_option("wolfvtc_setup");
		add_option("wolfvtc_setup", TRUE);
	} elseif (!isset($_GET['do'])) {
		echo '<p>Welcome to the front page of the WolfVTC WordPress Admin Panel. Here you can manage divisions, settings, jobs and more!</p>';

		echo '<a href="?page=wolfvtcadmin&do=options"><input type="button" class="button-primary" style="width:100%" value="Manage Options"></a>
		<p>Manage and change the VTC settings, such as turning on and off the division system and more.</p>';

		echo '<a href="?page=wolfvtcadmin&do=jobs"><input type="button" class="button-primary" style="width:100%" value="Manage Jobs"></a>
		<p>Review, accept or deny job reports from members.</p>';

		if (get_option('wolftvc_divisionsenabled') != FALSE) {
			echo '<a href="?page=wolfvtcadmin&do=div"><input type="button" class="button-primary" style="width:100%" value="Manage Divisions"></a>
			<p>Create, delete or change division names and descriptions.</p>';
		}

		echo '<a href="?page=wolfvtcadmin&do=users"><input type="button" class="button-primary" style="width:100%" value="Manage Users"></a>
		<p>Change user settings and permissions related to the VTC system.</p>';

		echo '<a href="?page=wolfvtcadmin&do=cities"><input type="button" class="button-primary" style="width:100%" value="Add city"></a>
		<p>Add new cities to the system. Useful if a new DLC is released..</p>';

		echo '<a href="?page=wolfvtcadmin&do=cargo"><input type="button" class="button-primary" style="width:100%" value="Add cargo types"></a>
		<p>Add new cargo types to the system. Useful if a cargo type is missing, or if a new DLC is released.</p>';
	} elseif ($_GET['do'] == "options") {

	}
	echo '</div>';
}