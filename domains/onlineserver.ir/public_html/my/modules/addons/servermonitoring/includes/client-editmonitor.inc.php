<?php

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

use Illuminate\Database\Capsule\Manager as Capsule;

$querye = mysql_query("SELECT * FROM `mod_servermonitoring_services` WHERE `uid`='" . $_SESSION['uid'] . "' AND `serviceid`='" . mysql_real_escape_string($_REQUEST['id']) . "'");
$services = mysql_fetch_assoc($querye);
$querya = mysql_query("SELECT * FROM `tblhosting` WHERE `id`='" . $services['serviceid'] . "' AND `userid`='" . $_SESSION['uid'] . "'");
$hosting = mysql_fetch_assoc($querya);
$queryd = mysql_query("SELECT `configoption10`,`configoption9` FROM `tblproducts` WHERE `id`='" . $hosting['packageid'] . "'");
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
if (isset($_REQUEST['disable']) && $_REQUEST['disable']) {
    $mid = mysql_real_escape_string($_REQUEST['mid']);
    $query = mysql_query("UPDATE `mod_servermonitoring_monitors` SET `status`='Disabled' WHERE type='standard' AND `id`='" . $mid . "'");
    redir("m=servermonitoring&id=" . $_REQUEST['id'] . "&mid=" . $mid . "&edit=true&type=standard&success=disabled");
}
if (isset($_REQUEST['enable']) && $_REQUEST['enable']) {
    $mid = mysql_real_escape_string($_REQUEST['mid']);
    $query = mysql_query("UPDATE `mod_servermonitoring_monitors` SET `status`='Active' WHERE `type`='standard' AND `id`='" . $mid . "'");
    redir("m=servermonitoring&id=" . $_REQUEST['id'] . "&mid=" . $mid . "&edit=true&type=standard&success=enabled");
}
if (isset($_REQUEST['delete']) && $_REQUEST['delete']) {
    $mid = mysql_real_escape_string($_REQUEST['mid']);
    $query = mysql_query("DELETE FROM `mod_servermonitoring_response` WHERE `res_server_id` ='" . $mid . "'");
    $query = mysql_query("DELETE FROM `mod_servermonitoring_stat` WHERE `mid` ='" . $mid . "'");
    $query = mysql_query("DELETE FROM `mod_servermonitoring_status` WHERE `mid` ='" . $mid . "'");
    $query = mysql_query("DELETE FROM `mod_servermonitoring_monitors` WHERE `type`='standard' AND `id`='" . $mid . "'");
    redir("m=servermonitoring&id=" . $_REQUEST['id'] . "&success=deleted");
}

if (isset($_POST['url']) && isset($_POST['port']) && isset($_POST['location']) && isset($_REQUEST['mid']) && isset($_REQUEST['id']) && is_numeric($_REQUEST['mid']) && is_numeric($_REQUEST['id'])) {
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
    $query = mysql_query("SELECT * FROM `mod_servermonitoring_services` WHERE `serviceid`='" . mysql_real_escape_string($_REQUEST['id']) . "'");
    $services = mysql_fetch_assoc($query);
    $querya = mysql_query("SELECT * FROM `mod_servermonitoring_monitors` WHERE `type`='standard' AND `serviceid`='" . $services['id'] . "' AND `id`='" . mysql_real_escape_string($_REQUEST['mid']) . "'");
    $monitor = mysql_fetch_assoc($querya);
    if (!empty($monitor['id']) && $monitor['type'] == "standard") {
        if ($_POST['url'] != $monitor['url'] || $_POST['port'] != $monitor['port'] || $keyword != $monitor['keyword'])
            $clearstats = true;
        else
            $clearstats = false;
        if ($clearstats) {
            $query = mysql_query("DELETE FROM `mod_servermonitoring_response` WHERE `res_server_id` ='" . $monitor['id'] . "'");
            $query = mysql_query("DELETE FROM `mod_servermonitoring_stat` WHERE `mid` ='" . $monitor['id'] . "'");
            $query = mysql_query("DELETE FROM `mod_servermonitoring_status` WHERE `mid` ='" . $monitor['id'] . "'");
            $query = mysql_query("UPDATE `mod_servermonitoring_monitors` SET `url`='" . $url . "', `smssent`='0', `emailssent`='0', `port`='" . $port . "',`totaldowntime`='', `downtime`='0', `lastmonitor`='0', `lastsms`='0', `lastemail`='0' WHERE `id`='" . $monitor['id'] . "'");
        }
        $query = mysql_query("UPDATE `mod_servermonitoring_monitors` SET `custom_interval`='" . $custom_interval . "', `keyword`='" . $keyword . "', `k_username`='" . $k_username . "', `k_password`='" . $k_password . "', `monitorname`='" . $monitorname . "', `location`='" . $location . "' WHERE `id`='" . $monitor['id'] . "'");
        redir('m=servermonitoring&id=' . $_REQUEST['id'] . '&success=true');
    }
}

$query = mysql_query("SELECT * FROM `mod_servermonitoring_services` WHERE `serviceid`='" . mysql_real_escape_string($_REQUEST['id']) . "'");
$services = mysql_fetch_assoc($query);
$querya = mysql_query("SELECT * FROM `mod_servermonitoring_monitors` WHERE `type`='standard' AND `serviceid`='" . $services['id'] . "' AND `id`='" . mysql_real_escape_string($_REQUEST['mid']) . "'");
$monitor = mysql_fetch_assoc($querya);
$total = mysql_num_rows($querya);

if ($total == 1) {
    $output .= '<pre><p align="center" style="margin-bottom:0px;">';
    $settings = servermonitoring_settings($vars);
    if ($products['configoption10'] == 'on')
        $output .= '<a href="index.php?m=servermonitoring&id=' . $_REQUEST['id'] . '&mid=' . $_REQUEST['mid'] . '&maintenance=true"><button type="button" class="btn btn-info">' . $LANG['maintenanceschedule'] . '</button></a>&nbsp;&nbsp;';
    if ($monitor['status'] == "Active") {
        $output .= '<a href="index.php?m=servermonitoring&id=' . $_REQUEST['id'] . '&mid=' . $_REQUEST['mid'] . '&edit=true&type=standard&disable=true"><button type="button" class="btn btn-warning">' . $LANG['disablemonitor'] . '</button></a>&nbsp;&nbsp;';
    } else {
        $output .= '<a href="index.php?m=servermonitoring&id=' . $_REQUEST['id'] . '&mid=' . $_REQUEST['mid'] . '&edit=true&type=standard&enable=true"><button type="button" class="btn btn-success">' . $LANG['enablemonitor'] . '</button></a>&nbsp;&nbsp;';
    }

    $output .= '<a href="index.php?m=servermonitoring&id=' . $_REQUEST['id'] . '&mid=' . $_REQUEST['mid'] . '&edit=true&type=standard&delete=true"><button class="btn btn-danger" type="button" onclick="return confirm(\'' . $LANG['deletemonitorwarn'] . '\');">' . $LANG['deletemonitor'] . '</button></a>';
    $output .= '</p></pre>';

    $output .= '<center><div style="width:50%"><form action="index.php?m=servermonitoring&id=' . $_REQUEST['id'] . '&mid=' . $_REQUEST['mid'] . '&edit=true&type=standard&save=true" method="post">';

    $ports = servermonitoring_ports($_SESSION['uid'], $services['id']);
    $ports = explode(',', $ports);
    $poutput = "";
    $turl = (filter_var($monitor['url'], FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED) === false ? false : true);
    foreach ($ports AS $key => $value) {
        $explodeports = explode("|", $value);
        $selected = "";
        if ($monitor['port'] == $explodeports[1] && strpos(strtolower($explodeports[0]), 'url') === false && $turl == false)
            $selected = " selected";
        elseif ($monitor['port'] == $explodeports[1] && $turl == true)
            $selected = " selected";
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
    $queryd = mysql_query("SELECT `configoption1`,`configoption9` FROM `tblproducts` WHERE `id`='" . $hosting['packageid'] . "'");
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
    if (isset($_POST['url']) && !empty($_POST['url']))
        $url = $_POST['url'];
    else
        $url = $monitor['url'];
    if (isset($_POST['port']) && !empty($_POST['port']))
        $port = $_POST['port'];
    else
        $port = $monitor['port'];
    if (isset($_POST['monitorname']) && !empty($_POST['monitorname']))
        $monitorname = $_POST['monitorname'];
    else
        $monitorname = $monitor['monitorname'];
    if ($products['configoption9'] == 'on')
        $output .= '<span><strong>' . $LANG['monitortype'] . '</strong></span><p><select size="1" name="m_type" class="form-control"><option value="port" ' . (($monitor['keyword'] == '') ? 'selected' : '') . '>port</option><option value="keyword" ' . (($monitor['keyword'] != '') ? 'selected' : '') . '>keyword</option></select></p>';
    $output .= '<span><strong>' . $LANG['monitorname'] . '</strong></span><p><input type="text" name="monitorname" value="' . $monitorname . '" class="form-control" placeholder="' . $LANG['monitorname'] . '"></p>';
    $output .= '<span><strong>' . $LANG['monitoringlocation'] . '</strong></span><p><select size="1" name="location" class="form-control">' . $ldropdown . '</select></p>';
    $output .= '<span><strong>' . $LANG['urlip'] . '</strong></span><p><input type="text" name="url" value="' . $url . '" class="form-control" placeholder="' . $LANG['urlip'] . '"></p>';
    $output .= '<span><strong>' . $LANG['port'] . '</strong></span><p><select size="1" name="port" class="form-control">' . $poutput . '</select></p>';
    if ($products['configoption9'] == 'on') {
        $output .= '<span class="KeywordTxtBox"><strong>' . $LANG['monitorkeyword'] . '</strong></span><p class="KeywordTxtBox"><input type="text" name="Keyword" value="' . $monitor['keyword'] . '" class="form-control" placeholder="' . $LANG['keywordfind'] . '"></p>';
        $output .= '<span class="KeywordTxtBox"><strong>' . $LANG['monitoruser'] . '</strong></span><p class="KeywordTxtBox"><input type="text" name="k_username" value="' . $monitor['k_username'] . '" class="form-control" placeholder="' . $LANG['optionalkeyword'] . '"></p>';
        $output .= '<span class="KeywordTxtBox"><strong>' . $LANG['monitorpass'] . '</strong></span><p class="KeywordTxtBox"><input type="text" name="k_password" value="' . $monitor['k_password'] . '" class="form-control" placeholder="' . $LANG['optionalkeyword'] . '"></p>';
    }
    $output .= '<span><strong>' . $LANG['interval'] . '</strong></span><p><select size="1" name="custom_interval" class="form-control">' . $intdropdown . '</select></p>';
    $output .= '<p><button class="btn btn-success" type="submit" onclick="return confirm(\'' . $LANG['editmonitorwarn'] . '\');">' . $LANG['savechanges'] . '</button>&nbsp;&nbsp;<a href="index.php?m=servermonitoring&id=' . $_REQUEST['id'] . '"><button class="btn btn-danger" type="button">' . $LANG['goback'] . '</button></a></p></form></div></center>';
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
} else {
    redir('m=servermonitoring&id=' . $_REQUEST['id']);
}