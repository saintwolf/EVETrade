<?php
require_once 'vendor/autoload.php';
require_once 'inc/config.inc.php';


//Connect to DB
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Get API key from DB
$sql = 'SELECT * FROM apikeys WHERE id=1';
$result = $db->query($sql);
$api = $result->fetch_assoc();
?>
<body style="text-align: center;"">
<h1>COMING SOON!!!</h1><br />
<a href="overview.php">Temp Link To Overview</a><br />
Choose Character:<br />
<select>
    <option><?php print $api['charName']; ?></option>
    <input type="submit" value="GO!" />
</select>
</body>