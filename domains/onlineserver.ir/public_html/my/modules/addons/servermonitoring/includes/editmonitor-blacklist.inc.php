<?php

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

use Illuminate\Database\Capsule\Manager as Capsule;

$id = mysql_real_escape_string($_REQUEST['id']);

if (isset($_REQUEST['save']) && $_REQUEST['save'] && isset($_REQUEST['monitorname']) && isset($_REQUEST['url'])) {
    $id = mysql_real_escape_string($_REQUEST['id']);
    $url = mysql_real_escape_string($_REQUEST['url']);
    $monitorname = mysql_real_escape_string($_REQUEST['monitorname']);
    $custom_interval = mysql_real_escape_string($_POST['custom_interval']);
    $custom_interval = ($custom_interval * 60);
    $query = mysql_query("UPDATE `mod_servermonitoring_monitors` SET `monitorname`='" . $monitorname . "', `url`='" . $url . "', `custom_interval`='" . $custom_interval . "' WHERE `type`='blacklist' AND `id`='" . $id . "'");
    redir("module=servermonitoring&success=edit");
    exit;
}

$query = mysql_query("SELECT `url`,`monitorname`,`serviceid`,`custom_interval` FROM `mod_servermonitoring_monitors` WHERE `type`='blacklist' AND `id`='" . $id . "' LIMIT 1");
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

$intervals = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 12, 24, 36, 48, 60);
foreach ($intervals AS $value) {
    $select = '';
    if ($value == 1)
        $minute = $LANG['hour'];
    else
        $minute = $LANG['hours'];
    $t = ($value * 60);
    if ($t == $result['custom_interval'])
        $select = 'selected';
    $intdropdown .= '<option value="' . $value . '" ' . $select . '>' . $value . ' ' . $minute . '</option>';
}
if (empty($_POST['monitorname']))
    $monitorname = $result['monitorname'];
else
    $monitorname = $_POST['monitorname'];
if (empty($_POST['url']))
    $url = $result['url'];
else
    $url = $_POST['url'];
echo '<form action="' . $modulelink . '&a=editmonitor&save=true&id=' . $_REQUEST['id'] . '&type=blacklist" method="post"><pre style="text-align:center;">';
echo '<center><h2>' . $LANG['editmonitor'] . '</h2><div style="width:50%;"><input type="text" class="form-control" name="monitorname" value="' . $monitorname . '" placeholder="' . $LANG['monitorname'] . '" style="margin-bottom:10px;"><input type="text" class="form-control" name="url" value="' . $url . '" placeholder="' . $LANG['url'] . '" style="margin-bottom:10px;"><select class="form-control" size="1" style="margin-bottom:10px;" name="custom_interval" class="form-control">' . $intdropdown . '</select>';
//echo '<select size="1" name="custom_interval" class="form-control">'.$intdropdown.'</select>';
echo '</div></center>';
echo '<br><p align="center"><button class="btn btn-success" type="submit" style="margin-top: 10px;">' . $LANG['editmonitor'] . '</button></p>';
echo '</pre></form>';
