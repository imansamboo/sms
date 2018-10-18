<?php

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

use Illuminate\Database\Capsule\Manager as Capsule;

if (isset($_REQUEST['delete']) && isset($_REQUEST['cid']) && is_numeric($_REQUEST['cid'])) {
    $id = $_REQUEST['cid'];
    Capsule::table('mod_servermonitoring_contacts')->where('id', $id)->delete();
}

if (!is_numeric($_REQUEST['id']) || !is_numeric($_REQUEST['mid'])) {
    redir("m=servermonitoring");
    exit;
}

$output .= '<p align="right"><a href="index.php?m=servermonitoring&id=' . $_REQUEST['id'] . '&mid=' . $_REQUEST['mid'] . '&addcontacts=true"><button type="button" class="btn btn-success">' . $LANG['contactadd'] . '</button></a></p>';
$output .= '
<div class="table-container clearfix">
    <table id="tableServicesList" class="table table-list">
        <thead>
            <tr>
                <th>' . $LANG['contactname'] . '</th>
                <th>' . $LANG['contactemail'] . '</th>
                <th>' . $LANG['to'] . '</th>
                <th class="responsive-edit-button"></th>
                <th class="responsive-edit-button"></th>
            </tr>
        </thead>
        <tbody>';
$list = Capsule::table('mod_servermonitoring_contacts')->where('mid', $_REQUEST['mid'])->orderBy('id', 'DESC')->get();
foreach ($list as $data) {
    $output .= '<tr>
						<td class="text-center"><strong>' . $data->name . ' ' . $data->family . '</strong></td>
						<td class="text-center"><strong>' . $data->email . '</strong></td>
						<td class="text-center"><strong>' . $data->phonenumber . '</strong></td>
						<td class="responsive-edit-button">
							<a href="index.php?m=servermonitoring&id=' . $_REQUEST['id'] . '&mid=' . $_REQUEST['mid'] . '&cid=' . $data->id . '&editcontacts=true" class="btn btn-block btn-info">
								' . $LANG['edit'] . '
							</a>
						</td>
						<td class="responsive-edit-button">
							<a href="index.php?m=servermonitoring&amp;id=' . $_REQUEST['id'] . '&amp;mid=' . $_REQUEST['mid'] . '&cid=' . $data->id . '&contacts=true&delete=true" onclick="return confirm(\'' . $LANG['deletecontact'] . '\');" class="btn btn-block btn-danger">
								' . $LANG['delete'] . '
							</a></tr>';
}
$output .= '</tbody>
    </table>
</div>';
$output .= '<p align="center"><a href="index.php?m=servermonitoring&id=' . $_REQUEST['id'] . '"><button class="btn btn-danger" type="button">' . $LANG['goback'] . '</button></a></p></form></div></center>';
?>