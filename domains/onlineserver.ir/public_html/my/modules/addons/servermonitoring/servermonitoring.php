<?php

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

use Illuminate\Database\Capsule\Manager as Capsule;

include(ROOTDIR . '/modules/addons/servermonitoring/includes/functions.inc.php');

function servermonitoring_config() {
    $configarray = array(
        "name" => "Server Monitoring",
        "description" => "Server Monitoring is an advanced monitoring system which allows your clients to monitor their websites or servers.",
        "version" => "5.6.0",
        "author" => "WHMCSServices",
        "language" => "english",
        "fields" => array(
    ));
    return $configarray;
}

function servermonitoring_activate() {
    try {
        Capsule::schema()->create(
                'mod_servermonitoring_settings', function ($table) {
            $table->string('setting');
            $table->text('value');
        }
        );
    } catch (\Exception $e) {
        logActivity("Unable to create mod_servermonitoring_settings: {$e->getMessage()}");
    }
    try {
        Capsule::connection()->transaction(
                function ($connectionManager) {
            $connectionManager->table('tblemailtemplates')->insert(array(
                array(
                    'type' => 'general',
                    'name' => 'Server Monitoring - Monitor Down Email',
                    'subject' => 'Monitor DOWN for URL: {$servermonitoring_url} and Port: {$servermonitoring_port}',
                    'message' => 'Hello {$client_name},<br>NOTE: This is an automatic email please do not respond.<br> We detected that URL: {$servermonitoring_url}, Port: {$servermonitoring_port} has gone down at {$servermonitoring_time}.<br/ >Please check this at your end.<br/ >Thank you,<br/ >{$company_name}',
                    'language' => '',
                    'plaintext' => '0',
                    'custom' => '0',
                    'disabled' => '0',
                ),
                array(
                    'type' => 'general',
                    'name' => 'Server Monitoring - Monitor Keyword Down Email',
                    'subject' => 'Monitor Keyword DOWN for URL: {$servermonitoring_url}',
                    'message' => 'Hello {$client_name},<br>NOTE: This is an automatic email please do not respond.<br> We detected that URL: {$servermonitoring_url} has not found keyword at {$servermonitoring_time}.<br/ >Please check this at your end.<br/ >Thank you,<br/ >{$company_name}',
                    'language' => '',
                    'plaintext' => '0',
                    'custom' => '0',
                    'disabled' => '0',
                ),
                array(
                    'type' => 'general',
                    'name' => 'Server Monitoring - Monitor Up Email',
                    'subject' => 'Monitor UP for URL: {$servermonitoring_url}',
                    'message' => 'Hello {$client_name},<br>NOTE: This is an automatic email please do not respond.<br> We detected that URL: {$servermonitoring_url} is back online at {$servermonitoring_time}. Was down for {$servermonitoring_downtime}.<br/ >Please check this at your end.<br/ >Thank you,<br/ >{$company_name}',
                    'language' => '',
                    'plaintext' => '0',
                    'custom' => '0',
                    'disabled' => '0',
                ),
                array(
                    'type' => 'general',
                    'name' => 'Server Monitoring - Monitor Keyword Up Email',
                    'subject' => 'Monitor Keyword UP for URL: {$servermonitoring_url}',
                    'message' => 'Hello {$client_name},<br>NOTE: This is an automatic email please do not respond.<br> We detected your monitoring keyword on that URL: {$servermonitoring_url} is back online at {$servermonitoring_time}. Was down for {$servermonitoring_downtime}.<br/ >Please check this at your end.<br/ >Thank you,<br/ >{$company_name}',
                    'language' => '',
                    'plaintext' => '0',
                    'custom' => '0',
                    'disabled' => '0',
                ),
                array(
                    'type' => 'general',
                    'name' => 'Server Monitoring - SolusVM Monitor Down Email',
                    'subject' => 'Monitor DOWN for {$servermonitoring_monitorname} (SolusVM VPS)',
                    'message' => 'Hello {$client_name},<br />NOTE: This is an automatic email please do not respond.<br />We detected that {$servermonitoring_monitorname} (SolusVM VPS) has gone down at {$servermonitoring_time}.<br />We have rebooted your VPS and it should be back online shortly.<br />Please check this at your end.<br />Thank you,<br />{$company_name}',
                    'language' => '',
                    'plaintext' => '0',
                    'custom' => '0',
                    'disabled' => '0',
                ),
                array(
                    'type' => 'general',
                    'name' => 'Server Monitoring - SolusVM Monitor Down and Rebooted Email',
                    'subject' => 'Monitor DOWN and VPS has been rebooted for {$servermonitoring_monitorname} (SolusVM VPS)',
                    'message' => 'Hello {$client_name},<br />NOTE: This is an automatic email please do not respond.<br />We detected that {$servermonitoring_monitorname} (SolusVM VPS) has gone down at {$servermonitoring_time}.<br />We have rebooted your VPS and it should be back online shortly.<br />Please check this at your end.<br />Thank you,<br />{$company_name}',
                    'language' => '',
                    'plaintext' => '0',
                    'custom' => '0',
                    'disabled' => '0',
                ),
                array(
                    'type' => 'general',
                    'name' => 'Server Monitoring - SolusVM Monitor Up Email',
                    'subject' => 'Monitor UP for {$servermonitoring_monitorname} (SolusVM VPS)',
                    'message' => 'Hello {$client_name},<br />NOTE: This is an automatic email please do not respond.<br />We detected that {$servermonitoring_monitorname} (SolusVM VPS) is back online at {$servermonitoring_time}. Was down for {$servermonitoring_downtime}.<br />Please check this at your end.<br />Thank you,<br />{$company_name}',
                    'language' => '',
                    'plaintext' => '0',
                    'custom' => '0',
                    'disabled' => '0',
                ),
                array(
                    'type' => 'general',
                    'name' => 'Server Monitoring - Blacklist Change Email',
                    'subject' => 'There is a blacklist change for {$servermonitoring_url}',
                    'message' => 'Hello, {$client_name},<br />NOTE: This is an automatic email please do not respond.<br />There is a change to your blacklist for {$servermonitoring_url}. Please login to your account to see the full report.<br />Thank you,<br />{$company_name}',
                    'language' => '',
                    'plaintext' => '0',
                    'custom' => '0',
                    'disabled' => '0',
                ),
                array(
                    'type' => 'general',
                    'name' => 'Server Monitoring - Weekly Uptime Report',
                    'subject' => 'Weekly Uptime Report for URL: {$servermonitoring_url}, Port: {$servermonitoring_port}',
                    'message' => 'Hello {$client_name},<br />NOTE: This is an automatic email please do not respond.<br />Here is your weekly report for URL: {$servermonitoring_url}, Port: {$servermonitoring_port}.<br />{$servermonitoring_weeklyreport}<br />Thank you,<br />{$company_name}',
                    'language' => '',
                    'plaintext' => '0',
                    'custom' => '0',
                    'disabled' => '0',
                ),
                array(
                    'type' => 'general',
                    'name' => 'Server Monitoring - Weekly SolusVM Uptime Report',
                    'subject' => 'Weekly Uptime Report for {$servermonitoring_monitorname}',
                    'message' => 'Hello {$client_name},<br />NOTE: This is an automatic email please do not respond.<br />Here is your weekly report for monitor: {$servermonitoring_monitorname}.<br />{$servermonitoring_weeklyreport}<br />Thank you,<br />{$company_name}',
                    'language' => '',
                    'plaintext' => '0',
                    'custom' => '0',
                    'disabled' => '0',
                ),
            ));
        }
        );
    } catch (\Exception $e) {
        logActivity("Unable to insert into tblemailtemplates: {$e->getMessage()}");
    }

    try {
        Capsule::connection()->transaction(
                function ($connectionManager) {
            $connectionManager->table('mod_servermonitoring_settings')->insert(array(
                array(
                    'setting' => 'clientQuickCheck',
                    'value' => '0',
                ),
                array(
                    'setting' => 'allowPing',
                    'value' => '0',
                ),
                array(
                    'setting' => 'allowSMS',
                    'value' => '0',
                ),
                array(
                    'setting' => 'smsGateway',
                    'value' => 'clickatell',
                ),
                array(
                    'setting' => 'api_id',
                    'value' => '',
                ),
                array(
                    'setting' => 'api_username',
                    'value' => '',
                ),
                array(
                    'setting' => 'api_password',
                    'value' => '',
                ),
                array(
                    'setting' => 'api_sender',
                    'value' => '',
                ),
                array(
                    'setting' => 'api_address',
                    'value' => '',
                ),
                array(
                    'setting' => 'banner',
                    'value' => '',
                ),
                array(
                    'setting' => 'chart_time',
                    'value' => '24',
                ),
                array(
                    'setting' => 'rss_enable',
                    'value' => 'on',
                ),
                array(
                    'setting' => 'localhostName',
                    'value' => 'Localhost',
                )
            ));
        }
        );
    } catch (\Exception $e) {
        logActivity("Unable to insert into mod_servermonitoring_settings: {$e->getMessage()}");
    }

    try {
        Capsule::schema()->create(
                'mod_servermonitoring_maintenance', function ($table) {
            $table->increments('id');
            $table->integer('serviceid');
            $table->integer('mid');
            $table->string('message');
            $table->string('from');
            $table->string('to');
            $table->integer('status');
        }
        );
    } catch (\Exception $e) {
        logActivity("Unable to create mod_servermonitoring_maintenance: {$e->getMessage()}");
    }
    try {
        Capsule::schema()->create(
                'mod_servermonitoring_ports', function ($table) {
            $table->increments('id');
            $table->integer('port');
            $table->string('desc');
            $table->integer('serviceid');
            $table->integer('uid');
        }
        );
    } catch (\Exception $e) {
        logActivity("Unable to create mod_servermonitoring_ports: {$e->getMessage()}");
    }
    try {
        Capsule::schema()->create(
                'mod_servermonitoring_pages', function ($table) {
            $table->increments('id');
            $table->integer('service_id');
            $table->string('monitors');
            $table->integer('uid');
            $table->integer('status');
            $table->string('logo');
            $table->string('title');
            $table->string('accesskey');
        }
        );
    } catch (\Exception $e) {
        logActivity("Unable to create mod_servermonitoring_pages: {$e->getMessage()}");
    }
    try {
        Capsule::schema()->create(
                'mod_servermonitoring_services', function ($table) {
            $table->increments('id');
            $table->integer('serviceid');
            $table->integer('uid');
            $table->integer('smscredits');
            $table->string('smsrecipient');
            $table->integer('emaillimit');
            $table->integer('smslimit');
            $table->string('status');
        }
        );
    } catch (\Exception $e) {
        logActivity("Unable to create mod_servermonitoring_services: {$e->getMessage()}");
    }
    try {
        Capsule::schema()->create(
                'mod_servermonitoring_stat', function ($table) {
            $table->increments('id');
            $table->integer('mid');
            $table->integer('type');
            $table->integer('event_date');
            $table->string('reason');
            $table->integer('duration');
        }
        );
    } catch (\Exception $e) {
        logActivity("Unable to create mod_servermonitoring_stat: {$e->getMessage()}");
    }
    try {
        Capsule::schema()->create(
                'mod_servermonitoring_blacklist', function ($table) {
            $table->increments('id');
            $table->string('server_name');
            $table->string('server_url');
            $table->string('removal_url');
            $table->enum('status', array('0', '1'));
        }
        );
    } catch (\Exception $e) {
        logActivity("Unable to create mod_servermonitoring_blacklist: {$e->getMessage()}");
    }

    try {
        Capsule::connection()->transaction(
                function ($connectionManager) {
            $connectionManager->table('mod_servermonitoring_blacklist')->insert(array(
                array(
                    'id' => '1',
                    'server_name' => 'Dronebl.org',
                    'server_url' => 'dnsbl.dronebl.org',
                    'removal_url' => 'http://dronebl.org/lookup',
                    'status' => '1',
                ),
                array(
                    'id' => '2',
                    'server_name' => 'Njabl.org',
                    'server_url' => 'dnsbl.njabl.org',
                    'removal_url' => 'http://dnsbl.njabl.org',
                    'status' => '1',
                ),
                array(
                    'id' => '3',
                    'server_name' => 'Sorbs.net',
                    'server_url' => 'dnsbl.sorbs.net',
                    'removal_url' => 'http://www.sorbs.net/lookup.shtml',
                    'status' => '1',
                ),
                array(
                    'id' => '4',
                    'server_name' => 'UCEProtect Level 2',
                    'server_url' => 'dnsbl-2.uceprotect.net',
                    'removal_url' => 'http://www.uceprotect.net/en/rblcheck.php',
                    'status' => '1',
                ),
                array(
                    'id' => '5',
                    'server_name' => 'Inps.de',
                    'server_url' => 'dnsbl.inps.de',
                    'removal_url' => 'http://dnsbl.inps.de/query.cgi?lang=en',
                    'status' => '1',
                ),
                array(
                    'id' => '6',
                    'server_name' => 'Surriel.com',
                    'server_url' => 'psbl.surriel.com',
                    'removal_url' => 'http://psbl.org/listing',
                    'status' => '1',
                ),
                array(
                    'id' => '7',
                    'server_name' => 'Barracuda',
                    'server_url' => 'barracudacentral.org/rbl',
                    'removal_url' => 'http://www.barracudanetworks.com/reputation/?pr=1',
                    'status' => '1',
                ),
                array(
                    'id' => '8',
                    'server_name' => 'Lashback',
                    'server_url' => 'www.lashback.com',
                    'removal_url' => 'http://www.lashback.com/blacklist/',
                    'status' => '1',
                ),
                array(
                    'id' => '9',
                    'server_name' => 'Sorbs SPAM',
                    'server_url' => 'spam.dnsbl.sorbs.net',
                    'removal_url' => 'http://www.sorbs.net/lookup.shtml',
                    'status' => '1',
                ),
                array(
                    'id' => '10',
                    'server_name' => 'Sorbs Web',
                    'server_url' => 'web.dnsbl.sorbs.net',
                    'removal_url' => 'http://www.sorbs.net/lookup.shtml',
                    'status' => '1',
                ),
                array(
                    'id' => '11',
                    'server_name' => 'Protected Sky',
                    'server_url' => 'bad.psky.me',
                    'removal_url' => 'http://psky.me/check/',
                    'status' => '1',
                ),
                array(
                    'id' => '12',
                    'server_name' => 'nsZones SBL',
                    'server_url' => 'db.nszones.com',
                    'removal_url' => 'http://db.nszones.com/sbl.ip?',
                    'status' => '1',
                ),
                array(
                    'id' => '13',
                    'server_name' => 'nsZones DYN',
                    'server_url' => 'db.nszones.com',
                    'removal_url' => 'http://db.nszones.com/dyn.ip?',
                    'status' => '1',
                ),
                array(
                    'id' => '14',
                    'server_name' => 'nsZones WhiteList',
                    'server_url' => 'nszones.com',
                    'removal_url' => 'http://db.nszones.com/wl.ip?',
                    'status' => '1',
                ),
                array(
                    'id' => '15',
                    'server_name' => 'SpamRats',
                    'server_url' => 'noptr.spamrats.com',
                    'removal_url' => 'http://www.spamrats.com/lookup.php?ip=',
                    'status' => '1',
                ),
                array(
                    'id' => '16',
                    'server_name' => 'Spam Eating Monkey',
                    'server_url' => 'bl.spameatingmonkey.net',
                    'removal_url' => 'https://spameatingmonkey.com/lookup/',
                    'status' => '1',
                ),
                array(
                    'id' => '17',
                    'server_name' => 'EFnet TOR',
                    'server_url' => 'rbl.efnetrbl.org',
                    'removal_url' => 'http://efnetrbl.org/multicheck.php?i=',
                    'status' => '1',
                ),
                array(
                    'id' => '18',
                    'server_name' => 'SenderScore',
                    'server_url' => 'bl.score.senderscore.com',
                    'removal_url' => 'https://www.senderscore.org/lookup.php?lookup=',
                    'status' => '1',
                ),
                array(
                    'id' => '19',
                    'server_name' => 'JustSpam',
                    'server_url' => 'dnsbl.justspam.org',
                    'removal_url' => 'http://www.justspam.org/check-an-ip',
                    'status' => '1',
                ),
                array(
                    'id' => '20',
                    'server_name' => 'SpamRats All',
                    'server_url' => 'all.spamrats.com',
                    'removal_url' => 'http://www.spamrats.com/lookup.php?ip=',
                    'status' => '1',
                ),
                array(
                    'id' => '21',
                    'server_name' => 'Spamhaus XBL',
                    'server_url' => 'xbl.spamhaus.org',
                    'removal_url' => 'https://www.spamhaus.org/query/ip/',
                    'status' => '1',
                ),
                array(
                    'id' => '22',
                    'server_name' => 'Spamcop',
                    'server_url' => 'bl.spamcop.net',
                    'removal_url' => 'https://www.spamcop.net/w3m?action=checkblock&ip',
                    'status' => '1',
                ),
                array(
                    'id' => '23',
                    'server_name' => 'Spamlookup',
                    'server_url' => 'bsb.spamlookup.net',
                    'removal_url' => 'http://bsb.spamlookup.net/lookup',
                    'status' => '1',
                ),
                array(
                    'id' => '24',
                    'server_name' => 'CBL',
                    'server_url' => 'cbl.abuseat.org',
                    'removal_url' => 'http://www.abuseat.org/lookup.cgi?ip=',
                    'status' => '1',
                ),
                array(
                    'id' => '25',
                    'server_name' => 'Spamhaus PBL',
                    'server_url' => 'pbl.spamhaus.org',
                    'removal_url' => 'https://www.spamhaus.org/query/ip/',
                    'status' => '1',
                ),
                array(
                    'id' => '26',
                    'server_name' => 'Spamhaus Zen',
                    'server_url' => 'zen.spamhaus.org',
                    'removal_url' => 'https://www.spamhaus.org/query/ip/',
                    'status' => '1',
                ),
                array(
                    'id' => '27',
                    'server_name' => 'Spamrats DYNA',
                    'server_url' => 'dyna.spamrats.com',
                    'removal_url' => 'http://www.spamrats.com/bl?',
                    'status' => '1',
                ),
                array(
                    'id' => '28',
                    'server_name' => 'Barracuda Reputation',
                    'server_url' => 'b.barracudacentral.org',
                    'removal_url' => 'http://www.barracudanetworks.com/reputation/?r=1&ip=',
                    'status' => '1',
                ),
                array(
                    'id' => '29',
                    'server_name' => 'UCEProtect Level 1',
                    'server_url' => 'dnsbl-1.uceprotect.net',
                    'removal_url' => 'http://www.uceprotect.net/rblcheck.php?ipr=',
                    'status' => '1',
                ),
                array(
                    'id' => '30',
                    'server_name' => 'Foobar.hu',
                    'server_url' => 'pofon.foobar.hu',
                    'removal_url' => 'https://rbl.foobar.hu/pofon/bl?',
                    'status' => '1',
                ),
                array(
                    'id' => '31',
                    'server_name' => 'Weighted Private Block List',
                    'server_url' => 'db.wpbl.info',
                    'removal_url' => 'http://wpbl.info/record?ip=',
                    'status' => '1',
                ),
                array(
                    'id' => '32',
                    'server_name' => 'Comodo KoruMail',
                    'server_url' => 'srnblack.surgate.net',
                    'removal_url' => 'http://tools.korumail.com/bl/?ip=',
                    'status' => '1',
                ),
                array(
                    'id' => '33',
                    'server_name' => 'Junk Email Filter',
                    'server_url' => 'hostkarma.junkemailfilter.com',
                    'removal_url' => 'http://ipadmin.junkemailfilter.com/remove.php?ip=',
                    'status' => '1',
                ),
                array(
                    'id' => '34',
                    'server_name' => 'BlockList.de',
                    'server_url' => 'bl.blocklist.de',
                    'removal_url' => 'http://www.blocklist.de/en/view.html?ip=',
                    'status' => '1',
                ),
                array(
                    'id' => '35',
                    'server_name' => 'Passive Spam Block List',
                    'server_url' => 'bl.blocklist.de',
                    'removal_url' => 'http://psbl.org/listing?ip=',
                    'status' => '1',
                ),
                array(
                    'id' => '36',
                    'server_name' => 'Return Path Reputation',
                    'server_url' => 'bl.score.senderscore.com',
                    'removal_url' => 'http://www.senderscore.org/blacklistlookup/',
                    'status' => '1',
                ),
                array(
                    'id' => '37',
                    'server_name' => 'LashBack Unsubscribe',
                    'server_url' => 'ubl.unsubscore.com',
                    'removal_url' => 'http://blacklist.lashback.com/',
                    'status' => '1',
                ),
                array(
                    'id' => '38',
                    'server_name' => 'GBUdb.com',
                    'server_url' => 'truncate.gbudb.net',
                    'removal_url' => 'http://www.gbudb.com/truncate/',
                    'status' => '1',
                ),
                array(
                    'id' => '39',
                    'server_name' => 'Backscatterer',
                    'server_url' => 'ips.backscatterer.org',
                    'removal_url' => 'http://www.backscatterer.org/?ip=',
                    'status' => '1',
                ),
                array(
                    'id' => '40',
                    'server_name' => 'MegaRBL',
                    'server_url' => 'rbl.megarbl.net',
                    'removal_url' => 'https://www.megarbl.net/check',
                    'status' => '1',
                ),
                array(
                    'id' => '41',
                    'server_name' => 'DNSBL of NiX Spam',
                    'server_url' => 'ix.dnsbl.manitu.net',
                    'removal_url' => 'http://www.dnsbl.manitu.net/lookup.php?value=',
                    'status' => '1',
                ),
                array(
                    'id' => '42',
                    'server_name' => 'DBlocklisted by DNSRBL',
                    'server_url' => 'dnsrbl.org',
                    'removal_url' => 'http://dnsrbl.org/rbl-lookup-remove/',
                    'status' => '1',
                ),
                array(
                    'id' => '43',
                    'server_name' => 'SpamCannibal',
                    'server_url' => 'bl.spamcannibal.org',
                    'removal_url' => 'http://www.spamcannibal.org/',
                    'status' => '1',
                ),
            ));
        }
        );
    } catch (\Exception $e) {
        logActivity("Unable to insert into mod_servermonitoring_blacklist: {$e->getMessage()}");
    }

    try {
        Capsule::schema()->create(
                'mod_servermonitoring_logs', function ($table) {
            $table->increments('id');
            $table->integer('serviceid');
            $table->string('url');
            $table->integer('port');
            $table->integer('timestamp');
            $table->string('type');
            $table->string('status');
            $table->string('message');
            $table->string('recipient');
        }
        );
    } catch (\Exception $e) {
        logActivity("Unable to create mod_servermonitoring_logs: {$e->getMessage()}");
    }

    try {
        Capsule::schema()->create(
                'mod_servermonitoring_monitors', function ($table) {
            $table->increments('id');
            $table->enum('type', array('standard', 'solusvm', 'blacklist'));
            $table->integer('serviceid');
            $table->string('monitorname');
            $table->string('url');
            $table->integer('port');
            $table->string('solusvm_url');
            $table->string('solusvm_key');
            $table->string('solusvm_hash');
            $table->string('action');
            $table->integer('blacklisted');
            $table->integer('location');
            $table->integer('emailinterval');
            $table->integer('smsinterval');
            $table->integer('emailssent');
            $table->integer('smssent');
            $table->integer('lastemail');
            $table->integer('lastsms');
            $table->integer('lastmonitor');
            $table->string('weeklyreport');
            $table->integer('lastweeklyreport');
            $table->integer('online');
            $table->string('downtime');
            $table->string('totaldowntime');
            $table->integer('cron');
            $table->string('status');
            $table->integer('custom_interval');
            $table->string('keyword');
            $table->string('k_username');
            $table->string('k_password');
            $table->string('accesskey');
        }
        );
    } catch (\Exception $e) {
        logActivity("Unable to create mod_servermonitoring_monitors: {$e->getMessage()}");
    }

    try {
        Capsule::schema()->create(
                'mod_servermonitoring_locations', function ($table) {
            $table->increments('id');
            $table->string('location');
            $table->string('url');
            $table->string('description');
        }
        );
    } catch (\Exception $e) {
        logActivity("Unable to create mod_servermonitoring_locations: {$e->getMessage()}");
    }
    try {
        Capsule::schema()->create(
                'mod_servermonitoring_response', function ($table) {
            $table->increments('id');
            $table->string('res_time');
            $table->integer('res_server_id');
            $table->integer('status');
            $table->integer('port')->nullable();
            $table->string('loadtime')->nullable();
        }
        );
    } catch (\Exception $e) {
        logActivity("Unable to create mod_servermonitoring_response: {$e->getMessage()}");
    }
    try {
        Capsule::schema()->create(
                'mod_servermonitoring_smspackages', function ($table) {
            $table->increments('id');
            $table->string('credits');
            $table->string('pricing');
            $table->string('description');
        }
        );
    } catch (\Exception $e) {
        logActivity("Unable to create mod_servermonitoring_smspackages: {$e->getMessage()}");
    }

    try {
        Capsule::connection()->transaction(
                function ($connectionManager) {
            $connectionManager->table('mod_servermonitoring_ports')->insert(array(
                array(
                    'id' => '1',
                    'port' => '80',
                    'desc' => 'HTTP',
                    'serviceid' => '0',
                    'uid' => '0'
                ),
                array(
                    'id' => '2',
                    'port' => '110',
                    'desc' => 'POP3',
                    'serviceid' => '0',
                    'uid' => '0'
                ),
                array(
                    'id' => '3',
                    'port' => '25',
                    'desc' => 'SMTP',
                    'serviceid' => '0',
                    'uid' => '0'
                ),
                array(
                    'id' => '4',
                    'port' => '443',
                    'desc' => 'HTTPS',
                    'serviceid' => '0',
                    'uid' => '0'
                ),
                array(
                    'id' => '5',
                    'port' => '53',
                    'desc' => 'DNS',
                    'serviceid' => '0',
                    'uid' => '0'
                ),
                array(
                    'id' => '6',
                    'port' => '21',
                    'desc' => 'FTP',
                    'serviceid' => '0',
                    'uid' => '0'
                ),
                array(
                    'id' => '7',
                    'port' => '3306',
                    'desc' => 'MySQL',
                    'serviceid' => '0',
                    'uid' => '0'
                ),
                array(
                    'id' => '8',
                    'port' => '143',
                    'desc' => 'IMAP',
                    'serviceid' => '0',
                    'uid' => '0'
                ),
                array(
                    'id' => '9',
                    'port' => '22',
                    'desc' => 'SSH',
                    'serviceid' => '0',
                    'uid' => '0'
                ),
                array(
                    'id' => '10',
                    'port' => '2534',
                    'desc' => 'Ping',
                    'serviceid' => '0',
                    'uid' => '0'
                ),
                array(
                    'id' => '11',
                    'port' => '80',
                    'desc' => 'WEBSITE URL',
                    'serviceid' => '0',
                    'uid' => '0'
                ),
            ));
        }
        );
    } catch (\Exception $e) {
        logActivity("Unable to insert into mod_servermonitoring_ports: {$e->getMessage()}");
    }
    try {
        Capsule::schema()->create(
                'mod_servermonitoring_status', function ($table) {
            $table->increments('id');
            $table->date('res_date');
            $table->integer('req_count');
            $table->integer('error_count');
            $table->integer('mid');
            $table->integer('lastid');
        }
        );
    } catch (\Exception $e) {
        logActivity("Unable to create mod_servermonitoring_status: {$e->getMessage()}");
    }
    try {
        Capsule::schema()->create(
                'mod_servermonitoring_contacts', function ($table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('mid');
            $table->string('family');
            $table->string('email');
            $table->string('phonenumber');
        }
        );
    } catch (\Exception $e) {
        logActivity("Unable to create mod_servermonitoring_contacts: {$e->getMessage()}");
    }
    return array('status' => 'success', 'description' => 'Server Monitoring has been activated successfully.');
}

function servermonitoring_upgrade($vars) {
    if (isset($vars['servermonitoring']))
        $vars = $vars['servermonitoring'];
    $vars['version'] = str_replace('.', '', $vars['version']);
    if ($vars['version'] < 550) {
        try {
            Capsule::connection()->transaction(
                    function ($connectionManager) {
                $connectionManager->table('mod_servermonitoring_blacklist')->insert(array(
                    array(
                        'server_name' => 'Spamhaus XBL',
                        'server_url' => 'xbl.spamhaus.org',
                        'removal_url' => 'https://www.spamhaus.org/query/ip/',
                        'status' => '1',
                    ),
                    array(
                        'server_name' => 'Spamcop',
                        'server_url' => 'bl.spamcop.net',
                        'removal_url' => 'https://www.spamcop.net/w3m?action=checkblock&ip',
                        'status' => '1',
                    ),
                    array(
                        'server_name' => 'Spamlookup',
                        'server_url' => 'bsb.spamlookup.net',
                        'removal_url' => 'http://bsb.spamlookup.net/lookup',
                        'status' => '1',
                    ),
                    array(
                        'server_name' => 'CBL',
                        'server_url' => 'cbl.abuseat.org',
                        'removal_url' => 'http://www.abuseat.org/lookup.cgi?ip=',
                        'status' => '1',
                    ),
                    array(
                        'server_name' => 'Spamhaus PBL',
                        'server_url' => 'pbl.spamhaus.org',
                        'removal_url' => 'https://www.spamhaus.org/query/ip/',
                        'status' => '1',
                    ),
                    array(
                        'server_name' => 'Spamhaus Zen',
                        'server_url' => 'zen.spamhaus.org',
                        'removal_url' => 'https://www.spamhaus.org/query/ip/',
                        'status' => '1',
                    ),
                    array(
                        'server_name' => 'Spamrats DYNA',
                        'server_url' => 'dyna.spamrats.com',
                        'removal_url' => 'http://www.spamrats.com/bl?',
                        'status' => '1',
                    ),
                    array(
                        'server_name' => 'Barracuda Reputation',
                        'server_url' => 'b.barracudacentral.org',
                        'removal_url' => 'http://www.barracudanetworks.com/reputation/?r=1&ip=',
                        'status' => '1',
                    ),
                    array(
                        'server_name' => 'UCEProtect Level 1',
                        'server_url' => 'dnsbl-1.uceprotect.net',
                        'removal_url' => 'http://www.uceprotect.net/rblcheck.php?ipr=',
                        'status' => '1',
                    ),
                    array(
                        'server_name' => 'Foobar.hu',
                        'server_url' => 'pofon.foobar.hu',
                        'removal_url' => 'https://rbl.foobar.hu/pofon/bl?',
                        'status' => '1',
                    ),
                    array(
                        'server_name' => 'Weighted Private Block List',
                        'server_url' => 'db.wpbl.info',
                        'removal_url' => 'http://wpbl.info/record?ip=',
                        'status' => '1',
                    ),
                    array(
                        'server_name' => 'Comodo KoruMail',
                        'server_url' => 'srnblack.surgate.net',
                        'removal_url' => 'http://tools.korumail.com/bl/?ip=',
                        'status' => '1',
                    ),
                    array(
                        'server_name' => 'Junk Email Filter',
                        'server_url' => 'hostkarma.junkemailfilter.com',
                        'removal_url' => 'http://ipadmin.junkemailfilter.com/remove.php?ip=',
                        'status' => '1',
                    ),
                    array(
                        'server_name' => 'BlockList.de',
                        'server_url' => 'bl.blocklist.de',
                        'removal_url' => 'http://www.blocklist.de/en/view.html?ip=',
                        'status' => '1',
                    ),
                    array(
                        'server_name' => 'Passive Spam Block List',
                        'server_url' => 'bl.blocklist.de',
                        'removal_url' => 'http://psbl.org/listing?ip=',
                        'status' => '1',
                    ),
                    array(
                        'server_name' => 'Return Path Reputation',
                        'server_url' => 'bl.score.senderscore.com',
                        'removal_url' => 'http://www.senderscore.org/blacklistlookup/',
                        'status' => '1',
                    ),
                    array(
                        'server_name' => 'LashBack Unsubscribe',
                        'server_url' => 'ubl.unsubscore.com',
                        'removal_url' => 'http://blacklist.lashback.com/',
                        'status' => '1',
                    ),
                    array(
                        'server_name' => 'GBUdb.com',
                        'server_url' => 'truncate.gbudb.net',
                        'removal_url' => 'http://www.gbudb.com/truncate/',
                        'status' => '1',
                    ),
                    array(
                        'server_name' => 'Backscatterer',
                        'server_url' => 'ips.backscatterer.org',
                        'removal_url' => 'http://www.backscatterer.org/?ip=',
                        'status' => '1',
                    ),
                    array(
                        'server_name' => 'MegaRBL',
                        'server_url' => 'rbl.megarbl.net',
                        'removal_url' => 'https://www.megarbl.net/check',
                        'status' => '1',
                    ),
                    array(
                        'server_name' => 'DNSBL of NiX Spam',
                        'server_url' => 'ix.dnsbl.manitu.net',
                        'removal_url' => 'http://www.dnsbl.manitu.net/lookup.php?value=',
                        'status' => '1',
                    ),
                    array(
                        'server_name' => 'DBlocklisted by DNSRBL',
                        'server_url' => 'dnsrbl.org',
                        'removal_url' => 'http://dnsrbl.org/rbl-lookup-remove/',
                        'status' => '1',
                    ),
                    array(
                        'server_name' => 'SpamCannibal',
                        'server_url' => 'bl.spamcannibal.org',
                        'removal_url' => 'http://www.spamcannibal.org/',
                        'status' => '1',
                    ),
                ));
            }
            );
        } catch (\Exception $e) {
            logActivity("Unable to insert into mod_servermonitoring_blacklist: {$e->getMessage()}");
        }
        if (Capsule::schema()->hasColumn('mod_servermonitoring_monitors', 'contactid')) {
            Capsule::schema()->table('mod_servermonitoring_monitors', function($table) {
                $table->dropColumn('contactid');
            });
        }
    }
    if ($vars['version'] < 543) {
        try {
            Capsule::schema()->create(
                    'mod_servermonitoring_contacts', function ($table) {
                $table->increments('id');
                $table->string('name');
                $table->string('family');
                $table->string('email');
                $table->integer('mid');
                $table->string('phonenumber');
            }
            );
        } catch (\Exception $e) {
            logActivity("Unable to create mod_servermonitoring_contacts: {$e->getMessage()}");
        }
    }
    if ($vars['version'] < 50) {
        full_query("ALTER TABLE `mod_servermonitoring_settings` MODIFY COLUMN `value` text");
        full_query("ALTER TABLE `mod_servermonitoring_monitors` MODIFY COLUMN `totaldowntime` text");
        if (!Capsule::schema()->hasColumn('mod_servermonitoring_monitors', 'keyword')) {
            Capsule::schema()->table('mod_servermonitoring_monitors', function($table) {
                $table->string('keyword');
                $table->string('k_username');
                $table->string('k_password');
                $table->string('accesskey');
            });
        }
        try {
            Capsule::connection()->transaction(
                    function ($connectionManager) {
                $connectionManager->table('mod_servermonitoring_ports')->insert(array(
                    array(
                        'port' => '80',
                        'desc' => 'WEBSITE URL',
                        'serviceid' => '0',
                        'uid' => '0'
                    )
                ));
            }
            );
        } catch (\Exception $e) {
            logActivity("Unable to insert updated PING port into mod_servermonitoring_ports: {$e->getMessage()}");
        }
        try {
            Capsule::connection()->transaction(
                    function ($connectionManager) {
                $connectionManager->table('tblemailtemplates')->insert(array(
                    array(
                        'type' => 'general',
                        'name' => 'Server Monitoring - Monitor Keyword Down Email',
                        'subject' => 'Monitor Keyword DOWN for URL: {$servermonitoring_url}',
                        'message' => 'Hello {$client_name},<br>NOTE: This is an automatic email please do not respond.<br> We detected that URL: {$servermonitoring_url} has not found keyword at {$servermonitoring_time}.<br/ >Please check this at your end.<br/ >Thank you,<br/ >{$company_name}',
                        'language' => '',
                        'plaintext' => '0',
                        'custom' => '0',
                        'disabled' => '0',
                    ),
                    array(
                        'type' => 'general',
                        'name' => 'Server Monitoring - Monitor Keyword Up Email',
                        'subject' => 'Monitor Keyword UP for URL: {$servermonitoring_url}',
                        'message' => 'Hello {$client_name},<br>NOTE: This is an automatic email please do not respond.<br> We detected your monitoring keyword on that URL: {$servermonitoring_url} is back online at {$servermonitoring_time}. Was down for {$servermonitoring_downtime}.<br/ >Please check this at your end.<br/ >Thank you,<br/ >{$company_name}',
                        'language' => '',
                        'plaintext' => '0',
                        'custom' => '0',
                        'disabled' => '0',
                    ),
                ));
            }
            );
        } catch (\Exception $e) {
            logActivity("Unable to insert into tblemailtemplates: {$e->getMessage()}");
        }

        try {
            Capsule::schema()->create(
                    'mod_servermonitoring_pages', function ($table) {
                $table->increments('id');
                $table->integer('service_id');
                $table->string('monitors');
                $table->integer('uid');
                $table->integer('status');
                $table->string('logo');
                $table->string('title');
                $table->string('accesskey');
            }
            );
        } catch (\Exception $e) {
            logActivity("Unable to create mod_servermonitoring_pages: {$e->getMessage()}");
        }
        try {
            Capsule::schema()->create(
                    'mod_servermonitoring_stat', function ($table) {
                $table->increments('id');
                $table->integer('mid');
                $table->integer('type');
                $table->integer('event_date');
                $table->string('reason');
                $table->integer('duration');
            }
            );
        } catch (\Exception $e) {
            logActivity("Unable to create mod_servermonitoring_stat: {$e->getMessage()}");
        }
        try {
            Capsule::connection()->transaction(
                    function ($connectionManager) {
                $connectionManager->table('mod_servermonitoring_settings')->insert(array(
                    array(
                        'setting' => 'banner',
                        'value' => '',
                    ),
                    array(
                        'setting' => 'api_address',
                        'value' => '',
                    ),
                    array(
                        'setting' => 'chart_time',
                        'value' => '24',
                    ),
                    array(
                        'setting' => 'rss_enable',
                        'value' => 'on',
                    ),
                ));
            }
            );
        } catch (\Exception $e) {
            logActivity("Unable to insert updated PING setting into mod_servermonitoring_settings: {$e->getMessage()}");
        }
        try {
            Capsule::schema()->create(
                    'mod_servermonitoring_response', function ($table) {
                $table->increments('id');
                $table->string('res_time');
                $table->integer('res_server_id');
                $table->integer('status');
                $table->integer('port')->nullable();
                $table->string('loadtime')->nullable();
            }
            );
        } catch (\Exception $e) {
            logActivity("Unable to create mod_servermonitoring_response: {$e->getMessage()}");
        }
        try {
            Capsule::schema()->create(
                    'mod_servermonitoring_status', function ($table) {
                $table->increments('id');
                $table->date('res_date');
                $table->integer('req_count');
                $table->integer('error_count');
                $table->integer('mid');
                $table->integer('lastid');
            }
            );
        } catch (\Exception $e) {
            logActivity("Unable to create mod_servermonitoring_status: {$e->getMessage()}");
        }
        $blist = array(
            array(
                'server_name' => 'Protected Sky',
                'server_url' => 'bad.psky.me',
                'removal_url' => 'http://psky.me/check/',
                'status' => '1',
            ),
            array(
                'server_name' => 'nsZones SBL',
                'server_url' => 'db.nszones.com',
                'removal_url' => 'http://db.nszones.com/sbl.ip?',
                'status' => '1',
            ),
            array(
                'server_name' => 'nsZones DYN',
                'server_url' => 'db.nszones.com',
                'removal_url' => 'http://db.nszones.com/dyn.ip?',
                'status' => '1',
            ),
            array(
                'server_name' => 'nsZones WhiteList',
                'server_url' => 'nszones.com',
                'removal_url' => 'http://db.nszones.com/wl.ip?',
                'status' => '1',
            ),
            array(
                'server_name' => 'SpamRats',
                'server_url' => 'noptr.spamrats.com',
                'removal_url' => 'http://www.spamrats.com/lookup.php?ip=',
                'status' => '1',
            ),
            array(
                'server_name' => 'Spam Eating Monkey',
                'server_url' => 'bl.spameatingmonkey.net',
                'removal_url' => 'https://spameatingmonkey.com/lookup/',
                'status' => '1',
            ),
            array(
                'server_name' => 'EFnet TOR',
                'server_url' => 'rbl.efnetrbl.org',
                'removal_url' => 'http://efnetrbl.org/multicheck.php?i=',
                'status' => '1',
            ),
            array(
                'server_name' => 'SenderScore',
                'server_url' => 'bl.score.senderscore.com',
                'removal_url' => 'https://www.senderscore.org/lookup.php?lookup=',
                'status' => '1',
            ),
            array(
                'server_name' => 'JustSpam',
                'server_url' => 'dnsbl.justspam.org',
                'removal_url' => 'http://www.justspam.org/check-an-ip',
                'status' => '1',
            ),
            array(
                'server_name' => 'SpamRats All',
                'server_url' => 'all.spamrats.com',
                'removal_url' => 'http://www.spamrats.com/lookup.php?ip=',
                'status' => '1',
            )
        );
        foreach ($blist as $value => $key) {
            $c = Capsule::table('mod_servermonitoring_blacklist')->Where('server_name', $key['server_name'])->count();
            if ($c <= 0) {
                Capsule::table('mod_servermonitoring_blacklist')->insert(array(
                    'server_name' => $key['server_name'],
                    'server_url' => $key['server_url'],
                    'removal_url' => $key['removal_url'],
                    'status' => '1',
                ));
            }
        }
        /*
          try {
          Capsule::connection()->transaction(
          function ($connectionManager) {
          $connectionManager->table('mod_servermonitoring_blacklist')->insert(array(
          array(
          'server_name' => 'Protected Sky',
          'server_url' => 'bad.psky.me',
          'removal_url' => 'http://psky.me/check/',
          'status' => '1',
          ),
          array(
          'server_name' => 'nsZones SBL',
          'server_url' => 'db.nszones.com',
          'removal_url' => 'http://db.nszones.com/sbl.ip?',
          'status' => '1',
          ),
          array(
          'server_name' => 'nsZones DYN',
          'server_url' => 'db.nszones.com',
          'removal_url' => 'http://db.nszones.com/dyn.ip?',
          'status' => '1',
          ),
          array(
          'server_name' => 'nsZones WhiteList',
          'server_url' => 'nszones.com',
          'removal_url' => 'http://db.nszones.com/wl.ip?',
          'status' => '1',
          ),
          array(
          'server_name' => 'SpamRats',
          'server_url' => 'noptr.spamrats.com',
          'removal_url' => 'http://www.spamrats.com/lookup.php?ip=',
          'status' => '1',
          ),
          array(
          'server_name' => 'Spam Eating Monkey',
          'server_url' => 'bl.spameatingmonkey.net',
          'removal_url' => 'https://spameatingmonkey.com/lookup/',
          'status' => '1',
          ),
          array(
          'server_name' => 'EFnet TOR',
          'server_url' => 'rbl.efnetrbl.org',
          'removal_url' => 'http://efnetrbl.org/multicheck.php?i=',
          'status' => '1',
          ),
          array(
          'server_name' => 'SenderScore',
          'server_url' => 'bl.score.senderscore.com',
          'removal_url' => 'https://www.senderscore.org/lookup.php?lookup=',
          'status' => '1',
          ),
          array(
          'server_name' => 'JustSpam',
          'server_url' => 'dnsbl.justspam.org',
          'removal_url' => 'http://www.justspam.org/check-an-ip',
          'status' => '1',
          ),
          array(
          'server_name' => 'SpamRats All',
          'server_url' => 'all.spamrats.com',
          'removal_url' => 'http://www.spamrats.com/lookup.php?ip=',
          'status' => '1',
          ),
          ));
          }
          );
          } catch (\Exception $e) {
          logActivity("Unable to insert update into mod_servermonitoring_blacklist: {$e->getMessage()}");
          }
         */
        $list = Capsule::table('tblemailtemplates')->orWhere('name', 'like', '%Server Monitoring - %')->get();
        foreach ($list as $value) {
            $search = '&lt;br /&gt;';
            $message = str_replace($search, '<br>', $value->message);
            Capsule::table('tblemailtemplates')->where('id', $value->id)->update(['message' => $message]);
        }
        $list = Capsule::table('mod_servermonitoring_monitors')->select('id')->get();
        foreach ($list as $value) {
            $newkey = rand(1, 1000000) . date('y-m-d h:i:s');
            Capsule::table('mod_servermonitoring_monitors')->where('id', $value->id)->update(['accesskey' => md5($newkey)]);
        }
    }
    if ($vars['version'] < 40) {
        if (!Capsule::schema()->hasColumn('mod_servermonitoring_monitors', 'custom_interval')) {
            Capsule::schema()->table('mod_servermonitoring_monitors', function($table) {
                $table->integer('custom_interval');
            });
        }
    }
    if ($vars['version'] < 41) {
        try {
            Capsule::connection()->transaction(
                    function ($connectionManager) {
                $connectionManager->table('mod_servermonitoring_ports')->insert(array(
                    array(
                        'port' => '2534',
                        'desc' => 'Ping',
                        'serviceid' => '0',
                        'uid' => '0'
                    )
                ));
            }
            );
        } catch (\Exception $e) {
            logActivity("Unable to insert updated PING port into mod_servermonitoring_ports: {$e->getMessage()}");
        }
        try {
            Capsule::connection()->transaction(
                    function ($connectionManager) {
                $connectionManager->table('mod_servermonitoring_settings')->insert(array(
                    array(
                        'setting' => 'allowPing',
                        'value' => '0'
                    ),
                    array(
                        'setting' => 'allowSMS',
                        'value' => '0',
                    )
                ));
            }
            );
        } catch (\Exception $e) {
            logActivity("Unable to insert updated PING setting into mod_servermonitoring_settings: {$e->getMessage()}");
        }
    }
}

function servermonitoring_deactivate($vars) {
    Capsule::schema()->dropIfExists('mod_servermonitoring_settings');
    Capsule::schema()->dropIfExists('mod_servermonitoring_locations');
    Capsule::schema()->dropIfExists('mod_servermonitoring_monitors');
    Capsule::schema()->dropIfExists('mod_servermonitoring_services');
    Capsule::schema()->dropIfExists('mod_servermonitoring_ports');
    Capsule::schema()->dropIfExists('mod_servermonitoring_logs');
    Capsule::schema()->dropIfExists('mod_servermonitoring_blacklist');
    Capsule::schema()->dropIfExists('mod_servermonitoring_smspackages');
    Capsule::schema()->dropIfExists('mod_servermonitoring_maintenance');
    Capsule::schema()->dropIfExists('mod_servermonitoring_status');
    Capsule::schema()->dropIfExists('mod_servermonitoring_stat');
    Capsule::schema()->dropIfExists('mod_servermonitoring_pages');
    Capsule::schema()->dropIfExists('mod_servermonitoring_response');
    Capsule::schema()->dropIfExists('mod_servermonitoring_contacts');
    Capsule::table('tblemailtemplates')->orWhere('name', 'like', '%Server Monitoring - %')->delete();
    return array('status' => 'success', 'description' => 'Server Monitoring has been deactivated and removed from the database.');
}

function servermonitoring_output($vars) {
    $modulelink = $vars['modulelink'];
    $version = $vars['version'];
    $LANG = $vars['_lang'];
    $SETING = servermonitoring_settings();
    global $aInt;
    global $numrows;
    $tabledata = null;
    if (!isset($_REQUEST['a']))
        $_REQUEST['a'] = '';
    if (!isset($_REQUEST['page']))
        $_REQUEST['page'] = '';
    echo '<link rel="stylesheet" href="../modules/addons/servermonitoring/css/styles.css">';
    echo '<ul id="mbmcpebul_table" class="mbmcpebul_menulist css_menu" style="width: 100%; height: 47px;">
			<li class="topitem spaced_li';
    if ($_REQUEST['a'] == '' || $_REQUEST['a'] == 'editmonitor' || $_REQUEST['a'] == 'addmonitor') {
        echo ' active';
    } echo '"><div class="buttonbg gradient_button gradient47" style="width:auto;"><a href="' . $modulelink . '">' . $LANG['home'] . '</a></div></li>
			<li class="topitem spaced_li';
    if ($_REQUEST['a'] == 'services') {
        echo ' active';
    } echo '"><div class="buttonbg gradient_button gradient47" style="width: auto;"><a href="' . $modulelink . '&a=services">' . $LANG['services'] . '</a></div></li>
			<li class="topitem spaced_li';
    if ($_REQUEST['a'] == 'quickcheck' || $_REQUEST['a'] == 'blacklistcheck') {
        echo ' active';
    } echo '"><div class="buttonbg gradient_button gradient47" style="width: auto;"><div class="arrow"><a href="#" class="button_2">' . $LANG['tools'] . '</a></div></div>
				<ul>
					<li class="gradient_menuitem gradient27';
    if ($_REQUEST['a'] == 'quickcheck' && @$_REQUEST['type'] == '') {
        echo ' subactive';
    } echo '"><a href="' . $modulelink . '&a=quickcheck" title="">' . $LANG['quickcheck'] . '</a></li>
					<li class="gradient_menuitem gradient27';
    if ($_REQUEST['a'] == 'quickcheck' && @$_REQUEST['type'] == 'blacklist') {
        echo ' subactive';
    } echo '"><a href="' . $modulelink . '&a=quickcheck&type=blacklist" title="">' . $LANG['blacklistcheck'] . '</a></li>
				</ul>
			</li>
			<li class="topitem spaced_li';
    if ($_REQUEST['a'] == 'ports' || $_REQUEST['a'] == 'locations' || $_REQUEST['a'] == 'smspackages' || $_REQUEST['a'] == 'addport' || $_REQUEST['a'] == 'editport' || $_REQUEST['a'] == 'addlocation' || $_REQUEST['a'] == 'editlocation' || $_REQUEST['a'] == 'blacklist' || $_REQUEST['a'] == 'settings') {
        echo ' active';
    } echo '"><div class="buttonbg gradient_button gradient47" style="width: auto;"><div class="arrow"><a href="#" class="button_2">' . $LANG['management'] . '</a></div></div>
				<ul>
					<li class="gradient_menuitem gradient27';
    if ($_REQUEST['a'] == 'ports' || $_REQUEST['a'] == 'addport' || $_REQUEST['a'] == 'editport') {
        echo ' subactive';
    } echo '"><a href="' . $modulelink . '&a=ports" title="">' . $LANG['ports'] . '</a></li>
<li class="gradient_menuitem gradient27';
    if ($_REQUEST['a'] == 'locations' || $_REQUEST['a'] == 'addlocation' || $_REQUEST['a'] == 'editlocation') {
        echo ' subactive';
    } echo '"><a href="' . $modulelink . '&a=locations" title="">' . $LANG['locations'] . '</a></li>
					<li class="gradient_menuitem gradient27';
    if ($_REQUEST['a'] == 'smspackages') {
        echo ' subactive';
    } echo '"><a href="' . $modulelink . '&a=smspackages" title="">' . $LANG['smspackages'] . '</a></li>
					<li class="gradient_menuitem gradient27';
    if ($_REQUEST['a'] == 'blacklist') {
        echo ' subactive';
    } echo '"><a href="' . $modulelink . '&a=blacklist" title="">' . $LANG['blacklistservers'] . '</a></li>
					<li class="gradient_menuitem gradient27';
    if ($_REQUEST['a'] == 'settings') {
        echo ' subactive';
    } echo '"><a href="' . $modulelink . '&a=settings" title="">' . $LANG['settings'] . '</a></li>
				</ul>
			</li>
                        <li><a href="../index.php?m=servermonitoring&page=public" target="_blank">' . $LANG['MonitoringStats'] . '</a></li>
			<li class="topitem';
    if ($_REQUEST['a'] == 'logs') {
        echo ' active';
    } echo '"><div class="buttonbg gradient_button gradient47" style="width: auto;"><a href="' . $modulelink . '&a=logs">' . $LANG['logs'] . '</a></div></li>
		</ul><br><br>';


    if ($_REQUEST['a'] == "") {
        include(ROOTDIR . '/modules/addons/servermonitoring/includes/monitors.inc.php');
    } elseif ($_REQUEST['a'] == "quickcheck") {
        if (isset($_REQUEST['type']) && $_REQUEST['type'] == "blacklist") {
            include(ROOTDIR . '/modules/addons/servermonitoring/includes/quickcheck-blacklist.inc.php');
        } else {
            include(ROOTDIR . '/modules/addons/servermonitoring/includes/quickcheck.inc.php');
        }
    } elseif ($_REQUEST['a'] == "ports") {
        include(ROOTDIR . '/modules/addons/servermonitoring/includes/ports.inc.php');
    } elseif ($_REQUEST['a'] == "addport") {
        include(ROOTDIR . '/modules/addons/servermonitoring/includes/addport.inc.php');
    } elseif ($_REQUEST['a'] == "editport") {
        include(ROOTDIR . '/modules/addons/servermonitoring/includes/editport.inc.php');
    } elseif ($_REQUEST['a'] == "services") {
        if (isset($_REQUEST['id']) && isset($_REQUEST['editcredits']) && $_REQUEST['editcredits']) {
            include(ROOTDIR . '/modules/addons/servermonitoring/includes/editcredits.inc.php');
        } else {
            include(ROOTDIR . '/modules/addons/servermonitoring/includes/services.inc.php');
        }
    } elseif ($_REQUEST['a'] == "locations") {
        include(ROOTDIR . '/modules/addons/servermonitoring/includes/locations.inc.php');
    } elseif ($_REQUEST['a'] == "addlocation") {
        include(ROOTDIR . '/modules/addons/servermonitoring/includes/addlocation.inc.php');
    } elseif ($_REQUEST['a'] == "editlocation") {
        include(ROOTDIR . '/modules/addons/servermonitoring/includes/editlocation.inc.php');
    } elseif ($_REQUEST['a'] == "editmonitor" && $_REQUEST['type'] == "standard") {
        include(ROOTDIR . '/modules/addons/servermonitoring/includes/editmonitor.inc.php');
    } elseif ($_REQUEST['a'] == "editmonitor" && $_REQUEST['type'] == "solusvm") {
        include(ROOTDIR . '/modules/addons/servermonitoring/includes/editmonitor-solusvm.inc.php');
    } elseif ($_REQUEST['a'] == "editmonitor" && $_REQUEST['type'] == "blacklist") {
        include(ROOTDIR . '/modules/addons/servermonitoring/includes/editmonitor-blacklist.inc.php');
    } elseif ($_REQUEST['a'] == "settings") {
        include(ROOTDIR . '/modules/addons/servermonitoring/includes/settings.inc.php');
    } elseif ($_REQUEST['a'] == "blacklist") {
        if (isset($_REQUEST['edit']) && $_REQUEST['edit'] && isset($_REQUEST['id']) && is_numeric($_REQUEST['id'])) {
            include(ROOTDIR . '/modules/addons/servermonitoring/includes/editblacklist-servers.inc.php');
        } elseif (isset($_REQUEST['add']) && $_REQUEST['add']) {
            include(ROOTDIR . '/modules/addons/servermonitoring/includes/addblacklist-servers.inc.php');
        } else {
            include(ROOTDIR . '/modules/addons/servermonitoring/includes/blacklist-servers.inc.php');
        }
    } elseif ($_REQUEST['a'] == "logs") {
        include(ROOTDIR . '/modules/addons/servermonitoring/includes/logs.inc.php');
    } elseif ($_REQUEST['a'] == "smspackages") {
        if (isset($_REQUEST['edit']) && $_REQUEST['edit'] && isset($_REQUEST['id']) && is_numeric($_REQUEST['id'])) {
            include(ROOTDIR . '/modules/addons/servermonitoring/includes/editsmspackage.inc.php');
        } elseif (isset($_REQUEST['add']) && $_REQUEST['add']) {
            include(ROOTDIR . '/modules/addons/servermonitoring/includes/addsmspackage.inc.php');
        } else {
            include(ROOTDIR . '/modules/addons/servermonitoring/includes/smspackages.inc.php');
        }
    }
    echo '<div class="panel panel-default">
            <div class="panel-body" align="center">Please vote the Addon at <a href="https://marketplace.whmcs.com/product/1774" target="_blank">WHMCS Marketplace</a>. Your feedback is essential for us!</div>
          </div>';
}

function servermonitoring_clientarea($vars = NULL) {
    $modulelink = $vars['modulelink'];
    $version = $vars['version'];
    $LANG = $vars['_lang'];
    $SETING = servermonitoring_settings();
    $templatefile = "clientarea";
    $requirelogin = true;
    $pagetitle = $LANG['clientarea_title'];

    $output = '';
    if (!isset($_REQUEST['type']))
        $_REQUEST['type'] = '';
    if (!isset($_REQUEST['id'])) {
        if (isset($_SESSION['uid'])) {
            if (isset($_REQUEST['page']) && $_REQUEST['page'] == 'public') {
                if (isset($_REQUEST['viewchart']) && $_REQUEST['viewchart']) {
                    $pagetitle = 'chart view';
                    include(ROOTDIR . '/modules/addons/servermonitoring/includes/client-chart.php');
                    $requirelogin = false;
                } else
                    include(ROOTDIR . '/modules/addons/servermonitoring/includes/client-public.inc.php');
            } elseif (isset($_REQUEST['page']) && $_REQUEST['page'] == 'stats') {
                include(ROOTDIR . '/modules/addons/servermonitoring/includes/public-stat.php');
                $requirelogin = false;
            } elseif (isset($_REQUEST['page']) && $_REQUEST['page'] == 'banner') {
                include(ROOTDIR . '/modules/addons/servermonitoring/includes/client-linkus.php');
                $requirelogin = false;
            } else
                include(ROOTDIR . '/modules/addons/servermonitoring/includes/client-services.inc.php');
        } else {
            $pagetitle = $LANG['quickcheck'];
            if ($SETING['clientQuickCheck'] == 'on')
                $requirelogin = false;
            if ($_REQUEST['type'] == "solusvm") {
                include(ROOTDIR . '/modules/addons/servermonitoring/includes/client-quickcheck-solusvm.inc.php');
            } elseif ($_REQUEST['type'] == "blacklist") {
                include(ROOTDIR . '/modules/addons/servermonitoring/includes/client-quickcheck-blacklist.inc.php');
            } elseif (isset($_REQUEST['viewchart']) && $_REQUEST['viewchart']) {
                $pagetitle = 'chart view';
                $requirelogin = false;
                include(ROOTDIR . '/modules/addons/servermonitoring/includes/client-chart.php');
                $requirelogin = false;
            } elseif (isset($_REQUEST['page']) && $_REQUEST['page'] == 'public') {
                include(ROOTDIR . '/modules/addons/servermonitoring/includes/client-public.inc.php');
            } elseif (isset($_REQUEST['page']) && $_REQUEST['page'] == 'stats') {
                include(ROOTDIR . '/modules/addons/servermonitoring/includes/public-stat.php');
                $requirelogin = false;
            } elseif (isset($_REQUEST['page']) && $_REQUEST['page'] == 'banner') {
                include(ROOTDIR . '/modules/addons/servermonitoring/includes/client-linkus.php');
                $requirelogin = false;
            } elseif ($_REQUEST['type'] == "blacklist") {
                include(ROOTDIR . '/modules/addons/servermonitoring/includes/client-quickcheck-blacklist.inc.php');
            } else {
                include(ROOTDIR . '/modules/addons/servermonitoring/includes/client-quickcheck.inc.php');
            }
        }
    } else {
        if (!isset($_REQUEST['mid'])) {
            if (isset($_REQUEST['emailsettings']) && $_REQUEST['emailsettings']) {
                $pagetitle = $LANG['clientarea_emailsettings_title'];
                include(ROOTDIR . '/modules/addons/servermonitoring/includes/client-emailsettings.inc.php');
            } elseif (isset($_REQUEST['smssettings']) && $_REQUEST['smssettings']) {
                $pagetitle = $LANG['clientarea_smssettings_title'];
                include(ROOTDIR . '/modules/addons/servermonitoring/includes/client-smssettings.inc.php');
            } elseif (isset($_REQUEST['smscredits']) && $_REQUEST['smscredits'] == "order") {
                $pagetitle = $LANG['ordersmscredits'];
                include(ROOTDIR . '/modules/addons/servermonitoring/includes/client-smscredits-order.inc.php');
            } elseif (isset($_REQUEST['customport']) && $_REQUEST['customport']) {
                $pagetitle = $LANG['customports'];
                include(ROOTDIR . '/modules/addons/servermonitoring/includes/client-customports.inc.php');
            } elseif (isset($_REQUEST['add']) && $_REQUEST['add']) {
                if (isset($_REQUEST['type']) && $_REQUEST['type'] == "solusvm") {
                    include(ROOTDIR . '/modules/addons/servermonitoring/includes/client-addmonitor-solusvm.inc.php');
                } elseif (isset($_REQUEST['type']) && $_REQUEST['type'] == "blacklist") {
                    include(ROOTDIR . '/modules/addons/servermonitoring/includes/client-addmonitor-blacklist.inc.php');
                } else {
                    include(ROOTDIR . '/modules/addons/servermonitoring/includes/client-addmonitor.inc.php');
                }
            } elseif (isset($_REQUEST['page']) && $_REQUEST['page'] == 'stats') {
                include(ROOTDIR . '/modules/addons/servermonitoring/includes/public-stat.php');
                $requirelogin = false;
            } else {
                $pagetitle = $LANG['clientarea_monitors_title'];
                if (isset($_REQUEST['page']) && $_REQUEST['page'] == 'RSS') {
                    include(ROOTDIR . '/modules/addons/servermonitoring/includes/client-rss.inc.php');
                } else
                if (isset($_REQUEST['page']) && $_REQUEST['page'] == 'publicpages') {
                    include(ROOTDIR . '/modules/addons/servermonitoring/includes/client-publicpages.php');
                } else
                    include(ROOTDIR . '/modules/addons/servermonitoring/includes/client-monitors.inc.php');
            }
        } else {
            if (isset($_REQUEST['edit']) && $_REQUEST['edit']) {
                $pagetitle = $LANG['clientarea_editmonitor_title'];
                if ($_REQUEST['type'] == "blacklist") {
                    include(ROOTDIR . '/modules/addons/servermonitoring/includes/client-editmonitor-blacklist.inc.php');
                } elseif ($_REQUEST['type'] == "solusvm") {
                    include(ROOTDIR . '/modules/addons/servermonitoring/includes/client-editmonitor-solusvm.inc.php');
                } else {
                    include(ROOTDIR . '/modules/addons/servermonitoring/includes/client-editmonitor.inc.php');
                }
            } elseif (isset($_REQUEST['maintenance']) && $_REQUEST['maintenance']) {
                include(ROOTDIR . '/modules/addons/servermonitoring/includes/client-maintenance.inc.php');
            } elseif (isset($_REQUEST['addmaintenance']) && $_REQUEST['addmaintenance']) {
                include(ROOTDIR . '/modules/addons/servermonitoring/includes/client-addmaintenance.inc.php');
            } elseif (isset($_REQUEST['contacts']) && $_REQUEST['contacts']) {
                include(ROOTDIR . '/modules/addons/servermonitoring/includes/client-contacts.php');
            } elseif (isset($_REQUEST['addcontacts']) && $_REQUEST['addcontacts']) {
                include(ROOTDIR . '/modules/addons/servermonitoring/includes/client-addcontacts.php');
            } elseif (isset($_REQUEST['editcontacts']) && $_REQUEST['editcontacts']) {
                include(ROOTDIR . '/modules/addons/servermonitoring/includes/client-editcontacts.php');
            } elseif (isset($_REQUEST['page']) && $_REQUEST['page'] == 'stats') {
                include(ROOTDIR . '/modules/addons/servermonitoring/includes/public-stat.php');
                $requirelogin = false;
            } elseif (isset($_REQUEST['page']) && $_REQUEST['page'] == 'banner') {
                include(ROOTDIR . '/modules/addons/servermonitoring/includes/client-linkus.php');
                $requirelogin = false;
            } elseif (isset($_REQUEST['editmaintenance']) && $_REQUEST['editmaintenance']) {
                include(ROOTDIR . '/modules/addons/servermonitoring/includes/client-editmaintenance.inc.php');
            } else {
                $pagetitle = $LANG['clientarea_manage_title'];
                include(ROOTDIR . '/modules/addons/servermonitoring/includes/client-managemonitor.inc.php');
            }
        }
    }

    $tempvars['output'] = $output;
    if (isset($_GET['page']) && $_GET['page'] == 'public')
        $requirelogin = false;

    return array('pagetitle' => $pagetitle, 'templatefile' => $templatefile, 'vars' => $tempvars, 'requirelogin' => $requirelogin);
}
