<?php

class ModelExtensionModuleSoAskQuestion extends Model {

public function sendData($data) {
    $this->load->language('extension/module/so_askquestion');
    $this->load->model('catalog/product');
    
    $product_info = $this->model_catalog_product->getProduct($data['product_id']);
    
    $mail = new Mail($this->config->get('config_mail_engine'));
    $mail->parameter = $this->config->get('config_mail_parameter');
    $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
    $mail->smtp_username = $this->config->get('config_mail_smtp_username');
    $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
    $mail->smtp_port = $this->config->get('config_mail_smtp_port');
    $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

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
    $mail->setFrom($this->config->get('config_email'));
    $mail->setSender($this->config->get('config_name'));
    $mail->setSubject($subject);

    if ($data['email']) {
        $mail->setTo($data['email']);
        $mail->setText($message);
        $mail->send();
    }

    /* ---------------------------
     *  SEND EMAIL TO ADMIN
     * --------------------------- */
    $mail->setText($messageAdmin);

    // Main store email
    $mail->setTo($this->config->get('config_email'));
    $mail->send();

    // Additional admin emails
    if ($this->config->get('module_so_askquestion_add_email')) {
        $emails = explode(',', $this->config->get('module_so_askquestion_add_email'));
        foreach ($emails as $email) {
            $email = trim($email);
            if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $mail->setTo($email);
                $mail->send();
            }
        }
    }
}

}