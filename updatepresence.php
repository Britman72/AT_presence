<?php

// Get the params first

$params = array_change_key_case($_GET);
$id = $params['id'];
$name = $params['name'];
$place = $params['place'];
$lastplace = $params['lastplace'];

if (empty($place)) {
    $place = "Away";
}

if ($place != "Away") {
   $place = "At " . $place;
}

//Connect to DB
$servername = "localhost";
$username = "";
$password = "";
$dbname = "";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 

// query
$sql = "REPLACE INTO presence (`name`,`place`,`timestamp`,`last place`) values ('" . $name . "','" . $place . "','" . date("Y-m-d H:i:s") . "','" . $lastplace . "')";

if ($conn->query($sql) === TRUE) {
    echo "New record created successfully";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
