<?php
require 'includes/header.php';

// Get Market Orders
$marketOrders = $pheal->MarketOrders(array('characterID' => $apiKeys['charId']))->toArray()['result']['orders'];
?>
<h1>Market Orders</h1> <br />
<h2>Sell Orders</h2>
<table border="1">
    <thead>
    <td>TYPE</td>
    <td>UNITS</td>
    <td>PRICE/ITEM</td>
    <td>PRICE/STACK</td>
    <td>STATION</td>
    <td>EXPIRES IN</td>
    </thead>
    <tbody>
        <?php foreach ($marketOrders as $mo): ?>
        <?php if(($mo['orderState'] == '0') && $mo['bid'] == '0'): ?>
        <tr>
            <td>
                <?php
                print getItemName($mo['typeID'], $db);
                ?>
            </td>
            <td>
                <?php
                print number_format($mo['volRemaining']) . '/' . number_format($mo['volEntered']);
                ?>
            </td>
            <td>
                <?php
                print number_format($mo['price']);
                ?> ISK
            </td>
            <td>
                <?php
                print number_format((int)$mo['price'] * $mo['volEntered']);
                ?> ISK
            </td>
            <td>
                <?php
                print getStationName($mo['stationID'], $db);
                ?>
            </td>
            <td>
                <?php
                $rem =  strtotime($mo['issued'] . '+90 days') - time();
                $day = floor($rem / 86400);
                $hr  = floor(($rem % 86400) / 3600);
                $min = floor(($rem % 3600) / 60);
                $sec = ($rem % 60);
                print $day . 'D ' . $hr . 'H ' . $min . 'M ' . $sec . 'S';
                ?>
            </td>
        </tr>
        <?php endif; ?>
        <?php endforeach; ?>
    </tbody>
</table>
<br />
<h2>Buy Orders</h2>
<table border="1">
    <thead>
    <td>TYPE</td>
    <td>UNITS</td>
    <td>PRICE/ITEM</td>
    <td>PRICE/STACK</td>
    <td>STATION</td>
    <td>EXPIRES IN</td>
    </thead>
    <tbody>
        <?php foreach ($marketOrders as $mo): ?>
        <?php if(($mo['orderState'] == '0') && $mo['bid'] == '1'): ?>
        <tr>
            <td>
                <?php
                print getItemName($mo['typeID'], $db);
                ?>
            </td>
            <td>
                <?php
                print number_format($mo['volRemaining']) . '/' . number_format($mo['volEntered']);
                ?>
            </td>
            <td>
                <?php
                print number_format($mo['price']);
                ?> ISK
            </td>
            <td>
                <?php
                print number_format((int)$mo['price'] * $mo['volEntered']);
                ?> ISK
            </td>
            <td>
                <?php
                print getStationName($mo['stationID'], $db);
                ?>
            </td>
            <td>
                <?php
                $rem =  strtotime($mo['issued'] . '+90 days') - time();
                $day = floor($rem / 86400);
                $hr  = floor(($rem % 86400) / 3600);
                $min = floor(($rem % 3600) / 60);
                $sec = ($rem % 60);
                print $day . 'D ' . $hr . 'H ' . $min . 'M ' . $sec . 'S';
                ?>
            </td>
        </tr>
        <?php endif; ?>
        <?php endforeach; ?>
    </tbody>
</table>
<?php print var_dump($marketOrders); ?>