<?php
/* WHMCS SMS Addon with GNU/GPL Licence
 * FaraPayamak - http://www.farapayamak.ir
 * */
if (!defined("WHMCS"))
	die("&#1575;&#1605;&#1705;&#1575;&#1606; &#1583;&#1587;&#1578;&#1585;&#1587;&#1740; &#1605;&#1587;&#1578;&#1602;&#1740;&#1605; &#1576;&#1607; &#1575;&#1740;&#1606; &#1601;&#1575;&#1740;&#1604; &#1608;&#1580;&#1608;&#1583; &#1606;&#1583;&#1575;&#1585;&#1583;");

function aktuel_sms_config() {
    $configarray = array(
        "name" => "FaraPayamak",
        "description" => "ماژول اختصاصی فراپیامک",
        "version" => "1.1.8",
        "author" => "FaraPayamak",
		"language" => "persian",
    );
    return $configarray;
}

function aktuel_sms_activate() {

    $query = "CREATE TABLE IF NOT EXISTS `mod_aktuelsms_messages` (`id` int(11) NOT NULL AUTO_INCREMENT,`sender` varchar(40) NOT NULL,`to` varchar(15) DEFAULT NULL,`text` text,`msgid` varchar(50) DEFAULT NULL,`status` varchar(10) DEFAULT NULL,`errors` text,`logs` text,`user` int(11) DEFAULT NULL,`datetime` datetime NOT NULL,PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
	mysql_query($query);

    $query = "CREATE TABLE IF NOT EXISTS `mod_aktuelsms_settings` (`id` int(11) NOT NULL AUTO_INCREMENT,`api` varchar(40) CHARACTER SET utf8 NOT NULL,`apiparams` varchar(500) CHARACTER SET utf8 NOT NULL,`wantsmsfield` int(11) DEFAULT NULL,`gsmnumberfield` int(11) DEFAULT NULL,`dateformat` varchar(12) CHARACTER SET utf8 DEFAULT NULL,`version` varchar(6) CHARACTER SET utf8 DEFAULT NULL,PRIMARY KEY (`id`)) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
	mysql_query($query);

    $query = "INSERT INTO `mod_aktuelsms_settings` (`api`, `apiparams`, `wantsmsfield`, `gsmnumberfield`,`dateformat`, `version`) VALUES ('', '', 0, 0,'%d.%m.%y','1.1.3');";
	mysql_query($query);

    $query = "CREATE TABLE IF NOT EXISTS `mod_aktuelsms_templates` (`id` int(11) NOT NULL AUTO_INCREMENT,`name` varchar(50) CHARACTER SET utf8 NOT NULL,`type` enum('client','admin') CHARACTER SET utf8 NOT NULL,`admingsm` varchar(255) CHARACTER SET utf8 NOT NULL,`template` varchar(240) CHARACTER SET utf8 NOT NULL,`variables` varchar(500) CHARACTER SET utf8 NOT NULL,`active` tinyint(1) NOT NULL,`extra` varchar(3) CHARACTER SET utf8 NOT NULL,`description` text CHARACTER SET utf8,PRIMARY KEY (`id`)) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
	mysql_query($query);

    //Creating hooks
	require_once("smsclass.php");
    $class = new AktuelSms();
    $class->checkHooks();

    return array('status'=>'success','description'=>'ماژول ارسال پیامک فراپیامک با موفقیت فعال شد.');
}

function aktuel_sms_deactivate() {

    $query = "DROP TABLE `mod_aktuelsms_templates`";
	mysql_query($query);
    $query = "DROP TABLE `mod_aktuelsms_settings`";
    mysql_query($query);
    $query = "DROP TABLE `mod_aktuelsms_messages`";
    mysql_query($query);

    return array('status'=>'success','description'=>'ماژول با موفقیت غیر فعال شد.');
}

function aktuel_sms_upgrade($vars) {
    $version = $vars['version'];

    switch($version){
        case "1":
        case "1.0.1":
            $sql = "ALTER TABLE `mod_aktuelsms_messages` ADD `errors` TEXT NULL AFTER `status` ;ALTER TABLE `mod_aktuelsms_templates` ADD `description` TEXT NULL ;ALTER TABLE `mod_aktuelsms_messages` ADD `logs` TEXT NULL AFTER `errors` ;";
            mysql_query($sql);
        case "1.1":
            $sql = "ALTER TABLE `mod_aktuelsms_settings` CHANGE `apiparams` `apiparams` VARCHAR( 500 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ;";
            mysql_query($sql);
        case "1.1.1":
        case "1.1.2":
            $sql = "ALTER TABLE `mod_aktuelsms_settings` ADD `dateformat` VARCHAR(12) NULL AFTER `gsmnumberfield`;UPDATE `mod_aktuelsms_settings` SET dateformat = '%d.%m.%y';";
            mysql_query($sql);
        case "1.1.3":
        case "1.1.4":
            $sql = "ALTER TABLE `mod_aktuelsms_templates` CHANGE `name` `name` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,CHANGE `type` `type` ENUM( 'client', 'admin' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,CHANGE `admingsm` `admingsm` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,CHANGE `template` `template` VARCHAR( 240 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,CHANGE `variables` `variables` VARCHAR( 500 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,CHANGE `extra` `extra` VARCHAR( 3 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,CHANGE `description` `description` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ;";
            mysql_query($sql);
            $sql = "ALTER TABLE `mod_aktuelsms_settings` CHANGE `api` `api` VARCHAR( 40 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,CHANGE `apiparams` `apiparams` VARCHAR( 500 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,CHANGE `dateformat` `dateformat` VARCHAR( 12 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,CHANGE `version` `version` VARCHAR( 6 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ;";
            mysql_query($sql);
            $sql = "ALTER TABLE `mod_aktuelsms_messages` CHANGE `sender` `sender` VARCHAR( 40 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,CHANGE `to` `to` VARCHAR( 15 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,CHANGE `text` `text` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,CHANGE `msgid` `msgid` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,CHANGE `status` `status` VARCHAR( 10 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,CHANGE `errors` `errors` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,CHANGE `logs` `logs` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ;";
            mysql_query($sql);

            $sql = "ALTER TABLE `mod_aktuelsms_templates` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";
            mysql_query($sql);
            $sql = "ALTER TABLE `mod_aktuelsms_settings` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";
            mysql_query($sql);
            $sql = "ALTER TABLE `mod_aktuelsms_messages` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";
            mysql_query($sql);
        case "1.1.5":
        case "1.1.6":
        case "1.1.7":
            break;

    }

    $class = new AktuelSms();
    $class->checkHooks();
}

function aktuel_sms_output($vars){
	$modulelink = $vars['modulelink'];
	$version = $vars['version'];
	$LANG = $vars['_lang'];
	putenv("TZ=Europe/Istanbul");

    $class = new AktuelSms();

    $tab = $_GET['tab'];
    echo '
    <div id="clienttabs" style="float: right">


    
<html dir="rtl">

        <ul>
                    <li class="' . (($tab == "support")?"tabselected":"tab") . '"><a href="addonmodules.php?module=aktuel_sms&amp;tab=support">'.$LANG['support'].'</a></li>

            <li class="' . (($tab == "messages")?"tabselected":"tab") . '"><a href="addonmodules.php?module=aktuel_sms&amp;tab=messages">'.$LANG['messages'].'</a></li>
            <li class="' . (($tab == "sendbulk")?"tabselected":"tab") . '"><a href="addonmodules.php?module=aktuel_sms&tab=sendbulk">'.$LANG['sendsms'].'</a></li>
            <li class="' . ((@$_GET['type'] == "client")?"tabselected":"tab") . '"><a href="addonmodules.php?module=aktuel_sms&tab=templates&type=client">'.$LANG['clientsmstemplates'].'</a></li>
            <li class="' . ((@$_GET['type'] == "admin")?"tabselected":"tab") . '"><a href="addonmodules.php?module=aktuel_sms&tab=templates&type=admin">'.$LANG['adminsmstemplates'].'</a></li>
            <li class="' . (($tab == "settings")?"tabselected":"tab") . '"><a href="addonmodules.php?module=aktuel_sms&tab=settings">'.$LANG['settings'].'</a></li>
        </ul>
    </div>
    ';
    if (!isset($tab) || $tab == "settings")
    {
        /* UPDATE SETTINGS */
        if ($_POST['params']) {
            $update = array(
                "api" => $_POST['api'],
                "apiparams" => json_encode($_POST['params']),
                'wantsmsfield' => $_POST['wantsmsfield'],
                'gsmnumberfield' => $_POST['gsmnumberfield'],
                'dateformat' => $_POST['dateformat']
            );
            update_query("mod_aktuelsms_settings", $update, "");
        }
        /* UPDATE SETTINGS */

        $settings = $class->getSettings();
        $apiparams = json_decode($settings['apiparams']);

        /* CUSTOM FIELDS START */
        $where = array(
            "fieldtype" => array("sqltype" => "LIKE", "value" => "tickbox"),
            "showorder" => array("sqltype" => "LIKE", "value" => "on")
        );
        $result = select_query("tblcustomfields", "id,fieldname", $where);
        $wantsms = '';
        while ($data = mysql_fetch_array($result)) {
            if ($data['id'] == $settings['wantsmsfield']) {
                $selected = 'selected="selected"';
            } else {
                $selected = "";
            }
            $wantsms .= '<option  style="float: right"  value="' . $data['id'] . '" ' . $selected . '>' . $data['fieldname'] . '</option>';
        }

        $where = array(
            "fieldtype" => array("sqltype" => "LIKE", "value" => "text"),
            "showorder" => array("sqltype" => "LIKE", "value" => "on")
        );
        $result = select_query("tblcustomfields", "id,fieldname", $where);
        $gsmnumber = '';
        while ($data = mysql_fetch_array($result)) {
            if ($data['id'] == $settings['gsmnumberfield']) {
                $selected = 'selected="selected"';
            } else {
                $selected = "";
            }
            $gsmnumber .= '<option  style="float: right" value="' . $data['id'] . '" ' . $selected . '>' . $data['fieldname'] . '</option>';
        }
        /* CUSTOM FIELDS FINISH HIM */

        $classers = $class->getSenders();
        $classersoption = '';
        $classersfields = '';
        foreach($classers as $classer){
            $classersoption .= '<option  style="float: right" value="'.$classer['value'].'" ' . (($settings['api'] == $classer['value'])?"selected=\"selected\"":"") . '>'.$classer['label'].'</option>';
            if($settings['api'] == $classer['value']){
                foreach($classer['fields'] as $field){
                    $classersfields .=
                        '<tr>
                            <td class="fieldlabel" width="30%">'.$LANG[$field].'</td>
                            <td class="fieldarea"><input style="float: right" type="text" name="params['.$field.']" size="40" value="' . $apiparams->$field . '"></td>
                        </tr>';
                }
            }
        }

        echo '
        <script type="text/javascript">
            $(document).ready(function(){
                $("#api").change(function(){
                    $("#form").submit();
                });
            });
        </script>
     <html dir="rtl">
                <form action="" method="post" id="form">
        <input type="hidden" name="action" value="save" />
            <div style="text-align: left;background-color: whiteSmoke;margin: 0px;padding: 10px;">
			 <p align="right">
                <table class="form" width="65%" border="0" cellspacing="2" cellpadding="3">
                    <tbody>
                        <tr>
                            <td class="fieldlabel" width="40%" align="center">'.$LANG['sender'].'</td>
                            <td class="fieldarea" align="center">
                                <select style="float: right" name="api" id="api">
                                    '.$classersoption.'
                                </select>
                            </td>
                        </tr>
                        <tr>
						 <p align="right">
                            <td class="fieldlabel" width="40%" align="center">'.$LANG['senderid'].'</td>
                            <td class="fieldarea" align="center">
							<p align="center"><input type="text" name="params[senderid]" size="40" value="' . $apiparams->senderid . '" style="float: right"> 
							</p>
							<p dir="ltr" style="float: right"  align="center">&#1576;&#1607; &#1593;&#1606;&#1608;&#1575;&#1606; &#1605;&#1579;&#1575;&#1604; : 10002013</td>
                        </tr>
                        '.$classersfields.'
                        <tr>
                            <td class="fieldlabel" style="float: right" width="40%" align="center">'.$LANG['signature'].'</td>
                            <td class="fieldarea" align="center">
							<p align="center">
							<input name="params[signature]" size="40" value="' . $apiparams->signature . '" style="float: right"></p>
						<p dir="ltr" style="float: right"  align="center">&#1576;&#1607; 
						&#1593;&#1606;&#1608;&#1575;&#1606; &#1605;&#1579;&#1575;&#1604; :<span lang="fa"> &#1601;&#1585;&#1575;&#1662;&#1740;&#1575;&#1605;&#1705;</span></td>
                        </tr>
                        <tr>
                            <td class="fieldlabel" width="40%" align="center">'.$LANG['wantsmsfield'].'</td>
                            <td class="fieldarea" align="center">
                                <select name="wantsmsfield" style="float: right">
                                    ' . $wantsms . '
                                </select>
                            </td>
                        </tr>

                        <tr>
                            <td class="fieldlabel" width="40%" align="center">'.$LANG['gsmnumberfield'].'</td>
                            <td class="fieldarea" align="center">
                                <select name="gsmnumberfield" style="float: right">
                                    ' . $gsmnumber . '
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="fieldlabel" width="40%" align="center">'.$LANG['dateformat'].'</td>
                            <td class="fieldarea" align="center"><input style="float: right" type="text" name="dateformat" size="40" value="' . $settings['dateformat'] . '"> 
<p dir="ltr" style="float: right"  align="center"> e.g:  %d.%m.%y (27.01.2014)</span></td>
                        </tr>                        </tr>
                    </tbody>
                </table>
            </div>
            <p align="center"><input type="submit" value="'.$LANG['save'].'" class="button" /></p>
        </form>
        ';
    }
    elseif ($tab == "templates")
    {
        if ($_POST['submit']) {
            $where = array("type" => array("sqltype" => "LIKE", "value" => $_GET['type']));
            $result = select_query("mod_aktuelsms_templates", "*", $where);
            while ($data = mysql_fetch_array($result)) {
               if ($_POST[$data['id'] . '_active'] == "on") {
                    $tmp_active = 1;
                } else {
                    $tmp_active = 0;
                }
                $update = array(
                    "template" => $_POST[$data['id'] . '_template'],
                    "active" => $tmp_active
                );

                if(isset($_POST[$data['id'] . '_extra'])){
                    $update['extra']= trim($_POST[$data['id'] . '_extra']);
                }
                if(isset($_POST[$data['id'] . '_admingsm'])){
                    $update['admingsm']= $_POST[$data['id'] . '_admingsm'];
                    $update['admingsm'] = str_replace(" ","",$update['admingsm']);
                }
                update_query("mod_aktuelsms_templates", $update, "id = " . $data['id']);
            }
        }
            if($_GET['type'] == "admin")

{

                echo '
         <div style="text-align: left;background-color: whiteSmoke;margin: 0px;padding: 10px;">
	<p dir="rtl" style="text-align: right"><span lang="fa">راهنما&#1740;&#1740; :</span></p>
	<p dir="rtl" style="text-align: right"><span lang="fa">در ا&#1740;ن بخش ،در صورت 
	تما&#1740;ل به فعالساز&#1740; ارسال پ&#1740;امک به مد&#1740;ر ابتدا &#1740;کبار ت&#1740;ک &quot;فعال باشد&quot; را غ&#1740;ر 
	فعال و سپس فعال نما&#1740;&#1740;د تا در د&#1740;تاب&#1740;س ا&#1740;ن عمل ذخ&#1740;ره و ماژول فعال گردد در غ&#1740;ر 
	ا&#1740;نصورت پ&#1740;امک&#1740; برا&#1740; مد&#1740;ر ارسال نخواهد شد.</span>
                ';

            }
        echo '<html dir="rtl">

<form action="" method="post">
        <input type="hidden" name="action" value="save" />
            <div style="text-align: left;background-color: whiteSmoke;margin: 0px;padding: 10px;">
                <table class="form" width="67%" border="0" cellspacing="2" cellpadding="3">
                    <tbody>';
        $where = array("type" => array("sqltype" => "LIKE", "value" => $_GET['type']));
        $result = select_query("mod_aktuelsms_templates", "*", $where);

        while ($data = mysql_fetch_array($result)) {
            if ($data['active'] == 1) {
                $active = 'checked = "checked"';
            } else {
                $active = '';
            }
            $desc = json_decode($data['description']);
            if(isset($desc->$LANG['lang'])){
                $name = $desc->$LANG['lang'];
            }else{
                $name = $data['name'];
            }
            echo '
                <tr>
                    <td class="fieldlabel" width="45%" style="float: right">' . $name . '</td>
                    <td class="fieldarea" width="53%">
                        <textarea cols="50" name="' . $data['id'] . '_template" style="float: right">' . $data['template'] . '</textarea>
                    </td>
                </tr>';
            echo '
            <tr>
                <td class="fieldlabel" width="45%" style="float: right">'.$LANG['active'].'</td>
 <td width="53%"><input style="float: right" type="checkbox" value="on" name="' . $data['id'] . '_active" ' . $active . '></td>            </tr>
            ';
            echo '
            <tr>
                <td class="fieldlabel" width="45%" style="float: right">'.$LANG['parameter'].'</td>
                <p dir="ltr" style="float: right"  align="center">
				<td width="53%">' . $data['variables'] . '</td>
            </tr>
            ';

            if(!empty($data['extra'])){
                echo '
                <tr>
                    <td class="fieldlabel" width="45%" style="float: right">'.$LANG['ekstra'].'</td>
                    <td class="fieldarea" width="53%">
                        <input style="float: right" type="text" name="'.$data['id'].'_extra" value="'.$data['extra'].'">
                    </td>
                </tr>
                ';
            }

            if($_GET['type'] == "admin")

{

                echo '
                <tr>
                    <td class="fieldlabel" width="45%" style="float: right">'.$LANG['admingsm'].'</td>
                    <td class="fieldarea" width="53%">
                    <p dir="ltr" style="float: right"  align="center">
                        <input style="float: right" type="text" name="'.$data['id'].'_admingsm" value="'.$data['admingsm'].'">
                        '.$LANG['admingsmornek'].'
                    </td>
                </tr>
                ';

            }

            echo '<tr>
                <td colspan="2"><hr></td>
            </tr>';
        }
        echo '
        </tbody>
                </table>
            </div>
            <p align="center"><input type="submit" name="submit" value="ذخیره" class="button" /></p>
        </form>';

    }
    elseif ($tab == "messages")
    {
        if(!empty($_GET['deletesms'])){
            $smsid = (int) $_GET['deletesms'];
            $sql = "DELETE FROM mod_aktuelsms_messages WHERE id = '$smsid'";
            mysql_query($sql);
        }
        echo '<div style="text-align: left;background-color: whiteSmoke;margin: 0px;padding: 10px;">
	<p dir="rtl" style="text-align: right"><span lang="fa">&nbsp;&#1583;&#1585; &#1575;&#1740;&#1606; &#1602;&#1587;&#1605;&#1578; 
	&#1662;&#1740;&#1575;&#1605;&#1705; &#1607;&#1575;&#1740; &#1575;&#1585;&#1587;&#1575;&#1604;&#1740; &#1705;&#1607; &#1575;&#1586; &#1587;&#1608;&#1740; &#1587;&#1740;&#1587;&#1578;&#1605; &#1576;&#1585;&#1575;&#1740; &#1705;&#1575;&#1585;&#1576;&#1585; &#1575;&#1585;&#1587;&#1575;&#1604; &#1605;&#1740; &#1588;&#1608;&#1583;&#1548;&#1602;&#1575;&#1576;&#1604; &#1605;&#1588;&#1575;&#1607;&#1583;&#1607; &#1582;&#1608;&#1575;&#1607;&#1583; 
	&#1576;&#1608;&#1583;</span></p>';
        echo  '
        <!--<script src="http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/jquery.dataTables.min.js"></script>
        <link rel="stylesheet" href="http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/css/jquery.dataTables.css" type="text/css">
        <link rel="stylesheet" href="http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/css/jquery.dataTables_themeroller.css" type="text/css">
        <script type="text/javascript">
            $(document).ready(function(){
                $(".datatable").dataTable();
            });
        </script>-->

        <div style="text-align: left;background-color: whiteSmoke;margin: 0px;padding: 10px;">
        <table class="datatable" border="0" cellspacing="1" cellpadding="3">
        <thead>
            <tr>
                <th>#</th>
                <th>'.$LANG['client'].'</th>
                <th>'.$LANG['gsmnumber'].'</th>
                <th>'.$LANG['message'].'</th>
                <th>'.$LANG['datetime'].'</th>
                <th width="20"></th>
            </tr>
        </thead>
        <tbody>
        ';

        /* Getting messages order by date desc */
        $sql = "SELECT `m`.*,`user`.`firstname`,`user`.`lastname`
        FROM `mod_aktuelsms_messages` as `m`
        JOIN `tblclients` as `user` ON `m`.`user` = `user`.`id`
        ORDER BY `m`.`datetime` DESC";
        $result = mysql_query($sql);
        $i = 0;
        while ($data = mysql_fetch_array($result)) {
            if($data['msgid'] && $data['status'] == ""){
                $status = $class->getReport($data['msgid']);
                mysql_query("UPDATE mod_aktuelsms_messages SET status = '$status' WHERE id = ".$data['id']."");
            }else{
                $status = $data['status'];
            }

            $i++;
            echo  '<tr>
            <td>'.$i.'</td>
            <td><a href="clientssummary.php?userid='.$data['user'].'">'.$data['firstname'].' '.$data['lastname'].'</a></td>
            <td>'.$data['to'].'</td>
            <td>'.$data['text'].'</td>
            <td>'.$data['datetime'].'</td>
            <td><a href="addonmodules.php?module=aktuel_sms&tab=messages&deletesms='.$data['id'].'" title="'.$LANG['delete'].'"><img src="images/delete.gif" width="16" height="16" border="0" alt="Delete"></a></td></tr>';
        }
        /* Getting messages order by date desc */

        echo '
        </tbody>
        </table>
        </div>
        ';

    }
    elseif($tab=="sendbulk")
    {
        $settings = $class->getSettings();

        if(!empty($_POST['client'])){
            $userinf = explode("_",$_POST['client']);
            $userid = $userinf[0];
            $gsmnumber = $userinf[1];

            $class->setGsmnumber($gsmnumber);
            $class->setMessage($_POST['message']);
            $class->setUserid($userid);

            $result = $class->send();
            if($result == false){
                echo $class->getErrors();
            }else{
                echo $LANG['smssent'].' '.$gsmnumber;
            }

            if($_POST["debug"] == "ON"){
                $debug = 1;
            }
        }

        $userSql = "SELECT `a`.`id`,`a`.`firstname`, `a`.`lastname`, `b`.`value` as `gsmnumber`
        FROM `tblclients` as `a`
        JOIN `tblcustomfieldsvalues` as `b` ON `b`.`relid` = `a`.`id`
        JOIN `tblcustomfieldsvalues` as `c` ON `c`.`relid` = `a`.`id`
        WHERE `b`.`fieldid` = '".$settings['gsmnumberfield']."'
        AND `c`.`fieldid` = '".$settings['wantsmsfield']."'
        AND `c`.`value` = 'on' order by `a`.`firstname`";
        $clients = '';
        $result = mysql_query($userSql);
        while ($data = mysql_fetch_array($result)) {
            $clients .= '<option value="'.$data['id'].'_'.$data['gsmnumber'].'">'.$data['firstname'].' '.$data['lastname'].' (#'.$data['id'].')</option>';
        }
        echo '
        <script>
        jQuery.fn.filterByText = function(textbox, selectSingleMatch) {
          return this.each(function() {
            var select = this;
            var options = [];
            $(select).find("option").each(function() {
              options.push({value: $(this).val(), text: $(this).text()});
            });
            $(select).data("options", options);
            $(textbox).bind("change keyup", function() {
              var options = $(select).empty().scrollTop(0).data("options");
              var search = $.trim($(this).val());
              var regex = new RegExp(search,"gi");

              $.each(options, function(i) {
                var option = options[i];
                if(option.text.match(regex) !== null) {
                  $(select).append(
                     $("<option>").text(option.text).val(option.value)
                  );
                }
              });
              if (selectSingleMatch === true && 
                  $(select).children().length === 1) {
                $(select).children().get(0).selected = true;
              }
            });
          });
        };
        $(function() {
          $("#clientdrop").filterByText($("#textbox"), true);
        });  
        </script>';
           echo '<center>
<form method="post" dir="rtl">
        <input type="hidden" name="action" value="save" />
            <div style="text-align: left;background-color: whiteSmoke;margin: 0px;padding: 10px;">
                <table class="form" width="55%" border="0" cellspacing="2" cellpadding="3">
                    <tbody><center>

                        <tr>
                            <td class="fieldlabel" width="27%" align="center">'.$LANG['client'].'</td>
                            <td class="fieldarea" align="center">
                                <input dir="rtl" id="textbox" type="text" placeholder="&#1580;&#1587;&#1578;&#1580;&#1608;..." style="width:498px;padding:5px"><br>
                                <select dir="rtl" name="client" multiple id="clientdrop" style="width:512px;padding:5px">
                                    <option value="">'.$LANG['selectclient'].'</option>
                                    ' . $clients . '
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="fieldlabel" dir="rtl" width="27%" align="center">'.$LANG['mesaj'].'</td>
                            <td dir="rtl" class="fieldarea" align="center">
                               <textarea cols="70" rows="20" name="message" style="width:498px;padding:5px" dir="rtl"></textarea>
                            </td>
                        </tr>
                     
                    </tbody>
                </table>
            </div>
            <p align="center"><input type="submit" value="'.$LANG['send'].'" class="button" /></p>
        </form>';

        if(isset($debug)){
            echo $class->getLogs();
        }
    }
    elseif($tab == "support"){
        echo '<div style="text-align: left;background-color: whiteSmoke;margin: 0px;padding: 10px;">
	<p dir="rtl" style="text-align: right"><span lang="fa">&nbsp;در صورت ن&#1740;از به 
	هرگونه پ&#1740;شت&#1740;بان&#1740; در&nbsp; رابطه با ا&#1740;ن ماژول م&#1740; توان&#1740;د با شماره 02124814 بخش 
	پشت&#1740;بان&#1740; تماس حاصل نما&#1740;&#1740;د و &#1740;ا از داخل سامانه بخش پشت&#1740;بان&#1740; (ت&#1740;کت) ، درخواست 
	جد&#1740;د ارسال نما&#1740;&#1740;د.</span></p>
	<p dir="rtl" style="text-align: right"><span lang="fa">راهنما&#1740;&#1740; :</span></p>
	<p dir="rtl" style="text-align: right"><span lang="fa">در بخش تنظ&#1740;م متن 
	پ&#1740;امک مد&#1740;ر،در صورت تما&#1740;ل به فعالساز&#1740; ابتدا &#1740;کبار ت&#1740;ک &quot;فعال باشد&quot; را غ&#1740;ر 
	فعال و سپس فعال نما&#1740;&#1740;د تا در د&#1740;تاب&#1740;س ا&#1740;ن عمل ذخ&#1740;ره و ماژول فعال گردد در غ&#1740;ر 
	ا&#1740;نصورت پ&#1740;امک&#1740; برا&#1740; مد&#1740;ر ارسال نخواهد شد.</span></p>
	';
        if($version != $currentversion){
            echo $LANG['newversion'];
        }else{
            echo $LANG['support'].'<br><br>';
        }
        echo '</div>';
    }

    $credit =  $class->getBalance();
    if($credit){
        echo '
            <div style="text-align: left;background-color: whiteSmoke;margin: 0px;padding: 10px;">
            <b>'.$LANG['credit'].':</b> '.$credit.'
            </div>';
    }

echo '<div style="text-align: left;background-color: whiteSmoke;margin: 0px;padding: 10px;">
	<p dir="rtl" style="text-align: right"><span lang="fa">تمام&#1740; حقوق ا&#1740;ن ماژول 
	متعلق به <b><a target="_blank" href="http://farapayamak.ir">فراپ&#1740;امک</a></b> 
	م&#1740; باشد</span>';

}