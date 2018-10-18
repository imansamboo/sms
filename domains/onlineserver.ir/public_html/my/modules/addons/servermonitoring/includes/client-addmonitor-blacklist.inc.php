<?php

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

use Illuminate\Database\Capsule\Manager as Capsule;

$querys = mysql_query("SELECT `id`,`serviceid` FROM `mod_servermonitoring_services` WHERE `serviceid`='" . mysql_real_escape_string($_REQUEST['id']) . "' AND `uid`='" . mysql_real_escape_string($_SESSION['uid']) . "' LIMIT 1");
$services = mysql_fetch_assoc($querys);
$queryb = mysql_query("SELECT `packageid` FROM `tblhosting` WHERE `id`='" . $services['serviceid'] . "' AND `userid`='" . $_SESSION['uid'] . "'");
$hosting = mysql_fetch_assoc($queryb);
$queryc = mysql_query("SELECT `configoption3` FROM `tblproducts` WHERE `id`='" . $hosting['packageid'] . "'");
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
if ($products['configoption3'] != "on") {
    redir("m=servermonitoring&id=" . $_REQUEST['id']);
    exit;
}

if (isset($_POST['url']) && isset($_POST['monitorname']) && isset($_REQUEST['id']) && is_numeric($_REQUEST['id'])) {
    $url = mysql_real_escape_string($_POST['url']);
    $monitorname = mysql_real_escape_string($_POST['monitorname']);
    $custom_interval = mysql_real_escape_string($_POST['custom_interval']);
    $query = mysql_query("SELECT * FROM `mod_servermonitoring_services` WHERE `serviceid`='" . mysql_real_escape_string($_REQUEST['id']) . "'");
    $services = mysql_fetch_assoc($query);
    $tot = mysql_num_rows($query);
    $querya = mysql_query("SELECT `id` FROM `mod_servermonitoring_monitors` WHERE `serviceid`='" . $services['id'] . "'");
    $tota = mysql_num_rows($querya);
    $queryb = mysql_query("SELECT `packageid` FROM `tblhosting` WHERE `id`='" . $services['serviceid'] . "' AND `userid`='" . $_SESSION['uid'] . "'");
    $hosting = mysql_fetch_assoc($queryb);
    $queryc = mysql_query("SELECT `configoption2`,`configoption3`,`configoption4` FROM `tblproducts` WHERE `id`='" . $hosting['packageid'] . "'");
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
    $interval = ($custom_interval * 60);
    if ($tot == 1 && $tota < $products['configoption2'] && $products['configoption3'] == "on") {
        $query = mysql_query("INSERT INTO `mod_servermonitoring_monitors` SET `type`='blacklist', `status`='Active', `serviceid`='" . $services['id'] . "', `custom_interval`='" . $interval . "',`url`='" . $url . "', `port`='', `totaldowntime`='', `downtime`='0', `lastmonitor`='0', `accesskey`='" . md5(time()) . "', `lastsms`='0', `lastemail`='0', `smssent`='0', `emailssent`='0', `monitorname`='" . $monitorname . "', `location`='0'");
        redir('m=servermonitoring&id=' . $_REQUEST['id'] . '&success=true');
    } elseif ($tota >= $products['configoption2'] || $products['configoption3'] != $LANG['yes']) {
        $error = $LANG['reachedlimit'];
    } else {
        $error = $LANG['checkfields'];
    }
}
$intervals = array(1, 2, 5, 8, 12, 16, 24, 32, 40, 48);
$intdropdown = '';
foreach ($intervals AS $value) {
    if ($value == 1)
        $minute = $LANG['hour'];
    else
        $minute = $LANG['hours'];
    $intdropdown .= '<option value="' . $value . '">' . $value . ' ' . $minute . '</option>';
}

if (!empty($error) && trim($error) != '') {
    $output .= '<div class="alert alert-danger"><center>' . $error . '</center></div>';
}

$output .= '<center><div style="width:50%"><form action="index.php?m=servermonitoring&id=' . $_REQUEST['id'] . '&add=true&type=blacklist&save=true" method="post">';
$url = '';
$monitorname = '';
if (isset($_POST['url']) && !empty($_POST['url']))
    $url = $_POST['url'];
if (isset($_POST['monitorname']) && !empty($_POST['monitorname']))
    $monitorname = $_POST['monitorname'];
$output .= '<span><strong>' . $LANG['monitorname'] . '</strong></span><p><input type="text" name="monitorname" value="' . $monitorname . '" class="form-control" placeholder="' . $LANG['monitorname'] . '"></p>';
$output .= '<span><strong>' . $LANG['hostnameip'] . '</strong></span><p><input type="text" name="url" value="' . $url . '" class="form-control" placeholder="' . $LANG['hostnameip'] . '"></p>';
$output .= '<span><strong>' . $LANG['interval'] . '</strong></span><p><select size="1" name="custom_interval" class="form-control">' . $intdropdown . '</select></p>';
$output .= '<p><button class="btn btn-success" type="submit">' . $LANG['savechanges'] . '</button>&nbsp;&nbsp;<a href="index.php?m=servermonitoring&id=' . $_REQUEST['id'] . '"><button class="btn btn-danger" type="button">' . $LANG['goback'] . '</button></a></p></form></div></center>';
