<?php
require_once 'includes/header.php';

$journals = $pheal->WalletJournal(array('characterID' => $apiKeys['charId'], 'rowCount' => '2560'))->toArray()['result']['transactions'];

$pheal->scope = 'eve';
$refTypes = $pheal->RefTypes()->toArray()['result']['refTypes'];
?>
<h1>JOURNAL</h1>
<table border="1">
    <tr>
        <td>WHEN</td>
        <td>TYPE</td>
        <td>TOTAL</td>
        <td>BALANCE<td>
    </tr>
    <?php foreach ($journals as $jrn): ?>
    <tr>
        <td><?=$jrn['date'] ?></td>
        <td><?=$refTypes[$jrn['refTypeID']]['refTypeName'] ?></td>
        <td style="color: <?=(int)$jrn['amount'] < 0 ? 'red' : 'green' ?>;"><?=number_format($jrn['amount']) ?> ISK</td>
        <td><?=number_format($jrn['balance']) ?> ISK</td>
    </tr>
    <?php endforeach; ?>
    
</table>