<?php

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

use Illuminate\Database\Capsule\Manager as Capsule;

//$monitor = 'Test';
//$s = servermonitoring_sendSMS('+16468532140', $monitor, 'up', '10', '19', '0', '', '');
//die("s : " . $s);

if (isset($_REQUEST['submit']) && $_REQUEST['submit']) {
    unset($_POST['submit']);
    if (!isset($_POST['clientQuickCheck']))
        $_POST['clientQuickCheck'] = '';
    if (!isset($_POST['allowPing']))
        $_POST['allowPing'] = '';
    if (!isset($_POST['allowSMS']))
        $_POST['allowSMS'] = '';
    if (!isset($_POST['rss_enable']))
        $_POST['rss_enable'] = '';
    foreach ($_POST AS $key => $value) {
        if ($key == "token")
            continue;
        $value = mysql_real_escape_string($value);
        $key = mysql_real_escape_string($key);
        $query = mysql_query("UPDATE `mod_servermonitoring_settings` SET `value`='" . $value . "' WHERE `setting`='" . $key . "'");
    }
    redir('module=servermonitoring&a=settings&success=true');
}

$settings = servermonitoring_settings($vars);

if ($settings['smsGateway'] == 'bulksms')
    $bulksmsselect = ' selected';
else
    $bulksmsselect = '';
if ($settings['smsGateway'] == 'ecall')
    $ecallselect = ' selected';
else
    $ecallselect = '';
if ($settings['smsGateway'] == 'onverify')
    $onverifyselect = ' selected';
else
    $onverifyselect = '';
if ($settings['smsGateway'] == 'Plivo')
    $Plivoselect = ' selected';
else
    $Plivoselect = '';
if ($settings['smsGateway'] == 'clickatell')
    $clickatellselect = ' selected';
else
    $clickatellselect = '';
if ($settings['smsGateway'] == 'clickatellc')
    $clickatellcselect = ' selected';
else
    $clickatellcselect = '';
if ($settings['smsGateway'] == 'text marketer')
    $textmarketerselect = ' selected';
else
    $textmarketerselect = '';
if (@$settings['smsGateway'] == 'super solutions')
    $supersolutionsselect = ' selected';
else
    $supersolutionsselect = '';
if (@$settings['smsGateway'] == 'synergywholesale')
    $synergywholesaleselect = ' selected';
else
    $synergywholesaleselect = '';
if (@$settings['smsGateway'] == 'mobily.ws')
    $mobily = ' selected';
else
    $mobily = '';
if (@$settings['smsGateway'] == 'skebby.it')
    $skebbyitselect = ' selected';
else
    $skebbyitselect = '';
if ($settings['smsGateway'] == 'Twilio')
    $Twilio = ' selected';
else
    $Twilio = '';
if ($settings['smsGateway'] == 'SpaceSMS')
    $SpaceSMS = ' selected';
else
    $SpaceSMS = '';
if (@$settings['clientQuickCheck'] == 'on')
    $cqcchecked = " checked";
else
    $cqcchecked = "";
if (@$settings['allowPing'] == 'on')
    $pingchecked = " checked";
else
    $pingchecked = "";
if (@$settings['allowSMS'] == 'on')
    $allowsmschecked = " checked";
else
    $allowsmschecked = "";
if (@$settings['public'] == 'on')
    $publicchecked = " checked";
else
    $publicchecked = "";
if (@$settings['maintenance'] == 'on')
    $maintenancechecked = " checked";
else
    $maintenancechecked = "";
if (@$settings['keyword_monitor'] == 'on')
    $keywordchecked = " checked";
else
    $keywordchecked = "";
if (@$settings['rss_enable'] == 'on')
    $rss_enable = " checked";
else
    $rss_enable = "";

echo '<script>function hideAPIID() {
	if (document.getElementById("smsGateway").value != "clickatell"){
    document.getElementById("api_id").style.display = "none";
    document.getElementById("api_idTitle").style.display = "none";
    document.getElementById("api_username").style.display = "block";
    document.getElementById("api_password").style.display = "block";
    document.getElementById("tapi_username").style.display = "block";
    document.getElementById("tapi_password").style.display = "block";
	} else {
    document.getElementById("api_id").style.display = "block";
    document.getElementById("api_idTitle").style.display = "block";
    document.getElementById("api_username").style.display = "none";
    document.getElementById("api_password").style.display = "none";
    document.getElementById("tapi_username").style.display = "none";
    document.getElementById("tapi_password").style.display = "none";
	}
	if (document.getElementById("smsGateway").value == "clickatellc"){
		document.getElementById("api_id").style.display = "block";
		document.getElementById("api_idTitle").style.display = "block";
	}
}</script>';

$cronjob = 'php -q ' . ROOTDIR . '/modules/addons/servermonitoring/crons/cron.php';

echo '<h2 align="center" style="color:grey;"><strong>' . $LANG['settings'] . '</strong></h2>';
echo '<form action="addonmodules.php?module=servermonitoring&a=settings&submit=true" method="post"><center><div style="width:60%">';
echo '<pre style="font-family:\'Open Sans\', Verdana, Tahoma, serif;"><pre style="font-family:\'Open Sans\', Verdana, Tahoma, serif;"><strong>' . $LANG['cronjob'] . '</strong></pre>';
echo '<span>' . $LANG['addcronjob'] . '</span><p><input type="text" value="' . $cronjob . '" placeholder="' . $LANG['cronjob'] . '" class="form-control" readonly></p>';

echo '<pre style="font-family:\'Open Sans\', Verdana, Tahoma, serif;"><pre style="font-family:\'Open Sans\', Verdana, Tahoma, serif;"><strong>' . $LANG['quickchecksettings'] . '</strong></pre>';
echo '<span>' . $LANG['enableclientquickcheck'] . '</span><p><input type="checkbox" id="clientQuickCheck" value="on" name="clientQuickCheck"' . $cqcchecked . '>&nbsp;&nbsp;<label for="clientQuickCheck" style="font-weight:normal;">' . $LANG['yes'] . '</label></p></pre>';

echo '<pre style="font-family:\'Open Sans\', Verdana, Tahoma, serif;"><pre style="font-family:\'Open Sans\', Verdana, Tahoma, serif;"><strong>' . $LANG['allowpingsettings'] . '</strong></pre>';
echo '<span>' . $LANG['enableallowping'] . '</span><p><input type="checkbox" id="allowPing" value="on" name="allowPing"' . $pingchecked . '>&nbsp;&nbsp;<label for="allowPing" style="font-weight:normal;">' . $LANG['yes'] . '</label></p></pre>';

echo '<pre style="font-family:\'Open Sans\', Verdana, Tahoma, serif;"><pre style="font-family:\'Open Sans\', Verdana, Tahoma, serif;"><strong>' . $LANG['rssenable'] . '</strong></pre>';
echo '<span>' . $LANG['rssenabledesc'] . '</span><p><input type="checkbox" id="rss_enable" value="on" name="rss_enable"' . $rss_enable . '>&nbsp;&nbsp;<label for="rss_enable" style="font-weight:normal;">' . $LANG['yes'] . '</label></p></pre>';

echo '<pre style="font-family:\'Open Sans\', Verdana, Tahoma, serif;"><pre style="font-family:\'Open Sans\', Verdana, Tahoma, serif;"><strong>' . $LANG['localhostsettings'] . '</strong></pre>';
echo '<span>' . $LANG['localhostname'] . '</span><p><input type="text" name="localhostName" value="' . $settings['localhostName'] . '" placeholder="' . $LANG['localhostname'] . '" class="form-control"></p></pre>';

echo '<pre style="font-family:\'Open Sans\', Verdana, Tahoma, serif;"><pre style="font-family:\'Open Sans\', Verdana, Tahoma, serif;"><strong>' . $LANG['charttime'] . '</strong></pre>';
echo '<span>' . $LANG['charttimedesc'] . '</span><p><input type="text" name="chart_time" value="' . $settings['chart_time'] . '" placeholder="' . $LANG['charttimedesc'] . '" class="form-control"></p></pre>';

echo '<pre style="font-family:\'Open Sans\', Verdana, Tahoma, serif;"><pre style="font-family:\'Open Sans\', Verdana, Tahoma, serif;"><strong>' . $LANG['linkus'] . '</strong></pre>';
echo '<span>' . $LANG['linkus'] . '<br><i>' . $LANG['linkusadmin'] . '</i></span><p><textarea name="banner" class="form-control">' . $settings['banner'] . '</textarea ></p></pre>';

echo '<pre style="font-family:\'Open Sans\', Verdana, Tahoma, serif;"><pre style="font-family:\'Open Sans\', Verdana, Tahoma, serif;"><strong>' . $LANG['enabledisablesms'] . '</strong></pre>';
echo '<span>' . $LANG['enabledisablesmsdesc'] . '</span><p><input type="checkbox" id="allowSMS" value="on" name="allowSMS"' . $allowsmschecked . '>&nbsp;&nbsp;<label for="allowSMS" style="font-weight:normal;">' . $LANG['yes'] . '</label></p></pre>';


if ($settings['allowSMS'] == 'on') {
    echo '<pre style="font-family:\'Open Sans\', Verdana, Tahoma, serif;"><pre style="font-family:\'Open Sans\', Verdana, Tahoma, serif;"><strong>' . $LANG['smsgwsettings'] . '</strong></pre>';
    echo '<span>' . $LANG['smsGateway'] . '</span><p><select onchange="hideAPIID()" id="smsGateway" name="smsGateway" class="form-control"><option value="text marketer"' . $textmarketerselect . '>Text Marketer</option><option value="bulksms"' . $bulksmsselect . '>BulkSMS</option><option value="synergywholesale"' . $synergywholesaleselect . '>Synergywholesale</option><option value="clickatell"' . $clickatellselect . '>Clickatell Platform</option><option value="clickatellc"' . $clickatellcselect . '>Clickatell Communicator / Central</option><option value="onverify"' . $onverifyselect . '>onverify</option><option value="ecall"' . $ecallselect . '>ecall.ch</option><option value="Plivo"' . $Plivoselect . '>Plivo</option><option value="super solutions"' . $supersolutionsselect . '>Super Solutions</option><option value="skebby.it"' . $skebbyitselect . '>Skebby.it</option><option value="mobily.ws" ' . $mobily . '>mobily.ws</option><option value="Twilio" ' . $Twilio . '>Twilio</option><option value="SpaceSMS" ' . $SpaceSMS . '>SpaceSMS</option></select></p>';
    if ($settings['smsGateway'] == "clickatell" || $settings['smsGateway'] == 'clickatellc')
        $hideapiid = '';
    else
        $hideapiid = 'display:none;';
    $hideap = '';
    if ($settings['smsGateway'] == "clickatell")
        $hideap = 'display:none;';
    echo '<span style="' . $hideapiid . '" id="api_idTitle">' . $LANG['api_id'] . '</span><p><input type="text" id="api_id" name="api_id" value="' . $settings['api_id'] . '" placeholder="' . $LANG['api_id'] . '" class="form-control" style="' . $hideapiid . '"></p>';
    echo '<span style="' . $hideap . '" id="api_username">' . $LANG['api_username'] . '</span><p style="' . $hideap . '" id="tapi_username"><input type="text" name="api_username" value="' . $settings['api_username'] . '" placeholder="' . $LANG['api_username'] . '" class="form-control"></p>';
    echo '<span style="' . $hideap . '" id="api_password">' . $LANG['api_password'] . '</span><p style="' . $hideap . '" id="tapi_password"><input type="password" name="api_password" value="' . $settings['api_password'] . '" placeholder="' . $LANG['api_password'] . '" class="form-control"></p>';
    echo '<span>' . $LANG['api_sender'] . '<i>' . $LANG['senderid_note'] . '</i></span><p><input type="text" name="api_sender" value="' . $settings['api_sender'] . '" placeholder="' . $LANG['api_sender'] . '" class="form-control"></p>';
    echo '<span>' . $LANG['api_address'] . '</span><p><input type="text" name="api_address" value="' . $settings['api_address'] . '" placeholder="' . $LANG['ApiAddress'] . '" class="form-control"></p></pre>';
}

echo '<p><button class="btn btn-success" type="submit">' . $LANG['savechanges'] . '</button></p>';
echo '</div></center></form>';
?>