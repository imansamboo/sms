<?php
function servermonitoring_checkstatus($url, $port, $keyword) {
    $full_url = $url;
    $u = '';
    $p = '';
    $key = '';
    if (is_array($keyword)) {
        $u = $keyword['username'];
        $p = $keyword['password'];
        $key = $keyword['keyword'];
    } else {
        $key = $keyword;
    }
    $port = preg_replace("/[^0-9]/", "", $port);
    $url = str_replace('http://', '', $url);
    $url = str_replace('https://', '', $url);
    $start = microtime(true);
    if ($keyword == '')
        $fp = @fsockopen($url, $port, $errCode, $errStr, 5);
    else
        $fp = true;
    $finish = microtime(true);
    $time = round((($finish - $start) * 1000), 0) . " ms";
    if ($fp) {
        if ($keyword != '') {
            $k_status = servermonitoring_keywordstatus($full_url, $key, $u, $p);
            if ($k_status == false) {
                $finish = microtime(true);
                $time = round((($finish - $start) * 1000), 0) . " ms";
                return(array('status' => 0, 'url' => $url, 'port' => $port, 'external' => true));
            }
            $finish = microtime(true);
            $time = round((($finish - $start) * 1000), 0) . " ms";
            return(array('status' => 1, 'time' => $time, 'url' => $url, 'port' => $port, 'external' => true));
        } else {
            return(array('status' => 1, 'time' => $time, 'url' => $url, 'port' => $port, 'external' => true));
        }
    } else {
        return(array('status' => 0, 'url' => $url, 'port' => $port, 'external' => true));
    }
}

function servermonitoring_checkstatus_solusvm($url, $key, $hash) {
    $solusvmurl = "$url/api/client/command.php";
    $start = microtime(true);
    $response = servermonitoring_post($solusvmurl, "key=$key&hash=$hash&action=status");
    $finish = microtime(true);
    $time = round((($finish - $start) * 1000), 0) . " ms";

    if (preg_match("/online/", $response)) {
        return(array('status' => 1, 'time' => $time, 'url' => $url, 'external' => true));
    } else {
        return(array('status' => 0, 'url' => $url, 'external' => true));
    }
}

function servermonitoring_post($url, $vars) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_VERBOSE, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
    $response = curl_exec($ch);
    return $response;
}

function servermonitoring_keywordstatus($url, $keyword, $user, $pass) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
    if ($user != '')
        curl_setopt($ch, CURLOPT_USERPWD, "$user:$pass");
    $data = curl_exec($ch);
    curl_close($ch);
    $pos = strpos($data, $keyword);
    if ($pos == false)
        return false;
    else
        return true;
}

if (isset($_REQUEST['url']) && isset($_REQUEST['port'])) {
    $url = trim($_REQUEST['url']);
    $port = trim($_REQUEST['port']);
    $keyword = '';
    if (isset($_REQUEST['keyword']))
        $keyword = unserialize(base64_decode($_REQUEST['keyword']));
    if (is_array($keyword)) {
        if (isset($keyword['k_username']) && $keyword['k_username'] != '' && isset($keyword['k_password']) && $keyword['k_password'] != '') {
            $keyword = array('username' => $keyword['k_username'], 'password' => $keyword['k_password'], 'keyword' => $keyword['Keyword']);
        }
    }
    if (!empty($url) && !empty($port) && is_numeric($port)) {
        $status = servermonitoring_checkstatus($url, $port, $keyword);
        echo serialize($status);
    } else {
        echo serialize(array('status' => 0, 'url' => $url, 'port' => $port, 'external' => true));
    }
} elseif (isset($_REQUEST['solusvm_url']) && isset($_REQUEST['solusvm_key']) && isset($_REQUEST['solusvm_hash'])) {
    $solusvm_url = $_REQUEST['solusvm_url'];
    $solusvm_hash = $_REQUEST['solusvm_hash'];
    $solusvm_key = $_REQUEST['solusvm_key'];

    if (!empty($solusvm_url) && !empty($solusvm_hash) && !empty($solusvm_key)) {
        $status = servermonitoring_checkstatus_solusvm($solusvm_url, $solusvm_key, $solusvm_hash);
        echo serialize($status);
    } else {
        echo serialize(array('status' => 0, 'url' => $solusvm_url, 'external' => true));
    }
} else {
    echo serialize(array('status' => '0', 'external' => true));
}
?>