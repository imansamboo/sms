<?php

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");
use Illuminate\Database\Capsule\Manager as Capsule;
global $CONFIG;
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
$checking = $products['configoption11'];
if ($checking == 'none')
    $checking = 0;
if ($checking == 'unlimited')
    $checking = 10000000;
$settings = servermonitoring_settings($vars);

if (isset($_REQUEST['addpage']) && $_REQUEST['addpage'] == 'new') {
    include('client-addpublic.php');
} else if (isset($_REQUEST['edit'])) {
    include('client-editpublic.php');
} else {
    $output .= '<p align="right">';
    if ($products['configoption11'] != "none") {
        $querye = mysql_query("SELECT id FROM `mod_servermonitoring_pages` WHERE `uid`='" . $_SESSION['uid'] . "' AND `service_id`='" . mysql_real_escape_string($_REQUEST['id']) . "'");
        if (mysql_num_rows($querye) <= $checking)
            $output .= '<a href="' . $modulelink . '&id=' . $_REQUEST['id'] . '&page=publicpages&addpage=new"><button class="btn btn-primary" type="button">' . $LANG['AddPublicPages'] . '</button></a>&nbsp;&nbsp;';
    }
    $output .= '
<div class="table-container clearfix">
    <table id="tableServicesList" class="table table-list">
        <thead>
            <tr>
                <th>' . $LANG['id'] . '</th>
                <th>' . $LANG['PublicPagesName'] . '</th>
                <th>' . $LANG['monitors'] . '</th>
                <th>' . $LANG['status'] . '</th>
                <th>' . $LANG['PublicPagesLogo'] . '</th>                    
                <th class="responsive-view-button"></th>
                <th class="responsive-edit-button"></th>
            </tr>
        </thead>
        <tbody>';
    $service = (int) $_REQUEST['id'];
    $query = mysql_query("SELECT * FROM `mod_servermonitoring_pages` WHERE `service_id`='" . $service . "' AND `uid`='" . $_SESSION['uid'] . "' ORDER BY id DESC");
    while ($result = mysql_fetch_assoc($query)) {
        $m_list = explode(',', $result['monitors']);
        $monitors = '';
        foreach ($m_list as $monitor) {
            $querya = mysql_query("SELECT monitorname,id,accesskey FROM `mod_servermonitoring_monitors` WHERE `id`='" . $monitor . "'");
            $name = mysql_fetch_assoc($querya);
            if (isset($name['monitorname']))
                $monitors .= '<a href="public-charts.php?mid=' . $name['accesskey'] . '" target="_blank" class="btn btn-sm btn-success">' . $name['monitorname'] . '</a> ';
        }
        $img = '';
        if (isset($result['logo'])) {
            $img = '<img src="' . $CONFIG['SystemURL'] . $result['logo'] . '" width="30" height="30">';
        }
        $output .= '<tr>
						<td class="text-center"><strong>' . $result['id'] . '</strong></td>
                                                    <td class="text-center"><strong>' . $result['title'] . '</strong></td>
						<td class="text-center"><strong>' . $monitors . '</strong></td>
						<td class="text-center"><strong>' . (($result['status'] == '1') ? $LANG['active'] : $LANG['suspended']) . '</strong></td>
						<td class="text-center">' . $img . '</td>  
						<td class="responsive-view-button">
							<a href="public-status.php?statid=' . $result['accesskey'] . '" target="_blank" class="btn btn-block btn-info">
								' . $LANG['PublicPagesLink'] . '
							</a>
						</td>                                                    
						<td class="responsive-edit-button">
							<a href="index.php?m=servermonitoring&amp;id=' . mysql_real_escape_string($_REQUEST['id']) . '&amp;pageid=' . $result['id'] . '&amp;page=publicpages&edit=1" class="btn btn-block btn-warning">
								' . $LANG['edit'] . '
							</a>
						</td>
					</tr>';
    }

    $output .= '</tbody>
    </table>
</div>';
    $output .= '<p align="center"><a href="index.php?m=servermonitoring&id=' . (int) $_REQUEST['id'] . '"><button class="btn btn-danger" type="button">' . $LANG['goback'] . '</button></p>';
}
?>