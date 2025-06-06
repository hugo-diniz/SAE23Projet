<?php
$servername = "localhost";
$username = "adminB";
$password = "passroot";
$dbname = "sae23";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
  die("Connection to database failed: " . mysqli_connect_error());
}

