<?php

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

use Illuminate\Database\Capsule\Manager as Capsule;
$querys = mysql_query("SELECT `id`,`serviceid` FROM `mod_servermonitoring_services` WHERE `serviceid`='" . mysql_real_escape_string($_REQUEST['id']) . "' AND `uid`='" . mysql_real_escape_string($_SESSION['uid']) . "' LIMIT 1");
$services = mysql_fetch_assoc($querys);
$queryb = mysql_query("SELECT `packageid` FROM `tblhosting` WHERE `id`='" . $services['serviceid'] . "' AND `userid`='" . $_SESSION['uid'] . "'");
$hosting = mysql_fetch_assoc($queryb);
$queryc = mysql_query("SELECT `configoption10` FROM `tblproducts` WHERE `id`='" . $hosting['packageid'] . "'");
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
        $pname = Capsule::table('tbladdons')->where('id', $checkisaddon->addonid)->select('name')->first();
        $products['name'] = $pname->name;
    }
}
if ($products['configoption10'] != "on") {
    redir("m=servermonitoring&id=" . $_REQUEST['id']);
    exit;
}

if (isset($_REQUEST['save']) && $_REQUEST['save'] && isset($_REQUEST['to']) && isset($_REQUEST['from']) && isset($_REQUEST['message'])) {
    $to = mysql_real_escape_string($_REQUEST['to']);
    $to = DateTime::createFromFormat('m-d-Y H:i', $to);
    $to = '' . $to->getTimestamp();
    $from = mysql_real_escape_string($_REQUEST['from']);
    $from = DateTime::createFromFormat('m-d-Y H:i', $from);
    $from = '' . $from->getTimestamp();
    $message = mysql_real_escape_string($_REQUEST['message']);
    $mid = mysql_real_escape_string($_REQUEST['mid']);
    $serviceid = mysql_real_escape_string($_REQUEST['id']);
    $query = mysql_query("SELECT `id` FROM `mod_servermonitoring_services` WHERE `serviceid`='" . $serviceid . "' AND `uid`='" . $_SESSION['uid'] . "'");
    $services = mysql_fetch_assoc($query);
    $querya = mysql_query("SELECT `id` FROM `mod_servermonitoring_monitors` WHERE `serviceid`='" . $services['id'] . "' AND `id`='" . $mid . "'");
    $tot = mysql_num_rows($querya);
    if ($tot == 1) {
        $query = mysql_query("INSERT INTO `mod_servermonitoring_maintenance` SET `serviceid`='" . $services['id'] . "', `mid`='" . $mid . "',`to`='" . $to . "', `from`='" . $from . "', `message`='" . $message . "', `status`='1'");
        header("Location: index.php?m=servermonitoring&id=" . $_REQUEST['id'] . "&mid=" . $_REQUEST['mid'] . "&maintenance=true&success=add");
        exit;
    }
}
$output .= '<link rel="stylesheet" href="modules/addons/servermonitoring/css/kendo.common.min.css">';
$output .= '<link rel="stylesheet" href="modules/addons/servermonitoring/css/kendo.default.min.css">';
$output .= '<script src="modules/addons/servermonitoring/js/kendo.all.min.js"></script>';

$output .= '<form action="' . $modulelink . '&id=' . $_REQUEST['id'] . '&mid=' . $_REQUEST['mid'] . '&addmaintenance=true&save=true" method="post"><pre style="text-align:center;">';
$output .= '<center><h2>' . $LANG['addmaintenance'] . '</h2><div style="width:50%;">';

$output .= '<p><input id="from" name="from" style="width: 100%;" /></p>';
$output .= '<p><input id="to" name="to" style="width: 100%;" /></p>';

$output .= '<p><input type="text" class="form-control" name="message" value="' . $_POST['message'] . '" placeholder="' . $LANG['message'] . '" style="margin-bottom:10px;"></p></div></center>';
$output .= '<br><p align="center"><button class="btn btn-success" type="submit">' . $LANG['addmaintenance'] . '</button>&nbsp;&nbsp;<a href="index.php?m=servermonitoring&id=' . $_REQUEST['id'] . '&mid=' . $_REQUEST['mid'] . '&maintenance=true"><button class="btn btn-danger" type="button">' . $LANG['goback'] . '</button></a></p>';
$output .= '</pre></form>';
$output .= '            <script>
                $(document).ready(function () {
                    $("#to").kendoDateTimePicker({
                        value:new Date(),
						format: "MM-dd-yyyy HH:mm"
                    });
                    $("#from").kendoDateTimePicker({
                        value:new Date(),
						format: "MM-dd-yyyy HH:mm"
                    });
                });
            </script>';
