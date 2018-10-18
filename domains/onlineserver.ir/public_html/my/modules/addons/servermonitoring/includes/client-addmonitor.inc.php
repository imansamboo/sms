<?php

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

use Illuminate\Database\Capsule\Manager as Capsule;

if (isset($_POST['url']) && isset($_POST['port']) && isset($_POST['location']) && isset($_REQUEST['id']) && is_numeric($_REQUEST['id']) && !empty($_POST['url']) && !empty($_POST['port'])) {
    $url = mysql_real_escape_string($_POST['url']);
    $port = mysql_real_escape_string($_POST['port']);
    $location = mysql_real_escape_string($_POST['location']);
    $monitorname = mysql_real_escape_string($_POST['monitorname']);
    $settings = servermonitoring_settings($vars);
    $k_username = '';
    $p_username = '';
    $monitor_type = mysql_real_escape_string($_POST['m_type']);
    $keyword = '';
    if ($monitor_type == 'keyword')
        $keyword = mysql_real_escape_string($_POST['Keyword']);
    $k_username = mysql_real_escape_string($_POST['k_username']);
    $k_password = mysql_real_escape_string($_POST['k_password']);
    $custom_interval = mysql_real_escape_string($_POST['custom_interval']);
    $query = mysql_query("SELECT * FROM `mod_servermonitoring_services` WHERE `serviceid`='" . mysql_real_escape_string($_REQUEST['id']) . "' AND `uid`='" . $_SESSION['uid'] . "'");
    $services = mysql_fetch_assoc($query);
    $tot = mysql_num_rows($query);
    $querya = mysql_query("SELECT `id` FROM `mod_servermonitoring_monitors` WHERE `serviceid`='" . $services['id'] . "'");
    $tota = mysql_num_rows($querya);
    $queryb = mysql_query("SELECT `packageid` FROM `tblhosting` WHERE `id`='" . $services['serviceid'] . "' AND `userid`='" . $_SESSION['uid'] . "'");
    $hosting = mysql_fetch_assoc($queryb);
    $queryc = mysql_query("SELECT `configoption2`,`configoption8` FROM `tblproducts` WHERE `id`='" . $hosting['packageid'] . "'");
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
    if ($tot == 1 && $tota < $products['configoption2'] && $products['configoption8'] == "on") {
        $query = mysql_query("INSERT INTO `mod_servermonitoring_monitors` SET `custom_interval`='" . $custom_interval . "',`k_username`='" . $k_username . "', `k_password`='" . $k_password . "',`keyword`='" . $keyword . "', `type`='standard', `status`='Active', `serviceid`='" . $services['id'] . "',`url`='" . $url . "', `port`='" . $port . "', `totaldowntime`='', `downtime`='0', `lastmonitor`='0', `lastsms`='0', `accesskey`='" . md5(time()) . "', `lastemail`='0', `smssent`='0', `emailssent`='0', `monitorname`='" . $monitorname . "', `location`='" . $location . "'");
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

$output .= '<center><div style="width:50%"><form action="index.php?m=servermonitoring&id=' . $_REQUEST['id'] . '&add=true&type=standard&save=true" method="post">';

$query = mysql_query("SELECT * FROM `mod_servermonitoring_services` WHERE `serviceid`='" . mysql_real_escape_string($_REQUEST['id']) . "' AND `uid`='" . $_SESSION['uid'] . "'");
$services = mysql_fetch_assoc($query);

$ports = servermonitoring_ports($_SESSION['uid'], $services['id']);
$ports = explode(',', $ports);
$poutput = "";
foreach ($ports AS $key => $value) {
    $explodeports = explode("|", $value);
    if ($explodeports[1] == "80")
        $selected = " selected";
    else
        $selected = '';
    if ($explodeports[0] == 'Keyword')
        $poutput .= '<option value="' . $explodeports[1] . '"' . $selected . '>' . $explodeports[0] . '</option>';
    else
        $poutput .= '<option value="' . $explodeports[1] . '"' . $selected . '>' . $explodeports[1] . ' (' . $explodeports[0] . ')</option>';
}
$SETTINGS = servermonitoring_settings($vars);
$localhostName = $SETTINGS['localhostName'];
$locations = servermonitoring_locations();
$ldropdown .= '<option value="0">' . $localhostName . '</option>';
foreach ($locations AS $key => $value) {
    $ldropdown .= '<option value="' . $key . '">' . $value['location'] . '</option>';
}

$querye = mysql_query("SELECT * FROM `mod_servermonitoring_services` WHERE `uid`='" . $_SESSION['uid'] . "' AND `serviceid`='" . mysql_real_escape_string($_REQUEST['id']) . "'");
$services = mysql_fetch_assoc($querye);
$querya = mysql_query("SELECT * FROM `tblhosting` WHERE `id`='" . $services['serviceid'] . "' AND `userid`='" . $_SESSION['uid'] . "'");
$hosting = mysql_fetch_assoc($querya);
$queryd = mysql_query("SELECT `configoption1`,`configoption9` FROM `tblproducts` WHERE `id`='" . $hosting['packageid'] . "'");
$products = mysql_fetch_assoc($queryd);
$pinterval = trim(preg_replace("/[^0-9]/", "", $products['configoption1']));

$intervals = servermonitoring_intervals($pinterval);
$intdropdown = '';
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
$settings = servermonitoring_settings($vars);
$url = '';
$port = '';
$monitorname = '';
if (isset($_POST['url']) && !empty($_POST['url']))
    $url = $_POST['url'];
if (isset($_POST['port']) && !empty($_POST['port']))
    $port = $_POST['port'];
if (isset($_POST['monitorname']) && !empty($_POST['monitorname']))
    $monitorname = $_POST['monitorname'];
if ($products['configoption9'] == 'on')
    $output .= '<span><strong>' . $LANG['monitortype'] . '</strong></span><p><select size="1" name="m_type" class="form-control"><option value="port">port</option><option value="keyword">keyword</option></select></p>';
$output .= '<span><strong>' . $LANG['monitorname'] . '</strong></span><p><input type="text" name="monitorname" value="' . $monitorname . '" class="form-control" placeholder="' . $LANG['monitorname'] . '"></p>';
$output .= '<span><strong>' . $LANG['monitoringlocation'] . '</strong></span><p><select size="1" name="location" class="form-control">' . $ldropdown . '</select></p>';
$output .= '<span><strong>' . $LANG['urlip'] . '</strong></span><p><input type="text" name="url" value="' . $url . '" class="form-control" placeholder="' . $LANG['urlip'] . '"></p>';
$output .= '<span><strong>' . $LANG['port'] . '</strong></span><p><select size="1" name="port" class="form-control">' . $poutput . '</select></p>';
if ($products['configoption9'] == 'on') {
    $output .= '<span class="KeywordTxtBox"><strong>' . $LANG['monitorkeyword'] . '</strong></span><p class="KeywordTxtBox"><input type="text" name="Keyword" value="" class="form-control" placeholder="' . $LANG['keywordfind'] . '"></p>';
    $output .= '<span class="KeywordTxtBox"><strong>' . $LANG['monitorpuser'] . '</strong></span><p class="KeywordTxtBox"><input type="text" name="k_username" value="" class="form-control" placeholder="' . $LANG['optionalkeyword'] . '"></p>';
    $output .= '<span class="KeywordTxtBox"><strong>' . $LANG['monitorpass'] . '</strong></span><p class="KeywordTxtBox"><input type="text" name="k_password" value="" class="form-control" placeholder="' . $LANG['optionalkeyword'] . '"></p>';
}
$output .= '<span><strong>' . $LANG['interval'] . '</strong></span><p><select size="1" name="custom_interval" class="form-control">' . $intdropdown . '</select></p>';
$output .= '<p><button class="btn btn-success" type="submit">' . $LANG['savechanges'] . '</button>&nbsp;&nbsp;<a href="index.php?m=servermonitoring&id=' . $_REQUEST['id'] . '"><button class="btn btn-danger" type="button">' . $LANG['goback'] . '</button></a></p></form></div></center>';
if ($products['configoption9'] == 'on') {
    $output .= '<script>
	$(function() {
		$(".KeywordTxtBox").hide();
		var
		jqDdl = $("select[name=m_type]"),
		onChange = function(event) {
			if ($(this).val() == "keyword") {
				$(".KeywordTxtBox").show(700);
				$(".KeywordTxtBox").focus().select();
			} else {
				$(".KeywordTxtBox").hide(700);
			}
		};
		onChange.apply(jqDdl.get(0)); // To show/hide the Other textbox initially
		jqDdl.change(onChange);
	});
    </script>';
}
