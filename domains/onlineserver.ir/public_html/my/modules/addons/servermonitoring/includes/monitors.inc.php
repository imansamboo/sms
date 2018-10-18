<?php

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");
use Illuminate\Database\Capsule\Manager as Capsule;
if (isset($_REQUEST['action']) && $_REQUEST['action'] == "delete" && isset($_REQUEST['id']) && is_numeric($_REQUEST['id'])) {
    $mid = mysql_real_escape_string($_REQUEST['id']);
    $query = mysql_query("DELETE FROM `mod_servermonitoring_response` WHERE `res_server_id` ='" . $mid . "'");
    $query = mysql_query("DELETE FROM `mod_servermonitoring_stat` WHERE `mid` ='" . $mid . "'");
    $query = mysql_query("DELETE FROM `mod_servermonitoring_status` WHERE `mid` ='" . $mid . "'");
    $query = mysql_query("DELETE FROM `mod_servermonitoring_monitors` WHERE `id`='" . $mid . "'");
    redir("module=servermonitoring&success=delete");
    exit;
}

if (isset($_REQUEST['disable']) && isset($_REQUEST['id']) && is_numeric($_REQUEST['id'])) {
    $id = mysql_real_escape_string($_REQUEST['id']);
    $query = mysql_query("UPDATE `mod_servermonitoring_monitors` SET `status`='Disabled' WHERE `id`='" . $id . "'");
    redir("module=servermonitoring&success=true");
    exit;
}
if (isset($_REQUEST['enable']) && isset($_REQUEST['id']) && is_numeric($_REQUEST['id'])) {
    $id = mysql_real_escape_string($_REQUEST['id']);
    $query = mysql_query("UPDATE `mod_servermonitoring_monitors` SET `status`='Active' WHERE `id`='" . $id . "'");
    redir("module=servermonitoring&success=true");
    exit;
}

echo '<h2 align="center" style="color:grey;"><strong>' . $LANG['monitors'] . '</strong></h2>';

$aInt->sortableTableInit("id");
$numrows = get_query_val("mod_servermonitoring_monitors", "COUNT(*)", "", "id", "ASC");
$limit = "50";
if(!isset($_REQUEST['page']))
	$_REQUEST['page']='';
$page = $_REQUEST['page'];
if (empty($page) || !is_numeric($page))
    $page = 0;
else
    $page = $page;
$page = "" . $page . "";
$tabledata = array();
$result = select_query("mod_servermonitoring_monitors", "", "", "id", "ASC", $page * $limit . "," . $limit);
while ($data = mysql_fetch_assoc($result)) {
    $serviceid = $data['serviceid'];

    $querya = mysql_query("SELECT `uid`,`serviceid` FROM `mod_servermonitoring_services` WHERE `id`='" . $serviceid . "'");
    $servicedata = mysql_fetch_assoc($querya);
    $userid = $servicedata['uid'];
    $serviceid = $servicedata['serviceid'];

    $querya = mysql_query("SELECT `firstname`,`lastname`,`companyname` FROM `tblclients` WHERE `id`='" . $userid . "'");
    $clientdata = mysql_fetch_array($querya);
    $clientname = '<a href="clientssummary.php?userid=' . $userid . '">' . ucwords($clientdata['firstname'] . " " . $clientdata['lastname'] . (($clientdata['companyname'] != '') ? ' -- ' . $clientdata['companyname'] : '' )) . "</a>";

    $querya = mysql_query("SELECT `packageid` FROM `tblhosting` WHERE `id`='" . $serviceid . "'");
    $hostingdata = mysql_fetch_array($querya);

    $querya = mysql_query("SELECT `name`,`configoption1` FROM `tblproducts` WHERE `id`='" . $hostingdata['packageid'] . "'");
    $productdata = mysql_fetch_array($querya);
    if (Capsule::schema()->hasTable('tblmodule_configuration')) {
        $checkisaddon = Capsule::table('tblhostingaddons')->where('hostingid', $serviceid)->join('tbladdons', 'tblhostingaddons.addonid', '=', 'tbladdons.id')->where('module', 'serverMonitoring')->select('addonid')->first();
        $plist = Capsule::table('tblmodule_configuration')->where('entity_type', 'addon')->where('entity_id', $checkisaddon->addonid)->get();
        if (count($plist) > 0) {
            foreach ($plist as $value) {
                $productdata[$value->setting_name] = $value->value;
            }
        }        
        if (count($checkisaddon) > 0) {
            $pname = Capsule::table('tbladdons')->where('id', $checkisaddon->addonid)->select('name')->first();
            $productdata['name'] = $pname->name;
        }
    }    
    $servicename = '<a href="clientsservices.php?userid=' . $userid . '&id=' . $serviceid . '">' . $productdata['name'] . '</a>';
    $pinterval = trim(preg_replace("/[^0-9]/", "", $productdata['configoption1']));

    if ($data['custom_interval'] != 0 && $data['custom_interval'] > $pinterval)
        $interval = $data['custom_interval'];
    else
        $interval = $pinterval;
    if ($interval == 1)
        $interval = $interval . ' ' . $LANG['minute'];
    else
        $interval = $interval . ' ' . $LANG['minutes'];

    if ($data['online'] == 0)
        $monitorstatus = '<font color="#FF0000">' . $LANG['down'] . '</font>';
    else
        $monitorstatus = '<font color="green">' . $LANG['up'] . '</font>';
    if ($data['type'] == 'blacklist' && $data['blacklisted'])
        $monitorstatus = '<font color="#FF0000">' . $LANG['blacklisted'] . '</font>';
    elseif ($data['type'] == 'blacklist')
        $monitorstatus = '<font color="green">' . $LANG['notblacklisted'] . '</font>';

    if ($data['url'] != '' && $data['type'] == 'standard') {
        if (!filter_var($data['url'], FILTER_VALIDATE_URL) === false) {
            $url = '<a target="_new" href="' . $data['url'] . '">' . $data['url'] . '</a>';            
        } else {
            $url = '<a target="_new" href="http://' . $data['url'] . '">' . $data['url'] . '</a>';
        }
    } elseif ($data['type'] == "solusvm")
        $url = $data['monitorname'] . ' <i>(' . $LANG['solusvm'] . ')</i>';
    elseif ($data['type'] == "blacklist")
        $url = $data['url'] . ' <i>(' . $LANG['blacklist'] . ')</i>';
    if ($data['keyword'] != '')
        $port = $data['keyword'];
    else
    if ($data['port'] != 0)
        $port = '<a target="_new" href="' . $data['url'] . ':' . $data['port'] . '">' . $data['port'] . '</a>';
    else
        $port = "-";

    $servicestatus = strtolower($data['status']);
    if ($servicestatus == "disabled")
        $servicestatus = '<a href="addonmodules.php?module=servermonitoring&enable=true&id=' . $data['id'] . '">' . $LANG['disabled'] . '</a>';
    elseif ($servicestatus == "active")
        $servicestatus = '<a href="addonmodules.php?module=servermonitoring&disable=true&id=' . $data['id'] . '">' . $LANG['active'] . '</a>';
    else
        $servicestatus = $LANG[$servicestatus];

    $tabledata[] = array("<center>#" . $data['id'] . "</center>", "<center>" . $clientname . "</center>", "<center>" . $servicename . "</center>", "<center>" . $interval . "</cemter>", '<center>' . $url . '</center>', '<center>' . $port . '</center>', "<center>" . $monitorstatus . "</center>", "<center>" . $servicestatus . "</center>", '<a target="_blank" href="../public-charts.php?mid=' . $data['accesskey'] . '"><center><img src="images/info.gif"></center></a>', '<a href="' . $modulelink . '&a=editmonitor&id=' . $data['id'] . '&type=' . $data['type'] . '"><center><img src="images/edit.gif"></center></a>', '<a href="' . $modulelink . '&action=delete&id=' . $data['id'] . '" onclick="return confirm(\'' . $LANG['deletemonitorwarn'] . '\');"><center><img src="images/delete.gif"></center></a>');
}

echo $aInt->sortableTable(array($LANG['id'], $LANG['clientname'], $LANG['servicename'], $LANG['interval'], $LANG['urlip'], $LANG['PortORKeyword'], $LANG['monitorstatus'], $LANG['status'], $LANG['viewchart'], $LANG['editmonitor'], $LANG['deletemonitor']), $tabledata);
?>