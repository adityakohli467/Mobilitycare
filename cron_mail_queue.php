<?php
/**
 * Mail Queue Processor (Cron Job)
 * 
 * Processes pending emails from oc_mail_queue table.
 * Run via cron every 1 minute:
 *   * * * * * php /home/healthychoicesca/public_html/cron_mail_queue.php >> /home/healthychoicesca/storage/logs/mail_queue.log 2>&1
 * 
 * Lock file prevents overlapping runs.
 */

// Prevent web access
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('CLI only');
}

require_once __DIR__ . '/config.php';

// Lock file to prevent concurrent runs
$lockFile = DIR_STORAGE . 'mail_queue.lock';

if (file_exists($lockFile)) {
    $lockAge = time() - filemtime($lockFile);
    // Stale lock (older than 5 minutes) — remove it
    if ($lockAge > 300) {
        unlink($lockFile);
    } else {
        exit(0); // Another instance is running
    }
}

file_put_contents($lockFile, getmypid());

// Cleanup on exit
register_shutdown_function(function() use ($lockFile) {
    if (file_exists($lockFile)) {
        unlink($lockFile);
    }
});

try {
    $db = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, (int)DB_PORT);
    if ($db->connect_error) {
        throw new Exception('DB ERROR: ' . $db->connect_error);
    }
    $db->set_charset('utf8mb4');

    // Fetch pending emails (batch of 10, high priority first)
    $result = $db->query("
        SELECT * FROM " . DB_PREFIX . "mail_queue 
        WHERE status = 'pending' 
          AND attempts < max_attempts
        ORDER BY priority DESC, created_at ASC 
        LIMIT 10
    ");

    if (!$result || $result->num_rows === 0) {
        $db->close();
        exit(0);
    }

    // Load the OpenCart Mail class + adaptors
    require_once DIR_SYSTEM . 'library/mail.php';
    require_once DIR_SYSTEM . 'library/mail/smtp.php';
    require_once DIR_SYSTEM . 'library/mail/mail.php';

    // Get SMTP config from the DB settings table
    $configResult = $db->query("
        SELECT `key`, `value` FROM " . DB_PREFIX . "setting 
        WHERE `key` IN (
            'config_mail_engine', 'config_mail_parameter',
            'config_mail_smtp_hostname', 'config_mail_smtp_username',
            'config_mail_smtp_password', 'config_mail_smtp_port',
            'config_mail_smtp_timeout'
        )
    ");

    $config = [];
    while ($row = $configResult->fetch_assoc()) {
        $config[$row['key']] = $row['value'];
    }

    $sent = 0;
    $failed = 0;

    while ($row = $result->fetch_assoc()) {
        $id = (int)$row['mail_queue_id'];
        
        // Mark as processing
        $db->query("UPDATE " . DB_PREFIX . "mail_queue SET status = 'processing' WHERE mail_queue_id = {$id}");

        try {
            $mail = new Mail(isset($config['config_mail_engine']) ? $config['config_mail_engine'] : 'mail');
            $mail->parameter = isset($config['config_mail_parameter']) ? $config['config_mail_parameter'] : '';
            $mail->smtp_hostname = isset($config['config_mail_smtp_hostname']) ? $config['config_mail_smtp_hostname'] : '';
            $mail->smtp_username = isset($config['config_mail_smtp_username']) ? $config['config_mail_smtp_username'] : '';
            $mail->smtp_password = html_entity_decode(
                isset($config['config_mail_smtp_password']) ? $config['config_mail_smtp_password'] : '', 
                ENT_QUOTES, 'UTF-8'
            );
            $mail->smtp_port = isset($config['config_mail_smtp_port']) ? $config['config_mail_smtp_port'] : 25;
            $mail->smtp_timeout = isset($config['config_mail_smtp_timeout']) ? $config['config_mail_smtp_timeout'] : 5;

            $mail->setTo($row['to_email']);
            $mail->setFrom($row['from_email']);
            $mail->setSender(html_entity_decode($row['sender_name'], ENT_QUOTES, 'UTF-8'));
            
            if (!empty($row['reply_to'])) {
                $mail->setReplyTo($row['reply_to']);
            }
            
            $mail->setSubject($row['subject']);
            $mail->setHtml($row['html_body']);
            $mail->send();

            // Mark as sent
            $db->query("UPDATE " . DB_PREFIX . "mail_queue SET 
                status = 'sent', 
                attempts = attempts + 1,
                processed_at = NOW() 
                WHERE mail_queue_id = {$id}");
            
            $sent++;

        } catch (\Throwable $e) {
            $errorMsg = $db->real_escape_string($e->getMessage());
            $newAttempts = (int)$row['attempts'] + 1;
            $newStatus = ($newAttempts >= (int)$row['max_attempts']) ? 'failed' : 'pending';
            
            $db->query("UPDATE " . DB_PREFIX . "mail_queue SET 
                status = '{$newStatus}', 
                attempts = {$newAttempts},
                last_error = '{$errorMsg}',
                processed_at = NOW() 
                WHERE mail_queue_id = {$id}");
            
            $failed++;
        }
    }

    // Cleanup old sent emails (older than 30 days)
    $db->query("DELETE FROM " . DB_PREFIX . "mail_queue WHERE status = 'sent' AND processed_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");

    if ($sent > 0 || $failed > 0) {
        echo date('Y-m-d H:i:s') . " - Processed: {$sent} sent, {$failed} failed" . PHP_EOL;
    }

    $db->close();

} catch (\Throwable $e) {
    echo date('Y-m-d H:i:s') . " - FATAL: " . $e->getMessage() . PHP_EOL;
    if (file_exists($lockFile)) {
        unlink($lockFile);
    }
    exit(1);
}
