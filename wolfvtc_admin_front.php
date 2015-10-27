<?php
defined('ABSPATH') or die('No direct access! Bad user!');

add_action('admin_menu', 'wolfvtc_admin_menu');

function wolfvtc_admin_menu() {
	if (wolfvtc_hasperm(get_current_user_id(), "all")) {
		add_menu_page('WolfVTC Admin', 'WolfVTC Admin', 'read', 'wolfvtcadmin', 'wolfvtc_admin_front', "", 1);
	}
}

function wolfvtc_admin_front() {
	global $wpdb;

	echo '<div class="wrap">
	<h1>WolfVTC Admin Panel</h1>';
	if (get_option("wolfvtc_setup") == FALSE) {
		echo '<div style="width:60%;margin-left:20%;margin-right:20%;background-color:#2980b9;text-align:center"><h1 style="color:#ffffff">WolfVTC is now installed.</h1></div>
		<p>We recommend that you add the User Panel widget added by the WolfVTC plugin to the sidebar and turn on the WordPress option allowing everyone to register.</p>
		<p>Changing the registration setting can be done under General Settings, and adding the widget can be done under Appearances->Widgets, both here in the WP Dashboard.</p>
		<p><a href="?page=wolfvtcadmin"><input type="button" class="button-primary" style="width:100%" value="Go to control panel"></a></p>';

		if (get_option("wolfvtc_dbversion") < 1) {
			include 'wolfvtc_cities.php';
			foreach ($cities as $c) {
				$wpdb->insert( 
					$wpdb->prefix . 'wolfvtc_cities', 
					array( 
						'cityname' => $c,
					) 
				);
			}
			delete_option("wolfvtc_dbversion");
    		add_option("wolfvtc_dbversion", 1);

    		$wpdb->insert( 
					$wpdb->prefix . 'wolfvtc_cargo', 
					array( 
						'cargoname' => 'Other',
					) 
				);
		}

		$wpdb->insert( 
			$wpdb->prefix . 'wolfvtc_users', 
			array( 
				'userid' => get_current_user_id(), 
				'division' => 0, 
				'divisionmember' => 0, 
				'divisionadmin' => 0, 
				'fullmember' => 1, 
				'adminjobs' => 1, 
				'adminusers' => 1, 
				'admindiv' => 1, 
				'admincc' => 1, 
				'superadmin' => 1,
			) 
		);

		delete_option("wolfvtc_setup");
		add_option("wolfvtc_setup", TRUE);
	} elseif (!isset($_GET['do']) && wolfvtc_hasperm(get_current_user_id(), "all")) { //FRONT PAGE
		echo '<p>Welcome to the front page of the WolfVTC WordPress Admin Panel. Here you can manage divisions, settings, jobs and more!</p>';

		if (wolfvtc_hasperm(get_current_user_id(), "super")) {
			echo '<a href="?page=wolfvtcadmin&do=options"><input type="button" class="button-primary" style="width:100%" value="Manage Options"></a>
			<p>Manage and change the VTC settings, such as turning on and off the division system and more.</p>';
		}

		if (wolfvtc_hasperm(get_current_user_id(), "jobs")) {
			echo '<a href="?page=wolfvtcadmin&do=jobs"><input type="button" class="button-primary" style="width:100%" value="Manage Jobs"></a>
			<p>Review, accept or deny job reports from members.</p>';
		}

		if (get_option("wolfvtc_divisionsenabled") != 0 && wolfvtc_hasperm(get_current_user_id(), "div")) {
			echo '<a href="?page=wolfvtcadmin&do=div"><input type="button" class="button-primary" style="width:100%" value="Manage Divisions"></a>
			<p>Create, delete or change division names and descriptions.</p>';
		}

		if (wolfvtc_hasperm(get_current_user_id(), "users")) {
			echo '<a href="?page=wolfvtcadmin&do=users"><input type="button" class="button-primary" style="width:100%" value="Manage Users"></a>
			<p>Change user settings and permissions related to the VTC system.</p>';
		}

		if (wolfvtc_hasperm(get_current_user_id(), "cc")) {
			echo '<a href="?page=wolfvtcadmin&do=cities"><input type="button" class="button-primary" style="width:100%" value="Add city"></a>
			<p>Add new cities to the system. Useful if a new DLC is released.</p>

			<a href="?page=wolfvtcadmin&do=cargo"><input type="button" class="button-primary" style="width:100%" value="Add cargo types"></a>
			<p>Add new cargo types to the system. Useful if a cargo type is missing, or if a new DLC is released.</p>';
		}


	} elseif ($_GET['do'] == "options" && wolfvtc_hasperm(get_current_user_id(), "super")) { // OPTIONS
		echo '<h3>VTC Options</h3>
		<form action="?page=wolfvtcadmin&do=options" method="post">';

		if (isset($_POST['changed']) && $_POST['changed'] != "") {
			if (isset($_POST['divs']) && $_POST['divs'] != "") {
    			update_option("wolfvtc_divisionsenabled", 1);
    			echo '<p><strong>Settings have been updated.</strong></p>';
			} else {
    			update_option("wolfvtc_divisionsenabled", 0);
    			echo '<p><strong>Settings have been updated.</strong></p>';
			}
		}

		echo '<h4>Enable Divisions</h4>
		<p>';
		if (get_option("wolfvtc_divisionsenabled") != 0) {
			echo '<input type="checkbox" name="divs" value="yes" checked>';
		} else {
			echo '<input type="checkbox" name="divs" value="yes">';
		}

		echo ' Enable the division system. This allows you to create divisions in your company, each with individual leaders. Drivers can submit jobs as division jobs, allowing division leaders to accept them. This is a useful way of making specialised groups within your company.</p>';

		echo '<input type="hidden" name="changed" value="yes">
		<input type="submit" value="Save"> <a href="?page=wolfvtcadmin"><input type="button" value="Back"></a>
		</form>';

	} elseif ($_GET['do'] == "jobs") {

	} elseif ($_GET['do'] == "div") {

	} elseif ($_GET['do'] == "users") {

	} elseif ($_GET['do'] == "cities") {

	} elseif ($_GET['do'] == "cargo") {

	} else {
		echo '<p>404 - This page does not exist, or you do not have permission to view it.</p>';
	}
	echo '</div>';
}