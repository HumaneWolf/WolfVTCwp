<?php
defined('ABSPATH') or die('No direct access! Bad user!');

function wolfvtc_install() {
    global $wpdb;

    //Options
    delete_option("wolfvtc_divisionsenabled");
    add_option("wolfvtc_divisionsenabled", "0");

    if (get_option('wolfvtc_dbversion') == FALSE) {
        delete_option("wolfvtc_dbversion");
        add_option("wolfvtc_dbversion", "0");
    }

    //Mysql
    $prefix = $wpdb->prefix . 'wolfvtc_';
    $collate = $wpdb->get_charset_collate();

    $queries[0] = 'CREATE TABLE ' . $prefix . 'cargo ( 
        `cargoid` BIGINT NOT NULL AUTO_INCREMENT,
        `cargoname` TEXT NOT NULL,
        PRIMARY KEY (`cargoid`))
        ' . $collate;

    $queries[1] = 'CREATE TABLE ' . $prefix . 'cities ( 
        `cityid` BIGINT NOT NULL AUTO_INCREMENT,
        `cityname` TEXT NOT NULL,
        PRIMARY KEY (`cityid`))
        ' . $collate;

    $queries[2] = 'CREATE TABLE ' . $prefix . 'users ( 
        `userid` BIGINT NOT NULL,
        `division` INT NOT NULL DEFAULT 0,
        `divisionmember` BOOLEAN NOT NULL DEFAULT FALSE,
        `divisionadmin` BOOLEAN NOT NULL DEFAULT FALSE,
        `fullmember` BOOLEAN NOT NULL DEFAULT TRUE,
        `adminjobs` BOOLEAN NOT NULL DEFAULT FALSE,
        `adminusers` BOOLEAN NOT NULL DEFAULT FALSE,
        `admindiv` BOOLEAN NOT NULL DEFAULT FALSE,
        `admincc` BOOLEAN NOT NULL DEFAULT FALSE,
        `superadmin` BOOLEAN NOT NULL DEFAULT FALSE,
        PRIMARY KEY (`userid`))
        ' . $collate;

    $queries[3] = 'CREATE TABLE ' . $prefix . 'divs ( 
        `divid` BIGINT NOT NULL AUTO_INCREMENT,
        `divname` TEXT NOT NULL,
        `description` TEXT NOT NULL,
        `ispublic` BOOLEAN NOT NULL DEFAULT FALSE,
        PRIMARY KEY (`divid`))
        ' . $collate;

    $queries[4] = 'CREATE TABLE ' . $prefix . 'jobs ( 
        `jobid` BIGINT NOT NULL AUTO_INCREMENT,
        `userid` BIGINT NOT NULL,

        `fromcity` BIGINT NOT NULL,
        `tocity` BIGINT NOT NULL,
        `cargo` BIGINT NOT NULL,

        `distance` INT NOT NULL,
        `earnings` INT NOT NULL,
        `notes` TEXT,

        `fuelcosts` INT NOT NULL,
        `repaircosts` INT NOT NULL,
        `travelcosts` INT NOT NULL,

        `approved` BOOLEAN NOT NULL DEFAULT FALSE,
        `approvedby` BIGINT NOT NULL DEFAULT 0,
        `approvedtime` DATETIME NOT NULL DEFAULT "0000-00-00 00:00:00",

        `addedtime` DATETIME NOT NULL,

        `divid` BIGINT NOT NULL DEFAULT 0,
        PRIMARY KEY (`jobid`))
        ' . $collate;
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    foreach ($queries as $q) {
        dbDelta($q);
    }
}

function wolfvtc_remove() {
    global $wpdb;

    //Options
    delete_option("wolfvtc_setup");

    delete_option("wolfvtc_divisionsenabled");
    delete_option("wolfvtc_defaultdivision");
}