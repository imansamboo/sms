<?php

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

use Illuminate\Database\Capsule\Manager as Capsule;

if (isset($_REQUEST['save']) && $_REQUEST['save']) {
    $i = 0;
    $query = mysql_query("SELECT * FROM `mod_servermonitoring_services` WHERE `serviceid`='" . mysql_real_escape_string($_REQUEST['id']) . "' AND `uid`='" . mysql_real_escape_string($_SESSION['uid']) . "' LIMIT 1");
    $result = mysql_fetch_assoc($query);
    if ($_POST['weeklyreport'] == "on")
        $weeklyreport = "on";
    else
        $weeklyreport = "";
    $query = mysql_query("UPDATE `mod_servermonitoring_monitors` SET `weeklyreport`='" . $weeklyreport . "', `emailinterval`='" . $_POST['emailinterval'] . "' WHERE `serviceid`='" . $result['id'] . "'");
    $emaillimit = mysql_real_escape_string($_REQUEST['emaillimit']);
    $contacts = serialize($contacts);
    $query = mysql_query("UPDATE `mod_servermonitoring_services` SET `emaillimit`='" . $emaillimit . "' WHERE `serviceid`='" . mysql_real_escape_string($_REQUEST['id']) . "' AND `uid`='" . mysql_real_escape_string($_SESSION['uid']) . "'");
    redir('m=servermonitoring&id=' . $_REQUEST['id']);
}

$query = mysql_query("SELECT * FROM `mod_servermonitoring_services` WHERE `serviceid`='" . mysql_real_escape_string($_REQUEST['id']) . "' AND `uid`='" . mysql_real_escape_string($_SESSION['uid']) . "' LIMIT 1");
$result = mysql_fetch_assoc($query);

$output .= '<form action="index.php?m=servermonitoring&id=' . $_REQUEST['id'] . '&emailsettings=true&save=true" method="post"><center><div style="width:50%">';

$output .= '<p><select size="1" name="emailinterval" class="form-control">';

$querya = mysql_query("SELECT * FROM `tblhosting` WHERE `id`='" . mysql_real_escape_string($_REQUEST['id']) . "' AND `userid`='" . $_SESSION['uid'] . "'");
$hosting = mysql_fetch_assoc($querya);
$queryb = mysql_query("SELECT * FROM `tblproducts` WHERE `id`='" . $hosting['packageid'] . "'");
$products = mysql_fetch_assoc($queryb);
if (Capsule::schema()->hasTable('tblmodule_configuration')) {
    $checkisaddon = Capsule::table('tblhostingaddons')->where('hostingid', mysql_real_escape_string($_REQUEST['id']))->join('tbladdons', 'tblhostingaddons.addonid', '=', 'tbladdons.id')->where('module', 'serverMonitoring')->select('addonid')->first();
    if (count($checkisaddon) > 0) {
        $plist = Capsule::table('tblmodule_configuration')->where('entity_type', 'addon')->where('entity_id', $checkisaddon->addonid)->get();
        if (count($plist) > 0) {
            foreach ($plist as $value) {
                $products[$value->setting_name] = $value->value;
            }
        }
    }
}
$interval = explode(" ", $products['configoption1']);
$interval = $interval[0];

for ($i = 1; $i <= 30; $i++) {
    $intervala = $interval * $i;
    $queryc = mysql_query("SELECT * FROM `mod_servermonitoring_monitors` WHERE `serviceid`='" . $result['id'] . "'");
    $monitor = mysql_fetch_assoc($queryc);
    if ($monitor['weeklyreport'] == 'on')
        $weeklyreportchecked = " checked";
    if ($monitor['emailinterval'] == ($intervala * 60))
        $select = ' selected';
    else
        $select = '';
    $output .= '<option value="' . ($intervala * 60) . '"' . $select . '>' . $LANG['emailsevery'] . ' ' . $intervala . ' ' . $LANG['minutes'] . '</option>';
}

$output .= '</select><p>';

$output .= '<select size="1" name="emaillimit" class="form-control"><option value="0"';
if ($result['emaillimit'] == 0)
    $output .= ' selected';
$output .= '>' . $LANG['send'] . " " . $LANG['unlimited'] . " " . $LANG['emailsforeachdowntime'] . '</option><option value="1"';
if ($result['emaillimit'] == 1)
    $output .= ' selected';
$output .= '>' . $LANG['sendupto'] . " 1 " . $LANG['emailsforeachdowntime'] . '</option><option value="2"';
if ($result['emaillimit'] == 2)
    $output .= ' selected';
$output .= '>' . $LANG['sendupto'] . " 2 " . $LANG['emailsforeachdowntime'] . '</option><option value="3"';
if ($result['emaillimit'] == 3)
    $output .= ' selected';
$output .= '>' . $LANG['sendupto'] . " 3 " . $LANG['emailsforeachdowntime'] . '</option><option value="4"';
if ($result['emaillimit'] == 4)
    $output .= ' selected';
$output .= '>' . $LANG['sendupto'] . " 4 " . $LANG['emailsforeachdowntime'] . '</option><option value="5"';
if ($result['emaillimit'] == 5)
    $output .= ' selected';
$output .= '>' . $LANG['sendupto'] . " 5 " . $LANG['emailsforeachdowntime'] . '</option><option value="6"';
if ($result['emaillimit'] == 6)
    $output .= ' selected';
$output .= '>' . $LANG['sendupto'] . " 6 " . $LANG['emailsforeachdowntime'] . '</option><option value="7"';
if ($result['emaillimit'] == 7)
    $output .= ' selected';
$output .= '>' . $LANG['sendupto'] . " 7 " . $LANG['emailsforeachdowntime'] . '</option><option value="8"';
if ($result['emaillimit'] == 8)
    $output .= ' selected';
$output .= '>' . $LANG['sendupto'] . " 8 " . $LANG['emailsforeachdowntime'] . '</option><option value="9"';
if ($result['emaillimit'] == 9)
    $output .= ' selected';
$output .= '>' . $LANG['sendupto'] . " 9 " . $LANG['emailsforeachdowntime'] . '</option><option value="10"';
if ($result['emaillimit'] == 10)
    $output .= ' selected';
$output .= '>' . $LANG['sendupto'] . " 10 " . $LANG['emailsforeachdowntime'] . '</option></select>';

$output .= '<span><strong>' . $LANG['weeklyreport'] . '</strong></span><p><input type="checkbox" id="weeklyreport" name="weeklyreport" value="on"' . $weeklyreportchecked . '>&nbsp;&nbsp;<label for="weeklyreport" style="font-weight:normal;">' . $LANG['yes'] . '</label>';
$output .= '<p><button type="submit" name="submit" class="btn btn-success">' . $LANG['savechanges'] . '</button>&nbsp;&nbsp;<a href="index.php?m=servermonitoring&id=' . $_REQUEST['id'] . '"><button type="button" class="btn btn-danger">' . $LANG['goback'] . '</button></a></p>';
$output .= '</div></center></form>';
