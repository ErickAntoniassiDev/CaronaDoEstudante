<?php

$host = "sql300.infinityfree.com";
$user = "if0_40131532";
$pass = "estudanteapp";
$db = "if0_40131532_bdcarona";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
  die("Erro na conexão: " . $conn->connect_error);
}

?>