<?php
if (!defined("WHMCS")) die("This file cannot be accessed directly");

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "delete" && isset($_REQUEST['id']) && is_numeric($_REQUEST['id'])) {
	$id = mysql_real_escape_string($_REQUEST['id']);
	$query = mysql_query("DELETE FROM `mod_servermonitoring_blacklist` WHERE `id`='".$id."'");
	redir("module=servermonitoring&a=blacklist&success=true");
	exit;
}

if (isset($_REQUEST['disable']) && $_REQUEST['disable'] && isset($_REQUEST['id']) && is_numeric($_REQUEST['id'])) {
	$id = mysql_real_escape_string($_REQUEST['id']);
	$query = mysql_query("UPDATE `mod_servermonitoring_blacklist` SET `status`='0' WHERE `id`='".$id."'");
	redir("module=servermonitoring&a=blacklist&success=true");
	exit;
}

if (isset($_REQUEST['enable']) && $_REQUEST['enable'] && isset($_REQUEST['id']) && is_numeric($_REQUEST['id'])) {
	$id = mysql_real_escape_string($_REQUEST['id']);
	$query = mysql_query("UPDATE `mod_servermonitoring_blacklist` SET `status`='1' WHERE `id`='".$id."'");
	redir("module=servermonitoring&a=blacklist&success=true");
	exit;
}

echo '<h2 align="center" style="color:grey;"><strong>'.$LANG['blacklistservers'].'</strong></h2><p align="right"><a href="addonmodules.php?module=servermonitoring&a=blacklist&add=true"><button type="button" class="btn btn-success">'.$LANG['addnew'].'</button></a></p>';

$aInt->sortableTableInit("id");
$numrows = get_query_val("mod_servermonitoring_blacklist", "COUNT(*)", "", "id", "ASC");
$limit = "50";
$page = $_REQUEST['page'];
if (empty($page) || !is_numeric($page)) $page = 0; else $page = $page;
$page = "".$page."";
	
$result = select_query("mod_servermonitoring_blacklist", "", "", "id", "ASC", $page * $limit . "," . $limit);
while ($data = mysql_fetch_assoc($result)) {
	if ($data['status']) $status = '<a href="addonmodules.php?module=servermonitoring&a=blacklist&disable=true&id='.$data['id'].'">'.$LANG['enabled'].'</a>'; else $status = '<a href="addonmodules.php?module=servermonitoring&a=blacklist&enable=true&id='.$data['id'].'">'.$LANG['disabled'].'</a>';
	$tabledata[] = array("<center>#".$data['id']."</center>", "<center>".$data['server_name']."</center>", "<center>".$data['server_url']."</center>", "<center>".$data['removal_url']."</center>", "<center>".$status."</center>", '<a href="addonmodules.php?module=servermonitoring&a=blacklist&edit=true&id='.$data['id'].'"><center><img src="images/edit.gif"></center></a>','<a href="'.$modulelink.'&a=blacklist&action=delete&id='.$data['id'].'" onclick="return confirm(\''.$LANG['deletesmspackagewarn'].'\');"><center><img src="images/delete.gif"></center></a>');
}

echo $aInt->sortableTable(array($LANG['id'],$LANG['servername'],$LANG['serverurl'],$LANG['removalurl'],$LANG['status'],"",""),$tabledata);
?>