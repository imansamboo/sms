<?php
if (!defined("WHMCS"))
    die("This file cannot be accessed directly");
use Illuminate\Database\Capsule\Manager as Capsule;
$querys = mysql_query("SELECT `id`,`serviceid` FROM `mod_servermonitoring_services` WHERE `serviceid`='" . mysql_real_escape_string($_REQUEST['id']) . "' AND `uid`='" . mysql_real_escape_string($_SESSION['uid']) . "' LIMIT 1");
$services = mysql_fetch_assoc($querys);

$queryb = mysql_query("SELECT `packageid` FROM `tblhosting` WHERE `id`='" . $services['serviceid'] . "' AND `userid`='" . $_SESSION['uid'] . "'");
$hosting = mysql_fetch_assoc($queryb);
$queryc = mysql_query("SELECT `configoption6` FROM `tblproducts` WHERE `id`='" . $hosting['packageid'] . "'");
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
if ($products['configoption6'] != "on") {
    redir("m=servermonitoring&id=" . $_REQUEST['id']);
    exit;
}

if (isset($_REQUEST['save']) && $_REQUEST['save']) {
    unset($_POST['token']);
    foreach ($_POST AS $key => $value) {
        $query = mysql_query("SELECT * FROM `mod_servermonitoring_ports` WHERE `id`='" . mysql_real_escape_string($key) . "' AND `serviceid`='" . mysql_real_escape_string($services['id']) . "' AND `uid`='" . mysql_real_escape_string($_SESSION['uid']) . "' LIMIT 1");
        $total = mysql_num_rows($query);
        if ($total == 1) {
            if (trim($value) == '') {
                $querya = mysql_query("DELETE FROM `mod_servermonitoring_ports` WHERE `serviceid`='" . mysql_real_escape_string($services['id']) . "' AND `uid`='" . mysql_real_escape_string($_SESSION['uid']) . "' AND `id`='" . mysql_real_escape_string($key) . "'");
            } else {
                $querya = mysql_query("UPDATE `mod_servermonitoring_ports` SET `port`='" . $value . "' WHERE `serviceid`='" . mysql_real_escape_string($services['id']) . "' AND `uid`='" . mysql_real_escape_string($_SESSION['uid']) . "' AND `id`='" . mysql_real_escape_string($key) . "'");
            }
        } else {
            if (trim($value) == '')
                continue;
            $querya = mysql_query("INSERT INTO `mod_servermonitoring_ports` SET `port`='" . $value . "', `desc`='" . $LANG['customport'] . "',`serviceid`='" . mysql_real_escape_string($services['id']) . "', `uid`='" . mysql_real_escape_string($_SESSION['uid']) . "'");
        }
    }
    redir('m=servermonitoring&id=' . $_REQUEST['id'] . '&customport=true');
}

$output .= '<form action="index.php?m=servermonitoring&id=' . $_REQUEST['id'] . '&customport=true&save=true" method="post"><center><div style="width:30%">';
$nextid = '0';
$query = mysql_query("SELECT * FROM `mod_servermonitoring_ports` WHERE `serviceid`='" . mysql_real_escape_string($services['id']) . "' AND `uid`='" . mysql_real_escape_string($_SESSION['uid']) . "' ORDER BY `id` ASC");
while ($result = mysql_fetch_assoc($query)) {
    $phonenumber = explode("|", $value);
    $output .= '<p><input type="text" value="' . $result['port'] . '" name="' . $result['id'] . '" placeholder="' . $LANG['port'] . '" class="form-control"></p></pre>';
    $nextid = $result['id'] + 1;
}
$output .= '<p><input type="text" value="" name="' . $nextid . '" placeholder="' . $LANG['port'] . '" class="form-control"></p></pre>';
$output .= '<p><button type="submit" class="btn btn-success">' . $LANG['savechanges'] . '</button>&nbsp;&nbsp;<a href="index.php?m=servermonitoring&id=' . $_REQUEST['id'] . '"><button type="button" class="btn btn-danger">' . $LANG['goback'] . '</button></a></p>';
$output .= '</div></center></form>';
