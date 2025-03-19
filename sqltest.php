<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize output
$output = "";

// Check if there's a message in session
if (isset($_SESSION['output'])) {
    $output = $_SESSION['output'];
    unset($_SESSION['output']); // Clear the message
}

// Database configuration
$servername = "localhost";
$username = ""; 
$password = ""; 
$dbname = ""; 

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection with improved error handling
if ($conn->connect_error) {
    $output = "<p style='color: red;'>Database connection failed. Please try again later.</p>";
} else {
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        // Validate email
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
        if ($email === false) {
            $output = "<p style='color: red;'>Invalid email format</p>";
        } else {
            $email = $conn->real_escape_string($email);
            
            // Updated query to use COL 1
            $sql = "SELECT * FROM `2025_01_12_00_35_22_matches` WHERE `COL 1` = ?";
            $stmt = $conn->prepare($sql);
            
            if ($stmt) {
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result) {
                    if ($result->num_rows > 0) {
                        $row = $result->fetch_assoc();

                        $output .= "<h2>Matches for Email: " . htmlspecialchars($email) . "</h2>";
                        $output .= "<table>";
                        $output .= "<tr><th>Match 1</th><th>Match 2</th><th>Match 3</th><th>Match 4</th><th>Match 5</th></tr>";
                        $output .= "<tr>";
                        $output .= "<td>" . htmlspecialchars($row['COL 2']) . "</td>";
                        $output .= "<td>" . htmlspecialchars($row['COL 3']) . "</td>";
                        $output .= "<td>" . htmlspecialchars($row['COL 4']) . "</td>";
                        $output .= "<td>" . htmlspecialchars($row['COL 5']) . "</td>";
                        $output .= "<td>" . htmlspecialchars($row['COL 6']) . "</td>";
                        $output .= "</tr>";
                        $output .= "</table>";
                    } else {
                        $output .= "<p>No matches found for the email: " . htmlspecialchars($email) . "</p>";
                    }
                } else {
                    $output = "<p style='color: red;'>Error executing query: " . $conn->error . "</p>";
                }
                $stmt->close();
            } else {
                $output = "<p style='color: red;'>Error preparing query: " . $conn->error . "</p>";
            }
        }
        // Store output in session instead of variable
        $_SESSION['output'] = $output;
        
        // Redirect to same page
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fetch Matches</title>
    <style>
        body {
            background-color: #121212;
            color: #ffffff;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        h1, h2 {
            color: #ff9800;
        }
        form {
            margin-bottom: 20px;
        }
        input[type="text"] {
            padding: 10px;
            border: 1px solid #ff9800;
            border-radius: 5px;
            background-color: #333;
            color: #fff;
            width: 300px;
        }
        button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            background-color: #ff9800;
            color: #000;
            cursor: pointer;
        }
        button:hover {
            background-color: #e68900;
        }
        table {
            border-collapse: collapse;
            width: 80%;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ff9800;
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #333;
        }
        td {
            background-color: #222;
        }
    </style>
</head>
<body>
    <h1>Search Matches</h1>
    <form method="POST" action="">
        <input type="text" name="email" placeholder="Enter Email" required>
        <button type="submit">Search</button>
    </form>
    <div>
        <?= $output; ?>
    </div>
</body>
</html>
