<?php
if (!defined("WHMCS")) die("This file cannot be accessed directly");

if (isset($_REQUEST['save']) && $_REQUEST['save'] && isset($_REQUEST['server_url']) && isset($_REQUEST['server_name']) && isset($_REQUEST['removal_url'])) {
	$query = mysql_query("UPDATE `mod_servermonitoring_blacklist` SET `server_url`='".mysql_real_escape_string($_REQUEST['server_url'])."',`server_name`='".mysql_real_escape_string($_REQUEST['server_name'])."',`removal_url`='".mysql_real_escape_string($_REQUEST['removal_url'])."' WHERE `id`='".mysql_real_escape_string($_REQUEST['id'])."'");
	redir('module=servermonitoring&a=blacklist&success=true');
}

$query = mysql_query("SELECT * FROM `mod_servermonitoring_blacklist` WHERE `id`='".mysql_real_escape_string($_REQUEST['id'])."'");
$result = mysql_fetch_assoc($query);
$server_name = $result['server_name'];
$server_url = $result['server_url'];
$removal_url = $result['removal_url'];

echo '<form action="addonmodules.php?module=servermonitoring&a=blacklist&edit=true&id='.$_REQUEST['id'].'&save=true" method="post"><center><pre style="width:50%; text-align:center;">';
echo '<p><span>'.$LANG['nameofserver'].'</span><input type="text" value="'.$server_name.'" style="text-align:center;" class="form-control" name="server_name"></p>';
echo '<p><span>'.$LANG['serverurlfordns'].'</span><input type="text" value="'.$server_url.'" style="text-align:center;" class="form-control" name="server_url"></p>';
echo '<p><span>'.$LANG['fullremovalurl'].'</span><input type="text" value="'.$removal_url.'" style="text-align:center;" class="form-control" name="removal_url"></p>';
echo '<p><button type="submit" class="btn btn-success">'.$LANG['savechanges'].'</button></p>';
echo '</pre></center></form>';