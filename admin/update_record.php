<?php
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db_host = "localhost";
    $db_name = "";
    $db_user = "";
    $db_pass = "";

    try {
        $db = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $db->prepare("UPDATE registration SET 
            name = ?, age = ?, gender = ?, campus = ?, email = ?,
            category1_score = ?, category2_score = ?, category3_score = ?,
            category4_score = ?, category5_score = ?
            WHERE id = ?");
            
        $stmt->execute([
            $_POST['name'],
            $_POST['age'],
            $_POST['gender'],
            $_POST['campus'],
            $_POST['email'],
            $_POST['category1_score'],
            $_POST['category2_score'],
            $_POST['category3_score'],
            $_POST['category4_score'],
            $_POST['category5_score'],
            $_POST['id']
        ]);
        
        header('Location: /admin?message=Record+updated+successfully');
    } catch(PDOException $e) {
        header('Location: /admin?error=Update+failed:+'.$e->getMessage());
    }
    exit;
}
