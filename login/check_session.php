<?php
session_start();
header('Content-Type: application/json');

$response = [
    'active' => isset($_SESSION['user_email']) && !empty($_SESSION['user_email']),
    'email' => $_SESSION['user_email'] ?? null
];

echo json_encode($response);
