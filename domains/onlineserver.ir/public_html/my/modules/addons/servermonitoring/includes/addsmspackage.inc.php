<?php
if (!defined("WHMCS")) die("This file cannot be accessed directly");

if (isset($_REQUEST['save']) && $_REQUEST['save']) {
	unset($_POST['token']);
	foreach ($_POST['setupfee'] AS $key => $value) {
		$_POST['setupfee'][$key] = number_format($value,2);
	}
	foreach ($_POST['price'] AS $key => $value) {
		$_POST['price'][$key] = number_format($value,2);
	}
	$credits = $_POST['credits'];
	$description = $_POST['description'];
	unset($_POST['description']);
	unset($_POST['credits']);
	$pricing = serialize($_POST);
	$query = mysql_query("INSERT INTO `mod_servermonitoring_smspackages` SET `credits`='".mysql_real_escape_string($credits)."', `description`='".mysql_real_escape_string($description)."', `pricing`='".mysql_real_escape_string($pricing)."'");
	redir('module=servermonitoring&a=smspackages&success=true');
}

echo '<form action="addonmodules.php?module=servermonitoring&a=smspackages&add=true&save=true" method="post">';

echo '<pre><center><div style="width:40%">';
echo '<span>'.$LANG['smscredits'].'</span><p><input type="text" name="credits" value="'.$package['credits'].'" class="form-control"></p>';
echo '<span>'.$LANG['description'].'</span><p><input type="text" name="description" value="'.$package['description'].'" class="form-control"></p>';
echo '</div></center></pre>';

echo '<table id="pricingtbl" class="table table-condensed">
                <tr bgcolor="#efefef" style="text-align:center;font-weight:bold">
                    <td>'.$LANG['currency'].'</td>
                    <td></td>
                    <td>'.$LANG['price'].'</td>
                </tr>';	

$query = mysql_query("SELECT * FROM `tblcurrencies`");
while ($currency = mysql_fetch_assoc($query)) {

echo '<tr bgcolor="#ffffff" style="text-align:center">
			<td rowspan="2" bgcolor="#efefef"><b>'.strtoupper($currency['code']).'</b></td>
			<td>'.$LANG['setupfee'].'</td>
			<td><input type="text" name="setupfee['.$currency['id'].']" value="" class="form-control input-inline input-100 text-center" /></td>
		</tr>
		<tr bgcolor="#ffffff" style="text-align:center">
			<td>'.$LANG['price'].'</td>
			<td><input type="text" name="price['.$currency['id'].']" size="10" value="" class="form-control input-inline input-100 text-center" /></td>
		</tr>';
}

echo '</table>';
echo '<center><button type="submit" class="btn btn-success">'.$LANG['savechanges'].'</button></center></form>';