<?php

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

$output .= '<pre><p align="center" style="margin-bottom:0px;">';
$output .= '<a href="index.php?m=servermonitoring&type=blacklist"><button type="button" class="btn btn-success">' . $LANG['blacklistcheck'] . '</button></a>';
$output .= '</p></pre>';

if (isset($_POST['url']) && isset($_POST['port'])) {
    $keyword = '';
    if (isset($_REQUEST['Keyword']))
        $keyword = $_REQUEST['Keyword'];
    if (isset($_REQUEST['k_username']) && $_REQUEST['k_username'] != '' && isset($_REQUEST['k_password']) && $_REQUEST['k_password'] != '') {
        $keyword = array('username' => $_REQUEST['k_username'], 'password' => $_REQUEST['k_password'], 'keyword' => $_REQUEST['Keyword']);
    }
    $status = servermonitoring_checkstatus($_POST['url'], $_POST['port'], $_POST['location'], $keyword);
    if ($status['status'] == 1) {
        $output .= '<div class="alert alert-success"style="text-align:center;">' . trim($status['url']) . ':' . $status['port'] . ' is <strong>' . $LANG['online'] . '</strong> (' . $status['time'] . ')</div>';
    } else {
        $output .= '<div class="alert alert-danger" style="text-align:center;">' . trim($status['url']) . ':' . $status['port'] . ' is <strong>' . $LANG['offline'] . '</strong></div>';
    }
}

$ports = servermonitoring_ports();
$ports = explode(',', $ports);
$poutput = "";
foreach ($ports AS $key => $value) {
    $explodeports = explode("|", $value);
    if ($status['port'] == $explodeports[1])
        $selected = " selected";
    else
        $selected = "";
    if ($status['port'] == "" && $explodeports[1] == "80")
        $selected = " selected";
    elseif ($status['port'] == "")
        $selected = "";
    $poutput .= '<option value="' . $explodeports[1] . '"' . $selected . '>' . $explodeports[1] . ' (' . $explodeports[0] . ')</option>';
}

$SETTINGS = servermonitoring_settings($vars);
$localhostName = $SETTINGS['localhostName'];
$locations = servermonitoring_locations();
$ldropdown .= '<option value="0">' . $localhostName . '</option>';
foreach ($locations AS $key => $value) {
    $ldropdown .= '<option value="' . $key . '">' . $value['location'] . '</option>';
}

$output .= '<form action="index.php?m=servermonitoring&type=standard" method="post"><pre style="text-align:center;">';
$output .= '<center><h2>' . $LANG['quickcheck'] . '</h2><div style="width:50%;">';
if ($SETTINGS['keyword_monitor'] == 'on')
    $output .= '<select size="1" name="m_type" class="form-control" style="margin-bottom:10px;"><option value="port">port</option><option value="keyword">keyword</option></select>';
$output .= '<input type="text" class="form-control" name="url" value="' . $status['url'] . '" placeholder="' . $LANG['enterurl'] . '">'
        . '<select size="1" name="port" class="form-control" style="margin-top: 10px;">' . $poutput . '</select>'
        . '<select size="1" name="location" class="form-control" style="margin-top: 10px;">' . $ldropdown . '</select>';
if ($SETTINGS['keyword_monitor'] == 'on') {
    $output .= '<span class="KeywordTxtBox"><strong>Keyword</strong></span><p class="KeywordTxtBox"><input type="text" name="Keyword" value="" class="form-control" placeholder="'.$LANG['keywordfind'].'"></p>';
    $output .= '<span class="KeywordTxtBox"><strong>Page username</strong></span><p class="KeywordTxtBox"><input type="text" name="k_username" value="" class="form-control" placeholder="'.$LANG['optionalkeyword'].'"></p>';
    $output .= '<span class="KeywordTxtBox"><strong>Page password</strong></span><p class="KeywordTxtBox"><input type="text" name="k_password" value="" class="form-control" placeholder="'.$LANG['optionalkeyword'].'"></p>';
}
$output .= '<br><p align="center"><button class="btn btn-success" type="submit" style="margin-top: 10px;">' . $LANG['check'] . '</button></p>';
$output .= '</pre></form>' .
        '<script>
	$(function() {
		$(".KeywordTxtBox").hide();
		var 
		jqDdl = $("select[name=m_type]"),
		onChange = function(event) {
			if ($(this).val() == "keyword") {
				$(".KeywordTxtBox").show(700);
				$(".KeywordTxtBox").focus().select();
			} else {
				$(".KeywordTxtBox").hide(700);
			}
		};
		onChange.apply(jqDdl.get(0)); // To show/hide the Other textbox initially
		jqDdl.change(onChange);
	});
</script>';
?>