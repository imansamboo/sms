<?php

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

if (isset($_POST['url'])) {
    $status = servermonitoring_checkstatus_blacklist($_POST['url']);
    if ($status['success'] == 1) {
        $listed = false;
        $bloutput = '';
        foreach ($status['result'] AS $key => $value) {
            if ($status['result'][$key]['return']) {
                $bloutput .= $status['url'] . " <strong>" . $LANG['islisted'] . "</strong> on " . $status['result'][$key]['server_url'] . " (" . $status['result'][$key]['timer'] . ")<br>";
                $listed = true;
            }
        }
        $bloutput = rtrim($bloutput, '<br>');
        if ($listed) {
            echo '<div class="alert alert-danger"style="text-align:center;">';
            echo $bloutput;
            echo '</div>';
        } else {
            echo '<div class="alert alert-success"style="text-align:center;">' . $status['url'] . ' ' . $LANG['notlisted'] . '</div>';
        }
    } elseif ($status['result'] == 'emptyurl') {
        echo '<div class="alert alert-danger" style="text-align:center;">' . $LANG['emptyurl'] . '</div>';
    }
}

$output = "";

if (empty($status['url']))
    $status['url'] = @$_POST['url'];

echo '<form action="' . $modulelink . '&a=quickcheck&type=blacklist" method="post"><pre style="text-align:center;">';
echo '<center><h2>' . $LANG['blacklistcheck'] . '</h2><div style="width:50%;"><input type="text" class="form-control" name="url" value="' . $status['url'] . '" placeholder="' . $LANG['enterip'] . '"></div></center>';
echo '<br><p align="center"><button class="btn btn-success" type="submit" style="margin-top: 10px;">' . $LANG['check'] . '</button></p>';
echo '</pre></form>';
?>