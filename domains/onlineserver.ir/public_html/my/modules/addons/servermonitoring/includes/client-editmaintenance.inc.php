<?php
if (!defined("WHMCS")) die("This file cannot be accessed directly");

if (isset($_REQUEST['save']) && $_REQUEST['save'] && isset($_REQUEST['to']) && isset($_REQUEST['from']) && isset($_REQUEST['maid']) && is_numeric($_REQUEST['maid']) && isset($_REQUEST['message'])) {
	$to = mysql_real_escape_string($_REQUEST['to']);
	$to = DateTime::createFromFormat('m-d-Y H:i', $to);
	$to = ''.$to->getTimestamp();
	$from = mysql_real_escape_string($_REQUEST['from']);
	$from = DateTime::createFromFormat('m-d-Y H:i', $from);
	$from = ''.$from->getTimestamp();
	$message = mysql_real_escape_string($_REQUEST['message']);
	$mid = mysql_real_escape_string($_REQUEST['mid']);
	$serviceid = mysql_real_escape_string($_REQUEST['id']);
	$maid = mysql_real_escape_string($_REQUEST['maid']);
	$query = mysql_query("SELECT `id` FROM `mod_servermonitoring_services` WHERE `serviceid`='".$serviceid."' AND `uid`='".$_SESSION['uid']."'");
	$services = mysql_fetch_assoc($query);
	$querya = mysql_query("SELECT `id` FROM `mod_servermonitoring_maintenance` WHERE `serviceid`='".$services['id']."' AND `mid`='".mysql_real_escape_string($_REQUEST['mid'])."' AND `id`='".$maid."'");
	$maintenance = mysql_fetch_assoc($querya);
	$tot = mysql_num_rows($querya);
	if ($tot == 1) {
		$query = mysql_query("UPDATE `mod_servermonitoring_maintenance` SET `serviceid`='".$services['id']."', `mid`='".$mid."',`to`='".$to."', `from`='".$from."', `message`='".$message."', `status`='1' WHERE `id`='".$maintenance['id']."'");
		header("Location: index.php?m=servermonitoring&id=".$_REQUEST['id']."&mid=".$_REQUEST['mid']."&maintenance=true&success=edit");
		exit;
	}
}
$output .= '<link rel="stylesheet" href="modules/addons/servermonitoring/css/kendo.common.min.css">';
$output .= '<link rel="stylesheet" href="modules/addons/servermonitoring/css/kendo.default.min.css">';
$output .= '<script src="modules/addons/servermonitoring/js/kendo.all.min.js"></script>';

$query = mysql_query("SELECT `id` FROM `mod_servermonitoring_services` WHERE `serviceid`='".mysql_real_escape_string($_REQUEST['id'])."' AND `uid`='".$_SESSION['uid']."'");
$services = mysql_fetch_assoc($query);
$querya = mysql_query("SELECT * FROM `mod_servermonitoring_maintenance` WHERE `serviceid`='".$services['id']."' AND `mid`='".mysql_real_escape_string($_REQUEST['mid'])."' AND `id`='".mysql_real_escape_string($_REQUEST['maid'])."'");
$maintenance = mysql_fetch_assoc($querya);

$to = $maintenance['to'];
$from = $maintenance['from'];

$output .= '<form action="'.$modulelink.'&id='.$_REQUEST['id'].'&mid='.$_REQUEST['mid'].'&maid='.$_REQUEST['maid'].'&editmaintenance=true&save=true" method="post"><pre style="text-align:center;">';
$output .= '<center><h2>'.$LANG['editmaintenance'].'</h2><div style="width:50%;">';

$output .= '<p><input id="from" name="from" style="width: 100%;" /></p>';
$output .= '<p><input id="to" name="to" style="width: 100%;" /></p>';
			
$output .= '<p><input type="text" class="form-control" name="message" value="'.$maintenance['message'].'" placeholder="'.$LANG['message'].'" style="margin-bottom:10px;"></p></div></center>';
$output .= '<br><p align="center"><button class="btn btn-success" type="submit" style="margin-top: 10px;">'.$LANG['editmaintenance'].'</button>&nbsp;&nbsp;<a href="index.php?m=servermonitoring&id='.$_REQUEST['id'].'&mid='.$_REQUEST['mid'].'&maintenance=true"><button class="btn btn-danger" type="button" style="margin-top: 10px;">'.$LANG['goback'].'</button></a></p>';
$output .= '</pre></form>';
$output .= '            <script>
                $(document).ready(function () {
                    $("#to").kendoDateTimePicker({
                        value: "'.date('m-d-Y H:i',$to).'",
						format: "MM-dd-yyyy HH:mm"
                    });
                    $("#from").kendoDateTimePicker({
                        value: "'.date('m-d-Y H:i',$from).'",
						format: "MM-dd-yyyy HH:mm"
                    });
                });
            </script>';