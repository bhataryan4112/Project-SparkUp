<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");

$host = 'localhost';  
$username = "";  
$password = "";  
$dbname = "";

// Create connection  
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection  
if ($conn->connect_error) {  
    die("Connection failed: " . $conn->connect_error);  
}  

$sql = "SELECT Users.Email, Matches.TableNumber, Matches.RoomNumber, Users.Status 
        FROM Matches 
        JOIN Users ON Matches.User1ID = Users.UserID OR Matches.User2ID = Users.UserID";  
$result = $conn->query($sql);  

$data = array();

if ($result->num_rows > 0) {  
    while($row = $result->fetch_assoc()) {  
        $data[] = $row;  
    }  
}  

echo json_encode($data);  

$conn->close();  
?>