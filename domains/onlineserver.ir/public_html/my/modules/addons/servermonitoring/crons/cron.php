<?php

ini_set('display_errors', 0);
ini_set('log_errors', 0);
error_reporting(0);
$dir = dirname(dirname(dirname(dirname(dirname(__FILE__)))));
define('ROOTDIR', $dir);
error_reporting(0);
if (file_exists(ROOTDIR . "/init.php")) {
    require_once(ROOTDIR . "/init.php");
} else {
    die("ERROR: init.php not found in: " . ROOTDIR);
}

use Illuminate\Database\Capsule\Manager as Capsule;

require_once(ROOTDIR . "/modules/addons/servermonitoring/includes/functions.inc.php");
global $CONFIG;

if (file_exists(ROOTDIR . "/modules/addons/servermonitoring/lang/" . $CONFIG['Language'] . ".php")) {
    include(ROOTDIR . "/modules/addons/servermonitoring/lang/" . $CONFIG['Language'] . ".php");
} elseif (file_exists(ROOTDIR . "/modules/addons/servermonitoring/lang/english.php")) {
    include(ROOTDIR . "/modules/addons/servermonitoring/lang/english.php");
}

// Server Down checking
$ip = gethostbyname('www.google.com');
if ($ip == 'www.google.com') {
    echo '101';
    exit;
}
// End Server Down checking

$settings = servermonitoring_settings($vars);
$res_week = strtotime("-1 week");
Capsule::table('mod_servermonitoring_response')->where('res_time', '<', $res_week)->delete();
$error = true;
$query = mysql_query("SELECT * FROM `mod_servermonitoring_monitors` WHERE `status`='Active'");
while ($result = mysql_fetch_assoc($query)) {
    $error = false;
    $timestamp = time();

    $querya = mysql_query("SELECT `id`,`serviceid`,`smscredits`,`smsrecipient`,`emaillimit`,`smslimit`,`uid` FROM `mod_servermonitoring_services` WHERE `id`='" . $result['serviceid'] . "'");
    $services = mysql_fetch_assoc($querya);
    $queryb = mysql_query("SELECT `packageid`,`domainstatus` FROM `tblhosting` WHERE `id`='" . $services['serviceid'] . "'");
    $hosting = mysql_fetch_assoc($queryb);
    $queryc = mysql_query("SELECT `configoption1`,`configoption2`,`configoption3`,`configoption4` FROM `tblproducts` WHERE `id`='" . $hosting['packageid'] . "'");
    $products = mysql_fetch_assoc($queryc);
    if (Capsule::schema()->hasTable('tblmodule_configuration')) {
        $checkisaddon = Capsule::table('tblhostingaddons')->where('hostingid', $services['serviceid'])->join('tbladdons', 'tblhostingaddons.addonid', '=', 'tbladdons.id')->where('module', 'serverMonitoring')->select('addonid')->first();
        if (count($checkisaddon) > 0) {
            $plist = Capsule::table('tblmodule_configuration')->where('entity_type', 'addon')->where('entity_id', $checkisaddon->addonid)->get();
            if (count($plist) > 0) {
                foreach ($plist as $value) {
                    $products[$value->setting_name] = $value->value;
                }
            }
        }
    }
    $querydd = mysql_query("SELECT * FROM `mod_servermonitoring_maintenance` WHERE `serviceid`='" . $services['id'] . "' AND `mid`='" . $result['id'] . "' AND `status`='1' ORDER BY `id` DESC");
    $maintenanceactive = false;
    while ($maintenance = mysql_fetch_assoc($querydd)) {
        if ($timestamp >= $maintenance['from'] && $timestamp <= $maintenance['to']) {
            $maintenanceactive = true;
            break;
        }
    }

    $interval = explode(" ", $products['configoption1']);
    $interval = preg_replace("/[^0-9]/", "", $interval[0]);
    $grace = $interval * 10;
    $interval = $interval * 60;
    $interval = $interval - $grace;

    $blinterval = explode(" ", $products['configoption4']);
    $blinterval = preg_replace("/[^0-9]/", "", $blinterval[0]);
    $blgrace = $blinterval * 10;
    $blinterval = ($blinterval * 60) * 60;
    $blinterval = $blinterval - $blgrace;

    $userid = $services['uid'];
    $tosms = unserialize($services['smsrecipient']);

    $queryd = mysql_query("UPDATE `mod_servermonitoring_monitors` SET `cron`='" . time() . "' WHERE `id`='" . $result['id'] . "'");

    if ($result['type'] == "blacklist" && $products['configoption3'] != "on")
        continue;
    if (time() - $result['lastmonitor'] < $blinterval && $result['type'] == "blacklist" || time() - $result['lastmonitor'] < $interval && $result['type'] != "blacklist" || $hosting['domainstatus'] != "Active" || $maintenanceactive)
        continue;
    if ($result['custom_interval'] != 0) {
        $custom_grace = $result['custom_interval'] * 10;
        $custom_interval = $result['custom_interval'] * 60;
        $custom_interval = $custom_interval - $custom_grace;
        if (time() - $result['lastmonitor'] < $custom_interval)
            continue;
    }

    $queryd = mysql_query("UPDATE `mod_servermonitoring_maintenance` SET `status`='0' WHERE `serviceid`='" . $services['id'] . "' AND `mid`='" . $result['id'] . "' AND `to`<" . $timestamp . " AND `from`<" . $timestamp . "");

    // WEEKLY REPORT START
    if ($result['type'] != "blacklist" && $result['weeklyreport'] == 'on' && $result['lastweeklyreport'] + 604800 < time()) {
        if ($result['lastweeklyreport'] == 0 || $result['lastweeklyreport'] == "") {
            $queryd = mysql_query("UPDATE `mod_servermonitoring_monitors` SET `lastweeklyreport`='" . time() . "' WHERE `id`='" . $result['id'] . "'");
            continue;
        }
        $totaldowntime = unserialize($result['totaldowntime']);
        $weeklydata = array();
        foreach ($totaldowntime AS $key => $value) {
            $keya = explode(",", $key);
            $month = $keya[0];
            $year = $keya[1];
            $week = '';
            if (isset($keya[2]))
                $week = $keya[2];
            if ($week != date('W'))
                continue;
            $value = round($value / 60);
            $minutes = 60 * 24 * 7;

            $totaluptime = number_format(((($minutes - $value) / ($minutes)) * 100), 2) . "%";
            $totaluptime = str_replace(".00%", "%", $totaluptime);

            $valuea = number_format($value) . " " . $_ADDONLANG['minutes'];

            $weeklydata[$key]['uptime_percentage'] = $totaluptime;
            $weeklydata[$key]['total_downtime'] = $valuea;
            $weeklydata[$key]['url'] = $result['url'] . ':' . $result['port'];
        }

        if (!empty($result['url'])) {
            $domip = 'http://|' . $result['url'];
            $domip = str_replace('http://|http://', 'http://|', $domip);
            $domip = str_replace('http://|https://', 'https://|', $domip);
            $domipa = explode('|', $domip);
            $domtype = $domipa[0];
            $domip = $domipa[1];
            if (!empty($result['port'])) {
                $domip = $domip . ':' . $result['port'];
            }
        }

        $line = '<hr>';
        $weeklydata = array_reverse($weeklydata);
        if (!empty($weeklydata)) {
            foreach ($weeklydata AS $key => $value) {
                if (!empty($result['url'])) {
                    $line .= $_ADDONLANG['urlip'] . ': <a href="' . $domtype . $domip . '">' . $domip . '</a><br>';
                }
                $line .= $_ADDONLANG['date'] . ': ' . date('Y-m-d', strtotime('-1 weeks')) . ' --- ' . date('Y-m-d') . '<br>';
                $line .= $_ADDONLANG['totaldowntime'] . ': ' . $weeklydata[$key]['total_downtime'] . '<br>';
                $line .= $_ADDONLANG['uptimescore'] . ': ' . $weeklydata[$key]['uptime_percentage'] . '<br>';
                $line .= '<br>';
            }
        }
        if ($line == '<hr>') {
            $line .= $_ADDONLANG['weekly_neveronline'];
        }

        $monitorra['port'] = $result['port'];
        $monitorra['url'] = $result['url'];
        $monitorra = serialize($monitorra);

        if ($result['type'] == "solusvm")
            $emltype = "weeklyreport-solusvm";
        else
            $emltype = "weeklyreport";
        $sendmail = servermonitoring_sendMessage($monitorra, $emltype, $result['serviceid'], $result['id'], $line);
        $queryd = mysql_query("UPDATE `mod_servermonitoring_monitors` SET `lastweeklyreport`='" . time() . "' WHERE `id`='" . $result['id'] . "'");
        unset($monitorra);
        unset($line);
        unset($weeklydata);
    }
    // WEEKLY REPORT END
    if ($result['type'] == "blacklist") {
        $start = microtime(true);
        $blstatus = servermonitoring_checkstatus_blacklist($result['url']);
        if ($blstatus['success'] == 1) {
            $finish = microtime(true);
            $time = round((($finish - $start) * 1000), 0) . " ms";
            $listed = false;
            $bloutput = '';
            foreach ($blstatus['result'] AS $key => $value) {
                if ($blstatus['result'][$key]['return']) {
                    if (trim($blstatus['result'][$key]['return']) != '') {
                        $bloutput[$key] = $blstatus['result'][$key]['return'];
                    }
                    $listed = true;
                }
            }
            if ($listed) {
                Capsule::table('mod_servermonitoring_response')->insert(['status' => '0', 'res_time' => $timestamp, 'res_server_id' => $result['id'], 'port' => '', 'loadtime' => $time]);
                $querye = mysql_query("UPDATE `mod_servermonitoring_monitors` SET `blacklisted`='1' WHERE `id`='" . $result['id'] . "'");
            } else {
                Capsule::table('mod_servermonitoring_response')->insert(['status' => '1', 'res_time' => $timestamp, 'res_server_id' => $result['id'], 'port' => '', 'loadtime' => $time]);
                $querye = mysql_query("UPDATE `mod_servermonitoring_monitors` SET `blacklisted`='0' WHERE `id`='" . $result['id'] . "'");
            }
            $bloutput = serialize($bloutput);
            $blstatus = serialize($blstatus);
            if ($bloutput == 's:0:"";')
                $bloutput = '';
            $querye = mysql_query("UPDATE `mod_servermonitoring_monitors` SET `totaldowntime`='" . $bloutput . "', `lastmonitor`='" . time() . "' WHERE `id`='" . $result['id'] . "'");
            if ($bloutput != $result['totaldowntime']) {
                if (($timestamp - $result['lastemail']) >= $result['emailinterval'] || $result['lastemail'] == '0') {
                    $mail = servermonitoring_sendMessage($blstatus, "blacklist-report", $result['serviceid'], $result['id']);
                    $queryf = mysql_query("UPDATE `mod_servermonitoring_monitors` SET `lastemail`='" . $timestamp . "' WHERE `id`='" . $result['id'] . "'");
                }

                if (($timestamp - $result['lastsms']) >= $result['smsinterval'] && $services['smscredits'] > 0 || $result['lastsms'] == '0' && $services['smscredits'] > 0) {
                    foreach ($tosms AS $valueb) {
                        $queryi = mysql_query("SELECT `smscredits` FROM `mod_servermonitoring_services` WHERE `id`='" . $services['id'] . "'");
                        $servicesa = mysql_fetch_assoc($queryi);

                        if ($servicesa['smscredits'] > 0 && $settings['allowSMS'] == 'on') {
                            $queryg = mysql_query("UPDATE `mod_servermonitoring_monitors` SET `lastsms`='" . $timestamp . "' WHERE `id`='" . $result['id'] . "'");
                            $sms = servermonitoring_sendSMS($valueb, 'blacklist-report', $blstatus, $result['serviceid'], $result['id']);
                            $querym = mysql_query("UPDATE `mod_servermonitoring_services` SET `smscredits`=smscredits-1 WHERE `id`='" . $services['id'] . "'");
                        }
                    }
                }
            }
        }
        continue;
    }

    if (!empty($result['solusvm_url']) && !empty($result['solusvm_key']) && !empty($result['solusvm_hash']) && empty($result['url']) && $result['type'] == "solusvm") {
        if ($result['location'] != '0' && !empty($result['location'])) {
            $solusvmmonitor = servermonitoring_checkstatus_solusvm($result['solusvm_url'], $result['solusvm_key'], $result['solusvm_hash'], $result['location']);
        } else {
            $solusvmmonitor = servermonitoring_checkstatus_solusvm($result['solusvm_url'], $result['solusvm_key'], $result['solusvm_hash'], 0);
        }
        if ($result['solusvm_autoreboot'] == 1 && $solusvmmonitor['status'] == 0 && $result['action'] == "") {
            $reboot = servermonitoring_reboot_solusvm($result['solusvm_url'], $result['solusvm_key'], $result['solusvm_hash']);
            if ($reboot['status'] == 1) {
                $queryd = mysql_query("UPDATE `mod_servermonitoring_monitors` SET `action`='rebooted' WHERE `id`='" . $result['id'] . "'");
            }
        }
    } elseif (!empty($result['url']) && empty($result['solusvm_url']) && empty($result['solusvm_key']) && empty($result['solusvm_hash']) && $result['type'] == "standard") {
        if ($result['location'] != '0' && !empty($result['location'])) {
            $keyword = '';
            if ($result['keyword'] != '') {
                $keyword = $result['keyword'];
            }
            if ($result['k_username'] != '') {
                $keyword = array('keyword' => $result['keyword'], 'username' => $result['k_username'], 'password' => $result['k_password']);
            }
            $monitor = servermonitoring_checkstatus($result['url'], $result['port'], $result['location'], $keyword);
        } else {
            $keyword = '';
            if ($result['keyword'] != '') {
                $keyword = $result['keyword'];
            }
            if ($result['k_username'] != '') {
                $keyword = array('keyword' => $result['keyword'], 'username' => $result['k_username'], 'password' => $result['k_password']);
            }
            $monitor = servermonitoring_checkstatus($result['url'], $result['port'], 0, $keyword);
        }
    }
    $results = unserialize($result['results']);
    $results[$timestamp] = serialize($monitor);
    $results = serialize($results);
    $reason = 'OK';
    if (!isset($monitor['status']) || @is_null($monitor['status']))
        continue;
    if (!isset($monitor['time']))
        $monitor['time'] = '0 ms';
    if (!isset($monitor['time']) || $monitor['time'] == '' || is_null($monitor['time']))
        $monitor['status'] = 0;
    if ($monitor['status'] == 1)
        $online = '1';
    else
        $online = '0';
    if ($solusvmmonitor['status'] == 1)
        $solusvmonline = '1';
    else
        $solusvmonline = '0';
    $s_online = '';
    if ($solusvmmonitor['status'] == 1 || $monitor['status'] == 1)
        $s_online = 1;
    else {
        $s_online = 0;
        $reason = 'Connection Timeout';
    }
    //New Stat Adding
    $tt = $timestamp - rand(1, 300);
    $stat_record = Capsule::table('mod_servermonitoring_stat')->where('mid', $result['id'])->orderBy('id', 'DESC')->take(1)->first();
    if (count($stat_record) >= 1) {
        if ($stat_record->type == $monitor['status']) {
            Capsule::table('mod_servermonitoring_stat')->where('id', $stat_record->id)->update(['duration' => $timestamp]);
        } else {
            $son = 0;
            if ($monitor['status'] == 1)
                $son = 1;
            Capsule::table('mod_servermonitoring_stat')->insert(['mid' => $result['id'], 'type' => $son, 'event_date' => $tt, 'reason' => $reason, 'duration' => time()]);
        }
    } else {
        Capsule::table('mod_servermonitoring_stat')->insert(['mid' => $result['id'], 'type' => $monitor['status'], 'event_date' => $tt, 'reason' => $reason, 'duration' => time()]);
    }

    //track response
    $querye = mysql_query("UPDATE `mod_servermonitoring_monitors` SET `lastmonitor`='" . $timestamp . "' WHERE `id`='" . $result['id'] . "'");
    @Capsule::table('mod_servermonitoring_response')->insert(['status' => $monitor['status'], 'res_time' => $timestamp, 'res_server_id' => $result['id'], 'port' => $monitor['port'], 'loadtime' => $monitor['time']]);

    if ($solusvmonline == '0' && $solusvmmonitor != '' && $result['type'] == "solusvm") {
        $solusvmmonitor = serialize($solusvmmonitor);
        $downtime = $result['downtime'] + ($interval + $grace);
        $querye = mysql_query("UPDATE `mod_servermonitoring_monitors` SET `online`='0', `downtime`='" . $downtime . "' WHERE `id`='" . $result['id'] . "'");

        if ($services['emaillimit'] > $result['emailssent'] || $services['emaillimit'] == 0 || $services['emaillimit'] == 0 && $result['emailssent'] == 0)
            $allowemailsend = true;
        else
            $allowemailsend = false;
        if (($timestamp - $result['lastemail']) >= $result['emailinterval'] && $allowemailsend || $result['lastemail'] == '0' && $allowemailsend) {
            if ($services['emaillimit'] > 0)
                $queryf = mysql_query("UPDATE `mod_servermonitoring_monitors` SET `emailssent`=emailssent+1 WHERE `id`='" . $result['id'] . "'");
            $queryf = mysql_query("UPDATE `mod_servermonitoring_monitors` SET `lastemail`='" . $timestamp . "' WHERE `id`='" . $result['id'] . "'");
            if ($reboot['status'] == 1) {
                $mail = servermonitoring_sendMessage($solusvmmonitor, "solusvm-gone-down-rebooted", $result['serviceid'], $result['id']);
            } else {
                $mail = servermonitoring_sendMessage($solusvmmonitor, "solusvm-gone-down", $result['serviceid'], $result['id']);
            }
        }
        if ($services['smslimit'] > $result['smssent'] || $services['smslimit'] == 0 || $services['smslimit'] == 0 && $result['smssent'] == 0)
            $allowsmssend = true;
        else
            $allowsmssend = false;
        if (($timestamp - $result['lastsms']) >= $result['smsinterval'] && $allowsmssend && $services['smscredits'] > 0 || $result['lastsms'] == '0' && $services['smscredits'] > 0 && $allowsmssend) {
            if ($services['smslimit'] > 0)
                $queryf = mysql_query("UPDATE `mod_servermonitoring_monitors` SET `smssent`=smssent+1 WHERE `id`='" . $result['id'] . "'");
            foreach ($tosms AS $valueb) {
                $queryi = mysql_query("SELECT `smscredits` FROM `mod_servermonitoring_services` WHERE `id`='" . $services['id'] . "'");
                $servicesa = mysql_fetch_assoc($queryi);
                if ($servicesa['smscredits'] > 0 && $settings['allowSMS'] == 'on') {
                    $queryg = mysql_query("UPDATE `mod_servermonitoring_monitors` SET `lastsms`='" . $timestamp . "' WHERE `id`='" . $result['id'] . "'");
                    if ($reboot['status'] == 1) {
                        $sms = servermonitoring_sendSMS($valueb, 'solusvm-gone-down-rebooted', $solusvmmonitor, $result['serviceid'], $result['id']);
                    } else {
                        $sms = servermonitoring_sendSMS($valueb, 'solusvm-gone-down', $solusvmmonitor, $result['serviceid'], $result['id']);
                    }
                    $querym = mysql_query("UPDATE `mod_servermonitoring_services` SET `smscredits`=smscredits-1 WHERE `id`='" . $services['id'] . "'");
                }
            }
        }
    } elseif ($solusvmonline == '1' && $solusvmmonitor != '' && $result['type'] == "solusvm") {
        $totaldowntime = unserialize($result['totaldowntime']);
        $key = date("M,Y,W");
        if ($totaldowntime[$key] == "") {
            $totaldowntime[$key] = $result['downtime'];
        } else {
            $totaldowntime[$key] = $totaldowntime[$key] + $result['downtime'];
        }
        if ($result['downtime'] != 0) {
            $solusvmmonitor = serialize($solusvmmonitor);
            $mail = servermonitoring_sendMessage($solusvmmonitor, "solusvm-back-up", $result['serviceid'], $result['id'], '', $result['downtime'], $_ADDONLANG);

            foreach ($tosms AS $valuec) {
                $queryj = mysql_query("SELECT `smscredits` FROM `mod_servermonitoring_services` WHERE `id`='" . $services['id'] . "'");
                $servicesa = mysql_fetch_assoc($queryj);
                if ($servicesa['smscredits'] > 0 && $settings['allowSMS'] == 'on') {
                    $queryk = mysql_query("UPDATE `mod_servermonitoring_monitors` SET `lastsms`='" . $timestamp . "' WHERE `id`='" . $result['id'] . "'");
                    $sms = servermonitoring_sendSMS($valuec, 'solusvm-back-up', $solusvmmonitor, $result['serviceid'], $result['id'], $result['downtime'], $_ADDONLANG);
                    $queryl = mysql_query("UPDATE `mod_servermonitoring_services` SET `smscredits`=smscredits-1 WHERE `id`='" . $services['id'] . "'");
                }
            }
            $totaldowntime[$key] = $totaldowntime[$key] + ($interval + $grace);
        }
        $totaldowntime = serialize($totaldowntime);
        $queryh = mysql_query("UPDATE `mod_servermonitoring_monitors` SET `online`='1', `action`='', `lastsms`='0', `emailssent`='0', `smssent`='0', `lastemail`='0', `totaldowntime`='" . $totaldowntime . "', `downtime`='' WHERE `id`='" . $result['id'] . "'");
    }

    if ($online == 0 && $monitor != '' && $result['type'] == "standard") {
        $monitor = serialize($monitor);
        $downtime = $result['downtime'] + ($interval + $grace);
        $querye = mysql_query("UPDATE `mod_servermonitoring_monitors` SET `online`='0', `downtime`='" . $downtime . "' WHERE `id`='" . $result['id'] . "'");

        if ($services['emaillimit'] > $result['emailssent'] || $services['emaillimit'] == 0 || $services['emaillimit'] == 0 && $result['emailssent'] == 0)
            $allowemailsend = true;
        else
            $allowemailsend = false;
        if (($timestamp - $result['lastemail']) >= $result['emailinterval'] && $allowemailsend || $result['lastemail'] == '0' && $allowemailsend) {
            if ($services['emaillimit'] > 0)
                $queryf = mysql_query("UPDATE `mod_servermonitoring_monitors` SET `emailssent`=emailssent+1 WHERE `id`='" . $result['id'] . "'");
            if ($result['keyword'] == '')
                $mail = servermonitoring_sendMessage($monitor, "gone-down", $result['serviceid'], $result['id']);
            else
                $mail = servermonitoring_sendMessage($monitor, "keyword-gone-down", $result['serviceid'], $result['id']);
        }
        if ($services['smslimit'] > $result['smssent'] || $services['smslimit'] == 0 || $services['smslimit'] == 0 && $result['smssent'] == 0)
            $allowsmssend = true;
        else
            $allowsmssend = false;
        if (($timestamp - $result['lastsms']) >= $result['smsinterval'] && $allowsmssend && $services['smscredits'] > 0 || $result['lastsms'] == '0' && $services['smscredits'] > 0 && $allowsmssend) {
            if ($services['smslimit'] > 0)
                $queryf = mysql_query("UPDATE `mod_servermonitoring_monitors` SET `smssent`=smssent+1 WHERE `id`='" . $result['id'] . "'");
            foreach ($tosms AS $valueb) {
                $queryi = mysql_query("SELECT `smscredits` FROM `mod_servermonitoring_services` WHERE `id`='" . $services['id'] . "'");
                $servicesa = mysql_fetch_assoc($queryi);
                if ($servicesa['smscredits'] > 0 && $settings['allowSMS'] == 'on') {
                    $queryg = mysql_query("UPDATE `mod_servermonitoring_monitors` SET `lastsms`='" . $timestamp . "' WHERE `id`='" . $result['id'] . "'");
                    $sms = servermonitoring_sendSMS($valueb, 'gone-down', $monitor, $result['serviceid'], $result['id']);
                    $querym = mysql_query("UPDATE `mod_servermonitoring_services` SET `smscredits`=smscredits-1 WHERE `id`='" . $services['id'] . "'");
                }
            }
        }
    } elseif ($monitor != '' && $result['type'] == "standard") {
        $totaldowntime = unserialize($result['totaldowntime']);
        $key = date("M,Y,W");
        if ($totaldowntime[$key] == "") {
            $totaldowntime[$key] = $result['downtime'];
        } else {
            $totaldowntime[$key] = $totaldowntime[$key] + $result['downtime'];
        }
        if ($result['downtime'] != 0) {
            $monitor = serialize($monitor);
            if ($result['keyword'] == '')
                $mail = servermonitoring_sendMessage($monitor, "back-up", $result['serviceid'], $result['id'], '', $result['downtime'], $_ADDONLANG);
            else
                $mail = servermonitoring_sendMessage($monitor, "keyword-back-up", $result['serviceid'], $result['id'], '', $result['downtime'], $_ADDONLANG);
            foreach ($tosms AS $valuec) {
                $queryj = mysql_query("SELECT `smscredits` FROM `mod_servermonitoring_services` WHERE `id`='" . $services['id'] . "'");
                $servicesa = mysql_fetch_assoc($queryj);
                if ($servicesa['smscredits'] > 0 && $settings['allowSMS'] == 'on') {
                    $queryk = mysql_query("UPDATE `mod_servermonitoring_monitors` SET `lastsms`='" . $timestamp . "' WHERE `id`='" . $result['id'] . "'");
                    $sms = servermonitoring_sendSMS($valuec, 'back-up', $monitor, $result['serviceid'], $result['id'], $result['downtime'], $_ADDONLANG);
                    $queryl = mysql_query("UPDATE `mod_servermonitoring_services` SET `smscredits`=smscredits-1 WHERE `id`='" . $services['id'] . "'");
                }
            }
            $totaldowntime[$key] = $totaldowntime[$key] + ($interval + $grace);
        }
        $totaldowntime = serialize($totaldowntime);
        $queryh = mysql_query("UPDATE `mod_servermonitoring_monitors` SET `online`='1', `action`='', `lastsms`='0', `lastemail`='0', `emailssent`='0', `smssent`='0', `totaldowntime`='" . $totaldowntime . "', `downtime`='' WHERE `id`='" . $result['id'] . "'");
    }
}
if (date('H-i') == '23-59') {
    $add_day = date('Y-m-d');
    $s_list = Capsule::table('mod_servermonitoring_monitors')->where('status', 'Active')->get();
    foreach ($s_list as $value) {
        $lasti = Capsule::table('mod_servermonitoring_status')->where('mid', $value->id)->orderBy('id', 'DESC')->take(1)->select('lastid')->first();
        $lastid = $lasti->lastid;
        if ($lastid == '')
            $lastid = 0;
        $count = Capsule::table('mod_servermonitoring_response')->where('res_server_id', $value->id)->where('id', '>', $lastid)->count();
        if ($count > 0) {
            $error_count = Capsule::table('mod_servermonitoring_response')->where('res_server_id', $value->id)->where('id', '>', $lastid)->where('status', '0')->count();
            $lasti = Capsule::table('mod_servermonitoring_response')->where('res_server_id', $value->id)->orderBy('id', 'DESC')->take(1)->select('id')->first();
            $lastid = $lasti->id;
            Capsule::table('mod_servermonitoring_status')->insert(['res_date' => $add_day, 'lastid' => $lastid, 'mid' => $value->id, 'req_count' => $count, 'error_count' => $error_count]);
        }
    }
}
if ($error)
    echo 0;
else
    echo 1;