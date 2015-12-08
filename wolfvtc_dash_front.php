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
					if (wolfvtc_userdiv(get_current_user_id()) != 0) {
						echo '<a href="?page=wolfvtc&do=mydiv"><input type="button" class="button-primary" style="width:100%" value="My division"></a>
					<p>My division dashboard.</p>';
					} else {
						echo '<a href="?page=wolfvtc&do=divs"><input type="button" class="button-primary" style="width:100%" value="Join division"></a>
					<p>Join a division.</p>';
					}
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
	} elseif ($_GET['do'] == "about") { //MY PROFILE PAGE
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

		if (get_option("wolfvtc_divisionsenabled") != 0) {
			if (wolfvtc_hasperm(get_current_user_id(), "divmember")) {
				$rank = "Approved";
				if (wolfvtc_hasperm(get_current_user_id(), "divadmin")) {
					$rank .= ", admin";
				}
			} else {
				$rank = "Not approved";
			}
			$divid = wolfvtc_userdiv(get_current_user_id());
			echo '<p><strong>Division:</strong> ' . wolfvtc_divname($divid) . ' (' . $rank . ')</p>';
		}

		echo '<p><strong>Permissions</strong></p>
		<ul style="list-style-type:disc;margin-left:20px">';
		if (wolfvtc_hasperm(get_current_user_id(), "fullmember")) {//check each permission
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

		if (wolfvtc_hasperm(get_current_user_id(), "all") == FALSE && wolfvtc_hasperm(get_current_user_id(), "fullmember") == FALSE) { //If (s)he doesn't have any permissions
			echo '<li>None</li>';
		}

		echo '</ul>';

	} elseif ($_GET['do'] == "newjob" && wolfvtc_hasperm(get_current_user_id(), "fullmember")) {
		$userdata['fromcity'] = 0;
		$userdata['tocity'] = 0;
		$userdata['cargo'] = 0;

		$userdata['distance'] = "";
		$userdata['earnings'] = "";

		$userdata['notes'] = "";

		$userdata['fuelcosts'] = "";
		$userdata['repaircosts'] = "";
		$userdata['travelcosts'] = "";

		$userdata['div'] = "";

		$error['is'] = FALSE;
		$error['msg'] = "";

		if (isset($_POST['submitted'])) {
			//From city
			if (isset($_POST['fromcity']) && intval($_POST['fromcity']) > 0 && wolfvtc_cityname(intval($_POST['fromcity'])) != FALSE) {
				$userdata['fromcity'] = intval($_POST['fromcity']);
			} else {
				$error['is'] = TRUE;
				$error['msg'] .= '<p>You must select the city you picked up your load from.</p>';
			}

			//tocity
			if (isset($_POST['tocity']) && intval($_POST['tocity']) > 0 && wolfvtc_cityname(intval($_POST['tocity'])) != FALSE) {
				$userdata['tocity'] = intval($_POST['tocity']);
			} else {
				$error['is'] = TRUE;
				$error['msg'] .= '<p>You must select the city you deliverd your load to.</p>';
			}

			//cargo
			if (isset($_POST['cargo']) && intval($_POST['cargo']) > 0 && wolfvtc_cargoname(intval($_POST['cargo'])) != FALSE) {
				$userdata['cargo'] = intval($_POST['cargo']);
			} else {
				$error['is'] = TRUE;
				$error['msg'] .= '<p>You must select your load type.</p>';
			}


			//distance
			if (isset($_POST['distance']) && intval($_POST['distance']) > 0) {
				if (intval($_POST['distance']) < 1000000) {
					$userdata['distance'] = intval($_POST['distance']);
				} else {
					$error['is'] = TRUE;
					$error['msg'] .= '<p>Are you sure you drove that far?</p>';
				}
				
			} else {
				$error['is'] = TRUE;
				$error['msg'] .= '<p>You must enter your distance.</p>';
			}


			//earnings
			if (isset($_POST['earnings']) && intval($_POST['earnings']) > 0) {
				if (intval($_POST['earnings']) < 1000000) {
					$userdata['earnings'] = intval($_POST['earnings']);
				} else {
					$error['is'] = TRUE;
					$error['msg'] .= '<p>Are you sure you earned a million euros?</p>';
				}
			} else {
				$error['is'] = TRUE;
				$error['msg'] .= '<p>You must enter your earnings.</p>';
			}


			//Other notes
			if (isset($_POST['notes'])) {
				if (strlen($_POST['notes']) <= 250) {
					$userdata['notes'] = htmlspecialchars($_POST['notes']);
				} else {
					$error['is'] = TRUE;
					$error['msg'] .= '<p>The note can be no more than 250 characters.</p>';
				}
			}


			//fuelcosts
			if (isset($_POST['fuelcosts']) && intval($_POST['fuelcosts']) >= 0) {
				if (intval($_POST['fuelcosts']) < 10000) {
					$userdata['fuelcosts'] = intval($_POST['fuelcosts']);
				} else {
					$error['is'] = TRUE;
					$error['msg'] .= '<p>That\'s a lot of fuel. Sure you used that much?</p>';
				}
			} else {
				$error['is'] = TRUE;
				$error['msg'] .= '<p>You must enter your fuel costs</p>';
			}

			//repaircosts
			if (isset($_POST['repaircosts']) && intval($_POST['repaircosts']) >= 0) {
				if (intval($_POST['repaircosts']) < 500000) {
					$userdata['repaircosts'] = intval($_POST['repaircosts']);
				} else {
					$error['is'] = TRUE;
					$error['msg'] .= '<p>You seem to have spent a lot on repairs.</p>';
				}
			} else {
				$error['is'] = TRUE;
				$error['msg'] .= '<p>You must enter your repair costs.</p>';
			}

			//travelcosts
			if (isset($_POST['travelcosts']) && intval($_POST['travelcosts']) >= 0) {
				if (intval($_POST['travelcosts']) < 10000) {
					$userdata['travelcosts'] = intval($_POST['travelcosts']);
				} else {
					$error['is'] = TRUE;
					$error['msg'] .= '<p>That\'s a lot of ferries and toll booths. Are you sure you paid that much?</p>';
				}
			} else {
				$error['is'] = TRUE;
				$error['msg'] .= '<p>You must enter your travel costs.</p>';
			}

			//Division
			if (isset($_POST['div'])) {
				$div = wolfvtc_userdiv(get_current_user_id());
				if (get_option("wolfvtc_divisionsenabled") != 0 && $div != 0 && wolfvtc_hasperm(get_current_user_id(), "divmember")) { //check if it's enabled and user is in a division
					$userdata['div'] = $div;
				} else {
					$userdata['div'] = 0;
				}
			} else {
				$userdata['div'] = 0;
			}

			//Submit?
			if ($error['is'] == FALSE) {
				$wpdb->insert(
					$wpdb->prefix . 'wolfvtc_jobs',
					array(
						'userid' => get_current_user_id(), //userid INT
						'fromcity' => $userdata['fromcity'], // from city INT
						'tocity' => $userdata['tocity'], //to city INT
						'cargo' => $userdata['cargo'], //cargo INT
						'distance' => $userdata['distance'], //distance INT
						'earnings' => $userdata['earnings'], //earnings INT
						'notes' => $userdata['notes'], //notes STRING
						'fuelcosts' => $userdata['fuelcosts'], //fuel INT
						'repaircosts' => $userdata['repaircosts'], //repair INT
						'travelcosts' => $userdata['travelcosts'], //travel INT
						'approved' => 0, //nope, INT, bool
						'approvedby' => 0, //nope, INT
						'addedtime' => current_time('mysql'), //time, STRING
						'divid' => $userdata['div'], //division INT
						),
					array(
						'%d',
						'%d',
						'%d',
						'%d',
						'%d',
						'%d',
						'%s', //NOTES
						'%d',
						'%d',
						'%d',
						'%d',
						'%d',
						'%s', //TIME
						'%d',
						)
					);
				echo '<div style="background-color:darkgreen;color:#FFFFFF;padding:10px"><p><strong>Job Saved.</strong></p></div>';
			} else {
				echo '<div style="background-color:darkred;color:#FFFFFF;padding:10px"><p><strong>Please fix the following:</strong></p>' . $error['msg'] . '</div>';
			}
		}

		echo '<form action="?page=wolfvtc&do=newjob" method="post">
		<table>';

		//Load cities and make dropdown
		echo '<tr>
		<th>Load from:</th>
		<td><select name="fromcity">';
		$citiesdb = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . 'wolfvtc_cities ORDER BY cityname ASC');
		foreach ($citiesdb as $c) {
			if ($c->cityid == $userdata['fromcity']) {
				echo '<option value="' . $c->cityid . '" selected>' . $c->cityname . '</option>';
			} else {
				echo '<option value="' . $c->cityid . '">' . $c->cityname . '</option>';
			}
		}
		echo '</select>
		</td>
		</tr>';

		//And repeat it to make the to city dropdown
		echo '<tr>
		<th>Delivered to:</th>
		<td><select name="tocity">';
		foreach ($citiesdb as $c) {
			if ($c->cityid == $userdata['tocity']) {
				echo '<option value="' . $c->cityid . '" selected>' . $c->cityname . '</option>';
			} else {
				echo '<option value="' . $c->cityid . '">' . $c->cityname . '</option>';
			}
		}
		echo '</select>
		</td>
		</tr>';

		//Last but not least: Cargo dropdown
		echo '<tr>
		<th>Cargo:</th>
		<td><select name="cargo">';
		$cargodb = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . 'wolfvtc_cargo ORDER BY cargoname ASC');
		foreach ($cargodb as $c) {
			if ($c->cargoid == $userdata['cargo']) {
				echo '<option value="' . $c->cargoid . '" selected>' . $c->cargoname . '</option>';
			} else {
				echo '<option value="' . $c->cargoid . '">' . $c->cargoname . '</option>';
			}
		}
		echo '</select>
		</td>
		</tr>';

		//Skip a row
		echo '<tr><td style="height:15px"></td><td style="height:15px"></td></tr>';

		//Distance
		echo '<tr>
		<th>Kilometres Driven:</th>
		<td><input type="number" name="distance" value="' . $userdata['distance'] . '" placeholder="250"> Km</td>
		</tr>';

		//Earnings
		echo '<tr>
		<th>Earnings:</th>
		<td><input type="number" name="earnings" value="' . $userdata['earnings'] . '" placeholder="30000"> €</td>
		</tr>';

		//fuelcosts
		echo '<tr>
		<th>Fuel costs:</th>
		<td><input type="number" name="fuelcosts" value="' . $userdata['fuelcosts'] . '" placeholder="600"> €</td>
		</tr>';

		//repaircosts
		echo '<tr>
		<th>Repair costs:</th>
		<td><input type="number" name="repaircosts" value="' . $userdata['repaircosts'] . '" placeholder="10000"> €</td>
		</tr>';

		//travelcosts
		echo '<tr>
		<th>Travel costs:</th>
		<td><input type="number" name="travelcosts" value="' . $userdata['travelcosts'] . '" placeholder="350"> €</td>
		</tr>';

		//Skip a row
		echo '<tr><td style="height:15px"></td><td style="height:15px"></td></tr>';

		//Notes
		echo '<tr>
		<th>Other notes:</th>
		<td><textarea name="notes">' . $userdata['notes'] . '</textarea></td>
		</tr>';

		//Div job?
		if (get_option("wolfvtc_divisionsenabled") != 0 && wolfvtc_userdiv(get_current_user_id()) != 0 && wolfvtc_hasperm(get_current_user_id(), "divmember")) {
			echo '<tr>
			<th>Division job?</th>
			<td>
			<input type="checkbox" name="div" value="yessir"';
			if (isset($userdata['div']) && $userdata['div'] != "") {
				echo ' checked';
			}
			echo '></td>
			</tr>';
		}

		//Skip a row then submit
		echo '<tr><td style="height:15px"></td><td style="height:15px"></td></tr>
		<input type="hidden" name="submitted" value="yessir">
		<tr>
		<td></td>
		<td><input type="submit" value="Submit job"></td>
		</tr>
		</table>
		</form>';

	} elseif ($_GET['do'] == "myjobs" && wolfvtc_hasperm(get_current_user_id(), "fullmember")) { //ALL MY SUBMITTED JOBS
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

	} elseif ($_GET['do'] == "mydiv" && wolfvtc_hasperm(get_current_user_id(), "fullmember") && wolfvtc_hasperm(get_current_user_id(), "divmember") && get_option("wolfvtc_divisionsenabled") != 0) {
		echo "This page will display info about your current division and a couple of options.";
	} elseif ($_GET['do'] == "divs" && wolfvtc_hasperm(get_current_user_id(), "fullmember") && !wolfvtc_hasperm(get_current_user_id(), "divmember") && get_option("wolfvtc_divisionsenabled") != 0) {
		echo 'This page will display a list of all available divisions to users who are not in one.';
	} else {
		echo '<p>404 - Page not found.</p>';
	}
	echo '</div>';
}