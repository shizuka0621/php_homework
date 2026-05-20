<?php
// ========================================
// 資料庫設定 - 請依實際環境修改
// ========================================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // 資料庫帳號
define('DB_PASS', '0502');           // 資料庫密碼
define('DB_NAME', 'mailblast');

// ========================================
// PHPMailer SMTP 設定 - 請填入你的資訊
// ========================================
define('SMTP_HOST',     'smtp.gmail.com');
define('SMTP_USER',     'a1133304@mail.nuk.edu.tw');   // 寄件人 Gmail
define('SMTP_PASS',     'udmd nvws uhby iwnb');       // Gmail 應用程式密碼
define('SMTP_PORT',     465);
define('SMTP_FROM',     'a1133304@mail.nuk.edu.tw');
define('SMTP_FROM_NAME','MailBlast 系統');

// ========================================
// 建立 PDO 連線
// ========================================
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            throw new RuntimeException('資料庫連線失敗: ' . $e->getMessage(), 0, $e);
        }
    }
    return $pdo;
}
