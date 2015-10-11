<?php

defined('ABSPATH') or die('No direct access! Bad user!');

//no of drivers
function wolfvtc_drivers() {
	global $wpdb;

	$d = $wpdb->get_col('SELECT count(*) FROM ' . $wpdb->prefix . 'wolfvtc_users WHERE fullmember=TRUE');
	foreach ($d as $s) {
		return $s;
	}
}

function wolfvtc_jobs() {
	global $wpdb;

	$d = $wpdb->get_col('SELECT count(*) FROM ' . $wpdb->prefix . 'wolfvtc_jobs WHERE approved=TRUE');
	foreach ($d as $s) {
		return $s;
	}
}

function wolfvtc_kmdriven() {
	global $wpdb;

	$d = $wpdb->get_col('SELECT sum(`distance`) FROM ' . $wpdb->prefix . 'wolfvtc_jobs WHERE approved=TRUE');
	foreach ($d as $s) {
		return $s;
	}
}

function wolfvtc_divisions() {
	global $wpdb;

	$d = $wpdb->get_col('SELECT count(*) FROM ' . $wpdb->prefix . 'wolfvtc_divs');
	foreach ($d as $s) {
		return $s;
	}
}