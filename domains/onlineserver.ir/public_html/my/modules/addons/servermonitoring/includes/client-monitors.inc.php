<?php

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

use Illuminate\Database\Capsule\Manager as Capsule;

$querye = mysql_query("SELECT * FROM `mod_servermonitoring_services` WHERE `uid`='" . $_SESSION['uid'] . "' AND `serviceid`='" . mysql_real_escape_string($_REQUEST['id']) . "'");
$services = mysql_fetch_assoc($querye);
$querya = mysql_query("SELECT * FROM `tblhosting` WHERE `id`='" . $services['serviceid'] . "' AND `userid`='" . $_SESSION['uid'] . "'");
$hosting = mysql_fetch_assoc($querya);
$queryd = mysql_query("SELECT `configoption3`,`configoption6`,`configoption7`,`configoption8`,`configoption9`,`configoption10`,`configoption11` FROM `tblproducts` WHERE `id`='" . $hosting['packageid'] . "'");
$products = mysql_fetch_assoc($queryd);
if (Capsule::schema()->hasTable('tblmodule_configuration')) {
    $checkisaddon = Capsule::table('tblhostingaddons')->where('hostingid', $services['serviceid'])->join('tbladdons', 'tblhostingaddons.addonid', '=', 'tbladdons.id')->where('module', 'serverMonitoring')->select('addonid')->first();
    if (count($checkisaddon) > 0) {
        $plist = Capsule::table('tblmodule_configuration')->where('entity_type', 'addon')->where('entity_id', $checkisaddon->addonid)->get();
        if (count($plist) > 0) {
            foreach ($plist as $value) {
                $products[$value->setting_name] = $value->value;
            }
        }
    }
}
$settings = servermonitoring_settings($vars);

$output .= '<p align="right">';
if ($settings['banner'] != "") {
    $output .= '<a href="' . $modulelink . '&page=banner"><button class="btn btn-primary" type="button">' . $LANG['linkus'] . '</button></a>&nbsp;&nbsp;';
}
if ($products['configoption6'] == "on") {
    $output .= '<a href="' . $modulelink . '&id=' . $_REQUEST['id'] . '&customport=true"><button class="btn btn-primary" type="button">' . $LANG['customports'] . '</button></a>&nbsp;&nbsp;';
}
if ($products['configoption11'] != "none" && $products['configoption11'] != '') {
    $output .= '<a href="' . $modulelink . '&id=' . $_REQUEST['id'] . '&page=publicpages"><button class="btn btn-primary" type="button">' . $LANG['PublicPages'] . '</button></a>&nbsp;&nbsp;';
}
if ($products['configoption8'] == "on") {
    $output .= '<a href="' . $modulelink . '&id=' . $_REQUEST['id'] . '&add=true&type=standard"><button class="btn btn-success" type="button">' . $LANG['addmonitor'] . '</button></a>&nbsp;&nbsp;';
}
if ($products['configoption7'] == "on") {
    $output .= '<a href="' . $modulelink . '&id=' . $_REQUEST['id'] . '&add=true&type=solusvm"><button class="btn btn-success" type="button">' . $LANG['addsolusvmmonitor'] . '</button></a>&nbsp;&nbsp;';
}
if ($products['configoption3'] == "on") {
    $output .= '<a href="' . $modulelink . '&id=' . $_REQUEST['id'] . '&add=true&type=blacklist"><button class="btn btn-success" type="button">' . $LANG['addblacklistmonitor'] . '</button></a>&nbsp;&nbsp;';
}
$output .= '<a href="index.php?m=servermonitoring&id=' . $_REQUEST['id'] . '&emailsettings=true"><button type="button" class="btn btn-warning">' . $LANG['emailsettings'] . '</button></a>';
if ($settings['allowSMS'] == "on") {
    $output .= '&nbsp;&nbsp;<a href="index.php?m=servermonitoring&id=' . $_REQUEST['id'] . '&smssettings=true"><button type="button" class="btn btn-warning">' . $LANG['smssettings'] . '</button></a></p>';
}

$output .= '
<div class="table-container clearfix">
    <table id="tableServicesList" class="table table-list">
        <thead>
            <tr>
                <th>' . $LANG['monitorname'] . '</th>
                <th>' . $LANG['urlip'] . '</th>
                <th>' . $LANG['port'] . '</th>
                <th>' . $LANG['interval'] . '</th>
                <th>' . $LANG['location'] . '</th>
                <th>' . $LANG['lastmonitor'] . '</th>
                <th>' . $LANG['monitorstatus'] . '</th>
                <th>' . $LANG['status'] . '</th>
                <th class="responsive-edit-button"></th>
                <th class="responsive-edit-button"></th>
                <th class="responsive-edit-button"></th>
            </tr>
        </thead>
        <tbody>';

$querye = mysql_query("SELECT * FROM `mod_servermonitoring_services` WHERE `uid`='" . $_SESSION['uid'] . "' AND `serviceid`='" . mysql_real_escape_string($_REQUEST['id']) . "'");
$services = mysql_fetch_assoc($querye);

$query = mysql_query("SELECT * FROM `mod_servermonitoring_monitors` WHERE `serviceid`='" . $services['id'] . "'");
while ($result = mysql_fetch_assoc($query)) {

    $querya = mysql_query("SELECT * FROM `tblhosting` WHERE `id`='" . $services['serviceid'] . "' AND `userid`='" . $_SESSION['uid'] . "'");
    $hosting = mysql_fetch_assoc($querya);

    $queryb = mysql_query("SELECT `currency` FROM `tblclients` WHERE `id`='" . $_SESSION['uid'] . "'");
    $client = mysql_fetch_assoc($queryb);

    $queryd = mysql_query("SELECT * FROM `tblproducts` WHERE `id`='" . $hosting['packageid'] . "'");
    $products = mysql_fetch_assoc($queryd);
    if (Capsule::schema()->hasTable('tblmodule_configuration')) {
        $checkisaddon = Capsule::table('tblhostingaddons')->where('hostingid', $services['serviceid'])->join('tbladdons', 'tblhostingaddons.addonid', '=', 'tbladdons.id')->where('module', 'serverMonitoring')->select('addonid')->first();
        if (count($checkisaddon) > 0) {
            $plist = Capsule::table('tblmodule_configuration')->where('entity_type', 'addon')->where('entity_id', $checkisaddon->addonid)->get();
            if (count($plist) > 0) {
                foreach ($plist as $value) {
                    $products[$value->setting_name] = $value->value;
                }
            }
        }
    }
    $pinterval = trim(preg_replace("/[^0-9]/", "", $products['configoption1']));

    if ($pinterval < $result['custom_interval'])
        $monitorinterval = $result['custom_interval'];
    else
        $monitorinterval = $pinterval;
    if ($monitorinterval == 1)
        $monitorinterval = $monitorinterval . ' ' . $LANG['minute'];
    else
        $monitorinterval = $monitorinterval . ' ' . $LANG['minutes'];

    if ($hosting['domainstatus'] == "Active")
        $dstatus = '<span class="label status status-active">' . $LANG['active'] . '</span>';
    if ($hosting['domainstatus'] == "Suspended")
        $dstatus = '<span class="label status status-suspended">' . $LANG['suspended'] . '</span>';
    if ($hosting['domainstatus'] == "Terminated")
        $dstatus = '<span class="label status status-terminated">' . $LANG['terminated'] . '</span>';
    if ($hosting['domainstatus'] == "Fraud")
        $dstatus = '<span class="label status status-fraud">' . $LANG['fraud'] . '</span>';
    if ($hosting['domainstatus'] == "Cancelled")
        $dstatus = '<span class="label status status-cancelled">' . $LANG['cancelled'] . '</span>';
    if ($hosting['domainstatus'] == "Pending")
        $dstatus = '<span class="label status status-pending">' . $LANG['pending'] . '</span>';
    if ($result['status'] == "Disabled" && $hosting['domainstatus'] == "Active")
        $dstatus = '<span class="label status status-suspended">' . $LANG['disabled'] . '</span>';

    if ($result['online'] == '1')
        $mstatus = '<span class="label status status-active">' . $LANG['up'] . '</span>';
    else
        $mstatus = '<span class="label status status-pending">' . $LANG['down'] . '</span>';
    if ($result['type'] == "blacklist" && !$result['blacklisted'])
        $mstatus = '<span class="label status status-active">' . $LANG['notblacklisted'] . '</span>';
    elseif ($result['type'] == "blacklist")
        $mstatus = '<span class="label status status-pending">' . $LANG['blacklisted'] . '</span>';

    if (empty($result['lastmonitor']) || $result['lastmonitor'] == '0')
        $lastmonitor = $_ADDONLANG['never'];
    else
        $lastmonitor = date('M d, Y H:i:s', $result['lastmonitor']);
    if (empty($result['url']) && $result['type'] == "solusvm")
        $url = $result['monitorname'] . ' <i>(' . $LANG['solusvm'] . ')</i>';
    elseif ($result['type'] == "blacklist")
        $url = $result['url'] . ' <i>(' . $LANG['blacklist'] . ')</i>';
    else
        $url = $result['url'];
    if ($result['port'] == 0 || $result['keyword'] != '')
        $port = "-";
    else {
        if (strpos($result['url'], 'https://') !== false && $result['port'] == 80)
            $port = 443;
        else
            $port = $result['port'];
    }
    $this_location = '';
    if ($result['location'] == '0') {
        $SETTINGS = servermonitoring_settings($vars);
        $this_location = $SETTINGS['localhostName'];
    } else {
        $locations = servermonitoring_locations();
        foreach ($locations AS $key => $value) {
            if ($key == $result['location'])
                $this_location = $value['location'];
        }
    }
    $s_url = ' onclick="clickableSafeRedirect(event, \'index.php?m=servermonitoring&amp;page=public&amp;mid=' . $result['accesskey'] . '&amp;viewchart=1\', false)" ';
    $b_url = 'index.php?m=servermonitoring&amp;page=public&amp;mid=' . $result['accesskey'] . '&amp;viewchart=1';
    if ($result['type'] == "blacklist") {
        $s_url = '';
        $b_url = 'index.php?m=servermonitoring&amp;id=' . mysql_real_escape_string($_REQUEST['id']) . '&amp;mid=' . $result['id'];
    }
    $output .= '<tr ' . $s_url . '>
						<td class="text-center"><strong>' . $result['monitorname'] . '</strong></td>
						<td class="text-center"><strong>' . $url . '</strong></td>
						<td class="text-center"><strong>' . $port . '</strong></td>
						<td class="text-center"><strong>' . $monitorinterval . '</strong></td>
						<td class="text-center">' . $this_location . '</td>
						<td class="text-center">' . $lastmonitor . '</td>
						<td class="text-center">' . $mstatus . '</td>
						<td class="text-center">' . $dstatus . '</td>
						<td class="responsive-edit-button">
							<a href="' . $b_url . '" class="btn btn-block btn-success">
								' . $LANG['stats'] . '
							</a>
						</td>
						<td class="responsive-edit-button">
							<a href="index.php?m=servermonitoring&amp;id=' . mysql_real_escape_string($_REQUEST['id']) . '&amp;mid=' . $result['id'] . '&amp;contacts=true" class="btn btn-block btn-primary">
								' . $LANG['contactlist'] . '
							</a>
						</td>
						<td class="responsive-edit-button">
							<a href="index.php?m=servermonitoring&amp;id=' . mysql_real_escape_string($_REQUEST['id']) . '&amp;mid=' . $result['id'] . '&amp;edit=true&amp;type=' . $result['type'] . '" class="btn btn-block btn-warning">
								' . $LANG['edit'] . '
							</a>
						</td>
					</tr>';
}

$output .= '</tbody>
    </table>
</div>';
$output .= '
    <hr>
<div class="table-container clearfix">
    <center>' . $LANG['MonitoringLogs'] . '</center></br>
    <table id="tableServicesList" class="table table-list">
        <thead>
            <tr>
                <th>' . $LANG['id'] . '</th>
                <th>' . $LANG['type'] . '</th>
                <th>' . $LANG['message'] . '</th>
                <th>' . $LANG['receiver'] . '</th>
            </tr>
        </thead>
        <tbody>';
$result = full_query("SELECT * FROM mod_servermonitoring_logs  INNER JOIN mod_servermonitoring_services ON mod_servermonitoring_services.uid = '" . $_SESSION['uid'] . "' AND mod_servermonitoring_logs.serviceid = mod_servermonitoring_services.id ORDER BY mod_servermonitoring_logs.id DESC LIMIT 0,20");
$i = 20;
while ($data = mysql_fetch_assoc($result)) {
    $serviceid = $data['serviceid'];
    $querya = mysql_query("SELECT `serviceid`,`uid` FROM `mod_servermonitoring_services` WHERE `id`='" . $serviceid . "'");
    $service = mysql_fetch_array($querya);
    $userid = $service['uid'];
    $queryb = mysql_query("SELECT `packageid` FROM `tblhosting` WHERE `id`='" . $service['serviceid'] . "'");
    $hosting = mysql_fetch_array($queryb);
    $queryc = mysql_query("SELECT `name` FROM `tblproducts` WHERE `id`='" . $hosting['packageid'] . "'");
    $product = mysql_fetch_array($queryc);
    if (Capsule::schema()->hasTable('tblmodule_configuration')) {
        $checkisaddon = Capsule::table('tblhostingaddons')->where('hostingid', $service['serviceid'])->join('tbladdons', 'tblhostingaddons.addonid', '=', 'tbladdons.id')->where('module', 'serverMonitoring')->select('addonid')->first();
        if (count($checkisaddon) > 0) {
            $plist = Capsule::table('tbladdons')->where('id', $checkisaddon->addonid)->select('name')->first();
            if (count($plist) > 0) {
                $name = $plist->name;
            }
        }
    }
    $queryd = mysql_query("SELECT `firstname`,`lastname` FROM `tblclients` WHERE `id`='" . $service['uid'] . "'");
    $client = mysql_fetch_array($queryd);
    $clientname = '<a href="clientssummary.php?userid=' . $userid . '">' . ucwords($client['firstname'] . " " . $client['lastname']) . "</a>";
    $servicename = '<a href="clientsservices.php?userid=' . $userid . '&id=' . $service['serviceid'] . '">' . $product['name'] . '</a>';
    $output .= '<tr>
			<td class="text-center"><center><strong>' . $i . '</strong></center></td>
			<td class="text-center"><center><strong>' . ucwords($data['type']) . '</strong></center></td>
			<td class="text-center"><center><strong>' . $data['message'] . '</strong></center></td>
			<td class="text-center"><center>' . strtolower($data['recipient']) . '</center></td>
                    </tr>';
    $i--;
}
$output .= '</tbody>
    </table>
</div>';
$rss = '';
if ($settings['rss_enable'] == "on")
    $rss = '<a href="index.php?m=servermonitoring&page=RSS&id=' . $_SESSION['uid'] . '"><button class="btn btn-info" type="button">' . $LANG['rssactivities'] . '</button></a>';
$output .= '<p align="center"><a href="index.php?m=servermonitoring"><button class="btn btn-danger" type="button">' . $LANG['goback'] . '</button></a>&nbsp&nbsp' . $rss . '</p>';
?>