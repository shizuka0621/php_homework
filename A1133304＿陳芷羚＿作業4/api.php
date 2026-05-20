<?php
// api.php - 處理所有 AJAX 請求
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/config.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

function json_out(array $data): void {
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// 避免 PHP warning / fatal 變成 HTML，造成前端 res.json() 失敗卻沒提示
set_exception_handler(function (Throwable $e) {
    http_response_code(500);
    json_out([
        'success' => false,
        'msg' => '後端錯誤：' . $e->getMessage(),
    ]);
});

switch ($action) {

    // ── 取得收件人清單 ──────────────────────────────
    case 'get_recipients':
        $db = getDB();
        $rows = $db->query("SELECT no, email, name, status, created_at FROM recipients ORDER BY no")->fetchAll();
        json_out(['success' => true, 'data' => $rows, 'total' => count($rows)]);

    // ── 新增 Email ──────────────────────────────────
    case 'add_email':
        $email = trim($_POST['email'] ?? '');
        $name  = trim($_POST['name']  ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            json_out(['success' => false, 'msg' => '無效的 Email 格式']);
        }

        try {
            $db = getDB();
            $st = $db->prepare("INSERT INTO recipients (email, name) VALUES (?, ?)");
            $st->execute([$email, $name]);
            json_out(['success' => true, 'msg' => "已新增 $email"]);
        } catch (PDOException $e) {
            json_out(['success' => false, 'msg' => 'Email 已存在或資料庫寫入失敗：' . $e->getMessage()]);
        }

    // ── 批次匯入 Email（換行、逗號、分號分隔）──────────
    case 'bulk_import':
        $raw   = trim($_POST['emails'] ?? '');
        $lines = preg_split('/[\r\n,;]+/', $raw);
        $ok = $fail = 0;
        $db = getDB();
        $st = $db->prepare("INSERT IGNORE INTO recipients (email) VALUES (?)");

        foreach ($lines as $line) {
            $email = trim($line);
            if ($email === '') continue;

            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $st->execute([$email]);
                if ($st->rowCount() > 0) {
                    $ok++;
                } else {
                    $fail++; // 重複資料視為跳過
                }
            } else {
                $fail++;
            }
        }
        json_out(['success' => true, 'msg' => "成功匯入 $ok 筆，失敗/跳過 $fail 筆"]);

    // ── 刪除收件人 ──────────────────────────────────
    case 'delete_recipient':
        $id = (int)($_POST['id'] ?? 0);
        $db = getDB();
        $db->prepare("DELETE FROM recipients WHERE no = ?")->execute([$id]);
        json_out(['success' => true, 'msg' => '已刪除']);

    // ── 取得寄送記錄 ────────────────────────────────
    case 'get_logs':
        $db = getDB();
        $rows = $db->query(
            "SELECT id, email, subject, status, sent_at, error_message
             FROM send_log ORDER BY sent_at DESC LIMIT 200"
        )->fetchAll();
        json_out(['success' => true, 'data' => $rows]);

    // ── 單封測試寄信 ────────────────────────────────
    case 'send_single':
        $to      = trim($_POST['to']      ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $body    = trim($_POST['body']    ?? '');
        json_out(sendMail($to, $subject, $body));

    default:
        json_out(['success' => false, 'msg' => '未知指令']);
}

// ────────────────────────────────────────────────────
// 寄信函式：只在真的寄信時才載入 PHPMailer
// 這樣新增 / 匯入收件人不會被 PHPMailer 路徑問題卡住
// ────────────────────────────────────────────────────
function sendMail(string $to, string $subject, string $body): array {
    require_once __DIR__ . '/PHPMailer/src/Exception.php';
    require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
    require_once __DIR__ . '/PHPMailer/src/SMTP.php';

    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';
        $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags($body);
        $mail->send();
        return ['success' => true, 'msg' => '寄送成功'];
    } catch (Throwable $e) {
        return ['success' => false, 'error' => $mail->ErrorInfo ?: $e->getMessage()];
    }
}
