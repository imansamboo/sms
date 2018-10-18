<?php
if (!defined("WHMCS")) die("This file cannot be accessed directly");

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "delete" && isset($_REQUEST['id']) && is_numeric($_REQUEST['id'])) {
	$id = mysql_real_escape_string($_REQUEST['id']);
	$query = mysql_query("DELETE FROM `mod_servermonitoring_ports` WHERE `id`='".$id."'");
	redir("module=servermonitoring&a=ports&success=delete");
	exit;
}

echo '<h2 align="center" style="color:grey;"><strong>'.$LANG['monitorports'].'</strong></h2><p align="right"><a href="'.$modulelink.'&a=addport"><button class="btn btn-success" type="button">'.$LANG['addnew'].'</button></a></p>';
$aInt->sortableTableInit("id");
$numrows = get_query_val("mod_servermonitoring_ports", "COUNT(*)", "", "id", "ASC");
$limit = "50";
$page = $_REQUEST['page'];
if (empty($page) || !is_numeric($page)) $page = 0; else $page = $page;
$page = "".$page."";
	
$result = select_query("mod_servermonitoring_ports", "", "", "id", "ASC", $page * $limit . "," . $limit);
while ($data = mysql_fetch_assoc($result)) {
    if(!isset($data['userid']))
        $data['userid'] = 0;
if ($data['serviceid'] == 0) $serviceid = $LANG['global']; else $serviceid = $data['serviceid'];
if ($data['userid'] == 0) $userid = $LANG['global']; else $userid = $data['userid'];
$tabledata[] = array($data['id'], $data['port'], $data['desc'], $serviceid, $userid, '<a href="'.$modulelink.'&a=editport&id='.$data['id'].'"><center><img src="images/edit.gif"></center></a>','<a href="'.$modulelink.'&a=ports&action=delete&id='.$data['id'].'" onclick="return confirm(\''.$LANG['deleteportwarn'].'\');"><center><img src="images/delete.gif"></center></a>');
}

echo $aInt->sortableTable(array($LANG['id'],$LANG['port'],$LANG['description'],$LANG['servicename'],$LANG['clientname'],"",""),$tabledata);
?>