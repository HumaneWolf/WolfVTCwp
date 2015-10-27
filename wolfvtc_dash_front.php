<?php
defined('ABSPATH') or die('No direct access! Bad user!');

add_action('admin_menu', 'wolfvtc_dash_menu');

function wolfvtc_dash_menu() {
	add_menu_page('WolfVTC Dash', 'WolfVTC Dash', 'read', 'wolfvtc', 'wolfvtc_dash_front', "", 0);
}

function wolfvtc_dash_front() {
	global $wpdb;

	echo '<div class="wrap">
	<h1>WolfVTC Member Dashboard</h1>';
	if (get_option("wolfvtc_setup") == FALSE) {
		echo '<div style="width:60%;margin-left:20%;margin-right:20%;background-color:#2980b9;text-align:center"><h1 style="color:#ffffff">Your admin has to do something!</h1></div>
		<p>Please tell the site administrator to access the WolfVTC Admin Panel.</p>';

		delete_option("wolfvtc_setup");
		add_option("wolfvtc_setup", TRUE);
	} elseif (!isset($_GET['do'])) {
		if (wolfvtc_isuser(get_current_user_id())) {
			echo '<a href="?page=wolfvtc&do=about"><input type="button" class="button-primary" style="width:100%" value="My profile"></a>
			<p>View your profile information</p>';

			if (wolfvtc_hasperm(get_current_user_id(), "fullmember")) {
				echo '<a href="?page=wolfvtc&do=newjob"><input type="button" class="button-primary" style="width:100%" value="Submit Job"></a>
				<p>Submit a job report to the VTC.</p>

				<a href="?page=wolfvtc&do=myjobs"><input type="button" class="button-primary" style="width:100%" value="My jobs"></a>
				<p>View all your job reports.</p>';

				if (get_option("wolfvtc_divisionsenabled") != 0) {
					echo '<a href="?page=wolfvtc&do=mydiv"><input type="button" class="button-primary" style="width:100%" value="My division"></a>
					<p>My division dashboard.</p>

					<a href="?page=wolfvtc&do=divs"><input type="button" class="button-primary" style="width:100%" value="Join division"></a>
					<p>Join a division.</p>';
				}
			} else {
				echo '<p><strong>Your account is not a full member of the VTC, so you may not submit jobs.</strong></p>';
			}
		} else {
			$wpdb->insert( 
				$wpdb->prefix . 'wolfvtc_users', 
				array( 
					'userid' => get_current_user_id(), 
					'division' => 0, 
					'divisionmember' => 0, 
					'divisionadmin' => 0, 
					'fullmember' => 1, 
					'adminjobs' => 0, 
					'adminusers' => 0, 
					'admindiv' => 0, 
					'admincc' => 0, 
					'superadmin' => 0,
				) 
			);
			echo '<p><strong>Your account has been registered with the VTC system.</strong></p>
			<a href="?page=wolfvtc"><input type="button" class="button-primary" style="width:100%" value="Browse my member dashboard"></a>';
		}
		echo '<a href="' . home_url() . '"><input type="button" class="button-primary" style="width:100%;margin-top:20px" value="Front page"></a>';
	} elseif ($_GET['do'] == "about") {
		$user = wp_get_current_user();
		echo '<h3>User profile</h3>
		<p><strong>User ID:</strong> ' . $user->ID . '</p>
		<p><strong>Displayed name:</strong> ' . $user->display_name . '</p>
		<p><strong>Km driven:</strong> ';
		if ($km = wolfvtc_userkmdriven(get_current_user_id())) {
			echo $km;
		} else {
			echo 0;
		}
		echo ' Km</p>';
		//TODO: Division name.
		echo '<p><strong>Permissions</strong></p>
		<ul style="list-style-type:disc;margin-left:20px">';
		if (wolfvtc_hasperm(get_current_user_id(), "fullmember")) {
			echo '<li>Can submit jobs and join divisions</li>';
		}
		if (wolfvtc_hasperm(get_current_user_id(), "jobs")) {
			echo '<li>Can approve submitted jobs</li>';
		}
		if (wolfvtc_hasperm(get_current_user_id(), "users")) {
			echo '<li>Can manage other users</li>';
		}
		if (wolfvtc_hasperm(get_current_user_id(), "div") && get_option("wolfvtc_divisionsenabled") != 0) {
			echo '<li>Can manage divisions</li>';
		}
		if (wolfvtc_hasperm(get_current_user_id(), "cc")) {
			echo '<li>Can add cities and cargo</li>';
		}
		if (wolfvtc_hasperm(get_current_user_id(), "super")) {
			echo '<li>Can edit VTC settings</li>';
		}
		if (wolfvtc_hasperm(get_current_user_id(), "all") == FALSE && wolfvtc_hasperm(get_current_user_id(), "fullmember") == FALSE) {
			echo '<li>None</li>';
		}
		echo '</ul>';

	} elseif ($_GET['do'] == "newjob" && wolfvtc_hasperm(get_current_user_id(), "fullmember")) {

	} elseif ($_GET['do'] == "myjobs" && wolfvtc_hasperm(get_current_user_id(), "fullmember")) {
		echo '<h3>My Jobs</h3>';
		$q = 'SELECT * FROM ' . $wpdb->prefix . 'wolfvtc_jobs WHERE userid=' . intval(get_current_user_id()) . ' ORDER BY addedtime DESC';
		$q = $wpdb->get_results($q);

		echo '<table style="width:100%">
		<tr>
		<th>Time</th>
		<th>From</th>
		<th>To</th>
		<th>Cargo</th>
		<th>Status</th>
		<th>Division</th>
		<th style="width:50px">Options</th>
		</tr>';

		foreach ($q as $j) {
			if ($j->approved == 1) {
				$approved = '<span style="color:darkgreen">Approved</span>';
			} else {
				$approved = '<span style="color:darkred">Not approved</span>';
			}
			echo '<tr>
			<td style="border: 1px solid black">' . $j->addedtime . '</td>
			<td style="border: 1px solid black">' . wolfvtc_cityname($j->fromcity) . '</td>
			<td style="border: 1px solid black">' . wolfvtc_cityname($j->tocity) . '</td>
			<td style="border: 1px solid black">' . wolfvtc_cargoname($j->cargo) . '</td>
			<td style="border: 1px solid black">' . $approved . '</td>
			<td style="border: 1px solid black">' . wolfvtc_divname($j->divid) . '</td>
			<td style="border: 1px solid black;width:50px"><button onclick="document.getElementById(\'job' . $j->jobid . '\').style.display=\'table-row\'" style="width:100%">Display info</button></td>
			</tr>';

			$expenses = $j->fuelcosts + $j->repaircosts + $j->travelcosts;
			$profit = $j->earnings - $expenses;

			if ($profit >= 0) {
				$profits = '<span style="color:darkgreen">' . $profit . ' €</span>';
			} else {
				$profits = '<span style="color:darkred">' . $profit . ' €</span>';
			}

			if (intval($j->approved) == 0) {
				$approvedby = "";
				$approvedtime = "";
			} else {
				$appuser = get_user_by("id", $j->approvedby);

				$approvedby = $appuser->display_name;
				$approvedtime = $j->approvedtime;
			}

			echo '<tr id="job' . $j->jobid . '" style="display:none">
			<td><strong>Earnings:</strong> ' . $j->earnings . ' €</td>
			<td><strong>Expenses:</strong> ' . $expenses . ' €</td>
			<td><strong>Profit:</strong> ' . $profits . '</td>
			<td><strong>Notes:</strong> ' . $j->notes . '</td>
			<td><strong>Approved by:</strong> ' . $approvedby . '</td>
			<td><strong>Approved at:</strong> ' . $approvedtime . '</td>
			<td style="width:50px"><button onclick="document.getElementById(\'job' . $j->jobid . '\').style.display=\'none\'" style="width:100%">Hide info</button></td>
			</tr>';
		}

		echo '</table>';

	} elseif ($_GET['mydiv'] && wolfvtc_hasperm(get_current_user_id(), "fullmember") && get_option("wolfvtc_divisionsenabled") != 0) {

	} elseif ($_GET['divs'] && wolfvtc_hasperm(get_current_user_id(), "fullmember") && get_option("wolfvtc_divisionsenabled") != 0) {
		
	} else {
		echo '<p>404 - Page not found.</p>';
	}
	echo '</div>';
}