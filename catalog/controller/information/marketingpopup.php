<?php
class ControllerInformationMarketingPopup extends Controller {

  public function submit() {
    $this->load->language('information/contact');
    $json = [];

    $email = $this->request->post['work_email'] ?? '';
    $product_id = isset($this->request->post['product_id']) ? (int)$this->request->post['product_id'] : 0;

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $json['success'] = false;
      $json['message'] = 'Please enter a valid email address.';
      $this->response->addHeader('Content-Type: application/json');
      $this->response->setOutput(json_encode($json));
      return;
    }
    
    $this->load->model('catalog/product');

        $product_name = 'Not specified / General request';

        if ($product_id > 0) {
            $product_info = $this->model_catalog_product->getProduct($product_id);

            if ($product_info) {
                $product_name = $product_info['name']; // already in current language
            } else {
                $product_name = 'Product ID ' . $product_id . ' (not found)';
            }
        }
        

    // Admin email
    $admin_message  = "New health professional document request.\n\n";
    $admin_message .= "Email: " . $email . "\n";
    $admin_message .= "Product: " . $product_name . "\n";
    $admin_message .= "Date: " . date('Y-m-d H:i:s');

    $mail = new Mail();
    $mail->protocol = $this->config->get('config_mail_protocol');
    $mail->parameter = $this->config->get('config_mail_parameter');
    $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
    $mail->smtp_username = $this->config->get('config_mail_smtp_username');
    $mail->smtp_password = $this->config->get('config_mail_smtp_password');
    $mail->smtp_port = $this->config->get('config_mail_smtp_port');
    $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

    // Send to admin
     $mail->setTo($this->config->get('config_email'));
    // $mail->setTo('kohliaditya@yahoo.com');
    $mail->setFrom($this->config->get('config_email'));
    $mail->setSender($this->config->get('config_name'));
    $mail->setSubject('New Health Professional Document Request');
    $mail->setText($admin_message);
    $mail->send();

    // Confirmation to customer
    $mail->setTo($email);
    $mail->setSubject('We’ve received your request');
    $mail->setText(
      "Thank you for your request.\n\n" .
      "Our team has received your details and will email you the documents shortly.\n\n" .
      $this->config->get('config_name')
    );
    $mail->send();

    $json['success'] = true;
    $json['message'] = 'Thanks! Your request has been sent successfully.';

    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($json));
  }
}
