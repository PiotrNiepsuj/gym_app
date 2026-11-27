<?php
$config = require "config.php";

$connection = new mysqli(
    $config['host'],
    $config['user'],
    $config['password'],
    $config['db_name']
);

$query = "SET time_zone = '+00:00'";
$connection->query($query);

?>