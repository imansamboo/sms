<?php
if (!defined("WHMCS")) die("This file cannot be accessed directly");

$id = mysql_real_escape_string($_REQUEST['id']);

if (isset($_REQUEST['save']) && $_REQUEST['save'] && isset($_REQUEST['location']) && isset($_REQUEST['description']) && isset($_REQUEST['url'])) {
	$location = mysql_real_escape_string($_REQUEST['location']);
	$description = mysql_real_escape_string($_REQUEST['description']);
	$url = mysql_real_escape_string($_REQUEST['url']);
	$query = mysql_query("UPDATE `mod_servermonitoring_locations` SET `url`='".$url."', `description`='".$description."', `location`='".$location."' WHERE `id`='".$id."'");
	redir("module=servermonitoring&a=locations&success=edit");
	exit;
}

$query = mysql_query("SELECT * FROM `mod_servermonitoring_locations` WHERE `id`='".$id."' LIMIT 1");
$result = mysql_fetch_assoc($query);

if (empty($_POST['url'])) $url = $result['url']; else $url = $_POST['url'];
if (empty($_POST['location'])) $location = $result['location']; else $location = $_POST['location'];
if (empty($_POST['description'])) $description = $result['description']; else $description = $_POST['description'];

echo '<form action="'.$modulelink.'&a=editlocation&id='.$_REQUEST['id'].'&save=true" method="post"><pre style="text-align:center;">';
echo '<center><h2>'.$LANG['addlocation'].'</h2><div style="width:50%;"><input type="text" class="form-control" name="url" value="'.$url.'" placeholder="'.$LANG['url'].'" style="margin-bottom:10px;"><input type="text" class="form-control" name="location" value="'.$location.'" placeholder="'.$LANG['physicallocation'].'" style="margin-bottom:10px;"><input type="text" class="form-control" name="description" value="'.$description.'" placeholder="'.$LANG['description'].'"></div></center>';
echo '<br><p align="center"><button class="btn btn-success" type="submit" style="margin-top: 10px;">'.$LANG['editlocation'].'</button></p>';
echo '</pre></form>';