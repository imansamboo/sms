<?php

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");
use Illuminate\Database\Capsule\Manager as Capsule;
if (isset($_REQUEST['action']) && $_REQUEST['action'] == "terminate" && isset($_REQUEST['id']) && is_numeric($_REQUEST['id'])) {
    $id = mysql_real_escape_string($_REQUEST['id']);
    $query = mysql_query("SELECT `serviceid` FROM `mod_servermonitoring_services` WHERE `id`='" . $id . "'");
    $result = mysql_fetch_assoc($query);
    $query = mysql_query("UPDATE `tblhosting` SET `domainstatus`='Terminated' WHERE `id`='" . $result['serviceid'] . "'");
    $query = mysql_query("DELETE FROM `mod_servermonitoring_services` WHERE `id`='" . $id . "'");
    $query = mysql_query("DELETE FROM `mod_servermonitoring_monitors` WHERE `serviceid`='" . $id . "'");
    redir("module=servermonitoring&a=services&success=terminate");
    exit;
}

echo '<h2 align="center" style="color:grey;"><strong>' . $LANG['services'] . '</strong></h2>';

$aInt->sortableTableInit("id");
$numrows = get_query_val("mod_servermonitoring_services", "COUNT(*)", "", "id", "ASC");
$limit = "50";
$page = $_REQUEST['page'];
if (empty($page) || !is_numeric($page))
    $page = 0;
else
    $page = $page;
$page = "" . $page . "";
$tabledata = array();
$result = select_query("mod_servermonitoring_services", "", "", "id", "ASC", $page * $limit . "," . $limit);
while ($data = mysql_fetch_assoc($result)) {
    $serviceid = $data['serviceid'];
    $userid = $data['uid'];

    $querya = mysql_query("SELECT `firstname`,`lastname`,`companyname` FROM `tblclients` WHERE `id`='" . $userid . "'");
    $clientdata = mysql_fetch_array($querya);
    $clientname = '<a href="clientssummary.php?userid=' . $userid . '">' . ucwords($clientdata['firstname'] . " " . $clientdata['lastname']) . (($clientdata['companyname'] != '') ? ' -- ' . $clientdata['companyname'] : '' ) . "</a>";
    $querya = mysql_query("SELECT `packageid` FROM `tblhosting` WHERE `id`='" . $serviceid . "'");
    $servicedata = mysql_fetch_array($querya);
    $querya = mysql_query("SELECT `name` FROM `tblproducts` WHERE `id`='" . $servicedata['packageid'] . "'");
    $productdata = mysql_fetch_array($querya);
    if (Capsule::schema()->hasTable('tblmodule_configuration')) {
        $checkisaddon = Capsule::table('tblhostingaddons')->where('hostingid', $serviceid)->join('tbladdons', 'tblhostingaddons.addonid', '=', 'tbladdons.id')->where('module', 'serverMonitoring')->select('addonid')->first();
        if (count($checkisaddon) > 0) {
            $pname = Capsule::table('tbladdons')->where('id', $checkisaddon->addonid)->select('name')->first();
            $productdata['name'] = $pname->name;
        }
    }
    $servicename = '<a href="clientsservices.php?userid=' . $userid . '&id=' . $serviceid . '">' . $productdata['name'] . '</a>';
    $tabledata[] = array("<center>#" . $data['id'] . "</center>", "<center>" . $clientname . "</center>", "<center>" . $servicename . "</center>", "<center>" . ucfirst($data['status']) . "</center>", '<center><a href="addonmodules.php?module=servermonitoring&a=services&id=' . $data['id'] . '&editcredits=true">' . $data['smscredits'] . "</a></center>", '<a href="clientsservices.php?userid=' . $userid . '&id=' . $serviceid . '"><center><img src="images/edit.gif"></center></a>', '<a href="' . $modulelink . '&a=services&action=terminate&id=' . $data['id'] . '" onclick="return confirm(\'' . $LANG['terminateservicewarn'] . '\');"><center><img src="images/delete.gif"></center></a>');
}

echo $aInt->sortableTable(array($LANG['id'], $LANG['clientname'], $LANG['servicename'], $LANG['status'], $LANG['smscredits'], "", ""), $tabledata);
?>