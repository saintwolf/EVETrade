<?php
require 'includes/header.php';

$transactions = $pheal->WalletTransactions(array('characterID' => $apiKeys['charId']))->toArray()['result']['transactions'];
?>
        <h1>TRANSACTIONS</h1>
        <table border="1">                
            <tr>
                <td>WHEN</td>
                <td>TYPE</td>
                <td>PRICE</td>
                <td>QTY</td>
                <td>CREDIT</td>
                <td>WHO</td>
                <td>WHERE</td>
            </tr>
            <?php foreach ($transactions as $trans): ?>
                <tr>
                    <td><?=$trans['transactionDateTime'] ?></td>
                    <td>
                        <?php
                        $stmt = $db->prepare('SELECT typeName FROM invTypes WHERE typeID = ?');
                        $stmt->bind_param('i', $trans['typeID']);
                        $stmt->execute();
                        $typeName = $stmt->get_result()->fetch_assoc()['typeName'];
                        print $typeName;
                        ?>
                    </td>
                    <td><?=$trans['price'] ?></td>
                    <td><?=$trans['quantity'] ?></td>
                    <td style="<?php print $trans['transactionType'] == 'buy' ? 'color: red;' : 'color: green;'; ?>">
                        <?php
                        $credit = $trans['quantity'] * $trans['price'];
                        print $trans['transactionType'] == 'buy' ? '-' : '';
                        print number_format($credit) . ' ISK';
                        ?>
                    </td>
                    <td><?=$trans['clientName'] ?></td>
                    <td><?=$trans['stationName'] ?></td>
                </tr>
            <?php endforeach; ?>
        </table>