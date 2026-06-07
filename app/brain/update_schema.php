<?php
require_once 'app/config/database.php';

try {
    $db = (new Database())->getConnection();
    
    // Kiểm tra xem cột đã tồn tại chưa
    $check = $db->query("SHOW COLUMNS FROM account LIKE 'email_verify_expires_at'");
    if ($check->rowCount() == 0) {
        $db->exec("ALTER TABLE account ADD COLUMN email_verify_expires_at DATETIME NULL AFTER email_verify_token");
        echo "Successfully added email_verify_expires_at column.\n";
    } else {
        echo "Column already exists.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
