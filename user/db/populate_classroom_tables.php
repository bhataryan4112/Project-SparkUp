<?php
$host = 'localhost';
$username = "u265056410_sparkup";
$password = "Sparkup@12345";
$dbname = "u265056410_sparkup";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Define classrooms and tables
    $classrooms = [
        '1001' => 10,
        '1002' => 10,
        'ground' => 10,
        '2001' => 10
    ];
    
    // Insert classroom tables
    foreach ($classrooms as $classroom => $tableCount) {
        for ($i = 1; $i <= $tableCount; $i++) {
            try {
                $stmt = $pdo->prepare("INSERT INTO classroom_tables (table_number, classroom, capacity, status) 
                                      VALUES (?, ?, 2, 'available')");
                $stmt->execute([$i, $classroom]);
            } catch (PDOException $e) {
                // Skip duplicates
                if ($e->getCode() != 23000) {
                    throw $e;
                }
            }
        }
    }
    
    echo "Classroom tables populated successfully!";
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
