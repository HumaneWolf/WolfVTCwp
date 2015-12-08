<?php
defined('ABSPATH') or die('No direct access! Bad user!');

add_action('admin_menu', 'wolfvtc_admin_menu');

function wolfvtc_admin_menu() {
	if (wolfvtc_hasperm(get_current_user_id(), "all") || get_option("wolfvtc_setup") == FALSE) {
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
		//	echo '<input type="checkbox" name="divs" value="yes" checked>';
		} else {
		//	echo '<input type="checkbox" name="divs" value="yes">';
		}
		//Divisions not done.
		echo 'Divisions can not be enabled as they are not finished.';

		echo ' Enable the division system. This allows you to create divisions in your company, each with individual leaders. Drivers can submit jobs as division jobs, allowing division leaders to accept them. This is a useful way of making specialised groups within your company.</p>';

		echo '<input type="hidden" name="changed" value="yes">
		<input type="submit" value="Save"> <a href="?page=wolfvtcadmin"><input type="button" value="Back"></a>
		</form>';

	} elseif ($_GET['do'] == "jobs" && wolfvtc_hasperm(get_current_user_id(), "jobs")) {
		echo '<h3>Manage Jobs</h3>';

		if (isset($_GET['approve']) && intval($_GET['approve']) != 0) {
			$update = $wpdb->update(
				$wpdb->prefix . 'wolfvtc_jobs',
				array(
					'approved' => 1,
					'approvedby' => intval(get_current_user_id()),
					'approvedtime' => current_time('mysql')
					),
				array(
					'jobid' => intval($_GET['approve']),
					),
				array('%d',
					'%d',
					'%s'),
				array('%d')
			);

			if ($update) {
				echo '<div style="background-color:darkgreen;color:#FFFFFF;padding:10px"><p><strong>Job Saved.</strong></p></div>';
			} else {
				echo '<div style="background-color:darkred;color:#FFFFFF;padding:10px"><p><strong>Something seems to have gone wrong.</strong></p></div>';
			}
		}

		$q = 'SELECT * FROM ' . $wpdb->prefix . 'wolfvtc_jobs ORDER BY approved ASC, addedtime DESC';
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
				$approved = '<a style="color:darkred;font-style:italic" href="?page=wolfvtcadmin&do=jobs&approve=' . $j->jobid . '">Approve job</a>';
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

	} elseif ($_GET['do'] == "div") {
		echo 'The division system is not currently done.';
	} elseif ($_GET['do'] == "users" && wolfvtc_hasperm(get_current_user_id(), "users")) {
		if (isset($_GET['id']) && $_GET['id'] != "") { //If a user is specified, show stuff about that user.
			echo '<h3>User profile</h3>';
			if (isset($_POST['updated']) && $_POST['updated'] != "") { //If the user clicked save.
				if (isset($_POST['mbr'])) { //fullmember
					$p['fullmember'] = 1;
				} else {
					$p['fullmember'] = 0;
				}
				$pt[0] = '%d';

				if (isset($_POST['job'])) { //adminjobs
					$p['adminjobs'] = 1;
				} else {
					$p['adminjobs'] = 0;
				}
				$pt[1] = '%d';

				if (isset($_POST['usr'])) { //adminusers
					$p['adminusers'] = 1;
				} else {
					$p['adminusers'] = 0;
				}
				$pt[2] = '%d';

				if (isset($_POST['div'])) { //admindiv
					$p['admindiv'] = 1;
				} else {
					$p['admindiv'] = 0;
				}
				$pt[3] = '%d';

				if (isset($_POST['cc'])) { //admincc
					$p['admincc'] = 1;
				} else {
					$p['admincc'] = 0;
				}
				$pt[4] = '%d';

				if (wolfvtc_hasperm(get_current_user_id(), "super")) {
					if (isset($_POST['vtc'])) { //superadmin
						$p['superadmin'] = 1;
					} else {
						$p['superadmin'] = 0;
					}
					$pt[5] = '%d';
				}

				$update = $wpdb->update(
					$wpdb->prefix . 'wolfvtc_users',
					$p,
					array(
						'userid' => intval($_GET['id']),
						),
					$pt,
					array('%d')
				);

				if ($update) {
					echo '<div style="background-color:darkgreen;color:#FFFFFF;padding:10px"><p><strong>User updated.</strong></p></div>';
				} else {
					echo '<div style="background-color:darkred;color:#FFFFFF;padding:10px"><p><strong>Something seems to have gone wrong.</strong></p></div>';
				}
			}

			$u = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . 'wolfvtc_users WHERE userid=' . intval($_GET['id']));

			$user = get_userdata(intval($_GET['id']));
			echo '<p><strong>User ID:</strong> ' . intval($_GET['id']) . '</p>
			<p><strong>Displayed name:</strong> ' . $user->display_name . '</p>
			<p><strong>Km driven:</strong> ';
			if ($km = wolfvtc_userkmdriven(intval($_GET['id']))) {
				echo $km;
			} else {
				echo 0;
			}
			echo ' Km</p>';

			if (get_option("wolfvtc_divisionsenabled") != 0) {
				if (wolfvtc_hasperm(intval($_GET['id']), "divmember")) {
					$rank = "Approved";
					if (wolfvtc_hasperm(intval($_GET['id']), "divadmin")) {
						$rank .= ", admin";
					}
				} else {
					$rank = "Not approved";
				}
				$divid = wolfvtc_userdiv(intval($_GET['id']));
				echo '<p><strong>Division:</strong> ' . wolfvtc_divname($divid) . ' (' . $rank . ')</p>';
			}

			echo '<p><strong>Permissions</strong></p>
			<form method="post" action="?page=wolfvtcadmin&do=users&id=' . intval($_GET['id']) . '">
			<ul style="margin-left:20px">';
			if (wolfvtc_hasperm(intval($_GET['id']), "fullmember")) {//check each permission
				$perm = 'checked';
			} else {
				$perm = '';
			}
			echo '<li><input type="checkbox" name="mbr" value="yes" ' . $perm . '> Can submit jobs and join divisions</li>';

			if (wolfvtc_hasperm(intval($_GET['id']), "jobs")) {
				$perm = 'checked';
			} else {
				$perm = '';
			}
			echo '<li><input type="checkbox" name="job" value="yes" ' . $perm . '> Can approve submitted jobs</li>';

			if (wolfvtc_hasperm(intval($_GET['id']), "users")) {
				$perm = 'checked';
			} else {
				$perm = '';
			}
			echo '<li><input type="checkbox" name="usr" value="yes" ' . $perm . '> Can manage other users</li>';

			if (wolfvtc_hasperm(intval($_GET['id']), "div") && get_option("wolfvtc_divisionsenabled") != 0) {
				$perm = 'checked';
			} else {
				$perm = '';
			}
			echo '<li><input type="checkbox" name="div" value="yes" ' . $perm . '> Can manage divisions</li>';

			if (wolfvtc_hasperm(intval($_GET['id']), "cc")) {
				$perm = 'checked';
			} else {
				$perm = '';
			}
			echo '<li><input type="checkbox" name="cc" value="yes" ' . $perm . '> Can add cities and cargo</li>';

			if (wolfvtc_hasperm(intval($_GET['id']), "super")) {
				$perm = 'checked';
			} else {
				$perm = '';
			}
			if (wolfvtc_hasperm(get_current_user_id(), "super")) {
				$super = '<input type="checkbox" name="vtc" value="yes" ' . $perm . '>';
			} else {
				$super = '<input type="checkbox" name="vtc" value="yes" disabled ' . $perm . '>';
			}
			echo '<li>' . $super . ' Can edit VTC settings</li>';

			echo '</ul>
			<input type="hidden" name="updated" value="yes">
			<input type="submit" value="Update">
			</form>';

		} else {
			$user = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . 'wolfvtc_users');
			echo '<table style="width:100%;text-align:center;">
			<tr>
			<th>ID</th>
			<th>Username</th>
			<th>Jobs delivered and approved</th>
			<th>Full member</th>
			<th>Admin</th>
			<th>Actions</th>
			</tr>';
			foreach ($user as $u) {
				$wpu = get_userdata($u->userid);

				if (wolfvtc_hasperm($u->userid, "all")) {
					$perm = "Yes";
				} else {
					$perm = "No";
				}

				if (wolfvtc_hasperm($u->userid, "fullmember")) {
					$fullmember = "Yes";
				} else {
					$fullmember = "No";
				}

				echo '<tr>
				<td>' . $u->userid . '</td>
				<td>' . $wpu->display_name . '</td>
				<td>' . wolfvtc_userjobs($u->userid) . '</td>
				<td>' . $fullmember . '</td>
				<td>' . $perm . '</td>
				<td>
				<a href="?page=wolfvtcadmin&do=users&id=' . $u->userid . '"><button>Edit permissions</button></a>
				</td>
				</tr>';
			}
			echo '</table>';
		}

	} elseif ($_GET['do'] == "cities" && wolfvtc_hasperm(get_current_user_id(), "cc")) {
		echo '<h3>Add City</h3>';

		if (isset($_POST['cityname'])) {
			if (strlen($_POST['cityname']) >= 1 && strlen($_POST['cityname'] <= 70)) {
				$qu = $wpdb->insert(
					$wpdb->prefix . 'wolfvtc_cities',
					array('cityname' => $_POST['cityname']),
					array('%s')
					);

				if ($qu) {
					echo '<div style="background-color:darkgreen;color:#FFFFFF;padding:10px"><p><strong>City saved.</strong></p></div>';
				} else {
					echo '<div style="background-color:darkred;color:#FFFFFF;padding:10px"><p><strong>Something seems to have gone wrong.</strong></p></div>';
				}
			} else {
				echo '<div style="background-color:darkred;color:#FFFFFF;padding:10px"><p><strong>The city name must be between 1 and 70 characters long.</strong></p></div>';
			}
		}

		echo '
		<form method="post" action="?page=wolfvtcadmin&do=cities">
			<p><strong>City name</strong></p>
			<p><input type="text" name="cityname" placeholder="United Kingdom: London" maxlength="70" style="width:250px;"></p>
			<p>Standard format: <i>Country: City</i></p>
			<p><input type="submit" value="Save"></p>
		</form>
		';

	} elseif ($_GET['do'] == "cargo" && wolfvtc_hasperm(get_current_user_id(), "cc")) {
		echo '<h3>Add cargo category</h3>';

		if (isset($_POST['cargoname'])) {
			if (strlen($_POST['cargoname']) >= 1 && strlen($_POST['cargoname'] <= 35)) {
				$qu = $wpdb->insert(
					$wpdb->prefix . 'wolfvtc_cargo',
					array('cargoname' => $_POST['cargoname']),
					array('%s')
					);

				if ($qu) {
					echo '<div style="background-color:darkgreen;color:#FFFFFF;padding:10px"><p><strong>Cargo saved.</strong></p></div>';
				} else {
					echo '<div style="background-color:darkred;color:#FFFFFF;padding:10px"><p><strong>Something seems to have gone wrong.</strong></p></div>';
				}
			} else {
				echo '<div style="background-color:darkred;color:#FFFFFF;padding:10px"><p><strong>The cargo type name must be between 1 and 35 characters long.</strong></p></div>';
			}
		}

		echo '
		<form method="post" action="?page=wolfvtcadmin&do=cargo">
			<p><strong>Cargo type name</strong></p>
			<p><input type="text" name="cargoname" placeholder="Windmill parts" maxlength="35"></p>
			<p><input type="submit" value="Save"></p>
		</form>
		';
		
	} else {
		echo '<p>404 - This page does not exist, or you do not have permission to view it.</p>';
	}
	echo '</div>';
}