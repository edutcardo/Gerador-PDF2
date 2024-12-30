<?php
// db_connection.php
$servername = "srv1781.hstgr.io";
$username = "u345670158_eduardotcardo3";
$password = "Rtz6ngqr@";
$dbname = "u345670158_tarifa";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>