<?php

if (!defined('WHMCS'))
    die('This file cannot be accessed directly');

use Illuminate\Database\Capsule\Manager as Capsule;

add_hook('AdminClientServicesTabFields', 1, function($vars) {
    $checkservermove = Capsule::table('mod_servermonitoring_services')->where('serviceid', $vars['id'])->select('uid')->first();
    if ($checkservermove <= 0){
        return '';
    }
    $checkuserrmove = Capsule::table('tblhosting')->where('id', $vars['id'])->select('userid')->first();
    if ($checkservermove->uid != $checkuserrmove->userid) {
        Capsule::table('mod_servermonitoring_services')->where('serviceid', $vars['id'])->update([
            'uid' => $checkuserrmove->userid
        ]);
        Capsule::table('mod_servermonitoring_ports')->where('serviceid', $vars['id'])->update([
            'uid' => $checkuserrmove->userid
        ]);        
    }
});

function servermonitoring_settingsa($vars = null) {
    $SETING = array();
    $result = select_query("mod_servermonitoring_settings", "", "");
    while ($data = @mysql_fetch_array($result)) {
        $setting = $data['setting'];
        $value = $data['value'];
        $SETING["{$setting}"] = "{$value}";
    }
    return $SETING;
}

function servermonitoring_InvoicePaid($vars = null) {
    global $CONFIG;
    if (file_exists(ROOTDIR . "/modules/addons/servermonitoring/lang/" . $CONFIG['Language'] . ".php")) {
        include(ROOTDIR . "/modules/addons/servermonitoring/lang/" . $CONFIG['Language'] . ".php");
    } elseif (file_exists(ROOTDIR . "/modules/addons/servermonitoring/lang/english.php")) {
        include(ROOTDIR . "/modules/addons/servermonitoring/lang/english.php");
    }

    $query = mysql_query("SELECT `description`,`userid` FROM `tblinvoiceitems` WHERE `invoiceid`='" . $vars['invoiceid'] . "' AND `description` LIKE '%[#28193]%'");
    $invoicepart = mysql_fetch_assoc($query);
    $total = mysql_num_rows($query);

    if (strpos($invoicepart['description'], '[#28193]') === false || $total == 0)
        return;

    $desc = $invoicepart['description'];
    $credits = explode(' ', $desc);
    $serviceid = explode('][', $desc);
    $serviceid = explode(']', $serviceid[1]);
    $serviceid = (int) $serviceid[0];
    $credits = (int) $credits[0];

    $query = mysql_query("UPDATE `mod_servermonitoring_services` SET `smscredits`=smscredits+" . $credits . " WHERE `serviceid`='" . $serviceid . "' AND `uid`='" . $invoicepart['userid'] . "'");
    logactivity("Server Monitoring - " . $credits . " " . $_ADDONLANG['activitylogaddsmscredits'] . ' ' . $invoicepart['userid']);
}

function servermonitoring_EmailTplMergeFields($vars) {

    $tplid = mysql_real_escape_string($_GET['id']);
    $monitorTpl = full_query("SELECT `name` FROM `tblemailtemplates` WHERE `id`='" . $tplid . "' AND `type`='general' AND `custom`='0'");
    $monitorTpl = mysql_fetch_assoc($monitorTpl);

    if ($monitorTpl['name'] == "Server Monitoring - Weekly Uptime Report") {
        $merges = array(
            "servermonitoring_url" => "Monitor URL",
            "servermonitoring_port" => "Monitor Port",
            "servermonitoring_monitorname" => "Monitor Name",
            "servermonitoring_weeklyreport" => "Weekly Report Table",
        );
    } elseif ($monitorTpl['name'] == "Server Monitoring - Weekly SolusVM Uptime Report") {
        $merges = array(
            "servermonitoring_monitorname" => "Monitor Name",
            "servermonitoring_weeklyreport" => "Weekly Report Table",
        );
    } elseif ($monitorTpl['name'] == "Server Monitoring - Blacklist Change Email") {
        $merges = array(
            "servermonitoring_monitorname" => "Monitor Name",
            "servermonitoring_url" => "Blacklist IP",
            "servermonitoring_time" => "Monitor Time",
        );
    } elseif ($monitorTpl['name'] == "Server Monitoring - SolusVM Monitor Up Email") {
        $merges = array(
            "servermonitoring_monitorname" => "Monitor Name",
            "servermonitoring_time" => "Monitor Time",
            "servermonitoring_downtime" => "Downtime duration",
        );
    } elseif ($monitorTpl['name'] == "Server Monitoring - SolusVM Monitor Down Email" || $monitorTpl['name'] == "Server Monitoring - SolusVM Monitor Down and Rebooted Email") {
        $merges = array(
            "servermonitoring_monitorname" => "Monitor Name",
            "servermonitoring_time" => "Monitor Time",
        );
    } elseif ($monitorTpl['name'] == "Server Monitoring - Monitor Up Email") {
        $merges = array(
            "servermonitoring_url" => "Monitor URL",
            "servermonitoring_port" => "Monitor Port",
            "servermonitoring_monitorname" => "Monitor Name",
            "servermonitoring_time" => "Monitor Time",
            "servermonitoring_downtime" => "Downtime duration",
        );
    } elseif ($monitorTpl['name'] == "Server Monitoring - Monitor Up Email") {
        $merges = array(
            "servermonitoring_url" => "Monitor URL",
            "servermonitoring_port" => "Monitor Port",
            "servermonitoring_monitorname" => "Monitor Name",
            "servermonitoring_time" => "Monitor Time",
            "servermonitoring_downtime" => "Downtime duration",
        );
    } else {
        $merges = array(
            "servermonitoring_url" => "Monitor URL",
            "servermonitoring_port" => "Monitor Port",
            "servermonitoring_monitorname" => "Monitor Name",
            "servermonitoring_time" => "Monitor Time",
        );
    }

    if ($vars['type'] == "general") {
        if (strpos($monitorTpl['name'], 'Server Monitoring - ') !== false) {
            return $merges;
        }
    }
}

function servermonitoring_AddNavBar($vars) {
    global $CONFIG;
    $SETING = servermonitoring_settingsa($vars);
    if (file_exists(ROOTDIR . "/modules/addons/servermonitoring/lang/" . $CONFIG['Language'] . ".php")) {
        include(ROOTDIR . "/modules/addons/servermonitoring/lang/" . $CONFIG['Language'] . ".php");
    } elseif (file_exists(ROOTDIR . "/modules/addons/servermonitoring/lang/english.php")) {
        include(ROOTDIR . "/modules/addons/servermonitoring/lang/english.php");
    }
    if (isset($_SESSION['uid'])) {
        $primaryNavbar = Menu::primaryNavbar();
        $servermonitoring_nav = $_ADDONLANG['navbar_text'];
        $primaryNavbar['Services']->addChild('servermonitoring', array('label' => $servermonitoring_nav, 'uri' => 'index.php?m=servermonitoring', 'order' => 10));
    } elseif ($SETING['clientQuickCheck']) {
        $primaryNavbar = Menu::primaryNavbar();
        $servermonitoring_nav = $_ADDONLANG['navbar_text_qc'];
        $primaryNavbar->addChild('servermonitoring', array('label' => $servermonitoring_nav, 'uri' => 'index.php?m=servermonitoring', 'order' => 100));
    }
}

add_hook("ClientAreaPrimaryNavbar", 0, "servermonitoring_AddNavBar");
add_hook("EmailTplMergeFields", 1, "servermonitoring_EmailTplMergeFields");
add_hook("InvoicePaidPreEmail", 1, "servermonitoring_InvoicePaid");
