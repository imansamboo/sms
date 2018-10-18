<?php

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "delete" && isset($_REQUEST['id']) && is_numeric($_REQUEST['id'])) {
    $id = mysql_real_escape_string($_REQUEST['id']);
    $query = mysql_query("DELETE FROM `mod_servermonitoring_smspackages` WHERE `id`='" . $id . "'");
    redir("module=servermonitoring&a=smspackages&success=true");
    exit;
}

echo '<h2 align="center" style="color:grey;"><strong>' . $LANG['smspackages'] . '</strong></h2><p align="right"><a href="addonmodules.php?module=servermonitoring&a=smspackages&add=true"><button type="button" class="btn btn-success">' . $LANG['addnew'] . '</button></a></p>';

$aInt->sortableTableInit("id");
$numrows = get_query_val("mod_servermonitoring_smspackages", "COUNT(*)", "", "id", "ASC");
$limit = "50";
$page = $_REQUEST['page'];
if (empty($page) || !is_numeric($page))
    $page = 0;
else
    $page = $page;
$page = "" . $page . "";

$result = select_query("mod_servermonitoring_smspackages", "", "", "id", "ASC", $page * $limit . "," . $limit);
while ($data = mysql_fetch_assoc($result)) {
    $pricing = '<a href="addonmodules.php?module=servermonitoring&a=smspackages&edit=true&id=' . $data['id'] . '">' . $LANG['changepricing'] . '</a>';

    $tabledata[] = array("<center>#" . $data['id'] . "</center>", "<center>" . $data['credits'] . "</center>", "<center>" . $pricing . "</center>", "<center>" . $data['description'] . "</center>", '<a href="addonmodules.php?module=servermonitoring&a=smspackages&edit=true&id=' . $data['id'] . '"><center><img src="images/edit.gif"></center></a>', '<a href="' . $modulelink . '&a=smspackages&action=delete&id=' . $data['id'] . '" onclick="return confirm(\'' . $LANG['deletesmspackagewarn'] . '\');"><center><img src="images/delete.gif"></center></a>');
}

echo $aInt->sortableTable(array($LANG['id'], $LANG['smscredits'], $LANG['pricing'], $LANG['description'], "", ""), $tabledata);
?>