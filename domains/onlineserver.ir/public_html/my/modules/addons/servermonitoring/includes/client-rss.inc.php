<?php

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

use Illuminate\Database\Capsule\Manager as Capsule;

global $CONFIG;
$settings = servermonitoring_settings($vars);
if ($settings['rss_enable'] != "on") {
    redir('', 'index.php');
    exit;
}
Header('Content-type: text/xml');
//echo $rss->asXML();
echo '<rss version="2.0">
    <channel>
        <title>' . $CONFIG['CompanyName'] . ' RSS</title>
        <link>' . $CONFIG['Domain'] . '</link>
        <description>' . $LANG['rssdesc'] . $CONFIG['Domain'] . '</description>
        <language>en-us</language>
        <copyright>Copyright (C) 20' . date('y') . ' ' . $CONFIG['CompanyName'] . '</copyright>';

$cuid = (int) $_REQUEST['id'];
$result = Capsule::table('mod_servermonitoring_services')->where('uid', $cuid)->select('id')->get();
$arr = array();
foreach ($result as $id) {
    $arr[] = $id->id;
}
$i = 20;
$result = Capsule::table('mod_servermonitoring_monitors')->whereIn('serviceid', $arr)->orderBy('id', 'DESC')->take(20)->get();
foreach ($result as $data) {
    $mid = $data->id;
    $days = array();
    for ($i = 1; $i <= 6; $i++) {
        $time = strtotime("-" . $i . " day");
        $day1 = Capsule::table('mod_servermonitoring_status')->where('mid', $mid)->where('res_date', date('Y-m-d', $time))->first();
        $percent = $day1->error_count / $day1->req_count;
        if ($day1->error_count == 0 && $day1->req_count == 0) {
            $days[$i] = 'N/A';
        } else {
            $days[$i] = number_format(100 - number_format($percent * 100, 2), 2) . '%'; // change 2 to # of decimals   
        }
    }
    $lasti = Capsule::table('mod_servermonitoring_status')->where('mid', $mid)->orderBy('id', 'DESC')->take(1)->select('lastid')->first();
    $lastid = $lasti->lastid;
    if ($lastid == '')
        $lastid = 0;
    $error_count = Capsule::table('mod_servermonitoring_response')->where('res_server_id', $mid)->where('id', '>', $lastid)->where('status', '0')->count();
    $count = Capsule::table('mod_servermonitoring_response')->where('res_server_id', $mid)->where('id', '>', $lastid)->count();
    $percent = $error_count / $count;
    $today = number_format(100 - number_format($percent * 100, 2), 2) . '%'; // change 2 to # of decimals   
    echo '<item>
            <ID>' . $data->id . '</ID>
            <title>' . $data->monitorname . '</title>
            <description>' . $LANG['interval'] . ' : ' . $data->custom_interval . ' -- ' . $LANG['status'] . ' : ' . ($data->online == 1 ? 'UP' : 'DOWN') . ' -- ' . $LANG['todayuptime'] . ' : ' . $today . ' -- ' . $LANG['TotalRequests'] . ' : ' . $count . ' -- ' . $LANG['TotalDownRequests'] . ' : ' . $error_count . ' -- ' . $LANG['lastmonitor'] . ' : ' . (date('y-m-d h:i:s', $data->lastmonitor)) . '</description>
        </item>';
    $i--;
}
echo '
    </channel>
</rss>';
exit;
