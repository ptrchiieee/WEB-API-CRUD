<?php
// db.php
$host = 'localhost';
$db_name = 'todo_db';
$username = 'root';
$password = '';
$connected = mysqli_connect($host, $username, $password, $db_name);

if (!$connected) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
