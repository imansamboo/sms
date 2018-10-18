<?php

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

use Illuminate\Database\Capsule\Manager as Capsule;

$querys = mysql_query("SELECT `id`,`serviceid` FROM `mod_servermonitoring_services` WHERE `serviceid`='" . mysql_real_escape_string($_REQUEST['id']) . "' AND `uid`='" . mysql_real_escape_string($_SESSION['uid']) . "' LIMIT 1");
$services = mysql_fetch_assoc($querys);
$queryb = mysql_query("SELECT `packageid` FROM `tblhosting` WHERE `id`='" . $services['serviceid'] . "' AND `userid`='" . $_SESSION['uid'] . "'");
$hosting = mysql_fetch_assoc($queryb);
$queryc = mysql_query("SELECT `configoption10` FROM `tblproducts` WHERE `id`='" . $hosting['packageid'] . "'");
$products = mysql_fetch_assoc($queryc);
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
if ($products['configoption10'] != "on") {
    redir("m=servermonitoring&id=" . $_REQUEST['id']);
    exit;
}

if (isset($_REQUEST['delete']) && $_REQUEST['delete'] && isset($_REQUEST['maid']) && isset($_REQUEST['id'])) {
    $mid = mysql_real_escape_string($_REQUEST['mid']);
    $serviceid = mysql_real_escape_string($_REQUEST['id']);
    $maid = mysql_real_escape_string($_REQUEST['maid']);
    $query = mysql_query("SELECT `id` FROM `mod_servermonitoring_services` WHERE `serviceid`='" . $serviceid . "' AND `uid`='" . $_SESSION['uid'] . "'");
    $services = mysql_fetch_assoc($query);
    $queryb = mysql_query("SELECT `id` FROM `mod_servermonitoring_maintenance` WHERE `serviceid`='" . $services['id'] . "' AND `mid`='" . mysql_real_escape_string($_REQUEST['mid']) . "' AND `id`='" . $maid . "'");
    $maintenance = mysql_fetch_assoc($queryb);
    $tot = mysql_num_rows($queryb);
    if ($tot == 1) {
        $query = mysql_query("DELETE FROM `mod_servermonitoring_maintenance` WHERE `id`='" . $maintenance['id'] . "'");
        header("Location: index.php?m=servermonitoring&id=" . $_REQUEST['id'] . "&mid=" . $_REQUEST['mid'] . "&maintenance=true&success=delete");
        exit;
    }
}

$output .= '<p align="right"><a href="index.php?m=servermonitoring&id=' . $_REQUEST['id'] . '&mid=' . $_REQUEST['mid'] . '&addmaintenance=true"><button type="button" class="btn btn-success">' . $LANG['addmaintenance'] . '</button></a></p>';
$output .= '
<div class="table-container clearfix">
    <table id="tableServicesList" class="table table-list">
        <thead>
            <tr>
                <th>' . $LANG['message'] . '</th>
                <th>' . $LANG['from'] . '</th>
                <th>' . $LANG['to'] . '</th>
                <th>' . $LANG['status'] . '</th>
                <th class="responsive-edit-button"></th>
                <th class="responsive-edit-button"></th>
            </tr>
        </thead>
        <tbody>';
$querya = mysql_query("SELECT `id` FROM `mod_servermonitoring_services` WHERE `uid`='" . $_SESSION['uid'] . "' AND `serviceid`='" . mysql_real_escape_string($_REQUEST['id']) . "'");
$services = mysql_fetch_assoc($querya);
$query = mysql_query("SELECT * FROM `mod_servermonitoring_maintenance` WHERE `serviceid`='" . $services['id'] . "' AND `mid`='" . mysql_real_escape_string($_REQUEST['mid']) . "'");
while ($result = mysql_fetch_assoc($query)) {
    if ($result['status'] == "1")
        $status = '<span class="label status status-active">' . $LANG['pending'] . '</span>';
    else
        $status = '<span class="label status status-pending">' . $LANG['completed'] . '</span>';

    $output .= '<tr>
						<td class="text-center"><strong>' . $result['message'] . '</strong></td>
						<td class="text-center"><strong>' . date('M d, Y H:i', $result['from']) . '</strong></td>
						<td class="text-center"><strong>' . date('M d, Y H:i', $result['to']) . '</strong></td>
						<td class="text-center">' . $status . '</td>
						<td class="responsive-edit-button">
							<a href="index.php?m=servermonitoring&amp;id=' . $_REQUEST['id'] . '&amp;mid=' . $result['mid'] . '&amp;maid=' . $result['id'] . '&editmaintenance=true" class="btn btn-block btn-info">
								' . $LANG['edit'] . '
							</a>
						</td>
						<td class="responsive-edit-button">
							<a href="index.php?m=servermonitoring&amp;id=' . $_REQUEST['id'] . '&amp;mid=' . $result['mid'] . '&amp;maid=' . $result['id'] . '&maintenance=true&delete=true" onclick="return confirm(\'' . $LANG['deletemaintenancewarn'] . '\');" class="btn btn-block btn-danger">
								' . $LANG['delete'] . '
							</a>
						</td>
					</tr>';
}

$output .= '</tbody>
    </table>
</div>';
$querya = mysql_query("SELECT `type` FROM `mod_servermonitoring_monitors` WHERE `serviceid`='" . $services['id'] . "' AND `id`='" . mysql_real_escape_string($_REQUEST['mid']) . "'");
$monitors = mysql_fetch_assoc($querya);
$output .= '<p align="center"><a href="index.php?m=servermonitoring&id=' . $_REQUEST['id'] . '&mid=' . $_REQUEST['mid'] . '&edit=true&type=' . $monitors['type'] . '"><button class="btn btn-danger" type="button">' . $LANG['goback'] . '</button></a></p></form></div></center>';
?>