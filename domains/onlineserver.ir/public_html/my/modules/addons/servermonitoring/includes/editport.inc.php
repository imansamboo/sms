<?php

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

$id = mysql_real_escape_string($_REQUEST['id']);

if (isset($_REQUEST['save']) && $_REQUEST['save'] && isset($_REQUEST['port']) && isset($_REQUEST['desc']) && is_numeric($_REQUEST['port'])) {
    $id = mysql_real_escape_string($_REQUEST['id']);
    $port = mysql_real_escape_string($_REQUEST['port']);
    $desc = mysql_real_escape_string($_REQUEST['desc']);
    $query = mysql_query("UPDATE `mod_servermonitoring_ports` SET `port`='" . $port . "', `desc`='" . $desc . "' WHERE `id`='" . $id . "'");
    redir("module=servermonitoring&a=ports&success=edit");
    exit;
}

$query = mysql_query("SELECT `port`,`desc` FROM `mod_servermonitoring_ports` WHERE `id`='" . $id . "' LIMIT 1");
$result = mysql_fetch_assoc($query);

if (empty($_POST['port']))
    $port = $result['port'];
else
    $port = $_POST['port'];
if (empty($_POST['desc']))
    $desc = $result['desc'];
else
    $desc = $_POST['desc'];

echo '<form action="' . $modulelink . '&a=editport&save=true&id=' . $_REQUEST['id'] . '" method="post"><pre style="text-align:center;">';
echo '<center><h2>' . $LANG['editport'] . '</h2><div style="width:50%;"><input type="text" class="form-control" name="port" value="' . $port . '" placeholder="' . $LANG['port'] . '" style="margin-bottom:10px;"><input type="text" class="form-control" name="desc" value="' . $desc . '" placeholder="' . $LANG['description'] . '"></div></center>';
echo '<br><p align="center"><button class="btn btn-success" type="submit" style="margin-top: 10px;">' . $LANG['editport'] . '</button></p>';
echo '</pre></form>';
