<?php
require_once 'includes/header.php';
require_once('highroller/HighRollerSeriesData.php');
require_once('highroller/HighRollerLineChart.php');

$typeName = '';
if (!isset($_GET['typeID'])) {
    die('NO ITEM');
} else {
    $typeID = $_GET['typeID'];
}

// Get typeName
$stmt = $db->prepare('SELECT typeName FROM invTypes WHERE typeID = ?');
$stmt->bind_param('i', $typeID);
if (!$stmt->execute()) {
    die ('INVALID ITEM');
} else {
    $typeName = $stmt->get_result()->fetch_assoc()['typeName'];
}

// Get Transactions
$transactions = $pheal->WalletTransactions(array(
    'characterID' => $apiKeys['charId'],
    'rowCount' => '2560',
))->toArray()['result']['transactions'];

$overview = array();
$totalBuy = 0.00;
$totalSell = 0.00;
// Get last 30 days of item order by day
foreach ($transactions as $trn) {
    $date = DateTime::createFromFormat('Y-m-d H:i:s', $trn['transactionDateTime'])->format('Y-m-d');
    
    if ($trn['typeID'] == $typeID) {  
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
        if ($trn['transactionType'] == 'buy') {
            $totalBuy += $trn['price'] * $trn['quantity'];
        } else {
            $totalSell += $trn['price'] * $trn['quantity'];
        }
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
$linechart->title->text = $typeName;
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
<h1><?=$typeName ?></h1>
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
<h2>OPERATING STATEMENT</h2>
<table border="1" style="width: 100%">
    <tr>
        <td colspan="2" style="width: 50%">REVENUE</td>
        <td colspan="2" style="width: 50%">EXPENDITURE</td>
    </tr>
    <tr>
        <td><b>Sales</b></td>
        <td style="color: green"><?=number_format($totalSell) ?> ISK</td>
        <td><b>Purchases</b></td>
        <td style="color: red"><?=number_format($totalBuy) ?> ISK</td>
    </tr>
    <tr>
        <td colspan="4" style="text-align: right; color: <?=($totalSell - $totalBuy) <= 0 ? 'red' : 'green' ?>"><b>Summary: </b><?=number_format($totalSell-$totalBuy) ?> ISK</td>
    </tr>
</table>
<h2>PURCHASES / SALES</h2>
<table border="1" style="width: 100%">
    <tr>
        <td>WHEN</td>
        <td>TYPE</td>
        <td>PRICE</td>
        <td>QTY</td>
        <td>CREDIT</td>
        <td>WHO</td>
        <td>WHERE</td>
    </tr>
    <?php foreach ($transactions as $trn): ?>
    <tr>
        <td><?=$trn['transactionDateTime'] ?></td>
        <td><?=$typeName ?></td>
        <td><?=number_format($trn['price']) ?> ISK</td>
        <td><?=$trn['quantity'] ?></td>
        <td style="color: <?=$trn['transactionType'] == 'buy' ? 'red' : 'green' ?>">
            <?php
            print $trn['transactionType'] == 'buy' ? '-' : '';
            print number_format((int)$trn['price'] * (int)$trn['quantity']);
            print ' ISK';
            ?>
        </td>
        <td><?=$trn['clientName'] ?></td>
        <td><?=$trn['stationName'] ?></td>
    </tr>
    <?php endforeach; ?>
</table>