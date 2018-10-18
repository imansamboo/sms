<?php
if (!defined("WHMCS")) die("This file cannot be accessed directly");

if (isset($_REQUEST['save']) && $_REQUEST['save'] && isset($_REQUEST['port']) && isset($_REQUEST['desc']) && is_numeric($_REQUEST['port'])) {
	$port = mysql_real_escape_string($_REQUEST['port']);
	$desc = mysql_real_escape_string($_REQUEST['desc']);
	$query = mysql_query("INSERT INTO `mod_servermonitoring_ports` SET `port`='".$port."', `desc`='".$desc."', `uid`='0', `serviceid`='0'");
	redir("module=servermonitoring&a=ports&success=add");
	exit;
}

echo '<form action="'.$modulelink.'&a=addport&save=true" method="post"><pre style="text-align:center;">';
echo '<center><h2>'.$LANG['addport'].'</h2><div style="width:50%;"><input type="text" class="form-control" name="port" value="'.$_POST['port'].'" placeholder="'.$LANG['port'].'" style="margin-bottom:10px;"><input type="text" class="form-control" name="desc" value="'.$_POST['desc'].'" placeholder="'.$LANG['description'].'"></div></center>';
echo '<br><p align="center"><button class="btn btn-success" type="submit" style="margin-top: 10px;">'.$LANG['addport'].'</button></p>';
echo '</pre></form>';