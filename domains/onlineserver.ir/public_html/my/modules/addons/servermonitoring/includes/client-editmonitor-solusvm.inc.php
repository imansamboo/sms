<?php

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

use Illuminate\Database\Capsule\Manager as Capsule;

if (isset($_REQUEST['disable']) && $_REQUEST['disable']) {
    $mid = mysql_real_escape_string($_REQUEST['mid']);
    $query = mysql_query("UPDATE `mod_servermonitoring_monitors` SET `status`='Disabled' WHERE `type`='solusvm' AND `id`='" . $mid . "'");
    redir("m=servermonitoring&id=" . $_REQUEST['id'] . "&mid=" . $mid . "&edit=true&type=solusvm&success=disabled");
}
if (isset($_REQUEST['enable']) && $_REQUEST['enable']) {
    $mid = mysql_real_escape_string($_REQUEST['mid']);
    $query = mysql_query("UPDATE `mod_servermonitoring_monitors` SET `status`='Active' WHERE `type`='solusvm' AND `id`='" . $mid . "'");
    redir("m=servermonitoring&id=" . $_REQUEST['id'] . "&mid=" . $mid . "&edit=true&type=solusvm&success=enabled");
}
if (isset($_REQUEST['delete']) && $_REQUEST['delete']) {
    $mid = mysql_real_escape_string($_REQUEST['mid']);
    $query = mysql_query("DELETE FROM `mod_servermonitoring_monitors` WHERE `type`='solusvm' AND `id`='" . $mid . "'");
    redir("m=servermonitoring&id=" . $_REQUEST['id'] . "&success=deleted");
}

if (isset($_POST['solusvm_url']) && isset($_POST['solusvm_key']) && isset($_POST['solusvm_hash']) && isset($_POST['location']) && isset($_REQUEST['mid']) && isset($_REQUEST['id']) && is_numeric($_REQUEST['mid']) && is_numeric($_REQUEST['id'])) {
    $solusvm_url = mysql_real_escape_string($_POST['solusvm_url']);
    $solusvm_key = mysql_real_escape_string($_POST['solusvm_key']);
    $solusvm_hash = mysql_real_escape_string($_POST['solusvm_hash']);
    $location = mysql_real_escape_string($_POST['location']);
    $monitorname = mysql_real_escape_string($_POST['monitorname']);
    $custom_interval = mysql_real_escape_string($_POST['custom_interval']);
    $query = mysql_query("SELECT * FROM `mod_servermonitoring_services` WHERE `serviceid`='" . mysql_real_escape_string($_REQUEST['id']) . "'");
    $services = mysql_fetch_assoc($query);
    $querya = mysql_query("SELECT * FROM `mod_servermonitoring_monitors` WHERE `type`='solusvm' AND `serviceid`='" . $services['id'] . "' AND `id`='" . mysql_real_escape_string($_REQUEST['mid']) . "'");
    $monitor = mysql_fetch_assoc($querya);
    if (!empty($monitor['id']) && $monitor['type'] == "solusvm") {
        if ($_POST['solusvm_url'] != $monitor['solusvm_url'])
            $clearstats = true;
        else
            $clearstats = false;
        if ($clearstats) {
            $query = mysql_query("UPDATE `mod_servermonitoring_monitors` SET `solusvm_url`='" . $solusvm_url . "', `smssent`='0', `emailssent`='0',`totaldowntime`='', `downtime`='0', `lastmonitor`='0', `lastsms`='0', `lastemail`='0' WHERE `id`='" . $monitor['id'] . "'");
        }
        $query = mysql_query("UPDATE `mod_servermonitoring_monitors` SET `custom_interval`='" . $custom_interval . "', `monitorname`='" . $monitorname . "', `location`='" . $location . "', `solusvm_key`='" . $solusvm_key . "', `solusvm_hash`='" . $solusvm_hash . "' WHERE `type`='solusvm' AND `id`='" . $monitor['id'] . "'");
        redir('m=servermonitoring&id=' . $_REQUEST['id'] . '&success=true');
    }
}

$query = mysql_query("SELECT * FROM `mod_servermonitoring_services` WHERE `serviceid`='" . mysql_real_escape_string($_REQUEST['id']) . "'");
$services = mysql_fetch_assoc($query);
$querya = mysql_query("SELECT * FROM `mod_servermonitoring_monitors` WHERE `type`='solusvm' AND `serviceid`='" . $services['id'] . "' AND `id`='" . mysql_real_escape_string($_REQUEST['mid']) . "'");
$monitor = mysql_fetch_assoc($querya);
$total = mysql_num_rows($querya);

if ($total == 1) {
    $output .= '<pre><p align="center" style="margin-bottom:0px;">';
    $output .= '<a href="index.php?m=servermonitoring&id=' . $_REQUEST['id'] . '&mid=' . $_REQUEST['mid'] . '&maintenance=true"><button type="button" class="btn btn-info">' . $LANG['maintenanceschedule'] . '</button></a>&nbsp;&nbsp;';
    if ($monitor['status'] == "Active") {
        $output .= '<a href="index.php?m=servermonitoring&id=' . $_REQUEST['id'] . '&mid=' . $_REQUEST['mid'] . '&edit=true&type=solusvm&disable=true"><button type="button" class="btn btn-warning">' . $LANG['disablemonitor'] . '</button></a>&nbsp;&nbsp;';
    } else {
        $output .= '<a href="index.php?m=servermonitoring&id=' . $_REQUEST['id'] . '&mid=' . $_REQUEST['mid'] . '&edit=true&type=solusvm&enable=true"><button type="button" class="btn btn-success">' . $LANG['enablemonitor'] . '</button></a>&nbsp;&nbsp;';
    }

    $output .= '<a href="index.php?m=servermonitoring&id=' . $_REQUEST['id'] . '&mid=' . $_REQUEST['mid'] . '&edit=true&type=solusvm&delete=true"><button class="btn btn-danger" type="button" onclick="return confirm(\'' . $LANG['deletemonitorwarn'] . '\');">' . $LANG['deletemonitor'] . '</button></a>';
    $output .= '</p></pre>';

    $output .= '<center><div style="width:50%"><form action="index.php?m=servermonitoring&id=' . $_REQUEST['id'] . '&mid=' . $_REQUEST['mid'] . '&edit=true&type=solusvm&save=true" method="post">';

    $ports = servermonitoring_ports($_SESSION['uid'], $services['id']);
    $ports = explode(',', $ports);
    $poutput = "";
    foreach ($ports AS $key => $value) {
        $explodeports = explode("|", $value);
        if ($monitor['port'] == $explodeports[1])
            $selected = " selected";
        else
            $selected = "";
        if ($monitor['port'] == "" && $explodeports[1] == "80")
            $selected = " selected";
        elseif ($monitor['port'] == "")
            $selected = "";
        $poutput .= '<option value="' . $explodeports[1] . '"' . $selected . '>' . $explodeports[1] . ' (' . $explodeports[0] . ')</option>';
    }

    $SETTINGS = servermonitoring_settings($vars);
    $localhostName = $SETTINGS['localhostName'];
    $locations = servermonitoring_locations();
    $ldropdown .= '<option value="0">' . $localhostName . '</option>';
    foreach ($locations AS $key => $value) {
        if ($monitor['location'] == $key)
            $select = " selected";
        else
            $select = "";
        $ldropdown .= '<option value="' . $key . '"' . $select . '>' . $value['location'] . '</option>';
    }

    $querye = mysql_query("SELECT * FROM `mod_servermonitoring_services` WHERE `uid`='" . $_SESSION['uid'] . "' AND `serviceid`='" . mysql_real_escape_string($_REQUEST['id']) . "'");
    $services = mysql_fetch_assoc($querye);
    $querya = mysql_query("SELECT * FROM `tblhosting` WHERE `id`='" . $services['serviceid'] . "' AND `userid`='" . $_SESSION['uid'] . "'");
    $hosting = mysql_fetch_assoc($querya);
    $queryd = mysql_query("SELECT `configoption1` FROM `tblproducts` WHERE `id`='" . $hosting['packageid'] . "'");
    $products = mysql_fetch_assoc($queryd);
    if (Capsule::schema()->hasTable('tblmodule_configuration')) {
        $checkisaddon = Capsule::table('tblhostingaddons')->where('hostingid', $services['serviceid'])->join('tbladdons', 'tblhostingaddons.addonid', '=', 'tbladdons.id')->where('module', 'serverMonitoring')->select('addonid')->first();
        if (count($checkisaddon) > 0) {
            $plist = Capsule::table('tblmodule_configuration')->where('entity_type', 'addon')->where('entity_id', $checkisaddon->addonid)->get();
            if (count($plist) > 0) {
                foreach ($plist as $value) {
                    $products[$value->setting_name] = $value->value;
                }
            }
        }
    }
    $pinterval = trim(preg_replace("/[^0-9]/", "", $products['configoption1']));

    $intervals = servermonitoring_intervals($pinterval);
    $intdropdown = '';
    foreach ($intervals AS $value) {
        if ($monitor['custom_interval'] == $value && $monitor['custom_interval'] != 0)
            $select = " selected";
        elseif ($monitor['custom_interval'] != $value && $monitor['custom_interval'] != 0)
            $select = "";
        if ($monitor['custom_interval'] == 0 && $pinterval == $value)
            $select = " selected";
        elseif ($monitor['custom_interval'] == 0 && $pinterval != $value)
            $select = "";
        if ($value == 1)
            $minute = $LANG['minute'];
        else
            $minute = $LANG['minutes'];
        $intdropdown .= '<option value="' . $value . '"' . $select . '>' . $value . ' ' . $minute . '</option>';
    }

    if (isset($_POST['solusvm_url']) && !empty($_POST['solusvm_url']))
        $solusvm_url = $_POST['solusvm_url'];
    else
        $solusvm_url = $monitor['solusvm_url'];
    if (isset($_POST['solusvm_key']) && !empty($_POST['solusvm_key']))
        $solusvm_key = $_POST['solusvm_key'];
    else
        $solusvm_key = $monitor['solusvm_key'];
    if (isset($_POST['solusvm_hash']) && !empty($_POST['solusvm_hash']))
        $solusvm_hash = $_POST['solusvm_hash'];
    else
        $solusvm_hash = $monitor['solusvm_hash'];
    if (isset($_POST['monitorname']) && !empty($_POST['monitorname']))
        $monitorname = $_POST['monitorname'];
    else
        $monitorname = $monitor['monitorname'];
    $output .= '<span><strong>' . $LANG['monitorname'] . '</strong></span><p><input type="text" name="monitorname" value="' . $monitorname . '" class="form-control" placeholder="' . $LANG['monitorname'] . '"></p>';
    $output .= '<span><strong>' . $LANG['monitoringlocation'] . '</strong></span><p><select size="1" name="location" class="form-control">' . $ldropdown . '</select></p>';
    $output .= '<span><strong>' . $LANG['solusvmurlinchttp'] . '</strong></span><p><input type="text" name="solusvm_url" value="' . $solusvm_url . '" class="form-control" placeholder="' . $LANG['solusvm_url'] . '"></p>';
    $output .= '<span><strong>' . $LANG['solusvm_key'] . '</strong></span><p><input type="text" name="solusvm_key" value="' . $solusvm_key . '" class="form-control" placeholder="' . $LANG['solusvm_key'] . '"></p>';
    $output .= '<span><strong>' . $LANG['solusvm_hash'] . '</strong></span><p><input type="password" name="solusvm_hash" value="' . $solusvm_hash . '" class="form-control" placeholder="' . $LANG['solusvm_hash'] . '"></p>';
    $output .= '<span><strong>' . $LANG['interval'] . '</strong></span><p><select size="1" name="custom_interval" class="form-control">' . $intdropdown . '</select></p>';

    $output .= '<p><button class="btn btn-success" type="submit" onclick="return confirm(\'' . $LANG['editmonitorsolusvmwarn'] . '\');">' . $LANG['savechanges'] . '</button>&nbsp;&nbsp;<a href="index.php?m=servermonitoring&id=' . $_REQUEST['id'] . '"><button class="btn btn-danger" type="button">' . $LANG['goback'] . '</button></a></p></form></div></center>';
} else {
    redir('m=servermonitoring&id=' . $_REQUEST['id']);
}