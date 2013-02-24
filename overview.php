<?php
require 'includes/header.php';
require_once('highroller/HighRollerSeriesData.php');
require_once('highroller/HighRollerLineChart.php');

$overview = array();

// Get transactions and journal
$journal = $pheal->WalletJournal(array('characterID' => $apiKeys['charId'], 'rowCount' => '2560'))->toArray()['result']['transactions'];
$transactions = $pheal->WalletTransactions(array('characterID' => $apiKeys['charId'], 'rowCount' => '2560'))->toArray()['result']['transactions'];

foreach ($journal as $jr) {
    if ($jr['refTypeID'] == ('54' || '46')) {
        $date = DateTime::createFromFormat('Y-m-d H:i:s', $jr['date'])->format('Y-m-d');
        if (!array_key_exists($date, $overview)) {
            $overview[$date] = array();
        }
        if (!array_key_exists('expenditure', $overview[$date])) {
            $overview[$date]['expenditure'] = 0;
        }
        if (!array_key_exists('profit', $overview[$date])) {
            $overview[$date]['profit'] = 0;
        }
        $overview[$date]['expenditure'] = (double)substr($jr['amount'], 1) + $overview[$date]['expenditure'];
        $overview[$date]['profit'] += (double)$jr['amount'];
    }
}
foreach ($transactions as $trn) {
    $date = DateTime::createFromFormat('Y-m-d H:i:s', $trn['transactionDateTime'])->format('Y-m-d');
    if (!array_key_exists($date, $overview)) {
        $overview[$date] = array();
    }
    if (!array_key_exists('expenditure', $overview[$date])) {
            $overview[$date]['expenditure'] = 0 ;
    }
    if (!array_key_exists('profit', $overview[$date])) {
            $overview[$date]['profit'] = 0;
    }
    if (!array_key_exists('revenue', $overview[$date])) {
            $overview[$date]['revenue'] = 0;
    }
    if ($trn['transactionType'] == 'buy') {
        $price = '-' . $trn['price'];
        $overview[$date]['expenditure'] += (double)substr($price, 1);
        $overview[$date]['profit'] += (double)$price;
    } else {
    $overview[$date]['profit'] += (double)$trn['price'];
    $overview[$date]['revenue'] += (double)$trn['price'];
    }
}
$tradeDateTime = new DateTime();


for ($i = 0; $i <= 30; $i++) {
    $tradeDate = $tradeDateTime->format('Y-m-d');
    
    if (!array_key_exists($tradeDate, $overview)) {
            $overview[$tradeDate] = array();
    }
    if (!array_key_exists('expenditure', $overview[$tradeDate])) {
            $overview[$tradeDate]['expenditure'] = 0 ;
    }
    if (!array_key_exists('profit', $overview[$tradeDate])) {
            $overview[$tradeDate]['profit'] = 0;
    }
    if (!array_key_exists('revenue', $overview[$tradeDate])) {
            $overview[$tradeDate]['revenue'] = 0;
    }
    $tradeDateTime->setTimestamp($tradeDateTime->getTimestamp() - (60 * 60 * 24));
    
}

$chartDateTime = new DateTime();
$chartDateTime->setTimestamp(time() - (60*60*24*29));
$expenditure = array();
$revenue = array();
$profit = array();

for ($i = 1; $i <= 30; $i++) {
    $chartDate = $chartDateTime->format('Y-m-d');
    $expenditure[] = $overview[$chartDate]['expenditure'];
    $revenue[] = $overview[$chartDate]['revenue'];
    $profit[] = $overview[$chartDate]['profit'];
    
    $chartDateTime->setTimestamp($chartDateTime->getTimestamp() + (60 * 60 * 24));
    
}

$series1 = new HighRollerSeriesData();
$series1->addName('Expenditure')->addColor('#ff9900')->addData($expenditure);
$series2 = new HighRollerSeriesData();
$series2->addName('Profit')->addColor('#0099ff')->addData($profit);
$series3 = new HighRollerSeriesData();
$series3->addName('Revenue')->addColor('#00cc00')->addData($revenue);
$linechart = new HighRollerLineChart();
$linechart->title->text = "Overview";
$linechart->title->align = "center";
$linechart->title->floating = true;
$linechart->title->style->font = '18px Metrophobic, Arial, sans-serif';
$linechart->title->style->color = '#000000';
$linechart->title->x = 20;
$linechart->title->y = 20;
$linechart->chart->renderTo = 'linechart';
$linechart->chart->width = 700;
$linechart->chart->height = 500;
$linechart->chart->marginTop = 60;
$linechart->chart->marginLeft = 90;
$linechart->chart->marginRight = 30;
$linechart->chart->marginBottom = 110;
$linechart->chart->spacingRight = 10;
$linechart->chart->spacingBottom = 15;
$linechart->chart->spacingLeft = 0;
$linechart->chart->backgroundColor->linearGradient = array(0,0,0,300);
$linechart->chart->backgroundColor->stops = array(array(0,'rgb(217, 217, 217)'),array(1,'rgb(255, 255, 255)'));
$linechart->chart->alignTicks = false;
$linechart->legend->enabled = true;
$linechart->legend->layout = 'horizontal';
$linechart->legend->align = 'center';
$linechart->legend->verticalAlign = 'bottom';
$linechart->legend->itemStyle = array('color' => '#222');
$linechart->legend->backgroundColor->linearGradient = array(0,0,0,25);
$linechart->legend->backgroundColor->stops = array(array(0,'rgb(217, 217, 217)'),array(1,'rgb(255, 255, 255)'));
$linechart->tooltip->formatter = new HighRollerFormatter(); // TOOLTIP FORMATTER
$linechart->tooltip->backgroundColor->linearGradient = array(0,0,0,50);
$linechart->tooltip->backgroundColor->stops = array(array(0,'rgb(217, 217, 217)'),array(1,'rgb(0, 0, 0)'));
@$linechart->plotOptions->line->pointStart = strtotime('-29 day') * 1000;
$linechart->plotOptions->line->pointInterval = 24 * 3600 * 1000; // one day
$linechart->xAxis->type = 'datetime';
$linechart->xAxis->tickInterval = $linechart->plotOptions->line->pointInterval;
$linechart->xAxis->startOnTick = true;
$linechart->xAxis->tickmarkPlacement = 'on';
$linechart->xAxis->tickLength = 10;
$linechart->xAxis->minorTickLength = 5;
$linechart->xAxis->labels->align = 'right';
$linechart->xAxis->labels->step = 2;
$linechart->xAxis->labels->rotation = -35;
$linechart->xAxis->labels->x = 5;
$linechart->xAxis->labels->y = 10;
@$linechart->xAxis->dataLabels->formatter = new HighRollerFormatter();
$linechart->yAxis->labels->formatter = new HighRollerFormatter();
$linechart->yAxis->maxPadding = 0.2;
$linechart->yAxis->endOnTick = true;
$linechart->yAxis->minorGridLineWidth = 0;
$linechart->yAxis->minorTickInterval = 'auto';
$linechart->yAxis->minorTickLength = 1;
$linechart->yAxis->tickLength = 2;
$linechart->yAxis->minorTickWidth = 1;
$linechart->yAxis->title->text = 'ISK';
$linechart->yAxis->title->align = 'low';
$linechart->yAxis->title->style->font = '14px Metrophobic, Arial, sans-serif';
$linechart->yAxis->title->rotation = 0;
$linechart->yAxis->title->x = 60  ;
$linechart->yAxis->title->y = -10;
$linechart->yAxis->plotLines = array( array('color' => '#808080', 'width' => 1, 'value' => 0 ));
$linechart->addSeries($series1);
$linechart->addSeries($series2);
$linechart->addSeries($series3);
$linechart->enableAutoStep();


?>

<h1>OVERVIEW</h1><br />
<h2>PROFIT OVERVIEW (30 DAYS)</h2><br />
<div id="linechart"></div>
<script type="text/javascript">

  // example of how to define a tooltip formatter in a highcharts chart using using highroller
  var myChartOptions = <?php echo $linechart->getChartOptionsObject()?>

  // define your own formatter for tooltip
  myChartOptions.tooltip.formatter = function() {
    return '<b>' + this.series.name + '</b><br/>' +
        Highcharts.dateFormat('%b %e', this.x) + ': ' + Highcharts.numberFormat(this.y, 0, ',') + ' ISK';
  };

  // define your own formatter for xAxis.labels
  myChartOptions.xAxis.labels.formatter = function() {
    var newDate = new Date(this.value);
    return Highcharts.dateFormat('%b %e', this.value);
  };

  // define your own formatter for yAxis.labels
  myChartOptions.yAxis.labels.formatter = function() {
    return Highcharts.numberFormat(this.value, '', ',');
  };

  $(document).ready(function(){
    <?php echo $linechart->renderChartOptionsObject('myChartOptions')?>
  });

</script>
<h2>TURNOVER (30 DAYS)</h2>
<table border="1">
    <tr>
        <td>WHEN</td>
        <td>REVENUE</td>
        <td>EXPENDITURE</td>
        <td>PROFIT</td>
    </tr>
    <?php 
    $tradeDateTime = new DateTime();
    for ($i = 1; $i <= 30; $i++): 
    $tradeDate = $tradeDateTime->format('Y-m-d');
    ?>
    <tr>
        <td><?=$tradeDate ?></td>
        <td style="color: <?php print (double)$overview[$tradeDate]['revenue'] <= 0 ? 'red' : 'green'; ?>;">
            <?=number_format($overview[$tradeDate]['revenue']) ?> ISK
        </td>
        <td style="color: red;">
            -<?=number_format($overview[$tradeDate]['expenditure']) ?> ISK
        </td>
        <td style="color: <?php print (double)$overview[$tradeDate]['profit'] <= 0 ? 'red' : 'green'; ?>;">
            <?=number_format($overview[$tradeDate]['profit']) ?> ISK
        </td>
    </tr>
    <?php $tradeDateTime->setTimestamp($tradeDateTime->getTimestamp() - (60 * 60 * 24)); ?>
    <?php endfor; ?>
</table>