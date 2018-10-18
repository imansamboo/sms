<?php

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");
global $CONFIG;
if (isset($_POST['pagetitle']) && isset($_POST['monitors']) && isset($_GET['pageid']) && isset($_REQUEST['id']) && is_numeric($_REQUEST['id']) && !empty($_POST['pagetitle']) && !empty($_POST['monitors'])) {
    $pagetitle = mysql_real_escape_string($_POST['pagetitle']);
    //$logo = mysql_real_escape_string($_POST['logo']);
    $status = mysql_real_escape_string($_POST['status']);
    $_POST['monitors'] = implode(',', $_POST['monitors']);
    if (isset($_FILES['logo']) && $_FILES['logo']['size'] > 0) {
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
    } else {
        $quer = mysql_query("SELECT * FROM `mod_servermonitoring_pages` WHERE `uid`='" . $_SESSION['uid'] . "' AND `id`='" . mysql_real_escape_string($_REQUEST['pageid']) . "'");
        $page = mysql_fetch_assoc($quer);
        $logo = $page['logo'];
    }

    $query = mysql_query("UPDATE `mod_servermonitoring_pages` SET `title`='" . $pagetitle . "',`monitors`='" . $_POST['monitors'] . "', `logo`='" . $logo . "', `uid`='" . $_SESSION['uid'] . "', `status`='" . $status . "', `service_id`='" . (int) $_REQUEST['id'] . "' WHERE id='" . (int) $_REQUEST['pageid'] . "'");
    redir('m=servermonitoring&id=' . $_REQUEST['id'] . '&page=publicpages&success=true');
}
if (isset($_GET['delete'])) {
    $quer = mysql_query("SELECT * FROM `mod_servermonitoring_pages` WHERE `uid`='" . $_SESSION['uid'] . "' AND `id`='" . mysql_real_escape_string($_REQUEST['pageid']) . "'");
    $page = mysql_fetch_assoc($quer);
    @unlink(ROOTDIR .'/modules/addons/servermonitoring/images/'. $page['logo']);
    $query = mysql_query("DELETE from `mod_servermonitoring_pages` WHERE id='" . (int) $_REQUEST['pageid'] . "'");
    redir('m=servermonitoring&id=' . $_REQUEST['id'] . '&page=publicpages&success=true');
}
$output .= '<p align="center">';
$output .= '<a href="' . $modulelink . '&id=' . $_REQUEST['id'] . '&pageid=' . $_REQUEST['pageid'] . '&page=publicpages&edit=1&delete=1"><button class="btn btn-danger"  onclick="return confirm(\' ' . $LANG['PublicPagesDelete'] . ' \');" type="button">' . $LANG['delete'] . '</button></a>&nbsp;&nbsp;';
$output .= '<center><div style="width:50%"><form enctype="multipart/form-data" action="index.php?m=servermonitoring&id=' . $_REQUEST['id'] . '&pageid=' . $_REQUEST['pageid'] . '&edit=1&page=publicpages&save=true" method="post">';
if (!empty($error) && trim($error) != '') {
    $output .= '<div class="alert alert-danger"><center>' . $error . '</center></div>';
}
$quer = mysql_query("SELECT * FROM `mod_servermonitoring_pages` WHERE `uid`='" . $_SESSION['uid'] . "' AND `id`='" . mysql_real_escape_string($_REQUEST['pageid']) . "'");
$page = mysql_fetch_assoc($quer);
$query = mysql_query("SELECT * FROM `mod_servermonitoring_monitors` WHERE `serviceid`='" . $services['id'] . "'");
$select = explode(',', $page['monitors']);
$ldropdown = '';
while ($result = mysql_fetch_assoc($query)) {
    $selected = (in_array($result['id'], $select) ? 'selected' : '');
    $ldropdown .= '<option value="' . $result['id'] . '" ' . $selected . '>' . $result['monitorname'] . '</option>';
}
$img = '<img src="' . $CONFIG['SystemURL'] . $page['logo'] . '" width="100" height="100">';

$output .= '<span><strong>' . $LANG['PublicPagesTitle'] . '</strong></span><p><input type="text" name="pagetitle" value="' . $page['title'] . '" class="form-control" placeholder="' . $LANG['PublicPagesTitle'] . '"></p>';
$output .= '<span><strong>' . $LANG['PublicPagesIncludes'] . '</strong></span><p><select name="monitors[]" multiple class="form-control">' . $ldropdown . '</select></p>';
$output .= '<span><strong>' . $LANG['PublicPagesLogoUrl'] . '</strong></span><p>' . $img . '<br><input type="file" name="logo" value="' . $page['logo'] . '" class="form-control" placeholder="' . $LANG['PublicPagesLogoUrl'] . '"></p>';
$output .= '<span><strong>' . $LANG['status'] . '</strong></span><p><select name="status" class="form-control"><option value="1" ' . (($result['status'] == '1') ? 'selected' : '') . '>' . $LANG['active'] . '</option><option ' . (($result['status'] != '1') ? 'selected' : '') . ' value="0">' . $LANG['suspended'] . '</option></select></p>';
$output .= '<p><button class="btn btn-success" type="submit">' . $LANG['savechanges'] . '</button>&nbsp;&nbsp;<a href="index.php?m=servermonitoring&page=publicpages&id=' . $_REQUEST['id'] . '"><button class="btn btn-danger" type="button">' . $LANG['goback'] . '</button></a></p></form></div></center>';