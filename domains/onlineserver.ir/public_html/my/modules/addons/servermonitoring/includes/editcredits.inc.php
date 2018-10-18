<?php
if (!defined("WHMCS")) die("This file cannot be accessed directly");

if (isset($_REQUEST['edit']) && $_REQUEST['edit'] && isset($_REQUEST['smscredits']) && is_numeric($_REQUEST['smscredits'])) {
	$query = mysql_query("UPDATE `mod_servermonitoring_services` SET `smscredits`='".mysql_real_escape_string($_REQUEST['smscredits'])."' WHERE `id`='".mysql_real_escape_string($_REQUEST['id'])."'");
	redir('module=servermonitoring&a=services&success=editcredits');
}

$query = mysql_query("SELECT `smscredits`,`id` FROM `mod_servermonitoring_services` WHERE `id`='".mysql_real_escape_string($_REQUEST['id'])."'");
$result = mysql_fetch_assoc($query);
$smscredits = $result['smscredits'];

echo '<form action="addonmodules.php?module=servermonitoring&a=services&id='.$_REQUEST['id'].'&editcredits=true&edit=true" method="post"><center><pre style="width:20%; text-align:center;">';
echo '<p><span>'.$LANG['editcredits'].'</span><input type="text" value="'.$smscredits.'" style="text-align:center;" class="form-control" name="smscredits"></p>';
echo '<p><button type="submit" class="btn btn-success">'.$LANG['savechanges'].'</button></p>';
echo '</pre></center></form>';