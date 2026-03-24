<?php
class ControllerInformationTrialRequest extends Controller {
    private $error = array();

    public function index() {
        $this->load->language('information/contact');

        $this->document->setTitle('Organise a Product Trial | MobilityCare Australia');
        $this->document->setDescription('Request a free product trial for mobility aids, wheelchairs, rollators and assistive equipment. Try before you buy with MobilityCare Australia.');
        $this->load->model('catalog/demo_request');
        $this->load->model('catalog/manufacturer');

        // $data['products'] = $this->model_catalog_demo_request->getProductsByCategory(PRODUCT_TRIAL_CATEGORY_ID);
        $data['manufacturers'] = $this->model_catalog_manufacturer->getManufacturers();

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {

            $skip_validation = false;
            if (isset($this->session->data['trial_ajax_validated']) && $this->session->data['trial_ajax_validated'] === true) {
                $skip_validation = true;
                unset($this->session->data['trial_ajax_validated']);
            }
            
            if ($skip_validation || $this->validate()) {
                // Save to DB
                $this->model_catalog_demo_request->addProductTrialRequest($this->request->post);

                // Prepare Email
                $mailMessageHtml = $this->mailHtml($this->request->post);

                $mail = new Mail($this->config->get('config_mail_engine'));
                $mail->parameter = $this->config->get('config_mail_parameter');
                $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
                $mail->smtp_username = $this->config->get('config_mail_smtp_username');
                $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
                $mail->smtp_port = $this->config->get('config_mail_smtp_port');
                $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

                $mail->setTo($this->config->get('config_email'));
                 $mail->setFrom('enquiries@mobilitycare.net.au');
               $replyTo = (isset($this->request->post['email']) ? $this->request->post['email'] : 'enquiries@mobilitycare.net.au');
               $mail->setReplyTo($replyTo);
               $mail->setSender(html_entity_decode('MobilityCare', ENT_QUOTES, 'UTF-8'));
                $mail->setSubject('New Product Trial Request Received');
                $mail->setHtml($mailMessageHtml);
                $mail->send();
                
                
                 // auto reply to customer
                
                  if (isset($this->request->post['email'])) {
        $customerMail = new Mail($this->config->get('config_mail_engine'));
        $customerMail->parameter = $this->config->get('config_mail_parameter');
        $customerMail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
        $customerMail->smtp_username = $this->config->get('config_mail_smtp_username');
        $customerMail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
        $customerMail->smtp_port = $this->config->get('config_mail_smtp_port');
        $customerMail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');
        $customerMail->setTo($this->request->post['email']);
        $customerMail->setFrom('enquiries@mobilitycare.net.au');
        $customerMail->setReplyTo('enquiries@mobilitycare.net.au');
        $customerMail->setSender(html_entity_decode('MobilityCare', ENT_QUOTES, 'UTF-8'));
        $customerMail->setSubject('Thank You for Your Enquiry - MobilityCare');
        
        // Load the email template
        $data['customer_name'] = isset($this->request->post['fullname']) ? htmlspecialchars($this->request->post['fullname']) : 'Valued Customer';
        
        $customerMessageHtml = $this->load->view('mail/enquiry_confirmation', $data);
        
        $customerMail->setHtml($customerMessageHtml);
        // PDF brochure removed — 16 MB file causes memory exhaustion on 64 MB hosts
        $customerMail->send();
    }
    

               // redirect
                 $this->session->data['success'] = 'Your enquiry has been successfully submitted.';
                 $this->response->redirect($this->url->link('information/form_success/trial_request'));
            } 
        }

        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        // Error handling
        $data['error_fullname'] = isset($this->error['fullname']) ? $this->error['fullname'] : '';
        $data['error_email'] = isset($this->error['email']) ? $this->error['email'] : '';
        $data['error_phone'] = isset($this->error['phone']) ? $this->error['phone'] : '';
        $data['error_organisation'] = isset($this->error['organisation']) ? $this->error['organisation'] : '';
        $data['error_address'] = isset($this->error['address']) ? $this->error['address'] : '';
        $data['error_profession'] = isset($this->error['profession']) ? $this->error['profession'] : '';
        $data['error_profession_other'] = isset($this->error['profession_other']) ? $this->error['profession_other'] : '';
        $data['error_client_fullname'] = isset($this->error['client_fullname']) ? $this->error['client_fullname'] : '';
        $data['error_client_phone'] = isset($this->error['client_phone']) ? $this->error['client_phone'] : '';
        $data['error_manufacturer_id'] = isset($this->error['manufacturer_id']) ? $this->error['manufacturer_id'] : '';

        $data['button_submit'] = 'Submit Your Request';

        $data['action'] = '/organise-a-product-trial/';

        // Form field defaults
        $data['fullname'] = isset($this->request->post['fullname']) ? $this->request->post['fullname'] : $this->customer->getFirstName();
        $data['email'] = isset($this->request->post['email']) ? $this->request->post['email'] : $this->customer->getEmail();
        $data['phone'] = isset($this->request->post['phone']) ? $this->request->post['phone'] : '';
        $data['organisation'] = isset($this->request->post['organisation']) ? $this->request->post['organisation'] : '';
        $data['address'] = isset($this->request->post['address']) ? $this->request->post['address'] : '';
        $data['profession'] = isset($this->request->post['profession']) ? $this->request->post['profession'] : '';
        $data['profession_other'] = isset($this->request->post['profession_other']) ? $this->request->post['profession_other'] : '';
        $data['client_fullname'] = isset($this->request->post['client_fullname']) ? $this->request->post['client_fullname'] : '';
        $data['client_phone'] = isset($this->request->post['client_phone']) ? $this->request->post['client_phone'] : '';
        $data['manufacturer_id'] = isset($this->request->post['manufacturer_id']) ? $this->request->post['manufacturer_id'] : '';
        $data['notes'] = isset($this->request->post['notes']) ? $this->request->post['notes'] : '';

        // Captcha
        if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array('contact', (array)$this->config->get('config_captcha_page'))) {
            $data['captcha'] = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha'), $this->error);
        } else {
            $data['captcha'] = '';
        }
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('information/trial_request', $data));
    }

    private function mailHtml($post) {
        $this->load->model('catalog/manufacturer');
        $manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($post['manufacturer_id']);
        $manufacturer_name = $manufacturer_info ? html_entity_decode($manufacturer_info['name'], ENT_QUOTES, 'UTF-8') : 'Unknown Manufacturer';

        $html = '<h2>New Product Trial Request Received</h2>';
        $html .= '<p>A new product trial has been submitted with the following details:</p>';
        $html .= '<h3>Therapist Details</h3>';
        $html .= '<ul>';
        $html .= '<li><strong>Full Name:</strong> ' . htmlspecialchars($post['fullname']) . '</li>';
        $html .= '<li><strong>Email:</strong> ' . htmlspecialchars($post['email']) . '</li>';
        $html .= '<li><strong>Phone:</strong> ' . htmlspecialchars($post['phone']) . '</li>';
        $html .= '<li><strong>Postcode:</strong> ' . htmlspecialchars($post['postcode']) . '</li>';
        $html .= '<li><strong>Organisation/Clinic:</strong> ' . htmlspecialchars($post['organisation']) . '</li>';
        $html .= '<li><strong>Address:</strong> ' . nl2br(htmlspecialchars($post['address'])) . '</li>';
        $html .= '<li><strong>Profession:</strong> ' . htmlspecialchars($post['profession']) . '</li>';
        if (!empty($post['profession_other'])) {
            $html .= '<li><strong>Profession (Other):</strong> ' . htmlspecialchars($post['profession_other']) . '</li>';
        }
        $html .= '</ul>';
        $html .= '<h3>Client Details</h3>';
        $html .= '<ul>';
        $html .= '<li><strong>Client Full Name:</strong> ' . (empty($post['client_fullname']) ? 'Not provided' : htmlspecialchars($post['client_fullname'])) . '</li>';
        $html .= '<li><strong>Client Phone:</strong> ' . (empty($post['client_phone']) ? 'Not provided' : htmlspecialchars($post['client_phone'])) . '</li>';
        $html .= '<li><strong>Product Requested:</strong> ' . htmlspecialchars($manufacturer_name) . '</li>';
        $html .= '<li><strong>Notes:</strong> ' . (empty($post['notes']) ? 'None' : nl2br(htmlspecialchars($post['notes']))) . '</li>';
        $html .= '</ul>';
        return $html;
    }

    protected function validate() {
        // Therapist Details
        if ((utf8_strlen($this->request->post['fullname']) < 2) || (utf8_strlen($this->request->post['fullname']) > 32)) {
            $this->error['fullname'] = 'Full Name must be between 2 and 32 characters';
        }

        if (!filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)) {
            $this->error['email'] = 'Please enter a valid email address';
        }

        // Phone - accept 10 digits (national) or 11-12 digits (international with/without +)
        if (!preg_match('/^\+?[0-9]{9,12}$/', $this->request->post['phone'])) {
            $this->error['phone'] = 'Please enter a valid phone number.';
        }
        
        	// validate phone no is from AUS
	$this->load->helper('phone');	
     if (!isset($this->error['phone']) && !is_valid_au_phone($this->request->post['phone'])) {
      $this->error['phone'] = 'Please enter a valid Australian phone number';
     }
     

        if ((utf8_strlen($this->request->post['organisation']) < 2) || (utf8_strlen($this->request->post['organisation']) > 100)) {
            $this->error['organisation'] = 'Organisation/Clinic must be between 2 and 100 characters';
        }

        if ((utf8_strlen($this->request->post['address']) < 5) || (utf8_strlen($this->request->post['address']) > 255)) {
            $this->error['address'] = 'Address must be between 5 and 255 characters';
        }

        if (empty($this->request->post['profession'])) {
            $this->error['profession'] = 'Please select a profession';
        } elseif ($this->request->post['profession'] == 'Other' && (utf8_strlen($this->request->post['profession_other']) < 2 || utf8_strlen($this->request->post['profession_other']) > 100)) {
            $this->error['profession_other'] = 'Please specify a profession (2-100 characters)';
        }

        // Client Details (optional fields)
        if (!empty($this->request->post['client_fullname']) && ((utf8_strlen($this->request->post['client_fullname']) < 2) || (utf8_strlen($this->request->post['client_fullname']) > 32))) {
            $this->error['client_fullname'] = 'Client Full Name must be between 2 and 32 characters';
        }

        if (!empty($this->request->post['client_phone']) && !preg_match('/^[0-9]+$/', $this->request->post['client_phone'])) {
    $this->error['client_phone'] = 'Client Phone number must contain only digits';
}


        if (empty($this->request->post['manufacturer_id'])) {
            $this->error['manufacturer_id'] = 'Please select a product';
        }

        // Captcha
        if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array('contact', (array)$this->config->get('config_captcha_page'))) {
            $captcha = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha') . '/validate');
            if ($captcha) {
                $this->error['captcha'] = $captcha;
            }
        }

        return !$this->error;
    }

    public function validateAjax() {
        $json = [];
        if ($this->request->server['REQUEST_METHOD'] === 'POST') {
            if (!$this->validate()) {
                $json['error'] = $this->error;
            } else {
                $json['success'] = true;
                $this->session->data['trial_ajax_validated'] = true;
            }
        } else {
            $json['error'] = 'Invalid request';
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
?>