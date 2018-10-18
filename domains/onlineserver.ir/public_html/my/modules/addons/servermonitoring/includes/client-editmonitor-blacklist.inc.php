<?php

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

use Illuminate\Database\Capsule\Manager as Capsule;

if (isset($_REQUEST['disable']) && $_REQUEST['disable']) {
    $mid = mysql_real_escape_string($_REQUEST['mid']);
    $query = mysql_query("UPDATE `mod_servermonitoring_monitors` SET `status`='Disabled' WHERE type='blacklist' AND `id`='" . $mid . "'");
    redir("m=servermonitoring&id=" . $_REQUEST['id'] . "&mid=" . $mid . "&edit=true&type=blacklist&success=disabled");
}
if (isset($_REQUEST['enable']) && $_REQUEST['enable']) {
    $mid = mysql_real_escape_string($_REQUEST['mid']);
    $query = mysql_query("UPDATE `mod_servermonitoring_monitors` SET `status`='Active' WHERE `type`='blacklist' AND `id`='" . $mid . "'");
    redir("m=servermonitoring&id=" . $_REQUEST['id'] . "&mid=" . $mid . "&edit=true&type=blacklist&success=enabled");
}
if (isset($_REQUEST['delete']) && $_REQUEST['delete']) {
    $mid = mysql_real_escape_string($_REQUEST['mid']);
    $query = mysql_query("DELETE FROM `mod_servermonitoring_monitors` WHERE `type`='blacklist' AND `id`='" . $mid . "'");
    redir("m=servermonitoring&id=" . $_REQUEST['id'] . "&success=deleted");
}

if (isset($_POST['url']) && isset($_REQUEST['monitorname']) && isset($_REQUEST['mid']) && isset($_REQUEST['id']) && is_numeric($_REQUEST['mid']) && is_numeric($_REQUEST['id'])) {
    $url = mysql_real_escape_string($_POST['url']);
    $monitorname = mysql_real_escape_string($_POST['monitorname']);
    $custom_interval = mysql_real_escape_string($_POST['custom_interval']);
    $custom_interval = ($custom_interval * 60);
    $query = mysql_query("SELECT * FROM `mod_servermonitoring_services` WHERE `serviceid`='" . mysql_real_escape_string($_REQUEST['id']) . "'");
    $services = mysql_fetch_assoc($query);
    $querya = mysql_query("SELECT * FROM `mod_servermonitoring_monitors` WHERE `type`='blacklist' AND `serviceid`='" . $services['id'] . "' AND `id`='" . mysql_real_escape_string($_REQUEST['mid']) . "'");
    $monitor = mysql_fetch_assoc($querya);
    if (!empty($monitor['id']) && $monitor['type'] == "blacklist") {
        if ($_POST['url'] != $monitor['url'])
            $clearstats = true;
        else
            $clearstats = false;
        if ($clearstats) {
            $query = mysql_query("UPDATE `mod_servermonitoring_monitors` SET `url`='" . $url . "', `smssent`='0', `emailssent`='0', `blacklisted`='0',`totaldowntime`='', `downtime`='0', `lastmonitor`='0', `lastsms`='0', `lastemail`='0' WHERE `id`='" . $monitor['id'] . "'");
        }
        $query = mysql_query("UPDATE `mod_servermonitoring_monitors` SET `monitorname`='" . $monitorname . "', `custom_interval`='" . $custom_interval . "' WHERE `id`='" . $monitor['id'] . "'");
        redir('m=servermonitoring&id=' . $_REQUEST['id'] . '&success=true');
    }
}

$query = mysql_query("SELECT * FROM `mod_servermonitoring_services` WHERE `serviceid`='" . mysql_real_escape_string($_REQUEST['id']) . "'");
$services = mysql_fetch_assoc($query);
$querya = mysql_query("SELECT * FROM `mod_servermonitoring_monitors` WHERE `type`='blacklist' AND `serviceid`='" . $services['id'] . "' AND `id`='" . mysql_real_escape_string($_REQUEST['mid']) . "'");
$monitor = mysql_fetch_assoc($querya);
$total = mysql_num_rows($querya);

if ($total == 1) {
    $output .= '<pre><p align="center" style="margin-bottom:0px;">';
    $output .= '<a href="index.php?m=servermonitoring&id=' . $_REQUEST['id'] . '&mid=' . $_REQUEST['mid'] . '&maintenance=true"><button type="button" class="btn btn-info">' . $LANG['maintenanceschedule'] . '</button></a>&nbsp;&nbsp;';
    if ($monitor['status'] == "Active") {
        $output .= '<a href="index.php?m=servermonitoring&id=' . $_REQUEST['id'] . '&mid=' . $_REQUEST['mid'] . '&edit=true&type=blacklist&disable=true"><button type="button" class="btn btn-warning">' . $LANG['disablemonitor'] . '</button></a>&nbsp;&nbsp;';
    } else {
        $output .= '<a href="index.php?m=servermonitoring&id=' . $_REQUEST['id'] . '&mid=' . $_REQUEST['mid'] . '&edit=true&type=blacklist&enable=true"><button type="button" class="btn btn-success">' . $LANG['enablemonitor'] . '</button></a>&nbsp;&nbsp;';
    }
    $intervals = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 12, 24, 36, 48, 60);
    $intdropdown = '';
    foreach ($intervals AS $value) {
        $select = '';
        if ($value == 1)
            $minute = $LANG['hour'];
        else
            $minute = $LANG['hours'];
        $t = ($value * 60);
        if ($t == $monitor['custom_interval'])
            $select = 'selected';
        $intdropdown .= '<option value="' . $value . '" ' . $select . '>' . $value . ' ' . $minute . '</option>';
    }

    $output .= '<a href="index.php?m=servermonitoring&id=' . $_REQUEST['id'] . '&mid=' . $_REQUEST['mid'] . '&edit=true&type=blacklist&delete=true"><button class="btn btn-danger" type="button" onclick="return confirm(\'' . $LANG['deletemonitorwarn'] . '\');">' . $LANG['deletemonitor'] . '</button></a>';
    $output .= '</p></pre>';

    $output .= '<center><div style="width:50%"><form action="index.php?m=servermonitoring&id=' . $_REQUEST['id'] . '&mid=' . $_REQUEST['mid'] . '&edit=true&type=blacklist&save=true" method="post">';

    if (isset($_POST['url']) && !empty($_POST['url']))
        $url = $_POST['url'];
    else
        $url = $monitor['url'];
    if (isset($_POST['monitorname']) && !empty($_POST['monitorname']))
        $monitorname = $_POST['monitorname'];
    else
        $monitorname = $monitor['monitorname'];
    $output .= '<span><strong>' . $LANG['monitorname'] . '</strong></span><p><input type="text" name="monitorname" value="' . $monitorname . '" class="form-control" placeholder="' . $LANG['monitorname'] . '"></p>';
    $output .= '<span><strong>' . $LANG['hostnameip'] . '</strong></span><p><input type="text" name="url" value="' . $url . '" class="form-control" placeholder="' . $LANG['hostnameip'] . '"></p>';
    $output .= '<span><strong>' . $LANG['interval'] . '</strong></span><p><select size="1" name="custom_interval" class="form-control">' . $intdropdown . '</select></p>';
    $output .= '<p><button class="btn btn-success" type="submit" onclick="return confirm(\'' . $LANG['editblmonitorwarn'] . '\');">' . $LANG['savechanges'] . '</button>&nbsp;&nbsp;<a href="index.php?m=servermonitoring&id=' . $_REQUEST['id'] . '"><button class="btn btn-danger" type="button">' . $LANG['goback'] . '</button></a></p></form></div></center>';
} else {
    redir('m=servermonitoring&id=' . $_REQUEST['id']);
}