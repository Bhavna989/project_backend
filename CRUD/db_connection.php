<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "csci6040_study";

$con = mysqli_connect($host, $username, $password, $database);

if (!$con) {
    http_response_code(500);
    die(json_encode(["error" => "Cannot connect DB: " . mysqli_connect_error()]));
}
?>