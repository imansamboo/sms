<?php

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

use Illuminate\Database\Capsule\Manager as Capsule;

$output .= '
<div class="table-container clearfix">
    <table id="tableServicesList" class="table table-list">
        <thead>
            <tr>
                <th>' . $LANG['productservice'] . '</th>
                <th>' . $LANG['interval'] . '</th>
                <th>' . $LANG['limit'] . '</th>
                <th>' . $LANG['pricing'] . '</th>
                <th>' . $LANG['due'] . '</th>
                <th>' . $LANG['status'] . '</th>
                <th class="responsive-edit-button"></th>
            </tr>
        </thead>
        <tbody>';

$query = mysql_query("SELECT * FROM `mod_servermonitoring_services` WHERE `uid`='" . $_SESSION['uid'] . "'");
while ($result = mysql_fetch_assoc($query)) {

    $querya = mysql_query("SELECT * FROM `tblhosting` WHERE `id`='" . $result['serviceid'] . "' AND `userid`='" . $_SESSION['uid'] . "'");
    $hosting = mysql_fetch_assoc($querya);

    $queryb = mysql_query("SELECT `currency` FROM `tblclients` WHERE `id`='" . $_SESSION['uid'] . "'");
    $client = mysql_fetch_assoc($queryb);

    $queryc = mysql_query("SELECT `prefix`,`suffix` FROM `tblcurrencies` WHERE `id`='" . $client['currency'] . "'");
    $currency = mysql_fetch_assoc($queryc);
    $suffix = $currency['suffix'];
    $prefix = $currency['prefix'];

    $queryd = mysql_query("SELECT * FROM `tblproducts` WHERE `id`='" . $hosting['packageid'] . "'");
    $products = mysql_fetch_assoc($queryd);
    if (Capsule::schema()->hasTable('tblmodule_configuration')) {
        $checkisaddon = Capsule::table('tblhostingaddons')->where('hostingid', $result['serviceid'])->join('tbladdons', 'tblhostingaddons.addonid', '=', 'tbladdons.id')->where('module', 'serverMonitoring')->select('addonid')->first();
        if (count($checkisaddon) > 0) {
            $plist = Capsule::table('tblmodule_configuration')->where('entity_type', 'addon')->where('entity_id', $checkisaddon->addonid)->get();
            if (count($plist) > 0) {
                foreach ($plist as $value) {
                    $products[$value->setting_name] = $value->value;
                }
            }
            $pname = Capsule::table('tbladdons')->where('id', $checkisaddon->addonid)->select('name')->first();
            $products['name'] = $pname->name;
        }
    }

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
    if ($hosting['nextduedate'] == "0000-00-00")
        $nextdue = '<span class="hidden">0000-00-00</span>-';
    else
        $nextdue = '<span>' . $hosting['nextduedate'] . '</span>';
    $amount = $hosting['amount'];
    if ($amount == '0.00')
        $amount = $prefix . '0.00 ' . $suffix . '<br />Free Account';
    else
        $amount = $amount = $prefix . $amount . ' ' . $suffix . '';
    $cquery = mysql_query("SELECT count(*) from `mod_servermonitoring_monitors` WHERE `serviceid`='" . $result['serviceid'] . "'");
    $cquery = mysql_query($cquery);

    $cresult = mysql_query("SELECT COUNT(*) AS `count` FROM `mod_servermonitoring_monitors` WHERE `serviceid`='" . $result['id'] . "'");
    $rcow = mysql_fetch_assoc($cresult);
    $ccount = $rcow['count'];

    $output .= '<tr onclick="clickableSafeRedirect(event, \'index.php?m=servermonitoring&amp;id=' . $result['serviceid'] . '\', false)">
						<td class="text-center"><strong>' . $products['name'] . '</strong></td>
						<td class="text-center"><strong>' . $products['configoption1'] . '</strong></td>
						<td class="text-center"><strong>' . $ccount . '/' . $products['configoption2'] . '</strong></td>
						<td class="text-center">' . $amount . '</td>
						<td class="text-center">' . $nextdue . '</td>
						<td class="text-center">' . $dstatus . '</td>
						<td class="responsive-edit-button">
							<a href="index.php?m=servermonitoring&amp;id=' . $result['serviceid'] . '" class="btn btn-block btn-info">
								' . $LANG['manage'] . '
							</a>
						</td>
					</tr>';
}

$output .= '</tbody>
    </table>
</div>';
?>