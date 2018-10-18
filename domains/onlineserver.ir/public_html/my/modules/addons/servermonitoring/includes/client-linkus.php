<?php

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

$settings = servermonitoring_settings($vars);
global $CONFIG;
$pagetitle = $LANG['linkus'];
$settings['banner'] = str_replace('{URL}', $CONFIG['Domain'], $settings['banner']);
$banners = explode('{endbanner}', $settings['banner']);
$output .= '<p align="center"><strong>' . $LANG['linkus'] . '</strong><br>
' . $LANG['linkusdesc'] . '<br>';
for ($i = 0; $i < count($banners); $i++) {
    if ($banners[$i] == '')
        continue;
    $output .= htmlspecialchars_decode($banners[$i]) . '<br><textarea style="width: 645px; height: 126px;margin-top:10px;">' . $banners[$i] . '</textarea><br><br>';
}
$output .= '<a href="#" onclick="history.go(-1);"><button class="btn btn-danger" style="margin-top:10px;" type="button">' . $LANG['goback'] . '</button>&nbsp&nbsp</a></p>';
?>