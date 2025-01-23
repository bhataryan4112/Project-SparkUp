<?php
// Disable error display in production
ini_set('display_errors', 0);
error_reporting(0);

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // Get JSON data
    $jsonData = file_get_contents('php://input');
    $data = json_decode($jsonData, true);

    // Validate data
    if (!$data) {
        throw new Exception('Invalid JSON data received');
    }

    // Database configuration
    $db_host = "localhost";
    $db_name = "u265056410_registration";
    $db_user = "u265056410_arya";
    $db_pass = "Registration_SparkUp@123";

    // Connect to database
    $db = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Prepare statement
    $stmt = $db->prepare("INSERT INTO registration (
        name, age, gender, campus, email, fun_fact,
        category1_score, category2_score, category3_score, 
        category4_score, category5_score
    ) VALUES (
        :name, :age, :gender, :campus, :email, :funFact,
        :category1, :category2, :category3, :category4, :category5
    )");

    // Execute with data
    $result = $stmt->execute([
        ':name' => $data['name'],
        ':age' => $data['age'],
        ':gender' => $data['gender'],
        ':campus' => $data['campus'],
        ':email' => $data['email'],
        ':funFact' => $data['funFact'],
        ':category1' => $data['category1'],
        ':category2' => $data['category2'],
        ':category3' => $data['category3'],
        ':category4' => $data['category4'],
        ':category5' => $data['category5']
    ]);

    // Send success response
    echo json_encode([
        'success' => true,
        'message' => 'Registration completed successfully'
    ]);

} catch(Exception $e) {
    // Log error (but don't expose details to client)
    error_log("Error: " . $e->getMessage());
    
    // Send error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Registration failed. Please try again.'
    ]);
}
?>
