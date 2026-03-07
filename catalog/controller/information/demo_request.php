<?php
class ControllerInformationDemoRequest extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('information/contact');

		$this->document->setTitle('Demo');
        $this->load->model('catalog/demo_request');
        $this->load->model('catalog/manufacturer');
        $data['manufacturers'] = $this->model_catalog_manufacturer->getManufacturers();
        
        $data['marketing_popup'] = $this->load->controller('common/marketing_popup');
	  if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validate()) {
           
                // Save to DB
                $this->model_catalog_demo_request->addDemoRequest($this->request->post);

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
    $mail->setSubject('New Demo Request Received');
    $mail->setHtml($mailMessageHtml);
    $mail->send();
    
      //  Send automatic confirmation email to customer
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
    
                // redirect
       $this->session->data['success'] = 'Your enquiry has been successfully submitted.';
       $this->response->redirect($this->url->link('information/form_success/demo'));

             
        }

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

	
		if (isset($this->error['fullname'])) {
			$data['error_name'] = $this->error['fullname'];
		} else {
			$data['error_name'] = '';
		}
		
		if (isset($this->error['product_id'])) {
			$data['error_product'] = $this->error['product_id'];
		} else {
			$data['error_product'] = '';
		}


		if (isset($this->error['email'])) {
			$data['error_email'] = $this->error['email'];
		} else {
			$data['error_email'] = '';
		}

		if (isset($this->error['phone'])) {
			$data['error_phone'] = $this->error['phone'];
		} else {
			$data['error_phone'] = '';
		}

		$data['button_submit'] = 'Submit your request';

		$data['action'] = '/organise-a-product-demonstration/';


		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} else {
			$data['name'] = $this->customer->getFirstName();
		}

		if (isset($this->request->post['email'])) {
			$data['email'] = $this->request->post['email'];
		} else {
			$data['email'] = $this->customer->getEmail();
		}

		if (isset($this->request->post['phone'])) {
			$data['phone'] = $this->request->post['phone'];
		} else {
			$data['phone'] = '';
		}

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

		$this->response->setOutput($this->load->view('information/demo_request', $data));
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
  // only for authochair we need Car Make,model, year etc..
  
   $allowed_car_products = [79, 67, 115, 116, 117];
   
        $html = '<h2>New Demo Request Received</h2>';
        $html .= '<p>A new demo request has been submitted with the following details:</p>';
        $html .= '<ul>';
        $html .= '<li><strong>Full Name:</strong> ' . htmlspecialchars($post['fullname']) . '</li>';
        $html .= '<li><strong>Email:</strong> ' . htmlspecialchars($post['email']) . '</li>';
        $html .= '<li><strong>Phone:</strong> ' . htmlspecialchars($post['phone']) . '</li>';
        $html .= '<li><strong>Postcode:</strong> ' . htmlspecialchars($post['postcode']) . '</li>';
        $html .= '<li><strong>Demo Type:</strong> ' . htmlspecialchars($post['demo_type']) . '</li>';
        $html .= '<li><strong>Brand/Product Name:</strong> ' . htmlspecialchars($brandOrProductname) . '</li>';
        
        //  Add CAR FIELDS only for specific products
    if (isset($post['manufacturer_id']) && in_array((int)$post['manufacturer_id'], $allowed_car_products)) {

        $html .= '<li><strong>Car Make:</strong> ' . (!empty($post['car_make']) ? htmlspecialchars($post['car_make']) : 'N/A') . '</li>';
        $html .= '<li><strong>Car Model:</strong> ' . (!empty($post['car_model']) ? htmlspecialchars($post['car_model']) : 'N/A') . '</li>';
        $html .= '<li><strong>Car Year:</strong> ' . (!empty($post['car_year']) ? htmlspecialchars($post['car_year']) : 'N/A') . '</li>';
        $html .= '<li><strong>Body Type:</strong> ' . (!empty($post['body_type']) ? htmlspecialchars($post['body_type']) : 'N/A') . '</li>';
    }
    
        $html .= '<li><strong>Additional Info:</strong> ' . nl2br(htmlspecialchars($post['additional_info'])) . '</li>';
        $html .= '</ul>';
        return $html;
    }

	
	
	 protected function validate() {
        // Name
        if (empty($this->request->post['fullname']) || utf8_strlen($this->request->post['fullname']) < 2) {
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
        if (empty($this->request->post['manufacturer_id'])) {
            $this->error['manufacturer_id'] = 'Please choose the product of intrest';
        }

      

        // Captcha validation
        if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') 
            && in_array('contact', (array)$this->config->get('config_captcha_page'))) {
            $captcha = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha') . '/validate');
            if ($captcha) {
                $this->error['captcha'] = $captcha;
            }
        }

        return !$this->error;
    }

	
}
