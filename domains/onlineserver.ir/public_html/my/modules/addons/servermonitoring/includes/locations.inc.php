<?php

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");
/*
if (isset($_GET['download'])) {
    global $CONFIG;
    ob_end_clean();
    ob_start();
    $id = (int) $_GET['id'];
    $query = mysql_query("SELECT * FROM `mod_servermonitoring_locations` WHERE `id`='" . $id . "' LIMIT 1");
    $result = mysql_fetch_assoc($query);
    header('Content-Disposition: attachment; filename="remote.php"');
    header("Content-Type: application/x-httpd-php");
    $file = file_get_contents(ROOTDIR.'/modules/addons/servermonitoring/checkStatus.php');
    $file = str_replace('{servername}', $CONFIG['SystemURL'], $file);
    $file = str_replace('{accesskey}', $result['accesskey'], $file);
    echo $file;
    exit;
}
 * */
if (isset($_REQUEST['action']) && $_REQUEST['action'] == "delete" && isset($_REQUEST['id']) && is_numeric($_REQUEST['id'])) {
    $id = mysql_real_escape_string($_REQUEST['id']);
    $query = mysql_query("DELETE FROM `mod_servermonitoring_locations` WHERE `id`='" . $id . "'");
    redir("module=servermonitoring&a=locations&success=delete");
    exit;
}

echo '<h2 align="center" style="color:grey;"><strong>' . $LANG['monitorlocations'] . '</strong></h2><p align="right"><a href="' . $modulelink . '&a=addlocation"><button class="btn btn-success" type="button">' . $LANG['addnew'] . '</button></a></p>';
$aInt->sortableTableInit("id");
$numrows = get_query_val("mod_servermonitoring_locations", "COUNT(*)", "", "id", "ASC");
$limit = "50";
$page = $_REQUEST['page'];
if (empty($page) || !is_numeric($page))
    $page = 0;
else
    $page = $page;
$page = "" . $page . "";

$result = select_query("mod_servermonitoring_locations", "", "", "id", "ASC", $page * $limit . "," . $limit);
while ($data = mysql_fetch_assoc($result)) {
    if ($data['serviceid'] == 0)
        $serviceid = $LANG['global'];
    else
        $serviceid = $data['serviceid'];
    if ($data['userid'] == 0)
        $userid = $LANG['global'];
    else
        $userid = $data['userid'];
    $tabledata[] = array("<center>" . $data['id'] . "</center>", "<center>" . $data['url'] . "</center>", "<center>" . $data['location'] . "</center>", "<center>" . $data['description'], '<a href="' . $modulelink . '&a=editlocation&id=' . $data['id'] . '"><center><img src="images/edit.gif"></center></a>', '<a href="' . $modulelink . '&a=locations&action=delete&id=' . $data['id'] . '" onclick="return confirm(\'' . $LANG['deletelocationwarn'] . '\');"><center><img src="images/delete.gif"></center></a>');
}

echo $aInt->sortableTable(array($LANG['id'], $LANG['url'], $LANG['locations'], $LANG['description'], "", ""), $tabledata);
?>