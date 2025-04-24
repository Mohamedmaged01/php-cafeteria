
<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "php_project";

// Create myconnection
$myconnection = mysqli_connect($servername, $username, $password, $dbname);
// Check myconnection
if ($myconnection) {
} else {
    die("myconnection failed: " . mysqli_connect_error());
}
