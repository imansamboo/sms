<?php

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

use Illuminate\Database\Capsule\Manager as Capsule;

$requirelogin = false;
$settings = servermonitoring_settings($vars);
$mid = $_REQUEST['mid'];
$check = Capsule::table('mod_servermonitoring_monitors')->where('status', 'Active')->where('accesskey', $mid)->count();
if ($check <= 0)
    redir('m=servermonitoring');
$mi = Capsule::table('mod_servermonitoring_monitors')->where('status', 'Active')->where('accesskey', $mid)->select('id')->first();
$mid = $mi->id;
$dtime = $settings['chart_time'];
if ($dtime == '')
    $dtime = '6';
$s_time = strtotime("-" . $dtime . " hours");
$e_time = time();
$list = Capsule::table('mod_servermonitoring_response')->where('res_server_id', $mid)->where('res_time', '>', $s_time)->where('res_time', '<', $e_time)->whereNotIn('status', array('0'))->orderBy('id', 'asc')->get();
$o = '';
$start = 0;
$end = 0;
foreach ($list as $t) {
    if ($start == 0)
        $start = $t->res_time . '000';
    $end = $t->res_time . '000';
    $o .= '[new Date(' . $t->res_time . '000),' . ((float) $t->loadtime) . "],\n";
}
$interval = end($list);
$interval = $interval->res_server_id;
$servicei = Capsule::table('mod_servermonitoring_monitors')->where('id', $interval)->select('serviceid')->first();
$serviceid = $servicei->serviceid;
$servicei = Capsule::table('mod_servermonitoring_services')->where('id', $serviceid)->select('serviceid')->first();
$serviceid = $servicei->serviceid;
$interva = Capsule::table('mod_servermonitoring_monitors')->where('id', $interval)->select('custom_interval')->first();
$interval = $interva->custom_interval;
$interval = $interval * 60 * 1000;
$days = array();
for ($i = 1; $i <= 6; $i++) {
    $time = strtotime("-" . $i . " day");
    $day1 = Capsule::table('mod_servermonitoring_status')->where('mid', $mid)->where('res_date', date('Y-m-d', $time))->first();
    $percent = @$day1->error_count / @$day1->req_count;
    if (@$day1->error_count == 0 && @$day1->req_count == 0) {
        $days[$i] = 'N/A';
    } elseif ($day1->error_count == $day1->req_count) {
        $days[$i] = '0%';
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
$output .= '
<table id="tableServicesList" class="table table-list">
    <thead>
        <tr>
            <th>' . date("j M") . '</th>
            <th>' . date("j M", strtotime("-1 day")) . '</th>
            <th>' . date("j M", strtotime("-2 day")) . '</th>
            <th>' . date("j M", strtotime("-3 day")) . '</th>
            <th>' . date("j M", strtotime("-4 day")) . '</th>
            <th>' . date("j M", strtotime("-5 day")) . '</th>
            <th>' . date("j M", strtotime("-6 day")) . '</th>
        </tr>
    </thead>
    <tbody><tr>
            <td class="text-center"><span class="label status status-active">' . $today . '</span></td>    
';
foreach ($days as $day => $val) {
    $output .= '<td class="text-center"><span class="label status status-active">' . $val . '</span></td>';
}
$output .= '                    
        </tr></tbody>
</table>
<div id="chart_div" style="margin: 0 auto"></div>
<h3>' . $LANG['LastEvents'] . '</h3>
<table id="tableServicesList" class="table table-list">
    <thead>
        <tr>
            <th>' . $LANG['chartcheck'] . '</th>
            <th>' . $LANG['chartduration'] . '</th>
            <th>' . $LANG['chartreason'] . '</th>
            <th>' . $LANG['charttype'] . '</th>
        </tr>
    </thead>
    <tbody>';
$list = Capsule::table('mod_servermonitoring_stat')->where('mid', $mid)->orderBy('id', 'DESC')->take(10)->get();
foreach ($list as $status) {
    $time = ($status->duration - $status->event_date);
    $downtime = servermonitoring_secondsToTime($time, $LANG);
    $output .= '<tr>
                <td class="text-center">' . (date('y-m-d h:i:s', $status->duration)) . '</td>
                <td class="text-center">' . $downtime . '</td>
                <td class="text-center">' . $status->reason . '</td>
                <td class="text-center"><span class="label status status-' . (($status->type == "1") ? "active" : "disable" ) . '">' . (($status->type == "1") ? "UP" : "Down" ) . '</span></td>
            </tr>';
}

$output .= '</tbody>
</table>
';
$nam = Capsule::table('mod_servermonitoring_monitors')->where('status', 'Active')->where('id', $mid)->select('monitorname')->first();
$name = $nam->monitorname;
$output .= "
  <script type='text/javascript' src='https://www.gstatic.com/charts/loader.js'></script>
  <script type='text/javascript'>//<![CDATA[
//<![CDATA[
      google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(drawChart);
    function drawChart() {

      var data = new google.visualization.DataTable();
      data.addColumn('datetime', 'Time of Day');
      data.addColumn('number', '" . $LANG['ResponseTime'] . " (ms)');

      data.addRows([
        " . $o . "
      ]);

      var options = {
        width: '100%',
        height: 400,
        title: '" . $name . "',
        legend: {position: 'none'},
        enableInteractivity: true,
        chartArea: {
          width: '100%'
        },
        curveType: 'function',
        pointSize: 3,        
        hAxis: {
          viewWindow: {
            min: new Date(" . $start . "),
            max: new Date(" . $end . ")
          },
          gridlines: {
            count: -1,
            units: {
              days: {format: ['MMM dd']},
              hours: {format: ['HH:mm', 'ha']},
            }
          },
          minorGridlines: {
            units: {
              hours: {format: ['hh:mm:ss a', 'ha']},
              minutes: {format: ['HH:mm a Z', ':mm']}
            }
          }
        }
      };

      var chart = new google.visualization.LineChart(
        document.getElementById('chart_div'));

      chart.draw(data, options);
    }

//]]> 
</script>";
$pagetitle = $name;
if (isset($_SESSION['uid']) || isset($_SESSION['adminid'])) {
    $output .= '<p align="center"><a href="#" onclick="history.go(-1);"><button class="btn btn-danger" type="button">' . $LANG['goback'] . '</button>&nbsp&nbsp</a></p>';
} else {
    $output .= '<p align="center"><a href="#" onclick="history.go(-1);"><button class="btn btn-danger" type="button">' . $LANG['goback'] . '</button>&nbsp&nbsp</a></p>';
}