<?php
/**
 * Mail Queue Helper for MobilityCare
 * 
 * Queues emails for async processing instead of sending synchronously.
 * Usage from any controller:
 * 
 *   $this->load->helper('mail_queue');
 *   mail_queue_add($this->db, [
 *       'to'       => 'admin@example.com',
 *       'from'     => 'enquiries@mobilitycare.net.au',
 *       'sender'   => 'MobilityCare',
 *       'reply_to' => 'customer@example.com',
 *       'subject'  => 'New Enquiry',
 *       'html'     => '<p>Hello</p>',
 *       'priority' => 2,  // optional: 1=normal, 2=high
 *   ]);
 */

/**
 * Add an email to the mail queue for async processing.
 *
 * @param object $db       OpenCart DB object
 * @param array  $params   Email parameters
 * @return int|false       mail_queue_id on success, false on failure
 */
function mail_queue_add($db, $params) {
    $to       = isset($params['to']) ? $db->escape($params['to']) : '';
    $from     = isset($params['from']) ? $db->escape($params['from']) : '';
    $sender   = isset($params['sender']) ? $db->escape($params['sender']) : 'MobilityCare';
    $reply_to = isset($params['reply_to']) ? $db->escape($params['reply_to']) : '';
    $subject  = isset($params['subject']) ? $db->escape($params['subject']) : '';
    $html     = isset($params['html']) ? $db->escape($params['html']) : '';
    $priority = isset($params['priority']) ? (int)$params['priority'] : 1;

    if (empty($to) || empty($from) || empty($subject) || empty($html)) {
        return false;
    }

    $db->query("INSERT INTO " . DB_PREFIX . "mail_queue SET
        to_email     = '{$to}',
        from_email   = '{$from}',
        sender_name  = '{$sender}',
        reply_to     = '{$reply_to}',
        subject      = '{$subject}',
        html_body    = '{$html}',
        priority     = '{$priority}',
        status       = 'pending',
        attempts     = 0,
        max_attempts = 3,
        created_at   = NOW()");

    return $db->getLastId();
}
