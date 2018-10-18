<?php
if (!defined("WHMCS")) die("This file cannot be accessed directly");

$output .= '<pre><p align="center" style="margin-bottom:0px;">';
$output .= '<a href="index.php?m=servermonitoring&type=standard"><button type="button" class="btn btn-success">'.$LANG['quickcheck'].'</button></a>';
$output .= '</p></pre>';

if (isset($_POST['url'])) {
	$status = servermonitoring_checkstatus_blacklist($_POST['url']);
	if ($status['success'] == 1) {
		$listed = false;
		$bloutput = '';
		foreach($status['result'] AS $key => $value) {
			if ($status['result'][$key]['return']) {
				$bloutput .= $status['url']." <strong>".$LANG['islisted']."</strong> on ".$status['result'][$key]['server_url']." (".$status['result'][$key]['timer'].")<br>";
				$listed=true;
			}
		}
		$bloutput = rtrim($bloutput,'<br>');
		if ($listed) {
			$output .= '<div class="alert alert-danger"style="text-align:center;">';
			$output .= $bloutput;
			$output .= '</div>';
		} else {
			$output .= '<div class="alert alert-success"style="text-align:center;">'.$status['url'].' '.$LANG['notlisted'].'</div>';
		}
	} elseif ($status['result'] == 'emptyurl') {
		$output .= '<div class="alert alert-danger" style="text-align:center;">'.$LANG['emptyurl'].'</div>';
	}
}

if (empty($status['url'])) $status['url'] = $_POST['url'];

$output .= '<form action="index.php?m=servermonitoring&type=blacklist" method="post"><pre style="text-align:center;">';
$output .= '<center><h2>'.$LANG['blacklistcheck'].'</h2><div style="width:50%;"><input type="text" class="form-control" name="url" value="'.$status['url'].'" placeholder="'.$LANG['enterurl'].'"></div></center>';
$output .= '<br><p align="center"><button class="btn btn-success" type="submit" style="margin-top: 10px;">'.$LANG['check'].'</button></p>';
$output .= '</pre></form>';
?>