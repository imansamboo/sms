<?php

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");
global $CONFIG;

use Illuminate\Database\Capsule\Manager as Capsule;

if (isset($_GET['statid'])) {
    $id = $_GET['statid'];
    $list_monitors = Capsule::table('mod_servermonitoring_pages')->select('title', 'monitors', 'logo')->where('status', '1')->where('accesskey', $id)->first();
    if (count($list_monitors) <= 0)
        redir('', 'index.php');
} else {
    redir('', 'index.php');
}
$monitors = explode(',', $list_monitors->monitors);
$list = Capsule::table('mod_servermonitoring_monitors')->select('id', 'monitorname', 'port', 'type','accesskey')->where('status', 'Active')->whereIn('id', $monitors)->orderBy('id', 'DESC')->get();
$datatoshow = array();
$ii = 0;
foreach ($list as $monitor) {
    $datatoshow[$ii]['name'] = $monitor->monitorname;
    $datatoshow[$ii]['port'] = $monitor->port;
    $datatoshow[$ii]['mid'] = $monitor->accesskey;
    $datatoshow[$ii]['type'] = $monitor->type;
    $datatoshow[$ii]['actives'] = 1;
    $week = 0;
    for ($i = 0; $i <= 6; $i++) {
        if ($i == 0) {
            $lasti = Capsule::table('mod_servermonitoring_status')->where('mid', $monitor->id)->orderBy('id', 'DESC')->take(1)->select('lastid')->first();
            $lastid = $lasti->lastid;
            if (is_null($lastid))
                $lastid = 0;
            $error_count = Capsule::table('mod_servermonitoring_response')->where('res_server_id', $monitor->id)->where('id', '>', $lastid)->where('status', '0')->count();
            $count = Capsule::table('mod_servermonitoring_response')->where('res_server_id', $monitor->id)->where('id', '>', $lastid)->count();
            $percent = $error_count / $count;
            $today = number_format(100 - number_format($percent * 100, 2), 2) . '%'; // change 2 to # of decimals     }
            $datatoshow[$ii]['days'][] = $today;
            $week = $week + $today;
        } else {
            $time = strtotime("-" . $i . " day");
            $day1 = Capsule::table('mod_servermonitoring_status')->where('mid', $monitor->id)->where('res_date', date('Y-m-d', $time))->first();
            $percent = $day1->error_count / $day1->req_count;
            if ($day1->error_count == 0 && $day1->req_count == 0) {
                $today = 'N/A';
            } else {
                $today = number_format(100 - number_format($percent * 100, 2), 2) . '%'; // change 2 to # of decimals   
                $datatoshow[$ii]['actives'] += 1;
            }
            $datatoshow[$ii]['days'][] = $today;
            if ($today != 'N/A')
                $week = $week + $today;
        }
    }
    $datatoshow[$ii]['week'] = $week;
    $ii++;
}
$output .= '<center><img src="' . $CONFIG['SystemURL'] . $list_monitors->logo . '" ></center><br>';
$pagetitle = $list_monitors->title;
$output .= '
<div class="col-sm-12">
    <div class="panel panel-default panel-accent-blue">
<table id="tableServicesList" class="table table-list">
    <thead>
        <tr>
            <th>' . $LANG['servername'] . '</th>
            <th>' . $LANG['port'] . '</th>
            <th>' . $LANG['WeekStatus'] . '</th>            
            <th>' . date("j M") . '</th>
            <th>' . date("j M", strtotime("-1 day")) . '</th>
            <th>' . date("j M", strtotime("-2 day")) . '</th>
            <th>' . date("j M", strtotime("-3 day")) . '</th>
            <th>' . date("j M", strtotime("-4 day")) . '</th>
            <th>' . date("j M", strtotime("-5 day")) . '</th>
            <th>' . date("j M", strtotime("-6 day")) . '</th>
        </tr>
    </thead>
    <tbody>
';
foreach ($datatoshow as $mr) {
    $link = '';
    if ($mr['type'] != 'blacklist')
        $link = 'onclick="clickableSafeRedirect(event, \'index.php?m=servermonitoring&amp;page=public&amp;mid=' . $mr['mid'] . '&amp;viewchart=1\', false)"';
    $output .= '<tr ' . $link . '>'
            . '<td class="text-center"><b>' . $mr['name'] . '</b></td>';
    $output .= '<td class="text-center"><span class="label status status-info">' . $mr['port'] . '</span></td>';
    $output .= '<td class="text-center"><b>' . number_format(($mr['week'] / $mr['actives']), 2) . '%' . '</b></td>';
    for ($i = 0; $i <= 6; $i++) {
        if ($mr['days'][$i] < 99 && $mr['days'][$i] != 'N/A') {
            $mr['days'][$i] = '<span class="label status status-suspended">' . $mr['days'][$i] . '</span>';
        } else {
            $mr['days'][$i] = '<span class="label status status-active">' . $mr['days'][$i] . '</span>';
        }
        $output .= '<td class="text-center">' . $mr['days'][$i] . '</td>';
    }
    $output .= '</tr>';
}
$count_monitoring = Capsule::table('mod_servermonitoring_monitors')->whereIn('id', $monitors)->where('online', '1')->count();
$count_nomonitoring = Capsule::table('mod_servermonitoring_monitors')->whereIn('id', $monitors)->where('online', '<>', '1')->count();
$count_request = Capsule::table('mod_servermonitoring_response')->whereIn('res_server_id', $monitors)->where('status', '1')->count();
$count_norequest = Capsule::table('mod_servermonitoring_response')->whereIn('res_server_id', $monitors)->where('status', '<>', '1')->count();
$output .= '                    
        </tbody>
</table>
</div>
</div>
<div class="col-sm-6">
        <div class="panel panel-default panel-accent-blue">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <i class="fa fa-newspaper-o"></i>&nbsp;' . $LANG['ServerCount'] . '
                </h3>
            </div>
            <div class="list-group">
                <a class="list-group-item">
                    <span class="title"><strong>' . $LANG['UpServerCount'] . ' : ' . $count_monitoring . '</strong></span><br>
                    <strong>' . $LANG['DownServerCount'] . ' : ' . $count_nomonitoring . '</strong>
                </a>
            </div>
            <div class="panel-footer">
            </div>
        </div>
</div>
<div class="col-sm-6">
        <div class="panel panel-default panel-accent-gold">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <i class="fa fa-newspaper-o"></i>&nbsp;' . $LANG['QuickStats'] . '
                </h3>
            </div>
            <div class="list-group">
                <a class="list-group-item">
                    <span class="title"><strong>' . $LANG['TotalRequests'] . ' : ' . $count_request . '</strong></span><br>
                    <strong>' . $LANG['TotalDownRequests'] . ' : ' . $count_norequest . '</strong>
                </a>
            </div>
            <div class="panel-footer">
            </div>
        </div>
</div>
';
