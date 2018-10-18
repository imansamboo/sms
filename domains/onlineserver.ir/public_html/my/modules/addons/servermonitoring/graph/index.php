<?php
require_once('../../../../init.php');
require_once('../includes/functions.inc.php');
require_once('../includes/phplot/phplot.php');

if(file_exists(ROOTDIR."/modules/addons/servermonitoring/lang/".$CONFIG['Language'].".php")) {
	include(ROOTDIR."/modules/addons/servermonitoring/lang/".$CONFIG['Language'].".php");
} elseif(file_exists(ROOTDIR."/modules/addons/servermonitoring/lang/english.php")) {
	include(ROOTDIR."/modules/addons/servermonitoring/lang/english.php");
}

$statdata = $_REQUEST['data'];
$statdata = base64_decode($statdata);
$statdata = unserialize($statdata);

# Define the data array: Label, the 3 data sets.
# Year,  Features, Bugs, Happy Users:

$getGraph = $_REQUEST['getGraph'];

$p = new PHPlot(250, 200); // GRAPH SIZE
$p->SetDefaultTTFont('../includes/fonts/arial.ttf'); // FONT

// START

    $montharr = array("jan" => "01", "feb" => "02", "mar" => "03", "apr" => "04", "may" => "05", "jun" => "06", "jul" => "07", "aug" => "08", "sep" => "09", "oct" => "10", "nov" => "11", "dec" => "12");

    $month = array();
    foreach ($statdata AS $key => $value) {
        $sts = explode(',',$key);
        if (strtolower($sts[1]) != date('Y')) continue;
        $sts = strtolower($sts[0]);
        $sts = $montharr[$sts];
        if (!isset($month[$sts])) $month[$sts] = 0;
        $month[$sts] = $month[$sts]+($value/60);
    }

    $data = array(
      array('J', $month['01']),    array('F', $month['02']),    array('M', $month['03']),
      array('A', $month['04']),   array('M', $month['05']),   array('J', $month['06']),
      array('J', $month['07']),   array('A', $month['08']),  array('S', $month['09']),
      array('O', $month['10']),  array('N', $month['11']),  array('D', $month['12']),
    );
    $p->SetTitle($_ADDONLANG['totaldowntime']); // TITLE
    $p->SetYTitle($_ADDONLANG['minutes']);
    $p->SetPlotType('bars'); // Plot Type

    $p->SetPlotAreaWorld(NULL, 0, NULL, NULL);
    //$p->SetYTickIncrement(10000);

    if ($total > 10) $totalY = 7; else $totalY = $total;
    $p->SetNumYTicks($totalY);
    $p->SetYLabelType('data', 0);

// END

$p->SetDataType('text-data');
$p->SetDataValues($data);

$p->SetBackgroundColor('#ffffff');
$p->SetDrawPlotAreaBackground(True);
$p->SetPlotBgColor('#ffffff');

# Draw lines on all 4 sides of the plot:
//$p->SetPlotBorderType('full');

$p->SetXDataLabelPos('plotdown');
$p->SetXTickPos('none');

# Generate and output the graph now:
$p->DrawGraph();