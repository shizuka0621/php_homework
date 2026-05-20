<?php
// send.php - Server-Sent Events 串流寄信，即時回報進度
require_once __DIR__ . '/config.php';

// ── SSE 標頭 ────────────────────────────────────────
header('Content-Type: text/event-stream; charset=utf-8');
header('Cache-Control: no-cache');
header('X-Accel-Buffering: no');
set_time_limit(0);
ob_implicit_flush(true);
while (ob_get_level() > 0) {
    ob_end_flush();
}

function sse(string $event, array $data): void {
    echo "event: $event\n";
    echo "data: " . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n\n";
    flush();
}

set_exception_handler(function (Throwable $e) {
    sse('error', ['msg' => '後端錯誤：' . $e->getMessage()]);
    exit;
});

// ── PHPMailer ──────────────────────────────────────
require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

// ── 讀取參數 ────────────────────────────────────────
$mode      = $_GET['mode']      ?? 'all';      // all | random
$count     = (int)($_GET['count'] ?? 5);       // 隨機時的筆數
$minDelay  = (int)($_GET['min_delay'] ?? 2);   // 最小間隔秒
$maxDelay  = (int)($_GET['max_delay'] ?? 5);   // 最大間隔秒
$subject   = trim($_GET['subject'] ?? '（無主旨）');
$body      = trim($_GET['body'] ?? '');

if ($minDelay < 0) $minDelay = 0;
if ($maxDelay < 0) $maxDelay = 0;
if ($minDelay > $maxDelay) $maxDelay = $minDelay;

if ($subject === '') $subject = '（無主旨）';
if ($body === '') {
    sse('error', ['msg' => '郵件內容不可為空']);
    exit;
}

// ── 取得收件人 ──────────────────────────────────────
$db = getDB();
$allRecipients = $db->query(
    "SELECT no, email, name FROM recipients WHERE status = 'active' ORDER BY no"
)->fetchAll();

if (empty($allRecipients)) {
    sse('error', ['msg' => '收件人清單為空']);
    exit;
}

if ($mode === 'random') {
    $count = max(1, min($count, count($allRecipients)));
    $keys  = array_rand($allRecipients, $count);
    if (!is_array($keys)) $keys = [$keys];
    $recipients = array_map(fn($k) => $allRecipients[$k], $keys);
} else {
    $recipients = $allRecipients;
}

$total   = count($recipients);
$success = 0;
$failed  = 0;

sse('start', ['total' => $total, 'msg' => "準備寄送 $total 封郵件"]);

$logSt = $db->prepare(
    "INSERT INTO send_log (recipient_id, email, subject, status, error_message)
     VALUES (?, ?, ?, ?, ?)"
);

foreach ($recipients as $i => $r) {
    $current = $i + 1;
    $percent = round($current / $total * 100);

    $result = sendMail($r['email'], $subject, $body);

    if ($result['success']) {
        $success++;
        $status = 'success';
        $errMsg = null;
    } else {
        $failed++;
        $status = 'failed';
        $errMsg = $result['error'] ?? '未知錯誤';
    }

    $logSt->execute([$r['no'], $r['email'], $subject, $status, $errMsg]);

    sse('progress', [
        'current'  => $current,
        'total'    => $total,
        'percent'  => $percent,
        'email'    => $r['email'],
        'status'   => $status,
        'success'  => $success,
        'failed'   => $failed,
        'error'    => $errMsg,
    ]);

    if ($current < $total) {
        $delay = rand($minDelay, $maxDelay);
        if ($delay > 0) {
            sse('delay', ['seconds' => $delay, 'msg' => "等待 {$delay} 秒後繼續..."]);
            sleep($delay);
        }
    }
}

sse('done', [
    'msg'     => "寄送完成！成功 $success 封，失敗 $failed 封",
    'success' => $success,
    'failed'  => $failed,
    'total'   => $total,
]);

// ────────────────────────────────────────────────────
function sendMail(string $to, string $subject, string $body): array {
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
        return ['success' => true];
    } catch (Throwable $e) {
        return ['success' => false, 'error' => $mail->ErrorInfo ?: $e->getMessage()];
    }
}
