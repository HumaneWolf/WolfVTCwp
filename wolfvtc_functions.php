<?php

defined('ABSPATH') or die('No direct access! Bad user!');

//
// GENERAL STATISTICS
//

//no of drivers
function wolfvtc_drivers() {
	global $wpdb;

	$d = $wpdb->get_col('SELECT count(*) FROM ' . $wpdb->prefix . 'wolfvtc_users WHERE fullmember=TRUE');
	foreach ($d as $s) {
		return $s;
	}
}

//Number of jobs
function wolfvtc_jobs() {
	global $wpdb;

	$d = $wpdb->get_col('SELECT count(*) FROM ' . $wpdb->prefix . 'wolfvtc_jobs WHERE approved=TRUE');
	foreach ($d as $s) {
		return $s;
	}
}

//KM Driven
function wolfvtc_kmdriven() {
	global $wpdb;

	$d = $wpdb->get_col('SELECT sum(`distance`) FROM ' . $wpdb->prefix . 'wolfvtc_jobs WHERE approved=TRUE');
	foreach ($d as $s) {
		return $s;
	}
}

//Divisions
function wolfvtc_divisions() {
	global $wpdb;

	$d = $wpdb->get_col('SELECT count(*) FROM ' . $wpdb->prefix . 'wolfvtc_divs');
	foreach ($d as $s) {
		return $s;
	}
}

//
// USER
//

//Has permission to
function wolfvtc_hasperm($userid, $perm = "all") {
	global $wpdb;

	$u = $wpdb->get_row('SELECT * FROM ' . $wpdb->prefix . 'wolfvtc_users WHERE userid=' . intval($userid), 'ARRAY_A');

	if ($u != null) {
		if ($perm == "fullmember") {
			if ($u['fullmember'] == TRUE) {
				return TRUE;
			} else {
				return FALSE;
			}
		} elseif ($perm == "divmember") {
			if ($u['divisionmember'] == TRUE) {
				return TRUE;
			} else {
				return FALSE;
			}
		} elseif ($perm == "divadmin") {
			if ($u['divisionadmin'] == TRUE) {
				return TRUE;
			} else {
				return FALSE;
			}
		} elseif ($perm == "jobs") {
			if ($u['adminjobs'] == TRUE) {
				return TRUE;
			} else {
				return FALSE;
			}
		} elseif ($perm == "users") {
			if ($u['adminusers'] == TRUE) {
				return TRUE;
			} else {
				return FALSE;
			}
		} elseif ($perm == "div") {
			if ($u['admindiv'] == TRUE) {
				return TRUE;
			} else {
				return FALSE;
			}
		} elseif ($perm == "cc") {
			if ($u['admincc'] == TRUE) {
				return TRUE;
			} else {
				return FALSE;
			}
		} elseif ($perm == "super") {
			if ($u['superadmin'] == TRUE) {
				return TRUE;
			} else {
				return FALSE;
			}
		} else {
			if ($u['adminjobs'] == TRUE || $u['adminusers'] == TRUE || $u['admindiv'] == TRUE || $u['admincc'] == TRUE || $u['superadmin'] == TRUE) {
				return TRUE;
			} else {
				return FALSE;
			}
		}
	} else {
		return FALSE;
	}
}

//Is user in our system
function wolfvtc_isuser($userid) {
	global $wpdb;

	$u = $wpdb->get_row('SELECT userid FROM ' . $wpdb->prefix . 'wolfvtc_users WHERE userid=' . intval($userid), 'ARRAY_A');
	if ($u != null) {
		return TRUE;
	} else {
		return FALSE;
	}
}

//KM Driven
function wolfvtc_userkmdriven($userid) {
	global $wpdb;

	$d = $wpdb->get_col('SELECT sum(`distance`) FROM ' . $wpdb->prefix . 'wolfvtc_jobs WHERE approved=TRUE AND userid=' . intval(userid));
	foreach ($d as $s) {
		return $s;
	}
}

//User division
function wolfvtc_userdiv($userid) {
	global $wpdb;

	$u = $wpdb->get_var('SELECT division FROM ' . $wpdb->prefix . 'wolfvtc_users WHERE userid=' . intval($userid));

	return $u;
}

//
// CITY AND CARGO
//

function wolfvtc_cityname($id) {
	global $wpdb;

	$u = $wpdb->get_row('SELECT cityname FROM ' . $wpdb->prefix . 'wolfvtc_cities WHERE cityid=' . intval($id), 'ARRAY_A');
	if ($u != null) {
		foreach ($u as $k) {
			return $k;
		}
	} else {
		return FALSE;
	}
}

function wolfvtc_cargoname($id) {
	global $wpdb;

	$u = $wpdb->get_row('SELECT cargoname FROM ' . $wpdb->prefix . 'wolfvtc_cargo WHERE cargoid=' . intval($id), 'ARRAY_A');
	if ($u != null) {
		foreach ($u as $k) {
			return $k;
		}
	} else {
		return FALSE;
	}
}

//
// DIVISIONS
//

function wolfvtc_divname($id) {
	if ($id == 0) {
		return "None";
	} else {
		global $wpdb;

		$u = $wpdb->get_row('SELECT divname FROM ' . $wpdb->prefix . 'wolfvtc_divs WHERE divid=' . intval($id), 'ARRAY_A');
		if ($u != null) {
			foreach ($u as $k) {
				return $k;
			}
		} else {
			return FALSE;
		}
	}
}