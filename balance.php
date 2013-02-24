<?php
require 'includes/header.php';
// Get Market Orders
$marketOrders = $pheal->MarketOrders(array('characterID' => $apiKeys['charId']))->toArray()['result']['orders'];
$sellOrders = 0;
$buyOrders = 0;
foreach ($marketOrders as $mo) {
    if ($mo['orderState'] == '0' && $mo['bid'] == '0') {
        $sellOrders = $sellOrders + ((int)$mo['price'] * $mo['volRemaining']);
    } else if ($mo['orderState'] == '0' && $mo['bid'] == '1') {
        $buyOrders = $buyOrders + ((int)$mo['price'] * $mo['volRemaining']);
    }
}

$purchases = 0;
$sales = 0;
// Get Transactions
$transactions = $pheal->WalletTransactions(array('characterID' => $apiKeys['charId'], 'rowCount' => '2560'))->toArray()['result']['transactions'];
foreach ($transactions as $tr) {
    if ($tr['transactionType'] == 'buy') {
        $purchases = $purchases + ((float)$tr['price'] * $tr['quantity']);
    } elseif ($tr['transactionType'] == 'sell') {
        $sales = $sales + ((float)$tr['price'] * $tr['quantity']);
    }
}

$brokerFees = 0;
$transactionTaxes = 0;
$otherFees = 0;
// Get Journal
$journals = $pheal->WalletJournal(array('characterID' => $apiKeys['charId'], 'rowCount' => '2560'))->toArray()['result']['transactions'];
foreach ($journals as $jr) {
    if ($jr['refTypeID'] == '54') {
        $transactionTaxes = (float)$transactionTaxes + (float)$jr['amount'];
    } elseif ($jr['refTypeID'] == '46'){
        $brokerFees = (float)$brokerFees + (float)$jr['amount'];
    } else {
        $otherFees = (float)$otherFees + (float)$jr['amount'];
    }
}

// Get wallet balance
$walletBalance = $pheal->CharacterSheet(array('characterID' => $apiKeys['charId']))->toArray()['result']['balance'];


//Total Operating Statement
$totalOperating = ($sales - $purchases) + $brokerFees + $transactionTaxes;
?>
<h1>BALANCE</h1><br />
<h2>ASSETS AND LIABILITIES</h2>
<table border="1" width="100%">
    <tr style="text-align: center">
        <td colspan="2">ASSETS</td>
        <td colspan="2">LIABILITIES</td>
    </tr>
    <tr>
        <td width="20%"><b>Sell Orders</b></td>
        <td width="30%" style="color: green;"><?=number_format($sellOrders) ?> ISK</td>
        
        <td width="20%"><b>Wallet</b></td>
        <td width="30%" style="color: green;"><?=number_format($walletBalance) ?> ISK</td>
    </tr>
    <tr>
        <td width="20%"><b>Buy Orders</b></td>
        <td width="30%" style="color: green;"><?=number_format($buyOrders) ?> ISK</td>
    </tr>
    <tr></tr>
    <tr>
        <td width="20%"><b>Summary</b></td>
        <td width="30%" style="color: green;"><?=number_format($sellOrders + $buyOrders) ?> ISK</td>
        
        <td width="20%"><b>Summary</b></td>
        <td width="30%" style="color: green;"><?=number_format($walletBalance) ?> ISK</td>
    </tr>
    <tr><td colspan="4" style="text-align: right; color: green;"><b>Summary: </b><?=number_format($sellOrders + $buyOrders + $walletBalance) ?> ISK</td></tr>
</table>

<h2>OPERATING STATEMENT</h2>
<table border="1" width="100%">
    <tr style="text-align: center">
        <td colspan="2">REVENUE</td>
        <td colspan="2">EXPENDITURE</td>
    </tr>
    <tr>
        <td width="20%"><b>Sales</b></td>
        <td width="30%" style="color: green;"><?=number_format($sales) ?> ISK</td>
        
        <td width="20%"><b>Purchases</b></td>
        <td width="30%" style="color: red;">-<?=number_format($purchases) ?> ISK</td>
    </tr>
    <tr>
        <td colspan="2"></td>
        <td><b>Broker's Fees</td>
        <td style="color: red;"><?=number_format($brokerFees) ?> ISK</td>
    </tr>
    <tr>
        <td colspan="2"></td>
        <td><b>Transaction Taxes</td>
        <td style="color: red;"><?=number_format($transactionTaxes) ?> ISK</td>
    </tr>
    <tr><td colspan="4" style="text-align: right; color: <?=$totalOperating > 0 ? 'green' : 'red' ?>"><b>Summary: </b><?=number_format($totalOperating) ?> ISK</td></tr>