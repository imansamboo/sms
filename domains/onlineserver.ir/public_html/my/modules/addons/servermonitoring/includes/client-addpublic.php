<?php

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

use Illuminate\Database\Capsule\Manager as Capsule;

global $CONFIG;
$error = '';
if (isset($_POST['pagetitle']) && isset($_POST['monitors']) && isset($_REQUEST['id']) && is_numeric($_REQUEST['id']) && !empty($_POST['pagetitle']) && !empty($_POST['monitors'])) {
    $pagetitle = mysql_real_escape_string($_POST['pagetitle']);
    $logo = mysql_real_escape_string($_POST['logo']);
    $_POST['monitors'] = implode(',', $_POST['monitors']);
    $query = mysql_query("SELECT * FROM `mod_servermonitoring_services` WHERE `serviceid`='" . mysql_real_escape_string($_REQUEST['id']) . "' AND `uid`='" . $_SESSION['uid'] . "'");
    $services = mysql_fetch_assoc($query);
    $tot = mysql_num_rows($query);
    $querya = mysql_query("SELECT `id` FROM `mod_servermonitoring_monitors` WHERE `serviceid`='" . $services['id'] . "'");
    $tota = mysql_num_rows($querya);
    $qu = mysql_query("SELECT `id` FROM `mod_servermonitoring_pages` WHERE `uid`='" . $_SESSION['uid'] . "'");
    $to = mysql_num_rows($querya);
    $queryb = mysql_query("SELECT `packageid` FROM `tblhosting` WHERE `id`='" . $services['serviceid'] . "' AND `userid`='" . $_SESSION['uid'] . "'");
    $hosting = mysql_fetch_assoc($queryb);
    $queryc = mysql_query("SELECT `configoption11` FROM `tblproducts` WHERE `id`='" . $hosting['packageid'] . "'");
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
    $checking = $products['configoption11'];
    if ($checking == 'none')
        $checking = 0;
    if ($checking == 'unlimited')
        $checking = 10000000;
    $logo = $CONFIG['LogoURL'];
    if (isset($_FILES['logo'])) {
        $check = getimagesize($_FILES["logo"]["tmp_name"]);
        if ($check !== false) {
            $path_parts = pathinfo($_FILES["logo"]["name"]);
            $extension = $path_parts['extension'];
            $logo = "/modules/addons/servermonitoring/images/logo-pageid-" . md5(time()) . '.' . $extension;
            $target_upload = ROOTDIR . $logo;
            if (!move_uploaded_file($_FILES["logo"]["tmp_name"], $target_upload)) {
                $error = $LANG['PublicPagesUpload'];
            }
        } else {
            $error = $LANG['PublicPagesUpload'];
        }
    }
    if ($tot == 1 && $to <= $checking && $error == '') {
        $query = mysql_query("INSERT INTO `mod_servermonitoring_pages` SET `title`='" . $pagetitle . "',`monitors`='" . $_POST['monitors'] . "', `logo`='" . $logo . "', `accesskey`='" . md5(time()) . "', `uid`='" . $_SESSION['uid'] . "', `status`='1', `service_id`='" . (int) $_REQUEST['id'] . "'");
        redir('m=servermonitoring&id=' . $_REQUEST['id'] . '&page=publicpages&success=true');
    } else {
        if ($error == '')
            $error = $LANG['checkfields'];
    }
}
$output .= '<center><div style="width:50%"><form  enctype="multipart/form-data" action="index.php?m=servermonitoring&id=' . $_REQUEST['id'] . '&addpage=new&page=publicpages&save=true" method="post">';
if (!empty($error) && trim($error) != '') {
    $output .= '<div class="alert alert-danger"><center>' . $error . '</center></div>';
}
$ldropdown = '';
$querye = mysql_query("SELECT * FROM `mod_servermonitoring_services` WHERE `uid`='" . $_SESSION['uid'] . "' AND `serviceid`='" . mysql_real_escape_string($_REQUEST['id']) . "'");
$services = mysql_fetch_assoc($querye);
$query = mysql_query("SELECT * FROM `mod_servermonitoring_monitors` WHERE `serviceid`='" . $services['id'] . "'");
while ($result = mysql_fetch_assoc($query)) {
    $ldropdown .= '<option value="' . $result['id'] . '">' . $result['monitorname'] . '</option>';
}
$output .= '<span><strong>' . $LANG['PublicPagesTitle'] . '</strong></span><p><input type="text" name="pagetitle" value="" class="form-control" placeholder="' . $LANG['PublicPagesTitle'] . '"></p>';
$output .= '<span><strong>' . $LANG['PublicPagesIncludes'] . '</strong></span><p><select name="monitors[]" multiple class="form-control">' . $ldropdown . '</select></p>';
$output .= '<span><strong>' . $LANG['PublicPagesLogoUrl'] . '</strong></span><p><input type="file" name="logo" class="form-control"></p>';
$output .= '<p><button class="btn btn-success" type="submit">' . $LANG['savechanges'] . '</button>&nbsp;&nbsp;<a href="index.php?m=servermonitoring&id=' . $_REQUEST['id'] . '"><button class="btn btn-danger" type="button">' . $LANG['goback'] . '</button></a></p></form></div></center>';
