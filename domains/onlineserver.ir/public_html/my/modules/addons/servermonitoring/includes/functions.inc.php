<?php

if (!defined('WHMCS'))
    die('This file cannot be accessed directly');

use Illuminate\Database\Capsule\Manager as Capsule;

ini_set('memory_limit', '-1');
ini_set('max_execution_time', 0);

function servermonitoring_intervals($int = 0) {
    $intervals = array('1', '2', '5', '10', '15', '20', '30', '45', '60');
    $out = array();
    foreach ($intervals AS $value) {
        if ($value >= $int)
            $out[$value] = $value;
    }
    return $out;
}

function servermonitoring_secondsToTime($seconds, $LANG = '') {
    $secondsa = $seconds % 60;
    $minutes = floor($seconds / 60) % 60;
    $hours = floor($seconds / 60 / 60) % 24;
    $days = floor($seconds / 60 / 60 / 24);
    $downtime = $days . ' ' . $LANG['days'] . ', ' . $hours . ' ' . $LANG['hours'] . ', ' . $minutes . ' ' . $LANG['minutes'] . ', ' . $secondsa . ' ' . $LANG['seconds'];
    $downtime = str_replace('0 ' . $LANG['days'] . '', '', $downtime);
    $downtime = str_replace(', 0 ' . $LANG['hours'] . '', '', $downtime);
    $downtime = str_replace(', 0 ' . $LANG['minutes'] . '', '', $downtime);
    $downtime = str_replace(', 0 ' . $LANG['seconds'] . '', '', $downtime);
    $downtime = ltrim($downtime, ',');
    $downtime = trim(rtrim($downtime, ','));
    return $downtime;
}

function servermonitoring_sendMessage($result = '', $type, $serviceid, $monitorid, $extra = '', $downtime = '0', $LANG = '') {
    $monitor = unserialize($result);
    $time = date("M d, Y H:i:s");
    if ($type == "gone-down") {
        $tplname = "Server Monitoring - Monitor Down Email";
        $mstatus = "down";
    } elseif ($type == "keyword-gone-down") {
        $tplname = "Server Monitoring - Monitor Keyword Down Email";
        $mstatus = "down";
    } elseif ($type == "back-up") {
        $tplname = "Server Monitoring - Monitor Up Email";
        $mstatus = "up";
    } elseif ($type == "keyword-back-up") {
        $tplname = "Server Monitoring - Monitor Keyword Up Email";
        $mstatus = "up";
    } elseif ($type == "solusvm-gone-down") {
        $tplname = "Server Monitoring - SolusVM Monitor Down Email";
        $mstatus = "down";
    } elseif ($type == "solusvm-back-up") {
        $tplname = "Server Monitoring - SolusVM Monitor Up Email";
        $mstatus = "up";
    } elseif ($type == "solusvm-gone-down-rebooted") {
        $tplname = "Server Monitoring - SolusVM Monitor Down and Rebooted Email";
        $mstatus = "down";
    } elseif ($type == "blacklist-report") {
        $tplname = "Server Monitoring - Blacklist Change Email";
        $mstatus = "change";
        $monitor['port'] = '0';
    } elseif ($type == "blacklist-add") {
        $tplname = "Server Monitoring - Black list added";
        $mstatus = "change";
        $monitor['port'] = '0';
    } elseif ($type == "weeklyreport") {
        $tplname = "Server Monitoring - Weekly Uptime Report";
        $mstatus = "Weekly Report";
    } elseif ($type == "weeklyreport-solusvm") {
        $tplname = "Server Monitoring - Weekly SolusVM Uptime Report";
        $mstatus = "Weekly Report";
    }

    $querya = mysql_query("SELECT `id`,`uid` FROM `mod_servermonitoring_services` WHERE `id`='" . mysql_real_escape_string($serviceid) . "'");
    $services = mysql_fetch_assoc($querya);
    $userid = $services['uid'];
    $queryb = mysql_query("SELECT `email` FROM `tblclients` WHERE `id`='" . $userid . "'");
    $client = mysql_fetch_assoc($queryb);
    $queryc = mysql_query("SELECT * FROM `mod_servermonitoring_monitors` WHERE `serviceid`='" . mysql_real_escape_string($services['id']) . "' AND `id`='" . mysql_real_escape_string($monitorid) . "'");
    $monitordata = mysql_fetch_assoc($queryc);
    $queryd = mysql_query("SELECT * FROM `mod_servermonitoring_ports` WHERE `port`='" . mysql_real_escape_string($monitor['port']) . "' AND `uid`='" . mysql_real_escape_string($userid) . "' OR `port`='" . mysql_real_escape_string($monitor['port']) . "' AND `uid`='0'");
    $portdata = mysql_fetch_assoc($queryd);
    if ($monitor['port'] != '0' && !empty($monitor['port']))
        $portandname = $monitor['port'] . "&nbsp;(" . $portdata['desc'] . ")";

    $downtimedur = servermonitoring_secondsToTime($downtime, $LANG);
    //logactivity("Downtime: ".$downtime.' -> '. $downtimedur); // debugging

    $merge = array(
        "servermonitoring_url" => $monitor['url'],
        "servermonitoring_port" => $portandname,
        "servermonitoring_time" => $time,
        "servermonitoring_monitorname" => $monitordata['monitorname'],
        "servermonitoring_weeklyreport" => $extra,
        'servermonitoring_downtime' => $downtimedur
    );
    if ($type == 'weeklyreport') {
        //die(print_r($merge)); // debugging
    }
    $email = $client['email'];
    $mailsend = sendMessage($tplname, $userid, $merge);
    $clists = Capsule::table('mod_servermonitoring_contacts')->where('mid', $monitordata['id'])->select('id')->get();
    foreach ($clists as $value) {
        Servermonitoring_sendEmailContact($value->id, $monitordata['id'], $tplname, '', $merge);
    }
    $log = servermonitoring_log('email', $mstatus, $serviceid, $monitor['url'], $monitor['port'], $tplname, $email);
    return $mailsend;
}

function servermonitoring_checkstatus_blacklist($url) {
    $url = mysql_real_escape_string($url);
    $url = str_replace('http://www.', '', $url);
    $url = str_replace('http://', '', $url);
    $url = str_replace('https://www.', '', $url);
    $url = str_replace('https://', '', $url);
    $url = gethostbyname($url);

    if (!empty($url)) {
        $query = mysql_query("SELECT * FROM `mod_servermonitoring_blacklist` WHERE `status`='1'");
        $result = array();
        while ($blacklist = mysql_fetch_assoc($query)) {
            $serverurl = $blacklist['server_url'];
            if (preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $url) != 1)
                return Array(false, 0, 0);
            $octets = explode('.', $url);
            if ($octets[0] == '127')
                return Array(false, 0, 0);
            if ($octets[0] == '10')
                return Array(false, 0, 0);
            if ($octets[0] == '192' && $octets[0] == '168')
                return Array(false, 0, 0);
            if ($octets[0] == '169' && $octets[0] == '254')
                return Array(false, 0, 0);
            if ((int) $octets[0] > 255 || (int) $octets[1] > 255 || (int) $octets[2] > 255 || (int) $octets[3] > 255)
                return Array(false, 0, 0);
            $ret_val = false;
            $PTR = implode(array_reverse($octets), '.');
            $start = microtime(true);
            $dnsresult = dns_get_record($PTR . "." . $serverurl, DNS_A);
            list($ret_val, $ttl) = servermonitoring_checkDNS($dnsresult);
            $finish = microtime(true);
            $timer = round((($finish - $start) * 1000), 0) . " ms";
            $result[$blacklist['id']] = array('id' => $blacklist['id'], 'server_name' => $blacklist['server_name'], 'server_url' => $blacklist['server_url'], 'removal_url' => $blacklist['removal_url'], 'return' => $ret_val, 'ttl' => $ttl, 'timer' => $timer);
        }
        return array('success' => true, 'url' => $url, 'result' => $result);
    } else {
        return array('success' => false, 'url' => $url, 'result' => 'emptyurl');
    }
}

function servermonitoring_checkDNS($dns_answer) {
    $ttl = 0;
    if (!is_array($dns_answer))
        return Array(false, $ttl);
    $len = count($dns_answer);
    if ($len <= 0)
        return Array(false, $ttl);
    for ($i = 0; $i < $len; $i++) {
        $obj = $dns_answer[$i];
        if (!(is_object($obj) || is_array($obj)))
            return Array(false, $ttl);
        $ip_str = $obj['ip'];
        $ttl = $obj['ttl'];
        if (!is_string($ip_str))
            return Array(false, $ttl);
        $pos = strpos($ip_str, '127.0.0.');
        if ($pos !== false)
            return Array(true, $ttl);
    }
    return Array(false, $ttl);
}

function servermonitoring_checkstatus_solusvm($url, $key, $hash, $location) {
    if ($location == '' || $location == 0) {
        $solusvmurl = "$url/api/client/command.php";
        $start = microtime(true);
        $response = servermonitoring_post($solusvmurl, "key=$key&hash=$hash&action=status");
        $finish = microtime(true);
        $time = round((($finish - $start) * 1000), 0) . " ms";

        if (preg_match("/online/", $response)) {
            return(array('status' => 1, 'time' => $time, 'url' => $url));
        } else {
            return(array('status' => 0, 'url' => $url));
        }
    } else {
        $location = (int) $location;
        $locationdata = servermonitoring_locations($location);
        if (strpos($locationdata[$location]['url'], "http://") === false && strpos($locationdata[$location]['url'], "https://") === false) {
            $checkURL = "http://" . $locationdata[$location]['url'];
        } else {
            $checkURL = $locationdata[$location]['url'];
        }
        $checkURL = rtrim($checkURL, "/");
        $output = @file_get_contents($checkURL . "/checkStatus.php?solusvm_url=" . $url . "&solusvm_key=" . $key . "&solusvm_hash=" . $hash);
        $output = unserialize($output);
        if ($output['status'] == 1)
            return $output;
        else {
            $query = mysql_query("SELECT * FROM `mod_servermonitoring_locations` order by RAND() LIMIT 1 ");
            while ($result = mysql_fetch_assoc($query)) {
                $location = (int) $result['id'];
            }
            $recheckdata = servermonitoring_recheckstatus_solusvm($url, $key, $hash, $location);
            if ($recheckdata['status'] == 0)
                return $output;
        }
    }
}

function servermonitoring_recheckstatus_solusvm($url, $key, $hash, $location) {
    set_time_limit(0);
    //sleep(30);
    if ($location == '' || $location == 0) {
        $solusvmurl = "$url/api/client/command.php";
        $start = microtime(true);
        $response = servermonitoring_post($solusvmurl, "key=$key&hash=$hash&action=status");
        $finish = microtime(true);
        $time = round((($finish - $start) * 1000), 0) . " ms";

        if (preg_match("/online/", $response)) {
            return(array('status' => 1, 'time' => $time, 'url' => $url));
        } else {
            return(array('status' => 0, 'url' => $url));
        }
    } else {
        $location = (int) $location;
        $locationdata = servermonitoring_locations($location);
        if (strpos($locationdata[$location]['url'], "http://") === false && strpos($locationdata[$location]['url'], "https://") === false) {
            $checkURL = "http://" . $locationdata[$location]['url'];
        } else {
            $checkURL = $locationdata[$location]['url'];
        }
        $checkURL = rtrim($checkURL, "/");
        $output = @file_get_contents($checkURL . "/checkStatus.php?solusvm_url=" . $url . "&solusvm_key=" . $key . "&solusvm_hash=" . $hash);
        $output = unserialize($output);
        return $output;
    }
}

function servermonitoring_reboot_solusvm($url, $key, $hash) {
    $solusvmurl = "$url/api/client/command.php";
    $start = microtime(true);
    $response = servermonitoring_post($solusvmurl, "key=$key&hash=$hash&action=reboot");
    $finish = microtime(true);
    $time = round((($finish - $start) * 1000), 0) . " ms";

    if (preg_match("/success/", $response)) {
        return(array('status' => 1, 'time' => $time, 'url' => $url));
    } else {
        return(array('status' => 0, 'url' => $url));
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

function servermonitoring_plivoSMS($vars) {
    $url = $vars['API_URL'] . $vars['AUTH_ID'] . '/Message/';
    $data = array("src" => $vars['src'], "dst" => $vars['dst'], "text" => $vars['text']);
    $data_string = json_encode($data);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_USERPWD, $vars['AUTH_ID'] . ":" . $vars['AUTH_TOKEN']);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    $response = curl_exec($ch);
    curl_close($ch);
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

function servermonitoring_checkstatus($url, $port, $location, $keyword) {

    $full_url = $url;
    $port = preg_replace("/[^0-9]/", "", $port);
    $url = str_replace('http://', '', $url);
    $url = str_replace('https://', '', $url);
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
    $settings = servermonitoring_settings($vars);
    if ($port == '2534' && $settings['allowPing'] == 'on') {
        $start = microtime(true);
        exec("ping -c 4 " . $url, $output, $result);
        $finish = microtime(true);
        $time = round((($finish - $start) * 1000), 0) . " ms";
        $pline = '';
        foreach ($output AS $value) {
            $value = strtolower(trim($value));
            if ($value != '') {
                $pline .= $value . '<br>';
                if (strpos($value, 'received') !== false) {
                    preg_match('#transmitted, (.*?) received,#', $value, $received);
                }
            }
        }
        if ($received[1] > 0) {
            return(array('status' => 1, 'time' => $time, 'url' => $url, 'port' => $port, 'location' => '0'));
        } else {
            $recheckdata = servermonitoring_recheckstatus($url, $port, $location, $keyword = '');
            if ($recheckdata['status'] == 0)
                return(array('status' => 0, 'url' => $url, 'port' => $port));
            else
                return(array('status' => 1, 'time' => $time, 'url' => $url, 'port' => $port, 'location' => $location));
        }
    }
    if ($location == '0') {
        $start = microtime(true);
        if ($keyword == '') {
            if (!filter_var($full_url, FILTER_VALIDATE_URL) === false) {
                $context = stream_context_create(array('http' => array(
                    'method' => 'HEAD',
                    'timeout' => 5
                )));
                $response = @file_get_contents($full_url, false, $context);
                $fp = ($response !== false);
            } else
                $fp = @fsockopen($url, $port, $errCode, $errStr, 5);
        } else
            $fp = true;
        $finish = microtime(true);
        $time = round((($finish - $start) * 1000), 0) . " ms";
        if ($fp) {
            if ($keyword != '') {
                $k_status = servermonitoring_keywordstatus($full_url, $key, $u, $p);
                if ($k_status == false) {
                    $recheckdata = servermonitoring_recheckstatus($url, $port, $location, $keyword);
                    if ($recheckdata['status'] == 0)
                        return(array('status' => 0, 'time' => $time, 'url' => $url, 'port' => $port));
                    else {
                        $finish = microtime(true);
                        $time = round((($finish - $start) * 1000), 0) . " ms";
                        return(array('status' => 1, 'time' => $time, 'url' => $url, 'port' => $port));
                    }
                } else {
                    $finish = microtime(true);
                    $time = round((($finish - $start) * 1000), 0) . " ms";
                    return(array('status' => 1, 'time' => $time, 'url' => $url, 'port' => $port));
                }
            }
            return(array('status' => 1, 'time' => $time, 'url' => $url, 'port' => $port));
        } else {
            $query = mysql_query("SELECT * FROM `mod_servermonitoring_locations`");
            while ($result = mysql_fetch_assoc($query)) {
                $location = (int) $result['id'];
                $locationdata = servermonitoring_locations($location);
                if (strpos($locationdata[$location]['url'], "http://") === false && strpos($locationdata[$location]['url'], "https://") === false) {
                    $checkURL = "http://" . $locationdata[$location]['url'];
                } else {
                    $checkURL = $locationdata[$location]['url'];
                }
                $checkURL = rtrim($checkURL, "/");
                $start = microtime(true);
                $keyword = base64_encode(serialize($keyword));
                $output = @file_get_contents($checkURL . "?url=" . $url . "&port=" . $port . "&keyword=" . $keyword);
                $output = unserialize($output);
                $finish = microtime(true);
                $time = round((($finish - $start) * 1000), 0) . " ms";
                if ($output['status'] == 1) {
                    return(array('status' => 1, 'time' => $time, 'url' => $url, 'port' => $port, 'location' => $location));
                }
            }
            $recheckdata = servermonitoring_recheckstatus($url, $port, $location, $keyword);
            if ($recheckdata['status'] == 0)
                return(array('status' => 0, 'url' => $url, 'port' => $port));
        }
    } else {
        $location = (int) $location;
        $locationdata = servermonitoring_locations($location);
        if (strpos($locationdata[$location]['url'], "http://") === false && strpos($locationdata[$location]['url'], "https://") === false) {
            $checkURL = "http://" . $locationdata[$location]['url'];
        } else {
            $checkURL = $locationdata[$location]['url'];
        }
        $checkURL = rtrim($checkURL, "/");
        $keywords = '';
        if ($keyword != '')
            $keywords = base64_encode(serialize($keyword));
        $output = @file_get_contents($checkURL . "?url=" . $url . "&port=" . $port . "&keyword=" . $keywords);
        $output = unserialize($output);
        if ($output['status'] == 1)
            return $output;
        else {
            $query = mysql_query("SELECT * FROM `mod_servermonitoring_locations` order by RAND() LIMIT 1 ");
            while ($result = mysql_fetch_assoc($query)) {
                $location = (int) $result['id'];
            }
            $recheckdata = servermonitoring_recheckstatus($url, $port, $location, $keyword);
            if ($recheckdata['status'] == 0)
                return $recheckdata;
        }
    }
}

function servermonitoring_recheckstatus($url, $port, $location, $keyword = '') {
    set_time_limit(0);
    //sleep(30);
    $full_url = $url;
    $port = preg_replace("/[^0-9]/", "", $port);
    $url = str_replace('http://', '', $url);
    $url = str_replace('https://', '', $url);
    $settings = servermonitoring_settings($vars);
    if ($port == '2534' && $settings['allowPing'] == 'on') {
        $start = microtime(true);
        exec("ping -c 4 " . $url, $output, $result);
        $finish = microtime(true);
        $time = round((($finish - $start) * 1000), 0) . " ms";
        $pline = '';
        foreach ($output AS $value) {
            $value = strtolower(trim($value));
            if ($value != '') {
                $pline .= $value . '<br>';
                if (strpos($value, 'received') !== false) {
                    preg_match('#transmitted, (.*?) received,#', $value, $received);
                }
            }
        }
        if ($received[1] > 0) {
            return(array('status' => 1, 'time' => $time, 'url' => $url, 'port' => $port, 'location' => '0'));
        } else {
            return(array('status' => 0, 'url' => $url, 'port' => $port));
        }
    }
    if ($location == '0') {
        $start = microtime(true);
        if ($keyword == '') {
            if (!filter_var($full_url, FILTER_VALIDATE_URL) === false) {
                $context = stream_context_create(array('http' => array(
                    'method' => 'HEAD',
                    'timeout' => 30
                )));
                $response = @file_get_contents($full_url, false, $context);
                $fp = ($response !== false);
            } else
                $fp = @fsockopen($url, $port, $errCode, $errStr, 5);
        } else
            $fp = true;
        $finish = microtime(true);
        $time = round((($finish - $start) * 1000), 0) . " ms";
        if ($fp) {
            if ($keyword != '') {
                $u = '';
                $p = '';
                if (is_array($keyword)) {
                    $u = $keyword['username'];
                    $p = $keyword['password'];
                }
                $k_status = servermonitoring_keywordstatus($full_url, $keyword, $u, $p);
                if ($k_status == false)
                    return(array('status' => 0, 'time' => $time, 'url' => $url, 'port' => $port));
                else
                    return(array('status' => 1, 'time' => $time, 'url' => $url, 'port' => $port));
            }
            return(array('status' => 1, 'time' => $time, 'url' => $url, 'port' => $port));
        } else {
            $query = mysql_query("SELECT * FROM `mod_servermonitoring_locations`");
            while ($result = mysql_fetch_assoc($query)) {
                $location = (int) $result['id'];
                $locationdata = servermonitoring_locations($location);
                if (strpos($locationdata[$location]['url'], "http://") === false && strpos($locationdata[$location]['url'], "https://") === false) {
                    $checkURL = "http://" . $locationdata[$location]['url'];
                } else {
                    $checkURL = $locationdata[$location]['url'];
                }
                $checkURL = rtrim($checkURL, "/");
                $start = microtime(true);
                $output = @file_get_contents($checkURL . "?url=" . $url . "&port=" . $port);
                $output = unserialize($output);
                $finish = microtime(true);
                $time = round((($finish - $start) * 1000), 0) . " ms";
                if ($output['status'] == 1) {
                    return(array('status' => 1, 'time' => $time, 'url' => $url, 'port' => $port, 'location' => $location));
                }
            }
            return(array('status' => 0, 'url' => $url, 'port' => $port));
        }
    } else {
        $location = (int) $location;
        $locationdata = servermonitoring_locations($location);
        if (strpos($locationdata[$location]['url'], "http://") === false && strpos($locationdata[$location]['url'], "https://") === false) {
            $checkURL = "http://" . $locationdata[$location]['url'];
        } else {
            $checkURL = $locationdata[$location]['url'];
        }
        $checkURL = rtrim($checkURL, "/");
        $output = @file_get_contents($checkURL . "?url=" . $url . "&port=" . $port);
        $output = unserialize($output);
        return $output;
    }
}

function servermonitoring_log($type, $status, $serviceid, $url, $port, $message, $recipient) {
    $timestamp = time();
    if (empty($message))
        return false;
    if (strtolower($type) == "email") {
        $message = $message . ' - ' . date("M d, Y H:i:s");
    }
    $query = mysql_query("INSERT INTO `mod_servermonitoring_logs` SET `timestamp`='" . $timestamp . "', `status`='" . $status . "', `type`='" . $type . "', `url`='" . $url . "', `port`='" . $port . "', `serviceid`='" . $serviceid . "', `message`='" . $message . "', `recipient`='" . $recipient . "'");
    return true;
}

function servermonitoring_settings($vars = null) {
    $SETING = array();
    $result = select_query("mod_servermonitoring_settings", "", "");
    while ($data = @mysql_fetch_array($result)) {
        $setting = $data['setting'];
        $value = $data['value'];
        $SETING["{$setting}"] = "{$value}";
    }
    return $SETING;
}

function servermonitoring_ports($uid = '', $serviceid = '') {
    $uid = mysql_real_escape_string($uid);
    $serviceid = mysql_real_escape_string($serviceid);

    if (!empty($uid) && !empty($serviceid) && is_numeric($uid) && is_numeric($serviceid)) {
        $query = mysql_query("SELECT * FROM `mod_servermonitoring_ports` WHERE `uid`='0' AND `serviceid`='0' OR `uid`='" . $uid . "' AND `serviceid`='" . $serviceid . "' ORDER BY `port`");
    } elseif ($uid == "*") {
        $query = mysql_query("SELECT * FROM `mod_servermonitoring_ports` ORDER BY `port`");
    } else {
        $query = mysql_query("SELECT * FROM `mod_servermonitoring_ports` WHERE `uid`='0' AND `serviceid`='0' ORDER BY `port`");
    }
    $ports = "";
    while ($result = mysql_fetch_assoc($query)) {
        $ports .= $result['desc'] . "|" . $result['port'] . ",";
    }
    $ports = rtrim($ports, ",");
    return $ports;
}

function servermonitoring_locations($id = '') {
    if (!empty($id))
        $refine = " WHERE `id`='" . mysql_real_escape_string($id) . "'";
    else
        $refine = '';
    $query = mysql_query("SELECT * FROM `mod_servermonitoring_locations`" . $refine . " ORDER BY `location` ASC");
    $locations = array();
    while ($result = mysql_fetch_assoc($query)) {
        $locations[$result['id']]['url'] = $result['url'];
        $locations[$result['id']]['description'] = $result['description'];
        $locations[$result['id']]['location'] = $result['location'];
    }
    return $locations;
}

function servermonitoring_sendSMS($to = '', $message = '', $monitor = '', $serviceid = '', $monitorid = '', $downtime = '0', $LANG = '', $direct = '') {
    global $CONFIG;
    if (file_exists(ROOTDIR . "/modules/addons/servermonitoring/lang/" . $CONFIG['Language'] . ".php")) {
        include(ROOTDIR . "/modules/addons/servermonitoring/lang/" . $CONFIG['Language'] . ".php");
    } elseif (file_exists(ROOTDIR . "/modules/addons/servermonitoring/lang/english.php")) {
        include(ROOTDIR . "/modules/addons/servermonitoring/lang/english.php");
    }
    include(ROOTDIR . "/modules/addons/servermonitoring/includes/SMS-CONFIG.php");
    $monitor = unserialize($monitor);
    $time = date("M d, Y H:i:s");
    $downtime = servermonitoring_secondsToTime($downtime, $LANG);
    $querya = mysql_query("SELECT `uid` FROM `mod_servermonitoring_services` WHERE `id`='" . $serviceid . "'");
    $services = mysql_fetch_assoc($querya);
    $queryb = mysql_query("SELECT `firstname`,`lastname` FROM `tblclients` WHERE `id`='" . $services['uid'] . "'");
    $client = mysql_fetch_assoc($queryb);
    $queryc = mysql_query("SELECT * FROM `mod_servermonitoring_monitors` WHERE `serviceid`='" . $serviceid . "' AND `id`='" . $monitorid . "'");
    $monitordata = mysql_fetch_assoc($queryc);
    $companyname = $CONFIG['CompanyName'];
    $name = ucwords($client['firstname'] . " " . $client['lastname']);

    if ($message == "gone-down") {
        $message = $_ADDONLANG['downsms'];
        $mstatus = "down";
    } elseif ($message == "back-up") {
        $message = $_ADDONLANG['backupsms'];
        $mstatus = "up";
    } elseif ($message == "solusvm-gone-down") {
        $message = $_ADDONLANG['downsolusvmsms'];
        $mstatus = "down";
    } elseif ($message == "solusvm-gone-down-rebooted") {
        $message = $_ADDONLANG['downsolusvmsmsreboot'];
        $mstatus = "down";
    } elseif ($message == "solusvm-back-up") {
        $message = $_ADDONLANG['backupsolusvmsms'];
        $mstatus = "up";
    } elseif ($message == "blacklist-report") {
        $message = $_ADDONLANG['blacklistchangesms'];
        $mstatus = "change";
        $monitor['port'] = '0';
    }

    $queryd = mysql_query("SELECT * FROM `mod_servermonitoring_ports` WHERE `port`='" . mysql_real_escape_string($monitor['port']) . "' AND `uid`='" . mysql_real_escape_string($services['uid']) . "' OR `port`='" . mysql_real_escape_string($monitor['port']) . "' AND `uid`='0'");
    $portdata = mysql_fetch_assoc($queryd);
    $portandname = $monitor['port'] . ' (' . $portdata['desc'] . ')';

    $message = str_replace('{url}', $monitor['url'], $message);
    $message = str_replace('{port}', $portandname, $message);
    $message = str_replace('{time}', $time, $message);
    $message = str_replace('{companyname}', $companyname, $message);
    $message = str_replace('{monitorname}', $monitordata['monitorname'], $message);
    $message = str_replace('{downtime}', $downtime, $message);

    $SETTINGS = servermonitoring_settings();
    $smsUsername = $SETTINGS['api_username'];
    $smsPassword = $SETTINGS['api_password'];
    $smsSender = $SETTINGS['api_sender'];
    $apiID = $SETTINGS['api_id'];
    $apiAddress = $SETTINGS['api_address'];
    $smsGateway = strtolower($SETTINGS['smsGateway']);

    if ($smsGateway != "clickatell" && (empty($smsUsername) || empty($smsPassword))) {
        return $_ADDONLANG['smscredentialerror'];
    } elseif (empty($to) || strlen($to) < 3) {
        return $_ADDONLANG['entersmsrecipient'];
    } elseif (empty($message) || strlen($message) < 3) {
        return $_ADDONLANG['entersmsmessage'];
    }
    $to = ltrim($to, '+');
    $to = explode('|', $to);
    $to = '+' . $to[0] . ltrim($to[1]);
    $recp = array();
    $recp[$to] = $name;
    $clists = Capsule::table('mod_servermonitoring_contacts')->where('mid', $monitorid)->select('phonenumber', 'name', 'family')->get();
    foreach ($clists as $value) {
        $name = ucwords($value->name . " " . $value->family);
        $recp[$value->phonenumber] = $name;
    }
    $i = 0;
    $timestamp = time();
    $res = '';
    $message1 = $message;
    foreach ($recp as $key => $value) {
        $message = $message1;
        if ($i > 0) {
            $queryi = mysql_query("SELECT `smscredits` FROM `mod_servermonitoring_services` WHERE `id`='" . $serviceid . "'");
            $servicesa = mysql_fetch_assoc($queryi);
            if ($servicesa['smscredits'] <= 0) {
                continue;
            }
            mysql_query("UPDATE `mod_servermonitoring_monitors` SET `lastsms`='" . $timestamp . "' WHERE `id`='" . $serviceid . "'");
            mysql_query("UPDATE `mod_servermonitoring_services` SET `smscredits`=smscredits-1 WHERE `id`='" . $serviceid . "'");
        }
        $i++;
        $to = $key;
        // $to = ltrim($to, '+');
        // $to = '+' . $to;
        $message = str_replace('{name}', $value, $message);
        if ($smsGateway == "text marketer") {
            $url = 'http://api.textmarketer.co.uk/gateway/';
            if ($apiAddress != '')
                $url = $apiAddress;
            $url = 'http://api.textmarketer.co.uk/gateway/' . '?username=' . $smsUsername . '&password=' . $smsPassword . '&option=xml';
            $url .= '&to=' . $to . '&message=' . urlencode($message) . '&orig=' . urlencode($smsSender);
            $method = "get";
        }
        elseif ($smsGateway == "bulksms")
        {
            $url = 'http://api.payamak-panel.com/post/send.asmx?wsdl';
            if ($apiAddress != '')
                $url = $apiAddress;
            #$url = $url . '?username=' . $smsUsername . '&password=' . $smsPassword;
            #$url .= '&message=' . urlencode($message) . '&msisdn=' . urlencode($to) . '&sender=' . urlencode($smsSender);
            $method = "soap";
        } elseif ($smsGateway == "clickatell") {
            $url = 'https://platform.clickatell.com/messages/http/send';
            if ($apiAddress != '')
                $url = $apiAddress;
            $url .= '?apiKey=' . urlencode($apiID);
            $url .= '&to=' . urlencode($to) . '&content=' . urlencode($message) . '&from=' . urlencode($smsSender);
            $method = "get";
        } elseif ($smsGateway == "spacesms") {
            $url = 'http://spacesms.ir/API/SendSMS.ashx';
            if ($apiAddress != '')
                $url = $apiAddress;
            $to = str_replace('+98', '', $to);
            $url .= '?username=' . urlencode($smsUsername) . '&password=' . urlencode($smsPassword);
            $url .= '&to=' . urlencode($to) . '&text=' . urlencode($message) . '&from=' . urlencode($smsSender);
            $method = "get";
        } elseif ($smsGateway == "clickatellc") {
            $url = 'http://api.clickatell.com/http/sendmsg';
            if ($apiAddress != '')
                $url = $apiAddress;
            $url = $url . '?user=' . urlencode($smsUsername) . '&password=' . urlencode($smsPassword);
            $url .= '&api_id=' . urlencode($apiID);
            $url .= '&to=' . urlencode($to) . '&mo=1&text=' . urlencode($message) . '&from=' . urlencode($smsSender);
            $method = "get";
        } elseif ($smsGateway == "ecall") {
            $url = 'https://www.ecall.ch/ecallurl/ECALLURL.ASP';
            if ($apiAddress != '')
                $url = $apiAddress;
            $url = $url . '?WCI=Interface&Function=SendPage&AccountName=' . $smsUsername . '&AccountPassword=' . $smsPassword;
            $url .= '&Address=' . urlencode($to) . '&Message=' . urlencode($message);
            $method = "get";
        } elseif ($smsGateway == "onverify") {
            $url = 'http://www.onverify.com/sms.php';
            if ($apiAddress != '')
                $url = $apiAddress;
            $url = $url . '?userid=' . $smsUsername . '&apipass=' . $smsPassword;
            $url .= '&number=' . urlencode($to) . '&msg=' . urlencode($message);
            $method = "get";
        } elseif ($smsGateway == "super solutions") {
            $url = 'http://portal.supersolutions.pk/api/mt/SendSMS';
            if ($apiAddress != '')
                $url = $apiAddress;
            $toA = str_replace('%2B', '', $to);
            $toA = str_replace('+', '', $toA);
            $url = $url . '?user=' . $smsUsername . '&password=' . $smsPassword;
            $url .= '&number=' . $toA . '&text=' . urlencode($message) . '&channel=Normal&DCS=0&flashsms=0&mms=0&unicode=0&senderid=' . $smsSender;
            $method = "get";
            if ($smsSender == '')
                logactivity('Super Solutions require you to use a sender ID. Please login to your Super Solutions control panel to set one up.');
        } elseif ($smsGateway == "skebby.it") {
            $url = 'http://gateway.skebby.it/api/send/smseasy/advanced/http.php';
            if ($apiAddress != '')
                $url = $apiAddress;
            $toA = str_replace('%2B', '', $to);
            $toA = str_replace('+', '', $toA);
            $smsParams = 'method=send_sms_basic&username=' . $smsUsername . '&password=' . $smsPassword . '&text=' . urlencode($message) . '&recipients=' . urlencode($toA) . '&sender_string=' . $smsSender;
            $method = 'post';
        } elseif ($smsGateway == "mobily.ws") {
            $applicationType = "68";
            $msg = $msg;
            $sender = urlencode($sender);
            $domainName = $_SERVER['SERVER_NAME'];
            $toA = str_replace('%2B', '', $to);
            $toA = str_replace('+', '', $toA);
            $smsParams = http_build_query(array('mobile' => $smsUsername, 'password' => $smsPassword, 'numbers' => $toA, 'sender' => $smsSender, 'msg' => $message, 'timeSend' => 0, 'dateSend' => 0, 'applicationType' => $applicationType, 'domainName' => $domainName, 'msgId' => rand(1, 99999), 'deleteKey' => 0, 'lang' => '3'));
            $url = "http://www.mobily.ws/api/msgSend.php";
            $method = 'post';
        } elseif ($smsGateway == "plivo") {
            $url = 'https://api.plivo.com/v1/Account/';
            if ($apiAddress != '')
                $url = $apiAddress;
            $method = 'post';
        } elseif ($smsGateway == "twilio") {
            $url = 'Twilio API';
            if ($apiAddress != '')
                $url = $apiAddress;
            $method = 'post';
        } elseif ($smsGateway == "synergywholesale") {
            $url = 'https://api.synergywholesale.com/?wsdl';
            if ($apiAddress != '')
                $url = $apiAddress;
            $method = 'wsdl';
            if (!(strpos($to, '+61') === false)) {
                $to = str_replace('+61', '0', $to);
            }
        } else {
            return $_ADDONLANG['validsmsgateway'];
        }
        $url = trim($url);
        if ($method == "post") {
            if ($smsGateway == "plivo") {
                $sms_vars = array('AUTH_ID' => $smsUsername, 'AUTH_TOKEN' => $smsPassword, 'API_URL' => $url, 'src' => $smsSender, 'dst' => $to, 'text' => $message);
                $res = servermonitoring_plivoSMS($sms_vars);
            } elseif ($smsGateway == "twilio") {
                include(ROOTDIR . "/modules/addons/servermonitoring/includes/Twillo.php");
                $toA = str_replace('%2B', '', $to);
                $toA = str_replace('+', '', $toA);
                $toA = '+' . $toA;
                $res = SMS_user_Twilio($smsUsername, $smsPassword, $to, $smsSender, $message);
            } else
                $res = servermonitoring_post($url, $smsParams);
            //logactivity($res); // debugging
        }else if ($method == "wsdl") {
            try {
                // New soap connection
                $client = new SoapClient(null, array('location' => $url, 'uri' => ""));

                // Data array
                $params = array('resellerID' => $smsUsername, 'apiKey' => $smsPassword, 'destination' => $to, 'senderID' => $smsSender, 'message' => $message);

                // Attempt the update with the API
                $output = $client->sendSMS($params);

                if ($output->status == 'OK') {
                    $res = 'synergywholesale SMS is sent';
                } else {
                    $res = 'synergywholesale : ' . print_r($output, true);
                }
            } catch (SoapFault $fault) {
                $res = 'synergywholesale : ' . print_r($fault, true);
            }
        } else if ($method == "soap") {

            // new from farapayamak
            ini_set("soap.wsdl_cache_enabled", "0");
            try
            {

                $parameters = array();

                $parameters['username'] = $smsUsername;
                $parameters['password'] = $smsPassword;
                $parameters['from'] = $smsSender;
                //$parameters['to'] = array(ltrim(str_replace(array('+98','+'),'',$to),0));
                $parameters['text'] =$message;


                $client = new SoapClient("http://api.payamak-panel.com/post/send.asmx?wsdl");
                $parameters['username'] = "9125289380";
                $parameters['password'] = 'k-B5%zS-';
                $parameters['from'] = "2154634";

                $parameters['to'] = array(ltrim(str_replace('+98','',$to),0));
                $parameters['text'] = $message;
                $parameters['isflash'] = false;
                $parameters['udh'] = "";
                $parameters['recId'] = array(0);
                $parameters['status'] = 0x0;

                $_res =  $client->SendSms($parameters)->SendSmsResult;
                /*unset($parameters['text']);
                $parameters['reza_log_res'] = $_res;
                logactivity(json_encode($parameters)); // debugging
                $log = servermonitoring_log('SMS', $mstatus, $serviceid, $monitor['url'], $monitor['port'], json_encode($parameters), $to);
                */


                if ($_res=='1') {
                    $res = 'synergywholesale SMS is sent';
                } else {
                    $res = 'synergywholesale : ' . print_r($_res, true);
                }
            }
            catch (SoapFault $fault)
            {
                $res = 'synergywholesale : ' . print_r($fault, true);
            }

            //end

        } else {
            $fp = fopen($url, 'r');
            $res = fread($fp, 1024);
            if (trim($res) == "") {
                $res = @file_get_contents($url);
                if (trim($fp) == "") {
                    $res = servermonitoring_post($url, '');
                }
            }
        }
        if (is_array($res))
            $res = serialize($res);
        logactivity($smsGateway . " - " . $res); // debugging
        $log = servermonitoring_log('SMS', $mstatus, $serviceid, $monitor['url'], $monitor['port'], $message, $to);
    }
    return $res;
}

function servermonitoring_countryCodes($ccode = NULL) {
    $code = array();
    @$code[$ccode] = " selected";
    @$output = '
    <option value="98"' . $code[98] . '>ایران</option>';
    return $output;
}

function Servermonitoring_sendEmail($to, $uid, $func_messagename, $func_id, $extra = "", $displayresult = "", $attachments = "") {
    global $whmcs;
    global $CONFIG;
    global $_LANG;
    global $currency;
    global $fromname;
    global $fromemail;
    global $whmcs;
    $sysurl = ($CONFIG['SystemSSLURL'] ? $CONFIG['SystemSSLURL'] : $CONFIG['SystemURL']);
    $nosavemaillog = false;
    $email_merge_fields = array();
    $data = Capsule::table('tblemailtemplates')->where("name", $func_messagename)->where('language', '')->first();
    $emailtplid = $data->id;
    $type = $data->type;
    $subject = $data->subject;
    $message = $data->message;
    $tplattachments = $data->attachments;
    $fromname = $data->fromname;
    $fromemail = $data->fromemail;
    $disabled = $data->disabled;
    $copyto = $data->copyto;
    $plaintext = $data->plaintext;
    if ($to == 'contact') {
        $result2 = Capsule::connection()->select("SELECT tblcontacts.*,(SELECT groupid FROM tblclients WHERE id=tblcontacts.userid) AS clgroupid,(SELECT groupname FROM tblclientgroups WHERE id=clgroupid) AS clgroupname,(SELECT language FROM tblclients WHERE id=tblcontacts.userid) AS language FROM tblcontacts WHERE id='" . $uid . "'");
    } else {
        $result2 = Capsule::connection()->select("SELECT tblclients.*,tblclients.groupid AS clgroupid,(SELECT groupname FROM tblclientgroups WHERE id=tblclients.groupid) AS clgroupname FROM tblclients WHERE id='" . $uid . "'");
    }
    if (count($result2) > 0)
        $result2 = $result2[0];
    if ($to == 'contact')
        $userid = $result2->userid;
    else
        $userid = $uid;
    $firstname = $result2->firstname;
    $email = $result2->email;
    $lastname = $result2->lastname;
    $companyname = $result2->companyname;
    $address1 = $result2->address1;
    $address2 = $result2->address2;
    $city = $result2->city;
    $state = $result2->state;
    $postcode = $result2->postcode;
    $country = $result2->country;
    $phonenumber = $result2->phonenumber;
    $language = $result2->language;
    $credit = $result2->credit;
    $status = $result2->status;
    $clgroupid = $result2->clgroupid;
    $clgroupname = $result2->clgroupname;
    $gatewayid = $result2->gatewayid;
    $datecreated = fromMySQLDate($result2->datecreated, 0, 1);
    $password = "**********";
    if ($CONFIG['NOMD5']) {
        $password = decrypt($result2->password);
    }
    if (!$email) {
        return false;
    }
    $fname = trim($firstname . " " . $lastname);
    if ($companyname) {
        $fname .= " (" . $companyname . ")";
    }
    $email_merge_fields['client_id'] = $userid;
    $email_merge_fields['client_name'] = $fname;
    $email_merge_fields['client_first_name'] = $firstname;
    $email_merge_fields['client_last_name'] = $lastname;
    $email_merge_fields['client_company_name'] = $companyname;
    $email_merge_fields['client_email'] = $email;
    $email_merge_fields['client_address1'] = $address1;
    $email_merge_fields['client_address2'] = $address2;
    $email_merge_fields['client_city'] = $city;
    $email_merge_fields['client_state'] = $state;
    $email_merge_fields['client_postcode'] = $postcode;
    $email_merge_fields['client_country'] = $country;
    $email_merge_fields['client_phonenumber'] = $phonenumber;
    $email_merge_fields['client_password'] = $password;
    $email_merge_fields['client_signup_date'] = $datecreated;
    //$email_merge_fields['client_cc_type'] = $cardtype;
    $email_merge_fields['client_language'] = $language;
    $email_merge_fields['client_status'] = $status;
    $email_merge_fields['client_group_id'] = $clgroupid;
    $email_merge_fields['client_group_name'] = $clgroupname;
    $email_merge_fields['client_gateway_id'] = $gatewayid;
    $email_merge_fields['unsubscribe_url'] = $CONFIG['SystemURL'] . "/unsubscribe.php?email=" . $email . "&key=" . sha1($email . $userid . $cc_encryption_hash);

    if (!function_exists("getCustomFields")) {
        require ROOTDIR . '/includes/' . "/customfieldfunctions.php";
    }
    $customfields = getCustomFields("client", "", $userid, true, "");
    $email_merge_fields['client_custom_fields'] = array();
    foreach ($customfields as $customfield) {
        $customfieldname = preg_replace("/[^0-9a-z]/", "", strtolower($customfield['name']));
        $email_merge_fields["client_custom_field_" . $customfieldname] = $customfield['value'];
        $email_merge_fields['client_custom_fields'][] = $customfield['value'];
    }


    if (is_array($extra)) {
        foreach ($extra as $k => $v) {
            $email_merge_fields[$k] = $v;
        }
    }
    $email_merge_fields['company_name'] = $CONFIG['CompanyName'];
    $email_merge_fields['company_domain'] = $CONFIG['Domain'];
    $email_merge_fields['company_logo_url'] = $CONFIG['LogoURL'];
    $email_merge_fields['whmcs_url'] = $CONFIG['SystemURL'];
    $email_merge_fields['whmcs_link'] = "<a href=\"" . $CONFIG['SystemURL'] . "\">" . $CONFIG['SystemURL'] . "</a>";
    $email_merge_fields['signature'] = nl2br(html_entity_decode($CONFIG['Signature'], ENT_QUOTES));
    $email_merge_fields['date'] = date("l, jS F Y");
    $email_merge_fields['time'] = date("g:ia");
    $datas = Capsule::table('tblemailtemplates')->where("name", $func_messagename)->where('language', $language)->first();
    if (count($datas) > 0) {
        if ($data->subject)
            $subject = $data->subject;
        if ($data->message)
            $message = $data->message;
    }
    if (!$fromname) {
        $fromname = $CONFIG['CompanyName'];
    }


    if (!$fromemail) {
        $fromemail = $CONFIG['Email'];
    }
    if (!trim($subject) && !trim($message)) {
        logActivity("EMAILERROR: Email Message Empty so Aborting Sending - Template Name " . $func_messagename . " ID " . $func_id);
        return false;
    }
    foreach ($email_merge_fields as $value => $key) {
        $message = str_replace('{$' . $value . '}', $key, $message);
        $subject = str_replace('{$' . $value . '}', $key, $subject);
    }
    $logo = '';
    if ($CONFIG['LogoURL']) {
        $logo = '<img src="' . $CONFIG['LogoURL'] . '" style="max-width:600px;padding:20px" id="headerImage" alt="' . $CONFIG['CompanyName'] . '" />';
    }
    $message = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                <html xmlns="http://www.w3.org/1999/xhtml">
                    <head>
                        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
                        <style type="text/css">
                        ' . $CONFIG['EmailCSS'] . '
                        </style>
                    </head>
                    <body leftmargin="0" marginwidth="0" topmargin="0" marginheight="0" offset="0">
                        <center>
                            <table align="center" border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" id="bodyTable">
                                <tr>
                                    <td align="center" valign="top" id="bodyCell">
                                        <table border="0" cellpadding="0" cellspacing="0" id="templateContainer">
                                            <tr>
                                                <td align="center" valign="top">
                                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" id="templateHeader">
                                                        <tr>
                                                            <td valign="top" class="headerContent">
                                                                <a href="' . $CONFIG['Domain'] . '">
                                                                    ' . $logo . '
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td align="center" valign="top">
                                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" id="templateBody">
                                                        <tr>
                                                            <td valign="top" class="bodyContent">
                                                            <!-- message header end -->
                                                                ' . $message . '
                                                            <!-- message footer start -->
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td align="center" valign="top">
                                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" id="templateFooter">
                                                        <tr>
                                                            <td valign="top" class="footerContent">
                                                                Copyright © ' . $CONFIG['CompanyName'] . ', All rights reserved.
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </center>
                    </body>
                </html>';
    $mail = new PHPMailer();
    $subject = html_entity_decode($subject, ENT_QUOTES);
    $mail->From = $fromemail;
    $mail->FromName = $fromname;
    $mail->Subject = strip_tags($subject);
    $mail->CharSet = $CONFIG['Charset'];
    if ($CONFIG['MailType'] == "mail") {
        $mail->Mailer = "mail";
    } elseif ($CONFIG['MailType'] == "smtp") {
        $mail->IsSMTP();
        $mail->Host = $CONFIG['SMTPHost'];
        $mail->Port = $CONFIG['SMTPPort'];
        $mail->Hostname = $_SERVER['SERVER_NAME'];
        if ($CONFIG['SMTPSSL']) {
            $mail->SMTPSecure = $CONFIG['SMTPSSL'];
        }
        if ($CONFIG['SMTPUsername']) {
            $mail->SMTPAuth = true;
            $mail->Username = $CONFIG['SMTPUsername'];
            $mail->Password = decrypt($CONFIG['SMTPPassword']);
        }
        $mail->Sender = $CONFIG['Email'];
        $mail->AddReplyTo($CONFIG['Email'], $fromname);
    }
    $mail->AddAddress($email, $fname);
    $mail->Body = $message;
    $message_text = str_replace("</p>", "\r\n\r\n", $message);
    $message_text = str_replace("<br>", "\r\n", $message);
    $message_text = str_replace("<br />", "\r\n", $message);
    $mail->AltBody = $message_text;
    $mail->Send();
    $mail->ClearAddresses();
    $fname = $fname . '<' . $email . '>';
    Capsule::table('tblemails')->insert([
        'userid' => $userid,
        'subject' => strip_tags($subject),
        'message' => $message,
        'to' => $fname,
        'cc' => '',
        'bcc' => '',
        'date' => date('Y-m-d h:i:s'),
    ]);
    return TRUE;
}

function Servermonitoring_sendEmailContact($to, $uid, $func_messagename, $func_id, $extra = "", $displayresult = "", $attachments = "") {
    global $whmcs;
    global $CONFIG;
    global $_LANG;
    global $currency;
    global $fromname;
    global $fromemail;
    global $whmcs;
    $sysurl = ($CONFIG['SystemSSLURL'] ? $CONFIG['SystemSSLURL'] : $CONFIG['SystemURL']);
    $nosavemaillog = false;
    $email_merge_fields = array();
    $data = Capsule::table('tblemailtemplates')->where("name", $func_messagename)->where('language', '')->first();
    $emailtplid = $data->id;
    $type = $data->type;
    $subject = $data->subject;
    $message = $data->message;
    $tplattachments = $data->attachments;
    $fromname = $data->fromname;
    $fromemail = $data->fromemail;
    $disabled = $data->disabled;
    $copyto = $data->copyto;
    $plaintext = $data->plaintext;
    $item = Capsule::table('mod_servermonitoring_contacts')->where('id', $to)->first();
    $userids = Capsule::table('mod_servermonitoring_services')->join('mod_servermonitoring_monitors', 'mod_servermonitoring_monitors.serviceid', '=', 'mod_servermonitoring_services.id')->select('mod_servermonitoring_services.uid as userid')->where('mod_servermonitoring_monitors.id', $uid)->first();
    $userid = $userids->userid;
    $uid = $userids->userid;
    $email = $item->email;
    $fname = trim($item->name . " " . $item->family);
    $email_merge_fields['client_id'] = '';
    $email_merge_fields['client_name'] = $fname;
    $email_merge_fields['client_first_name'] = $item->name;
    $email_merge_fields['client_last_name'] = $item->family;
    $email_merge_fields['client_email'] = $item->email;
    $email_merge_fields['client_phonenumber'] = $item->phonenumber;
    $email_merge_fields['unsubscribe_url'] = $CONFIG['SystemURL'] . "/unsubscribe.php?email=" . $email;
    if (is_array($extra)) {
        foreach ($extra as $k => $v) {
            $email_merge_fields[$k] = $v;
        }
    }
    $email_merge_fields['company_name'] = $CONFIG['CompanyName'];
    $email_merge_fields['company_domain'] = $CONFIG['Domain'];
    $email_merge_fields['company_logo_url'] = $CONFIG['LogoURL'];
    $email_merge_fields['whmcs_url'] = $CONFIG['SystemURL'];
    $email_merge_fields['whmcs_link'] = "<a href=\"" . $CONFIG['SystemURL'] . "\">" . $CONFIG['SystemURL'] . "</a>";
    $email_merge_fields['signature'] = nl2br(html_entity_decode($CONFIG['Signature'], ENT_QUOTES));
    $email_merge_fields['date'] = date("l, jS F Y");
    $email_merge_fields['time'] = date("g:ia");
    $datas = Capsule::table('tblemailtemplates')->where("name", $func_messagename)->where('language', $language)->first();
    if (count($datas) > 0) {
        if ($data->subject)
            $subject = $data->subject;
        if ($data->message)
            $message = $data->message;
    }
    if (!$fromname) {
        $fromname = $CONFIG['CompanyName'];
    }


    if (!$fromemail) {
        $fromemail = $CONFIG['Email'];
    }
    if (!trim($subject) && !trim($message)) {
        logActivity("EMAILERROR: Email Message Empty so Aborting Sending - Template Name " . $func_messagename . " ID " . $func_id);
        return false;
    }
    foreach ($email_merge_fields as $value => $key) {
        $message = str_replace('{$' . $value . '}', $key, $message);
        $subject = str_replace('{$' . $value . '}', $key, $subject);
    }
    $logo = '';
    if ($CONFIG['LogoURL']) {
        $logo = '<img src="' . $CONFIG['LogoURL'] . '" style="max-width:600px;padding:20px" id="headerImage" alt="' . $CONFIG['CompanyName'] . '" />';
    }
    $message = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                <html xmlns="http://www.w3.org/1999/xhtml">
                    <head>
                        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
                        <style type="text/css">
                        ' . $CONFIG['EmailCSS'] . '
                        </style>
                    </head>
                    <body leftmargin="0" marginwidth="0" topmargin="0" marginheight="0" offset="0">
                        <center>
                            <table align="center" border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" id="bodyTable">
                                <tr>
                                    <td align="center" valign="top" id="bodyCell">
                                        <table border="0" cellpadding="0" cellspacing="0" id="templateContainer">
                                            <tr>
                                                <td align="center" valign="top">
                                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" id="templateHeader">
                                                        <tr>
                                                            <td valign="top" class="headerContent">
                                                                <a href="' . $CONFIG['Domain'] . '">
                                                                    ' . $logo . '
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td align="center" valign="top">
                                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" id="templateBody">
                                                        <tr>
                                                            <td valign="top" class="bodyContent">
                                                            <!-- message header end -->
                                                                ' . $message . '
                                                            <!-- message footer start -->
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td align="center" valign="top">
                                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" id="templateFooter">
                                                        <tr>
                                                            <td valign="top" class="footerContent">
                                                                Copyright © ' . $CONFIG['CompanyName'] . ', All rights reserved.
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </center>
                    </body>
                </html>';
    $mail = new PHPMailer();
    $subject = html_entity_decode($subject, ENT_QUOTES);
    $mail->From = $fromemail;
    $mail->FromName = $fromname;
    $mail->Subject = strip_tags($subject);
    $mail->CharSet = $CONFIG['Charset'];
    if ($CONFIG['MailType'] == "mail") {
        $mail->Mailer = "mail";
    } elseif ($CONFIG['MailType'] == "smtp") {
        $mail->IsSMTP();
        $mail->Host = $CONFIG['SMTPHost'];
        $mail->Port = $CONFIG['SMTPPort'];
        $mail->Hostname = $_SERVER['SERVER_NAME'];
        if ($CONFIG['SMTPSSL']) {
            $mail->SMTPSecure = $CONFIG['SMTPSSL'];
        }
        if ($CONFIG['SMTPUsername']) {
            $mail->SMTPAuth = true;
            $mail->Username = $CONFIG['SMTPUsername'];
            $mail->Password = decrypt($CONFIG['SMTPPassword']);
        }
        $mail->Sender = $CONFIG['Email'];
        $mail->AddReplyTo($CONFIG['Email'], $fromname);
    }
    $mail->AddAddress($email, $fname);
    $mail->Body = $message;
    $message_text = str_replace("</p>", "\r\n\r\n", $message);
    $message_text = str_replace("<br>", "\r\n", $message);
    $message_text = str_replace("<br />", "\r\n", $message);
    $mail->AltBody = $message_text;
    $mail->Send();
    $mail->ClearAddresses();
    $fname = $fname . '<' . $email . '>';
    Capsule::table('tblemails')->insert([
        'userid' => $userid,
        'subject' => strip_tags($subject),
        'message' => $message,
        'to' => $fname,
        'cc' => '',
        'bcc' => '',
        'date' => date('Y-m-d h:i:s'),
    ]);
    return TRUE;
}

?>