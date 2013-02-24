<?php
require_once 'vendor/autoload.php';
require_once 'inc/config.inc.php';
require_once 'inc/functions.php';
require_once 'calendar/classes/tc_calendar.php';
require_once('highroller/HighRoller.php');

// Get stuff from DB
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Get the API Keys
$sql = 'SELECT * FROM apikeys WHERE id=1';
$stmt = $db->prepare($sql);
$stmt->execute();

$apiKeys = $stmt->get_result()->fetch_assoc();

$pheal = new Pheal($apiKeys['apiKey'], $apiKeys['vCode'], 'char');
?>
<html>
<head>
    <title>EVETrade</title>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
    <?php echo HighRoller::setHighChartsLocation("highcharts/highcharts.js");?>
    <?php echo HighRoller::setHighChartsThemeLocation("highcharts/themes/gray.js");?>
    <script language="javascript" src="calendar/calendar.js"></script>
</head>
<body>
    <a href="overview.php">OVERVIEW</a> - 
    <a href="purchases_sales.php">PURCHASES/SALES</a> - 
    <a href="balance.php">BALANCE</a> - 
    <a href="marketorders.php">MARKET ORDERS</a> - 
    <a href="journal.php">JOURNAL</a> - 
    <a href="transactions.php">TRANSACTIONS<a/>