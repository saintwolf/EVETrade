<?php
require_once 'includes/header.php';

$dateFrom = '';
$dateTo = '';
if (isset($_REQUEST['dateFrom']) && isset($_REQUEST['dateTo'])) {
    $dateFrom = stripslashes($_POST['dateFrom']);
    $dateTo = stripslashes($_POST['dateTo']);
} else { // Otherwise, set both to today!
    $dateFrom = new DateTime();
    $dateFrom = $dateFrom->format('Y-m-d');
    $dateTo = new DateTime();
    $dateTo = $dateTo->format('Y-m-d');
}

$transactions = $pheal->WalletTransactions(array('characterID' => $apiKeys['charId']))->toArray()['result']['transactions'];
$journals = $pheal->WalletJournal(array('characterID' => $apiKeys['charId'], 'rowCount' => '2560'))->toArray()['result']['transactions'];

$avgItemPrices = array();

$totalSold = 0.00;
$totalBought = 0.00;

foreach ($transactions as $trn) {
    $transDate = substr($trn['transactionDateTime'], 0, 10);
    
    if ((strtotime($transDate) >= strtotime($dateFrom)) && (strtotime($transDate) <= strtotime($dateTo))) {
        if (!array_key_exists($trn['typeID'], $avgItemPrices)) {
            $avgItemPrices[$trn['typeID']] = array(
                'purchased' =>  array(
                    'items' =>  0,
                    'totalPrice' => 0.00,
                    'typeID'    =>  $trn['typeID'],
                ),
                'sold'  => array(
                    'items' => 0,
                    'totalPrice' => 0.00,
                    'typeID'    =>  $trn['typeID'],
                )
            );
        }
        if ($trn['transactionType'] == 'buy') {
            $avgItemPrices[$trn['typeID']]['purchased']['items'] += (int)$trn['quantity'];
            $avgItemPrices[$trn['typeID']]['purchased']['totalPrice'] += ((double)$trn['price'] * (double)$trn['quantity']);
            $totalBought += ((double)$trn['price'] * (double)$trn['quantity']);
        } else { // It's a sell order
            $avgItemPrices[$trn['typeID']]['sold']['items'] += (int)$trn['quantity'];
            $avgItemPrices[$trn['typeID']]['sold']['totalPrice'] += ((double)$trn['price'] * (double)$trn['quantity']);
            $totalSold += ((double)$trn['price'] * (double)$trn['quantity']);
        }
    }
}

$brokerFees = 0.00;
$transactionTaxes = 0.00;
$otherFees = 0.00;
foreach ($journals as $jr) {
    $transDate = substr($jr['date'], 0, 10);
    if ((strtotime($transDate) > strtotime($dateFrom)) && (strtotime($transDate) < strtotime($dateTo))) {
        if ($jr['refTypeID'] == '54') {
         $transactionTaxes = (float)$transactionTaxes + (float)$jr['amount'];
        } elseif ($jr['refTypeID'] == '46'){
            $brokerFees = (float)$brokerFees + (float)$jr['amount'];
        } else {
            $otherFees = (float)$otherFees + (float)$jr['amount'];
        }
    }
}

?>
<h1>PURCHASES / SALES</h1>
<h2>PERIOD OF TIME</h2>
<table>
    <tr>
        <td>From:</td>
        <td>
            <form action="<?=$_SERVER['PHP_SELF'] ?>" method="post">
            <?php
            $date4_default = $dateTo;
            $date3_default = $dateFrom;
	    $myCalendar = new tc_calendar("dateFrom", true, false);
	    $myCalendar->setIcon("calendar/images/iconCalendar.gif");
	    $myCalendar->setDate(date('d', strtotime($date3_default))
                  , date('m', strtotime($date3_default))
                  , date('Y', strtotime($date3_default)));
            $myCalendar->setPath("calendar/");
	    $myCalendar->setYearInterval(1970, 2020);
	    $myCalendar->setAlignment('left', 'bottom');
	    $myCalendar->setDatePair('date3', 'date4', $date4_default);
	    $myCalendar->writeScript();
            ?>
        </td>
        <td>To:</td>
        <td>
          <?php
	  $myCalendar = new tc_calendar("dateTo", true, false);
	  $myCalendar->setIcon("calendar/images/iconCalendar.gif");
	  $myCalendar->setDate(date('d', strtotime($date4_default))
           , date('m', strtotime($date4_default))
           , date('Y', strtotime($date4_default)));
	  $myCalendar->setPath("calendar/");
	  $myCalendar->setYearInterval(1970, 2020);
	  $myCalendar->setAlignment('left', 'bottom');
	  $myCalendar->setDatePair('date3', 'date4', $date3_default);
	  $myCalendar->writeScript();	  
	  ?>
        </td>
        <td>
            <input type="submit" value="GO" />
        </td>
</form>
<table border="1" style="width: 100%">
    <tr>
        <td colspan="2">REVENUE</td>
        <td colspan="2">EXPENDITURE</td>
    </tr>
    <tr>
        <td><b>Sales</b></td>
        <td style="color: green"><?=number_format($totalSold) ?> ISK</td>
        <td><b>Purchases</b></td>
        <td style="color: red">-<?=number_format($totalBought) ?> ISK</td>
    </tr>
    <tr>
        <td colspan="4" style="text-align: right; color: <?php print (($totalSold - $totalBought) <= 0 ? 'red' : 'green') ?>">Summary: <?php print number_format($totalSold - $totalBought) ?> ISK</td>
    </tr>
    <tr>
        <td colspan="2"></td>
        <td><b>Broker's Fees</b></td>
        <td style="color: red"><?=number_format($brokerFees) ?> ISK</td>
    </tr>
    <tr>
        <td colspan="2"></td>
        <td><b>Transaction Taxes</b></td>
        <td style="color: red"><?=number_format($transactionTaxes) ?> ISK</td>
    </tr>
    <tr>
        <td colspan="4" style="text-align: right; color: <?php print (($totalSold - $totalBought - $brokerFees - $transactionTaxes) <= 0 ? 'red' : 'green') ?>">Summary: <?php print number_format($totalSold - $totalBought - $brokerFees - $transactionTaxes) ?> ISK</td>
    </tr>
</table>
<h2>TRANSACTIONS</h2><br />
<table border="1" style="width: 100%">
    <thead> 
    <tr>
        <th rowspan="2">Product Type</th>
        <th colspan="2">Purchased</th>
        <th colspan="2">Sold</th>
        <th rowspan="2">Margin</th>
        <th rowspan="2">Profit</th>
    </tr>
    <tr>
        <th>Items</th>
        <th>Average Price</th>
        <th>Items</th>
        <th>Average Price</th>
    </tr>
</thead> 
<tbody>
    <?php foreach ($avgItemPrices as $item): ?>
    
    <?php
    $avgBought = $item['purchased']['items'] == 0 ? 0 : ($item['purchased']['totalPrice'] / $item['purchased']['items']);
    $avgSold = $item['sold']['items'] == 0 ? 0 : ($item['sold']['totalPrice'] / $item['sold']['items']);
    ?>
    <tr>
        <td>
            <?php
            $stmt = $db->prepare('SELECT typeName FROM invTypes WHERE typeID = ?');
            $stmt->bind_param('i', $item['purchased']['typeID']);
            $stmt->execute();
            $typeName = $stmt->get_result()->fetch_assoc()['typeName'];
            print '<a href="product.php?typeID=' . $item['purchased']['typeID'] . '">';
            print $typeName;
            print '</a>';
            ?>
        </td>
        <td>
            <?php
            print number_format($item['purchased']['items'])
            ?>
        </td>
        <td style="color: red"><?=$item['purchased']['items'] == 0 ? 0 : number_format(sprintf('%0.2f', $avgBought)) ?> ISK</td>
        <td>
            <?php
            print number_format($item['sold']['items'])
            ?>
        </td>
        <td style="color: green"><?=$item['sold']['items'] == 0 ? 0 : number_format((int)$avgSold) ?> ISK</td>
        <td>
        <?php
        if ($avgSold == 0 && $avgBought == 0) {
            print 0;
        } elseif ($avgBought == 0 && $avgSold != 0) {
            print 100 * $item['purchased']['items'];
        } elseif ($avgSold == 0 && $avgBought != 0) {
            $margin = 100*(($avgSold + 0.01) / $avgBought-1);
            print (int)$margin;
        }
        else {
            $margin = 100*($avgSold / $avgBought-1);
            print (int)$margin;
        }
1       ?>% 
        </td>
        <td style="color: <?=($avgSold * $item['sold']['items']) - ($avgBought * $item['purchased']['items']) <= 0 ? 'red' : 'green' ?>">
            <?php
            print number_format(($avgSold * $item['sold']['items']) - ($avgBought * $item['purchased']['items']));
            ?> ISK
        </td>
    </tr>
    <?php endforeach; ?>
</tbody>
</table>
