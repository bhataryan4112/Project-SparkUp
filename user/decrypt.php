<?php
define('ENCRYPTION_KEY', '');  // Same 32-char key
define('ENCRYPTION_IV', '');  // Same 16-char IV

function decryptEmail($encryptedData, $key, $iv) {
    $decoded = base64_decode($encryptedData);
    $decrypted = openssl_decrypt($decoded, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    return $decrypted;
}

// Get the encrypted data from the POST request
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['encryptedData'])) {
    $decryptedEmail = decryptEmail($data['encryptedData'], ENCRYPTION_KEY, ENCRYPTION_IV);

    if ($decryptedEmail !== false) {
        echo json_encode(["email" => $decryptedEmail]);
    } else {
        echo json_encode(["error" => "Decryption failed"]);
    }
} else {
    echo json_encode(["error" => "Invalid request"]);
}
?>
