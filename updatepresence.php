<?php

// Get the params first

$params = array_change_key_case($_GET);
$name = $params['name'];
$status = $params['status'];

if (empty($status)) {
    $status = "Away";
}

if ($status != "Away") {
   $status = "At " . $status;
}

//Connect to DB
$servername = "localhost";
$username = "<your username>";
$password = "<your password>";
$dbname = "<your AT_presence database>";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 

// query
$sql = "REPLACE INTO presence (`name`,`status`,`timestamp`) values ('" . $name . "','" . $status . "','" . date("Y-m-d H:i:s") . "')";

if ($conn->query($sql) === TRUE) {
    echo "New record created successfully";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
