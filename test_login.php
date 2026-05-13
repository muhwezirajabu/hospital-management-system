<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Your InfinityFree Database Credentials
 $host = "sql300.infinityfree.com"; // CHECK YOUR ACTUAL HOST IN THE CONTROL PANEL
 $username = "if0_41869145_uph"; 
 $password = "WmJX6I3PIFo";
 $dbname = "if0_41869145_uph";

// Try to connect
 $conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    // If it fails, this will tell you EXACTLY why
    die("<h3>Connection FAILED!</h3><b>Error:</b> " . $conn->connect_error);
} else {
    echo "<h3>Connection Successful!</h3><p>The database is working fine on your phone.</p>";
}
?>
