<?php

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

use Illuminate\Database\Capsule\Manager as Capsule;

$id = mysql_real_escape_string($_REQUEST['id']);

if (isset($_REQUEST['save']) && $_REQUEST['save'] && isset($_REQUEST['solusvm_url']) && isset($_REQUEST['monitorname']) && isset($_REQUEST['solusvm_key']) && isset($_REQUEST['solusvm_hash'])) {
    $id = mysql_real_escape_string($_REQUEST['id']);
    $solusvm_url = mysql_real_escape_string($_REQUEST['solusvm_url']);
    $solusvm_key = mysql_real_escape_string($_REQUEST['solusvm_key']);
    $solusvm_hash = mysql_real_escape_string($_REQUEST['solusvm_hash']);
    $monitorname = mysql_real_escape_string($_REQUEST['monitorname']);
    $custom_interval = mysql_real_escape_string($_REQUEST['custom_interval']);
    $query = mysql_query("UPDATE `mod_servermonitoring_monitors` SET `custom_interval`='" . $custom_interval . "', `monitorname`='" . $monitorname . "', `solusvm_url`='" . $solusvm_url . "', `solusvm_key`='" . $solusvm_key . "', `solusvm_hash`='" . $solusvm_hash . "' WHERE `type`='solusvm' AND `id`='" . $id . "'");
    redir("module=servermonitoring&success=edit");
    exit;
}

$query = mysql_query("SELECT `solusvm_url`,`solusvm_key`,`solusvm_hash`,`monitorname`,`custom_interval`,`serviceid` FROM `mod_servermonitoring_monitors` WHERE `type`='solusvm' AND `id`='" . $id . "' LIMIT 1");
$result = mysql_fetch_assoc($query);

$querye = mysql_query("SELECT * FROM `mod_servermonitoring_services` WHERE `id`='" . mysql_real_escape_string($result['serviceid']) . "'");
$services = mysql_fetch_assoc($querye);
$querya = mysql_query("SELECT * FROM `tblhosting` WHERE `id`='" . $services['serviceid'] . "'");
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
        $pname = Capsule::table('tbladdons')->where('id', $checkisaddon->addonid)->select('name')->first();
        $products['name'] = $pname->name;
    }
}
$pinterval = trim(preg_replace("/[^0-9]/", "", $products['configoption1']));

$intervals = servermonitoring_intervals($pinterval);
foreach ($intervals AS $value) {
    if ($result['custom_interval'] == $value && $result['custom_interval'] != 0)
        $select = " selected";
    elseif ($result['custom_interval'] != $value && $result['custom_interval'] != 0)
        $select = "";
    if ($result['custom_interval'] == 0 && $pinterval == $value)
        $select = " selected";
    elseif ($result['custom_interval'] == 0 && $pinterval != $value)
        $select = "";
    if ($value == 1)
        $minute = $LANG['minute'];
    else
        $minute = $LANG['minutes'];
    $intdropdown .= '<option value="' . $value . '"' . $select . '>' . $value . ' ' . $minute . '</option>';
}

if (empty($_POST['monitorname']))
    $monitorname = $result['monitorname'];
else
    $monitorname = $_POST['monitorname'];
if (empty($_POST['solusvm_url']))
    $solusvm_url = $result['solusvm_url'];
else
    $solusvm_url = $_POST['solusvm_url'];
if (empty($_POST['solusvm_key']))
    $solusvm_key = $result['solusvm_key'];
else
    $solusvm_key = $_POST['solusvm_key'];
if (empty($_POST['solusvm_hash']))
    $solusvm_hash = $result['solusvm_hash'];
else
    $solusvm_hash = $_POST['solusvm_hash'];
echo '<form action="' . $modulelink . '&a=editmonitor&save=true&id=' . $_REQUEST['id'] . '&type=solusvm" method="post"><pre style="text-align:center;">';
echo '<center><h2>' . $LANG['editmonitor'] . '</h2><div style="width:50%;"><input type="text" class="form-control" name="monitorname" value="' . $monitorname . '" placeholder="' . $LANG['monitorname'] . '" style="margin-bottom:10px;"><input type="text" class="form-control" name="solusvm_url" value="' . $solusvm_url . '" placeholder="' . $LANG['solusvm_url'] . '" style="margin-bottom:10px;"><input type="text" class="form-control" name="solusvm_key" value="' . $solusvm_key . '" placeholder="' . $LANG['solusvm_key'] . '" style="margin-bottom:10px;"><input type="text" class="form-control" name="solusvm_hash" value="' . $solusvm_hash . '" placeholder="' . $LANG['solusvm_hash'] . '" style="margin-bottom:10px;">';
echo '<select size="1" name="custom_interval" class="form-control">' . $intdropdown . '</select>';
echo '</div></center>';
echo '<br><p align="center"><button class="btn btn-success" type="submit" style="margin-top: 10px;">' . $LANG['editmonitor'] . '</button></p>';
echo '</pre></form>';
