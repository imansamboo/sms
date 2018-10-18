<?php

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

$settings = servermonitoring_settings($vars);

if ($settings['allowSMS'] != 'on' && $settings['allowSMS'] != '1') {
    redir('m=servermonitoring&id=' . $_REQUEST['id'] . '');
}

$query = mysql_query("SELECT `currency` FROM `tblclients` WHERE `id`='" . $_SESSION['uid'] . "'");
$client = mysql_fetch_assoc($query);
$querya = mysql_query("SELECT `prefix`,`suffix` FROM `tblcurrencies` WHERE `id`='" . $client['currency'] . "'");
$currency = mysql_fetch_assoc($querya);
$clientcurrency = $client['currency'];
if (isset($_SESSION['uid']) && is_numeric($_SESSION['uid']))
    $currencyData = getCurrency($_SESSION['uid']);
else
    $currencyData = getCurrency();
$default_currency = getCurrency();
$clientcurrency = $currencyData['id'];
if (isset($_REQUEST['pid']) && is_numeric($_REQUEST['pid'])) {

    $query = mysql_query("SELECT * FROM `mod_servermonitoring_smspackages` WHERE `id`='" . mysql_real_escape_string($_REQUEST['pid']) . "'");
    $result = mysql_fetch_assoc($query);
    $tot = mysql_num_rows($query);
    if ($tot == 1) {
        $pricing = unserialize($result['pricing']);
        $price = $pricing['price'][$clientcurrency];
        $setupfee = $pricing['setupfee'][$clientcurrency];
        $values["userid"] = $_SESSION['uid'];
        $values["date"] = date('Ymd');
        $values["duedate"] = date('Ymd');
        $values["sendinvoice"] = true;
        $values["itemdescription1"] = $result['credits'] . ' ' . $LANG['invsmscredits'] . ' - [#28193][' . mysql_real_escape_string($_REQUEST['id']) . ']';
        $values["itemamount1"] = $price;
        $values["itemtaxed1"] = 1;
        if (!empty($setupfee) && $setupfee != '0' && $setupfee != '0.00') {
            $values["itemdescription2"] = $LANG['invsetupfee'];
            $values["itemamount2"] = $setupfee;
            $values["itemtaxed2"] = 0;
        }
        $results = localAPI('createinvoice', $values, '');
        $invoiceid = $results['invoiceid'];
        header("Location: viewinvoice.php?id=" . $invoiceid);
        exit;
    }
}

$output .= '<p>';

$query = mysql_query("SELECT * FROM `mod_servermonitoring_smspackages`");
while ($result = mysql_fetch_assoc($query)) {
    $pricing = unserialize($result['pricing']);
    $price = $pricing['price'][$clientcurrency];
    $setupfee = $pricing['setupfee'][$clientcurrency];
    $setupfee = formatCurrency($setupfee, $clientcurrency);
    $price = formatCurrency($price, $clientcurrency);
    if (!empty($setupfee) && $setupfee != '0' && $setupfee != '0.00') {
        $setupfee = '<br><font color="red">+ ' . $setupfee . ' [' . $LANG['setupfee'] . ']</font>';
    } else
        $setupfee = '';
    $output .= '<div><pre><pre><center><strong>' . $result['credits'] . ' ' . $LANG['smscredits'] . '</strong></center></pre><p align="center"><strong>' . $price . '</strong>' . $setupfee . '</p><p align="center"><a href="index.php?m=servermonitoring&id=' . $_REQUEST['id'] . '&smscredits=order&pid=' . $result['id'] . '"><button class="btn btn-success">' . $LANG['order'] . '</button></p></pre></div>';
}

$output .= '</p>';
$output .= '<p align="center"><a href="index.php?m=servermonitoring&id=' . $_REQUEST['id'] . '&smssettings=true"><button class="btn btn-danger" type="button">' . $LANG['goback'] . '</button></a></p>';
