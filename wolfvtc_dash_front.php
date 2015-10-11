<?php
defined('ABSPATH') or die('No direct access! Bad user!');

add_action('admin_menu', 'wolfvtc_dash_menu');

function wolfvtc_dash_menu() {
	add_options_page('WolfVTC Dash', 'WolfVTC Dash', 'manage_options', 'wolfvtc', 'wolfvtc_dash_front');
}

function wolfvtc_dash_front() {
	echo '<div class="wrap">
	<h1>WolfVTC Member Dashboard</h1>';
	if (get_option("wolfvtc_setup") == FALSE) {
		echo '<div style="width:60%;margin-left:20%;margin-right:20%;background-color:#2980b9;text-align:center"><h1 style="color:#ffffff">Your admin has to do something!</h1></div>
		<p>Please tell the site administrator to access the WolfVTC Admin Panel.</p>';

		delete_option("wolfvtc_setup");
		add_option("wolfvtc_setup", TRUE);
	} elseif (!isset($_GET['do'])) {
		echo '<a href="?page=wolfvtc&do=about"><input type="button" class="button-primary" style="width:100%" value="My profile"></a>
		<p>Manage and change the VTC settings, such as turning on and off the division system and more.</p>';

		echo '<a href="?page=wolfvtc&do=newjob"><input type="button" class="button-primary" style="width:100%" value="Submit Job"></a>
		<p>Review, accept or deny job reports from members.</p>';

		echo '<a href="?page=wolfvtc&do=myjobs"><input type="button" class="button-primary" style="width:100%" value="My jobs"></a>
		<p>Review, accept or deny job reports from members.</p>';

		if (get_option('wolftvc_divisionsenabled') != FALSE) {
			echo '<a href="?page=wolfvtc&do=mydiv"><input type="button" class="button-primary" style="width:100%" value="My division"></a>
			<p>Create, delete or change division names and descriptions.</p>';

			echo '<a href="?page=wolfvtc&do=divs"><input type="button" class="button-primary" style="width:100%" value="Join division"></a>
			<p>Create, delete or change division names and descriptions.</p>';
		}
	} elseif ($_GET['do'] == "options") {

	}
	echo '</div>';
}