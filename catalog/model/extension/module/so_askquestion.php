<?php

class ModelExtensionModuleSoAskQuestion extends Model {

public function sendData($data) {
    $this->load->language('extension/module/so_askquestion');
    $this->load->model('catalog/product');
    
    $product_info = $this->model_catalog_product->getProduct($data['product_id']);
    
    $subject = 'Find a dealer enquiry - ' . html_entity_decode($product_info['name'], ENT_QUOTES, 'UTF-8');

    /* ---------------------------
     *  CUSTOMER EMAIL
     * --------------------------- */
    $message  = "Dear user  \n\n";
    $message .= "Thank you for your interest in our product:\n";
    $message .= html_entity_decode($product_info['name'], ENT_QUOTES, 'UTF-8') . "\n";
    $message .= $data['product_link'] . "\n\n";
    $message .= "Your request has been forwarded and we will contact you shortly.\n\n";
    $message .= "Best Regards,\n";
    $message .= $this->config->get('config_name') . "\n";
    $message .= $data['shop_url'];

    /* ---------------------------
     *  ADMIN EMAIL
     * --------------------------- */
    $messageAdmin  = "Hello Admin,\n\n";
    $messageAdmin .= "You have received a new 'Find a Dealer' enquiry.\n\n";
    $messageAdmin .= "Customer Information:\n";
   
    $messageAdmin .= "Email: " . $data['email'] . "\n";
    $messageAdmin .= "State: " . $data['state'] . "\n";
    $messageAdmin .= "Postcode: " . $data['postcode'] . "\n";
    
    if (!empty($data['phone'])) {
        $messageAdmin .= "Contact Number: " . $data['phone'] . "\n";
    }
    if (!empty($data['message'])) {
        $messageAdmin .= "Question: " . $data['message'] . "\n";
    }

    $messageAdmin .= "\nProduct: " . $product_info['name'] . "\n";
    $messageAdmin .= "Product Link: " . $data['product_link'] . "\n\n";
    $messageAdmin .= "Best Regards,\n";
    $messageAdmin .= $this->config->get('config_name') . "\n";
    $messageAdmin .= $data['shop_url'];

    /* ---------------------------
     *  SEND EMAIL TO CUSTOMER
     * --------------------------- */
    if ($data['email']) {
        $customerMail = new Mail($this->config->get('config_mail_engine'));
        $customerMail->parameter = $this->config->get('config_mail_parameter');
        $customerMail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
        $customerMail->smtp_username = $this->config->get('config_mail_smtp_username');
        $customerMail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
        $customerMail->smtp_port = $this->config->get('config_mail_smtp_port');
        $customerMail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');
        $customerMail->setTo($data['email']);
        $customerMail->setFrom('enquiries@mobilitycare.net.au');
        $customerMail->setReplyTo('enquiries@mobilitycare.net.au');
        $customerMail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
        $customerMail->setSubject($subject);
        $customerMail->setText($message);
        $customerMail->send();
    }

    /* ---------------------------
     *  SEND EMAIL TO ADMIN
     * --------------------------- */
    $adminMail = new Mail($this->config->get('config_mail_engine'));
    $adminMail->parameter = $this->config->get('config_mail_parameter');
    $adminMail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
    $adminMail->smtp_username = $this->config->get('config_mail_smtp_username');
    $adminMail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
    $adminMail->smtp_port = $this->config->get('config_mail_smtp_port');
    $adminMail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');
    $adminMail->setFrom('enquiries@mobilitycare.net.au');
    $replyTo = !empty($data['email']) ? $data['email'] : 'enquiries@mobilitycare.net.au';
    $adminMail->setReplyTo($replyTo);
    $adminMail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
    $adminMail->setSubject($subject);
    $adminMail->setText($messageAdmin);

    // Main store email
    $adminMail->setTo($this->config->get('config_email'));
    $adminMail->send();

    // Additional admin emails
    if ($this->config->get('module_so_askquestion_add_email')) {
        $emails = explode(',', $this->config->get('module_so_askquestion_add_email'));
        foreach ($emails as $email) {
            $email = trim($email);
            if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $extraMail = new Mail($this->config->get('config_mail_engine'));
                $extraMail->parameter = $this->config->get('config_mail_parameter');
                $extraMail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
                $extraMail->smtp_username = $this->config->get('config_mail_smtp_username');
                $extraMail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
                $extraMail->smtp_port = $this->config->get('config_mail_smtp_port');
                $extraMail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');
                $extraMail->setFrom('enquiries@mobilitycare.net.au');
                $extraMail->setReplyTo($replyTo);
                $extraMail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
                $extraMail->setSubject($subject);
                $extraMail->setText($messageAdmin);
                $extraMail->setTo($email);
                $extraMail->send();
            }
        }
    }
}

}