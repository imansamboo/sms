<?php

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

if (isset($_REQUEST['save']) && $_REQUEST['save'] && isset($_REQUEST['location']) && isset($_REQUEST['description']) && isset($_REQUEST['url'])) {
    $location = mysql_real_escape_string($_REQUEST['location']);
    $description = mysql_real_escape_string($_REQUEST['description']);
    $url = mysql_real_escape_string($_REQUEST['url']);
    //$accesskey = md5('y-m-d h:i:s'); 
    $query = mysql_query("INSERT INTO `mod_servermonitoring_locations` SET `url`='" . $url . "', `description`='" . $description . "', `location`='" . $location . "'");
    redir("module=servermonitoring&a=locations&success=add");
    exit;
}

echo '<form action="' . $modulelink . '&a=addlocation&save=true" method="post"><pre style="text-align:center;">';
echo '<center><h2>' . $LANG['addlocation'] . '</h2><div style="width:50%;"><input type="text" class="form-control" name="url" value="' . $_POST['url'] . '" placeholder="' . $LANG['url'] . '" style="margin-bottom:10px;"><input type="text" class="form-control" name="location" value="' . $_POST['location'] . '" placeholder="' . $LANG['physicallocation'] . '" style="margin-bottom:10px;"><input type="text" class="form-control" name="description" value="' . $_POST['description'] . '" placeholder="' . $LANG['description'] . '"></div></center>';
echo '<br><p align="center"><button class="btn btn-success" type="submit" style="margin-top: 10px;">' . $LANG['addlocation'] . '</button></p>';
echo '</pre></form>';
