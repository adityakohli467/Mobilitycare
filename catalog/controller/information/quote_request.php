<?php
class ControllerInformationQuoteRequest extends Controller {
    private $error = array();

    public function index() {
        $this->load->language('information/contact');
        $this->load->model('catalog/demo_request');
        $this->load->model('catalog/manufacturer');

        $data['manufacturers'] = $this->model_catalog_manufacturer->getManufacturers();

        $this->document->setTitle('Quote Request');

        // Handle form submission
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            if ($this->validate()) {

                // Default empty for missing fields
                $fields = ['vehicle_make', 'vehicle_model', 'vehicle_year', 'body_type', 'lifting_item', 'item_weight', 'item_height'];
                foreach ($fields as $field) {
                    if (!isset($this->request->post[$field])) {
                        $this->request->post[$field] = '';
                    }
                }

                // Send mail
                $mailMessageHtml = $this->mailHtml($this->request->post);

                try {
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
    $mail->setSubject('New Quote Request Received');
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
        $customerMail->addAttachment(DIR_IMAGE . 'mobilitycare-brochure-assistive-technology-web.pdf');
        $customerMail->send();
    }
    
} catch (Exception $e) {
    // Log the error
    $this->log->write('MAIL ERROR: ' . $e->getMessage());
}


                // Save in database
                $this->model_catalog_demo_request->addQuoteRequest($this->request->post);

                // redirect
                 $this->session->data['success'] = 'Your enquiry has been successfully submitted.';
                 $this->response->redirect($this->url->link('information/form_success/quote'));
            } 
        }

        $data['action'] = '/request-quote/';
        $data['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';
        $data['error_fullname'] = isset($this->error['fullname']) ? $this->error['fullname'] : '';
        $data['error_email'] = isset($this->error['email']) ? $this->error['email'] : '';
        $data['error_phone'] = isset($this->error['phone']) ? $this->error['phone'] : '';
        
        	if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array('contact', (array)$this->config->get('config_captcha_page'))) {
			$data['captcha'] = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha'), $this->error);
	    	} else {
			$data['captcha'] = '';
		    }

        $data['header'] = $this->load->controller('common/header');
        $data['footer'] = $this->load->controller('common/footer');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');

        $this->response->setOutput($this->load->view('information/quote_request', $data));
    }

   
   public function validateAjax() {
    $json = [];

    if ($this->request->server['REQUEST_METHOD'] === 'POST') {

        if (!$this->validate()) {
            $json['error'] = $this->error;
        } else {
            $json['success'] = true;
        }
    } else {
        $json['error'] = 'Invalid request';
    }

    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($json));
}


    private function mailHtml($post) {

       if(isset($post['is_manufacturer_or_product']) && $post['is_manufacturer_or_product'] == 1){
           $this->load->model('catalog/product');
         $product_info = $this->model_catalog_product->getProduct($post['manufacturer_id']);
        $brandOrProductname = $product_info ? html_entity_decode($product_info['name'], ENT_QUOTES, 'UTF-8') : 'Unknown Product';  
        }else{
             $this->load->model('catalog/manufacturer');
           $manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($post['manufacturer_id']);
        $brandOrProductname = $manufacturer_info ? html_entity_decode($manufacturer_info['name'], ENT_QUOTES, 'UTF-8') : 'Unknown Manufacturer';
   
        }
        
        
    $html = '<html><body>';
$html .= '<h3>New Quote Request Received</h3>';

$html .= '<p><b>Full Name:</b> ' . htmlspecialchars(isset($post['fullname']) ? $post['fullname'] : '') . '</p>';
$html .= '<p><b>Email:</b> ' . htmlspecialchars(isset($post['email']) ? $post['email'] : '') . '</p>';
$html .= '<p><b>Phone:</b> ' . htmlspecialchars(isset($post['phone']) ? $post['phone'] : '') . '</p>';
$html .= '<p><b>Postcode:</b> ' . htmlspecialchars(isset($post['postcode']) ? $post['postcode'] : '') . '</p>';
$html .= '<p><b>Contact Type:</b> ' . htmlspecialchars(isset($post['contact_type']) ? $post['contact_type'] : '') . '</p>';
$html .= '<p><b>Quote Type:</b> ' . htmlspecialchars(isset($post['quote_type']) ? $post['quote_type'] : '') . '</p>';

// If vehicle details exist
if (isset($post['vehicle_make']) || isset($post['vehicle_model']) || isset($post['vehicle_year'])) {
    $html .= '<h4>Vehicle Details</h4>';
    $html .= '<p><b>Make:</b> ' . htmlspecialchars(isset($post['vehicle_make']) ? $post['vehicle_make'] : '') . '</p>';
    $html .= '<p><b>Model:</b> ' . htmlspecialchars(isset($post['vehicle_model']) ? $post['vehicle_model'] : '') . '</p>';
    $html .= '<p><b>Year:</b> ' . htmlspecialchars(isset($post['vehicle_year']) ? $post['vehicle_year'] : '') . '</p>';
    $html .= '<p><b>Body Type:</b> ' . htmlspecialchars(isset($post['body_type']) ? $post['body_type'] : '') . '</p>';
}

// If lifting item details exist
if (isset($post['lifting_item']) || isset($post['item_height']) || isset($post['item_weight'])) {
    $html .= '<h4>Mobility Item Details</h4>';
    $html .= '<p><b>Lifting Item:</b> ' . htmlspecialchars(isset($post['lifting_item']) ? $post['lifting_item'] : '') . '</p>';
    $html .= '<p><b>Item Height (cm):</b> ' . htmlspecialchars(isset($post['item_height']) ? $post['item_height'] : '') . '</p>';
    $html .= '<p><b>Item Weight (kg):</b> ' . htmlspecialchars(isset($post['item_weight']) ? $post['item_weight'] : '') . '</p>';
}

// Brand/Product name logic (depends on whether manufacturer or product)
$html .= '<p><b>Brand/Product Name:</b> ' . htmlspecialchars(isset($brandOrProductname) ? $brandOrProductname : '') . '</p>';

$html .= '<p><b>Additional Info:</b><br>' . nl2br(htmlspecialchars(isset($post['additional_info']) ? $post['additional_info'] : '')) . '</p>';

$html .= '</body></html>';


        return $html;
    }

    protected function validate() {
        // Name
        if (empty($this->request->post['fullname']) || utf8_strlen($this->request->post['fullname']) < 1) {
            $this->error['fullname'] = 'Full name must be at least 2 characters.';
        }

        // Email
        if (empty($this->request->post['email']) || !filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)) {
            $this->error['email'] = 'Please enter a valid email address.';
        }

        // Phone - accept 10 digits (national) or 11-12 digits (international with/without +)
        if (empty($this->request->post['phone']) || !preg_match('/^\+?[0-9]{9,12}$/', $this->request->post['phone'])) {
            $this->error['phone'] = 'Please enter a valid phone number.';
        }
        
        	// validate phone no is from AUS
	$this->load->helper('phone');	
     if (!isset($this->error['phone']) && !is_valid_au_phone($this->request->post['phone'])) {
      $this->error['phone'] = 'Please enter a valid Australian phone number';
     }
     

        // Postcode
        if (empty($this->request->post['postcode']) || !preg_match('/^[0-9]{4}$/', $this->request->post['postcode'])) {
            $this->error['postcode'] = 'Please enter a valid 4-digit postcode.';
        }

        // Contact Type
        if (empty($this->request->post['contact_type'])) {
            $this->error['contact_type'] = 'Please select how you are contacting us.';
        }

        // Quote Type
        if (empty($this->request->post['quote_type'])) {
            $this->error['quote_type'] = 'Please select a preferred quote type.';
        }
        
        $item_weight = isset($this->request->post['item_weight']) ? trim($this->request->post['item_weight']) : '';

if ((isset($this->request->post['item_weight']) ) && ($item_weight === '' || !preg_match('/^\d+(\.\d+)?$/', $item_weight))) {
    $this->error['item_weight'] = 'Please enter a valid weight (integer or decimal).';
}

$item_height = isset($this->request->post['item_height']) ? trim($this->request->post['item_height']) : '';

if ((isset($this->request->post['item_height'])) && ($item_height === '' || !preg_match('/^\d+(\.\d+)?$/', $item_height))) {
    $this->error['item_height'] = 'Please enter a valid height (integer or decimal).';
}


        // Captcha validation
        if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') 
            && in_array('contact', (array)$this->config->get('config_captcha_page'))) {
            
            // Check if submission came from so_listing_tabs module (uses separate session key
            // because other captcha widgets on the same page overwrite session['captcha'])
            $listing_captcha_valid = false;
            if (isset($this->request->post['captcha']) && isset($this->session->data['listing_captcha'])) {
                if ($this->request->post['captcha'] == $this->session->data['listing_captcha']) {
                    $listing_captcha_valid = true;
                    unset($this->session->data['listing_captcha']); // one-time use
                }
            }
            
            if (!$listing_captcha_valid) {
                $captcha = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha') . '/validate');
                if ($captcha) {
                    $this->error['captcha'] = $captcha;
                }
            }
        }

        return !$this->error;
    }

  
}
