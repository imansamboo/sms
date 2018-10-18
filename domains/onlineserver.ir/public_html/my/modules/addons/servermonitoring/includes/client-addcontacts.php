<?php

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

use Illuminate\Database\Capsule\Manager as Capsule;

global $CONFIG;
if (isset($_REQUEST['save']) && count($_POST) > 3) {
    Capsule::table('mod_servermonitoring_contacts')->insert([
        'name' => $_REQUEST['name'],
        'family' => $_REQUEST['family'],
        'email' => $_REQUEST['email'],
        'mid' => $_REQUEST['mid'],
        'phonenumber' => $_REQUEST['phonenumber'],
    ]);
    redir('m=servermonitoring&id=' . $_REQUEST['id'] . '&mid=' . $_REQUEST['mid'] . '&contacts=true');
    exit;
}
$error = '';
$output .= '<center><div style="width:50%"><form  enctype="multipart/form-data" action="index.php?m=servermonitoring&id=' . $_REQUEST['id'] . '&mid=' . $_REQUEST['mid'] . '&addcontacts=true&save=true" method="post">';
$output .= '<span><strong>' . $LANG['contactnam'] . '</strong></span><p><input type="text" name="name" value="" class="form-control" placeholder="' . $LANG['contactnam'] . '"></p>';
$output .= '<span><strong>' . $LANG['contactfam'] . '</strong></span><p><input type="text" name="family" value="" class="form-control" placeholder="' . $LANG['contactfam'] . '"></p>';
$output .= '<span><strong>' . $LANG['contactemail'] . '</strong></span><p><input type="text" name="email" value="" class="form-control" placeholder="' . $LANG['contactemail'] . '"></p>';
$output .= '<span><strong>' . $LANG['contactp'] . '</strong></span><p><input type="text" name="phonenumber" value="" class="form-control" placeholder="' . $LANG['contactpp'] . '"></p>';

$output .= '<p><button class="btn btn-success" type="submit">' . $LANG['savechanges'] . '</button>&nbsp;&nbsp;<a href="index.php?m=servermonitoring&id=' . $_REQUEST['id'] . '&mid=' . $_REQUEST['mid'] . '&contacts=true"><button class="btn btn-danger" type="button">' . $LANG['goback'] . '</button></a></p></form></div></center>';
