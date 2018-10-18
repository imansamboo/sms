<?php

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

$query = mysql_query("SELECT `id` FROM `mod_servermonitoring_services` WHERE `serviceid`='" . mysql_real_escape_string($_REQUEST['id']) . "' AND `uid`='" . mysql_real_escape_string($_SESSION['uid']) . "'");
$services = mysql_fetch_assoc($query);
$querya = mysql_query("SELECT * FROM `mod_servermonitoring_monitors` WHERE `id`='" . mysql_real_escape_string($_REQUEST['mid']) . "' AND `serviceid`='" . mysql_real_escape_string($services['id']) . "'");
$monitor = mysql_fetch_assoc($querya);
$blacklists = unserialize($monitor['totaldowntime']);

if ($monitor['type'] == "blacklist") {
    $output .= '<div class="table-container clearfix">
						<table id="tableServicesList" class="table table-list">
							<thead>
								<tr>
									<th>' . $LANG['servername'] . '</th>
									<th>' . $LANG['serverurl'] . '</th>
									<th>' . $LANG['removalurl'] . '</th>
									<th>' . $LANG['blacklisted'] . '</th>
								</tr>
							</thead>
							<tbody>';

    foreach ($blacklists AS $key => $value) {
        $querya = mysql_query("SELECT * FROM `mod_servermonitoring_blacklist` WHERE `id`='" . mysql_real_escape_string($key) . "'");
        $bldata = mysql_fetch_assoc($querya);
        if ($value)
            $islisted = '<font color="red">' . $LANG['blacklisted'] . '</font>';
        else
            $islisted = '<font color="green">' . $LANG['notblacklisted'] . '</font>';
        $output .= '<tr>
						<td class="text-center" style="cursor:initial;"><strong>' . $bldata['server_name'] . '</strong></td>
						<td class="text-center" style="cursor:initial;"><strong>' . $bldata['server_url'] . '</strong></td>
						<td class="text-center" style="cursor:initial;"><strong><a href="' . $bldata['removal_url'] . '" target="_new">' . $bldata['removal_url'] . '</a></strong></td>
						<td class="text-center" style="cursor:initial;"><strong>' . $islisted . '</strong></td>
					</tr>';
    }

    $output .= '		</tbody>
						</table>
					</div>';
} else {

    $output .= '
<div class="table-container clearfix">
    <table id="tableServicesList" class="table table-list">
        <thead>
            <tr>
                <th>' . $LANG['monthyear'] . '</th>
                <th>' . $LANG['totaldowntime'] . '</th>
                <th>' . $LANG['uptimescore'] . '</th>
            </tr>
        </thead>
        <tbody>';

    $activity = array_reverse(unserialize($monitor['totaldowntime']));

    foreach ($activity AS $key => $value) {
        $key = explode(",", $key);
        $month = $key[0];
        $year = $key[1];

        if (strtolower($month) == "janurary" || strtolower($month) == "jan")
            $nmonth = "1";
        if (strtolower($month) == "feburary" || strtolower($month) == "feb")
            $nmonth = "2";
        if (strtolower($month) == "march" || strtolower($month) == "mar")
            $nmonth = "3";
        if (strtolower($month) == "april" || strtolower($month) == "apr")
            $nmonth = "4";
        if (strtolower($month) == "may")
            $nmonth = "5";
        if (strtolower($month) == "june" || strtolower($month) == "jun")
            $nmonth = "6";
        if (strtolower($month) == "july" || strtolower($month) == "jul")
            $nmonth = "7";
        if (strtolower($month) == "august" || strtolower($month) == "aug")
            $nmonth = "8";
        if (strtolower($month) == "september" || strtolower($month) == "sep")
            $nmonth = "9";
        if (strtolower($month) == "october" || strtolower($month) == "oct")
            $nmonth = "10";
        if (strtolower($month) == "november" || strtolower($month) == "nov")
            $nmonth = "11";
        if (strtolower($month) == "december" || strtolower($month) == "dec")
            $nmonth = "12";
        $value = round($value / 60);
        $days = cal_days_in_month(CAL_GREGORIAN, $nmonth, $year);
        $minutes = 60 * 24 * $days;

        $totaluptime = number_format(((($minutes - $value) / ($minutes)) * 100), 2) . "%";
        $totaluptime = str_replace(".00%", "%", $totaluptime);

        $valuea = number_format($value) . " " . $LANG['minutes'];
        $output .= '<tr>
						<td class="text-center" style="cursor:initial;"><strong>' . $month . ', ' . $year . '</strong></td>
						<td class="text-center" style="cursor:initial;"><strong>' . $valuea . '</strong></td>
						<td class="text-center" style="cursor:initial;"><strong>' . $totaluptime . '</strong></td>
					</tr>';
    }

    $output .= '</tbody>
    </table>
</div>';
    $output .= '<center><img width="300" height="200" src="modules/addons/servermonitoring/graph/?data=' . base64_encode($monitor['totaldowntime']) . '" border="0"></center>';
}
$output .= '<p align="center"><a href="index.php?m=servermonitoring&id=' . $_REQUEST['id'] . '"><button type="button" class="btn btn-danger">' . $LANG['goback'] . '</button></a></p>';
?>