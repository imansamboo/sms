<?php

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

use Illuminate\Database\Capsule\Manager as Capsule;

$id = mysql_real_escape_string($_REQUEST['id']);

if (isset($_REQUEST['save']) && $_REQUEST['save'] && isset($_REQUEST['port']) && isset($_REQUEST['monitorname']) && isset($_REQUEST['url']) && is_numeric($_REQUEST['port'])) {
    $id = mysql_real_escape_string($_REQUEST['id']);
    $port = mysql_real_escape_string($_REQUEST['port']);
    $url = mysql_real_escape_string($_REQUEST['url']);
    $monitorname = mysql_real_escape_string($_REQUEST['monitorname']);
    $monitor_type = mysql_real_escape_string($_REQUEST['m_type']);
    $status = mysql_real_escape_string($_REQUEST['status']);
    $monitor_type = mysql_real_escape_string($_POST['m_type']);
    $keyword = '';
    $k_username = '';
    $k_password = '';
    if ($monitor_type == 'keyword') {
        $keyword = mysql_real_escape_string($_POST['Keyword']);
        $k_username = mysql_real_escape_string($_POST['k_username']);
        $k_password = mysql_real_escape_string($_POST['k_password']);
    }
    $custom_interval = mysql_real_escape_string($_REQUEST['custom_interval']);
    $query = mysql_query("UPDATE `mod_servermonitoring_monitors` SET `custom_interval`='" . $custom_interval . "',`keyword`='" . $keyword . "', `k_username`='" . $k_username . "', `k_password`='" . $k_password . "',`status`='" . $status . "', `monitorname`='" . $monitorname . "', `port`='" . $port . "', `url`='" . $url . "' WHERE `type`='standard' AND `id`='" . $id . "'");
    redir("module=servermonitoring&success=edit");
    exit;
}

$query = mysql_query("SELECT `url`,`port`,`monitorname`,`serviceid`,`keyword`,`status`,`custom_interval` FROM `mod_servermonitoring_monitors` WHERE `type`='standard' AND `id`='" . $id . "' LIMIT 1");
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
if (empty($_POST['port']))
    $port = $result['port'];
else
    $port = $_POST['port'];
if (empty($_POST['url']))
    $url = $result['url'];
else
    $url = $_POST['url'];
echo '<form action="' . $modulelink . '&a=editmonitor&save=true&id=' . $_REQUEST['id'] . '&type=standard" method="post">'
 . '<pre style="text-align:center;">';
echo '<center><h2>' . $LANG['editmonitor'] . '</h2>'
 . '<div style="width:50%;">'
 . '<select size="1" name="status" class="form-control" style="margin-bottom:10px;"><option value="Active" ' . (($result['status'] == 'Active') ? 'selected' : '') . '>' . $LANG['active'] . '</option><option value="Blacklisted" ' . (($result['status'] != 'Active') ? 'selected' : '') . '>' . $LANG['suspended'] . '</option></select>'
 . '<select size="1" name="m_type" class="form-control" style="margin-bottom:10px;"><option value="port" ' . (($result['keyword'] == '') ? 'selected' : '') . '>port</option><option value="keyword" ' . (($result['keyword'] != '') ? 'selected' : '') . '>keyword</option></select>'
 . '<input type="text" class="form-control" name="monitorname" value="' . $monitorname . '" placeholder="' . $LANG['monitorname'] . '" style="margin-bottom:10px;">'
 . '<input type="text" class="form-control" name="url" value="' . $url . '" placeholder="' . $LANG['url'] . '" style="margin-bottom:10px;">'
 . '<input type="text" class="form-control" name="port" value="' . $port . '" placeholder="' . $LANG['port'] . '" style="margin-bottom:10px;">';
echo '<input type="text" class="form-control KeywordTxtBox" name="Keyword" value="' . $result['keyword'] . '" placeholder="' . $LANG['keywordfind'] . '" style="margin-bottom:10px;">';
echo '<input type="text" class="form-control KeywordTxtBox" name="k_username" value="' . $result['k_username'] . '" placeholder="' . $LANG['optionalkeyword'] . '" style="margin-bottom:10px;">';
echo '<input type="text" class="form-control KeywordTxtBox" name="k_password" value="' . $result['k_password'] . '" placeholder="' . $LANG['optionalkeyword'] . '" style="margin-bottom:10px;">';

echo '<select size="1" name="custom_interval" class="form-control">' . $intdropdown . '</select>';
echo '</div></center>';
echo '<br><p align="center"><button class="btn btn-success" type="submit" style="margin-top: 10px;">' . $LANG['editmonitor'] . '</button></p>';
echo '</pre></form>' .
 '<script>
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
