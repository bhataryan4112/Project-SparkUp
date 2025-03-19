<?php
// Constants for encryption/decryption
define('ENCRYPTION_KEY', '');
define('ENCRYPTION_IV', '');

/**
 * Encrypts data using AES-256-CBC encryption
 * 
 * @param string $data The data to encrypt
 * @return string Base64-encoded and URL-encoded encrypted data
 */
function encryptData($data) {
    $encrypted = openssl_encrypt(
        $data, 
        'AES-256-CBC', 
        ENCRYPTION_KEY, 
        OPENSSL_RAW_DATA, 
        ENCRYPTION_IV
    );
    
    return urlencode(base64_encode($encrypted));
}

/**
 * Decrypts data that was encrypted with encryptData()
 * 
 * @param string $encryptedData URL-encoded, base64-encoded encrypted data
 * @return string|false The decrypted data or false on failure
 */
function decryptData($encryptedData) {
    return openssl_decrypt(
        base64_decode(urldecode($encryptedData)), 
        'AES-256-CBC', 
        ENCRYPTION_KEY, 
        OPENSSL_RAW_DATA, 
        ENCRYPTION_IV
    );
}

// API endpoint functionality
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Parse the JSON data
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (isset($input['action'])) {
        header('Content-Type: application/json');
        
        switch ($input['action']) {
            case 'encrypt':
                if (isset($input['data'])) {
                    $result = encryptData($input['data']);
                    echo json_encode(['success' => true, 'encrypted' => $result]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Missing data parameter']);
                }
                break;
                
            case 'decrypt':
                if (isset($input['data'])) {
                    $result = decryptData($input['data']);
                    if ($result !== false) {
                        echo json_encode(['success' => true, 'decrypted' => $result]);
                    } else {
                        echo json_encode(['success' => false, 'error' => 'Decryption failed']);
                    }
                } else {
                    echo json_encode(['success' => false, 'error' => 'Missing data parameter']);
                }
                break;
                
            default:
                echo json_encode(['success' => false, 'error' => 'Invalid action']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Missing action parameter']);
    }
    
    exit;
}
?>
