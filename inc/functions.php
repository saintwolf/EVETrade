<?php
function getItemName($characterID, mysqli $db) {
    $sql = 'SELECT typeName FROM invTypes WHERE typeID = ?';
                $stmt = $db->prepare($sql);
                $stmt->bind_param('i', $characterID);
                $stmt->execute();
                $name = $stmt->get_result()->fetch_assoc()['typeName'];
                return $name;
}
function getStationName($stationID, mysqli $db) {
    $sql = 'SELECT itemName FROM invNames WHERE itemID = ?';
                $stmt = $db->prepare($sql);
                $stmt->bind_param('i', $stationID);
                $stmt->execute();
                $name = $stmt->get_result()->fetch_assoc()['itemName'];
                return $name;
}
function sortTransDate($a, $b) {

    $a = strtotime($a['saleref']);
    $b = strtotime($b['saleref']);

    if ($a == $b) {
        return 0;
    }

    return ($a < $b) ? -1 : 1;

}
?>
