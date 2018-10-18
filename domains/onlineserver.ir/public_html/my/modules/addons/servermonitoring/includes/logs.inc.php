<?php

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

use Illuminate\Database\Capsule\Manager as Capsule;

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "delete" && isset($_REQUEST['id']) && is_numeric($_REQUEST['id'])) {
    $id = mysql_real_escape_string($_REQUEST['id']);
    $query = mysql_query("DELETE FROM `mod_servermonitoring_logs` WHERE `id`='" . $id . "'");
    redir("module=servermonitoring&a=logs&success=delete");
    exit;
}

if (isset($_REQUEST['clear']) && $_REQUEST['clear']) {
    $query = mysql_query("DELETE FROM `mod_servermonitoring_logs`");
    redir("module=servermonitoring&a=logs&success=clear");
    exit;
}

echo '<h2 align="center" style="color:grey;"><strong>' . $LANG['logs'] . '</strong></h2>';

echo '<p align="right"><a href="addonmodules.php?module=servermonitoring&a=logs&clear=true"><button class="btn btn-success" type="submit" onclick="return confirm(\'' . $LANG['clearlogwarn'] . '\');">' . $LANG['clearlog'] . '</button></a></p>';

$aInt->sortableTableInit("id");
$numrows = get_query_val("mod_servermonitoring_logs", "COUNT(*)", "", "id", "ASC");
$limit = "50";
$page = $_REQUEST['page'];
if (empty($page) || !is_numeric($page))
    $page = 0;
else
    $page = $page;
$page = "" . $page . "";

$result = select_query("mod_servermonitoring_logs", "", "", "id", "DESC", $page * $limit . "," . $limit);
while ($data = mysql_fetch_assoc($result)) {
    $serviceid = $data['serviceid'];

    $querya = mysql_query("SELECT `serviceid`,`uid` FROM `mod_servermonitoring_services` WHERE `id`='" . $serviceid . "'");
    $service = mysql_fetch_array($querya);
    $userid = $service['uid'];
    $queryb = mysql_query("SELECT `packageid` FROM `tblhosting` WHERE `id`='" . $service['serviceid'] . "'");
    $hosting = mysql_fetch_array($queryb);
    $queryc = mysql_query("SELECT `name` FROM `tblproducts` WHERE `id`='" . $hosting['packageid'] . "'");
    $product = mysql_fetch_array($queryc);
    if (Capsule::schema()->hasTable('tblmodule_configuration')) {
        $checkisaddon = Capsule::table('tblhostingaddons')->where('hostingid', $service['serviceid'])->join('tbladdons', 'tblhostingaddons.addonid', '=', 'tbladdons.id')->where('module', 'serverMonitoring')->select('addonid')->first();
        if (count($checkisaddon) > 0) {
            $pname = Capsule::table('tbladdons')->where('id', $checkisaddon->addonid)->select('name')->first();
            $product['name'] = $pname->name;
        }
    }
    $queryd = mysql_query("SELECT `firstname`,`lastname` FROM `tblclients` WHERE `id`='" . $service['uid'] . "'");
    $client = mysql_fetch_array($queryd);
    $clientname = '<a href="clientssummary.php?userid=' . $userid . '">' . ucwords($client['firstname'] . " " . $client['lastname']) . "</a>";
    $servicename = '<a href="clientsservices.php?userid=' . $userid . '&id=' . $service['serviceid'] . '">' . $product['name'] . '</a>';

    $tabledata[] = array("<center>#" . $data['id'] . "</center>", "<center>" . $clientname . "</center>", "<center>" . ucwords($data['type']) . "</center>", "<center>" . $data['message'] . "</center>", "<center>" . strtolower($data['recipient']) . "</center>", "<center>" . $servicename . "</center>", '<a href="' . $modulelink . '&a=logs&action=delete&id=' . $data['id'] . '" onclick="return confirm(\'' . $LANG['deletelogwarn'] . '\');"><center><img src="images/delete.gif"></center></a>');
}

echo $aInt->sortableTable(array($LANG['id'], $LANG['clientname'], $LANG['type'], $LANG['message'], $LANG['recipient'], $LANG['servicename'], ""), $tabledata);
?>