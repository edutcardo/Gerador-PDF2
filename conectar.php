<?php
$servername = "localhost"; // ou o endereço do seu servidor de banco de dados
$username = "root"; // seu usuário do banco de dados
$password = "";   // sua senha do banco de dados
$dbname = "irradiacao";    // nome do seu banco de dados

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}
?>