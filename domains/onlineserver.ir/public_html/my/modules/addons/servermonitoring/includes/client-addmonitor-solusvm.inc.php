<?php

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

use Illuminate\Database\Capsule\Manager as Capsule;

$querys = mysql_query("SELECT `id`,`serviceid` FROM `mod_servermonitoring_services` WHERE `serviceid`='" . mysql_real_escape_string($_REQUEST['id']) . "' AND `uid`='" . mysql_real_escape_string($_SESSION['uid']) . "' LIMIT 1");
$services = mysql_fetch_assoc($querys);
$queryb = mysql_query("SELECT `packageid` FROM `tblhosting` WHERE `id`='" . $services['serviceid'] . "' AND `userid`='" . $_SESSION['uid'] . "'");
$hosting = mysql_fetch_assoc($queryb);
$queryc = mysql_query("SELECT `configoption7` FROM `tblproducts` WHERE `id`='" . $hosting['packageid'] . "'");
$products = mysql_fetch_assoc($queryc);
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
if ($products['configoption7'] != "on") {
    redir("m=servermonitoring&id=" . $_REQUEST['id']);
    exit;
}

if (isset($_POST['solusvm_url']) && isset($_POST['solusvm_hash']) && isset($_POST['solusvm_key']) && isset($_POST['location']) && isset($_POST['monitorname']) && isset($_REQUEST['id']) && is_numeric($_REQUEST['id'])) {
    $solusvm_url = mysql_real_escape_string($_POST['solusvm_url']);
    $solusvm_hash = mysql_real_escape_string($_POST['solusvm_hash']);
    $solusvm_key = mysql_real_escape_string($_POST['solusvm_key']);
    $location = mysql_real_escape_string($_POST['location']);
    $monitorname = mysql_real_escape_string($_POST['monitorname']);
    $custom_interval = mysql_real_escape_string($_POST['custom_interval']);
    $query = mysql_query("SELECT * FROM `mod_servermonitoring_services` WHERE `serviceid`='" . mysql_real_escape_string($_REQUEST['id']) . "'");
    $services = mysql_fetch_assoc($query);
    $tot = mysql_num_rows($query);
    $querya = mysql_query("SELECT `id` FROM `mod_servermonitoring_monitors` WHERE `serviceid`='" . $services['id'] . "'");
    $tota = mysql_num_rows($querya);
    $queryb = mysql_query("SELECT `packageid` FROM `tblhosting` WHERE `id`='" . $services['serviceid'] . "' AND `userid`='" . $_SESSION['uid'] . "'");
    $hosting = mysql_fetch_assoc($queryb);
    $queryc = mysql_query("SELECT `configoption2`,`configoption7` FROM `tblproducts` WHERE `id`='" . $hosting['packageid'] . "'");
    $products = mysql_fetch_assoc($queryc);
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
    if ($tot == 1 && $tota < $products['configoption2'] && $products['configoption7'] == "on" && !empty($_POST['solusvm_url']) && !empty($_POST['solusvm_hash']) && !empty($_POST['solusvm_key']) && !empty($_POST['monitorname'])) {
        $query = mysql_query("INSERT INTO `mod_servermonitoring_monitors` SET `custom_interval`='" . $custom_interval . "', `type`='solusvm', `status`='Active', `solusvm_url`='" . $solusvm_url . "', `solusvm_key`='" . $solusvm_key . "', `solusvm_hash`='" . $solusvm_hash . "', `accesskey`='" . md5(time()) . "', `serviceid`='" . $services['id'] . "',`url`='', `port`='0', `totaldowntime`='', `downtime`='0', `lastmonitor`='0', `lastsms`='0', `lastemail`='0', `smssent`='0', `emailssent`='0', `monitorname`='" . $monitorname . "', `location`='" . $location . "'");
        redir('m=servermonitoring&id=' . $_REQUEST['id'] . '&success=true');
    } elseif ($tota >= $products['configoption2']) {
        $error = $LANG['reachedlimit'];
    } else {
        $error = $LANG['checkfields'];
    }
}

if (!empty($error) && trim($error) != '') {
    $output .= '<div class="alert alert-danger"><center>' . $error . '</center></div>';
}

$output .= '<center><div style="width:50%"><form action="index.php?m=servermonitoring&id=' . $_REQUEST['id'] . '&add=true&type=solusvm&save=true" method="post">';
$SETTINGS = servermonitoring_settings($vars);
$localhostName = $SETTINGS['localhostName'];
$locations = servermonitoring_locations();
$ldropdown .= '<option value="0">' . $localhostName . '</option>';
foreach ($locations AS $key => $value) {
    if (isset($_POST['location']) && $_POST['location'] == $key)
        $selected = " selected";
    else
        $selected = "";
    $ldropdown .= '<option value="' . $key . '"' . $selected . '>' . $value['location'] . '</option>';
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
$intdropdown = '';
$intervals = servermonitoring_intervals($pinterval);
foreach ($intervals AS $value) {
    if ($pinterval == $value)
        $select = " selected";
    else
        $select = "";
    if ($value == 1)
        $minute = $LANG['minute'];
    else
        $minute = $LANG['minutes'];
    $intdropdown .= '<option value="' . $value . '"' . $select . '>' . $value . ' ' . $minute . '</option>';
}
$solusvm_url = '';
$solusvm_key = '';
$solusvm_hash = '';
$monitorname = '';
if (isset($_POST['solusvm_url']) && !empty($_POST['solusvm_url']))
    $solusvm_url = $_POST['solusvm_url'];
if (isset($_POST['solusvm_key']) && !empty($_POST['solusvm_key']))
    $solusvm_key = $_POST['solusvm_key'];
if (isset($_POST['solusvm_hash']) && !empty($_POST['solusvm_hash']))
    $solusvm_hash = $_POST['solusvm_hash'];
if (isset($_POST['monitorname']) && !empty($_POST['monitorname']))
    $monitorname = $_POST['monitorname'];
$output .= '<span><strong>' . $LANG['monitorname'] . '</strong></span><p><input type="text" name="monitorname" value="' . $monitorname . '" class="form-control" placeholder="' . $LANG['monitorname'] . '"></p>';
$output .= '<span><strong>' . $LANG['monitoringlocation'] . '</strong></span><p><select size="1" name="location" class="form-control">' . $ldropdown . '</select></p>';
$output .= '<span><strong>' . $LANG['solusvmurlinchttp'] . '</strong></span><p><input type="text" name="solusvm_url" value="' . $solusvm_url . '" class="form-control" placeholder="' . $LANG['solusvm_url'] . '"></p>';
$output .= '<span><strong>' . $LANG['solusvm_key'] . '</strong></span><p><input type="text" name="solusvm_key" value="' . $solusvm_key . '" class="form-control" placeholder="' . $LANG['solusvm_key'] . '"></p>';
$output .= '<span><strong>' . $LANG['solusvm_hash'] . '</strong></span><p><input type="password" name="solusvm_hash" value="' . $solusvm_hash . '" class="form-control" placeholder="' . $LANG['solusvm_hash'] . '"></p>';
$output .= '<span><strong>' . $LANG['interval'] . '</strong></span><p><select size="1" name="custom_interval" class="form-control">' . $intdropdown . '</select></p>';
$output .= '<p><button class="btn btn-success" type="submit">' . $LANG['savechanges'] . '</button>&nbsp;&nbsp;<a href="index.php?m=servermonitoring&id=' . $_REQUEST['id'] . '"><button class="btn btn-danger" type="button">' . $LANG['goback'] . '</button></a></p></form></div></center>';
