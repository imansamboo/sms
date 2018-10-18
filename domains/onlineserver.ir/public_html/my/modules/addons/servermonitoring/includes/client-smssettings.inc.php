<?php

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

use Illuminate\Database\Capsule\Manager as Capsule;

$settings = servermonitoring_settings($vars);

if ($settings['allowSMS'] != 'on' && $settings['allowSMS'] != '1') {
    redir('m=servermonitoring&id=' . $_REQUEST['id'] . '');
}

if (isset($_REQUEST['save']) && $_REQUEST['save']) {
    $i = 0;
    $smslimit = mysql_real_escape_string($_REQUEST['smslimit']);
    $query = mysql_query("SELECT * FROM `mod_servermonitoring_services` WHERE `serviceid`='" . mysql_real_escape_string($_REQUEST['id']) . "' AND `uid`='" . mysql_real_escape_string($_SESSION['uid']) . "' LIMIT 1");
    $result = mysql_fetch_assoc($query);

    $query = mysql_query("UPDATE `mod_servermonitoring_monitors` SET `smsinterval`='" . $_POST['smsinterval'] . "' WHERE `serviceid`='" . $result['id'] . "'");
    unset($_POST['smsinterval']);
    unset($_POST['smslimit']);
    $contacts = array();
    foreach ($_POST AS $key => $value) {
        $value = str_replace(' ', '', $value);
        if (strpos($value, '+') !== false)
            ltrim($value, '+');
        if (strpos($key, 'countryCode') !== false)
            continue;
        if (is_numeric($value)) {
            $countryCode = 'countryCode' . $key;
            $value = $_POST[$countryCode] . '|' . mysql_real_escape_string($value);
            $contacts[$i] = $value;
            $i++;
        }
    }
    $contacts = serialize($contacts);
    $query = mysql_query("UPDATE `mod_servermonitoring_services` SET `smslimit`='" . $smslimit . "', `smsrecipient`='" . mysql_real_escape_string($contacts) . "' WHERE `serviceid`='" . mysql_real_escape_string($_REQUEST['id']) . "' AND `uid`='" . mysql_real_escape_string($_SESSION['uid']) . "'");
    redir('m=servermonitoring&id=' . $_REQUEST['id'] . '&smssettings=true');
}

$query = mysql_query("SELECT * FROM `mod_servermonitoring_services` WHERE `serviceid`='" . mysql_real_escape_string($_REQUEST['id']) . "' AND `uid`='" . mysql_real_escape_string($_SESSION['uid']) . "' LIMIT 1");
$result = mysql_fetch_assoc($query);

$smsrecipient = unserialize($result['smsrecipient']);

$output .= '<form action="index.php?m=servermonitoring&id=' . $_REQUEST['id'] . '&smssettings=true&save=true" method="post"><center><div style="width:40%">';

$output .= '<p><i class="fa fa-money" style="color:green;"></i>&nbsp;&nbsp;<strong>' . $LANG['smsbalance'] . '</strong>&nbsp;' . $result['smscredits'] . '</p>';

$output .= '<p><a href="index.php?m=servermonitoring&id=' . $_REQUEST['id'] . '&smscredits=order"><button type="button" class="btn btn-success"><i class="fa fa-money" style="color:white;"></i>&nbsp;&nbsp;' . $LANG['ordersmscredits'] . '</button></a></p>';

$output .= '<p><select size="1" name="smsinterval" class="form-control">';

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
        $pname = Capsule::table('tbladdons')->where('id', $checkisaddon->addonid)->select('name')->first();
        $products['name'] = $pname->name;
    }
}
$interval = explode(" ", $products['configoption1']);
$interval = $interval[0];

for ($i = 1; $i <= 30; $i++) {
    $intervala = $interval * $i;
    $queryc = mysql_query("SELECT * FROM `mod_servermonitoring_monitors` WHERE `serviceid`='" . $result['id'] . "'");
    $monitor = mysql_fetch_assoc($queryc);
    if ($monitor['smsinterval'] == ($intervala * 60))
        $select = ' selected';
    else
        $select = '';
    $output .= '<option value="' . ($intervala * 60) . '"' . $select . '>' . $LANG['smsevery'] . ' ' . $intervala . ' ' . $LANG['minutes'] . '</option>';
}

$output .= '</select><p>';

$output .= '<select size="1" name="smslimit" class="form-control"><option value="0"';
if ($result['smslimit'] == 0)
    $output .= ' selected';
$output .= '>' . $LANG['send'] . " " . $LANG['unlimited'] . " " . $LANG['smsforeachdowntime'] . '</option><option value="1"';
if ($result['smslimit'] == 1)
    $output .= ' selected';
$output .= '>' . $LANG['sendupto'] . " 1 " . $LANG['smsforeachdowntime'] . '</option><option value="2"';
if ($result['smslimit'] == 2)
    $output .= ' selected';
$output .= '>' . $LANG['sendupto'] . " 2 " . $LANG['smsforeachdowntime'] . '</option><option value="3"';
if ($result['smslimit'] == 3)
    $output .= ' selected';
$output .= '>' . $LANG['sendupto'] . " 3 " . $LANG['smsforeachdowntime'] . '</option><option value="4"';
if ($result['smslimit'] == 4)
    $output .= ' selected';
$output .= '>' . $LANG['sendupto'] . " 4 " . $LANG['smsforeachdowntime'] . '</option><option value="5"';
if ($result['smslimit'] == 5)
    $output .= ' selected';
$output .= '>' . $LANG['sendupto'] . " 5 " . $LANG['smsforeachdowntime'] . '</option><option value="6"';
if ($result['smslimit'] == 6)
    $output .= ' selected';
$output .= '>' . $LANG['sendupto'] . " 6 " . $LANG['smsforeachdowntime'] . '</option><option value="7"';
if ($result['smslimit'] == 7)
    $output .= ' selected';
$output .= '>' . $LANG['sendupto'] . " 7 " . $LANG['smsforeachdowntime'] . '</option><option value="8"';
if ($result['smslimit'] == 8)
    $output .= ' selected';
$output .= '>' . $LANG['sendupto'] . " 8 " . $LANG['smsforeachdowntime'] . '</option><option value="9"';
if ($result['smslimit'] == 9)
    $output .= ' selected';
$output .= '>' . $LANG['sendupto'] . " 9 " . $LANG['smsforeachdowntime'] . '</option><option value="10"';
if ($result['smslimit'] == 10)
    $output .= ' selected';
$output .= '>' . $LANG['sendupto'] . " 10 " . $LANG['smsforeachdowntime'] . '</option></select>';
$key = NULL;
foreach ($smsrecipient AS $key => $value) {
    $phonenumber = explode("|", $value);
    $output .= '<pre><p><select name="countryCode' . $key . '" size="1" class="form-control"><option value="" selected>کد بین المللی کشور مربوطه را انتخاب نمایید</option>' . servermonitoring_countryCodes($phonenumber[0]) . '</select></p>';
    $output .= '<p><input type="text" value="' . $phonenumber[1] . '" name="' . $key . '" placeholder="' . $LANG['phonenumber'] . '" class="form-control"></p></pre>';
}
$output .= '<pre><p><select name="countryCode' . ($key + 1) . '" size="1" class="form-control"><option value="" selected>کد بین المللی کشور مربوطه را انتخاب نمایید</option>' . servermonitoring_countryCodes() . '</select></p>';
$output .= '<p><input type="text" value="" name="' . ($key + 1) . '" placeholder="' . $LANG['phonenumber'] . '" class="form-control"></p></pre>';
$output .= '<p><button type="submit" name="submit" class="btn btn-success">' . $LANG['savechanges'] . '</button>&nbsp;&nbsp;<a href="index.php?m=servermonitoring&id=' . $_REQUEST['id'] . '"><button type="button" class="btn btn-danger">' . $LANG['goback'] . '</button></a></p>';
$output .= '</div></center></form>';
